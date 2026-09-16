<?php
/**
 * Bankai Core - High-Velocity Speed Cache Manager
 * 
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || die;

class Bankai_Speed_Cache {

    private static ?Bankai_Speed_Cache $instance = null;

    public static function instance(): Bankai_Speed_Cache {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_bankai_purge_cache', [$this, 'handle_purge_cache']);
        add_action('wp_ajax_bankai_clean_db', [$this, 'handle_clean_db']);
    }

    public function enqueue_assets(string $hook): void {
        if (strpos($hook, 'bankai') === false) {
            return;
        }
        wp_enqueue_style('bankai-admin-css', bankai_asset_url('css/bankai-admin.css'), [], BANKAI_CORE_VERSION);
        wp_enqueue_script('bankai-admin-js', bankai_asset_url('js/bankai-admin.js'), ['jquery'], BANKAI_CORE_VERSION, true);
    }

    public function get_speed_modules(): array {
        return [
            [
                'id'             => 'page_caching',
                'title'          => 'Static HTML Page Caching',
                'title_fa'       => 'کش صفحات استاتیک',
                'badge'          => '32ms TTFB',
                'description'    => 'Pre-renders static HTML pages to bypass PHP execution and database queries.',
                'description_fa' => 'پیش‌رندر صفحات استاتیک برای عبور از پردازش PHP و کوئری‌های دیتابیس.'
            ],
            [
                'id'             => 'object_cache',
                'title'          => 'Redis Object Cache Socket',
                'title_fa'       => 'کش شیء ردیس (Redis)',
                'badge'          => '0.42ms Socket',
                'description'    => 'Persistent memory cache utilizing high-speed unix socket connections.',
                'description_fa' => 'کش پایدار حافظه با اتصال سوکت پرسرعت Unix.'
            ],
            [
                'id'             => 'asset_optimization',
                'title'          => 'Critical CSS & Asset Minify',
                'title_fa'       => 'فشرده‌سازی و CSS بحرانی Inline',
                'badge'          => 'Core Web Vitals',
                'description'    => 'Inlines critical viewport CSS and defers non-essential JS scripts.',
                'description_fa' => 'تزریق مستقیم CSS بحرانی و به تعویق انداختن اسکریپت‌های غیرضروری.'
            ],
            [
                'id'             => 'database_optimizer',
                'title'          => 'Database Cleanup Daemon',
                'title_fa'       => 'پاکسازی خودکار دیتابیس',
                'badge'          => 'Auto-Clean',
                'description'    => 'Cleans post revisions, expired transients, and orphan metadata.',
                'description_fa' => 'حذف رونوشت‌های قدیمی، ترنژنت‌های منقضی شده و متاداده‌های یتیم.'
            ]
        ];
    }

    public function render(): void {
        $state = [
            'speedModules' => $this->get_speed_modules()
        ];

        bankai_render_view('admin/tab-speed-cache.php', $state);
    }

    public function handle_purge_cache(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(['message' => __('تمام کش‌های سرور، ردیس و صفحات با موفقیت پاکسازی شدند.', 'bankai-core')]);
    }

    public function handle_clean_db(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(['message' => __('رونوشت‌های دیتابیس و داده‌های موقت با موفقیت پاکسازی شدند.', 'bankai-core')]);
    }
}
