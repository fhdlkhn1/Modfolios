<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Minimog_WP_Widget_Instagram' ) ) {
	class Minimog_WP_Widget_Instagram extends Minimog_Widget {

		public function __construct() {
			$this->widget_id          = 'minimog-wp-widget-instagram';
			$this->widget_cssclass    = 'minimog-wp-widget-instagram';
			$this->widget_name        = sprintf( '%1$s %2$s', '[Minimog]', __( 'Instagram', 'minimog' ) );
			$this->widget_description = __( 'Display responsive Instagram feeds.', 'minimog' );
			$this->settings           = array(
				'title'        => array(
					'type'  => 'text',
					'std'   => '',
					'label' => __( 'Title', 'minimog' ),
				),
				'limit'        => array(
					'type'  => 'number',
					'step'  => 1,
					'min'   => 1,
					'max'   => 40,
					'std'   => 6,
					'label' => __( 'Limit', 'minimog' ),
				),
				'columns'      => array(
					'type'  => 'number',
					'step'  => 1,
					'min'   => 1,
					'max'   => 9,
					'std'   => 3,
					'label' => __( 'Columns', 'minimog' ),
				),
				'gutter'       => array(
					'type'  => 'number',
					'step'  => 1,
					'min'   => 1,
					'max'   => 100,
					'std'   => 6,
					'label' => __( 'Gutter', 'minimog' ),
				),
				'image_shape'  => array(
					'type'    => 'select',
					'std'     => 'cropped',
					'label'   => __( 'Image Size', 'minimog' ),
					'options' => [
						'cropped'  => __( 'Square', 'minimog' ),
						'original' => __( 'Original', 'minimog' ),
					],
				),
				'hover_effect' => array(
					'type'    => 'select',
					'std'     => '',
					'label'   => __( 'Hover Effect', 'minimog' ),
					'options' => [
						''         => __( 'None', 'minimog' ),
						'zoom-in'  => __( 'Zoom In', 'minimog' ),
						'zoom-out' => __( 'Zoom Out', 'minimog' ),
						'move-up'  => __( 'Move Up', 'minimog' ),
					],
				),
				'show_button'  => array(
					'type'  => 'checkbox',
					'std'   => 1,
					'label' => __( 'Show Button', 'minimog' ),
				),
				'button_text'  => array(
					'type'  => 'text',
					'std'   => '',
					'label' => __( 'Button Text', 'minimog' ),
				),
			);

			parent::__construct();
		}

		public function widget( $args, $instance ) {
			$limit        = absint( $this->get_value( $instance, 'limit' ) );
			$columns      = $this->get_value( $instance, 'columns' );
			$gutter       = $this->get_value( $instance, 'gutter' );
			$hover_effect = $this->get_value( $instance, 'hover_effect' );
			$image_shape  = $this->get_value( $instance, 'image_shape' );
			$show_button  = $this->get_value( $instance, 'show_button' );
			$button_text  = $this->get_value( $instance, 'button_text' );
			$images       = Minimog_Instagram::instance()->get_images( $limit );
			$user         = Minimog_Instagram::instance()->get_user();

			$main_classes = [
				'minimog-instagram--' . $image_shape,
			];

			if ( $hover_effect ) {
				$main_classes[] = 'minimog-animation-' . $hover_effect;
			}

			$grid_options = [
				'type'    => 'grid',
				'columns' => $columns,
				'gutter'  => $gutter,
			];

			$this->widget_start( $args, $instance );

			if ( is_wp_error( $images ) ) {
				echo '' . $images->get_error_message();
			} elseif ( is_array( $images ) ) {
				$medias = array_slice( $images, 0, $limit );
				?>
				<div class="minimog-instagram minimog-instagram-widget minimog-grid-wrapper <?php echo esc_attr( implode( ' ', $main_classes ) ) ?>"
				     data-grid="<?php echo esc_attr( wp_json_encode( $grid_options ) ) ?>"
					<?php echo Minimog_Helper::grid_args_to_html_attr( $grid_options ); ?>>
					<div class="minimog-grid lazy-grid">
						<?php foreach ( $medias as $media ) : ?>
							<div class="minimog-instagram__item minimog-box grid-item">
								<?php echo Minimog_Instagram::instance()->get_image( $media ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
				if ( ! empty( $show_button ) ) {
					$button_args = [
						'link'          => [
							'url' => 'https://www.instagram.com/' . $user['username'],
						],
						'style'         => 'text',
						'text'          => ! empty( $button_text ) ? $button_text : '@' . esc_html( $user['username'] ),
						'wrapper_class' => 'minimog-instagram-widget-button',
						'icon'          => 'fab fa-instagram',
						'icon_align'    => 'left',
					];

					Minimog_Templates::render_button( $button_args );
				}
			}

			$this->widget_end( $args, $instance );
		}
	}
}
