<?php
if (!defined('ABSPATH')) {
    exit;
}

$post_id = isset($post_id) ? absint($post_id) : 0;
if (!$post_id && isset($_GET['post'])) {
    $post_id = absint($_GET['post']);
}
if (!$post_id) {
    $post_id = get_the_ID();
}
$post = $post_id ? get_post($post_id) : null;
// Allow shell render for new unsaved posts (post_id = 0).
if ($post_id && !$post) {
    return;
}
?>
<div
    id="bankai-seo-sidebar"
    class="bankai-seo-root"
    x-data="bankaiSeoSidebar(<?php echo (int) $post_id; ?>)"
    x-init="init()"
    dir="rtl"
>

    <!-- HEADER (Scores Overview) -->
    <header class="bk-header">
        <div class="bk-header-main">
            <div class="bk-score-ring" :style="'--score-color:' + scoreColor">
                <svg viewBox="0 0 36 36">
                    <circle class="bk-track" cx="18" cy="18" r="14" />
                    <circle class="bk-progress" cx="18" cy="18" r="14"
                        :stroke-dasharray="scoreCircumference"
                        :stroke-dashoffset="scoreOffset"
                        :style="{ stroke: scoreColor }" />
                </svg>
                <span class="bk-score-num" x-text="analysis.score">0</span>
            </div>
            <div class="bk-header-text">
                <strong>سئو بنکای</strong>
            </div>
            <button type="button" class="bk-icon-btn" @click="analyze(true)" :disabled="loading" title="تازه‌سازی و آنالیز مجدد">
                <svg class="solar-icon" :class="{ 'bk-spin': loading }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/></svg>
            </button>
        </div>

        <!-- SUB-SCORES (SEO, AEO, GEO Circular Progress Bars Side-by-Side) -->
        <div class="bk-subscores-row">
            <div class="bk-subscore-card">
                <div class="bk-subscore-ring">
                    <svg viewBox="0 0 32 32">
                        <circle class="bk-track" cx="16" cy="16" r="12" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                        <circle class="bk-progress" cx="16" cy="16" r="12" fill="none"
                            stroke-width="3" stroke-linecap="round"
                            :style="{ stroke: subScoreColor(subScores.seo), strokeDasharray: (2 * Math.PI * 12), strokeDashoffset: subScoreOffset(subScores.seo) }"/>
                    </svg>
                    <span class="bk-subscore-num" :style="{ color: subScoreColor(subScores.seo) }" x-text="(subScores.seo || 0) + '%'">0%</span>
                </div>
                <span class="bk-subscore-label" :style="{ color: subScoreColor(subScores.seo) }">SEO</span>
            </div>

            <div class="bk-subscore-card">
                <div class="bk-subscore-ring">
                    <svg viewBox="0 0 32 32">
                        <circle class="bk-track" cx="16" cy="16" r="12" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                        <circle class="bk-progress" cx="16" cy="16" r="12" fill="none"
                            stroke-width="3" stroke-linecap="round"
                            :style="{ stroke: subScoreColor(subScores.aeo), strokeDasharray: (2 * Math.PI * 12), strokeDashoffset: subScoreOffset(subScores.aeo) }"/>
                    </svg>
                    <span class="bk-subscore-num" :style="{ color: subScoreColor(subScores.aeo) }" x-text="(subScores.aeo || 0) + '%'">0%</span>
                </div>
                <span class="bk-subscore-label" :style="{ color: subScoreColor(subScores.aeo) }">AEO</span>
            </div>

            <div class="bk-subscore-card">
                <div class="bk-subscore-ring">
                    <svg viewBox="0 0 32 32">
                        <circle class="bk-track" cx="16" cy="16" r="12" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                        <circle class="bk-progress" cx="16" cy="16" r="12" fill="none"
                            stroke-width="3" stroke-linecap="round"
                            :style="{ stroke: subScoreColor(subScores.geo), strokeDasharray: (2 * Math.PI * 12), strokeDashoffset: subScoreOffset(subScores.geo) }"/>
                    </svg>
                    <span class="bk-subscore-num" :style="{ color: subScoreColor(subScores.geo) }" x-text="(subScores.geo || 0) + '%'">0%</span>
                </div>
                <span class="bk-subscore-label" :style="{ color: subScoreColor(subScores.geo) }">GEO</span>
            </div>
        </div>

        <!-- TABS — capsule style -->
        <nav class="bk-seo-btn-nav is-editor-sidebar" role="tablist">
            <div class="bk-seo-btn-row">
                <button type="button" class="bk-seo-nav-btn" :class="{ 'is-active': activeTab === 'seo' || activeTab === 'checklist' }" @click="activeTab = 'seo'" title="سئو">سئو</button>
                <button type="button" class="bk-seo-nav-btn" :class="{ 'is-active': activeTab === 'aeo_geo' || activeTab === 'aeogeo' }" @click="activeTab = 'aeo_geo'" title="AEO & GEO">AEO &amp; GEO</button>
                <button type="button" class="bk-seo-nav-btn" :class="{ 'is-active': activeTab === 'links' }" @click="activeTab = 'links'; loadLinks()" title="لینک‌ها">لینک‌ها</button>
                <button type="button" class="bk-seo-nav-btn" :class="{ 'is-active': activeTab === 'media' }" @click="activeTab = 'media'; scanPostImages()" title="تصاویر">تصاویر</button>
            </div>
            <div class="bk-seo-btn-row">
                <button type="button" class="bk-seo-nav-btn" :class="{ 'is-active': activeTab === 'schema' }" @click="activeTab = 'schema'" title="اسکیما">اسکیما</button>
                <button type="button" class="bk-seo-nav-btn" :class="{ 'is-active': activeTab === 'social' }" @click="activeTab = 'social'" title="سوشال">سوشال</button>
                <button type="button" class="bk-seo-nav-btn" :class="{ 'is-active': activeTab === 'ai' }" @click="activeTab = 'ai'" title="دستیار AI">دستیار AI</button>
            </div>
        </nav>
    </header>

    <!-- BODY CONTENT -->
    <div class="bk-body">

        <!-- ========== 1. SEO TAB ========== -->
        <section x-show="activeTab === 'seo' || activeTab === 'checklist'" class="bk-panel" x-cloak>

            <!-- SERP PREVIEW -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <svg class="solar-icon bk-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <span>پیش‌نمایش SERP</span>
                    <div class="bk-seg">
                        <button type="button" :class="{ 'is-on': !serpMobile }" @click="serpMobile = false" title="دسکتاپ">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
                        </button>
                        <button type="button" :class="{ 'is-on': serpMobile }" @click="serpMobile = true" title="موبایل">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                        </button>
                    </div>
                </div>
                <div class="bk-serp" :class="{ 'is-mobile': serpMobile }">
                    <div class="bk-serp-url" dir="ltr">
                        <span class="bk-fav">B</span>
                        <span x-text="seo.permalink || '…'"></span>
                    </div>
                    <div class="bk-serp-title" x-text="seo.seo_title || seo.title"></div>
                    <div class="bk-serp-byline" x-show="!seo.robots.hide_date">
                        <span x-text="serpAuthor || 'نویسنده'"></span>
                        <span x-show="serpDate"> · </span>
                        <span x-text="serpDate"></span>
                    </div>
                    <div class="bk-serp-desc" x-text="seo.description"></div>
                </div>
                <div class="bk-serp-meta">
                    <span :class="titlePxOk ? 'ok' : 'warn'">
                        <i></i>
                        طول پیکسل عنوان: <span x-text="titlePx"></span>px
                    </span>
                    <a class="bk-link-btn" x-show="seo.permalink" :href="'https://search.google.com/search-console?resource_id=' + encodeURIComponent(seo.permalink)" target="_blank" rel="noopener" title="Search Console">
                        GSC ↗
                    </a>
                </div>
            </div>

            <!-- KEYWORDS SECTION -->
            <div class="bk-card bk-kw-card">
                <div class="bk-card-head">
                    <span>کلیدواژه‌ها</span>
                    <button type="button" class="bk-link-btn" @click="openAiModal('keywords')">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        پیشنهاد AI
                    </button>
                </div>

                <div class="bk-kw-box" @click="$refs.kwInput.focus()">
                    <template x-for="(kw, i) in keywordList" :key="i">
                        <span class="bk-chip" :class="{ 'is-primary': i === 0, 'is-fixed': isFixedKeyword(kw) }">
                            <span x-show="isFixedKeyword(kw)" title="کلمه کلیدی ثابت سایت">📌 </span>
                            <span x-text="kw" @dblclick="editKeyword(i)"></span>
                            <em x-show="i === 0" class="bk-chip-badge">اصلی</em>
                            <button type="button" class="bk-chip-x" @click.stop="removeKeyword(i)" title="حذف">
                                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </span>
                    </template>
                    <input
                        x-ref="kwInput"
                        type="text"
                        class="bk-kw-input"
                        x-model="keywordInput"
                        @keydown.enter.prevent="addKeywordFromInput()"
                        @keydown.backspace="if(!keywordInput && keywordList.length) removeKeyword(keywordList.length - 1)"
                        placeholder="افزودن کلیدواژه + اینتر…"
                    >
                </div>

                <div class="bk-kw-stats" x-show="seo.focus_keyword">
                    <span>چگالی کلیدواژه اصلی: <strong x-text="(analysis.stats?.density ?? 0) + '%'"></strong></span>
                    <span class="ok" x-show="analysis.stats?.density >= 0.5 && analysis.stats?.density <= 2.5">مناسب (۰٫۵٪ تا ۲٫۵٪)</span>
                    <span class="warn" x-show="analysis.stats?.density < 0.5 || analysis.stats?.density > 2.5">نیازمند تنظیم</span>
                </div>
            </div>

            <!-- SEO TITLE -->
            <div class="bk-card bk-field-card">
                <div class="bk-field-head">
                    <label>عنوان سئو (Title Tag)</label>
                    <div class="bk-field-actions">
                        <span class="bk-counter" :class="seoTitleClass" x-text="(seo.seo_title || '').length + '/60'"></span>
                        <button type="button" class="bk-link-btn" @click="openAiModal('title')">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/></svg>
                            تولید AI
                        </button>
                    </div>
                </div>
                <input type="text" x-model="seo.seo_title" @input.debounce.400ms="onFieldChange()" maxlength="70" placeholder="عنوان سئو مقاله...">
                <div class="bk-bar"><div :style="'width:' + titleBarPct + '%;background:' + titleBarColor"></div></div>
            </div>

            <!-- META DESCRIPTION -->
            <div class="bk-card bk-field-card">
                <div class="bk-field-head">
                    <label>توضیحات متا (Meta Description)</label>
                    <div class="bk-field-actions">
                        <span class="bk-counter" :class="descClass" x-text="(seo.description || '').length + '/160'"></span>
                        <button type="button" class="bk-link-btn" @click="openAiModal('description')">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/></svg>
                            تولید AI
                        </button>
                    </div>
                </div>
                <textarea x-model="seo.description" @input.debounce.400ms="onFieldChange()" maxlength="170" rows="3" placeholder="توضیحات جذاب متا شامل کلمه کلیدی اصلی..."></textarea>
                <div class="bk-bar"><div :style="'width:' + descBarPct + '%;background:' + descBarColor"></div></div>
            </div>

            <!-- SEO CHECKLIST (minimal accordion — SEO groups only) -->
            <div class="bk-seo-checklist-mini bk-checklist-serp">
                <template x-for="gkey in seoGroupKeys()" :key="gkey">
                    <div class="bk-acc" x-show="(analysis.groups && analysis.groups[gkey] && analysis.groups[gkey].items && analysis.groups[gkey].items.length)">
                        <button type="button" class="bk-acc-head" @click="toggleGroup(gkey)">
                            <svg class="bk-chevron solar-icon" :style="openGroups[gkey] ? 'transform:rotate(90deg)' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 18l6-6-6-6"/></svg>
                            <span x-text="(analysis.groups[gkey] && analysis.groups[gkey].label) || gkey"></span>
                            <span class="bk-acc-count" x-text="groupPassedCount(analysis.groups[gkey]) + '/' + ((analysis.groups[gkey] && analysis.groups[gkey].items) ? analysis.groups[gkey].items.length : 0)"></span>
                        </button>
                        <div class="bk-acc-body" x-show="openGroups[gkey]">
                            <template x-for="check in (analysis.groups[gkey] && analysis.groups[gkey].items) || []" :key="check.key || check.id">
                                <div class="bk-check-mini" :class="checkStatusClass(check)">
                                    <template x-if="check.status === 'pass' || check.passed">
                                        <span class="bk-check-icon-mini is-pass" title="تایید">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M20 6L9 17l-5-5"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="check.status === 'not_applicable'">
                                        <span class="bk-check-icon-mini is-na" title="نامرتبط">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="9"/><path d="M5 12h14"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="check.status === 'unknown'">
                                        <span class="bk-check-icon-mini is-unknown" title="نامشخص">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="9"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="!check.passed && check.status !== 'pass' && check.status !== 'not_applicable' && check.status !== 'unknown'">
                                        <span class="bk-check-icon-mini is-fail" title="هشدار">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                                        </span>
                                    </template>
                                    <div class="bk-check-text-wrap">
                                        <span class="bk-check-text" x-text="check.label"></span>
                                        <small class="bk-check-desc" x-show="check.message" x-text="check.message"></small>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ROBOTS & INDEXING -->
            <div class="bk-card">
                <div class="bk-card-head"><span>تنظیمات ایندکس و Robots</span></div>
                <label class="bk-switch-row">
                    <span>اجازه ایندکس (Index)</span>
                    <input type="checkbox" x-model="seo.robots.index" @change="onFieldChange()">
                </label>
                <label class="bk-switch-row">
                    <span>دنبال کردن لینک‌ها (Follow)</span>
                    <input type="checkbox" x-model="seo.robots.follow" @change="onFieldChange()">
                </label>
                <label class="bk-switch-row">
                    <span>عدم آرشیو (noarchive)</span>
                    <input type="checkbox" x-model="seo.robots.noarchive" @change="onFieldChange()">
                </label>
                <label class="bk-switch-row">
                    <span>عدم نمایش اسنیپت (nosnippet)</span>
                    <input type="checkbox" x-model="seo.robots.nosnippet" @change="onFieldChange()">
                </label>
                <label class="bk-switch-row">
                    <span>مخفی کردن تاریخ در SERP</span>
                    <input type="checkbox" x-model="seo.robots.hide_date" @change="onFieldChange()">
                </label>
                <div class="bk-field" style="margin-top:8px">
                    <label>آدرس کانونی (Canonical URL)</label>
                    <div class="bk-input-with-action">
                        <input type="url" dir="ltr" x-model="seo.canonical" @input.debounce.500ms="onFieldChange()" placeholder="https://…">
                        <a class="bk-icon-action" :href="seo.canonical || '#'" target="_blank" rel="noopener" title="مشاهده لینک" :class="{ 'is-disabled': !seo.canonical }" @click="if(!seo.canonical)$event.preventDefault()">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h7v7M10 14L21 3M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
                        </a>
                    </div>
                </div>
                <div class="bk-field" style="margin-top:10px">
                    <label>ریدایرکت ۳۰۱ (اختیاری)</label>
                    <input type="url" dir="ltr" x-model="seo.redirect" @input.debounce.500ms="onFieldChange()" placeholder="https://example.com/new-url">
                    <p class="bk-hint" style="margin-top:6px">در صورت تنظیم، بازدیدکنندگان به آدرس جدید هدایت می‌شوند.</p>
                </div>
            </div>

        </section>


        


        <!-- ========== 3. AEO & GEO TAB ========== -->
        <section x-show="activeTab === 'aeo_geo' || activeTab === 'aeogeo'" class="bk-panel" x-cloak>

            <div class="bk-card bk-aeo-card">
                <div class="bk-card-head">
                    <span class="bk-pill-tag bk-tag-aeo">AEO</span>
                    <span>آمادگی پاسخ‌محور (Answer Engine)</span>
                    <span class="bk-badge" :style="'background:' + subScoreColor(subScores.aeo) + '22;color:' + subScoreColor(subScores.aeo)">
                        <span x-text="Math.round(subScores.aeo || 0)"></span>%
                    </span>
                </div>
                <p class="bk-hint">AEO یعنی محتوا برای پاسخ مستقیم به سوالات کاربر در Featured Snippet، People Also Ask و موتورهای پاسخ‌گو آماده باشد.</p>
                <div class="bk-aeo-tools">
                    <button type="button" class="bk-aeo-tool bk-aeo-tool-visual" @click="fixAeoWithAi()" :disabled="aiBusy || aiLoading">
                        <div class="bk-ai-tool-icon bk-icon-purple">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        </div>
                        <div class="bk-aeo-tool-body">
                            <strong>بهبود AEO با هوش مصنوعی</strong>
                            <small>تولید FAQ ساختاریافته + اسکیما — انتخاب AI، پیشرفت و تایید نهایی</small>
                            <span class="bk-tool-chip">FAQ · Schema · Review</span>
                        </div>
                        <span class="bk-tool-arrow">←</span>
                    </button>
                    <button type="button" class="bk-aeo-tool" @click="activeTab='schema'; schemaType='FAQPage'">
                        <div class="bk-ai-tool-icon bk-icon-purple"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></div>
                        <div>
                            <strong>اسکیما FAQ</strong>
                            <small>ثبت دستی سوالات متداول در تب اسکیما</small>
                        </div>
                    </button>
                </div>

                <!-- AEO checklist items -->
                <div class="bk-seo-checklist-mini bk-checklist-serp" style="margin-top:10px" x-show="analysis.groups && analysis.groups.aeo && analysis.groups.aeo.items && analysis.groups.aeo.items.length">
                    <div class="bk-acc">
                        <button type="button" class="bk-acc-head" @click="toggleGroup('aeo')">
                            <svg class="bk-chevron solar-icon" :style="openGroups.aeo ? 'transform:rotate(90deg)' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 18l6-6-6-6"/></svg>
                            <span x-text="(analysis.groups.aeo && analysis.groups.aeo.label) || 'چک‌لیست AEO'"></span>
                            <span class="bk-acc-count" x-text="groupPassedCount(analysis.groups.aeo) + '/' + ((analysis.groups.aeo && analysis.groups.aeo.items) ? analysis.groups.aeo.items.length : 0)"></span>
                        </button>
                        <div class="bk-acc-body" x-show="openGroups.aeo">
                            <template x-for="check in (analysis.groups.aeo && analysis.groups.aeo.items) || []" :key="check.key || check.id">
                                <div class="bk-check-mini" :class="checkStatusClass(check)">
                                    <template x-if="check.status === 'pass' || check.passed">
                                        <span class="bk-check-icon-mini is-pass"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M20 6L9 17l-5-5"/></svg></span>
                                    </template>
                                    <template x-if="!check.passed && check.status !== 'pass' && check.status !== 'not_applicable' && check.status !== 'unknown'">
                                        <span class="bk-check-icon-mini is-fail"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></span>
                                    </template>
                                    <template x-if="check.status === 'not_applicable' || check.status === 'unknown'">
                                        <span class="bk-check-icon-mini is-na"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="9"/><path d="M5 12h14"/></svg></span>
                                    </template>
                                    <div class="bk-check-text-wrap">
                                        <span class="bk-check-text" x-text="check.label"></span>
                                        <small class="bk-check-desc" x-show="check.message" x-text="check.message"></small>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bk-card bk-geo-card" style="margin-top:12px">
                <div class="bk-card-head">
                    <span class="bk-pill-tag bk-tag-geo">GEO</span>
                    <span>آمادگی موتورهای مولد (Generative)</span>
                    <span class="bk-badge" :style="'background:' + subScoreColor(subScores.geo) + '22;color:' + subScoreColor(subScores.geo)">
                        <span x-text="Math.round(subScores.geo || 0)"></span>%
                    </span>
                </div>
                <p class="bk-hint">GEO یعنی محتوا برای نقل‌قول توسط ChatGPT، Perplexity، Gemini و خلاصه‌سازهای AI شفاف، ساختاریافته و قابل استناد باشد.</p>
                <div class="bk-aeo-tools">
                    <button type="button" class="bk-aeo-tool bk-aeo-tool-visual" @click="fixGeoWithAi()" :disabled="aiBusy || aiLoading">
                        <div class="bk-ai-tool-icon bk-icon-amber">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <div class="bk-aeo-tool-body">
                            <strong>بهبود GEO با هوش مصنوعی</strong>
                            <small>خلاصه نکات کلیدی + متا برای نقل‌قول ChatGPT و موتورهای مولد</small>
                            <span class="bk-tool-chip">Takeaways · Meta · Review</span>
                        </div>
                        <span class="bk-tool-arrow">←</span>
                    </button>
                    <button type="button" class="bk-aeo-tool" @click="activeTab='ai'; openAiModal('meta')">
                        <div class="bk-ai-tool-icon bk-icon-blue"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 7h16M4 12h10M4 17h7"/></svg></div>
                        <div>
                            <strong>بهینه‌سازی عنوان و توضیحات</strong>
                            <small>متای واضح با پاسخ مستقیم به نیت جستجو</small>
                        </div>
                    </button>
                </div>

                <!-- GEO checklist items -->
                <div class="bk-seo-checklist-mini bk-checklist-serp" style="margin-top:10px" x-show="analysis.groups && analysis.groups.geo && analysis.groups.geo.items && analysis.groups.geo.items.length">
                    <div class="bk-acc">
                        <button type="button" class="bk-acc-head" @click="toggleGroup('geo')">
                            <svg class="bk-chevron solar-icon" :style="openGroups.geo ? 'transform:rotate(90deg)' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 18l6-6-6-6"/></svg>
                            <span x-text="(analysis.groups.geo && analysis.groups.geo.label) || 'چک‌لیست GEO'"></span>
                            <span class="bk-acc-count" x-text="groupPassedCount(analysis.groups.geo) + '/' + ((analysis.groups.geo && analysis.groups.geo.items) ? analysis.groups.geo.items.length : 0)"></span>
                        </button>
                        <div class="bk-acc-body" x-show="openGroups.geo">
                            <template x-for="check in (analysis.groups.geo && analysis.groups.geo.items) || []" :key="check.key || check.id">
                                <div class="bk-check-mini" :class="checkStatusClass(check)">
                                    <template x-if="check.status === 'pass' || check.passed">
                                        <span class="bk-check-icon-mini is-pass"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M20 6L9 17l-5-5"/></svg></span>
                                    </template>
                                    <template x-if="!check.passed && check.status !== 'pass' && check.status !== 'not_applicable' && check.status !== 'unknown'">
                                        <span class="bk-check-icon-mini is-fail"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></span>
                                    </template>
                                    <template x-if="check.status === 'not_applicable' || check.status === 'unknown'">
                                        <span class="bk-check-icon-mini is-na"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="9"/><path d="M5 12h14"/></svg></span>
                                    </template>
                                    <div class="bk-check-text-wrap">
                                        <span class="bk-check-text" x-text="check.label"></span>
                                        <small class="bk-check-desc" x-show="check.message" x-text="check.message"></small>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>




        <!-- ========== 4. LINKS TAB ========== -->
        <section x-show="activeTab === 'links'" class="bk-panel" x-cloak>

            <div class="bk-link-type-tabs bk-link-type-tabs-visual">
                <button type="button" class="bk-link-type-tab" :class="{ 'is-active': linkTypeTab === 'internal' }" @click="linkTypeTab = 'internal'">
                    <span class="bk-ltab-icon bk-icon-tone-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="18" height="18"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </span>
                    <span class="bk-ltab-text">
                        <strong>لینک داخلی</strong>
                        <small x-text="(linkData.counts?.internal ?? 0) + ' لینک'"></small>
                    </span>
                </button>
                <button type="button" class="bk-link-type-tab" :class="{ 'is-active': linkTypeTab === 'external' }" @click="linkTypeTab = 'external'">
                    <span class="bk-ltab-icon bk-icon-tone-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="18" height="18"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14L21 3"/></svg>
                    </span>
                    <span class="bk-ltab-text">
                        <strong>لینک خارجی</strong>
                        <small x-text="(linkData.counts?.external ?? 0) + ' لینک'"></small>
                    </span>
                </button>
            </div>

            <!-- INTERNAL ONLY -->
            <div x-show="linkTypeTab === 'internal'" x-cloak>
                <div class="bk-card bk-link-builder-card">
                    <div class="bk-card-head bk-link-tools-row">
                        <span>ابزار لینک داخلی</span>
                        <div class="bk-icon-actions">
                            <button type="button" class="bk-icon-action-btn bk-icon-tone-amber" title="پیشنهاد هوشمند AI" @click="startAiLinkMode()" :disabled="linkBusy" :class="{ 'is-active': linkSourceMode === 'ai' }">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                            </button>
                            <button type="button" class="bk-icon-action-btn bk-icon-tone-blue" title="تازه‌سازی لیست" @click="loadLinks()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="bk-search-unified">
                        <input type="text" x-model="linkAnchor" placeholder="جستجوی مقاله با کلیدواژه / انکر…" @keydown.enter.prevent="startManualLinkSearch()">
                        <button type="button" class="bk-search-unified-btn" @click="startManualLinkSearch()" :disabled="linkBusy" title="جستجو">
                            <svg x-show="!linkBusy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                            <svg x-show="linkBusy" class="bk-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg>
                        </button>
                    </div>

                    <div class="bk-match-section" x-show="linkSourceMode === 'ai' && autoLinkSuggestions.length" x-cloak>
                        <div class="bk-card-head">
                            <span>پیشنهادهای هوشمند AI</span>
                            <button type="button" class="bk-icon-action-btn" title="بستن" @click="linkSourceMode=null; autoLinkSuggestions=[]">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="bk-select-all-row">
                            <label class="bk-pick-check">
                                <input type="checkbox" :checked="allAiSelected()" @change="toggleAllAi($event.target.checked)">
                                <span class="bk-pick-box"></span>
                            </label>
                            <span>انتخاب همه</span>
                        </div>
                        <template x-for="(s, linkIdx) in autoLinkSuggestions" :key="s.target_post_id || s.id || linkIdx">
                            <div class="bk-sug-row" :class="{ 'is-picked': s.selected }">
                                <div class="bk-sug-top">
                                    <label class="bk-pick-check">
                                        <input type="checkbox" :checked="!!s.selected" @change="s.selected = $event.target.checked">
                                        <span class="bk-pick-box"></span>
                                    </label>
                                    <div class="bk-sug-main">
                                        <strong class="bk-sug-title" x-text="s.target_title || s.title" :title="s.target_title || s.title"></strong>
                                        <small class="bk-sug-url" dir="ltr" x-text="s.permalink || s.target_url"></small>
                                        <small class="bk-sug-anchor">انکر: <span x-text="s.suggested_anchor || s.keyword || pickRandomAnchor()"></span></small>
                                    </div>
                                </div>
                                <div class="bk-actions-row">
                                    <a class="bk-icon-action-btn bk-icon-tone-blue" :href="s.permalink || s.target_url" target="_blank" rel="noopener" title="مشاهده لینک"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="15" height="15"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                    <button type="button" class="bk-icon-action-btn is-danger bk-icon-tone-red" title="حذف این پیشنهاد" @click="autoLinkSuggestions.splice(linkIdx,1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg></button>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="bk-btn-primary bk-btn-sm" style="width:100%;margin-top:10px"
                            @click="confirmSelectedAiLinks()"
                            :disabled="!autoLinkSuggestions.some(s => s.selected)">
                            تایید و اعمال لینک‌های انتخاب‌شده
                        </button>
                    </div>

                    <div class="bk-match-section" x-show="linkSourceMode === 'manual' && postResults.length" x-cloak>
                        <div class="bk-card-head">
                            <span>مقالات مرتبط یافت‌شده</span>
                            <button type="button" class="bk-icon-action-btn" title="بستن" @click="linkSourceMode=null; postResults=[]">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="bk-select-all-row">
                            <label class="bk-pick-check">
                                <input type="checkbox" :checked="allManualSelected()" @change="toggleAllManual($event.target.checked)">
                                <span class="bk-pick-box"></span>
                            </label>
                            <span>انتخاب همه</span>
                        </div>
                        <template x-for="p in postResults" :key="p.id">
                            <div class="bk-sug-row" :class="{ 'is-picked': p.accepted }">
                                <div class="bk-sug-top">
                                    <label class="bk-pick-check">
                                        <input type="checkbox" :checked="!!p.accepted" @change="p.accepted = $event.target.checked; p.rejected = !p.accepted">
                                        <span class="bk-pick-box"></span>
                                    </label>
                                    <div class="bk-sug-main">
                                        <strong class="bk-sug-title" x-text="p.title" :title="p.title"></strong>
                                        <small class="bk-sug-url" dir="ltr" x-text="p.permalink"></small>
                                        <small class="bk-sug-anchor">انکر: <span x-text="linkAnchor || pickRandomAnchor() || p.title"></span></small>
                                    </div>
                                </div>
                                <div class="bk-actions-row">
                                    <a class="bk-icon-action-btn bk-icon-tone-blue" :href="p.permalink" target="_blank" rel="noopener" title="مشاهده لینک"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="15" height="15"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="bk-btn-primary bk-btn-sm" style="width:100%;margin-top:10px"
                            @click="confirmSelectedManualLinks()"
                            :disabled="!postResults.some(p => p.accepted)">
                            تایید و اعمال لینک‌های انتخاب‌شده
                        </button>
                    </div>
                </div>

                <div class="bk-card">
                    <div class="bk-card-head"><span>لینک‌های داخلی موجود</span></div>
                    <template x-if="!(linkData.internal?.length)">
                        <div class="bk-empty-state">هنوز لینک داخلی در متن ثبت نشده است.</div>
                    </template>
                    <template x-for="(l, idx) in (linkData.internal || [])" :key="'li'+idx+l.href">
                        <div class="bk-link-list-item">
                            <div class="bk-link-list-title" x-text="l.text || 'بدون انکر'" :title="l.text || l.href"></div>
                            <div class="bk-actions-row">
                                <button type="button" class="bk-icon-action-btn bk-icon-tone-blue" title="مشاهده در محتوا" @click="scrollToLinkInContent(l)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="15" height="15"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg></button>
                                <a class="bk-icon-action-btn bk-icon-tone-purple" :href="l.href" target="_blank" rel="noopener" title="باز کردن آدرس"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="15" height="15"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14L21 3"/></svg></a>
                                <button type="button" class="bk-icon-action-btn is-danger bk-icon-tone-red" title="غیرفعال‌سازی / حذف لینک از محتوا" @click="removeLinkFromContent(l)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" width="15" height="15"><path d="M18.84 12.25l1.72-1.71a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M5.16 11.75l-1.72 1.71a5 5 0 0 0 7.07 7.07l1.72-1.71"/><path d="M8 16l8-8"/><path d="M2 2l20 20"/></svg></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- EXTERNAL ONLY -->
            <div x-show="linkTypeTab === 'external'" x-cloak>
                <div class="bk-card">
                    <div class="bk-card-head"><span>افزودن لینک خارجی</span></div>
                    <div class="bk-field" style="margin-bottom:8px">
                        <label>انکر تکست</label>
                        <input type="text" x-model="extAnchor" placeholder="متن لینک…">
                    </div>
                    <div class="bk-field" style="margin-bottom:8px">
                        <label>آدرس URL</label>
                        <input type="url" dir="ltr" x-model="extUrl" placeholder="https://…">
                    </div>
                    <label class="bk-switch-row" style="margin-bottom:10px">
                        <span>Nofollow</span>
                        <input type="checkbox" x-model="extNofollow">
                    </label>
                    <button type="button" class="bk-btn-primary bk-btn-sm" style="width:100%" @click="addExternalLink()" :disabled="!extUrl">
                        افزودن لینک خارجی به متن
                    </button>
                </div>

                <div class="bk-card bk-cta-card">
                    <div class="bk-card-head"><span>لینک کال‌تو‌اکشن (CTA)</span></div>
                    <p class="bk-hint" style="margin-bottom:8px">متن و لینک را وارد کنید؛ بین هر ۲–۳ پاراگراف به‌صورت خودکار درج می‌شود.</p>
                    <div class="bk-field" style="margin-bottom:8px">
                        <label>متن CTA</label>
                        <input type="text" x-model="ctaText" placeholder="مثلاً: همین حالا مشاوره بگیرید">
                    </div>
                    <div class="bk-field" style="margin-bottom:8px">
                        <label>لینک CTA</label>
                        <input type="url" dir="ltr" x-model="ctaUrl" placeholder="https://…">
                    </div>
                    <div class="bk-cta-format-row">
                        <button type="button" class="bk-icon-action-btn" :class="{ 'is-active': ctaAlign === 'right' }" @click="ctaAlign='right'" title="راست‌چین">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M7 12h14M3 18h18"/></svg>
                        </button>
                        <button type="button" class="bk-icon-action-btn" :class="{ 'is-active': ctaAlign === 'center' }" @click="ctaAlign='center'" title="وسط‌چین">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M6 12h12M4 18h16"/></svg>
                        </button>
                        <button type="button" class="bk-icon-action-btn" :class="{ 'is-active': ctaAlign === 'left' }" @click="ctaAlign='left'" title="چپ‌چین">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M3 12h14M3 18h18"/></svg>
                        </button>
                        <button type="button" class="bk-icon-action-btn" :class="{ 'is-active': ctaBold }" @click="ctaBold=!ctaBold" title="بولد">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 4h8a4 4 0 0 1 0 8H6zM6 12h9a4 4 0 0 1 0 8H6z"/></svg>
                        </button>
                    </div>
                    <button type="button" class="bk-btn-primary bk-btn-sm" style="width:100%;margin-top:10px" @click="insertCtaBlocks()" :disabled="!ctaText || !ctaUrl">
                        درج خودکار CTA در مقاله
                    </button>

                    <div class="bk-cta-list" x-show="ctaPlacements.length" x-cloak style="margin-top:12px">
                        <div class="bk-card-head"><span>CTAهای درج‌شده</span></div>
                        <template x-for="(c, ci) in ctaPlacements" :key="c.id">
                            <div class="bk-link-list-item" :class="{ 'is-selected': c.selected }">
                                <label class="bk-pick-check">
                                    <input type="checkbox" x-model="c.selected">
                                    <span class="bk-pick-box"></span>
                                </label>
                                <div class="bk-sug-main">
                                    <strong class="bk-sug-title" x-text="c.text"></strong>
                                    <small class="bk-sug-url" dir="ltr" x-text="c.url"></small>
                                    <small class="bk-hint" x-text="'موقعیت: بعد از پاراگراف ' + c.afterParagraph"></small>
                                </div>
                                <div class="bk-actions-row">
                                    <button type="button" class="bk-icon-action-btn is-danger bk-icon-tone-red" title="حذف این CTA" @click="removeCtaPlacement(c)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg></button>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="bk-btn-ghost bk-btn-sm" style="width:100%;margin-top:8px" x-show="ctaPlacements.some(c=>c.selected)" @click="removeSelectedCtas()">
                            حذف موارد انتخاب‌شده
                        </button>
                    </div>
                </div>

                <div class="bk-card">
                    <div class="bk-card-head"><span>لینک‌های خارجی موجود</span></div>
                    <template x-if="!(linkData.external?.length)">
                        <div class="bk-empty-state">هنوز لینک خارجی در متن ثبت نشده است.</div>
                    </template>
                    <template x-for="(l, idx) in (linkData.external || [])" :key="'le'+idx+l.href">
                        <div class="bk-link-list-item">
                            <div class="bk-link-list-title" x-text="l.text || 'بدون انکر'" :title="l.text || l.href"></div>
                            <div class="bk-actions-row">
                                <button type="button" class="bk-icon-action-btn bk-icon-tone-blue" title="مشاهده در محتوا" @click="scrollToLinkInContent(l)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="15" height="15"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg></button>
                                <a class="bk-icon-action-btn bk-icon-tone-purple" :href="l.href" target="_blank" rel="noopener" title="باز کردن آدرس"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="15" height="15"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14L21 3"/></svg></a>
                                <button type="button" class="bk-icon-action-btn is-danger bk-icon-tone-red" title="غیرفعال‌سازی / حذف لینک از محتوا" @click="removeLinkFromContent(l)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" width="15" height="15"><path d="M18.84 12.25l1.72-1.71a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M5.16 11.75l-1.72 1.71a5 5 0 0 0 7.07 7.07l1.72-1.71"/><path d="M8 16l8-8"/><path d="M2 2l20 20"/></svg></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>



        <!-- ========== 5. MEDIA / IMAGES TAB ========== -->
        <section x-show="activeTab === 'media'" class="bk-panel" x-cloak>
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>تصاویر مقاله</span>
                    <button type="button" class="bk-link-btn" @click="scanPostImages()">اسکن مجدد</button>
                </div>

                <div class="bk-media-actions-top">
                    <button type="button" class="bk-btn-primary bk-btn-sm" style="width:100%"
                        @click="optOptions.watermark=false; optOptions.resize=true; runOptimizeImages()"
                        :disabled="optBusy || !(postImages||[]).length">
                        <span x-text="optBusy ? 'در حال فشرده‌سازی…' : 'فشرده‌سازی و استانداردسازی سایز'"></span>
                    </button>
                    <div class="bk-media-wm-row">
                        <button type="button" class="bk-btn-ghost bk-btn-sm"
                            @click="optOptions.watermark=true; runOptimizeImages()"
                            :disabled="optBusy || !(postImages||[]).length">
                            اعمال واترمارک
                        </button>
                        <button type="button" class="bk-btn-ghost bk-btn-sm"
                            @click="showBulkAltPanel = !showBulkAltPanel"
                            :disabled="!(postImages||[]).length">
                            ساخت Alt
                        </button>
                    </div>
                    <div class="bk-bulk-alt-panel" x-show="showBulkAltPanel" x-cloak>
                        <button type="button" class="bk-btn-primary bk-btn-sm" style="width:100%;margin-bottom:8px"
                            @click="generateAllAltsWithAi()" :disabled="aiBusy || optBusy">
                            <span x-text="aiBusy ? 'در حال تولید Alt با AI…' : 'تولید Alt همه با هوش مصنوعی'"></span>
                        </button>
                        <div class="bk-field">
                            <label>Alt یکسان برای همه</label>
                            <input type="text" x-model="bulkAltText" placeholder="متن Alt برای تمام تصاویر…">
                        </div>
                        <button type="button" class="bk-btn-ghost bk-btn-sm" style="width:100%;margin-top:8px"
                            @click="applyBulkAltText()" :disabled="!bulkAltText">
                            اعمال Alt یکسان
                        </button>
                    </div>
                </div>

                <div class="bk-media-list" x-show="(postImages||[]).length">
                    <template x-for="(img, idx) in postImages" :key="img.id || img.src || idx">
                        <div class="bk-media-card">
                            <div class="bk-media-card__thumb">
                                <img :src="img.src || img.original_src" alt="" loading="lazy">
                                <div class="bk-media-thumb-meta">
                                    <span x-text="(img.format_after || img.format_before || img.format || '—').toUpperCase()"></span>
                                    <span x-text="img.size_after || img.size_before || img.size || '—'"></span>
                                </div>
                            </div>
                            <div class="bk-media-card__body">
                                <div class="bk-media-card__meta">
                                    <span class="bk-media-badge is-ok" x-show="img.alt || img.new_alt">Alt دارد</span>
                                    <span class="bk-media-badge is-warn" x-show="!(img.alt || img.new_alt)">بدون Alt</span>
                                    <span class="bk-media-badge is-fmt" x-text="(img.format_after || img.format_before || img.format || '—').toUpperCase()"></span>
                                </div>
                                <div class="bk-media-size-row">
                                    <span>قبل: <strong x-text="img.size_before || img.size || '—'"></strong></span>
                                    <span x-show="img.size_after">بعد: <strong x-text="img.size_after"></strong></span>
                                </div>
                                <div class="bk-media-alt-row">
                                    <template x-if="!img.editingAlt">
                                        <div class="bk-media-alt-display">
                                            <span class="bk-media-alt-text" x-text="img.new_alt || img.alt || 'بدون Alt'"></span>
                                            <button type="button" class="bk-icon-action-btn" title="ویرایش Alt" @click="img.editingAlt = true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="14" height="14"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="img.editingAlt">
                                        <div class="bk-media-alt-edit">
                                            <input type="text" class="bk-media-alt-input"
                                                :value="img.new_alt || img.alt || ''"
                                                @keydown.enter.prevent="updateImageAlt(img, $event.target.value); img.editingAlt=false"
                                                x-ref="altEdit"
                                                placeholder="متن جایگزین (Alt)">
                                            <div class="bk-actions-row" style="margin-top:6px">
                                                <button type="button" class="bk-btn-primary bk-btn-xs" @click="updateImageAlt(img, $el.closest('.bk-media-alt-edit').querySelector('input').value); img.editingAlt=false">ذخیره</button>
                                                <button type="button" class="bk-btn-ghost bk-btn-xs" @click="img.editingAlt=false">انصراف</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="bk-media-empty" x-show="!(postImages||[]).length">
                    تصویری در بدنه مقاله یافت نشد.
                </div>
            </div>
        </section>



        <!-- ========== 6. SCHEMA TAB ========== -->
        <section x-show="activeTab === 'schema'" class="bk-panel" x-cloak>
            <div class="bk-card">
                <div class="bk-card-head"><span>نوع داده ساختاریافته (Schema)</span></div>
                <select x-model="schemaType" @change="onSchemaTypeChange()">
                    <option value="Article">Article (مقاله)</option>
                    <option value="BlogPosting">BlogPosting (پست بلاگ)</option>
                    <option value="WebPage">WebPage (صفحه وب)</option>
                    <option value="FAQPage">FAQPage (سوالات متداول)</option>
                    <option value="HowTo">HowTo (راهنمای گام‌به‌گام)</option>
                    <option value="Product">Product (محصول)</option>
                    <option value="Organization">Organization (سازمان)</option>
                </select>
                <p class="bk-hint" style="margin-top:8px">BreadcrumbList و Article به‌صورت خودکار در خروجی کد اضافه می‌شود.</p>
            </div>

            <div class="bk-card" x-show="schemaType === 'FAQPage'" x-cloak>
                <div class="bk-card-head">
                    <span>سوالات متداول (FAQ)</span>
                    <button type="button" class="bk-link-btn" @click="faqItems.push({q:'',a:''})">+ سوال</button>
                </div>
                <template x-for="(item, i) in faqItems" :key="'faq'+i">
                    <div class="bk-schema-row">
                        <input type="text" placeholder="سوال" x-model="item.q" @input.debounce.400ms="syncSchemaFromForms()">
                        <textarea rows="2" placeholder="پاسخ" x-model="item.a" @input.debounce.400ms="syncSchemaFromForms()"></textarea>
                        <button type="button" class="bk-icon-action is-danger" @click="faqItems.splice(i,1); syncSchemaFromForms()" title="حذف">×</button>
                    </div>
                </template>
            </div>

            <div class="bk-card">
                <div class="bk-card-head">
                    <span>پیش‌نمایش JSON-LD</span>
                    <button type="button" class="bk-link-btn" @click="rebuildSchema()">بازنشانی</button>
                </div>
                <textarea
                    class="bk-code"
                    dir="ltr"
                    rows="10"
                    x-model="seo.schema"
                    @input.debounce.600ms="onFieldChange()"
                ></textarea>
            </div>
        </section>


        <!-- ========== 7. SOCIAL TAB ========== -->
        <section x-show="activeTab === 'social'" class="bk-panel" x-cloak>
            <div class="bk-card">
                <div class="bk-card-head"><span>پیش‌نمایش Open Graph (شبکه‌های اجتماعی)</span></div>
                <div class="bk-og-preview">
                    <div class="bk-og-img" :style="(seo.og_image || seo.x_image) ? ('background-image:url(' + (seo.og_image || seo.x_image) + ')') : ''">
                        <span x-show="!(seo.og_image || seo.x_image)">بدون تصویر</span>
                    </div>
                    <div class="bk-og-meta">
                        <small x-text="(seo.permalink || '').replace(/^https?:\/\//,'').split('/')[0] || 'example.com'"></small>
                        <strong x-text="seo.og_title || seo.seo_title || seo.title || 'عنوان'"></strong>
                        <p x-text="seo.og_description || seo.description || 'توضیحات…'"></p>
                    </div>
                </div>
            </div>

            <div class="bk-card">
                <div class="bk-card-head"><span>تنظیمات Open Graph</span></div>
                <div class="bk-field">
                    <label>عنوان OG</label>
                    <input type="text" x-model="seo.og_title" @input.debounce.400ms="onFieldChange()">
                </div>
                <div class="bk-field">
                    <label>توضیحات OG</label>
                    <textarea rows="3" x-model="seo.og_description" @input.debounce.400ms="onFieldChange()"></textarea>
                </div>
                <div class="bk-field">
                    <label>تصویر OG</label>
                    <div class="bk-input-with-action">
                        <input type="url" dir="ltr" x-model="seo.og_image" @input.debounce.400ms="onFieldChange()" placeholder="https://…">
                        <button type="button" class="bk-btn-ghost bk-btn-sm" @click="pickMedia('og_image')">رسانه</button>
                    </div>
                </div>
            </div>
        </section>


        <!-- ========== 8. AI ASSISTANT TAB ========== -->
        <section x-show="activeTab === 'ai'" class="bk-panel bk-ai-engine-readonly" x-cloak>
            <div class="bk-card bk-ai-dashboard-card">
                <div class="bk-card-head" style="margin-bottom:12px">
                    <div class="bk-ai-logo-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                    </div>
                    <div>
                        <strong style="font-size:13px;display:block">دستیار هوشمند سئوی بنکای</strong>
                        <small style="color:#64748b;font-size:10px">ابزارهای حرفه‌ای بهینه‌سازی تولید محتوا و سئو با یک کلیک</small>
                    </div>
                </div>

                <!-- HERO ONE-CLICK AUTO OPTIMIZE -->
                <div class="bk-action-card bk-hero-action-card" @click="openAiModal('auto_all')" style="margin-bottom:14px;cursor:pointer">
                    <div class="bk-action-badge">پیشنهادی 🚀</div>
                    <div class="bk-action-content">
                        <h4 style="margin-bottom:4px">بهینه‌سازی یک‌پارچه مقاله (Auto-Optimize All)</h4>
                        <p style="margin-bottom:10px;font-size:11px">اجرای کامل تحلیل مقاله، اصلاح عنوان و متادیسکریپشن، استخراج کلیدواژه‌ها، پیشنهاد لینک داخلی، سئوی تصاویر و اسکیما با AI.</p>
                    </div>
                    <button type="button" class="bk-btn-primary bk-full" :disabled="aiLoading">
                        شروع بهینه‌سازی هوشمند
                    </button>
                </div>

                <!-- AI TOOLS DASHBOARD GRID -->
                <div class="bk-ai-tools-list" style="display:flex;flex-direction:column;gap:8px">
                    <button type="button" class="bk-ai-tool-item" @click="openAiModal('meta')">
                        <div class="bk-ai-tool-icon bk-icon-blue">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 7V5h16v2M12 5v14M9 19h6"/></svg>
                        </div>
                        <div class="bk-ai-tool-info">
                            <strong>تولید عنوان و متا دیسکریپشن سئو</strong>
                            <small>ایجاد متاداده‌های استاندارد و جذاب با الگوریتم سئو</small>
                        </div>
                    </button>

                    <button type="button" class="bk-ai-tool-item" @click="openAiModal('keywords')">
                        <div class="bk-ai-tool-icon bk-icon-purple">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/></svg>
                        </div>
                        <div class="bk-ai-tool-info">
                            <strong>استخراج کلمه کلیدی اصلی و فرعی</strong>
                            <small>شناسایی کلیدواژه‌های پرجستجو و ادغام با کلیدواژه‌های ثابت</small>
                        </div>
                    </button>

                    <button type="button" class="bk-ai-tool-item" @click="generateAeoFaq()" :disabled="aiLoading">
                        <div class="bk-ai-tool-icon bk-icon-emerald">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="bk-ai-tool-info">
                            <strong>تولید بخش سوالات متداول (AEO FAQ)</strong>
                            <small>ساخت پرسش‌های واقعی کاربر جهت جلب پاسخ برجسته گوگل</small>
                        </div>
                    </button>

                    <button type="button" class="bk-ai-tool-item" @click="generateGeoSummary()" :disabled="aiLoading">
                        <div class="bk-ai-tool-icon bk-icon-amber">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <div class="bk-ai-tool-info">
                            <strong>تولید خلاصه نکات کلیدی (GEO Takeaways)</strong>
                            <small>چکیده مطالب جهت نقل‌قول توسط موتورهای مولد مثل ChatGPT</small>
                        </div>
                    </button>

                    <button type="button" class="bk-ai-tool-item" @click="openAiModal('links')">
                        <div class="bk-ai-tool-icon bk-icon-blue">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="2"/><path d="M12 2v4M12 18v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M2 12h4M18 12h4M4.9 19.1l2.8-2.8M16.3 7.7l2.8-2.8"/></svg>
                        </div>
                        <div class="bk-ai-tool-info">
                            <strong>لینک‌سازی هوشمند داخلی</strong>
                            <small>پیشنهاد انکورتکست و مقالات مرتبط بر اساس محتوا</small>
                        </div>
                    </button>

                    <button type="button" class="bk-ai-tool-item" @click="openAiModal('images')">
                        <div class="bk-ai-tool-icon bk-icon-amber">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        </div>
                        <div class="bk-ai-tool-info">
                            <strong>تولید Alt Text تصاویر مقاله</strong>
                            <small>تولید متن جایگزین سئوشده بر اساس محتوای پیرامونی</small>
                        </div>
                    </button>

                    <button type="button" class="bk-ai-tool-item" @click="openAiModal('rewrite')">
                        <div class="bk-ai-tool-icon bk-icon-purple">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                        </div>
                        <div class="bk-ai-tool-info">
                            <strong>بازنویسی کامل مقاله با AI</strong>
                            <small>روان‌سازی و ارتقای لحن محتوا با حفظ تگ‌های HTML</small>
                        </div>
                    </button>
                </div>
            </div>
        </section>

    </div>

    <!-- AI MODAL — visual multi-phase workflow -->
    <div class="bk-modal-backdrop"
         x-show="aiModalOpen || aiOpen"
         x-cloak
         :class="{ 'is-open': aiModalOpen || aiOpen }"
         @keydown.escape.window="closeAiModal()"
         @click.self="closeAiModal()">
        <div class="bk-modal bk-modal-ai-flow" @click.stop>
            <div class="bk-modal-head">
                <div class="bk-modal-head-text">
                    <strong x-text="aiModalTitle || 'دستیار AI سئو'"></strong>
                    <small class="bk-modal-phase-label" x-text="aiPhaseLabel()"></small>
                </div>
                <button type="button" class="bk-icon-action" @click="closeAiModal()" :disabled="aiBusy || aiLoading" aria-label="بستن">×</button>
            </div>

            <!-- phase stepper -->
            <div class="bk-ai-stepper">
                <div class="bk-ai-step" :class="{ 'is-active': aiPhase === 'setup', 'is-done': aiPhaseIndex() > 0 }"><span>1</span><em>تنظیم</em></div>
                <div class="bk-ai-step-line" :class="{ 'is-on': aiPhaseIndex() > 0 }"></div>
                <div class="bk-ai-step" :class="{ 'is-active': aiPhase === 'running', 'is-done': aiPhaseIndex() > 1 }"><span>2</span><em>اجرا</em></div>
                <div class="bk-ai-step-line" :class="{ 'is-on': aiPhaseIndex() > 1 }"></div>
                <div class="bk-ai-step" :class="{ 'is-active': aiPhase === 'review', 'is-done': aiPhaseIndex() > 2 }"><span>3</span><em>بازبینی</em></div>
            </div>

            <div class="bk-modal-body">
                <!-- ===== PHASE: SETUP ===== -->
                <div x-show="aiPhase === 'setup'" x-cloak>
                    <div class="bk-ai-hero-banner" :class="'tone-' + (aiTask || 'default')">
                        <div class="bk-ai-hero-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="22" height="22"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        </div>
                        <div>
                            <strong x-text="aiModalTitle"></strong>
                            <p x-text="aiTaskBlurb()"></p>
                        </div>
                    </div>

                    <div class="bk-field" style="margin-bottom:10px">
                        <label>انتخاب موتور هوش مصنوعی</label>
                        <select x-model="aiProvider">
                            <template x-for="p in (window.bankaiEditorSeo && bankaiEditorSeo.aiProviders) || [{id:'',label:'پیش‌فرض سیستم'}]" :key="p.id || p.label">
                                <option :value="p.id" x-text="p.label || p.name || p.id"></option>
                            </template>
                        </select>
                    </div>

                    <div class="bk-field" x-show="aiTask === 'title' || aiTask === 'description' || aiTask === 'meta' || aiTask === 'rewrite' || aiTask === 'takeaways' || aiTask === 'faq' || aiTask === 'auto_all' || !aiTask" style="margin-bottom:10px">
                        <label>سبک تولید</label>
                        <select x-model="aiOptions.style">
                            <option value="seo">سئو استاندارد (پیش‌فرض)</option>
                            <option value="engaging">جذاب و کلیک‌خور (High CTR)</option>
                            <option value="formal">رسمی و حرفه‌ای</option>
                            <option value="simple">ساده و روان</option>
                        </select>
                    </div>

                    <div class="bk-ai-plan-card">
                        <div class="bk-ai-plan-head">فرایندهای این اجرا</div>
                        <template x-for="(item, i) in plannedChecklist()" :key="item.id">
                            <div class="bk-ai-plan-row">
                                <span class="bk-ai-plan-num" x-text="(i+1)"></span>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>

                    <button type="button" class="bk-btn-primary bk-full bk-ai-start-btn" @click="startAiWorkflow()" :disabled="aiBusy || aiLoading">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        <span>شروع فرایند</span>
                    </button>
                </div>

                <!-- ===== PHASE: RUNNING ===== -->
                <div x-show="aiPhase === 'running'" x-cloak>
                    <div class="bk-ai-progress-ring-wrap">
                        <div class="bk-ai-progress-pct" x-text="(aiProgress || 0) + '%'"></div>
                        <div class="bk-ai-progress-track">
                            <div class="bk-ai-progress-fill" :style="'width:' + (aiProgress || 0) + '%'"></div>
                        </div>
                        <p class="bk-ai-progress-step" x-text="aiStepText || 'در حال پردازش…'"></p>
                    </div>

                    <div class="bk-ai-live-checklist">
                        <template x-for="item in activeRunChecklist()" :key="item.id">
                            <div class="bk-ai-live-item" :class="'st-' + (item.status || 'pending')">
                                <span class="bk-ai-live-dot">
                                    <template x-if="item.status === 'success' || item.status === 'done'"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></template>
                                    <template x-if="item.status === 'loading'"><svg class="bk-spin" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg></template>
                                    <template x-if="item.status === 'error'"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></template>
                                    <template x-if="!item.status || item.status === 'pending'"><span class="bk-ai-dot-empty"></span></template>
                                </span>
                                <span class="bk-ai-live-label" x-text="item.label"></span>
                                <span class="bk-ai-live-status" x-text="aiStatusFa(item.status)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ===== PHASE: REVIEW ===== -->
                <div x-show="aiPhase === 'review'" x-cloak>
                    <div class="bk-ai-review-banner">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                        <span>پاسخ هوش مصنوعی آماده است — بازبینی و تایید کنید</span>
                    </div>

                    <div class="bk-ai-result-card" x-show="aiDraft.title || aiDraft.description">
                        <div class="bk-ai-result-label">عنوان سئو</div>
                        <div class="bk-ai-result-value" x-text="aiDraft.title || '—'"></div>
                        <div class="bk-ai-result-label">توضیحات متا</div>
                        <div class="bk-ai-result-value" x-text="aiDraft.description || '—'"></div>
                    </div>

                    <div class="bk-ai-result-card" x-show="(aiDraft.keywords && aiDraft.keywords.length) || aiDraft.focus_keyword">
                        <div class="bk-ai-result-label">کلمات کلیدی</div>
                        <div class="bk-ai-result-chips">
                            <span class="bk-chip is-primary" x-show="aiDraft.focus_keyword" x-text="aiDraft.focus_keyword"></span>
                            <template x-for="(k,ki) in (aiDraft.keywords || [])" :key="ki">
                                <span class="bk-chip" x-text="k"></span>
                            </template>
                        </div>
                    </div>

                    <div class="bk-ai-result-card" x-show="aiDraft.rewrite">
                        <div class="bk-ai-result-label">بازنویسی محتوا</div>
                        <textarea class="bk-ai-edit-area" x-model="aiDraft.rewrite" rows="6"></textarea>
                    </div>

                    <div class="bk-ai-result-card" x-show="aiDraft.faqHtml || aiDraft.faqText">
                        <div class="bk-ai-result-label">خروجی AEO / FAQ</div>
                        <div class="bk-ai-html-preview" x-show="aiDraft.faqHtml" x-html="aiDraft.faqHtml"></div>
                        <textarea class="bk-ai-edit-area" x-model="aiDraft.faqText" rows="4" placeholder="ویرایش متنی FAQ…"></textarea>
                    </div>

                    <div class="bk-ai-result-card" x-show="aiDraft.geoText || aiDraft.takeaways || aiDraft.geoHtml">
                        <div class="bk-ai-result-label">خروجی GEO / نکات کلیدی</div>
                        <div class="bk-ai-html-preview" x-show="aiDraft.geoHtml" x-html="aiDraft.geoHtml"></div>
                        <textarea class="bk-ai-edit-area" x-model="aiDraft.geoText" rows="4" placeholder="ویرایش متنی خلاصه…"></textarea>
                    </div>

                    <div class="bk-ai-result-card" x-show="aiDraft.smartLinks && aiDraft.smartLinks.length">
                        <div class="bk-ai-result-label">پیشنهاد لینک داخلی</div>
                        <template x-for="(s,si) in aiDraft.smartLinks" :key="si">
                            <div class="bk-ai-mini-link">
                                <strong x-text="s.target_title || s.title"></strong>
                                <small dir="ltr" x-text="s.target_url || s.permalink"></small>
                            </div>
                        </template>
                    </div>

                    <div class="bk-ai-result-card" x-show="aiDraft.altTexts && aiDraft.altTexts.length">
                        <div class="bk-ai-result-label">Alt تصاویر</div>
                        <template x-for="(a,ai) in aiDraft.altTexts" :key="ai">
                            <div class="bk-ai-mini-link"><span x-text="a.suggestedAlt || a.alt || '—'"></span></div>
                        </template>
                    </div>

                    <div class="bk-ai-review-actions">
                        <button type="button" class="bk-btn-primary bk-full" @click="applyAiReview()">
                            تایید و اعمال
                        </button>
                        <div class="bk-ai-review-secondary">
                            <button type="button" class="bk-btn-ghost bk-btn-sm" @click="aiPhase = 'setup'">ویرایش دستی تنظیمات</button>
                            <button type="button" class="bk-btn-ghost bk-btn-sm" @click="rewriteAiWorkflow()" :disabled="aiBusy || aiLoading">بازنویسی با AI</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div class="bk-toast" x-show="toast.show" x-cloak
         :class="'is-' + (toast.type === 'warn' ? 'warning' : toast.type)"
         role="status"
         @click="toast.show = false">
        <span class="bk-toast-icon" aria-hidden="true">
            <template x-if="toast.type === 'success'"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg></template>
            <template x-if="toast.type === 'error'"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg></template>
            <template x-if="toast.type === 'warning' || toast.type === 'warn'"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg></template>
            <template x-if="toast.type === 'info' || !toast.type"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg></template>
        </span>
        <span class="bk-toast-msg" x-text="toast.message"></span>
    </div>
</div>
