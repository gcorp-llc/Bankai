<?php

/**
 * Bankai Core Functions and Definitions
 *
 * @package Bankai_core
 */

defined('ABSPATH') || exit;

defined('BANKAI_CORE_VERSION') || define('BANKAI_CORE_VERSION', '1.0.0');

/**
 * Physical filesystem path.
 *
 * This may resolve to the real target path when the plugin is symlinked.
 * It is ONLY for filesystem operations, never for URLs.
 */
defined('BANKAI_CORE_DIR') || define(
    'BANKAI_CORE_DIR',
    plugin_dir_path(__FILE__)
);

/**
 * Public URL of the plugin.
 *
 * IMPORTANT:
 * Do not use plugin_dir_url(__FILE__) here because the plugin may be
 * symlinked and __FILE__ can resolve to the real filesystem path.
 *
 * The public WordPress plugin URL must always be:
 *
 * /wp-content/plugins/bankai-core/
 */
if (!defined('BANKAI_CORE_URL')) {
    define(
        'BANKAI_CORE_URL',
        trailingslashit(content_url('plugins/bankai-core'))
    );
}

defined('BANKAI_CORE_VIEWS_DIR') || define(
    'BANKAI_CORE_VIEWS_DIR',
    BANKAI_CORE_DIR . 'views/'
);

/**
 * Get Bankai Asset URL Helper
 */
if (!function_exists('bankai_asset_url')) {
    function bankai_asset_url(string $path = ''): string
    {
        return BANKAI_CORE_URL . 'assets/' . ltrim($path, '/');
    }
}

/**
 * Get Bankai Core Plugin Option
 */
function bankai_get_option(string $key = '', $default = false)
{
    $settings = get_option('bankai_core_settings', []);

    if (empty($key)) {
        return $settings;
    }

    return $settings[$key] ?? $default;
}

/**
 * Update Bankai Core Option Key
 */
function bankai_update_option(string $key, $value): bool
{
    $settings = get_option('bankai_core_settings', []);

    $settings[$key] = $value;

    return update_option('bankai_core_settings', $settings);
}

/**
 * Render View Partial Utility
 */
function bankai_render_view(string $view_path, array $args = []): void
{
    $file = BANKAI_CORE_VIEWS_DIR . ltrim($view_path, '/');

    if (file_exists($file)) {
        extract($args);
        include $file;
    }
}

/**
 * Enqueue Core Admin Scripts and Styles
 */
function bankai_core_admin_scripts($hook): void
{
    if (strpos($hook, 'bankai') === false) {
        return;
    }

    wp_enqueue_style(
        'bankai-admin-style',
        bankai_asset_url('css/bankai-admin.css'),
        [],
        BANKAI_CORE_VERSION
    );

    wp_enqueue_script(
        'bankai-htmx',
        bankai_asset_url('js/htmx.min.js'),
        [],
        '1.9.10',
        true
    );

    wp_enqueue_script(
        'bankai-alpine',
        bankai_asset_url('js/alpine.min.js'),
        [],
        '3.13.5',
        true
    );

    wp_script_add_data(
        'bankai-alpine',
        'strategy',
        'defer'
    );

    wp_enqueue_script(
        'bankai-admin-script',
        bankai_asset_url('js/bankai-admin.js'),
        ['bankai-alpine', 'bankai-htmx'],
        BANKAI_CORE_VERSION,
        true
    );
}

add_action(
    'admin_enqueue_scripts',
    'bankai_core_admin_scripts'
);
