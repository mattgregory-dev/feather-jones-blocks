<?php
/**
 * Lead-capture popup.
 *
 * A two-step timed popup on the landing pages + blog posts: frame 1 presents the
 * free-teaching offer with a single-click CTA (a low-friction micro-commitment);
 * clicking it reveals frame 2 with the email form. It reuses Forminator form
 * 2573 — the same lead magnet as the home CTA band — so every submission lands
 * in one place with one nonce and one ajax path.
 *
 * To avoid two live copies of form 2573 on a page (which would duplicate the
 * form's static IDs — module/nonce/honeypot — and confuse Forminator's JS), the
 * popup BORROWS an existing inline instance where one is present (the home CTA
 * band): src/scripts/popup.js moves that node into the popup on frame 2 and
 * returns it on close. On pages with no inline form the popup renders its own
 * single instance. Styling: src/styles/_popup.scss.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Forminator lead-magnet form id and the delay (ms) before the popup opens.
if ( ! defined( 'SB_POPUP_FORM_ID' ) ) {
	define( 'SB_POPUP_FORM_ID', 2573 );
}
if ( ! defined( 'SB_POPUP_DELAY' ) ) {
	define( 'SB_POPUP_DELAY', 8000 );
}

/**
 * Whether the current request is a page the popup should appear on: the front
 * page, single blog posts, or one of the marketing landing pages.
 *
 * @return bool
 */
function sb_popup_is_target() {
	if ( is_admin() ) {
		return false;
	}
	if ( is_front_page() || is_singular( 'post' ) ) {
		return true;
	}
	$landing = array( 'courses', 'live-group-classes', 'study-with-feather', 'field-trips', 'about' );
	return is_page( $landing );
}

/**
 * Whether the popup should borrow an inline form already on the page (rather
 * than render its own). True when the page content embeds the Forminator
 * shortcode — currently the home CTA band. Filterable so the blog single view
 * can opt in once its sidebar form exists (its form isn't in post_content).
 *
 * @return bool
 */
function sb_popup_should_borrow() {
	$post = get_post();
	$has  = $post ? has_shortcode( (string) $post->post_content, 'forminator_form' ) : false;
	// Blog single posts render the lead form in the sidebar (not post_content),
	// so borrow that instance rather than rendering a second one.
	if ( is_singular( 'post' ) ) {
		$has = true;
	}
	return (bool) apply_filters( 'sb_popup_should_borrow', $has );
}

/**
 * Print the popup markup in the footer on target pages.
 */
function sb_render_popup() {
	if ( ! sb_popup_is_target() ) {
		return;
	}
	$borrow = sb_popup_should_borrow();
	?>
	<div class="sb-popup__backdrop" data-popup-close hidden></div>
	<div class="sb-popup" data-popup data-popup-delay="<?php echo esc_attr( SB_POPUP_DELAY ); ?>" data-popup-borrow="<?php echo $borrow ? '1' : '0'; ?>" data-popup-form-id="<?php echo esc_attr( SB_POPUP_FORM_ID ); ?>" hidden>
		<button type="button" class="sb-popup__close" aria-label="<?php esc_attr_e( 'Close', 'fj-blocks' ); ?>" data-popup-close>
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
		</button>

		<div class="sb-popup__frame sb-popup__frame--offer" data-popup-frame="offer">
			<p class="sb-popup__eyebrow">A Free Teaching</p>
			<h2 class="sb-popup__title">How to Hold a Sacred Plant Ceremony</h2>
			<p class="sb-popup__body">A short teaching on sage, sweetgrass, juniper, and the meaning of ceremony &mdash; closing with a live ceremony to the four directions, filmed from a vortex. 26 minutes. A gentle introduction to the plant path.</p>
			<p class="sb-popup__trust"><span class="sb-popup__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span> <em>Trusted by thousands of students over 20+ years</em></p>
			<button type="button" class="sb-popup__cta" data-popup-next>Yes, send me the free teaching</button>
			<button type="button" class="sb-popup__decline" data-popup-dismiss>No thanks, I&rsquo;m not interested</button>
		</div>

		<div class="sb-popup__frame sb-popup__frame--form" data-popup-frame="form" hidden>
			<p class="sb-popup__eyebrow sb-popup__eyebrow--accent">Almost There</p>
			<h2 class="sb-popup__title">Where Should We Send It?</h2>
			<p class="sb-popup__body">Drop your email and we&rsquo;ll take you straight to the teaching. We&rsquo;ll also send you the link, so you can come back to it anytime.</p>
			<div class="sb-popup__form" data-popup-form-slot>
				<?php
				if ( ! $borrow ) {
					echo do_shortcode( '[forminator_form id="' . SB_POPUP_FORM_ID . '"]' );
				}
				?>
			</div>
			<p class="sb-popup__fineprint">You&rsquo;ll also receive herbal insights and program updates. Unsubscribe any time.</p>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'sb_render_popup' );
