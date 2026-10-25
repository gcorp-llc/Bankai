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

<div id="tab-articles" class="bankai-tab-pane bk-orange-shell" :class="{ 'is-active-tab': activeTab === 'articles' }" x-show="activeTab === 'articles'" x-cloak
     x-data="bankaiArticlesHub" x-init="init()">

  <header class="bk-top-head">
    <div class="bk-top-head__brand">
      <span class="bk-top-head__logo"><iconify-icon icon="solar:document-text-bold-duotone" width="24" height="24"></iconify-icon></span>
      <div>
        <strong>مدیریت محتوا</strong>
        <small>سئوی محتوا · تولید با AI · انتشار</small>
      </div>
    </div>
  </header>

  <nav class="bk-top-tabs" role="tablist">
    <button type="button" class="bk-top-tab" :class="{'is-active': panel==='list'}" @click="panel='list'; loadArticles()">
      <iconify-icon icon="solar:checklist-minimalistic-bold-duotone" width="18"></iconify-icon>
      <span>سئو محتوا</span>
    </button>
    <button type="button" class="bk-top-tab" :class="{'is-active': panel==='generate'}" @click="panel='generate'">
      <iconify-icon icon="solar:magic-stick-3-bold-duotone" width="18"></iconify-icon>
      <span>تولید با AI</span>
    </button>
  </nav>

  <div class="bk-top-body">
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
            <!-- Full-screen analysis / AI overlay -->
            <div class="bk-audit-blur-overlay" x-show="auditDrawer.phase==='run' || auditDrawer.aiRunning || seoLoading" x-cloak>
                <div class="bk-audit-blur-card">
                    <div class="bk-audit-blur-spinner"></div>
                    <strong x-text="auditDrawer.aiRunning ? (auditDrawer.aiStep || 'در حال بهینه‌سازی با هوش مصنوعی…') : (auditDrawer.step || 'در حال آنالیز کامل مقاله…')"></strong>
                    <div class="bk-audit-progress-line" style="margin-top:14px;max-width:260px;width:100%">
                        <div class="bk-audit-progress-line__fill" :style="'width:'+((auditDrawer.aiRunning ? auditDrawer.aiProgress : auditDrawer.progress)||8)+'%'"></div>
                    </div>
                    <small style="color:#94a3b8;margin-top:8px;display:block" x-text="auditDrawer.aiRunning ? 'تنها موارد تاییدنشده چک‌لیست اصلاح می‌شوند' : 'لطفاً تا پایان آنالیز SEO · AEO · GEO صبر کنید'"></small>
                </div>
            </div>

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


                <!-- ANALYSIS TAB -->
                <div class="bk-audit-analysis-only" x-cloak>
                    <div class="bk-audit-panel__actions" style="margin-bottom:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:center" x-show="auditDrawer.phase==='done' && !auditDrawer.aiRunning">
                        <button type="button" class="bk-abtn bk-abtn-primary bk-btn-3d" @click="runAiFix()"
                                :disabled="isBtnBusy('aiFix') || failedAuditItems().length===0 || auditDrawer.aiReady"
                                x-show="!auditDrawer.aiReady"
                                :class="{ 'is-loading': isBtnBusy('aiFix') }">
                            <span class="bk-btn-spin" x-show="isBtnBusy('aiFix')"></span>
                            <span x-text="failedAuditItems().length ? ('بهینه‌سازی موارد تاییدنشده با AI (' + failedAuditItems().length + ')') : 'همه موارد تایید شده‌اند'"></span>
                        </button>
                        <button type="button" class="bk-abtn" @click="rerunAudit(true)" :disabled="!!auditBusyId || isBtnBusy('rerun')">
                            آنالیز مجدد
                        </button>
                        <span style="font-size:11px;color:#94a3b8" x-show="failedAuditItems().length===0 && !auditDrawer.aiReady">چک‌لیست کامل است</span>
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
                        <div class="bk-audit-ai__preview" x-show="auditDrawer.aiReady && auditDrawer.aiProposed" style="margin-top:12px;padding:14px;border:1px solid #fdbaa4;border-radius:12px;background:#fff7ed">
                            <div style="font-size:13px;font-weight:800;margin-bottom:10px;color:#c2410c">گزارش موارد ساخته‌شده توسط هوش مصنوعی</div>
                            <template x-if="auditDrawer.aiProposed">
                              <div>
                            <div x-show="auditDrawer.aiProposed.seo_title" style="margin-bottom:8px"><small style="color:#78716c">عنوان سئو</small><strong style="display:block" x-text="auditDrawer.aiProposed.seo_title"></strong></div>
                            <div x-show="auditDrawer.aiProposed.description" style="margin-bottom:8px"><small style="color:#78716c">توضیحات متا</small><strong style="display:block" x-text="auditDrawer.aiProposed.description"></strong></div>
                            <div x-show="auditDrawer.aiProposed.focus_keyword" style="margin-bottom:8px"><small style="color:#78716c">کلمه کلیدی</small><strong style="display:block" x-text="auditDrawer.aiProposed.focus_keyword"></strong></div>
                            <div x-show="auditDrawer.aiProposed.og_title" style="margin-bottom:8px"><small style="color:#78716c">OG Title</small><strong style="display:block" x-text="auditDrawer.aiProposed.og_title"></strong></div>
                            <div x-show="auditDrawer.aiProposed.content_changed" style="margin-bottom:8px;padding:8px;background:#ecfdf5;border-radius:8px;font-size:12px;color:#065f46">
                              تغییرات محتوایی (FAQ / TOC / Schema / …) آماده اعمال است.
                              <div x-show="auditDrawer.aiProposed.content_preview" style="margin-top:6px;opacity:.85" x-text="String(auditDrawer.aiProposed.content_preview||'').slice(0,160)+'…'"></div>
                            </div>
                            <div x-show="auditDrawer.aiProposed.applied_keys && auditDrawer.aiProposed.applied_keys.length" style="margin-bottom:8px">
                                <small style="color:#78716c">کلیدهای اعمال‌شده</small>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px">
                                    <template x-for="k in (auditDrawer.aiProposed.applied_keys || [])" :key="'ak'+k">
                                        <span class="bk-chip" x-text="k"></span>
                                    </template>
                                </div>
                            </div>
                              </div>
                            </template>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:14px">
                                <button type="button" class="bk-abtn bk-abtn-primary bk-btn-3d" @click="applyAiFix()" :disabled="isBtnBusy('applyFix')" :class="{ 'is-loading': isBtnBusy('applyFix') }">
                                    <span x-text="isBtnBusy('applyFix') ? 'در حال ذخیره و آنالیز مجدد…' : 'تایید و ذخیره + آنالیز مجدد'"></span>
                                </button>
                                <button type="button" class="bk-abtn" @click="runAiFix()" :disabled="isBtnBusy('aiFix')">برطرف‌سازی مجدد</button>
                                <button type="button" class="bk-abtn" @click="cancelAiFix()">لغو</button>
                            </div>
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

        <!-- GENERATE (AI CONTENT PUBLISHING WORKFLOW) -->
                <div class="bk-art-card bk-pub-hub" x-show="panel==='generate'" x-cloak>
            <!-- Hub header -->
            <div class="bk-pub-hero">
                <div class="bk-pub-hero__icon">
                    <iconify-icon icon="solar:magic-stick-3-bold-duotone" width="28" height="28"></iconify-icon>
                </div>
                <div class="bk-pub-hero__text">
                    <h3>کارگاه هوشمند تولید و انتشار محتوا</h3>
                    <p>از ایده تا پیش‌نویس سئوشده — خوشه‌بندی موضوع، لحن برند، تولید دسته‌ای و انتشار زمان‌بندی‌شده</p>
                </div>
                <div class="bk-pub-hero__stats">
                    <div class="bk-pub-stat">
                        <strong x-text="(wiz.selected||[]).filter(x=>x.approved).length || 0"></strong>
                        <span>عنوان تاییدشده</span>
                    </div>
                    <div class="bk-pub-stat">
                        <strong x-text="(wiz.queue||[]).filter(x=>x.status==='done').length || 0"></strong>
                        <span>تولید موفق</span>
                    </div>
                    <div class="bk-pub-stat">
                        <strong x-text="(wiz.guidelines && wiz.guidelines.wordCount) || 2500"></strong>
                        <span>کلمه هدف</span>
                    </div>
                </div>
            </div>

            <!-- Step rail -->
            <nav class="bk-pub-steps" role="tablist">
                <button type="button" class="bk-pub-step" :class="{'is-active': wiz.subTab==='planning', 'is-done': stepDone('planning')}" @click="wiz.subTab='planning'">
                    <span class="bk-pub-step__num">1</span>
                    <iconify-icon icon="solar:lightbulb-bolt-bold-duotone" width="18"></iconify-icon>
                    <span>پیشنهاد و خوشه</span>
                </button>
                <span class="bk-pub-step__line" :class="{'is-on': stepDone('planning')}"></span>
                <button type="button" class="bk-pub-step" :class="{'is-active': wiz.subTab==='guidelines', 'is-done': stepDone('guidelines')}" @click="wiz.subTab='guidelines'">
                    <span class="bk-pub-step__num">2</span>
                    <iconify-icon icon="solar:pen-new-square-bold-duotone" width="18"></iconify-icon>
                    <span>دستورالعمل و لحن</span>
                </button>
                <span class="bk-pub-step__line" :class="{'is-on': stepDone('guidelines')}"></span>
                <button type="button" class="bk-pub-step" :class="{'is-active': wiz.subTab==='wizard', 'is-done': stepDone('wizard')}" @click="wiz.subTab='wizard'">
                    <span class="bk-pub-step__num">3</span>
                    <iconify-icon icon="solar:settings-bold-duotone" width="18"></iconify-icon>
                    <span>تنظیم تولید</span>
                </button>
                <span class="bk-pub-step__line" :class="{'is-on': stepDone('wizard')}"></span>
                <button type="button" class="bk-pub-step" :class="{'is-active': wiz.subTab==='dashboard'}" @click="wiz.subTab='dashboard'">
                    <span class="bk-pub-step__num">4</span>
                    <iconify-icon icon="solar:playlist-bold-duotone" width="18"></iconify-icon>
                    <span>صف تولید</span>
                </button>
            </nav>

            <!-- ========== 1. PLANNING ========== -->
            <div x-show="wiz.subTab==='planning'" x-cloak class="bk-pub-panel">
                <div class="bk-pub-grid-2">
                    <div class="bk-pub-card">
                        <div class="bk-pub-card__head">
                            <iconify-icon icon="solar:compass-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
                            <strong>استراتژی موضوعی</strong>
                        </div>
                        <label class="bk-pub-label">موضوع اصلی / خوشه محتوا</label>
                        <textarea class="bk-pub-input" x-model="wiz.topic" rows="4" placeholder="مثلاً: کاردرمانی آسیب نخاعی گردنی و بازگشت عملکرد دست…"></textarea>
                        <label class="bk-pub-label">تعداد پیشنهاد عنوان</label>
                        <select class="bk-pub-input" x-model="wiz.count">
                            <option value="5">۵ عنوان</option>
                            <option value="8">۸ عنوان</option>
                            <option value="12">۱۲ عنوان</option>
                        </select>
                        <div class="bk-pub-actions">
                            <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="suggestTitles()" :disabled="wizBusy || !(wiz.topic||'').trim()">
                                <span class="bk-btn-spin" x-show="wizBusy"></span>
                                <iconify-icon icon="solar:magic-stick-3-bold-duotone" width="16" x-show="!wizBusy"></iconify-icon>
                                <span x-text="wizBusy ? 'در حال پیشنهاد…' : 'پیشنهاد عنوان با AI'"></span>
                            </button>
                            <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="addManualTitle()">
                                <iconify-icon icon="solar:add-circle-bold-duotone" width="16"></iconify-icon>
                                افزودن دستی
                            </button>
                        </div>
                        <p class="bk-pub-hint">موضوع را دقیق بنویسید تا خوشهٔ عنوان‌ها برای سئو و نیت جستجو هم‌راستا شود.</p>
                    </div>
                    <div class="bk-pub-card">
                        <div class="bk-pub-card__head">
                            <iconify-icon icon="solar:checklist-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
                            <strong>عنوان‌های پیشنهادی</strong>
                            <span class="bk-pub-badge" x-text="(wiz.selected||[]).length + ' مورد'"></span>
                        </div>
                        <div class="bk-pub-empty" x-show="!(wiz.selected||[]).length">
                            <iconify-icon icon="solar:document-add-bold-duotone" width="36" style="color:#cbd5e1"></iconify-icon>
                            <p>هنوز عنوانی نیست. موضوع را وارد کنید و پیشنهاد AI بگیرید.</p>
                        </div>
                        <div class="bk-pub-title-list" x-show="(wiz.selected||[]).length">
                            <template x-for="(item, idx) in (wiz.selected||[])" :key="'t'+idx">
                                <div class="bk-pub-title-row" :class="{'is-on': item.approved}">
                                    <label class="bk-pub-check">
                                        <input type="checkbox" x-model="item.approved">
                                        <span></span>
                                    </label>
                                    <input type="text" class="bk-pub-title-input" x-model="item.title" :placeholder="'عنوان '+(idx+1)">
                                    <button type="button" class="bk-pub-icon-btn" title="حذف" @click="wiz.selected.splice(idx,1)">
                                        <iconify-icon icon="solar:trash-bin-2-bold-duotone" width="16"></iconify-icon>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div class="bk-pub-actions" style="margin-top:12px" x-show="(wiz.selected||[]).length">
                            <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="(wiz.selected||[]).forEach(x=>x.approved=true)">تایید همه</button>
                            <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="wiz.subTab='guidelines'" :disabled="!(wiz.selected||[]).filter(x=>x.approved).length">
                                مرحله بعد
                                <iconify-icon icon="solar:arrow-left-bold-duotone" width="16"></iconify-icon>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== 2. GUIDELINES ========== -->
            <div x-show="wiz.subTab==='guidelines'" x-cloak class="bk-pub-panel">
                <div class="bk-pub-grid-2">
                    <div class="bk-pub-card">
                        <div class="bk-pub-card__head">
                            <iconify-icon icon="solar:palette-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
                            <strong>لحن و طول محتوا</strong>
                        </div>
                        <label class="bk-pub-label">لحن نوشتار</label>
                        <div class="bk-pub-tone-grid">
                            <button type="button" class="bk-pub-tone" :class="{'is-on': wiz.guidelines.tone==='seo'}" @click="wiz.guidelines.tone='seo'">
                                <iconify-icon icon="solar:chart-bold-duotone" width="18"></iconify-icon>
                                سئو استاندارد
                            </button>
                            <button type="button" class="bk-pub-tone" :class="{'is-on': wiz.guidelines.tone==='engaging'}" @click="wiz.guidelines.tone='engaging'">
                                <iconify-icon icon="solar:fire-bold-duotone" width="18"></iconify-icon>
                                جذاب و روایی
                            </button>
                            <button type="button" class="bk-pub-tone" :class="{'is-on': wiz.guidelines.tone==='formal'}" @click="wiz.guidelines.tone='formal'">
                                <iconify-icon icon="solar:case-bold-duotone" width="18"></iconify-icon>
                                رسمی و تخصصی
                            </button>
                            <button type="button" class="bk-pub-tone" :class="{'is-on': wiz.guidelines.tone==='simple'}" @click="wiz.guidelines.tone='simple'">
                                <iconify-icon icon="solar:chat-round-bold-duotone" width="18"></iconify-icon>
                                ساده و روان
                            </button>
                        </div>
                        <label class="bk-pub-label">طول هدف (کلمه)</label>
                        <div class="bk-pub-range">
                            <input type="range" min="800" max="4000" step="100" x-model="wiz.guidelines.wordCount">
                            <em x-text="(wiz.guidelines.wordCount||2500)+' کلمه'"></em>
                        </div>
                        <label class="bk-pub-label">دستورالعمل اختصاصی این دسته</label>
                        <textarea class="bk-pub-input" rows="4" x-model="wiz.guidelines.siteWide" placeholder="نکات برند، ممنوعیت‌ها، ساختار مورد انتظار…"></textarea>
                    </div>
                    <div class="bk-pub-card">
                        <div class="bk-pub-card__head">
                            <iconify-icon icon="solar:bookmark-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
                            <strong>دستورالعمل سراسری سایت</strong>
                        </div>
                        <p class="bk-pub-hint" style="margin-top:0">این لایه روی همه تولیدهای AI اعمال می‌شود و جداگانه ذخیره می‌شود.</p>
                        <label class="bk-pub-label">حوزه / دامنه موضوعی</label>
                        <input class="bk-pub-input" type="text" x-model="siteGuidelines.site_domain">
                        <label class="bk-pub-label">مخاطب هدف</label>
                        <input class="bk-pub-input" type="text" x-model="siteGuidelines.target_audience">
                        <label class="bk-pub-label">سیاست کلمه کلیدی</label>
                        <textarea class="bk-pub-input" rows="2" x-model="siteGuidelines.keyword_policy"></textarea>
                        <label class="bk-pub-label">عبارات ممنوع</label>
                        <textarea class="bk-pub-input" rows="2" x-model="siteGuidelines.forbidden_phrases"></textarea>
                        <div class="bk-pub-actions">
                            <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="saveSiteGuidelines()" :disabled="guidelinesSaving">
                                <iconify-icon icon="solar:diskette-bold-duotone" width="16"></iconify-icon>
                                <span x-text="guidelinesSaving ? 'ذخیره…' : 'ذخیره دستورالعمل سایت'"></span>
                            </button>
                            <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="wiz.subTab='wizard'">
                                مرحله بعد
                                <iconify-icon icon="solar:arrow-left-bold-duotone" width="16"></iconify-icon>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== 3. WIZARD SETTINGS ========== -->
            <div x-show="wiz.subTab==='wizard'" x-cloak class="bk-pub-panel">
                <div class="bk-pub-card">
                    <div class="bk-pub-card__head">
                        <iconify-icon icon="solar:rocket-2-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
                        <strong>انتشار و اجرای تولید</strong>
                    </div>
                    <div class="bk-pub-grid-3">
                        <div>
                            <label class="bk-pub-label">وضعیت انتشار</label>
                            <select class="bk-pub-input" x-model="wiz.status">
                                <option value="draft">پیش‌نویس</option>
                                <option value="pending">در انتظار بازبینی</option>
                                <option value="future">زمان‌بندی‌شده</option>
                                <option value="publish">انتشار مستقیم</option>
                            </select>
                        </div>
                        <div x-show="wiz.status==='future'">
                            <label class="bk-pub-label">تاریخ و زمان انتشار</label>
                            <input type="datetime-local" class="bk-pub-input" x-model="wiz.publishDate">
                        </div>
                        <div>
                            <label class="bk-pub-label">عناوین آماده تولید</label>
                            <div class="bk-pub-stat-inline">
                                <strong x-text="(wiz.selected||[]).filter(x=>x.approved).length"></strong>
                                <span>عنوان تاییدشده</span>
                            </div>
                        </div>
                    </div>
                    <div class="bk-pub-callout">
                        <iconify-icon icon="solar:info-circle-bold-duotone" width="18"></iconify-icon>
                        <div>
                            هر عنوان به‌ترتیب با موتور AI پیش‌فرض تولید می‌شود؛ سپس متای سئو (عنوان، توضیح، کلیدواژه) خودکار نوشته می‌شود.
                            از قطع کردن صفحه در حین تولید خودداری کنید.
                        </div>
                    </div>
                    <div class="bk-pub-actions" style="justify-content:space-between">
                        <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="wiz.subTab='guidelines'">
                            <iconify-icon icon="solar:arrow-right-bold-duotone" width="16"></iconify-icon>
                            قبلی
                        </button>
                        <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="startProduce()"
                                :disabled="batchBusy || !(wiz.selected||[]).filter(x=>x.approved).length"
                                :class="{'is-loading': batchBusy}">
                            <span class="bk-btn-spin" x-show="batchBusy"></span>
                            <iconify-icon icon="solar:play-circle-bold-duotone" width="18" x-show="!batchBusy"></iconify-icon>
                            شروع تولید دسته‌ای
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========== 4. QUEUE ========== -->
            <div x-show="wiz.subTab==='dashboard'" x-cloak class="bk-pub-panel">
                <div class="bk-pub-card">
                    <div class="bk-pub-card__head">
                        <iconify-icon icon="solar:playlist-minimalistic-bold-duotone" width="20" style="color:#fc4c2f"></iconify-icon>
                        <strong>صف تولید دسته‌ای</strong>
                        <span class="bk-pub-badge" x-text="(wiz.queue||[]).length + ' کار'"></span>
                    </div>
                    <div class="bk-pub-empty" x-show="!(wiz.queue||[]).length">
                        <iconify-icon icon="solar:inbox-bold-duotone" width="36" style="color:#cbd5e1"></iconify-icon>
                        <p>صف خالی است. از مرحله ۳ تولید را شروع کنید.</p>
                        <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="wiz.subTab='wizard'">رفتن به تنظیم تولید</button>
                    </div>
                    <div class="bk-pub-queue" x-show="(wiz.queue||[]).length">
                        <template x-for="(job, qi) in (wiz.queue||[])" :key="'q'+qi">
                            <div class="bk-pub-job" :class="'is-'+job.status">
                                <div class="bk-pub-job__top">
                                    <span class="bk-pub-job__status" x-text="jobStatusLabel(job.status)"></span>
                                    <strong class="bk-pub-job__title" x-text="job.title"></strong>
                                    <a class="bk-pub-job__link" x-show="job.post_id" :href="'/wp-admin/post.php?post='+job.post_id+'&action=edit'" target="_blank">ویرایش</a>
                                </div>
                                <div class="bk-pub-job__bar"><i :style="'width:'+(job.progress||0)+'%'"></i></div>
                                <small class="bk-pub-job__step" x-text="job.stepLabel || ''"></small>
                            </div>
                        </template>
                    </div>
                    <div class="bk-pub-actions" style="margin-top:14px" x-show="(wiz.queue||[]).length && !batchBusy">
                        <button type="button" class="bk-wpr-btn bk-wpr-btn--secondary" @click="retryFailedJobs()" x-show="(wiz.queue||[]).some(j=>j.status==='error')">
                            <iconify-icon icon="solar:restart-bold-duotone" width="16"></iconify-icon>
                            تلاش مجدد ناموفق‌ها
                        </button>
                        <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="panel='list'; loadArticles()">
                            <iconify-icon icon="solar:checklist-minimalistic-bold-duotone" width="16"></iconify-icon>
                            بازگشت به لیست مقالات
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Prompt Preview Modal -->
    <template x-teleport="body">
        <div>
            <div class="bk-modal-ai-backdrop" x-show="promptPreviewOpen" x-cloak @click="promptPreviewOpen = false"></div>
            <div class="bk-modal-ai-flow bk-modal-ai-wide" x-show="promptPreviewOpen" x-cloak @click.stop="" style="max-width:800px">
                <div class="bk-modal-head">
                    <div>
                        <strong style="font-size:15px;color:#1f2328">پیش‌نمایش پرامپت هوش مصنوعی</strong>
                        <small style="display:block;font-size:11px;color:#656d76">بررسی دقیق لایه‌های دستورالعمل ترکیبی قبل از ارسال به مدل</small>
                    </div>
                    <button type="button" class="bk-abtn-icon" @click="promptPreviewOpen = false">×</button>
                </div>
                <div class="bk-modal-body" style="padding:16px">
                    <div style="margin-bottom:12px;padding:10px;background:#f6f8fa;border:1px solid #d0d7de;border-radius:8px">
                        <strong style="display:block;font-size:12px;color:#0969da;margin-bottom:4px">لایه‌های فعال دستورالعمل:</strong>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;font-size:11px">
                            <span class="bk-chip is-primary">۱. محدودیت‌های امنیتی سیستم</span>
                            <span class="bk-chip">۲. دستورالعمل عمومی سایت (ذخیره‌شده)</span>
                            <span class="bk-chip">۳. لحن و طول هدف مقاله</span>
                            <span class="bk-chip">۴. عنوان و پرامپت اختصاصی کاربر</span>
                        </div>
                    </div>

                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;color:#1f2328">متن کامل پرامپت سیستمی و کاربر:</label>
                    <textarea readonly rows="12" class="bk-textarea" style="width:100%;font-family:monospace;font-size:11px;line-height:1.5;background:#1f2328;color:#f6f8fa;padding:12px;border-radius:8px;box-sizing:border-box;direction:ltr" x-text="promptPreviewData.full_prompt || 'در حال بارگذاری…'"></textarea>

                    <div style="display:flex;justify-content:flex-end;margin-top:14px">
                        <button type="button" class="bk-btn bk-btn-primary" @click="promptPreviewOpen = false">متوجه شدم</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

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

  </div><!-- /.bk-top-body -->

</div><!-- /tab-articles -->
