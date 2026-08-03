<?php
/**
 * Title: Link Cards
 * Slug: starter-blocks/link-cards
 * Categories: starter-blocks
 * Description: Three linked image cards. Swap the placeholder images and update the headings, text, and links.
 *
 * @package starter-blocks
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"link-cards","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull link-cards" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">A Section Heading</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--50)"><!-- wp:column {"backgroundColor":"base","style":{"border":{"radius":"14px"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|30"},"shadow":"var:preset|shadow|soft"}} -->
<div class="wp-block-column has-base-background-color has-background" style="border-radius:14px;box-shadow:var(--wp--preset--shadow--soft);padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><?php echo sb_image_block( 'placeholder-horizontal.webp', '', '#' ); ?>

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Card Heading</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A short description of what this card links to.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="#">Learn More →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"backgroundColor":"base","style":{"border":{"radius":"14px"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|30"},"shadow":"var:preset|shadow|soft"}} -->
<div class="wp-block-column has-base-background-color has-background" style="border-radius:14px;box-shadow:var(--wp--preset--shadow--soft);padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><?php echo sb_image_block( 'placeholder-horizontal.webp', '', '#' ); ?>

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Card Heading</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A short description of what this card links to.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="#">Learn More →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"backgroundColor":"base","style":{"border":{"radius":"14px"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|30"},"shadow":"var:preset|shadow|soft"}} -->
<div class="wp-block-column has-base-background-color has-background" style="border-radius:14px;box-shadow:var(--wp--preset--shadow--soft);padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><?php echo sb_image_block( 'placeholder-horizontal.webp', '', '#' ); ?>

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Card Heading</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A short description of what this card links to.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="#">Learn More →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
