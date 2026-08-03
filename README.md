# FJ Blocks

A bespoke **Full Site Editing** WordPress theme for Feather Jones,
bringing online courses, classes, events, and a store together under one
block-native design system. Built the modern way — **design tokens in `theme.json`, page
sections as block patterns, pages as block templates** — on top of the
[starter-blocks](https://github.com/mattgregory-dev/starter-blocks) component
catalog.

![FJ Blocks](assets/images/feather-jones-blocks.jpg)

> Bespoke client theme, designed and built end-to-end on the starter-blocks
> foundation. Reference available on request.

---

## What makes it worth a look

- **`theme.json` (v3) as the single source of truth.** The palette, fluid type
  scale, spacing rhythm, and element/block styles all live in `theme.json` and
  emit `--wp--preset--*` tokens. Custom CSS is a deliberate, documented *escape
  hatch* — not the primary layer.
- **True Full Site Editing.** Block templates + template parts render real
  `<header>`/`<main>`/`<footer>` landmarks and stay editable in the Site Editor.
- **Custom dynamic blocks.** Section blocks (`hero`, `spotlight`, `bio`,
  `intro-section`, `cta-band`, `checklist-section`) built with
  `@wordpress/scripts` — editor UI in `edit.js`, front-end markup in `render.php`,
  content in the database. Source in `blocks/`, compiled to `build/`.
- **Reusable starter patterns.** Section starters in `patterns/`, auto-registered
  in the inserter, that authors insert and edit — page content lives in the
  database, not hardcoded in templates.
- **LearnDash LMS.** Courses, lessons, quizzes, and topics, styled to the theme
  with lean custom templates so LearnDash does the heavy lifting.
- **WooCommerce store.** Products, cart, and checkout, built on WooCommerce's own
  blocks and templates so the shop stays first-class and upgrade-safe.
- **A hand-built Vite + SCSS pipeline** — autoprefixed, minified, HMR in dev.
- **Tooling that catches invisible bugs.** stylelint plus a custom
  **block-grammar linter** that stack-parses block comments to catch the
  unclosed-block error *before* the editor throws "unexpected or invalid
  content."
- **Deploy-safe images.** A filename → attachment-ID resolver so image
  references survive dev → production (where IDs differ), while still emitting
  responsive `srcset`.
- **Accessibility by default.** Responsive images, skip links, visible focus
  states, and reduced-motion support.

## Tech stack

WordPress (Full Site Editing) · `theme.json` v3 · custom blocks
(`@wordpress/scripts`) · LearnDash · WooCommerce · PHP · SCSS · Vite ·
JavaScript (ES modules) · stylelint / eslint / phpcs

## Quick start

```bash
npm install
npm run build        # compile assets → dist/ and blocks → build/
npm run lint         # eslint + stylelint + phpcs + block-grammar audit
```

For live development with hot reload, set `define( 'CUSTOM_WP_VITE_DEV', true );`
in `wp-config.php` and run `npm run dev`. Full details in
[docs/BUILD.md](docs/BUILD.md).

## Documentation

The engineering detail lives in [`docs/`](docs/):

- **[Architecture](docs/ARCHITECTURE.md)** — the styling model and layer
  priority, project structure, the portable image helper, fonts, and
  accessibility.
- **[Build & tooling](docs/BUILD.md)** — the Vite/SCSS pipeline, lint commands,
  and the block-grammar audit.
- **[Gotchas & root-cause notes](docs/GOTCHAS.md)** — an engineering notebook of
  the non-obvious WordPress problems solved along the way (caching layers, the
  media-grid/`WP_DEBUG_DISPLAY` trap, the inline-SVG-logo editor sandbox, and
  more).

## Credits

Design & development by Matthew Gregory. Built for Feather Jones on the
starter-blocks foundation.
