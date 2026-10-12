<?php
/**
 * Test suite for SEO / AEO / GEO Engine scoring & AI Studio prompts.
 */

define('ABSPATH', __DIR__ . '/../../../');
define('BANKAI_CORE_PATH', __DIR__ . '/../');

class WP_Error {
    public $errors = array();
    public function __construct($code = '', $message = '', $data = '') {
        $this->errors[$code][] = $message;
    }
}

function add_action() {}
function remove_action() {}
function add_filter() {}
function sanitize_text_field($str) { return trim(strip_tags($str)); }
function sanitize_textarea_field($str) { return trim($str); }
function esc_html($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function esc_attr($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function wp_unslash($str) { return $str; }
function wp_strip_all_tags($str) { return strip_tags($str); }
function wp_parse_url($url, $component = -1) { return parse_url($url, $component); }
function home_url($path = '') { return 'https://example.com' . $path; }
function get_option($key, $default = false) { return $default; }
function get_posts($args = []) { return []; }
function get_post($id) {
    return (object)[
        'ID' => 1,
        'post_type' => 'post',
        'post_title' => 'راهنمای جامع سئو و بهینه‌سازی موتورهای جستجو',
        'post_content' => '<h2>نکات کلیدی این مقاله</h2><ul><li><strong>پاسخ مستقیم:</strong> سئو بهینه‌سازی سایت برای موتورهای جستجو است.</li><li>توضیح کامل تکنیک‌ها و استراتژی‌ها.</li></ul><h2>سوالات متداول</h2><h3>سئو چیست و چرا اهمیت دارد؟</h3><p>سئو مجموعه اقداماتی است که کیفیت و کمیت ترافیک سایت را بهبود می‌بخشد.</p>',
        'post_name' => 'seo-guide',
    ];
}
function get_the_title($id) { return 'راهنمای جامع سئو و بهینه‌سازی موتورهای جستجو'; }
function get_post_meta($id, $key, $single = true) {
    if ($key === '_bankai_focus_keyword') return 'سئو';
    if ($key === '_bankai_seo_title') return 'راهنمای جامع سئو و بهینه‌سازی';
    if ($key === '_bankai_meta_description') return 'آموزش کامل سئو و بهینه‌سازی سایت برای موتورهای جستجو همراه با نکات کاربردی و AEO';
    return '';
}
function get_permalink($id) { return 'https://example.com/seo-guide'; }

require_once BANKAI_CORE_PATH . 'inc/modules/class-seo-text-normalizer.php';
require_once BANKAI_CORE_PATH . 'inc/modules/class-seo-link-ranker.php';
require_once BANKAI_CORE_PATH . 'inc/modules/class-seo-engine.php';
require_once BANKAI_CORE_PATH . 'inc/modules/class-ai-studio.php';

$seoEngine = Bankai_SEO_Engine::instance();

$analysis = $seoEngine->analyze(1);

assert(isset($analysis['score']), 'Overall score must be present');
assert(isset($analysis['seo_score']), 'SEO score missing');
assert(isset($analysis['aeo_score']), 'AEO score missing');
assert(isset($analysis['geo_score']), 'GEO score missing');

echo "[PASS] SEO / AEO / GEO Engine analysis test passed successfully.\n";
echo "Overall Score: " . $analysis['score'] . "\n";
echo "SEO Score: " . $analysis['seo_score'] . "\n";
echo "AEO Score: " . $analysis['aeo_score'] . "\n";
echo "GEO Score: " . $analysis['geo_score'] . "\n";
