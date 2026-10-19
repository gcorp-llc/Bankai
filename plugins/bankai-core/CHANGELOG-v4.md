# Bankai SEO v4 — Changelog

## Implemented

1. **List table score circle**  
   Column «سئو» with SVG progress ring and number. Colors:
   - 0–10 → `#B8BCC2`
   - 10–20 → `#E8D3A2`
   - 20–40 → `#A6122D`
   - 40–60 → `#E0A030`
   - 60–80 → `#93C572`
   - 80–100 → `#1E7F5C`

2. **Gutenberg toolbar button**  
   Same circle + label «سئو», rounded pill style. Live updates via `bankai-seo-score` event.

3. **Compact header**  
   Only score ring + «سئوی بنکای» + refresh. Faster dashoffset transition (0.25s).

4. **Larger tabs, less edge padding**  
   Grid 5-column pill tabs; body padding 8px.

5. **No inner scroll**  
   Meta box content flows with the page; sticky header only.

6. **Keywords (Rank Math style)**  
   Chip input, Enter/comma to add, double-click edit, × remove, primary badge, live density from analysis.

7. **Professional AI modal**  
   Tabs: عنوان و متا · کلیدواژه · لینک داخلی · لینک خارجی · بازنویسی.  
   Placeholder generator until AI Studio is connected (`generateAI`).

8. **Links tab**  
   Internal/external counts, search posts API, insert bold internal links, multi external with nofollow, list of existing links in content.

9. **Schema tab**  
   Type select + editable JSON-LD, rebuild helper.

10. **Social tab**  
    Full OG + Twitter/X fields with autosave.

## Autosave
Field changes debounce → analyze (350ms) + save (900ms). No footer save button.

## Analysis checks (~20)
Focus keyword, in title/desc/URL/intro/content, content length, headings, alt, density, URL length, internal/external links, title start/length/number, meta desc length, short paragraphs, rich media.

## Files
```
functions.php

## v1.4.0 - SEO Image Compression, Auto Schema, Enhanced AI Assistant (Integrated Optimize, GEO, AEO, Image Gen)

### Images Tab
- Visual loading preview for all images on compress/standardize button
- Compress, format change (WebP etc.), uniform sizing for all images including cover
- Before/after display post-compression
- Unified size framing for all images

### Schema Tab
- Auto JSON-LD preview on initial load

### AI Assistant Tab
- Smart Optimize button: enlarges module (2x width)
- Integrated Article Optimization: content analysis, SEO title/meta, keywords, internal links, images/alt, compression, schema, categories/tags
- GEO AI: readiness analysis, key takeaways for quotes, meta opt for AI quotes, preview
- AEO AI: response-ready structure, FAQ, FAQ schema, preview
- AI Image Generation for cover and content images with professional styles (visual design, notifications, alerts)
- Internal links inserted scattered in content body, saved

Professional UI/UX with beautiful visuals, professional alerts.
inc/class-editor-seo.php
inc/meta/class-post-seo-meta.php
inc/modules/class-seo-engine.php
views/tab-seo-engine.php
assets/css/editor-seo.css
assets/js/editor-seo.js
assets/js/editor-seo-gutenberg.js
```
