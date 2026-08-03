# Gotchas & root-cause notes

An engineering notebook of the non-obvious problems that shaped this theme, and
*why* each fix is what it is. These are hard-won: most cost real hours before the
cause was found, and they are kept here so they cost you minutes instead. Block
themes edit as flat files, but a lot of what WordPress derives from those files
is cached in the database or rendered in surprising contexts — so "the file is
right but the page is wrong" happens often, and the cause is rarely the file.

---

## 1. WordPress caching — "my change didn't take"

When behavior contradicts the file on disk, suspect the DB/transient layer
first. This is the single most common time-sink in block-theme work.

- **New/edited patterns render blank or stale.** Registered patterns are cached
  in transients. After adding or changing a `patterns/*.php` file, flush them:
  `wp transient delete --all` (or SQL:
  `DELETE FROM wp_options WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%';`).
  Adding or **removing** a pattern file also needs the theme's cached file list
  cleared before the new set is re-scanned — `wp_clean_themes_cache()` is the
  targeted call (it drops the `wp_theme_files_patterns-*` transient). The re-scan
  happens on the *next* request, so clear, then reload to verify.
- **`theme.json` changes don't show up.** The Site Editor writes user
  customizations to a `wp_global_styles` custom post, and **that DB copy
  overrides the file**. Reset via **Styles → Revert to theme defaults**, or
  clear the `wp_global_styles` post for the theme. The same shadowing applies to
  templates and template parts edited in the Site Editor: once you edit one in
  the Site Editor it forks into the database, and **deleting the file does not
  clear the fork** — you must reset it from the editor (Template → Clear
  customizations).
- **A new page/slug 404s, or a template won't resolve.** Rewrite rules are
  cached — `wp rewrite flush`.
- **Full reset:** `wp transient delete --all && wp rewrite flush`, plus a Global
  Styles revert if `theme.json` is involved.

> **Not a cache:** if a font size looks wrong (core's 13/20/36/42 instead of the
> theme's ladder), that's `settings.typography.defaultFontSizes`, which defaults
> to `true` and merges core's sizes on top of yours. This theme sets it to
> `false`.

---

## 2. `WP_DEBUG_DISPLAY` breaks the media library

**Symptom:** every item in the Media Library grid stuck showing "uploading…",
even long-uploaded images whose thumbnails clearly existed.

**Root cause:** the media grid loads its items via
`admin-ajax.php?action=query-attachments`, which must return clean JSON. With
`WP_DEBUG_DISPLAY = true`, PHP notices/deprecations are *printed into the
output* — including AJAX responses. A single core deprecation firing on admin
requests (`preg_replace(): Passing null …` in `wp-admin/includes/plugin.php`)
corrupted the JSON, the grid's JS couldn't parse it, and every item stayed
frozen in its initial "uploading…" placeholder.

**Fix:** the standard dev configuration — keep logging, stop *printing*:

```php
define( 'WP_DEBUG',         true  );
define( 'WP_DEBUG_LOG',     true  );
define( 'WP_DEBUG_DISPLAY', false );  // ← the fix
```

Any notice + `WP_DEBUG_DISPLAY` on will do this, and it can break the block
editor the same way. Errors still go to `debug.log`; they just stop poisoning
responses.

---

## 3. Inline-SVG logos and the Custom HTML sandbox

The starter ships no inline-SVG logo, but adding one is a common first
customization — and it has a trap worth knowing before you hit it.

**Symptom:** an inline `<svg fill="currentColor">` logo placed in a **Custom
HTML** block renders the wrong color (or invisible) in the **editor**, while the
front end is fine — and several "obvious" fixes do nothing.

**Root cause:** the block editor previews each Custom HTML block in an isolated
`<iframe srcdoc>` containing **only that block's own markup** — no
`.site-header` / `.site-footer` ancestor, and not the front-end bundle unless
it's a registered editor style. So any selector that leaned on an ancestor could
never match in the sandbox.

**What works:**

- **Style the logo with direct `.site-logo` selectors, never ancestor
  selectors.** Set the base color on `.site-logo` itself; give a footer variant
  its own class (e.g. `.site-logo--footer`) rather than selecting it through
  `.site-footer`.
- **Put the logo color in `theme.json` `styles.css`, not the SCSS bundle.**
  theme.json global styles load in *both* the editor and the front end, and
  aren't run through the CSS minifier — which can rewrite a nested `&` rule down
  to a bare, over-broad selector that then takes over once the editor re-scopes
  everything under `.editor-styles-wrapper`.
- **Gate editor-only tweaks on the sandbox body.** The sandbox `<body>` carries
  `data-resizable-iframe-connected`; the front-end body doesn't. So
  `body[data-resizable-iframe-connected]:has(a.site-logo--footer){…}` fixes the
  preview **in the editor only**, with zero effect on the live site.
- `add_editor_style( 'dist/assets/main.css' )` loads the bundle into the editor
  canvas so most custom CSS previews correctly — but it does **not** reach the
  isolated Custom HTML sandbox, which is the other reason logo color belongs in
  theme.json.

**Takeaway:** an inline-SVG-in-Custom-HTML element is styled by ancestor context
on the front end but rendered context-free in a sandboxed iframe in the editor.
Anything depending on `.site-header`/`.site-footer` — or on the bundle being
present — silently breaks in the editor only. Keep such an element's color in
theme.json and its layout (sizing/`display`) in the SCSS layer.

---

## 4. Editor-valid vs. dynamic image markup

`core/image` blocks validate their saved HTML against the block's expected
output. Rendering an image with `wp_get_attachment_image()` (which injects
`width`/`height`/`class="attachment-full size-full"`/`srcset`/`decoding`)
triggers *"unexpected or invalid content"*, because that's not what the block
expects.

The [portable image helper](ARCHITECTURE.md#images-portable-deploy-safe-references)
sidesteps this by emitting **canonical** block markup — `src` + `alt` +
`wp-image-<id>` class, with the resolved ID written into the block comment too —
and letting WordPress core add `srcset`/`sizes` at render. Portable across
installs *and* editor-consistent.

---

## 5. Editing `blocks/` does nothing until you rebuild

**Symptom:** a change to a custom block's `edit.js`, `render.php`, or `block.json`
has no effect — the editor and front end keep showing the old behavior.

**Root cause:** WordPress registers each block from `build/<name>`, not
`blocks/<name>` (`inc/blocks.php`). `@wordpress/scripts` compiles the JSX and
**copies** `render.php`/`block.json` into `build/`. Until that build runs, the
registered copy is stale — so source edits are invisible.

**Fix:** `npm run build:blocks` (or `npm run start:blocks` to watch) after any
edit under `blocks/`. Note the two independent pipelines: `src/` → `dist/` (Vite)
and `blocks/` → `build/` (wp-scripts); both outputs are git-ignored, and a source
change is not live until *its* pipeline runs. The full `npm run build` runs Vite,
the SCSS compile, and the block build in sequence.

---

## 6. Image starters render empty until the placeholders are seeded

**Symptom:** the image-bearing starters (Spotlight, Bio, Hero, Link Cards) insert
with no image on a fresh clone, even though `assets/images/` clearly contains the
placeholder files.

**Root cause:** the starters resolve images **by filename** at render time
(`sb_image_block( 'placeholder-horizontal.webp', … )`), which looks up an
attachment ID in the current install's media library. The files shipping in
`assets/images/` are theme assets, **not** media-library attachments — so until
they're uploaded, the lookup returns 0 and the block renders empty (by design; it
self-heals the moment the file exists).

**Fix:** upload `assets/images/placeholder-horizontal.webp` and
`placeholder-vertical.webp` to the media library once per environment (via the
admin, or `wp media import`). This is the one manual seeding step a fresh clone
needs; see [BUILD.md](BUILD.md#first-run-setup).
