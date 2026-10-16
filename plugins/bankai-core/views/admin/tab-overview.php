<!-- Tab 1: Dashboard -->
<div id="tab-overview" class="bankai-tab-pane" :class="{ 'is-active-tab': activeTab === 'overview' }" x-show="activeTab === 'overview'" x-cloak x-init="initDashboard && initDashboard()">

    <!-- Modern dashboard (visual style inspired by SEO AI Tools — sidebar unchanged) -->
    <div class="bk-dash-v2">
        <div class="bk-dash-v2-welcome">
            <div>
                <h2 class="bk-dash-v2-title" x-text="isRtl ? 'خوش آمدید به پیشخوان Bankai' : 'Welcome to Bankai Dashboard'">خوش آمدید به پیشخوان Bankai</h2>
                <p class="bk-dash-v2-sub" x-text="isRtl ? 'وضعیت محتوا، سئو و عملکرد را یک‌جا ببینید و برای بهینه‌سازی اقدام کنید.' : 'Track content, SEO and performance — then optimize with one click.'">وضعیت محتوا، سئو و عملکرد را یک‌جا ببینید و برای بهینه‌سازی اقدام کنید.</p>
            </div>
            <div class="bk-dash-v2-actions">
                <button type="button" class="bk-btn bk-btn-primary" @click="setTab('articles')" x-text="isRtl ? 'مدیریت محتوا' : 'Content'">مدیریت محتوا</button>
                <button type="button" class="bk-btn" @click="setTab('seo-engine')" x-text="isRtl ? 'موتور سئو' : 'SEO Engine'">موتور سئو</button>
            </div>
        </div>

        <div class="bk-dash-v2-kpis">
            <div class="bk-dash-v2-kpi is-content">
                <div class="bk-dash-v2-kpi-lab" x-text="isRtl ? 'محتوای قابل ایندکس' : 'Indexable content'">محتوای قابل ایندکس</div>
                <div class="bk-dash-v2-kpi-val"><?php echo esc_html((string) ($state['stats']['indexable'] ?? $state['stats']['posts'] ?? '—')); ?></div>
                <div class="bk-dash-v2-kpi-sub" x-text="isRtl ? 'کل مقالات / برگه‌ها' : 'Posts & pages'">کل مقالات / برگه‌ها</div>
            </div>
            <div class="bk-dash-v2-kpi is-seo">
                <div class="bk-dash-v2-kpi-lab">Avg. SEO Score</div>
                <div class="bk-dash-v2-kpi-val"><?php echo esc_html((string) ($state['stats']['avg_seo'] ?? $state['stats']['seo_score'] ?? '—')); ?></div>
                <div class="bk-dash-v2-kpi-sub" x-text="isRtl ? 'میانگین امتیاز سئو' : 'Average post score'">میانگین امتیاز سئو</div>
            </div>
            <div class="bk-dash-v2-kpi is-up">
                <div class="bk-dash-v2-kpi-lab">Uptime</div>
                <div class="bk-dash-v2-kpi-val"><?php echo esc_html(($state['stats']['uptime'] ?? '99.9%')); ?></div>
                <div class="bk-dash-v2-kpi-sub" x-text="isRtl ? 'پایداری سرویس' : 'Service stability'">پایداری سرویس</div>
            </div>
            <div class="bk-dash-v2-kpi is-speed">
                <div class="bk-dash-v2-kpi-lab">TTFB</div>
                <div class="bk-dash-v2-kpi-val"><?php
                    $ttfb = $state['stats']['avg_ttfb'] ?? $state['stats']['avg_latency'] ?? null;
                    echo esc_html($ttfb !== null && $ttfb !== '' ? (is_numeric($ttfb) ? $ttfb . 'ms' : (string) $ttfb) : '—');
                ?></div>
                <div class="bk-dash-v2-kpi-sub" x-text="isRtl ? 'میانگین تأخیر' : 'Avg latency'">میانگین تأخیر</div>
            </div>
        </div>

        <div class="bk-dash-v2-grid">
            <div class="bk-dash-v2-card bk-dash-v2-visits">
                <div class="bk-dash-v2-card-head">
                    <strong x-text="isRtl ? 'بازدید سایت (۷ روز)' : 'Site visits (7 days)'">بازدید سایت (۷ روز)</strong>
                    <button type="button" class="bk-btn-ghost" style="font-size:12px;" @click="loadDashboardVisits()" :disabled="dashVisitsLoading" x-text="dashVisitsLoading ? '…' : (isRtl ? 'تازه‌سازی' : 'Refresh')">تازه‌سازی</button>
                </div>
                <div class="bk-dash-v2-visits-main">
                    <div class="bk-dash-v2-visits-num" x-text="formatDashNum(dashVisits.total)">—</div>
                    <div class="bk-dash-v2-visits-change">
                        <span x-text="isRtl ? 'نسبت به هفته قبل' : 'vs previous week'"></span>
                        <strong :class="(dashVisits.change||0) >= 0 ? 'is-up' : 'is-down'" x-text="((dashVisits.change||0) >= 0 ? '+' : '') + (dashVisits.change||0) + '%'"></strong>
                    </div>
                </div>
                <div class="bk-dash-v2-devices">
                    <div class="bk-dash-v2-device">
                        <span x-text="isRtl ? 'رایانه' : 'Desktop'">رایانه</span>
                        <strong x-text="formatDashNum(dashVisits.desktop)">0</strong>
                        <div class="bk-dash-v2-bar"><i :style="'width:' + Math.min(100, (dashVisits.desktop && dashVisits.total) ? Math.round(dashVisits.desktop/dashVisits.total*100) : 0) + '%'"></i></div>
                    </div>
                    <div class="bk-dash-v2-device">
                        <span x-text="isRtl ? 'موبایل' : 'Mobile'">موبایل</span>
                        <strong x-text="formatDashNum(dashVisits.mobile)">0</strong>
                        <div class="bk-dash-v2-bar is-teal"><i :style="'width:' + Math.min(100, (dashVisits.mobile && dashVisits.total) ? Math.round(dashVisits.mobile/dashVisits.total*100) : 0) + '%'"></i></div>
                    </div>
                    <div class="bk-dash-v2-device">
                        <span x-text="isRtl ? 'تبلت' : 'Tablet'">تبلت</span>
                        <strong x-text="formatDashNum(dashVisits.tablet)">0</strong>
                        <div class="bk-dash-v2-bar is-amber"><i :style="'width:' + Math.min(100, (dashVisits.tablet && dashVisits.total) ? Math.round(dashVisits.tablet/dashVisits.total*100) : 0) + '%'"></i></div>
                    </div>
                </div>
                <p class="bk-dash-v2-error" x-show="dashVisits.error" x-text="dashVisits.error"></p>
            </div>

            <div class="bk-dash-v2-card">
                <div class="bk-dash-v2-card-head">
                    <strong x-text="isRtl ? 'میانبرهای سریع' : 'Quick actions'">میانبرهای سریع</strong>
                </div>
                <div class="bk-dash-v2-quick">
                    <button type="button" class="bk-dash-v2-quick-item" @click="setTab('articles')">
                        <span class="ico">📝</span>
                        <span x-text="isRtl ? 'تولید مقاله با AI' : 'Generate article'">تولید مقاله با AI</span>
                    </button>
                    <button type="button" class="bk-dash-v2-quick-item" @click="setTab('seo-engine')">
                        <span class="ico">🎯</span>
                        <span x-text="isRtl ? 'تنظیمات سئو' : 'SEO settings'">تنظیمات سئو</span>
                    </button>
                    <button type="button" class="bk-dash-v2-quick-item" @click="setTab('ai-studio')">
                        <span class="ico">✨</span>
                        <span x-text="isRtl ? 'استودیو AI' : 'AI Studio'">استودیو AI</span>
                    </button>
                    <button type="button" class="bk-dash-v2-quick-item" @click="setTab('speed-cache')">
                        <span class="ico">⚡</span>
                        <span x-text="isRtl ? 'کش و سرعت' : 'Cache & Speed'">کش و سرعت</span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Core modules with descriptions + toggles -->
    <div class="bankai-card" style="padding:20px;margin-bottom:18px;">
        <h3 style="margin:0 0 6px;font-size:15px;font-weight:800;" x-text="isRtl ? 'ماژول‌های هسته' : 'Core modules'">ماژول‌های هسته</h3>
        <p style="margin:0 0 14px;font-size:12px;color:#8b90a0;" x-text="isRtl ? 'هر ماژول را فعال یا غیرفعال کنید. توضیح کوتاه زیر نام آمده است.' : 'Enable or disable each module. Short description under the name.'">هر ماژول را فعال یا غیرفعال کنید.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
            <template x-for="mod in coreModuleCards" :key="mod.key">
                <div class="bk-core-mod-card" :class="{ 'is-on': moduleActive[mod.key] !== false }">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                        <div style="min-width:0;">
                            <div style="font-weight:800;font-size:13px;color:#1a1433;" x-text="isRtl ? mod.title_fa : mod.title"></div>
                            <div style="font-size:12px;color:#64748b;line-height:1.55;margin-top:4px;" x-text="isRtl ? mod.desc_fa : mod.desc"></div>
                        </div>
                        <label class="bankai-switch" style="flex-shrink:0;">
                            <input type="checkbox"
                                   :checked="moduleActive[mod.key] !== false"
                                   @change="toggleCoreModule(mod.key, $event.target.checked)">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <div style="margin-top:10px;font-size:11px;font-weight:700;"
                         :style="moduleActive[mod.key] !== false ? 'color:#16a34a' : 'color:#94a3b8'"
                         x-text="moduleActive[mod.key] !== false ? (isRtl ? 'فعال' : 'Active') : (isRtl ? 'غیرفعال' : 'Off')"></div>
                </div>
            </template>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
        <button type="button" class="bankai-card" style="padding:16px;text-align:start;cursor:pointer;" @click="setTab('seo-engine')">
            <div style="font-weight:800;margin-bottom:4px;" x-text="isRtl ? 'سئو و اسکیما' : 'SEO & Schema'"></div>
            <div style="font-size:12px;color:#8b90a0;" x-text="isRtl ? 'ممیزی و ماژول‌ها' : 'Audit & modules'"></div>
        </button>
        <button type="button" class="bankai-card" style="padding:16px;text-align:start;cursor:pointer;" @click="setTab('ai-studio')">
            <div style="font-weight:800;margin-bottom:4px;" x-text="isRtl ? 'استودیو AI' : 'AI Studio'"></div>
            <div style="font-size:12px;color:#8b90a0;" x-text="isRtl ? 'موتورهای متن و تصویر' : 'Text & image engines'"></div>
        </button>
        <button type="button" class="bankai-card" style="padding:16px;text-align:start;cursor:pointer;" @click="setTab('speed-cache')">
            <div style="font-weight:800;margin-bottom:4px;" x-text="isRtl ? 'کش و سرعت' : 'Cache & Speed'"></div>
            <div style="font-size:12px;color:#8b90a0;" x-text="isRtl ? 'بهینه‌سازی عملکرد' : 'Performance tools'"></div>
        </button>
        <button type="button" class="bankai-card" style="padding:16px;text-align:start;cursor:pointer;" @click="setTab('settings-license')">
            <div style="font-weight:800;margin-bottom:4px;" x-text="isRtl ? 'تنظیمات و لایسنس' : 'Settings & License'"></div>
            <div style="font-size:12px;color:#8b90a0;" x-text="isRtl ? 'پیکربندی عمومی' : 'General config'"></div>
        </button>
    </div>
</div>
<!-- /tab-overview root -->
