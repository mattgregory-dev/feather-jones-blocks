<?php
/**
 * Site shortcodes.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [current_year] — the current four-digit year in the site's timezone, for an
 * evergreen copyright line that needs no annual maintenance. Placed via a
 * Shortcode block (e.g. in the footer).
 *
 * @return string
 */
function sb_current_year_shortcode() {
	return esc_html( wp_date( 'Y' ) );
}
add_shortcode( 'current_year', 'sb_current_year_shortcode' );
