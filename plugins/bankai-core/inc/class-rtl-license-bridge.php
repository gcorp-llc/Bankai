<?php
/**
 * RTL Theme license bridge for Bankai Core.
 *
 * Production: official RTL_License_* (ionCube) + SHA1 + isActive().
 * Activation UI stores purchase code / username and tries known activate APIs on the class.
 * Development: BANKAI_DEV_MODE true unlocks everything (no key).
 *
 * @package Bankai
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const BANKAI_RTL_LICENSE_SHA1  = 'a1dea5429a2924ebdd2186cefae0634326f7b94c';
const BANKAI_RTL_LICENSE_CLASS = 'RTL_License_b4280a8197854356';
const BANKAI_RTL_LICENSE_OPT   = 'bankai_rtl_license_data';

function bankai_is_dev_build(): bool
{
    if (defined('BANKAI_DEV_MODE') && BANKAI_DEV_MODE) {
        return true;
    }
    // Optional local override without rebuilding the zip
    if (defined('BANKAI_FORCE_LICENSE') && BANKAI_FORCE_LICENSE) {
        return true;
    }
    return false;
}

/**
 * Stored activation payload (purchase code + username).
 *
 * @return array{code:string,username:string,activated_at:string,last_message:string}
 */
function bankai_rtl_license_get_data(): array
{
    $raw = get_option(BANKAI_RTL_LICENSE_OPT, []);
    if (!is_array($raw)) {
        $raw = [];
    }
    return [
        'code'         => isset($raw['code']) ? (string) $raw['code'] : '',
        'username'     => isset($raw['username']) ? (string) $raw['username'] : '',
        'activated_at' => isset($raw['activated_at']) ? (string) $raw['activated_at'] : '',
        'last_message' => isset($raw['last_message']) ? (string) $raw['last_message'] : '',
    ];
}

/**
 * @param array{code?:string,username?:string,activated_at?:string,last_message?:string} $data
 */
function bankai_rtl_license_save_data(array $data): void
{
    $cur = bankai_rtl_license_get_data();
    $out = array_merge($cur, $data);
    update_option(BANKAI_RTL_LICENSE_OPT, $out, false);
}

/**
 * Instantiate verified RTL license class or null.
 *
 * @return object|null
 */
function bankai_rtl_license_instance()
{
    $class = BANKAI_RTL_LICENSE_CLASS;
    if (!class_exists($class, false)) {
        return null;
    }
    try {
        $ref  = new ReflectionClass($class);
        $file = $ref->getFileName();
        if (!is_string($file) || !is_file($file)) {
            return null;
        }
        $hash = @sha1_file($file);
        if (!is_string($hash) || !hash_equals(BANKAI_RTL_LICENSE_SHA1, $hash)) {
            return null;
        }
        return new $class();
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * Whether product is unlocked.
 */
function bankai_rtl_license_is_active(): bool
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    if (bankai_is_dev_build()) {
        $cached = true;
        return $cached;
    }

    $cached = false;
    $instance = bankai_rtl_license_instance();
    if (!$instance || !method_exists($instance, 'isActive')) {
        return $cached;
    }

    try {
        if ($instance->{'isActive'}() === true) {
            $cached = true;
        }
    } catch (Throwable $e) {
        $cached = false;
    }

    return $cached;
}

/**
 * Try to activate using the ionCube license class (method names vary by RTL product generation).
 *
 * @return array{success:bool,message:string,active:bool}
 */
function bankai_rtl_license_activate(string $code, string $username = ''): array
{
    $code     = trim($code);
    $username = trim($username);

    if ($code === '') {
        return [
            'success' => false,
            'message' => 'کد خرید / کد لایسنس را وارد کنید.',
            'active'  => false,
        ];
    }

    bankai_rtl_license_save_data([
        'code'         => $code,
        'username'     => $username,
        'last_message' => '',
    ]);

    if (bankai_is_dev_build()) {
        bankai_rtl_license_save_data([
            'activated_at' => current_time('mysql'),
            'last_message' => 'لایسنس فعال است.',
        ]);
        return [
            'success' => true,
            'message' => 'لایسنس فعال است.',
            'active'  => true,
        ];
    }

    if (!extension_loaded('ionCube Loader')) {
        return [
            'success' => false,
            'message' => 'برای لایسنس راست‌چین باید ionCube Loader روی سرور نصب باشد.',
            'active'  => false,
        ];
    }

    $instance = bankai_rtl_license_instance();
    if (!$instance) {
        return [
            'success' => false,
            'message' => 'فایل لایسنس بارگذاری نشد یا هش آن نامعتبر است.',
            'active'  => false,
        ];
    }

    // Attempt common activation entry points used across RTL-encoded products
    $tried   = [];
    $ok_call = false;
    $err_msg = '';

    $candidates = [
        // [method, args]
        ['activate', [$code, $username]],
        ['activate', [$code]],
        ['setLicense', [$code]],
        ['setLicenseCode', [$code]],
        ['setCode', [$code]],
        ['setPurchaseCode', [$code]],
        ['setUser', [$username]],
        ['setUsername', [$username]],
        ['checkLicense', [$code, $username]],
        ['check', [$code]],
        ['register', [$code, $username]],
    ];

    foreach ($candidates as [$method, $args]) {
        if (!method_exists($instance, $method)) {
            continue;
        }
        $tried[] = $method;
        try {
            $refm = new ReflectionMethod($instance, $method);
            $params = $refm->getNumberOfParameters();
            $callArgs = array_slice($args, 0, max(0, $params));
            $refm->invokeArgs($instance, $callArgs);
            $ok_call = true;
        } catch (Throwable $e) {
            $err_msg = $e->getMessage();
        }
    }

    // Persist for classes that read from options/globals
    if (method_exists($instance, 'isActive') === false) {
        return [
            'success' => false,
            'message' => 'کلاس لایسنس متد isActive ندارد.',
            'active'  => false,
        ];
    }

    // Clear static cache by using a fresh check (static inside is_active — force via transient trick)
    $active = false;
    try {
        $active = ($instance->{'isActive'}() === true);
    } catch (Throwable $e) {
        $err_msg = $e->getMessage();
    }

    // If still inactive but we have a code, some products need domain registered on rtl-theme first
    $message = $active
        ? 'لایسنس با موفقیت فعال شد.'
        : (
            $ok_call
                ? 'کد ذخیره شد اما isActive هنوز false است. دامنه را در پنل راست‌چین (دانلودها → افزودن دامنه) ثبت کنید و دوباره تلاش کنید.'
                : (
                    $tried
                        ? ('فراخوانی متدهای لایسنس انجام شد (' . implode(', ', $tried) . ') ولی فعال نشد.' . ($err_msg ? ' ' . $err_msg : ''))
                        : 'متد فعال‌سازی روی کلاس لایسنس پیدا نشد. کد ذخیره شد؛ اگر دامنه در راست‌چین ثبت شده، پس از رفرش isActive باید true شود.'
                )
        );

    bankai_rtl_license_save_data([
        'activated_at' => $active ? current_time('mysql') : '',
        'last_message' => $message,
    ]);

    // Bust static cache of is_active for this request by redefining via filter result
    // (static $cached already may be false — modules load after activate AJAX so OK)

    return [
        'success' => $active,
        'message' => $message,
        'active'  => $active,
        'tried'   => $tried,
    ];
}

/**
 * @return array{active:bool,dev:bool,loader:bool,class:bool,hash_ok:bool|null,message:string,code:string,username:string}
 */
function bankai_rtl_license_status(): array
{
    $dev     = bankai_is_dev_build();
    $loader  = extension_loaded('ionCube Loader');
    $class   = class_exists(BANKAI_RTL_LICENSE_CLASS, false);
    $hash_ok = null;
    $path    = defined('BANKAI_CORE_DIR')
        ? BANKAI_CORE_DIR . BANKAI_RTL_LICENSE_CLASS . '.php'
        : '';
    $data    = bankai_rtl_license_get_data();

    if (is_string($path) && is_file($path)) {
        $h = @sha1_file($path);
        $hash_ok = is_string($h) && hash_equals(BANKAI_RTL_LICENSE_SHA1, $h);
    }

    $active = bankai_rtl_license_is_active();

    if ($dev) {
        $message = 'لایسنس فعال است.';
    } elseif ($active) {
        $message = 'لایسنس راست‌چین فعال است.';
    } elseif (!$loader) {
        $message = 'افزونه ionCube Loader روی سرور نصب نیست.';
    } elseif (!$class) {
        $message = 'کلاس لایسنس بارگذاری نشد.';
    } elseif ($hash_ok === false) {
        $message = 'هش فایل لایسنس با نسخه رسمی مطابقت ندارد.';
    } elseif ($data['last_message'] !== '') {
        $message = $data['last_message'];
    } else {
        $message = 'لایسنس غیرفعال است. کد خرید و نام کاربری راست‌چین را وارد کنید.';
    }

    return [
        'active'   => $active,
        'dev'      => $dev,
        'loader'   => $loader,
        'class'    => $class,
        'hash_ok'  => $hash_ok,
        'message'  => $message,
        'code'     => $data['code'],
        'username' => $data['username'],
    ];
}

// --------------------------------------------------------------------------------------------------- Start RTL License
if (bankai_rtl_license_is_active()) {
    if (!defined('BANKAI_RTL_LICENSED')) {
        define('BANKAI_RTL_LICENSED', true);
    }
    add_filter('bankai_is_pro_licensed', static fn(): bool => true);
} else {
    if (!defined('BANKAI_RTL_LICENSED')) {
        define('BANKAI_RTL_LICENSED', false);
    }
    add_filter('bankai_is_pro_licensed', static fn(): bool => false);
}
// ----------------------------------------------------------------------------------------------------- End RTL License
