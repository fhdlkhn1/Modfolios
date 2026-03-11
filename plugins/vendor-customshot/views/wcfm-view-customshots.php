<?php
/**
 * WCFM Custom Shots View
 * Displays the list of custom shot requests for the vendor
 */

if (!defined('ABSPATH')) exit;

global $WCFM, $wpdb;

// Get current vendor ID
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



<div class="collapse wcfm-collapse" id="wcfm_customshots_listing">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-camera"></span>
        <span class="wcfm-page-heading-text"><?php _e('Custom Shots', 'vendor-customshot'); ?></span>
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

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'new';

// Define status mapping for tabs
$tab_statuses = array(
    'all'      => array('pending', 'quoted', 'accepted', 'in_progress', 'delivered', 'completed', 'rejected', 'declined'),
    'new requests'      => array('pending'),
    'active'   => array('quoted', 'accepted', 'in_progress', 'delivered'),
    'completed'=> array('completed'),
    'declined' => array('rejected', 'declined'),
);

$statuses = isset($tab_statuses[$current_tab]) ? $tab_statuses[$current_tab] : array('pending');

// Get custom shots for this vendor
$custom_shots = get_posts(array(
    'post_type'      => 'customshot',
    'posts_per_page' => -1,
    'post_status'    => array('pending', 'publish', 'draft'),
    'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => '_customshot_vendor_id',
            'value'   => $vendor_id,
            'compare' => '='
        ),
        array(
            'key'     => '_customshot_status',
            'value'   => $statuses,
            'compare' => 'IN'
        )
    ),
    'orderby'        => 'date',
    'order'          => 'DESC'
));

// Count for each tab
$counts = array(
    'all'      => 0,
    'new'      => 0,
    'active'   => 0,
    'completed'=> 0,
    'declined' => 0,
);

foreach ($tab_statuses as $tab => $tab_status_arr) {
    $count_query = get_posts(array(
        'post_type'      => 'customshot',
        'posts_per_page' => -1,
        'post_status'    => array('pending', 'publish', 'draft'),
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => '_customshot_vendor_id',
                'value'   => $vendor_id,
                'compare' => '='
            ),
            array(
                'key'     => '_customshot_status',
                'value'   => $tab_status_arr,
                'compare' => 'IN'
            )
        ),
        'fields' => 'ids'
    ));
    $counts[$tab] = count($count_query);
}

$base_url = get_wcfm_customshots_url();
?>

<div class="collapse wcfm-collapse" id="wcfm_customshots_listing">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-camera"></span>
        <span class="wcfm-page-heading-text"><?php _e('Custom Shots', 'vendor-customshot'); ?></span>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <!-- Tabs -->
        <div class="customshots-tabs">
            <a href="<?php echo esc_url(add_query_arg('tab', 'all', $base_url)); ?>"
               class="customshots-tab <?php echo $current_tab === 'all' ? 'active' : ''; ?>">
                <?php _e('All', 'vendor-customshot'); ?> (<?php echo $counts['all']; ?>)
            </a>
            <a href="<?php echo esc_url(add_query_arg('tab', 'new', $base_url)); ?>"
               class="customshots-tab <?php echo $current_tab === 'new' ? 'active' : ''; ?>">
                <?php _e('New', 'vendor-customshot'); ?> (<?php echo $counts['new']; ?>)
            </a>
            <a href="<?php echo esc_url(add_query_arg('tab', 'active', $base_url)); ?>"
               class="customshots-tab <?php echo $current_tab === 'active' ? 'active' : ''; ?>">
                <?php _e('Active', 'vendor-customshot'); ?> (<?php echo $counts['active']; ?>)
            </a>
            <a href="<?php echo esc_url(add_query_arg('tab', 'completed', $base_url)); ?>"
               class="customshots-tab <?php echo $current_tab === 'completed' ? 'active' : ''; ?>">
                <?php _e('Completed', 'vendor-customshot'); ?> (<?php echo $counts['completed']; ?>)
            </a>
            <a href="<?php echo esc_url(add_query_arg('tab', 'declined', $base_url)); ?>"
               class="customshots-tab <?php echo $current_tab === 'declined' ? 'active' : ''; ?>">
                <?php _e('Declined', 'vendor-customshot'); ?> (<?php echo $counts['declined']; ?>)
            </a>
        </div>

        <!-- Requests List -->
        <div class="customshots-list">
            <?php if (empty($custom_shots)) : ?>
                <div class="customshots-empty">
                    <p><?php _e('No custom shot requests found.', 'vendor-customshot'); ?></p>
                </div>
            <?php else : ?>
                <?php foreach ($custom_shots as $shot) :
                    $buyer_id = get_post_meta($shot->ID, '_customshot_buyer_id', true);
                    $buyer = get_user_by('id', $buyer_id);
                    $buyer_name = $buyer ? $buyer->display_name : __('Unknown', 'vendor-customshot');
                    $buyer_avatar = get_avatar_url($buyer_id, array('size' => 40));

                    $shoot_type_terms = wp_get_post_terms($shot->ID, 'shoot_type');
                    $shoot_type = !empty($shoot_type_terms) ? $shoot_type_terms[0]->name : '-';

                    $usage_type_terms = wp_get_post_terms($shot->ID, 'usage_type');
                    $usage_type = !empty($usage_type_terms) ? $usage_type_terms[0]->name : '-';

                    $budget = get_post_meta($shot->ID, '_customshot_budget', true);
                    $shoot_date = get_post_meta($shot->ID, '_customshot_shoot_date', true);
                    $status = get_post_meta($shot->ID, '_customshot_status', true);
                    $received_date = $shot->post_date;

                    $status_class = 'status-' . $status;
                    $status_labels = array(
                        'pending'     => __('Pending', 'vendor-customshot'),
                        'quoted'      => __('Quoted', 'vendor-customshot'),
                        'accepted'    => __('Accepted', 'vendor-customshot'),
                        'in_progress' => __('In Progress', 'vendor-customshot'),
                        'delivered'   => __('Delivered', 'vendor-customshot'),
                        'rejected'    => __('Declined', 'vendor-customshot'),
                        'declined'    => __('Declined', 'vendor-customshot'),
                        'completed'   => __('Completed', 'vendor-customshot'),
                    );
                    $status_label = isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);

                    $detail_url = add_query_arg(array('tab' => 'view', 'request_id' => $shot->ID), $base_url);
                ?>
                    <div class="customshot-row" data-shot-id="<?php echo esc_attr($shot->ID); ?>">
                        <!-- Buyer Info -->
                        <div class="customshot-col customshot-col-buyer">
                            <img src="<?php echo esc_url($buyer_avatar); ?>" alt="<?php echo esc_attr($buyer_name); ?>" class="buyer-avatar">
                            <div class="buyer-info">
                                <span class="buyer-name"><?php echo esc_html($buyer_name); ?></span>
                                <span class="buyer-type"><?php echo esc_html($usage_type); ?></span>
                                <span class="buyer-category"><?php echo esc_html($shoot_type); ?></span>
                            </div>
                        </div>

                        <!-- Budget -->
                        <div class="customshot-col customshot-col-budget">
                            <span class="budget-label"><?php _e('Budget:', 'vendor-customshot'); ?></span>
                            <span class="budget-amount">$<?php echo number_format((float)$budget, 0); ?></span>
                        </div>

                        <!-- Request Info -->
                        <div class="customshot-col customshot-col-info">
                            <div class="info-row">
                                <span class="info-label"><?php _e('Request ID:', 'vendor-customshot'); ?></span>
                                <span class="info-value"><?php echo esc_html($shot->ID); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?php _e('Received Date:', 'vendor-customshot'); ?></span>
                                <span class="info-value"><?php echo date_i18n('M d, Y', strtotime($received_date)); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?php _e('Delivery Date:', 'vendor-customshot'); ?></span>
                                <span class="info-value"><?php echo $shoot_date ? date_i18n('M d, Y', strtotime($shoot_date)) : '-'; ?></span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="customshot-col customshot-col-status">
                            <span class="customshot-status <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="customshot-col customshot-col-actions">
                            <a href="<?php echo esc_url($detail_url); ?>" class="action-icon" title="<?php esc_attr_e('View', 'vendor-customshot'); ?>">
                                <!-- <span class="wcfmfa fa-eye"></span> -->
                                <svg width="20" height="13" viewBox="0 0 20 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 12.5C3.45733 12.5 0.213576 6.79626 0.0787324 6.55361C0.0270978 6.46072 0 6.3562 0 6.24993C0 6.14366 0.0270978 6.03914 0.0787324 5.94626C0.213576 5.70376 3.45733 0 10 0C16.5427 0 19.7864 5.70376 19.9213 5.94642C19.9729 6.0393 20 6.14382 20 6.25009C20 6.35636 19.9729 6.46088 19.9213 6.55376C19.7864 6.79626 16.5427 12.5 10 12.5ZM1.36045 6.24907C2.10389 7.37579 5.0228 11.25 10 11.25C14.993 11.25 17.8989 7.37892 18.6395 6.25095C17.8961 5.12423 14.9772 1.25 10 1.25C5.00702 1.25 2.10108 5.1211 1.36045 6.24907ZM10 10C7.93218 10 6.24999 8.31783 6.24999 6.25001C6.24999 4.1822 7.93218 2.5 10 2.5C12.0678 2.5 13.75 4.1822 13.75 6.25001C13.75 8.31783 12.0678 10 10 10ZM10 3.75001C8.62156 3.75001 7.5 4.87157 7.5 6.25001C7.5 7.62845 8.62156 8.75002 10 8.75002C11.3784 8.75002 12.5 7.62845 12.5 6.25001C12.5 4.87157 11.3784 3.75001 10 3.75001Z" fill="black"/>
                                </svg>
                                <?php _e('View', 'vendor-customshot'); ?>
                            </a>
                            <?php if ($status === 'pending') : ?>
                                <a href="<?php echo esc_url($detail_url); ?>" class="action-icon action-accept" title="<?php esc_attr_e('Send Quote', 'vendor-customshot'); ?>">
                                    <!-- <span class="wcfmfa fa-paper-plane"></span> -->
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.0831 6.99963C10.7605 6.99963 10.4998 7.261 10.4998 7.58291V12.2495C10.4998 12.5709 10.2384 12.8328 9.91653 12.8328H1.74997C1.42795 12.8328 1.16668 12.5709 1.16668 12.2495V4.08298C1.16668 3.76158 1.42795 3.49969 1.74997 3.49969H6.41659C6.73916 3.49969 6.99987 3.23834 6.99987 2.9164C6.99987 2.59438 6.73916 2.33301 6.41659 2.33301H1.74997C0.785162 2.33301 0 3.11817 0 4.08298V12.2495C0 13.2144 0.785162 13.9995 1.74997 13.9995H9.91653C10.8813 13.9995 11.6665 13.2144 11.6665 12.2495V7.58291C11.6665 7.26037 11.4056 6.99963 11.0831 6.99963Z" fill="black"/>
                                        <path d="M5.47062 6.46801C5.43008 6.50874 5.40231 6.56042 5.39072 6.61671L4.97833 8.67943C4.95911 8.77502 4.98943 8.87359 5.05823 8.94302C5.08538 8.97008 5.11759 8.99153 5.15303 9.00614C5.18848 9.02075 5.22645 9.02823 5.26478 9.02816C5.28337 9.02816 5.30271 9.02644 5.32193 9.02239L7.38399 8.61C7.44114 8.59825 7.49304 8.5709 7.53331 8.53L12.1486 3.91473L10.0865 1.85277L5.47062 6.46801ZM13.5742 0.426575C13.0055 -0.142192 12.0803 -0.142192 11.5121 0.426575L10.7048 1.23383L12.7669 3.2959L13.5742 2.48853C13.8495 2.21381 14.0012 1.84744 14.0012 1.4578C14.0012 1.06816 13.8495 0.701813 13.5742 0.426575Z" fill="black"/>
                                    </svg>
                                     <?php _e('Accept & Quote', 'vendor-customshot'); ?>
                                </a>
                                <a href="#" class="action-icon action-decline decline-customshot" data-shot-id="<?php echo esc_attr($shot->ID); ?>" title="<?php esc_attr_e('Decline', 'vendor-customshot'); ?>">
                                    <!-- <span class="wcfmfa fa-times"></span> -->
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.00124 14.0029C5.61652 14.0029 4.26291 13.5923 3.11156 12.823C1.96021 12.0537 1.06285 10.9603 0.53294 9.68095C0.00303355 8.40164 -0.135614 6.99393 0.13453 5.63582C0.404674 4.27772 1.07148 3.03022 2.05062 2.05108C3.02976 1.07194 4.27726 0.405133 5.63536 0.13499C6.99347 -0.135155 8.40119 0.00349331 9.68049 0.5334C10.9598 1.06331 12.0532 1.96067 12.8226 3.11202C13.5919 4.26337 14.0025 5.61698 14.0025 7.0017C14.0003 8.85787 13.2619 10.6374 11.9494 11.9499C10.6369 13.2624 8.85741 14.0007 7.00124 14.0029ZM7.00124 1.27341C5.86829 1.27341 4.76079 1.60937 3.81877 2.2388C2.87676 2.86823 2.14255 3.76287 1.70899 4.80958C1.27543 5.85628 1.162 7.00805 1.38302 8.11923C1.60405 9.23041 2.14961 10.2511 2.95073 11.0522C3.75184 11.8533 4.77253 12.3989 5.8837 12.6199C6.99488 12.8409 8.14665 12.7275 9.19336 12.2939C10.2401 11.8604 11.1347 11.1262 11.7641 10.1842C12.3936 9.24215 12.7295 8.13464 12.7295 7.0017C12.7278 5.48298 12.1238 4.02695 11.0499 2.95305C9.97599 1.87915 8.51995 1.2751 7.00124 1.27341Z" fill="black"/>
                                        <path d="M9.5486 7.63867H4.45679C4.28798 7.63867 4.12609 7.57161 4.00673 7.45225C3.88737 7.33289 3.82031 7.171 3.82031 7.0022C3.82031 6.83339 3.88737 6.6715 4.00673 6.55214C4.12609 6.43278 4.28798 6.36572 4.45679 6.36572H9.5486C9.7174 6.36572 9.87929 6.43278 9.99865 6.55214C10.118 6.6715 10.1851 6.83339 10.1851 7.0022C10.1851 7.171 10.118 7.33289 9.99865 7.45225C9.87929 7.57161 9.7174 7.63867 9.5486 7.63867Z" fill="black"/>
                                    </svg>
                                    <?php _e('Decline', 'vendor-customshot'); ?>
                                </a>
                            <?php endif; ?>
                            <?php if (in_array($status, array('accepted', 'in_progress', 'delivered', 'quoted'))) : ?>
                                <a href="<?php echo esc_url(get_wcfm_customshot_messages_url($shot->ID)); ?>" class="action-icon action-message" title="<?php esc_attr_e('Message', 'vendor-customshot'); ?>">
                                    <!-- <span class="wcfmfa fa-comment"></span> -->
                                        <svg width="19" height="17" viewBox="0 0 19 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M16.2873 16.6661H12.4996C11.439 16.6661 10.6057 15.8328 10.6057 14.7722V10.9845C10.6057 9.9239 11.439 9.09059 12.4996 9.09059H16.2873C17.3479 9.09059 18.1812 9.9239 18.1812 10.9845V14.7722C18.1812 15.8328 17.3479 16.6661 16.2873 16.6661ZM5.68162 16.6661H1.89387C0.833304 16.6661 0 15.8328 0 14.7722V10.9845C0 9.9239 0.833304 9.09059 1.89387 9.09059H5.68162C6.74219 9.09059 7.57549 9.9239 7.57549 10.9845V14.7722C7.57549 15.8328 6.74219 16.6661 5.68162 16.6661ZM1.89387 10.6057C1.66661 10.6057 1.5151 10.7572 1.5151 10.9845V14.7722C1.5151 14.9995 1.66661 15.151 1.89387 15.151H5.68162C5.90888 15.151 6.06039 14.9995 6.06039 14.7722V10.9845C6.06039 10.7572 5.90888 10.6057 5.68162 10.6057H1.89387ZM14.3934 7.57549C14.1662 7.57549 14.0147 7.49974 13.8632 7.34823L12.3481 5.83313C12.045 5.53011 12.045 5.07558 12.3481 4.77256C12.6511 4.46954 13.1056 4.46954 13.4086 4.77256L13.4844 4.84832C12.9541 2.87869 11.136 1.5151 9.09059 1.5151C6.59068 1.5151 4.5453 3.56048 4.5453 6.06039V6.81794C4.5453 7.27247 4.24228 7.57549 3.78775 7.57549C3.33322 7.57549 3.0302 7.27247 3.0302 6.81794V6.06039C3.0302 2.72718 5.75737 0 9.09059 0C12.1208 0 14.6207 2.19689 15.0752 5.07558L15.3783 4.77256C15.6813 4.46954 16.1358 4.46954 16.4388 4.77256C16.7418 5.07558 16.7418 5.53011 16.4388 5.83313L14.9237 7.34823C14.7722 7.49974 14.6207 7.57549 14.3934 7.57549Z" fill="black"/>
                                        </svg>
                                    <?php _e('Message', 'vendor-customshot'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>