<p align="center">
  <img src="./logo.jpg" alt="Bankai Logo" width="133" style="border-radius: 16px;" />
</p>

# Bankai — High-Performance WordPress Ecosystem

**Developer:** GCORP LLC  
**Architecture:** Server-Side Rendered (SSR) Dual-Component Monorepo (Theme + Modular Core Plugin)  
**License:** GPLv2 or later  

Bankai is a enterprise-grade dual-component ecosystem for WordPress engineered for maximum execution speed, advanced SEO architecture, ultra-fast image optimization, and integrated AI capabilities.

---

## Architecture (Alpine.js + HTMX + Native PHP SSR + Tailwind CSS)

To guarantee ultimate execution speed, eliminate white screen (WSOD) errors, and remove heavy client-side bundler overhead, Bankai Core's admin suite utilizes **Native PHP Server-Side Rendering (SSR) coupled with Alpine.js v3, HTMX, and Cyberpunk Dark Slate (#080C14) Tailwind CSS design**:

1. **Bankai Theme (`themes/bankai-theme`)**
   * Ultra-lightweight base theme without jQuery dependency.
   * Dynamic CSS variables for high-fidelity color palettes and typography token management.

2. **Bankai Core Plugin (`plugins/bankai-core`)**
   * **Admin Suite Architecture:** Direct PHP view rendering via Native PHP SSR.
   * **Alpine.js:** Reactive client state management, tab routing (`activeTab`), interactive import drawers, module toggles, and toast notice feedback.
   * **HTMX & REST API Integration:** Asynchronous REST API communication (`/wp-json/bankai/v1`) for cache purging, starter kit synchronization, media bulk conversion, and SEO audit triggers.
   * **Cyberpunk Dark Slate Theme:** Deep Dark Slate (#080C14 background, #111827 cards, #10B981 green & #6366F1 indigo accents) visual design scoped inside `#bankai-admin-app` with 100% RTL (Right-to-Left) and Vazirmatn font support for Persian localization.

---

## Admin Views & Capabilities

1. **View 1 - Overview & Health Dashboard (`templates/admin/dashboard.php`)**
   * Real-time system telemetry, 404 anomaly logs with 301 heuristic recommendations, Core Web Vitals gauges, and active engine toggles.

2. **View 2 - Theme Architecture & 1-Click Starter Kits (`templates/admin/theme-kits.php`)**
   * Global typography font selector, container max-width slider (1200px-1600px), starter kit card grid with live preview links, and interactive Alpine.js import modal drawer with step checklist and animated progress bar.

3. **View 3 - Autonomous SEO & Schema Architecture (`templates/admin/seo-engine.php`)**
   * 28-Point SEO audit trigger, 12 modular feature cards (AI Search Visibility, AI Content Assistant, AI Link Genius, Instant Indexing Engine, XML Sitemaps Hub, Schema.org Builder, `llms.txt` Builder) with settings drawers.

4. **View 4 - High-Velocity Cache & Asset Engine (`templates/admin/speed-cache.php`)**
   * Live PageSpeed score (99/100 Mobile), 1-click "Purge All Caches" trigger, Redis/Memcached socket connection telemetry, database cleaner (revisions & transients), inline critical CSS, and Google Fonts localizer.

5. **View 5 - Media & Dynamic Watermark Studio (`templates/admin/media-watermark.php`)**
   * Automated WebP & AVIF conversion engine, real-time visual canvas positioning watermark overlay, image quality compression sliders, and bulk library converter.

6. **View 6 - AI Content Studio & Multi-LLM Orchestrator (`templates/admin/ai-studio.php`)**
   * Multi-LLM provider API key configuration (OpenAI, Anthropic Claude, Google Gemini, Groq, Ollama Local), automatic post ALT text generation, and SEO meta descriptions generator.

7. **View 7 - Settings, License & System Tools Hub (`templates/admin/settings-license.php`)**
   * License activation, system report diagnostic exporter, configuration backup/restore, and hard reset capabilities.

---

## Directory Structure

```text
.
├── logo.jpg                    <- Ecosystem logo
├── packages/
│   └── ui/                     <- Shared UI library
├── plugins/
│   └── bankai-core/
│       ├── logo.jpg            <- Admin menu branding icon
│       ├── bankai-core.php     <- Main plugin bootstrap
│       ├── assets/
│       │   ├── css/
│       │   │   └── bankai-admin.css <- Cyberpunk Dark Slate (#080C14) & RTL CSS
│       │   └── js/
│       │       ├── alpine.min.js    <- Alpine.js v3.13 library
│       │       ├── htmx.min.js      <- HTMX v1.9 library
│       │       └── bankai-admin.js  <- Alpine component logic & REST API bindings
│       ├── includes/
│       │   ├── class-admin-menu.php <- Menu registration (Position 2), asset loader
│       │   ├── class-editor-sidebar.php <- Gutenberg editor meta integration
│       │   ├── class-rest-api.php   <- REST API endpoints
│       │   └── modules/             <- Independent modules (SEO, Speed, Media, AI)
│       └── templates/
│           └── admin/
│               ├── admin-dashboard.php <- Master flexbox layout wrapper
│               ├── header.php          <- Modular header component
│               ├── sidebar.php         <- Two-column layout sidebar (LTR/RTL aware)
│               ├── dashboard.php       <- View 1: Overview & Health
│               ├── theme-kits.php      <- View 2: Theme & Starter Kits
│               ├── seo-engine.php      <- View 3: SEO Engine
│               ├── speed-cache.php     <- View 4: Speed & Cache
│               ├── media-watermark.php <- View 5: Media & Watermark
│               ├── ai-studio.php       <- View 6: AI Studio
│               └── settings-license.php<- View 7: Settings & License
└── themes/
    └── bankai-theme/           <- Light-weight base theme
```

---

## Development & Local Testing Setup

1. Copy or symlink `plugins/bankai-core` and `themes/bankai-theme` into your WordPress installation (`wp-content/plugins/` and `wp-content/themes/`).
2. Activate **Bankai Core** in WordPress WP Admin -> Plugins.
3. Access the dashboard at **WP Admin -> Bankai Core** (positioned 2nd in the main WP Admin sidebar with `logo.jpg` icon).
4. For PHP syntax validation across all core templates:
   ```bash
   for f in plugins/bankai-core/templates/admin/*.php; do php -l "$f"; done
   ```

---

## Release Packaging

To build all workspaces and generate production release zip archives:

```bash
npm run build
npm run build:zip
```
