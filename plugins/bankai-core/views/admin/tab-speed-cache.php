<?php
/**
 * Tab: Cache & Performance — GitHub Light / Primer Design System
 * Pure Vanilla JS rendering without Alpine.js or HTMX directives.
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

$cfg = function_exists('bankai_get_option') ? (bankai_get_option('speed_config', []) ?: []) : [];
if (!is_array($cfg)) { $cfg = []; }
$cfg_json = wp_json_encode($cfg, JSON_UNESCAPED_UNICODE);
$psi_key  = function_exists('bankai_get_option') ? (bankai_get_option('psi_api_key', '') ?: '') : get_option('bankai_psi_api_key', '');
?>

<div id="tab-speed-cache" class="bankai-tab-pane bk-speed-tab-root">

    <!-- Hero Header -->
    <div class="bk-speed-hero">
        <div class="bk-speed-hero__info">
            <div class="bk-speed-hero__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <div>
                <div class="bk-speed-hero__title-row">
                    <h2>مدیریت کش و شتاب‌دهنده سرعت</h2>
                    <span class="bk-speed-pill">Performance Lab</span>
                </div>
                <p class="bk-speed-hero__desc">کش صفحات استاتیک، فشرده‌سازی CSS/JS، بهینه‌سازی CWV، Redis Object Cache و تحلیل‌گر Performance</p>
            </div>
        </div>
        <div class="bk-speed-hero__actions">
            <button type="button" class="bk-btn-secondary" id="bk-speed-rebuild-btn">
                <span class="bk-spin" style="display:none"></span>
                بازسازی فایل‌های بهینه‌شده
            </button>
            <button type="button" class="bk-btn-secondary" id="bk-speed-purge-btn">
                <span class="bk-spin" style="display:none"></span>
                تخلیه کامل کش
            </button>
            <button type="button" class="bk-btn-primary" id="bk-speed-preload-btn">
                <span class="bk-spin" style="display:none"></span>
                پیش‌بارگذاری کش (Preload)
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="bk-speed-stats-row">
        <div class="bk-speed-stat-card">
            <span class="bk-speed-stat-card__label">زمان پاسخ‌دهی (TTFB)</span>
            <span class="bk-speed-stat-card__val" id="bk-stat-ttfb"><?php echo $ttfb; ?></span>
            <span class="bk-speed-stat-card__sub">✓ سرعت پاسخ سرور</span>
        </div>
        <div class="bk-speed-stat-card">
            <span class="bk-speed-stat-card__label">نرخ موفقیت کش (Hit Ratio)</span>
            <span class="bk-speed-stat-card__val" id="bk-stat-hit"><?php echo $hit; ?></span>
            <span class="bk-speed-stat-card__sub">درخواست‌های سروشده از کش</span>
        </div>
        <div class="bk-speed-stat-card">
            <span class="bk-speed-stat-card__label">وضعیت Redis Object Cache</span>
            <span class="bk-speed-stat-card__val" id="bk-stat-redis"><?php echo $redis; ?></span>
            <span class="bk-speed-stat-card__sub">بک‌اند کش پایگاه‌داده</span>
        </div>
        <div class="bk-speed-stat-card">
            <span class="bk-speed-stat-card__label">فایل‌های کش صفحات</span>
            <span class="bk-speed-stat-card__val" id="bk-stat-files"><?php echo (int)($stats['page_files'] ?? 0); ?></span>
            <span class="bk-speed-stat-card__sub">نسخه‌های ذخیره‌شده HTML</span>
        </div>
    </div>

    <!-- Subtab Navigation Bar -->
    <div class="bk-speed-tabs-nav" role="tablist">
        <button type="button" class="bk-speed-tab-btn is-active" data-subtab="auditor">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm7-3.25v2.69l1.88 1.88a.75.75 0 1 1-1.06 1.06l-2.2-2.2A.75.75 0 0 1 7 8.75v-4a.75.75 0 0 1 1.5 0Z"/></svg>
            تست عملکرد و Fix All
        </button>
        <button type="button" class="bk-speed-tab-btn" data-subtab="config">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v12.5A1.75 1.75 0 0 1 14.25 16H1.75A1.75 1.75 0 0 1 0 14.25Zm1.75-.25a.25.25 0 0 0-.25.25v12.5c0 .138.112.25.25.25h12.5a.25.25 0 0 0 .25-.25V1.75a.25.25 0 0 0-.25-.25ZM3.5 7.75a.75.75 0 0 1 .75-.75h7a.75.75 0 0 1 0 1.5h-7a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5Z"/></svg>
            پیکربندی کش و دارایی‌ها
        </button>
        <button type="button" class="bk-speed-tab-btn" data-subtab="redis">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M0 2.75C0 1.784.784 1 1.75 1h12.5c.966 0 1.75.784 1.75 1.75v10.5A1.75 1.75 0 0 1 14.25 15H1.75A1.75 1.75 0 0 1 0 13.25ZM1.75 2.5a.25.25 0 0 0-.25.25v10.5c0 .138.112.25.25.25h12.5a.25.25 0 0 0 .25-.25V2.75a.25.25 0 0 0-.25-.25Z"/></svg>
            تنظیمات Redis
        </button>
        <button type="button" class="bk-speed-tab-btn" data-subtab="db">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0a8 8 0 0 0-1.5.14v1.53A6.5 6.5 0 1 1 1.67 8H.14A8 8 0 1 0 8 0ZM2.5 8a5.5 5.5 0 1 0 5.5-5.5V8Z"/></svg>
            بهینه‌سازی دیتابیس
        </button>
        <button type="button" class="bk-speed-tab-btn" data-subtab="history">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M1.75 2.5a.25.25 0 0 0-.25.25v10.5c0 .138.112.25.25.25h12.5a.25.25 0 0 0 .25-.25V2.75a.25.25 0 0 0-.25-.25Z"/></svg>
            تاریخچه تست‌ها و گزارش
        </button>
    </div>

    <!-- PANE 1: Performance Auditor & Fix All -->
    <div class="bk-speed-tab-pane is-active" id="bk-pane-auditor">
        <div class="bk-audit-form-card">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                <div>
                    <strong style="font-size:15px;color:#1f2328">ابزار تحلیل جامع Performance ( PageSpeed / Lighthouse )</strong>
                    <p style="margin:4px 0 0;font-size:12px;color:#636c76">آدرس صفحه سایت را وارد کنید تا تمام شاخص‌های حیاتی وب و فرصت‌های بهینه‌سازی سنجیده شوند.</p>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <input type="text" id="bk-psi-key-input" placeholder="کلید PageSpeed API (اختیاری)" value="<?php echo esc_attr($psi_key); ?>" style="font-size:12px;padding:6px 10px;border:1px solid #d0d7de;border-radius:6px;width:200px" dir="ltr">
                    <button type="button" class="bk-btn-secondary" id="bk-save-psi-key-btn" style="padding:6px 12px;font-size:12px">ذخیره کلید</button>
                </div>
            </div>

            <div class="bk-audit-form-row">
                <input type="url" id="bk-audit-url" class="bk-audit-url-input" value="<?php echo esc_attr(home_url('/')); ?>" placeholder="https://example.com">
                <div class="bk-audit-device-toggle">
                    <button type="button" class="bk-audit-device-btn is-active" data-strategy="mobile">
                        📱 موبایل
                    </button>
                    <button type="button" class="bk-audit-device-btn" data-strategy="desktop">
                        💻 دسکتاپ
                    </button>
                </div>
                <button type="button" class="bk-btn-primary" id="bk-run-audit-btn" style="padding:10px 20px">
                    <span class="bk-spin" style="display:none"></span>
                    اجرای تست عملکرد
                </button>
            </div>
        </div>

        <!-- Fix All Guided Drawer -->
        <div class="bk-fix-all-panel" id="bk-fix-all-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                <div>
                    <strong style="font-size:15px;color:#1f2328">رفع هوشمند و یک‌کلیکه تمام مشکلات (Fix All Issues)</strong>
                    <p style="margin:4px 0 0;font-size:12px;color:#636c76">ایجاد خودکار نسخه پشتیبان (Snapshot)، اصلاح تنظیمات پربازده و سنجش مجدد نتایج جهت اطمینان از عدم اختلال.</p>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <button type="button" class="bk-btn-primary" id="bk-fix-all-btn" style="background:#0969da;border-color:#0969da">
                        <span class="bk-spin" style="display:none"></span>
                        ⚡ اجرای Fix All و بهینه‌سازی خودکار
                    </button>
                    <button type="button" class="bk-btn-secondary" id="bk-rollback-btn" style="display:none">
                        بازگردانی آخرین Snapshot
                    </button>
                </div>
            </div>
            <div id="bk-fix-all-log" style="display:none;font-size:12px;background:#ffffff;border:1px solid #d0d7de;border-radius:8px;padding:12px;max-height:180px;overflow-y:auto"></div>
        </div>

        <!-- Audit Score Cards & Gauges -->
        <div id="bk-audit-results-container" style="display:none">
            <div class="bk-audit-gauges-grid">
                <div class="bk-gauge-card">
                    <div class="bk-gauge-circle" id="gauge-perf">—</div>
                    <span class="bk-gauge-label">Performance</span>
                </div>
                <div class="bk-gauge-card">
                    <div class="bk-gauge-circle" id="gauge-access">—</div>
                    <span class="bk-gauge-label">Accessibility</span>
                </div>
                <div class="bk-gauge-card">
                    <div class="bk-gauge-circle" id="gauge-bp">—</div>
                    <span class="bk-gauge-label">Best Practices</span>
                </div>
                <div class="bk-gauge-card">
                    <div class="bk-gauge-circle" id="gauge-seo">—</div>
                    <span class="bk-gauge-label">SEO</span>
                </div>
            </div>

            <!-- Vital Metrics Cards Row -->
            <div class="bk-vitals-grid" style="margin-top:16px">
                <div class="bk-vital-card">
                    <span class="bk-vital-card__title">First Contentful Paint (FCP)</span>
                    <span class="bk-vital-card__val" id="vital-fcp">—</span>
                    <span class="bk-vital-card__status" id="status-fcp">—</span>
                </div>
                <div class="bk-vital-card">
                    <span class="bk-vital-card__title">Largest Contentful Paint (LCP)</span>
                    <span class="bk-vital-card__val" id="vital-lcp">—</span>
                    <span class="bk-vital-card__status" id="status-lcp">—</span>
                </div>
                <div class="bk-vital-card">
                    <span class="bk-vital-card__title">Total Blocking Time (TBT)</span>
                    <span class="bk-vital-card__val" id="vital-tbt">—</span>
                    <span class="bk-vital-card__status" id="status-tbt">—</span>
                </div>
                <div class="bk-vital-card">
                    <span class="bk-vital-card__title">Cumulative Layout Shift (CLS)</span>
                    <span class="bk-vital-card__val" id="vital-cls">—</span>
                    <span class="bk-vital-card__status" id="status-cls">—</span>
                </div>
                <div class="bk-vital-card">
                    <span class="bk-vital-card__title">Speed Index</span>
                    <span class="bk-vital-card__val" id="vital-si">—</span>
                    <span class="bk-vital-card__status" id="status-si">—</span>
                </div>
            </div>

            <!-- Issues & Opportunities List -->
            <div style="margin-top:20px">
                <h3 style="font-size:16px;font-weight:700;color:#1f2328;margin-bottom:12px">فرصت‌های بهینه‌سازی و مشکلات شناسایی‌شده</h3>
                <div class="bk-issue-list" id="bk-opportunities-list"></div>
            </div>
        </div>
    </div>

    <!-- PANE 2: Configuration & Modules -->
    <div class="bk-speed-tab-pane" id="bk-pane-config">
        <div class="bk-mod-grid">
            <!-- Page Caching Card -->
            <div class="bk-mod-card is-on">
                <div class="bk-mod-card__head">
                    <span class="bk-mod-card__title">کش صفحات استاتیک (Page Cache)</span>
                    <input type="checkbox" id="cfg-page-cache-enable" checked>
                </div>
                <p class="bk-mod-card__desc">ذخیره نسخه کامل HTML صفحات برای کاربران ناشناس جهت کاهش TTFB به کمتر از ۲۰۰ میلی‌ثانیه.</p>
                <div class="bk-mod-card__foot">
                    <span style="font-size:12px;color:#1f883d;font-weight:600">فعال</span>
                    <button type="button" class="bk-btn-secondary" style="padding:4px 10px;font-size:12px" onclick="document.getElementById('opt-details-page-cache').style.display=document.getElementById('opt-details-page-cache').style.display==='none'?'block':'none'">تنظیمات</button>
                </div>
                <div id="opt-details-page-cache" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #f0f2f5">
                    <label style="font-size:12px;display:block;margin-bottom:6px">عمر اعتبار کش (ثانیه)
                        <input type="number" id="cfg-page-cache-ttl" value="86400" style="width:100%;font-size:12px;padding:6px;border:1px solid #d0d7de;border-radius:6px">
                    </label>
                    <label style="font-size:12px;display:block;margin-bottom:6px">استثنائات آدرس (هر خط یک آدرس/الگو)
                        <textarea id="cfg-page-cache-excl" rows="2" style="width:100%;font-size:12px;padding:6px;border:1px solid #d0d7de;border-radius:6px"></textarea>
                    </label>
                </div>
            </div>

            <!-- Asset Optimization Card -->
            <div class="bk-mod-card is-on">
                <div class="bk-mod-card__head">
                    <span class="bk-mod-card__title">بهینه‌سازی CSS و JavaScript</span>
                    <input type="checkbox" id="cfg-assets-enable" checked>
                </div>
                <p class="bk-mod-card__desc">فشرده‌سازی Minify، اجرای به تعویق انداخته (Defer / Delay) و حذف CSS/JS استفاده‌نشده.</p>
                <div class="bk-mod-card__foot">
                    <span style="font-size:12px;color:#1f883d;font-weight:600">فعال</span>
                    <button type="button" class="bk-btn-secondary" style="padding:4px 10px;font-size:12px" onclick="document.getElementById('opt-details-assets').style.display=document.getElementById('opt-details-assets').style.display==='none'?'block':'none'">تنظیمات</button>
                </div>
                <div id="opt-details-assets" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #f0f2f5">
                    <label style="font-size:12px;display:block;margin-bottom:4px"><input type="checkbox" id="cfg-asset-minify-css" checked> Minify CSS</label>
                    <label style="font-size:12px;display:block;margin-bottom:4px"><input type="checkbox" id="cfg-asset-minify-js" checked> Minify JS</label>
                    <label style="font-size:12px;display:block;margin-bottom:4px"><input type="checkbox" id="cfg-asset-defer-js" checked> Defer JS</label>
                    <label style="font-size:12px;display:block;margin-bottom:4px"><input type="checkbox" id="cfg-asset-delay-js" checked> Delay اسکریپت‌های شخص‌ثالث</label>
                </div>
            </div>

            <!-- Core Web Vitals Card -->
            <div class="bk-mod-card is-on">
                <div class="bk-mod-card__head">
                    <span class="bk-mod-card__title">بهینه‌سازی Core Web Vitals (CLS & Font)</span>
                    <input type="checkbox" id="cfg-cwv-enable" checked>
                </div>
                <p class="bk-mod-card__desc">تعیین ابعاد صریح تصاویر جهت صفر کردن CLS، تنظیم font-display: swap و محدودسازی Preconnect.</p>
                <div class="bk-mod-card__foot">
                    <span style="font-size:12px;color:#1f883d;font-weight:600">فعال</span>
                </div>
            </div>

            <!-- Media Lazy Load Card -->
            <div class="bk-mod-card is-on">
                <div class="bk-mod-card__head">
                    <span class="bk-mod-card__title">بارگذاری تنبل رسانه‌ها (Lazy Load)</span>
                    <input type="checkbox" id="cfg-media-enable" checked>
                </div>
                <p class="bk-mod-card__desc">بارگذاری تنبل تصاویر، iframeها و ویدیوهای یوتیوب تا اولین رندر سریع‌تر انجام شود.</p>
                <div class="bk-mod-card__foot">
                    <span style="font-size:12px;color:#1f883d;font-weight:600">فعال</span>
                </div>
            </div>

            <!-- Preload & Hints Card -->
            <div class="bk-mod-card is-on">
                <div class="bk-mod-card__head">
                    <span class="bk-mod-card__title">Preload و DNS Prefetch</span>
                    <input type="checkbox" id="cfg-preload-enable" checked>
                </div>
                <p class="bk-mod-card__desc">Preload فونت‌های حیاتی و برقراری پیش‌فرض اتصال به دامنه‌های خارجی مانند گوگل‌فونت.</p>
                <div class="bk-mod-card__foot">
                    <span style="font-size:12px;color:#1f883d;font-weight:600">فعال</span>
                </div>
            </div>

            <!-- Heartbeat Control Card -->
            <div class="bk-mod-card is-on">
                <div class="bk-mod-card__head">
                    <span class="bk-mod-card__title">کنترل Heartbeat API</span>
                    <input type="checkbox" id="cfg-heartbeat-enable" checked>
                </div>
                <p class="bk-mod-card__desc">کاهش یا غیرفعال‌سازی پایش‌های AJAX وردپرس در فرانت‌اند برای سبک‌تر شدن بار پردازش سرور.</p>
                <div class="bk-mod-card__foot">
                    <span style="font-size:12px;color:#1f883d;font-weight:600">فعال</span>
                </div>
            </div>

            <!-- Browser Cache Card -->
            <div class="bk-mod-card is-on">
                <div class="bk-mod-card__head">
                    <span class="bk-mod-card__title">کش مرورگر و فشرده‌سازی سرور</span>
                    <input type="checkbox" id="cfg-browser-enable" checked>
                </div>
                <p class="bk-mod-card__desc">تولید خودکار قواعد .htaccess برای mod_expires، mod_headers و فشرده‌سازی Gzip/Deflate.</p>
                <div class="bk-mod-card__foot">
                    <span style="font-size:12px;color:#1f883d;font-weight:600">فعال</span>
                </div>
            </div>
        </div>

        <div style="margin-top:16px;display:flex;justify-content:flex-end">
            <button type="button" class="bk-btn-primary" id="bk-save-config-btn" style="padding:10px 24px">
                <span class="bk-spin" style="display:none"></span>
                ذخیره تنظیمات
            </button>
        </div>
    </div>

    <!-- PANE 3: Redis Settings -->
    <div class="bk-speed-tab-pane" id="bk-pane-redis">
        <div class="bk-audit-form-card">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 12px">پیکربندی Redis Object Cache</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
                <label style="font-size:12px;display:block;grid-column:1/-1">
                    <input type="checkbox" id="redis-enabled"> فعال‌سازی Redis Object Cache
                </label>
                <label style="font-size:12px">Host
                    <input type="text" id="redis-host" value="127.0.0.1" dir="ltr" style="width:100%;font-size:13px;padding:8px;border:1px solid #d0d7de;border-radius:6px;margin-top:4px">
                </label>
                <label style="font-size:12px">Port
                    <input type="number" id="redis-port" value="6379" dir="ltr" style="width:100%;font-size:13px;padding:8px;border:1px solid #d0d7de;border-radius:6px;margin-top:4px">
                </label>
                <label style="font-size:12px">Password
                    <input type="password" id="redis-password" dir="ltr" style="width:100%;font-size:13px;padding:8px;border:1px solid #d0d7de;border-radius:6px;margin-top:4px">
                </label>
                <label style="font-size:12px">Database Index
                    <input type="number" id="redis-db" value="0" dir="ltr" style="width:100%;font-size:13px;padding:8px;border:1px solid #d0d7de;border-radius:6px;margin-top:4px">
                </label>
            </div>
            <div style="margin-top:16px;display:flex;align-items:center;gap:12px">
                <button type="button" class="bk-btn-secondary" id="bk-test-redis-btn">
                    <span class="bk-spin" style="display:none"></span>
                    تست اتصال Redis
                </button>
                <button type="button" class="bk-btn-primary" id="bk-save-redis-btn">
                    <span class="bk-spin" style="display:none"></span>
                    ذخیره و فعال‌سازی Drop-in
                </button>
                <span id="redis-status-msg" style="font-size:12px;font-weight:600"></span>
            </div>
        </div>
    </div>

    <!-- PANE 4: Database Optimizer -->
    <div class="bk-speed-tab-pane" id="bk-pane-db">
        <div class="bk-audit-form-card">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 8px">پاکسازی و بهینه‌سازی پایگاه داده</h3>
            <p style="font-size:12px;color:#636c76;margin-bottom:16px">حذف رونوشت‌های قدیمی، پیش‌نویس‌های خودکار، زباله‌دان، دیدگاه‌های اسپم و transientهای منقضی شده.</p>
            <button type="button" class="bk-btn-primary" id="bk-run-db-opt-btn" style="width:fit-content">
                <span class="bk-spin" style="display:none"></span>
                اجرای پاکسازی پایگاه داده
            </button>
            <div id="db-opt-result" style="display:none;margin-top:14px;font-size:12px;padding:12px;background:#f6f8fa;border:1px solid #d0d7de;border-radius:8px"></div>
        </div>
    </div>

    <!-- PANE 5: Test History & Report Export -->
    <div class="bk-speed-tab-pane" id="bk-pane-history">
        <div class="bk-audit-form-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3 style="font-size:16px;font-weight:700;margin:0">تاریخچه تست‌های Performance</h3>
                <button type="button" class="bk-btn-secondary" id="bk-export-report-btn">خروجی گزارش JSON / قابل چاپ</button>
            </div>
            <div id="bk-history-list" style="display:flex;flex-direction:column;gap:10px"></div>
        </div>
    </div>

</div>

<!-- Pass Initial Config Payload to Vanilla JS -->
<script>
window.bankaiSpeedInitialCfg = <?php echo $cfg_json; ?>;
</script>
