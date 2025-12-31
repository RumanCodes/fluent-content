<?php

namespace FluentContent\Http\Controllers;

use FluentContent\Services\OpenAIService;

class SettingsController
{
    /**
     * Get all settings
     */
    public function getSettings() {
        try {
            $settings = get_option('fluent_content_settings', $this->getDefaultSettings());

            // Don't expose the full API key for security
            if (!empty($settings['apiKey'])) {
                $settings['apiKey'] = $this->maskApiKey($settings['apiKey']);
                $settings['hasApiKey'] = true;
            } else {
                $settings['hasApiKey'] = false;
            }

            wp_send_json_success($settings);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Failed to load settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save settings
     */
    public function saveSettings() {
        try {
            // Check user permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Insufficient permissions'], 403);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['settings'])) {
                wp_send_json_error(['message' => 'Invalid request data'], 400);
                return;
            }

            $settings = $input['settings'];

            // Sanitize and validate settings
            $sanitized_settings = $this->sanitizeSettings($settings);

            // Get existing settings to preserve the actual API key if masked
            $existing_settings = get_option('fluent_content_settings', []);

            // If API key is masked, keep the existing one
            if ($this->isApiKeyMasked($sanitized_settings['apiKey'])) {
                $sanitized_settings['apiKey'] = $existing_settings['apiKey'] ?? '';
            }

            // Encrypt API key before storing
            if (!empty($sanitized_settings['apiKey'])) {
                $sanitized_settings['apiKey'] = $this->encryptApiKey($sanitized_settings['apiKey']);
            }

            // Save settings
            update_option('fluent_content_settings', $sanitized_settings);

            // Test connection if requested
            $connectionTest = null;
            if (!empty($settings['testConnection']) && !empty($sanitized_settings['apiKey'])) {
                $connectionTest = $this->testApiConnection($sanitized_settings);
            }

            wp_send_json_success([
                'message' => 'Settings saved successfully',
                'connectionTest' => $connectionTest
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test API connection
     */
    public function testConnection() {
        try {

            $settings = get_option('fluent_content_settings', []);

            if (empty($settings['apiKey'])) {
                wp_send_json_error(['message' => 'API key not configured'], 400);
                return;
            }

            $result = $this->testApiConnection($settings);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result, 400);
            }

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform API connection test
     */
    private function testApiConnection($settings) {
        $apiKey = $this->decryptApiKey($settings['apiKey']);
        $provider = $settings['apiProvider'] ?? 'openai-gpt4';

        try {
            if ($provider === 'openai-gpt4') {
                // Use the saved model or try a quick test with gpt-3.5-turbo first
                $model = $settings['model'] ?? 'gpt-3.5-turbo';

                $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode([
                        'model' => $model,
                        'messages' => [
                            ['role' => 'user', 'content' => 'Hi']
                        ],
                        'max_tokens' => 5
                    ]),
                    'timeout' => 15, // Reduced from 60 to 15 seconds
                    'sslverify' => true,
                    'httpversion' => '1.1'
                ]);

                if (is_wp_error($response)) {
                    $error_msg = $response->get_error_message();

                    // Provide more helpful error messages
                    if (strpos($error_msg, 'cURL error 28') !== false || strpos($error_msg, 'Timeout') !== false) {
                        return [
                            'success' => false,
                            'message' => 'Connection timeout. This could be due to: firewall blocking outgoing connections, slow server, or server configuration issues. Please check your server\'s firewall settings and ensure it can make outgoing HTTPS requests.',
                            'technical' => $error_msg
                        ];
                    }

                    if (strpos($error_msg, 'cURL error 6') !== false || strpos($error_msg, 'Could not resolve host') !== false) {
                        return [
                            'success' => false,
                            'message' => 'DNS resolution failed. Your server cannot resolve api.openai.com. Please check your server\'s DNS settings.',
                            'technical' => $error_msg
                        ];
                    }

                    if (strpos($error_msg, 'cURL error 7') !== false) {
                        return [
                            'success' => false,
                            'message' => 'Failed to connect to OpenAI API. Your server may be blocking outgoing connections or the OpenAI API may be temporarily unavailable.',
                            'technical' => $error_msg
                        ];
                    }

                    return [
                        'success' => false,
                        'message' => 'Connection failed: ' . $error_msg,
                        'suggestion' => 'Contact your hosting provider to ensure outgoing HTTPS connections are allowed.'
                    ];
                }

                $status_code = wp_remote_retrieve_response_code($response);
                $body = json_decode(wp_remote_retrieve_body($response), true);

                if ($status_code === 200) {
                    // Save the working model for future use
                    $detectedModel = $body['model'] ?? $model;
                    $settings['model'] = $detectedModel;
                    update_option('fluent_content_settings', $settings);

                    return [
                        'success' => true,
                        'message' => 'Connection successful! API is working correctly.',
                        'model' => $detectedModel
                    ];
                } else {
                    $error_message = $body['error']['message'] ?? 'Unknown error';
                    $error_code = $body['error']['code'] ?? '';

                    // If it's a model access error, try gpt-3.5-turbo as fallback
                    if (strpos($error_message, 'does not exist') !== false ||
                        strpos($error_message, 'do not have access') !== false ||
                        strpos($error_message, 'model_not_found') !== false) {
                        
                        // Quick fallback to gpt-3.5-turbo without full detection
                        if ($model !== 'gpt-3.5-turbo') {
                            $fallbackResponse = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $apiKey,
                                    'Content-Type' => 'application/json',
                                ],
                                'body' => json_encode([
                                    'model' => 'gpt-3.5-turbo',
                                    'messages' => [
                                        ['role' => 'user', 'content' => 'Hi']
                                    ],
                                    'max_tokens' => 5
                                ]),
                                'timeout' => 15,
                                'sslverify' => true,
                                'httpversion' => '1.1'
                            ]);

                            if (!is_wp_error($fallbackResponse)) {
                                $fallbackStatus = wp_remote_retrieve_response_code($fallbackResponse);
                                if ($fallbackStatus === 200) {
                                    $settings['model'] = 'gpt-3.5-turbo';
                                    update_option('fluent_content_settings', $settings);
                                    return [
                                        'success' => true,
                                        'message' => 'Connection successful! Using gpt-3.5-turbo model.',
                                        'model' => 'gpt-3.5-turbo'
                                    ];
                                }
                            }
                        }
                    }

                    // Handle specific error codes
                    if ($error_code === 'invalid_api_key' || strpos($error_message, 'Invalid API key') !== false) {
                        return [
                            'success' => false,
                            'message' => 'Invalid API key. Please check your OpenAI API key.'
                        ];
                    }

                    if ($error_code === 'insufficient_quota' || strpos($error_message, 'quota') !== false) {
                        return [
                            'success' => false,
                            'message' => 'API quota exceeded. Please check your OpenAI account billing.'
                        ];
                    }

                    return [
                        'success' => false,
                        'message' => 'API Error: ' . $error_message
                    ];
                }
            }

            // Add support for other providers here
            return [
                'success' => false,
                'message' => 'Provider not yet implemented'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Detect available OpenAI model (deprecated - using simpler approach)
     * This method is kept for backward compatibility but not used in test connection
     * to avoid long timeouts
     */
    private function detectAvailableModel($apiKey) {
        // Try models in order of preference with shorter timeout
        $modelsToTry = [
            'gpt-4-turbo-preview',
            'gpt-4-turbo',
            'gpt-4',
            'gpt-3.5-turbo'
        ];

        foreach ($modelsToTry as $model) {
            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Hi']
                    ],
                    'max_tokens' => 5
                ]),
                'timeout' => 10, // Reduced timeout for detection
                'sslverify' => true,
                'httpversion' => '1.1'
            ]);

            if (!is_wp_error($response)) {
                $status_code = wp_remote_retrieve_response_code($response);
                if ($status_code === 200) {
                    return $model;
                }
            }
        }

        // Default to gpt-3.5-turbo if nothing works
        return 'gpt-3.5-turbo';
    }

    /**
     * Get default settings
     */
    private function getDefaultSettings() {
        return [
            'apiProvider' => 'openai-gpt4',
            'apiKey' => '',
            'model' => 'gpt-3.5-turbo', // Default to most accessible model
            'testConnection' => false,
            'wordCount' => '1000',
            'tone' => 'professional',
            'language' => 'english',
            'creativity' => 'medium',
            'autoGenerateTitle' => true,
            'autoGenerateMeta' => true,
            'autoAddImages' => false,
            'defaultCategory' => '',
            'defaultAuthor' => 'admin',
            'postStatus' => 'draft',
            'allowComments' => true,
            'sendEmailNotification' => true,
            'timezone' => 'utc',
            'retryAttempts' => 3,
            'retryInterval' => 15,
            'removeAfterSuccess' => false
        ];
    }

    /**
     * Sanitize settings
     */
    private function sanitizeSettings($settings) {
        return [
            'apiProvider' => sanitize_text_field($settings['apiProvider'] ?? 'openai-gpt4'),
            'apiKey' => sanitize_text_field($settings['apiKey'] ?? ''),
            'model' => sanitize_text_field($settings['model'] ?? 'gpt-3.5-turbo'),
            'testConnection' => (bool)($settings['testConnection'] ?? false),
            'wordCount' => sanitize_text_field($settings['wordCount'] ?? '1000'),
            'tone' => sanitize_text_field($settings['tone'] ?? 'professional'),
            'language' => sanitize_text_field($settings['language'] ?? 'english'),
            'creativity' => sanitize_text_field($settings['creativity'] ?? 'medium'),
            'autoGenerateTitle' => (bool)($settings['autoGenerateTitle'] ?? true),
            'autoGenerateMeta' => (bool)($settings['autoGenerateMeta'] ?? true),
            'autoAddImages' => (bool)($settings['autoAddImages'] ?? false),
            'defaultCategory' => sanitize_text_field($settings['defaultCategory'] ?? ''),
            'defaultAuthor' => sanitize_text_field($settings['defaultAuthor'] ?? 'admin'),
            'postStatus' => sanitize_text_field($settings['postStatus'] ?? 'draft'),
            'allowComments' => (bool)($settings['allowComments'] ?? true),
            'sendEmailNotification' => (bool)($settings['sendEmailNotification'] ?? true),
            'timezone' => sanitize_text_field($settings['timezone'] ?? 'utc'),
            'retryAttempts' => intval($settings['retryAttempts'] ?? 3),
            'retryInterval' => intval($settings['retryInterval'] ?? 15),
            'removeAfterSuccess' => (bool)($settings['removeAfterSuccess'] ?? false)
        ];
    }

    /**
     * Encrypt API key
     */
    private function encryptApiKey($apiKey) {
        if (empty($apiKey)) {
            return '';
        }

        // Use WordPress salts for encryption
        $key = wp_salt('auth');
        $iv = substr(wp_salt('secure_auth'), 0, 16);

        return base64_encode(openssl_encrypt($apiKey, 'AES-256-CBC', $key, 0, $iv));
    }

    /**
     * Decrypt API key
     */
    private function decryptApiKey($encryptedKey) {
        if (empty($encryptedKey)) {
            return '';
        }

        $key = wp_salt('auth');
        $iv = substr(wp_salt('secure_auth'), 0, 16);

        return openssl_decrypt(base64_decode($encryptedKey), 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Mask API key for display
     */
    private function maskApiKey($apiKey) {
        $decrypted = $this->decryptApiKey($apiKey);
        if (strlen($decrypted) <= 8) {
            return str_repeat('*', strlen($decrypted));
        }
        return substr($decrypted, 0, 4) . str_repeat('*', strlen($decrypted) - 8) . substr($decrypted, -4);
    }

    /**
     * Check if API key is masked
     */
    private function isApiKeyMasked($apiKey) {
        return strpos($apiKey, '*') !== false;
    }
}
