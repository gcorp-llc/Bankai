<?php
/**
 * Footer — builder rows + fixed GCORP credit
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?>
	</div><!-- #content -->

	<?php
	if (function_exists('bankai_footer_builder_render')) {
		bankai_footer_builder_render();
	}
	?>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
