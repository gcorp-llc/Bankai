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



---

## نقشه فایل‌ها و ساختار پوشه‌بندی (Directory Structure)

```text
.
├── .distignore                  <- لیست استثناها برای ساخت فایل خروجی Release
├── .env.example                 <- الگوی متغیرهای محیطی برای توسعه local
├── .gitignore                   <- مدیریت فایل‌های نادیده‌گرفته‌شده در Git
├── README.md                    <- مستندات رسمی پروژه
├── plugins/
│   └── bankai-core/             <- هسته اصلی افزونه
│       ├── bankai-core.php      <- فایل بوت‌استرپ اصلی افزونه
│       ├── assets/              <- دارایی‌های ایستا (Static Assets)
│       │   ├── css/
│       │   │   └── bankai-admin.css <- استایل‌های اختصاصی پنل مدیریت
│       │   ├── images/
│       │   │   └── logo.jpg     <- لوگو و آیکون برند
│       │   └── js/
│       │       ├── alpine.min.js    <- کتابخانه Alpine.js v3
│       │       ├── bankai-admin.js  <- منطق تعاملی فرانت‌اند و REST API
│       │       └── htmx.min.js      <- کتابخانه HTMX برای درخواست‌های درخواست‌های AJAX/SSR
│       ├── inc/            <- کلاس‌های هسته و ماژول‌ها
│       │   ├── class-admin-menu.php <- ثبت منوی منوی مدیریت و enqueue اسکریپت‌ها
│       │   ├── class-rest-api.php   <- اندپوینت‌های اختصاصی REST API (/bankai/v1)
│       │   ├── helpers.php          <- توابع کمکی و عمومی (Global Helpers)
│       │   └── modules/             <- ماژول‌های مستقل عملکردی
│       │       ├── class-ai-studio.php
│       │       ├── class-media-watermark.php
│       │       ├── class-seo-engine.php
│       │       ├── class-settings-license.php
│       │       ├── class-speed-cache.php
│       │       └── class-theme-kits.php
│       └── views/               <- قالب‌های نمایش (Views / Templates)
│           └── admin/
│               ├── header.php   <- هدر اختصاصی پنل مدیریت
│               ├── layout.php   <- شابلون اصلی اسکلت‌بندی پنل
│               ├── sidebar.php  <- منوی کناری تعاملی
│               └── toast.php    <- سیستم اعلان‌ها و اعلان‌های تعاملی Toast
└── themes/                      <- پوشه پوسته‌های توسعه (Bankai Theme)

```

---

## روش توسعه و پیاده‌سازی (Development Workflow)

### ۱. راه اندازی محیط توسعه محلی

1. پوشه `plugins/bankai-core` و `themes/` را به دایرکتوری وردپرس محلی خود (`wp-content/plugins/` و `wp-content/themes/`) متصل (Symlink) یا کپی کنید.
2. فایل `.env.example` را به `.env` تغییر نام داده و تنظیمات دیتابیس و کلیدهای API را پیکربندی کنید.
3. افزونه **Bankai Core** را از پنل مدیریت وردپرس فعال کنید.
4. جهت بررسی صحت سینتکس PHP فایل‌های ماژول و نودها، دستور زیر را اجرا کنید:
```bash
find plugins/bankai-core -name "*.php" -exec php -l {} \;

```



---

## روش ایجاد فایل پروداکت (Production Packaging)

برای ساخت فایل فشرده آماده نصب (Production Archive) و حذف فایل‌های توسعه (مانند `.git` ، `.env` و...):

### روش اول: استفاده از WP-CLI (پیشنهادی)

در صورتی که WP-CLI روی سیستم نصب است:

```bash
wp dist-archive plugins/bankai-core bankai-core-production.zip

```

### روش دوم: ساخت اسکریپتی از طریق CLI با رعایت قوانین `.distignore`

دستور زیر با خواندن استثناهای فایل `.distignore` یک نسخه سبک و پاکیزه ساخت پروداکت در ریشه پروژه ایجاد می‌کند:

```bash
zip -r bankai-core.zip plugins/bankai-core/ -x@.distignore

```