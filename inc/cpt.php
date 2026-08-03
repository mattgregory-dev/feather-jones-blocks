<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable Squiz.PHP.CommentedOutCode.Found -- Intentional CPT scaffold; uncomment to enable.

// IMPORTANT — read before uncommenting:
//
// Custom post types register data that should outlive the active theme. If
// the site is ever re-skinned with a different theme, a CPT registered here
// disappears: existing posts become "post type 'example' not registered"
// ghosts (still in the database, but invisible in admin and unqueryable by
// type). Front-end URLs 404. The client effectively loses access to that
// content until the theme is reactivated.
//
// **For production CPTs, build a plugin** (e.g., `site-events`, `site-projects`)
// and register the post type there. Theme switches won't affect data.
// Templates (single-{type}.php, archive-{type}.php) still live in the theme,
// but the CPT registration and field definitions belong in the plugin.
//
// This scaffold exists for two narrow cases only:
//   1. Quick prototyping where the data is throwaway.
//   2. A genuinely theme-locked CPT (rare — usually a sign the design is
//      tightly coupled to a content type that will never outlive this build).
//
// If neither applies, build a plugin instead.

// Example custom post type — uncomment, rename, and adjust to enable.
//
// function sb_register_cpt_example() {
//   register_post_type(
//     'example',
//     array(
//       'label'         => __( 'Examples', 'starter-blocks' ),
//       'public'        => true,
//       'menu_icon'     => 'dashicons-admin-post',
//       'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
//       'has_archive'   => true,
//       'rewrite'       => array( 'slug' => 'examples' ),
//       'show_in_rest'  => true,
//     )
//   );
// }
// add_action( 'init', 'sb_register_cpt_example' );
