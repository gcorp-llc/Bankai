<?php
defined('ABSPATH') || exit;


/** Server telemetry for sidebar */
if (!function_exists('bankai_sidebar_telemetry')) {
    function bankai_sidebar_telemetry(): array {
        global $wpdb;
        $mem_limit = ini_get('memory_limit');
        $mem_usage = function_exists('memory_get_usage') ? size_format(memory_get_usage(true)) : '—';
        $mysql = '—';
        if (isset($wpdb) && $wpdb instanceof wpdb) {
            $mysql = $wpdb->db_server_info() ?: ($wpdb->db_version() ?: '—');
        }
        $cpu = function_exists('sys_getloadavg') ? sys_getloadavg() : false;
        $load = is_array($cpu) ? number_format((float) $cpu[0], 2) : '—';
        $disk_free = function_exists('disk_free_space') ? @disk_free_space(ABSPATH) : false;
        $disk_total = function_exists('disk_total_space') ? @disk_total_space(ABSPATH) : false;
        $disk = ($disk_free && $disk_total)
            ? size_format($disk_free) . ' / ' . size_format($disk_total)
            : '—';
        $host = wp_parse_url(home_url(), PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? '—');
        $max_exec = ini_get('max_execution_time') ?: '—';
        $upload = size_format(wp_max_upload_size());
        return [
            'php' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION,
            'mysql' => is_string($mysql) ? $mysql : '—',
            'memory_limit' => $mem_limit ?: '—',
            'memory_usage' => $mem_usage,
            'load' => $load,
            'disk' => $disk,
            'domain' => $host,
            'wp' => get_bloginfo('version'),
            'upload_max' => $upload,
            'max_exec' => $max_exec . (is_numeric($max_exec) ? 's' : ''),
            'os' => PHP_OS_FAMILY ?: PHP_OS,
            'sapi' => PHP_SAPI,
        ];
    }
}
$bk_tel = bankai_sidebar_telemetry();



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
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </div>
                <span x-text="isRtl ? 'منوی مدیریت' : 'Navigation'">Navigation</span>
            </div>
            <button type="button" class="bankai-mobile-close-btn" @click="closeMobileMenu()" aria-label="Close">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="bankai-brand-card bankai-ekit-brand">
            <div class="bankai-brand-logo">
                <iconify-icon icon="solar:bolt-bold-duotone" width="22" height="22" aria-hidden="true"></iconify-icon>
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

                <!-- 1. پیشخوان و سلامت -->
                <button type="button" id="nav-tab-overview" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'overview' }"
                        @click="setTab('overview')">
                    <div class="bankai-nav-icon"><iconify-icon icon="solar:widget-2-bold-duotone" width="20" height="20" aria-hidden="true"></iconify-icon></div>
                    <span><?php echo esc_html__('پیشخوان و سلامت', 'bankai-core'); ?></span>
                </button>

                <!-- فروشگاه — بلافاصله قبل از مدیریت محتوا -->
                <button type="button" id="nav-tab-store" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'store', 'is-off': !isModuleNavEnabled('store') || !wooActive }"
                        @click="openStoreTab()">
                    <div class="bankai-nav-icon"><iconify-icon icon="solar:shop-bold-duotone" width="20" height="20" aria-hidden="true"></iconify-icon></div>
                    <span><?php echo esc_html__('فروشگاه', 'bankai-core'); ?></span>
                </button>

                <!-- مدیریت محتوا -->
                <button type="button" id="nav-tab-articles" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'articles' }"
                        @click="setTab('articles')">
                    <div class="bankai-nav-icon"><iconify-icon icon="solar:document-text-bold-duotone" width="20" height="20" aria-hidden="true"></iconify-icon></div>
                    <span><?php echo esc_html__('مدیریت محتوا', 'bankai-core'); ?></span>
                </button>

                <!-- 3. موتور سئو و اسکیما -->
                <button type="button" id="nav-tab-seo" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'seo-engine', 'is-off': !isModuleNavEnabled('seo-engine') }"
                        @click="isModuleNavEnabled('seo-engine') ? setTab('seo-engine') : showToast(isRtl ? 'ماژول سئو غیرفعال است — از پیشخوان فعال کنید' : 'SEO module is off', 'warning')">
                    <div class="bankai-nav-icon"><iconify-icon icon="solar:magnifer-bold-duotone" width="20" height="20" aria-hidden="true"></iconify-icon></div>
                    <span><?php echo esc_html__('موتور سئو و اسکیما', 'bankai-core'); ?></span>
                </button>

                <!-- 4. استودیو هوش مصنوعی -->
                <button type="button" id="nav-tab-ai" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'ai-studio', 'is-off': !isModuleNavEnabled('ai-studio') }"
                        @click="isModuleNavEnabled('ai-studio') ? setTab('ai-studio') : showToast(isRtl ? 'استودیو AI غیرفعال است — از پیشخوان فعال کنید' : 'AI Studio is off', 'warning')">
                    <div class="bankai-nav-icon"><iconify-icon icon="solar:ghost-bold-duotone" width="20" height="20" aria-hidden="true"></iconify-icon></div>
                    <span><?php echo esc_html__('استودیو هوش مصنوعی', 'bankai-core'); ?></span>
                </button>

                <!-- 5. کش و سرعت -->
                <button type="button" id="nav-tab-speed" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'speed-cache', 'is-off': !isModuleNavEnabled('speed-cache') }"
                        @click="isModuleNavEnabled('speed-cache') ? setTab('speed-cache') : showToast(isRtl ? 'ماژول کش غیرفعال است' : 'Speed module is off', 'warning')">
                    <div class="bankai-nav-icon"><iconify-icon icon="solar:bolt-bold-duotone" width="20" height="20" aria-hidden="true"></iconify-icon></div>
                    <span><?php echo esc_html__('کش و سرعت', 'bankai-core'); ?></span>
                </button>

                <!-- 6. رسانه و واترمارک -->
                <button type="button" id="nav-tab-media" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'media-watermark', 'is-off': !isModuleNavEnabled('media-watermark') }"
                        @click="isModuleNavEnabled('media-watermark') ? setTab('media-watermark') : showToast(isRtl ? 'ماژول رسانه غیرفعال است' : 'Media module is off', 'warning')">
                    <div class="bankai-nav-icon"><iconify-icon icon="solar:gallery-bold-duotone" width="20" height="20" aria-hidden="true"></iconify-icon></div>
                    <span><?php echo esc_html__('رسانه و واترمارک', 'bankai-core'); ?></span>
                </button>

                <!-- 7. تنظیمات و لایسنس -->
                <button type="button" id="nav-tab-settings" class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'settings-license' }"
                        @click="setTab('settings-license')">
                    <div class="bankai-nav-icon"><iconify-icon icon="solar:settings-bold-duotone" width="20" height="20" aria-hidden="true"></iconify-icon></div>
                    <span><?php echo esc_html__('تنظیمات و لایسنس', 'bankai-core'); ?></span>
                </button>
            </div>
        </nav>
    </div>

    
    <div class="bankai-telemetry-card">
        <div class="telemetry-header">
            <div class="telemetry-status">
                <span class="ping-container"><span class="ping-pulse"></span><span class="ping-dot"></span></span>
                <span class="status-title" style="color:#334155;font-weight:800"><?php echo esc_html__('همه سیستم‌ها عادی', 'bankai-core'); ?></span>
            </div>
            <span class="php-badge">PHP <?php echo esc_html($bk_tel['php']); ?></span>
        </div>
        <div class="telemetry-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;font-size:10px;line-height:1.4;">
            <div class="tel-item"><span style="color:#475569;display:block;font-weight:700">MySQL</span><strong style="color:#0f172a;font-weight:800;font-size:11px;word-break:break-all"><?php echo esc_html(wp_html_excerpt($bk_tel['mysql'], 24, '…')); ?></strong></div>
            <div class="tel-item"><span style="color:#475569;display:block;font-weight:700">حافظه PHP</span><strong style="color:#0f172a;font-weight:800"><?php echo esc_html($bk_tel['memory_usage']); ?> / <?php echo esc_html($bk_tel['memory_limit']); ?></strong></div>
            <div class="tel-item"><span style="color:#475569;display:block;font-weight:700">بار پردازنده</span><strong style="color:#0f172a;font-weight:800"><?php echo esc_html($bk_tel['load']); ?></strong></div>
            <div class="tel-item"><span style="color:#475569;display:block;font-weight:700">دیسک آزاد</span><strong style="color:#0f172a;font-weight:800" font-size:10px"><?php echo esc_html($bk_tel['disk']); ?></strong></div>
            <div class="tel-item" style="grid-column:1/-1"><span style="color:#475569;display:block;font-weight:700">دامنه</span><strong style="color:#0f172a;font-weight:800" dir="ltr" font-size:11px"><?php echo esc_html($bk_tel['domain']); ?></strong></div>
            <div class="tel-item"><span style="color:#475569;display:block;font-weight:700">وردپرس</span><strong style="color:#0f172a;font-weight:800"><?php echo esc_html($bk_tel['wp']); ?></strong></div>
            <div class="tel-item"><span style="color:#475569;display:block;font-weight:700">آپلود</span><strong style="color:#0f172a;font-weight:800"><?php echo esc_html($bk_tel['upload_max']); ?></strong></div>
            <div class="tel-item"><span style="color:#475569;display:block;font-weight:700">max_exec</span><strong style="color:#0f172a;font-weight:800"><?php echo esc_html($bk_tel['max_exec']); ?></strong></div>
            <div class="tel-item"><span style="color:#475569;display:block;font-weight:700">سیستم</span><strong style="color:#0f172a;font-weight:800"><?php echo esc_html($bk_tel['os'] . ' / ' . $bk_tel['sapi']); ?></strong></div>
        </div>
    </div>
</aside>

<style>
/* nav always clickable */
/* off badge removed */
#bankai-admin-sidebar .bankai-nav-icon svg.solar-icon {
    width: 17px; height: 17px; display: block;
}
</style>

<style>
#bankai-admin-sidebar .bankai-nav-btn.is-off{opacity:.45;filter:grayscale(.4)}
#bankai-admin-sidebar .bankai-nav-btn.is-off::after{content:'OFF';font-size:9px;font-weight:900;margin-inline-start:auto;background:#fee2e2;color:#b91c1c;padding:2px 6px;border-radius:999px}
</style>