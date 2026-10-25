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
    public const OPT_IMAGE_KEYS    = 'bankai_ai_image_keys';
    public const OPT_IMAGE_MODELS  = 'bankai_ai_image_models';
    public const OPT_IMAGE_DEFAULT = 'bankai_ai_image_default';

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
        add_action('wp_ajax_bankai_ai_generate_article', [$this, 'ajax_generate_article']);
        add_action('wp_ajax_bankai_ai_suggest_titles', [$this, 'ajax_suggest_titles']);
        add_action('wp_ajax_bankai_ai_assign_terms', [$this, 'ajax_assign_terms']);
        add_action('wp_ajax_bankai_ai_test_connection', [$this, 'ajax_test_connection']);
        add_action('wp_ajax_bankai_ai_remove_queue_item', [$this, 'ajax_remove_queue_item']);
        add_action('bankai_publish_scheduled_ai_post', [$this, 'cron_publish_scheduled'], 10, 1);
        add_action('save_post', [$this, 'on_save_post_modules'], 40, 3);
        add_action('add_attachment', [$this, 'on_add_attachment_alt'], 20);
        add_action('wp_ajax_bankai_get_site_guidelines', [$this, 'ajax_get_site_guidelines']);
        add_action('wp_ajax_bankai_save_site_guidelines', [$this, 'ajax_save_site_guidelines']);
        add_action('wp_ajax_bankai_preview_ai_prompt', [$this, 'ajax_preview_ai_prompt']);
        add_action('wp_ajax_bankai_set_default_provider', [$this, 'ajax_set_default_provider']);
        add_action('wp_ajax_bankai_save_custom_provider', [$this, 'ajax_save_custom_provider']);
        add_action('wp_ajax_bankai_delete_custom_provider', [$this, 'ajax_delete_custom_provider']);
        add_action('wp_ajax_bankai_add_provider_model', [$this, 'ajax_add_provider_model']);
        add_action('wp_ajax_bankai_remove_provider_model', [$this, 'ajax_remove_provider_model']);
        add_action('wp_ajax_bankai_save_image_ai_key', [$this, 'ajax_save_image_ai_key']);
        add_action('wp_ajax_bankai_clear_image_ai_key', [$this, 'ajax_clear_image_ai_key']);
        add_action('wp_ajax_bankai_test_image_ai', [$this, 'ajax_test_image_ai']);
        add_action('wp_ajax_bankai_ai_image_generate', [$this, 'ajax_image_generate']);
        add_action('wp_ajax_bankai_set_default_image_provider', [$this, 'ajax_set_default_image_provider']);
        add_action('wp_ajax_bankai_add_image_model', [$this, 'ajax_add_image_model']);
        add_action('wp_ajax_bankai_remove_image_model', [$this, 'ajax_remove_image_model']);
    }

    public static function default_site_guidelines(): array
    {
        return [
            'language'             => 'fa_IR',
            'site_domain'          => 'طراحی سایت، سئو، فناوری و کسب‌وکار آنلاین',
            'target_audience'      => 'مدیران کسب‌وکار، وبمستران و علاقه‌مندان به بازاریابی دیجیتال',
            'brand_tone'           => 'seo',
            'formality'            => 'میانه (حرفه‌ای و صمیمی)',
            'heading_rules'        => 'یک H1 یکتا، استفاده منظم از H2 برای بخش‌های اصلی و H3 برای زیربخش‌ها. پرهیز از پرش سطح.',
            'intro_outro_rules'    => 'مقدمه جذاب با پاسخ صریح در ۲–۳ پاراگراف؛ جمع‌بندی کاربردی با دعوت به اقدام.',
            'keyword_policy'       => 'استفاده طبیعی از کلمه کلیدی اصلی در مقدمه، یک H2 و نتیجه‌گیری (چگالی ~۱٪). پرهیز از تکرار مصنوعی.',
            'internal_link_policy' => 'پیشنهاد ۲ تا ۵ لینک داخلی با انکر تکست توصیفی و مرتبط.',
            'external_link_policy' => 'ارجاع به منابع معتبر فقط در صورت لزوم.',
            'factual_policy'       => 'عدم ادعای بدون مدرک؛ حفظ صحت فنی.',
            'faq_aeo_geo_rules'    => 'ارائه پاسخ‌های کوتاه، مستقیم و ساختاریافته برای AEO و GEO همراه با اسکیما.',
            'forbidden_phrases'    => 'عبارات کلیشه‌ای مانند: در دنیای امروز، بدون شک، حتما می‌دانید، امروزه با پیشرفت علم.',
            'custom_notes'         => 'محتوا باید جامع، کاملاً کاربردی و قابل اعتماد باشد.',
        ];
    }

    public static function get_site_guidelines(): array
    {
        $saved = get_option('bankai_ai_site_guidelines', []);
        if (!is_array($saved)) {
            $saved = [];
        }
        return array_merge(self::default_site_guidelines(), $saved);
    }

    public static function format_guidelines_prompt_context(): string
    {
        $g = self::get_site_guidelines();
        $lines = [
            "=== دستورالعمل عمومی تولید محتوای سایت ===",
            "- حوزه و موضوع سایت: " . $g['site_domain'],
            "- مخاطب هدف: " . $g['target_audience'],
            "- لحن و شخصیت برند: " . $g['brand_tone'] . " (" . $g['formality'] . ")",
            "- قواعد هدینگ‌ها: " . $g['heading_rules'],
            "- قواعد مقدمه و نتیجه‌گیری: " . $g['intro_outro_rules'],
            "- سیاست کلیدواژه: " . $g['keyword_policy'],
            "- لینک‌سازی داخلی/خارجی: " . $g['internal_link_policy'],
            "- قواعد FAQ، AEO و GEO: " . $g['faq_aeo_geo_rules'],
            "- عبارات و سبک‌های ممنوع: " . $g['forbidden_phrases'],
        ];
        if (!empty($g['custom_notes'])) {
            $lines[] = "- نکات اختصاصی مدیر: " . $g['custom_notes'];
        }
        return implode("\n", $lines);
    }

    public function ajax_get_site_guidelines(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        wp_send_json_success(['guidelines' => self::get_site_guidelines()]);
    }

    public function ajax_save_site_guidelines(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $raw = wp_unslash($_POST['guidelines'] ?? []);
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        if (!is_array($raw)) {
            $raw = [];
        }
        $defaults = self::default_site_guidelines();
        $clean = [];
        foreach ($defaults as $k => $v) {
            $clean[$k] = sanitize_textarea_field((string) ($raw[$k] ?? $v));
        }
        update_option('bankai_ai_site_guidelines', $clean, false);
        wp_send_json_success([
            'message'    => __('دستورالعمل عمومی با موفقیت ذخیره شد.', 'bankai-core'),
            'guidelines' => $clean,
        ]);
    }

    public function ajax_preview_ai_prompt(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $topic = sanitize_text_field(wp_unslash($_POST['topic'] ?? 'موضوع نمونه'));
        $focus = sanitize_text_field(wp_unslash($_POST['focus'] ?? 'کلمه کلیدی نمونه'));
        $task  = sanitize_key(wp_unslash($_POST['task'] ?? 'article'));

        $layer1 = "Layer 1 (System Safety & Core Rules): OpenAI / Anthropic / Gemini safety policies and language constraints.";
        $layer2 = self::format_guidelines_prompt_context();
        $layer3 = "Layer 3 (Topic Cluster / Strategy Context): Pillar content relationships, taxonomy alignment, and site post deduplication.";
        $layer4 = "Layer 4 (Article Brief & Target Outline):\n- Topic: {$topic}\n- Focus Keyword: {$focus}\n- Task: {$task}";
        $layer5 = "Layer 5 (User Command / Custom Operation): Specific user request for this generation pass.";

        $full_prompt = implode("\n\n", [$layer1, $layer2, $layer3, $layer4, $layer5]);

        wp_send_json_success([
            'topic'       => $topic,
            'task'        => $task,
            'full_prompt' => $full_prompt,
            'layers'      => [
                'layer1' => $layer1,
                'layer2' => $layer2,
                'layer3' => $layer3,
                'layer4' => $layer4,
                'layer5' => $layer5,
            ]
        ]);
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
                    'gpt-5',
                    'gpt-5-mini',
                    'gpt-5-nano',
                    'gpt-5.4-mini',
                    'gpt-4.1',
                    'gpt-4.1-mini',
                    'gpt-4o',
                    'gpt-4o-mini',
                    'o4-mini',
                    'o3-mini',
                ],
                'default'   => 'gpt-5-mini',
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
                    'claude-sonnet-5-5',
                    'claude-haiku-5-5',
                    'claude-opus-5-5',
                    'claude-fable-5-1',
                    'claude-haiku-4-5',
                    'claude-sonnet-4-5',
                    'claude-opus-4-6',
                ],
                'default'   => 'claude-sonnet-5-5',
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
                'name'      => 'Cloudflare AI',
                'label'     => 'Cloudflare AI',
                'tagline'   => 'Workers AI + Unified REST · Responses / Chat',
                'docs'      => 'https://developers.cloudflare.com/workers-ai/configuration/open-ai-compatibility/',
                'models'    => [
                    // Workers AI (hosted on CF) — free-plan friendly first
                    '@cf/meta/llama-3.1-8b-instruct',
                    '@cf/meta/llama-3.2-3b-instruct',
                    '@cf/meta/llama-3.3-70b-instruct-fp8-fast',
                    '@cf/google/gemma-2-9b-it',
                    '@cf/qwen/qwen1.5-7b-chat-awq',
                    '@cf/mistral/mistral-7b-instruct-v0.2',
                    '@cf/openai/gpt-oss-20b',
                    '@cf/openai/gpt-oss-120b',
                    '@cf/moonshotai/kimi-k2.6',
                    '@cf/deepseek-ai/deepseek-r1-distill-qwen-32b',
                    // Unified API (third-party via CF account token)
                    'openai/gpt-4.1',
                    'openai/gpt-4.1-mini',
                    'openai/gpt-5-mini',
                    'openai/gpt-5',
                    'google/gemini-2.5-flash',
                    'anthropic/claude-sonnet-4-5',
                ],
                'default'   => '@cf/meta/llama-3.1-8b-instruct',
                'key_hint'  => 'AccountID|API_Token',
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


    /**
     * Image-generation providers (cover + in-content visuals).
     */
    public static function image_providers_catalog(): array
    {
        return [
            'cloudflare_image' => [
                'id'        => 'cloudflare_image',
                'name'      => 'Cloudflare Workers AI',
                'tagline'   => 'Flux 2 · Leonardo · SDXL',
                'docs'      => 'https://developers.cloudflare.com/workers-ai/models/',
                'models'    => [
                    '@cf/black-forest-labs/flux-1-schnell',
                    '@cf/black-forest-labs/flux-2-klein-9b',
                    '@cf/black-forest-labs/flux-2-klein-4b',
                    '@cf/black-forest-labs/flux-2-dev',
                    '@cf/leonardo/lucid-origin',
                    '@cf/leonardo/phoenix-1.0',
                    '@cf/bytedance/stable-diffusion-xl-lightning',
                    '@cf/lykon/dreamshaper-8-lcm',
                    '@cf/stabilityai/stable-diffusion-xl-base-1.0',
                    '@cf/runwayml/stable-diffusion-v1-5-inpainting',
                ],
                'default'   => '@cf/black-forest-labs/flux-1-schnell',
                'key_hint'  => 'AccountID|APIToken',
                'accent'    => '#F6821F',
                'free_tier' => true,
                'order'     => 1,
                'features'  => ['cover', 'inline'],
            ],
            'openai_image' => [
                'id'        => 'openai_image',
                'name'      => 'OpenAI Images',
                'tagline'   => 'GPT Image · DALL·E 3',
                'docs'      => 'https://platform.openai.com/api-keys',
                'models'    => [
                    'gpt-image-1',
                    'dall-e-3',
                    'dall-e-2',
                ],
                'default'   => 'gpt-image-1',
                'key_hint'  => 'sk-…',
                'accent'    => '#10A37F',
                'free_tier' => false,
                'order'     => 10,
                'features'  => ['cover', 'inline'],
            ],
            'gemini_image' => [
                'id'        => 'gemini_image',
                'name'      => 'Google Gemini / Imagen',
                'tagline'   => 'Imagen 3 · Flash Image',
                'docs'      => 'https://aistudio.google.com/apikey',
                'models'    => [
                    'imagen-3.0-generate-002',
                    'imagen-3.0-fast-generate-001',
                    'gemini-2.0-flash-preview-image-generation',
                    'gemini-2.5-flash-image-preview',
                ],
                'default'   => 'imagen-3.0-generate-002',
                'key_hint'  => 'AIza…',
                'accent'    => '#4285F4',
                'free_tier' => true,
                'order'     => 20,
                'features'  => ['cover', 'inline'],
            ],
            'huggingface_image' => [
                'id'        => 'huggingface_image',
                'name'      => 'Hugging Face',
                'tagline'   => 'FLUX · SD3.5 · رایگان/کردیت',
                'docs'      => 'https://huggingface.co/settings/tokens',
                'models'    => [
                    'black-forest-labs/FLUX.1-schnell',
                    'black-forest-labs/FLUX.1-dev',
                    'stabilityai/stable-diffusion-3.5-large',
                    'stabilityai/stable-diffusion-xl-base-1.0',
                ],
                'default'   => 'black-forest-labs/FLUX.1-schnell',
                'key_hint'  => 'hf_…',
                'accent'    => '#FFD21E',
                'free_tier' => true,
                'order'     => 30,
                'features'  => ['cover', 'inline'],
            ],
            'stability' => [
                'id'        => 'stability',
                'name'      => 'Stability AI',
                'tagline'   => 'Stable Image Core / Ultra',
                'docs'      => 'https://platform.stability.ai/account/keys',
                'models'    => [
                    'stable-image-core',
                    'stable-image-ultra',
                    'sd3.5-large',
                    'sd3.5-medium',
                ],
                'default'   => 'stable-image-core',
                'key_hint'  => 'sk-…',
                'accent'    => '#9B59B6',
                'free_tier' => false,
                'order'     => 40,
                'features'  => ['cover', 'inline'],
            ],
            'replicate' => [
                'id'        => 'replicate',
                'name'      => 'Replicate',
                'tagline'   => 'FLUX · SD3 · متن‌باز',
                'docs'      => 'https://replicate.com/account/api-tokens',
                'models'    => [
                    'black-forest-labs/flux-schnell',
                    'black-forest-labs/flux-dev',
                    'black-forest-labs/flux-1.1-pro',
                    'stability-ai/stable-diffusion-3.5-large',
                ],
                'default'   => 'black-forest-labs/flux-schnell',
                'key_hint'  => 'r8_…',
                'accent'    => '#111827',
                'free_tier' => false,
                'order'     => 50,
                'features'  => ['cover', 'inline'],
            ],
            'together_image' => [
                'id'        => 'together_image',
                'name'      => 'Together AI',
                'tagline'   => 'FLUX · قیمت مناسب',
                'docs'      => 'https://api.together.xyz/settings/api-keys',
                'models'    => [
                    'black-forest-labs/FLUX.1-schnell',
                    'black-forest-labs/FLUX.1-dev',
                    'black-forest-labs/FLUX.1.1-pro',
                    'stabilityai/stable-diffusion-xl-base-1.0',
                ],
                'default'   => 'black-forest-labs/FLUX.1-schnell',
                'key_hint'  => '…',
                'accent'    => '#0F6FFF',
                'free_tier' => true,
                'order'     => 60,
                'features'  => ['cover', 'inline'],
            ],
            'fal_image' => [
                'id'        => 'fal_image',
                'name'      => 'fal.ai',
                'tagline'   => 'FLUX · سرعت بالا',
                'docs'      => 'https://fal.ai/dashboard/keys',
                'models'    => [
                    'fal-ai/flux/schnell',
                    'fal-ai/flux/dev',
                    'fal-ai/flux-pro/v1.1',
                    'fal-ai/stable-diffusion-v35-large',
                ],
                'default'   => 'fal-ai/flux/schnell',
                'key_hint'  => '…',
                'accent'    => '#E11D48',
                'free_tier' => false,
                'order'     => 70,
                'features'  => ['cover', 'inline'],
            ],
        ];
    }


    /** Built-in + user-added image models for a provider. */
    public static function get_image_provider_models(string $provider): array
    {
        $cat = self::image_providers_catalog();
        $base = array_values($cat[$provider]['models'] ?? []);
        $extra = get_option('bankai_ai_image_extra_models', []);
        if (is_array($extra) && !empty($extra[$provider]) && is_array($extra[$provider])) {
            foreach ($extra[$provider] as $m) {
                $m = trim((string) $m);
                if ($m !== '' && !in_array($m, $base, true)) {
                    $base[] = $m;
                }
            }
        }
        return $base;
    }

    public function get_image_keys(): array
    {
        $keys = get_option(self::OPT_IMAGE_KEYS, []);
        return is_array($keys) ? $keys : [];
    }

    public function get_image_models(): array
    {
        $m = get_option(self::OPT_IMAGE_MODELS, []);
        return is_array($m) ? $m : [];
    }

    public function get_default_image_provider(): string
    {
        $d = (string) get_option(self::OPT_IMAGE_DEFAULT, 'gemini_image');
        $cat = self::image_providers_catalog();
        return isset($cat[$d]) ? $d : (array_key_first($cat) ?: 'gemini_image');
    }

    public function image_provider_status_list(): array
    {
        $keys = $this->get_image_keys();
        $models = $this->get_image_models();
        $out = [];
        foreach (self::image_providers_catalog() as $id => $meta) {
            $key = isset($keys[$id]) ? (string) $keys[$id] : '';
            $has = $key !== '';
            $out[] = [
                'id'         => $id,
                'name'       => $meta['name'] ?? $id,
                'has_key'    => $has,
                'status'     => $has ? 'ready' : 'missing_key',
                'label'      => $has ? __('آماده', 'bankai-core') : __('بدون کلید API', 'bankai-core'),
                'masked_key' => $has ? self::mask_key($key) : '',
                'model'      => $models[$id] ?? ($meta['default'] ?? ''),
            ];
        }
        return $out;
    }


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


    /**
     * Phase D — text Provider for SEO assistant: always central default.
     * Client-supplied provider is ignored (cannot override settings).
     */
    public function resolve_central_text_provider(string $client_provider = ''): string
    {
        $keys = $this->get_keys();
        if (!empty($client_provider) && !empty($keys[$client_provider]) && is_string($keys[$client_provider]) && trim($keys[$client_provider]) !== '') {
            return $client_provider;
        }
        $central = $this->get_default_provider();
        if (!empty($central) && !empty($keys[$central]) && is_string($keys[$central]) && trim($keys[$central]) !== '') {
            return $central;
        }
        foreach ($keys as $pid => $k) {
            if (is_string($k) && trim($k) !== '') {
                return (string) $pid;
            }
        }
        return is_string($central) && !empty($central) ? $central : 'gemini';
    }

    /**
     * Phase D — image Provider resolution: check client provider, default image provider, or active keyed fallback.
     */
    public function resolve_central_image_provider(string $client_provider = ''): string
    {
        $keys = $this->get_image_keys();
        if (!empty($client_provider) && !empty($keys[$client_provider]) && is_string($keys[$client_provider]) && trim($keys[$client_provider]) !== '') {
            return $client_provider;
        }
        $central = $this->get_default_image_provider();
        if (!empty($central) && !empty($keys[$central]) && is_string($keys[$central]) && trim($keys[$central]) !== '') {
            return $central;
        }
        foreach ($keys as $pid => $k) {
            if (is_string($k) && trim($k) !== '') {
                return (string) $pid;
            }
        }
        return is_string($central) ? $central : '';
    }

    /**
     * Human-readable readiness error for text provider, or empty if OK.
     */
    public function text_provider_readiness_error(string $provider = ''): string
    {
        if ($provider === '') {
            $provider = $this->resolve_central_text_provider();
        }
        if ($provider === '') {
            return 'هیچ هوش مصنوعی فعالی ندارید لطفا میزان مصرف خود در هوش مصنوعی مورد نظر را برسی کنید';
        }
        $keys = $this->get_keys();
        if (empty($keys[$provider]) || !is_string($keys[$provider]) || trim($keys[$provider]) === '') {
            return 'هیچ هوش مصنوعی فعالی ندارید لطفا میزان مصرف خود در هوش مصنوعی مورد نظر را برسی کنید';
        }
        return '';
    }

    /**
     * Human-readable readiness error for image provider, or empty if OK.
     */
    public function image_provider_readiness_error(string $provider = ''): string
    {
        if ($provider === '') {
            $provider = $this->resolve_central_image_provider();
        }
        if ($provider === '') {
            return 'هیچ هوش مصنوعی فعالی ندارید لطفا میزان مصرف خود در هوش مصنوعی مورد نظر را برسی کنید';
        }
        $keys = $this->get_image_keys();
        if (empty($keys[$provider]) || !is_string($keys[$provider]) || trim($keys[$provider]) === '') {
            return 'هیچ هوش مصنوعی فعالی ندارید لطفا میزان مصرف خود در هوش مصنوعی مورد نظر را برسی کنید';
        }
        return '';
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


    /**
     * Phase C — Generate SEO title/description, validate via Bankai_SEO_Engine::analyze(),
     * repair up to 2 times. Never mutates post content/meta by itself.
     *
     * @param array<string,mixed> $ctx post_id, title, content, focus_keyword, locale, provider, mode(title|description|both)
     * @return array<string,mixed>
     */
    public function run_meta_generation_pipeline(array $ctx): array
    {
        $post_id  = absint($ctx['post_id'] ?? 0);
        $title    = sanitize_text_field((string) ($ctx['title'] ?? ''));
        $content  = (string) ($ctx['content'] ?? '');
        $focus    = sanitize_text_field((string) ($ctx['focus_keyword'] ?? ''));
        $locale   = sanitize_text_field((string) ($ctx['locale'] ?? 'fa_IR'));
        $provider = sanitize_key((string) ($ctx['provider'] ?? ''));
        $mode     = sanitize_key((string) ($ctx['mode'] ?? 'both')); // title|description|both

        // Phase D: always central text provider (ignore client)
        $provider = $this->resolve_central_text_provider((string) $provider);
        $ready_err = $this->text_provider_readiness_error($provider);
        if ($ready_err !== '') {
            return [
                'seo_title' => '',
                'title' => '',
                'description' => '',
                'meta_description' => '',
                'generation_status' => 'error',
                'validation_status' => 'failed',
                'attempts' => 0,
                'failed_checks' => [],
                'analysis' => [],
                'baseline_score' => 0,
                'score' => 0,
                'message' => $ready_err,
                'provider_error' => $ready_err,
                'validated' => false,
                'needs_review' => false,
            ];
        }

        $is_fa = $this->detect_is_fa($title, $content, $locale);

        // Baseline analysis (deterministic)
        $baseline = $this->run_deterministic_analyze($post_id, [
            'seo_title'      => (string) ($ctx['current_seo_title'] ?? ''),
            'description'    => (string) ($ctx['current_description'] ?? ''),
            'focus_keyword'  => $focus,
            'content'        => $content,
        ]);

        $seo_title   = '';
        $description = '';
        $attempts    = 0;
        $max_attempts = 3; // 1 initial + 2 repairs
        $last_failed = [];
        $generation_status = 'error';
        $validation_status = 'failed';
        $provider_error = '';

        $need_title = in_array($mode, ['title', 'both', 'meta_title'], true);
        $need_desc  = in_array($mode, ['description', 'both', 'meta_description', 'meta'], true);

        while ($attempts < $max_attempts) {
            $attempts++;
            try {
                if ($need_title) {
                    $extra = [
                        'failed_checks' => $last_failed,
                        'attempt'       => $attempts,
                        'repair'        => $attempts > 1,
                    ];
                    $prompts = $this->build_seo_prompts('meta_title', $title, $content, $focus, $is_fa, $extra);
                    if (!$prompts) {
                        $provider_error = 'Unknown meta_title task';
                        break;
                    }
                    if ($attempts > 1 && $last_failed) {
                        $prompts['user'] .= "\n\n" . $this->format_repair_instructions($last_failed, 'title', $is_fa);
                        $prompts['temperature'] = 0.25;
                    }
                    $raw = $this->chat($provider, $prompts['system'], $prompts['user'], [
                        'temperature' => $prompts['temperature'] ?? 0.45,
                        'max_tokens'  => $prompts['max_tokens'] ?? 90,
                    ]);
                    $parsed = $this->parse_seo_response('meta_title', (string) $raw, $focus, $is_fa);
                    $cand = trim((string) ($parsed['seo_title'] ?? $parsed['text'] ?? ''));
                    if ($cand === '' || $this->looks_like_instruction_echo($cand)) {
                        if ($attempts >= $max_attempts) {
                            $provider_error = $provider_error ?: 'خروجی عنوان خالی یا نامعتبر بود';
                        }
                        // keep previous good candidate if any
                    } else {
                        $seo_title = $cand;
                        $generation_status = 'success';
                    }
                }

                if ($need_desc) {
                    $extra = [
                        'failed_checks' => $last_failed,
                        'attempt'       => $attempts,
                        'repair'        => $attempts > 1,
                    ];
                    $prompts = $this->build_seo_prompts('meta_description', $title, $content, $focus, $is_fa, $extra);
                    if (!$prompts) {
                        $provider_error = 'Unknown meta_description task';
                        break;
                    }
                    if ($attempts > 1 && $last_failed) {
                        $prompts['user'] .= "\n\n" . $this->format_repair_instructions($last_failed, 'description', $is_fa);
                        $prompts['temperature'] = 0.25;
                    }
                    $raw = $this->chat($provider, $prompts['system'], $prompts['user'], [
                        'temperature' => $prompts['temperature'] ?? 0.45,
                        'max_tokens'  => $prompts['max_tokens'] ?? 180,
                    ]);
                    $parsed = $this->parse_seo_response('meta_description', (string) $raw, $focus, $is_fa);
                    $cand = trim((string) ($parsed['description'] ?? $parsed['text'] ?? ''));
                    if ($cand === '' || $this->looks_like_instruction_echo($cand)) {
                        if ($attempts >= $max_attempts) {
                            $provider_error = $provider_error ?: 'خروجی توضیحات متا خالی یا نامعتبر بود';
                        }
                    } else {
                        $description = $cand;
                        $generation_status = 'success';
                    }
                }
            } catch (\Throwable $e) {
                $provider_error = $e->getMessage();
                $generation_status = 'error';
                // Do not wipe candidates already produced
                break;
            }

            if ($seo_title === '' && $need_title) {
                continue;
            }
            if ($description === '' && $need_desc) {
                continue;
            }

            $analysis = $this->run_deterministic_analyze($post_id, [
                'seo_title'     => $need_title ? $seo_title : (string) ($ctx['current_seo_title'] ?? ''),
                'description'   => $need_desc ? $description : (string) ($ctx['current_description'] ?? ''),
                'focus_keyword' => $focus,
                'content'       => $content,
            ]);

            $last_failed = $this->extract_meta_failures($analysis, $need_title, $need_desc);
            // Soft criteria (title_has_number) never block success
            $blocking = array_values(array_filter($last_failed, static function ($f) {
                return ($f['key'] ?? '') !== 'title_has_number';
            }));

            if (!$blocking) {
                $validation_status = 'passed';
                break;
            }

            if ($attempts >= $max_attempts) {
                $validation_status = 'needs_review';
                break;
            }
            // else repair loop continues with $last_failed
        }

        if ($generation_status === 'error' && $seo_title === '' && $description === '') {
            $validation_status = 'failed';
        } elseif ($validation_status !== 'passed' && ($seo_title !== '' || $description !== '')) {
            if ($validation_status !== 'needs_review') {
                $validation_status = 'needs_review';
            }
        }

        $final_analysis = $this->run_deterministic_analyze($post_id, [
            'seo_title'     => $seo_title !== '' ? $seo_title : (string) ($ctx['current_seo_title'] ?? ''),
            'description'   => $description !== '' ? $description : (string) ($ctx['current_description'] ?? ''),
            'focus_keyword' => $focus,
            'content'       => $content,
        ]);

        if ($validation_status === 'passed') {
            $message = 'تولید و اعتبارسنجی موفق بود.';
        } elseif ($validation_status === 'needs_review') {
            $message = 'خروجی تولید شد اما برخی معیارهای سئو هنوز نیازمند بازبینی هستند.';
        } else {
            $message = $provider_error !== '' ? $provider_error : 'تولید یا اعتبارسنجی ناموفق بود.';
        }

        return [
            'seo_title'          => $seo_title,
            'title'              => $seo_title,
            'description'        => $description,
            'meta_description'   => $description,
            'generation_status'  => $generation_status,
            'validation_status'  => $validation_status,
            'attempts'           => $attempts,
            'failed_checks'      => $last_failed,
            'analysis'           => $final_analysis,
            'baseline_score'     => (int) ($baseline['score'] ?? 0),
            'score'              => (int) ($final_analysis['score'] ?? 0),
            'message'            => $message,
            'provider_error'     => $provider_error,
            // Backward-compatible flags
            'validated'          => $validation_status === 'passed',
            'needs_review'       => $validation_status === 'needs_review',
        ];
    }

    private function detect_is_fa(string $title, string $content, string $locale): bool
    {
        $sample = $title . ' ' . mb_substr(wp_strip_all_tags($content), 0, 800);
        $persian_chars = preg_match_all('/[\x{0600}-\x{06FF}]/u', $sample);
        $latin_chars = preg_match_all('/[A-Za-z]/', $sample);
        if ($persian_chars >= 8 && $persian_chars >= $latin_chars) {
            return true;
        }
        if ($latin_chars >= 20 && $latin_chars > $persian_chars * 2) {
            return false;
        }
        return (stripos($locale, 'fa') !== false || stripos($locale, 'persian') !== false);
    }

    /**
     * @param array<string,mixed> $overrides
     * @return array<string,mixed>
     */
    private function run_deterministic_analyze(int $post_id, array $overrides = []): array
    {
        if ($post_id > 0 && class_exists('Bankai_SEO_Engine')) {
            try {
                return Bankai_SEO_Engine::instance()->analyze($post_id, $overrides);
            } catch (\Throwable $e) {
                // fall through
            }
        }
        return $this->offline_meta_analysis($overrides);
    }

    /**
     * Offline subset for title/description when engine unavailable (tests / edge).
     *
     * @param array<string,mixed> $o
     * @return array<string,mixed>
     */
    private function offline_meta_analysis(array $o): array
    {
        $N = class_exists('Bankai_SEO_Text_Normalizer') ? 'Bankai_SEO_Text_Normalizer' : null;
        $title = (string) ($o['seo_title'] ?? '');
        $desc  = (string) ($o['description'] ?? '');
        $kw    = trim((string) ($o['focus_keyword'] ?? ''));
        $checks = [];

        $contains = static function (string $hay, string $needle) use ($N): bool {
            if ($needle === '') {
                return false;
            }
            return $N ? $N::contains($hay, $needle) : (mb_stripos($hay, $needle) !== false);
        };

        if ($kw === '') {
            $checks[] = ['key' => 'focus_keyword', 'status' => 'fail', 'passed' => false, 'message' => 'no keyword'];
            $checks[] = ['key' => 'kw_in_title', 'status' => 'unknown', 'passed' => false, 'message' => 'unknown'];
            $checks[] = ['key' => 'kw_in_desc', 'status' => 'unknown', 'passed' => false, 'message' => 'unknown'];
        } else {
            $checks[] = ['key' => 'focus_keyword', 'status' => 'pass', 'passed' => true, 'message' => 'ok'];
            $t_ok = $contains($title, $kw);
            $checks[] = ['key' => 'kw_in_title', 'status' => $t_ok ? 'pass' : 'fail', 'passed' => $t_ok, 'message' => $t_ok ? 'ok' : 'missing kw'];
            $d_ok = $contains($desc, $kw);
            $checks[] = ['key' => 'kw_in_desc', 'status' => $d_ok ? 'pass' : 'fail', 'passed' => $d_ok, 'message' => $d_ok ? 'ok' : 'missing kw'];
        }

        $tlen = mb_strlen($title);
        $tlen_ok = $tlen >= 30 && $tlen <= 60;
        $checks[] = ['key' => 'title_length', 'status' => $tlen_ok ? 'pass' : 'warning', 'passed' => $tlen_ok, 'message' => "len=$tlen"];

        $dlen = mb_strlen($desc);
        if ($desc === '') {
            $checks[] = ['key' => 'desc_length', 'status' => 'fail', 'passed' => false, 'message' => 'empty'];
        } else {
            $dlen_ok = $dlen >= 120 && $dlen <= 160;
            $checks[] = ['key' => 'desc_length', 'status' => $dlen_ok ? 'pass' : 'warning', 'passed' => $dlen_ok, 'message' => "len=$dlen"];
        }

        $pass = 0;
        $total = 0;
        foreach ($checks as $c) {
            if (($c['status'] ?? '') === 'not_applicable') {
                continue;
            }
            $total++;
            if (($c['status'] ?? '') === 'pass') {
                $pass++;
            }
        }
        return [
            'score'  => $total ? (int) round($pass / $total * 100) : 0,
            'passed' => $pass,
            'total'  => $total,
            'checks' => $checks,
            'groups' => [],
            'stats'  => [],
        ];
    }

    /**
     * @param array<string,mixed> $analysis
     * @return list<array{key:string,status:string,message:string}>
     */
    private function extract_meta_failures(array $analysis, bool $need_title, bool $need_desc): array
    {
        $keys = [];
        if ($need_title) {
            $keys = array_merge($keys, ['kw_in_title', 'title_length', 'kw_start_title']);
        }
        if ($need_desc) {
            $keys = array_merge($keys, ['kw_in_desc', 'desc_length']);
        }
        $failed = [];
        foreach ($analysis['checks'] ?? [] as $c) {
            $key = (string) ($c['key'] ?? $c['id'] ?? '');
            if (!in_array($key, $keys, true)) {
                continue;
            }
            $st = (string) ($c['status'] ?? ($c['passed'] ? 'pass' : 'fail'));
            if (in_array($st, ['pass', 'not_applicable'], true)) {
                continue;
            }
            // kw_start_title is soft: only fail if explicit fail and keyword set
            if ($key === 'kw_start_title' && $st === 'warning') {
                continue;
            }
            $failed[] = [
                'key'     => $key,
                'status'  => $st,
                'message' => (string) ($c['message'] ?? ''),
                'label'   => (string) ($c['label'] ?? $key),
            ];
        }
        return $failed;
    }

    /**
     * @param list<array{key?:string,message?:string}> $failed
     */
    private function format_repair_instructions(array $failed, string $kind, bool $is_fa): string
    {
        $lines = [];
        foreach ($failed as $f) {
            $k = $f['key'] ?? '';
            $m = $f['message'] ?? '';
            if ($kind === 'title' && in_array($k, ['kw_in_title', 'title_length', 'kw_start_title'], true)) {
                $lines[] = "- {$k}: {$m}";
            }
            if ($kind === 'description' && in_array($k, ['kw_in_desc', 'desc_length'], true)) {
                $lines[] = "- {$k}: {$m}";
            }
        }
        if (!$lines) {
            return '';
        }
        if ($is_fa) {
            return "اصلاح الزامی — خروجی قبلی این معیارها را رد کرد:\n" . implode("\n", $lines)
                . "\nفقط خروجی نهایی را بنویس. برای عنوان: ۳۰ تا ۶۰ کاراکتر و شامل کلمه کلیدی. برای توضیحات: ۱۲۰ تا ۱۶۰ کاراکتر و شامل کلمه کلیدی. بدون توضیح اضافه.";
        }
        return "Required fix — previous output failed:\n" . implode("\n", $lines)
            . "\nOutput only the final text. Title: 30–60 chars with focus keyword. Description: 120–160 chars with focus keyword.";
    }

    public function ajax_seo_task(): void

    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        // Long-running AI calls (meta, rewrite, links…)
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }

        $task          = sanitize_key(wp_unslash($_POST['task'] ?? ''));

        // Phase C pipeline: meta / meta_title / meta_description with deterministic validation
        if (in_array($task, ['meta', 'met', 'meta_title', 'meta_description', 'title', 'description'], true)) {
            $post_id = absint($_POST['post_id'] ?? $_POST['id'] ?? 0);
            if ($post_id && !current_user_can('edit_post', $post_id)) {
                wp_send_json_error(['message' => 'Permission denied for this post'], 403);
            }
            $mode = 'both';
            if (in_array($task, ['meta_title', 'title'], true)) {
                $mode = 'title';
            } elseif (in_array($task, ['meta_description', 'description'], true)) {
                $mode = 'description';
            }
            $result = $this->run_meta_generation_pipeline([
                'post_id'              => $post_id,
                'title'                => sanitize_text_field(wp_unslash($_POST['title'] ?? '')),
                'content'              => wp_unslash($_POST['content'] ?? ''),
                'focus_keyword'        => sanitize_text_field(wp_unslash($_POST['focus_keyword'] ?? '')),
                'locale'               => sanitize_text_field(wp_unslash($_POST['locale'] ?? get_user_locale())),
                'provider'             => sanitize_key(wp_unslash($_POST['provider'] ?? '')),
                'mode'                 => $mode,
                'current_seo_title'    => sanitize_text_field(wp_unslash($_POST['seo_title'] ?? $_POST['current_seo_title'] ?? '')),
                'current_description'  => sanitize_text_field(wp_unslash($_POST['description'] ?? $_POST['current_description'] ?? '')),
            ]);
            if (($result['generation_status'] ?? '') === 'error' && ($result['seo_title'] ?? '') === '' && ($result['description'] ?? '') === '') {
                wp_send_json_error([
                    'message' => $result['message'] ?? 'AI generation failed',
                    'generation_status' => 'error',
                    'validation_status' => $result['validation_status'] ?? 'failed',
                    'attempts' => $result['attempts'] ?? 0,
                ], 500);
            }
            wp_send_json_success($result);
        }
        // alias لینک داخلی
        if ($task === 'internal_links') {
            $task = 'smart_internal_links';
        }
        $title         = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $raw_content   = wp_unslash($_POST['content'] ?? '');
        $focus         = sanitize_text_field(wp_unslash($_POST['focus_keyword'] ?? ''));
        $locale        = sanitize_text_field(wp_unslash($_POST['locale'] ?? 'fa_IR'));
        $provider      = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $img_context   = sanitize_text_field(wp_unslash($_POST['image_context'] ?? $_POST['context'] ?? ''));
        $img_url       = esc_url_raw(wp_unslash($_POST['image_url'] ?? $_POST['src'] ?? ''));
        $articles_data = wp_unslash($_POST['articles'] ?? $_POST['site_articles'] ?? '');
        $rewrite_style = sanitize_key(wp_unslash($_POST['rewrite_style'] ?? 'seo'));

        // Phase D: SEO assistant always uses central text provider
        $provider = $this->resolve_central_text_provider($provider);
        $ready_err = $this->text_provider_readiness_error($provider);
        if ($ready_err !== '') {
            wp_send_json_error(['message' => $ready_err, 'generation_status' => 'error'], 400);
        }

        if ($task === 'rewrite') {
            // Keep light HTML structure for rewrite quality
            $content = wp_kses($raw_content, [
                'p' => [], 'br' => [], 'h2' => [], 'h3' => [], 'h4' => [],
                'ul' => [], 'ol' => [], 'li' => [],
                'strong' => [], 'b' => [], 'em' => [], 'i' => [],
                'a' => ['href' => [], 'title' => []],
            ]);
            $content = mb_substr($content, 0, 14000);
        } else {
            $content = wp_strip_all_tags($raw_content);
            $content = mb_substr(preg_replace('/\s+/u', ' ', $content), 0, 8000);
        }
        // Prefer article language: Persian script in content/title wins over locale alone
        $sample_lang = $title . ' ' . mb_substr(wp_strip_all_tags($raw_content), 0, 800);
        $persian_chars = preg_match_all('/[\x{0600}-\x{06FF}]/u', $sample_lang);
        $latin_chars = preg_match_all('/[A-Za-z]/', $sample_lang);
        if ($persian_chars >= 8 && $persian_chars >= $latin_chars) {
            $is_fa = true;
        } elseif ($latin_chars >= 20 && $latin_chars > $persian_chars * 2) {
            $is_fa = false;
        } else {
            $is_fa = (stripos($locale, 'fa') !== false || stripos($locale, 'persian') !== false || stripos($locale, 'ar') !== false);
        }

        $extra = [
            'img_context'   => $img_context,
            'img_url'       => $img_url,
            'articles_data' => $articles_data,
            'rewrite_style' => $rewrite_style ?? 'seo',
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

        if ($is_fa && in_array($task, ['meta_title', 'title', 'meta_description', 'description'], true)) {
            $check = (string) ($data['seo_title'] ?? $data['description'] ?? $data['text'] ?? '');
            if ($this->looks_like_instruction_echo($check)) {
                try {
                    $retry_user = ($task === 'meta_description' || $task === 'description')
                        ? "فقط متا دیسکریپشن فارسی (۱۴۰ تا ۱۶۰ کاراکتر) برای «{$title}» با کلمه کلیدی «{$focus}» بنویس. هیچ انگلیسی ننویس."
                        : "فقط عنوان سئو فارسی (۴۵ تا ۶۰ کاراکتر) برای «{$title}» با کلمه کلیدی «{$focus}» بنویس. هیچ انگلیسی ننویس.";
                    $raw2 = $this->chat($provider, 'فقط خروجی نهایی فارسی. بدون توضیح.', $retry_user, [
                        'temperature' => 0.3,
                        'max_tokens'  => ($task === 'meta_description' || $task === 'description') ? 180 : 90,
                    ]);
                    $data = $this->parse_seo_response($task, $raw2, $focus, $is_fa);
                } catch (Throwable $e) {
                    // keep first
                }
            }
        }

        wp_send_json_success($data);
    }

    private function looks_like_instruction_echo(string $text): bool
    {
        $text = trim($text);
        if ($text === '') {
            return true;
        }
        if (preg_match('/\b(we need to produce|must include|length between|characters inclusive|output only the|hard limit)\b/i', $text)) {
            return true;
        }
        $fa = preg_match_all('/[\x{0600}-\x{06FF}]/u', $text);
        $en = preg_match_all('/[A-Za-z]/', $text);
        if ($en > 20 && $fa < 10) {
            return true;
        }
        return false;
    }


    /**
     * اجرای یک task سئو از داخل همان درخواست AJAX (بدون wp_send_json).
     */
    private function run_seo_task_inline(string $task, array $post): array
    {
        $title       = sanitize_text_field(wp_unslash($post['title'] ?? ''));
        $raw_content = wp_unslash($post['content'] ?? '');
        $content     = wp_strip_all_tags((string) $raw_content);
        $focus       = sanitize_text_field(wp_unslash($post['focus_keyword'] ?? ''));
        $locale      = sanitize_text_field(wp_unslash($post['locale'] ?? 'fa_IR'));
        $provider    = sanitize_key(wp_unslash($post['provider'] ?? ''));
        if ($provider === '') {
            $provider = $this->get_default_provider();
        }
        $is_fa = (stripos($locale, 'fa') !== false || $locale === 'fa_IR');
        $extra = [
            'img_context'   => sanitize_text_field(wp_unslash($post['image_context'] ?? $post['context'] ?? '')),
            'img_url'       => esc_url_raw(wp_unslash($post['image_url'] ?? $post['src'] ?? '')),
            'articles_data' => wp_unslash($post['articles_data'] ?? $post['articles'] ?? ''),
        ];
        $prompts = $this->build_seo_prompts($task, $title, $content, $focus, $is_fa, $extra);
        if (!$prompts) {
            return [];
        }
        try {
            $raw = $this->chat($provider, $prompts['system'], $prompts['user'], [
                'temperature' => $prompts['temperature'] ?? 0.55,
                'max_tokens'  => $prompts['max_tokens'] ?? 800,
            ]);
            return $this->parse_seo_response($task, (string) $raw, $focus, $is_fa) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function build_seo_prompts(string $task, string $title, string $content, string $focus, bool $is_fa, array $extra = []): ?array
    {
        $snip_len = ($task === 'rewrite') ? 12000 : 4000;
        $snippet = $content !== '' ? mb_substr($content, 0, $snip_len) : '(خالی)';
        $style = sanitize_key((string) ($extra['rewrite_style'] ?? 'seo'));

        $guidelines_ctx = self::format_guidelines_prompt_context();
        if ($is_fa) {
            $lang_rule = 'زبان خروجی فقط فارسی (پارسی ایران) است. هیچ کلمه انگلیسی، لاتین یا ترجمه انگلیسی ننویس مگر نام برند یا URL. از اعداد فارسی در متن عادی استفاده نکن مگر ضروری.';
            $base_sys = "تو «دستیار سئوی بنکای» هستی؛ متخصص سئو و نویسندگی برای وردپرس فارسی. {$lang_rule}\n{$guidelines_ctx}\n"
                . 'بر اساس نیت جستجو بنویس، کلمه کلیدی را طبیعی به کار ببر، پر کردن کلمه کلیدی نکن، و واقعیت خارج از محتوا اختراع نکن. '
                . 'فقط خروجی خواسته‌شده را بده؛ بدون توضیح اضافه، بدون markdown و بدون ```.';
        } else {
            $lang_rule = 'Respond only in English.';
            $base_sys = "You are Bankai SEO Agent for WordPress. {$lang_rule}\n{$guidelines_ctx}\n"
                . 'Follow search intent, use the focus keyword naturally, no keyword stuffing, no invented facts. '
                . 'Output only what is asked. No markdown fences.';
        }

        switch ($task) {
            case 'meta_title':
            case 'title':
                if ($is_fa) {
                    return [
                        'system' => 'نقش تو: نویسنده عنوان سئو فارسی. پاسخ تو باید فقط یک خط عنوان فارسی باشد. هرگز انگلیسی ننویس. هرگز دستورالعمل، توضیح، یا جمله‌ای مثل We need / Must / length ننویس. فقط عنوان نهایی.',
                        'user'   => "عنوان سئو بنویس.\nقوانین: فقط فارسی | ۳۰ تا ۶۰ کاراکتر | کلمه کلیدی نزدیک ابتدا | بدون گیومه | بدون تکرار مصنوعی کلمه کلیدی\nکلمه کلیدی: {$focus}\nموضوع/عنوان: {$title}\nخلاصه محتوا: " . mb_substr($snippet, 0, 600) . "\n\nعنوان:",
                        'temperature' => 0.35,
                        'max_tokens'  => 90,
                    ];
                }
                return [
                    'system' => 'You write SEO titles. Reply with ONLY the final title line. No instructions, no quotes, no preamble.',
                    'user'   => "SEO title (45-60 chars), include focus keyword near start.\nFocus: {$focus}\nTitle: {$title}\nExcerpt: " . mb_substr($snippet, 0, 600) . "\n\nTitle:",
                    'temperature' => 0.4,
                    'max_tokens'  => 80,
                ];

            case 'meta_description':
            case 'description':
                if ($is_fa) {
                    return [
                        'system' => 'نقش تو: نویسنده متا دیسکریپشن فارسی. پاسخ تو باید فقط همان متن توضیحات باشد. هرگز انگلیسی ننویس. هرگز دستورالعمل یا جمله We need / Must / length ننویس.',
                        'user'   => "متا دیسکریپشن بنویس.\nقوانین: فقط فارسی | ۱۲۰ تا ۱۶۰ کاراکتر | شامل کلمه کلیدی به‌صورت طبیعی | خلاصهٔ واقعی محتوا | بدون وعدهٔ نادرست | بدون گیومه\nکلمه کلیدی: {$focus}\nعنوان: {$title}\nخلاصه: " . mb_substr($snippet, 0, 800) . "\n\nتوضیحات:",
                        'temperature' => 0.4,
                        'max_tokens'  => 180,
                    ];
                }
                return [
                    'system' => 'You write SEO meta descriptions. Reply with ONLY the final description. No instructions or preamble.',
                    'user'   => "Meta description (120-160 chars) including focus keyword.\nFocus: {$focus}\nTitle: {$title}\nExcerpt: " . mb_substr($snippet, 0, 800) . "\n\nDescription:",
                    'temperature' => 0.45,
                    'max_tokens'  => 160,
                ];

            case 'focus_keyword':
                if ($is_fa) {
                    return [
                        'system' => $base_sys . ' فقط یک عبارت کلیدی اصلی (۲ تا ۵ کلمه فارسی) برگردان. بدون توضیح و بدون گیومه.',
                        'user'   => "بهترین کلمه/عبارت کلیدی اصلی این مقاله چیست؟\nعنوان: {$title}\nمحتوا:\n{$snippet}",
                        'temperature' => 0.25,
                        'max_tokens'  => 40,
                    ];
                }
                return [
                    'system' => $base_sys . ' Output ONLY one primary focus keyword (2–5 words).',
                    'user'   => "Best focus keyword?\nTitle: {$title}\nContent:\n{$snippet}",
                    'temperature' => 0.3,
                    'max_tokens'  => 40,
                ];

            case 'keywords':
                if ($is_fa) {
                    return [
                        'system' => $base_sys . ' فقط یک آرایه JSON از حداقل ۵ و حداکثر ۱۲ کلیدواژه فرعی فارسی (رشته). حداقل ۵ مورد الزامی است. مثال: ["کلمه۱","کلمه۲","کلمه۳","کلمه۴","کلمه۵"]. بدون markdown.',
                        'user'   => "حداقل ۵ کلیدواژه فرعی سئو پیشنهاد بده.\nکلمه اصلی: {$focus}\nعنوان: {$title}\nمحتوا:\n{$snippet}",
                        'temperature' => 0.4,
                        'max_tokens'  => 400,
                    ];
                }
                return [
                    'system' => $base_sys . ' Output JSON array of at least 5 and up to 12 secondary keywords only. Minimum 5 required.',
                    'user'   => "Suggest at least 5 secondary SEO keywords.\nFocus: {$focus}\nTitle: {$title}\nContent:\n{$snippet}",
                    'temperature' => 0.4,
                    'max_tokens'  => 350,
                ];

            case 'rewrite':
                $style_hint_fa = [
                    'seo'      => 'ساختار H2/H3 واضح، کلمه کلیدی طبیعی، پاراگراف‌های کوتاه.',
                    'formal'   => 'لحن رسمی و حرفه‌ای.',
                    'simple'   => 'زبان ساده و همه‌فهم؛ جملات کوتاه.',
                    'engaging' => 'جذاب و روان با حفظ دقت.',
                    'expand'   => 'گسترش مفید محتوا با حفظ موضوع.',
                    'shorten'  => 'کوتاه‌سازی با حفظ نکات کلیدی.',
                ];
                $style_hint_en = [
                    'seo' => 'Clear H2/H3, natural keyword usage.',
                    'formal' => 'Formal tone.',
                    'simple' => 'Simple language.',
                    'engaging' => 'Engaging tone.',
                    'expand' => 'Expand usefully.',
                    'shorten' => 'Condense, keep key points.',
                ];
                if ($is_fa) {
                    $hint = $style_hint_fa[$style] ?? $style_hint_fa['seo'];
                    return [
                        'system' => $base_sys . ' کل بدنه مقاله را بازنویسی کن. فقط HTML با تگ‌های p,h2,h3,ul,ol,li,strong,em,a. بدون markdown و بدون تگ html/body. ' . $hint,
                        'user'   => "بازنویسی کن.\nعنوان: {$title}\nکلمه کلیدی: {$focus}\nسبک: {$style}\n\nمحتوا:\n{$snippet}",
                        'temperature' => 0.6,
                        'max_tokens'  => 8000,
                    ];
                }
                $hint = $style_hint_en[$style] ?? $style_hint_en['seo'];
                return [
                    'system' => $base_sys . ' Rewrite article body as HTML fragments only. ' . $hint,
                    'user'   => "Rewrite.\nTitle: {$title}\nFocus: {$focus}\nStyle: {$style}\n\nContent:\n{$snippet}",
                    'temperature' => 0.62,
                    'max_tokens'  => 8000,
                ];

            case 'outline':
                if ($is_fa) {
                    return [
                        'system' => $base_sys . ' فقط فهرست سرفصل‌های H2/H3 به فارسی، هر خط یک سرفصل.',
                        'user'   => "طرح کلی سئو بساز.\nعنوان: {$title}\nکلمه کلیدی: {$focus}\nمحتوا:\n{$snippet}",
                        'temperature' => 0.45,
                        'max_tokens'  => 600,
                    ];
                }
                return [
                    'system' => $base_sys . ' H2/H3 outline only, one per line.',
                    'user'   => "SEO outline.\nTitle: {$title}\nFocus: {$focus}\nContent:\n{$snippet}",
                    'temperature' => 0.5,
                    'max_tokens'  => 600,
                ];

            case 'alt_text':
            case 'image_alt_text':
                $ctx_str = !empty($extra['img_context']) ? "\nبافت اطراف تصویر: " . $extra['img_context'] : '';
                $url_str = !empty($extra['img_url']) ? "\nآدرس تصویر: " . $extra['img_url'] : '';
                if ($is_fa) {
                    return [
                        'system' => $base_sys . ' فقط یک متن جایگزین (alt) کوتاه و توصیفی فارسی زیر ۱۲۵ کاراکتر. بدون گیومه و بدون پیشوند.',
                        'user'   => "alt مناسب تصویر داخل مقاله.\nعنوان مقاله: {$title}\nکلمه کلیدی: {$focus}{$ctx_str}{$url_str}\nگزیده:\n{$snippet}",
                        'temperature' => 0.35,
                        'max_tokens'  => 90,
                    ];
                }
                return [
                    'system' => $base_sys . ' Output ONLY short alt text under 125 chars.',
                    'user'   => "Alt for image.\nTitle: {$title}\nFocus: {$focus}{$ctx_str}{$url_str}\nExcerpt:\n{$snippet}",
                    'temperature' => 0.4,
                    'max_tokens'  => 80,
                ];

            case 'smart_internal_links':
                $art_str = !empty($extra['articles_data'])
                    ? (is_string($extra['articles_data']) ? $extra['articles_data'] : wp_json_encode($extra['articles_data'], JSON_UNESCAPED_UNICODE))
                    : '[]';
                if ($is_fa) {
                    return [
                        'system' => $base_sys . ' فقط یک آرایه JSON از پیشنهاد لینک داخلی. هر آیتم: keyword (عبارت لنگر فارسی موجود یا مناسب در متن)، target_post_id (عدد از لیست)، target_title، target_url، reason (یک جمله فارسی). فقط از مقالات همین لیست استفاده کن. بدون markdown.',
                        'user'   => "برای این مقاله لینک داخلی هوشمند پیشنهاد بده (حداکثر ۸ مورد).\nعنوان: {$title}\nکلمه کلیدی: {$focus}\nگزیده مقاله:\n{$snippet}\n\nمقالات موجود سایت (فقط از این‌ها انتخاب کن):\n{$art_str}",
                        'temperature' => 0.35,
                        'max_tokens'  => 1200,
                    ];
                }
                return [
                    'system' => $base_sys . ' Output ONLY JSON array of internal link objects with keyword, target_post_id, target_title, target_url, reason. Use only listed articles.',
                    'user'   => "Recommend internal links.\nTitle: {$title}\nFocus: {$focus}\nSnippet:\n{$snippet}\n\nSite articles:\n{$art_str}",
                    'temperature' => 0.4,
                    'max_tokens'  => 1000,
                ];

            case 'aeo_faq':
            case 'faq':
                if ($is_fa) {
                    return [
                        'system' => $base_sys . ' تو قطعه HTML شامل یک بخش سوالات متداول تولید می‌کنی. فقط تگ‌های <section class="bankai-faq">, <h2>سوالات متداول</h2>, <h3>پرسش؟</h3>, <p>پاسخ کوتاه و صریح...</p> خروجی بده. بدون markdown و بدون ```.',
                        'user'   => "برای این مقاله یک بخش سوالات متداول (۲ تا ۴ پرسش و پاسخ صریح) بنویس.\nعنوان مقاله: {$title}\nکلمه کلیدی: {$focus}\nگزیده محتوا:\n{$snippet}",
                        'temperature' => 0.45,
                        'max_tokens'  => 1200,
                    ];
                }
                return [
                    'system' => $base_sys . ' Output ONLY HTML fragment for FAQ section with <section class="bankai-faq">, <h2>FAQ</h2>, <h3>Question?</h3>, <p>Direct answer...</p>. No markdown fences.',
                    'user'   => "Generate FAQ section (2-4 QA pairs) for this article.\nTitle: {$title}\nFocus: {$focus}\nSnippet:\n{$snippet}",
                    'temperature' => 0.5,
                    'max_tokens'  => 1200,
                ];

            case 'geo_summary':
            case 'summary':
                if ($is_fa) {
                    return [
                        'system' => $base_sys . ' تو قطعه HTML خلاصه نکات کلیدی تولید می‌کنی. فقط تگ‌های <div class="bankai-summary">, <h2>خلاصه نکات کلیدی</h2>, <ul>, <li>نکته...</li> خروجی بده. بدون markdown.',
                        'user'   => "یک خلاصه نکات کلیدی (۳ تا ۵ مورد مهم) برای موتورهای هوش مصنوعی بنویس.\nعنوان مقاله: {$title}\nکلمه کلیدی: {$focus}\nگزیده محتوا:\n{$snippet}",
                        'temperature' => 0.4,
                        'max_tokens'  => 800,
                    ];
                }
                return [
                    'system' => $base_sys . ' Output ONLY HTML fragment for Key Takeaways summary with <div class="bankai-summary">, <h2>Key Takeaways</h2>, <ul>, <li>...</li>. No markdown fences.',
                    'user'   => "Generate Key Takeaways summary for AI engines.\nTitle: {$title}\nFocus: {$focus}\nSnippet:\n{$snippet}",
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
                $title = $this->clean_one_line($text, 60);
                $out['seo_title'] = $title;
                $out['text'] = $title;
                break;

            case 'meta_description':
            case 'description':
                $desc = $this->clean_one_line($text, 160);
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

            case 'aeo_faq':
            case 'faq':
                $out['faq_html'] = $text;
                $out['content'] = $text;
                break;

            case 'geo_summary':
            case 'summary':
                $out['summary_html'] = $text;
                $out['content'] = $text;
                break;

            case 'smart_internal_links':
                $links = [];
                $decoded = null;
                if (preg_match('/\[[\s\S]*\]/u', $text, $m)) {
                    $decoded = json_decode($m[0], true);
                }
                if (!is_array($decoded)) {
                    $decoded = json_decode($text, true);
                }
                if (is_array($decoded)) {
                    // Handle { "links": [...] }
                    if (isset($decoded['links']) && is_array($decoded['links'])) {
                        $decoded = $decoded['links'];
                    }
                    foreach ($decoded as $item) {
                        if (!is_array($item)) {
                            continue;
                        }
                        $kw  = sanitize_text_field((string) ($item['keyword'] ?? $item['anchor'] ?? $item['phrase'] ?? ''));
                        $url = esc_url_raw((string) ($item['target_url'] ?? $item['url'] ?? $item['permalink'] ?? ''));
                        $pid = absint($item['target_post_id'] ?? $item['post_id'] ?? $item['id'] ?? 0);
                        $ttl = sanitize_text_field((string) ($item['target_title'] ?? $item['title'] ?? ''));
                        if ($pid > 0 && $url === '') {
                            $url = (string) get_permalink($pid);
                        }
                        if ($pid > 0 && $ttl === '') {
                            $ttl = get_the_title($pid);
                        }
                        if ($kw === '' || $url === '') {
                            continue;
                        }
                        $links[] = [
                            'keyword'        => $kw,
                            'target_post_id' => $pid,
                            'target_title'   => $ttl,
                            'target_url'     => $url,
                            'reason'         => sanitize_text_field((string) ($item['reason'] ?? $item['why'] ?? '')),
                        ];
                    }
                }
                $out['links'] = array_slice($links, 0, 12);
                break;
        }

        return $out;
    }

    private function clean_one_line(string $text, int $max): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));
        if (count($lines) > 1) {
            $best = '';
            $bestScore = -1;
            foreach ($lines as $line) {
                $line = trim($line, " \t\"'«»");
                if ($line === '') {
                    continue;
                }
                if (preg_match('/^(we need|must |output only|hard (limit|length)|length between|including spaces|rules?:|قوانین|خروجی فقط)/iu', $line)) {
                    continue;
                }
                if (preg_match('/\b(we need to produce|must include keyword|characters inclusive)\b/iu', $line)) {
                    continue;
                }
                $fa = preg_match_all('/[\x{0600}-\x{06FF}]/u', $line);
                $score = $fa * 3 + min(60, mb_strlen($line));
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $line;
                }
            }
            if ($best !== '') {
                $text = $best;
            }
        }

        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text, " \t\"'«»\n\r");
        $text = preg_replace('/^(title|seo title|meta title|description|meta description|keyword|focus keyword|عنوان|توضیحات|کلمه کلیدی|عنوان سئو|متا)\s*[:：\-]\s*/iu', '', $text) ?? $text;

        if (preg_match('/\b(we need to produce|must include|length between|characters inclusive)\b/iu', $text)) {
            if (preg_match_all('/[\x{0600}-\x{06FF}][^A-Za-z]{0,200}/u', $text, $mm)) {
                $joined = trim(implode('', $mm[0]));
                if (mb_strlen($joined) >= 12) {
                    $text = $joined;
                }
            }
        }

        if (mb_strlen($text) > $max) {
            $text = mb_substr($text, 0, $max - 1);
            $text = preg_replace('/\s+\S*$/u', '', $text) ?? $text;
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
        $parts = preg_split('/[،\n]+/u', $raw);
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
        // Explicit model from caller (e.g. UI active model) wins
        if (!empty($opts['model']) && is_string($opts['model'])) {
            $model = trim($opts['model']);
        }

        if ($key === '') {
            foreach ($keys as $pid => $k) {
                if (is_string($k) && trim($k) !== '') {
                    $provider = $pid;
                    $key      = trim($k);
                    if (empty($opts['model'])) {
                        $model = trim((string) ($models[$pid] ?? ''));
                    }
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
        $no_fallback = !empty($opts['no_fallback']) || !empty($opts['strict_model']);
        $fallback_models = [];
        if ($no_fallback) {
            // تست اتصال / پینگ: فقط مدل فعال
            if ($model !== '') {
                $fallback_models = [$model];
            } elseif (isset($catalog[$provider]['default'])) {
                $fallback_models = [(string) $catalog[$provider]['default']];
            }
        } else {
            if (isset($catalog[$provider]['models']) && is_array($catalog[$provider]['models'])) {
                $fallback_models = $catalog[$provider]['models'];
            }
            if ($model !== '') {
                array_unshift($fallback_models, $model);
            }
        }
        $fallback_models = array_values(array_unique(array_filter($fallback_models)));
        if ($fallback_models === []) {
            throw new Exception('هیچ مدل فعالی برای این ارائه‌دهنده انتخاب نشده است.');
        }

        $last_error = null;
        foreach ($fallback_models as $try_model) {
            try {
                return $this->dispatch_chat($provider, $key, $try_model, $system, $user, $temperature, $max_tokens);
            } catch (Throwable $e) {
                $last_error = $e;
                $msg = $e->getMessage();
                // Fall through on model-not-found OR transient server errors
                if (!preg_match('/404|not found|no longer available|does not exist|invalid model|model_not_found|429|500|502|503|504|timeout|timed out/i', $msg)) {
                    // still try next model for empty/unknown errors
                    if (!preg_match('/empty|پاسخ خالی|Empty/i', $msg)) {
                        // continue to cross-provider below rather than hard-fail immediately
                        break;
                    }
                }
            }
        }

        // Cross-provider fallback: try other providers that have API keys
        $tried = [$provider => true];
        foreach ($keys as $pid => $k) {
            $k = is_string($k) ? trim($k) : '';
            if ($k === '' || isset($tried[$pid])) {
                continue;
            }
            $tried[$pid] = true;
            $models_p = [];
            if (isset($catalog[$pid]['models']) && is_array($catalog[$pid]['models'])) {
                $models_p = $catalog[$pid]['models'];
            }
            $sel = trim((string) ($models[$pid] ?? ''));
            if ($sel !== '') {
                array_unshift($models_p, $sel);
            }
            $models_p = array_values(array_unique(array_filter($models_p)));
            foreach ($models_p as $try_model) {
                try {
                    return $this->dispatch_chat($pid, $k, $try_model, $system, $user, $temperature, $max_tokens);
                } catch (Throwable $e) {
                    $last_error = $e;
                    break; // next provider
                }
            }
        }

        throw new Exception('هیچ هوش مصنوعی فعالی ندارید لطفا میزان مصرف خود در هوش مصنوعی مورد نظر را برسی کنید');
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
        $token   = $key;
        if (str_contains($key, '|')) {
            [$account, $token] = array_map('trim', explode('|', $key, 2));
        } else {
            $account = (string) get_option('bankai_cloudflare_account_id', '');
            // Also try provider meta storage
            if ($account === '' && function_exists('bankai_get_option')) {
                $account = (string) (bankai_get_option('cloudflare_account_id', '') ?: '');
            }
        }
        if ($account === '' || $token === '') {
            throw new Exception('Cloudflare نیاز به Account ID و API Token دارد (فرمت: AccountID|Token).');
        }

        $model = trim($model) !== '' ? trim($model) : '@cf/meta/llama-3.1-8b-instruct';
        $is_workers = str_starts_with($model, '@cf/');
        $is_gpt_oss = str_contains($model, 'gpt-oss');
        $base = 'https://api.cloudflare.com/client/v4/accounts/' . rawurlencode($account);

        // 1) Responses API — preferred for gpt-oss and unified third-party models (user curl shape)
        if ($is_gpt_oss || !$is_workers) {
            try {
                $url = $base . '/ai/v1/responses';
                $body = [
                    'model'             => $model,
                    'input'             => trim($system) !== ''
                        ? ("System: " . $system . "\n\nUser: " . $user)
                        : $user,
                    'max_output_tokens' => max(64, $max),
                ];
                // reasoning effort only when model likely supports it
                if ($is_gpt_oss || str_contains($model, 'gpt-') || str_contains($model, 'o3') || str_contains($model, 'o4')) {
                    $body['reasoning'] = ['effort' => 'medium'];
                }
                $res = $this->http_json($url, $body, [
                    'Authorization' => 'Bearer ' . $token,
                ]);
                $text = $this->extract_cf_response_text($res);
                if ($text !== '') {
                    return $text;
                }
            } catch (Exception $e) {
                // fall through to chat completions / run
            }
        }

        // 2) OpenAI-compatible Chat Completions (Workers AI + Unified)
        try {
            $url = $base . '/ai/v1/chat/completions';
            $messages = [];
            if (trim($system) !== '') {
                $messages[] = ['role' => 'system', 'content' => $system];
            }
            $messages[] = ['role' => 'user', 'content' => $user];
            $body = [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => max(64, $max),
                'temperature' => $temp,
            ];
            $res = $this->http_json($url, $body, [
                'Authorization' => 'Bearer ' . $token,
            ]);
            $text = $res['choices'][0]['message']['content']
                ?? ($res['result']['choices'][0]['message']['content'] ?? '');
            if (is_array($text)) {
                $text = wp_json_encode($text, JSON_UNESCAPED_UNICODE);
            }
            if (is_string($text) && $text !== '') {
                return $text;
            }
            // CF envelope errors
            $err = $res['errors'][0]['message'] ?? ($res['error']['message'] ?? '');
            if ($err !== '') {
                // continue to legacy run for @cf models
                if (!$is_workers) {
                    throw new Exception(is_string($err) ? $err : 'Cloudflare chat error');
                }
            }
        } catch (Exception $e) {
            if (!$is_workers) {
                throw $e;
            }
        }

        // 3) Legacy Workers AI run endpoint — most reliable for @cf/* models
        if ($is_workers) {
            $url = $base . '/ai/run/' . $model;
            $body = [
                'messages' => array_values(array_filter([
                    trim($system) !== '' ? ['role' => 'system', 'content' => $system] : null,
                    ['role' => 'user', 'content' => $user],
                ])),
                'max_tokens' => max(64, $max),
            ];
            $res = $this->http_json($url, $body, [
                'Authorization' => 'Bearer ' . $token,
            ]);
            $text = $res['result']['response'] ?? ($res['result']['output'] ?? '');
            if (is_array($text)) {
                $text = $text['response'] ?? ($text['text'] ?? wp_json_encode($text, JSON_UNESCAPED_UNICODE));
            }
            if (is_string($text) && $text !== '') {
                return $text;
            }
            $err = $res['errors'][0]['message'] ?? ($res['error']['message'] ?? 'Empty Cloudflare response');
            throw new Exception(is_string($err) ? $err : 'Cloudflare AI error');
        }

        throw new Exception('Cloudflare AI: پاسخ خالی یا مدل پشتیبانی نمی‌شود.');
    }

    /**
     * Parse OpenAI Responses API / CF unified envelope into plain text.
     */
    private function extract_cf_response_text(array $res): string
    {
        // Standard Responses API: output[].content[].text
        if (!empty($res['output']) && is_array($res['output'])) {
            $parts = [];
            foreach ($res['output'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (($item['type'] ?? '') === 'message' && !empty($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $c) {
                        if (is_array($c) && isset($c['text']) && is_string($c['text'])) {
                            $parts[] = $c['text'];
                        } elseif (is_string($c)) {
                            $parts[] = $c;
                        }
                    }
                }
                if (isset($item['text']) && is_string($item['text'])) {
                    $parts[] = $item['text'];
                }
            }
            if ($parts) {
                return implode("\n", $parts);
            }
        }
        // Flat fields
        foreach (['output_text', 'response', 'text'] as $k) {
            if (!empty($res[$k]) && is_string($res[$k])) {
                return $res[$k];
            }
        }
        if (!empty($res['result'])) {
            $r = $res['result'];
            if (is_string($r)) {
                return $r;
            }
            if (is_array($r)) {
                if (!empty($r['response']) && is_string($r['response'])) {
                    return $r['response'];
                }
                if (!empty($r['output_text']) && is_string($r['output_text'])) {
                    return $r['output_text'];
                }
            }
        }
        if (!empty($res['choices'][0]['message']['content'])) {
            $c = $res['choices'][0]['message']['content'];
            return is_string($c) ? $c : '';
        }
        return '';
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
            'timeout' => 150,
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

            // Cloudflare envelope: HTTP 200 but success=false
            if (array_key_exists('success', $data) && $data['success'] === false) {
                $msg = $data['errors'][0]['message']
                    ?? ($data['error']['message'] ?? null)
                    ?? ($data['messages'][0] ?? null)
                    ?? 'Cloudflare AI request failed';
                if (is_array($msg)) {
                    $msg = wp_json_encode($msg, JSON_UNESCAPED_UNICODE);
                }
                throw new Exception(is_string($msg) ? $msg : 'Cloudflare AI error');
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
        $model = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));
        try {
            $text = $this->chat($provider, 'Reply with exactly: OK', 'ping', [
                'max_tokens'   => 10,
                'temperature'  => 0,
                'no_fallback'  => true,
                'strict_model' => true,
                'model'        => $model,
            ]);
            $used = $model !== '' ? $model : ($this->get_selected_models()[$provider] ?? '');
            wp_send_json_success([
                'message'  => 'OK',
                'preview'  => mb_substr($text, 0, 80),
                'provider' => $provider,
                'model'    => $used,
            ]);
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
        // Keep active-providers list in sync
        $active = get_option('bankai_ai_active_text_providers', []);
        if (!is_array($active)) $active = [];
        if ($has) {
            $active[$provider] = true;
        } else {
            unset($active[$provider]);
        }
        update_option('bankai_ai_active_text_providers', $active, false);

        wp_send_json_success([
            'status'     => $has ? 'ready' : 'missing_key',
            'label'      => $has ? __('کلید تنظیم شده', 'bankai-core') : __('بدون کلید API', 'bankai-core'),
            'masked_key' => $has ? self::mask_key($keys[$provider]) : '',
            'message'    => __('ذخیره شد', 'bankai-core'),
            'active'     => array_keys(array_filter($active)),
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
        // Remove from active list
        $active = get_option('bankai_ai_active_text_providers', []);
        if (is_array($active)) {
            unset($active[$provider]);
            update_option('bankai_ai_active_text_providers', $active, false);
        }
        $keys[$provider] = '';
        update_option(self::OPT_KEYS, $keys, false);
        $this->mirror_keys_to_core($keys);

        wp_send_json_success([
            'status' => 'missing_key',
            'label'  => __('بدون کلید API', 'bankai-core'),
            'message'=> __('کلید حذف شد', 'bankai-core'),
        ]);
    }



    public function ajax_test_connection(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        if ($provider === '') {
            $provider = $this->get_default_provider();
        }
        $keys = $this->get_keys();
        if (empty($keys[$provider])) {
            foreach ($this->provider_status_list() as $row) {
                if (!empty($row['has_key'])) {
                    $provider = (string) $row['id'];
                    break;
                }
            }
        }
        if (empty($keys[$provider])) {
            wp_send_json_error(['message' => 'هیچ کلید API معتبری یافت نشد. ابتدا در استودیو هوش مصنوعی کلید را ذخیره کنید.']);
        }
        $model = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));
        try {
            $text = $this->chat($provider, 'Reply with exactly the word OK and nothing else.', 'ping', [
                'max_tokens'   => 16,
                'temperature'  => 0,
                'no_fallback'  => true,
                'strict_model' => true,
                'model'        => $model,
            ]);
            $ok = is_string($text) && trim($text) !== '';
            if (!$ok) {
                wp_send_json_error(['message' => 'پاسخ خالی از موتور AI']);
            }
            $used = $model !== '' ? $model : ($this->get_selected_models()[$provider] ?? '');
            wp_send_json_success([
                'message'  => 'اتصال برقرار است — مدل: ' . $used,
                'provider' => $provider,
                'model'    => $used,
                'sample'   => mb_substr(trim($text), 0, 40),
            ]);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
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
                    'accent' => $info['accent'] ?? '#0078d4',
                ];
                continue;
            }
            try {
                $selected = $this->get_selected_models();
                $active_model = (string) ($selected[$pid] ?? '');
                $text = $this->chat($pid, 'Reply with exactly the word OK and nothing else.', 'ping', [
                    'max_tokens'   => 16,
                    'temperature'  => 0,
                    'no_fallback'  => true,
                    'strict_model' => true,
                    'model'        => $active_model,
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
                    'accent' => $info['accent'] ?? '#0078d4',
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

        $img_catalog = self::image_providers_catalog();
        $img_keys = $this->get_image_keys();
        $img_results = [];
        foreach ($img_catalog as $ipid => $imeta) {
            if (empty($img_keys[$ipid])) {
                $img_results[] = [
                    'id'      => $ipid,
                    'name'    => $imeta['name'] ?? $ipid,
                    'status'  => 'missing_key',
                    'label'   => __('بدون کلید API', 'bankai-core'),
                    'ok'      => false,
                    'message' => __('کلید تنظیم نشده', 'bankai-core'),
                    'accent'  => $imeta['accent'] ?? '#10A37F',
                ];
                continue;
            }
            $img_results[] = [
                'id'      => $ipid,
                'name'    => $imeta['name'] ?? $ipid,
                'status'  => 'ready',
                'label'   => __('آماده ✓', 'bankai-core'),
                'ok'      => true,
                'message' => __('کلید فعال موجود است', 'bankai-core'),
                'accent'  => $imeta['accent'] ?? '#10A37F',
            ];
        }

        wp_send_json_success([
            'providers'       => $results,
            'image_providers' => $img_results,
            'message'         => __('تست اتصالات انجام شد', 'bankai-core'),
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
        if ($provider === '') {
            wp_send_json_error(['message' => __('ارائه‌دهنده خالی است', 'bankai-core')], 400);
        }
        // Accept built-in + custom agents
        $catalog = self::all_providers_catalog();
        if (!isset($catalog[$provider])) {
            wp_send_json_error(['message' => __('ارائه‌دهنده نامعتبر است', 'bankai-core')], 400);
        }
        update_option(self::OPT_DEFAULT, $provider, false);
        // Also mirror into core settings used by editor / SEO assistant
        if (function_exists('bankai_update_option')) {
            bankai_update_option('active_ai_provider', $provider);
        }
        $core = get_option('bankai_core_settings', []);
        if (!is_array($core)) {
            $core = [];
        }
        $core['active_ai_provider'] = $provider;
        $core['default_ai_provider'] = $provider;
        update_option('bankai_core_settings', $core, false);

        wp_send_json_success([
            'message'  => __('پیش‌فرض ذخیره شد', 'bankai-core'),
            'provider' => $provider,
            'name'     => $catalog[$provider]['name'] ?? $provider,
        ]);
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

        // Optional: fill fields from curl snippet
        $curl = (string) wp_unslash($_POST['curl_snippet'] ?? '');
        if ($curl !== '' && empty($_POST['endpoint'])) {
            if (preg_match('/curl\s+(?:-X\s+POST\s+)?["\']([^"\']+)["\']/i', $curl, $m)
                || preg_match('/curl\s+-X\s+POST\s+(\S+)/i', $curl, $m)) {
                $_POST['endpoint'] = $m[1];
            }
            if (preg_match('/"model"\s*:\s*"([^"]+)"/', $curl, $mm)) {
                if (empty($_POST['default_model'])) {
                    $_POST['default_model'] = $mm[1];
                }
                if (empty($_POST['models'])) {
                    $_POST['models'] = $mm[1];
                }
            }
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


    /**
     * Internal SEO task runner (modules / automation).
     */
    private function run_seo_task(string $task, array $ctx, array $extra = []): array
    {
        $title = (string) ($ctx['title'] ?? '');
        $content = (string) ($ctx['content'] ?? '');
        $focus = (string) ($ctx['focus_keyword'] ?? '');
        $locale = determine_locale();
        $is_fa = (stripos($locale, 'fa') !== false);
        $prompts = $this->build_seo_prompts($task, $title, $content, $focus, $is_fa, $extra);
        if ($prompts === null) {
            throw new Exception('Unknown SEO task: ' . $task);
        }
        $raw = $this->chat($this->get_default_provider(), $prompts['system'], $prompts['user'], [
            'temperature' => $prompts['temperature'] ?? 0.55,
            'max_tokens'  => $prompts['max_tokens'] ?? 800,
        ]);
        return $this->parse_seo_response($task, $raw, $focus, $is_fa);
    }

    /**
     * Module automation: auto SEO meta on publish when module enabled.
     */
    public function on_save_post_modules(int $post_id, $post = null, $update = null): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }
        $post = $post instanceof WP_Post ? $post : get_post($post_id);
        if (!$post || !in_array($post->post_status, ['publish', 'future'], true)) {
            return;
        }
        if (!in_array($post->post_type, ['post', 'page'], true)) {
            return;
        }
        $mods = get_option(self::OPT_MODULES, []);
        if (!is_array($mods) || empty($mods['auto_seo_meta'])) {
            return;
        }
        // Only fill empty fields — never overwrite manual SEO
        $title = (string) get_post_meta($post_id, '_bankai_seo_title', true);
        $desc  = (string) get_post_meta($post_id, '_bankai_seo_description', true);
        $kw    = (string) get_post_meta($post_id, '_bankai_seo_focus_keyword', true);
        if ($title !== '' && $desc !== '' && $kw !== '') {
            return;
        }
        try {
            $content = wp_strip_all_tags($post->post_content);
            $snippet = mb_substr($content, 0, 2500);
            $ctx = [
                'title' => $post->post_title,
                'content' => $snippet,
                'focus_keyword' => $kw,
            ];
            if ($kw === '') {
                $res = $this->run_seo_task('focus_keyword', $ctx);
                if (!empty($res['focus_keyword'])) {
                    update_post_meta($post_id, '_bankai_seo_focus_keyword', sanitize_text_field($res['focus_keyword']));
                    $kw = $res['focus_keyword'];
                    $ctx['focus_keyword'] = $kw;
                }
            }
            if ($title === '') {
                $res = $this->run_seo_task('meta_title', $ctx);
                if (!empty($res['seo_title'])) {
                    update_post_meta($post_id, '_bankai_seo_title', sanitize_text_field($res['seo_title']));
                }
            }
            if ($desc === '') {
                $res = $this->run_seo_task('meta_description', $ctx);
                if (!empty($res['description'])) {
                    update_post_meta($post_id, '_bankai_seo_description', sanitize_text_field($res['description']));
                }
            }
        } catch (Throwable $e) {
            // silent — never block publish
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Bankai AI modules] ' . $e->getMessage());
            }
        }
    }

    /**
     * Module: auto-suggest alt text when attachment added (queued style).
     */
    public function on_add_attachment_alt(int $attachment_id): void
    {
        $mods = get_option(self::OPT_MODULES, []);
        if (!is_array($mods) || empty($mods['alt_generator'])) {
            return;
        }
        if (!wp_attachment_is_image($attachment_id)) {
            return;
        }
        $alt = (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        if ($alt !== '') {
            return;
        }
        $title = get_the_title($attachment_id);
        $url = wp_get_attachment_url($attachment_id);
        try {
            $res = $this->run_seo_task('image_alt_text', [
                'title' => $title,
                'content' => $title,
                'focus_keyword' => '',
            ], [
                'img_url' => $url,
                'img_context' => $title,
            ]);
            $text = $res['text'] ?? ($res['alt'] ?? '');
            if (is_string($text) && trim($text) !== '') {
                update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field(mb_substr(trim($text), 0, 125)));
            }
        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Bankai alt module] ' . $e->getMessage());
            }
        }
    }



    /**
     * Convert classic HTML body into Gutenberg block markup so the editor is editable without "Convert to blocks".
     */
    public function html_to_gutenberg_blocks(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        // Already block content
        if (strpos($html, '<!-- wp:') !== false) {
            return $html;
        }
        // Strip accidental fences / wrappers
        $html = preg_replace('/^```(?:html)?\s*/i', '', $html);
        $html = preg_replace('/\s*```$/', '', $html);
        $html = preg_replace('/<\/?(?:html|body|head)[^>]*>/i', '', $html);
        $html = trim($html);

        // Normalize line breaks between block elements
        $html = preg_replace('/\r\n|\r/', "\n", $html);

        $blocks = [];
        // Match common block-level chunks
        $pattern = '/<(h[1-6]|p|ul|ol|blockquote|pre|figure|table)(\s[^>]*)?>[\s\S]*?<\/\1>/iu';
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[0] as $i => $chunk) {
                $tag = strtolower($matches[1][$i]);
                $chunk = trim($chunk);
                if ($tag === 'p') {
                    $blocks[] = "<!-- wp:paragraph -->\n{$chunk}\n<!-- /wp:paragraph -->";
                } elseif (preg_match('/^h([1-6])$/', $tag, $hm)) {
                    $level = (int) $hm[1];
                    $blocks[] = "<!-- wp:heading {\"level\":{$level}} -->\n{$chunk}\n<!-- /wp:heading -->";
                } elseif ($tag === 'ul') {
                    $blocks[] = "<!-- wp:list -->\n{$chunk}\n<!-- /wp:list -->";
                } elseif ($tag === 'ol') {
                    $blocks[] = "<!-- wp:list {\"ordered\":true} -->\n{$chunk}\n<!-- /wp:list -->";
                } elseif ($tag === 'blockquote') {
                    $blocks[] = "<!-- wp:quote -->\n{$chunk}\n<!-- /wp:quote -->";
                } elseif ($tag === 'pre') {
                    $blocks[] = "<!-- wp:code -->\n{$chunk}\n<!-- /wp:code -->";
                } elseif ($tag === 'figure') {
                    $blocks[] = "<!-- wp:html -->\n{$chunk}\n<!-- /wp:html -->";
                } elseif ($tag === 'table') {
                    $blocks[] = "<!-- wp:table -->\n<figure class=\"wp-block-table\">{$chunk}</figure>\n<!-- /wp:table -->";
                } else {
                    $blocks[] = "<!-- wp:html -->\n{$chunk}\n<!-- /wp:html -->";
                }
            }
        }

        if (!$blocks) {
            // Fallback: split by double newlines into paragraphs
            $plain = wp_strip_all_tags($html, true);
            $paras = preg_split('/\n{2,}/u', $plain) ?: [$plain];
            foreach ($paras as $para) {
                $para = trim($para);
                if ($para === '') {
                    continue;
                }
                $p = '<p>' . esc_html($para) . '</p>';
                $blocks[] = "<!-- wp:paragraph -->\n{$p}\n<!-- /wp:paragraph -->";
            }
        }

        return implode("\n\n", $blocks);
    }

    public function ajax_generate_article(): void
    {
        // Long-running AI generation — survive proxy/gateway timeouts as far as PHP can
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(600);
        }
        if (function_exists('ini_set')) {
            @ini_set('max_execution_time', '600');
            @ini_set('memory_limit', '512M');
        }
        // Ensure JSON even if something echoes HTML later (best-effort)
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Accel-Buffering: no');
        }

        try {
            check_ajax_referer('bankai_admin_nonce', 'nonce');
        } catch (Throwable $e) {
            wp_send_json_error(['message' => 'نشست منقضی شده — صفحه را تازه کنید'], 403);
        }
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'دسترسی مجاز نیست'], 403);
        }
        $topic = sanitize_text_field(wp_unslash($_POST['topic'] ?? ''));
        $focus = sanitize_text_field(wp_unslash($_POST['focus'] ?? ''));
        $length = absint($_POST['length'] ?? 1500);
        $status = sanitize_key($_POST['status'] ?? 'draft');
        $schedule_at = sanitize_text_field(wp_unslash($_POST['schedule_at'] ?? $_POST['publish_date'] ?? ''));
        $notes = sanitize_textarea_field(wp_unslash($_POST['notes'] ?? ''));
        $tone = sanitize_key(wp_unslash($_POST['tone'] ?? 'seo'));
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        if ($provider === '') {
            $provider = $this->get_default_provider();
        }
        // Ensure provider has a key when possible
        $keys = $this->get_keys();
        if (empty($keys[$provider])) {
            // fallback to first ready provider
            foreach ($this->provider_status_list() as $row) {
                if (!empty($row['has_key'])) {
                    $provider = $row['id'];
                    break;
                }
            }
            if (empty($keys[$provider])) {
                wp_send_json_error(['message' => 'هیچ موتور AI با کلید معتبر فعال نیست. ابتدا در استودیو هوش مصنوعی کلید را ذخیره کنید.']);
            }
        }
        if ($topic === '') {
            wp_send_json_error(['message' => 'موضوع خالی است']);
        }
        $safe_mode = !empty($_POST['safe_mode']) && ($_POST['safe_mode'] === '1' || $_POST['safe_mode'] === true || $_POST['safe_mode'] === 'true');
        // Cap length to reduce reverse-proxy 504; client retries with lower length on timeout
        $length = max(400, min($safe_mode ? 2500 : 5000, $length));
        $tone_map = [
            'seo' => 'لحن سئو استاندارد، دقیق و کاربردی',
            'engaging' => 'لحن جذاب، روایی و گیرا بدون اغراق',
            'formal' => 'لحن رسمی، تخصصی و دانشگاهی',
            'simple' => 'لحن ساده، روان و همه‌فهم',
        ];
        $tone_label = $tone_map[$tone] ?? $tone_map['seo'];
        $guide_ctx = self::format_guidelines_prompt_context();
        $system = 'نقش تو: نویسنده ارشد محتوا و سئوی فارسی برای وردپرس. '
            . 'فقط بدنهٔ HTML مقاله را بنویس با تگ‌های مجاز: p, h2, h3, ul, ol, li, strong, em, a. '
            . 'ممنوع: markdown، بلوک کد، تگ html/body/head، H1، عنوان انگلیسی، توضیح قبل از مقاله، «Certainly» یا هر متن غیرفارسی. '
            . 'حدود ' . $length . ' کلمهٔ واقعی فارسی (از این مقدار خیلی فراتر نرو). '
            . 'ساختار اجباری: ۱) مقدمهٔ کوتاه (۲–۳ پاراگراف) با پاسخ مستقیم به نیاز خواننده؛ ۲) چند بخش H2 با محتوای عمیق؛ ۳) در صورت نیاز H3؛ ۴) حداقل یک فهرست ul/ol؛ ۵) جمع‌بندی کاربردی. '
            . 'هر پاراگراف حداکثر ۳–۴ جمله. ' . $tone_label . '. '
            . 'کلمه کلیدی اصلی را فقط به‌صورت طبیعی در مقدمه، یک H2 و جمع‌بندی بیاور؛ چگالی تقریبی ۱٪ و بدون تکرار مصنوعی. '
            . ($guide_ctx !== '' ? "\nدستورالعمل برند سایت:\n" . $guide_ctx : '');
        $user = "عنوان مقاله: {$topic}
"
            . "کلمه کلیدی اصلی: " . ($focus !== '' ? $focus : $topic) . "
"
            . "طول هدف: حدود {$length} کلمه (از این مقدار فراتر نرو)
"
            . "دستورالعمل اضافه: " . ($notes !== '' ? $notes : 'ندارد') . "

"
            . "یک مقالهٔ کامل، تخصصی، قابل انتشار و بهینه‌شده برای سئو و خوانایی بنویس. "
            . "از کلیشه‌های ترجمه‌شده و پر کردن مصنوعی متن پرهیز کن. فقط HTML بدنه را برگردان.";
        $max_tokens = (int) min($safe_mode ? 3500 : 5500, max(1200, (int) ($length * 1.35)));
        try {
            $body = $this->chat($provider, $system, $user, [
                'temperature' => 0.65,
                'max_tokens'  => $max_tokens,
            ]);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if ($msg === '' || stripos($msg, '<html') !== false) {
                $msg = 'خطا در ارتباط با موتور AI یا تایم‌اوت سرور';
            }
            wp_send_json_error(['message' => $msg], 500);
        }
        if (!is_string($body) || trim($body) === '') {
            wp_send_json_error(['message' => 'پاسخ خالی از موتور AI — دوباره تلاش کنید یا موتور دیگری انتخاب کنید']);
        }
        // Clean model output then convert to Gutenberg blocks (avoids "Convert to blocks" in editor)
        try {
            $body = preg_replace('/^```(?:html)?\s*/i', '', trim((string) $body));
            $body = preg_replace('/\s*```$/', '', $body);
            $body = wp_kses_post($body);
            if (method_exists($this, 'html_to_gutenberg_blocks')) {
                $body = $this->html_to_gutenberg_blocks($body);
            }
        } catch (Throwable $e) {
            wp_send_json_error(['message' => 'خطا در تبدیل خروجی AI به بلوک‌های وردپرس: ' . $e->getMessage()], 500);
        }
        if (!is_string($body) || trim($body) === '') {
            wp_send_json_error(['message' => 'پس از پاک‌سازی، محتوای مقاله خالی شد']);
        }

        $postarr = [
            'post_title'   => $topic,
            'post_content' => $body,
            'post_status'  => $status === 'future' ? 'future' : 'draft',
            'post_type'    => 'post',
            'post_author'  => get_current_user_id(),
        ];
        if ($status === 'future' && $schedule_at !== '') {
            $ts = strtotime($schedule_at);
            if ($ts && $ts > time()) {
                $postarr['post_date'] = gmdate('Y-m-d H:i:s', $ts - (int) (get_option('gmt_offset') * HOUR_IN_SECONDS));
                $postarr['post_date_gmt'] = gmdate('Y-m-d H:i:s', $ts);
                $postarr['post_status'] = 'future';
            } else {
                $postarr['post_status'] = 'draft';
            }
        }
        $post_id = wp_insert_post($postarr, true);
        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => $post_id->get_error_message()]);
        }
        if ($focus !== '') {
            update_post_meta($post_id, '_bankai_seo_focus_keyword', $focus);
        }

        $seo_done = [];
        $do_seo = !empty($_POST['do_seo']);
        if ($do_seo) {
            $ctx = [
                'title' => $topic,
                'content' => mb_substr(wp_strip_all_tags($body), 0, 6000),
                'focus_keyword' => $focus,
            ];
            try {
                if ($focus === '') {
                    $res = $this->run_seo_task('focus_keyword', $ctx);
                    if (!empty($res['focus_keyword'])) {
                        $focus = sanitize_text_field((string) $res['focus_keyword']);
                        update_post_meta($post_id, '_bankai_seo_focus_keyword', $focus);
                        $ctx['focus_keyword'] = $focus;
                        $seo_done[] = 'focus_keyword';
                    }
                }
                $res = $this->run_seo_task('meta_title', $ctx);
                if (!empty($res['seo_title'])) {
                    update_post_meta($post_id, '_bankai_seo_title', sanitize_text_field((string) $res['seo_title']));
                    $seo_done[] = 'meta_title';
                }
                $res = $this->run_seo_task('meta_description', $ctx);
                if (!empty($res['description'])) {
                    update_post_meta($post_id, '_bankai_seo_description', sanitize_text_field((string) $res['description']));
                    $seo_done[] = 'meta_description';
                }
                $res = $this->run_seo_task('keywords', $ctx);
                $kws = $res['keywords'] ?? ($res['list'] ?? null);
                if (is_array($kws) && $kws) {
                    update_post_meta($post_id, '_bankai_seo_keywords', wp_json_encode(array_values($kws), JSON_UNESCAPED_UNICODE));
                    $seo_done[] = 'keywords';
                    $this->assign_terms_from_keywords((int) $post_id, $kws, $focus);
                    $seo_done[] = 'terms';
                }
            } catch (\Throwable $e) {
                // keep post even if SEO steps fail
            }
            if (class_exists('Bankai_SEO_Engine')) {
                try {
                    $analysis = Bankai_SEO_Engine::instance()->analyze((int) $post_id);
                    update_post_meta($post_id, '_bankai_seo_score', (int) ($analysis['score'] ?? 0));
                } catch (\Throwable $e) {}
            }
        }

        $queue = get_option('bankai_ai_publish_queue', []);
        if (!is_array($queue)) {
            $queue = [];
        }
        if (($postarr['post_status'] ?? '') === 'future') {
            $queue[] = [
                'post_id'     => $post_id,
                'title'       => $topic,
                'topic'       => $topic,
                'schedule_at' => $schedule_at,
                'status'      => 'scheduled',
            ];
            update_option('bankai_ai_publish_queue', $queue, false);
        }
        wp_send_json_success([
            'message'   => 'مقاله #' . $post_id . ' ایجاد شد (' . ($postarr['post_status'] ?? 'draft') . ')',
            'post_id'   => $post_id,
            'edit_link' => get_edit_post_link($post_id, 'raw'),
            'edit_url'  => get_edit_post_link($post_id, 'raw'),
            'permalink' => get_permalink($post_id),
            'seo_done'  => $seo_done ?? [],
            'queue'     => array_values($queue),
        ]);
    }

    public function ajax_remove_queue_item(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $idx = absint($_POST['index'] ?? -1);
        $queue = get_option('bankai_ai_publish_queue', []);
        if (!is_array($queue)) {
            $queue = [];
        }
        if ($idx >= 0 && isset($queue[$idx])) {
            array_splice($queue, $idx, 1);
            update_option('bankai_ai_publish_queue', array_values($queue), false);
        }
        wp_send_json_success(['queue' => array_values($queue)]);
    }

    public function cron_publish_scheduled(int $post_id): void
    {
        $post = get_post($post_id);
        if ($post && $post->post_status === 'future') {
            wp_publish_post($post_id);
        }
    }



    /**
     * Strip quotes, brackets, HTML entities from AI titles.
     */
    private function clean_ai_title(string $title): string
    {
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = trim($title);
        // Unwrap accidental JSON array / quoted wrappers
        if ((str_starts_with($title, '[') && str_ends_with($title, ']'))
            || (str_starts_with($title, '"') && str_ends_with($title, '"'))) {
            $decoded = json_decode($title, true);
            if (is_array($decoded) && isset($decoded[0]) && is_string($decoded[0])) {
                $title = $decoded[0];
            } elseif (is_string($decoded)) {
                $title = $decoded;
            }
        }
        $title = html_entity_decode((string) $title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = preg_replace('/^[\[\(\{\s«"\'‘’“”`]+/u', '', $title) ?? $title;
        $title = preg_replace('/[\]\)\}\s»"\'‘’“”`]+$/u', '', $title) ?? $title;
        $title = preg_replace('/\s+/u', ' ', $title) ?? $title;
        return sanitize_text_field(trim($title));
    }

    public function ajax_suggest_titles(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        try {
            check_ajax_referer('bankai_admin_nonce', 'nonce');
        } catch (Throwable $e) {
            wp_send_json_error(['message' => 'نشست منقضی شده — صفحه را تازه کنید'], 403);
        }
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'دسترسی مجاز نیست'], 403);
        }
        $topic = sanitize_text_field(wp_unslash($_POST['topic'] ?? ''));
        $count = max(3, min(20, absint($_POST['count'] ?? 10)));
        $category = sanitize_text_field(wp_unslash($_POST['category'] ?? ''));
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        if ($provider === '') {
            $provider = $this->get_default_provider();
        }
        if ($topic === '') {
            wp_send_json_error(['message' => 'موضوع یا کلمه کلیدی را وارد کنید']);
        }
        $keys = $this->get_keys();
        if (empty($keys[$provider])) {
            foreach ($this->provider_status_list() as $row) {
                if (!empty($row['has_key'])) {
                    $provider = (string) $row['id'];
                    break;
                }
            }
            if (empty($keys[$provider])) {
                wp_send_json_error(['message' => 'کلید API برای موتور انتخابی تنظیم نشده است']);
            }
        }

        // Context from existing site content (titles + keywords)
        $ctx_lines = [];
        $recent = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 25,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
        ]);
        foreach ($recent as $rid) {
            $rid = (int) $rid;
            $title = get_the_title($rid);
            $kws = get_post_meta($rid, '_bankai_seo_keywords', true);
            $focus = (string) get_post_meta($rid, '_bankai_seo_focus_keyword', true);
            $kw_list = [];
            if (is_string($kws) && $kws !== '') {
                $decoded = json_decode($kws, true);
                if (is_array($decoded)) {
                    $kw_list = array_map('strval', $decoded);
                } else {
                    $kw_list = preg_split('/[،]+/u', $kws) ?: [];
                }
            }
            if ($focus !== '') {
                array_unshift($kw_list, $focus);
            }
            $kw_list = array_slice(array_values(array_unique(array_filter(array_map('trim', $kw_list)))), 0, 6);
            $ctx_lines[] = '- ' . $title . ($kw_list ? ' | KW: ' . implode('، ', $kw_list) : '');
        }
        $site_ctx = $ctx_lines ? implode("\n", array_slice($ctx_lines, 0, 25)) : '(no prior posts)';

        $system = 'نقش تو: استراتژیست عنوان سئوی فارسی برای وردپرس. '
            . 'فقط یک آرایه JSON از رشته‌های عنوان فارسی برگردان. '
            . 'بدون markdown، بدون توضیح، بدون شماره‌گذاری، بدون کلید انگلیسی. '
            . 'هر آیتم فقط یک رشته عنوان باشد.';
        $user = "برای موضوع زیر {$count} عنوان مقالهٔ منحصربه‌فرد، جذاب و بهینه‌شده برای سئو به زبان فارسی پیشنهاد بده.\n"
            . "موضوع / کلمه کلیدی: {$topic}\n"
            . "دسته (راهنما): " . ($category !== '' ? $category : 'عمومی') . "\n\n"
            . "قوانین:\n"
            . "- طول هر عنوان ترجیحاً بین ۴۵ تا ۹۰ کاراکتر\n"
            . "- کلمه کلیدی را طبیعی و نزدیک ابتدای عنوان بیاور\n"
            . "- زاویه‌های متنوع: آموزشی، مقایسه‌ای، چک‌لیست، اشتباهات رایج، روندها، مبتدی در برابر حرفه‌ای\n"
            . "- از تکرار یا بازنویسی نزدیک عناوین موجود سایت پرهیز کن\n"
            . "- فقط فارسی؛ بدون اموجی و بدون گیومهٔ اضافه\n\n"
            . "عناوین و کلیدواژه‌های موجود سایت (فقط برای الهام و جلوگیری از تکرار):\n"
            . "{$site_ctx}\n\n"
            . "خروجی نمونه: [\"عنوان اول\", \"عنوان دوم\"]";
        try {
            $raw = $this->chat($provider, $system, $user, ['temperature' => 0.7, 'max_tokens' => 1200]);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
        $titles = [];
        $text = trim((string) $raw);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/```$/', '', $text);
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $titles[] = $this->clean_ai_title($item);
                } elseif (is_array($item) && !empty($item['title'])) {
                    $titles[] = $this->clean_ai_title((string) $item['title']);
                }
            }
        } else {
            foreach (preg_split('/\r\n|\n|\r/', $text) as $line) {
                $line = trim(preg_replace('/^[\d\-\.\*\)]+\s*/u', '', $line));
                if ($line !== '') {
                    $titles[] = $this->clean_ai_title($line);
                }
            }
        }
        $titles = array_values(array_unique(array_filter($titles)));
        $titles = array_slice($titles, 0, $count);
        if (!$titles) {
            wp_send_json_error(['message' => 'پیشنهادی دریافت نشد']);
        }
        wp_send_json_success(['titles' => $titles, 'provider' => $provider]);
    }

    /**
     * Assign categories/tags from keyword list (create if missing).
     */
    /**
     * Find existing term by name/slug (case-insensitive) or create it.
     * @return int term_id or 0
     */
    private function find_or_create_term(string $name, string $taxonomy): int
    {
        $name = trim(wp_strip_all_tags($name));
        if ($name === '') {
            return 0;
        }
        // Exact
        $found = term_exists($name, $taxonomy);
        if ($found) {
            return is_array($found) ? (int) $found['term_id'] : (int) $found;
        }
        // Slug match
        $slug = sanitize_title($name);
        $by_slug = get_term_by('slug', $slug, $taxonomy);
        if ($by_slug && !is_wp_error($by_slug)) {
            return (int) $by_slug->term_id;
        }
        // Case-insensitive scan among existing terms (limit)
        $all = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'number'     => 500,
        ]);
        if (!is_wp_error($all) && is_array($all)) {
            $needle = mb_strtolower($name);
            foreach ($all as $term) {
                if (mb_strtolower($term->name) === $needle) {
                    return (int) $term->term_id;
                }
            }
            // Partial / contains match for categories (prefer existing over proliferating)
            if ($taxonomy === 'category') {
                foreach ($all as $term) {
                    $tn = mb_strtolower($term->name);
                    if ($tn !== '' && (str_contains($needle, $tn) || str_contains($tn, $needle))) {
                        return (int) $term->term_id;
                    }
                }
            }
        }
        $created = wp_insert_term($name, $taxonomy);
        if (is_wp_error($created)) {
            // Maybe race: term created between checks
            $again = term_exists($name, $taxonomy);
            if ($again) {
                return is_array($again) ? (int) $again['term_id'] : (int) $again;
            }
            return 0;
        }
        return is_array($created) ? (int) $created['term_id'] : (int) $created;
    }

    /**
     * Assign categories/tags from keyword list (match existing or create).
     * Categories behave like tags: reuse similar names when possible.
     */
    private function assign_terms_from_keywords(int $post_id, array $keywords, string $focus = ''): array
    {
        $pool = [];
        if ($focus !== '') {
            $pool[] = $focus;
        }
        foreach ($keywords as $kw) {
            $kw = trim((string) $kw);
            if ($kw !== '') {
                $pool[] = $kw;
            }
        }
        $pool = array_values(array_unique($pool));
        if (!$pool) {
            return ['category_ids' => [], 'tag_ids' => [], 'categories' => [], 'tags' => []];
        }

        // Categories: first 1–2 distinct keywords (not pure noise)
        $cat_ids = [];
        $cat_candidates = array_slice($pool, 0, 2);
        foreach ($cat_candidates as $cname) {
            $id = $this->find_or_create_term($cname, 'category');
            if ($id > 0) {
                $cat_ids[] = $id;
            }
        }
        $cat_ids = array_values(array_unique(array_filter($cat_ids)));
        if ($cat_ids) {
            // Keep Uncategorized out of exclusive replace only if we have real cats
            wp_set_post_terms($post_id, $cat_ids, 'category', false);
        }

        // Tags: remaining keywords (max 10), create if missing (WP handles by name)
        $tag_names = array_slice($pool, 0, 10);
        $tag_ids = [];
        if ($tag_names) {
            // Ensure each tag exists then set by ID for reliability
            foreach ($tag_names as $tn) {
                $tid = $this->find_or_create_term($tn, 'post_tag');
                if ($tid > 0) {
                    $tag_ids[] = $tid;
                }
            }
            $tag_ids = array_values(array_unique($tag_ids));
            if ($tag_ids) {
                wp_set_post_terms($post_id, $tag_ids, 'post_tag', false);
            }
        }

        $cats = wp_get_post_terms($post_id, 'category', ['fields' => 'names']);
        $tags = wp_get_post_terms($post_id, 'post_tag', ['fields' => 'names']);
        return [
            'category_ids' => $cat_ids,
            'tag_ids'      => $tag_ids,
            'categories'   => is_wp_error($cats) ? [] : array_values($cats),
            'tags'         => is_wp_error($tags) ? [] : array_values($tags),
        ];
    }



    public function ajax_assign_terms(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id || !get_post($post_id)) {
            wp_send_json_error(['message' => 'پست نامعتبر']);
        }
        $focus = sanitize_text_field(wp_unslash($_POST['focus'] ?? ''));
        $keywords = [];
        $raw = wp_unslash($_POST['keywords'] ?? '[]');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $keywords = $decoded;
            }
        } elseif (is_array($raw)) {
            $keywords = $raw;
        }
        $result = $this->assign_terms_from_keywords($post_id, $keywords, $focus);
        wp_send_json_success([
            'message' => 'دسته و برچسب ذخیره شد',
            'categories' => $result['categories'] ?? [],
            'tags' => $result['tags'] ?? [],
            'category_ids' => $result['category_ids'] ?? [],
            'tag_ids' => $result['tag_ids'] ?? [],
        ]);
    }



    public function ajax_save_image_ai_key(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $key = sanitize_text_field(wp_unslash($_POST['api_key'] ?? ''));
        $model = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));
        $cat = self::image_providers_catalog();
        if (!isset($cat[$provider])) {
            wp_send_json_error(['message' => 'ارائه‌دهنده نامعتبر']);
        }
        $keys = $this->get_image_keys();
        if ($key !== '') {
            $keys[$provider] = $key;
        }
        update_option(self::OPT_IMAGE_KEYS, $keys, false);
        if ($model !== '') {
            $models = $this->get_image_models();
            $models[$provider] = $model;
            update_option(self::OPT_IMAGE_MODELS, $models, false);
        }
        // Active image providers list
        $active = get_option('bankai_ai_active_image_providers', []);
        if (!is_array($active)) $active = [];
        if (!empty($keys[$provider])) {
            $active[$provider] = true;
        } else {
            unset($active[$provider]);
        }
        update_option('bankai_ai_active_image_providers', $active, false);

        wp_send_json_success([
            'message' => 'ذخیره شد',
            'masked'  => self::mask_key($keys[$provider] ?? ''),
            'active'  => array_keys(array_filter($active)),
        ]);
    }

    public function ajax_clear_image_ai_key(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $keys = $this->get_image_keys();
        unset($keys[$provider]);
        update_option(self::OPT_IMAGE_KEYS, $keys, false);
        $active = get_option('bankai_ai_active_image_providers', []);
        if (is_array($active)) {
            unset($active[$provider]);
            update_option('bankai_ai_active_image_providers', $active, false);
        }
        wp_send_json_success(['message' => 'کلید حذف شد']);
    }

    public function ajax_set_default_image_provider(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        if (!isset(self::image_providers_catalog()[$provider])) {
            wp_send_json_error(['message' => 'نامعتبر']);
        }
        update_option(self::OPT_IMAGE_DEFAULT, $provider, false);
        wp_send_json_success(['message' => 'پیش‌فرض ذخیره شد']);
    }

    public function ajax_test_image_ai(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $keys = $this->get_image_keys();
        if (empty($keys[$provider])) {
            wp_send_json_error(['message' => 'کلید API تنظیم نشده']);
        }
        // Lightweight: only verify key presence + optional ping for openai/gemini
        try {
            $opts = [
                'width' => 512,
                'height' => 512,
                'test_mode' => true,
            ];
            // Cloudflare: always test with Flux Schnell JSON (avoids multipart schema models)
            if ($provider === 'cloudflare_image') {
                $opts['model'] = '@cf/black-forest-labs/flux-1-schnell';
            }
            $result = $this->generate_image_bytes($provider, 'minimal test pattern abstract soft gradient', $opts);
            if (empty($result['bytes']) && empty($result['url'])) {
                wp_send_json_error(['message' => $result['error'] ?? 'پاسخ خالی']);
            }
            wp_send_json_success(['message' => 'اتصال و تولید آزمایشی موفق بود', 'provider' => $provider]);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_image_generate(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $prompt   = sanitize_textarea_field(wp_unslash($_POST['prompt'] ?? ''));
        $purpose  = sanitize_key(wp_unslash($_POST['purpose'] ?? 'cover')); // cover | inline
        $post_id  = absint($_POST['post_id'] ?? 0);
        $model    = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));

        if ($prompt === '') {
            wp_send_json_error(['message' => 'پرامپت خالی است']);
        }
        // Phase D: image generation from editor uses central image provider only
        $provider = $this->resolve_central_image_provider($provider);
        $img_err = $this->image_provider_readiness_error($provider);
        if ($img_err !== '') {
            wp_send_json_error(['message' => $img_err, 'generation_status' => 'error']);
        }
        $img_keys = $this->get_image_keys();

        // Persian → English translation for image models (most image AIs work best in English)
        $style = sanitize_text_field(wp_unslash($_POST['style'] ?? 'photorealistic'));
        $title_ctx = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $section_ctx = sanitize_textarea_field(wp_unslash($_POST['section_context'] ?? ''));
        $focus_ctx = sanitize_text_field(wp_unslash($_POST['focus_keyword'] ?? ''));
        $final_prompt = $this->prepare_english_image_prompt($prompt, $purpose, $style, $title_ctx, $section_ctx, $focus_ctx);

        $size = $purpose === 'cover' ? ['width' => 1280, 'height' => 720] : ['width' => 1024, 'height' => 1024];
        try {
            $gen = $this->generate_image_bytes($provider, $final_prompt, array_merge($size, ['model' => $model]));
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }

        $bytes = $gen['bytes'] ?? '';
        $src_url = $gen['url'] ?? '';
        if ($bytes === '' && $src_url !== '') {
            $resp = wp_remote_get($src_url, ['timeout' => 60]);
            if (!is_wp_error($resp)) {
                $bytes = (string) wp_remote_retrieve_body($resp);
            }
        }
        if ($bytes === '') {
            wp_send_json_error(['message' => $gen['error'] ?? 'داده تصویر دریافت نشد']);
        }

        $filename = 'bankai-' . $purpose . '-' . time() . '.png';
        $upload = wp_upload_bits($filename, null, $bytes);
        if (!empty($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        $filetype = wp_check_filetype($filename, null);
        $attachment = [
            'post_mime_type' => $filetype['type'] ?: 'image/png',
            'post_title'     => sanitize_text_field(mb_substr($prompt, 0, 80)),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id > 0 ? $post_id : 0);
        if (is_wp_error($attach_id)) {
            wp_send_json_error(['message' => $attach_id->get_error_message()]);
        }
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $meta = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $meta);
        update_post_meta($attach_id, '_wp_attachment_image_alt', sanitize_text_field(mb_substr($prompt, 0, 120)));
        update_post_meta($attach_id, '_bankai_ai_image', 1);
        update_post_meta($attach_id, '_bankai_ai_image_provider', $provider);
        update_post_meta($attach_id, '_bankai_ai_image_prompt', $prompt);

        $url = wp_get_attachment_url($attach_id);

        if ($purpose === 'cover' && $post_id > 0) {
            set_post_thumbnail($post_id, $attach_id);
        }

        $alt_src = $section_ctx !== '' ? $section_ctx : ($title_ctx !== '' ? $title_ctx : $prompt);
        if ($focus_ctx !== '' && mb_stripos($alt_src, $focus_ctx) === false) {
            $alt_src = $focus_ctx . ' — ' . $alt_src;
        }
        $alt = sanitize_text_field(mb_substr(preg_replace('/\s+/', ' ', $alt_src), 0, 120));

        $html = '';
        if ($purpose === 'inline') {
            $html = sprintf(
                '<figure class="wp-block-image bankai-ai-inline"><img src="%s" alt="%s" loading="lazy" /></figure>',
                esc_url($url),
                esc_attr($alt)
            );
        }

        wp_send_json_success([
            'attachment_id' => $attach_id,
            'url'           => $url,
            'html'          => $html,
            'purpose'       => $purpose,
            'provider'      => $provider,
            'message'       => $purpose === 'cover' ? 'کاور ساخته و در رسانه ذخیره شد' : 'تصویر محتوا ساخته شد',
        ]);
    }

    /**
     * Prepare English prompt for image models.
     * If source text is Persian, translate via text AI first.
     */
    public function prepare_english_image_prompt(
        string $raw_prompt,
        string $purpose = 'cover',
        string $style = 'photorealistic',
        string $title = '',
        string $section = '',
        string $focus = ''
    ): string {
        $style_map = [
            'photorealistic' => 'photorealistic, high detail, cinematic lighting',
            'minimalist'     => 'minimalist modern corporate style, clean composition',
            'vector'         => 'vector illustration, flat design, clean lines',
            'render_3d'      => '3D digital render, studio lighting, high quality',
            'cinematic'      => 'cinematic studio lighting, dramatic atmosphere, film still',
            'infographic'    => 'infographic style, educational illustration, clear visual hierarchy',
        ];
        $style_en = $style_map[$style] ?? $style_map['photorealistic'];

        $has_persian = (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $raw_prompt . $title . $section . $focus);

        $content_en = $raw_prompt;
        if ($has_persian) {
            try {
                $sys = 'You are a professional translator. Translate the following Persian text into clear natural English suitable for an AI image generation prompt. Output ONLY the English translation, nothing else. No explanations.';
                $user = "Title: {$title}\nFocus keyword: {$focus}\nSection / prompt context:\n" . mb_substr(trim($raw_prompt . "\n" . $section), 0, 1200);
                $translated = $this->chat($this->resolve_central_text_provider(), $sys, $user, [
                    'temperature' => 0.2,
                    'max_tokens'  => 400,
                    'no_fallback' => false,
                ]);
                $translated = trim(preg_replace('/^["\']|["\']$/', '', (string) $translated));
                if ($translated !== '') {
                    $content_en = $translated;
                }
            } catch (Throwable $e) {
                // keep original if translation fails
                $content_en = $raw_prompt;
            }
        }

        $purpose_line = $purpose === 'cover'
            ? 'Create a professional editorial blog cover image (16:9) that represents this article content.'
            : 'Create a high-quality illustrative photo suitable for an article body section.';

        $parts = [
            $purpose_line,
            'Visual style: ' . $style_en . '.',
            'Content description: ' . $content_en,
        ];
        if ($title !== '') {
            $parts[] = 'Article title (for context only): ' . $title;
        }
        if ($focus !== '') {
            $parts[] = 'Main topic keyword: ' . $focus;
        }
        $parts[] = 'Strict rules: no text, no letters, no watermark, no logo, no UI elements, no captions on the image.';

        return implode("\n", $parts);
    }

    /**
     * Provider-specific image generation → ['bytes'=>, 'url'=>, 'error'=>].
     */
    public function generate_image_bytes(string $provider, string $prompt, array $opts = []): array
    {
        $keys   = $this->get_image_keys();
        $models = $this->get_image_models();
        $cat    = self::image_providers_catalog();
        $w      = (int) ($opts['width'] ?? 1024);
        $h      = (int) ($opts['height'] ?? 1024);

        $tried    = [];
        $last_err = null;

        if ($provider !== '' && !empty($keys[$provider])) {
            $key   = (string) $keys[$provider];
            $model = $opts['model'] ?? ($models[$provider] ?? ($cat[$provider]['default'] ?? ''));
            $tried[$provider] = true;
            try {
                return $this->dispatch_image_gen($provider, $key, $model, $prompt, $w, $h);
            } catch (Throwable $e) {
                $last_err = $e;
            }
        }

        // Cross-provider fallback for active image providers with keys
        foreach ($keys as $pid => $k) {
            $k = is_string($k) ? trim($k) : '';
            if ($k === '' || !empty($tried[$pid])) {
                continue;
            }
            $tried[$pid] = true;
            $model = $models[$pid] ?? ($cat[$pid]['default'] ?? '');
            try {
                return $this->dispatch_image_gen((string) $pid, $k, $model, $prompt, $w, $h);
            } catch (Throwable $e) {
                $last_err = $e;
            }
        }

        throw new RuntimeException('هیچ هوش مصنوعی فعالی ندارید لطفا میزان مصرف خود در هوش مصنوعی مورد نظر را برسی کنید');
    }

    private function dispatch_image_gen(string $provider, string $key, string $model, string $prompt, int $w, int $h): array
    {
        switch ($provider) {
            case 'cloudflare_image':
                return $this->image_gen_cloudflare($key, $model, $prompt, $w, $h);
            case 'openai_image':
                return $this->image_gen_openai($key, $model, $prompt, $w, $h);
            case 'gemini_image':
                return $this->image_gen_gemini($key, $model, $prompt, $w, $h);
            case 'huggingface_image':
                return $this->image_gen_huggingface($key, $model, $prompt);
            case 'stability':
                return $this->image_gen_stability($key, $model, $prompt, $w, $h);
            case 'replicate':
                return $this->image_gen_replicate($key, $model, $prompt);
            case 'together_image':
                return $this->image_gen_together($key, $model, $prompt, $w, $h);
            case 'fal_image':
                return $this->image_gen_fal($key, $model, $prompt);
            default:
                throw new RuntimeException('ارائه‌دهنده پشتیبانی نمی‌شود: ' . $provider);
        }
    }


    private function image_gen_cloudflare(string $key, string $model, string $prompt, int $w, int $h): array
    {
        // Key format: AccountID|APIToken (same as text CF)
        $account = '';
        $token = $key;
        if (str_contains($key, '|')) {
            [$account, $token] = array_map('trim', explode('|', $key, 2));
        }
        if ($account === '') {
            $account = (string) get_option('bankai_cloudflare_account_id', '');
        }
        if ($account === '' || $token === '') {
            throw new RuntimeException('کلید Cloudflare باید به صورت AccountID|APIToken باشد');
        }

        $model = $model ?: '@cf/black-forest-labs/flux-1-schnell';

        // Inpainting needs image+mask (multipart) — not for text-to-image test/cover
        if (str_contains($model, 'inpainting')) {
            // Fall back to a pure text-to-image model for generation without mask
            $model = '@cf/black-forest-labs/flux-1-schnell';
        }

        $w = max(256, min(1024, $w ?: 1024));
        $h = max(256, min(1024, $h ?: 1024));
        // Many CF models require multiples of 8
        $w = (int) (round($w / 8) * 8);
        $h = (int) (round($h / 8) * 8);

        $url = 'https://api.cloudflare.com/client/v4/accounts/' . rawurlencode($account) . '/ai/run/' . $model;

        // Payload shapes differ by family (JSON — not multipart for text-to-image)
        $body = ['prompt' => $prompt];
        if (str_contains($model, 'flux')) {
            // Flux / Flux2: prompt + steps (width/height optional on some variants)
            $body['steps'] = str_contains($model, 'schnell') ? 4 : 20;
            if (!str_contains($model, 'schnell')) {
                $body['width'] = $w;
                $body['height'] = $h;
            }
        } elseif (str_contains($model, 'leonardo') || str_contains($model, 'lucid') || str_contains($model, 'phoenix')) {
            $body['width'] = $w;
            $body['height'] = $h;
            $body['num_steps'] = 20;
        } else {
            // SDXL / Dreamshaper / Lightning
            $body['width'] = $w;
            $body['height'] = $h;
            $body['num_steps'] = 20;
            $body['guidance'] = 7.5;
        }

        $res = wp_remote_post($url, [
            'timeout' => 150,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => '*/*',
            ],
            'body' => wp_json_encode($body),
        ]);

        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        $raw = (string) wp_remote_retrieve_body($res);
        $ctype = strtolower((string) wp_remote_retrieve_header($res, 'content-type'));

        // Binary image response (common for CF Workers AI)
        $looks_json = str_starts_with(ltrim($raw), '{') || str_starts_with(ltrim($raw), '[');
        if ($code < 400 && (str_contains($ctype, 'image/') || (!$looks_json && strlen($raw) > 200))) {
            return ['bytes' => $raw, 'url' => ''];
        }

        $data = json_decode($raw, true);
        if ($code >= 400) {
            $msg = '';
            if (is_array($data)) {
                $msg = $data['errors'][0]['message']
                    ?? $data['error']['message']
                    ?? $data['message']
                    ?? '';
                // Surface nested AI schema errors (e.g. multipart)
                if ($msg === '' && !empty($data['errors'][0])) {
                    $msg = is_string($data['errors'][0]) ? $data['errors'][0] : wp_json_encode($data['errors'][0]);
                }
            }
            if ($msg === '') {
                $msg = 'HTTP ' . $code . ' — ' . mb_substr($raw, 0, 240);
            }
            // Auto-retry once with minimal Flux payload if schema mismatch
            if (str_contains((string) $msg, 'multipart') || str_contains((string) $msg, 'Bad input')) {
                return $this->image_gen_cloudflare_minimal($account, $token, $prompt, $w, $h);
            }
            throw new RuntimeException((string) $msg);
        }

        // JSON with base64 image
        $b64 = '';
        if (is_array($data)) {
            $b64 = $data['result']['image']
                ?? $data['result'][0]
                ?? $data['image']
                ?? '';
            if (is_array($b64)) {
                $b64 = $b64['b64_json'] ?? $b64['image'] ?? '';
            }
        }
        if (is_string($b64) && $b64 !== '') {
            if (str_starts_with($b64, 'data:')) {
                $b64 = (string) preg_replace('#^data:image/[^;]+;base64,#', '', $b64);
            }
            $bin = base64_decode($b64, true);
            if ($bin !== false && $bin !== '') {
                return ['bytes' => $bin, 'url' => ''];
            }
        }

        throw new RuntimeException('پاسخ تصویر Cloudflare نامعتبر است');
    }

    /**
     * Fallback: simplest Flux Schnell JSON body (avoids multipart schema errors).
     */
    private function image_gen_cloudflare_minimal(string $account, string $token, string $prompt, int $w, int $h): array
    {
        $model = '@cf/black-forest-labs/flux-1-schnell';
        $url = 'https://api.cloudflare.com/client/v4/accounts/' . rawurlencode($account) . '/ai/run/' . $model;
        $res = wp_remote_post($url, [
            'timeout' => 120,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => '*/*',
            ],
            'body' => wp_json_encode([
                'prompt' => $prompt,
                'steps'  => 4,
            ]),
        ]);
        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }
        $code = (int) wp_remote_retrieve_response_code($res);
        $raw = (string) wp_remote_retrieve_body($res);
        $ctype = strtolower((string) wp_remote_retrieve_header($res, 'content-type'));
        $looks_json = str_starts_with(ltrim($raw), '{');
        if ($code < 400 && (str_contains($ctype, 'image/') || (!$looks_json && strlen($raw) > 200))) {
            return ['bytes' => $raw, 'url' => ''];
        }
        $data = json_decode($raw, true);
        if ($code >= 400) {
            $msg = is_array($data) ? ($data['errors'][0]['message'] ?? $data['message'] ?? ('HTTP ' . $code)) : ('HTTP ' . $code);
            throw new RuntimeException('Cloudflare Image: ' . (string) $msg);
        }
        $b64 = is_array($data) ? ($data['result']['image'] ?? $data['result'][0] ?? '') : '';
        if (is_string($b64) && $b64 !== '') {
            $bin = base64_decode($b64, true);
            if ($bin !== false) {
                return ['bytes' => $bin, 'url' => ''];
            }
        }
        throw new RuntimeException('Cloudflare Image: پاسخ نامعتبر در حالت ساده');
    }

    private function image_gen_openai(string $key, string $model, string $prompt, int $w, int $h): array
    {
        $size = '1024x1024';
        if ($w >= 1280 || $h === 720) {
            $size = '1792x1024'; // landscape-ish for covers on dall-e-3
        }
        $body = [
            'model' => $model ?: 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
            'response_format' => 'b64_json',
        ];
        $res = wp_remote_post('https://api.openai.com/v1/images/generations', [
            'timeout' => 150,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode($body),
        ]);
        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }
        $code = wp_remote_retrieve_response_code($res);
        $data = json_decode((string) wp_remote_retrieve_body($res), true);
        if ($code >= 400) {
            throw new RuntimeException($data['error']['message'] ?? ('OpenAI HTTP ' . $code));
        }
        $b64 = $data['data'][0]['b64_json'] ?? '';
        $url = $data['data'][0]['url'] ?? '';
        return ['bytes' => $b64 !== '' ? base64_decode($b64) : '', 'url' => $url];
    }

    private function image_gen_gemini(string $key, string $model, string $prompt, int $w, int $h): array
    {
        $model = $model ?: 'imagen-3.0-generate-002';
        // Imagen predict endpoint
        if (strpos($model, 'imagen') !== false) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':predict?key=' . rawurlencode($key);
            $body = [
                'instances' => [['prompt' => $prompt]],
                'parameters' => [
                    'sampleCount' => 1,
                    'aspectRatio' => ($w > $h) ? '16:9' : '1:1',
                ],
            ];
            $res = wp_remote_post($url, [
                'timeout' => 150,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => wp_json_encode($body),
            ]);
            if (is_wp_error($res)) {
                throw new RuntimeException($res->get_error_message());
            }
            $code = wp_remote_retrieve_response_code($res);
            $data = json_decode((string) wp_remote_retrieve_body($res), true);
            if ($code >= 400) {
                // Fallback to gemini flash image generation if imagen blocked
                return $this->image_gen_gemini_flash($key, $prompt);
            }
            $b64 = $data['predictions'][0]['bytesBase64Encoded']
                ?? $data['predictions'][0]['image']['bytesBase64Encoded']
                ?? '';
            if ($b64 === '') {
                return $this->image_gen_gemini_flash($key, $prompt);
            }
            return ['bytes' => base64_decode($b64), 'url' => ''];
        }
        return $this->image_gen_gemini_flash($key, $prompt);
    }

    private function image_gen_gemini_flash(string $key, string $prompt): array
    {
        $model = 'gemini-2.0-flash-preview-image-generation';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . rawurlencode($key);
        $body = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'responseModalities' => ['TEXT', 'IMAGE'],
            ],
        ];
        $res = wp_remote_post($url, [
            'timeout' => 150,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode($body),
        ]);
        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }
        $code = wp_remote_retrieve_response_code($res);
        $raw = (string) wp_remote_retrieve_body($res);
        $data = json_decode($raw, true);
        if ($code >= 400) {
            throw new RuntimeException($data['error']['message'] ?? ('Gemini HTTP ' . $code));
        }
        $parts = $data['candidates'][0]['content']['parts'] ?? [];
        foreach ($parts as $part) {
            if (!empty($part['inlineData']['data'])) {
                return ['bytes' => base64_decode($part['inlineData']['data']), 'url' => ''];
            }
            if (!empty($part['inline_data']['data'])) {
                return ['bytes' => base64_decode($part['inline_data']['data']), 'url' => ''];
            }
        }
        throw new RuntimeException('Gemini تصویری در پاسخ برنگرداند');
    }

    private function image_gen_huggingface(string $key, string $model, string $prompt): array
    {
        $model = $model ?: 'black-forest-labs/FLUX.1-schnell';
        $url = 'https://api-inference.huggingface.co/models/' . $model;
        $res = wp_remote_post($url, [
            'timeout' => 150,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
                'Accept'        => 'image/png',
            ],
            'body' => wp_json_encode(['inputs' => $prompt]),
        ]);
        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }
        $code = wp_remote_retrieve_response_code($res);
        $body = (string) wp_remote_retrieve_body($res);
        $ctype = (string) wp_remote_retrieve_header($res, 'content-type');
        if ($code >= 400) {
            $j = json_decode($body, true);
            throw new RuntimeException($j['error'] ?? ('HuggingFace HTTP ' . $code));
        }
        if (strpos($ctype, 'application/json') !== false) {
            $j = json_decode($body, true);
            throw new RuntimeException($j['error'] ?? 'پاسخ JSON به‌جای تصویر');
        }
        return ['bytes' => $body, 'url' => ''];
    }

    private function image_gen_stability(string $key, string $model, string $prompt, int $w, int $h): array
    {
        $endpoint = 'https://api.stability.ai/v2beta/stable-image/generate/core';
        $res = wp_remote_post($endpoint, [
            'timeout' => 150,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Accept'        => 'image/*',
            ],
            'body' => [
                'prompt' => $prompt,
                'output_format' => 'png',
                'aspect_ratio' => ($w > $h) ? '16:9' : '1:1',
            ],
        ]);
        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }
        $code = wp_remote_retrieve_response_code($res);
        $body = (string) wp_remote_retrieve_body($res);
        if ($code >= 400) {
            $j = json_decode($body, true);
            throw new RuntimeException($j['message'] ?? $j['errors'][0] ?? ('Stability HTTP ' . $code));
        }
        return ['bytes' => $body, 'url' => ''];
    }

    private function image_gen_replicate(string $key, string $model, string $prompt): array
    {
        $model = $model ?: 'black-forest-labs/flux-schnell';
        // Prefer official models API
        $res = wp_remote_post('https://api.replicate.com/v1/models/' . $model . '/predictions', [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Token ' . $key,
                'Content-Type'  => 'application/json',
                'Prefer'        => 'wait=60',
            ],
            'body' => wp_json_encode(['input' => ['prompt' => $prompt]]),
        ]);
        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }
        $data = json_decode((string) wp_remote_retrieve_body($res), true);
        $out = $data['output'] ?? null;
        $url = is_array($out) ? (string) ($out[0] ?? '') : (string) $out;
        if ($url === '' && !empty($data['urls']['get'])) {
            // poll once
            sleep(2);
            $poll = wp_remote_get($data['urls']['get'], [
                'timeout' => 60,
                'headers' => ['Authorization' => 'Token ' . $key],
            ]);
            if (!is_wp_error($poll)) {
                $pdata = json_decode((string) wp_remote_retrieve_body($poll), true);
                $out = $pdata['output'] ?? null;
                $url = is_array($out) ? (string) ($out[0] ?? '') : (string) $out;
            }
        }
        if ($url === '') {
            throw new RuntimeException($data['error'] ?? 'Replicate خروجی نداد');
        }
        return ['bytes' => '', 'url' => $url];
    }

    private function image_gen_together(string $key, string $model, string $prompt, int $w, int $h): array
    {
        $res = wp_remote_post('https://api.together.xyz/v1/images/generations', [
            'timeout' => 150,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode([
                'model' => $model ?: 'black-forest-labs/FLUX.1-schnell',
                'prompt' => $prompt,
                'width' => $w,
                'height' => $h,
                'response_format' => 'b64_json',
            ]),
        ]);
        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }
        $data = json_decode((string) wp_remote_retrieve_body($res), true);
        $code = wp_remote_retrieve_response_code($res);
        if ($code >= 400) {
            throw new RuntimeException($data['error']['message'] ?? ('Together HTTP ' . $code));
        }
        $b64 = $data['data'][0]['b64_json'] ?? '';
        $url = $data['data'][0]['url'] ?? '';
        return ['bytes' => $b64 !== '' ? base64_decode($b64) : '', 'url' => $url];
    }

    private function image_gen_fal(string $key, string $model, string $prompt): array
    {
        $model = $model ?: 'fal-ai/flux/schnell';
        $url = 'https://fal.run/' . ltrim($model, '/');
        $res = wp_remote_post($url, [
            'timeout' => 150,
            'headers' => [
                'Authorization' => 'Key ' . $key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode(['prompt' => $prompt, 'image_size' => 'landscape_16_9']),
        ]);
        if (is_wp_error($res)) {
            throw new RuntimeException($res->get_error_message());
        }
        $data = json_decode((string) wp_remote_retrieve_body($res), true);
        $code = wp_remote_retrieve_response_code($res);
        if ($code >= 400) {
            throw new RuntimeException($data['detail'] ?? $data['message'] ?? ('fal HTTP ' . $code));
        }
        $img_url = $data['images'][0]['url'] ?? $data['image']['url'] ?? '';
        if ($img_url === '') {
            throw new RuntimeException('fal خروجی تصویر نداد');
        }
        return ['bytes' => '', 'url' => $img_url];
    }


    public function ajax_add_image_model(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $model = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));
        if ($provider === '' || $model === '' || !isset(self::image_providers_catalog()[$provider])) {
            wp_send_json_error(['message' => 'داده نامعتبر']);
        }
        $extra = get_option('bankai_ai_image_extra_models', []);
        if (!is_array($extra)) {
            $extra = [];
        }
        if (!isset($extra[$provider]) || !is_array($extra[$provider])) {
            $extra[$provider] = [];
        }
        if (!in_array($model, $extra[$provider], true)) {
            $extra[$provider][] = $model;
        }
        update_option('bankai_ai_image_extra_models', $extra, false);
        $models = $this->get_image_models();
        $models[$provider] = $model;
        update_option(self::OPT_IMAGE_MODELS, $models, false);
        wp_send_json_success(['message' => 'مدل اضافه شد', 'model' => $model]);
    }

    public function ajax_remove_image_model(): void
    {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        $provider = sanitize_key(wp_unslash($_POST['provider'] ?? ''));
        $model = sanitize_text_field(wp_unslash($_POST['model'] ?? ''));
        $extra = get_option('bankai_ai_image_extra_models', []);
        if (is_array($extra) && isset($extra[$provider]) && is_array($extra[$provider])) {
            $extra[$provider] = array_values(array_filter($extra[$provider], static fn($m) => $m !== $model));
            update_option('bankai_ai_image_extra_models', $extra, false);
        }
        wp_send_json_success(['message' => 'مدل حذف شد']);
    }

}
