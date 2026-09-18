<p align="center">
  <img src="./logo.jpg" alt="Bankai Logo" width="133" style="border-radius: 16px;" />
</p>

# Bankai — High-Performance WordPress Ecosystem

**Developer:** GCORP LLC  
**Architecture:** Server-Side Rendered (SSR) Dual-Component Monorepo (Theme + Modular Core Plugin)  
**License:** GPLv2 or later  

Bankai is a enterprise-grade dual-component ecosystem for WordPress engineered for maximum execution speed, advanced SEO architecture, ultra-fast image optimization, and integrated AI capabilities.

---
محتوای بازنویسی‌شده برای فایل **`README.md`** بر اساس ساختار جدید درختی و معماری به‌روزرسانی‌شده:

---

# Bankai Core — اکوسیستم ساختارمند و ماژولار وردپرس

پلتفرم **Bankai Core** یک اکوسیستم سازمانی و دو جزئی (افزونه ماژولار + پوسته پایه) برای وردپرس است که بر پایه معماری **Native PHP SSR**، **Alpine.js v3**، **HTMX** و **Tailwind CSS** توسعه یافته است. این معماری بدون وابستگی به jQuery یا Bundlerهای سنگین کلاینت، اجرای واکنش‌گرا و بدون تاخیر (Zero-Latency) پنل مدیریت و هسته وب‌سایت را تضمین می‌کند.

---

## معماری و قابلیت‌های کلیدی

1. **معماری ماژولار هسته (`inc/modules/`)**
* **SEO Engine (`class-seo-engine.php`):** موتور آنالیز ۲۸ گانه سئو، تولید هوشمند JSON-LD Schema، نقشه سایت پویا (XML) و پایش استناد برند در مدل‌های هوش مصنوعی (GEO/AI Search).
* **Speed Cache Engine (`class-speed-cache.php`):** مدیریت کش استاتیک HTML، اتصال سوکت Redis/Memcached، تزریق مستقیم Critical CSS و پاکسازی اتوماتیک دیتابیس.
* **Theme Kits System (`class-theme-kits.php`):** مدیریت کیت‌های آماده پوسته (از جمله پلتفرم‌های خبری Journa و سامانه‌های Paypey)، تایپوگرافی اختصاصی (Vazirmatn) و تنظیمات کانتینر.
* **Media Watermark Studio (`class-media-watermark.php`):** تبدیل خودکار تصاویر به فرمت‌های WebP/AVIF و اعمال واتربرگ تصویری/متنی روی کارهای رسانه‌ای.
* **AI Studio Hub (`class-ai-studio.php`):** ارکستراتور اتصال به APIهای متعددی نظیر OpenAI، Claude، Gemini و DeepSeek جهت تولید خودکار متن و ALT تصاویر.
* **Settings & License (`class-settings-license.php`):** سیستم اعتبارسنجی لایسنس، دریافت لاگ‌های سیستم و پشتیبان‌گیری از تنظیمات.


2. **لایه نمایش و UI رندرینگ (`views/admin/`)**
* تفکیک کامل کامپوننت‌های فرانت‌اند (`header.php`, `sidebar.php`, `toast.php`) از منطق بک‌اند داخل شابلون اصلی (`layout.php`).
* پشتیبانی ۱۰۰ درصدی از جهت‌نمایی راست‌به‌چپ (RTL) و فونت وزیرمتن.

# Bankai Core — راهنمای کامل محصول و نقشه پروژه


# Bankai Core

**Enterprise WordPress Engine · AI · SEO · Performance · Media**

نسخه: 1.2.0  
لایسنس: Proprietary (Enterprise)  
سازگاری: WordPress 6.0+ · PHP 8.0+ · MySQL 5.7+

---

## فهرست مطالب

1. [معرفی محصول](#1-معرفی-محصول)
2. [ویژگی‌های اصلی](#2-ویژگیهای-اصلی)
3. [نصب و راه‌اندازی](#3-نصب-و-راهاندازی)
4. [معماری سیستم](#4-معماری-سیستم)
5. [نقشه کامل فایل‌ها](#5-نقشه-کامل-فایلها)
6. [نقش و کاربرد هر فایل](#6-نقش-و-کاربرد-هر-فایل)
7. [جریان داده و AJAX](#7-جریان-داده-و-ajax)
8. [طراحی رابط کاربری](#8-طراحی-رابط-کاربری)
9. [پشتیبانی زبان (RTL/LTR)](#9-پشتیبانی-زبان-rtlltr)
10. [امنیت](#10-امنیت)
11. [توسعه و توسعه آینده](#11-توسعه-و-توسعه-آینده)
12. [عیب‌یابی رایج](#12-عیبیابی-رایج)

---

## 1. معرفی محصول

**Bankai Core** یک پلاگین سطح سازمانی (Enterprise) برای وردپرس است که به‌صورت یک پنل مدیریتی یکپارچه (SPA-like Admin) عمل می‌کند.

هدف اصلی:
- جایگزینی چندین پلاگین پراکنده (SEO، Cache، Watermark، AI، Schema)
- ارائه یک داشبورد واحد با طراحی مدرن (GitHub Primer Light)
- پشتیبانی کامل از فارسی (RTL) و انگلیسی (LTR)
- معماری ماژولار و قابل گسترش

پنل ادمین فقط **یک آیتم** در منوی وردپرس دارد:

```
Bankai Core
```

تمام زیرماژول‌ها داخل همین صفحه و از طریق سایدبار داخلی مدیریت می‌شوند.

---

## 2. ویژگی‌های اصلی

| ماژول | قابلیت‌ها |
|-------|-----------|
| **Overview** | تلمتری لحظه‌ای، امتیاز Schema، Cache Hit Ratio، Core Web Vitals، لاگ ۴۰۴ |
| **SEO Engine** | Schema JSON-LD، متاتگ خودکار، ریدایرکت ۳۰۱، ایندکس آنی (IndexNow/Google) |
| **Speed & Cache** | Page Cache، Redis Object Cache، بهینه‌سازی دیتابیس، Critical CSS |
| **Media & Watermark** | تبدیل WebP/AVIF، واترمارک پویا (GD/Imagick)، موقعیت‌دهی ۹ نقطه‌ای |
| **AI Studio** | Multi-LLM (Gemini، GPT-4o، Claude)، تولید محتوا، تست اتصال API |
| **LLMS.txt** | تولید خودکار `/llms.txt` و `/llms-full.txt` برای ربات‌های AI |
| **Theme Kits** | قالب‌های آماده ۱-کلیکه، Customizer (فونت، عرض کانتینر، Sticky Header) |
| **Settings & License** | مدیریت لایسنس، Feature Flags سراسری، RBAC، Export/Import JSON |

---

## 3. نصب و راه‌اندازی

### پیش‌نیازها
- WordPress 6.0 یا بالاتر
- PHP 8.0+ (توصیه: 8.1 یا 8.2)
- اکستنشن‌های PHP: `gd` یا `imagick`، `curl`، `json`، `mbstring`
- (اختیاری) Redis برای Object Cache

### مراحل نصب

1. پوشه پلاگین را در مسیر زیر قرار دهید:
   ```
   wp-content/plugins/bankai-core/
   ```

2. از پیشخوان وردپرس پلاگین را فعال کنید.

3. منوی **Bankai Core** در سایدبار وردپرس ظاهر می‌شود.

4. لایسنس را در تب **Settings & License** وارد کنید.

### ساختار پیشنهادی پوشه

```
bankai-core/
├── bankai-core.php              # فایل اصلی پلاگین (Bootstrap)
├── includes/                    # کلاس‌های PHP
│   ├── class-admin-menu.php
│   ├── class-rest-api.php
│   ├── class-ai-studio.php
│   ├── class-llms-txt.php
│   ├── class-media-watermark.php
│   ├── class-seo-engine.php
│   ├── class-settings-license.php
│   ├── class-speed-cache.php
│   └── class-theme-kits.php
├── views/                       # قالب‌های رابط کاربری
│   ├── layout.php
│   ├── header.php
│   ├── sidebar.php
│   ├── toast.php
│   └── tabs/
│       ├── tab-overview.php
│       ├── tab-ai-studio.php
│       ├── tab-media-watermark.php
│       ├── tab-seo-engine.php
│       ├── tab-speed-cache.php
│       ├── tab-settings-license.php
│       └── tab-theme-kits.php
├── assets/
│   ├── css/
│   │   └── bankai-admin.css
│   ├── js/
│   │   └── bankai-admin.js
│   └── images/
│       └── logo.jpg
└── README.md
```

---

## 4. معماری سیستم

```
┌─────────────────────────────────────────────────────────┐
│                    WordPress Admin                       │
│  ┌─────────────┐                                         │
│  │ WP Sidebar  │     ┌─────────────────────────────────┐ │
│  │ (سیاه)      │     │     #bankai-admin-app           │ │
│  │             │     │  ┌──────────┐  ┌─────────────┐  │ │
│  │ Bankai Core │────►│  │ Header   │  │             │  │ │
│  │ (تنها آیتم) │     │  │ (sticky) │  │  Main Area  │  │ │
│  │             │     │  └──────────┘  │  (Tabs)     │  │ │
│  │             │     │  ┌──────────┐  │             │  │ │
│  │             │     │  │ Sidebar  │  │  Overview   │  │ │
│  │             │     │  │ (sticky) │  │  SEO        │  │ │
│  │             │     │  │          │  │  Speed      │  │ │
│  │             │     │  │ Nav only │  │  Media      │  │ │
│  │             │     │  │ Icon+Name│  │  AI Studio  │  │ │
│  │             │     │  └──────────┘  │  Settings   │  │ │
│  └─────────────┘     │                └─────────────┘  │ │
│                      └─────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### الگوی طراحی
- **Singleton** برای تمام کلاس‌های ماژول
- **Alpine.js v3** برای reactivity سمت کلاینت
- **AJAX + Nonce** برای تمام عملیات
- **Feature Flags** سراسری از طریق Settings

---

## 5. نقشه کامل فایل‌ها

```
bankai-core/
│
├── bankai-core.php                     ← Bootstrap اصلی
│
├── includes/                           ← لایه منطق کسب‌وکار
│   ├── class-admin-menu.php            ← ثبت منو و رندر صفحه
│   ├── class-rest-api.php              ← REST API عمومی
│   ├── class-ai-studio.php             ← موتور Multi-LLM
│   ├── class-llms-txt.php              ← تولید llms.txt
│   ├── class-media-watermark.php       ← واترمارک و تبدیل تصویر
│   ├── class-seo-engine.php            ← SEO، Schema، Redirect
│   ├── class-settings-license.php      ← لایسنس و تنظیمات سراسری
│   ├── class-speed-cache.php           ← کش و بهینه‌سازی سرعت
│   └── class-theme-kits.php            ← قالب‌های آماده
│
├── views/                              ← لایه نمایش
│   ├── layout.php                      ← اسکلت اصلی + Data Bridge
│   ├── header.php                      ← هدر شفاف و sticky
│   ├── sidebar.php                     ← ناوبری داخلی (فقط آیکون+نام)
│   ├── toast.php                       ← اعلان‌های موقت
│   └── tabs/
│       ├── tab-overview.php
│       ├── tab-ai-studio.php
│       ├── tab-media-watermark.php
│       ├── tab-seo-engine.php
│       ├── tab-speed-cache.php
│       ├── tab-settings-license.php
│       └── tab-theme-kits.php
│
└── assets/
    ├── css/bankai-admin.css            ← استایل ایزوله شده
    ├── js/bankai-admin.js              ← منطق Alpine.js
    └── images/logo.jpg                 ← لوگوی منو و هدر
```

---

## 6. نقش و کاربرد هر فایل

### لایه Bootstrap

| فایل | نقش |
|------|-----|
| `bankai-core.php` | نقطه ورود پلاگین. بارگذاری کلاس‌ها، تعریف ثابت‌ها (`BANKAI_CORE_VERSION`، `BANKAI_CORE_VIEWS_DIR`)، ثبت هوک‌های اصلی |

### لایه منطق (includes/)

| فایل | نقش | هوک‌های مهم |
|------|-----|-------------|
| `class-admin-menu.php` | ثبت منوی تکی `Bankai Core`، رندر صفحه ادمین، تزریق CSS/JS | `admin_menu`, `admin_enqueue_scripts` |
| `class-rest-api.php` | نقاط پایانی REST عمومی | `rest_api_init` |
| `class-ai-studio.php` | تولید محتوا با LLM، تست اتصال API | `wp_ajax_bankai_generate_ai`, `wp_ajax_bankai_test_ai` |
| `class-llms-txt.php` | سرو کردن `/llms.txt` و `/llms-full.txt` | `template_redirect`, `init` (rewrite) |
| `class-media-watermark.php` | اعمال واترمارک روی آپلود، تبدیل WebP/AVIF | `wp_handle_upload`, `wp_ajax_bankai_save_watermark` |
| `class-seo-engine.php` | Schema، متاتگ، ریدایرکت ۳۰۱، لاگ ۴۰۴ | `wp_head`, `template_redirect`, `wp_ajax_bankai_get_404` |
| `class-settings-license.php` | ذخیره تنظیمات سراسری، اعتبارسنجی لایسنس، Export | `wp_ajax_bankai_save_settings`, `wp_ajax_bankai_export_config` |
| `class-speed-cache.php` | پاکسازی کش، بهینه‌سازی دیتابیس، بنچمارک | `wp_ajax_bankai_purge_cache`, `wp_ajax_bankai_optimize_db` |
| `class-theme-kits.php` | لیست کیت‌ها، درون‌ریزی قالب | `wp_ajax_bankai_import_kit`, `wp_ajax_bankai_sync_library` |

### لایه نمایش (views/)

| فایل | نقش |
|------|-----|
| `layout.php` | ظرف اصلی `#bankai-admin-app`. پل داده PHP → JS (`window.bankaiData`)، تشخیص RTL با `is_rtl()`، بارگذاری هدر/سایدبار/تب‌ها |
| `header.php` | هدر sticky و شفاف. دکمه‌های: قالب Bankai، پیش‌نمایش سایت، تخلیه کش، تغییر زبان |
| `sidebar.php` | ناوبری داخلی. فقط آیکون + نام (بدون بج). sticky هنگام اسکرول |
| `toast.php` | اعلان موفقیت/خطا با `role="alert"` |
| `tab-*.php` | هر تب یک پنل کامل Alpine.js با کارت‌ها، سوئیچ‌ها و Drawer |

### لایه دارایی (assets/)

| فایل | نقش |
|------|-----|
| `bankai-admin.css` | استایل کاملاً Scoped. ایزوله‌سازی `#wpcontent`، جلوگیری از تداخل با منوی وردپرس، RTL/LTR، کامپوننت‌ها |
| `bankai-admin.js` | تعریف `bankaiAdmin()` برای Alpine، مدیریت تب‌ها، Toast، Drawer، فراخوانی AJAX |
| `images/logo.jpg` | لوگوی منوی وردپرس و هدر (با CSS کنترل سایز ۲۰px و border-radius) |

---

## 7. جریان داده و AJAX

```
[User Action] → Alpine.js (@click / x-model)
       ↓
[bankai-admin.js] → jQuery/Fetch AJAX
       ↓
check_ajax_referer + current_user_can('manage_options')
       ↓
[class-*.php] → پردازش → wp_send_json_success/error
       ↓
[Alpine] → به‌روزرسانی UI + showToast()
```

### قوانین امنیتی اجباری در تمام AJAX

```php
if (!current_user_can('manage_options')) {
    wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
}
check_ajax_referer('bankai_admin_nonce', 'nonce');
// سپس sanitize_text_field / wp_unslash
```

---

## 8. طراحی رابط کاربری

- **طراحی:** GitHub Primer Light
- **فونت:** Inter (LTR) · Vazirmatn (RTL)
- **فریم‌ورک UI:** Tailwind-inspired utility classes (`bankai-card`, `bankai-grid-3`, `bankai-switch`)
- **آیکون‌ها:** Solar Broken SVG
- **حالت‌ها:** Sticky Header · Sticky Sidebar · Modal Drawer · Toast

### کلاس‌های CSS کلیدی

| کلاس | کاربرد |
|------|--------|
| `.bankai-tab-pane` | ظرف هر تب (`x-show` + `x-cloak`) |
| `.bankai-card` | کارت استاندارد |
| `.bankai-card-interactive` | کارت قابل کلیک |
| `.bankai-switch` | سوئیچ ماژول |
| `.bankai-nav-btn` | آیتم منوی سایدبار |
| `.bankai-modal-overlay` | پس‌زمینه Drawer/Modal |

---

## 9. پشتیبانی زبان (RTL/LTR)

زبان به‌صورت خودکار از وردپرس گرفته می‌شود:

```php
// در layout.php
$is_rtl = is_rtl();
```

و به Alpine پاس داده می‌شود:

```js
isRtl: window.bankaiData.isRtl
```

تمام متن‌ها با الگوی زیر نوشته شده‌اند:

```html
<span x-text="isRtl ? 'متن فارسی' : 'English text'">Fallback</span>
```

یا از تابع `t('key')` داخل `bankai-admin.js`.

---

## 10. امنیت

- تمام ورودی‌ها با `sanitize_text_field` / `wp_kses_post` پاک‌سازی می‌شوند
- تمام خروجی‌ها با `esc_html` / `esc_attr` / `esc_url` escape می‌شوند
- Nonce در تمام درخواست‌های AJAX بررسی می‌شود
- Capability `manage_options` برای تمام عملیات ادمین الزامی است
- فایل‌های آپلود از فیلتر `wp_handle_upload` عبور می‌کنند

---

## 11. توسعه و توسعه آینده

### افزودن تب جدید

1. فایل `views/tabs/tab-new-feature.php` بسازید
2. در `sidebar.php` دکمه ناوبری اضافه کنید
3. در `layout.php` فایل را include کنید
4. در `bankai-admin.js` کلید تب را به `normalizeTabMap` اضافه کنید

### افزودن ماژول Backend

1. کلاس Singleton در `includes/class-new-module.php`
2. هوک‌های AJAX را ثبت کنید
3. در `bankai-core.php` کلاس را instantiate کنید

### نقشه راه پیشنهادی

- [ ] اتصال واقعی به سرور لایسنس
- [ ] پشتیبانی کامل از IndexNow API
- [ ] داشبورد عمومی (Frontend Widget)
- [ ] Webhook برای اعلان‌های AI
- [ ] حالت Dark Mode

---

## 12. عیب‌یابی رایج

| مشکل | علت احتمالی | راه‌حل |
|------|-------------|--------|
| سایدبار زیر منوی وردپرس می‌رود | قوانین `toplevel_page_bankai-core` در CSS غیرفعال است | فعال‌سازی بخش Isolation در `bankai-admin.css` |
| لوگوی منو فول‌سایز است | CSS کنترل سایز اعمال نشده | `#adminmenu .toplevel_page_bankai-core .wp-menu-image img { width:20px !important; border-radius:6px; }` |
| تب نمایش داده نمی‌شود | مقدار `activeTab` اشتباه است | مطابقت با کلیدهای `overview`, `seo-engine`, `speed-cache`, `media-watermark`, `ai-studio`, `settings-license`, `theme-kits` |
| AJAX خطای ۴۰۳ می‌دهد | Nonce یا Capability | بررسی `bankai_admin_nonce` و نقش کاربر |
| زبان عوض نمی‌شود | `is_rtl()` نادرست | زبان سایت/کاربر را در وردپرس تغییر دهید |

---

## مجوز و پشتیبانی

این محصول تحت لایسنس اختصاصی (Proprietary) منتشر شده است.  
برای پشتیبانی تجاری و تمدید لایسنس با تیم Bankai تماس بگیرید.

---
