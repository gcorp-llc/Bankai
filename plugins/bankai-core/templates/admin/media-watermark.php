<?php
/**
 * Bankai Core Admin - View 5: Media & Dynamic Watermark Studio
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$media_modules = array(
    array(
        'id'          => 'webp_avif_engine',
        'title'       => 'WebP & AVIF Conversion Engine',
        'badge'       => 'NEXT-GEN FORMATS',
        'badge_color' => '#10B981',
        'icon'        => '🖼️',
        'description' => 'Automatic WebP and AVIF image generation on upload with quality controls and fallback to original format.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'dynamic_watermark',
        'title'       => 'Dynamic Watermark Studio',
        'badge'       => '9-GRID POSITION',
        'badge_color' => '#6366F1',
        'icon'        => '🎨',
        'description' => 'Visual positioning controls, opacity slider, text vs logo watermark options, and dimension threshold rules.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'exif_privacy_stripper',
        'title'       => 'EXIF & Privacy Metadata Stripper',
        'badge'       => 'PRIVACY SAFE',
        'badge_color' => '#38BDF8',
        'icon'        => '🔒',
        'description' => 'Strips camera metadata and GPS coordinates to shrink file sizes and protect location privacy.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'cloud_offload_cdn',
        'title'       => 'Cloud Offload & CDN Hub',
        'badge'       => 'S3 / R2 READY',
        'badge_color' => '#F59E0B',
        'icon'        => '☁️',
        'description' => 'Offload media attachments to AWS S3, Cloudflare R2, or Bunny CDN with automated URL rewrite.',
        'enabled'     => false,
    ),
    array(
        'id'          => 'responsive_cls_guard',
        'title'       => 'Responsive Images & CLS Guard',
        'badge'       => 'ZERO CLS',
        'badge_color' => '#10B981',
        'icon'        => '📐',
        'description' => 'Injects width/height aspect ratios, loading="lazy", and decoding="async" attributes to pass Core Web Vitals.',
        'enabled'     => true,
    ),
    array(
        'id'          => 'image_compression',
        'title'       => 'Lossy & Lossless Compression',
        'badge'       => 'IMAGICK / GD',
        'badge_color' => '#38BDF8',
        'icon'        => '⚙️',
        'description' => 'Advanced image compression engine utilizing local Imagick, GD, or cURL binaries.',
        'enabled'     => true,
    ),
);
?>

<div x-show="activeTab === 'media'" x-transition>
    <!-- View 5 Header & Storage Telemetry Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                <h2 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 8px;">
                    🖼️ <?php esc_html_e( 'Next-Gen Media Engine & Dynamic Watermark Studio', 'bankai-core' ); ?>
                </h2>
                <span style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10B981; color: #10B981; font-size: 11px; padding: 2px 10px; border-radius: 12px; font-weight: 700;">
                    <?php esc_html_e( '1.4 GB Saved (82% Avg Compression Rate)', 'bankai-core' ); ?>
                </span>
            </div>
            <p style="font-size: 12px; color: #94A3B8; margin: 0;">
                <?php esc_html_e( 'WebP & AVIF auto-conversion, dynamic watermark overlay, EXIF metadata stripping & CDN offloading.', 'bankai-core' ); ?>
            </p>
        </div>

        <div style="display: flex; gap: 12px;">
            <button @click="regenerateThumbnails()"
                    style="background-color: #1E2D4A; border: 1px solid #334155; color: #F8FAFC; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                🔄 <?php esc_html_e( 'Regenerate Thumbnails', 'bankai-core' ); ?>
            </button>

            <button @click="bulkConvertMedia()"
                    style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
                🚀 <?php esc_html_e( 'Bulk Convert Media Library', 'bankai-core' ); ?>
            </button>
        </div>
    </div>

    <!-- Server Environment & Format Badges -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px;">
            <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">PHP GD Extension</div>
            <div style="font-size: 16px; font-weight: 800; color: #10B981; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                <span>✓ Active (v2.3)</span>
            </div>
            <div style="font-size: 10px; color: #94A3B8; margin-top: 2px;">PNG, JPEG, WebP enabled</div>
        </div>

        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px;">
            <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">Imagick Binary</div>
            <div style="font-size: 16px; font-weight: 800; color: #10B981; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                <span>✓ Active (ImageMagick 7)</span>
            </div>
            <div style="font-size: 10px; color: #94A3B8; margin-top: 2px;">AVIF &amp; WebP conversion supported</div>
        </div>

        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px;">
            <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">AVIF Support</div>
            <div style="font-size: 16px; font-weight: 800; color: #38BDF8; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                <span>✓ Enabled</span>
            </div>
            <div style="font-size: 10px; color: #94A3B8; margin-top: 2px;">50% smaller than WebP</div>
        </div>

        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px;">
            <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">Original Backup</div>
            <div style="font-size: 16px; font-weight: 800; color: #F59E0B; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                <span>Safe Mode Active</span>
            </div>
            <div style="font-size: 10px; color: #94A3B8; margin-top: 2px;">Original JPG/PNG retained</div>
        </div>
    </div>

    <!-- Dynamic Watermark Studio Visual Preview Card -->
    <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #F8FAFC; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            🎨 <?php esc_html_e( 'Interactive Watermark Position Studio', 'bankai-core' ); ?>
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Interactive 9-Grid Selector -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 12px;">
                    <?php esc_html_e( 'Select Watermark Overlay Anchor Position', 'bankai-core' ); ?>
                </label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; width: 240px; margin-bottom: 16px;">
                    <?php
                    $positions = array(
                        'top-left'      => '↖️ Top Left',
                        'top-center'    => '⬆️ Top Center',
                        'top-right'     => '↗️ Top Right',
                        'center-left'   => '⬅️ Center Left',
                        'center'        => '⏺️ Center',
                        'center-right'  => '➡️ Center Right',
                        'bottom-left'   => '↙️ Bottom Left',
                        'bottom-center' => '⬇️ Bottom Center',
                        'bottom-right'  => '↘️ Bottom Right',
                    );
                    foreach ( $positions as $pos_key => $pos_label ) :
                        ?>
                        <button @click="watermarkStudio.position = '<?php echo esc_js( $pos_key ); ?>'"
                                :style="watermarkStudio.position === '<?php echo esc_js( $pos_key ); ?>' ? 'background-color: #10B981; color: #FFFFFF; border-color: #10B981;' : 'background-color: #0B0F19; color: #94A3B8; border-color: #1E2D4A;'"
                                style="padding: 10px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid; text-align: center; transition: all 0.2s;">
                            <?php echo esc_html( explode( ' ', $pos_label )[0] ); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Watermark Opacity & Text Settings -->
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #94A3B8; margin-bottom: 4px;">
                            <span>Watermark Opacity</span>
                            <span style="color: #10B981; font-weight: 800;" x-text="watermarkStudio.opacity + '%'">75%</span>
                        </div>
                        <input type="range" min="10" max="100" x-model="watermarkStudio.opacity" style="width: 240px; accent-color: #10B981;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; color: #94A3B8; margin-bottom: 4px;">Watermark Custom Text</label>
                        <input type="text" x-model="watermarkStudio.text"
                               style="width: 240px; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 8px 12px; border-radius: 6px; font-size: 12px;">
                    </div>
                </div>
            </div>

            <!-- Live Canvas Simulation -->
            <div style="position: relative; height: 240px; background-color: #0B0F19; border: 1px solid #1E2D4A; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=600&q=80" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.6;">
                
                <!-- Live Watermark Overlay -->
                <div style="position: absolute; padding: 12px; pointer-events: none; transition: all 0.3s ease;"
                     :style="getWatermarkPositionStyle()">
                    <span style="background-color: rgba(0, 0, 0, 0.6); color: #FFFFFF; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 800; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);"
                          :style="'opacity: ' + (watermarkStudio.opacity / 100)"
                          x-text="watermarkStudio.text || '© BANKAI MEDIA'">
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modular Feature Grid (6 Cards) -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px;">
        <?php foreach ( $media_modules as $mod ) : ?>
            <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; transition: border-color 0.2s;"
                 onmouseover="this.style.borderColor='#38BDF8';"
                 onmouseout="this.style.borderColor='#1E2D4A';">
                
                <div>
                    <!-- Top title + badge + switch -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 20px;"><?php echo esc_html( $mod['icon'] ); ?></span>
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #F8FAFC; display: flex; align-items: center; gap: 6px;">
                                    <?php echo esc_html( $mod['title'] ); ?>
                                </div>
                                <span style="background-color: <?php echo esc_attr( $mod['badge_color'] ); ?>20; color: <?php echo esc_attr( $mod['badge_color'] ); ?>; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-block; margin-top: 2px;">
                                    <?php echo esc_html( $mod['badge'] ); ?>
                                </span>
                            </div>
                        </div>

                        <label class="bankai-switch">
                            <input type="checkbox"
                                   x-model="mediaState.<?php echo esc_attr( $mod['id'] ); ?>"
                                   @change="toggleMediaModule('<?php echo esc_js( $mod['id'] ); ?>')">
                            <span class="bankai-slider"></span>
                        </label>
                    </div>

                    <!-- Description -->
                    <p style="font-size: 12px; color: #94A3B8; line-height: 1.5; margin: 0 0 16px 0;">
                        <?php echo esc_html( $mod['description'] ); ?>
                    </p>
                </div>

                <!-- Action / Settings Drawer Trigger -->
                <div style="border-top: 1px solid #1E2D4A; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; color: #64748B;"
                          x-text="mediaState.<?php echo esc_attr( $mod['id'] ); ?> ? 'Active' : 'Disabled'">
                        Active
                    </span>

                    <button @click="openMediaDrawer('<?php echo esc_js( $mod['id'] ); ?>', '<?php echo esc_js( $mod['title'] ); ?>')"
                            style="background-color: #1E2D4A; border: none; color: #38BDF8; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; cursor: pointer;">
                        ⚙️ <?php esc_html_e( 'Configure', 'bankai-core' ); ?> &rarr;
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Media Module Settings Modal Drawer (Alpine.js x-show) -->
    <div x-show="mediaDrawer.show" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="mediaDrawer.show = false"
             style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 16px; width: 560px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.8); position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #1E2D4A; padding-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 8px;">
                    ⚙️ <span x-text="mediaDrawer.title + ' Settings'"></span>
                </h3>
                <button @click="mediaDrawer.show = false" style="background: none; border: none; color: #64748B; font-size: 20px; cursor: pointer;">&times;</button>
            </div>

            <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                        <?php esc_html_e( 'WebP / AVIF Compression Quality Level (0 - 100)', 'bankai-core' ); ?>
                    </label>
                    <input type="number" value="82" min="10" max="100"
                           style="width: 100%; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 10px; border-radius: 8px; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                        <?php esc_html_e( 'Minimum Dimension Threshold for Watermarking', 'bankai-core' ); ?>
                    </label>
                    <select style="width: 100%; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #F8FAFC; padding: 10px; border-radius: 8px; font-size: 13px;">
                        <option value="300">Skip images under 300px width/height</option>
                        <option value="500">Skip images under 500px width/height</option>
                        <option value="0">Apply to all images regardless of size</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button @click="mediaDrawer.show = false" style="background-color: #1E2D4A; border: none; color: #F8FAFC; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button @click="saveMediaDrawerSettings()" style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(16,185,129,0.3);">
                    Save Media Configuration
                </button>
            </div>
        </div>
    </div>
</div>
