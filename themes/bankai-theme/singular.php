<?php
/**
 * Singular (post/page)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

get_header();

$side_pos = function_exists('bankai_sidebar_position') ? bankai_sidebar_position() : 'right';
$show_side = function_exists('bankai_show_sidebar') && bankai_show_sidebar();
$layout_class = $show_side ? 'bankai-layout bankai-layout--sidebar-' . esc_attr($side_pos) : 'bankai-layout bankai-layout--full';
?>

<main id="primary" class="bankai-main site-main">
	<div class="<?php echo esc_attr(bankai_container_class() . ' ' . $layout_class); ?>">
		<?php if ($show_side && $side_pos === 'left') : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>

		<div class="bankai-content">
			<?php
			while (have_posts()) :
				the_post();
				get_template_part('template-parts/content', is_page() ? 'page' : 'single');

				if (is_singular('post')) {
					$show_comments = function_exists('bankai_theme_mod') ? bankai_theme_mod('single_show_comments', true) : true;
					if ($show_comments && (comments_open() || get_comments_number())) {
						comments_template();
					}
				}
			endwhile;
			?>
		</div>

		<?php if ($show_side && $side_pos !== 'left') : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
