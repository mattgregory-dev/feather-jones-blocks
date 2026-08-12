<?php
/**
 * WooCommerce integration.
 *
 * WooCommerce auto-inserts its Mini-Cart and Customer Account blocks into the
 * header template part via Block Hooks (after core/navigation), which we don't
 * want placed for us — it breaks the header's grid. We drop the auto-inserted
 * copies and instead place both blocks explicitly in parts/header.html, so we
 * control exactly where they sit in the header's right-hand action zone.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drop WooCommerce's auto-inserted header blocks (Mini-Cart, Customer Account).
 *
 * @param string[] $hooked_block_types Block types WordPress will auto-insert at
 *                                     the current anchor/position.
 * @return string[]
 */
function sb_remove_woo_hooked_blocks( $hooked_block_types ) {
	return array_values(
		array_diff(
			$hooked_block_types,
			array( 'woocommerce/mini-cart', 'woocommerce/customer-account' )
		)
	);
}
add_filter( 'hooked_block_types', 'sb_remove_woo_hooked_blocks' );

/**
 * Tailor the My Account navigation for the course-driven store.
 *
 * Removing Downloads and renaming Dashboard leaves the default Woo order as:
 * My Courses, Orders, Address, Payment Methods, Account Details, Log out — no
 * explicit reordering needed. The Dashboard screen itself is repurposed into a
 * course list via the dashboard.php template override (woocommerce/myaccount/).
 *
 * @param array $items Menu slug => label.
 * @return array
 */
function sb_woo_account_menu_items( $items ) {
	unset( $items['downloads'] );

	if ( isset( $items['dashboard'] ) ) {
		$items['dashboard'] = __( 'My Courses', 'starter-blocks' );
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'sb_woo_account_menu_items', 20 );
