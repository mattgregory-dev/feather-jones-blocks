<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Comments are disabled globally. To re-enable site-wide, define
// SB_ENABLE_COMMENTS as true in wp-config.php — or remove the require_once
// for this file from functions.php.
if ( defined( 'SB_ENABLE_COMMENTS' ) && SB_ENABLE_COMMENTS ) {
	return;
}

// Force comments and trackbacks closed on every post.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

// Hide existing comments from the front-end.
add_filter( 'comments_array', '__return_empty_array', 10 );

// Drop comment support from posts and pages.
function sb_remove_comment_support() {
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'post', 'trackbacks' );
	remove_post_type_support( 'page', 'comments' );
	remove_post_type_support( 'page', 'trackbacks' );
}
add_action( 'init', 'sb_remove_comment_support', 100 );

// Hide the Comments admin menu item and the admin bar shortcut.
function sb_remove_comments_admin_menu() {
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'sb_remove_comments_admin_menu' );

function sb_remove_comments_admin_bar( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'sb_remove_comments_admin_bar', 999 );

// Redirect /wp-admin/edit-comments.php in case the URL is hit directly.
function sb_redirect_comments_admin_page() {
	global $pagenow;
	if ( 'edit-comments.php' === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'sb_redirect_comments_admin_page' );
