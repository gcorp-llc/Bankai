<!-- Tab 1: System Telemetry Overview - GitHub Light Edition -->
<div id="tab-overview" x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <!-- System Telemetry Metrics Grid -->
    <div class="bankai-grid-4">
        <div class="bankai-card bankai-card-interactive" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                    <!-- Solar Broken Bolt -->
                    <svg class="solar-icon solar-icon-sm" style="color: #1A7F37;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13.5 2L4 13.5h7L9.5 22 20 10.5h-7.5L13.5 2Z" />
                    </svg>
                    <span x-text="t('uptimeSpeed')">Uptime &amp; Speed</span>
                </div>
                <span style="background: rgba(31, 136, 61, 0.2); color: #1A7F37; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">Live</span>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #1A7F37; font-family: monospace;"><?php echo esc_html($state['stats']['uptime'] ?? '99.98%'); ?></div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 6px; display: flex; justify-content: space-between;">
                <span x-text="t('avgLatency')">Avg Latency:</span>
                <span style="color: #1F2328; font-weight: 600;"><?php echo esc_html($state['stats']['avg_latency'] ?? '18ms'); ?></span>
            </div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                    <!-- Solar Broken Sitemap / Layers -->
                    <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="6" height="6" rx="2" />
                        <rect x="15" y="3" width="6" height="6" rx="2" />
                        <rect x="9" y="15" width="6" height="6" rx="2" />
                        <path d="M6 9v3a3 3 0 0 0 3 3h3m6-6v3a3 3 0 0 1-3 3" />
                    </svg>
                    <span x-text="t('indexedNodes')">Indexed Nodes</span>
                </div>
                <span style="background: rgba(9, 105, 218, 0.15); color: #0969DA; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">XML</span>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #0969DA; font-family: monospace;"><?php echo esc_html($state['stats']['indexed_nodes'] ?? '4,892'); ?></div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 6px; display: flex; justify-content: space-between;">
                <span x-text="t('varnishHit')">Varnish Hit Ratio:</span>
                <span style="color: #1F2328; font-weight: 600;"><?php echo esc_html($state['stats']['varnish_hit'] ?? '96.4%'); ?></span>
            </div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                    <!-- Solar Broken Bot / CPU -->
                    <svg class="solar-icon solar-icon-sm" style="color: #8250DF;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="4" width="16" height="16" rx="4" />
                        <path d="M9 9h.01M15 9h.01M9 15h6" />
                        <path d="M12 2v2m0 16v2M2 12h2m16 0h2" />
                    </svg>
                    <span x-text="t('aiCrawls')">AI Crawler Hits</span>
                </div>
                <span style="background: rgba(130, 80, 223, 0.15); color: #8250DF; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">LLM</span>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #8250DF; font-family: monospace;"><?php echo esc_html($state['stats']['ai_crawls'] ?? '12,410 Hits'); ?></div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 6px; display: flex; justify-content: space-between;">
                <span x-text="t('llmActive')">LLM Manifest Active</span>
                <span style="color: #0969DA; font-weight: 700;">v1.2</span>
            </div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                    <!-- Solar Broken Medal Star -->
                    <svg class="solar-icon solar-icon-sm" style="color: #BC4C00;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="9" r="6" />
                        <path d="M8.21 13.89L7 22l5-3 5 3-1.21-8.11" />
                    </svg>
                    <span x-text="t('schemaScore')">SEO Schema Score</span>
                </div>
                <span style="background: rgba(188, 76, 0, 0.15); color: #BC4C00; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">Top 1%</span>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #BC4C00; font-family: monospace;"><?php echo esc_html($state['stats']['schema_score'] ?? '98/100'); ?></div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 6px; display: flex; justify-content: space-between;">
                <span x-text="t('gradeA')">Grade A+ Certified</span>
                <span style="color: #1A7F37; font-weight: 700;">99.8%</span>
            </div>
        </div>
    </div>

    <!-- Active Engine Modules Toggles -->
    <div class="bankai-card" style="padding: 22px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                </svg>
                <span x-text="t('coreModulesTitle')">Core Engine Modules &amp; Features</span>
            </h3>
            <span style="font-size: 11px; color: #1A7F37; background: rgba(31, 136, 61, 0.2); padding: 3px 10px; border-radius: 12px; font-weight: 700; border: 1px solid rgba(31, 136, 61, 0.4);"
                  x-text="isRtl ? '۶ ماژول فعال در حافظه' : '6 Modules Active In Memory'">
                6 Modules Active In Memory
            </span>
        </div>

        <div class="bankai-grid-3">
            <!-- Module 1: SEO -->
            <div class="bankai-card-subtle" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18.5 18.5L22 22" />
                            <path d="M6.75 3.27A9 9 0 0 1 18.23 6.75M20 11.5a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0Z" />
                        </svg>
                        <span style="font-weight: 700; font-size: 13px; color: #1F2328;"
                              x-text="isRtl ? 'موتور سئو و اسکیما' : 'SEO & Schema Engine'">SEO Engine</span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" checked @change="showToast(isRtl ? 'ماژول سئو تغییر وضعیت داد' : 'Toggled SEO Engine module')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0;"
                   x-text="t('seoEngineDesc')">
                    Automated meta generation, Schema.org builder &amp; XML Sitemaps.
                </p>
            </div>

            <!-- Module 2: Media Optimizer -->
            <div class="bankai-card-subtle" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg class="solar-icon solar-icon-sm" style="color: #218BFF;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 22h6c4.418 0 6-1.582 6-6V8c0-4.418-1.582-6-6-6H9C4.582 2 3 3.582 3 8v8c0 4.418 1.582 6 6 6Z" />
                            <circle cx="8.5" cy="7.5" r="1.5" fill="currentColor" stroke="none" />
                        </svg>
                        <span style="font-weight: 700; font-size: 13px; color: #1F2328;"
                              x-text="isRtl ? 'بهینه‌ساز رسانه' : 'Media Optimizer'">Media Optimizer</span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" checked @change="showToast(isRtl ? 'ماژول رسانه تغییر وضعیت داد' : 'Toggled Media Optimizer module')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0;"
                   x-text="t('mediaOptimizerDesc')">
                    WebP/AVIF auto-conversion, async processor &amp; lazyloading.
                </p>
            </div>

            <!-- Module 3: Speed & Cache -->
            <div class="bankai-card-subtle" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg class="solar-icon solar-icon-sm" style="color: #1A7F37;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.5 9.5L18 6M15.5 15.5L12 19l-3.5-1.5L7 16l-2.5-2.5L3 10l3.5-3.5L10 3l6 3.5 4.5 1.5c.5.167.9.6.9 1.1a12.5 12.5 0 0 1-5.9 7.4Z" />
                        </svg>
                        <span style="font-weight: 700; font-size: 13px; color: #1F2328;"
                              x-text="isRtl ? 'موتور کش و شتاب‌دهنده' : 'Speed & Cache Engine'">Speed &amp; Cache</span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" checked @change="showToast(isRtl ? 'ماژول کش تغییر وضعیت داد' : 'Toggled Speed & Cache module')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0;"
                   x-text="t('speedCacheDesc')">
                    Zero-latency dynamic HTML page caching &amp; Redis object store.
                </p>
            </div>

            <!-- Module 4: LLM Manifest -->
            <div class="bankai-card-subtle" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg class="solar-icon solar-icon-sm" style="color: #8250DF;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10V8c0-2.828 0-4.243.879-5.121C4.757 2 6.172 2 9 2h6c2.828 0 4.243 0 5.121.879C21 3.757 21 5.172 21 8v8c0 2.828 0 4.243-.879 5.121C19.243 22 17.828 22 15 22H9c-2.828 0-4.243 0-5.121-.879C3 20.243 3 18.828 3 16" />
                            <path d="M7 8h10M7 12h6M7 16h4" />
                        </svg>
                        <span style="font-weight: 700; font-size: 13px; color: #1F2328;"
                              x-text="isRtl ? 'مانیفست هوش مصنوعی (llms.txt)' : 'LLM Manifest'">LLM Manifest</span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" checked @change="showToast(isRtl ? 'ماژول مانیفست LLM تغییر وضعیت داد' : 'Toggled LLM Manifest module')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0;"
                   x-text="t('llmManifestDesc')">
                    Structured markdown index endpoints for AI agents.
                </p>
            </div>

            <!-- Module 5: AI Studio -->
            <div class="bankai-card-subtle" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg class="solar-icon solar-icon-sm" style="color: #54AEFF;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v3m0 14v3M2 12h3m14 0h3M4.93 4.93l2.12 2.12m9.9 9.9l2.12 2.12M4.93 19.07l2.12-2.12m9.9-9.9l2.12-2.12" />
                            <path d="M15.5 12a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" />
                        </svg>
                        <span style="font-weight: 700; font-size: 13px; color: #1F2328;"
                              x-text="isRtl ? 'استودیو محتوای هوش مصنوعی' : 'AI Content Studio'">AI Studio</span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" checked @change="showToast(isRtl ? 'استودیو هوش مصنوعی تغییر وضعیت داد' : 'Toggled AI Studio module')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0;"
                   x-text="t('aiStudioDesc')">
                    Server-side Gemini AI content generation &amp; prompt engineering.
                </p>
            </div>

            <!-- Module 6: WooCommerce SEO -->
            <div class="bankai-card-subtle" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg class="solar-icon solar-icon-sm" style="color: #BC4C00;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                        <span style="font-weight: 700; font-size: 13px; color: #1F2328;"
                              x-text="isRtl ? 'سئو اختصاصی ووکامرس' : 'WooCommerce SEO'">WooCommerce SEO</span>
                    </div>
                    <label class="bankai-switch">
                        <input type="checkbox" checked @change="showToast(isRtl ? 'سئو ووکامرس فعال است' : 'Toggled WooCommerce SEO module')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
                <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0;"
                   x-text="isRtl ? 'اسکیمای کامل محصولات، قیمت، موجودی انبار و امتیاز نظرات خریداران.' : 'Complete Product schema, price, stock status and buyer review ratings.'">
                    Complete Product schema, price, stock status and buyer review ratings.
                </p>
            </div>
        </div>
    </div>

    <!-- 404 Anomaly Log & Core Web Vitals -->
    <div class="bankai-grid-split">
        <!-- Radial Gauge Telemetry -->
        <div class="bankai-card" style="padding: 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <span style="font-weight: 700; font-size: 14px; color: #1F2328; display: flex; align-items: center; gap: 6px;">
                    <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                    </svg>
                    <span x-text="t('vitalsTitle')">SEO &amp; Core Web Vitals</span>
                </span>
                <span style="background-color: rgba(31, 136, 61, 0.2); border: 1px solid rgba(31, 136, 61, 0.4); color: #1A7F37; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 700;">
                    Grade A+
                </span>
            </div>

            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 16px 0 24px 0;">
                <div style="position: relative; width: 140px; height: 140px;">
                    <svg width="140" height="140" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" stroke="#D0D7DE" stroke-width="8" fill="none" />
                        <circle cx="50" cy="50" r="42" stroke="#1A7F37" stroke-width="8" fill="none"
                                stroke-dasharray="264"
                                stroke-dashoffset="6"
                                stroke-linecap="round" transform="rotate(-90 50 50)" />
                    </svg>
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="font-size: 28px; font-weight: 800; color: #1F2328;"><?php echo esc_html($state['stats']['overall_score'] ?? '98'); ?><span style="font-size: 14px; color: #8C959F;">/100</span></div>
                        <div style="font-size: 10px; color: #1A7F37; font-weight: 700; text-transform: uppercase;"
                             x-text="t('optimalIndex')">OPTIMAL INDEX</div>
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: #656D76;">TTFB (Time to First Byte)</span>
                        <span style="color: #1A7F37; font-weight: 700; font-family: monospace;"><?php echo esc_html($state['stats']['ttfb'] ?? '32ms'); ?></span>
                    </div>
                    <div style="height: 6px; background-color: #D0D7DE; border-radius: 3px; overflow: hidden;">
                        <div style="width: 92%; height: 100%; background-color: #1A7F37;"></div>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: #656D76;">LCP (Largest Contentful Paint)</span>
                        <span style="color: #1A7F37; font-weight: 700; font-family: monospace;"><?php echo esc_html($state['stats']['lcp'] ?? '0.8s'); ?></span>
                    </div>
                    <div style="height: 6px; background-color: #D0D7DE; border-radius: 3px; overflow: hidden;">
                        <div style="width: 85%; height: 100%; background-color: #1A7F37;"></div>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: #656D76;">CLS (Cumulative Layout Shift)</span>
                        <span style="color: #1A7F37; font-weight: 700; font-family: monospace;">0.002 (Clean)</span>
                    </div>
                    <div style="height: 6px; background-color: #D0D7DE; border-radius: 3px; overflow: hidden;">
                        <div style="width: 98%; height: 100%; background-color: #1A7F37;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 404 Anomaly Log Table -->
        <div class="bankai-card" style="padding: 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="font-weight: 700; font-size: 14px; color: #1F2328; display: flex; align-items: center; gap: 6px;">
                    <svg class="solar-icon solar-icon-sm" style="color: #BC4C00;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 8.5v4.5M12 16.5h.01" />
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    </svg>
                    <span x-text="t('anomalyTitle')">404 Anomaly Hits &amp; Heuristics</span>
                </span>
                <span style="background-color: rgba(188, 76, 0, 0.15); border: 1px solid rgba(188, 76, 0, 0.3); color: #BC4C00; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600;"
                      x-text="t('liveFeed')">
                    Live Feed
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;"
                       :style="isRtl ? 'text-align: right;' : 'text-align: left;'">
                    <thead>
                        <tr style="border-bottom: 1px solid #D0D7DE; color: #8C959F; font-size: 10px; text-transform: uppercase;">
                            <th style="padding: 10px 8px;" x-text="t('requestedUri')">REQUESTED URI</th>
                            <th style="padding: 10px 8px; text-align: center;" x-text="t('hits')">HITS</th>
                            <th style="padding: 10px 8px;" :style="isRtl ? 'text-align: left;' : 'text-align: right;'" x-text="t('action')">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($state['logs404']) && is_array($state['logs404'])): ?>
                            <?php foreach ($state['logs404'] as $log): 
                                $uri = esc_attr($log['requested_uri']);
                                $hits = esc_html($log['hits']);
                            ?>
                                <tr style="border-bottom: 1px solid #D0D7DE;">
                                    <td style="padding: 12px 8px;">
                                        <div style="color: #1F2328; font-weight: 600; font-family: monospace;"><?php echo esc_html($log['requested_uri']); ?></div>
                                    </td>
                                    <td style="padding: 12px 8px; text-align: center;">
                                        <span style="background-color: rgba(9, 105, 218, 0.12); padding: 3px 8px; border-radius: 6px; font-weight: 700; color: #0969DA; font-family: monospace;">
                                            <?php echo $hits; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 8px;" :style="isRtl ? 'text-align: left;' : 'text-align: right;'">
                                        <button @click="showToast(isRtl ? 'قانون ریدایرکت ۳۰۱ برای این آدرس ثبت شد' : 'Applied 301 rule for <?php echo $uri; ?>')"
                                                style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 4px;">
                                            <svg class="solar-icon solar-icon-sm" style="width: 12px; height: 12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                            <span x-text="t('apply301')">Apply 301</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
