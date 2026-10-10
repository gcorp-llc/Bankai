<!-- Tab 1: Dashboard -->
<div id="tab-overview" class="bankai-tab-pane" data-bankai-show="activeTab === 'overview'" data-bankai-cloak x-init="initDashboard && initDashboard()">

    <div class="bk-dash-hero">
        <div class="bk-dash-hero-main">
            <div style="font-size:12px;opacity:.85;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:8px;color:#fff;">Bankai Core</div>
            <h2 style="margin:0 0 8px;font-size:22px;font-weight:800;letter-spacing:-.02em;color:#ffffff !important;"
                data-bankai-text="isRtl ? 'پیشخوان سلامت و عملکرد سایت' : 'Site health & performance'">پیشخوان سلامت و عملکرد سایت</h2>
            <p style="margin:0 0 16px;opacity:.9;font-size:13px;max-width:48ch;line-height:1.65;color:#f5f3ff;"
               data-bankai-text="isRtl ? 'وضعیت سئو، سرعت، بازدید و ماژول‌های فعال را از یک نگاه ببینید.' : 'SEO, speed, traffic and active modules at a glance.'">وضعیت سئو، سرعت، بازدید و ماژول‌های فعال را از یک نگاه ببینید.</p>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <button type="button" class="bk-btn-primary" style="background:#fff!important;color:#4c1d95!important;box-shadow:none!important;" data-bankai-click="setTab('seo-engine')" data-bankai-text="isRtl ? 'موتور سئو' : 'SEO Engine'">موتور سئو</button>
                <button type="button" class="bankai-btn-action" style="background:rgba(255,255,255,.14)!important;border-color:rgba(255,255,255,.25)!important;color:#fff!important;" data-bankai-click="setTab('articles')" data-bankai-text="isRtl ? 'مدیریت مقالات' : 'Articles'">مدیریت مقالات</button>
            </div>
        </div>
        <div class="bankai-card" style="padding:18px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <strong style="font-size:14px;" data-bankai-text="isRtl ? 'بازدید سایت (۷ روز)' : 'Site visits (7 days)'">بازدید سایت (۷ روز)</strong>
                <button type="button" class="bk-btn-ghost" style="font-size:12px;" data-bankai-click="loadDashboardVisits()" data-bankai-disabled="dashVisitsLoading" data-bankai-text="dashVisitsLoading ? '…' : (isRtl ? 'تازه‌سازی' : 'Refresh')">تازه‌سازی</button>
            </div>
            <div style="font-size:32px;font-weight:800;color:#6d4aff;line-height:1;" data-bankai-text="formatDashNum(dashVisits.total)">—</div>
            <div style="font-size:12px;color:#8b90a0;margin-top:8px;">
                <span data-bankai-text="isRtl ? 'نسبت به هفته قبل: ' : 'vs previous week: '"></span>
                <strong data-bankai-style="(dashVisits.change||0) >= 0 ? 'color:#16a34a' : 'color:#dc2626'" data-bankai-text="((dashVisits.change||0) >= 0 ? '+' : '') + (dashVisits.change||0) + '%'"></strong>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:14px;">
                <div style="background:#f8f7ff;border-radius:12px;padding:10px;text-align:center;">
                    <div style="font-size:11px;color:#8b90a0;" data-bankai-text="isRtl ? 'رایانه' : 'Desktop'">رایانه</div>
                    <div style="font-weight:800;color:#1a1433;font-size:18px;" data-bankai-text="formatDashNum(dashVisits.desktop)">0</div>
                </div>
                <div style="background:#f8f7ff;border-radius:12px;padding:10px;text-align:center;">
                    <div style="font-size:11px;color:#8b90a0;" data-bankai-text="isRtl ? 'موبایل' : 'Mobile'">موبایل</div>
                    <div style="font-weight:800;color:#1a1433;font-size:18px;" data-bankai-text="formatDashNum(dashVisits.mobile)">0</div>
                </div>
                <div style="background:#f8f7ff;border-radius:12px;padding:10px;text-align:center;">
                    <div style="font-size:11px;color:#8b90a0;" data-bankai-text="isRtl ? 'تبلت' : 'Tablet'">تبلت</div>
                    <div style="font-weight:800;color:#1a1433;font-size:18px;" data-bankai-text="formatDashNum(dashVisits.tablet)">0</div>
                </div>
            </div>
            <p style="margin:12px 0 0;font-size:11px;color:#94a3b8;" data-bankai-show="dashVisits.error" data-bankai-text="dashVisits.error"></p>
        </div>
    </div>

    <div class="bk-dash-metrics">
        <div class="bk-dash-metric is-green">
            <div class="lab">Uptime</div>
            <div class="val"><?php echo esc_html(($state['stats']['uptime'] ?? '99.9%')); ?></div>
            <div class="sub" data-bankai-text="isRtl ? 'پایداری سرویس' : 'Service stability'"></div>
        </div>
        <div class="bk-dash-metric is-blue">
            <div class="lab">SEO Score</div>
            <div class="val"><?php echo esc_html((string) ($state['stats']['avg_seo'] ?? $state['stats']['seo_score'] ?? '—')); ?></div>
            <div class="sub" data-bankai-text="isRtl ? 'میانگین امتیاز مقالات' : 'Average post score'"></div>
        </div>
        <div class="bk-dash-metric is-purple">
            <div class="lab">Indexable</div>
            <div class="val"><?php echo esc_html((string) ($state['stats']['indexable'] ?? $state['stats']['posts'] ?? '—')); ?></div>
            <div class="sub" data-bankai-text="isRtl ? 'محتوای قابل ایندکس' : 'Indexable content'"></div>
        </div>
        <div class="bk-dash-metric is-amber">
            <div class="lab">TTFB</div>
            <div class="val"><?php
                $ttfb = $state['stats']['avg_ttfb'] ?? $state['stats']['avg_latency'] ?? null;
                echo esc_html($ttfb !== null && $ttfb !== '' ? (is_numeric($ttfb) ? $ttfb . 'ms' : (string) $ttfb) : '—');
            ?></div>
            <div class="sub" data-bankai-text="isRtl ? 'میانگین تأخیر' : 'Avg latency'"></div>
        </div>
    </div>

    <!-- Core modules with descriptions + toggles -->
    <div class="bankai-card" style="padding:20px;margin-bottom:18px;">
        <h3 style="margin:0 0 6px;font-size:15px;font-weight:800;" data-bankai-text="isRtl ? 'ماژول‌های هسته' : 'Core modules'">ماژول‌های هسته</h3>
        <p style="margin:0 0 14px;font-size:12px;color:#8b90a0;" data-bankai-text="isRtl ? 'هر ماژول را فعال یا غیرفعال کنید. توضیح کوتاه زیر نام آمده است.' : 'Enable or disable each module. Short description under the name.'">هر ماژول را فعال یا غیرفعال کنید.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
            <template data-bankai-for="mod in coreModuleCards" data-bankai-key="mod.key">
                <div class="bk-core-mod-card" data-bankai-class="{ 'is-on': moduleActive[mod.key] !== false }">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                        <div style="min-width:0;">
                            <div style="font-weight:800;font-size:13px;color:#1a1433;" data-bankai-text="isRtl ? mod.title_fa : mod.title"></div>
                            <div style="font-size:12px;color:#64748b;line-height:1.55;margin-top:4px;" data-bankai-text="isRtl ? mod.desc_fa : mod.desc"></div>
                        </div>
                        <label class="bankai-switch" style="flex-shrink:0;">
                            <input type="checkbox"
                                   :checked="moduleActive[mod.key] !== false"
                                   data-bankai-change="toggleCoreModule(mod.key, $event.target.checked)">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <div style="margin-top:10px;font-size:11px;font-weight:700;"
                         data-bankai-style="moduleActive[mod.key] !== false ? 'color:#16a34a' : 'color:#94a3b8'"
                         data-bankai-text="moduleActive[mod.key] !== false ? (isRtl ? 'فعال' : 'Active') : (isRtl ? 'غیرفعال' : 'Off')"></div>
                </div>
            </template>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
        <button type="button" class="bankai-card" style="padding:16px;text-align:start;cursor:pointer;" data-bankai-click="setTab('seo-engine')">
            <div style="font-weight:800;margin-bottom:4px;" data-bankai-text="isRtl ? 'سئو و اسکیما' : 'SEO & Schema'"></div>
            <div style="font-size:12px;color:#8b90a0;" data-bankai-text="isRtl ? 'ممیزی و ماژول‌ها' : 'Audit & modules'"></div>
        </button>
        <button type="button" class="bankai-card" style="padding:16px;text-align:start;cursor:pointer;" data-bankai-click="setTab('ai-studio')">
            <div style="font-weight:800;margin-bottom:4px;" data-bankai-text="isRtl ? 'استودیو AI' : 'AI Studio'"></div>
            <div style="font-size:12px;color:#8b90a0;" data-bankai-text="isRtl ? 'موتورهای متن و تصویر' : 'Text & image engines'"></div>
        </button>
        <button type="button" class="bankai-card" style="padding:16px;text-align:start;cursor:pointer;" data-bankai-click="setTab('speed-cache')">
            <div style="font-weight:800;margin-bottom:4px;" data-bankai-text="isRtl ? 'کش و سرعت' : 'Cache & Speed'"></div>
            <div style="font-size:12px;color:#8b90a0;" data-bankai-text="isRtl ? 'بهینه‌سازی عملکرد' : 'Performance tools'"></div>
        </button>
        <button type="button" class="bankai-card" style="padding:16px;text-align:start;cursor:pointer;" data-bankai-click="setTab('settings-license')">
            <div style="font-weight:800;margin-bottom:4px;" data-bankai-text="isRtl ? 'تنظیمات و لایسنس' : 'Settings & License'"></div>
            <div style="font-size:12px;color:#8b90a0;" data-bankai-text="isRtl ? 'پیکربندی عمومی' : 'General config'"></div>
        </button>
    </div>
</div>
