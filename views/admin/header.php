<?php
/**
 * Bankai Core Admin Header Bar
 * views/admin/header.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<header class="bg-[#111827] border border-slate-800 rounded-2xl p-4 mb-6 flex flex-wrap items-center justify-between gap-4 shadow-xl">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20">
            BK
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                <span><?php echo $is_rtl ? 'بانکای کُور پِرو' : 'Bankai Core Pro'; ?></span>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 font-semibold">v4.0.0</span>
            </h1>
            <p class="text-xs text-slate-400">
                <?php echo $is_rtl ? 'پلتفرم هوشمند مدیریت و بهینه‌سازی پیشرفته وردپرس' : 'Next-Gen High-Performance Modular WordPress Platform & AI Studio'; ?>
            </p>
        </div>
    </div>

    <div class="flex items-center gap-3 flex-wrap">
        <!-- Status Badge -->
        <div class="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 rounded-xl text-emerald-400 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span><?php echo $is_rtl ? 'وضعیت سیستم: عالی' : 'SYSTEM STATUS: OPTIMAL'; ?></span>
        </div>

        <!-- Purge Cache Trigger -->
        <button @click="triggerCachePurge()" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3.5 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all">
            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span><?php echo $is_rtl ? 'پاکسازی کش' : 'Purge Cache'; ?></span>
        </button>

        <!-- License Badge -->
        <div class="bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 px-3 py-1.5 rounded-xl text-xs font-bold">
            <?php echo $is_rtl ? 'لایسنس مادام‌العمر PRO' : 'LIFETIME PRO'; ?>
        </div>
    </div>
</header>
