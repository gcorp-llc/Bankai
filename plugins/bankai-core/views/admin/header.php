<?php
defined('ABSPATH') || exit;
?>
<header id="bankai-admin-header">
    <div style="display: flex; align-items: center; gap: 12px;">
        <!-- دکمه منوی موبایل -->
        <button type="button"
                id="btn-mobile-menu"
                class="bankai-mobile-btn"
                @click="toggleMobileMenu()"
                :title="isRtl ? '<?php echo esc_js(__('باز و بسته کردن منو', 'bankai-core')); ?>' : '<?php echo esc_js(__('Toggle navigation menu', 'bankai-core')); ?>'"
                aria-label="<?php esc_attr_e('Toggle navigation menu', 'bankai-core'); ?>">
            <svg x-show="!mobileMenuOpen"
                 class="solar-icon"
                 width="20"
                 height="20"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.5"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 style="display: block; flex-shrink: 0;"
                 aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="mobileMenuOpen"
                 x-cloak
                 class="solar-icon"
                 width="20"
                 height="20"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.5"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 style="display: block; flex-shrink: 0;"
                 aria-hidden="true">
                <path d="M18 6L6 18M6 6l12 12" />
            </svg>
        </button>

        <!-- لوگو -->
        <div style="width: 32px; height: 32px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
            <img src="<?php echo esc_url(bankai_asset_url('images/logo.jpg')); ?>"
                 alt="<?php esc_attr_e('Bankai Logo', 'bankai-core'); ?>"
                 width="32"
                 height="32"
                 loading="eager"
                 decoding="async"
                 style="width: 32px; height: 32px; object-fit: contain; display: block; border-radius: 6px;"
                 onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 40 40\'%3E%3Crect width=\'40\' height=\'40\' rx=\'8\' fill=\'%230969DA\'/%3E%3Ctext x=\'20\' y=\'26\' font-size=\'18\' font-weight=\'bold\' fill=\'%23FFFFFF\' text-anchor=\'middle\'%3EB%3C/text%3E%3C/svg%3E';">
        </div>

        <!-- عنوان برند (CORE زیر BANKAI) -->
        <div style="display: flex; flex-direction: column; justify-content: center; line-height: 1;">
            <h1 style="font-size: 13px; font-weight: 800; color: #1F2328; margin: 0; letter-spacing: 0.5px;">
                BANKAI
            </h1>
            <span style="color: #0969DA; font-size: 9px; font-weight: 800; letter-spacing: 0.8px; margin-top: 2px;">
                CORE
            </span>
        </div>
    </div>

    <!-- دکمه‌های اقدام هدر (دارای رنگ‌بندی و هوشمند در موبایل) -->
    <div class="bankai-header-actions">
        <!-- قالب Bankai -->
        <a href="<?php echo esc_url(admin_url('admin.php?page=bankai-theme')); ?>"
           id="header-theme-link"
           class="bankai-btn-action bankai-btn-theme"
           :title="isRtl ? '<?php echo esc_js(__('قالب Bankai', 'bankai-core')); ?>' : '<?php echo esc_js(__('Bankai Theme', 'bankai-core')); ?>'">
            <svg class="solar-icon solar-icon-sm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display: block; flex-shrink: 0;">
                <rect x="2" y="3" width="20" height="14" rx="2" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
            </svg>
            <span class="btn-label" x-text="isRtl ? '<?php echo esc_js(__('قالب Bankai', 'bankai-core')); ?>' : '<?php echo esc_js(__('Bankai Theme', 'bankai-core')); ?>'">
                <?php esc_html_e('Bankai Theme', 'bankai-core'); ?>
            </span>
        </a>

        <!-- پیش‌نمایش سایت -->
        <a href="<?php echo esc_url(home_url('/')); ?>"
           id="header-preview-link"
           target="_blank" rel="noopener noreferrer"
           class="bankai-btn-action bankai-btn-preview"
           :title="isRtl ? '<?php echo esc_js(__('پیش‌نمایش سایت', 'bankai-core')); ?>' : '<?php echo esc_js(__('Site Preview', 'bankai-core')); ?>'">
            <svg class="solar-icon solar-icon-sm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display: block; flex-shrink: 0;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10z"/>
            </svg>
            <span class="btn-label" x-text="isRtl ? '<?php echo esc_js(__('پیش‌نمایش سایت', 'bankai-core')); ?>' : '<?php echo esc_js(__('Site Preview', 'bankai-core')); ?>'">
                <?php esc_html_e('Site Preview', 'bankai-core'); ?>
            </span>
        </a>

        <!-- تخلیه کش -->
        <button type="button"
                id="btn-purge-cache"
                class="bankai-btn-action bankai-btn-purge"
                @click="purgeAllCaches()"
                :title="isRtl ? '<?php echo esc_js(__('تخلیه تمام کش‌ها', 'bankai-core')); ?>' : '<?php echo esc_js(__('Purge All Caches', 'bankai-core')); ?>'">
            <svg class="solar-icon solar-icon-sm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display: block; flex-shrink: 0;">
                <path d="M21 12a9 9 0 1 1-3-6.7" />
                <polyline points="21 3 21 9 15 9" />
            </svg>
            <span class="btn-label" x-text="t('purgeCache')"><?php esc_html_e('Purge Cache', 'bankai-core'); ?></span>
        </button>
    </div>
</header>