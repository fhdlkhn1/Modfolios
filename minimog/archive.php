<?php
/**
 * The template for displaying archive pages.
 *
 * @link     https://codex.wordpress.org/Template_Hierarchy
 *
 * @package  Minimog
 * @since    1.0
 */
get_header();

$archive_container_class = 'container';
if ( Minimog_Post::instance()->is_archive() ) {
	$archive_container_class = Minimog_Site_Layout::instance()->get_container_class( Minimog::setting( 'blog_archive_site_layout' ) );
}
?>
	<div id="page-content" class="page-content">
		<div class="<?php echo esc_attr( $archive_container_class ); ?>">
			<div class="row">

				<?php Minimog_Sidebar::instance()->render( 'left' ); ?>

				<div class="page-main-content">

					
					
					<div class="elementor-element elementor-element-d1c1145 e-flex e-con-boxed e-con e-parent e-lazyloaded" data-id="d1c1145" data-element_type="container">
					<div class="e-con-inner">
				<div class="elementor-element elementor-element-0ef8396 elementor-widget elementor-widget-heading" data-id="0ef8396" data-element_type="widget" data-widget_type="heading.default">
					<h2 class="elementor-heading-title elementor-size-default">Join Modfolios - Turn Portfolios into profit or license authentic content.</h2>				</div>
				<div class="elementor-element elementor-element-4ee9bf9 elementor-widget elementor-widget-heading" data-id="4ee9bf9" data-element_type="widget" data-widget_type="heading.default">
					<p class="elementor-heading-title elementor-size-default">Choose the path that fits you. You can Switch or add roles later.</p>				</div>
					</div>
				</div>
					
					
					
					
					
					<?php
					if ( ! minimog_has_elementor_template( 'archive' ) ) {
						minimog_load_template( 'blog/archive-blog' );
					}
					?>

				</div>

				<?php Minimog_Sidebar::instance()->render( 'right' ); ?>

			</div>
		</div>
	</div>
<?php get_footer();