<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

class Widget_Instagram_Simulator_Carousel extends Carousel_Base {

	public function get_name() {
		return 'tm-instagram-simulator-carousel';
	}

	public function get_title() {
		return __( 'Instagram Simulator Carousel', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-carousel';
	}

	public function get_keywords() {
		return [ 'instagram', 'gallery', 'image', 'carousel' ];
	}

	protected function register_controls() {
		$this->add_content_section();

		$this->add_style_section();

		parent::register_controls();
	}

	protected function add_content_section() {
		$this->start_controls_section( 'instagram_content_section', [
			'label' => __( 'Slides', 'minimog' ),
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

		$this->add_control( 'slides', [
			'label'     => __( 'Slides', 'minimog' ),
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

	protected function render() {
		$settings = $this->get_settings_for_display();
		$this->add_render_attribute( 'wrapper', 'class', [
			'tm-instagram-carousel',
			'minimog-instagram',
		] );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php $this->print_slider( $settings ); ?>
		</div>
		<?php
	}

	protected function print_slides( array $settings ) {
		$this->add_render_attribute( 'item_wrapper', 'class', [
			'swiper-slide',
			'minimog-box',
		] );
		?>
		<?php foreach ( $settings['slides'] as $slide ) : ?>
			<?php
			$link_url      = $slide['url']['url'];
			$link_target   = $slide['url']['is_external'] ? ' target="_blank"' : '';
			$link_nofollow = $slide['url']['nofollow'] ? ' rel="nofollow"' : '';
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
						'settings'       => $slide,
						'size_settings'  => $settings,
						'image_size_key' => 'image_size',
					] ); ?>
					<?php if ( $link_url ) {
						echo '</a>';
					} ?>
				</div>
			</div>
		<?php endforeach;
	}
}
