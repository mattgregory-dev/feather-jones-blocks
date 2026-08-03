<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Point the login logo at the home page instead of wordpress.org.
function sb_login_logo_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'sb_login_logo_url' );

// Use the site name as the login logo title.
function sb_login_logo_title() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'sb_login_logo_title' );

// Use the uploaded custom logo (Customizer → Site Identity) on the login
// screen. Appended via wp_add_inline_style so the rules land *after*
// wp-admin/css/login.css in the cascade.
function sb_login_logo_styles() {
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( ! $logo_id ) {
		return;
	}
	$logo = wp_get_attachment_image_src( $logo_id, 'full' );
	if ( ! $logo ) {
		return;
	}

	$css = '.login h1 a {
    background-image: url(' . esc_url( $logo[0] ) . ');
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;
    width: 320px;
    height: 80px;
  }';

	wp_add_inline_style( 'login', $css );
}
add_action( 'login_enqueue_scripts', 'sb_login_logo_styles' );
