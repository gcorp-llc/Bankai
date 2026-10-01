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
            'jalali_calendar',
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
        $keys = ['seo_engine', 'speed_cache', 'media_watermark', 'ai_studio', 'llms_txt', 'jalali_calendar'];
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


/**
 * Solar Broken outline icon helper (no Material Symbols).
 */
if (!function_exists('bankai_icon')) {
    function bankai_icon(string $name, string $class = 'solar-icon'): string
    {
        static $map = null;
        if ($map === null) {
            $map = [
                'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
                'article' => '<path d="M4 4h12a2 2 0 0 1 2 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 0 1 2-2z"/><path d="M8 8h8M8 12h6"/>',
                'travel_explore' => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
                'search' => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
                'smart_toy' => '<rect x="4" y="8" width="16" height="12" rx="3"/><path d="M12 8V5M9 12h.01M15 12h.01M9 16h6M8 4h8"/>',
                'speed' => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
                'photo_library' => '<rect x="3" y="5" width="14" height="14" rx="2"/><path d="M21 9v10a2 2 0 0 1-2 2H7"/><circle cx="9" cy="11" r="1.5"/><path d="M3 15l4-3 3 2 4-4 3 3"/>',
                'palette' => '<circle cx="13.5" cy="6.5" r="1"/><circle cx="17.5" cy="10.5" r="1"/><circle cx="8.5" cy="7.5" r="1"/><circle cx="6.5" cy="12" r="1"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.5-.7 1.5-1.5 0-.4-.1-.7-.4-1-.3-.3-.4-.6-.4-1 0-.8.7-1.5 1.5-1.5H16c3.3 0 6-2.7 6-6 0-5-4.6-9-10-9z"/>',
                'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/>',
                'close' => '<path d="M18 6L6 18M6 6l12 12"/>',
                'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
                'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
                'edit_note' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
                'delete' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
                'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/>',
                'refresh' => '<path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/>',
                'auto_awesome' => '<path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/>',
                'bolt' => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
                'play_arrow' => '<path d="M8 5v14l11-7z"/>',
                'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
                'monitoring' => '<path d="M3 12h4l2-5 4 10 2-5h6"/>',
                'hub' => '<circle cx="12" cy="12" r="2"/><path d="M12 2v4M12 18v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M2 12h4M18 12h4M4.9 19.1l2.8-2.8M16.3 7.7l2.8-2.8"/>',
                'science' => '<path d="M9 3h6M10 3v5.5L5 18a2 2 0 0 0 1.7 3h10.6a2 2 0 0 0 1.7-3L14 8.5V3"/>',
                'extension' => '<path d="M7 7V3h4v4h4v4h4v4h-4v4H7v-4H3v-4h4V7z"/>',
                'dns' => '<rect x="2" y="3" width="20" height="7" rx="2"/><rect x="2" y="14" width="20" height="7" rx="2"/><circle cx="6" cy="6.5" r="1"/><circle cx="6" cy="17.5" r="1"/>',
                'tune' => '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>',
                'analytics' => '<path d="M3 3v18h18"/><path d="M7 14l4-4 4 3 5-6"/>',
                'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
                'schedule' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
                'check_circle' => '<circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/>',
                'open_in_new' => '<path d="M14 3h7v7M10 14L21 3M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>',
                'visibility' => '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>',
                'visibility_off' => '<path d="M17.94 17.94A10.9 10.9 0 0 1 12 19c-7 0-11-7-11-7a21.8 21.8 0 0 1 5-5.9M9.9 4.2A10.9 10.9 0 0 1 12 4c7 0 11 7 11 7a21.9 21.9 0 0 1-2.2 3.2"/><path d="M1 1l22 22"/>',
                'progress_activity' => '<path d="M12 2a10 10 0 0 1 10 10"/>',
                'autorenew' => '<path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/>',
                'code' => '<path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/>',
                'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
                'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/>',
                'database' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
                'preview' => '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>',
                'terminal' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 9l3 3-3 3M12 15h5"/>',
                'data_object' => '<path d="M8 4H6a2 2 0 0 0-2 2v3a2 2 0 0 1-2 2 2 2 0 0 1 2 2v3a2 2 0 0 0 2 2h2M16 4h2a2 2 0 0 1 2 2v3a2 2 0 0 0 2 2 2 2 0 0 0-2 2v3a2 2 0 0 1-2 2h-2"/>',
                'inventory_2' => '<path d="M21 8l-9-5-9 5v8l9 5 9-5V8z"/><path d="M3.5 8.5L12 13l8.5-4.5M12 13v9"/>',
                'post_add' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15h6"/>',
                'bookmark' => '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
                'content_copy' => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
                'payments' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
                'restart_alt' => '<path d="M23 4v6h-6"/><path d="M20.5 15a9 9 0 1 1-2.1-9.4L23 10"/>',
                'play_circle' => '<circle cx="12" cy="12" r="9"/><path d="M10 8l6 4-6 4V8z"/>',
                'savings' => '<path d="M19 8a5 5 0 0 0-5-5H8a5 5 0 0 0 0 10h1"/><circle cx="16" cy="11" r="1"/>',
                'api' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V4s-1 1-4 1-5-2-8-2-4 1-4 1z"/>',
                'psychology' => '<path d="M12 2a5 5 0 0 0-5 5v1a4 4 0 0 0-2 3.5V14a3 3 0 0 0 3 3h1v3h6v-3h1a3 3 0 0 0 3-3v-2.5A4 4 0 0 0 17 8V7a5 5 0 0 0-5-5z"/>',
                'brightness_5' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
                'check' => '<path d="M20 6L9 17l-5-5"/>',
                'add' => '<path d="M12 5v14M5 12h14"/>',
                'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/>',
                'warning' => '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
            ];
        }
        $d = $map[$name] ?? $map['settings'];
        $cls = esc_attr($class);
        return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
    }
}

