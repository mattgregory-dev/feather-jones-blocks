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

/**
 * WooCommerce asset offload
 * -------------------------
 * The store funnels through LearnDash: the path to purchase is enroll →
 * /checkout/, and the header cart/account are plain icon links (no Woo blocks).
 * So WooCommerce's CSS/JS is only needed on the actual store pages. Everywhere
 * else — home, courses, all LearnDash, landing, contact, terms, regular pages —
 * we shut its assets off. Cart fragments (the live cart-count AJAX) are dropped
 * everywhere since nothing on the front end shows a live cart.
 */

/**
 * Is the current request a page that legitimately needs WooCommerce assets?
 * Product / shop / category (is_woocommerce), plus cart, checkout, and account.
 *
 * @return bool
 */
function sb_is_woocommerce_asset_context() {
	if ( is_admin() || wp_doing_ajax() || ! function_exists( 'WC' ) ) {
		return true;
	}

	return ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
		|| ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() );
}

/**
 * Stop WooCommerce loading its block JS/CSS bundles off the store pages.
 *
 * @param bool $should_load Whether WooCommerce would load block assets/styles.
 * @return bool
 */
function sb_woocommerce_should_load_block_assets( $should_load ) {
	return sb_is_woocommerce_asset_context() ? $should_load : false;
}
add_filter( 'woocommerce_should_load_block_assets', 'sb_woocommerce_should_load_block_assets' );
add_filter( 'woocommerce_should_load_block_styles', 'sb_woocommerce_should_load_block_assets' );

/**
 * Drop the classic WooCommerce stylesheets (general / layout / smallscreen) off
 * the store pages.
 *
 * @param array $styles WooCommerce's registered style handles.
 * @return array
 */
function sb_filter_woocommerce_enqueue_styles( $styles ) {
	return sb_is_woocommerce_asset_context() ? $styles : array();
}
add_filter( 'woocommerce_enqueue_styles', 'sb_filter_woocommerce_enqueue_styles' );

/**
 * Dequeue any WooCommerce styles/scripts that slipped past the filters, and drop
 * cart fragments everywhere (no live mini-cart to feed).
 */
function sb_dequeue_woocommerce_offscreen_assets() {
	if ( is_admin() ) {
		return;
	}

	// No live cart-count anywhere on the front end.
	wp_dequeue_script( 'wc-cart-fragments' );

	if ( sb_is_woocommerce_asset_context() ) {
		return;
	}

	$style_handles = array(
		'woocommerce-general',
		'woocommerce-layout',
		'woocommerce-smallscreen',
		'woocommerce-block-style',
		'wc-block-style',
		'wc-blocks-style',
		'wc-blocks-vendors-style',
		'woocommerce-blocktheme',
		'wc-stripe-blocks-checkout-style',
		'wc-stripe-upe-blocks',
	);
	foreach ( $style_handles as $handle ) {
		wp_dequeue_style( $handle );
	}

	$script_handles = array(
		'woocommerce',
		'wc-add-to-cart',
		'wc-add-to-cart-variation',
		'wc-single-product',
		'wc-cart',
		'wc-checkout',
		'wc-jquery-blockui',
		'wc-js-cookie',
		// Order-attribution marketing tracking — dropped off the store pages.
		'wc-order-attribution',
		'sourcebuster-js',
	);
	foreach ( $script_handles as $handle ) {
		wp_dequeue_script( $handle );
	}
}
// Woo enqueues some block styles late (during block render, printed in the
// footer), so run at enqueue, head-print, and early-footer time to catch them.
add_action( 'wp_enqueue_scripts', 'sb_dequeue_woocommerce_offscreen_assets', 100 );
add_action( 'wp_print_styles', 'sb_dequeue_woocommerce_offscreen_assets', 100 );
add_action( 'wp_print_scripts', 'sb_dequeue_woocommerce_offscreen_assets', 100 );
add_action( 'wp_footer', 'sb_dequeue_woocommerce_offscreen_assets', 5 );

/**
 * Null out the cart-fragments script data so nothing tries to revive it.
 *
 * @param array|null $params Localized script params.
 * @param string     $handle Script handle.
 * @return array|null
 */
function sb_disable_woocommerce_cart_fragments_data( $params, $handle ) {
	return 'wc-cart-fragments' === $handle ? null : $params;
}
add_filter( 'woocommerce_get_script_data', 'sb_disable_woocommerce_cart_fragments_data', 10, 2 );

/**
 * Output a real <h1> title above the my-account login form. WooCommerce's
 * template hard-codes an <h2>Login</h2>; we hide that (in _woocommerce.scss) and
 * print our own semantic page title here. Scoped to the guest account page so it
 * never fires on the checkout login form.
 */
function sb_woocommerce_login_title() {
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
		echo '<h1 class="my-account-login__title">' . esc_html__( 'Log in to your account', 'fj-blocks' ) . '</h1>';
	}
}
add_action( 'woocommerce_before_customer_login_form', 'sb_woocommerce_login_title' );
