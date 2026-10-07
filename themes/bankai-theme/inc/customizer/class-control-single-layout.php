<?php
/**
 * Visual single-post layout toggles — card boxes
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

if (! class_exists('WP_Customize_Control')) {
    return;
}

class Bankai_Customize_Single_Layout_Control extends WP_Customize_Control {

    public $type = 'bankai_single_layout';

    /** @var array<string, array{label:string, icon:string}> */
    public $items = [];

    public function enqueue(): void {
        wp_enqueue_style(
            'bankai-single-layout-control',
            BANKAI_THEME_URI . '/assets/css/customizer-single-layout.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_script(
            'bankai-single-layout-control',
            BANKAI_THEME_URI . '/assets/js/customizer-single-layout.js',
            ['customize-controls', 'jquery'],
            BANKAI_THEME_VERSION,
            true
        );
    }

    public function render_content(): void {
        if (empty($this->items)) {
            return;
        }
        ?>
        <div class="bsl" dir="rtl">
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
            <div class="bsl-grid">
                <?php foreach ($this->items as $setting_id => $meta) :
                    $val = $this->manager->get_setting($setting_id) ? $this->manager->get_setting($setting_id)->value() : true;
                    $on  = ! empty($val) && $val !== '0' && $val !== 'false';
                    $icon = $meta['icon'] ?? 'block';
                    ?>
                    <button type="button"
                        class="bsl-card<?php echo $on ? ' is-on' : ''; ?>"
                        data-setting="<?php echo esc_attr($setting_id); ?>"
                        aria-pressed="<?php echo $on ? 'true' : 'false'; ?>">
                        <span class="bsl-card__preview bsl-card__preview--<?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
                        <span class="bsl-card__label"><?php echo esc_html($meta['label']); ?></span>
                        <span class="bsl-card__state"><?php echo $on ? esc_html__('فعال', 'bankai-theme') : esc_html__('خاموش', 'bankai-theme'); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Visual sidebar position control.
 */
class Bankai_Customize_Sidebar_Position_Control extends WP_Customize_Control {

    public $type = 'bankai_sidebar_position';

    public function enqueue(): void {
        wp_enqueue_style(
            'bankai-single-layout-control',
            BANKAI_THEME_URI . '/assets/css/customizer-single-layout.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_script(
            'bankai-single-layout-control',
            BANKAI_THEME_URI . '/assets/js/customizer-single-layout.js',
            ['customize-controls', 'jquery'],
            BANKAI_THEME_VERSION,
            true
        );
    }

    public function render_content(): void {
        $choices = [
            'default' => __('پیش‌فرض', 'bankai-theme'),
            'left'    => __('سایدبار چپ', 'bankai-theme'),
            'right'   => __('سایدبار راست', 'bankai-theme'),
            'none'    => __('بدون سایدبار', 'bankai-theme'),
        ];
        $value = $this->value() ?: 'default';
        ?>
        <div class="bsp" dir="rtl">
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <div class="bsp-grid">
                <?php foreach ($choices as $id => $label) : ?>
                    <label class="bsp-card bsp-card--<?php echo esc_attr($id); ?><?php echo $value === $id ? ' is-active' : ''; ?>">
                        <input type="radio" name="_customize-radio-<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> value="<?php echo esc_attr($id); ?>" <?php checked($value, $id); ?>>
                        <span class="bsp-card__art" aria-hidden="true">
                            <span class="bsp-bar bsp-bar--side"></span>
                            <span class="bsp-bar bsp-bar--main"></span>
                        </span>
                        <span class="bsp-card__label"><?php echo esc_html($label); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
