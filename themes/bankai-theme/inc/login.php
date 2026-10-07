<?php
/**
 * Login / Register — layout, design style, branding, custom URLs
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

$__bankai_login_emergency = __DIR__ . '/login-emergency.php';
if (is_readable($__bankai_login_emergency)) {
    require_once $__bankai_login_emergency;
}

/* ——— Helpers ——— */
function bankai_login_mod(string $key, $default = '') {
    return get_theme_mod($key, $default);
}

function bankai_login_layout(): string {
    $v = (string) bankai_login_mod('login_layout', 'center');
    return in_array($v, ['center', 'right', 'left', 'split'], true) ? $v : 'center';
}

function bankai_login_design_style(): string {
    $v = sanitize_key((string) bankai_login_mod('login_design_style', 'premium-minimalism'));
    return $v !== '' ? $v : 'premium-minimalism';
}

function bankai_login_logo_url(): string {
    $id = (int) bankai_login_mod('login_logo_id', 0);
    if ($id) {
        $src = wp_get_attachment_image_url($id, 'medium');
        if ($src) {
            return $src;
        }
    }
    if (has_custom_logo()) {
        $cid = (int) get_theme_mod('custom_logo');
        $src = $cid ? wp_get_attachment_image_url($cid, 'medium') : false;
        if ($src) {
            return $src;
        }
    }
    if (is_readable(BANKAI_THEME_DIR . '/assets/images/logo.jpg')) {
        return BANKAI_THEME_URI . '/assets/images/logo.jpg';
    }
    return 'data:image/svg+xml,' . rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 40"><rect width="120" height="40" rx="10" fill="#1a1a1a"/><text x="60" y="26" text-anchor="middle" font-family="system-ui" font-size="14" font-weight="700" fill="#fff">Bankai</text></svg>'
    );
}

function bankai_login_bg_url(): string {
    $id = (int) bankai_login_mod('login_bg_id', 0);
    if ($id) {
        $src = wp_get_attachment_image_url($id, 'full');
        if ($src) {
            return $src;
        }
    }
    if (is_readable(BANKAI_THEME_DIR . '/assets/images/login-bg.jpg')) {
        return BANKAI_THEME_URI . '/assets/images/login-bg.jpg';
    }
    return '';
}

function bankai_login_title_text(): string {
    $t = (string) bankai_login_mod('login_title', '');
    return $t !== '' ? $t : __('ورود به پنل', 'bankai-theme');
}

function bankai_login_intro_title(): string {
    $v = (string) bankai_login_mod('login_intro_title', '');
    return $v !== '' ? $v : __('خوش آمدید', 'bankai-theme');
}

function bankai_login_intro_text(): string {
    $v = (string) bankai_login_mod('login_intro_text', '');
    return $v !== '' ? $v : __('به پنل مدیریت خوش آمدید. وارد شوید یا حساب جدید بسازید.', 'bankai-theme');
}

function bankai_login_register_title_text(): string {
    $t = (string) bankai_login_mod('login_register_title', '');
    return $t !== '' ? $t : __('ثبت‌نام', 'bankai-theme');
}

function bankai_login_custom_slug(string $which = 'login'): string {
    $key = $which === 'register' ? 'register_custom_slug' : 'login_custom_slug';
    $slug = sanitize_title((string) bankai_login_mod($key, ''));
    return $slug;
}

/* ——— Assets on wp-login ——— */
add_filter('login_display_language_dropdown', '__return_false');
add_action('login_head', static function () {
    echo '<style>.language-switcher,.login .language-switcher{display:none!important;}</style>';
}, 1);

function bankai_login_assets(): void {
    $ver = defined('BANKAI_THEME_VERSION') ? BANKAI_THEME_VERSION : '1.0';
    $logo_url = bankai_login_logo_url();
    $bg = bankai_login_bg_url();
    $layout = bankai_login_layout();
    $style = bankai_login_design_style();
    $title = bankai_login_title_text();
    $reg_title = bankai_login_register_title_text();

    $theme_id = sanitize_key((string) bankai_login_mod('login_theme', 'light'));
    $colors = [
        'primary' => '#570DF8',
        'secondary' => '#F000B8',
        'accent' => '#37CDBE',
        'neutral' => '#3D4451',
        'base' => '#FFFFFF',
        'base_content' => '#1F2937',
    ];
    if (function_exists('bankai_theme_presets')) {
        $presets = bankai_theme_presets();
        if (isset($presets[$theme_id]['colors']) && is_array($presets[$theme_id]['colors'])) {
            $colors = array_merge($colors, $presets[$theme_id]['colors']);
        }
    }
    $accent = $colors['primary'];
    $scheme = 'light';
    if (function_exists('bankai_theme_presets')) {
        $presets = bankai_theme_presets();
        if (! empty($presets[$theme_id]['scheme'])) {
            $scheme = $presets[$theme_id]['scheme'] === 'dark' ? 'dark' : 'light';
        }
    }

    wp_enqueue_style('bankai-login', BANKAI_THEME_URI . '/assets/css/login.css', [], $ver);

    $css = sprintf(
        ':root{'
        . '--bankai-login-accent:%1$s;'
        . '--bankai-login-secondary:%2$s;'
        . '--bankai-login-base:%3$s;'
        . '--bankai-login-text:%4$s;'
        . '--bankai-login-neutral:%5$s;'
        . '--bankai-login-logo:url("%6$s");'
        . '%7$s'
        . '}'
        . 'body.login h1 a{background-image:var(--bankai-login-logo)!important;width:160px;height:52px;background-size:contain!important;background-position:center!important;}'
        . 'body.login #loginform::before,body.login #registerform::before,body.login #lostpasswordform::before{content:none!important;display:none!important;}'
        . '.language-switcher{display:none!important;}',
        esc_attr($colors['primary']),
        esc_attr($colors['secondary'] ?? $colors['primary']),
        esc_attr($colors['base'] ?? '#fff'),
        esc_attr($colors['base_content'] ?? '#1a1a1a'),
        esc_attr($colors['neutral'] ?? '#3D4451'),
        esc_url($logo_url),
        $bg !== '' ? '--bankai-login-bg:url("' . esc_url($bg) . '");' : ''
    );
    wp_add_inline_style('bankai-login', $css);

    // HTML titles instead of CSS content (fixes garbled title bug)
    add_filter('login_message', static function ($message) use ($title, $reg_title) {
        $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';
        if ($action === 'register') {
            $heading = $reg_title;
        } elseif ($action === 'lostpassword' || $action === 'retrievepassword') {
            $heading = __('بازیابی رمز عبور', 'bankai-theme');
        } else {
            $heading = $title;
        }
        $html = '<div class="bankai-login-form-title">' . esc_html($heading) . '</div>';
        return $html . $message;
    });

    // Intro panel for split / side layouts
    add_action('login_header', static function () {
        $layout = bankai_login_layout();
        if (! in_array($layout, ['split', 'left', 'right'], true)) {
            return;
        }
        echo '<div class="bankai-login-split-bg" aria-hidden="true"></div>';
        echo '<aside class="bankai-login-intro">';
        echo '<div class="bankai-login-intro__card">';
        echo '<h2 class="bankai-login-intro__title">' . esc_html(bankai_login_intro_title()) . '</h2>';
        echo '<p class="bankai-login-intro__text">' . esc_html(bankai_login_intro_text()) . '</p>';
        echo '</div></aside>';
    });

    add_filter('login_body_class', static function ($classes) use ($layout, $style, $theme_id, $scheme) {
        $classes[] = 'bankai-login-layout--' . sanitize_html_class($layout);
        $classes[] = 'bankai-login-style--' . sanitize_html_class($style);
        $classes[] = 'bankai-style-' . sanitize_html_class($style);
        $classes[] = 'bankai-login-theme--' . sanitize_html_class($theme_id);
        $classes[] = 'bankai-login-scheme--' . sanitize_html_class($scheme);
        return $classes;
    });
}
add_action('login_enqueue_scripts', 'bankai_login_assets');

add_action('login_footer', static function () {
    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
    if ($action !== 'register') {
        return;
    }
    ?>
    <style>
      body.login-action-register #registerform > p:has(#user_login) { display: none !important; }
    </style>
    <script>
    (function(){
      var email = document.getElementById('user_email');
      var login = document.getElementById('user_login');
      if (!email || !login) return;
      function sync(){
        var v = (email.value || '').trim();
        if (v.indexOf('@') > 0) {
          login.value = v.split('@')[0].replace(/[^a-zA-Z0-9._-]/g, '') || v;
        }
      }
      email.addEventListener('input', sync);
      email.addEventListener('change', sync);
      document.getElementById('registerform') && document.getElementById('registerform').addEventListener('submit', sync);
    })();
    </script>
    <?php
});


add_filter('login_headerurl', static function () {
    return home_url('/');
});
add_filter('login_headertext', static function () {
    return get_bloginfo('name');
});


/* ——— Extended register fields: name, email, password, confirm ——— */
add_action('register_form', static function () {
    $first = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
    ?>
    <p class="bankai-reg-name">
        <label for="first_name"><?php esc_html_e('نام', 'bankai-theme'); ?></label>
        <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr($first); ?>" size="25" autocomplete="name" required>
    </p>
    <p class="bankai-reg-pass">
        <label for="user_pass"><?php esc_html_e('رمز عبور', 'bankai-theme'); ?></label>
        <input type="password" name="user_pass" id="user_pass" class="input" value="" size="25" autocomplete="new-password" required>
    </p>
    <p class="bankai-reg-pass2">
        <label for="user_pass2"><?php esc_html_e('تکرار رمز عبور', 'bankai-theme'); ?></label>
        <input type="password" name="user_pass2" id="user_pass2" class="input" value="" size="25" autocomplete="new-password" required>
    </p>
    <style>
      body.login #registerform > p:first-of-type label[for="user_login"],
      body.login #registerform > p:first-of-type #user_login { /* keep username visible for WP */ }
    </style>
    <?php
});

add_filter('pre_user_login', static function ($login) {
    if (is_admin() || empty($_POST['user_email'])) {
        return $login;
    }
    // If username empty on register, derive from email
    if ($login === '' || $login === 'admin') {
        $email = sanitize_email(wp_unslash($_POST['user_email']));
        if ($email && str_contains($email, '@')) {
            $login = sanitize_user(strstr($email, '@', true), true);
        }
    }
    return $login;
});

add_filter('registration_errors', static function ($errors, $sanitized_user_login, $user_email) {
    $first = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
    if ($first === '') {
        $errors->add('first_name_error', __('<strong>خطا</strong>: نام الزامی است.', 'bankai-theme'));
    }
    $pass = isset($_POST['user_pass']) ? (string) wp_unslash($_POST['user_pass']) : '';
    $pass2 = isset($_POST['user_pass2']) ? (string) wp_unslash($_POST['user_pass2']) : '';
    if (strlen($pass) < 6) {
        $errors->add('pass_error', __('<strong>خطا</strong>: رمز عبور حداقل ۶ کاراکتر باشد.', 'bankai-theme'));
    }
    if ($pass !== $pass2) {
        $errors->add('pass2_error', __('<strong>خطا</strong>: تکرار رمز عبور یکسان نیست.', 'bankai-theme'));
    }
    return $errors;
}, 10, 3);

add_action('user_register', static function ($user_id) {
    if (! empty($_POST['first_name'])) {
        update_user_meta($user_id, 'first_name', sanitize_text_field(wp_unslash($_POST['first_name'])));
        wp_update_user(['ID' => $user_id, 'display_name' => sanitize_text_field(wp_unslash($_POST['first_name']))]);
    }
    if (! empty($_POST['user_pass'])) {
        wp_set_password((string) wp_unslash($_POST['user_pass']), $user_id);
    }
});

/* ——— Custom login / register URLs ——— */
/**
 * مسیر امن ورود سفارشی (الگوی WPS Hide Login)
 * - /{slug}/ مستقیماً wp-login.php را لود می‌کند
 * - مسدودسازی wp-login.php فقط وقتی اسلاگ ست شده و درخواست از مسیر سفارشی نیست
 * - کلید اضطراری: /wp-login.php?bankai_unlock=1 همیشه کار می‌کند
 */

function bankai_login_request_path(): string {
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = (string) (parse_url($uri, PHP_URL_PATH) ?: '');
    $home_path = (string) (parse_url(home_url('/'), PHP_URL_PATH) ?: '/');
    if ($home_path !== '/' && $home_path !== '' && str_starts_with($path, $home_path)) {
        $path = substr($path, strlen(rtrim($home_path, '/'))) ?: '/';
    }
    return '/' . trim($path, '/');
}

function bankai_login_is_custom_request(string $which = 'login'): bool {
    $slug = bankai_login_custom_slug($which);
    if ($slug === '') {
        return false;
    }
    $path = trim(bankai_login_request_path(), '/');
    // exact: login  OR login/action
    if ($path === $slug) {
        return true;
    }
    if (str_starts_with($path, $slug . '/')) {
        return true;
    }
    return false;
}

/** لود واقعی فرم ورود روی اسلاگ سفارشی */
function bankai_login_load_custom_endpoint(): void {
    // جلوگیری از تداخل با ادمین و AJAX و cron
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }

    $login_slug = bankai_login_custom_slug('login');
    $reg_slug = bankai_login_custom_slug('register');
    if ($login_slug === '' && $reg_slug === '') {
        return;
    }

    $path = trim(bankai_login_request_path(), '/');
    $is_login = ($login_slug !== '' && ($path === $login_slug || str_starts_with($path, $login_slug . '/')));
    $is_reg = ($reg_slug !== '' && ($path === $reg_slug || str_starts_with($path, $reg_slug . '/')));

    if (! $is_login && ! $is_reg) {
        return;
    }

    // action از مسیر یا query
    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
    if ($is_reg && $action === '') {
        $action = 'register';
        $_REQUEST['action'] = 'register';
        $_GET['action'] = 'register';
    }
    if ($is_login && $action === '' && str_starts_with($path, $login_slug . '/')) {
        $parts = explode('/', $path);
        if (isset($parts[1]) && $parts[1] !== '') {
            $action = sanitize_key($parts[1]);
            $_REQUEST['action'] = $action;
            $_GET['action'] = $action;
        }
    }

    // شبیه‌سازی محیط wp-login
    global $pagenow, $user_login, $error;
    $pagenow = 'wp-login.php';
    if (! defined('WP_USE_THEMES')) {
        // continue
    }

    // جلوگیری از ریدایرکت مسدودساز برای این درخواست
    if (! defined('BANKAI_LOGIN_CUSTOM_HIT')) {
        define('BANKAI_LOGIN_CUSTOM_HIT', true);
    }

    require_once ABSPATH . 'wp-login.php';
    exit;
}
add_action('init', 'bankai_login_load_custom_endpoint', 2);

/** مسدودسازی wp-login.php — فقط دسترسی مستقیم، نه مسیر سفارشی */
function bankai_login_block_default(): void {
    // کلید اضطراری همیشه باز است
    if (isset($_GET['bankai_unlock']) && (string) $_GET['bankai_unlock'] === '1') {
        return;
    }
    if (defined('BANKAI_LOGIN_CUSTOM_HIT') && BANKAI_LOGIN_CUSTOM_HIT) {
        return;
    }
    if (! (bool) bankai_login_mod('login_block_default_urls', false)) {
        return;
    }
    $login_slug = bankai_login_custom_slug('login');
    if ($login_slug === '') {
        // بدون اسلاگ، مسدودسازی معنی ندارد — جلوگیری از قفل
        return;
    }

    global $pagenow;
    // فقط وقتی خود فایل wp-login.php مستقیم صدا زده شده
    $script = isset($_SERVER['SCRIPT_NAME']) ? basename((string) $_SERVER['SCRIPT_NAME']) : '';
    $is_direct = ($pagenow === 'wp-login.php') || ($script === 'wp-login.php');
    if (! $is_direct) {
        return;
    }

    // اگر از مسیر سفارشی آمده‌ایم، اجازه بده
    if (bankai_login_is_custom_request('login') || bankai_login_is_custom_request('register')) {
        return;
    }

    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';
    // logout و postpass همیشه از wp-login باید کار کنند
    if (in_array($action, ['logout', 'postpass'], true)) {
        return;
    }

    // ریدایرکت به مسیر سفارشی (نه حلقه)
    $reg_slug = bankai_login_custom_slug('register');
    if ($action === 'register' && $reg_slug !== '') {
        wp_safe_redirect(home_url(user_trailingslashit($reg_slug)));
        exit;
    }
    $target = home_url(user_trailingslashit($login_slug));
    if ($action && $action !== 'login') {
        $target = add_query_arg('action', $action, $target);
    }
    wp_safe_redirect($target);
    exit;
}
add_action('init', 'bankai_login_block_default', 1);

add_filter('login_url', static function ($url, $redirect, $force_reauth) {
    $slug = bankai_login_custom_slug('login');
    if ($slug === '') {
        return $url;
    }
    $custom = home_url(user_trailingslashit($slug));
    if ($redirect) {
        $custom = add_query_arg('redirect_to', $redirect, $custom);
    }
    if ($force_reauth) {
        $custom = add_query_arg('reauth', '1', $custom);
    }
    return $custom;
}, 10, 3);

add_filter('register_url', static function ($url) {
    $slug = bankai_login_custom_slug('register');
    if ($slug === '') {
        return $url;
    }
    return home_url(user_trailingslashit($slug));
});

add_filter('lostpassword_url', static function ($url, $redirect) {
    $slug = bankai_login_custom_slug('login');
    if ($slug === '') {
        return $url;
    }
    $custom = add_query_arg('action', 'lostpassword', home_url(user_trailingslashit($slug)));
    if ($redirect) {
        $custom = add_query_arg('redirect_to', $redirect, $custom);
    }
    return $custom;
}, 10, 2);

/** پس از ذخیره اسلاگ، rewrite لازم نیست — endpoint روی init لود می‌شود */
function bankai_login_maybe_flush_rewrites(): void {
    // نگه‌داشته شده برای سازگاری؛ دیگر به rewrite وابسته نیستیم
}
add_action('init', 'bankai_login_maybe_flush_rewrites', 20);

/* ——— Front-end live preview for Customizer ——— */
function bankai_login_preview_query_vars(array $vars): array {
    $vars[] = 'bankai_login_preview';
    return $vars;
}
add_filter('query_vars', 'bankai_login_preview_query_vars');

function bankai_login_preview_template(): void {
    if ((int) get_query_var('bankai_login_preview') !== 1) {
        return;
    }
    if (! is_user_logged_in() && ! is_customize_preview()) {
        // Allow preview only in customizer or for admins
        if (! current_user_can('edit_theme_options')) {
            return;
        }
    }

    $layout = bankai_login_layout();
    $style = bankai_login_design_style();
    $logo = bankai_login_logo_url();
    $bg = bankai_login_bg_url();
    $view = isset($_GET['view']) ? sanitize_key(wp_unslash($_GET['view'])) : 'login';
    if ($view !== 'register') { $view = 'login'; }
    $title = $view === 'register' ? bankai_login_register_title_text() : bankai_login_title_text();
    $theme_id = sanitize_key((string) bankai_login_mod('login_theme', 'light'));
    $colors = ['primary' => '#570DF8', 'secondary' => '#F000B8', 'base' => '#FFFFFF', 'base_content' => '#1F2937', 'neutral' => '#3D4451'];
    if (function_exists('bankai_theme_presets')) {
        $presets = bankai_theme_presets();
        if (! empty($presets[$theme_id]['colors'])) {
            $colors = array_merge($colors, $presets[$theme_id]['colors']);
        }
    }
    $accent = $colors['primary'];
    $style = bankai_login_design_style();
    $layout = bankai_login_layout();

    status_header(200);
    nocache_headers();
    ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html($title); ?> — <?php bloginfo('name'); ?></title>
<link rel="stylesheet" href="<?php echo esc_url(BANKAI_THEME_URI . '/assets/css/login.css'); ?>?v=<?php echo esc_attr(BANKAI_THEME_VERSION); ?>">
<style>
:root{
--bankai-login-accent:<?php echo esc_attr($colors['primary']); ?>;
--bankai-login-secondary:<?php echo esc_attr($colors['secondary'] ?? $colors['primary']); ?>;
--bankai-login-base:<?php echo esc_attr($colors['base'] ?? '#fff'); ?>;
--bankai-login-text:<?php echo esc_attr($colors['base_content'] ?? '#1a1a1a'); ?>;
--bankai-login-neutral:<?php echo esc_attr($colors['neutral'] ?? '#333'); ?>;
--bankai-login-logo:url("<?php echo esc_url($logo); ?>");
<?php echo $bg ? '--bankai-login-bg:url("' . esc_url($bg) . '");' : ''; ?>
}
body.login h1 a{background-image:var(--bankai-login-logo)!important;width:160px;height:52px;background-size:contain!important;background-position:center!important;}
body.login #loginform::before,body.login #registerform::before{content:none!important;display:none!important;}
</style>
<?php
if (is_customize_preview()) {
    wp_head();
}
?>
</head>
<body class="login wp-core-ui bankai-login-layout--<?php echo esc_attr($layout); ?> bankai-login-style--<?php echo esc_attr($style); ?> bankai-style-<?php echo esc_attr($style); ?> bankai-login-theme--<?php echo esc_attr($theme_id); ?>">
<?php if (in_array($layout, ['split', 'left', 'right'], true)) : ?>
<div class="bankai-login-split-bg" aria-hidden="true"></div>
<aside class="bankai-login-intro">
  <div class="bankai-login-intro__card">
    <h2 class="bankai-login-intro__title"><?php echo esc_html(bankai_login_intro_title()); ?></h2>
    <p class="bankai-login-intro__text"><?php echo esc_html(bankai_login_intro_text()); ?></p>
  </div>
</aside>
<?php endif; ?>
<div id="login">
  <h1><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></h1>
  <div class="bankai-login-form-title"><?php echo esc_html($title); ?></div>
  <?php if ($view === 'register') : ?>
  <form name="registerform" id="registerform" action="#" method="post" onsubmit="return false;">
    <p>
      <label for="first_name"><?php esc_html_e('نام', 'bankai-theme'); ?></label>
      <input type="text" name="first_name" id="first_name" class="input" value="" autocomplete="name">
    </p>
    <p>
      <label for="user_email"><?php esc_html_e('ایمیل', 'bankai-theme'); ?></label>
      <input type="email" name="user_email" id="user_email" class="input" value="" autocomplete="email">
    </p>
    <p>
      <label for="user_pass"><?php esc_html_e('رمز عبور', 'bankai-theme'); ?></label>
      <input type="password" name="user_pass" id="user_pass" class="input" value="" autocomplete="new-password">
    </p>
    <p>
      <label for="user_pass2"><?php esc_html_e('تکرار رمز عبور', 'bankai-theme'); ?></label>
      <input type="password" name="user_pass2" id="user_pass2" class="input" value="" autocomplete="new-password">
    </p>
    <p class="submit">
      <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php echo esc_attr($title); ?>">
    </p>
  </form>
  <p id="nav">
    <a href="<?php echo esc_url(home_url('/?bankai_login_preview=1')); ?>"><?php esc_html_e('ورود', 'bankai-theme'); ?></a>
  </p>
  <?php else : ?>
  <form name="loginform" id="loginform" action="#" method="post" onsubmit="return false;">
    <p>
      <label for="user_login"><?php esc_html_e('نام کاربری یا ایمیل', 'bankai-theme'); ?></label>
      <input type="text" name="log" id="user_login" class="input" value="" autocomplete="username">
    </p>
    <p>
      <label for="user_pass"><?php esc_html_e('رمز عبور', 'bankai-theme'); ?></label>
      <input type="password" name="pwd" id="user_pass" class="input" value="" autocomplete="current-password">
    </p>
    <p class="forgetmenot"><label for="rememberme"><input name="rememberme" type="checkbox" id="rememberme" value="forever"> <?php esc_html_e('مرا به خاطر بسپار', 'bankai-theme'); ?></label></p>
    <p class="submit">
      <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php echo esc_attr($title); ?>">
    </p>
  </form>
  <p id="nav">
    <a href="#"><?php esc_html_e('رمز عبور را فراموش کرده‌اید؟', 'bankai-theme'); ?></a>
    <span class="bankai-login-sep">|</span>
    <a href="<?php echo esc_url(home_url('/?bankai_login_preview=1&view=register')); ?>"><?php esc_html_e('ثبت‌نام', 'bankai-theme'); ?></a>
  </p>
  <?php endif; ?>
  <p id="backtoblog"><a href="<?php echo esc_url(home_url('/')); ?>">&larr; <?php bloginfo('name'); ?></a></p>
</div>
<?php
if (is_customize_preview()) {
    wp_footer();
}
?>
</body>
</html>
    <?php
    exit;
}
add_action('template_redirect', 'bankai_login_preview_template', 1);
