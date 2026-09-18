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
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="1.5"
             stroke-linecap="round"
             stroke-linejoin="round"
             aria-hidden="true">
            <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-show="mobileMenuOpen"
             x-cloak
             class="solar-icon"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="1.5"
             stroke-linecap="round"
             stroke-linejoin="round"
             aria-hidden="true">
            <path d="M18 6L6 18M6 6l12 12" />
        </svg>
    </button>

    <!-- لوگو -->
    <div style="width: 30px; height: 30px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <img src="<?php echo esc_url(bankai_asset_url('images/logo.jpg')); ?>"
             alt="<?php esc_attr_e('Bankai Logo', 'bankai-core'); ?>"
             width="30"
             height="30"
             loading="eager"
             decoding="async"
             style="width: 30px; height: 30px; object-fit: contain; display: block; border-radius: 6px;"
             onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 40 40\'%3E%3Crect width=\'40\' height=\'40\' rx=\'8\' fill=\'%230969DA\'/%3E%3Ctext x=\'20\' y=\'26\' font-size=\'18\' font-weight=\'bold\' fill=\'%23FFFFFF\' text-anchor=\'middle\'%3EB%3C/text%3E%3C/svg%3E';">
    </div>

    <!-- عنوان برند -->
    <div style="display: flex; align-items: baseline; gap: 6px; line-height: 1;">
        <h1 style="font-size: 14px; font-weight: 700; color: #1F2328; margin: 0; letter-spacing: 0.3px;">
            BANKAI
        </h1>
        <span style="color: #0969DA; font-size: 10px; font-weight: 800; letter-spacing: 0.4px;">
            CORE
        </span>
    </div>
</div>

    <div class="bankai-header-actions" style="display: flex; align-items: center; gap: 4px;">
        <a href="<?php echo esc_url(admin_url('admin.php?page=bankai-theme')); ?>"
           id="header-theme-link"
           class="bankai-btn-ghost"
           style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600;"
           onmouseover="this.style.color='#8250DF'"
           onmouseout="this.style.color='#656D76'">
            <span style="width: 7px; height: 7px; border-radius: 50%; background: #8250DF; flex-shrink: 0;"></span>
            <span x-text="isRtl ? '<?php echo esc_js(__('قالب Bankai', 'bankai-core')); ?>' : '<?php echo esc_js(__('Bankai Theme', 'bankai-core')); ?>'">
                <?php esc_html_e('Bankai Theme', 'bankai-core'); ?>
            </span>
        </a>

        <a href="<?php echo esc_url(home_url('/')); ?>"
           id="header-preview-link"
           target="_blank" rel="noopener noreferrer"
           class="bankai-btn-ghost"
           style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600;"
           onmouseover="this.style.color='#0969DA'"
           onmouseout="this.style.color='#656D76'">
            <svg class="solar-icon solar-icon-sm" style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <span x-text="isRtl ? '<?php echo esc_js(__('پیش‌نمایش سایت', 'bankai-core')); ?>' : '<?php echo esc_js(__('Site Preview', 'bankai-core')); ?>'">
                <?php esc_html_e('Site Preview', 'bankai-core'); ?>
            </span>
        </a>

       <!-- تخلیه کش — آیکون Refresh/Clear مناسب -->
        <button type="button"
                id="btn-purge-cache"
                class="bankai-btn-ghost"
                @click="purgeAllCaches()"
                :title="isRtl ? '<?php echo esc_js(__('تخلیه تمام کش‌ها', 'bankai-core')); ?>' : '<?php echo esc_js(__('Purge All Caches', 'bankai-core')); ?>'"
                style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #656D76; padding: 6px 10px; border-radius: 6px; cursor: pointer; background: transparent; border: none;"
                onmouseover="this.style.color='#0969DA'"
                onmouseout="this.style.color='#656D76'">
            <!-- آیکون: دایره با فلش چرخشی (Clear / Refresh Cache) -->
            <svg class="solar-icon solar-icon-sm" style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 12a9 9 0 1 1-3-6.7" />
                <polyline points="21 3 21 9 15 9" />
            </svg>
            <span x-text="t('purgeCache')"><?php esc_html_e('Purge Cache', 'bankai-core'); ?></span>
        </button>

        <!-- <button type="button"
                id="btn-toggle-lang"
                class="bankai-btn-ghost"
                @click="toggleLanguage()"
                :title="isRtl ? '<?php echo esc_js(__('تغییر به انگلیسی', 'bankai-core')); ?>' : '<?php echo esc_js(__('Switch to Persian', 'bankai-core')); ?>'"
                style="font-size: 12px; font-weight: 700; cursor: pointer;"
                onmouseover="this.style.color='#0969DA'"
                onmouseout="this.style.color='#656D76'">
            <span x-text="isRtl ? 'EN' : 'فا'">EN</span>
        </button> -->
    </div>
</header>