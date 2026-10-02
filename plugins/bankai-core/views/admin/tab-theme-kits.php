<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_active  = ($active_tab === 'theme-kits');
?>
<div id="tab-theme-kits" class="bankai-tab-pane<?php echo $is_active ? ' active' : ''; ?>" <?php echo $is_active ? '' : 'hidden'; ?> style="width:100%;max-width:100%;box-sizing:border-box">

    <div class="bankai-section-header" style="margin-bottom: 24px;">
        <div class="bankai-section-title-wrap">
            <h2 class="bankai-section-title">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <span><?php echo esc_html__('کیت‌های طراحی و سفارشی‌ساز پوسته‌ی Bankai', 'bankai-core'); ?></span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo esc_html__('انتخاب کیت‌های طراحی سفارشی، فونت‌های فارسی و تنظیمات چیدمان', 'bankai-core'); ?>
            </p>
        </div>
    </div>

    <!-- Container Width & Fonts -->
    <div class="bankai-card" style="margin-bottom: 24px;">
        <div class="bankai-card-header">
            <h3 class="bankai-card-title"><?php echo esc_html__('تنظیمات فونت و چیدمان', 'bankai-core'); ?></h3>
        </div>
        <div class="bankai-card-body">
            <div class="bankai-form-group">
                <label for="bk-customizer-font"><?php echo esc_html__('فونت تایپوگرافی', 'bankai-core'); ?></label>
                <select id="bk-customizer-font" class="bankai-select">
                    <option value="vazirmatn">Vazirmatn (وزیرمتن) — پشنهادی سئو و خوانایی</option>
                    <option value="iransans">IRANSans (ایران‌سنس)</option>
                    <option value="yekan">Yekan Bakh (یکان بخ)</option>
                    <option value="system">System UI (فونت پیش‌فرض سیستم)</option>
                </select>
            </div>
        </div>
    </div>

</div>
