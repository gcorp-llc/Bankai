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

<div id="bankai-admin-app" class="bankai-admin-wrap" dir="<?php echo $is_rtl ? 'rtl' : 'ltr'; ?>" x-data="bankaiAdmin">
    <!-- Toast Notice Floating Container -->
    <div class="bankai-toast"
         :class="toast.type === 'error' ? 'bankai-toast-error' : 'bankai-toast-success'"
         x-show="toast.show"
         x-transition
         style="display: none;">
        <span x-text="toast.message"></span>
    </div>

    <!-- Sidebar Navigation -->
    <?php
    $sidebar_file = BANKAI_CORE_PATH . 'templates/admin/sidebar.php';
    if ( file_exists( $sidebar_file ) ) {
        include $sidebar_file;
    }
    ?>

    <!-- Main Dynamic Content Workspace -->
    <main class="bankai-content">
        <!-- Modular Header -->
        <?php
        $header_file = BANKAI_CORE_PATH . 'templates/admin/header.php';
        if ( file_exists( $header_file ) ) {
            include $header_file;
        }
        ?>

        <!-- View 1: Overview & Health Dashboard -->
        <?php
        $dashboard_file = BANKAI_CORE_PATH . 'templates/admin/dashboard.php';
        if ( file_exists( $dashboard_file ) ) {
            include $dashboard_file;
        }
        ?>

        <!-- View 2: Theme & Starter Kits Importer -->
        <?php
        $theme_kits_file = BANKAI_CORE_PATH . 'templates/admin/theme-kits.php';
        if ( file_exists( $theme_kits_file ) ) {
            include $theme_kits_file;
        }
        ?>

        <!-- View 3: SEO Engine -->
        <?php
        $seo_engine_file = BANKAI_CORE_PATH . 'templates/admin/seo-engine.php';
        if ( file_exists( $seo_engine_file ) ) {
            include $seo_engine_file;
        }
        ?>

        <!-- View 4: Speed & Cache Engine -->
        <?php
        $speed_cache_file = BANKAI_CORE_PATH . 'templates/admin/speed-cache.php';
        if ( file_exists( $speed_cache_file ) ) {
            include $speed_cache_file;
        }
        ?>

        <!-- View 5: Media & Dynamic Watermark Studio -->
        <?php
        $media_watermark_file = BANKAI_CORE_PATH . 'templates/admin/media-watermark.php';
        if ( file_exists( $media_watermark_file ) ) {
            include $media_watermark_file;
        }
        ?>

        <!-- View 6: AI Content Studio & Multi-LLM Orchestrator -->
        <?php
        $ai_studio_file = BANKAI_CORE_PATH . 'templates/admin/ai-studio.php';
        if ( file_exists( $ai_studio_file ) ) {
            include $ai_studio_file;
        }
        ?>

        <!-- View 7: Settings, License & System Tools Hub -->
        <?php
        $settings_license_file = BANKAI_CORE_PATH . 'templates/admin/settings-license.php';
        if ( file_exists( $settings_license_file ) ) {
            include $settings_license_file;
        }
        ?>
    </main>
</div>
