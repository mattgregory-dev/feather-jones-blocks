<?php
/**
 * Title: FAQ Accordion
 * Slug: starter-blocks/faq-accordion
 * Categories: starter-blocks
 * Description: Expandable question-and-answer accordion. Add a question by duplicating an item.
 *
 * @package starter-blocks
 */
?>
<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"layout":{"type":"constrained","contentSize":"820px"}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)"><!-- wp:heading {"textAlign":"center","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--50)">Frequently Asked Questions</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"faq-accordion","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"default"}} -->
<div class="wp-block-group faq-accordion"><!-- wp:details {"showContent":true,"style":{"border":{"width":"1px","radius":"10px"},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"borderColor":"border"} -->
<details class="wp-block-details has-border-color has-border-border-color" open style="border-width:1px;border-radius:10px;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)"><summary>The first question goes here?</summary><!-- wp:paragraph -->
<p>The answer goes here — one or two sentences that respond clearly to the question above.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"style":{"border":{"width":"1px","radius":"10px"},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"borderColor":"border"} -->
<details class="wp-block-details has-border-color has-border-border-color" style="border-width:1px;border-radius:10px;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)"><summary>The second question goes here?</summary><!-- wp:paragraph -->
<p>The answer goes here — one or two sentences that respond clearly to the question above.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"style":{"border":{"width":"1px","radius":"10px"},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"borderColor":"border"} -->
<details class="wp-block-details has-border-color has-border-border-color" style="border-width:1px;border-radius:10px;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)"><summary>The third question goes here?</summary><!-- wp:paragraph -->
<p>The answer goes here — one or two sentences that respond clearly to the question above.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
