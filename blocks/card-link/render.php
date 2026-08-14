<?php
/**
 * Card Link — server render.
 *
 * An entire card rendered as a single anchor: the whole surface routes to `url`.
 * Inner blocks are decorative (block.json excludes core/buttons, so nothing nests
 * a second anchor). The result is one valid, keyboard-focusable link with a
 * freely-composed card for its label.
 *
 * A card with no URL yet (freshly inserted) renders nothing on the front end
 * rather than a dead, hrefless anchor.
 *
 * @package starter-blocks
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Inner blocks markup (.sb-card-link__body).
 */

$cl_url = trim( $attributes['url'] ?? '' );
if ( '' === $cl_url ) {
	return '';
}

$cl_pad = in_array( $attributes['padding'] ?? '80', array( '60', '70', '80' ), true )
	? $attributes['padding']
	: '80';

// Alignment flexes the whole card so the image centers with the text; left is
// the default and needs no modifier.
$cl_align = ( isset( $attributes['contentAlign'] ) && in_array( $attributes['contentAlign'], array( 'center', 'right' ), true ) )
	? ' sb-card-link--align-' . $attributes['contentAlign']
	: '';

$cl_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'sb-card-link sb-card' . $cl_align,
		'style' => sprintf( '--sb-card-link-pad:var(--wp--preset--spacing--%s);', esc_attr( $cl_pad ) ),
	)
);

$cl_target = ! empty( $attributes['opensInNewTab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
?>
<a href="<?php echo esc_url( $cl_url ); ?>"<?php echo $cl_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, safe. ?> <?php echo $cl_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner blocks, escaped by core. ?>
</a>
