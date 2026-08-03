<?php
/**
 * Title: Hero
 * Slug: starter-blocks/hero
 * Categories: starter-blocks
 * Block Types: starter-blocks/hero
 * Description: Full-width hero with a cover background image and overlay tint, plus eyebrow, heading, text, and buttons. Swap the placeholder background and adjust the tint.
 *
 * @package starter-blocks
 */
?>
<!-- wp:starter-blocks/hero {"align":"full","backgroundImageId":<?php echo (int) sb_attachment_id_by_filename( 'placeholder-horizontal.webp' ); ?>,"overlayColor":"#1f1f1f66","eyebrow":"Section Eyebrow","level":"h2","title":"A Hero Heading"} -->
<div class="hero__body"><!-- wp:paragraph -->
<p>Open with a short, striking line that sets the tone. The text sits above the background and its overlay tint.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"url":"#"} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">Primary Action</a></div>
<!-- /wp:button -->

<!-- wp:button {"url":"#","className":"is-style-secondary"} -->
<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button" href="#">Secondary Action</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"fontSize":"x-small"} -->
<p class="has-x-small-font-size">Optional fine print — a short qualifier or reassurance beneath the actions.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:starter-blocks/hero -->
