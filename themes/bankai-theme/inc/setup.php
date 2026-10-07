<?php
/**
 * Theme supports, menus, sidebars
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * After setup theme.
 */
function bankai_theme_setup(): void {
    load_theme_textdomain('bankai-theme', BANKAI_THEME_DIR . '/languages');

    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size(1200, 675, true);
    add_image_size('bankai-card', 640, 400, true);

    register_nav_menus([
        'primary' => __('منوی اصلی', 'bankai-theme'),
        'footer'  => __('منوی فوتر', 'bankai-theme'),
        'mobile'  => __('منوی موبایل', 'bankai-theme'),
    ]);

    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ]);

    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    add_theme_support('custom-background', [
        'default-color' => 'ffffff',
    ]);

    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_editor_style('assets/css/main.css');

    // WooCommerce
    add_theme_support('woocommerce', [
        'thumbnail_image_width' => 400,
        'single_image_width'    => 600,
    ]);
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Content width (legacy)
    $GLOBALS['content_width'] = absint(bankai_theme_mod('container_width', 1280));
}
add_action('after_setup_theme', 'bankai_theme_setup');

/**
 * Register widget areas.
 */
function bankai_theme_widgets_init(): void {
    register_sidebar([
        'name'          => __('سایدبار اصلی', 'bankai-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('ویجت‌های کنار محتوا', 'bankai-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    for ($i = 1; $i <= 4; $i++) {
        register_sidebar([
            'name'          => sprintf(__('فوتر ستون %d', 'bankai-theme'), $i),
            'id'            => 'footer-' . $i,
            'description'   => __('ویجت فوتر', 'bankai-theme'),
            'before_widget' => '<section id="%1$s" class="widget footer-widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ]);
    }
}
add_action('widgets_init', 'bankai_theme_widgets_init');

/**
 * Body classes for layout.
 *
 * @param array $classes Classes.
 * @return array
 */
function bankai_body_classes(array $classes): array {
    $classes[] = is_rtl() ? 'bankai-rtl' : 'bankai-ltr';
    $classes[] = 'bankai-layout-' . sanitize_html_class(bankai_theme_mod('content_layout', 'content'));
    if (bankai_show_sidebar()) {
        $classes[] = 'has-sidebar';
    }
    if (bankai_theme_mod('sticky_header', true)) {
        $classes[] = 'has-sticky-header';
    }
    return $classes;
}
add_filter('body_class', 'bankai_body_classes');


/**
 * Body classes for layout.
 */
function bankai_theme_body_classes(array $classes): array {
    $layout = (string) bankai_theme_mod('site_layout', 'wide');
    $classes[] = 'bankai-layout-' . sanitize_html_class($layout);
    $classes[] = 'bankai-container-' . sanitize_html_class((string) bankai_theme_mod('container_layout', 'boxed'));
    $classes[] = 'bankai-container-style-' . sanitize_html_class((string) bankai_theme_mod('container_style', 'default'));
    return $classes;
}
add_filter('body_class', 'bankai_theme_body_classes');

/**
 * Force text direction from طرح کلی when set.
 */
function bankai_theme_language_attributes(string $output): string {
    $dir = bankai_theme_mod('text_direction', '');
    if ($dir === 'rtl' || $dir === 'ltr') {
        $output = preg_replace('/dir="[^"]*"/', 'dir="' . esc_attr($dir) . '"', $output);
        if (strpos($output, 'dir=') === false) {
            $output .= ' dir="' . esc_attr($dir) . '"';
        }
    }
    return $output;
}
add_filter('language_attributes', 'bankai_theme_language_attributes');


add_filter('body_class', static function (array $classes): array {
    if (function_exists('bankai_theme_get_design')) {
        $d = bankai_theme_get_design();
        $style = sanitize_key($d['style'] ?? 'premium-minimalism');
        $classes[] = 'bankai-style-' . $style;
    }
    return $classes;
});


/**
 * Force 404 template for Customizer preview URL.
 */
add_action('template_redirect', static function () {
    if (! is_404()) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, 'bankai-preview-404-page') !== false) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_query_template('404');
            exit;
        }
    }
}, 0);
