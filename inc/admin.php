<?php
/**
 * Admin UI tweaks.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hide ACF's per-metabox "Edit field group" cog.
 *
 * It sits in the postbox handle and appears on hover, nudging the header and
 * making the panel jump under the pointer. Field groups are edited from the ACF
 * menu here, so the shortcut is only in the way.
 *
 * CSS rather than a filter: ACF's JS injects the link from localized postbox
 * data, and neither the link nor that data passes through a filter.
 *
 * `!important` because ACF reveals the cog with a four-class hover selector
 * (`.acf-postbox > .hndle:hover .acf-hndle-cog`), which a single class can't
 * out-rank; matching that selector instead would tie this to ACF's internal
 * markup.
 */
function sb_hide_acf_field_group_edit_link() {
	echo '<style>.acf-hndle-cog{display:none!important;}</style>' . "\n";
}
add_action( 'admin_head', 'sb_hide_acf_field_group_edit_link' );

/**
 * Unstack LearnDash's editor header, the admin bar, and the editor.
 *
 * All three want the top of the viewport: LearnDash fixes #sfwd-header at
 * top: 0, behind the admin bar, and the editor starts under the header. So the
 * header drops below the bar, and the editor below the header.
 *
 * The editor's offset is measured at runtime — header height varies by screen
 * (tab bar, action row). Its `padding-top` is zeroed rather than reused: the
 * element is fixed with `bottom: 0`, so padding pushes the canvas out through
 * the bottom instead of moving the box. `!important` throughout to clear
 * LearnDash's per-breakpoint values. Desktop only; below 782px LearnDash
 * re-flows the header and the editor stops being fixed.
 */
function sb_learndash_editor_header_offset() {
	$screen = get_current_screen();
	if ( ! $screen || 0 !== strpos( (string) $screen->post_type, 'sfwd' ) ) {
		return;
	}
	?>
<style>
	@media ( min-width: 782px ) {
		/* Header below the admin bar. */
		body.learndash-post-type.admin-bar #sfwd-header {
			top: var(--wp-admin--admin-bar--height, 32px) !important;
		}

		/* Editor below the header. */
		body.learndash-post-type.block-editor-page .interface-interface-skeleton {
			top: var(--sb-ld-header-bottom, 115px) !important;
		}

		body.learndash-post-type.block-editor-page .edit-post-layout {
			padding-top: 0 !important;
		}
	}
</style>
<script>
( function () {
	var sync = function () {
		var header = document.getElementById( 'sfwd-header' );
		if ( ! header ) {
			return;
		}
		document.body.style.setProperty(
			'--sb-ld-header-bottom',
			header.getBoundingClientRect().bottom + 'px'
		);
	};

	window.addEventListener( 'load', sync );
	window.addEventListener( 'resize', sync );

	// The header renders after the editor mounts, grows when its tabs wrap, and
	// shifts when the admin bar hides — so watch the element and the body class.
	var observe = function () {
		var header = document.getElementById( 'sfwd-header' );
		if ( header && window.ResizeObserver ) {
			new ResizeObserver( sync ).observe( header );
		}
	};

	new MutationObserver( function () {
		sync();
		observe();
	} ).observe( document.body, { attributes: true, attributeFilter: [ 'class' ], childList: true } );

	sync();
	observe();
}() );
</script>
	<?php
}
add_action( 'admin_footer', 'sb_learndash_editor_header_offset' );
