<?php
/**
 * Admin tab: Jalali calendar (Shamsi) settings — Bankai
 *
 * @package Bankai
 */
defined('ABSPATH') || exit;

$js = function_exists('bankai_get_option') ? bankai_get_option('jalali_settings', []) : [];
if (!is_array($js)) {
    $js = [];
}
$persian_digits = !empty($js['persian_digits']);
$dual_date      = !empty($js['dual_date']);
$datepicker     = !isset($js['datepicker']) || !empty($js['datepicker']);
$admin_column   = !isset($js['admin_column']) || !empty($js['admin_column']);
$enabled = function_exists('bankai_is_module_active') ? bankai_is_module_active('jalali_calendar') : true;

$sample = '';
if (class_exists('Bankai_Jalali_Calendar')) {
    $sample = Bankai_Jalali_Calendar::format_timestamp(
        time(),
        get_option('date_format') . ' — ' . get_option('time_format')
    );
} else {
    $sample = date_i18n(get_option('date_format') . ' ' . get_option('time_format'));
}
?>
<div id="tab-jalali-calendar" class="bankai-tab-pane" x-show="activeTab === 'jalali-calendar'" x-cloak
     x-data="bankaiJalaliTab()">
    <div class="bankai-card" style="padding:22px;margin-bottom:16px">
        <h2 style="margin:0 0 8px;font-size:20px;font-weight:800;display:flex;align-items:center;gap:8px">
            <svg class="solar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            تقویم جلالی (شمسی)
        </h2>
        <p style="margin:0;font-size:13px;color:#64748b;line-height:1.7">
            نمایش و انتخاب تاریخ شمسی در وردپرس و پنل بنکای.
        </p>
    </div>

    <div class="bankai-card" style="padding:20px;margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px">
            <div>
                <div style="font-weight:800;font-size:14px">وضعیت ماژول</div>
                <div style="font-size:12px;color:#64748b"><?php echo $enabled ? 'فعال — تاریخ‌ها شمسی نمایش داده می‌شوند' : 'غیرفعال — از پیشخوان فعال کنید'; ?></div>
            </div>
            <span style="font-size:12px;font-weight:800;padding:6px 12px;border-radius:999px;background:<?php echo $enabled ? '#e8f5e9' : '#f1f5f9'; ?>;color:<?php echo $enabled ? '#0f7b3a' : '#64748b'; ?>">
                <?php echo $enabled ? 'فعال' : 'خاموش'; ?>
            </span>
        </div>
        <div style="padding:14px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;margin-bottom:16px">
            <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px">نمونه تاریخ الان</div>
            <div style="font-size:18px;font-weight:800;direction:rtl"><?php echo esc_html($sample); ?></div>
        </div>

        <div style="display:grid;gap:12px;margin-bottom:16px">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600">
                <input type="checkbox" x-model="persianDigits"> ارقام فارسی (۰۱۲۳…)
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600">
                <input type="checkbox" x-model="dualDate"> نمایش دوگانه (شمسی + میلادی)
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600">
                <input type="checkbox" x-model="datepicker"> دیت‌پیکر جلالی در ادمین
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600">
                <input type="checkbox" x-model="adminColumn"> تبدیل ستون تاریخ در لیست نوشته‌ها
            </label>
        </div>

        <div style="margin-bottom:16px">
            <div style="font-size:12px;font-weight:800;margin-bottom:6px">تست دیت‌پیکر</div>
            <input type="text" class="bankai-jalali-date" placeholder="تاریخ شمسی را انتخاب کنید" style="max-width:260px">
        </div>

        <button type="button" class="bankai-btn bankai-btn-primary" @click="save()" :disabled="saving"
                style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;border-radius:10px;border:none;background:#0078d4;color:#fff;font-weight:700;cursor:pointer">
            <span x-text="saving ? 'در حال ذخیره…' : 'ذخیره تنظیمات'"></span>
        </button>
        <p x-show="msg" style="margin:10px 0 0;font-size:12px;font-weight:600;color:#0f7b3a" x-text="msg"></p>
    </div>
</div>
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('bankaiJalaliTab', function () {
        return {
            persianDigits: <?php echo $persian_digits ? 'true' : 'false'; ?>,
            dualDate: <?php echo $dual_date ? 'true' : 'false'; ?>,
            datepicker: <?php echo $datepicker ? 'true' : 'false'; ?>,
            adminColumn: <?php echo $admin_column ? 'true' : 'false'; ?>,
            saving: false,
            msg: '',
            init: function () {
                this.$nextTick(function () {
                    if (window.BankaiJalaliDatepicker) window.BankaiJalaliDatepicker.init();
                });
            },
            save: function () {
                var self = this;
                self.saving = true;
                self.msg = '';
                var body = new FormData();
                body.append('action', 'bankai_save_jalali_settings');
                body.append('nonce', (window.bankaiAdmin && bankaiAdmin.nonce) || '');
                body.append('persian_digits', self.persianDigits ? '1' : '0');
                body.append('dual_date', self.dualDate ? '1' : '0');
                body.append('datepicker', self.datepicker ? '1' : '0');
                body.append('admin_column', self.adminColumn ? '1' : '0');
                fetch((window.bankaiAdmin && bankaiAdmin.ajaxUrl) || ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        self.saving = false;
                        self.msg = (res && res.success) ? 'ذخیره شد' : ((res && res.data && res.data.message) || 'خطا در ذخیره');
                    })
                    .catch(function () {
                        self.saving = false;
                        self.msg = 'خطا در ارتباط';
                    });
            }
        };
    });
});
</script>
