<?php
/**
 * View 3: Autonomous SEO Engine
 * views/admin/seo-engine.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<div class="space-y-6">
    <div class="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-bold text-slate-100"><?php echo $is_rtl ? 'موتور سئوی خودکار و ساختار اسکیما' : 'Autonomous SEO & Schema Engine'; ?></h2>
                <p class="text-xs text-slate-400"><?php echo $is_rtl ? 'مدیریت نقشه سایت، هوش مصنوعی لینک‌سازی و فایل llms.txt' : 'Automated 28-point technical audit and dynamic JSON-LD Schema builder.'; ?></p>
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs px-4 py-2 rounded-xl font-semibold shadow-lg shadow-indigo-600/30 transition-all">
                <?php echo $is_rtl ? 'اجرای اسکن کامل سئو' : 'Run Full SEO Audit'; ?>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-xl flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-200"><?php echo $is_rtl ? 'نقشه سایت XML' : 'XML Sitemaps Hub'; ?></div>
                    <div class="text-xs text-emerald-400"><?php echo $is_rtl ? 'فعال و بروز' : 'Active & Synced'; ?></div>
                </div>
                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded bg-slate-800 border-slate-700">
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-xl flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-200"><?php echo $is_rtl ? 'مولد llms.txt' : 'LLMs.txt Generator'; ?></div>
                    <div class="text-xs text-indigo-400"><?php echo $is_rtl ? 'آماده برای مدل‌های AI' : 'Ready for AI Crawlers'; ?></div>
                </div>
                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded bg-slate-800 border-slate-700">
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-xl flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-200"><?php echo $is_rtl ? 'اسکیما اتوماتیک Schema.org' : 'Schema.org Builder'; ?></div>
                    <div class="text-xs text-emerald-400"><?php echo $is_rtl ? 'JSON-LD فعال' : 'JSON-LD Enabled'; ?></div>
                </div>
                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded bg-slate-800 border-slate-700">
            </div>
        </div>
    </div>
</div>
