<?php
/**
 * Visual control for 404 page settings
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

if (! class_exists('WP_Customize_Control')) {
    return;
}

class Bankai_Customize_404_Page_Control extends WP_Customize_Control {

    public $type = 'bankai_404_page';

    public function enqueue(): void {
        wp_enqueue_style(
            'bankai-single-layout-control',
            BANKAI_THEME_URI . '/assets/css/customizer-single-layout.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_style(
            'bankai-blog-cards-control',
            BANKAI_THEME_URI . '/assets/css/customizer-blog-cards.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_style(
            'bankai-404-control',
            BANKAI_THEME_URI . '/assets/css/customizer-404.css',
            ['bankai-single-layout-control', 'bankai-blog-cards-control'],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_script(
            'bankai-404-control',
            BANKAI_THEME_URI . '/assets/js/customizer-404.js',
            ['customize-controls', 'jquery'],
            BANKAI_THEME_VERSION,
            true
        );
        wp_localize_script('bankai-404-control', 'bankai404Ctrl', [
            'homeUrl' => home_url('/'),
            'preview404' => home_url('/bankai-preview-404-page/'),
        ]);
    }

    public function render_content(): void {
        $m = static function (string $id, $default = '') {
            return get_theme_mod($id, $default);
        };
        $bool = static function ($v): bool {
            return ! empty($v) && $v !== '0' && $v !== 'false' && $v !== false;
        };

        $show_nav = $bool($m('page_404_show_navbar', true));
        $show_mob = $bool($m('page_404_show_mobile_bar', true));
        $show_footer = $bool($m('page_404_show_footer', true));
        $show_latest = $bool($m('page_404_show_latest', true));
        $auto_redir = $bool($m('page_404_auto_redirect', false));
        $theme = (string) $m('page_404_theme', 'astronaut');
        $msg = (string) $m('page_404_message', __('صفحه پیدا نشد. ممکن است آدرس اشتباه باشد یا صفحه حذف شده باشد.', 'bankai-theme'));
        $btn_text = (string) $m('page_404_btn_text', __('بازگشت به خانه', 'bankai-theme'));
        $btn_url = (string) $m('page_404_btn_url', home_url('/'));
        $btn_style = (string) $m('page_404_btn_style', 'primary');
        $btn_icon = (string) $m('page_404_btn_icon', 'home');
        $posts_layout = (string) $m('page_404_posts_layout', 'grid');
        $posts_count = (int) $m('page_404_posts_count', 3);
        $posts_card = (string) $m('page_404_posts_card_style', 'elevated');
        $posts_mode = (string) $m('page_404_posts_mode', 'latest');
        if ($posts_mode === 'related') { $posts_mode = 'random'; }
        $delay = (int) $m('page_404_redirect_delay', 8);
        $redir_type = (string) $m('page_404_redirect_type', 'custom');
        $redir_url = (string) $m('page_404_redirect_url', home_url('/'));

        $themes = [
            'astronaut'    => ['label' => 'فضانورد', 'desc' => 'صحنه شناور در فضا'],
            'cat'          => ['label' => 'گربه', 'desc' => 'ایلاستریشن بازیگوش'],
            'milk'         => ['label' => 'شیر ریخته', 'desc' => 'کارتن انیمیشنی'],
            'construction' => ['label' => 'در حال ساخت', 'desc' => 'نوار و مخروط ایمنی'],
            'geometric'    => ['label' => 'هندسی', 'desc' => 'اشکال مدرن شناور'],
            'lost'         => ['label' => 'گم‌شده', 'desc' => 'انیمیشن کلاسیک گم‌شدن'],
        ];
        ?>
        <div class="b404" dir="rtl">
            <span class="customize-control-title"><?php esc_html_e('عناصر نمایش ۴۰۴', 'bankai-theme'); ?></span>
            <p class="description"><?php esc_html_e('با کلیک فعال/خاموش کنید — مشابه تنظیمات مقاله', 'bankai-theme'); ?></p>

            <div class="bsl-grid b404-toggles">
                <button type="button" class="bsl-card<?php echo $show_nav ? ' is-on' : ''; ?>" data-b404-toggle="page_404_show_navbar" aria-pressed="<?php echo $show_nav ? 'true' : 'false'; ?>">
                    <span class="bsl-card__preview b404-prev--navbar" aria-hidden="true"></span>
                    <span class="bsl-card__label"><?php esc_html_e('نوبار', 'bankai-theme'); ?></span>
                    <span class="bsl-card__state"><?php echo $show_nav ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                </button>
                <button type="button" class="bsl-card<?php echo $show_mob ? ' is-on' : ''; ?>" data-b404-toggle="page_404_show_mobile_bar" aria-pressed="<?php echo $show_mob ? 'true' : 'false'; ?>">
                    <span class="bsl-card__preview b404-prev--mobilebar" aria-hidden="true"></span>
                    <span class="bsl-card__label"><?php esc_html_e('نوار موبایل', 'bankai-theme'); ?></span>
                    <span class="bsl-card__state"><?php echo $show_mob ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                </button>
                <button type="button" class="bsl-card<?php echo $show_footer ? ' is-on' : ''; ?>" data-b404-toggle="page_404_show_footer" aria-pressed="<?php echo $show_footer ? 'true' : 'false'; ?>">
                    <span class="bsl-card__preview b404-prev--footer" aria-hidden="true"></span>
                    <span class="bsl-card__label"><?php esc_html_e('پاورقی', 'bankai-theme'); ?></span>
                    <span class="bsl-card__state"><?php echo $show_footer ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                </button>
                <button type="button" class="bsl-card<?php echo $show_latest ? ' is-on' : ''; ?>" data-b404-toggle="page_404_show_latest" aria-pressed="<?php echo $show_latest ? 'true' : 'false'; ?>">
                    <span class="bsl-card__preview bsl-card__preview--related" aria-hidden="true"></span>
                    <span class="bsl-card__label"><?php esc_html_e('آخرین مقالات', 'bankai-theme'); ?></span>
                    <span class="bsl-card__state"><?php echo $show_latest ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                </button>
                <button type="button" class="bsl-card<?php echo $auto_redir ? ' is-on' : ''; ?>" data-b404-toggle="page_404_auto_redirect" aria-pressed="<?php echo $auto_redir ? 'true' : 'false'; ?>">
                    <span class="bsl-card__preview b404-prev--redirect" aria-hidden="true"></span>
                    <span class="bsl-card__label"><?php esc_html_e('انتقال خودکار', 'bankai-theme'); ?></span>
                    <span class="bsl-card__state"><?php echo $auto_redir ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                </button>
            </div>

            <hr class="bhb-section-sep" />
            <span class="customize-control-title"><?php esc_html_e('تم پیش‌ساخته ۴۰۴', 'bankai-theme'); ?></span>
            <div class="b404-themes">
                <?php foreach ($themes as $tid => $meta) : ?>
                    <button type="button" class="b404-theme<?php echo $theme === $tid ? ' is-active' : ''; ?>" data-b404-choice="page_404_theme" data-value="<?php echo esc_attr($tid); ?>">
                        <span class="b404-theme__preview b404-theme__preview--<?php echo esc_attr($tid); ?>" aria-hidden="true"></span>
                        <span class="b404-theme__label"><?php echo esc_html($meta['label']); ?></span>
                        <span class="b404-theme__desc"><?php echo esc_html($meta['desc']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <hr class="bhb-section-sep" />
            <span class="customize-control-title"><?php esc_html_e('متن هشدار', 'bankai-theme'); ?></span>
            <textarea class="b404-input" rows="3" data-b404-setting="page_404_message"><?php echo esc_textarea($msg); ?></textarea>

            <span class="customize-control-title" style="margin-top:12px"><?php esc_html_e('دکمه انتقال', 'bankai-theme'); ?></span>
            <input type="text" class="b404-input" data-b404-setting="page_404_btn_text" value="<?php echo esc_attr($btn_text); ?>" placeholder="<?php esc_attr_e('متن دکمه', 'bankai-theme'); ?>">
            <input type="url" class="b404-input" data-b404-setting="page_404_btn_url" value="<?php echo esc_attr($btn_url); ?>" placeholder="https://">
            <div class="bsl-grid" style="grid-template-columns:repeat(3,1fr);margin-top:8px">
                <?php foreach (['primary' => 'اصلی', 'outline' => 'حاشیه‌دار', 'ghost' => 'شیشه‌ای'] as $sid => $slab) : ?>
                    <button type="button" class="bsl-card<?php echo $btn_style === $sid ? ' is-on' : ''; ?>" data-b404-choice="page_404_btn_style" data-value="<?php echo esc_attr($sid); ?>">
                        <span class="bsl-card__preview b404-prev--btn-<?php echo esc_attr($sid); ?>" aria-hidden="true"></span>
                        <span class="bsl-card__label"><?php echo esc_html($slab); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="bhb-icon-pick-row" style="margin-top:8px">
                <button type="button" class="bhb-icon-preview" data-open-icon-modal data-b404-icon="page_404_btn_icon" data-icon="<?php echo esc_attr($btn_icon); ?>">
                    <span class="bhb-icon-preview__glyph"><?php echo function_exists('bankai_theme_icon') ? bankai_theme_icon($btn_icon, ['size' => 20]) : '•'; ?></span>
                    <span><?php esc_html_e('آیکون دکمه', 'bankai-theme'); ?></span>
                </button>
            </div>

            <div class="b404-panel<?php echo $show_latest ? ' is-open' : ''; ?>" data-b404-panel="latest">
                <hr class="bhb-section-sep" />
                <span class="customize-control-title"><?php esc_html_e('لیست مقالات', 'bankai-theme'); ?></span>

                <span class="customize-control-title" style="margin-top:8px"><?php esc_html_e('نوع بلوک مطالب', 'bankai-theme'); ?></span>
                <div class="bvc-grid">
                    <?php
                    $modes = [
                        'random' => ['label' => __('رندم', 'bankai-theme'), 'icon' => 'related'],
                        'latest' => ['label' => __('آخرین', 'bankai-theme'), 'icon' => 'latest'],
                    ];
                    foreach ($modes as $mid => $meta) :
                        ?>
                        <button type="button" class="bvc-card bvc-card--<?php echo esc_attr($meta['icon']); ?><?php echo $posts_mode === $mid ? ' is-active' : ''; ?>" data-b404-choice="page_404_posts_mode" data-value="<?php echo esc_attr($mid); ?>">
                            <span class="bvc-card__art" aria-hidden="true"></span>
                            <span class="bvc-card__label"><?php echo esc_html($meta['label']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <span class="customize-control-title" style="margin-top:12px"><?php esc_html_e('چیدمان مطالب مرتبط', 'bankai-theme'); ?></span>
                <div class="bvc-grid" style="grid-template-columns:repeat(3,1fr)">
                    <?php
                    $layouts = [
                        'grid' => ['label' => __('شبکه', 'bankai-theme'), 'icon' => 'grid'],
                        'list' => ['label' => __('لیست', 'bankai-theme'), 'icon' => 'list'],
                        'carousel' => ['label' => __('کاروسل', 'bankai-theme'), 'icon' => 'carousel'],
                    ];
                    foreach ($layouts as $lid => $meta) :
                        ?>
                        <button type="button" class="bvc-card bvc-card--<?php echo esc_attr($meta['icon']); ?><?php echo $posts_layout === $lid ? ' is-active' : ''; ?>" data-b404-choice="page_404_posts_layout" data-value="<?php echo esc_attr($lid); ?>">
                            <span class="bvc-card__art" aria-hidden="true"></span>
                            <span class="bvc-card__label"><?php echo esc_html($meta['label']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <label class="b404-field">
                    <span><?php esc_html_e('تعداد مطالب مرتبط', 'bankai-theme'); ?></span>
                    <input type="number" min="1" max="12" data-b404-setting="page_404_posts_count" value="<?php echo esc_attr((string) $posts_count); ?>">
                </label>

                <span class="customize-control-title" style="margin-top:12px"><?php esc_html_e('سبک کارت بلاگ', 'bankai-theme'); ?></span>
                <div class="bbc-grid">
                    <?php
                    $cards = [
                        'default'    => ['title' => __('پیش‌فرض', 'bankai-theme'), 'desc' => __('تصویر بالا، عنوان و خلاصه', 'bankai-theme')],
                        'horizontal' => ['title' => __('افقی', 'bankai-theme'), 'desc' => __('تصویر کنار محتوا', 'bankai-theme')],
                        'overlay'    => ['title' => __('پوشش تصویر', 'bankai-theme'), 'desc' => __('متن روی کاور', 'bankai-theme')],
                        'minimal'    => ['title' => __('مینیمال', 'bankai-theme'), 'desc' => __('بدون تصویر', 'bankai-theme')],
                        'bordered'   => ['title' => __('حاشیه‌دار', 'bankai-theme'), 'desc' => __('کادر مشخص', 'bankai-theme')],
                    ];
                    // map elevated/soft -> default for compat
                    $card_val = $posts_card;
                    if (in_array($card_val, ['elevated', 'soft'], true)) {
                        $card_val = 'default';
                    }
                    foreach ($cards as $cid => $meta) :
                        ?>
                        <button type="button" class="bbc-card<?php echo $card_val === $cid ? ' is-active' : ''; ?>" data-b404-choice="page_404_posts_card_style" data-value="<?php echo esc_attr($cid); ?>">
                            <div class="bbc-preview bbc-preview--<?php echo esc_attr($cid); ?>" aria-hidden="true">
                                <span class="bbc-preview__img"></span>
                                <span class="bbc-preview__line bbc-preview__line--title"></span>
                                <span class="bbc-preview__line bbc-preview__line--text"></span>
                                <span class="bbc-preview__meta"></span>
                            </div>
                            <strong><?php echo esc_html($meta['title']); ?></strong>
                            <em><?php echo esc_html($meta['desc']); ?></em>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="b404-panel<?php echo $auto_redir ? ' is-open' : ''; ?>" data-b404-panel="redirect">
                <hr class="bhb-section-sep" />
                <span class="customize-control-title"><?php esc_html_e('انتقال زمان‌بندی‌شده', 'bankai-theme'); ?></span>
                <label class="b404-field">
                    <span><?php esc_html_e('ثانیه انتظار', 'bankai-theme'); ?></span>
                    <input type="number" min="2" max="120" data-b404-setting="page_404_redirect_delay" value="<?php echo esc_attr((string) $delay); ?>">
                </label>
                <span class="customize-control-title" style="font-size:12px;margin-top:10px"><?php esc_html_e('مقصد انتقال', 'bankai-theme'); ?></span>
                <div class="bsl-grid" style="grid-template-columns:repeat(2,1fr)">
                    <button type="button" class="bsl-card<?php echo $redir_type === 'random' ? ' is-on' : ''; ?>" data-b404-choice="page_404_redirect_type" data-value="random">
                        <span class="bsl-card__preview b404-prev--random" aria-hidden="true"></span>
                        <span class="bsl-card__label"><?php esc_html_e('رندم مقالات', 'bankai-theme'); ?></span>
                    </button>
                    <button type="button" class="bsl-card<?php echo $redir_type === 'custom' ? ' is-on' : ''; ?>" data-b404-choice="page_404_redirect_type" data-value="custom">
                        <span class="bsl-card__preview b404-prev--custom" aria-hidden="true"></span>
                        <span class="bsl-card__label"><?php esc_html_e('لینک سفارشی', 'bankai-theme'); ?></span>
                    </button>
                </div>
                <div class="b404-redir-url<?php echo $redir_type === 'custom' ? ' is-open' : ''; ?>" data-b404-redir-url>
                    <input type="url" class="b404-input" data-b404-setting="page_404_redirect_url" value="<?php echo esc_attr($redir_url); ?>" placeholder="https://">
                </div>
            </div>
        </div>
        <?php
    }
}
