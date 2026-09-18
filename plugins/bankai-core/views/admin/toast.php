<?php
defined('ABSPATH') || exit;
?>
<div x-show="toast.show"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="bankai-toast"
     :class="toast.type === 'error' ? 'bankai-toast-error' : 'bankai-toast-success'"
     role="alert"
     aria-live="assertive"
     aria-atomic="true">

    <template x-if="toast.type !== 'error'">
        <svg class="solar-icon" style="color: #1A7F37; flex-shrink: 0; width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9.5" />
            <path d="M8.5 12.5l2.5 2.5 5-5" />
        </svg>
    </template>

    <template x-if="toast.type === 'error'">
        <svg class="solar-icon" style="color: #CF222E; flex-shrink: 0; width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg>
    </template>

    <span x-text="toast.message" style="line-height: 1.45; color: #1F2328; font-size: 13px;"></span>
</div>