<?php
/**
 * Login / Register — same visual design system as طرح کلی › دیزاین
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

if (! class_exists('WP_Customize_Control')) {
    return;
}

class Bankai_Customize_Login_Page_Control extends WP_Customize_Control {
    public $type = 'bankai_login_page';

    public function enqueue(): void {
        wp_enqueue_style(
            'bankai-theme-generator',
            BANKAI_THEME_URI . '/assets/css/theme-generator.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_style(
            'bankai-login-control',
            BANKAI_THEME_URI . '/assets/css/customizer-login.css',
            ['bankai-theme-generator', 'bankai-single-layout-control'],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_media();
        wp_enqueue_script(
            'bankai-login-control',
            BANKAI_THEME_URI . '/assets/js/customizer-login.js',
            ['customize-controls', 'jquery'],
            BANKAI_THEME_VERSION,
            true
        );
        wp_localize_script('bankai-login-control', 'bankaiLoginCtrl', [
            'homeUrl'        => home_url('/'),
            'loginPreview'   => home_url('/?bankai_login_preview=1'),
            'registerPreview'=> home_url('/?bankai_login_preview=1&view=register'),
            'i18n'           => [
                'select' => __('انتخاب تصویر', 'bankai-theme'),
                'remove' => __('حذف', 'bankai-theme'),
            ],
        ]);
    }

    public function render_content(): void {
        $m = static function (string $id, $default = '') {
            return get_theme_mod($id, $default);
        };

        $layout     = (string) $m('login_layout', 'center');
        $style      = (string) $m('login_design_style', 'premium-minimalism');
        $theme_id   = (string) $m('login_theme', 'light');
        $title      = (string) $m('login_title', __('ورود به پنل', 'bankai-theme'));
        $reg_title  = (string) $m('login_register_title', __('ثبت‌نام', 'bankai-theme'));
        $intro_title = (string) $m('login_intro_title', __('خوش آمدید', 'bankai-theme'));
        $intro_text = (string) $m('login_intro_text', __('به پنل مدیریت خوش آمدید. وارد شوید یا حساب جدید بسازید.', 'bankai-theme'));
        $bg_id      = (int) $m('login_bg_id', 0);
        $logo_id    = (int) $m('login_logo_id', 0);
        $bg_url     = $bg_id ? wp_get_attachment_image_url($bg_id, 'large') : '';
        $logo_url   = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
        $login_slug = (string) $m('login_custom_slug', '');
        $reg_slug   = (string) $m('register_custom_slug', '');
        $block_default = (bool) $m('login_block_default_urls', false);

        $styles = function_exists('bankai_theme_design_styles') ? bankai_theme_design_styles() : [];
        $presets = function_exists('bankai_theme_presets') ? bankai_theme_presets() : [];

        $layouts = [
            'center' => ['label' => __('مرکز', 'bankai-theme'), 'art' => 'center'],
            'right'  => ['label' => __('راست', 'bankai-theme'), 'art' => 'right'],
            'left'   => ['label' => __('چپ', 'bankai-theme'), 'art' => 'left'],
            'split'  => ['label' => __('نصف صفحه', 'bankai-theme'), 'art' => 'split'],
        ];
        ?>
        <div class="blogin btg btg--customizer" dir="rtl">

            <div class="blogin-preview-switch" role="tablist">
                <button type="button" class="blogin-preview-switch__btn is-active" data-blogin-preview-view="login">
                    <?php esc_html_e('نمای ورود', 'bankai-theme'); ?>
                </button>
                <button type="button" class="blogin-preview-switch__btn" data-blogin-preview-view="register">
                    <?php esc_html_e('نمای ثبت‌نام', 'bankai-theme'); ?>
                </button>
            </div>

            <span class="customize-control-title"><?php esc_html_e('چیدمان فرم', 'bankai-theme'); ?></span>
            <p class="description"><?php esc_html_e('موقعیت کادر ورود و ثبت‌نام روی صفحه', 'bankai-theme'); ?></p>
            <div class="blogin-layouts">
                <?php foreach ($layouts as $id => $meta) : ?>
                    <button type="button"
                        class="blogin-layout<?php echo $layout === $id ? ' is-active' : ''; ?>"
                        data-blogin-setting="login_layout"
                        data-value="<?php echo esc_attr($id); ?>">
                        <span class="blogin-layout__art blogin-layout__art--<?php echo esc_attr($meta['art']); ?>" aria-hidden="true"></span>
                        <span class="blogin-layout__label"><?php echo esc_html($meta['label']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="btg-panel" style="margin-top:14px">
                <h3><?php esc_html_e('سبک طراحی', 'bankai-theme'); ?></h3>
                <p class="btg-hint"><?php esc_html_e('همان سبک‌های طرح کلی — فقط روی ورود و ثبت‌نام', 'bankai-theme'); ?></p>
                <div class="btg-style-grid" id="blogin-style-grid">
                    <?php foreach ($styles as $sid => $smeta) :
                        $active = $style === $sid ? ' is-active' : '';
                        ?>
                        <button type="button" class="btg-style-card<?php echo esc_attr($active); ?>"
                            data-blogin-setting="login_design_style"
                            data-value="<?php echo esc_attr($sid); ?>">
                            <span class="btg-style-card__art btg-style-card__art--<?php echo esc_attr($sid); ?>" aria-hidden="true">
                                <span class="btg-style-card__shape"></span>
                                <span class="btg-style-card__shape"></span>
                                <span class="btg-style-card__shape"></span>
                            </span>
                            <span class="btg-style-card__name"><?php echo esc_html($smeta['label'] ?? $sid); ?></span>
                            <span class="btg-style-card__desc"><?php echo esc_html($smeta['desc'] ?? ''); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="btg-panel" style="margin-top:14px">
                <h3><?php esc_html_e('تم‌ها', 'bankai-theme'); ?></h3>
                <p class="btg-hint"><?php esc_html_e('پالت رنگی فقط برای صفحه ورود و ثبت‌نام', 'bankai-theme'); ?></p>
                <div class="btg-presets" id="blogin-presets">
                    <?php foreach ($presets as $id => $preset) :
                        $active = $theme_id === $id ? ' is-active' : '';
                        $colors = $preset['colors'] ?? [];
                        ?>
                        <button type="button" class="btg-preset<?php echo esc_attr($active); ?>"
                            data-blogin-setting="login_theme"
                            data-value="<?php echo esc_attr($id); ?>">
                            <span class="btg-preset-swatches">
                                <?php
                                foreach (['primary', 'secondary', 'accent', 'base'] as $ck) {
                                    $hex = $colors[$ck] ?? '#ccc';
                                    echo '<span style="background:' . esc_attr($hex) . '"></span>';
                                }
                                ?>
                            </span>
                            <span class="btg-preset-name"><?php echo esc_html($preset['label'] ?? $id); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <span class="customize-control-title" style="margin-top:14px"><?php esc_html_e('عنوان و برند', 'bankai-theme'); ?></span>
            <label class="blogin-field">
                <span><?php esc_html_e('عنوان فرم ورود', 'bankai-theme'); ?></span>
                <input type="text" data-blogin-setting="login_title" value="<?php echo esc_attr($title); ?>">
            </label>
            <label class="blogin-field">
                <span><?php esc_html_e('عنوان فرم ثبت‌نام', 'bankai-theme'); ?></span>
                <input type="text" data-blogin-setting="login_register_title" value="<?php echo esc_attr($reg_title); ?>">
            </label>

            <span class="customize-control-title" style="margin-top:14px"><?php esc_html_e('کارت معرفی کنار فرم', 'bankai-theme'); ?></span>
            <p class="description"><?php esc_html_e('در چیدمان نصف‌صفحه، راست و چپ نمایش داده می‌شود', 'bankai-theme'); ?></p>
            <label class="blogin-field">
                <span><?php esc_html_e('عنوان معرفی', 'bankai-theme'); ?></span>
                <input type="text" data-blogin-setting="login_intro_title" value="<?php echo esc_attr($intro_title); ?>">
            </label>
            <label class="blogin-field">
                <span><?php esc_html_e('متن معرفی', 'bankai-theme'); ?></span>
                <textarea data-blogin-setting="login_intro_text" rows="3" style="width:100%;border:1px solid #E5E7EB;border-radius:10px;padding:8px 10px;font:inherit"><?php echo esc_textarea($intro_text); ?></textarea>
            </label>

            <div class="blogin-media">
                <div class="blogin-media__item">
                    <span class="blogin-media__lab"><?php esc_html_e('لوگو', 'bankai-theme'); ?></span>
                    <div class="blogin-media__preview" data-blogin-preview="login_logo_id">
                        <?php if ($logo_url) : ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="">
                        <?php else : ?>
                            <span class="blogin-media__ph"><?php esc_html_e('بدون لوگو', 'bankai-theme'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="blogin-media__actions">
                        <button type="button" class="button" data-blogin-media="login_logo_id"><?php esc_html_e('انتخاب', 'bankai-theme'); ?></button>
                        <button type="button" class="button" data-blogin-media-clear="login_logo_id"><?php esc_html_e('حذف', 'bankai-theme'); ?></button>
                    </div>
                </div>
                <div class="blogin-media__item">
                    <span class="blogin-media__lab"><?php esc_html_e('تصویر پس‌زمینه', 'bankai-theme'); ?></span>
                    <div class="blogin-media__preview blogin-media__preview--bg" data-blogin-preview="login_bg_id">
                        <?php if ($bg_url) : ?>
                            <img src="<?php echo esc_url($bg_url); ?>" alt="">
                        <?php else : ?>
                            <span class="blogin-media__ph"><?php esc_html_e('پیش‌فرض تم', 'bankai-theme'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="blogin-media__actions">
                        <button type="button" class="button" data-blogin-media="login_bg_id"><?php esc_html_e('انتخاب', 'bankai-theme'); ?></button>
                        <button type="button" class="button" data-blogin-media-clear="login_bg_id"><?php esc_html_e('حذف', 'bankai-theme'); ?></button>
                    </div>
                </div>
            </div>

            <span class="customize-control-title" style="margin-top:14px"><?php esc_html_e('آدرس سفارشی ورود و ثبت‌نام', 'bankai-theme'); ?></span>
            <p class="description"><?php esc_html_e('به‌جای wp-login.php — پس از انتشار، پیوندهای یکتا را یک‌بار ذخیره کنید', 'bankai-theme'); ?></p>
            <label class="blogin-field">
                <span><?php esc_html_e('اسلاگ ورود', 'bankai-theme'); ?> <em>(مثلاً account)</em></span>
                <input type="text" data-blogin-setting="login_custom_slug" value="<?php echo esc_attr($login_slug); ?>" placeholder="account" dir="ltr">
            </label>
            <label class="blogin-field">
                <span><?php esc_html_e('اسلاگ ثبت‌نام', 'bankai-theme'); ?> <em>(مثلاً join)</em></span>
                <input type="text" data-blogin-setting="register_custom_slug" value="<?php echo esc_attr($reg_slug); ?>" placeholder="join" dir="ltr">
            </label>
            <button type="button" class="bsl-card<?php echo $block_default ? ' is-on' : ''; ?>" data-blogin-toggle="login_block_default_urls" style="width:100%;margin-top:8px">
                <span class="bsl-card__label"><?php esc_html_e('بستن آدرس‌های پیش‌فرض وردپرس', 'bankai-theme'); ?></span>
                <span class="bsl-card__state"><?php echo $block_default ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
            </button>
        </div>
        <?php
    }
}
