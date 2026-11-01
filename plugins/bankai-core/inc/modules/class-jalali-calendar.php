<?php
/**
 * Jalali (Shamsi) calendar — Bankai
 *
 * Native Bankai Jalali calendar and editor date picker.
 *
 * @package Bankai
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class Bankai_Jalali_Calendar
{
    private static $instance = null;

    /** @var bool */
    private $converting = false;

    /** @var string[] */
    private static $months = [
        1  => 'فروردین',
        2  => 'اردیبهشت',
        3  => 'خرداد',
        4  => 'تیر',
        5  => 'مرداد',
        6  => 'شهریور',
        7  => 'مهر',
        8  => 'آبان',
        9  => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند',
    ];

    /** @var string[] */
    private static $weekdays = [
        'Sunday'    => 'یکشنبه',
        'Monday'    => 'دوشنبه',
        'Tuesday'   => 'سه‌شنبه',
        'Wednesday' => 'چهارشنبه',
        'Thursday'  => 'پنجشنبه',
        'Friday'    => 'جمعه',
        'Saturday'  => 'شنبه',
    ];

    /** @var string[] */
    private static $weekdays_short = [
        'Sun' => 'ی',
        'Mon' => 'د',
        'Tue' => 'س',
        'Wed' => 'چ',
        'Thu' => 'پ',
        'Fri' => 'ج',
        'Sat' => 'ش',
    ];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        if (function_exists('bankai_is_module_active') && !bankai_is_module_active('jalali_calendar')) {
            return;
        }

        add_filter('wp_date', [$this, 'filter_wp_date'], 10, 4);
        add_filter('date_i18n', [$this, 'filter_date_i18n'], 10, 4);

        add_filter('the_time', [$this, 'filter_the_time'], 10, 2);
        add_filter('get_the_time', [$this, 'filter_get_the_time'], 10, 3);
        add_filter('the_date', [$this, 'filter_the_date'], 10, 2);
        add_filter('get_the_date', [$this, 'filter_get_the_date'], 100, 3);
        add_filter('get_the_modified_date', [$this, 'filter_get_the_modified_date'], 10, 3);
        add_filter('get_the_modified_time', [$this, 'filter_get_the_modified_time'], 10, 3);
        add_filter('get_comment_time', [$this, 'filter_comment_time'], 10, 2);
        add_filter('get_comment_date', [$this, 'filter_comment_date'], 10, 3);

        add_filter('post_date_column_time', [$this, 'filter_post_date_column'], 10, 4);
        add_filter('media_view_settings', [$this, 'filter_media_view_settings'], 10, 2);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_footer', [$this, 'admin_footer_marker']);

        // ACF date picker integration (Jalali UI, Gregorian storage)
        // Pattern: wp_date → render_field / acf_field_date_picker
        add_action('acf/render_field/type=date_picker', [$this, 'acf_render_date_picker'], 20, 1);
        add_action('acf/render_field/type=date_time_picker', [$this, 'acf_render_date_picker'], 20, 1);
        add_filter('acf/format_value/type=date_picker', [$this, 'acf_format_date_value'], 20, 3);
        add_filter('acf/format_value/type=date_time_picker', [$this, 'acf_format_date_value'], 20, 3);
        // Pattern without class: wp_date, acf_format_date
        add_filter('acf/format_date', [$this, 'acf_format_date'], 20, 2);
        add_action('acf/input/admin_enqueue_scripts', [$this, 'acf_enqueue_assets']);
    }

    /**
     * This module intentionally has no settings screen.
     * Keep behavior deterministic and avoid depending on optional option storage.
     *
     * @return array<string,mixed>
     */
    private function settings(): array
    {
        return [
            'persian_digits' => true,
            'dual_date'      => false,
            'datepicker'     => true,
            'admin_column'   => true,
        ];
    }

    /* ---------- ACF date picker (Jalali) ---------- */

    public function acf_enqueue_assets(): void
    {
        $this->enqueue_admin_assets('acf');
    }

    /**
     * @param array<string,mixed> $field
     */
    public function acf_render_date_picker($field): void
    {
        if (empty($field) || !is_array($field)) {
            return;
        }
        $settings = $this->settings();
        if (empty($settings['datepicker'])) {
            return;
        }
        $key = isset($field['key']) ? (string) $field['key'] : '';
        $name = isset($field['name']) ? (string) $field['name'] : '';
        $type = isset($field['type']) ? (string) $field['type'] : 'date_picker';
        $val = isset($field['value']) ? (string) $field['value'] : '';
        $jalali_hint = '';
        if ($val !== '') {
            $ts = $this->acf_value_to_timestamp($val);
            if ($ts) {
                $jalali_hint = $this->format_jalali($ts, 'Y/m/d');
            }
        }
        echo '<div class="bk-acf-jalali-wrap" data-bk-acf-date="1" data-field-key="' . esc_attr($key) . '" data-field-name="' . esc_attr($name) . '" data-field-type="' . esc_attr($type) . '">';
        if ($jalali_hint !== '') {
            echo '<p class="description bk-acf-jalali-hint" style="margin:6px 0 0;color:#6d28d9;font-weight:700;">📅 ' . esc_html($jalali_hint) . '</p>';
        }
        echo '</div>';
    }

    /**
     * @param mixed $value
     * @param int|string $post_id
     * @param array<string,mixed> $field
     * @return mixed
     */
    public function acf_format_date_value($value, $post_id, $field)
    {
        if ($value === null || $value === '' || $value === false) {
            return $value;
        }
        $ts = $this->acf_value_to_timestamp((string) $value);
        if (!$ts) {
            return $value;
        }
        $fmt = !empty($field['display_format']) ? (string) $field['display_format'] : (string) get_option('date_format', 'Y/m/d');
        return $this->format_jalali($ts, $fmt);
    }

    /**
     * @param string $date
     * @param string $format
     */
    public function acf_format_date($date, $format = '')
    {
        if ($this->converting || $date === '' || $date === null) {
            return $date;
        }
        $ts = $this->acf_value_to_timestamp((string) $date);
        if (!$ts) {
            $try = strtotime((string) $date);
            if (!$try) {
                return $date;
            }
            $ts = $try;
        }
        $fmt = $format !== '' ? (string) $format : (string) get_option('date_format', 'Y/m/d');
        return $this->format_jalali($ts, $fmt);
    }

    private function acf_value_to_timestamp(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $timezone = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC');

        try {
            if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $match)) {
                $date = sprintf('%04d-%02d-%02d 12:00:00', (int) $match[1], (int) $match[2], (int) $match[3]);
                $dt = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $date, $timezone);
                return $dt ? $dt->getTimestamp() : 0;
            }

            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?/', $value, $match)) {
                $date = sprintf(
                    '%04d-%02d-%02d %02d:%02d:%02d',
                    (int) $match[1],
                    (int) $match[2],
                    (int) $match[3],
                    isset($match[4]) ? (int) $match[4] : 12,
                    isset($match[5]) ? (int) $match[5] : 0,
                    isset($match[6]) ? (int) $match[6] : 0
                );
                $dt = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $date, $timezone);
                return $dt ? $dt->getTimestamp() : 0;
            }

            $dt = new DateTimeImmutable($value, $timezone);
            return $dt->getTimestamp();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Parse a WordPress MySQL date as a site-local date/time.
     */
    private function mysql_date_timestamp(string $value): int
    {
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return 0;
        }

        try {
            $timezone = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC');
            $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value, $timezone);
            if ($date instanceof DateTimeImmutable) {
                return $date->getTimestamp();
            }
            $date = new DateTimeImmutable($value, $timezone);
            return $date->getTimestamp();
        } catch (Exception $e) {
            return 0;
        }
    }

    /* ---------- Filters ---------- */

    public function filter_wp_date($date, $format, $timestamp, $timezone)
    {
        if ($this->converting || $this->is_machine_format((string) $format)) {
            return $date;
        }
        $this->converting = true;
        try {
            $ts = is_numeric($timestamp) ? (int) $timestamp : time();
            $tz = $timezone instanceof DateTimeZone
                ? $timezone
                : (function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC'));
            return $this->format_jalali($ts, (string) $format, $tz);
        } finally {
            $this->converting = false;
        }
    }

    public function filter_date_i18n($date, $format, $timestamp, $gmt)
    {
        if ($this->converting || $this->is_machine_format((string) $format)) {
            return $date;
        }
        $this->converting = true;
        try {
            $ts = $timestamp ? (int) $timestamp : time();
            return $this->format_jalali($ts, (string) $format);
        } finally {
            $this->converting = false;
        }
    }

    public function filter_the_time($time, $format = '')
    {
        global $post;
        return $this->format_post_field($time, $format ?: (string) get_option('time_format'), $post, 'post_date');
    }

    public function filter_get_the_time($time, $format = '', $post = null)
    {
        $post = get_post($post);
        if (!$post) {
            global $post;
        }
        return $this->format_post_field($time, $format ?: (string) get_option('time_format'), $post, 'post_date');
    }

    public function filter_get_the_modified_time($time, $format = '', $post = null)
    {
        if ($post === null) {
            global $post;
        } else {
            $post = get_post($post);
        }
        if (!$post || empty($post->post_modified)) {
            return $time;
        }
        $format = $format ?: (string) get_option('time_format');
        if ($this->is_machine_format($format)) {
            return $time;
        }
        return $this->format_jalali($this->mysql_date_timestamp((string) $post->post_modified), $format);
    }

    public function filter_the_date($time, $format = '')
    {
        global $post;
        return $this->format_post_field($time, $format ?: (string) get_option('date_format'), $post, 'post_date');
    }

    public function filter_get_the_date($time, $format = '', $post = null)
    {
        if ($post === null) {
            global $post;
        } else {
            $post = get_post($post);
        }
        if (!$post) {
            return $time;
        }
        $format = $format ?: (string) get_option('date_format', 'F j, Y');
        if ($this->is_machine_format($format)) {
            return $time;
        }
        return $this->format_jalali($this->mysql_date_timestamp((string) $post->post_date), $format);
    }

    public function filter_get_the_modified_date($time, $format = '', $post = null)
    {
        if ($post === null) {
            global $post;
        } else {
            $post = get_post($post);
        }
        if (!$post) {
            return $time;
        }
        $format = $format ?: (string) get_option('date_format');
        if ($this->is_machine_format($format)) {
            return $time;
        }
        return $this->format_jalali($this->mysql_date_timestamp((string) $post->post_modified), $format);
    }

    public function filter_comment_date($comment_date, $format = '', $comment = null)
    {
        if ($comment === null) {
            return $comment_date;
        }
        $format = $format ?: (string) get_option('date_format');
        if ($this->is_machine_format($format)) {
            return $comment_date;
        }
        return $this->format_jalali($this->mysql_date_timestamp((string) $comment->comment_date), $format);
    }

    public function filter_comment_time($time, $format = '')
    {
        global $comment;
        if (empty($comment)) {
            return $time;
        }
        $format = $format ?: (string) get_option('time_format');
        return $this->format_jalali($this->mysql_date_timestamp((string) $comment->comment_date), $format);
    }

    public function filter_post_date_column($h_time, $post, $column_name = '', $mode = '')
    {
        $s = $this->settings();
        if (isset($s['admin_column']) && !$s['admin_column']) {
            return $h_time;
        }
        $ts = get_post_time('U', true, $post);
        if (!$ts) {
            return $h_time;
        }
        return $this->format_jalali((int) $ts, 'Y/m/d H:i');
    }

    public function filter_media_view_settings($settings, $post)
    {
        if (empty($settings['months']) || !is_array($settings['months'])) {
            return $settings;
        }
        foreach ($settings['months'] as $i => $row) {
            if (!isset($row->year, $row->month)) {
                continue;
            }
            [$jy, $jm] = self::gregorian_to_jalali((int) $row->year, (int) $row->month, 15);
            $name = self::$months[$jm] ?? (string) $jm;
            $settings['months'][$i]->text = $name . ' ' . $jy;
        }
        return $settings;
    }

    /**
     * @param mixed $post
     */
    private function format_post_field($fallback, string $format, $post, string $field)
    {
        if (empty($post) || empty($post->$field)) {
            return $fallback;
        }
        if ($this->is_machine_format($format)) {
            return $fallback;
        }
        return $this->format_jalali($this->mysql_date_timestamp((string) $post->$field), $format);
    }

    /* ---------- Format core ---------- */

    private function is_machine_format(string $format): bool
    {
        $machine = [
            'c', 'r', 'U', 'u', 'timestamp',
            DATE_ATOM, DATE_COOKIE, DATE_ISO8601,
            DATE_RFC2822, DATE_RFC3339, DATE_RSS, DATE_W3C,
        ];
        if (in_array($format, $machine, true)) {
            return true;
        }
        $clean = preg_replace('/\\\\./', '', $format);
        return !(bool) preg_match('/[dDjlNSwzWFmMntLoYyS]/', (string) $clean);
    }

    public static function format_timestamp(int $timestamp, string $format): string
    {
        return self::instance()->format_jalali($timestamp, $format);
    }

    public function format_jalali(int $timestamp, string $format, $timezone = null): string
    {
        try {
            $tz = $timezone instanceof DateTimeZone
                ? $timezone
                : (function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC'));
            $dt = new DateTime('@' . $timestamp);
            $dt->setTimezone($tz);
        } catch (Exception $e) {
            return (string) $timestamp;
        }

        $gy = (int) $dt->format('Y');
        $gm = (int) $dt->format('n');
        $gd = (int) $dt->format('j');
        [$jy, $jm, $jd] = self::gregorian_to_jalali($gy, $gm, $gd);

        $month_name   = self::$months[$jm] ?? (string) $jm;
        $weekday_en   = $dt->format('l');
        $weekday_fa   = self::$weekdays[$weekday_en] ?? $weekday_en;
        $weekday_short = self::$weekdays_short[$dt->format('D')] ?? (function_exists('mb_substr') ? mb_substr($weekday_fa, 0, 1, 'UTF-8') : substr($weekday_fa, 0, 2));
        $jalali_day_of_year = ($jm <= 6)
            ? (($jm - 1) * 31 + $jd - 1)
            : (186 + (($jm - 7) * 30) + $jd - 1);
        $jalali_month_days = $jm <= 6 ? 31 : ($jm <= 11 ? 30 : (
            (int) round((
                gmmktime(0, 0, 0, self::jalali_to_gregorian($jy + 1, 1, 1)[1], self::jalali_to_gregorian($jy + 1, 1, 1)[2], self::jalali_to_gregorian($jy + 1, 1, 1)[0])
                - gmmktime(0, 0, 0, self::jalali_to_gregorian($jy, 12, 1)[1], self::jalali_to_gregorian($jy, 12, 1)[2], self::jalali_to_gregorian($jy, 12, 1)[0])
            ) / 86400)
        ));
        $year_start_gregorian = self::jalali_to_gregorian($jy, 1, 1);
        $year_start_date = DateTimeImmutable::createFromFormat(
            '!Y-n-j',
            implode('-', $year_start_gregorian),
            $tz
        );
        $year_start_weekday = $year_start_date ? (((int) $year_start_date->format('w') + 1) % 7) : 0;
        $jalali_week = intdiv($jalali_day_of_year + $year_start_weekday, 7) + 1;

        $map = [
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'j' => (string) $jd,
            'D' => $weekday_short,
            'l' => $weekday_fa,
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'n' => (string) $jm,
            'F' => $month_name,
            'M' => (function_exists('mb_substr') ? mb_substr($month_name, 0, 3, 'UTF-8') : $month_name),
            'Y' => (string) $jy,
            'y' => substr((string) $jy, -2),
            'o' => (string) $jy,
            'z' => (string) $jalali_day_of_year,
            'W' => str_pad((string) $jalali_week, 2, '0', STR_PAD_LEFT),
            'N' => $dt->format('N'),
            'w' => $dt->format('w'),
            't' => (string) $jalali_month_days,
            'L' => $jalali_month_days === 30 && $jm === 12 ? '1' : '0',
            'S' => 'م',
            'H' => $dt->format('H'),
            'G' => $dt->format('G'),
            'h' => $dt->format('h'),
            'g' => $dt->format('g'),
            'i' => $dt->format('i'),
            's' => $dt->format('s'),
            'A' => $dt->format('A') === 'AM' ? 'ق.ظ' : 'ب.ظ',
            'a' => $dt->format('a') === 'am' ? 'ق.ظ' : 'ب.ظ',
        ];

        $out = '';
        $len = strlen($format);
        for ($i = 0; $i < $len; $i++) {
            $ch = $format[$i];
            if ($ch === '\\' && $i + 1 < $len) {
                $out .= $format[++$i];
                continue;
            }
            $out .= $map[$ch] ?? $ch;
        }

        $settings = $this->settings();
        if (!empty($settings['persian_digits'])) {
            $out = $this->to_persian_digits($out);
        }

        if (!empty($settings['dual_date']) && preg_match('/[dDjlNSwzWFmMntLoYyS]/', $format)) {
            $g = $dt->format($format);
            if ($g && $g !== $out) {
                $sep = apply_filters('bankai_jalali_dual_separator', ' - ', $format, $timestamp);
                $out .= $sep . $g;
            }
        }

        return $out;
    }

    private function to_persian_digits(string $s): string
    {
        return strtr($s, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    /**
     * Gregorian to Jalali using the break-year algorithm (not a fixed 33-year cycle).
     * The supported range follows the published Jalali break table.
     *
     * @return array{0:int,1:int,2:int}
     */
    public static function gregorian_to_jalali(int $gy, int $gm, int $gd): array
    {
        if (!checkdate($gm, $gd, $gy)) {
            throw new InvalidArgumentException('Invalid Gregorian date.');
        }

        $jdn = self::gregorian_to_jdn($gy, $gm, $gd);
        return self::jdn_to_jalali($jdn);
    }

    /**
     * Jalali to Gregorian using the break-year algorithm.
     *
     * @return array{0:int,1:int,2:int}
     */
    public static function jalali_to_gregorian(int $jy, int $jm, int $jd): array
    {
        if ($jm < 1 || $jm > 12 || $jd < 1) {
            throw new InvalidArgumentException('Invalid Jalali date.');
        }

        $first = self::jalali_to_jdn($jy, $jm, 1);
        $next  = $jm === 12
            ? self::jalali_to_jdn($jy + 1, 1, 1)
            : self::jalali_to_jdn($jy, $jm + 1, 1);
        $month_days = $next - $first;

        if ($jd > $month_days) {
            throw new InvalidArgumentException('Invalid day for Jalali month.');
        }

        $gregorian = self::jdn_to_gregorian($first + $jd - 1);
        return [$gregorian['gy'], $gregorian['gm'], $gregorian['gd']];
    }

    private static function floor_div(int $a, int $b): int
    {
        return (int) floor($a / $b);
    }

    private static function positive_mod(int $a, int $b): int
    {
        return $a - self::floor_div($a, $b) * $b;
    }

    /**
     * @return array{leap:int,gy:int,march:int}
     */
    private static function jalali_calendar(int $jy): array
    {
        $breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
        $count = count($breaks);
        if ($jy < $breaks[0] || $jy >= $breaks[$count - 1]) {
            throw new InvalidArgumentException('Jalali year is outside the supported conversion range.');
        }

        $gy = $jy + 621;
        $leap_j = -14;
        $jp = $breaks[0];
        $jump = 0;

        for ($i = 1; $i < $count; $i++) {
            $jm = $breaks[$i];
            $jump = $jm - $jp;
            if ($jy < $jm) {
                break;
            }
            $leap_j += self::floor_div($jump, 33) * 8
                + self::floor_div(self::positive_mod($jump, 33), 4);
            $jp = $jm;
        }

        $n = $jy - $jp;
        $leap_j += self::floor_div($n, 33) * 8
            + self::floor_div(self::positive_mod($n, 33) + 3, 4);

        if (self::positive_mod($jump, 33) === 4 && $jump - $n === 4) {
            $leap_j++;
        }

        $leap_g = self::floor_div($gy, 4)
            - self::floor_div((self::floor_div($gy, 100) + 1) * 3, 4)
            - 150;
        $march = 20 + $leap_j - $leap_g;

        if ($jump - $n < 6) {
            $n = $n - $jump + self::floor_div($jump + 4, 33) * 33;
        }

        $leap = (($n + 1) % 33 - 1) % 4;
        if ($leap === -1) {
            $leap = 4;
        }

        return ['leap' => $leap, 'gy' => $gy, 'march' => $march];
    }

    private static function gregorian_to_jdn(int $gy, int $gm, int $gd): int
    {
        $a = self::floor_div(14 - $gm, 12);
        $y = $gy + 4800 - $a;
        $m = $gm + 12 * $a - 3;
        return $gd
            + self::floor_div(153 * $m + 2, 5)
            + 365 * $y
            + self::floor_div($y, 4)
            - self::floor_div($y, 100)
            + self::floor_div($y, 400)
            - 32045;
    }

    /**
     * @return array{gy:int,gm:int,gd:int}
     */
    private static function jdn_to_gregorian(int $jdn): array
    {
        $a = $jdn + 32044;
        $b = self::floor_div(4 * $a + 3, 146097);
        $c = $a - self::floor_div(146097 * $b, 4);
        $d = self::floor_div(4 * $c + 3, 1461);
        $e = $c - self::floor_div(1461 * $d, 4);
        $m = self::floor_div(5 * $e + 2, 153);
        $gd = $e - self::floor_div(153 * $m + 2, 5) + 1;
        $gm = $m + 3 - 12 * self::floor_div($m, 10);
        $gy = 100 * $b + $d - 4800 + self::floor_div($m, 10);
        return ['gy' => $gy, 'gm' => $gm, 'gd' => $gd];
    }

    private static function jalali_to_jdn(int $jy, int $jm, int $jd): int
    {
        $r = self::jalali_calendar($jy);
        return self::gregorian_to_jdn($r['gy'], 3, $r['march'])
            + ($jm - 1) * 31
            - self::floor_div($jm, 7) * ($jm - 7)
            + $jd - 1;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private static function jdn_to_jalali(int $jdn): array
    {
        $g = self::jdn_to_gregorian($jdn);
        $jy = $g['gy'] - 621;
        $r = self::jalali_calendar($jy);
        $first_day = self::gregorian_to_jdn($g['gy'], 3, $r['march']);
        $k = $jdn - $first_day;

        if ($k >= 0) {
            if ($k <= 185) {
                $jm = 1 + self::floor_div($k, 31);
                $jd = self::positive_mod($k, 31) + 1;
                return [$jy, $jm, $jd];
            }
            $k -= 186;
        } else {
            $jy--;
            $k += 179;
            if ($r['leap'] === 1) {
                $k++;
            }
        }

        $jm = 7 + self::floor_div($k, 30);
        $jd = self::positive_mod($k, 30) + 1;
        return [$jy, $jm, $jd];
    }

    /* ---------- Assets ---------- */

    public function enqueue_admin_assets($hook = ''): void
    {
        if (!is_admin()) {
            return;
        }

        $hook = is_string($hook) ? $hook : '';
        $is_post_screen = in_array($hook, ['post.php', 'post-new.php'], true);
        $is_acf_screen  = function_exists('acf') || (isset($_GET['page']) && is_string($_GET['page']) && strpos(sanitize_text_field(wp_unslash($_GET['page'])), 'acf') === 0);

        // Only load on post editor screens or when ACF is available.
        if (!$is_post_screen && !$is_acf_screen) {
            return;
        }

        $ver = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0';
        $css = '
        .bk-jdp{position:relative;display:inline-block;min-width:180px;max-width:100%}
        .bk-jdp input.bk-jdp-input{width:100%;min-height:36px;padding:7px 11px;border:1px solid #c3c4c7;border-radius:8px;font-size:13px;direction:rtl;background:#fff;color:#1d2327;box-sizing:border-box}
        .bk-jdp input.bk-jdp-input:focus{border-color:#2271b1;box-shadow:0 0 0 1px #2271b1;outline:2px solid transparent}
        .bk-jdp-panel{display:none;position:fixed;z-index:1000000;width:292px;max-width:calc(100vw - 16px);max-height:min(440px,calc(100vh - 16px));overflow:auto;box-sizing:border-box;padding:12px;background:#fff;color:#1d2327;border:1px solid #dcdcde;border-radius:12px;box-shadow:0 10px 35px rgba(0,0,0,.18);direction:rtl;font-family:inherit}
        .bk-jdp-panel.is-open{display:block}
        .bk-jdp-head{display:grid;grid-template-columns:36px 1fr 36px;align-items:center;gap:8px;margin-bottom:10px}
        .bk-jdp-head button,.bk-jdp-foot button{min-height:32px;border:1px solid #dcdcde;background:#f6f7f7;color:#1d2327;border-radius:7px;cursor:pointer;font:inherit}
        .bk-jdp-head button{font-size:18px}
        .bk-jdp-title{text-align:center;font-size:13px;font-weight:700}
        .bk-jdp-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:3px;text-align:center}
        .bk-jdp-grid .dow{padding:6px 0;color:#646970;font-size:11px;font-weight:700}
        .bk-jdp-grid button.day{min-width:0;height:34px;padding:0;border:0;border-radius:8px;background:transparent;color:#1d2327;cursor:pointer;font:inherit;font-size:12px}
        .bk-jdp-grid button.day:hover{background:#f0f6fc;color:#135e96}
        .bk-jdp-grid button.day:focus-visible{outline:2px solid #2271b1;outline-offset:1px}
        .bk-jdp-grid button.day.is-today{box-shadow:inset 0 0 0 1px #2271b1}
        .bk-jdp-grid button.day.is-sel{background:#2271b1;color:#fff}
        .bk-jdp-grid button.day:disabled{cursor:default;opacity:.25}
        .bk-jdp-foot{display:flex;gap:7px;margin-top:10px}
        .bk-jdp-foot button{flex:1;padding:6px 8px;font-size:11px;font-weight:600}
        .bk-jdp-foot button.primary{background:#2271b1;border-color:#2271b1;color:#fff}
        .bk-jdp-classic{margin:12px 0;padding:12px;border:1px solid #dcdcde;border-radius:9px;background:#f6f7f7}
        .bk-jdp-classic label{display:block;margin-bottom:7px;font-weight:600}
        .bk-jdp-classic .bk-jdp-time{display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-top:8px}
        .bk-jdp-classic .bk-jdp-time input{width:64px}
        .bk-jalali-doc-panel .bk-jdp{width:100%}
        .bk-acf-jalali-wrap{margin-top:6px}
        .bk-acf-jalali-view{max-width:220px}
        ';
        wp_register_style('bankai-jalali-datepicker', false, [], $ver);
        wp_enqueue_style('bankai-jalali-datepicker');
        wp_add_inline_style('bankai-jalali-datepicker', $css);

        $deps = $is_post_screen ? ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'] : [];
        wp_register_script('bankai-jalali-datepicker', false, $deps, $ver, true);
        wp_enqueue_script('bankai-jalali-datepicker');
        wp_add_inline_script(
            'bankai-jalali-datepicker',
            'window.BankaiJalaliNow = ' . wp_json_encode(current_time('Y-m-d H:i:s')) . ';',
            'before'
        );
        wp_add_inline_script('bankai-jalali-datepicker', $this->datepicker_js(), 'after');

        if ($is_post_screen) {
            wp_add_inline_script('bankai-jalali-datepicker', $this->post_editor_datepicker_js(), 'after');
        }
        if ($is_acf_screen) {
            wp_add_inline_script('bankai-jalali-datepicker', $this->acf_datepicker_bridge_js(), 'after');
        }
    }

    private function acf_datepicker_bridge_js(): string
    {
        return <<<'JS'
(function (w, d) {
  var api = w.BankaiJalaliDatepicker;
  if (!api) return;

  function digitsToLatin(value) {
    return String(value || '').replace(/[۰-۹٠-٩]/g, function (ch) {
      var code = ch.charCodeAt(0);
      return String(code >= 0x06f0 && code <= 0x06f9 ? code - 0x06f0 : code - 0x0660);
    });
  }
  function pad(n) { return String(n).padStart(2, '0'); }
  function parseAcfValue(value) {
    value = digitsToLatin(value).trim();
    var m;
    if (/^\d{8}$/.test(value)) {
      return { y: +value.slice(0,4), m: +value.slice(4,6), d: +value.slice(6,8) };
    }
    m = value.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) return { y: +m[1], m: +m[2], d: +m[3] };
    return null;
  }
  function enhance(wrap) {
    if (!wrap || wrap.getAttribute('data-bk-enhanced') === '1') return;
    var row = wrap.closest('.acf-field') || wrap.parentElement;
    var input = row && row.querySelector('input.acf-date-picker, input.acf-date-time-picker, input.hasDatepicker');
    if (!input) return;

    wrap.setAttribute('data-bk-enhanced', '1');
    var view = d.createElement('input');
    view.type = 'text';
    view.className = 'bk-jdp-input bk-acf-jalali-view';
    view.readOnly = true;
    view.placeholder = 'انتخاب تاریخ شمسی';
    view.setAttribute('aria-label', 'تاریخ شمسی');
    wrap.appendChild(view);

    function sync() {
      var g = parseAcfValue(input.value);
      if (!g) { view.value = ''; return; }
      var j = api.g2j(g.y, g.m, g.d);
      view.value = j[0] + '/' + pad(j[1]) + '/' + pad(j[2]);
    }
    sync();

    view.addEventListener('focus', function () {
      api.openForInput(view, function (jy, jm, jd) {
        var g = api.j2g(jy, jm, jd);
        // ACF date_picker stores Ymd; preserve the field's storage contract.
        input.value = String(g[0]) + pad(g[1]) + pad(g[2]);
        if (w.jQuery) w.jQuery(input).trigger('change');
        else input.dispatchEvent(new Event('change', { bubbles: true }));
        view.value = jy + '/' + pad(jm) + '/' + pad(jd);
      });
    });
    input.addEventListener('change', sync);
    if (w.acf && w.acf.addAction) {
      w.acf.addAction('change', function (field) {
        if (field && field.$el && field.$el[0] === row) sync();
      });
    }
  }
  function scan(root) {
    (root || d).querySelectorAll('[data-bk-acf-date="1"]').forEach(enhance);
  }
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', function () { scan(); });
  else scan();
  if (w.acf && w.acf.addAction) {
    ['ready', 'append'].forEach(function (hook) {
      w.acf.addAction(hook, function ($el) { scan($el && $el[0] ? $el[0] : d); });
    });
  }
})(window, document);
JS;
    }


    public function admin_footer_marker(): void
    {
        if (!is_admin()) {
            return;
        }
        echo '<script>document.documentElement.setAttribute("data-bankai-jalali","1");</script>';
    }


    /**
     * Bridge: Jalali datepicker for WP classic timestamp + Gutenberg schedule.
     */
    private function post_editor_datepicker_js(): string
    {
        return <<<'JS'
(function (w, d) {
  var api = w.BankaiJalaliDatepicker;
  if (!api) return;
  var g2j = api.g2j, j2g = api.j2g;

  function latin(value) {
    return String(value == null ? '' : value).replace(/[۰-۹٠-٩]/g, function (ch) {
      var n = ch.charCodeAt(0);
      return String(n >= 0x06f0 && n <= 0x06f9 ? n - 0x06f0 : n - 0x0660);
    });
  }
  function pad(n) { return String(n).padStart(2, '0'); }
  function parseJalali(value) {
    var m = latin(value).trim().match(/^(\d{4})\s*[\/.-]\s*(\d{1,2})\s*[\/.-]\s*(\d{1,2})$/);
    if (!m) return null;
    var y = +m[1], mo = +m[2], day = +m[3];
    if (mo < 1 || mo > 12 || day < 1 || day > api.daysInMonth(y, mo)) return null;
    return [y, mo, day];
  }
  function dateParts(value) {
    // WordPress editor date attributes are local site-time strings without a Z suffix.
    var m = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}):(\d{2})(?::(\d{2}))?)?/);
    if (!m) return null;
    return { y:+m[1], mo:+m[2], d:+m[3], h:+(m[4] || 0), mi:+(m[5] || 0), s:+(m[6] || 0) };
  }
  function isoLocal(g, h, mi, sec) {
    return g[0] + '-' + pad(g[1]) + '-' + pad(g[2]) + 'T' + pad(h) + ':' + pad(mi) + ':' + pad(sec || 0);
  }

  function enhanceClassic() {
    var box = d.getElementById('timestampdiv');
    if (!box || box.getAttribute('data-bk-jalali') === '1') return;
    var aa = d.getElementById('aa'), mm = d.getElementById('mm'), jj = d.getElementById('jj');
    var hh = d.getElementById('hh'), mn = d.getElementById('mn');
    if (!aa || !mm || !jj) return;
    box.setAttribute('data-bk-jalali', '1');

    var wrap = d.createElement('div');
    wrap.className = 'bk-jdp-classic';
    var label = d.createElement('label');
    label.textContent = 'زمان‌بندی انتشار به تاریخ جلالی';
    var dateInput = d.createElement('input');
    dateInput.type = 'text';
    dateInput.id = 'bk-classic-jdate';
    dateInput.className = 'bankai-jalali-date';
    dateInput.autocomplete = 'off';
    dateInput.placeholder = 'مثلاً ۱۴۰۵/۰۸/۱۰';
    dateInput.style.maxWidth = '230px';
    var time = d.createElement('div');
    time.className = 'bk-jdp-time';
    var hour = d.createElement('input');
    hour.type = 'number'; hour.min = '0'; hour.max = '23'; hour.id = 'bk-classic-hour';
    hour.setAttribute('aria-label', 'ساعت');
    var sep = d.createElement('span'); sep.textContent = ':';
    var minute = d.createElement('input');
    minute.type = 'number'; minute.min = '0'; minute.max = '59'; minute.id = 'bk-classic-minute';
    minute.setAttribute('aria-label', 'دقیقه');
    var apply = d.createElement('button');
    apply.type = 'button'; apply.className = 'button button-primary'; apply.textContent = 'اعمال تاریخ انتشار';
    var status = d.createElement('span');
    status.setAttribute('aria-live', 'polite');
    status.style.cssText = 'font-size:12px;margin-inline-start:8px';
    time.append(hour, sep, minute, apply, status);
    wrap.append(label, dateInput, time);
    box.insertBefore(wrap, box.firstChild);

    function readClassic() {
      var y = parseInt(aa.value, 10), mo = parseInt(mm.value, 10), day = parseInt(jj.value, 10);
      if (!y || !mo || !day) return;
      var j = g2j(y, mo, day);
      dateInput.value = j[0] + '/' + pad(j[1]) + '/' + pad(j[2]);
      hour.value = hh ? hh.value : '0';
      minute.value = mn ? mn.value : '0';
    }
    function applyClassic() {
      var j = parseJalali(dateInput.value);
      if (!j) {
        status.textContent = 'تاریخ معتبر نیست';
        status.style.color = '#b32d2e';
        return;
      }
      var g = j2g(j[0], j[1], j[2]);
      var h = Math.max(0, Math.min(23, parseInt(hour.value, 10) || 0));
      var m = Math.max(0, Math.min(59, parseInt(minute.value, 10) || 0));
      aa.value = String(g[0]); mm.value = pad(g[1]); jj.value = pad(g[2]);
      if (hh) hh.value = pad(h);
      if (mn) mn.value = pad(m);
      if (typeof w.updateTimestamp === 'function') w.updateTimestamp();
      status.textContent = 'تاریخ انتشار اعمال شد';
      status.style.color = '#008a20';
    }
    readClassic();
    apply.addEventListener('click', applyClassic);
    dateInput.addEventListener('change', applyClassic);
    [aa, mm, jj, hh, mn].filter(Boolean).forEach(function (el) {
      el.addEventListener('change', readClassic);
    });
    api.init(wrap);
  }

  function enhanceBlockEditor() {
    if (!w.wp || !wp.plugins || !wp.element || !wp.data) return;
    var Panel = (wp.editPost && wp.editPost.PluginDocumentSettingPanel) ||
                (wp.editor && wp.editor.PluginDocumentSettingPanel);
    if (!Panel || !wp.plugins.registerPlugin) return;
    if (wp.plugins.getPlugin && wp.plugins.getPlugin('bankai-jalali-schedule')) return;

    var el = wp.element.createElement;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var useSelect = wp.data.useSelect;

    function JalaliSchedulePanel() {
      var postDate = useSelect(function (select) {
        return select('core/editor').getEditedPostAttribute('date');
      }, []);
      var parts = dateParts(postDate) || dateParts(String(w.BankaiJalaliNow || '').replace(' ', 'T')) || (function () {
        var now = new Date();
        return { y:now.getFullYear(), mo:now.getMonth()+1, d:now.getDate(), h:now.getHours(), mi:now.getMinutes(), s:0 };
      })();
      var jalali = g2j(parts.y, parts.mo, parts.d);
      var state = useState(jalali[0] + '/' + pad(jalali[1]) + '/' + pad(jalali[2]));
      var dateValue = state[0], setDateValue = state[1];
      var hs = useState(parts.h), hour = hs[0], setHour = hs[1];
      var ms = useState(parts.mi), minute = ms[0], setMinute = ms[1];
      var msgState = useState(''), message = msgState[0], setMessage = msgState[1];
      var lastDate = wp.element.useRef ? wp.element.useRef(postDate) : null;

      useEffect(function () {
        var pickerRoot = d.getElementById('bk-gutenberg-jdate-wrap');
        if (pickerRoot) api.init(pickerRoot);
      }, []);

      useEffect(function () {
        if (lastDate && lastDate.current === postDate) return;
        if (lastDate) lastDate.current = postDate;
        var p = dateParts(postDate);
        if (!p) return;
        var j = g2j(p.y, p.mo, p.d);
        setDateValue(j[0] + '/' + pad(j[1]) + '/' + pad(j[2]));
        setHour(p.h); setMinute(p.mi);
      }, [postDate]);

      function apply() {
        var j = parseJalali(dateValue);
        if (!j) { setMessage('تاریخ واردشده معتبر نیست.'); return; }
        var g = j2g(j[0], j[1], j[2]);
        var h = Math.max(0, Math.min(23, parseInt(hour, 10) || 0));
        var mi = Math.max(0, Math.min(59, parseInt(minute, 10) || 0));
        wp.data.dispatch('core/editor').editPost({ date: isoLocal(g, h, mi, 0) });
        setMessage('تاریخ جلالی به زمان‌بندی نوشته اعمال شد.');
      }

      return el(Panel, {
        name: 'bankai-jalali-schedule',
        title: 'زمان‌بندی جلالی',
        className: 'bk-jalali-doc-panel'
      },
        el('div', { className: 'bk-jalali-editor-field' },
          el('label', { htmlFor: 'bk-gutenberg-jdate', style: { display:'block', marginBottom:'6px', fontWeight:600 } }, 'تاریخ انتشار (شمسی)'),
          el('input', {
            id: 'bk-gutenberg-jdate',
            type: 'text',
            className: 'components-text-control__input bankai-jalali-date',
            value: dateValue,
            placeholder: '۱۴۰۵/۰۸/۱۰',
            autoComplete: 'off',
            onChange: function (e) { setDateValue(e.target.value); setMessage(''); }
          }),
          el('div', { style: { display:'flex', gap:'7px', alignItems:'center', marginTop:'8px' } },
            el('label', { htmlFor:'bk-jalali-hour' }, 'ساعت'),
            el('input', { id:'bk-jalali-hour', type:'number', min:0, max:23, value:hour, style:{width:'62px'}, onChange:function(e){setHour(e.target.value);} }),
            el('span', null, ':'),
            el('input', { type:'number', min:0, max:59, value:minute, 'aria-label':'دقیقه', style:{width:'62px'}, onChange:function(e){setMinute(e.target.value);} })
          ),
          el('button', { type:'button', className:'components-button is-primary', style:{marginTop:'10px',width:'100%',justifyContent:'center'}, onClick:apply }, 'اعمال زمان‌بندی'),
          message ? el('p', { role:'status', style:{fontSize:'12px', marginTop:'8px'} }, message) : null
        )
      );
    }
    wp.plugins.registerPlugin('bankai-jalali-schedule', { render:JalaliSchedulePanel, icon:'calendar-alt' });
  }

  function boot() {
    enhanceClassic();
    try { enhanceBlockEditor(); } catch (e) { /* Do not break the editor if Gutenberg APIs differ. */ }
  }
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', boot, { once:true });
  else boot();
  d.addEventListener('click', function (event) {
    if (event.target && event.target.closest && event.target.closest('#edit-timestamp')) {
      w.setTimeout(enhanceClassic, 80);
    }
  });
})(window, document);
JS;
    }

    private function datepicker_js(): string
    {
        $months_json = wp_json_encode(array_values(self::$months), JSON_UNESCAPED_UNICODE);
        $today = current_time('Y-n-j');
        return <<<JS
(function (w, d) {
  'use strict';
  var MONTHS = {$months_json};
  var TODAY = '{$today}'.split('-').map(Number);

  function latinDigits(value) {
    return String(value == null ? '' : value).replace(/[۰-۹٠-٩]/g, function (ch) {
      var n = ch.charCodeAt(0);
      return String(n >= 0x06f0 && n <= 0x06f9 ? n - 0x06f0 : n - 0x0660);
    });
  }
  function faDigits(value) {
    return String(value).replace(/\d/g, function (n) { return '۰۱۲۳۴۵۶۷۸۹'[Number(n)]; });
  }
  function pad(n) { return n < 10 ? '0' + n : String(n); }

  function floorDiv(a, b) { return Math.floor(a / b); }
  function positiveMod(a, b) { return a - floorDiv(a, b) * b; }

  var JALALI_BREAKS = [-61,9,38,199,426,686,756,818,1111,1181,1210,1635,2060,2097,2192,2262,2324,2394,2456,3178];
  function jalaliCal(jy) {
    var count = JALALI_BREAKS.length;
    if (jy < JALALI_BREAKS[0] || jy >= JALALI_BREAKS[count - 1]) throw new Error('Jalali year out of range');
    var gy = jy + 621, leapJ = -14, jp = JALALI_BREAKS[0], jump = 0;
    for (var i = 1; i < count; i++) {
      var jm = JALALI_BREAKS[i];
      jump = jm - jp;
      if (jy < jm) break;
      leapJ += floorDiv(jump, 33) * 8 + floorDiv(positiveMod(jump, 33), 4);
      jp = jm;
    }
    var n = jy - jp;
    leapJ += floorDiv(n, 33) * 8 + floorDiv(positiveMod(n, 33) + 3, 4);
    if (positiveMod(jump, 33) === 4 && jump - n === 4) leapJ++;
    var leapG = floorDiv(gy, 4) - floorDiv((floorDiv(gy, 100) + 1) * 3, 4) - 150;
    var march = 20 + leapJ - leapG;
    if (jump - n < 6) n = n - jump + floorDiv(jump + 4, 33) * 33;
    var leap = ((n + 1) % 33 - 1) % 4;
    if (leap === -1) leap = 4;
    return { leap:leap, gy:gy, march:march };
  }
  function gregorianToJdn(gy, gm, gd) {
    var a = floorDiv(14 - gm, 12);
    var y = gy + 4800 - a;
    var m = gm + 12 * a - 3;
    return gd + floorDiv(153 * m + 2, 5) + 365 * y + floorDiv(y, 4)
      - floorDiv(y, 100) + floorDiv(y, 400) - 32045;
  }
  function jdnToGregorian(jdn) {
    var a = jdn + 32044;
    var b = floorDiv(4 * a + 3, 146097);
    var c = a - floorDiv(146097 * b, 4);
    var dd = floorDiv(4 * c + 3, 1461);
    var e = c - floorDiv(1461 * dd, 4);
    var m = floorDiv(5 * e + 2, 153);
    var gd = e - floorDiv(153 * m + 2, 5) + 1;
    var gm = m + 3 - 12 * floorDiv(m, 10);
    var gy = 100 * b + dd - 4800 + floorDiv(m, 10);
    return [gy, gm, gd];
  }
  function jalaliToJdn(jy, jm, jd) {
    var r = jalaliCal(jy);
    return gregorianToJdn(r.gy, 3, r.march) + (jm - 1) * 31
      - floorDiv(jm, 7) * (jm - 7) + jd - 1;
  }
  function jdnToJalali(jdn) {
    var g = jdnToGregorian(jdn);
    var jy = g[0] - 621;
    var r = jalaliCal(jy);
    var first = gregorianToJdn(g[0], 3, r.march);
    var k = jdn - first;
    if (k >= 0) {
      if (k <= 185) return [jy, 1 + floorDiv(k, 31), positiveMod(k, 31) + 1];
      k -= 186;
    } else {
      jy--;
      k += 179;
      if (r.leap === 1) k++;
    }
    return [jy, 7 + floorDiv(k, 30), positiveMod(k, 30) + 1];
  }
  function g2j(gy, gm, gd) {
    gy = Number(gy); gm = Number(gm); gd = Number(gd);
    var date = new Date(Date.UTC(gy, gm - 1, gd));
    if (gm < 1 || gm > 12 || date.getUTCFullYear() !== gy || date.getUTCMonth() !== gm - 1 || date.getUTCDate() !== gd) {
      throw new Error('Invalid Gregorian date');
    }
    return jdnToJalali(gregorianToJdn(gy, gm, gd));
  }
  function j2g(jy, jm, jd) {
    jy = Number(jy); jm = Number(jm); jd = Number(jd);
    if (jm < 1 || jm > 12 || jd < 1) throw new Error('Invalid Jalali date');
    var first = jalaliToJdn(jy, jm, 1);
    var next = jm === 12 ? jalaliToJdn(jy + 1, 1, 1) : jalaliToJdn(jy, jm + 1, 1);
    if (jd > next - first) throw new Error('Invalid Jalali day');
    return jdnToGregorian(first + jd - 1);
  }
  function daysInMonth(jy, jm) {
    if (jm < 1 || jm > 12) return 0;
    var first = jalaliToJdn(jy, jm, 1);
    var next = jm === 12 ? jalaliToJdn(jy + 1, 1, 1) : jalaliToJdn(jy, jm + 1, 1);
    return next - first;
  }
  function validJalali(y, m, day) {
    return Number.isInteger(y) && Number.isInteger(m) && Number.isInteger(day)
      && m >= 1 && m <= 12 && day >= 1 && day <= daysInMonth(y, m);
  }
  function parseJalali(value) {
    var m = latinDigits(value).trim().match(/^(\d{4})\s*[\/.-]\s*(\d{1,2})\s*[\/.-]\s*(\d{1,2})$/);
    if (!m) return null;
    var out = [+m[1], +m[2], +m[3]];
    return validJalali(out[0], out[1], out[2]) ? out : null;
  }
  function makeDateInputValue(y, m, day) {
    return faDigits(y + '/' + pad(m) + '/' + pad(day));
  }

  var ACTIVE = null;
  var sharedListeners = false;
  var placeFrame = 0;
  var DOW = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
  var SELECTOR = 'input.bankai-jalali-date,input[data-bankai-jalali],input.bk-jalali-date';

  function requestPlace() {
    if (placeFrame) return;
    placeFrame = w.requestAnimationFrame(function () {
      placeFrame = 0;
      if (ACTIVE) ACTIVE.place();
    });
  }
  function bindSharedListeners() {
    if (sharedListeners) return;
    sharedListeners = true;
    w.addEventListener('resize', requestPlace, { passive:true });
    w.addEventListener('scroll', requestPlace, { passive:true, capture:true });
    d.addEventListener('pointerdown', function (event) {
      if (!ACTIVE) return;
      if (ACTIVE.wrap.contains(event.target) || ACTIVE.panel.contains(event.target)) return;
      ACTIVE.close();
    }, true);
    d.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && ACTIVE) {
        ACTIVE.close();
        ACTIVE.input.focus();
      }
    });
  }

  function JalaliPicker(input) {
    if (!input || input._bankaiJdp) return input && input._bankaiJdpApi;
    input._bankaiJdp = true;
    bindSharedListeners();

    var wrap = d.createElement('div');
    wrap.className = 'bk-jdp';
    input.classList.add('bk-jdp-input');
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('inputmode', 'numeric');
    if (input.parentNode) {
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);
    }

    var panel = d.createElement('div');
    panel.className = 'bk-jdp-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'انتخاب تاریخ جلالی');
    panel.setAttribute('aria-modal', 'false');
    d.body.appendChild(panel);

    var today = TODAY.length === 3 && TODAY.every(Number.isFinite) ? TODAY : g2j((new Date()).getFullYear(), (new Date()).getMonth()+1, (new Date()).getDate());
    var viewY = today[0], viewM = today[1], selected = null, renderedKey = '';

    function readInput() {
      var parsed = parseJalali(input.value);
      if (parsed) {
        selected = parsed;
        viewY = parsed[0];
        viewM = parsed[1];
      }
    }
    function render() {
      var key = viewY + '-' + viewM + '-' + (selected ? selected.join('-') : '') + '-' + today.join('-');
      if (key === renderedKey && panel.firstChild) return;
      renderedKey = key;
      var count = daysInMonth(viewY, viewM);
      var firstG = j2g(viewY, viewM, 1);
      // Saturday is column zero; JavaScript UTC weekday is Sunday=0.
      var start = (new Date(Date.UTC(firstG[0], firstG[1]-1, firstG[2])).getUTCDay() + 1) % 7;
      var html = '<div class="bk-jdp-head">'
        + '<button type="button" data-nav="-1" aria-label="ماه قبل">‹</button>'
        + '<div class="bk-jdp-title">' + MONTHS[viewM-1] + ' ' + faDigits(viewY) + '</div>'
        + '<button type="button" data-nav="1" aria-label="ماه بعد">›</button></div><div class="bk-jdp-grid">';
      for (var i = 0; i < 7; i++) html += '<div class="dow">' + DOW[i] + '</div>';
      for (var blank = 0; blank < start; blank++) html += '<button type="button" class="day" disabled tabindex="-1" aria-hidden="true"></button>';
      for (var day = 1; day <= count; day++) {
        var cls = 'day';
        var isToday = today[0] === viewY && today[1] === viewM && today[2] === day;
        var isSelected = selected && selected[0] === viewY && selected[1] === viewM && selected[2] === day;
        if (isToday) cls += ' is-today';
        if (isSelected) cls += ' is-sel';
        html += '<button type="button" class="' + cls + '" data-day="' + day + '"'
          + (isSelected ? ' aria-pressed="true"' : ' aria-pressed="false"')
          + ' aria-label="' + faDigits(day) + ' ' + MONTHS[viewM-1] + ' ' + faDigits(viewY) + '">'
          + faDigits(day) + '</button>';
      }
      html += '</div><div class="bk-jdp-foot">'
        + '<button type="button" data-action="today">امروز</button>'
        + '<button type="button" data-action="clear">پاک‌کردن</button>'
        + '<button type="button" class="primary" data-action="close">بستن</button></div>';
      panel.innerHTML = html;
    }
    function place() {
      if (!input.isConnected) { close(); return; }
      var r = input.getBoundingClientRect(), vw = w.innerWidth, vh = w.innerHeight;
      var width = Math.min(292, vw - 16);
      panel.style.display = 'block';
      panel.style.width = width + 'px';
      var height = panel.offsetHeight || 340;
      var left = Math.max(8, Math.min(r.right - width, vw - width - 8));
      var top = r.bottom + 6;
      if (top + height > vh - 8) top = Math.max(8, r.top - height - 6);
      if (top + height > vh - 8) top = Math.max(8, vh - height - 8);
      panel.style.left = left + 'px';
      panel.style.top = top + 'px';
    }
    function open() {
      if (ACTIVE && ACTIVE !== api) ACTIVE.close();
      readInput();
      renderedKey = '';
      render();
      panel.classList.add('is-open');
      panel.style.display = 'block';
      ACTIVE = api;
      place();
    }
    function close() {
      panel.classList.remove('is-open');
      panel.style.display = 'none';
      if (ACTIVE === api) ACTIVE = null;
    }
    function emit() {
      input.dispatchEvent(new Event('input', { bubbles:true }));
      input.dispatchEvent(new Event('change', { bubbles:true }));
    }
    function setValue(y, m, day) {
      if (!validJalali(y, m, day)) return;
      selected = [y, m, day];
      viewY = y; viewM = m;
      input.value = makeDateInputValue(y, m, day);
      var g = j2g(y, m, day);
      input.setAttribute('data-gregorian', g[0] + '-' + pad(g[1]) + '-' + pad(g[2]));
      emit();
      close();
    }
    panel.addEventListener('click', function (event) {
      var target = event.target.closest('button');
      if (!target || !panel.contains(target)) return;
      if (target.hasAttribute('data-nav')) {
        viewM += Number(target.getAttribute('data-nav'));
        if (viewM > 12) { viewM = 1; viewY++; }
        if (viewM < 1) { viewM = 12; viewY--; }
        renderedKey = ''; render(); place();
        return;
      }
      var action = target.getAttribute('data-action');
      if (action === 'today') { setValue(today[0], today[1], today[2]); return; }
      if (action === 'clear') {
        selected = null; input.value = ''; input.removeAttribute('data-gregorian'); emit(); close(); return;
      }
      if (action === 'close') { close(); return; }
      var day = Number(target.getAttribute('data-day'));
      if (day) setValue(viewY, viewM, day);
    });

    var api = {
      input:input, wrap:wrap, panel:panel,
      open:open, close:close, place:place,
      setValue:setValue
    };
    input._bankaiJdpApi = api;
    input.addEventListener('focus', open);
    input.addEventListener('click', open);
    return api;
  }

  function init(root) {
    var scope = root || d;
    if (!scope.querySelectorAll) return;
    var nodes = scope.querySelectorAll(SELECTOR);
    Array.prototype.forEach.call(nodes, JalaliPicker);
    if (scope.matches && scope.matches(SELECTOR)) JalaliPicker(scope);
  }
  function openForInput(input, onPick) {
    if (!input) return;
    var picker = input._bankaiJdpApi || JalaliPicker(input);
    if (!picker) return;
    if (typeof onPick === 'function' && !input._bankaiJdpOnPick) {
      input._bankaiJdpOnPick = true;
      input.addEventListener('change', function () {
        var j = parseJalali(input.value);
        if (j) onPick(j[0], j[1], j[2]);
      });
    }
    picker.open();
  }

  w.BankaiJalaliDatepicker = {
    init:init,
    g2j:g2j,
    j2g:j2g,
    daysInMonth:daysInMonth,
    isValid:validJalali,
    parse:parseJalali,
    openForInput:openForInput,
    closeActive:function () { if (ACTIVE) ACTIVE.close(); }
  };
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', function () { init(); }, { once:true });
  else init();
})(window, document);
JS;
    }
}
