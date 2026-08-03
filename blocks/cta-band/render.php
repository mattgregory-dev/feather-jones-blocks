<?php
/**
 * CTA Band — server render.
 *
 * Full-width band with a constrained, centered column: eyebrow → heading →
 * inner-block body (paragraph(s) and buttons). The look is owned entirely by
 * _cta-band.scss; no color or style variations.
 *
 * Title is always the typed `title` attribute; `level` chooses h2 or h3 only
 * (never h1 — a CTA is not the page's top heading). An empty title renders no
 * heading tag; an empty eyebrow renders no eyebrow.
 *
 * @package starter-blocks
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Inner blocks markup (.cta-band__body).
 */

$cta_eyebrow = trim( $attributes['eyebrow'] ?? '' );
$cta_title   = trim( $attributes['title'] ?? '' );
$cta_level   = ( isset( $attributes['level'] ) && 'h3' === $attributes['level'] ) ? 'h3' : 'h2';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'sb-band' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<div class="cta-band__inner">
		<?php if ( '' !== $cta_eyebrow ) : ?>
			<p class="cta-band__eyebrow"><?php echo esc_html( wptexturize( $cta_eyebrow ) ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $cta_title ) : ?>
			<<?php echo esc_attr( $cta_level ); ?> class="cta-band__title"><?php echo esc_html( wptexturize( $cta_title ) ); ?></<?php echo esc_attr( $cta_level ); ?>>
		<?php endif; ?>
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner blocks, escaped by core. ?>
	</div>
</section>
