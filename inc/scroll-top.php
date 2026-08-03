<?php
/**
 * Back-to-top button.
 *
 * Rendered on wp_footer as fixed-position chrome (not block content). It stays
 * hidden until the page is scrolled. Behavior: src/scripts/scroll-top.js.
 * Styling: src/styles/_scroll-top.scss.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the button. The SVG is an inline chevron (no icon font / CDN) and is
 * aria-hidden because the button itself carries an accessible label.
 */
function sb_back_to_top_button() {
	?>
	<button type="button" class="back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'starter-blocks' ); ?>">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<path d="M18 15l-6-6-6 6" />
		</svg>
	</button>
	<?php
}
add_action( 'wp_footer', 'sb_back_to_top_button' );
