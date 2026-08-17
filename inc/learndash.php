<?php
/**
 * LearnDash front-end wiring (ported from the classic fj theme).
 *
 * Course-overview essentials only: asset-gating (LearnDash enqueues a broad
 * front bundle on every page — keep it only where courses are browsed/consumed),
 * login-link redirect to the account page, and a currency-symbol fallback. The
 * classic theme's admin course-tag-priority machinery is deferred with the
 * /courses/ archive work.
 *
 * @package fj-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is the current request a context that legitimately needs LearnDash assets?
 */
function sb_is_learndash_asset_context() {
	if ( is_admin() || wp_doing_ajax() ) {
		return true;
	}

	// The WooCommerce account dashboard includes course-overview content.
	if ( is_page( 'account' ) ) {
		return true;
	}

	// The course catalog and every course detail URL.
	$request_path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( 'courses' === $request_path || 0 === strpos( $request_path, 'courses/' ) ) {
		return true;
	}

	// Direct lesson/topic/quiz URLs need LearnDash behaviour even off /courses/.
	$learndash_post_types = array(
		'sfwd-courses',
		'sfwd-lessons',
		'sfwd-topic',
		'sfwd-quiz',
		'sfwd-assignment',
		'sfwd-essays',
	);

	return is_singular( $learndash_post_types );
}

/**
 * Strip LearnDash's front bundle everywhere it isn't needed.
 */
function sb_dequeue_learndash_offscreen_assets() {
	if ( sb_is_learndash_asset_context() ) {
		return;
	}

	$style_handles = array(
		'learndash_quiz_front_css',
		'dashicons',
		'learndash',
		'jquery-dropdown-css',
		'learndash_lesson_video',
		'learndash-admin-bar',
		'learndash-course-grid-skin-grid',
		'learndash-course-grid-pagination',
		'learndash-course-grid-filter',
		'learndash-course-grid-card-grid-1',
		'learndash-front',
	);
	foreach ( $style_handles as $handle ) {
		wp_dequeue_style( $handle );
	}

	$script_handles = array(
		'learndash-course-grid-skin-grid',
		'learndash',
		'learndash-main',
		'learndash-breakpoints',
		'learndash-front',
	);
	foreach ( $script_handles as $handle ) {
		wp_dequeue_script( $handle );
	}
}
// LearnDash may enqueue late, so catch enqueue and print time.
add_action( 'wp_enqueue_scripts', 'sb_dequeue_learndash_offscreen_assets', 100 );
add_action( 'wp_print_styles', 'sb_dequeue_learndash_offscreen_assets', 100 );
add_action( 'wp_print_scripts', 'sb_dequeue_learndash_offscreen_assets', 100 );

/**
 * Remove the LearnDash course-grid meta box from posts/pages/products/events.
 */
add_action(
	'do_meta_boxes',
	function () {
		foreach ( array( 'post', 'page', 'product', 'events' ) as $screen ) {
			remove_meta_box( 'learndash-course-grid-meta-box', $screen, 'advanced' );
			remove_meta_box( 'learndash-course-grid-meta-box', $screen, 'normal' );
			remove_meta_box( 'learndash-course-grid-meta-box', $screen, 'side' );
		}
	},
	999
);

/**
 * Point LearnDash login links at the My Account page.
 */
function sb_learndash_login_url( $login_url, $context, $args ) {
	if ( 'login' !== $context ) {
		return $login_url;
	}
	return home_url( '/account/' );
}
add_filter( 'learndash_login_url', 'sb_learndash_login_url', 10, 3 );

/**
 * Course-overview stat bar: time + lesson/topic/quiz counts (shared with the
 * course catalog) followed by the course's `sb_course_attributes`. Rendered via
 * the [sb_course_meta] shortcode so the block template can drop it into the
 * course-detail header band.
 *
 * @return string  <div class="sb-course-meta"> markup, or '' when nothing to show.
 */
function sb_course_meta_shortcode() {
	$course_id = get_the_ID();
	if ( ! $course_id || 'sfwd-courses' !== get_post_type( $course_id ) ) {
		return '';
	}

	$meta_items = array();

	// Time + lesson/topic/quiz counts come from the catalog's shared helper, so
	// the course-detail stat bar and the catalog rows never drift. It returns a
	// ' · '-joined line; we split it back into items for this bar's separators.
	// (The time item uses spaces, not ' · ', so the split is unambiguous.)
	$stat_line = function_exists( 'sb_course_stat_line' ) ? sb_course_stat_line( $course_id ) : '';
	if ( '' !== $stat_line ) {
		foreach ( explode( ' · ', $stat_line ) as $stat ) {
			$meta_items[] = $stat;
		}
	}

	// Course attributes (ACF checkbox) — the labelled qualities shown alongside
	// the stats. Replaces the legacy level / access meta and ld_course_tag path.
	$attributes = function_exists( 'get_field' ) ? get_field( 'sb_course_attributes', $course_id ) : array();
	if ( ! empty( $attributes ) && is_array( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ( '' !== trim( (string) $attribute ) ) {
				$meta_items[] = (string) $attribute;
			}
		}
	}

	if ( empty( $meta_items ) ) {
		return '';
	}

	$spans = '';
	foreach ( $meta_items as $meta_item ) {
		$spans .= '<span class="cm">' . esc_html( $meta_item ) . '</span>';
	}

	return '<div class="sb-course-meta">' . $spans . '</div>';
}
add_shortcode( 'sb_course_meta', 'sb_course_meta_shortcode' );

/**
 * Compact star summary — five stars filled to the rounded average, the numeric
 * average, and the review count — from LearnDash's Course Reviews (ld_review
 * comments). Sits below the title in the course-detail header. Returns '' when
 * the reviews module is unavailable or the course has no approved reviews yet.
 *
 * @param int $course_id Optional course ID; defaults to the current post.
 * @return string  <div class="sb-course-stars"> markup, or '' when there are none.
 */
function sb_course_star_summary( $course_id = 0 ) {
	$course_id = $course_id ? (int) $course_id : (int) get_the_ID();
	if ( ! $course_id || ! function_exists( 'learndash_course_reviews_get_average_review_score' ) ) {
		return '';
	}

	$average = learndash_course_reviews_get_average_review_score( $course_id );
	if ( false === $average ) {
		return '';
	}

	$count = (int) get_comments(
		array(
			'post_id' => $course_id,
			'type'    => 'ld_review',
			'status'  => 'approve',
			'count'   => true,
		)
	);
	if ( $count < 1 ) {
		return '';
	}

	$filled = (int) round( (float) $average );
	$stars  = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$stars .= '<span class="sb-course-star' . ( $i <= $filled ? ' is-filled' : '' ) . '" aria-hidden="true">&#9733;</span>';
	}

	/* translators: %d: number of reviews. */
	$count_label = sprintf( _n( '%d review', '%d reviews', $count, 'fj-blocks' ), $count );

	return sprintf(
		'<div class="sb-course-stars"><span class="sb-course-stars__icons">%1$s</span> <span class="sb-course-stars__score">%2$s</span> <span class="sb-course-stars__count">&middot; %3$s</span></div>',
		$stars,
		esc_html( number_format( (float) $average, 1 ) ),
		esc_html( $count_label )
	);
}

/**
 * [sb_course_stars] — the star summary for the current course. See
 * sb_course_star_summary().
 *
 * @return string
 */
function sb_course_stars_shortcode() {
	return sb_course_star_summary();
}
add_shortcode( 'sb_course_stars', 'sb_course_stars_shortcode' );

/**
 * [sb_course_breadcrumb] — "Courses / <course>" trail for the course-detail
 * header. Uses the `sb_course_short_title` label when set (keeps a long title
 * from overrunning the crumb), else the full title. The "Courses" crumb links to
 * the catalog page when a page with the `courses` slug exists.
 *
 * @return string  <nav class="sb-course-breadcrumb"> markup, or '' off a course.
 */
function sb_course_breadcrumb_shortcode() {
	$course_id = get_the_ID();
	if ( ! $course_id || 'sfwd-courses' !== get_post_type( $course_id ) ) {
		return '';
	}

	$short = function_exists( 'get_field' ) ? trim( (string) get_field( 'sb_course_short_title', $course_id ) ) : '';
	$label = '' !== $short ? $short : get_the_title( $course_id );

	$catalog       = get_page_by_path( 'courses' );
	$courses_crumb = $catalog
		? '<a href="' . esc_url( (string) get_permalink( $catalog ) ) . '">' . esc_html__( 'Courses', 'fj-blocks' ) . '</a>'
		: '<span>' . esc_html__( 'Courses', 'fj-blocks' ) . '</span>';

	return '<nav class="sb-course-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'fj-blocks' ) . '">'
		. $courses_crumb
		. '<span class="sb-course-breadcrumb__sep" aria-hidden="true"> / </span>'
		. '<span class="sb-course-breadcrumb__current" aria-current="page">' . esc_html( $label ) . '</span>'
		. '</nav>';
}
add_shortcode( 'sb_course_breadcrumb', 'sb_course_breadcrumb_shortcode' );

/**
 * Ensure LearnDash prices show a currency symbol even when intl is missing.
 */
function sb_learndash_currency_symbol_fallback( $symbol ) {
	if ( ! function_exists( 'learndash_get_currency_code' ) ) {
		return $symbol;
	}

	$currency_code = strtoupper( trim( learndash_get_currency_code() ) );
	if ( '' === $currency_code ) {
		return $symbol;
	}

	return 'USD' === $currency_code ? '$' : $symbol;
}
add_filter( 'learndash_currency_symbol', 'sb_learndash_currency_symbol_fallback' );
