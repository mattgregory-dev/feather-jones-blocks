# Starter Blocks

A Full Site Editing WordPress starter theme: a catalog of proven custom blocks
and starter patterns on a tokenized `theme.json`, cloned and seeded with
per-project design tokens. It is the standing asset behind a repeatable
copy → mockup → prototype → componentize → handoff pipeline; you clone it, reseed
the tokens, and build the site on components that already survived prior projects.

## Quick start

On a fresh clone:

```bash
npm install          # JS/CSS toolchain
composer install     # PHP dev tooling (phpcs + WordPress Coding Standards)
npm run build        # produce dist/ and build/ (both git-ignored)
```

Then, in WordPress:

1. **Activate** the theme.
2. **Seed the placeholder images** — upload `assets/images/placeholder-horizontal.webp`
   and `placeholder-vertical.webp` to the media library (or
   `wp media import assets/images/placeholder-*.webp`). The image-bearing starters
   resolve images by filename at render, so until the files exist as attachments
   they render empty.
3. **Reseed the tokens** in `theme.json` for the project — colors, fonts, type
   scale, spacing — before writing markup. Slugs are roles; you change the
   values, not the slugs.

For live development with hot-module reload, add `define( 'CUSTOM_WP_VITE_DEV', true );`
to `wp-config.php` and run `npm run dev` (Vite on `:5175`).

## What's inside

- **Six custom blocks** (`blocks/`) — hero, spotlight, bio, intro-section,
  cta-band, checklist-section. Dynamic blocks: structure in git, content in the
  database.
- **Starter patterns** (`patterns/`) — section starters and core-block starters
  (Day Cards, Steps Cards, Link Cards, Pricing Cards, FAQ Accordion, Legal Page
  Starter) under one "Starter Blocks" category.
- **Tokenized `theme.json`** — role-based color palette, fluid type scale,
  spacing rhythm, shadows, element and block defaults.
- **Two build pipelines** — Vite (`src/` → `dist/`) and `@wordpress/scripts`
  (`blocks/` → `build/`), plus phpcs, eslint, stylelint, and a block-grammar audit.

## Documentation

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) — the styling model, token
  contract, project structure, custom blocks, portable images, accessibility.
- [docs/BUILD.md](docs/BUILD.md) — the two pipelines, commands, first-run setup,
  the SCSS layer, the block-grammar audit.
- [docs/GOTCHAS.md](docs/GOTCHAS.md) — the non-obvious traps and their root
  causes (caching, editor sandboxes, rebuild-after-blocks, image seeding).
- [docs/PIPELINE.md](docs/PIPELINE.md) — the full project pipeline this theme is
  the Stage 0 standing asset for.
- [CLAUDE.md](CLAUDE.md) — the conventions index (namespaces, prefixes, token
  rules, commit discipline) read at the start of any work in this repo.
