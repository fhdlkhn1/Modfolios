<?php
/**
 * WCFM Custom Shot Messages List View
 * Shows all conversations for vendor
 */

if (!defined('ABSPATH')) exit;

global $WCFM, $wpdb;

$vendor_id = get_current_user_id();

/* -------------------------------------------------------------
 *  Check if vendor has Elite membership (Custom Shots is Elite only)
 * ------------------------------------------------------------- */
$elite_membership_id = 21879; // Elite only
$subscription_status = get_user_meta( $vendor_id, 'wcfm_subscription_status', true );
$membership_id = (int) get_user_meta( $vendor_id, 'wcfm_membership', true );
$has_elite = ( $subscription_status === 'active' && $membership_id === $elite_membership_id );

// $has_elite = true;

// If vendor doesn't have Elite membership, show upgrade prompt
if ( ! $has_elite ) :
    // Enqueue the restriction CSS
    wp_enqueue_style( 'wcfm-membership-restriction', plugins_url( 'css/membership-restriction.css', dirname(__FILE__) ), array(), '1.0.0' );
?>
<div class="collapse wcfm-collapse" id="wcfm_customshot_messages">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-comments"></span>
        <span class="wcfm-page-heading-text"><?php esc_html_e('Messages / Inquiries', 'vendor-customshot'); ?></span>
    </div>
    <div class="wcfm-collapse-content">
        <div class="membership-upgrade-box">
            <div class="upgrade-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                </svg>
            </div>
            <h2 class="upgrade-title"><?php _e( 'Unlock Custom Bookings', 'vendor-customshot' ); ?></h2>
            <p class="upgrade-description">
                <?php _e( 'Professional brands want to book you for custom projects! The ability to receive and negotiate custom shoot requests is exclusive to our', 'vendor-customshot' ); ?>
                <span class="highlight"><?php _e( 'Elite members', 'vendor-customshot' ); ?></span>.
            </p>
            <div class="upgrade-benefits">
                <div class="benefit-item">
                    <span class="benefit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    </span>
                    <span class="benefit-text"><?php _e( 'Direct negotiation with luxury brands', 'vendor-customshot' ); ?></span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    </span>
                    <span class="benefit-text"><?php _e( 'Secure escrow payments for large projects', 'vendor-customshot' ); ?></span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    </span>
                    <span class="benefit-text"><?php _e( 'Unlimited Portfolio Uploads', 'vendor-customshot' ); ?></span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    </span>
                    <span class="benefit-text"><?php _e( 'Lowest commission rates (9%)', 'vendor-customshot' ); ?></span>
                </div>
            </div>
            <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="upgrade-btn">
                <?php _e( 'Upgrade to Elite', 'vendor-customshot' ); ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>
                </svg>
            </a>
        </div>
    </div>
</div>
<?php
return;
endif;

/* -------------------------------------------------------------
 *  Vendor has Elite - Show normal content
 * ------------------------------------------------------------- */

$conversations = Customshot_Chat::get_vendor_conversations($vendor_id);

?>

<div class="collapse wcfm-collapse" id="wcfm_customshot_messages">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-comments"></span>
        <span class="wcfm-page-heading-text"><?php esc_html_e('Messages / Inquiries', 'vendor-customshot'); ?></span>
    </div>
    <div class="wcfm-collapse-content">
        <div class="wcfm_clearfix"></div>

        <div class="messages-container">
            <?php if (empty($conversations)) : ?>
                <div class="messages-empty">
                    <div class="empty-icon">
                        <span class="wcfmfa fa-comments"></span>
                    </div>
                    <h3><?php esc_html_e('No messages yet', 'vendor-customshot'); ?></h3>
                    <p><?php esc_html_e('When you accept a custom shot request or a buyer accepts your quote, conversations will appear here.', 'vendor-customshot'); ?></p>
                </div>
            <?php else : ?>
                <div class="messages-list">
                    <?php foreach ($conversations as $conv) :
                        $last_msg_time = $conv['last_message'] ? Customshot_Chat::format_time($conv['last_message']->created_at) : '';
                        $last_msg_preview = $conv['last_message'] ? Customshot_Chat::get_message_preview($conv['last_message']) : __('No messages yet', 'vendor-customshot');
                        $is_unread = $conv['unread_count'] > 0;
                    ?>
                        <a href="<?php echo esc_url(get_wcfm_customshot_messages_url($conv['shot_id'])); ?>"
                           class="message-row <?php echo $is_unread ? 'unread' : ''; ?>">
                            <div class="message-avatar">
                                <img src="<?php echo esc_url($conv['buyer_avatar']); ?>" alt="<?php echo esc_attr($conv['buyer_name']); ?>">
                                <?php if ($is_unread) : ?>
                                    <span class="unread-badge"><?php echo esc_html($conv['unread_count']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="message-name"><?php echo esc_html($conv['buyer_name']); ?></span>
                                    <span class="message-time"><?php echo esc_html($last_msg_time); ?></span>
                                </div>
                                <div class="message-title"><?php echo esc_html($conv['shot_title']); ?></div>
                                <div class="message-preview"><?php echo esc_html($last_msg_preview); ?></div>
                            </div>
                            <div class="message-status">
                                <span class="status-badge status-<?php echo esc_attr($conv['status']); ?>">
                                    <?php echo esc_html(ucfirst($conv['status'])); ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>