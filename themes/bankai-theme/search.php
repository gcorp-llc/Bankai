<?php
/**
 * Search results — empty state, blog-like cards, infinite scroll
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

$q = get_search_query();
$has_query = $q !== '';
$card_style = (string) bankai_theme_mod('search_card_style', bankai_theme_mod('blog_card_style', 'default'));
$pagination = (string) bankai_theme_mod('search_pagination', bankai_theme_mod('blog_pagination', 'numbers'));
$side_pos = function_exists('bankai_sidebar_position') ? bankai_sidebar_position() : 'right';
$show_side = function_exists('bankai_show_sidebar') && bankai_show_sidebar();
$layout_class = $show_side ? 'bankai-layout bankai-layout--sidebar-' . esc_attr($side_pos) : 'bankai-layout bankai-layout--full';

get_header();
?>

<main id="primary" class="bankai-main site-main bankai-search-page">
	<div class="<?php echo esc_attr(bankai_container_class() . ' ' . $layout_class); ?>">
		<div class="bankai-content">
			<?php if (! $has_query) : ?>
				<section class="bankai-search-empty">
					<div class="bankai-search-empty__box">
						<div class="bankai-search-empty__icon" aria-hidden="true">
							<?php
							if (function_exists('bankai_theme_icon')) {
								echo bankai_theme_icon('search', ['size' => 40]);
							}
							?>
						</div>
						<h1 class="bankai-search-empty__title"><?php esc_html_e('جستجو در سایت', 'bankai-theme'); ?></h1>
						<p class="bankai-search-empty__desc"><?php esc_html_e('عبارت مورد نظر را وارد کنید تا مقالات و صفحات مرتبط نمایش داده شوند.', 'bankai-theme'); ?></p>
						<?php get_search_form(); ?>
						<?php
						$suggestions = function_exists('bankai_search_sitemap_terms') ? bankai_search_sitemap_terms(12) : [];
						if ($suggestions) :
							?>
							<div class="bankai-search-suggest">
								<span class="bankai-search-suggest__label"><?php esc_html_e('پیشنهادها', 'bankai-theme'); ?></span>
								<div class="bankai-search-suggest__list">
									<?php foreach ($suggestions as $term) : ?>
										<a class="bankai-search-suggest__item" href="<?php echo esc_url(home_url('/?s=' . rawurlencode($term))); ?>">
											<?php echo esc_html($term); ?>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</section>
			<?php else : ?>
				<header class="bankai-archive-header bankai-search-header">
					<h1 class="bankai-archive-title">
						<?php
						printf(
							/* translators: %s: search query */
							esc_html__('نتایج جستجو برای: %s', 'bankai-theme'),
							'<span>' . esc_html($q) . '</span>'
						);
						?>
					</h1>
					<div class="bankai-search-header__form">
						<?php get_search_form(); ?>
					</div>
				</header>

				<?php if (have_posts()) : ?>
					<div
						class="bankai-posts-grid bankai-posts-grid--<?php echo esc_attr($card_style); ?>"
						data-pagination="<?php echo esc_attr($pagination); ?>"
						data-context="search"
						data-search="<?php echo esc_attr($q); ?>"
						data-page="1"
						data-max="<?php echo esc_attr((string) $GLOBALS['wp_query']->max_num_pages); ?>"
					>
						<?php
						while (have_posts()) :
							the_post();
							if (function_exists('bankai_render_post_card')) {
								// Use search card style when rendering
								bankai_render_post_card($card_style);
							} else {
								get_template_part('template-parts/content', get_post_type());
							}
						endwhile;
						?>
					</div>
					<?php if ($pagination === 'infinite') : ?>
						<div class="bankai-infinite-status" data-infinite-status hidden>
							<span class="bankai-infinite-status__spin" aria-hidden="true"></span>
							<span><?php esc_html_e('در حال بارگذاری…', 'bankai-theme'); ?></span>
						</div>
						<div class="bankai-infinite-end" data-infinite-end hidden>
							<?php esc_html_e('همه نتایج نمایش داده شد', 'bankai-theme'); ?>
						</div>
					<?php elseif ($pagination === 'prevnext') : ?>
						<?php
						the_posts_navigation([
							'prev_text' => __('قبلی', 'bankai-theme'),
							'next_text' => __('بعدی', 'bankai-theme'),
						]);
						?>
					<?php else : ?>
						<?php
						the_posts_pagination([
							'mid_size'  => 1,
							'prev_text' => __('قبلی', 'bankai-theme'),
							'next_text' => __('بعدی', 'bankai-theme'),
						]);
						?>
					<?php endif; ?>
				<?php else : ?>
					<section class="bankai-search-none" role="alert">
						<div class="bankai-search-none__icon" aria-hidden="true">
							<?php
							if (function_exists('bankai_theme_icon')) {
								echo bankai_theme_icon('search-x', ['size' => 36]);
							}
							?>
						</div>
						<h2><?php esc_html_e('نتیجه‌ای یافت نشد', 'bankai-theme'); ?></h2>
						<p>
							<?php
							printf(
								/* translators: %s: search query */
								esc_html__('برای «%s» مطلبی پیدا نشد. عبارت دیگری را امتحان کنید یا از پیشنهادها استفاده کنید.', 'bankai-theme'),
								esc_html($q)
							);
							?>
						</p>
						<?php get_search_form(); ?>
						<?php
						$suggestions = function_exists('bankai_search_sitemap_terms') ? bankai_search_sitemap_terms(10) : [];
						if ($suggestions) :
							?>
							<div class="bankai-search-suggest">
								<span class="bankai-search-suggest__label"><?php esc_html_e('شاید منظورتان این باشد', 'bankai-theme'); ?></span>
								<div class="bankai-search-suggest__list">
									<?php foreach ($suggestions as $term) : ?>
										<a class="bankai-search-suggest__item" href="<?php echo esc_url(home_url('/?s=' . rawurlencode($term))); ?>">
											<?php echo esc_html($term); ?>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</section>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php if ($show_side) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
