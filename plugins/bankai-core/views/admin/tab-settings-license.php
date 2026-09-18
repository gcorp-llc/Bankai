<!-- Tab: Settings & License Management -->
<div id="tab-settings-license" class="bankai-tab-pane" x-show="activeTab === 'settings-license'" x-cloak>

    <!-- Header -->
    <div class="bankai-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px; flex-wrap: wrap;">
                <h2 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <polyline points="9 12 11 14 15 10" />
                    </svg>
                    <span x-text="t('settingsTitle')">Platform Settings, Modular Switcher &amp; License</span>
                </h2>
                <span style="background-color: rgba(31, 136, 61, 0.2); border: 1px solid rgba(31, 136, 61, 0.5); color: #1A7F37; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 700;">
                    ✓ <span x-text="isRtl ? 'نسخه حرفه‌ای سازمانی تایید شده' : 'Verified Pro Enterprise'">Verified Pro Enterprise</span>
                </span>
            </div>
            <p style="font-size: 12px; color: #8C959F; margin: 0;" x-text="t('settingsSubtitle')">
                Global feature flags, license activation, role permissions, JSON configuration export/import &amp; system diagnostics.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button"
                    id="btn-save-all-settings"
                    @click="triggerSaveLoader({ title: isRtl ? 'در حال ذخیره‌سازی تمامی تنظیمات پلتفرم...' : 'Saving All Platform Settings...', message: isRtl ? 'در حال اعمال پرچم‌های ماژولار و هماهنگ‌سازی دیتابیس...' : 'Updating modular flags and synchronizing database...', duration: 700 })"
                    style="background-color: #0969DA; border: 1px solid #0969DA; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 1px 3px rgba(9, 105, 218, 0.3);">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                <span x-text="isRtl ? 'ذخیره تمامی تنظیمات' : 'Save All Settings'">Save All Settings</span>
            </button>

            <button type="button"
                    @click="exportConfiguration()"
                    style="background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 1px 2px rgba(46, 52, 64, 0.04);">
                <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" />
                </svg>
                <span x-text="t('exportConfig')">Export Config JSON</span>
            </button>

            <button type="button"
                    @click="confirmFactoryReset()"
                    style="background-color: rgba(207, 34, 46, 0.12); border: 1px solid rgba(207, 34, 46, 0.4); color: #CF222E; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <span x-text="t('factoryReset')">Factory Reset</span>
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
                    <h3 style="font-size: 16px; font-weight: 800; color: #1F2328; margin: 0;"
                        x-text="isRtl ? 'لایسنس فعال نسخه تجاری و دائمی پلتفرم' : 'Bankai Core Pro License Key'">
                        Bankai Core Pro License Key
                    </h3>
                    <span style="background-color: rgba(31, 136, 61, 0.25); color: #1A7F37; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(31, 136, 61, 0.5);"
                          x-text="isRtl ? 'فعال و تایید شده' : 'ACTIVE & VERIFIED'">
                        ACTIVE &amp; VERIFIED
                    </span>
                </div>
                <div style="font-size: 12px; color: #8C959F; margin-top: 4px;">
                    <span x-text="isRtl ? 'کد لایسنس:' : 'Key:'">Key:</span>
                    <code style="color: #0969DA; background-color: #F6F8FA; border: 1px solid #D0D7DE; padding: 2px 6px; border-radius: 4px; font-family: monospace;">BNK-PRO-8849-2049-9941-X9</code>
                    &bull; <span x-text="isRtl ? 'سطح دائمی نامحدود (Lifetime)' : 'Lifetime Tier'">Lifetime Tier</span>
                    &bull; <span x-text="isRtl ? 'دامنه نامحدود' : 'Unlimited Domains'">Unlimited Domains</span>
                </div>
            </div>
        </div>

        <button type="button"
                @click="checkLicenseUpdates()"
                style="background-color: #F6F8FA; border: 1px solid #D0D7DE; color: #0969DA; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M19.95 11a8 8 0 1 0-.5 4m.5-4h-5m5 0V6" />
            </svg>
            <span x-text="isRtl ? 'اعتبارسنجی مجدد لایسنس' : 'Revalidate License'">Revalidate License</span>
        </button>
    </div>

    <!-- Global Module Switcher -->
    <div class="bankai-card" style="padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #1F2328; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <line x1="4" y1="21" x2="4" y2="14" /><line x1="4" y1="10" x2="4" y2="3" />
                <line x1="12" y1="21" x2="12" y2="12" /><line x1="12" y1="8" x2="12" y2="3" />
                <line x1="20" y1="21" x2="20" y2="16" /><line x1="20" y1="12" x2="20" y2="3" />
                <line x1="1" y1="14" x2="7" y2="14" /><line x1="9" y1="8" x2="15" y2="8" /><line x1="17" y1="16" x2="23" y2="16" />
            </svg>
            <span x-text="isRtl ? 'کلیدهای سراسری مدیریت ماژول‌ها' : 'Modular Global Architecture Switcher'">Modular Global Architecture Switcher</span>
        </h3>

        <div class="bankai-grid-2">
            <div style="background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="isRtl ? 'موتور سئو خودکار و اسکیما' : 'Autonomous SEO Engine'">Autonomous SEO Engine</div>
                    <div style="font-size: 11px; color: #8C959F;" x-text="isRtl ? 'سوئیچ اصلی برای اسکیما، متادیتا و پایش ۴۰۴' : 'Master toggle for SEO, Schema and 404 monitoring'">Master toggle for SEO, Schema and 404 monitoring</div>
                </div>
                <label class="bankai-switch">
                    <input type="checkbox" checked @change="triggerSaveLoader({ title: isRtl ? 'در حال به‌روزرسانی پرچم موتور سئو...' : 'Updating SEO Engine Flag...', savedTitle: isRtl ? 'تنظیمات سئو ذخیره شد' : 'SEO Setting Saved' })">
                    <span class="bankai-slider"></span>
                </label>
            </div>

            <div style="background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="isRtl ? 'شتاب‌دهنده سرعت و کش' : 'Speed Cache Accelerator'">Speed Cache Accelerator</div>
                    <div style="font-size: 11px; color: #8C959F;" x-text="isRtl ? 'سوئیچ اصلی برای کش صفحات، فشرده‌سازی و Redis' : 'Master toggle for page cache, minification & Redis'">Master toggle for page cache, minification &amp; Redis</div>
                </div>
                <label class="bankai-switch">
                    <input type="checkbox" checked @change="triggerSaveLoader({ title: isRtl ? 'در حال به‌روزرسانی شتاب‌دهنده سرعت...' : 'Updating Speed Accelerator Flag...', savedTitle: isRtl ? 'تنظیمات سرعت ذخیره شد' : 'Speed Setting Saved' })">
                    <span class="bankai-slider"></span>
                </label>
            </div>

            <div style="background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="isRtl ? 'استودیو رسانه و واترمارک' : 'Media & Dynamic Watermark Studio'">Media &amp; Dynamic Watermark Studio</div>
                    <div style="font-size: 11px; color: #8C959F;" x-text="isRtl ? 'سوئیچ اصلی برای WebP/AVIF و واترمارک' : 'Master toggle for WebP/AVIF and watermarks'">Master toggle for WebP/AVIF and watermarks</div>
                </div>
                <label class="bankai-switch">
                    <input type="checkbox" checked @change="triggerSaveLoader({ title: isRtl ? 'در حال به‌روزرسانی استودیو رسانه...' : 'Updating Media Studio Flag...', savedTitle: isRtl ? 'تنظیمات رسانه ذخیره شد' : 'Media Setting Saved' })">
                    <span class="bankai-slider"></span>
                </label>
            </div>

            <div style="background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="isRtl ? 'استودیو هوش مصنوعی چندمدلی' : 'Generative AI Studio & Multi-LLM'">Generative AI Studio &amp; Multi-LLM</div>
                    <div style="font-size: 11px; color: #8C959F;" x-text="isRtl ? 'سوئیچ اصلی برای Gemini، OpenAI و Claude' : 'Master toggle for Gemini, OpenAI, Claude & DeepSeek'">Master toggle for Gemini, OpenAI, Claude &amp; DeepSeek</div>
                </div>
                <label class="bankai-switch">
                    <input type="checkbox" checked @change="triggerSaveLoader({ title: isRtl ? 'در حال به‌روزرسانی هوش مصنوعی...' : 'Updating AI Studio Flag...', savedTitle: isRtl ? 'تنظیمات هوش مصنوعی ذخیره شد' : 'AI Setting Saved' })">
                    <span class="bankai-slider"></span>
                </label>
            </div>

            <div style="background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="isRtl ? 'استاندارد llms.txt و مانیفست' : 'Standard llms.txt & Manifest API'">Standard llms.txt &amp; Manifest API</div>
                    <div style="font-size: 11px; color: #8C959F;" x-text="isRtl ? 'فعال‌سازی /llms.txt برای ربات‌های AI' : 'Enables /llms.txt endpoints for AI crawlers'">Enables /llms.txt endpoints for AI crawlers</div>
                </div>
                <label class="bankai-switch">
                    <input type="checkbox" checked @change="triggerSaveLoader({ title: isRtl ? 'در حال به‌روزرسانی llms.txt...' : 'Updating llms.txt Configuration...', savedTitle: isRtl ? 'تنظیمات llms.txt ذخیره شد' : 'llms.txt Config Saved' })">
                    <span class="bankai-slider"></span>
                </label>
            </div>

            <div style="background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; font-size: 13px; color: #1F2328;" x-text="isRtl ? 'ایندکس آنی (Google & IndexNow)' : 'Instant Indexing API (Google & IndexNow)'">Instant Indexing API (Google &amp; IndexNow)</div>
                    <div style="font-size: 11px; color: #8C959F;" x-text="isRtl ? 'اطلاع‌رسانی بلادرنگ به موتورهای جستجو' : 'Pings search engines instantly on publish'">Pings search engines instantly on publish</div>
                </div>
                <label class="bankai-switch">
                    <input type="checkbox" checked @change="triggerSaveLoader({ title: isRtl ? 'در حال فعال‌سازی ایندکس آنی...' : 'Updating Instant Indexing...', savedTitle: isRtl ? 'تنظیمات ایندکس آنی ذخیره شد' : 'Instant Indexing Saved' })">
                    <span class="bankai-slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- RBAC + Config Portability -->
    <div class="bankai-grid-split" style="margin-bottom: 24px;">
        <div class="bankai-card" style="padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #1F2328; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span x-text="isRtl ? 'سطوح دسترسی نقش‌های کاربری' : 'Role-Based Access Permissions'">Role-Based Access Permissions</span>
            </h3>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background-color: #F6F8FA; padding: 10px 14px; border-radius: 8px; border: 1px solid #D0D7DE;">
                    <span style="font-size: 12px; color: #1F2328; font-weight: 600;" x-text="isRtl ? 'مدیران کل (دسترسی کامل)' : 'Administrators (Full Access)'">Administrators (Full Access)</span>
                    <span style="color: #1A7F37; font-weight: 700; font-size: 11px;" x-text="isRtl ? 'اجباری و فعال' : 'Enforced'">Enforced</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; background-color: #F6F8FA; padding: 10px 14px; border-radius: 8px; border: 1px solid #D0D7DE;">
                    <span style="font-size: 12px; color: #1F2328; font-weight: 600;" x-text="isRtl ? 'ویرایشگران (سئو و AI محتوا)' : 'Editors (SEO & Content AI Tools)'">Editors (SEO &amp; Content AI Tools)</span>
                    <label class="bankai-switch">
                        <input type="checkbox" checked @change="triggerSaveLoader({ title: isRtl ? 'در حال به‌روزرسانی دسترسی ویرایشگر...' : 'Updating Editor Permissions...', savedTitle: isRtl ? 'دسترسی ویرایشگر ذخیره شد' : 'Permissions Saved' })">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; background-color: #F6F8FA; padding: 10px 14px; border-radius: 8px; border: 1px solid #D0D7DE;">
                    <span style="font-size: 12px; color: #1F2328; font-weight: 600;" x-text="isRtl ? 'نویسندگان (فقط طرح اولیه AI)' : 'Authors (AI Outlines Only)'">Authors (AI Outlines Only)</span>
                    <label class="bankai-switch">
                        <input type="checkbox" @change="triggerSaveLoader({ title: isRtl ? 'در حال به‌روزرسانی دسترسی نویسنده...' : 'Updating Author Permissions...', savedTitle: isRtl ? 'دسترسی نویسنده ذخیره شد' : 'Permissions Saved' })">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bankai-card" style="padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #1F2328; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polyline points="21 8 21 21 3 21 3 8" />
                    <rect x="1" y="3" width="22" height="5" />
                    <line x1="10" y1="12" x2="14" y2="12" />
                </svg>
                <span x-text="isRtl ? 'پشتیبان‌گیری و انتقال تنظیمات' : 'Configuration Backup & Migration'">Configuration Backup &amp; Migration</span>
            </h3>

            <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0 0 16px 0;"
               x-text="isRtl ? 'برون‌بری تمام تنظیمات فعال، مختصات واترمارک و الگوهای AI به فایل JSON.' : 'Export all active settings, watermark coordinates and prompt manifests to a portable JSON file.'">
                Export all active settings, watermark coordinates and prompt manifests to a portable JSON file.
            </p>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button type="button"
                        @click="exportConfiguration()"
                        style="flex: 1; min-width: 130px; background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 12px rgba(9, 105, 218, 0.35);"
                        x-text="isRtl ? 'دانلود فایل JSON' : 'Download JSON'">
                    Download JSON
                </button>
                <label style="flex: 1; min-width: 130px; text-align: center; background-color: #F6F8FA; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <span x-text="isRtl ? 'درون‌ریزی فایل JSON' : 'Import JSON'">Import JSON</span>
                    <input type="file" style="display: none;" @change="confirmImportConfig()">
                </label>
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
                <span x-text="isRtl ? 'گزارش عیب‌یابی سیستم' : 'System Diagnostic Log Report'">System Diagnostic Log Report</span>
            </h3>
            <button type="button"
                    @click="copySystemReport()"
                    style="background-color: #F6F8FA; border: 1px solid #D0D7DE; color: #0969DA; padding: 6px 14px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px;"
                    x-text="isRtl ? 'کپی متن لاگ عیب‌یابی' : 'Copy Diagnostic Log'">
                Copy Diagnostic Log
            </button>
        </div>

        <textarea readonly rows="8"
                  style="width: 100%; background-color: #F6F8FA; border: 1px solid #D0D7DE; color: #656D76; font-family: monospace; font-size: 12px; padding: 12px; border-radius: 8px; line-height: 1.6; resize: none; direction: ltr; text-align: left;"><?php echo esc_textarea($state['systemReport'] ?? ''); ?></textarea>
    </div>
</div>