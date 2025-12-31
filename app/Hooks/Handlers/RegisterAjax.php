<?php

namespace FluentContent\Hooks\Handlers;

use FluentContent\Http\Controllers\SettingsController;
use FluentContent\Http\Controllers\ContentController;

class RegisterAjax
{
    public function __construct(){
        // Settings AJAX handlers
        add_action('wp_ajax_fluent_content_get_settings',[$this, 'getSettingsAjax']);
        add_action('wp_ajax_fluent_content_save_settings',[$this, 'saveSettingsAjax']);
        add_action('wp_ajax_fluent_content_test_connection',[$this, 'testConnectionAjax']);
        
        // Content generation AJAX handlers
        add_action('wp_ajax_fluent_content_generate',[$this, 'generateContentAjax']);
        add_action('wp_ajax_fluent_content_save_post',[$this, 'saveContentAjax']);
    }

    public function getSettingsAjax(): void
    {
        $controller = new SettingsController();
        $controller->getSettings();
    }
    
    public function saveSettingsAjax(): void
    {
        $controller = new SettingsController();
        $controller->saveSettings();
    }
    
    public function testConnectionAjax(): void
    {
        $controller = new SettingsController();
        $controller->testConnection();
    }

    public function generateContentAjax(): void
    {
        $controller = new ContentController();
        $controller->generateContent();
    }

    public function saveContentAjax(): void
    {
        $controller = new ContentController();
        $controller->saveContent();
    }
}
