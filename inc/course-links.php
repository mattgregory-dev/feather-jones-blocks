<?php
/**
 * Product → linked-course/event resolution (ported from the classic fj theme).
 *
 * WooCommerce products are sold as enrolment into a LearnDash course (or, legacy,
 * an event). These helpers resolve the linked content and repoint cart/checkout
 * item links at the course/event page instead of the bare product page, so a
 * shopper clicking a cart item lands on the course they're buying.
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
 * The preferred front-end URL for a product: its linked course, else a legacy event, else ''.
 */
function sb_get_product_linked_frontend_url( $product_id ) {
	if ( $product_id <= 0 ) {
		return '';
	}

	$course_id = sb_get_product_linked_course_id( $product_id );
	if ( $course_id > 0 ) {
		$course_url = get_permalink( $course_id );
		if ( $course_url ) {
			return $course_url;
		}
	}

	// Legacy fallback: linked event page.
	if ( function_exists( 'get_field' ) ) {
		$event_id = sb_resolve_linked_post_id( get_field( 'event_post', $product_id ) );
		if ( $event_id > 0 ) {
			$event_url = get_permalink( $event_id );
			if ( $event_url ) {
				return $event_url;
			}
		}
	}

	return '';
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
 * Cart/checkout: point item links at the linked course/event page, not the product.
 */
function sb_cart_item_permalink_linked_content( $permalink, $cart_item, $cart_item_key ) {
	$linked_url = sb_cart_item_linked_url( $cart_item );
	return '' !== $linked_url ? $linked_url : $permalink;
}
add_filter( 'woocommerce_cart_item_permalink', 'sb_cart_item_permalink_linked_content', 10, 3 );

/**
 * Safety net: if the cart item name already contains an anchor, force its href.
 */
function sb_cart_item_name_linked_content( $product_name, $cart_item, $cart_item_key ) {
	$linked_url = sb_cart_item_linked_url( $cart_item );
	if ( '' === $linked_url ) {
		return $product_name;
	}

	if ( false !== stripos( $product_name, '<a ' ) ) {
		$product_name = preg_replace( '/href=(["\']).*?\1/i', 'href="' . esc_url( $linked_url ) . '"', $product_name, 1 );
	}

	return $product_name;
}
add_filter( 'woocommerce_cart_item_name', 'sb_cart_item_name_linked_content', 10, 3 );
