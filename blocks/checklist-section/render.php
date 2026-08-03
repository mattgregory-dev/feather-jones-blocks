<?php
/**
 * Checklist Section — server render.
 *
 * Full-width band with a constrained inner column: eyebrow → heading → a
 * two-column checklist grid (the two inner core/list blocks). The grid layout is
 * owned by _checklist-section.scss; the ✓ marker by _lists.scss.
 *
 * Title is always the typed `title`; `level` chooses h2 or h3 (never h1). An
 * empty title renders no heading tag; an empty eyebrow renders no eyebrow.
 *
 * @package starter-blocks
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Inner blocks markup (.checklist-section__lists).
 */

$cl_eyebrow = trim( $attributes['eyebrow'] ?? '' );
$cl_title   = trim( $attributes['title'] ?? '' );
$cl_level   = ( isset( $attributes['level'] ) && 'h3' === $attributes['level'] ) ? 'h3' : 'h2';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'sb-band' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<div class="checklist-section__inner">
		<?php if ( '' !== $cl_eyebrow ) : ?>
			<p class="checklist-section__eyebrow"><?php echo esc_html( wptexturize( $cl_eyebrow ) ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $cl_title ) : ?>
			<<?php echo esc_attr( $cl_level ); ?> class="checklist-section__title"><?php echo esc_html( wptexturize( $cl_title ) ); ?></<?php echo esc_attr( $cl_level ); ?>>
		<?php endif; ?>
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner blocks, escaped by core. ?>
	</div>
</section>
