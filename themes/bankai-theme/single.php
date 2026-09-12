<?php
get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header" style="margin-bottom: 30px;">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-meta" style="color: #888; font-size: 14px;">
				<?php echo esc_html( get_the_date() ); ?> | <?php the_author(); ?>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-thumbnail" style="margin-bottom: 30px;">
				<?php the_post_thumbnail( 'large', array( 'style' => 'max-width: 100%; height: auto; border-radius: 8px;' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="entry-content" style="line-height: 1.8;">
			<?php the_content(); ?>
		</div>
	</article>
<?php endwhile; ?>

<?php
get_footer();
