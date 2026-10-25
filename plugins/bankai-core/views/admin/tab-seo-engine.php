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

<div id="tab-seo-engine" class="bankai-tab-pane bk-orange-shell" :class="{ 'is-active-tab': activeTab === 'seo-engine' }" x-show="activeTab === 'seo-engine'" x-cloak
     x-data="bankaiSeoEngineData"
     x-init="initEngine()">

  <header class="bk-top-head">
    <div class="bk-top-head__brand">
      <span class="bk-top-head__logo"><iconify-icon icon="solar:magnifer-bold-duotone" width="24" height="24"></iconify-icon></span>
      <div>
        <strong>موتور سئو و اسکیما</strong>
        <small>JSON-LD · OG · Sitemap · Analytics</small>
      </div>
    </div>
    <div class="bk-top-head__actions">
      <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="runAutoWizard()" :disabled="wizardBusy">
        <iconify-icon icon="solar:magic-stick-3-bold-duotone" width="16"></iconify-icon>
        <span x-text="wizardBusy ? 'در حال راه‌اندازی…' : 'ویزارد راه‌اندازی'"></span>
      </button>
    </div>
  </header>

  <nav class="bk-top-tabs" role="tablist">
    <button type="button" class="bk-top-tab" :class="{ 'is-active': seoPanel === 'tools' }" @click="seoPanel = 'tools'; loadSitewide(); if (!an._loaded) loadAnalyticsReport()">
      <iconify-icon icon="solar:widget-2-bold-duotone" width="18"></iconify-icon>
      <span>ماژول‌های سئو</span>
    </button>
    <button type="button" class="bk-top-tab" :class="{ 'is-active': seoPanel === 'analytics' }" @click="seoPanel = 'analytics'">
      <iconify-icon icon="solar:chart-bold-duotone" width="18"></iconify-icon>
      <span>آنالیتیکس</span>
    </button>
    <button type="button" class="bk-top-tab" :class="{ 'is-active': seoPanel === 'import' }" @click="seoPanel = 'import'; initSeoImport()">
      <iconify-icon icon="solar:refresh-bold-duotone" width="18"></iconify-icon>
      <span>همگام‌سازی</span>
    </button>
  </nav>

  <div class="bk-top-body">
    <!-- Site Analytics inside SEO modules -->
    <div x-show="seoPanel === 'analytics'" x-cloak class="bk-analytics-dash bankai-card"
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
    <div class="bankai-card bk-seo-audit-card" x-show="seoPanel === 'tools'" x-cloak style="position:relative">
        <div class="bk-seo-audit-loading" x-show="seoAudit.running" x-cloak>
          <span class="bk-btn-spin" style="width:28px;height:28px;border-width:3px;border-color:#fc4c2f transparent #fc4c2f transparent"></span>
          <strong>در حال بررسی واقعی وضعیت سئو…</strong>
          <small>نقشه سایت · robots · llms · آنالیتیکس · برند</small>
        </div>
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
                    <div class="bk-seo-site-hero" style="grid-column:1/-1;">
                    <div>
                        <div style="font-weight:800;font-size:18px;color:#0F172A;margin-bottom:6px;">وضعیت کل سئوی سایت</div>
                        <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;">ممیزی فنی، نقشه سایت، robots و اتصال ابزارهای وب‌مستر را از اینجا کنترل کنید.</p>
                    </div>
                </div>
                <div style="font-weight:800;font-size:16px;color:#0F172A;display:none;">وضعیت کل سئوی سایت</div>
                    <div style="font-size:12px;color:#64748B;margin-top:3px;" x-text="(seoAudit && seoAudit.running) ? 'در حال پروب واقعی endpointها…' : ((seoAudit.passed || 0) + ' از ' + (seoAudit.total || ((seoAudit.items||[]).length)) + ' بررسی ضروری پاس شد')">—</div>
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
                        <small class="bk-audit-detail" x-show="item.detail" x-text="item.detail"></small>
                        <a x-show="item.link" :href="item.link" target="_blank" rel="noopener" class="bk-audit-link">مشاهده</a>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    <!-- SEO Tools Panel -->
    <div x-show="seoPanel === 'tools'" x-cloak>

        <!-- 1) Sitewide SEO -->
        <h3 class="bk-section-title" style="display:flex;align-items:center;gap:8px">
          <iconify-icon icon="solar:global-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
          سئوی سراسری سایت
        </h3>
        <div class="bk-site-conn-strip" style="margin-bottom:20px">
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico"><iconify-icon icon="solar:map-bold-duotone" width="22"></iconify-icon></div>
                <div>
                    <strong>نقشه سایت XML</strong>
                    <p>ایندکس خودکار پست و برگه</p>
                    <a class="bk-guide-link" href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank" rel="noopener">مشاهده sitemap ↗</a>
                    <a class="bk-guide-link" href="https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview" target="_blank" rel="noopener">راهنما ↗</a>
                </div>
            </div>
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico"><iconify-icon icon="solar:document-text-bold-duotone" width="22"></iconify-icon></div>
                <div>
                    <strong>llms.txt</strong>
                    <p>مانیفست برای موتورهای AI</p>
                    <a class="bk-guide-link" href="<?php echo esc_url(home_url('/llms.txt')); ?>" target="_blank" rel="noopener">مشاهده llms.txt ↗</a>
                    <a class="bk-guide-link" href="https://llmstxt.org/" target="_blank" rel="noopener">استاندارد ↗</a>
                </div>
            </div>
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico"><iconify-icon icon="solar:robot-bold-duotone" width="22"></iconify-icon></div>
                <div>
                    <strong>robots.txt</strong>
                    <p>مدیریت خزنده‌ها</p>
                    <a class="bk-guide-link" href="<?php echo esc_url(home_url('/robots.txt')); ?>" target="_blank" rel="noopener">مشاهده ↗</a>
                    <a class="bk-guide-link" href="https://developers.google.com/search/docs/crawling-indexing/robots/intro" target="_blank" rel="noopener">راهنما ↗</a>
                </div>
            </div>
            <div class="bk-site-conn-card bankai-card">
                <div class="bk-site-conn-card__ico"><iconify-icon icon="solar:smartphone-bold-duotone" width="22"></iconify-icon></div>
                <div>
                    <strong>PWA Manifest</strong>
                    <p>نصب‌پذیری و آیکون اپ</p>
                    <a class="bk-guide-link" href="<?php echo esc_url(home_url('/manifest.webmanifest')); ?>" target="_blank" rel="noopener">مشاهده ↗</a>
                </div>
            </div>
        </div>

        <div class="bk-seo-grid-2" style="margin-bottom:24px">
          <div class="bankai-card" style="padding:18px">
            <div style="font-weight:800;font-size:14px;margin-bottom:12px;display:flex;align-items:center;gap:8px">
              <iconify-icon icon="solar:buildings-2-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
              سازمان / برند (Schema)
            </div>
            <label class="bk-seo-label">نام سازمان</label>
            <input type="text" class="bk-seo-input" x-model="sw.settings.org_name" placeholder="<?php echo esc_attr($site_name); ?>">
            <label class="bk-seo-label">لوگو سازمان</label>
            <div class="bk-logo-picker">
              <div class="bk-logo-preview" x-show="sw.settings.org_logo">
                <img :src="sw.settings.org_logo" alt="logo" style="max-width:96px;max-height:96px;border-radius:10px;border:1px solid #e5e5e5;background:#fff">
              </div>
              <div class="bk-logo-actions">
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="pickOrgLogo()">
                  <iconify-icon icon="solar:gallery-add-bold-duotone" width="16"></iconify-icon>
                  انتخاب از رسانه
                </button>
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" x-show="sw.settings.org_logo" @click="clearOrgLogo()">حذف</button>
              </div>
              <input type="hidden" x-model="sw.settings.org_logo">
            </div>
            <label class="bk-seo-label">شبکه‌های اجتماعی (هر خط یک URL)</label>
            <textarea class="bk-seo-input" rows="2" dir="ltr" x-model="sw.settings.org_same_as" placeholder="https://twitter.com/…"></textarea>
            <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="margin-top:10px" @click="saveSitewide()" :disabled="sw.saving">
              <span x-text="sw.saving ? 'ذخیره…' : 'ذخیره هویت برند'"></span>
            </button>
          </div>
          <div class="bankai-card" style="padding:18px">
            <div style="font-weight:800;font-size:14px;margin-bottom:12px;display:flex;align-items:center;gap:8px">
              <iconify-icon icon="solar:eye-closed-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
              Noindex آرشیوها
            </div>
            <p class="bk-fk-desc" style="margin:0 0 10px">جلوگیری از ایندکس صفحات کم‌ارزش برای تمرکز اعتبار روی مقالات.</p>
            <label class="bk-switch-row"><span>آرشیو نویسنده</span><input type="checkbox" x-model="sw.settings.noindex_author"></label>
            <label class="bk-switch-row"><span>آرشیو تاریخ</span><input type="checkbox" x-model="sw.settings.noindex_date"></label>
            <label class="bk-switch-row"><span>آرشیو برچسب</span><input type="checkbox" x-model="sw.settings.noindex_tag"></label>
            <label class="bk-switch-row"><span>صفحه جستجو</span><input type="checkbox" x-model="sw.settings.noindex_search"></label>
            <label class="bk-switch-row"><span>پیوست‌ها</span><input type="checkbox" x-model="sw.settings.noindex_attach"></label>
            <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="margin-top:10px" @click="saveSitewide()" :disabled="sw.saving">ذخیره Noindex</button>
          </div>
        </div>

        <!-- 2) Modules -->
        <h3 class="bk-section-title" style="display:flex;align-items:center;gap:8px">
          <iconify-icon icon="solar:widget-2-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
          ماژول‌های فعال سئو و اسکیما
        </h3>
        <div class="bk-seo-grid-3" style="margin-bottom:28px;">
            <?php foreach ($seo_mods as $mod):
                $mod_id   = esc_attr($mod['id'] ?? '');
                $title_fa = esc_html($mod['title_fa'] ?? $mod['title'] ?? '');
                $desc_fa  = esc_html($mod['description_fa'] ?? $mod['description'] ?? '');
                $badge    = esc_html($mod['badge'] ?? '');
                $badge_c  = esc_attr($mod['badge_color'] ?? '#0969DA');
                $icon     = $mod['icon'] ?? '⚡';
                $mod_link = '';
                if ($mod_id === 'sitemap_pro') {
                    $mod_link = home_url('/sitemap.xml');
                } elseif ($mod_id === 'llms_txt_builder') {
                    $mod_link = home_url('/llms.txt');
                }
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
                    <?php if ($mod_link): ?>
                    <a class="bk-seo-btn-sm" href="<?php echo esc_url($mod_link); ?>" target="_blank" rel="noopener">مشاهده خروجی ↗</a>
                    <?php else: ?>
                    <button type="button" class="bk-seo-btn-sm" @click="openSeoDrawer('<?php echo $mod_id; ?>', '<?php echo esc_js($title_fa); ?>')">تنظیمات</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 3) Integrations -->
        <h3 class="bk-section-title" style="display:flex;align-items:center;gap:8px">
          <iconify-icon icon="solar:link-circle-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
          یکپارچه‌سازی و ابزارهای وب‌مستر
        </h3>
        <div class="bk-seo-grid-2" style="margin-bottom:28px;">
            <div class="bankai-card bk-card-with-guide" style="padding:20px;">
                <a class="bk-card-guide-ico" href="https://support.google.com/analytics/answer/9539598" target="_blank" rel="noopener" title="راهنمای دریافت GA4">
                  <iconify-icon icon="solar:question-circle-bold-duotone" width="18"></iconify-icon>
                </a>
                <div style="font-weight:800;font-size:14px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                    <iconify-icon icon="solar:chart-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
                    گوگل و ابزارهای تحلیلی
                </div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label class="bk-seo-label">GA4 Measurement ID</label>
                        <input type="text" class="bk-seo-input" x-model="integEdit.ga4" placeholder="G-XXXXXXXX" dir="ltr">
                        <p class="bk-field-hint">مستقیم: <a href="https://analytics.google.com/" target="_blank" rel="noopener">analytics.google.com</a> → Admin → Data Streams → Measurement ID</p>
                    </div>
                    <div>
                        <label class="bk-seo-label">Google Tag Manager</label>
                        <input type="text" class="bk-seo-input" x-model="integEdit.gtm" placeholder="GTM-XXXX" dir="ltr">
                        <p class="bk-field-hint">مستقیم: <a href="https://tagmanager.google.com/" target="_blank" rel="noopener">tagmanager.google.com</a></p>
                    </div>
                    <div>
                        <label class="bk-seo-label">Google Site Verification</label>
                        <input type="text" class="bk-seo-input" x-model="integEdit.gsc" placeholder="محتوای meta content" dir="ltr">
                        <p class="bk-field-hint">مستقیم: <a href="https://search.google.com/search-console" target="_blank" rel="noopener">Search Console</a> → Ownership verification</p>
                    </div>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="align-self:flex-start;"
                            @click="saveIntegration({ _key:'google', google_analytics_id: integEdit.ga4, ga4_measurement_id: integEdit.ga4, google_tag_manager_id: integEdit.gtm, google_site_verification: integEdit.gsc })"
                            :disabled="integSaving.google">
                        <span x-text="integSaving.google ? 'در حال ذخیره…' : 'ذخیره تنظیمات گوگل'"></span>
                    </button>
                </div>
            </div>

            <div class="bankai-card bk-card-with-guide" style="padding:20px;">
                <a class="bk-card-guide-ico" href="https://www.bing.com/webmasters/about" target="_blank" rel="noopener" title="راهنمای Bing">
                  <iconify-icon icon="solar:question-circle-bold-duotone" width="18"></iconify-icon>
                </a>
                <div style="font-weight:800;font-size:14px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                    <iconify-icon icon="solar:global-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
                    موتورهای جستجو
                </div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label class="bk-seo-label">Bing Webmaster</label>
                        <input type="text" class="bk-seo-input" x-model="integEdit.bing" dir="ltr" placeholder="کد تأیید Bing">
                        <p class="bk-field-hint">مستقیم: <a href="https://www.bing.com/webmasters" target="_blank" rel="noopener">bing.com/webmasters</a></p>
                    </div>
                    <div>
                        <label class="bk-seo-label">Yandex Verification</label>
                        <input type="text" class="bk-seo-input" x-model="integEdit.yandex" dir="ltr">
                        <p class="bk-field-hint">مستقیم: <a href="https://webmaster.yandex.com/" target="_blank" rel="noopener">webmaster.yandex.com</a></p>
                    </div>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" style="align-self:flex-start;"
                            @click="saveIntegration({ _key:'other', bing_webmaster: integEdit.bing, yandex_verification: integEdit.yandex })"
                            :disabled="integSaving.other">
                        <span x-text="integSaving.other ? 'در حال ذخیره…' : 'ذخیره'"></span>
                    </button>
                </div>
            </div>

            <div class="bankai-card bk-card-with-guide" style="padding:20px;grid-column:1 / -1;">
                <a class="bk-card-guide-ico" href="https://developers.google.com/search/docs/crawling-indexing/robots/create-robots-txt" target="_blank" rel="noopener" title="راهنمای robots.txt">
                  <iconify-icon icon="solar:question-circle-bold-duotone" width="18"></iconify-icon>
                </a>
                <div style="font-weight:800;font-size:14px;margin-bottom:10px;display:flex;align-items:center;gap:8px">
                  <iconify-icon icon="solar:document-text-bold-duotone" width="18" style="color:#fc4c2f"></iconify-icon>
                  فایل robots.txt
                </div>
                <p class="bk-field-hint">اگر خالی بگذارید، وردپرس رفتار پیش‌فرض را اعمال می‌کند. برای مسدود کردن مسیر خاص از <code>Disallow:</code> استفاده کنید.</p>
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

    <!-- Sync Wizard Rank Math / Yoast — centered flow -->
    <div x-show="seoPanel === 'import'" x-cloak class="bk-sync-wizard">
        <div class="bk-sync-shell">
            <div class="bk-sync-hero">
                <h3>همگام‌سازی سئو با Rank Math / Yoast</h3>
                <p>ابتدا افزونه مبدأ/مقصد را انتخاب کنید، سپس درون‌ریزی یا برون‌ریزی را شروع کنید.</p>
            </div>

            <!-- Step 1: provider -->
            <div x-show="seoImport.phase === 'pick' || !seoImport.selectedSource" class="bk-sync-step">
                <div class="bk-sync-step__label">۱ — انتخاب افزونه</div>
                <div class="bk-sync-providers bk-sync-providers--center">
                    <button type="button" class="bk-sync-provider" :class="{ 'is-on': seoImport.sources.rankmath && seoImport.sources.rankmath.active, 'is-selected': seoImport.selectedSource === 'rankmath' }"
                        @click="seoImport.selectedSource = 'rankmath'; seoImport.direction = ''; seoImport.phase = 'direction'"
                        >
                        <span class="bk-sync-provider__badge" :class="(seoImport.sources.rankmath && (seoImport.sources.rankmath.plugin_active || seoImport.sources.rankmath.posts)) ? 'is-ok' : 'is-off'"
                              x-text="(seoImport.sources.rankmath && (seoImport.sources.rankmath.plugin_active || seoImport.sources.rankmath.posts)) ? '✓ فعال' : 'آماده انتخاب'"></span>
                        <div class="bk-sync-provider__logo bk-sync-provider__logo--rm">RM</div>
                        <strong>Rank Math</strong>
                        <small x-text="(seoImport.sources.rankmath && seoImport.sources.rankmath.posts) ? (seoImport.sources.rankmath.posts + ' مقاله دارای متا') : 'افزونه شناسایی نشد'"></small>
                    </button>
                    <button type="button" class="bk-sync-provider" :class="{ 'is-on': seoImport.sources.yoast && seoImport.sources.yoast.active, 'is-selected': seoImport.selectedSource === 'yoast' }"
                        @click="seoImport.selectedSource = 'yoast'; seoImport.direction = ''; seoImport.phase = 'direction'"
                        >
                        <span class="bk-sync-provider__badge" :class="(seoImport.sources.yoast && (seoImport.sources.yoast.plugin_active || seoImport.sources.yoast.posts)) ? 'is-ok' : 'is-off'"
                              x-text="(seoImport.sources.yoast && (seoImport.sources.yoast.plugin_active || seoImport.sources.yoast.posts)) ? '✓ فعال' : 'آماده انتخاب'"></span>
                        <div class="bk-sync-provider__logo bk-sync-provider__logo--yo">Y</div>
                        <strong>Yoast SEO</strong>
                        <small x-text="(seoImport.sources.yoast && seoImport.sources.yoast.posts) ? (seoImport.sources.yoast.posts + ' مقاله دارای متا') : 'افزونه شناسایی نشد'"></small>
                    </button>
                </div>
            </div>

            <!-- Step 2: import / export -->
            <div x-show="seoImport.phase === 'direction' && seoImport.selectedSource" class="bk-sync-step" x-cloak>
                <div class="bk-sync-step__label">۲ — جهت همگام‌سازی
                    <button type="button" class="bk-sync-back" @click="seoImport.phase = 'pick'; seoImport.selectedSource = ''">← تغییر افزونه</button>
                </div>
                <div class="bk-sync-dir-row">
                    <button type="button" class="bk-sync-dir-card" @click="seoImport.direction = 'import'; selectSeoSource(seoImport.selectedSource)">
                        <div class="bk-sync-dir-card__art bk-sync-dir-card__art--in" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </div>
                        <strong>درون‌ریزی</strong>
                        <small x-text="(seoImport.selectedSource === 'yoast' ? 'Yoast' : 'Rank Math') + ' → Bankai'"></small>
                        <span class="bk-sync-dir-card__cta">شروع استخراج</span>
                    </button>
                    <button type="button" class="bk-sync-dir-card" @click="seoImport.direction = 'export'; selectSeoSource(seoImport.selectedSource)">
                        <div class="bk-sync-dir-card__art bk-sync-dir-card__art--out" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </div>
                        <strong>برون‌ریزی</strong>
                        <small x-text="'Bankai → ' + (seoImport.selectedSource === 'yoast' ? 'Yoast' : 'Rank Math')"></small>
                        <span class="bk-sync-dir-card__cta">شروع ارسال</span>
                    </button>
                </div>
                <label class="bk-sync-overwrite">
                    <input type="checkbox" x-model="seoImport.overwrite">
                    در صورت وجود، فیلدهای قبلی بازنویسی شوند
                </label>
            </div>

            <!-- Progress + cards -->
            <div x-show="seoImport.phase === 'list' || seoImport.phase === 'run' || seoImport.phase === 'done'" class="bk-sync-run" x-cloak>
                <div class="bk-sync-loader" x-show="seoImport.running">
                    <div class="bk-sync-spinner"></div>
                    <div>
                        <strong x-text="seoImport.stepText || 'در حال پردازش…'"></strong>
                        <div class="bk-sync-bar" style="margin-top:10px;"><div class="bk-sync-bar__fill" :style="'width:' + (seoImport.progress || 0) + '%'"></div></div>
                        <small x-text="(seoImport.processed || 0) + ' / ' + (seoImport.queueTotal || 0) + ' — موفق ' + (seoImport.importedCount || 0) + ' | رد شده ' + (seoImport.skippedCount || 0)"></small>
                    </div>
                </div>
                <div class="bk-sync-cards">
                    <template x-for="card in seoImport.cards" :key="card.id">
                        <div class="bk-sync-card" :class="'is-' + (card.status || 'pending')">
                            <div class="bk-sync-card__head">
                                <div>
                                    <strong x-text="card.title"></strong>
                                    <div class="bk-sync-card__meta" x-text="'#' + card.id + (card.type ? ' · ' + card.type : '')"></div>
                                </div>
                                <span class="bk-sync-card__status" x-text="card.statusLabel || 'در صف'"></span>
                            </div>
                            <div class="bk-sync-bar bk-sync-bar--sm" x-show="card.status === 'running' || card.progress > 0">
                                <div class="bk-sync-bar__fill" :style="'width:' + (card.progress || 0) + '%'"></div>
                            </div>
                            <ul class="bk-sync-check" x-show="card.checklist && card.checklist.length">
                                <template x-for="item in card.checklist" :key="item.id">
                                    <li :class="'is-' + item.status">
                                        <span x-text="item.status === 'done' ? '✓' : (item.status === 'skip' ? '–' : '…')"></span>
                                        <span x-text="item.label"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
                <div class="bk-sync-done" x-show="seoImport.done || seoImport.phase === 'done'">
                    <div class="bk-sync-done__ico">✓</div>
                    <strong x-text="seoImport.doneTitle || 'همگام‌سازی کامل شد'"></strong>
                    <p x-text="seoImport.doneMessage || ''"></p>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="seoImport.phase='pick'; seoImport.selectedSource=''; seoImport.direction=''; seoImport.done=false; seoImport.cards=[]; seoImport.progress=0;">شروع مجدد</button>
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
    <div x-show="edit.open" x-cloak class="bk-seo-modal-bg" style="display:none" @click.self="edit.open = false">
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
      </div><!-- /.bk-top-body -->




  <!-- Module Settings Drawer -->
  <div class="bk-mod-drawer-overlay"
       :class="{ 'is-open': modDrawer.open }"
       x-show="modDrawer.open"
       x-cloak
       style="display:none"
       @click.self="closeSeoDrawer()"
       @keydown.escape.window="modDrawer.open && closeSeoDrawer()">
    <div class="bk-mod-drawer bk-mod-drawer--center" role="dialog" aria-modal="true" @click.stop>
      <div class="bk-mod-drawer__head">
        <div>
          <strong x-text="modDrawer.title || 'تنظیمات ماژول'"></strong>
          <small x-text="modDrawer.id" style="display:block;color:#94a3b8;font-size:11px;direction:ltr"></small>
        </div>
        <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="closeSeoDrawer()">✕</button>
      </div>
      <div class="bk-mod-drawer__body">
          <div class="bk-mod-fields">
            <label class="bk-switch-row" x-show="Object.prototype.hasOwnProperty.call(modDrawer.fields,'enabled')">
              <span>فعال بودن ماژول</span>
              <input type="checkbox" x-model="modDrawer.fields.enabled">
            </label>

            <template x-if="modDrawer.id==='auto_meta'">
              <div class="bk-mod-block">
                <label class="bk-switch-row"><span>تولید خودکار Title</span><input type="checkbox" x-model="modDrawer.fields.auto_title"></label>
                <label class="bk-switch-row"><span>تولید خودکار Description</span><input type="checkbox" x-model="modDrawer.fields.auto_description"></label>
                <label class="bk-switch-row"><span>اسکیمای Article/WebPage</span><input type="checkbox" x-model="modDrawer.fields.auto_schema"></label>
                <label class="bk-seo-label">الگوی عنوان</label>
                <input type="text" class="bk-seo-input" dir="ltr" x-model="modDrawer.fields.title_template">
                <label class="bk-seo-label">طول هدف توضیحات</label>
                <input type="number" class="bk-seo-input" x-model="modDrawer.fields.desc_length" min="70" max="320">
              </div>
            </template>

            <template x-if="modDrawer.id==='sitemap_pro'">
              <div class="bk-mod-block">
                <label class="bk-switch-row"><span>شامل تصاویر</span><input type="checkbox" x-model="modDrawer.fields.include_images"></label>
                <label class="bk-switch-row"><span>ایندکس نوشته‌ها</span><input type="checkbox" x-model="modDrawer.fields.include_posts"></label>
                <label class="bk-switch-row"><span>ایندکس برگه‌ها</span><input type="checkbox" x-model="modDrawer.fields.include_pages"></label>
                <label class="bk-switch-row"><span>ایندکس دسته‌ها/برچسب</span><input type="checkbox" x-model="modDrawer.fields.include_tax"></label>
                <label class="bk-switch-row"><span>Ping به موتورهای جستجو</span><input type="checkbox" x-model="modDrawer.fields.ping_search"></label>
                <p class="bk-field-hint">خروجی: <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank" rel="noopener"><?php echo esc_html(home_url('/sitemap.xml')); ?></a></p>
              </div>
            </template>

            <template x-if="modDrawer.id==='canonical_guard'">
              <div class="bk-mod-block">
                <label class="bk-switch-row"><span>حذف پارامترهای ردیابی از canonical</span><input type="checkbox" x-model="modDrawer.fields.strip_params"></label>
                <label class="bk-switch-row"><span>اجبار اسلش انتهایی</span><input type="checkbox" x-model="modDrawer.fields.force_trailing"></label>
              </div>
            </template>

            <template x-if="modDrawer.id==='open_graph_ai'">
              <div class="bk-mod-block">
                <label class="bk-seo-label">تصویر پیش‌فرض OG (URL)</label>
                <input type="url" class="bk-seo-input" dir="ltr" x-model="modDrawer.fields.default_image">
                <label class="bk-seo-label">نوع Twitter Card</label>
                <select class="bk-seo-input" x-model="modDrawer.fields.twitter_card">
                  <option value="summary_large_image">summary_large_image</option>
                  <option value="summary">summary</option>
                </select>
              </div>
            </template>

            <template x-if="modDrawer.id==='local_seo_schema'">
              <div class="bk-mod-block">
                <label class="bk-seo-label">نوع کسب‌وکار (Schema)</label>
                <input type="text" class="bk-seo-input" dir="ltr" x-model="modDrawer.fields.business_type">
                <label class="bk-seo-label">تلفن</label>
                <input type="text" class="bk-seo-input" x-model="modDrawer.fields.phone">
                <label class="bk-seo-label">آدرس</label>
                <textarea class="bk-seo-input" rows="2" x-model="modDrawer.fields.address"></textarea>
              </div>
            </template>

            <template x-if="modDrawer.id==='llms_txt_builder'">
              <div class="bk-mod-block">
                <label class="bk-switch-row"><span>درج فهرست نوشته‌ها</span><input type="checkbox" x-model="modDrawer.fields.include_posts"></label>
                <label class="bk-switch-row"><span>نسخه کامل llms-full.txt</span><input type="checkbox" x-model="modDrawer.fields.full_version"></label>
                <label class="bk-seo-label">حداکثر تعداد نوشته</label>
                <input type="number" class="bk-seo-input" x-model="modDrawer.fields.max_posts" min="5" max="500">
                <p class="bk-field-hint">خروجی: <a href="<?php echo esc_url(home_url('/llms.txt')); ?>" target="_blank" rel="noopener"><?php echo esc_html(home_url('/llms.txt')); ?></a></p>
              </div>
            </template>
          </div>
        <p class="bk-mod-msg" x-show="modDrawer.message" x-text="modDrawer.message"></p>
      </div>
      <div class="bk-mod-drawer__foot">
        <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="closeSeoDrawer()">انصراف</button>
        <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="saveModDrawer()" :disabled="modDrawer.saving">
          <span class="bk-btn-spin" x-show="modDrawer.saving"></span>
          <span x-text="modDrawer.saving ? 'در حال ذخیره…' : 'ذخیره تنظیمات'"></span>
        </button>
      </div>
    </div>
  </div>

</div><!-- /tab-seo-engine -->
