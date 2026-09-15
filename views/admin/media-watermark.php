<?php
/**
 * View 5: Media & Watermark Studio
 * views/admin/media-watermark.php
 */
$is_rtl = function_exists('is_rtl') && is_rtl();
?>

<div class="space-y-6">
    <div class="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-100 mb-1"><?php echo $is_rtl ? 'استودیو رسانه و واترمارک هوشمند' : 'Media & Dynamic Watermark Studio'; ?></h2>
        <p class="text-xs text-slate-400 mb-6"><?php echo $is_rtl ? 'تبدیل خودکار به WebP/AVIF و درج لوگو روی تصاویر آپلودی' : 'Automated WebP/AVIF compression with live canvas watermark overlay.'; ?></p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-xl space-y-4">
                <div class="text-sm font-bold text-slate-200"><?php echo $is_rtl ? 'موقعیت واترمارک' : 'Watermark Overlay Position'; ?></div>
                <div class="grid grid-cols-3 gap-2">
                    <button class="bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white p-2 text-center rounded-lg text-xs font-semibold"><?php echo $is_rtl ? 'بالا چپ' : 'Top Left'; ?></button>
                    <button class="bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white p-2 text-center rounded-lg text-xs font-semibold"><?php echo $is_rtl ? 'بالا وسط' : 'Top Center'; ?></button>
                    <button class="bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white p-2 text-center rounded-lg text-xs font-semibold"><?php echo $is_rtl ? 'بالا راست' : 'Top Right'; ?></button>
                    <button class="bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white p-2 text-center rounded-lg text-xs font-semibold"><?php echo $is_rtl ? 'وسط' : 'Center'; ?></button>
                    <button class="bg-indigo-600 text-white p-2 text-center rounded-lg text-xs font-semibold shadow-lg shadow-indigo-600/30"><?php echo $is_rtl ? 'پایین راست' : 'Bottom Right'; ?></button>
                    <button class="bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white p-2 text-center rounded-lg text-xs font-semibold"><?php echo $is_rtl ? 'پایین چپ' : 'Bottom Left'; ?></button>
                </div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-xl space-y-4">
                <div class="text-sm font-bold text-slate-200"><?php echo $is_rtl ? 'تبدیل تصاویر به WebP/AVIF' : 'Auto WebP Conversion'; ?></div>
                <p class="text-xs text-slate-400"><?php echo $is_rtl ? 'کاهش حجم اتوماتیک تصاویر هنگام آپلود' : 'Convert all PNG/JPEG uploads automatically.'; ?></p>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-300"><?php echo $is_rtl ? 'کیفیت تصویر: ۸۵٪' : 'Quality: 85%'; ?></span>
                    <input type="range" min="50" max="100" value="85" class="w-full accent-indigo-500">
                </div>
            </div>
        </div>
    </div>
</div>
