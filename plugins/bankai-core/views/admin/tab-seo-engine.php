<?php
/**
 * Admin tab: SEO Engine — modules, schema/integrations, fixed keywords
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

/** @var array $state */
$seo_mods = is_array($state['seoModules'] ?? null) ? $state['seoModules'] : [];
$si       = is_array($state['seoIntegrations'] ?? null) ? $state['seoIntegrations'] : [];

$fixed_raw = function_exists('bankai_get_option')
    ? (string) bankai_get_option('seo_fixed_keywords', '')
    : (string) get_option('bankai_seo_fixed_keywords', '');

$ga4     = esc_attr($si['ga4_measurement_id'] ?? $si['google_analytics_id'] ?? '');
$gtm     = esc_attr($si['google_tag_manager_id'] ?? '');
$gsc     = esc_attr($si['google_site_verification'] ?? '');
$bing    = esc_attr($si['bing_webmaster'] ?? '');
$yandex  = esc_attr($si['yandex_verification'] ?? '');
$sitemap = !isset($si['sitemap_enabled']) || !empty($si['sitemap_enabled']);
$robots  = esc_textarea($si['robots_txt'] ?? '');
?>

<div id="tab-seo-engine" class="bankai-tab-pane">

    <!-- Header Card -->
    <div class="bankai-card bk-seo-header">
        <div class="bk-seo-header-main">
            <div class="bk-seo-title-group">
                <div class="bk-seo-title-flex">
                    <h2 class="bk-seo-h2">
                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                        </svg>
                        <span>موتور سئو و اسکیما (SEO &amp; Schema Engine)</span>
                    </h2>
                    <span class="bk-seo-chip">JSON-LD · OG · Sitemap</span>
                </div>
                <p class="bk-seo-subtext">مدیریت ماژول‌های سئو، اسکیما، ساختار متا و تحلیل هوشمند مطالب سایت</p>
            </div>
        </div>
    </div>

    <!-- SEO Tools Panel -->
    <div>
        <h3 class="bk-section-title">ماژول‌های فعال سئو و اسکیما</h3>
        <div class="bk-seo-grid-3" style="margin-bottom:28px;">
            <?php foreach ($seo_mods as $mod):
                $mod_id   = esc_attr($mod['id'] ?? '');
                $title_fa = esc_html($mod['title_fa'] ?? $mod['title'] ?? '');
                $desc_fa  = esc_html($mod['description_fa'] ?? $mod['description'] ?? '');
                $badge    = esc_html($mod['badge'] ?? '');
                $badge_c  = esc_attr($mod['badge_color'] ?? '#0969DA');
                $icon     = $mod['icon'] ?? '⚡';
                $is_mod_on = !empty($mod['enabled']);
            ?>
            <div class="bankai-card bk-seo-mod-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;gap:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="bk-seo-mod-icon"><?php echo esc_html($icon); ?></div>
                            <div>
                                <div style="font-weight:700;font-size:13px;color:#0F172A;line-height:1.35;"><?php echo $title_fa; ?></div>
                                <span class="bk-seo-badge" style="background:<?php echo $badge_c; ?>1A;color:<?php echo $badge_c; ?>;"><?php echo $badge; ?></span>
                            </div>
                        </div>
                        <label class="bankai-switch">
                            <input type="checkbox"
                                   class="bk-module-toggle"
                                   data-id="<?php echo $mod_id; ?>"
                                   <?php checked($is_mod_on); ?>>
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <p style="font-size:12px;color:#64748B;line-height:1.6;margin:0 0 14px;"><?php echo $desc_fa; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Integrations -->
        <h3 class="bk-section-title">یکپارچه‌سازی و ابزارهای وب‌مستر</h3>
        <div class="bk-seo-grid-2" style="margin-bottom:28px;">
            <div class="bankai-card" style="padding:20px;">
                <div style="font-weight:800;font-size:14px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                    <span class="bk-seo-mod-icon" style="width:28px;height:28px;font-size:14px;">🔍</span>
                    گوگل و ابزارهای تحلیلی
                </div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label class="bk-seo-label">GA4 Measurement ID</label>
                        <input type="text" class="bk-seo-input" id="input-seo-ga4" value="<?php echo $ga4; ?>" placeholder="G-XXXXXXXX">
                    </div>
                    <div>
                        <label class="bk-seo-label">Google Tag Manager</label>
                        <input type="text" class="bk-seo-input" id="input-seo-gtm" value="<?php echo $gtm; ?>" placeholder="GTM-XXXX">
                    </div>
                    <div>
                        <label class="bk-seo-label">Google Site Verification</label>
                        <input type="text" class="bk-seo-input" id="input-seo-gsc" value="<?php echo $gsc; ?>" placeholder="کد متای گوگل سرچ کنسول">
                    </div>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" id="btn-save-seo-google" style="align-self:flex-start;">
                        ذخیره تنظیمات گوگل
                    </button>
                </div>
            </div>

            <div class="bankai-card" style="padding:20px;">
                <div style="font-weight:800;font-size:14px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                    <span class="bk-seo-mod-icon" style="width:28px;height:28px;font-size:14px;">🌐</span>
                    موتورهای جستجو و Sitemap
                </div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label class="bk-seo-label">Bing Webmaster</label>
                        <input type="text" class="bk-seo-input" id="input-seo-bing" value="<?php echo $bing; ?>">
                    </div>
                    <div>
                        <label class="bk-seo-label">Yandex Verification</label>
                        <input type="text" class="bk-seo-input" id="input-seo-yandex" value="<?php echo $yandex; ?>">
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;cursor:pointer;margin-top:4px;">
                        <input type="checkbox" id="input-seo-sitemap" <?php checked($sitemap); ?> style="accent-color:#0969DA;">
                        فعال‌سازی نقشه سایت دینامیک (Sitemap XML)
                    </label>
                    <button type="button" class="bk-seo-btn bk-seo-btn-primary" id="btn-save-seo-other" style="align-self:flex-start;">
                        ذخیره تنظیمات موتورهای جستجو
                    </button>
                </div>
            </div>

            <div class="bankai-card" style="padding:20px;grid-column:1 / -1;">
                <div style="font-weight:800;font-size:14px;margin-bottom:10px;">فایل robots.txt</div>
                <textarea class="bk-seo-input mono" id="input-seo-robots" rows="4" placeholder="User-agent: *&#10;Allow: /"><?php echo $robots; ?></textarea>
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" id="btn-save-seo-robots" style="margin-top:10px;">
                    ذخیره robots.txt
                </button>
            </div>
        </div>

        <!-- Fixed Keywords Section 📌 -->
        <div class="bk-fk-section bankai-card">
            <div class="bk-fk-head">
                <div>
                    <h3 class="bk-fk-title">📌 کلمات کلیدی ثابت و سراسری سایت</h3>
                    <p class="bk-fk-desc">این کلیدواژه‌ها با علامت پین (📌) متمایز شده و به‌صورت خودکار به متای تمامی مقالات چسبانده می‌شوند.</p>
                </div>
            </div>
            <div class="bk-fk-add-row" style="margin-top:10px;">
                <input type="text" class="bk-seo-input bk-fk-input" id="input-seo-fixed-keywords"
                       value="<?php echo esc_attr($fixed_raw); ?>"
                       placeholder="کلمات کلیدی ثابت را با ویرگول (،) جدا کنید…">
                <button type="button" class="bk-seo-btn bk-seo-btn-primary" id="btn-save-fixed-keywords">ذخیره کلمات کلیدی ثابت 📌</button>
            </div>
        </div>
    </div>
</div>

<style>
#tab-seo-engine { font-family: inherit; color: #0F172A; }
.bk-section-title { font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 14px; }
.bk-seo-header { border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; background: #fff; margin-bottom: 20px; }
.bk-seo-header-main { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
.bk-seo-title-flex { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.bk-seo-h2 { font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px; }
.bk-seo-h2 svg { width: 22px; height: 22px; color: #0969DA; }
.bk-seo-subtext { font-size: 12px; color: #64748B; margin: 4px 0 0; }
.bk-seo-chip { font-size: 10px; font-weight: 800; background: #F1F5F9; color: #475569; padding: 3px 8px; border-radius: 6px; }
.bk-seo-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; border: 1px solid transparent; transition: all .15s ease; }
.bk-seo-btn-primary { background: #2563EB; color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,0.25); }
.bk-seo-btn-primary:hover { background: #1D4ED8; }
.bk-seo-grid-3 { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.bk-seo-grid-2 { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; }
.bk-seo-mod-card { padding: 18px; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid #E2E8F0; border-radius: 12px; background: #fff; }
.bk-seo-mod-icon { width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.bk-seo-badge { font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 3px; }
.bk-seo-label { font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px; }
.bk-seo-input { width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 12px; box-sizing: border-box; outline: none; }
.bk-seo-input.mono { font-family: ui-monospace, monospace; font-size: 11px; }
.bk-fk-section { padding: 20px; border: 1px solid #E2E8F0; border-radius: 12px; background: #fff; margin-bottom: 24px; }
.bk-fk-title { margin: 0; font-size: 14px; font-weight: 800; color: #0F172A; }
.bk-fk-desc { margin: 4px 0 0; font-size: 12px; color: #64748B; }
.bk-fk-add-row { display: flex; gap: 8px; flex-wrap: wrap; }
.bk-fk-input { flex: 1; min-width: 200px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function restPost(path, body) {
        var base = (window.bankaiCoreData && window.bankaiCoreData.restUrl ? window.bankaiCoreData.restUrl : '/wp-json/bankai/v1/').replace(/\/$/, '');
        return fetch(base + path, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': (window.bankaiCoreData && window.bankaiCoreData.nonce) || ''
            },
            body: JSON.stringify(body)
        }).then(function(r) { return r.json(); });
    }

    var btnGoogle = document.getElementById('btn-save-seo-google');
    if (btnGoogle) {
        btnGoogle.addEventListener('click', function() {
            var ga4 = document.getElementById('input-seo-ga4') ? document.getElementById('input-seo-ga4').value : '';
            var gtm = document.getElementById('input-seo-gtm') ? document.getElementById('input-seo-gtm').value : '';
            var gsc = document.getElementById('input-seo-gsc') ? document.getElementById('input-seo-gsc').value : '';

            restPost('/settings', { settings: { ga4_measurement_id: ga4, google_tag_manager_id: gtm, google_site_verification: gsc } }).then(function(res) {
                if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('تنظیمات گوگل ذخیره شد', 'success');
            });
        });
    }

    var btnOther = document.getElementById('btn-save-seo-other');
    if (btnOther) {
        btnOther.addEventListener('click', function() {
            var bing = document.getElementById('input-seo-bing') ? document.getElementById('input-seo-bing').value : '';
            var yandex = document.getElementById('input-seo-yandex') ? document.getElementById('input-seo-yandex').value : '';
            var sitemap = document.getElementById('input-seo-sitemap') ? document.getElementById('input-seo-sitemap').checked : true;

            restPost('/settings', { settings: { bing_webmaster: bing, yandex_verification: yandex, sitemap_enabled: sitemap } }).then(function(res) {
                if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('تنظیمات موتورهای جستجو ذخیره شد', 'success');
            });
        });
    }

    var btnRobots = document.getElementById('btn-save-seo-robots');
    if (btnRobots) {
        btnRobots.addEventListener('click', function() {
            var robots = document.getElementById('input-seo-robots') ? document.getElementById('input-seo-robots').value : '';
            restPost('/settings', { settings: { robots_txt: robots } }).then(function(res) {
                if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('robots.txt ذخیره شد', 'success');
            });
        });
    }

    var btnFixed = document.getElementById('btn-save-fixed-keywords');
    if (btnFixed) {
        btnFixed.addEventListener('click', function() {
            var raw = document.getElementById('input-seo-fixed-keywords') ? document.getElementById('input-seo-fixed-keywords').value : '';
            restPost('/seo/fixed-keywords', { raw: raw }).then(function(res) {
                if (window.bankaiAdminInstance) window.bankaiAdminInstance.showToast('کلمات کلیدی ثابت 📌 ذخیره شد', 'success');
            });
        });
    }
});
</script>
