<?php
/**
 * Custom block style variations.
 *
 * Registers the reusable `is-style-*` looks that show up as selectable styles
 * in the editor. The look itself is defined in the escape-hatch SCSS
 * (src/styles/_buttons.scss, _lists.scss); this file only makes the options
 * available.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sb_register_block_styles() {
	// Secondary button — the filled inverse of the default button; hover inverts
	// it back. Styled in src/styles/_buttons.scss.
	register_block_style(
		'core/button',
		array(
			'name'  => 'secondary',
			'label' => __( 'Secondary', 'fj-blocks' ),
		)
	);

	// Checklist — swaps list bullets for a bordered checkmark marker. core/list
	// has no styles by default, so registering this also surfaces a "Default"
	// option alongside it. Styled in src/styles/_lists.scss.
	register_block_style(
		'core/list',
		array(
			'name'  => 'checklist',
			'label' => __( 'Checklist', 'fj-blocks' ),
		)
	);

	// Botanical — the decorative full-width gradient divider (sage/taupe fade).
	// Scoped as a style so plain separators keep the core look. Styled in
	// src/styles/_separator.scss. Best paired with full-width alignment.
	register_block_style(
		'core/separator',
		array(
			'name'  => 'botanical',
			'label' => __( 'Botanical', 'fj-blocks' ),
		)
	);

	// Specs — a borderless key/value table: hairline row dividers, the first
	// column as a Montserrat/brand kicker label, the second as the value.
	// Styled in src/styles/_table.scss.
	register_block_style(
		'core/table',
		array(
			'name'  => 'specs',
			'label' => __( 'Specs', 'fj-blocks' ),
		)
	);

	// Comparison — a two-option comparison: tinted header caps (surface-2 /
	// surface-3), bold first-column labels, and a soft green wash down the
	// featured (right) column. Expects a header row. Styled in _table.scss.
	register_block_style(
		'core/table',
		array(
			'name'  => 'comparison',
			'label' => __( 'Comparison', 'fj-blocks' ),
		)
	);
}
add_action( 'init', 'sb_register_block_styles' );
