<!-- Tab 5: Media & Watermark Studio - GitHub Light Edition -->
<div id="tab-media-watermark" x-show="activeTab === 'media'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <!-- View Header & Actions -->
    <div class="bankai-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px; flex-wrap: wrap;">
                <h2 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <!-- Solar Broken Gallery / Image -->
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 22h6c4.418 0 6-1.582 6-6V8c0-4.418-1.582-6-6-6H9C4.582 2 3 3.582 3 8v8c0 4.418 1.582 6 6 6Z" />
                        <circle cx="8.5" cy="7.5" r="1.5" fill="currentColor" stroke="none" />
                        <path d="M3 16l6.5-6.5c.78-.78 2.05-.78 2.83 0L21 18M14 13l2.5-2.5c.78-.78 2.05-.78 2.83 0L21 12" />
                    </svg>
                    <span x-text="t('mediaEngineTitle')">Next-Gen Media Engine &amp; Dynamic Watermark Studio</span>
                </h2>
                <span style="background-color: rgba(31, 136, 61, 0.2); border: 1px solid rgba(31, 136, 61, 0.5); color: #1A7F37; font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-family: monospace;">
                    1.4 GB <span x-text="isRtl ? 'صرفه‌جویی (کاهش ۸۲٪ حجم)' : 'Saved (82% Avg Compression Rate)'">Saved (82% Avg Compression Rate)</span>
                </span>
            </div>
            <p style="font-size: 12px; color: #8C959F; margin: 0;" x-text="t('mediaEngineSubtitle')">
                WebP &amp; AVIF auto-conversion, dynamic watermark overlay, EXIF metadata stripping &amp; CDN offloading.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button @click="regenerateThumbnails()"
                    style="background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 1px 2px rgba(46, 52, 64, 0.04);">
                <!-- Solar Broken Refresh -->
                <svg class="solar-icon solar-icon-sm" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19.95 11a8 8 0 1 0-.5 4m.5-4h-5m5 0V6" />
                </svg>
                <span x-text="t('regenThumbs')">Regenerate Thumbnails</span>
            </button>

            <button @click="bulkConvertMedia()"
                    style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(9, 105, 218, 0.35); transition: all 0.2s;">
                <!-- Solar Broken Bolt -->
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
                <span x-text="t('bulkConvert')">Bulk Convert Media Library</span>
            </button>
        </div>
    </div>

    <!-- Server Environment & Format Badges -->
    <div class="bankai-grid-4">
        <div class="bankai-card bankai-card-interactive" style="padding: 16px;">
            <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase;">Image GD Engine</div>
            <div style="font-size: 16px; font-weight: 800; color: #1A7F37; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                <span x-text="isRtl ? 'فعال (نسخه ۲.۳)' : 'Active (v2.3)'">Active (v2.3)</span>
            </div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 2px;">PNG, JPEG, WebP</div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 16px;">
            <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase;">Imagick Processor</div>
            <div style="font-size: 16px; font-weight: 800; color: #1A7F37; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                <span x-text="isRtl ? 'فعال (نسخه ۷)' : 'Active (ImageMagick 7)'">Active (ImageMagick 7)</span>
            </div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 2px;">AVIF &amp; WebP</div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 16px;">
            <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase;">AVIF Compression</div>
            <div style="font-size: 16px; font-weight: 800; color: #0969DA; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                <span x-text="isRtl ? 'پشتیبانی کامل' : 'Enabled'">Enabled</span>
            </div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 2px;" x-text="isRtl ? '۵۰٪ سبک‌تر از وب‌پی' : '50% smaller than WebP'">50% smaller than WebP</div>
        </div>

        <div class="bankai-card bankai-card-interactive" style="padding: 16px;">
            <div style="font-size: 11px; color: #8C959F; font-weight: 700; text-transform: uppercase;" x-text="isRtl ? 'پشتیبان فایل اصلی' : 'Original Backup'">Original Backup</div>
            <div style="font-size: 16px; font-weight: 800; color: #BC4C00; margin-top: 4px;" x-text="isRtl ? 'حالت امن فعال است' : 'Safe Mode Active'">Safe Mode Active</div>
            <div style="font-size: 11px; color: #8C959F; margin-top: 2px;">JPG/PNG Retained</div>
        </div>
    </div>

    <!-- Dynamic Watermark Studio Visual Preview Card -->
    <div class="bankai-card" style="padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #1F2328; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            <!-- Solar Broken Stamp / Palette -->
            <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22C6.477 22 2 17.523 2 12c0-4.478 2.946-8.267 7-9.535M16.5 3.12C19.832 4.675 22 8.09 22 12c0 3.5-1.5 5.5-4 5.5h-1.5c-1.105 0-2 .895-2 2 0 1.38 1.12 2.5 2.5 2.5" />
                <circle cx="8" cy="10" r="1.5" fill="currentColor" stroke="none" />
                <circle cx="12" cy="7" r="1.5" fill="currentColor" stroke="none" />
            </svg>
            <span x-text="t('watermarkStudio')">Interactive Watermark Position Studio</span>
        </h3>

        <div class="bankai-grid-split">
            <!-- Interactive 9-Grid Selector -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 12px;"
                       x-text="t('selectAnchor')">
                    Select Watermark Overlay Anchor Position
                </label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; max-width: 280px; margin-bottom: 16px;">
                    <button @click="watermarkStudio.position = 'top-left'"
                            :style="watermarkStudio.position === 'top-left' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'بالا راست' : 'Top Left'">Top Left</span></button>
                    <button @click="watermarkStudio.position = 'top-center'"
                            :style="watermarkStudio.position === 'top-center' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'بالا وسط' : 'Top Center'">Top Center</span></button>
                    <button @click="watermarkStudio.position = 'top-right'"
                            :style="watermarkStudio.position === 'top-right' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'بالا چپ' : 'Top Right'">Top Right</span></button>

                    <button @click="watermarkStudio.position = 'center-left'"
                            :style="watermarkStudio.position === 'center-left' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'راست' : 'Left'">Left</span></button>
                    <button @click="watermarkStudio.position = 'center'"
                            :style="watermarkStudio.position === 'center' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'مرکز' : 'Center'">Center</span></button>
                    <button @click="watermarkStudio.position = 'center-right'"
                            :style="watermarkStudio.position === 'center-right' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'چپ' : 'Right'">Right</span></button>

                    <button @click="watermarkStudio.position = 'bottom-left'"
                            :style="watermarkStudio.position === 'bottom-left' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'پایین راست' : 'Bottom Left'">Bottom Left</span></button>
                    <button @click="watermarkStudio.position = 'bottom-center'"
                            :style="watermarkStudio.position === 'bottom-center' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'پایین وسط' : 'Bottom Center'">Bottom Center</span></button>
                    <button @click="watermarkStudio.position = 'bottom-right'"
                            :style="watermarkStudio.position === 'bottom-right' ? 'background-color: #0969DA; color: #FFFFFF; border-color: #0969DA;' : 'background-color: #F6F8FA; color: #656D76; border-color: #D0D7DE;'"
                            style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center;"><span x-text="isRtl ? 'پایین چپ' : 'Bottom Right'">Bottom Right</span></button>
                </div>

                <!-- Watermark Opacity & Text Settings -->
                <div style="display: flex; flex-direction: column; gap: 12px; max-width: 280px;">
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #656D76; margin-bottom: 4px;">
                            <span x-text="t('watermarkOpacity')">Watermark Opacity</span>
                            <span style="color: #0969DA; font-weight: 800; font-family: monospace;" x-text="watermarkStudio.opacity + '%'">75%</span>
                        </div>
                        <input type="range" min="10" max="100" x-model="watermarkStudio.opacity" style="width: 100%; accent-color: #0969DA; cursor: pointer;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; color: #656D76; margin-bottom: 4px;" x-text="t('watermarkText')">Watermark Custom Text</label>
                        <input type="text" x-model="watermarkStudio.text"
                               style="width: 100%; background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 8px 12px; border-radius: 6px; font-size: 12px; outline: none;">
                    </div>
                </div>
            </div>

            <!-- Live Canvas Simulation -->
            <div style="position: relative; min-height: 240px; background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=600&q=80" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;">
                
                <!-- Live Watermark Overlay -->
                <div style="position: absolute; padding: 12px; pointer-events: none; transition: all 0.3s ease;"
                     :style="getWatermarkPositionStyle()">
                    <span style="background-color: rgba(46, 52, 64, 0.75); color: #FFFFFF; padding: 6px 14px; border-radius: 4px; font-size: 12px; font-weight: 800; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(6px); letter-spacing: 0.5px;"
                          :style="'opacity: ' + (watermarkStudio.opacity / 100)"
                          x-text="watermarkStudio.text || '© BANKAI WP ENGINE'">
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modular Feature Grid (6 Cards) -->
    <div class="bankai-grid-3" style="margin-bottom: 32px;">
        <?php if (!empty($state['mediaModules']) && is_array($state['mediaModules'])): ?>
            <?php foreach ($state['mediaModules'] as $mod): 
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
                                    <?php if ($mod['id'] === 'webp_avif_converter'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 22h6c4.418 0 6-1.582 6-6V8c0-4.418-1.582-6-6-6H9C4.582 2 3 3.582 3 8v8c0 4.418 1.582 6 6 6Z" /><circle cx="8.5" cy="7.5" r="1.5" fill="currentColor" stroke="none" /></svg>
                                    <?php elseif ($mod['id'] === 'dynamic_watermarking'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22C6.477 22 2 17.523 2 12c0-4.478 2.946-8.267 7-9.535M16.5 3.12C19.832 4.675 22 8.09 22 12" /><circle cx="12" cy="12" r="3" /></svg>
                                    <?php elseif ($mod['id'] === 'retina_generator'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" /><line x1="8" y1="21" x2="16" y2="21" /><line x1="12" y1="17" x2="12" y2="21" /></svg>
                                    <?php elseif ($mod['id'] === 'svg_sanitizer'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><polyline points="9 12 11 14 15 10" /></svg>
                                    <?php elseif ($mod['id'] === 'exif_metadata_scrubber'): ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" /><line x1="1" y1="1" x2="23" y2="23" /></svg>
                                    <?php else: ?>
                                        <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z" /><polyline points="16 16 12 12 8 16" /><line x1="12" y1="12" x2="12" y2="21" /></svg>
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
                                       x-model="mediaState['<?php echo $mod_id; ?>']"
                                       @change="toggleMediaModule('<?php echo $mod_id; ?>')">
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
                              :style="mediaState['<?php echo $mod_id; ?>'] ? 'color: #1A7F37;' : 'color: #8C959F;'"
                              x-text="mediaState['<?php echo $mod_id; ?>'] ? t('active') : t('disabled')">
                            Active
                        </span>

                        <button @click="openMediaDrawer('<?php echo $mod_id; ?>', isRtl ? '<?php echo $title_fa; ?>' : '<?php echo $title_en; ?>')"
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
    </div>

    <!-- Media Module Settings Modal Drawer -->
    <div x-show="mediaDrawer.show" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="mediaDrawer.show = false"
             class="bankai-card"
             style="width: 560px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(46, 52, 64, 0.25); position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #D0D7DE; padding-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <!-- Solar Broken Settings -->
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                    </svg>
                    <span x-text="mediaDrawer.title + (isRtl ? ' - تنظیمات رسانه' : ' Settings')"></span>
                </h3>
                <button @click="mediaDrawer.show = false" style="background: none; border: none; color: #8C959F; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 6px;"
                           x-text="isRtl ? 'کیفیت فشرده‌سازی WebP و AVIF (۰ الی ۱۰۰)' : 'WebP / AVIF Compression Quality Level (0 - 100)'">
                        WebP / AVIF Compression Quality Level (0 - 100)
                    </label>
                    <input type="number" value="82" min="10" max="100"
                           style="width: 100%; background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px; border-radius: 8px; font-size: 13px; outline: none; font-family: monospace;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 6px;"
                           x-text="isRtl ? 'حداقل ابعاد مجاز برای درج واترمارک' : 'Minimum Dimension Threshold for Watermarking'">
                        Minimum Dimension Threshold for Watermarking
                    </label>
                    <select style="width: 100%; background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px; border-radius: 8px; font-size: 13px; outline: none;">
                        <option value="300">تصاویر زیر ۳۰۰ پیکسل نادیده گرفته شوند</option>
                        <option value="500">تصاویر زیر ۵۰۰ پیکسل نادیده گرفته شوند</option>
                        <option value="0">اعمال بر تمام تصاویر بدون توجه به اندازه</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button @click="mediaDrawer.show = false" style="background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #656D76; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;"
                        x-text="t('cancel')">
                    Cancel
                </button>
                <button @click="saveMediaDrawerSettings()" style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(9, 105, 218, 0.35);"
                        x-text="isRtl ? 'ذخیره تنظیمات رسانه' : 'Save Media Configuration'">
                    Save Media Configuration
                </button>
            </div>
        </div>
    </div>
</div>
