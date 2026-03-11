<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

class Widget_Instagram_Simulator extends Base {
	public function get_name() {
		return 'tm-instagram-simulator';
	}

	public function get_title() {
		return __( 'Instagram Simulator', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-instagram-gallery';
	}

	public function get_keywords() {
		return [ 'instagram', 'gallery', 'image', 'grid' ];
	}

	protected function register_controls() {
		$this->add_content_section();

		$this->add_style_section();

		$this->add_grid_section();
	}

	protected function add_content_section() {
		$this->start_controls_section( 'instagram_content_section', [
			'label' => __( 'Items', 'minimog' ),
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'image', [
			'label' => esc_html__( 'Image', 'minimog' ),
			'type'  => Controls_Manager::MEDIA,
		] );

		$repeater->add_control( 'url', [
			'label'       => __( 'URL', 'minimog' ),
			'type'        => Controls_Manager::URL,
			'placeholder' => __( 'https://your-link.com', 'minimog' ),
			'default'     => [
				'url'         => '',
				'is_external' => true,
				'nofollow'    => true,
			],
		] );

		$this->add_control( 'items', [
			'label'     => __( 'Items', 'minimog' ),
			'type'      => Controls_Manager::REPEATER,
			'fields'    => $repeater->get_controls(),
			'separator' => 'after',
		] );

		$this->add_group_control( Group_Control_Image_Size::get_type(), [
			'name'        => 'image_size',
			'default'     => 'full',
			'render_type' => 'template',
		] );

		$this->add_control( 'hover_effect', [
			'label'        => __( 'Hover Effect', 'minimog' ),
			'type'         => Controls_Manager::SELECT,
			'options'      => [
				''         => __( 'None', 'minimog' ),
				'zoom-in'  => __( 'Zoom In', 'minimog' ),
				'zoom-out' => __( 'Zoom Out', 'minimog' ),
				'move-up'  => __( 'Move Up', 'minimog' ),
			],
			'default'      => '',
			'prefix_class' => 'minimog-animation-',
			'separator'    => 'before',
		] );

		$this->end_controls_section();
	}

	protected function add_style_section() {
		$this->start_controls_section( 'instagram_style_section', [
			'label' => __( 'Instagram', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'image_rounded', [
			'label'      => __( 'Rounded', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .minimog-instagram-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'icon_style_hr', [
			'label'     => __( 'Icon', 'minimog' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'icon_size', [
			'label'      => __( 'Size', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 1,
					'max' => 100,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .minimog-instagram-image .icon' => 'font-size: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'icon_width', [
			'label'      => __( 'Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 1,
					'max' => 200,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .minimog-instagram-image .icon i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'icon_rounded', [
			'label'      => __( 'Rounded', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .minimog-instagram-image .icon i' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'icon_color', [
			'label'     => __( 'Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-instagram-image .icon' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'icon_bg_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .minimog-instagram-image .icon i' => 'background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_section();
	}

	private function add_grid_section() {
		$this->start_controls_section( 'grid_options_section', [
			'label' => __( 'Grid Options', 'minimog' ),
		] );

		$this->add_responsive_control( 'grid_columns', [
			'label'              => __( 'Columns', 'minimog' ),
			'type'               => Controls_Manager::NUMBER,
			'min'                => 1,
			'max'                => 12,
			'step'               => 1,
			'default'            => 5,
			'tablet_default'     => 2,
			'mobile_default'     => 1,
			'render_type'        => 'template',
			'frontend_available' => true,
		] );

		$this->add_responsive_control( 'grid_gutter', [
			'label'   => __( 'Gutter', 'minimog' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 0,
			'max'     => 200,
			'step'    => 1,
			'default' => 20,
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', [
			'minimog-instagram',
			'tm-instagram',
			'minimog-grid-wrapper',
		] );

		$grid_options = $this->get_grid_options( $settings );

		$this->add_render_attribute( 'wrapper', 'data-grid', wp_json_encode( $grid_options ) );

		$grid_args_style = \Minimog_Helper::grid_args_to_html_style( $grid_options );
		if ( ! empty( $grid_args_style ) ) {
			$this->add_render_attribute( 'wrapper', 'style', $grid_args_style );
		}

		$this->add_render_attribute( 'content_wrapper', 'class', [
			'minimog-grid lazy-grid',
		] );

		$this->add_render_attribute( 'item_wrapper', 'class', [
			'minimog-instagram__item',
			'minimog-box',
			'grid-item',
		] );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div <?php $this->print_render_attribute_string( 'content_wrapper' ); ?>>
				<?php foreach ( $settings['items'] as $item ) : ?>
					<?php
					$link_url      = $item['url']['url'];
					$link_target   = $item['url']['is_external'] ? ' target="_blank"' : '';
					$link_nofollow = $item['url']['nofollow'] ? ' rel="nofollow"' : '';
					?>
					<div <?php $this->print_render_attribute_string( 'item_wrapper' ); ?>>
						<div class="minimog-image minimog-instagram-image">
							<?php if ( $link_url ) {
								echo '<a class="instagram-item-link" href="' . esc_url( $link_url ) . '"' . $link_target . $link_nofollow . '>';
							} ?>
							<span class="icon">
								<i class="fab fa-instagram"></i>
							</span>
							<?php echo \Minimog_Image::get_elementor_attachment( [
								'settings'       => $item,
								'size_settings'  => $settings,
								'image_size_key' => 'image_size',
							] ); ?>
							<?php if ( $link_url ) {
								echo '</a>';
							} ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php

	}

	protected function get_grid_options( array $settings ) {
		$grid_options = [
			'type' => 'grid',
		];

		$columns_settings = $this->parse_responsive_settings( $settings, 'grid_columns', 'columns' );
		$gutter_settings  = $this->parse_responsive_settings( $settings, 'grid_gutter', 'gutter' );

		$grid_options += $columns_settings + $gutter_settings;

		return $grid_options;
	}
}
