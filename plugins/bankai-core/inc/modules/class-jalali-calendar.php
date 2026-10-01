<?php
/**
 * Jalali (Shamsi) calendar — Bankai
 *
 * Patterns adapted from WP-ParsiDate FixDates / ShamsiDate.
 *
 * @package Bankai
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
        add_filter('get_the_modified_time', [$this, 'filter_get_the_time'], 10, 3);
        add_filter('get_comment_time', [$this, 'filter_comment_time'], 10, 2);
        add_filter('get_comment_date', [$this, 'filter_comment_date'], 10, 3);

        add_filter('post_date_column_time', [$this, 'filter_post_date_column'], 10, 4);
        add_filter('media_view_settings', [$this, 'filter_media_view_settings'], 10, 2);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_footer', [$this, 'admin_footer_marker']);
    }

    /**
     * @return array<string,mixed>
     */
    private function settings(): array
    {
        $s = function_exists('bankai_get_option') ? bankai_get_option('jalali_settings', []) : [];
        if (!is_array($s)) {
            $s = [];
        }
        return array_merge([
            'persian_digits' => false,
            'dual_date'      => false,
            'datepicker'     => true,
            'admin_column'   => true,
        ], $s);
    }

    /* ---------- Filters ---------- */

    public function filter_wp_date($date, $format, $timestamp, $timezone)
    {
        if ($this->converting || $this->is_machine_format((string) $format)) {
            return $date;
        }
        $this->converting = true;
        $ts  = is_numeric($timestamp) ? (int) $timestamp : time();
        $tz  = $timezone instanceof DateTimeZone ? $timezone : (function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC'));
        $out = $this->format_jalali($ts, (string) $format, $tz);
        $this->converting = false;
        return $out;
    }

    public function filter_date_i18n($date, $format, $timestamp, $gmt)
    {
        if ($this->converting || $this->is_machine_format((string) $format)) {
            return $date;
        }
        $this->converting = true;
        $ts  = $timestamp ? (int) $timestamp : time();
        $out = $this->format_jalali($ts, (string) $format);
        $this->converting = false;
        return $out;
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
            return date($format, strtotime($post->post_date));
        }
        return $this->format_jalali(strtotime($post->post_date), $format);
    }

    public function filter_get_the_modified_date($time, $format = '', $post = null)
    {
        $post = get_post($post);
        if (!$post) {
            return $time;
        }
        $format = $format ?: (string) get_option('date_format');
        if ($this->is_machine_format($format)) {
            return date($format, strtotime($post->post_modified));
        }
        return $this->format_jalali(strtotime($post->post_modified), $format);
    }

    public function filter_comment_date($comment_date, $format = '', $comment = null)
    {
        if ($comment === null) {
            return $comment_date;
        }
        $format = $format ?: (string) get_option('date_format');
        if ($this->is_machine_format($format)) {
            return date($format, strtotime($comment->comment_date));
        }
        return $this->format_jalali(strtotime($comment->comment_date), $format);
    }

    public function filter_comment_time($time, $format = '')
    {
        global $comment;
        if (empty($comment)) {
            return $time;
        }
        $format = $format ?: (string) get_option('time_format');
        return $this->format_jalali(strtotime($comment->comment_date), $format);
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
            return date($format, strtotime($post->$field));
        }
        return $this->format_jalali(strtotime($post->$field), $format);
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
        return !(bool) preg_match('/[dDjlNSwzWFmMntLoYy]/', (string) $clean);
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
        $weekday_short = self::$weekdays_short[$dt->format('D')] ?? mb_substr($weekday_fa, 0, 1);

        $map = [
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'j' => (string) $jd,
            'D' => $weekday_short,
            'l' => $weekday_fa,
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'n' => (string) $jm,
            'F' => $month_name,
            'M' => mb_substr($month_name, 0, 3),
            'Y' => (string) $jy,
            'y' => substr((string) $jy, -2),
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

        if (!empty($settings['dual_date']) && preg_match('/[dDjlNSwzWFmMntLoYy]/', $format)) {
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
    public static function gregorian_to_jalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2   = ($gm > 2) ? ($gy + 1) : $gy;
        $days  = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }
        return [$jy, $jm, $jd];
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    public static function jalali_to_gregorian(int $jy, int $jm, int $jd): array
    {
        $jy  += 1595;
        $days = -355668 + (365 * $jy) + intdiv($jy, 33) * 8 + intdiv(($jy % 33) + 3, 4) + $jd
            + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30 + 186));
        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;
        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;
            if ($days >= 365) {
                $days++;
            }
        }
        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;
        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }
        $gd    = $days + 1;
        $sal_a = [0, 31, (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        for ($gm = 1; $gm <= 12 && $gd > $sal_a[$gm]; $gm++) {
            $gd -= $sal_a[$gm];
        }
        return [$gy, $gm, $gd];
    }

    /* ---------- Assets ---------- */

    public function enqueue_admin_assets($hook = ''): void
    {
        if (!is_admin()) {
            return;
        }

        $ver = defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.60';
        $css = '.bk-jdp{position:relative;display:inline-block;min-width:180px}'
            . '.bk-jdp input.bk-jdp-input{width:100%;padding:8px 12px;border:1px solid #d0d7de;border-radius:10px;font-size:13px;direction:rtl}'
            . '.bk-jdp-panel{display:none;position:fixed;z-index:1000000;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 12px 40px rgba(15,23,42,.18);padding:12px;width:280px;max-width:calc(100vw - 16px);max-height:min(420px,calc(100vh - 24px));overflow:auto;box-sizing:border-box;contain:layout style paint;will-change:transform,opacity;transform:translateZ(0)}'
            . '.bk-jdp-panel.is-open{display:block}'
            . '.bk-jdp-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}'
            . '.bk-jdp-head button{border:1px solid #e2e8f0;background:#f8fafc;border-radius:8px;width:32px;height:32px;cursor:pointer;font-weight:700}'
            . '.bk-jdp-title{font-size:13px;font-weight:800}'
            . '.bk-jdp-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center}'
            . '.bk-jdp-grid .dow{font-size:10px;color:#94a3b8;font-weight:700;padding:4px 0}'
            . '.bk-jdp-grid button.day{border:none;background:transparent;border-radius:8px;height:32px;cursor:pointer;font-size:12px;font-weight:600}'
            . '.bk-jdp-grid button.day:hover{background:#eef6fc;color:#0078d4}'
            . '.bk-jdp-grid button.day.is-today{box-shadow:inset 0 0 0 1px #0078d4}'
            . '.bk-jdp-grid button.day.is-sel{background:#0078d4;color:#fff}'
            . '.bk-jdp-grid button.day.muted{opacity:.35}'
            . '.bk-jdp-foot{display:flex;justify-content:space-between;margin-top:10px;gap:6px}'
            . '.bk-jdp-foot button{flex:1;border:1px solid #e2e8f0;background:#f8fafc;border-radius:8px;padding:6px;font-size:11px;font-weight:700;cursor:pointer}';

        wp_register_style('bankai-jalali-datepicker', false, [], $ver);
        wp_enqueue_style('bankai-jalali-datepicker');
        wp_add_inline_style('bankai-jalali-datepicker', $css);

        $hook = is_string($hook) ? $hook : '';
        $is_post_screen = in_array($hook, ['post.php', 'post-new.php'], true);
        $deps = $is_post_screen ? ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'] : [];
        wp_register_script('bankai-jalali-datepicker', false, $deps, $ver, true);
        wp_enqueue_script('bankai-jalali-datepicker');
        wp_add_inline_script('bankai-jalali-datepicker', $this->datepicker_js(), 'after');
        if ($is_post_screen) {
            wp_add_inline_script('bankai-jalali-datepicker', $this->post_editor_datepicker_js(), 'after');
        }
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
  if (!w.BankaiJalaliDatepicker) return;
  var g2j = w.BankaiJalaliDatepicker.g2j;
  var j2g = w.BankaiJalaliDatepicker.j2g;
  function pad(n) { return n < 10 ? '0' + n : '' + n; }

  function enhanceClassic() {
    var box = d.getElementById('timestampdiv');
    if (!box || box.getAttribute('data-bk-jalali')) return;
    box.setAttribute('data-bk-jalali', '1');

    var aa = d.getElementById('aa');
    var mm = d.getElementById('mm');
    var jj = d.getElementById('jj');
    var hh = d.getElementById('hh');
    var mn = d.getElementById('mn');
    if (!aa || !mm || !jj) return;

    var wrap = d.createElement('div');
    wrap.className = 'bk-jdp-classic';
    wrap.style.cssText = 'margin:10px 0;padding:12px;border:1px solid #d0d7de;border-radius:10px;background:#f6f8fa';
    wrap.innerHTML = '<label style="display:block;font-weight:600;margin-bottom:6px">تاریخ شمسی</label>'
      + '<input type="text" class="bankai-jalali-date" id="bk-classic-jdate" autocomplete="off" style="width:100%;max-width:220px;margin-bottom:8px" />'
      + '<div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">'
      + '<input type="number" id="bk-classic-hh" min="0" max="23" style="width:64px" />'
      + '<span>:</span>'
      + '<input type="number" id="bk-classic-mn" min="0" max="59" style="width:64px" />'
      + '<button type="button" class="button" id="bk-classic-apply">اعمال روی انتشار</button>'
      + '</div>';
    box.insertBefore(wrap, box.firstChild);

    function fillFromClassic() {
      var y = parseInt(aa.value, 10) || (new Date()).getFullYear();
      var m = parseInt(mm.value, 10) || 1;
      var day = parseInt(jj.value, 10) || 1;
      var j = g2j(y, m, day);
      var inp = d.getElementById('bk-classic-jdate');
      if (inp) inp.value = j[0] + '/' + pad(j[1]) + '/' + pad(j[2]);
      var hEl = d.getElementById('bk-classic-hh');
      var mEl = d.getElementById('bk-classic-mn');
      if (hEl) hEl.value = hh ? hh.value : '0';
      if (mEl) mEl.value = mn ? mn.value : '0';
    }
    function applyToClassic() {
      var inp = d.getElementById('bk-classic-jdate');
      var v = (inp && inp.value || '').trim();
      var m = v.match(/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/);
      if (!m) return;
      var g = j2g(parseInt(m[1],10), parseInt(m[2],10), parseInt(m[3],10));
      aa.value = String(g[0]);
      mm.value = pad(g[1]);
      jj.value = pad(g[2]);
      var hEl = d.getElementById('bk-classic-hh');
      var mEl = d.getElementById('bk-classic-mn');
      if (hh && hEl) hh.value = pad(Math.min(23, Math.max(0, parseInt(hEl.value,10)||0)));
      if (mn && mEl) mn.value = pad(Math.min(59, Math.max(0, parseInt(mEl.value,10)||0)));
      try { if (typeof updateTimestamp === 'function') updateTimestamp(); } catch (e) {}
    }
    fillFromClassic();
    var applyBtn = d.getElementById('bk-classic-apply');
    if (applyBtn) applyBtn.addEventListener('click', applyToClassic);
    var jinp = d.getElementById('bk-classic-jdate');
    if (jinp) {
      jinp.addEventListener('change', applyToClassic);
      if (w.BankaiJalaliDatepicker.init) w.BankaiJalaliDatepicker.init(wrap);
    }
  }

  function enhanceBlock() {
    if (!w.wp || !wp.plugins || !wp.editPost || !wp.element || !wp.data) return;
    var el = wp.element.createElement;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
    var registerPlugin = wp.plugins.registerPlugin;
    if (wp.plugins.getPlugin && wp.plugins.getPlugin('bankai-jalali-schedule')) return;

    function Panel() {
      var postDate = wp.data.select('core/editor').getEditedPostAttribute('date');
      var dt = postDate ? new Date(postDate) : new Date();
      if (isNaN(dt.getTime())) dt = new Date();
      var j = g2j(dt.getFullYear(), dt.getMonth() + 1, dt.getDate());
      var _s = useState(j[0] + '/' + pad(j[1]) + '/' + pad(j[2]));
      var jstr = _s[0], setJstr = _s[1];
      var _h = useState(dt.getHours());
      var hour = _h[0], setHour = _h[1];
      var _m = useState(dt.getMinutes());
      var minute = _m[0], setMinute = _m[1];
      var _msg = useState('');
      var msg = _msg[0], setMsg = _msg[1];

      useEffect(function () {
        setTimeout(function () {
          var root = d.getElementById('bk-gutenberg-jdate-wrap');
          if (root && w.BankaiJalaliDatepicker) w.BankaiJalaliDatepicker.init(root);
        }, 80);
      }, []);

      function apply() {
        var m = String(jstr || '').trim().match(/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/);
        if (!m) { setMsg('تاریخ نامعتبر'); return; }
        var g = j2g(parseInt(m[1],10), parseInt(m[2],10), parseInt(m[3],10));
        var hh = Math.min(23, Math.max(0, parseInt(hour,10)||0));
        var mmn = Math.min(59, Math.max(0, parseInt(minute,10)||0));
        var iso = g[0] + '-' + pad(g[1]) + '-' + pad(g[2]) + 'T' + pad(hh) + ':' + pad(mmn) + ':00';
        wp.data.dispatch('core/editor').editPost({ date: iso });
        setMsg('تاریخ انتشار به‌روز شد');
      }

      return el(PluginDocumentSettingPanel, {
        name: 'bankai-jalali-schedule',
        title: 'تاریخ شمسی انتشار',
        className: 'bk-jalali-doc-panel'
      }, el('div', { id: 'bk-gutenberg-jdate-wrap' },
        el('label', { style: { display: 'block', marginBottom: '6px', fontWeight: 600 } }, 'تاریخ جلالی'),
        el('input', {
          type: 'text',
          className: 'bankai-jalali-date components-text-control__input',
          value: jstr,
          onChange: function (e) { setJstr(e.target.value); },
          style: { width: '100%', marginBottom: '8px' }
        }),
        el('div', { style: { display: 'flex', gap: '6px', alignItems: 'center', marginBottom: '8px' } },
          el('input', { type: 'number', min: 0, max: 23, value: hour, onChange: function (e) { setHour(e.target.value); }, style: { width: '64px' } }),
          el('span', null, ':'),
          el('input', { type: 'number', min: 0, max: 59, value: minute, onChange: function (e) { setMinute(e.target.value); }, style: { width: '64px' } })
        ),
        el('button', { type: 'button', className: 'components-button is-primary is-compact', onClick: apply }, 'اعمال تاریخ'),
        msg ? el('p', { style: { marginTop: '8px', fontSize: '12px', color: '#0f7b3a' } }, msg) : null
      ));
    }

    registerPlugin('bankai-jalali-schedule', { render: Panel, icon: 'calendar-alt' });
  }

  function boot() {
    enhanceClassic();
    try { enhanceBlock(); } catch (e) {}
  }
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', boot);
  else boot();
  d.addEventListener('click', function (e) {
    if (e.target && (e.target.id === 'edit-timestamp' || (e.target.closest && e.target.closest('#edit-timestamp')))) {
      setTimeout(enhanceClassic, 50);
    }
  });
})(window, document);
JS;
    }

    private function datepicker_js(): string
    {
        $months_json = wp_json_encode(array_values(self::$months), JSON_UNESCAPED_UNICODE);
        return <<<JS
(function (w) {
  var MONTHS = {$months_json};
  function g2j(gy, gm, gd) {
    var g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
    var gy2 = (gm > 2) ? (gy + 1) : gy;
    var days = 355666 + (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
      + Math.floor((gy2 + 399) / 400) + gd + g_d_m[gm - 1];
    var jy = -1595 + (33 * Math.floor(days / 12053));
    days %= 12053;
    jy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) { jy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
    var jm, jd;
    if (days < 186) { jm = 1 + Math.floor(days / 31); jd = 1 + (days % 31); }
    else { jm = 7 + Math.floor((days - 186) / 30); jd = 1 + ((days - 186) % 30); }
    return [jy, jm, jd];
  }
  function j2g(jy, jm, jd) {
    jy += 1595;
    var days = -355668 + (365 * jy) + Math.floor(jy / 33) * 8 + Math.floor(((jy % 33) + 3) / 4) + jd
      + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30 + 186));
    var gy = 400 * Math.floor(days / 146097);
    days %= 146097;
    if (days > 36524) {
      gy += 100 * Math.floor(--days / 36524);
      days %= 36524;
      if (days >= 365) days++;
    }
    gy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) { gy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
    var gd = days + 1;
    var sal_a = [0,31,((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28,31,30,31,30,31,31,30,31,30,31];
    var gm = 1;
    for (; gm <= 12 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
    return [gy, gm, gd];
  }
  function daysInMonth(jy, jm) {
    if (jm <= 6) return 31;
    if (jm <= 11) return 30;
    var a = jy - (jy > 0 ? 474 : 473);
    var b = a % 2820 + 474;
    return (((b + 38) * 682) % 2816) < 682 ? 30 : 29;
  }
  function pad(n) { return n < 10 ? '0' + n : '' + n; }

  var ACTIVE = null;
  var placeRaf = 0;
  var listenersBound = false;
  var DOW = ['ش','ی','د','س','چ','پ','ج'];
  var SELECTOR = 'input.bankai-jalali-date, input[data-bankai-jalali], input.bk-jalali-date';

  function schedulePlace() {
    if (placeRaf) return;
    placeRaf = requestAnimationFrame(function () {
      placeRaf = 0;
      if (ACTIVE && ACTIVE.place) ACTIVE.place();
    });
  }
  function onWinScrollOrResize() {
    if (ACTIVE) schedulePlace();
  }
  function onDocPointer(e) {
    if (!ACTIVE) return;
    var t = e.target;
    if (ACTIVE.wrap.contains(t) || ACTIVE.panel.contains(t)) return;
    ACTIVE.close();
  }
  function bindSharedListeners() {
    if (listenersBound) return;
    listenersBound = true;
    window.addEventListener('scroll', onWinScrollOrResize, { capture: true, passive: true });
    window.addEventListener('resize', onWinScrollOrResize, { passive: true });
    document.addEventListener('pointerdown', onDocPointer, true);
  }

  function BankaiJdp(input) {
    if (!input || input._bkjdp) return;
    input._bkjdp = true;
    bindSharedListeners();

    var wrap = document.createElement('div');
    wrap.className = 'bk-jdp';
    input.classList.add('bk-jdp-input');
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('inputmode', 'numeric');
    if (input.parentNode) {
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);
    }
    var panel = document.createElement('div');
    panel.className = 'bk-jdp-panel';
    panel.setAttribute('role', 'dialog');
    document.body.appendChild(panel);

    var now = new Date();
    var cur = g2j(now.getFullYear(), now.getMonth() + 1, now.getDate());
    var viewY = cur[0], viewM = cur[1];
    var sel = null;
    var lastKey = '';

    function parseInput() {
      var v = (input.value || '').trim();
      var m = v.match(/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/);
      if (m) {
        sel = [parseInt(m[1], 10), parseInt(m[2], 10), parseInt(m[3], 10)];
        viewY = sel[0];
        viewM = sel[1];
      }
    }
    function render() {
      var key = viewY + '-' + viewM + '-' + (sel ? sel.join('.') : '') + '-' + cur.join('.');
      if (key === lastKey && panel.firstChild) return;
      lastKey = key;
      var dim = daysInMonth(viewY, viewM);
      var gFirst = j2g(viewY, viewM, 1);
      var startDow = (new Date(gFirst[0], gFirst[1] - 1, gFirst[2]).getDay() + 1) % 7;
      var parts = [];
      parts.push('<div class="bk-jdp-head"><button type="button" data-nav="-1" aria-label="prev">‹</button><div class="bk-jdp-title">');
      parts.push(MONTHS[viewM - 1], ' ', String(viewY));
      parts.push('</div><button type="button" data-nav="1" aria-label="next">›</button></div><div class="bk-jdp-grid">');
      for (var di = 0; di < 7; di++) parts.push('<div class="dow">', DOW[di], '</div>');
      for (var i = 0; i < startDow; i++) parts.push('<button type="button" class="day muted" disabled tabindex="-1"></button>');
      for (var d = 1; d <= dim; d++) {
        var cls = 'day';
        if (sel && sel[0] === viewY && sel[1] === viewM && sel[2] === d) cls += ' is-sel';
        if (cur[0] === viewY && cur[1] === viewM && cur[2] === d) cls += ' is-today';
        parts.push('<button type="button" class="', cls, '" data-d="', String(d), '">', String(d), '</button>');
      }
      parts.push('</div><div class="bk-jdp-foot"><button type="button" data-act="today">امروز</button><button type="button" data-act="clear">پاک</button></div>');
      panel.innerHTML = parts.join('');
    }
    function place() {
      var r = input.getBoundingClientRect();
      var gap = 6;
      var vw = window.innerWidth;
      var vh = window.innerHeight;
      var pw = Math.min(280, vw - 16);
      var ph = panel.offsetHeight || 300;
      var left = r.right - pw;
      if (left < 8) left = 8;
      if (left + pw > vw - 8) left = Math.max(8, vw - pw - 8);
      var top = r.bottom + gap;
      if (top + ph > vh - 8) {
        top = r.top - ph - gap;
        if (top < 8) top = Math.max(8, Math.min(r.bottom + gap, vh - ph - 8));
      }
      panel.style.display = 'block';
      panel.style.left = left + 'px';
      panel.style.top = top + 'px';
      panel.style.right = 'auto';
      panel.style.width = pw + 'px';
    }
    function open(e) {
      if (e && e.type === 'focus' && ACTIVE === api) return;
      if (ACTIVE && ACTIVE !== api) ACTIVE.close();
      parseInput();
      lastKey = '';
      render();
      panel.classList.add('is-open');
      ACTIVE = api;
      place();
      schedulePlace();
    }
    function close() {
      if (!panel.classList.contains('is-open')) return;
      panel.classList.remove('is-open');
      panel.style.display = 'none';
      if (ACTIVE === api) ACTIVE = null;
    }
    function emit() {
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    function onPanelClick(e) {
      var t = e.target.closest ? e.target.closest('[data-nav],[data-act],[data-d]') : e.target;
      if (!t || !panel.contains(t)) return;
      var nav = t.getAttribute('data-nav');
      if (nav) {
        var n = parseInt(nav, 10);
        viewM += n;
        if (viewM > 12) { viewM = 1; viewY++; }
        if (viewM < 1) { viewM = 12; viewY--; }
        lastKey = '';
        render();
        schedulePlace();
        return;
      }
      var act = t.getAttribute('data-act');
      if (act === 'today') {
        sel = [cur[0], cur[1], cur[2]];
        viewY = cur[0]; viewM = cur[1];
        input.value = sel[0] + '/' + pad(sel[1]) + '/' + pad(sel[2]);
        var g0 = j2g(sel[0], sel[1], sel[2]);
        input.setAttribute('data-gregorian', g0[0] + '-' + pad(g0[1]) + '-' + pad(g0[2]));
        emit(); close(); return;
      }
      if (act === 'clear') {
        sel = null; input.value = ''; input.removeAttribute('data-gregorian');
        emit(); close(); return;
      }
      var ds = t.getAttribute('data-d');
      if (ds) {
        var day = parseInt(ds, 10);
        sel = [viewY, viewM, day];
        input.value = viewY + '/' + pad(viewM) + '/' + pad(day);
        var g = j2g(viewY, viewM, day);
        input.setAttribute('data-gregorian', g[0] + '-' + pad(g[1]) + '-' + pad(g[2]));
        emit(); close();
      }
    }

    var api = { wrap: wrap, panel: panel, place: place, close: close, open: open };
    input.addEventListener('focus', open);
    input.addEventListener('click', open);
    panel.addEventListener('click', onPanelClick);
  }

  function boot(root) {
    var scope = root || document;
    var list = scope.querySelectorAll(SELECTOR);
    for (var i = 0; i < list.length; i++) BankaiJdp(list[i]);
  }
  w.BankaiJalaliDatepicker = {
    init: boot,
    g2j: g2j,
    j2g: j2g,
    closeActive: function () { if (ACTIVE) ACTIVE.close(); }
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { boot(); }, { once: true });
  } else {
    boot();
  }
})(window);
JS;
    }
}
