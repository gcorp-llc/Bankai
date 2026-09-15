<?php
/**
 * Astra-Style Theme Dashboard Template
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || exit;
?>

<div id="bankai-theme-admin-app"
     x-data="bankaiThemeAdmin()"
     x-init="init()"
     :dir="isRtl ? 'rtl' : 'ltr'"
     class="bankai-theme-dashboard"
     style="max-width: 1240px; margin: 24px auto; padding: 0 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Vazirmatn', sans-serif; color: #1F2328;">

    <!-- Save Notification Toast -->
    <div x-show="toast.show"
         x-cloak
         x-transition
         class="bankai-theme-toast"
         style="position: fixed; bottom: 24px; left: 24px; z-index: 99999; background: #1F2328; color: #FFFFFF; padding: 12px 20px; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600;">
        <svg style="width: 18px; height: 18px; color: #2DA44E;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span x-text="toast.message">Changes saved</span>
    </div>

    <!-- Astra-Style Top Hero Banner -->
    <div class="bankai-theme-hero-card"
         style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 24px 32px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: linear-gradient(135deg, #0969DA 0%, #054a99 100%); display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-weight: 800; font-size: 22px; box-shadow: 0 4px 12px rgba(9, 105, 218, 0.25);">
                B
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: #1F2328;">
                        <?php esc_html_e('پیشخوان و تنظیمات قالب Bankai', 'bankai-theme'); ?>
                    </h1>
                    <span style="background: #DDF4FF; color: #0969DA; border: 1px solid #54AEFF; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px;">
                        v<?php echo esc_html(BANKAI_THEME_VERSION); ?>
                    </span>
                    <span style="background: #DAFBE1; color: #1A7F37; border: 1px solid #4AC26B; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px;">
                        <?php esc_html_e('سبک و فوق‌سریع (مشابه Astra)', 'bankai-theme'); ?>
                    </span>
                </div>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #656D76;">
                    <?php esc_html_e('قالب مدرن و سبک وردپرس، سازگار با گوتنبرگ و المنتور، بدون نیاز به جی‌کوئری با نمره سرعت ۱۰۰٪', 'bankai-theme'); ?>
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <a :href="customizeUrl"
               class="button button-primary"
               style="background: #0969DA; border-color: #0969DA; color: #FFFFFF; padding: 8px 18px; height: auto; border-radius: 8px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(9, 105, 218, 0.25); text-decoration: none;">
                <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <?php esc_html_e('سفارشی‌سازی زنده قالب (Customizer)', 'bankai-theme'); ?>
            </a>
            <a href="https://gcorp.io/docs/bankai-theme"
               target="_blank"
               style="background: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <?php esc_html_e('مستندات قالب', 'bankai-theme'); ?>
            </a>
        </div>
    </div>

    <!-- Classic Astra 2-Column Grid Layout -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="bankai-theme-layout-grid">
        
        <!-- Left Main Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">

            <!-- Quick Customizer Shortcuts (مانند میانبرهای سفارشی‌ساز قالب آسترا) -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #EAEEF2;">
                    <div>
                        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #1F2328;">
                            <?php esc_html_e('میانبرهای سریع سفارشی‌ساز قالب (Astra Customizer Shortcuts)', 'bankai-theme'); ?>
                        </h2>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #656D76;">
                            <?php esc_html_e('دسترسی مستقیم و بدون واسطه به بخش‌های اصلی سفارشی‌سازی زنده', 'bankai-theme'); ?>
                        </p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;">
                    <!-- Shortcut 1: Header Builder -->
                    <a :href="`${customizeUrl}?autofocus[panel]=bankai_header_panel`"
                       style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; text-decoration: none; color: #1F2328; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 32px; height: 32px; border-radius: 6px; background: #DDF4FF; color: #0969DA; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
                            </span>
                            <div>
                                <div style="font-size: 13px; font-weight: 700;"><?php esc_html_e('هدرساز و لوگو (Header Builder)', 'bankai-theme'); ?></div>
                                <div style="font-size: 11px; color: #656D76;"><?php esc_html_e('لوگو، منو، جستجو و دکمه CTA', 'bankai-theme'); ?></div>
                            </div>
                        </div>
                        <span style="font-size: 12px; color: #0969DA; font-weight: 600;">&larr;</span>
                    </a>

                    <!-- Shortcut 2: Footer Builder -->
                    <a :href="`${customizeUrl}?autofocus[panel]=bankai_footer_panel`"
                       style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; text-decoration: none; color: #1F2328; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 32px; height: 32px; border-radius: 6px; background: #F3E8FF; color: #8250DF; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="15" x2="21" y2="15"/></svg>
                            </span>
                            <div>
                                <div style="font-size: 13px; font-weight: 700;"><?php esc_html_e('فوترساز و کپی‌رایت (Footer Builder)', 'bankai-theme'); ?></div>
                                <div style="font-size: 11px; color: #656D76;"><?php esc_html_e('ابزارک‌ها، کپی‌رایت و منوی پایین', 'bankai-theme'); ?></div>
                            </div>
                        </div>
                        <span style="font-size: 12px; color: #0969DA; font-weight: 600;">&larr;</span>
                    </a>

                    <!-- Shortcut 3: Global Colors -->
                    <a :href="`${customizeUrl}?autofocus[section]=bankai_colors_section`"
                       style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; text-decoration: none; color: #1F2328; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 32px; height: 32px; border-radius: 6px; background: #FFEBE9; color: #CF222E; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a7 7 0 0 0 7 7c0 2-2 3-2 5a3 3 0 0 1-3 3h-2"/></svg>
                            </span>
                            <div>
                                <div style="font-size: 13px; font-weight: 700;"><?php esc_html_e('پالت رنگ‌های سراسری (Global Colors)', 'bankai-theme'); ?></div>
                                <div style="font-size: 11px; color: #656D76;"><?php esc_html_e('رنگ اصلی، لینک‌ها و پس‌زمینه', 'bankai-theme'); ?></div>
                            </div>
                        </div>
                        <span style="font-size: 12px; color: #0969DA; font-weight: 600;">&larr;</span>
                    </a>

                    <!-- Shortcut 4: Typography -->
                    <a :href="`${customizeUrl}?autofocus[section]=bankai_typography_section`"
                       style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; text-decoration: none; color: #1F2328; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 32px; height: 32px; border-radius: 6px; background: #DAFBE1; color: #1A7F37; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                            </span>
                            <div>
                                <div style="font-size: 13px; font-weight: 700;"><?php esc_html_e('تایپوگرافی و فونت‌ها (Typography)', 'bankai-theme'); ?></div>
                                <div style="font-size: 11px; color: #656D76;"><?php esc_html_e('وزیرمتن، پلاس جاکارتا، اندازه تیترها', 'bankai-theme'); ?></div>
                            </div>
                        </div>
                        <span style="font-size: 12px; color: #0969DA; font-weight: 600;">&larr;</span>
                    </a>

                    <!-- Shortcut 5: Container Layout -->
                    <a :href="`${customizeUrl}?autofocus[section]=bankai_layout_section`"
                       style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; text-decoration: none; color: #1F2328; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 32px; height: 32px; border-radius: 6px; background: #FFF8C5; color: #9A6700; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                            </span>
                            <div>
                                <div style="font-size: 13px; font-weight: 700;"><?php esc_html_e('طرح‌بندی کانتینر (Container Layout)', 'bankai-theme'); ?></div>
                                <div style="font-size: 11px; color: #656D76;"><?php esc_html_e('عرض کانتینر، جعبه‌ای و سایدبار', 'bankai-theme'); ?></div>
                            </div>
                        </div>
                        <span style="font-size: 12px; color: #0969DA; font-weight: 600;">&larr;</span>
                    </a>

                    <!-- Shortcut 6: Blog Layout -->
                    <a :href="`${customizeUrl}?autofocus[section]=bankai_blog_section`"
                       style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; text-decoration: none; color: #1F2328; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 32px; height: 32px; border-radius: 6px; background: #E1F0FF; color: #0550AE; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </span>
                            <div>
                                <div style="font-size: 13px; font-weight: 700;"><?php esc_html_e('طرح وبلاگ و بایگانی (Blog & Archive)', 'bankai-theme'); ?></div>
                                <div style="font-size: 11px; color: #656D76;"><?php esc_html_e('شبکه‌ای، متاداده، تصویر شاخص', 'bankai-theme'); ?></div>
                            </div>
                        </div>
                        <span style="font-size: 12px; color: #0969DA; font-weight: 600;">&larr;</span>
                    </a>
                </div>
            </div>

            <!-- Astra Pro Style Modular Extensions (ماژول‌های پیشرفته قالب مشابه Astra Pro) -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #EAEEF2;">
                    <div>
                        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #1F2328;">
                            <?php esc_html_e('افزونه‌های ماژولار قالب (Astra Pro Style Extensions)', 'bankai-theme'); ?>
                        </h2>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #656D76;">
                            <?php esc_html_e('فعال یا غیرفعال‌سازی ماژول‌های حرفه‌ای جهت بهینه‌سازی دقیق منابع و سرعت بارگذاری', 'bankai-theme'); ?>
                        </p>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <template x-for="(mod, key) in themeModules" :key="key">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="font-size: 18px;" x-text="mod.icon"></span>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="mod.title"></div>
                                    <div style="font-size: 11px; color: #656D76;" x-text="mod.description"></div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <a :href="`${customizeUrl}?autofocus[section]=${mod.customizerSection}`"
                                   style="font-size: 11px; font-weight: 600; color: #0969DA; text-decoration: none;"
                                   x-show="modulesState[key]">
                                    <?php esc_html_e('تنظیمات اختصاصی', 'bankai-theme'); ?> &larr;
                                </a>
                                <label class="bankai-theme-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                    <input type="checkbox"
                                           :checked="modulesState[key]"
                                           @change="toggleModule(key)"
                                           style="opacity: 0; width: 0; height: 0;">
                                    <span class="bankai-theme-slider"
                                          :style="modulesState[key] ? 'background-color: #1A7F37;' : 'background-color: #D0D7DE;'"
                                          style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; border-radius: 24px; transition: .3s;">
                                        <span :style="modulesState[key] ? 'transform: translateX(20px);' : 'transform: translateX(2px);'"
                                              style="position: absolute; content: ''; height: 18px; width: 18px; left: 2px; bottom: 3px; background-color: white; border-radius: 50%; transition: .3s; display: block;"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Starter Kits (مشابه قالب‌های آماده آسترا Astra Starter Sites) -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div>
                        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #1F2328;">
                            <?php esc_html_e('کتابخانه سایت‌های آماده (Astra Starter Templates Library)', 'bankai-theme'); ?>
                        </h2>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #656D76;">
                            <?php esc_html_e('نصب و درون‌ریزی ۱-کلیکه دموهای آماده شرکتی، فروشگاهی و وبلاگی', 'bankai-theme'); ?>
                        </p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <div style="border: 1px solid #D0D7DE; border-radius: 8px; overflow: hidden; background: #F6F8FA;">
                        <img src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=400&q=80" style="width: 100%; height: 130px; object-fit: cover;" alt="Demo" />
                        <div style="padding: 12px;">
                            <div style="font-weight: 700; font-size: 13px;">فروشگاه آنلاین سایبر (eCommerce)</div>
                            <div style="font-size: 11px; color: #656D76; margin-top: 4px;">سازگار با ووکامرس و درگاه پرداخت</div>
                        </div>
                    </div>
                    <div style="border: 1px solid #D0D7DE; border-radius: 8px; overflow: hidden; background: #F6F8FA;">
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=400&q=80" style="width: 100%; height: 130px; object-fit: cover;" alt="Demo" />
                        <div style="padding: 12px;">
                            <div style="font-weight: 700; font-size: 13px;">شرکتی و استارتاپی (Agency Pro)</div>
                            <div style="font-size: 11px; color: #656D76; margin-top: 4px;">گوتنبرگ و بلوک‌های ریسپانسیو</div>
                        </div>
                    </div>
                    <div style="border: 1px solid #D0D7DE; border-radius: 8px; overflow: hidden; background: #F6F8FA;">
                        <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=400&q=80" style="width: 100%; height: 130px; object-fit: cover;" alt="Demo" />
                        <div style="padding: 12px;">
                            <div style="font-weight: 700; font-size: 13px;">پرتال خبری و مجله (Tech Portal)</div>
                            <div style="font-size: 11px; color: #656D76; margin-top: 4px;">سرعت لود ۰.۴ ثانیه و سئوی قوی</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Sidebar Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">

            <!-- Performance Metrics (بنچمارک‌های سبک‌وزن بودن قالب مشابه آسترا) -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #1A7F37;"></span>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2328;">
                        <?php esc_html_e('شاخص‌های سرعت و عملکرد (Astra Benchmarks)', 'bankai-theme'); ?>
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #F6F8FA; border-radius: 6px;">
                        <span style="font-size: 12px; color: #656D76;">Google PageSpeed</span>
                        <span style="font-size: 13px; font-weight: 800; color: #1A7F37;">100 / 100</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #F6F8FA; border-radius: 6px;">
                        <span style="font-size: 12px; color: #656D76;">حجم اولیه فرانت‌اند</span>
                        <span style="font-size: 13px; font-weight: 800; color: #0969DA;">&lt; 38 KB</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #F6F8FA; border-radius: 6px;">
                        <span style="font-size: 12px; color: #656D76;">وابستگی به jQuery</span>
                        <span style="font-size: 12px; font-weight: 700; color: #1A7F37;">صفر (Pure Vanilla JS)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #F6F8FA; border-radius: 6px;">
                        <span style="font-size: 12px; color: #656D76;">زمان پاسخ سرور (TTFB)</span>
                        <span style="font-size: 12px; font-weight: 700; color: #1F2328;">28 میلی‌ثانیه</span>
                    </div>
                </div>
            </div>

            <!-- Compatible Page Builders -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #1F2328;">
                    <?php esc_html_e('صفحه‌سازهای کاملاً سازگار', 'bankai-theme'); ?>
                </h3>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #1F2328;">
                        <span style="color: #1A7F37;">&#10004;</span> Gutenberg (ویرایشگر بلاک بومی)
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; color: #1F2328;">
                        <span style="color: #1A7F37;">&#10004;</span> Elementor & Elementor Pro
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; color: #1F2328;">
                        <span style="color: #1A7F37;">&#10004;</span> Spectra (Ultimate Addons for Blocks)
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; color: #1F2328;">
                        <span style="color: #1A7F37;">&#10004;</span> WooCommerce & CartFlows
                    </div>
                </div>
            </div>

            <!-- Ecosystem Connection: Bankai Core Plugin -->
            <div style="background: linear-gradient(135deg, #F6F8FA 0%, #EAEEF2 100%); border: 1px solid #D0D7DE; border-radius: 12px; padding: 20px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <span style="background: #0969DA; color: white; width: 22px; height: 22px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">⚡</span>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2328;">
                        <?php esc_html_e('افزونه مکمل Bankai Core', 'bankai-theme'); ?>
                    </h3>
                </div>
                <p style="margin: 0 0 12px 0; font-size: 12px; color: #656D76; line-height: 1.5;">
                    <?php esc_html_e('برای بهره‌مندی از هوش مصنوعی چندمدلی، موتور سئو خودکار و فشرده‌سازی WebP، افزونه Bankai Core در کنار این قالب فعال است.', 'bankai-theme'); ?>
                </p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=bankai-core')); ?>"
                   style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #0969DA; text-decoration: none;">
                    <?php esc_html_e('مشاهده پیشخوان افزونه Bankai Core', 'bankai-theme'); ?> &larr;
                </a>
            </div>

        </div>
    </div>
</div>
