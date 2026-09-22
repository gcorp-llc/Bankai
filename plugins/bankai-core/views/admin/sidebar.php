<?php
defined('ABSPATH') || exit;

/** @var array $state */
$core = is_array($state['coreModules'] ?? null) ? $state['coreModules'] : [];
$active_map = [];
foreach ($core as $m) {
    if (!empty($m['key'])) {
        $active_map[$m['key']] = !empty($m['active']);
    }
}
$is_on = static function (string $key) use ($active_map): bool {
    return !array_key_exists($key, $active_map) || !empty($active_map[$key]);
};
?>
<aside id="bankai-admin-sidebar"
       class="bankai-sidebar"
       :class="{ 'mobile-open': mobileMenuOpen }">

    <div class="bankai-sidebar-content">
        <div class="bankai-sidebar-mobile-header" x-show="mobileMenuOpen" x-cloak>
            <div class="bankai-mobile-title">
                <div class="bankai-mobile-icon-box">
                    <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16M4 12h10M4 18h16" /></svg>
                </div>
                <span x-text="isRtl ? 'منوی مدیریت' : 'Navigation'">Navigation</span>
            </div>
            <button type="button" class="bankai-mobile-close-btn" @click="closeMobileMenu()" aria-label="Close">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6L6 18M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="bankai-brand-card">
            <div class="bankai-brand-logo">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13.5 2L4 13.5h7L9.5 22 20 10.5h-7.5L13.5 2Z" /></svg>
            </div>
            <div class="bankai-brand-info">
                <div class="bankai-brand-title">BANKAI CORE</div>
                <div class="bankai-brand-status">
                    <span class="status-dot"></span>
                    <span x-text="isRtl ? 'نسخه تجاری فعال' : 'Active Enterprise'">Active Enterprise</span>
                </div>
            </div>
        </div>

        <nav class="bankai-nav-menu" aria-label="<?php esc_attr_e('Bankai navigation', 'bankai-core'); ?>">
            <div class="bankai-nav-group">

                <button type="button" id="nav-tab-overview" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'overview' }"
                        @click="setTab('overview')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </div>
                    <span x-text="t('navOverview')">Dashboard</span>
                </button>

                <button type="button" id="nav-tab-theme-kits" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'theme-kits', 'is-disabled': !isModuleNavEnabled('theme-kits') }"
                        :disabled="!isModuleNavEnabled('theme-kits')"
                        :title="!isModuleNavEnabled('theme-kits') ? t('moduleDisabled') : ''"
                        @click="setTab('theme-kits')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    </div>
                    <span x-text="t('navTheme')">Theme Kits</span>
                    <?php if (!$is_on('theme_kits')): ?>
                        <span class="bankai-nav-off-badge" x-show="!isModuleNavEnabled('theme-kits')">OFF</span>
                    <?php endif; ?>
                </button>

                <button type="button" id="nav-tab-seo" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'seo-engine', 'is-disabled': !isModuleNavEnabled('seo-engine') }"
                        :disabled="!isModuleNavEnabled('seo-engine')"
                        :title="!isModuleNavEnabled('seo-engine') ? t('moduleDisabled') : ''"
                        @click="setTab('seo-engine')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </div>
                    <span x-text="t('navSeo')">SEO Engine</span>
                    <span class="bankai-nav-off-badge" x-show="!isModuleNavEnabled('seo-engine')" x-cloak>OFF</span>
                </button>

                <button type="button" id="nav-tab-speed" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'speed-cache', 'is-disabled': !isModuleNavEnabled('speed-cache') }"
                        :disabled="!isModuleNavEnabled('speed-cache')"
                        :title="!isModuleNavEnabled('speed-cache') ? t('moduleDisabled') : ''"
                        @click="setTab('speed-cache')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <span x-text="t('navSpeed')">Speed &amp; Cache</span>
                    <span class="bankai-nav-off-badge" x-show="!isModuleNavEnabled('speed-cache')" x-cloak>OFF</span>
                </button>

                <button type="button" id="nav-tab-media" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'media-watermark', 'is-disabled': !isModuleNavEnabled('media-watermark') }"
                        :disabled="!isModuleNavEnabled('media-watermark')"
                        :title="!isModuleNavEnabled('media-watermark') ? t('moduleDisabled') : ''"
                        @click="setTab('media-watermark')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    </div>
                    <span x-text="t('navMedia')">Media</span>
                    <span class="bankai-nav-off-badge" x-show="!isModuleNavEnabled('media-watermark')" x-cloak>OFF</span>
                </button>

                <button type="button" id="nav-tab-ai" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'ai-studio', 'is-disabled': !isModuleNavEnabled('ai-studio') }"
                        :disabled="!isModuleNavEnabled('ai-studio')"
                        :title="!isModuleNavEnabled('ai-studio') ? t('moduleDisabled') : ''"
                        @click="setTab('ai-studio')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2a4 4 0 0 1 4 4v1a4 4 0 0 1-8 0V6a4 4 0 0 1 4-4z"/><path d="M6 10v1a6 6 0 0 0 12 0v-1"/><path d="M12 17v5M8 22h8"/></svg>
                    </div>
                    <span x-text="t('navAi')">AI Studio</span>
                    <span class="bankai-nav-off-badge" x-show="!isModuleNavEnabled('ai-studio')" x-cloak>OFF</span>
                </button>

                <button type="button" id="nav-tab-settings" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'settings-license' }"
                        @click="setTab('settings-license')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <span x-text="t('navSettings')">Settings</span>
                </button>
            </div>
        </nav>
    </div>

    <div class="bankai-telemetry-card">
        <div class="telemetry-header">
            <div class="telemetry-status">
                <span class="ping-container"><span class="ping-pulse"></span><span class="ping-dot"></span></span>
                <span class="status-title" x-text="t('allSystemsNormal')">All Systems Normal</span>
            </div>
            <span class="php-badge">PHP <?php echo esc_html(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION); ?></span>
        </div>
        <div class="telemetry-progress-wrapper">
            <div class="telemetry-progress-label">
                <span x-text="t('memoryLimit')">Memory</span>
                <span class="memory-value">
                    <?php
                    $memory_used  = function_exists('memory_get_usage') ? size_format((int) memory_get_usage(true)) : '—';
                    $memory_limit = (string) ini_get('memory_limit');
                    echo esc_html($memory_used . ' / ' . ($memory_limit !== '' ? $memory_limit : '—'));
                    ?>
                </span>
            </div>
        </div>
    </div>
</aside>

<style>
.bankai-nav-btn.is-disabled,
.bankai-nav-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed !important;
    filter: grayscale(0.6);
}
.bankai-nav-off-badge {
    margin-inline-start: auto;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.04em;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(166, 18, 45, 0.12);
    color: #A6122D;
    border: 1px solid rgba(166, 18, 45, 0.25);
}
</style>
