<!-- Tab 4: Speed & Cache Engine - GitHub Light Edition -->
<div id="tab-speed-cache" class="bankai-tab-pane" x-show="activeTab === 'speed'">
    <!-- View Header & Actions -->
    <div class="bankai-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px; flex-wrap: wrap;">
                <h2 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <!-- Solar Broken Rocket -->
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.5 9.5L18 6M15.5 15.5L12 19l-3.5-1.5L7 16l-2.5-2.5L3 10l3.5-3.5L10 3l6 3.5 4.5 1.5c.5.167.9.6.9 1.1a12.5 12.5 0 0 1-5.9 7.4Z" />
                        <path d="M9 15L4 20M2 22l3-1-2-2-1 3Z" />
                    </svg>
                    <span x-text="t('speedEngineTitle')">Autonomous Speed &amp; Cache Accelerator</span>
                </h2>
                <span style="background-color: rgba(31, 136, 61, 0.2); border: 1px solid rgba(31, 136, 61, 0.5); color: #1A7F37; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-family: monospace;">
                    32ms TTFB - <span x-text="isRtl ? 'لود زیر یک ثانیه' : 'Sub-Second Load'">Sub-Second Load</span>
                </span>
            </div>
            <p style="font-size: 12px; color: #8C959F; margin: 0;" x-text="t('speedEngineSubtitle')">
                Sub-50ms TTFB page caching, inline critical CSS, Redis object cache, and database optimization.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button @click="benchmarkVitals()"
                    style="background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 1px 2px rgba(46, 52, 64, 0.04);">
                <!-- Solar Broken Stopwatch -->
                <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="13" r="8" />
                    <path d="M12 9v4l2 2M12 5V2m-2 0h4" />
                </svg>
                <span x-text="t('benchmarkVitals')">Benchmark Core Web Vitals</span>
            </button>

            <button @click="purgeAllCaches()"
                    style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(9, 105, 218, 0.35); transition: all 0.2s;">
                <!-- Solar Broken Trash / Broom -->
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" />
                </svg>
                <span x-text="t('purgeCaches')">Purge All Caches</span>
            </button>
        </div>
    </div>

    <!-- Live Telemetry Bar -->
    <div class="bankai-grid-4">
        <div class="bankai-card bankai-card-interactive" style="padding: 16px;">
            <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                <svg class="solar-icon solar-icon-sm" style="color: #1A7F37;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83M22 12A10 10 0 0 0 12 2v10z" /></svg>
                <span x-text="isRtl ? 'نرخ اصابت به کش (Hit)' : 'Cache Hit Ratio'">Cache Hit Ratio</span>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #1A7F37; margin-top: 4px; font-family: monospace;">96.4%</div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 2px;"
                 x-text="isRtl ? 'کش وارنیش و صفحات HTML' : 'Edge Varnish & HTML'">Edge Varnish &amp; HTML</div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 16px;">
            <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                <span x-text="isRtl ? 'میانگین TTFB سرور' : 'Avg Server TTFB'">Avg Server TTFB</span>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #0969DA; margin-top: 4px; font-family: monospace;">32ms</div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 2px;"
                 x-text="isRtl ? 'بافر SSR سریع فعال است' : 'SSR Buffer active'">SSR Buffer active</div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 16px;">
            <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                <svg class="solar-icon solar-icon-sm" style="color: #8250DF;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="4" /><path d="M9 9h.01M15 9h.01M9 15h6M12 2v2m0 16v2" /></svg>
                <span x-text="isRtl ? 'تاخیر سوکت ردیس (Redis)' : 'Redis Socket Latency'">Redis Socket Latency</span>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #8250DF; margin-top: 4px; font-family: monospace;">0.42ms</div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 2px; font-family: monospace;">unix:///tmp/redis.sock</div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 16px;">
            <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                <svg class="solar-icon solar-icon-sm" style="color: #BC4C00;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" /></svg>
                <span x-text="isRtl ? 'رونوشت‌های دیتابیس' : 'Database Revisions'">Database Revisions</span>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: #BC4C00; margin-top: 4px; font-family: monospace;">142 <span style="font-size: 12px; color: #8C959F;" x-text="isRtl ? 'مورد' : 'Items'">Items</span></div>
            <div style="font-size: 11px; color: #0969DA; cursor: pointer; font-weight: 700; margin-top: 2px;" @click="optimizeDatabase()"
                 x-text="isRtl ? 'پاکسازی فوری دیتابیس &larr;' : 'Clean DB Now &rarr;'">
                Clean DB Now &rarr;
            </div>
        </div>
    </div>

    <!-- Modular Feature Grid (6 Cards) -->
    <div class="bankai-grid-3" style="margin-bottom: 32px;">
        <?php if (!empty($state['speedModules']) && is_array($state['speedModules'])): ?>
            <?php foreach ($state['speedModules'] as $mod): 
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
                                    <?php if ($mod['id'] === 'page_caching'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" /></svg>
                                    <?php elseif ($mod['id'] === 'asset_optimization'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6" /><polyline points="8 6 2 12 8 18" /><line x1="10" y1="20" x2="14" y2="4" /></svg>
                                    <?php elseif ($mod['id'] === 'database_optimizer'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" /></svg>
                                    <?php elseif ($mod['id'] === 'object_cache'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="4" /><path d="M9 9h.01M15 9h.01M9 15h6M12 2v2m0 16v2" /></svg>
                                    <?php elseif ($mod['id'] === 'server_compression'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" /></svg>
                                    <?php else: ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7" /><line x1="9" y1="20" x2="15" y2="20" /><line x1="12" y1="4" x2="12" y2="20" /></svg>
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
                                       x-model="speedState['<?php echo $mod_id; ?>']"
                                       @change="toggleSpeedModule('<?php echo $mod_id; ?>')">
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
                              :style="speedState['<?php echo $mod_id; ?>'] ? 'color: #1A7F37;' : 'color: #8C959F;'"
                              x-text="speedState['<?php echo $mod_id; ?>'] ? t('active') : t('disabled')">
                            Active
                        </span>

                        <button @click="openSpeedDrawer('<?php echo $mod_id; ?>', isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>')"
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

    <!-- Speed Module Settings Modal Drawer -->
    <div x-show="speedDrawer.show" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="speedDrawer.show = false"
             class="bankai-card"
             style="width: 560px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(46, 52, 64, 0.25); position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #D0D7DE; padding-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <!-- Solar Broken Settings -->
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                    </svg>
                    <span x-text="speedDrawer.title + (isRtl ? ' - تنظیمات کش و سرعت' : ' Settings')"></span>
                </h3>
                <button @click="speedDrawer.show = false" style="background: none; border: none; color: #8C959F; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 6px;"
                           x-text="isRtl ? 'زمان انقضای کش (TTL به ثانیه)' : 'Cache Expiry Time (TTL in Seconds)'">
                        Cache Expiry Time (TTL in Seconds)
                    </label>
                    <input type="number" value="86400"
                           style="width: 100%; background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px; border-radius: 8px; font-size: 13px; outline: none; font-family: monospace;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 6px;"
                           x-text="isRtl ? 'قوانین استثنای آدرس‌ها (مسیرهایی که کش نمی‌شوند)' : 'Exclusion Rules (URLs to Bypass Cache)'">
                        Exclusion Rules (URLs to Bypass Cache)
                    </label>
                    <textarea rows="3" style="width: 100%; background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px; border-radius: 8px; font-size: 12px; font-family: monospace; outline: none;">/cart/*
/checkout/*
/my-account/*</textarea>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button @click="speedDrawer.show = false" style="background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #656D76; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;"
                        x-text="t('cancel')">
                    Cancel
                </button>
                <button @click="saveSpeedDrawerSettings()" style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(9, 105, 218, 0.35);"
                        x-text="isRtl ? 'ذخیره پیکربندی کش' : 'Save Cache Configuration'">
                    Save Cache Configuration
                </button>
            </div>
        </div>
    </div>
</div>
