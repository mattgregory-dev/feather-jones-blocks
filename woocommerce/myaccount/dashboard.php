<?php
/**
 * My Account Dashboard — repurposed as "My Courses".
 *
 * The default WooCommerce dashboard shows a generic "Hello …, from your account
 * you can view recent orders…" blurb, which is dead weight for a course store.
 * We drop it and render LearnDash's profile block instead, so the dashboard
 * opens on the student's course list + progress stat bar. The nav label is
 * renamed to "My Courses" in inc/woocommerce.php.
 *
 * Overrides woocommerce/templates/myaccount/dashboard.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package fj-blocks
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// LearnDash profile: course list with the completion/points stat bar header.
echo do_blocks( '<!-- wp:learndash/ld-profile {"expand_all":false,"profile_link":false,"show_header":true} /-->' );

/**
 * My Account dashboard hook.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );
