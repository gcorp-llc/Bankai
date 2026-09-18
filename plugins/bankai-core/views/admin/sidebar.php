<?php
defined('ABSPATH') || exit;
?>
<aside id="bankai-admin-sidebar"
       class="bankai-sidebar"
       :class="{ 'mobile-open': mobileMenuOpen }">

    <div class="bankai-sidebar-content">
        <!-- Header موبایل (هنگام باز شدن منو در موبایل) -->
        <div class="bankai-sidebar-mobile-header"
             x-show="mobileMenuOpen"
             x-cloak>
            <div class="bankai-mobile-title">
                <div class="bankai-mobile-icon-box">
                    <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M4 6h16M4 12h10M4 18h16" />
                    </svg>
                </div>
                <span x-text="isRtl ? '<?php echo esc_js(__('منوی مدیریت', 'bankai-core')); ?>' : '<?php echo esc_js(__('Navigation', 'bankai-core')); ?>'">
                    <?php esc_html_e('Navigation', 'bankai-core'); ?>
                </span>
            </div>
            <button type="button"
                    class="bankai-mobile-close-btn"
                    @click="closeMobileMenu()"
                    aria-label="<?php esc_attr_e('Close navigation', 'bankai-core'); ?>">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Brand Box -->
        <div class="bankai-brand-card">
            <div class="bankai-brand-logo">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M13.5 2L4 13.5h7L9.5 22 20 10.5h-7.5L13.5 2Z" />
                </svg>
            </div>
            <div class="bankai-brand-info">
                <div class="bankai-brand-title">BANKAI CORE</div>
                <div class="bankai-brand-status">
                    <span class="status-dot"></span>
                    <span x-text="isRtl ? '<?php echo esc_js(__('نسخه تجاری فعال', 'bankai-core')); ?>' : '<?php echo esc_js(__('Active Enterprise', 'bankai-core')); ?>'">
                        <?php esc_html_e('Active Enterprise', 'bankai-core'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="bankai-nav-menu" aria-label="<?php esc_attr_e('Bankai Admin Navigation', 'bankai-core'); ?>">

            <!-- گروه ۱: معماری اصلی -->
            <div class="bankai-nav-group">
                <div class="bankai-nav-group-title" x-text="t('navArch')">Core Architecture</div>

                <button type="button"
                        id="nav-tab-overview"
                        class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'overview' }"
                        @click="setTab('overview')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M2.5 9.5V4.5C2.5 3.395 3.395 2.5 4.5 2.5H9.5C10.605 2.5 11.5 3.395 11.5 4.5V9.5C11.5 10.605 10.605 11.5 9.5 11.5H4.5C3.395 11.5 2.5 10.605 2.5 9.5Z" />
                            <path d="M14.5 19.5V14.5C14.5 13.395 15.395 12.5 16.5 12.5H19.5C20.605 12.5 21.5 13.395 21.5 14.5V19.5C21.5 20.605 20.605 21.5 19.5 21.5H16.5C15.395 21.5 14.5 20.605 14.5 19.5Z" />
                            <path d="M2.5 16.5C2.5 14.29 4.29 12.5 6.5 12.5H9.5C10.605 12.5 11.5 13.395 11.5 14.5V19.5C11.5 20.605 10.605 21.5 9.5 21.5H6.5C4.29 21.5 2.5 19.71 2.5 17.5V16.5Z" />
                            <path d="M14.5 4.5C14.5 3.395 15.395 2.5 16.5 2.5H19.5C20.605 2.5 21.5 3.395 21.5 4.5V7.5C21.5 9.71 19.71 11.5 17.5 11.5H16.5C15.395 11.5 14.5 10.605 14.5 9.5V4.5Z" />
                        </svg>
                    </div>
                    <span x-text="t('navOverview')"><?php esc_html_e('Overview & Telemetry', 'bankai-core'); ?></span>
                </button>

                <button type="button"
                        id="nav-tab-theme-kits"
                        class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'theme-kits' }"
                        @click="setTab('theme-kits')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M12 22C6.477 22 2 17.523 2 12c0-4.478 2.946-8.267 7-9.535M16.5 3.12C19.832 4.675 22 8.09 22 12c0 3.5-1.5 5.5-4 5.5h-1.5c-1.105 0-2 .895-2 2 0 1.38 1.12 2.5 2.5 2.5" />
                            <circle cx="8" cy="10" r="1.5" fill="currentColor" stroke="none" />
                            <circle cx="12" cy="7" r="1.5" fill="currentColor" stroke="none" />
                            <circle cx="16" cy="10" r="1.5" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <span x-text="t('navThemeKits')"><?php esc_html_e('Theme Kits & Customizer', 'bankai-core'); ?></span>
                </button>

              
            </div>

            <!-- گروه ۲: بهینه‌سازی و سرعت -->
            <div class="bankai-nav-group">
                <div class="bankai-nav-group-title" x-text="t('navPerformance')">Optimization & Speed</div>

                <button type="button"
                        id="nav-tab-seo"
                        class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'seo-engine' }"
                        @click="setTab('seo-engine')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M18.5 18.5L22 22" />
                            <path d="M6.75 3.27A9 9 0 0 1 18.23 6.75M20 11.5a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0Z" />
                        </svg>
                    </div>
                    <span x-text="t('navSeo')"><?php esc_html_e('SEO & Schema Engine', 'bankai-core'); ?></span>
                </button>

                <button type="button"
                        id="nav-tab-speed"
                        class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'speed-cache' }"
                        @click="setTab('speed-cache')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M14.5 9.5L18 6M15.5 15.5L12 19l-3.5-1.5L7 16l-2.5-2.5L3 10l3.5-3.5L10 3l6 3.5 4.5 1.5c.5.167.9.6.9 1.1a12.5 12.5 0 0 1-5.9 7.4Z" />
                            <path d="M9 15L4 20M2 22l3-1-2-2-1 3Z" />
                        </svg>
                    </div>
                    <span x-text="t('navSpeed')"><?php esc_html_e('Speed & Cache Engine', 'bankai-core'); ?></span>
                </button>

                <button type="button"
                        id="nav-tab-media"
                        class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'media-watermark' }"
                        @click="setTab('media-watermark')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M9 22h6c4.418 0 6-1.582 6-6V8c0-4.418-1.582-6-6-6H9C4.582 2 3 3.582 3 8v8c0 4.418 1.582 6 6 6Z" />
                            <path d="M2.5 15l4.5-4c1.172-1.041 2.828-1.041 4 0l6 5.5" />
                            <circle cx="8.5" cy="7.5" r="1.5" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <span x-text="t('navMedia')"><?php esc_html_e('Media & Watermark', 'bankai-core'); ?></span>
                </button>
            </div>

            <!-- گروه ۳: هوش مصنوعی و تنظیمات -->
            <div class="bankai-nav-group" style="margin-bottom: 0;">
                <div class="bankai-nav-group-title" x-text="t('navIntelligence')">Intelligence & Admin</div>

                <button type="button"
                        id="nav-tab-ai"
                        class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'ai-studio' }"
                        @click="setTab('ai-studio')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M12 2v3m0 14v3M2 12h3m14 0h3M4.93 4.93l2.12 2.12m9.9 9.9l2.12 2.12M4.93 19.07l2.12-2.12m9.9-9.9l2.12-2.12" />
                            <path d="M15.5 12a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" />
                        </svg>
                    </div>
                    <span x-text="t('navAi')"><?php esc_html_e('AI Studio & Manifests', 'bankai-core'); ?></span>
                </button>

                <button type="button"
                        id="nav-tab-settings"
                        class="bankai-nav-btn"
                        :class="{ 'active': activeTab === 'settings-license' }"
                        @click="setTab('settings-license')">
                    <div class="bankai-nav-icon">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                            <path d="M9 12l2 2 4-4" />
                        </svg>
                    </div>
                    <span x-text="t('navSettings')"><?php esc_html_e('Settings & License', 'bankai-core'); ?></span>
                </button>
            </div>
        </nav>
    </div>

    <!-- Telemetry Footer -->
    <div class="bankai-telemetry-card">
        <div class="telemetry-header">
            <div class="telemetry-status">
                <span class="ping-container">
                    <span class="ping-pulse"></span>
                    <span class="ping-dot"></span>
                </span>
                <span class="status-title" x-text="t('allSystemsNormal')">
                    <?php esc_html_e('All Systems Normal', 'bankai-core'); ?>
                </span>
            </div>
            <span class="php-badge">
                PHP <?php echo esc_html(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION); ?>
            </span>
        </div>

        <div class="telemetry-progress-wrapper">
            <div class="telemetry-progress-label">
                <span x-text="t('memoryLimit')"><?php esc_html_e('Memory Limit', 'bankai-core'); ?></span>
                <span class="memory-value">
                    <?php
                    $memory_used  = function_exists('memory_get_usage') ? size_format((int) memory_get_usage(true)) : '—';
                    $memory_limit = (string) ini_get('memory_limit');
                    echo esc_html($memory_used . ' / ' . ($memory_limit !== '' ? $memory_limit : '—'));
                    ?>
                </span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill"></div>
            </div>
        </div>

        <div class="telemetry-details">
            <div class="detail-row">
                <span class="detail-label">
                    <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <ellipse cx="12" cy="5" rx="9" ry="3" />
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                    </svg>
                    <span x-text="t('database')"><?php esc_html_e('Database:', 'bankai-core'); ?></span>
                </span>
                <span class="detail-val">MySQL 8.0+</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">
                    <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <circle cx="12" cy="12" r="9.5" />
                        <path d="M2.5 12h19M12 2.5a15.3 15.3 0 0 1 4 9.5 15.3 15.3 0 0 1-4 9.5 15.3 15.3 0 0 1-4-9.5 15.3 15.3 0 0 1 4-9.5Z" />
                    </svg>
                    <span x-text="isRtl ? '<?php echo esc_js(__('زبان رابط:', 'bankai-core')); ?>' : '<?php echo esc_js(__('Language:', 'bankai-core')); ?>'">
                        <?php esc_html_e('Language:', 'bankai-core'); ?>
                    </span>
                </span>
                <button type="button"
                        class="lang-switch-btn"
                        @click="toggleLanguage()"
                        :title="isRtl ? '<?php echo esc_js(__('تغییر به انگلیسی', 'bankai-core')); ?>' : '<?php echo esc_js(__('Switch to Persian', 'bankai-core')); ?>'"
                        x-text="isRtl ? 'FA → EN' : 'EN → FA'">
                    EN → FA
                </button>
            </div>
        </div>
    </div>
</aside>