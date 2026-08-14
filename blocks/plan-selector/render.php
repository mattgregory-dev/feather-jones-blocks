<?php
/**
 * Plan Selector — server render.
 *
 * A radiogroup of selectable plan cards. This block renders ONLY the cards; the
 * shared "act" button and any surrounding copy are native blocks. On the front
 * end, view.js reads the checked card's data-cta-label / data-cta-url and writes
 * them onto the nearest button marked `.plan-cta`. The selected-card look is pure
 * CSS (:has(input:checked)).
 *
 * The radio group needs a name unique to this instance so multiple selectors on
 * one page don't share state; wp_unique_id() gives that per render. The <label>
 * wraps its <input>, so no id/for pairing is needed.
 *
 * @package starter-blocks
 *
 * @var array $attributes Block attributes.
 */

$ps_cards = ( isset( $attributes['cards'] ) && is_array( $attributes['cards'] ) ) ? $attributes['cards'] : array();
if ( empty( $ps_cards ) ) {
	return '';
}

$ps_selected = isset( $attributes['selected'] ) ? (int) $attributes['selected'] : 0;
$ps_name     = wp_unique_id( 'plan-' );
$ps_attrs    = get_block_wrapper_attributes( array( 'class' => 'plan-selector' ) );
?>
<div <?php echo $ps_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?> role="radiogroup">
	<?php foreach ( $ps_cards as $ps_i => $ps_card ) : ?>
		<label class="plan-card">
			<input
				type="radio"
				name="<?php echo esc_attr( $ps_name ); ?>"
				value="<?php echo esc_attr( $ps_i ); ?>"
				<?php checked( $ps_i, $ps_selected ); ?>
				data-cta-label="<?php echo esc_attr( $ps_card['ctaLabel'] ?? '' ); ?>"
				data-cta-url="<?php echo esc_url( $ps_card['ctaUrl'] ?? '' ); ?>"
			>
			<span class="plan-card__dot" aria-hidden="true"></span>
			<?php if ( ! empty( $ps_card['kicker'] ) ) : ?>
				<span class="plan-card__kicker"><?php echo esc_html( $ps_card['kicker'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $ps_card['price'] ) ) : ?>
				<span class="plan-card__price"><?php echo esc_html( $ps_card['price'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $ps_card['per'] ) ) : ?>
				<span class="plan-card__per"><?php echo esc_html( $ps_card['per'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $ps_card['total'] ) ) : ?>
				<span class="plan-card__total"><?php echo esc_html( $ps_card['total'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $ps_card['pill'] ) ) : ?>
				<span class="plan-card__pill"><?php echo esc_html( $ps_card['pill'] ); ?></span>
			<?php endif; ?>
		</label>
	<?php endforeach; ?>
</div>
