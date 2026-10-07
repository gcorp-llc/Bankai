<?php
/**
 * Theme helpers
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

/**
 * Get theme mod with default.
 *
 * @param string $key     Theme mod key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function bankai_theme_mod(string $key, $default = '') {
    $val = get_theme_mod($key, $default);
    // Customizer checkboxes may store "" / "0" / false when off
    if (is_bool($default)) {
        if ($val === '' || $val === '0' || $val === 0 || $val === 'false' || $val === null) {
            return false;
        }
        if ($val === true || $val === '1' || $val === 1 || $val === 'true') {
            return true;
        }
        return (bool) $val;
    }
    return $val;
}

/**
 * Force posts_per_page from theme mod on main blog/archive queries.
 */
function bankai_blog_posts_per_page(WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query()) {
        return;
    }
    if ($query->is_search()) {
        $n = (int) get_theme_mod('search_posts_per_page', 0);
        if ($n < 1) {
            $n = (int) get_theme_mod('blog_posts_per_page', 0);
        }
        if ($n > 0) {
            $query->set('posts_per_page', max(1, min(48, $n)));
        }
        return;
    }
    if ($query->is_home() || $query->is_archive()) {
        $n = (int) get_theme_mod('blog_posts_per_page', 0);
        if ($n > 0) {
            $query->set('posts_per_page', max(1, min(48, $n)));
        }
    }
}
add_action('pre_get_posts', 'bankai_blog_posts_per_page');

/**
 * Expand search to title, content, tags, categories, SEO keywords meta.
 */
function bankai_search_query_expand(WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return;
    }
    $s = $query->get('s');
    if (! is_string($s) || $s === '') {
        return;
    }
    // Prefer posts; keep pages too
    $query->set('post_type', ['post', 'page']);
}
add_action('pre_get_posts', 'bankai_search_query_expand', 20);

/**
 * Join keyword meta into search SQL for better matching.
 */
function bankai_search_posts_join($join, WP_Query $query) {
    global $wpdb;
    if (! $query->is_main_query() || ! $query->is_search() || is_admin()) {
        return $join;
    }
    $join .= " LEFT JOIN {$wpdb->postmeta} AS bankai_seo_kw ON ({$wpdb->posts}.ID = bankai_seo_kw.post_id AND bankai_seo_kw.meta_key IN ('_bankai_seo_keywords','bankai_seo_keywords','rank_math_focus_keyword','yoast_wpseo_focuskw')) ";
    return $join;
}
add_filter('posts_join', 'bankai_search_posts_join', 10, 2);

function bankai_search_posts_where($where, WP_Query $query) {
    global $wpdb;
    if (! $query->is_main_query() || ! $query->is_search() || is_admin()) {
        return $where;
    }
    $s = $query->get('s');
    if (! is_string($s) || $s === '') {
        return $where;
    }
    $like = '%' . $wpdb->esc_like($s) . '%';
    // Append meta keyword match into existing search group is complex; add OR on meta
    $where .= $wpdb->prepare(" OR (bankai_seo_kw.meta_value LIKE %s)", $like);
    return $where;
}
add_filter('posts_where', 'bankai_search_posts_where', 10, 2);

function bankai_search_posts_distinct($distinct, WP_Query $query) {
    if ($query->is_main_query() && $query->is_search() && ! is_admin()) {
        return 'DISTINCT';
    }
    return $distinct;
}
add_filter('posts_distinct', 'bankai_search_posts_distinct', 10, 2);

/**
 * Build keyword sitemap terms from posts, tags, categories, SEO keywords.
 *
 * @return string[]
 */
function bankai_search_sitemap_terms(int $limit = 20): array {
    $limit = max(1, min(50, $limit));
    $terms = [];

    $tags = get_terms(['taxonomy' => 'post_tag', 'number' => $limit, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC']);
    if (! is_wp_error($tags)) {
        foreach ($tags as $t) {
            $terms[] = $t->name;
        }
    }
    $cats = get_terms(['taxonomy' => 'category', 'number' => (int) ceil($limit / 2), 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC']);
    if (! is_wp_error($cats)) {
        foreach ($cats as $t) {
            if ($t->slug === 'uncategorized') {
                continue;
            }
            $terms[] = $t->name;
        }
    }

    $posts = get_posts([
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'fields' => 'ids',
    ]);
    foreach ($posts as $pid) {
        $kw = get_post_meta($pid, '_bankai_seo_keywords', true);
        if (! $kw) {
            $kw = get_post_meta($pid, 'bankai_seo_keywords', true);
        }
        if (is_string($kw) && $kw !== '') {
            foreach (preg_split('/[,،|]+/u', $kw) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $terms[] = $part;
                }
            }
        }
        $title = get_the_title($pid);
        if (is_string($title) && mb_strlen($title) > 3) {
            // take first 4 words as phrase suggestion
            $words = preg_split('/\s+/u', $title);
            if ($words) {
                $terms[] = implode(' ', array_slice($words, 0, min(4, count($words))));
            }
        }
    }

    $terms = array_values(array_unique(array_filter(array_map('trim', $terms))));
    return array_slice($terms, 0, $limit);
}

/**
 * AJAX: infinite scroll page load.
 */
function bankai_ajax_load_posts(): void {
    $page = max(1, absint($_GET['page'] ?? $_POST['page'] ?? 1));
    $context = sanitize_key($_GET['context'] ?? $_POST['context'] ?? 'blog');
    $search = sanitize_text_field(wp_unslash($_GET['s'] ?? $_POST['s'] ?? ''));

    $args = [
        'post_type' => $context === 'search' ? ['post', 'page'] : 'post',
        'post_status' => 'publish',
        'paged' => $page,
        'ignore_sticky_posts' => true,
    ];
    if ($context === 'search') {
        $args['s'] = $search;
        $n = (int) get_theme_mod('search_posts_per_page', 0);
        if ($n < 1) {
            $n = (int) get_theme_mod('blog_posts_per_page', (int) get_option('posts_per_page', 10));
        }
        $args['posts_per_page'] = max(1, min(48, $n));
        $card = (string) get_theme_mod('search_card_style', get_theme_mod('blog_card_style', 'default'));
    } else {
        $n = (int) get_theme_mod('blog_posts_per_page', (int) get_option('posts_per_page', 10));
        $args['posts_per_page'] = max(1, min(48, $n));
        $card = (string) get_theme_mod('blog_card_style', 'default');
    }

    $q = new WP_Query($args);
    ob_start();
    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            if (function_exists('bankai_render_post_card')) {
                bankai_render_post_card($card);
            } else {
                get_template_part('template-parts/content', get_post_type());
            }
        }
        wp_reset_postdata();
    }
    $html = ob_get_clean();
    wp_send_json_success([
        'html' => $html,
        'page' => $page,
        'max' => (int) $q->max_num_pages,
        'has_more' => $page < (int) $q->max_num_pages,
    ]);
}
add_action('wp_ajax_bankai_load_posts', 'bankai_ajax_load_posts');
add_action('wp_ajax_nopriv_bankai_load_posts', 'bankai_ajax_load_posts');

/**
 * Container CSS class based on layout setting.
 */
function bankai_container_class(): string {
    $width = bankai_theme_mod('container_width', '1280');
    return 'bankai-container bankai-container--' . sanitize_html_class((string) $width);
}

/**
 * Whether sidebar should show on current view.
 */
function bankai_show_sidebar(): bool {
    if (is_page_template('templates/full-width.php')) {
        return false;
    }
    // Per-view sidebar setting
    if (is_singular('post')) {
        $side = (string) bankai_theme_mod('page_single_sidebar', 'default');
    } elseif (is_search()) {
        $side = (string) bankai_theme_mod('page_search_sidebar', 'default');
    } elseif (is_home() || is_archive()) {
        $side = (string) bankai_theme_mod('page_blog_sidebar', 'default');
    } else {
        $side = 'default';
    }
    if ($side === 'none') {
        return false;
    }
    if (in_array($side, ['left', 'right'], true)) {
        return true; // theme sidebar widgets always available structure
    }
    $layout = bankai_theme_mod('content_layout', 'content');
    if ($layout === 'content') {
        return false;
    }
    if (is_singular('post') || is_home() || is_archive() || is_search()) {
        return true;
    }
    return false;
}

function bankai_sidebar_position(): string {
    if (is_singular('post')) {
        $side = (string) bankai_theme_mod('page_single_sidebar', 'default');
    } elseif (is_search()) {
        $side = (string) bankai_theme_mod('page_search_sidebar', 'default');
    } elseif (is_home() || is_archive()) {
        $side = (string) bankai_theme_mod('page_blog_sidebar', 'default');
    } else {
        $side = 'default';
    }
    if ($side === 'left') {
        return 'left';
    }
    if ($side === 'right') {
        return 'right';
    }
    $layout = (string) bankai_theme_mod('content_layout', 'content');
    return $layout === 'sidebar-content' ? 'left' : 'right';
}


/**
 * Truncate string by characters (UTF-8 safe).
 */
function bankai_truncate(string $text, int $chars): string {
    $text = wp_strip_all_tags($text);
    if ($chars < 1 || mb_strlen($text) <= $chars) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $chars)) . '…';
}

/**
 * Render a Flowbite-inspired post card for archives.
 */
function bankai_render_post_card(?string $force_style = null): void {
    $is_search = function_exists('is_search') && is_search();
    $style = $force_style !== null ? $force_style : (string) bankai_theme_mod($is_search ? 'search_card_style' : 'blog_card_style', bankai_theme_mod('blog_card_style', 'default'));
    $title_chars = (int) bankai_theme_mod('blog_title_chars', 80);
    $excerpt_chars = (int) bankai_theme_mod('blog_excerpt_chars', 140);
    if ($is_search) {
        $show_author = (bool) bankai_theme_mod('search_show_author', bankai_theme_mod('blog_show_author', true));
        $show_date = (bool) bankai_theme_mod('search_show_date', bankai_theme_mod('blog_show_date', true));
        $show_cats = (bool) bankai_theme_mod('search_show_cats', bankai_theme_mod('blog_show_cats', true));
        $show_excerpt = (bool) bankai_theme_mod('search_show_excerpt', bankai_theme_mod('blog_show_excerpt', true));
    } else {
        $show_author = (bool) bankai_theme_mod('blog_show_author', true);
        $show_date = (bool) bankai_theme_mod('blog_show_date', true);
        $show_cats = (bool) bankai_theme_mod('blog_show_cats', true);
        $show_excerpt = (bool) bankai_theme_mod('blog_show_excerpt', true);
    }
    $title = bankai_truncate(get_the_title(), $title_chars);
    $excerpt = bankai_truncate(get_the_excerpt(), $excerpt_chars);
    $classes = 'bankai-post-card bankai-post-card--' . sanitize_html_class($style);
    ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class($classes); ?>>
        <?php if ($style !== 'minimal' && has_post_thumbnail()) : ?>
            <a class="bankai-post-card__media" href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('medium_large'); ?>
            </a>
        <?php endif; ?>
        <div class="bankai-post-card__body">
            <?php if ($show_cats) : ?>
                <div class="bankai-post-card__cats"><?php the_category(' '); ?></div>
            <?php endif; ?>
            <h2 class="bankai-post-card__title">
                <a href="<?php the_permalink(); ?>"><?php echo esc_html($title); ?></a>
            </h2>
            <?php if ($show_excerpt && $excerpt) : ?>
                <p class="bankai-post-card__excerpt"><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>
            <div class="bankai-post-card__meta">
                <?php if ($show_author) : ?>
                    <span class="bankai-post-card__author"><?php the_author(); ?></span>
                <?php endif; ?>
                <?php if ($show_date) : ?>
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}


/**
 * Chat-style comment callback (Flowbite-inspired).
 *
 * @param WP_Comment $comment Comment object.
 * @param array      $args    Args.
 * @param int        $depth   Depth.
 */
function bankai_comment_chat_callback($comment, array $args, int $depth): void {
    $tag = ('div' === $args['style']) ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class('bankai-chat__item', $comment); ?>>
        <article class="bankai-chat__bubble" itemscope itemtype="https://schema.org/Comment">
            <div class="bankai-chat__avatar">
                <?php echo get_avatar($comment, $args['avatar_size'], '', get_comment_author($comment), ['class' => 'bankai-chat__avatar-img']); ?>
            </div>
            <div class="bankai-chat__body">
                <div class="bankai-chat__head">
                    <span class="bankai-chat__author" itemprop="author"><?php echo esc_html(get_comment_author($comment)); ?></span>
                    <time class="bankai-chat__time" datetime="<?php echo esc_attr(get_comment_date('c', $comment)); ?>" itemprop="datePublished">
                        <?php echo esc_html(get_comment_date('', $comment) . ' · ' . get_comment_time('')); ?>
                    </time>
                </div>
                <div class="bankai-chat__text" itemprop="text">
                    <?php if ($comment->comment_approved === '0') : ?>
                        <em class="bankai-chat__awaiting"><?php esc_html_e('در انتظار تأیید', 'bankai-theme'); ?></em>
                    <?php endif; ?>
                    <?php comment_text(); ?>
                </div>
                <div class="bankai-chat__actions">
                    <?php
                    comment_reply_link(array_merge($args, [
                        'depth'     => $depth,
                        'max_depth' => $args['max_depth'],
                        'before'    => '<span class="bankai-chat__reply">',
                        'after'     => '</span>',
                    ]));
                    ?>
                </div>
            </div>
        </article>
    <?php
}


/**
 * Resolve top padding under transparent/sticky header for current view.
 */
function bankai_page_top_offset(): int {
    $key = 'home';
    if (is_singular('post')) {
        $key = 'single';
    } elseif (is_home() || is_archive()) {
        $key = 'blog';
    } elseif (is_search()) {
        $key = 'search';
    } elseif (is_404()) {
        $key = '404';
    } elseif (is_front_page()) {
        $key = 'home';
    }
    $mode = (string) get_theme_mod("page_{$key}_top_mode", 'offset');
    if ($mode === 'flush') {
        return 0;
    }
    return max(0, min(200, (int) get_theme_mod("page_{$key}_top_offset", 80)));
}
