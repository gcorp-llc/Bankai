<?php
/**
 * The template for displaying the footer
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?>

    <footer id="colophon" class="bankai-footer">
        <div class="bankai-site-container">
            <div class="bankai-footer-info">
                <p>
                    &copy; <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?>.
                    <?php esc_html_e('طراحی‌شده با قالب فوق‌سریع Bankai Theme (بر پایه معماری Bankai Framework)', 'bankai-theme'); ?>
                </p>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
