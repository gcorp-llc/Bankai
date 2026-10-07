<?php
/**
 * Bankai Theme — پیشخوان دمو و الگوها
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;

class Bankai_Theme_Admin {

    private static ?self $instance = null;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_menu'], 5);
        add_action('admin_head', [$this, 'menu_icon_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_ajax_bankai_theme_import_demo', [$this, 'ajax_import_demo']);
        add_action('wp_ajax_bankai_theme_apply_pattern', [$this, 'ajax_apply_pattern']);
        add_action('wp_ajax_bankai_theme_save_blog_cards', [$this, 'ajax_save_blog_cards']);
    }


    public function menu_icon_styles(): void {
        echo '<style>
        #adminmenu #toplevel_page_bankai .wp-menu-image img,
        #adminmenu #toplevel_page_bankai-theme .wp-menu-image img {
            width: 20px; height: 20px; padding: 6px 0; object-fit: contain; opacity: .9;
        }
        #adminmenu #toplevel_page_bankai.current .wp-menu-image img,
        #adminmenu #toplevel_page_bankai:hover .wp-menu-image img { opacity: 1; }
        </style>';
    }

    public function register_menu(): void {
        $icon = BANKAI_THEME_URI . '/assets/images/logo.jpg';
        if (! is_readable(BANKAI_THEME_DIR . '/assets/images/logo.jpg')) {
            $icon = 'dashicons-superhero';
        }
        // منوی والد Bankai
        add_menu_page(
            __('Bankai', 'bankai-theme'),
            __('Bankai', 'bankai-theme'),
            'edit_theme_options',
            'bankai',
            [$this, 'render_page'],
            $icon,
            2
        );
        add_submenu_page(
            'bankai',
            __('Bankai Theme', 'bankai-theme'),
            __('Bankai Theme', 'bankai-theme'),
            'edit_theme_options',
            'bankai-theme',
            [$this, 'render_page']
        );
        // اگر Core فعال است، زیرمنوی آن را هم از تم ثبت نکن — پلاگین خودش ثبت می‌کند
        // حذف زیرمنوی تکراری با اسلاگ bankai
        remove_submenu_page('bankai', 'bankai');
    }

    public function enqueue(string $hook): void {
        if ($hook !== 'toplevel_page_bankai' && $hook !== 'bankai_page_bankai-theme' && $hook !== 'toplevel_page_bankai-theme') {
            return;
        }
        wp_enqueue_style(
            'bankai-theme-admin',
            BANKAI_THEME_URI . '/assets/css/theme-admin.css',
            [],
            BANKAI_THEME_VERSION
        );
        wp_enqueue_script(
            'bankai-theme-admin',
            BANKAI_THEME_URI . '/assets/js/theme-admin.js',
            [],
            BANKAI_THEME_VERSION,
            true
        );
        wp_localize_script('bankai-theme-admin', 'bankaiThemeAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('bankai_theme_admin'),
            'i18n'    => [
                'importing' => __('در حال اعمال…', 'bankai-theme'),
                'done'      => __('اعمال شد', 'bankai-theme'),
                'error'     => __('خطا', 'bankai-theme'),
                'saved'     => __('ذخیره شد', 'bankai-theme'),
            ],
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function demos(): array {
        return [
            ['id' => 'agency', 'title' => 'آژانس دیجیتال', 'type' => 'multi', 'cat' => 'business', 'pages' => 8, 'desc' => 'صفحه اصلی، خدمات، نمونه کار، تماس'],
            ['id' => 'corp', 'title' => 'شرکت سازمانی', 'type' => 'multi', 'cat' => 'business', 'pages' => 10, 'desc' => 'درباره ما، تیم، اخبار، فرصت‌های شغلی'],
            ['id' => 'startup', 'title' => 'استارتاپ SaaS', 'type' => 'multi', 'cat' => 'tech', 'pages' => 6, 'desc' => 'لندینگ محصول، قیمت‌گذاری، بلاگ'],
            ['id' => 'shop', 'title' => 'فروشگاه', 'type' => 'multi', 'cat' => 'shop', 'pages' => 7, 'desc' => 'ویترین، دسته‌بندی، سبد خرید'],
            ['id' => 'blog', 'title' => 'مجله محتوا', 'type' => 'multi', 'cat' => 'blog', 'pages' => 5, 'desc' => 'آرشیو، مقاله، نویسنده، دسته‌ها'],
            ['id' => 'portfolio', 'title' => 'پورتفolio', 'type' => 'multi', 'cat' => 'creative', 'pages' => 6, 'desc' => 'گالری پروژه، درباره، تماس'],
            ['id' => 'restaurant', 'title' => 'رستوران', 'type' => 'multi', 'cat' => 'food', 'pages' => 5, 'desc' => 'منو، رزرو، گالری'],
            ['id' => 'education', 'title' => 'آموزشگاه', 'type' => 'multi', 'cat' => 'edu', 'pages' => 7, 'desc' => 'دوره‌ها، اساتید، ثبت‌نام'],
            ['id' => 'landing-app', 'title' => 'لندینگ اپ', 'type' => 'single', 'cat' => 'tech', 'pages' => 1, 'desc' => 'یک‌صفحه‌ای محصول موبایل'],
            ['id' => 'landing-event', 'title' => 'رویداد', 'type' => 'single', 'cat' => 'business', 'pages' => 1, 'desc' => 'ثبت‌نام همایش و تایمر'],
            ['id' => 'landing-personal', 'title' => 'شخصی / رزومه', 'type' => 'single', 'cat' => 'creative', 'pages' => 1, 'desc' => 'معرفی مهارت و نمونه‌کار'],
            ['id' => 'landing-product', 'title' => 'محصول فیزیکی', 'type' => 'single', 'cat' => 'shop', 'pages' => 1, 'desc' => 'فروش مستقیم یک محصول'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function tool_patterns(): array {
        return [
            ['id' => 'nav-simple', 'group' => 'navbar', 'title' => 'Navbar ساده', 'desc' => 'لوگو + منو + CTA'],
            ['id' => 'nav-search', 'group' => 'navbar', 'title' => 'Navbar با جستجو', 'desc' => 'منو + فیلد جستجو'],
            ['id' => 'nav-cta', 'group' => 'navbar', 'title' => 'Navbar با دکمه', 'desc' => 'CTA برجسته در انتها'],
            ['id' => 'mega-cols', 'group' => 'mega', 'title' => 'مگا منو ستونی', 'desc' => 'چند ستون لینک و توضیح'],
            ['id' => 'mega-cards', 'group' => 'mega', 'title' => 'مگا منو کارتی', 'desc' => 'کارت‌های تصویری خدمات'],
            ['id' => 'mega-full', 'group' => 'mega', 'title' => 'مگا منو تمام‌عرض', 'desc' => 'پنل تمام عرض زیر هدر'],
            ['id' => 'footer-simple', 'group' => 'footer', 'title' => 'فوتر ساده', 'desc' => 'کپی‌رایت + لینک‌ها'],
            ['id' => 'footer-cols', 'group' => 'footer', 'title' => 'فوتر چندستونه', 'desc' => '۴ ستون لینک و سوشال'],
            ['id' => 'footer-cta', 'group' => 'footer', 'title' => 'فوتر با CTA', 'desc' => 'خبرنامه و دکمه اقدام'],
            ['id' => 'card-default', 'group' => 'card', 'title' => 'کارت پیش‌فرض', 'desc' => 'تصویر + عنوان + خلاصه'],
            ['id' => 'card-horizontal', 'group' => 'card', 'title' => 'کارت افقی', 'desc' => 'تصویر کنار متن'],
            ['id' => 'card-overlay', 'group' => 'card', 'title' => 'کارت روی تصویر', 'desc' => 'عنوان روی کاور'],
            ['id' => 'card-minimal', 'group' => 'card', 'title' => 'کارت مینیمال', 'desc' => 'بدون تصویر، تایپوگرافی‌محور'],
        ];
    }

    public function render_page(): void {
        if (! current_user_can('edit_theme_options')) {
            return;
        }
        $demos   = $this->demos();
        $tools   = $this->tool_patterns();
        $blog    = $this->blog_card_settings();
        $cats    = [
            'all'      => __('همه', 'bankai-theme'),
            'business' => __('کسب‌وکار', 'bankai-theme'),
            'tech'     => __('فناوری', 'bankai-theme'),
            'shop'     => __('فروشگاهی', 'bankai-theme'),
            'blog'     => __('محتوا', 'bankai-theme'),
            'creative' => __('خلاقانه', 'bankai-theme'),
            'food'     => __('غذایی', 'bankai-theme'),
            'edu'      => __('آموزشی', 'bankai-theme'),
        ];
        ?>
        <div class="bta wrap" id="bankai-theme-admin" dir="rtl">
            <header class="bta-hero">
                <div class="bta-hero__text">
                    <p class="bta-kicker">Bankai Theme</p>
                    <h1><?php esc_html_e('پیشخوان قالب', 'bankai-theme'); ?></h1>
                    <p class="bta-lead">
                        <?php esc_html_e('قالب Bankai برای سرعت بالا و سفارشی‌سازی زنده طراحی شده است. دموهای آماده، الگوهای هدر/فوتر/مگا‌منو و کارت‌های بلاگ را از همین صفحه روی سایت اعمال کنید. ظاهر و رنگ‌ها را از سفارشی‌ساز وردپرس (طرح کلی) تنظیم کنید.', 'bankai-theme'); ?>
                    </p>
                </div>
                <div class="bta-hero__actions">
                    <a class="bta-btn bta-btn--primary" href="<?php echo esc_url(admin_url('customize.php')); ?>"><?php esc_html_e('سفارشی‌ساز', 'bankai-theme'); ?></a>
                    <a class="bta-btn bta-btn--ghost" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener"><?php esc_html_e('مشاهده سایت', 'bankai-theme'); ?></a>
                </div>
            </header>

            <nav class="bta-tabs" role="tablist">
                <button type="button" class="bta-tab is-active" data-tab="demos"><?php esc_html_e('دموها', 'bankai-theme'); ?></button>
                <button type="button" class="bta-tab" data-tab="tools"><?php esc_html_e('ابزارها و الگوها', 'bankai-theme'); ?></button>
                <button type="button" class="bta-tab" data-tab="blog"><?php esc_html_e('بلاگ و کارت‌ها', 'bankai-theme'); ?></button>
            </nav>

            <!-- دموها -->
            <section class="bta-panel is-active" data-panel="demos">
                <div class="bta-toolbar">
                    <div class="bta-filters" data-filter-group="demo-type">
                        <button type="button" class="bta-chip is-active" data-filter="all"><?php esc_html_e('همه', 'bankai-theme'); ?></button>
                        <button type="button" class="bta-chip" data-filter="multi"><?php esc_html_e('چندصفحه‌ای', 'bankai-theme'); ?></button>
                        <button type="button" class="bta-chip" data-filter="single"><?php esc_html_e('تک‌صفحه‌ای', 'bankai-theme'); ?></button>
                    </div>
                    <div class="bta-filters" data-filter-group="demo-cat">
                        <?php foreach ($cats as $cid => $clabel) : ?>
                            <button type="button" class="bta-chip<?php echo $cid === 'all' ? ' is-active' : ''; ?>" data-cat="<?php echo esc_attr($cid); ?>"><?php echo esc_html($clabel); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="bta-grid" id="bta-demos">
                    <?php foreach ($demos as $demo) : ?>
                        <article class="bta-card" data-type="<?php echo esc_attr($demo['type']); ?>" data-cat="<?php echo esc_attr($demo['cat']); ?>">
                            <div class="bta-card__media bta-card__media--<?php echo esc_attr($demo['cat']); ?>">
                                <span class="bta-badge"><?php echo $demo['type'] === 'single' ? esc_html__('تک‌صفحه', 'bankai-theme') : esc_html__('چندصفحه', 'bankai-theme'); ?></span>
                            </div>
                            <div class="bta-card__body">
                                <h3><?php echo esc_html($demo['title']); ?></h3>
                                <p><?php echo esc_html($demo['desc']); ?></p>
                                <div class="bta-card__meta">
                                    <span><?php echo esc_html((string) $demo['pages']); ?> <?php esc_html_e('صفحه', 'bankai-theme'); ?></span>
                                </div>
                                <button type="button" class="bta-btn bta-btn--primary bta-btn--block" data-import-demo="<?php echo esc_attr($demo['id']); ?>">
                                    <?php esc_html_e('نصب دمو', 'bankai-theme'); ?>
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ابزارها -->
            <section class="bta-panel" data-panel="tools">
                <p class="bta-section-desc"><?php esc_html_e('الگوهای Navbar، مگا‌منو، فوتر و کارت — الهام‌گرفته از Flowbite — را جداگانه روی سایت اعمال کنید.', 'bankai-theme'); ?></p>
                <?php
                $groups = [
                    'navbar' => __('هدر / Navbar', 'bankai-theme'),
                    'mega'   => __('مگا منو', 'bankai-theme'),
                    'footer' => __('فوتر', 'bankai-theme'),
                    'card'   => __('کارت', 'bankai-theme'),
                ];
                foreach ($groups as $gid => $glabel) :
                    ?>
                    <h2 class="bta-h2"><?php echo esc_html($glabel); ?></h2>
                    <div class="bta-grid bta-grid--tools">
                        <?php foreach ($tools as $tool) :
                            if ($tool['group'] !== $gid) {
                                continue;
                            }
                            ?>
                            <article class="bta-card bta-card--tool">
                                <div class="bta-card__body">
                                    <h3><?php echo esc_html($tool['title']); ?></h3>
                                    <p><?php echo esc_html($tool['desc']); ?></p>
                                    <button type="button" class="bta-btn bta-btn--ghost bta-btn--block" data-apply-pattern="<?php echo esc_attr($tool['id']); ?>" data-group="<?php echo esc_attr($gid); ?>">
                                        <?php esc_html_e('اعمال روی سایت', 'bankai-theme'); ?>
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- بلاگ و کارت‌ها -->
            <section class="bta-panel" data-panel="blog">
                <p class="bta-section-desc"><?php esc_html_e('مدل نمایش لیست مقالات (سبک کارت‌های Flowbite)، صفحه‌بندی و فیلدهای کارت را تنظیم کنید.', 'bankai-theme'); ?></p>

                <h2 class="bta-h2"><?php esc_html_e('مدل کارت', 'bankai-theme'); ?></h2>
                <div class="bta-card-styles" id="bta-card-styles">
                    <?php
                    $styles = [
                        'default'    => ['title' => 'پیش‌فرض', 'desc' => 'تصویر بالا، عنوان و خلاصه'],
                        'horizontal' => ['title' => 'افقی', 'desc' => 'تصویر کنار محتوا'],
                        'overlay'    => ['title' => 'پوشش تصویر', 'desc' => 'متن روی کاور'],
                        'minimal'    => ['title' => 'مینیمال', 'desc' => 'بدون تصویر'],
                        'bordered'   => ['title' => 'حاشیه‌دار', 'desc' => 'کادر مشخص و سایه کم'],
                    ];
                    foreach ($styles as $sid => $smeta) :
                        $active = ($blog['style'] === $sid) ? ' is-active' : '';
                        ?>
                        <label class="bta-style-card<?php echo esc_attr($active); ?>">
                            <input type="radio" name="blog_card_style" value="<?php echo esc_attr($sid); ?>" <?php checked($blog['style'], $sid); ?>>
                            <div class="bta-style-preview bta-style-preview--<?php echo esc_attr($sid); ?>" aria-hidden="true"></div>
                            <strong><?php echo esc_html($smeta['title']); ?></strong>
                            <span><?php echo esc_html($smeta['desc']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="bta-settings-grid">
                    <div class="bta-card bta-card--form">
                        <h3><?php esc_html_e('فهرست و صفحه‌بندی', 'bankai-theme'); ?></h3>
                        <label class="bta-field">
                            <span><?php esc_html_e('تعداد پست در هر صفحه', 'bankai-theme'); ?></span>
                            <input type="number" min="1" max="48" id="blog_posts_per_page" value="<?php echo esc_attr((string) $blog['per_page']); ?>">
                        </label>
                        <label class="bta-field">
                            <span><?php esc_html_e('نوع صفحه‌بندی', 'bankai-theme'); ?></span>
                            <select id="blog_pagination">
                                <option value="numbers" <?php selected($blog['pagination'], 'numbers'); ?>><?php esc_html_e('شماره‌ای', 'bankai-theme'); ?></option>
                                <option value="prevnext" <?php selected($blog['pagination'], 'prevnext'); ?>><?php esc_html_e('قبلی / بعدی', 'bankai-theme'); ?></option>
                                <option value="infinite" <?php selected($blog['pagination'], 'infinite'); ?>><?php esc_html_e('اسکرول بی‌نهایت', 'bankai-theme'); ?></option>
                            </select>
                        </label>
                    </div>
                    <div class="bta-card bta-card--form">
                        <h3><?php esc_html_e('محتوای کارت', 'bankai-theme'); ?></h3>
                        <label class="bta-field">
                            <span><?php esc_html_e('حداکثر کاراکتر عنوان', 'bankai-theme'); ?></span>
                            <input type="number" min="20" max="200" id="blog_title_chars" value="<?php echo esc_attr((string) $blog['title_chars']); ?>">
                        </label>
                        <label class="bta-field">
                            <span><?php esc_html_e('حداکثر کاراکتر خلاصه', 'bankai-theme'); ?></span>
                            <input type="number" min="40" max="400" id="blog_excerpt_chars" value="<?php echo esc_attr((string) $blog['excerpt_chars']); ?>">
                        </label>
                        <div class="bta-checks">
                            <label><input type="checkbox" id="blog_show_author" <?php checked($blog['show_author']); ?>> <?php esc_html_e('نویسنده', 'bankai-theme'); ?></label>
                            <label><input type="checkbox" id="blog_show_date" <?php checked($blog['show_date']); ?>> <?php esc_html_e('تاریخ انتشار', 'bankai-theme'); ?></label>
                            <label><input type="checkbox" id="blog_show_cats" <?php checked($blog['show_cats']); ?>> <?php esc_html_e('دسته‌ها', 'bankai-theme'); ?></label>
                            <label><input type="checkbox" id="blog_show_excerpt" <?php checked($blog['show_excerpt']); ?>> <?php esc_html_e('خلاصه', 'bankai-theme'); ?></label>
                        </div>
                    </div>
                </div>
                <p class="bta-actions-row">
                    <button type="button" class="bta-btn bta-btn--primary" id="bta-save-blog"><?php esc_html_e('ذخیره تنظیمات بلاگ', 'bankai-theme'); ?></button>
                    <span class="bta-status" id="bta-blog-status" aria-live="polite"></span>
                </p>
            </section>

            <p class="bta-status" id="bta-global-status" aria-live="polite"></p>
        </div>
        <?php
    }

    /** @return array<string, mixed> */
    private function blog_card_settings(): array {
        return [
            'style'         => (string) get_theme_mod('blog_card_style', 'default'),
            'per_page'      => (int) get_theme_mod('blog_posts_per_page', (int) get_option('posts_per_page', 10)),
            'pagination'    => (string) get_theme_mod('blog_pagination', 'numbers'),
            'title_chars'   => (int) get_theme_mod('blog_title_chars', 80),
            'excerpt_chars' => (int) get_theme_mod('blog_excerpt_chars', 140),
            'show_author'   => (bool) get_theme_mod('blog_show_author', true),
            'show_date'     => (bool) get_theme_mod('blog_show_date', true),
            'show_cats'     => (bool) get_theme_mod('blog_show_cats', true),
            'show_excerpt'  => (bool) get_theme_mod('blog_show_excerpt', true),
        ];
    }

    public function ajax_import_demo(): void {
        check_ajax_referer('bankai_theme_admin', 'nonce');
        if (! current_user_can('edit_theme_options')) {
            wp_send_json_error(['message' => 'forbidden'], 403);
        }
        $id = sanitize_key(wp_unslash($_POST['demo'] ?? ''));
        if ($id === '') {
            wp_send_json_error(['message' => 'invalid']);
        }
        if (function_exists('bankai_theme_run_demo_import')) {
            bankai_theme_run_demo_import($id);
        } else {
            set_theme_mod('bankai_last_demo', $id);
            // Minimal: set blog name hint + flag
            update_option('bankai_theme_active_demo', $id, false);
        }
        wp_send_json_success(['message' => __('دمو علامت‌گذاری و آماده اعمال شد.', 'bankai-theme'), 'demo' => $id]);
    }

    public function ajax_apply_pattern(): void {
        check_ajax_referer('bankai_theme_admin', 'nonce');
        if (! current_user_can('edit_theme_options')) {
            wp_send_json_error(['message' => 'forbidden'], 403);
        }
        $id    = sanitize_key(wp_unslash($_POST['pattern'] ?? ''));
        $group = sanitize_key(wp_unslash($_POST['group'] ?? ''));
        if ($id === '') {
            wp_send_json_error(['message' => 'invalid']);
        }
        $map = [
            'navbar' => 'header_nav_pattern',
            'mega'   => 'header_mega_pattern',
            'footer' => 'footer_pattern',
            'card'   => 'blog_card_style',
        ];
        if (isset($map[$group])) {
            $value = $id;
            if ($group === 'card') {
                $value = str_replace('card-', '', $id);
            }
            set_theme_mod($map[$group], $value);
        }
        set_theme_mod('bankai_last_pattern', $id);
        wp_send_json_success(['message' => __('الگو اعمال شد.', 'bankai-theme'), 'pattern' => $id]);
    }

    public function ajax_save_blog_cards(): void {
        check_ajax_referer('bankai_theme_admin', 'nonce');
        if (! current_user_can('edit_theme_options')) {
            wp_send_json_error(['message' => 'forbidden'], 403);
        }
        $style = sanitize_key(wp_unslash($_POST['style'] ?? 'default'));
        $allowed = ['default', 'horizontal', 'overlay', 'minimal', 'bordered'];
        if (! in_array($style, $allowed, true)) {
            $style = 'default';
        }
        set_theme_mod('blog_card_style', $style);
        $per = max(1, min(48, absint($_POST['per_page'] ?? 10)));
        set_theme_mod('blog_posts_per_page', $per);
        update_option('posts_per_page', $per);
        $pag = sanitize_key(wp_unslash($_POST['pagination'] ?? 'numbers'));
        if (! in_array($pag, ['numbers', 'prevnext', 'infinite'], true)) {
            $pag = 'numbers';
        }
        set_theme_mod('blog_pagination', $pag);
        set_theme_mod('blog_title_chars', max(20, min(200, absint($_POST['title_chars'] ?? 80))));
        set_theme_mod('blog_excerpt_chars', max(40, min(400, absint($_POST['excerpt_chars'] ?? 140))));
        set_theme_mod('blog_show_author', ! empty($_POST['show_author']));
        set_theme_mod('blog_show_date', ! empty($_POST['show_date']));
        set_theme_mod('blog_show_cats', ! empty($_POST['show_cats']));
        set_theme_mod('blog_show_excerpt', ! empty($_POST['show_excerpt']));
        wp_send_json_success(['message' => __('تنظیمات بلاگ ذخیره شد.', 'bankai-theme')]);
    }
}
