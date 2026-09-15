<?php
/**
 * The template for displaying single posts and pages
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="bankai-content site-main">
    <?php
    while (have_posts()) : the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="max-width: 800px; margin: 0 auto; background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 40px;">
            <header class="entry-header" style="margin-bottom: 24px; border-bottom: 1px solid #EAEEF2; padding-bottom: 20px;">
                <?php the_title('<h1 class="entry-title" style="margin: 0; font-size: 28px; font-weight: 800; color: #1F2328;">', '</h1>'); ?>
                <div class="entry-meta" style="margin-top: 10px; font-size: 13px; color: #656D76;">
                    <?php echo esc_html(get_the_date()); ?> &bull; <?php echo esc_html(get_the_author()); ?>
                </div>
            </header>

            <div class="entry-content" style="font-size: 16px; line-height: 1.8; color: #1F2328;">
                <?php the_content(); ?>
            </div>
        </article>
        <?php
    endwhile;
    ?>
</main>

<?php
get_footer();
