<?php
/**
 * Buyer Custom Shot Chat View
 * Chat interface for buyer to communicate with vendor
 */

if (!defined('ABSPATH')) exit;

$buyer_id = get_current_user_id();

// Verify this shot belongs to this buyer
$shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
if ($shot_buyer_id != $buyer_id) {
    echo '<p>' . esc_html__('You do not have permission to view this conversation.', 'vendor-customshot') . '</p>';
    return;
}

$shot = get_post($shot_id);
if (!$shot) {
    echo '<p>' . esc_html__('Custom shot not found.', 'vendor-customshot') . '</p>';
    return;
}

// Get vendor info
$vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
$vendor = get_user_by('id', $vendor_id);
$store_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
$vendor_name = $store_name ? $store_name : ($vendor ? $vendor->display_name : __('Unknown', 'vendor-customshot'));
$vendor_avatar = get_avatar_url($vendor_id, array('size' => 50));

$status = get_post_meta($shot_id, '_customshot_status', true);

// Get messages
$messages = Customshot_Chat::get_messages($shot_id, 100);

// Mark messages as read
Customshot_Chat::mark_as_read($shot_id, $buyer_id);

// Get deliverables
$deliverable_images = get_post_meta($shot_id, '_customshot_deliverable_images', true);
$deliverable_videos = get_post_meta($shot_id, '_customshot_deliverable_videos', true);
$deliverable_status = get_post_meta($shot_id, '_customshot_deliverable_status', true);

if (!is_array($deliverable_images)) $deliverable_images = array();
if (!is_array($deliverable_videos)) $deliverable_videos = array();

$base_url = wc_get_account_endpoint_url('custom-shots');
$messages_url = wc_get_account_endpoint_url('custom-shots-messages');

// Get all buyer conversations for sidebar
$all_conversations = Customshot_Chat::get_buyer_conversations($buyer_id);

// Determine back link based on referrer
$back_url = $messages_url;
$back_text = __('Back to Messages', 'vendor-customshot');
?>

<div class="buyer-chat-view">
    <!-- Back Link -->
    <a href="<?php echo esc_url($back_url); ?>" class="back-link">
        &larr; <?php echo esc_html($back_text); ?>
    </a>

    <div class="chat-wrapper">
        <!-- Conversations Sidebar -->
        <div class="conversations-sidebar">
            <div class="sidebar-header">
                <h4><?php esc_html_e('Conversations', 'vendor-customshot'); ?></h4>
            </div>
            <div class="conversations-list">
                <?php if (empty($all_conversations)) : ?>
                    <div class="conversations-empty">
                        <p><?php esc_html_e('No conversations yet.', 'vendor-customshot'); ?></p>
                    </div>
                <?php else : ?>
                    <?php foreach ($all_conversations as $conv) :
                        $conv_vendor_id = $conv['vendor_id'];
                        $conv_vendor_avatar = get_avatar_url($conv_vendor_id, array('size' => 40));
                        $is_active = ($conv['shot_id'] == $shot_id);
                        $is_unread = $conv['unread_count'] > 0;
                        $chat_url = $base_url . '?chat=1&id=' . $conv['shot_id'];
                    ?>
                        <a href="<?php echo esc_url($chat_url); ?>" class="conversation-item <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_unread ? 'unread' : ''; ?>">
                            <div class="conv-avatar">
                                <img src="<?php echo esc_url($conv_vendor_avatar); ?>" alt="">
                                <?php if ($is_unread) : ?>
                                    <span class="unread-dot"></span>
                                <?php endif; ?>
                            </div>
                            <div class="conv-info">
                                <span class="conv-name"><?php echo esc_html($conv['vendor_name']); ?></span>
                                <span class="conv-project"><?php echo esc_html($conv['shot_title']); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-container" data-shot-id="<?php echo esc_attr($shot_id); ?>">
            <!-- Chat Main -->
            <div class="chat-main">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-header-user">
                    <img src="<?php echo esc_url($vendor_avatar); ?>" alt="" class="chat-avatar">
                    <div class="chat-user-info">
                        <span class="chat-user-name"><?php echo esc_html($vendor_name); ?></span>
                        <span class="chat-project-title"><?php echo esc_html($shot->post_title); ?></span>
                    </div>
                </div>
                <div class="chat-header-status">
                    <span class="status-badge status-<?php echo esc_attr($status); ?>">
                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $status))); ?>
                    </span>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="chat-messages" id="chat-messages">
                <?php if (empty($messages)) : ?>
                    <div class="chat-empty">
                        <p><?php esc_html_e('No messages yet. Start the conversation!', 'vendor-customshot'); ?></p>
                    </div>
                <?php else : ?>
                    <?php foreach ($messages as $msg) :
                        $is_mine = $msg->sender_id == $buyer_id;
                        $is_system = $msg->message_type === 'system';
                        $sender_avatar = $msg->sender_id ? get_avatar_url($msg->sender_id, array('size' => 40)) : '';
                    ?>
                        <?php if ($is_system) : ?>
                            <div class="message-system">
                                <span><?php echo esc_html($msg->message_content); ?></span>
                            </div>
                        <?php else : ?>
                            <div class="message-bubble <?php echo $is_mine ? 'sent' : 'received'; ?>" data-message-id="<?php echo esc_attr($msg->id); ?>">
                                <?php if (!$is_mine) : ?>
                                    <img src="<?php echo esc_url($sender_avatar); ?>" alt="" class="message-avatar">
                                <?php endif; ?>
                                <div class="message-content">
                                    <?php if ($msg->message_type === 'text') : ?>
                                        <div class="message-text"><?php echo nl2br(esc_html($msg->message_content)); ?></div>
                                    <?php elseif ($msg->message_type === 'file') : ?>
                                        <div class="message-file">
                                            <?php if (strpos($msg->file_type, 'image') !== false) : ?>
                                                <a href="<?php echo esc_url($msg->file_url); ?>" target="_blank">
                                                    <img src="<?php echo esc_url($msg->file_url); ?>" alt="" class="message-image">
                                                </a>
                                            <?php else : ?>
                                                <a href="<?php echo esc_url($msg->file_url); ?>" target="_blank" class="file-link">
                                                    <span class="wcfmfa fa-file"></span>
                                                    <?php echo esc_html($msg->file_name); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($msg->message_type === 'deliverable') : ?>
                                        <div class="message-deliverable">
                                            <span>&#128230;</span>
                                            <span><?php echo esc_html($msg->message_content); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="message-time"><?php echo esc_html(Customshot_Chat::format_time($msg->created_at)); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Message Input -->
            <div class="chat-input-area">
                <form id="chat-form" class="chat-form">
                    <input type="hidden" name="shot_id" value="<?php echo esc_attr($shot_id); ?>">
                    <div class="chat-input-wrapper">
                        <span type="button" class="chat-btn-attach" id="btn-attach" title="<?php esc_attr_e('Attach file', 'vendor-customshot'); ?>">
                            <span class="wcfmfa fa-paperclip"></span>
                        </span>
                        <input type="file" id="file-input" style="display: none;" accept="image/*,video/*,.pdf,.doc,.docx,.zip">
                        <textarea name="message" id="chat-input" placeholder="<?php esc_attr_e('Type a message...', 'vendor-customshot'); ?>" rows="1"></textarea>
                        <span type="submit" class="chat-btn-send" id="btn-send">
                            <span class="wcfmfa fa-paper-plane"></span>
                        </span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deliverables Panel (Buyer can approve individual items) -->
        <div class="chat-deliverables buyer-deliverables">
            <?php
            // Get individual approval status for each deliverable
            $approved_items = get_post_meta($shot_id, '_customshot_approved_items', true);
            if (!is_array($approved_items)) $approved_items = array();
            $total_items = count($deliverable_images) + count($deliverable_videos);
            $approved_count = count($approved_items);
            $submission_date = get_post_meta($shot_id, '_customshot_deliverable_submitted_date', true);
            ?>

            <!-- Header with status badge -->
            <div class="deliverables-header-bar" style="<?php echo $submission_date ? '' : 'border-bottom: 0px !important;' ?>">
                <div class="header-left">
                    <span class="submitted-date"><?php echo $submission_date ? sprintf(__('Submitted %s', 'vendor-customshot'), date_i18n('M d, Y', strtotime($submission_date))) : ''; ?></span>
                </div>
                <div class="header-right">
                    <?php if ($deliverable_status === 'pending_review') : ?>
                        <span class="status-pill status-pending"><?php esc_html_e('Pending Review', 'vendor-customshot'); ?></span>
                    <?php elseif ($deliverable_status === 'approved') : ?>
                        <span class="status-pill status-approved"><?php esc_html_e('Approved', 'vendor-customshot'); ?></span>
                    <?php elseif ($deliverable_status === 'revision_requested') : ?>
                        <span class="status-pill status-revision"><?php esc_html_e('Revision Requested', 'vendor-customshot'); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="deliverables-content">
                <?php if (empty($deliverable_images) && empty($deliverable_videos)) : ?>
                    <div class="deliverables-empty">
                        <div class="empty-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                        <p><?php esc_html_e('No deliverables uploaded yet.', 'vendor-customshot'); ?></p>
                        <p class="muted"><?php esc_html_e('The vendor will upload files here when ready.', 'vendor-customshot'); ?></p>
                    </div>
                <?php else : ?>
                    <!-- Section Title -->
                    <div class="deliverables-title-row">
                        <h4><?php esc_html_e('Deliverables', 'vendor-customshot'); ?></h4>
                        <span class="item-count">#<?php echo $approved_count; ?> of <?php echo str_pad($total_items, 2, '0', STR_PAD_LEFT); ?></span>
                    </div>

                    <!-- Deliverables Grid -->
                    <div class="deliverables-grid">
                        <?php
                        $item_index = 0;
                        // Images
                        foreach ($deliverable_images as $attachment_id) :
                            $item_index++;
                            $thumb_url = wp_get_attachment_image_url($attachment_id, 'medium');
                            $full_url = wp_get_attachment_url($attachment_id);
                            $is_approved = in_array($attachment_id, $approved_items);
                            if ($thumb_url) :
                        ?>
                            <div class="deliverable-item <?php echo $is_approved ? 'is-approved' : ''; ?>" data-attachment-id="<?php echo esc_attr($attachment_id); ?>" data-type="image">
                                <div class="item-thumb">
                                    <a href="<?php echo esc_url($full_url); ?>" target="_blank">
                                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo sprintf(__('Image %d', 'vendor-customshot'), $item_index); ?>">
                                    </a>
                                    <?php if ($is_approved) : ?>
                                        <span class="approved-check">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($deliverable_status === 'pending_review' && !$is_approved) : ?>
                                    <button type="button" class="btn-approve-item" data-shot-id="<?php echo esc_attr($shot_id); ?>" data-attachment-id="<?php echo esc_attr($attachment_id); ?>">
                                        <?php esc_html_e('Approve', 'vendor-customshot'); ?>
                                    </button>
                                <?php elseif ($is_approved) : ?>
                                    <span class="item-approved-label"><?php esc_html_e('Approved', 'vendor-customshot'); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; endforeach; ?>

                        <?php
                        // Videos
                        foreach ($deliverable_videos as $attachment_id) :
                            $item_index++;
                            $video_url = wp_get_attachment_url($attachment_id);
                            $is_approved = in_array($attachment_id, $approved_items);
                            if ($video_url) :
                        ?>
                            <div class="deliverable-item video-item <?php echo $is_approved ? 'is-approved' : ''; ?>" data-attachment-id="<?php echo esc_attr($attachment_id); ?>" data-type="video">
                                <div class="item-thumb">
                                    <video>
                                        <source src="<?php echo esc_url($video_url); ?>">
                                    </video>
                                    <span class="play-overlay">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#fff">
                                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                        </svg>
                                    </span>
                                    <?php if ($is_approved) : ?>
                                        <span class="approved-check">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($deliverable_status === 'pending_review' && !$is_approved) : ?>
                                    <button type="button" class="btn-approve-item" data-shot-id="<?php echo esc_attr($shot_id); ?>" data-attachment-id="<?php echo esc_attr($attachment_id); ?>">
                                        <?php esc_html_e('Approve', 'vendor-customshot'); ?>
                                    </button>
                                <?php elseif ($is_approved) : ?>
                                    <span class="item-approved-label"><?php esc_html_e('Approved', 'vendor-customshot'); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>

                    <!-- Download Samples Link -->
                    <div class="download-samples-row">
                        <a href="#" class="download-samples-link" id="download-all-deliverables" data-shot-id="<?php echo esc_attr($shot_id); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            <?php esc_html_e('Download Samples', 'vendor-customshot'); ?>
                        </a>
                    </div>

                    <!-- Feedback Section -->
                    <?php if ($deliverable_status === 'pending_review') : ?>
                        <div class="feedback-section">
                            <label><?php esc_html_e('Feedback', 'vendor-customshot'); ?></label>
                            <div class="feedback-input-wrap">
                                <span class="feedback-divider"></span>
                                <input type="text" id="deliverable-feedback" placeholder="<?php esc_attr_e('Great Lighting-Please Edit tone in #3', 'vendor-customshot'); ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Bottom Action Buttons -->
                    <?php if ($deliverable_status === 'pending_review') : ?>
                        <div class="deliverable-actions-bottom">
                            <button type="button" class="btn-approve-all-green" data-shot-id="<?php echo esc_attr($shot_id); ?>">
                                <?php esc_html_e('Approve', 'vendor-customshot'); ?>
                            </button>
                            <button type="button" class="btn-request-revision-outline" data-bs-toggle="modal" data-bs-target="#revisionModal" data-shot-id="<?php echo esc_attr($shot_id); ?>">
                                <?php esc_html_e('Request Revision', 'vendor-customshot'); ?>
                            </button>
                        </div>
                    <?php elseif ($deliverable_status === 'approved') : ?>
                        <div class="deliverable-approved-banner">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <?php esc_html_e('All Deliverables Approved', 'vendor-customshot'); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        </div><!-- /chat-container -->
    </div><!-- /chat-wrapper -->
</div>

<!-- Bootstrap 5 Request Revision Modal -->
<div class="modal fade" id="revisionModal" tabindex="-1" aria-labelledby="revisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body revision-modal-body">
                <!-- Modal Header Icon -->
                <div class="revision-modal-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </div>

                <h5 class="revision-modal-title"><?php esc_html_e('Request Revision', 'vendor-customshot'); ?></h5>
                <p class="revision-modal-subtitle"><?php esc_html_e('Files Needing Revision', 'vendor-customshot'); ?></p>

                <!-- Deliverables Selection Grid -->
                <div class="revision-items-grid">
                    <?php
                    $item_index = 0;
                    // Images for revision selection
                    foreach ($deliverable_images as $attachment_id) :
                        $item_index++;
                        $thumb_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
                        $is_approved = in_array($attachment_id, $approved_items);
                        if ($thumb_url) :
                    ?>
                        <div class="revision-item">
                            <div class="revision-item-thumb">
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                            </div>
                            <div class="revision-item-check">
                                <input type="checkbox" class="revision-checkbox" id="revision-img-<?php echo esc_attr($attachment_id); ?>"
                                       data-attachment-id="<?php echo esc_attr($attachment_id); ?>" <?php echo $is_approved ? 'disabled' : ''; ?>>
                                <label for="revision-img-<?php echo esc_attr($attachment_id); ?>"><?php echo sprintf(__('Image %d', 'vendor-customshot'), $item_index); ?></label>
                            </div>
                        </div>
                    <?php endif; endforeach; ?>

                    <?php
                    // Videos for revision selection
                    foreach ($deliverable_videos as $attachment_id) :
                        $item_index++;
                        $is_approved = in_array($attachment_id, $approved_items);
                    ?>
                        <div class="revision-item">
                            <div class="revision-item-thumb video-thumb-small">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#666">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </div>
                            <div class="revision-item-check">
                                <input type="checkbox" class="revision-checkbox" id="revision-vid-<?php echo esc_attr($attachment_id); ?>"
                                       data-attachment-id="<?php echo esc_attr($attachment_id); ?>" <?php echo $is_approved ? 'disabled' : ''; ?>>
                                <label for="revision-vid-<?php echo esc_attr($attachment_id); ?>"><?php echo sprintf(__('Video %d', 'vendor-customshot'), $item_index - count($deliverable_images)); ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Revision Notes -->
                <div class="revision-notes-section">
                    <label for="revision-notes"><?php esc_html_e('Revision Notes', 'vendor-customshot'); ?> <span class="required">*</span></label>
                    <textarea id="revision-notes" class="form-control" rows="3" placeholder="<?php esc_attr_e('Please Adjust The Light', 'vendor-customshot'); ?>"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="button" class="btn-send-revision" id="submit-revision-request" data-shot-id="<?php echo esc_attr($shot_id); ?>">
                    <?php esc_html_e('Send Revision Request', 'vendor-customshot'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
