# Phase 1 Migration Audit — fj-blocks

The input to the page-rebuild specs. It inventories every phase-1 page's sections,
dispositions each against our component catalog, records the cross-page repeats
(the abstraction evidence), captures the site-wide chrome, and proposes a rebuild
order. Phase 1 is the **marketing / content pages**; LearnDash and WooCommerce are
later phases.

## Method

Each old template (`wp-content/themes/fj/`) was inventoried section-by-section
against the rendered markup. Finding up front: **the old pages are ~100% static
hand-authored HTML** — no `WP_Query`, shortcodes, or plugin loops in the page
bodies (with the single exception of the LearnDash course catalog on Courses).
So phase 1 is overwhelmingly **pattern authoring**, not data wiring. The dynamic
pieces (LearnDash catalog, Forminator forms, WooCommerce, offer banners) are
already scoped to later phases.

## Disposition legend

- **catalog block** — an existing custom block: `hero`, `spotlight`, `bio`,
  `intro-section`, `cta-band`, `checklist-section`.
- **catalog starter** — an existing pattern: `intro-section`, `content-section`,
  `cta`, `day-cards`, `steps-cards`, `link-cards`, `pricing-cards`,
  `faq-accordion`, `spotlight`, `bio`, `checklist-section-starter`,
  `legal-page-starter`.
- **core** — plain core blocks (quote, pullquote, embed, table, image, columns).
- **new pattern** — needs a new FJ pattern (static, no propagation/logic/controls).
- **new block?** — a new-block candidate; flagged with justification, not built.
- **deferred** — LearnDash / WooCommerce / Forminator / offers; a later phase.

## Page → template map (from the DB)

| Page (slug) | Template | Phase |
|---|---|---|
| Home (`home`, front page) | `front-page.php` | 1 |
| About (`about`) | `page-about.php` | 1 |
| Field Trip (`field-trips`) | `page-events.php` | 1 |
| Earth Ceremony (`earth-ceremony`) | `page-earth-ceremony.php` | 1 |
| Live Group Classes (`live-group-classes`) | `page-group-classes.php` | 1 |
| Study With Feather (`study-with-feather`) | `page-private-classes.php` | 1 |
| Courses (`courses`) | `page-courses.php` | 1 (marketing) + 2 (catalog) |
| Contact (`contact`) | `page-contact.php` | 1 (layout) + 2 (form) |
| Privacy (`privacy`), Terms (`terms`) | default | 1 (legal-page-starter) |
| Blog (`blog`) | default | uses `home`/`archive` templates |
| Profile, My Account, Cart, Checkout, Shop | `page-account.php` / Woo | **2 (WooCommerce)** |

---

## Per-page inventories & dispositions

### Home — `front-page.php`

| # | Section | Disposition | Notes |
|---|---|---|---|
| 1 | Hero: eyebrow + h1 + lede + 2 CTA buttons | **catalog block** `hero` | Single-column; no bg image needed |
| 2 | Social-proof bar: 3 stats + dot dividers | **new pattern** (stats bar) | Small, recurs nowhere else — pattern |
| 3 | Offering routing cards: 4× icon + h3 + copy + button | **catalog starter** `link-cards` | Adapt to 4-up + icon; buttons instead of "Learn more" |
| 4 | "Not Sure Where to Begin" CTA band | **catalog block** `cta-band` | |
| 5 | Credibility / teacher split (portrait + eyebrow + h2 + copy + link) | **catalog block** `spotlight` or `bio` | "Meet Feather" shape — see cross-page |
| 6 | Single testimonial (blockquote + cite) | **core** quote + **new pattern** (see testimonials) | |
| 7 | Botanical divider (×several) | **new pattern** / block style | Decorative rule, site-wide — see repeats |
| 8 | Earth Ceremony offer banner | **deferred** | `get_template_part`, Forminator form |
| 9 | Pest Control offer banner | **deferred** | Forminator form |

### About — `page-about.php`

| # | Section | Disposition | Notes |
|---|---|---|---|
| 1 | Hero: two-col, text + portrait | **catalog block** `hero` (or `spotlight`) | Two-column hero variant |
| 2 | "Where It Started" text/image split | **catalog block** `spotlight` | |
| 3 | "Her Approach" image/text split + pull-quote | **catalog block** `spotlight` + **core** pullquote | |
| 4 | Credentials & Contributions: bio + credential pills + book | **catalog block** `bio` + **new pattern** (credential tags) | Tag-pill row is a small new component |
| 5 | "See how Feather teaches" CTA band | **catalog block** `cta-band` | |
| 6 | "What Students Say": 4× testimonial cards + peer note | **new pattern** (testimonials) | |
| 7 | "Beyond Teaching" text/image split | **catalog block** `spotlight` | |
| 8 | Invitation / "What's Next": 4× link cards + social row | **catalog starter** `link-cards` | |

### Field Trip / Events — `page-events.php`

| # | Section | Disposition | Notes |
|---|---|---|---|
| 1 | Hero: eyebrow + h1 + lede + meta + 2 CTAs | **catalog block** `hero` | CTA → Woo add-to-cart href (static) |
| 2 | "The Gap" text/image split + pull-quote | **catalog block** `spotlight` + **core** pullquote | |
| 3 | "The Experience" image-stack + text split | **catalog block** `spotlight` + **core** gallery/columns | Stacked-image column |
| 4 | "What's Inside": 4× day cards + CTA | **catalog starter** `day-cards` | One `is-included` variant |
| 5 | Testimonials: 4× cards | **new pattern** (testimonials) | |
| 6 | "Already Enrolled?" note band | **catalog block** `intro-section` / `cta-band` | Small centered note |
| 7 | FAQ: 10× accordion items | **catalog starter** `faq-accordion` | |
| 8 | Final CTA band (teal) + cross-sell | **catalog block** `cta-band` | Dark background variant |

### Earth Ceremony — `page-earth-ceremony.php`

| # | Section | Disposition | Notes |
|---|---|---|---|
| 1 | Hero + YouTube video | **catalog block** `hero` + **core** embed | External YouTube iframe |
| 2 | Intro / teaching blurb (pull-quote + lede) | **catalog block** `intro-section` + **core** pullquote | |
| 3 | "Go Deeper" CTA (eyebrow + h2 + lede + button) | **catalog block** `cta-band` | |
| 4 | Signature sign-off | **core** paragraph | Trivial |

*Simplest full page — a good smoke test.*

### Live Group Classes — `page-group-classes.php`

| # | Section | Disposition | Notes |
|---|---|---|---|
| 1 | Hero: eyebrow + h1 + lede + CTA + fine print | **catalog block** `hero` | |
| 2 | "Sound Familiar?" centered narrative | **catalog block** `intro-section` (+ body) | |
| 3 | Program Overview: text/image split + commitment list | **catalog block** `spotlight` + **new pattern** (label/value list) | |
| 4 | Curriculum: 8× cards + collapsible month agenda | **new pattern** (card grid) + **new pattern** (collapsible agenda) | Agenda uses a JS/`<details>` toggle — see new-block note |
| 5 | Sedona Field Trip split + credential tag + quote pair | **catalog block** `spotlight` | |
| 6 | Meet Feather teacher split | **catalog block** `bio` | Same shape as front/about/private |
| 7 | Student Stories: 4× testimonial cards | **new pattern** (testimonials) | |
| 8 | Tuition: 3× pricing cards (featured/badge) + perks | **catalog starter** `pricing-cards` | CTAs → Woo add-to-cart hrefs (static) |
| 9 | FAQ: 7× accordion | **catalog starter** `faq-accordion` | |
| 10 | Final CTA band | **catalog block** `cta-band` | |

### Study With Feather / Private Classes — `page-private-classes.php`

| # | Section | Disposition | Notes |
|---|---|---|---|
| 1 | Hero: eyebrow + h1 + lede + CTA + hero-quote | **catalog block** `hero` + **core** quote | CTA → external scheduling link |
| 2 | "Is This For You": 3× profile/icon cards | **new pattern** (icon cards) or `link-cards` variant | |
| 3 | "What You'll Get": 6× numbered cards | **catalog starter** `steps-cards` | Numbered 01–06 |
| 4 | "Why Private" split + comparison table | **catalog block** `spotlight` + **new pattern** (comparison table) | Table is unique to this page |
| 5 | Meet Feather teacher split | **catalog block** `bio` | |
| 6 | Testimonials: 3× cards | **new pattern** (testimonials) | |
| 7 | Details & Investment: label/value list + single price box | **new pattern** (label/value list) + **new pattern** (single price box) | |
| 8 | FAQ: 8× accordion | **catalog starter** `faq-accordion` | |
| 9 | Final CTA band (deep-teal) + blockquote | **catalog block** `cta-band` | |

### Courses — `page-courses.php`

| # | Section | Disposition | Notes |
|---|---|---|---|
| 1 | Hero: eyebrow + h1 + lede + 2 CTAs | **catalog block** `hero` | |
| 2 | Featured Course card (hard-coded, incl. price + Woo cart URL) | **new pattern** (featured course) | Static despite looking dynamic — **verify the hard-coded price and product link should stay static** |
| 3 | Course Catalog wrapper (eyebrow + h2 + lede) | **catalog block** `intro-section` | Static header above the listing |
| 4 | Course Catalog **rows** | **deferred** | LearnDash `sfwd-courses` WP_Query + ACF — phase 2 |
| 5 | Support band: eyebrow + h2 + 3× support cards | **catalog starter** `link-cards` (or new "info cards") | Static |

### Contact — `page-contact.php`

| # | Section | Disposition | Notes |
|---|---|---|---|
| 1 | Hero: eyebrow + h1 + intro | **catalog block** `intro-section` | |
| 2 | Contact form card | **deferred** (form) + **new pattern** (2-col layout) | Forminator form |
| 3 | Sidebar: "Before You Write" + "Quick Links" | **new pattern** (sidebar cards) | Static |

### Legal — Privacy, Terms (default template)

| Section | Disposition | Notes |
|---|---|---|
| Full legal prose page | **catalog starter** `legal-page-starter` | Paste existing copy into the starter |

### Deferred pages (phase 2)

- **Profile / My Account / Cart / Checkout / Shop** — WooCommerce (`page-account.php` is a bare `the_content()` shell hosting the Woo My Account block/shortcode). Whole-page deferred.
- **Blog** — served by the theme's `home`/`archive` templates; not a hand-built marketing page.

---

## Cross-page repeats (abstraction evidence)

The shapes that recur are the strongest candidates for reusable components. Ranked
by frequency:

| Shape | Appears on | Maps to |
|---|---|---|
| **Eyebrow + heading (+ lede) intro** | every page, every band | `intro-section` block |
| **Hero** (eyebrow + h1 + lede + CTA) | Home, About, Events, Group, Private, Courses | `hero` block |
| **Image + text split** | About ×4, Events ×2, Group ×3, Private ×2 | `spotlight` block |
| **"Meet Feather" teacher split** (same portrait → /about) | Home, Group, Private (About is the source) | `spotlight` block — near-identical, copy varies |
| **CTA band** (eyebrow + h2 + text + button) | every page, often ×2–3 | `cta-band` block |
| **FAQ accordion** | Events (10), Group (7), Private (8) | `faq-accordion` starter |
| **Testimonial cards** (quote + attribution grid) | About (4), Events (4), Group (4), Private (3) | **new pattern** — see below |
| **Card grid** (eyebrow + h2 → responsive cards) | Home routes (4), About links (4), Private profiles (3)/steps (6), Group curriculum (8), Courses support (3) | `link-cards` / `steps-cards` + **new** variants |
| **Day/step cards** | Events day-cards (4), Private numbered (6) | `day-cards` / `steps-cards` starters |
| **Pricing** | Group 3-tier (`pricing-cards`), Private single price box | `pricing-cards` starter + **new** single-price variant |
| **Botanical divider** | Home, About, Events, Ceremony, Group, Private | **new pattern** / separator block-style |
| **Label/value detail list** | Group `commitment-list`, Private `detail-item` | **new pattern** (one, unify the class drift) |

**Class-name drift to unify on the way in:** `s-card`/`t-card` (testimonials),
`commitment-item`/`detail-item` (detail lists) are the same shapes under different
names — consolidate to one component each.

---

## New pattern candidates (phase-1, static)

1. **Testimonials** — quote + attribution cards in a responsive grid (1-up feature
   + N-up). Highest-value new pattern: appears on 4 pages. *Possibly a block* — see
   below.
2. **Stat / social-proof bar** — N stats (number + label) with dividers. Home only.
3. **Feature/icon card grid** — icon + h3 + copy, no link (distinct from `link-cards`,
   which is image + link). Private "Is This For You", Courses support.
4. **Comparison table** — feature matrix (core `table` block, styled). Private only.
5. **Single price box** — one price + period + CTA (vs the 3-tier `pricing-cards`).
   Private only.
6. **Label/value detail list** — aligned label/value rows. Group + Private.
7. **Featured course card** — the hard-coded flagship promo on Courses (image +
   eyebrow + h2 + two feature lists + price + buttons).
8. **Credential-tag row** — small pill list. About; possibly inline in `bio`.
9. **Botanical divider** — decorative gradient rule. Site-wide; could be a
   `core/separator` style variation instead of a pattern.
10. **Contact layout** — 2-col form + sidebar cards (form itself deferred).
11. **Collapsible curriculum agenda** — month blocks revealed by a toggle. Group
    only; core `<details>` or a small interaction. See new-block note.

## New block candidates (flagged, not built)

Per the bar — **propagation, logic, or controls** justify a block; breakable
structure alone stays a pattern (WordPress 7.0):

- **Testimonials** — *the* borderline case. It repeats on 4 pages with a unified
  attribute set (quote, attribution, optional role). If the client will add/reorder
  testimonials often and we want a guided editing surface, a `testimonial` block
  (or an InnerBlocks "testimonials" wrapper) earns its keep. If they'll edit copy in
  place a few times, a pattern is enough. **Decision deferred to the rebuild** — the
  First-Light revision round is the evidence engine. Lean: start as a pattern; promote
  only if it churns.
- Everything else audited is static and one-shot or already a catalog component →
  **patterns**. No other new-block candidates in phase 1.

---

## Site-wide / chrome inventory

### Header (→ FSE `header` template part)

- **Logo** — two images (a dark and a light logo) swapped by CSS; linked home.
- **Desktop nav** — **hardcoded** 5 links (Courses, Live Group Classes, Study With
  Feather, Field Trip, About), *not* a registered menu; each item has a duplicated
  span for a hover animation.
- **Dual header** — a main `<header>` **and** a separate sticky duplicate
  (`sticky-header.php`), JS-toggled on scroll.
- **Per-page header variant/color** — passed per-template via `get_template_part($args)`
  (`header_variant`, `header_color`).
- **Mobile drawer** — off-canvas; reads the `mobile` / `mobile-about` registered
  menus (falls back to the 5 hardcoded links); cart + account buttons + social row.
- **Cart / account** — cart count reads `WC()->cart` live (load-time only; fragments
  disabled); account label flips Login/Account by auth state.
- **Search popup** — `partials/search-popup` (not in the first scan set; review).

### Footer (→ FSE `footer` template part)

- Hardcoded brand ("Feather Jones" / "Herbalist"), footer nav (Contact, Blog, About),
  inline social SVGs (FB/IG/YouTube), **medical/legal disclosure** (3 paragraphs),
  copyright (**a hardcoded year** — switch to dynamic), scroll-to-top button.
- **Offer popups** auto-injected here (earth-ceremony-teaching, pest-control) gated by
  a slug/Woo exclusion list → deferred.

### functions.php visitor-facing output

- Shortcode `[current_year]` (registered; footer currently ignores it — wire it in).
- Google Analytics (gtag) via `wp_head`, gated on a measurement-ID constant — our
  theme has the equivalent measurement-ID gate in `inc/analytics.php`.
- Font preload (Lato) in `header.php`; Stripe sandbox-overlay suppression; ACF
  `body_class` per page. No widget areas, no custom image sizes.

### Menus (DB)

- Only two **mobile** menus exist (`Mobile Menu - Top` = `mobile`, `Mobile Menu -
  Bottom` = `mobile-about`). No desktop/footer menu — those are hardcoded. **Decision:**
  build a real primary Navigation menu (from the 5 hardcoded links) + footer nav, or
  keep static.

### Media reuse

- The shared Feather portrait recurs on Home, About, Group, Private (the "Meet
  Feather" split). Logo images site-wide. These want
  seeding into the media library and referencing via `sb_image_block` where in patterns.

### Plugin coupling (all deferred)

- **WooCommerce** (deep): cart/account/checkout/shop, add-to-cart hrefs, product↔course
  links, checkout mini-cart. **LearnDash**: course catalog + course/product linking.
  **Forminator**: the contact form and the offer forms. **ACF**: body classes,
  product/course/event linking. **Stripe**: checkout.

---

## Proposed rebuild order

Simplest first (smoke test), ascending complexity, front page near-last (it composes
the most patterns). Header/footer parts come first since every page needs them.

0. **Header + footer template parts** — adapt the starter parts to FJ's nav, logo,
   footer disclosure/social. Ship a *simple* single sticky header first; defer the
   cart-count layout + dual-header nuance (those lean on Woo, phase 2).
1. **Legal pages** (Privacy, Terms) — `legal-page-starter`, paste copy. The Lumina
   smoke-test: proves activation, chrome, typography end-to-end on real prose.
2. **Earth Ceremony** — smallest full marketing page (hero + video + intro + CTA).
3. **Contact** — static 2-col layout; drop a Forminator placeholder (form = phase 2).
4. **About** — establishes `spotlight`, `bio`, the new **testimonials** pattern.
5. **Field Trip / Events** — exercises `day-cards`, `faq-accordion`, dark CTA band.
6. **Live Group Classes** — `pricing-cards`, curriculum grid + collapsible agenda.
7. **Study With Feather / Private** — comparison table, single price box, `steps-cards`.
8. **Courses** (marketing shell) — hero + featured-course pattern + support cards; the
   LearnDash catalog stays a phase-2 placeholder.
9. **Home** — composes hero, stats bar, link cards, splits, testimonials, CTAs; offer
   banners deferred. Last because it reuses everything proven above.

Deferred to later phases: LearnDash course catalog, WooCommerce (account/cart/checkout/
shop), Forminator forms, offer banners + popups.

---

## Decisions (settled with the client)

Component / approach:
1. **Testimonials** — build as a **pattern** first; promote to a block only if the
   First-Light revision round shows churn.
2. **Navigation** — migrate the hardcoded desktop + footer nav to **real Navigation
   menus** (editable), not static markup.
3. **Featured course card (Courses §2)** — keep **hard-coded / static** (price and
   product ID as-is); no dynamic wiring.
4. **Botanical divider** — build as a **new pattern**.
5. **"Meet Feather" split** — use the **`spotlight`** block everywhere it appears
   (it's a teaser to /about, not a full founder bio).
6. **Header** — ship a **simplified single header** for phase 1.
7. **Card grids** — **consolidate** the link / feature / profile / support card shapes
   into as few patterns as possible.

Chrome flags:
- **Dual header** (main + sticky duplicate) → **drop**; one header.
- **Per-page header variant/color args** → **drop**.
- **Hardcoded desktop + footer nav** → **real Navigation menus** (per #2).
- **Animated dual-span nav items** → **defer to the last steps** of phase 1.
- **Offer popups** (footer auto-inject) → **defer to the last steps**.
- **Font preload** (Lato) → **drop** (self-hosted via `fontFace`).
- **Copyright year** → rebuild as an **evergreen/dynamic year** (no annual upkeep).
- **Cart-count header layout** → **defer to the WooCommerce phase** (needs live cart
  state; likely the Mini-Cart block).
