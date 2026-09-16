<?php
defined('ABSPATH') || exit;
$state = $state ?? [];
$active_tab = sanitize_text_field($state['activeTab'] ?? 'overview');
$is_rtl = !empty($state['isRtl']);
?>

<style>
    [x-cloak] { display: none !important; }
    .bankai-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(31, 35, 40, 0.45);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
    }
</style>

<!-- Global Bridge Data for Alpine.js -->
<script>
    window.bankaiData = {
        activeTab: <?php echo json_encode($active_tab); ?>,
        isRtl: <?php echo json_encode($is_rtl); ?>,
        restUrl: <?php echo json_encode(esc_url_raw(rest_url('bankai/v1/'))); ?>,
        nonce: <?php echo json_encode(wp_create_nonce('bankai_admin_nonce')); ?>
    };

    window.setTab = function(tab) {
        if (window.bankaiAdminInstance) {
            window.bankaiAdminInstance.activeTab = tab;
            return window.bankaiAdminInstance.setTab(tab);
        }
        const app = document.getElementById('bankai-admin-app');
        if (app && window.Alpine) {
            try {
                const data = window.Alpine.$data(app);
                if (data) {
                    data.activeTab = tab;
                    if (typeof data.setTab === 'function') {
                        return data.setTab(tab);
                    }
                }
            } catch(e) {}
        }
    };

    window.showToast = function(msg, type) {
        type = type || 'success';
        if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.showToast === 'function') {
            return window.bankaiAdminInstance.showToast(msg, type);
        }
    };

    window.toggleLanguage = function() {
        if (window.bankaiAdminInstance && typeof window.bankaiAdminInstance.toggleLanguage === 'function') {
            return window.bankaiAdminInstance.toggleLanguage();
        }
    };
    window.toggleRtl = window.toggleLanguage;
</script>

<div id="bankai-admin-app"
     class="bankai-admin-wrap"
     x-data="bankaiAdmin()"
     :dir="isRtl ? 'rtl' : 'ltr'"
     :class="isRtl ? 'rtl' : 'ltr'">
    
    <!-- Ambient Decorative Animated Background Elements -->
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

    <!-- Toast Notification -->
    <?php include BANKAI_CORE_VIEWS_DIR . 'admin/toast.php'; ?>

    <!-- Global Page Switch Progress Bar -->
    <div id="bankai-page-loader"
         class="bankai-page-progress-track"
         x-show="pageLoading"
         x-cloak>
        <div class="bankai-page-progress-bar"
             :style="`width: ${pageProgress}%;`"></div>
    </div>

    <!-- Floating Settings & Actions Saving Loader -->
    <div x-show="savingLoader.show"
         x-cloak
         x-transition.opacity
         class="bankai-save-loader-overlay"
         id="bankai-save-loader">
        <div class="bankai-save-loader-card"
             :class="savingLoader.state === 'saved' ? 'bankai-save-loader-success' : ''">
            <template x-if="savingLoader.state === 'saving'">
                <div class="bankai-save-spinner-wrap">
                    <svg class="bankai-spinner" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="rgba(9, 105, 218, 0.2)" stroke-width="2.5" />
                        <path d="M12 3a9 9 0 0 1 9 9" stroke="#0969DA" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                </div>
            </template>
            <template x-if="savingLoader.state === 'saved'">
                <div class="bankai-save-success-wrap">
                    <svg class="solar-icon" style="color: #1A7F37; width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
            </template>

            <div style="display: flex; flex-direction: column; gap: 2px;">
                <div style="font-size: 13px; font-weight: 700; color: #1F2328; display: flex; align-items: center; gap: 6px;">
                    <span x-text="savingLoader.title"><?php esc_html_e('Saving Changes...', 'bankai-core'); ?></span>
                    <span x-show="savingLoader.state === 'saving'" class="bankai-pulse-dot"></span>
                </div>
                <div style="font-size: 11px; color: #656D76;" x-text="savingLoader.message">
                    <?php esc_html_e('Applying updates to server & synchronizing cache...', 'bankai-core'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Backdrop Overlay -->
    <div class="bankai-mobile-overlay"
         x-show="mobileMenuOpen"
         @click="closeMobileMenu()"
         x-transition.opacity
         x-cloak></div>

    <!-- Top Header Navigation -->
    <?php include BANKAI_CORE_VIEWS_DIR . 'admin/header.php'; ?>

    <!-- Main Body Layout -->
    <div class="bankai-body-layout">
        <!-- Sidebar Navigation -->
        <?php include BANKAI_CORE_VIEWS_DIR . 'admin/sidebar.php'; ?>

        <!-- Main Tabbed Viewport -->
        <main id="bankai-main-content">
            <div x-show="pageLoading"
                 x-cloak
                 x-transition.opacity
                 class="bankai-tab-loading-overlay">
                <div class="bankai-loading-pill">
                    <svg class="bankai-spinner" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="rgba(9, 105, 218, 0.2)" stroke-width="2.5" />
                        <path d="M12 3a9 9 0 0 1 9 9" stroke="#0969DA" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                    <span x-text="isRtl ? 'در حال بارگذاری بخش...' : 'Loading section...'"><?php esc_html_e('Loading section...', 'bankai-core'); ?></span>
                </div>
            </div>

            <!-- Views / Tab Partials -->
            <?php
            $tabs = ['overview', 'theme-kits', 'seo-engine', 'speed-cache', 'media-watermark', 'ai-studio', 'settings-license'];
            foreach ($tabs as $tab) {
                $tab_file = BANKAI_CORE_VIEWS_DIR . "admin/tab-{$tab}.php";
                if (file_exists($tab_file)) {
                    include $tab_file;
                }
            }
            ?>
        </main>
    </div>
</div>