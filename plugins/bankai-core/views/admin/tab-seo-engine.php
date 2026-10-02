<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_active  = ($active_tab === 'seo-engine');
$is_rtl     = !empty($state['isRtl']) || is_rtl();
?>
<div id="tab-seo-engine" class="bankai-tab-pane<?php echo $is_active ? ' active' : ''; ?>" <?php echo $is_active ? '' : 'hidden'; ?> style="width:100%;max-width:100%;box-sizing:border-box">

    <div class="bankai-section-header" style="margin-bottom: 24px;">
        <div class="bankai-section-title-wrap">
            <h2 class="bankai-section-title">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <span><?php echo esc_html__('موتور سئو و اسکیما (Bankai SEO Engine)', 'bankai-core'); ?></span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo esc_html__('مدیریت سئوی یک‌پارچه، نشانه‌گذاری اسکیما، ریدایرکت‌های ۳۰۱ و پایش ۴۰۴', 'bankai-core'); ?>
            </p>
        </div>
    </div>

    <!-- SEO Engine Settings Card -->
    <div class="bankai-card" style="margin-bottom: 24px;">
        <div class="bankai-card-header">
            <h3 class="bankai-card-title"><?php echo esc_html__('تنظیمات عمومی سئو', 'bankai-core'); ?></h3>
        </div>
        <div class="bankai-card-body">
            <div class="bankai-form-group">
                <label><input type="checkbox" id="bk-seo-auto-schema" checked> <?php echo esc_html($is_rtl ? 'تولید خودکار اسکیمای Article و BreadcrumbList' : 'Auto Generate Article & Breadcrumb Schema'); ?></label>
            </div>
            <div class="bankai-form-group" style="margin-top:14px;">
                <label><input type="checkbox" id="bk-seo-canonical-default" checked> <?php echo esc_html($is_rtl ? 'تنظیم خودکار Canonical URL به ساختار پیوند یکتا' : 'Auto Set Canonical URL'); ?></label>
            </div>
        </div>
    </div>

</div>
