<?php
defined('ABSPATH') || exit;

/** @var array $state */
$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_rtl     = !empty($state['isRtl']) || is_rtl();

$nav_items = [
    [
        'id'    => 'overview',
        'title' => __('پیشخوان و سلامت', 'bankai-core'),
        'icon'  => '<svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
    ],
    [
        'id'    => 'seo-engine',
        'title' => __('سئو و اسکیما', 'bankai-core'),
        'icon'  => '<svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>',
    ],
    [
        'id'    => 'ai-studio',
        'title' => __('AI', 'bankai-core'),
        'icon'  => '<svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="8" width="16" height="12" rx="3"/><path d="M12 8V5M9 12h.01M15 12h.01M9 16h6"/></svg>',
    ],
    [
        'id'    => 'articles',
        'title' => __('مدیریت مقالات', 'bankai-core'),
        'icon'  => '<svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h12a2 2 0 0 1 2 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 0 1 2-2z"/><path d="M8 8h8M8 12h6"/></svg>',
    ],
    [
        'id'    => 'speed-cache',
        'title' => __('سرعت و کش', 'bankai-core'),
        'icon'  => '<svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
    ],
    [
        'id'    => 'media-watermark',
        'title' => __('تصاویر و واترمارک', 'bankai-core'),
        'icon'  => '<svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="14" height="14" rx="2"/><path d="M21 9v10a2 2 0 0 1-2 2H7"/><circle cx="9" cy="11" r="1.5"/><path d="M3 15l4-3 3 2 4-4 3 3"/></svg>',
    ],
    [
        'id'    => 'theme-kits',
        'title' => __('کیت‌های طراحی', 'bankai-core'),
        'icon'  => '<svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
    ],
    [
        'id'    => 'settings-license',
        'title' => __('تنظیمات و لایسنس', 'bankai-core'),
        'icon'  => '<svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>',
    ],
];
?>
<aside id="bankai-admin-sidebar" class="bankai-sidebar">

    <div class="bankai-sidebar-content">
        <div id="bankai-sidebar-mobile-header" class="bankai-sidebar-mobile-header" hidden>
            <div class="bankai-mobile-title">
                <div class="bankai-mobile-icon-box">
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </div>
                <span><?php echo $is_rtl ? 'منوی مدیریت' : 'Navigation'; ?></span>
            </div>
            <button type="button" id="bankai-mobile-close-btn" class="bankai-mobile-close-btn" aria-label="Close">
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
                    <span><?php echo $is_rtl ? 'نسخه تجاری فعال' : 'Active Enterprise'; ?></span>
                </div>
            </div>
        </div>

        <nav class="bankai-nav-menu" aria-label="<?php esc_attr_e('Bankai navigation', 'bankai-core'); ?>">
            <div class="bankai-nav-group">
                <?php foreach ($nav_items as $item) : ?>
                    <?php
                    $item_id   = $item['id'];
                    $is_active = ($active_tab === $item_id);
                    $btn_class = 'bankai-nav-btn' . ($is_active ? ' active' : '');
                    ?>
                    <button type="button"
                            id="nav-tab-<?php echo esc_attr($item_id); ?>"
                            class="<?php echo esc_attr($btn_class); ?>"
                            data-tab="<?php echo esc_attr($item_id); ?>">
                        <div class="bankai-nav-icon"><?php echo $item['icon']; ?></div>
                        <span><?php echo esc_html($item['title']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </nav>
    </div>

    <div class="bankai-telemetry-card">
        <div class="telemetry-header">
            <div class="telemetry-status">
                <span class="ping-container"><span class="ping-pulse"></span><span class="ping-dot"></span></span>
                <span class="status-title"><?php echo esc_html__('سامانه پایدار است', 'bankai-core'); ?></span>
            </div>
            <span class="php-badge">PHP <?php echo esc_html(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION); ?></span>
        </div>
        <div class="telemetry-progress-wrapper">
            <div class="telemetry-progress-label">
                <span><?php echo esc_html__('حافظه', 'bankai-core'); ?></span>
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
