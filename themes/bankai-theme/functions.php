<?php
/**
 * Bankai Theme — bootstrap
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

define('BANKAI_THEME_VERSION', '1.10.4');
define('BANKAI_THEME_DIR', get_template_directory());
define('BANKAI_THEME_URI', get_template_directory_uri());

$bankai_theme_includes = [
    '/inc/helpers.php',
    '/inc/icons.php',
    '/inc/setup.php',
    '/inc/enqueue.php',
    '/inc/performance.php',
    '/inc/customizer/customizer.php',
    '/inc/elementor.php',
    '/inc/blocks.php',
    '/inc/demo-import.php',
    '/inc/theme-generator.php',
    '/inc/header-builder.php',
    '/inc/login.php',
    '/inc/seo-ai.php',
];

foreach ($bankai_theme_includes as $file) {
    $path = BANKAI_THEME_DIR . $file;
    if (is_readable($path)) {
        require_once $path;
    }
}

if (is_admin()) {
    $admin = BANKAI_THEME_DIR . '/inc/admin/class-theme-admin.php';
    if (is_readable($admin)) {
        require_once $admin;
        Bankai_Theme_Admin::instance();
    }
}
