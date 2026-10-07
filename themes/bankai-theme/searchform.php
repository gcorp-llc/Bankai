<?php
/**
 * Search form
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?>
<form role="search" method="get" class="bankai-search-form" action="<?php echo esc_url(home_url('/')); ?>">
	<label class="screen-reader-text" for="bankai-search-field"><?php esc_html_e('جستجو', 'bankai-theme'); ?></label>
	<input type="search" id="bankai-search-field" class="bankai-search-field" placeholder="<?php esc_attr_e('جستجو…', 'bankai-theme'); ?>" value="<?php echo esc_attr(get_search_query()); ?>" name="s" />
	<button type="submit" class="bankai-btn bankai-btn--primary"><?php esc_html_e('جستجو', 'bankai-theme'); ?></button>
</form>
