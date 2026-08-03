# Build & tooling

Two independent build pipelines:

- **Vite + SCSS** compiles the theme's front-end bundle (`src/` → `dist/`): the
  escape-hatch stylesheet and JS behavior modules.
- **`@wordpress/scripts`** compiles the custom blocks (`blocks/` → `build/`): the
  JSX editor UI plus the `render.php`/`block.json` that WordPress registers.

Both outputs are git-ignored, and a source change is not live until *its*
pipeline runs. Design lives in `theme.json` tokens and block markup, not utility
classes.

## First-run setup

On a fresh clone:

```bash
npm install          # JS/CSS toolchain (Vite, wp-scripts, eslint, stylelint)
composer install     # PHP dev tooling (phpcs + WordPress Coding Standards)
npm run build        # produce dist/ and build/ (both git-ignored)
```

Then, in WordPress: **activate the theme**, and **seed the placeholder images**
— upload `assets/images/placeholder-horizontal.webp` and
`placeholder-vertical.webp` to the media library (admin upload, or
`wp media import assets/images/placeholder-*.webp`). The image-bearing starters
resolve their images by filename at render, so until the files exist as
attachments they render empty. This is the one manual seeding step a clone needs;
see [GOTCHAS.md](GOTCHAS.md#6-image-starters-render-empty-until-the-placeholders-are-seeded).

## Commands

```bash
npm run dev          # Vite dev server on :5175 (HMR) for src/ assets
npm run build        # full build: vite build + src/style.scss → dist/ + blocks → build/
npm run build:css    # compile src/style.scss → dist/assets/main.css only
npm run build:blocks # compile the custom blocks only (blocks/ → build/)
npm run start:blocks # watch-compile the custom blocks during development
npm run preview      # preview the Vite build

npm run lint         # eslint + stylelint + phpcs + block-grammar audit
npm run lint:css     # stylelint on the SCSS layer  (:fix to autofix)
npm run lint:js      # eslint on src/              (:fix to autofix)
npm run lint:php     # phpcs (WordPress standards)  (:fix runs phpcbf)
npm run lint:blocks  # block-grammar audit (patterns/, templates/, parts/)
```

The theme loads its compiled bundle from `dist/`. For live development with
hot-module reload, add the following to `wp-config.php` and run `npm run dev`:

```php
define( 'CUSTOM_WP_VITE_DEV', true );
```

With that flag on, `inc/enqueue.php` loads assets from the Vite dev server
(`http://localhost:5175`) instead of `dist/`; with it off (production), it
enqueues the built files with a `filemtime()` cache-buster.

## The SCSS layer (`src/style.scss`)

`src/style.scss` is an entry file that `@use`s one partial per feature from
`src/styles/`. The convention: **a partial rides with the feature it styles.**
When a block, style variation, or pattern lands, its stylesheet lands in the same
change and gets a matching `@use` line — `_faq.scss` arrives with the FAQ
Accordion pattern, `blocks/_hero.scss` with the hero block, and so on. There is
no monolithic stylesheet to keep in sync; the entry file's `@use` list *is* the
manifest of what the escape-hatch layer covers.

Keep every rule referencing `var(--wp--preset--*)` tokens — the SCSS layer styles
behavior and context that `theme.json` can't reach, but it never redefines a
design value. See [ARCHITECTURE.md](ARCHITECTURE.md#the-styling-model-read-this-first).

## Custom blocks (`build:blocks`)

The blocks in `blocks/` are compiled by `@wordpress/scripts` (webpack) into
`build/`, and `inc/blocks.php` registers each block from that **built** copy.
`npm run build` runs this as its final step; during block development,
`npm run start:blocks` watch-compiles.

Because WordPress loads the built copy, editing a file under `blocks/` — the JSX
in `edit.js`/`index.js`, or the `render.php`/`block.json` that get copied verbatim
into `build/` — has no effect until the block build runs. Rebuild after any
change under `blocks/`.

## Block-grammar audit (`lint:blocks`)

`scripts/block-audit.js` stack-parses every `<!-- wp:x -->` / `<!-- /wp:x -->`
comment across `patterns/`, `templates/`, and `parts/`, and exits non-zero on
any unclosed or mismatched block. Self-closing blocks (`… /-->`) are ignored.

**Why it exists:** a single missing block closer serializes as perfectly valid
HTML, so it's invisible until the editor loads the file and throws *"This block
contains unexpected or invalid content."* The audit surfaces it in the terminal
instead. It runs as part of `npm run lint` and is intentionally **not** a
pre-commit hook — it's run on demand.

A passing run reports, e.g.:

```
Audited 23 files — ALL BALANCED ✅
```

## Editor parity

`inc/enqueue.php` registers the compiled bundle as an editor style
(`add_editor_style`) so custom CSS (image radius, FAQ styling, button and list
variations) previews in the Site Editor the same as on the front end. See
[GOTCHAS.md](GOTCHAS.md#3-inline-svg-logos-and-the-custom-html-sandbox) for the
one context this deliberately does *not* reach (the Custom HTML block sandbox)
and how to work around it.
