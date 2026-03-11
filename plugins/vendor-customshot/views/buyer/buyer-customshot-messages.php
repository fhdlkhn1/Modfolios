<?php
/**
 * Buyer Custom Shot Messages List View
 * Shows all conversations for the buyer (matches vendor inbox design)
 */

if (!defined('ABSPATH')) exit;

$buyer_id = get_current_user_id();

// Get all conversations for this buyer
$conversations = Customshot_Chat::get_buyer_conversations($buyer_id);

$base_url = wc_get_account_endpoint_url('custom-shots-messages');
?>

<div class="buyer-messages-view">
    <h2><?php esc_html_e('Messages / Inquiries', 'vendor-customshot'); ?></h2>

    <div class="messages-container">
        <?php if (empty($conversations)) : ?>
            <div class="messages-empty">
                <div class="empty-icon">
                    <span class="wcfmfa fa-comments"></span>
                </div>
                <h3><?php esc_html_e('No messages yet', 'vendor-customshot'); ?></h3>
                <p><?php esc_html_e('When you accept a quote from a vendor, conversations will appear here.', 'vendor-customshot'); ?></p>
            </div>
        <?php else : ?>
            <div class="messages-list">
                <?php foreach ($conversations as $conv) :
                    $last_msg_time = $conv['last_message'] ? Customshot_Chat::format_time($conv['last_message']->created_at) : '';
                    $last_msg_preview = $conv['last_message'] ? Customshot_Chat::get_message_preview($conv['last_message']) : __('No messages yet', 'vendor-customshot');
                    $is_unread = $conv['unread_count'] > 0;

                    $chat_url = $base_url . '?id=' . $conv['shot_id'];
                ?>
                    <a href="<?php echo esc_url($chat_url); ?>"
                       class="message-row <?php echo $is_unread ? 'unread' : ''; ?>">
                        <div class="message-avatar">
                            <img src="<?php echo esc_url($conv['vendor_avatar']); ?>" alt="<?php echo esc_attr($conv['vendor_name']); ?>">
                            <?php if ($is_unread) : ?>
                                <span class="unread-badge"><?php echo esc_html($conv['unread_count']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-name"><?php echo esc_html($conv['vendor_name']); ?></span>
                                <span class="message-time"><?php echo esc_html($last_msg_time); ?></span>
                            </div>
                            <div class="message-title"><?php echo esc_html($conv['shot_title']); ?></div>
                            <div class="message-preview"><?php echo esc_html($last_msg_preview); ?></div>
                        </div>
                        <div class="message-status">
                            <span class="status-badge status-<?php echo esc_attr($conv['status']); ?>">
                                <?php
                                $status_labels = array(
                                    'accepted'    => __('In Progress', 'vendor-customshot'),
                                    'in_progress' => __('In Progress', 'vendor-customshot'),
                                    'delivered'   => __('Review', 'vendor-customshot'),
                                    'completed'   => __('Completed', 'vendor-customshot'),
                                );
                                echo esc_html($status_labels[$conv['status']] ?? ucfirst(str_replace('_', ' ', $conv['status'])));
                                ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
