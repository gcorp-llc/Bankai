<?php
/**
 * Collects real front-end metrics (TTFB samples, 404 log, LLM hits).
 */
defined('ABSPATH') || exit;

final class Bankai_Dashboard_Stats
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        add_action('template_redirect', [$this, 'track_404'], 1);
        add_action('shutdown', [$this, 'track_request_timing'], 0);
    }

    public function track_404(): void
    {
        if (!is_404()) {
            return;
        }
        $uri = isset($_SERVER['REQUEST_URI'])
            ? esc_url_raw(wp_unslash((string) $_SERVER['REQUEST_URI']))
            : '';
        if ($uri === '') {
            return;
        }

        $log = get_option('bankai_404_log', []);
        if (!is_array($log)) {
            $log = [];
        }

        $key = md5($uri);
        if (!isset($log[$key])) {
            $log[$key] = [
                'requested_uri' => $uri,
                'hits'          => 0,
                'first'         => current_time('mysql'),
                'last'          => current_time('mysql'),
            ];
        }
        $log[$key]['hits']++;
        $log[$key]['last'] = current_time('mysql');

        if (count($log) > 200) {
            uasort($log, static fn($a, $b) => ((int) ($b['hits'] ?? 0)) <=> ((int) ($a['hits'] ?? 0)));
            $log = array_slice($log, 0, 200, true);
        }
        update_option('bankai_404_log', $log, false);
    }

    public function track_request_timing(): void
    {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        if (!isset($GLOBALS['timestart'])) {
            return;
        }

        $elapsed_ms = (microtime(true) - (float) $GLOBALS['timestart']) * 1000;
        $samples    = get_transient('bankai_ttfb_samples');
        if (!is_array($samples)) {
            $samples = [];
        }
        $samples[] = round($elapsed_ms, 2);
        if (count($samples) > 50) {
            $samples = array_slice($samples, -50);
        }
        set_transient('bankai_ttfb_samples', $samples, DAY_IN_SECONDS);

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        if ($uri !== '' && (str_contains($uri, 'llms.txt') || str_contains($uri, '/llm'))) {
            update_option('bankai_llm_hits', (int) get_option('bankai_llm_hits', 0) + 1, false);
        }
    }

    /**
     * Real telemetry for overview tab (replaces demo numbers).
     *
     * @return array<string, string|int|float|null>
     */
    public static function telemetry_stats(): array
    {
        $posts = (int) (wp_count_posts('post')->publish ?? 0);
        $pages = (int) (wp_count_posts('page')->publish ?? 0);
        $indexable = $posts + $pages;

        global $wpdb;
        $scores = $wpdb->get_col(
            "SELECT meta_value FROM {$wpdb->postmeta}
             WHERE meta_key = '_bankai_seo_score' AND meta_value REGEXP '^[0-9]+$'
             LIMIT 500"
        );
        $nums = array_map('intval', $scores ?: []);
        $avg_seo = $nums ? (int) round(array_sum($nums) / count($nums)) : 0;

        $with_schema = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
             WHERE meta_key = '_bankai_seo_schema' AND meta_value != ''"
        );
        $schema_pct = $indexable > 0
            ? (int) min(100, round((max($with_schema, count($nums)) / max(1, $indexable)) * 100))
            : 0;
        $schema_score = (int) round(($schema_pct * 0.4) + ($avg_seo * 0.6));

        $samples = get_transient('bankai_ttfb_samples');
        if (!is_array($samples)) {
            $samples = [];
        }
        $avg_ttfb = $samples ? round(array_sum($samples) / count($samples), 1) : null;
        $ok = 0;
        foreach ($samples as $ms) {
            if ((float) $ms < 2000) {
                $ok++;
            }
        }
        $stability = $samples ? round(($ok / count($samples)) * 100, 2) : null;

        $cache = get_option('bankai_cache_stats', []);
        if (!is_array($cache)) {
            $cache = [];
        }
        $hits   = isset($cache['hits']) ? (int) $cache['hits'] : null;
        $misses = isset($cache['misses']) ? (int) $cache['misses'] : null;
        $varnish = null;
        if ($hits !== null && $misses !== null && ($hits + $misses) > 0) {
            $varnish = round(($hits / ($hits + $misses)) * 100, 1);
        }

        $cwv = get_option('bankai_cwv_metrics', []);
        if (!is_array($cwv)) {
            $cwv = [];
        }

        $grade = self::grade($avg_seo);

        return [
            'uptime'         => $stability !== null ? $stability . '%' : '—',
            'avg_latency'    => $avg_ttfb !== null ? $avg_ttfb . 'ms' : '—',
            'indexed_nodes'  => number_format_i18n($indexable),
            'varnish_hit'    => $varnish !== null ? $varnish . '%' : '—',
            'ai_crawls'      => number_format_i18n((int) get_option('bankai_llm_hits', 0)),
            'schema_score'   => $schema_score . '/100',
            'overall_score'  => (string) $avg_seo,
            'ttfb'           => $avg_ttfb !== null ? $avg_ttfb . 'ms' : '—',
            'lcp'            => isset($cwv['lcp']) ? $cwv['lcp'] . 's' : '—',
            'cls'            => isset($cwv['cls']) ? (string) $cwv['cls'] : '—',
            'grade'          => $grade,
            'scored_posts'   => count($nums),
            'sample_count'   => count($samples),
            'posts'          => $posts,
            'pages'          => $pages,
        ];
    }

    /**
     * @return list<array{requested_uri:string,hits:int}>
     */
    public static function logs_404(int $limit = 10): array
    {
        $log = get_option('bankai_404_log', []);
        if (!is_array($log) || !$log) {
            return [];
        }
        uasort($log, static fn($a, $b) => ((int) ($b['hits'] ?? 0)) <=> ((int) ($a['hits'] ?? 0)));
        $out = [];
        foreach (array_slice($log, 0, $limit, true) as $row) {
            $out[] = [
                'requested_uri' => (string) ($row['requested_uri'] ?? $row['uri'] ?? ''),
                'hits'          => (int) ($row['hits'] ?? $row['count'] ?? 0),
            ];
        }
        return $out;
    }

    /**
     * Core module switches mapped to bankai_core_settings.active_modules.
     *
     * @return list<array{key:string,label:string,label_fa:string,desc:string,desc_fa:string,active:bool}>
     */
    public static function core_modules(): array
    {
        $map = [
            'seo_engine' => [
                'label'    => 'SEO & Schema Engine',
                'label_fa' => 'موتور سئو و اسکیما',
                'desc'     => 'Automated meta generation, Schema.org builder & XML Sitemaps.',
                'desc_fa'  => 'تولید خودکار متاتگ‌ها، تولیدکننده کدهای اسکیما و نقشه‌های داینامیک XML.',
            ],
            'media_watermark' => [
                'label'    => 'Media Optimizer',
                'label_fa' => 'بهینه‌ساز رسانه',
                'desc'     => 'WebP/AVIF auto-conversion, async processor & lazyloading.',
                'desc_fa'  => 'تبدیل خودکار به WebP/AVIF، پردازش ناهمگام و لود تنبل تصاویر.',
            ],
            'speed_cache' => [
                'label'    => 'Speed & Cache Engine',
                'label_fa' => 'موتور کش و شتاب‌دهنده',
                'desc'     => 'Zero-latency dynamic HTML page caching & Redis object store.',
                'desc_fa'  => 'کش صفحات HTML فوق‌سریع و پایگاه داده کش اشیاء ردیس.',
            ],
            'llms_txt' => [
                'label'    => 'LLM Manifest',
                'label_fa' => 'مانیفست هوش مصنوعی',
                'desc'     => 'Structured markdown index endpoints for AI agents.',
                'desc_fa'  => 'اندپوینت‌های استاندارد متنی مارک‌داون برای دستیاران هوش مصنوعی و LLMها.',
            ],
            'ai_studio' => [
                'label'    => 'AI Content Studio',
                'label_fa' => 'استودیو هوش مصنوعی',
                'desc'     => 'Server-side AI content generation & prompt engineering.',
                'desc_fa'  => 'تولید محتوا، ایده و تیترهای سئو شده با مدل‌های پیشرفته هوش مصنوعی.',
            ],
            'theme_kits' => [
                'label'    => 'Theme Kits',
                'label_fa' => 'کیت‌های قالب',
                'desc'     => 'Starter kits and typography presets.',
                'desc_fa'  => 'کیت‌های استارتر و پیش‌فرض‌های تایپوگرافی.',
            ],
        ];

        $out = [];
        foreach ($map as $key => $meta) {
            $active = function_exists('bankai_is_module_active')
                ? bankai_is_module_active($key)
                : true;
            $out[] = array_merge($meta, [
                'key'    => $key,
                'active' => $active,
            ]);
        }
        return $out;
    }

    private static function grade(int $score): string
    {
        if ($score >= 90) {
            return 'A+';
        }
        if ($score >= 80) {
            return 'A';
        }
        if ($score >= 70) {
            return 'B';
        }
        if ($score >= 55) {
            return 'C';
        }
        if ($score > 0) {
            return 'D';
        }
        return '—';
    }
}
