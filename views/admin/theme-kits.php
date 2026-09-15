<?php
/**
 * View 2: Theme & Starter Kits
 * views/admin/theme-kits.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<div class="space-y-6">
    <div class="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-100 mb-1"><?php echo $is_rtl ? 'کیت‌های طراحی و استارتر قالب' : 'Theme Customizer & Starter Kits'; ?></h2>
        <p class="text-xs text-slate-400 mb-6"><?php echo $is_rtl ? 'انتخاب فونت‌های پیش‌فرض سیستم، عرض کانتینر و درون‌ریزی با یک کلیک' : 'Manage typography, dynamic container sizing, and import starter templates.'; ?></p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-xl space-y-4">
                <div class="text-sm font-semibold text-indigo-300"><?php echo $is_rtl ? '۱. استارتر سایبرپانک شرکتی' : '1. Cyberpunk Corporate'; ?></div>
                <p class="text-xs text-slate-400"><?php echo $is_rtl ? 'طراحی مدرن تیره‌رنگ ویژه شرکت‌های فناوری و استارت‌آپ‌ها' : 'Modern Dark SaaS layout optimized for tech platforms.'; ?></p>
                <button class="w-full bg-indigo-600 hover:bg-indigo-500 text-white text-xs py-2 rounded-xl font-semibold transition-all">
                    <?php echo $is_rtl ? 'درون‌ریزی دمو' : 'Import Demo Kit'; ?>
                </button>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-xl space-y-4">
                <div class="text-sm font-semibold text-emerald-300"><?php echo $is_rtl ? '۲. فروشگاه هوشمند ووکارمرس' : '2. Smart WooCommerce'; ?></div>
                <p class="text-xs text-slate-400"><?php echo $is_rtl ? 'طراحی فروشگاهی فوق‌العاده سریع با نرخ تبدیل بالا' : 'High-conversion ecommerce template set.'; ?></p>
                <button class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs py-2 rounded-xl font-semibold transition-all">
                    <?php echo $is_rtl ? 'درون‌ریزی دمو' : 'Import Demo Kit'; ?>
                </button>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-xl space-y-4">
                <div class="text-sm font-semibold text-purple-300"><?php echo $is_rtl ? '۳. مجله خبری و محتوایی' : '3. Executive Portal & News'; ?></div>
                <p class="text-xs text-slate-400"><?php echo $is_rtl ? 'مناسب رسانه‌های پرترافیک با کشینگ هوشمند اختصاصی' : 'High-density content and publishing suite.'; ?></p>
                <button class="w-full bg-purple-600 hover:bg-purple-500 text-white text-xs py-2 rounded-xl font-semibold transition-all">
                    <?php echo $is_rtl ? 'درون‌ریزی دمو' : 'Import Demo Kit'; ?>
                </button>
            </div>
        </div>
    </div>
</div>
