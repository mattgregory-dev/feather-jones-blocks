<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prevent WordPress from auto-generating attachment titles from the filename.
function sb_strip_auto_attachment_title( $data, $postarr ) {
	if ( ! empty( $data['post_title'] ) && ! empty( $postarr['file'] ) ) {
		$filename = pathinfo( $postarr['file'], PATHINFO_FILENAME );

		$normalized_title = sanitize_title( $data['post_title'] );
		$normalized_file  = sanitize_title( $filename );

		if ( $normalized_title === $normalized_file ) {
			$data['post_title'] = '';
		}
	}

	if ( ! empty( $data['post_excerpt'] ) ) {
		$data['post_excerpt'] = '';
	}

	return $data;
}
add_filter( 'wp_insert_attachment_data', 'sb_strip_auto_attachment_title', 10, 2 );
