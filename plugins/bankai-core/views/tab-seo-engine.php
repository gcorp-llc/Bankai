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

    <!-- HEADER (compact) -->
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
            <button type="button" class="bk-icon-btn" @click="analyze(true)" :disabled="loading" title="تازه‌سازی">
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
                            :stroke="subScoreColor(subScores.seo)" stroke-width="3"
                            stroke-linecap="round"
                            :stroke-dasharray="2 * Math.PI * 12"
                            :stroke-dashoffset="subScoreOffset(subScores.seo)"/>
                    </svg>
                    <span class="bk-subscore-num" :style="{ color: subScoreColor(subScores.seo) }" x-text="(subScores.seo || 0) + '%'">0%</span>
                </div>
                <span class="bk-subscore-label">SEO</span>
            </div>

            <div class="bk-subscore-card">
                <div class="bk-subscore-ring">
                    <svg viewBox="0 0 32 32">
                        <circle class="bk-track" cx="16" cy="16" r="12" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                        <circle class="bk-progress" cx="16" cy="16" r="12" fill="none"
                            :stroke="subScoreColor(subScores.aeo)" stroke-width="3"
                            stroke-linecap="round"
                            :stroke-dasharray="2 * Math.PI * 12"
                            :stroke-dashoffset="subScoreOffset(subScores.aeo)"/>
                    </svg>
                    <span class="bk-subscore-num" :style="{ color: subScoreColor(subScores.aeo) }" x-text="(subScores.aeo || 0) + '%'">0%</span>
                </div>
                <span class="bk-subscore-label">AEO</span>
            </div>

            <div class="bk-subscore-card">
                <div class="bk-subscore-ring">
                    <svg viewBox="0 0 32 32">
                        <circle class="bk-track" cx="16" cy="16" r="12" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                        <circle class="bk-progress" cx="16" cy="16" r="12" fill="none"
                            :stroke="subScoreColor(subScores.geo)" stroke-width="3"
                            stroke-linecap="round"
                            :stroke-dasharray="2 * Math.PI * 12"
                            :stroke-dashoffset="subScoreOffset(subScores.geo)"/>
                    </svg>
                    <span class="bk-subscore-num" :style="{ color: subScoreColor(subScores.geo) }" x-text="(subScores.geo || 0) + '%'">0%</span>
                </div>
                <span class="bk-subscore-label">GEO</span>
            </div>
        </div>

        <!-- TABS -->
        <nav class="bk-tabs">
            <button type="button" :class="{ 'is-active': activeTab === 'seo' }" @click="activeTab = 'seo'">چک‌لیست</button>
            <button type="button" :class="{ 'is-active': activeTab === 'aeo_geo' }" @click="activeTab = 'aeo_geo'">AEO & GEO</button>
            <button type="button" :class="{ 'is-active': activeTab === 'links' }" @click="activeTab = 'links'; loadLinks()">لینک‌ها</button>
            <button type="button" :class="{ 'is-active': activeTab === 'media' }" @click="activeTab = 'media'; scanPostImages()">تصاویر</button>
            <button type="button" :class="{ 'is-active': activeTab === 'schema' }" @click="activeTab = 'schema'">اسکیما</button>
            <button type="button" :class="{ 'is-active': activeTab === 'social' }" @click="activeTab = 'social'">سوشال</button>
            <button type="button" class="bk-tab-ai" @click="openAiModal()">
                دستیار AI <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
            </button>
        </nav>
    </header>

    <!-- BODY (no inner scroll — page scrolls) -->
    <div class="bk-body">

        <!-- ========== SEO TAB ========== -->
        <section x-show="activeTab === 'seo'" class="bk-panel">

            <!-- SERP -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <svg class="solar-icon bk-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <span>پیش‌نمایش SERP</span>
                    <div class="bk-seg">
                        <button type="button" :class="{ 'is-on': !serpMobile }" @click="serpMobile = false">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
                        </button>
                        <button type="button" :class="{ 'is-on': serpMobile }" @click="serpMobile = true">
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

            <!-- Sync from Rank Math / Yoast -->
            <div class="bk-card" style="padding:10px 12px;">
                <button type="button"
                    class="bk-btn-primary bk-btn-sm bk-sync-legacy-btn"
                    style="width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;"
                    :class="{ 'is-loading': legacySyncBusy, 'is-done': legacySyncDone }"
                    @click="syncFromLegacySeo()"
                    :disabled="legacySyncBusy">
                    <!-- loading spinner -->
                    <svg x-show="legacySyncBusy" class="solar-icon bk-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg>
                    <!-- success check -->
                    <svg x-show="!legacySyncBusy && legacySyncDone" class="solar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    <!-- chart icon default -->
                    <svg x-show="!legacySyncBusy && !legacySyncDone" class="solar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 16v-5M12 16V8M17 16v-3"/></svg>
                    <span x-text="legacySyncBusy ? 'در حال همگام‌سازی…' : (legacySyncDone ? 'همگام‌سازی انجام شد' : 'همگام‌سازی')"></span>
                </button>
                <p class="bk-hint" style="margin:6px 0 0;font-size:11px;">ورود یک‌کلیکه متا از Rank Math / Yoast در صورت وجود داده قبلی.</p>
            </div>
            <style>
                .bk-sync-legacy-btn.is-done { background:#16a34a !important; border-color:#16a34a !important; color:#fff !important; }
                .bk-sync-legacy-btn.is-loading { opacity:.9; }
                .bk-spin { animation: bk-spin 0.8s linear infinite; }
                @keyframes bk-spin { to { transform: rotate(360deg); } }
            </style>
            <!-- KEYWORDS (Rank Math style) -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>کلیدواژه‌ها</span>
                    <button type="button" class="bk-link-btn" @click="openAiModal('keywords')">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        پیشنهاد AI
                    </button>
                </div>

                <div class="bk-kw-box" @click="$refs.kwInput.focus()">
                    <template x-for="(kw, i) in keywordList" :key="i">
                        <span class="bk-chip" :class="{ 'is-primary': i === 0 }">
                            <span
                                x-text="kw"
                                @dblclick="editKeyword(i)"
                                contenteditable="false"
                            ></span>
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
                        placeholder="کلیدواژه + Enter"
                        @keydown.enter.prevent="addKeywordFromInput()"
                        @keydown.,.prevent="addKeywordFromInput()"
                        x-model="kwDraft"
                    >
                </div>
                <div class="bk-kw-stats" x-show="seo.focus_keyword">
                    <span>چگالی: <strong x-text="(analysis.stats?.density ?? 0) + '%'"></strong></span>
                    <span class="ok" x-show="analysis.stats?.density >= 0.5 && analysis.stats?.density <= 2.5">توزیع امن</span>
                    <span class="warn" x-show="analysis.stats?.density < 0.5 || analysis.stats?.density > 2.5">نیاز به تنظیم</span>
                </div>
            </div>

            <!-- SEO TITLE -->
            <div class="bk-card bk-field-card">
                <div class="bk-field-head">
                    <label>عنوان سئو</label>
                    <div class="bk-field-actions">
                        <span class="bk-counter" :class="seoTitleClass" x-text="(seo.seo_title || '').length + '/60'"></span>
                        <button type="button" class="bk-link-btn" @click="openAiModal('title')">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 20V10M18 20V4M6 20v-4M10 20V8"/></svg>
                            AI
                        </button>
                    </div>
                </div>
                <input type="text" x-model="seo.seo_title" @input.debounce.400ms="onFieldChange()" maxlength="70">
                <div class="bk-bar"><div :style="'width:' + titleBarPct + '%;background:' + titleBarColor"></div></div>
            </div>

            <!-- META DESCRIPTION -->
            <div class="bk-card bk-field-card">
                <div class="bk-field-head">
                    <label>توضیحات متا</label>
                    <div class="bk-field-actions">
                        <span class="bk-counter" :class="descClass" x-text="(seo.description || '').length + '/160'"></span>
                        <button type="button" class="bk-link-btn" @click="openAiModal('description')">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                            AI
                        </button>
                    </div>
                </div>
                <textarea x-model="seo.description" @input.debounce.400ms="onFieldChange()" maxlength="170" rows="3"></textarea>
                <div class="bk-bar"><div :style="'width:' + descBarPct + '%;background:' + descBarColor"></div></div>
            </div>

            <!-- ANALYSIS CHECKLIST (Redesigned into Separate Graphic Cards per Group) -->
            <div class="bk-checklist-section">
                <div class="bk-section-divider">
                    <span>چک‌لیست و کیفیت سئو</span>
                    <span class="bk-badge" :style="'background:' + scoreColor + '22;color:' + scoreColor + ';border-color:' + scoreColor">
                        <span x-text="analysis.passed"></span>/<span x-text="analysis.total"></span>
                    </span>
                </div>

                <template x-for="(group, gkey) in analysis.groups" :key="gkey">
                    <div class="bk-card bk-check-group-card" x-show="group.items && group.items.length">
                        <div class="bk-card-head" @click="toggleGroup(gkey)" style="cursor:pointer;user-select:none;">
                            <div class="bk-group-title-wrap">
                                <svg class="solar-icon bk-purple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
                                <strong x-text="group.label"></strong>
                            </div>
                            <div class="bk-group-head-actions">
                                <span class="bk-badge-sm" :class="groupPassedCount(group) === group.items.length ? 'is-pass' : 'is-warn'">
                                    <span x-text="groupPassedCount(group) + '/' + group.items.length"></span>
                                </span>
                                <svg class="solar-icon bk-chevron" :class="{ 'is-open': openGroups[gkey] }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                            </div>
                        </div>

                        <!-- Progress Bar for Group -->
                        <div class="bk-bar" style="margin:0 12px 10px;">
                            <div :style="'width:' + Math.round((groupPassedCount(group) / (group.items.length || 1)) * 100) + '%;background:' + (groupPassedCount(group) === group.items.length ? '#10b981' : '#f59e0b')"></div>
                        </div>

                        <div class="bk-acc-body" x-show="openGroups[gkey]">
                            <template x-for="check in group.items" :key="check.key">
                                <div class="bk-check" :class="checkStatusClass(check)">
                                    <div class="bk-check-icon">
                                        <template x-if="check.passed"><svg class="solar-icon bk-text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></template>
                                        <template x-if="!check.passed"><svg class="solar-icon bk-text-warn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></template>
                                    </div>
                                    <div class="bk-check-main">
                                        <strong x-text="check.label"></strong>
                                        <small x-text="check.message"></small>
                                        <template x-if="check.key === 'short_paragraphs' && !check.passed">
                                            <button type="button" class="bk-btn-sm bk-fix-para-btn" @click="autoSplitLongParagraphs()">
                                                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M3 12h12a3 3 0 0 1 0 6h-3"/><path d="M15 15l-3 3 3 3M3 18h5"/></svg>
                                                کوتاه‌سازی خودکار پاراگراف‌ها
                                            </button>
                                        </template>
                                    </div>
                                    <span class="bk-check-status" x-text="checkStatusLabel(check)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ROBOTS -->
            <div class="bk-card">
                <div class="bk-card-head"><span>ایندکس و Robots</span></div>
                <label class="bk-switch-row">
                    <span>اجازه ایندکس</span>
                    <input type="checkbox" x-model="seo.robots.index" @change="onFieldChange()">
                </label>
                <label class="bk-switch-row">
                    <span>دنبال کردن لینک‌ها</span>
                    <input type="checkbox" x-model="seo.robots.follow" @change="onFieldChange()">
                </label>
                <label class="bk-switch-row">
                    <span>noarchive (عدم آرشیو)</span>
                    <input type="checkbox" x-model="seo.robots.noarchive" @change="onFieldChange()">
                </label>
                <label class="bk-switch-row">
                    <span>nosnippet</span>
                    <input type="checkbox" x-model="seo.robots.nosnippet" @change="onFieldChange()">
                </label>
                <label class="bk-switch-row">
                    <span>مخفی کردن تاریخ در SERP</span>
                    <input type="checkbox" x-model="seo.robots.hide_date" @change="onFieldChange()">
                </label>
                <div class="bk-field" style="margin-top:8px">
                    <label>max-image-preview</label>
                    <select x-model="seo.robots.max_image_preview" @change="onFieldChange()">
                        <option value="large">large</option>
                        <option value="standard">standard</option>
                        <option value="none">none</option>
                    </select>
                </div>
                <div class="bk-field">
                    <label>max-snippet (−۱ = بدون محدودیت)</label>
                    <input type="number" x-model.number="seo.robots.max_snippet" @change="onFieldChange()" min="-1" max="9999">
                </div>
                <div class="bk-field" style="margin-top:8px">
                    <label>Canonical URL</label>
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
                    <p class="bk-hint" style="margin-top:6px">در صورت پر بودن، بازدید این صفحه با کد ۳۰۱ به آدرس بالا منتقل می‌شود.</p>
                </div>
            </div>
        </section>

        <!-- ========== AEO & GEO TAB ========== -->
        <section x-show="activeTab === 'aeo_geo'" class="bk-panel">

            <!-- AI Engines Readiness Card -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <svg class="solar-icon bk-purple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg>
                    <span>آمادگی موتورهای پاسخ‌گو (AEO & GEO)</span>
                </div>
                <p class="bk-hint" style="margin-bottom:12px">ارزیابی ساختار محتوا، پاسخ‌های مستقیم و داده‌های ساختاریافته برای موتورهای هوش مصنوعی.</p>

                <div class="bk-aeo-engines-grid">
                    <div class="bk-engine-card">
                        <div class="bk-engine-head">
                            <strong>ChatGPT / OpenAI</strong>
                            <span class="bk-engine-score" :style="{ color: subScoreColor(subScores.aeo) }" x-text="(subScores.aeo || 0) + '%'"></span>
                        </div>
                        <div class="bk-bar"><div :style="'width:' + (subScores.aeo || 0) + '%;background:' + subScoreColor(subScores.aeo)"></div></div>
                        <small>استخراج FAQ و تعاریف</small>
                    </div>

                    <div class="bk-engine-card">
                        <div class="bk-engine-head">
                            <strong>Perplexity AI</strong>
                            <span class="bk-engine-score" :style="{ color: subScoreColor(subScores.aeo) }" x-text="Math.min(100, (subScores.aeo || 0) + 5) + '%'"></span>
                        </div>
                        <div class="bk-bar"><div :style="'width:' + Math.min(100, (subScores.aeo || 0) + 5) + '%;background:' + subScoreColor(subScores.aeo)"></div></div>
                        <small>لیست‌ها و منابع استنادی</small>
                    </div>

                    <div class="bk-engine-card">
                        <div class="bk-engine-head">
                            <strong>Google AI Overview</strong>
                            <span class="bk-engine-score" :style="{ color: subScoreColor(subScores.geo) }" x-text="(subScores.geo || 0) + '%'"></span>
                        </div>
                        <div class="bk-bar"><div :style="'width:' + (subScores.geo || 0) + '%;background:' + subScoreColor(subScores.geo)"></div></div>
                        <small>موجودیت‌ها و Key Takeaways</small>
                    </div>

                    <div class="bk-engine-card">
                        <div class="bk-engine-head">
                            <strong>Claude AI</strong>
                            <span class="bk-engine-score" :style="{ color: subScoreColor(subScores.aeo) }" x-text="Math.max(0, (subScores.aeo || 0) - 5) + '%'"></span>
                        </div>
                        <div class="bk-bar"><div :style="'width:' + Math.max(0, (subScores.aeo || 0) - 5) + '%;background:' + subScoreColor(subScores.aeo)"></div></div>
                        <small>عمق متن و ساختار منطقی</small>
                    </div>
                </div>
            </div>

            <!-- AEO FAQ Section Helper -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <svg class="solar-icon bk-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>بهینه‌سازی سوالات متداول (AEO FAQs)</span>
                    <button type="button" class="bk-link-btn" @click="openAiModal('faq')">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        تولید FAQ با AI
                    </button>
                </div>
                <p class="bk-hint">پاسخ‌های کوتاه و مستقیم به سوالات رایج کاربران شانس حضور در پاسخ‌های هوش مصنوعی را تا ۷۰٪ افزایش می‌دهد.</p>
                <div class="bk-faq-quick-list" x-show="faqItems && faqItems.length && faqItems[0].q">
                    <template x-for="(item, fi) in faqItems" :key="'aeofaq'+fi">
                        <div class="bk-faq-quick-item" x-show="item.q">
                            <strong x-text="'س) ' + item.q"></strong>
                            <p x-text="item.a || 'بدون پاسخ'"></p>
                        </div>
                    </template>
                </div>
                <button type="button" class="bk-btn-ghost bk-full" style="margin-top:10px;" @click="activeTab = 'schema'">مدیریت اسکیما FAQ</button>
            </div>

            <!-- GEO Key Takeaways & Summary -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <svg class="solar-icon bk-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/></svg>
                    <span>نکات کلیدی مقاله (GEO Summary / Key Takeaways)</span>
                    <button type="button" class="bk-link-btn" @click="openAiModal('takeaways')">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        ساخت با AI
                    </button>
                </div>
                <p class="bk-hint">خلاصه کلیدی در ابتدای مقاله جهت خلاصه‌سازی مستقیم توسط خزنده‌های موتورهای مولد (GEO).</p>
                <button type="button" class="bk-btn-primary bk-full" style="margin-top:10px" @click="insertKeyTakeawaysBox()">
                    درج باکس نکات کلیدی در ابتدای متن مقاله
                </button>
            </div>

        </section>

        <!-- ========== LINKS TAB (Enhanced Side-box) ========== -->
        <section x-show="activeTab === 'links'" class="bk-panel">
            
            <!-- Link Stats Header Card -->
            <div class="bk-card">
                <div class="bk-card-head"><span>آمار لینک‌های مقاله</span></div>
                <div class="bk-link-stats">
                    <div class="bk-stat">
                        <strong x-text="linkData.counts?.internal ?? 0">0</strong>
                        <span>داخلی</span>
                    </div>
                    <div class="bk-stat">
                        <strong x-text="linkData.counts?.external ?? 0">0</strong>
                        <span>خارجی</span>
                    </div>
                </div>

                <!-- Sub-tabs: Active vs Pending AI Links -->
                <div class="bk-subtabs-row" style="margin-top:12px;display:flex;gap:6px;border-bottom:1px solid #E2E8F0;padding-bottom:8px;">
                    <button type="button" class="bk-subtab-btn" :class="{ 'is-active': linksSubTab === 'active' }" @click="linksSubTab = 'active'">
                        لینک‌های فعال (<span x-text="(linkData.internal?.length || 0) + (linkData.external?.length || 0)">0</span>)
                    </button>
                    <button type="button" class="bk-subtab-btn" :class="{ 'is-active': linksSubTab === 'pending' }" @click="linksSubTab = 'pending'">
                        پیشنهادهای هوشمند (<span x-text="pendingAiLinks.length">0</span>)
                        <span class="bk-badge-dot" x-show="pendingAiLinks.length"></span>
                    </button>
                </div>
            </div>

            <!-- Pending AI Link Suggestions Box -->
            <div x-show="linksSubTab === 'pending'" x-cloak class="bk-pending-links-container">
                <div class="bk-card">
                    <div class="bk-card-head" style="justify-content:space-between;">
                        <span style="display:flex;align-items:center;gap:6px;">
                            <svg class="solar-icon bk-purple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                            <span>پیشنهادهای هوشمند در انتظار تایید</span>
                        </span>
                        <button type="button" class="bk-btn-ghost bk-btn-xs" @click="approveAllPendingLinks()" x-show="pendingAiLinks.length">
                            تایید و درج همگی
                        </button>
                    </div>

                    <template x-if="!pendingAiLinks.length">
                        <div class="bk-empty-state" style="text-align:center;padding:16px;color:#64748B;font-size:12px;">
                            هیچ پیشنهاد لینکی در انتظار تایید وجود ندارد.
                            <button type="button" class="bk-link-btn" @click="openAiModal('links')" style="display:block;margin:8px auto 0;">اجرای لینک‌سازی هوشمند با AI</button>
                        </div>
                    </template>

                    <template x-for="link in pendingAiLinks" :key="link.id">
                        <div class="bk-pending-link-card bk-link-card-stack">
                            <div class="bk-link-title-only" x-text="link.target_title || link.keyword" :title="link.target_title || link.keyword"></div>
                            <div class="bk-hint" style="margin:4px 0" x-show="link.keyword">انکر: <span x-text="link.keyword"></span></div>
                            <div class="bk-hint" style="margin:2px 0" x-show="link.score">امتیاز ارتباط: <strong x-text="Math.round(link.score)"></strong>
                                <span x-show="link.insertion_confidence==='low'" style="color:#b45309"> — محل درج نامطمئن؛ فقط پس از بررسی دستی اعمال شود</span>
                            </div>
                            <div class="bk-hint" style="margin:2px 0;color:#475569" x-show="link.reason" x-text="link.reason"></div>
                            <div class="bk-pending-actions bk-link-actions-below">
                                <button type="button" class="bk-btn-primary bk-btn-xs" @click="acceptPendingLink(link)" title="تایید و درج">تأیید و درج</button>
                                <a class="bk-btn-ghost bk-btn-xs" :href="link.target_url || '#'" target="_blank" rel="noopener">نمایش مقاله</a>
                                <button type="button" class="bk-btn-ghost bk-btn-xs bk-btn-danger" @click="rejectPendingLink(link)" title="رد">رد</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="bk-card" x-show="autoLinkSuggestions.length" x-cloak>
                <div class="bk-card-head"><span>پیشنهاد لینک داخلی (پس از ذخیره)</span></div>
                <template x-for="s in autoLinkSuggestions" :key="'als'+s.id">
                    <div class="bk-search-result-item bk-link-card-stack">
                        <div class="bk-link-title-only" x-text="s.title" :title="s.title"></div>
                        <div class="bk-link-actions-below">
                            <button type="button" class="bk-btn-primary bk-btn-xs" @click="linkAnchor = seo.focus_keyword || linkAnchor; insertLinkIntoEditor(linkAnchor || s.title, s.permalink, s.title, false, true); showToast('لینک درج شد', 'success'); loadLinks()">تایید</button>
                            <a class="bk-btn-ghost bk-btn-xs" :href="s.permalink" target="_blank" rel="noopener">نمایش مقاله</a>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Active Links List -->
            <div x-show="linksSubTab === 'active'">
                <!-- Internal Search & Manual Insert -->
                <div class="bk-card bk-link-builder-card">
                    <div class="bk-card-head">
                        <svg class="solar-icon bk-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="2"/><path d="M12 2v4M12 18v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M2 12h4M18 12h4M4.9 19.1l2.8-2.8M16.3 7.7l2.8-2.8"/></svg>
                        <span>لینک‌ساز داخلی</span>
                        <button type="button" class="bk-btn-ghost bk-btn-ai bk-btn-xs" style="margin-inline-start:auto" @click="openAiModal('links')" title="پیشنهاد با هوش مصنوعی">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                            AI
                        </button>
                    </div>
                    <p class="bk-hint">۱) کلیدواژه را جستجو کنید → ۲) مقالات مرتبط را تایید کنید → ۳) محل لینک در متن را انتخاب کنید → ۴) اعمال</p>
                    <div class="bk-search-combo bk-search-combo-inline">
                        <input type="text" x-model="linkAnchor" placeholder="کلیدواژه / انکر تکست…" @keydown.enter.prevent="runInternalSearch()">
                        <button type="button" class="bk-search-icon-btn" @click="runInternalSearch()" :disabled="linkBusy" title="جستجو">
                            <svg class="solar-icon" :class="{ 'bk-spin': linkBusy }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                        </button>
                    </div>

                    <!-- Step 1: Related articles -->
                    <div class="bk-match-section" x-show="postResults.length" x-cloak>
                        <div class="bk-card-head">
                            <span>مقالات مرتبط</span>
                            <button type="button" class="bk-link-btn" @click="acceptAllInternalPosts(); loadInternalContentMatches()" x-show="postResults.length">همه تایید</button>
                        </div>
                        <template x-for="p in postResults" :key="p.id">
                            <div class="bk-search-result-item bk-article-pick bk-link-card-stack" :class="{ 'is-accepted': p.accepted, 'is-rejected': p.rejected }">
                                <div class="bk-link-title-only" x-text="p.title" :title="p.title"></div>
                                <div class="bk-pending-actions bk-link-actions-below">
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="p.accepted=true; p.rejected=false; loadInternalContentMatches()" title="تایید">تایید</button>
                                    <a class="bk-btn-ghost bk-btn-xs" :href="p.permalink" target="_blank" rel="noopener">نمایش مقاله</a>
                                    <button type="button" class="bk-btn-ghost bk-btn-xs bk-btn-danger" @click="p.accepted=false; p.rejected=true" title="رد">رد</button>
                                </div>
                            </div>
                        </template>
                        <div class="bk-ext-actions-row" x-show="acceptedInternalPosts().length">
                            <button type="button" class="bk-btn-ghost bk-btn-sm" @click="loadInternalContentMatches()">
                                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="M21 21l-4.3-4.3M14 14l6 6"/></svg>
                                یافتن محل لینک در متن
                            </button>
                            <button type="button" class="bk-btn-ghost bk-btn-sm" @click="applyInternalRandom()" x-show="acceptedInternalPosts().length > 1">
                                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5"/></svg>
                                لینک تصادفی
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Content matches + assign article -->
                    <div class="bk-match-section" x-show="contentMatchesInternal.length" x-cloak>
                        <div class="bk-card-head"><span>تطابق در متن مقاله — انتخاب محل لینک</span></div>
                        <template x-for="(m, i) in contentMatchesInternal" :key="'cmi'+i">
                            <div class="bk-match-pick bk-match-assign">
                                <label class="bk-match-pick-label">
                                    <input type="checkbox" x-model="m.selected">
                                    <span x-text="m.snippet"></span>
                                </label>
                                <select x-show="m.selected" x-model="m.targetId">
                                    <option value="">انتخاب مقاله مقصد…</option>
                                    <template x-for="p in acceptedInternalPosts()" :key="'opt'+p.id">
                                        <option :value="String(p.id)" x-text="p.title"></option>
                                    </template>
                                </select>
                            </div>
                        </template>
                        <button type="button" class="bk-btn-primary bk-full" style="margin-top:10px"
                            @click="confirmInternalLinking()" :disabled="linkBusy">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                            ایجاد لینک داخلی (بولد + target=_blank + follow)
                        </button>
                    </div>
                </div>

                <div class="bk-card bk-link-builder-card">
                    <div class="bk-card-head">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>
                        <span>افزودن لینک خارجی</span>
                    </div>
                    <div class="bk-field">
                        <label>انکر / عبارت برای جستجو در متن</label>
                        <input type="text" x-model="extAnchor" placeholder="عبارت داخل مقاله" @keydown.enter.prevent="findExternalMatches()">
                    </div>
                    <div class="bk-field">
                        <label>آدرس مقصد</label>
                        <input type="url" dir="ltr" x-model="extUrl" placeholder="https://…">
                    </div>
                    <div class="bk-ext-actions-row">
                        <label class="bk-check-inline">
                            <input type="radio" name="bk_ext_rel" :checked="!extNofollow" @change="extNofollow=false"> follow
                        </label>
                        <label class="bk-check-inline">
                            <input type="radio" name="bk_ext_rel" :checked="extNofollow" @change="extNofollow=true"> nofollow
                        </label>
                        <button type="button" class="bk-btn-ghost bk-btn-sm" @click="findExternalMatches()">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                            یافتن در متن
                        </button>
                    </div>
                    <div class="bk-match-section" x-show="contentMatchesExternal.length" x-cloak>
                        <div class="bk-card-head"><span>تطابق در متن مقاله</span></div>
                        <template x-for="(m, i) in contentMatchesExternal" :key="'cme'+i">
                            <label class="bk-match-pick">
                                <input type="checkbox" x-model="m.selected">
                                <span x-text="m.snippet"></span>
                            </label>
                        </template>
                        <button type="button" class="bk-btn-primary bk-full" style="margin-top:10px" @click="confirmExternalLinking()">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                            ایجاد لینک خارجی (بولد + پنجره جدید)
                        </button>
                    </div>
                </div>


                <!-- Existing Links List -->
                <div class="bk-card">
                    <div class="bk-card-head">
                        <span>لینک‌های موجود در مقاله</span>
                        <button type="button" class="bk-link-btn" @click="loadLinks()" title="تازه‌سازی">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/></svg>
                        </button>
                    </div>
                    <template x-if="!((linkData.internal?.length || 0) + (linkData.external?.length || 0))">
                        <div class="bk-empty-state">هنوز لینکی در متن مقاله ثبت نشده است.</div>
                    </template>
                    <template x-for="(l, idx) in (linkData.internal || [])" :key="'li'+idx+l.href">
                        <div class="bk-link-item bk-link-item-row">
                            <span class="bk-tag internal">داخلی</span>
                            <div class="bk-link-item-main">
                                <strong x-text="l.text || '—'"></strong>
                                <a class="bk-link-url" :href="l.href" target="_blank" rel="noopener" dir="ltr" x-text="l.href"></a>
                            </div>
                            <div class="bk-link-item-actions">
                                <a class="bk-icon-action" :href="l.href" target="_blank" rel="noopener" title="مشاهده لینک">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h7v7M10 14L21 3M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
                                </a>
                                <button type="button" class="bk-icon-action is-danger" @click="removeLinkFromContent(l)" title="حذف لینک از متن">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.5.5l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7.5-.5l-2 2a5 5 0 0 0 7 7l1-1"/><path d="M2 2l20 20"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-for="(l, idx) in (linkData.external || [])" :key="'le'+idx+l.href">
                        <div class="bk-link-item bk-link-item-row">
                            <span class="bk-tag external">خارجی</span>
                            <div class="bk-link-item-main">
                                <strong x-text="l.text || '—'"></strong>
                                <a class="bk-link-url" :href="l.href" target="_blank" rel="noopener" dir="ltr" x-text="l.href"></a>
                            </div>
                            <div class="bk-link-item-actions">
                                <a class="bk-icon-action" :href="l.href" target="_blank" rel="noopener" title="مشاهده لینک">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h7v7M10 14L21 3M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
                                </a>
                                <button type="button" class="bk-icon-action is-danger" @click="removeLinkFromContent(l)" title="حذف لینک از متن">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.5.5l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7.5-.5l-2 2a5 5 0 0 0 7 7l1-1"/><path d="M2 2l20 20"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </section>

        <!-- ========== MEDIA / OPTIMIZE TAB ========== -->
        <section x-show="activeTab === 'media'" class="bk-panel bk-media-panel">
            <div class="bk-media-hero">
                <div class="bk-media-hero__text">
                    <span class="bk-media-hero__tag">Images</span>
                    <strong>بهینه‌سازی تصاویر مقاله</strong>
                    <p>اسکن، واترمارک، ابعاد ثابت، Alt و فشرده‌سازی — روی کاور و تصاویر محتوا.</p>
                </div>
                <button type="button" class="bk-media-btn bk-media-btn-icon" @click="scanPostImages()" :disabled="optBusy" title="اسکن تصاویر">
                    <svg class="solar-icon" :class="{ 'bk-spin': optBusy }" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.13-3.36L23 10M1 14l5.36 5.36A9 9 0 0 0 20.49 15"/></svg>
                </button>
            </div>

            <div class="bk-media-stats" x-show="(postImages||[]).length">
                <div class="bk-media-stat"><span x-text="(postImages||[]).length"></span><small>تصویر</small></div>
                <div class="bk-media-stat"><span x-text="(postImages||[]).filter(i=>!(i.alt||i.new_alt)).length"></span><small>بدون Alt</small></div>
                <div class="bk-media-stat"><span x-text="(postImages||[]).filter(i=>i.watermarked).length"></span><small>واترمارک</small></div>
            </div>

            <div class="bk-media-tools">
                <label class="bk-media-tool" :class="{ 'is-on': !!optOptions.convertWebp }">
                    <input type="checkbox" x-model="optOptions.convertWebp">
                    <span class="bk-media-tool__ico">🗜️</span>
                    <span class="bk-media-tool__lab">WebP / فشرده</span>
                    <small>تبدیل و کاهش حجم</small>
                </label>
                <label class="bk-media-tool" :class="{ 'is-on': !!optOptions.resize }">
                    <input type="checkbox" x-model="optOptions.resize">
                    <span class="bk-media-tool__ico">📐</span>
                    <span class="bk-media-tool__lab">ابعاد ثابت</span>
                    <small>عرض / ارتفاع حداکثر</small>
                </label>
                <label class="bk-media-tool" :class="{ 'is-on': !!optOptions.watermark }">
                    <input type="checkbox" x-model="optOptions.watermark">
                    <span class="bk-media-tool__ico">💧</span>
                    <span class="bk-media-tool__lab">واترمارک</span>
                    <small>اعمال روی تصاویر</small>
                </label>
                <label class="bk-media-tool" :class="{ 'is-on': !!optOptions.fillAlt }">
                    <input type="checkbox" x-model="optOptions.fillAlt">
                    <span class="bk-media-tool__ico">🔤</span>
                    <span class="bk-media-tool__lab">Alt خودکار</span>
                    <small>تکمیل متن جایگزین</small>
                </label>
            </div>

            <div class="bk-media-sliders">
                <div class="bk-media-field">
                    <label>حداکثر عرض <em x-text="(optOptions.maxWidth||1600)+'px'"></em></label>
                    <input type="range" min="320" max="2560" step="40" x-model.number="optOptions.maxWidth">
                </div>
                <div class="bk-media-field">
                    <label>حداکثر ارتفاع <em x-text="(optOptions.maxHeight||0) ? (optOptions.maxHeight+'px') : 'آزاد'"></em></label>
                    <input type="range" min="0" max="2000" step="40" x-model.number="optOptions.maxHeight">
                </div>
                <div class="bk-media-field">
                    <label>کیفیت فشرده‌سازی <em x-text="(optOptions.quality||82)+'%'"></em></label>
                    <input type="range" min="40" max="95" step="1" x-model.number="optOptions.quality">
                </div>
            </div>

            <div class="bk-media-alt-bar bk-media-alt-bar--full">
                <input type="text" x-model="optOptions.defaultAlt" placeholder="Alt پیش‌فرض برای تصاویر بدون متن جایگزین">
                <button type="button" class="bk-media-btn bk-media-btn-icon" @click="openAiModal('images')" title="ایجاد Alt با هوش مصنوعی">
                    <svg class="solar-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                </button>
            </div>

            <div class="bk-media-actions">
                <button type="button" class="bk-media-btn bk-media-btn-primary" @click="runOptimizeImages()" :disabled="optBusy || !(postImages||[]).length">
                    <span x-text="optBusy ? 'در حال بهینه‌سازی…' : 'اجرای بهینه‌سازی گروهی'"></span>
                </button>
            </div>

            <div class="bk-media-list" x-show="(postImages||[]).length">
                <div class="bk-media-list__head">گزارش تصاویر</div>
                <template x-for="(img, idx) in postImages" :key="img.id || img.src || idx">
                    <div class="bk-media-card">
                        <div class="bk-media-card__thumb">
                            <img :src="img.src || img.original_src" alt="" loading="lazy">
                        </div>
                        <div class="bk-media-card__body">
                            <div class="bk-media-card__meta">
                                <span class="bk-media-io" x-show="img.format_before || img.size_before">
                                    <em>قبل</em>
                                    <span x-text="(img.format_before || '—') + (img.size_before ? (' · ' + img.size_before) : '')"></span>
                                </span>
                                <span class="bk-media-io is-after" x-show="img.format_after || img.size_after || img.format || img.size">
                                    <em>بعد</em>
                                    <span x-text="(img.format_after || img.format || '—') + ((img.size_after || img.size) ? (' · ' + (img.size_after || img.size)) : '')"></span>
                                </span>
                                <span class="bk-media-badge is-wm" x-show="img.watermarked">واترمارک</span>
                                <span class="bk-media-badge is-ok" x-show="img.alt || img.new_alt">Alt</span>
                                <span class="bk-media-badge is-warn" x-show="!(img.alt || img.new_alt)">بدون Alt</span>
                            </div>
                            <input type="text" class="bk-media-alt-input"
                                :value="img.new_alt || img.alt || ''"
                                @change="updateImageAlt(img, $event.target.value)"
                                placeholder="متن جایگزین (Alt)">
                            <div class="bk-media-card__actions">
                                <button type="button" class="bk-media-btn bk-media-btn-sm" @click="watermarkSingleImage(img)" :disabled="optBusy">واترمارک</button>
                                <button type="button" class="bk-media-btn bk-media-btn-sm is-danger" x-show="img.watermarked" @click="removeImageWatermark(img)" :disabled="optBusy">حذف واترمارک</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="bk-media-empty" x-show="!(postImages||[]).length">
                تصویری یافت نشد — مقاله را ذخیره کنید یا آیکن اسکن را بزنید.
            </div>
        </section>

<section x-show="activeTab === 'schema'" class="bk-panel">
            <div class="bk-card">
                <div class="bk-card-head"><span>نوع اسکیما</span></div>
                <select x-model="schemaType" @change="onSchemaTypeChange()">
                    <option value="Article">Article</option>
                    <option value="BlogPosting">BlogPosting</option>
                    <option value="WebPage">WebPage</option>
                    <option value="FAQPage">FAQPage</option>
                    <option value="HowTo">HowTo</option>
                    <option value="Product">Product</option>
                    <option value="Organization">Organization</option>
                </select>
                <p class="bk-hint" style="margin-top:8px">BreadcrumbList به‌صورت خودکار در خروجی صفحه اضافه می‌شود.</p>
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

            <div class="bk-card" x-show="schemaType === 'HowTo'" x-cloak>
                <div class="bk-card-head">
                    <span>مراحل HowTo</span>
                    <button type="button" class="bk-link-btn" @click="howToSteps.push({name:'',text:''})">+ مرحله</button>
                </div>
                <template x-for="(step, i) in howToSteps" :key="'ht'+i">
                    <div class="bk-schema-row">
                        <input type="text" :placeholder="'عنوان مرحله ' + (i+1)" x-model="step.name" @input.debounce.400ms="syncSchemaFromForms()">
                        <textarea rows="2" placeholder="توضیح مرحله" x-model="step.text" @input.debounce.400ms="syncSchemaFromForms()"></textarea>
                        <button type="button" class="bk-icon-action is-danger" @click="howToSteps.splice(i,1); syncSchemaFromForms()" title="حذف">×</button>
                    </div>
                </template>
            </div>

            <div class="bk-card">
                <div class="bk-card-head">
                    <span>JSON-LD</span>
                    <button type="button" class="bk-link-btn" @click="rebuildSchema()">بازنشانی از فرم</button>
                </div>
                <textarea
                    class="bk-code"
                    dir="ltr"
                    rows="12"
                    x-model="seo.schema"
                    @input.debounce.600ms="onFieldChange()"
                ></textarea>
            </div>
        </section>

        <!-- ========== SOCIAL TAB ========== -->
        <section x-show="activeTab === 'social'" class="bk-panel">
            <div class="bk-card">
                <div class="bk-card-head"><span>پیش‌نمایش Open Graph</span></div>
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
                <div class="bk-card-head"><span>Open Graph</span></div>
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

            <div class="bk-card">
                <div class="bk-card-head"><span>پیش‌نمایش X / Twitter</span></div>
                <div class="bk-x-preview">
                    <div class="bk-x-img" x-show="seo.x_image || seo.og_image" :style="'background-image:url(' + (seo.x_image || seo.og_image) + ')'"></div>
                    <strong x-text="seo.x_title || seo.og_title || seo.seo_title || seo.title || 'عنوان'"></strong>
                    <p x-text="seo.x_description || seo.og_description || seo.description || ''"></p>
                </div>
            </div>

            <div class="bk-card">
                <div class="bk-card-head"><span>Twitter / X</span></div>
                <div class="bk-field">
                    <label>عنوان X</label>
                    <input type="text" x-model="seo.x_title" @input.debounce.400ms="onFieldChange()">
                </div>
                <div class="bk-field">
                    <label>توضیحات X</label>
                    <textarea rows="3" x-model="seo.x_description" @input.debounce.400ms="onFieldChange()"></textarea>
                </div>
                <div class="bk-field">
                    <label>تصویر X</label>
                    <div class="bk-input-with-action">
                        <input type="url" dir="ltr" x-model="seo.x_image" @input.debounce.400ms="onFieldChange()" placeholder="https://…">
                        <button type="button" class="bk-btn-ghost bk-btn-sm" @click="pickMedia('x_image')">رسانه</button>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- ========== AI ASSISTANT MODAL (Redesigned Categorized Grid & Wizard Workflow) ========== -->
    <div class="bk-modal-backdrop" x-show="aiOpen" x-cloak @click.self="aiOpen = false" @keydown.escape.window="aiOpen = false" dir="rtl">
        <div class="bk-modal bk-ai-modal" role="dialog" aria-modal="true">
            
            <!-- Modal Header -->
            <header class="bk-modal-head">
                <div class="bk-modal-title">
                    <div class="bk-ai-logo-icon">
                        <svg class="solar-icon bk-purple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                    </div>
                    <div>
                        <strong>دستیار هوشمند سئو و محتوا Bankai</strong>
                        <small>بهینه‌سازی خودکار متا، کلیدواژه‌ها، لینک‌سازی داخلی، سئوی تصاویر و دسته/برچسب</small>
                    </div>
                </div>

                <button type="button" class="bk-icon-btn bk-modal-close-btn" @click="aiOpen = false" title="بستن">
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </header>

            <!-- Phase D: read-only central providers (no local selectors) -->
            <div class="bk-ai-engine-bar bk-ai-engine-readonly">
                <div class="bk-ai-engine-label">موتورهای فعال (تنظیمات مرکزی)</div>
                <div class="bk-ai-central-info" style="display:flex;flex-wrap:wrap;gap:10px;padding:8px 0;">
                    <div class="bk-ai-engine-card is-active" style="cursor:default;pointer-events:none;">
                        <span class="bk-ai-engine-name">متن: <span x-text="centralTextProviderName || 'تنظیم نشده'"></span></span>
                        <span class="bk-ai-engine-status" x-text="centralTextProviderReady ? 'آماده' : 'نیاز به کلید'"></span>
                    </div>
                    <div class="bk-ai-engine-card is-active" style="cursor:default;pointer-events:none;">
                        <span class="bk-ai-engine-name">تصویر: <span x-text="centralImageProviderName || 'تنظیم نشده'"></span></span>
                        <span class="bk-ai-engine-status" x-text="centralImageProviderReady ? 'آماده' : 'نیاز به کلید'"></span>
                    </div>
                </div>
                <p style="margin:0;font-size:11px;color:#64748b;line-height:1.5;">
                    برای تغییر موتور، از <strong>استودیو هوش مصنوعی</strong> در پنل بنکای استفاده کنید. انتخاب در این بخش غیرفعال است.
                </p>
            </div>


            <!-- Modal Body -->
            <div class="bk-modal-body">

                <!-- Progress Bar & Status (Shown when loading or active) -->
                <div class="bk-progress-card" x-show="aiLoading || aiProgress > 0" x-cloak>
                    <div class="bk-progress-head">
                        <span class="bk-progress-text" x-text="aiStepText || 'در حال پردازش عملیات...'"></span>
                        <span class="bk-progress-pct" x-text="aiProgress + '%'"></span>
                    </div>
                    <div class="bk-progress-track">
                        <div class="bk-progress-bar" :style="{ width: aiProgress + '%' }"></div>
                    </div>

                    <!-- Interactive Checklist -->
                    <div class="bk-checklist-grid">
                        <template x-for="step in aiChecklist" :key="step.id">
                            <div class="bk-checklist-item" :class="'is-' + step.status">
                                <div class="bk-chk-icon">
                                    <template x-if="step.status === 'success'">
                                        <svg class="solar-icon bk-text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
                                    </template>
                                    <template x-if="step.status === 'loading'">
                                        <svg class="solar-icon bk-spin bk-text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/></svg>
                                    </template>
                                    <template x-if="step.status === 'pending'">
                                        <svg class="solar-icon bk-text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>
                                    </template>
                                    <template x-if="step.status === 'error'">
                                        <svg class="solar-icon bk-text-error" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                                    </template>
                                </div>
                                <span class="bk-chk-label" x-text="step.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Dashboard / Actions Grid -->
                <div class="bk-actions-dashboard" x-show="!aiReviewOpen">
                    <!-- Hero Auto-Optimize All Card -->
                    <div class="bk-action-card bk-hero-action-card" @click="runAiAction('auto_all')" :class="{ 'is-disabled': aiLoading }" style="margin-bottom:16px;">
                        <div class="bk-action-badge">پیشنهادی 🚀</div>
                        <div class="bk-action-icon">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 16.5c-1.5 1.3-2 3.5-2 3.5s2.2-.5 3.5-2c1-1 1.1-2.3.4-3.1-.8-.7-2.1-.6-3.1.4z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.9A12.4 12.4 0 0 1 22 2c0 2.7-.8 5.6-2.4 7.9A22 22 0 0 1 15 12l-3 3z"/><path d="M9 12H4s.5-1 2-2c1.4-.9 2.5-.5 3 0"/><path d="M12 15v5s1-.5 2-2c.9-1.4.5-2.5 0-3"/></svg>
                        </div>
                        <div class="bk-action-content">
                            <h4>اجرای ویزارد کامل بهینه‌سازی (Auto-Optimize All)</h4>
                            <p>تحلیل یک‌پارچه مقاله، عنوان و متا، لینک‌سازی، Alt تصاویر و تولید کاور با AI.</p>
                        </div>
                        <button type="button" class="bk-btn-primary bk-action-btn" :disabled="aiLoading">
                            <span x-show="!aiLoading">شروع ویزارد کامل</span>
                            <span x-show="aiLoading">در حال پردازش...</span>
                        </button>
                    </div>

                    <!-- CATEGORY 1: Auto Meta & Titles -->
                    <div class="bk-ai-group-section" style="margin-bottom:12px;">
                        <div class="bk-ai-group-header">
                            <span class="bk-ai-group-badge blue">گروه ۱</span>
                            <strong>بهینه‌سازی خودکار و متاها (Meta & Titles)</strong>
                        </div>
                        <div class="bk-quick-cards-grid">
                            <div class="bk-action-card" @click="runAiAction('meta')" :class="{ 'is-disabled': aiLoading }">
                                <div class="bk-action-icon bk-icon-blue">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 7V5h16v2M12 5v14M9 19h6"/></svg>
                                </div>
                                <div class="bk-action-content">
                                    <h4>تولید خودکار متاها</h4>
                                    <p>عنوان سئو جذاب، متادیسکریپشن استاندارد و استخراج کلمات کلیدی.</p>
                                </div>
                                <button type="button" class="bk-btn-ghost bk-action-btn">تولید متا و کلیدواژه</button>
                            </div>
                        </div>
                    </div>

                    <!-- CATEGORY 2: Content AI & Rewriting -->
                    <div class="bk-ai-group-section" style="margin-bottom:12px;">
                        <div class="bk-ai-group-header">
                            <span class="bk-ai-group-badge purple">گروه ۲</span>
                            <strong>محتوا و بازنویسی (Content AI & Rewriting)</strong>
                        </div>
                        <div class="bk-quick-cards-grid">
                            <div class="bk-action-card" @click="runAiAction('rewrite')" :class="{ 'is-disabled': aiLoading }">
                                <div class="bk-action-icon bk-icon-purple">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                </div>
                                <div class="bk-action-content">
                                    <h4>بازنویسی کامل مقاله</h4>
                                    <p>ارتقای لحن و روان‌سازی محتوا با حفظ تگ‌های HTML و ساختار.</p>
                                </div>
                                <button type="button" class="bk-btn-ghost bk-action-btn">بازنویسی با AI</button>
                            </div>

                            <div class="bk-action-card">
                                <div style="font-weight:700;margin-bottom:6px;font-size:12px;">کال‌تو‌اکشن (CTA) در محتوا</div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:6px;">
                                    <input type="url" x-model="cta.url" placeholder="لینک CTA https://..." class="bk-input-sm">
                                    <input type="text" x-model="cta.label" placeholder="متن دکمه" class="bk-input-sm">
                                </div>
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <select x-model="cta.every" class="bk-input-sm" style="width:auto;">
                                        <option value="2">هر ۲ بند</option>
                                        <option value="3">هر ۳ بند</option>
                                    </select>
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="runAiAction('cta_auto')" :disabled="aiLoading">درج خودکار</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CATEGORY 3: Links & Architecture -->
                    <div class="bk-ai-group-section" style="margin-bottom:12px;">
                        <div class="bk-ai-group-header">
                            <span class="bk-ai-group-badge emerald">گروه ۳</span>
                            <strong>لینک‌سازی و ساختار (Links & Architecture)</strong>
                        </div>
                        <div class="bk-quick-cards-grid">
                            <div class="bk-action-card" @click="runAiAction('links')" :class="{ 'is-disabled': aiLoading }">
                                <div class="bk-action-icon bk-icon-emerald">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="2"/><path d="M12 2v4M12 18v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M2 12h4M18 12h4M4.9 19.1l2.8-2.8M16.3 7.7l2.8-2.8"/></svg>
                                </div>
                                <div class="bk-action-content">
                                    <h4>لینک‌سازی هوشمند داخلی</h4>
                                    <p>شناسایی کلمات کلیدی محتوا و پیشنهاد بهترین مقالات مرتبط.</p>
                                </div>
                                <button type="button" class="bk-btn-ghost bk-action-btn">پیشنهاد لینک داخلی</button>
                            </div>

                            <div class="bk-action-card" @click="runAiAction('terms')" :class="{ 'is-disabled': aiLoading }">
                                <div class="bk-action-icon bk-icon-amber">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                </div>
                                <div class="bk-action-content">
                                    <h4>تولید دسته و برچسب</h4>
                                    <p>استخراج یا ایجاد دسته‌بندی و برچسب‌های سئو مقاله.</p>
                                </div>
                                <button type="button" class="bk-btn-ghost bk-action-btn">اجرا</button>
                            </div>
                        </div>
                    </div>

                    <!-- CATEGORY 4: Images & Media AI -->
                    <div class="bk-ai-group-section">
                        <div class="bk-ai-group-header">
                            <span class="bk-ai-group-badge amber">گروه ۴</span>
                            <strong>سئوی تصاویر و رسانه (Images & Media AI)</strong>
                        </div>
                        <div class="bk-quick-cards-grid">
                            <div class="bk-action-card" @click="runAiAction('images')" :class="{ 'is-disabled': aiLoading }">
                                <div class="bk-action-icon bk-icon-blue">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M21 16l-5-5-4 4-2-2-5 5"/></svg>
                                </div>
                                <div class="bk-action-content">
                                    <h4>سئوی تصاویر مقاله</h4>
                                    <p>شناسایی تصاویر فاقد Alt و تولید متن جایگزین سئوشده.</p>
                                </div>
                                <button type="button" class="bk-btn-ghost bk-action-btn">تولید Alt</button>
                            </div>

                            <div class="bk-action-card" @click="runAiAction('cover_image')" :class="{ 'is-disabled': aiLoading }">
                                <div class="bk-action-icon bk-icon-purple">
                                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M21 16l-5-5-4 4-2-2-5 5"/></svg>
                                </div>
                                <div class="bk-action-content">
                                    <h4>تولید کاور با AI</h4>
                                    <p>ساخت تصویر شاخص از عنوان و مقدمه مقاله.</p>
                                </div>
                                <button type="button" class="bk-btn-ghost bk-action-btn">اجرا</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review & Approve Summary View (خلاصه تغییرات جهت تایید نهایی) -->
                <div class="bk-review-summary-card" x-show="aiReviewOpen" x-cloak>
                    <div class="bk-review-head">
                        <div>
                            <h3>خلاصه نتایج و تغییرات هوش مصنوعی (Review & Approve)</h3>
                            <p>تغییرات مورد نظر را بررسی و تایید کنید تا روی مقاله و متاداده‌ها اعمال شوند.</p>
                        </div>
                        <button type="button" class="bk-btn-ghost bk-btn-sm" @click="aiReviewOpen = false">بازگشت به منوی اکشن‌ها</button>
                    </div>

                    <div class="bk-review-list">
                        
                        <!-- Meta Review -->
                        <div class="bk-review-item" x-show="aiDraft.title || aiDraft.description">
                            <label class="bk-review-label">
                                <input type="checkbox" x-model="aiReviewSelection.applyMeta">
                                <strong>عنوان سئو و متا دیسکریپشن</strong>
                            </label>
                            <div class="bk-review-box" x-show="aiReviewSelection.applyMeta">
                                <div class="bk-review-field">
                                    <small>عنوان سئو پیشنهادی:</small>
                                    <input type="text" x-model="aiDraft.title" class="bk-input-sm">
                                </div>
                                <div class="bk-review-field" style="margin-top:8px;">
                                    <small>متادیسکریپشن پیشنهادی:</small>
                                    <textarea rows="2" x-model="aiDraft.description" class="bk-input-sm" @input="metaValidationStatus=''; metaValidationMessage=''; if (typeof analyze==='function') analyze(false)"></textarea>
                                </div>
                                <div class="bk-meta-validation" style="margin-top:10px;padding:10px;border-radius:10px;background:rgba(15,23,42,.04);font-size:12px;" x-show="metaValidationStatus || metaGenerationStatus">
                                    <div><strong>وضعیت تولید:</strong>
                                        <span x-text="metaGenerationStatus==='success' ? 'تولید موفق شد' : (metaGenerationStatus==='error' ? 'خطای تولید' : (metaGenerationStatus || '—'))"></span>
                                    </div>
                                    <div style="margin-top:4px;"><strong>وضعیت اعتبارسنجی سئو:</strong>
                                        <span x-text="metaValidationStatus==='passed' ? 'اعتبارسنجی موفق شد' : (metaValidationStatus==='needs_review' ? 'نیازمند بازبینی' : (metaValidationStatus==='failed' ? 'ناموفق' : '—'))"></span>
                                    </div>
                                    <div style="margin-top:4px;color:#64748b;" x-show="metaValidationMessage" x-text="metaValidationMessage"></div>
                                    <template x-if="metaFailedChecks && metaFailedChecks.length">
                                        <ul style="margin:6px 0 0;padding-inline-start:18px;color:#b45309;">
                                            <template x-for="fc in metaFailedChecks" :key="fc.key">
                                                <li><span x-text="fc.label || fc.key"></span>: <span x-text="fc.message || fc.status"></span></li>
                                            </template>
                                        </ul>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Keywords Review -->
                        <div class="bk-review-item" x-show="aiDraft.keywords && aiDraft.keywords.length">
                            <label class="bk-review-label">
                                <input type="checkbox" x-model="aiReviewSelection.applyKeywords">
                                <strong>کلمات کلیدی پیشنهادی (<span x-text="aiDraft.keywords.length"></span> مورد)</strong>
                            </label>
                            <div class="bk-review-box" x-show="aiReviewSelection.applyKeywords">
                                <div class="bk-chips-flex">
                                    <template x-for="kw in aiDraft.keywords" :key="kw">
                                        <span class="bk-chip" :class="{ 'is-fixed': isFixedKeyword(kw) }">
                                            <span x-show="isFixedKeyword(kw)" title="کلمه کلیدی ثابت سایت">📌 </span>
                                            <span x-text="kw"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Smart Links Review -->
                        <div class="bk-review-item" x-show="aiDraft.smartLinks && aiDraft.smartLinks.length">
                            <label class="bk-review-label">
                                <input type="checkbox" x-model="aiReviewSelection.applyLinks">
                                <strong>لینک‌های داخلی هوشمند پیشنهادی (<span x-text="aiDraft.smartLinks.length"></span> مورد)</strong>
                            </label>
                            <div class="bk-review-box" x-show="aiReviewSelection.applyLinks">
                                <template x-for="l in aiDraft.smartLinks" :key="l.target_url">
                                    <div class="bk-review-subitem">
                                        <div>
                                             انکر: <strong x-text="l.keyword"></strong> ➔ مقصد: <strong x-text="l.target_title"></strong>
                                            <div class="bk-hint" x-text="l.reason"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Alt Text Review -->
                        <div class="bk-review-item" x-show="aiDraft.altTexts && aiDraft.altTexts.length">
                            <label class="bk-review-label">
                                <input type="checkbox" x-model="aiReviewSelection.applyImages">
                                <strong>متن‌های جایگزین (Alt Text) تصاویر مقاله (<span x-text="aiDraft.altTexts.length"></span> تصویر)</strong>
                            </label>
                            <div class="bk-review-box" x-show="aiReviewSelection.applyImages">
                                <template x-for="img in aiDraft.altTexts" :key="img.src">
                                    <div class="bk-review-field" style="margin-bottom:8px;">
                                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                            <img :src="img.src" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">
                                            <small dir="ltr" style="font-size:10px;color:#64748B;" x-text="img.src.split('/').pop()"></small>
                                        </div>
                                        <input type="text" x-model="img.suggestedAlt" class="bk-input-sm" placeholder="متن جایگزین...">
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Rewrite Review -->
                        <div class="bk-review-item" x-show="aiDraft.rewrite">
                            <label class="bk-review-label">
                                <input type="checkbox" x-model="aiReviewSelection.applyRewrite">
                                <strong>جایگزینی متن بازنویسی‌شده در مقاله</strong>
                            </label>
                            <div class="bk-review-box" x-show="aiReviewSelection.applyRewrite">
                                <textarea rows="6" x-model="aiDraft.rewrite" class="bk-input-sm"></textarea>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Actions -->
                    <div class="bk-modal-actions" style="margin-top:20px;justify-content:flex-end;">
                        <button type="button" class="bk-btn-ghost" @click="aiReviewOpen = false">ویرایش مجدد</button>
                        <button type="button" class="bk-btn-primary" @click="applyApprovedAiChanges()">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
                            اعمال تغییرات تاییدشده
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Toast -->
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
        <span class="bk-toast-bar" :style="'--toast-ms:' + (toast.duration || 3600) + 'ms'"></span>
    </div>
</div>
