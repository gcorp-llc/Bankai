<?php
/**
 * Progressive Web App — manifest, service worker, install prompt
 *
 * @package Bankai
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Bankai_PWA
{
    private static ?self $instance = null;

    public const OPT = 'bankai_pwa_settings';

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        // REST always available for admin configuration
        add_action('rest_api_init', [$this, 'register_rest']);

        // Routes must work even before user enables UI toggle (manifest URL must not 404)
        add_action('init', [$this, 'register_rewrites'], 5);
        add_filter('query_vars', [$this, 'query_vars']);
        add_action('template_redirect', [$this, 'serve_assets'], 0);

        $module_on = !function_exists('bankai_is_module_active') || bankai_is_module_active('pwa');
        if (!$module_on) {
            return;
        }

        add_action('wp_head', [$this, 'print_head'], 4);
        add_action('wp_footer', [$this, 'print_footer'], 20);
    }

    public function query_vars(array $vars): array
    {
        $vars[] = 'bankai_pwa_manifest';
        $vars[] = 'bankai_pwa_sw';
        return $vars;
    }

    public static function defaults(): array
    {
        $name = get_bloginfo('name') ?: 'Site';
        $icon_192 = '';
        $icon_512 = '';
        if (function_exists('get_site_icon_url')) {
            $icon_192 = (string) get_site_icon_url(192);
            $icon_512 = (string) get_site_icon_url(512);
        }

        return [
            'enabled'         => true,
            'name'            => $name,
            'short_name'      => self::truncate_text($name, 12),
            'description'     => get_bloginfo('description') ?: $name,
            'start_url'       => '/',
            'scope'           => '/',
            'display'         => 'standalone',
            'orientation'     => 'any',
            'theme_color'     => '#fc4c2f',
            'background_color'=> '#ffffff',
            'icon_192'        => $icon_192,
            'icon_512'        => $icon_512,
            'service_worker'  => true,
            'install_prompt'  => true,
            'offline_cache'   => true,
        ];
    }

    public function get_settings(): array
    {
        $saved = function_exists('bankai_get_option')
            ? bankai_get_option(self::OPT, [])
            : get_option(self::OPT, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        return array_merge(self::defaults(), $saved);
    }

    public function save_settings(array $in): array
    {
        $cur = $this->get_settings();
        $bools = ['enabled', 'service_worker', 'install_prompt', 'offline_cache'];
        $urls  = ['icon_192', 'icon_512'];
        $enums_display = ['standalone', 'fullscreen', 'minimal-ui', 'browser'];
        $enums_orient  = ['any', 'portrait', 'landscape'];

        foreach ($cur as $k => $v) {
            if (!array_key_exists($k, $in)) {
                continue;
            }
            if (in_array($k, $bools, true)) {
                $cur[$k] = !empty($in[$k]);
            } elseif (in_array($k, $urls, true)) {
                $cur[$k] = esc_url_raw((string) $in[$k]);
            } elseif ($k === 'display') {
                $val = sanitize_key((string) $in[$k]);
                $cur[$k] = in_array($val, $enums_display, true) ? $val : 'standalone';
            } elseif ($k === 'orientation') {
                $val = sanitize_key((string) $in[$k]);
                $cur[$k] = in_array($val, $enums_orient, true) ? $val : 'any';
            } elseif ($k === 'theme_color' || $k === 'background_color') {
                $hex = sanitize_hex_color((string) $in[$k]);
                $cur[$k] = $hex ?: $v;
            } elseif ($k === 'start_url' || $k === 'scope') {
                $fallback = '/';
                $cur[$k] = $this->normalize_local_path((string) $in[$k], $fallback, $k === 'scope');
            } elseif ($k === 'short_name') {
                $cur[$k] = $this->truncate_text(sanitize_text_field((string) $in[$k]), 12);
            } else {
                $cur[$k] = sanitize_text_field((string) $in[$k]);
            }
        }

        if (function_exists('bankai_update_option')) {
            bankai_update_option(self::OPT, $cur);
        } else {
            update_option(self::OPT, $cur, false);
        }

        // Keep core module toggle in sync so front-end hooks (head/footer) load
        if (function_exists('bankai_set_module_active')) {
            bankai_set_module_active('pwa', !empty($cur['enabled']));
        }
        return $cur;
    }

    public function is_module_on(): bool
    {
        if (!function_exists('bankai_is_module_active')) {
            return true;
        }
        if (bankai_is_module_active('pwa')) {
            return true;
        }
        // The central module switch is authoritative. Do not silently reactivate
        // a module that an administrator intentionally disabled in the dashboard.
        return false;
    }

    public function is_enabled(): bool
    {
        if (!$this->is_module_on()) {
            return false;
        }
        $s = $this->get_settings();
        // defaults() sets enabled=true; empty/missing → on
        return !array_key_exists('enabled', $s) || !empty($s['enabled']);
    }

    /**
     * Diagnostic snapshot for admin / site-audit.
     */
    public function status_report(): array
    {
        $s = $this->get_settings();
        $module = $this->is_module_on();
        $enabled = $this->is_enabled();
        $has_192 = !empty($s['icon_192']) || (function_exists('get_site_icon_url') && (string) get_site_icon_url(192) !== '');
        $has_512 = !empty($s['icon_512']) || (function_exists('get_site_icon_url') && (string) get_site_icon_url(512) !== '');
        $icons_ok = $has_192 && $has_512;
        $sw_on = !empty($s['service_worker']);
        $prompt_on = !empty($s['install_prompt']);
        $https = is_ssl() || str_starts_with(home_url(), 'https') || str_contains((string) home_url(), 'localhost') || str_contains((string) home_url(), '.test');
        $manifest = $this->build_manifest();
        $icon_count = count($manifest['icons'] ?? []);
        $display = (string) ($manifest['display'] ?? 'standalone');

        $issues = [];
        if (!$module) {
            $issues[] = 'ماژول PWA در پیشخوان خاموش است — از پیشخوان Bankai یا تنظیمات PWA فعال کنید';
        }
        if ($module && empty($s['enabled'])) {
            $issues[] = 'سوئیچ «فعال» داخل تنظیمات PWA خاموش است';
        }
        if (!$icons_ok || $icon_count < 1) {
            $issues[] = 'آیکون ۱۹۲/۵۱۲ تنظیم نشده';
        }
        if (!$sw_on) {
            $issues[] = 'Service Worker خاموش است';
        }
        if (!$https) {
            $issues[] = 'سایت روی HTTPS نیست (مرورگر نصب را فقط روی HTTPS مجاز می‌داند؛ localhost/.test استثناست)';
        }
        if ($enabled && $display === 'browser') {
            $issues[] = 'display مانیفست روی browser است (باید standalone باشد)';
        }

        $ready = $module && $enabled && $icons_ok && $sw_on && $https && $display !== 'browser';

        return [
            'module_active'   => $module,
            'enabled'         => $enabled,
            'ready'           => $ready,
            'icons_ok'        => $icons_ok,
            'icon_count'      => $icon_count,
            'service_worker'  => $sw_on,
            'install_prompt'  => $prompt_on,
            'https'           => $https,
            'display'         => $display,
            'manifest_url'    => home_url('/manifest.webmanifest'),
            'sw_url'          => home_url('/bankai-sw.js'),
            'issues'          => $issues,
            'detail'          => $ready
                ? ('فعال · ' . $icon_count . ' آیکون · SW ' . ($sw_on ? 'روشن' : 'خاموش') . ' · ' . $display)
                : (count($issues) ? $issues[0] : 'ناقص'),
        ];
    }

    public function register_rewrites(): void
    {
        add_rewrite_rule('^manifest\.webmanifest/?$', 'index.php?bankai_pwa_manifest=1', 'top');
        add_rewrite_rule('^bankai-sw\.js/?$', 'index.php?bankai_pwa_sw=1', 'top');
        $ver = defined('BANKAI_CORE_VERSION') ? (string) BANKAI_CORE_VERSION : '1';
        if ((string) get_option('bankai_pwa_rewrite_v') !== $ver) {
            flush_rewrite_rules(false);
            update_option('bankai_pwa_rewrite_v', $ver, false);
        }
    }

    public function serve_assets(): void
    {
        if ((int) get_query_var('bankai_pwa_manifest') === 1
            || $this->uri_ends_with('/manifest.webmanifest')) {
            $this->output_manifest();
        }
        if ((int) get_query_var('bankai_pwa_sw') === 1
            || $this->uri_ends_with('/bankai-sw.js')) {
            $this->output_service_worker();
        }
    }

    private function uri_ends_with(string $suffix): bool
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = (string) (wp_parse_url($uri, PHP_URL_PATH) ?: $uri);
        return (bool) preg_match('#' . preg_quote($suffix, '#') . '/?$#', $path);
    }

    private function abs_url(string $path_or_url): string
    {
        if ($path_or_url === '') {
            return home_url('/');
        }
        if (preg_match('#^https?://#i', $path_or_url)) {
            return $path_or_url;
        }
        return home_url('/' . ltrim($path_or_url, '/'));
    }

    public function build_manifest(): array
    {
        $s = $this->get_settings();
        // Manifest requests must remain read-only and inexpensive. Icon generation
        // is performed explicitly through the authenticated admin REST endpoint.
        $icons = [];
        $add_icon = function (array &$icons, string $src, string $sizes) {
            if ($src === '') {
                return;
            }
            $icons[] = [
                'src'     => $src,
                'sizes'   => $sizes,
                'type'    => $this->icon_mime_type($src),
                'purpose' => 'any',
            ];
        };
        if (!empty($s['icon_192'])) {
            $add_icon($icons, (string) $s['icon_192'], '192x192');
        }
        if (!empty($s['icon_512'])) {
            $add_icon($icons, (string) $s['icon_512'], '512x512');
        }
        if (function_exists('get_site_icon_url')) {
            $fallback_192 = (string) get_site_icon_url(192);
            $fallback_512 = (string) get_site_icon_url(512);

            if (empty($s['icon_192']) && $fallback_192 !== '') {
                $add_icon($icons, $fallback_192, '192x192');
            }
            if (empty($s['icon_512']) && $fallback_512 !== '') {
                $add_icon($icons, $fallback_512, '512x512');
            }
        }

        return [
            'name'             => $s['name'] ?: get_bloginfo('name'),
            'short_name'       => $s['short_name'] ?: $this->truncate_text((string) get_bloginfo('name'), 12),
            'description'      => $s['description'] ?: get_bloginfo('description'),
            'start_url'        => $this->abs_url($s['start_url'] ?: '/'),
            'scope'            => $this->abs_url($s['scope'] ?: '/'),
            'display'          => $s['display'] ?: 'standalone',
            'orientation'      => $s['orientation'] ?: 'any',
            'background_color' => $s['background_color'] ?: '#ffffff',
            'theme_color'      => $s['theme_color'] ?: '#fc4c2f',
            'lang'             => get_bloginfo('language') ?: 'fa',
            'dir'              => is_rtl() ? 'rtl' : 'ltr',
            'icons'            => $icons,
        ];
    }

    private function icon_mime_type(string $url): string
    {
        $path = (string) (wp_parse_url($url, PHP_URL_PATH) ?: '');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $types = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'svg' => 'image/svg+xml',
        ];
        return $types[$ext] ?? 'image/png';
    }

    private function output_manifest(): void
    {
        nocache_headers();
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('X-Robots-Tag: noindex');
        $data = $this->build_manifest();
        // Only force non-installable when both module and settings are off
        if (!$this->is_enabled()) {
            $data['display'] = 'browser';
        } elseif (empty($data['display']) || $data['display'] === 'browser') {
            // Guard: never ship installable-looking settings with browser display when enabled
            $data['display'] = 'standalone';
        }
        echo wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    private function output_service_worker(): void
    {
        $s = $this->get_settings();
        if (!$this->is_enabled() || empty($s['service_worker'])) {
            status_header(404);
            header('Content-Type: application/javascript; charset=utf-8');
            echo '/* Bankai PWA SW disabled */';
            exit;
        }

        $site_hash = substr(md5(home_url('/')), 0, 10);
        $cache_name = 'bankai-pwa-' . $site_hash . '-v'
            . (defined('BANKAI_CORE_VERSION') ? preg_replace('/[^a-zA-Z0-9_.-]/', '', BANKAI_CORE_VERSION) : '1');
        $offline = !empty($s['offline_cache']);
        $start = $this->abs_url($s['start_url'] ?: '/');
        $cache_js = wp_json_encode($cache_name, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $start_js = wp_json_encode($start, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $js = <<<JS
/* Bankai PWA Service Worker */
const CACHE = {$cache_js};
const CACHE_PREFIX = 'bankai-pwa-{$site_hash}-';
const OFFLINE = {$this->js_bool($offline)};
const START = {$start_js};

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    if (OFFLINE) {
      const cache = await caches.open(CACHE);
      try { await cache.addAll([START]); } catch (e) {}
    }
    self.skipWaiting();
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    // Never delete caches owned by WordPress themes, other plugins, or other apps.
    await Promise.all(keys.filter((k) => k.indexOf(CACHE_PREFIX) === 0 && k !== CACHE).map((k) => caches.delete(k)));
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (!OFFLINE || req.method !== 'GET' || req.mode !== 'navigate') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Do not cache admin, authentication, API, preview, or commerce-sensitive pages.
  if (/\/(wp-admin|wp-login\.php|wp-json|cart|checkout|my-account)(\/|$)/i.test(url.pathname)
      || /(?:^|[?&])(preview|add-to-cart|customize_changeset_uuid)=/i.test(url.search)) return;

  event.respondWith((async () => {
    try {
      const response = await fetch(req);
      const cacheControl = (response.headers.get('Cache-Control') || '').toLowerCase();
      const hasSetCookie = response.headers.has('Set-Cookie');
      const contentType = (response.headers.get('Content-Type') || '').toLowerCase();

      if (response.ok && contentType.indexOf('text/html') !== -1
          && !hasSetCookie && cacheControl.indexOf('no-store') === -1
          && cacheControl.indexOf('private') === -1) {
        const cache = await caches.open(CACHE);
        await cache.put(req, response.clone());
      }
      return response;
    } catch (err) {
      const cache = await caches.open(CACHE);
      const cachedPage = await cache.match(req);
      if (cachedPage) return cachedPage;

      const fallback = await cache.match(START);
      if (fallback) return fallback;

      return new Response('صفحه در حالت آفلاین در دسترس نیست.', {
        status: 503,
        headers: { 'Content-Type': 'text/plain; charset=utf-8' }
      });
    }
  })());
});
JS;

        nocache_headers();
        header('Content-Type: application/javascript; charset=utf-8');
        $sw_scope = (string) (wp_parse_url($this->abs_url($s['scope'] ?: '/'), PHP_URL_PATH) ?: '/');
        header('Service-Worker-Allowed: ' . trailingslashit($sw_scope));
        echo $js;
        exit;
    }

    private function js_bool(bool $v): string
    {
        return $v ? 'true' : 'false';
    }

    public function print_head(): void
    {
        if (is_admin() || !$this->is_enabled()) {
            return;
        }
        $s = $this->get_settings();
        echo '<link rel="manifest" href="' . esc_url(home_url('/manifest.webmanifest')) . '">' . "\n";
        if (!empty($s['theme_color'])) {
            echo '<meta name="theme-color" content="' . esc_attr($s['theme_color']) . '">' . "\n";
        }
        echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        if (!empty($s['name'])) {
            echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr($s['short_name'] ?: $s['name']) . '">' . "\n";
        }
        $icon = !empty($s['icon_192']) ? (string) $s['icon_192'] : (string) ($s['icon_512'] ?? '');
        if ($icon === '' && function_exists('get_site_icon_url')) {
            $icon = (string) get_site_icon_url(192);
        }
        if ($icon !== '') {
            echo '<link rel="apple-touch-icon" sizes="192x192" href="' . esc_url($icon) . '">' . "\n";
            echo '<link rel="icon" sizes="192x192" href="' . esc_url($icon) . '">' . "\n";
        }
    }

    public function print_footer(): void
    {
        if (is_admin() || !$this->is_enabled()) {
            return;
        }
        $s = $this->get_settings();
        $sw_url = home_url('/bankai-sw.js');
        $need_sw = !empty($s['service_worker']);
        $need_prompt = !empty($s['install_prompt']);
        $is_admin_user = current_user_can('manage_options');

        if (!$need_sw && !$need_prompt && !$is_admin_user) {
            return;
        }

        $cfg = [
            'swUrl'      => $sw_url,
            'scope'      => $this->abs_url($s['scope'] ?: '/'),
            'needSw'     => $need_sw,
            'needPrompt' => $need_prompt,
            'isAdmin'    => $is_admin_user,
            'name'       => $s['short_name'] ?: ($s['name'] ?: get_bloginfo('name')),
            'theme'      => $s['theme_color'] ?: '#fc4c2f',
            'manifest'   => home_url('/manifest.webmanifest'),
        ];
        $cfg_json = wp_json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $theme_css = esc_attr($cfg['theme']);

        echo '<style id="bankai-pwa-css">'
            . '#bankai-pwa-banner{position:fixed;z-index:99999;left:16px;right:16px;bottom:16px;max-width:380px;margin-inline-end:auto;'
            . 'background:#0f172a;color:#fff;border-radius:16px;padding:14px 16px;box-shadow:0 12px 40px rgba(15,23,42,.35);'
            . 'font-family:Tahoma,Arial,sans-serif;direction:rtl;display:none;align-items:center;gap:12px}'
            . '#bankai-pwa-banner.is-show{display:flex}'
            . '#bankai-pwa-banner .bk-pwa-txt{flex:1;min-width:0}'
            . '#bankai-pwa-banner strong{display:block;font-size:13px;margin-bottom:3px}'
            . '#bankai-pwa-banner span{font-size:11px;opacity:.85;line-height:1.4}'
            . '#bankai-pwa-banner button{border:0;border-radius:10px;padding:10px 14px;font:700 12px/1 Tahoma,sans-serif;cursor:pointer}'
            . '#bankai-pwa-banner .bk-pwa-go{background:' . $theme_css . ';color:#fff}'
            . '#bankai-pwa-banner .bk-pwa-x{background:transparent;color:#94a3b8;padding:8px}'
            . '#bankai-pwa-status{position:fixed;z-index:99998;left:16px;bottom:16px;max-width:340px;background:#fff;color:#0f172a;'
            . 'border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px;font:12px/1.55 Tahoma,sans-serif;direction:rtl;'
            . 'box-shadow:0 8px 24px rgba(15,23,42,.12);display:none}'
            . '#bankai-pwa-status.is-show{display:block;white-space:pre-line}'
            . '#bankai-pwa-status b{color:#c2410c}'
            . '#bankai-pwa-status a{color:#2563eb}'
            . '</style>';

        echo '<script id="bankai-pwa-runtime">(function(){var C=' . $cfg_json . ';';
        echo <<<'JS'
function qs(id){return document.getElementById(id)}
function showBanner(deferred){
  if(qs('bankai-pwa-banner'))return;
  var el=document.createElement('div');
  el.id='bankai-pwa-banner';el.className='is-show';
  var text=document.createElement('div');text.className='bk-pwa-txt';
  var title=document.createElement('strong');title.textContent='نصب '+(C.name||'اپ');
  var description=document.createElement('span');description.textContent='برای دسترسی سریع‌تر، اپلیکیشن را روی دستگاه نصب کنید.';
  text.appendChild(title);text.appendChild(description);
  var install=document.createElement('button');install.type='button';install.className='bk-pwa-go';install.textContent='نصب';
  var close=document.createElement('button');close.type='button';close.className='bk-pwa-x';close.setAttribute('aria-label','بستن');close.textContent='✕';
  el.appendChild(text);el.appendChild(install);el.appendChild(close);
  document.body.appendChild(el);
  close.onclick=function(){el.remove();try{localStorage.setItem('bankai_pwa_dismiss',String(Date.now()))}catch(e){}};
  install.onclick=function(){
    if(!deferred)return;
    deferred.prompt();
    deferred.userChoice.finally(function(){deferred=null;el.remove();});
  };
}
function showStatus(lines){
  if(!C.isAdmin)return;
  var el=qs('bankai-pwa-status');
  if(!el){el=document.createElement('div');el.id='bankai-pwa-status';document.body.appendChild(el)}
  el.className='is-show';
  el.textContent='وضعیت PWA\n'+lines.join('\n');
}
if(C.needSw && 'serviceWorker' in navigator){
  window.addEventListener('load',function(){
    navigator.serviceWorker.register(C.swUrl,{scope:C.scope || '/',updateViaCache:'none'})
      .then(function(reg){if(C.isAdmin)console.info('[Bankai PWA] SW ok',reg.scope);try{reg.update()}catch(e){}})
      .catch(function(err){
        console.warn('[Bankai PWA] SW fail',err);
        showStatus(['ثبت Service Worker ناموفق',String(err&&err.message||err),'URL: '+C.swUrl]);
      });
  });
} else if(C.isAdmin && C.needSw){
  showStatus(['مرورگر از Service Worker پشتیبانی نمی‌کند']);
}
var deferred=null,dismissed=false;
try{dismissed=Date.now()-Number(localStorage.getItem('bankai_pwa_dismiss')||0)<7*24*3600*1000}catch(e){}
window.addEventListener('beforeinstallprompt',function(e){
  e.preventDefault();deferred=e;
  if(C.needPrompt&&!dismissed)showBanner(deferred);
  if(C.isAdmin)showStatus(['✓ معیارهای نصب برقرار است','دکمه نصب باید نمایش داده شود']);
});
window.addEventListener('appinstalled',function(){
  var b=qs('bankai-pwa-banner');if(b)b.remove();
  if(C.isAdmin)showStatus(['✓ اپ نصب شد']);
});
if(C.isAdmin){
  setTimeout(function(){
    if(deferred)return;
    if(window.matchMedia&&window.matchMedia('(display-mode: standalone)').matches){
      showStatus(['✓ در حال اجرا به‌صورت نصب‌شده']);return;
    }
    var lines=['وضعیت: منتظر شرط نصب مرورگر'];
    lines.push('مانیفست: '+C.manifest);
    lines.push('Service Worker: '+C.swUrl);
    if(!window.isSecureContext)lines.push('⚠ بستر امن (HTTPS) نیست — نصب فقط روی HTTPS فعال می‌شود');
    if(!('serviceWorker' in navigator))lines.push('⚠ این مرورگر Service Worker ندارد');
    lines.push('نکته: رویداد «نصب» فقط وقتی ظاهر می‌شود که مانیفست، SW و آیکون‌های ۱۹۲/۵۱۲ معتبر باشند و اپ قبلاً نصب نشده باشد.');
    lines.push('اگر آیکون‌ها یک لوگوی مشترک باشند، از پیشخوان Bankai → تنظیمات PWA دکمه «ساخت خودکار آیکون» را بزنید.');
    showStatus(lines);
  },4000);
}
})();
JS;
        echo '</script>';
    }

    private static function truncate_text(string $value, int $length): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $length, 'UTF-8');
        }

        if (preg_match_all('/./us', $value, $matches)) {
            return implode('', array_slice($matches[0], 0, $length));
        }

        return substr($value, 0, $length);
    }

    /**
     * Accept only same-site paths/URLs for manifest start_url and scope.
     */
    private function normalize_local_path(string $value, string $fallback = '/', bool $directory = false): string
    {
        $value = trim($value);
        if ($value === '') {
            $value = $fallback;
        }

        $parts = wp_parse_url($value);
        if ($parts === false) {
            return $fallback;
        }

        $path = (string) ($parts['path'] ?? '/');
        if (!empty($parts['host'])) {
            $home = wp_parse_url(home_url('/'));
            if (empty($home['host']) || strtolower($parts['host']) !== strtolower($home['host'])) {
                return $fallback;
            }
            if (!empty($parts['scheme']) && !empty($home['scheme'])
                && strtolower($parts['scheme']) !== strtolower($home['scheme'])) {
                return $fallback;
            }

            // Full same-site URLs are converted to paths relative to the WP home URL.
            $home_path = trailingslashit((string) (wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/'));
            if ($home_path !== '/' && strpos(trailingslashit($path), $home_path) === 0) {
                $path = '/' . ltrim(substr($path, strlen($home_path)), '/');
            }
        }

        if ($path === '' || $path[0] !== '/') {
            $path = '/' . ltrim($path, '/');
        }
        $home_path = trailingslashit((string) (wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/'));
        if ($home_path !== '/' && strpos(trailingslashit($path), $home_path) === 0) {
            $path = '/' . ltrim(substr($path, strlen($home_path)), '/');
        }
        if (preg_match('#(?:^|/)\.\.(?:/|$)#', $path) || strpos($path, "\0") !== false) {
            return $fallback;
        }

        if ($directory) {
            return trailingslashit($path);
        }

        $query = isset($parts['query']) ? '?' . sanitize_text_field((string) $parts['query']) : '';
        return untrailingslashit($path) . $query;
    }

    /**
     * Generate real square PNG icons from a local attachment or the site icon.
     *
     * @return array{ok:bool,message:string,icons?:array}
     */
    public function generate_icons_from_source(?string $source = null, bool $force = false): array
    {
        $settings = $this->get_settings();
        if (!$force && ($source === null || $source === '') && !empty($settings['icon_192']) && !empty($settings['icon_512'])) {
            return [
                'ok' => true,
                'message' => 'آیکون‌ها از قبل تنظیم شده‌اند.',
                'icons' => ['192' => $settings['icon_192'], '512' => $settings['icon_512']],
            ];
        }

        $attachment_id = 0;
        if ($source !== null && $source !== '') {
            if (ctype_digit($source)) {
                $attachment_id = absint($source);
            } else {
                $source = esc_url_raw($source);
                if ($source === '') {
                    return ['ok' => false, 'message' => 'آدرس تصویر معتبر نیست.'];
                }
                $attachment_id = absint(attachment_url_to_postid($source));
            }
            if (!$attachment_id) {
                return ['ok' => false, 'message' => 'تصویر باید از کتابخانه رسانه همین سایت انتخاب شود.'];
            }
        } else {
            $site_icon_id = (int) get_option('site_icon');
            if ($site_icon_id > 0) {
                $attachment_id = $site_icon_id;
            }
        }

        if (!$attachment_id || get_post_type($attachment_id) !== 'attachment') {
            return ['ok' => false, 'message' => 'تصویر منبع یافت نشد؛ ابتدا آیکون سایت یا یک تصویر رسانه انتخاب کنید.'];
        }

        $source_file = get_attached_file($attachment_id);
        if (!$source_file || !is_readable($source_file) || !@getimagesize($source_file)) {
            return ['ok' => false, 'message' => 'فایل منبع یک تصویر معتبر و قابل‌خواندن نیست.'];
        }

        $upload = wp_upload_dir();
        if (!empty($upload['error'])) {
            return ['ok' => false, 'message' => (string) $upload['error']];
        }

        $directory = trailingslashit($upload['basedir']) . 'bankai-pwa';
        if (!wp_mkdir_p($directory)) {
            return ['ok' => false, 'message' => 'ساخت پوشه آیکون‌های PWA ناموفق بود.'];
        }

        $generated = [];
        foreach ([192, 512] as $size) {
            $image_editor = wp_get_image_editor($source_file);
            if (is_wp_error($image_editor)) {
                return ['ok' => false, 'message' => $image_editor->get_error_message()];
            }

            $resized = $image_editor->resize($size, $size, true);
            if (is_wp_error($resized)) {
                return ['ok' => false, 'message' => $resized->get_error_message()];
            }

            $destination = trailingslashit($directory) . 'icon-' . $size . '.png';
            $saved = $image_editor->save($destination, 'image/png');
            if (is_wp_error($saved) || empty($saved['path']) || !is_file($saved['path'])) {
                return [
                    'ok' => false,
                    'message' => is_wp_error($saved) ? $saved->get_error_message() : 'ذخیره آیکون PNG ناموفق بود.',
                ];
            }

            $generated[(string) $size] = trailingslashit($upload['baseurl']) . 'bankai-pwa/icon-' . $size . '.png';
        }

        $settings['icon_192'] = esc_url_raw($generated['192']);
        $settings['icon_512'] = esc_url_raw($generated['512']);
        $this->save_settings($settings);

        return [
            'ok' => true,
            'message' => 'آیکون‌های ۱۹۲ و ۵۱۲ پیکسل ساخته شدند.',
            'icons' => $generated,
        ];
    }

    public function register_rest(): void
    {
        $ns = 'bankai/v1';
        register_rest_route($ns, '/pwa/generate-icons', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => function (WP_REST_Request $request) {
                    $params = $request->get_json_params();
                    if (!is_array($params)) {
                        $params = [];
                    }
                    $src = isset($params['source']) ? (string) $params['source'] : null;
                    $force = !empty($params['force']);
                    $result = $this->generate_icons_from_source($src ?: null, $force);
                    $code = !empty($result['ok']) ? 200 : 400;
                    return new WP_REST_Response([
                        'success' => !empty($result['ok']),
                        'data'    => array_merge($result, [
                            'settings'     => $this->get_settings(),
                            'manifest_url' => home_url('/manifest.webmanifest'),
                            'status'       => $this->status_report(),
                        ]),
                        'message' => $result['message'] ?? '',
                    ], $code);
                },
                'permission_callback' => static fn () => current_user_can('manage_options'),
            ],
        ]);
        register_rest_route($ns, '/pwa/settings', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => function () {
                    return new WP_REST_Response([
                        'success' => true,
                        'data'    => [
                            'settings' => $this->get_settings(),
                            'manifest' => $this->is_enabled() ? $this->build_manifest() : null,
                            'manifest_url' => home_url('/manifest.webmanifest'),
                            'sw_url' => home_url('/bankai-sw.js'),
                            'module_active' => $this->is_module_on(),
                            'status' => $this->status_report(),
                        ],
                    ]);
                },
                'permission_callback' => static fn () => current_user_can('manage_options'),
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => function (WP_REST_Request $request) {
                    $params = $request->get_json_params();
                    if (!is_array($params)) {
                        return new WP_REST_Response(['success' => false, 'message' => 'Invalid body'], 400);
                    }
                    $in = isset($params['settings']) && is_array($params['settings']) ? $params['settings'] : $params;
                    $saved = $this->save_settings($in);
                    return new WP_REST_Response([
                        'success' => true,
                        'data'    => [
                            'settings' => $saved,
                            'manifest_url' => home_url('/manifest.webmanifest'),
                            'sw_url' => home_url('/bankai-sw.js'),
                            'status' => $this->status_report(),
                        ],
                    ]);
                },
                'permission_callback' => static fn () => current_user_can('manage_options'),
            ],
        ]);

        register_rest_route($ns, '/pwa/status', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => function () {
                    return new WP_REST_Response([
                        'success' => true,
                        'data'    => $this->status_report(),
                    ]);
                },
                'permission_callback' => static fn () => current_user_can('manage_options'),
            ],
        ]);
    }
}
