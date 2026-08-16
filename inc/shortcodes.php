<?php
/**
 * Site shortcodes.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [current_year] — the current four-digit year in the site's timezone, for an
 * evergreen copyright line that needs no annual maintenance. Placed via a
 * Shortcode block (e.g. in the footer).
 *
 * @return string
 */
function sb_current_year_shortcode() {
	return esc_html( wp_date( 'Y' ) );
}
add_shortcode( 'current_year', 'sb_current_year_shortcode' );

/**
 * [sb_post_meta] — the blog post's date and estimated reading time as one line
 * ("April 23, 2026 · 7 min read"), for the single-post band header. Reading time
 * is the content's word count at 200 wpm, floored at one minute; the CSS adds
 * the dot between the two spans. Placed via a Shortcode block in single.html.
 *
 * @return string
 */
function sb_post_meta_shortcode() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$date    = get_the_date( 'F j, Y', $post_id );
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) );
	$minutes = max( 1, (int) ceil( $words / 200 ) );

	return '<div class="sb-post-meta">'
		. '<span class="sb-post-meta__date">' . esc_html( $date ) . '</span>'
		. '<span class="sb-post-meta__read">' . esc_html( sprintf( '%d min read', $minutes ) ) . '</span>'
		. '</div>';
}
add_shortcode( 'sb_post_meta', 'sb_post_meta_shortcode' );

/**
 * [sb_breadcrumb] — a Home / Journal / <current post> trail for the single-post
 * band. "Journal" links to the posts page (Settings → Reading) when one is set,
 * otherwise the site home. Only the current title is unlinked. Placed via a
 * Shortcode block in single.html.
 *
 * @return string
 */
function sb_breadcrumb_shortcode() {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	$blog_id  = (int) get_option( 'page_for_posts' );
	$blog_url = $blog_id ? get_permalink( $blog_id ) : home_url( '/' );
	$sep      = '<span class="sb-breadcrumb__sep" aria-hidden="true">/</span>';

	return '<nav class="sb-breadcrumb" aria-label="Breadcrumb">'
		. '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'fj-blocks' ) . '</a>'
		. $sep
		. '<a href="' . esc_url( $blog_url ) . '">' . esc_html__( 'Journal', 'fj-blocks' ) . '</a>'
		. $sep
		. '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>'
		. '</nav>';
}
add_shortcode( 'sb_breadcrumb', 'sb_breadcrumb_shortcode' );

/**
 * [sb_post_pagination] — previous/next links between adjacent blog posts, each a
 * stacked direction label over the post title. A side renders only when that
 * neighbour exists; a lone next link stays pinned right via an empty spacer.
 * Placed via a Shortcode block in single.html; styled by _single-post.scss.
 *
 * @return string
 */
function sb_post_pagination_shortcode() {
	$prev = get_previous_post();
	$next = get_next_post();

	if ( empty( $prev ) && empty( $next ) ) {
		return '';
	}

	$out = '<nav class="sb-post-pagination" aria-label="' . esc_attr__( 'Post navigation', 'fj-blocks' ) . '">';

	if ( $prev ) {
		$out .= '<a class="sb-post-pagination__link is-prev" href="' . esc_url( (string) get_permalink( $prev ) ) . '">'
			. '<span class="sb-post-pagination__dir">&#8592;&nbsp;' . esc_html__( 'Previous', 'fj-blocks' ) . '</span>'
			. '<span class="sb-post-pagination__title">' . esc_html( get_the_title( $prev ) ) . '</span>'
			. '</a>';
	} else {
		$out .= '<span></span>';
	}

	if ( $next ) {
		$out .= '<a class="sb-post-pagination__link is-next" href="' . esc_url( (string) get_permalink( $next ) ) . '">'
			. '<span class="sb-post-pagination__dir">' . esc_html__( 'Next', 'fj-blocks' ) . '&nbsp;&#8594;</span>'
			. '<span class="sb-post-pagination__title">' . esc_html( get_the_title( $next ) ) . '</span>'
			. '</a>';
	}

	$out .= '</nav>';

	return $out;
}
add_shortcode( 'sb_post_pagination', 'sb_post_pagination_shortcode' );

/**
 * [sb_author_avatar size="64"] — the post author's avatar for the bio block. The
 * site stores the photo in an ACF user Image field (user_profile_image), not the
 * WordPress gravatar, so read that first; fall back to get_avatar() if the field
 * is empty or ACF is inactive. Handles ACF's array / ID / URL return formats.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function sb_author_avatar_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'size' => 64 ), $atts, 'sb_author_avatar' );
	$size = max( 1, (int) $atts['size'] );

	$author_id = (int) get_post_field( 'post_author', get_the_ID() );
	if ( ! $author_id ) {
		return '';
	}

	$alt     = get_the_author_meta( 'display_name', $author_id );
	$img_url = '';

	if ( function_exists( 'get_field' ) ) {
		$field = get_field( 'user_profile_image', 'user_' . $author_id );

		if ( is_array( $field ) ) {
			if ( ! empty( $field['sizes']['thumbnail'] ) ) {
				$img_url = $field['sizes']['thumbnail'];
			} elseif ( ! empty( $field['url'] ) ) {
				$img_url = $field['url'];
			}
			if ( ! empty( $field['alt'] ) ) {
				$alt = $field['alt'];
			}
		} elseif ( is_numeric( $field ) ) {
			$src = wp_get_attachment_image_url( (int) $field, array( $size * 2, $size * 2 ) );
			if ( $src ) {
				$img_url = $src;
			}
		} elseif ( is_string( $field ) && '' !== $field ) {
			$img_url = $field;
		}
	}

	// No ACF image set — fall back to the WordPress avatar.
	if ( '' === $img_url ) {
		return get_avatar( $author_id, $size, '', $alt, array( 'class' => 'sb-author-bio__avatar' ) );
	}

	return sprintf(
		'<img class="sb-author-bio__avatar" src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async">',
		esc_url( $img_url ),
		esc_attr( $alt ),
		$size,
		$size
	);
}
add_shortcode( 'sb_author_avatar', 'sb_author_avatar_shortcode' );

/**
 * [sb_course_catalog] — the dynamic course catalog (Courses page). A nested loop:
 * LearnDash course categories (ordered by the ACF term number sb_course_cat_order),
 * each a series bar (name + term description) over its courses. Within a category,
 * a featured course sorts first, then by sb_course_order, then title. Every row is
 * a whole-card link to the course. Emits the .sb-course-* markup styled by
 * _learndash.scss. Placed via a Shortcode block on the Courses page.
 *
 * @return string
 */
function sb_course_catalog_shortcode() {
	if ( ! post_type_exists( 'sfwd-courses' ) ) {
		return '';
	}

	$taxonomy = 'ld_course_category';
	$terms    = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	// Order categories by the ACF term number, then name.
	foreach ( $terms as $term ) {
		$order          = function_exists( 'get_field' ) ? get_field( 'sb_course_cat_order', $term ) : '';
		$term->sb_order = is_numeric( $order ) ? (int) $order : PHP_INT_MAX;
	}
	usort(
		$terms,
		static function ( $a, $b ) {
			return $a->sb_order === $b->sb_order ? strcasecmp( $a->name, $b->name ) : $a->sb_order <=> $b->sb_order;
		}
	);

	$out = '<div class="sb-course-catalog">';

	foreach ( $terms as $term ) {
		$courses = get_posts(
			array(
				'post_type'      => 'sfwd-courses',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					),
				),
			)
		);
		if ( empty( $courses ) ) {
			continue; // Omit a category with no published courses.
		}

		// Featured first, then sb_course_order, then title.
		foreach ( $courses as $course ) {
			$course->sb_featured = sb_course_is_featured( $course->ID );
			$order               = function_exists( 'get_field' ) ? get_field( 'sb_course_order', $course->ID ) : '';
			$course->sb_order    = is_numeric( $order ) ? (int) $order : PHP_INT_MAX;
		}
		usort(
			$courses,
			static function ( $a, $b ) {
				if ( $a->sb_featured !== $b->sb_featured ) {
					return $a->sb_featured ? -1 : 1;
				}
				return $a->sb_order === $b->sb_order
					? strcasecmp( get_the_title( $a ), get_the_title( $b ) )
					: $a->sb_order <=> $b->sb_order;
			}
		);

		$note = trim( wp_strip_all_tags( (string) $term->description ) );

		$out .= '<div class="sb-course-series-block">';
		$out .= '<div class="sb-course-series">';
		$out .= '<h2 class="sb-course-series__title">' . esc_html( $term->name ) . '</h2>';
		if ( '' !== $note ) {
			$out .= '<p class="sb-course-series__note">' . esc_html( $note ) . '</p>';
		}
		$out .= '</div>';

		$out .= '<div class="sb-course-list">';
		foreach ( $courses as $course ) {
			$out .= sb_course_catalog_row( $course );
		}
		$out .= '</div></div>';
	}

	$out .= '</div>';

	return $out;
}
add_shortcode( 'sb_course_catalog', 'sb_course_catalog_shortcode' );

/**
 * Whether a course is flagged featured (ACF True/False field sb_course_featured).
 *
 * @param int $course_id Course post ID.
 * @return bool
 */
function sb_course_is_featured( $course_id ) {
	if ( function_exists( 'get_field' ) ) {
		return (bool) get_field( 'sb_course_featured', $course_id );
	}
	return ! empty( get_post_meta( $course_id, 'sb_course_featured', true ) );
}

/**
 * One catalog row: a whole-card link to the course. Thumb | info (pill, title,
 * short description, stat bar) | side (price, View Course).
 *
 * @param WP_Post $course Course post (may carry a ->sb_featured flag from the sort).
 * @return string
 */
function sb_course_catalog_row( $course ) {
	$course_id = $course->ID;
	$link      = get_permalink( $course_id );
	$title     = get_the_title( $course_id );
	$featured  = isset( $course->sb_featured ) ? (bool) $course->sb_featured : sb_course_is_featured( $course_id );

	$summary = function_exists( 'get_field' ) ? trim( (string) get_field( 'sb_course_summary', $course_id ) ) : '';
	$stats   = sb_course_stat_line( $course_id );
	$price   = sb_course_price( $course_id );

	$row  = '<a class="sb-course-row' . ( $featured ? ' is-featured' : '' ) . '" href="' . esc_url( $link ) . '">';
	$row .= '<span class="sb-course-thumb">' . sb_course_thumb_html( $course_id ) . '</span>';

	$row .= '<span class="sb-course-info">';
	if ( $featured ) {
		// Optional custom pill text (sb_course_featured_label); "Featured" otherwise.
		$pill_label = function_exists( 'get_field' ) ? trim( (string) get_field( 'sb_course_featured_label', $course_id ) ) : '';
		if ( '' === $pill_label ) {
			$pill_label = __( 'Featured', 'fj-blocks' );
		}
		$row .= '<span class="sb-course-pill">' . esc_html( $pill_label ) . '</span>';
	}
	$row .= '<h3 class="sb-course-title">' . esc_html( $title ) . '</h3>';
	if ( '' !== $summary ) {
		$row .= '<p class="sb-course-desc">' . esc_html( $summary ) . '</p>';
	}
	if ( '' !== $stats ) {
		$row .= '<span class="sb-course-row-meta"><span class="sb-course-stats">' . esc_html( $stats ) . '</span></span>';
	}
	$row .= '</span>';

	$row .= '<span class="sb-course-side">';
	if ( '' !== $price ) {
		$row .= '<span class="sb-course-price">' . esc_html( $price ) . '</span>';
	}
	$row .= '<span class="sb-course-cta">' . esc_html__( 'View Course', 'fj-blocks' ) . ' &rarr;</span>';
	$row .= '</span>';

	$row .= '</a>';

	return $row;
}

/**
 * Course thumbnail <img>, resolved by fallback chain: ACF sb_course_thumbnail →
 * post featured image → bundled placeholder. Always an <img> so the grid holds.
 *
 * @param int $course_id Course post ID.
 * @return string
 */
function sb_course_thumb_html( $course_id ) {
	$url = '';
	$alt = '';

	// 1. ACF image (handles Array / ID / URL return formats).
	if ( function_exists( 'get_field' ) ) {
		$field = get_field( 'sb_course_thumbnail', $course_id );
		if ( is_array( $field ) ) {
			$url = ! empty( $field['sizes']['medium_large'] ) ? $field['sizes']['medium_large'] : ( $field['url'] ?? '' );
			$alt = $field['alt'] ?? '';
		} elseif ( is_numeric( $field ) ) {
			$url = (string) wp_get_attachment_image_url( (int) $field, 'medium_large' );
			$alt = (string) get_post_meta( (int) $field, '_wp_attachment_image_alt', true );
		} elseif ( is_string( $field ) && '' !== $field ) {
			$url = $field;
		}
	}

	// 2. Post featured image.
	if ( '' === $url ) {
		$thumb_id = get_post_thumbnail_id( $course_id );
		if ( $thumb_id ) {
			$url = (string) wp_get_attachment_image_url( $thumb_id, 'medium_large' );
			$alt = (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
		}
	}

	// 3. Bundled placeholder (decorative; the title carries the meaning).
	if ( '' === $url ) {
		$url = get_template_directory_uri() . '/assets/images/course-placeholder.svg';
		$alt = '';
	}

	return sprintf(
		'<img src="%s" alt="%s" loading="lazy" decoding="async">',
		esc_url( $url ),
		esc_attr( $alt )
	);
}

/**
 * The stat line for a course — time (hours/minutes), lessons, topics, quizzes —
 * spelled out and pluralized, joined by " · ". Any zero/empty segment is omitted.
 *
 * @param int $course_id Course post ID.
 * @return string
 */
function sb_course_stat_line( $course_id ) {
	$parts = array();

	$hours   = function_exists( 'get_field' ) ? max( 0, (int) get_field( 'sb_course_hours', $course_id ) ) : 0;
	$minutes = function_exists( 'get_field' ) ? max( 0, (int) get_field( 'sb_course_minutes', $course_id ) ) : 0;

	if ( $hours > 0 && $minutes > 0 ) {
		$parts[] = sprintf(
			/* translators: 1: hours, 2: hour/hours, 3: minutes, 4: minute/minutes */
			'%1$d %2$s %3$d %4$s',
			$hours,
			_n( 'hour', 'hours', $hours, 'fj-blocks' ),
			$minutes,
			_n( 'minute', 'minutes', $minutes, 'fj-blocks' )
		);
	} elseif ( $hours > 0 ) {
		$parts[] = sprintf( '%d %s', $hours, _n( 'hour', 'hours', $hours, 'fj-blocks' ) );
	} elseif ( $minutes > 0 ) {
		$parts[] = sprintf( '%d %s', $minutes, _n( 'minute', 'minutes', $minutes, 'fj-blocks' ) );
	}

	$counts = sb_course_step_counts( $course_id );
	if ( $counts['lessons'] > 0 ) {
		$parts[] = $counts['lessons'] . ' ' . _n( 'lesson', 'lessons', $counts['lessons'], 'fj-blocks' );
	}
	if ( $counts['topics'] > 0 ) {
		$parts[] = $counts['topics'] . ' ' . _n( 'topic', 'topics', $counts['topics'], 'fj-blocks' );
	}
	if ( $counts['quizzes'] > 0 ) {
		$parts[] = $counts['quizzes'] . ' ' . _n( 'quiz', 'quizzes', $counts['quizzes'], 'fj-blocks' );
	}

	return implode( ' · ', $parts );
}

/**
 * Lesson / topic / quiz counts for a course, via the LearnDash step post types
 * joined on their `course_id` meta. Lessons named like helper steps
 * (completion / introduction) are skipped. Returns zeros without LearnDash.
 *
 * @param int $course_id Course post ID.
 * @return array{lessons:int,topics:int,quizzes:int}
 */
function sb_course_step_counts( $course_id ) {
	$counts = array(
		'lessons' => 0,
		'topics'  => 0,
		'quizzes' => 0,
	);
	if ( ! function_exists( 'learndash_get_post_type_slug' ) ) {
		return $counts;
	}

	$base = array(
		'post_status'            => 'publish',
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'course_id',
				'value'   => (int) $course_id,
				'compare' => '=',
			),
		),
	);

	$lessons = get_posts( array_merge( $base, array( 'post_type' => learndash_get_post_type_slug( 'lesson' ) ) ) );
	foreach ( $lessons as $lesson_id ) {
		$slug = (string) get_post_field( 'post_name', $lesson_id );
		if ( false !== strpos( $slug, 'completion' ) || false !== strpos( $slug, 'introduction' ) ) {
			continue;
		}
		++$counts['lessons'];
	}

	$counts['topics']  = count( get_posts( array_merge( $base, array( 'post_type' => learndash_get_post_type_slug( 'topic' ) ) ) ) );
	$counts['quizzes'] = count( get_posts( array_merge( $base, array( 'post_type' => learndash_get_post_type_slug( 'quiz' ) ) ) ) );

	return $counts;
}

/**
 * A course's display price as "$" + whole dollars, or '' when free/empty (which
 * the row then omits). Uses LearnDash's resolved course price.
 *
 * @param int $course_id Course post ID.
 * @return string
 */
function sb_course_price( $course_id ) {
	if ( ! function_exists( 'learndash_get_course_price' ) ) {
		return '';
	}

	$pricing = learndash_get_course_price( $course_id );
	$type    = isset( $pricing['type'] ) ? $pricing['type'] : '';
	$price   = isset( $pricing['price'] ) ? $pricing['price'] : '';

	if ( 'free' === $type || '' === $price || null === $price ) {
		return '';
	}

	$amount = (float) preg_replace( '/[^0-9.]/', '', (string) $price );
	if ( $amount <= 0 ) {
		return '';
	}

	return '$' . number_format( $amount, 0 );
}
