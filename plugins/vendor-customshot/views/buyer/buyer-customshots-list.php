<?php
/**
 * Buyer Custom Shots List View
 * Shows all custom shot requests for the buyer in My Account
 */

if (!defined('ABSPATH')) exit;

$buyer_id = get_current_user_id();

// Get all custom shots for this buyer
$args = array(
    'post_type'      => 'customshot',
    'post_status'    => array('pending', 'publish', 'draft'),
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'   => '_customshot_buyer_id',
            'value' => $buyer_id,
        ),
    ),
    'orderby'        => 'date',
    'order'          => 'DESC',
);

$shots = get_posts($args);

// Group by status
$pending_shots = array();
$quoted_shots = array();
$active_shots = array();
$completed_shots = array();

foreach ($shots as $shot) {
    $status = get_post_meta($shot->ID, '_customshot_status', true);

    switch ($status) {
        case 'pending':
            $pending_shots[] = $shot;
            break;
        case 'quoted':
            $quoted_shots[] = $shot;
            break;
        case 'accepted':
        case 'in_progress':
        case 'delivered':
            $active_shots[] = $shot;
            break;
        case 'completed':
        case 'declined':
        case 'quote_rejected':
            $completed_shots[] = $shot;
            break;
        default:
            $pending_shots[] = $shot;
    }
}

$base_url = wc_get_account_endpoint_url('custom-shots');
?>

<div class="buyer-customshots">
    <h2><?php esc_html_e('My Custom Shot Requests', 'vendor-customshot'); ?></h2>

    <?php if (empty($shots)) : ?>
        <div class="customshots-empty">
            <p><?php esc_html_e('You haven\'t made any custom shot requests yet.', 'vendor-customshot'); ?></p>
            <p><?php esc_html_e('Browse creators and request a custom shoot!', 'vendor-customshot'); ?></p>
            <a href="<?php echo esc_url(home_url('/creators/')); ?>" class="button">
                <?php esc_html_e('Browse Creators', 'vendor-customshot'); ?>
            </a>
        </div>
    <?php else : ?>

        <!-- Quotes Waiting for Response -->
        <?php if (!empty($quoted_shots)) : ?>
            <div class="customshots-section">
                <h3 class="section-title">
                    <span class="badge badge-warning"><?php echo count($quoted_shots); ?></span>
                    <?php esc_html_e('Quotes Received', 'vendor-customshot'); ?>
                </h3>
                <div class="customshots-list">
                    <?php foreach ($quoted_shots as $shot) :
                        $vendor_id = get_post_meta($shot->ID, '_customshot_vendor_id', true);
                        $vendor = get_user_by('id', $vendor_id);
                        $store_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
                        $vendor_name = $store_name ? $store_name : ($vendor ? $vendor->display_name : __('Unknown', 'vendor-customshot'));
                        $vendor_avatar = get_avatar_url($vendor_id, array('size' => 50));
                        $quote = get_post_meta($shot->ID, '_customshot_vendor_quote', true);
                        $delivery_date = get_post_meta($shot->ID, '_customshot_delivery_date', true);
                    ?>
                        <div class="customshot-card quote-card">
                            <div class="card-header">
                                <img src="<?php echo esc_url($vendor_avatar); ?>" alt="" class="vendor-avatar">
                                <div class="card-info">
                                    <h4><?php echo esc_html($shot->post_title); ?></h4>
                                    <span class="vendor-name"><?php echo esc_html($vendor_name); ?></span>
                                </div>
                                <div class="quote-amount">
                                    <span class="amount">$<?php echo number_format((float)$quote, 2); ?></span>
                                    <span class="label"><?php esc_html_e('Quote', 'vendor-customshot'); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="meta-row">
                                    <span class="meta-label"><?php esc_html_e('Delivery by:', 'vendor-customshot'); ?></span>
                                    <span class="meta-value"><?php echo $delivery_date ? date_i18n('M d, Y', strtotime($delivery_date)) : '-'; ?></span>
                                </div>
                            </div>
                            <div class="card-actions">
                                <a href="<?php echo esc_url($base_url . '?view=1&id=' . $shot->ID); ?>" class="btn btn-outline tm-button">
                                    <?php esc_html_e('View Details', 'vendor-customshot'); ?>
                                </a>
                                <button type="button" class="btn btn-primary btn-accept-quote" data-shot-id="<?php echo esc_attr($shot->ID); ?>">
                                    <?php esc_html_e('Accept Quote', 'vendor-customshot'); ?>
                                </button>
                                <button type="button" class="btn btn-outline btn-reject-quote" data-shot-id="<?php echo esc_attr($shot->ID); ?>">
                                    <?php esc_html_e('Decline', 'vendor-customshot'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Active Projects -->
        <?php if (!empty($active_shots)) : ?>
            <div class="customshots-section">
                <h3 class="section-title">
                    <span class="badge badge-success"><?php echo count($active_shots); ?></span>
                    <?php esc_html_e('Active Projects', 'vendor-customshot'); ?>
                </h3>
                <div class="customshots-list">
                    <?php foreach ($active_shots as $shot) :
                        $vendor_id = get_post_meta($shot->ID, '_customshot_vendor_id', true);
                        $vendor = get_user_by('id', $vendor_id);
                        $store_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
                        $vendor_name = $store_name ? $store_name : ($vendor ? $vendor->display_name : __('Unknown', 'vendor-customshot'));
                        $vendor_avatar = get_avatar_url($vendor_id, array('size' => 50));
                        $status = get_post_meta($shot->ID, '_customshot_status', true);
                        $deliverable_status = get_post_meta($shot->ID, '_customshot_deliverable_status', true);

                        // Get unread message count
                        $unread = Customshot_Chat::get_unread_count($shot->ID, $buyer_id);
                    ?>
                        <div class="customshot-card active-card">
                            <div class="card-header">
                                <img src="<?php echo esc_url($vendor_avatar); ?>" alt="" class="vendor-avatar">
                                <div class="card-info">
                                    <h4><?php echo esc_html($shot->post_title); ?></h4>
                                    <span class="vendor-name"><?php echo esc_html($vendor_name); ?></span>
                                </div>
                                <div class="status-badge status-<?php echo esc_attr($status); ?>">
                                    <?php
                                    $status_labels = array(
                                        'accepted'    => __('In Progress', 'vendor-customshot'),
                                        'in_progress' => __('In Progress', 'vendor-customshot'),
                                        'delivered'   => __('Review Deliverables', 'vendor-customshot'),
                                    );
                                    echo esc_html($status_labels[$status] ?? ucfirst($status));
                                    ?>
                                </div>
                            </div>
                            <?php if ($deliverable_status === 'pending_review') : ?>
                                <div class="card-alert">
                                    <span class="alert-icon">!</span>
                                    <?php esc_html_e('Deliverables ready for review', 'vendor-customshot'); ?>
                                </div>
                            <?php endif; ?>
                            <div class="card-actions">
                                <a href="<?php echo esc_url($base_url . '?chat=1&id=' . $shot->ID); ?>" class="btn btn-primary">
                                    <?php esc_html_e('Open Chat', 'vendor-customshot'); ?>
                                    <?php if ($unread > 0) : ?>
                                        <span class="unread-badge"><?php echo esc_html($unread); ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pending Requests -->
        <?php if (!empty($pending_shots)) : ?>
            <div class="customshots-section">
                <h3 class="section-title">
                    <span class="badge badge-info"><?php echo count($pending_shots); ?></span>
                    <?php esc_html_e('Pending Requests', 'vendor-customshot'); ?>
                </h3>
                <div class="customshots-list">
                    <?php foreach ($pending_shots as $shot) :
                        $vendor_id = get_post_meta($shot->ID, '_customshot_vendor_id', true);
                        $vendor = get_user_by('id', $vendor_id);
                        $store_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
                        $vendor_name = $store_name ? $store_name : ($vendor ? $vendor->display_name : __('Unknown', 'vendor-customshot'));
                        $vendor_avatar = get_avatar_url($vendor_id, array('size' => 50));
                        $budget = get_post_meta($shot->ID, '_customshot_budget', true);
                    ?>
                        <div class="customshot-card pending-card">
                            <div class="card-header">
                                <img src="<?php echo esc_url($vendor_avatar); ?>" alt="" class="vendor-avatar">
                                <div class="card-info">
                                    <h4><?php echo esc_html($shot->post_title); ?></h4>
                                    <span class="vendor-name"><?php echo esc_html($vendor_name); ?></span>
                                </div>
                                <div class="status-badge status-pending">
                                    <?php esc_html_e('Awaiting Response', 'vendor-customshot'); ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="meta-row">
                                    <span class="meta-label"><?php esc_html_e('Your Budget:', 'vendor-customshot'); ?></span>
                                    <span class="meta-value">$<?php echo number_format((float)$budget, 0); ?></span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label"><?php esc_html_e('Submitted:', 'vendor-customshot'); ?></span>
                                    <span class="meta-value"><?php echo date_i18n('M d, Y', strtotime($shot->post_date)); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Completed/Closed -->
        <?php if (!empty($completed_shots)) : ?>
            <div class="customshots-section">
                <h3 class="section-title section-title-muted">
                    <?php esc_html_e('Completed & Closed', 'vendor-customshot'); ?>
                </h3>
                <div class="customshots-list">
                    <?php foreach ($completed_shots as $shot) :
                        $vendor_id = get_post_meta($shot->ID, '_customshot_vendor_id', true);
                        $vendor = get_user_by('id', $vendor_id);
                        $store_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
                        $vendor_name = $store_name ? $store_name : ($vendor ? $vendor->display_name : __('Unknown', 'vendor-customshot'));
                        $status = get_post_meta($shot->ID, '_customshot_status', true);
                    ?>
                        <div class="customshot-card closed-card">
                            <div class="card-header">
                                <div class="card-info">
                                    <h4><?php echo esc_html($shot->post_title); ?></h4>
                                    <span class="vendor-name"><?php echo esc_html($vendor_name); ?></span>
                                </div>
                                <div class="status-badge status-<?php echo esc_attr($status); ?>">
                                    <?php
                                    $status_labels = array(
                                        'completed'      => __('Completed', 'vendor-customshot'),
                                        'declined'       => __('Declined', 'vendor-customshot'),
                                        'quote_rejected' => __('Quote Rejected', 'vendor-customshot'),
                                    );
                                    echo esc_html($status_labels[$status] ?? ucfirst($status));
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
