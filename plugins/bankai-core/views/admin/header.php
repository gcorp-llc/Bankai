<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_rtl     = !empty($state['isRtl']) || is_rtl();

$logo_url = defined('BANKAI_CORE_URL') ? BANKAI_CORE_URL . 'assets/images/logo.jpg' : '';
?>
<header id="bankai-admin-header" class="bankai-header">

    <div class="bankai-header-right">
        <button type="button"
                id="bankai-mobile-toggle-btn"
                class="bankai-mobile-toggle-btn"
                aria-label="<?php esc_attr_e('Toggle navigation menu', 'bankai-core'); ?>">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="bankai-brand-group">
            <div class="bankai-header-logo-box">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>"
                         alt="Bankai Core"
                         class="bankai-header-logo-img"
                         width="28"
                         height="28"
                         loading="eager" />
                <?php else : ?>
                    <div class="bankai-header-logo-fallback">B</div>
                <?php endif; ?>
            </div>
            <div class="bankai-brand-text">
                <h1 class="bankai-header-title">BANKAI CORE</h1>
                <span class="bankai-header-ver-badge"><?php echo esc_html(defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0'); ?></span>
            </div>
        </div>
    </div>

    <div class="bankai-header-left">
        <div class="bankai-header-actions">
            <a href="https://github.com"
               target="_blank"
               rel="noopener noreferrer"
               class="bankai-btn bankai-btn-ghost bankai-btn-sm"
               title="<?php esc_attr_e('GitHub Repository', 'bankai-core'); ?>">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.082.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                </svg>
                <span class="btn-label"><?php echo esc_html($is_rtl ? __('قالب Bankai', 'bankai-core') : __('Bankai Theme', 'bankai-core')); ?></span>
            </a>

            <a href="<?php echo esc_url(home_url('/')); ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="bankai-btn bankai-btn-ghost bankai-btn-sm"
               title="<?php esc_attr_e('View Site', 'bankai-core'); ?>">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/>
                </svg>
                <span class="btn-label"><?php echo esc_html($is_rtl ? __('پیش‌نمایش سایت', 'bankai-core') : __('Site Preview', 'bankai-core')); ?></span>
            </a>

            <button type="button"
                    id="bankai-header-purge-cache-btn"
                    class="bankai-btn bankai-btn-primary bankai-btn-sm"
                    title="<?php esc_attr_e('Purge Cache', 'bankai-core'); ?>">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                <span class="btn-label"><?php esc_html_e('Purge Cache', 'bankai-core'); ?></span>
            </button>
        </div>
    </div>

</header>
