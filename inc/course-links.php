<?php
/**
 * Product → linked-course resolution (ported from the classic fj theme).
 *
 * WooCommerce products are sold as enrolment into a LearnDash course. These
 * helpers resolve the linked course and repoint cart/checkout item links at its
 * page instead of the bare product page, so a shopper clicking a cart item lands
 * on the course they're buying. A product with no linked course renders
 * unlinked — this store has no product pages.
 *
 * @package fj-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a post ID from a loosely-typed value (WP_Post, id, array, CSV string).
 * Optionally constrained to an expected post type.
 */
function sb_resolve_linked_post_id( $value, $expected_post_type = '' ) {
	$post_id = 0;

	if ( $value instanceof WP_Post ) {
		$post_id = (int) $value->ID;
	} elseif ( is_numeric( $value ) ) {
		$post_id = absint( $value );
	} elseif ( is_array( $value ) ) {
		if ( isset( $value['ID'] ) && is_numeric( $value['ID'] ) ) {
			$post_id = absint( $value['ID'] );
		} elseif ( isset( $value['id'] ) && is_numeric( $value['id'] ) ) {
			$post_id = absint( $value['id'] );
		} else {
			foreach ( $value as $candidate ) {
				$candidate_id = sb_resolve_linked_post_id( $candidate, $expected_post_type );
				if ( $candidate_id > 0 ) {
					return $candidate_id;
				}
			}
		}
	} elseif ( is_string( $value ) && '' !== trim( $value ) ) {
		$parts = array_filter( array_map( 'trim', explode( ',', $value ) ) );
		foreach ( $parts as $part ) {
			if ( is_numeric( $part ) ) {
				$post_id = absint( $part );
				break;
			}
		}
	}

	if ( $post_id <= 0 ) {
		return 0;
	}

	if ( '' !== $expected_post_type && get_post_type( $post_id ) !== $expected_post_type ) {
		return 0;
	}

	return $post_id;
}

/**
 * Resolve the LearnDash course linked to a product (ACF field + LD-Woo meta fallbacks).
 */
function sb_get_product_linked_course_id( $product_id ) {
	if ( $product_id <= 0 ) {
		return 0;
	}

	if ( function_exists( 'get_field' ) ) {
		$course_id = sb_resolve_linked_post_id( get_field( 'courses_post', $product_id ), 'sfwd-courses' );
		if ( $course_id > 0 ) {
			return $course_id;
		}
	}

	// LearnDash-WooCommerce integration stores the linked course under one of these keys.
	$course_meta_keys = array(
		'_related_course',
		'related_course',
		'_learndash_woocommerce_related_course',
		'learndash_woocommerce_related_course',
		'learndash_woocommerce_courses',
	);

	foreach ( $course_meta_keys as $meta_key ) {
		$course_id = sb_resolve_linked_post_id( get_post_meta( $product_id, $meta_key, true ), 'sfwd-courses' );
		if ( $course_id > 0 ) {
			return $course_id;
		}

		$meta_values = get_post_meta( $product_id, $meta_key, false );
		if ( ! empty( $meta_values ) ) {
			$course_id = sb_resolve_linked_post_id( $meta_values, 'sfwd-courses' );
			if ( $course_id > 0 ) {
				return $course_id;
			}
		}
	}

	return 0;
}

/**
 * The front-end URL for a product: its linked course, else '' (no link).
 */
function sb_get_product_linked_frontend_url( $product_id ) {
	if ( $product_id <= 0 ) {
		return '';
	}

	$course_id = sb_get_product_linked_course_id( $product_id );
	if ( $course_id <= 0 ) {
		return '';
	}

	$course_url = get_permalink( $course_id );

	return $course_url ? $course_url : '';
}

/**
 * Helper: the linked URL for whatever product a cart item wraps (variation → parent).
 */
function sb_cart_item_linked_url( $cart_item ) {
	if ( empty( $cart_item['data'] ) || ! is_a( $cart_item['data'], 'WC_Product' ) ) {
		return '';
	}

	$product    = $cart_item['data'];
	$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

	return sb_get_product_linked_frontend_url( $product_id );
}

/**
 * Cart/checkout: point item links at the linked course page, not the product.
 *
 * With no linked course the item goes unlinked rather than falling back to the
 * product page: single-product pages are not part of this store (LearnDash's
 * add-to-cart sends buyers straight to checkout), so that fallback would drop a
 * shopper on a page the theme never designed. Returning an empty permalink is
 * WooCommerce's own signal for "render the name as plain text", honoured by the
 * classic templates and by the Store API (CartItemSchema applies this filter),
 * so it covers the cart and checkout blocks as well.
 */
function sb_cart_item_permalink_linked_content( $permalink, $cart_item, $cart_item_key ) {
	return sb_cart_item_linked_url( $cart_item );
}
add_filter( 'woocommerce_cart_item_permalink', 'sb_cart_item_permalink_linked_content', 10, 3 );

/**
 * Safety net for templates that build the name markup themselves: rewrite an
 * existing anchor to the linked content, or unwrap it when there is none.
 */
function sb_cart_item_name_linked_content( $product_name, $cart_item, $cart_item_key ) {
	if ( false === stripos( $product_name, '<a ' ) ) {
		return $product_name;
	}

	$linked_url = sb_cart_item_linked_url( $cart_item );

	if ( '' === $linked_url ) {
		return preg_replace( '#<a\b[^>]*>(.*?)</a>#is', '$1', $product_name, 1 );
	}

	return preg_replace( '/href=(["\']).*?\1/i', 'href="' . esc_url( $linked_url ) . '"', $product_name, 1 );
}
add_filter( 'woocommerce_cart_item_name', 'sb_cart_item_name_linked_content', 10, 3 );
