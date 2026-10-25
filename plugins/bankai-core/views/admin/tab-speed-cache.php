<?php
/**
 * Tab: Speed & Cache — Professional 3-column settings layout
 * Inspired by modern performance plugin admin UX (sidebar + content + quick actions).
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

/** @var array $state */
$stats = is_array($state['speedStats'] ?? null) ? $state['speedStats'] : [];
if (!$stats && class_exists('Bankai_Speed_Cache')) {
    $stats = Bankai_Speed_Cache::collect_stats();
}
$hit   = esc_html($stats['hit_ratio'] ?? '—');
$ttfb  = esc_html($stats['ttfb'] ?? '—');
$redis = esc_html($stats['redis_latency'] ?? '—');
$page_files = (int) ($stats['page_files'] ?? 0);
$cache_size = esc_html($stats['cache_size'] ?? '—');
$last_purge = esc_html($stats['last_purge'] ?? '—');

$cfg = function_exists('bankai_get_option') ? (bankai_get_option('speed_config', []) ?: []) : [];
if (!is_array($cfg)) { $cfg = []; }
$cfg_json = wp_json_encode($cfg, JSON_UNESCAPED_UNICODE);
$psi_key  = function_exists('bankai_get_option') ? (bankai_get_option('psi_api_key', '') ?: '') : get_option('bankai_psi_api_key', '');
?>

<div id="tab-speed-cache" class="bankai-tab-pane bk-speed-tab-root wpr-like"
     :class="{ 'is-active-tab': activeTab === 'speed-cache' }"
     x-show="activeTab === 'speed-cache'"
     x-cloak>

<div class="bk-wpr-wrap">
  <aside class="bk-wpr-nav" id="bk-wpr-nav">
    <div class="bk-wpr-nav__brand">
      <span class="bk-wpr-nav__logo"><iconify-icon icon="solar:bolt-bold-duotone" width="22" height="22"></iconify-icon></span>
      <div>
        <strong>شتاب‌دهنده بنکای</strong>
        <small>Speed &amp; Cache</small>
      </div>
    </div>
    <nav class="bk-wpr-nav__menu" role="tablist">
      <button type="button" class="bk-wpr-nav__item is-active" data-section="dashboard"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:home-2-bold-duotone" width="20"></iconify-icon></span><span>داشبورد</span></button>
      <button type="button" class="bk-wpr-nav__item" data-section="cache"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:document-bold-duotone" width="20"></iconify-icon></span><span>کش صفحه</span></button>
      <button type="button" class="bk-wpr-nav__item" data-section="files"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:code-file-bold-duotone" width="20"></iconify-icon></span><span>بهینه‌سازی فایل‌ها</span></button>
      <button type="button" class="bk-wpr-nav__item" data-section="media"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:gallery-bold-duotone" width="20"></iconify-icon></span><span>رسانه و تصاویر</span></button>
      <button type="button" class="bk-wpr-nav__item" data-section="preload"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:refresh-bold-duotone" width="20"></iconify-icon></span><span>پیش‌بارگذاری</span></button>
      <button type="button" class="bk-wpr-nav__item" data-section="rules"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:settings-bold-duotone" width="20"></iconify-icon></span><span>قوانین پیشرفته</span></button>
      <button type="button" class="bk-wpr-nav__item" data-section="database"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:database-bold-duotone" width="20"></iconify-icon></span><span>دیتابیس</span></button>
      <button type="button" class="bk-wpr-nav__item" data-section="cdn"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:global-bold-duotone" width="20"></iconify-icon></span><span>CDN و Object Cache</span></button>
      <button type="button" class="bk-wpr-nav__item" data-section="tools"><span class="bk-wpr-nav__icon"><iconify-icon icon="solar:widget-bold-duotone" width="20"></iconify-icon></span><span>ابزار و تست</span></button>
    </nav>
  </aside>

  <main class="bk-wpr-content" id="bk-wpr-content">
    <div class="bk-wpr-progress" id="bk-speed-op-progress" style="display:none">
      <div class="bk-wpr-progress__head"><strong id="bk-op-title">در حال اجرا…</strong><span id="bk-op-pct">0%</span></div>
      <div class="bk-wpr-progress__track"><div class="bk-wpr-progress__fill" id="bk-op-fill" style="width:0%"></div></div>
      <div class="bk-wpr-progress__steps" id="bk-op-steps"></div>
      <p class="bk-wpr-progress__msg" id="bk-op-msg">آماده‌سازی…</p>
    </div>

    <section class="bk-wpr-section is-active" data-section="dashboard">
      <header class="bk-wpr-section__head"><h2>داشبورد</h2><p>وضعیت کلی، آمار کش و دسترسی سریع</p></header>
      <div class="bk-wpr-cards">
        <div class="bk-wpr-card"><span class="bk-wpr-card__label">TTFB</span><strong class="bk-wpr-card__val" id="bk-stat-ttfb"><?php echo $ttfb; ?></strong><span class="bk-wpr-card__hint">هدف: &lt; ۲۰۰ms</span></div>
        <div class="bk-wpr-card"><span class="bk-wpr-card__label">نرخ Hit کش</span><strong class="bk-wpr-card__val" id="bk-stat-hit"><?php echo $hit; ?></strong><span class="bk-wpr-card__hint">درخواست از کش</span></div>
        <div class="bk-wpr-card"><span class="bk-wpr-card__label">فایل‌های کش</span><strong class="bk-wpr-card__val" id="bk-stat-files"><?php echo $page_files; ?></strong><span class="bk-wpr-card__hint">آخرین پاک‌سازی: <?php echo $last_purge; ?></span></div>
        <div class="bk-wpr-card"><span class="bk-wpr-card__label">حجم کش</span><strong class="bk-wpr-card__val" id="bk-stat-size"><?php echo $cache_size; ?></strong><span class="bk-wpr-card__hint">HTML + فشرده</span></div>
      </div>
      <div class="bk-wpr-field-group">
        <h3 class="bk-wpr-field-group__title">وضعیت لایه‌ها</h3>
        <div class="bk-wpr-status-grid">
          <div class="bk-wpr-status-item"><span class="bk-wpr-dot is-on"></span> کش صفحه <strong>فعال</strong></div>
          <div class="bk-wpr-status-item"><span class="bk-wpr-dot is-on"></span> بهینه‌سازی فایل‌ها <strong>فعال</strong></div>
          <div class="bk-wpr-status-item"><span class="bk-wpr-dot <?php echo $redis !== '—' ? 'is-on' : 'is-off'; ?>"></span> Object Cache <strong><?php echo $redis !== '—' ? 'متصل' : 'غیرفعال'; ?></strong></div>
          <div class="bk-wpr-status-item"><span class="bk-wpr-dot is-on"></span> کش مرورگر <strong>فعال</strong></div>
        </div>
      </div>
    </section>

    <section class="bk-wpr-section" data-section="cache">
      <header class="bk-wpr-section__head"><h2>کش صفحه</h2><p>ذخیره HTML عمومی و تحویل سریع بدون اجرای مجدد PHP</p></header>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-page-cache-enable" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>فعال‌سازی کش صفحه</strong><p>نسخه HTML صفحات عمومی ذخیره و در درخواست‌های بعدی تحویل داده می‌شود.</p></div></div></div>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><div><strong>مدت اعتبار کش (TTL)</strong><p>پس از این مدت، فایل کش منقضی می‌شود.</p></div></div><div class="bk-wpr-field__control"><input type="number" id="cfg-page-cache-ttl" value="86400" min="300" step="60" dir="ltr" class="bk-wpr-input"><span class="bk-wpr-unit">ثانیه</span></div></div>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><div><strong>استثناها (URLهایی که کش نشوند)</strong><p>هر خط یک الگو</p></div></div><div class="bk-wpr-field__control bk-wpr-field__control--full"><textarea id="cfg-page-cache-excl" rows="3" class="bk-wpr-textarea" dir="ltr" placeholder="/cart/&#10;/checkout/"></textarea></div></div>
    </section>

    <section class="bk-wpr-section" data-section="files">
      <header class="bk-wpr-section__head"><h2>بهینه‌سازی فایل‌ها</h2><p>کاهش حجم و زمان‌بندی بارگذاری CSS و JavaScript</p></header>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-assets-enable" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>فعال‌سازی بهینه‌سازی دارایی‌ها</strong><p>Minify، Defer و Delay</p></div></div></div>
      <h3 class="bk-wpr-subtitle">CSS</h3>
      <div class="bk-wpr-field bk-wpr-field--child"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-asset-minify-css" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>Minify فایل‌های CSS</strong><p>حذف فاصله و توضیحات غیرضروری</p></div></div></div>
      <h3 class="bk-wpr-subtitle">JavaScript</h3>
      <div class="bk-wpr-field bk-wpr-field--child"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-asset-minify-js" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>Minify فایل‌های JavaScript</strong><p>کاهش حجم اسکریپت‌ها</p></div></div></div>
      <div class="bk-wpr-field bk-wpr-field--child"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-asset-defer-js" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>بارگذاری معوق (Defer)</strong><p>اجرا پس از تجزیه HTML</p></div></div></div>
      <div class="bk-wpr-field bk-wpr-field--child"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-asset-delay-js" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>تأخیر تا تعامل کاربر (Delay JS)</strong><p>اسکریپت‌های غیرضروری تا حرکت موس/اسکرول اجرا نمی‌شوند</p></div></div></div>
    </section>

    <section class="bk-wpr-section" data-section="media">
      <header class="bk-wpr-section__head"><h2>رسانه، تصاویر و فونت</h2><p>Lazy Load، اولویت LCP و میزبانی محلی فونت‌ها</p></header>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-media-enable" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>LazyLoad برای تصاویر</strong><p>تصاویر پایین صفحه هنگام نزدیک شدن به دید بارگذاری می‌شوند. تصویر LCP (بزرگ‌ترین عنصر محتوایی) از Lazy Load خارج می‌شود.</p></div></div></div>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-cwv-enable" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>بهینه‌سازی Core Web Vitals</strong><p>اولویت تصویر LCP، ابعاد تصاویر و کاهش جابه‌جایی چیدمان (CLS)</p></div></div></div>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-preload-enable" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>Preload منابع بحرانی</strong><p>فقط فونت‌ها و منابع واقعاً بالای صفحه زودتر درخواست می‌شوند</p></div></div></div>

      <h3 class="bk-wpr-subtitle">فونت‌های محلی (Self-Host)</h3>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-fonts-local" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>میزبانی محلی فونت‌ها</strong><p>Google Fonts و فونت‌های خارجی به‌جای درخواست شبکه، از سرور خود سایت سرو می‌شوند — کاهش وابستگی خارجی و بهبود TTFB/LCP.</p></div></div></div>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-fonts-display-swap" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>font-display: swap</strong><p>متن بلافاصله با فونت جایگزین نمایش داده می‌شود تا FOIT رخ ندهد</p></div></div></div>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-fonts-preload-critical" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>Preload فونت‌های بحرانی</strong><p>فقط وزن‌های واقعاً استفاده‌شده در بالای صفحه (معمولاً ۴۰۰ و ۷۰۰)</p></div></div></div>
      <div class="bk-wpr-field">
        <div class="bk-wpr-field__main"><div><strong>دانلود و محلی‌سازی Google Fonts</strong><p>فونت‌های شناسایی‌شده را به پوشه uploads/bankai-fonts منتقل و CSS محلی تولید می‌کند.</p></div></div>
        <div class="bk-wpr-field__control">
          <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" id="bk-fonts-localize-btn">
            <span class="bk-spin" style="display:none"></span>
            <iconify-icon icon="solar:download-minimalistic-bold-duotone" width="18"></iconify-icon>
            محلی‌سازی فونت‌ها
          </button>
        </div>
      </div>
      <div id="bk-fonts-status" style="display:none;margin-top:8px;font-size:12px;padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:6px"></div>
    </section>

    <section class="bk-wpr-section" data-section="preload">
      <header class="bk-wpr-section__head"><h2>پیش‌بارگذاری کش</h2><p>تولید از پیش فایل‌های کش با نرخ کنترل‌شده</p></header>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><div><strong>پیش‌گرم هوشمند</strong><p>URLها اولویت‌بندی و با محدودیت بار سرور کش می‌شوند.</p></div></div><div class="bk-wpr-field__control"><button type="button" class="bk-wpr-btn bk-wpr-btn--primary" id="bk-speed-preload-btn"><span class="bk-spin" style="display:none"></span>شروع پیش‌بارگذاری</button></div></div>
    </section>

    <section class="bk-wpr-section" data-section="rules">
      <header class="bk-wpr-section__head"><h2>قوانین پیشرفته</h2><p>Heartbeat و کش مرورگر</p></header>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-browser-enable" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>کش مرورگر</strong><p>هدرهای Expires برای فایل‌های ثابت</p></div></div></div>
      <div class="bk-wpr-field"><div class="bk-wpr-field__main"><label class="bk-wpr-toggle"><input type="checkbox" id="cfg-heartbeat-enable" checked><span class="bk-wpr-toggle__slider"></span></label><div><strong>کاهش فعالیت Heartbeat</strong><p>فاصله درخواست‌های admin-ajax افزایش می‌یابد</p></div></div></div>
    </section>

    <section class="bk-wpr-section" data-section="database">
      <header class="bk-wpr-section__head"><h2>بهینه‌سازی دیتابیس</h2><p>پاکسازی داده‌های قدیمی — قبل از اجرا از پشتیبان مطمئن شوید</p></header>
      <div class="bk-wpr-chips" id="bk-db-chips">
        <label class="bk-wpr-chip is-checked"><input type="checkbox" data-db-opt="revisions" checked> رونوشت‌ها</label>
        <label class="bk-wpr-chip is-checked"><input type="checkbox" data-db-opt="auto_drafts" checked> پیش‌نویس خودکار</label>
        <label class="bk-wpr-chip is-checked"><input type="checkbox" data-db-opt="trash" checked> زباله‌دان</label>
        <label class="bk-wpr-chip is-checked"><input type="checkbox" data-db-opt="spam" checked> دیدگاه اسپم</label>
        <label class="bk-wpr-chip is-checked"><input type="checkbox" data-db-opt="transients" checked> Transient منقضی</label>
      </div>
      <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" id="bk-run-db-opt-btn"><span class="bk-spin" style="display:none"></span>اجرای پاکسازی</button>

      <div id="bk-db-progress" class="bk-db-progress" style="display:none;margin-top:18px">
        <div class="bk-db-progress__bar"><i id="bk-db-progress-fill" style="width:0%"></i></div>
        <div class="bk-db-progress__label" id="bk-db-progress-label">آماده‌سازی…</div>
        <ul class="bk-db-steps" id="bk-db-steps">
          <li data-step="revisions"><span class="bk-db-st">…</span> پاکسازی رونوشت‌ها</li>
          <li data-step="auto_drafts"><span class="bk-db-st">…</span> پیش‌نویس‌های خودکار</li>
          <li data-step="trash"><span class="bk-db-st">…</span> زباله‌دان</li>
          <li data-step="spam"><span class="bk-db-st">…</span> دیدگاه اسپم</li>
          <li data-step="transients"><span class="bk-db-st">…</span> Transient منقضی</li>
          <li data-step="optimize"><span class="bk-db-st">…</span> بهینه‌سازی جداول</li>
        </ul>
      </div>
      <div id="db-opt-result" class="bk-db-report" style="display:none"></div>
    </section>

        <section class="bk-wpr-section" data-section="cdn">
      <header class="bk-wpr-section__head"><h2>CDN و Object Cache</h2><p>کش آبجکت مکمل کش صفحه است — فرم‌ها عمودی و بدون اسکرول افقی</p></header>

      <div class="bk-cdn-stack">
        <div class="bk-cdn-card">
          <h3 class="bk-cdn-card__title"><iconify-icon icon="solar:server-bold-duotone" width="18"></iconify-icon> Redis / Memcached</h3>
          <label class="bk-cdn-field">Host<input type="text" id="redis-host" value="127.0.0.1" dir="ltr" class="bk-wpr-input"></label>
          <label class="bk-cdn-field">Port<input type="number" id="redis-port" value="6379" dir="ltr" class="bk-wpr-input"></label>
          <label class="bk-cdn-field">Password<input type="password" id="redis-password" dir="ltr" class="bk-wpr-input" autocomplete="new-password"></label>
          <label class="bk-cdn-field">DB Index<input type="number" id="redis-db" value="0" dir="ltr" class="bk-wpr-input"></label>
          <div class="bk-cdn-actions">
            <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" id="bk-test-redis-btn"><span class="bk-spin" style="display:none"></span>تست اتصال</button>
            <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" id="bk-save-redis-btn"><span class="bk-spin" style="display:none"></span>ذخیره و فعال‌سازی</button>
          </div>
          <span id="redis-status-msg" class="bk-cdn-status"></span>
        </div>

        <div class="bk-cdn-card">
          <h3 class="bk-cdn-card__title"><iconify-icon icon="solar:global-bold-duotone" width="18"></iconify-icon> CDN</h3>
          <label class="bk-cdn-field">CDN CNAME / URL<input type="url" id="cdn-url" placeholder="https://cdn.example.com" dir="ltr" class="bk-wpr-input"></label>
          <label class="bk-cdn-field bk-cdn-check"><input type="checkbox" id="cdn-enabled"> فعال‌سازی بازنویسی URL دارایی‌ها</label>
          <label class="bk-cdn-field bk-cdn-check"><input type="checkbox" id="cdn-ssl" checked> اجبار HTTPS روی CDN</label>
          <div class="bk-cdn-actions">
            <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" id="bk-save-cdn-btn"><span class="bk-spin" style="display:none"></span>ذخیره CDN</button>
          </div>
          <span id="cdn-status-msg" class="bk-cdn-status"></span>
        </div>
      </div>
    </section>

<section class="bk-wpr-section" data-section="tools">
      <header class="bk-wpr-section__head"><h2>ابزار و تست عملکرد</h2><p>تحلیل و رفع خودکار با امکان بازگشت</p></header>
      <div class="bk-wpr-field-group">
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:12px">
          <input type="url" id="bk-audit-url" class="bk-wpr-input" style="flex:1;min-width:200px" value="<?php echo esc_attr(home_url('/')); ?>" dir="ltr">
          <div class="bk-wpr-device-toggle">
            <button type="button" class="bk-wpr-device-btn is-active" data-strategy="mobile">موبایل</button>
            <button type="button" class="bk-wpr-device-btn" data-strategy="desktop">دسکتاپ</button>
          </div>
          <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" id="bk-run-audit-btn"><span class="bk-spin" style="display:none"></span>اجرای تست</button>
        </div>
        <div style="display:flex;gap:8px;align-items:center;margin-bottom:16px">
          <input type="text" id="bk-psi-key-input" class="bk-wpr-input" placeholder="کلید PageSpeed API (اختیاری)" value="<?php echo esc_attr($psi_key); ?>" dir="ltr" style="flex:1">
          <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" id="bk-save-psi-key-btn">ذخیره کلید</button>
        </div>
      </div>
      <div class="bk-wpr-field-group">
        <h3 class="bk-wpr-field-group__title">رفع هوشمند (Fix All)</h3>
        <p style="font-size:13px;color:#64748b;margin:0 0 12px">Snapshot → تشخیص → اعمال → اعتبارسنجی → پیش‌گرم</p>
        <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" id="bk-fix-all-btn"><span class="bk-spin" style="display:none"></span>شروع Fix All</button>
        <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" id="bk-rollback-btn" style="display:none;margin-right:8px">بازگشت به Snapshot</button>
        <div id="bk-fix-all-stages" style="display:none;margin-top:14px">
          <div class="bk-wpr-stage" data-stage="snapshot"><span class="bk-wpr-stage__n">1</span> ایجاد Snapshot <span class="bk-wpr-stage__st">—</span></div>
          <div class="bk-wpr-stage" data-stage="diagnose"><span class="bk-wpr-stage__n">2</span> تشخیص گلوگاه <span class="bk-wpr-stage__st">—</span></div>
          <div class="bk-wpr-stage" data-stage="apply"><span class="bk-wpr-stage__n">3</span> اعمال بهینه‌سازی <span class="bk-wpr-stage__st">—</span></div>
          <div class="bk-wpr-stage" data-stage="validate"><span class="bk-wpr-stage__n">4</span> اعتبارسنجی <span class="bk-wpr-stage__st">—</span></div>
          <div class="bk-wpr-stage" data-stage="warmup"><span class="bk-wpr-stage__n">5</span> پیش‌گرم کش <span class="bk-wpr-stage__st">—</span></div>
        </div>
      </div>
      <div id="bk-audit-results" style="display:none;margin-top:16px"></div>
    </section>

    <div class="bk-wpr-savebar">
      <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" id="bk-save-speed-cfg-btn"><span class="bk-spin" style="display:none"></span>ذخیره تغییرات</button>
    </div>
  </main>

  <aside class="bk-wpr-sidebar">
    <div class="bk-wpr-sidebar__box">
      <h3>اقدامات سریع</h3>
      <p class="bk-wpr-sidebar__desc">عملیات پرکاربرد</p>
      <div class="bk-wpr-qa"><span class="bk-wpr-qa__label">پاک‌سازی فایل‌های کش</span><button type="button" class="bk-wpr-btn bk-wpr-btn--action" id="bk-speed-purge-btn"><span class="bk-spin" style="display:none"></span><iconify-icon icon="solar:trash-bin-trash-bold-duotone" width="16"></iconify-icon> CLEAR CACHE</button></div>
      <div class="bk-wpr-qa"><span class="bk-wpr-qa__label">شروع پیش‌بارگذاری</span><button type="button" class="bk-wpr-btn bk-wpr-btn--action" id="bk-speed-preload-btn-side"><span class="bk-spin" style="display:none"></span><iconify-icon icon="solar:refresh-circle-bold-duotone" width="16"></iconify-icon> PRELOAD CACHE</button></div>
      <div class="bk-wpr-qa"><span class="bk-wpr-qa__label">بازسازی دارایی‌ها</span><button type="button" class="bk-wpr-btn bk-wpr-btn--action" id="bk-speed-rebuild-btn"><span class="bk-spin" style="display:none"></span><iconify-icon icon="solar:restart-bold-duotone" width="16"></iconify-icon> REBUILD ASSETS</button></div>
      <div class="bk-wpr-qa"><span class="bk-wpr-qa__label">پاک‌سازی + پیش‌بارگذاری</span><button type="button" class="bk-wpr-btn bk-wpr-btn--action" id="bk-speed-purge-preload-btn"><span class="bk-spin" style="display:none"></span><iconify-icon icon="solar:bolt-bold-duotone" width="16"></iconify-icon> CLEAR &amp; PRELOAD</button></div>
    </div>
    <div class="bk-wpr-sidebar__box">
      <h3>نکته</h3>
      <p style="font-size:12px;color:#64748b;line-height:1.55;margin:0">پس از هر تغییر مهم، یک‌بار Clear &amp; Preload بزنید. همه گزینه‌ها را هم‌زمان فعال نکنید.</p>
    </div>
  </aside>
</div>

<script>window.bankaiSpeedInitialCfg = <?php echo $cfg_json; ?>;</script>
</div>
