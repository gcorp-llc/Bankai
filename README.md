<p align="center">
  <img src="./logo.jpg" alt="Bankai Logo" width="133" style="border-radius: 16px;" />
</p>

# Bankai — High-Performance WordPress Ecosystem

**Developer:** GCORP LLC  
**Architecture:** Dual-Component (Theme + Modular Core Plugin)  
**License:** GPLv2 or later  

Bankai یک سیستم دوپایهٔ ساختاریافته برای وردپرس است که با هدف ارتقای حداکثری سرعت، سئوی پیشرفته، مدیریت بهینهٔ رسانه و ادغام پردازش‌های هوش مصنوعی طراحی شده است. این پروژه نیاز سایت به چندین افزونهٔ سنگین و مجزا را از بین می‌برد و تمام امکانات کلیدی را در یک اکوسیستم یکپارچه و عاری از کدهای اضافی ارائه می‌دهد.

---

## 🏛 معماری پروژه (Dual-Component Architecture)

پروژه به دو بخش کاملاً مجزا اما هماهنگ تفکیک شده است تا استانداردهای کارایی و مالکیت داده رعایت شوند:

1. **Bankai Theme (`themes/bankai-theme`)**
   * پوستهٔ فوق‌العاده سبک بر پایهٔ مفاهیم Astra بدون وابستگی به jQuery.
   * سیستم مدیریت پالت رنگ (Dynamic CSS Variables) و مدیریت تایپوگرافی و فونت‌های سفارشی (Font Display Swappable).
   * عاری از کدهای سنگین بک‌اند و ابزارهای سئو جهت عدم وابستگی داده به پوسته.

2. **Bankai Core Plugin (`plugins/bankai-core`)**
   * افزونهٔ اصلی و ماژولار با پنل React قدرتمند مبتنی بر `@wordpress/components`.
   * **ماژول SEO:** متاتگ‌های داینامیک، اسکیمای JSON-LD، ساخت سایت‌مپ XML و سیستم ریدایرکت (جایگزین Rank Math).
   * **ماژول Speed:** کش سطح صفحه، لایه Safe Drop-in برای Object Cache (Redis/Memcached)، مینیفای دارایی‌ها و لیزی‌لود (جایگزین WP Rocket).
   * **ماژول Media:** تبدیل خودکار تصاویر به WebP/AVIF و اعمال واترمارک اختصاصی در پس‌زمینه با Action Scheduler.
   * **ماژول AI:** لایهٔ اتصال به Gemini, OpenAI, Claude, DeepSeek و OpenRouter همراه با ذخیره‌سازی رمزنگاری‌شده کلیدها (AES-256-GCM).

---

## 📂 ساختار مخزن (Monorepo Layout)

ساختار مخزن در زمان توسعه به شکل زیر است. لایهٔ توسعه (`packages/ui`, `docs`, `package.json` ریشه) در زمان خروجی گرفتن نهایی فیلتر شده و **فقط** دو پوشهٔ زیپ‌شده مستقل برای آپلود در `wp-content` تولید می‌شوند.

```text
bankai/
├── package.json                   ← [فقط توسعه] پیکربندی npm workspaces و اسکریپت‌های build
├── .distignore                    ← [فقط توسعه] استثناها برای ساخت پکیج زیپ نهایی
├── packages/
│   └── ui/                        ← [فقط توسعه] کامپوننت‌ها و هپلهای مشترک JS/React
├── themes/
│   └── bankai-theme/              ← [قابل آپلود در wp-content/themes]
│       ├── style.css              ← هدر استاندارد پوسته
│       ├── functions.php
│       ├── package.json           ← کانفیگ @wordpress/scripts پوسته
│       ├── src/                   ← سورس React/JS
│       └── build/                 ← خروجی کامپایل‌شده پوسته
└── plugins/
    └── bankai-core/               ← [قابل آپلود در wp-content/plugins]
        ├── bankai-core.php        ← فایل اصلی افزونه
        ├── package.json           ← کانفیگ @wordpress/scripts افزونه
        ├── admin/
        │   ├── src/               ← سورس React پنل مدیریت
        │   └── build/             ← خروجی کامپایل‌شده افزونه
        └── includes/              ← ماژول‌ها و منطق PHP بک‌اند

```

---

## 🛠 پشتهٔ فناوری (Tech Stack)

* **PHP:** حداقل ۷٫۴ (سازگار کامل با PHP 8.1+)
* **WordPress:** نسخه ۶٫۰ به بالا
* **Build Tool:** `@wordpress/scripts` (بدون بارگذاری نسخهٔ دوم React روی سرور)
* **UI Components:** `@wordpress/components` (پشتیبانی نیتیو از RTL، دسترسی‌پذیری و استایل بومی وردپرس)
* **State Management:** `@wordpress/data`
* **Data Fetching:** `@wordpress/api-fetch`
* **Background Tasks:** Action Scheduler (پردازش غیرهمزمان رسانه)
* **Encryption:** AES-256-GCM (ذخیره‌سازی کلیدهای API)

---

## 💻 راهنمای توسعه‌دهندگان (Development Setup)

### پیش‌نیازها

* Node.js نسخه ۱۸ به بالا
* pnpm یا npm

### نصب وابستگی‌ها و بیلد

```bash
# نصب کل وابستگی‌های Monorepo
npm install

# اجرای حالت توسعه (Watch Mode)
npm run dev

# بیلد نهایی فایل‌های JS و CSS برای تولید
npm run build

```