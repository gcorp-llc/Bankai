<!-- Tab: Settings & License Management -->
<div id="tab-settings-license" class="bankai-tab-pane" style="width:100%;max-width:100%;box-sizing:border-box">

    <!-- Header -->
    <div class="bankai-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px; flex-wrap: wrap;">
                <h2 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <polyline points="9 12 11 14 15 10" />
                    </svg>
                    <span><?php echo is_rtl() ? 'تنظیمات و لایسنس (Settings &amp; License)' : 'Platform Settings &amp; License'; ?></span>
                </h2>
                <span style="background-color: rgba(31, 136, 61, 0.2); border: 1px solid rgba(31, 136, 61, 0.5); color: #1A7F37; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 700;">
                    ✓ <span><?php echo is_rtl() ? 'نسخه حرفه‌ای فعال' : 'Verified Pro Enterprise'; ?></span>
                </span>
            </div>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo is_rtl() ? 'تنظیمات کلی، لایسنس پلتفرم و گزارش‌های عیب‌یابی سیستم' : 'Global feature flags, license activation &amp; system diagnostics.'; ?>
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button"
                    id="btn-save-settings"
                    class="btn-save-settings"
                    style="background-color: #0969DA; border: 1px solid #0969DA; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 1px 3px rgba(9, 105, 218, 0.3);">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                <span><?php echo is_rtl() ? 'ذخیره تمامی تنظیمات' : 'Save All Settings'; ?></span>
            </button>
        </div>
    </div>

    <!-- License Card -->
    <div class="bankai-card" style="padding: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background-color: rgba(9, 105, 218, 0.12); border: 1px solid rgba(9, 105, 218, 0.3); display: flex; align-items: center; justify-content: center; color: #0969DA;">
                <svg class="solar-icon solar-icon-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="8" r="7" />
                    <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
                </svg>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h3 style="font-size: 16px; font-weight: 800; color: #1F2328; margin: 0;">
                        <?php echo is_rtl() ? 'لایسنس فعال نسخه تجاری پلتفرم' : 'Bankai Core Pro License Key'; ?>
                    </h3>
                    <span style="background-color: rgba(31, 136, 61, 0.25); color: #1A7F37; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(31, 136, 61, 0.5);">
                        ACTIVE &amp; VERIFIED
                    </span>
                </div>
                <div style="font-size: 12px; color: #8C959F; margin-top: 4px;">
                    <span><?php echo is_rtl() ? 'کد لایسنس:' : 'Key:'; ?></span>
                    <code style="color: #0969DA; background-color: #F6F8FA; border: 1px solid #D0D7DE; padding: 2px 6px; border-radius: 4px; font-family: monospace;">BNK-PRO-8849-2049-9941-X9</code>
                    &bull; <span>Lifetime Enterprise</span>
                </div>
            </div>
        </div>
    </div>

    <!-- System Diagnostic -->
    <div class="bankai-card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                </svg>
                <span><?php echo is_rtl() ? 'گزارش عیب‌یابی سیستم' : 'System Diagnostic Log Report'; ?></span>
            </h3>
        </div>

        <textarea readonly rows="8"
                  style="width: 100%; background-color: #F6F8FA; border: 1px solid #D0D7DE; color: #656D76; font-family: monospace; font-size: 12px; padding: 12px; border-radius: 8px; line-height: 1.6; resize: none; direction: ltr; text-align: left;"><?php echo esc_textarea($state['systemReport'] ?? ''); ?></textarea>
    </div>
</div>
