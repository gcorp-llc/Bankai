<?php
defined('ABSPATH') || exit;
?>
<style>
    [x-cloak] { display: none !important; }
    .bankai-admin-wrap {
        background-color: #F6F8FA;
        color: #1F2328;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        margin-inline-start: -20px;
        padding: 0;
        min-height: calc(100vh - 32px);
    }
    .rtl .bankai-admin-wrap {
        font-family: 'Vazirmatn', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
</style>

<div id="bankai-admin-app" class="bankai-admin-wrap" x-data="bankaiAdmin()" x-cloak>
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
    <?php require_once BANKAI_CORE_VIEWS_DIR . 'admin/toast.php'; ?>

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
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
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
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak></div>

    <!-- Top Header Navigation -->
    <?php require_once BANKAI_CORE_VIEWS_DIR . 'admin/header.php'; ?>

    <!-- Main Body Layout -->
    <div class="bankai-body-layout">
        <!-- Sidebar Navigation -->
        <?php require_once BANKAI_CORE_VIEWS_DIR . 'admin/sidebar.php'; ?>

        <!-- Main Tabbed Viewport -->
        <main id="bankai-main-content">
            <div x-show="pageLoading"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
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
                    require_once $tab_file;
                }
            }
            ?>
        </main>
    </div>
</div>