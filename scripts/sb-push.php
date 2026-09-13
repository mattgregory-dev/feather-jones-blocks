<?php
/**
 * Direct-to-DB PUSH. Writes wp/.work/<slug>.html into the slug-matched page's
 * post_content.
 *
 * Correctness guards (load-bearing):
 *
 *   1. Runs as an administrator. WP-CLI's default user-0 context has kses
 *      ACTIVE, which silently strips <iframe>, <script> and inline SVG — so
 *      embeds and inline-SVG markup survive the editor but vanish through a raw
 *      write. wp_set_current_user() fires 'set_current_user' → kses_init()
 *      re-runs → an admin has unfiltered_html → the kses filters drop.
 *   2. wp_slash() before save: wp_insert_post() unslashes its input, so
 *      unslashed content containing backslashes would corrupt. Slashing first
 *      makes the write byte-exact.
 *
 * Safety guards (added after a stale push scrubbed ~20 minutes of live editor
 * work):
 *
 *   A. STALE-PUSH GUARD (optimistic lock). Refuses to push unless the current DB
 *      content still matches the baseline hash recorded at the last sb-pull of
 *      this page. If the DB changed since that pull — someone edited it in the
 *      block editor — it ABORTS rather than clobbering. Re-pull to refresh the
 *      baseline and reconcile first. There is NO override flag, on purpose.
 *   B. PRE-PUSH BACKUP. The current DB content is copied to
 *      .work/backups/<slug>-<utc-timestamp>.html before any write, so every push
 *      is instantly reversible.
 *
 *   docker compose run --rm -T wpcli wp eval-file \
 *     wp-content/themes/fj-blocks/scripts/sb-push.php <slug>
 *
 * @package starter-blocks
 */

if ( empty( $args[0] ) ) {
	echo "sb-push: pass a page slug\n";
	return;
}

$sb_slug = $args[0];
$sb_file = ABSPATH . '.work/' . $sb_slug . '.html';

if ( ! is_readable( $sb_file ) ) {
	echo "sb-push: no working file at .work/{$sb_slug}.html\n";
	return;
}

$sb_page = get_page_by_path( $sb_slug );

if ( ! $sb_page ) {
	echo "sb-push: no page with slug '{$sb_slug}'\n";
	return;
}

$sb_current_db = get_post_field( 'post_content', $sb_page->ID, 'raw' );
$sb_db_hash    = md5( $sb_current_db );
$sb_baseline   = get_post_meta( $sb_page->ID, '_sb_base_hash', true );

// GUARD A — stale-push protection.
if ( empty( $sb_baseline ) ) {
	echo "sb-push: ABORT — no baseline recorded for '{$sb_slug}'. Pull it first: sb-pull.php {$sb_page->ID} > .work/{$sb_slug}.html  (pull-before-push). Nothing was written.\n";
	return;
}

if ( $sb_db_hash !== $sb_baseline ) {
	echo "sb-push: ABORT — '{$sb_slug}' changed in the DB since your last pull (someone edited it in the editor). Re-pull, reconcile, THEN push. Nothing was written.\n";
	return;
}

// GUARD B — back up the current DB content before overwriting. The write is
// verified, and a failure ABORTS: the container runs as uid 33 (www-data), so a
// .work/ directory that isn't group-writable silently defeats this. A push that
// reports a backup it did not write is worse than one that refuses to run.
$sb_backup_dir = ABSPATH . '.work/backups';

if ( ! is_dir( $sb_backup_dir ) ) {
	wp_mkdir_p( $sb_backup_dir );
}

$sb_stamp  = gmdate( 'Ymd-His' );
$sb_backup = "{$sb_backup_dir}/{$sb_slug}-{$sb_stamp}.html";
$sb_wrote  = is_dir( $sb_backup_dir )
	? file_put_contents( $sb_backup, $sb_current_db ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- CLI-only tooling; WP_Filesystem is not bootstrapped here.
	: false;

if ( false === $sb_wrote ) {
	echo "sb-push: ABORT — could not write the pre-push backup to .work/backups/. Fix the permissions (the wpcli container writes as uid 33: chmod 2775 wp/.work) and retry. Nothing was written.\n";
	return;
}

$sb_admins = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
		'fields' => 'ID',
	)
);

if ( $sb_admins ) {
	wp_set_current_user( (int) $sb_admins[0] );
}

$sb_content = file_get_contents( $sb_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_get_contents -- CLI-only tooling; reading a local working file, not a remote resource.

wp_update_post(
	array(
		'ID'           => $sb_page->ID,
		'post_content' => wp_slash( $sb_content ),
	)
);

update_post_meta( $sb_page->ID, '_sb_base_hash', md5( $sb_content ) );

echo 'pushed ' . $sb_slug . " (post {$sb_page->ID}, " . strlen( $sb_content ) . ' bytes, as user ' . get_current_user_id() . ") — DB backup: .work/backups/{$sb_slug}-{$sb_stamp}.html\n";
