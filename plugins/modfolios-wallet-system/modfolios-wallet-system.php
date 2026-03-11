<?php
/**
 * Plugin Name: Modfolios Wallet System (Woo + WCFM)
 * Description: Wallet topup via a zero-price product (custom price per cart item) + wallet payment gateway at checkout.
 * Version: 2.0.0
 */

if (!defined('ABSPATH')) exit;

class Modfolios_Wallet_System {
    const WALLET_META_KEY    = '_modfolios_user_wallet';
    const TOPUP_PRODUCT_ID   = 24074;
    const TOPUP_CART_FLAG    = '_modfolios_wallet_topup';
    const TOPUP_AMOUNT_KEY   = '_modfolios_topup_amount';
    const NONCE_ACTION       = 'modfolios_wallet_nonce';
    const SAVED_CART_META_KEY = '_modfolios_saved_cart';

    public function __construct() {
        // My Account tab + endpoint
        add_action('init', [$this, 'register_account_endpoint']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_account_menu_item']);
        add_action('woocommerce_account_wallet-topup_endpoint', [$this, 'render_wallet_topup_page']);

        // Handle topup form submit
        add_action('template_redirect', [$this, 'handle_topup_form_submit']);

        add_action('woocommerce_checkout_create_order', [$this, 'mark_wallet_topup_order'], 10, 1);
        add_action('woocommerce_thankyou', [$this, 'restore_cart_after_wallet_topup'], 20, 1);

        // Apply custom price for topup cart item
        add_action('woocommerce_before_calculate_totals', [$this, 'apply_topup_custom_price'], 20, 1);

        // Credit wallet after successful payment for topup order
        add_action('woocommerce_payment_complete', [$this, 'maybe_credit_wallet_on_topup_payment'], 20, 1);
        add_action('woocommerce_order_status_completed', [$this, 'maybe_credit_wallet_on_topup_status_completed'], 20, 1);

        // Enqueue checkout enhancement script
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        if (!function_exists('is_checkout') || !is_checkout()) return;

        wp_enqueue_script(
            'modfolios-wallet-js',
            plugins_url('wallet.js', __FILE__),
            ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'wallet.js'),
            true
        );
    }

    /* ---------------------------
     * Wallet Helpers
     * --------------------------- */
    public static function get_wallet_balance($user_id) {
        $bal = get_user_meta($user_id, self::WALLET_META_KEY, true);
        if ($bal === '' || $bal === null) $bal = 0;
        return (float) $bal;
    }

    public static function set_wallet_balance($user_id, $amount) {
        $amount = max(0, (float) $amount);
        update_user_meta($user_id, self::WALLET_META_KEY, wc_format_decimal($amount, wc_get_price_decimals()));
    }

    public static function add_wallet_balance($user_id, $delta) {
        $current = self::get_wallet_balance($user_id);
        self::set_wallet_balance($user_id, $current + (float)$delta);
    }

    public static function subtract_wallet_balance($user_id, $delta) {
        $current = self::get_wallet_balance($user_id);
        $new = $current - (float)$delta;
        if ($new < 0) $new = 0;
        self::set_wallet_balance($user_id, $new);
    }

    /* ---------------------------
     * My Account: /wallet-topup
     * --------------------------- */
    public function register_account_endpoint() {
        add_rewrite_endpoint('wallet-topup', EP_ROOT | EP_PAGES);
    }

    public function add_query_vars($vars) {
        $vars[] = 'wallet-topup';
        return $vars;
    }

    public function add_account_menu_item($items) {
        $new = [];
        foreach ($items as $key => $label) {
            $new[$key] = $label;
            if ($key === 'dashboard') {
                $new['wallet-topup'] = __('Wallet Topup', 'modfolios');
            }
        }
        if (!isset($new['wallet-topup'])) {
            $new['wallet-topup'] = __('Wallet Topup', 'modfolios');
        }
        return $new;
    }

    public function render_wallet_topup_page() {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('Please login to top up your wallet.', 'modfolios') . '</p>';
            return;
        }

        wc_print_notices();

        $user_id = get_current_user_id();
        $bal = self::get_wallet_balance($user_id);

        echo '<div class="wallet__topup_page">';
        echo '<div class="topup__content">';
        echo '<p class="current__balance"><strong>' . esc_html__('Current balance', 'modfolios') . '</strong> ' . wc_price($bal) . '</p>';

        echo '<form method="post">';
        wp_nonce_field(self::NONCE_ACTION, 'modfolios_wallet_nonce');
        echo '<p class="input__wrapper">
                <label>' . esc_html__('Topup Amount', 'modfolios') . '</label><br/>
                <input type="number" name="topup_amount" min="1" step="0.01" required style="max-width: 240px;" />
              </p>';
        echo '<p class="button__wrapper"><button type="submit" name="modfolios_topup_submit" class="button alt">' . esc_html__('Add to Wallet', 'modfolios') . '</button></p>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }

    /* ---------------------------
     * Topup form submission
     * --------------------------- */
    public function handle_topup_form_submit() {

        if (!is_user_logged_in()) {
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return;
        }

        if (!isset($_POST['modfolios_topup_submit'])) {
            return;
        }

        $nonce = $_POST['modfolios_wallet_nonce'] ?? '';
        if (!$nonce || !wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            wc_add_notice(__('Security check failed.', 'modfolios'), 'error');
            return;
        }

        $amount_raw = wp_unslash($_POST['topup_amount'] ?? '');
        $amount     = (float) wc_format_decimal($amount_raw);

        if ($amount <= 0) {
            wc_add_notice(__('Please enter a valid topup amount.', 'modfolios'), 'error');
            return;
        }

        wc_load_cart();

        if (!WC()->cart || !WC()->session) {
            wc_add_notice(__('Cart is not available. Please refresh and try again.', 'modfolios'), 'error');
            return;
        }

        $user_id = get_current_user_id();

        // Save current cart snapshot
        $saved_cart = [];
        foreach (WC()->cart->get_cart() as $item) {
            $saved_cart[] = [
                'product_id'     => $item['product_id'],
                'variation_id'   => $item['variation_id'],
                'quantity'       => $item['quantity'],
                'variation'      => $item['variation'],
                'cart_item_data' => array_diff_key(
                    $item,
                    array_flip([
                        'key',
                        'product_id',
                        'variation_id',
                        'quantity',
                        'variation',
                        'data',
                        'line_total',
                        'line_tax',
                        'line_subtotal',
                        'line_subtotal_tax',
                    ])
                ),
            ];
        }
        update_user_meta($user_id, self::SAVED_CART_META_KEY, $saved_cart);

        // Clear cart and add topup product only
        WC()->cart->empty_cart();

        $pid     = (int) self::TOPUP_PRODUCT_ID;
        $product = wc_get_product($pid);

        if (!$product || !$product->is_purchasable()) {
            wc_add_notice(__('Topup product not available.', 'modfolios'), 'error');
            return;
        }

        WC()->cart->add_to_cart(
            $pid,
            1,
            0,
            [],
            [
                self::TOPUP_CART_FLAG  => 1,
                self::TOPUP_AMOUNT_KEY => $amount,
            ]
        );

        WC()->cart->calculate_totals();
        WC()->cart->set_session();

        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }

    /* ---------------------------
     * Mark topup orders
     * --------------------------- */
    public function mark_wallet_topup_order($order) {
        if (!$order instanceof WC_Order) {
            return;
        }

        foreach ($order->get_items() as $item) {
            if ((int) $item->get_product_id() === (int) self::TOPUP_PRODUCT_ID) {
                $order->update_meta_data('_modfolios_wallet_topup_order', 1);
                break;
            }
        }
    }

    /* ---------------------------
     * Restore cart after topup
     * --------------------------- */
    public function restore_cart_after_wallet_topup($order_id) {
        if (!$order_id) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $user_id = $order->get_user_id();
        if (!$user_id) {
            return;
        }

        if (!$order->get_meta('_modfolios_wallet_topup_order')) {
            return;
        }

        $saved_cart = get_user_meta($user_id, self::SAVED_CART_META_KEY, true);
        if (empty($saved_cart) || !is_array($saved_cart)) {
            return;
        }

        wc_load_cart();

        if (!WC()->cart) {
            return;
        }

        WC()->cart->empty_cart();

        foreach ($saved_cart as $item) {
            WC()->cart->add_to_cart(
                $item['product_id'],
                $item['quantity'],
                $item['variation_id'],
                $item['variation'],
                $item['cart_item_data']
            );
        }

        WC()->cart->calculate_totals();
        WC()->cart->set_session();

        delete_user_meta($user_id, self::SAVED_CART_META_KEY);
    }

    /* ---------------------------
     * Custom price for topup item
     * --------------------------- */
    public function apply_topup_custom_price($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;
        if (!$cart || $cart->is_empty()) return;

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (!empty($cart_item[self::TOPUP_CART_FLAG]) && isset($cart_item[self::TOPUP_AMOUNT_KEY])) {
                $amount = (float) $cart_item[self::TOPUP_AMOUNT_KEY];
                if ($amount > 0 && isset($cart_item['data']) && is_object($cart_item['data'])) {
                    $cart_item['data']->set_price($amount);
                }
            }
        }
    }

    /* ---------------------------
     * Credit wallet on topup order
     * --------------------------- */
    private function is_topup_order($order) {
        if (!$order instanceof WC_Order) return false;

        foreach ($order->get_items() as $item) {
            if ((int)$item->get_product_id() === (int)self::TOPUP_PRODUCT_ID) {
                $line_total = (float) $item->get_total();
                if ($line_total > 0) return true;
            }
        }
        return false;
    }

    private function credit_wallet_for_topup_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;

        if ($order->get_meta('_modfolios_wallet_credited') === 'yes') return;

        if (!$this->is_topup_order($order)) return;

        $user_id = $order->get_user_id();
        if (!$user_id) return;

        $credit = 0.0;
        foreach ($order->get_items() as $item) {
            if ((int)$item->get_product_id() === (int)self::TOPUP_PRODUCT_ID) {
                $credit += (float) $item->get_total();
            }
        }

        if ($credit <= 0) return;

        self::add_wallet_balance($user_id, $credit);

        $order->update_meta_data('_modfolios_wallet_credited', 'yes');
        $order->save();
    }

    public function maybe_credit_wallet_on_topup_payment($order_id) {
        $this->credit_wallet_for_topup_order($order_id);
    }

    public function maybe_credit_wallet_on_topup_status_completed($order_id) {
        $this->credit_wallet_for_topup_order($order_id);
    }
}

/* =========================================================
 * WooCommerce Payment Gateway: Modfolios Wallet
 * ========================================================= */
add_action('plugins_loaded', 'modfolios_init_wallet_gateway');

function modfolios_init_wallet_gateway() {
    if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_Modfolios_Wallet extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'modfolios_wallet';
            $this->method_title       = __('Modfolios Wallet', 'modfolios');
            $this->method_description = __('Let customers pay using their wallet balance.', 'modfolios');
            $this->has_fields         = true;
            $this->supports           = ['products'];
            $this->order_button_text  = __('Pay with Wallet', 'modfolios');

            $this->init_form_fields();
            $this->init_settings();

            $this->title   = $this->get_option('title', __('Pay with Wallet', 'modfolios'));
            $this->enabled = $this->get_option('enabled', 'yes');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __('Enable/Disable', 'modfolios'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable Wallet Payment', 'modfolios'),
                    'default' => 'yes',
                ],
                'title' => [
                    'title'       => __('Title', 'modfolios'),
                    'type'        => 'text',
                    'description' => __('Payment method title shown at checkout.', 'modfolios'),
                    'default'     => __('Pay with Wallet', 'modfolios'),
                ],
            ];
        }

        /**
         * Only available for logged-in users.
         */
        public function is_available() {
            if (!parent::is_available()) return false;
            if (!is_user_logged_in()) return false;
            return true;
        }

        /**
         * Show wallet balance and insufficient-balance message on checkout.
         */
        public function payment_fields() {
            $user_id    = get_current_user_id();
            $balance    = Modfolios_Wallet_System::get_wallet_balance($user_id);
            $cart_total = WC()->cart ? (float) WC()->cart->get_total('edit') : 0;
            $sufficient = $balance >= $cart_total;
            $topup_url  = wc_get_account_endpoint_url('wallet-topup');

            echo '<div class="modfolios-wallet-checkout-info" style="padding:4px 0;">';

            echo '<p style="margin:0 0 6px; font-size:14px;">'
                . '<strong>' . esc_html__('Wallet Balance:', 'modfolios') . '</strong> '
                . wc_price($balance)
                . '</p>';

            if ($sufficient) {
                echo '<p style="margin:0; color:#155724; font-size:13px;">'
                    . esc_html__('Your wallet balance covers this order.', 'modfolios')
                    . '</p>';
            } else {
                $needed = $cart_total - $balance;
                echo '<div style="background:#fff3cd; border-left:4px solid #ffc107; padding:12px 15px; border-radius:4px; margin-top:6px;">';
                echo '<p style="margin:0 0 8px; color:#856404; font-size:14px;">'
                    . sprintf(
                        esc_html__('You need %s more to pay with your wallet.', 'modfolios'),
                        '<strong>' . wc_price($needed) . '</strong>'
                    )
                    . '</p>';
                echo '<a href="' . esc_url($topup_url) . '" style="color:#856404; text-decoration:underline; font-weight:600;">'
                    . esc_html__('Top up your wallet from My Account', 'modfolios')
                    . ' &rarr;</a>';
                echo '</div>';
            }

            echo '</div>';
        }

        /**
         * Validate wallet balance before placing the order.
         */
        public function validate_fields() {
            $user_id = get_current_user_id();

            if (!$user_id) {
                wc_add_notice(__('You must be logged in to pay with your wallet.', 'modfolios'), 'error');
                return false;
            }

            $balance    = Modfolios_Wallet_System::get_wallet_balance($user_id);
            $cart_total = (float) WC()->cart->get_total('edit');

            if ($balance < $cart_total) {
                $topup_url = wc_get_account_endpoint_url('wallet-topup');
                wc_add_notice(
                    sprintf(
                        __('Insufficient wallet balance. You need %1$s more. <a href="%2$s">Top up your wallet</a>', 'modfolios'),
                        wc_price($cart_total - $balance),
                        esc_url($topup_url)
                    ),
                    'error'
                );
                return false;
            }

            return true;
        }

        /**
         * Process the wallet payment.
         */
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            if (!$order) {
                wc_add_notice(__('Order not found.', 'modfolios'), 'error');
                return ['result' => 'failure'];
            }

            $user_id     = $order->get_user_id();
            $balance     = Modfolios_Wallet_System::get_wallet_balance($user_id);
            $order_total = (float) $order->get_total();

            if ($balance < $order_total) {
                wc_add_notice(__('Insufficient wallet balance. Please top up your wallet.', 'modfolios'), 'error');
                return ['result' => 'failure'];
            }

            // Deduct from wallet
            Modfolios_Wallet_System::subtract_wallet_balance($user_id, $order_total);

            // Order note
            $order->add_order_note(
                sprintf(
                    'Wallet payment of %s deducted. Previous balance: %s, New balance: %s',
                    wc_price($order_total),
                    wc_price($balance),
                    wc_price($balance - $order_total)
                )
            );

            // Mark payment complete
            $order->payment_complete();

            // Auto-complete wallet orders
            $order->update_status('completed', __('Wallet order auto-completed.', 'modfolios'));

            // Empty cart
            WC()->cart->empty_cart();

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        }
    }

    // Register the gateway with WooCommerce
    add_filter('woocommerce_payment_gateways', function ($gateways) {
        $gateways[] = 'WC_Gateway_Modfolios_Wallet';
        return $gateways;
    });
}

new Modfolios_Wallet_System();
