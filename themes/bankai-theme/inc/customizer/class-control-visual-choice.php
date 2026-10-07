<?php
/**
 * Visual multi-choice cards (layout / mode)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

if (! class_exists('WP_Customize_Control')) {
    return;
}

class Bankai_Customize_Visual_Choice_Control extends WP_Customize_Control {

    public $type = 'bankai_visual_choice';

    /** @var array<string, array{label:string, icon?:string}> */
    public $choices_visual = [];

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
        $value = $this->value();
        ?>
        <div class="bvc" dir="rtl">
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
            <div class="bvc-grid">
                <?php foreach ($this->choices_visual as $id => $meta) :
                    $icon = $meta['icon'] ?? $id;
                    ?>
                    <label class="bvc-card bvc-card--<?php echo esc_attr($icon); ?><?php echo (string) $value === (string) $id ? ' is-active' : ''; ?>">
                        <input type="radio" name="_customize-radio-<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> value="<?php echo esc_attr($id); ?>" <?php checked($value, $id); ?>>
                        <span class="bvc-card__art" aria-hidden="true"></span>
                        <span class="bvc-card__label"><?php echo esc_html($meta['label']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Color group — several color pickers in cards
 */
class Bankai_Customize_Color_Group_Control extends WP_Customize_Control {

    public $type = 'bankai_color_group';

    /** @var array<string, string> setting_id => label */
    public $colors = [];

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
        ?>
        <div class="bcg" dir="rtl">
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
            <div class="bcg-grid">
                <?php foreach ($this->colors as $sid => $label) :
                    $setting = $this->manager->get_setting($sid);
                    $val = $setting ? $setting->value() : '';
                    if ($val === '') {
                        $val = '#5C6B7A';
                    }
                    ?>
                    <label class="bcg-card">
                        <span class="bcg-card__swatch" style="background:<?php echo esc_attr($val); ?>"></span>
                        <span class="bcg-card__meta">
                            <span class="bcg-card__label"><?php echo esc_html($label); ?></span>
                            <input type="text" class="bcg-card__input" data-setting="<?php echo esc_attr($sid); ?>" value="<?php echo esc_attr($val); ?>" data-default="<?php echo esc_attr($val); ?>">
                        </span>
                        <input type="color" class="bcg-card__picker" data-setting="<?php echo esc_attr($sid); ?>" value="<?php echo esc_attr($val); ?>">
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Top spacing under transparent/sticky header — visual two modes.
 */
class Bankai_Customize_Top_Spacing_Control extends WP_Customize_Control {

    public $type = 'bankai_top_spacing';

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
        $offset_key = $this->input_attrs['offset_setting'] ?? (preg_replace('/_mode$/', '_offset', $this->id));
        $mode = $this->value() ?: 'offset';
        $offset_val = 80;
        if ($this->manager->get_setting($offset_key)) {
            $offset_val = (int) $this->manager->get_setting($offset_key)->value();
            if ($offset_val < 1 && $mode === 'offset') {
                $offset_val = 80;
            }
        }
        ?>
        <div class="bts" dir="rtl" data-offset-setting="<?php echo esc_attr($offset_key); ?>">
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
            <div class="bts-grid">
                <label class="bts-card<?php echo $mode === 'flush' ? ' is-active' : ''; ?>">
                    <input type="radio" name="_customize-radio-<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> value="flush" <?php checked($mode, 'flush'); ?>>
                    <span class="bts-card__art bts-card__art--flush" aria-hidden="true">
                        <span class="bts-nav"></span>
                        <span class="bts-content"></span>
                    </span>
                    <span class="bts-card__label"><?php esc_html_e('چسبیده به بالا', 'bankai-theme'); ?></span>
                </label>
                <label class="bts-card<?php echo $mode === 'offset' ? ' is-active' : ''; ?>">
                    <input type="radio" name="_customize-radio-<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> value="offset" <?php checked($mode, 'offset'); ?>>
                    <span class="bts-card__art bts-card__art--offset" aria-hidden="true">
                        <span class="bts-nav"></span>
                        <span class="bts-gap"></span>
                        <span class="bts-content"></span>
                    </span>
                    <span class="bts-card__label"><?php esc_html_e('فاصله از نوبار', 'bankai-theme'); ?></span>
                </label>
            </div>
        </div>
        <?php
    }
}

/**
 * Style mode: inherit theme vs custom (colors + typography).
 */
class Bankai_Customize_Style_Mode_Control extends WP_Customize_Control {

    public $type = 'bankai_style_mode';

    /** @var array<string, string> color setting id => label */
    public $colors = [];

    /** @var string font family setting id */
    public $font_setting = '';

    /** @var string font size setting id */
    public $size_setting = '';

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
        $mode = $this->value() ?: 'inherit';
        $fonts = [
            'inherit'     => __('ارث از تم', 'bankai-theme'),
            'Vazirmatn'   => 'Vazirmatn',
            'system-ui'   => 'System UI',
            'Georgia'     => 'Georgia',
            'Tahoma'      => 'Tahoma',
            'Arial'       => 'Arial',
        ];
        $font_val = $this->font_setting && $this->manager->get_setting($this->font_setting)
            ? (string) $this->manager->get_setting($this->font_setting)->value()
            : 'inherit';
        $size_val = $this->size_setting && $this->manager->get_setting($this->size_setting)
            ? (int) $this->manager->get_setting($this->size_setting)->value()
            : 16;
        ?>
        <div class="bsm" dir="rtl">
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <div class="bsm-modes">
                <label class="bsm-mode<?php echo $mode === 'inherit' ? ' is-active' : ''; ?>">
                    <input type="radio" name="_customize-radio-<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> value="inherit" <?php checked($mode, 'inherit'); ?>>
                    <span class="bsm-mode__art bsm-mode__art--inherit"></span>
                    <span><?php esc_html_e('ارث‌بری از تم', 'bankai-theme'); ?></span>
                </label>
                <label class="bsm-mode<?php echo $mode === 'custom' ? ' is-active' : ''; ?>">
                    <input type="radio" name="_customize-radio-<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> value="custom" <?php checked($mode, 'custom'); ?>>
                    <span class="bsm-mode__art bsm-mode__art--custom"></span>
                    <span><?php esc_html_e('سفارشی', 'bankai-theme'); ?></span>
                </label>
            </div>
            <div class="bsm-custom" style="<?php echo $mode === 'custom' ? '' : 'display:none'; ?>">
                <?php if ($this->colors) : ?>
                    <div class="bcg-grid bsm-colors">
                        <?php foreach ($this->colors as $sid => $label) :
                            $setting = $this->manager->get_setting($sid);
                            $val = $setting ? ($setting->value() ?: '#5C6B7A') : '#5C6B7A';
                            ?>
                            <label class="bcg-card">
                                <span class="bcg-card__swatch" style="background:<?php echo esc_attr($val); ?>"></span>
                                <span class="bcg-card__meta">
                                    <span class="bcg-card__label"><?php echo esc_html($label); ?></span>
                                    <input type="text" class="bcg-card__input" data-setting="<?php echo esc_attr($sid); ?>" value="<?php echo esc_attr($val); ?>">
                                </span>
                                <input type="color" class="bcg-card__picker" data-setting="<?php echo esc_attr($sid); ?>" value="<?php echo esc_attr($val); ?>">
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->font_setting) : ?>
                    <label class="bsm-field">
                        <span><?php esc_html_e('فونت', 'bankai-theme'); ?></span>
                        <select class="bsm-font" data-setting="<?php echo esc_attr($this->font_setting); ?>">
                            <?php foreach ($fonts as $fid => $flabel) : ?>
                                <option value="<?php echo esc_attr($fid); ?>" <?php selected($font_val, $fid); ?>><?php echo esc_html($flabel); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>
                <?php if ($this->size_setting) : ?>
                    <label class="bsm-field">
                        <span><?php esc_html_e('سایز فونت (px)', 'bankai-theme'); ?></span>
                        <input type="number" min="12" max="24" class="bsm-size" data-setting="<?php echo esc_attr($this->size_setting); ?>" value="<?php echo esc_attr((string) $size_val); ?>">
                    </label>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}



class Bankai_Customize_Seo_Ai_Control extends WP_Customize_Control {
    public $type = 'bankai_seo_ai';
    public $page_key = '';

    public function enqueue(): void {
        wp_enqueue_style('bankai-single-layout-control', BANKAI_THEME_URI . '/assets/css/customizer-single-layout.css', [], BANKAI_THEME_VERSION);
        wp_enqueue_script('bankai-single-layout-control', BANKAI_THEME_URI . '/assets/js/customizer-single-layout.js', ['customize-controls', 'jquery'], BANKAI_THEME_VERSION, true);
        wp_localize_script('bankai-single-layout-control', 'bankaiThemeCustomizer', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('bankai_theme_seo_ai'),
            'hasAi'   => (class_exists('Bankai_AI_Studio') || function_exists('bankai_ai_complete') || defined('BANKAI_CORE_VERSION')),
        ]);
    }

    public function render_content(): void {
        $key = $this->page_key ?: 'home';
        $title = (string) get_theme_mod("page_{$key}_seo_title", '');
        $desc  = (string) get_theme_mod("page_{$key}_seo_desc", '');
        $kw    = (string) get_theme_mod("page_{$key}_seo_keywords", '');
        $has_ai = class_exists('Bankai_AI_Studio') || function_exists('bankai_ai_complete') || defined('BANKAI_CORE_VERSION');
        $score = 0;
        if ($title !== '') $score += 30;
        if (mb_strlen($desc) >= 120) $score += 40; elseif ($desc !== '') $score += 20;
        if ($kw !== '') $score += 30;
        $score = min(100, $score);
        $color = $score >= 70 ? '#22c55e' : ($score >= 40 ? '#eab308' : '#ef4444');
        ?>
        <div class="bsa-panel" dir="rtl" data-page="<?php echo esc_attr($key); ?>">
            <div class="bsa-panel__head">
                <div class="bsa-score" style="--score:<?php echo (int) $score; ?>;--score-color:<?php echo esc_attr($color); ?>">
                    <svg viewBox="0 0 36 36" class="bsa-score__ring">
                        <path class="bsa-score__bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="bsa-score__fg" stroke-dasharray="<?php echo (int) $score; ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <span class="bsa-score__num"><?php echo (int) $score; ?></span>
                </div>
                <div>
                    <strong><?php esc_html_e('سئوی صفحه', 'bankai-theme'); ?></strong>
                    <p class="bsa-panel__hint"><?php echo $has_ai ? esc_html__('متصل به استودیو هوش مصنوعی بنکای', 'bankai-theme') : esc_html__('برای AI پلاگین Bankai Core را فعال کنید', 'bankai-theme'); ?></p>
                </div>
            </div>
            <label class="bsa-field">
                <span><?php esc_html_e('عنوان سئو', 'bankai-theme'); ?></span>
                <input type="text" class="bsa-input" data-seo-field="title" data-setting="page_<?php echo esc_attr($key); ?>_seo_title" value="<?php echo esc_attr($title); ?>" maxlength="70">
                <span class="bsa-len" data-for="title"><?php echo esc_html((string) mb_strlen($title)); ?>/60</span>
            </label>
            <label class="bsa-field">
                <span><?php esc_html_e('متا دیسکریپشن', 'bankai-theme'); ?></span>
                <textarea class="bsa-input bsa-textarea" data-seo-field="description" data-setting="page_<?php echo esc_attr($key); ?>_seo_desc" rows="3" maxlength="180"><?php echo esc_textarea($desc); ?></textarea>
                <span class="bsa-len" data-for="description"><?php echo esc_html((string) mb_strlen($desc)); ?>/160</span>
            </label>
            <label class="bsa-field">
                <span><?php esc_html_e('کلمات کلیدی', 'bankai-theme'); ?></span>
                <input type="text" class="bsa-input" data-seo-field="keywords" data-setting="page_<?php echo esc_attr($key); ?>_seo_keywords" value="<?php echo esc_attr($kw); ?>">
            </label>
            <button type="button" class="bsa-ai__btn" data-bankai-seo-ai="<?php echo esc_attr($key); ?>" <?php disabled(! $has_ai); ?>>
                <?php echo $has_ai ? esc_html__('تولید با هوش مصنوعی', 'bankai-theme') : esc_html__('AI غیرفعال — نصب Bankai Core', 'bankai-theme'); ?>
            </button>
            <div class="bsa-progress" hidden>
                <div class="bsa-progress__bar"></div>
                <span class="bsa-progress__label"><?php esc_html_e('در حال تولید…', 'bankai-theme'); ?></span>
            </div>
            <p class="bsa-ai__status" aria-live="polite"></p>
        </div>
        <?php
    }
}
