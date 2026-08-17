# Starter Blocks — conventions

Read this before working in this repo. Starter Blocks is a Full Site Editing
starter theme meant to be cloned and reseeded per project (see
[docs/PIPELINE.md](docs/PIPELINE.md), Stage 0). The conventions below are what a
clone inherits and must not drift from.

## Permanent, shared identifiers

These are shared across **every** cloned project and are **append-only** — never
rename or repurpose an existing one; only add:

| Concern | Value |
|---|---|
| Block namespace | `starter-blocks/*` |
| Generated block class | `wp-block-starter-blocks-*` |
| PHP function prefix | `sb_*` |
| Text domain / `@package` | `starter-blocks` |
| Pattern category | slug `starter-blocks`, label "Starter Blocks" |

A rename silently breaks downstream references (inserted patterns forked into the
DB, inline classes, translations), which is why the rule is absolute.

## Design tokens

The `theme.json` palette is **role-based, not descriptive** — slugs name what a
color does, not what it is. A clone changes the *values*, never the *slugs*.

`base`, `contrast`, `contrast-2`, `surface-1`, `surface-2`, `surface-3`,
`surface-dark`, `border`, `muted`, `link`, `link-hover`, `error`.

- **Append-only**, same as the identifiers above.
- **Surfaces are luminance-ordered, lightest first** (`surface-1` lightest of the
  light set → `surface-3` darkest of it; `surface-dark` is the dark surface).
  New surfaces slot into that order.
- **Markup references tokens only.** All pattern/block markup uses preset slugs
  and `var(--wp--preset--*)` values — no literal hex, no raw px. This holds even
  in throwaway prototype markup, because prototype markup becomes production
  markup in this pipeline.
- **Alpha variants via `color-mix`, not new slugs.** For a token at reduced
  opacity, use `color-mix(in srgb, var(--wp--preset--color--X) N%, transparent)`
  with a plain-token fallback line above it — keeps the palette append-only
  instead of sprouting alpha-variant slugs. See
  [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

The font families are roles too (a serif heading stack, a sans body stack); swap
values, keep roles.

## Build & styling

- **Two pipelines, both git-ignored outputs:** Vite (`src/` → `dist/`) and
  `@wordpress/scripts` (`blocks/` → `build/`). WordPress registers blocks from
  `build/`, so **editing `blocks/` does nothing until `npm run build:blocks`.**
- **Styling priority:** `theme.json` → block markup/attributes → block style
  variations (`is-style-*`) → `src/style.scss` (escape hatch only: pseudo-
  elements, `:has()`, keyframes, JS-state classes). The SCSS layer always
  references tokens and never redefines one.
- **Units:** spacing (padding/margin/gap) uses a `--wp--preset--spacing--*`
  preset or rem, never raw px; px is reserved for borders, radii, shadows,
  transforms, and fixed media dimensions. Details in
  [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md#units-spacing-preset-vs-rem-vs-px).
- **A SCSS partial rides with its feature.** New block/variation/pattern → its
  stylesheet and a matching `@use` line land in the same change.
- Run `npm run lint` (eslint + stylelint + phpcs + block-grammar audit) before
  considering a change done.

## Blocks vs. patterns

A custom block is justified by **propagation, logic, or controls** — many
instances sharing a centrally-updated structure, `render.php` computation/
sanitization, or typed editor controls beyond core + block bindings. Breakable
structure **alone** is a pattern (use `templateLock` / block bindings for a safe
editing surface). See [docs/PIPELINE.md](docs/PIPELINE.md), Stage 4.

## Commit discipline

- **Conventional Commits, spec types only:** `feat`, `fix`, `refactor`, `chore`,
  `build`, `ci`, `perf`, `docs`. One idea per commit; the history is part of the
  deliverable.
- Commit **bodies only when the diff can't tell the story alone** (a non-obvious
  why or a gotcha) — no filler bodies.
- **No em dashes in marketing prose.** Docs, docblocks, and pattern descriptions
  may use them freely; visitor-facing copy may not.

## Editing page content (this deployment)

Deployment-specific (the fj Docker/WordPress environment), not a starter-blocks
convention. Page **content** lives in the database, edited in the block editor.
To read or write it programmatically:

- **Only ever go through `wp/sb-pull.php` and `wp/sb-push.php`. NEVER raw
  `wp post update` / `wp_update_post`.** Same wp-cli-in-Docker transport, but the
  scripts add load-bearing guards that raw commands skip:
  - **Stale-push guard** — `sb-push` aborts (no override) if the page changed in
    the DB since the last `sb-pull`, so it can't clobber the user's live editor
    work. Baseline auto-refreshes on push, so back-to-back solo edits are fine.
  - **Fidelity** — `sb-push` runs as an admin with `unfiltered_html`; raw wp-cli
    runs as user 0 with kses ACTIVE and silently strips `<iframe>`/`<script>`/
    inline SVG. It also `wp_slash`es for byte-exactness.
  - **Backup** — every push copies the current DB content to `.work/backups/`.
- **Flow:** `wp eval-file sb-pull.php <id> > wp/.work/<slug>.html` (arms the
  baseline) → edit the `.work` file → `wp eval-file sb-push.php <slug>`.
- **Re-pull before editing every time — even for a page I authored this session.**
  The user may be editing it in the block editor in parallel; the DB is the only
  source of truth. Do not regenerate a page from an in-context/local copy.
- Run all tooling through WSL / `docker compose` (see the project-root
  `AGENTS.md`); `npm run build:*` fails over the `\\wsl.localhost` UNC path, so
  build inside WSL.

## Where things live

- Conventions & mental model → this file, then [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).
- How to build/lint/run → [docs/BUILD.md](docs/BUILD.md).
- When something behaves wrong on a right-looking file → [docs/GOTCHAS.md](docs/GOTCHAS.md).
- The end-to-end project process → [docs/PIPELINE.md](docs/PIPELINE.md).
