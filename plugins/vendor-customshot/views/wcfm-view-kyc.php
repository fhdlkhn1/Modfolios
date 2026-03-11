<?php
/**
 * WCFM KYC / ID Verification View
 * - Vendors: Upload identification documents for verification
 * - Admins: Manage and approve/reject vendor verification requests
 */

if (!defined('ABSPATH')) exit;

global $WCFM, $WCFMu;

// Include helper functions
require_once get_template_directory() . '/wcfm/wcfm-helpers.php';

$user_id = get_current_user_id();
$is_admin = current_user_can('administrator') || current_user_can('shop_manager');
$is_vendor = function_exists('wcfm_is_vendor') ? wcfm_is_vendor() : false;

// =====================================================
// ADMIN VIEW - KYC Verification Management
// =====================================================
if ($is_admin) :

    // Current tab/status
    $kyc_status = !empty($_GET['kyc_status']) ? sanitize_text_field($_GET['kyc_status']) : 'pending';

    // Status labels
    $status_labels = array(
        'pending'  => __('Pending', 'vendor-customshot'),
        'approved' => __('Approved', 'vendor-customshot'),
        'rejected' => __('Rejected', 'vendor-customshot'),
    );

    // Count vendors for each status
    $count_kyc = array();
    foreach (array_keys($status_labels) as $status_key) {
        $count_args = array(
            'meta_query' => array(
                array(
                    'key'     => '_vendor_id_verification_status',
                    'value'   => $status_key,
                    'compare' => '=',
                ),
                array(
                    'key'     => '_vendor_id_document',
                    'compare' => 'EXISTS',
                ),
            ),
            'fields' => 'ID',
        );
        $count_kyc[$status_key] = count(get_users($count_args));
    }

    // Pagination
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 10;
    $offset = ($paged - 1) * $per_page;

    // Query vendors with KYC documents
    $user_args = array(
        'meta_query' => array(
            array(
                'key'     => '_vendor_id_verification_status',
                'value'   => $kyc_status,
                'compare' => '=',
            ),
            array(
                'key'     => '_vendor_id_document',
                'compare' => 'EXISTS',
            ),
        ),
        'number' => $per_page,
        'offset' => $offset,
        'orderby' => 'meta_value',
        'meta_key' => '_vendor_id_upload_date',
        'order' => 'DESC',
    );

    $vendors_query = new WP_User_Query($user_args);
    $vendors = $vendors_query->get_results();
    $total_vendors = $vendors_query->get_total();
    $total_pages = ceil($total_vendors / $per_page);

    // Get KYC page URL
    $kyc_page_url = function_exists('get_wcfm_url') ? get_wcfm_url() . 'kyc/' : home_url('/store-manager/kyc/');
?>

<div class="collapse wcfm-collapse modfolio-kyc-admin-page" id="wcfm_kyc_listing">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <?php
        // Render header with breadcrumb
        $breadcrumb_items = array(
            array('label' => __('KYC Verification', 'vendor-customshot'), 'url' => ''),
            array('label' => isset($status_labels[$kyc_status]) ? $status_labels[$kyc_status] : 'Pending', 'url' => '')
        );
        modfolio_wcfm_render_header('', $breadcrumb_items);
        ?>

        <!-- Tabs Bar -->
        <div class="modfolio-tabs-bar">
            <div class="tabs-left">
                <ul class="modfolio-kyc-tabs">
                    <?php foreach ($status_labels as $status_key => $status_label) : ?>
                        <li>
                            <a href="<?php echo esc_url(add_query_arg('kyc_status', $status_key, $kyc_page_url)); ?>"
                               class="tab-btn <?php echo ($status_key == $kyc_status) ? 'active' : ''; ?>">
                                <?php echo esc_html($status_label); ?>
                                <span class="tab-count">(<?php echo intval($count_kyc[$status_key]); ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="wcfm-clearfix"></div>

        <!-- KYC Requests Table -->
        <div class="modfolio-kyc-container">
            <?php if (empty($vendors)) : ?>
                <div class="modfolio-no-items">
                    <p><?php _e('No verification requests found.', 'vendor-customshot'); ?></p>
                </div>
            <?php else : ?>
                <!-- Table Header -->
                <div class="kyc-table-header">
                    <div class="kyc-col kyc-col-document"><?php _e('Verification Document', 'vendor-customshot'); ?></div>
                    <div class="kyc-col kyc-col-store"><?php _e('Store Name', 'vendor-customshot'); ?></div>
                    <div class="kyc-col kyc-col-user"><?php _e('User Details', 'vendor-customshot'); ?></div>
                    <div class="kyc-col kyc-col-date"><?php _e('Submitted', 'vendor-customshot'); ?></div>
                    <div class="kyc-col kyc-col-status"><?php _e('Status', 'vendor-customshot'); ?></div>
                    <div class="kyc-col kyc-col-actions"><?php _e('Actions', 'vendor-customshot'); ?></div>
                </div>

                <div class="modfolio-kyc-list">
                    <?php foreach ($vendors as $vendor) :
                        $vendor_id = $vendor->ID;
                        $document_id = get_user_meta($vendor_id, '_vendor_id_document', true);
                        $document_url = $document_id ? wp_get_attachment_url($document_id) : '';
                        $document_name = $document_id ? basename(get_attached_file($document_id)) : '';
                        $verification_status = get_user_meta($vendor_id, '_vendor_id_verification_status', true);
                        $upload_date = get_user_meta($vendor_id, '_vendor_id_upload_date', true);
                        $rejection_reason = get_user_meta($vendor_id, '_vendor_id_rejection_reason', true);

                        // Get document thumbnail
                        $document_thumb = '';
                        $is_pdf = false;
                        $file_path = $document_id ? get_attached_file($document_id) : '';
                        $file_ext = $file_path ? strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) : '';

                        if ($file_ext === 'pdf') {
                            $is_pdf = true;
                            // Use a PDF icon as thumbnail
                            $document_thumb = '';
                        } else {
                            // Get image thumbnail
                            $thumb_array = wp_get_attachment_image_src($document_id, 'thumbnail');
                            $document_thumb = $thumb_array ? $thumb_array[0] : $document_url;
                        }

                        // Get store info
                        $store_name = get_user_meta($vendor_id, 'store_name', true);
                        if (empty($store_name)) {
                            $store_name = get_user_meta($vendor_id, 'wcfmmp_store_name', true);
                        }
                        if (empty($store_name)) {
                            $store_name = $vendor->display_name;
                        }

                        // Store URL
                        $store_url = function_exists('wcfmmp_get_store_url') ? wcfmmp_get_store_url($vendor_id) : '#';

                        // Admin user edit URL
                        $user_edit_url = admin_url('user-edit.php?user_id=' . $vendor_id);

                        // Status class
                        $status_class = 'status-' . $verification_status;
                    ?>
                        <div class="kyc-row" data-vendor-id="<?php echo esc_attr($vendor_id); ?>">
                            <!-- Document Preview with LightGallery -->
                            <div class="kyc-col kyc-col-document">
                                <div class="document-preview-wrapper minimog-light-gallery">
                                    <a href="<?php echo esc_url($document_url); ?>" class="document-preview-link" data-sub-html="<?php echo esc_attr($store_name . ' - ' . __('Verification Document', 'vendor-customshot')); ?>">
                                        <?php if ($is_pdf) : ?>
                                            <div class="document-preview document-preview-pdf">
                                                <svg width="40" height="48" viewBox="0 0 40 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M24 0H4C2.93913 0 1.92172 0.421427 1.17157 1.17157C0.421427 1.92172 0 2.93913 0 4V44C0 45.0609 0.421427 46.0783 1.17157 46.8284C1.92172 47.5786 2.93913 48 4 48H36C37.0609 48 38.0783 47.5786 38.8284 46.8284C39.5786 46.0783 40 45.0609 40 44V16L24 0Z" fill="#E74C3C"/>
                                                    <path d="M24 0V16H40" fill="#C0392B"/>
                                                    <text x="20" y="36" text-anchor="middle" fill="white" font-size="10" font-weight="bold">PDF</text>
                                                </svg>
                                                <span class="preview-label"><?php _e('PDF Document', 'vendor-customshot'); ?></span>
                                            </div>
                                        <?php else : ?>
                                            <div class="document-preview document-preview-image">
                                                <img src="<?php echo esc_url($document_thumb); ?>" alt="<?php echo esc_attr(__('Verification Document', 'vendor-customshot')); ?>">
                                                <span class="preview-zoom-icon">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M21 21L16.65 16.65" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M11 8V14M8 11H14" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Store Name -->
                            <div class="kyc-col kyc-col-store">
                                <a href="<?php echo esc_url($store_url); ?>" target="_blank" class="store-link">
                                    <?php echo esc_html($store_name); ?>
                                </a>
                            </div>

                            <!-- User Details -->
                            <div class="kyc-col kyc-col-user">
                                <a href="<?php echo esc_url($user_edit_url); ?>" target="_blank" class="user-link">
                                    <?php echo esc_html($vendor->user_email); ?>
                                    <span class="external-icon">&#8599;</span>
                                </a>
                            </div>

                            <!-- Submitted Date -->
                            <div class="kyc-col kyc-col-date">
                                <?php echo $upload_date ? date_i18n('M j, Y', strtotime($upload_date)) : '-'; ?>
                            </div>

                            <!-- Status -->
                            <div class="kyc-col kyc-col-status">
                                <span class="status-badge <?php echo esc_attr($status_class); ?>">
                                    <?php echo isset($status_labels[$verification_status]) ? esc_html($status_labels[$verification_status]) : esc_html(ucfirst($verification_status)); ?>
                                </span>
                                <?php if ($verification_status === 'rejected' && $rejection_reason) : ?>
                                    <span class="rejection-reason" title="<?php echo esc_attr($rejection_reason); ?>">
                                        <?php echo esc_html($rejection_reason); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="kyc-col kyc-col-actions">
                                <a href="#" class="action-btn action-view-details"
                                   data-vendor-id="<?php echo esc_attr($vendor_id); ?>"
                                   data-vendor-name="<?php echo esc_attr($store_name); ?>"
                                   data-status="<?php echo esc_attr($verification_status); ?>">
                                    <span class="action-icon">&#128065;</span>
                                    <span class="action-text"><?php _e('View Details', 'vendor-customshot'); ?></span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1) : ?>
                    <div class="modfolio-pagination">
                        <?php
                        $base_url = add_query_arg('kyc_status', $kyc_status, $kyc_page_url);
                        for ($i = 1; $i <= $total_pages; $i++) :
                            $page_url = add_query_arg('paged', $i, $base_url);
                            $is_current = ($i == $paged);
                        ?>
                            <a href="<?php echo esc_url($page_url); ?>" class="page-btn <?php echo $is_current ? 'active' : ''; ?>">
                                <?php echo esc_html($i); ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- KYC Detail Modal -->
<div class="modfolio-modal-overlay" id="kyc-detail-modal" style="display: none;">
    <div class="modfolio-modal kyc-detail-modal-content">
        <span class="modal-close" id="kyc-detail-close">&times;</span>

        <!-- Loading -->
        <div class="kyc-detail-loading" id="kyc-detail-loading">
            <div class="kyc-detail-spinner"></div>
            <p><?php _e('Loading details...', 'vendor-customshot'); ?></p>
        </div>

        <!-- Detail Body -->
        <div class="kyc-detail-body" id="kyc-detail-body" style="display:none;">
            <!-- Vendor Name Header -->
            <div class="kyc-detail-header">
                <h3 id="kd-vendor-name"></h3>
                <span class="status-badge" id="kd-status-badge"></span>
            </div>

            <!-- Personal Info -->
            <div class="kyc-detail-section">
                <h4><?php _e('Personal Information', 'vendor-customshot'); ?></h4>
                <div class="kyc-detail-grid">
                    <div class="detail-item">
                        <span class="detail-label"><?php _e('First Name', 'vendor-customshot'); ?></span>
                        <span class="detail-value" id="kd-first-name">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php _e('Last Name', 'vendor-customshot'); ?></span>
                        <span class="detail-value" id="kd-last-name">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php _e('Date of Birth', 'vendor-customshot'); ?></span>
                        <span class="detail-value" id="kd-dob">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php _e('Country', 'vendor-customshot'); ?></span>
                        <span class="detail-value" id="kd-country">-</span>
                    </div>
                </div>
            </div>

            <!-- Document Info -->
            <div class="kyc-detail-section">
                <h4><?php _e('ID Document', 'vendor-customshot'); ?></h4>
                <p class="detail-doc-type"><?php _e('Type:', 'vendor-customshot'); ?> <strong id="kd-doc-type">-</strong></p>
                <div class="kyc-detail-images">
                    <div class="detail-image-box" id="kd-doc-box">
                        <div class="detail-image-gallery minimog-light-gallery" id="kd-doc-gallery">
                            <a href="#" id="kd-doc-link" data-sub-html="<?php esc_attr_e('ID Document', 'vendor-customshot'); ?>">
                                <img src="" id="kd-doc-img" alt="<?php esc_attr_e('ID Document', 'vendor-customshot'); ?>">
                                <span class="image-zoom-overlay">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="white" stroke-width="2"/><path d="M21 21L16.65 16.65" stroke="white" stroke-width="2"/><path d="M11 8V14M8 11H14" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                                </span>
                            </a>
                        </div>
                        <span class="image-label"><?php _e('ID Document', 'vendor-customshot'); ?></span>
                    </div>
                    <div class="detail-image-box" id="kd-selfie-box">
                        <div class="detail-image-gallery minimog-light-gallery" id="kd-selfie-gallery">
                            <a href="#" id="kd-selfie-link" data-sub-html="<?php esc_attr_e('Liveness Selfie', 'vendor-customshot'); ?>">
                                <img src="" id="kd-selfie-img" alt="<?php esc_attr_e('Liveness Selfie', 'vendor-customshot'); ?>">
                                <span class="image-zoom-overlay">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="white" stroke-width="2"/><path d="M21 21L16.65 16.65" stroke="white" stroke-width="2"/><path d="M11 8V14M8 11H14" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                                </span>
                            </a>
                        </div>
                        <span class="image-label"><?php _e('Liveness Selfie', 'vendor-customshot'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Rejection reason display -->
            <div class="kyc-detail-rejection-display" id="kd-rejection-display" style="display:none;">
                <span class="detail-label"><?php _e('Rejection Reason:', 'vendor-customshot'); ?></span>
                <span class="detail-rejection-text" id="kd-rejection-reason-text"></span>
            </div>

            <!-- Action Buttons (only for pending) -->
            <div class="kyc-detail-actions" id="kd-actions" style="display:none;">
                <button type="button" class="modal-btn btn-reject" id="kd-reject-btn"><?php _e('Reject', 'vendor-customshot'); ?></button>
                <button type="button" class="modal-btn btn-approve" id="kd-approve-btn"><?php _e('Approve', 'vendor-customshot'); ?></button>
            </div>

            <!-- Rejection Form (hidden) -->
            <div class="kyc-detail-rejection-form" id="kd-rejection-form" style="display:none;">
                <div class="rejection-form">
                    <label for="kd-rejection-reason-input"><?php _e('Rejection Reason', 'vendor-customshot'); ?> <span class="required">*</span></label>
                    <input type="text" id="kd-rejection-reason-input" placeholder="<?php esc_attr_e('Enter rejection reason here', 'vendor-customshot'); ?>">
                </div>
                <div class="modal-actions">
                    <button type="button" class="modal-btn btn-cancel" id="kd-cancel-reject"><?php _e('Cancel', 'vendor-customshot'); ?></button>
                    <button type="button" class="modal-btn btn-submit" id="kd-submit-reject"><?php _e('Submit', 'vendor-customshot'); ?></button>
                </div>
            </div>
        </div>

        <input type="hidden" id="kyc-detail-vendor-id" value="">
    </div>
</div>

<style>
<?php echo modfolio_wcfm_get_header_styles(); ?>

/* KYC Admin Page Styles */
.modfolio-kyc-admin-page {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Hide default WCFM elements */
.modfolio-kyc-admin-page > .wcfm-page-headig {
    display: none !important;
}

/* Tabs Bar */
.modfolio-tabs-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.tabs-left {
    flex: 1;
}

.modfolio-kyc-tabs {
    display: flex;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.modfolio-kyc-tabs li {
    margin: 0;
}

.modfolio-kyc-tabs .tab-btn {
    display: inline-block;
    padding: 10px 20px;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 500;
    color: #666;
    text-decoration: none;
    transition: all 0.2s;
}

.modfolio-kyc-tabs .tab-btn:hover {
    border-color: #00c4aa;
    color: #00c4aa;
}

.modfolio-kyc-tabs .tab-btn.active {
    background: #1a1a1a;
    border-color: #1a1a1a;
    color: #fff;
}

.tab-count {
    opacity: 0.7;
    margin-left: 4px;
}

/* KYC Container */
.modfolio-kyc-container {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
}

.modfolio-no-items {
    padding: 60px 20px;
    text-align: center;
    color: #666;
}

/* Table Header */
.kyc-table-header {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e5e5e5;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* KYC List */
.modfolio-kyc-list {
    display: flex;
    flex-direction: column;
}

/* KYC Row */
.kyc-row {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.kyc-row:hover {
    background: #f8f9fa;
}

.kyc-row:last-child {
    border-bottom: none;
}

/* KYC Columns */
.kyc-col {
    flex-shrink: 0;
}

.kyc-col-document {
    width: 25%;
    min-width: 180px;
}

.kyc-col-store {
    width: 18%;
    min-width: 140px;
}

.kyc-col-user {
    width: 22%;
    min-width: 160px;
}

.kyc-col-date {
    width: 12%;
    min-width: 100px;
    font-size: 13px;
    color: #666;
}

.kyc-col-status {
    width: 12%;
    min-width: 100px;
    text-align: center;
}

.kyc-col-actions {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 16px;
    justify-content: flex-end;
}

/* Document Preview */
.document-preview-wrapper {
    display: inline-block;
}

.document-preview-link {
    display: block;
    text-decoration: none;
}

.document-preview {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.document-preview:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.document-preview-image {
    width: 70px;
    height: 70px;
    background: #f5f5f5;
}

.document-preview-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.document-preview-image .preview-zoom-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 32px;
    height: 32px;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.document-preview-image:hover .preview-zoom-icon {
    opacity: 1;
}

.document-preview-pdf {
    width: 70px;
    height: 70px;
    background: #fff5f5;
    border: 1px solid #f0e0e0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.document-preview-pdf svg {
    width: 32px;
    height: 38px;
}

.document-preview-pdf .preview-label {
    font-size: 9px;
    color: #666;
    text-align: center;
}

/* Store Link */
.store-link {
    font-size: 14px;
    font-weight: 500;
    color: #1a1a1a;
    text-decoration: none;
}

.store-link:hover {
    color: #00c4aa;
}

/* User Link */
.user-link {
    font-size: 13px;
    color: #666;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
}

.user-link:hover {
    color: #00c4aa;
}

.external-icon {
    font-size: 12px;
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #00c4aa;
    color: #fff;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.rejection-reason {
    display: block;
    font-size: 11px;
    color: #e74c3c;
    margin-top: 4px;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Action Buttons */
.action-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #666;
    text-decoration: none;
    transition: color 0.2s;
    white-space: nowrap;
}

.action-btn:hover {
    color: #00c4aa;
}

.action-icon {
    font-size: 14px;
}

.action-view-details {
    color: #1a1a1a;
    font-weight: 500;
}

/* Pagination */
.modfolio-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 20px;
    border-top: 1px solid #f0f0f0;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f5f5f5;
    color: #666;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}

.page-btn:hover {
    background: #e5e5e5;
    color: #1a1a1a;
}

.page-btn.active {
    background: #00c4aa;
    color: #fff;
}

/* Modal */
.modfolio-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
}

.modfolio-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 400px;
    padding: 30px;
    position: relative;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 30px;
    height: 30px;
    border: none;
    background: none;
    font-size: 24px;
    color: #999;
    cursor: pointer;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #1a1a1a;
}

.modal-content {
    text-align: center;
}

.modal-icon {
    margin-bottom: 20px;
}

.modal-text {
    font-size: 14px;
    color: #666;
    margin-bottom: 16px;
    line-height: 1.5;
}

.modal-text strong {
    color: #1a1a1a;
    font-size: 16px;
}

.modal-vendor-name {
    font-size: 16px;
    font-weight: 600;
    color: #00c4aa;
    margin-bottom: 24px;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.modal-btn {
    padding: 12px 28px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-reject,
.btn-cancel {
    background: #fff;
    border: 1px solid #e5e5e5;
    color: #1a1a1a;
}

.btn-reject:hover,
.btn-cancel:hover {
    background: #f5f5f5;
}

.btn-approve,
.btn-submit {
    background: #00c4aa;
    color: #fff;
}

.btn-approve:hover,
.btn-submit:hover {
    background: #00b09a;
}

/* Rejection Form */
.rejection-form {
    margin-bottom: 24px;
    text-align: left;
}

.rejection-form label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #00c4aa;
    margin-bottom: 8px;
}

.rejection-form label .required {
    color: #e74c3c;
}

.rejection-form input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
}

.rejection-form input:focus {
    outline: none;
    border-color: #00c4aa;
}

/* KYC Detail Modal */
.kyc-detail-modal-content {
    max-width: 640px;
    max-height: 90vh;
    overflow-y: auto;
}

.kyc-detail-loading {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}
.kyc-detail-spinner {
    width: 36px;
    height: 36px;
    border: 3px solid #e5e5e5;
    border-top-color: #00c4aa;
    border-radius: 50%;
    animation: kyc-admin-spin 0.8s linear infinite;
    margin: 0 auto 12px;
}
@keyframes kyc-admin-spin { to { transform: rotate(360deg); } }

.kyc-detail-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f0f0;
}
.kyc-detail-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.kyc-detail-section {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
}
.kyc-detail-section:last-of-type {
    border-bottom: none;
}
.kyc-detail-section h4 {
    font-size: 12px;
    font-weight: 600;
    color: #00c4aa;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 16px 0;
}

.kyc-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.detail-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.detail-value {
    font-size: 15px;
    font-weight: 500;
    color: #1a1a1a;
}

.detail-doc-type {
    font-size: 14px;
    color: #666;
    margin: 0 0 16px 0;
}
.detail-doc-type strong {
    color: #1a1a1a;
}

.kyc-detail-images {
    display: flex;
    gap: 16px;
}
.detail-image-box {
    flex: 1;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e5e5;
    background: #f8f9fa;
}
.detail-image-gallery {
    position: relative;
    display: block;
}
.detail-image-gallery a {
    display: block;
    position: relative;
}
.detail-image-gallery img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
    cursor: pointer;
}
.image-zoom-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
.detail-image-gallery a:hover .image-zoom-overlay {
    opacity: 1;
}
.image-label {
    display: block;
    text-align: center;
    padding: 8px;
    font-size: 12px;
    color: #666;
    border-top: 1px solid #e5e5e5;
}

.kyc-detail-rejection-display {
    background: #fff5f5;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 20px;
}
.detail-rejection-text {
    font-size: 14px;
    color: #721c24;
    font-weight: 500;
}

.kyc-detail-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 20px;
}

.kyc-detail-rejection-form {
    margin-top: 20px;
}

/* Responsive */
@media (max-width: 1024px) {
    .modfolio-tabs-bar {
        flex-direction: column;
        gap: 16px;
    }

    .modfolio-kyc-tabs {
        flex-wrap: wrap;
        justify-content: center;
    }

    .kyc-table-header {
        display: none;
    }

    .kyc-row {
        flex-wrap: wrap;
        gap: 12px;
    }

    .kyc-col {
        width: auto !important;
        min-width: auto !important;
    }

    .kyc-col-actions {
        width: 100%;
        justify-content: flex-start;
        margin-top: 8px;
    }
}

@media (max-width: 768px) {
    .modfolio-kyc-tabs .tab-btn {
        padding: 8px 14px;
        font-size: 12px;
    }
    .kyc-detail-grid {
        grid-template-columns: 1fr;
    }
    .kyc-detail-images {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    var nonce = '<?php echo wp_create_nonce("modfolio_kyc_actions"); ?>';
    var ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';
    var currentVendorId = null;

    var lgSettings = {
        selector: 'a',
        download: true,
        counter: false,
        getCaptionFromTitleOrAlt: false,
        subHtmlSelectorRelative: true
    };
    var settings = (typeof window.minimog !== 'undefined' && window.minimog.LightGallery)
        ? window.minimog.LightGallery
        : lgSettings;

    // ==================
    // Open Detail Modal
    // ==================
    $(document).on('click', '.action-view-details', function(e) {
        e.preventDefault();
        currentVendorId = $(this).data('vendor-id');
        $('#kyc-detail-vendor-id').val(currentVendorId);

        // Reset modal state
        $('#kyc-detail-loading').show();
        $('#kyc-detail-body').hide();
        $('#kd-rejection-form').hide();
        $('#kd-actions').hide();
        $('#kd-rejection-display').hide();
        $('#kyc-detail-modal').fadeIn(200);

        // Fetch KYC details via AJAX
        $.post(ajaxUrl, {
            action: 'modfolio_get_kyc_details',
            vendor_id: currentVendorId,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                var d = response.data;

                // Populate fields
                $('#kd-vendor-name').text(d.vendor_name || '-');
                $('#kd-first-name').text(d.first_name || '-');
                $('#kd-last-name').text(d.last_name || '-');
                $('#kd-dob').text(d.dob || '-');
                $('#kd-country').text(d.country || '-');
                $('#kd-doc-type').text(d.document_type || '-');

                // Document image
                if (d.document_thumb) {
                    $('#kd-doc-img').attr('src', d.document_thumb);
                    $('#kd-doc-link').attr('href', d.document_url);
                    $('#kd-doc-box').show();
                } else {
                    $('#kd-doc-box').hide();
                }

                // Selfie image
                if (d.selfie_thumb) {
                    $('#kd-selfie-img').attr('src', d.selfie_thumb);
                    $('#kd-selfie-link').attr('href', d.selfie_url);
                    $('#kd-selfie-box').show();
                } else {
                    $('#kd-selfie-box').hide();
                }

                // Status badge
                var statusClass = 'status-' + d.status;
                var statusLabels = { 'pending': '<?php echo esc_js(__("Pending", "vendor-customshot")); ?>', 'approved': '<?php echo esc_js(__("Approved", "vendor-customshot")); ?>', 'rejected': '<?php echo esc_js(__("Rejected", "vendor-customshot")); ?>' };
                $('#kd-status-badge').attr('class', 'status-badge ' + statusClass).text(statusLabels[d.status] || d.status);

                // Rejection reason
                if (d.status === 'rejected' && d.rejection_reason) {
                    $('#kd-rejection-reason-text').text(d.rejection_reason);
                    $('#kd-rejection-display').show();
                } else {
                    $('#kd-rejection-display').hide();
                }

                // Show approve/reject actions only for pending
                if (d.status === 'pending') {
                    $('#kd-actions').show();
                } else {
                    $('#kd-actions').hide();
                }

                // Show body, hide loading
                $('#kyc-detail-loading').hide();
                $('#kyc-detail-body').show();

                // Initialize LightGallery for images
                if ($.fn.lightGallery) {
                    try { $('#kd-doc-gallery').data('lightGallery').destroy(true); } catch(ex) {}
                    try { $('#kd-selfie-gallery').data('lightGallery').destroy(true); } catch(ex) {}
                    if (d.document_thumb) {
                        $('#kd-doc-gallery').lightGallery(settings);
                    }
                    if (d.selfie_thumb) {
                        $('#kd-selfie-gallery').lightGallery(settings);
                    }
                }
            } else {
                alert(response.data.message || '<?php echo esc_js(__("Failed to load details.", "vendor-customshot")); ?>');
                $('#kyc-detail-modal').fadeOut(200);
            }
        }).fail(function() {
            alert('<?php echo esc_js(__("Failed to load details.", "vendor-customshot")); ?>');
            $('#kyc-detail-modal').fadeOut(200);
        });
    });

    // ==================
    // Close Modal
    // ==================
    $('#kyc-detail-close').on('click', function() {
        $('#kyc-detail-modal').fadeOut(200);
        currentVendorId = null;
    });
    $('#kyc-detail-modal').on('click', function(e) {
        if ($(e.target).hasClass('modfolio-modal-overlay')) {
            $(this).fadeOut(200);
            currentVendorId = null;
        }
    });

    // ==================
    // Approve
    // ==================
    $('#kd-approve-btn').on('click', function() {
        if (!currentVendorId) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('<?php echo esc_js(__("Processing...", "vendor-customshot")); ?>');

        $.post(ajaxUrl, {
            action: 'modfolio_approve_kyc',
            vendor_id: currentVendorId,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php echo esc_js(__("Error approving verification.", "vendor-customshot")); ?>');
                $btn.prop('disabled', false).text('<?php echo esc_js(__("Approve", "vendor-customshot")); ?>');
            }
        }).fail(function() {
            alert('<?php echo esc_js(__("Error approving verification.", "vendor-customshot")); ?>');
            $btn.prop('disabled', false).text('<?php echo esc_js(__("Approve", "vendor-customshot")); ?>');
        });
    });

    // ==================
    // Reject Flow
    // ==================
    $('#kd-reject-btn').on('click', function() {
        $('#kd-actions').hide();
        $('#kd-rejection-form').show();
    });

    $('#kd-cancel-reject').on('click', function() {
        $('#kd-rejection-form').hide();
        $('#kd-actions').show();
        $('#kd-rejection-reason-input').val('');
    });

    $('#kd-submit-reject').on('click', function() {
        if (!currentVendorId) return;
        var reason = $('#kd-rejection-reason-input').val().trim();
        if (!reason) {
            alert('<?php echo esc_js(__("Please enter a rejection reason.", "vendor-customshot")); ?>');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).text('<?php echo esc_js(__("Processing...", "vendor-customshot")); ?>');

        $.post(ajaxUrl, {
            action: 'modfolio_reject_kyc',
            vendor_id: currentVendorId,
            reason: reason,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php echo esc_js(__("Error rejecting verification.", "vendor-customshot")); ?>');
                $btn.prop('disabled', false).text('<?php echo esc_js(__("Submit", "vendor-customshot")); ?>');
            }
        }).fail(function() {
            alert('<?php echo esc_js(__("Error rejecting verification.", "vendor-customshot")); ?>');
            $btn.prop('disabled', false).text('<?php echo esc_js(__("Submit", "vendor-customshot")); ?>');
        });
    });
});
</script>

<?php
// =====================================================
// VENDOR VIEW - KYC Multi-Step Verification
// =====================================================
else :

    // Get current verification status
    $verification_status = get_user_meta($user_id, '_vendor_id_verification_status', true);
    $uploaded_file_id = get_user_meta($user_id, '_vendor_id_document', true);
    $uploaded_file_url = $uploaded_file_id ? wp_get_attachment_url($uploaded_file_id) : '';
    $rejection_reason = get_user_meta($user_id, '_vendor_id_rejection_reason', true);

    // Get saved KYC fields (for re-submission after rejection)
    $saved_first_name = get_user_meta($user_id, '_vendor_kyc_first_name', true);
    $saved_last_name  = get_user_meta($user_id, '_vendor_kyc_last_name', true);
    $saved_dob        = get_user_meta($user_id, '_vendor_kyc_dob', true);
    $saved_country    = get_user_meta($user_id, '_vendor_kyc_country', true);
    $saved_doc_type   = get_user_meta($user_id, '_vendor_kyc_document_type', true);

    // Country list from WooCommerce
    $countries = (function_exists('WC') && WC()->countries) ? WC()->countries->get_countries() : array();
?>

<div class="collapse wcfm-collapse" id="wcfm_kyc_listing">

<?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>

    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-id-card"></span>
        <span class="wcfm-page-heading-text"><?php _e('Identity Verification', 'vendor-customshot'); ?></span>
    </div>
    <div class="wcfm-collapse-content">
        <div class="wcfm_kyc_container">

            <!-- Current Status Banner -->
            <?php if ($verification_status) : ?>
                <div class="kyc-status-banner kyc-status-<?php echo esc_attr($verification_status); ?>">
                    <?php if ($verification_status === 'approved') : ?>
                        <span class="kyc-status-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="kyc-status-text"><?php _e('Your ID has been verified', 'vendor-customshot'); ?></span>
                    <?php elseif ($verification_status === 'pending') : ?>
                        <span class="kyc-status-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="kyc-status-text"><?php _e('Your verification is under review', 'vendor-customshot'); ?></span>
                    <?php elseif ($verification_status === 'rejected') : ?>
                        <span class="kyc-status-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div class="kyc-status-text-wrapper">
                            <span class="kyc-status-text"><?php _e('Your verification was rejected. Please re-submit.', 'vendor-customshot'); ?></span>
                            <?php if ($rejection_reason) : ?>
                                <span class="kyc-rejection-reason"><?php _e('Reason:', 'vendor-customshot'); ?> <?php echo esc_html($rejection_reason); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($verification_status === 'approved') : ?>
                <!-- Verified Success Section -->
                <div class="kyc-verified-section">
                    <div class="kyc-verified-content">
                        <div class="kyc-verified-icon">
                            <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="60" cy="60" r="55" fill="#E8F8F5" stroke="#00C4B4" stroke-width="3"/>
                                <path d="M35 60L52 77L85 44" stroke="#00C4B4" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3 class="kyc-verified-title"><?php _e('ID Verification Complete', 'vendor-customshot'); ?></h3>
                        <p class="kyc-verified-message"><?php _e('Your identity has been successfully verified. You now have full access to all vendor features.', 'vendor-customshot'); ?></p>

                        <?php if ($uploaded_file_url) : ?>
                            <div class="kyc-verified-document minimog-light-gallery">
                                <a href="<?php echo esc_url($uploaded_file_url); ?>" class="kyc-view-document-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <?php _e('View Uploaded Document', 'vendor-customshot'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else : ?>
                <!-- Multi-Step KYC Form -->
                <div class="kyc-multistep-wrapper">
                    <!-- Header -->
                    <div class="kyc-multistep-header">
                        <h2 class="kyc-multistep-title"><?php _e('Identity Verification', 'vendor-customshot'); ?></h2>
                        <span class="kyc-step-counter"><?php _e('Step', 'vendor-customshot'); ?> <span id="kyc-current-step-num">1</span> <?php _e('of 3', 'vendor-customshot'); ?></span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="kyc-progress-bar">
                        <div class="kyc-progress-fill" id="kyc-progress-fill" style="width: 33.33%;"></div>
                    </div>

                    <!-- Step Labels -->
                    <div class="kyc-step-labels">
                        <span class="kyc-step-label active" data-step="1"><?php _e('Personal Info', 'vendor-customshot'); ?></span>
                        <span class="kyc-step-label" data-step="2"><?php _e('ID Document', 'vendor-customshot'); ?></span>
                        <span class="kyc-step-label" data-step="3"><?php _e('Liveness Check', 'vendor-customshot'); ?></span>
                    </div>

                    <form id="kyc-multistep-form" method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field('kyc_upload_nonce', 'kyc_nonce'); ?>

                        <!-- ============================== -->
                        <!-- STEP 1: Basic Information      -->
                        <!-- ============================== -->
                        <div class="kyc-form-step" id="kyc-step-1">
                            <div class="kyc-step-content">
                                <div class="kyc-step-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#00C4B4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#00C4B4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <h3><?php _e('Basic Information', 'vendor-customshot'); ?></h3>
                                </div>
                                <p class="kyc-step-desc"><?php _e('Please enter your legal name exactly as it appears on your government-issued ID.', 'vendor-customshot'); ?></p>

                                <div class="kyc-field-row">
                                    <div class="figma-input-field">
                                        <span class="field-label"><?php _e('First Name', 'vendor-customshot'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" id="kyc_first_name" name="kyc_first_name"
                                               value="<?php echo esc_attr($saved_first_name); ?>"
                                               placeholder="<?php esc_attr_e('e.g. Sarah', 'vendor-customshot'); ?>">
                                    </div>
                                    <div class="figma-input-field">
                                        <span class="field-label"><?php _e('Last Name', 'vendor-customshot'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="text" id="kyc_last_name" name="kyc_last_name"
                                               value="<?php echo esc_attr($saved_last_name); ?>"
                                               placeholder="<?php esc_attr_e('e.g. Connor', 'vendor-customshot'); ?>">
                                    </div>
                                </div>

                                <div class="kyc-field-row">
                                    <div class="figma-input-field">
                                        <span class="field-label"><?php _e('Date of Birth', 'vendor-customshot'); ?></span>
                                        <span class="field-separator"></span>
                                        <input type="date" id="kyc_dob" name="kyc_dob"
                                               value="<?php echo esc_attr($saved_dob); ?>"
                                               placeholder="dd/mm/yyyy">
                                    </div>
                                    <div class="figma-input-field">
                                        <span class="field-label"><?php _e('Country', 'vendor-customshot'); ?></span>
                                        <span class="field-separator"></span>
                                        <select id="kyc_country" name="kyc_country">
                                            <option value=""><?php _e('Select Country', 'vendor-customshot'); ?></option>
                                            <?php foreach ($countries as $code => $name) : ?>
                                                <option value="<?php echo esc_attr($code); ?>" <?php selected($saved_country, $code); ?>>
                                                    <?php echo esc_html($name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <span class="kyc-field-hint"><?php _e('You must be 18+ to become a creator.', 'vendor-customshot'); ?></span>
                            </div>

                            <div class="kyc-step-actions kyc-step-actions-right">
                                <button type="button" class="kyc-btn kyc-btn-primary kyc-btn-next" data-next="2">
                                    <?php _e('Continue', 'vendor-customshot'); ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- ============================== -->
                        <!-- STEP 2: Upload Government ID   -->
                        <!-- ============================== -->
                        <div class="kyc-form-step" id="kyc-step-2" style="display:none;">
                            <div class="kyc-step-content">
                                <div class="kyc-step-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="2" y="5" width="20" height="14" rx="2" stroke="#00C4B4" stroke-width="2"/>
                                        <path d="M2 10H22" stroke="#00C4B4" stroke-width="2"/>
                                    </svg>
                                    <h3><?php _e('Upload Government ID', 'vendor-customshot'); ?></h3>
                                </div>

                                <!-- Document Type Toggle -->
                                <div class="kyc-doc-type-toggle">
                                    <span class="doc-type-btn <?php echo ($saved_doc_type === 'passport' || empty($saved_doc_type)) ? 'active' : ''; ?>" data-type="passport">
                                        <?php _e('Passport', 'vendor-customshot'); ?>
                                    </span>
                                    <span class="doc-type-btn <?php echo ($saved_doc_type === 'drivers_license') ? 'active' : ''; ?>" data-type="drivers_license">
                                        <?php _e("Driver's License", 'vendor-customshot'); ?>
                                    </span>
                                    <span class="doc-type-btn <?php echo ($saved_doc_type === 'national_id') ? 'active' : ''; ?>" data-type="national_id">
                                        <?php _e('National ID', 'vendor-customshot'); ?>
                                    </span>
                                </div>
                                <input type="hidden" id="kyc_document_type" name="kyc_document_type" value="<?php echo esc_attr($saved_doc_type ?: 'passport'); ?>">

                                <!-- Good / Bad Examples -->
                                <div class="kyc-examples-row">
                                    <div class="kyc-example kyc-example-good">
                                        <span class="example-badge good"><?php _e('GOOD', 'vendor-customshot'); ?></span>
                                        <div class="example-img-placeholder">
                                            <svg width="120" height="80" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="10" y="10" width="100" height="60" rx="6" stroke="#00C4B4" stroke-width="2" fill="#1a2332"/>
                                                <rect x="18" y="22" width="30" height="38" rx="2" fill="#2a3a4a"/>
                                                <rect x="56" y="25" width="44" height="6" rx="2" fill="#2a3a4a"/>
                                                <rect x="56" y="37" width="36" height="6" rx="2" fill="#2a3a4a"/>
                                                <rect x="56" y="49" width="28" height="6" rx="2" fill="#2a3a4a"/>
                                            </svg>
                                        </div>
                                        <p class="example-caption"><?php _e('All 4 corners visible. Text is clear and readable.', 'vendor-customshot'); ?></p>
                                    </div>
                                    <div class="kyc-example kyc-example-bad">
                                        <span class="example-badge bad"><?php _e('BAD', 'vendor-customshot'); ?></span>
                                        <div class="example-img-placeholder">
                                            <svg width="120" height="80" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="10" y="10" width="100" height="60" rx="6" stroke="#e74c3c" stroke-width="2" fill="#2a1a1a" opacity="0.6"/>
                                                <rect x="18" y="22" width="30" height="38" rx="2" fill="#3a2a2a" opacity="0.5"/>
                                                <rect x="56" y="25" width="44" height="6" rx="2" fill="#3a2a2a" opacity="0.4"/>
                                                <rect x="56" y="37" width="36" height="6" rx="2" fill="#3a2a2a" opacity="0.3"/>
                                                <line x1="0" y1="80" x2="120" y2="0" stroke="#e74c3c" stroke-width="1" opacity="0.3"/>
                                            </svg>
                                        </div>
                                        <p class="example-caption"><?php _e('Blurry, glare, or cut-off corners.', 'vendor-customshot'); ?></p>
                                    </div>
                                </div>

                                <!-- Dropzone for ID Document -->
                                <div class="kyc-dropzone" id="kyc-doc-dropzone">
                                    <div class="kyc-dropzone-content" id="kyc-doc-dropzone-content">
                                        <div class="kyc-upload-icon">
                                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M16 32L24 24L32 32" stroke="#00C4B4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M24 24V42" stroke="#00C4B4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M40.76 36.76C42.7 35.71 44.22 34.04 45.09 32.01C45.96 29.98 46.16 27.72 45.66 25.56C45.16 23.4 43.98 21.47 42.31 20.05C40.64 18.63 38.56 17.81 36.4 17.8H33.84C33.26 15.45 32.17 13.26 30.63 11.39C29.1 9.53 27.16 8.02 24.97 6.99C22.78 5.95 20.38 5.43 17.97 5.43C15.55 5.44 13.16 5.97 10.97 6.99C8.79 8.02 6.86 9.54 5.33 11.42C3.8 13.3 2.69 15.49 2.09 17.86C1.49 20.22 1.42 22.69 1.88 25.08C2.35 27.48 3.34 29.74 4.79 31.72" stroke="#00C4B4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <p class="kyc-dropzone-text"><?php _e('Drop your file here or click to upload', 'vendor-customshot'); ?></p>
                                        <p class="kyc-file-hint"><?php _e('JPG or PNG, max 5MB', 'vendor-customshot'); ?></p>
                                    </div>
                                    <div class="kyc-file-preview" id="kyc-doc-preview" style="display:none;">
                                        <!-- Image Thumbnail Preview -->
                                        <div class="kyc-doc-image-preview">
                                            <img id="kyc-doc-preview-img" src="" alt="<?php esc_attr_e('Document Preview', 'vendor-customshot'); ?>">
                                        </div>
                                        <div class="kyc-preview-details">
                                            <div class="kyc-preview-info">
                                                <span class="kyc-preview-name" id="kyc-doc-preview-name"></span>
                                                <span class="kyc-preview-status"><?php _e('File selected', 'vendor-customshot'); ?></span>
                                            </div>
                                            <div class="kyc-preview-actions">
                                                <span class="kyc-btn-icon kyc-crop-btn" id="kyc-doc-crop-btn" title="<?php esc_attr_e('Crop Image', 'vendor-customshot'); ?>">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M6 2V6M6 6H2M6 6V18H18V14M18 22V18M18 18H22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                                <span class="kyc-remove-file" id="kyc-doc-remove">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" name="kyc_document" id="kyc-doc-input" accept="image/jpeg,image/png" style="display:none;">
                                </div>
                            </div>

                            <div class="kyc-step-actions">
                                <button type="button" class="kyc-btn kyc-btn-outline kyc-btn-back" data-back="1"><?php _e('Back', 'vendor-customshot'); ?></button>
                                <button type="button" class="kyc-btn kyc-btn-primary kyc-btn-next" data-next="3">
                                    <?php _e('Continue', 'vendor-customshot'); ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- ============================== -->
                        <!-- STEP 3: Liveness Check         -->
                        <!-- ============================== -->
                        <div class="kyc-form-step" id="kyc-step-3" style="display:none;">
                            <div class="kyc-step-content">
                                <div class="kyc-step-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 3H5C3.89543 3 3 3.89543 3 5V9" stroke="#00C4B4" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M15 3H19C20.1046 3 21 3.89543 21 5V9" stroke="#00C4B4" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M9 21H5C3.89543 21 3 20.1046 3 19V15" stroke="#00C4B4" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M15 21H19C20.1046 21 21 20.1046 21 19V15" stroke="#00C4B4" stroke-width="2" stroke-linecap="round"/>
                                        <circle cx="12" cy="10" r="3" stroke="#00C4B4" stroke-width="2"/>
                                        <path d="M7 18C7 15.2386 9.23858 13 12 13C14.7614 13 17 15.2386 17 18" stroke="#00C4B4" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <h3><?php _e('Liveness Check', 'vendor-customshot'); ?></h3>
                                </div>

                                <!-- Strict Requirements -->
                                <div class="kyc-requirements-box">
                                    <div class="kyc-req-header">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#00C4B4" stroke-width="2"/>
                                            <path d="M9 12L11 14L15 10" stroke="#00C4B4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <strong><?php _e('Strict Requirement', 'vendor-customshot'); ?></strong>
                                    </div>
                                    <p><?php _e('To prevent identity theft, you must take a selfie holding:', 'vendor-customshot'); ?></p>
                                    <ul>
                                        <li><?php _e('Your ID document', 'vendor-customshot'); ?></li>
                                        <li><?php printf(__('A handwritten note saying "Modfolios" and "%s"', 'vendor-customshot'), date_i18n('M j, Y')); ?></li>
                                    </ul>
                                </div>

                                <!-- Selfie Upload / Camera Area -->
                                <div class="kyc-selfie-area">
                                    <!-- Camera Placeholder / Upload -->
                                    <div class="kyc-selfie-preview-box" id="kyc-selfie-preview-box">
                                        <div class="kyc-selfie-placeholder" id="kyc-selfie-placeholder">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M23 19C23 19.5304 22.7893 20.0391 22.4142 20.4142C22.0391 20.7893 21.5304 21 21 21H3C2.46957 21 1.96086 20.7893 1.58579 20.4142C1.21071 20.0391 1 19.5304 1 19V8C1 7.46957 1.21071 6.96086 1.58579 6.58579C1.96086 6.21071 2.46957 6 3 6H7L9 3H15L17 6H21C21.5304 6 22.0391 6.21071 22.4142 6.58579C22.7893 6.96086 23 7.46957 23 8V19Z" stroke="#556677" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M12 17C14.2091 17 16 15.2091 16 13C16 10.7909 14.2091 9 12 9C9.79086 9 8 10.7909 8 13C8 15.2091 9.79086 17 12 17Z" stroke="#556677" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <p><?php _e('Tap to Open Camera', 'vendor-customshot'); ?></p>
                                            <span class="kyc-selfie-hint"><?php _e('Ensure good lighting. No filters.', 'vendor-customshot'); ?></span>
                                        </div>
                                        <!-- Camera Video (hidden by default) -->
                                        <video id="kyc-camera-video" autoplay playsinline style="display:none;"></video>
                                        <!-- Captured / Uploaded Image Preview -->
                                        <img id="kyc-selfie-captured" src="" alt="" style="display:none;">
                                    </div>

                                    <div class="kyc-selfie-controls">
                                        <button type="button" class="kyc-btn kyc-btn-camera" id="kyc-open-camera">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23 19C23 19.5304 22.7893 20.0391 22.4142 20.4142C22.0391 20.7893 21.5304 21 21 21H3C2.46957 21 1.96086 20.7893 1.58579 20.4142C1.21071 20.0391 1 19.5304 1 19V8C1 7.46957 1.21071 6.96086 1.58579 6.58579C1.96086 6.21071 2.46957 6 3 6H7L9 3H15L17 6H21C21.5304 6 22.0391 6.21071 22.4142 6.58579C22.7893 6.96086 23 7.46957 23 8V19Z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="2"/></svg>
                                            <?php _e('Open Camera', 'vendor-customshot'); ?>
                                        </button>
                                        <button type="button" class="kyc-btn kyc-btn-capture" id="kyc-capture-btn" style="display:none;">
                                            <?php _e('Capture Photo', 'vendor-customshot'); ?>
                                        </button>
                                        <button type="button" class="kyc-btn kyc-btn-outline kyc-btn-retake" id="kyc-retake-btn" style="display:none;">
                                            <?php _e('Retake', 'vendor-customshot'); ?>
                                        </button>
                                        <span class="kyc-selfie-or"><?php _e('or', 'vendor-customshot'); ?></span>
                                        <button type="button" class="kyc-btn kyc-btn-outline" id="kyc-selfie-upload-btn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 8L12 3L7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 3V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            <?php _e('Upload Photo', 'vendor-customshot'); ?>
                                        </button>
                                    </div>
                                    <input type="file" name="kyc_selfie" id="kyc-selfie-input" accept="image/jpeg,image/png" style="display:none;">
                                    <canvas id="kyc-camera-canvas" style="display:none;"></canvas>
                                </div>
                            </div>

                            <div class="kyc-step-actions">
                                <button type="button" class="kyc-btn kyc-btn-outline kyc-btn-back" data-back="2"><?php _e('Back', 'vendor-customshot'); ?></button>
                                <button type="submit" class="kyc-btn kyc-btn-primary" id="kyc-submit-btn"><?php _e('Submit Verification', 'vendor-customshot'); ?></button>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div class="kyc-messages" id="kyc-messages"></div>
                    </form>

                    <!-- Crop Modal -->
                    <div class="kyc-crop-modal-overlay" id="kyc-crop-modal" style="display:none;">
                        <div class="kyc-crop-modal">
                            <div class="kyc-crop-modal-header">
                                <h3><?php _e('Crop Document Image', 'vendor-customshot'); ?></h3>
                                <span class="kyc-crop-modal-close" id="kyc-crop-close">&times;</span>
                            </div>
                            <div class="kyc-crop-modal-body">
                                <div class="kyc-crop-container">
                                    <img id="kyc-crop-image" src="" alt="">
                                </div>
                            </div>
                            <div class="kyc-crop-modal-footer">
                                <div class="kyc-crop-toolbar">
                                    <span class="kyc-btn-icon" id="kyc-crop-rotate-left" title="<?php esc_attr_e('Rotate Left', 'vendor-customshot'); ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M1 4V10H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.51 15C4.15 17.02 5.43 18.77 7.16 19.97C8.89 21.18 10.97 21.76 13.07 21.64C15.17 21.52 17.17 20.69 18.74 19.3C20.31 17.91 21.37 16.03 21.74 13.97C22.12 11.91 21.79 9.78 20.82 7.93C19.85 6.08 18.29 4.61 16.4 3.76C14.51 2.91 12.38 2.73 10.38 3.24C8.37 3.76 6.61 4.94 5.34 6.58L1 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="kyc-btn-icon" id="kyc-crop-rotate-right" title="<?php esc_attr_e('Rotate Right', 'vendor-customshot'); ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M23 4V10H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.49 15C19.85 17.02 18.57 18.77 16.84 19.97C15.11 21.18 13.03 21.76 10.93 21.64C8.83 21.52 6.83 20.69 5.26 19.3C3.69 17.91 2.63 16.03 2.26 13.97C1.88 11.91 2.21 9.78 3.18 7.93C4.15 6.08 5.71 4.61 7.6 3.76C9.49 2.91 11.62 2.73 13.62 3.24C15.63 3.76 17.39 4.94 18.66 6.58L23 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="kyc-btn-icon" id="kyc-crop-zoom-in" title="<?php esc_attr_e('Zoom In', 'vendor-customshot'); ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21L16.65 16.65M11 8V14M8 11H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="kyc-btn-icon" id="kyc-crop-zoom-out" title="<?php esc_attr_e('Zoom Out', 'vendor-customshot'); ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21L16.65 16.65M8 11H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="kyc-btn-icon" id="kyc-crop-reset" title="<?php esc_attr_e('Reset', 'vendor-customshot'); ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12Z" stroke="currentColor" stroke-width="2"/><path d="M9 12L15 12M12 9L12 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    </span>
                                </div>
                                <div class="kyc-crop-actions">
                                    <button type="button" class="kyc-btn kyc-btn-outline" id="kyc-crop-cancel"><?php _e('Cancel', 'vendor-customshot'); ?></button>
                                    <button type="button" class="kyc-btn kyc-btn-primary" id="kyc-crop-apply"><?php _e('Apply Crop', 'vendor-customshot'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">

<style>
/* KYC Vendor Page Styles */
.wcfm_kyc_container {}

/* Status Banner */
.kyc-status-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 15px;
    margin-bottom: 30px;
    font-weight: 500;
}
.kyc-status-approved { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.kyc-status-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
.kyc-status-rejected { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.kyc-status-icon { display: flex; align-items: center; }
.kyc-status-text-wrapper { display: flex; flex-direction: column; gap: 4px; }
.kyc-rejection-reason { font-size: 13px; font-style: italic; }

/* Verified Section */
.kyc-verified-section { background: #fff; border-radius: 16px; padding: 60px 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); text-align: center; }
.kyc-verified-content { max-width: 450px; margin: 0 auto; }
.kyc-verified-icon { margin-bottom: 24px; }
.kyc-verified-title { font-size: 28px; font-weight: 700; color: #1a1a1a; margin: 0 0 16px 0; }
.kyc-verified-message { font-size: 16px; color: #666; line-height: 1.6; margin: 0 0 32px 0; }
.kyc-verified-document { display: inline-block; }
.kyc-view-document-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 16px; font-size: 14px; font-weight: 500; text-decoration: none; transition: all 0.2s; background: #00C4B4; border-color: #00C4B4; color: #fff; }
.kyc-view-document-btn:hover { background: #00A89A; color: #fff; }

/* Multi-Step Wrapper */
.kyc-multistep-wrapper {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    color: #333;
    box-shadow: 0 0 6px rgba(0,0,0,0.1);
    margin: 3px;
}

/* Header */
.kyc-multistep-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.kyc-multistep-title {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0;
}
.kyc-step-counter {
    font-size: 13px;
    color: #8b949e;
}

/* Progress Bar */
.kyc-progress-bar {
    width: 100%;
    height: 4px;
    background: #e7e7e7;
    border-radius: 4px;
    margin-bottom: 12px;
    overflow: hidden;
}
.kyc-progress-fill {
    height: 100%;
    background: #00C4B4;
    border-radius: 4px;
    transition: width 0.4s ease;
}

/* Step Labels */
.kyc-step-labels {
    display: flex;
    justify-content: space-between;
    margin-bottom: 32px;
}
.kyc-step-label {
    font-size: 13px;
    color: #484f58;
    transition: color 0.3s;
}
.kyc-step-label.active {
    color: #00C4B4;
}
.kyc-step-label.completed {
    color: #8b949e;
}

/* Form Step */
.kyc-form-step {}
.kyc-step-content {
    margin-bottom: 30px;
}

/* Step Icon + Title */
.kyc-step-icon {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.kyc-step-icon h3 {
    font-size: 18px;
    font-weight: 600;
    color: #000;
    margin: 0;
}
.kyc-step-desc {
    font-size: 14px;
    color: #8b949e;
    margin: 0 0 24px 0;
    line-height: 1.5;
}

/* Form Fields */
.kyc-field-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}
.kyc-multistep-wrapper .figma-input-field {
    flex: 1;
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1);
    min-height: 57px;
}
.kyc-multistep-wrapper .figma-input-field .field-label {
    font-size: 13px;
    font-weight: 500;
    color: #1a1a1a;
    padding: 10px 14px;
    white-space: nowrap;
    min-width: fit-content;
}
.kyc-multistep-wrapper .figma-input-field .field-separator {
    width: 1px;
    height: 24px;
    background: #e0e0e0;
    flex-shrink: 0;
}
.kyc-multistep-wrapper .figma-input-field input,
.kyc-multistep-wrapper .figma-input-field select {
    flex: 1;
    border: none !important;
    background: transparent !important;
    padding: 10px 12px !important;
    font-size: 13px;
    color: #333 !important;
    min-width: 0;
    outline: none !important;
    box-shadow: none !important;
    border: none !important;
}
#wcfm-main-contentainer .figma-input-field input,
#wcfm-main-contentainer .figma-input-field select{
    border: none !important;
}
.kyc-multistep-wrapper .figma-input-field input::placeholder {
    color: #999;
}
.kyc-multistep-wrapper .figma-input-field input:focus,
.kyc-multistep-wrapper .figma-input-field select:focus {
    outline: none;
    box-shadow: none !important;
}
.kyc-multistep-wrapper .figma-input-field select {
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 35px !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23999' d='M6 8L1 3h10z'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
}
.kyc-multistep-wrapper .figma-input-field select option {
    background: #fff;
    color: #333;
}
.kyc-field-hint {
    display: block;
    font-size: 11px;
    color: #8b949e;
    margin-top: 4px;
    margin-bottom: 8px;
}

/* Document Type Toggle */
.kyc-doc-type-toggle {
    display: flex;
    gap: 10px;
    margin-bottom: 24px;
}
.doc-type-btn {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #ccc;
    border-radius: 10px;
    color: #8b949e;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.doc-type-btn:hover {
    border-color: #00C4B4;
    color: #00C4B4;
}
.doc-type-btn.active {
    border-color: #00C4B4;
    background: rgba(0, 196, 180, 0.1);
    color: #00C4B4;
}

/* Good / Bad Examples */
.kyc-examples-row {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
}
.kyc-example {
    flex: 1;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    border: 1px solid #30363d;
}
.kyc-example-good { border-color: #238636; }
.kyc-example-bad { border-color: #da3633; }

.example-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}
.example-badge.good { background: rgba(35, 134, 54, 0.2); color: #3fb950; }
.example-badge.bad { background: rgba(218, 54, 51, 0.2); color: #f85149; }
.example-img-placeholder { margin-bottom: 8px; }
.example-caption { font-size: 12px; color: #8b949e; margin: 0; line-height: 1.4; }

/* Dropzone */
.kyc-multistep-wrapper .kyc-dropzone {
    border: 2px dashed #bababa;
    border-radius: 14px;
    padding: 40px 20px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}
.kyc-multistep-wrapper .kyc-dropzone:hover,
.kyc-multistep-wrapper .kyc-dropzone.dragover {
    border-color: #00C4B4;
    background: rgba(0, 196, 180, 0.05);
}
.kyc-multistep-wrapper .kyc-dropzone-text {
    font-size: 16px;
    color: #bababa;
    margin: 0 0 4px 0;
    font-weight: 600;
}
.kyc-multistep-wrapper .kyc-file-hint {
    font-size: 13px;
    color: #484f58;
    margin: 0;
}

/* File Preview */
.kyc-multistep-wrapper .kyc-file-preview {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    justify-content: center;
}
.kyc-preview-check { flex-shrink: 0; }
.kyc-preview-info { display: flex; flex-direction: column; gap: 2px; }
.kyc-preview-info .kyc-preview-name { font-size: 14px; font-weight: 500; color: #333; word-break: break-all; }
.kyc-preview-info .kyc-preview-status { font-size: 12px; color: #00C4B4; }
.kyc-multistep-wrapper .kyc-remove-file {
    background: #21262d;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #8b949e;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.kyc-multistep-wrapper .kyc-remove-file:hover {
    background: rgba(218, 54, 51, 0.2);
    color: #f85149;
}

/* Requirements Box */
.kyc-requirements-box {
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 35px;
    box-shadow: 0 0 6px rgba(0,0,0,0.1);
    margin-top: 30px;
}
.kyc-req-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}
.kyc-req-header strong {
    color: #000;
    font-size: 14px;
}
.kyc-requirements-box p {
    color: #3c3c3c;
    font-size: 13px;
    margin: 0 0 10px 0;
    line-height: 1.5;
}
.kyc-requirements-box ul {
    margin: 0;
    padding-left: 20px;
}
.kyc-requirements-box ul li {
    color: #3c3c3c;
    font-size: 13px;
    margin-bottom: 4px;
}

/* Selfie Area */
.kyc-selfie-area {
    text-align: center;
}
.kyc-selfie-preview-box {
    width: 100%;
    max-width: 400px;
    margin: 0 auto 20px;
    border-radius: 16px;
    overflow: hidden;
    border: 2px solid #e7e7e7;
    aspect-ratio: 4/3;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.kyc-selfie-placeholder {
    text-align: center;
    color: #484f58;
    cursor: pointer;
}
.kyc-selfie-placeholder p {
    font-size: 14px;
    margin: 12px 0 4px;
    color: #8b949e;
}
.kyc-selfie-hint {
    font-size: 11px;
    color: #484f58;
}
#kyc-camera-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 14px;
}
#kyc-selfie-captured {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 14px;
}

/* Selfie Controls */
.kyc-selfie-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}
.kyc-selfie-or {
    color: #484f58;
    font-size: 13px;
}

/* Buttons */
.kyc-btn {
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.kyc-btn-primary {
    background: #00C4B4;
    color: #fff;
}
.kyc-btn-primary:hover {
    background: #00A89A;
}
button:not(.mce-container button).kyc-btn-outline {
    background: transparent !important;
    color: #30363d !important;
    border: 1px solid #30363d !important;
    text-shadow: none !important;
}
button:not(.mce-container button).kyc-btn-outline:hover {
    border-color: #8b949e !important;
    color: #8b949e !important;
    box-shadow: none;
}
button:not(.mce-container button).kyc-btn-outline:hover{
    box-shadow: none !important;
}
.kyc-btn-camera {
    background: #238636;
    color: #fff;
}
.kyc-btn-camera:hover {
    background: #2ea043;
}
.kyc-btn-capture {
    background: #00C4B4;
    color: #fff;
    padding: 12px 32px;
}
.kyc-btn-capture:hover {
    background: #00A89A;
}
button:not(.mce-container button).kyc-btn-primary:disabled {
    background: #21262d !important;
    color: #484f58 !important;
    cursor: not-allowed;
}

/* Step Actions */
.kyc-step-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #e7e7e7;
}
.kyc-step-actions-right {
    justify-content: flex-end;
}

/* Messages */
.kyc-messages { margin-top: 16px; }
.kyc-message { padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-top: 12px; }
.kyc-message-success { background-color: rgba(35, 134, 54, 0.2); color: #3fb950; border: 1px solid #238636; }
.kyc-message-error { background-color: rgba(218, 54, 51, 0.2); color: #f85149; border: 1px solid #da3633; }

/* Document Image Preview */
.kyc-doc-image-preview {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    border: 2px solid #e7e7e7;
    background: #f5f5f5;
}
.kyc-doc-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.kyc-preview-details {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-width: 0;
}
.kyc-preview-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.kyc-btn-icon {
    background: #f0f0f0;
    border: none;
    border-radius: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
}
.kyc-btn-icon:hover {
    background: rgba(0, 196, 180, 0.15);
    color: #00C4B4;
}
.kyc-crop-btn {
    background: rgba(0, 196, 180, 0.1);
    color: #00C4B4;
}
.kyc-crop-btn:hover {
    background: rgba(0, 196, 180, 0.25);
}

/* Crop Modal */
.kyc-crop-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.kyc-crop-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    border: 1px solid #e7e7e7;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.2);
}
.kyc-crop-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e7e7e7;
}
.kyc-crop-modal-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
}
.kyc-crop-modal-close {
    font-size: 24px;
    color: #8b949e;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
}
.kyc-crop-modal-close:hover {
    color: #f85149;
}
.kyc-crop-modal-body {
    flex: 1;
    overflow: hidden;
    padding: 20px;
    min-height: 0;
}
.kyc-crop-container {
    width: 100%;
    max-height: 450px;
    background: #f5f5f5;
    border-radius: 10px;
    overflow: hidden;
}
.kyc-crop-container img {
    display: block;
    max-width: 100%;
}
.kyc-crop-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-top: 1px solid #e7e7e7;
    gap: 12px;
}
.kyc-crop-toolbar {
    display: flex;
    gap: 6px;
}
.kyc-crop-actions {
    display: flex;
    gap: 10px;
}

/* Loading State */
.kyc-btn-primary.loading { pointer-events: none; opacity: 0.7; }
.kyc-btn-primary.loading::after {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-left: 8px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: kyc-spin 0.8s linear infinite;
    vertical-align: middle;
}
@keyframes kyc-spin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 768px) {
    .kyc-multistep-wrapper { padding: 24px 16px; }
    .kyc-field-row { flex-direction: column; gap: 12px; }
    .kyc-doc-type-toggle { flex-direction: column; }
    .kyc-examples-row { flex-direction: column; }
    .kyc-step-actions { flex-direction: column-reverse; gap: 12px; }
    .kyc-step-actions .kyc-btn { width: 100%; justify-content: center; }
    .kyc-selfie-controls { flex-direction: column; }
    .kyc-crop-modal { max-width: 100%; }
    .kyc-crop-modal-footer { flex-direction: column; }
    .kyc-crop-toolbar { justify-content: center; }
    .kyc-crop-actions { width: 100%; justify-content: stretch; }
    .kyc-crop-actions .kyc-btn { flex: 1; justify-content: center; }
    .kyc-doc-image-preview { width: 60px; height: 60px; }
}
</style>

<!-- Cropper.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize LightGallery for verified document view
    var lgSettings = { selector: 'a', download: true, counter: false, getCaptionFromTitleOrAlt: false, subHtmlSelectorRelative: true };
    var settings = (typeof window.minimog !== 'undefined' && window.minimog.LightGallery) ? window.minimog.LightGallery : lgSettings;
    if ($.fn.lightGallery) {
        $('.kyc-verified-document.minimog-light-gallery').each(function() { $(this).lightGallery(settings); });
    }

    var currentStep = 1;
    var cameraStream = null;
    var capturedBlob = null;
    var croppedDocBlob = null; // Stores cropped document image
    var cropperInstance = null; // Cropper.js instance
    var originalDocDataUrl = null; // Original uploaded image data URL for cropper
    var ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';
    var $messages = $('#kyc-messages');

    // ==================
    // Step Navigation
    // ==================
    function goToStep(step) {
        $messages.empty();
        $('.kyc-form-step').hide();
        $('#kyc-step-' + step).show();
        currentStep = step;
        updateProgress();
    }

    function updateProgress() {
        var pct = (currentStep / 3) * 100;
        $('#kyc-progress-fill').css('width', pct + '%');
        $('#kyc-current-step-num').text(currentStep);
        $('.kyc-step-label').each(function() {
            var s = parseInt($(this).data('step'));
            $(this).toggleClass('active', s === currentStep);
            $(this).toggleClass('completed', s < currentStep);
        });
    }

    // Validate Step 1
    function validateStep1() {
        var fn = $('#kyc_first_name').val().trim();
        var ln = $('#kyc_last_name').val().trim();
        var dob = $('#kyc_dob').val();
        var country = $('#kyc_country').val();
        if (!fn || !ln || !dob || !country) {
            showMessage('<?php echo esc_js(__("Please fill in all fields.", "vendor-customshot")); ?>', 'error');
            return false;
        }
        // Age check (18+)
        var birthDate = new Date(dob);
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        if (age < 18) {
            showMessage('<?php echo esc_js(__("You must be at least 18 years old.", "vendor-customshot")); ?>', 'error');
            return false;
        }
        return true;
    }

    // Validate Step 2
    function validateStep2() {
        var docInput = $('#kyc-doc-input')[0];
        if (!docInput.files || !docInput.files[0]) {
            showMessage('<?php echo esc_js(__("Please upload your ID document.", "vendor-customshot")); ?>', 'error');
            return false;
        }
        return true;
    }

    // Next button
    $(document).on('click', '.kyc-btn-next', function() {
        var next = parseInt($(this).data('next'));
        if (currentStep === 1 && !validateStep1()) return;
        if (currentStep === 2 && !validateStep2()) return;
        goToStep(next);
    });

    // Back button
    $(document).on('click', '.kyc-btn-back', function() {
        goToStep(parseInt($(this).data('back')));
    });

    // ==================
    // Document Type Toggle
    // ==================
    $(document).on('click', '.doc-type-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.doc-type-btn').removeClass('active');
        $(this).addClass('active');
        $('#kyc_document_type').val($(this).data('type'));
    });

    // ==================
    // ID Document Dropzone
    // ==================
    function setupDropzone(dropzoneId, inputId, previewId, contentId, previewNameId, removeId) {
        var $dz = $(dropzoneId);
        var $input = $(inputId);
        var $preview = $(previewId);
        var $content = $(contentId);
        var $name = $(previewNameId);

        $dz.on('click', function(e) {
            if (!$(e.target).closest(removeId).length) $input[0].click();
        });

        $dz.on('dragover dragenter', function(e) { e.preventDefault(); e.stopPropagation(); $(this).addClass('dragover'); });
        $dz.on('dragleave drop', function(e) { e.preventDefault(); e.stopPropagation(); $(this).removeClass('dragover'); });
        $dz.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) processDropzoneFile(files[0], $input, $preview, $content, $name);
        });

        $input.on('change', function() {
            if (this.files && this.files[0]) processDropzoneFile(this.files[0], $input, $preview, $content, $name);
        });

        $(removeId).on('click', function(e) {
            e.stopPropagation();
            $input.val('');
            $preview.hide();
            $content.show();
            // Clear preview image and cropped data
            $('#kyc-doc-preview-img').attr('src', '');
            croppedDocBlob = null;
            originalDocDataUrl = null;
        });
    }

    function processDropzoneFile(file, $input, $preview, $content, $name) {
        if (file.size > 5 * 1024 * 1024) {
            showMessage('<?php echo esc_js(__("File is too large. Maximum size is 5MB.", "vendor-customshot")); ?>', 'error');
            return;
        }
        var allowed = ['image/jpeg', 'image/png'];
        if (allowed.indexOf(file.type) === -1) {
            showMessage('<?php echo esc_js(__("Please upload a JPG or PNG image.", "vendor-customshot")); ?>', 'error');
            return;
        }
        $content.hide();
        $name.text(file.name);
        $preview.show();
        $messages.empty();

        // Reset cropped blob when new file is selected
        croppedDocBlob = null;

        // Generate image preview
        var reader = new FileReader();
        reader.onload = function(e) {
            originalDocDataUrl = e.target.result;
            $('#kyc-doc-preview-img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    }

    setupDropzone('#kyc-doc-dropzone', '#kyc-doc-input', '#kyc-doc-preview', '#kyc-doc-dropzone-content', '#kyc-doc-preview-name', '#kyc-doc-remove');

    // ==================
    // Selfie - Camera
    // ==================
    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function(t) { t.stop(); });
            cameraStream = null;
        }
    }

    // Open Camera
    $('#kyc-open-camera, #kyc-selfie-placeholder').on('click', function() {
        if (cameraStream) return; // already open
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showMessage('<?php echo esc_js(__("Camera not supported. Please upload a photo instead.", "vendor-customshot")); ?>', 'error');
            return;
        }
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } } })
            .then(function(stream) {
                cameraStream = stream;
                var video = $('#kyc-camera-video')[0];
                video.srcObject = stream;
                video.style.display = 'block';
                $('#kyc-selfie-placeholder').hide();
                $('#kyc-selfie-captured').hide();
                $('#kyc-open-camera').hide();
                $('#kyc-capture-btn').show();
                $('#kyc-retake-btn').hide();
            })
            .catch(function() {
                showMessage('<?php echo esc_js(__("Camera access denied. Please use upload instead.", "vendor-customshot")); ?>', 'error');
            });
    });

    // Capture Photo
    $('#kyc-capture-btn').on('click', function() {
        var video = $('#kyc-camera-video')[0];
        var canvas = $('#kyc-camera-canvas')[0];
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        stopCamera();

        canvas.toBlob(function(blob) {
            capturedBlob = blob;
            var url = URL.createObjectURL(blob);
            $('#kyc-selfie-captured').attr('src', url).show();
            $('#kyc-camera-video').hide();
            $('#kyc-capture-btn').hide();
            $('#kyc-retake-btn').show();
        }, 'image/jpeg', 0.9);
    });

    // Retake
    $('#kyc-retake-btn').on('click', function() {
        capturedBlob = null;
        $('#kyc-selfie-captured').hide();
        $('#kyc-retake-btn').hide();
        // Re-open camera
        $('#kyc-open-camera').trigger('click');
    });

    // Upload Photo button
    $('#kyc-selfie-upload-btn').on('click', function() {
        $('#kyc-selfie-input')[0].click();
    });

    // Selfie file input change
    $('#kyc-selfie-input').on('change', function() {
        if (this.files && this.files[0]) {
            var file = this.files[0];
            if (file.size > 5 * 1024 * 1024) {
                showMessage('<?php echo esc_js(__("File is too large. Maximum size is 5MB.", "vendor-customshot")); ?>', 'error');
                $(this).val('');
                return;
            }
            var allowed = ['image/jpeg', 'image/png'];
            if (allowed.indexOf(file.type) === -1) {
                showMessage('<?php echo esc_js(__("Please upload a JPG or PNG image.", "vendor-customshot")); ?>', 'error');
                $(this).val('');
                return;
            }
            stopCamera();
            capturedBlob = null;
            var url = URL.createObjectURL(file);
            $('#kyc-selfie-captured').attr('src', url).show();
            $('#kyc-camera-video').hide();
            $('#kyc-selfie-placeholder').hide();
            $('#kyc-capture-btn').hide();
            $('#kyc-open-camera').show();
            $('#kyc-retake-btn').show();
            $messages.empty();
        }
    });

    // ==================
    // Document Crop
    // ==================
    function openCropModal() {
        if (!originalDocDataUrl) return;
        $('#kyc-crop-image').attr('src', originalDocDataUrl);
        $('#kyc-crop-modal').fadeIn(200);

        // Destroy existing cropper if any
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }

        // Initialize Cropper.js after image loads
        var cropImg = document.getElementById('kyc-crop-image');
        var cropperOptions = {
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.9,
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: true,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            background: true
        };

        function initCropper() {
            if (cropperInstance) return; // Already initialized
            cropperInstance = new Cropper(cropImg, cropperOptions);
        }

        // If image is already cached, init immediately; otherwise wait for load
        if (cropImg.complete && cropImg.naturalWidth > 0) {
            initCropper();
        } else {
            cropImg.onload = initCropper;
        }
    }

    function closeCropModal() {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        $('#kyc-crop-modal').fadeOut(200);
    }

    // Open crop modal on crop button click
    $('#kyc-doc-crop-btn').on('click', function(e) {
        e.stopPropagation();
        openCropModal();
    });

    // Close crop modal
    $('#kyc-crop-close, #kyc-crop-cancel').on('click', function() {
        closeCropModal();
    });

    // Close on overlay click
    $('#kyc-crop-modal').on('click', function(e) {
        if ($(e.target).is('#kyc-crop-modal')) closeCropModal();
    });

    // Crop toolbar buttons
    $('#kyc-crop-rotate-left').on('click', function() {
        if (cropperInstance) cropperInstance.rotate(-90);
    });
    $('#kyc-crop-rotate-right').on('click', function() {
        if (cropperInstance) cropperInstance.rotate(90);
    });
    $('#kyc-crop-zoom-in').on('click', function() {
        if (cropperInstance) cropperInstance.zoom(0.1);
    });
    $('#kyc-crop-zoom-out').on('click', function() {
        if (cropperInstance) cropperInstance.zoom(-0.1);
    });
    $('#kyc-crop-reset').on('click', function() {
        if (cropperInstance) cropperInstance.reset();
    });

    // Apply crop
    $('#kyc-crop-apply').on('click', function() {
        if (!cropperInstance) return;

        var canvas = cropperInstance.getCroppedCanvas({
            maxWidth: 2048,
            maxHeight: 2048,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        canvas.toBlob(function(blob) {
            croppedDocBlob = blob;
            // Update the preview thumbnail with cropped image
            var croppedUrl = URL.createObjectURL(blob);
            $('#kyc-doc-preview-img').attr('src', croppedUrl);
            $('#kyc-doc-preview .kyc-preview-status').text('<?php echo esc_js(__("Cropped", "vendor-customshot")); ?>');
            closeCropModal();
        }, 'image/jpeg', 0.92);
    });

    // ==================
    // Form Submit
    // ==================
    $('#kyc-multistep-form').on('submit', function(e) {
        e.preventDefault();

        // Validate selfie
        var hasSelfieFile = $('#kyc-selfie-input')[0].files && $('#kyc-selfie-input')[0].files[0];
        if (!hasSelfieFile && !capturedBlob) {
            showMessage('<?php echo esc_js(__("Please capture or upload your selfie photo.", "vendor-customshot")); ?>', 'error');
            return;
        }

        var formData = new FormData(this);
        formData.append('action', 'wcfm_kyc_upload');

        // If document was cropped, use cropped blob instead of original file
        if (croppedDocBlob) {
            formData.delete('kyc_document');
            formData.append('kyc_document', croppedDocBlob, 'document-cropped.jpg');
        }

        // If camera captured, replace the file input
        if (capturedBlob && !hasSelfieFile) {
            formData.delete('kyc_selfie');
            formData.append('kyc_selfie', capturedBlob, 'selfie-capture.jpg');
        }

        var $btn = $('#kyc-submit-btn');
        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $btn.removeClass('loading');
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    stopCamera();
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    showMessage(response.data.message || '<?php echo esc_js(__("Submission failed. Please try again.", "vendor-customshot")); ?>', 'error');
                    $btn.prop('disabled', false);
                }
            },
            error: function() {
                $btn.removeClass('loading').prop('disabled', false);
                showMessage('<?php echo esc_js(__("An error occurred. Please try again.", "vendor-customshot")); ?>', 'error');
            }
        });
    });

    function showMessage(message, type) {
        var cls = type === 'success' ? 'kyc-message-success' : 'kyc-message-error';
        $messages.html('<div class="kyc-message ' + cls + '">' + message + '</div>');
        // Scroll to message
        $('html, body').animate({ scrollTop: $messages.offset().top - 100 }, 300);
    }
});
</script>

<?php endif; ?>