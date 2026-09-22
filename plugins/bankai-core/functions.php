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
 * ONLY for filesystem operations, never for URLs.
 */
defined('BANKAI_CORE_DIR') || define(
    'BANKAI_CORE_DIR',
    plugin_dir_path(__FILE__)
);

/**
 * Public URL of the plugin.
 * Do not use plugin_dir_url(__FILE__) — may resolve wrong under symlink.
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
        $base = defined('BANKAI_CORE_URL')
            ? BANKAI_CORE_URL
            : trailingslashit(content_url('plugins/bankai-core'));

        return $base . 'assets/' . ltrim($path, '/');
    }
}

/**
 * Get Bankai Core Plugin Option (from bankai_core_settings array).
 */
if (!function_exists('bankai_get_option')) {
    function bankai_get_option(string $key = '', $default = false)
    {
        $settings = get_option('bankai_core_settings', []);

        if (!is_array($settings)) {
            $settings = [];
        }

        if ($key === '') {
            return $settings;
        }

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }
}

/**
 * Update a single key inside bankai_core_settings.
 */
if (!function_exists('bankai_update_option')) {
    function bankai_update_option(string $key, $value): bool
    {
        $settings = get_option('bankai_core_settings', []);
        if (!is_array($settings)) {
            $settings = [];
        }
        $settings[$key] = $value;
        return update_option('bankai_core_settings', $settings, false);
    }
}

/**
 * Merge multiple keys into bankai_core_settings and save once.
 *
 * @param array<string,mixed> $pairs
 */
if (!function_exists('bankai_save_settings')) {
    function bankai_save_settings(array $pairs): bool
    {
        $settings = get_option('bankai_core_settings', []);
        if (!is_array($settings)) {
            $settings = [];
        }
        foreach ($pairs as $key => $value) {
            $settings[sanitize_key((string) $key)] = $value;
        }
        return update_option('bankai_core_settings', $settings, false);
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
 * Known core module keys.
 *
 * @return list<string>
 */
if (!function_exists('bankai_core_module_keys')) {
    function bankai_core_module_keys(): array
    {
        return [
            'seo_engine',
            'speed_cache',
            'media_watermark',
            'ai_studio',
            'llms_txt',
            'theme_kits',
            'settings_license',
        ];
    }
}

/**
 * Check if a Bankai core module is active.
 * Default: true when key is missing (first install / migration).
 */
if (!function_exists('bankai_is_module_active')) {
    function bankai_is_module_active(string $module_key): bool
    {
        $active = bankai_get_option('active_modules', []);

        if (!is_array($active) || $active === []) {
            return true;
        }

        if (!array_key_exists($module_key, $active)) {
            return true;
        }

        return (bool) $active[$module_key];
    }
}

/**
 * Enable / disable a core module and persist.
 */
if (!function_exists('bankai_set_module_active')) {
    function bankai_set_module_active(string $module_key, bool $enabled): bool
    {
        $module_key = sanitize_key($module_key);
        if (!in_array($module_key, bankai_core_module_keys(), true)) {
            return false;
        }

        $active = bankai_get_option('active_modules', []);
        if (!is_array($active)) {
            $active = [];
        }
        $active[$module_key] = $enabled;

        return bankai_update_option('active_modules', $active);
    }
}

/**
 * Count active core modules (excluding settings_license).
 */
if (!function_exists('bankai_count_active_modules')) {
    function bankai_count_active_modules(): int
    {
        $keys = ['seo_engine', 'speed_cache', 'media_watermark', 'ai_studio', 'llms_txt', 'theme_kits'];
        $n    = 0;
        foreach ($keys as $k) {
            if (bankai_is_module_active($k)) {
                $n++;
            }
        }
        return $n;
    }
}

/**
 * SEO score color by range (shared helper).
 */
if (!function_exists('bankai_seo_score_color')) {
    function bankai_seo_score_color(int $score): string
    {
        if ($score < 10) {
            return '#B8BCC2';
        }
        if ($score < 20) {
            return '#E8D3A2';
        }
        if ($score < 40) {
            return '#A6122D';
        }
        if ($score < 60) {
            return '#E0A030';
        }
        if ($score < 80) {
            return '#93C572';
        }
        return '#1E7F5C';
    }
}
