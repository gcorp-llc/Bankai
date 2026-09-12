<?php
get_header();
?>

<header class="archive-header" style="margin-bottom: 40px;">
	<h1 class="archive-title"><?php the_archive_title(); ?></h1>
</header>

<div class="posts-wrapper">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom: 30px;">
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>
	<?php endif; ?>
</div>

<?php
get_footer();
