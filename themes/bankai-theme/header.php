<?php
/**
 * Header
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

$scheme = bankai_theme_mod('color_scheme', 'system');
?><!doctype html>
<html <?php language_attributes(); ?> data-theme="<?php echo esc_attr($scheme === 'dark' ? 'dark' : ($scheme === 'light' ? 'light' : 'system')); ?>">
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<script>
	(function(){try{
		var s=document.documentElement.getAttribute('data-theme')||'system';
		var saved=localStorage.getItem('bankai-theme-scheme');
		if(saved==='light'||saved==='dark'){s=saved;}
		else if(s==='system'){s=window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light';}
		document.documentElement.setAttribute('data-theme',s);
	}catch(e){}})();
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="bankai-site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('پرش به محتوا', 'bankai-theme'); ?></a>

<?php
if (function_exists('bankai_header_render')) {
	bankai_header_render();
} else {
	// Fallback minimal header
	?>
	<header id="masthead" class="bankai-header is-sticky">
		<div class="<?php echo esc_attr(bankai_container_class()); ?> bankai-header__inner">
			<div class="bankai-brand">
				<?php if (has_custom_logo()) { the_custom_logo(); } else { ?>
					<a class="bankai-site-title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
				<?php } ?>
			</div>
			<nav class="bankai-nav">
				<?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'bankai-menu', 'fallback_cb' => 'bankai_fallback_menu', 'depth' => 2]); ?>
			</nav>
		</div>
	</header>
	<?php
}

if (! function_exists('bankai_fallback_menu')) {
	function bankai_fallback_menu(): void {
		echo '<ul class="bankai-menu"><li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('صفحه نخست', 'bankai-theme') . '</a></li></ul>';
	}
}
?>
	<div id="content" class="bankai-site-content">
