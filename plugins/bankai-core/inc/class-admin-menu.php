<?php
/**
 * Bankai Core - Admin Menu & Controller
 *
 * @package Bankai
 * @subpackage Admin
 */

defined('ABSPATH') || exit;

class Bankai_Admin_Menu {

    private static ?Bankai_Admin_Menu $instance = null;
    private string $page_hook = '';

    public static function instance(): Bankai_Admin_Menu {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('script_loader_tag', [$this, 'add_defer_attribute'], 10, 2);
    }

    public function register_admin_menu(): void {
        $this->page_hook = add_menu_page(
            __('Bankai Platform', 'bankai-core'),
            __('Bankai Core', 'bankai-core'),
            'manage_options',
            'bankai-core',
            [$this, 'render_admin_layout'],
            bankai_asset_url('images/logo.jpg'),
            2
        );

        // Submenus for direct menu navigation
        add_submenu_page(
            'bankai-core',
            __('Overview & Telemetry', 'bankai-core'),
            __('پیشخوان و پایش سیستم', 'bankai-core'),
            'manage_options',
            'bankai-core',
            [$this, 'render_admin_layout']
        );

        add_submenu_page(
            'bankai-core',
            __('Theme Kits & Customizer', 'bankai-core'),
            __('کیت‌های قالب و شخصی‌ساز', 'bankai-core'),
            'manage_options',
            'bankai-theme-kits',
            [$this, 'render_admin_layout']
        );

        add_submenu_page(
            'bankai-core',
            __('SEO & Schema Engine', 'bankai-core'),
            __('موتور هوشمند سئو', 'bankai-core'),
            'manage_options',
            'bankai-seo-engine',
            [$this, 'render_admin_layout']
        );

        add_submenu_page(
            'bankai-core',
            __('Speed & Cache Hub', 'bankai-core'),
            __('شتاب‌دهنده سرعت و کش', 'bankai-core'),
            'manage_options',
            'bankai-speed-cache',
            [$this, 'render_admin_layout']
        );

        add_submenu_page(
            'bankai-core',
            __('Media & Watermark Studio', 'bankai-core'),
            __('استودیو رسانه و واترمارک', 'bankai-core'),
            'manage_options',
            'bankai-media',
            [$this, 'render_admin_layout']
        );

        add_submenu_page(
            'bankai-core',
            __('AI Studio & Manifests', 'bankai-core'),
            __('استودیو هوش مصنوعی', 'bankai-core'),
            'manage_options',
            'bankai-ai-manifests',
            [$this, 'render_admin_layout']
        );

        add_submenu_page(
            'bankai-core',
            __('Settings & License', 'bankai-core'),
            __('تنظیمات و لایسنس', 'bankai-core'),
            'manage_options',
            'bankai-settings',
            [$this, 'render_admin_layout']
        );
    }

    public function enqueue_admin_assets(string $hook_suffix): void {
        $screen = get_current_screen();
        $is_bankai_screen = false;

        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if ($page && strpos($page, 'bankai') !== false) {
            $is_bankai_screen = true;
        } elseif (strpos($hook_suffix, 'bankai') !== false) {
            $is_bankai_screen = true;
        } elseif ($screen && (strpos($screen->id, 'bankai') !== false || strpos($screen->base, 'bankai') !== false)) {
            $is_bankai_screen = true;
        }

        if (!$is_bankai_screen) {
            return;
        }

        wp_enqueue_style(
            'bankai-vazirmatn-font',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Vazirmatn:wght@400;500;600;700;800&display=swap',
            [],
            null
        );

        wp_enqueue_style(
            'bankai-admin-css',
            bankai_asset_url('css/bankai-admin.css'),
            [],
            BANKAI_CORE_VERSION
        );

        wp_enqueue_script(
            'bankai-htmx-js',
            bankai_asset_url('js/htmx.min.js'),
            [],
            '1.9.10',
            true
        );

        // Load Bankai Admin JS FIRST so bankaiAdmin and alpine:init listeners exist before Alpine boots
        wp_enqueue_script(
            'bankai-admin-js',
            bankai_asset_url('js/bankai-admin.js'),
            ['jquery', 'bankai-htmx-js'],
            BANKAI_CORE_VERSION,
            true
        );

        // Load Alpine JS AFTER bankai-admin-js
        wp_enqueue_script(
            'bankai-alpine-js',
            bankai_asset_url('js/alpine.min.js'),
            ['bankai-admin-js'],
            '3.13.5',
            true
        );

        $core_data = [
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'restUrl'   => esc_url_raw(rest_url('bankai/v1/')),
            'nonce'     => wp_create_nonce('bankai_admin_nonce'),
            'isRtl'     => is_rtl(),
            'version'   => BANKAI_CORE_VERSION,
            'activeTab' => $this->get_current_tab(),
        ];

        wp_localize_script('bankai-admin-js', 'bankaiCoreData', $core_data);
    }

    public function add_defer_attribute(string $tag, string $handle): string {
        if (in_array($handle, ['bankai-alpine-js', 'bankai-htmx-js'], true)) {
            return str_replace(' src', ' defer="defer" src', $tag);
        }
        return $tag;
    }

    private function get_current_tab(): string {
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'bankai-core';
        
        $map = [
            'bankai-core'         => 'overview',
            'bankai-theme-kits'   => 'theme-kits',
            'bankai-seo-engine'   => 'seo-engine',
            'bankai-speed-cache'  => 'speed-cache',
            'bankai-media'        => 'media-watermark',
            'bankai-ai-manifests' => 'ai-studio',
            'bankai-settings'     => 'settings-license',
        ];

        return $map[$page] ?? 'overview';
    }

    public function render_admin_layout(): void {
        $state = [
            'activeTab' => $this->get_current_tab(),
            'isRtl'     => is_rtl(),
            'modules'   => $this->get_initial_state_data(),
        ];

        bankai_render_view('admin/layout.php', ['state' => $state]);
    }

    private function get_initial_state_data(): array {
        $system_report = sprintf(
            "=== Bankai Core Diagnostic Telemetry ===\nPHP: %s | WP: %s | Server: %s | Memory Limit: %s\nActive Modules: 7/7 Enabled | Autonomous Engine Status: OPTIMAL",
            phpversion(),
            get_bloginfo('version'),
            $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            WP_MEMORY_LIMIT
        );

        return [
            'seoModules' => [
                ['id' => 'auto_meta', 'title' => 'Autonomous Meta & Schema Generator', 'title_fa' => 'تولیدکننده خودکار متاداده و اسکیما', 'badge' => 'AI AUTO', 'badge_color' => '#10B981', 'icon' => '⚡', 'description' => 'Real-time JSON-LD structured data injection for Article, Product, FAQ, and BreadcrumbList.', 'description_fa' => 'تزریق خودکار کدهای استانداردهای نشانه‌گذاری گوگل (JSON-LD) بدون نیاز به تنظیمات پیچیده.', 'enabled' => true],
                ['id' => 'sitemap_pro', 'title' => 'High-Velocity XML & News Sitemap', 'title_fa' => 'نقشه سایت پیشرفته و قدرتمند XML', 'badge' => 'SPEED SITEMAP', 'badge_color' => '#38BDF8', 'icon' => '🗺️', 'description' => 'Generates instant XML sitemaps with ping protocols sent straight to Google Search Console.', 'description_fa' => 'ایجاد سریع نقشه سایت استاندارد و اطلاع‌رسانی لحظه‌ای به موتورهای جستجو هنگام انتشار مطلب.', 'enabled' => true],
                ['id' => 'canonical_guard', 'title' => 'Canonical & Redirects Matrix', 'title_fa' => 'مدیریت کانوینکال و هدایت ۴۰۴', 'badge' => 'SEO GUARD', 'badge_color' => '#F59E0B', 'icon' => '🔗', 'description' => 'Automated 301/302 redirect rules engine and canonical URL correction to prevent duplicate content.', 'description_fa' => 'جلوگیری از خطاهای محتوای تکراری و هدایت هوشمند آدرس‌های قدیمی به لینک جدید.', 'enabled' => true],
                ['id' => 'open_graph_ai', 'title' => 'Social Cards & OpenGraph AI', 'title_fa' => 'کارت‌های شبکه‌های اجتماعی (OG/Twitter)', 'badge' => 'VIRAL', 'badge_color' => '#EC4899', 'icon' => '📱', 'description' => 'Creates rich previews for WhatsApp, Twitter/X, and Telegram with automated image generation.', 'description_fa' => 'تنظیم خودکار تصویر، عنوان و توضیحات هنگام اشتراک‌گذاری لینک در شبکه‌های اجتماعی.', 'enabled' => true],
                ['id' => 'local_seo_schema', 'title' => 'Local SEO Knowledge Graph', 'title_fa' => 'سئوی محلی و گراف دانش گوگل', 'badge' => 'KNOWLEDGE GRAPH', 'badge_color' => '#6366F1', 'icon' => '📍', 'description' => 'Geo-coordinates, opening hours JSON-LD, business organization graphs, and Google Maps embed.', 'description_fa' => 'مختصات جغرافیایی، ساعات کاری، اسکیماهای کسب‌وکار محلی و اتصال نقشه برای رتبه اول لوکال سئو.', 'enabled' => true],
                ['id' => 'llms_txt_builder', 'title' => 'llms.txt Manifest Builder', 'title_fa' => 'تولیدکننده مانیفست llms.txt برای هوش مصنوعی', 'badge' => 'AI SPEC v1.2', 'badge_color' => '#10B981', 'icon' => '📄', 'description' => 'Generates standardized /llms.txt and /llms-full.txt files for AI agents and web crawlers.', 'description_fa' => 'تولید فایل‌های استاندارد llms.txt برای فهم ساختار سایت توسط موتورهای هوش مصنوعی و ربات‌ها.', 'enabled' => true],
            ],
            'speedModules' => [
                ['id' => 'page_caching', 'title' => 'Page Cache & Varnish Purge', 'title_fa' => 'کش پیشرفته صفحات و تخلیه خودکار وارنیش', 'badge' => 'HIGH SPEED', 'badge_color' => '#10B981', 'icon' => '⚡', 'description' => 'Sub-50ms static HTML generation with automated edge cache purging upon post revision.', 'description_fa' => 'تولید فایل‌های استاتیک HTML فوق‌سریع و پاکسازی خودکار کش لبه سرور هنگام ویرایش نوشته‌ها.', 'enabled' => true],
                ['id' => 'asset_optimization', 'title' => 'CSS & JS Minification / Defer', 'title_fa' => 'فشرده‌سازی و بارگذاری تاخیری CSS و JS', 'badge' => 'CRITICAL CSS', 'badge_color' => '#38BDF8', 'icon' => '📦', 'description' => 'Eliminates render-blocking resources by generating critical inline CSS and delaying non-essential scripts.', 'description_fa' => 'حذف منابع مسدودکننده رندر با استخراج خودکار CSS بحرانی و به تعویق انداختن اسکریپت‌های سنگین.', 'enabled' => true],
                ['id' => 'database_optimizer', 'title' => 'Database Heuristic Sweeper', 'title_fa' => 'پاکسازی هوشمند پایگاه‌داده وردپرس', 'badge' => 'MAINTENANCE', 'badge_color' => '#F59E0B', 'icon' => '🧹', 'description' => 'Scheduled cleanup of post revisions, orphaned postmeta, spam comments, and transient transients.', 'description_fa' => 'حذف رونوشت‌های قدیمی، متادیتای یتیم، نظرات اسپم و بهینه‌سازی جداول MySQL طبق زمان‌بندی.', 'enabled' => true],
                ['id' => 'object_cache', 'title' => 'Redis Object Cache', 'title_fa' => 'کش آبجکت و دیتابیس ردیس (Redis)', 'badge' => 'IN-MEMORY', 'badge_color' => '#EF4444', 'icon' => '🧠', 'description' => 'Persistent Redis/Memcached daemon integration to cache complex MySQL queries and theme options.', 'description_fa' => 'ذخیره پرسرعت کوئری‌های پیچیده دیتابیس و تنظیمات قالب در حافظه رم برای کاهش لود سرور.', 'enabled' => true],
                ['id' => 'server_compression', 'title' => 'Gzip & Brotli Compression', 'title_fa' => 'فشرده‌سازی لایه‌ای بروتلی و Gzip', 'badge' => 'TRANSFER', 'badge_color' => '#6366F1', 'icon' => '🗜️', 'description' => 'Dynamic HTTP header configuration to compress static text, SVG, JSON, and Web fonts.', 'description_fa' => 'ارسال هدرهای فشرده‌سازی با بالاترین نرخ تراکم جهت کاهش چشمگیر حجم تبادل اطلاعات.', 'enabled' => true],
                ['id' => 'fonts_localizer', 'title' => 'Google & Persian Fonts Localizer', 'title_fa' => 'میزبانی محلی فونت‌های فارسی و گوگل', 'badge' => 'PRIVACY / SPEED', 'badge_color' => '#10B981', 'icon' => '🔤', 'description' => 'Self-hosts Google and Persian Vazirmatn fonts locally with preconnect links and display:swap.', 'description_fa' => 'میزبانی فونت‌های فارسی نظیر وزیرمتن مستقیماً روی سرور بدون نیاز به درخواست خارجی و تحمیل تاخیر.', 'enabled' => true],
            ],
            'mediaModules' => [
                ['id' => 'webp_avif_converter', 'title' => 'WebP & AVIF Conversion', 'title_fa' => 'تبدیل خودکار به فرمت‌های وب‌پی و AVIF', 'badge' => 'NEXT-GEN FORMATS', 'badge_color' => '#10B981', 'icon' => '⚡', 'description' => 'Automated lossless conversion of JPEG and PNG uploads with transparent fallback rewrite rules.', 'description_fa' => 'تبدیل خودکار تصاویر بارگذاری‌شده به فرمت‌های سبک نسل جدید با قابلیت حفظ پس‌زمینه شفاف.', 'enabled' => true],
                ['id' => 'dynamic_watermarking', 'title' => 'Dynamic Watermark Studio', 'title_fa' => 'استودیو واترمارک متحرک و پویا', 'badge' => 'BRAND PROTECTION', 'badge_color' => '#38BDF8', 'icon' => '🎨', 'description' => 'Non-destructive watermark overlay supporting 9 visual anchors, custom PNG logos, and opacity slider.', 'description_fa' => 'درج لوگو و واترمارک روی عکس‌ها بدون دستکاری فایل اصلی در ۹ موقعیت مختلف همراه با تنظیم شفافیت.', 'enabled' => true],
                ['id' => 'exif_metadata_scrubber', 'title' => 'EXIF Metadata Stripper', 'title_fa' => 'حذف اطلاعات حریم خصوصی EXIF عکس‌ها', 'badge' => 'PRIVACY', 'badge_color' => '#6366F1', 'icon' => '🛡️', 'description' => 'Removes GPS coordinates, camera serials, and timestamp metadata from uploaded user media.', 'description_fa' => 'پاکسازی خودکار مختصات مکانی GPS، مدل دوربین و متادیتای شخصی از فایل‌های مدیا هنگام آپلود.', 'enabled' => true],
                ['id' => 'cloud_offload_cdn', 'title' => 'S3 & Cloudflare CDN Offload', 'title_fa' => 'انتقال مدیاها به فضای ابری و CDN', 'badge' => 'ENTERPRISE', 'badge_color' => '#F59E0B', 'icon' => '☁️', 'description' => 'Syncs /wp-content/uploads/ directly to Amazon S3, Cloudflare R2, or bunny.net storage zones.', 'description_fa' => 'همگام‌سازی و آپلود پوشه رسانه‌ها در مخازن ابری و شبکه‌های توزیع محتوا برای صرفه‌جویی در هاست.', 'enabled' => false],
                ['id' => 'retina_generator', 'title' => 'CLS Dimension Guard', 'title_fa' => 'محافظ ابعاد تصویر جهت جلوگیری از CLS', 'badge' => 'CORE WEB VITALS', 'badge_color' => '#10B981', 'icon' => '📐', 'description' => 'Automatically detects and embeds explicit width and height attributes to prevent layout shifts.', 'description_fa' => 'تزریق خودکار طول و عرض دقیق برای تگ‌های تصویر به منظور حذف کامل پرش ناگهانی صفحه.', 'enabled' => true],
                ['id' => 'svg_sanitizer', 'title' => 'Lossy & Lossless Compression', 'title_fa' => 'موتور فشرده‌سازی باکیفیت Imagick/GD', 'badge' => 'IMAGICK / GD', 'badge_color' => '#38BDF8', 'icon' => '⚙️', 'description' => 'Advanced image compression engine utilizing local Imagick, GD, or cURL binaries.', 'description_fa' => 'کاهش حداکثری حجم فایل‌ها با الگوریتم‌های هوشمند متناسب با پهنای باند و استانداردهای وب.', 'enabled' => true],
            ],
            'aiModules' => [
                ['id' => 'smart_excerpt_generator', 'title' => 'Automated ALT & Meta Vision', 'title_fa' => 'تولید متن جایگزین هوشمند با بینایی ماشین', 'badge' => 'GEMINI VISION', 'badge_color' => '#10B981', 'icon' => '👁️', 'description' => 'Uses multi-modal AI vision to automatically analyze images and craft SEO and accessibility alt text.', 'description_fa' => 'تحلیل چندرسانه‌ای تصاویر با هوش مصنوعی جمینای ویژن و نگارش متن‌های جایگزین سئو و دسترس‌پذیری.', 'enabled' => true],
                ['id' => 'llm_manifest_auto', 'title' => 'Content Outline Studio', 'title_fa' => 'استودیو سرفصل و طرح‌بندی محتوای سئو', 'badge' => 'LLM WORKFLOW', 'badge_color' => '#38BDF8', 'icon' => '✍️', 'description' => 'Builds comprehensive article outlines based on top-ranking SERP competitor clusters.', 'description_fa' => 'طراحی ساختار و عناوین H2/H3 مقالات بر اساس تحلیل عمیق رقبای صفحه اول نتایج گوگل.', 'enabled' => true],
                ['id' => 'meta_desc_auto', 'title' => 'Brand Persona & Tone Tuning', 'title_fa' => 'تنظیم لحن و پرسونای اختصاصی برند', 'badge' => 'SYS PROMPT', 'badge_color' => '#F59E0B', 'icon' => '🎭', 'description' => 'Fine-tune tone, vocabulary constraints, and dialect profiles across all generated copy.', 'description_fa' => 'شخصی‌سازی لحن نگارش، دایره واژگان و دستورالعمل‌های خاص سازمانی در تمام خروجی‌های هوش مصنوعی.', 'enabled' => true],
                ['id' => 'bulk_content_enricher', 'title' => 'Semantic Interlinking Engine', 'title_fa' => 'موتور برداری لینک‌سازی معنایی', 'badge' => 'VECTOR EMBED', 'badge_color' => '#6366F1', 'icon' => '🧠', 'description' => 'Semantic similarity embeddings suggest internal links to elevate topical authority.', 'description_fa' => 'استفاده از وکتور امبدینگ برای شناسایی شباهت محتوایی و ایجاد پیوندهای درونی جهت افزایش اعتبار موضوعی.', 'enabled' => true],
                ['id' => 'faq_schema_ai', 'title' => 'Multi-Channel Repurposer', 'title_fa' => 'بازآفرینی چندکاناله محتوا', 'badge' => 'OMNICHANNEL', 'badge_color' => '#38BDF8', 'icon' => '🔄', 'description' => 'Converts long-form blog posts into Twitter/X threads, LinkedIn carousels, and newsletters.', 'description_fa' => 'تبدیل خودکار مقالات وبلاگ به رشته‌توییت، پست‌های لینکدین و خلاصه خبرنامه‌های ایمیلی جذاب.', 'enabled' => true],
                ['id' => 'brand_voice_tuning', 'title' => 'Prompt Manifests & Workflows', 'title_fa' => 'الگوهای پرامپت و متغیرهای پویا', 'badge' => 'TAG HELPERS', 'badge_color' => '#6366F1', 'icon' => '📑', 'description' => 'Custom prompt template builder with variable tag helpers ({post_title}, {post_content}).', 'description_fa' => 'طراحی قالب‌های پرامپت سفارشی همراه با متغیرهای در دسترس وردپرس ({post_title}، {site_name}).', 'enabled' => true],
            ],
            'providers' => [
                ['id' => 'openai', 'name' => 'OpenAI', 'badge' => 'Connected', 'color' => '#10B981', 'models' => 'GPT-4o / o3-mini'],
                ['id' => 'anthropic', 'name' => 'Anthropic Claude', 'badge' => 'Connected', 'color' => '#10B981', 'models' => 'Claude 3.5 / 3.7 Sonnet'],
                ['id' => 'deepseek', 'name' => 'DeepSeek', 'badge' => 'Active', 'color' => '#38BDF8', 'models' => 'DeepSeek V3 / R1'],
                ['id' => 'gemini', 'name' => 'Google Gemini', 'badge' => 'Active', 'color' => '#38BDF8', 'models' => 'Gemini 2.0 Flash'],
                ['id' => 'openrouter', 'name' => 'OpenRouter', 'badge' => 'Fallback', 'color' => '#6366F1', 'models' => '200+ Open Models'],
            ],
            'systemReport' => $system_report,
        ];
    }
}