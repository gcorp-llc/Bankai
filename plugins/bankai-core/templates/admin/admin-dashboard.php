<?php
/**
 * Bankai Core - Master Native SSR Admin View Layout Template
 * Powered by Alpine.js, HTMX, Tailwind CSS and Native PHP SSR
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$is_rtl = is_rtl();
?>

<div id="bankai-admin-app" x-data="bankaiAdmin" class="bg-[#080C14] text-slate-100 min-h-screen flex flex-col md:flex-row rtl:flex-row-reverse gap-6 p-6 bankai-admin-wrap" dir="<?php echo $is_rtl ? 'rtl' : 'ltr'; ?>">
    <!-- Toast Notice Floating Container -->
    <div class="bankai-toast"
         :class="toast.type === 'error' ? 'bankai-toast-error' : 'bankai-toast-success'"
         x-show="toast.show"
         x-cloak
         x-transition
         style="display: none;">
        <span x-text="toast.message"></span>
    </div>

    <!-- Sidebar Navigation Container -->
    <aside class="w-full md:w-64 flex-shrink-0">
        <?php
        $sidebar_file = BANKAI_CORE_PATH . 'templates/admin/sidebar.php';
        if ( file_exists( $sidebar_file ) ) {
            include $sidebar_file;
        }
        ?>
    </aside>

    <!-- Main Dynamic Content Workspace Container -->
    <main class="flex-1 min-w-0 bankai-content">
        <!-- Modular Header -->
        <?php
        $header_file = BANKAI_CORE_PATH . 'templates/admin/header.php';
        if ( file_exists( $header_file ) ) {
            include $header_file;
        }
        ?>

        <!-- View 1: Overview & Health Dashboard -->
        <div x-show="activeTab === 'overview'" x-cloak x-transition>
            <?php
            $dashboard_file = BANKAI_CORE_PATH . 'templates/admin/dashboard.php';
            if ( file_exists( $dashboard_file ) ) {
                include $dashboard_file;
            }
            ?>
        </div>

        <!-- View 2: Theme & Starter Kits Importer -->
        <div x-show="activeTab === 'theme-kits'" x-cloak x-transition>
            <?php
            $theme_kits_file = BANKAI_CORE_PATH . 'templates/admin/theme-kits.php';
            if ( file_exists( $theme_kits_file ) ) {
                include $theme_kits_file;
            }
            ?>
        </div>

        <!-- View 3: SEO Engine -->
        <div x-show="activeTab === 'seo'" x-cloak x-transition>
            <?php
            $seo_engine_file = BANKAI_CORE_PATH . 'templates/admin/seo-engine.php';
            if ( file_exists( $seo_engine_file ) ) {
                include $seo_engine_file;
            }
            ?>
        </div>

        <!-- View 4: Speed & Cache Engine -->
        <div x-show="activeTab === 'speed'" x-cloak x-transition>
            <?php
            $speed_cache_file = BANKAI_CORE_PATH . 'templates/admin/speed-cache.php';
            if ( file_exists( $speed_cache_file ) ) {
                include $speed_cache_file;
            }
            ?>
        </div>

        <!-- View 5: Media & Dynamic Watermark Studio -->
        <div x-show="activeTab === 'media'" x-cloak x-transition>
            <?php
            $media_watermark_file = BANKAI_CORE_PATH . 'templates/admin/media-watermark.php';
            if ( file_exists( $media_watermark_file ) ) {
                include $media_watermark_file;
            }
            ?>
        </div>

        <!-- View 6: AI Content Studio & Multi-LLM Orchestrator -->
        <div x-show="activeTab === 'ai'" x-cloak x-transition>
            <?php
            $ai_studio_file = BANKAI_CORE_PATH . 'templates/admin/ai-studio.php';
            if ( file_exists( $ai_studio_file ) ) {
                include $ai_studio_file;
            }
            ?>
        </div>

        <!-- View 7: Settings, License & System Tools Hub -->
        <div x-show="activeTab === 'settings'" x-cloak x-transition>
            <?php
            $settings_license_file = BANKAI_CORE_PATH . 'templates/admin/settings-license.php';
            if ( file_exists( $settings_license_file ) ) {
                include $settings_license_file;
            }
            ?>
        </div>
    </main>
</div>
