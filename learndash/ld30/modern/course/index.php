<?php
/**
 * View: Course Page. Modern variant.
 *
 * @since 4.21.0
 * @version 4.21.0
 *
 * @var Course   $course       Course model.
 * @var bool     $show_sidebar Whether to show the sidebar.
 * @var bool     $show_header  Whether to show the header.
 * @var Template $this         Current Instance of template engine rendering this template.
 *
 * @package LearnDash\Core
 */

use LearnDash\Core\Models\Course;
use LearnDash\Core\Template\Template;

$additional_classes = 'ld-layout';
if ( ! $show_sidebar ) {
	$additional_classes .= ' ld-layout--no-sidebar';
}

if ( ! $show_header ) {
	$additional_classes .= ' ld-layout--no-header';
}

$course_post = $course->get_post();
$course_title = get_the_title( $course_post );

if ( function_exists( 'get_field' ) && ! empty( $course_post->ID ) ) {
	$shortname = get_field( 'breadcrumb_shortname', $course_post->ID );
	if ( is_string( $shortname ) && $shortname !== '' ) {
		$course_title = $shortname;
	}
}
?>
<div
	class="<?php learndash_the_wrapper_class( $course_post, $additional_classes ); ?>"
	data-js="learndash-view"
	data-learndash-breakpoints="<?php echo esc_attr( $this->get_breakpoints_json() ); ?>"
	data-learndash-breakpoint-pointer="<?php echo esc_attr( $this->get_breakpoint_pointer() ); ?>"
>
  <?php if ( is_user_logged_in() ) : ?>
    <nav
      aria-label="<?php esc_html_e( 'Breadcrumbs', 'learndash' ); ?>"
      class="ld-breadcrumbs ld-breadcrumbs--modern"
    >
      <ol class="ld-breadcrumbs__items">
        <li class="ld-breadcrumbs__item ld-breadcrumbs__item--all-courses">
          <a href="<?php echo esc_url( home_url( '/courses/' ) ); ?>" class="ld-breadcrumbs__link">
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
        <li class="ld-breadcrumbs__item ld-breadcrumbs__item--current">
          <span class="ld-breadcrumbs__link" aria-current="page">
            <?php echo esc_html( $course_title ); ?>
          </span>
        </li>
      </ol>
    </nav>
  <?php endif; ?>

	<?php if ( $show_header ) : ?>
		<?php $this->template( 'modern/course/header' ); ?>
	<?php endif; ?>

	<?php $this->template( 'modern/course/content' ); ?>

	<?php if ( $show_sidebar ) : ?>
		<?php $this->template( 'modern/course/sidebar' ); ?>
	<?php endif; ?>
</div>

<?php
$this->template( 'components/breakpoints', [ 'is_initial_load' => true ] );
