<?php
defined('ABSPATH') || exit;

$state = is_array($state ?? null) ? $state : [];

$active_tab    = sanitize_key($state['activeTab'] ?? 'overview');
$active_subtab = sanitize_key($state['activeSubtab'] ?? '');
$is_rtl        = !empty($state['isRtl']) || is_rtl();

$dir_attr   = $is_rtl ? 'rtl' : 'ltr';
$class_attr = $is_rtl ? 'bankai-admin-wrap rtl' : 'bankai-admin-wrap ltr';
?>
<div id="bankai-admin-app"
     class="<?php echo esc_attr($class_attr); ?>"
     dir="<?php echo esc_attr($dir_attr); ?>"
     data-active-tab="<?php echo esc_attr($active_tab); ?>"
     data-active-subtab="<?php echo esc_attr($active_subtab); ?>">

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

    <!-- Page Progress Track (Hidden by default) -->
    <div id="bankai-page-loader"
         class="bankai-page-progress-track"
         hidden
         role="progressbar"
         aria-live="polite">
        <div class="bankai-page-progress-bar" id="bankai-page-progress-bar" style="width:0%"></div>
    </div>

    <!-- Header -->
    <?php
    $header_file = defined('BANKAI_CORE_VIEWS_DIR') ? BANKAI_CORE_VIEWS_DIR . 'admin/header.php' : '';
    if ($header_file && is_file($header_file)) {
        include $header_file;
    }
    ?>

    <!-- Body Layout -->
    <div class="bankai-body-layout">

        <!-- Mobile Overlay (Hidden by default) -->
        <div id="bankai-mobile-overlay"
             class="bankai-mobile-overlay"
             hidden
             aria-hidden="true"></div>

        <!-- Sidebar -->
        <?php
        $sidebar_file = defined('BANKAI_CORE_VIEWS_DIR') ? BANKAI_CORE_VIEWS_DIR . 'admin/sidebar.php' : '';
        if ($sidebar_file && is_file($sidebar_file)) {
            include $sidebar_file;
        }
        ?>

        <!-- Main Content -->
        <main id="bankai-main-content" class="bankai-main-content">
            <div id="bankai-tab-loading-overlay"
                 class="bankai-tab-loading-overlay"
                 hidden
                 role="status"
                 aria-live="polite">
                <div class="bankai-loading-pill">
                    <svg class="bankai-spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="2.5"/>
                        <path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    <span>
                        <?php esc_html_e('در حال بارگذاری بخش...', 'bankai-core'); ?>
                    </span>
                </div>
            </div>

            <?php
            $tabs = class_exists('Bankai_Admin_Menu')
                ? Bankai_Admin_Menu::get_allowed_tabs()
                : [
                    'overview',
                    'seo-engine',
                    'ai-studio',
                    'articles',
                    'speed-cache',
                    'media-watermark',
                    'theme-kits',
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
