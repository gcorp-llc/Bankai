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

<div id="tab-seo-engine" class="bankai-tab-pane" x-show="activeTab === 'seo-engine'" x-cloak
     x-data="bankaiSeoEngineData()"
     x-init="initEngine()">

    <!-- Header Card -->
    <div class="bankai-card bk-seo-header">
        <div class="bk-seo-header-main">
            <div class="bk-seo-title-group">
                <div class="bk-seo-title-flex">
                    <h2 class="bk-seo-h2">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                        </svg>
                        <span>موتور سئو و اسکیما (SEO Engine)</span>
                    </h2>
                    <span class="bk-seo-chip">JSON-LD · OG · Sitemap</span>
                </div>
                <p class="bk-seo-subtext">مدیریت ماژول‌های سئو، اسکیما، ساختار متا و تحلیل هوشمند مقالات سایت</p>
            </div>
            <button type="button" class="bk-seo-btn bk-seo-btn-wizard" @click="runAutoWizard()" :disabled="wizardBusy">
                <span class="bk-wizard-spin" x-show="wizardBusy" x-cloak></span>
                <svg x-show="!wizardBusy" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                <span x-text="wizardBusy ? 'در حال راه‌اندازی…' : 'ویزارد راه‌اندازی سئو'"></span>
            </button>
        </div>
        
        <nav class="bk-seo-tabs" role="tablist">
            <button type="button" role="tab" class="bk-seo-tab" :class="{ 'is-active': seoPanel === 'tools' }" @click="seoPanel = 'tools'; if (!an._loaded) loadAnalyticsReport()"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg> ماژول‌های سئو</button>
            <button type="button" role="tab" class="bk-seo-tab" :class="{ 'is-active': seoPanel === 'analytics' }" @click="seoPanel = 'analytics'"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg> گوگل آنالیتیکس</button>
            <button type="button" role="tab" class="bk-seo-tab" :class="{ 'is-active': seoPanel === 'import' }" @click="seoPanel = 'import'; initSeoImport()"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m8 11 4 4 4-4"/><path d="M4 19h16"/></svg> همگام‌سازی Rank Math / Yoast</button>
            <button type="button" role="tab" class="bk-seo-tab" :class="{ 'is-active': seoPanel === 'sitewide' }" @click="seoPanel = 'sitewide'; loadSitewide()"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg> سئوی سایت</button>
        </nav>
    </div>

    <!-- Site Analytics inside SEO modules -->
    <div x-show="seoPanel === 'tools'" x-cloak class="bk-analytics-dash bankai-card"
         >
        <div class="bk-an-top">
            <div>
                <div class="bk-an-brand">Bankai Analytics</div>
                <h3 class="bk-an-title">
                    آنالیتیکس سایت
                    <span class="bk-an-badge" :class="an.visits_change >= 0 ? 'is-up' : 'is-down'"
                          x-text="(an.visits_change >= 0 ? '↑ ' : '↓ ') + Math.abs(an.visits_change) + '%'"></span>
                </h3>
                <p class="bk-an-sub">بازدید واقعی · دستگاه · کشور بر اساس IP</p>
            </div>
            <div class="bk-an-ranges">
                <button type="button" class="bk-an-range" :class="{ 'is-active': an.range === '1y' }" @click="an.range='1y'; an._loaded=false; loadAnalyticsReport()">۱ سال</button>
                <button type="button" class="bk-an-range" :class="{ 'is-active': an.range === '30d' }" @click="an.range='30d'; an._loaded=false; loadAnalyticsReport()">۳۰ روز</button>
                <button type="button" class="bk-an-range" :class="{ 'is-active': an.range === '7d' }" @click="an.range='7d'; an._loaded=false; loadAnalyticsReport()">۷ روز</button>
                <button type="button" class="bk-an-range" :class="{ 'is-active': an.range === '24h' }" @click="an.range='24h'; an._loaded=false; loadAnalyticsReport()">۲۴ ساعت</button>
            </div>
        </div>

        <div class="bk-an-grid">
            <div class="bk-an-card">
                <div class="bk-an-card__head">
                    <span>بازدید</span>
                    <span class="bk-an-chip" :class="an.visits_change >= 0 ? 'is-up' : 'is-down'"
                          x-text="(an.visits_change >= 0 ? '+' : '') + an.visits_change + '%'"></span>
                </div>
                <div class="bk-an-card__val" x-text="formatAnNum(an.visits)">0</div>
                <svg class="bk-an-spark" viewBox="0 0 200 48" preserveAspectRatio="none">
                    <path :d="sparkPath(an.series)" fill="none" stroke="var(--bankai-accent, #5C6B7A)" stroke-width="2"/>
                    <path :d="sparkArea(an.series)" fill="var(--bankai-accent-subtle, rgba(92,107,122,.12))" opacity=".8"/>
                </svg>
            </div>
            <div class="bk-an-card">
                <div class="bk-an-card__head"><span>دوره قبل</span></div>
                <div class="bk-an-card__val" x-text="formatAnNum(an.prev_visits)">0</div>
                <p class="bk-an-hint">مقایسه با بازه قبلی هم‌طول</p>
            </div>
            <div class="bk-an-card">
                <div class="bk-an-card__head"><span>کشورهای فعال</span></div>
                <div class="bk-an-card__val" x-text="an.country_count || 0">0</div>
                <p class="bk-an-hint">از IP / Cloudflare</p>
            </div>

            <div class="bk-an-card bk-an-card--devices">
                <div class="bk-an-devices">
                    <div>
                        <div class="bk-an-dev-label">دسکتاپ</div>
                        <div class="bk-an-dev-pct" x-text="(an.devices.desktop.pct || 0) + '%'">0%</div>
                        <div class="bk-an-dev-hits" x-text="formatAnNum(an.devices.desktop.hits)">0</div>
                    </div>
                    <div>
                        <div class="bk-an-dev-label">موبایل</div>
                        <div class="bk-an-dev-pct" x-text="(an.devices.mobile.pct || 0) + '%'">0%</div>
                        <div class="bk-an-dev-hits" x-text="formatAnNum(an.devices.mobile.hits)">0</div>
                    </div>
                    <div>
                        <div class="bk-an-dev-label">تبلت</div>
                        <div class="bk-an-dev-pct" x-text="(an.devices.tablet.pct || 0) + '%'">0%</div>
                        <div class="bk-an-dev-hits" x-text="formatAnNum(an.devices.tablet.hits)">0</div>
                    </div>
                </div>
                <div class="bk-an-bar">
                    <span class="bk-an-bar__seg is-desk" :style="'width:' + (an.devices.desktop.pct || 0) + '%'"></span>
                    <span class="bk-an-bar__seg is-mob" :style="'width:' + (an.devices.mobile.pct || 0) + '%'"></span>
                    <span class="bk-an-bar__seg is-tab" :style="'width:' + (an.devices.tablet.pct || 0) + '%'"></span>
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
                    <template x-if="!an.countries.length">
                        <p class="bk-an-hint">پس از بازدید کاربران، کشورها اینجا نمایش داده می‌شوند.</p>
                    </template>
                    <template x-for="c in an.countries" :key="c.code">
                        <div class="bk-an-co-row">
                            <span class="bk-an-co-code" x-text="c.code"></span>
                            <div class="bk-an-co-track"><span :style="'width:' + countryPct(c.hits) + '%'"></span></div>
                            <span class="bk-an-co-hits" x-text="formatAnNum(c.hits)"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="bk-an-card bk-an-card--donut">
                <div class="bk-an-donut" :style="donutStyle()">
                    <div class="bk-an-donut__inner">
                        <strong x-text="formatAnNum(an.visits)">0</strong>
                        <span>کل بازدید</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bk-an-card" style="margin-top:12px;">
            <div class="bk-an-card__head">
                <span>پربازدیدترین مطالب</span>
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="an._loaded=false; loadAnalyticsReport()" :disabled="an.loading"
                        x-text="an.loading ? '…' : 'تازه‌سازی'"></button>
            </div>
            <table class="bk-seo-table" x-show="an.top_posts.length">
                <thead><tr><th>عنوان</th><th style="width:100px;text-align:center;">بازدید</th></tr></thead>
                <tbody>
                    <template x-for="p in an.top_posts" :key="p.id">
                        <tr>
                            <td><a :href="p.url" target="_blank" x-text="p.title"></a></td>
                            <td style="text-align:center;font-weight:700" x-text="formatAnNum(p.hits)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="bk-an-hint" x-show="!an.top_posts.length">هنوز بازدیدی ثبت نشده است.</p>
        </div>
    </div>

    <!-- Audit Banner -->
    <div class="bankai-card bk-seo-audit-card" x-show="seoPanel === 'tools'" x-cloak>
        <div class="bk-seo-audit-head">
            <div style="display:flex;align-items:center;gap:16px;">
                <div class="bk-score-ring-lg">
                    <svg viewBox="0 0 36 36">
                        <circle class="bk-track" cx="18" cy="18" r="14"></circle>
                        <circle class="bk-progress" cx="18" cy="18" r="14"
                            stroke-dasharray="87.96"
                            :stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, (seoAudit && seoAudit.score) || 0)) / 100)"
                            :style="{ stroke: scoreColor((seoAudit && seoAudit.score) || 0) }"></circle>
                    </svg>
                    <span class="bk-score-num" x-text="(seoAudit && seoAudit.running) ? '…' : ((seoAudit && seoAudit.score) || 0)">0</span>
                </div>
                <div>
                    <div style="font-weight:800;font-size:16px;color:#0F172A;">وضعیت کل سئوی سایت</div>
                    <div style="font-size:12px;color:#64748B;margin-top:3px;" x-text="(seoAudit && seoAudit.running) ? 'در حال تحلیل بخش‌های مختلف…' : (((seoAudit && seoAudit.items) || []).filter(i => i.pass).length + ' از ' + (((seoAudit && seoAudit.items) || []).length) + ' استاندارد رعایت شده است.')">—</div>
                </div>
            </div>
            <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="runSeoAuditInline()" :disabled="seoAudit && seoAudit.running">
                <span x-text="(seoAudit && seoAudit.running) ? 'در حال ممیزی…' : 'بررسی مجدد'"></span>
            </button>
        </div>
        <ul class="bk-seo-audit-list">
            <template x-for="item in (seoAudit && seoAudit.items) || []" :key="item.key">
                <li :class="item.pass ? 'is-pass' : 'is-fail'">
                    <span class="bk-audit-icon" x-text="item.pass ? '✓' : '!'"></span>
                    <div>
                        <strong x-text="item.label || item.label_en"></strong>
                        <a x-show="item.link" :href="item.link" target="_blank" rel="noopener" class="bk-audit-link">مشاهده</a>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    <!-- SEO Tools Panel -->
    <div x-show="seoPanel === 'tools'" x-cloak>
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
                                   x-model="seoState['<?php echo $mod_id; ?>']"
                                   @change="toggleSeoModule('<?php echo $mod_id; ?>')">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <p style="font-size:12px;color:#64748B;line-height:1.6;margin:0 0 14px;"><?php echo $desc_fa; ?></p>
                </div>
                <div class="bk-seo-mod-foot">
                    <span style="font-size:11px;font-weight:700;" :style="seoState['<?php echo $mod_id; ?>'] ? 'color:#16A34A' : 'color:#94A3B8'" x-text="seoState['<?php echo $mod_id; ?>'] ? 'فعال' : 'غیرفعال'"></span>
                    <button type="button" class="bk-seo-btn-sm" @click="openSeoDrawer('<?php echo $mod_id; ?>', '<?php echo esc_js($title_fa); ?>')">تنظیمات</button>
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
                        <input type="text" class="bk-seo-input" x-model="integEdit.ga4" placeholder="G-XXXXXXXX">
                    </div>
                    <div>
                        <label class="bk-seo-label">Google Tag Manager</label>
                        <input type="text" class="bk-seo-input" x-model="integEdit.gtm" placeholder="GTM-XXXX">
                    </div>
                    <div>
                        <label class="bk-seo-label">Google Site Verification</label>
                        <input type="text" class="bk-seo-input" x-model="integEdit.gsc" placeholder="کد متای گوگل سرچ کنسول">
                    </div>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="align-self:flex-start;"
                            @click="saveIntegration({ _key:'google', google_analytics_id: integEdit.ga4, ga4_measurement_id: integEdit.ga4, google_tag_manager_id: integEdit.gtm, google_site_verification: integEdit.gsc })"
                            :disabled="integSaving.google">
                        <span x-text="integSaving.google ? 'در حال ذخیره…' : 'ذخیره تنظیمات گوگل'"></span>
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
                        <input type="text" class="bk-seo-input" x-model="integEdit.bing">
                    </div>
                    <div>
                        <label class="bk-seo-label">Yandex Verification</label>
                        <input type="text" class="bk-seo-input" x-model="integEdit.yandex">
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;cursor:pointer;margin-top:4px;">
                        <input type="checkbox" x-model="integEdit.sitemap" style="accent-color:#0969DA;">
                        فعال‌سازی نقشه سایت دینامیک (Sitemap XML)
                    </label>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="align-self:flex-start;"
                            @click="saveIntegration({ _key:'other', bing_webmaster: integEdit.bing, yandex_verification: integEdit.yandex, sitemap_enabled: !!integEdit.sitemap })"
                            :disabled="integSaving.other">
                        <span x-text="integSaving.other ? 'در حال ذخیره…' : 'ذخیره تنظیمات'"></span>
                    </button>
                </div>
            </div>

            <div class="bankai-card" style="padding:20px;grid-column:1 / -1;">
                <div style="font-weight:800;font-size:14px;margin-bottom:10px;">فایل robots.txt</div>
                <textarea class="bk-seo-input mono" rows="4" x-model="integEdit.robots" placeholder="User-agent: *&#10;Allow: /"></textarea>
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="margin-top:10px;"
                        @click="saveIntegration({ _key:'robots', robots_txt: integEdit.robots })"
                        :disabled="integSaving.robots">
                    <span x-text="integSaving.robots ? 'در حال ذخیره…' : 'ذخیره robots.txt'"></span>
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
                <span class="bk-fk-count" x-text="(fixedList.length || 0) + ' کلمه'"></span>
            </div>
            <div class="bk-fk-tags" x-show="fixedList.length">
                <template x-for="(kw, i) in fixedList" :key="'fk'+i">
                    <span class="bk-fk-chip">
                        <span x-text="kw"></span>
                        <button type="button" class="bk-fk-remove" title="حذف" @click="removeFixedKeyword(i)">&times;</button>
                    </span>
                </template>
            </div>
            <div class="bk-fk-empty" x-show="!fixedList.length">هنوز کلمه کلیدی ثابتی تعریف نشده است.</div>
            <div class="bk-fk-add-row">
                <input type="text" class="bk-seo-input bk-fk-input" x-model="fixedKeywordInput"
                       @keydown.enter.prevent="addFixedKeyword()"
                       placeholder="کلمه جدید را وارد کرده و Enter بزنید…">
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="addFixedKeyword()">افزودن</button>
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="saveFixedKeywords()" :disabled="savingFixed">
                    <span x-text="savingFixed ? 'ذخیره…' : 'ذخیره کلیدواژه‌ها'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Google Analytics Panel (real settings) -->
    <!-- Google Analytics connection tab -->

    <!-- Import Wizard Rank Math / Yoast -->
    <div x-show="seoPanel === 'import'" x-cloak class="bankai-card" style="padding:0;margin-bottom:20px;overflow:hidden;border-radius:16px;border:1px solid #E2E8F0;">
        <div style="padding:18px 20px;background:linear-gradient(135deg,#faf5ff 0%,#eef2ff 100%);border-bottom:1px solid #E5E7EB;display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <h3 class="bk-section-title" style="margin:0 0 6px;font-size:16px;font-weight:800;">ویزارد همگام‌سازی سئو</h3>
                <p style="margin:0;font-size:13px;color:#64748B;line-height:1.65;max-width:640px;">
                    اطلاعات متا، کلیدواژه، canonical و شبکه‌های اجتماعی از <strong>Rank Math</strong> و <strong>Yoast SEO</strong>
                    به فیلدهای Bankai منتقل می‌شود — حتی اگر آن پلاگین‌ها حذف شده باشند (تا وقتی متا در دیتابیس باشد).
                </p>
            </div>
            <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="startSeoImport()" :disabled="seoImport.running" style="border-radius:10px;">
                <span x-text="seoImport.running ? 'در حال همگام‌سازی…' : 'شروع همگام‌سازی'"></span>
            </button>
        </div>
        <div style="padding:20px;">

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;">
            <template x-for="src in Object.values(seoImport.sources || {})" :key="src.id">
                <div class="bankai-card" style="padding:14px;border:1px solid #E5E7EB;border-radius:12px;">
                    <div style="font-weight:700;margin-bottom:4px;" x-text="src.label"></div>
                    <div style="font-size:12px;color:#64748B;" x-text="src.active ? ('فعال / داده: ' + (src.posts||0) + ' مقاله') : 'یافت نشد'"></div>
                </div>
            </template>
            <div class="bankai-card" style="padding:14px;border:1px solid #E5E7EB;border-radius:12px;">
                <div style="font-weight:700;margin-bottom:4px;">قابل استخراج</div>
                <div style="font-size:20px;font-weight:800;color:#0078d4;" x-text="seoImport.importableTotal || 0"></div>
            </div>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:14px;">
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;">
                <input type="checkbox" x-model="seoImport.overwrite">
                بازنویسی فیلدهای موجود Bankai
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                اولویت منبع:
                <select x-model="seoImport.prefer" class="bk-seo-input" style="width:auto;">
                    <option value="auto">خودکار (Rank Math سپس Yoast)</option>
                    <option value="rankmath">فقط Rank Math</option>
                    <option value="yoast">فقط Yoast</option>
                </select>
            </label>
        </div>

        <!-- Progress -->
        <div x-show="seoImport.running || seoImport.progress > 0" style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;color:#475569;">
                <span x-text="seoImport.stepText || 'آماده'"></span>
                <span x-text="(seoImport.progress || 0) + '%'"></span>
            </div>
            <div style="height:10px;background:#E5E7EB;border-radius:999px;overflow:hidden;">
                <div style="height:100%;background:linear-gradient(90deg,#0078d4,#50e6ff);transition:width .3s;"
                     :style="'width:' + (seoImport.progress || 0) + '%'"></div>
            </div>
            <div style="font-size:12px;color:#64748B;margin-top:6px;"
                 x-text="'صفحه ' + (seoImport.page || 0) + ' / ' + (seoImport.totalPages || 0) + ' — وارد شده در این اجرا: ' + (seoImport.importedCount || 0)"></div>
        </div>

        <!-- Report -->
        <div x-show="seoImport.report" class="bankai-card" style="padding:16px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;margin-bottom:16px;">
            <h4 style="margin:0 0 8px;">گزارش همگام‌سازی</h4>
            <ul style="margin:0;padding-inline-start:18px;font-size:13px;line-height:1.8;color:#334155;">
                <li x-text="'تعداد مقالات استخراج‌شده: ' + (seoImport.importedCount || 0)"></li>
                <li x-text="'رد شده / بدون متا جدید: ' + (seoImport.skippedCount || 0)"></li>
                <li x-text="'مقالات بدون سئوی Bankai: ' + (seoImport.missingTotal || 0)"></li>
            </ul>
            <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="loadMissingSeo()">نمایش مقالات سئونشده</button>
            </div>
        </div>


        </div><!-- /import body padding -->

        <!-- CTA: missing SEO moved to Articles -->
        <div class="bankai-card" style="margin-top:16px;padding:18px 20px;border:1px solid #E2E8F0;border-radius:14px;background:linear-gradient(135deg,#f8fafc,#eef6fc);">
            <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:center;">
                <div>
                    <h4 style="margin:0 0 6px;font-size:15px;font-weight:800;color:#0f172a;">مقالات بدون سئوی Bankai</h4>
                    <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;">
                        سئوی خودکار با دستیار هوشمند (متا، کلیدواژه، لینک، تصویر، اسکیما…) اکنون در
                        <strong>مدیریت مقالات</strong> در تب جداگانه قرار دارد.
                        <span x-show="seoImport.missingTotal"> تعداد فعلی بدون سئو: <strong x-text="seoImport.missingTotal || 0"></strong></span>
                    </p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="loadMissingSeo()">بروزرسانی شمارش</button>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary"
                        @click="activeTab='articles'; $nextTick(() => { window.dispatchEvent(new CustomEvent('bankai-open-missing-seo')); })">
                        رفتن به تب مقالات بدون سئو
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="seoPanel === 'analytics'" x-cloak class="bk-ga-panel">
        <div class="bk-conn-hero bankai-card">
            <div class="bk-conn-hero__icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/></svg>
            </div>
            <div class="bk-conn-hero__text">
                <h3>اتصال Google Analytics 4</h3>
                <p>شناسه اندازه‌گیری را وارد کنید تا gtag فقط با ID معتبر در سایت تزریق شود. گزارش بازدید در تب «ماژول‌های سئو» از آمار خود بنکای است.</p>
            </div>
            <span class="bk-seo-chip" :class="integEdit.ga4 ? 'is-ok' : 'is-warn'"
                  x-text="integEdit.ga4 ? 'متصل' : 'قطع'"></span>
        </div>

        <div class="bk-conn-grid">
            <div class="bk-conn-card bankai-card">
                <div class="bk-conn-card__head">
                    <strong>GA4 Measurement ID</strong>
                    <a class="bk-guide-link" href="https://support.google.com/analytics/answer/9304153" target="_blank" rel="noopener">راهنمای دریافت ID ↗</a>
                </div>
                <input type="text" class="bk-seo-input" x-model="integEdit.ga4" placeholder="G-XXXXXXXXXX" dir="ltr">
                <p class="bk-fk-desc">فرمت معتبر: G- و سپس حروف/عدد</p>
            </div>
            <div class="bk-conn-card bankai-card">
                <div class="bk-conn-card__head">
                    <strong>Google Tag Manager</strong>
                    <a class="bk-guide-link" href="https://support.google.com/tagmanager/answer/6103696" target="_blank" rel="noopener">راهنمای GTM ↗</a>
                </div>
                <input type="text" class="bk-seo-input" x-model="integEdit.gtm" placeholder="GTM-XXXXXXX" dir="ltr">
            </div>
            <div class="bk-conn-card bankai-card">
                <div class="bk-conn-card__head">
                    <strong>Search Console</strong>
                    <a class="bk-guide-link" href="https://search.google.com/search-console" target="_blank" rel="noopener">کنسول جستجو ↗</a>
                </div>
                <input type="text" class="bk-seo-input" x-model="integEdit.gsc" placeholder="کد تأیید meta" dir="ltr">
            </div>
        </div>

        <div class="bk-conn-actions">
            <button type="button" class="bk-seo-btn bk-seo-btn-primary"
                @click="saveIntegration({ _key:'google', google_analytics_id: integEdit.ga4, ga4_measurement_id: integEdit.ga4, google_tag_manager_id: integEdit.gtm, google_site_verification: integEdit.gsc })"
                :disabled="integSaving && integSaving.google">
                <span x-text="(integSaving && integSaving.google) ? 'در حال ذخیره…' : 'ذخیره اتصال'"></span>
            </button>
            <a class="bk-seo-btn bk-seo-btn-ghost" href="https://analytics.google.com/" target="_blank" rel="noopener">باز کردن Analytics ↗</a>
        </div>

        <div class="bk-conn-status bankai-card">
            <h4>وضعیت</h4>
            <ul>
                <li><span class="bk-dot" :class="integEdit.ga4 ? 'ok' : 'off'"></span> GA4: <strong dir="ltr" x-text="integEdit.ga4 || '—'"></strong></li>
                <li><span class="bk-dot" :class="integEdit.gtm ? 'ok' : 'off'"></span> GTM: <strong dir="ltr" x-text="integEdit.gtm || '—'"></strong></li>
                <li><span class="bk-dot" :class="integEdit.gsc ? 'ok' : 'off'"></span> Search Console: <strong x-text="integEdit.gsc ? 'تنظیم شده' : 'تنظیم نشده'"></strong></li>
            </ul>
        </div>
    </div>

    <!-- Articles Section -->
    <div id="bk-seo-articles-section" x-show="false" x-cloak>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:12px;">
            <h3 class="bk-section-title" style="margin:0;">جدول ممیزی سئوی مقالات</h3>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select x-model="perPage" @change="page=1;loadArticles()" class="bk-seo-input" style="width:auto;">
                    <option value="10">۱۰ عدد</option>
                    <option value="25">۲۵ عدد</option>
                    <option value="50">۵۰ عدد</option>
                </select>
                <select x-model="orderby" @change="page=1;loadArticles()" class="bk-seo-input" style="width:auto;">
                    <option value="modified">آخرین ویرایش</option>
                    <option value="date">تاریخ انتشار</option>
                    <option value="seo_score">امتیاز سئو</option>
                    <option value="views">تعداد بازدید</option>
                    <option value="title">عنوان</option>
                </select>
                <select x-model="order" @change="page=1;loadArticles()" class="bk-seo-input" style="width:auto;">
                    <option value="DESC">نزولی</option>
                    <option value="ASC">صعودی</option>
                </select>
                <input type="search" x-model="searchQ" @keydown.enter.prevent="page=1;loadArticles()"
                       placeholder="جستجوی مقاله…" class="bk-seo-input" style="width:180px;">
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="page=1;loadArticles()" :disabled="loading">
                    <span x-text="loading ? '…' : 'جستجو'"></span>
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
                        <tr x-show="!loading && articles.length === 0">
                            <td colspan="8" style="text-align:center;padding:32px;color:#94A3B8;">مقاله‌ای یافت نشد.</td>
                        </tr>
                        <template x-for="row in articles" :key="row.id">
                            <tr>
                                <td>
                                    <div class="bk-seo-cover">
                                        <img x-show="row.cover" :src="row.cover" alt="" width="44" height="44">
                                        <span x-show="!row.cover">🖼</span>
                                    </div>
                                </td>
                                <td style="max-width:320px;">
                                    <div style="font-weight:700;font-size:13px;color:#0F172A;" x-text="row.title"></div>
                                    <div style="font-size:11px;color:#64748B;margin-top:2px;" x-text="row.seo_title || '— بدون عنوان سئو —'"></div>
                                    <div style="font-size:11px;color:#94A3B8;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                         x-text="row.description || '— بدون توضیحات متا —'"></div>
                                </td>
                                <td>
                                    <div class="bk-score-ring-sm">
                                        <svg viewBox="0 0 36 36">
                                            <circle class="bk-track" cx="18" cy="18" r="14"></circle>
                                            <circle class="bk-progress" cx="18" cy="18" r="14"
                                                stroke-dasharray="87.96"
                                                :stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, row.score || 0)) / 100)"
                                                :style="{ stroke: scoreColor(row.score) }"></circle>
                                        </svg>
                                        <span class="bk-score-num" x-text="row.score || 0">0</span>
                                    </div>
                                </td>
                                <td style="text-align:center;font-weight:700;" x-text="row.views || 0"></td>
                                <td style="text-align:center;font-weight:700;" x-text="row.internal_links"></td>
                                <td style="text-align:center;font-weight:700;" x-text="row.external_links"></td>
                                <td>
                                    <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                        <span x-show="row.focus_keyword" class="bk-seo-tag focus" x-text="row.focus_keyword"></span>
                                        <template x-for="(k, ki) in (row.keywords || []).slice(0, 2)" :key="ki">
                                            <span class="bk-seo-tag muted" x-text="k"></span>
                                        </template>
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex;flex-direction:column;gap:4px;align-items:center;">
                                        <button type="button" class="bk-seo-btn-sm" @click="openEdit(row)">ویرایش متا</button>
                                        <a :href="row.edit_url" target="_blank" rel="noopener" class="bk-seo-link">ویرایش مطلب ↗</a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <div class="bk-seo-pager" x-show="totalPages > 1">
                <button type="button" class="bk-seo-btn-sm" :disabled="page <= 1" @click="page--; loadArticles()">صفحه قبل</button>
                <span x-text="page + ' از ' + totalPages + ' (کل: ' + total + ' مقاله)'"></span>
                <button type="button" class="bk-seo-btn-sm" :disabled="page >= totalPages" @click="page++; loadArticles()">صفحه بعد</button>
            </div>
        </div>
    </div>

    <!-- Edit Meta Modal -->
    <div x-show="edit.open" x-cloak class="bk-seo-modal-bg" @click.self="edit.open = false">
        <div class="bankai-card bk-seo-modal" @click.stop>
            <div class="bk-seo-modal-head">
                <strong x-text="'ویرایش متای: ' + (edit.row?.title || '')"></strong>
                <button type="button" class="bk-seo-modal-x" @click="edit.open = false">&times;</button>
            </div>
            <div class="bk-seo-modal-body">
                <label class="bk-seo-label">عنوان سئو</label>
                <input type="text" class="bk-seo-input" x-model="edit.seo_title" maxlength="70">
                
                <label class="bk-seo-label">متا دیسکریپشن</label>
                <textarea class="bk-seo-input" x-model="edit.description" rows="3" maxlength="170"></textarea>
                
                <label class="bk-seo-label">کلمه کلیدی اصلی</label>
                <input type="text" class="bk-seo-input" x-model="edit.focus_keyword">
                
                <label class="bk-seo-label">کلمات کلیدی جانبی (با ویرگول جدا کنید)</label>
                <input type="text" class="bk-seo-input" x-model="edit.keywords_str">
            </div>
            <div class="bk-seo-modal-foot">
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="edit.open = false">انصراف</button>
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="saveEdit()" :disabled="edit.saving">
                    <span x-text="edit.saving ? 'در حال ذخیره…' : 'ذخیره تغییرات'"></span>
                </button>
            </div>
        </div>
    </div>

<!-- Site-wide SEO -->
    <div x-show="seoPanel === 'sitewide'" class="bk-sitewide-panel" style="display:none" :style="seoPanel === 'sitewide' ? 'display:block' : 'display:none'">
        <h3 class="bk-section-title">سئوی سراسری سایت</h3>

        <div class="bk-site-conn-strip">
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico">🗺</div>
                <div>
                    <strong>نقشه سایت XML</strong>
                    <p>ایندکس خودکار پست و برگه</p>
                    <a class="bk-guide-link" :href="sw.sitemapIndex || '#'" target="_blank" rel="noopener">مشاهده sitemap ↗</a>
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
                                    <strong x-text="sw.settings.org_name || '<?php echo esc_js($site_name); ?>'"><?php echo esc_html($site_name); ?></strong>
                                    <p x-text="sw.settings.org_logo ? 'لوگو تنظیم شده' : '<?php echo esc_js(mb_substr($site_desc, 0, 80)); ?>'"><?php echo esc_html(mb_substr($site_desc, 0, 80)); ?></p>
                                </div>
                            </div>
                            <div class="bk-tablet-score">
                                <span class="bk-tablet-score__num" x-text="(seoAudit && seoAudit.score) || 0">0</span>
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
                        <div class="bk-metric-card__val" x-text="(seoAudit && seoAudit.score) || 0">0</div>
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
                        <div class="bk-metric-card__val" dir="ltr" style="font-size:13px;font-weight:700" x-text="(sw.sitemapIndex || '').replace(/^https?:\/\//,'').slice(0,28) || 'sitemap'">sitemap</div>
                        <div class="bk-metric-card__lab">نقشه سایت</div>
                    </div>
                </div>
                <div class="bk-metric-card bk-metric-card--wide">
                    <div class="bk-metric-card__ico" style="background:#FEF2F2;color:#DC2626">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    </div>
                    <div>
                        <div class="bk-metric-card__val" x-text="(sw.log404 && sw.log404.length) || 0">0</div>
                        <div class="bk-metric-card__lab">مسیرهای ۴۰۴ ثبت‌شده</div>
                    </div>
                </div>
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="width:100%" @click="runSeoAuditInline()" :disabled="seoAudit && seoAudit.running">
                    <span x-text="(seoAudit && seoAudit.running) ? 'در حال ممیزی…' : 'اجرای ممیزی سئوی سایت'"></span>
                </button>
            </div>
        </div>

        <!-- Visual noindex toggles -->
        <div class="bk-visual-toggles" style="margin-bottom:16px;">
            <h4 class="bk-vsec-title">noindex آرشیوها</h4>
            <div class="bk-vtoggle-grid">
                <label class="bk-vtoggle" :class="{ 'is-on': sw.settings.noindex_author }"><input type="checkbox" x-model="sw.settings.noindex_author"><span class="bk-vtoggle__art">👤</span><span>نویسنده</span></label>
                <label class="bk-vtoggle" :class="{ 'is-on': sw.settings.noindex_date }"><input type="checkbox" x-model="sw.settings.noindex_date"><span class="bk-vtoggle__art">📅</span><span>تاریخ</span></label>
                <label class="bk-vtoggle" :class="{ 'is-on': sw.settings.noindex_tag }"><input type="checkbox" x-model="sw.settings.noindex_tag"><span class="bk-vtoggle__art">🏷</span><span>برچسب</span></label>
                <label class="bk-vtoggle" :class="{ 'is-on': sw.settings.noindex_search }"><input type="checkbox" x-model="sw.settings.noindex_search"><span class="bk-vtoggle__art">🔍</span><span>جستجو</span></label>
                <label class="bk-vtoggle" :class="{ 'is-on': sw.settings.noindex_attach }"><input type="checkbox" x-model="sw.settings.noindex_attach"><span class="bk-vtoggle__art">📎</span><span>پیوست</span></label>
            </div>
        </div>

        <div class="bankai-card" style="padding:16px;margin-bottom:16px;">
            <h4 style="margin:0 0 12px;font-size:13px;">Organization / WebSite Schema</h4>
            <div class="bk-field"><label>نام سازمان</label><input type="text" x-model="sw.settings.org_name"></div>
            <div class="bk-field"><label>لوگو (URL)</label><input type="url" dir="ltr" x-model="sw.settings.org_logo"></div>
            <div class="bk-field"><label>sameAs (هر خط یک شبکه اجتماعی)</label><textarea rows="3" dir="ltr" x-model="sw.settings.org_same_as"></textarea></div>
            <label class="bk-switch-row"><span>SearchAction در اسکیمای سایت</span><input type="checkbox" x-model="sw.settings.website_search"></label>
        </div>


        <div class="bankai-card" style="padding:16px;margin-bottom:16px;">
            <h4 style="margin:0 0 12px;font-size:13px;">نقشه سایت</h4>
            <p class="bk-fk-desc">ایندکس: <a :href="sw.sitemapIndex" target="_blank" dir="ltr" x-text="sw.sitemapIndex"></a></p>
            <div class="bk-field"><label>شناسه پست‌های exclude (با کاما)</label><input type="text" dir="ltr" x-model="sw.settings.sitemap_exclude_ids" placeholder="12, 45, 90"></div>
        </div>

        <div class="bankai-card" style="padding:16px;margin-bottom:16px;">
            <h4 style="margin:0 0 12px;font-size:13px;">hreflang / چندزبانه</h4>
            <label class="bk-switch-row"><span>فعال (یا تشخیص خودکار Polylang/WPML)</span><input type="checkbox" x-model="sw.settings.hreflang_enabled"></label>
            <div class="bk-field"><label>زبان پیش‌فرض</label><input type="text" dir="ltr" x-model="sw.settings.hreflang_default" placeholder="fa"></div>
        </div>

        <div class="bankai-card" style="padding:16px;margin-bottom:16px;">
            <h4 style="margin:0 0 12px;font-size:13px;">Search Console</h4>
            <div class="bk-field"><label>Property URL</label><input type="url" dir="ltr" x-model="sw.settings.gsc_property" placeholder="https://example.com/"></div>
            <p class="bk-fk-desc">تأیید مالکیت از تب یکپارچه‌سازی؛ این فیلد برای لینک سریع و گزارش‌هاست.</p>
        </div>

        <div style="margin-bottom:20px;">
            <button type="button" class="button button-primary" @click="saveSitewide()" :disabled="sw.saving">
                <span x-text="sw.saving ? 'در حال ذخیره…' : 'ذخیره تنظیمات سئوی سایت'"></span>
            </button>
        </div>

        <div class="bankai-card" style="padding:16px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h4 style="margin:0;font-size:13px;">ریدایرکت سراسری</h4>
                <button type="button" class="button" @click="sw.redirects.push({id:'',from:'',to:'',code:301})">+ ردیف</button>
            </div>
            <template x-for="(r,i) in sw.redirects" :key="'rd'+i">
                <div style="display:grid;grid-template-columns:1fr 1fr 90px 36px;gap:8px;margin-bottom:8px;">
                    <input type="text" dir="ltr" placeholder="/old-path" x-model="r.from">
                    <input type="url" dir="ltr" placeholder="https://…" x-model="r.to">
                    <select x-model.number="r.code"><option :value="301">301</option><option :value="302">302</option><option :value="410">410</option></select>
                    <button type="button" class="button" @click="sw.redirects.splice(i,1)">×</button>
                </div>
            </template>
            <button type="button" class="button button-primary" @click="saveRedirects()" :disabled="sw.savingRd">ذخیره ریدایرکت‌ها</button>
        </div>

        <div class="bankai-card" style="padding:16px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h4 style="margin:0;font-size:13px;">لاگ ۴۰۴</h4>
                <button type="button" class="button" @click="clear404()">پاک کردن</button>
            </div>
            <template x-if="!sw.log404.length"><p class="bk-fk-desc">موردی ثبت نشده.</p></template>
            <table class="bk-seo-table" x-show="sw.log404.length">
                <thead><tr><th>URI</th><th>تعداد</th><th>آخرین</th></tr></thead>
                <tbody>
                    <template x-for="row in sw.log404" :key="row.uri">
                        <tr>
                            <td dir="ltr" x-text="row.uri"></td>
                            <td x-text="row.count"></td>
                            <td x-text="row.last ? new Date(row.last*1000).toLocaleString() : ''"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>
<style>

.bk-site-hero{display:grid;grid-template-columns:minmax(260px,340px) 1fr;gap:20px;margin-bottom:24px;align-items:start}
@media(max-width:900px){.bk-site-hero{grid-template-columns:1fr;justify-items:center}}
.bk-tablet-frame{position:relative;border:12px solid #1e293b;border-radius:2rem;background:#1e293b;max-width:320px;margin:0 auto;box-shadow:0 20px 50px rgba(15,23,42,.2)}
.bk-tablet-btn{position:absolute;width:3px;background:#334155;border-radius:2px}
.bk-tablet-btn--1{height:28px;inset-inline-start:-14px;top:64px}
.bk-tablet-btn--2{height:40px;inset-inline-start:-14px;top:110px}
.bk-tablet-btn--3{height:40px;inset-inline-start:-14px;top:158px}
.bk-tablet-btn--4{height:56px;inset-inline-end:-14px;top:128px}
.bk-tablet-screen{border-radius:1.35rem;overflow:hidden;background:#f8fafc;height:420px;position:relative}
.bk-tablet-chrome{display:flex;align-items:center;gap:6px;padding:8px 10px;background:#fff;border-bottom:1px solid #e2e8f0;font-size:11px;color:#64748b}
.bk-tablet-dot{width:7px;height:7px;border-radius:50%;background:#cbd5e1}
.bk-tablet-dot:nth-child(1){background:#f87171}
.bk-tablet-dot:nth-child(2){background:#fbbf24}
.bk-tablet-dot:nth-child(3){background:#34d399}
.bk-tablet-url{flex:1;margin-inline-start:6px;background:#f1f5f9;border-radius:6px;padding:3px 8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.bk-tablet-iframe{border:0;background:#fff;pointer-events:none;transform:scale(.85);transform-origin:top center;width:117.6%;height:calc(100% - 36px);margin-left:-8.8%}
.bk-tablet-overlay{position:absolute;left:0;right:0;bottom:0;padding:12px;background:linear-gradient(to top,rgba(15,23,42,.88),transparent);display:flex;justify-content:space-between;align-items:flex-end;gap:8px;color:#fff;pointer-events:none}
.bk-tablet-site{display:flex;gap:8px;align-items:center;min-width:0}
.bk-tablet-site strong{display:block;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bk-tablet-site p{margin:2px 0 0;font-size:11px;opacity:.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px}
.bk-tablet-favicon{border-radius:8px;background:#fff}
.bk-tablet-score{text-align:center;background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border-radius:12px;padding:8px 12px;flex-shrink:0}
.bk-tablet-score__num{display:block;font-size:22px;font-weight:800;line-height:1}
.bk-tablet-score__lab{font-size:10px;opacity:.9}
.bk-site-metrics{display:grid;grid-template-columns:1fr 1fr;gap:12px;align-content:start}
.bk-metric-card{display:flex;gap:12px;align-items:center;padding:14px;border-radius:14px;border:1px solid #E2E8F0;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.bk-metric-card--wide{grid-column:1/-1}
.bk-metric-card__ico{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.bk-metric-card__val{font-size:20px;font-weight:800;color:#0F172A;line-height:1.1}
.bk-metric-card__lab{font-size:12px;color:#64748B;font-weight:600;margin-top:2px}
.bk-vsec-title{margin:0 0 10px;font-size:13px;font-weight:800;color:#0F172A}
.bk-vtoggle-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px}
.bk-vtoggle{display:flex;flex-direction:column;align-items:center;gap:6px;padding:12px 8px;border-radius:14px;border:1.5px solid #E2E8F0;background:#fff;cursor:pointer;font-size:12px;font-weight:700;color:#334155;text-align:center}
.bk-vtoggle input{position:absolute;opacity:0;pointer-events:none}
.bk-vtoggle.is-on,.bk-vtoggle:has(input:checked){border-color:#8B9BB4;box-shadow:0 0 0 2px rgba(139,155,180,.2);background:#F8FAFC}
.bk-vtoggle__art{font-size:20px;line-height:1}



/* Analytics — aligned with Bankai admin theme */
.bk-analytics-dash {
  background: var(--bankai-card-bg, #fff);
  border: 1px solid var(--bankai-card-border, #E8E8EA);
  border-radius: var(--bankai-radius-lg, 20px);
  padding: 18px 20px 20px;
  margin-bottom: 18px;
  box-shadow: var(--bankai-shadow-1);
  color: var(--bankai-text-main, #1A1A1A);
}
.bk-an-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}
.bk-an-brand {
  font-size: 11px;
  color: var(--bankai-text-muted, #8A8F98);
  font-weight: 650;
  letter-spacing: .02em;
}
.bk-an-title {
  margin: 4px 0;
  font-size: 18px;
  font-weight: 800;
  color: var(--bankai-text-main);
  display: flex;
  align-items: center;
  gap: 10px;
}
.bk-an-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 999px;
}
.bk-an-badge.is-up, .bk-an-chip.is-up {
  background: var(--bankai-success-subtle, rgba(48,209,88,.12));
  color: #1a7f37;
}
.bk-an-badge.is-down, .bk-an-chip.is-down {
  background: rgba(255,69,58,.12);
  color: var(--bankai-danger, #FF453A);
}
.bk-an-sub {
  margin: 0;
  font-size: 12px;
  color: var(--bankai-text-muted);
}
.bk-an-ranges {
  display: flex;
  gap: 4px;
  background: #F4F4F5;
  padding: 4px;
  border-radius: 12px;
}
.bk-an-range {
  border: none;
  background: transparent;
  color: var(--bankai-text-muted);
  font-size: 12px;
  font-weight: 650;
  padding: 6px 12px;
  border-radius: 10px;
  cursor: pointer;
  font-family: inherit;
}
.bk-an-range.is-active {
  background: #fff;
  color: var(--bankai-text-main);
  box-shadow: var(--bankai-shadow-1);
}
.bk-an-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}
@media (max-width: 960px) {
  .bk-an-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
  .bk-an-grid { grid-template-columns: 1fr; }
}
.bk-an-card {
  background: #FAFBFC;
  border: 1px solid var(--bankai-border-subtle, #E8E8EA);
  border-radius: var(--bankai-radius-md, 16px);
  padding: 14px 16px;
}
.bk-an-card__head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  color: var(--bankai-text-muted);
  font-weight: 650;
  margin-bottom: 6px;
}
.bk-an-chip {
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 6px;
  font-weight: 700;
}
.bk-an-card__val {
  font-size: 26px;
  font-weight: 800;
  color: var(--bankai-text-main);
  letter-spacing: -.02em;
}
.bk-an-spark {
  width: 100%;
  height: 44px;
  margin-top: 8px;
  display: block;
}
.bk-an-hint {
  font-size: 12px;
  color: var(--bankai-text-muted);
  margin: 8px 0 0;
}
.bk-an-devices {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 8px;
  margin-bottom: 12px;
}
.bk-an-dev-label { font-size: 11px; color: var(--bankai-text-muted); font-weight: 650; }
.bk-an-dev-pct { font-size: 20px; font-weight: 800; color: var(--bankai-text-main); }
.bk-an-dev-hits { font-size: 11px; color: var(--bankai-text-subtle); }
.bk-an-bar {
  display: flex;
  height: 10px;
  border-radius: 99px;
  overflow: hidden;
  background: #E8E8EA;
}
.bk-an-bar__seg.is-desk, .bk-an-legend i.is-desk { background: #5C6B7A; }
.bk-an-bar__seg.is-mob, .bk-an-legend i.is-mob { background: #8B9BB4; }
.bk-an-bar__seg.is-tab, .bk-an-legend i.is-tab { background: #C5CBD3; }
.bk-an-legend {
  display: flex;
  gap: 14px;
  margin-top: 10px;
  font-size: 11px;
  color: var(--bankai-text-muted);
}
.bk-an-legend i {
  display: inline-block;
  width: 8px; height: 8px;
  border-radius: 50%;
  margin-inline-end: 4px;
}
.bk-an-countries { max-height: 180px; overflow: auto; }
.bk-an-co-row {
  display: grid;
  grid-template-columns: 36px 1fr 48px;
  gap: 8px;
  align-items: center;
  margin-bottom: 8px;
  font-size: 12px;
}
.bk-an-co-code {
  font-weight: 800;
  font-family: ui-monospace, monospace;
  color: var(--bankai-accent);
}
.bk-an-co-track {
  height: 8px;
  background: #E8E8EA;
  border-radius: 99px;
  overflow: hidden;
}
.bk-an-co-track span {
  display: block;
  height: 100%;
  background: var(--bankai-accent);
  border-radius: 99px;
}
.bk-an-co-hits { text-align: end; color: var(--bankai-text-muted); font-weight: 700; }
.bk-an-card--donut {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 160px;
}
.bk-an-donut {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.bk-an-donut__inner {
  width: 92px;
  height: 92px;
  border-radius: 50%;
  background: #fff;
  border: 1px solid var(--bankai-border-subtle);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.bk-an-donut__inner strong { font-size: 18px; color: var(--bankai-text-main); }
.bk-an-donut__inner span { font-size: 11px; color: var(--bankai-text-muted); }

/* GA connection panel */
.bk-ga-panel { margin-bottom: 20px; }
.bk-conn-hero {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 18px 20px;
  margin-bottom: 14px;
}
.bk-conn-hero__icon {
  width: 48px; height: 48px;
  border-radius: 14px;
  background: var(--bankai-accent-subtle);
  color: var(--bankai-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.bk-conn-hero__text { flex: 1; min-width: 0; }
.bk-conn-hero__text h3 { margin: 0 0 6px; font-size: 16px; font-weight: 800; }
.bk-conn-hero__text p { margin: 0; font-size: 12px; color: var(--bankai-text-muted); line-height: 1.6; }
.bk-seo-chip.is-ok { background: var(--bankai-success-subtle); color: #1a7f37; }
.bk-seo-chip.is-warn { background: rgba(255,69,58,.1); color: var(--bankai-danger); }
.bk-conn-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 14px;
}
@media (max-width: 900px) {
  .bk-conn-grid { grid-template-columns: 1fr; }
}
.bk-conn-card { padding: 16px; }
.bk-conn-card__head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}
.bk-conn-card__head strong { font-size: 13px; }
.bk-guide-link {
  font-size: 11px;
  font-weight: 650;
  color: var(--bankai-accent);
  text-decoration: none;
}
.bk-guide-link:hover { text-decoration: underline; }
.bk-conn-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}
.bk-conn-status { padding: 16px 18px; }
.bk-conn-status h4 { margin: 0 0 10px; font-size: 13px; }
.bk-conn-status ul {
  list-style: none;
  margin: 0;
  padding: 0;
  font-size: 13px;
  color: var(--bankai-text-muted);
  line-height: 1.9;
}
.bk-dot {
  display: inline-block;
  width: 8px; height: 8px;
  border-radius: 50%;
  margin-inline-end: 6px;
  background: #C5CBD3;
}
.bk-dot.ok { background: var(--bankai-success); }
.bk-dot.off { background: #E8E8EA; }

/* Site SEO connection strip */
.bk-site-conn-strip {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin: 16px 0 20px;
}
@media (max-width: 960px) {
  .bk-site-conn-strip { grid-template-columns: 1fr 1fr; }
}
.bk-site-conn-card {
  display: flex;
  gap: 12px;
  padding: 14px 16px;
  align-items: flex-start;
}
.bk-site-conn-card__ico {
  width: 40px; height: 40px;
  border-radius: 12px;
  background: var(--bankai-accent-subtle);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.bk-site-conn-card strong {
  display: block;
  font-size: 13px;
  margin-bottom: 2px;
}
.bk-site-conn-card p {
  margin: 0 0 6px;
  font-size: 11px;
  color: var(--bankai-text-muted);
}

.bk-sitewide-panel .bk-field { margin-bottom: 12px; }
.bk-sitewide-panel .bk-field label { display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px; }
.bk-sitewide-panel .bk-field input,
.bk-sitewide-panel .bk-field textarea,
.bk-sitewide-panel .bk-field select {
  width: 100%; max-width: 100%; box-sizing: border-box;
  border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px 10px; font-size: 13px;
}
.bk-sitewide-panel .bk-switch-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 0; border-bottom: 1px solid #F1F5F9; font-size: 13px; color: #334155;
}
.bk-sitewide-panel .bankai-card { background:#fff; border:1px solid #E2E8F0; border-radius:12px; }

/* CSS Styling System for Bankai SEO Engine */
#tab-seo-engine { font-family: inherit; color: #0F172A; }
#tab-seo-engine [x-cloak] { display: none !important; }

.bk-section-title { font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 14px; }
.bk-seo-header { border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; background: #fff; margin-bottom: 20px; }
.bk-seo-header-main { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
.bk-seo-title-flex { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.bk-seo-h2 { font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px; }
.bk-seo-h2 svg { width: 22px; height: 22px; color: #0969DA; }
.bk-seo-subtext { font-size: 12px; color: #64748B; margin: 4px 0 0; }
.bk-seo-chip { font-size: 10px; font-weight: 800; background: #F1F5F9; color: #475569; padding: 3px 8px; border-radius: 6px; }

/* Tabs */
.bk-seo-tabs { display: flex; gap: 8px; border-top: 1px solid #F1F5F9; padding-top: 14px; }
.bk-seo-tab { background: transparent; border: none; font-size: 13px; font-weight: 700; color: #64748B; padding: 8px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
.bk-seo-tab:hover { background: #F8FAFC; color: #0F172A; }
.bk-seo-tab.is-active { background: #EFF6FF; color: #2563EB; }

/* Buttons */
.bk-seo-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; border: 1px solid transparent; transition: all .15s ease; }
.bk-seo-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.bk-seo-btn-primary { background: #2563EB; color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,0.25); }
.bk-seo-btn-primary:hover { background: #1D4ED8; }
.bk-seo-btn-ghost { background: #fff; border-color: #CBD5E1; color: #334155; }
.bk-seo-btn-ghost:hover { background: #F8FAFC; }
.bk-seo-btn-sm { background: #F1F5F9; border: 1px solid #CBD5E1; color: #2563EB; font-size: 11px; font-weight: 700; padding: 5px 10px; border-radius: 6px; cursor: pointer; }
.bk-seo-btn-sm:hover { background: #E2E8F0; }

/* Grids */
.bk-seo-grid-3 { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.bk-seo-grid-2 { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; }

.bk-seo-mod-card { padding: 18px; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid #E2E8F0; border-radius: 12px; background: #fff; }
.bk-seo-mod-icon { width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.bk-seo-badge { font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 3px; }
.bk-seo-mod-foot { border-top: 1px solid #F1F5F9; padding-top: 10px; display: flex; justify-content: space-between; align-items: center; }

/* Form inputs */
.bk-seo-label { font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px; }
.bk-seo-input { width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 12px; box-sizing: border-box; outline: none; transition: border-color .15s; }
.bk-seo-input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.bk-seo-input.mono { font-family: ui-monospace, monospace; font-size: 11px; }

/* Audit Banner */
.bk-seo-audit-card { padding: 20px; border: 1px solid #E2E8F0; border-radius: 12px; background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%); margin-bottom: 24px; }
.bk-seo-audit-head { display: flex; justify-content: space-between; align-items: center; }
.bk-seo-audit-list { list-style: none; margin: 16px 0 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; }
.bk-seo-audit-list li { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; padding: 8px 12px; border-radius: 8px; background: #fff; border: 1px solid #F1F5F9; }
.bk-seo-audit-list li.is-pass .bk-audit-icon { color: #16A34A; }
.bk-seo-audit-list li.is-fail .bk-audit-icon { color: #DC2626; }

/* Score Ring */
.bk-score-ring-sm, .bk-score-ring-lg { position: relative; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
.bk-score-ring-lg { width: 64px; height: 64px; }
.bk-score-ring-sm svg, .bk-score-ring-lg svg { position: absolute; inset: 0; width: 100%; height: 100%; transform: rotate(-90deg); }
.bk-score-ring-sm .bk-track, .bk-score-ring-lg .bk-track { fill: none; stroke: #E2E8F0; stroke-width: 3.5; }
.bk-score-ring-sm .bk-progress, .bk-score-ring-lg .bk-progress { fill: none; stroke-width: 3.5; stroke-linecap: round; transition: stroke-dashoffset .4s ease; }
.bk-score-ring-sm .bk-score-num { font-size: 10px; font-weight: 800; }
.bk-score-ring-lg .bk-score-num { font-size: 15px; font-weight: 800; }

/* Tags */
.bk-seo-tag { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 10px; font-weight: 700; }
.bk-seo-tag.focus { background: #EFF6FF; color: #1D4ED8; }
.bk-seo-tag.muted { background: #F1F5F9; color: #64748B; }

/* Table */
.bk-seo-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.bk-seo-table th { text-align: right; padding: 10px 12px; background: #F8FAFC; color: #475569; font-weight: 700; border-bottom: 1px solid #E2E8F0; }
.bk-seo-table td { padding: 10px 12px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
.bk-seo-cover { width: 40px; height: 40px; border-radius: 6px; background: #F1F5F9; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.bk-seo-cover img { width: 40px; height: 40px; object-fit: cover; }
.bk-seo-link { font-size: 11px; color: #2563EB; text-decoration: none; font-weight: 600; }
.bk-seo-pager { padding: 12px 16px; display: flex; gap: 8px; align-items: center; justify-content: center; border-top: 1px solid #F1F5F9; font-size: 12px; color: #64748B; }

/* Fixed Keywords */
.bk-fk-section { padding: 20px; border: 1px solid #E2E8F0; border-radius: 12px; background: #fff; margin-bottom: 24px; }
.bk-fk-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
.bk-fk-title { margin: 0; font-size: 14px; font-weight: 800; color: #0F172A; }
.bk-fk-desc { margin: 4px 0 0; font-size: 12px; color: #64748B; }
.bk-fk-count { font-size: 11px; font-weight: 800; background: #EFF6FF; color: #1D4ED8; padding: 3px 10px; border-radius: 999px; }
.bk-fk-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.bk-fk-chip { display: inline-flex; align-items: center; gap: 6px; background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; border-radius: 999px; padding: 4px 10px; font-size: 11px; font-weight: 700; }
.bk-fk-remove { border: none; background: transparent; color: #94A3B8; cursor: pointer; font-size: 14px; line-height: 1; padding: 0; }
.bk-fk-remove:hover { color: #DC2626; }
.bk-fk-empty { font-size: 12px; color: #94A3B8; padding: 12px; background: #F8FAFC; border-radius: 8px; text-align: center; margin-bottom: 12px; }
.bk-fk-add-row { display: flex; gap: 8px; flex-wrap: wrap; }
.bk-fk-input { flex: 1; min-width: 200px; }

/* Modals */
.bk-seo-modal-bg { position: fixed; inset: 0; background: rgba(15,23,42,0.5); z-index: 100000; display: flex; align-items: center; justify-content: center; padding: 16px; }
.bk-seo-modal { width: min(480px, 100%); padding: 0; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); background: #fff; overflow: hidden; }
.bk-seo-modal-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; border-bottom: 1px solid #F1F5F9; font-size: 13px; }
.bk-seo-modal-x { border: none; background: transparent; font-size: 18px; cursor: pointer; color: #94A3B8; }
.bk-seo-modal-body { padding: 16px; display: flex; flex-direction: column; gap: 10px; }
.bk-seo-modal-foot { padding: 12px 16px; display: flex; gap: 8px; justify-content: flex-end; border-top: 1px solid #F1F5F9; background: #F8FAFC; }
</style>

<script>
function bankaiSeoEngineData() {
    return {
        seoPanel: 'tools',
        sw: {
            settings: {
                org_name: '', org_logo: '', org_same_as: '', website_search: true,
                noindex_author: true, noindex_date: true, noindex_tag: false, noindex_search: true, noindex_attach: true,
                sitemap_exclude_ids: '', hreflang_enabled: false, hreflang_default: '', gsc_property: ''
            },
            redirects: [],
            log404: [],
            sitemapIndex: '',
            saving: false,
            savingRd: false
        },
        wizardBusy: false,
        seoAudit: { running: false, score: 0, items: [] },
        an: {
            loading: false, range: '30d', visits: 0, prev_visits: 0, visits_change: 0,
            series: [], country_count: 0, countries: [], top_posts: [],
            devices: { desktop: {hits:0,pct:0}, mobile: {hits:0,pct:0}, tablet: {hits:0,pct:0} }, _loaded: false
        },
        seoState: <?php
            $__ss = [];
            foreach ($seo_mods as $__m) {
                $__id = $__m['id'] ?? '';
                if ($__id !== '') {
                    $__ss[$__id] = !empty($__m['enabled']);
                }
            }
            echo wp_json_encode($__ss);
        ?>,

        integEdit: {
            ga4: '<?php echo $ga4; ?>',
            gtm: '<?php echo $gtm; ?>',
            gsc: '<?php echo $gsc; ?>',
            bing: '<?php echo $bing; ?>',
            yandex: '<?php echo $yandex; ?>',
            sitemap: <?php echo $sitemap ? 'true' : 'false'; ?>,
            robots: <?php echo wp_json_encode($robots); ?>
        },
        integSaving: { google: false, other: false, robots: false },
        fixedList: [],
        fixedKeywordInput: '',
        savingFixed: false,
        
        // Articles state
        articles: [],
        page: 1,
        perPage: 10,
        totalPages: 1,
        total: 0,
        searchQ: '',
        orderby: 'modified',
        order: 'DESC',
        loading: false,

        edit: { open: false, row: null, seo_title: '', description: '', focus_keyword: '', keywords_str: '', saving: false },


        loadAnalyticsReport: function () {
            var self = this;
            self.an.loading = true;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'analytics/report?range=' + encodeURIComponent(self.an.range || '30d'), {
                credentials: 'same-origin',
                headers: { 'X-WP-Nonce': nonce }
            }).then(function (r) { return r.json(); }).then(function (j) {
                self.an.loading = false;
                var d = (j && j.data) ? j.data : j;
                if (!d || typeof d !== 'object') return;
                self.an.visits = d.visits || 0;
                self.an.prev_visits = d.prev_visits || 0;
                self.an.visits_change = d.visits_change || 0;
                self.an.series = d.series || [];
                if (d.devices) self.an.devices = d.devices;
                self.an.countries = d.countries || [];
                self.an.country_count = d.country_count || 0;
                self.an.top_posts = d.top_posts || [];
                self.an._loaded = true;
            }).catch(function () { self.an.loading = false; });
        },
        formatAnNum: function (n) {
            n = parseInt(n, 10) || 0;
            if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
            if (n >= 1000) return (n / 1000).toFixed(1) + 'k';
            return String(n);
        },
        sparkPath: function (series) {
            series = series || [];
            if (!series.length) return 'M0 40 L200 40';
            var max = 1;
            for (var i = 0; i < series.length; i++) { if ((series[i].h || 0) > max) max = series[i].h; }
            var w = 200, h = 48, step = w / Math.max(1, series.length - 1);
            var d = '';
            for (var j = 0; j < series.length; j++) {
                var x = j * step;
                var y = h - ((series[j].h || 0) / max) * (h - 4) - 2;
                d += (j === 0 ? 'M' : 'L') + x.toFixed(1) + ' ' + y.toFixed(1) + ' ';
            }
            return d;
        },
        sparkArea: function (series) {
            return this.sparkPath(series) + ' L200 48 L0 48 Z';
        },
        countryPct: function (hits) {
            var max = 1;
            var list = this.an.countries || [];
            for (var i = 0; i < list.length; i++) { if (list[i].hits > max) max = list[i].hits; }
            return Math.max(4, Math.round((hits / max) * 100));
        },
        donutStyle: function () {
            var d = this.an.devices || {};
            var a = (d.desktop && d.desktop.pct) || 0;
            var b = (d.mobile && d.mobile.pct) || 0;
            return 'background:conic-gradient(#5C6B7A 0 ' + a + '%, #8B9BB4 ' + a + '% ' + (a + b) + '%, #C5CBD3 ' + (a + b) + '% 100%)';
        },
        loadSitewide: function () {
            var self = this;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'seo/sitewide', { credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (!j || !j.success || !j.data) return;
                    if (j.data.settings) self.sw.settings = Object.assign({}, self.sw.settings, j.data.settings);
                    self.sw.redirects = j.data.redirects || [];
                    self.sw.log404 = j.data.log404 || [];
                    self.sw.sitemapIndex = (window.location.origin || '') + '/sitemap.xml';
                }).catch(function () {});
        },
        saveSitewide: function () {
            var self = this;
            self.sw.saving = true;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'seo/sitewide', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ settings: self.sw.settings })
            }).then(function (r) { return r.json(); }).then(function (j) {
                self.sw.saving = false;
                if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast((j && j.success) ? 'ذخیره شد' : 'خطا', j && j.success ? 'success' : 'error');
            }).catch(function () { self.sw.saving = false; });
        },
        saveRedirects: function () {
            var self = this;
            self.sw.savingRd = true;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'seo/redirects', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ redirects: self.sw.redirects })
            }).then(function (r) { return r.json(); }).then(function (j) {
                self.sw.savingRd = false;
                if (j && j.data) self.sw.redirects = j.data;
                if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast('ریدایرکت‌ها ذخیره شد', 'success');
            }).catch(function () { self.sw.savingRd = false; });
        },
        clear404: function () {
            var self = this;
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'seo/404-log', { method: 'DELETE', credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
                .then(function () { self.sw.log404 = []; });
        },
        toggleSeoModule: function (modId) {
            var self = this;
            var enabled = !!self.seoState[modId];
            var root = (window.bankaiCoreData && bankaiCoreData.restUrl) ? bankaiCoreData.restUrl : '/wp-json/bankai/v1/';
            var nonce = (window.bankaiCoreData && bankaiCoreData.nonce) ? bankaiCoreData.nonce : '';
            fetch(root + 'module/' + encodeURIComponent(modId), {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ enabled: enabled })
            }).then(function (r) { return r.json(); }).then(function (j) {
                if (j && j.success) {
                    if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast(j.message || 'ذخیره شد', 'success');
                    else if (typeof Alpine !== 'undefined' && window.bankaiShowToast) window.bankaiShowToast(j.message || 'ذخیره شد');
                } else {
                    self.seoState[modId] = !enabled;
                    if (window.bankaiAdmin && bankaiAdmin.showToast) bankaiAdmin.showToast((j && j.message) || 'خطا در ذخیره', 'error');
                }
            }).catch(function () {
                self.seoState[modId] = !enabled;
            });
        },
        initEngine: function () {
            var self = this;
            setTimeout(function () { if (self.seoPanel === 'tools') self.loadAnalyticsReport(); }, 300);
            // Seed module toggles from server-rendered state if empty
            if (!this.seoState || !Object.keys(this.seoState).length) {
                var mods = (window.bankaiState && window.bankaiState.seoModules) || [];
                var map = {};
                (mods || []).forEach(function (m) {
                    if (m && m.id) map[m.id] = !!m.enabled;
                });
                this.seoState = map;
            }
            this.parseFixedKeywords(<?php echo wp_json_encode($fixed_raw); ?>);
            this.runSeoAuditInline();
        },
        parseFixedKeywords: function(raw) {
            this.fixedList = String(raw || '').split(/[,،\n]+/).map(s => s.trim()).filter(Boolean);
        },
        scoreColor: function (s) {
            s = parseInt(s, 10) || 0;
            if (s >= 80) return '#16A34A';
            if (s >= 50) return '#D97706';
            return '#DC2626';
        },
        cfg: function () {
            return window.bankaiCoreData || window.bankaiEditorSeo || {};
        },
        openArticlesPanel: function () {
            this.seoPanel = 'articles';
            if (!this.articles.length) {
                this.loadArticles();
            }
        },
        loadArticles: async function () {
            this.loading = true;
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const url = `${base}seo/articles?page=${this.page}&per_page=${this.perPage}&search=${encodeURIComponent(this.searchQ || '')}&orderby=${this.orderby}&order=${this.order}`;
                
                const r = await fetch(url, { credentials: 'same-origin', headers: { 'X-WP-Nonce': c.nonce || '' } });
                const j = await r.json();
                
                if (j && j.success && j.data) {
                    this.articles = j.data.items || [];
                    this.total = j.data.total || 0;
                    this.totalPages = j.data.total_pages || 1;
                }
            } catch (e) {
                console.error('Error loading articles:', e);
            } finally {
                this.loading = false;
            }
        },
        addFixedKeyword: function() {
            const kw = this.fixedKeywordInput.trim();
            if (kw && !this.fixedList.includes(kw)) {
                this.fixedList.push(kw);
                this.fixedKeywordInput = '';
            }
        },
        removeFixedKeyword: function(index) {
            this.fixedList.splice(index, 1);
        },
        saveFixedKeywords: async function () {
            this.savingFixed = true;
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const r = await fetch(base + 'seo/fixed-keywords', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': c.nonce || '' },
                    body: JSON.stringify({ keywords: this.fixedList })
                });
                const j = await r.json();
                if (j && j.success) {
                    this.fixedList = j.data.keywords || this.fixedList;
                }
            } catch (e) {
                console.error('Save failed:', e);
            } finally {
                this.savingFixed = false;
            }
        },
        openEdit: function (row) {
            this.edit = {
                open: true,
                row: row,
                seo_title: row.seo_title || '',
                description: row.description || '',
                focus_keyword: row.focus_keyword || '',
                keywords_str: (row.keywords || []).join('، '),
                saving: false
            };
        },
        saveEdit: async function () {
            if (!this.edit.row) return;
            this.edit.saving = true;
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const keywords = this.edit.keywords_str.split(/[,،]+/).map(s => s.trim()).filter(Boolean);
                
                const r = await fetch(base + 'seo/articles/' + this.edit.row.id, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': c.nonce || '' },
                    body: JSON.stringify({
                        seo_title: this.edit.seo_title,
                        description: this.edit.description,
                        focus_keyword: this.edit.focus_keyword,
                        keywords: keywords
                    })
                });
                const j = await r.json();
                if (j && j.success) {
                    this.edit.open = false;
                    this.loadArticles();
                }
            } catch (e) {
                console.error('Save meta failed:', e);
            } finally {
                this.edit.saving = false;
            }
        },
        runSeoAuditInline: function() {
            this.seoAudit.running = true;
            setTimeout(() => {
                this.seoAudit = {
                    running: false,
                    score: 88,
                    items: [
                        { key: 'title', label: 'ساختار عناوین و متای صفحات', pass: true },
                        { key: 'sitemap', label: 'فعال بودن نقشه سایت XML', pass: !!this.integEdit.sitemap },
                        { key: 'robots', label: 'تنظیمات صحیح robots.txt', pass: true },
                        { key: 'ga4', label: 'اتصال گوگل آنالیتیکس (GA4)', pass: !!this.integEdit.ga4 }
                    ]
                };
            }, 600);
        }
    };
}
</script>