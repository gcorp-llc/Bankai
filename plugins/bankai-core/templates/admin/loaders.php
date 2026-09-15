<?php
/**
 * Loaders Template for Bankai Core (Page Progress & Save Notification)
 *
 * @package Bankai_Core
 */

defined('ABSPATH') || exit;
?>
<!-- Global Page Switch Progress Bar (GitHub Primer Style) -->
<div id="bankai-page-loader"
     class="bankai-page-progress-track"
     x-show="pageLoading"
     x-cloak>
    <div class="bankai-page-progress-bar"
         :style="`width: ${pageProgress}%;`"></div>
</div>

<!-- Floating Settings & Actions Saving Loader (GitHub Primer Edition) -->
<div x-show="savingLoader.show"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
     x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
     x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
     class="bankai-save-loader-overlay"
     id="bankai-save-loader">
    <div class="bankai-save-loader-card"
         :class="savingLoader.state === 'saved' ? 'bankai-save-loader-success' : ''">
        <!-- Spinner Icon when Saving -->
        <template x-if="savingLoader.state === 'saving'">
            <div class="bankai-save-spinner-wrap">
                <svg class="bankai-spinner" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="9" stroke="rgba(9, 105, 218, 0.2)" stroke-width="2.5" />
                    <path d="M12 3a9 9 0 0 1 9 9" stroke="#0969DA" stroke-width="2.5" stroke-linecap="round" />
                </svg>
            </div>
        </template>
        <!-- Success Icon when Saved -->
        <template x-if="savingLoader.state === 'saved'">
            <div class="bankai-save-success-wrap">
                <svg class="solar-icon" style="color: #1A7F37; width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
        </template>

        <div style="display: flex; flex-direction: column; gap: 2px;">
            <div style="font-size: 13px; font-weight: 700; color: #1F2328; display: flex; align-items: center; gap: 6px;">
                <span x-text="savingLoader.title">Saving Changes...</span>
                <span x-show="savingLoader.state === 'saving'" class="bankai-pulse-dot"></span>
            </div>
            <div style="font-size: 11px; color: #656D76;" x-text="savingLoader.message">
                Applying updates to server &amp; synchronizing cache...
            </div>
        </div>
    </div>
</div>
