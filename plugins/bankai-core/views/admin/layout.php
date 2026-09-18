<?php
defined('ABSPATH') || exit;

$state = is_array($state ?? null) ? $state : [];

$active_tab = sanitize_key($state['activeTab'] ?? 'overview');
$is_rtl     = !empty($state['isRtl']) || is_rtl();

$bankai_data = [
    'activeTab'  => $active_tab,
    'isRtl'      => (bool) $is_rtl,
    'restUrl'    => esc_url_raw(rest_url('bankai/v1/')),
    'nonce'      => wp_create_nonce('wp_rest'),
    'adminNonce' => wp_create_nonce('bankai_admin_nonce'),
    'ajaxUrl'    => admin_url('admin-ajax.php'),
    'locale'     => get_user_locale(),
    'version'    => defined('BANKAI_CORE_VERSION') ? BANKAI_CORE_VERSION : '1.0.0',
];

// هلپر اختصاصی جهت فراخوانی ایمن فایل‌های نمایشی
$views_dir = defined('BANKAI_CORE_VIEWS_DIR') ? BANKAI_CORE_VIEWS_DIR : '';
$load_view = function (string $rel_path) use ($views_dir) {
    if ($views_dir !== '') {
        $file = $views_dir . ltrim($rel_path, '/');
        if (is_file($file)) {
            include $file;
        }
    }
};
?>
<div id="bankai-admin-app"
     class="bankai-admin-wrap"
     x-data="bankaiAdmin()"
     :dir="isRtl ? 'rtl' : 'ltr'"
     :class="{ 'rtl': isRtl, 'ltr': !isRtl }">

    <!-- Bridge PHP → Alpine -->
    <script>
        window.bankaiData = <?php echo wp_json_encode($bankai_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.bankaiCoreData = window.bankaiData;

        window.setTab = function (tab) {
            return (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.setTab === 'function')
                ? window.bankaiAdminInstance.setTab(tab)
                : false;
        };

        window.showToast = function (message, type) {
            type = type || 'success';
            return (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.showToast === 'function')
                ? window.bankaiAdminInstance.showToast(message, type)
                : false;
        };

        window.toggleLanguage = function () {
            return (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.toggleLanguage === 'function')
                ? window.bankaiAdminInstance.toggleLanguage()
                : false;
        };

        window.toggleRtl = window.toggleLanguage;
    </script>

    <!-- Ultra-Modern Glassmorphism Page Loader -->
    <div id="bankai-page-loader"
         class="bankai-loader-overlay"
         x-show="pageLoading"
         x-cloak
         x-transition:enter="bankai-transition-enter"
         x-transition:enter-start="bankai-transition-start"
         x-transition:enter-end="bankai-transition-end"
         x-transition:leave="bankai-transition-leave"
         x-transition:leave-start="bankai-transition-end"
         x-transition:leave-end="bankai-transition-start"
         role="dialog"
         aria-modal="true">

        <div class="bankai-loader-card">
            <!-- لوگو و حلقه‌های چرخان اروبیتال -->
            <div class="bankai-loader-brand">
                <div class="bankai-spinner-ring"></div>
                <div class="bankai-spinner-ring-inner"></div>
                <div class="bankai-loader-logo">
                    <img src="<?php echo esc_url(bankai_asset_url('images/logo.jpg')); ?>" 
                         alt="Bankai Core" 
                         width="44"
                         height="44"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <span class="bankai-logo-fallback" style="display: none;">B</span>
                </div>
            </div>

            <!-- اطلاعات و وضعیت لودینگ -->
            <div class="bankai-loader-info">
                <h3 class="bankai-loader-title">
                    BANKAI <span class="bankai-loader-badge">CORE</span>
                </h3>
                <p class="bankai-loader-status" 
                   x-text="isRtl ? 'در حال بارگذاری و همگام‌سازی هسته...' : 'Loading core components...'"></p>
            </div>

            <!-- نوار پیشرفت و درصد -->
            <div class="bankai-loader-progress-wrap">
                <div class="bankai-loader-track">
                    <div class="bankai-loader-fill" :style="{ width: (pageProgress || 5) + '%' }"></div>
                </div>
                <span class="bankai-loader-percentage" x-text="Math.round(pageProgress || 0) + '%'">0%</span>
            </div>
        </div>
    </div>

    <!-- Ambient Background Decorations -->
    <div class="bankai-bg-decorations" aria-hidden="true">
        <div class="bankai-ambient-grid"></div>
        <div class="bankai-ambient-orb bankai-orb-1"></div>
        <div class="bankai-ambient-orb bankai-orb-2"></div>
        <div class="bankai-ambient-orb bankai-orb-3"></div>
        <div class="bankai-ambient-particles">
            <span class="bankai-particle p-1"></span>
            <span class="bankai-particle p-2"></span>
            <span class="bankai-particle p-3"></span>
            <span class="bankai-particle p-4"></span>
            <span class="bankai-particle p-5"></span>
        </div>
    </div>

    <!-- Toast Component -->
    <?php $load_view('admin/toast.php'); ?>

    <!-- Save Loader Overlay -->
    <div id="bankai-save-loader"
         class="bankai-save-loader-overlay"
         x-show="savingLoader && savingLoader.show"
         x-cloak
         x-transition.opacity
         role="status"
         aria-live="polite"
         aria-atomic="true">
        <div class="bankai-save-loader-card"
             :class="{ 'bankai-save-loader-success': savingLoader.state === 'saved' }">
            <template x-if="savingLoader.state === 'saving'">
                <div class="bankai-save-spinner-wrap">
                    <svg class="bankai-spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" style="width:20px; height:20px; flex-shrink:0;" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="2.5"/>
                        <path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </div>
            </template>
            <template x-if="savingLoader.state === 'saved'">
                <div class="bankai-save-success-wrap">
                    <svg class="solar-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px; flex-shrink:0;" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </template>
            <div class="bankai-save-loader-content">
                <div class="bankai-save-loader-title" style="display: flex; align-items: center; gap: 6px;">
                    <span x-text="savingLoader.title"><?php esc_html_e('Saving Changes...', 'bankai-core'); ?></span>
                    <span x-show="savingLoader.state === 'saving'" class="bankai-pulse-dot" aria-hidden="true"></span>
                </div>
                <div class="bankai-save-loader-message" x-text="savingLoader.message">
                    <?php esc_html_e('Applying updates to server & synchronizing cache...', 'bankai-core'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Overlay -->
    <div class="bankai-mobile-overlay"
         x-show="mobileMenuOpen"
         x-cloak
         x-transition.opacity
         @click="closeMobileMenu()"
         aria-hidden="true"></div>

    <!-- Header View -->
    <?php $load_view('admin/header.php'); ?>

    <!-- Body Layout -->
    <div class="bankai-body-layout">

        <!-- Sidebar View -->
        <?php $load_view('admin/sidebar.php'); ?>

        <!-- Main Content Area -->
        <main id="bankai-main-content" class="bankai-main-content">
            <!-- Overlay لودینگ تب‌های داخلی -->
            <div class="bankai-tab-loading-overlay"
                 x-show="pageLoading"
                 x-cloak
                 x-transition.opacity
                 role="status"
                 aria-live="polite">
                <div class="bankai-loading-pill">
                    <svg class="bankai-spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" style="width:18px; height:18px; flex-shrink:0;" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="2.5"/>
                        <path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    <span x-text="isRtl ? 'در حال بارگذاری بخش...' : 'Loading section...'">
                        <?php esc_html_e('Loading section...', 'bankai-core'); ?>
                    </span>
                </div>
            </div>

            <?php
            $tabs = [
                'overview',
                'theme-kits',
                'seo-engine',
                'speed-cache',
                'media-watermark',
                'ai-studio',
                'settings-license',
            ];

            foreach ($tabs as $tab) {
                $load_view('admin/tab-' . sanitize_key($tab) . '.php');
            }
            ?>
        </main>
    </div>
</div>