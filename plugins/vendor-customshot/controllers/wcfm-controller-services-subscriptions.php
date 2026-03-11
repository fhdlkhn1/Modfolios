<?php
/**
 * WCFM Services & Subscriptions Controller
 * Handles AJAX actions for services and subscription settings
 */

if (!defined('ABSPATH')) exit;

class WCFM_Services_Subscriptions_Controller {

    public function __construct() {
        // AJAX handlers
        add_action('wp_ajax_wcfm_save_vendor_service', array($this, 'save_vendor_service'));
        add_action('wp_ajax_wcfm_delete_vendor_service', array($this, 'delete_vendor_service'));
        add_action('wp_ajax_wcfm_save_subscription_settings', array($this, 'save_subscription_settings'));
        add_action('wp_ajax_wcfm_toggle_subscriptions', array($this, 'toggle_subscriptions'));
    }

    /**
     * Save or update a vendor service
     */
    public function save_vendor_service() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcfm_services_subscriptions_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
        }

        $vendor_id = get_current_user_id();
        if (!$vendor_id) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'vendor-customshot')));
        }

        // Sanitize input
        $service_name = sanitize_text_field($_POST['service_name']);
        $service_rate = floatval($_POST['service_rate']);
        $service_turnaround = sanitize_text_field($_POST['service_turnaround']);
        $service_description = sanitize_textarea_field($_POST['service_description']);
        $edit_index = isset($_POST['edit_index']) && $_POST['edit_index'] !== '' ? intval($_POST['edit_index']) : -1;

        // Validate required fields
        if (empty($service_name) || $service_rate < 0 || empty($service_turnaround)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'vendor-customshot')));
        }

        // Get existing services
        $services = get_user_meta($vendor_id, '_vendor_rate_card_services', true);
        $services = is_array($services) ? $services : array();

        // Create service data
        $service_data = array(
            'name' => $service_name,
            'base_rate' => $service_rate,
            'turnaround' => $service_turnaround,
            'description' => $service_description,
            'updated_at' => current_time('mysql')
        );

        // Add or update
        if ($edit_index >= 0 && isset($services[$edit_index])) {
            $services[$edit_index] = $service_data;
        } else {
            $service_data['created_at'] = current_time('mysql');
            $services[] = $service_data;
        }

        // Save
        update_user_meta($vendor_id, '_vendor_rate_card_services', $services);

        wp_send_json_success(array(
            'message' => __('Service saved successfully.', 'vendor-customshot'),
            'services' => $services
        ));
    }

    /**
     * Delete a vendor service
     */
    public function delete_vendor_service() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcfm_services_subscriptions_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
        }

        $vendor_id = get_current_user_id();
        if (!$vendor_id) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'vendor-customshot')));
        }

        $service_index = isset($_POST['service_index']) ? intval($_POST['service_index']) : -1;

        if ($service_index < 0) {
            wp_send_json_error(array('message' => __('Invalid service index.', 'vendor-customshot')));
        }

        // Get existing services
        $services = get_user_meta($vendor_id, '_vendor_rate_card_services', true);
        $services = is_array($services) ? $services : array();

        // Remove service
        if (isset($services[$service_index])) {
            unset($services[$service_index]);
            $services = array_values($services); // Re-index array
            update_user_meta($vendor_id, '_vendor_rate_card_services', $services);
        }

        wp_send_json_success(array(
            'message' => __('Service deleted successfully.', 'vendor-customshot'),
            'services' => $services
        ));
    }

    /**
     * Save subscription settings (prices)
     */
    public function save_subscription_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcfm_services_subscriptions_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
        }

        $vendor_id = get_current_user_id();
        if (!$vendor_id) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'vendor-customshot')));
        }

        $basic_price = floatval($_POST['basic_price']);
        $elite_price = floatval($_POST['elite_price']);

        // Validate prices
        if ($basic_price < 0 || $elite_price < 0) {
            wp_send_json_error(array('message' => __('Prices must be positive numbers.', 'vendor-customshot')));
        }

        // Save prices to user meta
        update_user_meta($vendor_id, 'wss_basic_price', $basic_price);
        update_user_meta($vendor_id, 'wss_elite_price', $elite_price);

        // Update subscription products if they exist
        $basic_product_id = get_user_meta($vendor_id, 'wss_basic_product_id', true);
        $elite_product_id = get_user_meta($vendor_id, 'wss_elite_product_id', true);

        if ($basic_product_id) {
            $this->update_subscription_product_price($basic_product_id, $basic_price);
        }

        if ($elite_product_id) {
            $this->update_subscription_product_price($elite_product_id, $elite_price);
        }

        // Also save to wcfmmp_profile_settings for backwards compatibility
        $vendor_settings = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);
        $vendor_settings = is_array($vendor_settings) ? $vendor_settings : array();
        $vendor_settings['wss_basic_price'] = $basic_price;
        $vendor_settings['wss_elite_price'] = $elite_price;
        update_user_meta($vendor_id, 'wcfmmp_profile_settings', $vendor_settings);

        wp_send_json_success(array(
            'message' => __('Subscription settings saved successfully.', 'vendor-customshot'),
            'basic_price' => $basic_price,
            'elite_price' => $elite_price
        ));
    }

    /**
     * Toggle subscriptions on/off
     */
    public function toggle_subscriptions() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcfm_services_subscriptions_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
        }

        $vendor_id = get_current_user_id();
        if (!$vendor_id) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'vendor-customshot')));
        }

        $enabled = isset($_POST['enabled']) && $_POST['enabled'] === 'yes' ? 'yes' : 'no';

        update_user_meta($vendor_id, '_wss_subscriptions_enabled', $enabled);

        // If enabling for the first time, create subscription products
        if ($enabled === 'yes') {
            $basic_product_id = get_user_meta($vendor_id, 'wss_basic_product_id', true);
            $elite_product_id = get_user_meta($vendor_id, 'wss_elite_product_id', true);

            $basic_price = get_user_meta($vendor_id, 'wss_basic_price', true) ?: 4.99;
            $elite_price = get_user_meta($vendor_id, 'wss_elite_price', true) ?: 14.99;

            // Create basic product if not exists
            if (!$basic_product_id) {
                $basic_product_id = $this->create_subscription_product($vendor_id, 'basic', $basic_price);
                if ($basic_product_id) {
                    update_user_meta($vendor_id, 'wss_basic_product_id', $basic_product_id);
                }
            }

            // Create elite product if not exists
            if (!$elite_product_id) {
                $elite_product_id = $this->create_subscription_product($vendor_id, 'elite', $elite_price);
                if ($elite_product_id) {
                    update_user_meta($vendor_id, 'wss_elite_product_id', $elite_product_id);
                }
            }
        }

        wp_send_json_success(array(
            'message' => $enabled === 'yes'
                ? __('Subscriptions enabled successfully.', 'vendor-customshot')
                : __('Subscriptions disabled.', 'vendor-customshot'),
            'enabled' => $enabled,
            'basic_product_id' => get_user_meta($vendor_id, 'wss_basic_product_id', true),
            'elite_product_id' => get_user_meta($vendor_id, 'wss_elite_product_id', true)
        ));
    }

    /**
     * Create a subscription product for a vendor
     */
    private function create_subscription_product($vendor_id, $type, $price) {
        // Check if WooCommerce Subscriptions is active
        if (!class_exists('WC_Subscriptions')) {
            return false;
        }

        $vendor_name = get_user_meta($vendor_id, 'nickname', true);
        if (!$vendor_name) {
            $user = get_user_by('id', $vendor_id);
            $vendor_name = $user ? $user->display_name : 'Vendor';
        }

        $product_title = $type === 'basic'
            ? sprintf(__('%s - Basic Subscription', 'vendor-customshot'), $vendor_name)
            : sprintf(__('%s - Premium Subscription', 'vendor-customshot'), $vendor_name);

        $product_description = $type === 'basic'
            ? __('Basic tier subscription - 10% discount on all products from this creator.', 'vendor-customshot')
            : __('Premium tier subscription - 10% discount plus exclusive content access from this creator.', 'vendor-customshot');

        // Create the product
        $product = new WC_Product_Subscription();
        $product->set_name($product_title);
        $product->set_description($product_description);
        $product->set_short_description($product_description);
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_regular_price($price);
        $product->set_virtual(true);
        $product->set_sold_individually(true);

        // Set subscription meta
        $product->update_meta_data('_subscription_price', $price);
        $product->update_meta_data('_subscription_period', 'month');
        $product->update_meta_data('_subscription_period_interval', '1');
        $product->update_meta_data('_subscription_length', '0'); // Never expires

        // Set vendor
        $product->update_meta_data('_wcfm_product_author', $vendor_id);

        $product_id = $product->save();

        if ($product_id) {
            // Assign to vendor using WCFM
            update_post_meta($product_id, '_wcfm_product_author', $vendor_id);
            wp_update_post(array(
                'ID' => $product_id,
                'post_author' => $vendor_id
            ));

            // Store subscription type
            update_post_meta($product_id, '_wss_subscription_type', $type);
            update_post_meta($product_id, '_wss_vendor_id', $vendor_id);
        }

        return $product_id;
    }

    /**
     * Update subscription product price
     */
    private function update_subscription_product_price($product_id, $price) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }

        $product->set_regular_price($price);
        $product->update_meta_data('_subscription_price', $price);
        $product->save();

        return true;
    }
}

// Initialize
new WCFM_Services_Subscriptions_Controller();
