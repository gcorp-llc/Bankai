<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_active  = ($active_tab === 'speed-cache');
$is_rtl     = !empty($state['isRtl']) || is_rtl();
?>
<div id="tab-speed-cache" class="bankai-tab-pane<?php echo $is_active ? ' active' : ''; ?>" <?php echo $is_active ? '' : 'hidden'; ?> style="width:100%;max-width:100%;box-sizing:border-box">

    <div class="bankai-section-header" style="margin-bottom: 24px;">
        <div class="bankai-section-title-wrap">
            <h2 class="bankai-section-title">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                <span><?php echo esc_html__('شتاب‌دهنده سرعت و کش Bankai', 'bankai-core'); ?></span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo esc_html__('مدیریت کش استاتیک HTML، فشرده‌سازی منابع و بهینه‌سازی دیتابیس', 'bankai-core'); ?>
            </p>
        </div>
    </div>

    <!-- Speed Cache Card -->
    <div class="bankai-card" style="margin-bottom: 24px;">
        <div class="bankai-card-header">
            <h3 class="bankai-card-title"><?php echo esc_html__('تنظیمات کش', 'bankai-core'); ?></h3>
        </div>
        <div class="bankai-card-body">
            <div class="bankai-form-group">
                <label><input type="checkbox" id="bk-speed-page-cache" checked> <?php echo esc_html($is_rtl ? 'فعال‌سازی کش فوق‌سریع استاتیک HTML' : 'Enable Static HTML Page Cache'); ?></label>
            </div>
            <div class="bankai-form-group" style="margin-top:14px;">
                <label><input type="checkbox" id="bk-speed-asset-min" checked> <?php echo esc_html($is_rtl ? 'فشرده‌سازی CSS و JS' : 'Minify CSS & JS'); ?></label>
            </div>
            <button type="button" class="bankai-btn bankai-btn-primary" id="bk-btn-purge-speed-cache" style="margin-top:16px;">
                <?php echo esc_html($is_rtl ? 'پاکسازی کامل کش' : 'Purge All Caches'); ?>
            </button>
        </div>
    </div>

</div>
