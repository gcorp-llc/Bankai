<?php
/**
 * No results
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?>
<section class="bankai-no-results">
	<h1><?php esc_html_e('موردی یافت نشد', 'bankai-theme'); ?></h1>
	<p><?php esc_html_e('چیزی مطابق درخواست شما پیدا نشد. جستجو را امتحان کنید.', 'bankai-theme'); ?></p>
	<?php get_search_form(); ?>
</section>
