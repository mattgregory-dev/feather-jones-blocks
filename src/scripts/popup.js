// Lead-capture popup (two-step, timed). Markup: inc/popup.php. Styles:
// _popup.scss. Frame 1 is the offer + one-click CTA; frame 2 reveals the
// Forminator lead form. On pages that already show an inline copy of the form
// (the home CTA band, flagged data-popup-borrow), the form node is MOVED into
// the popup on frame 2 and returned on close, so there's only ever one live
// instance of the form. Dismissals are remembered for 24h; a successful submit
// suppresses it permanently.

const KEY = 'sb_popup_state';
const DAY = 24 * 60 * 60 * 1000;

function readState() {
  try {
    return JSON.parse(localStorage.getItem(KEY)) || {};
  } catch {
    return {};
  }
}
function writeState(next) {
  try {
    localStorage.setItem(KEY, JSON.stringify(next));
  } catch {
    /* storage unavailable — popup just won't persist */
  }
}
function isSuppressed() {
  const s = readState();
  if (s.submitted) return true;
  if (s.dismissedUntil && Date.now() < s.dismissedUntil) return true;
  return false;
}

function initPopup() {
  const popup = document.querySelector('[data-popup]');
  const backdrop = document.querySelector('.sb-popup__backdrop');
  if (!popup || !backdrop) return;
  if (isSuppressed()) return;

  const borrow = popup.dataset.popupBorrow === '1';
  const formId = popup.dataset.popupFormId;
  const delay = parseInt(popup.dataset.popupDelay, 10) || 8000;
  const offerFrame = popup.querySelector('[data-popup-frame="offer"]');
  const formFrame = popup.querySelector('[data-popup-frame="form"]');
  const slot = popup.querySelector('[data-popup-form-slot]');

  // Dialog semantics (kept out of the markup so it stays plain).
  popup.setAttribute('role', 'dialog');
  popup.setAttribute('aria-modal', 'true');
  popup.setAttribute('aria-label', 'Free teaching');

  let opened = false;
  let borrowedForm = null;
  let borrowMarker = null;

  const open = () => {
    if (opened || isSuppressed()) return;
    opened = true;
    backdrop.hidden = false;
    popup.hidden = false;
    requestAnimationFrame(() => {
      backdrop.classList.add('is-open');
      popup.classList.add('is-open');
    });
    document.body.classList.add('is-popup-open');
    const closeBtn = popup.querySelector('.sb-popup__close');
    if (closeBtn) closeBtn.focus();
  };

  // Return a borrowed form node to its original spot.
  const restoreForm = () => {
    if (borrowedForm && borrowMarker && borrowMarker.parentNode) {
      borrowMarker.parentNode.insertBefore(borrowedForm, borrowMarker);
      borrowMarker.remove();
      borrowMarker = null;
      borrowedForm = null;
    }
  };

  const close = (dismiss) => {
    if (!opened) return;
    opened = false;
    popup.classList.remove('is-open');
    backdrop.classList.remove('is-open');
    document.body.classList.remove('is-popup-open');
    const hide = () => {
      popup.hidden = true;
      backdrop.hidden = true;
    };
    setTimeout(hide, 300);
    restoreForm();
    // Back to the offer frame for any future open.
    offerFrame.hidden = false;
    formFrame.hidden = true;
    if (dismiss) {
      writeState({ ...readState(), dismissedUntil: Date.now() + DAY });
    }
  };

  // Advance to the email frame, borrowing the inline form node if configured.
  const showForm = () => {
    if (borrow && !borrowedForm) {
      const form = document.querySelector('.forminator-custom-form-' + formId);
      if (form && !popup.contains(form)) {
        borrowedForm = form;
        borrowMarker = document.createComment('sb-popup-form');
        form.parentNode.insertBefore(borrowMarker, form);
        slot.appendChild(form);
      }
    }
    offerFrame.hidden = true;
    formFrame.hidden = false;
    const input = formFrame.querySelector('input[type="email"], input:not([type="hidden"])');
    if (input) input.focus();
  };

  popup.querySelectorAll('[data-popup-next]').forEach((el) =>
    el.addEventListener('click', showForm)
  );
  popup.querySelectorAll('[data-popup-dismiss], [data-popup-close]').forEach((el) =>
    el.addEventListener('click', () => close(true))
  );
  backdrop.addEventListener('click', () => close(true));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && opened) close(true);
  });

  // Suppress permanently once the form submits successfully — Forminator injects
  // a success response message (it also redirects, but this catches it either
  // way). Watch the popup subtree for that success state.
  const obs = new MutationObserver(() => {
    if (popup.querySelector('.forminator-response-message.forminator-success, .forminator-success')) {
      writeState({ ...readState(), submitted: true });
      obs.disconnect();
    }
  });
  obs.observe(popup, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class'],
  });

  setTimeout(open, delay);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPopup);
} else {
  initPopup();
}
