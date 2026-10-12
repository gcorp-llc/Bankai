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
            <div class="bk-header-info">
                <div class="bk-header-title">
                    <strong>سئوی بنکای</strong>
                    <span class="bk-ver-badge">v2.0</span>
                </div>
                <!-- Sub-score badges for SEO / AEO / GEO -->
                <div class="bk-subscores-pills">
                    <span class="bk-subscore-pill" title="امتیاز سئوی کلاسیک">
                        <em class="bk-dot bk-dot-seo"></em>
                        SEO: <strong x-text="(analysis.seo_score ?? analysis.score) + '%'"></strong>
                    </span>
                    <span class="bk-subscore-pill" title="امتیاز بهینه‌سازی موتورهای پاسخ">
                        <em class="bk-dot bk-dot-aeo"></em>
                        AEO: <strong x-text="(analysis.aeo_score ?? analysis.score) + '%'"></strong>
                    </span>
                    <span class="bk-subscore-pill" title="امتیاز بهینه‌سازی هوش مصنوعی">
                        <em class="bk-dot bk-dot-geo"></em>
                        GEO: <strong x-text="(analysis.geo_score ?? analysis.score) + '%'"></strong>
                    </span>
                </div>
            </div>
            <button type="button" class="bk-icon-btn" @click="analyze(true)" :disabled="loading" title="تازه‌سازی و آنالیز مجدد">
                <svg class="solar-icon" :class="{ 'bk-spin': loading }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/></svg>
            </button>
        </div>

        <!-- TABS NAV -->
        <nav class="bk-tabs bk-tabs-scroll">
            <button type="button" :class="{ 'is-active': activeTab === 'seo' }" @click="activeTab = 'seo'">سئو</button>
            <button type="button" :class="{ 'is-active': activeTab === 'checklist' }" @click="activeTab = 'checklist'">چک‌لیست</button>
            <button type="button" :class="{ 'is-active': activeTab === 'aeogeo' }" @click="activeTab = 'aeogeo'">AEO & GEO</button>
            <button type="button" :class="{ 'is-active': activeTab === 'links' }" @click="activeTab = 'links'; loadLinks()">لینک‌ها</button>
            <button type="button" :class="{ 'is-active': activeTab === 'media' }" @click="activeTab = 'media'; scanPostImages()">تصاویر</button>
            <button type="button" :class="{ 'is-active': activeTab === 'schema' }" @click="activeTab = 'schema'">اسکیما</button>
            <button type="button" :class="{ 'is-active': activeTab === 'social' }" @click="activeTab = 'social'">سوشال</button>
            <button type="button" class="bk-tab-ai" :class="{ 'is-active': activeTab === 'ai' }" @click="openAiModal()">
                دستیار AI
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
            </button>
        </nav>
    </header>

    <!-- BODY CONTENT -->
    <div class="bk-body">

        <!-- ========== 1. SEO TAB ========== -->
        <section x-show="activeTab === 'seo'" class="bk-panel">

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

            <!-- SYNC FROM RANK MATH / YOAST -->
            <div class="bk-card" style="padding:10px 12px;">
                <button type="button"
                    class="bk-btn-primary bk-btn-sm bk-sync-legacy-btn"
                    style="width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;"
                    :class="{ 'is-loading': legacySyncBusy, 'is-done': legacySyncDone }"
                    @click="syncFromLegacySeo()"
                    :disabled="legacySyncBusy">
                    <svg x-show="legacySyncBusy" class="solar-icon bk-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/></svg>
                    <svg x-show="!legacySyncBusy && legacySyncDone" class="solar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    <svg x-show="!legacySyncBusy && !legacySyncDone" class="solar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 16v-5M12 16V8M17 16v-3"/></svg>
                    <span x-text="legacySyncBusy ? 'در حال همگام‌سازی…' : (legacySyncDone ? 'همگام‌سازی انجام شد' : 'همگام‌سازی از سایر افزونه‌ها')"></span>
                </button>
                <p class="bk-hint" style="margin:6px 0 0;font-size:11px;">همگام‌سازی یک‌کلیکه متا از Rank Math / Yoast در صورت وجود داده قبلی.</p>
            </div>

            <!-- KEYWORDS SECTION -->
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


        <!-- ========== 2. CHECKLIST TAB ========== -->
        <section x-show="activeTab === 'checklist'" class="bk-panel">
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>چک‌لیست آنالیز سئو و هوش مصنوعی</span>
                    <span class="bk-badge" :style="'background:' + scoreColor + '22;color:' + scoreColor + ';border-color:' + scoreColor">
                        <span x-text="analysis.passed"></span>/<span x-text="analysis.total"></span> موارد تایید شده
                    </span>
                </div>

                <!-- FILTER CHIPS -->
                <div class="bk-filter-chips">
                    <button type="button" class="bk-fchip" :class="{ 'is-active': checklistFilter === 'all' }" @click="checklistFilter = 'all'">همه (<span x-text="allChecksList().length"></span>)</button>
                    <button type="button" class="bk-fchip" :class="{ 'is-active': checklistFilter === 'seo' }" @click="checklistFilter = 'seo'">سئو (<span x-text="filterChecksByPillar('seo').length"></span>)</button>
                    <button type="button" class="bk-fchip" :class="{ 'is-active': checklistFilter === 'aeo' }" @click="checklistFilter = 'aeo'">AEO (<span x-text="filterChecksByPillar('aeo').length"></span>)</button>
                    <button type="button" class="bk-fchip" :class="{ 'is-active': checklistFilter === 'geo' }" @click="checklistFilter = 'geo'">GEO (<span x-text="filterChecksByPillar('geo').length"></span>)</button>
                    <button type="button" class="bk-fchip bk-fchip-warn" :class="{ 'is-active': checklistFilter === 'failed' }" @click="checklistFilter = 'failed'">خطادار (<span x-text="failedChecksList().length"></span>)</button>
                    <button type="button" class="bk-fchip bk-fchip-ok" :class="{ 'is-active': checklistFilter === 'passed' }" @click="checklistFilter = 'passed'">پاس‌شده (<span x-text="passedChecksList().length"></span>)</button>
                </div>

                <!-- CHECKLIST ITEMS CARDS -->
                <div class="bk-checklist-items">
                    <template x-for="check in filteredChecklistItems()" :key="check.key || check.id">
                        <div class="bk-check-card" :class="checkStatusClass(check)">
                            <div class="bk-check-head-row">
                                <div class="bk-check-title-group">
                                    <template x-if="check.passed">
                                        <span class="bk-check-icon is-pass">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                                        </span>
                                    </template>
                                    <template x-if="!check.passed">
                                        <span class="bk-check-icon is-fail">
                                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                                        </span>
                                    </template>
                                    <strong class="bk-check-label" x-text="check.label"></strong>
                                </div>
                                <div class="bk-check-badges">
                                    <span class="bk-pillar-badge" x-text="(check.group === 'aeo' ? 'AEO' : (check.group === 'geo' ? 'GEO' : 'SEO'))"></span>
                                    <span class="bk-sev-badge" :class="'sev-' + (check.severity || 'medium')" x-text="check.severity === 'high' ? 'ضروری' : (check.severity === 'low' ? 'پیشنهاد' : 'مهم')"></span>
                                </div>
                            </div>

                            <p class="bk-check-msg" x-text="check.message"></p>

                            <template x-if="check.recommendation && !check.passed">
                                <div class="bk-check-rec">
                                    <strong>راهکار بهبود:</strong> <span x-text="check.recommendation"></span>
                                </div>
                            </template>

                            <!-- 1-CLICK ACTION BUTTONS -->
                            <div class="bk-check-actions" x-show="!check.passed">
                                <template x-if="check.key === 'short_paragraphs'">
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="autoSplitLongParagraphs()">
                                        کوتاه‌سازی خودکار پاراگراف‌ها
                                    </button>
                                </template>
                                <template x-if="check.key === 'aeo_faq_structure'">
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="generateAeoFaq()">
                                        تولید بخش FAQ با AI
                                    </button>
                                </template>
                                <template x-if="check.key === 'geo_summary_conclusion'">
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="generateGeoSummary()">
                                        تولید خلاصه نکات کلیدی با AI
                                    </button>
                                </template>
                                <template x-if="check.key === 'kw_in_title' || check.key === 'title_length'">
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="openAiModal('title')">
                                        تولید عنوان سئو با AI
                                    </button>
                                </template>
                                <template x-if="check.key === 'kw_in_desc' || check.key === 'desc_length'">
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="openAiModal('description')">
                                        تولید متا دیسکریپشن با AI
                                    </button>
                                </template>
                                <template x-if="check.key === 'kw_in_alt'">
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="activeTab='media'; scanPostImages()">
                                        تنظیم Alt تصاویر
                                    </button>
                                </template>
                                <template x-if="check.key === 'internal_links'">
                                    <button type="button" class="bk-btn-primary bk-btn-xs" @click="activeTab='links'; loadLinks()">
                                        افزودن لینک داخلی
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>


        <!-- ========== 3. AEO & GEO TAB ========== -->
        <section x-show="activeTab === 'aeogeo'" class="bk-panel">

            <!-- AEO READINESS CARD -->
            <div class="bk-card bk-aeo-card">
                <div class="bk-card-head">
                    <span class="bk-pill-tag bk-tag-aeo">AEO</span>
                    <span>بهینه‌سازی موتورهای پاسخ (Voice & Direct Answers)</span>
                    <span class="bk-score-badge" x-text="(analysis.aeo_score ?? analysis.score) + '%'"></span>
                </div>
                <p class="bk-card-desc">موتورهای پاسخ مانند Google Assistant، Siri و Featured Snippets پاسخ‌های کوتاه، صریح و ساختاریافته را ترجیح می‌دهند.</p>

                <div class="bk-aeo-tools">
                    <button type="button" class="bk-btn-primary bk-btn-sm bk-full" @click="generateAeoFaq()" :disabled="aiBusy">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        تولید خودکار بخش سوالات متداول (FAQ) با AI
                    </button>
                </div>
            </div>

            <!-- GEO READINESS CARD -->
            <div class="bk-card bk-geo-card">
                <div class="bk-card-head">
                    <span class="bk-pill-tag bk-tag-geo">GEO</span>
                    <span>بهینه‌سازی هوش مصنوعی (ChatGPT, Perplexity, AI Overview)</span>
                    <span class="bk-score-badge" x-text="(analysis.geo_score ?? analysis.score) + '%'"></span>
                </div>
                <p class="bk-card-desc">موتورهای جستجوی مولد مطالب دارای استناد به منبع، خلاصه نکات کلیدی، ساختار مقایسه‌ای و داده‌های ساختاریافته شفاف را نقل‌قول می‌کنند.</p>

                <div class="bk-geo-tools">
                    <button type="button" class="bk-btn-primary bk-btn-sm bk-full" @click="generateGeoSummary()" :disabled="aiBusy">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        تولید خلاصه نکات کلیدی (TL;DR) با AI
                    </button>
                </div>
            </div>

            <!-- AEO/GEO CHECKLIST HIGHLIGHTS -->
            <div class="bk-card">
                <div class="bk-card-head"><span>معیارهای آمادگی هوش مصنوعی</span></div>
                <div class="bk-checklist-items">
                    <template x-for="check in [...filterChecksByPillar('aeo'), ...filterChecksByPillar('geo')]" :key="check.key">
                        <div class="bk-check-card" :class="checkStatusClass(check)">
                            <div class="bk-check-head-row">
                                <strong><span x-text="check.label"></span></strong>
                                <span class="bk-pillar-badge" x-text="check.group.toUpperCase()"></span>
                            </div>
                            <p class="bk-check-msg" x-text="check.message"></p>
                        </div>
                    </template>
                </div>
            </div>
        </section>


        <!-- ========== 4. LINKS TAB ========== -->
        <section x-show="activeTab === 'links'" class="bk-panel">
            
            <div class="bk-card">
                <div class="bk-card-head"><span>آمار لینک‌های مقاله</span></div>
                <div class="bk-link-stats">
                    <div class="bk-stat">
                        <strong x-text="linkData.counts?.internal ?? 0">0</strong>
                        <small>لینک داخلی</small>
                    </div>
                    <div class="bk-stat">
                        <strong x-text="linkData.counts?.external ?? 0">0</strong>
                        <small>لینک خارجی</small>
                    </div>
                </div>
            </div>

            <!-- INTERNAL LINK SUGGESTIONS -->
            <div class="bk-card bk-link-builder-card">
                <div class="bk-card-head">
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    <span>پیشنهاد لینک داخلی هوشمند</span>
                    <button type="button" class="bk-btn-ghost bk-btn-ai bk-btn-xs" style="margin-inline-start:auto" @click="loadSmartLinkSuggestions()" :disabled="linkBusy">
                        پیشنهاد هوشمند AI
                    </button>
                </div>

                <div class="bk-search-combo bk-search-combo-inline">
                    <input type="text" x-model="linkAnchor" placeholder="کلیدواژه / انکر تکست…" @keydown.enter.prevent="runInternalSearch()">
                    <button type="button" class="bk-search-icon-btn" @click="runInternalSearch()" :disabled="linkBusy" title="جستجو">
                        <svg class="solar-icon" :class="{ 'bk-spin': linkBusy }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    </button>
                </div>

                <!-- Suggested list -->
                <div class="bk-match-section" x-show="autoLinkSuggestions.length" x-cloak>
                    <div class="bk-card-head"><span>پیشنهادهای هوشمند AI</span></div>
                    <template x-for="(s, linkIdx) in autoLinkSuggestions" :key="s.target_post_id || s.id || linkIdx">
                        <div class="bk-search-result-item bk-link-card-stack">
                            <div class="bk-link-title-only">
                                <strong x-text="s.target_title || s.title"></strong>
                                <small class="bk-hint" x-text="'انکر پیشنهادی: ' + (s.suggested_anchor || s.keyword)"></small>
                                <small class="bk-hint" x-show="s.reason || (typeof link !== 'undefined' && link.reason)" x-text="s.reason || (typeof link !== 'undefined' ? link.reason : '')"></small>
                                <span class="bk-score-tag" x-show="s.score || (typeof link !== 'undefined' && link.score)" x-text="'امتیاز: ' + (s.score || (typeof link !== 'undefined' ? link.score : ''))"></span>
                            </div>
                            <div class="bk-pending-actions bk-link-actions-below">
                                <button type="button" class="bk-btn-primary bk-btn-xs" @click="insertSuggestedLink(s)">
                                    افزودن لینک به متن
                                </button>
                                <a class="bk-btn-ghost bk-btn-xs" :href="s.permalink || s.target_url" target="_blank" rel="noopener">نمایش</a>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Manual search results -->
                <div class="bk-match-section" x-show="postResults.length" x-cloak>
                    <div class="bk-card-head"><span>مقالات مرتبط یافت‌شده</span></div>
                    <template x-for="p in postResults" :key="p.id">
                        <div class="bk-search-result-item bk-article-pick bk-link-card-stack" :class="{ 'is-accepted': p.accepted, 'is-rejected': p.rejected }">
                            <div class="bk-link-title-only" x-text="p.title" :title="p.title"></div>
                            <div class="bk-pending-actions bk-link-actions-below">
                                <button type="button" class="bk-btn-primary bk-btn-xs" @click="p.accepted=true; p.rejected=false; loadInternalContentMatches()">تایید</button>
                                <a class="bk-btn-ghost bk-btn-xs" :href="p.permalink" target="_blank" rel="noopener">نمایش</a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- EXISTING LINKS LIST -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>لینک‌های موجود در مقاله</span>
                    <button type="button" class="bk-link-btn" @click="loadLinks()" title="تازه‌سازی">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/></svg>
                    </button>
                </div>
                <template x-if="!((linkData.internal?.length || 0) + (linkData.external?.length || 0))">
                    <div class="bk-empty-state">هنوز لینکی در متن مقاله ثبت نشده است.</div>
                </template>
                <template x-for="(l, idx) in (linkData.internal || [])" :key="'li'+idx+l.href">
                    <div class="bk-link-item bk-link-item-row">
                        <span class="bk-tag internal">داخلی</span>
                        <div class="bk-link-item-info">
                            <strong x-text="l.text || 'بدون انکر'"></strong>
                            <small dir="ltr" x-text="l.href"></small>
                        </div>
                    </div>
                </template>
                <template x-for="(l, idx) in (linkData.external || [])" :key="'le'+idx+l.href">
                    <div class="bk-link-item bk-link-item-row">
                        <span class="bk-tag external">خارجی</span>
                        <div class="bk-link-item-info">
                            <strong x-text="l.text || 'بدون انکر'"></strong>
                            <small dir="ltr" x-text="l.href"></small>
                        </div>
                    </div>
                </template>
            </div>
        </section>


        <!-- ========== 5. MEDIA / IMAGES TAB ========== -->
        <section x-show="activeTab === 'media'" class="bk-panel">
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>تصاویر مقاله</span>
                    <button type="button" class="bk-link-btn" @click="scanPostImages()">اسکن مجدد</button>
                </div>

                <div class="bk-media-list" x-show="(postImages||[]).length">
                    <template x-for="(img, idx) in postImages" :key="img.id || img.src || idx">
                        <div class="bk-media-card">
                            <div class="bk-media-card__thumb">
                                <img :src="img.src || img.original_src" alt="" loading="lazy">
                            </div>
                            <div class="bk-media-card__body">
                                <div class="bk-media-card__meta">
                                    <span class="bk-media-badge is-ok" x-show="img.alt || img.new_alt">Alt دارد</span>
                                    <span class="bk-media-badge is-warn" x-show="!(img.alt || img.new_alt)">بدون Alt</span>
                                </div>
                                <input type="text" class="bk-media-alt-input"
                                    :value="img.new_alt || img.alt || ''"
                                    @change="updateImageAlt(img, $event.target.value)"
                                    placeholder="متن جایگزین (Alt)">
                                <button type="button" class="bk-btn-ghost bk-btn-xs" style="margin-top:6px" @click="generateSingleImageAlt(img)" :disabled="aiBusy">
                                    تولید Alt با AI
                                </button>
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
        <section x-show="activeTab === 'schema'" class="bk-panel">
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
        <section x-show="activeTab === 'social'" class="bk-panel">
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


        <!-- ========== 8. AI ASSISTANT TAB / MODAL ========== -->
        <section x-show="activeTab === 'ai'" class="bk-panel bk-ai-engine-readonly">
            <div class="bk-card">
                <div class="bk-card-head">
                    <svg class="solar-icon bk-purple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                    <span>دستیار هوشمند سئوی بنکای</span>
                </div>
                <p class="bk-card-desc">تنظیمات مرکزی هوش مصنوعی از طریق پنل مدیریت بنکای اعمال می‌شوند. تولید سریع و هوشمند عناصر مقاله بر اساس محتوای متنی و کلیدواژه‌ها:</p>

                <div class="bk-ai-quick-actions">
                    <button type="button" class="bk-btn-ghost bk-full" @click="openAiModal('title')">تولید عنوان سئو (Title Tag)</button>
                    <button type="button" class="bk-btn-ghost bk-full" @click="openAiModal('description')">تولید متا دیسکریپشن</button>
                    <button type="button" class="bk-btn-ghost bk-full" @click="openAiModal('focus_keyword')">استخراج کلمه کلیدی اصلی</button>
                    <button type="button" class="bk-btn-ghost bk-full" @click="openAiModal('keywords')">پیشنهاد کلیدواژه‌های فرعی</button>
                    <button type="button" class="bk-btn-ghost bk-full" @click="generateAeoFaq()">تولید بخش سوالات متداول (AEO FAQ)</button>
                    <button type="button" class="bk-btn-ghost bk-full" @click="generateGeoSummary()">تولید خلاصه نکات کلیدی (GEO Summary)</button>
                </div>
            </div>
        </section>

    </div>

    <!-- AI MODAL -->
    <div class="bk-modal-backdrop" x-show="aiModalOpen" x-cloak @keydown.escape.window="aiModalOpen = false">
        <div class="bk-modal" @click.away="aiModalOpen = false">
            <div class="bk-modal-head">
                <strong x-text="aiModalTitle || 'دستیار AI سئو'"></strong>
                <button type="button" class="bk-icon-action" @click="aiModalOpen = false">×</button>
            </div>
            <div class="bk-modal-body">
                <div class="bk-field" x-show="aiTask === 'title' || aiTask === 'description' || aiTask === 'rewrite'">
                    <label>سبک بازنویسی / تولید</label>
                    <select x-model="aiOptions.style">
                        <option value="seo">سئو استاندارد (پیش‌فرض)</option>
                        <option value="engaging">جذاب و کلیک‌خور (High CTR)</option>
                        <option value="formal">رسمی و حرفه‌ای</option>
                        <option value="simple">ساده و روان</option>
                    </select>
                </div>

                <button type="button" class="bk-btn-primary bk-full" @click="runAiGeneration()" :disabled="aiBusy">
                    <span x-text="aiBusy ? 'در حال تولید با AI…' : 'شروع تولید'"></span>
                </button>
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
