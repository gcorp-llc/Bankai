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
            if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.setTab === 'function') {
                return window.bankaiAdminInstance.setTab(tab);
            }
            return false;
        };

        window.showToast = function (message, type) {
            type = type || 'success';
            if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.showToast === 'function') {
                return window.bankaiAdminInstance.showToast(message, type);
            }
            return false;
        };

        window.toggleLanguage = function () {
            if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.toggleLanguage === 'function') {
                return window.bankaiAdminInstance.toggleLanguage();
            }
            return false;
        };

        window.toggleRtl = window.toggleLanguage;
    </script>

    <!-- Ambient Background -->
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

    <!-- Toast -->
    <?php
    $toast_file = defined('BANKAI_CORE_VIEWS_DIR') ? BANKAI_CORE_VIEWS_DIR . 'admin/toast.php' : '';
    if ($toast_file && is_file($toast_file)) {
        include $toast_file;
    }
    ?>

    <!-- Page Progress -->
    <div id="bankai-page-loader"
         class="bankai-page-progress-track"
         x-show="pageLoading"
         x-cloak
         role="progressbar"
         aria-live="polite">
        <div class="bankai-page-progress-bar" :style="{ width: pageProgress + '%' }"></div>
    </div>

    <!-- Save Loader -->
    <div id="bankai-save-loader"
         class="bankai-save-loader-overlay"
         x-show="savingLoader.show"
         x-cloak
         x-transition.opacity
         role="status"
         aria-live="polite"
         aria-atomic="true">
        <div class="bankai-save-loader-card"
             :class="{ 'bankai-save-loader-success': savingLoader.state === 'saved' }">
            <template x-if="savingLoader.state === 'saving'">
                <div class="bankai-save-spinner-wrap">
                    <svg class="bankai-spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="2.5"/>
                        <path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </div>
            </template>
            <template x-if="savingLoader.state === 'saved'">
                <div class="bankai-save-success-wrap">
                    <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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

    <!-- Mobile Overlay -->
    <div class="bankai-mobile-overlay"
         x-show="mobileMenuOpen"
         x-cloak
         x-transition.opacity
         @click="closeMobileMenu()"
         aria-hidden="true"></div>

    <!-- Header -->
    <?php
    $header_file = defined('BANKAI_CORE_VIEWS_DIR') ? BANKAI_CORE_VIEWS_DIR . 'admin/header.php' : '';
    if ($header_file && is_file($header_file)) {
        include $header_file;
    }
    ?>

    <!-- Body Layout -->
    <div class="bankai-body-layout">

        <!-- Sidebar -->
        <?php
        $sidebar_file = defined('BANKAI_CORE_VIEWS_DIR') ? BANKAI_CORE_VIEWS_DIR . 'admin/sidebar.php' : '';
        if ($sidebar_file && is_file($sidebar_file)) {
            include $sidebar_file;
        }
        ?>

        <!-- Main Content -->
        <main id="bankai-main-content" class="bankai-main-content">
            <div class="bankai-tab-loading-overlay"
                 x-show="pageLoading"
                 x-cloak
                 x-transition.opacity
                 role="status"
                 aria-live="polite">
                <div class="bankai-loading-pill">
                    <svg class="bankai-spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
                $tab      = sanitize_key($tab);
                $tab_file = defined('BANKAI_CORE_VIEWS_DIR')
                    ? BANKAI_CORE_VIEWS_DIR . "admin/tab-{$tab}.php"
                    : '';

                if ($tab_file && is_file($tab_file)) {
                    include $tab_file;
                }
            }
            ?>
        </main>
    </div>
</div>