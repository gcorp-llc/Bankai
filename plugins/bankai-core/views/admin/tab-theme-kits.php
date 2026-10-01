<!-- Tab: Theme Kits & Customizer -->
<div id="tab-theme-kits" class="bankai-tab-pane" style="width:100%;max-width:100%;box-sizing:border-box">

    <!-- Header -->
    <div class="bankai-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                    <polyline points="2 17 12 22 22 17"/>
                    <polyline points="2 12 12 17 22 12"/>
                </svg>
                <span><?php echo is_rtl() ? 'کیت‌های طراحی (Design Kits)' : 'Bankai Starter Kits'; ?></span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;">
                <?php echo is_rtl() ? 'کیت‌های طراحی آماده و قالب‌های نصب با ۱ کلیک' : '1-Click turnkey site architectures and high-performance design presets.'; ?>
            </p>
        </div>
    </div>

    <!-- Starter Kits Grid -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #1F2328; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            <span><?php echo is_rtl() ? 'قالب‌های آماده و معماری‌های قابل نصب' : 'Available Starter Kits'; ?></span>
        </h3>

        <div class="bankai-grid-3">
            <?php if (!empty($state['starterKits']) && is_array($state['starterKits'])): ?>
                <?php foreach ($state['starterKits'] as $kit):
                    $kit_name  = esc_attr($kit['name'] ?? '');
                    $kit_desc  = esc_html($kit['description'] ?? '');
                    $kit_thumb = esc_url($kit['thumbnail'] ?? '');
                ?>
                    <div class="bankai-card bankai-card-interactive" style="overflow: hidden; display: flex; flex-direction: column;">
                        <div style="position: relative; height: 180px; overflow: hidden; background-color: #D0D7DE;">
                            <img src="<?php echo $kit_thumb; ?>"
                                 alt="<?php echo $kit_name; ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;"
                                 loading="lazy"
                                 decoding="async">
                        </div>

                        <div style="padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 style="font-size: 16px; font-weight: 800; color: #1F2328; margin: 0 0 8px 0;">
                                    <?php echo esc_html($kit['name'] ?? ''); ?>
                                </h4>
                                <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0 0 16px 0;">
                                    <?php echo $kit_desc; ?>
                                </p>
                            </div>

                            <div style="display: flex; gap: 10px; margin-top: 12px;">
                                <button type="button"
                                        class="btn-import-theme-kit"
                                        data-kit="<?php echo $kit_name; ?>"
                                        style="flex: 1; background-color: #0969DA; border: none; color: #FFFFFF; padding: 9px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    <span><?php echo is_rtl() ? 'نصب کیت' : 'Import Kit'; ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-import-theme-kit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var kitName = this.getAttribute('data-kit');
            if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('در حال نصب کیت ' + kitName + '...', 'info');

            var body = new FormData();
            body.append('action', 'bankai_import_theme_kit');
            body.append('nonce', (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || '');
            body.append('kit', kitName);

            fetch(window.bankaiCoreData && window.bankaiCoreData.ajaxUrl ? window.bankaiCoreData.ajaxUrl : '/wp-admin/admin-ajax.php', {
                method: 'POST', body: body, credentials: 'same-origin'
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('کیت ' + kitName + ' با موفقیت نصب شد', 'success');
            });
        });
    });
});
</script>
