<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

/**
 * Compatible with WooCommerce Extra Product Options plugin.
 *
 * @see https://codecanyon.net/item/woocommerce-extra-product-options/7908619
 */
class WooCommerce_Extra_Product_Options {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );

		add_filter( 'wc_epo_override_edit_options', '__return_false' );
	}

	public function frontend_scripts() {
		$min = \Minimog_Enqueue::instance()->get_min_suffix();

		wp_register_style( 'minimog-woocommerce-tm-extra-product-options', MINIMOG_THEME_URI . "/assets/css/wc/woocommerce-tm-extra-product-options{$min}.css", null, MINIMOG_THEME_VERSION );
		wp_enqueue_style( 'minimog-woocommerce-tm-extra-product-options' );
	}
}

WooCommerce_Extra_Product_Options::instance()->initialize();
