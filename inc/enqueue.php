<?php
/**
 * Front-end asset loading.
 *
 * Loads the compiled SCSS bundle (dist/assets/main.css) and the theme JS
 * bundle (dist/main.js), or the Vite dev server when CUSTOM_WP_VITE_DEV is on.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sb_is_vite_dev() {
	return defined( 'CUSTOM_WP_VITE_DEV' ) && CUSTOM_WP_VITE_DEV;
}

function sb_assets() {
	$theme_uri = get_template_directory_uri();
	$dist      = $theme_uri . '/dist';

	// Toggle with: define('CUSTOM_WP_VITE_DEV', true); in wp-config.php
	if ( sb_is_vite_dev() ) {
		$vite = 'http://localhost:5175';

		add_filter(
			'script_loader_tag',
			function ( $tag, $handle, $src ) {
				$module_handles = array( 'vite-client', 'theme-main' );
				if ( in_array( $handle, $module_handles, true ) ) {
					// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Filtering an already-enqueued handle.
					return '<script type="module" src="' . esc_url( $src ) . '"></script>';
				}
				return $tag;
			},
			10,
			3
		);

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Dev server, no cache concern.
		wp_enqueue_script( 'vite-client', $vite . '/@vite/client', array(), null, false );
		wp_script_add_data( 'vite-client', 'type', 'module' );

		// Main JS entry served by Vite (imports SCSS in dev).
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Dev server, no cache concern.
		wp_enqueue_script( 'theme-main', $vite . '/main.js', array(), null, false );
		wp_script_add_data( 'theme-main', 'type', 'module' );

		return;
	}

	$main_css_path    = get_template_directory() . '/dist/assets/main.css';
	$main_js_path     = get_template_directory() . '/dist/main.js';
	$main_css_version = file_exists( $main_css_path ) ? filemtime( $main_css_path ) : null;
	$main_js_version  = file_exists( $main_js_path ) ? filemtime( $main_js_path ) : null;

	// Compiled SCSS bundle.
	wp_enqueue_style( 'theme-main', $dist . '/assets/main.css', array(), $main_css_version );

	// Main JS bundle (ES module).
	wp_enqueue_script(
		'theme-main',
		$dist . '/main.js',
		array(),
		$main_js_version,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_script_add_data( 'theme-main', 'type', 'module' );
}
add_action( 'wp_enqueue_scripts', 'sb_assets', 999 );

/**
 * Load the compiled SCSS bundle inside the editor canvas too, so custom CSS
 * (block styles, button variations, etc.) previews the same as the front end
 * instead of falling back to editor defaults.
 */
function sb_editor_assets() {
	add_editor_style( 'dist/assets/main.css' );
}
add_action( 'after_setup_theme', 'sb_editor_assets' );

// Force the theme main bundle to render as an ES module.
function sb_force_main_module_tag( $tag, $handle, $src ) {
	if ( 'theme-main' !== $handle ) {
		return $tag;
	}
	// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Filtering an already-enqueued handle.
	return '<script type="module" src="' . esc_url( $src ) . '"></script>';
}
add_filter( 'script_loader_tag', 'sb_force_main_module_tag', 20, 3 );

/**
 * Preload the two above-the-fold font faces (Lora for headings, Lato regular for
 * body) so the browser fetches them immediately rather than after it parses the
 * stylesheet — cutting the delay before the hero heading + copy can paint. Other
 * weights/styles load on demand via the theme.json @font-face rules.
 */
function sb_preload_critical_fonts() {
	$fonts = array( 'lora.woff2', 'lato-400-regular.woff2' );
	$base  = get_template_directory_uri() . '/assets/fonts/';
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $base . $font )
		);
	}
}
add_action( 'wp_head', 'sb_preload_critical_fonts', 1 );

/**
 * Defer render-blocking front-end scripts — Forminator's bundle (front.multi,
 * the form + validation scripts) and jQuery/jquery-migrate — so they leave the
 * critical render path (Lighthouse flagged ~990ms). Uses WordPress's
 * dependency-aware `strategy` API (6.3+): a script is only actually deferred
 * when every dependent (including inline `after` scripts) is defer/async-safe,
 * so Forminator's inline config can't be left stranded. Admin is untouched.
 */
function sb_defer_front_scripts() {
	if ( is_admin() ) {
		return;
	}
	foreach ( wp_scripts()->registered as $handle => $script ) {
		$src = is_string( $script->src ) ? $script->src : '';
		$defer =
			false !== strpos( $src, 'forminator' ) ||
			false !== strpos( $src, 'jquery.validate' ) ||
			'jquery-core' === $handle ||
			'jquery-migrate' === $handle;
		if ( $defer ) {
			wp_script_add_data( $handle, 'strategy', 'defer' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'sb_defer_front_scripts', 100 );
