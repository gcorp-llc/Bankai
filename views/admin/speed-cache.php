<?php
/**
 * View 4: Speed & Cache Engine
 * views/admin/speed-cache.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<div class="space-y-6">
    <div class="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-100 mb-1"><?php echo $is_rtl ? 'موتور شتاب‌دهنده و کشینگ بانکای' : 'Speed & Asset Acceleration Engine'; ?></h2>
        <p class="text-xs text-slate-400 mb-6"><?php echo $is_rtl ? 'مدیریت کش Redis/Memcached، CSS بحرانی و بومی‌سازی فونت‌های گوگل' : 'Control Redis sockets, inline critical CSS, and local Google Font caching.'; ?></p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-xl space-y-3">
                <div class="text-sm font-bold text-slate-200"><?php echo $is_rtl ? 'تنظیمات کش شیء (Object Cache)' : 'Object Cache Settings'; ?></div>
                <p class="text-xs text-slate-400"><?php echo $is_rtl ? 'اتصال مستقیم به سوکت Redis سرور' : 'Direct Redis socket connection.'; ?></p>
                <div class="flex items-center gap-2 text-xs text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span><?php echo $is_rtl ? 'متصل به Redis v7.2' : 'Connected to Redis v7.2'; ?></span>
                </div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-xl space-y-3">
                <div class="text-sm font-bold text-slate-200"><?php echo $is_rtl ? 'بهینه‌سازی پایگاه داده' : 'Database Optimization'; ?></div>
                <p class="text-xs text-slate-400"><?php echo $is_rtl ? 'پاکسازی پیش‌نویس‌های قدیمی و ترنژینت‌ها' : 'Purge old revisions and expired transients.'; ?></p>
                <button class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs px-3.5 py-1.5 rounded-lg border border-slate-700 font-semibold transition-all">
                    <?php echo $is_rtl ? 'بهینه‌سازی دیتابیس' : 'Clean Database'; ?>
                </button>
            </div>
        </div>
    </div>
</div>
