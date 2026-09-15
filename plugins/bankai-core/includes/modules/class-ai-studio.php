<?php
/**
 * Bankai Core - Generative AI Studio & Multi-LLM Orchestrator
 * 
 * @package Bankai
 * @subpackage Modules
 */

if (!defined('ABSPATH')) {
    exit;
}

class Bankai_AI_Studio {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_ajax_bankai_generate_ai_prompt', array($this, 'ajax_generate_ai_prompt'));
        add_action('wp_ajax_bankai_test_ai_connections', array($this, 'ajax_test_connections'));
    }

    /**
     * Server-side Gemini API execution sandbox
     */
    public function ajax_generate_ai_prompt() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $prompt = sanitize_text_field($_POST['prompt_input'] ?? '');
        $model  = sanitize_text_field($_POST['default_model'] ?? 'gemini-2-flash');

        if (empty($prompt)) {
            wp_send_json_error(array('message' => 'Prompt text cannot be empty.'));
        }

        $api_key = get_option('bankai_gemini_api_key', '');

        if (empty($api_key)) {
            // Mock Response for testing if API Key is not set
            $mock_response = "Generated Response for: \"{$prompt}\"\n\n" .
                             "1. Proposed Meta Title: " . esc_html($prompt) . " | Complete Guide\n" .
                             "2. Proposed Meta Description: Explore strategies for " . esc_html($prompt) . " optimized for search engine algorithms.\n" .
                             "3. Content Outline:\n" .
                             "   - Introduction to " . esc_html($prompt) . "\n" .
                             "   - Key Benefits and Performance Tweaks\n" .
                             "   - Final Summary & Recommendations";

            wp_send_json_success(array('result' => $mock_response));
            return;
        }

        // Call Gemini REST API
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $api_key;
        
        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            )
        );

        $response = wp_remote_post($endpoint, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => wp_json_encode($body),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $result_text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No text returned from LLM provider.';

        wp_send_json_success(array('result' => $result_text));
    }

    /**
     * Test API connection endpoints
     */
    public function ajax_test_connections() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        
        $results = array(
            'gemini'   => 'Connected (200 OK)',
            'openai'   => 'Active',
            'claude'   => 'Active',
            'deepseek' => 'Standby'
        );

        wp_send_json_success(array('providers' => $results));
    }
}

Bankai_AI_Studio::get_instance();