<?php
defined('ABSPATH') || exit;
?>
<div id="bankai-toast-container" class="bankai-toast-container" aria-live="polite" aria-atomic="true">
    <div
        x-show="toast.show"
        x-cloak
        x-transition:enter="bankai-toast-transition-enter"
        x-transition:enter-start="bankai-toast-transition-start"
        x-transition:enter-end="bankai-toast-transition-end"
        x-transition:leave="bankai-toast-transition-leave"
        x-transition:leave-start="bankai-toast-transition-end"
        x-transition:leave-end="bankai-toast-transition-start"
        class="bankai-toast-card"
        :class="{
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
        <template x-if="toast.type === 'success' || !toast.type">
            <div class="bankai-toast-icon-badge bankai-toast-icon-success" aria-hidden="true">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
        </template>
        <!-- Error -->
        <template x-if="toast.type === 'error'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-error" aria-hidden="true">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            </div>
        </template>
        <!-- Warning -->
        <template x-if="toast.type === 'warning' || toast.type === 'warn'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-warning" aria-hidden="true">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
            </div>
        </template>
        <!-- Info -->
        <template x-if="toast.type === 'info'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-info" aria-hidden="true">
                <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
            </div>
        </template>

        <div class="bankai-toast-body">
            <span class="bankai-toast-label" x-text="
                toast.type === 'error' ? (isRtl ? 'خطا' : 'Error') :
                toast.type === 'warning' || toast.type === 'warn' ? (isRtl ? 'هشدار' : 'Warning') :
                toast.type === 'info' ? (isRtl ? 'اطلاع' : 'Info') :
                (isRtl ? 'موفق' : 'Success')
            "></span>
            <span class="bankai-toast-text" x-text="toast.message"></span>
        </div>

        <button type="button"
                class="bankai-toast-dismiss"
                @click="toast.show = false"
                :title="isRtl ? 'بستن' : 'Dismiss'"
                aria-label="<?php esc_attr_e('Dismiss', 'bankai-core'); ?>">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>

        <div class="bankai-toast-progress" aria-hidden="true">
            <div class="bankai-toast-progress-bar"
                 :class="{ 'is-running': toast.show }"
                 :style="'--toast-ms:' + (toast.duration || 3600) + 'ms'"></div>
        </div>
    </div>
</div>
