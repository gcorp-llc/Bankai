<?php
/**
 * Template Name: Elementor تمام‌عرض (با هدر/فوتر)
 * Template Post Type: page, post
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="bankai-main bankai-elementor-full">
	<?php
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
	?>
</main>

<?php
get_footer();
