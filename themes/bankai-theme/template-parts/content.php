<?php
/**
 * Post card in loops
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('bankai-card'); ?>>
	<?php if (has_post_thumbnail()) : ?>
		<a class="bankai-card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail('bankai-card', ['loading' => 'lazy', 'decoding' => 'async']); ?>
		</a>
	<?php endif; ?>
	<div class="bankai-card__body">
		<header class="bankai-card__header">
			<?php the_title(sprintf('<h2 class="bankai-card__title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>'); ?>
			<p class="bankai-meta">
				<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
				<?php if (has_category()) : ?>
					<span class="bankai-meta-sep">·</span>
					<?php the_category(', '); ?>
				<?php endif; ?>
			</p>
		</header>
		<div class="bankai-card__excerpt">
			<?php the_excerpt(); ?>
		</div>
		<a class="bankai-card__more" href="<?php the_permalink(); ?>"><?php esc_html_e('ادامه مطلب', 'bankai-theme'); ?></a>
	</div>
</article>
