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
        $icon = '';
        if (function_exists('get_site_icon_url')) {
            $icon = (string) get_site_icon_url(512);
        }

        return [
            'enabled'         => true,
            'name'            => $name,
            'short_name'      => mb_substr($name, 0, 12),
            'description'     => get_bloginfo('description') ?: $name,
            'start_url'       => '/',
            'scope'           => '/',
            'display'         => 'standalone',
            'orientation'     => 'any',
            'theme_color'     => '#fc4c2f',
            'background_color'=> '#ffffff',
            'icon_192'        => $icon,
            'icon_512'        => $icon,
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
                $path = sanitize_text_field((string) $in[$k]);
                $cur[$k] = $path !== '' ? $path : '/';
            } elseif ($k === 'short_name') {
                $cur[$k] = mb_substr(sanitize_text_field((string) $in[$k]), 0, 12);
            } else {
                $cur[$k] = sanitize_text_field((string) $in[$k]);
            }
        }

        if (function_exists('bankai_update_option')) {
            bankai_update_option(self::OPT, $cur);
        } else {
            update_option(self::OPT, $cur, false);
        }

        // Flush rewrite rules once when enabling so manifest/SW routes work
        if (!empty($cur['enabled'])) {
            flush_rewrite_rules(false);
        }

        return $cur;
    }

    public function is_enabled(): bool
    {
        $s = $this->get_settings();
        // First-run: if option never saved, treat as enabled so manifest/icons work out of the box
        $raw = get_option(self::OPT, null);
        if ($raw === null || $raw === false || $raw === '') {
            return true;
        }
        return !empty($s['enabled']);
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
        $icons = [];
        if (!empty($s['icon_192'])) {
            $icons[] = [
                'src'     => $s['icon_192'],
                'sizes'   => '192x192',
                'type'    => 'image/png',
                'purpose' => 'any',
            ];
        }
        if (!empty($s['icon_512'])) {
            $icons[] = [
                'src'     => $s['icon_512'],
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'any maskable',
            ];
        }
        if (!$icons) {
            $fallback = function_exists('get_site_icon_url') ? (string) get_site_icon_url(512) : '';
            if ($fallback) {
                $icons[] = [
                    'src'     => $fallback,
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ];
            }
        }

        return [
            'name'             => $s['name'] ?: get_bloginfo('name'),
            'short_name'       => $s['short_name'] ?: mb_substr(get_bloginfo('name'), 0, 12),
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

    private function output_manifest(): void
    {
        nocache_headers();
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('X-Robots-Tag: noindex');
        $data = $this->build_manifest();
        if (!$this->is_enabled()) {
            $data['display'] = 'browser';
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

        $cache_name = 'bankai-pwa-v' . (defined('BANKAI_CORE_VERSION') ? preg_replace('/[^a-zA-Z0-9_.-]/', '', BANKAI_CORE_VERSION) : '1');
        $offline    = !empty($s['offline_cache']);
        $start      = $this->abs_url($s['start_url'] ?: '/');

        $js = <<<JS
/* Bankai PWA Service Worker */
const CACHE = '{$cache_name}';
const OFFLINE = {$this->js_bool($offline)};
const START = '{$start}';

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
    await Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)));
    self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  if (req.url.indexOf(self.location.origin) !== 0) return;

  event.respondWith((async () => {
    try {
      const net = await fetch(req);
      if (OFFLINE && net && net.ok && req.destination === 'document') {
        const cache = await caches.open(CACHE);
        cache.put(req, net.clone());
      }
      return net;
    } catch (err) {
      if (OFFLINE) {
        const cache = await caches.open(CACHE);
        const hit = await cache.match(req) || await cache.match(START);
        if (hit) return hit;
      }
      throw err;
    }
  })());
});
JS;

        nocache_headers();
        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
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
        $icon = (string) ($s['icon_192'] ?: $s['icon_512'] ?? '');
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

        if (!$need_sw && !$need_prompt) {
            return;
        }

        echo '<script id="bankai-pwa-runtime">';
        if ($need_sw) {
            echo "(function(){if(!('serviceWorker' in navigator))return;window.addEventListener('load',function(){navigator.serviceWorker.register(" . wp_json_encode($sw_url) . ",{scope:'/'}).catch(function(){});});})();";
        }
        if ($need_prompt) {
            echo <<<'JS'
(function(){
  var deferred=null;
  window.addEventListener('beforeinstallprompt',function(e){
    e.preventDefault();
    deferred=e;
    if(document.getElementById('bankai-pwa-install'))return;
    var btn=document.createElement('button');
    btn.id='bankai-pwa-install';
    btn.type='button';
    btn.textContent='\u0646\u0635\u0628 \u0627\u067e\u0644\u06cc\u06a9\u06cc\u0634\u0646';
    btn.setAttribute('style','position:fixed;bottom:20px;left:20px;z-index:99999;background:#fc4c2f;color:#fff;border:0;border-radius:10px;padding:12px 18px;font:700 13px/1.2 sans-serif;box-shadow:0 8px 24px rgba(252,76,47,.35);cursor:pointer');
    btn.addEventListener('click',function(){
      if(!deferred)return;
      deferred.prompt();
      deferred.userChoice.finally(function(){deferred=null;btn.remove();});
    });
    document.body.appendChild(btn);
  });
})();
JS;
        }
        echo '</script>';
    }

    public function register_rest(): void
    {
        $ns = 'bankai/v1';
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
                            'module_active' => function_exists('bankai_is_module_active') ? bankai_is_module_active('pwa') : true,
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
                        ],
                    ]);
                },
                'permission_callback' => static fn () => current_user_can('manage_options'),
            ],
        ]);
    }
}
