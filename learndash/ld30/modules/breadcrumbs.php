<?php
/**
 * LearnDash LD30 Displays the breadcrumbs.
 *
 * @since 3.0.0
 * @version 4.21.3
 *
 * @var WP_Post $post The post object.
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The $post var should be passed if from the calling template as of LD 4.0.0.
if ( ! isset( $post ) ) {
	$post = get_post();
}

/**
 * Fires before the breadcrumbs.
 *
 * @since 3.0.0
 */
do_action( 'learndash-breadcrumbs-before' ); ?>

<ol class="ld-breadcrumbs-segments">
	<?php
	$breadcrumbs = learndash_get_breadcrumbs( $post );

	/**
	 * Filter Breadcrumb keys
	 *
	 * @since 3.0.0
	 *
	 * @param array $keys Array of post type keys.
	 */
	$keys = apply_filters(
		'learndash_breadcrumbs_keys',
		array(
			'course',
			'lesson',
			'topic',
			'current',
		)
	);

	?>
	<li>
		<a href="<?php echo esc_url( home_url( '/account/' ) ); ?>">
			<?php echo esc_html__( 'Courses', 'learndash' ); ?>
		</a>
	</li>
	<?php
	foreach ( $keys as $key ) :
		if ( isset( $breadcrumbs[ $key ] ) ) :
			$breadcrumb_title = $breadcrumbs[ $key ]['title'];
			$breadcrumb_link  = $breadcrumbs[ $key ]['permalink'] ?? '';
			$breadcrumb_id    = 0;

			if ( 'current' === $key && ! empty( $post->ID ) ) {
				$breadcrumb_id = (int) $post->ID;
			} elseif ( ! empty( $breadcrumb_link ) ) {
				$breadcrumb_id = (int) url_to_postid( $breadcrumb_link );
			}

			if ( function_exists( 'get_field' ) && $breadcrumb_id ) {
				$shortname = get_field( 'breadcrumb_shortname', $breadcrumb_id );
				if ( is_string( $shortname ) && $shortname !== '' ) {
					$breadcrumb_title = $shortname;
				}
			}
			?>
			<li>
				<?php if ( $key === 'current' ) : ?>
					<a href="" aria-current="page">
						<?php echo esc_html( wp_strip_all_tags( $breadcrumb_title ) ); ?>
					</a>
				<?php else: ?>
					<a href="<?php echo esc_url( $breadcrumb_link ); ?>">
						<?php echo esc_html( wp_strip_all_tags( $breadcrumb_title ) ); ?>
					</a>
				<?php endif; ?>
			</li>
			<?php
		endif;
	endforeach;
	?>
</ol>

<?php
/**
 * Fires after the breadcrumbs.
 *
 * @since 3.0.0
 */
do_action( 'learndash-breadcrumbs-after' ); ?>
