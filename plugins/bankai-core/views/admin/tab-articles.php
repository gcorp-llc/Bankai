<?php
/**
 * Admin: Content hub — SEO content + AI generate (v179)
 * @package Bankai
 */
defined('ABSPATH') || exit;
?>
<script type="application/json" id="bk-wiz-providers-json"><?php
$__bp = [];
if (class_exists('Bankai_AI_Studio')) {
    foreach (Bankai_AI_Studio::instance()->provider_status_list() as $pr) {
        if (empty($pr['has_key'])) continue;
        $__bp[] = ['id' => $pr['id'] ?? '', 'name' => $pr['name'] ?? ($pr['id'] ?? ''), 'has_key' => true];
    }
}
echo wp_json_encode($__bp, JSON_UNESCAPED_UNICODE);
?></script>

<div id="tab-articles" class="bankai-tab-pane" :class="{ 'is-active-tab': activeTab === 'articles' }" x-show="activeTab === 'articles'" x-cloak
     x-data="bankaiArticlesHub" x-init="init()">

    <div class="bk-art-main">
        <div class="bk-art-head">
            <div>
                <h2>
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="22" height="22"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    مدیریت محتوا
                </h2>
                <p>سئوی محتوا با هوش مصنوعی و تولید مقاله — ویرایش در وردپرس</p>
            </div>
            <button type="button" class="bk-btn bk-btn-primary" @click="panel='generate'">تولید مقاله با AI</button>
        </div>

        <div class="bk-capsule-tabs bk-art-tabs" role="tablist">
            <button type="button" class="bk-capsule-tab" :class="{'is-active': panel==='list'}" @click="panel='list'; loadArticles()">
                <span class="bk-capsule-label">سئو محتوا</span>
            </button>
            <button type="button" class="bk-capsule-tab" :class="{'is-active': panel==='generate'}" @click="panel='generate'">
                <span class="bk-capsule-label">تولید با AI</span>
            </button>
        </div>

        <!-- LIST -->
        <div class="bk-art-card" x-show="panel==='list'" x-cloak>
            <div class="bk-art-toolbar">
                <div class="bk-art-search">
                    <input type="search" x-model="searchQ" @keydown.enter="page=1;loadArticles()" placeholder="جستجوی عنوان…">
                    <button type="button" class="bk-btn-sm" @click="page=1;loadArticles()">جستجو</button>
                </div>
                <select x-model="orderby" @change="loadArticles()">
                    <option value="date">تاریخ انتشار</option>
                    <option value="modified">آخرین ویرایش</option>
                    <option value="seo_score">امتیاز سئو</option>
                    <option value="views">بازدید</option>
                    <option value="title">عنوان</option>
                </select>
                <select x-model="postType" @change="page=1;loadArticles()">
                    <option value="post">مقالات</option>
                    <option value="page">برگه‌ها</option>
                    <option value="any">همه</option>
                </select>
            </div>

                        <!-- AUDIT PANEL -->
            <div class="bk-audit-panel" x-show="auditDrawer.open" x-cloak>
                <div class="bk-audit-panel__top">
                <div class="bk-audit-header-grid">
                    <div class="bk-audit-header-main">
                        <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start">
                            <div>
                                <span class="bk-audit-panel__tag">SEO · AEO · GEO</span>
                                <h3 class="bk-audit-panel__title" x-text="auditDrawer.title || 'آنالیز صفحه'"></h3>
                                <p class="bk-audit-panel__sub" x-show="auditDrawer.phase==='run'" x-text="auditDrawer.step || 'در حال آنالیز…'"></p>
                                <p class="bk-audit-panel__sub" x-show="auditDrawer.phase==='done'">گزارش آماده است — روی هر آیتم کلیک کنید تا راهنما باز شود.</p>
                            </div>
                            <button type="button" class="bk-abtn-icon" @click="closeAuditPanel()" title="بستن">×</button>
                        </div>
                        <div class="bk-audit-panel__rings under-title" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                <div class="bk-score-ring-wrap is-lg">
                                    <svg class="bk-score-ring" viewBox="0 0 36 36">
                                        <circle class="track" cx="18" cy="18" r="14"></circle>
                                        <circle class="val" :class="scoreTone((auditDrawer.summary && auditDrawer.summary.seo) || 0)" cx="18" cy="18" r="14" stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset((auditDrawer.summary && auditDrawer.summary.seo) || 0)"></circle>
                                    </svg>
                                    <span class="bk-score-ring-num" :class="scoreTone((auditDrawer.summary && auditDrawer.summary.seo) || 0)" x-text="((auditDrawer.summary && auditDrawer.summary.seo) || 0)"></span>
                                </div>
                                <span class="bk-ring-lab">SEO</span>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                <div class="bk-score-ring-wrap is-lg">
                                    <svg class="bk-score-ring" viewBox="0 0 36 36">
                                        <circle class="track" cx="18" cy="18" r="14"></circle>
                                        <circle class="val" :class="scoreTone((auditDrawer.summary && auditDrawer.summary.aeo) || 0)" cx="18" cy="18" r="14" stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset((auditDrawer.summary && auditDrawer.summary.aeo) || 0)"></circle>
                                    </svg>
                                    <span class="bk-score-ring-num" :class="scoreTone((auditDrawer.summary && auditDrawer.summary.aeo) || 0)" x-text="((auditDrawer.summary && auditDrawer.summary.aeo) || 0)"></span>
                                </div>
                                <span class="bk-ring-lab">AEO</span>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                <div class="bk-score-ring-wrap is-lg">
                                    <svg class="bk-score-ring" viewBox="0 0 36 36">
                                        <circle class="track" cx="18" cy="18" r="14"></circle>
                                        <circle class="val" :class="scoreTone((auditDrawer.summary && auditDrawer.summary.geo) || 0)" cx="18" cy="18" r="14" stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset((auditDrawer.summary && auditDrawer.summary.geo) || 0)"></circle>
                                    </svg>
                                    <span class="bk-score-ring-num" :class="scoreTone((auditDrawer.summary && auditDrawer.summary.geo) || 0)" x-text="((auditDrawer.summary && auditDrawer.summary.geo) || 0)"></span>
                                </div>
                                <span class="bk-ring-lab">GEO</span>
                            </div>
                        </div>
                    </div>
                    <div class="bk-audit-header-side">
                        <div class="bk-audit-views-card">
                            <div class="bk-audit-views-head">
                                <span>بازدید محتوا (۷ روز)</span>
                                <button type="button" class="bk-link-btn" style="border:0;background:transparent;color:#4f46e5;cursor:pointer;font-size:11px;font-weight:700" @click="refreshAuditViews()">تازه‌سازی</button>
                            </div>
                            <div class="bk-audit-views-total" x-text="(auditDrawer.viewsTotal||0).toLocaleString('fa-IR')">0</div>
                            <div class="bk-audit-views-delta" :class="(auditDrawer.viewsDelta||0) >= 0 ? 'is-up' : 'is-down'">
                                نسبت به هفته قبل
                                <strong x-text="((auditDrawer.viewsDelta||0) >= 0 ? '+' : '') + (auditDrawer.viewsDelta||0) + '%'"></strong>
                            </div>
                            <div class="bk-views-spark">
                                <template x-for="(v,i) in (auditDrawer.viewsSeries||[2,3,4,5,6,5,8])" :key="'vv'+i">
                                    <span :style="'height:' + Math.max(8, Math.round((v / Math.max(1, Math.max(...(auditDrawer.viewsSeries||[1])))) * 100)) + '%'"></span>
                                </template>
                            </div>
                            <div class="bk-audit-views-devices">
                                <span>رایانه <strong x-text="auditDrawer.viewsDesktop||0"></strong></span>
                                <span>موبایل <strong x-text="auditDrawer.viewsMobile||0"></strong></span>
                                <span>تبلت <strong x-text="auditDrawer.viewsTablet||0"></strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bk-audit-progress-line" x-show="auditDrawer.phase==='run'">
                    <div class="bk-audit-progress-line__fill" :style="'width:'+(auditDrawer.progress||0)+'%'"></div>
                </div>

                <div class="bk-capsule-tabs" role="tablist" style="margin:12px 0 14px">
                    <button type="button" class="bk-capsule-tab" :class="{'is-active': auditDrawer.uiTab==='seo'}" @click="auditDrawer.uiTab='seo'; loadAuditSeoForm()"><span class="bk-capsule-label">سئو</span></button>
                    <button type="button" class="bk-capsule-tab" :class="{'is-active': auditDrawer.uiTab==='ai'}" @click="switchAuditTab('ai')"><span class="bk-capsule-label">دستیار هوشمند</span></button>
                    <button type="button" class="bk-capsule-tab" :class="{'is-active': auditDrawer.uiTab==='analysis'}" @click="switchAuditTab('analysis')"><span class="bk-capsule-label">آنالیز SEO · AEO · GEO</span></button>
                </div>

                <!-- SEO TAB 2-COL -->
                <div class="bk-audit-seo-panel" x-show="auditDrawer.uiTab==='seo'" x-cloak>
                    <div class="bk-seo-loader" x-show="seoLoading"><div class="spin"></div><span>در حال بارگذاری اطلاعات سئو…</span></div>
                    <div x-show="!seoLoading">
                    <div class="bk-seo-2col">
                        <div>
                            <div class="bk-card">
                                <div class="bk-card-head" style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                                    <span>پیش‌نمایش SERP</span>
                                    <div class="bk-serp-actions" x-show="auditDrawer.seoForm && auditDrawer.seoForm.permalink">
                                        <a class="bk-serp-icon-btn" :href="auditDrawer.seoForm.permalink" target="_blank" rel="noopener" title="مشاهده صفحه">
                                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <button type="button" class="bk-serp-icon-btn" title="کپی لینک" @click="copyPermalink()">
                                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 0 0-7.07-7.07L11 4"/><path d="M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 0 0 7.07 7.07L13 20"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="bk-serp">
                                    <div class="bk-serp-title" x-text="(auditDrawer.seoForm && auditDrawer.seoForm.seo_title) || auditDrawer.title || 'عنوان سئو'"></div>
                                    <div class="bk-serp-desc" x-text="(auditDrawer.seoForm && auditDrawer.seoForm.description) || 'توضیحات متا…'"></div>
                                </div>
                                <div style="margin-top:8px;font-size:11px;color:#64748b">
                                    طول عنوان: <strong x-text="((auditDrawer.seoForm && auditDrawer.seoForm.seo_title) || '').length"></strong>
                                    · متا: <strong x-text="((auditDrawer.seoForm && auditDrawer.seoForm.description) || '').length"></strong>
                                </div>
                            </div>
                            <div class="bk-card">
                                <div class="bk-card-head"><span>کلیدواژه‌ها</span></div>
                                <label>کلیدواژه اصلی</label>
                                <input type="text" x-model="auditDrawer.seoForm.focus_keyword" style="margin-bottom:10px">
                                <label>کلیدواژه‌های فرعی</label>
                                <div class="bk-kw-box" style="margin-bottom:8px">
                                    <template x-for="(kw, i) in auditKeywordList()" :key="'kw'+i">
                                        <span class="bk-chip" :class="{ 'is-primary': i === 0 }">
                                            <span x-text="kw"></span>
                                            <button type="button" class="bk-chip-x" @click="removeAuditKeyword(i)">×</button>
                                        </span>
                                    </template>
                                    <input type="text" class="bk-kw-input" x-model="auditDrawer.keywordInput" @keydown.enter.prevent="addAuditKeyword()" placeholder="افزودن + اینتر…">
                                </div>
                            </div>
                            <div class="bk-card">
                                <div class="bk-card-head"><span>عنوان سئو (Title Tag)</span></div>
                                <input type="text" x-model="auditDrawer.seoForm.seo_title">
                            </div>
                            <div class="bk-card">
                                <div class="bk-card-head"><span>توضیحات متا (Meta Description)</span></div>
                                <textarea rows="3" x-model="auditDrawer.seoForm.description"></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="bk-card">
                                <div class="bk-card-head"><span>بررسی‌های پایه سئو</span></div>
                                <div class="bk-seo-loader" x-show="seoLoading || auditDrawer.phase==='run'">
                                    <div class="spin"></div>
                                    <span>در حال بارگذاری بررسی‌های سئو…</span>
                                </div>
                                <template x-if="!seoLoading && auditDrawer.phase!=='run'">
                                    <div>
                                        <template x-for="grp in groupSeoChecks((auditDrawer.result && auditDrawer.result.seo) || [])" :key="grp.key">
                                            <div class="bk-check-acc">
                                                <button type="button" class="bk-check-acc__head" @click="toggleCheckAcc(grp.key)">
                                                    <span x-text="grp.title"></span>
                                                    <span class="meta" x-text="(checkAcc[grp.key] ? '▾ ' : '▸ ') + grp.items.length + ' مورد'"></span>
                                                </button>
                                                <div class="bk-check-acc__body" x-show="checkAcc[grp.key]">
                                                    <template x-for="c in grp.items" :key="c._k">
                                                        <div class="bk-check-item" :class="isPass(c) ? 'is-pass' : 'is-fail'">
                                                            <div class="bk-check-item__row" @click="toggleAnalysisItem('seo-'+c._k)">
                                                                <span x-html="lineIcon(isPass(c) ? 'pass' : 'fail')"></span>
                                                                <div style="min-width:0;flex:1">
                                                                    <span class="bk-check-text" x-text="c.label || c.name"></span>
                                                                    <small class="bk-check-desc" x-show="c.message" x-text="c.message"></small>
                                                                </div>
                                                                <span class="bk-acc-chevron" :class="analysisOpen['seo-'+c._k] ? 'is-open' : ''" title="نمایش راهنما">▸</span>
                                                            </div>
                                                            <div class="bk-check-item__guide" x-show="analysisOpen['seo-'+c._k]" x-text="expandGuideHint(c)"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <p x-show="!(auditDrawer.result && auditDrawer.result.seo && auditDrawer.result.seo.length)" style="font-size:12px;color:#94a3b8;margin:8px 0 0">هنوز بررسی‌ای نیست — آنالیز را اجرا کنید.</p>
                                    </div>
                                </template>
                            </div>
                            <div class="bk-card">
                                <div class="bk-card-head"><span>تنظیمات ایندکس و Robots</span></div>
                                <div class="bk-robots-card">
                                <label class="bk-switch-row"><span>اجازه ایندکس (Index)</span><input type="checkbox" x-model="auditDrawer.seoForm.robots_index"></label>
                                <label class="bk-switch-row"><span>دنبال کردن لینک‌ها (Follow)</span><input type="checkbox" x-model="auditDrawer.seoForm.robots_follow"></label>
                                <label class="bk-switch-row"><span>مخفی کردن تاریخ در SERP</span><input type="checkbox" x-model="auditDrawer.seoForm.hide_date"></label>
                                <label class="bk-field-label">Canonical URL</label>
                                <input type="url" dir="ltr" x-model="auditDrawer.seoForm.canonical">
                                </div>
                            </div>
                            <button type="button" class="bk-btn bk-btn-primary" @click="saveAuditSeoForm()" :disabled="auditDrawer.seoSaving || isBtnBusy('saveSeo')" :class="{ 'is-loading': auditDrawer.seoSaving || isBtnBusy('saveSeo') }" :disabled="auditDrawer.seoSaving">ذخیره سئو</button>
                        </div>
                    </div>
                </div>

                </div>
                <!-- AI TAB 2-COL -->
                <div class="bk-hub-ai-match" x-show="auditDrawer.uiTab==='ai' && auditDrawer.phase!=='run'" x-cloak>
                    <div class="bk-card" style="padding:14px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;margin-bottom:12px">
                        <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px">
                            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f97316,#0f766e);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800">AI</div>
                            <div>
                                <strong style="font-size:13px;display:block">دستیار هوشمند سئوی بنکای</strong>
                                <small style="color:#64748b;font-size:10px">ابزارهای حرفه‌ای بهینه‌سازی با یک کلیک</small>
                            </div>
                        </div>
                        <div class="bk-hero-action-card" style="margin-bottom:14px;cursor:pointer;position:relative" @click="openAiModal('auto_all')">
                            <div class="bk-action-badge">پیشنهادی 🚀</div>
                            <h4 style="margin:0 0 4px;font-size:14px">بهینه‌سازی یک‌پارچه مقاله (Auto-Optimize All)</h4>
                            <p style="margin:0 0 10px;font-size:11px;color:#64748b">تحلیل، عنوان و متا، کلیدواژه، لینک، تصویر و اسکیما</p>
                            <button type="button" class="bk-btn bk-btn-primary bk-full bk-btn-3d" style="background:linear-gradient(135deg,#6366f1,#4f46e5);border:0" @click.stop="openAiModal('auto_all')">شروع بهینه‌سازی هوشمند</button>
                        </div>
                        <div class="bk-ai-tools-grid">
                            <button type="button" class="bk-ai-tool-item" @click="openAiModal('meta')">
                                <div class="bk-ai-tool-icon bk-icon-blue"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 7V5h16v2M12 5v14M9 19h6"/></svg></div>
                                <div class="bk-ai-tool-info"><strong>عنوان و متا دیسکریپشن</strong><small>متاداده استاندارد سئو</small></div>
                            </button>
                            <button type="button" class="bk-ai-tool-item" @click="openAiModal('keywords')">
                                <div class="bk-ai-tool-icon bk-icon-amber"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/></svg></div>
                                <div class="bk-ai-tool-info"><strong>کلیدواژه اصلی و فرعی</strong><small>استخراج از محتوا</small></div>
                            </button>

                            <button type="button" class="bk-ai-tool-item" @click="openAiModal('links')">
                                <div class="bk-ai-tool-icon bk-icon-blue"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="2"/><path d="M12 2v4M12 18v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M2 12h4M18 12h4"/></svg></div>
                                <div class="bk-ai-tool-info"><strong>لینک داخلی</strong><small>پیشنهاد انکورتکست</small></div>
                            </button>
                            <button type="button" class="bk-ai-tool-item" @click="openAiModal('images')">
                                <div class="bk-ai-tool-icon bk-icon-emerald"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>
                                <div class="bk-ai-tool-info"><strong>Alt تصاویر</strong><small>متن جایگزین سئو</small></div>
                            </button>
                            <button type="button" class="bk-ai-tool-item" @click="openAiModal('rewrite')" style="grid-column:1/-1">
                                <div class="bk-ai-tool-icon bk-icon-purple"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></div>
                                <div class="bk-ai-tool-info"><strong>بازنویسی کامل مقاله با AI</strong><small>حفظ تگ‌های HTML</small></div>
                            </button>
                        </div>
                        <div class="bk-ai-section-divider">
                            <span class="bk-ai-section-title">بهبود پاسخ‌محور و مولد (AEO · GEO)</span>
                            <p class="bk-ai-section-desc">تولید FAQ ساختاریافته، اسکیما، خلاصه نکات کلیدی و متای قابل‌نقل برای موتورهای پاسخ‌گو و مولد</p>
                        </div>
                        <div class="bk-ai-tools-grid bk-ai-tools-grid--aeo-geo">
                            <button type="button" class="bk-ai-tool-item bk-ai-tool-item--wide" @click="openAiModal('aeo_geo')">
                                <div class="bk-ai-tool-icon bk-icon-cyan"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M4 19h16M8 15v4M16 15v4"/></svg></div>
                                <div class="bk-ai-tool-info">
                                    <strong>بهبود یک‌پارچه AEO · GEO</strong>
                                    <small>FAQ + Schema + Takeaways + Meta — تولید، بازبینی و افزودن به مقاله</small>
                                </div>
                            </button>
                        </div>

                    </div>
                </div>

                <!-- ANALYSIS TAB -->
                <div x-show="auditDrawer.uiTab==='analysis'" x-cloak>
                    <div class="bk-audit-panel__actions" style="margin-bottom:12px" x-show="auditDrawer.phase!=='run'">
                        <button type="button" class="bk-abtn bk-abtn-primary bk-btn-3d" @click="rerunAudit(true)" :disabled="!!auditBusyId || isBtnBusy('rerun')" :class="{ 'is-loading': !!auditBusyId || isBtnBusy('rerun') }">
                            <span class="bk-btn-spin" x-show="!!auditBusyId || isBtnBusy('rerun')"></span>
                            <span x-text="(!!auditBusyId || isBtnBusy('rerun')) ? 'در حال آنالیز…' : (auditDrawer.phase==='done' ? 'آنالیز مجدد' : 'شروع آنالیز SEO · AEO · GEO')"></span>
                        </button>
                        <p class="bk-analysis-auto-hint" x-show="auditDrawer.phase==='done' && !auditBusyId">آنالیز ذخیره شده است — در صورت تغییر محتوا «آنالیز مجدد» را بزنید. برای FAQ و Takeaways از تب دستیار هوشمند استفاده کنید.</p>
                    </div>

                    <div class="bk-audit-ai" x-show="auditDrawer.aiRunning || auditDrawer.aiReady">
                        <div class="bk-audit-progress-line" x-show="auditDrawer.aiRunning">
                            <div class="bk-audit-progress-line__fill" style="background:linear-gradient(90deg,#f97316,#0f766e)" :style="'width:'+(auditDrawer.aiProgress||0)+'%'"></div>
                        </div>
                        <p class="bk-audit-panel__sub" x-text="auditDrawer.aiStep || 'دریافت پیشنهاد از AI…'"></p>
                        <div class="bk-audit-col" style="margin-top:8px" x-show="(auditDrawer.aiSteps||[]).length">
                            <div class="bk-audit-col__h">مراحل بهبود AI</div>
                            <template x-for="s in (auditDrawer.aiSteps||[])" :key="'ais'+s.id">
                                <div class="bk-audit-line" :class="'st-'+(s.status==='done'?'pass':(s.status==='run'?'run':'pending'))">
                                    <span class="bk-audit-line__ico" x-html="lineIcon(s.status==='done'?'pass':(s.status==='run'?'run':'pending'))"></span>
                                    <span x-text="s.label"></span>
                                </div>
                            </template>
                        </div>
                        <div class="bk-audit-ai__preview" x-show="auditDrawer.aiReady && auditDrawer.aiProposed" style="margin-top:10px;padding:10px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc">
                            <div style="font-size:12px;font-weight:700;margin-bottom:6px">پیش‌نمایش اصلاحات پیشنهادی</div>
                            <div x-show="auditDrawer.aiProposed && auditDrawer.aiProposed.seo_title"><small>عنوان سئو</small><strong style="display:block" x-text="auditDrawer.aiProposed.seo_title"></strong></div>
                            <div x-show="auditDrawer.aiProposed && auditDrawer.aiProposed.description" style="margin-top:6px"><small>توضیحات متا</small><strong style="display:block" x-text="auditDrawer.aiProposed.description"></strong></div>
                        </div>
                    </div>

                    <div class="bk-audit-cols" x-show="auditDrawer.phase==='run' || (auditDrawer.phase==='done' && auditDrawer.view==='list')">
                        <div class="bk-audit-col">
                            <div class="bk-audit-col__h">SEO <span x-text="'(' + ((auditDrawer.liveSeo||[]).length || ((auditDrawer.result&&auditDrawer.result.seo)||[]).length) + ')'"></span></div>
                            <template x-for="item in (auditDrawer.phase==='run' ? (auditDrawer.liveSeo||[]) : ((auditDrawer.result && auditDrawer.result.seo) || []))" :key="'seo'+item.key">
                                <div class="bk-check-item" :class="(item.state==='pass' || isPass(item)) ? 'is-pass' : 'is-fail'">
                                    <div class="bk-check-item__row" @click="toggleAnalysisItem((item.group||'x')+'-'+(item.key||item.label))">
                                        <span class="bk-audit-line__ico" x-html="lineIcon(item.state || (isPass(item)?'pass':'fail'))"></span>
                                        <div style="min-width:0;flex:1">
                                            <span class="bk-check-text" x-text="item.label"></span>
                                            <small class="bk-check-desc" x-show="item.message" x-text="item.message || ''"></small>
                                        </div>
                                        <span class="bk-acc-chevron" :class="analysisOpen[(item.group||'x')+'-'+(item.key||item.label)] ? 'is-open' : ''" title="نمایش راهنما">▸</span>
                                    </div>
                                    <div class="bk-check-item__guide" x-show="analysisOpen[(item.group||'x')+'-'+(item.key||item.label)]" x-text="expandGuideHint(item)"></div>
                                </div>
                            </template>
                        </div>
                        <div class="bk-audit-col">
                            <div class="bk-audit-col__h">AEO <span x-text="'(' + ((auditDrawer.liveAeo||[]).length || ((auditDrawer.result&&auditDrawer.result.aeo)||[]).length) + ')'"></span></div>
                            <template x-for="item in (auditDrawer.phase==='run' ? (auditDrawer.liveAeo||[]) : ((auditDrawer.result && auditDrawer.result.aeo) || []))" :key="'aeo'+item.key">
                                <div class="bk-check-item" :class="(item.state==='pass' || isPass(item)) ? 'is-pass' : 'is-fail'">
                                    <div class="bk-check-item__row" @click="toggleAnalysisItem((item.group||'x')+'-'+(item.key||item.label))">
                                        <span class="bk-audit-line__ico" x-html="lineIcon(item.state || (isPass(item)?'pass':'fail'))"></span>
                                        <div style="min-width:0;flex:1">
                                            <span class="bk-check-text" x-text="item.label"></span>
                                            <small class="bk-check-desc" x-show="item.message" x-text="item.message || ''"></small>
                                        </div>
                                        <span class="bk-acc-chevron" :class="analysisOpen[(item.group||'x')+'-'+(item.key||item.label)] ? 'is-open' : ''" title="نمایش راهنما">▸</span>
                                    </div>
                                    <div class="bk-check-item__guide" x-show="analysisOpen[(item.group||'x')+'-'+(item.key||item.label)]" x-text="expandGuideHint(item)"></div>
                                </div>
                            </template>
                        </div>
                        <div class="bk-audit-col">
                            <div class="bk-audit-col__h">GEO <span x-text="'(' + ((auditDrawer.liveGeo||[]).length || ((auditDrawer.result&&auditDrawer.result.geo)||[]).length) + ')'"></span></div>
                            <template x-for="item in (auditDrawer.phase==='run' ? (auditDrawer.liveGeo||[]) : ((auditDrawer.result && auditDrawer.result.geo) || []))" :key="'geo'+item.key">
                                <div class="bk-check-item" :class="(item.state==='pass' || isPass(item)) ? 'is-pass' : 'is-fail'">
                                    <div class="bk-check-item__row" @click="toggleAnalysisItem((item.group||'x')+'-'+(item.key||item.label))">
                                        <span class="bk-audit-line__ico" x-html="lineIcon(item.state || (isPass(item)?'pass':'fail'))"></span>
                                        <div style="min-width:0;flex:1">
                                            <span class="bk-check-text" x-text="item.label"></span>
                                            <small class="bk-check-desc" x-show="item.message" x-text="item.message || ''"></small>
                                        </div>
                                        <span class="bk-acc-chevron" :class="analysisOpen[(item.group||'x')+'-'+(item.key||item.label)] ? 'is-open' : ''" title="نمایش راهنما">▸</span>
                                    </div>
                                    <div class="bk-check-item__guide" x-show="analysisOpen[(item.group||'x')+'-'+(item.key||item.label)]" x-text="expandGuideHint(item)"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    
                </div>
</div>

<!-- TABLE -->
            <div class="bk-art-table-wrap">
                <table class="bk-art-table">
                    <colgroup>
                        <col style="width:36%"><col style="width:10%"><col style="width:10%"><col style="width:10%"><col style="width:12%"><col style="width:22%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th style="text-align:center">SEO</th>
                            <th style="text-align:center">AEO</th>
                            <th style="text-align:center">GEO</th>
                            <th style="text-align:center">وضعیت</th>
                            <th style="text-align:left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="!articles || !articles.length">
                            <tr><td colspan="6" style="text-align:center;padding:28px;color:#94a3b8">موردی یافت نشد</td></tr>
                        </template>
                        <template x-for="row in articles" :key="row.id">
                            <tr>
                                <td>
                                    <strong class="bk-art-title" x-text="row.title" :title="row.title"></strong>
                                    <div class="bk-art-date" x-text="row.date || row.modified || ''"></div>
                                </td>
                                <td style="text-align:center">
                                    <div class="bk-score-ring-wrap" :title="'SEO ' + rowScores(row).seo + '%'">
                                        <svg class="bk-score-ring" viewBox="0 0 36 36">
                                            <circle class="track" cx="18" cy="18" r="14"></circle>
                                            <circle class="val" :class="scoreTone(rowScores(row).seo)" cx="18" cy="18" r="14" stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset(rowScores(row).seo)"></circle>
                                        </svg>
                                        <span class="bk-score-ring-num" :class="scoreTone(rowScores(row).seo)" x-text="rowScores(row).seo"></span>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <div class="bk-score-ring-wrap" :title="'AEO ' + rowScores(row).aeo + '%'">
                                        <svg class="bk-score-ring" viewBox="0 0 36 36">
                                            <circle class="track" cx="18" cy="18" r="14"></circle>
                                            <circle class="val" :class="scoreTone(rowScores(row).aeo)" cx="18" cy="18" r="14" stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset(rowScores(row).aeo)"></circle>
                                        </svg>
                                        <span class="bk-score-ring-num" :class="scoreTone(rowScores(row).aeo)" x-text="rowScores(row).aeo"></span>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <div class="bk-score-ring-wrap" :title="'GEO ' + rowScores(row).geo + '%'">
                                        <svg class="bk-score-ring" viewBox="0 0 36 36">
                                            <circle class="track" cx="18" cy="18" r="14"></circle>
                                            <circle class="val" :class="scoreTone(rowScores(row).geo)" cx="18" cy="18" r="14" stroke-dasharray="87.96" :stroke-dashoffset="scoreOffset(rowScores(row).geo)"></circle>
                                        </svg>
                                        <span class="bk-score-ring-num" :class="scoreTone(rowScores(row).geo)" x-text="rowScores(row).geo"></span>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <span class="bk-status-pill" :class="'is-' + (row.status || 'draft')" x-text="statusLabelFa(row)"></span>
                                </td>
                                <td style="text-align:left;white-space:nowrap">
                                    <div class="bk-art-actions">
                                        <button type="button" class="bk-icon-btn is-analyze" title="آنالیز SEO · AEO · GEO" @click="openAuditPanel(row)" :class="{ 'is-loading': auditBusyId === row.id }">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5"/><path d="M4 19h16"/><path d="M8 16v-5"/><path d="M12 16V8"/><path d="M16 16v-3"/></svg>
                                        </button>
                                        <a class="bk-icon-btn is-edit" title="ویرایش مقاله" :href="row.edit_link || row.edit_url || ('post.php?post='+row.id+'&action=edit')" target="_blank" rel="noopener">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;gap:10px;flex-wrap:wrap" x-show="totalPages > 1">
                <span style="font-size:12px;color:#64748b" x-text="'صفحه ' + page + ' از ' + totalPages"></span>
                <div style="display:flex;gap:8px">
                    <button type="button" class="bk-btn" :disabled="page<=1" @click="page--;loadArticles()">قبلی</button>
                    <button type="button" class="bk-btn" :disabled="page>=totalPages" @click="page++;loadArticles()">بعدی</button>
                </div>
            </div>
        </div>

        <!-- GENERATE -->
        <div class="bk-art-card" x-show="panel==='generate'" x-cloak>
            <h3 style="margin:0 0 8px">ویزارد هوشمند تولید مقاله</h3>
            <p style="margin:0 0 12px;font-size:12px;color:#64748b">از ایده تا پیش‌نویس سئوشده</p>
            <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">موضوع / کلمه کلیدی</label>
            <textarea x-model="wiz.topic" rows="3" placeholder="مثلاً: راهنمای انتخاب هاست وردپرس…" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;box-sizing:border-box"></textarea>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
                <button type="button" class="bk-btn bk-btn-primary" @click="suggestTitles()" :disabled="wizBusy">دریافت پیشنهاد عنوان از AI</button>
                <button type="button" class="bk-btn" @click="addManualTitle()">افزودن عنوان دستی</button>
            </div>
            <div x-show="(wiz.selected||[]).length" style="margin-top:16px">
                <template x-for="(item, idx) in wiz.selected" :key="'ws'+idx">
                    <div style="padding:10px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px">
                        <input type="text" x-model="item.title" style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:8px;box-sizing:border-box">
                    </div>
                </template>
                <button type="button" class="bk-btn bk-btn-primary" @click="startProduce()" :disabled="batchBusy">شروع تولید</button>
            </div>
            <div x-show="wiz.phase==='produce'" style="margin-top:16px">
                <template x-for="(job, ji) in wiz.queue" :key="'jq'+ji">
                    <div style="padding:10px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px">
                        <strong x-text="job.title"></strong>
                        <div style="font-size:12px;color:#64748b" x-text="job.stepLabel || job.status"></div>
                        <div style="height:6px;background:#e2e8f0;border-radius:99px;margin-top:6px;overflow:hidden">
                            <div :style="'width:'+(job.progress||0)+'%;height:100%;background:#6366f1'"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- AI Modal backdrop + modal -->
    <template x-teleport="body">
        <div>
            <div class="bk-modal-ai-backdrop" x-show="aiModalOpen" x-cloak @click="closeAiModal()"></div>
            <div class="bk-modal-ai-flow bk-modal-ai-wide" x-show="aiModalOpen" x-cloak @click.stop="">
                <div class="bk-modal-head">
                    <div>
                        <strong x-text="aiModalTitle || 'دستیار AI سئو'"></strong>
                        <small style="display:block;font-size:11px;color:#64748b" x-text="aiPhaseLabel()"></small>
                    </div>
                    <button type="button" class="bk-abtn-icon" @click="closeAiModal()" :disabled="aiBusy || aiLoading">×</button>
                </div>
                <div class="bk-ai-stepper">
                    <div class="bk-ai-step" :class="{ 'is-active': aiPhase === 'setup', 'is-done': aiPhaseIndex() > 0 }"><span>1</span><em>تنظیم</em></div>
                    <div class="bk-ai-step-line" :class="{ 'is-on': aiPhaseIndex() > 0 }"></div>
                    <div class="bk-ai-step" :class="{ 'is-active': aiPhase === 'running', 'is-done': aiPhaseIndex() > 1 }"><span>2</span><em>اجرا</em></div>
                    <div class="bk-ai-step-line" :class="{ 'is-on': aiPhaseIndex() > 1 }"></div>
                    <div class="bk-ai-step" :class="{ 'is-active': aiPhase === 'review', 'is-done': aiPhaseIndex() > 2 }"><span>3</span><em>بازبینی</em></div>
                </div>
                <div class="bk-modal-body">
                    <div x-show="aiPhase === 'setup'">
                        <div class="bk-ai-hero-banner">
                            <div><strong x-text="aiModalTitle"></strong><p style="margin:4px 0 0;font-size:12px;color:#475569" x-text="aiTaskBlurb()"></p></div>
                        </div>
                        <div class="bk-field">
                            <label>انتخاب موتور هوش مصنوعی</label>
                            <select class="bk-select" x-model="aiProvider">
                                <option value="">پیش‌فرض سیستم</option>
                                <template x-for="p in aiProvidersList()" :key="p.id">
                                    <option :value="p.id" x-text="p.label || p.name || p.id"></option>
                                </template>
                            </select>
                        </div>
                        <div class="bk-field">
                            <label>سبک تولید</label>
                            <select class="bk-select" x-model="aiOptions.style">
                                <option value="seo">سئو استاندارد</option>
                                <option value="engaging">جذاب و کلیک‌خور</option>
                                <option value="formal">رسمی و حرفه‌ای</option>
                                <option value="simple">ساده و روان</option>
                            </select>
                        </div>
                        <div class="bk-ai-plan-card">
                            <div class="bk-ai-plan-head">فرایندهای این اجرا</div>
                            <template x-for="(item, i) in plannedChecklist()" :key="item.id">
                                <div class="bk-ai-plan-row"><span class="bk-ai-plan-num" x-text="(i+1)"></span><span x-text="item.label"></span></div>
                            </template>
                        </div>
                        <button type="button" class="bk-btn bk-btn-primary bk-full bk-ai-start-btn bk-btn-3d" @click="startAiWorkflow()" :disabled="aiBusy || aiLoading || isBtnBusy('aiStart')" :class="{ 'is-loading': aiBusy || aiLoading || isBtnBusy('aiStart') }"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg><span>شروع فرایند</span></button>
                    </div>
                    <div x-show="aiPhase === 'running'">
                        <div class="bk-ai-progress-ring-wrap">
                            <div class="bk-ai-progress-pct" x-text="(aiProgress || 0) + '%'"></div>
                            <div class="bk-ai-progress-track"><div class="bk-ai-progress-fill" :style="'width:' + (aiProgress || 0) + '%'"></div></div>
                            <p style="font-size:12px;color:#64748b" x-text="aiStepText || 'در حال پردازش…'"></p>
                        </div>
                        <div>
                            <template x-for="item in activeRunChecklist()" :key="item.id">
                                <div class="bk-ai-live-item">
                                    <span x-html="lineIcon(item.status === 'success' || item.status === 'done' ? 'pass' : (item.status === 'loading' ? 'run' : (item.status === 'error' ? 'fail' : 'pending')))"></span>
                                    <span x-text="item.label"></span>
                                    <span class="bk-ai-live-status" x-text="aiStatusFa(item.status)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div x-show="aiPhase === 'review'">
                        <div class="bk-ai-review-banner"><span>پاسخ هوش مصنوعی آماده است — بازبینی و تایید کنید</span></div>
                        <div class="bk-ai-result-card" x-show="aiDraft.title || aiDraft.description">
                            <div class="bk-ai-result-label">عنوان سئو</div>
                            <div class="bk-ai-result-value" x-text="aiDraft.title || '—'"></div>
                            <div class="bk-ai-result-label">توضیحات متا</div>
                            <div class="bk-ai-result-value" x-text="aiDraft.description || '—'"></div>
                        </div>
                        <div class="bk-ai-result-card" x-show="(aiDraft.keywords && aiDraft.keywords.length) || aiDraft.focus_keyword">
                            <div class="bk-ai-result-label">کلمات کلیدی</div>
                            <div style="display:flex;flex-wrap:wrap;gap:6px">
                                <span class="bk-chip is-primary" x-show="aiDraft.focus_keyword" x-text="aiDraft.focus_keyword"></span>
                                <template x-for="(k,ki) in (aiDraft.keywords || [])" :key="ki"><span class="bk-chip" x-text="k"></span></template>
                            </div>
                        </div>
                        <div class="bk-ai-result-card" x-show="aiDraft.rewrite">
                            <div class="bk-ai-result-label">بازنویسی محتوا</div>
                            <textarea x-model="aiDraft.rewrite" rows="5" style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:8px;box-sizing:border-box"></textarea>
                        </div>
                        
                    <div class="bk-ai-result-card" x-show="aiDraft.faq || aiDraft.faqText">
                        <div class="bk-ai-result-label">FAQ · AEO</div>
                        <div class="bk-ai-result-body" x-text="plainText(typeof aiDraft.faq === 'string' ? aiDraft.faq : (aiDraft.faqText || (Array.isArray(aiDraft.faq) ? aiDraft.faq.map(function(x){return (x.q||x.question||'') + ' — ' + (x.a||x.answer||'');}).join(' | ') : JSON.stringify(aiDraft.faq))))"></div>
                    </div>
                    <div class="bk-ai-result-card" x-show="aiDraft.takeaways || aiDraft.takeawaysText">
                        <div class="bk-ai-result-label">Takeaways · GEO</div>
                        <div class="bk-ai-result-body" x-text="plainText(typeof aiDraft.takeaways === 'string' ? aiDraft.takeaways : (aiDraft.takeawaysText || (Array.isArray(aiDraft.takeaways) ? aiDraft.takeaways.join(' • ') : JSON.stringify(aiDraft.takeaways))))"></div>
                    </div>
                    <div class="bk-ai-result-card" x-show="aiDraft.schema">
                        <div class="bk-ai-result-label">Schema FAQ</div>
                        <div class="bk-ai-result-body" dir="ltr" style="font-size:11px;word-break:break-all" x-text="typeof aiDraft.schema === 'string' ? aiDraft.schema : JSON.stringify(aiDraft.schema)"></div>
                    </div>

                    <div class="bk-ai-review-actions">
                            <button type="button" class="bk-btn bk-btn-primary bk-full bk-btn-3d" @click="applyAiReview()" :disabled="isBtnBusy('aiApply')" :class="{ 'is-loading': isBtnBusy('aiApply') }">تایید و افزودن به مقاله</button>
                            <div class="bk-ai-review-secondary">
                                <button type="button" class="bk-btn-sm" @click="aiPhase = 'setup'">ویرایش دستی تنظیمات</button>
                                <button type="button" class="bk-btn-sm" @click="rewriteAiWorkflow()" :disabled="aiBusy || aiLoading">بازنویسی با AI</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
