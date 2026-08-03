<?php
/**
 * Custom block registration.
 *
 * Native blocks whose structural markup stays in git while their content lives
 * in the database (editing surface = block attributes only). Source is in
 * blocks/; @wordpress/scripts compiles it to build/. Each block is registered
 * from its built directory; the registration lines are added as blocks land.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sb_register_blocks() {
	register_block_type( get_template_directory() . '/build/intro-section' );
	register_block_type( get_template_directory() . '/build/cta-band' );
	register_block_type( get_template_directory() . '/build/checklist-section' );
	register_block_type( get_template_directory() . '/build/spotlight' );
	register_block_type( get_template_directory() . '/build/bio' );
	register_block_type( get_template_directory() . '/build/hero' );
}
add_action( 'init', 'sb_register_blocks' );
