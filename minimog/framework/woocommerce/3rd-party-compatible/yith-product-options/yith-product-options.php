<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Name: YITH WooCommerce Product Add-ons & Extra Options Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/
 */
class Yith_Product_Options {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ], 9999 );

		add_filter( 'yith_wccl_enable_handle_variation_gallery', '__return_false' );
	}

	public function frontend_scripts() {
		wp_enqueue_script( 'minimog-yith-product-options', MINIMOG_THEME_URI . '/framework/woocommerce/3rd-party-compatible/yith-product-options/frontend.js', [], MINIMOG_THEME_VERSION );
	}
}

new Yith_Product_Options();
