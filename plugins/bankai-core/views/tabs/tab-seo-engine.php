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
if ($post_id && !$post) {
    return;
}

$score = $post_id ? (int) get_post_meta($post_id, '_bankai_seo_score', true) : 0;
$score_color = function_exists('bankai_seo_score_color') ? bankai_seo_score_color($score) : '#B8BCC2';
$seo_title = $post_id ? (string) get_post_meta($post_id, '_bankai_seo_title', true) : '';
if (!$seo_title && $post) {
    $seo_title = $post->post_title;
}
$seo_desc = $post_id ? (string) get_post_meta($post_id, '_bankai_seo_description', true) : '';
$permalink = $post_id ? get_permalink($post_id) : home_url('/');
$author_name = ($post && $post->post_author) ? get_the_author_meta('display_name', $post->post_author) : '';
$post_date = $post_id ? get_the_date('Y/m/d', $post_id) : '';
?>
<div id="bankai-seo-sidebar" class="bankai-seo-root" data-post-id="<?php echo (int) $post_id; ?>" dir="rtl">

    <!-- HEADER (compact) -->
    <header class="bk-header">
        <div class="bk-header-main">
            <div class="bk-score-ring" style="--score-color: <?php echo esc_attr($score_color); ?>">
                <svg viewBox="0 0 36 36">
                    <circle class="bk-track" cx="18" cy="18" r="14" />
                    <circle class="bk-progress" cx="18" cy="18" r="14"
                        stroke-dasharray="87.96"
                        stroke-dashoffset="<?php echo esc_attr(87.96 - (87.96 * min(100, max(0, $score)) / 100)); ?>"
                        style="stroke: <?php echo esc_attr($score_color); ?>" />
                </svg>
                <span class="bk-score-num" id="bk-seo-score-num"><?php echo (int) $score; ?></span>
            </div>
            <div class="bk-header-text">
                <strong>سئوی بنکای</strong>
            </div>
            <button type="button" class="bk-icon-btn" id="bk-seo-refresh-btn" title="تازه‌سازی">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.1-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/></svg>
            </button>
        </div>

        <!-- TABS -->
        <nav class="bk-tabs bk-tabs-6" id="bk-editor-tabs-nav">
            <button type="button" class="bk-tab-btn is-active" data-tab="seo">سئو</button>
            <button type="button" class="bk-tab-btn" data-tab="links">لینک‌ها</button>
            <button type="button" class="bk-tab-btn" data-tab="media">تصاویر</button>
            <button type="button" class="bk-tab-btn" data-tab="schema">اسکیما</button>
            <button type="button" class="bk-tab-btn" data-tab="social">سوشال</button>
            <button type="button" class="bk-tab-ai" data-tab="ai">
                AI <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
            </button>
        </nav>
    </header>

    <!-- BODY -->
    <div class="bk-body">

        <!-- ========== SEO TAB ========== -->
        <section id="bk-tab-seo" class="bk-panel bk-tab-pane">

            <!-- SERP -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <svg class="solar-icon bk-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <span>پیش‌نمایش SERP</span>
                    <div class="bk-seg" id="bk-serp-toggle-seg">
                        <button type="button" class="is-on" data-device="desktop">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
                        </button>
                        <button type="button" data-device="mobile">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                        </button>
                    </div>
                </div>
                <div class="bk-serp" id="bk-serp-preview">
                    <div class="bk-serp-url" dir="ltr">
                        <span class="bk-fav">B</span>
                        <span id="bk-serp-url-text"><?php echo esc_html($permalink); ?></span>
                    </div>
                    <div class="bk-serp-title" id="bk-serp-title-text"><?php echo esc_html($seo_title); ?></div>
                    <div class="bk-serp-byline" id="bk-serp-byline">
                        <span id="bk-serp-author-text"><?php echo esc_html($author_name ?: 'نویسنده'); ?></span>
                        <span> · </span>
                        <span id="bk-serp-date-text"><?php echo esc_html($post_date); ?></span>
                    </div>
                    <div class="bk-serp-desc" id="bk-serp-desc-text"><?php echo esc_html($seo_desc); ?></div>
                </div>
                <div class="bk-serp-meta">
                    <span id="bk-title-px-badge" class="ok">
                        <i></i>
                        طول پیکسل عنوان: <span id="bk-title-px-val">0</span>px
                    </span>
                    <a class="bk-link-btn" id="bk-gsc-link" href="https://search.google.com/search-console" target="_blank" rel="noopener" title="Search Console">
                        GSC ↗
                    </a>
                </div>
            </div>

            <!-- KEYWORDS -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>کلیدواژه‌ها</span>
                    <button type="button" class="bk-link-btn bk-open-ai-sub" data-ai-target="keywords">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                        پیشنهاد AI
                    </button>
                </div>

                <div class="bk-kw-box" id="bk-kw-chips-container">
                    <input type="text" id="bk-kw-input" class="bk-kw-input" placeholder="کلیدواژه + Enter">
                </div>
                <div class="bk-kw-stats" id="bk-kw-stats-row">
                    <span>چگالی: <strong id="bk-kw-density-val">0%</strong></span>
                    <span class="ok" id="bk-kw-density-status">توزیع امن</span>
                </div>
            </div>

            <!-- SEO TITLE -->
            <div class="bk-card bk-field-card">
                <div class="bk-field-head">
                    <label for="bk-seo-title-input">عنوان سئو</label>
                    <div class="bk-field-actions">
                        <span class="bk-counter" id="bk-seo-title-counter">0/60</span>
                        <button type="button" class="bk-link-btn bk-open-ai-sub" data-ai-target="title">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 20V10M18 20V4M6 20v-4M10 20V8"/></svg>
                            AI
                        </button>
                    </div>
                </div>
                <input type="text" id="bk-seo-title-input" value="<?php echo esc_attr($seo_title); ?>" maxlength="70">
                <div class="bk-bar"><div id="bk-title-bar" style="width:0%;background:#10B981"></div></div>
            </div>

            <!-- META DESCRIPTION -->
            <div class="bk-card bk-field-card">
                <div class="bk-field-head">
                    <label for="bk-seo-desc-input">توضیحات متا</label>
                    <div class="bk-field-actions">
                        <span class="bk-counter" id="bk-seo-desc-counter">0/160</span>
                        <button type="button" class="bk-link-btn bk-open-ai-sub" data-ai-target="description">
                            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.2 3.6L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.4L12 3z"/><path d="M19 14l.7 2L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/></svg>
                            AI
                        </button>
                    </div>
                </div>
                <textarea id="bk-seo-desc-input" maxlength="170" rows="3"><?php echo esc_textarea($seo_desc); ?></textarea>
                <div class="bk-bar"><div id="bk-desc-bar" style="width:0%;background:#10B981"></div></div>
            </div>

            <!-- ANALYSIS -->
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>تحلیل سئو</span>
                    <span class="bk-badge" id="bk-analysis-score-badge">0/0</span>
                </div>
                <div id="bk-analysis-groups-list"></div>
            </div>

            <!-- ROBOTS -->
            <div class="bk-card">
                <div class="bk-card-head"><span>ایندکس و Robots</span></div>
                <label class="bk-switch-row">
                    <span>اجازه ایندکس</span>
                    <input type="checkbox" id="bk-robot-index" checked>
                </label>
                <label class="bk-switch-row">
                    <span>دنبال کردن لینک‌ها</span>
                    <input type="checkbox" id="bk-robot-follow" checked>
                </label>
                <label class="bk-switch-row">
                    <span>noarchive (عدم آرشیو)</span>
                    <input type="checkbox" id="bk-robot-noarchive">
                </label>
                <label class="bk-switch-row">
                    <span>nosnippet</span>
                    <input type="checkbox" id="bk-robot-nosnippet">
                </label>
                <label class="bk-switch-row">
                    <span>مخفی کردن تاریخ در SERP</span>
                    <input type="checkbox" id="bk-robot-hidedate">
                </label>
                <div class="bk-field" style="margin-top:8px">
                    <label for="bk-robot-max-image">max-image-preview</label>
                    <select id="bk-robot-max-image">
                        <option value="large">large</option>
                        <option value="standard">standard</option>
                        <option value="none">none</option>
                    </select>
                </div>
                <div class="bk-field">
                    <label for="bk-robot-max-snippet">max-snippet (−۱ = بدون محدودیت)</label>
                    <input type="number" id="bk-robot-max-snippet" value="-1" min="-1" max="9999">
                </div>
                <div class="bk-field" style="margin-top:8px">
                    <label for="bk-seo-canonical-input">Canonical URL</label>
                    <div class="bk-input-with-action">
                        <input type="url" dir="ltr" id="bk-seo-canonical-input" placeholder="https://…">
                    </div>
                </div>
                <div class="bk-field" style="margin-top:10px">
                    <label for="bk-seo-redirect-input">ریدایرکت ۳۰۱ (اختیاری)</label>
                    <input type="url" dir="ltr" id="bk-seo-redirect-input" placeholder="https://example.com/new-url">
                    <p class="bk-hint" style="margin-top:6px">در صورت پر بودن، بازدید این صفحه با کد ۳۰۱ به آدرس بالا منتقل می‌شود.</p>
                </div>
            </div>
        </section>

        <!-- ========== LINKS TAB ========== -->
        <section id="bk-tab-links" class="bk-panel bk-tab-pane" hidden>
            <div class="bk-card">
                <div class="bk-card-head"><span>آمار لینک‌های مقاله</span></div>
                <div class="bk-link-stats">
                    <div class="bk-stat">
                        <strong id="bk-cnt-internal">0</strong>
                        <span>داخلی</span>
                    </div>
                    <div class="bk-stat">
                        <strong id="bk-cnt-external">0</strong>
                        <span>خارجی</span>
                    </div>
                </div>
            </div>
            <div class="bk-card">
                <div class="bk-card-head">
                    <span>لینک‌های موجود در مقاله</span>
                    <button type="button" class="bk-link-btn" id="bk-reload-links-btn" title="تازه‌سازی">تازه‌سازی</button>
                </div>
                <div id="bk-existing-links-list">
                    <div class="bk-empty-state">هنوز لینکی در متن مقاله ثبت نشده است.</div>
                </div>
            </div>
        </section>

        <!-- ========== MEDIA TAB ========== -->
        <section id="bk-tab-media" class="bk-panel bk-tab-pane" hidden>
            <div class="bk-card bk-optimize-hero">
                <div class="bk-card-head">
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15V5a2 2 0 0 0-2-2H9"/><rect x="3" y="9" width="12" height="12" rx="2"/><path d="M3 15l3-3 2 2 4-4"/></svg>
                    <span>بهینه‌سازی تصاویر مقاله (Optimize)</span>
                </div>
                <p class="bk-hint">تبدیل فرمت، تغییر اندازه، افزودن Alt و واترمارک برای تصاویر داخل محتوا.</p>
                <div class="bk-opt-actions bk-opt-actions-inline">
                    <button type="button" class="bk-btn-primary bk-btn-optimize" id="bk-run-media-scan">
                        <span>اسکن و بهینه‌سازی تصاویر</span>
                    </button>
                </div>
            </div>
            <div class="bk-card">
                <div class="bk-card-head"><span>گزارش تصاویر</span></div>
                <div id="bk-media-report-list">
                    <div class="bk-empty-state">تصویری در محتوا یافت نشد.</div>
                </div>
            </div>
        </section>

        <!-- ========== SCHEMA TAB ========== -->
        <section id="bk-tab-schema" class="bk-panel bk-tab-pane" hidden>
            <div class="bk-card">
                <div class="bk-card-head"><span>نوع اسکیما</span></div>
                <select id="bk-schema-type-select">
                    <option value="Article">Article</option>
                    <option value="BlogPosting">BlogPosting</option>
                    <option value="WebPage">WebPage</option>
                    <option value="FAQPage">FAQPage</option>
                    <option value="HowTo">HowTo</option>
                </select>
            </div>
            <div class="bk-card">
                <div class="bk-card-head"><span>JSON-LD</span></div>
                <textarea class="bk-code" id="bk-schema-json-text" dir="ltr" rows="10"></textarea>
            </div>
        </section>

        <!-- ========== SOCIAL TAB ========== -->
        <section id="bk-tab-social" class="bk-panel bk-tab-pane" hidden>
            <div class="bk-card">
                <div class="bk-card-head"><span>Open Graph</span></div>
                <div class="bk-field">
                    <label for="bk-og-title">عنوان OG</label>
                    <input type="text" id="bk-og-title">
                </div>
                <div class="bk-field">
                    <label for="bk-og-desc">توضیحات OG</label>
                    <textarea id="bk-og-desc" rows="2"></textarea>
                </div>
            </div>
        </section>

    </div>

    <!-- AI MODAL -->
    <div class="bk-modal-backdrop" id="bk-ai-modal-backdrop" hidden dir="rtl">
        <div class="bk-modal bk-ai-modal" role="dialog" aria-modal="true">
            <header class="bk-modal-head">
                <div class="bk-modal-title">
                    <strong>دستیار هوشمند سئو Bankai</strong>
                </div>
                <button type="button" class="bk-icon-btn" id="bk-ai-modal-close" title="بستن">×</button>
            </header>
            <div class="bk-modal-body" style="padding:16px;">
                <p>موتور هوش مصنوعی برای بهینه‌سازی متا و عناوین در دسترس است.</p>
                <div style="display:flex;gap:8px;margin-top:12px;">
                    <button type="button" class="bk-btn-primary" id="bk-ai-run-meta-btn">تولید هوشمند متا و کلیدواژه</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="bk-toast" id="bk-editor-toast" hidden role="status">
        <span class="bk-toast-msg" id="bk-editor-toast-msg"></span>
    </div>
</div>
