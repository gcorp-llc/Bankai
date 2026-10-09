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
            'bankai-astra-ui',
            BANKAI_THEME_URI . '/assets/js/bk-astra-ui.js',
            [],
            BANKAI_THEME_VERSION,
            true
        );
        wp_enqueue_script(
            'bankai-header-builder',
            BANKAI_THEME_URI . '/assets/js/header-builder.js',
            ['customize-controls', 'jquery', 'bankai-astra-ui'],
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

            <span class="customize-control-title ast-builder-title" style="margin-top:14px"><?php esc_html_e('ظاهر نوار', 'bankai-theme'); ?></span>
            <p class="description bhb-desc"><?php esc_html_e('تنظیمات جدا برای دسکتاپ و موبایل — مشابه Astra', 'bankai-theme'); ?></p>
            <?php
            $nd = $cfg['nav_desktop'] ?? (function_exists('bankai_header_nav_skin_defaults') ? bankai_header_nav_skin_defaults() : []);
            $nm = $cfg['nav_mobile'] ?? (function_exists('bankai_header_nav_skin_defaults') ? bankai_header_nav_skin_defaults() : []);
            $nd_mode = (($nd['style_mode'] ?? 'inherit') === 'custom') ? 'custom' : 'inherit';
            $nm_mode = (($nm['style_mode'] ?? 'inherit') === 'custom') ? 'custom' : 'inherit';
            ?>
            <div class="bhb-device-tabs bhb-device-tabs--visual" role="tablist">
              <button type="button" class="bhb-device-tab is-active" data-bhb-device-tab="desktop">
                <span class="bhb-device-tab__art bhb-device-tab__art--desk" aria-hidden="true">
                  <span class="bhb-device-tab__monitor"><i></i></span>
                </span>
                <span class="bhb-device-tab__lab"><?php esc_html_e('دسکتاپ', 'bankai-theme'); ?></span>
                <small><?php esc_html_e('نمایش عریض', 'bankai-theme'); ?></small>
              </button>
              <button type="button" class="bhb-device-tab" data-bhb-device-tab="mobile">
                <span class="bhb-device-tab__art bhb-device-tab__art--mob" aria-hidden="true">
                  <span class="bhb-device-tab__phone"><i></i></span>
                </span>
                <span class="bhb-device-tab__lab"><?php esc_html_e('موبایل', 'bankai-theme'); ?></span>
                <small><?php esc_html_e('نمایش موبایل', 'bankai-theme'); ?></small>
              </button>
            </div>

            <div class="bhb-device-panel is-active" data-bhb-device-panel="desktop">
              <div class="btg-mode-toggle btg-mode-toggle--visual bhb-style-mode" data-nav-mode="desktop" style="margin-bottom:12px">
                <button type="button" class="btg-mode-btn<?php echo $nd_mode === 'inherit' ? ' is-active' : ''; ?>" data-nav="desktop" data-nav-key="style_mode" data-value="inherit">
                  <span class="btg-mode-btn__art btg-mode-btn__art--inherit" aria-hidden="true"></span>
                  <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                  <small><?php esc_html_e('رنگ و فونت از طرح کلی', 'bankai-theme'); ?></small>
                </button>
                <button type="button" class="btg-mode-btn<?php echo $nd_mode === 'custom' ? ' is-active' : ''; ?>" data-nav="desktop" data-nav-key="style_mode" data-value="custom">
                  <span class="btg-mode-btn__art btg-mode-btn__art--custom" aria-hidden="true"></span>
                  <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                  <small><?php esc_html_e('تنظیم دستی رنگ و فاصله', 'bankai-theme'); ?></small>
                </button>
              </div>
              <div class="bhb-nav-skin bhb-nav-skin--custom-only<?php echo $nd_mode === 'custom' ? ' is-open' : ''; ?>" data-nav-skin="desktop" data-depends-mode="desktop">

            
            <div class="bhb-nav-skin-form" data-nav-skin="desktop">
              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('رنگ‌ها و زمینه', 'bankai-theme'); ?></div>
                <?php
                echo bankai_ui_color_row([
                    'label' => __('پس‌زمینه نوار', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nd['bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'bg'],
                    ]],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('متن / پیوند', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [
                        [
                            'value' => (string) ($nd['color'] ?? ''),
                            'default' => '',
                            'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'color'],
                        ],
                        [
                            'value' => (string) ($nd['link_active'] ?? ''),
                            'default' => '',
                            'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'link_active'],
                        ],
                    ],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('رنگ لینک فعال', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nd['link_active'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'link_active'],
                    ]],
                ]);
                ?>
              </div>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('پوشش پس‌زمینه ردیف‌ها', 'bankai-theme'); ?></div>
                <?php
                echo bankai_ui_color_row([
                    'label' => __('بالای سربرگ', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nd['row_top_bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'row_top_bg'],
                    ]],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('سربرگ اصلی', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nd['row_main_bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'row_main_bg'],
                    ]],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('پایین سربرگ', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nd['row_bottom_bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'row_bottom_bg'],
                    ]],
                ]);
                ?>
              </div>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('سربرگ چسبان', 'bankai-theme'); ?></div>
                <div class="bk-crow">
                  <div class="bk-crow__label"><span><?php esc_html_e('فعال', 'bankai-theme'); ?></span></div>
                  <div class="bk-crow__controls">
                    <label class="bhb-check-pill<?php echo ! empty($nd['sticky_on']) ? ' is-on' : ''; ?>"><input type="checkbox" data-nav="desktop" data-nav-key="sticky_on" <?php checked(! empty($nd['sticky_on'])); ?>><span><?php esc_html_e('چسبان', 'bankai-theme'); ?></span></label>
                  </div>
                </div>
                <?php
                echo bankai_ui_color_row([
                    'label' => __('پس‌زمینه اسکرول', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nd['sticky_bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'sticky_bg'],
                    ]],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('متن اسکرول', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nd['sticky_color'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'sticky_color'],
                    ]],
                ]);
                ?>
                <div class="bk-crow">
                  <div class="bk-crow__label"><span><?php esc_html_e('مات / شفاف', 'bankai-theme'); ?></span></div>
                  <div class="bk-crow__controls">
                    <label class="bhb-check-pill<?php echo ! empty($nd['sticky_blur']) ? ' is-on' : ''; ?>"><input type="checkbox" data-nav="desktop" data-nav-key="sticky_blur" <?php checked(! empty($nd['sticky_blur'])); ?>><span>Blur</span></label>
                    <label class="bhb-check-pill<?php echo ! empty($nd['sticky_anim']) ? ' is-on' : ''; ?>"><input type="checkbox" data-nav="desktop" data-nav-key="sticky_anim" <?php checked(! empty($nd['sticky_anim'])); ?>><span><?php esc_html_e('انیمیشن', 'bankai-theme'); ?></span></label>
                  </div>
                </div>
              </div>

              <?php
              echo bankai_ui_spacing([
                  'label' => __('حاشیه (Margin)', 'bankai-theme'),
                  'device' => true,
                  'nav' => 'desktop',
                  'attrs_prefix' => 'margin',
                  'linked' => ! empty($nd['margin_linked']) || ! isset($nd['margin_linked']),
                  'values' => [
                      'top' => (int) ($nd['margin_top'] ?? $nd['margin'] ?? 0),
                      'right' => (int) ($nd['margin_right'] ?? $nd['margin'] ?? 0),
                      'bottom' => (int) ($nd['margin_bottom'] ?? $nd['margin'] ?? 0),
                      'left' => (int) ($nd['margin_left'] ?? $nd['margin'] ?? 0),
                  ],
              ]);
              echo bankai_ui_spacing([
                  'label' => __('پدینگ (Padding)', 'bankai-theme'),
                  'device' => true,
                  'nav' => 'desktop',
                  'attrs_prefix' => 'padding',
                  'linked' => ! empty($nd['padding_linked']) || ! isset($nd['padding_linked']),
                  'values' => [
                      'top' => (int) ($nd['padding_top'] ?? $nd['padding_y'] ?? 0),
                      'right' => (int) ($nd['padding_right'] ?? $nd['padding_x'] ?? 0),
                      'bottom' => (int) ($nd['padding_bottom'] ?? $nd['padding_y'] ?? 0),
                      'left' => (int) ($nd['padding_left'] ?? $nd['padding_x'] ?? 0),
                  ],
              ]);
              echo bankai_ui_spacing([
                  'label' => __('Border Radius', 'bankai-theme'),
                  'device' => true,
                  'nav' => 'desktop',
                  'attrs_prefix' => 'radius',
                  'linked' => ! empty($nd['radius_linked']) || ! isset($nd['radius_linked']),
                  'values' => [
                      'top' => (int) ($nd['radius_top'] ?? $nd['radius'] ?? 0),
                      'right' => (int) ($nd['radius_right'] ?? $nd['radius'] ?? 0),
                      'bottom' => (int) ($nd['radius_bottom'] ?? $nd['radius'] ?? 0),
                      'left' => (int) ($nd['radius_left'] ?? $nd['radius'] ?? 0),
                  ],
              ]);
              ?>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('بوردر', 'bankai-theme'); ?></div>
                <div class="bk-crow">
                  <div class="bk-crow__label"><span><?php esc_html_e('ضخامت / سبک', 'bankai-theme'); ?></span></div>
                  <div class="bk-crow__controls" style="gap:8px">
                    <input type="number" min="0" max="12" style="width:56px" data-nav="desktop" data-nav-key="border_width" value="<?php echo esc_attr((string) ($nd['border_width'] ?? 0)); ?>">
                    <select data-nav="desktop" data-nav-key="border_style">
                      <?php foreach (['solid'=>'Solid','dashed'=>'Dashed','dotted'=>'Dotted','none'=>'None'] as $bv=>$bl): ?>
                        <option value="<?php echo esc_attr($bv); ?>" <?php selected(($nd['border_style'] ?? 'solid'), $bv); ?>><?php echo esc_html($bl); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <?php
                echo bankai_ui_color_row([
                    'label' => __('رنگ بوردر', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nd['border_color'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'border_color'],
                    ]],
                ]);
                ?>
              </div>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('استایل لینک فعال', 'bankai-theme'); ?></div>
                <div class="bhb-seg" style="margin:10px 14px">
                  <?php foreach (['none'=>__('بدون', 'bankai-theme'),'underline'=>__('زیرخط', 'bankai-theme'),'box'=>__('کادر', 'bankai-theme')] as $av=>$al): ?>
                    <button type="button" class="bhb-seg__btn<?php echo (($nd['active_style'] ?? 'none') === $av) ? ' is-active' : ''; ?>" data-nav="desktop" data-nav-key="active_style" data-value="<?php echo esc_attr($av); ?>"><?php echo esc_html($al); ?></button>
                  <?php endforeach; ?>
                </div>
                <div class="bhb-nav-active-panel" data-active-for="underline" style="<?php echo (($nd['active_style'] ?? '') === 'underline') ? '' : 'display:none'; ?>;padding:0 14px 12px">
                  <label class="bhb-side-field"><span><?php esc_html_e('نوع خط', 'bankai-theme'); ?></span>
                    <select data-nav="desktop" data-nav-key="ul_style">
                      <?php foreach (['solid','dashed','dotted','double'] as $us): ?>
                        <option value="<?php echo esc_attr($us); ?>" <?php selected(($nd['ul_style'] ?? 'solid'), $us); ?>><?php echo esc_html($us); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                  <?php
                  echo bankai_ui_color_row([
                      'label' => __('رنگ خط', 'bankai-theme'),
                      'swatches' => [[
                          'value' => (string) ($nd['ul_color'] ?? ''),
                          'default' => '',
                          'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'ul_color'],
                      ]],
                  ]);
                  ?>
                </div>
                <div class="bhb-nav-active-panel" data-active-for="box" style="<?php echo (($nd['active_style'] ?? '') === 'box') ? '' : 'display:none'; ?>;padding:0 14px 12px">
                  <?php
                  echo bankai_ui_color_row([
                      'label' => __('پس‌زمینه کادر', 'bankai-theme'),
                      'swatches' => [[
                          'value' => (string) ($nd['box_bg'] ?? ''),
                          'default' => '',
                          'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'box_bg'],
                      ]],
                  ]);
                  echo bankai_ui_color_row([
                      'label' => __('بوردر کادر', 'bankai-theme'),
                      'swatches' => [[
                          'value' => (string) ($nd['box_border_c'] ?? ''),
                          'default' => '',
                          'attrs' => ['data-nav' => 'desktop', 'data-nav-key' => 'box_border_c'],
                      ]],
                  ]);
                  ?>
                  <div class="bhb-nav-grid-nums" style="padding:8px 0">
                    <label class="bhb-side-field"><span><?php esc_html_e('شفافیت %', 'bankai-theme'); ?></span><input type="number" min="0" max="100" data-nav="desktop" data-nav-key="box_opacity" value="<?php echo esc_attr((string) ($nd['box_opacity'] ?? 100)); ?>"></label>
                    <label class="bhb-side-field"><span><?php esc_html_e('فاصله', 'bankai-theme'); ?></span><input type="number" min="0" max="40" data-nav="desktop" data-nav-key="box_pad" value="<?php echo esc_attr((string) ($nd['box_pad'] ?? 6)); ?>"></label>
                  </div>
                </div>
              </div>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('فونت لینک‌ها', 'bankai-theme'); ?></div>
                <div class="bk-crow">
                  <div class="bk-crow__label"><span><?php esc_html_e('سایز px', 'bankai-theme'); ?></span></div>
                  <div class="bk-crow__controls">
                    <input type="number" min="10" max="28" style="width:72px" data-nav="desktop" data-nav-key="font_size" value="<?php echo esc_attr((string) ($nd['font_size'] ?? 15)); ?>">
                  </div>
                </div>
              </div>
            </div>


              </div>
            </div>

            <div class="bhb-device-panel" data-bhb-device-panel="mobile" style="display:none">
              <div class="btg-mode-toggle btg-mode-toggle--visual bhb-style-mode" data-nav-mode="mobile" style="margin-bottom:12px">
                <button type="button" class="btg-mode-btn<?php echo $nm_mode === 'inherit' ? ' is-active' : ''; ?>" data-nav="mobile" data-nav-key="style_mode" data-value="inherit">
                  <span class="btg-mode-btn__art btg-mode-btn__art--inherit" aria-hidden="true"></span>
                  <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                  <small><?php esc_html_e('رنگ و فونت از طرح کلی', 'bankai-theme'); ?></small>
                </button>
                <button type="button" class="btg-mode-btn<?php echo $nm_mode === 'custom' ? ' is-active' : ''; ?>" data-nav="mobile" data-nav-key="style_mode" data-value="custom">
                  <span class="btg-mode-btn__art btg-mode-btn__art--custom" aria-hidden="true"></span>
                  <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                  <small><?php esc_html_e('تنظیم دستی رنگ و فاصله', 'bankai-theme'); ?></small>
                </button>
              </div>
              <div class="bhb-nav-skin bhb-nav-skin--custom-only<?php echo $nm_mode === 'custom' ? ' is-open' : ''; ?>" data-nav-skin="mobile" data-depends-mode="mobile">

            
            <div class="bhb-nav-skin-form" data-nav-skin="mobile">
              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('رنگ‌ها و زمینه', 'bankai-theme'); ?></div>
                <?php
                echo bankai_ui_color_row([
                    'label' => __('پس‌زمینه نوار', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nm['bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'bg'],
                    ]],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('متن / پیوند', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [
                        [
                            'value' => (string) ($nm['color'] ?? ''),
                            'default' => '',
                            'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'color'],
                        ],
                        [
                            'value' => (string) ($nm['link_active'] ?? ''),
                            'default' => '',
                            'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'link_active'],
                        ],
                    ],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('رنگ لینک فعال', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nm['link_active'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'link_active'],
                    ]],
                ]);
                ?>
              </div>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('پوشش پس‌زمینه ردیف‌ها', 'bankai-theme'); ?></div>
                <?php
                echo bankai_ui_color_row([
                    'label' => __('بالای سربرگ', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nm['row_top_bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'row_top_bg'],
                    ]],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('سربرگ اصلی', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nm['row_main_bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'row_main_bg'],
                    ]],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('پایین سربرگ', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nm['row_bottom_bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'row_bottom_bg'],
                    ]],
                ]);
                ?>
              </div>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('سربرگ چسبان', 'bankai-theme'); ?></div>
                <div class="bk-crow">
                  <div class="bk-crow__label"><span><?php esc_html_e('فعال', 'bankai-theme'); ?></span></div>
                  <div class="bk-crow__controls">
                    <label class="bhb-check-pill<?php echo ! empty($nm['sticky_on']) ? ' is-on' : ''; ?>"><input type="checkbox" data-nav="mobile" data-nav-key="sticky_on" <?php checked(! empty($nm['sticky_on'])); ?>><span><?php esc_html_e('چسبان', 'bankai-theme'); ?></span></label>
                  </div>
                </div>
                <?php
                echo bankai_ui_color_row([
                    'label' => __('پس‌زمینه اسکرول', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nm['sticky_bg'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'sticky_bg'],
                    ]],
                ]);
                echo bankai_ui_color_row([
                    'label' => __('متن اسکرول', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nm['sticky_color'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'sticky_color'],
                    ]],
                ]);
                ?>
                <div class="bk-crow">
                  <div class="bk-crow__label"><span><?php esc_html_e('مات / شفاف', 'bankai-theme'); ?></span></div>
                  <div class="bk-crow__controls">
                    <label class="bhb-check-pill<?php echo ! empty($nm['sticky_blur']) ? ' is-on' : ''; ?>"><input type="checkbox" data-nav="mobile" data-nav-key="sticky_blur" <?php checked(! empty($nm['sticky_blur'])); ?>><span>Blur</span></label>
                    <label class="bhb-check-pill<?php echo ! empty($nm['sticky_anim']) ? ' is-on' : ''; ?>"><input type="checkbox" data-nav="mobile" data-nav-key="sticky_anim" <?php checked(! empty($nm['sticky_anim'])); ?>><span><?php esc_html_e('انیمیشن', 'bankai-theme'); ?></span></label>
                  </div>
                </div>
              </div>

              <?php
              echo bankai_ui_spacing([
                  'label' => __('حاشیه (Margin)', 'bankai-theme'),
                  'device' => true,
                  'nav' => 'mobile',
                  'attrs_prefix' => 'margin',
                  'linked' => ! empty($nm['margin_linked']) || ! isset($nm['margin_linked']),
                  'values' => [
                      'top' => (int) ($nm['margin_top'] ?? $nm['margin'] ?? 0),
                      'right' => (int) ($nm['margin_right'] ?? $nm['margin'] ?? 0),
                      'bottom' => (int) ($nm['margin_bottom'] ?? $nm['margin'] ?? 0),
                      'left' => (int) ($nm['margin_left'] ?? $nm['margin'] ?? 0),
                  ],
              ]);
              echo bankai_ui_spacing([
                  'label' => __('پدینگ (Padding)', 'bankai-theme'),
                  'device' => true,
                  'nav' => 'mobile',
                  'attrs_prefix' => 'padding',
                  'linked' => ! empty($nm['padding_linked']) || ! isset($nm['padding_linked']),
                  'values' => [
                      'top' => (int) ($nm['padding_top'] ?? $nm['padding_y'] ?? 0),
                      'right' => (int) ($nm['padding_right'] ?? $nm['padding_x'] ?? 0),
                      'bottom' => (int) ($nm['padding_bottom'] ?? $nm['padding_y'] ?? 0),
                      'left' => (int) ($nm['padding_left'] ?? $nm['padding_x'] ?? 0),
                  ],
              ]);
              echo bankai_ui_spacing([
                  'label' => __('Border Radius', 'bankai-theme'),
                  'device' => true,
                  'nav' => 'mobile',
                  'attrs_prefix' => 'radius',
                  'linked' => ! empty($nm['radius_linked']) || ! isset($nm['radius_linked']),
                  'values' => [
                      'top' => (int) ($nm['radius_top'] ?? $nm['radius'] ?? 0),
                      'right' => (int) ($nm['radius_right'] ?? $nm['radius'] ?? 0),
                      'bottom' => (int) ($nm['radius_bottom'] ?? $nm['radius'] ?? 0),
                      'left' => (int) ($nm['radius_left'] ?? $nm['radius'] ?? 0),
                  ],
              ]);
              ?>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('بوردر', 'bankai-theme'); ?></div>
                <div class="bk-crow">
                  <div class="bk-crow__label"><span><?php esc_html_e('ضخامت / سبک', 'bankai-theme'); ?></span></div>
                  <div class="bk-crow__controls" style="gap:8px">
                    <input type="number" min="0" max="12" style="width:56px" data-nav="mobile" data-nav-key="border_width" value="<?php echo esc_attr((string) ($nm['border_width'] ?? 0)); ?>">
                    <select data-nav="mobile" data-nav-key="border_style">
                      <?php foreach (['solid'=>'Solid','dashed'=>'Dashed','dotted'=>'Dotted','none'=>'None'] as $bv=>$bl): ?>
                        <option value="<?php echo esc_attr($bv); ?>" <?php selected(($nm['border_style'] ?? 'solid'), $bv); ?>><?php echo esc_html($bl); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <?php
                echo bankai_ui_color_row([
                    'label' => __('رنگ بوردر', 'bankai-theme'),
                    'device' => true,
                    'swatches' => [[
                        'value' => (string) ($nm['border_color'] ?? ''),
                        'default' => '',
                        'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'border_color'],
                    ]],
                ]);
                ?>
              </div>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('استایل لینک فعال', 'bankai-theme'); ?></div>
                <div class="bhb-seg" style="margin:10px 14px">
                  <?php foreach (['none'=>__('بدون', 'bankai-theme'),'underline'=>__('زیرخط', 'bankai-theme'),'box'=>__('کادر', 'bankai-theme')] as $av=>$al): ?>
                    <button type="button" class="bhb-seg__btn<?php echo (($nm['active_style'] ?? 'none') === $av) ? ' is-active' : ''; ?>" data-nav="mobile" data-nav-key="active_style" data-value="<?php echo esc_attr($av); ?>"><?php echo esc_html($al); ?></button>
                  <?php endforeach; ?>
                </div>
                <div class="bhb-nav-active-panel" data-active-for="underline" style="<?php echo (($nm['active_style'] ?? '') === 'underline') ? '' : 'display:none'; ?>;padding:0 14px 12px">
                  <label class="bhb-side-field"><span><?php esc_html_e('نوع خط', 'bankai-theme'); ?></span>
                    <select data-nav="mobile" data-nav-key="ul_style">
                      <?php foreach (['solid','dashed','dotted','double'] as $us): ?>
                        <option value="<?php echo esc_attr($us); ?>" <?php selected(($nm['ul_style'] ?? 'solid'), $us); ?>><?php echo esc_html($us); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                  <?php
                  echo bankai_ui_color_row([
                      'label' => __('رنگ خط', 'bankai-theme'),
                      'swatches' => [[
                          'value' => (string) ($nm['ul_color'] ?? ''),
                          'default' => '',
                          'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'ul_color'],
                      ]],
                  ]);
                  ?>
                </div>
                <div class="bhb-nav-active-panel" data-active-for="box" style="<?php echo (($nm['active_style'] ?? '') === 'box') ? '' : 'display:none'; ?>;padding:0 14px 12px">
                  <?php
                  echo bankai_ui_color_row([
                      'label' => __('پس‌زمینه کادر', 'bankai-theme'),
                      'swatches' => [[
                          'value' => (string) ($nm['box_bg'] ?? ''),
                          'default' => '',
                          'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'box_bg'],
                      ]],
                  ]);
                  echo bankai_ui_color_row([
                      'label' => __('بوردر کادر', 'bankai-theme'),
                      'swatches' => [[
                          'value' => (string) ($nm['box_border_c'] ?? ''),
                          'default' => '',
                          'attrs' => ['data-nav' => 'mobile', 'data-nav-key' => 'box_border_c'],
                      ]],
                  ]);
                  ?>
                  <div class="bhb-nav-grid-nums" style="padding:8px 0">
                    <label class="bhb-side-field"><span><?php esc_html_e('شفافیت %', 'bankai-theme'); ?></span><input type="number" min="0" max="100" data-nav="mobile" data-nav-key="box_opacity" value="<?php echo esc_attr((string) ($nm['box_opacity'] ?? 100)); ?>"></label>
                    <label class="bhb-side-field"><span><?php esc_html_e('فاصله', 'bankai-theme'); ?></span><input type="number" min="0" max="40" data-nav="mobile" data-nav-key="box_pad" value="<?php echo esc_attr((string) ($nm['box_pad'] ?? 6)); ?>"></label>
                  </div>
                </div>
              </div>

              <div class="bk-cgroup">
                <div class="bk-cgroup__head"><?php esc_html_e('فونت لینک‌ها', 'bankai-theme'); ?></div>
                <div class="bk-crow">
                  <div class="bk-crow__label"><span><?php esc_html_e('سایز px', 'bankai-theme'); ?></span></div>
                  <div class="bk-crow__controls">
                    <input type="number" min="10" max="28" style="width:72px" data-nav="mobile" data-nav-key="font_size" value="<?php echo esc_attr((string) ($nm['font_size'] ?? 15)); ?>">
                  </div>
                </div>
              </div>
            </div>


              </div>
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
            'bankai-astra-ui',
            BANKAI_THEME_URI . '/assets/js/bk-astra-ui.js',
            [],
            BANKAI_THEME_VERSION,
            true
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
