<?php
/**
 * Bankai Core Master Container Wrapper Layout
 * views/admin/layout.php
 */
if (!defined('BANKAI_CORE_PATH')) {
    define('BANKAI_CORE_PATH', plugin_dir_path(dirname(__DIR__)) . '/');
}
?>

<div id="bankai-admin-app" x-data="bankaiAdmin()" class="bg-[#080C14] text-slate-100 min-h-screen flex flex-col md:flex-row rtl:flex-row-reverse gap-6 p-6">
    <aside class="w-full md:w-64 flex-shrink-0">
        <?php include BANKAI_CORE_PATH . 'views/admin/sidebar.php'; ?>
    </aside>

    <main class="flex-1 min-w-0">
        <?php include BANKAI_CORE_PATH . 'views/admin/header.php'; ?>

        <!-- Views Container with x-show tab switching -->
        <div x-show="activeTab === 'overview'" x-cloak>
            <?php include BANKAI_CORE_PATH . 'views/admin/dashboard.php'; ?>
        </div>
        <div x-show="activeTab === 'theme-kits'" x-cloak>
            <?php include BANKAI_CORE_PATH . 'views/admin/theme-kits.php'; ?>
        </div>
        <div x-show="activeTab === 'seo-engine'" x-cloak>
            <?php include BANKAI_CORE_PATH . 'views/admin/seo-engine.php'; ?>
        </div>
        <div x-show="activeTab === 'speed-cache'" x-cloak>
            <?php include BANKAI_CORE_PATH . 'views/admin/speed-cache.php'; ?>
        </div>
        <div x-show="activeTab === 'media-watermark'" x-cloak>
            <?php include BANKAI_CORE_PATH . 'views/admin/media-watermark.php'; ?>
        </div>
        <div x-show="activeTab === 'ai-studio'" x-cloak>
            <?php include BANKAI_CORE_PATH . 'views/admin/ai-studio.php'; ?>
        </div>
        <div x-show="activeTab === 'settings-license'" x-cloak>
            <?php include BANKAI_CORE_PATH . 'views/admin/settings-license.php'; ?>
        </div>
    </main>
</div>
