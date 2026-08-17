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

	// Jump to the reviews: the guest sales layout has our bottom section,
	// the enrolled layout has LearnDash's Reviews tab.
	$has_access = function_exists( 'sfwd_lms_has_access' ) && sfwd_lms_has_access( $course_id, get_current_user_id() );
	$target     = $has_access ? '#ld-tab-reviews' : '#sb-course-reviews';

	return sprintf(
		'<a class="sb-course-stars" href="%4$s"><span class="sb-course-stars__icons">%1$s</span> <span class="sb-course-stars__score">%2$s</span> <span class="sb-course-stars__count">&middot; %3$s</span></a>',
		$stars,
		esc_html( number_format( (float) $average, 1 ) ),
		esc_html( $count_label ),
		esc_attr( $target )
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
 * Breadcrumb label for a post: its `sb_course_short_title` when set (keeps long
 * course / lesson / topic / quiz titles from overrunning the crumb), else the
 * full title. Replicates the classic breadcrumb-shortname override in-theme.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function sb_ld_crumb_label( $post_id ) {
	$short = function_exists( 'get_field' ) ? trim( (string) get_field( 'sb_course_short_title', $post_id ) ) : '';
	return '' !== $short ? $short : get_the_title( $post_id );
}

/**
 * [sb_ld_breadcrumb] — trail for a LearnDash step page (lesson / topic / quiz):
 * Courses / <Course> / [<Lesson>] / <current>, reusing the .sb-course-breadcrumb
 * styling. Each crumb uses its post's `sb_course_short_title` when set; the
 * parent lesson crumb appears for topics and lesson-nested quizzes.
 *
 * @return string
 */
function sb_ld_breadcrumb_shortcode() {
	$post_id = get_the_ID();
	$type    = $post_id ? get_post_type( $post_id ) : '';
	if ( ! in_array( $type, array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ), true ) ) {
		return '';
	}

	$crumbs = array();

	// Courses (catalog).
	$catalog  = get_page_by_path( 'courses' );
	$crumbs[] = array(
		'label' => __( 'Courses', 'fj-blocks' ),
		'url'   => $catalog ? (string) get_permalink( $catalog ) : '',
	);

	// Course.
	$course_id = function_exists( 'learndash_get_course_id' ) ? (int) learndash_get_course_id( $post_id ) : 0;
	if ( $course_id ) {
		$crumbs[] = array(
			'label' => sb_ld_crumb_label( $course_id ),
			'url'   => (string) get_permalink( $course_id ),
		);
	}

	// Parent lesson for topics and lesson-nested quizzes.
	if ( in_array( $type, array( 'sfwd-topic', 'sfwd-quiz' ), true ) && function_exists( 'learndash_get_lesson_id' ) ) {
		$lesson_id = (int) learndash_get_lesson_id( $post_id, $course_id );
		if ( $lesson_id && $lesson_id !== $post_id ) {
			$crumbs[] = array(
				'label' => sb_ld_crumb_label( $lesson_id ),
				'url'   => (string) get_permalink( $lesson_id ),
			);
		}
	}

	// Current step (unlinked).
	$crumbs[] = array(
		'label' => sb_ld_crumb_label( $post_id ),
		'url'   => '',
	);

	$sep    = '<span class="sb-course-breadcrumb__sep" aria-hidden="true"> / </span>';
	$last   = count( $crumbs ) - 1;
	$pieces = array();
	foreach ( $crumbs as $index => $crumb ) {
		if ( '' === (string) $crumb['label'] ) {
			continue;
		}
		if ( $index === $last ) {
			$pieces[] = '<span class="sb-course-breadcrumb__current" aria-current="page">' . esc_html( $crumb['label'] ) . '</span>';
		} elseif ( '' !== $crumb['url'] ) {
			$pieces[] = '<a href="' . esc_url( $crumb['url'] ) . '">' . esc_html( $crumb['label'] ) . '</a>';
		} else {
			$pieces[] = '<span>' . esc_html( $crumb['label'] ) . '</span>';
		}
	}

	return '<nav class="sb-course-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'fj-blocks' ) . '">'
		. implode( $sep, $pieces )
		. '</nav>';
}
add_shortcode( 'sb_ld_breadcrumb', 'sb_ld_breadcrumb_shortcode' );

/**
 * [sb_ld_header] — the mint band for LearnDash step pages (lesson / topic /
 * quiz): title + breadcrumb, mirroring the course-detail header band.
 *
 * @return string
 */
function sb_ld_header_shortcode() {
	$post_id = get_the_ID();
	$type    = $post_id ? get_post_type( $post_id ) : '';
	if ( ! in_array( $type, array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ), true ) ) {
		return '';
	}

	return '<section class="sb-course-band alignfull has-surface-3-background-color has-background has-global-padding">'
		. '<div class="sb-course-band__inner">'
		. '<h1 class="sb-course-band__title">' . esc_html( get_the_title( $post_id ) ) . '</h1>'
		. sb_ld_breadcrumb_shortcode()
		. '</div></section>';
}
add_shortcode( 'sb_ld_header', 'sb_ld_header_shortcode' );

/**
 * Suppress LearnDash's own breadcrumbs on step pages — our [sb_ld_header] band
 * carries the breadcrumb now (with short-name support). Emptying the breadcrumb
 * list makes the modern breadcrumb template render nothing; the legacy (quiz)
 * breadcrumb is hidden in CSS.
 */
add_filter( 'learndash_template_views_breadcrumbs', '__return_empty_array' );

/**
 * Category eyebrow — the course's `ld_course_category` term name(s), linked, as
 * the small brand kicker above the title. Shared by both header layouts.
 *
 * @param int $course_id Course post ID.
 * @return string  <p class="sb-course-eyebrow"> markup, or '' with no terms.
 */
function sb_course_category_eyebrow( $course_id ) {
	$terms = get_the_terms( $course_id, 'ld_course_category' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	$links = array();
	foreach ( $terms as $term ) {
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
	}
	if ( empty( $links ) ) {
		return '';
	}

	return '<p class="sb-course-eyebrow">' . implode( ', ', $links ) . '</p>';
}

/**
 * Course-detail header — branches on enrollment (access), not login, so a
 * logged-in-but-not-enrolled visitor still gets the sales layout.
 *
 * - Enrolled → the mint band (eyebrow, title, star summary, stat bar, breadcrumb).
 * - Guest    → the two-column product header (image | eyebrow, title, star
 *              summary, stat bar, summary, Buy Now).
 *
 * Rendered via [sb_course_header] so the block template stays header → body →
 * footer. The matching body_class (`sb-course--enrolled` / `sb-course--guest`)
 * drives the state-scoped CSS that suppresses LD's guest chrome.
 *
 * @return string
 */
function sb_course_header_shortcode() {
	$course_id = get_the_ID();
	if ( ! $course_id || 'sfwd-courses' !== get_post_type( $course_id ) ) {
		return '';
	}

	$has_access = function_exists( 'sfwd_lms_has_access' )
		&& sfwd_lms_has_access( $course_id, get_current_user_id() );

	return $has_access
		? sb_course_header_enrolled( $course_id )
		: sb_course_header_guest( $course_id );
}
add_shortcode( 'sb_course_header', 'sb_course_header_shortcode' );

/**
 * Enrolled header: the mint band. Full-width surface-3 with a constrained,
 * centered stack.
 *
 * @param int $course_id Course post ID.
 * @return string
 */
function sb_course_header_enrolled( $course_id ) {
	$html  = '<section class="sb-course-band alignfull has-surface-3-background-color has-background has-global-padding">';
	$html .= '<div class="sb-course-band__inner">';
	$html .= sb_course_category_eyebrow( $course_id );
	$html .= '<h1 class="sb-course-band__title">' . esc_html( get_the_title( $course_id ) ) . '</h1>';
	$html .= sb_course_star_summary( $course_id );
	$html .= sb_course_meta_shortcode();
	$html .= sb_course_breadcrumb_shortcode();
	$html .= '</div></section>';

	return $html;
}

/**
 * Guest header: the two-column product/sales header on cream — breadcrumb, then
 * featured image alongside eyebrow, title, star summary, stat bar, summary, and
 * a Buy Now button linking to the course's LearnDash Button URL.
 *
 * @param int $course_id Course post ID.
 * @return string
 */
function sb_course_header_guest( $course_id ) {
	$summary = function_exists( 'get_field' ) ? trim( (string) get_field( 'sb_course_summary', $course_id ) ) : '';
	$buy_url = function_exists( 'learndash_get_setting' ) ? (string) learndash_get_setting( $course_id, 'custom_button_url' ) : '';
	$price   = function_exists( 'sb_course_price' ) ? sb_course_price( $course_id ) : '';

	$html  = '<section class="sb-course-sales alignfull has-surface-1-background-color has-background has-global-padding">';
	$html .= '<div class="sb-course-sales__inner">';
	$html .= sb_course_breadcrumb_shortcode();

	$html .= '<div class="sb-course-sales__grid">';
	$html .= '<div class="sb-course-sales__media">' . sb_course_thumb_html( $course_id ) . '</div>';

	$html .= '<div class="sb-course-sales__info">';
	$html .= sb_course_category_eyebrow( $course_id );
	$html .= '<h1 class="sb-course-sales__title">' . esc_html( get_the_title( $course_id ) ) . '</h1>';
	$html .= sb_course_star_summary( $course_id );
	$html .= sb_course_meta_shortcode();
	if ( '' !== $summary ) {
		$html .= '<p class="sb-course-sales__summary">' . esc_html( $summary ) . '</p>';
	}
	if ( '' !== $buy_url ) {
		$label = 'free' === strtolower( $price ) || '' === $price
			? esc_html__( 'Enroll Now', 'fj-blocks' )
			: esc_html__( 'Buy Now', 'fj-blocks' ) . ' &middot; ' . esc_html( $price );
		$html .= '<div class="sb-course-sales__actions">';
		$html .= '<a class="sb-course-buy" href="' . esc_url( $buy_url ) . '">' . $label . '</a>';
		$html .= '</div>';
	}
	$html .= '</div>'; // .sb-course-sales__info

	$html .= '</div>'; // .sb-course-sales__grid
	$html .= '</div></section>';

	return $html;
}

/**
 * State class on single-course pages: `sb-course--enrolled` when the current
 * user has access, else `sb-course--guest`. Drives the guest-only CSS that hides
 * LearnDash's enroll sidebar and duplicate featured image.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function sb_course_body_class( $classes ) {
	if ( is_singular( 'sfwd-courses' ) ) {
		$course_id  = get_queried_object_id();
		$has_access = function_exists( 'sfwd_lms_has_access' )
			&& sfwd_lms_has_access( $course_id, get_current_user_id() );
		$classes[] = $has_access ? 'sb-course--enrolled' : 'sb-course--guest';
	}
	return $classes;
}
add_filter( 'body_class', 'sb_course_body_class' );

/**
 * Tidy LearnDash's review-list markup: collapse the template's heavy
 * indentation to a single line so a following wpautop can't turn its newlines
 * into stray <br>s. Used on both the guest section and LD's enrolled Reviews
 * tab. (The empty <p></p> tags wpautop leaves behind are hidden in CSS via
 * `p:empty`, since it re-inserts them at render regardless of this pass.)
 *
 * @param string $html Review markup.
 * @return string
 */
function sb_clean_review_markup( $html ) {
	return trim( preg_replace( '/\s+/', ' ', (string) $html ) );
}

/**
 * Apply the same tidy to LearnDash's enrolled Reviews tab (its content is built
 * from the same review templates, so it carries the same <br> / empty-<p> noise).
 *
 * @param array  $tabs      Content tabs.
 * @param string $context   Tab context.
 * @param int    $course_id Course ID.
 * @param int    $user_id   User ID.
 * @return array
 */
function sb_clean_reviews_tab( $tabs, $context = '', $course_id = 0, $user_id = 0 ) {
	if ( ! is_array( $tabs ) ) {
		return $tabs;
	}
	foreach ( $tabs as $index => $tab ) {
		if ( isset( $tab['id'], $tab['content'] ) && 'reviews' === $tab['id'] ) {
			$tabs[ $index ]['content'] = sb_clean_review_markup( $tab['content'] );
		}
	}
	return $tabs;
}
// After LearnDash's own add_reviews_tab (priority 10).
add_filter( 'learndash_content_tabs', 'sb_clean_reviews_tab', 20, 4 );

/**
 * [sb_course_reviews] — guest-only reviews section: LearnDash's approved review
 * list (social proof for the buy decision) plus a "Log in to leave a review"
 * prompt linking to the account page. Enrolled users keep LD's Reviews tab (list
 * + submit form), so this returns '' for them, and also '' when reviews are
 * disabled or the course has none yet.
 *
 * @return string
 */
function sb_course_reviews_shortcode() {
	$course_id = get_the_ID();
	if ( ! $course_id || 'sfwd-courses' !== get_post_type( $course_id ) ) {
		return '';
	}
	// Enrolled users get LD's Reviews tab (with the submit form).
	if ( function_exists( 'sfwd_lms_has_access' ) && sfwd_lms_has_access( $course_id, get_current_user_id() ) ) {
		return '';
	}
	if ( function_exists( 'learndash_course_reviews_is_review_enabled' ) && ! learndash_course_reviews_is_review_enabled( $course_id ) ) {
		return '';
	}
	if ( ! function_exists( 'learndash_course_reviews_locate_template' ) ) {
		return '';
	}

	// Only surface the section when there's something to show.
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

	// LD's review-list template renders its own `.reviews-list > .ld_review`
	// markup, which our Phase 1 review-card CSS already styles.
	ob_start();
	learndash_course_reviews_locate_template( 'review-list.php', array( 'course_id' => $course_id ) );
	$list = (string) ob_get_clean();
	if ( '' === trim( $list ) ) {
		return '';
	}

	$list = sb_clean_review_markup( $list );

	$prompt = '<p class="sb-course-reviews__prompt"><a href="' . esc_url( home_url( '/account/' ) ) . '">'
		. esc_html__( 'Log in to leave a review', 'fj-blocks' )
		. '</a></p>';

	return '<section id="sb-course-reviews" class="sb-course-reviews learndash-course-reviews-container">'
		. '<h2 class="sb-course-reviews__heading">' . esc_html__( 'What learners are saying', 'fj-blocks' ) . '</h2>'
		. $list
		. $prompt
		. '</section>';
}
add_shortcode( 'sb_course_reviews', 'sb_course_reviews_shortcode' );

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
