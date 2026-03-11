<?php
/**
 * Custom Shot Chat Handler
 * Handles chat messaging between vendor and buyer
 */

if (!defined('ABSPATH')) exit;

class Customshot_Chat {

    private static $table_name;

    /**
     * Initialize chat system
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'customshot_messages';
    }

    /**
     * Get table name
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'customshot_messages';
    }

    /**
     * Create database table on plugin activation
     */
    public static function create_table() {
        global $wpdb;

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            shot_id bigint(20) unsigned NOT NULL,
            sender_id bigint(20) unsigned NOT NULL,
            message_type varchar(20) NOT NULL DEFAULT 'text',
            message_content longtext,
            file_url varchar(500),
            file_name varchar(255),
            file_type varchar(100),
            is_deliverable tinyint(1) NOT NULL DEFAULT 0,
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY shot_id (shot_id),
            KEY sender_id (sender_id),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Mark table as created
        update_option('customshot_chat_table_version', '1.0');
    }

    /**
     * Check if chat is active for a custom shot
     */
    public static function is_chat_active($shot_id) {
        $status = get_post_meta($shot_id, '_customshot_status', true);

        // Chat is active when status is: accepted, in_progress, delivered, completed
        $active_statuses = array('accepted', 'in_progress', 'delivered', 'completed');

        return in_array($status, $active_statuses);
    }

    /**
     * Initialize chat for a custom shot (called when accepted)
     */
    public static function initialize_chat($shot_id) {
        // Mark chat as initialized
        update_post_meta($shot_id, '_customshot_chat_initialized', current_time('mysql'));

        // Send system message
        self::send_message($shot_id, 0, 'system', __('Chat has been started. You can now communicate about this custom shot.', 'vendor-customshot'));

        return true;
    }

    /**
     * Send a message
     */
    public static function send_message($shot_id, $sender_id, $type = 'text', $content = '', $file_data = array()) {
        global $wpdb;

        $table_name = self::get_table_name();

        $data = array(
            'shot_id'         => $shot_id,
            'sender_id'       => $sender_id,
            'message_type'    => $type,
            'message_content' => $content,
            'file_url'        => isset($file_data['url']) ? $file_data['url'] : '',
            'file_name'       => isset($file_data['name']) ? $file_data['name'] : '',
            'file_type'       => isset($file_data['type']) ? $file_data['type'] : '',
            'is_deliverable'  => isset($file_data['is_deliverable']) ? 1 : 0,
            'is_read'         => 0,
            'created_at'      => current_time('mysql'),
        );

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Get messages for a custom shot
     */
    public static function get_messages($shot_id, $limit = 50, $offset = 0, $after_id = 0) {
        global $wpdb;

        $table_name = self::get_table_name();

        $where = $wpdb->prepare("shot_id = %d", $shot_id);

        if ($after_id > 0) {
            $where .= $wpdb->prepare(" AND id > %d", $after_id);
        }

        $sql = "SELECT * FROM $table_name WHERE $where ORDER BY created_at ASC";

        if ($limit > 0 && $after_id == 0) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Get latest messages (for polling)
     */
    public static function get_new_messages($shot_id, $last_message_id) {
        global $wpdb;

        $table_name = self::get_table_name();

        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE shot_id = %d AND id > %d ORDER BY created_at ASC",
            $shot_id,
            $last_message_id
        );

        return $wpdb->get_results($sql);
    }

    /**
     * Mark messages as read
     */
    public static function mark_as_read($shot_id, $user_id) {
        global $wpdb;

        $table_name = self::get_table_name();

        // Mark all messages NOT sent by this user as read
        return $wpdb->update(
            $table_name,
            array('is_read' => 1),
            array('shot_id' => $shot_id),
            array('%d'),
            array('%d')
        );
    }

    /**
     * Get unread count for a user
     */
    public static function get_unread_count($shot_id, $user_id) {
        global $wpdb;

        $table_name = self::get_table_name();

        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE shot_id = %d AND sender_id != %d AND is_read = 0",
            $shot_id,
            $user_id
        ));
    }

    /**
     * Get all conversations for a vendor (with last message)
     */
    public static function get_vendor_conversations($vendor_id) {
        global $wpdb;

        $table_name = self::get_table_name();

        // Get all custom shots for this vendor with chat initialized
        $shots = get_posts(array(
            'post_type'      => 'customshot',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_customshot_vendor_id',
                    'value' => $vendor_id,
                ),
                array(
                    'key'     => '_customshot_chat_initialized',
                    'compare' => 'EXISTS',
                ),
            ),
        ));

        $conversations = array();

        foreach ($shots as $shot) {
            // Get last message
            $last_message = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE shot_id = %d ORDER BY created_at DESC LIMIT 1",
                $shot->ID
            ));

            // Get unread count
            $unread_count = self::get_unread_count($shot->ID, $vendor_id);

            // Get buyer info
            $buyer_id = get_post_meta($shot->ID, '_customshot_buyer_id', true);
            $buyer = get_user_by('id', $buyer_id);

            $conversations[] = array(
                'shot_id'       => $shot->ID,
                'shot_title'    => $shot->post_title,
                'buyer_id'      => $buyer_id,
                'buyer_name'    => $buyer ? $buyer->display_name : __('Unknown', 'vendor-customshot'),
                'buyer_avatar'  => get_avatar_url($buyer_id, array('size' => 50)),
                'last_message'  => $last_message,
                'unread_count'  => $unread_count,
                'status'        => get_post_meta($shot->ID, '_customshot_status', true),
            );
        }

        // Sort by last message time
        usort($conversations, function($a, $b) {
            $time_a = $a['last_message'] ? strtotime($a['last_message']->created_at) : 0;
            $time_b = $b['last_message'] ? strtotime($b['last_message']->created_at) : 0;
            return $time_b - $time_a;
        });

        return $conversations;
    }

    /**
     * Get all conversations for a buyer
     */
    public static function get_buyer_conversations($buyer_id) {
        global $wpdb;

        $table_name = self::get_table_name();

        // Get all custom shots for this buyer with chat initialized
        $shots = get_posts(array(
            'post_type'      => 'customshot',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_customshot_buyer_id',
                    'value' => $buyer_id,
                ),
                array(
                    'key'     => '_customshot_chat_initialized',
                    'compare' => 'EXISTS',
                ),
            ),
        ));

        $conversations = array();

        foreach ($shots as $shot) {
            // Get last message
            $last_message = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE shot_id = %d ORDER BY created_at DESC LIMIT 1",
                $shot->ID
            ));

            // Get unread count
            $unread_count = self::get_unread_count($shot->ID, $buyer_id);

            // Get vendor info
            $vendor_id = get_post_meta($shot->ID, '_customshot_vendor_id', true);
            $vendor = get_user_by('id', $vendor_id);
            $store_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);

            $conversations[] = array(
                'shot_id'       => $shot->ID,
                'shot_title'    => $shot->post_title,
                'vendor_id'     => $vendor_id,
                'vendor_name'   => $store_name ? $store_name : ($vendor ? $vendor->display_name : __('Unknown', 'vendor-customshot')),
                'vendor_avatar' => get_avatar_url($vendor_id, array('size' => 50)),
                'last_message'  => $last_message,
                'unread_count'  => $unread_count,
                'status'        => get_post_meta($shot->ID, '_customshot_status', true),
            );
        }

        // Sort by last message time
        usort($conversations, function($a, $b) {
            $time_a = $a['last_message'] ? strtotime($a['last_message']->created_at) : 0;
            $time_b = $b['last_message'] ? strtotime($b['last_message']->created_at) : 0;
            return $time_b - $time_a;
        });

        return $conversations;
    }

    /**
     * Handle file upload
     */
    public static function handle_file_upload($file) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_overrides = array('test_form' => false);
        $uploaded_file = wp_handle_upload($file, $upload_overrides);

        if (isset($uploaded_file['error'])) {
            return new WP_Error('upload_error', $uploaded_file['error']);
        }

        return array(
            'url'  => $uploaded_file['url'],
            'name' => $file['name'],
            'type' => $file['type'],
        );
    }

    /**
     * Format message time
     */
    public static function format_time($datetime) {
        $timestamp = strtotime($datetime);
        $now = current_time('timestamp');
        $diff = $now - $timestamp;

        if ($diff < 60) {
            return __('Just now', 'vendor-customshot');
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return sprintf(_n('%d min ago', '%d mins ago', $mins, 'vendor-customshot'), $mins);
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'vendor-customshot'), $hours);
        } elseif ($diff < 604800) {
            return date_i18n('l g:i A', $timestamp);
        } else {
            return date_i18n('M j, Y g:i A', $timestamp);
        }
    }

    /**
     * Get message preview (truncated)
     */
    public static function get_message_preview($message, $length = 50) {
        if ($message->message_type === 'file') {
            return '📎 ' . $message->file_name;
        } elseif ($message->message_type === 'voice') {
            return '🎤 ' . __('Voice message', 'vendor-customshot');
        } elseif ($message->message_type === 'deliverable') {
            return '📦 ' . __('Deliverable uploaded', 'vendor-customshot');
        } elseif ($message->message_type === 'system') {
            return '🔔 ' . wp_trim_words($message->message_content, 5, '...');
        }

        $content = strip_tags($message->message_content);
        if (strlen($content) > $length) {
            return substr($content, 0, $length) . '...';
        }
        return $content;
    }
}

// Initialize
Customshot_Chat::init();
