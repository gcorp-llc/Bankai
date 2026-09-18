<?php
defined('ABSPATH') || exit;
?>
<div id="bankai-toast-container" class="bankai-toast-container" aria-live="polite" aria-atomic="true">
    <div x-show="toast.show"
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
             'bankai-toast-warning': toast.type === 'warning',
             'bankai-toast-info': toast.type === 'info'
         }"
         role="alert">

        <!-- آیکن موفقیت -->
        <template x-if="toast.type === 'success' || !toast.type">
            <div class="bankai-toast-icon-badge bankai-toast-icon-success">
                <svg class="solar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px; height:16px; flex-shrink:0;" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </template>

        <!-- آیکن خطا -->
        <template x-if="toast.type === 'error'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-error">
                <svg class="solar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px; height:16px; flex-shrink:0;" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </div>
        </template>

        <!-- آیکن هشدار -->
        <template x-if="toast.type === 'warning'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-warning">
                <svg class="solar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px; height:16px; flex-shrink:0;" aria-hidden="true">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
        </template>

        <!-- آیکن اطلاعات -->
        <template x-if="toast.type === 'info'">
            <div class="bankai-toast-icon-badge bankai-toast-icon-info">
                <svg class="solar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:16px; height:16px; flex-shrink:0;" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
            </div>
        </template>

        <!-- متن پیغام -->
        <div class="bankai-toast-body">
            <span class="bankai-toast-text" x-text="toast.message"></span>
        </div>

        <!-- دکمه بستن سریع -->
        <button type="button"
                class="bankai-toast-dismiss"
                @click="toast.show = false"
                :title="isRtl ? 'بستن اعلان' : 'Dismiss notification'"
                aria-label="<?php esc_attr_e('Dismiss notification', 'bankai-core'); ?>">
            <svg class="solar-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px; height:14px; flex-shrink:0;" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
</div>