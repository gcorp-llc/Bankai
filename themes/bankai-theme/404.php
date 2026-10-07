<?php
/**
 * 404 template — visual themes + latest posts + auto redirect
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

$bool = static function ($v): bool {
    return ! empty($v) && $v !== '0' && $v !== 'false' && $v !== false;
};

$show_nav     = $bool(get_theme_mod('page_404_show_navbar', true));
$show_mob     = $bool(get_theme_mod('page_404_show_mobile_bar', true));
$show_footer  = $bool(get_theme_mod('page_404_show_footer', true));
$show_latest  = $bool(get_theme_mod('page_404_show_latest', true));
$auto_redir   = $bool(get_theme_mod('page_404_auto_redirect', false));
$theme        = sanitize_key(get_theme_mod('page_404_theme', 'minimal'));
$allowed_themes = ['astronaut', 'cat', 'milk', 'construction', 'geometric', 'lost'];
// map legacy keys
$legacy = [
    'minimal' => 'lost',
    'soft' => 'geometric',
    'bold' => 'astronaut',
    'space' => 'astronaut',
    'glitch' => 'milk',
    'illustration' => 'cat',
];
if (isset($legacy[$theme])) {
    $theme = $legacy[$theme];
}
if (! in_array($theme, $allowed_themes, true)) {
    $theme = 'astronaut';
}
$message  = (string) get_theme_mod('page_404_message', __('صفحه پیدا نشد. ممکن است آدرس اشتباه باشد یا صفحه حذف شده باشد.', 'bankai-theme'));
$btn_text = (string) get_theme_mod('page_404_btn_text', __('بازگشت به خانه', 'bankai-theme'));
$btn_url  = (string) get_theme_mod('page_404_btn_url', home_url('/'));
if ($btn_url === '') {
    $btn_url = home_url('/');
}
$btn_style = sanitize_key(get_theme_mod('page_404_btn_style', 'primary'));
if (! in_array($btn_style, ['primary', 'outline', 'ghost'], true)) {
    $btn_style = 'primary';
}
$btn_icon = sanitize_title(get_theme_mod('page_404_btn_icon', 'home'));
$posts_mode   = sanitize_key(get_theme_mod('page_404_posts_mode', 'latest'));
$posts_layout = sanitize_key(get_theme_mod('page_404_posts_layout', 'grid'));
$posts_count  = max(2, min(12, absint(get_theme_mod('page_404_posts_count', 3))));
$posts_card   = sanitize_key(get_theme_mod('page_404_posts_card_style', 'default'));
if (in_array($posts_card, ['elevated', 'soft'], true)) { $posts_card = 'default'; }
$delay        = max(2, min(120, absint(get_theme_mod('page_404_redirect_delay', 8))));
$redir_type   = sanitize_key(get_theme_mod('page_404_redirect_type', 'custom'));
$redir_url    = (string) get_theme_mod('page_404_redirect_url', home_url('/'));

if ($auto_redir && $redir_type === 'random') {
    $rand = get_posts(['post_type' => 'post', 'posts_per_page' => 1, 'orderby' => 'rand', 'post_status' => 'publish']);
    if ($rand) {
        $redir_url = get_permalink($rand[0]);
    } else {
        $redir_url = home_url('/');
    }
}
if ($redir_url === '') {
    $redir_url = home_url('/');
}

$body_extra = 'bankai-404-page bankai-404-theme-' . $theme;
if (! $show_nav) {
    $body_extra .= ' bankai-404-hide-navbar';
}
if (! $show_mob) {
    $body_extra .= ' bankai-404-hide-mobilebar';
}
if (! $show_footer) {
    $body_extra .= ' bankai-404-hide-footer';
}

add_filter('body_class', static function ($classes) use ($body_extra) {
    foreach (explode(' ', $body_extra) as $c) {
        $classes[] = $c;
    }
    return $classes;
});

get_header();
?>

<main id="primary" class="bankai-main site-main bankai-404-main">
	<div class="<?php echo esc_attr(bankai_container_class()); ?>">
		<section class="bankai-error-404 bankai-error-404--<?php echo esc_attr($theme); ?>" <?php if ($auto_redir) : ?>data-redirect-url="<?php echo esc_url($redir_url); ?>" data-redirect-delay="<?php echo esc_attr((string) $delay); ?>"<?php endif; ?>>
			<div class="bankai-404-hero">
				<div class="bankai-404-visual bankai-404-visual--<?php echo esc_attr($theme); ?>" aria-hidden="true">
					<?php if ($theme === 'astronaut') : ?>
						<div class="b404-astro">
							<div class="b404-astro__bg"></div>
							<span class="bankai-404-code">404</span>
							<svg class="b404-astro__ship" viewBox="0 0 120 120" width="120" height="120">
								<ellipse cx="60" cy="70" rx="28" ry="36" fill="#e8eef5"/>
								<ellipse cx="60" cy="58" rx="18" ry="16" fill="#7dd3fc"/>
								<rect x="48" y="88" width="8" height="16" rx="2" fill="#94a3b8"/>
								<rect x="64" y="88" width="8" height="16" rx="2" fill="#94a3b8"/>
								<circle cx="60" cy="56" r="6" fill="#0ea5e9" opacity=".5"/>
							</svg>
						</div>
					<?php elseif ($theme === 'cat') : ?>
						<div class="b404-cat">
							<span class="bankai-404-code">404</span>
							<svg class="b404-cat__svg" viewBox="0 0 200 140" width="200" height="140">
								<path d="M40 100c0-30 20-50 60-50s60 20 60 50v10H40z" fill="#fbbf24"/>
								<path d="M60 50l-15-28 25 18M140 50l15-28-25 18" fill="#fbbf24"/>
								<circle cx="85" cy="85" r="6" fill="#1e293b"/>
								<circle cx="125" cy="85" r="6" fill="#1e293b"/>
								<path d="M95 100c5 8 15 8 20 0" stroke="#1e293b" stroke-width="3" fill="none" stroke-linecap="round"/>
								<path d="M100 90h10" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
								<ellipse class="b404-cat__tail" cx="165" cy="95" rx="18" ry="8" fill="#f59e0b"/>
							</svg>
						</div>
					<?php elseif ($theme === 'milk') : ?>
						<div class="b404-milk">
							<svg class="b404-milk__carton" viewBox="0 0 200 240" width="140" height="168">
								<path fill="#f1fbfe" d="M70 220V60l80-10v160z"/>
								<path fill="#c2e8f8" d="M40 45l30 15v160l-30-25z"/>
								<path fill="#e4f5f9" d="M70 60L55 25l80-8 20 43z"/>
								<path fill="#5f4b8b" d="M95 130h40v50H95z"/>
								<text x="115" y="162" text-anchor="middle" fill="#fff" font-size="22" font-weight="700" font-family="system-ui">404</text>
							</svg>
							<svg class="b404-milk__spill" viewBox="0 0 200 40" width="180" height="36">
								<path fill="rgba(255,255,255,.85)" d="M10 20c20-12 40-8 60-6s50 4 70-2 40-4 50 6c-30 8-60 10-90 8S30 28 10 20z"/>
							</svg>
						</div>
					<?php elseif ($theme === 'construction') : ?>
						<div class="b404-build">
							<span class="bankai-404-code">404</span>
							<div class="b404-build__bar">
								<span></span><span></span><span></span><span></span>
							</div>
							<svg class="b404-build__cone" viewBox="0 0 80 90" width="72" height="80">
								<path d="M40 8L8 78h64z" fill="#f59e0b"/>
								<path d="M18 55h44" stroke="#fff" stroke-width="8"/>
								<path d="M22 40h36" stroke="#fff" stroke-width="8"/>
								<rect x="28" y="78" width="24" height="8" fill="#334155"/>
							</svg>
						</div>
					<?php elseif ($theme === 'geometric') : ?>
						<div class="b404-geo">
							<span class="b404-geo__shape b404-geo__shape--a"></span>
							<span class="b404-geo__shape b404-geo__shape--b"></span>
							<span class="b404-geo__shape b404-geo__shape--c"></span>
							<span class="bankai-404-code">404</span>
						</div>
					<?php else : /* lost */ ?>
						<div class="b404-lost">
							<div class="b404-lost__scene">
								<span class="bankai-404-code">404</span>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<h1 class="bankai-404-title"><?php esc_html_e('صفحه پیدا نشد', 'bankai-theme'); ?></h1>
				<p class="bankai-404-message"><?php echo esc_html($message); ?></p>

				<div class="bankai-404-actions">
					<a class="bankai-btn bankai-btn--<?php echo esc_attr($btn_style); ?> bankai-404-btn" href="<?php echo esc_url($btn_url); ?>">
						<?php
						if ($btn_icon && function_exists('bankai_header_icon_svg')) {
							echo bankai_header_icon_svg($btn_icon, 18);
						} elseif ($btn_icon && function_exists('bankai_theme_icon')) {
							echo bankai_theme_icon($btn_icon, ['size' => 18]);
						}
						?>
						<span><?php echo esc_html($btn_text); ?></span>
					</a>
				</div>

				<?php if ($auto_redir) : ?>
					<p class="bankai-404-countdown" data-404-countdown>
						<?php
						printf(
							/* translators: %s: seconds */
							esc_html__('انتقال خودکار تا %s ثانیه…', 'bankai-theme'),
							'<strong data-404-seconds>' . esc_html((string) $delay) . '</strong>'
						);
						?>
					</p>
				<?php endif; ?>

				<div class="bankai-error-search">
					<?php get_search_form(); ?>
				</div>
			</div>

			<?php if ($show_latest) : ?>
				<?php
				$q_args = [
					'post_type'           => 'post',
					'posts_per_page'      => $posts_count,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
				];
				if ($posts_mode === 'random' || $posts_mode === 'related') {
					$q_args['orderby'] = 'rand';
				}
				$q = new WP_Query($q_args);
				?>
				<?php if ($q->have_posts()) : ?>
					<section class="bankai-404-posts bankai-404-posts--<?php echo esc_attr($posts_layout); ?>">
						<h2 class="bankai-404-posts__title"><?php esc_html_e('شاید این مطالب برایتان مفید باشد', 'bankai-theme'); ?></h2>
						<div class="bankai-404-posts__grid bankai-posts bankai-posts--<?php echo esc_attr($posts_layout); ?> bankai-card-style--<?php echo esc_attr($posts_card); ?>">
							<?php
							while ($q->have_posts()) {
								$q->the_post();
								if (function_exists('bankai_render_post_card')) {
									bankai_render_post_card($posts_card === 'default' || in_array($posts_card, ['horizontal','overlay','minimal','bordered'], true) ? $posts_card : 'default');
								} else {
									get_template_part('template-parts/content', get_post_type());
								}
							}
							wp_reset_postdata();
							?>
						</div>
					</section>
				<?php endif; ?>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php if ($auto_redir) : ?>
<script>
(function(){
  var root = document.querySelector('.bankai-error-404[data-redirect-url]');
  if (!root) return;
  var url = root.getAttribute('data-redirect-url');
  var delay = parseInt(root.getAttribute('data-redirect-delay'), 10) || 8;
  var el = document.querySelector('[data-404-seconds]');
  var left = delay;
  var t = setInterval(function(){
    left -= 1;
    if (el) el.textContent = String(Math.max(0, left));
    if (left <= 0) {
      clearInterval(t);
      if (url) window.location.href = url;
    }
  }, 1000);
})();
</script>
<?php endif; ?>

<?php
get_footer();
