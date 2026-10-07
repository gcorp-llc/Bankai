<?php
/**
 * First-party site analytics — device, country, daily series
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

class Bankai_Site_Analytics {

    public const TABLE_VER = '2';

    public static function init(): void {
        $self = new self();
        add_action('init', [$self, 'maybe_install'], 5);
        add_action('wp_footer', [$self, 'print_beacon'], 99);
        add_action('rest_api_init', [$self, 'register_routes']);
    }

    public function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'bankai_analytics';
    }

    public function maybe_install(): void {
        if (get_option('bankai_analytics_table_ver') === self::TABLE_VER) {
            return;
        }
        global $wpdb;
        $table = $this->table();
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            viewed_at DATE NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            device VARCHAR(16) NOT NULL DEFAULT 'desktop',
            country VARCHAR(8) NOT NULL DEFAULT '',
            hits INT UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY day_post_dev_co (viewed_at, post_id, device, country),
            KEY viewed_at (viewed_at),
            KEY country (country),
            KEY device (device)
        ) {$charset};";
        dbDelta($sql);
        update_option('bankai_analytics_table_ver', self::TABLE_VER, false);
    }

    public function register_routes(): void {
        register_rest_route('bankai/v1', '/analytics/beacon', [
            'methods'             => 'POST',
            'callback'            => [$this, 'api_beacon'],
            'permission_callback' => '__return_true',
        ]);
        register_rest_route('bankai/v1', '/analytics/report', [
            'methods'             => 'GET',
            'callback'            => [$this, 'api_report'],
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },
        ]);
    }

    public static function detect_device(?string $ua = null): string {
        $ua = strtolower($ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        if ($ua === '') {
            return 'desktop';
        }
        if (preg_match('/ipad|tablet|playbook|silk|(android(?!.*mobile))/i', $ua)) {
            return 'tablet';
        }
        if (preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry/i', $ua)) {
            return 'mobile';
        }
        return 'desktop';
    }

    public static function detect_country(): string {
        // Cloudflare / proxy headers first
        $headers = [
            'HTTP_CF_IPCOUNTRY',
            'HTTP_X_COUNTRY_CODE',
            'HTTP_X_APPENGINE_COUNTRY',
            'GEOIP_COUNTRY_CODE',
        ];
        foreach ($headers as $h) {
            if (! empty($_SERVER[$h]) && preg_match('/^[A-Z]{2}$/i', $_SERVER[$h])) {
                return strtoupper(substr((string) $_SERVER[$h], 0, 2));
            }
        }
        $ip = self::client_ip();
        if ($ip === '' || $ip === '127.0.0.1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return 'LO'; // local
        }
        // Lightweight cache per IP/day
        $ckey = 'bankai_geo_' . md5($ip);
        $cached = get_transient($ckey);
        if (is_string($cached) && preg_match('/^[A-Z]{2}$/', $cached)) {
            return $cached;
        }
        // Free endpoint (short timeout) — fail soft
        $code = '';
        $resp = wp_remote_get('https://ipapi.co/' . rawurlencode($ip) . '/country/', [
            'timeout' => 2,
            'headers' => ['User-Agent' => 'BankaiCore/1.0'],
        ]);
        if (! is_wp_error($resp) && (int) wp_remote_retrieve_response_code($resp) === 200) {
            $body = trim((string) wp_remote_retrieve_body($resp));
            if (preg_match('/^[A-Z]{2}$/i', $body)) {
                $code = strtoupper($body);
            }
        }
        if ($code === '') {
            $code = 'XX';
        }
        set_transient($ckey, $code, DAY_IN_SECONDS);
        return $code;
    }

    public static function client_ip(): string {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $h) {
            if (empty($_SERVER[$h])) {
                continue;
            }
            $raw = (string) $_SERVER[$h];
            if (str_contains($raw, ',')) {
                $raw = trim(explode(',', $raw)[0]);
            }
            if (filter_var($raw, FILTER_VALIDATE_IP)) {
                return $raw;
            }
        }
        return '';
    }

    public static function is_bot(?string $ua = null): bool {
        $ua = strtolower($ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $bots = ['googlebot', 'bingbot', 'yandex', 'baiduspider', 'duckduckbot', 'slurp', 'bot/', 'crawler', 'spider', 'facebookexternalhit'];
        foreach ($bots as $b) {
            if (str_contains($ua, $b)) {
                return true;
            }
        }
        return false;
    }

    public function api_beacon(WP_REST_Request $request): WP_REST_Response {
        if (self::is_bot()) {
            return new WP_REST_Response(['ok' => false, 'reason' => 'bot'], 200);
        }
        $post_id = absint($request->get_param('post_id'));
        $device  = sanitize_key((string) $request->get_param('device'));
        if (! in_array($device, ['desktop', 'mobile', 'tablet'], true)) {
            $device = self::detect_device();
        }
        $country = self::detect_country();
        $day = current_time('Y-m-d');

        $ip = self::client_ip();
        $dedupe = 'bankai_an_' . md5($ip . '|' . $day . '|' . $post_id . '|' . $device);
        if (get_transient($dedupe)) {
            return new WP_REST_Response(['ok' => false, 'reason' => 'dedupe'], 200);
        }
        set_transient($dedupe, 1, HOUR_IN_SECONDS);

        global $wpdb;
        $table = $this->table();
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$table} (viewed_at, post_id, device, country, hits)
             VALUES (%s, %d, %s, %s, 1)
             ON DUPLICATE KEY UPDATE hits = hits + 1",
            $day,
            $post_id,
            $device,
            $country
        ));

        return new WP_REST_Response(['ok' => true], 200);
    }

    public function api_report(WP_REST_Request $request): WP_REST_Response {
        $range = sanitize_key((string) ($request->get_param('range') ?: '30d'));
        $days = match ($range) {
            '24h', '1d' => 1,
            '7d' => 7,
            '1y', '365d' => 365,
            default => 30,
        };
        $from = gmdate('Y-m-d', time() - ($days * DAY_IN_SECONDS));

        global $wpdb;
        $table = $this->table();

        // Ensure table exists
        $this->maybe_install();

        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(hits),0) FROM {$table} WHERE viewed_at >= %s",
            $from
        ));

        $prev_from = gmdate('Y-m-d', time() - (2 * $days * DAY_IN_SECONDS));
        $prev_to = $from;
        $prev_total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(hits),0) FROM {$table} WHERE viewed_at >= %s AND viewed_at < %s",
            $prev_from,
            $prev_to
        ));

        $series = $wpdb->get_results($wpdb->prepare(
            "SELECT viewed_at AS d, SUM(hits) AS h FROM {$table}
             WHERE viewed_at >= %s GROUP BY viewed_at ORDER BY viewed_at ASC",
            $from
        ), ARRAY_A) ?: [];

        $devices = $wpdb->get_results($wpdb->prepare(
            "SELECT device, SUM(hits) AS h FROM {$table}
             WHERE viewed_at >= %s GROUP BY device",
            $from
        ), ARRAY_A) ?: [];

        $countries = $wpdb->get_results($wpdb->prepare(
            "SELECT country, SUM(hits) AS h FROM {$table}
             WHERE viewed_at >= %s AND country != '' GROUP BY country ORDER BY h DESC LIMIT 20",
            $from
        ), ARRAY_A) ?: [];

        $top_posts = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, SUM(hits) AS h FROM {$table}
             WHERE viewed_at >= %s AND post_id > 0 GROUP BY post_id ORDER BY h DESC LIMIT 8",
            $from
        ), ARRAY_A) ?: [];

        $dev_map = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
        foreach ($devices as $row) {
            $k = $row['device'];
            if (isset($dev_map[$k])) {
                $dev_map[$k] = (int) $row['h'];
            }
        }
        $dev_sum = max(1, array_sum($dev_map));

        $change = 0.0;
        if ($prev_total > 0) {
            $change = round((($total - $prev_total) / $prev_total) * 100, 1);
        } elseif ($total > 0) {
            $change = 100.0;
        }

        $series_filled = [];
        $map = [];
        foreach ($series as $row) {
            $map[$row['d']] = (int) $row['h'];
        }
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = gmdate('Y-m-d', time() - ($i * DAY_IN_SECONDS));
            $series_filled[] = ['d' => $d, 'h' => $map[$d] ?? 0];
        }

        $posts_out = [];
        foreach ($top_posts as $row) {
            $pid = (int) $row['post_id'];
            $posts_out[] = [
                'id'    => $pid,
                'title' => get_the_title($pid) ?: ('#' . $pid),
                'hits'  => (int) $row['h'],
                'url'   => get_permalink($pid) ?: '',
            ];
        }

        // Unique countries count
        $country_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT country) FROM {$table} WHERE viewed_at >= %s AND country NOT IN ('','XX','LO')",
            $from
        ));

        return new WP_REST_Response([
            'range'          => $range,
            'days'           => $days,
            'visits'         => $total,
            'visits_change'  => $change,
            'prev_visits'    => $prev_total,
            'series'         => $series_filled,
            'devices'        => [
                'desktop' => ['hits' => $dev_map['desktop'], 'pct' => round($dev_map['desktop'] / $dev_sum * 100)],
                'mobile'  => ['hits' => $dev_map['mobile'], 'pct' => round($dev_map['mobile'] / $dev_sum * 100)],
                'tablet'  => ['hits' => $dev_map['tablet'], 'pct' => round($dev_map['tablet'] / $dev_sum * 100)],
            ],
            'countries'      => array_map(static function ($r) {
                return ['code' => $r['country'], 'hits' => (int) $r['h']];
            }, $countries),
            'country_count'  => $country_count,
            'top_posts'      => $posts_out,
            'generated_at'   => current_time('c'),
        ], 200);
    }

    public function print_beacon(): void {
        if (is_admin()) {
            return;
        }
        $post_id = is_singular() ? (int) get_queried_object_id() : 0;
        $url = esc_url_raw(rest_url('bankai/v1/analytics/beacon'));
        $js = sprintf(
            "(function(){try{var d='desktop';var ua=navigator.userAgent||'';"
            . "if(/iPad|Tablet|Android(?!.*Mobile)/i.test(ua))d='tablet';"
            . "else if(/Mobile|iPhone|Android.*Mobile|Windows Phone/i.test(ua))d='mobile';"
            . "fetch(%s,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},"
            . "body:JSON.stringify({post_id:%d,device:d})});}catch(e){}})();",
            wp_json_encode($url),
            $post_id
        );
        echo '<script>' . $js . '</script>' . "\n";
    }
}

Bankai_Site_Analytics::init();
