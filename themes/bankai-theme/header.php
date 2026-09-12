<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header id="masthead" class="site-header" style="padding: 20px 0; border-bottom: 1px solid #eee;">
	<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-space-between; align-items: center;">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<h1 class="site-title" style="margin: 0; font-size: 24px;"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<?php endif; ?>
		</div>
		<nav id="site-navigation" class="main-navigation">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>
</header>
<main id="primary" class="site-main" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
