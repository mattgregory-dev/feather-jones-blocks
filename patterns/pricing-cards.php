<?php
/**
 * Title: Pricing Cards
 * Slug: starter-blocks/pricing-cards
 * Categories: starter-blocks
 * Description: Two side-by-side pricing tiers, one featured, with a fine-print note.
 *
 * @package starter-blocks
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"pricing-cards","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull pricing-cards" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">A Section Heading</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"640px"}} -->
<p class="has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--50)">A sentence introducing the options and what each one includes.</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"backgroundColor":"base","style":{"border":{"radius":"14px","width":"2px","color":"var:preset|color|contrast-2"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"},"shadow":"var:preset|shadow|lifted"}} -->
<div class="wp-block-column has-base-background-color has-background has-border-color" style="border-color:var(--wp--preset--color--contrast-2);border-width:2px;border-radius:14px;box-shadow:var(--wp--preset--shadow--lifted);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.1em"},"border":{"radius":"999px","width":"1px","color":"var:preset|color|contrast-2"},"spacing":{"padding":{"top":"4px","bottom":"4px","left":"12px","right":"12px"}}},"textColor":"contrast-2","backgroundColor":"surface-2","fontSize":"x-small"} -->
<p class="has-border-color has-contrast-2-color has-surface-2-background-color has-text-color has-background has-x-small-font-size" style="border-color:var(--wp--preset--color--contrast-2);border-width:1px;border-radius:999px;padding-top:4px;padding-right:12px;padding-bottom:4px;padding-left:12px;text-transform:uppercase;letter-spacing:0.1em">Most popular</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Featured Plan</h3>
<!-- /wp:heading -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">$0</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"x-small"} -->
<p class="has-x-small-font-size">per person</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>A short sentence describing what this plan offers and who it's for.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-checklist","style":{"spacing":{"blockGap":"var:preset|spacing|20","margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|40"}}}} -->
<ul class="wp-block-list is-style-checklist" style="margin-top:var(--wp--preset--spacing--30);margin-bottom:var(--wp--preset--spacing--40)"><!-- wp:list-item -->
<li>Included feature one</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Included feature two</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Included feature three</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Included feature four</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"width":100} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button" href="#">Choose Plan</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"backgroundColor":"base","style":{"border":{"radius":"14px","width":"1px","color":"var:preset|color|border"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"},"shadow":"var:preset|shadow|soft"}} -->
<div class="wp-block-column has-base-background-color has-background has-border-color" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:14px;box-shadow:var(--wp--preset--shadow--soft);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Basic Plan</h3>
<!-- /wp:heading -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">$0</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"x-small"} -->
<p class="has-x-small-font-size">per person</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>A short sentence describing what this plan offers and who it's for.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-checklist","style":{"spacing":{"blockGap":"var:preset|spacing|20","margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|40"}}}} -->
<ul class="wp-block-list is-style-checklist" style="margin-top:var(--wp--preset--spacing--30);margin-bottom:var(--wp--preset--spacing--40)"><!-- wp:list-item -->
<li>Included feature one</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Included feature two</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Included feature three</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Included feature four</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"width":100,"className":"is-style-secondary"} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-secondary"><a class="wp-block-button__link wp-element-button" href="#">Choose Plan</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:paragraph {"align":"center","className":"pricing-note","fontSize":"x-small","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
<p class="has-text-align-center has-x-small-font-size pricing-note" style="margin-top:var(--wp--preset--spacing--60)">A short fine-print line for deposit, payment, or cancellation terms.</p>
<!-- /wp:paragraph --></section>
<!-- /wp:group -->
