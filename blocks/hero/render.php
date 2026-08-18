<?php
/**
 * Hero — server render. A sibling of Spotlight.
 *
 * A spotlight-style two-column split (text beside an optional column image),
 * plus an optional full-cover background image with an alpha-capable overlay
 * tint. Column image, background image, and overlay are each independent — any
 * combination is valid, and with no background the block renders exactly like a
 * spotlight.
 *
 * `imagePosition` / `verticalAlignment` behave as in spotlight (moot when no
 * column image is set). The title is the typed `title`; `level` chooses the
 * heading tag (h1 for a page hero, h2 for a mid-page feature). An empty title
 * renders no heading; an empty eyebrow renders no eyebrow.
 *
 * @package starter-blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks markup (.hero__body).
 */

$hero_level    = ( isset( $attributes['level'] ) && 'h1' === $attributes['level'] ) ? 'h1' : 'h2';
$hero_eyebrow  = trim( $attributes['eyebrow'] ?? '' );
$hero_position = ( isset( $attributes['imagePosition'] ) && 'left' === $attributes['imagePosition'] ) ? 'left' : 'right';
$hero_valign   = ( isset( $attributes['verticalAlignment'] ) && 'top' === $attributes['verticalAlignment'] ) ? 'top' : 'center';
$hero_image_id = isset( $attributes['imageId'] ) ? (int) $attributes['imageId'] : 0;
$hero_alt      = trim( $attributes['imageAlt'] ?? '' );
$hero_title    = trim( $attributes['title'] ?? '' );

$hero_bg_id  = isset( $attributes['backgroundImageId'] ) ? (int) $attributes['backgroundImageId'] : 0;
$hero_bg_url = $hero_bg_id ? wp_get_attachment_image_url( $hero_bg_id, 'full' ) : '';

// Solid background color — user input landing in a style attribute, so accept
// only a hex (3/4/6/8-digit) or rgb()/rgba() string; anything else is dropped.
// Independent of the image: it paints the section, and a cover image (if set)
// simply layers on top — so it doubles as both a standalone fill and a fallback.
$hero_bg_color_raw = isset( $attributes['backgroundColor'] ) ? trim( (string) $attributes['backgroundColor'] ) : '';
$hero_bg_color     = '';
if ( '' !== $hero_bg_color_raw
	&& preg_match( '/^(#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})|rgba?\(\s*[0-9.,%\s\/]+\))$/i', $hero_bg_color_raw )
) {
	$hero_bg_color = $hero_bg_color_raw;
}

// Overlay is user input landing in a style attribute. Accept only a hex
// (3/4/6/8-digit) or rgb()/rgba() string, and only when a background exists
// (an overlay over nothing is meaningless). Anything else is dropped, never
// echoed raw. The allowed charset inside rgb()/rgba() can't break out of the
// value.
$hero_overlay_raw = isset( $attributes['overlayColor'] ) ? trim( (string) $attributes['overlayColor'] ) : '';
$hero_overlay     = '';
if ( $hero_bg_url && '' !== $hero_overlay_raw
	&& preg_match( '/^(#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})|rgba?\(\s*[0-9.,%\s\/]+\))$/i', $hero_overlay_raw )
) {
	$hero_overlay = $hero_overlay_raw;
}

$hero_media = '';
if ( $hero_image_id ) {
	$hero_media = '<figure class="hero__media">'
		. wp_get_attachment_image( $hero_image_id, 'full', false, array( 'alt' => $hero_alt ) )
		. '</figure>';
}

// The background image is also rendered as an <img> band, hidden on desktop and
// shown below 800px (for a cover hero with no column image) so the photo reads as
// a real image on top of the copy instead of a full-cover backdrop behind it.
// Decorative alt: it duplicates the CSS background and the copy carries the
// message.
$hero_bg_band = '';
if ( $hero_bg_url ) {
	// The hero background is the LCP element, so load it eagerly with high
	// priority (and skip the async decode) rather than lazily — lazy-loading it
	// is what deprioritised the LCP paint.
	$hero_bg_band = '<figure class="hero__bg-band">'
		. wp_get_attachment_image(
			$hero_bg_id,
			'full',
			false,
			array(
				'alt'           => '',
				'class'         => 'hero__bg-image',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
			)
		)
		. '</figure>';
}

$hero_classes = 'is-position-' . $hero_position . ' is-valign-' . $hero_valign;
if ( '' === $hero_media ) {
	$hero_classes .= ' has-no-media';
}
if ( $hero_bg_url ) {
	$hero_classes .= ' has-cover-background';
	if ( '' !== $hero_overlay ) {
		$hero_classes .= ' has-overlay';
	}
}

// Text color scheme — a legibility preset, independent of the background.
// `default` emits nothing.
$hero_scheme = ( isset( $attributes['textScheme'] ) && in_array( $attributes['textScheme'], array( 'light', 'dark' ), true ) )
	? $attributes['textScheme']
	: 'default';
if ( 'default' !== $hero_scheme ) {
	$hero_classes .= ' has-text-' . $hero_scheme;
}

$hero_style = '';
if ( '' !== $hero_bg_color ) {
	$hero_style .= 'background-color:' . $hero_bg_color . ';';
}
if ( $hero_bg_url ) {
	$hero_style .= 'background-image:url(' . esc_url( $hero_bg_url ) . ');';
	if ( '' !== $hero_overlay ) {
		$hero_style .= '--hero-overlay:' . $hero_overlay . ';';
	}
}

$hero_wrapper_args = array( 'class' => $hero_classes );
if ( '' !== $hero_style ) {
	$hero_wrapper_args['style'] = $hero_style;
}
?>
<section <?php echo get_block_wrapper_attributes( $hero_wrapper_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php echo $hero_bg_band; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image(). ?>
	<div class="hero__inner">
		<div class="hero__text">
			<?php if ( '' !== $hero_eyebrow ) : ?>
				<p class="hero__eyebrow"><?php echo esc_html( wptexturize( $hero_eyebrow ) ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $hero_title ) : ?>
				<<?php echo esc_attr( $hero_level ); ?> class="hero__title"><?php echo esc_html( wptexturize( $hero_title ) ); ?></<?php echo esc_attr( $hero_level ); ?>>
			<?php endif; ?>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner blocks, escaped by core. ?>
		</div>
		<?php echo $hero_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image(). ?>
	</div>
</section>
