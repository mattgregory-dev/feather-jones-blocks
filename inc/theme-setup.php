<?php
/**
 * Theme supports and setup for a block (FSE) theme.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sb_setup() {
	load_theme_textdomain( 'starter-blocks', get_template_directory() . '/languages' );

	// Block themes provide title-tag, post-thumbnails, responsive-embeds and
	// HTML5 automatically, but declaring the ones we rely on is harmless and explicit.
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'html5',
		array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);
}
add_action( 'after_setup_theme', 'sb_setup' );

/**
 * Register the theme's block-pattern category. The starter patterns group under
 * it in the inserter, separate from WordPress's core pattern categories.
 */
function sb_register_pattern_categories() {
	register_block_pattern_category(
		'starter-blocks',
		array( 'label' => __( 'Starter Blocks', 'starter-blocks' ) )
	);
}
add_action( 'init', 'sb_register_pattern_categories' );
