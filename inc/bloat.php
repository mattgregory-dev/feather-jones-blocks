<?php
/**
 * Trim front-end cruft — block-theme (FSE) safe.
 *
 * Only removes things that are genuinely unused AND safe for a block theme.
 * Notably does NOT dequeue wp-block-library / global-styles (load-bearing in
 * FSE) and does NOT touch jQuery. Verify any addition against the rendered
 * front end — block-theme breakage is silent.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Disable the JS-based emoji polyfill. Modern OSes render emoji natively.
function sb_disable_emoji() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'sb_disable_emoji' );

// Strip the s.w.org dns-prefetch hint that exists only to support emoji.
function sb_remove_emoji_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$urls = array_diff(
			$urls,
			array( 'http://s.w.org', 'https://s.w.org', 's.w.org' )
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'sb_remove_emoji_dns_prefetch', 10, 2 );

// Strip <meta name="generator" content="WordPress X.Y"> from <head>.
function sb_remove_generator() {
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'sb_remove_generator' );

// NOTE: deliberately NOT touching block CSS loading. `should_load_separate_core
// _block_assets` (per-block CSS) was tried and DROPPED — in this WP version it
// stops the core alignment styles (.has-text-align-center) from loading, so
// centered blocks render left-aligned. wp-block-library is ~30KB; on a lean
// site that is not worth risking correctness. Verified on the rendered page.
