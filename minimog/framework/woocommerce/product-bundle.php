<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WPCleverWoosb' ) ) {
	class Product_Bundle extends \WPCleverWoosb {

		protected static $instance = null;

		const MINIMUM_PLUGIN_VERSION = '8.0.0';
		const RECOMMEND_PLUGIN_VERSION = '8.0.0';

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Init self constructor to avoid auto call parent::__construct
		 * This make code run twice times.
		 */
		public function __construct() {
		}

		public function initialize() {
			if ( ! $this->is_activate() ) {
				return;
			}

			if ( defined( 'WOOSB_VERSION' ) ) {
				if ( version_compare( WOOSB_VERSION, self::RECOMMEND_PLUGIN_VERSION, '<' ) ) {
					add_action( 'admin_notices', [ $this, 'admin_notice_recommend_plugin_version' ] );
				}

				if ( version_compare( WOOSB_VERSION, self::MINIMUM_PLUGIN_VERSION, '<' ) ) {
					return;
				}
			}

			add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );

			minimog_remove_filters_for_anonymous_class( 'woocommerce_woosb_add_to_cart', 'WPCleverWoosb', 'add_to_cart_form' );
			add_action( 'woocommerce_woosb_add_to_cart', [ $this, 'add_to_cart_form' ] );
		}

		public function is_activate() {
			return class_exists( 'WPCleverWoosb' );
		}

		public function frontend_scripts() {
			$min = \Minimog_Enqueue::instance()->get_min_suffix();
			$rtl = \Minimog_Enqueue::instance()->get_rtl_suffix();

			wp_dequeue_style( 'woosb-frontend' );

			wp_register_style( 'minimog-wc-product-bundle', MINIMOG_THEME_URI . "/assets/css/wc/product-bundle{$rtl}{$min}.css", null, MINIMOG_THEME_VERSION );
			wp_enqueue_style( 'minimog-wc-product-bundle' );
		}

		public function admin_notice_recommend_plugin_version() {
			minimog_notice_required_plugin_version( 'WPC Product Bundles for WooCommerce', self::RECOMMEND_PLUGIN_VERSION );
		}

		public function get_types() {
			return self::$types;
		}

		public function add_to_cart_form() {
			/**
			 * @var \WC_Product
			 */ global $product;

			if ( ! $product || ! $product->is_type( 'woosb' ) ) {
				return;
			}

			if ( $product->has_variables() ) {
				wp_enqueue_script( 'wc-add-to-cart-variation' );
			}

			$position = get_option( '_woosb_bundled_position', 'above' );
			$can_show = apply_filters( 'woosb_show_bundled', true, $product->get_id() );

			if ( 'above' === $position && $can_show ) {
				$this->minimog_show_bundled();
			}

			wc_get_template( 'single-product/add-to-cart/simple.php' );

			if ( 'below' === $position && $can_show ) {
				$this->minimog_show_bundled();
			}
		}

		/**
		 * @param \WC_Product_Woosb $product
		 *
		 * @see \WPCleverWoosb::show_bundled()
		 */
		function minimog_show_bundled( $product = null ) {
			if ( ! $product ) {
				global $product;
			}

			if ( ! $product || ! is_a( $product, 'WC_Product_Woosb' ) ) {
				return;
			}

			$items = $product->get_items();

			if ( empty( $items ) ) {
				return;
			}

			$order                 = 1;
			$product_id            = $product->get_id();
			$fixed_price           = $product->is_fixed_price();
			$has_variables         = $product->has_variables();
			$has_optional          = $product->has_optional();
			$discount_amount       = $product->get_discount_amount();
			$discount_percentage   = $product->get_discount_percentage();
			$exclude_unpurchasable = $product->exclude_unpurchasable();
			$total_limit           = get_post_meta( $product_id, 'woosb_total_limits', true ) === 'on';
			$total_min             = get_post_meta( $product_id, 'woosb_total_limits_min', true );
			$total_max             = get_post_meta( $product_id, 'woosb_total_limits_max', true );
			$whole_min             = get_post_meta( $product_id, 'woosb_limit_whole_min', true ) ?: 1;
			$whole_max             = get_post_meta( $product_id, 'woosb_limit_whole_max', true ) ?: '-1';
			$layout                = get_post_meta( $product_id, 'woosb_layout', true ) ?: 'unset';
			$layout                = $layout !== 'unset' ? $layout : \WPCleverWoosb_Helper::get_setting( 'layout', 'list' );
			$bundled_price         = \WPCleverWoosb_Helper()->get_setting( 'bundled_price', 'price' );
			$products_class        = apply_filters( 'woosb_products_class', 'woosb-products woosb-products-layout-' . $layout, $product );

			do_action( 'woosb_before_wrap', $product );

			echo '<div class="woosb-wrap woosb-bundled" data-id="' . esc_attr( $product_id ) . '">';

			if ( $before_text = apply_filters( 'woosb_before_text', get_post_meta( $product_id, 'woosb_before_text', true ), $product_id ) ) {
				echo '<div class="woosb-before-text woosb-text">' . wp_kses_post( do_shortcode( $before_text ) ) . '</div>';
			}

			do_action( 'woosb_before_table', $product );
			?>
			<div class="<?php echo esc_attr( $products_class ); ?>"
			     data-discount-amount="<?php echo esc_attr( $discount_amount ); ?>"
			     data-discount="<?php echo esc_attr( $discount_percentage ); ?>"
			     data-fixed-price="<?php echo esc_attr( $fixed_price ? 'yes' : 'no' ); ?>"
			     data-price="<?php echo esc_attr( wc_get_price_to_display( $product ) ); ?>"
			     data-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
			     data-variables="<?php echo esc_attr( $has_variables ? 'yes' : 'no' ); ?>"
			     data-optional="<?php echo esc_attr( $has_optional ? 'yes' : 'no' ); ?>"
			     data-min="<?php echo esc_attr( $whole_min ); ?>"
			     data-max="<?php echo esc_attr( $whole_max ); ?>"
			     data-total-min="<?php echo esc_attr( $total_limit && $total_min ? $total_min : 0 ); ?>"
			     data-total-max="<?php echo esc_attr( $total_limit && $total_max ? $total_max : '-1' ); ?>"
			     data-exclude-unpurchasable="<?php echo esc_attr( $exclude_unpurchasable ? 'yes' : 'no' ); ?>"
			>
				<?php
				// store global $product.
				$global_product    = $product;
				$global_product_id = $product_id;

				foreach ( $items as $key => $item ) {
					if ( ! empty( $item['id'] ) ) {
						/**
						 * @var \WC_Product_Variable $product
						 */
						$product = wc_get_product( $item['id'] );

						if ( ! $product || in_array( $product->get_type(), $this->get_types(), true ) ) {
							continue;
						}

						if ( ! apply_filters( 'woosb_item_exclude', true, $product, $global_product ) ) {
							continue;
						}

						$item_qty = isset( $item['qty'] ) ? (float) $item['qty'] : 1;
						$item_min = ! empty( $item['min'] ) ? (float) $item['min'] : 0;
						$item_max = ! empty( $item['max'] ) ? (float) $item['max'] : 10000;
						$optional = ! empty( $item['optional'] );

						if ( $optional ) {
							if ( ( $max_purchase = $product->get_max_purchase_quantity() ) && ( $max_purchase > 0 ) && ( $max_purchase < $item_max ) ) {
								// get_max_purchase_quantity can return -1
								$item_max = $max_purchase;
							}

							if ( $item_qty < $item_min ) {
								$item_qty = $item_min;
							}

							if ( ( $item_max > $item_min ) && ( $item_qty > $item_max ) ) {
								$item_qty = $item_max;
							}
						}

						$item_class = 'product woosb-item-product woosb-product woosb-product-type-' . $product->get_type();

						if ( $optional ) {
							$item_class .= ' woosb-product-optional';
						}

						if ( ! apply_filters( 'woosb_item_visible', true, $product, $global_product_id ) ) {
							$item_class .= ' woosb-product-hidden';
						}

						if ( ! $product->is_in_stock() || ! $product->has_enough_stock( $item_qty ) || ! $product->is_purchasable() ) {
							if ( ! apply_filters( 'woosb_allow_unpurchasable_qty', false ) ) {
								$item_qty = 0;
							}

							$item_class .= ' woosb-product-unpurchasable';
						}

						$quantity_input_html = '';
						if ( $optional ) {
							ob_start();
							?>
							<?php if ( $product->is_in_stock() && ( $product->is_type( 'variable' ) || $product->is_purchasable() ) ) { ?>
								<div class="woosb-quantity">
									<?php
									woocommerce_quantity_input( array(
										'input_value' => $item_qty,
										'min_value'   => $item_min,
										'max_value'   => $item_max,
										'woosb_qty'   => array(
											'input_value' => $item_qty,
											'min_value'   => $item_min,
											'max_value'   => $item_max,
										),
										'classes'     => apply_filters( 'woosb_qty_classes', [
											'input-text',
											'woosb-qty',
											'qty',
											'text',
										] ),
										'input_name'  => 'woosb_qty_' . $order
										// compatible with WPC Product Quantity
									), $product );
									?>
								</div>
							<?php } else { ?>
								<div class="woosb-quantity woosb-quantity-disabled">
									<input type="number" class="input-text qty text" value="0" disabled/>
								</div>
							<?php } ?>
							<?php
							$quantity_input_html = ob_get_clean();
						}

						do_action( 'woosb_above_item', $product, $global_product, $order );

						$has_link = $product->is_visible() && ( \WPCleverWoosb_Helper()->get_setting( 'bundled_link', 'yes' ) !== 'no' );
						?>
						<div class="<?php echo esc_attr( apply_filters( 'woosb_item_class', $item_class, $product, $global_product, $order ) ); ?>"
						     data-key="<?php echo esc_attr( $key ); ?>"
						     data-name="<?php echo esc_attr( $product->get_name() ); ?>"
						     data-id="<?php echo esc_attr( $product->is_type( 'variable' ) ? 0 : $item['id'] ); ?>"
						     data-price="<?php echo esc_attr( \WPCleverWoosb_Helper::get_price_to_display( $product ) ); ?>"
						     data-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
						     data-qty="<?php echo esc_attr( $item_qty ); ?>"
						     data-order="<?php echo esc_attr( $order ); ?>"
						>
							<?php do_action( 'woosb_before_item', $product, $global_product, $order ); ?>

							<?php if ( \WPCleverWoosb_Helper()->get_setting( 'bundled_thumb', 'yes' ) !== 'no' ) { ?>
								<div class="woosb-thumb">
									<?php if ( $has_link ) {
										echo '<a ' . ( \WPCleverWoosb_Helper::get_setting( 'bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-link no-ajaxy" data-id="' . $item['id'] . '" data-context="woosb"' : '' ) . ' href="' . esc_url( $product->get_permalink() ) . '" ' . ( \WPCleverWoosb_Helper::get_setting( 'bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
									} ?>
									<?php
									/**
									 * Disabled variation image changed.
									 * Because it's not support properly image size.
									 * Move img out of div to make js disabled.
									 */ ?>
									<!--<div class="woosb-thumb-ori"></div>
									<div class="woosb-thumb-new"></div>-->
									<?php
									$product_image = \Minimog_Woo::instance()->get_product_image( $product, \Minimog_Woo::instance()->get_loop_product_image_size() );
									echo apply_filters( 'woosb_item_thumbnail', $product_image, $product );
									?>

									<?php if ( $has_link ) {
										echo '</a>';
									} ?>
								</div><!-- /woosb-thumb -->
							<?php } ?>

							<div class="woosb-product-info">
								<div class="woosb-product-main-info">
									<div class="woosb-title-wrap">
										<?php
										do_action( 'woosb_before_item_name', $product );

										echo '<h3 class="woosb-title post-title-2-rows">';

										if ( ( \WPCleverWoosb_Helper()->get_setting( 'bundled_qty', 'yes' ) === 'yes' ) && ! $optional ) {
											echo apply_filters( 'woosb_item_qty', $item['qty'] . ' × ', $item['qty'], $product );
										}

										$item_name    = '';
										$product_name = apply_filters( 'woosb_item_product_name', $product->get_name(), $product );

										if ( $has_link ) {
											$item_name .= '<a ' . ( \WPCleverWoosb_Helper::get_setting( 'bundled_link', 'yes' ) === 'yes_popup' ? 'class="woosq-link no-ajaxy" data-id="' . esc_attr( $item['id'] ) . '" data-context="woosb"' : '' ) . ' href="' . esc_url( $product->get_permalink() ) . '" ' . ( \WPCleverWoosb_Helper::get_setting( 'bundled_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
										}

										if ( $product->is_in_stock() && $product->has_enough_stock( $item_qty ) ) {
											$item_name .= $product_name;
										} else {
											$item_name .= '<s>' . $product_name . '</s>';
										}

										if ( $has_link ) {
											$item_name .= '</a>';
										}

										echo apply_filters( 'woosb_item_name', $item_name, $product, $global_product, $order );
										echo '</h3>';

										do_action( 'woosb_after_item_name', $product );

										if ( $bundled_price === 'price_under_name' || $bundled_price === 'subtotal_under_name' ) {
											self::show_bundled_price( $bundled_price, $fixed_price, $discount_percentage, $product, $item );
										}

										if ( \WPCleverWoosb_Helper::get_setting( 'bundled_description', 'no' ) === 'yes' ) {
											echo '<div class="woosb-description">' . apply_filters( 'woosb_item_description', $product->get_short_description(), $product ) . '</div>';
										}
										?>
									</div>

									<?php if ( $bundled_price === 'price' || $bundled_price === 'subtotal' ) { ?>
										<?php self::show_bundled_price( $bundled_price, $fixed_price, $discount_percentage, $product, $item ); ?>
									<?php } ?>
								</div>
								<div class="woosb-product-cart">
									<?php if ( $product->is_type( 'variable' ) ) : ?>
										<div class="minimog-variation-select-wrap">
											<?php
											if ( \WPCleverWoosb_Helper::get_setting( 'variations_selector', 'default' ) === 'woovr' && class_exists( 'WPClever_Woovr' ) ) {
												$allowed_terms = ! empty( $item['terms'] ) ? $item['terms'] : [];
												WPClever_Woovr::woovr_variations_form( $product, false, 'woosb', $allowed_terms );
											} else {
												\Minimog_Woo::instance()->get_product_variation_dropdown_html( $product, [
													'show_label' => false,
													'show_price' => false,
												] );

												$attributes           = $product->get_variation_attributes();
												$available_variations = $product->get_available_variations();
												$variations_json      = wp_json_encode( $available_variations );
												$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

												if ( is_array( $attributes ) && ( count( $attributes ) > 0 ) ) {
													$total_attrs = count( $attributes );
													$loop_count  = 0;

													echo '<div class="variations_form" data-product_id="' . absint( $product->get_id() ) . '" data-product_variations="' . esc_attr( $variations_attr ) . '">';
													echo '<div class="variations">';

													foreach ( $attributes as $attribute_name => $options ) {
														$loop_count ++;
														?>
														<div class="variation">
															<div class="label">
																<?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?>
															</div>
															<div class="select">
																<?php
																$attr     = 'attribute_' . sanitize_title( $attribute_name );
																$selected = isset( $_REQUEST[ $attr ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ $attr ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
																wc_dropdown_variation_attribute_options( [
																	'options'          => $options,
																	'attribute'        => $attribute_name,
																	'product'          => $product,
																	'selected'         => $selected,
																	'show_option_none' => wc_attribute_label( $attribute_name ),
																] );
																?>
															</div>
															<?php if ( $loop_count === $total_attrs ): ?>
																<?php echo '<div class="reset">' . apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'minimog' ) . '</a>' ) . '</div>'; ?>
															<?php endif; ?>
														</div>
													<?php }

													echo '</div>';
													echo '</div>';

													if ( get_option( '_woosb_bundled_description', 'no' ) === 'yes' ) {
														echo '<div class="woosb-variation-description"></div>';
													}
												}
											}

											do_action( 'woosb_after_item_variations', $product );
											?>
										</div>
									<?php endif; ?>
									<?php echo '' . $quantity_input_html; ?>
									<?php echo '<div class="woosb-availability">' . wc_get_stock_html( $product ) . '</div>'; ?>
								</div>
							</div>
							<?php do_action( 'woosb_after_item', $product, $global_product, $order ); ?>
						</div>
						<?php
						do_action( 'woosb_under_item', $product, $global_product, $order );
					} elseif ( ! empty( $item['text'] ) ) {
						$item_class = 'woosb-item-text';

						if ( ! empty( $item['type'] ) ) {
							$item_class .= ' woosb-item-text-type-' . $item['type'];
						}

						echo '<div class="' . esc_attr( apply_filters( 'woosb_item_text_class', $item_class, $item, $global_product, $order ) ) . '">';

						if ( empty( $item['type'] ) || ( $item['type'] === 'none' ) ) {
							echo $item['text'];
						} else {
							echo '<' . $item['type'] . '>' . $item['text'] . '</' . $item['type'] . '>';
						}

						echo '</div>';
					}

					$order ++;
				}

				// Restore global $product.
				$product    = $global_product;
				$product_id = $global_product_id;
				?>
			</div>
			<?php
			if ( ! $fixed_price && ( $has_variables || $optional ) ) {
				echo '<div class="woosb-total woosb-text"></div>';
			}

			echo '<div class="woosb-alert woosb-text" style="display: none"></div>';

			do_action( 'woosb_after_table', $product );

			if ( $after_text = apply_filters( 'woosb_after_text', get_post_meta( $product_id, 'woosb_after_text', true ), $product_id ) ) {
				echo '<div class="woosb-after-text woosb-text">' . wp_kses_post( do_shortcode( $after_text ) ) . '</div>';
			}

			echo '</div>';

			do_action( 'woosb_after_wrap', $product );
		}
	}

	Product_Bundle::instance()->initialize();
}

