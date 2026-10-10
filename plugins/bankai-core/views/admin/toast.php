<?php
defined('ABSPATH') || exit;
?>
<div id="bankai-toast-container" class="bankai-toast-container" aria-live="polite" aria-atomic="true">
    <div
        data-bankai-show="toast.show"
        data-bankai-cloak
        data-bankai-transition:enter="bankai-toast-transition-enter"
        data-bankai-transition:enter-start="bankai-toast-transition-start"
        data-bankai-transition:enter-end="bankai-toast-transition-end"
        data-bankai-transition:leave="bankai-toast-transition-leave"
        data-bankai-transition:leave-start="bankai-toast-transition-end"
        data-bankai-transition:leave-end="bankai-toast-transition-start"
        class="bankai-toast-card"
        data-bankai-class="{
            'bankai-toast-success': toast.type === 'success' || !toast.type,
            'bankai-toast-error': toast.type === 'error',
            'bankai-toast-warning': toast.type === 'warning' || toast.type === 'warn',
            'bankai-toast-info': toast.type === 'info'
        }"
        role="status"
        @keydown.escape.window="toast.show = false"
    >
        <span class="bankai-toast-accent" aria-hidden="true"></span>

        <!-- Success -->
        <template data-bankai-if="toast.type === 'success' || !toast.type">
            <div class="bankai-toast-icon-badge bankai-toast-icon-success" aria-hidden="true">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
        </template>
        <!-- Error -->
        <template data-bankai-if="toast.type === 'error'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-error" aria-hidden="true">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            </div>
        </template>
        <!-- Warning -->
        <template data-bankai-if="toast.type === 'warning' || toast.type === 'warn'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-warning" aria-hidden="true">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
            </div>
        </template>
        <!-- Info -->
        <template data-bankai-if="toast.type === 'info'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-info" aria-hidden="true">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
            </div>
        </template>

        <div class="bankai-toast-body">
            <span class="bankai-toast-label" data-bankai-text="
                toast.type === 'error' ? (isRtl ? 'خطا' : 'Error') :
                toast.type === 'warning' || toast.type === 'warn' ? (isRtl ? 'هشدار' : 'Warning') :
                toast.type === 'info' ? (isRtl ? 'اطلاع' : 'Info') :
                (isRtl ? 'موفق' : 'Success')
            "></span>
            <span class="bankai-toast-text" data-bankai-text="toast.message"></span>
        </div>

        <button type="button"
                class="bankai-toast-dismiss"
                data-bankai-click="toast.show = false"
                :title="isRtl ? 'بستن' : 'Dismiss'"
                aria-label="<?php esc_attr_e('Dismiss', 'bankai-core'); ?>">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>

        <div class="bankai-toast-progress" aria-hidden="true">
            <div class="bankai-toast-progress-bar"
                 data-bankai-class="{ 'is-running': toast.show }"
                 data-bankai-style="'--toast-ms:' + (toast.duration || 3600) + 'ms'"></div>
        </div>
    </div>
</div>
