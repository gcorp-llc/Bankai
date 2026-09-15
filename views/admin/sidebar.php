<?php
/**
 * Bankai Core Navigation Sidebar
 * views/admin/sidebar.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<nav class="bg-[#111827] border border-slate-800 rounded-2xl p-4 flex flex-col gap-2 shadow-xl">
    <!-- Brand Info -->
    <div class="flex items-center gap-3 px-3 py-2 border-b border-slate-800/80 mb-2 pb-4">
        <img src="<?php echo defined('BANKAI_CORE_URL') ? BANKAI_CORE_URL . 'public/images/logo.jpg' : '../public/images/logo.jpg'; ?>" alt="Bankai Logo" class="w-9 h-9 rounded-xl object-cover border border-slate-700" />
        <div>
            <div class="font-bold text-sm text-slate-100"><?php echo $is_rtl ? 'معماری بانکای' : 'Bankai Architecture'; ?></div>
            <div class="text-[11px] text-slate-400"><?php echo $is_rtl ? 'نسخه ۴.۰ اختصاصی' : 'Enterprise Suite'; ?></div>
        </div>
    </div>

    <!-- Navigation Items -->
    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3 mt-1 mb-1">
        <?php echo $is_rtl ? 'منوی اصلی' : 'Main Modules'; ?>
    </div>

    <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'" class="w-full text-start flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span><?php echo $is_rtl ? 'داشبورد و سلامت سیستم' : 'Overview & Health'; ?></span>
    </button>

    <button @click="activeTab = 'theme-kits'" :class="activeTab === 'theme-kits' ? 'bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'" class="w-full text-start flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
        <span><?php echo $is_rtl ? 'قالب و کیت‌های آماده' : 'Theme & Starter Kits'; ?></span>
    </button>

    <button @click="activeTab = 'seo-engine'" :class="activeTab === 'seo-engine' ? 'bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'" class="w-full text-start flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <span><?php echo $is_rtl ? 'موتور سئو و اسکیما' : 'Autonomous SEO Engine'; ?></span>
    </button>

    <button @click="activeTab = 'speed-cache'" :class="activeTab === 'speed-cache' ? 'bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'" class="w-full text-start flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span><?php echo $is_rtl ? 'موتور سرعت و کش' : 'Speed & Cache Engine'; ?></span>
    </button>

    <button @click="activeTab = 'media-watermark'" :class="activeTab === 'media-watermark' ? 'bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'" class="w-full text-start flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span><?php echo $is_rtl ? 'استودیو رسانه و واترمارک' : 'Media & Watermark Studio'; ?></span>
    </button>

    <button @click="activeTab = 'ai-studio'" :class="activeTab === 'ai-studio' ? 'bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'" class="w-full text-start flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <span><?php echo $is_rtl ? 'استودیو هوش مصنوعی' : 'AI Content Studio & LLMs'; ?></span>
    </button>

    <button @click="activeTab = 'settings-license'" :class="activeTab === 'settings-license' ? 'bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'" class="w-full text-start flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span><?php echo $is_rtl ? 'تنظیمات و لایسنس' : 'Settings & License Hub'; ?></span>
    </button>
</nav>
