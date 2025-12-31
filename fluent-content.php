<?php

if ( ! defined('ABSPATH')) {
    exit;
}
/**
 * WordPress - Fluent Content
 *
 * Plugin Name:         Fluent Content
 * Plugin URI:          https://wordpress.org/plugins/fluent-content
 * Description:         Content Generation with AI
 * Version:             1.1.1
 * Requires at least:   5.2
 * Requires PHP:        7.2
 * Contributor:         Contributor according to the WordPress.org
 * Author:              WPManageNinja LLC
 * Author URI:          https://suitepress.org/fluent-content
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         fluent-content
 * Domain Path:         /languages
 */
require_once __DIR__ . '/vendor/autoload.php';

use FluentContent\App;

if ( class_exists( 'FluentContent\App' ) ) {
    $app = new App();
}
