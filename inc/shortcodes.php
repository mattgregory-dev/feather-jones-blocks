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
