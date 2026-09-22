<?php
/**
 * Tab: Media & Watermark Studio
 */
defined('ABSPATH') || exit;

/** @var array $state */
$media_mods = is_array($state['mediaModules'] ?? null) ? $state['mediaModules'] : [];
$wm = is_array($state['watermarkSettings'] ?? null) ? $state['watermarkSettings'] : [];
if (!$wm && class_exists('Bankai_Media_Watermark')) {
    $wm = Bankai_Media_Watermark::instance()->get_settings();
}
$env = class_exists('Bankai_Media_Watermark') ? Bankai_Media_Watermark::environment() : [];
$gd_ok = !empty($env['gd']);
$imagick_ok = !empty($env['imagick']);
$webp_ok = !empty($env['webp']);
?>
<div id="tab-media-watermark" class="bankai-tab-pane" x-show="activeTab === 'media-watermark'" x-cloak
     x-init="initMediaStudio()">

    <!-- Header -->
    <div class="bankai-card" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;padding:20px;flex-wrap:wrap;gap:14px;">
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;flex-wrap:wrap;">
                <h2 style="font-size:18px;font-weight:800;color:#1F2328;margin:0;display:flex;align-items:center;gap:8px;">
                    <svg class="solar-icon" style="color:#0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    <span x-text="t('mediaEngineTitle')">موتور رسانه و استودیو واترمارک</span>
                </h2>
                <span style="background:rgba(9,105,218,.12);border:1px solid rgba(9,105,218,.3);color:#0969DA;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700;">
                    WebP · AVIF · Lazy · Watermark
                </span>
            </div>
            <p style="font-size:12px;color:#8C959F;margin:0;" x-text="t('mediaEngineSubtitle')">
                تبدیل خودکار، واترمارک متن/تصویر، حذف EXIF و لود تنبل
            </p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button type="button" @click="regenerateThumbnails()" :disabled="busy"
                    style="background:#fff;border:1px solid #D0D7DE;color:#1F2328;padding:10px 16px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;">
                <svg class="solar-icon solar-icon-sm" style="color:#0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19.95 11a8 8 0 1 0-.5 4m.5-4h-5m5 0V6"/></svg>
                <span x-text="t('regenThumbs')">بازتولید بندانگشتی</span>
            </button>
            <button type="button" @click="bulkConvertMedia()" :disabled="busy"
                    style="background:#0969DA;border:none;color:#fff;padding:10px 18px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(9,105,218,.35);">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                <span x-text="t('bulkConvert')">تبدیل گروهی کتابخانه</span>
            </button>
        </div>
    </div>

    <!-- Environment -->
    <div class="bankai-grid-4" style="margin-bottom:24px;">
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;">GD Engine</div>
            <div style="font-size:15px;font-weight:800;margin-top:4px;color:<?php echo $gd_ok ? '#1A7F37' : '#A6122D'; ?>">
                <?php echo $gd_ok ? '✓ فعال' : '✗ غیرفعال'; ?>
            </div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;">Imagick</div>
            <div style="font-size:15px;font-weight:800;margin-top:4px;color:<?php echo $imagick_ok ? '#1A7F37' : '#8C959F'; ?>">
                <?php echo $imagick_ok ? '✓ فعال' : '— در دسترس نیست'; ?>
            </div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;">WebP</div>
            <div style="font-size:15px;font-weight:800;margin-top:4px;color:<?php echo $webp_ok ? '#1A7F37' : '#A6122D'; ?>">
                <?php echo $webp_ok ? '✓ پشتیبانی' : '✗ بدون پشتیبانی'; ?>
            </div>
        </div>
        <div class="bankai-card" style="padding:16px;">
            <div style="font-size:11px;color:#8C959F;font-weight:700;text-transform:uppercase;">Lazy Load</div>
            <div style="font-size:15px;font-weight:800;margin-top:4px;color:#0969DA;"
                 x-text="watermarkStudio.lazy_load ? '✓ فعال' : 'خاموش'">—</div>
        </div>
    </div>

    <!-- Watermark Studio -->
    <div class="bankai-card" style="padding:24px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
            <h3 style="font-size:15px;font-weight:800;margin:0;display:flex;align-items:center;gap:8px;">
                <svg class="solar-icon" style="color:#0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2"/></svg>
                استودیو واترمارک تعاملی
            </h3>
            <label style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:700;cursor:pointer;">
                <input type="checkbox" x-model="watermarkStudio.enabled" style="accent-color:#0969DA;">
                فعال‌سازی واترمارک
            </label>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div style="display:flex;flex-direction:column;gap:16px;">
                <!-- Type -->
                <div>
                    <label style="font-size:12px;font-weight:700;color:#656D76;display:block;margin-bottom:8px;">نوع واترمارک</label>
                    <div style="display:flex;gap:8px;">
                        <button type="button" @click="watermarkStudio.type='text'"
                                :style="watermarkStudio.type==='text' ? 'background:#0969DA;color:#fff;border-color:#0969DA' : 'background:#F6F8FA;color:#656D76;border-color:#D0D7DE'"
                                style="flex:1;padding:10px;border-radius:8px;border:1px solid;font-size:12px;font-weight:700;cursor:pointer;">متن</button>
                        <button type="button" @click="watermarkStudio.type='image'"
                                :style="watermarkStudio.type==='image' ? 'background:#0969DA;color:#fff;border-color:#0969DA' : 'background:#F6F8FA;color:#656D76;border-color:#D0D7DE'"
                                style="flex:1;padding:10px;border-radius:8px;border:1px solid;font-size:12px;font-weight:700;cursor:pointer;">تصویر / لوگو</button>
                    </div>
                </div>

                <!-- Text or Image -->
                <div x-show="watermarkStudio.type==='text'">
                    <label style="font-size:12px;font-weight:700;color:#656D76;display:block;margin-bottom:6px;">متن واترمارک</label>
                    <input type="text" x-model="watermarkStudio.text" maxlength="80"
                           style="width:100%;padding:10px 12px;border:1px solid #D0D7DE;border-radius:8px;font-size:13px;">
                </div>
                <div x-show="watermarkStudio.type==='image'" x-cloak>
                    <label style="font-size:12px;font-weight:700;color:#656D76;display:block;margin-bottom:6px;">لوگوی واترمارک</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <button type="button" @click="pickWatermarkImage()"
                                style="padding:10px 14px;border-radius:8px;border:1px solid #D0D7DE;background:#F6F8FA;font-size:12px;font-weight:700;color:#0969DA;cursor:pointer;">
                            انتخاب از رسانه
                        </button>
                        <span style="font-size:11px;color:#8C959F;" x-text="watermarkStudio.image_id ? ('ID: ' + watermarkStudio.image_id) : 'انتخاب نشده'"></span>
                    </div>
                    <img x-show="watermarkStudio.image_url" :src="watermarkStudio.image_url" alt=""
                         style="margin-top:10px;max-height:64px;border-radius:6px;border:1px solid #D0D7DE;">
                </div>

                <!-- Position grid -->
                <div>
                    <label style="font-size:12px;font-weight:700;color:#656D76;display:block;margin-bottom:8px;">موقعیت</label>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;max-width:280px;">
                        <?php
                        $positions = [
                            'top-left' => '↖', 'top-center' => '↑', 'top-right' => '↗',
                            'center-left' => '←', 'center' => '●', 'center-right' => '→',
                            'bottom-left' => '↙', 'bottom-center' => '↓', 'bottom-right' => '↘',
                        ];
                        foreach ($positions as $pos => $icon):
                        ?>
                        <button type="button"
                                @click="watermarkStudio.position='<?php echo esc_js($pos); ?>'"
                                :style="watermarkStudio.position==='<?php echo esc_js($pos); ?>' ? 'background:#0969DA;color:#fff;border-color:#0969DA' : 'background:#F6F8FA;color:#656D76;border-color:#D0D7DE'"
                                style="padding:12px;border-radius:8px;border:1px solid;font-size:14px;font-weight:700;cursor:pointer;">
                            <?php echo $icon; ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:#656D76;margin-bottom:4px;">
                        <span>شفافیت</span>
                        <span style="color:#0969DA;font-weight:800;font-family:monospace;" x-text="watermarkStudio.opacity + '%'">75%</span>
                    </div>
                    <input type="range" min="10" max="100" x-model="watermarkStudio.opacity" style="width:100%;accent-color:#0969DA;">
                </div>

                <div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:#656D76;margin-bottom:4px;">
                        <span>کیفیت فشرده‌سازی</span>
                        <span style="color:#0969DA;font-weight:800;font-family:monospace;" x-text="watermarkStudio.quality">82</span>
                    </div>
                    <input type="range" min="40" max="100" x-model="watermarkStudio.quality" style="width:100%;accent-color:#0969DA;">
                </div>

                <!-- Scope -->
                <div style="display:flex;flex-direction:column;gap:8px;padding:12px;background:#F6F8FA;border-radius:10px;border:1px solid #EAEEF2;">
                    <label style="font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" x-model="watermarkStudio.apply_upload" style="accent-color:#0969DA;">
                        اعمال روی آپلودهای جدید
                    </label>
                    <label style="font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" x-model="watermarkStudio.apply_content" style="accent-color:#0969DA;">
                        اعمال روی تصاویر موجود (با تبدیل گروهی)
                    </label>
                    <label style="font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" x-model="watermarkStudio.lazy_load" style="accent-color:#0969DA;">
                        Lazy Load تصاویر محتوا و بندانگشتی
                    </label>
                    <label style="font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" x-model="watermarkStudio.strip_exif" style="accent-color:#0969DA;">
                        حذف خودکار EXIF / GPS
                    </label>
                    <label style="font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" x-model="watermarkStudio.convert_webp" style="accent-color:#0969DA;">
                        ساخت نسخه WebP در کنار اصل
                    </label>
                </div>

                <button type="button" @click="saveWatermarkStudio()" :disabled="busy"
                        style="padding:12px;border:none;border-radius:10px;background:#0969DA;color:#fff;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 4px 14px rgba(9,105,218,.3);">
                    ذخیره تنظیمات رسانه
                </button>
            </div>

            <!-- Preview -->
            <div style="position:relative;min-height:320px;background:#F6F8FA;border:1px solid #D0D7DE;border-radius:14px;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=700&q=80"
                     alt="Preview" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">
                <div style="position:absolute;padding:12px;pointer-events:none;transition:all .25s ease;"
                     :style="getWatermarkPositionStyle()">
                    <template x-if="watermarkStudio.type==='text'">
                        <span style="background:rgba(31,35,40,.72);color:#fff;padding:6px 14px;border-radius:4px;font-size:12px;font-weight:800;border:1px solid rgba(255,255,255,.25);backdrop-filter:blur(6px);"
                              :style="'opacity:' + (watermarkStudio.opacity/100)"
                              x-text="watermarkStudio.text || '© BANKAI'"></span>
                    </template>
                    <template x-if="watermarkStudio.type==='image' && watermarkStudio.image_url">
                        <img :src="watermarkStudio.image_url" alt=""
                             :style="'max-width:120px;opacity:' + (watermarkStudio.opacity/100) + ';filter:drop-shadow(0 2px 6px rgba(0,0,0,.25))'">
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules -->
    <div class="bankai-grid-3" style="margin-bottom:32px;">
        <?php foreach ($media_mods as $mod):
            $mod_id = esc_attr($mod['id'] ?? '');
            $title_fa = esc_js($mod['title_fa'] ?? $mod['title'] ?? '');
            $title_en = esc_js($mod['title'] ?? '');
            $desc_fa = esc_js($mod['description_fa'] ?? $mod['description'] ?? '');
            $desc_en = esc_js($mod['description'] ?? '');
            $badge = esc_html($mod['badge'] ?? '');
        ?>
        <div class="bankai-card" style="padding:20px;display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div>
                        <div style="font-weight:700;font-size:14px;color:#1F2328;"
                             x-text="isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>'">
                            <?php echo esc_html($mod['title_fa'] ?? $mod['title'] ?? ''); ?>
                        </div>
                        <span style="background:rgba(9,105,218,.12);color:#0969DA;font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;display:inline-block;margin-top:3px;"><?php echo $badge; ?></span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox"
                               class="bk-sub-module-toggle"
                               data-module="<?php echo $mod_id; ?>"
                               x-model="mediaState['<?php echo $mod_id; ?>']"
                               @change="toggleMediaModule('<?php echo $mod_id; ?>')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size:12px;color:#656D76;line-height:1.5;margin:0 0 16px;"
                   x-text="isRtl ? '<?php echo $desc_fa; ?>' : '<?php echo $desc_en; ?>'"></p>
            </div>
            <div style="border-top:1px solid #D0D7DE;padding-top:12px;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:11px;font-weight:700;"
                      :style="mediaState['<?php echo $mod_id; ?>'] ? 'color:#1A7F37' : 'color:#8C959F'"
                      x-text="mediaState['<?php echo $mod_id; ?>'] ? t('active') : t('disabled')">—</span>
                <button type="button"
                        @click="openMediaDrawer('<?php echo $mod_id; ?>', isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>')"
                        style="background:#F6F8FA;border:1px solid #D0D7DE;color:#0969DA;font-size:11px;font-weight:700;padding:5px 12px;border-radius:6px;cursor:pointer;">
                    پیکربندی
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Media drawer modern -->
    <div x-show="mediaDrawer.show" x-cloak class="bankai-modal-shell" x-transition>
        <div class="bankai-modal-panel" @click.outside="mediaDrawer.show=false">
            <div class="bankai-modal-head">
                <h3 class="bankai-modal-title" x-text="mediaDrawer.title + (isRtl ? ' — تنظیمات' : ' — Settings')"></h3>
                <button type="button" class="bankai-modal-close" @click="mediaDrawer.show=false">&times;</button>
            </div>
            <div class="bankai-modal-body">
                <p style="font-size:13px;color:#656D76;margin:0;line-height:1.6;">
                    تنظیمات عمومی این ماژول از استودیو واترمارک بالا کنترل می‌شود. سوئیچ کارت، فعال/غیرفعال بودن قابلیت را ذخیره می‌کند.
                </p>
            </div>
            <div class="bankai-modal-foot">
                <button type="button" class="bankai-btn-ghost" @click="mediaDrawer.show=false">بستن</button>
                <button type="button" class="bankai-btn-primary" @click="mediaDrawer.show=false; saveWatermarkStudio()">ذخیره</button>
            </div>
        </div>
    </div>
</div>
<style>
@media (max-width: 900px) {
    #tab-media-watermark [style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
