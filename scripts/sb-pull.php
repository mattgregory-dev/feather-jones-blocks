<?php
/**
 * Direct-to-DB PULL. Echoes a post's RAW post_content — exact bytes, no
 * formatter trailing newline — so a pull → edit → push → pull round trip is
 * byte-identical. Redirect host-side into wp/.work/<slug>.html.
 *
 * SAFETY: when the id is a real page (not a revision), records a baseline hash
 * in postmeta (`_sb_base_hash`). sb-push refuses to write unless the DB still
 * matches that baseline, so a stale push cannot clobber edits made after this
 * pull. This is the pull half of the stale-push guard — ALWAYS pull immediately
 * before an intended push so the baseline is fresh.
 *
 *   docker compose run --rm -T wpcli wp eval-file \
 *     wp-content/themes/fj-blocks/scripts/sb-pull.php <post-id> > wp/.work/<slug>.html
 *
 * @package starter-blocks
 */

if ( empty( $args[0] ) ) {
	fwrite( STDERR, "sb-pull: pass a post id\n" );
	return;
}

$sb_id      = (int) $args[0];
$sb_content = get_post_field( 'post_content', $sb_id, 'raw' );
$sb_post    = get_post( $sb_id );

if ( $sb_post && 'revision' !== $sb_post->post_type ) {
	// Record what the DB looked like at this pull; sb-push checks against it.
	update_post_meta( $sb_id, '_sb_base_hash', md5( $sb_content ) );
}

echo $sb_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw post_content by design; this is a byte-exact dump, not page output.
