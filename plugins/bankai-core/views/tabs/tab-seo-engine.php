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
        <nav class="bk-tabs bk-tabs-6">
            <button type="button" :class="{ 'is-active': activeTab === 'seo' }" @click="activeTab = 'seo'">سئو</button>
            <button type="button" :class="{ 'is-active': activeTab === 'links' }" @click="activeTab = 'links'; loadLinks()">لینک‌ها</button>
            <button type="button" :class="{ 'is-active': activeTab === 'media' }" @click="activeTab = 'media'; scanPostImages()">تصاویر</button>
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
                                    <div class="bk-check-main">
                                        <strong x-text="check.label"></strong>
                                        <small x-text="check.message"></small>
                                        <template x-if="check.key === 'short_paragraphs' && !check.passed">
                                            <button type="button" class="bk-btn-sm bk-fix-para-btn" @click="autoSplitLongParagraphs()">
                                                <span class="material-symbols-outlined">wrap_text</span>
                                                کوتاه‌سازی خودکار پاراگراف‌ها
                                            </button>
                                        </template>
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
                    <div class="bk-input-with-action">
                        <input type="url" dir="ltr" x-model="seo.canonical" @input.debounce.500ms="onFieldChange()" placeholder="https://…">
                        <a class="bk-icon-action" :href="seo.canonical || '#'" target="_blank" rel="noopener" title="مشاهده لینک" :class="{ 'is-disabled': !seo.canonical }" @click="if(!seo.canonical)$event.preventDefault()">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== LINKS TAB (Enhanced Side-box) ========== -->
        <section x-show="activeTab === 'links'" x-cloak class="bk-panel">
            
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
                            <span class="material-symbols-outlined bk-purple">auto_awesome</span>
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
                        <div class="bk-pending-link-card">
                            <div class="bk-pending-link-info">
                                <div>انکر: <strong x-text="link.keyword"></strong></div>
                                <div>مقصد: <strong x-text="link.target_title"></strong></div>
                                <div class="bk-hint" x-text="link.reason"></div>
                            </div>
                            <div class="bk-pending-actions">
                                <button type="button" class="bk-btn-primary bk-btn-xs" @click="acceptPendingLink(link)" title="پذیرش و درج در متن">
                                    پذیرش و درج
                                </button>
                                <button type="button" class="bk-btn-ghost bk-btn-xs bk-btn-danger" @click="rejectPendingLink(link)" title="رد پیشنهاد">
                                    رد
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Active Links List -->
            <div x-show="linksSubTab === 'active'">
                <!-- Internal Search & Manual Insert -->
                <div class="bk-card bk-link-builder-card">
                    <div class="bk-card-head">
                        <span class="material-symbols-outlined bk-blue">hub</span>
                        <span>لینک‌ساز داخلی</span>
                        <button type="button" class="bk-btn-ghost bk-btn-ai bk-btn-xs" style="margin-inline-start:auto" @click="openAiModal('links')" title="پیشنهاد با هوش مصنوعی">
                            <span class="material-symbols-outlined">auto_awesome</span>
                            AI
                        </button>
                    </div>
                    <p class="bk-hint">۱) کلیدواژه را جستجو کنید → ۲) مقالات مرتبط را تایید کنید → ۳) محل لینک در متن را انتخاب کنید → ۴) اعمال</p>
                    <div class="bk-search-combo bk-search-combo-inline">
                        <input type="text" x-model="linkAnchor" placeholder="کلیدواژه / انکر تکست…" @keydown.enter.prevent="runInternalSearch()">
                        <button type="button" class="bk-search-icon-btn" @click="runInternalSearch()" :disabled="linkBusy" title="جستجو">
                            <span class="material-symbols-outlined" :class="{ 'bk-spin': linkBusy }">search</span>
                        </button>
                    </div>

                    <!-- Step 1: Related articles -->
                    <div class="bk-match-section" x-show="postResults.length" x-cloak>
                        <div class="bk-card-head">
                            <span>مقالات مرتبط</span>
                            <button type="button" class="bk-link-btn" @click="acceptAllInternalPosts()" x-show="postResults.length">همه تایید</button>
                        </div>
                        <template x-for="p in postResults" :key="p.id">
                            <div class="bk-search-result-item bk-article-pick" :class="{ 'is-accepted': p.accepted, 'is-rejected': p.rejected }">
                                <div class="bk-search-result-body">
                                    <strong x-text="p.title"></strong>
                                    <small dir="ltr" x-text="p.permalink"></small>
                                    <small x-show="p.match_reason" x-text="p.match_reason"></small>
                                </div>
                                <div class="bk-search-result-actions">
                                    <a class="bk-icon-action" :href="p.permalink" target="_blank" rel="noopener" title="مشاهده">
                                        <span class="material-symbols-outlined">open_in_new</span>
                                    </a>
                                    <button type="button" class="bk-icon-action is-ok" @click="p.accepted=true; p.rejected=false" title="تایید">
                                        <span class="material-symbols-outlined">check</span>
                                    </button>
                                    <button type="button" class="bk-icon-action is-danger" @click="p.accepted=false; p.rejected=true" title="رد">
                                        <span class="material-symbols-outlined">close</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div class="bk-ext-actions-row" x-show="acceptedInternalPosts().length">
                            <button type="button" class="bk-btn-ghost bk-btn-sm" @click="loadInternalContentMatches()">
                                <span class="material-symbols-outlined">manage_search</span>
                                یافتن محل لینک در متن
                            </button>
                            <button type="button" class="bk-btn-ghost bk-btn-sm" @click="applyInternalRandom()" x-show="acceptedInternalPosts().length > 1">
                                <span class="material-symbols-outlined">shuffle</span>
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
                            <span class="material-symbols-outlined">check</span>
                            تایید و اعمال لینک‌های داخلی
                        </button>
                    </div>
                </div>

                <div class="bk-card bk-link-builder-card">
                    <div class="bk-card-head">
                        <span class="material-symbols-outlined" style="color:#6b4eff">public</span>
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
                            <input type="checkbox" x-model="extNofollow"> nofollow
                        </label>
                        <button type="button" class="bk-btn-ghost bk-btn-sm" @click="findExternalMatches()">
                            <span class="material-symbols-outlined">search</span>
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
                            <span class="material-symbols-outlined">check</span>
                            تایید و اعمال لینک روی متن انتخاب‌شده
                        </button>
                    </div>
                </div>


                <!-- Existing Links List -->
                <div class="bk-card">
                    <div class="bk-card-head">
                        <span>لینک‌های موجود در مقاله</span>
                        <button type="button" class="bk-link-btn" @click="loadLinks()" title="تازه‌سازی">
                            <span class="material-symbols-outlined">refresh</span>
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
                                    <span class="material-symbols-outlined">open_in_new</span>
                                </a>
                                <button type="button" class="bk-icon-action is-danger" @click="removeLinkFromContent(l)" title="حذف لینک از متن">
                                    <span class="material-symbols-outlined">link_off</span>
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
                                    <span class="material-symbols-outlined">open_in_new</span>
                                </a>
                                <button type="button" class="bk-icon-action is-danger" @click="removeLinkFromContent(l)" title="حذف لینک از متن">
                                    <span class="material-symbols-outlined">link_off</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </section>

        <!-- ========== MEDIA / OPTIMIZE TAB ========== -->
        <section x-show="activeTab === 'media'" x-cloak class="bk-panel">
            <div class="bk-card bk-optimize-hero">
                <div class="bk-card-head">
                    <span class="material-symbols-outlined" style="color:#D97706">photo_size_select_large</span>
                    <span>بهینه‌سازی تصاویر مقاله (Optimize)</span>
                </div>
                <p class="bk-hint">تبدیل فرمت، تغییر اندازه، افزودن Alt و واترمارک برای تصاویر داخل محتوا.</p>
                <div class="bk-opt-options">
                    <label class="bk-opt-check"><input type="checkbox" x-model="optOptions.convertWebp"> تبدیل به WebP</label>
                    <label class="bk-opt-check"><input type="checkbox" x-model="optOptions.resize"> محدود کردن عرض</label>
                    <label class="bk-opt-check"><input type="checkbox" x-model="optOptions.watermark"> اعمال واترمارک</label>
                    <label class="bk-opt-check"><input type="checkbox" x-model="optOptions.fillAlt"> تکمیل Alt خالی</label>
                </div>
                <div class="bk-opt-row bk-opt-row-2">
                    <label>حداکثر عرض (px)
                        <input type="number" min="320" max="2560" x-model.number="optOptions.maxWidth">
                    </label>
                    <label>کیفیت
                        <input type="number" min="40" max="95" x-model.number="optOptions.quality">
                    </label>
                </div>
                <div class="bk-opt-row bk-opt-row-alt">
                    <label class="bk-alt-label">Alt پیش‌فرض
                        <input type="text" x-model="optOptions.defaultAlt" placeholder="اختیاری — یا خالی بگذارید">
                    </label>
                    <button type="button" class="bk-btn-ghost bk-btn-sm" @click="openAiModal('images')" title="تولید Alt با AI">
                        <span class="material-symbols-outlined">auto_awesome</span>
                        AI
                    </button>
                </div>
                <div class="bk-opt-actions bk-opt-actions-inline">
                    <button type="button" class="bk-icon-action bk-scan-btn" @click="scanPostImages()" :disabled="optBusy" title="اسکن تصاویر">
                        <span class="material-symbols-outlined" :class="{ 'bk-spin': optBusy }">refresh</span>
                    </button>
                    <button type="button" class="bk-btn-primary bk-btn-optimize" @click="runOptimizeImages()" :disabled="optBusy">
                        <span class="material-symbols-outlined" x-show="!optBusy">auto_fix_high</span>
                        <span x-text="optBusy ? 'در حال بهینه‌سازی…' : 'Optimize تصاویر'"></span>
                    </button>
                </div>
            </div>

            <div class="bk-card" x-show="postImages.length || optReport.length">
                <div class="bk-card-head">
                    <span>گزارش تصاویر</span>
                    <span class="bk-badge" x-text="(postImages.length || optReport.length) + ' مورد'"></span>
                </div>
                <template x-for="(img, i) in (optReport.length ? optReport : postImages)" :key="img.id || img.src || i">
                    <div class="bk-img-report-card bk-img-report-stack">
                        <div class="bk-img-thumb-wrap bk-img-thumb-lg">
                            <img :src="img.src || img.original_src" alt="" class="bk-img-thumb" loading="lazy">
                        </div>
                        <div class="bk-img-size-row">
                            <span class="bk-size-before" x-show="img.size_before || img.format_before || img.format">
                                <em x-text="(img.format_before || img.format || '—').toUpperCase()"></em>
                                <span x-text="formatBytes(img.size_before || img.size || 0)"></span>
                            </span>
                            <span class="material-symbols-outlined bk-size-arrow" x-show="img.size_after || img.format_after">arrow_forward</span>
                            <span class="bk-size-after" x-show="img.size_after || img.format_after">
                                <em x-text="(img.format_after || img.format || '—').toUpperCase()"></em>
                                <span x-text="formatBytes(img.size_after || 0)"></span>
                            </span>
                            <span class="bk-tag" style="background:#FEF3C7;color:#D97706" x-show="img.watermarked">Watermark</span>
                        </div>
                        <div class="bk-alt-edit">
                            <input type="text" :value="img.new_alt || img.alt || ''" @change="updateImageAlt(img, $event.target.value)" placeholder="متن جایگزین (Alt)">
                        </div>
                        <div class="bk-img-actions">
                            <a class="bk-icon-action" :href="img.src || img.original_src" target="_blank" rel="noopener" title="مشاهده">
                                <span class="material-symbols-outlined">open_in_new</span>
                            </a>
                            <button type="button" class="bk-icon-action" x-show="img.watermarked" @click="removeImageWatermark(img)" title="حذف واترمارک">
                                <span class="material-symbols-outlined">water_drop</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="bk-card" x-show="!postImages.length && !optReport.length && !optBusy">
                <div class="bk-empty-state">تصویری در محتوا یافت نشد. ابتدا مقاله را ذخیره کنید یا اسکن را بزنید.</div>
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

    <!-- ========== AI ASSISTANT MODAL (Redesigned Action Grid & Automation Workflow) ========== -->
    <div class="bk-modal-backdrop" x-show="aiOpen" x-cloak @click.self="aiOpen = false" dir="rtl">
        <div class="bk-modal bk-ai-modal" role="dialog" aria-modal="true">
            
            <!-- Modal Header -->
            <header class="bk-modal-head">
                <div class="bk-modal-title">
                    <div class="bk-ai-logo-icon">
                        <span class="material-symbols-outlined bk-purple">auto_awesome</span>
                    </div>
                    <div>
                        <strong>دستیار هوشمند سئو و محتوا Bankai</strong>
                        <small>بهینه‌سازی خودکار متا، کلیدواژه‌ها، لینک‌سازی داخلی و سئوی تصاویر</small>
                    </div>
                </div>

                <button type="button" class="bk-icon-btn bk-modal-close-btn" @click="aiOpen = false" title="بستن">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </header>

            <div class="bk-ai-engine-bar">
                <div class="bk-ai-engine-label">انتخاب موتور AI</div>
                <div class="bk-ai-engine-scroll">
                    <template x-for="p in aiProvidersList" :key="p.id">
                        <button type="button" class="bk-ai-engine-card"
                            :class="{ 'is-active': aiProvider === p.id }"
                            @click="aiProvider = p.id">
                            <span class="bk-ai-engine-name" x-text="p.name"></span>
                            <span class="bk-ai-engine-status" x-text="aiProvider === p.id ? 'فعال' : 'انتخاب'"></span>
                        </button>
                    </template>
                </div>
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
                                        <span class="material-symbols-outlined bk-text-success">check_circle</span>
                                    </template>
                                    <template x-if="step.status === 'loading'">
                                        <span class="material-symbols-outlined bk-spin bk-text-primary">sync</span>
                                    </template>
                                    <template x-if="step.status === 'pending'">
                                        <span class="material-symbols-outlined bk-text-muted">radio_button_unchecked</span>
                                    </template>
                                    <template x-if="step.status === 'error'">
                                        <span class="material-symbols-outlined bk-text-error">error</span>
                                    </template>
                                </div>
                                <span class="bk-chk-label" x-text="step.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Dashboard / Actions Grid -->
                <div class="bk-actions-dashboard" x-show="!aiReviewOpen">
                    <h3 class="bk-grid-section-title">کارت‌های اقدام سریع (Quick Actions)</h3>
                    
                    <!-- Hero Auto-Optimize All Card -->
                    <div class="bk-action-card bk-hero-action-card" @click="runAiAction('auto_all')" :class="{ 'is-disabled': aiLoading }">
                        <div class="bk-action-badge">پیشنهادی 🚀</div>
                        <div class="bk-action-icon">
                            <span class="material-symbols-outlined">rocket_launch</span>
                        </div>
                        <div class="bk-action-content">
                            <h4>اجرای یک‌پارچه تمام موارد (Auto-Optimize All)</h4>
                            <p>تحلیل هم‌زمان مقاله، نگارش عنوان و متا، لینک‌سازی هوشمند و تولید Alt خودکار برای تمام تصاویر.</p>
                        </div>
                        <button type="button" class="bk-btn-primary bk-action-btn" :disabled="aiLoading">
                            <span x-show="!aiLoading">شروع بهینه‌سازی کامل</span>
                            <span x-show="aiLoading">در حال پردازش...</span>
                        </button>
                    </div>

                    <!-- Quick Action Cards Grid -->
                    <div class="bk-quick-cards-grid">
                        
                        <!-- Full Rewrite -->
                        <div class="bk-action-card" @click="runAiAction('rewrite')" :class="{ 'is-disabled': aiLoading }">
                            <div class="bk-action-icon bk-icon-purple">
                                <span class="material-symbols-outlined">edit_note</span>
                            </div>
                            <div class="bk-action-content">
                                <h4>بازنویسی کامل مقاله</h4>
                                <p>ارتقای لحن و روان‌سازی محتوا با حفظ تگ‌های HTML و ساختار هدینگ‌ها.</p>
                            </div>
                            <button type="button" class="bk-btn-ghost bk-action-btn">بازنویسی با AI</button>
                        </div>

                        <!-- Auto Metas & Keywords -->
                        <div class="bk-action-card" @click="runAiAction('meta')" :class="{ 'is-disabled': aiLoading }">
                            <div class="bk-action-icon bk-icon-blue">
                                <span class="material-symbols-outlined">title</span>
                            </div>
                            <div class="bk-action-content">
                                <h4>تولید خودکار متاها</h4>
                                <p>ایجاد عنوان سئو، متادیسکریپشن جذاب و استخراج کلمات کلیدی + ادغام کلیدواژه‌های ثابت.</p>
                            </div>
                            <button type="button" class="bk-btn-ghost bk-action-btn">تولید متا و کلیدواژه</button>
                        </div>

                        <!-- Smart Internal Links -->
                        <div class="bk-action-card" @click="runAiAction('links')" :class="{ 'is-disabled': aiLoading }">
                            <div class="bk-action-icon bk-icon-emerald">
                                <span class="material-symbols-outlined">hub</span>
                            </div>
                            <div class="bk-action-content">
                                <h4>لینک‌سازی هوشمند داخلی</h4>
                                <p>شناسایی کلمات کلیدی محتوا و پیشنهاد بهترین مقالات مرتبط سایت برای لینک‌سازی.</p>
                            </div>
                            <button type="button" class="bk-btn-ghost bk-action-btn">پیشنهاد لینک داخلی</button>
                        </div>

                        <!-- Image Alt Text -->
                        <div class="bk-action-card" @click="runAiAction('images')" :class="{ 'is-disabled': aiLoading }">
                            <div class="bk-action-icon bk-icon-amber">
                                <span class="material-symbols-outlined">image</span>
                            </div>
                            <div class="bk-action-content">
                                <h4>تولید Alt برای تصاویر</h4>
                                <p>شناسایی تصاویر فاقد Alt و تولید متن جایگزین سئوشده بر اساس محتوای پیرامونی.</p>
                            </div>
                            <button type="button" class="bk-btn-ghost bk-action-btn">سئوی تصاویر مقاله</button>
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
                                    <textarea rows="2" x-model="aiDraft.description" class="bk-input-sm"></textarea>
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
                            <span class="material-symbols-outlined">check_circle</span>
                            اعمال تغییرات تاییدشده
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="bk-toast" x-show="toast.show" x-cloak :class="'is-' + toast.type" x-text="toast.message"></div>
</div>
