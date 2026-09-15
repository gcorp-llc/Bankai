<?php
defined('ABSPATH') || exit;

/**
 * Get Bankai Core Plugin Option
 */
function bankai_get_option(string $key = '', $default = false) {
    $settings = get_option('bankai_core_settings', []);
    if (empty($key)) {
        return $settings;
    }
    return $settings[$key] ?? $default;
}

/**
 * Update Bankai Core Option Key
 */
function bankai_update_option(string $key, $value): bool {
    $settings = get_option('bankai_core_settings', []);
    $settings[$key] = $value;
    return update_option('bankai_core_settings', $settings);
}

/**
 * Get Asset URL Helper
 */
function bankai_asset_url(string $path): string {
    return BANKAI_CORE_URL . 'assets/' . ltrim($path, '/');
}

/**
 * Render View Partial Utility
 */
function bankai_render_view(string $view_path, array $args = []): void {
    $file = BANKAI_CORE_VIEWS_DIR . ltrim($view_path, '/');
    if (file_exists($file)) {
        extract($args);
        include $file;
    }
}