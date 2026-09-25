<?php
/**
 * Bankai Core - AI Studio
 *
 * Multi-provider LLM client + SEO AI tasks (title, description, keywords, rewrite, …)
 *
 * @package Bankai
 */
if (!defined('ABSPATH')) {
    exit;
}

final class Bankai_AI_Studio
{
    private static ?self $instance = null;

    public const OPT_KEYS     = 'bankai_ai_keys';
    public const OPT_MODELS   = 'bankai_ai_models';
    public const OPT_DEFAULT  = 'bankai_ai_default_provider';
    public const OPT_MODULES  = 'bankai_ai_modules';
    public const OPT_CUSTOM   = 'bankai_ai_custom_providers';
    public const OPT_EXTRA_MODELS = 'bankai_ai_extra_models';

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('ai_studio')) {
            return;
        }

        add_action('wp_ajax_bankai_ai_seo_task', [$this, 'ajax_seo_task']);
        add_action('wp_ajax_bankai_ai_test_provider', [$this, 'ajax_test_provider']);
        add_action('wp_ajax_bankai_ai_save_keys', [$this, 'ajax_save_keys']);
        add_action('wp_ajax_bankai_ai_save_modules', [$this, 'ajax_save_modules']);

        // Aliases expected by AI Studio admin UI
        add_action('wp_ajax_bankai_save_ai_keys', [$this, 'ajax_save_ai_keys_ui']);
        add_action('wp_ajax_bankai_clear_ai_key', [$this, 'ajax_clear_ai_key']);
        add_action('wp_ajax_bankai_test_ai_connections', [$this, 'ajax_test_connections']);
        add_action('wp_ajax_bankai_generate_ai_prompt', [$this, 'ajax_generate_prompt']);
        add_action('wp_ajax_bankai_toggle_ai_module', [$this, 'ajax_toggle_ai_module']);
        add_action('wp_ajax_bankai_set_default_provider', [$this, 'ajax_set_default_provider']);
        add_action('wp_ajax_bankai_save_custom_provider', [$this, 'ajax_save_custom_provider']);
        add_action('wp_ajax_bankai_delete_custom_provider', [$this, 'ajax_delete_custom_provider']);
        add_action('wp_ajax_bankai_add_provider_model', [$this, 'ajax_add_provider_model']);
        add_action('wp_ajax_bankai_remove_provider_model', [$this, 'ajax_remove_provider_model']);
    }

    public static function mask_key(string $key): string
    {
        $key = trim($key);
        $len = strlen($key);
        if ($len <= 8) {
            return str_repeat('•', max(4, $len));
        }
        return substr($key, 0, 4) . str_repeat('•', min(12, $len - 8)) . substr($key, -4);
    }

    /* ------------------------------------------------------------------
     * Catalog & settings
     * ----------------------------------------------------------------*/

    public static function providers_catalog(): array
    {
        // Models verified against provider docs (Sep 2026)
        return [
            'openrouter' => [
                'id'        => 'openrouter',
                'name'      => 'OpenRouter',
                'label'     => 'OpenRouter',
                'tagline'   => 'گیت‌وی چندمدلی · free + Auto',
                'docs'      => 'https://openrouter.ai/keys',
                'models'    => [
                    'openrouter/auto',
                    'openrouter/free',
                    'qwen/qwen3.8-27b:free',
                    'google/gemini-3.8-flash',
                    'google/gemini-2.5-flash',
                    'openai/gpt-4o-mini',
                    'anthropic/claude-3.5-sonnet',
                    'deepseek/deepseek-chat',
                    'meta-llama/llama-3.3-70b-instruct',
                ],
                'default'   => 'openrouter/auto',
                'key_hint'  => 'sk-or-…',
                'accent'    => '#8B5CF6',
                'glow'      => 'rgba(139,92,246,.22)',
                'free_tier' => true,
                'order'     => 10,
            ],
            'gemini' => [
                'id'        => 'gemini',
                'name'      => 'Google Gemini',
                'label'     => 'Google Gemini',
                'tagline'   => '3.8 Flash · 3.6 Flash · رایگان',
                'docs'      => 'https://aistudio.google.com/apikey',
                'models'    => [
                    'gemini-3.8-flash',
                    'gemini-3.6-flash',
                    'gemini-3.5-flash',
                    'gemini-3.5-flash-lite',
                    'gemini-2.5-flash',
                    'gemini-2.5-pro',
                ],
                'default'   => 'gemini-3.8-flash',
                'key_hint'  => 'AIza…',
                'accent'    => '#4285F4',
                'glow'      => 'rgba(66,133,244,.22)',
                'free_tier' => true,
                'order'     => 20,
            ],
            'openai' => [
                'id'        => 'openai',
                'name'      => 'OpenAI',
                'label'     => 'OpenAI',
                'tagline'   => 'GPT-5 · GPT-4o · o4-mini',
                'docs'      => 'https://platform.openai.com/api-keys',
                'models'    => [
                    'gpt-5.4-mini',
                    'gpt-5.4',
                    'gpt-4.1',
                    'gpt-4.1-mini',
                    'gpt-4o',
                    'gpt-4o-mini',
                    'o4-mini',
                ],
                'default'   => 'gpt-4o-mini',
                'key_hint'  => 'sk-…',
                'accent'    => '#10A37F',
                'glow'      => 'rgba(16,163,127,.22)',
                'free_tier' => false,
                'order'     => 30,
            ],
            'anthropic' => [
                'id'        => 'anthropic',
                'name'      => 'Anthropic Claude',
                'label'     => 'Anthropic Claude',
                'tagline'   => 'Sonnet 4 · Haiku 4.5 · Opus',
                'docs'      => 'https://console.anthropic.com/settings/keys',
                'models'    => [
                    'claude-sonnet-4-20250514',
                    'claude-3-5-haiku-latest',
                    'claude-3-5-sonnet-latest',
                    'claude-3-haiku-20240307',
                ],
                'default'   => 'claude-3-5-haiku-latest',
                'key_hint'  => 'sk-ant-…',
                'accent'    => '#D97706',
                'glow'      => 'rgba(217,119,6,.22)',
                'free_tier' => false,
                'order'     => 40,
            ],
            'deepseek' => [
                'id'        => 'deepseek',
                'name'      => 'DeepSeek',
                'label'     => 'DeepSeek',
                'tagline'   => 'Chat · Reasoner',
                'docs'      => 'https://platform.deepseek.com/api_keys',
                'models'    => [
                    'deepseek-chat',
                    'deepseek-reasoner',
                ],
                'default'   => 'deepseek-chat',
                'key_hint'  => 'sk-…',
                'accent'    => '#4F46E5',
                'glow'      => 'rgba(79,70,229,.22)',
                'free_tier' => false,
                'order'     => 50,
            ],
            'huggingface' => [
                'id'        => 'huggingface',
                'name'      => 'Hugging Face',
                'label'     => 'Hugging Face',
                'tagline'   => 'Router · اعتبار رایگان',
                'docs'      => 'https://huggingface.co/settings/tokens',
                'models'    => [
                    'meta-llama/Llama-3.3-70B-Instruct',
                    'Qwen/Qwen2.5-72B-Instruct',
                    'google/gemma-2-27b-it',
                ],
                'default'   => 'meta-llama/Llama-3.3-70B-Instruct',
                'key_hint'  => 'hf_…',
                'accent'    => '#FFD21E',
                'glow'      => 'rgba(255,210,30,.25)',
                'free_tier' => true,
                'order'     => 60,
            ],
            'groq' => [
                'id'        => 'groq',
                'name'      => 'Groq',
                'label'     => 'Groq',
                'tagline'   => 'LPU · سریع',
                'docs'      => 'https://console.groq.com/keys',
                'models'    => [
                    'llama-3.3-70b-versatile',
                    'llama-3.1-8b-instant',
                    'openai/gpt-oss-20b',
                    'openai/gpt-oss-120b',
                    'gemma2-9b-it',
                ],
                'default'   => 'llama-3.3-70b-versatile',
                'key_hint'  => 'gsk_…',
                'accent'    => '#F55036',
                'glow'      => 'rgba(245,80,54,.22)',
                'free_tier' => true,
                'order'     => 70,
            ],
            'cloudflare' => [
                'id'        => 'cloudflare',
                'name'      => 'Cloudflare Workers AI',
                'label'     => 'Cloudflare Workers AI',
                'tagline'   => '۱۰٬۰۰۰ Neuron/روز رایگان',
                'docs'      => 'https://developers.cloudflare.com/workers-ai/',
                'models'    => [
                    '@cf/meta/llama-3.1-8b-instruct',
                    '@cf/meta/llama-3.1-70b-instruct',
                    '@cf/mistral/mistral-7b-instruct-v0.2',
                    '@cf/google/gemma-7b-it',
                ],
                'default'   => '@cf/meta/llama-3.1-8b-instruct',
                'key_hint'  => 'AccountID|APIToken',
                'accent'    => '#F6821F',
                'glow'      => 'rgba(246,130,31,.22)',
                'free_tier' => true,
                'order'     => 80,
            ],
        ];
    }


    /**
     * Built-in + user-defined custom providers.
     */
    public static function all_providers_catalog(): array
    {
        $base = self::providers_catalog();
        $custom = get_option(self::OPT_CUSTOM, []);
        if (!is_array($custom)) {
            $custom = [];
        }
        foreach ($custom as $id => $meta) {
            if (!is_array($meta) || $id === '') {
                continue;
            }
            $pid = sanitize_key((string) $id);
            if ($pid === '') {
                continue;
            }
            $models = [];
            if (!empty($meta['models']) && is_array($meta['models'])) {
                foreach ($meta['models'] as $m) {
                    $m = trim((string) $m);
                    if ($m !== '') {
                        $models[] = $m;
                    }
                }
            }
            $base[$pid] = [
                'id'          => $pid,
                'name'        => sanitize_text_field($meta['name'] ?? $pid),
                'label'       => sanitize_text_field($meta['name'] ?? $pid),
                'tagline'     => sanitize_text_field($meta['tagline'] ?? 'ایجنت سفارشی'),
                'docs'        => esc_url_raw($meta['docs'] ?? ''),
                'models'      => $models ?: ['default'],
                'default'     => sanitize_text_field($meta['default'] ?? ($models[0] ?? 'default')),
                'key_hint'    => sanitize_text_field($meta['key_hint'] ?? 'API Key'),
                'accent'      => sanitize_hex_color($meta['accent'] ?? '') ?: '#6b4eff',
                'glow'        => 'rgba(107,78,255,.22)',
                'free_tier'   => !empty($meta['free_tier']),
                'order'       => 900 + (int) ($meta['order'] ?? 0),
                'custom'      => true,
                'type'        => sanitize_key($meta['type'] ?? 'openai_compat'),
                'endpoint'    => esc_url_raw($meta['endpoint'] ?? ''),
                'auth_header' => sanitize_text_field($meta['auth_header'] ?? 'Authorization'),
                'auth_prefix' => sanitize_text_field($meta['auth_prefix'] ?? 'Bearer '),
                'extra_headers'=> is_array($meta['extra_headers'] ?? null) ? $meta['extra_headers'] : [],
            ];
        }

        // Merge extra models into built-in providers
        $extra = get_option(self::OPT_EXTRA_MODELS, []);
        if (is_array($extra)) {
            foreach ($extra as $pid => $list) {
                $pid = sanitize_key((string) $pid);
                if (!isset($base[$pid]) || !is_array($list)) {
                    continue;
                }
                $models = $base[$pid]['models'] ?? [];
                foreach ($list as $m) {
                    $m = trim((string) $m);
                    if ($m !== '' && !in_array($m, $models, true)) {
                        $models[] = $m;
                    }
                }
                $base[$pid]['models'] = $models;
            }
        }

        uasort($base, static function ($a, $b) {
            return ((int) ($a['order'] ?? 100)) <=> ((int) ($b['order'] ?? 100));
        });

        return $base;
    }

    public static function get_custom_providers_raw(): array
    {
        $custom = get_option(self::OPT_CUSTOM, []);
        return is_array($custom) ? $custom : [];
    }

    public static function encrypt_secret(string $plain): string
    {
        if ($plain === '') {
            return '';
        }

        if (!function_exists('sodium_crypto_secretbox')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning"><p>' . esc_html__('افزونه Sodium PHP روی سرور نصب نیست. کلیدهای AI با سیستم Fallback نگهداری می‌شوند.', 'bankai-core') . '</p></div>';
            });
            return $plain;
        }

        $auth_key = defined('AUTH_KEY') ? AUTH_KEY : 'bankai_default_auth_key';
        $secure_key = defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : 'bankai_default_secure_key';
        $secret_key = sodium_crypto_generichash($auth_key . $secure_key, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plain, $nonce, $secret_key);

        return 'bkenc:v1:' . base64_encode($nonce . $ciphertext);
    }

    public static function decrypt_secret(string $cipher): string
    {
        if ($cipher === '' || !str_starts_with($cipher, 'bkenc:v1:')) {
            return $cipher;
        }

        if (!function_exists('sodium_crypto_secretbox_open')) {
            return $cipher;
        }

        $auth_key = defined('AUTH_KEY') ? AUTH_KEY : 'bankai_default_auth_key';
        $secure_key = defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : 'bankai_default_secure_key';
        $secret_key = sodium_crypto_generichash($auth_key . $secure_key, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

        $decoded = base64_decode(substr($cipher, 9), true);
        if ($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return '';
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $secret_key);
        if ($decrypted === false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . esc_html__('رمزگشایی کلید API شکست خورد. لطفاً کلید را دوباره در بخش AI Studio وارد کنید.', 'bankai-core') . '</p></div>';
            });
            return '';
        }

        return $decrypted;
    }

    public static function get_config_key(string $provider): ?string
    {
        $const_name = 'BANKAI_' . strtoupper($provider) . '_KEY';
        if (defined($const_name) && is_string(constant($const_name)) && constant($const_name) !== '') {
            return constant($const_name);
        }
        return null;
    }

    public function get_keys(): array
    {
        $from_opt = get_option(self::OPT_KEYS, []);
        if (!is_array($from_opt)) {
            $from_opt = [];
        }

        $core = function_exists('bankai_get_option') ? bankai_get_option() : [];
        if (!is_array($core)) {
            $core = [];
        }

        $map = [
            'openai'       => 'openai_api_key',
            'anthropic'    => 'anthropic_api_key',
            'gemini'       => 'gemini_api_key',
            'deepseek'     => 'deepseek_api_key',
            'openrouter'   => 'openrouter_api_key',
            'groq'         => 'groq_api_key',
            'cloudflare'   => 'cloudflare_api_key',
            'huggingface'  => 'huggingface_api_key',
        ];

        $out = [];
        $needs_save = false;

        foreach ($map as $pid => $core_key) {
            $config_val = self::get_config_key($pid);
            if ($config_val !== null) {
                $out[$pid] = $config_val;
                continue;
            }

            $val = $from_opt[$pid] ?? ($core[$core_key] ?? '');
            if ($val === '' && !empty($core['ai_keys'][$pid])) {
                $val = $core['ai_keys'][$pid];
            }

            if (is_string($val) && $val !== '') {
                if (str_starts_with($val, 'bkenc:v1:')) {
                    $decrypted = self::decrypt_secret($val);
                    $out[$pid] = $decrypted;
                } else {
                    $encrypted = self::encrypt_secret($val);
                    if ($encrypted !== $val) {
                        if (self::decrypt_secret($encrypted) === $val) {
                            $from_opt[$pid] = $encrypted;
                            $needs_save = true;
                        }
                    }
                    $out[$pid] = $val;
                }
            } else {
                $out[$pid] = '';
            }
        }

        // Include custom / unknown provider keys stored in option
        foreach ($from_opt as $pid => $val) {
            $pid = sanitize_key((string) $pid);
            if ($pid === '' || isset($out[$pid])) {
                continue;
            }
            if (is_string($val) && $val !== '') {
                $out[$pid] = str_starts_with($val, 'bkenc:v1:') ? self::decrypt_secret($val) : $val;
            }
        }

        if ($needs_save) {
            update_option(self::OPT_KEYS, $from_opt, false);
        }

        return $out;
    }

    public function get_selected_models(): array
    {
        $models = get_option(self::OPT_MODELS, []);
        if (!is_array($models)) {
            $models = [];
        }
        $catalog = self::all_providers_catalog();
        foreach ($catalog as $pid => $info) {
            if (empty($models[$pid])) {
                $models[$pid] = $info['default'] ?? '';
            }
        }
        return $models;
    }

    public function get_default_provider(): string
    {
        $p = get_option(self::OPT_DEFAULT, '');
        if (!is_string($p) || $p === '') {
            $core = function_exists('bankai_get_option') ? bankai_get_option('active_ai_provider', 'openrouter') : 'openrouter';
            $p = is_string($core) && $core !== '' ? $core : 'openrouter';
        }
        $catalog = self::all_providers_catalog();
        if (isset($catalog[$p])) {
            return $p;
        }
        return array_key_first($catalog) ?: 'openrouter';
    }

    public function provider_status_list(): array
    {
        $keys    = $this->get_keys();
        $models  = $this->get_selected_models();
        $default = $this->get_default_provider();
        $list    = [];
        foreach (self::all_providers_catalog() as $pid => $info) {
            $has = !empty($keys[$pid]);
            $list[] = [
                'id'         => $pid,
                'name'       => $info['name'] ?? $info['label'],
                'label'      => $has ? __('کلید تنظیم شده', 'bankai-core') : __('بدون کلید API', 'bankai-core'),
                'status'     => $has ? 'ready' : 'missing_key',
                'has_key'    => $has,
                'masked_key' => $has ? self::mask_key($keys[$pid]) : '',
                'model'      => $models[$pid] ?? $info['default'],
                'models'     => $info['models'] ?? [],
                'is_default' => $pid === $default,
            ];
        }
        return $list;
    }

    /* ------------------------------------------------------------------
     * AJAX: SEO task
     * ----------------------------------------------------------------*/

    public function ajax_seo_task(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $task          = sanitize_key(wp_unslash($_POST['task'] ?? ''));
        $title         = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $content       = wp_strip_all_tags(wp_unslash($_POST['content'] ?? ''));
        $focus         = sanitize_text_field(wp_unslash($_POST['focus_keyword'] ?? ''));
        $locale        = sanitize_text_field(wp_unslash($_POST['locale'] ?? 'fa_IR'));
        $provider      = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $img_context   = sanitize_text_field(wp_unslash($_POST['image_context'] ?? $_POST['context'] ?? ''));
        $img_url       = esc_url_raw(wp_unslash($_POST['image_url'] ?? $_POST['src'] ?? ''));
        $articles_data = wp_unslash($_POST['articles'] ?? $_POST['site_articles'] ?? '');

        if ($provider === '') {
            $provider = $this->get_default_provider();
        }

        $content = mb_substr(preg_replace('/\s+/u', ' ', $content), 0, 8000);
        $is_fa   = (stripos($locale, 'fa') !== false || stripos($locale, 'persian') !== false);

        $extra = [
            'img_context'   => $img_context,
            'img_url'       => $img_url,
            'articles_data' => $articles_data,
        ];

        $prompts = $this->build_seo_prompts($task, $title, $content, $focus, $is_fa, $extra);
        if ($prompts === null) {
            wp_send_json_error(['message' => 'Unknown task: ' . $task], 400);
        }

        try {
            $raw = $this->chat($provider, $prompts['system'], $prompts['user'], [
                'temperature' => $prompts['temperature'] ?? 0.55,
                'max_tokens'  => $prompts['max_tokens'] ?? 800,
            ]);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }

        $data = $this->parse_seo_response($task, $raw, $focus, $is_fa);
        wp_send_json_success($data);
    }

    private function build_seo_prompts(string $task, string $title, string $content, string $focus, bool $is_fa, array $extra = []): ?array
    {
        $lang = $is_fa ? 'Persian (Farsi)' : 'English';
        $snippet = $content !== '' ? mb_substr($content, 0, 3500) : '(empty)';

        $base_sys = "You are Bankai SEO Agent — a senior SEO strategist and copywriter for WordPress content. "
            . "Always respond in {$lang}. Follow search intent, include the focus keyword naturally, avoid keyword stuffing, "
            . "and never invent facts not supported by the content. Do not use markdown fences unless the task explicitly requires JSON.";

        switch ($task) {
            case 'meta_title':
            case 'title':
                return [
                    'system' => $base_sys . ' Output ONLY the SEO title text, nothing else. Max 60 characters.',
                    'user'   => "Write an optimized SEO meta title for this article.\nFocus keyword: {$focus}\nArticle title: {$title}\nContent excerpt:\n{$snippet}",
                    'temperature' => 0.5,
                    'max_tokens'  => 80,
                ];

            case 'meta_description':
            case 'description':
                return [
                    'system' => $base_sys . ' Output ONLY the meta description, nothing else. 120–155 characters. Include the focus keyword naturally.',
                    'user'   => "Write an optimized SEO meta description.\nFocus keyword: {$focus}\nArticle title: {$title}\nContent excerpt:\n{$snippet}",
                    'temperature' => 0.55,
                    'max_tokens'  => 120,
                ];

            case 'focus_keyword':
                return [
                    'system' => $base_sys . ' Output ONLY one primary focus keyword/phrase (2–5 words). No quotes, no explanation.',
                    'user'   => "Suggest the best primary focus keyword for this article.\nTitle: {$title}\nContent:\n{$snippet}",
                    'temperature' => 0.3,
                    'max_tokens'  => 40,
                ];

            case 'keywords':
                return [
                    'system' => $base_sys . ' Output a JSON array of 8–12 secondary keywords (strings only). Example: ["kw1","kw2"]. No markdown fences.',
                    'user'   => "Suggest secondary SEO keywords related to this article.\nPrimary focus: {$focus}\nTitle: {$title}\nContent:\n{$snippet}",
                    'temperature' => 0.45,
                    'max_tokens'  => 300,
                ];

            case 'rewrite':
                return [
                    'system' => $base_sys . ' Rewrite the article to be clearer, more engaging, and SEO-friendly. Keep the same language and approximate length. Output only the rewritten body text.',
                    'user'   => "Rewrite this article.\nTitle: {$title}\nFocus: {$focus}\n\n{$snippet}",
                    'temperature' => 0.65,
                    'max_tokens'  => 2500,
                ];

            case 'outline':
                return [
                    'system' => $base_sys . ' Output a structured outline with H2/H3 headings only, one per line.',
                    'user'   => "Create an SEO content outline.\nTitle: {$title}\nFocus: {$focus}\nContent:\n{$snippet}",
                    'temperature' => 0.5,
                    'max_tokens'  => 600,
                ];

            case 'alt_text':
            case 'image_alt_text':
                $ctx_str = !empty($extra['img_context']) ? "\nSurrounding context: " . $extra['img_context'] : '';
                $url_str = !empty($extra['img_url']) ? "\nImage URL: " . $extra['img_url'] : '';
                return [
                    'system' => $base_sys . ' Output ONLY a short, descriptive image alt text (under 125 characters). No quotes, no explanation, no markdown.',
                    'user'   => "Suggest an alt text for an image inside this article.\nArticle Title: {$title}\nFocus Keyword: {$focus}{$ctx_str}{$url_str}\nContent excerpt:\n{$snippet}",
                    'temperature' => 0.4,
                    'max_tokens'  => 80,
                ];

            case 'smart_internal_links':
                $art_str = !empty($extra['articles_data']) ? (is_string($extra['articles_data']) ? $extra['articles_data'] : wp_json_encode($extra['articles_data'], JSON_UNESCAPED_UNICODE)) : '[]';
                return [
                    'system' => $base_sys . ' Output ONLY a valid JSON array of objects representing recommended internal link insertions pointing to published site articles. Each object MUST contain: "keyword" (anchor phrase found or relevant to insert in content), "target_post_id" (integer ID), "target_title" (string), "target_url" (permalink string), and "reason" (short 1-sentence explanation in ' . $lang . '). Output ONLY the JSON array without markdown fences.',
                    'user'   => "Analyze this article and recommend internal links to site articles.\nArticle Title: {$title}\nFocus Keyword: {$focus}\nArticle Snippet:\n{$snippet}\n\nAvailable site articles:\n{$art_str}",
                    'temperature' => 0.45,
                    'max_tokens'  => 800,
                ];

            default:
                return null;
        }
    }

    private function parse_seo_response(string $task, string $raw, string $focus, bool $is_fa): array
    {
        $text = trim($raw);
        // Strip common markdown fences
        $text = preg_replace('/^```(?:json|text)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        $out = ['text' => $text, 'task' => $task];

        switch ($task) {
            case 'meta_title':
            case 'title':
                $title = $this->clean_one_line($text, 70);
                $out['seo_title'] = $title;
                $out['text'] = $title;
                break;

            case 'meta_description':
            case 'description':
                $desc = $this->clean_one_line($text, 170);
                $out['description'] = $desc;
                $out['text'] = $desc;
                break;

            case 'focus_keyword':
                $fk = $this->clean_one_line($text, 80);
                $fk = trim($fk, " \t\"'«»");
                $out['focus_keyword'] = $fk;
                $out['text'] = $fk;
                break;

            case 'keywords':
                $fixed_raw = function_exists('bankai_get_option')
                    ? (string) bankai_get_option('seo_fixed_keywords', '')
                    : (string) get_option('bankai_seo_fixed_keywords', '');

                $fixed_list = [];
                if ($fixed_raw !== '') {
                    $parts = preg_split('/[\r\n,،]+/u', $fixed_raw);
                    foreach ($parts as $p) {
                        $p = sanitize_text_field(trim((string) $p));
                        if ($p !== '') {
                            $fixed_list[] = $p;
                        }
                    }
                    $fixed_list = array_values(array_unique($fixed_list));
                }

                $raw_kws = $this->extract_keyword_list($text, $focus);
                $merged = [];
                $seen = [];
                $structured = [];

                foreach ($raw_kws as $kw) {
                    $key = mb_strtolower($kw);
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $is_fixed = in_array($kw, $fixed_list, true);
                        $merged[] = $kw;
                        $structured[] = ['text' => $kw, 'is_fixed' => $is_fixed];
                    }
                }

                foreach ($fixed_list as $fkw) {
                    $key = mb_strtolower($fkw);
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $merged[] = $fkw;
                        $structured[] = ['text' => $fkw, 'is_fixed' => true];
                    }
                }

                $out['keywords'] = $merged;
                $out['fixed_keywords'] = $fixed_list;
                $out['keywords_structured'] = $structured;
                $out['text'] = implode($is_fa ? '، ' : ', ', $merged);
                break;

            case 'rewrite':
                $out['rewrite'] = $text;
                break;

            case 'outline':
                $out['outline'] = $text;
                break;

            case 'alt_text':
            case 'image_alt_text':
                $alt = $this->clean_one_line($text, 130);
                $out['alt_text'] = $alt;
                $out['text'] = $alt;
                break;

            case 'smart_internal_links':
                $links = [];
                if (preg_match('/\[[\s\S]*\]/u', $text, $m)) {
                    $decoded = json_decode($m[0], true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $item) {
                            if (is_array($item) && !empty($item['keyword']) && !empty($item['target_url'])) {
                                $links[] = [
                                    'keyword'        => sanitize_text_field($item['keyword']),
                                    'target_post_id' => absint($item['target_post_id'] ?? 0),
                                    'target_title'   => sanitize_text_field($item['target_title'] ?? ''),
                                    'target_url'     => esc_url_raw($item['target_url']),
                                    'reason'         => sanitize_text_field($item['reason'] ?? ''),
                                ];
                            }
                        }
                    }
                }
                $out['links'] = $links;
                break;
        }

        return $out;
    }

    private function clean_one_line(string $text, int $max): string
    {
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text, " \t\"'«»\n\r");
        // Drop leading labels like "Title:" / "عنوان:"
        $text = preg_replace('/^(title|seo title|meta title|description|meta description|keyword|focus keyword|عنوان|توضیحات|کلمه کلیدی)\s*[:：\-]\s*/iu', '', $text);
        if (mb_strlen($text) > $max) {
            $text = mb_substr($text, 0, $max - 1);
            // avoid cutting mid-word roughly
            $text = preg_replace('/\s+\S*$/u', '', $text);
        }
        return trim($text);
    }

    private function extract_keyword_list(string $text, string $focus): array
    {
        $keywords = [];

        // Try JSON array first
        if (preg_match('/\[[\s\S]*\]/u', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_string($item) && trim($item) !== '') {
                        $keywords[] = sanitize_text_field(trim($item));
                    }
                }
            }
        }

        if (!$keywords) {
            // Split by comma / Persian comma / newline / bullet
            $parts = preg_split('/[\n,،;•\-]+/u', $text);
            foreach ($parts as $p) {
                $p = trim(preg_replace('/^\d+[\.\)]\s*/', '', trim($p)));
                $p = trim($p, " \t\"'«»");
                if ($p !== '' && mb_strlen($p) < 60) {
                    $keywords[] = sanitize_text_field($p);
                }
            }
        }

        // Dedupe, drop focus if present as exact match, limit
        $seen = [];
        $clean = [];
        foreach ($keywords as $kw) {
            $key = mb_strtolower($kw);
            if (isset($seen[$key])) {
                continue;
            }
            if ($focus !== '' && mb_strtolower($focus) === $key) {
                continue;
            }
            $seen[$key] = true;
            $clean[] = $kw;
            if (count($clean) >= 12) {
                break;
            }
        }

        // Append site-wide fixed keywords if configured
        $fixed = $this->get_site_fixed_keywords();
        foreach ($fixed as $fk) {
            $key = mb_strtolower($fk);
            if (!isset($seen[$key]) && count($clean) < 15) {
                $seen[$key] = true;
                $clean[] = $fk;
            }
        }

        return $clean;
    }

    public function get_site_fixed_keywords(): array
    {
        $raw = function_exists('bankai_get_option')
            ? bankai_get_option('seo_fixed_keywords', '')
            : get_option('bankai_seo_fixed_keywords', '');

        if (is_array($raw)) {
            return array_values(array_filter(array_map('sanitize_text_field', $raw)));
        }
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $parts = preg_split('/[,،\n]+/u', $raw);
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $out[] = sanitize_text_field($p);
            }
        }
        return array_values(array_unique($out));
    }

    /* ------------------------------------------------------------------
     * Chat providers
     * ----------------------------------------------------------------*/

    /**
     * @throws Exception
     */
    public function chat(string $provider, string $system, string $user, array $opts = []): string
    {
        $keys   = $this->get_keys();
        $models = $this->get_selected_models();
        $key    = trim((string) ($keys[$provider] ?? ''));
        $model  = trim((string) ($models[$provider] ?? ''));

        if ($key === '') {
            foreach ($keys as $pid => $k) {
                if (is_string($k) && trim($k) !== '') {
                    $provider = $pid;
                    $key      = trim($k);
                    $model    = trim((string) ($models[$pid] ?? ''));
                    break;
                }
            }
        }

        if ($key === '') {
            throw new Exception('هیچ کلید API برای هوش مصنوعی تنظیم نشده است. از بخش AI Studio یک کلید وارد کنید.');
        }

        $temperature = isset($opts['temperature']) ? (float) $opts['temperature'] : 0.4;
        $max_tokens  = isset($opts['max_tokens']) ? (int) $opts['max_tokens'] : 1200;

        $catalog = self::all_providers_catalog();
        $fallback_models = [];
        if (isset($catalog[$provider]['models']) && is_array($catalog[$provider]['models'])) {
            $fallback_models = $catalog[$provider]['models'];
        }
        if ($model !== '') {
            array_unshift($fallback_models, $model);
        }
        $fallback_models = array_values(array_unique(array_filter($fallback_models)));

        $last_error = null;
        foreach ($fallback_models as $try_model) {
            try {
                return $this->dispatch_chat($provider, $key, $try_model, $system, $user, $temperature, $max_tokens);
            } catch (Throwable $e) {
                $last_error = $e;
                $msg = $e->getMessage();
                // Only fall through on model-not-found style errors
                if (!preg_match('/404|not found|no longer available|does not exist|invalid model|model_not_found/i', $msg)) {
                    throw $e;
                }
            }
        }

        throw $last_error ?: new Exception('پاسخی از سرویس هوش مصنوعی دریافت نشد.');
    }

    private function dispatch_chat(string $provider, string $key, string $model, string $system, string $user, float $temperature, int $max_tokens): string
    {
        $catalog = self::all_providers_catalog();
        $meta = $catalog[$provider] ?? [];
        if (!empty($meta['custom'])) {
            return $this->call_custom_provider($meta, $key, $model, $system, $user, $temperature, $max_tokens);
        }

        switch ($provider) {
            case 'gemini':
                return $this->call_gemini($key, $model ?: 'gemini-3.8-flash', $system, $user, $temperature, $max_tokens);
            case 'openai':
                return $this->call_openai_compat('https://api.openai.com/v1/chat/completions', $key, $model ?: 'gpt-4o-mini', $system, $user, $temperature, $max_tokens);
            case 'deepseek':
                return $this->call_openai_compat('https://api.deepseek.com/chat/completions', $key, $model ?: 'deepseek-chat', $system, $user, $temperature, $max_tokens);
            case 'huggingface':
                return $this->call_openai_compat('https://router.huggingface.co/v1/chat/completions', $key, $model ?: 'meta-llama/Llama-3.3-70B-Instruct', $system, $user, $temperature, $max_tokens);
            case 'groq':
                return $this->call_openai_compat('https://api.groq.com/openai/v1/chat/completions', $key, $model ?: 'llama-3.3-70b-versatile', $system, $user, $temperature, $max_tokens);
            case 'openrouter':
                return $this->call_openai_compat('https://openrouter.ai/api/v1/chat/completions', $key, $model ?: 'openrouter/auto', $system, $user, $temperature, $max_tokens, [
                    'HTTP-Referer' => home_url('/'),
                    'X-Title'      => 'Bankai SEO',
                ]);
            case 'cloudflare':
                return $this->call_cloudflare($key, $model ?: '@cf/meta/llama-3.1-8b-instruct', $system, $user, $temperature, $max_tokens);
            case 'anthropic':
                return $this->call_anthropic($key, $model ?: 'claude-3-5-haiku-latest', $system, $user, $temperature, $max_tokens);
            default:
                throw new Exception('Provider not supported: ' . $provider);
        }
    }

    private function call_custom_provider(array $meta, string $key, string $model, string $system, string $user, float $temp, int $max): string
    {
        $type = sanitize_key($meta['type'] ?? 'openai_compat');
        $endpoint = trim((string) ($meta['endpoint'] ?? ''));

        if ($type === 'gemini') {
            // If custom endpoint empty, use official Gemini
            if ($endpoint === '') {
                return $this->call_gemini($key, $model, $system, $user, $temp, $max);
            }
            // Custom Gemini-compatible endpoint
            $url = rtrim($endpoint, '/');
            if (!str_contains($url, ':generateContent')) {
                $url .= '/models/' . rawurlencode($model) . ':generateContent';
            }
            $body = [
                'systemInstruction' => ['parts' => [['text' => $system]]],
                'contents' => [['role' => 'user', 'parts' => [['text' => $user]]]],
                'generationConfig' => ['temperature' => $temp, 'maxOutputTokens' => max(64, $max)],
            ];
            $headers = ['x-goog-api-key' => $key];
            $extra = $meta['extra_headers'] ?? [];
            if (is_array($extra)) {
                foreach ($extra as $hk => $hv) {
                    $headers[sanitize_text_field((string) $hk)] = sanitize_text_field((string) $hv);
                }
            }
            $res = $this->http_json($url, $body, $headers);
            $text = $res['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($text === '') {
                throw new Exception($res['error']['message'] ?? 'Empty custom Gemini response');
            }
            return $text;
        }

        if ($type === 'anthropic') {
            if ($endpoint === '') {
                return $this->call_anthropic($key, $model, $system, $user, $temp, $max);
            }
            $headers = [
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
            ];
            $extra = $meta['extra_headers'] ?? [];
            if (is_array($extra)) {
                foreach ($extra as $hk => $hv) {
                    $headers[sanitize_text_field((string) $hk)] = sanitize_text_field((string) $hv);
                }
            }
            $body = [
                'model' => $model,
                'max_tokens' => max(64, $max),
                'temperature' => $temp,
                'system' => $system,
                'messages' => [['role' => 'user', 'content' => $user]],
            ];
            $res = $this->http_json($endpoint, $body, $headers);
            $text = $res['content'][0]['text'] ?? '';
            if ($text === '') {
                throw new Exception($res['error']['message'] ?? 'Empty custom Anthropic response');
            }
            return $text;
        }

        // Default: OpenAI-compatible chat completions
        if ($endpoint === '') {
            throw new Exception('آدرس Endpoint برای ایجنت سفارشی تنظیم نشده است.');
        }
        $auth_header = $meta['auth_header'] ?? 'Authorization';
        $auth_prefix = $meta['auth_prefix'] ?? 'Bearer ';
        $headers = [
            $auth_header => $auth_prefix . $key,
        ];
        $extra = $meta['extra_headers'] ?? [];
        if (is_array($extra)) {
            foreach ($extra as $hk => $hv) {
                $headers[sanitize_text_field((string) $hk)] = sanitize_text_field((string) $hv);
            }
        }
        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => $temp,
            'max_tokens' => max(64, $max),
        ];
        $res = $this->http_json($endpoint, $body, $headers);
        $text = $res['choices'][0]['message']['content'] ?? '';
        if (is_array($text)) {
            $joined = '';
            foreach ($text as $part) {
                if (is_string($part)) {
                    $joined .= $part;
                } elseif (is_array($part) && isset($part['text'])) {
                    $joined .= $part['text'];
                }
            }
            $text = $joined;
        }
        if ($text === '' || $text === null) {
            throw new Exception($res['error']['message'] ?? 'Empty custom provider response');
        }
        return (string) $text;
    }

    private function call_gemini(string $key, string $model, string $system, string $user, float $temp, int $max): string
    {
        $model = preg_replace('#^models/#', '', $model);
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
        $body = [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $user]]],
            ],
            'generationConfig' => [
                'temperature'     => $temp,
                'maxOutputTokens' => max(64, $max),
            ],
        ];
        $res = $this->http_json($url, $body, [
            'x-goog-api-key' => $key,
        ]);
        $text = $res['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '') {
            // blockReason / safety
            $reason = $res['candidates'][0]['finishReason'] ?? '';
            $err = $res['error']['message'] ?? ($reason ? ('Gemini finish: ' . $reason) : 'Empty Gemini response');
            throw new Exception($err);
        }
        return $text;
    }

    private function call_openai_compat(string $url, string $key, string $model, string $system, string $user, float $temp, int $max, array $extra_headers = []): string
    {
        $headers = array_merge([
            'Authorization' => 'Bearer ' . $key,
        ], $extra_headers);

        $body = [
            'model'    => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];

        // Newer OpenAI reasoning / GPT-5 family prefer max_completion_tokens
        if (preg_match('/^(gpt-5|gpt-6|o[1-9]|o4)/i', $model)) {
            $body['max_completion_tokens'] = max(64, $max);
        } else {
            $body['temperature'] = $temp;
            $body['max_tokens']  = max(64, $max);
        }

        $res = $this->http_json($url, $body, $headers);
        $text = $res['choices'][0]['message']['content'] ?? '';
        if (is_array($text)) {
            // Some models return content parts
            $joined = '';
            foreach ($text as $part) {
                if (is_string($part)) {
                    $joined .= $part;
                } elseif (is_array($part) && isset($part['text'])) {
                    $joined .= $part['text'];
                }
            }
            $text = $joined;
        }
        if ($text === '' || $text === null) {
            $err = $res['error']['message'] ?? ($res['error']['code'] ?? 'Empty OpenAI-compatible response');
            throw new Exception(is_string($err) ? $err : 'Empty AI response');
        }
        return (string) $text;
    }

    private function call_cloudflare(string $key, string $model, string $system, string $user, float $temp, int $max): string
    {
        $account = '';
        $token = $key;
        if (str_contains($key, '|')) {
            [$account, $token] = array_map('trim', explode('|', $key, 2));
        } else {
            $account = (string) get_option('bankai_cloudflare_account_id', '');
        }
        if ($account === '' || $token === '') {
            throw new Exception('Cloudflare نیاز به Account ID و API Token دارد (فرمت: AccountID|Token).');
        }
        $url = 'https://api.cloudflare.com/client/v4/accounts/' . rawurlencode($account) . '/ai/run/' . $model;
        $body = [
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'max_tokens' => $max,
        ];
        $res = $this->http_json($url, $body, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $text = $res['result']['response'] ?? ($res['result']['output'] ?? '');
        if (is_array($text)) {
            $text = wp_json_encode($text, JSON_UNESCAPED_UNICODE);
        }
        if ($text === '' || $text === null) {
            $err = $res['errors'][0]['message'] ?? 'Empty Cloudflare response';
            throw new Exception(is_string($err) ? $err : 'Cloudflare AI error');
        }
        return (string) $text;
    }

    private function call_anthropic(string $key, string $model, string $system, string $user, float $temp, int $max): string
    {
        $url = 'https://api.anthropic.com/v1/messages';
        $headers = [
            'x-api-key'         => $key,
            'anthropic-version' => '2023-06-01',
        ];
        $body = [
            'model'       => $model,
            'max_tokens'  => max(64, $max),
            'temperature' => $temp,
            'system'      => $system,
            'messages'    => [
                ['role' => 'user', 'content' => $user],
            ],
        ];
        $res = $this->http_json($url, $body, $headers);
        $text = $res['content'][0]['text'] ?? '';
        if ($text === '') {
            $err = $res['error']['message'] ?? 'Empty Anthropic response';
            throw new Exception($err);
        }
        return $text;
    }

    /**
     * @throws Exception
     */
    private function http_json(string $url, array $body, array $headers): array
    {
        $args = [
            'timeout' => 90,
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ], $headers),
            'body'    => wp_json_encode($body, JSON_UNESCAPED_UNICODE),
        ];

        $max_retries = 2;
        $attempt     = 0;
        $last_error  = null;

        while ($attempt < $max_retries) {
            $attempt++;
            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $last_error = $response->get_error_message();
                if ($attempt < $max_retries) {
                    usleep(600000 * $attempt);
                    continue;
                }
                throw new Exception('خطا در ارتباط با سرویس هوش مصنوعی: ' . $last_error);
            }

            $code = (int) wp_remote_retrieve_response_code($response);
            $raw  = wp_remote_retrieve_body($response);
            $data = json_decode($raw, true);

            if (in_array($code, [429, 500, 502, 503, 504], true) && $attempt < $max_retries) {
                usleep(700000 * $attempt);
                continue;
            }

            if (!is_array($data)) {
                $snippet = mb_substr(trim(wp_strip_all_tags((string) $raw)), 0, 180);
                throw new Exception('پاسخ نامعتبر از سرویس هوش مصنوعی (کد ' . $code . ')' . ($snippet ? ': ' . $snippet : ''));
            }

            if ($code >= 400) {
                $msg = $data['error']['message']
                    ?? ($data['error']['code'] ?? null)
                    ?? ($data['message'] ?? null)
                    ?? ($data['errors'][0]['message'] ?? null)
                    ?? ('خطای ' . $code);
                if (is_array($msg)) {
                    $msg = wp_json_encode($msg, JSON_UNESCAPED_UNICODE);
                }
                throw new Exception(is_string($msg) ? $msg : 'خطای سرویس هوش مصنوعی');
            }

            return $data;
        }

        throw new Exception('پاسخی از سرویس هوش مصنوعی دریافت نشد.');
    }

    /* ------------------------------------------------------------------
     * Other AJAX
     * ----------------------------------------------------------------*/

    public function ajax_test_provider(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? $this->get_default_provider()));
        try {
            $text = $this->chat($provider, 'Reply with exactly: OK', 'ping', ['max_tokens' => 10, 'temperature' => 0]);
            wp_send_json_success(['message' => 'OK', 'preview' => mb_substr($text, 0, 80)]);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_save_keys(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $keys = [];
        foreach (array_keys(self::providers_catalog()) as $pid) {
            if (isset($_POST['keys'][$pid])) {
                $keys[$pid] = sanitize_text_field(wp_unslash($_POST['keys'][$pid]));
            } elseif (isset($_POST[$pid . '_key'])) {
                $keys[$pid] = sanitize_text_field(wp_unslash($_POST[$pid . '_key']));
            }
        }
        $existing = $this->get_keys();
        foreach ($keys as $pid => $val) {
            // Keep existing if masked placeholder sent
            if ($val !== '' && str_contains($val, '****')) {
                $keys[$pid] = $existing[$pid] ?? '';
            }
        }
        $merged = array_merge($existing, $keys);
        $to_store = [];
        foreach ($merged as $pid => $val) {
            $val = is_string($val) ? $val : '';
            if ($val !== '' && !str_starts_with($val, 'bkenc:v1:')) {
                $enc = self::encrypt_secret($val);
                $to_store[$pid] = ($enc !== '' && self::decrypt_secret($enc) === $val) ? $enc : $val;
            } else {
                $to_store[$pid] = $val;
            }
        }
        update_option(self::OPT_KEYS, $to_store, false);
        $merged = $this->get_keys(); // plain for mirror

        // Mirror into core settings
        if (function_exists('bankai_update_option')) {
            bankai_update_option('openai_api_key', $merged['openai'] ?? '');
            bankai_update_option('anthropic_api_key', $merged['anthropic'] ?? '');
            bankai_update_option('gemini_api_key', $merged['gemini'] ?? '');
            bankai_update_option('deepseek_api_key', $merged['deepseek'] ?? '');
            bankai_update_option('openrouter_api_key', $merged['openrouter'] ?? '');
            bankai_update_option('ai_keys', $merged);
        }

        if (isset($_POST['default_provider'])) {
            $dp = sanitize_key(wp_unslash($_POST['default_provider']));
            if (isset(self::providers_catalog()[$dp])) {
                update_option(self::OPT_DEFAULT, $dp, false);
                if (function_exists('bankai_update_option')) {
                    bankai_update_option('active_ai_provider', $dp);
                }
            }
        }

        wp_send_json_success(['message' => 'Saved']);
    }

    public function ajax_save_modules(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $mods = isset($_POST['modules']) && is_array($_POST['modules'])
            ? array_map('rest_sanitize_boolean', wp_unslash($_POST['modules']))
            : [];
        update_option(self::OPT_MODULES, $mods, false);
        wp_send_json_success(['message' => 'Saved']);
    }

    /* ------------------------------------------------------------------
     * UI-facing AJAX (AI Studio tab)
     * ----------------------------------------------------------------*/

    public function ajax_save_ai_keys_ui(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }

        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $catalog  = self::providers_catalog();
        if (!isset($catalog[$provider])) {
            wp_send_json_error(['message' => __('ارائه‌دهنده نامعتبر است.', 'bankai-core')], 400);
        }

        $keys = $this->get_keys();
        $raw  = isset($_POST['key']) ? wp_unslash((string) $_POST['key']) : '';

        if ($raw !== '' && $raw !== '__unchanged__' && !str_contains($raw, '•') && !str_contains($raw, '*')) {
            $plain = sanitize_text_field($raw);
            $enc = self::encrypt_secret($plain);
            $keys[$provider] = ($enc !== '' && self::decrypt_secret($enc) === $plain) ? $enc : $plain;
            update_option(self::OPT_KEYS, $keys, false);
            $this->mirror_keys_to_core($this->get_keys());
        }

        $model = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));
        if ($model !== '') {
            $models = $this->get_selected_models();
            $models[$provider] = $model;
            update_option(self::OPT_MODELS, $models, false);
        }

        // Optional: set as default when requested
        if (!empty($_POST['set_default'])) {
            update_option(self::OPT_DEFAULT, $provider, false);
            if (function_exists('bankai_update_option')) {
                bankai_update_option('active_ai_provider', $provider);
            }
        }

        $has = !empty($keys[$provider]);
        wp_send_json_success([
            'status'     => $has ? 'ready' : 'missing_key',
            'label'      => $has ? __('کلید تنظیم شده', 'bankai-core') : __('بدون کلید API', 'bankai-core'),
            'masked_key' => $has ? self::mask_key($keys[$provider]) : '',
            'message'    => __('ذخیره شد', 'bankai-core'),
        ]);
    }

    public function ajax_clear_ai_key(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $catalog  = self::providers_catalog();
        if (!isset($catalog[$provider])) {
            wp_send_json_error(['message' => 'Invalid provider'], 400);
        }
        $keys = $this->get_keys();
        $keys[$provider] = '';
        update_option(self::OPT_KEYS, $keys, false);
        $this->mirror_keys_to_core($keys);

        wp_send_json_success([
            'status' => 'missing_key',
            'label'  => __('بدون کلید API', 'bankai-core'),
            'message'=> __('کلید حذف شد', 'bankai-core'),
        ]);
    }

    public function ajax_test_connections(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $only = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $catalog = self::providers_catalog();
        $targets = $only !== '' && isset($catalog[$only])
            ? [$only => $catalog[$only]]
            : $catalog;

        $results = [];
        foreach ($targets as $pid => $info) {
            $keys = $this->get_keys();
            if (empty($keys[$pid])) {
                $results[] = [
                    'id'     => $pid,
                    'name'   => $info['name'] ?? $pid,
                    'status' => 'missing_key',
                    'label'  => __('بدون کلید API', 'bankai-core'),
                    'ok'     => false,
                    'message'=> __('کلید تنظیم نشده', 'bankai-core'),
                ];
                continue;
            }
            try {
                $text = $this->chat($pid, 'Reply with exactly the word OK and nothing else.', 'ping', [
                    'max_tokens'  => 16,
                    'temperature' => 0,
                ]);
                $ok = (stripos($text, 'OK') !== false) || (trim($text) !== '');
                $results[] = [
                    'id'     => $pid,
                    'name'   => $info['name'] ?? $pid,
                    'status' => $ok ? 'ready' : 'error',
                    'label'  => $ok ? __('متصل ✓', 'bankai-core') : __('پاسخ نامعتبر', 'bankai-core'),
                    'ok'     => $ok,
                    'message'=> $ok ? __('اتصال موفق', 'bankai-core') : mb_substr($text, 0, 80),
                    'preview'=> mb_substr(trim($text), 0, 60),
                ];
            } catch (Throwable $e) {
                $results[] = [
                    'id'     => $pid,
                    'name'   => $info['name'] ?? $pid,
                    'status' => 'error',
                    'label'  => __('خطای اتصال', 'bankai-core'),
                    'ok'     => false,
                    'message'=> $e->getMessage(),
                ];
            }
        }

        // When single provider test, put that first for UI
        wp_send_json_success([
            'providers' => $results,
            'message'   => __('تست انجام شد', 'bankai-core'),
        ]);
    }

    public function ajax_generate_prompt(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $prompt   = sanitize_textarea_field(wp_unslash($_POST['prompt_input'] ?? $_POST['prompt'] ?? ''));
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? $this->get_default_provider()));

        if (trim($prompt) === '') {
            wp_send_json_error(['message' => __('پرامپت خالی است.', 'bankai-core')], 400);
        }

        try {
            $system = 'You are a helpful SEO and content assistant for a WordPress site. Respond in the same language as the user. Be concise and practical.';
            $text = $this->chat($provider, $system, $prompt, [
                'temperature' => 0.6,
                'max_tokens'  => 1200,
            ]);
            wp_send_json_success([
                'result'   => $text,
                'provider' => $provider,
            ]);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }

    public function ajax_toggle_ai_module(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $mod_id = sanitize_key(wp_unslash($_POST['module_id'] ?? ''));
        $active = !empty($_POST['active']) && $_POST['active'] !== '0';
        if ($mod_id === '') {
            wp_send_json_error(['message' => 'Invalid module'], 400);
        }
        $mods = get_option(self::OPT_MODULES, []);
        if (!is_array($mods)) {
            $mods = [];
        }
        $mods[$mod_id] = $active;
        update_option(self::OPT_MODULES, $mods, false);
        wp_send_json_success(['message' => __('ذخیره شد', 'bankai-core'), 'active' => $active]);
    }

    public function ajax_set_default_provider(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        if (!isset(self::providers_catalog()[$provider])) {
            wp_send_json_error(['message' => 'Invalid provider'], 400);
        }
        update_option(self::OPT_DEFAULT, $provider, false);
        if (function_exists('bankai_update_option')) {
            bankai_update_option('active_ai_provider', $provider);
        }
        wp_send_json_success(['message' => __('پیش‌فرض ذخیره شد', 'bankai-core'), 'provider' => $provider]);
    }

    private function mirror_keys_to_core(array $keys): void
    {
        if (!function_exists('bankai_update_option')) {
            return;
        }
        bankai_update_option('openai_api_key', $keys['openai'] ?? '');
        bankai_update_option('anthropic_api_key', $keys['anthropic'] ?? '');
        bankai_update_option('gemini_api_key', $keys['gemini'] ?? '');
        bankai_update_option('deepseek_api_key', $keys['deepseek'] ?? '');
        bankai_update_option('openrouter_api_key', $keys['openrouter'] ?? '');
        bankai_update_option('ai_keys', $keys);
    }
    public function ajax_save_custom_provider(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        $id = sanitize_key(wp_unslash($_POST['id'] ?? ''));
        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $type = sanitize_key(wp_unslash($_POST['type'] ?? 'openai_compat'));
        if (!in_array($type, ['openai_compat', 'gemini', 'anthropic', 'cloudflare'], true)) {
            $type = 'openai_compat';
        }
        $endpoint = esc_url_raw(wp_unslash($_POST['endpoint'] ?? ''));
        $models_raw = wp_unslash($_POST['models'] ?? '');
        $models = [];
        if (is_array($models_raw)) {
            foreach ($models_raw as $m) {
                $m = trim(sanitize_text_field((string) $m));
                if ($m !== '') {
                    $models[] = $m;
                }
            }
        } else {
            foreach (preg_split('/[\n,]+/', (string) $models_raw) as $m) {
                $m = trim(sanitize_text_field($m));
                if ($m !== '') {
                    $models[] = $m;
                }
            }
        }
        if ($name === '') {
            wp_send_json_error(['message' => 'نام ایجنت الزامی است.']);
        }
        if ($id === '') {
            $id = 'custom_' . substr(md5($name . microtime()), 0, 8);
        }
        if (isset(self::providers_catalog()[$id])) {
            wp_send_json_error(['message' => 'این شناسه متعلق به پروایدر داخلی است. شناسه دیگری انتخاب کنید.']);
        }
        if ($type === 'openai_compat' && $endpoint === '') {
            wp_send_json_error(['message' => 'برای نوع OpenAI-Compatible آدرس Endpoint الزامی است.']);
        }

        $default = sanitize_text_field(wp_unslash($_POST['default_model'] ?? ($models[0] ?? '')));
        $auth_header = sanitize_text_field(wp_unslash($_POST['auth_header'] ?? 'Authorization'));
        $auth_prefix = sanitize_text_field(wp_unslash($_POST['auth_prefix'] ?? 'Bearer '));
        $tagline = sanitize_text_field(wp_unslash($_POST['tagline'] ?? 'ایجنت سفارشی'));
        $docs = esc_url_raw(wp_unslash($_POST['docs'] ?? ''));
        $key = sanitize_text_field(wp_unslash($_POST['api_key'] ?? ''));

        $extra_headers = [];
        $eh_raw = wp_unslash($_POST['extra_headers'] ?? '');
        if (is_string($eh_raw) && $eh_raw !== '') {
            $decoded = json_decode($eh_raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $hk => $hv) {
                    $extra_headers[sanitize_text_field((string) $hk)] = sanitize_text_field((string) $hv);
                }
            }
        }

        $custom = self::get_custom_providers_raw();
        $custom[$id] = [
            'id'            => $id,
            'name'          => $name,
            'type'          => $type,
            'endpoint'      => $endpoint,
            'models'        => $models ?: ['default'],
            'default'       => $default ?: ($models[0] ?? 'default'),
            'auth_header'   => $auth_header ?: 'Authorization',
            'auth_prefix'   => $auth_prefix,
            'extra_headers' => $extra_headers,
            'tagline'       => $tagline,
            'docs'          => $docs,
            'free_tier'     => !empty($_POST['free_tier']),
            'accent'        => sanitize_hex_color(wp_unslash($_POST['accent'] ?? '')) ?: '#6b4eff',
        ];
        update_option(self::OPT_CUSTOM, $custom, false);

        if ($key !== '' && !str_contains($key, '•') && !str_contains($key, '****')) {
            $keys = get_option(self::OPT_KEYS, []);
            if (!is_array($keys)) {
                $keys = [];
            }
            $enc = self::encrypt_secret($key);
            $keys[$id] = ($enc !== '' && self::decrypt_secret($enc) === $key) ? $enc : $key;
            update_option(self::OPT_KEYS, $keys, false);
        }

        $models_opt = get_option(self::OPT_MODELS, []);
        if (!is_array($models_opt)) {
            $models_opt = [];
        }
        $models_opt[$id] = $default ?: ($models[0] ?? '');
        update_option(self::OPT_MODELS, $models_opt, false);

        wp_send_json_success([
            'message'  => 'ایجنت سفارشی ذخیره شد.',
            'provider' => self::all_providers_catalog()[$id] ?? $custom[$id],
        ]);
    }

    public function ajax_delete_custom_provider(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $id = sanitize_key(wp_unslash($_POST['id'] ?? ''));
        if ($id === '' || isset(self::providers_catalog()[$id])) {
            wp_send_json_error(['message' => 'شناسه نامعتبر']);
        }
        $custom = self::get_custom_providers_raw();
        unset($custom[$id]);
        update_option(self::OPT_CUSTOM, $custom, false);

        $keys = get_option(self::OPT_KEYS, []);
        if (is_array($keys)) {
            unset($keys[$id]);
            update_option(self::OPT_KEYS, $keys, false);
        }
        $models = get_option(self::OPT_MODELS, []);
        if (is_array($models)) {
            unset($models[$id]);
            update_option(self::OPT_MODELS, $models, false);
        }
        wp_send_json_success(['message' => 'ایجنت حذف شد.']);
    }

    public function ajax_add_provider_model(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $model = trim(sanitize_text_field(wp_unslash($_POST['model'] ?? '')));
        if ($provider === '' || $model === '') {
            wp_send_json_error(['message' => 'پروایدر و مدل الزامی است.']);
        }
        $catalog = self::all_providers_catalog();
        if (!isset($catalog[$provider])) {
            wp_send_json_error(['message' => 'پروایدر یافت نشد.']);
        }

        if (!empty($catalog[$provider]['custom'])) {
            $custom = self::get_custom_providers_raw();
            if (!isset($custom[$provider])) {
                wp_send_json_error(['message' => 'ایجنت سفارشی یافت نشد.']);
            }
            $list = $custom[$provider]['models'] ?? [];
            if (!in_array($model, $list, true)) {
                $list[] = $model;
            }
            $custom[$provider]['models'] = $list;
            update_option(self::OPT_CUSTOM, $custom, false);
        } else {
            $extra = get_option(self::OPT_EXTRA_MODELS, []);
            if (!is_array($extra)) {
                $extra = [];
            }
            if (!isset($extra[$provider]) || !is_array($extra[$provider])) {
                $extra[$provider] = [];
            }
            if (!in_array($model, $extra[$provider], true)) {
                $extra[$provider][] = $model;
            }
            update_option(self::OPT_EXTRA_MODELS, $extra, false);
        }

        if (!empty($_POST['set_active'])) {
            $models_opt = get_option(self::OPT_MODELS, []);
            if (!is_array($models_opt)) {
                $models_opt = [];
            }
            $models_opt[$provider] = $model;
            update_option(self::OPT_MODELS, $models_opt, false);
        }

        wp_send_json_success([
            'message' => 'مدل اضافه شد.',
            'models'  => self::all_providers_catalog()[$provider]['models'] ?? [],
        ]);
    }

    public function ajax_remove_provider_model(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['id'] ?? $_POST['provider'] ?? ''));
        $model = trim(sanitize_text_field(wp_unslash($_POST['model'] ?? '')));
        $custom = self::get_custom_providers_raw();
        if (isset($custom[$provider])) {
            $custom[$provider]['models'] = array_values(array_filter(
                $custom[$provider]['models'] ?? [],
                static fn($m) => $m !== $model
            ));
            update_option(self::OPT_CUSTOM, $custom, false);
        } else {
            $extra = get_option(self::OPT_EXTRA_MODELS, []);
            if (is_array($extra) && isset($extra[$provider])) {
                $extra[$provider] = array_values(array_filter(
                    $extra[$provider],
                    static fn($m) => $m !== $model
                ));
                update_option(self::OPT_EXTRA_MODELS, $extra, false);
            }
        }
        wp_send_json_success(['message' => 'مدل حذف شد.']);
    }
}
