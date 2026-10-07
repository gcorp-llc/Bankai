<?php
/**
 * Header / Footer builder controls
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

if (! class_exists('WP_Customize_Control')) {
    return;
}

class Bankai_Customize_Header_Builder_Control extends WP_Customize_Control {

    public $type = 'bankai_header_builder';

    public function enqueue(): void {
        wp_enqueue_style(
            'bankai-header-builder',
            BANKAI_THEME_URI . '/assets/css/header-builder.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_media();
        wp_enqueue_script(
            'bankai-header-builder',
            BANKAI_THEME_URI . '/assets/js/header-builder.js',
            ['customize-controls', 'jquery'],
            BANKAI_THEME_VERSION,
            true
        );

        $cfg = function_exists('bankai_header_get_config') ? bankai_header_get_config() : [];
        $footer = function_exists('bankai_footer_get_config') ? bankai_footer_get_config() : null;

        wp_localize_script('bankai-header-builder', 'bankaiHeaderBuilder', [
            'config'       => $cfg,
            'footerConfig' => $footer,
            'elements'     => [
                'logo'    => __('لوگو', 'bankai-theme'),
                'menu'    => __('فهرست', 'bankai-theme'),
                'search'  => __('جستجو', 'bankai-theme'),
                'social'  => __('سوشال', 'bankai-theme'),
                'button'  => __('دکمه', 'bankai-theme'),
                'account' => __('حساب', 'bankai-theme'),
                'cart'    => __('سبد', 'bankai-theme'),
                'phone'   => __('تلفن', 'bankai-theme'),
                'text'    => __('متن', 'bankai-theme'),
                'html'    => 'HTML',
                'divider' => __('جداکننده', 'bankai-theme'),
            ],
            'icons' => function_exists('bankai_theme_icon_catalog') ? array_keys(bankai_theme_icon_catalog()) : [],
            'lucideNodesUrl' => get_template_directory_uri() . '/assets/icons/lucide/icon-nodes.json',
            'lucideTagsUrl'  => get_template_directory_uri() . '/assets/icons/lucide/tags.json',
            'lucideCoreUrl'  => get_template_directory_uri() . '/assets/icons/lucide/core-nodes.json',
            'lucideCategoriesUrl' => get_template_directory_uri() . '/assets/icons/lucide/categories.json',
            'footerElements' => [
                'widget'    => __('ویجت', 'bankai-theme'),
                'social'    => __('سوشال', 'bankai-theme'),
                'copyright' => __('کپی‌رایت', 'bankai-theme'),
                'menu'      => __('فهرست', 'bankai-theme'),
                'html'      => 'HTML',
                'divider'   => __('جداکننده', 'bankai-theme'),
            ],
        ]);
    }

    public function render_content(): void {
        $cfg = function_exists('bankai_header_get_config') ? bankai_header_get_config() : [];
        $beh = $cfg['behavior'] ?? ($cfg['transparent'] ?? false ? 'transparent' : 'sticky');
        ?>
        <div class="bhb-sidebar" dir="rtl">
            <span class="customize-control-title ast-builder-title"><?php esc_html_e('رفتار سربرگ', 'bankai-theme'); ?></span>
            <p class="description bhb-desc"><?php esc_html_e('نحوه نمایش نوار هنگام اسکرول صفحه', 'bankai-theme'); ?></p>
            <div class="bhb-beh bhb-beh--visual">
                <?php
                $behaviors = [
                    'sticky' => [
                        'label' => __('چسبان', 'bankai-theme'),
                        'desc'  => __('همیشه بالای صفحه ثابت', 'bankai-theme'),
                        'art'   => 'sticky',
                    ],
                    'transparent' => [
                        'label' => __('شفاف', 'bankai-theme'),
                        'desc'  => __('روی تصویر/محتوا شفاف', 'bankai-theme'),
                        'art'   => 'transparent',
                    ],
                    'hide-on-scroll' => [
                        'label' => __('محو با اسکرول', 'bankai-theme'),
                        'desc'  => __('با پایین رفتن مخفی می‌شود', 'bankai-theme'),
                        'art'   => 'hide',
                    ],
                    'sticky-scroll' => [
                        'label' => __('چسبان با اسکرول', 'bankai-theme'),
                        'desc'  => __('بعد از کمی اسکرول می‌چسبد', 'bankai-theme'),
                        'art'   => 'stickyscroll',
                    ],
                ];
                foreach ($behaviors as $id => $meta) :
                    ?>
                    <button type="button" class="bhb-beh__card<?php echo $beh === $id ? ' is-active' : ''; ?>" data-bhb-behavior="<?php echo esc_attr($id); ?>">
                        <span class="bhb-beh__art bhb-beh__art--<?php echo esc_attr($meta['art']); ?>" aria-hidden="true">
                            <span class="bhb-beh__browser">
                                <span class="bhb-beh__dots"><i></i><i></i><i></i></span>
                                <span class="bhb-beh__bar"></span>
                                <span class="bhb-beh__content"><span></span><span></span><span></span></span>
                            </span>
                        </span>
                        <span class="bhb-beh__label"><?php echo esc_html($meta['label']); ?></span>
                        <span class="bhb-beh__desc"><?php echo esc_html($meta['desc']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <span class="customize-control-title"><?php esc_html_e('پیش‌ساخته نوبار', 'bankai-theme'); ?></span>
            <div class="bhb-presets">
                <button type="button" class="bhb-preset" data-bhb-preset="1row">
                    <span class="bhb-preset__art"><span class="bhb-preset__line bhb-preset__line--main"></span></span>
                    <?php esc_html_e('یک‌سطری', 'bankai-theme'); ?>
                </button>
                <button type="button" class="bhb-preset" data-bhb-preset="2row">
                    <span class="bhb-preset__art">
                        <span class="bhb-preset__line"></span>
                        <span class="bhb-preset__line bhb-preset__line--main"></span>
                    </span>
                    <?php esc_html_e('دوسطری', 'bankai-theme'); ?>
                </button>
                <button type="button" class="bhb-preset" data-bhb-preset="3row">
                    <span class="bhb-preset__art">
                        <span class="bhb-preset__line"></span>
                        <span class="bhb-preset__line bhb-preset__line--main"></span>
                        <span class="bhb-preset__line"></span>
                    </span>
                    <?php esc_html_e('سه‌سطری', 'bankai-theme'); ?>
                </button>
            </div>

            
                        <?php
            $mm = $cfg['mobile_menu'] ?? [];
            $mm_style = $mm['style'] ?? 'drawer-right';
            if ($mm_style === 'bottom-sheet') {
                $mm_style = 'sheet';
            }
            $mm_icon = $mm['icon'] ?? 'menu';
            if ($mm_icon === 'hamburger') {
                $mm_icon = 'menu';
            }
            ?>
            <span class="customize-control-title ast-builder-title" style="margin-top:14px"><?php esc_html_e('منوی موبایل', 'bankai-theme'); ?></span>
            <p class="description bhb-desc"><?php esc_html_e('آیکون نوار و نوع باز شدن منو', 'bankai-theme'); ?></p>

            <span class="customize-control-title" style="font-size:12px;margin-top:8px"><?php esc_html_e('آیکون منوی موبایل', 'bankai-theme'); ?></span>
            <div class="bhb-icon-pick-row" style="margin-bottom:12px">
                <button type="button" class="bhb-icon-preview" data-open-icon-modal data-mm-icon-pick data-icon="<?php echo esc_attr($mm_icon); ?>">
                    <span class="bhb-icon-preview__glyph"><?php echo function_exists('bankai_theme_icon') ? bankai_theme_icon($mm_icon, ['size' => 24]) : '☰'; ?></span>
                    <span><?php esc_html_e('انتخاب آیکون', 'bankai-theme'); ?></span>
                </button>
            </div>

            <p class="description" style="font-size:11px;margin:0 0 8px;color:#64748B"><?php esc_html_e('نوع باز شدن منوی موبایل', 'bankai-theme'); ?></p>
            <div class="bhb-mm-styles">
                <button type="button" class="bhb-mm-style<?php echo $mm_style === 'dropdown' ? ' is-active' : ''; ?>" data-mm-style="dropdown" title="دراپ‌داون از بالا">
                    <span class="bhb-mm-style__art bhb-mm-style__art--dropdown" aria-hidden="true">
                        <span class="bhb-mm-phone"><span class="bhb-mm-phone__bar"></span><span class="bhb-mm-phone__drop"></span></span>
                    </span>
                    <strong>از بالا</strong>
                    <small>دراپ‌باکس زیر هدر</small>
                </button>
                <button type="button" class="bhb-mm-style<?php echo $mm_style === 'drawer-right' ? ' is-active' : ''; ?>" data-mm-style="drawer-right" title="ساید راست">
                    <span class="bhb-mm-style__art bhb-mm-style__art--drawer-right" aria-hidden="true">
                        <span class="bhb-mm-phone"><span class="bhb-mm-phone__bar"></span><span class="bhb-mm-phone__side bhb-mm-phone__side--right"></span></span>
                    </span>
                    <strong>ساید راست</strong>
                    <small>کشو از راست</small>
                </button>
                <button type="button" class="bhb-mm-style<?php echo $mm_style === 'drawer-left' ? ' is-active' : ''; ?>" data-mm-style="drawer-left" title="ساید چپ">
                    <span class="bhb-mm-style__art bhb-mm-style__art--drawer-left" aria-hidden="true">
                        <span class="bhb-mm-phone"><span class="bhb-mm-phone__bar"></span><span class="bhb-mm-phone__side bhb-mm-phone__side--left"></span></span>
                    </span>
                    <strong>ساید چپ</strong>
                    <small>کشو از چپ</small>
                </button>
                <button type="button" class="bhb-mm-style<?php echo $mm_style === 'sheet' ? ' is-active' : ''; ?>" data-mm-style="sheet" title="از پایین">
                    <span class="bhb-mm-style__art bhb-mm-style__art--sheet" aria-hidden="true">
                        <span class="bhb-mm-phone"><span class="bhb-mm-phone__bar"></span><span class="bhb-mm-phone__sheet"></span></span>
                    </span>
                    <strong>از پایین</strong>
                    <small>شیت چسبیده به پایین</small>
                </button>
            </div>

            <span class="customize-control-title ast-builder-title" style="margin-top:14px"><?php esc_html_e('ظاهر نوار دسکتاپ', 'bankai-theme'); ?></span>
            <?php
            $nd = $cfg['nav_desktop'] ?? [];
            $nd_mode = ($nd['style_mode'] ?? 'inherit') === 'custom' ? 'custom' : 'inherit';
            ?>
            <div class="btg-mode-toggle bhb-style-mode" data-nav-mode="desktop" style="margin-bottom:10px">
                <button type="button" class="btg-mode-btn<?php echo $nd_mode === 'inherit' ? ' is-active' : ''; ?>" data-nav="desktop" data-nav-key="style_mode" data-value="inherit">
                    <span class="btg-mode-btn__art btg-mode-btn__art--inherit" aria-hidden="true"></span>
                    <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                    <small><?php esc_html_e('رنگ و استایل تم', 'bankai-theme'); ?></small>
                </button>
                <button type="button" class="btg-mode-btn<?php echo $nd_mode === 'custom' ? ' is-active' : ''; ?>" data-nav="desktop" data-nav-key="style_mode" data-value="custom">
                    <span class="btg-mode-btn__art btg-mode-btn__art--custom" aria-hidden="true"></span>
                    <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                    <small><?php esc_html_e('رنگ و فونت دستی', 'bankai-theme'); ?></small>
                </button>
            </div>
            <div class="bhb-nav-skin bhb-nav-skin--custom-only<?php echo $nd_mode === 'custom' ? ' is-open' : ''; ?>" data-nav-skin="desktop" data-depends-mode="desktop">
                <label class="bhb-color-circle" title="پس‌زمینه"><span class="bhb-color-circle__lab">پس‌زمینه</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr(($nd['bg'] ?? '') ?: '#ffffff'); ?>"></span><input type="color" data-nav="desktop" data-nav-key="bg" value="<?php echo esc_attr(($nd['bg'] ?? '') ?: '#ffffff'); ?>"></label>
                <label class="bhb-color-circle" title="متن"><span class="bhb-color-circle__lab">متن</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr(($nd['color'] ?? '') ?: '#1a1a1a'); ?>"></span><input type="color" data-nav="desktop" data-nav-key="color" value="<?php echo esc_attr(($nd['color'] ?? '') ?: '#1a1a1a'); ?>"></label>
                <label class="bhb-check-pill<?php echo ! empty($nd['shadow']) ? ' is-on' : ''; ?>"><input type="checkbox" data-nav="desktop" data-nav-key="shadow" <?php checked(! empty($nd['shadow'])); ?>><span>سایه</span></label>
                <label class="bhb-side-field"><span>سایز فونت</span><input type="number" min="10" max="28" data-nav="desktop" data-nav-key="font_size" value="<?php echo esc_attr((string) ($nd['font_size'] ?? 15)); ?>"></label>
            </div>

            <span class="customize-control-title ast-builder-title" style="margin-top:14px"><?php esc_html_e('ظاهر نوار موبایل', 'bankai-theme'); ?></span>
            <?php
            $nm = $cfg['nav_mobile'] ?? [];
            $nm_mode = ($nm['style_mode'] ?? 'inherit') === 'custom' ? 'custom' : 'inherit';
            ?>
            <div class="btg-mode-toggle bhb-style-mode" data-nav-mode="mobile" style="margin-bottom:10px">
                <button type="button" class="btg-mode-btn<?php echo $nm_mode === 'inherit' ? ' is-active' : ''; ?>" data-nav="mobile" data-nav-key="style_mode" data-value="inherit">
                    <span class="btg-mode-btn__art btg-mode-btn__art--inherit" aria-hidden="true"></span>
                    <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                    <small><?php esc_html_e('هماهنگ با تم', 'bankai-theme'); ?></small>
                </button>
                <button type="button" class="btg-mode-btn<?php echo $nm_mode === 'custom' ? ' is-active' : ''; ?>" data-nav="mobile" data-nav-key="style_mode" data-value="custom">
                    <span class="btg-mode-btn__art btg-mode-btn__art--custom" aria-hidden="true"></span>
                    <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                    <small><?php esc_html_e('رنگ و فونت دستی', 'bankai-theme'); ?></small>
                </button>
            </div>
            <div class="bhb-nav-skin bhb-nav-skin--custom-only<?php echo $nm_mode === 'custom' ? ' is-open' : ''; ?>" data-nav-skin="mobile" data-depends-mode="mobile">
                <label class="bhb-color-circle" title="پس‌زمینه"><span class="bhb-color-circle__lab">پس‌زمینه</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr(($nm['bg'] ?? '') ?: '#ffffff'); ?>"></span><input type="color" data-nav="mobile" data-nav-key="bg" value="<?php echo esc_attr(($nm['bg'] ?? '') ?: '#ffffff'); ?>"></label>
                <label class="bhb-color-circle" title="متن"><span class="bhb-color-circle__lab">متن</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr(($nm['color'] ?? '') ?: '#1a1a1a'); ?>"></span><input type="color" data-nav="mobile" data-nav-key="color" value="<?php echo esc_attr(($nm['color'] ?? '') ?: '#1a1a1a'); ?>"></label>
                <label class="bhb-check-pill<?php echo ! empty($nm['shadow']) ? ' is-on' : ''; ?>"><input type="checkbox" data-nav="mobile" data-nav-key="shadow" <?php checked(! empty($nm['shadow'])); ?>><span>سایه</span></label>
                <label class="bhb-side-field"><span>سایز فونت</span><input type="number" min="10" max="28" data-nav="mobile" data-nav-key="font_size" value="<?php echo esc_attr((string) ($nm['font_size'] ?? 15)); ?>"></label>
            </div>

            <button type="button" class="bhb-open-btn" id="bhb-open-sheet"><?php esc_html_e('ویرایش ردیف‌ها و المان‌ها', 'bankai-theme'); ?></button>
            <input type="hidden" <?php $this->link(); ?> id="bhb-config-input" value="<?php echo esc_attr(is_string($this->value()) ? $this->value() : wp_json_encode($cfg)); ?>">
        </div>
        <?php
    }
}

class Bankai_Customize_Footer_Builder_Control extends WP_Customize_Control {

    public $type = 'bankai_footer_builder';

    public function enqueue(): void {
        wp_enqueue_style(
            'bankai-single-layout-control',
            BANKAI_THEME_URI . '/assets/css/customizer-single-layout.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_style(
            'bankai-header-builder',
            BANKAI_THEME_URI . '/assets/css/header-builder.css',
            ['bankai-single-layout-control'],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_script(
            'bankai-header-builder',
            BANKAI_THEME_URI . '/assets/js/header-builder.js',
            ['customize-controls', 'jquery'],
            BANKAI_THEME_VERSION,
            true
        );
    }

    public function render_content(): void {
        $cfg = function_exists('bankai_footer_get_config') ? bankai_footer_get_config() : [];
        $st = $cfg['scroll_top'] ?? [];
        $ct = $cfg['contact_fab'] ?? [];
        $bn = $cfg['bottom_nav'] ?? [];
        $bn_cols = (int) ($bn['columns'] ?? 3);
        if (! in_array($bn_cols, [3, 4, 5], true)) {
            $bn_cols = 3;
        }
        $bn_layout = $bn['item_layout'] ?? 'icon-text-below';
        $bn_items = array_values($bn['items'] ?? []);
        while (count($bn_items) < 5) {
            $bn_items[] = ['icon' => 'home', 'label' => '', 'url' => ''];
        }
        $row_count = count(array_filter($cfg['rows'] ?? [], function ($r) {
            return ! empty($r['enabled']);
        }));
        if ($row_count < 1) {
            $row_count = count($cfg['rows'] ?? []) ?: 1;
        }
        ?>
        <div class="bhb-sidebar bfb-sidebar" dir="rtl">
            <span class="customize-control-title"><?php esc_html_e('پیش‌ساخته پاورقی', 'bankai-theme'); ?></span>
            <p class="description bhb-desc"><?php esc_html_e('تعداد سطر پاورقی را انتخاب کنید', 'bankai-theme'); ?></p>
            <div class="bhb-presets" style="margin-bottom:14px">
                <button type="button" class="bhb-preset<?php echo $row_count === 1 ? ' is-active' : ''; ?>" data-bfb-preset="1row">
                    <span class="bhb-preset__art">
                        <span class="bhb-preset__line bhb-preset__line--main"></span>
                    </span>
                    <?php esc_html_e('یک‌سطری', 'bankai-theme'); ?>
                </button>
                <button type="button" class="bhb-preset<?php echo $row_count === 2 ? ' is-active' : ''; ?>" data-bfb-preset="2row">
                    <span class="bhb-preset__art">
                        <span class="bhb-preset__line bhb-preset__line--main"></span>
                        <span class="bhb-preset__line"></span>
                    </span>
                    <?php esc_html_e('دوسطری', 'bankai-theme'); ?>
                </button>
                <button type="button" class="bhb-preset<?php echo $row_count >= 3 ? ' is-active' : ''; ?>" data-bfb-preset="3row">
                    <span class="bhb-preset__art">
                        <span class="bhb-preset__line"></span>
                        <span class="bhb-preset__line bhb-preset__line--main"></span>
                        <span class="bhb-preset__line"></span>
                    </span>
                    <?php esc_html_e('سه‌سطری', 'bankai-theme'); ?>
                </button>
            </div>

            <hr class="bhb-section-sep" />
            <span class="customize-control-title"><?php esc_html_e('ویرایش ردیف‌های پاورقی', 'bankai-theme'); ?></span>
            <p class="description bhb-desc"><?php esc_html_e('تعداد ستون هر ردیف و المان‌های هر ستون', 'bankai-theme'); ?></p>
            <div id="bfb-inline-builder" class="bfb-inline-builder" dir="rtl"></div>
            <div id="bfb-inline-dock" class="bfb-inline-dock"></div>

            <p class="description" style="margin-top:10px;font-size:11px;color:#8A8F98">
                <?php esc_html_e('نوار اعتبار ثابت: GCORP LLC · Powered Dr Manhattan', 'bankai-theme'); ?>
            </p>

            <hr class="bhb-section-sep" />
            <span class="customize-control-title"><?php esc_html_e('عناصر پاورقی و شناور', 'bankai-theme'); ?></span>
            <p class="description" style="margin-bottom:8px"><?php esc_html_e('کارت را بزنید تا فعال/خاموش شود.', 'bankai-theme'); ?></p>

            <div class="bsl-grid bhb-ft-grid">
                <button type="button" class="bsl-card<?php echo ! empty($st['enabled']) ? ' is-on' : ''; ?>" data-ft-card="scroll_top" aria-pressed="<?php echo ! empty($st['enabled']) ? 'true' : 'false'; ?>">
                    <span class="bsl-card__preview bsl-card__preview--scroll" aria-hidden="true"></span>
                    <span class="bsl-card__label"><?php esc_html_e('بازگشت به بالا', 'bankai-theme'); ?></span>
                    <span class="bsl-card__state"><?php echo ! empty($st['enabled']) ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                </button>
                <button type="button" class="bsl-card<?php echo ! empty($ct['enabled']) ? ' is-on' : ''; ?>" data-ft-card="contact_fab" aria-pressed="<?php echo ! empty($ct['enabled']) ? 'true' : 'false'; ?>">
                    <span class="bsl-card__preview bsl-card__preview--contact" aria-hidden="true"></span>
                    <span class="bsl-card__label"><?php esc_html_e('تماس سریع', 'bankai-theme'); ?></span>
                    <span class="bsl-card__state"><?php echo ! empty($ct['enabled']) ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                </button>
                <button type="button" class="bsl-card<?php echo ! empty($bn['enabled']) ? ' is-on' : ''; ?>" data-ft-card="bottom_nav" aria-pressed="<?php echo ! empty($bn['enabled']) ? 'true' : 'false'; ?>">
                    <span class="bsl-card__preview bsl-card__preview--bottomnav" aria-hidden="true"></span>
                    <span class="bsl-card__label"><?php esc_html_e('نوار پایین موبایل', 'bankai-theme'); ?></span>
                    <span class="bsl-card__state"><?php echo ! empty($bn['enabled']) ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                </button>
            </div>

            <?php $st_mode = ($st['style_mode'] ?? 'inherit') === 'custom' ? 'custom' : 'inherit'; ?>
            <div class="bhb-ft-editor<?php echo ! empty($st['enabled']) ? ' is-open' : ''; ?>" data-ft-editor="scroll_top">
                <span class="customize-control-title" style="margin-top:14px"><?php esc_html_e('تنظیمات بازگشت به بالا', 'bankai-theme'); ?></span>
                <div class="btg-mode-toggle" style="margin-bottom:10px">
                    <button type="button" class="btg-mode-btn<?php echo $st_mode === 'inherit' ? ' is-active' : ''; ?>" data-ft="scroll_top" data-ft-key="style_mode" data-value="inherit">
                        <span class="btg-mode-btn__art btg-mode-btn__art--inherit"></span>
                        <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                    </button>
                    <button type="button" class="btg-mode-btn<?php echo $st_mode === 'custom' ? ' is-active' : ''; ?>" data-ft="scroll_top" data-ft-key="style_mode" data-value="custom">
                        <span class="btg-mode-btn__art btg-mode-btn__art--custom"></span>
                        <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                    </button>
                </div>
                <div class="bhb-icon-pick-row">
                    <button type="button" class="bhb-icon-preview" data-open-icon-modal data-ft="scroll_top" data-ft-key="icon" data-icon="<?php echo esc_attr($st['icon'] ?? 'arrow-up'); ?>">
                        <span class="bhb-icon-preview__glyph"><?php echo function_exists('bankai_theme_icon') ? bankai_theme_icon($st['icon'] ?? 'arrow-up', ['size' => 22]) : '↑'; ?></span>
                        <span><?php esc_html_e('آیکون', 'bankai-theme'); ?></span>
                    </button>
                </div>
                <div class="bhb-nav-skin bhb-nav-skin--custom-only<?php echo $st_mode === 'custom' ? ' is-open' : ''; ?>" data-ft-custom="scroll_top">
                    <label class="bhb-color-circle"><span class="bhb-color-circle__lab">پس‌زمینه</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr($st['bg'] ?? '#5C6B7A'); ?>"></span><input type="color" data-ft="scroll_top" data-ft-key="bg" value="<?php echo esc_attr($st['bg'] ?? '#5C6B7A'); ?>"></label>
                    <label class="bhb-color-circle"><span class="bhb-color-circle__lab">آیکون</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr(($st['icon_color'] ?? '') ?: ($st['color'] ?? '#ffffff')); ?>"></span><input type="color" data-ft="scroll_top" data-ft-key="icon_color" value="<?php echo esc_attr(($st['icon_color'] ?? '') ?: '#ffffff'); ?>"></label>
                    <label class="bhb-side-field"><span>سایز دکمه</span><input type="number" min="36" max="72" data-ft="scroll_top" data-ft-key="size" value="<?php echo esc_attr((string) ($st['size'] ?? 48)); ?>"></label>
                    <label class="bhb-side-field"><span>سایز آیکون</span><input type="number" min="14" max="40" data-ft="scroll_top" data-ft-key="icon_size" value="<?php echo esc_attr((string) ($st['icon_size'] ?? 22)); ?>"></label>
                    <label class="bhb-side-field"><span>انحنا</span><input type="number" min="0" max="999" data-ft="scroll_top" data-ft-key="radius" value="<?php echo esc_attr((string) ($st['radius'] ?? 999)); ?>"></label>
                </div>
            </div>

            <?php $ct_mode = ($ct['style_mode'] ?? 'inherit') === 'custom' ? 'custom' : 'inherit'; ?>
            <div class="bhb-ft-editor<?php echo ! empty($ct['enabled']) ? ' is-open' : ''; ?>" data-ft-editor="contact_fab">
                <span class="customize-control-title" style="margin-top:14px"><?php esc_html_e('تنظیمات تماس سریع', 'bankai-theme'); ?></span>
                <div class="btg-mode-toggle" style="margin-bottom:10px">
                    <button type="button" class="btg-mode-btn<?php echo $ct_mode === 'inherit' ? ' is-active' : ''; ?>" data-ft="contact_fab" data-ft-key="style_mode" data-value="inherit">
                        <span class="btg-mode-btn__art btg-mode-btn__art--inherit"></span>
                        <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                    </button>
                    <button type="button" class="btg-mode-btn<?php echo $ct_mode === 'custom' ? ' is-active' : ''; ?>" data-ft="contact_fab" data-ft-key="style_mode" data-value="custom">
                        <span class="btg-mode-btn__art btg-mode-btn__art--custom"></span>
                        <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                    </button>
                </div>
                <input type="url" class="bhb-input-text" data-ft="contact_fab" data-ft-key="url" placeholder="https:// یا tel:" value="<?php echo esc_attr($ct['url'] ?? ''); ?>" style="margin-bottom:8px">
                <div class="bhb-icon-pick-row">
                    <button type="button" class="bhb-icon-preview" data-open-icon-modal data-ft="contact_fab" data-ft-key="icon" data-icon="<?php echo esc_attr($ct['icon'] ?? 'phone'); ?>">
                        <span class="bhb-icon-preview__glyph"><?php echo function_exists('bankai_theme_icon') ? bankai_theme_icon($ct['icon'] ?? 'phone', ['size' => 22]) : '☎'; ?></span>
                        <span><?php esc_html_e('آیکون', 'bankai-theme'); ?></span>
                    </button>
                </div>
                <div class="bhb-nav-skin bhb-nav-skin--custom-only<?php echo $ct_mode === 'custom' ? ' is-open' : ''; ?>" data-ft-custom="contact_fab">
                    <label class="bhb-color-circle"><span class="bhb-color-circle__lab">پس‌زمینه</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr($ct['bg'] ?? '#16a34a'); ?>"></span><input type="color" data-ft="contact_fab" data-ft-key="bg" value="<?php echo esc_attr($ct['bg'] ?? '#16a34a'); ?>"></label>
                    <label class="bhb-color-circle"><span class="bhb-color-circle__lab">آیکون</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr(($ct['icon_color'] ?? '') ?: '#ffffff'); ?>"></span><input type="color" data-ft="contact_fab" data-ft-key="icon_color" value="<?php echo esc_attr(($ct['icon_color'] ?? '') ?: '#ffffff'); ?>"></label>
                    <label class="bhb-side-field"><span>سایز دکمه</span><input type="number" min="36" max="72" data-ft="contact_fab" data-ft-key="size" value="<?php echo esc_attr((string) ($ct['size'] ?? 48)); ?>"></label>
                    <label class="bhb-side-field"><span>سایز آیکون</span><input type="number" min="14" max="40" data-ft="contact_fab" data-ft-key="icon_size" value="<?php echo esc_attr((string) ($ct['icon_size'] ?? 22)); ?>"></label>
                    <label class="bhb-side-field"><span>انحنا</span><input type="number" min="0" max="999" data-ft="contact_fab" data-ft-key="radius" value="<?php echo esc_attr((string) ($ct['radius'] ?? 999)); ?>"></label>
                </div>
            </div>

            <div class="bhb-ft-editor<?php echo ! empty($bn['enabled']) ? ' is-open' : ''; ?>" data-ft-editor="bottom_nav">
                <span class="customize-control-title" style="margin-top:14px"><?php esc_html_e('ظاهر نوار پایین', 'bankai-theme'); ?></span>
                <?php $bn_mode = ($bn['style_mode'] ?? 'inherit') === 'custom' ? 'custom' : 'inherit'; ?>
                <div class="btg-mode-toggle" style="margin-bottom:10px">
                    <button type="button" class="btg-mode-btn<?php echo $bn_mode === 'inherit' ? ' is-active' : ''; ?>" data-ft="bottom_nav" data-ft-key="style_mode" data-value="inherit">
                        <span class="btg-mode-btn__art btg-mode-btn__art--inherit"></span>
                        <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                    </button>
                    <button type="button" class="btg-mode-btn<?php echo $bn_mode === 'custom' ? ' is-active' : ''; ?>" data-ft="bottom_nav" data-ft-key="style_mode" data-value="custom">
                        <span class="btg-mode-btn__art btg-mode-btn__art--custom"></span>
                        <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                    </button>
                </div>
                <div class="bhb-nav-skin bhb-nav-skin--custom-only<?php echo $bn_mode === 'custom' ? ' is-open' : ''; ?>" data-bn-custom>
                    <label class="bhb-color-circle"><span class="bhb-color-circle__lab">پس‌زمینه</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr($bn['bg'] ?? '#ffffff'); ?>"></span><input type="color" data-ft="bottom_nav" data-ft-key="bg" value="<?php echo esc_attr($bn['bg'] ?? '#ffffff'); ?>"></label>
                    <label class="bhb-color-circle"><span class="bhb-color-circle__lab">متن</span><span class="bhb-color-circle__swatch" style="background:<?php echo esc_attr($bn['color'] ?? '#1a1a1a'); ?>"></span><input type="color" data-ft="bottom_nav" data-ft-key="color" value="<?php echo esc_attr($bn['color'] ?? '#1a1a1a'); ?>"></label>
                </div>
                <span class="customize-control-title" style="margin-top:12px"><?php esc_html_e('تعداد ستون', 'bankai-theme'); ?></span>
                <div class="bsl-grid bhb-cols-grid" style="grid-template-columns:repeat(3,1fr)">
                    <?php foreach ([3, 4, 5] as $col) : ?>
                        <button type="button" class="bsl-card<?php echo $bn_cols === $col ? ' is-on' : ''; ?>" data-bn-cols="<?php echo esc_attr((string) $col); ?>">
                            <span class="bsl-card__preview bsl-card__preview--cols-<?php echo esc_attr((string) $col); ?>" aria-hidden="true"></span>
                            <span class="bsl-card__label"><?php echo esc_html((string) $col); ?> ستون</span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <span class="customize-control-title" style="margin-top:12px"><?php esc_html_e('چیدمان آیتم', 'bankai-theme'); ?></span>
                <div class="bsl-grid" style="grid-template-columns:repeat(3,1fr)">
                    <?php
                    $layouts = [
                        'icon' => ['label' => 'فقط آیکون', 'art' => 'icon'],
                        'icon-text' => ['label' => 'آیکون + متن کنار', 'art' => 'icontext'],
                        'icon-text-below' => ['label' => 'آیکون + متن زیر', 'art' => 'iconbelow'],
                    ];
                    foreach ($layouts as $lid => $lm) :
                        ?>
                        <button type="button" class="bsl-card<?php echo $bn_layout === $lid ? ' is-on' : ''; ?>" data-bn-layout="<?php echo esc_attr($lid); ?>">
                            <span class="bsl-card__preview bsl-card__preview--layout-<?php echo esc_attr($lm['art']); ?>" aria-hidden="true"></span>
                            <span class="bsl-card__label"><?php echo esc_html($lm['label']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <span class="customize-control-title" style="margin-top:12px"><?php esc_html_e('آیتم‌های ستون', 'bankai-theme'); ?></span>
                <div class="bhb-bn-items" id="bhb-bn-items">
                    <?php for ($i = 0; $i < 5; $i++) :
                        $it = $bn_items[$i];
                        $hidden = $i >= $bn_cols ? ' is-hidden' : '';
                        ?>
                        <div class="bhb-bn-item<?php echo $hidden; ?>" data-bn-item="<?php echo esc_attr((string) $i); ?>">
                            <div class="bhb-bn-item__head">ستون <?php echo esc_html((string) ($i + 1)); ?></div>
                            <button type="button" class="bhb-icon-preview" data-open-icon-modal data-bn-item-icon="<?php echo esc_attr((string) $i); ?>" data-icon="<?php echo esc_attr($it['icon'] ?? 'home'); ?>">
                                <span class="bhb-icon-preview__glyph"><?php echo function_exists('bankai_theme_icon') ? bankai_theme_icon($it['icon'] ?? 'home', ['size' => 20]) : '•'; ?></span>
                                <span><?php esc_html_e('آیکون', 'bankai-theme'); ?></span>
                            </button>
                            <input type="text" class="bhb-input-text" data-bn-item-label="<?php echo esc_attr((string) $i); ?>" placeholder="برچسب" value="<?php echo esc_attr($it['label'] ?? ''); ?>">
                            <input type="url" class="bhb-input-text" data-bn-item-url="<?php echo esc_attr((string) $i); ?>" placeholder="https:// یا /path" value="<?php echo esc_attr($it['url'] ?? ''); ?>">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <input type="hidden" <?php $this->link(); ?> id="bfb-config-input" value="<?php echo esc_attr(is_string($this->value()) ? $this->value() : wp_json_encode($cfg)); ?>">
        </div>
        <?php
    }
}
