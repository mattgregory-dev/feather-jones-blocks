<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve an uploaded file's attachment ID from its base filename.
 *
 * Attachment IDs are assigned per install, so the same file has a different ID
 * on dev vs. live. Patterns therefore reference images by filename and resolve
 * the local ID here, at render time — keeping the block markup portable across
 * installs while still yielding a real ID (so WordPress adds srcset/sizes).
 *
 * Results are cached per request. Returns 0 if no matching attachment exists,
 * in which case the image simply renders empty until the file is uploaded.
 *
 * @param string $filename Base filename, e.g. "placeholder-horizontal.webp".
 * @return int Attachment ID, or 0 if not found.
 */
function sb_attachment_id_by_filename( $filename ) {
	static $cache = array();

	if ( array_key_exists( $filename, $cache ) ) {
		return $cache[ $filename ];
	}

	global $wpdb;

	// _wp_attached_file stores either "file.webp" (flat uploads) or
	// "2026/07/file.webp" (date-based), so match the bare name or a path ending.
	$id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attached_file'
			 AND ( meta_value = %s OR meta_value LIKE %s )
			 ORDER BY post_id DESC
			 LIMIT 1",
			$filename,
			'%/' . $wpdb->esc_like( $filename )
		)
	);

	$cache[ $filename ] = $id;
	return $id;
}

/**
 * Render a core/image block for a media-library file referenced by filename.
 *
 * The resolved (per-install) ID is written into the block comment, the src, and
 * the wp-image-<id> class together, so the markup stays internally consistent
 * and portable — and WordPress adds srcset/sizes because a real ID is present.
 *
 * @param string $filename Base filename, e.g. "placeholder-horizontal.webp".
 * @param string $alt      Alt text.
 * @param string $link_url Optional URL to wrap the image in (linkDestination custom).
 * @return string Block markup (empty <img> if the file is not in the library).
 */
function sb_image_block( $filename, $alt = '', $link_url = '' ) {
	$id  = sb_attachment_id_by_filename( $filename );
	$src = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';

	$img = sprintf(
		'<img src="%s" alt="%s" class="wp-image-%d"/>',
		esc_url( $src ),
		esc_attr( $alt ),
		$id
	);

	if ( '' !== $link_url ) {
		$inner = '<a href="' . esc_url( $link_url ) . '">' . $img . '</a>';
		$dest  = 'custom';
	} else {
		$inner = $img;
		$dest  = 'none';
	}

	return sprintf(
		'<!-- wp:image {"id":%d,"sizeSlug":"full","linkDestination":"%s"} -->' . "\n" .
		'<figure class="wp-block-image size-full">%s</figure>' . "\n" .
		'<!-- /wp:image -->',
		$id,
		$dest,
		$inner
	);
}

// Override the threshold above which WP scales uploaded images.
// WP default is 2560px. Bumped to 3000 so retina-density source images
// survive their initial upload pass.
function sb_big_image_size_threshold() {
	return 3000;
}
add_filter( 'big_image_size_threshold', 'sb_big_image_size_threshold' );

// Override default JPEG quality. WP default is 82.
function sb_jpeg_quality() {
	return 85;
}
add_filter( 'jpeg_quality', 'sb_jpeg_quality' );

// LCP hint: promote the first attachment image rendered inside the main loop
// on a singular page (typically `the_post_thumbnail()`) to `loading="eager"`
// and `fetchpriority="high"`. Overrides WP's auto-heuristic, which can pick
// the wrong image on layouts with logos/avatars/nav icons above the hero.
//
// Only affects images flowing through wp_get_attachment_image[*] functions.
// Hand-written <img> tags must set loading/fetchpriority themselves.
function sb_lcp_image_hint( $attr ) {
	static $promoted = false;

	if ( $promoted ) {
		return $attr;
	}
	if ( is_admin() || is_feed() ) {
		return $attr;
	}
	if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
		return $attr;
	}

	$attr['loading']       = 'eager';
	$attr['fetchpriority'] = 'high';
	$promoted              = true;

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'sb_lcp_image_hint' );

//////////////////////////////////////////////////
//////////////// SVG uploads /////////////////////
//////////////////////////////////////////////////
//
// SVGs can carry inline JavaScript that executes when the file is rendered,
// so they are blocked by default. To allow uploads safely, choose ONE:
//
//   1. Install the "Safe SVG" plugin (by 10up). Sanitization happens at
//      upload time, no theme code change required.
//
//   2. `composer require enshrined/svg-sanitize`, then in a custom
//      wp_handle_upload_prefilter add the sanitizer call before WP stores
//      the file. Then uncomment the filter below.
//
// The bare allow-list below is INTENTIONALLY commented out. Do not enable
// without one of the sanitization paths above in place — uploaded SVGs
// will execute JS in the context of any logged-in user who views them.
//
// function sb_allow_svg_upload( $mimes ) {
//   $mimes['svg'] = 'image/svg+xml';
//   return $mimes;
// }
// add_filter( 'upload_mimes', 'sb_allow_svg_upload' );
