<?php
namespace FluentContent\Services;
class OpenAIService
{
    private $apiKey;
    private $model;
    private $settings;

    public function __construct() {
        $this->settings = get_option('fluent_content_settings', []);
        $this->apiKey = $this->decryptApiKey($this->settings['apiKey'] ?? '');
        $this->model = $this->settings['model'] ?? 'gpt-3.5-turbo';
    }

    /**
     * Get the current model
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * Generate blog post content
     */
    public function generateBlogPost($params) {
        $prompt = $this->buildBlogPostPrompt($params);

        // Use the model from settings, or detect available model
        $model = $this->settings['model'] ?? 'gpt-3.5-turbo';

        return $this->makeApiRequest([
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional content writer specialized in creating engaging, SEO-optimized blog posts.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $this->calculateMaxTokens($params['wordCount'] ?? 1000),
            'temperature' => $this->getTemperature($params['creativity'] ?? 'medium')
        ]);
    }

    /**
     * Build blog post prompt
     */
    private function buildBlogPostPrompt($params) {
        $keyword = $params['keyword'] ?? '';
        $wordCount = $params['wordCount'] ?? $this->settings['wordCount'] ?? 1000;
        $tone = $params['tone'] ?? $this->settings['tone'] ?? 'professional';
        $language = $params['language'] ?? $this->settings['language'] ?? 'english';

        $prompt = "Write a comprehensive blog post about: {$keyword}\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- Word count: approximately {$wordCount} words\n";
        $prompt .= "- Tone: {$tone}\n";
        $prompt .= "- Language: {$language}\n";
        $prompt .= "- Include an engaging introduction\n";
        $prompt .= "- Use headings and subheadings (H2, H3)\n";
        $prompt .= "- Include relevant examples and explanations\n";
        $prompt .= "- Add a compelling conclusion\n";
        $prompt .= "- Make it SEO-friendly\n\n";
        $prompt .= "Format the output in HTML with proper heading tags.";

        return $prompt;
    }

    /**
     * Make API request to OpenAI
     */
    public function makeApiRequest($data) {
        if (empty($this->apiKey)) {
            throw new \Exception('API key not configured. Please configure your OpenAI API key in settings.');
        }

        // Ensure model is set in the request
        if (empty($data['model'])) {
            $data['model'] = $this->model;
        }

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data),
            'timeout' => 120, // Increased timeout for content generation
            'sslverify' => true,
            'httpversion' => '1.1'
        ]);

        if (is_wp_error($response)) {
            $error_msg = $response->get_error_message();
            
            // Provide helpful error messages
            if (strpos($error_msg, 'cURL error 28') !== false || strpos($error_msg, 'Timeout') !== false) {
                throw new \Exception('Request timeout. The content generation is taking longer than expected. Please try again or reduce the word count.');
            }
            
            if (strpos($error_msg, 'cURL error 6') !== false) {
                throw new \Exception('DNS resolution failed. Please check your server\'s network connection.');
            }
            
            throw new \Exception('API request failed: ' . $error_msg);
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code !== 200) {
            $error_message = $body['error']['message'] ?? 'Unknown error';
            $error_code = $body['error']['code'] ?? '';
            
            // Handle specific error codes
            if ($error_code === 'invalid_api_key' || strpos($error_message, 'Invalid API key') !== false) {
                throw new \Exception('Invalid API key. Please check your OpenAI API key in settings.');
            }
            
            if ($error_code === 'insufficient_quota' || strpos($error_message, 'quota') !== false) {
                throw new \Exception('API quota exceeded. Please check your OpenAI account billing.');
            }
            
            if (strpos($error_message, 'rate_limit') !== false) {
                throw new \Exception('Rate limit exceeded. Please wait a moment and try again.');
            }
            
            throw new \Exception('OpenAI API Error: ' . $error_message);
        }

        return $body;
    }

    /**
     * Calculate max tokens based on word count
     */
    private function calculateMaxTokens($wordCount) {
        // Rough estimate: 1 token ≈ 0.75 words
        return intval($wordCount / 0.75 * 1.2); // Add 20% buffer
    }

    /**
     * Get temperature based on creativity level
     */
    private function getTemperature($creativity) {
        $temperatures = [
            'low' => 0.3,
            'medium' => 0.7,
            'high' => 0.9
        ];

        return $temperatures[$creativity] ?? 0.7;
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
}
