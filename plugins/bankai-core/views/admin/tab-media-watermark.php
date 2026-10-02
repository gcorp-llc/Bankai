<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_active  = ($active_tab === 'media-watermark');
$is_rtl     = !empty($state['isRtl']) || is_rtl();
?>
<div id="tab-media-watermark" class="bankai-tab-pane<?php echo $is_active ? ' active' : ''; ?>" <?php echo $is_active ? '' : 'hidden'; ?> style="width:100%;max-width:100%;box-sizing:border-box">

    <div class="bankai-section-header" style="margin-bottom: 24px;">
        <div class="bankai-section-title-wrap">
            <h2 class="bankai-section-title">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="14" height="14" rx="2"/><path d="M21 9v10a2 2 0 0 1-2 2H7"/><circle cx="9" cy="11" r="1.5"/><path d="M3 15l4-3 3 2 4-4 3 3"/></svg>
                <span><?php echo esc_html__('استودیو رسانه، بهینه‌سازی تصاویر و واترمارک', 'bankai-core'); ?></span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo esc_html__('تبدیل به فرمت‌های WebP/AVIF، واترمارک متحرک و فشرده‌سازی هوشمند تصاویر', 'bankai-core'); ?>
            </p>
        </div>
    </div>

    <!-- Media Settings Card -->
    <div class="bankai-card" style="margin-bottom: 24px;">
        <div class="bankai-card-header">
            <h3 class="bankai-card-title"><?php echo esc_html__('تنظیمات واترمارک و بهینه‌سازی تصاویر', 'bankai-core'); ?></h3>
        </div>
        <div class="bankai-card-body">
            <div class="bankai-form-group">
                <label for="bk-media-quality"><?php echo esc_html($is_rtl ? 'کیفیت تصاویر فشرده‌شده (٪)' : 'Compressed Image Quality (%)'); ?></label>
                <input type="number" id="bk-media-quality" class="bankai-input" min="40" max="100" value="82" style="width:120px;">
            </div>
            <div class="bankai-form-group" style="margin-top:14px;">
                <label><input type="checkbox" id="bk-media-auto-webp" checked> <?php echo esc_html($is_rtl ? 'تبدیل خودکار آپلودهای جدید به WebP' : 'Auto Convert New Uploads to WebP'); ?></label>
            </div>
        </div>
    </div>

</div>
