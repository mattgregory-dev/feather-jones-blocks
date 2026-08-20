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

	$included_map = sb_course_included_map();

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
			$out .= sb_course_catalog_row( $course, $included_map );
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
 * Map of course ID → "included" chip label, built in one pass over every
 * course's sb_course_included relationship. A child course inherits the label of
 * the parent that lists it (the parent's sb_course_included_label, or a default
 * when unset). First parent wins; self-references are skipped.
 *
 * @return array<int,string>
 */
function sb_course_included_map() {
	$map = array();
	if ( ! function_exists( 'get_field' ) ) {
		return $map;
	}

	$parents = get_posts(
		array(
			'post_type'      => 'sfwd-courses',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	foreach ( $parents as $parent_id ) {
		$children = get_field( 'sb_course_included', $parent_id );
		if ( empty( $children ) ) {
			continue;
		}

		$label = trim( (string) get_field( 'sb_course_included_label', $parent_id ) );
		if ( '' === $label ) {
			$label = __( 'Included in the Complete Course', 'fj-blocks' );
		}

		foreach ( (array) $children as $child ) {
			$child_id = is_object( $child ) ? (int) $child->ID : (int) $child;
			if ( $child_id && $child_id !== (int) $parent_id && ! isset( $map[ $child_id ] ) ) {
				$map[ $child_id ] = $label; // First parent wins.
			}
		}
	}

	return $map;
}

/**
 * One catalog row: a whole-card link to the course. Thumb | info (pill, title,
 * short description, stat bar) | side (price, View Course).
 *
 * @param WP_Post $course Course post (may carry a ->sb_featured flag from the sort).
 * @return string
 */
function sb_course_catalog_row( $course, $included_map = array() ) {
	$course_id = $course->ID;
	$link      = get_permalink( $course_id );
	$title     = get_the_title( $course_id );
	$featured  = isset( $course->sb_featured ) ? (bool) $course->sb_featured : sb_course_is_featured( $course_id );

	$summary        = function_exists( 'get_field' ) ? trim( (string) get_field( 'sb_course_summary', $course_id ) ) : '';
	$stats          = sb_course_stat_line( $course_id );
	$price          = sb_course_price( $course_id );
	$included_label = isset( $included_map[ $course_id ] ) ? $included_map[ $course_id ] : '';

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
	if ( '' !== $stats || '' !== $included_label ) {
		$row .= '<span class="sb-course-row-meta">';
		if ( '' !== $stats ) {
			$row .= '<span class="sb-course-stats">' . esc_html( $stats ) . '</span>';
		}
		if ( '' !== $included_label ) {
			$row .= '<span class="sb-course-chip">' . esc_html( $included_label ) . '</span>';
		}
		$row .= '</span>';
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
 * @param int  $course_id Course post ID.
 * @param bool $compact   Stop after lessons — for the narrow related-course
 *                        cards, where the full line wraps to three lines.
 * @return string
 */
function sb_course_stat_line( $course_id, $compact = false ) {
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
	if ( $compact ) {
		return implode( ' · ', $parts );
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

/**
 * Mobile-drawer footer: the Cart + account buttons, then the social circles.
 *
 * This lives in PHP rather than in the header template part because the account
 * button is auth-aware: logged out it offers Login, logged in it becomes Logout,
 * and a logout URL carries a per-user nonce that static block markup cannot
 * hold. Everything else is the markup the template part used to carry, moved
 * verbatim so the drawer keeps one source of truth. Styling and the state
 * treatments (filled/outlined) live in _header.scss.
 *
 * @return string Footer markup.
 */
function sb_drawer_footer_shortcode() {
	if ( is_user_logged_in() ) {
		$account_url   = wp_logout_url( home_url( '/' ) );
		$account_label = __( 'Logout', 'fj-blocks' );
	} else {
		$account_url   = home_url( '/account/' );
		$account_label = __( 'Login', 'fj-blocks' );
	}

	$cart_icon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';

	$socials = <<<'HTML'
<p class="mobile-drawer__eyebrow mobile-drawer__eyebrow--footer">Find Feather</p>
<div class="mobile-drawer__socials">
<a class="mobile-drawer__social" href="https://www.facebook.com/profile.php?id=100064034190506" aria-label="Facebook" target="_blank" rel="noopener">
<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.09 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.09 24 18.1 24 12.07z"/></svg>
</a>
<a class="mobile-drawer__social" href="https://www.instagram.com/jones.feather/" aria-label="Instagram" target="_blank" rel="noopener">
<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41 1.27-.06 1.65-.07 4.85-.07M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.31-1.46.72-2.13 1.38C1.35 2.68.94 3.35.63 4.14.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.79.72 1.46 1.38 2.13.67.66 1.34 1.07 2.13 1.38.76.3 1.64.5 2.91.56 1.28.06 1.69.07 4.95.07s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56.79-.31 1.46-.72 2.13-1.38.66-.67 1.07-1.34 1.38-2.13.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91-.31-.79-.72-1.46-1.38-2.13C21.32 1.35 20.65.94 19.86.63c-.76-.3-1.64-.5-2.91-.56C15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1 0 18.16 12 6.16 6.16 0 0 0 12 5.84zm0 10.15A3.99 3.99 0 1 1 16 12a3.99 3.99 0 0 1-4 3.99zm7.85-10.4a1.44 1.44 0 1 1-1.44-1.44 1.44 1.44 0 0 1 1.44 1.44z"/></svg>
</a>
<a class="mobile-drawer__social" href="https://www.youtube.com/@featherjonescanyonspiritbo5336" aria-label="YouTube" target="_blank" rel="noopener">
<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.5A3.02 3.02 0 0 0 .5 6.19 31.6 31.6 0 0 0 0 12a31.6 31.6 0 0 0 .5 5.81 3.02 3.02 0 0 0 2.12 2.14c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3.02 3.02 0 0 0 2.12-2.14A31.6 31.6 0 0 0 24 12a31.6 31.6 0 0 0-.5-5.81zM9.55 15.57V8.43L15.82 12z"/></svg>
</a>
</div>
HTML;

	return sprintf(
		'<div class="mobile-drawer__footer"><div class="mobile-drawer__actions">' .
		'<a class="mobile-drawer__btn mobile-drawer__btn--cart" href="%1$s">%2$s<span>%3$s</span></a>' .
		'<a class="mobile-drawer__btn mobile-drawer__btn--login" href="%4$s">%5$s</a>' .
		'</div>%6$s</div>',
		esc_url( home_url( '/cart/' ) ),
		$cart_icon,
		esc_html__( 'Cart', 'fj-blocks' ),
		esc_url( $account_url ),
		esc_html( $account_label ),
		$socials
	);
}
add_shortcode( 'sb_drawer_footer', 'sb_drawer_footer_shortcode' );

/**
 * "Keep Exploring" — a 4-up grid of other courses, for the foot of a single
 * course page.
 *
 * The selection is random on every load and carries no relatedness logic: with
 * a catalog this size any other course is a reasonable next step, and rotating
 * them gives every course exposure. Only the course being viewed is excluded.
 * Cards reuse the catalog's field helpers (thumbnail, stats, price) so the two
 * listings can never disagree about a course.
 *
 * @return string Section markup, or '' outside a single course / with nothing to show.
 */
function sb_related_courses_shortcode() {
	if ( ! is_singular( 'sfwd-courses' ) ) {
		return '';
	}

	// Guests and logged-in visitors without access see this; someone already
	// enrolled is here to study, not to shop, so it renders nothing for them
	// (and skips the query). The band wrapper is hidden alongside it in
	// _related-courses.scss, keyed off the same access state.
	if ( function_exists( 'sfwd_lms_has_access' )
		&& sfwd_lms_has_access( get_the_ID(), get_current_user_id() ) ) {
		return '';
	}

	$courses = get_posts(
		array(
			'post_type'        => 'sfwd-courses',
			'post_status'      => 'publish',
			'posts_per_page'   => 4,
			'post__not_in'     => array( get_the_ID() ),
			'orderby'          => 'rand',
			'suppress_filters' => false,
		)
	);
	if ( empty( $courses ) ) {
		return '';
	}

	// The "All Courses" link is emitted twice: beside the heading on wider
	// screens, and after the cards on the narrowest ones, where it no longer
	// fits on the heading row. Exactly one is visible at any width (see
	// _related-courses.scss), so the hidden copy is inert for assistive tech.
	$all_link = '<a class="sb-related__all" href="' . esc_url( home_url( '/courses/' ) ) . '">'
		. esc_html__( 'All Courses', 'fj-blocks' ) . ' &rarr;</a>';

	$out  = '<section class="sb-related" aria-label="' . esc_attr__( 'Related courses', 'fj-blocks' ) . '">';
	$out .= '<div class="sb-related__header">';
	$out .= '<h2 class="sb-related__title">' . esc_html__( 'Keep Exploring', 'fj-blocks' ) . '</h2>';
	$out .= '<div class="sb-related__all-wrap">' . $all_link . '</div>';
	$out .= '</div>';

	$out .= '<div class="sb-related__grid">';
	foreach ( $courses as $course ) {
		$out .= sb_related_course_card( $course );
	}
	$out .= '</div>';

	$out .= '<div class="sb-related__all-wrap sb-related__all-wrap--foot">' . $all_link . '</div>';
	$out .= '</section>';

	return $out;
}
add_shortcode( 'sb_related_courses', 'sb_related_courses_shortcode' );

/**
 * One related-course card: whole-card link, thumbnail over series / title /
 * compact stats, with price and a View cue on the footer rule.
 *
 * @param WP_Post $course Course post.
 * @return string
 */
function sb_related_course_card( $course ) {
	$course_id = $course->ID;
	$stats     = sb_course_stat_line( $course_id, true );
	$price     = sb_course_price( $course_id );
	$terms     = get_the_terms( $course_id, 'ld_course_category' );
	$series    = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

	$card  = '<a class="sb-related-card" href="' . esc_url( get_permalink( $course_id ) ) . '">';
	$card .= '<span class="sb-related-card__photo">' . sb_course_thumb_html( $course_id ) . '</span>';

	$card .= '<span class="sb-related-card__body">';
	if ( '' !== $series ) {
		$card .= '<span class="sb-related-card__series">' . esc_html( $series ) . '</span>';
	}
	$card .= '<span class="sb-related-card__title">' . esc_html( get_the_title( $course_id ) ) . '</span>';
	if ( '' !== $stats ) {
		$card .= '<span class="sb-related-card__stats">' . esc_html( $stats ) . '</span>';
	}

	$card .= '<span class="sb-related-card__foot">';
	if ( '' !== $price ) {
		$card .= '<span class="sb-related-card__price">' . esc_html( $price ) . '</span>';
	}
	$card .= '<span class="sb-related-card__cta">' . esc_html__( 'View', 'fj-blocks' ) . ' &rarr;</span>';
	$card .= '</span>';

	$card .= '</span></a>';

	return $card;
}
