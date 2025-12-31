<?php
namespace FluentContent\Hooks\Handlers;

class AdminMenuHandlers {

    public function __construct() {
        $this->init();
    }

    public function init(): void
    {
        add_action('admin_menu', [$this, 'fluent_content_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'fluent_content_conditionally_enqueue_assets']);
    }

    public function fluent_content_conditionally_enqueue_assets($hook): void {
        // Only load assets on our plugin page
        if (
            !str_contains($hook, 'fluent-content-dashboard') &&
            !str_contains($hook, 'generate-content') &&
            !str_contains($hook, 'scheduled-posts') &&
            !str_contains($hook, 'content-library') &&
            !str_contains($hook, 'integration') &&
            !str_contains($hook, 'brand-voice') &&
            !str_contains($hook, 'seo-automation') &&
            !str_contains($hook, 'settings')
        ) {
            return;
        }
        $this->fluent_content_enqueue_assets();
    }

    public function fluent_content_admin_menu(): void
    {

        add_menu_page(
            __('Fluent Content', 'fluent-content'),
            __('Fluent Content', 'fluent-content'),
            'manage_options',
            'fluent-content-dashboard',
            [$this, 'fluent_content_render_screen'],
            $this->fluent_content_get_menu_icon(),
            24
        );
        add_submenu_page(
            "fluent-content-dashboard",
            __("Dashboard","fluent-content"),
            __("Dashboard","fluent-content"),
            "manage_options",
            "fluent-content-dashboard",
            [$this, 'fluent_content_render_screen']
        );
        add_submenu_page(
            "fluent-content-dashboard",
            __("Generate Content","fluent-content"),
            __("Generate Content","fluent-content"),
            "manage_options",
            "generate-content",
            [$this, 'fluent_content_generate_content_screen']
        );
        add_submenu_page(
            "fluent-content-dashboard",
            __("Scheduled Posts","fluent-content"),
            __("Scheduled Posts","fluent-content"),
            "manage_options",
            "scheduled-posts",
            [$this, 'fluent_content_empty']
        );
        add_submenu_page(
            "fluent-content-dashboard",
            __("Content Library","fluent-content"),
            __("Content Library","fluent-content"),
            "manage_options",
            "content-library",
            [$this, 'fluent_content_empty']
        );
        add_submenu_page(
            "fluent-content-dashboard",
            __("Integration","fluent-content"),
            __("Integration","fluent-content"),
            "manage_options",
            "integration",
            [$this, 'fluent_content_empty']
        );
        add_submenu_page(
            "fluent-content-dashboard",
            __("Brand Voice","fluent-content"),
            __("Brand Voice","fluent-content"),
            "manage_options",
            "brand-voice",
            [$this, 'fluent_content_empty']
        );
        add_submenu_page(
            "fluent-content-dashboard",
            __("SEO Automation","fluent-content"),
            __("SEO Automation","fluent-content"),
            "manage_options",
            "seo-automation",
            [$this, 'fluent_content_empty']
        );
        add_submenu_page(
            "fluent-content-dashboard",
            __("Settings","fluent-content"),
            __("Settings","fluent-content"),
            "manage_options",
            "settings",
            [$this, 'fluent_content_settings']
        );
    }

    public function fluent_content_get_menu_icon(): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#f6fafd" d="M372.2 116C372.2 136.9 359.8 155 342 163.2L448 256L552.4 235.1C547.1 227.4 544 218 544 208C544 181.5 565.5 160 592 160C618.5 160 640 181.5 640 208C640 234 619.4 255.1 593.6 256L481 506.3C470.7 529.3 447.8 544 422.6 544L217.4 544C192.2 544 169.4 529.2 159 506.3L46.4 256C20.6 255.1 0 234 0 208C0 181.5 21.5 160 48 160C74.5 160 96 181.5 96 208C96 218.1 92.9 227.4 87.6 235.1L192 256L298.1 163.1C280.4 154.8 268.1 136.8 268.1 116C268.1 87.3 291.4 64 320.1 64C348.8 64 372.1 87.3 372.1 116L372.2 116z"/></svg>');
    }

    public function fluent_content_render_screen(): void
    {
        include_once FLUENT_CONTENT_PATH . '/app/Views/admin-dashboard.php';
    }
    public function fluent_content_generate_content_screen(): void
    {
        include_once FLUENT_CONTENT_PATH . '/app/Views/generateContent.php';
    }
    public function fluent_content_settings(): void
    {
        include_once FLUENT_CONTENT_PATH . '/app/Views/contentSettings.php';
    }
    public function fluent_content_empty(): void
    {
        include_once FLUENT_CONTENT_PATH . '/app/Views/404.php';
    }

    public function fluent_content_enqueue_assets(): void {

        $dev_server = 'http://localhost:5173';
        $hot_file_path = FLUENT_CONTENT_PATH . '/.hot';
        $is_dev = file_exists($hot_file_path);

        if ($is_dev) {
            // Enqueue Vite HMR client and main entry
            wp_enqueue_script('vite-client', $dev_server . '/@vite/client', [],null, true);
            wp_enqueue_script('fluent-content-vite', $dev_server . '/js/main.js',  [], null, true);

            wp_localize_script('fluent-content-vite', 'fluentContentObject', [
                'restUrl' => esc_url_raw(rest_url('suitepress/v1/plugin-stats')),
                'nonce'   => wp_create_nonce('fluentContentNonce'),
                'ajaxurl' => admin_url('admin-ajax.php'),
            ]);
        } else {
            // Prod: Use filetime for cache busting
            $main_js = FLUENT_CONTENT_BUILD_PATH . '/main.js';
            $main_css = FLUENT_CONTENT_BUILD_PATH . '/main.css';

            $js_version = file_exists($main_js) ? filemtime($main_js) : '1.0.0';
            $css_version = file_exists($main_css) ? filemtime($main_css) : '1.0.0';

            wp_enqueue_script('fluent-content-main', FLUENT_CONTENT_BUILD_URL . '/main.js', [], $js_version, true);
            wp_enqueue_style('fluent-content-style', FLUENT_CONTENT_BUILD_URL . '/main.css',[],$css_version);

            wp_localize_script('fluent-content-main', 'fluentContentObject', [
                'restUrl' => esc_url_raw(rest_url('suitepress/v1/plugin-stats')),
                'nonce'   => wp_create_nonce('fluentContentNonce'),
                'ajaxurl' => admin_url('admin-ajax.php'),
            ]);
        }

        // Optional: Add type="module" for both dev and prod
        add_filter('script_loader_tag', function ($tag, $handle) {
            if (in_array($handle, ['vite-client', 'fluent-content-vite', 'fluent-content-main'])) {
                $tag = str_replace('<script ', '<script type="module" ', $tag);
            }
            return $tag;
        }, 10, 2);
    }
}
