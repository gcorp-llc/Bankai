<?php
/**
 * Test Suite: Bankai Speed & Cache Module
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../../');
}

function run_speed_cache_tests()
{
    echo "=== Running Bankai Speed & Cache Unit & Integration Tests ===\n";
    $passed = 0;
    $failed = 0;

    function assert_equals($expected, $actual, $msg)
    {
        global $passed, $failed;
        if ($expected === $actual) {
            echo "[PASS] $msg\n";
            $passed++;
        } else {
            echo "[FAIL] $msg\n";
            echo "  Expected: " . var_export($expected, true) . "\n";
            echo "  Actual:   " . var_export($actual, true) . "\n";
            $failed++;
        }
    }

    function assert_true($condition, $msg)
    {
        assert_equals(true, (bool) $condition, $msg);
    }

    // 1. Minify CSS Test
    $raw_css = "/* Comment */ body { color: #333; margin: 0px; } calc(100% - 20px);";
    $min_css = Bankai_Speed_Cache::minify_css_string($raw_css);
    assert_true(strpos($min_css, '/* Comment */') === false, 'CSS minify removes comments');
    assert_true(strpos($min_css, 'color:#333') !== false, 'CSS minify removes spaces around colons');
    assert_true(strpos($min_css, 'calc(100% - 20px)') !== false, 'CSS minify preserves spaces in calc()');

    // 2. Minify JS Test
    $raw_js = "
    // Single line comment
    function test() {
        var url = 'https://example.com/path';
        var regex = /https:\\/\\/test.com\\/g;
        return url;
    }
    /* Multi
       line comment */
    ";
    $min_js = Bankai_Speed_Cache::minify_js_string($raw_js);
    assert_true(strpos($min_js, '// Single line comment') === false, 'JS minify removes single-line comments');
    assert_true(strpos($min_js, '/* Multi') === false, 'JS minify removes multi-line comments');
    assert_true(strpos($min_js, 'https://example.com/path') !== false, 'JS minify preserves URL strings with slashes');

    // 3. Font-display override syntax check
    ob_start();
    Bankai_Speed_Cache::get_instance()->print_font_display_override();
    $font_css = ob_get_clean();
    assert_true(strpos($font_css, '@font-face{font-display:swap}') !== false, 'Font-display override uses valid syntax without !important');

    // 4. Targeted URL Cache Invalidation
    $inst = Bankai_Speed_Cache::get_instance();
    $cache_dir = Bankai_Speed_Cache::page_cache_dir();
    $dummy_url = 'https://example.com/test-page';
    $parsed = wp_parse_url($dummy_url);
    $key = md5('https://example.com/test-page');
    $sub = substr($key, 0, 2);
    $target_dir = $cache_dir . '/' . $sub;
    if (!is_dir($target_dir)) {
        wp_mkdir_p($target_dir);
    }
    $dummy_file = $target_dir . '/' . $key . '.html';
    file_put_contents($dummy_file, '<html><body>Test</body></html>');

    assert_true(file_exists($dummy_file), 'Dummy page cache file created');
    $inst->page_cache_purge_url($dummy_url);
    assert_true(!file_exists($dummy_file), 'Targeted page_cache_purge_url removes specific cached file');

    echo "\nSummary: $passed Passed, $failed Failed.\n";
    if ($failed > 0) {
        exit(1);
    }
}

if (file_exists(__DIR__ . '/../inc/modules/class-speed-cache.php')) {
    // Mock minimal WP functions if isolated
    if (!function_exists('add_action')) { function add_action($hook, $callback, $priority = 10, $args = 1) {} }
    if (!function_exists('add_filter')) { function add_filter($hook, $callback, $priority = 10, $args = 1) {} }
    if (!function_exists('is_admin')) { function is_admin() { return false; } }
    if (!function_exists('wp_mkdir_p')) { function wp_mkdir_p($target) { @mkdir($target, 0755, true); return is_dir($target); } }
    if (!function_exists('wp_parse_url')) { function wp_parse_url($url) { return parse_url($url); } }
    if (!defined('WP_CONTENT_DIR')) { define('WP_CONTENT_DIR', sys_get_temp_dir() . '/bankai_tests'); }

    require_once __DIR__ . '/../inc/modules/class-speed-cache.php';
    run_speed_cache_tests();
}
