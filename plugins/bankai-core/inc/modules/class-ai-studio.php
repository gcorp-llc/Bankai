<?php
/**
 * Bankai Core - Multi-LLM AI Studio Engine
 *
 * Handles encrypted storage, multi-provider LLM orchestration,
 * REST API / AJAX endpoints, and SEO automated tasks.
 *
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || exit;

class Bankai_AI_Studio
{
    private static ?Bankai_AI_Studio $instance = null;

    public const OPT_KEYS = [
        'openai'     => 'bankai_openai_api_key',
        'anthropic'  => 'bankai_anthropic_api_key',
        'gemini'     => 'bankai_gemini_api_key',
        'deepseek'   => 'bankai_deepseek_api_key',
        'openrouter' => 'bankai_openrouter_api_key',
    ];

    public const OPT_MODELS  = 'bankai_ai_models';
    public const OPT_DEFAULT = 'bankai_ai_default_provider';
    public const OPT_MODULES = 'bankai_ai_modules_state';

    public static function instance(): Bankai_AI_Studio
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_instance(): Bankai_AI_Studio
    {
        return self::instance();
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('ai_studio')) {
            return;
        }

        // AJAX Handlers
        add_action('wp_ajax_bankai_generate_ai_prompt', [$this, 'ajax_generate']);
        add_action('wp_ajax_bankai_test_ai_connections', [$this, 'ajax_test_connections']);
        add_action('wp_ajax_bankai_save_ai_keys', [$this, 'ajax_save_keys']);
        add_action('wp_ajax_bankai_clear_ai_key', [$this, 'ajax_clear_key']);
        add_action('wp_ajax_bankai_save_ai_models', [$this, 'ajax_save_models']);
        add_action('wp_ajax_bankai_toggle_ai_module', [$this, 'ajax_toggle_module']);
        add_action('wp_ajax_bankai_ai_seo_task', [$this, 'ajax_seo_task']);

        // REST API
        add_action('rest_api_init', [$this, 'register_rest']);
    }

    /* ---------- REST Routes ---------- */

    public function register_rest(): void
    {
        register_rest_route('bankai/v1', '/ai/generate', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'rest_generate'],
            'permission_callback' => fn() => current_user_can('edit_posts'),
        ]);
        register_rest_route('bankai/v1', '/ai/seo-task', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'rest_seo_task'],
            'permission_callback' => fn() => current_user_can('edit_posts'),
        ]);
        register_rest_route('bankai/v1', '/ai/test', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'rest_test'],
            'permission_callback' => fn() => current_user_can('manage_options'),
        ]);
    }

    /* ---------- Encryption & Security Layer ---------- */

    private static function get_cipher_key(): string
    {
        $salt = defined('AUTH_KEY') ? AUTH_KEY : 'bankai_default_fallback_salt_sec_2026';
        return hash('sha256', $salt . wp_salt('auth'));
    }

    public static function encrypt_key(string $plain): string
    {
        $plain = trim($plain);
        if ($plain === '') {
            return '';
        }
        $cipher = 'aes-256-gcm';
        $key = self::get_cipher_key();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        $tag = '';
        $encrypted = openssl_encrypt($plain, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        if (false === $encrypted) {
            return '';
        }
        return base64_encode($iv . $tag . $encrypted);
    }

    public static function decrypt_key(string $encrypted_base64): string
    {
        $encrypted_base64 = trim($encrypted_base64);
        if ($encrypted_base64 === '') {
            return '';
        }
        $raw = base64_decode($encrypted_base64, true);
        if (!$raw) {
            return '';
        }
        $cipher = 'aes-256-gcm';
        $ivlen = openssl_cipher_iv_length($cipher);
        $taglen = 16;
        if (strlen($raw) < ($ivlen + $taglen)) {
            return '';
        }
        $iv = substr($raw, 0, $ivlen);
        $tag = substr($raw, $ivlen, $taglen);
        $ciphertext = substr($raw, $ivlen + $taglen);
        $key = self::get_cipher_key();
        $decrypted = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        return false !== $decrypted ? $decrypted : '';
    }

    /* ---------- Catalog & Settings ---------- */

    public static function providers_catalog(): array
    {
        return [
            'openai' => [
                'id'      => 'openai',
                'name'    => 'OpenAI',
                'tagline' => 'GPT-6 Astra · Sol · Codex',
                'accent'  => '#10A37F',
                'glow'    => 'rgba(16,163,127,.22)',
                'models'  => [
                    'gpt-6-astra',
                    'gpt-5.6-sol',
                    'gpt-5.6-terra',
                    'gpt-4o',
                    'gpt-4o-mini',
                    'o3-mini',
                ],
                'default' => 'gpt-5.6-sol',
                'docs'    => 'https://platform.openai.com/api-keys',
            ],
            'anthropic' => [
                'id'      => 'anthropic',
                'name'    => 'Anthropic Claude',
                'tagline' => 'Fable 5.1 · Opus 5 · Sonnet 5',
                'accent'  => '#D97706',
                'glow'    => 'rgba(217,119,6,.2)',
                'models'  => [
                    'claude-fable-5.1',
                    'claude-opus-5',
                    'claude-sonnet-5',
                    'claude-3-7-sonnet-latest',
                    'claude-3-5-haiku-latest',
                ],
                'default' => 'claude-sonnet-5',
                'docs'    => 'https://console.anthropic.com/settings/keys',
            ],
            'gemini' => [
                'id'      => 'gemini',
                'name'    => 'Google Gemini',
                'tagline' => '3.8 Flash · 3.1 Pro · Live',
                'accent'  => '#4285F4',
                'glow'    => 'rgba(66,133,244,.22)',
                'models'  => [
                    'gemini-3.8-flash',
                    'gemini-3.6-flash',
                    'gemini-3.1-pro',
                    'gemini-2.0-flash',
                    'gemini-2.0-flash-lite',
                ],
                'default' => 'gemini-3.8-flash',
                'docs'    => 'https://aistudio.google.com/apikey',
            ],
            'deepseek' => [
                'id'      => 'deepseek',
                'name'    => 'DeepSeek',
                'tagline' => 'V4 Pro · V4.1 Flash · R1',
                'accent'  => '#4F46E5',
                'glow'    => 'rgba(79,70,229,.22)',
                'models'  => [
                    'deepseek-v4-pro',
                    'deepseek-flash',
                    'deepseek-chat',
                    'deepseek-reasoner',
                ],
                'default' => 'deepseek-flash',
                'docs'    => 'https://platform.deepseek.com/api_keys',
            ],
            'openrouter' => [
                'id'      => 'openrouter',
                'name'    => 'OpenRouter',
                'tagline' => '200+ models · Auto Route',
                'accent'  => '#7C3AED',
                'glow'    => 'rgba(124,58,237,.22)',
                'models'  => [
                    'openrouter/auto',
                    'openai/gpt-5.6-sol',
                    'anthropic/claude-sonnet-5',
                    'google/gemini-3.8-flash',
                    'deepseek/deepseek-v4-pro',
                ],
                'default' => 'openrouter/auto',
                'docs'    => 'https://openrouter.ai/keys',
            ],
        ];
    }

    /**
     * دریافت کلید رمزگشایی‌شده یک ارائه‌دهنده خاص
     */
    public function get_key(string $provider): string
    {
        if (!array_key_exists($provider, self::OPT_KEYS)) {
            return '';
        }
        $enc = (string) get_option(self::OPT_KEYS[$provider], '');
        return self::decrypt_key($enc);
    }

    /**
     * دریافت تمامی کلیدهای API فعال و رمزگشایی‌شده
     */
    public function get_keys(): array
    {
        $out = [];
        foreach (self::OPT_KEYS as $id => $opt) {
            $enc = (string) get_option($opt, '');
            $out[$id] = self::decrypt_key($enc);
        }
        return $out;
    }

    public function get_selected_models(): array
    {
        $saved = get_option(self::OPT_MODELS, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        $out = [];
        foreach (self::providers_catalog() as $id => $meta) {
            $out[$id] = !empty($saved[$id]) ? (string) $saved[$id] : $meta['default'];
        }
        return $out;
    }

    /**
     * دریافت ارائه‌دهنده پیش‌فرض
     */
    public function get_default_provider(): string
    {
        $p = (string) get_option(self::OPT_DEFAULT, 'gemini');
        if (array_key_exists($p, self::OPT_KEYS) && !empty($this->get_key($p))) {
            return $p;
        }

        // اگر ارائه‌دهنده پیش‌فرض کلید نداشت، اولین ارائه‌دهنده دارای کلید را برمی‌گرداند
        foreach (self::OPT_KEYS as $id => $opt) {
            if (!empty($this->get_key($id))) {
                return $id;
            }
        }

        return 'gemini';
    }

    public static function mask_key(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return '';
        }
        $len = mb_strlen($key);
        if ($len <= 8) {
            return str_repeat('•', max(4, $len));
        }
        return mb_substr($key, 0, 4) . str_repeat('•', min(14, $len - 8)) . mb_substr($key, -4);
    }

    public function provider_status_list(): array
    {
        $keys = $this->get_keys();
        $models = $this->get_selected_models();
        $list = [];
        foreach (self::providers_catalog() as $id => $meta) {
            $has = !empty($keys[$id]);
            $list[] = [
                'id'         => $id,
                'name'       => $meta['name'],
                'models'     => $meta['models'],
                'model'      => $models[$id],
                'docs'       => $meta['docs'],
                'has_key'    => $has,
                'masked_key' => $has ? self::mask_key($keys[$id]) : '',
                'status'     => $has ? 'ready' : 'missing_key',
                'label'      => $has ? __('آماده (ذخیره‌شده)', 'bankai-core') : __('بدون کلید API', 'bankai-core'),
            ];
        }
        return $list;
    }

    /* ---------- AJAX Callbacks ---------- */

    public function ajax_save_keys(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز است.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $raw_key  = sanitize_text_field(wp_unslash($_POST['key'] ?? ''));
        $model    = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));

        if (!array_key_exists($provider, self::OPT_KEYS)) {
            wp_send_json_error(['message' => __('ارائه‌دهنده نامعتبر است.', 'bankai-core')], 400);
        }

        $opt_key = self::OPT_KEYS[$provider];

        if ($raw_key !== '' && $raw_key !== '__unchanged__' && !str_contains($raw_key, '•')) {
            $encrypted = self::encrypt_key($raw_key);
            update_option($opt_key, $encrypted, false);
        }

        if ($model !== '') {
            $models = get_option(self::OPT_MODELS, []);
            if (!is_array($models)) {
                $models = [];
            }
            $models[$provider] = $model;
            update_option(self::OPT_MODELS, $models, false);
        }

        if (isset($_POST['default_provider'])) {
            $dp = sanitize_key(wp_unslash($_POST['default_provider']));
            if (array_key_exists($dp, self::OPT_KEYS)) {
                update_option(self::OPT_DEFAULT, $dp, false);
            }
        }

        $current_key = $this->get_key($provider);
        $has_key = !empty($current_key);

        wp_send_json_success([
            'message'    => __('تنظیمات موتور با موفقیت ذخیره شد.', 'bankai-core'),
            'status'     => $has_key ? 'ready' : 'missing_key',
            'label'      => $has_key ? __('آماده (ذخیره‌شده)', 'bankai-core') : __('بدون کلید API', 'bankai-core'),
            'masked_key' => $has_key ? self::mask_key($current_key) : '',
        ]);
    }

    public function ajax_clear_key(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز است.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        if (array_key_exists($provider, self::OPT_KEYS)) {
            update_option(self::OPT_KEYS[$provider], '', false);
        }

        wp_send_json_success([
            'message' => __('کلید API پاکسازی شد.', 'bankai-core'),
            'status'  => 'missing_key',
            'label'   => __('بدون کلید API', 'bankai-core'),
        ]);
    }

    public function ajax_test_connections(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز است.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $results = $this->test_connections($provider !== '' ? $provider : null);

        wp_send_json_success([
            'message'   => __('تست اتصال انجام شد.', 'bankai-core'),
            'providers' => $results,
        ]);
    }

    public function ajax_toggle_module(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز است.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $mod_id = sanitize_key(wp_unslash($_POST['module_id'] ?? ''));
        $active = !empty($_POST['active']);

        $state = get_option(self::OPT_MODULES, []);
        if (!is_array($state)) {
            $state = [];
        }
        $state[$mod_id] = $active;
        update_option(self::OPT_MODULES, $state, false);

        wp_send_json_success(['message' => __('وضعیت ماژول به‌روز شد.', 'bankai-core')]);
    }

    /* ---------- API Ping & Testing Logic ---------- */

    public function test_connections(?string $only = null): array
    {
        $keys = $this->get_keys();
        $models = $this->get_selected_models();
        $out = [];

        foreach (self::providers_catalog() as $id => $meta) {
            if ($only && $only !== $id) {
                continue;
            }
            $key = $keys[$id] ?? '';
            if ($key === '') {
                $out[] = [
                    'id'     => $id,
                    'name'   => $meta['name'],
                    'status' => 'missing_key',
                    'label'  => __('بدون کلید API', 'bankai-core'),
                    'ok'     => false,
                    'detail' => __('کلید وارد نشده است.', 'bankai-core'),
                ];
                continue;
            }

            $check = $this->ping_provider($id, $key, $models[$id] ?? $meta['default']);
            $out[] = [
                'id'     => $id,
                'name'   => $meta['name'],
                'status' => $check['ok'] ? 'connected' : 'failed',
                'label'  => $check['ok'] ? __('اتصال موفق', 'bankai-core') : __('خطا در احراز هویت', 'bankai-core'),
                'ok'     => $check['ok'],
                'detail' => $check['detail'] ?? '',
            ];
        }
        return $out;
    }

    private function ping_provider(string $id, string $key, string $model): array
    {
        switch ($id) {
            case 'openai':
                $r = wp_remote_get('https://api.openai.com/v1/models', [
                    'headers' => ['Authorization' => 'Bearer ' . $key],
                    'timeout' => 10,
                ]);
                break;
            case 'anthropic':
                $r = wp_remote_get('https://api.anthropic.com/v1/models', [
                    'headers' => [
                        'x-api-key'         => $key,
                        'anthropic-version' => '2023-06-01',
                    ],
                    'timeout' => 10,
                ]);
                break;
            case 'gemini':
                $r = wp_remote_get(
                    'https://generativelanguage.googleapis.com/v1beta/models?key=' . rawurlencode($key),
                    ['timeout' => 12]
                );
                break;
            case 'deepseek':
                $r = wp_remote_get('https://api.deepseek.com/models', [
                    'headers' => ['Authorization' => 'Bearer ' . $key],
                    'timeout' => 10,
                ]);
                break;
            case 'openrouter':
                $r = wp_remote_get('https://openrouter.ai/api/v1/models', [
                    'headers' => ['Authorization' => 'Bearer ' . $key],
                    'timeout' => 10,
                ]);
                break;
            default:
                return ['ok' => false, 'detail' => 'Unknown Provider'];
        }

        if (is_wp_error($r)) {
            return ['ok' => false, 'detail' => $r->get_error_message()];
        }

        $code = (int) wp_remote_retrieve_response_code($r);
        if ($code >= 200 && $code < 300) {
            return ['ok' => true, 'detail' => 'HTTP ' . $code];
        }

        $body = wp_remote_retrieve_body($r);
        $json = json_decode($body, true);
        $msg = is_array($json) ? ($json['error']['message'] ?? $json['message'] ?? '') : '';
        return ['ok' => false, 'detail' => trim('HTTP ' . $code . ' ' . $msg)];
    }

    /* ---------- AI Prompt Generation Core ---------- */

    public function ajax_generate(): void
    {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز است.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $prompt   = sanitize_textarea_field(wp_unslash($_POST['prompt_input'] ?? ''));
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? $this->get_default_provider()));
        $model    = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));

        if ($prompt === '') {
            wp_send_json_error(['message' => __('دستور تولید (پرامپت) نباید خالی باشد.', 'bankai-core')]);
        }

        $result = $this->generate($prompt, $provider, $model);
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'result'   => $result,
            'provider' => $provider,
            'model'    => $model,
        ]);
    }

    public function generate(string $prompt, string $provider = '', string $model = '', string $system_prompt = ''): string|WP_Error
    {
        $keys = $this->get_keys();
        $models = $this->get_selected_models();
        $catalog = self::providers_catalog();

        $preferred = ($provider !== '' && array_key_exists($provider, self::OPT_KEYS))
            ? $provider
            : $this->get_default_provider();

        $order = [$preferred];
        foreach (['gemini', 'openai', 'anthropic', 'deepseek', 'openrouter'] as $fb) {
            if ($fb !== $preferred && !empty($keys[$fb])) {
                $order[] = $fb;
            }
        }

        $last_error = null;
        foreach ($order as $pid) {
            $key = $keys[$pid] ?? '';
            if ($key === '') {
                continue;
            }
            $use_model = ($pid === $preferred && $model !== '')
                ? $model
                : ($models[$pid] ?? $catalog[$pid]['default']);

            $result = match ($pid) {
                'openai', 'deepseek', 'openrouter' => $this->call_openai_compatible($pid, $key, $use_model, $prompt, $system_prompt),
                'anthropic' => $this->call_anthropic($key, $use_model, $prompt, $system_prompt),
                default => $this->call_gemini($key, $use_model, $prompt, $system_prompt),
            };

            if (!is_wp_error($result) && trim((string) $result) !== '') {
                return (string) $result;
            }
            $last_error = is_wp_error($result)
                ? $result
                : new WP_Error('empty', __('پاسخ خالی از مدل دریافت شد.', 'bankai-core'));
        }

        if ($last_error instanceof WP_Error) {
            return $last_error;
        }
        return new WP_Error(
            'no_key',
            __('هیچ کلید فعال API یافت نشد. از استودیو هوش مصنوعی یک کلید ذخیره کنید.', 'bankai-core')
        );
    }

    private function call_gemini(string $key, string $model, string $prompt, string $system = ''): string|WP_Error
    {
        $model = $model !== '' ? $model : 'gemini-3.8-flash';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($model) . ':generateContent?key=' . rawurlencode($key);

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature'     => 0.3,
                'maxOutputTokens' => 2048,
            ],
        ];

        if ($system !== '') {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $system]],
            ];
        }

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($payload),
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || !is_array($body)) {
            $err = $body['error']['message'] ?? __('خطا در پاسخ‌دهی گوگل جمینای', 'bankai-core');
            return new WP_Error('gemini_error', $err);
        }

        return (string) ($body['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }

    private function call_openai_compatible(string $provider, string $key, string $model, string $prompt, string $system = ''): string|WP_Error
    {
        $endpoint = match ($provider) {
            'deepseek'   => 'https://api.deepseek.com/v1/chat/completions',
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            default      => 'https://api.openai.com/v1/chat/completions',
        };

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $key,
        ];

        if ($provider === 'openrouter') {
            $headers['HTTP-Referer'] = home_url('/');
            $headers['X-Title']      = get_bloginfo('name');
        }

        $messages = [];
        if ($system !== '') {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $response = wp_remote_post($endpoint, [
            'headers' => $headers,
            'body'    => wp_json_encode([
                'model'       => $model,
                'messages'    => $messages,
                'temperature' => 0.3,
                'max_tokens'  => 2048,
            ]),
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code < 200 || $code >= 300 || !is_array($body)) {
            $err = $body['error']['message'] ?? sprintf(__('خطا در پاسخ‌دهی %s', 'bankai-core'), ucfirst($provider));
            return new WP_Error($provider . '_error', $err);
        }

        return (string) ($body['choices'][0]['message']['content'] ?? '');
    }

    private function call_anthropic(string $key, string $model, string $prompt, string $system = ''): string|WP_Error
    {
        $payload = [
            'model'      => $model,
            'max_tokens' => 2048,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        if ($system !== '') {
            $payload['system'] = $system;
        }

        $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code < 200 || $code >= 300 || !is_array($body)) {
            $err = $body['error']['message'] ?? __('خطا در پاسخ‌دهی کلود کلود', 'bankai-core');
            return new WP_Error('anthropic_error', $err);
        }

        $text = '';
        foreach ($body['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= (string) ($block['text'] ?? '');
            }
        }
        return $text;
    }

    /* ---------- SEO Automation Task Processing ---------- */

    public function ajax_seo_task(): void
    {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز است.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $task     = sanitize_key(wp_unslash($_POST['task'] ?? ''));
        $task = match ($task) {
            'title' => 'meta_title',
            'description', 'desc' => 'meta_description',
            'keyword', 'focus' => 'focus_keyword',
            default => $task,
        };
        $title    = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $content  = wp_kses_post(wp_unslash($_POST['content'] ?? ''));
        $keyword  = sanitize_text_field(wp_unslash($_POST['focus_keyword'] ?? ''));
        $locale   = sanitize_text_field(wp_unslash($_POST['locale'] ?? get_locale()));
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? $this->get_default_provider()));

        $out = $this->run_seo_task($task, [
            'title'         => $title,
            'content'       => $content,
            'focus_keyword' => $keyword,
            'locale'        => $locale,
            'provider'      => $provider,
        ]);

        if (is_wp_error($out)) {
            wp_send_json_error(['message' => $out->get_error_message()]);
        }

        wp_send_json_success($out);
    }

    public function rest_seo_task(\WP_REST_Request $request): \WP_REST_Response
    {
        $p = $request->get_json_params();
        if (!is_array($p)) {
            $p = [];
        }
        $out = $this->run_seo_task(sanitize_key((string) ($p['task'] ?? '')), [
            'title'         => sanitize_text_field((string) ($p['title'] ?? '')),
            'content'       => wp_kses_post((string) ($p['content'] ?? '')),
            'focus_keyword' => sanitize_text_field((string) ($p['focus_keyword'] ?? '')),
            'locale'        => sanitize_text_field((string) ($p['locale'] ?? get_locale())),
            'provider'      => sanitize_key((string) ($p['provider'] ?? $this->get_default_provider())),
        ]);
        if (is_wp_error($out)) {
            return new \WP_REST_Response(['success' => false, 'message' => $out->get_error_message()], 502);
        }
        return new \WP_REST_Response(array_merge(['success' => true], $out));
    }

    public function rest_generate(\WP_REST_Request $request): \WP_REST_Response
    {
        $p = $request->get_json_params();
        if (!is_array($p)) {
            $p = [];
        }
        $prompt = sanitize_textarea_field((string) ($p['prompt'] ?? ''));
        if ($prompt === '') {
            return new \WP_REST_Response(['success' => false, 'message' => 'Empty prompt'], 400);
        }
        $result = $this->generate(
            $prompt,
            sanitize_key((string) ($p['provider'] ?? '')),
            sanitize_text_field((string) ($p['model'] ?? '')),
            sanitize_textarea_field((string) ($p['system'] ?? ''))
        );
        if (is_wp_error($result)) {
            return new \WP_REST_Response(['success' => false, 'message' => $result->get_error_message()], 502);
        }
        return new \WP_REST_Response(['success' => true, 'result' => $result]);
    }

    public function rest_test(\WP_REST_Request $request): \WP_REST_Response
    {
        $p = $request->get_json_params();
        $only = is_array($p) ? sanitize_key((string) ($p['provider'] ?? '')) : '';
        return new \WP_REST_Response([
            'success'   => true,
            'providers' => $this->test_connections($only !== '' ? $only : null),
        ]);
    }

    public function run_seo_task(string $task, array $ctx): array|WP_Error
    {
        $allowed = ['meta_title', 'meta_description', 'focus_keyword', 'keywords', 'rewrite', 'outline', 'alt_text', 'faq_schema'];
        if (!in_array($task, $allowed, true)) {
            return new WP_Error('invalid_task', __('وظیفه سئو نامعتبر است.', 'bankai-core'));
        }

        $plain = wp_strip_all_tags((string) ($ctx['content'] ?? ''));
        $plain = mb_substr(preg_replace('/\s+/u', ' ', $plain) ?? '', 0, 4000);
        $title = (string) ($ctx['title'] ?? '');
        $kw    = (string) ($ctx['focus_keyword'] ?? '');
        $lang  = (string) ($ctx['locale'] ?? 'fa_IR');
        $is_fa = str_starts_with(strtolower($lang), 'fa');

        $system = $is_fa
            ? 'تو یک متخصص ارشد سئو و تولید محتوای وردپرس هستی. خروجی را دقیقاً مطابق خواسته و بدون مقدمه بفرست.'
            : 'You are an expert SEO specialist. Return only requested output, no preamble.';

        switch ($task) {
            case 'meta_title':
                $user = ($is_fa ? "یک عنوان سئو جذاب (حداکثر ۵۸ کاراکتر) بنویس.\nعنوان: {$title}\nکلید: {$kw}\nمحتوا: " : "Write one SEO title (max 58 chars).\nTitle: {$title}\nKW: {$kw}\nContent: ") . $plain;
                break;
            case 'meta_description':
                $user = ($is_fa ? "یک متا دیسکریپشن استاندارد سئو (۱۳۰ تا ۱۵۵ کاراکتر) بنویس شامل کلمه کلیدی.\nعنوان: {$title}\nکلید: {$kw}\nمحتوا: " : "Write an SEO meta description (130-155 chars) containing focus keyword.\nTitle: {$title}\nKW: {$kw}\nContent: ") . $plain;
                break;
            case 'focus_keyword':
                $user = ($is_fa ? "یک عبارت کلیدی اصلی کوتاه (۲ تا ۴ کلمه) برای این مطلب پیشنهاد بده.\nعنوان: {$title}\nمحتوا: " : "Suggest 1 short focus keyword (2-4 words).\nTitle: {$title}\nContent: ") . $plain;
                break;
            case 'keywords':
                $user = ($is_fa ? "۵ کلمه کلیدی ثانویه مرتبط را با کاما جدا کن.\nعنوان: {$title}\nکلید: {$kw}" : "5 secondary keywords comma-separated.\nTitle: {$title}\nKW: {$kw}");
                break;
            case 'outline':
                $user = ($is_fa ? "یک ساختار سرفصل H2 و H3 استاندارد تولید کن.\nعنوان: {$title}\nکلید: {$kw}" : "Generate an H2/H3 outline.\nTitle: {$title}\nKW: {$kw}");
                break;
            case 'rewrite':
                $user = ($is_fa ? "پاراگراف اول را برای خوانایی و سئو بازنویسی کن (حداکثر ۱۲۰ کلمه).\nمتن: " : "Rewrite opening paragraph for SEO (max 120 words).\nText: ") . mb_substr($plain, 0, 1000);
                break;
            case 'alt_text':
                $user = ($is_fa ? "متن alt کوتاه و دقیق برای تصویر شاخص بنویس.\nعنوان: {$title}\nکلید: {$kw}" : "Short image ALT text.\nTitle: {$title}\nKW: {$kw}");
                break;
            case 'faq_schema':
                $user = ($is_fa ? "۳ سوال و پاسخ متداول (FAQ) کوتاه تولید کن.\nعنوان: {$title}\nمحتوا: " : "Generate 3 FAQ Q&A pairs.\nTitle: {$title}\nContent: ") . $plain;
                break;
        }

        $provider = sanitize_key((string) ($ctx['provider'] ?? $this->get_default_provider()));
        $text = $this->generate($user, $provider, '', $system);

        if (is_wp_error($text)) {
            return $text;
        }

        $text = trim((string) $text);
        $text = preg_replace('/^```[a-z]*\s*|\s*```$/u', '', $text) ?? $text;
        $text = trim($text, " \t\n\r\0\x0B\"'");

        $payload = [
            'task'     => $task,
            'text'     => $text,
            'provider' => $provider,
        ];

        if ($task === 'meta_title') {
            $payload['seo_title'] = mb_substr($text, 0, 70);
        } elseif ($task === 'meta_description') {
            $payload['description'] = mb_substr($text, 0, 180);
        } elseif ($task === 'focus_keyword') {
            $payload['focus_keyword'] = mb_substr($text, 0, 80);
        } elseif ($task === 'keywords') {
            $parts = array_values(array_filter(array_map('trim', preg_split('/[,،\n]+/u', $text) ?: [])));
            $payload['keywords'] = array_slice($parts, 0, 10);
            $payload['text'] = implode('، ', $payload['keywords']);
        } elseif ($task === 'rewrite') {
            $payload['rewrite'] = $text;
        } elseif ($task === 'outline') {
            $payload['outline'] = $text;
        } elseif ($task === 'alt_text') {
            $payload['alt_text'] = mb_substr($text, 0, 120);
        } elseif ($task === 'faq_schema') {
            $payload['faq'] = $text;
        }

        return $payload;
    }
}