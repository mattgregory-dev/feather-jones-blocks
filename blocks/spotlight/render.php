<?php
/**
 * Spotlight — server render.
 *
 * Two-column split: a text column (eyebrow → heading → inner-block body) beside
 * an image column. `imagePosition` and `verticalAlignment` express themselves as
 * classes (the layout lives in _spotlight.scss); the DOM order is always
 * text-then-media, and the image side is flipped in CSS.
 *
 * The title is always the typed `title` attribute; `level` only chooses the
 * heading tag (h1 for a page hero, h2 for a mid-page feature). This diverges
 * from intro-section on purpose: spotlight headlines are marketing copy that
 * never matches the page title, so a page-title binding would only ever print
 * the page title into a hero. An empty title renders no heading tag; an empty
 * eyebrow renders no eyebrow. With no image selected, the figure is omitted and
 * the text spans full width.
 *
 * @package starter-blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks markup (.spotlight__body).
 */

$sp_level    = ( isset( $attributes['level'] ) && 'h1' === $attributes['level'] ) ? 'h1' : 'h2';
$sp_eyebrow  = trim( $attributes['eyebrow'] ?? '' );
$sp_position = ( isset( $attributes['imagePosition'] ) && 'left' === $attributes['imagePosition'] ) ? 'left' : 'right';
$sp_valign   = ( isset( $attributes['verticalAlignment'] ) && 'top' === $attributes['verticalAlignment'] ) ? 'top' : 'center';
$sp_image_id  = isset( $attributes['imageId'] ) ? (int) $attributes['imageId'] : 0;
$sp_mobile_id = isset( $attributes['mobileImageId'] ) ? (int) $attributes['mobileImageId'] : 0;
$sp_alt       = trim( $attributes['imageAlt'] ?? '' );

$sp_title = trim( $attributes['title'] ?? '' );

// The desktop image is always present when an image is set; the optional mobile
// image (same alt — same subject, different crop) replaces it below 800px, so
// they carry distinct classes the stylesheet swaps. With no mobile image, the
// desktop one is cropped in CSS (mobileRatio / mobileFocal) instead.
$sp_media = '';
if ( $sp_image_id ) {
	$sp_media  = '<figure class="spotlight__media">';
	$sp_media .= wp_get_attachment_image( $sp_image_id, 'full', false, array( 'alt' => $sp_alt, 'class' => 'spotlight__img spotlight__img--desktop' ) );
	if ( $sp_mobile_id ) {
		$sp_media .= wp_get_attachment_image( $sp_mobile_id, 'full', false, array( 'alt' => $sp_alt, 'class' => 'spotlight__img spotlight__img--mobile' ) );
	}
	$sp_media .= '</figure>';
}

$sp_classes = 'is-position-' . $sp_position . ' is-valign-' . $sp_valign;
if ( '' === $sp_media ) {
	$sp_classes .= ' has-no-media';
}
if ( $sp_image_id && $sp_mobile_id ) {
	$sp_classes .= ' has-mobile-image';
}

// Mobile crop controls (<= 800px) surface as custom properties the stylesheet
// reads: the aspect ratio the image is cropped to, and which part of it the crop
// keeps. Validated against a fixed set so only known CSS values reach the style.
$sp_ratio = in_array( $attributes['mobileRatio'] ?? '3 / 2', array( '3 / 2', '4 / 3', '1 / 1' ), true )
	? $attributes['mobileRatio']
	: '3 / 2';
$sp_focal = in_array( $attributes['mobileFocal'] ?? 'center', array( 'center', 'center top', 'center bottom' ), true )
	? $attributes['mobileFocal']
	: 'center';
$sp_style = sprintf( '--sb-spot-ratio:%s;--sb-spot-focal:%s;', $sp_ratio, $sp_focal );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $sp_classes, 'style' => $sp_style ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<div class="spotlight__inner">
		<div class="spotlight__text">
			<?php if ( '' !== $sp_eyebrow ) : ?>
				<p class="spotlight__eyebrow"><?php echo esc_html( wptexturize( $sp_eyebrow ) ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $sp_title ) : ?>
				<<?php echo esc_attr( $sp_level ); ?> class="spotlight__title"><?php echo esc_html( wptexturize( $sp_title ) ); ?></<?php echo esc_attr( $sp_level ); ?>>
			<?php endif; ?>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner blocks, escaped by core. ?>
		</div>
		<?php echo $sp_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image(). ?>
	</div>
</section>
