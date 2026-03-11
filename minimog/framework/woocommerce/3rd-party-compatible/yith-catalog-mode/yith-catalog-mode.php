<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Name: YITH WooCommerce Catalog Mode
 * Plugin URI: https://wordpress.org/plugins/yith-woocommerce-catalog-mode/
 */
class Yith_Catalog_Mode {

	public function __construct() {
		add_filter('ywctm_modify_woocommerce_after_shop_loop_item', '__return_false');
	}
}

new Yith_Catalog_Mode();
