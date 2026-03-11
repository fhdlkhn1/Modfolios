<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

/**
 * Compatible with WooCommerce Subscriptions plugin
 *
 * @author  WooCommerce
 *
 * @see     https://www.woocommerce.com/products/woocommerce-subscriptions/
 */
class Woocommerce_Subscriptions {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		add_action( 'woocommerce_cart_totals_after_order_total', [ $this, 'display_recurring_totals' ], 9 );
	}

	/**
	 * For some reason WC()->cart->recurring_carts become empty on ajax for some site, not all sites.\
	 * This make cart totals table missing recurring info.
	 * Then we need to re-create it.
	 *
	 * @return void
	 *
	 * @see \WC_Subscriptions_Cart::calculate_subscription_totals()
	 * @see \WC_Subscriptions_Cart::display_recurring_totals()
	 */
	public function display_recurring_totals() {
		if ( empty( WC()->cart->recurring_carts ) ) {
			do_action( 'woocommerce_subscription_cart_before_grouping' );

			$subscription_groups = array();

			// Group the subscription items by their cart item key based on billing schedule
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( \WC_Subscriptions_Product::is_subscription( $cart_item['data'] ) ) {
					$subscription_groups[ \WC_Subscriptions_Cart::get_recurring_cart_key( $cart_item ) ][] = $cart_item_key;
				}
			}

			do_action( 'woocommerce_subscription_cart_after_grouping' );

			$recurring_carts = array();

			// Back up the shipping method. Chances are WC is going to wipe the chosen_shipping_methods data
			WC()->session->set( 'wcs_shipping_methods', WC()->session->get( 'chosen_shipping_methods', array() ) );

			// Now let's calculate the totals for each group of subscriptions
			\WC_Subscriptions_Cart::set_calculation_type( 'recurring_total' );

			foreach ( $subscription_groups as $recurring_cart_key => $subscription_group ) {

				// Create a clone cart to calculate and store totals for this group of subscriptions
				$recurring_cart = clone WC()->cart;
				$product        = null;

				// Set the current recurring key flag on this class, and store the recurring_cart_key to the new cart instance.
				\WC_Subscriptions_Cart::set_recurring_cart_key( $recurring_cart_key );
				$recurring_cart->recurring_cart_key = $recurring_cart_key;

				// Remove any items not in this subscription group
				foreach ( $recurring_cart->get_cart() as $cart_item_key => $cart_item ) {
					if ( ! in_array( $cart_item_key, $subscription_group, true ) ) {
						unset( $recurring_cart->cart_contents[ $cart_item_key ] );
						continue;
					}

					if ( null === $product ) {
						$product = $cart_item['data'];
					}
				}

				$recurring_cart->start_date        = apply_filters( 'wcs_recurring_cart_start_date', gmdate( 'Y-m-d H:i:s' ), $recurring_cart );
				$recurring_cart->trial_end_date    = apply_filters( 'wcs_recurring_cart_trial_end_date', \WC_Subscriptions_Product::get_trial_expiration_date( $product, $recurring_cart->start_date ), $recurring_cart, $product );
				$recurring_cart->next_payment_date = apply_filters( 'wcs_recurring_cart_next_payment_date', \WC_Subscriptions_Product::get_first_renewal_payment_date( $product, $recurring_cart->start_date ), $recurring_cart, $product );
				$recurring_cart->end_date          = apply_filters( 'wcs_recurring_cart_end_date', \WC_Subscriptions_Product::get_expiration_date( $product, $recurring_cart->start_date ), $recurring_cart, $product );

				// Before calculating recurring cart totals, store this recurring cart object
				\WC_Subscriptions_Cart::set_cached_recurring_cart( $recurring_cart );

				// No fees recur (yet)
				if ( is_callable( array( $recurring_cart, 'fees_api' ) ) ) { // WC 3.2 +
					$recurring_cart->fees_api()->remove_all_fees();
				} else {
					$recurring_cart->fees = array();
				}

				$recurring_cart->fee_total = 0;
				//\WC_Subscriptions_Cart::maybe_restore_shipping_methods();
				$recurring_cart->calculate_totals();

				// Store this groups cart details
				$recurring_carts[ $recurring_cart_key ] = clone $recurring_cart;

				// And remove some other floatsam
				$recurring_carts[ $recurring_cart_key ]->removed_cart_contents = array();
				$recurring_carts[ $recurring_cart_key ]->cart_session_data     = array();
			}

			WC()->cart->recurring_carts = $recurring_carts;
		}
	}
}

Woocommerce_Subscriptions::instance()->initialize();
