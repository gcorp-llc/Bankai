/**
 * Bankai Core - Alpine.js State Engine & HTMX Handlers
 * public/js/bankai-admin.js
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('bankaiAdmin', () => ({
        activeTab: (window.bankaiData && window.bankaiData.activeTab) || 'overview',
        isRtl: (window.bankaiData && window.bankaiData.isRtl) || false,
        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        init() {
            console.log('Bankai Core Admin Suite Initialized. Active Tab:', this.activeTab);
        },

        switchTab(tabId) {
            this.activeTab = tabId;
        },

        triggerCachePurge() {
            this.showToast(this.isRtl ? 'تمام کش‌های سیستم با موفقیت پاکسازی شدند.' : 'All system caches successfully purged.', 'success');
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        }
    }));
});
