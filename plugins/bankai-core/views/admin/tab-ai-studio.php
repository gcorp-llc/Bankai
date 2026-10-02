<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_active  = ($active_tab === 'ai-studio');
$is_rtl     = !empty($state['isRtl']) || is_rtl();
?>
<div id="tab-ai-studio" class="bankai-tab-pane<?php echo $is_active ? ' active' : ''; ?>" <?php echo $is_active ? '' : 'hidden'; ?> style="width:100%;max-width:100%;box-sizing:border-box">

    <div class="bankai-section-header" style="margin-bottom: 24px;">
        <div class="bankai-section-title-wrap">
            <h2 class="bankai-section-title">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="8" width="16" height="12" rx="3"/><path d="M12 8V5M9 12h.01M15 12h.01M9 16h6"/></svg>
                <span><?php echo esc_html__('استودیو هوش مصنوعی بنکای (AI Studio)', 'bankai-core'); ?></span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo esc_html__('مدیریت سرویس‌های هوش مصنوعی (Gemini، OpenAI، Claude) و خزانه کلیدهای API', 'bankai-core'); ?>
            </p>
        </div>
    </div>

    <!-- AI Providers Card -->
    <div class="bankai-card" style="margin-bottom: 24px;">
        <div class="bankai-card-header">
            <h3 class="bankai-card-title"><?php echo esc_html__('سرویس‌دهنده‌های فعال', 'bankai-core'); ?></h3>
        </div>
        <div class="bankai-card-body">
            <div class="bankai-form-group">
                <label for="bk-ai-default-provider"><?php echo esc_html($is_rtl ? 'سرویس‌دهنده پیش‌فرض:' : 'Default AI Provider:'); ?></label>
                <select id="bk-ai-default-provider" class="bankai-select" style="max-width:280px;">
                    <option value="gemini">Google Gemini (پیش‌فرض)</option>
                    <option value="openai">OpenAI (GPT-4o)</option>
                    <option value="claude">Anthropic Claude</option>
                </select>
            </div>
        </div>
    </div>

</div>
