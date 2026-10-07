<?php
/**
 * Comments — chat-bubble style (theme tokens)
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

if (post_password_required()) {
	return;
}
?>
<section id="comments" class="bankai-comments">
	<?php if (have_comments()) : ?>
		<h2 class="bankai-comments__title">
			<?php
			$n = get_comments_number();
			printf(
				esc_html(_n('%s دیدگاه', '%s دیدگاه', $n, 'bankai-theme')),
				esc_html(number_format_i18n($n))
			);
			?>
		</h2>

		<div class="bankai-chat">
			<?php
			wp_list_comments([
				'style'       => 'div',
				'short_ping'  => true,
				'avatar_size' => 32,
				'callback'    => 'bankai_comment_chat_callback',
			]);
			?>
		</div>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if (! comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
		<p class="no-comments"><?php esc_html_e('دیدگاه‌ها بسته شده‌اند.', 'bankai-theme'); ?></p>
	<?php endif; ?>

	<div class="bankai-comment-form-wrap">
		<?php
		comment_form([
			'title_reply'          => __('ارسال دیدگاه', 'bankai-theme'),
			'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
			'title_reply_after'    => '</h3>',
			'class_form'           => 'bankai-comment-form',
			'class_submit'         => 'bankai-comment-submit',
			'label_submit'         => __('ارسال', 'bankai-theme'),
			'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . esc_html__('پیام شما', 'bankai-theme') . '</label><textarea id="comment" name="comment" cols="45" rows="5" required></textarea></p>',
		]);
		?>
	</div>
</section>
