<?php
/**
 * Page content
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('bankai-page'); ?>>
	<?php if (has_post_thumbnail()) : ?>
		<figure class="bankai-page__thumb">
			<?php the_post_thumbnail('large', ['loading' => 'eager']); ?>
		</figure>
	<?php endif; ?>

	<div class="bankai-entry-content entry-content">
		<?php
		the_content();
		wp_link_pages([
			'before' => '<nav class="page-links">',
			'after'  => '</nav>',
		]);
		?>
	</div>
</article>
