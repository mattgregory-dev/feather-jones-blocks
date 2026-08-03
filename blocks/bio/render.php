<?php
/**
 * Bio — server render. Person-scoped sibling of Spotlight.
 *
 * Two-column split: a text column (role → name → inner-block biography) beside a
 * portrait. `imagePosition` (default left) and `verticalAlignment` (default center)
 * express themselves as classes; the layout lives in _bio.scss. DOM order is
 * always text-then-media; the image side is flipped in CSS.
 *
 * Unlike Spotlight there is no `level`: the name is always an <h2>. An empty
 * name renders no heading tag; an empty role renders no eyebrow. With no image
 * selected, the figure is omitted and the text spans full width.
 *
 * @package starter-blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks markup (.bio__body).
 */

$bio_eyebrow  = trim( $attributes['eyebrow'] ?? '' );
$bio_title    = trim( $attributes['title'] ?? '' );
$bio_position = ( isset( $attributes['imagePosition'] ) && 'right' === $attributes['imagePosition'] ) ? 'right' : 'left';
$bio_valign   = ( isset( $attributes['verticalAlignment'] ) && 'top' === $attributes['verticalAlignment'] ) ? 'top' : 'center';
$bio_image_id = isset( $attributes['imageId'] ) ? (int) $attributes['imageId'] : 0;
$bio_alt      = trim( $attributes['imageAlt'] ?? '' );

$bio_media = '';
if ( $bio_image_id ) {
	$bio_media = '<figure class="bio__media">'
		. wp_get_attachment_image( $bio_image_id, 'full', false, array( 'alt' => $bio_alt ) )
		. '</figure>';
}

$bio_classes = 'is-position-' . $bio_position . ' is-valign-' . $bio_valign;
if ( '' === $bio_media ) {
	$bio_classes .= ' has-no-media';
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $bio_classes ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<div class="bio__inner">
		<div class="bio__text">
			<?php if ( '' !== $bio_eyebrow ) : ?>
				<p class="bio__eyebrow"><?php echo esc_html( wptexturize( $bio_eyebrow ) ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $bio_title ) : ?>
				<h2 class="bio__title"><?php echo esc_html( wptexturize( $bio_title ) ); ?></h2>
			<?php endif; ?>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner blocks, escaped by core. ?>
		</div>
		<?php echo $bio_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image(). ?>
	</div>
</section>
