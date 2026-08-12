<?php
/**
 * View: Breadcrumbs - Item.
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
?>
<?php if ( 'course' === $breadcrumb->get_id() ) : ?>
	<li class="ld-breadcrumbs__item ld-breadcrumbs__item--all-courses">
		<a href="<?php echo esc_url( home_url( '/account/' ) ); ?>" class="ld-breadcrumbs__link">
			<?php echo esc_html__( 'Courses', 'learndash' ); ?>
		</a>
		<?php
		$this->template(
			'components/icons/slash-forward',
			[
				'classes'        => [ 'ld-breadcrumbs__delimiter' ],
				'is_aria_hidden' => true,
			]
		);
		?>
	</li>
<?php endif; ?>
<li class="ld-breadcrumbs__item" id="ld-breadcrumbs-<?php echo esc_attr( $breadcrumb->get_id() ); ?>">
	<?php $this->template( 'modern/components/breadcrumbs/link' ); ?>

	<?php if ( ! $breadcrumb->is_last() ) : ?>
		<?php
		$this->template(
			'components/icons/slash-forward',
			[
				'classes'        => [ 'ld-breadcrumbs__delimiter' ],
				'is_aria_hidden' => true,
			]
		);
		?>
	<?php endif; ?>
</li>
