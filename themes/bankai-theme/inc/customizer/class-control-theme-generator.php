<?php
/**
 * Customizer control: طرح کلی (دیزاین / صفحه / فونت)
 * Loaded only from customize_register when WP_Customize_Control exists.
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

if (! class_exists('WP_Customize_Control')) {
    return;
}

class Bankai_Customize_Theme_Generator_Control extends WP_Customize_Control {

    public $type = 'bankai_theme_generator';

    public function enqueue(): void {
        wp_enqueue_style(
            'bankai-theme-generator',
            BANKAI_THEME_URI . '/assets/css/theme-generator.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_script(
            'bankai-theme-generator',
            BANKAI_THEME_URI . '/assets/js/theme-generator.js',
            ['customize-controls'],
            BANKAI_THEME_VERSION,
            true
        );

        $design = function_exists('bankai_theme_get_design') ? bankai_theme_get_design() : [];
        $mod_json = get_theme_mod('bankai_design_json', '');
        if (is_string($mod_json) && $mod_json !== '' && function_exists('bankai_theme_sanitize_design')) {
            $decoded = json_decode($mod_json, true);
            if (is_array($decoded) && ! empty($decoded['colors'])) {
                $design = bankai_theme_sanitize_design($decoded);
            }
        }

        $presets = function_exists('bankai_theme_presets') ? bankai_theme_presets() : [];
        $presets_js = [];
        foreach ($presets as $id => $p) {
            $presets_js[$id] = [
                'label'  => $p['label'],
                'scheme' => $p['scheme'],
                'colors' => $p['colors'],
                'radius' => $p['radius'],
            ];
        }

        wp_localize_script('bankai-theme-generator', 'bankaiThemeGen', [
            'ajaxUrl'      => admin_url('admin-ajax.php'),
            'nonce'        => wp_create_nonce('bankai_theme_admin'),
            'design'       => $design,
            'presets'      => $presets_js,
            'styles'       => function_exists('bankai_theme_design_styles') ? bankai_theme_design_styles() : [],
            'isCustomizer' => true,
            'fonts'        => function_exists('bankai_theme_font_catalog') ? bankai_theme_font_catalog() : [],
            'customFonts'  => function_exists('bankai_theme_get_custom_fonts') ? bankai_theme_get_custom_fonts() : [],
            'i18n'         => [
                'saving'       => __('در حال ذخیره…', 'bankai-theme'),
                'saved'        => __('ذخیره شد', 'bankai-theme'),
                'error'        => __('خطا', 'bankai-theme'),
                'uploading'    => __('در حال آپلود فونت…', 'bankai-theme'),
                'upload_ok'    => __('فونت افزوده شد', 'bankai-theme'),
                'upload_fail'  => __('آپلود ناموفق', 'bankai-theme'),
            ],
        ]);
    }

    public function render_content(): void {
        $presets = function_exists('bankai_theme_presets') ? bankai_theme_presets() : [];
        $design  = function_exists('bankai_theme_get_design') ? bankai_theme_get_design() : [];
        $colors  = $design['colors'] ?? [];
        $fonts   = function_exists('bankai_theme_font_catalog') ? bankai_theme_font_catalog() : [];
        $custom  = function_exists('bankai_theme_get_custom_fonts') ? bankai_theme_get_custom_fonts() : [];

        $site_layout = get_theme_mod('site_layout', 'wide');
        $container_layout = get_theme_mod('container_layout', 'boxed');
        $container_style = get_theme_mod('container_style', 'default');
        $font_preset = get_theme_mod('font_preset', 'vazirmatn');
        $text_dir = get_theme_mod('text_direction', is_rtl() ? 'rtl' : 'ltr');
        $body_size = (int) get_theme_mod('font_body_size', 16);
        $body_weight = (string) get_theme_mod('font_body_weight', '400');
        $h1_size = (int) get_theme_mod('font_h1_size', 32);
        $h2_size = (int) get_theme_mod('font_h2_size', 24);

        $color_labels = [
            'primary' => __('اصلی', 'bankai-theme'), 'secondary' => __('فرعی', 'bankai-theme'),
            'accent' => __('تأکیدی', 'bankai-theme'), 'neutral' => __('خنثی', 'bankai-theme'),
            'base' => __('پس‌زمینه', 'bankai-theme'), 'base_content' => __('متن', 'bankai-theme'),
            'info' => __('اطلاعات', 'bankai-theme'), 'success' => __('موفق', 'bankai-theme'),
            'warning' => __('هشدار', 'bankai-theme'), 'error' => __('خطا', 'bankai-theme'),
        ];
        ?>
        <div class="btg btg--customizer" dir="rtl" id="bankai-tarh-kolli">
            <div class="btg-tabs-nav" role="tablist">
                <button type="button" class="btg-tab-btn is-active" data-btg-tab="design"><?php esc_html_e('دیزاین', 'bankai-theme'); ?></button>
                <button type="button" class="btg-tab-btn" data-btg-tab="page"><?php esc_html_e('صفحه', 'bankai-theme'); ?></button>
                <button type="button" class="btg-tab-btn" data-btg-tab="font"><?php esc_html_e('فونت', 'bankai-theme'); ?></button>
            </div>

            <div class="btg-tab-panel is-active" data-btg-panel="design">
                <div class="btg-panel">
                    <h3><?php esc_html_e('سبک طراحی', 'bankai-theme'); ?></h3>
                    <p class="btg-hint"><?php esc_html_e('سبک انتخابی روی همه المان‌های سایت اعمال می‌شود و با تم رنگی هماهنگ است.', 'bankai-theme'); ?></p>
                    <div class="btg-style-grid" id="btg-style-grid">
                        <?php
                        $styles = function_exists('bankai_theme_design_styles') ? bankai_theme_design_styles() : [];
                        $cur_style = $design['style'] ?? 'premium-minimalism';
                        foreach ($styles as $sid => $smeta) :
                            $active = ($cur_style === $sid) ? ' is-active' : '';
                            ?>
                            <button type="button" class="btg-style-card<?php echo esc_attr($active); ?>" data-style="<?php echo esc_attr($sid); ?>">
                                <span class="btg-style-card__art btg-style-card__art--<?php echo esc_attr($sid); ?>" aria-hidden="true">
                                    <span class="btg-style-card__shape"></span>
                                    <span class="btg-style-card__shape"></span>
                                    <span class="btg-style-card__shape"></span>
                                </span>
                                <span class="btg-style-card__name"><?php echo esc_html($smeta['label']); ?></span>
                                <span class="btg-style-card__desc"><?php echo esc_html($smeta['desc']); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="btg-panel">
                    <h3><?php esc_html_e('تم‌ها', 'bankai-theme'); ?></h3>
                    <div class="btg-presets">
                        <?php foreach ($presets as $id => $preset) :
                            $c = $preset['colors'];
                            $active = (($design['preset'] ?? '') === $id) ? ' is-active' : '';
                            ?>
                            <button type="button" class="btg-preset<?php echo esc_attr($active); ?>" data-preset="<?php echo esc_attr($id); ?>">
                                <span class="btg-preset-swatches">
                                    <span style="background:<?php echo esc_attr($c['primary']); ?>"></span>
                                    <span style="background:<?php echo esc_attr($c['secondary']); ?>"></span>
                                    <span style="background:<?php echo esc_attr($c['accent']); ?>"></span>
                                    <span style="background:<?php echo esc_attr($c['base']); ?>"></span>
                                </span>
                                <span class="btg-preset-name"><?php echo esc_html($preset['label']); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="btg-panel">
                    <h3><?php esc_html_e('رنگ‌ها', 'bankai-theme'); ?></h3>
                    <div class="btg-colors">
                        <?php foreach ($color_labels as $key => $label) : ?>
                            <div class="btg-color-row">
                                <label><?php echo esc_html($label); ?></label>
                                <input type="color" data-color="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($colors[$key] ?? '#000000'); ?>">
                                <input type="text" data-color-text="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($colors[$key] ?? '#000000'); ?>" maxlength="7">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="btg-radius">
                        <h3 style="margin:0 0 8px"><?php esc_html_e('گردی گوشه', 'bankai-theme'); ?></h3>
                        <?php foreach (['box' => __('کادرها', 'bankai-theme'), 'field' => __('فیلدها', 'bankai-theme'), 'selector' => __('نشان‌ها', 'bankai-theme')] as $rk => $rl) :
                            $rv = $design['radius'][$rk] ?? '1rem';
                            $range = ($rv === '0') ? 0 : (str_contains((string) $rv, 'px') ? (int) $rv : (int) round((float) $rv * 16));
                            ?>
                            <div>
                                <label><span><?php echo esc_html($rl); ?></span><span class="btg-radius-val"><?php echo esc_html((string) $rv); ?></span></label>
                                <input type="range" min="0" max="32" step="1" data-radius="<?php echo esc_attr($rk); ?>" value="<?php echo esc_attr((string) $range); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="btg-demo btg-demo--compact">
                    <h3><?php esc_html_e('پیش‌نمایش', 'bankai-theme'); ?></h3>
                    <div class="btg-btns"><span class="btg-demo-btn p"><?php esc_html_e('اصلی', 'bankai-theme'); ?></span><span class="btg-demo-btn s"><?php esc_html_e('فرعی', 'bankai-theme'); ?></span><span class="btg-demo-btn a"><?php esc_html_e('تأکیدی', 'bankai-theme'); ?></span></div>
                    <div class="btg-badges" style="margin-top:10px"><span class="btg-badge in">Info</span><span class="btg-badge su">OK</span><span class="btg-badge wa">Warn</span><span class="btg-badge er">Err</span></div>
                    <div class="btg-progress" style="margin-top:10px"><span></span></div>
                </div>
            </div>

            <div class="btg-tab-panel" data-btg-panel="page">
                <div class="btg-panel">
                    <h3><?php esc_html_e('طرح سایت', 'bankai-theme'); ?></h3>
                    <div class="btg-layout-grid" data-setting="site_layout">
                        <?php foreach (['boxed' => __('جعبه‌ای', 'bankai-theme'), 'wide' => __('عریض', 'bankai-theme'), 'full' => __('تمام‌عرض', 'bankai-theme'), 'content-sidebar' => __('سایدبار', 'bankai-theme')] as $val => $label) : ?>
                            <button type="button" class="btg-layout-card<?php echo $site_layout === $val ? ' is-active' : ''; ?>" data-value="<?php echo esc_attr($val); ?>">
                                <span class="btg-layout-wire btg-layout-wire--<?php echo esc_attr($val); ?>"></span>
                                <span class="btg-layout-label"><?php echo esc_html($label); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="btg-panel">
                    <h3><?php esc_html_e('چیدمان دربرگیرنده', 'bankai-theme'); ?></h3>
                    <div class="btg-layout-grid btg-layout-grid--2" data-setting="container_layout">
                        <?php foreach (['boxed' => __('جعبه‌ای', 'bankai-theme'), 'full' => __('کامل', 'bankai-theme')] as $val => $label) : ?>
                            <button type="button" class="btg-layout-card<?php echo $container_layout === $val ? ' is-active' : ''; ?>" data-value="<?php echo esc_attr($val); ?>">
                                <span class="btg-layout-wire btg-layout-wire--container-<?php echo esc_attr($val); ?>"></span>
                                <span class="btg-layout-label"><?php echo esc_html($label); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="btg-panel">
                    <h3><?php esc_html_e('سبک کانتینر', 'bankai-theme'); ?></h3>
                    <div class="btg-layout-grid btg-layout-grid--2" data-setting="container_style">
                        <?php foreach (['default' => __('پیش‌فرض', 'bankai-theme'), 'framed' => __('قاب‌دار', 'bankai-theme')] as $val => $label) : ?>
                            <button type="button" class="btg-layout-card<?php echo $container_style === $val ? ' is-active' : ''; ?>" data-value="<?php echo esc_attr($val); ?>">
                                <span class="btg-layout-wire btg-layout-wire--style-<?php echo esc_attr($val); ?>"></span>
                                <span class="btg-layout-label"><?php echo esc_html($label); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="btg-tab-panel" data-btg-panel="font">
                <div class="btg-panel">
                    <div class="btg-dir-row">
                        <span class="btg-dir-label"><?php esc_html_e('جهت نوشتار', 'bankai-theme'); ?></span>
                        <div class="btg-dir-toggle" data-setting="text_direction">
                            <button type="button" class="btg-dir-btn<?php echo $text_dir === 'rtl' ? ' is-active' : ''; ?>" data-value="rtl">RTL</button>
                            <button type="button" class="btg-dir-btn<?php echo $text_dir === 'ltr' ? ' is-active' : ''; ?>" data-value="ltr">LTR</button>
                        </div>
                    </div>
                </div>

                <div class="btg-panel">
                    <h3><?php esc_html_e('مخزن فونت', 'bankai-theme'); ?></h3>
                    <div class="btg-font-grid" data-setting="font_preset" id="btg-font-grid">
                        <?php foreach ($fonts as $fid => $fmeta) : ?>
                            <button type="button" class="btg-font-card<?php echo $font_preset === $fid ? ' is-active' : ''; ?>" data-value="<?php echo esc_attr($fid); ?>" data-stack="<?php echo esc_attr($fmeta['stack']); ?>">
                                <span class="btg-font-aa" style="font-family:<?php echo esc_attr($fmeta['stack']); ?>">Aa</span>
                                <span class="btg-font-name"><?php echo esc_html($fmeta['label']); ?></span>
                            </button>
                        <?php endforeach; ?>
                        <?php foreach ($custom as $cf) : ?>
                            <div class="btg-font-wrap">
                            <button type="button" class="btg-font-card<?php echo $font_preset === ($cf['id'] ?? '') ? ' is-active' : ''; ?>" data-value="<?php echo esc_attr($cf['id']); ?>" data-stack="<?php echo esc_attr($cf['stack']); ?>">
                                <span class="btg-font-aa" style="font-family:<?php echo esc_attr($cf['stack']); ?>">Aa</span>
                                <span class="btg-font-name"><?php echo esc_html($cf['label']); ?></span>
                            </button>
                            <button type="button" class="btg-font-del" data-del-font="<?php echo esc_attr($cf['id']); ?>" title="حذف">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="btg-hint" style="margin-top:10px"><?php esc_html_e('با انتخاب، روی همه عناصر اعمال می‌شود؛ در کارت هر عنصر می‌توانید جدا تنظیم کنید.', 'bankai-theme'); ?></p>
                </div>

                <div class="btg-panel">
                    <h3><?php esc_html_e('افزودن فونت', 'bankai-theme'); ?></h3>
                    <p class="btg-hint"><?php esc_html_e('woff2 / woff / ttf / otf', 'bankai-theme'); ?></p>
                    <input type="text" id="btg-font-label" class="btg-input-text" placeholder="<?php esc_attr_e('نام فونت', 'bankai-theme'); ?>">
                    <div class="btg-upload-row">
                        <label class="btg-btn btg-btn-ghost btg-file-label">
                            <?php esc_html_e('انتخاب فایل', 'bankai-theme'); ?>
                            <input type="file" id="btg-font-file" accept=".woff2,.woff,.ttf,.otf,font/woff2,font/woff,font/ttf,font/otf" hidden>
                        </label>
                        <button type="button" class="btg-btn btg-btn-primary" id="btg-font-upload"><?php esc_html_e('آپلود', 'bankai-theme'); ?></button>
                    </div>
                    <p class="btg-status" id="btg-font-status" aria-live="polite"></p>
                </div>

                <?php
                $font_options = '';
                foreach ($fonts as $fid => $fmeta) {
                    $font_options .= '<option value="' . esc_attr($fmeta['stack']) . '" data-id="' . esc_attr($fid) . '">' . esc_html($fmeta['label']) . '</option>';
                }
                foreach ($custom as $cf) {
                    $font_options .= '<option value="' . esc_attr($cf['stack']) . '" data-id="' . esc_attr($cf['id']) . '">' . esc_html($cf['label']) . '</option>';
                }
                $typo_cards = [
                    [
                        'key'   => 'body',
                        'label' => __('متن بدنه', 'bankai-theme'),
                        'stack' => (string) get_theme_mod('font_body', 'Vazirmatn, Tahoma, sans-serif'),
                        'size'  => (int) get_theme_mod('font_body_size', 16),
                        'size_mod' => 'font_body_size',
                        'stack_mod'=> 'font_body',
                        'color' => (string) get_theme_mod('font_body_color', '#1E293B'),
                        'color_mod' => 'font_body_color',
                        'color_hover' => (string) get_theme_mod('font_body_color_hover', '#0F172A'),
                        'color_hover_mod' => 'font_body_color_hover',
                        'min' => 12, 'max' => 24,
                    ],
                    [
                        'key' => 'nav',
                        'label' => __('نوار منو', 'bankai-theme'),
                        'stack' => (string) get_theme_mod('font_nav', 'Vazirmatn, Tahoma, sans-serif'),
                        'size' => (int) get_theme_mod('font_nav_size', 15),
                        'size_mod' => 'font_nav_size',
                        'stack_mod' => 'font_nav',
                        'color' => (string) get_theme_mod('font_nav_color', '#334155'),
                        'color_mod' => 'font_nav_color',
                        'color_hover' => (string) get_theme_mod('font_nav_color_hover', '#5B8DEF'),
                        'color_hover_mod' => 'font_nav_color_hover',
                        'min' => 12, 'max' => 20,
                    ],
                    [
                        'key' => 'title',
                        'label' => __('تیتر صفحه', 'bankai-theme'),
                        'stack' => (string) get_theme_mod('font_title', 'Vazirmatn, Tahoma, sans-serif'),
                        'size' => (int) get_theme_mod('font_title_size', 28),
                        'size_mod' => 'font_title_size',
                        'stack_mod' => 'font_title',
                        'color' => (string) get_theme_mod('font_title_color', '#0F172A'),
                        'color_mod' => 'font_title_color',
                        'color_hover' => (string) get_theme_mod('font_title_color_hover', '#1E293B'),
                        'color_hover_mod' => 'font_title_color_hover',
                        'min' => 16, 'max' => 48,
                    ],
                    [
                        'key' => 'h1',
                        'label' => 'H1',
                        'stack' => (string) get_theme_mod('font_heading', 'Vazirmatn, Tahoma, sans-serif'),
                        'size' => (int) get_theme_mod('font_h1_size', 32),
                        'size_mod' => 'font_h1_size',
                        'stack_mod' => 'font_heading',
                        'color' => (string) get_theme_mod('font_h1_color', '#0F172A'),
                        'color_mod' => 'font_h1_color',
                        'color_hover' => (string) get_theme_mod('font_h1_color_hover', '#1E293B'),
                        'color_hover_mod' => 'font_h1_color_hover',
                        'min' => 18, 'max' => 56,
                    ],
                    [
                        'key' => 'h2',
                        'label' => 'H2',
                        'stack' => (string) get_theme_mod('font_heading', 'Vazirmatn, Tahoma, sans-serif'),
                        'size' => (int) get_theme_mod('font_h2_size', 24),
                        'size_mod' => 'font_h2_size',
                        'stack_mod' => 'font_heading',
                        'color' => (string) get_theme_mod('font_h2_color', '#0F172A'),
                        'color_mod' => 'font_h2_color',
                        'color_hover' => (string) get_theme_mod('font_h2_color_hover', '#1E293B'),
                        'color_hover_mod' => 'font_h2_color_hover',
                        'min' => 14, 'max' => 40,
                    ],
                    [
                        'key' => 'h3',
                        'label' => 'H3',
                        'stack' => (string) get_theme_mod('font_heading', 'Vazirmatn, Tahoma, sans-serif'),
                        'size' => (int) get_theme_mod('font_h3_size', 20),
                        'size_mod' => 'font_h3_size',
                        'stack_mod' => 'font_heading',
                        'color' => (string) get_theme_mod('font_h3_color', '#1E293B'),
                        'color_mod' => 'font_h3_color',
                        'color_hover' => (string) get_theme_mod('font_h3_color_hover', '#334155'),
                        'color_hover_mod' => 'font_h3_color_hover',
                        'min' => 14, 'max' => 32,
                    ],
                    [
                        'key' => 'h4',
                        'label' => 'H4',
                        'stack' => (string) get_theme_mod('font_heading', 'Vazirmatn, Tahoma, sans-serif'),
                        'size' => (int) get_theme_mod('font_h4_size', 18),
                        'size_mod' => 'font_h4_size',
                        'stack_mod' => 'font_heading',
                        'color' => (string) get_theme_mod('font_h4_color', '#334155'),
                        'color_mod' => 'font_h4_color',
                        'color_hover' => (string) get_theme_mod('font_h4_color_hover', '#475569'),
                        'color_hover_mod' => 'font_h4_color_hover',
                        'min' => 12, 'max' => 28,
                    ],
                ];
                ?>
                
                <?php
                $font_color_mode = get_theme_mod('font_color_mode', 'inherit');
                ?>
                <div class="btg-mode-toggle" data-setting="font_color_mode" style="margin-bottom:14px;">
                    <button type="button" class="btg-mode-btn<?php echo $font_color_mode === 'inherit' ? ' is-active' : ''; ?>" data-value="inherit">
                        <span class="btg-mode-btn__art btg-mode-btn__art--inherit"></span>
                        <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                        <small><?php esc_html_e('رنگ متن از طرح کلی', 'bankai-theme'); ?></small>
                    </button>
                    <button type="button" class="btg-mode-btn<?php echo $font_color_mode === 'custom' ? ' is-active' : ''; ?>" data-value="custom">
                        <span class="btg-mode-btn__art btg-mode-btn__art--custom"></span>
                        <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                        <small><?php esc_html_e('رنگ جدا برای هر عنصر', 'bankai-theme'); ?></small>
                    </button>
                </div>
                <div class="btg-typo-cards" data-color-mode="<?php echo esc_attr($font_color_mode); ?>">

                    <?php foreach ($typo_cards as $card) : ?>
                        <div class="btg-typo-card" data-typo="<?php echo esc_attr($card['key']); ?>">
                            <div class="btg-typo-card__head"><?php echo esc_html($card['label']); ?></div>
                            <div class="btg-typo-card__body">
                                <label class="btg-mini-label"><?php esc_html_e('فونت', 'bankai-theme'); ?></label>
                                <select class="btg-font-select" data-mod="<?php echo esc_attr($card['stack_mod']); ?>">
                                    <?php
                                    foreach ($fonts as $fid => $fmeta) {
                                        printf(
                                            '<option value="%s"%s>%s</option>',
                                            esc_attr($fmeta['stack']),
                                            selected($card['stack'], $fmeta['stack'], false),
                                            esc_html($fmeta['label'])
                                        );
                                    }
                                    foreach ($custom as $cf) {
                                        printf(
                                            '<option value="%s"%s>%s</option>',
                                            esc_attr($cf['stack']),
                                            selected($card['stack'], $cf['stack'], false),
                                            esc_html($cf['label'])
                                        );
                                    }
                                    ?>
                                </select>
                                <div class="btg-typo-card__row">
                                    <div class="btg-typo-field">
                                        <label class="btg-mini-label"><?php esc_html_e('سایز', 'bankai-theme'); ?></label>
                                        <input type="number" min="<?php echo (int) $card['min']; ?>" max="<?php echo (int) $card['max']; ?>" data-mod="<?php echo esc_attr($card['size_mod']); ?>" value="<?php echo esc_attr((string) $card['size']); ?>">
                                    </div>
                                    <div class="btg-typo-field">
                                        <label class="btg-mini-label"><?php esc_html_e('عادی', 'bankai-theme'); ?></label>
                                        <label class="btg-color-circle">
                                            <input type="color" data-mod="<?php echo esc_attr($card['color_mod']); ?>" value="<?php echo esc_attr($card['color']); ?>">
                                            <span class="btg-color-swatch" style="background:<?php echo esc_attr($card['color']); ?>"></span>
                                        </label>
                                    </div>
                                    <div class="btg-typo-field">
                                        <label class="btg-mini-label"><?php esc_html_e('هاور', 'bankai-theme'); ?></label>
                                        <label class="btg-color-circle">
                                            <input type="color" data-mod="<?php echo esc_attr($card['color_hover_mod']); ?>" value="<?php echo esc_attr($card['color_hover']); ?>">
                                            <span class="btg-color-swatch" style="background:<?php echo esc_attr($card['color_hover']); ?>"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
        <?php
    }
}
