<!-- Tab 3: Autonomous SEO & Schema Architecture - GitHub Light Edition -->
<div id="tab-seo-engine" x-show="activeTab === 'seo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <!-- View Header & Audit Bar -->
    <div class="bankai-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px; flex-wrap: wrap;">
                <h2 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <!-- Solar Broken Magnifier -->
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18.5 18.5L22 22" />
                        <path d="M6.75 3.27A9 9 0 0 1 18.23 6.75M20 11.5a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0Z" />
                        <path d="M10 8.5a3 3 0 0 1 3 3" />
                    </svg>
                    <span x-text="t('seoEngineTitle')">Autonomous SEO &amp; Schema Architecture</span>
                </h2>
                <span style="background-color: rgba(31, 136, 61, 0.2); border: 1px solid rgba(31, 136, 61, 0.5); color: #1A7F37; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-family: monospace;">
                    98/100 - <span x-text="isRtl ? 'عالی و بهینه' : 'Optimal'">Optimal</span>
                </span>
            </div>
            <p style="font-size: 12px; color: #8C959F; margin: 0;" x-text="t('seoEngineSubtitle')">
                Next-generation AI keyword optimization, 18+ JSON-LD schemas, instant indexing &amp; LLM search visibility.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button @click="runSeoAudit()"
                    style="background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 1px 2px rgba(46, 52, 64, 0.04);">
                <!-- Solar Broken Checklist -->
                <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 6h11M9 12h11M9 18h11M4 6l1 1 2-2M4 12l1 1 2-2M4 18l1 1 2-2" />
                </svg>
                <span x-text="t('runAudit')">Run 28-Point Audit</span>
            </button>

            <button @click="openSeoWizard()"
                    style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(9, 105, 218, 0.35); transition: all 0.2s;">
                <!-- Solar Broken Magic Stick -->
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 4V2m0 16v-2m8-7h-2M4 11H2m13.07-5.07l-1.41 1.41M5.34 16.66l-1.41 1.41m0-11.31l1.41 1.41m9.9 9.9l1.41 1.41M13 11l-9 9" />
                </svg>
                <span x-text="t('seoWizard')">SEO Setup Wizard</span>
            </button>
        </div>
    </div>

    <!-- Modular Feature Grid (12 Cards) -->
    <div class="bankai-grid-3" style="margin-bottom: 32px;">
        <?php if (!empty($state['seoModules']) && is_array($state['seoModules'])): ?>
            <?php foreach ($state['seoModules'] as $mod): 
                $mod_id = esc_attr($mod['id']);
                $title_en = esc_attr($mod['title']);
                $title_fa = esc_attr($mod['title_fa'] ?? $mod['title']);
                $desc_en = esc_attr($mod['description']);
                $desc_fa = esc_attr($mod['description_fa'] ?? $mod['description']);
                $badge = esc_html($mod['badge'] ?? '');
            ?>
                <div class="bankai-card bankai-card-interactive" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <!-- Top title + badge + switch -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(9, 105, 218, 0.12); display: flex; align-items: center; justify-content: center; color: #0969DA; flex-shrink: 0;">
                                    <?php if ($mod['id'] === 'ai_search_visibility'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="4" /><path d="M9 9h.01M15 9h.01M9 15h6M12 2v2m0 16v2" /></svg>
                                    <?php elseif ($mod['id'] === 'ai_meta_assistant'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v3m0 14v3M2 12h3m14 0h3M4.93 4.93l2.12 2.12m9.9 9.9l2.12 2.12" /><circle cx="12" cy="12" r="3" /></svg>
                                    <?php elseif ($mod['id'] === 'ai_link_genius'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" /><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" /></svg>
                                    <?php elseif ($mod['id'] === 'instant_indexing'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" /></svg>
                                    <?php elseif ($mod['id'] === 'xml_sitemaps'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" /><line x1="8" y1="2" x2="8" y2="18" /><line x1="16" y1="6" x2="16" y2="22" /></svg>
                                    <?php elseif ($mod['id'] === 'schema_builder'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" /><polyline points="3.27 6.96 12 12.01 20.73 6.96" /><line x1="12" y1="22.08" x2="12" y2="12" /></svg>
                                    <?php elseif ($mod['id'] === 'monitor_404'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                                    <?php elseif ($mod['id'] === 'image_seo'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><polyline points="21 15 16 10 5 21" /></svg>
                                    <?php elseif ($mod['id'] === 'acf_integration'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21" /><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" /></svg>
                                    <?php elseif ($mod['id'] === 'woocommerce_seo'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" /><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" /></svg>
                                    <?php elseif ($mod['id'] === 'local_seo'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" /></svg>
                                    <?php else: ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" /></svg>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 14px; color: #1F2328;"
                                         x-text="isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>'">
                                        <?php echo esc_html($mod['title']); ?>
                                    </div>
                                    <span style="background-color: rgba(9, 105, 218, 0.12); color: #0969DA; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 4px; display: inline-block; margin-top: 3px;">
                                        <?php echo $badge; ?>
                                    </span>
                                </div>
                            </div>

                            <label class="bankai-switch">
                                <input type="checkbox"
                                       x-model="seoState['<?php echo $mod_id; ?>']"
                                       @change="toggleSeoModule('<?php echo $mod_id; ?>')">
                                <span class="bankai-slider"></span>
                            </label>
                        </div>

                        <!-- Description -->
                        <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0 0 16px 0;"
                           x-text="isRtl ? '<?php echo $desc_fa; ?>' : '<?php echo $desc_en; ?>'">
                            <?php echo esc_html($mod['description']); ?>
                        </p>
                    </div>

                    <!-- Action / Settings Drawer Trigger -->
                    <div style="border-top: 1px solid #D0D7DE; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 700;"
                              :style="seoState['<?php echo $mod_id; ?>'] ? 'color: #1A7F37;' : 'color: #8C959F;'"
                              x-text="seoState['<?php echo $mod_id; ?>'] ? t('active') : t('disabled')">
                            Active
                        </span>

                        <button @click="openSeoDrawer('<?php echo $mod_id; ?>', isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>')"
                                style="background-color: #F6F8FA; border: 1px solid #D0D7DE; color: #0969DA; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px;">
                            <!-- Solar Broken Settings -->
                            <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3" />
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                            </svg>
                            <span x-text="t('configure')">Configure</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- SEO Module Settings Modal Drawer -->
    <div x-show="seoDrawer.show" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="seoDrawer.show = false"
             class="bankai-card"
             style="width: 560px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(46, 52, 64, 0.25); position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #D0D7DE; padding-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <!-- Solar Broken Settings -->
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                    </svg>
                    <span x-text="seoDrawer.title + (isRtl ? ' - تنظیمات اختصاصی' : ' Settings')"></span>
                </h3>
                <button @click="seoDrawer.show = false" style="background: none; border: none; color: #8C959F; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 6px;"
                           x-text="isRtl ? 'منطقه جغرافیایی و کشور هدف' : 'Primary Targeting Country / Region'">
                        Primary Targeting Country / Region
                    </label>
                    <select style="width: 100%; background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px; border-radius: 8px; font-size: 13px; outline: none;">
                        <option value="ir" selected>ایران (IR - سازگار با زبان فارسی و تاریخ شمسی)</option>
                        <option value="global">Global (Worldwide LLM &amp; Search Indexing)</option>
                        <option value="us">United States (US / English)</option>
                        <option value="eu">European Union (EU)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 6px;"
                           x-text="isRtl ? 'شدت بهینه‌سازی خودکار و سطح امنیتی' : 'Auto-Optimization Intensity & Safety Level'">
                        Auto-Optimization Intensity &amp; Safety Level
                    </label>
                    <div style="display: flex; gap: 12px;">
                        <button style="flex: 1; background-color: rgba(31, 136, 61, 0.15); border: 1px solid #1A7F37; color: #1A7F37; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;"
                                x-text="isRtl ? 'حالت امن (پیشنهادی)' : 'Safe Mode (Recommended)'">
                            Safe Mode (Recommended)
                        </button>
                        <button style="flex: 1; background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #656D76; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;"
                                x-text="isRtl ? 'رتبه‌بندی تهاجمی' : 'Aggressive Ranker'">
                            Aggressive Ranker
                        </button>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button @click="seoDrawer.show = false" style="background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #656D76; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;"
                        x-text="t('cancel')">
                    Cancel
                </button>
                <button @click="saveSeoDrawerSettings()" style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(9, 105, 218, 0.35);"
                        x-text="isRtl ? 'ذخیره تنظیمات ماژول' : 'Save Module Configuration'">
                    Save Module Configuration
                </button>
            </div>
        </div>
    </div>
</div>
