<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

class Product_Bought_Together {

	protected static $types = array(
		'simple',
		'variable',
		'variation',
		'woosb',
		'bundle',
		'subscription',
	);

	protected static $instance = null;

	/**
	 * @var \WPCleverWoobt $woobt
	 */
	public $woobt;

	const MINIMUM_PLUGIN_VERSION   = '7.5.1';
	const RECOMMEND_PLUGIN_VERSION = '7.5.1';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		if ( ! $this->is_activate() ) {
			return;
		}

		if ( defined( 'WOOBT_VERSION' ) ) {
			if ( version_compare( WOOBT_VERSION, self::RECOMMEND_PLUGIN_VERSION, '<' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_recommend_plugin_version' ] );
			}

			if ( version_compare( WOOBT_VERSION, self::MINIMUM_PLUGIN_VERSION, '<' ) ) {
				return;
			}
		}

		$this->woobt = \WPCleverWoobt::instance();

		add_action( 'woobt_wrap_before', [ $this, 'add_wrap_tag_open' ] );
		add_action( 'woobt_wrap_after', [ $this, 'add_wrap_tag_close' ] );

		add_filter('woobt_products_class', [$this, 'products_class']);

		/**
		 * Remove all hooks then re-add
		 *
		 * @see \WPCleverWoobt::__construct()
		 */
//		add_action( 'init', [ $this, 'wp_init' ] );
//		minimog_remove_filters_for_anonymous_class( 'woocommerce_before_add_to_cart_form', 'WPCleverWoobt', 'show_items_before_atc' );
//		minimog_remove_filters_for_anonymous_class( 'woocommerce_after_add_to_cart_form', 'WPCleverWoobt', 'show_items_after_atc' );
//		minimog_remove_filters_for_anonymous_class( 'woocommerce_single_product_summary', 'WPCleverWoobt', 'show_items_below_title', 6 );
//		minimog_remove_filters_for_anonymous_class( 'woocommerce_single_product_summary', 'WPCleverWoobt', 'show_items_below_price', 11 );
//		minimog_remove_filters_for_anonymous_class( 'woocommerce_single_product_summary', 'WPCleverWoobt', 'show_items_below_excerpt', 21 );
//		minimog_remove_filters_for_anonymous_class( 'woocommerce_single_product_summary', 'WPCleverWoobt', 'show_items_below_meta', 41 );
//		minimog_remove_filters_for_anonymous_class( 'woocommerce_after_single_product_summary', 'WPCleverWoobt', 'show_items_below_summary', 9 );
	}

	public function add_wrap_tag_open($product) {
		if ( ! $product ) {
			global $product;

			if ( $product ) {
				$product_id = $product->get_id();
			}
		} else {
			if ( is_a( $product, 'WC_Product' ) ) {
				$product_id = $product->get_id();
			}

			if ( is_numeric( $product ) ) {
				$product_id = absint( $product );
				$product    = wc_get_product( $product_id );
			}
		}

		ob_start();
		?>
		<div class="woobt-header">
			<h6 class="woobt-block-heading"><?php esc_html_e( 'Frequently Bought Together', 'minimog' ); ?></h6>
			<?php if ( $before_text = apply_filters( 'woobt_before_text', get_post_meta( $product_id, 'woobt_before_text', true ) ? : $this->woobt->localization( 'above_text' ), $product_id ) ) :?>
				<div class="woobt-before-text woobt-text"><?php echo do_shortcode( stripslashes( $before_text ) ) ?></div>
			<?php endif; ?>
		</div>
		<div class="woobt-body">
		<?php
		echo ob_get_clean();
	}

	public function add_wrap_tag_close() {
		echo '</div>';
	}

	public function products_class($classes) {
		$classes .= ' product';

		return $classes;
	}

	public function is_activate() {
		return class_exists( 'WPCleverWoobt' );
	}

	public function admin_notice_recommend_plugin_version() {
		minimog_notice_required_plugin_version( 'Frequently Bought Together for WooCommerce', self::RECOMMEND_PLUGIN_VERSION );
	}

	public function wp_init() {
		self::$types = apply_filters( 'woobt_product_types', self::$types );

		// Add to cart button & form.
		$position = apply_filters( 'woobt_position', self::get_setting( 'position', apply_filters( 'woobt_default_position', 'before' ) ) );

		switch ( $position ) {
			case 'before':
				add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'add_to_cart_form' ] );
				break;
			case 'after':
				add_action( 'woocommerce_after_add_to_cart_form', [ $this, 'add_to_cart_form' ] );
				break;
			case 'below_title':
				add_action( 'woocommerce_single_product_summary', [ $this, 'add_to_cart_form' ], 6 );
				break;
			case 'below_price':
				add_action( 'woocommerce_single_product_summary', [ $this, 'add_to_cart_form' ], 11 );
				break;
			case 'below_excerpt':
				add_action( 'woocommerce_single_product_summary', [ $this, 'add_to_cart_form' ], 21 );
				break;
			case 'below_meta':
				add_action( 'woocommerce_single_product_summary', [ $this, 'add_to_cart_form' ], 46 );
				break;
			case 'below_summary':
				add_action( 'woocommerce_after_single_product_summary', [ $this, 'add_to_cart_form' ], 9 );
				break;
		}
	}

	public function add_to_cart_form() {
		global $product;

		if ( ! $product || $product->is_type( 'grouped' ) ) {
			return;
		}

		$this->show_items();
	}

	/**
	 * Override html
	 *
	 * @param $product
	 * @param $custom_position
	 *
	 * @return void
	 * @see \WPCleverWoobt::show_items()
	 */
	public function show_items( $product = null, $custom_position = false ) {
		$product_id = 0;
		$is_default = false;

		if ( ! $product ) {
			global $product;

			if ( $product ) {
				$product_id = $product->get_id();
			}
		} else {
			if ( is_a( $product, 'WC_Product' ) ) {
				$product_id = $product->get_id();
			}

			if ( is_numeric( $product ) ) {
				$product_id = absint( $product );
				$product    = wc_get_product( $product_id );
			}
		}

		if ( ! $product_id || ! $product || $product->is_type( 'grouped' ) || $product->is_type( 'external' ) ) {
			return;
		}

		wp_enqueue_script( 'wc-add-to-cart-variation' );

		$custom_qty  = apply_filters( 'woobt_custom_qty', get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on', $product_id );
		$sync_qty    = apply_filters( 'woobt_sync_qty', get_post_meta( $product_id, 'woobt_sync_qty', true ) === 'on', $product_id );
		$checked_all = apply_filters( 'woobt_checked_all', get_post_meta( $product_id, 'woobt_checked_all', true ) === 'on', $product_id );
		$separately  = apply_filters( 'woobt_separately', get_post_meta( $product_id, 'woobt_separately', true ) === 'on', $product_id );
		$separately  &= apply_filters( 'woobt_separately_reset_price', true, $product_id, 'view' ); // change it to false if you want to keep the discounted price.
		$selection   = apply_filters( 'woobt_selection', get_post_meta( $product_id, 'woobt_selection', true ) ? : 'multiple', $product_id );

		$_position       = get_post_meta( $product_id, 'woobt_position', true ) ? : 'unset';
		$_layout         = get_post_meta( $product_id, 'woobt_layout', true ) ? : 'unset';
		$_atc_button     = get_post_meta( $product_id, 'woobt_atc_button', true ) ? : 'unset';
		$_show_this_item = get_post_meta( $product_id, 'woobt_show_this_item', true ) ? : 'unset';

		// settings
		$default          = apply_filters( 'woobt_default', self::get_setting( 'default', [ 'default' ] ) );
		$default_limit    = (int) apply_filters( 'woobt_default_limit', self::get_setting( 'default_limit', 0 ) );
		$pricing          = self::get_setting( 'pricing', 'sale_price' );
		$position         = $_position !== 'unset' ? $_position : apply_filters( 'woobt_position', self::get_setting( 'position', apply_filters( 'woobt_default_position', 'before' ) ) );
		$layout           = $_layout !== 'unset' ? $_layout : self::get_setting( 'layout', 'default' );
		$show_this_item   = apply_filters( 'woobt_show_this_item', $_show_this_item !== 'unset' ? $_show_this_item : self::get_setting( 'show_this_item', 'yes' ), $product_id );
		$atc_button       = apply_filters( 'woobt_atc_button', $_atc_button !== 'unset' ? $_atc_button : self::get_setting( 'atc_button', 'main' ), $product_id );
		$separate_atc     = $atc_button === 'separate' || $atc_button === 'both';
		$separate_images  = $layout === 'separate';
		$hide_this_item   = apply_filters( 'woobt_hide_this_item', ! $custom_position && ! $separate_atc && ! wc_string_to_bool( $show_this_item ), $product_id );
		$ignore_this_item = apply_filters( 'woobt_separately_ignore_this_item', false, $product_id );
		$discount         = $separately ? '0' : $this->woobt->get_discount( $product_id );
		$thumbnail_size   = \Minimog_Woo::instance()->get_loop_product_image_size();

		// Custom var of theme.
		$is_outside_layout = 'below_summary' === $position && $separate_images;

		// get items
		$items = apply_filters( 'woobt_show_items', $this->woobt->get_items( $product_id, 'view' ), $product_id );

		if ( ! empty( $items ) ) {
			// format items
			foreach ( $items as $key => $item ) {
				if ( is_array( $item ) ) {
					if ( ! empty( $item['id'] ) ) {
						$_item['id']    = $item['id'];
						$_item['price'] = $item['price'];
						$_item['qty']   = $item['qty'];
					} else {
						// heading/paragraph
						$_item = $item;
					}
				} else {
					// make it works with upsells/cross-sells/related
					$_item['id']    = absint( $item );
					$_item['price'] = self::get_setting( 'default_price', '100%' );
					$_item['qty']   = 1;
				}

				if ( ! empty( $_item['id'] ) ) {
					if ( $_item_product = wc_get_product( $_item['id'] ) ) {
						$_item['product'] = $_item_product;
					} else {
						unset( $items[ $key ] );
						continue;
					}
				}

				if ( ! empty( $_item['product'] ) && ( ! in_array( $_item['product']->get_type(), self::$types, true ) || ( ( self::get_setting( 'exclude_unpurchasable', 'no' ) === 'yes' ) && ( ! $_item['product']->is_purchasable() || ! $_item['product']->is_in_stock() ) ) ) ) {
					unset( $items[ $key ] );
					continue;
				}

				if ( ! empty( $_item['product'] ) && ! apply_filters( 'woobt_item_visible', $_item['product']->get_status() === 'publish', $_item ) ) {
					unset( $items[ $key ] );
					continue;
				}

				$items[ $key ] = $_item;
			}
		}

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $key => $item ) {
			if ( is_array( $item ) ) {
				if ( ! empty( $item['id'] ) ) {
					$_item['id']    = $item['id'];
					$_item['price'] = $item['price'];
					$_item['qty']   = $item['qty'];
				} else {
					// heading/paragraph
					$_item = $item;
				}
			} else {
				// make it works with upsells/cross-sells/related
				$_item['id']    = absint( $item );
				$_item['price'] = self::get_setting( 'default_price', '100%' );
				$_item['qty']   = 1;
			}

			if ( ! empty( $_item['id'] ) ) {
				if ( $_item_product = wc_get_product( $_item['id'] ) ) {
					$_item['product'] = $_item_product;
				} else {
					unset( $items[ $key ] );
					continue;
				}
			}

			if ( ! empty( $_item['product'] ) && $_item['product'] instanceof \WC_Product && ( ! in_array( $_item['product']->get_type(), self::$types, true ) || ( ( $this->woobt::get_setting( 'exclude_unpurchasable', 'no' ) === 'yes' ) && ( ! $_item['product']->is_purchasable() || ! $_item['product']->is_in_stock() ) ) ) ) {
				unset( $items[ $key ] );
				continue;
			}

			$items[ $key ] = $_item;
		}

		if ( empty( $items ) ) {
			return;
		}

		$wrap_class = "woobt-wrap woobt-position-{$position} woobt-layout-{$layout} woobt-wrap-{$product_id}" . ( self::get_setting( 'responsive', 'yes' ) === 'yes' ? ' woobt-wrap-responsive' : '' );

		if ( $custom_position ) {
			$wrap_class .= ' woobt-wrap-custom-position';
		}

		if ( $separate_atc ) {
			$wrap_class .= ' woobt-wrap-separate-atc';
		}

		$sku        = $product->get_sku();
		$weight     = htmlentities( wc_format_weight( $product->get_weight() ) );
		$dimensions = htmlentities( wc_format_dimensions( $product->get_dimensions( false ) ) );
		$price_html = htmlentities( $product->get_price_html() );

		$wrap_attrs = apply_filters( 'woobt_wrap_data_attributes', [
			'id'                   => $product_id,
			'selection'            => $selection,
			'position'             => $position,
			'atc-button'           => $atc_button,
			'this-item'            => $hide_this_item ? 'no' : 'yes',
			'ignore-this'          => $ignore_this_item ? 'yes' : 'no',
			'separately'           => $separately ? 'on' : 'off',
			'layout'               => $layout,
			'product-id'           => $product->is_type( 'variable' ) ? '0' : $product_id,
			'product-sku'          => $sku,
			'product-o_sku'        => $sku,
			'product-weight'       => $weight,
			'product-o_weight'     => $weight,
			'product-dimensions'   => $dimensions,
			'product-o_dimensions' => $dimensions,
			'product-price-html'   => $price_html,
			'product-o_price-html' => $price_html,
		], $product );

		echo '<div class="' . esc_attr( $wrap_class ) . '" ' . $this->woobt::data_attributes( $wrap_attrs ) . '>';

		echo '<h6 class="woobt-block-heading">' . esc_html__( 'Frequently Bought Together', 'minimog' ) . '</h6>';

		if ( $before_text = apply_filters( 'woobt_before_text', get_post_meta( $product_id, 'woobt_before_text', true ) ? : $this->woobt->localization( 'above_text' ), $product_id ) ) {
			echo '<div class="woobt-before-text woobt-text">' . do_shortcode( stripslashes( $before_text ) ) . '</div>';
		}

		do_action( 'woobt_wrap_before', $product );

		echo '<div class="woobt-block-content">';

		echo '<div class="woobt-body">';
		?>

		<?php if ( $separate_images && 'below_summary' !== $position ) : ?>
			<div class="woobt_images woobt-images">
				<?php
				echo '<div class="woobt-image woobt-image-this woobt-image-order-0 woobt-image-' . esc_attr( $product_id ) . '">' . \Minimog_Woo::instance()->get_product_image( $product, $thumbnail_size ) . '</div>';
				echo '<div class="woobt-image-plus">' . \Minimog_SVG_Manager::instance()->get( 'fal-plus' ) . '</div>';

				$order = 1;

				foreach ( $items as $item ) {
					if ( ! empty( $item['id'] ) ) {
						/**
						 * @var \WC_Product $item_product
						 */
						$item_product = $item['product'];
						ob_start();
						?>
						<div class="woobt-image<?php echo ' woobt-image-order-' . $order; ?><?php echo ' woobt-image-' . esc_attr( $item['id'] ); ?>">
							<a href="<?php echo esc_url( $item_product->get_permalink() ) ?>"><?php echo \Minimog_Woo::instance()->get_product_image( $item_product, $thumbnail_size ); ?></a>
						</div>
						<?php
						echo ob_get_clean();

						$order++;
					}
				}
				?>
			</div>
		<?php endif; ?>
		<div class="woobt-products-wrap">
			<?php
			$products_class = apply_filters( 'woobt_products_class', 'woobt-products woobt-products-layout-' . $layout . ' woobt-products-' . $product_id, $product );
			$products_attrs = apply_filters( 'woobt_products_data_attributes', [
				'show-price'           => self::get_setting( 'show_price', 'yes' ),
				'optional'             => $custom_qty ? 'on' : 'off',
				'separately'           => $separately ? 'on' : 'off',
				'sync-qty'             => $sync_qty ? 'on' : 'off',
				'variables'            => $this->woobt->has_variables( $items ) ? 'yes' : 'no',
				'product-id'           => $product->is_type( 'variable' ) ? '0' : $product_id,
				'product-type'         => $product->get_type(),
				'product-price-suffix' => htmlentities( $product->get_price_suffix() ),
				'pricing'              => $pricing,
				'discount'             => $discount,
			], $product );

			?>
			<?php do_action( 'woobt_products_above', $product ); ?>

			<div class="<?php echo esc_attr( $products_class ); ?>" <?php echo $this->woobt::data_attributes( $products_attrs ); ?>>
				<?php do_action( 'woobt_products_before', $product ); ?>
				<?php
				// This item.
				$product_name = apply_filters( 'woobt_product_get_name', $product->get_name(), $product );

				if ( $custom_position || $separate_atc || $separate_images || self::get_setting( 'show_this_item', 'yes' ) !== 'no' ) {
					?>
					<?php
					/**
					 * Required css class 'product' to avoid js functions affected main product.
					 * For eg: add-to-cart-variation.js
					 */ ?>
					<div class="product woobt-product woobt-product-this"
					     data-order="0"
					     data-qty="1"
					     data-o_qty="1"
					     data-id="<?php echo esc_attr( $product->is_type( 'variable' ) || ! $product->is_in_stock() ? 0 : $product_id ); ?>"
					     data-pid="<?php echo esc_attr( $product_id ); ?>"
					     data-name="<?php echo esc_attr( $product_name ); ?>"
					     data-new-price="<?php echo esc_attr( ! $separately && ( $discount = get_post_meta( $product_id, 'woobt_discount', true ) ) ? ( 100 - (float) $discount ) . '%' : '100%' ); ?>"
					     data-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
					     data-price="<?php echo esc_attr( apply_filters( 'woobt_item_data_price', wc_get_price_to_display( $product ), $product ) ); ?>"
					     data-regular-price="<?php echo esc_attr( apply_filters( 'woobt_item_data_regular_price', wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), $product ) ); ?>"
					>

						<?php do_action( 'woobt_product_before', $product ); ?>

						<div class="woobt-thumb-wrap">
							<?php if ( ! $is_outside_layout ) : ?>
								<div class="woobt-choose">
									<input class="woobt-checkbox woobt-checkbox-this" type="checkbox" checked disabled/>
								</div>
							<?php endif; ?>

							<?php if ( self::get_setting( 'show_thumb', 'yes' ) !== 'no' || ( 'below_summary' === $position && $separate_images ) ) { ?>
								<div class="woobt-thumb">
									<?php echo \Minimog_Woo::instance()->get_product_image( $product, $thumbnail_size ); ?>

									<?php echo '<div class="woobt-image-plus">' . \Minimog_SVG_Manager::instance()->get( 'fal-plus' ) . '</div>'; ?>
								</div>
							<?php } ?>
						</div>

						<div class="woobt-product-info">
							<h3 class="woobt-title">
								<?php echo '<span>' . $this->woobt->localization( 'this_item', esc_html__( 'This item:', 'minimog' ) ) . '</span> ' . wp_kses_post( $product_name ); ?>
							</h3>

							<div class="woobt-product-cart">
								<?php if ( $is_outside_layout ) : ?>
									<div class="woobt-choose">
										<input class="woobt-checkbox woobt-checkbox-this" type="checkbox" checked
										       disabled/>
									</div>
								<?php endif; ?>

								<div class="woobt-product-cart-inner">
									<?php if ( ( ! $separate_images && self::get_setting( 'show_price', 'yes' ) !== 'no' ) ) : ?>
										<div class="woobt-price">
											<div class="woobt-price-new">
												<?php
												if ( ! $separately && ( $discount = get_post_meta( $product_id, 'woobt_discount', true ) ) ) {
													$sale_price = $product->get_price() * ( 100 - (float) $discount ) / 100;
													echo wc_format_sale_price( $product->get_price(), $sale_price ) . $product->get_price_suffix( $sale_price );
												} else {
													echo '' . $product->get_price_html();
												}
												?>
											</div>
											<div class="woobt-price-ori">
												<?php echo '' . $product->get_price_html(); ?>
											</div>
										</div>
									<?php endif; ?>

									<?php if ( ( $separate_atc || $custom_position ) && $product->is_type( 'variable' ) ) : ?>
										<div class="minimog-variation-select-wrap">
											<?php
											if ( ( self::get_setting( 'variations_selector', 'default' ) === 'wpc_radio' ) && class_exists( 'WPClever_Woovr' ) ) {
												echo '<div class="wpc_variations_form">';
												// use class name wpc_variations_form to prevent found_variation in woovr
												\WPClever_Woovr::woovr_variations_form( $product );
												echo '</div>';
											} else {
												if ( $separate_atc ) {
													\Minimog_Woo::instance()->get_product_variation_dropdown_html( $product, [
														'show_label' => false,
														'show_price' => true,
													] );
												}

												$attributes           = $product->get_variation_attributes();
												$available_variations = $product->get_available_variations();

												if ( is_array( $attributes ) && ( count( $attributes ) > 0 ) ) {
													echo '<div class="variations_form woobt_variations_form" action="' . esc_url( $product->get_permalink() ) . '" data-product_id="' . absint( $product_id ) . '" data-product_variations="' . htmlspecialchars( wp_json_encode( $available_variations ) ) . '">';
													echo '<div class="variations">';

													foreach ( $attributes as $attribute_name => $options ) { ?>
														<div class="variation">
															<div class="label">
																<?php echo wc_attribute_label( $attribute_name ); ?>
															</div>
															<div class="select value">
																<?php
																$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
																wc_dropdown_variation_attribute_options( [
																	'options'          => $options,
																	'attribute'        => $attribute_name,
																	'product'          => $product,
																	'selected'         => $selected,
																	'show_option_none' => esc_html__( 'Choose', 'minimog' ) . ' ' . wc_attribute_label( $attribute_name ),
																] );
																?>
															</div>
														</div><!-- /variation -->
													<?php }

													echo '<div class="reset">' . apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'minimog' ) . '</a>' ) . '</div>';
													echo '</div><!-- /variations -->';
													echo '</div><!-- /variations_form -->';

													if ( self::get_setting( 'show_description', 'no' ) === 'yes' ) {
														echo '<div class="woobt-variation-description"></div>';
													}
												}
											} ?>
										</div>
									<?php endif; ?>

									<?php if ( $custom_qty ) : ?>
										<div class="woobt-quantity">
											<?php
											/*if ( self::get_setting( 'plus_minus', 'no' ) === 'yes' ) {
												echo '<div class="woobt-quantity-input">';
												echo '<div class="woobt-quantity-input-minus">-</div>';
											}*/

											woocommerce_quantity_input( array(
												'input_name' => 'woobt_qty_0',
												'classes'    => array(
													'input-text',
													'woobt-qty',
													'woobt-this-qty',
													'qty',
													'text',
												),
											), $product );

											/*if ( self::get_setting( 'plus_minus', 'no' ) === 'yes' ) {
												echo '<div class="woobt-quantity-input-plus">+</div>';
												echo '</div>';
											}*/ ?>
										</div>
									<?php endif; ?>

									<?php echo '<div class="woobt-availability">' . wc_get_stock_html( $product ) . '</div>'; ?>
								</div>
							</div>

							<?php do_action( 'woobt_product_after', $product ); ?>
						</div>
					</div>
					<?php
				} else {
					?>
					<div class="product woobt-product woobt-product-this woobt-hide-this"
					     data-order="0"
					     data-qty="1"
					     data-id="<?php echo esc_attr( $product->is_type( 'variable' ) || ! $product->is_in_stock() ? 0 : $product_id ); ?>"
					     data-pid="<?php echo esc_attr( $product_id ); ?>"
					     data-name="<?php echo esc_attr( $product_name ); ?>"
					     data-price="<?php echo esc_attr( apply_filters( 'woobt_item_data_price', wc_get_price_to_display( $product ), $product ) ); ?>"
					     data-regular-price="<?php echo esc_attr( apply_filters( 'woobt_item_data_regular_price', wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), $product ) ); ?>"
					>
						<div class="woobt-choose">
							<input class="woobt-checkbox woobt-checkbox-this" type="checkbox" checked disabled/>
						</div>
					</div>
					<?php
				}

				// Other items.
				$order = 1;

				$global_product = $product;

				foreach ( $items as $item_key => $item ) {
					if ( ! empty( $item['id'] ) ) {
						echo $this->product_output( $item, $item_key, $product_id, $order, $thumbnail_size );
						$order++;
					} else {
						// heading/paragraph
						echo $this->woobt->text_output( $item, $item_key, $product_id );
					}
				}

				$product = $global_product;

				do_action( 'woobt_products_after', $product );
				?>
			</div>

			<?php do_action( 'woobt_products_below', $product ); ?>
		</div>
		<?php
		echo '</div><!-- /woobt-body -->';

		echo '<div class="woobt-footer"><div class="woobt-footer-inner">';
		echo '<div class="woobt-additional"></div>';
		echo '<div class="woobt-total"></div>';

		if ( $custom_position || $separate_atc ) {
			do_action( 'woobt_actions_above', $product );
			echo '<div class="woobt-actions">';
			do_action( 'woobt_actions_before', $product );
			echo '<div class="woobt-form">';
			echo '<input type="hidden" name="woobt_ids" class="woobt-ids woobt-ids-' . esc_attr( $product->get_id() ) . '" data-id="' . esc_attr( $product->get_id() ) . '"/>';
			echo '<input type="hidden" name="quantity" value="1"/>';
			echo '<input type="hidden" name="product_id" value="' . esc_attr( $product_id ) . '">';
			echo '<input type="hidden" name="variation_id" class="variation_id" value="0">';
			echo '<button type="submit" class="single_add_to_cart_button button alt"><span>' . $this->woobt->localization( 'add_all_to_cart', esc_html__( 'Add selected items', 'minimog' ) ) . '</span></button>';
			echo '</div>';
			do_action( 'woobt_actions_after', $product );
			echo '</div><!-- /woobt-actions -->';
			do_action( 'woobt_actions_below', $product );
		}

		echo '<div class="woobt-alert" style="display: none"></div>';

		echo '</div></div><!-- /woobt-footer -->';

		echo '</div><!-- /woobt-block-content -->';

		if ( $after_text = apply_filters( 'woobt_after_text', get_post_meta( $product_id, 'woobt_after_text', true ) ? : $this->woobt->localization( 'under_text' ), $product_id ) ) {
			echo '<div class="woobt-after-text woobt-text">' . do_shortcode( stripslashes( $after_text ) ) . '</div>';
		}

		do_action( 'woobt_wrap_after', $product );

		echo '</div><!-- /woobt-wrap -->';

		do_action( 'woobt_wrap_below', $product );
	}

	/**
	 * @param $item
	 * @param $item_key
	 * @param $product_id
	 * @param $order
	 * @param $thumbnail_size Added by Minimog
	 *
	 * @return mixed|null
	 */
	public function product_output( $item, $item_key, $product_id, $order, $thumbnail_size ) {
		global $product;
		$product           = $item['product'];
		$item_id           = $item['id'];
		$item_price        = $item['price'];
		$item_qty          = $item['qty'];
		$item_qty_min      = 1;
		$item_qty_max      = 1000;
		$pricing           = self::get_setting( 'pricing', 'sale_price' );
		$custom_qty        = apply_filters( 'woobt_custom_qty', get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on', $product_id );
		$checked_all       = apply_filters( 'woobt_checked_all', get_post_meta( $product_id, 'woobt_checked_all', true ) === 'on', $product_id );
		$separately        = apply_filters( 'woobt_separately', get_post_meta( $product_id, 'woobt_separately', true ) === 'on', $product_id );
		$layout            = self::get_setting( 'layout', 'default' );
		$separate_images   = $layout === 'separate';
		$position          = apply_filters( 'woobt_position', self::get_setting( 'position', apply_filters( 'woobt_default_position', 'before' ) ) );
		$is_outside_layout = 'below_summary' === $position && $separate_images;

		if ( $custom_qty ) {
			if ( get_post_meta( $product_id, 'woobt_limit_each_min_default', true ) === 'on' ) {
				$item_qty_min = $item_qty;
			} else {
				$item_qty_min = absint( get_post_meta( $product_id, 'woobt_limit_each_min', true ) ? : 0 );
			}

			$item_qty_max = absint( get_post_meta( $product_id, 'woobt_limit_each_max', true ) ? : 1000 );

			if ( $item_qty < $item_qty_min ) {
				$item_qty = $item_qty_min;
			}

			if ( $item_qty > $item_qty_max ) {
				$item_qty = $item_qty_max;
			}
		}

		$checked_individual = apply_filters( 'woobt_checked_individual', false, $item, $product_id );
		$item_price         = apply_filters( 'woobt_item_price', ! $separately ? $item_price : '100%', $item, $product_id );
		$item_name          = apply_filters( 'woobt_product_get_name', $product->get_name(), $product );

		ob_start();
		?>
		<div class="woobt-choose">
			<input class="woobt-checkbox" type="checkbox"
			       value="<?php echo esc_attr( $item_id ); ?>"
				<?php if ( ! $product->is_in_stock() ) : ?>
					disabled="disabled"
				<?php endif; ?>
				<?php if ( $product->is_in_stock() && ( $checked_all || $checked_individual ) ): ?>
					checked="checked"
				<?php endif; ?>
			/>
		</div>
		<?php
		$choose_html = ob_get_clean();

		ob_start();
		?>
		<div class="product woobt-product woobt-product-together"
		     data-key="<?php echo esc_attr( $item_key ); ?>"
		     data-order="<?php echo esc_attr( $order ); ?>"
		     data-id="<?php echo esc_attr( $product->is_type( 'variable' ) || ! $product->is_in_stock() ? 0 : $item_id ); ?>"
		     data-pid="<?php echo esc_attr( $item_id ); ?>"
		     data-name="<?php echo esc_attr( $item_name ); ?>"
		     data-new-price="<?php echo esc_attr( $item_price ); ?>"
		     data-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
		     data-price="<?php echo esc_attr( apply_filters( 'woobt_item_data_price', ( $pricing === 'sale_price' ) ? wc_get_price_to_display( $product ) : wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), $product ) ); ?>"
		     data-regular-price="<?php echo esc_attr( apply_filters( 'woobt_item_data_regular_price', wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), $product ) ); ?>"
		     data-qty="<?php echo esc_attr( $item_qty ); ?>"
		     data-o_qty="<?php echo esc_attr( $item_qty ); ?>"
		>

			<?php do_action( 'woobt_product_before', $product, $order ); ?>

			<div class="woobt-thumb-wrap">
				<?php if ( ! $is_outside_layout ) : ?>
					<?php echo '' . $choose_html; ?>
				<?php endif; ?>

				<?php if ( self::get_setting( 'show_thumb', 'yes' ) !== 'no' || ( 'below_summary' === $position && $separate_images ) ) { ?>
					<div class="woobt-thumb">
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
							<?php
							/**
							 * Disabled variation image changed.
							 * Because it not support properly image size.
							 * Move img out of div to make js disabled.
							 */ ?>
							<!--<div class="woobt-thumb-ori"></div>
							<div class="woobt-thumb-new"></div>-->
							<?php echo \Minimog_Woo::instance()->get_product_image( $product, $thumbnail_size ); ?>
						</a>
					</div>
				<?php } ?>
			</div>

			<div class="woobt-product-info">
				<h3 class="woobt-title">
					<?php
					/*if ( ! $custom_qty ) {
						$product_qty = '<span class="woobt-qty-num"><span class="woobt-qty">' . $item_qty . '</span> × </span>';
					} else {
						$product_qty = '';
					}

					echo apply_filters( 'woobt_product_qty', $product_qty, $item_qty, $product );*/

					if ( $product->is_in_stock() ) {
						$product_name = $product->get_name();
					} else {
						$product_name = '<s>' . $product->get_name() . '</s>';
					}

					if ( self::get_setting( 'link', 'yes' ) !== 'no' ) {
						$product_name = '<a ' . ( self::get_setting( 'link', 'yes' ) === 'yes_popup' ? 'class="woosq-btn" data-id="' . $item_id . '"' : '' ) . ' href="' . $product->get_permalink() . '" ' . ( self::get_setting( 'link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>' . $product_name . '</a>';
					} else {
						$product_name = '<span>' . $product_name . '</span>';
					}

					echo apply_filters( 'woobt_product_name', $product_name, $product );
					?>
				</h3><!-- /woobt-title -->

				<?php if ( self::get_setting( 'show_description', 'no' ) === 'yes' && ! $separate_images ) : ?>
					<?php echo '<div class="woobt-description">' . $product->get_short_description() . '</div>'; ?>
				<?php endif; ?>
				<div class="woobt-product-cart">
					<?php if ( $is_outside_layout ) : ?>
						<?php echo '' . $choose_html; ?>
					<?php endif; ?>

					<div class="woobt-product-cart-inner">
						<?php if ( 'variable' !== $product->get_type() || ! $separate_images ) : ?>
							<div class="woobt-price">
								<div class="woobt-price-new"></div>
								<div class="woobt-price-ori">
									<?php
									if ( ! $separately && ( $item_price !== '100%' ) ) {
										if ( $product->is_type( 'variable' ) ) {
											$item_ori_price_min = ( $pricing === 'sale_price' ) ? $product->get_variation_price( 'min', true ) : $product->get_variation_regular_price( 'min', true );
											$item_ori_price_max = ( $pricing === 'sale_price' ) ? $product->get_variation_price( 'max', true ) : $product->get_variation_regular_price( 'max', true );
											$item_new_price_min = $this->woobt->new_price( $item_ori_price_min, $item_price );
											$item_new_price_max = $this->woobt->new_price( $item_ori_price_max, $item_price );

											if ( $item_new_price_min < $item_new_price_max ) {
												$product_price = wc_format_price_range( $item_new_price_min, $item_new_price_max );
											} else {
												$product_price = wc_format_sale_price( $item_ori_price_min, $item_new_price_min );
											}
										} else {
											$item_ori_price = ( $pricing === 'sale_price' ) ? wc_get_price_to_display( $product, array( 'price' => $product->get_price() ) ) : wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) );
											$item_new_price = $this->woobt->new_price( $item_ori_price, $item_price );

											if ( $item_new_price < $item_ori_price ) {
												$product_price = wc_format_sale_price( $item_ori_price, $item_new_price );
											} else {
												$product_price = wc_price( $item_new_price );
											}
										}

										$product_price .= $product->get_price_suffix();
									} else {
										$product_price = $product->get_price_html();
									}

									echo apply_filters( 'woobt_product_price', $product_price, $product, $item );
									?>
								</div>
							</div><!-- /woobt-price -->
						<?php endif; ?>

						<?php if ( $product->is_type( 'variable' ) ) : ?>
							<div class="minimog-variation-select-wrap">
								<?php
								if ( ( self::get_setting( 'variations_selector', 'default' ) === 'wpc_radio' ) && class_exists( 'WPClever_Woovr' ) ) {
									echo '<div class="wpc_variations_form">';
									// use class name wpc_variations_form to prevent found_variation in woovr
									\WPClever_Woovr::woovr_variations_form( $product );
									echo '</div>';
								} else {
									\Minimog_Woo::instance()->get_product_variation_dropdown_html( $product, [
										'show_label' => false,
										'show_price' => true,
									] );

									$attributes           = $product->get_variation_attributes();
									$available_variations = $product->get_available_variations();

									if ( is_array( $attributes ) && ( count( $attributes ) > 0 ) ) {
										echo '<div class="variations_form woobt_variations_form" data-product_id="' . absint( $product->get_id() ) . '" data-product_variations="' . htmlspecialchars( wp_json_encode( $available_variations ) ) . '">';
										echo '<div class="variations">';

										foreach ( $attributes as $attribute_name => $options ) { ?>
											<div class="variation">
												<div class="label">
													<?php echo wc_attribute_label( $attribute_name ); ?>
												</div>
												<div class="select value">
													<?php
													$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
													wc_dropdown_variation_attribute_options( [
														'options'          => $options,
														'attribute'        => $attribute_name,
														'product'          => $product,
														'selected'         => $selected,
														'show_option_none' => esc_html__( 'Choose', 'minimog' ) . ' ' . wc_attribute_label( $attribute_name ),
													] );
													?>
												</div>
											</div><!-- /variation -->
										<?php }

										echo '<div class="reset">' . apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'minimog' ) . '</a>' ) . '</div>';
										echo '</div><!-- /variations -->';
										echo '</div><!-- /variations_form -->';

										if ( self::get_setting( 'show_description', 'no' ) === 'yes' ) {
											echo '<div class="woobt-variation-description"></div>';
										}
									}
								}
								?>
							</div>
						<?php endif; ?>

						<?php if ( $custom_qty ) : ?>
							<?php
							echo '<div class="woobt-quantity">';

							/*if ( self::get_setting( 'plus_minus', 'no' ) === 'yes' ) {
								echo '<div class="woobt-quantity-input">';
								echo '<div class="woobt-quantity-input-minus">-</div>';
							}*/

							woocommerce_quantity_input( array(
								'classes'     => array( 'input-text', 'woobt-qty', 'qty', 'text' ),
								'input_value' => $item_qty,
								'min_value'   => $item_qty_min,
								'max_value'   => $item_qty_max,
								'input_name'  => 'woobt_qty_' . $order,
								'woobt_qty'   => array(
									'input_value' => $item_qty,
									'min_value'   => $item_qty_min,
									'max_value'   => $item_qty_max,
								)
								// compatible with WPC Product Quantity.
							), $product );

							/*if ( self::get_setting( 'plus_minus', 'no' ) === 'yes' ) {
								echo '<div class="woobt-quantity-input-plus">+</div>';
								echo '</div>';
							}*/

							echo '</div><!-- /woobt-quantity -->';
							?>
						<?php endif; ?>

						<?php echo '<div class="woobt-availability">' . wc_get_stock_html( $product ) . '</div>'; ?>
					</div>
				</div>

				<?php do_action( 'woobt_product_after', $product, $order ); ?>
			</div>
		</div>
		<?php
		return apply_filters( 'woobt_product_output', ob_get_clean(), $item, $product_id, $order );
	}

	/**
	 * Map function
	 *
	 * @param $name
	 * @param $default
	 *
	 * @return mixed|null
	 */
	public static function get_setting( $name, $default = false ) {
		return \WPCleverWoobt::instance()->get_setting( $name, $default );
	}
}

Product_Bought_Together::instance()->initialize();
