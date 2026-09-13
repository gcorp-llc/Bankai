<p align="center">
  <img src="./logo.jpg" alt="Bankai Logo" width="133" style="border-radius: 16px;" />
</p>

# Bankai — High-Performance WordPress Ecosystem

**Developer:** GCORP LLC  
**Architecture:** Server-Side Rendered (SSR) Dual-Component Monorepo (Theme + Modular Core Plugin)  
**License:** GPLv2 or later  

Bankai is a structured dual-component ecosystem for WordPress engineered for maximum speed, advanced SEO, optimized media management, and integrated AI capabilities.

---

## Architecture (Alpine.js + HTMX + Native PHP SSR + Tailwind CSS)

To guarantee ultimate execution speed, eliminate white screen (WSOD) errors completely, and remove heavy client-side bundler overhead, the Bankai Core admin suite is built using **Native PHP Server-Side Rendering (SSR) coupled with Alpine.js, HTMX, and Tailwind CSS**:

1. **Bankai Theme (`themes/bankai-theme`)**
   * Ultra-lightweight base theme without jQuery dependency.
   * Dynamic CSS variables for color palette and typography management.

2. **Bankai Core Plugin (`plugins/bankai-core`)**
   * **Admin Suite Architecture:** Modular PHP view rendering via Native PHP (purging legacy `@wordpress/scripts` / React build requirements).
   * **Alpine.js:** Lightweight reactive UI client state management, instant tab/route navigation, interactive import modal drawers, module toggles, and toast notice feedback.
   * **HTMX / REST Client:** Asynchronous REST API interactions (`/wp-json/bankai/v1`) for cache purging, starter kit synchronization, and 404 redirect rules.
   * **Cyberpunk Dark Slate Theme:** Custom Deep Dark Slate (#080C14 background, #111827 cards, #10B981 green accents) visual design scoped inside `#bankai-admin-app` with 100% RTL (Right-to-Left) and Persian Vazirmatn font support.

---

## Admin Views Implemented

1. **View 1 - Overview & Health Dashboard (`templates/admin/dashboard.php`)**
   * Real-time telemetry, 404 anomaly logs with 301 heuristic recommendations, Core Web Vitals gauges, and active engine toggles.

2. **View 2 - Theme Architecture & 1-Click Starter Kits (`templates/admin/theme-kits.php`)**
   * Global typography font selector, container max-width slider (1200px-1600px), 3-column starter kit card grid with live preview links, and interactive Alpine.js import modal drawer with step checklist and animated progress bar (0% -> 100%).

3. **View 3 - Autonomous SEO & Schema Architecture (`templates/admin/seo-engine.php`)**
   * 28-Point SEO audit trigger, 12 modular feature cards (AI Search Visibility, AI Content Assistant, AI Link Genius, Instant Indexing Engine, XML Sitemaps Hub, Schema.org Builder, RankMath-Grade Tools, `llms.txt` Builder) with module settings drawers.

4. **View 4 - High-Velocity Cache & Asset Engine (`templates/admin/speed-cache.php`)**
   * Live PageSpeed score (99/100 Mobile), 1-click "Purge All Caches" button, Redis/Memcached socket connection telemetry, database cleaner (revisions & transients), inline critical CSS, and Google Fonts localizer.

---

## Directory Structure

```text
plugins/bankai-core/
├── bankai-core.php             <- Main plugin bootstrap
├── assets/                     <- Static isolated assets
│   ├── css/
│   │   └── bankai-admin.css    <- Dark Slate / Cyberpunk & RTL styling
│   └── js/
│       ├── alpine.min.js       <- Alpine.js v3.13 library
│       ├── htmx.min.js         <- HTMX v1.9 library
│       └── bankai-admin.js     <- Alpine component logic & REST API bindings
├── includes/                   <- PHP Classes & backend logic
│   ├── class-admin-menu.php    <- Menu registration, submenu routing & asset loader
│   ├── class-editor-sidebar.php<- Gutenberg block editor meta integration
│   ├── class-rest-api.php      <- REST API endpoints
│   └── modules/                <- Independent modules (SEO, Speed, Media, AI)
└── templates/                  <- Direct PHP view templates
    └── admin/
        ├── admin-dashboard.php <- Master layout wrapper
        ├── header.php          <- Modular header component
        ├── sidebar.php         <- Navigation sidebar (LTR/RTL aware)
        ├── dashboard.php       <- View 1: Overview & Health
        ├── theme-kits.php      <- View 2: Theme & Starter Kits
        ├── seo-engine.php      <- View 3: SEO Engine
        └── speed-cache.php     <- View 4: Speed & Cache Engine
```

---

## Development & Testing Workflow

Testing and developing Bankai Core is zero-build and immediate:

1. Place `plugins/bankai-core` in your local WordPress `wp-content/plugins/` directory and activate it.
2. Navigate to `admin.php?page=bankai-core` in WP Admin.
3. The interface renders cleanly via PHP SSR without JS compilation steps, eliminating blank screens.

---

## Release Packaging

To build production release zip packages:

```bash
npm run build:zip
```
