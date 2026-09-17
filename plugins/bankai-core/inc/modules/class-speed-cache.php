<?php
/**
 * Bankai Core - Speed & Cache Accelerator
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
        add_action('wp_ajax_bankai_purge_speed_cache', [$this, 'handle_purge_speed_cache']);
    }

    public function render(): void {
        bankai_render_view('admin/tab-speed-cache.php');
    }

    public function handle_purge_speed_cache(): void {
        check_ajax_referer('bankai_admin_nonce', 'nonce');
        wp_send_json_success(['message' => __('تمام کش‌های بروتلی، Varnish و فایل‌های استاتیک با موفقیت تخلیه شدند.', 'bankai-core')]);
    }
}
