<?php
/**
 * Tab: Media & Watermark Studio — Fluent / Material redesign
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

$positions = [
    'top-left'      => ['icon' => 'north_west', 'label' => 'بالا چپ'],
    'top-center'    => ['icon' => 'north', 'label' => 'بالا وسط'],
    'top-right'     => ['icon' => 'north_east', 'label' => 'بالا راست'],
    'center-left'   => ['icon' => 'west', 'label' => 'وسط چپ'],
    'center'        => ['icon' => 'filter_center_focus', 'label' => 'مرکز'],
    'center-right'  => ['icon' => 'east', 'label' => 'وسط راست'],
    'bottom-left'   => ['icon' => 'south_west', 'label' => 'پایین چپ'],
    'bottom-center' => ['icon' => 'south', 'label' => 'پایین وسط'],
    'bottom-right'  => ['icon' => 'south_east', 'label' => 'پایین راست'],
];
?>
<div id="tab-media-watermark" class="bankai-tab-pane bk-media-shell" x-show="activeTab === 'media-watermark'" x-cloak
     x-init="initMediaStudio()">

    <!-- Page header -->
    <div class="bk-media-page-head bankai-card">
        <div>
            <h2 class="bk-media-title">
                <span class="material-symbols-outlined">photo_library</span>
                موتور رسانه و واترمارک
            </h2>
            <p class="bk-media-sub">WebP · فشرده‌سازی · واترمارک · Lazy Load</p>
        </div>
        <div class="bk-media-head-actions">
            <button type="button" class="bankai-btn-ghost" @click="regenerateThumbnails()" :disabled="busy">
                <span class="material-symbols-outlined">restart_alt</span>
                بازتولید بندانگشتی
            </button>
        </div>
    </div>

    <!-- Env chips -->
    <div class="bk-media-env-row">
        <div class="bk-env-chip" :class="<?php echo $gd_ok ? "'is-ok'" : "'is-bad'"; ?>">
            <span class="material-symbols-outlined"><?php echo $gd_ok ? 'check_circle' : 'cancel'; ?></span>
            GD
        </div>
        <div class="bk-env-chip" :class="<?php echo $imagick_ok ? "'is-ok'" : "'is-muted'"; ?>">
            <span class="material-symbols-outlined"><?php echo $imagick_ok ? 'check_circle' : 'remove_circle_outline'; ?></span>
            Imagick
        </div>
        <div class="bk-env-chip" :class="<?php echo $webp_ok ? "'is-ok'" : "'is-bad'"; ?>">
            <span class="material-symbols-outlined"><?php echo $webp_ok ? 'check_circle' : 'cancel'; ?></span>
            WebP
        </div>
        <div class="bk-env-chip" :class="watermarkStudio.lazy_load ? 'is-ok' : 'is-muted'">
            <span class="material-symbols-outlined">hourglass_empty</span>
            Lazy Load
        </div>
    </div>

    <!-- Sub tabs -->
    <div class="bk-media-subtabs">
        <button type="button" class="bk-media-subtab" :class="{ 'is-active': mediaSubTab === 'watermark' }"
                @click="mediaSubTab = 'watermark'">
            <span class="material-symbols-outlined">branding_watermark</span>
            استودیو واترمارک
        </button>
        <button type="button" class="bk-media-subtab" :class="{ 'is-active': mediaSubTab === 'compress' }"
                @click="mediaSubTab = 'compress'; loadMediaLibrary(true)">
            <span class="material-symbols-outlined">compress</span>
            فشرده‌سازی تصاویر
        </button>
        <button type="button" class="bk-media-subtab" :class="{ 'is-active': mediaSubTab === 'modules' }"
                @click="mediaSubTab = 'modules'">
            <span class="material-symbols-outlined">tune</span>
            ماژول‌ها
        </button>
    </div>

    <!-- ========== WATERMARK STUDIO ========== -->
    <div x-show="mediaSubTab === 'watermark'" x-cloak class="bk-wm-studio bankai-card">
        <div class="bk-wm-studio-head">
            <h3>
                <span class="material-symbols-outlined">palette</span>
                استودیو واترمارک تعاملی
            </h3>
            <label class="bk-switch-inline">
                <input type="checkbox" x-model="watermarkStudio.enabled">
                <span>فعال‌سازی واترمارک</span>
            </label>
        </div>

        <!-- Preview on TOP -->
        <div class="bk-wm-preview-stage">
            <img class="bk-wm-preview-bg"
                 src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80"
                 alt="Preview">
            <div class="bk-wm-overlay" :style="getWatermarkPositionStyle()">
                <template x-if="watermarkStudio.type === 'text'">
                    <span class="bk-wm-text-badge"
                          :style="'opacity:' + (watermarkStudio.opacity / 100)"
                          x-text="watermarkStudio.text || '© BANKAI'"></span>
                </template>
                <template x-if="watermarkStudio.type === 'image' && watermarkStudio.image_url">
                    <img class="bk-wm-logo-preview" :src="watermarkStudio.image_url" alt=""
                         :style="'opacity:' + (watermarkStudio.opacity / 100)">
                </template>
            </div>
        </div>

        <!-- Tools BELOW -->
        <div class="bk-wm-tools">
            <div class="bk-wm-tool-block">
                <label class="bk-wm-label">نوع واترمارک</label>
                <div class="bk-wm-type-row">
                    <button type="button" class="bk-wm-type-btn" :class="{ 'is-active': watermarkStudio.type === 'text' }"
                            @click="watermarkStudio.type = 'text'">
                        <span class="material-symbols-outlined">title</span>
                        متن
                    </button>
                    <button type="button" class="bk-wm-type-btn" :class="{ 'is-active': watermarkStudio.type === 'image' }"
                            @click="watermarkStudio.type = 'image'">
                        <span class="material-symbols-outlined">image</span>
                        لوگو
                    </button>
                </div>
            </div>

            <div class="bk-wm-tool-block" x-show="watermarkStudio.type === 'text'">
                <label class="bk-wm-label">متن واترمارک</label>
                <input type="text" class="bk-wm-input" x-model="watermarkStudio.text" maxlength="80" placeholder="© BANKAI">
            </div>

            <div class="bk-wm-tool-block" x-show="watermarkStudio.type === 'image'" x-cloak>
                <label class="bk-wm-label">لوگوی واترمارک</label>
                <div class="bk-wm-logo-row">
                    <button type="button" class="bankai-btn-ghost" @click="pickWatermarkImage()">
                        <span class="material-symbols-outlined">photo_library</span>
                        انتخاب از رسانه
                    </button>
                    <span class="bk-muted" x-text="watermarkStudio.image_id ? ('ID: ' + watermarkStudio.image_id) : 'انتخاب نشده'"></span>
                </div>
            </div>

            <div class="bk-wm-tool-block">
                <label class="bk-wm-label">موقعیت</label>
                <div class="bk-wm-pos-grid">
                    <?php foreach ($positions as $pos => $meta): ?>
                    <button type="button"
                            class="bk-wm-pos-btn"
                            title="<?php echo esc_attr($meta['label']); ?>"
                            :class="{ 'is-active': watermarkStudio.position === '<?php echo esc_js($pos); ?>' }"
                            @click="watermarkStudio.position = '<?php echo esc_js($pos); ?>'">
                        <span class="material-symbols-outlined"><?php echo esc_html($meta['icon']); ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bk-wm-sliders">
                <div class="bk-wm-slider-item">
                    <div class="bk-wm-slider-head">
                        <span>شفافیت</span>
                        <strong x-text="watermarkStudio.opacity + '%'">75%</strong>
                    </div>
                    <input type="range" min="10" max="100" x-model="watermarkStudio.opacity">
                </div>
                <div class="bk-wm-slider-item">
                    <div class="bk-wm-slider-head">
                        <span>کیفیت</span>
                        <strong x-text="watermarkStudio.quality">82</strong>
                    </div>
                    <input type="range" min="40" max="100" x-model="watermarkStudio.quality">
                </div>
            </div>

            <div class="bk-wm-options">
                <label><input type="checkbox" x-model="watermarkStudio.apply_upload"> اعمال روی آپلودهای جدید</label>
                <label><input type="checkbox" x-model="watermarkStudio.apply_content"> اعمال در تبدیل گروهی</label>
                <label><input type="checkbox" x-model="watermarkStudio.lazy_load"> Lazy Load</label>
                <label><input type="checkbox" x-model="watermarkStudio.strip_exif"> حذف EXIF / GPS</label>
                <label><input type="checkbox" x-model="watermarkStudio.convert_webp"> ساخت نسخه WebP</label>
            </div>

            <button type="button" class="bankai-btn-primary bk-wm-save" @click="saveWatermarkStudio()" :disabled="busy">
                <span class="material-symbols-outlined">save</span>
                ذخیره تنظیمات رسانه
            </button>
        </div>
    </div>

    <!-- ========== COMPRESS TAB ========== -->
    <div x-show="mediaSubTab === 'compress'" x-cloak class="bk-compress-panel">
        <div class="bk-compress-toolbar bankai-card">
            <div class="bk-compress-toolbar-main">
                <div class="bk-field-inline">
                    <label>فرمت خروجی</label>
                    <select x-model="compressOpts.format">
                        <option value="webp">WebP</option>
                        <option value="jpeg">JPEG</option>
                        <option value="png">PNG</option>
                        <option value="keep">حفظ فرمت</option>
                    </select>
                </div>
                <div class="bk-field-inline">
                    <label>کیفیت</label>
                    <input type="number" min="40" max="95" x-model.number="compressOpts.quality" style="width:72px">
                </div>
                <div class="bk-field-inline">
                    <label>حداکثر عرض</label>
                    <input type="number" min="0" max="4000" x-model.number="compressOpts.maxWidth" placeholder="0=بدون" style="width:90px">
                </div>
            </div>
            <div class="bk-compress-toolbar-actions">
                <button type="button" class="bankai-btn-ghost" @click="loadMediaLibrary(true)" :disabled="mediaLib.loading">
                    <span class="material-symbols-outlined" :class="{ 'bk-spin': mediaLib.loading }">refresh</span>
                    تازه‌سازی
                </button>
                <button type="button" class="bankai-btn-primary" @click="compressAllMedia()" :disabled="mediaLib.busy || !mediaLib.items.length">
                    <span class="material-symbols-outlined">auto_fix_high</span>
                    فشرده‌سازی همه
                </button>
            </div>
        </div>

        <!-- Progress -->
        <div class="bk-compress-progress bankai-card" x-show="mediaLib.busy || mediaLib.progress > 0" x-cloak>
            <div class="bk-compress-progress-head">
                <span x-text="mediaLib.progressText || 'در حال پردازش…'"></span>
                <strong x-text="mediaLib.progress + '%'">0%</strong>
            </div>
            <div class="bk-compress-track">
                <div class="bk-compress-bar" :style="{ width: mediaLib.progress + '%' }"></div>
            </div>
            <div class="bk-compress-meta" x-show="mediaLib.doneCount || mediaLib.totalCount">
                <span x-text="(mediaLib.doneCount || 0) + ' / ' + (mediaLib.totalCount || 0)"></span>
                <span x-show="mediaLib.savedBytes" x-text="'صرفه‌جویی: ' + formatMediaBytes(mediaLib.savedBytes)"></span>
            </div>
        </div>

        <!-- Grid 3 columns -->
        <div class="bk-media-grid"
             @scroll.passive="onMediaGridScroll($event)">
            <template x-for="img in mediaLib.items" :key="img.id">
                <div class="bk-media-card" :class="{ 'is-done': img.optimized, 'is-working': img.working }">
                    <div class="bk-media-card-thumb">
                        <img :src="img.thumb || img.url" :alt="img.title" loading="lazy" decoding="async">
                        <span class="bk-media-format" x-text="(img.format || '—').toUpperCase()"></span>
                    </div>
                    <div class="bk-media-card-body">
                        <strong class="bk-media-card-title" x-text="img.title || ('#' + img.id)"></strong>
                        <div class="bk-media-card-sizes">
                            <span class="bk-size-old" x-text="formatMediaBytes(img.bytes)"></span>
                            <template x-if="img.bytes_after">
                                <span class="bk-size-new">
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                    <span x-text="formatMediaBytes(img.bytes_after)"></span>
                                </span>
                            </template>
                        </div>
                        <div class="bk-media-card-actions">
                            <a class="bk-icon-btn" :href="img.url" target="_blank" rel="noopener" title="مشاهده">
                                <span class="material-symbols-outlined">open_in_new</span>
                            </a>
                            <button type="button" class="bk-icon-btn is-primary" @click="compressOneMedia(img)"
                                    :disabled="img.working || mediaLib.busy" title="فشرده کردن">
                                <span class="material-symbols-outlined" :class="{ 'bk-spin': img.working }">compress</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="bk-media-empty bankai-card" x-show="!mediaLib.items.length && !mediaLib.loading">
            تصویری در کتابخانه رسانه یافت نشد.
        </div>
        <div class="bk-media-loadmore" x-show="mediaLib.hasMore">
            <button type="button" class="bankai-btn-ghost" @click="loadMediaLibrary(false)" :disabled="mediaLib.loading">
                <span x-show="!mediaLib.loading">بارگذاری بیشتر</span>
                <span x-show="mediaLib.loading">در حال بارگذاری…</span>
            </button>
        </div>
    </div>

    <!-- ========== MODULES ========== -->
    <div x-show="mediaSubTab === 'modules'" x-cloak class="bankai-grid-3" style="margin-bottom:32px;">
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
                        <div style="font-weight:700;font-size:14px;"
                             x-text="isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>'">
                            <?php echo esc_html($mod['title_fa'] ?? $mod['title'] ?? ''); ?>
                        </div>
                        <span class="bk-badge-soft"><?php echo $badge; ?></span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" class="bk-sub-module-toggle" data-module="<?php echo $mod_id; ?>"
                               x-model="mediaState['<?php echo $mod_id; ?>']"
                               @change="toggleMediaModule('<?php echo $mod_id; ?>')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size:12px;color:var(--bankai-text-muted);line-height:1.5;margin:0 0 16px;"
                   x-text="isRtl ? '<?php echo $desc_fa; ?>' : '<?php echo $desc_en; ?>'"></p>
            </div>
            <div style="border-top:1px solid var(--bankai-border-subtle);padding-top:12px;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:11px;font-weight:700;"
                      :style="mediaState['<?php echo $mod_id; ?>'] ? 'color:#0f7b3a' : 'color:#8a8886'"
                      x-text="mediaState['<?php echo $mod_id; ?>'] ? t('active') : t('disabled')">—</span>
                <button type="button" class="bankai-btn-ghost"
                        @click="openMediaDrawer('<?php echo $mod_id; ?>', isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>')">
                    پیکربندی
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Drawer -->
    <div x-show="mediaDrawer.show" x-cloak class="bankai-modal-shell" x-transition>
        <div class="bankai-modal-panel" @click.outside="mediaDrawer.show=false">
            <div class="bankai-modal-head">
                <h3 class="bankai-modal-title" x-text="mediaDrawer.title + (isRtl ? ' — تنظیمات' : ' — Settings')"></h3>
                <button type="button" class="bankai-modal-close" @click="mediaDrawer.show=false">&times;</button>
            </div>
            <div class="bankai-modal-body">
                <p style="font-size:13px;color:var(--bankai-text-muted);margin:0;line-height:1.6;">
                    تنظیمات عمومی این ماژول از استودیو واترمارک کنترل می‌شود.
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
/* Media shell — Fluent / Material */
.bk-media-shell { display:flex; flex-direction:column; gap:16px; }
.bk-media-page-head {
    display:flex; justify-content:space-between; align-items:center;
    padding:18px 20px; flex-wrap:wrap; gap:12px;
    border-radius:16px !important;
}
.bk-media-title {
    margin:0; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;
}
.bk-media-title .material-symbols-outlined { color:#0078d4; font-size:24px; }
.bk-media-sub { margin:4px 0 0; font-size:12px; color:#8a8886; }
.bk-media-head-actions { display:flex; gap:8px; flex-wrap:wrap; }

.bk-media-env-row { display:flex; flex-wrap:wrap; gap:8px; }
.bk-env-chip {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 14px; border-radius:999px; font-size:12px; font-weight:700;
    background:#fff; border:1px solid rgba(0,0,0,.06); box-shadow:0 1px 2px rgba(0,0,0,.04);
}
.bk-env-chip .material-symbols-outlined { font-size:18px; }
.bk-env-chip.is-ok { color:#0f7b3a; background:#dff6e8; border-color:transparent; }
.bk-env-chip.is-bad { color:#c42b1c; background:#fde7e9; border-color:transparent; }
.bk-env-chip.is-muted { color:#8a8886; }

.bk-media-subtabs {
    display:flex; gap:6px; flex-wrap:wrap;
    padding:6px; background:rgba(255,255,255,.9); border-radius:999px;
    border:1px solid rgba(0,0,0,.06); width:fit-content; max-width:100%;
}
.bk-media-subtab {
    display:inline-flex; align-items:center; gap:6px;
    padding:10px 16px; border:none; border-radius:999px; background:transparent;
    font-size:12px; font-weight:700; cursor:pointer; color:#605e5c;
    transition: background .15s ease, color .15s ease;
}
.bk-media-subtab .material-symbols-outlined { font-size:18px; }
.bk-media-subtab:hover { background:rgba(0,0,0,.04); color:#1a1a1a; }
.bk-media-subtab.is-active { background:rgba(0,120,212,.1); color:#0078d4; }

/* Watermark studio */
.bk-wm-studio { padding:20px; border-radius:16px !important; }
.bk-wm-studio-head {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:16px; flex-wrap:wrap; gap:10px;
}
.bk-wm-studio-head h3 {
    margin:0; font-size:15px; font-weight:800; display:flex; align-items:center; gap:8px;
}
.bk-switch-inline { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; cursor:pointer; }

.bk-wm-preview-stage {
    position:relative; width:100%; height:280px;
    border-radius:16px; overflow:hidden;
    background:#f3f3f3; border:1px solid rgba(0,0,0,.06);
    margin-bottom:18px;
}
.bk-wm-preview-bg { width:100%; height:100%; object-fit:cover; display:block; }
.bk-wm-overlay { position:absolute; padding:12px; pointer-events:none; transition:all .25s ease; }
.bk-wm-text-badge {
    background:rgba(26,26,26,.72); color:#fff; padding:8px 16px; border-radius:8px;
    font-size:13px; font-weight:800; border:1px solid rgba(255,255,255,.2);
    backdrop-filter:blur(8px);
}
.bk-wm-logo-preview { max-width:140px; filter:drop-shadow(0 2px 8px rgba(0,0,0,.25)); }

.bk-wm-tools { display:flex; flex-direction:column; gap:16px; }
.bk-wm-label { display:block; font-size:11px; font-weight:700; color:#605e5c; margin-bottom:8px; }
.bk-wm-type-row { display:flex; gap:8px; }
.bk-wm-type-btn {
    flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
    padding:12px; border-radius:12px; border:1px solid rgba(0,0,0,.08);
    background:#fafafa; font-size:12px; font-weight:700; cursor:pointer; color:#605e5c;
}
.bk-wm-type-btn.is-active { background:rgba(0,120,212,.1); color:#0078d4; border-color:transparent; }
.bk-wm-input {
    width:100%; padding:11px 14px; border-radius:10px; border:1px solid rgba(0,0,0,.08);
    background:#fafafa; font-size:13px;
}
.bk-wm-logo-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }

.bk-wm-pos-grid {
    display:grid; grid-template-columns:repeat(3, 48px); gap:8px;
}
.bk-wm-pos-btn {
    width:48px; height:48px; border-radius:12px;
    border:1px solid rgba(0,0,0,.08); background:#fafafa;
    display:grid; place-items:center; cursor:pointer; color:#605e5c;
    transition: all .15s ease;
}
.bk-wm-pos-btn .material-symbols-outlined { font-size:22px; }
.bk-wm-pos-btn:hover { background:rgba(0,120,212,.08); color:#0078d4; }
.bk-wm-pos-btn.is-active {
    background:#0078d4; color:#fff; border-color:transparent;
    box-shadow:0 2px 8px rgba(0,120,212,.35);
}

.bk-wm-sliders { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.bk-wm-slider-head { display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px; color:#605e5c; }
.bk-wm-slider-head strong { color:#0078d4; font-family:monospace; }
.bk-wm-sliders input[type=range] { width:100%; accent-color:#0078d4; }

.bk-wm-options {
    display:grid; grid-template-columns:1fr 1fr; gap:10px;
    padding:14px; background:#f8f9fa; border-radius:12px; border:1px solid rgba(0,0,0,.05);
}
.bk-wm-options label {
    display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; cursor:pointer;
}
.bk-wm-save { width:100%; justify-content:center; display:inline-flex; gap:8px; padding:12px !important; }

/* Compress */
.bk-compress-panel { display:flex; flex-direction:column; gap:14px; }
.bk-compress-toolbar {
    display:flex; justify-content:space-between; align-items:flex-end; gap:12px;
    padding:16px 18px; flex-wrap:wrap; border-radius:16px !important;
}
.bk-compress-toolbar-main { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
.bk-compress-toolbar-actions { display:flex; gap:8px; flex-wrap:wrap; }
.bk-field-inline { display:flex; flex-direction:column; gap:4px; }
.bk-field-inline label { font-size:11px; font-weight:700; color:#605e5c; }
.bk-field-inline select, .bk-field-inline input {
    padding:9px 12px; border-radius:10px; border:1px solid rgba(0,0,0,.08); background:#fafafa;
}

.bk-compress-progress { padding:16px 18px; border-radius:16px !important; }
.bk-compress-progress-head { display:flex; justify-content:space-between; font-size:12px; font-weight:700; margin-bottom:8px; }
.bk-compress-track { height:8px; border-radius:999px; background:rgba(0,0,0,.06); overflow:hidden; }
.bk-compress-bar {
    height:100%; border-radius:999px;
    background:linear-gradient(90deg, #0078d4, #6b4eff);
    transition:width .3s ease;
}
.bk-compress-meta { display:flex; gap:16px; margin-top:8px; font-size:11px; color:#605e5c; font-weight:600; }

.bk-media-grid {
    display:grid; grid-template-columns:repeat(3, 1fr); gap:14px;
    max-height:70vh; overflow-y:auto; padding:4px 2px 12px;
}
.bk-media-card {
    background:#fff; border:1px solid rgba(0,0,0,.06); border-radius:16px;
    overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.05);
    transition: box-shadow .2s ease, transform .15s ease;
}
.bk-media-card:hover { box-shadow:0 6px 20px rgba(0,0,0,.08); transform:translateY(-2px); }
.bk-media-card.is-done { border-color:rgba(15,123,58,.25); }
.bk-media-card.is-working { opacity:.75; }
.bk-media-card-thumb { position:relative; height:140px; background:#f3f3f3; }
.bk-media-card-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.bk-media-format {
    position:absolute; top:8px; inset-inline-start:8px;
    background:rgba(0,0,0,.55); color:#fff; font-size:10px; font-weight:800;
    padding:3px 8px; border-radius:999px; backdrop-filter:blur(6px);
}
.bk-media-card-body { padding:12px; }
.bk-media-card-title {
    display:block; font-size:12px; font-weight:700; margin-bottom:6px;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.bk-media-card-sizes { display:flex; align-items:center; gap:6px; font-size:11px; font-weight:700; margin-bottom:8px; }
.bk-size-old { color:#c42b1c; background:#fde7e9; padding:3px 8px; border-radius:999px; }
.bk-size-new { color:#0f7b3a; background:#dff6e8; padding:3px 8px; border-radius:999px; display:inline-flex; align-items:center; gap:2px; }
.bk-size-new .material-symbols-outlined { font-size:14px; }
.bk-media-card-actions { display:flex; gap:6px; }
.bk-icon-btn {
    width:36px; height:36px; border-radius:10px; border:1px solid rgba(0,0,0,.06);
    background:#fafafa; display:grid; place-items:center; cursor:pointer; color:#605e5c;
}
.bk-icon-btn.is-primary { background:rgba(0,120,212,.1); color:#0078d4; border-color:transparent; }
.bk-icon-btn .material-symbols-outlined { font-size:18px; }
.bk-media-empty { padding:28px; text-align:center; color:#8a8886; font-size:13px; border-radius:16px !important; }
.bk-media-loadmore { text-align:center; padding:8px; }
.bk-badge-soft {
    background:rgba(0,120,212,.1); color:#0078d4; font-size:10px; font-weight:700;
    padding:2px 8px; border-radius:999px; display:inline-block; margin-top:4px;
}
.bk-spin { animation: bk-spin 1s linear infinite; }
@keyframes bk-spin { to { transform: rotate(360deg); } }

@media (max-width: 1100px) {
    .bk-media-grid { grid-template-columns:repeat(2, 1fr); }
}
@media (max-width: 700px) {
    .bk-media-grid { grid-template-columns:1fr; }
    .bk-wm-sliders, .bk-wm-options { grid-template-columns:1fr; }
    .bk-wm-preview-stage { height:200px; }
}
</style>
