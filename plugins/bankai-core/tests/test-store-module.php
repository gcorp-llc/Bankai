<?php
/**
 * Test Store Module Logic & API
 *
 * @package Bankai
 */

// Mock Minimal WordPress / WooCommerce Environment for Standalone Testing
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../../');
}
if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}
if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}

// Minimal mock functions if WP is not loaded
$GLOBALS['_mock_options'] = [
    'bankai_core_settings' => [
        'active_modules' => ['store' => true]
    ]
];
$GLOBALS['_mock_post_meta'] = [];
$GLOBALS['_mock_transients'] = [];

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) { return dirname($file) . '/'; }
}
if (!function_exists('trailingslashit')) {
    function trailingslashit($path) { return rtrim($path, '/\\') . '/'; }
}
if (!function_exists('content_url')) {
    function content_url($path = '') { return 'http://example.com/wp-content/' . ltrim($path, '/'); }
}
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
}
if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action, $query_arg) { return true; }
}
if (!function_exists('current_user_can')) {
    function current_user_can($cap) { return true; }
}
if (!function_exists('get_current_user_id')) {
    function get_current_user_id() { return 1; }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) { return trim(strip_tags((string)$str)); }
}
if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($str) { return trim(strip_tags((string)$str)); }
}
if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string)$key)); }
}
if (!function_exists('wp_unslash')) {
    function wp_unslash($val) { return $val; }
}
if (!function_exists('absint')) {
    function absint($val) { return max(0, (int)$val); }
}
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($str) { return (string)$str; }
}
if (!function_exists('get_option')) {
    function get_option($key, $default = false) {
        return $GLOBALS['_mock_options'][$key] ?? $default;
    }
}
if (!function_exists('update_option')) {
    function update_option($key, $val, $autoload = null) {
        $GLOBALS['_mock_options'][$key] = $val;
        return true;
    }
}
if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key = '', $single = false) {
        $data = $GLOBALS['_mock_post_meta'][$post_id][$key] ?? '';
        return $single ? $data : [$data];
    }
}
if (!function_exists('update_post_meta')) {
    function update_post_meta($post_id, $key, $value) {
        $GLOBALS['_mock_post_meta'][$post_id][$key] = $value;
        return true;
    }
}
if (!function_exists('delete_post_meta')) {
    function delete_post_meta($post_id, $key) {
        unset($GLOBALS['_mock_post_meta'][$post_id][$key]);
        return true;
    }
}
if (!function_exists('set_transient')) {
    function set_transient($key, $value, $expiration) {
        $GLOBALS['_mock_transients'][$key] = $value;
        return true;
    }
}
if (!function_exists('get_transient')) {
    function get_transient($key) {
        return $GLOBALS['_mock_transients'][$key] ?? false;
    }
}
if (!function_exists('delete_transient')) {
    function delete_transient($key) {
        unset($GLOBALS['_mock_transients'][$key]);
        return true;
    }
}

require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../inc/modules/class-store.php';

function assert_true($cond, $msg) {
    if (!$cond) {
        echo "FAIL: {$msg}\n";
        exit(1);
    }
    echo "PASS: {$msg}\n";
}

echo "=== Running Bankai Store Module Unit Tests ===\n";

// Test 1: Module Active check helper
assert_true(bankai_is_module_active('store') === true, 'Store module active by default or configured true');

// Test 2: Toggle Store module active status
bankai_set_module_active('store', false);
assert_true(bankai_is_module_active('store') === false, 'Store module can be deactivated');

bankai_set_module_active('store', true);
assert_true(bankai_is_module_active('store') === true, 'Store module can be reactivated');

// Test 3: Woo Active check behavior
$active_woo = Bankai_Store::woo_active();
echo "WooCommerce active status in test runner: " . ($active_woo ? 'true' : 'false') . "\n";

// Test 4: Verify scores computation & score caching
update_post_meta(101, '_bankai_seo_title', 'عنوان سئوی تست محصول');
update_post_meta(101, '_bankai_seo_description', 'توضیحات متا برای تست سئوی محصول ووکامرس بنکای');
update_post_meta(101, '_bankai_seo_focus_keyword', 'تست محصول');
update_post_meta(101, '_bankai_seo_faq_html', '<div class="bankai-product-faq">FAQ</div>');
update_post_meta(101, '_bankai_seo_geo_html', '<div class="bankai-product-geo">GEO</div>');

$store = Bankai_Store::instance();
$reflect = new ReflectionClass($store);
$method_scores = $reflect->getMethod('product_scores');
$method_scores->setAccessible(true);

$scores = $method_scores->invoke($store, 101);
assert_true($scores['seo'] >= 80, "SEO score calculated properly (got {$scores['seo']})");
assert_true($scores['aeo'] >= 70, "AEO score calculated properly (got {$scores['aeo']})");
assert_true($scores['geo'] >= 70, "GEO score calculated properly (got {$scores['geo']})");

// Test 5: Save scores caching
$method_save_scores = $reflect->getMethod('save_scores');
$method_save_scores->setAccessible(true);
$method_save_scores->invoke($store, 101);

$saved_meta = get_post_meta(101, '_bankai_product_scores', true);
assert_true(is_array($saved_meta) && isset($saved_meta['seo']), 'Product scores successfully saved to post meta');

echo "\nAll Store Module unit tests passed successfully!\n";
