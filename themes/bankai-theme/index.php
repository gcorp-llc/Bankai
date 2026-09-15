<?php
/**
 * The main template file
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="bankai-content site-main">
    <div class="bankai-hero">
        <h1><?php bloginfo('name'); ?></h1>
        <p><?php bloginfo('description'); ?></p>
    </div>

    <div class="bankai-posts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 10px; overflow: hidden; padding: 24px;">
                    <header class="entry-header">
                        <?php the_title('<h2 class="entry-title" style="margin-top: 0; font-size: 18px;"><a href="' . esc_url(get_permalink()) . '" rel="bookmark" style="text-decoration: none; color: #1F2328;">', '</a></h2>'); ?>
                    </header>
                    <div class="entry-summary" style="color: #656D76; font-size: 14px; margin-top: 10px;">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
                <?php
            endwhile;
        else :
            ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <p><?php esc_html_e('هیچ نوشته‌ای یافت نشد.', 'bankai-theme'); ?></p>
            </div>
            <?php
        endif;
        ?>
    </div>
</main>

<?php
get_footer();
