<?php
/**
 * Title: Spotlight
 * Slug: starter-blocks/spotlight
 * Categories: starter-blocks
 * Block Types: starter-blocks/spotlight
 * Description: Two-column split — an image beside a text column with eyebrow, heading, body copy, and actions. Swap the placeholder image for your own.
 *
 * @package starter-blocks
 */
?>
<!-- wp:starter-blocks/spotlight {"align":"full","imageId":<?php echo (int) sb_attachment_id_by_filename( 'placeholder-vertical.webp' ); ?>,"imageAlt":"","imagePosition":"right","eyebrow":"Section Eyebrow","level":"h2","title":"A Spotlight Heading"} -->
<div class="spotlight__body"><!-- wp:paragraph -->
<p>Open with a clear, grounded paragraph that states the main idea of this spotlight. The text column sits beside the image and reads left-aligned.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Add a second paragraph to develop the point, then point the reader toward an action below.</p>
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
<!-- /wp:starter-blocks/spotlight -->
