<?php
/**
 * Template Name: Elementor Canvas
 * Template Post Type: page, post
 * Description: بدون هدر و فوتر قالب — مناسب لندینگ Elementor
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class('bankai-elementor-canvas'); ?>>
<?php wp_body_open(); ?>
<main id="primary" class="bankai-main bankai-canvas">
	<?php
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php wp_footer(); ?>
</body>
</html>
