<?php
/**
 * Bankai Core Admin Sidebar Modular Template
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$is_rtl = is_rtl();
?>
<aside class="bankai-sidebar">
    <div>
        <!-- Brand / Logo -->
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 32px; padding: 0 8px;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #10B981, #6366F1); display: flex; align-items: center; justify-content: center; font-weight: 900; color: #FFFFFF; font-size: 18px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                B
            </div>
            <div>
                <div style="font-weight: 800; color: #F8FAFC; font-size: 15px; letter-spacing: 0.5px;">
                    <?php echo $is_rtl ? 'سامانه بانکای' : 'BANKAI ARCH'; ?>
                </div>
                <div style="font-size: 10px; color: #10B981; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                    <?php echo $is_rtl ? 'موتور قدرتمند SSR + Alpine' : 'SSR + Alpine Engine'; ?>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav style="display: flex; flex-direction: column; gap: 6px;">
            <button @click="setTab('overview')"
                    :style="activeTab === 'overview' ? 'background-color: #1E2D4A; color: #F8FAFC; ' + (isRtl ? 'border-right: 3px solid #10B981;' : 'border-left: 3px solid #10B981;') : 'color: #94A3B8;'"
                    style="display: flex; align-items: center; gap: 12px; width: 100%; text-align: inherit; padding: 10px 14px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                <span style="font-size: 16px;">📊</span>
                <span><?php echo $is_rtl ? 'داشبورد و سلامت سیستم' : esc_html__( 'Overview & Health', 'bankai-core' ); ?></span>
            </button>

            <button @click="setTab('theme-kits')"
                    :style="activeTab === 'theme-kits' ? 'background-color: #1E2D4A; color: #F8FAFC; ' + (isRtl ? 'border-right: 3px solid #10B981;' : 'border-left: 3px solid #10B981;') : 'color: #94A3B8;'"
                    style="display: flex; align-items: center; gap: 12px; width: 100%; text-align: inherit; padding: 10px 14px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                <span style="font-size: 16px;">🎨</span>
                <span><?php echo $is_rtl ? 'قالب‌ها و کیت‌های آماده' : esc_html__( 'Theme & Kits', 'bankai-core' ); ?></span>
            </button>

            <button @click="setTab('seo')"
                    :style="activeTab === 'seo' ? 'background-color: #1E2D4A; color: #F8FAFC; ' + (isRtl ? 'border-right: 3px solid #10B981;' : 'border-left: 3px solid #10B981;') : 'color: #94A3B8;'"
                    style="display: flex; align-items: center; gap: 12px; width: 100%; text-align: inherit; padding: 10px 14px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                <span style="font-size: 16px;">🔍</span>
                <span><?php echo $is_rtl ? 'موتور هوشمند سئو' : esc_html__( 'SEO Engine', 'bankai-core' ); ?></span>
            </button>

            <button @click="setTab('speed')"
                    :style="activeTab === 'speed' ? 'background-color: #1E2D4A; color: #F8FAFC; ' + (isRtl ? 'border-right: 3px solid #10B981;' : 'border-left: 3px solid #10B981;') : 'color: #94A3B8;'"
                    style="display: flex; align-items: center; gap: 12px; width: 100%; text-align: inherit; padding: 10px 14px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                <span style="font-size: 16px;">⚡</span>
                <span><?php echo $is_rtl ? 'سرعت و مدیریت کش' : esc_html__( 'Speed & Cache', 'bankai-core' ); ?></span>
            </button>

            <button @click="setTab('media')"
                    :style="activeTab === 'media' ? 'background-color: #1E2D4A; color: #F8FAFC; ' + (isRtl ? 'border-right: 3px solid #10B981;' : 'border-left: 3px solid #10B981;') : 'color: #94A3B8;'"
                    style="display: flex; align-items: center; gap: 12px; width: 100%; text-align: inherit; padding: 10px 14px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                <span style="font-size: 16px;">🖼️</span>
                <span><?php echo $is_rtl ? 'رسانه و واترمارک' : esc_html__( 'Media & Watermark', 'bankai-core' ); ?></span>
            </button>

            <button @click="setTab('ai')"
                    :style="activeTab === 'ai' ? 'background-color: #1E2D4A; color: #F8FAFC; ' + (isRtl ? 'border-right: 3px solid #10B981;' : 'border-left: 3px solid #10B981;') : 'color: #94A3B8;'"
                    style="display: flex; align-items: center; gap: 12px; width: 100%; text-align: inherit; padding: 10px 14px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                <span style="font-size: 16px;">🤖</span>
                <span><?php echo $is_rtl ? 'استودیو هوش مصنوعی' : esc_html__( 'AI & Manifests', 'bankai-core' ); ?></span>
            </button>

            <button @click="setTab('settings')"
                    :style="activeTab === 'settings' ? 'background-color: #1E2D4A; color: #F8FAFC; ' + (isRtl ? 'border-right: 3px solid #10B981;' : 'border-left: 3px solid #10B981;') : 'color: #94A3B8;'"
                    style="display: flex; align-items: center; gap: 12px; width: 100%; text-align: inherit; padding: 10px 14px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                <span style="font-size: 16px;">🛡️</span>
                <span><?php echo $is_rtl ? 'تنظیمات و لایسنس' : esc_html__( 'Settings & License', 'bankai-core' ); ?></span>
            </button>
        </nav>
    </div>

    <!-- Footer info in sidebar -->
    <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 14px; margin-top: 24px;">
        <div style="font-size: 11px; color: #94A3B8; font-weight: 600; margin-bottom: 4px; display: flex; justify-content: space-between;">
            <span><?php echo $is_rtl ? 'حافظه PHP:' : 'PHP Memory:'; ?></span>
            <span style="color: #10B981;">256M / 512M</span>
        </div>
        <div style="font-size: 11px; color: #94A3B8; font-weight: 600; display: flex; justify-content: space-between;">
            <span><?php echo $is_rtl ? 'حالت سیستم:' : 'Mode:'; ?></span>
            <span style="color: #38BDF8;"><?php echo $is_rtl ? 'فارسی (RTL)' : 'English (LTR)'; ?></span>
        </div>
    </div>
</aside>
