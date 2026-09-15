<?php
/**
 * View 7: Settings, RBAC & License Hub
 * views/admin/settings-license.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<div class="space-y-6">
    <div class="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-100 mb-1"><?php echo $is_rtl ? 'تنظیمات لایسنس و ابزارهای سیستم' : 'Settings, RBAC & License Hub'; ?></h2>
        <p class="text-xs text-slate-400 mb-6"><?php echo $is_rtl ? 'مدیریت فعال‌سازی لایسنس، خروجی گزارش عیب‌یابی و پشتیبان‌گیری' : 'License verification, diagnostic exports, and system hard reset.'; ?></p>

        <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-xl space-y-4 mb-6">
            <div class="text-sm font-bold text-indigo-300"><?php echo $is_rtl ? 'کلید فعال‌سازی لایسنس پرو' : 'Pro License Key Verification'; ?></div>
            <div class="flex gap-3">
                <input type="text" value="BANKAI-PRO-9984-7712-4410-EXT" class="flex-1 bg-slate-800 border border-slate-700 text-indigo-300 font-mono text-xs rounded-xl p-2.5 font-bold">
                <button class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs px-4 py-2.5 rounded-xl font-semibold transition-all">
                    <?php echo $is_rtl ? 'بررسی لایسنس' : 'Verify License'; ?>
                </button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs px-4 py-2.5 rounded-xl font-semibold transition-all">
                <?php echo $is_rtl ? 'دانلود گزارش سیستم (Diagnostics)' : 'Export System Report'; ?>
            </button>
            <button class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs px-4 py-2.5 rounded-xl font-semibold transition-all">
                <?php echo $is_rtl ? 'پشتیبان‌گیری از تنظیمات' : 'Backup Configurations'; ?>
            </button>
            <button class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs px-4 py-2.5 rounded-xl font-semibold transition-all">
                <?php echo $is_rtl ? 'بازنشانی کامل (Reset)' : 'Hard Reset All Settings'; ?>
            </button>
        </div>
    </div>
</div>
