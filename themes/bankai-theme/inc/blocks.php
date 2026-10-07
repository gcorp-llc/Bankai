<?php
/**
 * Custom Gutenberg blocks (dynamic, zero-build)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * Register block category.
 *
 * @param array $cats Categories.
 * @return array
 */
function bankai_block_categories(array $cats): array {
    array_unshift($cats, [
        'slug'  => 'bankai',
        'title' => __('بنکای', 'bankai-theme'),
        'icon'  => null,
    ]);
    return $cats;
}
add_filter('block_categories_all', 'bankai_block_categories', 10, 1);

/**
 * Register editor assets + blocks.
 */
function bankai_register_blocks(): void {
    wp_register_style(
        'bankai-blocks',
        BANKAI_THEME_URI . '/assets/css/blocks.css',
        [],
        BANKAI_THEME_VERSION
    );
    wp_register_script(
        'bankai-blocks-editor',
        BANKAI_THEME_URI . '/assets/js/blocks-editor.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render'],
        BANKAI_THEME_VERSION,
        true
    );

    $blocks = [
        'bankai/hero' => [
            'title'      => __('هیرو بنکای', 'bankai-theme'),
            'attributes' => [
                'title'    => ['type' => 'string', 'default' => 'قالب فوق‌سریع Bankai'],
                'subtitle' => ['type' => 'string', 'default' => 'سبک، مدرن و بهینه برای Core Web Vitals'],
                'ctaText'  => ['type' => 'string', 'default' => 'شروع کنید'],
                'ctaUrl'   => ['type' => 'string', 'default' => '#'],
                'align'    => ['type' => 'string', 'default' => 'center'],
            ],
            'render'     => 'bankai_render_block_hero',
        ],
        'bankai/features' => [
            'title'      => __('ویژگی‌ها (۳ ستون)', 'bankai-theme'),
            'attributes' => [
                'f1Title' => ['type' => 'string', 'default' => 'سرعت'],
                'f1Text'  => ['type' => 'string', 'default' => 'بدون jQuery و با CSS حداقلی.'],
                'f2Title' => ['type' => 'string', 'default' => 'سئو'],
                'f2Text'  => ['type' => 'string', 'default' => 'سازگار با پلاگین Bankai Core.'],
                'f3Title' => ['type' => 'string', 'default' => 'RTL'],
                'f3Text'  => ['type' => 'string', 'default' => 'پشتیبانی کامل فارسی و راست‌چین.'],
            ],
            'render'     => 'bankai_render_block_features',
        ],
        'bankai/cta' => [
            'title'      => __('فراخوان اقدام (CTA)', 'bankai-theme'),
            'attributes' => [
                'title'   => ['type' => 'string', 'default' => 'آماده شروع هستید؟'],
                'text'    => ['type' => 'string', 'default' => 'همین حالا سایت خود را با Bankai بسازید.'],
                'btnText' => ['type' => 'string', 'default' => 'تماس با ما'],
                'btnUrl'  => ['type' => 'string', 'default' => '#'],
            ],
            'render'     => 'bankai_render_block_cta',
        ],
        'bankai/posts' => [
            'title'      => __('شبکه نوشته‌ها', 'bankai-theme'),
            'attributes' => [
                'count'    => ['type' => 'number', 'default' => 3],
                'category' => ['type' => 'string', 'default' => ''],
            ],
            'render'     => 'bankai_render_block_posts',
        ],
    ];

    foreach ($blocks as $name => $cfg) {
        register_block_type($name, [
            'api_version'     => 2,
            'title'           => $cfg['title'],
            'category'        => 'bankai',
            'icon'            => 'star-filled',
            'attributes'      => $cfg['attributes'],
            'editor_script'   => 'bankai-blocks-editor',
            'style'           => 'bankai-blocks',
            'editor_style'    => 'bankai-blocks',
            'render_callback' => $cfg['render'],
        ]);
    }
}
add_action('init', 'bankai_register_blocks');

/**
 * Hero block.
 *
 * @param array $a Attributes.
 * @return string
 */
function bankai_render_block_hero(array $a): string {
    $title    = esc_html($a['title'] ?? '');
    $subtitle = esc_html($a['subtitle'] ?? '');
    $cta_text = esc_html($a['ctaText'] ?? '');
    $cta_url  = esc_url($a['ctaUrl'] ?? '#');
    $align    = in_array($a['align'] ?? 'center', ['left', 'center', 'right'], true) ? $a['align'] : 'center';

    ob_start();
    ?>
    <section class="bankai-block bankai-block-hero bankai-block-hero--<?php echo esc_attr($align); ?>">
        <div class="bankai-block-hero__inner">
            <?php if ($title) : ?><h1 class="bankai-block-hero__title"><?php echo $title; // phpcs:ignore ?></h1><?php endif; ?>
            <?php if ($subtitle) : ?><p class="bankai-block-hero__sub"><?php echo $subtitle; // phpcs:ignore ?></p><?php endif; ?>
            <?php if ($cta_text) : ?>
                <a class="bankai-btn bankai-btn--primary" href="<?php echo $cta_url; // phpcs:ignore ?>"><?php echo $cta_text; // phpcs:ignore ?></a>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/**
 * Features block.
 *
 * @param array $a Attributes.
 * @return string
 */
function bankai_render_block_features(array $a): string {
    $items = [
        [$a['f1Title'] ?? '', $a['f1Text'] ?? ''],
        [$a['f2Title'] ?? '', $a['f2Text'] ?? ''],
        [$a['f3Title'] ?? '', $a['f3Text'] ?? ''],
    ];
    ob_start();
    ?>
    <section class="bankai-block bankai-block-features">
        <div class="bankai-block-features__grid">
            <?php foreach ($items as $item) : ?>
                <div class="bankai-block-features__item">
                    <h3><?php echo esc_html($item[0]); ?></h3>
                    <p><?php echo esc_html($item[1]); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/**
 * CTA block.
 *
 * @param array $a Attributes.
 * @return string
 */
function bankai_render_block_cta(array $a): string {
    ob_start();
    ?>
    <section class="bankai-block bankai-block-cta">
        <div class="bankai-block-cta__inner">
            <h2><?php echo esc_html($a['title'] ?? ''); ?></h2>
            <p><?php echo esc_html($a['text'] ?? ''); ?></p>
            <?php if (! empty($a['btnText'])) : ?>
                <a class="bankai-btn bankai-btn--primary" href="<?php echo esc_url($a['btnUrl'] ?? '#'); ?>">
                    <?php echo esc_html($a['btnText']); ?>
                </a>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/**
 * Posts grid block.
 *
 * @param array $a Attributes.
 * @return string
 */
function bankai_render_block_posts(array $a): string {
    $count = max(1, min(12, (int) ($a['count'] ?? 3)));
    $args  = [
        'posts_per_page'      => $count,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ];
    $cat = sanitize_title((string) ($a['category'] ?? ''));
    if ($cat !== '') {
        $args['category_name'] = $cat;
    }
    $q = new WP_Query($args);
    ob_start();
    ?>
    <section class="bankai-block bankai-block-posts">
        <div class="bankai-posts-grid">
            <?php
            if ($q->have_posts()) :
                while ($q->have_posts()) :
                    $q->the_post();
                    get_template_part('template-parts/content', get_post_type());
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p class="bankai-block-empty">' . esc_html__('نوشته‌ای یافت نشد.', 'bankai-theme') . '</p>';
            endif;
            ?>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}
