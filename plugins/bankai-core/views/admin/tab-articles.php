<?php
/**
 * Admin: Articles hub — list, AI generate, in-panel editor + SEO sidebar
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

$queue = get_option('bankai_ai_publish_queue', []);
if (!is_array($queue)) {
    $queue = [];
}
$queue_json = wp_json_encode(array_values($queue), JSON_UNESCAPED_UNICODE);
?>

<script type="application/json" id="bk-wiz-providers-json"><?php
$__bp = [];
if (class_exists('Bankai_AI_Studio')) {
    foreach (Bankai_AI_Studio::instance()->provider_status_list() as $pr) {
        if (empty($pr['has_key'])) {
            continue; // فقط موتورهای فعال
        }
        $__bp[] = [
            'id' => $pr['id'] ?? '',
            'name' => $pr['name'] ?? ($pr['id'] ?? ''),
            'has_key' => true,
        ];
    }
}
echo wp_json_encode($__bp, JSON_UNESCAPED_UNICODE);
?></script>

<div id="tab-articles" class="bankai-tab-pane" data-bankai-show="activeTab === 'articles'" data-bankai-cloak
     data-bankai-data="bankaiArticlesHub"
     x-init="init()">


    <!-- LIST / GENERATE / SCHEDULE (hidden while editor open) -->
    <div class="bk-art-main">
        <div class="bk-art-head">
            <div>
                <h2>
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    مدیریت مقالات
                </h2>
                <p>فهرست مقالات، تولید و زمان‌بندی با هوش مصنوعی — ویرایش در وردپرس</p>
            </div>
            <button type="button" class="bk-btn bk-btn-primary" data-bankai-click="panel='generate'">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5v14M5 12h14"/></svg>
                تولید مقاله با AI
            </button>
        </div>

        <div class="bk-art-tabs">
            <button type="button" class="bk-pill-tab bk-art-tab" data-bankai-class="{'is-active': panel==='list'}" data-bankai-click="panel='list'; loadArticles()">مشاهده مقالات</button>
            <button type="button" class="bk-pill-tab bk-art-tab" data-bankai-class="{'is-active': panel==='missing-seo'}" data-bankai-click="panel='missing-seo'; initMissingSeo()">مقالات بدون سئو</button>
            <button type="button" class="bk-pill-tab bk-art-tab" data-bankai-class="{'is-active': panel==='generate'}" data-bankai-click="panel='generate'">تولید با AI</button>
            <button type="button" class="bk-pill-tab bk-art-tab" data-bankai-class="{'is-active': panel==='schedule'}" data-bankai-click="panel='schedule'">زمان‌بندی</button>
        </div>

        <div class="bk-art-card" data-bankai-show="panel==='list'" data-bankai-cloak>
            <div class="bk-art-toolbar">
                <div class="bk-art-search">
                    <input type="search" data-bankai-model="searchQ" @keydown.enter="page=1;loadArticles()" placeholder="جستجوی عنوان…">
                    <button type="button" class="bk-btn-icon" data-bankai-click="page=1;loadArticles()" style="border:none;background:transparent;cursor:pointer;color:#0078d4">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    </button>
                </div>
                <select data-bankai-model="orderby" data-bankai-change="loadArticles()">
                    <option value="date">تاریخ انتشار (جدید → قدیم)</option>
                    <option value="modified">آخرین ویرایش</option>
                    <option value="seo_score">امتیاز سئو</option>
                    <option value="views">بازدید</option>
                    <option value="title">عنوان</option>
                </select>
                <select data-bankai-model="order" data-bankai-change="loadArticles()">
                    <option value="DESC">نزولی</option>
                    <option value="ASC">صعودی</option>
                </select>
                <select data-bankai-model="perPage" data-bankai-change="page=1;loadArticles()">
                    <option value="10">۱۰</option>
                    <option value="25">۲۵</option>
                    <option value="50">۵۰</option>
                </select>
                <button type="button" class="bk-btn" data-bankai-click="loadArticles()">تازه‌سازی</button>
            </div>
            
            <!-- Inline SEO / AEO / GEO audit panel (above table) -->
            <div class="bk-audit-panel" data-bankai-show="auditDrawer.open" data-bankai-cloak>
                <div class="bk-audit-panel__top">
                    <div class="bk-audit-panel__meta">
                        <span class="bk-audit-panel__tag">SEO · AEO · GEO</span>
                        <h3 class="bk-audit-panel__title" data-bankai-text="auditDrawer.title || 'آنالیز صفحه'"></h3>
                        <p class="bk-audit-panel__sub" data-bankai-show="auditDrawer.phase==='run'" data-bankai-text="auditDrawer.step || 'در حال آنالیز…'"></p>
                        <p class="bk-audit-panel__sub" data-bankai-show="auditDrawer.phase==='done'">گزارش آماده است — برای جزئیات روی هر آیتم هاور کنید.</p>
                    </div>
                    <div class="bk-audit-panel__rings">
                        <div class="bk-ring-wrap">
                            <svg class="bk-ring" viewBox="0 0 36 36">
                                <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                <circle class="bk-ring-val is-seo" cx="18" cy="18" r="14"
                                    stroke-dasharray="87.96"
                                    data-bankai-stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, (auditDrawer.summary && auditDrawer.summary.seo) || 0)) / 100)"></circle>
                            </svg>
                            <span class="bk-ring-num" data-bankai-text="((auditDrawer.summary && auditDrawer.summary.seo) || 0) + '%'"></span>
                            <span class="bk-ring-lab">SEO</span>
                        </div>
                        <div class="bk-ring-wrap">
                            <svg class="bk-ring" viewBox="0 0 36 36">
                                <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                <circle class="bk-ring-val is-aeo" cx="18" cy="18" r="14"
                                    stroke-dasharray="87.96"
                                    data-bankai-stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, (auditDrawer.summary && auditDrawer.summary.aeo) || 0)) / 100)"></circle>
                            </svg>
                            <span class="bk-ring-num" data-bankai-text="((auditDrawer.summary && auditDrawer.summary.aeo) || 0) + '%'"></span>
                            <span class="bk-ring-lab">AEO</span>
                        </div>
                        <div class="bk-ring-wrap">
                            <svg class="bk-ring" viewBox="0 0 36 36">
                                <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                <circle class="bk-ring-val is-geo" cx="18" cy="18" r="14"
                                    stroke-dasharray="87.96"
                                    data-bankai-stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, (auditDrawer.summary && auditDrawer.summary.geo) || 0)) / 100)"></circle>
                            </svg>
                            <span class="bk-ring-num" data-bankai-text="((auditDrawer.summary && auditDrawer.summary.geo) || 0) + '%'"></span>
                            <span class="bk-ring-lab">GEO</span>
                        </div>
                    </div>
                    <div class="bk-audit-panel__actions">
                        <button type="button" class="bk-abtn bk-abtn-ai" data-bankai-show="auditDrawer.phase==='done' && !auditDrawer.aiRunning"
                            data-bankai-click="runAiFix()" data-bankai-disabled="!!auditBusyId || !failedAuditItems().length">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                            بهبود با هوش مصنوعی
                        </button>
                        <button type="button" class="bk-abtn bk-abtn-success" data-bankai-show="auditDrawer.phase==='done' && auditDrawer.aiReady && !auditDrawer.aiRunning"
                            data-bankai-click="applyAiFix()" data-bankai-disabled="!!auditBusyId">
                            ثبت اصلاحات
                        </button>
                        <button type="button" class="bk-abtn bk-abtn-primary" data-bankai-show="auditDrawer.phase==='done'" data-bankai-click="rerunAudit()" data-bankai-disabled="!!auditBusyId">
                            آنالیز مجدد
                        </button>
                        <button type="button" class="bk-abtn bk-abtn-ghost" data-bankai-show="auditDrawer.phase==='done'"
                            data-bankai-click="auditDrawer.view = auditDrawer.view==='guide' ? 'list' : 'guide'">
                            <span data-bankai-text="auditDrawer.view==='guide' ? 'بازگشت به چک‌لیست' : 'راهنمای رفع خطا'"></span>
                        </button>
                        <button type="button" class="bk-abtn bk-abtn-icon" data-bankai-click="closeAuditPanel()" title="بستن">×</button>
                    </div>
                </div>

                <div class="bk-audit-progress-line" data-bankai-show="auditDrawer.phase==='run'">
                    <div class="bk-audit-progress-line__fill" data-bankai-style="'width:'+(auditDrawer.progress||0)+'%'"></div>
                </div>
                <div class="bk-audit-ai" data-bankai-show="auditDrawer.aiRunning || auditDrawer.aiReady">
                    <div class="bk-audit-progress-line" data-bankai-show="auditDrawer.aiRunning">
                        <div class="bk-audit-progress-line__fill is-ai" data-bankai-style="'width:'+(auditDrawer.aiProgress||0)+'%'"></div>
                    </div>
                    <p class="bk-audit-panel__sub" data-bankai-show="auditDrawer.aiRunning" data-bankai-text="auditDrawer.aiStep || 'در حال بهبود با AI…'"></p>
                    <div class="bk-audit-cols" data-bankai-show="(auditDrawer.aiSteps||[]).length">
                        <div class="bk-audit-col" style="grid-column:1/-1">
                            <div class="bk-audit-col__h">مراحل بهبود AI</div>
                            <template data-bankai-for="s in (auditDrawer.aiSteps||[])" data-bankai-key="'ais'+s.id">
                                <div class="bk-audit-line" data-bankai-class="'st-'+(s.status==='done'?'pass':(s.status==='run'?'run':'pending'))">
                                    <span class="bk-audit-line__ico" data-bankai-html="lineIcon(s.status==='done'?'pass':(s.status==='run'?'run':'pending'))"></span>
                                    <span data-bankai-text="s.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="bk-audit-ai__preview" data-bankai-show="auditDrawer.aiReady && auditDrawer.aiProposed">
                        <div class="bk-audit-ai__preview-title">پیش‌نمایش اصلاحات پیشنهادی</div>
                        <div class="bk-audit-ai__grid">
                            <div data-bankai-show="auditDrawer.aiProposed.seo_title"><small>عنوان سئو</small><strong data-bankai-text="auditDrawer.aiProposed.seo_title"></strong></div>
                            <div data-bankai-show="auditDrawer.aiProposed.description"><small>توضیحات متا</small><strong data-bankai-text="auditDrawer.aiProposed.description"></strong></div>
                            <div data-bankai-show="auditDrawer.aiProposed.content_changed" style="grid-column:1/-1"><small>تغییر محتوا</small><strong data-bankai-text="auditDrawer.aiProposed.content_preview"></strong></div>
                        </div>
                    </div>
                </div>


                <!-- Running checklist: 3 columns -->
                <div class="bk-audit-cols" data-bankai-show="auditDrawer.phase==='run'">
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">SEO</div>
                        <template data-bankai-for="item in (auditDrawer.liveSeo||[])" data-bankai-key="'ls'+item.key">
                            <div class="bk-audit-line" data-bankai-class="'st-'+item.state"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" data-bankai-html="lineIcon(item.state)"></span>
                                <span data-bankai-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">AEO</div>
                        <template data-bankai-for="item in (auditDrawer.liveAeo||[])" data-bankai-key="'la'+item.key">
                            <div class="bk-audit-line" data-bankai-class="'st-'+item.state"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" data-bankai-html="lineIcon(item.state)"></span>
                                <span data-bankai-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">GEO</div>
                        <template data-bankai-for="item in (auditDrawer.liveGeo||[])" data-bankai-key="'lg'+item.key">
                            <div class="bk-audit-line" data-bankai-class="'st-'+item.state"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" data-bankai-html="lineIcon(item.state)"></span>
                                <span data-bankai-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Done: checklist or guide -->
                <div class="bk-audit-cols" data-bankai-show="auditDrawer.phase==='done' && auditDrawer.view!=='guide'">
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">SEO <small data-bankai-text="'('+((auditDrawer.summary&&auditDrawer.summary.seo_issues)||0)+' مورد)'"></small></div>
                        <template data-bankai-for="item in ((auditDrawer.result&&auditDrawer.result.seo)||[])" data-bankai-key="'s'+item.key">
                            <div class="bk-audit-line" data-bankai-class="item.pass ? 'st-pass' : 'st-fail'"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" data-bankai-html="lineIcon(item.pass?'pass':'fail')"></span>
                                <span data-bankai-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">AEO <small data-bankai-text="'('+((auditDrawer.summary&&auditDrawer.summary.aeo_issues)||0)+' مورد)'"></small></div>
                        <template data-bankai-for="item in ((auditDrawer.result&&auditDrawer.result.aeo)||[])" data-bankai-key="'a'+item.key">
                            <div class="bk-audit-line" data-bankai-class="item.pass ? 'st-pass' : 'st-fail'"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" data-bankai-html="lineIcon(item.pass?'pass':'fail')"></span>
                                <span data-bankai-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">GEO <small data-bankai-text="'('+((auditDrawer.summary&&auditDrawer.summary.geo_issues)||0)+' مورد)'"></small></div>
                        <template data-bankai-for="item in ((auditDrawer.result&&auditDrawer.result.geo)||[])" data-bankai-key="'g'+item.key">
                            <div class="bk-audit-line" data-bankai-class="item.pass ? 'st-pass' : 'st-fail'"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" data-bankai-html="lineIcon(item.pass?'pass':'fail')"></span>
                                <span data-bankai-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bk-audit-guide" data-bankai-show="auditDrawer.phase==='done' && auditDrawer.view==='guide'">
                    <div class="bk-audit-guide__head">راهنمای رفع موارد ناموفق</div>
                    <div class="bk-audit-guide__grid" data-bankai-show="failedAuditItems().length">
                        <template data-bankai-for="item in failedAuditItems()" data-bankai-key="'f'+item.key">
                            <div class="bk-guide-card">
                                <div class="bk-guide-card__top">
                                    <span class="bk-tip__badge is-fail">نیاز به اصلاح</span>
                                    <em data-bankai-text="(item.group||'seo').toUpperCase()"></em>
                                </div>
                                <strong class="bk-guide-card__title" data-bankai-text="item.label"></strong>
                                <p class="bk-guide-card__body" data-bankai-text="expandGuideHint(item)"></p>
                            </div>
                        </template>
                    </div>
                    <p class="bk-audit-guide__empty" data-bankai-show="!failedAuditItems().length">همه موارد پاس شده‌اند — نیازی به اصلاح نیست.</p>
                </div>

                <!-- Floating tip -->
                <div class="bk-tip" data-bankai-show="tip.show" data-bankai-cloak data-bankai-style="'top:'+tip.y+'px;left:'+tip.x+'px'" @mouseenter="tip.sticky=true" @mouseleave="hideTip()">
                    <div class="bk-tip__badge" data-bankai-class="'is-'+(tip.pass?'pass':'fail')" data-bankai-text="tip.pass ? 'پاس' : 'نیاز به اصلاح'"></div>
                    <strong class="bk-tip__title" data-bankai-text="tip.title"></strong>
                    <p class="bk-tip__body" data-bankai-text="tip.body"></p>
                </div>
            </div>

            <div style="overflow:auto">
                <table class="bk-art-table">
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>SEO</th>
                            <th>AEO</th>
                            <th>GEO</th>
                            <th>وضعیت</th>
                            <th style="text-align:left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-bankai-show="loading"><td colspan="6" style="text-align:center;padding:24px;color:#8a8886">در حال بارگذاری…</td></tr>
                        <tr data-bankai-show="!loading && !articles.length"><td colspan="6" style="text-align:center;padding:24px;color:#8a8886">مقاله‌ای یافت نشد.</td></tr>
                        <template data-bankai-for="row in articles" data-bankai-key="row.id">
                            <tr>
                                <td>
                                    <strong class="bk-art-title" data-bankai-text="row.title"></strong>
                                    <div class="bk-art-date">
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="12" height="12"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                        <span data-bankai-text="row.date || row.modified"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="bk-mini-ring" title="SEO">
                                        <svg viewBox="0 0 36 36">
                                            <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                            <circle class="bk-ring-val is-seo" cx="18" cy="18" r="14" stroke-dasharray="87.96"
                                                data-bankai-stroke-dashoffset="ringOffset(row.seo_audit)"></circle>
                                        </svg>
                                        <span data-bankai-text="ringLabel(row.seo_audit)"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="bk-mini-ring" title="AEO">
                                        <svg viewBox="0 0 36 36">
                                            <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                            <circle class="bk-ring-val is-aeo" cx="18" cy="18" r="14" stroke-dasharray="87.96"
                                                data-bankai-stroke-dashoffset="ringOffset(row.aeo_audit)"></circle>
                                        </svg>
                                        <span data-bankai-text="ringLabel(row.aeo_audit)"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="bk-mini-ring" title="GEO">
                                        <svg viewBox="0 0 36 36">
                                            <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                            <circle class="bk-ring-val is-geo" cx="18" cy="18" r="14" stroke-dasharray="87.96"
                                                data-bankai-stroke-dashoffset="ringOffset(row.geo_audit)"></circle>
                                        </svg>
                                        <span data-bankai-text="ringLabel(row.geo_audit)"></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="bk-status-badge" data-bankai-class="'is-' + (row.status || 'draft')">
                                        <span class="bk-status-badge__ico" data-bankai-html="statusIcon(row.status)"></span>
                                        <span data-bankai-text="row.status_label || row.status"></span>
                                    </span>
                                </td>
                                <td>
                                    <div class="bk-actions">
                                        <button type="button" class="bk-btn bk-btn-icon" title="گزارش SEO / AEO / GEO" data-bankai-click="runRowAudit(row)" data-bankai-disabled="auditBusyId === row.id">
                                            <svg data-bankai-show="auditBusyId !== row.id" class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19V9M4 19h16"/><path d="M8 15v-4"/><path d="M12 15V7"/><path d="M16 15v-6"/></svg>
                                            <span data-bankai-show="auditBusyId === row.id" class="bk-mini-spin"></span>
                                        </button>
                                        <a class="bk-btn bk-btn-icon bk-btn-primary" data-bankai-href="wpEditUrl(row.id)" title="ویرایش">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div><!-- /overflow table -->
        </div><!-- /panel list -->

        <!-- Missing SEO AI Assistant -->
        <div class="bk-art-card bk-import-ai" data-bankai-show="panel==='missing-seo'" data-bankai-cloak style="padding:0;overflow:hidden;">
            <div class="bk-ai-assistant-shell" style="border:none;box-shadow:none;margin:0;">
                <header class="bk-modal-head">
                    <div class="bk-modal-title">
                        <div class="bk-ai-logo-icon">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        </div>
                        <div>
                            <strong>دستیار هوشمند سئو و محتوا Bankai</strong>
                            <small>سئوی خودکار مقالات، برگه‌ها و محصولات بدون متای Bankai — متا، کلیدواژه، لینک، تصویر، اسکیما و دسته/برچسب</small>
                        </div>
                    </div>
                </header>

                <div class="bk-ai-engine-bar">
                    <div class="bk-ai-engine-label">انتخاب موتور AI</div>
                    <div class="bk-ai-engine-scroll">
                        <template data-bankai-for="p in (ms.textProviders || [])" data-bankai-key="'mt'+p.id">
                            <button type="button" class="bk-ai-engine-card"
                                data-bankai-class="{ 'is-active': ms.textProvider === p.id, 'is-disabled': !p.ready }"
                                data-bankai-click="ms.textProvider = p.id">
                                <span class="bk-ai-engine-name" data-bankai-text="p.name"></span>
                                <span class="bk-ai-engine-status" data-bankai-text="ms.textProvider === p.id ? 'فعال' : (p.ready ? 'انتخاب' : 'بدون کلید')"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="bk-import-toolbar">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <strong style="font-size:13px;">مقالات بدون سئوی Bankai</strong>
                        <span style="font-size:12px;color:#64748b;" data-bankai-text="(ms.total||0) + ' مورد'"></span>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button type="button" class="bk-btn-ghost" data-bankai-click="msToggleAll()" data-bankai-disabled="ms.aiRunning" data-bankai-text="ms.allSelected ? 'لغو انتخاب همه' : 'انتخاب همه'"></button>
                        <button type="button" class="bk-btn-ghost" data-bankai-click="loadMissingSeoList()" data-bankai-disabled="ms.aiRunning">تازه‌سازی</button>
                        <button type="button" class="bk-btn-primary" data-bankai-click="runMissingSeoBatch()"
                            data-bankai-disabled="ms.aiRunning || !msSelectedCount() || !ms.textProvider"
                            data-bankai-text="ms.aiRunning ? ('در حال اجرا… '+(ms.aiProgress||0)+'%') : ('اجرای یک‌پارچه ('+msSelectedCount()+')')"></button>
                        <button type="button" class="bk-btn-ghost" data-bankai-show="ms.aiRunning" data-bankai-click="ms.aiAbort=true">توقف</button>
                        <button type="button" class="bk-btn-success" data-bankai-show="!ms.aiRunning && msReadyCount()>0"
                            data-bankai-click="msApproveAll()" data-bankai-text="'اعمال تغییرات تاییدشده ('+msReadyCount()+')'"></button>
                    </div>
                </div>

                <div style="padding:14px 16px;">
                    <template data-bankai-if="ms.loaded && !(ms.items||[]).length">
                        <div style="padding:28px 16px;text-align:center;border:1px dashed #86efac;border-radius:14px;background:#f0fdf4;">
                            <div style="font-size:28px;margin-bottom:8px;">✓</div>
                            <strong style="display:block;font-size:15px;color:#166534;margin-bottom:6px;">عالی — همه چیز سئو شده است</strong>
                            <p style="margin:0;font-size:13px;color:#15803d;line-height:1.6;">مقاله، برگه یا محصولی بدون سئوی Bankai باقی نمانده است.</p>
                        </div>
                    </template>
                    <template data-bankai-if="!ms.loaded">
                        <p style="margin:0;color:#64748b;font-size:13px;">در حال بررسی مقالات، برگه‌ها و محصولات…</p>
                    </template>
                    <template data-bankai-for="row in (ms.items||[])" data-bankai-key="row.id">
                        <div class="bk-post-ai-card"
                             data-bankai-class="{'is-running':row.aiPhase==='running','is-review':row.aiPhase==='review','is-done':row.aiPhase==='done','is-error':row.aiPhase==='error'}">
                            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                                <div style="display:flex;gap:10px;align-items:flex-start;">
                                    <input type="checkbox" :value="row.id" data-bankai-model="ms.selected" data-bankai-disabled="ms.aiRunning && row.aiPhase==='running'">
                                    <div>
                                        <div style="font-weight:800;font-size:14px;" data-bankai-text="row.title"></div>
                                        <div style="font-size:12px;color:#64748B;margin-top:2px;">
                                            <span data-bankai-show="row.type_label" data-bankai-text="row.type_label" style="margin-inline-end:6px;font-weight:700;color:#6b4eff;"></span>
                                            <span data-bankai-text="row.status"></span>
                                            <span data-bankai-show="row.score"> · امتیاز: <strong data-bankai-text="row.score"></strong></span>
                                            <span data-bankai-show="row.aiPhase" style="margin-inline-start:6px;" data-bankai-text="msPhaseLabel(row.aiPhase)"></span>
                                        </div>
                                    </div>
                                </div>
                                <a data-bankai-href="row.edit_url" target="_blank" rel="noopener" style="font-size:12px;">ویرایش مطلب ↗</a>
                            </div>
                            <div data-bankai-show="row.aiPhase==='running' || (row.aiProgress||0)>0" style="margin-top:12px;">
                                <div style="display:flex;justify-content:space-between;font-size:12px;color:#475569;">
                                    <span data-bankai-text="row.aiStepText||''"></span>
                                    <span data-bankai-text="(row.aiProgress||0)+'%'"></span>
                                </div>
                                <div class="bk-progress-track"><div class="bk-progress-fill" data-bankai-style="'width:'+(row.aiProgress||0)+'%'"></div></div>
                            </div>
                            <ul class="bk-checklist-grid" data-bankai-show="row.checklist && row.checklist.length">
                                <template data-bankai-for="item in row.checklist" data-bankai-key="item.id">
                                    <li>
                                        <span data-bankai-show="item.status==='pending'" style="opacity:.35;">○</span>
                                        <span data-bankai-show="item.status==='loading'">…</span>
                                        <span data-bankai-show="item.status==='success'" style="color:#16a34a;font-weight:800;">✓</span>
                                        <span data-bankai-show="item.status==='error'" style="color:#dc2626;font-weight:800;">✕</span>
                                        <span data-bankai-text="item.label"></span>
                                    </li>
                                </template>
                            </ul>
                            <div data-bankai-show="row.aiPhase==='review' || row.aiPhase==='done' || row.aiPhase==='error'" style="margin-top:14px;padding-top:12px;border-top:1px solid #E2E8F0;">
                                <h5 style="margin:0 0 8px;font-size:13px;font-weight:800;">خلاصه نتایج (Review & Approve)</h5>
                                <div class="bk-review-box" data-bankai-show="row.draft">
                                    <div><strong>عنوان سئو:</strong> <span data-bankai-text="(row.draft&&row.draft.seo_title)||'—'"></span></div>
                                    <div><strong>متا:</strong> <span data-bankai-text="(row.draft&&row.draft.description)||'—'"></span></div>
                                    <div><strong>کلیدواژه:</strong> <span data-bankai-text="(row.draft&&row.draft.focus_keyword)||'—'"></span></div>
                                    <div><strong>کلیدواژه‌ها:</strong> <span data-bankai-text="(row.draft&&row.draft.keywords&&row.draft.keywords.join('، '))||'—'"></span></div>
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;" data-bankai-show="row.aiPhase==='review'">
                                    <button type="button" class="bk-btn-success" data-bankai-click="msApprove(row)" data-bankai-disabled="row.saving" data-bankai-text="row.saving?'در حال ذخیره…':'اعمال تغییرات تاییدشده'"></button>
                                    <button type="button" class="bk-btn-ghost" data-bankai-click="msRerun(row)" data-bankai-disabled="ms.aiRunning">بازتولید</button>
                                    <button type="button" class="bk-btn-ghost" data-bankai-click="msCancel(row)">لغو</button>
                                </div>
                                <div data-bankai-show="row.aiPhase==='done'" style="margin-top:8px;color:#166534;font-weight:700;font-size:13px;">✓ ذخیره شد — امتیاز: <span data-bankai-text="row.score"></span></div>
                                <div data-bankai-show="row.aiPhase==='error'" style="margin-top:8px;color:#991b1b;font-size:13px;" data-bankai-text="row.aiError||'خطا'"></div>
                            </div>
                        </div>
                    </template>
                    <div style="margin-top:10px;display:flex;gap:8px;align-items:center;" data-bankai-show="ms.pages>1">
                        <button type="button" class="bk-btn-ghost" data-bankai-disabled="ms.page<=1||ms.aiRunning" data-bankai-click="ms.page--; loadMissingSeoList()">قبلی</button>
                        <span data-bankai-text="ms.page+' / '+ms.pages"></span>
                        <button type="button" class="bk-btn-ghost" data-bankai-disabled="ms.page>=ms.pages||ms.aiRunning" data-bankai-click="ms.page++; loadMissingSeoList()">بعدی</button>
                    </div>
                </div>
            </div>
        </div>

                        <div class="bk-art-card bk-wiz" data-bankai-show="panel==='generate'" data-bankai-cloak>
            <!-- Global progress -->
            <div class="bk-wiz-topbar">
                <div>
                    <h3>ویزارد هوشمند تولید و زمان‌بندی مقاله با AI</h3>
                    <p>پیشنهاد عنوان بر اساس مقالات موجود، نگارش و بهینه‌سازی کامل سئو</p>
                </div>
                <div class="bk-wiz-global-progress" data-bankai-show="batchBusy || wizBusy || wiz.globalProgress > 0" data-bankai-cloak>
                    <div class="bk-ai-progress-meta">
                        <span data-bankai-text="wiz.globalLabel || 'در حال پردازش…'"></span>
                        <span data-bankai-text="(wiz.globalProgress||0) + '%'"></span>
                    </div>
                    <div class="bk-ai-progress-track"><div class="bk-ai-progress-fill" data-bankai-style="'width:' + (wiz.globalProgress||0) + '%'"></div></div>
                </div>
            </div>

            <!-- AI engines as cards -->
            <div class="bk-wiz-engines">
                <div class="bk-wiz-engines-label">موتور AI فعال</div>
                <div class="bk-wiz-engine-grid">
                    <template data-bankai-for="p in wizProviders" data-bankai-key="p.id">
                        <button type="button" class="bk-wiz-engine-card"
                                data-bankai-class="{'is-active': wiz.provider===p.id, 'is-disabled': !p.has_key}"
                                data-bankai-disabled="!p.has_key"
                                data-bankai-click="wiz.provider=p.id">
                            <span class="bk-wiz-engine-dot" data-bankai-style="p.has_key ? 'background:#1a7f37' : 'background:#cf222e'"></span>
                            <strong data-bankai-text="p.name || p.id"></strong>
                            <small data-bankai-text="p.has_key ? 'آماده' : 'بدون کلید'"></small>
                        </button>
                    </template>
                </div>
            </div>

            <!-- SETUP FORM (hidden while loading titles or after titles ready) -->
            <div data-bankai-show="!wizBusy && wiz.phase==='setup'" data-bankai-cloak>
                <div class="bk-field">
                    <label>راهنمای انتخاب / تولید عنوان‌ها</label>
                    <textarea data-bankai-model="wiz.topic" rows="3" placeholder="موضوع، زاویه محتوا یا کلمه کلیدی… عناوین و کلیدواژه‌های مقالات قبلی سایت به‌صورت خودکار به AI ارسال می‌شود."></textarea>
                </div>
                <div class="bk-wiz-row2">
                    <div class="bk-field">
                        <label>تعداد پیشنهاد</label>
                        <select data-bankai-model.number="wiz.count">
                            <option :value="5">۵</option>
                            <option :value="10">۱۰</option>
                            <option :value="15">۱۵</option>
                            <option :value="20">۲۰</option>
                        </select>
                    </div>
                    <div class="bk-field">
                        <label>طول پیش‌فرض مقاله (کلمه)</label>
                        <select data-bankai-model="wiz.defaultLength">
                            <option value="1500">۱٬۵۰۰</option>
                            <option value="3000">۳٬۰۰۰</option>
                            <option value="5000">۵٬۰۰۰</option>
                            <option value="7000">۷٬۰۰۰</option>
                        </select>
                    </div>
                </div>
                <div class="bk-field">
                    <label>دستورالعمل سراسری (اختیاری)</label>
                    <textarea data-bankai-model="wiz.defaultNotes" rows="3" placeholder="لحن، مخاطب، نکات اجباری…"></textarea>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
                    <button type="button" class="bk-btn bk-btn-primary" data-bankai-click="suggestTitles()" data-bankai-disabled="!wiz.topic.trim()">
                        دریافت پیشنهاد عنوان از AI
                    </button>
                    <button type="button" class="bk-btn" data-bankai-click="addManualTitle()">افزودن عنوان دستی</button>
                </div>
            </div>

            <!-- LOADING titles -->
            <div class="bk-wiz-loader" data-bankai-show="wizBusy" data-bankai-cloak>
                <div class="bk-wiz-spinner"></div>
                <p>در حال تولید عناوین بر اساس مقالات و کلیدواژه‌های سایت…</p>
            </div>

            <!-- TITLE CARDS -->
            <div data-bankai-show="!wizBusy && wiz.phase==='titles'" data-bankai-cloak>
                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap">
                    <strong style="font-size:13px">عناوین پیشنهادی — تأیید و تنظیم کنید</strong>
                    <button type="button" class="bk-btn" data-bankai-click="wiz.phase='setup'; wiz.selected=[]; wiz.suggestions=[]">بازگشت به فرم</button>
                </div>
                <template data-bankai-for="(item, idx) in wiz.selected" data-bankai-key="'card'+idx">
                    <div class="bk-wiz-title-card" data-bankai-class="{'is-checked': item.approved}">
                        <label class="bk-wiz-check">
                            <input type="checkbox" data-bankai-model="item.approved">
                            <span>تأیید برای تولید</span>
                        </label>
                        <input type="text" class="bk-wiz-title-input" data-bankai-model="item.title">
                        <div class="bk-wiz-card-grid">
                            <div class="bk-field">
                                <label>طول مقاله</label>
                                <select data-bankai-model="item.length">
                                    <option value="1500">۱٬۵۰۰</option>
                                    <option value="3000">۳٬۰۰۰</option>
                                    <option value="5000">۵٬۰۰۰</option>
                                    <option value="7000">۷٬۰۰۰</option>
                                </select>
                            </div>
                            <div class="bk-field">
                                <label>وضعیت انتشار</label>
                                <select data-bankai-model="item.pubStatus">
                                    <option value="draft">پیش‌نویس</option>
                                    <option value="publish">انتشار فوری</option>
                                    <option value="future">زمان‌بندی</option>
                                </select>
                            </div>
                            <div class="bk-field" data-bankai-show="item.pubStatus==='future'" data-bankai-cloak style="grid-column:1/-1">
                                <label>تاریخ انتشار (جلالی)</label>
                                <button type="button" class="bk-sched-toggle" data-bankai-click="openWizItemSchedule(idx)"
                                        :aria-expanded="wizSchedIdx===idx && wizItemSchedOpen ? 'true' : 'false'">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    <span data-bankai-text="wizItemScheduleLabel(item)"></span>
                                </button>
                            </div>
                        </div>
                        <div class="bk-field" style="margin-top:6px">
                            <label>دستورالعمل این عنوان</label>
                            <input type="text" data-bankai-model="item.notes" :placeholder="wiz.defaultNotes || 'ارث از سراسری'">
                        </div>
                    </div>
                </template>
                <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
                    <button type="button" class="bk-btn bk-btn-primary" data-bankai-click="startProductionFromCards()"
                            data-bankai-disabled="batchBusy || !wiz.selected.filter(s=>s.approved).length">
                        تهیه و تولید مقاله
                    </button>
                    <span style="font-size:12px;color:#656d76;align-self:center"
                          data-bankai-text="wiz.selected.filter(s=>s.approved).length + ' عنوان تأیید شده'"></span>
                </div>
            </div>

            <!-- PRODUCTION QUEUE -->
            <div data-bankai-show="wiz.phase==='produce'" data-bankai-cloak style="margin-top:8px">
                <div class="bk-wiz-paused" data-bankai-show="wiz.paused" data-bankai-cloak>
                    <strong>توقف به‌خاطر خطا</strong>
                    <p data-bankai-text="wiz.pauseMessage || 'یک مقاله با خطا متوقف شد. اتصال AI را بررسی و ادامه دهید.'"></p>
                    <button type="button" class="bk-btn bk-btn-primary" data-bankai-click="continueBatchAfterCheck()" data-bankai-disabled="batchBusy || wiz.checkingConn">
                        <span data-bankai-text="wiz.checkingConn ? 'بررسی اتصال…' : 'ادامه فرایند'"></span>
                    </button>
                    <button type="button" class="bk-btn" data-bankai-click="cancelWizardProcess()" data-bankai-disabled="batchBusy">
                        لغو و شروع مجدد
                    </button>
                </div>
                <template data-bankai-for="(job, ji) in wiz.queue" data-bankai-key="'job'+ji">
                    <div class="bk-wiz-job" data-bankai-class="'is-' + job.status">
                        <div class="bk-wiz-job-head">
                            <strong data-bankai-text="job.title"></strong>
                            <span class="bk-wiz-badge" data-bankai-text="jobStatusLabel(job.status)"></span>
                        </div>
                        <div class="bk-ai-progress">
                            <div class="bk-ai-progress-meta">
                                <span data-bankai-text="job.stepLabel || 'آماده'"></span>
                                <span data-bankai-text="(job.progress||0) + '%'"></span>
                            </div>
                            <div class="bk-ai-progress-track"><div class="bk-ai-progress-fill" data-bankai-style="'width:' + (job.progress||0) + '%'"></div></div>
                        </div>
                        <ul class="bk-wiz-checklist">
                            <li data-bankai-class="{'is-done': job.progress>=15}">تحلیل محتوا و ساختار مقاله</li>
                            <li data-bankai-class="{'is-done': job.progress>=35}">تولید خودکار عنوان سئو و متا دیسکریپشن</li>
                            <li data-bankai-class="{'is-done': job.progress>=50}">استخراج کلمات کلیدی + ادغام ثابت‌ها</li>
                            <li data-bankai-class="{'is-done': job.progress>=65}">لینک‌سازی هوشمند داخلی با مقالات مرتبط</li>
                            <li data-bankai-class="{'is-done': job.progress>=80}">سئوی تصاویر و تولید Alt Text خودکار</li>
                            <li data-bankai-class="{'is-done': job.progress>=100}">دسته و برچسب خودکار (موجود یا ایجاد جدید)</li>
                        </ul>
                        <div style="font-size:11px;color:#656d76;margin-top:4px" data-bankai-show="job.message" data-bankai-text="job.message"></div>
                        <div style="margin-top:6px" data-bankai-show="job.post_id">
                            <a class="bk-btn bk-btn-primary" data-bankai-href="wpEditUrl(job.post_id)">ویرایش مقاله</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

<div class="bk-art-card" data-bankai-show="panel==='schedule'" data-bankai-cloak>
            <h3 style="margin:0 0 12px;font-size:16px;font-weight:800">صف زمان‌بندی</h3>
            <template data-bankai-if="!queue.length"><p style="color:#8a8886;font-size:13px">موردی در صف نیست.</p></template>
            <template data-bankai-for="(item, idx) in queue" data-bankai-key="'q'+idx">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f1f5f9;gap:10px;flex-wrap:wrap">
                    <div>
                        <strong data-bankai-text="item.title || item.topic"></strong>
                        <div style="font-size:11px;color:#8a8886" data-bankai-text="item.schedule_at || '—'"></div>
                    </div>
                    <div class="bk-actions">
                        <a class="bk-btn bk-btn-primary" data-bankai-show="item.post_id" data-bankai-href="wpEditUrl(item.post_id)">ویرایش مقاله</a>
                        <button type="button" class="bk-btn" data-bankai-click="removeQueueItem(idx)">حذف از صف</button>
                    </div>
                </div>
            </template>
        </div><!-- /schedule -->
    </div><!-- /bk-art-main -->
</div><!-- /tab-articles -->

<script>
window.bankaiArticlesQueue = <?php echo $queue_json ?: '[]'; ?>;
</script>
