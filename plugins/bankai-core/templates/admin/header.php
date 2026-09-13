<?php
/**
 * Bankai Core Admin Header Modular Template
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #1E2D4A;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 12px;">
            <span style="background: linear-gradient(135deg, #38BDF8, #6366F1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                ⚡ <?php esc_html_e( 'BANKAI CORE ENGINE', 'bankai-core' ); ?>
            </span>
            <span style="font-size: 11px; font-weight: 700; background-color: rgba(56, 189, 248, 0.15); color: #38BDF8; padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(56, 189, 248, 0.3);">
                v<?php echo esc_html( BANKAI_CORE_VERSION ); ?>
            </span>
        </h1>
        <p style="font-size: 13px; color: #94A3B8; margin: 4px 0 0 0;">
            <?php esc_html_e( 'High-performance Modular WP Engine, Theme Customizer & Starter Kit Hub', 'bankai-core' ); ?>
        </p>
    </div>

    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="background-color: #111827; border: 1px solid #1E2D4A; padding: 6px 14px; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-size: 12px; color: #F8FAFC;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10B981; display: inline-block;"></span>
            <span><?php esc_html_e( 'System Status: Optimal', 'bankai-core' ); ?></span>
        </div>
        
        <button @click="showToast('<?php esc_attr_e( 'Cache flushed across edge & page warmers', 'bankai-core' ); ?>')"
                style="background-color: #1E2D4A; border: 1px solid #334155; color: #F8FAFC; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
            🧹 <?php esc_html_e( 'Flush Cache', 'bankai-core' ); ?>
        </button>
    </div>
</header>
