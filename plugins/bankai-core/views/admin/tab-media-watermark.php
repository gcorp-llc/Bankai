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
$avif_ok = !empty($env['avif']) || function_exists('imageavif');

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

<div id="tab-media-watermark" class="bankai-tab-pane bk-wm-shell" x-show="activeTab === 'media-watermark'" x-cloak>

    <!-- Hero -->
    <div class="bk-lg-header bk-wm-hero">
        <div class="bk-wm-hero__main">
            <div class="bk-wm-hero__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.25 15.75l5.16-5.16a2.25 2.25 0 013.18 0l5.16 5.16m-1.5-1.5l1.41-1.41a2.25 2.25 0 013.18 0l2.91 2.91M3.75 6h16.5A1.5 1.5 0 0121.75 7.5v12a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-12A1.5 1.5 0 013.75 6z"/></svg>
            </div>
            <div>
                <div class="bk-wm-hero__title-row">
                    <h2>رسانه و واترمارک</h2>
                    <span class="bk-wm-pill">موتور نسل سوم</span>
                </div>
                <p>فشرده‌سازی، WebP، واترمارک تعاملی و پاکسازی متادیتا EXIF</p>
            </div>
        </div>
        <div class="bk-wm-hero__actions">
            <button type="button" class="bk-wm-chip-btn" @click="regenerateThumbnails()" :disabled="busy">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 9h5V4M3 15v5h5M21 9A9 9 0 005.6 5.6M3 15a9 9 0 0015.4 3.4"/></svg>
                بازتولید بندانگشتی
            </button>
        </div>
    </div>

    <!-- Env status -->
    <div class="bk-wm-env">
        <span class="bk-wm-env-label">وضعیت ماژول‌های پردازش:</span>
        <span class="bk-wm-env-chip is-ok"><i></i> Lazy Load</span>
        <span class="bk-wm-env-chip <?php echo $webp_ok ? 'is-ok' : 'is-bad'; ?>"><i></i> WebP / AVIF</span>
        <span class="bk-wm-env-chip <?php echo $imagick_ok ? 'is-ok' : 'is-muted'; ?>"><i></i> Imagick</span>
        <span class="bk-wm-env-chip <?php echo $gd_ok ? 'is-ok' : 'is-bad'; ?>"><i></i> GD Library</span>
    </div>

    <!-- Sub tabs -->
    <div class="bk-art-tabs bk-lg-tabs bk-wm-subtabs">
        <button type="button" class="bk-lg-tab bk-wm-subtab" :class="{ 'is-active': mediaSubTab === 'watermark' }" @click="mediaSubTab = 'watermark'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/></svg>
            استودیو واترمارک
        </button>
        <button type="button" class="bk-lg-tab bk-wm-subtab" :class="{ 'is-active': mediaSubTab === 'compress' }" @click="mediaSubTab = 'compress'; loadMediaLibrary && loadMediaLibrary(true)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14h6v6H4zM14 4h6v6h-6zM14 14h6v6h-6zM4 4h6v6H4z"/></svg>
            فشرده‌سازی تصاویر
        </button>
        <button type="button" class="bk-lg-tab bk-wm-subtab" :class="{ 'is-active': mediaSubTab === 'modules' }" @click="mediaSubTab = 'modules'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            ماژول‌ها و هوک‌ها
        </button>
    </div>

    <!-- ========== WATERMARK STUDIO ========== -->
    <div x-show="mediaSubTab === 'watermark'" x-cloak class="bk-wm-studio">

        <!-- Service enable bar -->
        <div class="bk-wm-service-bar">
            <div class="bk-wm-service-bar__text">
                <div class="bk-wm-service-bar__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l2.5 1.5"/></svg>
                </div>
                <div>
                    <strong>استودیو پیشرفته واترمارک</strong>
                    <p>افزودن نام تجاری متنی یا لوگوی گرافیکی به تمامی رسانه‌ها همراه با تنظیم دقیق کادر، فونت و استثنائات</p>
                </div>
            </div>
            <label class="bk-wm-toggle">
                <span>فعال‌سازی سرویس</span>
                <input type="checkbox" x-model="watermarkStudio.enabled">
                <i></i>
            </label>
        </div>

        
        <div class="bk-wm-layout">
            <!-- COL 1: Preview + Position -->
            <div class="bk-wm-preview-col">
                <div class="bk-wm-card bk-wm-preview-card">
                    <div class="bk-wm-card__head">
                        <div class="bk-wm-live-dot">
                            <span class="ping"></span>
                            <span class="dot"></span>
                            <strong>پیش‌نمایش زنده (Live Mockup)</strong>
                        </div>
                        <div class="bk-wm-preview-tools">
                            <button type="button" class="bk-wm-icon-btn" title="ریست" @click="setWmPosition('bottom-right')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 9h5V4M3 15v5h5M21 9A9 9 0 005.6 5.6M3 15a9 9 0 0015.4 3.4"/></svg>
                            </button>
                            <button type="button" class="bk-wm-mini-btn" @click="pickWmPreviewImage()">انتخاب تصویر</button>
                            <button type="button" class="bk-wm-mini-btn" @click="cycleWmPreview()">نمونه بعدی</button>
                        </div>
                    </div>
                    <div class="bk-wm-canvas">
                        <img class="bk-wm-canvas__img"
                             :src="wmPreviewSrc()"
                             @error="onWmPreviewError($event)"
                             alt="preview">
                        <div class="bk-wm-canvas__vignette"></div>
                        <div class="bk-wm-canvas__badge">WebP · 1920×1080</div>
                        <div class="bk-wm-canvas__wm" :class="'is-' + (watermarkStudio.position || 'bottom-right') + (watermarkStudio.text_cover==='width'?' is-cover-width':'') + (watermarkStudio.text_cover==='height'?' is-cover-height':'') + (watermarkStudio.text_cover==='banner'?' is-cover-banner':'')" :style="wmBadgeStyle()">
                            <template x-if="watermarkStudio.type === 'text'">
                                <span class="bk-wm-badge"
                                      :style="{
                                        fontSize: (watermarkStudio.text_size||18)+'px',
                                        color: watermarkStudio.text_color || '#fff',
                                        background: watermarkStudio.box_enabled ? (watermarkStudio.box_color||'#000000') : 'transparent',
                                        opacity: watermarkStudio.box_enabled ? 1 : 1,
                                        padding: watermarkStudio.box_enabled ? ((watermarkStudio.box_padding||8)+'px') : '0',
                                        borderRadius: (watermarkStudio.box_radius||6)+'px',
                                        fontWeight: '700',
                                        backdropFilter: watermarkStudio.box_enabled ? 'blur(6px)' : 'none'
                                      }"
                                      x-text="watermarkStudio.text || '© BANKAI MEDIA'"></span>
                            </template>
                            <template x-if="watermarkStudio.type === 'image' && watermarkStudio.image_url">
                                <img class="bk-wm-logo" :src="watermarkStudio.image_url" alt="wm"
                                     :style="'width:'+(watermarkStudio.image_scale||20)+'%'">
                            </template>
                        </div>
                    </div>
                    <div class="bk-wm-savings">
                        <div class="bk-wm-savings__head">
                            <span>✓ محاسبه کاهش حجم و کارایی</span>
                            <strong>−۶۸٪ صرفه‌جویی</strong>
                        </div>
                        <div class="bk-wm-savings__grid">
                            <div><span>حجم اصلی</span><em>1,420 KB (JPEG)</em></div>
                            <div><span>خروجی با واترمارک</span><em class="is-accent">452 KB (WebP)</em></div>
                        </div>
                    </div>
                </div>

                <!-- Position under preview -->
                <section class="bk-wm-card">
                    <div class="bk-wm-card__head">
                        <h3><i class="dot is-indigo"></i> موقعیت قرارگیری و شفافیت</h3>
                        <span class="bk-wm-muted">شبکه ۹ جهتی</span>
                    </div>
                    <div class="bk-wm-pos-under">
                        <div class="bk-wm-pos-grid" role="group" aria-label="position">
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'top-left' }" @click="setWmPosition('top-left')" title="بالا چپ">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 18L6 6M6 6h8M6 6v8"/></svg>
                            </button>
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'top-center' }" @click="setWmPosition('top-center')" title="بالا وسط">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 19V5M12 5l-5 5M12 5l5 5"/></svg>
                            </button>
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'top-right' }" @click="setWmPosition('top-right')" title="بالا راست">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 18L18 6M18 6h-8M18 6v8"/></svg>
                            </button>
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'center-left' || watermarkStudio.position === 'middle-left' }" @click="setWmPosition('center-left')" title="وسط چپ">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M5 12l5-5M5 12l5 5"/></svg>
                            </button>
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'center' }" @click="setWmPosition('center')" title="مرکز">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="3"/><path d="M12 3v4M12 17v4M3 12h4M17 12h4"/></svg>
                            </button>
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'center-right' || watermarkStudio.position === 'middle-right' }" @click="setWmPosition('center-right')" title="وسط راست">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M19 12l-5-5M19 12l-5 5"/></svg>
                            </button>
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'bottom-left' }" @click="setWmPosition('bottom-left')" title="پایین چپ">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 6L6 18M6 18h8M6 18v-8"/></svg>
                            </button>
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'bottom-center' }" @click="setWmPosition('bottom-center')" title="پایین وسط">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M12 19l-5-5M12 19l5-5"/></svg>
                            </button>
                            <button type="button" class="bk-wm-pos-btn" :class="{ 'is-active': watermarkStudio.position === 'bottom-right' }" @click="setWmPosition('bottom-right')" title="پایین راست">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 6l12 12M18 18h-8M18 18v-8"/></svg>
                            </button>
                        </div>
                        <div class="bk-wm-fields">
                            <label class="bk-wm-field">
                                <span>شفافیت <em x-text="(watermarkStudio.opacity||75)+'%'"></em></span>
                                <input type="range" min="10" max="100" x-model.number="watermarkStudio.opacity">
                            </label>
                            <label class="bk-wm-field">
                                <span>کیفیت خروجی <em x-text="(watermarkStudio.quality||82)+'%'"></em></span>
                                <input type="range" min="30" max="100" x-model.number="watermarkStudio.quality">
                            </label>
                        </div>
                    </div>
                </section>

                <section class="bk-wm-card">
                    <div class="bk-wm-card__head">
                        <h3><i class="dot is-amber"></i> استثناها</h3>
                        <span class="bk-wm-muted mono">Regex &amp; Dimensions</span>
                    </div>
                    <label class="bk-wm-field">
                        <span>فیلتر نام فایل (هر خط یک کلمه)</span>
                        <textarea rows="4" x-model="watermarkStudio.exclude_patterns" placeholder="logo&#10;banner&#10;icon"></textarea>
                        <small>نام فایل یا URL حاوی این کلمات واترمارک نمی‌شود.</small>
                    </label>
                    <label class="bk-wm-field" style="max-width:260px">
                        <span>حداقل ابعاد (px)</span>
                        <input type="number" min="0" max="2000" x-model.number="watermarkStudio.min_dimension">
                    </label>
                    <div class="bk-wm-checks">
                        <label class="bk-wm-check"><input type="checkbox" x-model="watermarkStudio.apply_upload"><span>اعمال خودکار روی آپلودهای جدید</span></label>
                        <label class="bk-wm-check"><input type="checkbox" x-model="watermarkStudio.apply_content"><span>اعمال در تبدیل گروهی</span></label>
                        <label class="bk-wm-check"><input type="checkbox" x-model="watermarkStudio.lazy_load"><span>سازگاری با Lazy Load</span></label>
                    </div>
                </section>
            </div>

            <!-- COL 2: Typography + Exceptions -->
            <div class="bk-wm-controls-col">
                <section class="bk-wm-card">
                    <div class="bk-wm-card__head">
                        <h3><i class="dot is-purple"></i> تنظیمات محتوا و تایپوگرافی</h3>
                        <div class="bk-wm-type-switch">
                            <button type="button" :class="{ 'is-active': watermarkStudio.type === 'text' }" @click="watermarkStudio.type='text'">متنی</button>
                            <button type="button" :class="{ 'is-active': watermarkStudio.type === 'image' }" @click="watermarkStudio.type='image'">تصویری (لوگو)</button>
                        </div>
                    </div>
                    <div x-show="watermarkStudio.type === 'text'" class="bk-wm-fields">
                        <label class="bk-wm-field">
                            <span>متن واترمارک</span>
                            <input type="text" x-model="watermarkStudio.text" maxlength="80" placeholder="© BANKAI MEDIA">
                        </label>
                        <div class="bk-wm-grid-2">
                            
                            <label class="bk-wm-field">
                                <span>پوشش متن روی تصویر</span>
                                <select x-model="watermarkStudio.text_cover">
                                    <option value="none">عادی (گوشه)</option>
                                    <option value="width">تمام عرض</option>
                                    <option value="height">تمام ارتفاع</option>
                                    <option value="banner">بنر پایین (گرادیان)</option>
                                </select>
                            </label>
                            <label class="bk-wm-field">
                                <span>پس‌زمینه کادر</span>
                                <select x-model="watermarkStudio.box_style">
                                    <option value="solid">یک‌دست</option>
                                    <option value="gradient">گرادیان شفاف→پررنگ</option>
                                    <option value="none">بدون پس‌زمینه</option>
                                </select>
                            </label>
                            <label class="bk-wm-field" x-show="watermarkStudio.box_style !== 'none'">
                                <span>رنگ پس‌زمینه</span>
                                <input type="color" x-model="watermarkStudio.box_color">
                            </label>
                            
                            <label class="bk-wm-field">
                                <span>سایز <em x-text="(watermarkStudio.text_size||18)+'px'"></em></span>
                                <input type="range" min="12" max="64" x-model.number="watermarkStudio.text_size">
                            </label>
                        </div>
                        <label class="bk-wm-check">
                            <input type="checkbox" x-model="watermarkStudio.box_enabled">
                            <span>کادر دور متن (Background Badge)</span>
                        </label>
                        <div class="bk-wm-grid-2" x-show="watermarkStudio.box_enabled" x-cloak>
                            <label class="bk-wm-field">
                                <span>پدینگ <em x-text="(watermarkStudio.box_padding||8)+'px'"></em></span>
                                <input type="range" min="2" max="30" x-model.number="watermarkStudio.box_padding">
                            </label>
                            <label class="bk-wm-field">
                                <span>گردی گوشه <em x-text="(watermarkStudio.box_radius||6)+'px'"></em></span>
                                <input type="range" min="0" max="25" x-model.number="watermarkStudio.box_radius">
                            </label>
                        </div>
                        <div class="bk-wm-grid-2">
                            <label class="bk-wm-field">
                                <span>رنگ متن</span>
                                <input type="color" x-model="watermarkStudio.text_color">
                            </label>
                            <label class="bk-wm-field" x-show="watermarkStudio.box_enabled" x-cloak>
                                <span>رنگ کادر</span>
                                <input type="color" x-model="watermarkStudio.box_color">
                            </label>
                        </div>
                    </div>
                    <div x-show="watermarkStudio.type === 'image'" x-cloak class="bk-wm-fields">
                        <div class="bk-wm-logo-pick">
                            <div class="bk-wm-logo-pick__preview" x-show="watermarkStudio.image_url">
                                <img :src="watermarkStudio.image_url" alt="logo">
                            </div>
                            <div class="bk-wm-logo-pick__actions">
                                <button type="button" class="bk-wm-mini-btn is-primary" @click="pickWatermarkImage()">انتخاب لوگو</button>
                                <button type="button" class="bk-wm-mini-btn" x-show="watermarkStudio.image_url" @click="watermarkStudio.image_id=0; watermarkStudio.image_url=''">حذف</button>
                            </div>
                        </div>
                        <label class="bk-wm-field">
                            <span>مقیاس لوگو <em x-text="(watermarkStudio.image_scale||20)+'%'"></em></span>
                            <input type="range" min="5" max="50" x-model.number="watermarkStudio.image_scale">
                        </label>
                    </div>
                </section>

                <!-- Font card (separate) -->
                <section class="bk-wm-card bk-wm-font-card">
                    <div class="bk-wm-card__head">
                        <h3><i class="dot is-indigo"></i> فونت واترمارک</h3>
                        <span class="bk-wm-muted">انتخاب زنده روی پیش‌نمایش</span>
                    </div>
                    <div class="bk-wm-font-grid">
                        <template x-for="f in wmFontList()" :key="f.id">
                            <button type="button" class="bk-wm-font-item" :class="{ 'is-active': watermarkStudio.text_font === f.id }"
                                    @click="selectWmFont(f)">
                                <div class="bk-wm-font-item__sample" :style="f.style" x-text="f.sample || 'Aa آب'"></div>
                                <div class="bk-wm-font-item__name" x-text="f.name"></div>
                            </button>
                        </template>
                    </div>
                    <div class="bk-wm-font-upload">
                        <strong style="font-size:12px;display:block;margin-bottom:8px">آپلود فونت سفارشی</strong>
                        <input type="file" accept=".ttf,.otf,.woff,.woff2,font/ttf,font/otf,application/font-woff" @change="uploadWmFont($event)">
                        <p class="bk-wm-muted" style="margin:8px 0 0;font-size:11px" x-show="watermarkStudio.custom_font_name"
                           x-text="'فعال: '+watermarkStudio.custom_font_name"></p>
                        <p class="bk-wm-muted" style="margin:6px 0 0;font-size:11px" x-show="wmFontBusy">در حال آپلود…</p>
                    </div>
                    <!-- Color palette solid + transparent -->
                    <div style="margin-top:14px">
                        <span style="font-size:12px;font-weight:700;display:block;margin-bottom:6px">رنگ متن</span>
                        <div class="bk-wm-swatches">
                            <template x-for="sw in wmColorSwatches()" :key="sw">
                                <button type="button" class="bk-wm-swatch" :class="{ 'is-active': watermarkStudio.text_color === sw }"
                                        :style="sw === 'transparent' ? '' : 'background:'+sw"
                                        :class="sw==='transparent' ? 'is-clear' : ''"
                                        @click="watermarkStudio.text_color = sw"></button>
                            </template>
                            <input type="color" x-model="watermarkStudio.text_color" style="width:32px;height:28px;border:none;padding:0;cursor:pointer">
                        </div>
                        <span style="font-size:12px;font-weight:700;display:block;margin:12px 0 6px">رنگ پس‌زمینه کادر</span>
                        <div class="bk-wm-swatches">
                            <button type="button" class="bk-wm-swatch is-clear" :class="{ 'is-active': watermarkStudio.box_style === 'none' }" @click="watermarkStudio.box_style='none'"></button>
                            <template x-for="sw in wmBgSwatches()" :key="'bg-'+sw">
                                <button type="button" class="bk-wm-swatch" :class="{ 'is-active': watermarkStudio.box_color === sw }"
                                        :style="'background:'+sw" @click="watermarkStudio.box_color=sw; watermarkStudio.box_style=watermarkStudio.box_style==='none'?'solid':watermarkStudio.box_style"></button>
                            </template>
                            <input type="color" x-model="watermarkStudio.box_color" style="width:32px;height:28px;border:none;padding:0">
                        </div>
                    </div>
                </section>


                

                <div class="bk-wm-actions-bar">
                    <div class="bk-wm-actions-bar__btns">
                        <button type="button" class="bk-wm-btn-primary" :disabled="busy" @click="saveWatermarkStudio()">
                            <span class="bk-btn-spin" x-show="busy" x-cloak></span>
                            ذخیره تنظیمات
                        </button>
                        <button type="button" class="bk-wm-btn-ghost" @click="openWmBulkApply && openWmBulkApply()">اعمال گروهی روی کتابخانه</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- COMPRESS -->
    
    
    <!-- Bulk watermark progress modal -->
    <div x-show="wmBulkModal.show" x-cloak class="bankai-modal-shell bk-wm-bulk-modal" style="z-index:100080">
        <div class="bankai-modal-panel" @click.outside="!wmBulkModal.running && (wmBulkModal.show=false)">
            <div class="bankai-modal-head">
                <h3 class="bankai-modal-title" x-text="wmBulkModal.title || 'اعمال گروهی واترمارک'"></h3>
                <button type="button" class="bankai-modal-close" @click="wmBulkModal.show=false" :disabled="wmBulkModal.running">&times;</button>
            </div>
            <div class="bankai-modal-body">
                <div class="bk-compress-progress" x-show="wmBulkModal.running || wmBulkModal.progress > 0">
                    <div class="bk-compress-bar"><i :style="'width:'+(wmBulkModal.progress||0)+'%'"></i></div>
                    <span x-text="wmBulkModal.progressText || ''"></span>
                </div>
                <div class="bk-wm-bulk-list">
                    <template x-for="row in (wmBulkModal.items || [])" :key="row.id">
                        <div class="bk-wm-bulk-row" :class="{ 'is-done': row.status==='done', 'is-work': row.status==='work', 'is-fail': row.status==='fail' }">
                            <img :src="row.thumb || row.url" alt="">
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" x-text="row.title"></div>
                                <div style="color:#94a3b8;font-size:11px" x-text="row.statusText || row.status"></div>
                            </div>
                            <span x-show="row.status==='work'" class="bk-btn-spin"></span>
                            <span x-show="row.status==='done'" style="color:#16a34a">✓</span>
                            <span x-show="row.status==='fail'" style="color:#dc2626">✗</span>
                        </div>
                    </template>
                </div>
            </div>
            <div class="bankai-modal-foot">
                <button type="button" class="bankai-btn-ghost" @click="wmBulkModal.show=false" :disabled="wmBulkModal.running">بستن</button>
            </div>
        </div>
    </div>

    
    <div x-show="mediaSubTab === 'compress'" x-cloak class="bk-compress-shell">
        <div class="bk-compress-toolbar">
            <div class="bk-compress-toolbar__info">
                <strong>فشرده‌سازی تصاویر</strong>
                <p style="margin:4px 0 0;font-size:12px;color:#64748b">فقط فشرده‌سازی — واترمارک از تب استودیو اعمال می‌شود</p>
            </div>
            <div class="bk-compress-opts">
                <label class="bk-wm-field"><span>فرمت</span>
                    <select x-model="compressOpts.format">
                        <option value="webp">WebP</option>
                        <option value="jpeg">JPEG</option>
                        <option value="png">PNG</option>
                    </select>
                </label>
                <label class="bk-wm-field">
                    <span>کیفیت <em x-text="(compressOpts.quality||82)+'%'"></em></span>
                    <input type="range" min="40" max="95" x-model.number="compressOpts.quality">
                </label>
                <label class="bk-wm-field">
                    <span>حداکثر عرض</span>
                    <input type="number" min="400" max="4000" x-model.number="compressOpts.maxWidth">
                </label>
            </div>
            <div class="bk-compress-actions">
                <button type="button" class="bk-wm-btn-primary" :disabled="mediaLib.busy || mediaLib.loading" @click="startBulkCompress && startBulkCompress(false)">
                    <span class="bk-btn-spin" x-show="mediaLib.busy && !mediaLib.selectedOnly" x-cloak></span>
                    فشرده‌سازی گروهی
                </button>
                <button type="button" class="bk-wm-btn-ghost" :disabled="mediaLib.busy || selectedMediaCount()===0" @click="startBulkCompress && startBulkCompress(true)">
                    <span class="bk-btn-spin" x-show="mediaLib.busy && mediaLib.selectedOnly" x-cloak></span>
                    فشرده‌سازی انتخاب‌شده‌ها (<span x-text="selectedMediaCount()">0</span>)
                </button>
            </div>
            <div class="bk-compress-progress" x-show="mediaLib.busy || mediaLib.progress > 0" x-cloak>
                <div class="bk-compress-bar"><i :style="'width:'+(mediaLib.progress||0)+'%'"></i></div>
                <span x-text="mediaLib.progressText || ''"></span>
            </div>
        </div>

        <div class="bk-compress-loading" x-show="mediaLib.loading" x-cloak>
            <div class="bk-compress-spinner"></div>
            <span>در حال بارگذاری تصاویر…</span>
        </div>

        <div class="bk-compress-grid" x-show="!mediaLib.loading">
            <template x-for="img in (mediaLib.items || [])" :key="img.id">
                <div class="bk-c-card" :class="{ 'is-selected': img.selected }" @click="img.selected = !img.selected">
                    <img class="bk-c-card__img" :src="img.thumb || img.url" :alt="img.title">
                    <span class="bk-c-chip is-fmt" x-text="(img.mime || img.format || 'IMG').toString().replace('image/','').toUpperCase()"></span>
                    <span class="bk-c-chip is-size" x-text="img.size_label || img.size || '—'"></span>
                    <div class="bk-c-card__name" x-text="img.title || ('#'+img.id)"></div>
                </div>
            </template>
        </div>
        <div class="bk-wm-card" style="text-align:center;padding:28px;margin-top:12px" x-show="!(mediaLib.items||[]).length && !mediaLib.loading">
            تصویری یافت نشد.
        </div>
        <div style="margin-top:14px;text-align:center" x-show="mediaLib.hasMore">
            <button type="button" class="bk-wm-btn-ghost" @click="loadMediaLibrary(false)" :disabled="mediaLib.loading">بارگذاری بیشتر</button>
        </div>
    </div>

<div x-show="mediaSubTab === 'modules'" x-cloak class="bk-media-modules">
        <h3 style="margin:0 0 14px;font-size:15px;font-weight:800;color:#0f172a">ماژول‌ها و هوک‌های رسانه</h3>
        <div class="bk-seo-mod-grid">
            <?php if (empty($media_mods)): ?>
            <div class="bk-seo-mod-card" style="grid-column:1/-1;text-align:center;color:#94a3b8">ماژول ثبت نشده است.</div>
            <?php else: foreach ($media_mods as $mod):
                $mod_id = esc_attr($mod['id'] ?? '');
                $title_fa = esc_html($mod['title_fa'] ?? $mod['title'] ?? '');
                $desc_fa = esc_html($mod['description_fa'] ?? $mod['description'] ?? '');
                $badge = esc_html($mod['badge'] ?? '');
                $is_cdn = (bool) preg_match('/cdn|cloud|r2|bunny|s3|arvan|abr|liara/i', $mod_id . ' ' . ($mod['title'] ?? ''));
            ?>
            <div class="bk-seo-mod-card" :class="{ 'is-on': mediaState['<?php echo $mod_id; ?>'] }">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;gap:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="bk-seo-mod-icon">📦</div>
                            <div>
                                <div style="font-weight:700;font-size:13px;color:#0F172A;line-height:1.35;"><?php echo $title_fa; ?></div>
                                <?php if ($badge): ?><span class="bk-seo-badge" style="background:#3B82F61A;color:#3B82F6"><?php echo $badge; ?></span><?php endif; ?>
                            </div>
                        </div>
                        <label class="bankai-switch">
                            <input type="checkbox" x-model="mediaState['<?php echo $mod_id; ?>']" @change="toggleMediaModule('<?php echo $mod_id; ?>')">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <p style="font-size:12px;color:#64748B;line-height:1.6;margin:0 0 14px;"><?php echo $desc_fa; ?></p>
                </div>
                <div class="bk-seo-mod-foot">
                    <span :style="mediaState['<?php echo $mod_id; ?>'] ? 'color:#16A34A' : 'color:#94A3B8'"
                          x-text="mediaState['<?php echo $mod_id; ?>'] ? 'فعال' : 'غیرفعال'"></span>
                    <?php if ($is_cdn): ?>
                    <button type="button" class="bk-seo-btn-sm" @click="mediaState['<?php echo $mod_id; ?>']=true">تنظیمات CDN</button>
                    <?php else: ?>
                    <button type="button" class="bk-seo-btn-sm" disabled style="opacity:.45">تنظیمات</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>

<div class="bk-wm-card bk-cdn-panel"
             x-show="isCdnModuleOn()"
             x-cloak>
            <div class="bk-wm-card__head">
                <h3><i class="dot is-indigo"></i> تنظیمات CDN و فضای ابری</h3>
                <span class="bk-wm-muted">بازنویسی URL رسانه · CDN ایرانی و بین‌المللی</span>
            </div>
            <div class="bk-cdn-row">
                <label class="bk-wm-field">
                    <span>ارائه‌دهنده</span>
                    <select x-model="cdnSettings.provider">
                        <option value="custom">سفارشی (Base URL)</option>
                        <option value="arvan">آروان کلاد (ArvanCloud)</option>
                        <option value="abr">ابر آروان Object Storage</option>
                        <option value="liara">لیارا (Liara)</option>
                        <option value="pars">پارس‌پک CDN</option>
                        <option value="cloudflare_r2">Cloudflare R2</option>
                        <option value="bunny">Bunny CDN</option>
                        <option value="s3">Amazon S3 / سازگار</option>
                    </select>
                </label>
                <label class="bk-wm-field">
                    <span>آدرس عمومی CDN (Base URL)</span>
                    <input type="url" dir="ltr" x-model="cdnSettings.base_url" placeholder="https://cdn.example.com">
                </label>
                <label class="bk-wm-field">
                    <span>Path Prefix</span>
                    <input type="text" dir="ltr" x-model="cdnSettings.path_prefix" placeholder="wp-content/uploads">
                </label>
                <label class="bk-wm-field">
                    <span>Access Key</span>
                    <input type="text" dir="ltr" x-model="cdnSettings.access_key" autocomplete="off">
                </label>
                <label class="bk-wm-field">
                    <span>Secret Key</span>
                    <input type="text" dir="ltr" data-bk-secret="1" x-model="cdnSettings.secret_key" autocomplete="new-password">
                </label>
                <label class="bk-wm-field" x-show="['s3','cloudflare_r2','abr','liara'].includes(cdnSettings.provider)" x-cloak>
                    <span>Bucket / Endpoint</span>
                    <input type="text" dir="ltr" x-model="cdnSettings.bucket" placeholder="my-bucket">
                </label>
                <label class="bk-wm-check" style="align-self:end;padding-bottom:8px">
                    <input type="checkbox" x-model="cdnSettings.rewrite_urls">
                    <span>بازنویسی URL روی فرانت</span>
                </label>
                <div style="align-self:end">
                    <button type="button" class="bk-wm-btn-primary" :disabled="cdnModal.saving" @click="saveCdnSettings()">
                        <span class="bk-btn-spin" x-show="cdnModal.saving" x-cloak></span>
                        ذخیره CDN
                    </button>
                </div>
            </div>
            <p class="bk-wm-muted" style="margin-top:10px;line-height:1.6">
                پس از ذخیره، آدرس فایل‌های رسانه در فرانت از طریق Base URL بازنویسی می‌شود. برای آروان/لیارا معمولاً فقط Base URL عمومی کافی است.
            </p>
        </div>
    </div>

    <!-- Media module drawer (optional) -->
    <div x-show="mediaDrawer.show" x-cloak class="bankai-modal-shell" x-transition>
        <div class="bankai-modal-panel" @click.outside="mediaDrawer.show=false">
            <div class="bankai-modal-head">
                <h3 class="bankai-modal-title" x-text="(mediaDrawer.title || '') + ' — تنظیمات'"></h3>
                <button type="button" class="bankai-modal-close" @click="mediaDrawer.show=false">&times;</button>
            </div>
            <div class="bankai-modal-body">
                <p style="margin:0;font-size:13px;color:#64748b">تنظیمات جزئی این ماژول از کارت‌های بالا یا بخش CDN در دسترس است.</p>
            </div>
            <div class="bankai-modal-foot">
                <button type="button" class="bankai-btn-ghost" @click="mediaDrawer.show=false">بستن</button>
            </div>
        </div>
    </div><!-- /tab-media-watermark -->

<!-- /tab-media-watermark.php close -->
