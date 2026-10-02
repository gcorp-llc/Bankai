<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_active  = ($active_tab === 'settings-license');
$is_rtl     = !empty($state['isRtl']) || is_rtl();
?>
<div id="tab-settings-license" class="bankai-tab-pane<?php echo $is_active ? ' active' : ''; ?>" <?php echo $is_active ? '' : 'hidden'; ?> style="width:100%;max-width:100%;box-sizing:border-box">

    <div class="bankai-section-header" style="margin-bottom: 24px;">
        <div class="bankai-section-title-wrap">
            <h2 class="bankai-section-title">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
                <span><?php echo esc_html__('تنظیمات پلتفرم، سوییچ ماژولار و لایسنس', 'bankai-core'); ?></span>
                <span class="bankai-badge bankai-badge-success" style="margin-inline-start: 8px;">
                    ✓ <?php echo esc_html($is_rtl ? 'نسخه حرفه‌ای سازمانی تایید شده' : 'Verified Pro Enterprise'); ?>
                </span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo esc_html__('مدیریت کلیدهای عمومی پلتفرم، لایسنس سازمانی و سطوح دسترسی', 'bankai-core'); ?>
            </p>
        </div>
    </div>

    <!-- License Card -->
    <div class="bankai-card" style="margin-bottom: 24px;">
        <div class="bankai-card-header">
            <h3 class="bankai-card-title"><?php echo esc_html__('لایسنس و اعتبار سنجی', 'bankai-core'); ?></h3>
        </div>
        <div class="bankai-card-body">
            <div class="bankai-form-group">
                <label for="bk-license-key-input"><?php echo esc_html($is_rtl ? 'کد لایسنس فعال پلتفرم:' : 'Active License Key:'); ?></label>
                <input type="text" id="bk-license-key-input" class="bankai-input" value="BANKAI-PRO-ENTERPRISE-LIFETIME" readonly style="font-family: monospace;">
            </div>
            <button type="button" class="bankai-btn bankai-btn-primary" id="bk-btn-revalidate-license">
                <?php echo esc_html($is_rtl ? 'اعتبارسنجی مجدد لایسنس' : 'Revalidate License'); ?>
            </button>
        </div>
    </div>

</div>
