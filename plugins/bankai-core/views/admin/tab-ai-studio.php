<?php
/**
 * Tab: AI Studio — Premium Multi-LLM Control Center
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

/** @var array $state */
$studio      = Bankai_AI_Studio::instance();
$catalog     = Bankai_AI_Studio::providers_catalog();
$keys        = $studio->get_keys();
$models      = $studio->get_selected_models();
$default_p   = $studio->get_default_provider();
$status_list = $studio->provider_status_list();
$status_map  = [];

foreach ($status_list as $row) {
    $status_map[$row['id']] = $row;
}

$ready_count = 0;
foreach ($status_list as $row) {
    if (!empty($row['has_key'])) {
        $ready_count++;
    }
}

$ai_mods = [
    [
        'id'             => 'auto_seo_meta',
        'title'          => 'Auto SEO Metadata',
        'title_fa'       => 'تولید خودکار متای سئو',
        'description'    => 'Automatically generates title and description on save.',
        'description_fa' => 'تولید هوشمند عنوان سئو و متا دیسکریپشن در زمان ذخیره پیش‌نویس.',
        'badge'          => 'SEO Engine',
    ],
    [
        'id'             => 'alt_generator',
        'title'          => 'Smart Image ALT',
        'title_fa'       => 'تولید متن جایگزین تصاویر',
        'description'    => 'Generates contextual alt tags for uploaded media.',
        'description_fa' => 'تولید هوشمند متن ALT برای تصویر شاخص و رسانه‌های آپلود شده.',
        'badge'          => 'Media AI',
    ],
    [
        'id'             => 'content_expander',
        'title'          => 'Gutenberg AI Assistant',
        'title_fa'       => 'دستیار هوشمند گوتنبرگ',
        'description'    => 'Inline AI toolbar for block editor rewriting and outlines.',
        'description_fa' => 'افزودن نوار ابزار هوش مصنوعی مستقیم به ویرایشگر بلوکی گوتنبرگ.',
        'badge'          => 'Editor Kit',
    ],
];

$saved_mods = get_option(Bankai_AI_Studio::OPT_MODULES, []);
if (!is_array($saved_mods)) {
    $saved_mods = [];
}
?>

<style>
#tab-ai-studio {
    --ai-ink: #0F172A;
    --ai-muted: #64748B;
    --ai-line: rgba(15, 23, 42, .08);
    --ai-radius: 18px;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
#tab-ai-studio .ai-hero {
    position: relative;
    overflow: hidden;
    border-radius: 22px;
    padding: 28px 28px 24px;
    margin-bottom: 22px;
    background:
        radial-gradient(1200px 400px at 10% -20%, rgba(99,102,241,.28), transparent 55%),
        radial-gradient(900px 380px at 95% 0%, rgba(16,163,127,.22), transparent 50%),
        radial-gradient(700px 300px at 60% 120%, rgba(217,119,6,.12), transparent 45%),
        linear-gradient(135deg, #0B1220 0%, #121A2B 48%, #0E1624 100%);
    color: #F8FAFC;
    box-shadow: 0 20px 50px rgba(15, 23, 42, .18);
}
#tab-ai-studio .ai-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 28px 28px;
    mask-image: radial-gradient(ellipse at 50% 0%, #000 20%, transparent 70%);
    pointer-events: none;
}
#tab-ai-studio .ai-hero-inner { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: 18px; justify-content: space-between; align-items: flex-start; }
#tab-ai-studio .ai-kicker {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
    color: rgba(248,250,252,.7); margin-bottom: 10px;
}
#tab-ai-studio .ai-kicker-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #34D399; box-shadow: 0 0 0 4px rgba(52,211,153,.25);
    animation: ai-pulse 1.8s ease-in-out infinite;
}
@keyframes ai-pulse {
    0%, 100% { box-shadow: 0 0 0 3px rgba(52,211,153,.2); }
    50% { box-shadow: 0 0 0 7px rgba(52,211,153,.08); }
}
#tab-ai-studio .ai-hero h2 {
    margin: 0 0 8px; font-size: 24px; font-weight: 850; letter-spacing: -.02em;
    background: linear-gradient(90deg, #fff 0%, #C7D2FE 55%, #A7F3D0 100%);
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
#tab-ai-studio .ai-hero p { margin: 0; max-width: 540px; font-size: 13px; line-height: 1.65; color: rgba(248,250,252,.72); }
#tab-ai-studio .ai-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
#tab-ai-studio .ai-btn-primary {
    appearance: none; border: 0; cursor: pointer;
    padding: 11px 18px; border-radius: 12px; font-size: 12px; font-weight: 800;
    color: #0B1220; background: linear-gradient(135deg, #E0E7FF, #A7F3D0);
    box-shadow: 0 8px 24px rgba(167,243,208,.25);
    display: inline-flex; align-items: center; gap: 8px; transition: transform .15s ease, filter .15s ease;
}
#tab-ai-studio .ai-btn-primary:hover { transform: translateY(-1px); filter: brightness(1.05); }
#tab-ai-studio .ai-btn-ghost {
    appearance: none; cursor: pointer;
    padding: 11px 16px; border-radius: 12px; font-size: 12px; font-weight: 700;
    color: #F8FAFC; background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.14);
    display: inline-flex; align-items: center; gap: 8px;
}
#tab-ai-studio .ai-stat-row {
    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 22px; position: relative; z-index: 1;
}
#tab-ai-studio .ai-stat {
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
    border-radius: 14px; padding: 14px 16px; backdrop-filter: blur(8px);
}
#tab-ai-studio .ai-stat-label { font-size: 11px; font-weight: 700; color: rgba(248,250,252,.55); text-transform: uppercase; letter-spacing: .04em; }
#tab-ai-studio .ai-stat-value { margin-top: 4px; font-size: 20px; font-weight: 850; color: #fff; font-variant-numeric: tabular-nums; }

#tab-ai-studio .ai-section-title {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    margin: 18px 0 14px;
}
#tab-ai-studio .ai-section-title h3 {
    margin: 0; font-size: 14px; font-weight: 800; color: var(--ai-ink);
    display: flex; align-items: center; gap: 8px;
}
#tab-ai-studio .ai-section-title span.hint { font-size: 11px; color: var(--ai-muted); font-weight: 600; }

#tab-ai-studio .ai-provider-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
#tab-ai-studio .ai-provider-card {
    position: relative;
    border-radius: var(--ai-radius);
    background: #fff;
    border: 1px solid var(--ai-line);
    box-shadow: 0 1px 2px rgba(15,23,42,.04), 0 12px 28px rgba(15,23,42,.04);
    overflow: hidden;
    transition: transform .18s ease, box-shadow .18s ease;
}
#tab-ai-studio .ai-provider-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(15,23,42,.1);
}
#tab-ai-studio .ai-provider-card .ai-card-ribbon { height: 4px; width: 100%; }
#tab-ai-studio .ai-provider-body { padding: 18px 18px 16px; display: flex; flex-direction: column; gap: 12px; min-height: 100%; box-sizing: border-box; }
#tab-ai-studio .ai-provider-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
#tab-ai-studio .ai-provider-name { font-size: 15px; font-weight: 850; color: var(--ai-ink); letter-spacing: -.01em; }
#tab-ai-studio .ai-provider-tagline { font-size: 11px; color: var(--ai-muted); margin-top: 2px; font-weight: 600; }
#tab-ai-studio .ai-status-pill {
    flex-shrink: 0; font-size: 10px; font-weight: 800; letter-spacing: .02em;
    padding: 4px 9px; border-radius: 999px; border: 1px solid transparent;
}
#tab-ai-studio .ai-status-pill.ready,
#tab-ai-studio .ai-status-pill.connected { background: rgba(16,185,129,.12); color: #047857; border-color: rgba(16,185,129,.25); }
#tab-ai-studio .ai-status-pill.failed { background: rgba(239,68,68,.1); color: #B91C1C; border-color: rgba(239,68,68,.25); }
#tab-ai-studio .ai-status-pill.missing_key { background: #F8FAFC; color: #64748B; border-color: #E2E8F0; }

#tab-ai-studio .ai-field label {
    display: block; font-size: 11px; font-weight: 800; color: var(--ai-muted);
    margin-bottom: 6px; letter-spacing: .02em; text-transform: uppercase;
}
#tab-ai-studio .ai-field input,
#tab-ai-studio .ai-field select {
    width: 100%; box-sizing: border-box;
    padding: 10px 12px; border-radius: 11px;
    border: 1px solid #E2E8F0; background: #F8FAFC;
    font-size: 12.5px; color: var(--ai-ink); outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
}
#tab-ai-studio .ai-field input:focus,
#tab-ai-studio .ai-field select:focus {
    background: #fff; border-color: #94A3B8;
    box-shadow: 0 0 0 3px rgba(148,163,184,.2);
}
#tab-ai-studio .ai-field input { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
#tab-ai-studio .ai-docs { font-size: 11px; font-weight: 700; color: #4F46E5; text-decoration: none; display: inline-block; margin-top: 4px; }
#tab-ai-studio .ai-docs:hover { text-decoration: underline; }
#tab-ai-studio .ai-card-actions { display: flex; gap: 8px; margin-top: auto; padding-top: 4px; }
#tab-ai-studio .ai-card-actions button {
    appearance: none; cursor: pointer; border-radius: 10px; font-size: 11.5px; font-weight: 800;
    padding: 9px 12px; transition: transform .12s ease, filter .12s ease;
}
#tab-ai-studio .ai-card-actions button:disabled { opacity: .6; cursor: wait; }
#tab-ai-studio .ai-card-actions .save { flex: 1; border: 0; color: #fff; }
#tab-ai-studio .ai-card-actions .test { border: 1px solid #E2E8F0; background: #fff; color: #334155; }
#tab-ai-studio .ai-card-actions button:hover { transform: translateY(-1px); }

#tab-ai-studio .ai-sandbox {
    border-radius: 22px;
    border: 1px solid var(--ai-line);
    background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
    box-shadow: 0 16px 40px rgba(15,23,42,.05);
    overflow: hidden;
    margin-bottom: 24px;
}
#tab-ai-studio .ai-sandbox-head {
    padding: 18px 20px;
    border-bottom: 1px solid var(--ai-line);
    display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;
    background: linear-gradient(90deg, rgba(99,102,241,.06), rgba(16,163,127,.05));
}
#tab-ai-studio .ai-sandbox-head h3 { margin: 0; font-size: 14px; font-weight: 850; color: var(--ai-ink); }
#tab-ai-studio .ai-sandbox-body { padding: 18px 20px 20px; }
#tab-ai-studio .ai-prompt-row { display: flex; gap: 10px; flex-wrap: wrap; }
#tab-ai-studio .ai-prompt-row input {
    flex: 1; min-width: 220px; box-sizing: border-box;
    padding: 13px 16px; border-radius: 14px; border: 1px solid #E2E8F0;
    background: #fff; font-size: 13.5px; outline: none;
}
#tab-ai-studio .ai-prompt-row input:focus { border-color: #818CF8; box-shadow: 0 0 0 4px rgba(129,140,248,.15); }
#tab-ai-studio .ai-prompt-row button {
    appearance: none; border: 0; cursor: pointer;
    padding: 13px 20px; border-radius: 14px; font-size: 12.5px; font-weight: 850;
    color: #fff; background: linear-gradient(135deg, #4F46E5, #0EA5E9);
    box-shadow: 0 10px 24px rgba(79,70,229,.28);
}
#tab-ai-studio .ai-prompt-row button:disabled { opacity: .65; cursor: wait; }
#tab-ai-studio .ai-result {
    position: relative;
    margin-top: 14px; padding: 16px 18px; border-radius: 14px;
    background: #0B1220; color: #E2E8F0; font-size: 13px; line-height: 1.75;
    white-space: pre-wrap; border: 1px solid rgba(255,255,255,.06);
}
#tab-ai-studio .ai-copy-btn {
    position: absolute; top: 12px; left: 12px;
    background: rgba(255,255,255,.1); border: 0; color: #fff; border-radius: 8px;
    padding: 4px 10px; font-size: 11px; cursor: pointer;
}
#tab-ai-studio .ai-loader {
    margin-top: 14px; display: flex; align-items: center; gap: 10px;
    padding: 14px 16px; border-radius: 14px; background: #EEF2FF; color: #3730A3;
    font-size: 12.5px; font-weight: 700;
}
#tab-ai-studio .ai-loader-bar {
    width: 18px; height: 18px; border-radius: 50%;
    border: 2.5px solid rgba(79,70,229,.2); border-top-color: #4F46E5;
    animation: ai-spin .7s linear infinite;
}
@keyframes ai-spin { to { transform: rotate(360deg); } }

#tab-ai-studio .ai-mod-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; margin-bottom: 28px;
}
#tab-ai-studio .ai-mod-card {
    background: #fff; border: 1px solid var(--ai-line); border-radius: 16px; padding: 16px;
    box-shadow: 0 1px 2px rgba(15,23,42,.03);
}
#tab-ai-studio .ai-mod-card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
#tab-ai-studio .ai-mod-card .title { font-size: 13.5px; font-weight: 800; color: var(--ai-ink); }
#tab-ai-studio .ai-mod-card .badge {
    display: inline-block; margin-top: 4px; font-size: 10px; font-weight: 800;
    padding: 2px 7px; border-radius: 6px; background: #EEF2FF; color: #4338CA;
}
#tab-ai-studio .ai-mod-card p { margin: 0; font-size: 12px; color: var(--ai-muted); line-height: 1.55; }

/* Switch Style */
.bankai-switch { position: relative; display: inline-block; width: 38px; height: 22px; flex-shrink: 0; }
.bankai-switch input { opacity: 0; width: 0; height: 0; }
.bankai-slider { position: absolute; cursor: pointer; inset: 0; background-color: #CBD5E1; transition: .2s; border-radius: 999px; }
.bankai-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%; }
input:checked + .bankai-slider { background-color: #4F46E5; }
input:checked + .bankai-slider:before { transform: translateX(16px); }

/* Toast alert */
.bankai-toast {
    position: fixed; bottom: 24px; left: 24px; z-index: 99999;
    padding: 12px 20px; border-radius: 12px; background: #0F172A; color: #fff;
    font-size: 12.5px; font-weight: 700; box-shadow: 0 10px 30px rgba(0,0,0,.3);
    display: flex; align-items: center; gap: 10px;
}
</style>

<div id="tab-ai-studio"
     class="bankai-tab-pane"
     x-data="bankaiAiStudioRoot()"
     x-show="activeTab === 'ai-studio'"
     x-cloak>

    <!-- Toast Notification -->
    <div class="bankai-toast" x-show="toast.visible" x-transition x-cloak>
        <span x-text="toast.message"></span>
    </div>

    <!-- Hero Header -->
    <div class="ai-hero">
        <div class="ai-hero-inner">
            <div>
                <div class="ai-kicker">
                    <span class="ai-kicker-dot"></span>
                    Multi-LLM Control Engine · Bankai Core
                </div>
                <h2>استودیو هوش مصنوعی بنکای</h2>
                <p>
                    اتصال زنده و متمرکز به موتورهای هوش مصنوعی پیشرفته شامل GPT-6 Astra، Claude Fable، Gemini 3.8 و DeepSeek V4 با رمزنگاری اختصاصی کلیدها در پایگاه‌داده.
                </p>
            </div>
            <div class="ai-hero-actions">
                <button type="button" class="ai-btn-primary" @click="testAllConnections()" :disabled="busy">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    <span>تست همه‌جانبه اتصالات</span>
                </button>
                <button type="button" class="ai-btn-ghost" @click="scrollToSandbox()">
                    سندباکس تست زنده
                </button>
            </div>
        </div>
        <div class="ai-stat-row">
            <div class="ai-stat">
                <div class="ai-stat-label">موتورهای آماده</div>
                <div class="ai-stat-value"><?php echo (int) $ready_count; ?> / <?php echo count($catalog); ?></div>
            </div>
            <div class="ai-stat">
                <div class="ai-stat-label">ارائه‌دهنده پیش‌فرض</div>
                <div class="ai-stat-value" style="font-size:16px;" x-text="defaultProviderName">
                    <?php echo esc_html($catalog[$default_p]['name'] ?? $default_p); ?>
                </div>
            </div>
            <div class="ai-stat">
                <div class="ai-stat-label">لایه‌ رمزنگاری</div>
                <div class="ai-stat-value" style="font-size:15px;color:#34D399;">AES-256-GCM</div>
            </div>
        </div>
    </div>

    <div class="ai-section-title">
        <h3>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2"/></svg>
            پیکربندی موتورهای هوش مصنوعی
        </h3>
        <span class="hint">کلیدها با الگوریتم امنیتی بانک داده در دیتابیس انکریپت می‌شوند</span>
    </div>

    <!-- Provider Cards Grid -->
    <div class="ai-provider-grid">
        <?php foreach ($catalog as $id => $meta):
            $st = $status_map[$id] ?? [];
            $sel_model = $models[$id] ?? ($meta['default'] ?? '');
            $accent = $meta['accent'] ?? '#4F46E5';
            $glow = $meta['glow'] ?? 'rgba(79,70,229,.2)';
            $status_key = $st['status'] ?? 'missing_key';
            $label = $st['label'] ?? __('بدون کلید API', 'bankai-core');
            $masked = $st['masked_key'] ?? '';
        ?>
        <div class="ai-provider-card"
             x-data="bankaiProviderCard({
                id: '<?php echo esc_js($id); ?>',
                key: '<?php echo esc_js($masked); ?>',
                model: '<?php echo esc_js($sel_model); ?>',
                status: '<?php echo esc_js($status_key); ?>',
                label: '<?php echo esc_js($label); ?>'
             })">
            <div class="ai-card-ribbon" style="background: linear-gradient(90deg, <?php echo esc_attr($accent); ?>, <?php echo esc_attr($accent); ?>88);"></div>
            <div class="ai-provider-body">
                <div class="ai-provider-top">
                    <div>
                        <div class="ai-provider-name" style="display:flex;align-items:center;gap:8px;">
                            <span style="width:10px;height:10px;border-radius:50%;background:<?php echo esc_attr($accent); ?>;box-shadow:0 0 0 4px <?php echo esc_attr($glow); ?>;"></span>
                            <?php echo esc_html($meta['name']); ?>
                        </div>
                        <div class="ai-provider-tagline"><?php echo esc_html($meta['tagline'] ?? ''); ?></div>
                        <a class="ai-docs" href="<?php echo esc_url($meta['docs']); ?>" target="_blank" rel="noopener">دریافت API Key ↗</a>
                    </div>
                    <span class="ai-status-pill" :class="status" x-text="label"><?php echo esc_html($label); ?></span>
                </div>

                <div class="ai-field">
                    <label>مدل فعال</label>
                    <select x-model="model">
                        <?php foreach (($meta['models'] ?? []) as $m): ?>
                            <option value="<?php echo esc_attr($m); ?>" <?php selected($sel_model, $m); ?>><?php echo esc_html($m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ai-field">
                    <label>API Key</label>
                    <div style="display:flex;gap:6px;">
                        <input :type="showKey ? 'text' : 'password'"
                               x-model="key"
                               @input="keyDirty = true"
                               @focus="if(!keyDirty && key.indexOf('•') !== -1){ key=''; keyDirty=true; }"
                               placeholder="کلید API جدید..."
                               autocomplete="off"
                               style="flex:1;">
                        <button type="button" @click="showKey = !showKey"
                                style="appearance:none;border:1px solid #E2E8F0;background:#fff;border-radius:10px;padding:0 10px;cursor:pointer;font-size:11px;font-weight:700;color:#64748B;">
                            <span x-text="showKey ? 'مخفی' : 'نمایش'"></span>
                        </button>
                        <button type="button" title="حذف کلید"
                                @click="clearKey()"
                                style="appearance:none;border:1px solid #FECACA;background:#FEF2F2;border-radius:10px;padding:0 10px;cursor:pointer;font-size:11px;font-weight:800;color:#B91C1C;">
                            حذف
                        </button>
                    </div>
                </div>

                <div class="ai-card-actions">
                    <button type="button" class="save" :disabled="saving"
                            style="background: linear-gradient(135deg, <?php echo esc_attr($accent); ?>, <?php echo esc_attr($accent); ?>cc);"
                            @click="save()">
                        <span x-text="saving ? 'در حال ذخیره...' : 'ذخیره تنظیمات'"></span>
                    </button>
                    <button type="button" class="test" :disabled="testing" @click="test()">
                        <span x-text="testing ? 'تست...' : 'تست اتصال'"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Live Sandbox -->
    <div class="ai-sandbox" id="ai-sandbox-anchor">
        <div class="ai-sandbox-head">
            <div>
                <h3>سندباکس تست و تولید هوشمند</h3>
                <div style="font-size:11px;color:#64748B;font-weight:600;margin-top:2px;">ارسال پرامپت تست مستقیم به مدل انتخاب‌شده</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <label style="font-size:11px;font-weight:800;color:#64748B;">موتور پیش‌فرض:</label>
                <select x-model="defaultProvider" @change="updateDefaultProvider()"
                        style="padding:8px 12px;border-radius:10px;border:1px solid #E2E8F0;font-size:12px;font-weight:700;background:#fff;">
                    <?php foreach ($catalog as $id => $meta): ?>
                        <option value="<?php echo esc_attr($id); ?>" <?php selected($default_p, $id); ?>><?php echo esc_html($meta['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="ai-sandbox-body">
            <div class="ai-prompt-row">
                <input type="text" x-model="sandboxPrompt" @keydown.enter="generateResult()"
                       placeholder="یک دستور یا دستورالعمل سئو بنویسید (مثلاً: ۳ عنوان سئو جذاب برای موضوع خرید هوش مصنوعی)...">
                <button type="button" @click="generateResult()" :disabled="generating">
                    <span x-show="!generating">تولید هوشمند با AI</span>
                    <span x-show="generating" x-cloak>در حال پردازش...</span>
                </button>
            </div>
            <div class="ai-loader" x-show="generating" x-cloak>
                <span class="ai-loader-bar"></span>
                در حال فراخوانی مدل و تولید پاسخ زنده...
            </div>
            <div class="ai-result" x-show="sandboxResult && !generating" x-cloak>
                <button type="button" class="ai-copy-btn" @click="copyToClipboard(sandboxResult)">کپی متن</button>
                <div x-text="sandboxResult"></div>
            </div>
        </div>
    </div>

    <!-- AI Modules Automation Toggles -->
    <div class="ai-section-title">
        <h3>ماژول‌ها و قابلیت‌های خودکار</h3>
        <span class="hint">تنظیمات بلافاصله اعمال می‌شوند</span>
    </div>

    <div class="ai-mod-grid">
        <?php foreach ($ai_mods as $mod):
            $mod_id   = esc_attr($mod['id']);
            $title_fa = esc_html($mod['title_fa']);
            $desc_fa  = esc_html($mod['description_fa']);
            $badge    = esc_html($mod['badge']);
            $is_active = !empty($saved_mods[$mod_id]);
        ?>
        <div class="ai-mod-card" x-data="{ active: <?php echo $is_active ? 'true' : 'false'; ?> }">
            <div class="top">
                <div>
                    <div class="title"><?php echo $title_fa; ?></div>
                    <span class="badge"><?php echo $badge; ?></span>
                </div>
                <label class="bankai-switch">
                    <input type="checkbox" x-model="active" @change="toggleModule('<?php echo $mod_id; ?>', active)">
                    <span class="bankai-slider"></span>
                </label>
            </div>
            <p><?php echo $desc_fa; ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    // Root Controller
    Alpine.data('bankaiAiStudioRoot', () => ({
        busy: false,
        defaultProvider: '<?php echo esc_js($default_p); ?>',
        defaultProviderName: '<?php echo esc_js($catalog[$default_p]['name'] ?? $default_p); ?>',
        sandboxPrompt: '',
        sandboxResult: '',
        generating: false,
        toast: { visible: false, message: '' },

        showToast(msg) {
            this.toast.message = msg;
            this.toast.visible = true;
            setTimeout(() => { this.toast.visible = false; }, 3200);
        },

        scrollToSandbox() {
            document.getElementById('ai-sandbox-anchor')?.scrollIntoView({ behavior: 'smooth' });
        },

        testAllConnections() {
            this.busy = true;
            const data = new FormData();
            data.append('action', 'bankai_test_ai_connections');
            data.append('nonce', window.bankaiAdminNonce || '<?php echo wp_create_nonce('bankai_admin_nonce'); ?>');

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.showToast('تست تمامی موتورها انجام شد.');
                        window.location.reload();
                    } else {
                        this.showToast('خطا: ' + (res.data?.message || 'مشکلی رخ داد'));
                    }
                })
                .catch(() => this.showToast('خطا در برقراری ارتباط با سرور.'))
                .finally(() => { this.busy = false; });
        },

        updateDefaultProvider() {
            // Updated dynamically when card saves
        },

        generateResult() {
            if (!this.sandboxPrompt.trim()) return;
            this.generating = true;
            this.sandboxResult = '';

            const data = new FormData();
            data.append('action', 'bankai_generate_ai_prompt');
            data.append('nonce', window.bankaiAdminNonce || '<?php echo wp_create_nonce('bankai_admin_nonce'); ?>');
            data.append('prompt_input', this.sandboxPrompt);
            data.append('provider', this.defaultProvider);

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.sandboxResult = res.data.result;
                    } else {
                        this.sandboxResult = 'خطا: ' + (res.data?.message || 'مشکلی پیش آمد.');
                    }
                })
                .catch(() => { this.sandboxResult = 'خطای غیرمنتظره در پاسخ‌دهی سرور.'; })
                .finally(() => { this.generating = false; });
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.showToast('متن در حافظه کپی شد.');
            });
        },

        toggleModule(modId, state) {
            const data = new FormData();
            data.append('action', 'bankai_toggle_ai_module');
            data.append('nonce', window.bankaiAdminNonce || '<?php echo wp_create_nonce('bankai_admin_nonce'); ?>');
            data.append('module_id', modId);
            data.append('active', state ? '1' : '0');

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.showToast('وضعیت ماژول تغییر کرد.');
                    }
                });
        }
    }));

    // Card Component
    Alpine.data('bankaiProviderCard', (config) => ({
        id: config.id,
        key: config.key,
        keyDirty: false,
        model: config.model,
        status: config.status,
        label: config.label,
        showKey: false,
        saving: false,
        testing: false,

        save() {
            this.saving = true;
            const data = new FormData();
            data.append('action', 'bankai_save_ai_keys');
            data.append('nonce', window.bankaiAdminNonce || '<?php echo wp_create_nonce('bankai_admin_nonce'); ?>');
            data.append('provider', this.id);
            data.append('key', this.keyDirty ? this.key : '__unchanged__');
            data.append('model', this.model);

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.status = res.data.status;
                        this.label = res.data.label;
                        if (res.data.masked_key) {
                            this.key = res.data.masked_key;
                        }
                        this.keyDirty = false;
                        this.$root.closest('[x-data]').__x.$data.showToast('تنظیمات ' + this.id + ' ذخیره شد.');
                    } else {
                        alert(res.data?.message || 'خطا در ذخیره‌سازی');
                    }
                })
                .finally(() => { this.saving = false; });
        },

        clearKey() {
            if (!confirm('آیا از حذف این کلید مطمئن هستید؟')) return;
            const data = new FormData();
            data.append('action', 'bankai_clear_ai_key');
            data.append('nonce', window.bankaiAdminNonce || '<?php echo wp_create_nonce('bankai_admin_nonce'); ?>');
            data.append('provider', this.id);

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        this.key = '';
                        this.keyDirty = false;
                        this.status = res.data.status;
                        this.label = res.data.label;
                    }
                });
        },

        test() {
            this.testing = true;
            const data = new FormData();
            data.append('action', 'bankai_test_ai_connections');
            data.append('nonce', window.bankaiAdminNonce || '<?php echo wp_create_nonce('bankai_admin_nonce'); ?>');
            data.append('provider', this.id);

            fetch(ajaxurl, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.data.providers.length) {
                        const item = res.data.providers[0];
                        this.status = item.status;
                        this.label = item.label;
                    }
                })
                .finally(() => { this.testing = false; });
        }
    }));
});
</script>