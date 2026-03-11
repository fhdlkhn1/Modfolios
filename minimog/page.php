<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Minimog
 * @since   2.7.2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$gap             = Minimog_Helper::get_post_meta( 'page_content_container_gap', 30 );
$site_layout     = Minimog_Helper::get_post_meta( 'page_content_container_width' );
$container_class = Minimog_Site_Layout::instance()->get_container_class( $site_layout );
?>
	<div id="page-content" class="page-content"
		<?php if ( isset( $gap ) && '' !== $gap ): ?>
			style="--minimog-page-content-gap: <?php echo $gap . 'px'; ?>"
		<?php endif; ?>
	>
		<div class="<?php echo esc_attr( $container_class ); ?>">
			<div class="row">

				<?php Minimog_Sidebar::instance()->render( 'left' ); ?>

				<div id="page-main-content" class="page-main-content">
					<?php while ( have_posts() ) : the_post(); ?>
						<?php
						if ( ! minimog_has_elementor_template( 'single' ) ) {
							minimog_load_template( 'content-single-page' );
						}
						?>

						<?php
						// If comments are open or, we have at least one comment, load up the comment template.
						if ( comments_open() || get_comments_number() ) :
							comments_template();
						endif;
						?>
					<?php endwhile; ?>
				</div>

				<?php Minimog_Sidebar::instance()->render( 'right' ); ?>

			</div>
		</div>
	</div>
<?php
get_footer();
