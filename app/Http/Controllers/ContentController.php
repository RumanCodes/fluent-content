<?php

namespace FluentContent\Http\Controllers;

use FluentContent\Services\OpenAIService;
use FluentContent\Models\PostModel;

class ContentController
{
    private $openAIService;

    public function __construct()
    {
        $this->openAIService = new OpenAIService();
    }

    /**
     * Generate blog post content
     */
    public function generateContent()
    {
        try {
            // Check user permissions
            if (!current_user_can('publish_posts')) {
                wp_send_json_error(['message' => 'Insufficient permissions'], 403);
                return;
            }

            // Verify nonce
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                wp_send_json_error(['message' => 'Invalid request data'], 400);
                return;
            }

            // Verify nonce if provided
            if (isset($input['nonce']) && !wp_verify_nonce($input['nonce'], 'fluentContentNonce')) {
                wp_send_json_error(['message' => 'Invalid security token'], 403);
                return;
            }

            // Validate required fields
            $keywords = sanitize_text_field($input['keywords'] ?? '');
            if (empty($keywords)) {
                wp_send_json_error(['message' => 'Keywords are required'], 400);
                return;
            }

            // Get settings for defaults
            $settings = get_option('fluent_content_settings', []);

            // Prepare parameters for content generation
            $params = [
                'keyword' => $keywords,
                'title' => sanitize_text_field($input['title'] ?? ''),
                'contentType' => sanitize_text_field($input['contentType'] ?? 'blog'),
                'wordCount' => intval($input['wordCount'] ?? $settings['wordCount'] ?? 1000),
                'tone' => sanitize_text_field($input['tone'] ?? $settings['tone'] ?? 'professional'),
                'language' => sanitize_text_field($input['language'] ?? $settings['language'] ?? 'english'),
                'creativity' => sanitize_text_field($input['creativity'] ?? $settings['creativity'] ?? 'medium'),
            ];

            // Generate content using OpenAI service
            $response = $this->openAIService->generateBlogPost($params);

            if (empty($response['choices'][0]['message']['content'])) {
                wp_send_json_error(['message' => 'Failed to generate content'], 500);
                return;
            }

            $generatedContent = $response['choices'][0]['message']['content'];
            $model = $response['model'] ?? 'unknown';

            // Generate title if not provided
            $title = $params['title'];
            if (empty($title)) {
                $title = $this->generateTitle($keywords, $params);
            }

            // Extract meta description if needed
            $metaDescription = $this->extractMetaDescription($generatedContent);

            wp_send_json_success([
                'content' => $generatedContent,
                'title' => $title,
                'metaDescription' => $metaDescription,
                'model' => $model,
                'wordCount' => str_word_count(strip_tags($generatedContent)),
                'params' => $params
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Content generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save generated content as WordPress post
     */
    public function saveContent()
    {
        try {
            // Check user permissions
            if (!current_user_can('publish_posts')) {
                wp_send_json_error(['message' => 'Insufficient permissions'], 403);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                wp_send_json_error(['message' => 'Invalid request data'], 400);
                return;
            }

            // Verify nonce if provided
            if (isset($input['nonce']) && !wp_verify_nonce($input['nonce'], 'fluentContentNonce')) {
                wp_send_json_error(['message' => 'Invalid security token'], 403);
                return;
            }

            $title = sanitize_text_field($input['title'] ?? '');
            $content = wp_kses_post($input['content'] ?? '');
            $status = sanitize_text_field($input['status'] ?? 'draft');
            $category = intval($input['category'] ?? 0);

            if (empty($title) || empty($content)) {
                wp_send_json_error(['message' => 'Title and content are required'], 400);
                return;
            }

            // Get settings for defaults
            $settings = get_option('fluent_content_settings', []);

            // Prepare post data
            $postData = [
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => $status,
                'post_type' => 'post',
                'post_author' => get_current_user_id(),
            ];

            // Add meta description if provided
            if (!empty($input['metaDescription'])) {
                $postData['meta_input'] = [
                    '_yoast_wpseo_metadesc' => sanitize_text_field($input['metaDescription'])
                ];
            }

            // Create the post
            $postId = wp_insert_post($postData);

            if (is_wp_error($postId)) {
                wp_send_json_error(['message' => 'Failed to save post: ' . $postId->get_error_message()], 500);
                return;
            }

            // Set category if provided
            if ($category > 0) {
                wp_set_post_categories($postId, [$category]);
            } elseif (!empty($settings['defaultCategory'])) {
                $defaultCategory = get_category_by_slug($settings['defaultCategory']);
                if ($defaultCategory) {
                    wp_set_post_categories($postId, [$defaultCategory->term_id]);
                }
            }

            // Get the created post
            $post = get_post($postId);

            wp_send_json_success([
                'message' => 'Post saved successfully',
                'postId' => $postId,
                'post' => [
                    'ID' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_status' => $post->post_status,
                    'edit_link' => get_edit_post_link($postId, 'raw')
                ]
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Failed to save content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate title from keywords
     */
    private function generateTitle($keywords, $params)
    {
        try {
            $prompt = "Generate a compelling, SEO-friendly blog post title (maximum 60 characters) for the following topic: {$keywords}";
            
            if (!empty($params['tone'])) {
                $prompt .= "\nTone: {$params['tone']}";
            }

            $response = $this->openAIService->makeApiRequest([
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional content writer. Generate only the title, nothing else.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 20,
                'temperature' => 0.7
            ]);

            if (!empty($response['choices'][0]['message']['content'])) {
                $title = trim($response['choices'][0]['message']['content']);
                // Remove quotes if present
                $title = trim($title, '"\'');
                return $title;
            }
        } catch (\Exception $e) {
            // Fallback to simple title generation
        }

        // Fallback: create title from keywords
        $keywordsArray = explode(',', $keywords);
        $mainKeyword = trim($keywordsArray[0]);
        return ucwords($mainKeyword) . ' - Complete Guide';
    }

    /**
     * Extract meta description from content
     */
    private function extractMetaDescription($content)
    {
        // Remove HTML tags and get first 155 characters
        $text = wp_strip_all_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        
        if (strlen($text) > 155) {
            $text = substr($text, 0, 152) . '...';
        }
        
        return $text;
    }
}

