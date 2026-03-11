<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

class Product_Quantity_Select {
	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'product_data_tabs' ] );
		add_action( 'woocommerce_product_data_panels', [ $this, 'product_data_panels' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'process_product_meta' ] );

		add_filter( 'woocommerce_loop_add_to_cart_args', [ $this, 'loop_add_to_cart_args' ], 99, 2 );

		add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_to_cart_validation' ], 99, 4 );
	}

	public function product_data_tabs( $tabs ) {
		$tabs['minimog_quantity_select'] = array(
			'label'  => __( 'Quantity', 'minimog' ),
			'target' => 'minimog_quantity_settings',
		);

		return $tabs;
	}

	public function product_data_panels() {
		?>
		<div id="minimog_quantity_settings" class="panel woocommerce_options_panel">
			<div class="options_group">
				<?php
				woocommerce_wp_select( [
					'id'      => '_quantity_type',
					'label'   => __( 'Type', 'minimog' ),
					'options' => [
						''       => __( 'Default', 'minimog' ),
						'input'  => __( 'Input', 'minimog' ),
						'select' => __( 'Select', 'minimog' ),
					],
				] );

				woocommerce_wp_textarea_input( [
					'id'          => '_quantity_ranges',
					'label'       => __( 'Values', 'minimog' ),
					'cols'        => 50,
					'rows'        => 5,
					'style'       => 'height: 100px;',
					'description' => __( 'These values will be used for select type. Enter each value in one line and can use the range e.g "1-5".', 'minimog' ),
				] );
				?>
			</div>
		</div>
		<?php
	}

	public function process_product_meta( $post_id ) {
		if ( isset( $_POST['_quantity_type'] ) ) {
			update_post_meta( $post_id, '_quantity_type', sanitize_text_field( $_POST['_quantity_type'] ) );
		} else {
			delete_post_meta( $post_id, '_quantity_type' );
		}

		if ( isset( $_POST['_quantity_ranges'] ) ) {
			update_post_meta( $post_id, '_quantity_ranges', sanitize_textarea_field( $_POST['_quantity_ranges'] ) );
		} else {
			delete_post_meta( $post_id, '_quantity_ranges' );
		}
	}

	/**
	 *
	 *
	 * @param $args
	 * @param $product
	 *
	 * @return array|mixed|string|string[]|null
	 */
	public function loop_add_to_cart_args( $args, $product ) {
		$quantity_type = \Minimog_Woo::instance()->get_product_quantity_type( $product );

		if ( 'select' !== $quantity_type ) {
			return $args;
		}

		$values = \Minimog_Woo::instance()->get_product_quantity_select_ranges( $product );
		$ranges = explode( "\n", str_replace( "\r", "", $values ) );

		if ( ! empty( $ranges ) ) {
			$first_range = $ranges[0];

			if ( strpos( $first_range, '-' ) !== false ) {
				$range = explode( '-', $first_range );

				if ( count( $range ) === 2 ) {
					$args['quantity'] = intval( $range[0] );
				}
			} else {
				$args['quantity'] = intval( $first_range );
			}
		}

		return $args;
	}

	/**
	 * Update add to cart button with the first quantity select option.
	 *
	 * @param $passed
	 * @param $product_id
	 * @param $qty
	 * @param $variation_id
	 *
	 * @return void
	 */
	public function add_to_cart_validation( $passed, $product_id, $qty, $variation_id = 0 ) {
		if ( $variation_id ) {
			$product_id = $variation_id;
		}

		$product = wc_get_product( $product_id );

		if ( 'select' === \Minimog_Woo::instance()->get_product_quantity_type( $product )
		     && apply_filters( 'minimog/quantity_select/add_to_cart_validation', true, $product_id, $qty )
		) {
			$values             = \Minimog_Woo::instance()->get_product_quantity_select_ranges( $product );
			$ranges             = explode( "\n", str_replace( "\r", "", $values ) );
			$options            = [];
			$added_quantity     = $this->get_quantity_in_cart( $product_id );
			$after_add_quantity = $added_quantity + $qty;


			if ( empty( $values ) ) {
				$options[] = 1;
			} else {
				foreach ( $ranges as $value ) {
					if ( is_numeric( $value ) ) {
						$options[] = intval( $value );
					} elseif ( strpos( $value, '-' ) !== false ) {
						$range = explode( '-', $value );

						if ( count( $range ) === 2 ) {
							$min = intval( $range[0] );
							$max = intval( $range[1] );

							$options = array_merge( $options, range( $min, $max ) );
						}
					}
				}

				$options = array_unique( $options );
			}

			if ( ! in_array( $after_add_quantity, $options ) ) {
				wc_add_notice( sprintf( /* translators: invalid */ esc_html__( 'You can\'t add %1$s &times; "%2$s" to the cart.', 'minimog' ), $qty, esc_html( get_the_title( $product_id ) ) ), 'error' );

				return false;
			}
		}

		return $passed;
	}

	/**
	 * Get quantity of the given product in cart
	 *
	 * @param $product_id
	 *
	 * @return mixed|null
	 */
	public function get_quantity_in_cart( $product_id ) {
		$qty = 0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ( $cart_item['product_id'] === $product_id ) || ( $cart_item['variation_id'] === $product_id ) ) {
				$qty += $cart_item['quantity'];
			}
		}

		return $qty;
	}
}

Product_Quantity_Select::instance()->initialize();
