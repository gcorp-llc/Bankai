<?php
/**
 * Bankai Core - Articles Management & Smart AI Generator Wizard
 *
 * PHP Views + Vanilla JS (Zero Alpine/HTMX dependencies)
 * GitHub Light / Primer Design System
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

$categories = get_categories(['hide_empty' => false]);
$authors    = get_users(['capability' => 'edit_posts', 'fields' => ['ID', 'display_name']]);
$providers  = class_exists('Bankai_Admin_Menu') ? Bankai_Admin_Menu::instance()->get_initial_state_data()['providers'] ?? [] : [];
$default_provider = class_exists('Bankai_AI_Studio') ? Bankai_AI_Studio::instance()->get_default_provider() : 'gemini';

$subtab_get = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : '';
$initial_panel = ($subtab_get === 'ai-generate' || $subtab_get === 'generate') ? 'generate' : (($subtab_get === 'schedule') ? 'schedule' : 'list');
?>

<div id="tab-articles" class="bankai-tab-pane bankai-scope" style="width:100%;max-width:100%;box-sizing:border-box">

<style>
#tab-articles {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Vazirmatn", Roboto, Helvetica, Arial, sans-serif;
    color: var(--bankai-text-main, #1f2328);
}
#tab-articles .bk-card {
    background: var(--bankai-bg-surface, #ffffff);
    border: 1px solid var(--bankai-card-border, #d0d7de);
    border-radius: var(--bankai-radius-lg, 12px);
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--bankai-shadow-1, 0 1px 3px rgba(31,35,40,0.12));
}
#tab-articles .bk-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 20px;
}
#tab-articles .bk-head h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--bankai-text-main, #1f2328);
    display: flex;
    align-items: center;
    gap: 8px;
}
#tab-articles .bk-head p {
    margin: 4px 0 0;
    font-size: 13px;
    color: var(--bankai-text-muted, #59636e);
}
#tab-articles .bk-subnav {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid var(--bankai-border-subtle, #d0d7de);
    padding-bottom: 12px;
    margin-bottom: 20px;
}
#tab-articles .bk-subnav-btn {
    border: none;
    background: transparent;
    padding: 8px 16px;
    border-radius: var(--bankai-radius-sm, 6px);
    font-size: 13px;
    font-weight: 600;
    color: var(--bankai-text-muted, #59636e);
    cursor: pointer;
    transition: all 0.15s ease;
}
#tab-articles .bk-subnav-btn.is-active {
    background: var(--bankai-bg-main, #f6f8fa);
    color: var(--bankai-accent, #0969da);
    border: 1px solid var(--bankai-border-subtle, #d0d7de);
}
#tab-articles .bk-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: 1px solid var(--bankai-card-border, #d0d7de);
    border-radius: var(--bankai-radius-sm, 6px);
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    background: var(--bankai-bg-surface, #ffffff);
    color: var(--bankai-text-main, #1f2328);
    text-decoration: none;
    transition: background 0.15s ease, border-color 0.15s ease;
}
#tab-articles .bk-btn:hover {
    background: var(--bankai-bg-main, #f6f8fa);
    border-color: #8c959f;
}
#tab-articles .bk-btn-primary {
    background: var(--bankai-btn-primary-bg, #1f883d);
    border-color: var(--bankai-btn-primary-bg, #1f883d);
    color: #ffffff;
}
#tab-articles .bk-btn-primary:hover {
    background: var(--bankai-btn-primary-hover, #1a7f37);
    border-color: var(--bankai-btn-primary-hover, #1a7f37);
}
#tab-articles .bk-btn-accent {
    background: var(--bankai-accent, #0969da);
    border-color: var(--bankai-accent, #0969da);
    color: #ffffff;
}
#tab-articles .bk-btn-accent:hover {
    background: var(--bankai-accent-hover, #0550ae);
}
#tab-articles .bk-btn-danger {
    background: var(--bankai-danger-subtle, #ffebe9);
    color: var(--bankai-danger, #cf222e);
    border-color: var(--bankai-danger-border, #ff8182);
}
#tab-articles .bk-form-field {
    margin-bottom: 14px;
}
#tab-articles .bk-form-field label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--bankai-text-main, #1f2328);
    margin-bottom: 6px;
}
#tab-articles input[type="text"],
#tab-articles input[type="search"],
#tab-articles input[type="number"],
#tab-articles select,
#tab-articles textarea {
    width: 100%;
    border: 1px solid var(--bankai-card-border, #d0d7de);
    border-radius: var(--bankai-radius-sm, 6px);
    padding: 8px 12px;
    font-size: 13px;
    color: var(--bankai-text-main, #1f2328);
    background: var(--bankai-bg-surface, #ffffff);
    box-sizing: border-box;
}
#tab-articles input:focus,
#tab-articles select:focus,
#tab-articles textarea:focus {
    outline: none;
    border-color: var(--bankai-accent, #0969da);
    box-shadow: 0 0 0 3px var(--bankai-accent-subtle, #ddf4ff);
}
#tab-articles table.bk-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
#tab-articles table.bk-table th {
    text-align: right;
    padding: 10px 12px;
    background: var(--bankai-bg-main, #f6f8fa);
    color: var(--bankai-text-muted, #59636e);
    border-bottom: 1px solid var(--bankai-card-border, #d0d7de);
    font-weight: 600;
}
#tab-articles table.bk-table td {
    padding: 12px;
    border-bottom: 1px solid var(--bankai-card-border, #d0d7de);
    vertical-align: middle;
}
#tab-articles .bk-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
#tab-articles .bk-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
@media (max-width: 900px) {
    #tab-articles .bk-grid-2,
    #tab-articles .bk-grid-3 {
        grid-template-columns: 1fr;
    }
}
#tab-articles .bk-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}
#tab-articles .bk-badge-success { background: var(--bankai-success-subtle, #dafbe1); color: var(--bankai-success, #1a7f37); }
#tab-articles .bk-badge-warning { background: var(--bankai-warning-subtle, #fff8c5); color: var(--bankai-warning, #9a6700); }
#tab-articles .bk-badge-danger  { background: var(--bankai-danger-subtle, #ffebe9);  color: var(--bankai-danger, #cf222e); }
#tab-articles .bk-badge-info    { background: var(--bankai-accent-subtle, #ddf4ff);   color: var(--bankai-accent, #0969da); }

/* Wizard Specific Styles */
.bk-wiz-step-bar {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 24px;
}
.bk-wiz-step-item {
    flex: 1;
    text-align: center;
    padding: 10px;
    border-bottom: 3px solid var(--bankai-card-border, #d0d7de);
    color: var(--bankai-text-muted, #59636e);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.bk-wiz-step-item.is-active {
    border-bottom-color: var(--bankai-accent, #0969da);
    color: var(--bankai-accent, #0969da);
}
.bk-wiz-step-item.is-done {
    border-bottom-color: var(--bankai-success, #1a7f37);
    color: var(--bankai-success, #1a7f37);
}

.bk-outline-list {
    margin-top: 10px;
    padding: 10px 14px;
    background: var(--bankai-bg-main, #f6f8fa);
    border: 1px solid var(--bankai-card-border, #d0d7de);
    border-radius: var(--bankai-radius-md, 8px);
}
.bk-outline-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px dashed var(--bankai-card-border, #d0d7de);
    font-size: 12px;
}
.bk-outline-item:last-child {
    border-bottom: none;
}
.bk-alert-banner {
    padding: 14px 16px;
    border-radius: var(--bankai-radius-md, 8px);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.bk-alert-warning {
    background: var(--bankai-warning-subtle, #fff8c5);
    border: 1px solid var(--bankai-warning-border, #d4a72c);
    color: var(--bankai-warning, #9a6700);
}
</style>

<!-- Main Header -->
<div class="bk-head">
    <div>
        <h2>
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            <?php esc_html_e('مدیریت مقالات و ویزارد AI', 'bankai-core'); ?>
        </h2>
        <p><?php esc_html_e('پیشنهاد هوشمند عنوان، زمان‌بندی انتشار، نگارش بخش‌به‌بخش و بهینه‌سازی کامل سئو', 'bankai-core'); ?></p>
    </div>
    <div>
        <button type="button" class="bk-btn bk-btn-primary" id="bk-art-btn-open-gen">
            ✨ <?php esc_html_e('تولید مقاله با هوش مصنوعی', 'bankai-core'); ?>
        </button>
    </div>
</div>

<!-- Navigation Subtabs -->
<div class="bk-subnav" id="bk-art-nav">
    <button type="button" class="bk-subnav-btn <?php echo $initial_panel === 'list' ? 'is-active' : ''; ?>" data-panel="list">
        <?php esc_html_e('مشاهده مقالات', 'bankai-core'); ?>
    </button>
    <button type="button" class="bk-subnav-btn <?php echo $initial_panel === 'generate' ? 'is-active' : ''; ?>" data-panel="generate">
        ✨ <?php esc_html_e('تولید با AI و زمان‌بندی (ویزارد)', 'bankai-core'); ?>
    </button>
    <button type="button" class="bk-subnav-btn <?php echo $initial_panel === 'schedule' ? 'is-active' : ''; ?>" data-panel="schedule">
        📅 <?php esc_html_e('صف زمان‌بندی', 'bankai-core'); ?>
    </button>
</div>

<!-- PANEL 1: View Articles List -->
<div id="bk-art-panel-list" class="bk-art-panel" style="<?php echo $initial_panel !== 'list' ? 'display:none;' : ''; ?>">
    <div class="bk-card">
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;">
            <div style="flex:1;min-width:220px;">
                <input type="search" id="bk-art-search" placeholder="<?php esc_attr_e('جستجوی عنوان مقاله...', 'bankai-core'); ?>">
            </div>
            <select id="bk-art-orderby" style="width:auto;">
                <option value="date"><?php esc_html_e('تاریخ انتشار (جدید → قدیم)', 'bankai-core'); ?></option>
                <option value="modified"><?php esc_html_e('آخرین ویرایش', 'bankai-core'); ?></option>
                <option value="seo_score"><?php esc_html_e('امتیاز سئو', 'bankai-core'); ?></option>
                <option value="title"><?php esc_html_e('عنوان', 'bankai-core'); ?></option>
            </select>
            <button type="button" class="bk-btn" id="bk-art-refresh-btn">🔄 <?php esc_html_e('تازه‌سازی', 'bankai-core'); ?></button>
        </div>

        <div style="overflow-x:auto;">
            <table class="bk-table" id="bk-art-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('عنوان مقاله', 'bankai-core'); ?></th>
                        <th><?php esc_html_e('امتیاز سئو', 'bankai-core'); ?></th>
                        <th><?php esc_html_e('وضعیت', 'bankai-core'); ?></th>
                        <th><?php esc_html_e('تاریخ', 'bankai-core'); ?></th>
                        <th style="text-align:left;"><?php esc_html_e('عملیات', 'bankai-core'); ?></th>
                    </tr>
                </thead>
                <tbody id="bk-art-table-body">
                    <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--bankai-text-muted)"><?php esc_html_e('در حال بارگذاری مقالات...', 'bankai-core'); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PANEL 2: AI Smart Wizard + Single Form -->
<div id="bk-art-panel-generate" class="bk-art-panel" style="<?php echo $initial_panel !== 'generate' ? 'display:none;' : ''; ?>">

    <!-- Category Global Settings Card -->
    <div class="bk-card" style="border-right: 4px solid var(--bankai-accent, #0969da);">
        <h3 style="margin:0 0 12px;font-size:16px;font-weight:700;">
            ⚙️ <?php esc_html_e('تنظیمات کلی دسته و موتور AI (ارث‌بری خودکار)', 'bankai-core'); ?>
        </h3>
        <div class="bk-grid-3">
            <div class="bk-form-field">
                <label><?php esc_html_e('طول پیش‌فرض مقالات (کلمه)', 'bankai-core'); ?></label>
                <select id="bk-wiz-global-words">
                    <option value="1500">۱۵۰۰ کلمه (کوتاه)</option>
                    <option value="3000" selected>۳۰۰۰ کلمه (استاندارد)</option>
                    <option value="5000">۵۰۰۰ کلمه (جامع - چندبخشی)</option>
                    <option value="7000">۷۰۰۰ کلمه (پست مرجع - چندبخشی)</option>
                </select>
            </div>
            <div class="bk-form-field">
                <label><?php esc_html_e('وضعیت انتشار پیش‌فرض', 'bankai-core'); ?></label>
                <select id="bk-wiz-global-status">
                    <option value="future" selected><?php esc_html_e('زمان‌بندی‌شده (پیشنهای)', 'bankai-core'); ?></option>
                    <option value="draft"><?php esc_html_e('پیش‌نویس (Manual Review)', 'bankai-core'); ?></option>
                    <option value="publish"><?php esc_html_e('انتشار فوری', 'bankai-core'); ?></option>
                </select>
            </div>
            <div class="bk-form-field">
                <label><?php esc_html_e('انتخاب موتور AI (Provider)', 'bankai-core'); ?></label>
                <select id="bk-wiz-global-provider">
                    <?php foreach ($providers as $p): ?>
                        <option value="<?php echo esc_attr($p['id']); ?>" <?php echo (!$p['has_key'] ? 'disabled' : ($p['id'] === $default_provider ? 'selected' : '')); ?>>
                            <?php echo esc_html($p['label'] . (!$p['has_key'] ? ' (فاقد کلید API)' : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="bk-form-field">
            <label><?php esc_html_e('دستورالعمل کلی اضافه برای AI (اختیاری)', 'bankai-core'); ?></label>
            <textarea id="bk-wiz-global-notes" rows="2" placeholder="<?php esc_attr_e('مثال: لحن مقاله صمیمی و کاربردی باشد. حتماً از جدول و لیست بولت‌دار استفاده شود.', 'bankai-core'); ?>"></textarea>
        </div>
        <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
            <label style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;cursor:pointer;">
                <input type="checkbox" id="bk-wiz-global-seo" checked>
                <?php esc_html_e('تکمیل خودکار تمامی مراحل سئو پس از نگارش (متا، لینک داخلی، Alt و اسکیما)', 'bankai-core'); ?>
            </label>
            <label style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;cursor:pointer;">
                <input type="checkbox" id="bk-wiz-pause-outline">
                <?php esc_html_e('توقف برای تأیید و ویرایش زیرعنوان‌ها (Outline) قبل از شروع نگارش', 'bankai-core'); ?>
            </label>
        </div>
    </div>

    <!-- Smart Wizard Card -->
    <div class="bk-card">
        <h3 style="margin:0 0 16px;font-size:17px;font-weight:700;color:var(--bankai-accent);">
            🪄 <?php esc_html_e('ویزارد هوشمند تولید و زمان‌بندی مقاله با AI', 'bankai-core'); ?>
        </h3>

        <!-- Step Indicator Bar -->
        <div class="bk-wiz-step-bar">
            <div class="bk-wiz-step-item is-active" data-step="1">
                ۱. <?php esc_html_e('پیشنهاد و انتخاب عنوان', 'bankai-core'); ?>
            </div>
            <div class="bk-wiz-step-item" data-step="2">
                ۲. <?php esc_html_e('زمان‌بندی و تأیید', 'bankai-core'); ?>
            </div>
            <div class="bk-wiz-step-item" data-step="3">
                ۳. <?php esc_html_e('پیشرفت زنده و مدیریت صف', 'bankai-core'); ?>
            </div>
        </div>

        <!-- STEP 1: Title Suggestion & Overrides -->
        <div id="bk-wiz-step-1-content" class="bk-wiz-step-content">
            <div class="bk-grid-3">
                <div class="bk-form-field">
                    <label><?php esc_html_e('موضوع یا کلمه کلیدی اصلی', 'bankai-core'); ?></label>
                    <input type="text" id="bk-wiz-topic" placeholder="<?php esc_attr_e('مثال: طراحی سایت وردپرس', 'bankai-core'); ?>">
                </div>
                <div class="bk-form-field">
                    <label><?php esc_html_e('تعداد پیشنهاد (پیش‌فرض ۱۰)', 'bankai-core'); ?></label>
                    <input type="number" id="bk-wiz-count" min="1" max="20" value="10">
                </div>
                <div class="bk-form-field">
                    <label><?php esc_html_e('دسته‌بندی هدف', 'bankai-core'); ?></label>
                    <select id="bk-wiz-category">
                        <option value="0"><?php esc_html_e('همه دسته‌ها / عمومی', 'bankai-core'); ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
                <button type="button" class="bk-btn bk-btn-accent" id="bk-wiz-btn-suggest">
                    ✨ <?php esc_html_e('دریافت پیشنهاد عنوان از AI', 'bankai-core'); ?>
                </button>
                <button type="button" class="bk-btn" id="bk-wiz-btn-add-manual">
                    ➕ <?php esc_html_e('افزودن عنوان دستی', 'bankai-core'); ?>
                </button>
            </div>

            <!-- Titles List Table -->
            <div id="bk-wiz-titles-wrap" style="display:none;margin-top:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <strong><?php esc_html_e('فهرست عنوان‌های انتخابی جهت نگارش:', 'bankai-core'); ?></strong>
                    <div style="display:flex;gap:8px;">
                        <button type="button" class="bk-btn" id="bk-wiz-select-all"><?php esc_html_e('انتخاب همه', 'bankai-core'); ?></button>
                        <button type="button" class="bk-btn" id="bk-wiz-select-none"><?php esc_html_e('هیچکدام', 'bankai-core'); ?></button>
                    </div>
                </div>

                <table class="bk-table" id="bk-wiz-titles-table">
                    <thead>
                        <tr>
                            <th style="width:30px;"><input type="checkbox" id="bk-wiz-chk-master" checked></th>
                            <th><?php esc_html_e('عنوان مقاله', 'bankai-core'); ?></th>
                            <th><?php esc_html_e('کلمه کلیدی', 'bankai-core'); ?></th>
                            <th><?php esc_html_e('تنظیمات اختصاصی (طول / دستور)', 'bankai-core'); ?></th>
                            <th style="text-align:left;"><?php esc_html_e('عملیات', 'bankai-core'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="bk-wiz-titles-body"></tbody>
                </table>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;">
                    <button type="button" class="bk-btn" id="bk-wiz-btn-more-suggestions">✨ <?php esc_html_e('پیشنهاد بیشتر', 'bankai-core'); ?></button>
                    <button type="button" class="bk-btn bk-btn-primary" id="bk-wiz-btn-goto-step2">
                        <?php esc_html_e('مرحله بعد: زمان‌بندی انتشار ➔', 'bankai-core'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 2: Schedule & Confirmation -->
        <div id="bk-wiz-step-2-content" class="bk-wiz-step-content" style="display:none;">
            <div class="bk-card" style="background:var(--bankai-bg-main);">
                <h4 style="margin:0 0 10px;"><?php esc_html_e('ابزار توزیع خودکار زمان‌بندی', 'bankai-core'); ?></h4>
                <div class="bk-grid-3">
                    <div class="bk-form-field">
                        <label><?php esc_html_e('تاریخ شروع انتشار', 'bankai-core'); ?></label>
                        <input type="date" id="bk-wiz-sched-start-date" value="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </div>
                    <div class="bk-form-field">
                        <label><?php esc_html_e('فاصله انتشار بین مقالات (روز)', 'bankai-core'); ?></label>
                        <input type="number" id="bk-wiz-sched-interval" min="1" max="30" value="1">
                    </div>
                    <div class="bk-form-field">
                        <label><?php esc_html_e('ساعت انتشار ثابت', 'bankai-core'); ?></label>
                        <input type="time" id="bk-wiz-sched-time" value="10:00">
                    </div>
                </div>
                <button type="button" class="bk-btn bk-btn-accent" id="bk-wiz-btn-apply-auto-sched">
                    ⚡ <?php esc_html_e('اعمال توزیع خودکار زمان‌ها', 'bankai-core'); ?>
                </button>
            </div>

            <!-- Scheduled Items Table -->
            <table class="bk-table" id="bk-wiz-sched-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php esc_html_e('عنوان مقاله', 'bankai-core'); ?></th>
                        <th><?php esc_html_e('تاریخ و ساعت انتشار (wp_timezone)', 'bankai-core'); ?></th>
                    </tr>
                </thead>
                <tbody id="bk-wiz-sched-body"></tbody>
            </table>

            <!-- Batch Summary Box -->
            <div class="bk-card" style="margin-top:20px;background:var(--bankai-accent-subtle);border-color:var(--bankai-accent);">
                <h4 style="margin:0 0 8px;color:var(--bankai-accent);"><?php esc_html_e('خلاصه سفارش و تخمین توکن', 'bankai-core'); ?></h4>
                <div id="bk-wiz-summary-text" style="font-size:13px;line-height:1.6;"></div>
            </div>

            <div style="display:flex;justify-content:space-between;margin-top:16px;">
                <button type="button" class="bk-btn" id="bk-wiz-btn-back-step1">⬅️ <?php esc_html_e('بازگشت به گام ۱', 'bankai-core'); ?></button>
                <button type="button" class="bk-btn bk-btn-primary" id="bk-wiz-btn-start-generation">
                    🚀 <?php esc_html_e('تأیید و شروع تولید مقاله با AI', 'bankai-core'); ?>
                </button>
            </div>
        </div>

        <!-- STEP 3: Live Progress & Queue Management -->
        <div id="bk-wiz-step-3-content" class="bk-wiz-step-content" style="display:none;">

            <!-- Pause Alert Banner (if provider error occurs) -->
            <div id="bk-wiz-pause-alert" class="bk-alert-banner bk-alert-warning" style="display:none;">
                <div>
                    <strong>⚠️ <?php esc_html_e('صف تولید متوقف شد:', 'bankai-core'); ?></strong>
                    <span id="bk-wiz-pause-reason"></span>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=bankai-core&tab=ai-studio')); ?>" class="bk-btn" target="_blank">
                        ⚙️ <?php esc_html_e('تغییر Provider / تنظیمات API', 'bankai-core'); ?>
                    </a>
                    <button type="button" class="bk-btn bk-btn-primary" id="bk-wiz-btn-resume-queue">
                        ▶️ <?php esc_html_e('ادامه صف', 'bankai-core'); ?>
                    </button>
                </div>
            </div>

            <!-- Overall Progress Bar -->
            <div class="bk-card">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-weight:600;font-size:13px;">
                    <span id="bk-wiz-batch-progress-text"><?php esc_html_e('پیشرفت کلی دسته: ۰ از ۰ مقاله', 'bankai-core'); ?></span>
                    <span id="bk-wiz-batch-progress-percent">0%</span>
                </div>
                <div style="height:10px;background:var(--bankai-card-border);border-radius:999px;overflow:hidden;">
                    <div id="bk-wiz-batch-progress-fill" style="height:100%;width:0%;background:var(--bankai-btn-primary-bg);transition:width 0.3s ease;"></div>
                </div>
            </div>

            <!-- Jobs Cards Container -->
            <div id="bk-wiz-jobs-container"></div>
        </div>

    </div>

    <!-- Bottom: Single Article Form -->
    <div class="bk-card">
        <h3 style="margin:0 0 12px;font-size:16px;font-weight:700;">
            ✏️ <?php esc_html_e('تولید مقاله با هوش مصنوعی (تک‌مقاله)', 'bankai-core'); ?>
        </h3>
        <div class="bk-form-field">
            <label><?php esc_html_e('موضوع / عنوان مقاله', 'bankai-core'); ?></label>
            <input type="text" id="bk-single-topic" placeholder="<?php esc_attr_e('موضوع یا عنوان مقاله...', 'bankai-core'); ?>">
        </div>
        <div class="bk-grid-2">
            <div class="bk-form-field">
                <label><?php esc_html_e('کلمه کلیدی اصلی', 'bankai-core'); ?></label>
                <input type="text" id="bk-single-focus">
            </div>
            <div class="bk-form-field">
                <label><?php esc_html_e('طول متن (کلمه)', 'bankai-core'); ?></label>
                <select id="bk-single-length">
                    <option value="1500">۱۵۰۰ کلمه</option>
                    <option value="3000" selected>۳۰۰۰ کلمه</option>
                    <option value="5000">۵۰۰۰ کلمه</option>
                    <option value="7000">۷۰۰۰ کلمه</option>
                </select>
            </div>
        </div>
        <div class="bk-form-field">
            <label><?php esc_html_e('دستورالعمل اضافه (اختیاری)', 'bankai-core'); ?></label>
            <textarea id="bk-single-notes" rows="2"></textarea>
        </div>
        <button type="button" class="bk-btn bk-btn-primary" id="bk-single-btn-submit">
            🚀 <?php esc_html_e('شروع تولید تک‌مقاله', 'bankai-core'); ?>
        </button>
    </div>

</div>

<!-- PANEL 3: Schedule Queue -->
<div id="bk-art-panel-schedule" class="bk-art-panel" style="<?php echo $initial_panel !== 'schedule' ? 'display:none;' : ''; ?>">
    <div class="bk-card">
        <h3 style="margin:0 0 12px;font-size:16px;font-weight:700;"><?php esc_html_e('صف زمان‌بندی انتشار', 'bankai-core'); ?></h3>
        <div id="bk-schedule-queue-list"><?php esc_html_e('در حال دریافت اطلاعات صف...', 'bankai-core'); ?></div>
    </div>
</div>

</div>

<!-- Vanilla JS Module script for Article Hub & Wizard -->
<script>
(function() {
    'use strict';

    var currentBatchId = '';
    var activePollTimer = null;
    var suggestedTitlesList = [];

    function restBase() {
        return (window.bankaiData && bankaiData.restUrl) ? bankaiData.restUrl : '/wp-json/bankai/v1/';
    }

    function adminNonce() {
        return (window.bankaiData && bankaiData.adminNonce) || window.bankaiAdminNonce || '';
    }

    function ajaxUrl() {
        return (window.bankaiData && bankaiData.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
    }

    // Subtab Navigation Handler
    document.querySelectorAll('#bk-art-nav .bk-subnav-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#bk-art-nav .bk-subnav-btn').forEach(function(b) { b.classList.remove('is-active'); });
            document.querySelectorAll('.bk-art-panel').forEach(function(p) { p.style.display = 'none'; });

            btn.classList.add('is-active');
            var panelId = 'bk-art-panel-' + btn.getAttribute('data-panel');
            var panel = document.getElementById(panelId);
            if (panel) panel.style.display = 'block';

            if (btn.getAttribute('data-panel') === 'list') {
                loadArticlesList();
            } else if (btn.getAttribute('data-panel') === 'schedule') {
                loadScheduleQueue();
            }
        });
    });

    document.getElementById('bk-art-btn-open-gen')?.addEventListener('click', function() {
        var genNavBtn = document.querySelector('#bk-art-nav .bk-subnav-btn[data-panel="generate"]');
        if (genNavBtn) genNavBtn.click();
    });

    /* ---- Articles List Loader ---- */
    function loadArticlesList() {
        var tbody = document.getElementById('bk-art-table-body');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--bankai-text-muted)">در حال بارگذاری مقالات...</td></tr>';

        var search = document.getElementById('bk-art-search')?.value || '';
        var orderby = document.getElementById('bk-art-orderby')?.value || 'date';

        var q = new URLSearchParams({ page: '1', per_page: '25', search: search, orderby: orderby, order: 'DESC' });
        fetch(restBase() + 'seo/articles?' + q.toString(), { credentials: 'same-origin', headers: { 'X-WP-Nonce': (window.bankaiData && bankaiData.nonce) || '' } })
            .then(function(r) { return r.json(); })
            .then(function(j) {
                var items = (j && j.data && j.data.items) ? j.data.items : (j.items || []);
                if (!items.length) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--bankai-text-muted)">مقاله‌ای یافت نشد.</td></tr>';
                    return;
                }
                var html = '';
                items.forEach(function(row) {
                    var score = parseInt(row.score || 0, 10);
                    var badgeClass = score >= 80 ? 'bk-badge-success' : (score >= 50 ? 'bk-badge-warning' : 'bk-badge-danger');
                    html += '<tr>' +
                        '<td><strong>' + (row.title || '') + '</strong></td>' +
                        '<td><span class="bk-badge ' + badgeClass + '">' + score + '%</span></td>' +
                        '<td>' + (row.status_label || row.status || 'draft') + '</td>' +
                        '<td style="font-size:11px;color:var(--bankai-text-muted)">' + (row.date || row.modified || '') + '</td>' +
                        '<td style="text-align:left;"><a href="/wp-admin/post.php?post=' + row.id + '&action=edit" class="bk-btn" target="_blank">ویرایش وردپرس</a></td>' +
                        '</tr>';
                });
                tbody.innerHTML = html;
            })
            .catch(function() {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--bankai-danger)">خطا در بارگذاری مقالات.</td></tr>';
            });
    }

    document.getElementById('bk-art-refresh-btn')?.addEventListener('click', loadArticlesList);

    /* ---- Step 1: Suggest Titles ---- */
    document.getElementById('bk-wiz-btn-suggest')?.addEventListener('click', function() {
        var topic = document.getElementById('bk-wiz-topic')?.value || '';
        var count = document.getElementById('bk-wiz-count')?.value || '10';
        var catId = document.getElementById('bk-wiz-category')?.value || '0';

        if (!topic.trim()) {
            alert('لطفاً موضوع یا کلمه کلیدی اصلی را وارد کنید.');
            return;
        }

        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '⏳ در حال دریافت پیشنهاد...';

        var body = new FormData();
        body.append('action', 'bankai_ai_wizard_suggest_titles');
        body.append('nonce', adminNonce());
        body.append('topic', topic);
        body.append('count', count);
        body.append('category_id', catId);

        fetch(ajaxUrl(), { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(j) {
                if (j.success && j.data && Array.isArray(j.data.suggestions)) {
                    suggestedTitlesList = j.data.suggestions;
                    renderTitlesTable();
                } else {
                    alert((j.data && j.data.message) || 'خطا در دریافت پیشنهاد عنوان');
                }
            })
            .catch(function() { alert('خطا در ارتباط با سرور.'); })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '✨ دریافت پیشنهاد عنوان از AI';
            });
    });

    document.getElementById('bk-wiz-btn-add-manual')?.addEventListener('click', function() {
        var title = prompt('عنوان مقاله جدید را وارد کنید:');
        if (title && title.trim()) {
            suggestedTitlesList.push({
                title: title.trim(),
                focus_keyword: '',
                category_suggestion: ''
            });
            renderTitlesTable();
        }
    });

    function renderTitlesTable() {
        var wrap = document.getElementById('bk-wiz-titles-wrap');
        var tbody = document.getElementById('bk-wiz-titles-body');
        if (!wrap || !tbody) return;

        wrap.style.display = 'block';
        if (!suggestedTitlesList.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:16px;">عنوانی وجود ندارد.</td></tr>';
            return;
        }

        var globalWords = document.getElementById('bk-wiz-global-words')?.value || '3000';
        var html = '';
        suggestedTitlesList.forEach(function(item, idx) {
            html += '<tr data-idx="' + idx + '">' +
                '<td><input type="checkbox" class="bk-wiz-title-chk" checked></td>' +
                '<td><input type="text" class="bk-wiz-title-input" value="' + (item.title || '') + '"></td>' +
                '<td><input type="text" class="bk-wiz-kw-input" value="' + (item.focus_keyword || '') + '" placeholder="کلمه کلیدی..."></td>' +
                '<td>' +
                    '<select class="bk-wiz-len-input" style="width:auto;margin-bottom:4px;">' +
                        '<option value="">از تنظیمات کلی (' + globalWords + ' کلمه)</option>' +
                        '<option value="1500">۱۵۰۰ کلمه</option>' +
                        '<option value="3000">۳۰۰۰ کلمه</option>' +
                        '<option value="5000">۵۰۰۰ کلمه</option>' +
                        '<option value="7000">۷۰۰۰ کلمه</option>' +
                    '</select>' +
                '</td>' +
                '<td style="text-align:left;"><button type="button" class="bk-btn bk-btn-danger bk-wiz-del-row" data-idx="' + idx + '">حذف</button></td>' +
                '</tr>';
        });
        tbody.innerHTML = html;

        // Delete Row Event
        tbody.querySelectorAll('.bk-wiz-del-row').forEach(function(delBtn) {
            delBtn.addEventListener('click', function() {
                var idx = parseInt(this.getAttribute('data-idx'), 10);
                suggestedTitlesList.splice(idx, 1);
                renderTitlesTable();
            });
        });
    }

    document.getElementById('bk-wiz-select-all')?.addEventListener('click', function() {
        document.querySelectorAll('.bk-wiz-title-chk').forEach(function(c) { c.checked = true; });
    });
    document.getElementById('bk-wiz-select-none')?.addEventListener('click', function() {
        document.querySelectorAll('.bk-wiz-title-chk').forEach(function(c) { c.checked = false; });
    });

    /* ---- Step Navigation ---- */
    function gotoStep(stepNum) {
        document.querySelectorAll('.bk-wiz-step-item').forEach(function(item) {
            var s = parseInt(item.getAttribute('data-step'), 10);
            item.classList.remove('is-active', 'is-done');
            if (s === stepNum) item.classList.add('is-active');
            else if (s < stepNum) item.classList.add('is-done');
        });
        document.querySelectorAll('.bk-wiz-step-content').forEach(function(c) { c.style.display = 'none'; });
        var activeContent = document.getElementById('bk-wiz-step-' + stepNum + '-content');
        if (activeContent) activeContent.style.display = 'block';
    }

    document.getElementById('bk-wiz-btn-goto-step2')?.addEventListener('click', function() {
        var selected = getSelectedTitles();
        if (!selected.length) {
            alert('لطفاً حداقل یک مقاله را انتخاب کنید.');
            return;
        }
        renderScheduleTable(selected);
        gotoStep(2);
    });

    document.getElementById('bk-wiz-btn-back-step1')?.addEventListener('click', function() {
        gotoStep(1);
    });

    function getSelectedTitles() {
        var rows = document.querySelectorAll('#bk-wiz-titles-body tr');
        var result = [];
        rows.forEach(function(row) {
            var chk = row.querySelector('.bk-wiz-title-chk');
            if (chk && chk.checked) {
                var title = row.querySelector('.bk-wiz-title-input')?.value || '';
                var kw = row.querySelector('.bk-wiz-kw-input')?.value || '';
                var len = row.querySelector('.bk-wiz-len-input')?.value || '';
                if (title.trim()) {
                    result.push({
                        title: title.trim(),
                        focus_keyword: kw.trim(),
                        length: len
                    });
                }
            }
        });
        return result;
    }

    /* ---- Step 2: Schedule & Confirmation ---- */
    function renderScheduleTable(items) {
        var tbody = document.getElementById('bk-wiz-sched-body');
        var summary = document.getElementById('bk-wiz-summary-text');
        if (!tbody) return;

        var startDate = new Date();
        var globalWords = parseInt(document.getElementById('bk-wiz-global-words')?.value || '3000', 10);
        var totalWords = 0;

        var html = '';
        items.forEach(function(it, idx) {
            var w = parseInt(it.length || globalWords, 10);
            totalWords += w;

            var itemDate = new Date(startDate.getTime() + (idx * 86400000));
            var dateStr = itemDate.toISOString().split('T')[0] + ' 10:00';

            html += '<tr>' +
                '<td>' + (idx + 1) + '</td>' +
                '<td><strong>' + it.title + '</strong> (' + w + ' کلمه)</td>' +
                '<td><input type="text" class="bk-wiz-sched-input" value="' + dateStr + '" style="width:200px;"></td>' +
                '</tr>';
        });
        tbody.innerHTML = html;

        if (summary) {
            summary.innerHTML = 'تعداد مقالات انتخابی: <strong>' + items.length + ' مقاله</strong> | مجموع کلمات تخمینی: <strong>' + totalWords.toLocaleString() + ' کلمه</strong>';
        }
    }

    document.getElementById('bk-wiz-btn-start-generation')?.addEventListener('click', function() {
        var selected = getSelectedTitles();
        if (!selected.length) return;

        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '🚀 در حال ایجاد سفارش...';

        var globalOptions = {
            target_words: document.getElementById('bk-wiz-global-words')?.value || '3000',
            post_status: document.getElementById('bk-wiz-global-status')?.value || 'future',
            provider: document.getElementById('bk-wiz-global-provider')?.value || 'gemini',
            notes: document.getElementById('bk-wiz-global-notes')?.value || '',
            do_seo: document.getElementById('bk-wiz-global-seo')?.checked ? '1' : '0',
            pause_for_outline: document.getElementById('bk-wiz-pause-outline')?.checked ? '1' : '0'
        };

        var body = new FormData();
        body.append('action', 'bankai_ai_wizard_start_batch');
        body.append('nonce', adminNonce());

        var schedRows = document.querySelectorAll('#bk-wiz-sched-body tr');
        selected.forEach(function(art, i) {
            body.append('articles[' + i + '][title]', art.title);
            body.append('articles[' + i + '][focus_keyword]', art.focus_keyword);
            body.append('articles[' + i + '][length]', art.length);
            if (schedRows[i]) {
                var schedVal = schedRows[i].querySelector('.bk-wiz-sched-input')?.value || '';
                body.append('articles[' + i + '][scheduled_at_local]', schedVal);
                body.append('articles[' + i + '][scheduled_at]', schedVal);
            }
        });

        Object.keys(globalOptions).forEach(function(k) {
            body.append('options[' + k + ']', globalOptions[k]);
        });

        fetch(ajaxUrl(), { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(j) {
                if (j.success && j.data && j.data.batch_id) {
                    currentBatchId = j.data.batch_id;
                    gotoStep(3);
                    startBatchPolling();
                } else {
                    alert((j.data && j.data.message) || 'خطا در ثبت سفارش');
                }
            })
            .catch(function() { alert('خطا در ارتباط با سرور.'); })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '🚀 تأیید و شروع تولید مقاله با AI';
            });
    });

    /* ---- Step 3: Live Polling & Progress ---- */
    function startBatchPolling() {
        if (activePollTimer) clearInterval(activePollTimer);
        pollBatchProgress();
        activePollTimer = setInterval(pollBatchProgress, 3000);
    }

    function pollBatchProgress() {
        if (!currentBatchId) return;

        var body = new FormData();
        body.append('action', 'bankai_ai_wizard_get_progress');
        body.append('nonce', adminNonce());
        body.append('batch_id', currentBatchId);

        fetch(ajaxUrl(), { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(j) {
                if (j.success && j.data && Array.isArray(j.data.jobs)) {
                    renderJobsProgress(j.data.jobs);
                }
            })
            .catch(function() {});
    }

    function renderJobsProgress(jobs) {
        var container = document.getElementById('bk-wiz-jobs-container');
        var pauseAlert = document.getElementById('bk-wiz-pause-alert');
        var pauseReason = document.getElementById('bk-wiz-pause-reason');
        if (!container) return;

        var completedCount = 0;
        var hasPaused = false;
        var html = '';

        jobs.forEach(function(job) {
            if (job.status === 'completed' || job.status === 'needs_review') completedCount++;
            if (job.status === 'paused') {
                hasPaused = true;
                if (pauseReason) pauseReason.innerText = job.error_message || 'خطا در احراز هویت یا سهمیه Provider AI';
            }

            var stepData = {};
            try { stepData = JSON.parse(job.step_data || '{}'); } catch(e){}

            var statusBadge = job.status === 'completed' ? 'bk-badge-success' : (job.status === 'paused' ? 'bk-badge-warning' : (job.status === 'failed' ? 'bk-badge-danger' : 'bk-badge-info'));

            html += '<div class="bk-card" style="margin-bottom:14px;">' +
                '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">' +
                    '<strong>' + (job.title || '') + '</strong>' +
                    '<span class="bk-badge ' + statusBadge + '">' + (job.status || 'pending') + '</span>' +
                '</div>' +
                '<div style="font-size:12px;color:var(--bankai-text-muted);margin-bottom:8px;">مرحله فعلی: ' + (job.current_step || 'outline') + '</div>';

            // Sections Outline Display
            if (stepData.sections && Array.isArray(stepData.sections)) {
                html += '<div class="bk-outline-list"><strong>زیرعناوین مقاله (Outline):</strong>';
                stepData.sections.forEach(function(sec) {
                    var secStatus = sec.status === 'completed' ? '✅ تمام‌شده' : (sec.status === 'writing' ? '⌛ در حال نگارش...' : '⏳ در انتظار');
                    html += '<div class="bk-outline-item"><span>## ' + sec.h2 + '</span><span>' + secStatus + '</span></div>';
                });
                html += '</div>';
            }

            html += '</div>';
        });

        container.innerHTML = html;

        if (pauseAlert) pauseAlert.style.display = hasPaused ? 'flex' : 'none';

        var pct = jobs.length ? Math.round((completedCount / jobs.length) * 100) : 0;
        var progressText = document.getElementById('bk-wiz-batch-progress-text');
        var progressFill = document.getElementById('bk-wiz-batch-progress-fill');
        var progressPct = document.getElementById('bk-wiz-batch-progress-percent');

        if (progressText) progressText.innerText = 'پیشرفت کلی دسته: ' + completedCount + ' از ' + jobs.length + ' مقاله';
        if (progressPct) progressPct.innerText = pct + '%';
        if (progressFill) progressFill.style.width = pct + '%';

        if (completedCount >= jobs.length && jobs.length > 0) {
            if (activePollTimer) clearInterval(activePollTimer);
        }
    }

    document.getElementById('bk-wiz-btn-resume-queue')?.addEventListener('click', function() {
        var body = new FormData();
        body.append('action', 'bankai_ai_wizard_control_job');
        body.append('nonce', adminNonce());
        body.append('control_action', 'retry');

        fetch(ajaxUrl(), { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function() { pollBatchProgress(); });
    });

    /* ---- Single Article Form Submit ---- */
    document.getElementById('bk-single-btn-submit')?.addEventListener('click', function() {
        var topic = document.getElementById('bk-single-topic')?.value || '';
        var focus = document.getElementById('bk-single-focus')?.value || '';
        var length = document.getElementById('bk-single-length')?.value || '3000';
        var notes = document.getElementById('bk-single-notes')?.value || '';

        if (!topic.trim()) {
            alert('لطفاً عنوان مقاله را وارد کنید.');
            return;
        }

        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '🚀 در حال ثبت مقاله...';

        var body = new FormData();
        body.append('action', 'bankai_ai_wizard_start_batch');
        body.append('nonce', adminNonce());
        body.append('articles[0][title]', topic);
        body.append('articles[0][focus_keyword]', focus);
        body.append('articles[0][length]', length);
        body.append('articles[0][notes]', notes);

        fetch(ajaxUrl(), { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(j) {
                if (j.success && j.data && j.data.batch_id) {
                    currentBatchId = j.data.batch_id;
                    alert('مقاله با موفقیت به صف افزوده‌شد.');
                    gotoStep(3);
                    startBatchPolling();
                } else {
                    alert((j.data && j.data.message) || 'خطا در ثبت مقاله');
                }
            })
            .catch(function() { alert('خطا در ارتباط با سرور.'); })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '🚀 شروع تولید تک‌مقاله';
            });
    });

    function loadScheduleQueue() {
        var listWrap = document.getElementById('bk-schedule-queue-list');
        if (!listWrap) return;
        listWrap.innerHTML = 'در حال دریافت اطلاعات صف...';

        var body = new FormData();
        body.append('action', 'bankai_ai_wizard_get_progress');
        body.append('nonce', adminNonce());

        fetch(ajaxUrl(), { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(j) {
                if (j.success && j.data && Array.isArray(j.data.jobs)) {
                    var html = '';
                    j.data.jobs.forEach(function(job) {
                        html += '<div style="padding:10px 0;border-bottom:1px solid var(--bankai-card-border);display:flex;justify-content:space-between;">' +
                            '<div><strong>' + job.title + '</strong> - <span class="bk-badge bk-badge-info">' + job.status + '</span></div>' +
                            '<div>' + (job.scheduled_at || '—') + '</div>' +
                            '</div>';
                    });
                    listWrap.innerHTML = html || 'هیچ مقاله‌ای در صف نیست.';
                }
            })
            .catch(function() { listWrap.innerHTML = 'خطا در دریافت صف.'; });
    }

})();
</script>
