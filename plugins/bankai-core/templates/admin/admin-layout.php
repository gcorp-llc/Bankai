<?php
/**
 * Master Admin Layout Template for Bankai Core
 *
 * @package Bankai_Core
 */

defined('ABSPATH') || exit;
?>
<div id="bankai-admin-app"
     x-data="bankaiAdmin()"
     x-init="init()"
     :dir="isRtl ? 'rtl' : 'ltr'"
     :class="isRtl ? 'rtl font-vazir' : 'ltr font-sans'"
     style="position: relative; min-height: 100vh; background-color: #F6F8FA; color: #1F2328; -webkit-font-smoothing: antialiased;">

    <!-- Background Subtle Matrix Grid & Particles -->
    <div class="bankai-bg-grid" aria-hidden="true">
        <div class="bankai-particles">
            <span class="bankai-particle p-1"></span>
            <span class="bankai-particle p-2"></span>
            <span class="bankai-particle p-3"></span>
            <span class="bankai-particle p-4"></span>
            <span class="bankai-particle p-5"></span>
        </div>
    </div>

    <!-- Toast Notification Component -->
    <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/toast.php'; ?>

    <!-- Page Switch Progress Bar & Floating Save Loader -->
    <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/loaders.php'; ?>

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

    <!-- Top Header Navigation Bar -->
    <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/header.php'; ?>

    <!-- Main Body Layout: Floating Canvas with Margins from Header & Edges -->
    <div class="bankai-body-layout">
        <!-- Sidebar Navigation (Floating Card) -->
        <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/sidebar.php'; ?>

        <!-- Main Tabbed Viewport (Floating Content Area) -->
        <main id="bankai-main-content">
            <!-- Tab Switching Loading Indicator -->
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
                    <span x-text="isRtl ? 'در حال بارگذاری بخش...' : 'Loading section...'">Loading section...</span>
                </div>
            </div>

            <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/tab-overview.php'; ?>
            <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/tab-theme-kits.php'; ?>
            <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/tab-seo-engine.php'; ?>
            <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/tab-speed-cache.php'; ?>
            <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/tab-media-watermark.php'; ?>
            <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/tab-ai-studio.php'; ?>
            <?php include BANKAI_CORE_TEMPLATE_DIR . 'admin/tab-settings-license.php'; ?>
        </main>
    </div>
</div>
