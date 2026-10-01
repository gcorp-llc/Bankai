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
<aside id="bankai-admin-sidebar" class="bankai-sidebar">
    <div class="bankai-sidebar-content">
        <div class="bankai-sidebar-mobile-header" id="bankai-mobile-header" style="display:none;">
            <div class="bankai-mobile-title">
                <div class="bankai-mobile-icon-box">
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </div>
                <span><?php echo is_rtl() ? 'منوی مدیریت' : 'Navigation'; ?></span>
            </div>
            <button type="button" class="bankai-mobile-close-btn" id="btn-close-mobile-menu" aria-label="Close">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="bankai-brand-card">
            <div class="bankai-brand-logo">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <div class="bankai-brand-info">
                <div class="bankai-brand-title">BANKAI CORE</div>
                <div class="bankai-brand-status">
                    <span class="status-dot"></span>
                    <span><?php echo is_rtl() ? 'نسخه تجاری فعال' : 'Active Enterprise'; ?></span>
                </div>
            </div>
        </div>

        <nav class="bankai-nav-menu" aria-label="<?php esc_attr_e('Bankai navigation', 'bankai-core'); ?>">
            <div class="bankai-nav-group">

                <!-- 1. پیشخوان و سلامت -->
                <button type="button" id="nav-tab-overview" class="bankai-nav-btn" data-tab="overview">
                    <div class="bankai-nav-icon"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></div>
                    <span><?php echo esc_html__('پیشخوان و سلامت', 'bankai-core'); ?></span>
                </button>

                <!-- 2. سئو و اسکیما -->
                <button type="button" id="nav-tab-seo-engine" class="bankai-nav-btn" data-tab="seo-engine" data-module="seo_engine">
                    <div class="bankai-nav-icon"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg></div>
                    <span><?php echo esc_html__('سئو و اسکیما', 'bankai-core'); ?></span>
                    <span class="bankai-nav-off-badge" style="display:none;">OFF</span>
                </button>

                <!-- 3. AI -->
                <button type="button" id="nav-tab-ai-studio" class="bankai-nav-btn" data-tab="ai-studio" data-module="ai_studio">
                    <div class="bankai-nav-icon"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="8" width="16" height="12" rx="3"/><path d="M12 8V5M9 12h.01M15 12h.01M9 16h6"/></svg></div>
                    <span><?php echo esc_html__('AI', 'bankai-core'); ?></span>
                    <span class="bankai-nav-off-badge" style="display:none;">OFF</span>
                </button>

                <!-- 4. مدیریت مقالات -->
                <button type="button" id="nav-tab-articles" class="bankai-nav-btn" data-tab="articles">
                    <div class="bankai-nav-icon"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h12a2 2 0 0 1 2 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 0 1 2-2z"/><path d="M8 8h8M8 12h6"/></svg></div>
                    <span><?php echo esc_html__('مدیریت مقالات', 'bankai-core'); ?></span>
                </button>

                <!-- 5. کش و سرعت -->
                <button type="button" id="nav-tab-speed-cache" class="bankai-nav-btn" data-tab="speed-cache" data-module="speed_cache">
                    <div class="bankai-nav-icon"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
                    <span><?php echo esc_html__('سرعت و کش', 'bankai-core'); ?></span>
                    <span class="bankai-nav-off-badge" style="display:none;">OFF</span>
                </button>

                <!-- 6. تصاویر و واترمارک -->
                <button type="button" id="nav-tab-media-watermark" class="bankai-nav-btn" data-tab="media-watermark" data-module="media_watermark">
                    <div class="bankai-nav-icon"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="14" height="14" rx="2"/><path d="M21 9v10a2 2 0 0 1-2 2H7"/><circle cx="9" cy="11" r="1.5"/><path d="M3 15l4-3 3 2 4-4 3 3"/></svg></div>
                    <span><?php echo esc_html__('تصاویر و واترمارک', 'bankai-core'); ?></span>
                    <span class="bankai-nav-off-badge" style="display:none;">OFF</span>
                </button>

                <!-- 7. کیت‌های طراحی -->
                <button type="button" id="nav-tab-theme-kits" class="bankai-nav-btn" data-tab="theme-kits">
                    <div class="bankai-nav-icon"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg></div>
                    <span><?php echo esc_html__('کیت‌های طراحی', 'bankai-core'); ?></span>
                </button>

                <!-- 8. تنظیمات و لایسنس -->
                <button type="button" id="nav-tab-settings-license" class="bankai-nav-btn" data-tab="settings-license">
                    <div class="bankai-nav-icon"><svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg></div>
                    <span><?php echo esc_html__('تنظیمات و لایسنس', 'bankai-core'); ?></span>
                </button>

            </div>
        </nav>
    </div>

    <div class="bankai-telemetry-card">
        <div class="telemetry-header">
            <div class="telemetry-status">
                <span class="ping-container"><span class="ping-pulse"></span><span class="ping-dot"></span></span>
                <span class="status-title"><?php echo is_rtl() ? 'همه سیستم‌ها عادی' : 'All Systems Normal'; ?></span>
            </div>
            <span class="php-badge">PHP <?php echo esc_html(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION); ?></span>
        </div>
        <div class="telemetry-progress-wrapper">
            <div class="telemetry-progress-label">
                <span><?php echo is_rtl() ? 'حافظه' : 'Memory'; ?></span>
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
