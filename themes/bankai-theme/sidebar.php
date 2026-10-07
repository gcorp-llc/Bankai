<?php
/**
 * Sidebar — search, categories, recent
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?>
<aside id="secondary" class="bankai-sidebar widget-area" role="complementary" aria-label="<?php esc_attr_e('نوار کناری', 'bankai-theme'); ?>">
	<div class="bankai-sidebar__inner">
		<section class="bankai-sidebar__block bankai-sidebar__search">
			<h2 class="bankai-sidebar__heading screen-reader-text"><?php esc_html_e('جستجو', 'bankai-theme'); ?></h2>
			<?php get_search_form(); ?>
		</section>

		<?php if (is_active_sidebar('sidebar-1')) : ?>
			<?php dynamic_sidebar('sidebar-1'); ?>
		<?php else : ?>
			<section class="bankai-sidebar__block">
				<h2 class="bankai-sidebar__heading"><?php esc_html_e('دسته‌بندی‌ها', 'bankai-theme'); ?></h2>
				<ul class="bankai-sidebar__cats">
					<?php
					wp_list_categories([
						'title_li' => '',
						'show_count' => true,
						'number' => 12,
					]);
					?>
				</ul>
			</section>
			<section class="bankai-sidebar__block">
				<h2 class="bankai-sidebar__heading"><?php esc_html_e('آخرین مطالب', 'bankai-theme'); ?></h2>
				<ul class="bankai-sidebar__recent">
					<?php
					$recent = wp_get_recent_posts(['numberposts' => 5, 'post_status' => 'publish'], OBJECT);
					foreach ($recent as $r) :
						?>
						<li>
							<a href="<?php echo esc_url(get_permalink($r)); ?>">
								<?php if (has_post_thumbnail($r)) : ?>
									<span class="bankai-sidebar__thumb"><?php echo get_the_post_thumbnail($r, 'thumbnail'); ?></span>
								<?php endif; ?>
								<span class="bankai-sidebar__rtitle"><?php echo esc_html(get_the_title($r)); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>
	</div>
</aside>
