# Block Theme Project Pipeline

From copy to mockup to custom block theme to client handoff. The organizing principle: content certainty before structure certainty before component certainty. Each stage locks one layer, and each revision round happens at that stage's cost level. Cheap decisions early, expensive decisions late, and nothing expensive gets built until the evidence says it's needed.

---

## Stage 0: Standing Assets: the starter-blocks Theme (once, then maintained)

The pipeline compounds across projects through the starter-blocks theme. This is a practice, not a process that restarts at zero each time.

**starter-blocks contains:**
- The section catalog implemented as starter patterns: hero, spotlight, bio, intro section, content section, CTA band, checklist section, card grids, FAQ accordion, pricing cards
- The proven custom blocks (hero, spotlight, bio, intro-section, cta-band, checklist-section), carried as-is
- A templated `theme.json`: token slots for colors, fonts, sizes, and spacing, ready to seed per project
- Thin templates: `page.html` with `wp:post-content`, `single.html`, archive/home, search, 404
- Header and footer template parts
- The image conventions: placeholder image asset, the filename-to-attachment-ID resolver, and the insertion-time resolution pattern in starters
- Build tooling: Vite for Sass and site JS, `@wordpress/scripts` for block editor JS
- Git conventions: Conventional Commits, spec types only

**Maintenance rule:** when a project produces a new proven block or starter, promote it to starter-blocks after the project ships. starter-blocks grows only from evidence, never from speculation.

---

## Stage 1: Copy and Mockup

**Goal:** lock the prose and the layout skeleton. This is the cheapest stage, so it absorbs unlimited revision rounds.

1. Write the copy against the section catalog. The catalog defines the slots; the copy is written to fit them.
2. Proof the copy line by line. Copy quality gates everything downstream because prose length and rhythm drive layout.
3. Build the mockup: black and white wireframe, catalog sections, real copy. No images, no colors, no fonts, no effects. Those are deliberately unset.
4. Revise as many rounds as needed. Every round here is nearly free.
5. Get approval in writing. An email saying "I approve the design" is enough. This approval covers prose and layout.

**What is locked after this stage:** section inventory per page, prose, heading structure, layout skeleton.
**What is still open:** images, colors, fonts, design treatments, and any section the client will rethink once they see color.

---

## Stage 2: Prototype in WordPress

**Goal:** the approved mockup becomes a real WordPress site built from static patterns, fast, in git, with no custom development for novel sections yet.

1. **Clone starter-blocks.** Rename, strip anything project-irrelevant.
2. **Seed `theme.json` first.** Colors (brand colors if the client has them), fonts, type scale, spacing scale. Best available information now; refined later. Tokens exist before any markup gets written.
3. **Hard rule: markup references tokens only.** All pattern markup uses preset slugs and `var(--wp--preset--*)` values. No literal hex, no raw px sizes, even in throwaway markup. Prototype markup becomes production markup in this pipeline, so literals written at prototype speed become debt paid later. Enforce it in the AI prompt.
4. **Build the pages as static patterns.** One pattern per page section, or one giant pattern per page, composed in thin templates or stamped onto pages. Catalog sections adapt from the starter-blocks starters; novel sections get AI-assisted block markup generated from the mockup, reviewed and tweaked by hand. Nobody writes serialized block markup cold.
5. **Two-tier component rule:**
   - **Catalog blocks ship on day one.** They came with starter-blocks, they survived prior projects and client contact, and deferring them buys nothing. Prototype directly with them.
   - **Novel sections stay as static pattern markup.** Anything this design invents follows defer-until-evidence. No new custom block gets built before the client revision round proves the section's shape is stable.
6. **Hang real pages and posts.** Create the actual WordPress pages. Create sample blog posts: lorem ipsum for most, plus one real-shaped post with actual prose, because lorem tests layout but hides prose rhythm (heading cadence, image flow through paragraphs).
7. **Images: fast and approximate.** Free libraries, thumbnails, watermarked comps. Get in the ballpark, do not hunt for perfect. Seed the placeholder image and use the resolver conventions so every image reference is portable across environments from day one.

**What is locked after this stage:** the site exists, tokenized, in git, with all pages built.
**What is still open:** final images, design refinements, and any layout change the client requests at first light.

---

## Stage 3: First Light Revision Round

**Goal:** the client sees the real site with color, fonts, and approximate images for the first time, and spends their one serious revision round.

1. Present the prototype as a working site.
2. **One round of revisions.** This is the boundary that protects the project from scope creep, and it is enforced on the client's behalf as much as the developer's. Copy tweaks, image direction, section moves, layout adjustments: all legitimate here, all increasingly expensive after here. The client will live with what they approve, so it is in their interest to spend this round well.
3. **Read the round as data.** The revision round does two jobs at once. It collects design changes, and it reveals structural truth. A section that survives the round with its repeats intact is a confirmed abstraction candidate. A section that comes back fragmented ("make this one different from that one") stays a pattern. The client's own edits sort the block candidates at no cost.
4. Apply revisions. Get approval in writing. This approval covers the pixels.

---

## Stage 4: Componentization (blocks first, then patterns)

**Goal:** convert the approved prototype's novel sections into their permanent form. This happens after approval because the approval round supplied the evidence.

1. **Audit every novel section against the block-creation bar.** A custom block is justified by one of three things: **propagation** (many instances share a structure that must update centrally, which only code can do), **logic** (`render.php` needs to compute, sanitize, or pull dynamic data), or **controls** (the section needs typed editor controls beyond what core blocks and block bindings offer). If none of the three holds, it stays a **starter pattern** — even when the structure is breakable. As of WordPress 7.0, breakability alone no longer justifies a block: `templateLock`, block bindings, and section styling let a pattern give the client a safe, guided editing surface without custom code. Repeats-with-unified-attributes is the strongest block signal; appears-once is always a pattern.
2. **Build the blocks.** Native blocks by default (`block.json`, `render.php`, `@wordpress/scripts` for editor JS). Established conventions carry over: typed titles, h2 defaults with explicit h1 opt-in, empty attributes render nothing, `supports.color: false` with deliberate token-based exceptions, InnerBlocks for flowing content, `templateLock` where the structure needs a cage.
3. **Rebuild the prototype patterns on the new blocks.** The pages still render through patterns at this point, so every rebuild diffs against the approved prototype as you go. Output must not change.
4. **Commit discipline:** one `build:` for any new plumbing, then `feat:` (add block) paired with `refactor:` (rebuild patterns on block), one pair per block. Deletions trail their migrations.

This stage is not a hidden cost of block-based work. It is the deliverable's defining feature: the moment the site becomes client-editable without changing a pixel. The conversion is fast precisely because the prototype was already block markup.

---

## Stage 5: Content Migration

**Goal:** content moves from pattern files into `post_content`. Pages become editable; git stops holding prose.

1. **Migrate via the inserter, not paste.** Open each page, insert sections top to bottom with the + button from the Patterns tab, fill in production copy. This is deliberately the same path the client will use, so the migration doubles as QA of the pattern registry, the previews, the block sidebars, and the whole editing surface. Paste from the Code editor only for long prose (legal pages) or content whose only source is a dead pattern.
2. **Images resolve at insertion** through the starter patterns' resolver lines. Anything hand-placed gets picked from the media library through the block's own controls, never by hand-editing IDs in markup.
3. **Verify every page against its approved prototype state.** The client approved pixels; the migration must reproduce those pixels from different machinery. Per-page visual diff before moving on. This step is named because skipping it is how regressions slip in after approval, the worst possible timing.
4. Point pages at the thin default template. Confirm each page renders once, correctly, and check the Site Editor for stale database-forked template overrides (file deletion does not clear them).

---

## Stage 6: Teardown and Starters

**Goal:** the theme reaches its lean end state. Structure in git, content in the database, patterns as a browsable starter menu.

1. **Delete the page-definition patterns and per-page templates.** Their content lives in `post_content` now. Commit deletions as their own steps, trailing the migrations they depend on.
2. **Consolidate to starters.** One generic starter per section type: placeholder copy, maximal configuration (every capability visible, because deleting is discoverable and adding is not), named for the menu, one theme category. The Patterns tab becomes the client's section vocabulary.
3. **Promote to starter-blocks.** Any new block or starter this project proved goes back into Stage 0's standing asset.
4. Final cleanup pass: stale docblocks, dead scaffolds, shared markup extracted to template parts where two templates call it.

---

## Stage 7: Handoff

1. Walk the client through editing: click into text, Enter for new list items, the + button and the Patterns tab for new sections, duplicating pages as the preferred starting point for new ones.
2. The walkthrough script comes free from Stage 5: migrating through the editor surfaced everything worth teaching.
3. Deliver the approval trail, the git history, and the editing guide.

---

## Revision Policy Summary

| Stage | What the client reviews | Rounds | Cost per round |
|---|---|---|---|
| Mockup | Prose + layout skeleton | Unlimited | Near zero |
| Prototype first light | Real site, colors, approximate images | One | Moderate, and it doubles as abstraction evidence |
| Post-migration | Nothing new (pixel-identical) | None | n/a |
| Handoff | Editing walkthrough | n/a | n/a |

---

## Git Discipline

- Conventional Commits, spec types only: `feat`, `fix`, `refactor`, `chore`, `build`, `ci`, `perf`, `docs`
- `feat:` = visitor-facing output is new or improved. `fix:` = broken output corrected. `refactor:` = identical output, internal restructure.
- One idea per commit. `build:` before `feat:`. `feat:` paired with its `refactor:`. Deletions trail migrations. Feat before chore so the tree never lacks a capability.
- Commit bodies only when the diff cannot tell the story alone (example: template deletions whose cause lives in the database).
- Work on a branch per stage or per project phase; keep granular commits on merge. The history is part of the deliverable.

---

## Principles Index

- Content certainty before structure certainty before component certainty.
- Abstract on evidence, never on speculation. The client revision round is the evidence engine.
- Catalog components are pre-verified: ship them day one. Novel components wait for proof.
- Breakability alone is a pattern, not a block. The block bar is propagation, logic, or controls.
- Tokens from day one; markup never carries literals.
- Defaults are the safety; starters teach the capabilities.
- Blocks propagate through code. Patterns propagate through insertion. Synced patterns propagate through reference, and are reserved for verbatim client-owned content.
- Templates answer "what kind of page is this," never "which page is this."
- The stamp workflow (insert, then own) is the permanent editor path; bulk paste is migration scaffolding only.
- Every stage ends with an approval or a verification gate, and the expensive stages inherit certainty from the cheap ones.
