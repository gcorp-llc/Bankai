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

<div id="tab-articles" class="bankai-tab-pane" x-show="activeTab === 'articles'" x-cloak
     x-data="bankaiArticlesHub"
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
            <button type="button" class="bk-btn bk-btn-primary" @click="panel='generate'">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5v14M5 12h14"/></svg>
                تولید مقاله با AI
            </button>
        </div>

        <div class="bk-art-tabs">
            <button type="button" class="bk-pill-tab bk-art-tab" :class="{'is-active': panel==='list'}" @click="panel='list'; loadArticles()">مشاهده مقالات</button>
            <button type="button" class="bk-pill-tab bk-art-tab" :class="{'is-active': panel==='missing-seo'}" @click="panel='missing-seo'; initMissingSeo()">مقالات بدون سئو</button>
            <button type="button" class="bk-pill-tab bk-art-tab" :class="{'is-active': panel==='generate'}" @click="panel='generate'">تولید با AI</button>
            <button type="button" class="bk-pill-tab bk-art-tab" :class="{'is-active': panel==='schedule'}" @click="panel='schedule'">زمان‌بندی</button>
        </div>

        <div class="bk-art-card" x-show="panel==='list'" x-cloak>
            <div class="bk-art-toolbar">
                <div class="bk-art-search">
                    <input type="search" x-model="searchQ" @keydown.enter="page=1;loadArticles()" placeholder="جستجوی عنوان…">
                    <button type="button" class="bk-btn-icon" @click="page=1;loadArticles()" style="border:none;background:transparent;cursor:pointer;color:#0078d4">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    </button>
                </div>
                <select x-model="orderby" @change="loadArticles()">
                    <option value="date">تاریخ انتشار (جدید → قدیم)</option>
                    <option value="modified">آخرین ویرایش</option>
                    <option value="seo_score">امتیاز سئو</option>
                    <option value="views">بازدید</option>
                    <option value="title">عنوان</option>
                </select>
                <select x-model="order" @change="loadArticles()">
                    <option value="DESC">نزولی</option>
                    <option value="ASC">صعودی</option>
                </select>
                <select x-model="perPage" @change="page=1;loadArticles()">
                    <option value="10">۱۰</option>
                    <option value="25">۲۵</option>
                    <option value="50">۵۰</option>
                </select>
                <button type="button" class="bk-btn" @click="loadArticles()">تازه‌سازی</button>
            </div>
            
            <!-- Inline SEO / AEO / GEO audit panel (above table) -->
            <div class="bk-audit-panel" x-show="auditDrawer.open" x-cloak>
                <div class="bk-audit-panel__top">
                    <div class="bk-audit-panel__meta">
                        <span class="bk-audit-panel__tag">SEO · AEO · GEO</span>
                        <h3 class="bk-audit-panel__title" x-text="auditDrawer.title || 'آنالیز صفحه'"></h3>
                        <p class="bk-audit-panel__sub" x-show="auditDrawer.phase==='run'" x-text="auditDrawer.step || 'در حال آنالیز…'"></p>
                        <p class="bk-audit-panel__sub" x-show="auditDrawer.phase==='done'">گزارش آماده است — برای جزئیات روی هر آیتم هاور کنید.</p>
                    </div>
                    <div class="bk-audit-panel__rings">
                        <div class="bk-ring-wrap">
                            <svg class="bk-ring" viewBox="0 0 36 36">
                                <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                <circle class="bk-ring-val is-seo" cx="18" cy="18" r="14"
                                    stroke-dasharray="87.96"
                                    :stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, (auditDrawer.summary && auditDrawer.summary.seo) || 0)) / 100)"></circle>
                            </svg>
                            <span class="bk-ring-num" x-text="((auditDrawer.summary && auditDrawer.summary.seo) || 0) + '%'"></span>
                            <span class="bk-ring-lab">SEO</span>
                        </div>
                        <div class="bk-ring-wrap">
                            <svg class="bk-ring" viewBox="0 0 36 36">
                                <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                <circle class="bk-ring-val is-aeo" cx="18" cy="18" r="14"
                                    stroke-dasharray="87.96"
                                    :stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, (auditDrawer.summary && auditDrawer.summary.aeo) || 0)) / 100)"></circle>
                            </svg>
                            <span class="bk-ring-num" x-text="((auditDrawer.summary && auditDrawer.summary.aeo) || 0) + '%'"></span>
                            <span class="bk-ring-lab">AEO</span>
                        </div>
                        <div class="bk-ring-wrap">
                            <svg class="bk-ring" viewBox="0 0 36 36">
                                <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                <circle class="bk-ring-val is-geo" cx="18" cy="18" r="14"
                                    stroke-dasharray="87.96"
                                    :stroke-dashoffset="87.96 - (87.96 * Math.min(100, Math.max(0, (auditDrawer.summary && auditDrawer.summary.geo) || 0)) / 100)"></circle>
                            </svg>
                            <span class="bk-ring-num" x-text="((auditDrawer.summary && auditDrawer.summary.geo) || 0) + '%'"></span>
                            <span class="bk-ring-lab">GEO</span>
                        </div>
                    </div>
                    <div class="bk-audit-panel__actions">
                        <button type="button" class="bk-abtn bk-abtn-ai" x-show="auditDrawer.phase==='done' && !auditDrawer.aiRunning"
                            @click="runAiFix()" :disabled="!!auditBusyId || !failedAuditItems().length">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                            بهبود با هوش مصنوعی
                        </button>
                        <button type="button" class="bk-abtn bk-abtn-success" x-show="auditDrawer.phase==='done' && auditDrawer.aiReady && !auditDrawer.aiRunning"
                            @click="applyAiFix()" :disabled="!!auditBusyId">
                            ثبت اصلاحات
                        </button>
                        <button type="button" class="bk-abtn bk-abtn-primary" x-show="auditDrawer.phase==='done'" @click="rerunAudit()" :disabled="!!auditBusyId">
                            آنالیز مجدد
                        </button>
                        <button type="button" class="bk-abtn bk-abtn-ghost" x-show="auditDrawer.phase==='done'"
                            @click="auditDrawer.view = auditDrawer.view==='guide' ? 'list' : 'guide'">
                            <span x-text="auditDrawer.view==='guide' ? 'بازگشت به چک‌لیست' : 'راهنمای رفع خطا'"></span>
                        </button>
                        <button type="button" class="bk-abtn bk-abtn-icon" @click="closeAuditPanel()" title="بستن">×</button>
                    </div>
                </div>

                <div class="bk-audit-progress-line" x-show="auditDrawer.phase==='run'">
                    <div class="bk-audit-progress-line__fill" :style="'width:'+(auditDrawer.progress||0)+'%'"></div>
                </div>
                <div class="bk-audit-ai" x-show="auditDrawer.aiRunning || auditDrawer.aiReady">
                    <div class="bk-audit-progress-line" x-show="auditDrawer.aiRunning">
                        <div class="bk-audit-progress-line__fill is-ai" :style="'width:'+(auditDrawer.aiProgress||0)+'%'"></div>
                    </div>
                    <p class="bk-audit-panel__sub" x-show="auditDrawer.aiRunning" x-text="auditDrawer.aiStep || 'در حال بهبود با AI…'"></p>
                    <div class="bk-audit-cols" x-show="(auditDrawer.aiSteps||[]).length">
                        <div class="bk-audit-col" style="grid-column:1/-1">
                            <div class="bk-audit-col__h">مراحل بهبود AI</div>
                            <template x-for="s in (auditDrawer.aiSteps||[])" :key="'ais'+s.id">
                                <div class="bk-audit-line" :class="'st-'+(s.status==='done'?'pass':(s.status==='run'?'run':'pending'))">
                                    <span class="bk-audit-line__ico" x-html="lineIcon(s.status==='done'?'pass':(s.status==='run'?'run':'pending'))"></span>
                                    <span x-text="s.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="bk-audit-ai__preview" x-show="auditDrawer.aiReady && auditDrawer.aiProposed">
                        <div class="bk-audit-ai__preview-title">پیش‌نمایش اصلاحات پیشنهادی</div>
                        <div class="bk-audit-ai__grid">
                            <div x-show="auditDrawer.aiProposed.seo_title"><small>عنوان سئو</small><strong x-text="auditDrawer.aiProposed.seo_title"></strong></div>
                            <div x-show="auditDrawer.aiProposed.description"><small>توضیحات متا</small><strong x-text="auditDrawer.aiProposed.description"></strong></div>
                            <div x-show="auditDrawer.aiProposed.content_changed" style="grid-column:1/-1"><small>تغییر محتوا</small><strong x-text="auditDrawer.aiProposed.content_preview"></strong></div>
                        </div>
                    </div>
                </div>


                <!-- Running checklist: 3 columns -->
                <div class="bk-audit-cols" x-show="auditDrawer.phase==='run'">
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">SEO</div>
                        <template x-for="item in (auditDrawer.liveSeo||[])" :key="'ls'+item.key">
                            <div class="bk-audit-line" :class="'st-'+item.state"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" x-html="lineIcon(item.state)"></span>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">AEO</div>
                        <template x-for="item in (auditDrawer.liveAeo||[])" :key="'la'+item.key">
                            <div class="bk-audit-line" :class="'st-'+item.state"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" x-html="lineIcon(item.state)"></span>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">GEO</div>
                        <template x-for="item in (auditDrawer.liveGeo||[])" :key="'lg'+item.key">
                            <div class="bk-audit-line" :class="'st-'+item.state"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" x-html="lineIcon(item.state)"></span>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Done: checklist or guide -->
                <div class="bk-audit-cols" x-show="auditDrawer.phase==='done' && auditDrawer.view!=='guide'">
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">SEO <small x-text="'('+((auditDrawer.summary&&auditDrawer.summary.seo_issues)||0)+' مورد)'"></small></div>
                        <template x-for="item in ((auditDrawer.result&&auditDrawer.result.seo)||[])" :key="'s'+item.key">
                            <div class="bk-audit-line" :class="item.pass ? 'st-pass' : 'st-fail'"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" x-html="lineIcon(item.pass?'pass':'fail')"></span>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">AEO <small x-text="'('+((auditDrawer.summary&&auditDrawer.summary.aeo_issues)||0)+' مورد)'"></small></div>
                        <template x-for="item in ((auditDrawer.result&&auditDrawer.result.aeo)||[])" :key="'a'+item.key">
                            <div class="bk-audit-line" :class="item.pass ? 'st-pass' : 'st-fail'"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" x-html="lineIcon(item.pass?'pass':'fail')"></span>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <div class="bk-audit-col">
                        <div class="bk-audit-col__h">GEO <small x-text="'('+((auditDrawer.summary&&auditDrawer.summary.geo_issues)||0)+' مورد)'"></small></div>
                        <template x-for="item in ((auditDrawer.result&&auditDrawer.result.geo)||[])" :key="'g'+item.key">
                            <div class="bk-audit-line" :class="item.pass ? 'st-pass' : 'st-fail'"
                                 @mouseenter="showTip($event, item)" @mouseleave="hideTip()">
                                <span class="bk-audit-line__ico" x-html="lineIcon(item.pass?'pass':'fail')"></span>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bk-audit-guide" x-show="auditDrawer.phase==='done' && auditDrawer.view==='guide'">
                    <div class="bk-audit-guide__head">راهنمای رفع موارد ناموفق</div>
                    <div class="bk-audit-guide__grid" x-show="failedAuditItems().length">
                        <template x-for="item in failedAuditItems()" :key="'f'+item.key">
                            <div class="bk-guide-card">
                                <div class="bk-guide-card__top">
                                    <span class="bk-tip__badge is-fail">نیاز به اصلاح</span>
                                    <em x-text="(item.group||'seo').toUpperCase()"></em>
                                </div>
                                <strong class="bk-guide-card__title" x-text="item.label"></strong>
                                <p class="bk-guide-card__body" x-text="expandGuideHint(item)"></p>
                            </div>
                        </template>
                    </div>
                    <p class="bk-audit-guide__empty" x-show="!failedAuditItems().length">همه موارد پاس شده‌اند — نیازی به اصلاح نیست.</p>
                </div>

                <!-- Floating tip -->
                <div class="bk-tip" x-show="tip.show" x-cloak :style="'top:'+tip.y+'px;left:'+tip.x+'px'" @mouseenter="tip.sticky=true" @mouseleave="hideTip()">
                    <div class="bk-tip__badge" :class="'is-'+(tip.pass?'pass':'fail')" x-text="tip.pass ? 'پاس' : 'نیاز به اصلاح'"></div>
                    <strong class="bk-tip__title" x-text="tip.title"></strong>
                    <p class="bk-tip__body" x-text="tip.body"></p>
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
                        <tr x-show="loading"><td colspan="6" style="text-align:center;padding:24px;color:#8a8886">در حال بارگذاری…</td></tr>
                        <tr x-show="!loading && !articles.length"><td colspan="6" style="text-align:center;padding:24px;color:#8a8886">مقاله‌ای یافت نشد.</td></tr>
                        <template x-for="row in articles" :key="row.id">
                            <tr>
                                <td>
                                    <strong class="bk-art-title" x-text="row.title"></strong>
                                    <div class="bk-art-date">
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="12" height="12"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                        <span x-text="row.date || row.modified"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="bk-mini-ring" title="SEO">
                                        <svg viewBox="0 0 36 36">
                                            <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                            <circle class="bk-ring-val is-seo" cx="18" cy="18" r="14" stroke-dasharray="87.96"
                                                :stroke-dashoffset="ringOffset(row.seo_audit)"></circle>
                                        </svg>
                                        <span x-text="ringLabel(row.seo_audit)"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="bk-mini-ring" title="AEO">
                                        <svg viewBox="0 0 36 36">
                                            <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                            <circle class="bk-ring-val is-aeo" cx="18" cy="18" r="14" stroke-dasharray="87.96"
                                                :stroke-dashoffset="ringOffset(row.aeo_audit)"></circle>
                                        </svg>
                                        <span x-text="ringLabel(row.aeo_audit)"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="bk-mini-ring" title="GEO">
                                        <svg viewBox="0 0 36 36">
                                            <circle class="bk-ring-track" cx="18" cy="18" r="14"></circle>
                                            <circle class="bk-ring-val is-geo" cx="18" cy="18" r="14" stroke-dasharray="87.96"
                                                :stroke-dashoffset="ringOffset(row.geo_audit)"></circle>
                                        </svg>
                                        <span x-text="ringLabel(row.geo_audit)"></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="bk-status-badge" :class="'is-' + (row.status || 'draft')">
                                        <span class="bk-status-badge__ico" x-html="statusIcon(row.status)"></span>
                                        <span x-text="row.status_label || row.status"></span>
                                    </span>
                                </td>
                                <td>
                                    <div class="bk-actions">
                                        <button type="button" class="bk-btn bk-btn-icon" title="گزارش SEO / AEO / GEO" @click="runRowAudit(row)" :disabled="auditBusyId === row.id">
                                            <svg x-show="auditBusyId !== row.id" class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19V9M4 19h16"/><path d="M8 15v-4"/><path d="M12 15V7"/><path d="M16 15v-6"/></svg>
                                            <span x-show="auditBusyId === row.id" class="bk-mini-spin"></span>
                                        </button>
                                        <a class="bk-btn bk-btn-icon bk-btn-primary" :href="wpEditUrl(row.id)" title="ویرایش">
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
        <div class="bk-art-card bk-import-ai" x-show="panel==='missing-seo'" x-cloak style="padding:0;overflow:hidden;">
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
                        <template x-for="p in (ms.textProviders || [])" :key="'mt'+p.id">
                            <button type="button" class="bk-ai-engine-card"
                                :class="{ 'is-active': ms.textProvider === p.id, 'is-disabled': !p.ready }"
                                @click="ms.textProvider = p.id">
                                <span class="bk-ai-engine-name" x-text="p.name"></span>
                                <span class="bk-ai-engine-status" x-text="ms.textProvider === p.id ? 'فعال' : (p.ready ? 'انتخاب' : 'بدون کلید')"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="bk-import-toolbar">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <strong style="font-size:13px;">مقالات بدون سئوی Bankai</strong>
                        <span style="font-size:12px;color:#64748b;" x-text="(ms.total||0) + ' مورد'"></span>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button type="button" class="bk-btn-ghost" @click="msToggleAll()" :disabled="ms.aiRunning" x-text="ms.allSelected ? 'لغو انتخاب همه' : 'انتخاب همه'"></button>
                        <button type="button" class="bk-btn-ghost" @click="loadMissingSeoList()" :disabled="ms.aiRunning">تازه‌سازی</button>
                        <button type="button" class="bk-btn-primary" @click="runMissingSeoBatch()"
                            :disabled="ms.aiRunning || !msSelectedCount() || !ms.textProvider"
                            x-text="ms.aiRunning ? ('در حال اجرا… '+(ms.aiProgress||0)+'%') : ('اجرای یک‌پارچه ('+msSelectedCount()+')')"></button>
                        <button type="button" class="bk-btn-ghost" x-show="ms.aiRunning" @click="ms.aiAbort=true">توقف</button>
                        <button type="button" class="bk-btn-success" x-show="!ms.aiRunning && msReadyCount()>0"
                            @click="msApproveAll()" x-text="'اعمال تغییرات تاییدشده ('+msReadyCount()+')'"></button>
                    </div>
                </div>

                <div style="padding:14px 16px;">
                    <template x-if="ms.loaded && !(ms.items||[]).length">
                        <div style="padding:28px 16px;text-align:center;border:1px dashed #86efac;border-radius:14px;background:#f0fdf4;">
                            <div style="font-size:28px;margin-bottom:8px;">✓</div>
                            <strong style="display:block;font-size:15px;color:#166534;margin-bottom:6px;">عالی — همه چیز سئو شده است</strong>
                            <p style="margin:0;font-size:13px;color:#15803d;line-height:1.6;">مقاله، برگه یا محصولی بدون سئوی Bankai باقی نمانده است.</p>
                        </div>
                    </template>
                    <template x-if="!ms.loaded">
                        <p style="margin:0;color:#64748b;font-size:13px;">در حال بررسی مقالات، برگه‌ها و محصولات…</p>
                    </template>
                    <template x-for="row in (ms.items||[])" :key="row.id">
                        <div class="bk-post-ai-card"
                             :class="{'is-running':row.aiPhase==='running','is-review':row.aiPhase==='review','is-done':row.aiPhase==='done','is-error':row.aiPhase==='error'}">
                            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                                <div style="display:flex;gap:10px;align-items:flex-start;">
                                    <input type="checkbox" :value="row.id" x-model="ms.selected" :disabled="ms.aiRunning && row.aiPhase==='running'">
                                    <div>
                                        <div style="font-weight:800;font-size:14px;" x-text="row.title"></div>
                                        <div style="font-size:12px;color:#64748B;margin-top:2px;">
                                            <span x-show="row.type_label" x-text="row.type_label" style="margin-inline-end:6px;font-weight:700;color:#6b4eff;"></span>
                                            <span x-text="row.status"></span>
                                            <span x-show="row.score"> · امتیاز: <strong x-text="row.score"></strong></span>
                                            <span x-show="row.aiPhase" style="margin-inline-start:6px;" x-text="msPhaseLabel(row.aiPhase)"></span>
                                        </div>
                                    </div>
                                </div>
                                <a :href="row.edit_url" target="_blank" rel="noopener" style="font-size:12px;">ویرایش مطلب ↗</a>
                            </div>
                            <div x-show="row.aiPhase==='running' || (row.aiProgress||0)>0" style="margin-top:12px;">
                                <div style="display:flex;justify-content:space-between;font-size:12px;color:#475569;">
                                    <span x-text="row.aiStepText||''"></span>
                                    <span x-text="(row.aiProgress||0)+'%'"></span>
                                </div>
                                <div class="bk-progress-track"><div class="bk-progress-fill" :style="'width:'+(row.aiProgress||0)+'%'"></div></div>
                            </div>
                            <ul class="bk-checklist-grid" x-show="row.checklist && row.checklist.length">
                                <template x-for="item in row.checklist" :key="item.id">
                                    <li>
                                        <span x-show="item.status==='pending'" style="opacity:.35;">○</span>
                                        <span x-show="item.status==='loading'">…</span>
                                        <span x-show="item.status==='success'" style="color:#16a34a;font-weight:800;">✓</span>
                                        <span x-show="item.status==='error'" style="color:#dc2626;font-weight:800;">✕</span>
                                        <span x-text="item.label"></span>
                                    </li>
                                </template>
                            </ul>
                            <div x-show="row.aiPhase==='review' || row.aiPhase==='done' || row.aiPhase==='error'" style="margin-top:14px;padding-top:12px;border-top:1px solid #E2E8F0;">
                                <h5 style="margin:0 0 8px;font-size:13px;font-weight:800;">خلاصه نتایج (Review & Approve)</h5>
                                <div class="bk-review-box" x-show="row.draft">
                                    <div><strong>عنوان سئو:</strong> <span x-text="(row.draft&&row.draft.seo_title)||'—'"></span></div>
                                    <div><strong>متا:</strong> <span x-text="(row.draft&&row.draft.description)||'—'"></span></div>
                                    <div><strong>کلیدواژه:</strong> <span x-text="(row.draft&&row.draft.focus_keyword)||'—'"></span></div>
                                    <div><strong>کلیدواژه‌ها:</strong> <span x-text="(row.draft&&row.draft.keywords&&row.draft.keywords.join('، '))||'—'"></span></div>
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;" x-show="row.aiPhase==='review'">
                                    <button type="button" class="bk-btn-success" @click="msApprove(row)" :disabled="row.saving" x-text="row.saving?'در حال ذخیره…':'اعمال تغییرات تاییدشده'"></button>
                                    <button type="button" class="bk-btn-ghost" @click="msRerun(row)" :disabled="ms.aiRunning">بازتولید</button>
                                    <button type="button" class="bk-btn-ghost" @click="msCancel(row)">لغو</button>
                                </div>
                                <div x-show="row.aiPhase==='done'" style="margin-top:8px;color:#166534;font-weight:700;font-size:13px;">✓ ذخیره شد — امتیاز: <span x-text="row.score"></span></div>
                                <div x-show="row.aiPhase==='error'" style="margin-top:8px;color:#991b1b;font-size:13px;" x-text="row.aiError||'خطا'"></div>
                            </div>
                        </div>
                    </template>
                    <div style="margin-top:10px;display:flex;gap:8px;align-items:center;" x-show="ms.pages>1">
                        <button type="button" class="bk-btn-ghost" :disabled="ms.page<=1||ms.aiRunning" @click="ms.page--; loadMissingSeoList()">قبلی</button>
                        <span x-text="ms.page+' / '+ms.pages"></span>
                        <button type="button" class="bk-btn-ghost" :disabled="ms.page>=ms.pages||ms.aiRunning" @click="ms.page++; loadMissingSeoList()">بعدی</button>
                    </div>
                </div>
            </div>
        </div>

                        <div class="bk-art-card bk-wiz" x-show="panel==='generate'" x-cloak>
            <!-- Global progress -->
            <div class="bk-wiz-topbar">
                <div>
                    <h3>ویزارد هوشمند تولید و زمان‌بندی مقاله با AI</h3>
                    <p>پیشنهاد عنوان بر اساس مقالات موجود، نگارش و بهینه‌سازی کامل سئو</p>
                </div>
                <div class="bk-wiz-global-progress" x-show="batchBusy || wizBusy || wiz.globalProgress > 0" x-cloak>
                    <div class="bk-ai-progress-meta">
                        <span x-text="wiz.globalLabel || 'در حال پردازش…'"></span>
                        <span x-text="(wiz.globalProgress||0) + '%'"></span>
                    </div>
                    <div class="bk-ai-progress-track"><div class="bk-ai-progress-fill" :style="'width:' + (wiz.globalProgress||0) + '%'"></div></div>
                </div>
            </div>

            <!-- AI engines as cards -->
            <div class="bk-wiz-engines">
                <div class="bk-wiz-engines-label">موتور AI فعال</div>
                <div class="bk-wiz-engine-grid">
                    <template x-for="p in wizProviders" :key="p.id">
                        <button type="button" class="bk-wiz-engine-card"
                                :class="{'is-active': wiz.provider===p.id, 'is-disabled': !p.has_key}"
                                :disabled="!p.has_key"
                                @click="wiz.provider=p.id">
                            <span class="bk-wiz-engine-dot" :style="p.has_key ? 'background:#1a7f37' : 'background:#cf222e'"></span>
                            <strong x-text="p.name || p.id"></strong>
                            <small x-text="p.has_key ? 'آماده' : 'بدون کلید'"></small>
                        </button>
                    </template>
                </div>
            </div>

            <!-- SETUP FORM (hidden while loading titles or after titles ready) -->
            <div x-show="!wizBusy && wiz.phase==='setup'" x-cloak>
                <div class="bk-field">
                    <label>راهنمای انتخاب / تولید عنوان‌ها</label>
                    <textarea x-model="wiz.topic" rows="3" placeholder="موضوع، زاویه محتوا یا کلمه کلیدی… عناوین و کلیدواژه‌های مقالات قبلی سایت به‌صورت خودکار به AI ارسال می‌شود."></textarea>
                </div>
                <div class="bk-wiz-row2">
                    <div class="bk-field">
                        <label>تعداد پیشنهاد</label>
                        <select x-model.number="wiz.count">
                            <option :value="5">۵</option>
                            <option :value="10">۱۰</option>
                            <option :value="15">۱۵</option>
                            <option :value="20">۲۰</option>
                        </select>
                    </div>
                    <div class="bk-field">
                        <label>طول پیش‌فرض مقاله (کلمه)</label>
                        <select x-model="wiz.defaultLength">
                            <option value="1500">۱٬۵۰۰</option>
                            <option value="3000">۳٬۰۰۰</option>
                            <option value="5000">۵٬۰۰۰</option>
                            <option value="7000">۷٬۰۰۰</option>
                        </select>
                    </div>
                </div>
                <div class="bk-field">
                    <label>دستورالعمل سراسری (اختیاری)</label>
                    <textarea x-model="wiz.defaultNotes" rows="3" placeholder="لحن، مخاطب، نکات اجباری…"></textarea>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
                    <button type="button" class="bk-btn bk-btn-primary" @click="suggestTitles()" :disabled="!wiz.topic.trim()">
                        دریافت پیشنهاد عنوان از AI
                    </button>
                    <button type="button" class="bk-btn" @click="addManualTitle()">افزودن عنوان دستی</button>
                </div>
            </div>

            <!-- LOADING titles -->
            <div class="bk-wiz-loader" x-show="wizBusy" x-cloak>
                <div class="bk-wiz-spinner"></div>
                <p>در حال تولید عناوین بر اساس مقالات و کلیدواژه‌های سایت…</p>
            </div>

            <!-- TITLE CARDS -->
            <div x-show="!wizBusy && wiz.phase==='titles'" x-cloak>
                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap">
                    <strong style="font-size:13px">عناوین پیشنهادی — تأیید و تنظیم کنید</strong>
                    <button type="button" class="bk-btn" @click="wiz.phase='setup'; wiz.selected=[]; wiz.suggestions=[]">بازگشت به فرم</button>
                </div>
                <template x-for="(item, idx) in wiz.selected" :key="'card'+idx">
                    <div class="bk-wiz-title-card" :class="{'is-checked': item.approved}">
                        <label class="bk-wiz-check">
                            <input type="checkbox" x-model="item.approved">
                            <span>تأیید برای تولید</span>
                        </label>
                        <input type="text" class="bk-wiz-title-input" x-model="item.title">
                        <div class="bk-wiz-card-grid">
                            <div class="bk-field">
                                <label>طول مقاله</label>
                                <select x-model="item.length">
                                    <option value="1500">۱٬۵۰۰</option>
                                    <option value="3000">۳٬۰۰۰</option>
                                    <option value="5000">۵٬۰۰۰</option>
                                    <option value="7000">۷٬۰۰۰</option>
                                </select>
                            </div>
                            <div class="bk-field">
                                <label>وضعیت انتشار</label>
                                <select x-model="item.pubStatus">
                                    <option value="draft">پیش‌نویس</option>
                                    <option value="publish">انتشار فوری</option>
                                    <option value="future">زمان‌بندی</option>
                                </select>
                            </div>
                            <div class="bk-field" x-show="item.pubStatus==='future'" x-cloak style="grid-column:1/-1">
                                <label>تاریخ انتشار (جلالی)</label>
                                <button type="button" class="bk-sched-toggle" @click="openWizItemSchedule(idx)"
                                        :aria-expanded="wizSchedIdx===idx && wizItemSchedOpen ? 'true' : 'false'">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    <span x-text="wizItemScheduleLabel(item)"></span>
                                </button>
                            </div>
                        </div>
                        <div class="bk-field" style="margin-top:6px">
                            <label>دستورالعمل این عنوان</label>
                            <input type="text" x-model="item.notes" :placeholder="wiz.defaultNotes || 'ارث از سراسری'">
                        </div>
                    </div>
                </template>
                <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
                    <button type="button" class="bk-btn bk-btn-primary" @click="startProductionFromCards()"
                            :disabled="batchBusy || !wiz.selected.filter(s=>s.approved).length">
                        تهیه و تولید مقاله
                    </button>
                    <span style="font-size:12px;color:#656d76;align-self:center"
                          x-text="wiz.selected.filter(s=>s.approved).length + ' عنوان تأیید شده'"></span>
                </div>
            </div>

            <!-- PRODUCTION QUEUE -->
            <div x-show="wiz.phase==='produce'" x-cloak style="margin-top:8px">
                <div class="bk-wiz-paused" x-show="wiz.paused" x-cloak>
                    <strong>توقف به‌خاطر خطا</strong>
                    <p x-text="wiz.pauseMessage || 'یک مقاله با خطا متوقف شد. اتصال AI را بررسی و ادامه دهید.'"></p>
                    <button type="button" class="bk-btn bk-btn-primary" @click="continueBatchAfterCheck()" :disabled="batchBusy || wiz.checkingConn">
                        <span x-text="wiz.checkingConn ? 'بررسی اتصال…' : 'ادامه فرایند'"></span>
                    </button>
                    <button type="button" class="bk-btn" @click="cancelWizardProcess()" :disabled="batchBusy">
                        لغو و شروع مجدد
                    </button>
                </div>
                <template x-for="(job, ji) in wiz.queue" :key="'job'+ji">
                    <div class="bk-wiz-job" :class="'is-' + job.status">
                        <div class="bk-wiz-job-head">
                            <strong x-text="job.title"></strong>
                            <span class="bk-wiz-badge" x-text="jobStatusLabel(job.status)"></span>
                        </div>
                        <div class="bk-ai-progress">
                            <div class="bk-ai-progress-meta">
                                <span x-text="job.stepLabel || 'آماده'"></span>
                                <span x-text="(job.progress||0) + '%'"></span>
                            </div>
                            <div class="bk-ai-progress-track"><div class="bk-ai-progress-fill" :style="'width:' + (job.progress||0) + '%'"></div></div>
                        </div>
                        <ul class="bk-wiz-checklist">
                            <li :class="{'is-done': job.progress>=15}">تحلیل محتوا و ساختار مقاله</li>
                            <li :class="{'is-done': job.progress>=35}">تولید خودکار عنوان سئو و متا دیسکریپشن</li>
                            <li :class="{'is-done': job.progress>=50}">استخراج کلمات کلیدی + ادغام ثابت‌ها</li>
                            <li :class="{'is-done': job.progress>=65}">لینک‌سازی هوشمند داخلی با مقالات مرتبط</li>
                            <li :class="{'is-done': job.progress>=80}">سئوی تصاویر و تولید Alt Text خودکار</li>
                            <li :class="{'is-done': job.progress>=100}">دسته و برچسب خودکار (موجود یا ایجاد جدید)</li>
                        </ul>
                        <div style="font-size:11px;color:#656d76;margin-top:4px" x-show="job.message" x-text="job.message"></div>
                        <div style="margin-top:6px" x-show="job.post_id">
                            <a class="bk-btn bk-btn-primary" :href="wpEditUrl(job.post_id)">ویرایش مقاله</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

<div class="bk-art-card" x-show="panel==='schedule'" x-cloak>
            <h3 style="margin:0 0 12px;font-size:16px;font-weight:800">صف زمان‌بندی</h3>
            <template x-if="!queue.length"><p style="color:#8a8886;font-size:13px">موردی در صف نیست.</p></template>
            <template x-for="(item, idx) in queue" :key="'q'+idx">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f1f5f9;gap:10px;flex-wrap:wrap">
                    <div>
                        <strong x-text="item.title || item.topic"></strong>
                        <div style="font-size:11px;color:#8a8886" x-text="item.schedule_at || '—'"></div>
                    </div>
                    <div class="bk-actions">
                        <a class="bk-btn bk-btn-primary" x-show="item.post_id" :href="wpEditUrl(item.post_id)">ویرایش مقاله</a>
                        <button type="button" class="bk-btn" @click="removeQueueItem(idx)">حذف از صف</button>
                    </div>
                </div>
            </template>
        </div><!-- /schedule -->
    </div><!-- /bk-art-main -->
</div><!-- /tab-articles -->

<script>
window.bankaiArticlesQueue = <?php echo $queue_json ?: '[]'; ?>;
</script>
