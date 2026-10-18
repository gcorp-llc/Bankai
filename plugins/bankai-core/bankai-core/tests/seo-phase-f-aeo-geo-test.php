<?php
/**
 * Test suite for SEO + AEO + GEO analysis and prompt generation.
 */

define('ABSPATH', __DIR__ . '/');

// Mock WordPress functions
if (!function_exists('add_action')) { function add_action($a=null, $b=null, $p=10, $n=1) { return true; } }
if (!function_exists('add_filter')) { function add_filter($a=null, $b=null, $p=10, $n=1) { return true; } }
if (!function_exists('remove_action')) { function remove_action($a=null, $b=null, $p=10) { return true; } }
if (!function_exists('home_url')) { function home_url($path = '') { return 'https://example.com' . $path; } }
if (!function_exists('bankai_is_module_active')) { function bankai_is_module_active($mod) { return true; } }
if (!function_exists('get_post')) {
    function get_post($id) {
        if ($id <= 0) return null;
        return (object)[
            'ID' => $id,
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => 'عنوان آزمایشی مقاله درباره سئو وردپرس',
            'post_name' => 'test-seo-article',
            'post_content' => '<h2>سئوی وردپرس چیست؟</h2><p>سئوی وردپرس عبارت است از مجموعه اقدامات بهینه‌سازی موتورهای جستجو برای سایت‌های وردپرسی.</p><ul><li>نکته اول</li><li>نکته دوم</li></ul><p>برای اطلاعات بیشتر به <a href="https://example.com/source">منبع معتبر</a> مراجعه کنید.</p><h2>نتیجه‌گیری</h2><p>در نهایت باید سعی کنید ساختار استاندارد را رعایت کنید.</p>',
            'post_author' => 1,
        ];
    }
}
if (!function_exists('get_the_title')) { function get_the_title($id) { return 'عنوان آزمایشی مقاله درباره سئو وردپرس'; } }
if (!function_exists('get_permalink')) { function get_permalink($id) { return 'https://example.com/test-seo-article'; } }
if (!function_exists('get_post_meta')) {
    function get_post_meta($id, $key, $single = true) {
        if ($key === '_bankai_seo_title') return 'عنوان آزمایشی مقاله درباره سئو وردپرس برای گوگل';
        if ($key === '_bankai_seo_description') return 'یک توضیح متای استاندارد و کامل درباره بهینه‌سازی سایت وردپرسی و افزایش بازدید گوگل.';
        if ($key === '_bankai_seo_focus_keyword' || $key === '_bankai_seo_keyword') return 'سئوی وردپرس';
        if ($key === '_bankai_seo_keywords') return json_encode(['موتور جستجو', 'افزایش بازدید']);
        return '';
    }
}
if (!function_exists('update_post_meta')) { function update_post_meta($id, $key, $val) { return true; } }
if (!function_exists('wp_strip_all_tags')) { function wp_strip_all_tags($text) { return strip_tags($text); } }
if (!function_exists('mb_strlen')) { function mb_strlen($str) { return strlen($str); } }
if (!function_exists('mb_substr')) { function mb_substr($str, $s, $l = null) { return substr($str, $s, $l); } }
if (!function_exists('mb_strtolower')) { function mb_strtolower($str) { return strtolower($str); } }
if (!function_exists('mb_stripos')) { function mb_stripos($h, $n) { return stripos($h, $n); } }
if (!function_exists('mb_substr_count')) { function mb_substr_count($h, $n) { return substr_count(strtolower($h), strtolower($n)); } }
if (!function_exists('get_posts')) { function get_posts($args) { return []; } }
if (!function_exists('wp_parse_url')) { function wp_parse_url($url, $component = -1) { return parse_url($url, $component); } }
if (!function_exists('sanitize_title')) { function sanitize_title($title) { return strtolower(str_replace(' ', '-', $title)); } }
if (!function_exists('sanitize_text_field')) { function sanitize_text_field($str) { return trim(strip_tags((string)$str)); } }
if (!function_exists('wp_unslash')) { function wp_unslash($str) { return $str; } }
if (!function_exists('esc_url_raw')) { function esc_url_raw($str) { return $str; } }

require_once __DIR__ . '/../inc/modules/class-seo-engine.php';

function assert_true($cond, $msg) {
    if (!$cond) {
        echo "FAIL: {$msg}\n";
        exit(1);
    }
    echo "PASS  {$msg}\n";
}

$engine = Bankai_SEO_Engine::instance();
$res = $engine->analyze(1);

assert_true(isset($res['score']), 'Overall score exists in analyze result');
assert_true(isset($res['seo_score']), 'SEO sub-score exists');
assert_true(isset($res['aeo_score']), 'AEO sub-score exists');
assert_true(isset($res['geo_score']), 'GEO sub-score exists');
assert_true($res['aeo_score'] > 0, 'AEO score is positive for test article with question heading & definition');
assert_true($res['geo_score'] > 0, 'GEO score is positive for test article with source citation & summary');

// Check AEO question heading check item
$aeo_q_found = false;
foreach ($res['checks'] as $check) {
    if ($check['key'] === 'aeo_question_headings' && $check['passed']) {
        $aeo_q_found = true;
        break;
    }
}
assert_true($aeo_q_found, 'aeo_question_headings check passed');

// Check AEO direct definition check item
$aeo_def_found = false;
foreach ($res['checks'] as $check) {
    if ($check['key'] === 'aeo_direct_definition' && $check['passed']) {
        $aeo_def_found = true;
        break;
    }
}
assert_true($aeo_def_found, 'aeo_direct_definition check passed');

// Check GEO summary check item
$geo_sum_found = false;
foreach ($res['checks'] as $check) {
    if ($check['key'] === 'geo_summary_conclusion' && $check['passed']) {
        $geo_sum_found = true;
        break;
    }
}
assert_true($geo_sum_found, 'geo_summary_conclusion check passed');

echo "\nAll Phase F (SEO + AEO + GEO) tests passed successfully!\n";
