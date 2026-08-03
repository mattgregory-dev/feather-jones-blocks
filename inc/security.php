<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Disable XML-RPC. Common brute-force surface, rarely needed by modern sites.
// Re-enable for Jetpack or remote publishing by removing this filter.
add_filter( 'xmlrpc_enabled', '__return_false' );

// Mask login error details. Default WP returns "the username `admin` is
// incorrect" / "the password is wrong", which both leak information about
// whether a username exists.
function sb_generic_login_error() {
	return __( 'Invalid login. Please try again.', 'fj-blocks' );
}
add_filter( 'login_errors', 'sb_generic_login_error' );

// Strip the user-listing endpoints from the REST API. /wp-json/wp/v2/users
// is publicly readable by default and exposes every author's username.
function sb_block_rest_user_enumeration( $endpoints ) {
	if ( isset( $endpoints['/wp/v2/users'] ) ) {
		unset( $endpoints['/wp/v2/users'] );
	}
	if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'sb_block_rest_user_enumeration' );

// Block ?author=N enumeration (legacy attack vector — WordPress redirects
// from /?author=1 to /author/{username}/, leaking the slug).
function sb_block_author_query_enumeration() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Detection-only redirect, not form processing.
	if ( ! is_admin() && isset( $_GET['author'] ) ) {
		wp_safe_redirect( home_url(), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'sb_block_author_query_enumeration' );
