<?php
/**
 * WCFM Custom Shot Chat Controller
 * Handles AJAX requests for chat messages and deliverables
 */

if (!defined('ABSPATH')) exit;

class WCFM_Customshot_Chat_Controller {

    public function __construct() {
        $this->processing();
    }

    public function processing() {
        global $WCFM, $wpdb;

        $user_id = get_current_user_id();

        if (!$user_id) {
            wp_send_json_error(array('message' => __('You must be logged in.', 'vendor-customshot')));
            return;
        }

        if (!isset($_POST['action_type'])) {
            wp_send_json_error(array('message' => __('Invalid request.', 'vendor-customshot')));
            return;
        }

        $action_type = sanitize_text_field($_POST['action_type']);
        $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;

        // Verify user has access to this shot
        if ($shot_id) {
            $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
            $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);

            if ($user_id != $vendor_id && $user_id != $buyer_id) {
                wp_send_json_error(array('message' => __('You do not have permission to access this conversation.', 'vendor-customshot')));
                return;
            }
        }

        switch ($action_type) {
            case 'send_message':
                $this->send_message($shot_id, $user_id);
                break;

            case 'upload_file':
                $this->upload_file($shot_id, $user_id);
                break;

            case 'get_new_messages':
                $this->get_new_messages($shot_id, $user_id);
                break;

            case 'upload_deliverable':
                $this->upload_deliverable($shot_id, $user_id);
                break;

            case 'remove_deliverable':
                $this->remove_deliverable($shot_id, $user_id);
                break;

            case 'submit_deliverables':
                $this->submit_deliverables($shot_id, $user_id);
                break;

            default:
                wp_send_json_error(array('message' => __('Invalid action.', 'vendor-customshot')));
        }
    }

    /**
     * Send a text message
     */
    private function send_message($shot_id, $user_id) {
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        if (empty($message)) {
            wp_send_json_error(array('message' => __('Message cannot be empty.', 'vendor-customshot')));
            return;
        }

        $message_id = Customshot_Chat::send_message($shot_id, $user_id, 'text', $message);

        if ($message_id) {
            // Send email notification to the other party
            $this->send_notification($shot_id, $user_id, $message);

            wp_send_json_success(array(
                'message_id' => $message_id,
                'message'    => __('Message sent.', 'vendor-customshot'),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to send message.', 'vendor-customshot')));
        }
    }

    /**
     * Upload a file in chat
     */
    private function upload_file($shot_id, $user_id) {
        if (!isset($_FILES['file'])) {
            wp_send_json_error(array('message' => __('No file uploaded.', 'vendor-customshot')));
            return;
        }

        $file = $_FILES['file'];

        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            wp_send_json_error(array('message' => __('File is too large. Maximum size is 10MB.', 'vendor-customshot')));
            return;
        }

        // Upload file
        $uploaded = Customshot_Chat::handle_file_upload($file);

        if (is_wp_error($uploaded)) {
            wp_send_json_error(array('message' => $uploaded->get_error_message()));
            return;
        }

        // Save message
        $message_id = Customshot_Chat::send_message($shot_id, $user_id, 'file', '', array(
            'url'  => $uploaded['url'],
            'name' => $uploaded['name'],
            'type' => $uploaded['type'],
        ));

        if ($message_id) {
            $is_image = strpos($uploaded['type'], 'image') !== false;

            wp_send_json_success(array(
                'message_id' => $message_id,
                'file_url'   => $uploaded['url'],
                'file_name'  => $uploaded['name'],
                'file_type'  => $uploaded['type'],
                'is_image'   => $is_image,
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save file message.', 'vendor-customshot')));
        }
    }

    /**
     * Get new messages (for polling)
     */
    private function get_new_messages($shot_id, $user_id) {
        $last_id = isset($_POST['last_id']) ? intval($_POST['last_id']) : 0;

        $messages = Customshot_Chat::get_new_messages($shot_id, $last_id);

        // Mark as read
        Customshot_Chat::mark_as_read($shot_id, $user_id);

        // Add sender avatar to messages
        foreach ($messages as &$msg) {
            if ($msg->sender_id) {
                $msg->sender_avatar = get_avatar_url($msg->sender_id, array('size' => 40));
            }
        }

        wp_send_json_success(array(
            'messages' => $messages,
        ));
    }

    /**
     * Upload a deliverable file
     */
    private function upload_deliverable($shot_id, $user_id) {
        // Only vendor can upload deliverables
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        if ($user_id != $vendor_id) {
            wp_send_json_error(array('message' => __('Only the vendor can upload deliverables.', 'vendor-customshot')));
            return;
        }

        if (!isset($_FILES['file'])) {
            wp_send_json_error(array('message' => __('No file uploaded.', 'vendor-customshot')));
            return;
        }

        $file = $_FILES['file'];
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'image';

        // Upload to media library
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('file', $shot_id);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error(array('message' => $attachment_id->get_error_message()));
            return;
        }

        // Add to deliverables meta
        $meta_key = ($type === 'image') ? '_customshot_deliverable_images' : '_customshot_deliverable_videos';
        $deliverables = get_post_meta($shot_id, $meta_key, true);

        if (!is_array($deliverables)) {
            $deliverables = array();
        }

        $deliverables[] = $attachment_id;
        update_post_meta($shot_id, $meta_key, $deliverables);

        // Get URL for preview
        if ($type === 'image') {
            $url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
        } else {
            $url = wp_get_attachment_url($attachment_id);
        }

        wp_send_json_success(array(
            'attachment_id' => $attachment_id,
            'url'           => $url,
            'type'          => $type,
        ));
    }

    /**
     * Remove a deliverable
     */
    private function remove_deliverable($shot_id, $user_id) {
        // Only vendor can remove deliverables
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        if ($user_id != $vendor_id) {
            wp_send_json_error(array('message' => __('Only the vendor can remove deliverables.', 'vendor-customshot')));
            return;
        }

        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;

        if (!$attachment_id) {
            wp_send_json_error(array('message' => __('Invalid attachment.', 'vendor-customshot')));
            return;
        }

        // Remove from both image and video arrays
        foreach (array('_customshot_deliverable_images', '_customshot_deliverable_videos') as $meta_key) {
            $deliverables = get_post_meta($shot_id, $meta_key, true);
            if (is_array($deliverables)) {
                $deliverables = array_diff($deliverables, array($attachment_id));
                update_post_meta($shot_id, $meta_key, array_values($deliverables));
            }
        }

        wp_send_json_success(array('message' => __('Deliverable removed.', 'vendor-customshot')));
    }

    /**
     * Submit deliverables for review
     */
    private function submit_deliverables($shot_id, $user_id) {
        // Only vendor can submit deliverables
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        if ($user_id != $vendor_id) {
            wp_send_json_error(array('message' => __('Only the vendor can submit deliverables.', 'vendor-customshot')));
            return;
        }

        // Check if there are deliverables
        $images = get_post_meta($shot_id, '_customshot_deliverable_images', true);
        $videos = get_post_meta($shot_id, '_customshot_deliverable_videos', true);

        if (empty($images) && empty($videos)) {
            wp_send_json_error(array('message' => __('Please upload at least one deliverable before submitting.', 'vendor-customshot')));
            return;
        }

        // Update status
        update_post_meta($shot_id, '_customshot_deliverable_status', 'pending_review');
        update_post_meta($shot_id, '_customshot_status', 'delivered');

        // Send chat message
        Customshot_Chat::send_message($shot_id, $user_id, 'deliverable', __('Deliverables have been submitted for review.', 'vendor-customshot'));

        // Send email to buyer
        $this->send_deliverable_notification($shot_id);

        wp_send_json_success(array('message' => __('Deliverables submitted for review.', 'vendor-customshot')));
    }

    /**
     * Send notification email for new message
     */
    private function send_notification($shot_id, $sender_id, $message) {
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);

        // Determine recipient
        $recipient_id = ($sender_id == $vendor_id) ? $buyer_id : $vendor_id;
        $recipient = get_user_by('id', $recipient_id);

        if (!$recipient) return;

        $sender = get_user_by('id', $sender_id);
        $shot = get_post($shot_id);

        $subject = sprintf(__('New message from %s - %s', 'vendor-customshot'), $sender->display_name, $shot->post_title);

        $body = sprintf(
            __('You have a new message regarding your custom shot request "%s":', 'vendor-customshot'),
            $shot->post_title
        );
        $body .= "\n\n" . $message;
        $body .= "\n\n" . __('View the conversation in your dashboard.', 'vendor-customshot');

        wp_mail($recipient->user_email, $subject, $body);
    }

    /**
     * Send notification when deliverables are submitted
     */
    private function send_deliverable_notification($shot_id) {
        $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
        $buyer = get_user_by('id', $buyer_id);

        if (!$buyer) return;

        $shot = get_post($shot_id);
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        $vendor = get_user_by('id', $vendor_id);

        $subject = sprintf(__('Deliverables Ready for Review - %s', 'vendor-customshot'), $shot->post_title);

        $body = sprintf(
            __('%s has submitted deliverables for your custom shot request "%s".', 'vendor-customshot'),
            $vendor->display_name,
            $shot->post_title
        );
        $body .= "\n\n" . __('Please review the deliverables and approve or request revisions.', 'vendor-customshot');

        wp_mail($buyer->user_email, $subject, $body);
    }
}
