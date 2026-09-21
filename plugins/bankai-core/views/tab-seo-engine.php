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
                <strong>سئوی بنکای</strong>
            </div>
            <button type="button" class="bk-icon-btn" @click="analyze(true)" :disabled="loading" title="تازه‌سازی">
                <span class="material-symbols-outlined" :class="{ 'bk-spin': loading }">refresh</span>
            </button>
        </div>

        <!-- TABS -->
        <nav class="bk-tabs">
            <button type="button" :class="{ 'is-active': activeTab === 'seo' }" @click="activeTab = 'seo'">سئو</button>
            <button type="button" :class="{ 'is-active': activeTab === 'links' }" @click="activeTab = 'links'; loadLinks()">لینک‌ها</button>
            <button type="button" :class="{ 'is-active': activeTab === 'schema' }" @click="activeTab = 'schema'">اسکیما</button>
            <button type="button" :class="{ 'is-active': activeTab === 'social' }" @click="activeTab = 'social'">سوشال</button>
            <button type="button" class="bk-tab-ai" :class="{ 'is-active': activeTab === 'ai' }" @click="openAiModal()">
                AI <span class="material-symbols-outlined">auto_awesome</span>
            </button>
        </nav>
    </header>

    <!-- BODY (no inner scroll — page scrolls) -->
    <div class="bk-body">

        <!-- ========== SEO TAB ========== -->
        <section x-show="activeTab === 'seo'" x-cloak class="bk-panel">

            <!-- SERP -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span class="material-symbols-outlined bk-blue">search</span>
                    <span>پیش‌نمایش SERP</span>
                    <div class="bk-seg">
                        <button type="button" :class="{ 'is-on': !serpMobile }" @click="serpMobile = false">
                            <span class="material-symbols-outlined">desktop_windows</span>
                        </button>
                        <button type="button" :class="{ 'is-on': serpMobile }" @click="serpMobile = true">
                            <span class="material-symbols-outlined">smartphone</span>
                        </button>
                    </div>
                </div>
                <div class="bk-serp" :class="{ 'is-mobile': serpMobile }">
                    <div class="bk-serp-url" dir="ltr">
                        <span class="bk-fav">B</span>
                        <span x-text="seo.permalink || '…'"></span>
                    </div>
                    <div class="bk-serp-title" x-text="seo.seo_title || seo.title"></div>
                    <div class="bk-serp-desc" x-text="seo.description"></div>
                </div>
                <div class="bk-serp-meta">
                    <span :class="titlePxOk ? 'ok' : 'warn'">
                        <i></i>
                        طول پیکسل عنوان: <span x-text="titlePx"></span>px
                    </span>
                </div>
            </div>

            <!-- KEYWORDS (Rank Math style) -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>کلیدواژه‌ها</span>
                    <button type="button" class="bk-link-btn" @click="openAiModal('keywords')">
                        <span class="material-symbols-outlined">auto_awesome</span>
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
                                <span class="material-symbols-outlined">close</span>
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
                            <span class="material-symbols-outlined">auto_fix_high</span>
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
                            <span class="material-symbols-outlined">auto_awesome</span>
                            AI
                        </button>
                    </div>
                </div>
                <textarea x-model="seo.description" @input.debounce.400ms="onFieldChange()" maxlength="170" rows="3"></textarea>
                <div class="bk-bar"><div :style="'width:' + descBarPct + '%;background:' + descBarColor"></div></div>
            </div>

            <!-- ANALYSIS -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>تحلیل سئو</span>
                    <span class="bk-badge" :style="'background:' + scoreColor + '22;color:' + scoreColor + ';border-color:' + scoreColor">
                        <span x-text="analysis.passed"></span>/<span x-text="analysis.total"></span>
                    </span>
                </div>

                <template x-for="(group, gkey) in analysis.groups" :key="gkey">
                    <div class="bk-acc" x-show="group.items && group.items.length">
                        <button type="button" class="bk-acc-head" @click="toggleGroup(gkey)">
                            <span class="material-symbols-outlined" :class="groupPassed(group) ? 'ok' : 'warn'"
                                x-text="groupPassed(group) ? 'check_circle' : 'warning'"></span>
                            <span x-text="group.label"></span>
                            <span class="bk-acc-count" x-text="groupPassedCount(group) + '/' + group.items.length"></span>
                            <span class="material-symbols-outlined bk-chevron"
                                x-text="openGroups[gkey] ? 'expand_less' : 'expand_more'"></span>
                        </button>
                        <div class="bk-acc-body" x-show="openGroups[gkey]" x-collapse>
                            <template x-for="check in group.items" :key="check.key">
                                <div class="bk-check" :class="check.passed ? 'is-ok' : 'is-warn'">
                                    <span class="material-symbols-outlined" x-text="check.passed ? 'done' : 'priority_high'"></span>
                                    <div>
                                        <strong x-text="check.label"></strong>
                                        <small x-text="check.message"></small>
                                    </div>
                                    <span class="bk-check-status" x-text="check.passed ? 'تایید' : 'توجه'"></span>
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
                <div class="bk-field" style="margin-top:8px">
                    <label>Canonical URL</label>
                    <input type="url" dir="ltr" x-model="seo.canonical" @input.debounce.500ms="onFieldChange()">
                </div>
            </div>
        </section>

        <!-- ========== LINKS TAB ========== -->
        <section x-show="activeTab === 'links'" x-cloak class="bk-panel">
            <div class="bk-card">
                <div class="bk-card-head"><span>آمار لینک‌های فعلی مقاله</span></div>
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
                <button type="button" class="bk-btn-ghost bk-full" style="margin-top:8px" @click="loadLinks()">
                    تازه‌سازی آمار از محتوا
                </button>
            </div>

            <!-- Internal -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span class="material-symbols-outlined bk-blue">hub</span>
                    <span>لینک‌ساز داخلی</span>
                </div>
                <p class="bk-hint">کلمه/عبارت را بنویسید → مقالات مرتبط جستجو می‌شوند. سپس محل‌های همان عبارت در متن مقاله پیدا و برای لینک‌شدن پیشنهاد می‌شوند.</p>
                <div class="bk-row">
                    <input type="text" x-model="linkAnchor" placeholder="کلیدواژه یا انکر تکست" @keydown.enter.prevent="runInternalSearch()">
                    <button type="button" class="bk-btn-primary" @click="runInternalSearch()" :disabled="linkBusy">
                        <span x-show="!linkBusy">جستجو</span>
                        <span x-show="linkBusy">…</span>
                    </button>
                </div>

                <div class="bk-alert" x-show="internalReport.show" x-cloak :class="'is-' + internalReport.type">
                    <span x-text="internalReport.message"></span>
                </div>

                <ul class="bk-search-list" x-show="postResults.length">
                    <template x-for="p in postResults" :key="p.id">
                        <li>
                            <div>
                                <strong x-text="p.title"></strong>
                                <small dir="ltr" x-text="p.permalink"></small>
                            </div>
                            <button type="button" class="bk-btn-sm" @click="scanInternalMatches(p)">
                                یافتن در متن
                            </button>
                        </li>
                    </template>
                </ul>

                <div class="bk-match-box" x-show="internalMatches.length" x-cloak>
                    <div class="bk-card-head" style="margin-top:10px">
                        <span>موقعیت‌های یافت‌شده در محتوا</span>
                        <span class="bk-badge" x-text="internalMatches.length + ' مورد'"></span>
                    </div>
                    <p class="bk-hint" x-show="selectedInternalPost">
                        مقصد: <strong x-text="selectedInternalPost?.title"></strong>
                    </p>
                    <template x-for="(m, idx) in internalMatches" :key="'im'+idx">
                        <label class="bk-match-row">
                            <input type="checkbox" x-model="m.selected">
                            <div>
                                <small class="bk-ctx" x-html="m.contextHtml"></small>
                                <span class="bk-pos">موقعیت کاراکتر: <span x-text="m.index"></span></span>
                            </div>
                        </label>
                    </template>
                    <div class="bk-row" style="margin-top:8px">
                        <button type="button" class="bk-btn-primary" @click="applyInternalLinks()" :disabled="!internalMatches.some(m => m.selected)">
                            لینک کردن موارد انتخاب‌شده
                        </button>
                        <button type="button" class="bk-btn-ghost" @click="internalMatches = []">انصراف</button>
                    </div>
                </div>
            </div>

            <!-- External -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span class="material-symbols-outlined bk-purple">link</span>
                    <span>لینک‌ساز خارجی</span>
                </div>
                <p class="bk-hint">عبارت را وارد کنید؛ در متن مقاله جستجو می‌شود. فقط همان رخدادها قابل تبدیل به لینک هستند (نه افزودن به انتها).</p>
                <div class="bk-field">
                    <label>عبارت داخل متن (انکر)</label>
                    <input type="text" x-model="extAnchor" placeholder="مثلاً: Core Web Vitals">
                </div>
                <div class="bk-field">
                    <label>آدرس مقصد</label>
                    <input type="url" dir="ltr" x-model="extUrl" placeholder="https://example.com/page">
                </div>
                <label class="bk-switch-row">
                    <span>nofollow</span>
                    <input type="checkbox" x-model="extNofollow">
                </label>
                <button type="button" class="bk-btn-primary bk-full" style="margin-top:8px" @click="scanExternalMatches()" :disabled="linkBusy">
                    جستجو در متن مقاله
                </button>

                <div class="bk-alert" x-show="externalReport.show" x-cloak :class="'is-' + externalReport.type">
                    <span x-text="externalReport.message"></span>
                </div>

                <div class="bk-match-box" x-show="externalMatches.length" x-cloak>
                    <div class="bk-card-head" style="margin-top:10px">
                        <span>رخدادهای یافت‌شده</span>
                        <span class="bk-badge" x-text="externalMatches.length + ' مورد'"></span>
                    </div>
                    <template x-for="(m, idx) in externalMatches" :key="'em'+idx">
                        <label class="bk-match-row">
                            <input type="checkbox" x-model="m.selected">
                            <div>
                                <small class="bk-ctx" x-html="m.contextHtml"></small>
                                <span class="bk-pos">موقعیت: <span x-text="m.index"></span></span>
                            </div>
                        </label>
                    </template>
                    <div class="bk-row" style="margin-top:8px">
                        <button type="button" class="bk-btn-primary" @click="applyExternalLinks()" :disabled="!externalMatches.some(m => m.selected)">
                            تبدیل انتخاب‌شده‌ها به لینک
                        </button>
                        <button type="button" class="bk-btn-ghost" @click="externalMatches = []">انصراف</button>
                    </div>
                </div>
            </div>

            <!-- Existing -->
            <div class="bk-card" x-show="(linkData.internal?.length || 0) + (linkData.external?.length || 0) > 0">
                <div class="bk-card-head"><span>لینک‌های موجود در مقاله</span></div>
                <template x-for="l in (linkData.internal || [])" :key="'li'+l.href+l.text">
                    <div class="bk-link-item">
                        <span class="bk-tag internal">داخلی</span>
                        <strong x-text="l.text || '—'"></strong>
                        <small dir="ltr" x-text="l.href"></small>
                    </div>
                </template>
                <template x-for="l in (linkData.external || [])" :key="'le'+l.href+l.text">
                    <div class="bk-link-item">
                        <span class="bk-tag external">خارجی</span>
                        <strong x-text="l.text || '—'"></strong>
                        <small dir="ltr" x-text="l.href"></small>
                    </div>
                </template>
            </div>
        </section>

        <!-- ========== SCHEMA TAB ========== -->
        <section x-show="activeTab === 'schema'" x-cloak class="bk-panel">
            <div class="bk-card">
                <div class="bk-card-head"><span>نوع Schema</span></div>
                <select x-model="schemaType" @change="rebuildSchema()">
                    <option value="Article">Article</option>
                    <option value="WebPage">WebPage</option>
                    <option value="FAQPage">FAQPage</option>
                    <option value="HowTo">HowTo</option>
                    <option value="Product">Product</option>
                    <option value="Organization">Organization</option>
                </select>
            </div>
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>JSON-LD</span>
                    <button type="button" class="bk-link-btn" @click="rebuildSchema()">بازنشانی</button>
                </div>
                <textarea
                    class="bk-code"
                    dir="ltr"
                    rows="14"
                    x-model="seo.schema"
                    @input.debounce.600ms="onFieldChange()"
                ></textarea>
            </div>
        </section>

        <!-- ========== SOCIAL TAB ========== -->
        <section x-show="activeTab === 'social'" x-cloak class="bk-panel">
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
                    <input type="url" dir="ltr" x-model="seo.og_image" @input.debounce.400ms="onFieldChange()">
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
                    <input type="url" dir="ltr" x-model="seo.x_image" @input.debounce.400ms="onFieldChange()">
                </div>
            </div>
        </section>
    </div>

    <!-- ========== AI MODAL ========== -->
    <div class="bk-modal-backdrop" x-show="aiOpen" x-cloak @click.self="aiOpen = false">
        <div class="bk-modal" role="dialog" aria-modal="true">
            <header class="bk-modal-head">
                <div class="bk-modal-title">
                    <span class="material-symbols-outlined bk-purple">auto_awesome</span>
                    <div>
                        <strong>استودیو هوش مصنوعی بنکای</strong>
                        <small>تولید و بهینه‌سازی متا، کلیدواژه و لینک</small>
                    </div>
                </div>
                <button type="button" class="bk-icon-btn" @click="aiOpen = false">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </header>

            <nav class="bk-modal-tabs">
                <button type="button" :class="{ 'is-active': aiTab === 'title' }" @click="aiTab = 'title'">عنوان و متا</button>
                <button type="button" :class="{ 'is-active': aiTab === 'keywords' }" @click="aiTab = 'keywords'">کلیدواژه</button>
                <button type="button" :class="{ 'is-active': aiTab === 'links' }" @click="aiTab = 'links'">لینک داخلی</button>
                <button type="button" :class="{ 'is-active': aiTab === 'external' }" @click="aiTab = 'external'">لینک خارجی</button>
                <button type="button" :class="{ 'is-active': aiTab === 'rewrite' }" @click="aiTab = 'rewrite'">بازنویسی</button>
            </nav>

            <div class="bk-modal-body">
                <!-- Title / Desc -->
                <div x-show="aiTab === 'title'" class="bk-modal-pane">
                    <div class="bk-ai-grid">
                        <div>
                            <label>عنوان فعلی</label>
                            <input type="text" x-model="seo.seo_title">
                            <label>توضیحات فعلی</label>
                            <textarea rows="4" x-model="seo.description"></textarea>
                        </div>
                        <div>
                            <label>پیشنهاد AI</label>
                            <div class="bk-ai-output" x-text="aiDraft.title || 'برای تولید روی دکمه کلیک کنید…'"></div>
                            <div class="bk-ai-output" x-text="aiDraft.description || ''"></div>
                        </div>
                    </div>
                    <div class="bk-modal-actions">
                        <button type="button" class="bk-btn-primary" @click="generateAI('title')" :disabled="aiLoading">
                            <span class="material-symbols-outlined" :class="{ 'bk-spin': aiLoading }">auto_awesome</span>
                            تولید با AI
                        </button>
                        <button type="button" class="bk-btn-ghost" @click="applyAiDraft('title')" x-show="aiDraft.title">اعمال و ذخیره</button>
                    </div>
                </div>

                <!-- Keywords -->
                <div x-show="aiTab === 'keywords'" class="bk-modal-pane">
                    <label>کلیدواژه اصلی و ثانویه</label>
                    <div class="bk-kw-box">
                        <template x-for="(kw, i) in keywordList" :key="'m'+i">
                            <span class="bk-chip"><span x-text="kw"></span>
                                <button type="button" class="bk-chip-x" @click="removeKeyword(i)"><span class="material-symbols-outlined">close</span></button>
                            </span>
                        </template>
                        <input type="text" class="bk-kw-input" x-model="kwDraft" @keydown.enter.prevent="addKeywordFromInput()" placeholder="+ کلیدواژه">
                    </div>
                    <div class="bk-ai-output" style="margin-top:10px" x-text="aiDraft.keywords || 'پیشنهادهای AI اینجا نمایش داده می‌شود…'"></div>
                    <div class="bk-modal-actions">
                        <button type="button" class="bk-btn-primary" @click="generateAI('keywords')" :disabled="aiLoading">تولید کلیدواژه</button>
                        <button type="button" class="bk-btn-ghost" @click="applyAiDraft('keywords')" x-show="aiDraft.keywords">اعمال</button>
                    </div>
                </div>

                <!-- Internal links in modal -->
                <div x-show="aiTab === 'links'" class="bk-modal-pane">
                    <p class="bk-hint">جستجوی مقالات مرتبط بر اساس کلیدواژه اصلی و درج لینک.</p>
                    <div class="bk-row">
                        <input type="text" x-model="linkAnchor" :placeholder="seo.focus_keyword || 'کلیدواژه'">
                        <button type="button" class="bk-btn-primary" @click="searchPosts()">جستجو</button>
                    </div>
                    <ul class="bk-search-list" x-show="postResults.length">
                        <template x-for="p in postResults" :key="'mp'+p.id">
                            <li>
                                <div><strong x-text="p.title"></strong><small dir="ltr" x-text="p.permalink"></small></div>
                                <button type="button" class="bk-btn-sm" @click="insertInternalLink(p)">درج</button>
                            </li>
                        </template>
                    </ul>
                </div>

                <!-- External in modal -->
                <div x-show="aiTab === 'external'" class="bk-modal-pane">
                    <div class="bk-field"><label>انکر</label><input type="text" x-model="extAnchor"></div>
                    <div class="bk-field"><label>URL</label><input type="url" dir="ltr" x-model="extUrl"></div>
                    <label class="bk-switch-row"><span>nofollow</span><input type="checkbox" x-model="extNofollow"></label>
                    <button type="button" class="bk-btn-primary" @click="insertExternalLink()">درج در محتوا</button>
                </div>

                <!-- Rewrite -->
                <div x-show="aiTab === 'rewrite'" class="bk-modal-pane">
                    <p class="bk-hint">بازنویسی کل یا بخشی از مقاله با حفظ معنا و بهینه‌سازی سئو.</p>
                    <textarea rows="6" x-model="aiDraft.rewrite" placeholder="نتیجه بازنویسی اینجا ظاهر می‌شود…"></textarea>
                    <div class="bk-modal-actions">
                        <button type="button" class="bk-btn-primary" @click="generateAI('rewrite')" :disabled="aiLoading">بازنویسی با AI</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="bk-toast" x-show="toast.show" x-cloak :class="'is-' + toast.type" x-text="toast.message"></div>
</div>
