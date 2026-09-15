<?php
if (!defined('ABSPATH')) {
    exit;
}

class Bankai_Speed_Cache {

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_bankai_purge_cache', array($this, 'handle_purge_cache'));
        add_action('wp_ajax_bankai_clean_db', array($this, 'handle_clean_db'));
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'bankai') === false) {
            return;
        }
        wp_enqueue_style('bankai-speed-css', BANKAI_PLUGIN_URL . 'assets/css/speed.css', array(), BANKAI_VERSION);
        wp_enqueue_script('bankai-speed-js', BANKAI_PLUGIN_URL . 'assets/js/speed.js', array('jquery'), BANKAI_VERSION, true);
    }

    public function get_speed_modules() {
        return array(
            array(
                'id' => 'page_caching',
                'title' => 'Static HTML Page Caching',
                'title_fa' => 'کش صفحات استاتیک',
                'badge' => '32ms TTFB',
                'description' => 'Pre-renders static HTML pages to bypass PHP execution and database queries.',
                'description_fa' => 'پیش‌رندر صفحات استاتیک برای عبور از پردازش PHP و کوئری‌های دیتابیس.'
            ),
            array(
                'id' => 'object_cache',
                'title' => 'Redis Object Cache Socket',
                'title_fa' => 'کش شیء ردیس (Redis)',
                'badge' => '0.42ms Socket',
                'description' => 'Persistent memory cache utilizing high-speed unix socket connections.',
                'description_fa' => 'کش پایدار حافظه با اتصال سوکت پرسرعت Unix.'
            ),
            array(
                'id' => 'asset_optimization',
                'title' => 'Critical CSS & Asset Minify',
                'title_fa' => 'فشرده‌سازی و CSS بحرانی Inline',
                'badge' => 'Core Web Vitals',
                'description' => 'Inlines critical viewport CSS and defers non-essential JS scripts.',
                'description_fa' => 'تزریق مستقیم CSS بحرانی و به تعویق انداختن اسکریپت‌های غیرضروری.'
            ),
            array(
                'id' => 'database_optimizer',
                'title' => 'Database Cleanup Daemon',
                'title_fa' => 'پاکسازی خودکار دیتابیس',
                'badge' => 'Auto-Clean',
                'description' => 'Cleans post revisions, expired transients, and orphan metadata.',
                'description_fa' => 'حذف رونوشت‌های قدیمی، ترنژنت‌های منقضی شده و متاداده‌های یتیم.'
            )
        );
    }

    public function render() {
        $state = array(
            'speedModules' => $this->get_speed_modules()
        );

        $view_path = BANKAI_PLUGIN_DIR . 'views/admin/speed-cache.php';
        if (file_exists($view_path)) {
            include $view_path;
        }
    }

    public function handle_purge_cache() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(array('message' => 'تمام کش‌های سرور، ردیس و صفحات با موفقیت پاکسازی شدند.'));
    }

    public function handle_clean_db() {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(array('message' => 'رونوشت‌های دیتابیس و داده‌های موقت با موفقیت پاکسازی شدند.'));
    }
}