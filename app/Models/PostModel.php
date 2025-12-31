<?php
namespace FluentContent\Models;

class PostModel
{
    public static function getStats() {
        global $wpdb;

        // Get current month date range
        $first_day = date('Y-m-01 00:00:00');
        $last_day = date('Y-m-t 23:59:59');

        return [
            'total' => (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='post'"
            ),
            'published' => (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish'"
            ),
            'scheduled' => (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='post' AND post_status='future'"
            ),
            'private' => (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='post' AND post_status='private'"
            ),
            'pending' => (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='post' AND post_status='pending'"
            ),
            'draft' => (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='post' AND post_status='draft'"
            ),
            'thisMonth' => (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} 
                WHERE post_type='post' 
                AND post_status IN ('publish', 'future')
                AND post_date >= %s 
                AND post_date <= %s",
                $first_day,
                $last_day
            ))
        ];
    }
    public static function getRecent($limit = 10) {
        global $wpdb;

        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_title, post_status, post_date, post_modified 
            FROM {$wpdb->posts} 
            WHERE post_type = 'post' 
            AND post_status IN ('publish', 'future', 'draft', 'pending')
            ORDER BY post_modified DESC 
            LIMIT %d",
            $limit
        ));

        $formatted_posts = [];
        foreach ($posts as $post) {
            $category = self::getPostCategory($post->ID);

            $formatted_posts[] = [
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_status' => $post->post_status,
                'time_ago' => self::timeAgo($post->post_modified),
                'category' => $category,
                'post_date' => $post->post_date
            ];
        }

        return $formatted_posts;
    }
    public static function getUpcomingScheduled($limit = 5) {
        global $wpdb;

        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_title, post_date 
            FROM {$wpdb->posts} 
            WHERE post_type = 'post' 
            AND post_status = 'future'
            AND post_date > NOW()
            ORDER BY post_date ASC 
            LIMIT %d",
            $limit
        ));

        $formatted_posts = [];
        foreach ($posts as $post) {
            $tags = self::getPostTags($post->ID);

            $formatted_posts[] = [
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'scheduled_time' => self::formatScheduledTime($post->post_date),
                'tags' => $tags,
                'post_date' => $post->post_date
            ];
        }

        return $formatted_posts;
    }
    public static function getQuickStats() {
        global $wpdb;

        // Get all published and scheduled posts content
        $posts = $wpdb->get_results(
            "SELECT post_content 
            FROM {$wpdb->posts} 
            WHERE post_type = 'post' 
            AND post_status IN ('publish', 'future', 'draft')"
        );

        $total_words = 0;
        foreach ($posts as $post) {
            // Strip HTML tags and count words
            $content = wp_strip_all_tags($post->post_content);
            $word_count = str_word_count($content);
            $total_words += $word_count;
        }

        return [
            'wordsGenerated' => $total_words,
            'postsCount' => count($posts)
        ];
    }
    public static function getDashboardData() {
        return [
            'stats' => self::getStats(),
            'recent' => self::getRecent(4), // Show 4 recent posts
            'upcoming' => self::getUpcomingScheduled(5),
            'quickStats' => self::getQuickStats()
        ];
    }
    private static function getPostCategory($post_id) {
        $categories = wp_get_post_categories($post_id, ['fields' => 'names']);
        return !empty($categories) ? $categories[0] : null;
    }
    private static function getPostTags($post_id) {
        $tags = wp_get_post_tags($post_id, ['fields' => 'names']);

        // If no tags, try to get category
        if (empty($tags)) {
            $category = self::getPostCategory($post_id);
            return $category ? [$category] : ['Uncategorized'];
        }

        // Return first 3 tags
        return array_slice($tags, 0, 3);
    }
    private static function timeAgo($datetime) {
        $time = strtotime($datetime);
        $current = current_time('timestamp');
        $difference = $current - $time;

        if ($difference < 0) {
            // Future date
            $difference = abs($difference);
            if ($difference < 3600) {
                return 'in ' . round($difference / 60) . ' minutes';
            } elseif ($difference < 86400) {
                return 'in ' . round($difference / 3600) . ' hours';
            } else {
                return 'in ' . round($difference / 86400) . ' days';
            }
        }

        // Past date
        if ($difference < 60) {
            return 'just now';
        } elseif ($difference < 3600) {
            $minutes = round($difference / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($difference < 86400) {
            $hours = round($difference / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($difference < 604800) {
            $days = round($difference / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($difference < 2592000) {
            $weeks = round($difference / 604800);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }
    private static function formatScheduledTime($datetime) {
        $time = strtotime($datetime);
        $current = current_time('timestamp');
        $difference = $time - $current;

        // Check if it's today
        if (date('Y-m-d', $time) === date('Y-m-d', $current)) {
            return 'Today at ' . date('g:i A', $time);
        }

        // Check if it's tomorrow
        $tomorrow = strtotime('+1 day', strtotime(date('Y-m-d', $current)));
        if (date('Y-m-d', $time) === date('Y-m-d', $tomorrow)) {
            return 'Tomorrow at ' . date('g:i A', $time);
        }

        // Check if it's within a week
        if ($difference < 604800) {
            return date('l', $time) . ' at ' . date('g:i A', $time);
        }

        // Otherwise show full date
        return date('M j', $time) . ' at ' . date('g:i A', $time);
    }
}
