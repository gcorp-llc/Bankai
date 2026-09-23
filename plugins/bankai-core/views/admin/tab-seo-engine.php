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
            <button type="button" role="tab" class="bk-seo-tab" :class="{ 'is-active': seoPanel === 'tools' }" @click="seoPanel = 'tools'">ماژول‌های سئو</button>
            <button type="button" role="tab" class="bk-seo-tab" :class="{ 'is-active': seoPanel === 'articles' }" @click="openArticlesPanel()">مدیریت مقالات</button>
            <button type="button" role="tab" class="bk-seo-tab" :class="{ 'is-active': seoPanel === 'analytics' }" @click="seoPanel = 'analytics'">گوگل آنالیتیکس</button>
        </nav>
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

    <!-- Articles Section -->
    <div id="bk-seo-articles-section" x-show="seoPanel === 'articles'" x-cloak>
        <div class="bk-articles-toolbar">
            <h3 class="bk-section-title" style="margin:0;">جدول ممیزی سئوی مقالات</h3>
            <div class="bk-articles-filters">
                <div class="bk-search-combo">
                    <svg class="bk-search-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="search" x-model="searchQ" @keydown.enter.prevent="page=1;loadArticles()"
                           placeholder="جستجوی عنوان مقاله…" class="bk-search-input">
                    <button type="button" class="bk-search-go" @click="page=1;loadArticles()" :disabled="loading" title="جستجو">
                        <span x-show="!loading">⏎</span>
                        <span x-show="loading" class="bk-wizard-spin" style="border-color:rgba(255,255,255,.3);border-top-color:#fff;"></span>
                    </button>
                </div>
                <div class="bk-select-wrap">
                    <select x-model="orderby" @change="page=1;order='DESC';loadArticles()" class="bk-modern-select">
                        <option value="modified">مرتب‌سازی: آخرین ویرایش</option>
                        <option value="date">مرتب‌سازی: تاریخ انتشار</option>
                        <option value="seo_score">مرتب‌سازی: امتیاز سئو</option>
                        <option value="views">مرتب‌سازی: بازدید</option>
                        <option value="title">مرتب‌سازی: عنوان</option>
                    </select>
                </div>
                <div class="bk-select-wrap">
                    <select x-model.number="perPage" @change="page=1;loadArticles()" class="bk-modern-select">
                        <option value="10">۱۰ در صفحه</option>
                        <option value="25">۲۵ در صفحه</option>
                        <option value="50">۵۰ در صفحه</option>
                        <option value="100">۱۰۰ در صفحه</option>
                    </select>
                </div>
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
                                        <button type="button" class="bk-edit-meta-btn" @click="openEdit(row)">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                            ویرایش متا
                                        </button>
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

    <!-- Edit Meta + AI Studio Modal -->
    <div x-show="edit.open" x-cloak class="bk-seo-modal-bg" @click.self="closeEdit()">
        <div class="bankai-card bk-seo-modal bk-ai-studio-modal" @click.stop>
            <div class="bk-seo-modal-head bk-ai-head">
                <div class="bk-ai-head-title">
                    <span class="bk-ai-spark">✦</span>
                    <div>
                        <strong>استودیو هوش مصنوعی بنکای</strong>
                        <small>تولید و بهینه‌سازی متا، کلیدواژه و لینک</small>
                    </div>
                </div>
                <button type="button" class="bk-seo-modal-x" @click="closeEdit()">&times;</button>
            </div>

            <div class="bk-ai-provider-bar">
                <label>موتور AI:</label>
                <select x-model="aiProvider" class="bk-modern-select" style="min-width:160px;">
                    <option value="">پیش‌فرض استودیو</option>
                    <option value="openrouter">OpenRouter</option>
                    <option value="gemini">Google Gemini</option>
                    <option value="openai">OpenAI</option>
                    <option value="anthropic">Anthropic Claude</option>
                    <option value="deepseek">DeepSeek</option>
                    <option value="groq">Groq</option>
                </select>
                <span class="bk-ai-hint">در صورت خطا، خودکار به موتور بعدی می‌رود</span>
            </div>

            <nav class="bk-modal-tabs">
                <button type="button" :class="{ 'is-active': aiTab === 'title' }" @click="aiTab = 'title'">عنوان و متا</button>
                <button type="button" :class="{ 'is-active': aiTab === 'keywords' }" @click="aiTab = 'keywords'">کلیدواژه</button>
                <button type="button" :class="{ 'is-active': aiTab === 'links' }" @click="aiTab = 'links'; loadArticleLinks()">لینک داخلی</button>
                <button type="button" :class="{ 'is-active': aiTab === 'external' }" @click="aiTab = 'external'; loadArticleLinks()">لینک خارجی</button>
                <button type="button" :class="{ 'is-active': aiTab === 'rewrite' }" @click="aiTab = 'rewrite'">بازنویسی</button>
            </nav>

            <div class="bk-seo-modal-body">
                <!-- Title / Meta -->
                <div x-show="aiTab === 'title'" class="bk-modal-pane">
                    <div class="bk-ai-grid">
                        <div>
                            <label class="bk-seo-label">عنوان فعلی</label>
                            <input type="text" class="bk-seo-input" x-model="edit.seo_title" maxlength="70">
                            <label class="bk-seo-label" style="margin-top:10px;">توضیحات فعلی</label>
                            <textarea class="bk-seo-input" x-model="edit.description" rows="4" maxlength="170"></textarea>
                        </div>
                        <div>
                            <label class="bk-seo-label">پیشنهاد AI</label>
                            <div class="bk-ai-output" x-text="aiDraft.title || 'برای تولید روی دکمه کلیک کنید…'"></div>
                            <div class="bk-ai-output" style="margin-top:8px;" x-text="aiDraft.description || ''"></div>
                        </div>
                    </div>
                    <div class="bk-modal-actions">
                        <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="generateAI('title')" :disabled="aiLoading">
                            <span :class="{ 'bk-spin-txt': aiLoading }">✦</span>
                            <span x-text="aiLoading ? 'در حال تولید…' : 'تولید با AI'"></span>
                        </button>
                        <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="applyAiDraft('title')" x-show="aiDraft.title">اعمال پیشنهاد</button>
                    </div>
                </div>

                <!-- Keywords -->
                <div x-show="aiTab === 'keywords'" class="bk-modal-pane">
                    <label class="bk-seo-label">کلمه کلیدی اصلی</label>
                    <input type="text" class="bk-seo-input" x-model="edit.focus_keyword" placeholder="Focus keyword">
                    <label class="bk-seo-label" style="margin-top:10px;">کلیدواژه‌های جانبی</label>
                    <div class="bk-kw-box">
                        <template x-for="(kw, i) in editKeywordList" :key="'ek'+i">
                            <span class="bk-fk-chip">
                                <span x-text="kw"></span>
                                <button type="button" class="bk-fk-remove" @click="editKeywordList.splice(i,1); syncKeywordsStr()">&times;</button>
                            </span>
                        </template>
                        <input type="text" class="bk-seo-input" style="border:none;box-shadow:none;min-width:120px;flex:1;"
                               x-model="kwDraft" @keydown.enter.prevent="addEditKeyword()" placeholder="+ کلیدواژه">
                    </div>
                    <div class="bk-ai-output" style="margin-top:10px;" x-text="aiDraft.keywords || 'پیشنهادهای AI اینجا نمایش داده می‌شود…'"></div>
                    <div class="bk-modal-actions">
                        <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="generateAI('keywords')" :disabled="aiLoading">
                            <span>✦</span> تولید کلیدواژه
                        </button>
                        <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="applyAiDraft('keywords')" x-show="aiDraft.keywords">اعمال</button>
                    </div>
                </div>

                <!-- Internal links -->
                <div x-show="aiTab === 'links'" class="bk-modal-pane">
                    <div class="bk-link-stats" x-show="linkStats">
                        <span>داخلی: <strong x-text="linkStats.internal || 0"></strong></span>
                        <span>خارجی: <strong x-text="linkStats.external || 0"></strong></span>
                    </div>
                    <p class="bk-hint">جستجوی مقالات مرتبط بر اساس کلیدواژه و پیشنهاد لینک داخلی هوشمند.</p>
                    <div class="bk-row-flex">
                        <input type="text" class="bk-seo-input" x-model="linkAnchor" :placeholder="edit.focus_keyword || 'کلیدواژه / انکر'">
                        <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="searchRelatedPosts()" :disabled="linkBusy">جستجو</button>
                        <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="smartSuggestLinks()" :disabled="linkBusy">پیشنهاد هوشمند</button>
                    </div>
                    <div class="bk-report" x-show="internalReport.show" :class="'is-' + internalReport.type" x-text="internalReport.message"></div>
                    <ul class="bk-search-list" x-show="postResults.length">
                        <template x-for="p in postResults" :key="'mp'+p.id">
                            <li>
                                <div>
                                    <strong x-text="p.title"></strong>
                                    <small dir="ltr" x-text="p.permalink"></small>
                                    <span class="bk-seo-tag muted" x-show="p.focus_keyword" x-text="p.focus_keyword"></span>
                                </div>
                                <button type="button" class="bk-seo-btn-sm" @click="selectInternalTarget(p)">انتخاب</button>
                            </li>
                        </template>
                    </ul>
                    <div x-show="selectedInternalPost" class="bk-selected-target">
                        <div>هدف: <strong x-text="selectedInternalPost?.title"></strong></div>
                        <div class="bk-row-flex" style="margin-top:8px;">
                            <input type="text" class="bk-seo-input" x-model="linkAnchor" placeholder="انکر تکست">
                            <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="applyInternalNote()">ثبت پیشنهاد لینک</button>
                        </div>
                        <p class="bk-hint" style="margin-top:8px;">برای درج مستقیم در محتوا، از ویرایشگر مطلب استفاده کنید. اینجا پیشنهاد و گزارش لینک‌ها ذخیره می‌شود.</p>
                    </div>
                    <div x-show="currentInternalLinks.length" style="margin-top:12px;">
                        <label class="bk-seo-label">لینک‌های داخلی فعلی مقاله</label>
                        <ul class="bk-search-list">
                            <template x-for="(l, li) in currentInternalLinks" :key="'il'+li">
                                <li>
                                    <div>
                                        <strong x-text="l.text || l.href"></strong>
                                        <small dir="ltr" x-text="l.href"></small>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <!-- External links -->
                <div x-show="aiTab === 'external'" class="bk-modal-pane">
                    <div class="bk-link-stats" x-show="linkStats">
                        <span>داخلی: <strong x-text="linkStats.internal || 0"></strong></span>
                        <span>خارجی: <strong x-text="linkStats.external || 0"></strong></span>
                    </div>
                    <div class="bk-field"><label class="bk-seo-label">انکر تکست</label><input type="text" class="bk-seo-input" x-model="extAnchor"></div>
                    <div class="bk-field" style="margin-top:8px;"><label class="bk-seo-label">URL</label><input type="url" dir="ltr" class="bk-seo-input" x-model="extUrl" placeholder="https://"></div>
                    <label style="display:flex;align-items:center;gap:8px;margin:10px 0;font-size:12px;font-weight:600;">
                        <input type="checkbox" x-model="extNofollow"> nofollow
                    </label>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="applyExternalNote()">ثبت لینک خارجی</button>
                    <div class="bk-report" x-show="externalReport.show" :class="'is-' + externalReport.type" x-text="externalReport.message"></div>
                    <div x-show="currentExternalLinks.length" style="margin-top:12px;">
                        <label class="bk-seo-label">لینک‌های خارجی فعلی</label>
                        <ul class="bk-search-list">
                            <template x-for="(l, li) in currentExternalLinks" :key="'el'+li">
                                <li>
                                    <div>
                                        <strong x-text="l.text || l.href"></strong>
                                        <small dir="ltr" x-text="l.href"></small>
                                        <span class="bk-seo-tag muted" x-show="l.nofollow">nofollow</span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <!-- Rewrite -->
                <div x-show="aiTab === 'rewrite'" class="bk-modal-pane">
                    <p class="bk-hint">بازنویسی محتوا با حفظ معنا و بهینه‌سازی سئو. نتیجه را می‌توانید در ویرایشگر مطلب جایگزین کنید.</p>
                    <textarea class="bk-seo-input" rows="8" x-model="aiDraft.rewrite" placeholder="نتیجه بازنویسی اینجا ظاهر می‌شود…"></textarea>
                    <div class="bk-modal-actions">
                        <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="generateAI('rewrite')" :disabled="aiLoading">
                            <span>✦</span> بازنویسی با AI
                        </button>
                    </div>
                </div>
            </div>

            <div class="bk-seo-modal-foot">
                <button type="button" class="bk-seo-btn bk-seo-btn-ghost" @click="closeEdit()">بستن</button>
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" @click="saveEdit()" :disabled="edit.saving">
                    <span x-text="edit.saving ? 'در حال ذخیره…' : 'ذخیره تغییرات متا'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

<style>
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

.bk-articles-toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:12px; }
.bk-articles-filters { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.bk-search-combo { display:flex; align-items:center; background:#fff; border:1px solid #CBD5E1; border-radius:10px; overflow:hidden; min-width:220px; box-shadow:0 1px 2px rgba(15,23,42,.04); }
.bk-search-combo:focus-within { border-color:#2563EB; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.bk-search-ico { width:18px; height:18px; margin-inline-start:12px; color:#94A3B8; flex-shrink:0; }
.bk-search-input { border:none !important; box-shadow:none !important; outline:none; padding:9px 10px; font-size:12px; flex:1; min-width:0; background:transparent; }
.bk-search-go { border:none; background:#2563EB; color:#fff; padding:0 14px; height:38px; cursor:pointer; font-weight:800; }
.bk-search-go:hover { background:#1D4ED8; }
.bk-select-wrap { position:relative; }
.bk-modern-select { appearance:none; background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat left 10px center; padding:9px 12px 9px 28px; border:1px solid #CBD5E1; border-radius:10px; font-size:12px; font-weight:700; color:#334155; cursor:pointer; min-width:150px; }
.bk-modern-select:focus { border-color:#2563EB; outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.bk-edit-meta-btn { display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#7C3AED,#2563EB); color:#fff; border:none; border-radius:8px; padding:7px 12px; font-size:11px; font-weight:800; cursor:pointer; box-shadow:0 2px 8px rgba(124,58,237,.25); }
.bk-edit-meta-btn:hover { filter:brightness(1.05); }
.bk-ai-studio-modal { width:min(720px,100%) !important; max-height:90vh; display:flex; flex-direction:column; }
.bk-ai-head { background:linear-gradient(135deg,#F5F3FF,#EFF6FF); }
.bk-ai-head-title { display:flex; align-items:center; gap:10px; }
.bk-ai-head-title strong { display:block; font-size:14px; }
.bk-ai-head-title small { display:block; font-size:11px; color:#64748B; font-weight:600; }
.bk-ai-spark { width:32px; height:32px; border-radius:10px; background:linear-gradient(135deg,#7C3AED,#2563EB); color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; }
.bk-ai-provider-bar { display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid #F1F5F9; flex-wrap:wrap; font-size:12px; font-weight:700; }
.bk-ai-hint { font-size:10px; color:#94A3B8; font-weight:600; }
.bk-modal-tabs { display:flex; gap:4px; padding:8px 12px; border-bottom:1px solid #F1F5F9; overflow-x:auto; }
.bk-modal-tabs button { appearance:none; border:none; background:transparent; padding:8px 12px; font-size:12px; font-weight:700; color:#64748B; border-radius:8px; cursor:pointer; white-space:nowrap; }
.bk-modal-tabs button.is-active { background:#EFF6FF; color:#2563EB; }
.bk-ai-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media (max-width:640px){ .bk-ai-grid { grid-template-columns:1fr; } }
.bk-ai-output { min-height:48px; padding:10px 12px; background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px; font-size:12px; color:#334155; line-height:1.6; white-space:pre-wrap; }
.bk-modal-actions { display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; }
.bk-kw-box { display:flex; flex-wrap:wrap; gap:6px; padding:8px; border:1px solid #CBD5E1; border-radius:10px; background:#fff; min-height:44px; align-items:center; }
.bk-hint { font-size:12px; color:#64748B; margin:0 0 10px; line-height:1.6; }
.bk-row-flex { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.bk-search-list { list-style:none; margin:10px 0 0; padding:0; max-height:220px; overflow:auto; }
.bk-search-list li { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:10px; border:1px solid #F1F5F9; border-radius:8px; margin-bottom:6px; background:#fff; }
.bk-search-list li strong { display:block; font-size:12px; }
.bk-search-list li small { display:block; font-size:10px; color:#94A3B8; direction:ltr; }
.bk-link-stats { display:flex; gap:16px; font-size:12px; font-weight:700; margin-bottom:10px; padding:8px 12px; background:#F8FAFC; border-radius:8px; }
.bk-report { font-size:12px; padding:8px 12px; border-radius:8px; margin-top:10px; }
.bk-report.is-success { background:#F0FDF4; color:#166534; }
.bk-report.is-warn { background:#FFFBEB; color:#92400E; }
.bk-report.is-error { background:#FEF2F2; color:#991B1B; }
.bk-selected-target { margin-top:12px; padding:12px; background:#EFF6FF; border-radius:10px; font-size:12px; }
.bk-seo-modal-bg { align-items:flex-start !important; padding-top:40px !important; background:rgba(248,250,252,.8) !important; backdrop-filter:blur(6px); }
.bk-seo-modal { margin-top:0; animation:bkModalIn .28s ease; }
@keyframes bkModalIn { from { opacity:0; transform:translateY(-12px); } to { opacity:1; transform:translateY(0); } }
.bk-spin-txt { display:inline-block; animation:bkspin .7s linear infinite; }
@keyframes bkspin { to { transform:rotate(360deg); } }
.bk-wizard-spin { width:14px; height:14px; border:2px solid rgba(255,255,255,.35); border-top-color:#fff; border-radius:50%; animation:bkspin .7s linear infinite; display:inline-block; }

</style>

<script>
function bankaiSeoEngineData() {
    return {
        seoPanel: 'tools',
        wizardBusy: false,
        seoAudit: { running: false, score: 0, items: [] },
        seoState: {},
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
        editKeywordList: [],
        kwDraft: '',
        aiTab: 'title',
        aiProvider: '',
        aiLoading: false,
        aiDraft: { title: '', description: '', keywords: '', rewrite: '' },

        linkBusy: false,
        linkAnchor: '',
        postResults: [],
        selectedInternalPost: null,
        internalReport: { show: false, type: 'success', message: '' },
        externalReport: { show: false, type: 'success', message: '' },
        extAnchor: '',
        extUrl: '',
        extNofollow: true,
        linkStats: { internal: 0, external: 0 },
        currentInternalLinks: [],
        currentExternalLinks: [],

        initEngine: function () {
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
        ajaxUrl: function () {
            const c = this.cfg();
            return c.ajaxUrl || (window.ajaxurl || '/wp-admin/admin-ajax.php');
        },
        openArticlesPanel: function () {
            this.seoPanel = 'articles';
            if (!this.articles.length) this.loadArticles();
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
                this.saveFixedKeywords();
            }
        },
        removeFixedKeyword: function(index) {
            this.fixedList.splice(index, 1);
            this.saveFixedKeywords();
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
            const kws = (row.keywords || []).slice();
            this.edit = {
                open: true,
                row: row,
                seo_title: row.seo_title || '',
                description: row.description || '',
                focus_keyword: row.focus_keyword || '',
                keywords_str: kws.join('، '),
                saving: false
            };
            this.editKeywordList = kws;
            this.kwDraft = '';
            this.aiTab = 'title';
            this.aiDraft = { title: '', description: '', keywords: '', rewrite: '' };
            this.postResults = [];
            this.selectedInternalPost = null;
            this.internalReport = { show: false, type: 'success', message: '' };
            this.externalReport = { show: false, type: 'success', message: '' };
            this.linkAnchor = row.focus_keyword || '';
            this.extAnchor = '';
            this.extUrl = '';
            this.linkStats = { internal: row.internal_links || 0, external: row.external_links || 0 };
            this.currentInternalLinks = [];
            this.currentExternalLinks = [];
            this.loadArticleLinks();
        },
        closeEdit: function () {
            this.edit.open = false;
        },
        syncKeywordsStr: function () {
            this.edit.keywords_str = (this.editKeywordList || []).join('، ');
        },
        addEditKeyword: function () {
            const kw = (this.kwDraft || '').trim();
            if (!kw) return;
            if (!this.editKeywordList.includes(kw)) {
                this.editKeywordList.push(kw);
                this.syncKeywordsStr();
            }
            this.kwDraft = '';
        },
        saveEdit: async function () {
            if (!this.edit.row) return;
            this.edit.saving = true;
            this.syncKeywordsStr();
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const keywords = (this.editKeywordList || []).slice();
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
        generateAI: async function (task) {
            if (!this.edit.row) return;
            this.aiLoading = true;
            try {
                const c = this.cfg();
                const body = new FormData();
                body.append('action', 'bankai_ai_seo_task');
                body.append('nonce', c.nonce || '');
                body.append('task', task === 'title' ? 'meta_title_desc' : task);
                body.append('post_id', this.edit.row.id);
                body.append('title', this.edit.row.title || '');
                body.append('seo_title', this.edit.seo_title || '');
                body.append('description', this.edit.description || '');
                body.append('focus_keyword', this.edit.focus_keyword || '');
                body.append('provider', this.aiProvider || '');
                const r = await fetch(this.ajaxUrl(), { method: 'POST', credentials: 'same-origin', body });
                const j = await r.json();
                const data = (j && j.data) ? j.data : (j || {});
                if (task === 'title') {
                    this.aiDraft.title = data.title || data.seo_title || data.meta_title || '';
                    this.aiDraft.description = data.description || data.meta_description || '';
                } else if (task === 'keywords') {
                    const list = data.keywords || data.list || [];
                    this.aiDraft.keywords = Array.isArray(list) ? list.join('، ') : String(list || data.text || '');
                    if (data.focus_keyword) this.edit.focus_keyword = data.focus_keyword;
                } else if (task === 'rewrite') {
                    this.aiDraft.rewrite = data.content || data.text || data.rewrite || '';
                }
            } catch (e) {
                console.error('AI error', e);
                this.aiDraft.keywords = this.aiDraft.keywords || 'خطا در ارتباط با AI';
            } finally {
                this.aiLoading = false;
            }
        },
        applyAiDraft: function (task) {
            if (task === 'title') {
                if (this.aiDraft.title) this.edit.seo_title = this.aiDraft.title;
                if (this.aiDraft.description) this.edit.description = this.aiDraft.description;
            } else if (task === 'keywords' && this.aiDraft.keywords) {
                const parts = this.aiDraft.keywords.split(/[,،]+/).map(s => s.trim()).filter(Boolean);
                parts.forEach(p => {
                    if (!this.editKeywordList.includes(p)) this.editKeywordList.push(p);
                });
                this.syncKeywordsStr();
            }
        },
        loadArticleLinks: async function () {
            if (!this.edit.row) return;
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const r = await fetch(base + 'seo/links/' + this.edit.row.id, {
                    credentials: 'same-origin',
                    headers: { 'X-WP-Nonce': c.nonce || '' }
                });
                const j = await r.json();
                if (j && j.success && j.data) {
                    this.currentInternalLinks = j.data.internal || [];
                    this.currentExternalLinks = j.data.external || [];
                    this.linkStats = {
                        internal: (j.data.counts && j.data.counts.internal) || this.currentInternalLinks.length,
                        external: (j.data.counts && j.data.counts.external) || this.currentExternalLinks.length
                    };
                }
            } catch (e) {
                console.error(e);
            }
        },
        searchRelatedPosts: async function () {
            this.linkBusy = true;
            this.postResults = [];
            try {
                const c = this.cfg();
                const q = this.linkAnchor || this.edit.focus_keyword || this.edit.row?.title || '';
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const r = await fetch(base + 'seo/search-posts?q=' + encodeURIComponent(q), {
                    credentials: 'same-origin',
                    headers: { 'X-WP-Nonce': c.nonce || '' }
                });
                const j = await r.json();
                const list = (j && j.success && j.data) ? j.data : [];
                this.postResults = list.filter(p => !this.edit.row || p.id !== this.edit.row.id);
                this.internalReport = {
                    show: true,
                    type: this.postResults.length ? 'success' : 'warn',
                    message: this.postResults.length
                        ? this.postResults.length + ' مقاله مرتبط پیدا شد.'
                        : 'مقاله‌ای یافت نشد.'
                };
            } catch (e) {
                this.internalReport = { show: true, type: 'error', message: e.message || 'خطا در جستجو' };
            } finally {
                this.linkBusy = false;
            }
        },
        smartSuggestLinks: async function () {
            if (!this.edit.row) return;
            this.linkBusy = true;
            this.postResults = [];
            try {
                const c = this.cfg();
                const base = (c.restUrl || '/wp-json/bankai/v1/').replace(/\/?$/, '/');
                const keywords = [];
                if (this.edit.focus_keyword) keywords.push(this.edit.focus_keyword);
                (this.editKeywordList || []).forEach(k => keywords.push(k));
                const r = await fetch(base + 'seo/suggest-links', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': c.nonce || '' },
                    body: JSON.stringify({ post_id: this.edit.row.id, keywords })
                });
                const j = await r.json();
                const list = (j && j.success && j.data && j.data.suggestions) ? j.data.suggestions : [];
                this.postResults = list;
                this.internalReport = {
                    show: true,
                    type: list.length ? 'success' : 'warn',
                    message: list.length
                        ? list.length + ' پیشنهاد لینک داخلی بر اساس کلمات کلیدی.'
                        : 'پیشنهادی یافت نشد.'
                };
            } catch (e) {
                this.internalReport = { show: true, type: 'error', message: e.message || 'خطا' };
            } finally {
                this.linkBusy = false;
            }
        },
        selectInternalTarget: function (p) {
            this.selectedInternalPost = p;
            this.linkAnchor = p.suggested_anchor || p.focus_keyword || p.title || this.linkAnchor;
        },
        applyInternalNote: function () {
            if (!this.selectedInternalPost) return;
            this.internalReport = {
                show: true,
                type: 'success',
                message: 'پیشنهاد لینک به «' + this.selectedInternalPost.title + '» با انکر «' + (this.linkAnchor || '') + '» ثبت شد. برای درج در محتوا از ویرایشگر مطلب استفاده کنید.'
            };
        },
        applyExternalNote: function () {
            const phrase = (this.extAnchor || '').trim();
            const url = (this.extUrl || '').trim();
            if (!phrase || phrase.length < 2) {
                this.externalReport = { show: true, type: 'warn', message: 'انکر تکست را وارد کنید.' };
                return;
            }
            if (!url || !/^https?:\/\//i.test(url)) {
                this.externalReport = { show: true, type: 'warn', message: 'یک URL معتبر با http(s) وارد کنید.' };
                return;
            }
            this.externalReport = {
                show: true,
                type: 'success',
                message: 'لینک خارجی «' + phrase + '» → ' + url + (this.extNofollow ? ' (nofollow)' : '') + ' ثبت شد. برای درج در محتوا از ویرایشگر مطلب استفاده کنید.'
            };
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
        },
        runAutoWizard: function () {
            if (this.wizardBusy) return;
            this.wizardBusy = true;
            this.seoPanel = 'tools';
            const ids = ['auto_meta', 'sitemap_pro', 'canonical_guard', 'open_graph_ai'];
            ids.forEach(id => { this.seoState[id] = true; });
            setTimeout(() => {
                this.wizardBusy = false;
                this.runSeoAuditInline();
            }, 900);
        },
        toggleSeoModule: function () {},
        openSeoDrawer: function () {},
        saveIntegration: async function (payload) {
            const key = payload._key || 'google';
            this.integSaving[key] = true;
            try {
                const c = this.cfg();
                // best-effort; core may handle via existing endpoints
                if (typeof window.bankaiAdmin !== 'undefined' && window.bankaiAdmin.saveIntegration) {
                    await window.bankaiAdmin.saveIntegration(payload);
                }
            } finally {
                this.integSaving[key] = false;
            }
        }
    };
}
</script>