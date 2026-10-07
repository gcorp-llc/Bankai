<?php
/**
 * Visual blog card style control (Premium Minimalism)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

if (! class_exists('WP_Customize_Control')) {
    return;
}

class Bankai_Customize_Blog_Cards_Control extends WP_Customize_Control {

    public $type = 'bankai_blog_cards';

    public function enqueue(): void {
        wp_enqueue_style(
            'bankai-blog-cards-control',
            BANKAI_THEME_URI . '/assets/css/customizer-blog-cards.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_script(
            'bankai-blog-cards-control',
            BANKAI_THEME_URI . '/assets/js/customizer-blog-cards.js',
            ['customize-controls', 'jquery'],
            BANKAI_THEME_VERSION,
            true
        );
    }

    public function render_content(): void {
        $styles = [
            'default'    => ['title' => __('پیش‌فرض', 'bankai-theme'), 'desc' => __('تصویر بالا، عنوان و خلاصه', 'bankai-theme')],
            'horizontal' => ['title' => __('افقی', 'bankai-theme'), 'desc' => __('تصویر کنار محتوا', 'bankai-theme')],
            'overlay'    => ['title' => __('پوشش تصویر', 'bankai-theme'), 'desc' => __('متن روی کاور', 'bankai-theme')],
            'minimal'    => ['title' => __('مینیمال', 'bankai-theme'), 'desc' => __('بدون تصویر', 'bankai-theme')],
            'bordered'   => ['title' => __('حاشیه‌دار', 'bankai-theme'), 'desc' => __('کادر مشخص', 'bankai-theme')],
        ];
        $value = $this->value() ?: 'default';
        ?>
        <div class="bbc" id="bankai-blog-cards-control" dir="rtl">
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
            <div class="bbc-grid">
                <?php foreach ($styles as $id => $meta) : ?>
                    <label class="bbc-card<?php echo $value === $id ? ' is-active' : ''; ?>" data-style="<?php echo esc_attr($id); ?>">
                        <input type="radio" name="_customize-radio-<?php echo esc_attr($this->id); ?>" value="<?php echo esc_attr($id); ?>" <?php checked($value, $id); ?> <?php $this->link(); ?>>
                        <div class="bbc-preview bbc-preview--<?php echo esc_attr($id); ?>" aria-hidden="true">
                            <span class="bbc-preview__img"></span>
                            <span class="bbc-preview__line bbc-preview__line--title"></span>
                            <span class="bbc-preview__line bbc-preview__line--text"></span>
                            <span class="bbc-preview__meta"></span>
                        </div>
                        <strong><?php echo esc_html($meta['title']); ?></strong>
                        <em><?php echo esc_html($meta['desc']); ?></em>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
