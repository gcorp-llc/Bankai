<?php
/**
 * View 1: Overview & Health Dashboard
 * views/admin/dashboard.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#111827] border border-slate-800 p-5 rounded-2xl shadow-xl">
            <div class="text-xs text-slate-400 font-semibold mb-1"><?php echo $is_rtl ? 'امتیاز سرعت (PageSpeed)' : 'PageSpeed Score'; ?></div>
            <div class="text-3xl font-bold text-emerald-400">99 / 100</div>
            <div class="text-[11px] text-emerald-500/80 mt-2">✓ <?php echo $is_rtl ? 'عملکرد موبایل و دسکتاپ عالی' : 'Optimal Core Web Vitals'; ?></div>
        </div>

        <div class="bg-[#111827] border border-slate-800 p-5 rounded-2xl shadow-xl">
            <div class="text-xs text-slate-400 font-semibold mb-1"><?php echo $is_rtl ? 'وضعیت سلامت ایندکس' : 'SEO Health Index'; ?></div>
            <div class="text-3xl font-bold text-indigo-400">98%</div>
            <div class="text-[11px] text-indigo-400/80 mt-2">✓ <?php echo $is_rtl ? '۲۸ تست سئو فعال است' : '28 Automated Checks Active'; ?></div>
        </div>

        <div class="bg-[#111827] border border-slate-800 p-5 rounded-2xl shadow-xl">
            <div class="text-xs text-slate-400 font-semibold mb-1"><?php echo $is_rtl ? 'حجم کش ذخیره‌شده' : 'Cached Static Assets'; ?></div>
            <div class="text-3xl font-bold text-purple-400">142 MB</div>
            <div class="text-[11px] text-slate-400 mt-2"><?php echo $is_rtl ? 'نرخ پاسخ‌دهی کش ۹۹.۴٪' : '99.4% Redis Hit Rate'; ?></div>
        </div>

        <div class="bg-[#111827] border border-slate-800 p-5 rounded-2xl shadow-xl">
            <div class="text-xs text-slate-400 font-semibold mb-1"><?php echo $is_rtl ? 'بهینه‌سازی تصاویر (WebP/AVIF)' : 'Media Optimized'; ?></div>
            <div class="text-3xl font-bold text-cyan-400">1,420</div>
            <div class="text-[11px] text-cyan-400/80 mt-2"><?php echo $is_rtl ? '۶۸٪ کاهش حجم فایل‌ها' : '68% Bandwidth Saved'; ?></div>
        </div>
    </div>

    <div class="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-slate-100 flex items-center justify-between">
            <span><?php echo $is_rtl ? 'گزارش ناهنجاری‌ها و خطاهای 404' : 'Real-time 404 Anomaly & Redirection Logs'; ?></span>
            <span class="text-xs text-slate-400 font-normal"><?php echo $is_rtl ? 'آخرین بروزرسانی: هم‌اکنون' : 'Live Monitoring'; ?></span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-slate-300">
                <thead class="bg-slate-800/60 text-slate-400 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-3 text-start"><?php echo $is_rtl ? 'آدرس منبع (URL)' : 'Requested URL'; ?></th>
                        <th class="p-3 text-start"><?php echo $is_rtl ? 'تعداد خطا' : 'Hits'; ?></th>
                        <th class="p-3 text-start"><?php echo $is_rtl ? 'پیشنهاد ۳۰۱' : 'Suggested 301 Target'; ?></th>
                        <th class="p-3 text-start"><?php echo $is_rtl ? 'عملیات' : 'Action'; ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <tr>
                        <td class="p-3 font-mono text-indigo-300">/old-blog-post-2023/</td>
                        <td class="p-3 font-semibold text-amber-400">42</td>
                        <td class="p-3 font-mono text-slate-400">/blog/modern-wordpress/</td>
                        <td class="p-3">
                            <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-2.5 py-1 rounded-lg font-semibold transition-all">
                                <?php echo $is_rtl ? 'تایید ۳۰۱' : 'Apply 301 Redirect'; ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-3 font-mono text-indigo-300">/products/legacy-item/</td>
                        <td class="p-3 font-semibold text-amber-400">18</td>
                        <td class="p-3 font-mono text-slate-400">/shop/</td>
                        <td class="p-3">
                            <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-2.5 py-1 rounded-lg font-semibold transition-all">
                                <?php echo $is_rtl ? 'تایید ۳۰۱' : 'Apply 301 Redirect'; ?>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
