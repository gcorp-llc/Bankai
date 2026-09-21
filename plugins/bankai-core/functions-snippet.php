<?php
/**
 * Recommended helpers (merge into functions.php if not already present).
 * The existing functions.php is mostly fine; only small hardening notes below.
 */

defined('ABSPATH') || exit;

/**
 * Get Bankai Asset URL Helper
 */
if (!function_exists('bankai_asset_url')) {
    function bankai_asset_url(string $path = ''): string
    {
        $base = defined('BANKAI_CORE_URL')
            ? BANKAI_CORE_URL
            : trailingslashit(content_url('plugins/bankai-core'));

        return $base . 'assets/' . ltrim($path, '/');
    }
}

/**
 * Get Bankai Core Plugin Option
 */
if (!function_exists('bankai_get_option')) {
    function bankai_get_option(string $key = '', $default = false)
    {
        $settings = get_option('bankai_core_settings', []);

        if ($key === '') {
            return is_array($settings) ? $settings : [];
        }

        return is_array($settings) && array_key_exists($key, $settings)
            ? $settings[$key]
            : $default;
    }
}

/**
 * Update Bankai Core Option Key
 */
if (!function_exists('bankai_update_option')) {
    function bankai_update_option(string $key, $value): bool
    {
        $settings = get_option('bankai_core_settings', []);
        if (!is_array($settings)) {
            $settings = [];
        }
        $settings[$key] = $value;
        return update_option('bankai_core_settings', $settings);
    }
}

/**
 * Render View Partial Utility
 */
if (!function_exists('bankai_render_view')) {
    function bankai_render_view(string $view_path, array $args = []): void
    {
        $base = defined('BANKAI_CORE_VIEWS_DIR')
            ? BANKAI_CORE_VIEWS_DIR
            : (defined('BANKAI_CORE_DIR') ? BANKAI_CORE_DIR . 'views/' : '');

        $file = $base . ltrim($view_path, '/');

        if ($file && file_exists($file)) {
            // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
            extract($args, EXTR_SKIP);
            include $file;
        }
    }
}

/**
 * Check if a Bankai module is active.
 */
if (!function_exists('bankai_is_module_active')) {
    function bankai_is_module_active(string $module_key): bool
    {
        $active = bankai_get_option('active_modules', []);

        if (!is_array($active)) {
            return true;
        }

        return array_key_exists($module_key, $active)
            ? (bool) $active[$module_key]
            : true;
    }
}
