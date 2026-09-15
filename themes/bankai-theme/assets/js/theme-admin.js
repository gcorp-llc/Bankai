/**
 * Bankai Theme Admin Dashboard Alpine.js Component (Astra Style)
 */
function bankaiThemeAdmin() {
    return {
        isRtl: window.bankaiThemeData?.isRtl ?? true,
        customizeUrl: window.bankaiThemeData?.customizeUrl || '/wp-admin/customize.php',
        toast: {
            show: false,
            message: '',
            timer: null
        },
        modulesState: window.bankaiThemeData?.modules || {
            transparent_header: true,
            sticky_header: true,
            mega_menu: true,
            white_label: false,
            custom_fonts: true,
            woocommerce_boost: true,
            scroll_to_top: true,
            page_headers: true,
        },
        themeModules: {
            transparent_header: {
                title: 'هدر شفاف (Transparent Header)',
                description: 'طراحی مدرن هدر شیشه‌ای و شفاف برای صفحات نخست و فرود بدون مرز با محتوا',
                icon: '✨',
                customizerSection: 'bankai_transparent_header'
            },
            sticky_header: {
                title: 'هدر چسبان هوشمند (Sticky Header)',
                description: 'ثابت ماندن یا پدیدار شدن نرم هدر هنگام اسکرول به سمت پایین و کوچک‌سازی ارتفاع',
                icon: '📌',
                customizerSection: 'bankai_sticky_header'
            },
            mega_menu: {
                title: 'مگامنو حرفه‌ای (Advanced Mega Menu)',
                description: 'ایجاد منوهای چندستونه، افزودن ابزارک، تصاویر و دسته‌بندی‌های غنی به ناوبری سایت',
                icon: '🗂️',
                customizerSection: 'bankai_mega_menu'
            },
            custom_fonts: {
                title: 'فونت‌های محلی و اختصاصی (Custom Local Fonts)',
                description: 'بارگذاری مستقیم فونت‌های وزیرمتن، ایران‌یکان و وب‌فونت‌ها از سرور خودتان بدون نقض GDPR',
                icon: '🔤',
                customizerSection: 'bankai_typography_section'
            },
            woocommerce_boost: {
                title: 'بهینه‌ساز فروشگاه ووکامرس (WooCommerce Booster)',
                description: 'افزودن تسویه‌حساب بدون حواس‌پرتی، سبد خرید بازشونده و گالری زوم محصولات',
                icon: '🛍️',
                customizerSection: 'bankai_woocommerce_section'
            },
            scroll_to_top: {
                title: 'دکمه بازگشت به بالا (Scroll To Top)',
                description: 'دکمه شناور نرم با قابلیت سفارشی‌سازی رنگ، موقعیت چپ/راست و آیکون',
                icon: '⬆️',
                customizerSection: 'bankai_scroll_to_top'
            },
            page_headers: {
                title: 'طرح عناوین برگه (Page Header & Hero)',
                description: 'شخصی‌سازی هیرو بنر، عنوان نوشته، بردکرامب و تصویر پس‌زمینه در بالای محتوا',
                icon: '🖼️',
                customizerSection: 'bankai_page_header'
            },
            white_label: {
                title: 'برچسب سفید آژانسی (White Label Pro)',
                description: 'تغییر نام و برند قالب Bankai به نام برند یا شرکت مشتری در کل پنل وردپرس',
                icon: '🏷️',
                customizerSection: 'bankai_white_label'
            }
        },

        init() {
            // Initialization
        },

        toggleModule(key) {
            this.modulesState[key] = !this.modulesState[key];
            const modTitle = this.themeModules[key]?.title || key;
            const statusText = this.modulesState[key] ? 'فعال' : 'غیرفعال';
            
            this.showToast(`ماژول ${modTitle} با موفقیت ${statusText} شد.`);

            // Call WP AJAX if present
            if (window.bankaiThemeData?.ajaxUrl) {
                const formData = new FormData();
                formData.append('action', 'bankai_toggle_theme_module');
                formData.append('module', key);
                formData.append('state', this.modulesState[key] ? '1' : '0');
                formData.append('nonce', window.bankaiThemeData.nonce || '');

                fetch(window.bankaiThemeData.ajaxUrl, {
                    method: 'POST',
                    body: formData
                }).catch(() => {});
            }
        },

        showToast(msg) {
            this.toast.message = msg;
            this.toast.show = true;
            if (this.toast.timer) clearTimeout(this.toast.timer);
            this.toast.timer = setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        }
    };
}
