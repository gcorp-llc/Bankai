<?php
/**
 * Main template (blog / home)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="bankai-main site-main">
	<?php
$side_pos = function_exists('bankai_sidebar_position') ? bankai_sidebar_position() : 'right';
$show_side = function_exists('bankai_show_sidebar') && bankai_show_sidebar();
$layout_class = $show_side ? 'bankai-layout bankai-layout--sidebar-' . esc_attr($side_pos) : 'bankai-layout bankai-layout--full';
?>
	<div class="<?php echo esc_attr(bankai_container_class() . ' ' . $layout_class); ?>">
		<div class="bankai-content">
			<?php if (is_home() && ! is_front_page()) : ?>
				<header class="bankai-archive-header">
					<h1 class="bankai-archive-title"><?php single_post_title(); ?></h1>
				</header>
			<?php endif; ?>

			<?php if (have_posts()) : ?>
				<div class="bankai-posts-grid bankai-posts-grid--<?php echo esc_attr((string) bankai_theme_mod('blog_card_style', 'default')); ?>" data-pagination="<?php echo esc_attr((string) bankai_theme_mod('blog_pagination', 'numbers')); ?>" data-context="blog" data-page="1" data-max="<?php echo esc_attr((string) $GLOBALS['wp_query']->max_num_pages); ?>">
					<?php
					while (have_posts()) :
						the_post();
						if (function_exists('bankai_render_post_card')) {
							bankai_render_post_card();
						} else {
							get_template_part('template-parts/content', get_post_type());
						}
					endwhile;
					?>
				</div>
				<?php
				$pag = (string) bankai_theme_mod('blog_pagination', 'numbers');
				if ($pag === 'infinite') {
					echo '<div class="bankai-infinite-status" data-infinite-status hidden><span class="bankai-infinite-status__spin" aria-hidden="true"></span><span>' . esc_html__('در حال بارگذاری…', 'bankai-theme') . '</span></div>';
					echo '<div class="bankai-infinite-end" data-infinite-end hidden>' . esc_html__('همه مطالب نمایش داده شد', 'bankai-theme') . '</div>';
				} elseif ($pag === 'prevnext') {
					the_posts_navigation([
						'prev_text' => __('قبلی', 'bankai-theme'),
						'next_text' => __('بعدی', 'bankai-theme'),
					]);
				} else {
					the_posts_pagination([
						'mid_size'  => 1,
						'prev_text' => __('قبلی', 'bankai-theme'),
						'next_text' => __('بعدی', 'bankai-theme'),
					]);
				}
				?>
			<?php else : ?>
				<?php get_template_part('template-parts/content', 'none'); ?>
			<?php endif; ?>
		</div>

		<?php if (bankai_show_sidebar()) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
