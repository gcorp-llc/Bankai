<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="bankai-content site-main" style="text-align: center; padding: 80px 20px;">
    <h1 style="font-size: 72px; font-weight: 800; color: #0969DA; margin: 0;">404</h1>
    <h2 style="font-size: 24px; font-weight: 700; margin: 16px 0;"><?php esc_html_e('برگه مورد نظر پیدا نشد', 'bankai-theme'); ?></h2>
    <p style="color: #656D76; max-width: 500px; margin: 0 auto 24px auto;">
        <?php esc_html_e('صفحه‌ای که به دنبال آن بودید ممکن است حذف شده باشد، یا نام آن تغییر کرده باشد.', 'bankai-theme'); ?>
    </p>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="button button-primary" style="background: #0969DA; color: #FFF; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; display: inline-block;">
        <?php esc_html_e('بازگشت به صفحه اصلی', 'bankai-theme'); ?>
    </a>
</main>

<?php
get_footer();
