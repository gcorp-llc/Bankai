<?php
/**
 * Bankai Core - Admin Views Smoke Test
 */

define('ABSPATH', __DIR__ . '/');
define('BANKAI_CORE_DIR', __DIR__ . '/../plugins/bankai-core/');
define('BANKAI_CORE_VIEWS_DIR', BANKAI_CORE_DIR . 'views/');

// Mock WP core functions if needed
if (!function_exists('add_action')) { function add_action($a, $b, $p=10, $a_num=1) { return true; } }
if (!function_exists('add_filter')) { function add_filter($a, $b, $p=10, $a_num=1) { return true; } }
if (!function_exists('absint')) { function absint($s) { return abs((int)$s); } }
if (!function_exists('esc_html')) { function esc_html($s) { return $s; } }
if (!function_exists('esc_attr')) { function esc_attr($s) { return $s; } }
if (!function_exists('esc_url')) { function esc_url($s) { return $s; } }
if (!function_exists('esc_textarea')) { function esc_textarea($s) { return $s; } }
if (!function_exists('esc_js')) { function esc_js($s) { return $s; } }
if (!function_exists('esc_html__')) { function esc_html__($s, $d='') { return $s; } }
if (!function_exists('esc_html_e')) { function esc_html_e($s, $d='') { echo $s; } }
if (!function_exists('esc_attr_e')) { function esc_attr_e($s, $d='') { echo $s; } }
if (!function_exists('_e')) { function _e($s, $d='') { echo $s; } }
if (!function_exists('__')) { function __($s, $d='') { return $s; } }
if (!function_exists('sanitize_key')) { function sanitize_key($s) { return strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $s)); } }
if (!function_exists('is_rtl')) { function is_rtl() { return true; } }
if (!function_exists('get_user_locale')) { function get_user_locale() { return 'fa_IR'; } }
if (!function_exists('wp_create_nonce')) { function wp_create_nonce($a) { return 'nonce_' . $a; } }
if (!function_exists('rest_url')) { function rest_url($p) { return 'https://example.com/wp-json/' . $p; } }
if (!function_exists('admin_url')) { function admin_url($p) { return 'https://example.com/wp-admin/' . $p; } }
if (!function_exists('home_url')) { function home_url($p) { return 'https://example.com' . $p; } }
if (!function_exists('get_bloginfo')) { function get_bloginfo($p) { return '6.5.0'; } }
if (!function_exists('get_the_ID')) { function get_the_ID() { return 1; } }
if (!function_exists('get_post')) { function get_post($id) { return (object)['ID' => $id, 'post_title' => 'Test Post', 'post_author' => 1]; } }
if (!function_exists('get_post_meta')) { function get_post_meta($id, $key, $single=true) { return '0'; } }
if (!function_exists('get_permalink')) { function get_permalink($id) { return 'https://example.com/test-post'; } }
if (!function_exists('get_the_author_meta')) { function get_the_author_meta($k, $u) { return 'Admin'; } }
if (!function_exists('get_the_date')) { function get_the_date($f, $id) { return '1403/01/01'; } }
if (!function_exists('get_option')) { function get_option($k, $d=false) { return $d; } }
if (!function_exists('update_option')) { function update_option($k, $v) { return true; } }
if (!function_exists('selected')) { function selected($selected, $current = true, $echo = true) { $res = ((string)$selected === (string)$current) ? 'selected="selected"' : ''; if ($echo) echo $res; return $res; } }
if (!function_exists('checked')) { function checked($checked, $current = true, $echo = true) { $res = ((string)$checked === (string)$current) ? 'checked="checked"' : ''; if ($echo) echo $res; return $res; } }
if (!function_exists('wp_json_encode')) { function wp_json_encode($data, $options=0, $depth=512) { return json_encode($data, $options, $depth); } }
if (!function_exists('bankai_get_option')) { function bankai_get_option($k='', $d=false) { return $d; } }
if (!function_exists('bankai_seo_score_color')) { function bankai_seo_score_color($s) { return '#10B981'; } }
if (!function_exists('get_categories')) { function get_categories($args=[]) { return [(object)['term_id' => 1, 'name' => 'General']]; } }
if (!function_exists('get_users')) { function get_users($args=[]) { return [(object)['ID' => 1, 'display_name' => 'Admin']]; } }

// Load Bankai_AI_Studio class if available, or mock
if (file_exists(BANKAI_CORE_DIR . 'inc/modules/class-ai-studio.php')) {
    require_once BANKAI_CORE_DIR . 'inc/modules/class-ai-studio.php';
}

echo "Running Bankai Admin Views Smoke Test...\n";

$mock_state = [
    'activeTab' => 'overview',
    'isRtl'     => true,
    'seoModules' => [],
    'speedModules' => [],
    'speedStats' => [],
    'speedSettings' => [],
    'mediaModules' => [],
    'watermarkSettings' => [],
    'coreModules' => [],
    'seoIntegrations' => [],
    'homeUrl' => 'https://example.com',
    'stats' => [],
    'aiModules' => [],
    'providers' => [],
    'aiDefaultProvider' => 'gemini',
];

$tabs = [
    'overview',
    'seo-engine',
    'ai-studio',
    'articles',
    'speed-cache',
    'media-watermark',
    'theme-kits',
    'settings-license',
];

foreach ($tabs as $tab) {
    $file = BANKAI_CORE_VIEWS_DIR . "admin/tab-{$tab}.php";
    if (!file_exists($file)) {
        echo "[FAIL] File missing: {$file}\n";
        exit(1);
    }

    ob_start();
    $state = $mock_state;
    include $file;
    $out = ob_get_clean();

    if (empty($out)) {
        echo "[FAIL] Tab {$tab} rendered empty output!\n";
        exit(1);
    }

    echo "[OK] Tab '{$tab}' rendered successfully (" . strlen($out) . " bytes).\n";
}

// Test Editor SEO view
$editor_view = BANKAI_CORE_VIEWS_DIR . "tabs/tab-seo-engine.php";
if (file_exists($editor_view)) {
    ob_start();
    $post_id = 1;
    include $editor_view;
    $out = ob_get_clean();
    if (empty($out)) {
        echo "[FAIL] Editor SEO View rendered empty output!\n";
        exit(1);
    }
    echo "[OK] Editor SEO View rendered successfully (" . strlen($out) . " bytes).\n";
}

echo "All smoke tests passed successfully!\n";
