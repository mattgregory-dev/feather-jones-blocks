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
 * Course-overview meta bar: hours/minutes, lesson/topic/quiz counts, level,
 * access, and priority-sorted course tags. Ported from the classic theme's
 * single-sfwd-courses.php. Rendered via the [sb_course_meta] shortcode so the
 * block template can drop it between the title and the LearnDash content.
 *
 * @return string  <div class="sb-course-meta"> markup, or '' when nothing to show.
 */
function sb_course_meta_shortcode() {
	$course_id = get_the_ID();
	if ( ! $course_id || 'sfwd-courses' !== get_post_type( $course_id ) ) {
		return '';
	}

	$meta_items = array();

	$hours_raw   = function_exists( 'get_field' ) ? get_field( 'course_hours', $course_id ) : get_post_meta( $course_id, 'course_hours', true );
	$minutes_raw = function_exists( 'get_field' ) ? get_field( 'course_minutes', $course_id ) : get_post_meta( $course_id, 'course_minutes', true );
	$hours       = max( 0, is_numeric( $hours_raw ) ? (int) $hours_raw : 0 );
	$minutes     = max( 0, is_numeric( $minutes_raw ) ? (int) $minutes_raw : 0 );

	if ( $hours > 0 && $minutes > 0 ) {
		$meta_items[] = sprintf( '%1$d %2$s %3$d %4$s', $hours, _n( 'hour', 'hours', $hours ), $minutes, _n( 'minute', 'minutes', $minutes ) );
	} elseif ( $minutes > 0 ) {
		$meta_items[] = sprintf( '%1$d %2$s', $minutes, _n( 'minute', 'minutes', $minutes ) );
	} elseif ( $hours > 0 ) {
		$meta_items[] = sprintf( '%1$d %2$s', $hours, _n( 'hour', 'hours', $hours ) );
	}

	if ( function_exists( 'learndash_get_post_type_slug' ) ) {
		$count_base = array(
			'post_status'            => 'publish',
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'course_id',
					'value'   => absint( $course_id ),
					'compare' => '=',
				),
			),
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$lessons_query = new WP_Query(
			array_merge(
				$count_base,
				array(
					'post_type'      => learndash_get_post_type_slug( 'lesson' ),
					'posts_per_page' => -1,
					'no_found_rows'  => true,
				)
			)
		);
		$topics_query  = new WP_Query( array_merge( $count_base, array( 'post_type' => learndash_get_post_type_slug( 'topic' ), 'posts_per_page' => 1 ) ) );
		$quizzes_query = new WP_Query( array_merge( $count_base, array( 'post_type' => learndash_get_post_type_slug( 'quiz' ), 'posts_per_page' => 1 ) ) );

		// Structural lessons (completion / introduction) aren't real curriculum.
		$lessons_count = 0;
		foreach ( (array) $lessons_query->posts as $lesson_id ) {
			$lesson_slug = get_post_field( 'post_name', $lesson_id );
			if ( false !== strpos( $lesson_slug, 'completion' ) || false !== strpos( $lesson_slug, 'introduction' ) ) {
				continue;
			}
			$lessons_count++;
		}
		$topics_count  = (int) $topics_query->found_posts;
		$quizzes_count = (int) $quizzes_query->found_posts;
		wp_reset_postdata();

		if ( $lessons_count > 0 ) {
			$meta_items[] = $lessons_count . ' ' . _n( 'Lesson', 'Lessons', $lessons_count );
		}
		if ( $topics_count > 0 ) {
			$meta_items[] = $topics_count . ' ' . _n( 'Topic', 'Topics', $topics_count );
		}
		if ( $quizzes_count > 0 ) {
			$meta_items[] = $quizzes_count . ' ' . _n( 'Quiz', 'Quizzes', $quizzes_count );
		}
	}

	$course_level = get_post_meta( $course_id, 'course_level', true );
	if ( $course_level ) {
		$meta_items[] = $course_level;
	}
	$course_access = get_post_meta( $course_id, 'course_access', true );
	if ( $course_access ) {
		$meta_items[] = $course_access;
	}

	$course_tags = get_the_terms( $course_id, 'ld_course_tag' );
	if ( ! empty( $course_tags ) && ! is_wp_error( $course_tags ) ) {
		$tag_items = array();
		foreach ( $course_tags as $course_tag ) {
			$priority    = get_term_meta( $course_tag->term_id, 'ld_course_tag_priority', true );
			$tag_items[] = array(
				'name'     => $course_tag->name,
				'priority' => is_numeric( $priority ) ? (int) $priority : 9999,
			);
		}
		usort(
			$tag_items,
			function ( $a, $b ) {
				return $a['priority'] === $b['priority'] ? strcasecmp( $a['name'], $b['name'] ) : $a['priority'] <=> $b['priority'];
			}
		);
		foreach ( $tag_items as $tag_item ) {
			if ( ! empty( $tag_item['name'] ) ) {
				$meta_items[] = $tag_item['name'];
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
