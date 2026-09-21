<?php
if (!defined('ABSPATH')) {
    exit;
}

$post_id = isset($_GET['post'])
    ? absint($_GET['post'])
    : 0;

if (!$post_id) {
    $post_id = get_the_ID();
}

$post = $post_id ? get_post($post_id) : null;

if (!$post) {
    return;
}
?>

<div
    id="bankai-seo-sidebar"
    class="bankai-seo-sidebar"
    x-data="bankaiSeoSidebar(<?php echo esc_attr($post_id); ?>)"
    x-init="init()"
>

    <!-- HEADER -->
    <header class="bankai-seo-header">

        <div class="bankai-seo-header-title">

            <div class="bankai-seo-icon">
                <span class="material-symbols-outlined">
                    shield
                </span>
            </div>

            <div>
                <strong>سئوی بنکای</strong>

                <small>
                    SEO Engine
                </small>
            </div>

        </div>

        <div
            class="bankai-seo-score-mini"
            :class="scoreClass"
        >
            <span x-text="analysis.score">0</span>
        </div>

    </header>


    <!-- TABS -->
    <nav class="bankai-seo-tabs">

        <button
            type="button"
            :class="{ 'is-active': activeTab === 'seo' }"
            @click="activeTab = 'seo'"
        >
            <span class="material-symbols-outlined">
                search
            </span>
            سئو
        </button>

        <button
            type="button"
            :class="{ 'is-active': activeTab === 'performance' }"
            @click="activeTab = 'performance'"
        >
            <span class="material-symbols-outlined">
                speed
            </span>
            سرعت
        </button>

        <button
            type="button"
            :class="{ 'is-active': activeTab === 'schema' }"
            @click="activeTab = 'schema'"
        >
            <span class="material-symbols-outlined">
                data_object
            </span>
            اسکیما
        </button>

        <button
            type="button"
            :class="{ 'is-active': activeTab === 'social' }"
            @click="activeTab = 'social'"
        >
            <span class="material-symbols-outlined">
                share
            </span>
            سوشال
        </button>

        <button
            type="button"
            :class="{ 'is-active': activeTab === 'ai' }"
            @click="activeTab = 'ai'"
        >
            <span class="material-symbols-outlined">
                auto_awesome
            </span>
            AI
        </button>

    </nav>


    <!-- SEO TAB -->
    <section
        x-show="activeTab === 'seo'"
        x-cloak
        class="bankai-seo-panel"
    >

        <!-- SCORE -->
        <div class="bankai-seo-score-card">

            <div class="bankai-seo-score-circle">

                <svg viewBox="0 0 120 120">

                    <circle
                        cx="60"
                        cy="60"
                        r="50"
                        class="score-track"
                    />

                    <circle
                        cx="60"
                        cy="60"
                        r="50"
                        class="score-progress"
                        :style="scoreStroke"
                    />

                </svg>

                <div class="score-value">

                    <strong x-text="analysis.score">
                        0
                    </strong>

                    <small>/100</small>

                </div>

            </div>

            <div class="score-copy">

                <strong>
                    آمادگی سئو
                </strong>

                <span
                    x-text="analysis.passed + ' از ' + analysis.total + ' بررسی موفق'"
                >
                    در حال بررسی...
                </span>

            </div>

        </div>


        <!-- FOCUS KEYWORD -->
        <div class="bankai-seo-field">

            <label>
                کلمه کلیدی اصلی
            </label>

            <div class="bankai-seo-input-group">

                <input
                    type="text"
                    x-model="seo.focus_keyword"
                    @input.debounce.500ms="analyze()"
                    placeholder="مثلاً: کاردرمانی در تهران"
                >

                <button
                    type="button"
                    @click="generateKeywordSuggestions()"
                    title="پیشنهاد AI"
                >
                    <span class="material-symbols-outlined">
                        auto_awesome
                    </span>
                </button>

            </div>

        </div>


        <!-- SEO TITLE -->
        <div class="bankai-seo-field">

            <div class="field-header">

                <label>
                    عنوان سئو
                </label>

                <span
                    class="character-counter"
                    :class="{ 'is-warning': seo.seo_title.length > 60 }"
                    x-text="seo.seo_title.length + '/60'"
                >
                </span>

            </div>

            <input
                type="text"
                x-model="seo.seo_title"
                @input.debounce.500ms="analyze()"
                maxlength="70"
            >

        </div>


        <!-- META DESCRIPTION -->
        <div class="bankai-seo-field">

            <div class="field-header">

                <label>
                    توضیحات متا
                </label>

                <span
                    class="character-counter"
                    x-text="seo.description.length + '/160'"
                >
                </span>

            </div>

            <textarea
                x-model="seo.description"
                @input.debounce.500ms="analyze()"
                maxlength="170"
                rows="4"
            ></textarea>

        </div>


        <!-- SERP -->
        <div class="bankai-seo-card">

            <div class="card-title">

                <span>
                    پیش‌نمایش Google
                </span>

                <button
                    type="button"
                    @click="serpMobile = !serpMobile"
                >
                    <span
                        class="material-symbols-outlined"
                        x-text="serpMobile ? 'desktop_windows' : 'smartphone'"
                    ></span>
                </button>

            </div>

            <div
                class="bankai-serp-preview"
                :class="{ 'is-mobile': serpMobile }"
            >

                <div class="serp-url">
                    <span x-text="seo.permalink"></span>
                </div>

                <div
                    class="serp-title"
                    x-text="seo.seo_title || seo.title"
                ></div>

                <div
                    class="serp-description"
                    x-text="seo.description"
                ></div>

            </div>

        </div>


        <!-- ANALYSIS -->
        <div class="bankai-seo-analysis">

            <div class="analysis-header">

                <strong>
                    تحلیل سئو
                </strong>

                <button
                    type="button"
                    @click="analyze()"
                    :disabled="loading"
                >
                    <span
                        class="material-symbols-outlined"
                        :class="{ 'bankai-spin': loading }"
                    >
                        refresh
                    </span>
                </button>

            </div>


            <template
                x-for="check in analysis.checks"
                :key="check.key"
            >

                <div
                    class="seo-check"
                    :class="check.passed ? 'is-passed' : 'is-warning'"
                >

                    <span class="material-symbols-outlined">
                        <template x-if="check.passed">
                            check_circle
                        </template>

                        <template x-if="!check.passed">
                            warning
                        </template>
                    </span>

                    <div>

                        <strong x-text="check.label"></strong>

                        <small x-text="check.message"></small>

                    </div>

                </div>

            </template>

        </div>


        <!-- ROBOTS -->
        <div class="bankai-seo-card">

            <div class="card-title">
                <span>ایندکس و Robots</span>
            </div>

            <label class="bankai-switch-row">

                <span>
                    اجازه ایندکس
                </span>

                <input
                    type="checkbox"
                    x-model="seo.robots.index"
                >

            </label>

            <label class="bankai-switch-row">

                <span>
                    دنبال کردن لینک‌ها
                </span>

                <input
                    type="checkbox"
                    x-model="seo.robots.follow"
                >

            </label>

        </div>


        <!-- CANONICAL -->
        <div class="bankai-seo-field">

            <label>
                Canonical URL
            </label>

            <input
                type="url"
                dir="ltr"
                x-model="seo.canonical"
            >

        </div>

    </section>


    <!-- PERFORMANCE -->
    <section
        x-show="activeTab === 'performance'"
        x-cloak
        class="bankai-seo-panel"
    >

        <div class="bankai-metric-card">

            <span>
                LCP
            </span>

            <strong>
                —
            </strong>

            <small>
                داده میدانی / آزمایشگاهی
            </small>

        </div>

        <div class="bankai-metric-card">

            <span>
                INP
            </span>

            <strong>
                —
            </strong>

            <small>
                نیازمند Telemetry
            </small>

        </div>

        <div class="bankai-metric-card">

            <span>
                CLS
            </span>

            <strong>
                —
            </strong>

            <small>
                نیازمند Telemetry
            </small>

        </div>

    </section>


    <!-- SCHEMA -->
    <section
        x-show="activeTab === 'schema'"
        x-cloak
        class="bankai-seo-panel"
    >

        <div class="bankai-seo-card">

            <div class="card-title">
                <span>Schema Type</span>
            </div>

            <select x-model="schema.type">

                <option value="Article">
                    Article
                </option>

                <option value="WebPage">
                    WebPage
                </option>

                <option value="FAQPage">
                    FAQPage
                </option>

                <option value="HowTo">
                    HowTo
                </option>

                <option value="Product">
                    Product
                </option>

            </select>

        </div>

        <div class="bankai-schema-status">

            <span class="material-symbols-outlined">
                verified
            </span>

            <div>

                <strong>
                    Schema فعال است
                </strong>

                <small>
                    JSON-LD توسط Bankai تولید می‌شود.
                </small>

            </div>

        </div>

    </section>


    <!-- SOCIAL -->
    <section
        x-show="activeTab === 'social'"
        x-cloak
        class="bankai-seo-panel"
    >

        <div class="bankai-seo-field">

            <label>
                Open Graph Title
            </label>

            <input
                type="text"
                x-model="seo.og_title"
            >

        </div>

        <div class="bankai-seo-field">

            <label>
                Open Graph Description
            </label>

            <textarea
                x-model="seo.og_description"
                rows="4"
            ></textarea>

        </div>

        <div class="bankai-seo-field">

            <label>
                Open Graph Image
            </label>

            <input
                type="url"
                dir="ltr"
                x-model="seo.og_image"
            >

        </div>

    </section>


    <!-- AI -->
    <section
        x-show="activeTab === 'ai'"
        x-cloak
        class="bankai-seo-panel"
    >

        <div class="bankai-ai-card">

            <span class="material-symbols-outlined">
                auto_awesome
            </span>

            <strong>
                دستیار هوشمند SEO
            </strong>

            <p>
                پیشنهاد عنوان، توضیحات متا، کلمات کلیدی و لینک‌های داخلی.
            </p>

            <button
                type="button"
                class="bankai-primary-button"
                @click="generateSeoSuggestions()"
            >
                تحلیل با AI
            </button>

        </div>

    </section>


    <!-- FOOTER -->
    <footer class="bankai-seo-footer">

        <span>
            <i></i>
            Live Sync
        </span>

        <button
            type="button"
            @click="save()"
            :disabled="saving"
        >

            <span
                class="material-symbols-outlined"
                :class="{ 'bankai-spin': saving }"
            >
                save
            </span>

            <span
                x-text="saving ? 'در حال ذخیره...' : 'ذخیره تغییرات'"
            >
                ذخیره تغییرات
            </span>

        </button>

    </footer>

</div>