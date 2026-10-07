<?php
/**
 * Single post — SEO-first structure
 * Order: featured → title → meta → content → related → terms → comments
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

$show_cover   = bankai_theme_mod('single_show_cover', true);
$show_title   = bankai_theme_mod('single_show_title', true);
$show_author  = bankai_theme_mod('single_show_author', true);
$show_date    = bankai_theme_mod('single_show_date', true);
$show_cats    = bankai_theme_mod('single_show_cats', true);
$show_tags    = bankai_theme_mod('single_show_tags', true);
$show_related = bankai_theme_mod('single_show_related', true);
$show_comments = bankai_theme_mod('single_show_comments', true);
$related_mode = (string) bankai_theme_mod('single_related_mode', 'related');
$related_count = (int) bankai_theme_mod('single_related_count', 4);
$related_layout = (string) bankai_theme_mod('single_related_layout', 'grid');
$related_card = (string) bankai_theme_mod('single_related_card_style', 'default');
$show_author_box = bankai_theme_mod('single_show_author_box', true);

$author_id = (int) get_the_author_meta('ID');
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('bankai-single'); ?> itemscope itemtype="https://schema.org/BlogPosting">
	<meta itemprop="mainEntityOfPage" content="<?php the_permalink(); ?>">
	<meta itemprop="datePublished" content="<?php echo esc_attr(get_the_date('c')); ?>">
	<meta itemprop="dateModified" content="<?php echo esc_attr(get_the_modified_date('c')); ?>">

	<?php if ($show_cover && has_post_thumbnail()) : ?>
		<figure class="bankai-single__cover" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
			<?php
			the_post_thumbnail('large', [
				'loading'       => 'eager',
				'decoding'      => 'async',
				'fetchpriority' => 'high',
				'itemprop'      => 'url',
				'alt'           => the_title_attribute(['echo' => false]) ?: get_the_title(),
			]);
			$thumb_id = get_post_thumbnail_id();
			if ($thumb_id) {
				$src = wp_get_attachment_image_url($thumb_id, 'large');
				if ($src) {
					echo '<meta itemprop="url" content="' . esc_url($src) . '">';
				}
			}
			?>
		</figure>
	<?php endif; ?>

	<header class="bankai-single__header">
		<?php if ($show_title) : ?>
			<?php the_title('<h1 class="bankai-single__title" itemprop="headline">', '</h1>'); ?>
		<?php endif; ?>

		<div class="bankai-single__meta">
			<?php if ($show_author) : ?>
				<span class="bankai-single__author" itemprop="author" itemscope itemtype="https://schema.org/Person">
					<?php echo get_avatar($author_id, 40, '', '', ['class' => 'bankai-single__avatar']); ?>
					<span itemprop="name"><?php the_author(); ?></span>
				</span>
			<?php endif; ?>
			<?php if ($show_date) : ?>
				<time class="bankai-single__date" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>" itemprop="datePublished">
					<?php echo esc_html(get_the_date()); ?>
				</time>
			<?php endif; ?>
			<?php if ($show_cats && has_category()) : ?>
				<span class="bankai-single__cats"><?php the_category(' '); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<div class="bankai-entry-content entry-content" itemprop="articleBody">
		<?php
		the_content();
		wp_link_pages([
			'before' => '<nav class="page-links" aria-label="' . esc_attr__('صفحات مطلب', 'bankai-theme') . '">',
			'after'  => '</nav>',
		]);
		?>
	</div>

	<?php if ($show_author_box) : ?>
		<?php
		$aid = (int) get_the_author_meta('ID');
		$bio = get_the_author_meta('description');
		$aname = get_the_author();
		?>
		<section class="bankai-author-box" aria-label="<?php esc_attr_e('درباره نویسنده', 'bankai-theme'); ?>">
			<div class="bankai-author-box__profile">
				<?php echo get_avatar($aid, 72, '', $aname, ['class' => 'bankai-author-box__avatar']); ?>
				<div class="bankai-author-box__info">
					<h2 class="bankai-author-box__name"><?php echo esc_html($aname); ?></h2>
					<?php if ($bio) : ?>
						<p class="bankai-author-box__bio"><?php echo esc_html($bio); ?></p>
					<?php endif; ?>
					<a class="bankai-author-box__link" href="<?php echo esc_url(get_author_posts_url($aid)); ?>">
						<?php esc_html_e('همه مطالب این نویسنده', 'bankai-theme'); ?>
					</a>
				</div>
			</div>
			<?php
			$by = new WP_Query([
				'author' => $aid,
				'post__not_in' => [get_the_ID()],
				'posts_per_page' => 3,
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
			]);
			if ($by->have_posts()) :
				?>
				<div class="bankai-author-box__posts">
					<h3 class="bankai-author-box__posts-title"><?php esc_html_e('از همین نویسنده', 'bankai-theme'); ?></h3>
					<ul>
						<?php while ($by->have_posts()) : $by->the_post(); ?>
							<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
						<?php endwhile; wp_reset_postdata(); ?>
					</ul>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php
	// Publisher org for SEO
	?>
	<div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" class="screen-reader-text">
		<meta itemprop="name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
		<meta itemprop="url" content="<?php echo esc_url(home_url('/')); ?>">
	</div>

	<?php if ($show_related) : ?>
		<?php
		$args = [
			'post_type'           => 'post',
			'posts_per_page'      => max(1, min(12, $related_count)),
			'post__not_in'        => [get_the_ID()],
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];
		if ($related_mode === 'related') {
			$cats = wp_get_post_categories(get_the_ID());
			if ($cats) {
				$args['category__in'] = $cats;
			}
		}
		$rel = new WP_Query($args);
		if ($rel->have_posts()) :
			?>
			<section class="bankai-related bankai-related--<?php echo esc_attr($related_layout); ?>" aria-label="<?php esc_attr_e('مطالب مرتبط', 'bankai-theme'); ?>">
				<h2 class="bankai-related__title"><?php echo $related_mode === 'latest' ? esc_html__('آخرین مطالب', 'bankai-theme') : esc_html__('مطالب مرتبط', 'bankai-theme'); ?></h2>
				<div class="bankai-related__list">
					<?php
					while ($rel->have_posts()) :
						$rel->the_post();
						if (function_exists('bankai_render_post_card')) {
							bankai_render_post_card($related_card);
						} else {
							echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
						}
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ($show_tags || $show_cats) : ?>
		<footer class="bankai-single__terms">
			<?php if ($show_cats && has_category()) : ?>
				<div class="bankai-single__term-row">
					<span class="bankai-single__term-label"><?php esc_html_e('دسته‌ها', 'bankai-theme'); ?></span>
					<?php the_category(' '); ?>
				</div>
			<?php endif; ?>
			<?php if ($show_tags && has_tag()) : ?>
				<div class="bankai-single__term-row">
					<span class="bankai-single__term-label"><?php esc_html_e('برچسب‌ها', 'bankai-theme'); ?></span>
					<?php the_tags('', ' ', ''); ?>
				</div>
			<?php endif; ?>
		</footer>
	<?php endif; ?>
</article>
