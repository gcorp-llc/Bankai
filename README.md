<p align="center">
  <img src="./logo.jpg" alt="Bankai Logo" width="133" style="border-radius: 16px;" />
</p>

# Bankai — High-Performance WordPress Ecosystem

**Developer:** GCORP LLC  
**Architecture:** Dual-Component Monorepo (Theme + Modular Core Plugin)  
**License:** GPLv2 or later  

Bankai یک سیستم دوپایهٔ ساختاریافته برای وردپرس است که با هدف ارتقای حداکثری سرعت، سئوی پیشرفته، مدیریت بهینهٔ رسانه و ادغام پردازش‌های هوش مصنوعی طراحی شده است. این پروژه نیاز سایت به چندین افزونهٔ سنگین و مجزا را از بین می‌برد و تمام امکانات کلیدی را در یک اکوسیستم یکپارچه و عاری از کدهای اضافی ارائه می‌دهد.

---

## 🏛 معماری پروژه (Dual-Component Architecture)

پروژه به دو بخش کاملاً مجزا اما هماهنگ تفکیک شده است تا استانداردهای کارایی و مالکیت داده رعایت شوند:

1. **Bankai Theme (`themes/bankai-theme`)**
   * پوستهٔ فوق‌العاده سبک بر پایه مفاهیم Astra بدون وابستگی به jQuery.
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

ساختار مخزن بر پایه **npm Workspaces** تفکیک شده است:

```text
bankai/
├── package.json                   ← [فقط توسعه] پیکربندی npm workspaces و اسکریپت‌های build
├── .distignore                    ← [فقط توسعه] استثناها برای ساخت پکیج زیپ نهایی
├── packages/
│   └── ui/                        ← [فقط توسعه] کامپوننت‌ها و هپلهای مشترک JS/React (@bankai/ui)
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

## 💻 راهنمای راه‌اندازی محیط توسعه محلی (Local Development Setup)

پیش از شروع، مخزن پروژه را کلون کرده و وابستگی‌ها را نصب کنید:

```bash
git clone <repository-url>
cd bankai
npm install
```

برای تست و توسعه، می‌توانید از یکی از دو روش زیر استفاده نمایید:

### روش اول: استفاده از Symlink (میانبر سیستمی - LocalWP / Laragon)

در این روش، یک وردپرس محلی نصب کرده و پوشه‌های توسعهٔ تم و افزونه را مستقیماً به پوشه `wp-content` وصل می‌کنید:

#### ۱. ایجاد Symlink:

- **در لینوکس / مک (دستور Terminal):**
  ```bash
  ln -s /path/to/bankai/themes/bankai-theme /path/to/wordpress/wp-content/themes/bankai-theme
  ln -s /path/to/bankai/plugins/bankai-core /path/to/wordpress/wp-content/plugins/bankai-core
  ```

- **در ویندوز (دستور Command Prompt با دسترسی Administrator):**
  ```cmd
  mklink /D "C:\path\to\wordpress\wp-content\themes\bankai-theme" "C:\path\to\bankai\themes\bankai-theme"
  mklink /D "C:\path\to\wordpress\wp-content\plugins\bankai-core" "C:\path\to\bankai\plugins\bankai-core"
  ```

#### ۲. فرایند توسعه:
1. وارد ترمینال پروژه Monorepo شوید و دستور `npm run dev` را اجرا کنید.
2. در پیشخوان وردپرس محلی خود، پوسته **Bankai Theme** و افزونه **Bankai Core** را فعال کنید.
3. هر تغییری در کدهای React یا PHP بدهید، کامپایل شده و در مرورگر قابل مشاهده است.

---

### روش دوم: استفاده از ابزار رسمی Docker وردپرس (`wp-env`)

در صورتی که Docker روی سیستم شما فعال است، نیازی به نصب دستی وردپرس ندارید. ابزار `@wordpress/scripts` یک محیط کامل وردپرس را به‌صورت اتوماتیک ایجاد می‌کند:

1. در ریشه پروژه دستور زیر را بزنید:
   ```bash
   npx wp-env start
   ```
2. این دستور یک سایت وردپرس محلی کامل روی آدرس `http://localhost:8888` بالا می‌آورد (نام کاربری: `admin` / رمز: `password`).
3. تم و افزونه شما به‌صورت خودکار روی آن سوار و فعال می‌شوند.
4. سپس دستور `npm run dev` را جهت watch و کامپایل اتوماتیک اجرا کنید.

---

## 🔄 خلاصه جریان کاری توسعه (Workflow Summary)

```text
[شما کدهای React/PHP را ویرایش می‌کنید]
                  ↓
[دستور npm run dev فایل‌های build را آماده می‌کند]
                  ↓
[سرور محلی PHP (LocalWP/Docker) فایل‌های build را رندر کرده و در مرورگر نشان می‌دهد]
```

---

## 📦 بسته‌بندی و ساخت نسخه نهایی جهت انتشار (Release Packaging)

برای ساخت فایل‌های زیپ تمیز، کامپایل‌شده و آماده نصب در محیط پروداکشن:

```bash
npm run build:zip
```

این اسکریپت پوشه `dist/` را ایجاد کرده و دو فایل زیپ مستقل تولید می‌کند:
- `dist/bankai-theme.zip`
- `dist/bankai-core.zip`
