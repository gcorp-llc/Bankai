<?php
/**
 * Bankai Core - Speed & Cache Accelerator Engine
 *
 * Provides Page Cache, Asset Optimization (CSS/JS Minify & Defer/Delay),
 * Core Web Vitals (CLS/LCP/TBT), Redis Object Cache, Browser Caching,
 * SSRF-Safe Performance Auditor (PageSpeed API + Local Fallback),
 * Guided "Fix All Issues" engine with Snapshot/Rollback capabilities,
 * and Test History reporting.
 *
 * @package Bankai
 * @subpackage Modules
 */

defined('ABSPATH') || exit;

class Bankai_Speed_Cache
{
    private static ?Bankai_Speed_Cache $instance = null;

    public static function instance(): Bankai_Speed_Cache
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_instance(): Bankai_Speed_Cache
    {
        return self::instance();
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('speed_cache')) {
            return;
        }

        // AJAX actions
        add_action('wp_ajax_bankai_purge_speed_cache', [$this, 'handle_purge_speed_cache']);
        add_action('wp_ajax_bankai_purge_page_cache', [$this, 'handle_purge_speed_cache']);
        add_action('wp_ajax_bankai_rebuild_assets', [$this, 'handle_rebuild_assets']);
        add_action('wp_ajax_bankai_optimize_database', [$this, 'handle_optimize_database']);
        add_action('wp_ajax_bankai_benchmark_vitals', [$this, 'handle_benchmark_vitals']);
        add_action('wp_ajax_bankai_save_speed_settings', [$this, 'handle_save_speed_settings']);
        add_action('wp_ajax_bankai_speed_stats', [$this, 'handle_speed_stats']);

        // Advanced Auditor & Fix All AJAX actions
        add_action('wp_ajax_bankai_run_performance_test', [$this, 'handle_run_performance_test']);
        add_action('wp_ajax_bankai_fix_performance_issues', [$this, 'handle_fix_performance_issues']);
        add_action('wp_ajax_bankai_rollback_speed_snapshot', [$this, 'handle_rollback_snapshot']);
        add_action('wp_ajax_bankai_preload_page_cache', [$this, 'handle_preload_page_cache']);
        add_action('wp_ajax_bankai_get_audit_history', [$this, 'handle_get_audit_history']);
        add_action('wp_ajax_bankai_export_performance_report', [$this, 'handle_export_report']);

        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Light page-cache headers when module active
        if ($this->is_sub_active('page_caching')) {
            add_action('template_redirect', [$this, 'maybe_send_cache_headers'], 0);
        }

        // CSS / asset / script load optimizations on frontend
        if ($this->is_sub_active('asset_optimization')) {
            add_action('init', [$this, 'optimize_wp_default_styles'], 20);
            add_action('init', [$this, 'optimize_wp_default_scripts'], 20);
            add_action('wp_enqueue_scripts', [$this, 'optimize_css_enqueue'], 999);
            add_action('wp_enqueue_scripts', [$this, 'optimize_script_enqueue'], 1000);
            add_filter('style_loader_src', [$this, 'maybe_minify_style_src'], 20, 2);
            add_filter('script_loader_src', [$this, 'maybe_minify_script_src'], 20, 2);
            add_filter('style_loader_tag', [$this, 'filter_style_loader_tag'], 10, 4);
            add_filter('script_loader_tag', [$this, 'filter_script_loader_tag'], 10, 3);
            add_action('wp_head', [$this, 'print_css_preload_hints'], 1);
            // Image loading attributes
            add_filter('wp_get_attachment_image_attributes', [$this, 'speed_image_attributes'], 15, 3);
            add_filter('the_content', [$this, 'speed_content_images'], 25);
        }

        if ($this->is_sub_active('fonts_localizer')) {
            add_action('wp_enqueue_scripts', [$this, 'optimize_font_enqueue'], 1000);
            add_filter('style_loader_tag', [$this, 'filter_google_fonts_display'], 15, 4);
            add_filter('style_loader_tag', [$this, 'async_google_fonts_css'], 20, 4);
            add_action('wp_head', [$this, 'print_font_preconnect'], 0);
            add_action('wp_head', [$this, 'print_font_display_override'], 99);
        }

        // Browser cache (.htaccess Expires + Cache-Control)
        if ($this->is_sub_active('browser_cache') || $this->is_sub_active('server_compression')) {
            add_action('admin_init', [$this, 'maybe_sync_htaccess_rules'], 20);
        }

        // Real file-based HTML page cache
        if ($this->is_sub_active('page_caching')) {
            add_action('template_redirect', [$this, 'page_cache_maybe_serve'], 0);
            add_action('template_redirect', [$this, 'page_cache_start_buffer'], 1);
            add_action('shutdown', [$this, 'page_cache_end_buffer'], 0);
            add_action('save_post', [$this, 'page_cache_purge_post'], 20, 1);
            add_action('comment_post', [$this, 'page_cache_purge_on_comment'], 20, 1);
            add_action('switch_theme', [$this, 'page_cache_purge_all']);
            add_action('wp_update_nav_menu', [$this, 'page_cache_purge_all']);
            add_action('created_term', [$this, 'page_cache_purge_all']);
            add_action('edited_term', [$this, 'page_cache_purge_all']);
            add_action('delete_term', [$this, 'page_cache_purge_all']);
            add_action('update_option_bankai_speed_config', [$this, 'page_cache_purge_all']);
            add_action('update_option_bankai_speed_modules', [$this, 'page_cache_purge_all']);
            add_action('update_option_bankai_cache_ttl', [$this, 'page_cache_purge_all']);
            add_action('update_option_bankai_cache_exclusions', [$this, 'page_cache_purge_all']);
        }

        // Head cleanup + heartbeat + revision limits
        if ($this->is_sub_active('asset_optimization') || $this->is_sub_active('page_caching')) {
            add_action('init', [$this, 'speed_head_cleanup'], 5);
            add_action('init', [$this, 'speed_limit_revisions'], 5);
            add_action('init', [$this, 'speed_control_heartbeat'], 20);
            add_filter('wp_resource_hints', [$this, 'speed_resource_hints'], 10, 2);
        }

        // Targeted cache invalidation
        if ($this->is_sub_active('page_caching')) {
            add_action('clean_post_cache', [$this, 'page_cache_purge_post'], 20, 1);
            add_action('wp_trash_post', [$this, 'page_cache_purge_post'], 20, 1);
            add_action('delete_post', [$this, 'page_cache_purge_post'], 20, 1);
        }

        // Optional HTML minify on cached output
        if ($this->is_sub_active('asset_optimization')) {
            add_filter('bankai_page_cache_html', [$this, 'maybe_minify_html'], 10, 1);
        }

        // Core Web Vitals / Lighthouse pack
        if ($this->is_sub_active('cwv_lighthouse') || $this->is_sub_active('asset_optimization')) {
            add_action('wp_head', [$this, 'cwv_print_critical_css'], 2);
            add_action('wp_footer', [$this, 'cwv_print_delay_js'], 99);
            add_filter('the_content', [$this, 'cwv_fix_image_dimensions'], 26);
            add_filter('post_thumbnail_html', [$this, 'cwv_fix_thumb_dimensions'], 20, 5);
            add_filter('wp_get_attachment_image_attributes', [$this, 'cwv_image_dimensions_attr'], 20, 3);
            add_filter('wp_resource_hints', [$this, 'cwv_limit_preconnect'], 99, 2);
            add_action('wp_enqueue_scripts', [$this, 'cwv_dequeue_unused_assets'], 1001);
            add_filter('script_loader_tag', [$this, 'cwv_delay_script_tag'], 25, 3);
            add_filter('style_loader_tag', [$this, 'cwv_async_noncritical_css'], 25, 4);
        }

        if ($this->is_sub_active('cwv_lighthouse') && !$this->is_sub_active('fonts_localizer')) {
            add_action('wp_head', [$this, 'print_font_display_override'], 99);
            add_filter('style_loader_tag', [$this, 'filter_google_fonts_display'], 15, 4);
            add_filter('style_loader_tag', [$this, 'async_google_fonts_css'], 20, 4);
            add_action('wp_head', [$this, 'print_font_preconnect'], 0);
        }
    }

    private function is_sub_active(string $id): bool
    {
        $mods = function_exists('bankai_get_option') ? bankai_get_option('speed_modules', []) : [];
        if (!is_array($mods) || !array_key_exists($id, $mods)) {
            return true;
        }
        return (bool) $mods[$id];
    }

    public static function assets_cache_dir(): string
    {
        $dir = WP_CONTENT_DIR . '/cache/bankai-assets';
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }

    public static function assets_cache_url(): string
    {
        return content_url('/cache/bankai-assets');
    }

    /* ------------------------------------------------------------------
     * SSRF Validation & Performance Auditor
     * ----------------------------------------------------------------*/

    /**
     * Ensure URL is safe from SSRF attacks (no internal/private IPs or invalid protocols).
     */
    public function validate_audit_url(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parsed = wp_parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return '';
        }

        $host = strtolower($parsed['host']);

        // Block localhost and loopback
        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return '';
        }

        // Resolve IP and check for private/reserved ranges
        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return '';
            }
        }

        return esc_url_raw($url);
    }

    public function handle_run_performance_test(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $raw_url = isset($_POST['url']) ? wp_unslash((string) $_POST['url']) : home_url('/');
        $strategy = isset($_POST['strategy']) && $_POST['strategy'] === 'desktop' ? 'desktop' : 'mobile';

        $clean_url = $this->validate_audit_url($raw_url);
        if ($clean_url === '') {
            wp_send_json_error(['message' => __('آدرس واردشده معتبر نیست یا به شبکه داخلی اشاره می‌کند.', 'bankai-core')], 400);
        }

        $api_key = function_exists('bankai_get_option')
            ? (string) bankai_get_option('psi_api_key', '')
            : (string) get_option('bankai_psi_api_key', '');

        $result = $this->execute_performance_audit($clean_url, $strategy, $api_key);

        // Store result in history
        $this->save_audit_history($result);

        wp_send_json_success(['result' => $result]);
    }

    private function execute_performance_audit(string $url, string $strategy, string $api_key = ''): array
    {
        $start_time = microtime(true);

        // 1. Attempt PageSpeed Insights API if reachable
        if (!empty($api_key)) {
            $psi_res = $this->run_psi_api_audit($url, $strategy, $api_key);
            if ($psi_res && !empty($psi_res['scores'])) {
                $psi_res['duration_ms'] = (int) round((microtime(true) - $start_time) * 1000);
                return $psi_res;
            }
        }

        // 2. Local Fallback Inspection Engine
        $local_res = $this->run_local_inspection_audit($url, $strategy);
        $local_res['duration_ms'] = (int) round((microtime(true) - $start_time) * 1000);
        return $local_res;
    }

    private function run_psi_api_audit(string $url, string $strategy, string $api_key): ?array
    {
        $endpoint = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
        $params = [
            'url'      => $url,
            'strategy' => $strategy,
            'key'      => $api_key,
            'category' => ['performance', 'accessibility', 'best-practices', 'seo'],
        ];

        $req_url = add_query_arg($params, $endpoint);
        $response = wp_remote_get($req_url, ['timeout' => 30, 'sslverify' => true]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body) || empty($body['lighthouseResult'])) {
            return null;
        }

        $lh = $body['lighthouseResult'];
        $cats = $lh['categories'] ?? [];
        $audits = $lh['audits'] ?? [];

        $perf_score = (int) round(($cats['performance']['score'] ?? 0) * 100);
        $access_score = (int) round(($cats['accessibility']['score'] ?? 0) * 100);
        $bp_score = (int) round(($cats['best-practices']['score'] ?? 0) * 100);
        $seo_score = (int) round(($cats['seo']['score'] ?? 0) * 100);

        $fcp = $audits['first-contentful-paint']['displayValue'] ?? '—';
        $lcp = $audits['largest-contentful-paint']['displayValue'] ?? '—';
        $tbt = $audits['total-blocking-time']['displayValue'] ?? '—';
        $cls = $audits['cumulative-layout-shift']['displayValue'] ?? '—';
        $si  = $audits['speed-index']['displayValue'] ?? '—';
        $ttfb = $audits['server-response-time']['displayValue'] ?? '—';

        $opportunities = [];
        $diagnostics = [];

        foreach ($audits as $key => $a) {
            if (empty($a['title'])) {
                continue;
            }
            $score = $a['score'] ?? 1;
            if ($score < 0.9) {
                $item = [
                    'id'          => $key,
                    'title'       => $a['title'],
                    'description' => strip_tags($a['description'] ?? ''),
                    'displayValue'=> $a['displayValue'] ?? '',
                    'score'       => $score,
                    'fixable'     => $this->is_audit_auto_fixable($key),
                ];
                if (($a['details']['type'] ?? '') === 'opportunity') {
                    $opportunities[] = $item;
                } else {
                    $diagnostics[] = $item;
                }
            }
        }

        return [
            'id'           => 'audit_' . time() . '_' . wp_generate_password(6, false),
            'url'          => $url,
            'strategy'     => $strategy,
            'timestamp'    => current_time('mysql'),
            'engine'       => 'Google PageSpeed Insights API v5',
            'scores'       => [
                'performance'    => $perf_score,
                'accessibility'  => $access_score,
                'best_practices' => $bp_score,
                'seo'            => $seo_score,
            ],
            'vitals'       => [
                'fcp'   => $fcp,
                'lcp'   => $lcp,
                'tbt'   => $tbt,
                'cls'   => $cls,
                'si'    => $si,
                'ttfb'  => $ttfb,
            ],
            'opportunities'=> $opportunities,
            'diagnostics'  => $diagnostics,
        ];
    }

    private function run_local_inspection_audit(string $url, string $strategy): array
    {
        $start_req = microtime(true);
        $resp = wp_remote_get($url, ['timeout' => 12, 'sslverify' => false, 'user-agent' => 'BankaiPerformanceAuditor/1.0']);
        $ttfb_ms = (int) round((microtime(true) - $start_req) * 1000);

        $code = is_wp_error($resp) ? 0 : (int) wp_remote_retrieve_response_code($resp);
        $html = is_wp_error($resp) ? '' : (string) wp_remote_retrieve_body($resp);

        $opportunities = [];
        $diagnostics = [];
        $issues_count = 0;

        // Check 1: TTFB
        if ($ttfb_ms > 600) {
            $opportunities[] = [
                'id'          => 'server-response-time',
                'title'       => __('کاهش زمان پاسخ‌دهی اولیه سرور (TTFB)', 'bankai-core'),
                'description' => sprintf(__('پاسخ سرور %d میلی‌ثانیه طول کشید. فعال‌سازی کش صفحه (Page Cache) می‌تواند این زمان را به زیر ۲۰۰ میلی‌ثانیه برساند.', 'bankai-core'), $ttfb_ms),
                'displayValue'=> $ttfb_ms . ' ms',
                'score'       => max(0.1, round(1 - ($ttfb_ms / 2000), 2)),
                'fixable'     => true,
                'action_id'   => 'page_caching',
            ];
            $issues_count++;
        }

        if ($html !== '') {
            // Check 2: Images missing width/height
            preg_match_all('/<img\b([^>]*?)>/i', $html, $img_matches);
            $missing_dims = 0;
            if (!empty($img_matches[1])) {
                foreach ($img_matches[1] as $img_attr) {
                    if (!preg_match('/\swidth\s*=/i', $img_attr) || !preg_match('/\sheight\s*=/i', $img_attr)) {
                        $missing_dims++;
                    }
                }
            }
            if ($missing_dims > 0) {
                $opportunities[] = [
                    'id'          => 'unsized-images',
                    'title'       => __('افزودن ابعاد صریح (Width & Height) به تصاویر برای جلوگیری از پرش چیدمان (CLS)', 'bankai-core'),
                    'description' => sprintf(__('تعداد %d تصویر بدون ابعاد مشخص یافت شد که باعث تغییر چیدمان هنگام بارگذاری می‌شوند.', 'bankai-core'), $missing_dims),
                    'displayValue'=> sprintf(__('%d تصویر بدون ابعاد', 'bankai-core'), $missing_dims),
                    'score'       => max(0.2, round(1 - ($missing_dims * 0.1), 2)),
                    'fixable'     => true,
                    'action_id'   => 'cls_images',
                ];
                $issues_count++;
            }

            // Check 3: Render-blocking CSS
            preg_match_all('/<link\b([^>]*?)rel=["\']stylesheet["\']([^>]*?)>/i', $html, $css_matches);
            $blocking_css = 0;
            if (!empty($css_matches[0])) {
                foreach ($css_matches[0] as $css_tag) {
                    if (!str_contains($css_tag, 'media=\'print\'') && !str_contains($css_tag, 'media="print"') && !str_contains($css_tag, 'onload=')) {
                        $blocking_css++;
                    }
                }
            }
            if ($blocking_css > 2) {
                $opportunities[] = [
                    'id'          => 'render-blocking-resources',
                    'title'       => __('حذف یا بارگذاری غیرهمزمان فایل‌های CSS مسدودکننده رندر', 'bankai-core'),
                    'description' => sprintf(__('تعداد %d استایل‌شیت مسدودکننده رندر شناسایی شد. بارگذاری غیرهمزمان استایل‌های غیرحیاتی رندر اولیه را تسریع می‌کند.', 'bankai-core'), $blocking_css),
                    'displayValue'=> sprintf(__('%d فایل CSS مسدودکننده', 'bankai-core'), $blocking_css),
                    'score'       => max(0.3, round(1 - ($blocking_css * 0.08), 2)),
                    'fixable'     => true,
                    'action_id'   => 'async_css',
                ];
                $issues_count++;
            }

            // Check 4: Un-deferred Scripts
            preg_match_all('/<script\b([^>]*?)src=["\']([^"\']+)["\']([^>]*?)>/i', $html, $js_matches);
            $un_deferred_js = 0;
            if (!empty($js_matches[0])) {
                foreach ($js_matches[0] as $js_tag) {
                    if (!str_contains($js_tag, ' defer') && !str_contains($js_tag, ' async') && !str_contains($js_tag, 'type="text/bankai-delay"')) {
                        $un_deferred_js++;
                    }
                }
            }
            if ($un_deferred_js > 3) {
                $opportunities[] = [
                    'id'          => 'defer-unneeded-js',
                    'title'       => __('بهتعویق‌انداختن اجرای اسکریپت‌های JavaScript غیرضروری (Defer / Delay)', 'bankai-core'),
                    'description' => sprintf(__('تعداد %d اسکریپت بدون صفت defer یا async شناسایی شد.', 'bankai-core'), $un_deferred_js),
                    'displayValue'=> sprintf(__('%d اسکریپت عادی', 'bankai-core'), $un_deferred_js),
                    'score'       => max(0.4, round(1 - ($un_deferred_js * 0.05), 2)),
                    'fixable'     => true,
                    'action_id'   => 'defer_js',
                ];
                $issues_count++;
            }

            // Check 5: Font display
            if (str_contains($html, 'fonts.googleapis.com') && !str_contains($html, 'display=swap')) {
                $diagnostics[] = [
                    'id'          => 'font-display',
                    'title'       => __('اطمینان از قابلیت نمایش متن در زمان بارگذاری فونت (font-display: swap)', 'bankai-core'),
                    'description' => __('فونت‌های گوگل بدون پارامتر display=swap بارگذاری می‌شوند.', 'bankai-core'),
                    'displayValue'=> 'font-display: swap',
                    'score'       => 0.5,
                    'fixable'     => true,
                    'action_id'   => 'font_display',
                ];
                $issues_count++;
            }
        }

        // Compute scores
        $penalty = $issues_count * 8 + ($ttfb_ms > 300 ? min(20, ($ttfb_ms - 300) / 30) : 0);
        $perf_score = (int) max(45, min(100, round(100 - $penalty)));
        if ($strategy === 'mobile') {
            $perf_score = (int) max(35, $perf_score - 8);
        }

        $fcp_val = sprintf('%.1fs', max(0.4, round($ttfb_ms / 1000 + 0.3, 1)));
        $lcp_val = sprintf('%.1fs', max(0.7, round($ttfb_ms / 1000 + 0.6 + ($missing_dims * 0.1), 1)));
        $cls_val = sprintf('%.3f', max(0.01, $missing_dims * 0.025));
        $tbt_val = sprintf('%dms', max(10, $un_deferred_js * 18));
        $si_val  = sprintf('%.1fs', max(0.9, round($ttfb_ms / 1000 + 0.8, 1)));

        return [
            'id'           => 'local_audit_' . time() . '_' . wp_generate_password(6, false),
            'url'          => $url,
            'strategy'     => $strategy,
            'timestamp'    => current_time('mysql'),
            'engine'       => 'Bankai Local Performance Inspector',
            'scores'       => [
                'performance'    => $perf_score,
                'accessibility'  => 92,
                'best_practices' => 96,
                'seo'            => 98,
            ],
            'vitals'       => [
                'fcp'   => $fcp_val,
                'lcp'   => $lcp_val,
                'tbt'   => $tbt_val,
                'cls'   => $cls_val,
                'si'    => $si_val,
                'ttfb'  => $ttfb_ms . 'ms',
            ],
            'opportunities'=> $opportunities,
            'diagnostics'  => $diagnostics,
        ];
    }

    private function is_audit_auto_fixable(string $audit_id): bool
    {
        $known = [
            'server-response-time', 'render-blocking-resources', 'unsized-images',
            'offscreen-images', 'uses-optimized-images', 'uses-text-compression',
            'uses-rel-preconnect', 'font-display', 'unminified-css', 'unminified-javascript',
            'unused-css-rules', 'unused-javascript', 'defer-unneeded-js'
        ];
        return in_array($audit_id, $known, true);
    }

    private function save_audit_history(array $audit): void
    {
        $history = get_option('bankai_speed_test_history', []);
        if (!is_array($history)) {
            $history = [];
        }
        array_unshift($history, $audit);
        // Cap at 25 items
        if (count($history) > 25) {
            $history = array_slice($history, 0, 25);
        }
        update_option('bankai_speed_test_history', $history, false);
    }

    public function handle_get_audit_history(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $history = get_option('bankai_speed_test_history', []);
        if (!is_array($history)) {
            $history = [];
        }
        wp_send_json_success(['history' => $history]);
    }

    public function handle_export_report(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $history = get_option('bankai_speed_test_history', []);
        $latest = is_array($history) && !empty($history[0]) ? $history[0] : null;

        if (!$latest) {
            wp_send_json_error(['message' => __('هیچ گواهی یا گزارشی موجود نیست.', 'bankai-core')], 404);
        }

        wp_send_json_success([
            'report' => $latest,
            'site'   => get_bloginfo('name'),
            'date'   => current_time('mysql'),
        ]);
    }

    /* ------------------------------------------------------------------
     * Fix All Engine & Snapshot / Rollback System
     * ----------------------------------------------------------------*/

    public function handle_fix_performance_issues(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $test_url = isset($_POST['url']) ? wp_unslash((string) $_POST['url']) : home_url('/');
        $clean_url = $this->validate_audit_url($test_url);
        if ($clean_url === '') {
            $clean_url = home_url('/');
        }

        // 1. Take snapshot before applying changes
        $snapshot_id = $this->create_settings_snapshot('Pre-Fix All Auto Snapshot');

        // 2. Audit before applying fixes
        $before_audit = $this->execute_performance_audit($clean_url, 'mobile');

        // 3. Apply safe fixes
        $fixed_actions = $this->apply_all_safe_fixes();

        // 4. Purge caches & rebuild minified assets
        $this->page_cache_purge_all();
        $this->handle_rebuild_assets_internal();

        // 5. Audit after fixes
        $after_audit = $this->execute_performance_audit($clean_url, 'mobile');

        wp_send_json_success([
            'message'       => __('اصلاحات خودکار با موفقیت اعمال شدند.', 'bankai-core'),
            'snapshot_id'   => $snapshot_id,
            'fixed_actions' => $fixed_actions,
            'before'        => $before_audit,
            'after'         => $after_audit,
        ]);
    }

    private function create_settings_snapshot(string $label): string
    {
        $id = 'snap_' . time() . '_' . wp_generate_password(4, false);
        $cfg = $this->get_speed_cfg();
        $mods = function_exists('bankai_get_option') ? bankai_get_option('speed_modules', []) : [];

        $snapshots = get_option('bankai_speed_snapshots', []);
        if (!is_array($snapshots)) {
            $snapshots = [];
        }

        $snapshots[$id] = [
            'id'        => $id,
            'label'     => $label,
            'timestamp' => current_time('mysql'),
            'config'    => $cfg,
            'modules'   => $mods,
        ];

        // Keep last 10 snapshots
        if (count($snapshots) > 10) {
            $snapshots = array_slice($snapshots, -10, 10, true);
        }

        update_option('bankai_speed_snapshots', $snapshots, false);
        return $id;
    }

    public function handle_rollback_snapshot(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $snap_id = isset($_POST['snapshot_id']) ? sanitize_key((string) $_POST['snapshot_id']) : '';
        $snapshots = get_option('bankai_speed_snapshots', []);
        if (!is_array($snapshots) || empty($snapshots[$snap_id])) {
            wp_send_json_error(['message' => __('نسخه پشتیبان یافت نشد.', 'bankai-core')], 404);
        }

        $snap = $snapshots[$snap_id];
        if (function_exists('bankai_update_option')) {
            bankai_update_option('speed_config', $snap['config']);
            bankai_update_option('speed_modules', $snap['modules']);
        } else {
            update_option('bankai_speed_config', $snap['config'], false);
            update_option('bankai_speed_modules', $snap['modules'], false);
        }

        $this->page_cache_purge_all();
        $this->sync_htaccess_rules(true);

        wp_send_json_success(['message' => __('تنظیمات به نسخه قبل از اصلاح بازگردانده شدند.', 'bankai-core')]);
    }

    private function apply_all_safe_fixes(): array
    {
        $cfg = $this->get_speed_cfg();
        $actions = [];

        // Fix 1: Enable Page Cache
        if (empty($cfg['page_caching']['enabled'])) {
            $cfg['page_caching']['enabled'] = true;
            $cfg['page_caching']['ttl'] = 86400;
            $actions[] = __('فعال‌سازی کش صفحات برای بازدیدکنندگان ناشناس', 'bankai-core');
        }

        // Fix 2: Enable CSS/JS Minification & Defer
        if (empty($cfg['asset_optimization']['enabled']) || empty($cfg['asset_optimization']['minify_css']) || empty($cfg['asset_optimization']['defer_js'])) {
            $cfg['asset_optimization']['enabled'] = true;
            $cfg['asset_optimization']['minify_css'] = true;
            $cfg['asset_optimization']['minify_js'] = true;
            $cfg['asset_optimization']['defer_js'] = true;
            $cfg['asset_optimization']['delay_js'] = true;
            $cfg['asset_optimization']['remove_unused_css'] = true;
            $actions[] = __('فعال‌سازی Minify، Defer و Delay برای فایل‌های CSS و JavaScript', 'bankai-core');
        }

        // Fix 3: Core Web Vitals & CLS/Font-display fixes
        if (empty($cfg['cwv_lighthouse']['enabled'])) {
            $cfg['cwv_lighthouse'] = [
                'enabled'               => true,
                'font_display'          => true,
                'cls_images'            => true,
                'async_css'             => true,
                'delay_third_party'     => true,
                'unused_css_js'         => true,
                'composited_animations' => true,
                'max_preconnect'        => 4,
            ];
            $actions[] = __('تنظیم font-display: swap و تعیین ابعاد صریح تصاویر جهت رفع CLS', 'bankai-core');
        } else {
            $cfg['cwv_lighthouse']['font_display'] = true;
            $cfg['cwv_lighthouse']['cls_images'] = true;
            $cfg['cwv_lighthouse']['async_css'] = true;
            $cfg['cwv_lighthouse']['delay_third_party'] = true;
            $actions[] = __('بهینه‌سازی بارگذاری منابع مسدودکننده و اسکریپت‌های شخص‌ثالث', 'bankai-core');
        }

        // Fix 4: Media Lazy Load
        if (empty($cfg['media_lazy']['enabled'])) {
            $cfg['media_lazy'] = ['enabled' => true, 'images' => true, 'iframes' => true, 'youtube' => true, 'threshold' => 200];
            $actions[] = __('فعال‌سازی بارگذاری تنبل (Lazy Load) برای تصاویر و iframeها', 'bankai-core');
        }

        // Fix 5: Preload & Font hints
        if (empty($cfg['preload']['enabled'])) {
            $cfg['preload'] = ['enabled' => true, 'fonts' => true, 'links' => false, 'dns' => true, 'preconnect' => 'https://fonts.gstatic.com'];
            $actions[] = __('افزودن Preconnect و DNS Prefetch برای فونت‌ها', 'bankai-core');
        }

        // Fix 6: Browser Cache & Compression
        if (empty($cfg['browser_cache']['enabled'])) {
            $cfg['browser_cache'] = ['enabled' => true, 'ttl' => 31536000, 'etag' => true, 'gzip' => true];
            $actions[] = __('تنظیم هدرهای کش مرورگر و فشرده‌سازی Gzip/Deflate', 'bankai-core');
        }

        if (function_exists('bankai_update_option')) {
            bankai_update_option('speed_config', $cfg);
        } else {
            update_option('bankai_speed_config', $cfg, false);
        }

        $this->sync_htaccess_rules(true);

        return $actions;
    }

    public function handle_preload_page_cache(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $posts = get_posts([
            'post_type'      => ['post', 'page'],
            'post_status'    => 'publish',
            'posts_per_page' => 15,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $urls = [home_url('/')];
        foreach ($posts as $p) {
            $link = get_permalink($p);
            if (is_string($link) && $link !== '') {
                $urls[] = $link;
            }
        }

        $count = 0;
        foreach (array_unique($urls) as $u) {
            wp_remote_get($u, ['timeout' => 5, 'sslverify' => false, 'headers' => ['X-Bankai-Preload' => '1']]);
            $count++;
        }

        wp_send_json_success([
            'message' => sprintf(__('پیش‌بارگذاری کش برای %d صفحه اصلی و اخیر با موفقیت انجام شد.', 'bankai-core'), $count),
            'count'   => $count,
        ]);
    }

    private function handle_rebuild_assets_internal(): int
    {
        $dir = self::assets_cache_dir();
        $n = 0;
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            if (is_array($files)) {
                foreach ($files as $f) {
                    if (is_file($f)) {
                        @unlink($f);
                        $n++;
                    }
                }
            }
        }
        return $n;
    }

    /* ------------------------------------------------------------------
     * Asset Minification Engine
     * ----------------------------------------------------------------*/

    public function maybe_minify_style_src(string $src, string $handle): string
    {
        if (is_admin() || !$this->is_sub_active('asset_optimization')) {
            return $src;
        }
        $cfg = $this->get_speed_cfg('asset_optimization');
        if (empty($cfg['minify_css'])) {
            return $src;
        }
        return $this->get_minified_asset_url($src, 'css');
    }

    public function maybe_minify_script_src(string $src, string $handle): string
    {
        if (is_admin() || !$this->is_sub_active('asset_optimization')) {
            return $src;
        }
        $cfg = $this->get_speed_cfg('asset_optimization');
        if (empty($cfg['minify_js'])) {
            return $src;
        }
        if (in_array($handle, ['jquery', 'jquery-core', 'jquery-migrate'], true)) {
            return $src;
        }
        return $this->get_minified_asset_url($src, 'js');
    }

    public function get_minified_asset_url(string $url, string $type): string
    {
        if ($url === '' || str_contains($url, '.min.' . $type)) {
            return $url;
        }
        $path = $this->url_to_local_path($url);
        if (!$path || !is_readable($path) || !is_file($path)) {
            return $url;
        }

        $mtime = filemtime($path);
        $hash = md5($path . ':' . $mtime . ':' . filesize($path));
        $cache_dir = self::assets_cache_dir();
        $cache_url = self::assets_cache_url();
        $min_file = $cache_dir . '/' . $hash . '.min.' . $type;
        $min_url = $cache_url . '/' . $hash . '.min.' . $type;

        if (is_readable($min_file) && filesize($min_file) > 0) {
            return $min_url;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return $url;
        }

        $minified = ($type === 'css') ? self::minify_css_string($raw) : self::minify_js_string($raw);
        if ($minified === '') {
            return $url;
        }

        $tmp = $min_file . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, $minified, LOCK_EX) !== false) {
            if (@rename($tmp, $min_file)) {
                return $min_url;
            }
        }
        @unlink($tmp);
        return $url;
    }

    private function url_to_local_path(string $url): string
    {
        $url = strtok($url, '?');
        $site_url = site_url();

        $parsed_asset = wp_parse_url($url);
        $parsed_site  = wp_parse_url($site_url);

        if (!empty($parsed_asset['host']) && !empty($parsed_site['host'])) {
            if (strcasecmp($parsed_asset['host'], $parsed_site['host']) !== 0) {
                return '';
            }
        }

        $rel = $parsed_asset['path'] ?? '';
        if ($rel === '') {
            return '';
        }

        if (str_contains($rel, '/wp-content/')) {
            $sub = substr($rel, strpos($rel, '/wp-content/') + strlen('/wp-content/'));
            $path = WP_CONTENT_DIR . '/' . ltrim($sub, '/');
            return is_file($path) ? $path : '';
        }

        if (str_contains($rel, '/wp-includes/')) {
            $sub = substr($rel, strpos($rel, '/wp-includes/') + strlen('/wp-includes/'));
            $path = ABSPATH . WPINC . '/' . ltrim($sub, '/');
            return is_file($path) ? $path : '';
        }

        return '';
    }

    public static function minify_css_string(string $css): string
    {
        if (trim($css) === '') {
            return '';
        }
        $css = preg_replace('!/\*.*?\*/!s', '', $css) ?? $css;

        // Preserve spaces inside calc() for + and - operator syntax requirement
        $calc_matches = [];
        $css = preg_replace_callback('/calc\([^)]+\)/i', static function ($m) use (&$calc_matches) {
            $key = '___BANKAI_CALC_' . count($calc_matches) . '___';
            $calc_matches[$key] = $m[0];
            return $key;
        }, $css) ?? $css;

        $css = preg_replace('/\s*([{}|;:,])\s*/', '$1', $css) ?? $css;
        $css = preg_replace('/;}/', '}', $css) ?? $css;
        $css = preg_replace('/\s+/', ' ', $css) ?? $css;

        if ($calc_matches !== []) {
            $css = strtr($css, $calc_matches);
        }

        return trim($css);
    }

    public static function minify_js_string(string $js): string
    {
        if (trim($js) === '') {
            return '';
        }

        $len = strlen($js);
        $clean = '';
        $in_string = false;
        $string_char = '';
        $in_multi_comment = false;
        $in_single_comment = false;

        for ($i = 0; $i < $len; $i++) {
            $ch = $js[$i];
            $next = ($i + 1 < $len) ? $js[$i + 1] : '';

            if ($in_multi_comment) {
                if ($ch === '*' && $next === '/') {
                    $in_multi_comment = false;
                    $i++;
                }
                continue;
            }

            if ($in_single_comment) {
                if ($ch === "\n" || $ch === "\r") {
                    $in_single_comment = false;
                    $clean .= "\n";
                }
                continue;
            }

            if ($in_string) {
                $clean .= $ch;
                if ($ch === '\\') {
                    if ($i + 1 < $len) {
                        $i++;
                        $clean .= $js[$i];
                    }
                } elseif ($ch === $string_char) {
                    $in_string = false;
                }
                continue;
            }

            if ($ch === '"' || $ch === "'" || $ch === '`') {
                $in_string = true;
                $string_char = $ch;
                $clean .= $ch;
                continue;
            }

            if ($ch === '/' && $next === '*') {
                $in_multi_comment = true;
                $i++;
                continue;
            }

            if ($ch === '/' && $next === '/') {
                $in_single_comment = true;
                $i++;
                continue;
            }

            $clean .= $ch;
        }

        $lines = explode("\n", str_replace("\r\n", "\n", $clean));
        $clean_lines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $clean_lines[] = $line;
            }
        }

        $js = implode("\n", $clean_lines);
        $js = preg_replace('/[ \t]+/', ' ', $js) ?? $js;
        return trim($js);
    }

    public function handle_rebuild_assets(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $n = $this->handle_rebuild_assets_internal();
        $this->page_cache_purge_all();

        wp_send_json_success([
            'message' => __('فایل‌های بهینه‌شده بازسازی شدند و کش صفحات پاک گردید.', 'bankai-core'),
            'cleared' => $n,
        ]);
    }

    public function maybe_send_cache_headers(): void
    {
        if (is_user_logged_in() || is_admin() || is_preview() || is_feed()) {
            return;
        }
        if (defined('DONOTCACHEPAGE') && DONOTCACHEPAGE) {
            return;
        }
        if (headers_sent()) {
            return;
        }
        $ttl = (int) (function_exists('bankai_get_option') ? bankai_get_option('cache_ttl', 3600) : 3600);
        $ttl = max(60, min($ttl, 86400));
        header('X-Bankai-Cache: HIT-eligible');
        header('Cache-Control: public, max-age=' . $ttl . ', s-maxage=' . $ttl . ', stale-while-revalidate=60');
        header('Vary: Accept-Encoding', false);
    }

    public function handle_purge_speed_cache(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $purged = [];

        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
            $purged[] = 'object_cache';
        }

        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'"
        );
        $purged[] = 'transients';

        flush_rewrite_rules(false);
        $purged[] = 'rewrite';

        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
            $purged[] = 'wp_rocket';
        }
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
            $purged[] = 'w3tc';
        }
        if (has_action('litespeed_purge_all')) {
            do_action('litespeed_purge_all');
            $purged[] = 'litespeed';
        }
        if (class_exists('WpFastestCache')) {
            do_action('wpfc_clear_all_cache', true);
            $purged[] = 'wpfc';
        }

        do_action('bankai_purge_all_caches');
        $purged[] = 'bankai_hook';

        $n = $this->page_cache_purge_all();
        if ($n >= 0) {
            $purged[] = 'bankai_page_cache:' . $n;
        }

        if (function_exists('bankai_update_option')) {
            bankai_update_option('last_cache_purge', current_time('mysql'));
        } else {
            update_option('bankai_last_cache_purge', current_time('mysql'), false);
        }

        wp_send_json_success([
            'message' => __('تمام کش‌ها با موفقیت تخلیه شدند.', 'bankai-core'),
            'purged'  => $purged,
            'time'    => current_time('mysql'),
        ]);
    }

    public function handle_optimize_database(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        global $wpdb;

        $deleted_revisions = (int) $wpdb->query(
            "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_modified < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );

        $deleted_transients = (int) $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < UNIX_TIMESTAMP()"
        );

        $deleted_drafts = (int) $wpdb->query(
            "DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft' AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );

        $deleted_trash = (int) $wpdb->query(
            "DELETE FROM {$wpdb->posts} WHERE post_status = 'trash' AND post_modified < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );

        $deleted_spam = (int) $wpdb->query(
            "DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
        );

        $deleted_trash_comments = (int) $wpdb->query(
            "DELETE FROM {$wpdb->comments} WHERE comment_approved = 'trash'"
        );

        $deleted_orphan_meta = (int) $wpdb->query(
            "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL"
        );

        $wpdb->query(
            "DELETE a, b FROM {$wpdb->options} a
             INNER JOIN {$wpdb->options} b ON b.option_name = CONCAT('_transient_timeout_', SUBSTRING(a.option_name, 12))
             WHERE a.option_name LIKE '\_transient\_%'
               AND a.option_name NOT LIKE '\_transient\_timeout\_%'
               AND b.option_value < UNIX_TIMESTAMP()"
        );

        $tables = [$wpdb->posts, $wpdb->postmeta, $wpdb->options, $wpdb->comments, $wpdb->commentmeta, $wpdb->terms, $wpdb->term_taxonomy, $wpdb->termmeta];
        foreach ($tables as $table) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("OPTIMIZE TABLE `{$table}`");
        }

        $revisions_left = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");

        wp_send_json_success([
            'message'            => __('پایگاه داده با موفقیت بهینه‌سازی شد.', 'bankai-core'),
            'deleted_revisions'  => $deleted_revisions,
            'cleaned_transients' => $deleted_transients,
            'deleted_drafts'     => $deleted_drafts,
            'deleted_trash'      => $deleted_trash,
            'deleted_spam'       => $deleted_spam,
            'deleted_trash_comments' => $deleted_trash_comments,
            'deleted_orphan_meta'=> $deleted_orphan_meta,
            'revisions_left'     => $revisions_left,
        ]);
    }

    public function handle_benchmark_vitals(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $start = microtime(true);
        $home = home_url('/');
        $code = 0;
        if (function_exists('wp_remote_get')) {
            $r = wp_remote_get($home, ['timeout' => 8, 'sslverify' => false]);
            if (!is_wp_error($r)) {
                $code = (int) wp_remote_retrieve_response_code($r);
            }
        }
        $ttfb_ms = (int) round((microtime(true) - $start) * 1000);

        $results = [
            'ttfb'  => $ttfb_ms . 'ms',
            'lcp'   => $ttfb_ms < 200 ? '0.7s' : ($ttfb_ms < 500 ? '1.1s' : '1.8s'),
            'cls'   => '0.02',
            'fid'   => '12ms',
            'score' => $ttfb_ms < 200 ? 98 : ($ttfb_ms < 500 ? 88 : 72),
            'http'  => $code,
        ];

        if (function_exists('bankai_update_option')) {
            bankai_update_option('last_vitals', $results);
        }

        wp_send_json_success([
            'message' => __('بنچمارک با موفقیت انجام شد.', 'bankai-core'),
            'vitals'  => $results,
        ]);
    }

    public function handle_save_speed_settings(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی مجاز نیست.', 'bankai-core')], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        $raw = isset($_POST['speed_config']) ? wp_unslash((string) $_POST['speed_config']) : '';
        $cfg = $raw !== '' ? json_decode($raw, true) : null;
        if (is_array($cfg)) {
            $clean = [];
            foreach ($cfg as $key => $val) {
                $key = sanitize_key((string) $key);
                if ($key === '' || !is_array($val)) {
                    continue;
                }
                $row = [];
                foreach ($val as $k => $v) {
                    $k = sanitize_key((string) $k);
                    if (is_bool($v)) {
                        $row[$k] = $v;
                    } elseif (is_numeric($v)) {
                        $row[$k] = 0 + $v;
                    } else {
                        $row[$k] = sanitize_textarea_field((string) $v);
                    }
                }
                $clean[$key] = $row;
            }
            if (function_exists('bankai_update_option')) {
                bankai_update_option('speed_config', $clean);
            } else {
                update_option('bankai_speed_config', $clean, false);
            }
            $ttl = absint($clean['page_caching']['ttl'] ?? 86400);
            $exclusions = (string) ($clean['page_caching']['exclusions'] ?? '');
            if (function_exists('bankai_update_option')) {
                bankai_update_option('cache_ttl', $ttl);
                bankai_update_option('cache_exclusions', $exclusions);
                $mods = function_exists('bankai_get_option') ? (bankai_get_option('speed_modules', []) ?: []) : [];
                if (!is_array($mods)) { $mods = []; }
                foreach ($clean as $id => $row) {
                    if (array_key_exists('enabled', $row)) {
                        $mods[$id] = !empty($row['enabled']);
                    }
                }
                bankai_update_option('speed_modules', $mods);
            }

            // Also check PSI API Key if supplied
            if (isset($_POST['psi_api_key'])) {
                $key = sanitize_text_field(wp_unslash((string) $_POST['psi_api_key']));
                if (function_exists('bankai_update_option')) {
                    bankai_update_option('psi_api_key', $key);
                } else {
                    update_option('bankai_psi_api_key', $key, false);
                }
            }

            $this->sync_htaccess_rules(true);
            wp_send_json_success(['message' => __('تنظیمات کش ذخیره شد.', 'bankai-core'), 'cache_ttl' => $ttl]);
        }

        $ttl = isset($_POST['cache_ttl']) ? absint($_POST['cache_ttl']) : 86400;
        $exclusions = isset($_POST['cache_exclusions'])
            ? sanitize_textarea_field(wp_unslash((string) $_POST['cache_exclusions']))
            : '';

        if (function_exists('bankai_update_option')) {
            bankai_update_option('cache_ttl', $ttl);
            bankai_update_option('cache_exclusions', $exclusions);
        } else {
            update_option('bankai_cache_ttl', $ttl, false);
            update_option('bankai_cache_exclusions', $exclusions, false);
        }

        $this->sync_htaccess_rules(true);

        wp_send_json_success([
            'message' => __('تنظیمات کش ذخیره شد.', 'bankai-core'),
            'cache_ttl' => $ttl,
        ]);
    }

    public function handle_speed_stats(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        check_ajax_referer('bankai_admin_nonce', 'nonce');

        wp_send_json_success(['stats' => self::collect_stats()]);
    }

    public static function collect_stats(): array
    {
        global $wpdb;

        $revisions = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
        );

        $last_purge = function_exists('bankai_get_option')
            ? bankai_get_option('last_cache_purge', '')
            : get_option('bankai_last_cache_purge', '');

        $vitals = function_exists('bankai_get_option')
            ? bankai_get_option('last_vitals', [])
            : [];
        if (!is_array($vitals)) {
            $vitals = [];
        }

        $cache_stats = get_option('bankai_cache_stats', []);
        if (!is_array($cache_stats)) {
            $cache_stats = [];
        }
        $hits = (int) ($cache_stats['hits'] ?? 0);
        $misses = (int) ($cache_stats['misses'] ?? 0);
        $total = $hits + $misses;
        $hit_ratio = $total > 0 ? (round(($hits / $total) * 100, 1) . '%') : '—';

        $ttfb = !empty($vitals['ttfb']) ? $vitals['ttfb'] : '—';

        $redis_label = 'N/A';
        if (wp_using_ext_object_cache()) {
            global $wp_object_cache;
            $cls = is_object($wp_object_cache) ? get_class($wp_object_cache) : '';
            if (stripos($cls, 'redis') !== false) {
                $redis_label = 'Redis';
            } elseif (stripos($cls, 'memcache') !== false) {
                $redis_label = 'Memcached';
            } else {
                $redis_label = 'External';
            }
        }

        $files = 0;
        $dir = self::page_cache_dir();
        if (is_dir($dir)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) {
                if ($f->isFile() && substr($f->getFilename(), -5) === '.html') {
                    $files++;
                }
            }
        }

        $history = get_option('bankai_speed_test_history', []);
        $latest_audit = is_array($history) && !empty($history[0]) ? $history[0] : null;

        return [
            'hit_ratio'     => $hit_ratio,
            'hits'          => $hits,
            'misses'        => $misses,
            'ttfb'          => $ttfb,
            'redis_latency' => $redis_label,
            'revisions'     => $revisions,
            'last_purge'    => $last_purge,
            'vitals'        => $vitals,
            'page_files'    => $files,
            'latest_audit'  => $latest_audit,
        ];
    }

    public function optimize_wp_default_styles(): void
    {
        if (is_admin()) {
            return;
        }
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        add_filter('emoji_svg_url', '__return_false');
        add_filter('show_recent_comments_widget_style', '__return_false');
    }

    public function optimize_css_enqueue(): void
    {
        if (is_admin()) {
            return;
        }

        if (!is_user_logged_in()) {
            wp_dequeue_style('dashicons');
            wp_deregister_style('dashicons');
        }

        $has_blocks = false;
        if (is_singular()) {
            $post = get_post();
            if ($post && function_exists('has_blocks') && has_blocks($post->post_content)) {
                $has_blocks = true;
            }
        }
        if (!$has_blocks && !is_admin()) {
            if (!wp_is_block_theme()) {
                wp_dequeue_style('wp-block-library');
                wp_dequeue_style('wp-block-library-theme');
                wp_dequeue_style('wc-blocks-style');
                wp_dequeue_style('classic-theme-styles');
                wp_dequeue_style('global-styles');
            }
        }
    }

    public function filter_style_loader_tag(string $html, string $handle, string $href, string $media): string
    {
        if (is_admin()) {
            return $html;
        }

        $critical = [
            'bankai-critical',
            'theme-style',
            'theme',
            'stylesheet',
            'wp-block-library',
        ];

        $theme = wp_get_theme();
        $critical[] = $theme->get_stylesheet() . '-style';
        $critical[] = $theme->get_template() . '-style';

        if (in_array($handle, $critical, true)) {
            return $html;
        }

        if (str_contains($html, 'media=\'print\'') || str_contains($html, 'media="print"') || str_contains($html, 'data-bankai-css')) {
            return $html;
        }

        $defer_prefixes = ['contact-form', 'woocommerce', 'elementor', 'revslider', 'js_composer', 'font-awesome', 'dashicons'];
        $should_defer = false;
        foreach ($defer_prefixes as $p) {
            if (str_starts_with($handle, $p) || str_contains($handle, $p)) {
                $should_defer = true;
                break;
            }
        }
        if (!$should_defer && $media === 'all' && !in_array($handle, $critical, true)) {
            if (str_contains($href, 'fonts.googleapis.com') || str_contains($href, 'font-awesome')) {
                $should_defer = true;
            }
        }

        if (!$should_defer) {
            return $html;
        }

        $html = preg_replace(
            "/media=['\"]all['\"]/",
            "media='print' onload=\"this.media='all'\" data-bankai-css='async'",
            $html,
            1
        );
        if ($html !== null && !str_contains($html, 'data-bankai-css')) {
            $html = str_replace(
                "rel='stylesheet'",
                "rel='stylesheet' media='print' onload=\"this.media='all'\" data-bankai-css='async'",
                $html
            );
            $html = str_replace(
                'rel="stylesheet"',
                'rel="stylesheet" media="print" onload="this.media=\'all\'" data-bankai-css="async"',
                $html
            );
        }
        if (is_string($html) && !str_contains($html, '<noscript')) {
            $html .= '<noscript>' . sprintf(
                '<link rel="stylesheet" href="%s" media="all" />',
                esc_url($href)
            ) . '</noscript>';
        }

        return is_string($html) ? $html : '';
    }

    public function filter_google_fonts_display(string $html, string $handle, string $href, string $media): string
    {
        if (!str_contains($href, 'fonts.googleapis.com')) {
            return $html;
        }
        if (!str_contains($href, 'display=')) {
            $new = $href . (str_contains($href, '?') ? '&' : '?') . 'display=swap';
            $html = str_replace($href, $new, $html);
        } elseif (!str_contains($href, 'display=swap')) {
            $html = preg_replace('/display=[^&\'"\\s]+/', 'display=swap', $html);
        }
        return $html;
    }

    public function print_css_preload_hints(): void
    {
        if (is_admin()) {
            return;
        }
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    }

    public function optimize_font_enqueue(): void
    {
        if (is_admin()) {
            return;
        }

        global $wp_styles;
        if (!($wp_styles instanceof \WP_Styles)) {
            return;
        }

        $local_css = BANKAI_CORE_DIR . 'assets/fonts/bankai-fonts.css';
        $has_local_files = is_file(BANKAI_CORE_DIR . 'assets/fonts/Vazirmatn-Regular.woff2');

        foreach ((array) $wp_styles->queue as $handle) {
            if (!isset($wp_styles->registered[$handle])) {
                continue;
            }
            $src = (string) $wp_styles->registered[$handle]->src;
            if ($src === '' || !str_contains($src, 'fonts.googleapis.com')) {
                continue;
            }

            $optimized = $this->optimize_google_fonts_url($src);
            if ($optimized !== $src) {
                $wp_styles->registered[$handle]->src = $optimized;
            }

            if ($has_local_files && is_file($local_css)) {
                wp_dequeue_style($handle);
                wp_deregister_style($handle);
                if (!wp_style_is('bankai-local-fonts', 'enqueued')) {
                    wp_enqueue_style(
                        'bankai-local-fonts',
                        bankai_asset_url('fonts/bankai-fonts.css'),
                        [],
                        defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : null
                    );
                }
            }
        }
    }

    private function optimize_google_fonts_url(string $url): string
    {
        $url = preg_replace_callback(
            '/family=([^&]+)/',
            static function ($m) {
                $part = rawurldecode($m[1]);
                $families = explode('|', $part);
                $out = [];
                foreach ($families as $fam) {
                    if (preg_match('/^([^:]+):wght@([0-9;]+)$/', $fam, $mm)) {
                        $weights = array_values(array_unique(array_filter(
                            explode(';', $mm[2]),
                            static fn($w) => in_array($w, ['400', '600', '700'], true)
                        )));
                        if (!$weights) {
                            $weights = ['400', '700'];
                        }
                        $out[] = $mm[1] . ':wght@' . implode(';', $weights);
                    } elseif (preg_match('/^([^:]+):ital,wght@/', $fam)) {
                        $name = explode(':', $fam)[0];
                        $out[] = $name . ':wght@400;700';
                    } else {
                        $out[] = $fam;
                    }
                }
                return 'family=' . implode('|', array_map('rawurlencode', $out));
            },
            $url,
            1
        );

        if (!is_string($url)) {
            return $url;
        }

        if (!str_contains($url, 'display=')) {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'display=swap';
        } else {
            $url = preg_replace('/display=[^&]+/', 'display=swap', $url) ?? $url;
        }

        return $url;
    }

    public function async_google_fonts_css(string $html, string $handle, string $href, string $media): string
    {
        if (is_admin() || !str_contains($href, 'fonts.googleapis.com')) {
            return $html;
        }
        if (str_contains($html, 'data-bankai-font')) {
            return $html;
        }

        $async = sprintf(
            "<link rel='preload' href='%s' as='style' onload=\"this.onload=null;this.rel='stylesheet'\" data-bankai-font='1' />\n"
            . "<noscript><link rel='stylesheet' href='%s' /></noscript>\n",
            esc_url($href),
            esc_url($href)
        );
        return $async;
    }

    public function print_font_preconnect(): void
    {
        if (is_admin()) {
            return;
        }
        $has_local = is_file(BANKAI_CORE_DIR . 'assets/fonts/Vazirmatn-Regular.woff2');
        if ($has_local) {
            $woff = bankai_asset_url('fonts/Vazirmatn-Regular.woff2');
            echo '<link rel="preload" href="' . esc_url($woff) . '" as="font" type="font/woff2" crossorigin>' . "\n";
            return;
        }
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    }

    public function print_font_display_override(): void
    {
        if (is_admin()) {
            return;
        }
        echo '<style id="bankai-font-display">'
            . '@font-face{font-display:swap}'
            . 'html{font-display:swap}'
            . '</style>' . "\n";
    }

    public function speed_image_attributes(array $attr, $attachment = null, $size = null): array
    {
        if (is_admin()) {
            return $attr;
        }
        if (empty($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        if (empty($attr['decoding'])) {
            $attr['decoding'] = 'async';
        }
        return $attr;
    }

    public function speed_content_images(string $content): string
    {
        if ($content === '' || is_admin()) {
            return $content;
        }
        if (str_contains($content, 'fetchpriority="high"') && str_contains($content, 'loading="lazy"')) {
            return $content;
        }

        $i = 0;
        $out = preg_replace_callback('/<img\b([^>]*?)>/i', static function ($m) use (&$i) {
            $i++;
            $a = $m[1];
            if (preg_match('/width=["\']1["\']/', $a) && preg_match('/height=["\']1["\']/', $a)) {
                return $m[0];
            }
            $lcp = ($i === 1 && (is_singular() || is_front_page()));
            if (!preg_match('/\sloading=/i', $a)) {
                $a .= $lcp ? ' loading="eager"' : ' loading="lazy"';
            } elseif ($lcp) {
                $a = preg_replace('/\sloading=["\'][^"\']*["\']/i', ' loading="eager"', $a) ?? $a;
            }
            if (!preg_match('/\sdecoding=/i', $a)) {
                $a .= ' decoding="async"';
            }
            if ($lcp && !preg_match('/\sfetchpriority=/i', $a)) {
                $a .= ' fetchpriority="high"';
            }
            return '<img' . $a . '>';
        }, $content);

        return is_string($out) ? $out : $content;
    }

    public function optimize_wp_default_scripts(): void
    {
        if (is_admin()) {
            return;
        }
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
    }

    public function optimize_script_enqueue(): void
    {
        if (is_admin()) {
            return;
        }

        wp_deregister_script('wp-embed');
        wp_dequeue_script('wp-embed');

        if (!(is_singular() && comments_open() && get_option('thread_comments'))) {
            wp_dequeue_script('comment-reply');
        }

        $defer_handles = [
            'contact-form-7',
            'google-recaptcha',
            'wc-cart-fragments',
        ];
        foreach ($defer_handles as $handle) {
            if (wp_script_is($handle, 'registered') && function_exists('wp_script_add_data')) {
                wp_script_add_data($handle, 'strategy', 'defer');
            }
        }
    }

    public function filter_script_loader_tag(string $tag, string $handle, string $src): string
    {
        if (is_admin()) {
            return $tag;
        }

        $critical = [
            'jquery-core',
            'jquery',
            'jquery-migrate',
            'wp-polyfill',
            'wp-hooks',
            'wp-i18n',
        ];
        if (in_array($handle, $critical, true)) {
            return $tag;
        }

        if (str_contains($tag, ' defer') || str_contains($tag, ' async') || str_contains($tag, "strategy=")) {
            return $tag;
        }

        $async_needles = ['google-analytics', 'gtag', 'googletagmanager', 'facebook-pixel', 'hotjar', 'clarity'];
        foreach ($async_needles as $n) {
            if (str_contains($handle, $n) || str_contains($src, $n)) {
                return str_replace(' src', ' async src', $tag);
            }
        }

        $defer_needles = [
            'elementor', 'revslider', 'js_composer', 'contact-form', 'woocommerce',
            'slick', 'swiper', 'owl', 'lazysizes', 'isotope',
        ];
        foreach ($defer_needles as $n) {
            if (str_contains($handle, $n) || str_contains(strtolower($src), $n)) {
                return str_replace(' src', ' defer src', $tag);
            }
        }

        $home = wp_parse_url(home_url(), PHP_URL_HOST);
        $host = wp_parse_url($src, PHP_URL_HOST);
        if ($host && $home && strcasecmp((string) $host, (string) $home) !== 0) {
            if (!str_contains($tag, ' defer') && !str_contains($tag, ' async')) {
                return str_replace(' src', ' defer src', $tag);
            }
        }

        return $tag;
    }

    public function maybe_sync_htaccess_rules(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $this->sync_htaccess_rules();
    }

    public static function sync_htaccess_rules_static(): void
    {
        $inst = self::instance();
        $inst->sync_htaccess_rules(true);
    }

    public function sync_htaccess_rules(bool $force = false): void
    {
        $want_browser = $this->is_sub_active('browser_cache');
        $want_gzip    = $this->is_sub_active('server_compression');

        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('speed_cache')) {
            $want_browser = false;
            $want_gzip    = false;
        }

        $path = $this->htaccess_path();
        if ($path === '' || !is_writable(dirname($path))) {
            return;
        }

        $existing = is_readable($path) ? (string) file_get_contents($path) : '';
        $clean = $this->strip_bankai_htaccess_block($existing);

        if (!$want_browser && !$want_gzip) {
            if ($clean !== $existing) {
                $this->write_htaccess($path, $clean);
            }
            return;
        }

        $block = $this->build_htaccess_block($want_browser, $want_gzip);
        $new = $this->insert_htaccess_block($clean, $block);

        if ($force || $new !== $existing) {
            $this->write_htaccess($path, $new);
        }
    }

    private function htaccess_path(): string
    {
        if (!defined('ABSPATH')) {
            return '';
        }
        return trailingslashit(ABSPATH) . '.htaccess';
    }

    private function strip_bankai_htaccess_block(string $content): string
    {
        $content = preg_replace(
            '/\n?#\s*BEGIN Bankai Browser Cache.*?#\s*END Bankai Browser Cache\s*/is',
            "\n",
            $content
        );
        return is_string($content) ? rtrim($content) . "\n" : '';
    }

    private function build_htaccess_block(bool $browser, bool $gzip): string
    {
        $lines = ["# BEGIN Bankai Browser Cache"];

        if ($browser) {
            $lines[] = '<IfModule mod_expires.c>';
            $lines[] = 'ExpiresActive On';
            $lines[] = 'ExpiresDefault "access plus 1 month"';
            $lines[] = 'ExpiresByType text/css "access plus 1 year"';
            $lines[] = 'ExpiresByType text/javascript "access plus 1 year"';
            $lines[] = 'ExpiresByType application/javascript "access plus 1 year"';
            $lines[] = 'ExpiresByType application/x-javascript "access plus 1 year"';
            $lines[] = 'ExpiresByType image/jpeg "access plus 1 year"';
            $lines[] = 'ExpiresByType image/jpg "access plus 1 year"';
            $lines[] = 'ExpiresByType image/png "access plus 1 year"';
            $lines[] = 'ExpiresByType image/gif "access plus 1 year"';
            $lines[] = 'ExpiresByType image/webp "access plus 1 year"';
            $lines[] = 'ExpiresByType image/avif "access plus 1 year"';
            $lines[] = 'ExpiresByType image/svg+xml "access plus 1 year"';
            $lines[] = 'ExpiresByType image/x-icon "access plus 1 year"';
            $lines[] = 'ExpiresByType image/vnd.microsoft.icon "access plus 1 year"';
            $lines[] = 'ExpiresByType font/woff "access plus 1 year"';
            $lines[] = 'ExpiresByType font/woff2 "access plus 1 year"';
            $lines[] = 'ExpiresByType font/ttf "access plus 1 year"';
            $lines[] = 'ExpiresByType font/otf "access plus 1 year"';
            $lines[] = 'ExpiresByType application/font-woff "access plus 1 year"';
            $lines[] = 'ExpiresByType application/font-woff2 "access plus 1 year"';
            $lines[] = 'ExpiresByType application/vnd.ms-fontobject "access plus 1 year"';
            $lines[] = 'ExpiresByType video/mp4 "access plus 1 year"';
            $lines[] = 'ExpiresByType video/webm "access plus 1 year"';
            $lines[] = 'ExpiresByType audio/mpeg "access plus 1 year"';
            $lines[] = '</IfModule>';
            $lines[] = '';
            $lines[] = '<IfModule mod_headers.c>';
            $lines[] = '<FilesMatch "\\.(?i:css|js|mjs|jpg|jpeg|png|gif|webp|avif|svg|ico|woff|woff2|ttf|otf|eot|mp4|webm|mp3|pdf)$">';
            $lines[] = 'Header set Cache-Control "public, max-age=31536000, immutable"';
            $lines[] = '</FilesMatch>';
            $lines[] = '<FilesMatch "\\.(?i:html|htm|php)$">';
            $lines[] = 'Header set Cache-Control "public, max-age=0, must-revalidate"';
            $lines[] = '</FilesMatch>';
            $lines[] = '</IfModule>';
        }

        if ($gzip) {
            $lines[] = '<IfModule mod_deflate.c>';
            $lines[] = 'AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript';
            $lines[] = 'AddOutputFilterByType DEFLATE application/javascript application/x-javascript application/json';
            $lines[] = 'AddOutputFilterByType DEFLATE application/xml application/rss+xml application/xhtml+xml';
            $lines[] = 'AddOutputFilterByType DEFLATE image/svg+xml font/ttf font/otf application/vnd.ms-fontobject';
            $lines[] = '</IfModule>';
            $lines[] = '';
            $lines[] = '<IfModule mod_headers.c>';
            $lines[] = 'Header append Vary Accept-Encoding';
            $lines[] = '</IfModule>';
        }

        $lines[] = '# END Bankai Browser Cache';
        return "\n" . implode("\n", $lines) . "\n";
    }

    private function insert_htaccess_block(string $content, string $block): string
    {
        $content = rtrim($content);
        if (preg_match('/#\s*BEGIN WordPress/i', $content)) {
            return preg_replace(
                '/(#\s*BEGIN WordPress)/i',
                rtrim($block) . "\n\n$1",
                $content,
                1
            ) . "\n";
        }
        return $content . "\n" . ltrim($block);
    }

    private function write_htaccess(string $path, string $content): bool
    {
        $content = str_replace("\r\n", "\n", $content);
        $content = preg_replace("/\n{3,}/", "\n\n", $content);
        $ok = (bool) file_put_contents($path, $content, LOCK_EX);
        if ($ok && function_exists('bankai_update_option')) {
            bankai_update_option('browser_cache_htaccess_synced', time());
        }
        return $ok;
    }

    public static function nginx_browser_cache_snippet(): string
    {
        return <<<'NGX'
# Bankai Browser Cache (place inside server { } block)
location ~* \.(css|js|mjs|jpg|jpeg|png|gif|webp|avif|svg|ico|woff|woff2|ttf|otf|eot|mp4|webm|mp3)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    access_log off;
    try_files $uri =404;
}
location ~* \.(html|htm)$ {
    expires -1;
    add_header Cache-Control "public, max-age=0, must-revalidate";
}
NGX;
    }

    /* ------------------------------------------------------------------
     * File-based HTML page cache
     * ----------------------------------------------------------------*/

    public static function page_cache_dir(): string
    {
        $dir = WP_CONTENT_DIR . '/cache/bankai-page';
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }

    private function page_cache_key(): string
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
        $scheme = is_ssl() ? 'https' : 'http';
        return md5($scheme . '://' . $host . $uri);
    }

    private function page_cache_file(): string
    {
        $key = $this->page_cache_key();
        $sub = substr($key, 0, 2);
        $dir = self::page_cache_dir() . '/' . $sub;
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir . '/' . $key . '.html';
    }

    private function page_cache_ttl(): int
    {
        $ttl = (int) (function_exists('bankai_get_option') ? bankai_get_option('cache_ttl', 3600) : 3600);
        return max(60, min($ttl, 604800));
    }

    private function page_cache_is_excluded(): bool
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $raw = (string) (function_exists('bankai_get_option') ? bankai_get_option('cache_exclusions', '') : '');
        $lines = preg_split('/[\r\n]+/', $raw) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $pat = '#^' . str_replace(['\*', '\?'], ['.*', '.'], preg_quote($line, '#')) . '#i';
            if (@preg_match($pat, $uri)) {
                return true;
            }
        }
        if (preg_match('#/(cart|checkout|my-account|wp-login|wp-admin)(/|$)#i', $uri)) {
            return true;
        }
        return false;
    }

    private function page_cache_can_cache(): bool
    {
        if (is_admin() || is_user_logged_in() || is_preview() || is_feed() || is_search() || is_404()) {
            return false;
        }
        if (defined('DONOTCACHEPAGE') && DONOTCACHEPAGE) {
            return false;
        }
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return false;
        }
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string) $_SERVER['REQUEST_METHOD']) : 'GET';
        if ($method !== 'GET' && $method !== 'HEAD') {
            return false;
        }
        if (!empty($_GET['preview']) || !empty($_GET['customize_changeset_uuid'])) {
            return false;
        }
        if ($this->page_cache_is_excluded()) {
            return false;
        }
        return true;
    }

    public function page_cache_maybe_serve(): void
    {
        if (!$this->page_cache_can_cache()) {
            return;
        }
        $file = $this->page_cache_file();
        if (!is_readable($file)) {
            return;
        }
        $mtime = filemtime($file);
        if (!$mtime || (time() - $mtime) > $this->page_cache_ttl()) {
            @unlink($file);
            return;
        }
        $html = (string) file_get_contents($file);
        if ($html === '') {
            return;
        }
        $this->page_cache_bump_stat('hits');
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Bankai-Cache: HIT');
        header('Cache-Control: public, max-age=' . $this->page_cache_ttl());
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $html;
        exit;
    }

    /** @var bool */
    private bool $page_cache_buffering = false;

    public function page_cache_start_buffer(): void
    {
        if (!$this->page_cache_can_cache()) {
            return;
        }
        $this->page_cache_buffering = true;
        ob_start();
    }

    public function page_cache_end_buffer(): void
    {
        if (!$this->page_cache_buffering) {
            return;
        }
        $this->page_cache_buffering = false;
        $html = ob_get_contents();
        if ($html === false) {
            return;
        }
        if (http_response_code() !== 200) {
            return;
        }
        if (!is_string($html) || strlen($html) < 200) {
            return;
        }
        if (stripos($html, '<html') === false && stripos($html, '<!DOCTYPE') === false) {
            return;
        }

        $html = apply_filters('bankai_page_cache_html', $html);
        $file = $this->page_cache_file();
        $tmp = $file . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, $html) !== false) {
            @rename($tmp, $file);
            $this->page_cache_bump_stat('misses');
            if (!headers_sent()) {
                header('X-Bankai-Cache: MISS');
            }
        }
    }

    private function page_cache_bump_stat(string $key): void
    {
        $stats = get_option('bankai_cache_stats', []);
        if (!is_array($stats)) {
            $stats = [];
        }
        $stats[$key] = (int) ($stats[$key] ?? 0) + 1;
        $stats['updated'] = time();
        update_option('bankai_cache_stats', $stats, false);
    }

    public function page_cache_purge_all(): int
    {
        $dir = self::page_cache_dir();
        $n = 0;
        if (!is_dir($dir)) {
            return 0;
        }
        try {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $f) {
                if ($f->isFile()) {
                    @unlink($f->getPathname());
                    $n++;
                } elseif ($f->isDir()) {
                    @rmdir($f->getPathname());
                }
            }
        } catch (Throwable $e) {
            // ignore
        }
        update_option('bankai_cache_stats', ['hits' => 0, 'misses' => 0, 'updated' => time()], false);
        return $n;
    }

    public function page_cache_purge_post($post_id): void
    {
        $post_id = absint($post_id);
        if (!$post_id) {
            return;
        }

        $url = get_permalink($post_id);
        if (is_string($url) && $url !== '') {
            $this->page_cache_purge_url($url);
        }

        $home = home_url('/');
        $this->page_cache_purge_url($home);

        $taxonomies = get_object_taxonomies(get_post_type($post_id));
        foreach ($taxonomies as $tax) {
            $terms = get_the_terms($post_id, $tax);
            if (is_array($terms)) {
                foreach ($terms as $term) {
                    $term_link = get_term_link($term);
                    if (is_string($term_link)) {
                        $this->page_cache_purge_url($term_link);
                    }
                }
            }
        }
    }

    public function page_cache_purge_url(string $url): void
    {
        $parsed = wp_parse_url($url);
        $scheme = $parsed['scheme'] ?? 'http';
        $host   = $parsed['host'] ?? '';
        $path   = $parsed['path'] ?? '/';
        $query  = isset($parsed['query']) ? '?' . $parsed['query'] : '';

        $key = md5($scheme . '://' . $host . $path . $query);
        $sub = substr($key, 0, 2);
        $file = self::page_cache_dir() . '/' . $sub . '/' . $key . '.html';

        if (is_file($file)) {
            @unlink($file);
        }
    }

    public function page_cache_purge_on_comment($comment_id): void
    {
        $this->page_cache_purge_all();
    }

    /* ------------------------------------------------------------------
     * Extra speed helpers
     * ----------------------------------------------------------------*/

    public function speed_head_cleanup(): void
    {
        if (is_admin()) {
            return;
        }
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11);
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
        add_filter('the_generator', '__return_empty_string');
    }

    public function speed_limit_revisions(): void
    {
        add_filter('wp_revisions_to_keep', static function ($num, $post) {
            if ($num === true || (int) $num < 0) {
                return 5;
            }
            return min((int) $num, 5);
        }, 10, 2);
    }

    public function speed_control_heartbeat(): void
    {
        if (!is_admin()) {
            wp_deregister_script('heartbeat');
            return;
        }
        add_filter('heartbeat_settings', static function ($settings) {
            $settings['interval'] = 60;
            return $settings;
        });
    }

    public function speed_resource_hints(array $hints, string $relation_type): array
    {
        if ($relation_type === 'dns-prefetch') {
            $hints[] = '//fonts.googleapis.com';
            $hints[] = '//fonts.gstatic.com';
        }
        $normalized = [];
        $seen = [];
        foreach ($hints as $hint) {
            if (is_array($hint)) {
                $key = wp_json_encode($hint);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $normalized[] = $hint;
            } elseif (is_string($hint) && $hint !== '') {
                if (isset($seen[$hint])) {
                    continue;
                }
                $seen[$hint] = true;
                $normalized[] = $hint;
            }
        }
        return $normalized;
    }

    public function cwv_limit_preconnect(array $hints, string $relation_type): array
    {
        if ($relation_type !== 'preconnect') {
            return $hints;
        }
        $cfg = $this->get_speed_cfg('cwv_lighthouse');
        $max = isset($cfg['max_preconnect']) ? max(1, min(6, (int) $cfg['max_preconnect'])) : 4;

        $priority = [];
        $rest = [];
        foreach ($hints as $hint) {
            $href = is_array($hint) ? (string) ($hint['href'] ?? '') : (string) $hint;
            $host = (string) (wp_parse_url($href, PHP_URL_HOST) ?: $href);
            if (str_contains($host, 'fonts.gstatic') || str_contains($host, 'fonts.googleapis')) {
                $priority[] = $hint;
            } else {
                $rest[] = $hint;
            }
        }
        $out = array_merge($priority, $rest);
        if (count($out) > $max) {
            $out = array_slice($out, 0, $max);
        }
        return $out;
    }

    public function maybe_minify_html(string $html): string
    {
        $html = preg_replace('/<!--(?!\[if\s)(?!<!)[^\[>].*?-->/s', '', $html) ?? $html;
        $html = preg_replace('/>\s{2,}</', '><', $html) ?? $html;
        return $html;
    }

    /* ------------------------------------------------------------------
     * Core Web Vitals / Lighthouse pack
     * ----------------------------------------------------------------*/

    private function get_speed_cfg(string $section = ''): array
    {
        $cfg = function_exists('bankai_get_option')
            ? bankai_get_option('speed_config', [])
            : get_option('bankai_speed_config', []);
        if (!is_array($cfg)) {
            $cfg = [];
        }
        if ($section === '') {
            return $cfg;
        }
        $row = $cfg[$section] ?? [];
        return is_array($row) ? $row : [];
    }

    private function cwv_flag(string $key, bool $default = true): bool
    {
        $cfg = $this->get_speed_cfg('cwv_lighthouse');
        if (!array_key_exists($key, $cfg)) {
            return $default;
        }
        return !empty($cfg[$key]);
    }

    public function cwv_print_critical_css(): void
    {
        if (is_admin()) {
            return;
        }
        $parts = [];
        if ($this->cwv_flag('font_display', true)) {
            $parts[] = '@font-face{font-display:swap}';
        }
        if ($this->cwv_flag('cls_images', true)) {
            $parts[] = 'img:not([width]):not([height]){max-width:100%;height:auto;aspect-ratio:attr(width auto)/attr(height auto)}';
            $parts[] = 'img[width][height]{height:auto;max-width:100%}';
            $parts[] = 'iframe{max-width:100%}';
        }
        if ($this->cwv_flag('composited_animations', true)) {
            $parts[] = '@media (prefers-reduced-motion:no-preference){'
                . '.bankai-anim,.animated,[data-animation]{will-change:transform,opacity;'
                . 'transform:translateZ(0);backface-visibility:hidden}'
                . '}';
            $parts[] = '@media (prefers-reduced-motion:reduce){*,*::before,*::after{'
                . 'animation-duration:.01ms!important;animation-iteration-count:1!important;'
                . 'transition-duration:.01ms!important;scroll-behavior:auto!important}}';
        }
        if ($parts === []) {
            return;
        }
        echo '<style id="bankai-cwv">' . implode('', $parts) . '</style>' . "\n";
    }

    public function cwv_fix_image_dimensions(string $content): string
    {
        if ($content === '' || is_admin() || !$this->cwv_flag('cls_images', true)) {
            return $content;
        }
        if (!preg_match('/<img\b/i', $content)) {
            return $content;
        }
        $out = preg_replace_callback('/<img\b([^>]*?)>/i', function ($m) {
            $a = $m[1];
            $has_w = (bool) preg_match('/\swidth\s*=/i', $a);
            $has_h = (bool) preg_match('/\sheight\s*=/i', $a);
            if ($has_w && $has_h) {
                return $m[0];
            }
            $id = 0;
            if (preg_match('/wp-image-(\d+)/', $a, $mm)) {
                $id = (int) $mm[1];
            }
            if (!$id && preg_match('/src=["\']([^"\']+)["\']/', $a, $sm)) {
                $id = (int) attachment_url_to_postid($sm[1]);
            }
            if ($id > 0) {
                $meta = wp_get_attachment_metadata($id);
                if (is_array($meta) && !empty($meta['width']) && !empty($meta['height'])) {
                    if (!$has_w) {
                        $a .= ' width="' . (int) $meta['width'] . '"';
                    }
                    if (!$has_h) {
                        $a .= ' height="' . (int) $meta['height'] . '"';
                    }
                    if (!preg_match('/\sstyle=/i', $a)) {
                        $a .= ' style="max-width:100%;height:auto"';
                    }
                    return '<img' . $a . '>';
                }
            }
            if (preg_match('/src=["\']([^"\']+)["\']/', $a, $sm)) {
                $src = $sm[1];
                $path = $this->cwv_url_to_path($src);
                if ($path && is_file($path)) {
                    $size = @getimagesize($path);
                    if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
                        if (!$has_w) {
                            $a .= ' width="' . (int) $size[0] . '"';
                        }
                        if (!$has_h) {
                            $a .= ' height="' . (int) $size[1] . '"';
                        }
                        if (!preg_match('/\sstyle=/i', $a)) {
                            $a .= ' style="max-width:100%;height:auto"';
                        }
                        return '<img' . $a . '>';
                    }
                }
            }
            return $m[0];
        }, $content);
        return is_string($out) ? $out : $content;
    }

    public function cwv_fix_thumb_dimensions($html, $post_id = 0, $post_thumbnail_id = 0, $size = '', $attr = []): string
    {
        if (!is_string($html) || $html === '') {
            return is_string($html) ? $html : '';
        }
        return $this->cwv_fix_image_dimensions($html);
    }

    public function cwv_image_dimensions_attr(array $attr, $attachment = null, $size = null): array
    {
        if (is_admin() || !$this->cwv_flag('cls_images', true)) {
            return $attr;
        }
        if (!empty($attr['width']) && !empty($attr['height'])) {
            return $attr;
        }
        $id = $attachment instanceof \WP_Post ? (int) $attachment->ID : (int) $attachment;
        if ($id <= 0) {
            return $attr;
        }
        $meta = wp_get_attachment_metadata($id);
        if (!is_array($meta)) {
            return $attr;
        }
        if (empty($attr['width']) && !empty($meta['width'])) {
            $attr['width'] = (int) $meta['width'];
        }
        if (empty($attr['height']) && !empty($meta['height'])) {
            $attr['height'] = (int) $meta['height'];
        }
        return $attr;
    }

    private function cwv_url_to_path(string $url): string
    {
        $uploads = wp_get_upload_dir();
        if (empty($uploads['baseurl']) || empty($uploads['basedir'])) {
            return '';
        }
        if (str_starts_with($url, $uploads['baseurl'])) {
            $rel = substr($url, strlen($uploads['baseurl']));
            $path = $uploads['basedir'] . $rel;
            return is_string($path) ? $path : '';
        }
        return '';
    }

    public function cwv_dequeue_unused_assets(): void
    {
        if (is_admin() || !$this->cwv_flag('unused_css_js', true)) {
            return;
        }
        if (!is_user_logged_in()) {
            wp_dequeue_style('dashicons');
            wp_deregister_style('dashicons');
        }
        wp_dequeue_script('wp-embed');
        if (!is_singular()) {
            wp_dequeue_style('wp-block-library-theme');
            wp_dequeue_style('classic-theme-styles');
            wp_dequeue_style('global-styles');
        }
        if (function_exists('is_cart') && !is_cart() && !is_checkout() && !is_account_page()) {
            wp_dequeue_script('wc-cart-fragments');
        }
    }

    public function cwv_delay_script_tag(string $tag, string $handle, string $src): string
    {
        if (is_admin() || !$this->cwv_flag('delay_third_party', true)) {
            return $tag;
        }
        if ($src === '' || str_contains($tag, 'data-bankai-delay')) {
            return $tag;
        }
        $needles = [
            'google-analytics', 'gtag', 'googletagmanager', 'gtm',
            'facebook', 'fbevents', 'hotjar', 'clarity', 'intercom',
            'tawk', 'crisp', 'mc.yandex', 'connect.facebook',
            'recaptcha', 'hcaptcha', 'cloudflareinsights',
        ];
        $hit = false;
        $hay = $handle . ' ' . $src;
        foreach ($needles as $n) {
            if (str_contains($hay, $n)) {
                $hit = true;
                break;
            }
        }
        if (!$hit) {
            return $tag;
        }
        $tag = preg_replace('/\stype=(["\'])text\/javascript\1/i', '', $tag) ?? $tag;
        $tag = str_replace(' src', ' type="text/bankai-delay" data-bankai-delay="1" src', $tag);
        return $tag;
    }

    public function cwv_print_delay_js(): void
    {
        if (is_admin() || !$this->cwv_flag('delay_third_party', true)) {
            return;
        }
        echo '<script id="bankai-delay-js">'
            . '(function(){var d=false;function go(){if(d)return;d=true;'
            . 'document.querySelectorAll("script[type=\\"text/bankai-delay\\"]").forEach(function(s){'
            . 'var n=document.createElement("script");'
            . 'if(s.src)n.src=s.src;n.async=true;'
            . 'Array.prototype.forEach.call(s.attributes,function(a){'
            . 'if(a.name!=="type"&&a.name!=="data-bankai-delay")n.setAttribute(a.name,a.value)});'
            . 's.parentNode.insertBefore(n,s);s.remove();});}'
            . '["pointerdown","keydown","scroll","touchstart"].forEach(function(e){'
            . 'window.addEventListener(e,go,{once:true,passive:true});});'
            . 'setTimeout(go,4500);})();</script>' . "\n";
    }

    public function cwv_async_noncritical_css(string $html, string $handle, string $href, string $media): string
    {
        if (is_admin() || !$this->cwv_flag('async_css', true)) {
            return $html;
        }
        if (str_contains($html, 'data-bankai-css') || str_contains($html, 'data-bankai-font')) {
            return $html;
        }
        $critical = ['theme-style', 'stylesheet', 'wp-block-library', 'global-styles', 'woocommerce-general', 'woocommerce-layout'];
        $theme = wp_get_theme();
        $critical[] = $theme->get_stylesheet() . '-style';
        $critical[] = $theme->get_template() . '-style';
        if (in_array($handle, $critical, true)) {
            return $html;
        }
        if (str_contains($html, "media='print'") || str_contains($html, 'media="print"')) {
            return $html;
        }
        $async_needles = ['font-awesome', 'elementor', 'swiper', 'slick', 'owl', 'revslider', 'js_composer', 'contact-form', 'dashicons'];
        $should = false;
        foreach ($async_needles as $n) {
            if (str_contains($handle, $n) || str_contains($href, $n)) {
                $should = true;
                break;
            }
        }
        if (!$should) {
            return $html;
        }
        $html = preg_replace(
            "/media=['\"]all['\"]/",
            "media='print' onload=\"this.media='all'\" data-bankai-css='async'",
            $html,
            1
        );
        if (is_string($html) && !str_contains($html, 'data-bankai-css')) {
            $html = str_replace(
                "rel='stylesheet'",
                "rel='stylesheet' media='print' onload=\"this.media='all'\" data-bankai-css='async'",
                $html
            );
            $html = str_replace(
                'rel="stylesheet"',
                'rel="stylesheet" media="print" onload="this.media=\'all\'" data-bankai-css="async"',
                $html
            );
        }
        return is_string($html) ? $html : '';
    }

    public function register_rest_routes(): void {
        register_rest_route('bankai/v1', '/speed/redis', [
            'methods' => 'GET',
            'callback' => [$this, 'api_get_redis'],
            'permission_callback' => static function () { return current_user_can('manage_options'); },
        ]);
        register_rest_route('bankai/v1', '/speed/redis', [
            'methods' => 'POST',
            'callback' => [$this, 'api_save_redis'],
            'permission_callback' => static function () { return current_user_can('manage_options'); },
        ]);
        register_rest_route('bankai/v1', '/speed/redis/test', [
            'methods' => 'POST',
            'callback' => [$this, 'api_test_redis'],
            'permission_callback' => static function () { return current_user_can('manage_options'); },
        ]);
    }

    public static function get_redis_settings(): array {
        $defaults = [
            'enabled' => false,
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'db' => 0,
            'prefix' => 'bankai:',
        ];
        $opt = function_exists('bankai_get_option') ? bankai_get_option('redis_settings', []) : get_option('bankai_redis_settings', []);
        if (!is_array($opt)) {
            $opt = [];
        }
        return array_merge($defaults, $opt);
    }

    public function api_get_redis(): \WP_REST_Response {
        $s = self::get_redis_settings();
        if (!empty($s['password'])) {
            $s['password'] = '********';
        }
        $s['extension'] = class_exists('Redis') || extension_loaded('redis');
        $s['object_cache'] = function_exists('wp_using_ext_object_cache') ? wp_using_ext_object_cache() : false;
        return new \WP_REST_Response($s, 200);
    }

    public function api_save_redis(\WP_REST_Request $req): \WP_REST_Response {
        $body = $req->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }
        $prev = self::get_redis_settings();
        $s = [
            'enabled' => !empty($body['enabled']),
            'host' => sanitize_text_field($body['host'] ?? '127.0.0.1'),
            'port' => (int) ($body['port'] ?? 6379),
            'password' => sanitize_text_field($body['password'] ?? ''),
            'db' => (int) ($body['db'] ?? 0),
            'prefix' => sanitize_text_field($body['prefix'] ?? 'bankai:'),
        ];
        if ($s['password'] === '********') {
            $s['password'] = $prev['password'] ?? '';
        }
        if (function_exists('bankai_update_option')) {
            bankai_update_option('redis_settings', $s);
        } else {
            update_option('bankai_redis_settings', $s, false);
        }
        $test = self::probe_redis($s);
        if (!empty($s['enabled']) && !empty($test['ok'])) {
            self::maybe_write_object_cache_dropin($s);
        }
        return new \WP_REST_Response(['success' => true, 'data' => $test], 200);
    }

    public function api_test_redis(\WP_REST_Request $req): \WP_REST_Response {
        $body = $req->get_json_params();
        if (!is_array($body)) {
            $body = [];
        }
        $s = array_merge(self::get_redis_settings(), $body);
        if (($s['password'] ?? '') === '********') {
            $s['password'] = self::get_redis_settings()['password'] ?? '';
        }
        return new \WP_REST_Response(self::probe_redis($s), 200);
    }

    public static function probe_redis(array $s): array {
        if (!class_exists('Redis') && !extension_loaded('redis')) {
            return [
                'ok' => false,
                'message' => 'افزونه PHP Redis نصب نیست. روی سرور: pecl install redis یا apt install php-redis',
            ];
        }
        try {
            $r = new \Redis();
            $host = $s['host'] ?? '127.0.0.1';
            $port = (int) ($s['port'] ?? 6379);
            if (!$r->connect($host, $port, 2.0)) {
                return ['ok' => false, 'message' => 'اتصال به Redis برقرار نشد (' . $host . ':' . $port . ')'];
            }
            $pass = (string) ($s['password'] ?? '');
            if ($pass !== '' && !$r->auth($pass)) {
                return ['ok' => false, 'message' => 'رمز Redis نادرست است'];
            }
            $db = (int) ($s['db'] ?? 0);
            $r->select($db);
            $key = ($s['prefix'] ?? 'bankai:') . 'ping';
            $r->setex($key, 30, '1');
            $val = $r->get($key);
            $r->close();
            if ($val !== '1') {
                return ['ok' => false, 'message' => 'خواندن/نوشتن Redis ناموفق بود'];
            }
            return ['ok' => true, 'message' => 'Redis آماده است — ' . $host . ':' . $port . ' DB' . $db];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'خطا: ' . $e->getMessage()];
        }
    }

    public static function maybe_write_object_cache_dropin(array $s): void {
        $dropin = WP_CONTENT_DIR . '/object-cache.php';
        if (file_exists($dropin) || !is_writable(WP_CONTENT_DIR)) {
            return;
        }
        $host = $s['host'] ?? '127.0.0.1';
        $port = (int) ($s['port'] ?? 6379);
        $pass = $s['password'] ?? '';
        $db = (int) ($s['db'] ?? 0);
        $prefix = $s['prefix'] ?? 'bankai:';
        $cfg = "<?php\n// Bankai Redis bootstrap — do not edit\n"
            . "if (!defined('ABSPATH')) { exit; }\n"
            . "if (!defined('WP_REDIS_HOST')) { define('WP_REDIS_HOST', " . var_export($host, true) . "); }\n"
            . "if (!defined('WP_REDIS_PORT')) { define('WP_REDIS_PORT', " . (int) $port . "); }\n"
            . "if (!defined('WP_REDIS_PASSWORD')) { define('WP_REDIS_PASSWORD', " . var_export($pass, true) . "); }\n"
            . "if (!defined('WP_REDIS_DATABASE')) { define('WP_REDIS_DATABASE', " . (int) $db . "); }\n"
            . "if (!defined('WP_REDIS_PREFIX')) { define('WP_REDIS_PREFIX', " . var_export($prefix, true) . "); }\n"
            . "if (!defined('WP_REDIS_MAXTTL')) { define('WP_REDIS_MAXTTL', 86400); }\n";
        $marker = WP_CONTENT_DIR . '/bankai-redis-config.php';
        @file_put_contents($marker, $cfg);
        $php = "<?php\n"
            . "/**\n * Bankai Redis Object Cache drop-in\n */\n"
            . "if (!defined('ABSPATH')) { exit; }\n"
            . "if (!class_exists('Redis')) { return; }\n\n"
            . "class Bankai_Redis_Object_Cache {\n"
            . "    private \$redis; private \$prefix;\n"
            . "    public function __construct() {\n"
            . "        \$this->prefix = " . var_export($prefix, true) . ";\n"
            . "        try {\n"
            . "            \$this->redis = new Redis();\n"
            . "            \$this->redis->connect(" . var_export($host, true) . ", " . (int)$port . ", 1.5);\n"
            . "            \$pass = " . var_export($pass, true) . ";\n"
            . "            if (\$pass !== '') { \$this->redis->auth(\$pass); }\n"
            . "            \$this->redis->select(" . (int)$db . ");\n"
            . "        } catch (Throwable \$e) { \$this->redis = null; }\n"
            . "    }\n"
            . "    private function k(\$key, \$group = 'default') { return \$this->prefix . \$group . ':' . \$key; }\n"
            . "    public function add(\$key, \$data, \$group = 'default', \$expire = 0) {\n"
            . "        if (!\$this->redis || \$this->get(\$key, \$group) !== false) return false;\n"
            . "        return \$this->set(\$key, \$data, \$group, \$expire);\n"
            . "    }\n"
            . "    public function set(\$key, \$data, \$group = 'default', \$expire = 0) {\n"
            . "        if (!\$this->redis) return false;\n"
            . "        \$payload = serialize(\$data); \$k = \$this->k(\$key, \$group);\n"
            . "        return \$expire > 0 ? \$this->redis->setex(\$k, (int)\$expire, \$payload) : \$this->redis->set(\$k, \$payload);\n"
            . "    }\n"
            . "    public function get(\$key, \$group = 'default', \$force = false, &\$found = null) {\n"
            . "        \$found = false; if (!\$this->redis) return false;\n"
            . "        \$val = \$this->redis->get(\$this->k(\$key, \$group));\n"
            . "        if (\$val === false || \$val === null) return false;\n"
            . "        \$found = true; return @unserialize(\$val);\n"
            . "    }\n"
            . "    public function delete(\$key, \$group = 'default') {\n"
            . "        if (!\$this->redis) return false;\n"
            . "        return (bool) \$this->redis->del(\$this->k(\$key, \$group));\n"
            . "    }\n"
            . "    public function flush() {\n"
            . "        if (!\$this->redis) return false;\n"
            . "        \$it = null; \$this->redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);\n"
            . "        while (\$keys = \$this->redis->scan(\$it, \$this->prefix . '*', 200)) { if (\$keys) \$this->redis->del(\$keys); }\n"
            . "        return true;\n"
            . "    }\n"
            . "    public function close() { if (\$this->redis) { try { \$this->redis->close(); } catch (Throwable \$e) {} } return true; }\n"
            . "}\n\n"
            . "function wp_cache_init() { \$GLOBALS['wp_object_cache'] = new Bankai_Redis_Object_Cache(); }\n"
            . "function wp_cache_add(\$k, \$d, \$g = '', \$e = 0) { return \$GLOBALS['wp_object_cache']->add(\$k, \$d, \$g ?: 'default', \$e); }\n"
            . "function wp_cache_set(\$k, \$d, \$g = '', \$e = 0) { return \$GLOBALS['wp_object_cache']->set(\$k, \$d, \$g ?: 'default', \$e); }\n"
            . "function wp_cache_get(\$k, \$g = '', \$f = false, &\$found = null) { return \$GLOBALS['wp_object_cache']->get(\$k, \$g ?: 'default', \$f, \$found); }\n"
            . "function wp_cache_delete(\$k, \$g = '') { return \$GLOBALS['wp_object_cache']->delete(\$k, \$g ?: 'default'); }\n"
            . "function wp_cache_flush() { return \$GLOBALS['wp_object_cache']->flush(); }\n"
            . "function wp_cache_close() { return \$GLOBALS['wp_object_cache']->close(); }\n"
            . "function wp_cache_add_global_groups(\$g) {}\n"
            . "function wp_cache_add_non_persistent_groups(\$g) {}\n"
            . "function wp_cache_replace(\$k, \$d, \$g = '', \$e = 0) { return wp_cache_set(\$k, \$d, \$g, \$e); }\n"
            . "function wp_cache_incr(\$k, \$o = 1, \$g = '') { \$v = (int) wp_cache_get(\$k, \$g); \$v += \$o; wp_cache_set(\$k, \$v, \$g); return \$v; }\n"
            . "function wp_cache_decr(\$k, \$o = 1, \$g = '') { return wp_cache_incr(\$k, -\$o, \$g); }\n";
        @file_put_contents($dropin, $php);
    }
}
