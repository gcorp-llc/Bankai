<?php
/**
 * بازیابی اضطراری ورود
 * اگر قفل شدید: در مرورگر باز کنید:
 *   https://yoursite.com/wp-login.php?bankai_unlock=1
 * یا موقتاً این فایل را از functions.php require کنید و theme_mod را پاک کنید.
 */
defined('ABSPATH') || exit;

add_action('init', static function () {
    if (! isset($_GET['bankai_unlock']) || (string) $_GET['bankai_unlock'] !== '1') {
        return;
    }
    // فقط روی wp-login
    $script = isset($_SERVER['SCRIPT_NAME']) ? basename((string) $_SERVER['SCRIPT_NAME']) : '';
    if ($script !== 'wp-login.php' && (empty($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'wp-login.php')) {
        return;
    }
    // اگر ادمین لاگین کرد و ?bankai_reset_login=1 زد، تنظیمات مسدودساز پاک شود
    if (isset($_GET['bankai_reset_login']) && is_user_logged_in() && current_user_can('manage_options')) {
        remove_theme_mod('login_block_default_urls');
        remove_theme_mod('login_custom_slug');
        remove_theme_mod('register_custom_slug');
        wp_safe_redirect(admin_url());
        exit;
    }
}, 0);
