<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_active  = ($active_tab === 'overview');
$is_rtl     = !empty($state['isRtl']) || is_rtl();
?>
<div id="tab-overview" class="bankai-tab-pane<?php echo $is_active ? ' active' : ''; ?>" <?php echo $is_active ? '' : 'hidden'; ?> style="width:100%;max-width:100%;box-sizing:border-box">

    <div class="bankai-section-header" style="margin-bottom: 24px;">
        <div class="bankai-section-title-wrap">
            <h2 class="bankai-section-title">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <span><?php echo esc_html__('پیشخوان و سلامت سیستم Bankai Core', 'bankai-core'); ?></span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo esc_html__('بررسی وضعیت کلی موتورها، تحلیل سلامت سیستم و وضعیت سرور', 'bankai-core'); ?>
            </p>
        </div>
    </div>

    <!-- Overview Status Grid -->
    <div class="bankai-card" style="margin-bottom: 24px;">
        <div class="bankai-card-header">
            <h3 class="bankai-card-title"><?php echo esc_html__('وضعیت کلی پلتفرم', 'bankai-core'); ?></h3>
        </div>
        <div class="bankai-card-body" style="display:flex;gap:20px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;background:#f6f8fa;padding:16px;border-radius:8px;border:1px solid #d0d7de;">
                <div style="font-size:12px;color:#59636e;font-weight:600;"><?php echo esc_html($is_rtl ? 'وضعیت سامانه' : 'Platform Status'); ?></div>
                <div style="font-size:18px;font-weight:800;color:#1a7f37;margin-top:4px;"><?php echo esc_html($is_rtl ? 'پایدار و آماده' : 'Stable & Ready'); ?></div>
            </div>
            <div style="flex:1;min-width:200px;background:#f6f8fa;padding:16px;border-radius:8px;border:1px solid #d0d7de;">
                <div style="font-size:12px;color:#59636e;font-weight:600;"><?php echo esc_html($is_rtl ? 'نسخه وردپرس' : 'WordPress Version'); ?></div>
                <div style="font-size:18px;font-weight:800;color:#0969da;margin-top:4px;"><?php echo esc_html(get_bloginfo('version')); ?></div>
            </div>
            <div style="flex:1;min-width:200px;background:#f6f8fa;padding:16px;border-radius:8px;border:1px solid #d0d7de;">
                <div style="font-size:12px;color:#59636e;font-weight:600;"><?php echo esc_html($is_rtl ? 'نسخه PHP' : 'PHP Version'); ?></div>
                <div style="font-size:18px;font-weight:800;color:#8250df;margin-top:4px;"><?php echo esc_html(PHP_VERSION); ?></div>
            </div>
        </div>
    </div>

</div>
