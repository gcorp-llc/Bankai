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
        // Updated Sep 2026 from official/docs + model directories
        return [
            'openrouter' => [
                'id'        => 'openrouter',
                'name'      => 'OpenRouter',
                'label'     => 'OpenRouter',
                'tagline'   => 'گیت‌وی چندمدلی · :free + Auto',
                'docs'      => 'https://openrouter.ai/keys',
                'models'    => [
                    'openrouter/auto',
                    'openrouter/free',
                    'nvidia/nemotron-3-ultra:free',
                    'qwen/qwen3.8-27b:free',
                    'deepseek/deepseek-v4-flash:free',
                    'meta-llama/llama-4-scout:free',
                    'google/gemma-4-31b:free',
                    'openai/gpt-4o-mini',
                    'anthropic/claude-sonnet-5',
                    'google/gemini-3.8-flash',
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
                'tagline'   => '3.8 Flash · 3.5 Flash-Lite · رایگان',
                'docs'      => 'https://aistudio.google.com/apikey',
                'models'    => [
                    'gemini-3.8-flash',
                    'gemini-3.7-flash',
                    'gemini-3.6-flash',
                    'gemini-3.5-flash',
                    'gemini-3.5-flash-lite',
                    'gemini-3.1-pro-preview',
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
                'tagline'   => 'GPT-6 Astra · Sol · Luna',
                'docs'      => 'https://platform.openai.com/api-keys',
                'models'    => [
                    'gpt-6-astra',
                    'gpt-6-sol',
                    'gpt-6-luna',
                    'gpt-5.6',
                    'gpt-5.4',
                    'gpt-5.4-mini',
                    'gpt-5.4-nano',
                    'gpt-4.1',
                    'gpt-4o-mini',
                    'o4-mini',
                ],
                'default'   => 'gpt-6-luna',
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
                'tagline'   => 'Opus 5.5 · Sonnet 5 · Haiku 4.5',
                'docs'      => 'https://console.anthropic.com/settings/keys',
                'models'    => [
                    'claude-opus-5-5',
                    'claude-sonnet-5',
                    'claude-haiku-4-5',
                    'claude-opus-5',
                    'claude-fable-5-1',
                ],
                'default'   => 'claude-sonnet-5',
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
                'tagline'   => 'V4 Flash · V4 Pro · R1',
                'docs'      => 'https://platform.deepseek.com/api_keys',
                'models'    => [
                    'deepseek-flash',
                    'deepseek-v4-pro',
                    'deepseek-chat',
                    'deepseek-reasoner',
                ],
                'default'   => 'deepseek-flash',
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
                'tagline'   => 'Inference · اعتبار رایگان ماهانه',
                'docs'      => 'https://huggingface.co/settings/tokens',
                'models'    => [
                    'meta-llama/Llama-4-Scout-17B-16E-Instruct',
                    'meta-llama/Llama-3.3-70B-Instruct',
                    'Qwen/Qwen3-32B',
                    'Qwen/Qwen2.5-72B-Instruct',
                    'google/gemma-2-27b-it',
                    'deepseek-ai/DeepSeek-V3',
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
                'tagline'   => 'LPU · gpt-oss · Llama 3.3',
                'docs'      => 'https://console.groq.com/keys',
                'models'    => [
                    'llama-3.3-70b-versatile',
                    'openai/gpt-oss-120b',
                    'openai/gpt-oss-20b',
                    'qwen/qwen3.6-27b',
                    'gemma2-9b-it',
                    'whisper-large-v3',
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
                    '@cf/meta/llama-3.3-70b-instruct-fp8-fast',
                    '@cf/mistral/mistral-7b-instruct-v0.2',
                    '@cf/google/gemma-7b-it',
                    '@cf/qwen/qwen1.5-14b-chat-awq',
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

    public function get_keys(): array
    {
        $from_opt = get_option(self::OPT_KEYS, []);
        if (!is_array($from_opt)) {
            $from_opt = [];
        }

        // Also read from bankai_core_settings for compatibility.
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
        foreach ($map as $pid => $core_key) {
            $val = $from_opt[$pid] ?? ($core[$core_key] ?? '');
            if ($val === '' && !empty($core['ai_keys'][$pid])) {
                $val = $core['ai_keys'][$pid];
            }
            $out[$pid] = is_string($val) ? $val : '';
        }
        return $out;
    }

    public function get_selected_models(): array
    {
        $models = get_option(self::OPT_MODELS, []);
        if (!is_array($models)) {
            $models = [];
        }
        $catalog = self::providers_catalog();
        foreach ($catalog as $pid => $info) {
            if (empty($models[$pid])) {
                $models[$pid] = $info['default'];
            }
        }
        return $models;
    }

    public function get_default_provider(): string
    {
        $p = get_option(self::OPT_DEFAULT, '');
        if (!is_string($p) || $p === '') {
            $core = function_exists('bankai_get_option') ? bankai_get_option('active_ai_provider', 'gemini') : 'gemini';
            $p = is_string($core) && $core !== '' ? $core : 'gemini';
        }
        $catalog = self::providers_catalog();
        return isset($catalog[$p]) ? $p : 'gemini';
    }

    public function provider_status_list(): array
    {
        $keys    = $this->get_keys();
        $models  = $this->get_selected_models();
        $default = $this->get_default_provider();
        $list    = [];
        foreach (self::providers_catalog() as $pid => $info) {
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

        $task     = sanitize_key(wp_unslash($_POST['task'] ?? ''));
        $title    = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $content  = wp_strip_all_tags(wp_unslash($_POST['content'] ?? ''));
        $focus    = sanitize_text_field(wp_unslash($_POST['focus_keyword'] ?? ''));
        $locale   = sanitize_text_field(wp_unslash($_POST['locale'] ?? 'fa_IR'));
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));

        if ($provider === '') {
            $provider = $this->get_default_provider();
        }

        $content = mb_substr(preg_replace('/\s+/u', ' ', $content), 0, 8000);
        $is_fa   = (stripos($locale, 'fa') !== false || stripos($locale, 'persian') !== false);

        $prompts = $this->build_seo_prompts($task, $title, $content, $focus, $is_fa);
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

    private function build_seo_prompts(string $task, string $title, string $content, string $focus, bool $is_fa): ?array
    {
        $lang = $is_fa ? 'Persian (Farsi)' : 'English';
        $snippet = $content !== '' ? mb_substr($content, 0, 3500) : '(empty)';

        $base_sys = "You are an expert SEO copywriter. Always respond in {$lang}. Be concise and practical. Do not use markdown unless asked.";

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
                return [
                    'system' => $base_sys . ' Output ONLY a short image alt text (under 125 characters).',
                    'user'   => "Suggest an alt text for the featured image of this article.\nTitle: {$title}\nFocus: {$focus}",
                    'temperature' => 0.4,
                    'max_tokens'  => 60,
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
                $keywords = $this->extract_keyword_list($text, $focus);
                $out['keywords'] = $keywords;
                $out['text'] = implode($is_fa ? '، ' : ', ', $keywords);
                break;

            case 'rewrite':
                $out['rewrite'] = $text;
                break;

            case 'outline':
                $out['outline'] = $text;
                break;

            case 'alt_text':
                $alt = $this->clean_one_line($text, 130);
                $out['alt_text'] = $alt;
                $out['text'] = $alt;
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
        $key    = $keys[$provider] ?? '';
        $model  = $models[$provider] ?? '';

        if ($key === '') {
            // Fallback: try any provider that has a key
            foreach ($keys as $pid => $k) {
                if ($k !== '') {
                    $provider = $pid;
                    $key = $k;
                    $model = $models[$pid] ?? '';
                    break;
                }
            }
        }

        if ($key === '') {
            throw new Exception('هیچ کلید API برای هوش مصنوعی تنظیم نشده است. از بخش AI Studio یک کلید وارد کنید.');
        }

        $temperature = isset($opts['temperature']) ? (float) $opts['temperature'] : 0.5;
        $max_tokens  = isset($opts['max_tokens']) ? (int) $opts['max_tokens'] : 800;

        switch ($provider) {
            case 'gemini':
                return $this->call_gemini($key, $model ?: 'gemini-3.8-flash', $system, $user, $temperature, $max_tokens);
            case 'openai':
                return $this->call_openai_compat('https://api.openai.com/v1/chat/completions', $key, $model ?: 'gpt-6-luna', $system, $user, $temperature, $max_tokens);
            case 'deepseek':
                return $this->call_openai_compat('https://api.deepseek.com/chat/completions', $key, $model ?: 'deepseek-flash', $system, $user, $temperature, $max_tokens);
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
                return $this->call_anthropic($key, $model ?: 'claude-sonnet-5', $system, $user, $temperature, $max_tokens);
            default:
                throw new Exception('Provider not supported: ' . $provider);
        }
    }

    private function call_gemini(string $key, string $model, string $system, string $user, float $temp, int $max): string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($key);
        $body = [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $user]]],
            ],
            'generationConfig' => [
                'temperature'     => $temp,
                'maxOutputTokens' => $max,
            ],
        ];
        $res = $this->http_json($url, $body, []);
        $text = $res['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '') {
            $err = $res['error']['message'] ?? 'Empty Gemini response';
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
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => $temp,
            'max_tokens'  => $max,
        ];
        $res = $this->http_json($url, $body, $headers);
        $text = $res['choices'][0]['message']['content'] ?? '';
        if ($text === '') {
            $err = $res['error']['message'] ?? 'Empty OpenAI-compatible response';
            throw new Exception($err);
        }
        return $text;
    }

    private function call_cloudflare(string $key, string $model, string $system, string $user, float $temp, int $max): string
    {
        // Key format: AccountID|APIToken  OR just API token with account in option
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
            'max_tokens'  => $max,
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
            'timeout' => 55,
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ], $headers),
            'body'    => wp_json_encode($body, JSON_UNESCAPED_UNICODE),
        ];
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        $code = wp_remote_retrieve_response_code($response);
        $raw  = wp_remote_retrieve_body($response);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new Exception('Invalid JSON from AI provider (HTTP ' . $code . ')');
        }
        if ($code >= 400) {
            $msg = $data['error']['message'] ?? ($data['message'] ?? ('HTTP ' . $code));
            throw new Exception(is_string($msg) ? $msg : 'AI provider error');
        }
        return $data;
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
        update_option(self::OPT_KEYS, $merged, false);

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
            $keys[$provider] = sanitize_text_field($raw);
            update_option(self::OPT_KEYS, $keys, false);
            $this->mirror_keys_to_core($keys);
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
}
