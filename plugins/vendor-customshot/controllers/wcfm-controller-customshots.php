<?php
/**
 * WCFM Custom Shots Controller
 * Handles AJAX requests for custom shots
 */

if (!defined('ABSPATH')) exit;

// Include email handler
require_once(dirname(dirname(__FILE__)) . '/includes/class-customshot-emails.php');

class WCFM_Customshots_Controller {

    public function __construct() {
        $this->processing();
    }

    public function processing() {
        global $WCFM, $wpdb;

        $vendor_id = get_current_user_id();

        if (isset($_POST['action_type'])) {
            $action_type = sanitize_text_field($_POST['action_type']);
            $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;

            // Verify this shot belongs to this vendor
            $shot_vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
            if ($shot_vendor_id != $vendor_id) {
                wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'vendor-customshot')));
                return;
            }

            switch ($action_type) {
                case 'accept':
                    update_post_meta($shot_id, '_customshot_status', 'accepted');
                    wp_update_post(array('ID' => $shot_id, 'post_status' => 'publish'));

                    // Initialize chat when vendor directly accepts
                    Customshot_Chat::initialize_chat($shot_id);

                    // Send email to buyer
                    Customshot_Emails::send_accepted_to_buyer($shot_id);

                    wp_send_json_success(array('message' => __('Custom shot request accepted. Chat has been initialized.', 'vendor-customshot')));
                    break;

                case 'reject':
                case 'decline':
                    update_post_meta($shot_id, '_customshot_status', 'declined');
                    wp_update_post(array('ID' => $shot_id, 'post_status' => 'draft'));

                    // Send email to buyer
                    Customshot_Emails::send_declined_to_buyer($shot_id);

                    wp_send_json_success(array('message' => __('Custom shot request declined.', 'vendor-customshot')));
                    break;

                case 'send_quote':
                    $vendor_note = isset($_POST['vendor_note']) ? sanitize_textarea_field($_POST['vendor_note']) : '';
                    $vendor_quote = isset($_POST['vendor_quote']) ? floatval($_POST['vendor_quote']) : 0;
                    $delivery_date = isset($_POST['delivery_date']) ? sanitize_text_field($_POST['delivery_date']) : '';

                    if ($vendor_quote <= 0) {
                        wp_send_json_error(array('message' => __('Please enter a valid quote amount.', 'vendor-customshot')));
                        return;
                    }

                    if (empty($delivery_date)) {
                        wp_send_json_error(array('message' => __('Please select a delivery date.', 'vendor-customshot')));
                        return;
                    }

                    // Save quote data
                    update_post_meta($shot_id, '_customshot_vendor_note', $vendor_note);
                    update_post_meta($shot_id, '_customshot_vendor_quote', $vendor_quote);
                    update_post_meta($shot_id, '_customshot_delivery_date', $delivery_date);
                    update_post_meta($shot_id, '_customshot_quote_date', current_time('mysql'));
                    update_post_meta($shot_id, '_customshot_status', 'quoted');

                    wp_update_post(array('ID' => $shot_id, 'post_status' => 'publish'));

                    // Send email notification to buyer
                    Customshot_Emails::send_quote_to_buyer($shot_id);

                    wp_send_json_success(array('message' => __('Quote sent successfully! The buyer will be notified.', 'vendor-customshot')));
                    break;

                case 'get_details':
                    $shot = get_post($shot_id);
                    if (!$shot) {
                        wp_send_json_error(array('message' => __('Custom shot not found.', 'vendor-customshot')));
                        return;
                    }

                    $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
                    $buyer = get_user_by('id', $buyer_id);

                    $shoot_type_terms = wp_get_post_terms($shot_id, 'shoot_type');
                    $usage_type_terms = wp_get_post_terms($shot_id, 'usage_type');

                    $data = array(
                        'id'           => $shot_id,
                        'title'        => $shot->post_title,
                        'buyer_name'   => $buyer ? $buyer->display_name : __('Unknown', 'vendor-customshot'),
                        'buyer_email'  => $buyer ? $buyer->user_email : '',
                        'shoot_type'   => !empty($shoot_type_terms) ? $shoot_type_terms[0]->name : '-',
                        'usage_type'   => !empty($usage_type_terms) ? $usage_type_terms[0]->name : '-',
                        'brief'        => get_post_meta($shot_id, '_customshot_brief', true),
                        'deliverables' => get_post_meta($shot_id, '_customshot_deliverables', true),
                        'budget'       => get_post_meta($shot_id, '_customshot_budget', true),
                        'shoot_date'   => get_post_meta($shot_id, '_customshot_shoot_date', true),
                        'status'       => get_post_meta($shot_id, '_customshot_status', true),
                        'created_date' => $shot->post_date,
                    );

                    wp_send_json_success($data);
                    break;

                default:
                    wp_send_json_error(array('message' => __('Invalid action.', 'vendor-customshot')));
            }
        }
    }
}
