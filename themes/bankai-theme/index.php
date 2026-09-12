<?php
get_header();
?>

<div class="posts-wrapper">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'bankai-card' ); ?> style="margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid #f0f0f0;">
				<h2 style="margin-top: 0;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry-excerpt">
					<?php the_excerpt(); ?>
				</div>
			</article>
		<?php endwhile; ?>
		<?php the_posts_navigation(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'محتوایی یافت نشد.', 'bankai-theme' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
