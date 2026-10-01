<?php
/**
 * Bankai Core - AI Provider Interface & Base Adapter
 *
 * Defines common adapter structure for AI providers:
 * OpenRouter, Google Gemini, OpenAI, Anthropic Claude, DeepSeek, Hugging Face, Groq, Cloudflare Workers AI
 *
 * @package Bankai
 */

if (!defined('ABSPATH')) {
    exit;
}

interface Bankai_AI_Provider_Interface {
    public function get_id(): string;
    public function get_name(): string;
    public function complete(string $prompt, array $options = []): array;
    public function validate_key(string $api_key): bool;
    public function list_models(): array;
    public function estimate_cost(int $prompt_tokens, int $completion_tokens): float;
}

abstract class Bankai_AI_Base_Adapter implements Bankai_AI_Provider_Interface {

    protected string $api_key = '';

    public function __construct(string $api_key = '') {
        $this->api_key = $api_key;
    }

    public function set_api_key(string $key): void {
        $this->api_key = $key;
    }

    protected function http_post(string $url, array $headers, array $body, int $timeout = 30): array {
        $response = wp_remote_post($url, [
            'headers' => array_merge(['Content-Type' => 'application/json'], $headers),
            'body'    => wp_json_encode($body),
            'timeout' => $timeout,
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error'   => $response->get_error_message(),
            ];
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($code < 200 || $code >= 300) {
            $msg = $data['error']['message'] ?? $data['message'] ?? 'HTTP ' . $code;
            return [
                'success' => false,
                'error'   => $msg,
                'code'    => $code,
            ];
        }

        return [
            'success' => true,
            'data'    => $data,
        ];
    }
}
