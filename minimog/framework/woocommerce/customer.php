<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

class Customer {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		add_filter( 'minimog/user_login/url', [ $this, 'change_login_url' ] );
		add_filter( 'minimog/user_register/url', [ $this, 'change_register_url' ] );
		add_filter( 'minimog/user_profile/text', [ $this, 'change_profile_text' ] );
		add_filter( 'minimog/user_profile/url', [ $this, 'change_profile_url' ] );

		add_filter( 'woocommerce_account_settings', [ $this, 'add_custom_account_settings' ] );
		add_action( 'woocommerce_register_form_start', [ $this, 'add_custom_fields_registration' ] );
		add_filter( 'woocommerce_registration_errors', [ $this, 'validate_custom_fields_registration' ], 10, 3 );
		add_action( 'woocommerce_created_customer', [ $this, 'save_custom_fields_registration' ] );
	}

	public function change_login_url( $url ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	public function change_register_url( $url ) {
		/**
		 * Go to my account register page instead of default register page.
		 */
		if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) {
			$url = wc_get_page_permalink( 'myaccount' );
		}

		return $url;
	}

	public function change_profile_text( $text ) {
		return __( 'My account', 'minimog' );
	}

	public function change_profile_url( $url ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	public function add_custom_account_settings( $settings ) {
		$new_fields = [
			[
				'desc'          => __( 'Allow customers input first name and last name', 'minimog' ),
				'id'            => 'woocommerce_registration_input_name_enable',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'autoload'      => false,
			],
		];

		$pos = array_search( 'woocommerce_enable_myaccount_registration', array_column( $settings, 'id' ) );
		array_splice( $settings, $pos + 1, 0, $new_fields );

		return $settings;
	}

	public function add_custom_fields_registration() {
		if ( 'yes' !== get_option( 'woocommerce_registration_input_name_enable', 'yes' ) ) {
			return;
		}

		/**
		 * Do not add name fields to avoid duplicate fields. It added by the plugin.
		 */
		if ( class_exists( 'WeDevs_Dokan' ) ) {
			return;
		}

		$first_name = ! empty( $_POST['billing_first_name'] ) ? sanitize_text_field( $_POST['billing_first_name'] ) : '';
		$last_name  = ! empty( $_POST['billing_last_name'] ) ? sanitize_text_field( $_POST['billing_last_name'] ) : '';
		?>
		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
			<label for="reg_billing_first_name"><?php esc_html_e( 'First name', 'minimog' ); ?>
				<span class="required">*</span></label>
			<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php echo esc_attr( $first_name ); ?>"/>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
			<label for="reg_billing_last_name"><?php esc_html_e( 'Last name', 'minimog' ); ?>
				<span class="required">*</span></label>
			<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php echo esc_attr( $last_name ); ?>"/>
		</p>
		<div class="clear"></div>
		<?php
	}

	public function validate_custom_fields_registration( $errors, $username, $email ) {
		if ( 'yes' === get_option( 'woocommerce_registration_input_name_enable', 'yes' ) ) {
			if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
				$errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'minimog' ) );
			}
			if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
				$errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required!.', 'minimog' ) );
			}
		}

		return $errors;
	}

	public function save_custom_fields_registration( $customer_id ) {
		if ( 'yes' === get_option( 'woocommerce_registration_input_name_enable', 'yes' ) ) {
			if ( isset( $_POST['billing_first_name'] ) ) {
				update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
				update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
			}

			if ( isset( $_POST['billing_last_name'] ) ) {
				update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
				update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
			}
		}
	}
}

Customer::instance()->initialize();
