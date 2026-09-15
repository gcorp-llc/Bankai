<!-- Tab 2: Theme Kits & Customizer - GitHub Light Edition -->
<div id="tab-theme-kits" x-show="activeTab === 'theme-kits'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <!-- View Header & Action Bar -->
    <div class="bankai-card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <!-- Solar Broken Palette -->
                <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22C6.477 22 2 17.523 2 12c0-4.478 2.946-8.267 7-9.535M16.5 3.12C19.832 4.675 22 8.09 22 12c0 3.5-1.5 5.5-4 5.5h-1.5c-1.105 0-2 .895-2 2 0 1.38 1.12 2.5 2.5 2.5" />
                    <circle cx="8" cy="10" r="1.5" fill="currentColor" stroke="none" />
                    <circle cx="12" cy="7" r="1.5" fill="currentColor" stroke="none" />
                    <circle cx="16" cy="10" r="1.5" fill="currentColor" stroke="none" />
                </svg>
                <span x-text="t('themeKitsTitle')">Bankai Starter Kits &amp; Frontend Customizer</span>
            </h2>
            <p style="font-size: 12px; color: #8C959F; margin: 0;" x-text="t('themeKitsDesc')">
                1-Click turnkey site architectures, Tailwind CSS variable compiler, and high-performance design presets.
            </p>
        </div>

        <button @click="syncLibrary()"
                style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(9, 105, 218, 0.3); transition: all 0.2s;">
            <!-- Solar Broken Restart -->
            <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19.95 11a8 8 0 1 0-.5 4m.5-4h-5m5 0V6" />
            </svg>
            <span x-text="t('syncLibrary')">Sync Library</span>
        </button>
    </div>

    <!-- Theme Customizer Panel -->
    <div class="bankai-card" style="padding: 24px; margin-bottom: 32px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #1F2328; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
            <!-- Solar Broken Sliders / Tuning -->
            <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6" />
            </svg>
            <span x-text="t('customizerTitle')">Theme Customizer Panel</span>
        </h3>

        <div class="bankai-grid-3">
            <!-- Typography Selector -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 8px;"
                       x-text="t('fontFamily')">
                    Typography Font Family
                </label>
                <select x-model="customizer.fontFamily"
                        style="width: 100%; background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #1F2328; padding: 10px 14px; border-radius: 8px; font-size: 13px; outline: none;">
                    <option value="Vazirmatn">Vazirmatn (وزیرمتن - استاندارد فارسی)</option>
                    <option value="Inter">System UI / Inter (Default LTR)</option>
                    <option value="Roboto">Roboto Modern</option>
                    <option value="Fira Code">Fira Code (Developer / Monospace)</option>
                </select>
                <span style="font-size: 11px; color: #8C959F; margin-top: 6px; display: block;"
                      x-text="isRtl ? 'حالت فارسی به طور خودکار فواصل حروف و رندر روان فونت را بهینه‌سازی می‌کند.' : 'Persian / RTL mode automatically optimizes kerning and font fallbacks.'">
                    Persian / RTL mode automatically optimizes kerning and font fallbacks.
                </span>
            </div>

            <!-- Container Max-Width Slider -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="font-size: 12px; font-weight: 700; color: #656D76;"
                           x-text="t('containerWidth')">
                        Container Max-Width
                    </label>
                    <span style="font-size: 12px; font-weight: 800; color: #0969DA; font-family: monospace;" x-text="customizer.containerWidth + 'px'">1400px</span>
                </div>
                <input type="range" min="1200" max="1600" step="10" x-model="customizer.containerWidth"
                       style="width: 100%; accent-color: #0969DA; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; font-size: 10px; color: #8C959F; margin-top: 6px;">
                    <span x-text="isRtl ? '۱۲۰۰ پیکسل (فشرده)' : '1200px (Compact)'">1200px (Compact)</span>
                    <span x-text="isRtl ? '۱۴۰۰ پیکسل (استاندارد)' : '1400px (Standard)'">1400px (Standard)</span>
                    <span x-text="isRtl ? '۱۶۰۰ پیکسل (عریض)' : '1600px (Ultra Wide)'">1600px (Ultra Wide)</span>
                </div>
            </div>

            <!-- Header & Footer Layout Style Toggles -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 8px;"
                       x-text="t('layoutComponents')">
                    Layout Components
                </label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; background-color: #F6F8FA; padding: 10px 14px; border-radius: 8px; border: 1px solid #D0D7DE;">
                        <span style="font-size: 12px; color: #1F2328; font-weight: 600;"
                              x-text="isRtl ? 'سربرگ چسبان هوشمند (Sticky Header)' : 'Sticky Header Navigation'">Sticky Header Navigation</span>
                        <label class="bankai-switch">
                            <input type="checkbox" checked @change="showToast(isRtl ? 'تنظیم سربرگ چسبان بروزرسانی شد' : 'Sticky Header state updated')">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; background-color: #F6F8FA; padding: 10px 14px; border-radius: 8px; border: 1px solid #D0D7DE;">
                        <span style="font-size: 12px; color: #1F2328; font-weight: 600;"
                              x-text="isRtl ? 'پاورقی پیشرفته چندستونه (Footer)' : 'Expanded Multi-Column Footer'">Expanded Multi-Column Footer</span>
                        <label class="bankai-switch">
                            <input type="checkbox" checked @change="showToast(isRtl ? 'تنظیم پاورقی چندستونه بروزرسانی شد' : 'Expanded Footer state updated')">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Starter Kit Importer Grid -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #1F2328; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            <!-- Solar Broken Rocket -->
            <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.5 9.5L18 6M15.5 15.5L12 19l-3.5-1.5L7 16l-2.5-2.5L3 10l3.5-3.5L10 3l6 3.5 4.5 1.5c.5.167.9.6.9 1.1a12.5 12.5 0 0 1-5.9 7.4Z" />
                <path d="M9 15L4 20M2 22l3-1-2-2-1 3Z" />
            </svg>
            <span x-text="isRtl ? 'قالب‌های آماده و معماری‌های قابل نصب' : 'Available Starter Kits & Site Architecture'">Available Starter Kits &amp; Site Architecture</span>
        </h3>

        <div class="bankai-grid-3">
            <?php if (!empty($state['starterKits']) && is_array($state['starterKits'])): ?>
                <?php foreach ($state['starterKits'] as $kit): 
                    $kit_name = esc_attr($kit['name']);
                    $kit_desc = esc_html($kit['description']);
                    $kit_thumb = esc_url($kit['thumbnail']);
                    $kit_json = esc_attr(wp_json_encode($kit));
                ?>
                    <div class="bankai-card bankai-card-interactive" style="overflow: hidden; display: flex; flex-direction: column;">
                        <!-- Thumbnail with hover effect -->
                        <div style="position: relative; height: 180px; overflow: hidden; background-color: #D0D7DE;">
                            <img src="<?php echo $kit_thumb; ?>" alt="<?php echo $kit_name; ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;" />
                            <div style="position: absolute; top: 12px; left: 12px; display: flex; gap: 6px; flex-wrap: wrap;">
                                <?php if (!empty($kit['badges']) && is_array($kit['badges'])): ?>
                                    <?php foreach ($kit['badges'] as $badge): ?>
                                        <span style="background-color: rgba(255, 255, 255, 0.9); color: #0969DA; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(9, 105, 218, 0.3); box-shadow: 0 1px 3px rgba(46, 52, 64, 0.1);">
                                            <?php echo esc_html($badge); ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div style="padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 style="font-size: 16px; font-weight: 800; color: #1F2328; margin: 0 0 8px 0;">
                                    <?php echo esc_html($kit['name']); ?>
                                </h4>
                                <p style="font-size: 12px; color: #656D76; line-height: 1.5; margin: 0 0 16px 0;">
                                    <?php echo $kit_desc; ?>
                                </p>
                            </div>

                            <!-- Card Action Buttons -->
                            <div style="display: flex; gap: 10px; margin-top: 12px;">
                                <button @click="showToast(isRtl ? 'پیش‌نمایش آنلاین قالب فعال شد' : 'Live preview active for <?php echo esc_js($kit['name']); ?>')"
                                        style="flex: 1; text-align: center; background-color: #FFFFFF; color: #1F2328; padding: 9px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #D0D7DE; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 1px 2px rgba(46, 52, 64, 0.04);">
                                    <!-- Solar Broken Eye -->
                                    <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <span x-text="t('preview')">Preview</span>
                                </button>
                                
                                <button @click='openImportModal(<?php echo $kit_json; ?>)'
                                        style="flex: 1.2; background-color: #0969DA; border: none; color: #FFFFFF; padding: 9px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(9, 105, 218, 0.3); transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    <!-- Solar Broken Download / Bolt -->
                                    <svg class="solar-icon solar-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    <span x-text="t('importKit')">Import Kit</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Interactive Import Modal Drawer -->
    <div x-show="importModal.show" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="closeImportModal()"
             class="bankai-card"
             style="width: 520px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(46, 52, 64, 0.25); position: relative;">
            
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #D0D7DE; padding-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #1F2328; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <!-- Solar Broken Download -->
                    <svg class="solar-icon" style="color: #0969DA;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <span x-text="t('importingKit')">Import Starter Kit Architecture</span>
                </h3>
                <button @click="closeImportModal()" style="background: none; border: none; color: #8C959F; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <!-- Modal Content -->
            <template x-if="importModal.kit">
                <div>
                    <div style="background-color: #F6F8FA; border: 1px solid #D0D7DE; border-radius: 10px; padding: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 14px;">
                        <img :src="importModal.kit.thumbnail" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                        <div>
                            <div style="font-weight: 800; color: #1F2328; font-size: 15px;" x-text="importModal.kit.name"></div>
                            <div style="font-size: 11px; color: #1A7F37; font-weight: 700;" x-text="importModal.kit.version"></div>
                        </div>
                    </div>

                    <!-- Step Checklist -->
                    <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 12px;">
                        <template x-for="step in importModal.steps" :key="step.id">
                            <div style="display: flex; justify-content: space-between; align-items: center; background-color: #F6F8FA; border: 1px solid #D0D7DE; padding: 12px 14px; border-radius: 8px;">
                                <span style="font-size: 13px; color: #1F2328; font-weight: 600;"
                                      x-text="isRtl ? (step.id === 'demo_content' ? t('stepContent') : (step.id === 'custom_fields' ? t('stepAcf') : t('stepPlugins'))) : step.label"></span>
                                <span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px;"
                                      :style="step.status === 'completed' ? 'background-color: rgba(31, 136, 61,0.25); color: #1A7F37;' : (step.status === 'in_progress' ? 'background-color: rgba(9, 105, 218,0.2); color: #0969DA;' : 'background-color: #D0D7DE; color: #8C959F;')"
                                      x-text="step.status === 'completed' ? (isRtl ? '✓ آماده' : '✓ Ready') : (step.status === 'in_progress' ? (isRtl ? '⌛ در حال نصب...' : '⌛ Processing...') : (isRtl ? 'در انتظار' : 'Pending'))">
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Progress Bar -->
                    <div style="margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: #656D76; margin-bottom: 6px;">
                            <span x-text="isRtl ? 'پیشرفت نصب و درون‌ریزی' : 'Installation Progress'">Installation Progress</span>
                            <span style="color: #0969DA; font-family: monospace;" x-text="importModal.progress + '%'">0%</span>
                        </div>
                        <div style="height: 10px; background-color: #D0D7DE; border-radius: 5px; overflow: hidden;">
                            <div style="height: 100%; background: linear-gradient(90deg, #218BFF, #0969DA); transition: width 0.3s ease;"
                                 :style="'width: ' + importModal.progress + '%'"></div>
                        </div>
                    </div>

                    <!-- Status Completed Notice -->
                    <template x-if="importModal.status === 'completed'">
                        <div style="background-color: rgba(31, 136, 61, 0.2); border: 1px solid #1A7F37; color: #1A7F37; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 700; text-align: center; margin-bottom: 20px;"
                             x-text="isRtl ? 'قالب با موفقیت درون‌ریزی شد! چیدمان سایت اکنون فعال است.' : 'Starter Kit imported successfully! Your layout is now active.'">
                            Starter Kit imported successfully! Your layout is now active.
                        </div>
                    </template>

                    <!-- Modal Actions -->
                    <div style="display: flex; justify-content: flex-end; gap: 12px;">
                        <button @click="closeImportModal()" style="background-color: #FFFFFF; border: 1px solid #D0D7DE; color: #656D76; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                            <span x-text="importModal.status === 'completed' ? t('close') : t('cancel')">Cancel</span>
                        </button>

                        <button x-show="importModal.status === 'idle'" @click="startImport()"
                                style="background-color: #0969DA; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(9, 105, 218, 0.35);"
                                x-text="isRtl ? 'شروع نصب ۱-کلیکه' : 'Start 1-Click Import'">
                            Start 1-Click Import
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
