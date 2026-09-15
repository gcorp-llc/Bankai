<?php
/**
 * The header for Bankai Theme
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(is_rtl() ? 'rtl' : 'ltr'); ?>>
<?php wp_body_open(); ?>

<div id="page" class="bankai-site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('پرش به محتوای اصلی', 'bankai-theme'); ?></a>

    <header id="masthead" class="bankai-header">
        <div class="bankai-header-inner">
            <div class="bankai-site-logo">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    echo '<a href="' . esc_url(home_url('/')) . '" rel="home">' . esc_html(get_bloginfo('name')) . '</a>';
                }
                ?>
            </div>

            <nav id="site-navigation" class="bankai-nav" aria-label="<?php esc_attr_e('منوی اصلی', 'bankai-theme'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'fallback_cb'    => function() {
                        echo '<ul>';
                        echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('صفحه نخست', 'bankai-theme') . '</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/blog')) . '">' . esc_html__('وبلاگ', 'bankai-theme') . '</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/contact')) . '">' . esc_html__('تماس با ما', 'bankai-theme') . '</a></li>';
                        echo '</ul>';
                    },
                ]);
                ?>
            </nav>
        </div>
    </header>
