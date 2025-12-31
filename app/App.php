<?php
namespace FluentContent;

use FluentContent\Hooks\Handlers\AdminMenuHandlers;
use FluentContent\Hooks\Handlers\RegisterAjax;
use FluentContent\Hooks\Handlers\RestApiHandlers;
use FluentContent\Http\Controllers\DashboardController;
use FluentContent\Http\Controllers\SettingsController;

class App {

    public function __construct() {
        add_action('init', [$this, 'init']);
    }

    public function init() {

        define( 'FLUENT_CONTENT', 'plugins-migrator' );
        define( 'FLUENT_CONTENT_PATH', untrailingslashit( plugin_dir_path( __DIR__ ) ) );
        define( 'FLUENT_CONTENT_URL', untrailingslashit( plugin_dir_url( __DIR__ ) ) );
        define( 'FLUENT_CONTENT_BUILD_PATH', FLUENT_CONTENT_PATH . '/public/assets' );
        define( 'FLUENT_CONTENT_BUILD_URL', FLUENT_CONTENT_URL . '/public/assets' );
        define( 'FLUENT_CONTENT_VERSION', '1.1.1' );


       new AdminMenuHandlers();
       new DashboardController();
       new RegisterAjax();
    }
}
