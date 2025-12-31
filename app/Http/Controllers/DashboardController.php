<?php
namespace FluentContent\Http\Controllers;

use FluentContent\Models\PostModel;

class DashboardController
{
    public function __construct()
    {
        add_action('wp_ajax_fluent_content_post_data',[$this, 'getPostData']);
    }
    public function getPostData(): void
    {

//        check_ajax_referer('fluentContentNonce', 'nonce');

        // Get all dashboard data
        $data = PostModel::getDashboardData();

        wp_send_json_success($data);
    }

}
