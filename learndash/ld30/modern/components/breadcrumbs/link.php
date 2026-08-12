<?php
/**
 * View: Breadcrumbs Item - Link.
 *
 * @since 4.24.0
 * @version 4.24.0
 *
 * @var Breadcrumb $breadcrumb Breadcrumbs Item.
 * @var Template   $this       Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Template\Breadcrumbs\Breadcrumb;
use LearnDash\Core\Template\Template;

$label = $breadcrumb->get_label();
$breadcrumb_id = $breadcrumb->get_id();

if ( function_exists( 'get_field' ) && ! empty( $breadcrumb_id ) ) {
	$post_id = is_numeric( $breadcrumb_id ) ? (int) $breadcrumb_id : null;

	if ( ! $post_id ) {
		$post_id = url_to_postid( $breadcrumb->get_url() );
	}

	if ( ! $post_id && $breadcrumb->is_last() ) {
		$post_id = get_queried_object_id();
	}

	if ( $post_id ) {
		$shortname = get_field( 'breadcrumb_shortname', $post_id );

		if ( is_string( $shortname ) && $shortname !== '' ) {
			$label = $shortname;
		}
	}
}
?>
<a
	href="<?php echo esc_url( $breadcrumb->get_url() ); ?>"
	class="ld-breadcrumbs__link"
	<?php if ( $breadcrumb->is_last() ) : ?>
		aria-current="page"
	<?php endif; ?>
>
	<?php echo esc_html( $label ); ?>
</a>
