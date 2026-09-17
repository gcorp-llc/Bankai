<?php
/**
 * Bankai Theme Functions and Definitions (Bankai Framework Lightweight Architecture)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

define('BANKAI_THEME_VERSION', '1.0.0');
define('BANKAI_THEME_DIR', get_template_directory());
define('BANKAI_THEME_URI', get_template_directory_uri());

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function bankai_theme_setup(): void {
    // Make theme available for translation
    load_theme_textdomain('bankai-theme', BANKAI_THEME_DIR . '/languages');

    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size(1200, 675, true);

    // Register Navigation Menus
    register_nav_menus([
        'primary' => esc_html__('منوی اصلی هدر (Primary Header Menu)', 'bankai-theme'),
        'footer'  => esc_html__('منوی فوتر (Footer Menu)', 'bankai-theme'),
        'mobile'  => esc_html__('منوی همراه / موبایل (Mobile Off-Canvas)', 'bankai-theme'),
    ]);

    // Switch default core markup for search form, comment form, and comments to output valid HTML5
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    // Custom Logo Support
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 240,
        'flex-width'  => true,
        'flex-height' => true,
    ]);

    // Gutenberg Full & Wide Align Support
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');

    // WooCommerce Support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'bankai_theme_setup');

/**
 * Enqueue scripts and styles for frontend.
 */
function bankai_theme_scripts(): void {
    // Enqueue Google Fonts
    wp_enqueue_style(
        'bankai-theme-fonts',
        'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Vazirmatn:wght@400;500;600;700;800&display=swap',
        [],
        BANKAI_THEME_VERSION
    );

    // Enqueue Main Theme Style
    wp_enqueue_style(
        'bankai-theme-main',
        BANKAI_THEME_URI . '/assets/css/main.css',
        [],
        BANKAI_THEME_VERSION
    );

    // Enqueue Theme Style.css
    wp_enqueue_style('bankai-theme-style', get_stylesheet_uri(), ['bankai-theme-main'], BANKAI_THEME_VERSION);
}
add_action('wp_enqueue_scripts', 'bankai_theme_scripts');

/**
 * Load Customizer Integration
 */
require_once BANKAI_THEME_DIR . '/inc/customizer/customizer.php';

/**
 * Load Bankai Framework Theme Admin Panel
 */
if (is_admin()) {
    require_once BANKAI_THEME_DIR . '/inc/admin/class-theme-admin.php';
}
