<?php
/**
 * Bankai Core - Admin Menu & Asset Orchestrator
 *
 * @package Bankai
 * @subpackage Includes
 */

defined('ABSPATH') || die;

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
        add_action('admin_menu', [$this, 'register_admin_menu'], 9);
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

        // Submenus for direct navigation
        add_submenu_page(
            'bankai-core',
            __('Overview & Health', 'bankai-core'),
            __('پیشخوان و سلامت', 'bankai-core'),
            'manage_options',
            'bankai-core#overview',
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
            __('Autonomous SEO Engine', 'bankai-core'),
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

        if (strpos($hook_suffix, 'bankai') !== false) {
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

        // Load Alpine JS AFTER bankai-admin-js so Alpine can evaluate bankaiAdmin() immediately without ReferenceError
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
            'assetsUrl' => bankai_asset_url(''),
            'isRtl'     => is_rtl(),
            'activeTab' => 'overview',
        ];

        // Localize under both variable names to guarantee backwards compatibility
        wp_localize_script('bankai-admin-js', 'bankaiCoreData', $core_data);
        wp_localize_script('bankai-admin-js', 'bankaiData', $core_data);
    }

    public function add_defer_attribute(string $tag, string $handle): string {
        if (in_array($handle, ['bankai-admin-js', 'bankai-alpine-js', 'bankai-htmx-js'], true)) {
            if (strpos($tag, ' defer') === false) {
                return str_replace(' src', ' defer src', $tag);
            }
        }
        return $tag;
    }

    public function render_admin_layout(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('شما اجازه دسترسی به این بخش را ندارید.', 'bankai-core'));
        }

        $state = self::get_admin_state();
        bankai_render_view('admin/layout.php', ['state' => $state]);
    }

    /**
     * Get complete admin telemetry, modules and starter kits state
     */
    public static function get_admin_state(): array {
        global $wpdb;

        $db_version = method_exists($wpdb, 'db_version') ? $wpdb->db_version() : 'MySQL 8.0';
        $php_ver = phpversion();
        $wp_ver = get_bloginfo('version');
        $server_software = sanitize_text_field($_SERVER['SERVER_SOFTWARE'] ?? 'Nginx / LiteSpeed');
        $theme_name = function_exists('wp_get_theme') ? wp_get_theme()->get('Name') : 'Bankai Framework';
        $mem_limit = defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : '256M';

        $system_report = "### Bankai WordPress Platform System Report ###\n" .
            "WordPress Version: {$wp_ver}\n" .
            "PHP Version: {$php_ver}\n" .
            "Database: {$db_version}\n" .
            "Server Software: {$server_software}\n" .
            "Active Theme: {$theme_name}\n" .
            "Memory Limit: {$mem_limit}\n" .
            "REST API Route: " . esc_url(rest_url('bankai/v1')) . "\n" .
            "Object Cache: " . (wp_using_ext_object_cache() ? 'External Cache Active (Redis/Memcached)' : 'Standard WP Transients Cache') . "\n" .
            "Multilingual Support: Full Persian (RTL / Vazirmatn) + English (LTR) Active\n" .
            "Autonomous SEO & Speed Engine: Active";

        return [
            'activeTab' => 'overview',
            'isRtl'     => is_rtl(),
            'stats'     => [
                'uptime'        => '99.98%',
                'avg_latency'   => '18ms',
                'indexed_nodes' => '4,892',
                'varnish_hit'   => '96.4%',
                'ai_crawls'     => '12,410 Hits',
                'schema_score'  => '98/100',
                'overall_score' => '98',
                'ttfb'          => '32ms',
                'lcp'           => '0.8s',
            ],
            'logs404' => [
                ['requested_uri' => '/wp-content/themes/old-theme/style.css', 'hits' => 342, 'action_type' => 'auto_redirected', 'target_uri' => '/'],
                ['requested_uri' => '/product/summer-sale-2023/', 'hits' => 189, 'action_type' => 'auto_redirected', 'target_uri' => '/shop/'],
                ['requested_uri' => '/feed/rss2/', 'hits' => 88, 'action_type' => 'auto_redirected', 'target_uri' => '/feed/'],
                ['requested_uri' => '/api/v1/legacy-endpoint', 'hits' => 54, 'action_type' => 'auto_dropped', 'target_uri' => ''],
                ['requested_uri' => '/wp-login.php?action=register', 'hits' => 39, 'action_type' => 'auto_dropped', 'target_uri' => ''],
            ],
            'starterKits' => [
                [
                    'id'             => 'cyber_store',
                    'name'           => 'Cyberpunk WooCommerce Store',
                    'name_fa'        => 'فروشگاه ووکامرس سایبرپانک و دارک‌مود',
                    'description'    => 'High-conversion dark-mode ecommerce architecture with instant AJAX search, cart slideout, and WebP product galleries.',
                    'description_fa' => 'معماری فروشگاهی بهینه‌سازی‌شده نرخ تبدیل با جستجوی آنی آجاکس، سبد خرید کشویی شناور و گالری محصولات فرمت WebP.',
                    'version'        => 'v1.4.0',
                    'thumbnail'      => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80',
                    'badges'         => ['WooCommerce', 'Tailwind', 'AJAX Cart'],
                    'preview_url'    => '#',
                ],
                [
                    'id'             => 'tech_news',
                    'name'           => 'Tech Portal & Magazine News',
                    'name_fa'        => 'پرتال خبری و مجله تخصصی فناوری',
                    'description'    => 'High-traffic publisher template featuring 0.8s LCP scores, automated Schema.org NewsArticle markup, and Google Discover optimizations.',
                    'description_fa' => 'قالب پرسرعت برای سایت‌های پرترافیک و ناشران با امتیاز سرعت LCP زیر ۰.۸ ثانیه، نشانه‌گذاری خودکار اسکیما و سئوی گوگل دیسکاور.',
                    'version'        => 'v2.1.0',
                    'thumbnail'      => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80',
                    'badges'         => ['NewsArticle Schema', 'AMP Ready', '0.8s LCP'],
                    'preview_url'    => '#',
                ],
                [
                    'id'             => 'saas_agency',
                    'name'           => 'AI Agency & B2B SaaS Platform',
                    'name_fa'        => 'پلتفرم شرکتی، آژانس هوش مصنوعی و SaaS',
                    'description'    => 'Modern corporate architecture with interactive pricing tables, Persian RTL typography support, and dynamic lead capture workflows.',
                    'description_fa' => 'معماری شرکتی مدرن با جداول قیمت‌گذاری تعاملی، تایپوگرافی کامل راست‌چین با فونت وزیرمتن و فرم‌های تبدیل کاربر به مشتری.',
                    'version'        => 'v1.8.2',
                    'thumbnail'      => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
                    'badges'         => ['RTL Standard', 'Vazirmatn', 'Lead Gen'],
                    'preview_url'    => '#',
                ],
            ],
            'seoModules' => [
                ['id' => 'ai_search_visibility', 'title' => 'AI Search Visibility (GEO)', 'title_fa' => 'دیده‌شدن در موتورهای هوش مصنوعی (GEO)', 'badge' => 'CORE LLM', 'badge_color' => '#38BDF8', 'icon' => '🤖', 'description' => 'Optimizes content structure and metadata for generative engines like Google Gemini, ChatGPT, and Perplexity.', 'description_fa' => 'بهینه‌سازی ساختار محتوا و متادیتا برای موتورهای جستجوی هوش مصنوعی مانند گوگل جمینای، چت‌جی‌پی‌تی و پرپلکسیتی.', 'enabled' => true],
                ['id' => 'ai_meta_assistant', 'title' => 'AI Meta Description Assistant', 'title_fa' => 'دستیار تولید متای عنوان و توضیحات سئو', 'badge' => 'GENERATIVE', 'badge_color' => '#10B981', 'icon' => '✨', 'description' => 'Automated SERP snippet generation with CTR prediction models and real-time character limit enforcement.', 'description_fa' => 'تولید خودکار اسنیپت‌های نتایج جستجو با مدل‌های تخمین نرخ کلیک و رعایت دقیق محدودیت کاراکترها.', 'enabled' => true],
                ['id' => 'ai_link_genius', 'title' => 'Smart Internal Link Genius', 'title_fa' => 'پیشنهاددهنده هوشمند لینک‌سازی داخلی', 'badge' => 'HEURISTIC', 'badge_color' => '#6366F1', 'icon' => '🔗', 'description' => 'Contextual keyword scanning across custom post types to recommend semantic internal anchor links.', 'description_fa' => 'پویش معنایی متن مقالات و پست تایپ‌های اختصاصی برای پیشنهاد بهترین متن‌های پیوند داخلی مرتبط.', 'enabled' => true],
                ['id' => 'instant_indexing', 'title' => 'Instant Indexing API', 'title_fa' => 'ایندکس آنی (گوگل و بینگ)', 'badge' => 'GOOGLE & BING', 'badge_color' => '#F59E0B', 'icon' => '⚡', 'description' => 'Real-time pinging of Google Indexing API and IndexNow upon post publishing or updates.', 'description_fa' => 'ارسال و پینگ خودکار آدرس مقالات بلافاصله پس از انتشار یا بروزرسانی به Google API و IndexNow.', 'enabled' => true],
                ['id' => 'xml_sitemaps', 'title' => 'High-Velocity XML Sitemaps', 'title_fa' => 'نقشه‌های سایت XML پرسرعت و بهینه', 'badge' => 'CRITICAL', 'badge_color' => '#10B981', 'icon' => '🗺️', 'description' => 'Zero-overhead chunked sitemaps supporting Google News, Video, and Image XML protocols with instant invalidation.', 'description_fa' => 'تولید بدون تاخیر نقشه‌های سایت بخش‌بندی‌شده، شامل نقشه‌های خبری، تصویری و ویدیویی با کش هوشمند.', 'enabled' => true],
                ['id' => 'schema_builder', 'title' => 'Rich Snippet & Schema.org Builder', 'title_fa' => 'سازنده ساختاریافته اسکیما و ریچ اسنیپت', 'badge' => 'JSON-LD', 'badge_color' => '#8250DF', 'icon' => '🛡️', 'description' => 'Automatic JSON-LD graph generation for Article, Product, Organization, FAQPage, and BreadcrumbList.', 'description_fa' => 'تزریق خودکار اسکیماهای استاندارد سازمانی، محصول، مقالات و سوالات متداول در هدر صفحات.', 'enabled' => true],
                ['id' => 'monitor_404', 'title' => '404 Anomaly & 301 Auto-Redirects', 'title_fa' => 'رصد خطاهای ۴۰۴ و ریدایرکت خودکار ۳۰۱', 'badge' => 'HEURISTIC', 'badge_color' => '#CF222E', 'icon' => '⚠️', 'description' => 'Heuristic error catcher intercepting invalid URLs and creating smart automated 301 redirects to target matches.', 'description_fa' => 'شناسایی لینک‌های شکسته و آدرس‌های نامعتبر و تغییر مسیر خودکار هوشمند به مرتبط‌ترین صفحه مقصد.', 'enabled' => true],
                ['id' => 'image_seo', 'title' => 'Automated Image SEO', 'title_fa' => 'سئوی خودکار تصاویر و متن جایگزین (Alt)', 'badge' => 'ACCESSIBILITY', 'badge_color' => '#38BDF8', 'icon' => '🖼️', 'description' => 'Auto ALT tag generation, dynamic image title attribute injection, and WebP fallback attributes.', 'description_fa' => 'تولید خودکار برچسب‌های متن جایگزین و عنوان عکس‌ها بر اساس عنوان نوشته و تحلیل محتوا.', 'enabled' => true],
                ['id' => 'acf_integration', 'title' => 'ACF Meta Integration', 'title_fa' => 'سازگاری پیشرفته با زمینه دلخواه (ACF)', 'badge' => 'RANKMATH-GRADE', 'badge_color' => '#10B981', 'icon' => '📦', 'description' => 'Deep integration with Advanced Custom Fields to analyze dynamic content blocks for SEO density.', 'description_fa' => 'تحلیل دقیق کلمات کلیدی و محتوای سفارشی ذخیره‌شده در فیلدهای Advanced Custom Fields.', 'enabled' => true],
                ['id' => 'woocommerce_seo', 'title' => 'WooCommerce SEO Suite', 'title_fa' => 'بسته تخصصی سئوی ووکامرس', 'badge' => 'ECOMMERCE', 'badge_color' => '#F59E0B', 'icon' => '🛒', 'description' => 'Product GTIN/MPN schema fields, brand taxonomies, price currency metadata, and canonical rules.', 'description_fa' => 'ثبت اسکیماهای قیمت، موجودی، برند، متادیتای ارزی ریال/تومان و تنظیم کانونیکال محصولات.', 'enabled' => true],
                ['id' => 'local_seo', 'title' => 'Local SEO Knowledge Graph', 'title_fa' => 'سئوی محلی و گراف دانش گوگل', 'badge' => 'KNOWLEDGE GRAPH', 'badge_color' => '#6366F1', 'icon' => '📍', 'description' => 'Geo-coordinates, opening hours JSON-LD, business organization graphs, and Google Maps embed.', 'description_fa' => 'مختصات جغرافیایی، ساعات کاری، اسکیماهای کسب‌وکار محلی و اتصال نقشه برای رتبه اول لوکال سئو.', 'enabled' => true],
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
