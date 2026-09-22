<?php
defined('ABSPATH') || exit;
$post_id = isset($_GET['post']) ? absint($_GET['post']) : get_the_ID();
if (!$post_id) return;
?>

<div id="bankai-seo-sidebar" class="bankai-seo-sidebar" x-data="bankaiSeoSidebar(<?php echo esc_attr($post_id); ?>)" x-init="init()">

    <!-- هدر مدولار سئو -->
    <header class="bankai-seo-header">
        <div class="bankai-seo-brand">
            <span class="material-symbols-outlined icon-glow">auto_awesome</span>
            <div>
                <strong>سئوی بنکای & AI</strong>
                <small>نسخه هوشمند و خودکار</small>
            </div>
        </div>
        <div class="bankai-score-badge" :style="{ backgroundColor: scoreColor }">
            <span x-text="analysis.score || 0"></span>
        </div>
    </header>

    <!-- تب‌های اصلی -->
    <nav class="bankai-seo-tabs">
        <button type="button" :class="{ 'is-active': activeTab === 'seo' }" @click="activeTab = 'seo'">
            <span class="material-symbols-outlined">tune</span> سئو و متاها
        </button>
        <button type="button" :class="{ 'is-active': activeTab === 'analysis' }" @click="activeTab = 'analysis'">
            <span class="material-symbols-outlined">analytics</span> آنالیز و گزارش
        </button>
        <button type="button" :class="{ 'is-active': activeTab === 'ai' }" @click="activeTab = 'ai'">
            <span class="material-symbols-outlined">psychology</span> هوش مصنوعی
        </button>
        <button type="button" :class="{ 'is-active': activeTab === 'linker' }" @click="activeTab = 'linker'">
            <span class="material-symbols-outlined">link</span> لینک‌ساز داخلی
        </button>
    </nav>

    <!-- تب سئو و کلمات کلیدی -->
    <section x-show="activeTab === 'seo'" class="bankai-tab-content">
        
        <!-- کلمه کلیدی اصلی -->
        <div class="bankai-field-group">
            <label>کلمه کلیدی اصلی مقاله</label>
            <input type="text" x-model="seo.focus_keyword" @input.debounce.400ms="onFieldChange()" placeholder="مثال: آموزش وردپرس">
        </div>

        <!-- کلمات کلیدی پیش‌فرض (ثابت) -->
        <div class="bankai-field-group">
            <label>کلمات کلیدی ثابت (پیش‌فرض برند)</label>
            <div class="bankai-keywords-wrapper">
                <template x-for="(defKw, index) in defaultKeywordList" :key="index">
                    <span class="bankai-tag static-tag">
                        <span x-text="defKw"></span>
                        <button type="button" @click="removeDefaultKeyword(index)">&times;</button>
                    </span>
                </template>
            </div>
            <div class="bankai-inline-input">
                <input type="text" x-model="defKwDraft" @keydown.enter.prevent="addDefaultKeyword()" placeholder="افزودن کلمه ثابت جدید...">
                <button type="button" @click="addDefaultKeyword()">افزودن</button>
            </div>
        </div>

        <!-- عنوان سئو -->
        <div class="bankai-field-group">
            <div class="field-label-row">
                <label>عنوان سئو (Meta Title)</label>
                <span class="count-badge" :class="seo.seo_title.length > 60 ? 'warn' : 'ok'" x-text="seo.seo_title.length + '/60'"></span>
            </div>
            <input type="text" x-model="seo.seo_title" @input.debounce.400ms="onFieldChange()" placeholder="عنوان بهینه‌شده برای گوگل...">
        </div>

        <!-- توضیحات متا -->
        <div class="bankai-field-group">
            <div class="field-label-row">
                <label>توضیحات متا (Meta Description)</label>
                <span class="count-badge" :class="seo.description.length > 160 ? 'warn' : 'ok'" x-text="seo.description.length + '/160'"></span>
            </div>
            <textarea x-model="seo.description" @input.debounce.400ms="onFieldChange()" rows="3" placeholder="توضیحات جذابی که در نتایج گوگل قرار می‌گیرد..."></textarea>
        </div>

    </section>

    <!-- تب آنالیز و گزارش سئو -->
    <section x-show="activeTab === 'analysis'" class="bankai-tab-content" x-cloak>
        <div class="bankai-score-card">
            <div class="score-circle-wrapper">
                <svg viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="45" class="bg-circle" />
                    <circle cx="50" cy="50" r="45" class="prog-circle" :style="{ strokeDashoffset: scoreOffset, stroke: scoreColor }" />
                </svg>
                <div class="score-text" x-text="analysis.score || 0"></div>
            </div>
            <div class="score-info">
                <h4>وضعیت بهینه‌سازی محتوا</h4>
                <p x-text="(analysis.passed || 0) + ' از ' + (analysis.total || 0) + ' تست سئو موفقیت‌آمیز بوده است.'"></p>
            </div>
        </div>

        <!-- لیست چک‌های آنالیز و پیشنهاد بهبود -->
        <div class="bankai-analysis-list">
            <template x-for="check in analysis.checks" :key="check.key">
                <div class="bankai-check-item" :class="check.passed ? 'is-passed' : 'is-failed'">
                    <span class="material-symbols-outlined" x-text="check.passed ? 'check_circle' : 'warning'"></span>
                    <div class="check-body">
                        <strong x-text="check.label"></strong>
                        <p x-text="check.message"></p>
                        <small x-show="!check.passed && check.suggestion" class="suggestion-text">
                            <strong>راهکار بهبود:</strong> <span x-text="check.suggestion"></span>
                        </small>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <!-- تب دستیار هوش مصنوعی -->
    <section x-show="activeTab === 'ai'" class="bankai-tab-content" x-cloak>
        <div class="bankai-ai-box">
            <div class="ai-header">
                <span class="material-symbols-outlined">auto_awesome</span>
                <span>تولید خودکار با هوش مصنوعی</span>
            </div>

            <div class="ai-actions">
                <button type="button" class="bankai-btn" @click="generateAiTask('meta_title')" :disabled="aiLoading">
                    تولید عنوان سئو
                </button>
                <button type="button" class="bankai-btn" @click="generateAiTask('meta_description')" :disabled="aiLoading">
                    تولید توضیحات متا
                </button>
                <button type="button" class="bankai-btn" @click="generateAiTask('keywords')" :disabled="aiLoading">
                    پیشنهاد کلمات کلیدی
                </button>
                <button type="button" class="bankai-btn highlight" @click="generateAiTask('rewrite')" :disabled="aiLoading">
                    بازنویسی کامل مقاله
                </button>
            </div>

            <!-- پیش‌نمایش خروجی AI -->
            <div class="ai-preview-zone" x-show="aiDraft.title || aiDraft.description || aiDraft.rewrite">
                <h4>خروجی پیشنهادی هوش مصنوعی:</h4>
                <div class="preview-item" x-show="aiDraft.title">
                    <strong>عنوان سئو:</strong>
                    <p x-text="aiDraft.title"></p>
                </div>
                <div class="preview-item" x-show="aiDraft.description">
                    <strong>توضیحات متا:</strong>
                    <p x-text="aiDraft.description"></p>
                </div>
                <div class="preview-item" x-show="aiDraft.rewrite">
                    <strong>بازنویسی متن مقاله:</strong>
                    <div class="rewrite-scroll" x-text="aiDraft.rewrite"></div>
                </div>

                <div class="preview-actions">
                    <button type="button" class="bankai-btn primary" @click="applyAiDraft('meta')" x-show="aiDraft.title || aiDraft.description">
                        اعمال متاداده‌ها
                    </button>
                    <button type="button" class="bankai-btn primary" @click="applyAiDraft('rewrite')" x-show="aiDraft.rewrite">
                        جایگزینی در ادیتور
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- تب لینک‌ساز داخلی -->
    <section x-show="activeTab === 'linker'" class="bankai-tab-content" x-cloak>
        <div class="bankai-linker-box">
            <h4>شناسایی و پیاده‌سازی لینک داخلی</h4>
            <p>مقاله مقصد را جهت اتصال هوشمند انتخاب نمایید:</p>

            <div class="post-search-input">
                <input type="text" x-model="linkSearchQuery" @input.debounce.300ms="fetchSitePosts()" placeholder="جستجوی عنوان مقاله مقصد...">
            </div>

            <div class="posts-list-scroll">
                <template x-for="p in sitePosts" :key="p.id">
                    <div class="post-link-item" @click="scanForInternalLinks(p)">
                        <span x-text="p.title"></span>
                        <span class="material-symbols-outlined">chevron_left</span>
                    </div>
                </template>
            </div>

            <!-- گزارش تطبیق لینک -->
            <div class="linker-report" x-show="internalReport.show" :class="internalReport.type">
                <p x-text="internalReport.message"></p>
                <button type="button" class="bankai-btn primary" x-show="internalMatches.length" @click="applyInternalLinks()">
                    اعمال خودکار لینک‌ها در مقاله
                </button>
            </div>
        </div>
    </section>

    <!-- فوتر سیو سریع -->
    <footer class="bankai-seo-footer">
        <button type="button" class="bankai-save-btn" @click="save()" :disabled="saving">
            <span class="material-symbols-outlined" x-show="!saving">save</span>
            <span x-text="saving ? 'در حال ذخیره...' : 'ذخیره تنظیمات سئو'"></span>
        </button>
    </footer>

    <!-- توست پیام سیستم -->
    <div class="bankai-toast" x-show="toast.show" :class="toast.type" x-text="toast.message" x-cloak></div>

</div>