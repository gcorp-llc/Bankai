<?php
defined('ABSPATH') || exit;
?>
<aside id="bankai-admin-sidebar"
       class="bankai-sidebar"
       :class="mobileMenuOpen ? 'mobile-open' : ''">
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding: 4px 6px;"
             x-show="mobileMenuOpen"
             x-cloak>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(9, 105, 218, 0.12); display: flex; align-items: center; justify-content: center; color: #0969DA;">
                    <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6h16M4 12h10M4 18h16" />
                    </svg>
                </div>
                <span style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="isRtl ? '<?php echo esc_js(__('منوی مدیریت', 'bankai-core')); ?>' : '<?php echo esc_js(__('Navigation Menu', 'bankai-core')); ?>'"><?php esc_html_e('Navigation Menu', 'bankai-core'); ?></span>
            </div>
            <button @click="closeMobileMenu()"
                    aria-label="<?php esc_attr_e('Close navigation', 'bankai-core'); ?>"
                    style="background: #F6F8FA; border: 1px solid #D0D7DE; color: #656D76; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div style="padding: 4px 8px 16px 8px; margin-bottom: 10px; border-bottom: 1px solid #D0D7DE; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #0969DA 0%, #218BFF 100%); display: flex; align-items: center; justify-content: center; color: #FFFFFF; box-shadow: 0 3px 8px rgba(9, 105, 218, 0.25); flex-shrink: 0;">
                    <svg class="solar-icon" style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13.5 2L4 13.5h7L9.5 22 20 10.5h-7.5L13.5 2Z" />
                    </svg>
                </div>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: #1F2328; letter-spacing: 0.2px; line-height: 1.2;">
                        BANKAI CORE
                    </div>
                    <div style="font-size: 11px; color: #656D76; display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #1A7F37;"></span>
                        <span x-text="isRtl ? '<?php echo esc_js(__('نسخه تجاری فعال', 'bankai-core')); ?>' : '<?php echo esc_js(__('Active Enterprise', 'bankai-core')); ?>'"><?php esc_html_e('Active Enterprise', 'bankai-core'); ?></span>
                    </div>
                </div>
            </div>
            <span style="background: #DAFBE1; color: #1A7F37; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 12px; border: 1px solid #ACEBBB;">
                PRO
            </span>
        </div>

        <nav style="display: flex; flex-direction: column; gap: 2px;">
            <div class="bankai-nav-group">
                <div class="bankai-nav-group-title" x-text="t('navArch')">Core Architecture</div>

                <button id="nav-tab-overview"
                        class="bankai-nav-btn"
                        :class="activeTab === 'overview' ? 'active' : ''"
                        @click="activeTab = 'overview'; setTab('overview')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.5 9.5V4.5C2.5 3.395 3.395 2.5 4.5 2.5H9.5C10.605 2.5 11.5 3.395 11.5 4.5V9.5C11.5 10.605 10.605 11.5 9.5 11.5H4.5C3.395 11.5 2.5 10.605 2.5 9.5Z" />
                            <path d="M14.5 19.5V14.5C14.5 13.395 15.395 12.5 16.5 12.5H19.5C20.605 12.5 21.5 13.395 21.5 14.5V19.5C21.5 20.605 20.605 21.5 19.5 21.5H16.5C15.395 21.5 14.5 20.605 14.5 19.5Z" />
                            <path d="M2.5 16.5C2.5 14.29 4.29 12.5 6.5 12.5H9.5C10.605 12.5 11.5 13.395 11.5 14.5V19.5C11.5 20.605 10.605 21.5 9.5 21.5H6.5C4.29 21.5 2.5 19.71 2.5 17.5V16.5Z" />
                            <path d="M14.5 4.5C14.5 3.395 15.395 2.5 16.5 2.5H19.5C20.605 2.5 21.5 3.395 21.5 4.5V7.5C21.5 9.71 19.71 11.5 17.5 11.5H16.5C15.395 11.5 14.5 10.605 14.5 9.5V4.5Z" />
                            <circle cx="7" cy="7" r="1" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <span x-text="t('navOverview')"><?php esc_html_e('Overview & Telemetry', 'bankai-core'); ?></span>
                </button>

                <button id="nav-tab-theme-kits"
                        class="bankai-nav-btn"
                        :class="activeTab === 'theme-kits' ? 'active' : ''"
                        @click="activeTab = 'theme-kits'; setTab('theme-kits')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22C6.477 22 2 17.523 2 12c0-4.478 2.946-8.267 7-9.535M16.5 3.12C19.832 4.675 22 8.09 22 12c0 3.5-1.5 5.5-4 5.5h-1.5c-1.105 0-2 .895-2 2 0 1.38 1.12 2.5 2.5 2.5" />
                            <circle cx="8" cy="10" r="1.5" fill="currentColor" stroke="none" />
                            <circle cx="12" cy="7" r="1.5" fill="currentColor" stroke="none" />
                            <circle cx="16" cy="10" r="1.5" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <span x-text="t('navThemeKits')"><?php esc_html_e('Theme Kits & Customizer', 'bankai-core'); ?></span>
                    <span class="bankai-nav-badge" style="background: #DDF4FF; color: #0969DA; border: 1px solid #54AEFF;">3 Kits</span>
                </button>

                <a id="nav-link-astra-theme"
                   href="<?php echo esc_url(admin_url('admin.php?page=bankai-theme')); ?>"
                   class="bankai-nav-btn"
                   style="text-decoration: none; display: flex; align-items: center; border-inline-start: 2px solid transparent;">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" />
                            <line x1="8" y1="21" x2="16" y2="21" />
                            <line x1="12" y1="17" x2="12" y2="21" />
                        </svg>
                    </div>
                    <span x-text="isRtl ? '<?php echo esc_js(__('قالب Bankai (مشابه آسترا)', 'bankai-core')); ?>' : '<?php echo esc_js(__('Astra Theme Panel', 'bankai-core')); ?>'"><?php esc_html_e('Astra Theme Panel', 'bankai-core'); ?></span>
                    <span class="bankai-nav-badge" style="background: #F3E8FF; color: #8250DF; border: 1px solid #D8B4FE;">Astra Pro</span>
                </a>
            </div>

            <div class="bankai-nav-group">
                <div class="bankai-nav-group-title" x-text="t('navPerformance')">Optimization & Speed</div>

                <button id="nav-tab-seo"
                        class="bankai-nav-btn"
                        :class="activeTab === 'seo' ? 'active' : ''"
                        @click="activeTab = 'seo'; setTab('seo')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18.5 18.5L22 22" />
                            <path d="M6.75 3.27A9 9 0 0 1 18.23 6.75M20 11.5a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0Z" />
                            <path d="M10 8.5a3 3 0 0 1 3 3" />
                        </svg>
                    </div>
                    <span x-text="t('navSeo')"><?php esc_html_e('SEO & Schema Engine', 'bankai-core'); ?></span>
                    <span class="bankai-nav-badge" style="background: #DAFBE1; color: #1A7F37; border: 1px solid #ACEBBB;">98/100</span>
                </button>

                <button id="nav-tab-speed"
                        class="bankai-nav-btn"
                        :class="activeTab === 'speed' ? 'active' : ''"
                        @click="activeTab = 'speed'; setTab('speed')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.5 9.5L18 6M15.5 15.5L12 19l-3.5-1.5L7 16l-2.5-2.5L3 10l3.5-3.5L10 3l6 3.5 4.5 1.5c.5.167.9.6.9 1.1a12.5 12.5 0 0 1-5.9 7.4Z" />
                            <path d="M9 15L4 20M2 22l3-1-2-2-1 3Z" />
                            <circle cx="15.5" cy="8.5" r="1.2" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <span x-text="t('navSpeed')"><?php esc_html_e('Speed & Cache Engine', 'bankai-core'); ?></span>
                    <span class="bankai-nav-badge" style="background: #F6F8FA; color: #656D76; border: 1px solid #D0D7DE;">0.8s</span>
                </button>

                <button id="nav-tab-media"
                        class="bankai-nav-btn"
                        :class="activeTab === 'media' ? 'active' : ''"
                        @click="activeTab = 'media'; setTab('media')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 22h6c4.418 0 6-1.582 6-6V8c0-4.418-1.582-6-6-6H9C4.582 2 3 3.582 3 8v8c0 4.418 1.582 6 6 6Z" />
                            <path d="M2.5 15l4.5-4c1.172-1.041 2.828-1.041 4 0l6 5.5" />
                            <path d="M15 14l2-2c1.077-.957 2.65-.98 3.75-.054L22 13" />
                            <circle cx="8.5" cy="7.5" r="1.5" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <span x-text="t('navMedia')"><?php esc_html_e('Media & Watermark', 'bankai-core'); ?></span>
                </button>
            </div>

            <div class="bankai-nav-group" style="margin-bottom: 0;">
                <div class="bankai-nav-group-title" x-text="t('navIntelligence')">Intelligence & Admin</div>

                <button id="nav-tab-ai"
                        class="bankai-nav-btn"
                        :class="activeTab === 'ai' ? 'active' : ''"
                        @click="activeTab = 'ai'; setTab('ai')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v3m0 14v3M2 12h3m14 0h3M4.93 4.93l2.12 2.12m9.9 9.9l2.12 2.12M4.93 19.07l2.12-2.12m9.9-9.9l2.12-2.12" />
                            <path d="M15.5 12a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" />
                        </svg>
                    </div>
                    <span x-text="t('navAi')"><?php esc_html_e('AI Studio & Manifests', 'bankai-core'); ?></span>
                    <span class="bankai-nav-badge" style="background: #FBEFFF; color: #8250DF; border: 1px solid #D2A8FF;">AI 2.0</span>
                </button>

                <button id="nav-tab-settings"
                        class="bankai-nav-btn"
                        :class="activeTab === 'settings' ? 'active' : ''"
                        @click="activeTab = 'settings'; setTab('settings')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                            <path d="M9 12l2 2 4-4" />
                        </svg>
                    </div>
                    <span x-text="t('navSettings')"><?php esc_html_e('Settings & License', 'bankai-core'); ?></span>
                </button>
            </div>
        </nav>
    </div>

    <!-- Telemetry Sidebar Footer -->
    <div style="background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 10px; padding: 12px; margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="position: relative; display: flex; height: 8px; width: 8px;">
                    <span style="position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 50%; background-color: #1A7F37; opacity: 0.75; animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></span>
                    <span style="position: relative; display: inline-flex; border-radius: 50%; height: 8px; width: 8px; background-color: #1A7F37;"></span>
                </span>
                <span style="font-size: 11px; font-weight: 700; color: #1F2328;" x-text="t('allSystemsNormal')"><?php esc_html_e('All Systems Normal', 'bankai-core'); ?></span>
            </div>
            <span style="font-size: 10px; font-weight: 700; color: #0969DA; background: #DDF4FF; padding: 1px 6px; border-radius: 6px; border: 1px solid #54AEFF;">
                PHP <?php echo esc_html(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION); ?>
            </span>
        </div>

        <div style="margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between; font-size: 10px; font-weight: 600; color: #656D76; margin-bottom: 4px;">
                <span x-text="t('memoryLimit')"><?php esc_html_e('Memory Limit', 'bankai-core'); ?></span>
                <span style="font-family: monospace; color: #0969DA; font-weight: 700;">
                    <?php echo esc_html(size_format(wp_memory_get_usage()) . ' / ' . ini_get('memory_limit')); ?>
                </span>
            </div>
            <div style="width: 100%; height: 5px; background-color: #D0D7DE; border-radius: 3px; overflow: hidden;">
                <div style="width: 50%; height: 100%; background: linear-gradient(90deg, #218BFF, #0969DA); border-radius: 3px;"></div>
            </div>
        </div>

        <div style="padding-top: 8px; border-top: 1px solid #D0D7DE; display: flex; flex-direction: column; gap: 6px; font-size: 11px;">
            <div style="display: flex; justify-content: space-between; color: #656D76;">
                <span style="display: flex; align-items: center; gap: 4px;">
                    <svg class="solar-icon solar-icon-sm" style="width: 13px; height: 13px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="5" rx="9" ry="3" />
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                    </svg>
                    <span x-text="t('database')"><?php esc_html_e('Database:', 'bankai-core'); ?></span>
                </span>
                <span style="font-weight: 700; color: #1F2328; font-family: monospace;">MySQL 8.0+</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; color: #656D76; padding-top: 2px;">
                <span style="display: flex; align-items: center; gap: 4px;">
                    <svg class="solar-icon solar-icon-sm" style="width: 13px; height: 13px; color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9.5" />
                        <path d="M2.5 12h19M12 2.5a15.3 15.3 0 0 1 4 9.5 15.3 15.3 0 0 1-4 9.5 15.3 15.3 0 0 1 4-9.5Z" />
                    </svg>
                    <span x-text="isRtl ? '<?php echo esc_js(__('زبان رابط:', 'bankai-core')); ?>' : '<?php echo esc_js(__('Language:', 'bankai-core')); ?>'"><?php esc_html_e('Language:', 'bankai-core'); ?></span>
                </span>
                <button @click="toggleLanguage()"
                        type="button"
                        :title="isRtl ? '<?php echo esc_js(__('تغییر به زبان انگلیسی', 'bankai-core')); ?>' : '<?php echo esc_js(__('Switch to Persian', 'bankai-core')); ?>'"
                        style="background: #FFFFFF; border: 1px solid #D0D7DE; color: #0969DA; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 6px; cursor: pointer; transition: all 0.2s;"
                        x-text="isRtl ? 'فارسی (تغییر به EN)' : 'EN (Switch to FA)'">
                    EN (Switch to FA)
                </button>
            </div>
        </div>
    </div>
</aside>