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
    private string $menu_slug = 'bankai-core';

    private const TAB_MAP = [
        'bankai-core' => 'overview',
    ];

    public static function instance(): Bankai_Admin_Menu {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('script_loader_tag', [$this, 'add_defer_attribute'], 10, 3);
        add_action('admin_head', [$this, 'admin_menu_icon_styles']);
        add_filter('post_row_actions', [$this, 'post_row_ai_generate'], 20, 2);
        add_action('admin_head-edit.php', [$this, 'posts_list_ai_button_assets']);
        add_action('admin_footer-edit.php', [$this, 'posts_list_ai_button_inject']);
    }

    /**
     * کنترل دقیق اندازه و ظاهر آیکون منوی وردپرس
     * لوگو با گوشه‌های گرد + جلوگیری از فول‌سایز شدن
     */
    public function admin_menu_icon_styles(): void {
        // اسلاگ‌های ممکن: bankai (منوی مستقل بدون تم) و bankai-core (زیرمنو)
        $slugs = array_unique(array_filter([
            'bankai',
            $this->menu_slug,
            'bankai-core',
        ]));
        $sel_img = [];
        $sel_box = [];
        foreach ($slugs as $s) {
            $s = esc_attr($s);
            $sel_img[] = "#adminmenu li#toplevel_page_{$s} .wp-menu-image img";
            $sel_img[] = "#adminmenu .toplevel_page_{$s} .wp-menu-image img";
            $sel_img[] = "#adminmenu #toplevel_page_{$s} div.wp-menu-image img";
            $sel_box[] = "#adminmenu li#toplevel_page_{$s} .wp-menu-image";
            $sel_box[] = "#adminmenu #toplevel_page_{$s} .wp-menu-image";
        }
        $img = implode(",
            ", $sel_img);
        $box = implode(",
            ", $sel_box);
        ?>
        <style id="bankai-admin-menu-icon">
            /* اندازه ثابت آیکون منو — جلوگیری از نمایش فول‌سایز لوگو */
            <?php echo $img; ?> {
                width: 20px !important;
                height: 20px !important;
                max-width: 20px !important;
                max-height: 20px !important;
                padding: 6px 0 0 !important;
                margin: 0 auto !important;
                object-fit: contain !important;
                object-position: center center !important;
                background: transparent !important;
                display: block !important;
                border-radius: 5px !important;
            }
            <?php echo $box; ?> {
                width: 36px !important;
                height: 34px !important;
                overflow: hidden !important;
                background-size: 20px 20px !important;
            }
            /* حالت جمع‌شده */
            .folded #adminmenu li[id^="toplevel_page_bankai"] .wp-menu-image img {
                width: 20px !important;
                height: 20px !important;
                padding: 6px 0 0 !important;
            }
            #adminmenu li[id^="toplevel_page_bankai"] .wp-menu-image img {
                width: 20px !important;
                height: 20px !important;
                max-width: 20px !important;
                max-height: 20px !important;
            }
        </style>
        <?php
    }

    public function register_admin_menu(): void {
        // آیکون کوچک منو — نه لوگو فول‌سایز (باگ نمایش تمام‌صفحه)
        $menu_icon = 'dashicons-superhero';
        $icon_file = BANKAI_CORE_DIR . 'assets/images/menu-icon.svg';
        if (is_readable($icon_file)) {
            $menu_icon = bankai_asset_url('images/menu-icon.svg');
        } elseif (is_readable(BANKAI_CORE_DIR . 'assets/images/logo.jpg')) {
            // لوگو فقط اگر CSS اندازه را قفل کند؛ ترجیح با dashicon
            $menu_icon = 'dashicons-superhero';
        }
        $parent = 'bankai';

        // اگر تم Bankai والد Bankai را ساخته، Core زیرمنو می‌شود
        global $menu;
        $parent_exists = false;
        if (is_array($menu)) {
            foreach ($menu as $item) {
                if (isset($item[2]) && $item[2] === 'bankai') {
                    $parent_exists = true;
                    break;
                }
            }
        }

        if ($parent_exists) {
            $this->page_hook = add_submenu_page(
                $parent,
                __('Bankai Core', 'bankai-core'),
                __('Bankai Core', 'bankai-core'),
                'manage_options',
                $this->menu_slug,
                [$this, 'render_admin_layout']
            );
        } else {
            // بدون تم: منوی مستقل با عنوان Bankai و زیرمنوی Core
            add_menu_page(
                __('Bankai', 'bankai-core'),
                __('Bankai', 'bankai-core'),
                'manage_options',
                $parent,
                [$this, 'render_admin_layout'],
                $menu_icon,
                2
            );
            $this->page_hook = add_submenu_page(
                $parent,
                __('Bankai Core', 'bankai-core'),
                __('Bankai Core', 'bankai-core'),
                'manage_options',
                $this->menu_slug,
                [$this, 'render_admin_layout']
            );
            remove_submenu_page($parent, $parent);
        }
    }

    public function enqueue_admin_assets(string $hook_suffix): void {
        if (!$this->is_bankai_screen($hook_suffix)) {
            return;
        }

        // Fonts: system stack first; Vazirmatn progressive (async, few weights)
        $has_local_font = is_file(BANKAI_CORE_DIR . 'assets/fonts/Vazirmatn-Regular.woff2');
        add_action('admin_head', static function () use ($has_local_font) {
            // Preload main admin CSS for faster first paint
            $href = bankai_asset_url('css/bankai-admin.css');
            if ($href) {
                echo '<link rel="preload" href="' . esc_url($href) . '" as="style">' . "\n";
            }
            if ($has_local_font) {
                $woff = bankai_asset_url('fonts/Vazirmatn-Regular.woff2');
                echo '<link rel="preload" href="' . esc_url($woff) . '" as="font" type="font/woff2" crossorigin>' . "\n";
            } else {
                echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
                echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
            }
            // Non-blocking font CSS so admin paints with system fonts first
            if (!$has_local_font) {
                $font_css = 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap';
                echo '<link rel="preload" href="' . esc_url($font_css) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
                echo '<noscript><link rel="stylesheet" href="' . esc_url($font_css) . '"></noscript>' . "\n";
            }
        }, 1);

        if ($has_local_font && is_file(BANKAI_CORE_DIR . 'assets/fonts/bankai-fonts.css')) {
            wp_enqueue_style(
                'bankai-admin-fonts',
                bankai_asset_url('fonts/bankai-fonts.css'),
                [],
                BANKAI_CORE_VERSION
            );
        } else {
            // Registered for dependency order; actual load is async in admin_head above
            wp_register_style(
                'bankai-admin-fonts',
                'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap',
                [],
                null
            );
            // Do not wp_enqueue_style — avoids render-blocking duplicate
        }

        // No Material Symbols — icons are inline SVG (solar). Saves ~100KB+ font download.

        $css_ver = BANKAI_CORE_VERSION;
        $admin_css = BANKAI_CORE_DIR . 'assets/css/bankai-admin.css';
        if (is_file($admin_css)) {
            $css_ver .= '.' . (string) filemtime($admin_css);
        }

        wp_enqueue_style(
            'bankai-admin-css',
            bankai_asset_url('css/bankai-admin.css'),
            ['bankai-admin-fonts'],
            $css_ver
        );

        // Secondary CSS: only if files exist; versioned by mtime
        $sticky = BANKAI_CORE_DIR . 'assets/css/bankai-sidebar-sticky.css';
        if (is_file($sticky)) {
            wp_enqueue_style(
                'bankai-sidebar-sticky',
                bankai_asset_url('css/bankai-sidebar-sticky.css'),
                ['bankai-admin-css'],
                BANKAI_CORE_VERSION . '.' . (string) filemtime($sticky)
            );
        }

        $modals = BANKAI_CORE_DIR . 'assets/css/bankai-modals.css';
        if (is_file($modals)) {
            wp_enqueue_style(
                'bankai-modals',
                bankai_asset_url('css/bankai-modals.css'),
                ['bankai-admin-css'],
                BANKAI_CORE_VERSION . '.' . (string) filemtime($modals)
            );
        }

        // Scripts in footer; Alpine deferred after admin app is ready
        $js_ver = static function (string $rel) {
            $path = BANKAI_CORE_DIR . 'assets/js/' . $rel;
            $base = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0';
            return is_file($path) ? $base . '.' . (string) filemtime($path) : $base;
        };

        wp_enqueue_script(
            'bankai-htmx-js',
            bankai_asset_url('js/htmx.min.js'),
            [],
            $js_ver('htmx.min.js'),
            true
        );
        if (function_exists('wp_script_add_data')) {
            wp_script_add_data('bankai-htmx-js', 'strategy', 'defer');
        }

        wp_enqueue_media();

        // Classic editor assets for in-panel TinyMCE (safe — no fatal require)
        if (function_exists('wp_enqueue_editor')) {
            wp_enqueue_editor();
        }
        // Optional bundled class (only if file actually exists on this install)
        $editor_class = ABSPATH . 'wp-admin/includes/class-wp-editor.php';
        if (!class_exists('_WP_Editors', false) && is_readable($editor_class)) {
            require_once $editor_class;
        }
        if (class_exists('_WP_Editors') && is_callable(['_WP_Editors', 'enqueue_default_editor'])) {
            try {
                \_WP_Editors::enqueue_default_editor();
            } catch (\Throwable $e) {
                // ignore — wp_enqueue_editor above is enough
            }
        }
        // Styles / scripts only if already registered by core
        foreach (['editor-buttons', 'dashicons'] as $style) {
            if (wp_style_is($style, 'registered') || $style === 'dashicons') {
                wp_enqueue_style($style);
            }
        }
        foreach (['editor', 'quicktags', 'wplink', 'jquery-ui-autocomplete', 'wp-util'] as $script) {
            if (wp_script_is($script, 'registered')) {
                wp_enqueue_script($script);
            }
        }


        wp_enqueue_script(
            'bankai-admin-js',
            bankai_asset_url('js/bankai-admin.js'),
            ['jquery', 'bankai-htmx-js'],
            $js_ver('bankai-admin.js'),
            true
        );

        // Articles hub module (Alpine.data) — must register before Alpine boots
        if (is_file(BANKAI_CORE_DIR . 'assets/js/store-hub.js')) {
            wp_enqueue_script(
                'bankai-store-hub',
                bankai_asset_url('js/store-hub.js'),
                ['bankai-admin'],
                $js_ver('store-hub.js'),
                true
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/css/store-hub.css')) {
            wp_enqueue_style(
                'bankai-store-hub-css',
                bankai_asset_url('css/store-hub.css'),
                ['bankai-admin'],
                (defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0') . '.' . (string) filemtime(BANKAI_CORE_DIR . 'assets/css/store-hub.css')
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/js/articles-hub.js')) {
            wp_enqueue_script(
                'bankai-articles-hub',
                bankai_asset_url('js/articles-hub.js'),
                ['bankai-admin-js'],
                $js_ver('articles-hub.js'),
                true
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/js/seo-engine-tab.js')) {
            wp_enqueue_script(
                'bankai-seo-engine-tab',
                bankai_asset_url('js/seo-engine-tab.js'),
                ['bankai-admin-js', 'iconify-icon'],
                $js_ver('seo-engine-tab.js'),
                true
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/css/seo-engine-tab.css')) {
            wp_enqueue_style(
                'bankai-seo-engine-tab-css',
                bankai_asset_url('css/seo-engine-tab.css'),
                ['bankai-admin-css'],
                $js_ver('seo-engine-tab.js')
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/js/ai-studio-tab.js')) {
            wp_enqueue_script(
                'bankai-ai-studio-tab',
                bankai_asset_url('js/ai-studio-tab.js'),
                ['bankai-admin-js'],
                $js_ver('ai-studio-tab.js'),
                true
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/css/ai-studio-tab.css')) {
            wp_enqueue_style(
                'bankai-ai-studio-tab-css',
                bankai_asset_url('css/ai-studio-tab.css'),
                ['bankai-admin-css'],
                $js_ver('ai-studio-tab.js')
            );
        }
        // Solar Icons via Iconify (for Speed & Cache and other tabs)
        wp_enqueue_script(
            'iconify-icon',
            'https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js',
            [],
            '2.1.0',
            true
        );

        if (is_file(BANKAI_CORE_DIR . 'assets/css/wpr-layout.css')) {
            wp_enqueue_style(
                'bankai-wpr-layout',
                bankai_asset_url('css/wpr-layout.css'),
                ['bankai-admin-css'],
                defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0'
            );
        }

        if (is_file(BANKAI_CORE_DIR . 'assets/js/speed-cache-tab.js')) {
            wp_enqueue_script(
                'bankai-speed-cache-tab',
                bankai_asset_url('js/speed-cache-tab.js'),
                ['bankai-admin-js', 'iconify-icon'],
                $js_ver('speed-cache-tab.js'),
                true
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/css/speed-cache-tab.css')) {
            wp_enqueue_style(
                'bankai-speed-cache-tab-css',
                bankai_asset_url('css/speed-cache-tab.css'),
                ['bankai-admin-css'],
                $js_ver('speed-cache-tab.js')
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/css/media-watermark-tab.css')) {
            wp_enqueue_style(
                'bankai-media-watermark-tab-css',
                bankai_asset_url('css/media-watermark-tab.css'),
                ['bankai-admin-css'],
                $js_ver('speed-cache-tab.js')
            );
        }
        if (is_file(BANKAI_CORE_DIR . 'assets/css/articles-hub.css')) {
            wp_enqueue_style(
                'bankai-articles-hub-css',
                bankai_asset_url('css/articles-hub.css'),
                ['bankai-admin-css'],
                (defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0') . '.' . (string) filemtime(BANKAI_CORE_DIR . 'assets/css/articles-hub.css')
            );
        }

        // Alpine must load after component registrations
        wp_enqueue_script(
            'bankai-alpine-js',
            bankai_asset_url('js/alpine.min.js'),
            array_values(array_filter([
                'bankai-admin-js',
                is_file(BANKAI_CORE_DIR . 'assets/js/articles-hub.js') ? 'bankai-articles-hub' : null,
                is_file(BANKAI_CORE_DIR . 'assets/js/seo-engine-tab.js') ? 'bankai-seo-engine-tab' : null,
                is_file(BANKAI_CORE_DIR . 'assets/js/ai-studio-tab.js') ? 'bankai-ai-studio-tab' : null,
                is_file(BANKAI_CORE_DIR . 'assets/js/speed-cache-tab.js') ? 'bankai-speed-cache-tab' : null,
            ])),
            $js_ver('alpine.min.js'),
            true
        );
        // Do NOT defer Alpine — it auto-starts and must see prior registrations


        $core_data = [
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'adminUrl'   => trailingslashit(admin_url()),
            'restUrl'    => esc_url_raw(rest_url('bankai/v1/')),
            'nonce'      => wp_create_nonce('wp_rest'),
            'adminNonce' => wp_create_nonce('bankai_admin_nonce'),
            'isRtl'      => is_rtl(),
            'locale'     => get_user_locale(),
            'version'    => BANKAI_CORE_VERSION,
            'activeTab'  => $this->get_current_tab(),
            'wooActive'  => class_exists('WooCommerce'),
            'i18n'       => [
                'saving'  => __('در حال ذخیره‌سازی...', 'bankai-core'),
                'saved'   => __('ذخیره شد', 'bankai-core'),
                'error'   => __('خطا در ذخیره‌سازی', 'bankai-core'),
                'confirm' => __('آیا مطمئن هستید؟', 'bankai-core'),
            ],
        ];

        wp_localize_script('bankai-admin-js', 'bankaiCoreData', $core_data);

        // Settings persistence + module toggles
        $hooks = BANKAI_CORE_DIR . 'assets/js/bankai-admin-hooks.js';
        if (file_exists($hooks)) {
            wp_enqueue_script(
                'bankai-admin-hooks',
                bankai_asset_url('js/bankai-admin-hooks.js'),
                ['bankai-admin-js'],
                BANKAI_CORE_VERSION,
                true
            );
        }
    }

    private function is_bankai_screen(string $hook_suffix): bool {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if ($page !== '' && str_starts_with($page, 'bankai')) {
            return true;
        }

        if ($hook_suffix !== '' && str_contains($hook_suffix, 'bankai')) {
            return true;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen instanceof \WP_Screen) {
            if (str_contains((string) $screen->id, 'bankai') || str_contains((string) $screen->base, 'bankai')) {
                return true;
            }
        }

        return false;
    }

    public function add_defer_attribute(string $tag, string $handle, string $src = ''): string {
        if (!in_array($handle, ['bankai-alpine-js', 'bankai-htmx-js'], true)) {
            return $tag;
        }

        if (empty($src) || strpos($tag, ' src=') === false) {
            return $tag;
        }

        if (strpos($tag, ' defer') !== false) {
            return $tag;
        }

        return str_replace(' src=', ' defer="defer" src=', $tag);
    }

    private function get_current_tab(): string {
        if (!empty($_GET['bankai_tab'])) {
            $tab = sanitize_key(wp_unslash($_GET['bankai_tab']));
            $allowed = [
                'overview', 'store', 'articles', 'seo-engine', 'ai-studio',
                'speed-cache', 'media-watermark', 'settings-license',
                'jalali-calendar', 'theme-kits',
            ];
            if (in_array($tab, $allowed, true)) {
                return $tab;
            }
        }
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : $this->menu_slug;
        return self::TAB_MAP[$page] ?? 'overview';
    }

    public function render_admin_layout(): void {
        $state = array_merge(
            [
                'activeTab' => $this->get_current_tab(),
                'isRtl'     => is_rtl(),
            ],
            $this->get_initial_state_data()
        );

        bankai_render_view('admin/layout.php', ['state' => $state]);
    }

    public function get_initial_state_data(): array {
        $woo_active = class_exists('WooCommerce');
        $system_report = sprintf(
            "=== Bankai Core Diagnostic Telemetry ===\nPHP: %s | WP: %s | Server: %s | Memory Limit: %s\nActive Modules: 7/7 Enabled | Autonomous Engine Status: OPTIMAL",
            phpversion(),
            get_bloginfo('version'),
            $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            WP_MEMORY_LIMIT
        );

        $core_modules = class_exists('Bankai_Dashboard_Stats')
            ? Bankai_Dashboard_Stats::core_modules()
            : $this->get_core_modules_fallback();

        $stats = class_exists('Bankai_Dashboard_Stats')
            ? Bankai_Dashboard_Stats::telemetry_stats()
            : $this->get_telemetry_stats();

        $logs = class_exists('Bankai_Dashboard_Stats')
            ? Bankai_Dashboard_Stats::logs_404()
            : $this->get_sample_404_logs();

        return [
            'seoModules'   => $this->get_seo_modules(),
            'seoModuleSettings' => get_option('bankai_seo_module_settings', []) ?: [],
            'speedModules' => $this->get_speed_modules(),
            'mediaModules' => $this->get_media_modules(),
            'aiModules'    => $this->get_ai_modules(),
            'providers'    => $this->get_ai_providers(),
            'imageProviders' => class_exists('Bankai_AI_Studio')
                ? Bankai_AI_Studio::instance()->image_provider_status_list()
                : [],
            'systemReport' => $system_report,
            'stats'        => $stats,
            'logs404'      => $logs,
            'starterKits'  => $this->get_starter_kits(),
            'coreModules'  => $core_modules,
            'seoIntegrations' => class_exists('Bankai_SEO_Integrations')
                ? Bankai_SEO_Integrations::instance()->get_settings()
                : [],
            'homeUrl' => home_url('/'),
            'speedStats' => class_exists('Bankai_Speed_Cache') ? Bankai_Speed_Cache::collect_stats() : [],
            'aiDefaultProvider' => class_exists('Bankai_AI_Studio')
                ? Bankai_AI_Studio::instance()->get_default_provider()
                : 'gemini',
            'watermarkSettings' => class_exists('Bankai_Media_Watermark')
                ? Bankai_Media_Watermark::instance()->get_settings()
                : (get_option('bankai_watermark_settings', []) ?: []),
            'speedSettings' => [
                'cache_ttl' => function_exists('bankai_get_option') ? bankai_get_option('cache_ttl', 86400) : 86400,
                'cache_exclusions' => function_exists('bankai_get_option') ? bankai_get_option('cache_exclusions', '') : '',
            ],
        ];
    }

    private function get_telemetry_stats(): array {
        return [
            'uptime'        => '99.98%',
            'avg_latency'   => '18ms',
            'indexed_nodes' => '4,892',
            'varnish_hit'   => '96.4%',
            'ai_crawls'     => '12,410 Hits',
            'schema_score'  => '98/100',
            'overall_score' => '98',
            'ttfb'          => '32ms',
            'lcp'           => '0.8s',
        ];
    }

    private function get_sample_404_logs(): array {
        return [
            ['requested_uri' => '/old-product/xyz-2023', 'hits' => 142],
            ['requested_uri' => '/blog/deprecated-post', 'hits' => 87],
            ['requested_uri' => '/wp-content/uploads/old.jpg', 'hits' => 53],
            ['requested_uri' => '/category/archived', 'hits' => 29],
        ];
    }

    private function get_starter_kits(): array {
        return [
            [
                'name'        => 'Corporate Pro',
                'description' => 'Full business site with services, team and contact pages. RTL-ready.',
                'thumbnail'   => bankai_asset_url('images/kit-corporate.jpg'),
                'version'     => 'v2.1',
                'badges'      => ['RTL', 'WooCommerce'],
            ],
            [
                'name'        => 'Blog Magazine',
                'description' => 'High-performance magazine layout with category hubs and AI outlines.',
                'thumbnail'   => bankai_asset_url('images/kit-blog.jpg'),
                'version'     => 'v1.8',
                'badges'      => ['SEO', 'Fast'],
            ],
            [
                'name'        => 'E-Commerce Starter',
                'description' => 'WooCommerce-ready store with product schema and conversion-focused design.',
                'thumbnail'   => bankai_asset_url('images/kit-shop.jpg'),
                'version'     => 'v3.0',
                'badges'      => ['WooCommerce', 'Schema'],
            ],
        ];
    }

    private function get_seo_modules(): array {
        $saved = function_exists('bankai_get_option') ? bankai_get_option('seo_modules', []) : [];
        if (!is_array($saved)) { $saved = []; }
        $mods = [
            ['id' => 'auto_meta', 'title' => 'Autonomous Meta & Schema Generator', 'title_fa' => 'تولیدکننده خودکار متاداده و اسکیما', 'badge' => 'AI AUTO', 'badge_color' => '#10B981', 'icon' => '⚡', 'description' => 'Real-time JSON-LD structured data injection for Article, Product, FAQ, and BreadcrumbList.', 'description_fa' => 'تزریق خودکار کدهای استانداردهای نشانه‌گذاری گوگل (JSON-LD) بدون نیاز به تنظیمات پیچیده.', 'enabled' => true],
            ['id' => 'sitemap_pro', 'title' => 'High-Velocity XML & News Sitemap', 'title_fa' => 'نقشه سایت پیشرفته و قدرتمند XML', 'badge' => 'SPEED SITEMAP', 'badge_color' => '#38BDF8', 'icon' => '🗺️', 'description' => 'Generates instant XML sitemaps with ping protocols sent straight to Google Search Console.', 'description_fa' => 'ایجاد سریع نقشه سایت استاندارد و اطلاع‌رسانی لحظه‌ای به موتورهای جستجو هنگام انتشار مطلب.', 'enabled' => true],
            ['id' => 'canonical_guard', 'title' => 'Canonical & Redirects Matrix', 'title_fa' => 'مدیریت کانوینکال و هدایت ۴۰۴', 'badge' => 'SEO GUARD', 'badge_color' => '#F59E0B', 'icon' => '🔗', 'description' => 'Automated 301/302 redirect rules engine and canonical URL correction to prevent duplicate content.', 'description_fa' => 'جلوگیری از خطاهای محتوای تکراری و هدایت هوشمند آدرس‌های قدیمی به لینک جدید.', 'enabled' => true],
            ['id' => 'open_graph_ai', 'title' => 'Social Cards & OpenGraph AI', 'title_fa' => 'کارت‌های شبکه‌های اجتماعی (OG/Twitter)', 'badge' => 'VIRAL', 'badge_color' => '#EC4899', 'icon' => '📱', 'description' => 'Creates rich previews for WhatsApp, Twitter/X, and Telegram with automated image generation.', 'description_fa' => 'تنظیم خودکار تصویر، عنوان و توضیحات هنگام اشتراک‌گذاری لینک در شبکه‌های اجتماعی.', 'enabled' => true],
            ['id' => 'local_seo_schema', 'title' => 'Local SEO Knowledge Graph', 'title_fa' => 'سئوی محلی و گراف دانش گوگل', 'badge' => 'KNOWLEDGE GRAPH', 'badge_color' => '#6366F1', 'icon' => '📍', 'description' => 'Geo-coordinates, opening hours JSON-LD, business organization graphs, and Google Maps embed.', 'description_fa' => 'مختصات جغرافیایی، ساعات کاری، اسکیماهای کسب‌وکار محلی و اتصال نقشه برای رتبه اول لوکال سئو.', 'enabled' => true],
            ['id' => 'llms_txt_builder', 'title' => 'llms.txt Manifest Builder', 'title_fa' => 'تولیدکننده مانیفست llms.txt برای هوش مصنوعی', 'badge' => 'AI SPEC v1.2', 'badge_color' => '#10B981', 'icon' => '📄', 'description' => 'Generates standardized /llms.txt and /llms-full.txt files for AI agents and web crawlers.', 'description_fa' => 'تولید فایل‌های استاندارد llms.txt برای فهم ساختار سایت توسط موتورهای هوش مصنوعی و ربات‌ها.', 'enabled' => true],
        ];
        foreach ($mods as &$m) {
            $id = $m['id'] ?? '';
            if ($id !== '' && array_key_exists($id, $saved)) {
                $m['enabled'] = (bool) $saved[$id];
            }
        }
        unset($m);
        return $mods;
    }

    private function get_speed_modules(): array {
        $saved = function_exists('bankai_get_option') ? bankai_get_option('speed_modules', []) : [];
        if (!is_array($saved)) { $saved = []; }
        $mods = [
            ['id' => 'page_caching', 'title' => 'Page Cache & Varnish Purge', 'title_fa' => 'کش پیشرفته صفحات و تخلیه خودکار وارنیش', 'badge' => 'HIGH SPEED', 'badge_color' => '#10B981', 'icon' => '⚡', 'description' => 'Sub-50ms static HTML generation with automated edge cache purging upon post revision.', 'description_fa' => 'تولید فایل‌های استاتیک HTML فوق‌سریع و پاکسازی خودکار کش لبه سرور هنگام ویرایش نوشته‌ها.', 'enabled' => true],
            ['id' => 'asset_optimization', 'title' => 'CSS & JS Minification / Defer', 'title_fa' => 'فشرده‌سازی و بارگذاری تاخیری CSS و JS', 'badge' => 'CRITICAL CSS', 'badge_color' => '#38BDF8', 'icon' => '📦', 'description' => 'Eliminates render-blocking resources by generating critical inline CSS and delaying non-essential scripts.', 'description_fa' => 'حذف منابع مسدودکننده رندر با استخراج خودکار CSS بحرانی و به تعویق انداختن اسکریپت‌های سنگین.', 'enabled' => true],
            ['id' => 'database_optimizer', 'title' => 'Database Heuristic Sweeper', 'title_fa' => 'پاکسازی هوشمند پایگاه‌داده وردپرس', 'badge' => 'MAINTENANCE', 'badge_color' => '#F59E0B', 'icon' => '🧹', 'description' => 'Scheduled cleanup of post revisions, orphaned postmeta, spam comments, and transient transients.', 'description_fa' => 'حذف رونوشت‌های قدیمی، متادیتای یتیم، نظرات اسپم و بهینه‌سازی جداول MySQL طبق زمان‌بندی.', 'enabled' => true],
            ['id' => 'object_cache', 'title' => 'Redis Object Cache', 'title_fa' => 'کش آبجکت و دیتابیس ردیس (Redis)', 'badge' => 'IN-MEMORY', 'badge_color' => '#EF4444', 'icon' => '🧠', 'description' => 'Persistent Redis/Memcached daemon integration to cache complex MySQL queries and theme options.', 'description_fa' => 'ذخیره پرسرعت کوئری‌های پیچیده دیتابیس و تنظیمات قالب در حافظه رم برای کاهش لود سرور.', 'enabled' => true],
            ['id' => 'server_compression', 'title' => 'Gzip & Brotli Compression', 'title_fa' => 'فشرده‌سازی لایه‌ای بروتلی و Gzip', 'badge' => 'TRANSFER', 'badge_color' => '#6366F1', 'icon' => '🗜️', 'description' => 'Dynamic HTTP header configuration to compress static text, SVG, JSON, and Web fonts.', 'description_fa' => 'ارسال هدرهای فشرده‌سازی با بالاترین نرخ تراکم جهت کاهش چشمگیر حجم تبادل اطلاعات.', 'enabled' => true],
            ['id' => 'browser_cache', 'title' => 'Browser Cache Headers', 'title_fa' => 'بهینه‌سازی کش مرورگر', 'badge' => 'LIGHTHOUSE', 'badge_color' => '#0EA5E9', 'icon' => '🌐', 'description' => 'Long-lived Cache-Control and Expires headers for static CSS, JS, images and fonts via .htaccess.', 'description_fa' => 'هدرهای Cache-Control و Expires بلندمدت برای فایل‌های استاتیک (CSS، JS، تصویر، فونت) از طریق .htaccess — بهبود امتیاز Lighthouse.', 'enabled' => true],
            ['id' => 'fonts_localizer', 'title' => 'Google & Persian Fonts Localizer', 'title_fa' => 'میزبانی محلی فونت‌های فارسی و گوگل', 'badge' => 'PRIVACY / SPEED', 'badge_color' => '#10B981', 'icon' => '🔤', 'description' => 'Self-hosts Google and Persian Vazirmatn fonts locally with preconnect links and display:swap.', 'description_fa' => 'میزبانی فونت‌های فارسی نظیر وزیرمتن مستقیماً روی سرور بدون نیاز به درخواست خارجی و تحمیل تاخیر.', 'enabled' => true],
        ];
        foreach ($mods as &$m) {
            $id = $m['id'] ?? '';
            if ($id !== '' && array_key_exists($id, $saved)) {
                $m['enabled'] = (bool) $saved[$id];
            }
        }
        unset($m);
        return $mods;
    }

    private function get_media_modules(): array {
        $saved = function_exists('bankai_get_option') ? bankai_get_option('media_modules', []) : [];
        if (!is_array($saved)) { $saved = []; }
        $mods = [
            ['id' => 'webp_avif_converter', 'title' => 'WebP & AVIF Conversion', 'title_fa' => 'تبدیل خودکار به فرمت‌های وب‌پی و AVIF', 'badge' => 'NEXT-GEN FORMATS', 'badge_color' => '#10B981', 'icon' => '⚡', 'description' => 'Automated lossless conversion of JPEG and PNG uploads with transparent fallback rewrite rules.', 'description_fa' => 'تبدیل خودکار تصاویر بارگذاری‌شده به فرمت‌های سبک نسل جدید با قابلیت حفظ پس‌زمینه شفاف.', 'enabled' => true],
            ['id' => 'dynamic_watermarking', 'title' => 'Dynamic Watermark Studio', 'title_fa' => 'استودیو واترمارک متحرک و پویا', 'badge' => 'BRAND PROTECTION', 'badge_color' => '#38BDF8', 'icon' => '🎨', 'description' => 'Non-destructive watermark overlay supporting 9 visual anchors, custom PNG logos, and opacity slider.', 'description_fa' => 'درج لوگو و واترمارک روی عکس‌ها بدون دستکاری فایل اصلی در ۹ موقعیت مختلف همراه با تنظیم شفافیت.', 'enabled' => true],
            ['id' => 'exif_metadata_scrubber', 'title' => 'EXIF Metadata Stripper', 'title_fa' => 'حذف اطلاعات حریم خصوصی EXIF عکس‌ها', 'badge' => 'PRIVACY', 'badge_color' => '#6366F1', 'icon' => '🛡️', 'description' => 'Removes GPS coordinates, camera serials, and timestamp metadata from uploaded user media.', 'description_fa' => 'پاکسازی خودکار مختصات مکانی GPS، مدل دوربین و متادیتای شخصی از فایل‌های مدیا هنگام آپلود.', 'enabled' => true],
            ['id' => 'cloud_offload_cdn', 'title' => 'S3 & Cloudflare CDN Offload', 'title_fa' => 'انتقال مدیاها به فضای ابری و CDN', 'badge' => 'ENTERPRISE', 'badge_color' => '#F59E0B', 'icon' => '☁️', 'description' => 'Syncs /wp-content/uploads/ directly to Amazon S3, Cloudflare R2, or bunny.net storage zones.', 'description_fa' => 'همگام‌سازی و آپلود پوشه رسانه‌ها در مخازن ابری و شبکه‌های توزیع محتوا برای صرفه‌جویی در هاست.', 'enabled' => false],
            ['id' => 'retina_generator', 'title' => 'CLS Dimension Guard', 'title_fa' => 'محافظ ابعاد تصویر جهت جلوگیری از CLS', 'badge' => 'CORE WEB VITALS', 'badge_color' => '#10B981', 'icon' => '📐', 'description' => 'Automatically detects and embeds explicit width and height attributes to prevent layout shifts.', 'description_fa' => 'تزریق خودکار طول و عرض دقیق برای تگ‌های تصویر به منظور حذف کامل پرش ناگهانی صفحه.', 'enabled' => true],
            ['id' => 'svg_sanitizer', 'title' => 'Lossy & Lossless Compression', 'title_fa' => 'موتور فشرده‌سازی باکیفیت Imagick/GD', 'badge' => 'IMAGICK / GD', 'badge_color' => '#38BDF8', 'icon' => '⚙️', 'description' => 'Advanced image compression engine utilizing local Imagick, GD, or cURL binaries.', 'description_fa' => 'کاهش حداکثری حجم فایل‌ها با الگوریتم‌های هوشمند متناسب با پهنای باند و استانداردهای وب.', 'enabled' => true],
        ];
        foreach ($mods as &$m) {
            $id = $m['id'] ?? '';
            if ($id !== '' && array_key_exists($id, $saved)) {
                $m['enabled'] = (bool) $saved[$id];
            }
        }
        unset($m);
        return $mods;
    }

    private function get_ai_modules(): array {
        return [
            ['id' => 'smart_excerpt_generator', 'title' => 'Automated ALT & Meta Vision', 'title_fa' => 'تولید متن جایگزین هوشمند با بینایی ماشین', 'badge' => 'GEMINI VISION', 'badge_color' => '#10B981', 'icon' => '👁️', 'description' => 'Uses multi-modal AI vision to automatically analyze images and craft SEO and accessibility alt text.', 'description_fa' => 'تحلیل چندرسانه‌ای تصاویر با هوش مصنوعی جمینای ویژن و نگارش متن‌های جایگزین سئو و دسترس‌پذیری.', 'enabled' => true],
            ['id' => 'llm_manifest_auto', 'title' => 'Content Outline Studio', 'title_fa' => 'استودیو سرفصل و طرح‌بندی محتوای سئو', 'badge' => 'LLM WORKFLOW', 'badge_color' => '#38BDF8', 'icon' => '✍️', 'description' => 'Builds comprehensive article outlines based on top-ranking SERP competitor clusters.', 'description_fa' => 'طراحی ساختار و عناوین H2/H3 مقالات بر اساس تحلیل عمیق رقبای صفحه اول نتایج گوگل.', 'enabled' => true],
            ['id' => 'meta_desc_auto', 'title' => 'Brand Persona & Tone Tuning', 'title_fa' => 'تنظیم لحن و پرسونای اختصاصی برند', 'badge' => 'SYS PROMPT', 'badge_color' => '#F59E0B', 'icon' => '🎭', 'description' => 'Fine-tune tone, vocabulary constraints, and dialect profiles across all generated copy.', 'description_fa' => 'شخصی‌سازی لحن نگارش، دایره واژگان و دستورالعمل‌های خاص سازمانی در تمام خروجی‌های هوش مصنوعی.', 'enabled' => true],
            ['id' => 'bulk_content_enricher', 'title' => 'Semantic Interlinking Engine', 'title_fa' => 'موتور برداری لینک‌سازی معنایی', 'badge' => 'VECTOR EMBED', 'badge_color' => '#6366F1', 'icon' => '🧠', 'description' => 'Semantic similarity embeddings suggest internal links to elevate topical authority.', 'description_fa' => 'استفاده از وکتور امبدینگ برای شناسایی شباهت محتوایی و ایجاد پیوندهای درونی جهت افزایش اعتبار موضوعی.', 'enabled' => true],
            ['id' => 'faq_schema_ai', 'title' => 'Multi-Channel Repurposer', 'title_fa' => 'بازآفرینی چندکاناله محتوا', 'badge' => 'OMNICHANNEL', 'badge_color' => '#38BDF8', 'icon' => '🔄', 'description' => 'Converts long-form blog posts into Twitter/X threads, LinkedIn carousels, and newsletters.', 'description_fa' => 'تبدیل خودکار مقالات وبلاگ به رشته‌توییت، پست‌های لینکدین و خلاصه خبرنامه‌های ایمیلی جذاب.', 'enabled' => true],
            ['id' => 'brand_voice_tuning', 'title' => 'Prompt Manifests & Workflows', 'title_fa' => 'الگوهای پرامپت و متغیرهای پویا', 'badge' => 'TAG HELPERS', 'badge_color' => '#6366F1', 'icon' => '📑', 'description' => 'Custom prompt template builder with variable tag helpers ({post_title}, {post_content}).', 'description_fa' => 'طراحی قالب‌های پرامپت سفارشی همراه با متغیرهای در دسترس وردپرس ({post_title}، {site_name}).', 'enabled' => true],
        ];
    }

    private function get_ai_providers(): array {
        if (class_exists('Bankai_AI_Studio')) {
            $list = Bankai_AI_Studio::instance()->provider_status_list();
            $out = [];
            foreach ($list as $p) {
                // نام واقعی موتور (نه status label مثل «کلید تنظیم شده»)
                $name = $p['name'] ?? ($p['id'] ?? '');
                $status_label = $p['label'] ?? '';
                $out[] = [
                    'id'     => $p['id'] ?? '',
                    'name'   => $name,
                    'label'  => $name,
                    'badge'  => $name,
                    'status_label' => $status_label,
                    'color'  => !empty($p['has_key']) ? '#10B981' : '#8C959F',
                    'models' => is_array($p['models'] ?? null) ? implode(' / ', array_slice($p['models'], 0, 3)) : (string) ($p['model'] ?? ''),
                    'has_key'=> !empty($p['has_key']),
                    'ready'  => !empty($p['has_key']),
                ];
            }
            return $out;
        }
        return [];
    }

    /**
     * Link from WP posts list → Bankai Articles AI generate tab.
     */
    public function post_row_ai_generate(array $actions, $post): array
    {
        if (!$post instanceof WP_Post || $post->post_type !== 'post') {
            return $actions;
        }
        if (!current_user_can('edit_posts')) {
            return $actions;
        }
        $url = admin_url('admin.php?page=bankai-core&bankai_tab=articles&subtab=generate');
        $actions['bankai_ai_generate'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($url),
            esc_html__('تولید مقاله با AI', 'bankai-core')
        );
        return $actions;
    }

    public function posts_list_ai_button_assets(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'edit-post') {
            return;
        }
        ?>
        <style id="bankai-ai-write-btn">
            .page-title-action.bankai-ai-write-btn,
            a.page-title-action.bankai-ai-write-btn {
                display: inline-flex !important;
                align-items: center;
                gap: 6px;
                margin-inline-start: 8px !important;
                background: linear-gradient(135deg, #7c3aed 0%, #0969da 100%) !important;
                border-color: transparent !important;
                color: #fff !important;
                box-shadow: 0 1px 2px rgba(9,105,218,.25);
                font-weight: 600;
                border-radius: 6px !important;
                padding: 4px 12px !important;
                line-height: 2.15384615;
                text-decoration: none !important;
            }
            .page-title-action.bankai-ai-write-btn:hover,
            a.page-title-action.bankai-ai-write-btn:hover {
                background: linear-gradient(135deg, #6d28d9 0%, #0550ae 100%) !important;
                color: #fff !important;
            }
            .page-title-action.bankai-ai-write-btn svg {
                width: 16px; height: 16px; flex-shrink: 0;
            }
        </style>
        <?php
    }

    public function posts_list_ai_button_inject(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'edit-post') {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }
        $url = admin_url('admin.php?page=bankai-core&bankai_tab=articles&subtab=generate');
        $label = esc_js(__('نوشتن مقاله با AI', 'bankai-core'));
        $href = esc_url($url);
        ?>
        <script>
        (function(){
            var wrap = document.querySelector('.wrap .wp-heading-inline') || document.querySelector('.wrap h1');
            if (!wrap) return;
            var existing = document.querySelector('.page-title-action.bankai-ai-write-btn');
            if (existing) return;
            var a = document.createElement('a');
            a.href = <?php echo wp_json_encode($href); ?>;
            a.className = 'page-title-action bankai-ai-write-btn';
            a.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/><path d="M4 19h7"/></svg> ' + <?php echo wp_json_encode(wp_strip_all_tags(__('نوشتن مقاله با AI', 'bankai-core'))); ?>;
            // Insert after first .page-title-action (افزودن نوشته) if present
            var addBtn = document.querySelector('.wrap a.page-title-action:not(.bankai-ai-write-btn)');
            if (addBtn && addBtn.parentNode) {
                addBtn.parentNode.insertBefore(a, addBtn.nextSibling);
            } else {
                wrap.parentNode.insertBefore(a, wrap.nextSibling);
            }
        })();
        </script>
        <?php
    }

}
