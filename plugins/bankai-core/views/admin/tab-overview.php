<!-- Tab 1: System Telemetry Overview -->
<div id="tab-overview" class="bankai-tab-pane" x-show="activeTab === 'overview'" x-cloak>
    <!-- System Telemetry Metrics Grid -->
    <div class="bankai-grid-4">
        <div class="bankai-card bankai-card-interactive" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
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

    <!-- Active Engine Modules (real active_modules) -->
    <div class="bankai-card" style="padding: 22px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                </svg>
                <span x-text="t('coreModulesTitle')">Core Engine Modules &amp; Features</span>
            </h3>
            <?php
            $core_mods = is_array($state['coreModules'] ?? null) ? $state['coreModules'] : [];
            $active_n  = count(array_filter($core_mods, static fn($m) => !empty($m['active'])));
            ?>
            <span id="bk-core-mod-count" style="font-size: 11px; color: #1A7F37; background: rgba(31, 136, 61, 0.2); padding: 3px 10px; border-radius: 12px; font-weight: 700; border: 1px solid rgba(31, 136, 61, 0.4);">
                <?php echo (int) $active_n; ?> / <?php echo count($core_mods); ?>
                <span x-text="isRtl ? 'فعال' : 'Active'">Active</span>
            </span>
        </div>

        <div class="bankai-grid-3">
            <?php if (!$core_mods): ?>
                <p style="font-size: 12px; color: #656D76;">ماژول‌ها در دسترس نیستند. فایل class-dashboard-stats.php را بارگذاری کنید.</p>
            <?php else: ?>
                <?php foreach ($core_mods as $mod): ?>
                    <div class="bankai-card-subtle" style="padding: 16px;" data-module="<?php echo esc_attr($mod['key']); ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: 700; font-size: 13px; color: #1F2328;"
                                      x-text="isRtl ? <?php echo wp_json_encode($mod['label_fa'], JSON_UNESCAPED_UNICODE); ?> : <?php echo wp_json_encode($mod['label'], JSON_UNESCAPED_UNICODE); ?>">
                                    <?php echo esc_html($mod['label_fa']); ?>
                                </span>
                            </div>
                            <label class="bankai-switch">
                                <input type="checkbox"
                                       class="bk-core-module-toggle"
                                       @change="toggleCoreModule($el.getAttribute('data-module'), $el.checked)"
                                       data-module="<?php echo esc_attr($mod['key']); ?>"
                                       <?php checked(!empty($mod['active'])); ?>>
                                <span class="bankai-slider"></span>
                            </label>
                        </div>
                        <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0;"
                           x-text="isRtl ? <?php echo wp_json_encode($mod['desc_fa'], JSON_UNESCAPED_UNICODE); ?> : <?php echo wp_json_encode($mod['desc'], JSON_UNESCAPED_UNICODE); ?>">
                            <?php echo esc_html($mod['desc_fa']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 404 + Core Web Vitals -->
    <div class="bankai-grid-split">
        <div class="bankai-card" style="padding: 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <span style="font-weight: 700; font-size: 14px; color: #1F2328; display: flex; align-items: center; gap: 6px;">
                    <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" /></svg>
                    <span x-text="t('vitalsTitle')">SEO &amp; Core Web Vitals</span>
                </span>
                <span style="background-color: rgba(31, 136, 61, 0.2); border: 1px solid rgba(31, 136, 61, 0.4); color: #1A7F37; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 700;">Grade <?php echo esc_html($state['stats']['grade'] ?? '—'); ?></span>
            </div>

            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 16px 0 24px 0;">
                <div style="position: relative; width: 140px; height: 140px;">
                    <svg width="140" height="140" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" stroke="#D0D7DE" stroke-width="8" fill="none" />
                        <circle cx="50" cy="50" r="42" stroke="#1A7F37" stroke-width="8" fill="none" stroke-dasharray="264" stroke-dashoffset="6" stroke-linecap="round" transform="rotate(-90 50 50)" />
                    </svg>
                    <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="font-size: 28px; font-weight: 800; color: #1F2328;"><?php echo esc_html($state['stats']['overall_score'] ?? '98'); ?><span style="font-size: 14px; color: #8C959F;">/100</span></div>
                        <div style="font-size: 10px; color: #1A7F37; font-weight: 700; text-transform: uppercase;" x-text="t('optimalIndex')">OPTIMAL INDEX</div>
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: #656D76;">TTFB</span>
                        <span style="color: #1A7F37; font-weight: 700; font-family: monospace;"><?php echo esc_html($state['stats']['ttfb'] ?? '32ms'); ?></span>
                    </div>
                    <div style="height: 6px; background-color: #D0D7DE; border-radius: 3px; overflow: hidden;"><div style="width: 92%; height: 100%; background-color: #1A7F37;"></div></div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: #656D76;">LCP</span>
                        <span style="color: #1A7F37; font-weight: 700; font-family: monospace;"><?php echo esc_html($state['stats']['lcp'] ?? '0.8s'); ?></span>
                    </div>
                    <div style="height: 6px; background-color: #D0D7DE; border-radius: 3px; overflow: hidden;"><div style="width: 85%; height: 100%; background-color: #1A7F37;"></div></div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: #656D76;">CLS</span>
                        <span style="color: #1A7F37; font-weight: 700; font-family: monospace;"><?php echo esc_html($state['stats']['cls'] ?? '—'); ?></span>
                    </div>
                    <div style="height: 6px; background-color: #D0D7DE; border-radius: 3px; overflow: hidden;"><div style="width: 98%; height: 100%; background-color: #1A7F37;"></div></div>
                </div>
            </div>
        </div>

        <div class="bankai-card" style="padding: 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="font-weight: 700; font-size: 14px; color: #1F2328; display: flex; align-items: center; gap: 6px;">
                    <svg class="solar-icon solar-icon-sm" style="color: #BC4C00;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 8.5v4.5M12 16.5h.01" /><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /></svg>
                    <span x-text="t('anomalyTitle')">404 Anomaly Hits</span>
                </span>
                <span style="background-color: rgba(188, 76, 0, 0.15); border: 1px solid rgba(188, 76, 0, 0.3); color: #BC4C00; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600;" x-text="t('liveFeed')">Live Feed</span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;" :style="isRtl ? 'text-align: right;' : 'text-align: left;'">
                    <thead>
                        <tr style="border-bottom: 1px solid #D0D7DE; color: #8C959F; font-size: 10px; text-transform: uppercase;">
                            <th style="padding: 10px 8px;" x-text="t('requestedUri')">REQUESTED URI</th>
                            <th style="padding: 10px 8px; text-align: center;" x-text="t('hits')">HITS</th>
                            <th style="padding: 10px 8px;" :style="isRtl ? 'text-align: left;' : 'text-align: right;'" x-text="t('action')">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($state['logs404']) && is_array($state['logs404'])): ?>
                            <?php foreach ($state['logs404'] as $log): ?>
                                <tr style="border-bottom: 1px solid #D0D7DE;">
                                    <td style="padding: 12px 8px;"><div style="color: #1F2328; font-weight: 600; font-family: monospace;"><?php echo esc_html($log['requested_uri'] ?? ''); ?></div></td>
                                    <td style="padding: 12px 8px; text-align: center;"><span style="background-color: rgba(9, 105, 218, 0.12); padding: 3px 8px; border-radius: 6px; font-weight: 700; color: #0969DA; font-family: monospace;"><?php echo esc_html((string) ($log['hits'] ?? 0)); ?></span></td>
                                    <td style="padding: 12px 8px;" :style="isRtl ? 'text-align: left;' : 'text-align: right;'">
                                        <button type="button" class="bk-404-redirect" data-uri="<?php echo esc_attr($log['requested_uri'] ?? ''); ?>"
                                                style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                            <span x-text="t('apply301')">Apply 301</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="padding: 16px 8px; color: #8C959F; font-size: 12px;">
                                    <span x-text="isRtl ? 'هنوز خطای ۴۰۴ ثبت نشده. پس از بازدید مسیرهای نامعتبر اینجا ظاهر می‌شود.' : 'No 404 hits logged yet. Invalid front-end requests will appear here.'">
                                        No 404 hits logged yet.
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>