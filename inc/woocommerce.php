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

/**
 * Point the WooCommerce "shop" page permalink at the courses archive.
 *
 * The store funnels through LearnDash courses, not a product catalog, so every
 * "shop" affordance should land on /courses/ — including the Mini-Cart empty
 * "Start shopping" button, which the block JS renders from the localized
 * `storePages.shop.permalink`. Both that value and WooCommerce's
 * `wc_get_page_permalink( 'shop' )` derive from `get_permalink()`, so filtering
 * `page_link` for the shop page is the single lever covering server- and
 * client-rendered links alike. The page still renders at /shop/; only generated
 * links change.
 *
 * @param string $permalink The page URL.
 * @param int    $post_id   The page ID.
 * @return string
 */
function sb_shop_page_link_to_courses( $permalink, $post_id ) {
	if ( (int) $post_id === (int) wc_get_page_id( 'shop' ) ) {
		return home_url( '/courses/' );
	}

	return $permalink;
}
add_filter( 'page_link', 'sb_shop_page_link_to_courses', 10, 2 );

/**
 * Relabel the Mini-Cart empty-state button "Start shopping" → "Browse Courses"
 * to match the Cart block's empty-state link. The Mini-Cart button is
 * server-rendered (iAPI Mini-Cart) via `__( 'Start shopping', 'woocommerce' )`,
 * so a scoped gettext filter on that exact string relabels it.
 *
 * @param string $translation Translated text.
 * @param string $text        Original text.
 * @param string $domain      Text domain.
 * @return string
 */
function sb_mini_cart_shopping_label( $translation, $text, $domain ) {
	if ( 'woocommerce' === $domain && 'Start shopping' === $text ) {
		return __( 'Browse Courses', 'starter-blocks' );
	}

	return $translation;
}
add_filter( 'gettext', 'sb_mini_cart_shopping_label', 10, 3 );
