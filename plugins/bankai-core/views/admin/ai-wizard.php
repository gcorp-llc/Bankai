<?php
/**
 * Bankai Core - AI Generator Wizard View
 *
 * 3-Step Wizard View (Title Suggestions -> Scheduling -> Live Progress & Management)
 *
 * @package Bankai
 */

defined('ABSPATH') || exit;

$categories = get_categories(['hide_empty' => false]);
$authors    = get_users(['capability' => 'edit_posts']);
?>

<div class="wrap bankai-admin-wrap" dir="rtl">
    <div class="bankai-header-card" style="display:flex; align-items:center; justify-scale:space-between; padding:20px; background:#1e1e2e; color:#fff; border-radius:12px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:22px; color:#fff;">✨ ویزارد هوشمند تولید و زمان‌بندی مقاله با AI</h1>
            <p style="margin:6px 0 0; color:#a6adc8; font-size:13px;">پیشنهاد عنوان، زمان‌بندی خودکار، نگارش مقاله، بهینه‌سازی کامل سئو و انتشار خودکار</p>
        </div>
        <div>
            <a href="<?php echo esc_url(admin_url('edit.php')); ?>" class="button button-secondary" style="background:#313244; color:#cdd6f4; border:none;">بازگشت به لیست نوشته‌ها</a>
        </div>
    </div>

    <div class="bankai-wizard-nav" style="display:flex; gap:12px; margin-bottom:20px;">
        <button type="button" class="bk-wiz-tab active" data-step="1" style="flex:1; padding:12px; border:none; background:#89b4fa; color:#11111b; font-weight:bold; border-radius:8px; cursor:pointer;">۱. پیشنهاد و انتخاب عنوان</button>
        <button type="button" class="bk-wiz-tab" data-step="2" style="flex:1; padding:12px; border:none; background:#313244; color:#a6adc8; font-weight:bold; border-radius:8px; cursor:pointer;" disabled>۲. زمان‌بندی انتشار</button>
        <button type="button" class="bk-wiz-tab" data-step="3" style="flex:1; padding:12px; border:none; background:#313244; color:#a6adc8; font-weight:bold; border-radius:8px; cursor:pointer;">۳. پیشرفت و مدیریت صف</button>
    </div>

    <!-- Step 1 View -->
    <div id="bk-wiz-step-1" class="bk-wiz-step-panel">
        <div style="background:#1e1e2e; padding:20px; border-radius:12px; color:#cdd6f4; margin-bottom:20px;">
            <h3 style="margin-top:0; color:#89b4fa;">تنظیمات پیشنهاد عنوان</h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                <div>
                    <label style="display:block; margin-bottom:6px;">موضوع / کلمه کلیدی اصلی</label>
                    <input type="text" id="bk-wiz-topic" class="widefat" placeholder="مثلاً: آموزش طراحی سایت با وردپرس" style="background:#181825; border:1px solid #45475a; color:#fff; padding:8px; border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px;">تعداد پیشنهاد (پیش‌فرض ۱۰)</label>
                    <input type="number" id="bk-wiz-count" value="10" min="1" max="20" class="widefat" style="background:#181825; border:1px solid #45475a; color:#fff; padding:8px; border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px;">دسته هدف پیشنهادی</label>
                    <select id="bk-wiz-cat" class="widefat" style="background:#181825; border:1px solid #45475a; color:#fff; padding:8px; border-radius:6px;">
                        <option value="0">همه دسته‌ها / عمومی</option>
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; gap:10px;">
                <button type="button" id="bk-btn-suggest-titles" class="button button-primary" style="background:#89b4fa; color:#11111b; border:none; padding:8px 20px; font-weight:bold;">✨ دریافت پیشنهاد عنوان از AI</button>
                <button type="button" id="bk-btn-add-manual-title" class="button button-secondary" style="background:#313244; color:#cdd6f4; border:none;">➕ افزودن عنوان دستی</button>
            </div>
        </div>

        <div id="bk-suggestions-wrapper" style="background:#1e1e2e; padding:20px; border-radius:12px; color:#cdd6f4; display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h3 style="margin:0; color:#a6e3a1;">عنوان‌های پیشنهادی</h3>
                <div>
                    <span id="bk-selected-count" style="color:#f9e2af; font-weight:bold; margin-left:12px;">۰ عنوان انتخاب شده</span>
                    <button type="button" id="bk-btn-select-all" class="button button-secondary" style="background:#313244; color:#cdd6f4; border:none; font-size:11px;">انتخاب همه / هیچ</button>
                </div>
            </div>
            <table class="widefat striped" style="background:#181825; color:#cdd6f4; border:1px solid #313244; border-radius:8px; overflow:hidden;">
                <thead>
                    <tr style="background:#313244; color:#cdd6f4;">
                        <th style="width:40px;"><input type="checkbox" id="bk-chk-toggle-all"></th>
                        <th>عنوان مقاله (قابل ویرایش)</th>
                        <th>کلمه کلیدی کانونی</th>
                        <th>نیت جستجو</th>
                        <th style="width:60px;">عملیات</th>
                    </tr>
                </thead>
                <tbody id="bk-suggestions-list">
                    <!-- Dynamic Rows -->
                </tbody>
            </table>
            <div style="margin-top:20px; text-align:left;">
                <button type="button" id="bk-btn-goto-step2" class="button button-primary" style="background:#a6e3a1; color:#11111b; border:none; padding:10px 24px; font-weight:bold; font-size:14px;" disabled>ادامه به زمان‌بندی ➔</button>
            </div>
        </div>
    </div>

    <!-- Step 2 View -->
    <div id="bk-wiz-step-2" class="bk-wiz-step-panel" style="display:none;">
        <div style="background:#1e1e2e; padding:20px; border-radius:12px; color:#cdd6f4; margin-bottom:20px;">
            <h3 style="margin-top:0; color:#89b4fa;">تنظیمات و ابزار توزیع خودکار زمان‌بندی</h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                <div>
                    <label style="display:block; margin-bottom:6px;">تاریخ شروع انتشار</label>
                    <input type="date" id="bk-wiz-start-date" class="widefat" style="background:#181825; border:1px solid #45475a; color:#fff; padding:8px; border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px;">فاصله انتشار (روز)</label>
                    <input type="number" id="bk-wiz-interval-days" value="1" min="1" class="widefat" style="background:#181825; border:1px solid #45475a; color:#fff; padding:8px; border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px;">نویسنده پیش‌فرض</label>
                    <select id="bk-wiz-author" class="widefat" style="background:#181825; border:1px solid #45475a; color:#fff; padding:8px; border-radius:6px;">
                        <?php foreach ($authors as $usr) : ?>
                            <option value="<?php echo esc_attr($usr->ID); ?>"><?php echo esc_html($usr->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px;">وضعیت نهایی</label>
                    <select id="bk-wiz-status" class="widefat" style="background:#181825; border:1px solid #45475a; color:#fff; padding:8px; border-radius:6px;">
                        <option value="future">زمان‌بندی شده (Future)</option>
                        <option value="draft">پیش‌نویس (Draft)</option>
                    </select>
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="button" id="bk-btn-auto-distribute" class="button button-secondary" style="background:#313244; color:#cdd6f4; border:none;">🗓️ توزیع خودکار تاریخ‌ها</button>
            </div>
        </div>

        <div style="background:#1e1e2e; padding:20px; border-radius:12px; color:#cdd6f4;">
            <h3 style="margin-top:0; color:#a6e3a1;">جدول زمان‌بندی عنوان‌های انتخاب‌شده</h3>
            <table class="widefat striped" style="background:#181825; color:#cdd6f4; border:1px solid #313244; border-radius:8px; overflow:hidden;">
                <thead>
                    <tr style="background:#313244; color:#cdd6f4;">
                        <th>عنوان</th>
                        <th>کلمه کلیدی</th>
                        <th>تاریخ و ساعت انتشار</th>
                        <th>دسته بندی</th>
                    </tr>
                </thead>
                <tbody id="bk-schedule-list">
                    <!-- Dynamic Rows -->
                </tbody>
            </table>
            <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:center;">
                <button type="button" id="bk-btn-back-step1" class="button button-secondary" style="background:#313244; color:#cdd6f4; border:none;">⬅ بازگشت به عناوین</button>
                <button type="button" id="bk-btn-start-generation" class="button button-primary" style="background:#f38ba8; color:#11111b; border:none; padding:10px 28px; font-weight:bold; font-size:15px;">🚀 تأیید و شروع تولید خودکار مقالات</button>
            </div>
        </div>
    </div>

    <!-- Step 3 View -->
    <div id="bk-wiz-step-3" class="bk-wiz-step-panel" style="display:none;">
        <div style="background:#1e1e2e; padding:20px; border-radius:12px; color:#cdd6f4;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0; color:#89b4fa;">جدول زنده وضعیت تولید و مدیریت Jobها</h3>
                <button type="button" id="bk-btn-refresh-progress" class="button button-secondary" style="background:#313244; color:#cdd6f4; border:none;">🔄 به‌روزرسانی</button>
            </div>
            <table class="widefat striped" style="background:#181825; color:#cdd6f4; border:1px solid #313244; border-radius:8px; overflow:hidden;">
                <thead>
                    <tr style="background:#313244; color:#cdd6f4;">
                        <th>شناسه</th>
                        <th>عنوان مقاله</th>
                        <th>مرحله فعلی</th>
                        <th>وضعیت</th>
                        <th>تاریخ انتشار</th>
                        <th>امتیاز سئو</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody id="bk-progress-list">
                    <tr><td colspan="7" style="text-align:center; padding:20px; color:#a6adc8;">در حال دریافت اطلاعات صف...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
