<?php
/**
 * Elementor compatibility
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * Register Elementor theme locations (header/footer/single/archive).
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Manager.
 */
function bankai_elementor_locations($manager): void {
    if (! is_object($manager) || ! method_exists($manager, 'register_all_core_location')) {
        return;
    }
    $manager->register_all_core_location();
}
add_action('elementor/theme/register_locations', 'bankai_elementor_locations');

/**
 * Add Elementor support features after theme setup.
 */
function bankai_elementor_support(): void {
    // Default page template width for Elementor canvas-like pages.
    add_theme_support('elementor');
}
add_action('after_setup_theme', 'bankai_elementor_support', 20);

/**
 * Disable default theme header/footer when Elementor Theme Builder handles them.
 *
 * @return bool
 */
function bankai_elementor_header_footer_disabled(): bool {
    if (! did_action('elementor/loaded')) {
        return false;
    }
    if (! class_exists('\ElementorPro\Plugin')) {
        return false;
    }
    // Theme Builder conditions are complex; rely on templates canvas/header-footer.
    return false;
}

/**
 * Body class when editing with Elementor.
 *
 * @param array $classes Classes.
 * @return array
 */
function bankai_elementor_body_class(array $classes): array {
    if (isset($_GET['elementor-preview'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $classes[] = 'bankai-elementor-preview';
    }
    return $classes;
}
add_filter('body_class', 'bankai_elementor_body_class');
