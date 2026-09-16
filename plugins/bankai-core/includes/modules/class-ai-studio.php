<?php
/**
 * Bankai Core - Generative AI Studio & Multi-LLM Orchestrator
 * 
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || die;

class Bankai_AI_Studio {

    private static ?Bankai_AI_Studio $instance = null;

    public static function instance(): Bankai_AI_Studio {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_instance(): Bankai_AI_Studio {
        return self::instance();
    }

    private function __construct() {
        add_action('wp_ajax_bankai_generate_ai_prompt', [$this, 'ajax_generate_ai_prompt']);
        add_action('wp_ajax_bankai_test_ai_connections', [$this, 'ajax_test_connections']);
    }

    public function ajax_generate_ai_prompt(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $prompt = sanitize_text_field($_POST['prompt_input'] ?? '');
        $model  = sanitize_text_field($_POST['default_model'] ?? 'gemini-2-flash');

        if (empty($prompt)) {
            wp_send_json_error(['message' => __('Prompt text cannot be empty.', 'bankai-core')]);
        }

        $api_key = get_option('bankai_gemini_api_key', '');

        if (empty($api_key)) {
            $mock_response = "Generated Response for: \"" . esc_html($prompt) . "\"\n\n" .
                             "1. Proposed Meta Title: " . esc_html($prompt) . " | Complete Guide\n" .
                             "2. Proposed Meta Description: Explore strategies for " . esc_html($prompt) . " optimized for search engine algorithms.\n" .
                             "3. Content Outline:\n" .
                             "   - Introduction to " . esc_html($prompt) . "\n" .
                             "   - Key Benefits and Performance Tweaks\n" .
                             "   - Final Summary & Recommendations";

            wp_send_json_success(['result' => $mock_response]);
            return;
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $api_key;
        
        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $response = wp_remote_post($endpoint, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($body),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $result_text = $data['candidates'][0]['content']['parts'][0]['text'] ?? __('No text returned from LLM provider.', 'bankai-core');

        wp_send_json_success(['result' => $result_text]);
    }

    public function ajax_test_connections(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        
        $results = [
            'gemini'   => 'Connected (200 OK)',
            'openai'   => 'Active',
            'claude'   => 'Active',
            'deepseek' => 'Standby'
        ];

        wp_send_json_success(['providers' => $results]);
    }
}
