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

$custom_fonts = is_array($wm['custom_fonts'] ?? null) ? $wm['custom_fonts'] : [];
$wm_fonts_json = wp_json_encode($custom_fonts, JSON_UNESCAPED_UNICODE);


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

<div id="tab-media-watermark" class="bankai-tab-pane bk-orange-shell" :class="{ 'is-active-tab': activeTab === 'media-watermark' }" x-show="activeTab === 'media-watermark'" x-cloak>

  <header class="bk-top-head">
    <div class="bk-top-head__brand">
      <span class="bk-top-head__logo"><iconify-icon icon="solar:gallery-bold-duotone" width="24" height="24"></iconify-icon></span>
      <div>
        <strong>رسانه و واترمارک</strong>
        <small>فشرده‌سازی · WebP · واترمارک · EXIF</small>
      </div>
    </div>
    <div class="bk-top-head__actions">
      <button type="button" class="bk-wpr-btn bk-wpr-btn--primary" @click="regenerateThumbnails()" :disabled="busy">
        <iconify-icon icon="solar:restart-bold-duotone" width="16"></iconify-icon>
        بازتولید بندانگشتی
      </button>
    </div>
  </header>

  <nav class="bk-top-tabs" role="tablist">
    <button type="button" class="bk-top-tab" :class="{ 'is-active': mediaSubTab === 'watermark' }" @click="mediaSubTab = 'watermark'">
      <iconify-icon icon="solar:pen-new-square-bold-duotone" width="18"></iconify-icon>
      <span>واترمارک</span>
    </button>
    <button type="button" class="bk-top-tab" :class="{ 'is-active': mediaSubTab === 'compress' }" @click="mediaSubTab = 'compress'; loadMediaLibrary && loadMediaLibrary(true)">
      <iconify-icon icon="solar:minimize-square-2-bold-duotone" width="18"></iconify-icon>
      <span>فشرده‌سازی و واترمارک</span>
    </button>
  </nav>

  <div class="bk-top-body">
    <div class="bk-wm-env" style="margin-bottom:14px">
        <span class="bk-wm-env-label">وضعیت ماژول‌های پردازش:</span>
        <span class="bk-wm-env-chip is-ok"><i></i> Lazy Load</span>
        <span class="bk-wm-env-chip <?php echo $webp_ok ? 'is-ok' : 'is-bad'; ?>"><i></i> WebP <?php echo $webp_ok ? '✓' : '✗'; ?></span>
        <span class="bk-wm-env-chip <?php echo $imagick_ok ? 'is-ok' : 'is-muted'; ?>"><i></i> Imagick <?php echo $imagick_ok ? '✓' : '—'; ?></span>
        <span class="bk-wm-env-chip <?php echo $gd_ok ? 'is-ok' : 'is-bad'; ?>"><i></i> GD Library <?php echo $gd_ok ? '✓' : '✗'; ?></span>
    </div>
    <?php if (! $gd_ok): ?>
    <div class="bk-dash-card" style="margin-bottom:14px;border-color:#fecaca;background:#fef2f2">
      <strong style="color:#b91c1c">GD PHP فعال نیست</strong>
      <p style="margin:6px 0 0;font-size:12px;color:#7f1d1d">فشرده‌سازی تصاویر به افزونه GD نیاز دارد. از هاست بخواهید extension <code>gd</code> را فعال کند (با پشتیبانی imagewebp).</p>
    </div>
    <?php endif; ?>

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
                <input type="checkbox" :checked="!!watermarkStudio.enabled" @change.stop="watermarkStudio.enabled = $event.target.checked">
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
                                      :style="wmTextBadgeStyle()"
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
                        <label class="bk-wm-check" @click.stop>
                            <input type="checkbox" :checked="!!watermarkStudio.apply_upload" @change.stop="watermarkStudio.apply_upload = $event.target.checked">
                            <span>اعمال خودکار روی آپلودهای جدید</span>
                        </label>
                        <label class="bk-wm-check" @click.stop>
                            <input type="checkbox" :checked="!!watermarkStudio.apply_content" @change.stop="watermarkStudio.apply_content = $event.target.checked">
                            <span>اعمال در تبدیل گروهی</span>
                        </label>
                        <label class="bk-wm-check" @click.stop>
                            <input type="checkbox" :checked="!!watermarkStudio.lazy_load" @change.stop="watermarkStudio.lazy_load = $event.target.checked">
                            <span>سازگاری با Lazy Load</span>
                        </label>
                    </div>
                    <div class="bk-wm-actions-bar" style="margin-top:14px">
                        <div class="bk-wm-actions-bar__btns">
                            <button type="button" class="bk-wm-btn-primary" :disabled="busy" @click="saveWatermarkStudio()">
                                <span class="bk-btn-spin" x-show="busy" x-cloak></span>
                                ذخیره تنظیمات
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- COL 2: Typography + Font -->
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
                                <span>حالت اندازه‌گیری فونت</span>
                                <select x-model="watermarkStudio.text_size_type">
                                    <option value="scale">مقیاس‌پذیر بر اساس تصویر (پیش‌فرض)</option>
                                    <option value="fixed">اندازه ثابت پیکسل</option>
                                </select>
                            </label>
                            <label class="bk-wm-field" x-show="watermarkStudio.text_size_type === 'scale'">
                                <span>درصد از عرض تصویر <em x-text="(watermarkStudio.text_scale_percent||3.5)+'%'"></em></span>
                                <input type="range" min="1.0" max="15.0" step="0.5" x-model.number="watermarkStudio.text_scale_percent">
                            </label>
                            <label class="bk-wm-field" x-show="watermarkStudio.text_size_type === 'fixed'">
                                <span>سایز ثابت <em x-text="(watermarkStudio.text_size||18)+'px'"></em></span>
                                <input type="range" min="10" max="100" x-model.number="watermarkStudio.text_size">
                            </label>
                            <label class="bk-wm-field">
                                <span>فاصله از لبه (درصد عرض) <em x-text="(watermarkStudio.margin_percent||2.0)+'%'"></em></span>
                                <input type="range" min="0.0" max="10.0" step="0.5" x-model.number="watermarkStudio.margin_percent">
                            </label>
                        </div>
                        <div class="bk-wm-grid-2" x-show="watermarkStudio.text_size_type === 'scale'">
                            <label class="bk-wm-field">
                                <span>حداقل سایز فونت (px)</span>
                                <input type="number" min="8" max="50" x-model.number="watermarkStudio.min_font_size">
                            </label>
                            <label class="bk-wm-field">
                                <span>حداکثر سایز فونت (px)</span>
                                <input type="number" min="20" max="180" x-model.number="watermarkStudio.max_font_size">
                            </label>
                        </div>
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
                        </div>
                        <div class="bk-wm-color-block">
                            <span class="bk-wm-color-block__lbl">رنگ متن</span>
                            <div class="bk-wm-swatches">
                                <template x-for="sw in wmColorSwatches()" :key="'tc-'+sw">
                                    <button type="button" class="bk-wm-swatch"
                                            :class="{ 'is-active': watermarkStudio.text_color === sw, 'is-clear': sw === 'transparent' }"
                                            :style="sw === 'transparent' ? '' : 'background:'+sw"
                                            @click="watermarkStudio.text_color = sw"></button>
                                </template>
                                <input type="color" x-model="watermarkStudio.text_color" style="width:32px;height:28px;border:none;padding:0;cursor:pointer" title="رنگ سفارشی">
                            </div>
                        </div>
                        <div class="bk-wm-color-block" x-show="watermarkStudio.box_style !== 'none'">
                            <span class="bk-wm-color-block__lbl">رنگ پس‌زمینه کادر</span>
                            <div class="bk-wm-swatches">
                                <button type="button" class="bk-wm-swatch is-clear" :class="{ 'is-active': watermarkStudio.box_style === 'none' }" @click="watermarkStudio.box_style='none'" title="بدون پس‌زمینه"></button>
                                <template x-for="sw in wmBgSwatches()" :key="'bg2-'+sw">
                                    <button type="button" class="bk-wm-swatch" :class="{ 'is-active': watermarkStudio.box_color === sw }"
                                            :style="'background:'+sw"
                                            @click="watermarkStudio.box_color=sw; watermarkStudio.box_style=watermarkStudio.box_style==='none'?'solid':watermarkStudio.box_style"></button>
                                </template>
                                <input type="color" x-model="watermarkStudio.box_color" style="width:32px;height:28px;border:none;padding:0;cursor:pointer" title="رنگ سفارشی">
                            </div>
                        </div>
                        <label class="bk-wm-check">
                            <input type="checkbox" :checked="!!watermarkStudio.box_enabled" @change.stop="watermarkStudio.box_enabled = $event.target.checked">
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
                </section>


                

            </div>
        </div>
    </div>


    
    <!-- bulk watermark panel removed — use تب «فشرده‌سازی و واترمارک» -->

    <div x-show="mediaSubTab === 'compress'" x-cloak class="bk-compress-shell">
        <div class="bk-compress-toolbar">
            <div class="bk-compress-toolbar__info">
                <strong>فشرده‌سازی و واترمارک</strong>
                <p style="margin:4px 0 0;font-size:12px;color:#64748b">تصاویر را انتخاب کنید، سپس فشرده‌سازی یا واترمارک را اعمال کنید</p>
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
                <button type="button" class="bk-wm-btn-ghost" :disabled="mediaLib.busy || mediaLib.loading || !(mediaLib.items||[]).length" @click="selectAllMedia && selectAllMedia()">
                    انتخاب همه
                </button>
                <button type="button" class="bk-wm-btn-primary" :disabled="mediaLib.busy || selectedMediaCount()===0" @click="startBulkCompress && startBulkCompress(true)">
                    <span class="bk-btn-spin" x-show="mediaLib.busy && mediaLib.action==='compress'" x-cloak></span>
                    فشرده‌سازی (<span x-text="selectedMediaCount()">0</span>)
                </button>
                <button type="button" class="bk-wm-btn-ghost" :disabled="mediaLib.busy || selectedMediaCount()===0" @click="startSelectedWatermark && startSelectedWatermark()">
                    <span class="bk-btn-spin" x-show="mediaLib.busy && mediaLib.action==='watermark'" x-cloak></span>
                    واترمارک (<span x-text="selectedMediaCount()">0</span>)
                </button>
            </div>
            <div class="bk-compress-progress" x-show="mediaLib.busy || mediaLib.progress > 0" x-cloak>
                <div class="bk-compress-bar"><i :style="'width:'+(mediaLib.progress||0)+'%'"></i></div>
                <span x-text="mediaLib.progressText || ''"></span>
            </div>

            <!-- Bulk operation visual report -->
            <div class="bk-media-result bk-bulk-result"
                 :class="{
                    'is-show': mediaLib.resultState,
                    'is-loading': mediaLib.resultState === 'loading',
                    'is-ok': mediaLib.resultState === 'ok',
                    'is-err': mediaLib.resultState === 'err'
                 }"
                 x-show="mediaLib.resultState" x-cloak style="margin-top:12px">
                <div class="bk-media-result__title">
                    <span class="bk-media-result__icon" x-text="mediaLib.resultState === 'ok' ? '✓' : (mediaLib.resultState === 'err' ? '!' : '↻')"></span>
                    <span x-text="mediaLib.resultMessage || mediaLib.progressText || ''"></span>
                </div>
                <div class="bk-media-result__stats" x-show="mediaLib.resultStats && mediaLib.resultStats.length">
                    <template x-for="(st, si) in (mediaLib.resultStats || [])" :key="'bs-'+si">
                        <span>
                            <span x-text="st.label"></span>
                            <em x-text="st.value"></em>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <div class="bk-compress-loading" x-show="mediaLib.loading && !(mediaLib.items||[]).length" x-cloak>
            <div class="bk-compress-spinner" aria-hidden="true"></div>
            <strong>در حال بارگذاری تصاویر رسانه…</strong>
            <span style="font-weight:500;color:#94a3b8;font-size:12px">لطفاً چند لحظه صبر کنید</span>
        </div>

        <div class="bk-compress-grid" x-show="(mediaLib.items||[]).length">
            <template x-for="img in (mediaLib.items || [])" :key="img.id">
                <div class="bk-c-card" :class="{ 'is-selected': img.selected }">
                    <button type="button" class="bk-c-card__check"
                            @click.stop="img.selected = !img.selected"
                            :aria-pressed="!!img.selected"
                            :title="img.selected ? 'لغو انتخاب' : 'انتخاب برای عملیات گروهی'">
                        <span x-text="img.selected ? '✓' : ''"></span>
                    </button>
                    <span class="bk-c-card__check-hint" x-show="!img.selected">انتخاب</span>
                    <img class="bk-c-card__img" :src="img.thumb || img.url" :alt="img.alt || img.title" loading="lazy" decoding="async" @error="$el.style.opacity='0.35'" @click.stop="openMediaDetail && openMediaDetail(img)">
                    <span class="bk-c-chip is-fmt" x-text="(img.mime || img.format || 'IMG').toString().replace('image/','').toUpperCase()"></span>
                    <span class="bk-c-chip is-size" x-text="img.size_label || img.size || '—'"></span>
                    <div class="bk-c-card__name" x-text="img.title || ('#'+img.id)"></div>
                </div>
            </template>
        </div>
        <div class="bk-wm-card" style="text-align:center;padding:28px;margin-top:12px" x-show="!(mediaLib.items||[]).length && !mediaLib.loading">
            تصویری یافت نشد.
        </div>
        <div style="margin-top:14px;text-align:center" x-show="mediaLib.hasMore || mediaLib.loading">
            <button type="button" class="bk-wm-btn-ghost" @click="loadMediaLibrary(false)" :disabled="mediaLib.loading">
                <span class="bk-btn-spin" x-show="mediaLib.loading && (mediaLib.items||[]).length" x-cloak></span>
                <span x-text="mediaLib.loading && (mediaLib.items||[]).length ? 'در حال بارگذاری…' : 'بارگذاری بیشتر'"></span>
            </button>
        </div>

        <!-- Image detail modal — only in DOM when open; teleported to body for viewport center -->
        <template x-teleport="body">
        <div class="bk-media-detail-bg" x-show="mediaDetail.open" x-cloak
             x-transition.opacity.duration.150ms
             @click.self="mediaDetail.open=false"
             @keydown.escape.window="mediaDetail.open=false">
            <div class="bk-media-detail" @click.stop x-show="mediaDetail.open">
                <div class="bk-media-detail__head">
                    <strong x-text="mediaDetail.item && (mediaDetail.item.title || ('#'+mediaDetail.item.id))"></strong>
                    <button type="button" class="bk-store-modal__x" @click="mediaDetail.open=false">✕</button>
                </div>
                <div class="bk-media-detail__body">
                    <div class="bk-media-detail__preview"
                         :class="{ 'is-busy': mediaDetail.busy, 'is-flash': mediaDetail.flash }">
                        <img :src="(mediaDetail.item && (mediaDetail.item.url || mediaDetail.item.thumb)) || ''"
                             :alt="mediaDetail.item && mediaDetail.item.alt">
                    </div>
                    <div class="bk-media-detail__meta" x-show="mediaDetail.item">
                        <template x-if="mediaDetail.item">
                        <div>
                        <label class="bk-wm-field"><span>عنوان</span>
                            <input type="text" x-model="mediaDetail.item.title" :disabled="mediaDetail.busy">
                        </label>
                        <label class="bk-wm-field"><span>متن جایگزین (Alt)</span>
                            <input type="text" x-model="mediaDetail.item.alt" :disabled="mediaDetail.busy">
                        </label>
                        <label class="bk-wm-field"><span>توضیحات</span>
                            <textarea rows="3" x-model="mediaDetail.item.caption" :disabled="mediaDetail.busy"></textarea>
                        </label>
                        </div>
                        </template>
                        <div class="bk-media-detail__info" x-show="mediaDetail.item">
                            <span x-text="mediaDetail.item && (mediaDetail.item.size_label || mediaDetail.item.size || '')"></span>
                            <span x-text="mediaDetail.item && (mediaDetail.item.width && mediaDetail.item.height ? (mediaDetail.item.width+'×'+mediaDetail.item.height) : '')"></span>
                            <span x-text="mediaDetail.item && (mediaDetail.item.mime || '')"></span>
                        </div>
                        <div class="bk-media-detail__actions">
                            <button type="button" class="bk-wm-btn-ghost" :disabled="mediaDetail.saving || mediaDetail.busy" @click="saveMediaDetail && saveMediaDetail()">
                                <span class="bk-btn-spin" x-show="mediaDetail.saving" x-cloak></span>
                                <span x-text="mediaDetail.saving ? 'در حال ذخیره…' : 'ذخیره جزئیات'"></span>
                            </button>
                            <button type="button" class="bk-wm-btn-primary" :disabled="mediaDetail.busy" @click="compressOneMedia && compressOneMedia(mediaDetail.item)">
                                <span class="bk-btn-spin" x-show="mediaDetail.busy && mediaDetail.action==='compress'" x-cloak></span>
                                <span x-text="mediaDetail.busy && mediaDetail.action==='compress' ? 'در حال فشرده‌سازی…' : 'فشرده‌سازی'"></span>
                            </button>
                            <button type="button" class="bk-wm-btn-ghost" :disabled="mediaDetail.busy" @click="watermarkOneMedia && watermarkOneMedia(mediaDetail.item)">
                                <span class="bk-btn-spin" x-show="mediaDetail.busy && mediaDetail.action==='watermark'" x-cloak></span>
                                <span x-text="mediaDetail.busy && mediaDetail.action==='watermark' ? 'در حال واترمارک…' : 'واترمارک'"></span>
                            </button>
                        </div>

                        <!-- Visual result banner -->
                        <div class="bk-media-result"
                             :class="{
                                'is-show': mediaDetail.resultState,
                                'is-loading': mediaDetail.resultState === 'loading',
                                'is-ok': mediaDetail.resultState === 'ok',
                                'is-err': mediaDetail.resultState === 'err'
                             }"
                             x-show="mediaDetail.resultState" x-cloak>
                            <div class="bk-media-result__title">
                                <span class="bk-media-result__icon" x-text="mediaDetail.resultState === 'ok' ? '✓' : (mediaDetail.resultState === 'err' ? '!' : '↻')"></span>
                                <span x-text="mediaDetail.message || ''"></span>
                            </div>
                            <div class="bk-media-result__stats" x-show="mediaDetail.resultStats && mediaDetail.resultStats.length">
                                <template x-for="(st, si) in (mediaDetail.resultStats || [])" :key="'rs-'+si">
                                    <span>
                                        <span x-text="st.label"></span>
                                        <em x-text="st.value"></em>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </template>
    </div><!-- /.bk-compress-shell -->


</div><!-- /.bk-top-body -->
</div><!-- /tab-media-watermark -->

<!-- /tab-media-watermark root -->
