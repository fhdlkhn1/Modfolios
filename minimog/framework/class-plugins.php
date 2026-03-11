<?php
defined( 'ABSPATH' ) || exit;

/**
 * Plugin installation and activation for WordPress themes
 */
class Minimog_Register_Plugins {

	const GOOGLE_DRIVER_API = 'AIzaSyDXOs0Bxx-uBEA4fH4fzgoHtl64g0RWv-g';

	public function __construct() {
		add_filter( 'insight_core_tgm_plugins', [ $this, 'register_required_plugins' ] );

		add_filter( 'insight_core_compatible_plugins', [ $this, 'register_compatible_plugins' ] );
	}

	public function register_required_plugins( $plugins ) {
		/*
		 * Array of plugin arrays. Required keys are name and slug.
		 * If the source is NOT from the .org repo, then source is also required.
		 */
		$new_plugins = array(
			array(
				'name'        => 'Insight Core',
				'description' => 'Core functions for WordPress theme',
				'slug'        => 'insight-core',
				'logo'        => 'insight',
				'source'      => $this->get_plugin_google_driver_url( '18Ed9yJ6c8uh70wokhdZg4rxBxFw3osKR' ),
				'version'     => '2.7.4',
				'required'    => true,
			),
			array(
				'name'        => 'Redux Framework',
				'description' => 'Build better sites in WordPress fast',
				'slug'        => 'redux-framework',
				'logo'        => 'redux-framework',
				'required'    => true,
			),
			array(
				'name'        => 'Elementor',
				'description' => 'The Elementor Website Builder has it all: drag and drop page builder, pixel perfect design, mobile responsive editing, and more.',
				'slug'        => 'elementor',
				'logo'        => 'elementor',
				'required'    => true,
			),
			array(
				'name'        => 'Thememove Addons For Elementor',
				'description' => 'Additional functions for Elementor',
				'slug'        => 'tm-addons-for-elementor',
				'logo'        => 'insight',
				'source'      => $this->get_plugin_google_driver_url( '1n6fzKXb7Ij1EocMBo7WPNClFZFTzfCoL' ),
				'version'     => '2.1.0',
				'required'    => true,
			),
			array(
				'name'        => 'WPForms',
				'description' => 'Beginner friendly WordPress contact form plugin. Use our Drag & Drop form builder to create your WordPress forms',
				'slug'        => 'wpforms-lite',
				'logo'        => 'wpforms-lite',
			),
			array(
				'name'        => 'WooCommerce',
				'description' => 'An eCommerce toolkit that helps you sell anything. Beautifully.',
				'slug'        => 'woocommerce',
				'logo'        => 'woocommerce',
			),
			array(
				'name'        => 'Insight Swatches',
				'description' => 'Allows you set a style for each attribute variation as color, image, or label on product page.',
				'slug'        => 'insight-swatches',
				'logo'        => 'insight',
				'source'      => $this->get_plugin_google_driver_url( '1CPYAIyTRCzThJJosm4Y88rX3PD9lhRJn' ),
				'version'     => '1.8.1',
			),
			array(
				'name'        => 'Insight Product Brands',
				'description' => 'Add brands for products',
				'slug'        => 'insight-product-brands',
				'logo'        => 'insight',
				'source'      => $this->get_plugin_google_driver_url( '182mSvi_dDcYPGkI2pMQlet-qYRiP9cE4' ),
				'version'     => '1.5.0',
			),
			array(
				'name'        => 'Conditional Discounts for WooCommerce',
				'description' => 'This plugin is a simple yet advanced WooCommerce dynamic discount plugin ideal for all types of deals.',
				'slug'        => 'woo-advanced-discounts',
				'logo'        => 'woo-advanced-discounts',
			),
			array(
				'name'        => 'Sales Countdown Timer (Premium)',
				'description' => 'Create a sense of urgency with a countdown to the beginning or end of sales, store launch or other events for higher conversions.',
				'slug'        => 'sctv-sales-countdown-timer',
				'logo'        => 'sctv-sales-countdown-timer',
				'source'      => $this->get_plugin_google_driver_url( '13zQPptOXt_FaKkBvMq8j4tCHCCiYFStQ' ),
				'version'     => '1.1.7.1',
			),
			array(
				'name'        => 'WPC Smart Compare for WooCommerce (Premium)',
				'description' => 'Allows your visitors to compare some products of your shop.',
				'slug'        => 'woo-smart-compare-premium',
				'logo'        => 'woo-smart-compare',
				'source'      => $this->get_plugin_google_driver_url( '19nUBdCvrHvC6_y5UJzr6pNI0RVn5ipRC' ),
				'version'     => '6.4.8',
			),
			array(
				'name'        => 'WPC Smart Wishlist for WooCommerce (Premium)',
				'description' => 'Allows your visitors save products for buy later.',
				'slug'        => 'woo-smart-wishlist-premium',
				'logo'        => 'woo-smart-wishlist',
				'source'      => $this->get_plugin_google_driver_url( '1hFu1yFc-7UBDhUZJILxgSRRA5CWwjY0V' ),
				'version'     => '5.0.2',
			),
			array(
				'name'        => 'WPC Frequently Bought Together for WooCommerce (Premium)',
				'description' => 'Increase your sales with personalized product recommendations',
				'slug'        => 'woo-bought-together-premium',
				'logo'        => 'woo-bought-together-premium',
				'source'      => $this->get_plugin_google_driver_url( '1_AytQZogli33Db7kXlRMxLBvvP1HEc42' ),
				'version'     => '7.6.8',
			),
			array(
				'name'        => 'WPC Product Bundles for WooCommerce (Premium)',
				'description' => 'This plugin helps you bundle a few products, offer them at a discount and watch the sales go up.',
				'slug'        => 'woo-product-bundle-premium',
				'logo'        => 'woo-product-bundle-premium',
				'source'      => $this->get_plugin_google_driver_url( '1Y5tKd048NNadQ_pSBD8Ey_pFjRCWnphH' ),
				'version'     => '8.3.2',
			),
			array(
				'name'        => 'WPC Product Tabs for WooCommerce (Premium)',
				'description' => 'Allows adding custom tabs to your products and provide your buyers with extra details for boosting customers’ confidence in the items.',
				'slug'        => 'wpc-product-tabs-premium',
				'logo'        => 'wpc-product-tabs-premium',
				'source'      => $this->get_plugin_google_driver_url( '1_NBkuNOjpnNBYilpl5xWjIos6knucRfw' ),
				'version'     => '4.2.2',
			),
			array(
				'name'        => 'Shoppable Images',
				'description' => 'Easily add \'shoppable images\' (images with hotspots) to your website or store',
				'slug'        => 'mabel-shoppable-images-lite',
				'logo'        => 'mabel-shoppable-images-lite',
			),
		);

		return array_merge( $plugins, $new_plugins );
	}

	public function register_compatible_plugins( $plugins ) {
		/**
		 * Each Item should have 'compatible'
		 * 'compatible': set be "true" to work correctly
		 */
		$new_plugins = [
			array(
				'name'        => 'Multi Currency for WooCommerce (Premium)',
				'description' => 'Allows to display prices and accepts payments in multiple currencies.',
				'slug'        => 'woocommerce-multi-currency',
				'logo'        => 'woocommerce-multi-currency',
				'source'      => $this->get_plugin_google_driver_url( '1fZjUHk5mUeRkrSbco81LC7DEqz5zN3Yj' ),
				'version'     => '2.3.10.1',
				'compatible'  => true,
			),
			array(
				'name'        => 'WPC Smart Notification for WooCommerce (Premium)',
				'description' => 'Increase trust, credibility, and sales with smart notifications.',
				'slug'        => 'wpc-smart-notification-premium',
				'logo'        => 'wpc-smart-notification',
				'source'      => $this->get_plugin_google_driver_url( '1L8jZ9F7fPMRbqdX51OCesrjQfzV6ZyZQ' ),
				'version'     => '2.4.4',
				'compatible'  => true,
			),
			array(
				'name'        => 'Revolution Slider',
				'description' => 'This plugin helps beginner-and mid-level designers WOW their clients with pro-level visuals. You’ll be able to create anything you can imagine, not just amazing, responsive sliders.',
				'slug'        => 'revslider',
				'logo'        => 'revslider',
				'source'      => $this->get_plugin_google_driver_url( '1O_lutNVt_E50b_iqWI1EuhCUpXAMNTUs' ),
				'version'     => '6.7.36',
				'compatible'  => true,
			),
			array(
				'name'        => 'WordPress Social Login',
				'description' => 'Allows your visitors to login, comment and share with Facebook, Google, Apple, Twitter, LinkedIn etc using customizable buttons.',
				'slug'        => 'miniorange-login-openid',
				'logo'        => 'miniorange-login-openid',
				'compatible'  => true,
			),
			array(
				'name'        => 'User Profile Picture',
				'description' => 'Allows your visitors upload their avatar with the native WP uploader.',
				'slug'        => 'metronet-profile-picture',
				'logo'        => 'metronet-profile-picture',
				'compatible'  => true,
			),
			array(
				'name'        => 'DCO Comment Attachment',
				'description' => 'Allows your visitors to attach files with their comments.',
				'slug'        => 'dco-comment-attachment',
				'logo'        => 'dco-comment-attachment',
				'compatible'  => true,
			),
			array(
				'name'        => 'hCaptcha for WordPress',
				'description' => 'Add captcha to protects user privacy, rewards websites, and helps companies get their data labeled. Help build a better web.',
				'slug'        => 'hcaptcha-for-forms-and-more',
				'logo'        => 'hcaptcha-for-forms-and-more',
				'compatible'  => true,
			),
		];

		return array_merge( $plugins, $new_plugins );
	}

	public function get_plugin_google_driver_url( $file_id ) {
		return "https://www.googleapis.com/drive/v3/files/{$file_id}?alt=media&key=" . self::GOOGLE_DRIVER_API;
	}
}

new Minimog_Register_Plugins();
