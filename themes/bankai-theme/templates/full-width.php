<?php
/**
 * Template Name: تمام‌عرض (بدون سایدبار)
 * Template Post Type: page, post
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="bankai-main site-main bankai-full-width">
	<div class="<?php echo esc_attr(bankai_container_class()); ?>">
		<?php
		while (have_posts()) :
			the_post();
			get_template_part('template-parts/content', is_page() ? 'page' : 'single');
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
