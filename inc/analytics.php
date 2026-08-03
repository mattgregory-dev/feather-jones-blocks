<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics 4 (gtag.js) output.
 *
 * Active by default but no-ops unless `SB_GA_MEASUREMENT_ID` is defined
 * in `wp-config.php`. To enable, add:
 *
 *     define( 'SB_GA_MEASUREMENT_ID', 'G-XXXXXXXXXX' );
 *
 * Skips:
 *   - Vite dev mode (CUSTOM_WP_VITE_DEV) — no analytics from local work.
 *   - Logged-in users — admins clicking around shouldn't pollute stats.
 *
 * Performance:
 *   - Emits `<link rel="preconnect">` to googletagmanager.com and
 *     google-analytics.com so TLS handshakes start before the gtag.js
 *     request fires (saves ~100-300ms on first event).
 *   - Loads gtag.js with `async` so it doesn't block parsing or rendering.
 *
 * Hooked at `wp_head` priority 5 so it runs early in <head> (before
 * preloads and stylesheets), giving preconnect the maximum head start.
 */
function sb_output_google_analytics_tag() {
	if ( sb_is_vite_dev() || is_user_logged_in() ) {
		return;
	}

	if ( ! defined( 'SB_GA_MEASUREMENT_ID' ) ) {
		return;
	}

	$ga_measurement_id = trim( (string) SB_GA_MEASUREMENT_ID );
	if ( '' === $ga_measurement_id ) {
		return;
	}

	$ga_script_url = add_query_arg( 'id', $ga_measurement_id, 'https://www.googletagmanager.com/gtag/js' );
	// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Third-party gtag.js must load inline in <head>, before other scripts, so it cannot go through wp_enqueue_script().
	?>
	<!-- Google tag (gtag.js) -->
	<link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
	<link rel="preconnect" href="https://www.google-analytics.com" crossorigin>
	<script async src="<?php echo esc_url( $ga_script_url ); ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', <?php echo wp_json_encode( $ga_measurement_id ); ?>);
	</script>
	<?php
	// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript
}
add_action( 'wp_head', 'sb_output_google_analytics_tag', 5 );
