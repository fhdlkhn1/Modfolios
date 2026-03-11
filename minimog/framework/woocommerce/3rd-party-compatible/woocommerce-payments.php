<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

/**
 * Compatible with WooCommerce Payments plugin.
 *
 * @see https://wordpress.org/plugins/woocommerce-payments/
 */
class WC_Pay {

	private static $instance = null;

	/**
	 * Compatibility instance.
	 *
	 * @var \WCPay\MultiCurrency\MultiCurrency
	 */
	protected $multi_currency;

	/**
	 * Compatibility instance.
	 *
	 * @var \WCPay\MultiCurrency\Compatibility
	 */
	protected $compatibility;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		if ( class_exists( 'WC_Payments_Features' ) && \WC_Payments_Features::is_customer_multi_currency_enabled() ) {
			$this->multi_currency = WC_Payments_Multi_Currency();
			$this->compatibility  = $this->multi_currency->get_compatibility();

			add_filter( 'minimog/top_bar/components/currency_switcher/output', [
				$this,
				'get_currency_switcher_html',
			] );
			add_filter( 'minimog/header/components/currency_switcher/output', [ $this, 'get_currency_switcher_html' ] );
		}
	}

	/**
	 * Check whether the plugin activated
	 *
	 * @return boolean true if plugin activated
	 */
	public function is_activated() {
		return defined( 'WCPAY_PLUGIN_FILE' );
	}

	public function get_currency_switcher_html( $html ) {
		ob_start();
		$this->output_switcher();

		return ob_get_clean();
	}

	/**
	 * @return void
	 *
	 * @see \WCPay\MultiCurrency\CurrencySwitcherWidget::widget()
	 */
	public function output_switcher() {
		if ( method_exists( $this->compatibility, 'should_disable_currency_switching' ) ) {
			if ( $this->compatibility->should_disable_currency_switching() ) {
				return;
			}
		} else {
			if ( $this->compatibility->should_hide_widgets() ) { // Back compatible version below < 6.5.0
				return;
			}
		}

		$enabled_currencies = $this->multi_currency->get_enabled_currencies();

		if ( 1 === count( $enabled_currencies ) ) {
			return;
		}

		$with_symbol = false;
		$with_flag   = true;

		$select_options_html = '';
		$menu_toggle_link    = '';
		$menu_children_html  = '';

		global $wp;
		$base_url = home_url( $wp->request );

		foreach ( $enabled_currencies as $currency ) {
			$code           = $currency->get_code();
			$same_symbol    = html_entity_decode( $currency->get_symbol() ) === $code;
			$text           = $code;
			$menu_item_text = '<span class="currency-code">' . $code . '</span>';
			$is_selected    = $this->multi_currency->get_selected_currency()->code === $code;

			if ( $with_symbol && ! $same_symbol ) {
				$text = $currency->get_symbol() . ' ' . $text;

				$menu_item_text = '<span class="currency-symbol">' . $currency->get_symbol() . '</span>' . $menu_item_text;
			}
			if ( $with_flag ) {
				$text = $currency->get_flag() . ' ' . $text;

				$menu_item_text = '<span class="currency-flag">' . $currency->get_flag() . '</span>' . $menu_item_text;
			}

			$selected            = $is_selected ? ' selected' : '';
			$select_options_html .= "<option value=\"$code\"$selected>$text</option>"; // phpcs:ignore WordPress.Security.EscapeOutput

			// Custom switch menu.
			$menu_item_link_url = $this->add_get_params_to_url( $base_url );
			$menu_item_link_url = add_query_arg( 'currency', $code, $menu_item_link_url );
			$menu_item_link     = sprintf( '<a href="%1$s" data-currency="%2$s">%3$s</a>', $menu_item_link_url, $code, $menu_item_text );

			if ( $is_selected ) {
				$menu_toggle_link .= $menu_item_link;
			} else {
				$menu_children_html .= sprintf( '<li>%1$s</li>', $menu_item_link );
			}
		}
		?>
		<div class="currency-switcher-menu-wrap wc-pay">
			<ul class="menu currency-switcher-menu">
				<li class="menu-item-has-children">
					<?php echo $menu_toggle_link ?>
					<ul class="sub-menu"><?php echo $menu_children_html; ?></ul>
				</li>
			</ul>
			<!--<form>
				<?php /*$this->output_get_params(); */ ?>
				<select
					name="currency"
					onchange="this.form.submit()"
				>
					<?php /*echo $select_options_html; */ ?>
				</select>
			</form>-->
		</div>
		<?php
	}

	/**
	 * Output hidden inputs for every $_GET param.
	 * This prevent the switcher form to remove them on submit.
	 *
	 * @return void
	 *
	 * @see \WCPay\MultiCurrency\CurrencySwitcherWidget::output_get_params()
	 */
	private function output_get_params() {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( empty( $_GET ) ) {
			return;
		}
		$params = explode( '&', urldecode( http_build_query( $_GET ) ) );
		foreach ( $params as $param ) {
			$name_value = explode( '=', $param );
			$name       = $name_value[0];
			$value      = $name_value[1];
			if ( 'currency' === $name ) {
				continue;
			}
			echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
		}
		// phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Append url params for every $_GET param.
	 * This prevent the switcher menu links to remove them on click.
	 *
	 * @param $url
	 *
	 * @return mixed|string
	 *
	 * @see \WCPay\MultiCurrency\CurrencySwitcherWidget::output_get_params()
	 */
	private function add_get_params_to_url( $url ) {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! empty( $_GET ) ) {
			$params = explode( '&', urldecode( http_build_query( $_GET ) ) );
			foreach ( $params as $param ) {
				$name_value = explode( '=', $param );
				$name       = $name_value[0];
				$value      = $name_value[1];
				if ( 'currency' === $name ) {
					continue;
				}

				$url = add_query_arg( $name, $value, $url );
			}
		}

		return $url;
	}
}

WC_Pay::instance()->initialize();
