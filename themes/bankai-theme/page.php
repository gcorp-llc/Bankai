<?php
get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header" style="margin-bottom: 30px;">
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</header>

		<div class="entry-content" style="line-height: 1.8;">
			<?php the_content(); ?>
		</div>
	</article>
<?php endwhile; ?>

<?php
get_footer();
