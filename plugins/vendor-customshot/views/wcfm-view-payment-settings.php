<?php
/**
 * WCFM Payment Settings View
 * - Vendors: Configure payment/withdrawal methods
 * - Uses figma-input-field styling to match theme design
 */

if (!defined('ABSPATH')) exit;

global $WCFM, $WCFMmp;

// Check permissions
$wcfm_is_allow_billing_settings = apply_filters('wcfm_is_allow_billing_settings', true);
if (!$wcfm_is_allow_billing_settings) {
    wcfm_restriction_message_show("Payment Settings");
    return;
}

$user_id = apply_filters('wcfm_current_vendor_id', get_current_user_id());
$vendor_data = get_user_meta($user_id, 'wcfmmp_profile_settings', true);
if (!is_array($vendor_data)) $vendor_data = array();

// Get payment data
$payment_mode = isset($vendor_data['payment']['method']) ? esc_attr($vendor_data['payment']['method']) : '';
$paypal = isset($vendor_data['payment']['paypal']['email']) ? esc_attr($vendor_data['payment']['paypal']['email']) : '';
$skrill = isset($vendor_data['payment']['skrill']['email']) ? esc_attr($vendor_data['payment']['skrill']['email']) : '';
$ac_name = isset($vendor_data['payment']['bank']['ac_name']) ? esc_attr($vendor_data['payment']['bank']['ac_name']) : '';
$ac_number = isset($vendor_data['payment']['bank']['ac_number']) ? esc_attr($vendor_data['payment']['bank']['ac_number']) : '';
$bank_name = isset($vendor_data['payment']['bank']['bank_name']) ? esc_attr($vendor_data['payment']['bank']['bank_name']) : '';
$bank_addr = isset($vendor_data['payment']['bank']['bank_addr']) ? esc_textarea($vendor_data['payment']['bank']['bank_addr']) : '';
$routing_number = isset($vendor_data['payment']['bank']['routing_number']) ? esc_attr($vendor_data['payment']['bank']['routing_number']) : '';
$iban = isset($vendor_data['payment']['bank']['iban']) ? esc_attr($vendor_data['payment']['bank']['iban']) : '';
$swift = isset($vendor_data['payment']['bank']['swift']) ? esc_attr($vendor_data['payment']['bank']['swift']) : '';
$ifsc = isset($vendor_data['payment']['bank']['ifsc']) ? esc_attr($vendor_data['payment']['bank']['ifsc']) : '';

// Get available payment methods
$wcfm_marketplace_withdrwal_payment_methods = get_wcfm_marketplace_active_withdrwal_payment_methods();
if (isset($wcfm_marketplace_withdrwal_payment_methods['stripe_split'])) {
    unset($wcfm_marketplace_withdrwal_payment_methods['stripe_split']);
}
$wcfm_marketplace_withdrwal_payment_methods = array('' => __('Choose Withdrawal Payment Method', 'wc-frontend-manager')) + $wcfm_marketplace_withdrwal_payment_methods;

// Check if bank transfer is enabled
$bank_transfer_enabled = in_array('bank_transfer', array_keys($wcfm_marketplace_withdrwal_payment_methods));

// Check if Stripe is enabled and available
$stripe_enabled = array_key_exists('stripe', $wcfm_marketplace_withdrwal_payment_methods) && apply_filters('wcfm_is_allow_billing_stripe', true);
?>

<div class="collapse wcfm-collapse modfolio-payment-settings-page" id="wcfm_payment_settings">
    <div class="wcfm-page-headig">
		<span class="wcfmfa fa-currency"><?php echo get_woocommerce_currency_symbol(); ?></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Payment Setting', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <!-- Render Modfolio Welcomebox -->
        <?php modfolio_wcfm_render_header( '' );?>

        <div class="wcfm-payment-setting-section">
        <!-- Page Header -->
        <div class="payment-settings-header">
            <h1 class="page-title"><?php _e('Payment Settings', 'wc-frontend-manager'); ?></h1>
            <p class="page-subtitle"><?php _e('Configure your preferred withdrawal payment method', 'wc-frontend-manager'); ?></p>
        </div>

        <form id="wcfm_payment_settings_form" class="wcfm modfolio-payment-form">
            <?php wp_nonce_field('wcfm_settings', 'wcfm_nonce'); ?>

            <div class="payment-settings-container">

                <!-- Preferred Payment Method Section -->
                <div class="settings-section" id="payment-method-section">
                    <div class="section-header">
                        <h2><?php _e('Preferred Payment Method', 'wc-frontend-manager'); ?></h2>
                    </div>
                    <div class="section-content">
                        <div class="figma-input-field">
                            <span class="field-label"><?php _e('Payment Method', 'wc-frontend-manager'); ?></span>
                            <span class="field-separator"></span>
                            <select name="payment[method]" id="payment_mode" class="payment-method-select">
                                <?php foreach ($wcfm_marketplace_withdrwal_payment_methods as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($payment_mode, $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- PayPal Section -->
                <div class="settings-section paymode-section paymode-paypal" id="paypal-section" style="<?php echo ($payment_mode !== 'paypal') ? 'display:none;' : ''; ?>">
                    <div class="section-header">
                        <div class="section-title-with-icon">
                            <span class="section-icon paypal-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.773.773 0 0 1 .763-.65h7.42c2.468 0 4.24.625 5.266 1.856.956 1.146 1.264 2.732.916 4.713-.54 3.044-2.328 5.047-5.32 5.963-.778.236-1.646.362-2.582.376H9.012a.773.773 0 0 0-.764.65L7.076 21.337z" fill="#003087"/>
                                    <path d="M19.58 8.64c-.027.152-.057.307-.092.465-.787 4.024-3.478 5.404-6.92 5.404h-1.75a.85.85 0 0 0-.838.72l-.896 5.674-.254 1.607a.442.442 0 0 0 .437.513h3.07a.746.746 0 0 0 .737-.629l.03-.158.585-3.702.038-.205a.746.746 0 0 1 .737-.629h.464c3.004 0 5.357-1.22 6.046-4.752.287-1.477.139-2.71-.624-3.577a2.97 2.97 0 0 0-.77-.73z" fill="#009cde"/>
                                </svg>
                            </span>
                            <h2><?php _e('PayPal Details', 'wc-frontend-manager'); ?></h2>
                        </div>
                    </div>
                    <div class="section-content">
                        <div class="figma-input-field">
                            <span class="field-label"><?php _e('PayPal Email', 'wc-frontend-manager'); ?></span>
                            <span class="field-separator"></span>
                            <input type="email" name="payment[paypal][email]" value="<?php echo esc_attr($paypal); ?>" placeholder="<?php esc_attr_e('Enter your PayPal email address', 'wc-frontend-manager'); ?>">
                        </div>
                    </div>
                </div>

                <!-- Skrill Section -->
                <div class="settings-section paymode-section paymode-skrill" id="skrill-section" style="<?php echo ($payment_mode !== 'skrill') ? 'display:none;' : ''; ?>">
                    <div class="section-header">
                        <div class="section-title-with-icon">
                            <span class="section-icon skrill-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" fill="#862165"/>
                                    <path d="M8 12h8M12 8v8" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <h2><?php _e('Skrill Details', 'wc-frontend-manager'); ?></h2>
                        </div>
                    </div>
                    <div class="section-content">
                        <div class="figma-input-field">
                            <span class="field-label"><?php _e('Skrill Email', 'wc-frontend-manager'); ?></span>
                            <span class="field-separator"></span>
                            <input type="email" name="payment[skrill][email]" value="<?php echo esc_attr($skrill); ?>" placeholder="<?php esc_attr_e('Enter your Skrill email address', 'wc-frontend-manager'); ?>">
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer Section -->
                <?php if ($bank_transfer_enabled) : ?>
                <div class="settings-section paymode-section paymode-bank_transfer" id="bank-section" style="<?php echo ($payment_mode !== 'bank_transfer') ? 'display:none;' : ''; ?>">
                    <div class="section-header">
                        <div class="section-title-with-icon">
                            <span class="section-icon bank-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3" stroke="#00c4aa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <h2><?php _e('Bank Details', 'wc-frontend-manager'); ?></h2>
                        </div>
                    </div>
                    <div class="section-content">
                        <div class="bank-fields-grid">
                            <div class="figma-input-field">
                                <span class="field-label"><?php _e('Account Name', 'wc-frontend-manager'); ?></span>
                                <span class="field-separator"></span>
                                <input type="text" name="payment[bank][ac_name]" value="<?php echo esc_attr($ac_name); ?>" placeholder="<?php esc_attr_e('Your bank account name', 'wc-frontend-manager'); ?>">
                            </div>
                            <div class="figma-input-field">
                                <span class="field-label"><?php _e('Account Number', 'wc-frontend-manager'); ?></span>
                                <span class="field-separator"></span>
                                <input type="text" name="payment[bank][ac_number]" value="<?php echo esc_attr($ac_number); ?>" placeholder="<?php esc_attr_e('Your bank account number', 'wc-frontend-manager'); ?>">
                            </div>
                            <div class="figma-input-field">
                                <span class="field-label"><?php _e('Bank Name', 'wc-frontend-manager'); ?></span>
                                <span class="field-separator"></span>
                                <input type="text" name="payment[bank][bank_name]" value="<?php echo esc_attr($bank_name); ?>" placeholder="<?php esc_attr_e('Name of bank', 'wc-frontend-manager'); ?>">
                            </div>
                            <div class="figma-input-field">
                                <span class="field-label"><?php _e('Bank Address', 'wc-frontend-manager'); ?></span>
                                <span class="field-separator"></span>
                                <input type="text" name="payment[bank][bank_addr]" value="<?php echo esc_attr($bank_addr); ?>" placeholder="<?php esc_attr_e('Address of your bank', 'wc-frontend-manager'); ?>">
                            </div>
                            <div class="figma-input-field">
                                <span class="field-label"><?php _e('Routing Number', 'wc-frontend-manager'); ?></span>
                                <span class="field-separator"></span>
                                <input type="text" name="payment[bank][routing_number]" value="<?php echo esc_attr($routing_number); ?>" placeholder="<?php esc_attr_e('Routing number', 'wc-frontend-manager'); ?>">
                            </div>
                            <div class="figma-input-field">
                                <span class="field-label"><?php _e('IBAN', 'wc-frontend-manager'); ?></span>
                                <span class="field-separator"></span>
                                <input type="text" name="payment[bank][iban]" value="<?php echo esc_attr($iban); ?>" placeholder="<?php esc_attr_e('IBAN', 'wc-frontend-manager'); ?>">
                            </div>
                            <div class="figma-input-field">
                                <span class="field-label"><?php _e('Swift Code', 'wc-frontend-manager'); ?></span>
                                <span class="field-separator"></span>
                                <input type="text" name="payment[bank][swift]" value="<?php echo esc_attr($swift); ?>" placeholder="<?php esc_attr_e('Swift code', 'wc-frontend-manager'); ?>">
                            </div>
                            <div class="figma-input-field">
                                <span class="field-label"><?php _e('IFSC Code', 'wc-frontend-manager'); ?></span>
                                <span class="field-separator"></span>
                                <input type="text" name="payment[bank][ifsc]" value="<?php echo esc_attr($ifsc); ?>" placeholder="<?php esc_attr_e('IFSC code', 'wc-frontend-manager'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Stripe Section -->
                <?php if ($stripe_enabled && apply_filters('wcfm_is_allow_stripe_accounts_api', true)) :
                    $testmode = isset($WCFMmp->wcfmmp_withdrawal_options['test_mode']) ? true : false;
                    $client_id = sanitize_text_field($testmode ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_client_id'] : $WCFMmp->wcfmmp_withdrawal_options['stripe_client_id']);
                    $secret_key = sanitize_text_field($testmode ? $WCFMmp->wcfmmp_withdrawal_options['stripe_test_secret_key'] : $WCFMmp->wcfmmp_withdrawal_options['stripe_secret_key']);

                    if ($client_id && $secret_key) :
                        if (!class_exists('WCFM_Stripe_Connect_Client')) {
                            include_once $WCFM->plugin_path . "helpers/class-wcfm-stripe-connect-client.php";
                        }

                        $stripe_client = new WCFM_Stripe_Connect_Client($client_id, $secret_key);
                        $stripe_client->set_user_id($user_id);

                        if ($stripe_client->is_connected_to_stripe()) {
                            $stripe_message = __('You are connected with Stripe.', 'wc-frontend-manager');
                            $stripe_url = add_query_arg('stripe_action', 'disconnect', get_wcfm_settings_url());
                            $stripe_btn_text = __('Disconnect Stripe Account', 'wc-frontend-manager');
                            $stripe_btn_class = "stripe-disconnect-btn";
                            $stripe_connected = true;
                        } else {
                            $stripe_message = __('You are not connected with Stripe.', 'wc-frontend-manager');
                            $stripe_url = add_query_arg([
                                'stripe_action' => 'connect',
                                'vendor_country' => $stripe_client->get_platform_country()
                            ], get_wcfm_settings_url());
                            $stripe_btn_text = __('Connect with Stripe', 'wc-frontend-manager');
                            $stripe_btn_class = "stripe-connect-btn";
                            $stripe_connected = false;
                        }
                ?>
                <div class="settings-section paymode-section paymode-stripe" id="stripe-section" style="<?php echo ($payment_mode !== 'stripe') ? 'display:none;' : ''; ?>">
                    <div class="section-header">
                        <div class="section-title-with-icon">
                            <span class="section-icon stripe-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="24" height="24" rx="4" fill="#635bff"/>
                                    <path d="M11.5 9.5c0-.5.4-1 1-1 1.2 0 2.3.5 3 1.3l1.5-1.5c-1-1.2-2.6-2-4.5-2-2.5 0-4.5 1.5-4.5 4 0 4 6 3 6 5.5 0 .7-.6 1.2-1.5 1.2-1.4 0-2.7-.7-3.5-1.7l-1.5 1.5c1.2 1.4 3 2.2 5 2.2 2.7 0 4.5-1.5 4.5-4 0-4.5-6-3.5-6-5.5z" fill="#fff"/>
                                </svg>
                            </span>
                            <h2><?php _e('Stripe Connect', 'wc-frontend-manager'); ?></h2>
                        </div>
                    </div>
                    <div class="section-content">
                        <div class="stripe-connect-wrapper">
                            <div class="stripe-status <?php echo $stripe_connected ? 'connected' : 'disconnected'; ?>">
                                <span class="status-indicator"></span>
                                <span class="status-text"><?php echo esc_html($stripe_message); ?></span>
                            </div>
                            <a href="<?php echo esc_url($stripe_url); ?>" class="stripe-btn <?php echo esc_attr($stripe_btn_class); ?>">
                                <?php echo esc_html($stripe_btn_text); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; endif; ?>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="submit" class="save-btn" id="wcfm_payment_settings_submit">
                        <span class="btn-text"><?php _e('Save Payment Settings', 'wc-frontend-manager'); ?></span>
                        <span class="btn-loader" style="display:none;">
                            <svg class="spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.4 31.4" /></svg>
                        </span>
                    </button>
                </div>

                <!-- Messages -->
                <div class="form-messages" id="wcfm_payment_messages"></div>

            </div>
        </form>
        </div>

    </div>
</div>

<style>
/* Payment Settings Page Styles */
/* .modfolio-payment-settings-page {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
} */

/* Hide default WCFM elements */
/* .modfolio-payment-settings-page > .wcfm-page-headig {
    display: none !important;
} */

.wcfm-payment-setting-section{
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    width: 100%;
    padding: 20px;
}


/* Page Header */
.payment-settings-header {
    margin-bottom: 30px;
}

.payment-settings-header .page-title {
    font-size: 24px;
    font-weight: 800;
    color: #000;
    margin: 0 0 0px 0;
}

.payment-settings-header .page-subtitle {
    font-size: 14px;
    color: #666;
    margin: 0;
}

/* Payment Settings Container */
.payment-settings-container {
    /* max-width: 800px; */
}

/* Settings Section */
.modfolio-payment-settings-page .settings-section {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1);
}

.modfolio-payment-settings-page .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    padding-bottom: 10px;
}

.modfolio-payment-settings-page .section-header h2 {
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #000 !important;
    line-height: 1 !important;
    margin: 0 !important;
    font-style: normal !important;
}

.modfolio-payment-settings-page .section-title-with-icon {
    display: flex;
    align-items: center;
    gap: 12px;
}

.modfolio-payment-settings-page .section-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modfolio-payment-settings-page .section-icon.paypal-icon {
    background: #f5f7fa;
}

.modfolio-payment-settings-page .section-icon.skrill-icon {
    background: #f5f7fa;
}

.modfolio-payment-settings-page .section-icon.bank-icon {
    background: rgba(0, 196, 170, 0.1);
}

.modfolio-payment-settings-page .section-icon.stripe-icon {
    background: #f5f7fa;
}

.modfolio-payment-settings-page .section-content {
    padding: 20px 20px 35px;
}

/* Figma-style Input Field */
.modfolio-payment-settings-page .figma-input-field {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 16px;
    min-height: 57px;
    max-width: 550px;
}

.modfolio-payment-settings-page .figma-input-field:last-child {
    margin-bottom: 0;
}

.modfolio-payment-settings-page .figma-input-field .field-label {
    font-size: 13px;
    font-weight: 500;
    color: #1a1a1a;
    padding: 10px 14px;
    white-space: nowrap;
    min-width: fit-content;
}

.modfolio-payment-settings-page .figma-input-field .field-separator {
    width: 1px;
    height: 24px;
    background: #e0e0e0;
    flex-shrink: 0;
}

#wcfm-main-contentainer .figma-input-field input,
#wcfm-main-contentainer .figma-input-field select,
.modfolio-payment-settings-page .figma-input-field input,
.modfolio-payment-settings-page .figma-input-field select {
    flex: 1;
    border: none !important;
    background: transparent !important;
    padding: 10px 12px !important;
    font-size: 13px;
    color: #666;
    min-width: 0;
    outline: none !important;
    box-shadow: none !important;
}

.modfolio-payment-settings-page .figma-input-field input:focus,
.modfolio-payment-settings-page .figma-input-field select:focus {
    outline: none;
    box-shadow: none !important;
}

.modfolio-payment-settings-page .figma-input-field input::placeholder {
    color: #999;
}

.modfolio-payment-settings-page .figma-input-field select {
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 35px !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23999' d='M6 8L1 3h10z'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
}

/* Bank Fields Grid */
.bank-fields-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.bank-fields-grid .figma-input-field {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .bank-fields-grid {
        grid-template-columns: 1fr;
    }
}

/* Stripe Connect Wrapper */
.stripe-connect-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: flex-start;
}

.stripe-status {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    width: 100%;
    box-sizing: border-box;
}

.stripe-status .status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.stripe-status.connected .status-indicator {
    background: #00c4aa;
}

.stripe-status.disconnected .status-indicator {
    background: #f59e0b;
}

.stripe-status .status-text {
    font-size: 14px;
    color: #1a1a1a;
}

.stripe-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 28px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.stripe-connect-btn {
    background: #635bff;
    color: #fff;
}

.stripe-connect-btn:hover {
    background: #5147e5;
    color: #fff;
}

.stripe-disconnect-btn {
    background: #fff;
    color: #dc2626;
    border: 1px solid #dc2626;
}

.stripe-disconnect-btn:hover {
    background: #fef2f2;
    color: #dc2626;
}

/* Form Actions */
.form-actions {
    margin-top: 30px;
}

.save-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 32px;
    background: #00c4aa;
    color: #fff;
    border: none;
    border-radius: 30px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 200px;
}

.save-btn:hover {
    background: #00a89a;
}

.save-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.save-btn .spinner {
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Form Messages */
.form-messages {
    margin-top: 20px;
}

.form-message {
    padding: 14px 20px;
    border-radius: 12px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.form-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive */
@media (max-width: 768px) {
    .payment-settings-header .page-title {
        font-size: 22px;
    }

    .modfolio-payment-settings-page .section-content {
        padding: 15px;
    }

    .save-btn {
        width: 100%;
    }
}







/* ============= dark theme styling ============== */

.dark__theme .wcfm-payment-setting-section{
    background: #202020;
}
.dark__theme .payment-settings-header .page-title,
.dark__theme .modfolio-payment-settings-page .section-header h2 {
    color: #fff !important;
}
.dark__theme .modfolio-payment-settings-page .settings-section{
    box-shadow: 0px 0px 6px rgba(255, 255, 255, 0.1);
}
.dark__theme option {
    background: #202020;
    color: #fff;
}



</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Payment method toggle
    $('#payment_mode').on('change', function() {
        var selectedMethod = $(this).val();

        // Hide all payment method sections
        $('.paymode-section').hide();

        // Show the selected payment method section
        if (selectedMethod) {
            $('.paymode-' + selectedMethod).fadeIn(200);
        }
    });

    // Form submission
    $('#wcfm_payment_settings_form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submitBtn = $('#wcfm_payment_settings_submit');
        var $btnText = $submitBtn.find('.btn-text');
        var $btnLoader = $submitBtn.find('.btn-loader');
        var $messages = $('#wcfm_payment_messages');

        // Show loading state
        $btnText.hide();
        $btnLoader.show();
        $submitBtn.prop('disabled', true);
        $messages.empty();

        // Get WCFM nonce if available
        var wcfm_ajax_nonce = (typeof wcfm_params !== 'undefined' && wcfm_params.wcfm_ajax_nonce) ? wcfm_params.wcfm_ajax_nonce : '';

        // Serialize form data
        var formData = $form.serialize();

        $.ajax({
            url: (typeof wcfm_params !== 'undefined') ? wcfm_params.ajax_url : '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'wcfm_ajax_controller',
                controller: 'wcfm-settings',
                wcfm_settings_form: formData,
                wcfm_ajax_nonce: wcfm_ajax_nonce
            },
            success: function(response) {
                $btnLoader.hide();
                $btnText.show();

                try {
                    var data = typeof response === 'object' ? response : JSON.parse(response);

                    if (data.status) {
                        // Change button text to "Saved" and refresh page
                        $btnText.text('<?php echo esc_js(__('Saved', 'wc-frontend-manager')); ?>');
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        // Show error message only on failure
                        $submitBtn.prop('disabled', false);
                        $messages.html('<div class="form-message error">' + data.message + '</div>');
                    }
                } catch (err) {
                    $submitBtn.prop('disabled', false);
                    $messages.html('<div class="form-message error"><?php echo esc_js(__('An error occurred. Please try again.', 'wc-frontend-manager')); ?></div>');
                }
            },
            error: function() {
                $btnText.show();
                $btnLoader.hide();
                $submitBtn.prop('disabled', false);
                $messages.html('<div class="form-message error"><?php echo esc_js(__('Connection error. Please try again.', 'wc-frontend-manager')); ?></div>');
            }
        });
    });
});
</script>

<?php do_action('wcfm_vendor_end_settings_payment', $user_id); ?>
