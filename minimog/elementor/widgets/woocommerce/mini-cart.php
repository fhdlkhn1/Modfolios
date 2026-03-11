<?php

namespace Minimog_Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Widget_Mini_Cart extends Base {

	public function get_name() {
		return 'tm-mini-cart';
	}

	public function get_title() {
		return __( 'Mini Cart', 'minimog' );
	}

	public function get_icon_part() {
		return 'eicon-cart';
	}

	public function get_keywords() {
		return [ 'cart', 'mini cart', 'cart icon' ];
	}

	protected function register_controls() {
		$this->add_layout_section();

		$this->add_mini_cart_styling_section();

	}

	private function add_layout_section() {
		$this->start_controls_section( 'layout_section', [
			'label' => __( 'Layout', 'minimog' ),
		] );

		$this->add_control( 'cart_icon_style', [
			'label'       => __( 'Style', 'minimog' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				'icon-style-01' => sprintf( __( 'Icon set %s', 'minimog' ), '01' ),
				'icon-style-02' => sprintf( __( 'Icon set %s', 'minimog' ), '02' ),
				'icon-style-03' => sprintf( __( 'Icon set %s', 'minimog' ), '03' ),
				'icon-style-04' => sprintf( __( 'Icon set %s', 'minimog' ), '04' ),
				'icon-style-05' => sprintf( __( 'Icon set %s', 'minimog' ), '05' ),
			],
			'default'     => 'icon-style-01',
			'render_type' => 'template',
		] );

		$this->end_controls_section();
	}

	private function add_mini_cart_styling_section() {
		$this->start_controls_section( 'section_icon_toggle_style', [
			'label' => __( 'Icon', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'toggle_icon_size', [
			'label'      => __( 'Size', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'min' => 12,
					'max' => 40,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-icon-size: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'tm_minicart_icon_padding', [
			'label'      => __( 'Padding', 'minimog' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px' ],
			'selectors'  => [
				'body:not(.rtl) {{WRAPPER}} .tm-minicart .minicart-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'body.rtl {{WRAPPER}} .tm-minicart .minicart-icon'       => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'tm_minicart_icon_border_width', [
			'label'      => __( 'Border Width', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max' => 20,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-border-icon-width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'tm_minicart_icon_border_radius', [
			'label'      => __( 'Border Radius', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [
				'px' => [
					'max' => 200,
				],
				'%'  => [
					'max' => 100,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-border-icon-border-radius: {{SIZE}}{{UNIT}}',
			],
		] );

		$this->start_controls_tabs( 'tm_minicart_icon_colors' );

		$this->start_controls_tab( 'tm_minicart_icon_normal_colors', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'tm_minicart_icon_icon_color', [
			'label'     => __( 'Icon Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-icon-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'tm_minicart_icon_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-background-icon-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'tm_minicart_icon_border_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-border-icon-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'tm_minicart_icon_hover_colors', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'tm_minicart_icon_hover_color', [
			'label'     => __( 'Icon Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-icon-hover-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'tm_minicart_icon_background_hover_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-background-icon-hover-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'tm_minicart_icon_border_hover_color', [
			'label'     => __( 'Border Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-border-icon-hover-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Badge
		$this->start_controls_section( 'section_badge_toggle_style', [
			'label' => __( 'Badge', 'minimog' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'badge_size', [
			'label'      => __( 'Size', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max' => 40,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-icon-badge-size: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'badge_font_size', [
			'label'      => __( 'Font Size', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max' => 20,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-icon-badge-font-size: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'badge_spacing', [
			'label'      => __( 'Spacing', 'minimog' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [
					'max' => 20,
				],
			],
			'selectors'  => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-icon-badge-spacing: -{{SIZE}}{{UNIT}};',
			],
		] );

		$this->start_controls_tabs( 'tm_minicart_badge_colors' );

		$this->start_controls_tab( 'tm_minicart_badge_normal_colors', [
			'label' => __( 'Normal', 'minimog' ),
		] );

		$this->add_control( 'tm_minicart_badge_icon_color', [
			'label'     => __( 'Icon Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-icon-badge-text-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'tm_minicart_badge_background_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart' => '--tm-minicart-icon-badge-background-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( 'tm_minicart_badge_hover_colors', [
			'label' => __( 'Hover', 'minimog' ),
		] );

		$this->add_control( 'tm_minicart_badge_hover_color', [
			'label'     => __( 'Badge Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart:hover' => '--tm-minicart-icon-badge-text-hover-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'tm_minicart_badge_background_hover_color', [
			'label'     => __( 'Background Color', 'minimog' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tm-minicart:hover' => '--tm-minicart-icon-badge-background-hover-color: {{VALUE}};',
			],
		] );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$classes = [
			'tm-minicart',
			'tm-minicart--style-' . $settings['cart_icon_style'],
		];

		$this->add_render_attribute( 'wrapper', 'class', $classes );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php
			global $woocommerce;
			$cart_url = isset( $woocommerce ) ? wc_get_cart_url() : '/cart';

			$icon_style = $settings['cart_icon_style'];
			$link_class = "mini-cart__button has-badge hint--bounce hint--bottom style-{$icon_style} minicart-icon";
			$qty        = ! empty( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

			$cart_badge_html = '<div class="icon-badge mini-cart-badge" data-count="' . $qty . '">' . $qty . '</div>';

			switch ( $icon_style ) :
				case 'icon-style-02':
					$svg = \Minimog_SVG_Manager::instance()->get( 'phr-shopping-bag' );
					break;
				case 'icon-style-03':
					$svg = \Minimog_SVG_Manager::instance()->get( 'shopping-bag-solid' );
					break;
				case 'icon-style-04':
					$svg = \Minimog_SVG_Manager::instance()->get( 'phb-shopping-cart-simple' );
					break;
				case 'icon-style-05':
					$svg = \Minimog_SVG_Manager::instance()->get( 'shopping-basket' );
					break;
				default:
					$svg = \Minimog_SVG_Manager::instance()->get( 'shopping-bag' );
					break;
			endswitch;

			/**
			 * Add attribute data-e-disable-page-transition
			 * to disable page-transition feature from Elementor Pro reload page.
			 */

			?>
			<a href="<?php echo esc_url( $cart_url ); ?>" class="<?php echo esc_attr( $link_class ); ?>"
			   aria-label="<?php esc_attr_e( 'Cart', 'minimog' ); ?>"
			   data-e-disable-page-transition="1"
			>
				<?php echo '<div class="icon">' . $svg . $cart_badge_html . '</div>'; ?>
			</a>

		</div>
		<?php
	}
}
