<?php
/**
 * Bankai Framework Theme Dashboard Template
 *
 * @package Bankai_Theme
 */

defined('ABSPATH') || die;
?>

<div id="bankai-admin-app"
     class="bankai-admin-wrap bankai-theme-dashboard"
     x-data="bankaiThemeAdmin()"
     x-init="init()"
     :dir="isRtl ? 'rtl' : 'ltr'">

    <!-- Save Notification Toast -->
    <div x-show="toast.show"
         x-cloak
         x-transition
         class="bankai-theme-toast"
         style="position: fixed; bottom: 24px; left: 24px; z-index: 99999; background: #1F2328; color: #FFFFFF; padding: 12px 20px; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600;">
        <svg style="width: 18px; height: 18px; color: #2DA44E;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span x-text="toast.message"></span>
    </div>

    <!-- Bankai Framework Top Hero Banner -->
    <div style="background: linear-gradient(135deg, #0969DA 0%, #1F2328 100%); border-radius: 12px; padding: 28px 32px; color: #FFFFFF; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(9, 105, 218, 0.15);">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); color: #FFFFFF; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">
                    BANKAI FRAMEWORK <?php echo esc_html(BANKAI_THEME_VERSION); ?>
                </span>
                <span style="background: #2DA44E; color: #FFFFFF; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                    100% CORE WEB VITALS
                </span>
            </div>
            <h1 style="margin: 0 0 6px 0; font-size: 22px; font-weight: 800; color: #FFFFFF;">
                <?php esc_html_e('پیشخوان و مدیریت سفارشی‌سازی قالب Bankai', 'bankai-theme'); ?>
            </h1>
            <p style="margin: 0; font-size: 13px; color: rgba(255,255,255,0.8); max-width: 600px; line-height: 1.5;">
                <?php esc_html_e('قالب فوق‌سریع و سبک بانکای طراحی شده بر پایه معماری مدرن وب با صفر درصد وابستگی به jQuery و حداکثر بهره‌وری برای گوتنبرگ و ووکامرس.', 'bankai-theme'); ?>
            </p>
        </div>

        <div style="display: flex; gap: 12px;">
            <a :href="customizeUrl"
               target="_blank"
               style="background: #FFFFFF; color: #0969DA; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
               onmouseover="this.style.transform='translateY(-1px)';"
               onmouseout="this.style.transform='translateY(0)';">
                <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                <?php esc_html_e('ورود به سفارشی‌ساز وردپرس', 'bankai-theme'); ?>
            </a>
            <a href="https://gcorp.io/docs/bankai-theme"
               target="_blank"
               style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); color: #FFFFFF; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <?php esc_html_e('مستندات قالب', 'bankai-theme'); ?>
            </a>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px;">

        <!-- Left Content Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">

            <!-- Customizer Quick Links Matrix -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #D0D7DE;">
                    <div>
                        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #1F2328;">
                            <?php esc_html_e('میانبرهای سریع سفارشی‌ساز قالب (Bankai Customizer Shortcuts)', 'bankai-theme'); ?>
                        </h2>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #656D76;">
                            <?php esc_html_e('دسترسی مستقیم به بخش‌های مختلف تنظیمات ظاهری در سفارشی‌ساز وردپرس', 'bankai-theme'); ?>
                        </p>
                    </div>
                    <span style="font-size: 11px; background: #DDF4FF; color: #0969DA; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                        8 Quick Controls
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                    <template x-for="(shortcut, key) in shortcuts" :key="key">
                        <a :href="customizeUrl + '?autofocus[panel]=' + shortcut.autofocus"
                           target="_blank"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border: 1px solid #D0D7DE; border-radius: 8px; text-decoration: none; color: #1F2328; background: #FFFFFF; transition: all 0.15s;"
                           onmouseover="this.style.borderColor='#0969DA'; this.style.backgroundColor='#F6F8FA';"
                           onmouseout="this.style.borderColor='#D0D7DE'; this.style.backgroundColor='#FFFFFF';">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="font-size: 18px;" x-text="shortcut.icon"></span>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;" x-text="shortcut.title"></div>
                                    <div style="font-size: 11px; color: #656D76;" x-text="shortcut.desc"></div>
                                </div>
                            </div>
                            <span style="font-size: 14px; color: #0969DA; font-weight: bold;" x-text="isRtl ? '←' : '→'"></span>
                        </a>
                    </template>
                </div>
            </div>

            <!-- Pro Modular Extensions -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #D0D7DE;">
                    <div>
                        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #1F2328;">
                            <?php esc_html_e('افزونه‌های ماژولار قالب (Bankai Pro Style Extensions)', 'bankai-theme'); ?>
                        </h2>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #656D76;">
                            <?php esc_html_e('فعال‌سازی ماژول‌های فرانت‌اند بدون بارگذاری کدهای اضافی و سنگین‌سازی سایت', 'bankai-theme'); ?>
                        </p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                    <template x-for="(mod, key) in moduleDefinitions" :key="key">
                        <div style="border: 1px solid #D0D7DE; border-radius: 8px; padding: 14px 16px; background: #FFFFFF; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                    <span style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="mod.title"></span>
                                    <span x-show="mod.pro" style="font-size: 9px; background: #FFEBE9; color: #CF222E; font-weight: 800; padding: 2px 6px; border-radius: 10px;">PRO</span>
                                </div>
                                <div style="font-size: 11px; color: #656D76; line-height: 1.4;" x-text="mod.desc"></div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px;">
                                <a x-show="mod.settingsUrl" :href="mod.settingsUrl" target="_blank" title="تنظیمات اختصاصی" style="color: #656D76; text-decoration: none; font-size: 14px;">
                                    &larr;
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

            <!-- Starter Kits -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div>
                        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #1F2328;">
                            <?php esc_html_e('کتابخانه سایت‌های آماده (Bankai Starter Templates Library)', 'bankai-theme'); ?>
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

            <!-- Performance Metrics -->
            <div style="background: #FFFFFF; border: 1px solid #D0D7DE; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(31, 35, 40, 0.04);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #1A7F37;"></span>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1F2328;">
                        <?php esc_html_e('شاخص‌های سرعت و عملکرد (Bankai Benchmarks)', 'bankai-theme'); ?>
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
