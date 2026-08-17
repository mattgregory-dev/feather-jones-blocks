// Custom mobile menu drawer.
// Progressive enhancement over the header markup (parts/header.html): the
// hamburger `.site-header__toggle` opens an off-canvas `.mobile-drawer` panel
// (branded header + the Navigation block + Cart/Login/socials footer) over a
// `.mobile-drawer__backdrop`. Styling + slide transition: _header.scss. The
// panel's dialog semantics (id/role/aria) are set here so the static markup
// stays a plain Group block that's still editable in the Site Editor.

function initMobileMenu() {
  const toggle = document.querySelector('.site-header__toggle');
  const panel = document.querySelector('.mobile-drawer');
  const backdrop = document.querySelector('.mobile-drawer__backdrop');
  if (!toggle || !panel || !backdrop) return;

  // Dialog semantics (added at runtime so the markup stays a bare Group block).
  panel.id = 'mobile-drawer';
  panel.setAttribute('role', 'dialog');
  panel.setAttribute('aria-modal', 'true');
  panel.setAttribute('aria-label', 'Site menu');
  panel.setAttribute('aria-hidden', 'true');

  let lastFocused = null;

  const isOpen = () => panel.classList.contains('is-open');

  const open = () => {
    if (isOpen()) return;
    lastFocused = document.activeElement;
    backdrop.hidden = false;
    // Next frame so the transition runs from the hidden state.
    requestAnimationFrame(() => {
      panel.classList.add('is-open');
      backdrop.classList.add('is-open');
    });
    panel.setAttribute('aria-hidden', 'false');
    toggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('is-drawer-open');
    const first = panel.querySelector('.mobile-drawer__close');
    if (first) first.focus();
  };

  const close = () => {
    if (!isOpen()) return;
    panel.classList.remove('is-open');
    backdrop.classList.remove('is-open');
    panel.setAttribute('aria-hidden', 'true');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('is-drawer-open');
    // Hide the backdrop from AT/hit-testing once the fade finishes.
    const hide = () => { backdrop.hidden = true; };
    backdrop.addEventListener('transitionend', hide, { once: true });
    setTimeout(hide, 300); // fallback if transitionend doesn't fire
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
  };

  toggle.addEventListener('click', open);

  // Any element flagged data-drawer-close (backdrop, X button) closes.
  panel.querySelectorAll('[data-drawer-close]').forEach((el) =>
    el.addEventListener('click', close)
  );
  backdrop.addEventListener('click', close);

  // Tapping a menu link navigates away; close so it isn't left open on return
  // (and closes cleanly for same-page anchors).
  panel.addEventListener('click', (e) => {
    if (e.target.closest('.mobile-drawer__nav a')) close();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen()) close();
  });

  // If the viewport grows past the mobile breakpoint while open, close so the
  // desktop nav takes over cleanly.
  window.matchMedia('(min-width: 941px)').addEventListener('change', (e) => {
    if (e.matches) close();
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMobileMenu);
} else {
  initMobileMenu();
}
