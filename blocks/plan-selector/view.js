/**
 * Plan Selector — front-end wiring.
 *
 * Selecting a card writes its label and checkout URL onto the shared button.
 * The button is a native block the author marks with the `plan-cta` class; it can
 * sit anywhere near the selector (above or below, mobile or desktop), so we find
 * the nearest ancestor that contains a `.plan-cta` link rather than assuming a
 * fixed position. CSS owns the selected-card look via :has() — this only touches
 * the button.
 *
 * A card with an empty URL leaves the button's authored href alone (so the block
 * is usable before checkout links are wired up); likewise an empty label.
 */
( function () {
	function findCta( el ) {
		var node = el.parentElement;
		while ( node ) {
			var cta = node.querySelector( '.plan-cta a' );
			if ( cta ) {
				return cta;
			}
			node = node.parentElement;
		}
		return null;
	}

	function sync( selector, cta ) {
		var checked = selector.querySelector( 'input[type="radio"]:checked' );
		if ( ! checked ) {
			return;
		}
		var label = checked.getAttribute( 'data-cta-label' );
		var url = checked.getAttribute( 'data-cta-url' );
		if ( label ) {
			cta.textContent = label;
		}
		if ( url ) {
			cta.setAttribute( 'href', url );
		}
	}

	document.querySelectorAll( '.plan-selector' ).forEach( function ( selector ) {
		var cta = findCta( selector );
		if ( ! cta ) {
			return;
		}
		sync( selector, cta );
		selector.addEventListener( 'change', function ( event ) {
			if ( event.target && 'radio' === event.target.type ) {
				sync( selector, cta );
			}
		} );
	} );
} )();
