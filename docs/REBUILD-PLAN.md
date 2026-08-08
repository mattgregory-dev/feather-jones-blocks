# Phase 1 Rebuild Plan — fj-blocks

Execution plan for the phase-1 marketing/content pages, derived from
`MIGRATION-AUDIT.md` and the settled decisions. Phase 1 excludes LearnDash,
WooCommerce, Forminator, and the offer banners.

## How phase 1 runs

- **git holds** the reusable machinery: the header/footer template parts, any
  block tweaks, and the patterns (shipped generic, with placeholder copy).
- **The database holds the pages.** Each real page is assembled in the editor by
  inserting the patterns and filling in Feather's actual copy — the same path the
  client will use to edit later. Page content is not a git artifact.
- **Production is the visual reference.** Every rebuilt page is diffed against the
  live site before moving on.
- **Rhythm:** per-commit staged review on the git work (chrome, patterns), as
  always. **First-Light checkpoints** after the smoke test, after the first full
  marketing page (About), and after Home.
- **Per-page verification gate** (the two that bit us on Lumina): (1) **exactly one
  `<h1>` per page** — the hero heading is h1, every other section heading is h2; and
  (2) the **flush-band behavior** on a trailing CTA band (`.sb-band:last-child`
  pulling into the footer). Most FJ pages end in a CTA band, so this gets exercised
  nearly everywhere — verify it holds in FJ's DOM nesting the first time, then
  spot-check.

> **Method (confirmed):** patterns ship **generic (placeholder copy)**; the real
> Feather copy enters each page's `post_content` through the editor, exactly once.
> This is the pipeline in its mature form — prose is locked on production, so
> patterns are born generic and there's no copy-in-pattern divergence to unwind
> later (that was Lumina's Stage-3 grind; the lesson lives in GOTCHAS).

## Component work (what git gets)

### Chrome — build first, everything needs it

- **Header** (simplified single header): logo, a **real primary Navigation menu**
  built from the 5 links (Courses, Live Group Classes, Study With Feather, Field
  Trip, About). The Navigation block gives responsive/hamburger behavior natively.
  Drop: dual/sticky-duplicate header, per-page variant args, font preload, the
  animated dual-span nav (deferred), the cart-count layout (Woo phase). Account/Cart:
  **static links** to `/cart` and `/my-account` (the Woo pages exist in the DB) —
  only the *live* behavior (cart count, login/account label flip) is deferred.
- **Footer**: brand, footer nav, social SVGs, the **medical/legal disclosure**
  (keep — it's a real compliance element), scroll-to-top (we have `scroll-top`),
  and an **evergreen copyright year**. Drop: offer popups (deferred).
- **Evergreen year**: register a small `sb_`-prefixed `[current_year]` shortcode in
  `inc/` and place it via a Shortcode block in the footer — lighter than a block,
  and it wires in what the old theme registered but never used. **Promote to
  starter-blocks at phase end** (a dynamic copyright year is a universal utility).

### Catalog reuse — no new code, just adapt/insert

`intro-section`, `spotlight` (incl. the "Meet Feather" teaser), `cta-band`, `hero`,
`faq-accordion`, `day-cards`, `steps-cards`, `link-cards`, `pricing-cards`,
`legal-page-starter`.

### New patterns to build (static; built when first needed, reused after)

1. **testimonials** — quote + attribution cards (1-up feature + N-up grid). First
   use: About. *Pattern first* (promote to block only if it churns).
2. **stat / social-proof bar** — N stats + dividers. Home.
3. **feature-cards** — the consolidated info-card grid (icon *or* plain, no link):
   covers Private "profile" cards, Courses "support" cards, Group "curriculum" cards.
4. **comparison table** — styled `core/table`. Private.
5. **single price box** — one price + period + CTA. Private.
6. **label/value detail list** — aligned rows (unifies Group `commitment-list` +
   Private `detail-item`).
7. **featured course card** — the hard-coded flagship promo (static). Courses.
8. **botanical divider** — decorative gradient rule. Site-wide.
9. **contact layout** — 2-col form + sidebar cards (form itself is a phase-2
   placeholder). Contact.
10. **collapsible curriculum agenda** — month blocks behind a `core/details`
    toggle. Group.

### Card-grid consolidation (decision #7)

| Old shapes | Rebuild as |
|---|---|
| Home routes, About offerings/"What's Next", Courses support-with-links | `link-cards` (has link/button) |
| Private profile cards, Courses support cards, Group curriculum cards | **`feature-cards`** (new — icon/plain, no link) |
| Private "What You'll Get" numbered | `steps-cards` |
| Events day cards | `day-cards` |

## Build sequence

Each step is commit-sized on the git side (new pattern = `feat:`), then the page is
assembled + copy-filled in the editor and diffed against production.

| # | Step | New git work | Reuses |
|---|---|---|---|
| 0 | **Chrome** — header + footer parts, evergreen year | header/footer parts, `[current_year]` | scroll-top |
| 1 | **Legal** (Privacy, Terms) — *smoke test* | — | `legal-page-starter`, `intro-section` |
| 2 | **Earth Ceremony** | — | `hero`, `intro-section`, `cta-band`, core embed |
| 3 | **Contact** (form = phase-2 placeholder) | `contact-layout` | `intro-section` |
| 4 | **About** — ⚑ First-Light checkpoint | `testimonials`, credential-pills | `hero`, `spotlight`, `cta-band`, `link-cards` |
| 5 | **Events / Field Trip** | — | `hero`, `spotlight`, `day-cards`, `faq-accordion`, `cta-band`, `testimonials` |
| 6 | **Live Group Classes** | `feature-cards`, `label-value`, `curriculum-agenda`, `botanical-divider` | `hero`, `intro-section`, `spotlight`, `pricing-cards`, `faq-accordion`, `cta-band`, `testimonials` |
| 7 | **Study With Feather / Private** | `comparison-table`, `single-price-box` | `hero`, `spotlight`, `steps-cards`, `feature-cards`, `label-value`, `faq-accordion`, `cta-band`, `testimonials` |
| 8 | **Courses** (marketing shell) | `featured-course-card` | `hero`, `intro-section`, `feature-cards`; LearnDash catalog = phase-2 placeholder |
| 9 | **Home** — ⚑ First-Light checkpoint | `stat-bar` | everything above; offer banners = phase-2 placeholder |

## End-of-phase passes

After all pages are built and verified against production, two passes close out
phase 1 — in this order:

1. **Mobile optimization** — a dedicated responsive sweep across the chrome and
   every page (breakpoints, nav overlay, stacking, spacing, tap targets). The
   header and patterns are built desktop-first during the rebuild; this pass tunes
   them for small screens in one focused go, rather than piecemeal per page.
2. **Final touches** — animations and micro-interactions layered on last (e.g. the
   nav-pill hover, reveal-on-scroll), so they never complicate the structural work.
   Elements are built with their hooks ready (the nav pills already carry their
   padding/background for a hover state).

## Media

Seed the real production images (Feather portrait, farm/approach photos, book
cover, logos) into the media library and reference them
via `sb_image_block` in patterns / the block image controls. First task before the
image-bearing pages (About onward). I'll confirm which are already in the snapshot's
uploads vs. need importing from the old theme.

## Deferred to later phases

LearnDash course catalog · WooCommerce (account/cart/checkout/shop, add-to-cart) ·
Forminator forms (contact + offers) · offer banners + popups · animated nav ·
cart-count header behavior.

## Settled decisions

1. **Page-content method** — generic patterns, real copy in the editor. Confirmed
   (see the Method note above).
2. **Header Cart/Account** — **static links** to `/cart` and `/my-account`; don't
   omit. Only the live behavior (cart count, login/account label flip) is deferred.
3. **Evergreen year** — `[current_year]` **shortcode** via the Shortcode block;
   promote to starter-blocks at phase end.
4. **`feature-cards` consolidation** — **merge** on matching anatomy (icon-or-plain
   + heading + copy, **no link**); keep `link-cards` distinct because the link *is*
   the anatomy difference. **Boundary:** if the merged pattern starts needing many
   optional knobs to serve all three uses, that's pattern bloat — the cure is two
   patterns, not more options. Merge on matching anatomy; split the moment the
   anatomy diverges.
5. **Legal copy** — enter the **real** Privacy/Terms text immediately, no lorem
   stage. Real prose end-to-end is the point of the smoke test, and entering it now
   *is* the migration for those pages.
6. **Per-page verification** — the explicit gate (one `<h1>`; trailing-band
   flush-to-footer) is under "How phase 1 runs" above.
