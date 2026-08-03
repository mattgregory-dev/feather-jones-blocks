// Back-to-top button.
// Reveals the button (rendered by inc/scroll-top.php) once the page scrolls
// past a threshold, and smooth-scrolls to the top on click — respecting
// prefers-reduced-motion. The .is-visible class it toggles is styled in
// src/styles/_scroll-top.scss.

const THRESHOLD = 400; // px scrolled before the button appears

function initBackToTop() {
  const btn = document.querySelector('.back-to-top');
  if (!btn) return;

  const sync = () => {
    btn.classList.toggle('is-visible', window.scrollY > THRESHOLD);
  };

  sync();
  window.addEventListener('scroll', sync, { passive: true });

  btn.addEventListener('click', () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initBackToTop);
} else {
  initBackToTop();
}
