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

$bankai_state = [
    'seoModules'        => $state['seoModules'] ?? [],
    'speedModules'      => $state['speedModules'] ?? [],
    'speedStats'        => $state['speedStats'] ?? [],
    'speedSettings'     => $state['speedSettings'] ?? [],
    'mediaModules'      => $state['mediaModules'] ?? [],
    'watermarkSettings' => $state['watermarkSettings'] ?? [],
    'coreModules'       => $state['coreModules'] ?? [],
    'seoIntegrations'   => $state['seoIntegrations'] ?? [],
    'homeUrl'           => $state['homeUrl'] ?? home_url('/'),
    'stats'             => $state['stats'] ?? [],
    'aiModules'         => $state['aiModules'] ?? [],
    'providers'         => $state['providers'] ?? [],
    'aiDefaultProvider' => $state['aiDefaultProvider'] ?? 'gemini',
];
?>
<div id="bankai-admin-app"
     class="bankai-admin-wrap"
     :dir="isRtl ? 'rtl' : 'ltr'"
     :class="{ 'rtl': isRtl, 'ltr': !isRtl }">

    <!-- Bridge PHP → Vanilla JS -->
    <script>
        window.bankaiData = <?php echo wp_json_encode($bankai_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.bankaiCoreData = window.bankaiData;
        window.bankaiState = <?php echo wp_json_encode($bankai_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

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
    </script>

    <!-- Ambient Background -->
    <div class="bankai-bg-decorations" aria-hidden="true">
        <div class="bankai-ambient-grid"></div>
        <div class="bankai-ambient-orb bankai-orb-1"></div>
        <div class="bankai-ambient-orb bankai-orb-2"></div>
        <div class="bankai-ambient-orb bankai-orb-3"></div>
    </div>

    <!-- Toast Host -->
    <?php
    $toast_file = defined('BANKAI_CORE_VIEWS_DIR') ? BANKAI_CORE_VIEWS_DIR . 'admin/toast.php' : '';
    if ($toast_file && is_file($toast_file)) {
        include $toast_file;
    }
    ?>

    <!-- Page Progress -->
    <div id="bankai-page-loader"
         class="bankai-page-progress-track"
         style="display: none;"
         role="progressbar"
         aria-live="polite">
        <div class="bankai-page-progress-bar" style="width: 0%;"></div>
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

        <div class="bankai-mobile-overlay"
             style="display: none;"
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
            <div class="bankai-tab-loading-overlay"
                 style="display: none;"
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
            $tabs = [
                'overview',
                'articles',
                'seo-engine',
                'ai-studio',
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

    <!-- Toast Notification Host -->
    <div class="bankai-toast-host" id="bankai-toast-host" style="display:none; position:fixed; bottom:28px; left:50%; transform:translateX(-50%); z-index:100000;">
        <div class="bankai-toast" id="bankai-toast-message"
             style="background:#fff; color:#1F2328; border:1px solid #D0D7DE; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:700; box-shadow:0 12px 32px rgba(15,23,42,.12); min-width:220px; text-align:center;"></div>
    </div>

</div>
