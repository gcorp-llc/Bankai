<?php
/**
 * Admin tab: SEO Engine — modules, schema/integrations, fixed keywords, articles
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

/** @var array $state */
$seo_mods = is_array($state['seoModules'] ?? null) ? $state['seoModules'] : [];
$si       = is_array($state['seoIntegrations'] ?? null) ? $state['seoIntegrations'] : [];

$fixed_raw = function_exists('bankai_get_option')
    ? (string) bankai_get_option('seo_fixed_keywords', '')
    : (string) get_option('bankai_seo_fixed_keywords', '');

$ga4     = esc_attr($si['ga4_measurement_id'] ?? $si['google_analytics_id'] ?? '');
$gtm     = esc_attr($si['google_tag_manager_id'] ?? '');
$gsc     = esc_attr($si['google_site_verification'] ?? '');
$bing    = esc_attr($si['bing_webmaster'] ?? '');
$yandex  = esc_attr($si['yandex_verification'] ?? '');
$sitemap = !isset($si['sitemap_enabled']) || !empty($si['sitemap_enabled']);
$robots  = esc_textarea($si['robots_txt'] ?? '');
$site_name = get_bloginfo('name');
$site_desc = get_bloginfo('description');
$site_url  = home_url('/');
$site_icon = get_site_icon_url(128);
if (! $site_icon) {
    $site_icon = '';
}
$posts_count = (int) wp_count_posts('post')->publish;
$page_count  = (int) wp_count_posts('page')->publish;
?>

<div id="tab-seo-engine" class="bankai-tab-pane" data-bankai-show="activeTab === 'seo-engine'" data-bankai-cloak
     data-bankai-data="bankaiSeoEngineData"
     x-init="initEngine()">

    <!-- Unified hero header -->
    <div class="bk-lg-header bk-wm-hero bk-page-hero">
        <div class="bk-wm-hero__main">
            <div class="bk-wm-hero__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </div>
            <div>
                <div class="bk-wm-hero__title-row">
                    <h2>موتور سئو و اسکیما</h2>
                    <span class="bk-wm-pill">JSON-LD · OG · Sitemap</span>
                </div>
                <p>مدیریت ماژول‌های سئو، اسکیما، ساختار متا و تحلیل هوشمند مقالات سایت</p>
            </div>
        </div>
        <div class="bk-wm-hero__actions">
            <button type="button" class="bk-wm-chip-btn" data-bankai-click="runAutoWizard()" data-bankai-disabled="wizardBusy">
                <span class="bk-wizard-spin" data-bankai-show="wizardBusy" data-bankai-cloak></span>
                <span data-bankai-text="wizardBusy ? 'در حال راه‌اندازی…' : 'ویزارد راه‌اندازی سئو'"></span>
            </button>
        </div>
    </div>
    <nav class="bk-art-tabs bk-lg-tabs bk-seo-tabs" role="tablist">
        <button type="button" role="tab" class="bk-lg-tab bk-seo-tab" data-bankai-class="{ 'is-active': seoPanel === 'tools' }" data-bankai-click="seoPanel = 'tools'; if (!an._loaded) loadAnalyticsReport()">ماژول‌های سئو</button>
        <button type="button" role="tab" class="bk-lg-tab bk-seo-tab" data-bankai-class="{ 'is-active': seoPanel === 'analytics' }" data-bankai-click="seoPanel = 'analytics'">گوگل آنالیتیکس</button>
        <button type="button" role="tab" class="bk-lg-tab bk-seo-tab" data-bankai-class="{ 'is-active': seoPanel === 'import' }" data-bankai-click="seoPanel = 'import'; initSeoImport()">همگام‌سازی Rank Math / Yoast</button>
        <button type="button" role="tab" class="bk-lg-tab bk-seo-tab" data-bankai-class="{ 'is-active': seoPanel === 'sitewide' }" data-bankai-click="seoPanel = 'sitewide'; loadSitewide()">سئوی سایت</button>
    </nav>

    <!-- Site Analytics inside SEO modules -->
    <div data-bankai-show="seoPanel === 'tools'" data-bankai-cloak class="bk-analytics-dash bankai-card"
         >
        <div class="bk-an-top">
            <div>
                <div class="bk-an-brand">Bankai Analytics</div>
                <h3 class="bk-an-title">
                    آنالیتیکس سایت
                    <span class="bk-an-badge" data-bankai-class="an.visits_change >= 0 ? 'is-up' : 'is-down'"
                          data-bankai-text="(an.visits_change >= 0 ? '↑ ' : '↓ ') + Math.abs(an.visits_change) + '%'"></span>
                </h3>
                <p class="bk-an-sub">بازدید واقعی · دستگاه · کشور بر اساس IP</p>
            </div>
            <div class="bk-an-ranges">
                <button type="button" class="bk-an-range" data-bankai-class="{ 'is-active': an.range === '1y' }" data-bankai-click="an.range='1y'; an._loaded=false; loadAnalyticsReport()">۱ سال</button>
                <button type="button" class="bk-an-range" data-bankai-class="{ 'is-active': an.range === '30d' }" data-bankai-click="an.range='30d'; an._loaded=false; loadAnalyticsReport()">۳۰ روز</button>
                <button type="button" class="bk-an-range" data-bankai-class="{ 'is-active': an.range === '7d' }" data-bankai-click="an.range='7d'; an._loaded=false; loadAnalyticsReport()">۷ روز</button>
                <button type="button" class="bk-an-range" data-bankai-class="{ 'is-active': an.range === '24h' }" data-bankai-click="an.range='24h'; an._loaded=false; loadAnalyticsReport()">۲۴ ساعت</button>
            </div>
        </div>

        <div class="bk-an-grid">
            <div class="bk-an-card">
                <div class="bk-an-card__head">
                    <span>بازدید</span>
                    <span class="bk-an-chip" data-bankai-class="an.visits_change >= 0 ? 'is-up' : 'is-down'"
                          data-bankai-text="(an.visits_change >= 0 ? '+' : '') + an.visits_change + '%'"></span>
                </div>
                <div class="bk-an-card__val" data-bankai-text="formatAnNum(an.visits)">0</div>
                <svg class="bk-an-spark" viewBox="0 0 200 48" preserveAspectRatio="none">
                    <path :d="sparkPath(an.series)" fill="none" stroke="var(--bankai-accent, #5C6B7A)" stroke-width="2"/>
                    <path :d="sparkArea(an.series)" fill="var(--bankai-accent-subtle, rgba(92,107,122,.12))" opacity=".8"/>
                </svg>
            </div>
            <div class="bk-an-card">
                <div class="bk-an-card__head"><span>دوره قبل</span></div>
                <div class="bk-an-card__val" data-bankai-text="formatAnNum(an.prev_visits)">0</div>
                <p class="bk-an-hint">مقایسه با بازه قبلی هم‌طول</p>
            </div>
            <div class="bk-an-card">
                <div class="bk-an-card__head"><span>کشورهای فعال</span></div>
                <div class="bk-an-card__val" data-bankai-text="an.country_count || 0">0</div>
                <p class="bk-an-hint">از IP / Cloudflare</p>
            </div>

            <div class="bk-an-card bk-an-card--devices">
                <div class="bk-an-devices">
                    <div>
                        <div class="bk-an-dev-label">دسکتاپ</div>
                        <div class="bk-an-dev-pct" data-bankai-text="(an.devices.desktop.pct || 0) + '%'">0%</div>
                        <div class="bk-an-dev-hits" data-bankai-text="formatAnNum(an.devices.desktop.hits)">0</div>
                    </div>
                    <div>
                        <div class="bk-an-dev-label">موبایل</div>
                        <div class="bk-an-dev-pct" data-bankai-text="(an.devices.mobile.pct || 0) + '%'">0%</div>
                        <div class="bk-an-dev-hits" data-bankai-text="formatAnNum(an.devices.mobile.hits)">0</div>
                    </div>
                    <div>
                        <div class="bk-an-dev-label">تبلت</div>
                        <div class="bk-an-dev-pct" data-bankai-text="(an.devices.tablet.pct || 0) + '%'">0%</div>
                        <div class="bk-an-dev-hits" data-bankai-text="formatAnNum(an.devices.tablet.hits)">0</div>
                    </div>
                </div>
                <div class="bk-an-bar">
                    <span class="bk-an-bar__seg is-desk" data-bankai-style="'width:' + (an.devices.desktop.pct || 0) + '%'"></span>
                    <span class="bk-an-bar__seg is-mob" data-bankai-style="'width:' + (an.devices.mobile.pct || 0) + '%'"></span>
                    <span class="bk-an-bar__seg is-tab" data-bankai-style="'width:' + (an.devices.tablet.pct || 0) + '%'"></span>
                </div>
                <div class="bk-an-legend">
                    <span><i class="is-desk"></i> دسکتاپ</span>
                    <span><i class="is-mob"></i> موبایل</span>
                    <span><i class="is-tab"></i> تبلت</span>
                </div>
            </div>

            <div class="bk-an-card bk-an-card--map">
                <div class="bk-an-card__head"><span>بازدید بر اساس کشور</span></div>
                <div class="bk-an-countries">
                    <template data-bankai-if="!an.countries.length">
                        <p class="bk-an-hint">پس از بازدید کاربران، کشورها اینجا نمایش داده می‌شوند.</p>
                    </template>
                    <template data-bankai-for="c in an.countries" data-bankai-key="c.code">
                        <div class="bk-an-co-row">
                            <span class="bk-an-co-code" data-bankai-text="c.code"></span>
                            <div class="bk-an-co-track"><span data-bankai-style="'width:' + countryPct(c.hits) + '%'"></span></div>
                            <span class="bk-an-co-hits" data-bankai-text="formatAnNum(c.hits)"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="bk-an-card bk-an-card--donut">
                <div class="bk-an-donut" data-bankai-style="donutStyle()">
                    <div class="bk-an-donut__inner">
                        <strong data-bankai-text="formatAnNum(an.visits)">0</strong>
                        <span>کل بازدید</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bk-an-card" style="margin-top:12px;">
            <div class="bk-an-card__head">
                <span>پربازدیدترین مطالب</span>
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-click="an._loaded=false; loadAnalyticsReport()" data-bankai-disabled="an.loading"
                        data-bankai-text="an.loading ? '…' : 'تازه‌سازی'"></button>
            </div>
            <table class="bk-seo-table" data-bankai-show="an.top_posts.length">
                <thead><tr><th>عنوان</th><th style="width:100px;text-align:center;">بازدید</th></tr></thead>
                <tbody>
                    <template data-bankai-for="p in an.top_posts" data-bankai-key="p.id">
                        <tr>
                            <td><a data-bankai-href="p.url" target="_blank" data-bankai-text="p.title"></a></td>
                            <td style="text-align:center;font-weight:700" data-bankai-text="formatAnNum(p.hits)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="bk-an-hint" data-bankai-show="!an.top_posts.length">هنوز بازدیدی ثبت نشده است.</p>
        </div>
    </div>

    <!-- Audit Banner -->
    <div class="bankai-card bk-seo-audit-card" data-bankai-show="seoPanel === 'tools'" data-bankai-cloak>
        <div class="bk-seo-audit-head">
            <div style="display:flex;align-items:center;gap:16px;">
                <div class="bk-score-ring-lg">
                    <svg viewBox="0 0 36 36">
                        <circle class="bk-track" cx="18" cy="18" r="14"></circle>
                        <circle class="bk-progress" cx="18" cy="18" r="14"
                            stroke-dasharray="87.96"
                            data-bankai-stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, (seoAudit && seoAudit.score) || 0)) / 100)"
                            data-bankai-style="{ stroke: scoreColor((seoAudit && seoAudit.score) || 0) }"></circle>
                    </svg>
                    <span class="bk-score-num" data-bankai-text="(seoAudit && seoAudit.running) ? '…' : ((seoAudit && seoAudit.score) || 0)">0</span>
                </div>
                <div>
                    <div class="bk-seo-site-hero" style="grid-column:1/-1;">
                    <div>
                        <div style="font-weight:800;font-size:18px;color:#0F172A;margin-bottom:6px;">وضعیت کل سئوی سایت</div>
                        <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;">ممیزی فنی، نقشه سایت، robots و اتصال ابزارهای وب‌مستر را از اینجا کنترل کنید.</p>
                    </div>
                </div>
                <div style="font-weight:800;font-size:16px;color:#0F172A;display:none;">وضعیت کل سئوی سایت</div>
                    <div style="font-size:12px;color:#64748B;margin-top:3px;" data-bankai-text="(seoAudit && seoAudit.running) ? 'در حال تحلیل بخش‌های مختلف…' : (((seoAudit && seoAudit.items) || []).filter(i => i.pass).length + ' از ' + (((seoAudit && seoAudit.items) || []).length) + ' استاندارد رعایت شده است.')">—</div>
                </div>
            </div>
            <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-click="runSeoAuditInline()" data-bankai-disabled="seoAudit && seoAudit.running">
                <span data-bankai-text="(seoAudit && seoAudit.running) ? 'در حال ممیزی…' : 'بررسی مجدد'"></span>
            </button>
        </div>
        <ul class="bk-seo-audit-list">
            <template data-bankai-for="item in (seoAudit && seoAudit.items) || []" data-bankai-key="item.key">
                <li data-bankai-class="item.pass ? 'is-pass' : 'is-fail'">
                    <span class="bk-audit-icon" data-bankai-text="item.pass ? '✓' : '!'"></span>
                    <div>
                        <strong data-bankai-text="item.label || item.label_en"></strong>
                        <a data-bankai-show="item.link" data-bankai-href="item.link" target="_blank" rel="noopener" class="bk-audit-link">مشاهده</a>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    <!-- SEO Tools Panel -->
    <div data-bankai-show="seoPanel === 'tools'" data-bankai-cloak>
        <h3 class="bk-section-title">ماژول‌های فعال سئو و اسکیما</h3>
        <div class="bk-seo-grid-3" style="margin-bottom:28px;">
            <?php foreach ($seo_mods as $mod):
                $mod_id   = esc_attr($mod['id'] ?? '');
                $title_fa = esc_html($mod['title_fa'] ?? $mod['title'] ?? '');
                $desc_fa  = esc_html($mod['description_fa'] ?? $mod['description'] ?? '');
                $badge    = esc_html($mod['badge'] ?? '');
                $badge_c  = esc_attr($mod['badge_color'] ?? '#0969DA');
                $icon     = $mod['icon'] ?? '⚡';
            ?>
            <div class="bankai-card bk-seo-mod-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;gap:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="bk-seo-mod-icon"><?php echo esc_html($icon); ?></div>
                            <div>
                                <div style="font-weight:700;font-size:13px;color:#0F172A;line-height:1.35;"><?php echo $title_fa; ?></div>
                                <span class="bk-seo-badge" style="background:<?php echo $badge_c; ?>1A;color:<?php echo $badge_c; ?>;"><?php echo $badge; ?></span>
                            </div>
                        </div>
                        <label class="bankai-switch">
                            <input type="checkbox"
                                   data-module="<?php echo $mod_id; ?>"
                                   data-bankai-model="seoState['<?php echo $mod_id; ?>']"
                                   data-bankai-change="toggleSeoModule('<?php echo $mod_id; ?>')">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <p style="font-size:12px;color:#64748B;line-height:1.6;margin:0 0 14px;"><?php echo $desc_fa; ?></p>
                </div>
                <div class="bk-seo-mod-foot">
                    <span style="font-size:11px;font-weight:700;" data-bankai-style="seoState['<?php echo $mod_id; ?>'] ? 'color:#16A34A' : 'color:#94A3B8'" data-bankai-text="seoState['<?php echo $mod_id; ?>'] ? 'فعال' : 'غیرفعال'"></span>
                    <button type="button" class="bk-seo-btn-sm" data-bankai-click="openSeoDrawer('<?php echo $mod_id; ?>', '<?php echo esc_js($title_fa); ?>')">تنظیمات</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Integrations -->
        <h3 class="bk-section-title">یکپارچه‌سازی و ابزارهای وب‌مستر</h3>
        <div class="bk-seo-grid-2" style="margin-bottom:28px;">
            <div class="bankai-card" style="padding:20px;">
                <div style="font-weight:800;font-size:14px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                    <span class="bk-seo-mod-icon" style="width:28px;height:28px;font-size:14px;">🔍</span>
                    گوگل و ابزارهای تحلیلی
                </div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label class="bk-seo-label">GA4 Measurement ID</label>
                        <input type="text" class="bk-seo-input" data-bankai-model="integEdit.ga4" placeholder="G-XXXXXXXX">
                    </div>
                    <div>
                        <label class="bk-seo-label">Google Tag Manager</label>
                        <input type="text" class="bk-seo-input" data-bankai-model="integEdit.gtm" placeholder="GTM-XXXX">
                    </div>
                    <div>
                        <label class="bk-seo-label">Google Site Verification</label>
                        <input type="text" class="bk-seo-input" data-bankai-model="integEdit.gsc" placeholder="کد متای گوگل سرچ کنسول">
                    </div>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="align-self:flex-start;"
                            data-bankai-click="saveIntegration({ _key:'google', google_analytics_id: integEdit.ga4, ga4_measurement_id: integEdit.ga4, google_tag_manager_id: integEdit.gtm, google_site_verification: integEdit.gsc })"
                            data-bankai-disabled="integSaving.google">
                        <span data-bankai-text="integSaving.google ? 'در حال ذخیره…' : 'ذخیره تنظیمات گوگل'"></span>
                    </button>
                </div>
            </div>

            <div class="bankai-card" style="padding:20px;">
                <div style="font-weight:800;font-size:14px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                    <span class="bk-seo-mod-icon" style="width:28px;height:28px;font-size:14px;">🌐</span>
                    موتورهای جستجو و Sitemap
                </div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label class="bk-seo-label">Bing Webmaster</label>
                        <input type="text" class="bk-seo-input" data-bankai-model="integEdit.bing">
                    </div>
                    <div>
                        <label class="bk-seo-label">Yandex Verification</label>
                        <input type="text" class="bk-seo-input" data-bankai-model="integEdit.yandex">
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;cursor:pointer;margin-top:4px;">
                        <input type="checkbox" data-bankai-model="integEdit.sitemap" style="accent-color:#0969DA;">
                        فعال‌سازی نقشه سایت دینامیک (Sitemap XML)
                    </label>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="align-self:flex-start;"
                            data-bankai-click="saveIntegration({ _key:'other', bing_webmaster: integEdit.bing, yandex_verification: integEdit.yandex, sitemap_enabled: !!integEdit.sitemap })"
                            data-bankai-disabled="integSaving.other">
                        <span data-bankai-text="integSaving.other ? 'در حال ذخیره…' : 'ذخیره تنظیمات'"></span>
                    </button>
                </div>
            </div>

            <div class="bankai-card" style="padding:20px;grid-column:1 / -1;">
                <div style="font-weight:800;font-size:14px;margin-bottom:10px;">فایل robots.txt</div>
                <textarea class="bk-seo-input mono" rows="4" data-bankai-model="integEdit.robots" placeholder="User-agent: *&#10;Allow: /"></textarea>
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="margin-top:10px;"
                        data-bankai-click="saveIntegration({ _key:'robots', robots_txt: integEdit.robots })"
                        data-bankai-disabled="integSaving.robots">
                    <span data-bankai-text="integSaving.robots ? 'در حال ذخیره…' : 'ذخیره robots.txt'"></span>
                </button>
            </div>
        </div>

        <!-- Fixed Keywords Section -->
        <div class="bk-fk-section bankai-card">
            <div class="bk-fk-head">
                <div>
                    <h3 class="bk-fk-title">کلمات کلیدی ثابت و سراسری سایت</h3>
                    <p class="bk-fk-desc">این کلیدواژه‌ها به‌صورت خودکار پس از کلمات کلیدی هر مقاله چسبانده می‌شوند و در متای تمامی مطالب اعمال می‌گردند.</p>
                </div>
                <span class="bk-fk-count" data-bankai-text="(fixedList.length || 0) + ' کلمه'"></span>
            </div>
            <div class="bk-fk-tags" data-bankai-show="fixedList.length">
                <template data-bankai-for="(kw, i) in fixedList" data-bankai-key="'fk'+i">
                    <span class="bk-fk-chip">
                        <span data-bankai-text="kw"></span>
                        <button type="button" class="bk-fk-remove" title="حذف" data-bankai-click="removeFixedKeyword(i)">&times;</button>
                    </span>
                </template>
            </div>
            <div class="bk-fk-empty" data-bankai-show="!fixedList.length">هنوز کلمه کلیدی ثابتی تعریف نشده است.</div>
            <div class="bk-fk-add-row">
                <input type="text" class="bk-seo-input bk-fk-input" data-bankai-model="fixedKeywordInput"
                       @keydown.enter.prevent="addFixedKeyword()"
                       placeholder="کلمه جدید را وارد کرده و Enter بزنید…">
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" data-bankai-click="addFixedKeyword()">افزودن</button>
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-click="saveFixedKeywords()" data-bankai-disabled="savingFixed">
                    <span data-bankai-text="savingFixed ? 'ذخیره…' : 'ذخیره کلیدواژه‌ها'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Google Analytics Panel (real settings) -->
    <!-- Google Analytics connection tab -->

    <!-- Sync Wizard Rank Math / Yoast — centered flow -->
    <div data-bankai-show="seoPanel === 'import'" data-bankai-cloak class="bk-sync-wizard">
        <div class="bk-sync-shell">
            <div class="bk-sync-hero">
                <h3>همگام‌سازی سئو با Rank Math / Yoast</h3>
                <p>ابتدا افزونه مبدأ/مقصد را انتخاب کنید، سپس درون‌ریزی یا برون‌ریزی را شروع کنید.</p>
            </div>

            <!-- Step 1: provider -->
            <div data-bankai-show="seoImport.phase === 'pick' || !seoImport.selectedSource" class="bk-sync-step">
                <div class="bk-sync-step__label">۱ — انتخاب افزونه</div>
                <div class="bk-sync-providers bk-sync-providers--center">
                    <button type="button" class="bk-sync-provider" data-bankai-class="{ 'is-on': seoImport.sources.rankmath && seoImport.sources.rankmath.active, 'is-selected': seoImport.selectedSource === 'rankmath' }"
                        data-bankai-click="seoImport.selectedSource = 'rankmath'; seoImport.direction = ''; seoImport.phase = 'direction'"
                        >
                        <span class="bk-sync-provider__badge" data-bankai-class="(seoImport.sources.rankmath && (seoImport.sources.rankmath.plugin_active || seoImport.sources.rankmath.posts)) ? 'is-ok' : 'is-off'"
                              data-bankai-text="(seoImport.sources.rankmath && (seoImport.sources.rankmath.plugin_active || seoImport.sources.rankmath.posts)) ? '✓ فعال' : 'آماده انتخاب'"></span>
                        <div class="bk-sync-provider__logo bk-sync-provider__logo--rm">RM</div>
                        <strong>Rank Math</strong>
                        <small data-bankai-text="(seoImport.sources.rankmath && seoImport.sources.rankmath.posts) ? (seoImport.sources.rankmath.posts + ' مقاله دارای متا') : 'افزونه شناسایی نشد'"></small>
                    </button>
                    <button type="button" class="bk-sync-provider" data-bankai-class="{ 'is-on': seoImport.sources.yoast && seoImport.sources.yoast.active, 'is-selected': seoImport.selectedSource === 'yoast' }"
                        data-bankai-click="seoImport.selectedSource = 'yoast'; seoImport.direction = ''; seoImport.phase = 'direction'"
                        >
                        <span class="bk-sync-provider__badge" data-bankai-class="(seoImport.sources.yoast && (seoImport.sources.yoast.plugin_active || seoImport.sources.yoast.posts)) ? 'is-ok' : 'is-off'"
                              data-bankai-text="(seoImport.sources.yoast && (seoImport.sources.yoast.plugin_active || seoImport.sources.yoast.posts)) ? '✓ فعال' : 'آماده انتخاب'"></span>
                        <div class="bk-sync-provider__logo bk-sync-provider__logo--yo">Y</div>
                        <strong>Yoast SEO</strong>
                        <small data-bankai-text="(seoImport.sources.yoast && seoImport.sources.yoast.posts) ? (seoImport.sources.yoast.posts + ' مقاله دارای متا') : 'افزونه شناسایی نشد'"></small>
                    </button>
                </div>
            </div>

            <!-- Step 2: import / export -->
            <div data-bankai-show="seoImport.phase === 'direction' && seoImport.selectedSource" class="bk-sync-step" data-bankai-cloak>
                <div class="bk-sync-step__label">۲ — جهت همگام‌سازی
                    <button type="button" class="bk-sync-back" data-bankai-click="seoImport.phase = 'pick'; seoImport.selectedSource = ''">← تغییر افزونه</button>
                </div>
                <div class="bk-sync-dir-row">
                    <button type="button" class="bk-sync-dir-card" data-bankai-click="seoImport.direction = 'import'; selectSeoSource(seoImport.selectedSource)">
                        <div class="bk-sync-dir-card__art bk-sync-dir-card__art--in" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </div>
                        <strong>درون‌ریزی</strong>
                        <small data-bankai-text="(seoImport.selectedSource === 'yoast' ? 'Yoast' : 'Rank Math') + ' → Bankai'"></small>
                        <span class="bk-sync-dir-card__cta">شروع استخراج</span>
                    </button>
                    <button type="button" class="bk-sync-dir-card" data-bankai-click="seoImport.direction = 'export'; selectSeoSource(seoImport.selectedSource)">
                        <div class="bk-sync-dir-card__art bk-sync-dir-card__art--out" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </div>
                        <strong>برون‌ریزی</strong>
                        <small data-bankai-text="'Bankai → ' + (seoImport.selectedSource === 'yoast' ? 'Yoast' : 'Rank Math')"></small>
                        <span class="bk-sync-dir-card__cta">شروع ارسال</span>
                    </button>
                </div>
                <label class="bk-sync-overwrite">
                    <input type="checkbox" data-bankai-model="seoImport.overwrite">
                    در صورت وجود، فیلدهای قبلی بازنویسی شوند
                </label>
            </div>

            <!-- Progress + cards -->
            <div data-bankai-show="seoImport.phase === 'list' || seoImport.phase === 'run' || seoImport.phase === 'done'" class="bk-sync-run" data-bankai-cloak>
                <div class="bk-sync-loader" data-bankai-show="seoImport.running">
                    <div class="bk-sync-spinner"></div>
                    <div>
                        <strong data-bankai-text="seoImport.stepText || 'در حال پردازش…'"></strong>
                        <div class="bk-sync-bar" style="margin-top:10px;"><div class="bk-sync-bar__fill" data-bankai-style="'width:' + (seoImport.progress || 0) + '%'"></div></div>
                        <small data-bankai-text="(seoImport.processed || 0) + ' / ' + (seoImport.queueTotal || 0) + ' — موفق ' + (seoImport.importedCount || 0) + ' | رد شده ' + (seoImport.skippedCount || 0)"></small>
                    </div>
                </div>
                <div class="bk-sync-cards">
                    <template data-bankai-for="card in seoImport.cards" data-bankai-key="card.id">
                        <div class="bk-sync-card" data-bankai-class="'is-' + (card.status || 'pending')">
                            <div class="bk-sync-card__head">
                                <div>
                                    <strong data-bankai-text="card.title"></strong>
                                    <div class="bk-sync-card__meta" data-bankai-text="'#' + card.id + (card.type ? ' · ' + card.type : '')"></div>
                                </div>
                                <span class="bk-sync-card__status" data-bankai-text="card.statusLabel || 'در صف'"></span>
                            </div>
                            <div class="bk-sync-bar bk-sync-bar--sm" data-bankai-show="card.status === 'running' || card.progress > 0">
                                <div class="bk-sync-bar__fill" data-bankai-style="'width:' + (card.progress || 0) + '%'"></div>
                            </div>
                            <ul class="bk-sync-check" data-bankai-show="card.checklist && card.checklist.length">
                                <template data-bankai-for="item in card.checklist" data-bankai-key="item.id">
                                    <li data-bankai-class="'is-' + item.status">
                                        <span data-bankai-text="item.status === 'done' ? '✓' : (item.status === 'skip' ? '–' : '…')"></span>
                                        <span data-bankai-text="item.label"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
                <div class="bk-sync-done" data-bankai-show="seoImport.done || seoImport.phase === 'done'">
                    <div class="bk-sync-done__ico">✓</div>
                    <strong data-bankai-text="seoImport.doneTitle || 'همگام‌سازی کامل شد'"></strong>
                    <p data-bankai-text="seoImport.doneMessage || ''"></p>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" data-bankai-click="seoImport.phase='pick'; seoImport.selectedSource=''; seoImport.direction=''; seoImport.done=false; seoImport.cards=[]; seoImport.progress=0;">شروع مجدد</button>
                </div>
            </div>
        </div>

        
    </div>

<div data-bankai-show="seoPanel === 'analytics'" data-bankai-cloak class="bk-ga-panel">
        <div class="bk-conn-hero bankai-card">
            <div class="bk-conn-hero__icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/></svg>
            </div>
            <div class="bk-conn-hero__text">
                <h3>اتصال Google Analytics 4</h3>
                <p>شناسه اندازه‌گیری را وارد کنید تا gtag فقط با ID معتبر در سایت تزریق شود. گزارش بازدید در تب «ماژول‌های سئو» از آمار خود بنکای است.</p>
            </div>
            <span class="bk-seo-chip" data-bankai-class="integEdit.ga4 ? 'is-ok' : 'is-warn'"
                  data-bankai-text="integEdit.ga4 ? 'متصل' : 'قطع'"></span>
        </div>

        <div class="bk-conn-grid">
            <div class="bk-conn-card bankai-card">
                <div class="bk-conn-card__head">
                    <strong>GA4 Measurement ID</strong>
                    <a class="bk-guide-link" href="https://support.google.com/analytics/answer/9304153" target="_blank" rel="noopener">راهنمای دریافت ID ↗</a>
                </div>
                <input type="text" class="bk-seo-input" data-bankai-model="integEdit.ga4" placeholder="G-XXXXXXXXXX" dir="ltr">
                <p class="bk-fk-desc">فرمت معتبر: G- و سپس حروف/عدد</p>
            </div>
            <div class="bk-conn-card bankai-card">
                <div class="bk-conn-card__head">
                    <strong>Google Tag Manager</strong>
                    <a class="bk-guide-link" href="https://support.google.com/tagmanager/answer/6103696" target="_blank" rel="noopener">راهنمای GTM ↗</a>
                </div>
                <input type="text" class="bk-seo-input" data-bankai-model="integEdit.gtm" placeholder="GTM-XXXXXXX" dir="ltr">
            </div>
            <div class="bk-conn-card bankai-card">
                <div class="bk-conn-card__head">
                    <strong>Search Console</strong>
                    <a class="bk-guide-link" href="https://search.google.com/search-console" target="_blank" rel="noopener">کنسول جستجو ↗</a>
                </div>
                <input type="text" class="bk-seo-input" data-bankai-model="integEdit.gsc" placeholder="کد تأیید meta" dir="ltr">
            </div>
        </div>

        <div class="bk-conn-actions">
            <button type="button" class="bk-seo-btn bk-seo-btn-primary"
                data-bankai-click="saveIntegration({ _key:'google', google_analytics_id: integEdit.ga4, ga4_measurement_id: integEdit.ga4, google_tag_manager_id: integEdit.gtm, google_site_verification: integEdit.gsc })"
                data-bankai-disabled="integSaving && integSaving.google">
                <span data-bankai-text="(integSaving && integSaving.google) ? 'در حال ذخیره…' : 'ذخیره اتصال'"></span>
            </button>
            <a class="bk-seo-btn bk-seo-btn-ghost" href="https://analytics.google.com/" target="_blank" rel="noopener">باز کردن Analytics ↗</a>
        </div>

        <div class="bk-conn-status bankai-card">
            <h4>وضعیت</h4>
            <ul>
                <li><span class="bk-dot" data-bankai-class="integEdit.ga4 ? 'ok' : 'off'"></span> GA4: <strong dir="ltr" data-bankai-text="integEdit.ga4 || '—'"></strong></li>
                <li><span class="bk-dot" data-bankai-class="integEdit.gtm ? 'ok' : 'off'"></span> GTM: <strong dir="ltr" data-bankai-text="integEdit.gtm || '—'"></strong></li>
                <li><span class="bk-dot" data-bankai-class="integEdit.gsc ? 'ok' : 'off'"></span> Search Console: <strong data-bankai-text="integEdit.gsc ? 'تنظیم شده' : 'تنظیم نشده'"></strong></li>
            </ul>
        </div>
    </div>

    <!-- Articles Section -->
    <div id="bk-seo-articles-section" data-bankai-show="false" data-bankai-cloak>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:12px;">
            <h3 class="bk-section-title" style="margin:0;">جدول ممیزی سئوی مقالات</h3>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select data-bankai-model="perPage" data-bankai-change="page=1;loadArticles()" class="bk-seo-input" style="width:auto;">
                    <option value="10">۱۰ عدد</option>
                    <option value="25">۲۵ عدد</option>
                    <option value="50">۵۰ عدد</option>
                </select>
                <select data-bankai-model="orderby" data-bankai-change="page=1;loadArticles()" class="bk-seo-input" style="width:auto;">
                    <option value="modified">آخرین ویرایش</option>
                    <option value="date">تاریخ انتشار</option>
                    <option value="seo_score">امتیاز سئو</option>
                    <option value="views">تعداد بازدید</option>
                    <option value="title">عنوان</option>
                </select>
                <select data-bankai-model="order" data-bankai-change="page=1;loadArticles()" class="bk-seo-input" style="width:auto;">
                    <option value="DESC">نزولی</option>
                    <option value="ASC">صعودی</option>
                </select>
                <input type="search" data-bankai-model="searchQ" @keydown.enter.prevent="page=1;loadArticles()"
                       placeholder="جستجوی مقاله…" class="bk-seo-input" style="width:180px;">
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" data-bankai-click="page=1;loadArticles()" data-bankai-disabled="loading">
                    <span data-bankai-text="loading ? '…' : 'جستجو'"></span>
                </button>
            </div>
        </div>

        <div class="bankai-card" style="padding:0;overflow:hidden;margin-bottom:20px;">
            <div style="overflow-x:auto;">
                <table class="bk-seo-table">
                    <thead>
                        <tr>
                            <th style="width:56px;">کاور</th>
                            <th>عنوان و متاداده‌ها</th>
                            <th style="width:72px;text-align:center;">امتیاز</th>
                            <th style="width:70px;text-align:center;">بازدید</th>
                            <th style="width:70px;text-align:center;">داخلی</th>
                            <th style="width:70px;text-align:center;">خارجی</th>
                            <th style="width:160px;">کلمات کلیدی</th>
                            <th style="width:130px;text-align:center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-bankai-show="!loading && articles.length === 0">
                            <td colspan="8" style="text-align:center;padding:32px;color:#94A3B8;">مقاله‌ای یافت نشد.</td>
                        </tr>
                        <template data-bankai-for="row in articles" data-bankai-key="row.id">
                            <tr>
                                <td>
                                    <div class="bk-seo-cover">
                                        <img data-bankai-show="row.cover" data-bankai-src="row.cover" alt="" width="44" height="44">
                                        <span data-bankai-show="!row.cover">🖼</span>
                                    </div>
                                </td>
                                <td style="max-width:320px;">
                                    <div style="font-weight:700;font-size:13px;color:#0F172A;" data-bankai-text="row.title"></div>
                                    <div style="font-size:11px;color:#64748B;margin-top:2px;" data-bankai-text="row.seo_title || '— بدون عنوان سئو —'"></div>
                                    <div style="font-size:11px;color:#94A3B8;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                         data-bankai-text="row.description || '— بدون توضیحات متا —'"></div>
                                </td>
                                <td>
                                    <div class="bk-score-ring-sm">
                                        <svg viewBox="0 0 36 36">
                                            <circle class="bk-track" cx="18" cy="18" r="14"></circle>
                                            <circle class="bk-progress" cx="18" cy="18" r="14"
                                                stroke-dasharray="87.96"
                                                data-bankai-stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, row.score || 0)) / 100)"
                                                data-bankai-style="{ stroke: scoreColor(row.score) }"></circle>
                                        </svg>
                                        <span class="bk-score-num" data-bankai-text="row.score || 0">0</span>
                                    </div>
                                </td>
                                <td style="text-align:center;font-weight:700;" data-bankai-text="row.views || 0"></td>
                                <td style="text-align:center;font-weight:700;" data-bankai-text="row.internal_links"></td>
                                <td style="text-align:center;font-weight:700;" data-bankai-text="row.external_links"></td>
                                <td>
                                    <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                        <span data-bankai-show="row.focus_keyword" class="bk-seo-tag focus" data-bankai-text="row.focus_keyword"></span>
                                        <template data-bankai-for="(k, ki) in (row.keywords || []).slice(0, 2)" data-bankai-key="ki">
                                            <span class="bk-seo-tag muted" data-bankai-text="k"></span>
                                        </template>
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex;flex-direction:column;gap:4px;align-items:center;">
                                        <button type="button" class="bk-seo-btn-sm" data-bankai-click="openEdit(row)">ویرایش متا</button>
                                        <a data-bankai-href="row.edit_url" target="_blank" rel="noopener" class="bk-seo-link">ویرایش مطلب ↗</a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <div class="bk-seo-pager" data-bankai-show="totalPages > 1">
                <button type="button" class="bk-seo-btn-sm" data-bankai-disabled="page <= 1" data-bankai-click="page--; loadArticles()">صفحه قبل</button>
                <span data-bankai-text="page + ' از ' + totalPages + ' (کل: ' + total + ' مقاله)'"></span>
                <button type="button" class="bk-seo-btn-sm" data-bankai-disabled="page >= totalPages" data-bankai-click="page++; loadArticles()">صفحه بعد</button>
            </div>
        </div>
    </div>

    <!-- Edit Meta Modal -->
    <div data-bankai-show="edit.open" data-bankai-cloak class="bk-seo-modal-bg" data-bankai-click-self="edit.open = false">
        <div class="bankai-card bk-seo-modal" @click.stop>
            <div class="bk-seo-modal-head">
                <strong data-bankai-text="'ویرایش متای: ' + (edit.row?.title || '')"></strong>
                <button type="button" class="bk-seo-modal-x" data-bankai-click="edit.open = false">&times;</button>
            </div>
            <div class="bk-seo-modal-body">
                <label class="bk-seo-label">عنوان سئو</label>
                <input type="text" class="bk-seo-input" data-bankai-model="edit.seo_title" maxlength="70">
                
                <label class="bk-seo-label">متا دیسکریپشن</label>
                <textarea class="bk-seo-input" data-bankai-model="edit.description" rows="3" maxlength="170"></textarea>
                
                <label class="bk-seo-label">کلمه کلیدی اصلی</label>
                <input type="text" class="bk-seo-input" data-bankai-model="edit.focus_keyword">
                
                <label class="bk-seo-label">کلمات کلیدی جانبی (با ویرگول جدا کنید)</label>
                <input type="text" class="bk-seo-input" data-bankai-model="edit.keywords_str">
            </div>
            <div class="bk-seo-modal-foot">
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-click="edit.open = false">انصراف</button>
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" data-bankai-click="saveEdit()" data-bankai-disabled="edit.saving">
                    <span data-bankai-text="edit.saving ? 'در حال ذخیره…' : 'ذخیره تغییرات'"></span>
                </button>
            </div>
        </div>
    </div>

<!-- Site-wide SEO -->
    <div data-bankai-show="seoPanel === 'sitewide'" class="bk-sitewide-panel" style="display:none" data-bankai-style="seoPanel === 'sitewide' ? 'display:block' : 'display:none'">

                <div class="bk-section-hero">
            <h2>سئوی سایت</h2>
            <p>سازمان، آرشیوها، noindex، نقشه سایت، hreflang و اتصال Search Console — همه در یک نمای مدرن.</p>
        </div>
        <h3 class="bk-section-title">سئوی سراسری سایت</h3>

        <div class="bk-site-conn-strip">
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico">🗺</div>
                <div>
                    <strong>نقشه سایت XML</strong>
                    <p>ایندکس خودکار پست و برگه</p>
                    <a class="bk-guide-link" data-bankai-href="sw.sitemapIndex || '#'" target="_blank" rel="noopener">مشاهده sitemap ↗</a>
                </div>
            </div>
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico">🤖</div>
                <div>
                    <strong>robots.txt</strong>
                    <p>مدیریت خزنده‌ها</p>
                    <a class="bk-guide-link" href="https://developers.google.com/search/docs/crawling-indexing/robots/intro" target="_blank" rel="noopener">راهنمای Google ↗</a>
                </div>
            </div>
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico">🔗</div>
                <div>
                    <strong>ریدایرکت و ۴۰۴</strong>
                    <p>حفظ رتبه پس از تغییر URL</p>
                    <a class="bk-guide-link" href="https://developers.google.com/search/docs/crawling-indexing/301-redirects" target="_blank" rel="noopener">راهنمای ریدایرکت ↗</a>
                </div>
            </div>
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico">🏢</div>
                <div>
                    <strong>Organization Schema</strong>
                    <p>هویت برند در نتایج</p>
                    <a class="bk-guide-link" href="https://schema.org/Organization" target="_blank" rel="noopener">schema.org ↗</a>
                </div>
            </div>
        </div>

        <p class="bk-fk-desc" style="margin-bottom:16px">پیش‌نمایش واقعی سایت + سازمان، آرشیوها، ریدایرکت، ۴۰۴، نقشه سایت و hreflang.</p>

        <!-- Visual hero: tablet mockup + live metrics -->
        <div class="bk-site-hero">
            <div class="bk-tablet-mock" aria-hidden="false">
                <div class="bk-tablet-frame">
                    <div class="bk-tablet-btn bk-tablet-btn--1"></div>
                    <div class="bk-tablet-btn bk-tablet-btn--2"></div>
                    <div class="bk-tablet-btn bk-tablet-btn--3"></div>
                    <div class="bk-tablet-btn bk-tablet-btn--4"></div>
                    <div class="bk-tablet-screen">
                        <div class="bk-tablet-chrome">
                            <span class="bk-tablet-dot"></span><span class="bk-tablet-dot"></span><span class="bk-tablet-dot"></span>
                            <span class="bk-tablet-url" dir="ltr"><?php echo esc_html(wp_parse_url($site_url, PHP_URL_HOST) ?: $site_url); ?></span>
                        </div>
                        <iframe class="bk-tablet-iframe" src="<?php echo esc_url($site_url); ?>" title="<?php echo esc_attr($site_name); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <div class="bk-tablet-overlay">
                            <div class="bk-tablet-site">
                                <?php if ($site_icon) : ?>
                                    <img src="<?php echo esc_url($site_icon); ?>" alt="" width="40" height="40" class="bk-tablet-favicon">
                                <?php endif; ?>
                                <div>
                                    <strong data-bankai-text="sw.settings.org_name || '<?php echo esc_js($site_name); ?>'"><?php echo esc_html($site_name); ?></strong>
                                    <p data-bankai-text="sw.settings.org_logo ? 'لوگو تنظیم شده' : '<?php echo esc_js(mb_substr($site_desc, 0, 80)); ?>'"><?php echo esc_html(mb_substr($site_desc, 0, 80)); ?></p>
                                </div>
                            </div>
                            <div class="bk-tablet-score">
                                <span class="bk-tablet-score__num" data-bankai-text="(seoAudit && seoAudit.score) || 0">0</span>
                                <span class="bk-tablet-score__lab">امتیاز سئو</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bk-site-metrics">
                <div class="bk-metric-card">
                    <div class="bk-metric-card__ico" style="background:#EEF2FF;color:#4F46E5">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V5M4 19h16M8 17V9M12 17V7M16 17v-4"/></svg>
                    </div>
                    <div>
                        <div class="bk-metric-card__val" data-bankai-text="(seoAudit && seoAudit.score) || 0">0</div>
                        <div class="bk-metric-card__lab">امتیاز ممیزی</div>
                    </div>
                </div>
                <div class="bk-metric-card">
                    <div class="bk-metric-card__ico" style="background:#ECFDF5;color:#059669">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 15h6M9 11h6"/></svg>
                    </div>
                    <div>
                        <div class="bk-metric-card__val"><?php echo (int) $posts_count; ?></div>
                        <div class="bk-metric-card__lab">مقالات منتشرشده</div>
                    </div>
                </div>
                <div class="bk-metric-card">
                    <div class="bk-metric-card__ico" style="background:#FFF7ED;color:#EA580C">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    </div>
                    <div>
                        <div class="bk-metric-card__val"><?php echo (int) $page_count; ?></div>
                        <div class="bk-metric-card__lab">برگه‌ها</div>
                    </div>
                </div>
                <div class="bk-metric-card">
                    <div class="bk-metric-card__ico" style="background:#F0F9FF;color:#0284C7">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20"/></svg>
                    </div>
                    <div>
                        <div class="bk-metric-card__val" dir="ltr" style="font-size:13px;font-weight:700" data-bankai-text="(sw.sitemapIndex || '').replace(/^https?:\/\//,'').slice(0,28) || 'sitemap'">sitemap</div>
                        <div class="bk-metric-card__lab">نقشه سایت</div>
                    </div>
                </div>
                <div class="bk-metric-card bk-metric-card--wide">
                    <div class="bk-metric-card__ico" style="background:#FEF2F2;color:#DC2626">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    </div>
                    <div>
                        <div class="bk-metric-card__val" data-bankai-text="(sw.log404 && sw.log404.length) || 0">0</div>
                        <div class="bk-metric-card__lab">مسیرهای ۴۰۴ ثبت‌شده</div>
                    </div>
                </div>
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="width:100%" data-bankai-click="runSeoAuditInline()" data-bankai-disabled="seoAudit && seoAudit.running">
                    <span data-bankai-text="(seoAudit && seoAudit.running) ? 'در حال ممیزی…' : 'اجرای ممیزی سئوی سایت'"></span>
                </button>
            </div>
        </div>

        <!-- Visual noindex toggles -->
        <div class="bk-noindex-panel bankai-card" style="padding:18px;margin-bottom:16px;border-radius:16px;border:1px solid #E2E8F0;background:linear-gradient(180deg,#fff,#F8FAFC);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                <div>
                    <h4 style="margin:0 0 6px;font-size:15px;font-weight:900;color:#0F172A;">noindex آرشیوها</h4>
                    <p style="margin:0;font-size:12px;color:#64748B;line-height:1.55;">صفحات آرشیوی که نباید در گوگل ایندکس شوند را روشن کنید. هر کارت فعال = <code>noindex</code>.</p>
                </div>
                <span class="bk-seo-chip is-ok" style="font-size:11px;">robots</span>
            </div>
            <div class="bk-vtoggle-grid">
                <label class="bk-vtoggle" data-bankai-class="{ 'is-on': !!sw.settings.noindex_author }"><input type="checkbox" data-bankai-model="sw.settings.noindex_author" data-bankai-change="saveSitewide()"><span class="bk-vtoggle__art bk-vtoggle__art--user" aria-hidden="true"></span><span>نویسنده</span><small>Author archive</small></label>
                <label class="bk-vtoggle" data-bankai-class="{ 'is-on': !!sw.settings.noindex_date }"><input type="checkbox" data-bankai-model="sw.settings.noindex_date" data-bankai-change="saveSitewide()"><span class="bk-vtoggle__art bk-vtoggle__art--date" aria-hidden="true"></span><span>تاریخ</span><small>Date archive</small></label>
                <label class="bk-vtoggle" data-bankai-class="{ 'is-on': !!sw.settings.noindex_tag }"><input type="checkbox" data-bankai-model="sw.settings.noindex_tag" data-bankai-change="saveSitewide()"><span class="bk-vtoggle__art bk-vtoggle__art--tag" aria-hidden="true"></span><span>برچسب</span><small>Tag archive</small></label>
                <label class="bk-vtoggle" data-bankai-class="{ 'is-on': !!sw.settings.noindex_search }"><input type="checkbox" data-bankai-model="sw.settings.noindex_search" data-bankai-change="saveSitewide()"><span class="bk-vtoggle__art bk-vtoggle__art--search" aria-hidden="true"></span><span>جستجو</span><small>Search results</small></label>
                <label class="bk-vtoggle" data-bankai-class="{ 'is-on': !!sw.settings.noindex_attach }"><input type="checkbox" data-bankai-model="sw.settings.noindex_attach" data-bankai-change="saveSitewide()"><span class="bk-vtoggle__art bk-vtoggle__art--attach" aria-hidden="true"></span><span>پیوست</span><small>Attachments</small></label>
            </div>
        </div>

<div class="bk-seo-site-grid">
        <div class="bankai-card bk-seo-color-card bk-seo-color-indigo" style="padding:16px;margin-bottom:0;">
            <h4 style="margin:0 0 12px;font-size:13px;font-weight:800;color:#312e81;">Organization / WebSite Schema · PWA</h4>
            <div style="width:100%;">
            <div class="bk-field"><label>نام سازمان</label><input type="text" data-bankai-model="sw.settings.org_name"></div>
            <div class="bk-field">
                <label>لوگو سازمان</label>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <div data-bankai-show="sw.settings.org_logo" style="width:64px;height:64px;border-radius:12px;overflow:hidden;border:1px solid #E2E8F0;background:#F8FAFC;">
                        <img data-bankai-src="sw.settings.org_logo" alt="" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    <div style="flex:1;min-width:180px;">
                        <input type="url" dir="ltr" data-bankai-model="sw.settings.org_logo" placeholder="https://…/logo.png" style="width:100%;margin-bottom:8px;">
                        <div style="display:flex;gap:8px;">
                            <button type="button" class="bk-seo-btn bk-seo-btn-primary" data-bankai-click="pickOrgLogo()">انتخاب از رسانه</button>
                            <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-show="sw.settings.org_logo" data-bankai-click="sw.settings.org_logo=''">حذف</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bk-field"><label>sameAs (هر خط یک شبکه اجتماعی)</label><textarea rows="3" dir="ltr" data-bankai-model="sw.settings.org_same_as"></textarea></div>
            
            <label class="bk-switch-row"><span>SearchAction در اسکیمای سایت</span><input type="checkbox" data-bankai-model="sw.settings.website_search"></label>
        </div>

        <!-- PWA full-width card -->
        <div class="bankai-card" style="padding:20px;margin:16px 0;grid-column:1/-1;width:100%;" data-bankai-show="moduleActive.pwa !== false" data-bankai-cloak>
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                <div>
                    <h4 style="margin:0 0 6px;font-size:14px;font-weight:800;color:#312e81;">PWA — اپلیکیشن وب پیشرفته</h4>
                    <p style="margin:0;font-size:12px;color:#64748b;line-height:1.6">مانیفست، آیکون‌ها، رنگ تم، Service Worker و حالت نمایش. ماژول PWA را از پیشخوان فعال کنید.</p>
                </div>
                <label class="bk-switch-row" style="margin:0">
                    <span style="font-size:12px;font-weight:700">فعال‌سازی PWA</span>
                    <input type="checkbox" data-bankai-model="sw.settings.pwa_enabled">
                </label>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;" data-bankai-show="sw.settings.pwa_enabled">
                <div class="bk-field"><label>نام اپ (Name)</label><input type="text" data-bankai-model="sw.settings.pwa_name" :placeholder="sw.settings.org_name || ''"></div>
                <div class="bk-field"><label>نام کوتاه (Short name)</label><input type="text" data-bankai-model="sw.settings.pwa_short_name" maxlength="12"></div>
                <div class="bk-field"><label>توضیح</label><input type="text" data-bankai-model="sw.settings.pwa_description"></div>
                <div class="bk-field"><label>شروع (Start URL)</label><input type="text" dir="ltr" data-bankai-model="sw.settings.pwa_start_url" placeholder="/"></div>
                <div class="bk-field"><label>Scope</label><input type="text" dir="ltr" data-bankai-model="sw.settings.pwa_scope" placeholder="/"></div>
                <div class="bk-field"><label>حالت نمایش</label>
                    <select data-bankai-model="sw.settings.pwa_display">
                        <option value="standalone">Standalone</option>
                        <option value="fullscreen">Fullscreen</option>
                        <option value="minimal-ui">Minimal UI</option>
                        <option value="browser">Browser</option>
                    </select>
                </div>
                <div class="bk-field"><label>جهت صفحه</label>
                    <select data-bankai-model="sw.settings.pwa_orientation">
                        <option value="any">Any</option>
                        <option value="portrait">Portrait</option>
                        <option value="landscape">Landscape</option>
                    </select>
                </div>
                <div class="bk-field"><label>رنگ تم (Theme)</label><input type="color" data-bankai-model="sw.settings.pwa_theme_color"></div>
                <div class="bk-field"><label>رنگ پس‌زمینه</label><input type="color" data-bankai-model="sw.settings.pwa_bg_color"></div>
                <div class="bk-field" style="grid-column:1/-1"><label>آیکون ۱۹۲×۱۹۲</label>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                        <input type="url" dir="ltr" data-bankai-model="sw.settings.pwa_icon_192" placeholder="https://…/icon-192.png" style="flex:1;min-width:180px">
                        <button type="button" class="bk-seo-btn bk-seo-btn-primary" data-bankai-click="pickPwaIcon('192')">رسانه</button>
                    </div>
                </div>
                <div class="bk-field" style="grid-column:1/-1"><label>آیکون ۵۱۲×۵۱۲</label>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                        <input type="url" dir="ltr" data-bankai-model="sw.settings.pwa_icon_512" placeholder="https://…/icon-512.png" style="flex:1;min-width:180px">
                        <button type="button" class="bk-seo-btn bk-seo-btn-primary" data-bankai-click="pickPwaIcon('512')">رسانه</button>
                    </div>
                </div>
                <label class="bk-switch-row"><span>Service Worker (کش آفلاین پایه)</span><input type="checkbox" data-bankai-model="sw.settings.pwa_sw"></label>
                <label class="bk-switch-row"><span>نمایش دکمه «نصب اپ» در فرانت</span><input type="checkbox" data-bankai-model="sw.settings.pwa_install_prompt"></label>
            </div>
            <p style="margin:12px 0 0;font-size:11px;color:#94a3b8" data-bankai-show="sw.settings.pwa_enabled">مانیفست در <code dir="ltr">/manifest.webmanifest</code> تولید می‌شود. برای HTTPS و آیکون‌های مربع PNG الزامی است.</p>
        </div>

        <div class="bk-seo-site-grid-rest" style="display:none">
        </div>
</div>

        <!-- /.bk-seo-site-grid -->

        
        <div class="bk-data-panel bankai-card" style="padding:0;margin-bottom:16px;overflow:hidden;border-radius:16px;border:1px solid #E2E8F0;">
            <div class="bk-data-panel__head">
                <div>
                    <h4>ریدایرکت سراسری</h4>
                    <p>قوانین ۳۰۱ / ۳۰۲ / ۴۱۰ — ۱۰ ردیف در هر صفحه</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-click="sw.redirects.push({id:'',from:'',to:'',code:301})">+ افزودن</button>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" data-bankai-click="saveRedirects()" data-bankai-disabled="sw.savingRd" data-bankai-text="sw.savingRd ? '…' : 'ذخیره'"></button>
                </div>
            </div>
            <div class="bk-data-table-wrap">
                <table class="bk-data-table">
                    <thead><tr><th>مسیر مبدأ</th><th>مقصد</th><th style="width:100px">کد</th><th style="width:56px"></th></tr></thead>
                    <tbody>
                        <template data-bankai-if="!(pagedRedirects||[]).length"><tr><td colspan="4" class="bk-data-empty">ردیفی نیست</td></tr></template>
                        <template data-bankai-for="(r,i) in pagedRedirects" data-bankai-key="'rd'+((sw.rdPage||1)-1)*10+i">
                            <tr>
                                <td><input class="bk-data-input" type="text" dir="ltr" placeholder="/old-path" data-bankai-model="r.from"></td>
                                <td><input class="bk-data-input" type="url" dir="ltr" placeholder="https://…" data-bankai-model="r.to"></td>
                                <td>
                                    <select class="bk-data-input" data-bankai-model.number="r.code">
                                        <option :value="301">301</option>
                                        <option :value="302">302</option>
                                        <option :value="410">410</option>
                                    </select>
                                </td>
                                <td style="text-align:center"><button type="button" class="bk-btn bk-btn-icon" data-bankai-click="removeRedirectAt(((sw.rdPage||1)-1)*10+i)">×</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="bk-data-pager" data-bankai-show="(sw.redirects||[]).length > 10">
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-disabled="(sw.rdPage||1)<=1" data-bankai-click="sw.rdPage--">قبلی</button>
                <span data-bankai-text="'صفحه '+(sw.rdPage||1)+' از '+rdTotalPages"></span>
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-disabled="(sw.rdPage||1)>=rdTotalPages" data-bankai-click="sw.rdPage++">بعدی</button>
            </div>
        </div>

        <div class="bk-data-panel bankai-card" style="padding:0;margin-bottom:16px;overflow:hidden;border-radius:16px;border:1px solid #E2E8F0;">
            <div class="bk-data-panel__head">
                <div>
                    <h4>لاگ ۴۰۴</h4>
                    <p>آدرس‌های یافت‌نشده — ۱۰ مورد در هر صفحه</p>
                </div>
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-click="clear404()">پاک کردن</button>
            </div>
            <div class="bk-data-table-wrap">
                <table class="bk-data-table">
                    <thead><tr><th>URI</th><th style="width:90px">تعداد</th><th style="width:170px">آخرین</th></tr></thead>
                    <tbody>
                        <template data-bankai-if="!(paged404||[]).length"><tr><td colspan="3" class="bk-data-empty">موردی ثبت نشده</td></tr></template>
                        <template data-bankai-for="row in paged404" data-bankai-key="row.uri">
                            <tr>
                                <td dir="ltr" style="font-family:ui-monospace,monospace;font-size:12px" data-bankai-text="row.uri"></td>
                                <td><span class="bk-count-pill" data-bankai-text="row.count"></span></td>
                                <td style="font-size:12px;color:#64748B" data-bankai-text="row.last ? new Date(row.last*1000).toLocaleString('fa-IR') : '—'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="bk-data-pager" data-bankai-show="(sw.log404||[]).length > 10">
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-disabled="(sw.log404Page||1)<=1" data-bankai-click="sw.log404Page--">قبلی</button>
                <span data-bankai-text="'صفحه '+(sw.log404Page||1)+' از '+log404TotalPages"></span>
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" data-bankai-disabled="(sw.log404Page||1)>=log404TotalPages" data-bankai-click="sw.log404Page++">بعدی</button>
            </div>
        </div>




</div><!-- /tab-seo-engine -->
</div>
</div>
<!-- close tab-seo-engine.php -->
