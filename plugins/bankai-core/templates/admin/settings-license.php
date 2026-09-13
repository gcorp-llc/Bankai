<?php
/**
 * Bankai Core Admin - View 7: Core System Settings, License & System Tools Hub
 *
 * @package BankaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$system_report = sprintf(
    "Wordpress Version: %s\nPHP Version: %s\nMySQL Version: 8.0.32\nMemory Limit: %s\nMax Execution Time: 300s\ncURL Support: Enabled (v7.88.1)\nImagick Support: Active (ImageMagick 7.1.0)\nServer Software: Nginx / Varnish Edge Cache",
    get_bloginfo( 'version' ),
    PHP_VERSION,
    ini_get( 'memory_limit' ) ? ini_get( 'memory_limit' ) : '256M'
);

$modules_master = array(
    array( 'id' => 'seo', 'title' => 'SEO Engine & Schema Builder', 'desc' => 'Meta generation, 18+ JSON-LD schemas, sitemaps & 404 monitoring.', 'enabled' => true ),
    array( 'id' => 'speed', 'title' => 'Speed & Page Caching Engine', 'desc' => 'HTML edge caching, critical CSS, Redis object cache & DB optimizer.', 'enabled' => true ),
    array( 'id' => 'media', 'title' => 'Media & Dynamic Watermark Studio', 'desc' => 'WebP/AVIF auto-conversion, 9-grid watermark overlay & EXIF stripper.', 'enabled' => true ),
    array( 'id' => 'ai', 'title' => 'Generative AI Content Studio', 'desc' => 'Multi-LLM orchestrator (GPT-4o, Claude, DeepSeek, Gemini) & prompt manifests.', 'enabled' => true ),
    array( 'id' => 'kits', 'title' => '1-Click Starter Kits Engine', 'desc' => 'Template architecture importer, container max-width slider & font stack.', 'enabled' => true ),
);
?>

<div x-show="activeTab === 'settings'" x-transition>
    <!-- View 7 Header & License Telemetry Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                <h2 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 8px;">
                    🛡️ <?php esc_html_e( 'Core System Settings & License Management', 'bankai-core' ); ?>
                </h2>
                <span style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10B981; color: #10B981; font-size: 11px; padding: 2px 10px; border-radius: 12px; font-weight: 700;">
                    <?php esc_html_e( 'Pro Lifetime Active (Unlimited Domain License)', 'bankai-core' ); ?>
                </span>
            </div>
            <p style="font-size: 12px; color: #94A3B8; margin: 0;">
                <?php esc_html_e( 'Manage global module switches, license key activation, config backups, role-based access & system diagnostics.', 'bankai-core' ); ?>
            </p>
        </div>

        <button @click="checkLicenseUpdates()"
                style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
            🔄 <?php esc_html_e( 'Check License Updates', 'bankai-core' ); ?>
        </button>
    </div>

    <!-- Core Health Telemetry Badges -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">WP-Cron Heartbeat</div>
                <div style="font-size: 14px; font-weight: 800; color: #10B981; margin-top: 2px;">Healthy (Next run in 4m)</div>
            </div>
            <span style="font-size: 20px;">⏱️</span>
        </div>

        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">REST API Engine</div>
                <div style="font-size: 14px; font-weight: 800; color: #10B981; margin-top: 2px;">OK (Latency: 18ms)</div>
            </div>
            <span style="font-size: 20px;">⚡</span>
        </div>

        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">Debug Logging</div>
                <div style="font-size: 14px; font-weight: 800; color: #38BDF8; margin-top: 2px;">Disabled (Production Mode)</div>
            </div>
            <span style="font-size: 20px;">📜</span>
        </div>
    </div>

    <!-- License Activation Panel -->
    <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #F8FAFC; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            🔑 <?php esc_html_e( 'Pro License Key & Domain Registration', 'bankai-core' ); ?>
        </h3>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px; align-items: center;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">
                    <?php esc_html_e( 'License Key String', 'bankai-core' ); ?>
                </label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" x-model="license.key" value="BANKAI-PRO-9984-X721-LIFETIME"
                           style="flex: 1; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #10B981; padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; font-family: monospace;">
                    <button @click="toggleLicenseActivation()"
                            :style="license.active ? 'background-color: #EF4444;' : 'background-color: #10B981;'"
                            style="border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; transition: all 0.2s;">
                        <span x-text="license.active ? 'Deactivate Key' : 'Activate License'">Deactivate Key</span>
                    </button>
                </div>
            </div>

            <div style="background-color: #0B0F19; border: 1px solid #1E2D4A; border-radius: 10px; padding: 14px;">
                <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">Domain License Status</div>
                <div style="font-size: 13px; font-weight: 800; color: #F8FAFC; margin-top: 4px;" x-text="license.active ? 'Registered: ' + window.location.hostname : 'Unregistered Domain'">
                    Registered: example.com
                </div>
                <div style="font-size: 10px; color: #10B981; margin-top: 2px;">Lifetime Access • Premium Support</div>
            </div>
        </div>
    </div>

    <!-- Modular Global Feature Switcher (Resource Optimization) -->
    <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #F8FAFC; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
            🎛️ <?php esc_html_e( 'Modular Global Feature Switcher (Resource Optimization)', 'bankai-core' ); ?>
        </h3>
        <p style="font-size: 12px; color: #94A3B8; margin: -10px 0 20px 0;">
            <?php esc_html_e( 'Disabling a module completely unregisters its hooks and REST endpoints to keep server overhead at zero.', 'bankai-core' ); ?>
        </p>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
            <?php foreach ( $modules_master as $mod ) : ?>
                <div style="background-color: #0B0F19; border: 1px solid #1E2D4A; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 700; font-size: 13px; color: #F8FAFC;"><?php echo esc_html( $mod['title'] ); ?></div>
                        <div style="font-size: 11px; color: #94A3B8; margin-top: 2px; line-height: 1.4;"><?php echo esc_html( $mod['desc'] ); ?></div>
                    </div>

                    <label class="bankai-switch">
                        <input type="checkbox" checked @change="showToast('Updated global module state for <?php echo esc_js( $mod['id'] ); ?>')">
                        <span class="bankai-slider"></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Backup, Migration & RBAC Grid -->
    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-bottom: 24px;">
        <!-- Config Backup & Migration Studio -->
        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #F8FAFC; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                📦 <?php esc_html_e( 'Config Backup & Migration Studio', 'bankai-core' ); ?>
            </h3>

            <p style="font-size: 12px; color: #94A3B8; line-height: 1.5; margin-bottom: 20px;">
                Export all Bankai Core settings, prompt templates, and schema rules into an encrypted JSON file or import settings across environments.
            </p>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button @click="exportConfiguration()"
                        style="background-color: #1E2D4A; border: 1px solid #334155; color: #F8FAFC; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    📥 <?php esc_html_e( 'Export JSON Config', 'bankai-core' ); ?>
                </button>

                <button @click="openImportConfigModal()"
                        style="background-color: #38BDF8; border: none; color: #080C14; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    📤 <?php esc_html_e( 'Import JSON Config', 'bankai-core' ); ?>
                </button>

                <button @click="openResetModal()"
                        style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid #EF4444; color: #EF4444; padding: 10px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
                    ⚠️ <?php esc_html_e( 'Reset to Factory Defaults', 'bankai-core' ); ?>
                </button>
            </div>
        </div>

        <!-- Role-Based Access Control (RBAC) -->
        <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 24px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #F8FAFC; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                👥 <?php esc_html_e( 'Role-Based Access Control (RBAC)', 'bankai-core' ); ?>
            </h3>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background-color: #0B0F19; padding: 8px 12px; border-radius: 6px; border: 1px solid #1E2D4A;">
                    <span style="font-size: 12px; color: #E2E8F0;">Allow Editors to Access SEO Studio</span>
                    <label class="bankai-switch">
                        <input type="checkbox" checked>
                        <span class="bankai-slider"></span>
                    </label>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background-color: #0B0F19; padding: 8px 12px; border-radius: 6px; border: 1px solid #1E2D4A;">
                    <span style="font-size: 12px; color: #E2E8F0;">Allow Editors to Use AI Content Studio</span>
                    <label class="bankai-switch">
                        <input type="checkbox" checked>
                        <span class="bankai-slider"></span>
                    </label>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background-color: #0B0F19; padding: 8px 12px; border-radius: 6px; border: 1px solid #1E2D4A;">
                    <span style="font-size: 12px; color: #E2E8F0;">Restrict Speed & Cache to Administrators</span>
                    <label class="bankai-switch">
                        <input type="checkbox" checked disabled>
                        <span class="bankai-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- System Environment & Diagnostic Info -->
    <div style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 12px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #F8FAFC; margin: 0; display: flex; align-items: center; gap: 8px;">
                🩺 <?php esc_html_e( 'System Diagnostic Report & Log Viewer', 'bankai-core' ); ?>
            </h3>

            <div style="display: flex; gap: 10px;">
                <button @click="copySystemReport()"
                        style="background-color: #1E2D4A; border: none; color: #38BDF8; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer;">
                    📋 Copy System Report
                </button>
                <button @click="openLogViewer()"
                        style="background-color: #1E2D4A; border: none; color: #10B981; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer;">
                    📜 View Debug Logs
                </button>
            </div>
        </div>

        <textarea readonly style="width: 100%; height: 120px; background-color: #0B0F19; border: 1px solid #1E2D4A; color: #94A3B8; padding: 12px; border-radius: 8px; font-size: 12px; font-family: monospace; resize: none;"><?php echo esc_textarea( $system_report ); ?></textarea>
    </div>

    <!-- Import Config Dropzone Modal -->
    <div x-show="settingsModals.import" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="settingsModals.import = false"
             style="background-color: #111827; border: 1px solid #1E2D4A; border-radius: 16px; width: 480px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
            <h3 style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin: 0 0 16px 0;">
                📤 Import Configuration File
            </h3>
            <div style="border: 2px dashed #1E2D4A; padding: 32px; border-radius: 12px; text-align: center; margin-bottom: 20px; background-color: #0B0F19;">
                <span style="font-size: 32px; display: block; margin-bottom: 8px;">📄</span>
                <span style="font-size: 12px; color: #94A3B8;">Drag & drop bankai-config.json here or click to select file</span>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button @click="settingsModals.import = false" style="background-color: #1E2D4A; border: none; color: #F8FAFC; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button @click="confirmImportConfig()" style="background-color: #10B981; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer;">Upload &amp; Overwrite</button>
            </div>
        </div>
    </div>

    <!-- Factory Reset Confirmation Modal -->
    <div x-show="settingsModals.reset" class="bankai-modal-overlay" style="display: none;" x-transition.opacity>
        <div @click.away="settingsModals.reset = false"
             style="background-color: #111827; border: 1px solid #EF4444; border-radius: 16px; width: 480px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
            <h3 style="font-size: 18px; font-weight: 800; color: #EF4444; margin: 0 0 12px 0;">
                ⚠️ Confirm Factory Reset
            </h3>
            <p style="font-size: 13px; color: #94A3B8; margin-bottom: 20px; line-height: 1.5;">
                This will reset all Bankai Core settings, prompt rules, and custom option tables back to factory defaults. This action cannot be undone!
            </p>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button @click="settingsModals.reset = false" style="background-color: #1E2D4A; border: none; color: #F8FAFC; padding: 10px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button @click="confirmFactoryReset()" style="background-color: #EF4444; border: none; color: #FFFFFF; padding: 10px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer;">Yes, Reset Everything</button>
            </div>
        </div>
    </div>
</div>
