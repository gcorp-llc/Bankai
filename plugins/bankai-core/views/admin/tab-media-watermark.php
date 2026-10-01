<?php
/**
 * Tab: Media & Watermark Studio
 */
defined('ABSPATH') || exit;

/** @var array $state */
$media_mods = is_array($state['mediaModules'] ?? null) ? $state['mediaModules'] : [];
$wm = is_array($state['watermarkSettings'] ?? null) ? $state['watermarkSettings'] : [];
if (!$wm && class_exists('Bankai_Media_Watermark')) {
    $wm = Bankai_Media_Watermark::instance()->get_settings();
}
$env = class_exists('Bankai_Media_Watermark') ? Bankai_Media_Watermark::environment() : [];
$gd_ok = !empty($env['gd']);
$imagick_ok = !empty($env['imagick']);
$webp_ok = !empty($env['webp']);

$wm_text = esc_attr($wm['text'] ?? '© BANKAI MEDIA');
$wm_opacity = (int) ($wm['opacity'] ?? 75);
$wm_quality = (int) ($wm['quality'] ?? 82);
$wm_pos = esc_attr($wm['position'] ?? 'bottom-right');
$wm_enabled = !isset($wm['enabled']) || !empty($wm['enabled']);

$positions = [
    'top-left'      => 'بالا چپ',
    'top-center'    => 'بالا وسط',
    'top-right'     => 'بالا راست',
    'center-left'   => 'وسط چپ',
    'center'        => 'مرکز',
    'center-right'  => 'وسط راست',
    'bottom-left'   => 'پایین چپ',
    'bottom-center' => 'پایین وسط',
    'bottom-right'  => 'پایین راست',
];
?>
<div id="tab-media-watermark" class="bankai-tab-pane bk-media-shell">

    <!-- Page header -->
    <div class="bk-media-page-head bankai-card">
        <div>
            <h2 class="bk-media-title">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="14" height="14" rx="2"/><path d="M21 9v10a2 2 0 0 1-2 2H7"/><circle cx="9" cy="11" r="1.5"/><path d="M3 15l4-3 3 2 4-4 3 3"/></svg>
                <span><?php echo is_rtl() ? 'تصاویر و واترمارک (Media &amp; Watermark)' : 'Media &amp; Watermark Studio'; ?></span>
            </h2>
            <p class="bk-media-sub">WebP · فشرده‌سازی · واترمارک · Lazy Load</p>
        </div>
        <div class="bk-media-head-actions">
            <button type="button" class="bankai-btn-ghost" id="btn-regen-thumbs">
                بازتولید بندانگشتی
            </button>
        </div>
    </div>

    <!-- Env chips -->
    <div class="bk-media-env-row">
        <div class="bk-env-chip <?php echo $gd_ok ? 'is-ok' : 'is-bad'; ?>">
            GD
        </div>
        <div class="bk-env-chip <?php echo $imagick_ok ? 'is-ok' : 'is-muted'; ?>">
            Imagick
        </div>
        <div class="bk-env-chip <?php echo $webp_ok ? 'is-ok' : 'is-bad'; ?>">
            WebP
        </div>
    </div>

    <!-- Watermark Settings Form -->
    <div class="bk-wm-studio bankai-card">
        <div class="bk-wm-studio-head">
            <h3>تنظیمات واترمارک و تصاویر</h3>
            <label class="bk-switch-inline">
                <input type="checkbox" id="input-wm-enabled" <?php checked($wm_enabled); ?>>
                <span>فعال‌سازی واترمارک</span>
            </label>
        </div>

        <div class="bk-wm-tools">
            <div class="bk-wm-tool-block">
                <label class="bk-wm-label">متن واترمارک</label>
                <input type="text" class="bk-wm-input" id="input-wm-text" value="<?php echo $wm_text; ?>" maxlength="80" placeholder="© BANKAI">
            </div>

            <div class="bk-wm-tool-block">
                <label class="bk-wm-label">موقعیت درج واترمارک</label>
                <select id="select-wm-pos" class="bk-wm-input">
                    <?php foreach ($positions as $pos => $lbl): ?>
                        <option value="<?php echo esc_attr($pos); ?>" <?php selected($wm_pos, $pos); ?>><?php echo esc_html($lbl); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bk-wm-sliders">
                <div class="bk-wm-slider-item">
                    <div class="bk-wm-slider-head">
                        <span>شفافیت (%)</span>
                    </div>
                    <input type="number" id="input-wm-opacity" min="10" max="100" value="<?php echo $wm_opacity; ?>" class="bk-wm-input">
                </div>
                <div class="bk-wm-slider-item">
                    <div class="bk-wm-slider-head">
                        <span>کیفیت تصویر</span>
                    </div>
                    <input type="number" id="input-wm-quality" min="40" max="100" value="<?php echo $wm_quality; ?>" class="bk-wm-input">
                </div>
            </div>

            <button type="button" class="bankai-btn-primary bk-wm-save" id="btn-save-watermark" style="margin-top:16px;">
                ذخیره تنظیمات واترمارک
            </button>
        </div>
    </div>

    <!-- Modules -->
    <div class="bankai-grid-3" style="margin-top:20px;margin-bottom:32px;">
        <?php foreach ($media_mods as $mod):
            $mod_id = esc_attr($mod['id'] ?? '');
            $title_fa = esc_html($mod['title_fa'] ?? $mod['title'] ?? '');
            $desc_fa = esc_html($mod['description_fa'] ?? $mod['description'] ?? '');
            $badge = esc_html($mod['badge'] ?? '');
            $is_mod_on = !empty($mod['enabled']);
        ?>
        <div class="bankai-card" style="padding:20px;display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div>
                        <div style="font-weight:700;font-size:14px;"><?php echo $title_fa; ?></div>
                        <span class="bk-badge-soft"><?php echo $badge; ?></span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" class="bk-module-toggle" data-id="<?php echo $mod_id; ?>" <?php checked($is_mod_on); ?>>
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size:12px;color:var(--bankai-text-muted);line-height:1.5;margin:0;"><?php echo $desc_fa; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.bk-media-shell { display:flex; flex-direction:column; gap:16px; }
.bk-media-page-head { display:flex; justify-content:space-between; align-items:center; padding:18px 20px; flex-wrap:wrap; gap:12px; border-radius:16px !important; }
.bk-media-title { margin:0; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px; }
.bk-media-sub { margin:4px 0 0; font-size:12px; color:#8a8886; }
.bk-media-env-row { display:flex; flex-wrap:wrap; gap:8px; }
.bk-env-chip { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:999px; font-size:12px; font-weight:700; background:#fff; border:1px solid rgba(0,0,0,.06); }
.bk-env-chip.is-ok { color:#0f7b3a; background:#dff6e8; }
.bk-env-chip.is-bad { color:#c42b1c; background:#fde7e9; }
.bk-wm-studio { padding:20px; border-radius:16px !important; }
.bk-wm-studio-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
.bk-wm-studio-head h3 { margin:0; font-size:15px; font-weight:800; }
.bk-switch-inline { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; cursor:pointer; }
.bk-wm-tools { display:flex; flex-direction:column; gap:16px; }
.bk-wm-label { display:block; font-size:11px; font-weight:700; color:#605e5c; margin-bottom:8px; }
.bk-wm-input { width:100%; padding:11px 14px; border-radius:10px; border:1px solid rgba(0,0,0,.08); background:#fafafa; font-size:13px; box-sizing:border-box; }
.bk-wm-sliders { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.bk-wm-slider-head { display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px; color:#605e5c; }
.bk-badge-soft { background:rgba(0,120,212,.1); color:#0078d4; font-size:10px; font-weight:700; padding:2px 8px; border-radius:999px; display:inline-block; margin-top:4px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btnSaveWm = document.getElementById('btn-save-watermark');
    if (btnSaveWm) {
        btnSaveWm.addEventListener('click', function() {
            var body = new FormData();
            body.append('action', 'bankai_save_watermark_settings');
            body.append('nonce', (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || '');
            body.append('enabled', document.getElementById('input-wm-enabled') && document.getElementById('input-wm-enabled').checked ? '1' : '0');
            body.append('text', document.getElementById('input-wm-text') ? document.getElementById('input-wm-text').value : '');
            body.append('position', document.getElementById('select-wm-pos') ? document.getElementById('select-wm-pos').value : 'bottom-right');
            body.append('opacity', document.getElementById('input-wm-opacity') ? document.getElementById('input-wm-opacity').value : '75');
            body.append('quality', document.getElementById('input-wm-quality') ? document.getElementById('input-wm-quality').value : '82');

            fetch(window.bankaiCoreData && window.bankaiCoreData.ajaxUrl ? window.bankaiCoreData.ajaxUrl : '/wp-admin/admin-ajax.php', {
                method: 'POST', body: body, credentials: 'same-origin'
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('تنظیمات واترمارک ذخیره شد', 'success');
            });
        });
    }

    var btnRegen = document.getElementById('btn-regen-thumbs');
    if (btnRegen) {
        btnRegen.addEventListener('click', function() {
            if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('در حال بازتولید بندانگشتی‌ها…', 'info');
            var body = new FormData();
            body.append('action', 'bankai_regenerate_thumbs');
            body.append('nonce', (window.bankaiCoreData && window.bankaiCoreData.adminNonce) || '');

            fetch(window.bankaiCoreData && window.bankaiCoreData.ajaxUrl ? window.bankaiCoreData.ajaxUrl : '/wp-admin/admin-ajax.php', {
                method: 'POST', body: body, credentials: 'same-origin'
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('بازتولید بندانگشتی‌ها کامل شد', 'success');
            });
        });
    }
});
</script>
