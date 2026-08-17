// Fonts are self-hosted and registered in theme.json via `fontFace`
// (assets/fonts/*.woff2). WordPress loads them on the front end AND in the
// editor canvas, and surfaces them in the Font Library, so no JS font imports
// belong here.

// Dev-only: pull SCSS through Vite so the escape-hatch styles hot-reload.
// The production build compiles src/style.scss separately (see package.json).
if (import.meta.env.DEV) {
  import("./style.scss");
}

// Interactions that FSE handles natively (responsive nav, etc.) live in core
// blocks, not here. Add custom JS below only for genuinely bespoke behavior,
// ideally via the WordPress Interactivity API. JS behavior modules live in
// src/scripts/ (mirrors src/styles/ for SCSS), imported here as features land.

// Back-to-top button (fixed-position chrome; markup in inc/scroll-top.php).
import "./scripts/scroll-top.js";

// Custom mobile menu drawer (markup in parts/header.html; styles in _header.scss).
import "./scripts/mobile-menu.js";
