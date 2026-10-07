<?php
/**
 * Performance defaults — Hello/Astra-level lightness
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * Remove emoji scripts/styles on front.
 */
function bankai_disable_emojis(): void {
    if (is_admin()) {
        return;
    }
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    add_filter('emoji_svg_url', '__return_false');
}
add_action('init', 'bankai_disable_emojis');

/**
 * Clean wp_head noise.
 */
function bankai_clean_head(): void {
    if (is_admin()) {
        return;
    }
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
}
add_action('init', 'bankai_clean_head');

/**
 * Disable embeds on front if not needed.
 */
function bankai_dequeue_embed(): void {
    if (is_admin()) {
        return;
    }
    wp_deregister_script('wp-embed');
}
add_action('wp_footer', 'bankai_dequeue_embed');

/**
 * Remove dashicons for guests.
 */
function bankai_dequeue_dashicons(): void {
    if (!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
    }
}
add_action('wp_enqueue_scripts', 'bankai_dequeue_dashicons', 100);

/**
 * Lazy-load images by default (WP 5.5+ already does; ensure).
 *
 * @param array $attr Attributes.
 * @return array
 */
function bankai_image_lazy(array $attr): array {
    if (empty($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    if (empty($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'bankai_image_lazy', 10, 1);

/**
 * Limit heartbeats on front.
 */
function bankai_front_heartbeat(): void {
    if (!is_admin()) {
        wp_deregister_script('heartbeat');
    }
}
add_action('init', 'bankai_front_heartbeat', 1);
