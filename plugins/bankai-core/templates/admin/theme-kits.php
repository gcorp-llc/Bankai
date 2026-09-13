<?php
/**
 * Bankai Core Admin - View 2: Theme & Starter Kits View
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$starter_kits = array(
    array(
        'id'          => 'ecommerce_pro',
        'name'        => 'E-Commerce Pro Store',
        'description' => 'Full-featured online store layout with WooCommerce compatibility and instant checkout UI.',
        'version'     => 'v2.1',
        'badges'      => array( 'Elementor + Block Compatible', 'v2.1' ),
        'thumbnail'   => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
        'preview_url' => 'https://gcorp.llc/preview/ecommerce-pro',
    ),
    array(
        'id'          => 'agency_corporate',
        'name'        => 'Corporate Agency Hub',
        'description' => 'Sleek dark slate agency portfolio with modern micro-interactions & service showcase.',
        'version'     => 'v2.0',
        'badges'      => array( 'Elementor + Block Compatible', 'v2.0' ),
        'thumbnail'   => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80',
        'preview_url' => 'https://gcorp.llc/preview/agency-corporate',
    ),
    array(
        'id'          => 'ai_saas_platform',
        'name'        => 'AI SaaS Platform',
        'description' => 'Cyberpunk high-conversion AI platform landing page with pricing tiers and interactive demo sections.',
        'version'     => 'v2.3',
        'badges'      => array( 'Elementor + Block Compatible', 'v2.3' ),
        'thumbnail'   => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=600&q=80',
        'preview_url' => 'https://gcorp.llc/preview/ai-saas',
    ),
    array(
        'id'          => 'minimal_blog',
        'name'        => 'Minimalist Tech Blog',
        'description' => 'Clean, lightning-fast editorial blog setup designed for tech writers and content creators.',
        'version'     => 'v1.8',
        'badges'      => array( 'Block Engine Native', 'v1.8' ),
        'thumbnail'   => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=600&q=80',
        'preview_url' => 'https://gcorp.llc/preview/minimal-blog',
    ),
);
?>

<!-- View 2 Header Panel -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 10px;">
                🎨 <?php esc_html_e( 'Theme Architecture & 1-Click Starter Kits', 'bankai-core' ); ?>
            </h2>
            <p style="font-size: 12px; color: #94A3B8; margin: 4px 0 0 0;">
                <?php esc_html_e( 'Customize global typography, container dimensions, and instantly import pre-built starter templates.', 'bankai-core' ); ?>
            </p>
        </div>

        <button @click="syncLibrary()"
                style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
            🔄 <?php esc_html_e( 'Sync Library', 'bankai-core' ); ?>
        </button>
    </div>

    <!-- Theme Customizer Panel (Top Grid) -->
    <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 24px; margin-bottom: 32px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #F8FAFC; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
            ⚙️ <?php esc_html_e( 'Theme Customizer Panel', 'bankai-core' ); ?>
        </h3>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
            <!-- Typography Selector -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 8px;">
                    <?php esc_html_e( 'Typography Font Family', 'bankai-core' ); ?>
                </label>
                <select x-model="customizer.fontFamily"
                        style="width: 100%; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 10px 14px; border-radius: 8px; font-size: 13px; outline: none;">
                    <option value="Inter">System UI / Inter (Default LTR)</option>
                    <option value="Vazirmatn">Vazirmatn (Persian RTL Standard)</option>
                    <option value="Roboto">Roboto Modern</option>
                    <option value="Fira Code">Fira Code (Developer / Monospace)</option>
                </select>
                <span style="font-size: 11px; color: #64748B; margin-top: 6px; display: block;">
                    <?php esc_html_e( 'Persian / RTL mode automatically optimizes kerning and font fallbacks.', 'bankai-core' ); ?>
                </span>
            </div>

            <!-- Container Max-Width Slider -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="font-size: 12px; font-weight: 700; color: #94A3B8;">
                        <?php esc_html_e( 'Container Max-Width', 'bankai-core' ); ?>
                    </label>
                    <span style="font-size: 12px; font-weight: 800; color: #10B981;" x-text="customizer.containerWidth + 'px'">1400px</span>
                </div>
                <input type="range" min="1200" max="1600" step="10" x-model="customizer.containerWidth"
                       style="width: 100%; accent-color: #10B981; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; font-size: 10px; color: #64748B; margin-top: 6px;">
                    <span>1200px (Compact)</span>
                    <span>1400px (Standard)</span>
                    <span>1600px (Ultra Wide)</span>
                </div>
            </div>

            <!-- Header & Footer Layout Style Toggles -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 8px;">
                    <?php esc_html_e( 'Layout Components', 'bankai-core' ); ?>
                </label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; background-color: #0B0F19; padding: 8px 12px; border-radius: 6px; border: 1px solid #1E2D4A;">
                        <span style="font-size: 12px; color: #E2E8F0;"><?php esc_html_e( 'Sticky Header Glassmorphism', 'bankai-core' ); ?></span>
                        <label class="bankai-switch">
                            <input type="checkbox" checked>
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; background-color: #0B0F19; padding: 8px 12px; border-radius: 6px; border: 1px solid #1E2D4A;">
                        <span style="font-size: 12px; color: #E2E8F0;"><?php esc_html_e( 'Expanded Multi-Column Footer', 'bankai-core' ); ?></span>
                        <label class="bankai-switch">
                            <input type="checkbox" checked>
                            <span class="bankai-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Starter Kit Importer Grid (3-Column Layout) -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #F8FAFC; margin: 0 0 16px 0;">
            🚀 <?php esc_html_e( 'Available Starter Kits & Site Architecture', 'bankai-core' ); ?>
        </h3>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
            <?php foreach ( $starter_kits as $kit ) : ?>
                <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, border-color 0.2s;"
                     onmouseover="this.style.borderColor='#38BDF8';"
                     onmouseout="this.style.borderColor='#1E2D4A';">
                    <!-- Thumbnail with hover effect -->
                    <div style="position: relative; height: 180px; overflow: hidden; background-color: #0B0F19;">
                        <img src="<?php echo esc_url( $kit['thumbnail'] ); ?>" alt="<?php echo esc_attr( $kit['name'] ); ?>"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                             onmouseover="this.style.transform='scale(1.05)';"
                             onmouseout="this.style.transform='scale(1)';" />
                        <div style="position: absolute; top: 12px; left: 12px; display: flex; gap: 6px; flex-wrap: wrap;">
                            <?php foreach ( $kit['badges'] as $badge ) : ?>
                                <span style="background-color: rgba(15, 23, 42, 0.85); color: #38BDF8; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(56, 189, 248, 0.4); backdrop-filter: blur(4px);">
                                    <?php echo esc_html( $badge ); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div style="padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 16px; font-weight: 800; color: #F8FAFC; margin: 0 0 8px 0;">
                                <?php echo esc_html( $kit['name'] ); ?>
                            </h4>
                            <p style="font-size: 12px; color: #94A3B8; line-height: 1.5; margin: 0 0 16px 0;">
                                <?php echo esc_html( $kit['description'] ); ?>
                            </p>
                        </div>

                        <!-- Card Action Buttons -->
                        <div style="display: flex; gap: 10px; margin-top: 12px;">
                            <a href="<?php echo esc_url( $kit['preview_url'] ); ?>" target="_blank"
                               style="flex: 1; text-align: center; text-decoration: none; background-color: #1E2D4A; color: #F8FAFC; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid #334155; transition: all 0.2s;">
                                👁️ <?php esc_html_e( 'Live Preview', 'bankai-core' ); ?>
                            </a>
                            
                            <button @click="openImportModal(<?php echo esc_attr( json_encode( $kit ) ); ?>)"
                                    style="flex: 1.2; background-color: #10B981; border: none; color: #FFFFFF; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
                                📥 <?php esc_html_e( 'Import Template', 'bankai-core' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Interactive Import Modal Drawer (Alpine.js x-show) -->
    <div x-show="importModal.show" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="closeImportModal()"
             style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 16px; width: 520px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.8); position: relative;">
            
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #1E2D4A; padding-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0;">
                    📥 <?php esc_html_e( 'Import Starter Kit Architecture', 'bankai-core' ); ?>
                </h3>
                <button @click="closeImportModal()" style="background: none; border: none; color: #64748B; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <!-- Modal Content -->
            <template x-if="importModal.kit">
                <div>
                    <div style="background-color: #0B0F19; border: 1px solid #1E2D4A; border-radius: 10px; padding: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 14px;">
                        <img :src="importModal.kit.thumbnail" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                        <div>
                            <div style="font-weight: 800; color: #F8FAFC; font-size: 15px;" x-text="importModal.kit.name"></div>
                            <div style="font-size: 11px; color: #10B981; font-weight: 600;" x-text="importModal.kit.version"></div>
                        </div>
                    </div>

                    <!-- Step Checklist -->
                    <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 12px;">
                        <template x-for="step in importModal.steps" :key="step.id">
                            <div style="display: flex; justify-content: space-between; align-items: center; background-color: #0B0F19; border: 1px solid #1E2D4A; padding: 10px 14px; border-radius: 8px;">
                                <span style="font-size: 13px; color: #E2E8F0;" x-text="step.label"></span>
                                <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;"
                                      :style="step.status === 'completed' ? 'background-color: rgba(16,185,129,0.2); color: #10B981;' : (step.status === 'in_progress' ? 'background-color: rgba(56,189,248,0.2); color: #38BDF8;' : 'background-color: #1E2D4A; color: #64748B;')"
                                      x-text="step.status === 'completed' ? '✓ Ready' : (step.status === 'in_progress' ? '⌛ Processing...' : 'Pending')">
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Progress Bar -->
                    <div style="margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                            <span><?php esc_html_e( 'Installation Progress', 'bankai-core' ); ?></span>
                            <span style="color: #10B981;" x-text="importModal.progress + '%'">0%</span>
                        </div>
                        <div style="height: 10px; background-color: #0B0F19; border: 1px solid #1E2D4A; border-radius: 5px; overflow: hidden;">
                            <div style="height: 100%; background: linear-gradient(90deg, #10B981, #38BDF8); transition: width 0.3s ease;"
                                 :style="'width: ' + importModal.progress + '%'"></div>
                        </div>
                    </div>

                    <!-- Status Completed Notice -->
                    <template x-if="importModal.status === 'completed'">
                        <div style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10B981; color: #10B981; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 700; text-align: center; margin-bottom: 20px;">
                            🎉 <?php esc_html_e( 'Starter Kit imported successfully! Your layout is now active.', 'bankai-core' ); ?>
                        </div>
                    </template>

                    <!-- Modal Actions -->
                    <div style="display: flex; justify-content: flex-end; gap: 12px;">
                        <button @click="closeImportModal()" style="background-color: #1E2D4A; border: none; color: #F8FAFC; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                            <span x-text="importModal.status === 'completed' ? 'Close' : 'Cancel'">Cancel</span>
                        </button>

                        <button x-show="importModal.status === 'idle'" @click="startImport()"
                                style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                            🚀 <?php esc_html_e( 'Start 1-Click Import', 'bankai-core' ); ?>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
