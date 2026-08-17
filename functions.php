<?php
/**
 * Starter Blocks — theme bootstrap.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Theme module manifest. Each file is self-contained: comment out any
// require_once below to disable that module without removing the file.
$theme_inc = get_template_directory() . '/inc';

require_once $theme_inc . '/theme-setup.php';
require_once $theme_inc . '/block-styles.php';
require_once $theme_inc . '/blocks.php';
require_once $theme_inc . '/enqueue.php';
require_once $theme_inc . '/images.php';
require_once $theme_inc . '/attachments.php';
require_once $theme_inc . '/scroll-top.php';
require_once $theme_inc . '/branding.php';
require_once $theme_inc . '/cpt.php';
require_once $theme_inc . '/heartbeat.php';
require_once $theme_inc . '/security.php';
require_once $theme_inc . '/comments.php';
require_once $theme_inc . '/analytics.php';
require_once $theme_inc . '/bloat.php';
require_once $theme_inc . '/woocommerce.php';
require_once $theme_inc . '/learndash.php';
require_once $theme_inc . '/course-links.php';
require_once $theme_inc . '/shortcodes.php';
require_once $theme_inc . '/popup.php';
