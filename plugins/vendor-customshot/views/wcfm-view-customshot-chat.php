<?php
/**
 * WCFM Custom Shot Chat View
 * Individual chat conversation with deliverables panel
 */

if (!defined('ABSPATH')) exit;

global $WCFM, $wpdb;

$vendor_id = get_current_user_id();
$shot_id = isset($_GET['shot_id']) ? intval($_GET['shot_id']) : 0;

// Verify this shot belongs to this vendor
$shot_vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
if ($shot_vendor_id != $vendor_id) {
    echo '<div class="wcfm-container"><p>' . esc_html__('You do not have permission to view this conversation.', 'vendor-customshot') . '</p></div>';
    return;
}

// Get shot details
$shot = get_post($shot_id);
if (!$shot) {
    echo '<div class="wcfm-container"><p>' . esc_html__('Custom shot not found.', 'vendor-customshot') . '</p></div>';
    return;
}

// Get buyer info
$buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
$buyer = get_user_by('id', $buyer_id);
$buyer_name = $buyer ? $buyer->display_name : __('Unknown', 'vendor-customshot');
$buyer_avatar = get_avatar_url($buyer_id, array('size' => 50));

// Get status
$status = get_post_meta($shot_id, '_customshot_status', true);

// Get messages
$messages = Customshot_Chat::get_messages($shot_id, 100);

// Mark messages as read
Customshot_Chat::mark_as_read($shot_id, $vendor_id);

// Get deliverables
$deliverable_images = get_post_meta($shot_id, '_customshot_deliverable_images', true);
$deliverable_videos = get_post_meta($shot_id, '_customshot_deliverable_videos', true);
$deliverable_status = get_post_meta($shot_id, '_customshot_deliverable_status', true);

if (!is_array($deliverable_images)) $deliverable_images = array();
if (!is_array($deliverable_videos)) $deliverable_videos = array();

?>

<div class="collapse wcfm-collapse" id="wcfm_customshot_chat">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-comments"></span>
        <span class="wcfm-page-heading-text"><?php esc_html_e('Messages', 'vendor-customshot'); ?></span>
    </div>
    <div class="wcfm-collapse-content">
        <div class="wcfm_clearfix"></div>

        <!-- Render Modfolio Welcomebox -->
        <?php //modfolio_wcfm_render_header( '' );?>

        <!-- Breadcrumb -->
        <div class="chat-breadcrumb">
            <a href="<?php echo esc_url(get_wcfm_customshot_messages_url()); ?>">
                <span class="wcfmfa fa-arrow-left"></span>
                <?php esc_html_e('Back to Messages', 'vendor-customshot'); ?>
            </a>
        </div>

        <div class="chat-container" data-shot-id="<?php echo esc_attr($shot_id); ?>">
            <!-- Left: Conversations List (mini) -->
            <div class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h4><?php esc_html_e('Conversations', 'vendor-customshot'); ?></h4>
                </div>
                <div class="chat-sidebar-list">
                    <?php
                    $conversations = Customshot_Chat::get_vendor_conversations($vendor_id);
                    foreach ($conversations as $conv) :
                        $is_active = $conv['shot_id'] == $shot_id;
                        $is_unread = $conv['unread_count'] > 0 && !$is_active;
                    ?>
                        <a href="<?php echo esc_url(get_wcfm_customshot_messages_url($conv['shot_id'])); ?>"
                           class="sidebar-conversation <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_unread ? 'unread' : ''; ?>">
                            <img src="<?php echo esc_url($conv['buyer_avatar']); ?>" alt="" class="conv-avatar">
                            <div class="conv-info">
                                <span class="conv-name"><?php echo esc_html($conv['buyer_name']); ?></span>
                                <?php if ($is_unread) : ?>
                                    <span class="conv-badge"><?php echo esc_html($conv['unread_count']); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Middle: Chat Window -->
            <div class="chat-main">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-header-user">
                        <img src="<?php echo esc_url($buyer_avatar); ?>" alt="" class="chat-avatar">
                        <div class="chat-user-info">
                            <span class="chat-user-name"><?php echo esc_html($buyer_name); ?></span>
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
                            $is_mine = $msg->sender_id == $vendor_id;
                            $is_system = $msg->message_type === 'system';
                            $sender = get_user_by('id', $msg->sender_id);
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
                                                        <img src="<?php echo esc_url($msg->file_url); ?>" alt="<?php echo esc_attr($msg->file_name); ?>" class="message-image">
                                                    </a>
                                                <?php else : ?>
                                                    <a href="<?php echo esc_url($msg->file_url); ?>" target="_blank" class="file-link">
                                                        <span class="wcfmfa fa-file"></span>
                                                        <?php echo esc_html($msg->file_name); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif ($msg->message_type === 'voice') : ?>
                                            <div class="message-voice">
                                                <audio controls>
                                                    <source src="<?php echo esc_url($msg->file_url); ?>" type="audio/webm">
                                                    <?php esc_html_e('Your browser does not support audio.', 'vendor-customshot'); ?>
                                                </audio>
                                            </div>
                                        <?php elseif ($msg->message_type === 'deliverable') : ?>
                                            <div class="message-deliverable">
                                                <span class="wcfmfa fa-box"></span>
                                                <span><?php esc_html_e('Deliverable uploaded', 'vendor-customshot'); ?></span>
                                                <?php if ($msg->file_url) : ?>
                                                    <a href="<?php echo esc_url($msg->file_url); ?>" target="_blank">
                                                        <?php esc_html_e('View', 'vendor-customshot'); ?>
                                                    </a>
                                                <?php endif; ?>
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

            <!-- Right: Deliverables Panel -->
            <div class="chat-deliverables vendor-deliverables">
                <div class="deliverables-header">
                    <h4><?php esc_html_e('Deliverables', 'vendor-customshot'); ?></h4>
                </div>

                <?php
                // Get individual approval status
                $approved_items = get_post_meta($shot_id, '_customshot_approved_items', true);
                if (!is_array($approved_items)) $approved_items = array();
                $total_items = count($deliverable_images) + count($deliverable_videos);
                $approved_count = count($approved_items);
                ?>

                <div class="deliverables-content">
                    <!-- Approval Progress (if items exist) -->
                    <?php if ($total_items > 0 && $deliverable_status) : ?>
                        <div class="approval-progress-section">
                            <div class="progress-info">
                                <span class="progress-label"><?php esc_html_e('Buyer Approval', 'vendor-customshot'); ?></span>
                                <span class="progress-count"><?php printf('%d/%d', $approved_count, $total_items); ?></span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $total_items > 0 ? ($approved_count / $total_items * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Hidden file inputs (outside click areas to prevent event conflicts) -->
                    <input type="file" id="deliverable-images" multiple accept="image/*" style="display: none;">
                    <input type="file" id="deliverable-videos" multiple accept="video/*" style="display: none;">

                    <!-- Images Section -->
                    <div class="upload-section">
                        <label><?php esc_html_e('Images', 'vendor-customshot'); ?></label>
                        <div class="upload-area" id="upload-images">
                            <?php if (empty($deliverable_images)) : ?>
                                <div class="upload-placeholder" id="images-placeholder">
                                    <span class="wcfmfa fa-cloud-upload-alt"></span>
                                    <span><?php esc_html_e('Drop images here or click to upload', 'vendor-customshot'); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="upload-preview deliverable-grid-new" id="images-preview">
                                <?php foreach ($deliverable_images as $attachment_id) :
                                    $image_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
                                    $is_approved = in_array($attachment_id, $approved_items);
                                    if ($image_url) :
                                ?>
                                    <div class="preview-item <?php echo $is_approved ? 'is-approved' : ''; ?>" data-id="<?php echo esc_attr($attachment_id); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="">
                                        <?php if ($is_approved) : ?>
                                            <span class="approved-badge" title="<?php esc_attr_e('Buyer Approved', 'vendor-customshot'); ?>">&#10004;</span>
                                        <?php endif; ?>
                                        <?php if ($deliverable_status !== 'approved') : ?>
                                            <span type="button" class="remove-preview" data-id="<?php echo esc_attr($attachment_id); ?>">&times;</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                        <?php if (!empty($deliverable_images) && $deliverable_status !== 'approved') : ?>
                            <button type="button" class="btn-add-more" id="btn-add-images">
                                <span class="wcfmfa fa-plus"></span> <?php esc_html_e('Add More', 'vendor-customshot'); ?>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Videos Section -->
                    <div class="upload-section">
                        <label><?php esc_html_e('Videos', 'vendor-customshot'); ?></label>
                        <div class="upload-area" id="upload-videos">
                            <?php if (empty($deliverable_videos)) : ?>
                                <div class="upload-placeholder" id="videos-placeholder">
                                    <span class="wcfmfa fa-video"></span>
                                    <span><?php esc_html_e('Drop videos here or click to upload', 'vendor-customshot'); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="upload-preview deliverable-grid-new" id="videos-preview">
                                <?php foreach ($deliverable_videos as $attachment_id) :
                                    $video_url = wp_get_attachment_url($attachment_id);
                                    $is_approved = in_array($attachment_id, $approved_items);
                                    if ($video_url) :
                                ?>
                                    <div class="preview-item video-item <?php echo $is_approved ? 'is-approved' : ''; ?>" data-id="<?php echo esc_attr($attachment_id); ?>">
                                        <span class="wcfmfa fa-film"></span>
                                        <?php if ($is_approved) : ?>
                                            <span class="approved-badge" title="<?php esc_attr_e('Buyer Approved', 'vendor-customshot'); ?>">&#10004;</span>
                                        <?php endif; ?>
                                        <?php if ($deliverable_status !== 'approved') : ?>
                                            <span type="button" class="remove-preview" data-id="<?php echo esc_attr($attachment_id); ?>">&times;</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                        <?php if (!empty($deliverable_videos) && $deliverable_status !== 'approved') : ?>
                            <button type="button" class="btn-add-more" id="btn-add-videos">
                                <span class="wcfmfa fa-plus"></span> <?php esc_html_e('Add More', 'vendor-customshot'); ?>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Deliverable Status -->
                    <?php if ($deliverable_status) : ?>
                        <div class="deliverable-status-section">
                            <label><?php esc_html_e('Status', 'vendor-customshot'); ?></label>
                            <div class="deliverable-status status-<?php echo esc_attr($deliverable_status); ?>">
                                <?php
                                $status_labels = array(
                                    'pending_review'     => __('Pending Review', 'vendor-customshot'),
                                    'approved'           => __('All Approved', 'vendor-customshot'),
                                    'revision_requested' => __('Revision Requested', 'vendor-customshot'),
                                );
                                echo esc_html($status_labels[$deliverable_status] ?? ucfirst($deliverable_status));
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Submit Button -->
                    <div class="deliverables-actions">
                        <?php if ($deliverable_status !== 'approved') : ?>
                            <button type="button" class="btn-submit-deliverables" id="btn-submit-deliverables">
                                <?php
                                if ($deliverable_status === 'pending_review') {
                                    esc_html_e('Update Deliverables', 'vendor-customshot');
                                } elseif ($deliverable_status === 'revision_requested') {
                                    esc_html_e('Submit Revision', 'vendor-customshot');
                                } else {
                                    esc_html_e('Submit for Review', 'vendor-customshot');
                                }
                                ?>
                            </button>
                        <?php else : ?>
                            <div class="all-approved-message">
                                <span class="wcfmfa fa-check-circle"></span>
                                <?php esc_html_e('All deliverables approved by buyer!', 'vendor-customshot'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>