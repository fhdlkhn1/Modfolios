<?php
/**
 * WCFM Helper Functions for Modfolio Theme
 * Contains reusable components for WCFM template overrides
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WCFM Template Override Filter
 * Ensures theme templates in /wcfm/ folder override plugin templates
 */
add_filter('wcfm_locate_template', 'modfolio_wcfm_template_override', 10, 4);
function modfolio_wcfm_template_override($template, $template_name, $template_path, $default_path) {
    // Check if template exists in theme's wcfm folder
    $theme_template = get_template_directory() . '/wcfm/' . $template_name;

    if (file_exists($theme_template)) {
        return $theme_template;
    }

    return $template;
}

/**
 * Render the Modfolio Dashboard Welcome Header
 *
 * @param string $current_page Current page identifier for breadcrumb
 * @param array $breadcrumb_items Optional breadcrumb items array
 */
function modfolio_wcfm_render_header( $current_page = '', $breadcrumb_items = array() ) {
    // Get current user info
    $current_user = wp_get_current_user();
    $current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    $is_admin = current_user_can('administrator') || current_user_can('shop_manager');
    $is_vendor = function_exists('wcfm_is_vendor') ? wcfm_is_vendor() : false;

    if( !$is_vendor && !$is_admin ) {
        $current_user_id = get_current_user_id();
    }

    // Get membership info from WCFM Membership
    $membership_type = __( 'Basic Plan', 'wc-frontend-manager' );
    $membership_id = get_user_meta( $current_user_id, 'wcfm_membership', true );
    if ( $membership_id ) {
        $membership_post = get_post( $membership_id );
        if ( $membership_post && $membership_post->post_title ) {
            $membership_type = $membership_post->post_title;
        }
    }

    // Get last login
    $last_login = get_user_meta( $current_user_id, 'last_login', true );
    $last_login_display = $last_login ? date_i18n( 'g:i a (F j, Y)', strtotime( $last_login ) ) : date_i18n( 'g:i a (F j, Y)' );

    // Get avatar
//     $avatar_url = get_avatar_url( $current_user_id, array( 'size' => 60 ) );

global $wpdb;

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;

// Default fallback (WordPress avatar)
$avatar_url = get_avatar_url( $user_id, [ 'size' => 60 ] );

// Check if user is WCFM vendor
$is_vendor = in_array( 'wcfm_vendor', (array) $current_user->roles, true );

if ( $is_vendor ) {

    // Get WCFM profile settings
    $profile = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );

    /**
     * 1️⃣ User avatar (highest priority)
     */
    $user_avatar_id = get_user_meta(
        $user_id,
        $wpdb->get_blog_prefix( get_current_blog_id() ) . 'user_avatar',
        true
    );

    if ( $user_avatar_id ) {
        $avatar = wp_get_attachment_image_url( intval( $user_avatar_id ), 'thumbnail' );
        if ( $avatar ) {
            $avatar_url = $avatar;
        }
    }

    /**
     * 2️⃣ WCFM store logo (gravatar field)
     */
    if ( empty( $avatar ) && ! empty( $profile['gravatar'] ) ) {
        $avatar_id = intval( $profile['gravatar'] );
        $avatar    = wp_get_attachment_image_url( $avatar_id, 'thumbnail' );

        if ( $avatar ) {
            $avatar_url = $avatar;
        }
    }
}




    // Get WCFM URLs
    $wcfm_url = function_exists('get_wcfm_url') ? get_wcfm_url() : home_url();
//     $membership_url = function_exists('get_wcfm_membership_url') ? get_wcfm_membership_url() : '#';
	$membership_url = home_url('/pricing/');
    ?>

    <!-- Breadcrumb -->
    <div class="modfolio-breadcrumb">
        <a href="<?php echo esc_url( $wcfm_url ); ?>"><?php _e( 'Home', 'wc-frontend-manager' ); ?></a>
        <span class="separator">></span>
        <a href="<?php echo esc_url( $wcfm_url ); ?>"><?php _e( 'Dashboard', 'wc-frontend-manager' ); ?></a>
        <?php
        if ( ! empty( $breadcrumb_items ) ) {
            foreach ( $breadcrumb_items as $item ) {
                echo '<span class="separator">></span>';
                if ( isset( $item['url'] ) && $item['url'] ) {
                    echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
                } else {
                    echo '<span class="current">' . esc_html( $item['label'] ) . '</span>';
                }
            }
        } elseif ( $current_page ) {
            echo '<span class="separator">></span>';
            echo '<span class="current">' . esc_html( $current_page ) . '</span>';
        }
        ?>
    </div>

    <!-- Welcome Header Section -->
    <div class="modfolio-welcome-header">
        <div class="welcome-left">
            <div class="welcome-avatar">
                <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>">
            </div>
            <div class="welcome-info">
                <span class="welcome-label"><?php _e( 'WELCOME TO THE MODFOLIOS DASHBOARD', 'wc-frontend-manager' ); ?></span>
                <h2 class="welcome-name"><?php echo esc_html( $current_user->display_name ); ?></h2>
            </div>
        </div>
        <div class="welcome-right">
            <a href="<?php echo esc_url( $membership_url ); ?>" class="membership-badge">
                <span><?php _e( 'Membership:', 'wc-frontend-manager' ); ?></span>
                <strong><?php echo esc_html( $membership_type ); ?></strong>
            </a>
            <div class="last-login">
                <span class="login-label"><?php _e( 'Last Login:', 'wc-frontend-manager' ); ?></span>
                <span class="login-time"><?php echo esc_html( $last_login_display ); ?></span>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get Modfolio header CSS styles
 * Can be included in any WCFM template
 */
function modfolio_wcfm_get_header_styles() {
    ob_start();
    ?>
    /* Modfolio Dashboard Header Styles */
    .modfolio-breadcrumb {
        padding: 15px 0;
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
    }

    .modfolio-breadcrumb a {
        color: #666;
        text-decoration: none;
    }

    .modfolio-breadcrumb a:hover {
        color: #00c4aa;
    }

    .modfolio-breadcrumb .separator {
        margin: 0 8px;
        color: #999;
    }

    .modfolio-breadcrumb .current {
        color: #00c4aa;
    }

    .modfolio-welcome-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .welcome-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .welcome-avatar img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e5e5e5;
    }

    .welcome-label {
        display: block;
        font-size: 11px;
        font-weight: 500;
        color: #666;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .welcome-name {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }

    .welcome-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }

    .membership-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #1a1a1a;
        color: #fff;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .membership-badge:hover {
        background: #333;
        color: #fff;
    }

    .membership-badge span {
        font-weight: 400;
    }

    .membership-badge strong {
        font-weight: 600;
    }

    .last-login {
        display: flex;
        flex-direction: column;
        font-size: 12px;
        color: #666;
        text-align: right;
    }

    .last-login .login-label {
        font-weight: 500;
    }

    .last-login .login-time {
        color: #999;
    }

    @media (max-width: 1024px) {
        .modfolio-welcome-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }

        .welcome-left {
            flex-direction: column;
        }

        .welcome-right {
            flex-direction: column;
            gap: 12px;
        }

        .last-login {
            text-align: center;
        }
    }

    @media (max-width: 768px) {
        .modfolio-welcome-header {
            padding: 16px;
        }
    }
    <?php
    return ob_get_clean();
}

/**
 * ===============================================
 * MODFOLIO LICENSE SYSTEM
 * Portfolio License (Select: Standard vs Exclusive)
 * ===============================================
 */

/**
 * Add License Fields to WCFM Product Manager
 */
add_filter('wcfm_product_manage_fields_pricing', 'modfolio_add_license_fields', 50, 2);
function modfolio_add_license_fields($fields, $product_id) {
    // Saved meta
    $saved_licenses = $product_id ? get_post_meta($product_id, '_product_licenses', true) : array();
    if (!is_array($saved_licenses)) $saved_licenses = array();

    $saved_package = $product_id ? get_post_meta($product_id, '_license_package', true) : '';
    if (!in_array($saved_package, array('standard','exclusive'), true)) $saved_package = '';

    // Helper: read saved values
    $get_saved = function($key, $which='enabled') use ($saved_licenses){
        $v = isset($saved_licenses[$key][$which]) ? $saved_licenses[$key][$which] : ($which==='enabled' ? false : '');
        if ($which==='price' && $v!=='') return wc_format_localized_price($v);
        return $v;
    };

    // --- Package SELECT ---
    $fields['_license_package'] = array(
        'label'       => __('License Package', 'wc-frontend-manager'),
        'type'        => 'select',
        'class'       => 'wcfm_ele modfolio-license-package',
        'label_class' => 'wcfm_title',
        'options'     => array(
            ''          => __('— Select License Package —', 'wc-frontend-manager'),
            'standard'  => __('Standard (Personal + Commercial + AI Training)', 'wc-frontend-manager'),
            'exclusive' => __('Exclusive', 'wc-frontend-manager'),
        ),
        'value'       => $saved_package,
        'dfvalue'     => $saved_package,
    );

    /* --- STANDARD GROUP: Personal + Commercial + AI Training --- */
    $grpStd = 'license-group-standard';

    // Personal
    $fields['_product_license_personal'] = array(
        'label'       => __('Personal License', 'wc-frontend-manager'),
        'type'        => 'checkbox',
        'class'       => 'wcfm_ele modfolio-license-checkbox '.$grpStd,
        'label_class' => 'wcfm_title checkbox_title '.$grpStd,
        'value'       => 'yes',
        'dfvalue'     => $get_saved('personal','enabled') ? 'yes' : 'no',
    );
    $fields['_product_license_personal_price'] = array(
        'label'       => __('Personal License Price ($)', 'wc-frontend-manager'),
        'type'        => 'text',
        'class'       => 'wcfm_ele modfolio-license-price modfolio-license-price-personal '.$grpStd,
        'label_class' => 'wcfm_title '.$grpStd,
        'value'       => $get_saved('personal','price'),
        'placeholder' => '0.00',
    );

    // Commercial
    $fields['_product_license_commercial'] = array(
        'label'       => __('Commercial License', 'wc-frontend-manager'),
        'type'        => 'checkbox',
        'class'       => 'wcfm_ele modfolio-license-checkbox '.$grpStd,
        'label_class' => 'wcfm_title checkbox_title '.$grpStd,
        'value'       => 'yes',
        'dfvalue'     => $get_saved('commercial','enabled') ? 'yes' : 'no',
    );
    $fields['_product_license_commercial_price'] = array(
        'label'       => __('Commercial License Price ($)', 'wc-frontend-manager'),
        'type'        => 'text',
        'class'       => 'wcfm_ele modfolio-license-price modfolio-license-price-commercial '.$grpStd,
        'label_class' => 'wcfm_title '.$grpStd,
        'value'       => $get_saved('commercial','price'),
        'placeholder' => '0.00',
    );

    // AI Training License
    $fields['_product_license_ai_training'] = array(
        'label'       => __('AI Training License', 'wc-frontend-manager'),
        'type'        => 'checkbox',
        'class'       => 'wcfm_ele modfolio-license-checkbox '.$grpStd,
        'label_class' => 'wcfm_title checkbox_title '.$grpStd,
        'value'       => 'yes',
        'dfvalue'     => $get_saved('ai_training','enabled') ? 'yes' : 'no',
    );
    $fields['_product_license_ai_training_price'] = array(
        'label'       => __('AI Training License Price ($)', 'wc-frontend-manager'),
        'type'        => 'text',
        'class'       => 'wcfm_ele modfolio-license-price modfolio-license-price-ai_training '.$grpStd,
        'label_class' => 'wcfm_title '.$grpStd,
        'value'       => $get_saved('ai_training','price'),
        'placeholder' => '0.00',
    );

    /* --- EXCLUSIVE GROUP --- */
    $grpExc = 'license-group-exclusive';

    $fields['_product_license_exclusive'] = array(
        'label'       => __('Exclusive Right License', 'wc-frontend-manager'),
        'type'        => 'checkbox',
        'class'       => 'wcfm_ele modfolio-license-checkbox '.$grpExc,
        'label_class' => 'wcfm_title checkbox_title '.$grpExc,
        'value'       => 'yes',
        'dfvalue'     => $get_saved('exclusive','enabled') ? 'yes' : 'no',
    );
    $fields['_product_license_exclusive_price'] = array(
        'label'       => __('Exclusive Right License Price ($)', 'wc-frontend-manager'),
        'type'        => 'text',
        'class'       => 'wcfm_ele modfolio-license-price modfolio-license-price-exclusive '.$grpExc,
        'label_class' => 'wcfm_title '.$grpExc,
        'value'       => $get_saved('exclusive','price'),
        'placeholder' => '0.00',
    );

    // Hide Woo default prices
    if (isset($fields['regular_price'])) unset($fields['regular_price']);
    if (isset($fields['sale_price'])) unset($fields['sale_price']);

    return $fields;
}

/**
 * Save License Data
 */
add_action('after_wcfm_products_manage_meta_save', 'modfolio_save_license_data', 50, 2);
function modfolio_save_license_data($product_id, $form_data) {
    $package = isset($form_data['_license_package']) ? sanitize_text_field($form_data['_license_package']) : 'standard';
    if (!in_array($package, array('standard','exclusive'), true)) $package = 'standard';

    // Save exclusive product flag
    $is_exclusive = isset($form_data['_is_exclusive_product']) ? sanitize_text_field($form_data['_is_exclusive_product']) : 'no';
    if (!in_array($is_exclusive, array('yes','no'), true)) $is_exclusive = 'no';
    update_post_meta($product_id, '_is_exclusive_product', $is_exclusive);

    // Save "Make Product Free" flag (only if exclusive product is yes and checkbox is checked)
    $make_product_free = 'no';
    if ($is_exclusive === 'yes' && isset($form_data['_make_product_free']) && $form_data['_make_product_free'] === 'yes') {
        $make_product_free = 'yes';
    }
    update_post_meta($product_id, '_make_product_free', $make_product_free);

    update_post_meta($product_id, '_license_package', $package);

    // Include ai_training in license keys
    $license_keys = array('personal','commercial','ai_training','exclusive');
    $licenses = array();

    $clean_price = function($raw){
        if ($raw === '' || $raw === null) return '';
        $p = floatval( wc_clean( wp_unslash($raw) ) );
        return ($p >= 0) ? $p : '';
    };

    foreach ($license_keys as $key) {
        $price = isset($form_data["_product_license_{$key}_price"]) ? $clean_price($form_data["_product_license_{$key}_price"]) : '';

        // License is enabled if it has a price > 0
        $enabled = ($price !== '' && floatval($price) > 0);

        if ($package === 'standard' && $key === 'exclusive') { $enabled = false; $price = ''; }
        if ($package === 'exclusive' && in_array($key, array('personal','commercial','ai_training'))) {
            $enabled = false;
            $price = '';
        }

        $licenses[$key] = array(
            'enabled' => (bool) $enabled,
            'price'   => ($price === '' ? '' : floatval($price)),
        );
    }

    update_post_meta($product_id, '_product_licenses', $licenses);

    // Compute base price (include ai_training for standard)
    $visible_keys = ($package === 'standard') ? array('personal','commercial','ai_training') : (($package === 'exclusive') ? array('exclusive') : array());
    $available_prices = array();
    foreach ($visible_keys as $k) {
        if (!empty($licenses[$k]['enabled']) && $licenses[$k]['price'] !== '' && $licenses[$k]['price'] > 0) {
            $available_prices[] = (float) $licenses[$k]['price'];
        }
    }
    $final_price = !empty($available_prices) ? min($available_prices) : 0;

    update_post_meta($product_id, '_regular_price', $final_price);
    update_post_meta($product_id, '_price', $final_price);
    update_post_meta($product_id, '_sale_price', '');

    if ($product = wc_get_product($product_id)) {
        $product->set_regular_price($final_price);
        $product->set_price($final_price);
        $product->set_sale_price('');
        $product->save();
    }
}

/**
 * Handle Gallery Images from custom field
 */
add_action('after_wcfm_products_manage_meta_save', 'modfolio_save_gallery_images', 10, 2);
function modfolio_save_gallery_images($product_id, $form_data) {
    // Handle gallery_img_ids field (comma-separated IDs)
    if (isset($form_data['gallery_img_ids'])) {
        $gallery_ids_string = sanitize_text_field($form_data['gallery_img_ids']);
        $gallery_ids = array_filter(array_map('absint', explode(',', $gallery_ids_string)));

        $product = wc_get_product($product_id);
        if ($product) {
            $product->set_gallery_image_ids($gallery_ids);
            $product->save();
        }
    }
}

/**
 * Enqueue WordPress Media Library scripts for WCFM pages
 */
add_action('wp_enqueue_scripts', 'modfolio_enqueue_media_scripts');
function modfolio_enqueue_media_scripts() {
    if (function_exists('is_wcfm_page') && is_wcfm_page()) {
        wp_enqueue_media();
    }
}

/**
 * AJAX Handler: Update Password
 */
add_action('wp_ajax_modfolio_update_password', 'modfolio_update_password_handler');
function modfolio_update_password_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'modfolio_update_password')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'wc-frontend-manager')));
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'wc-frontend-manager')));
    }

    $user_id = get_current_user_id();
    $user = get_user_by('id', $user_id);

    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';

    // Verify current password
    if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
        wp_send_json_error(array('message' => __('Current password is incorrect.', 'wc-frontend-manager')));
    }

    // Validate new password
    if (strlen($new_password) < 8) {
        wp_send_json_error(array('message' => __('Password must be at least 8 characters long.', 'wc-frontend-manager')));
    }

    // Update password
    wp_set_password($new_password, $user_id);

    // Log user back in
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_send_json_success(array('message' => __('Password updated successfully.', 'wc-frontend-manager')));
}

/**
 * Save Profile Custom Fields
 * Note: WCFM passes parsed form data in $wcfm_profile_form, not $_POST
 */
add_action('wcfm_profile_update', 'modfolio_save_profile_custom_fields', 10, 2);
function modfolio_save_profile_custom_fields($user_id, $wcfm_profile_form) {
    // Two-factor authentication
    if (isset($wcfm_profile_form['two_factor_enabled'])) {
        update_user_meta($user_id, '_two_factor_enabled', 'yes');
    } else {
        update_user_meta($user_id, '_two_factor_enabled', 'no');
    }

    // Preferred currency
    if (isset($wcfm_profile_form['preferred_currency'])) {
        $currency = sanitize_text_field($wcfm_profile_form['preferred_currency']);
        if (in_array($currency, array('USD', 'EUR', 'GBP', 'PKR'))) {
            update_user_meta($user_id, '_preferred_currency', $currency);
        }
    }

    // Display name
    if (isset($wcfm_profile_form['display_name']) && !empty($wcfm_profile_form['display_name'])) {
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => sanitize_text_field($wcfm_profile_form['display_name'])
        ));
    }

    // Date of Birth
    if (isset($wcfm_profile_form['vendor_dob'])) {
        $dob = sanitize_text_field($wcfm_profile_form['vendor_dob']);
        // Validate date format (YYYY-MM-DD)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            update_user_meta($user_id, 'user_registration_vendor_daob', $dob);
        } elseif (empty($dob)) {
            delete_user_meta($user_id, 'user_registration_vendor_daob');
        }
    }

    // Gender
    if (isset($wcfm_profile_form['vendor_gender'])) {
        $gender = sanitize_text_field($wcfm_profile_form['vendor_gender']);
        if (in_array($gender, array('male', 'female', 'others'))) {
            update_user_meta($user_id, 'user_registration_vendor_gender', $gender);
        } elseif (empty($gender)) {
            delete_user_meta($user_id, 'user_registration_vendor_gender');
        }
    }

    // Update last login time
    update_user_meta($user_id, 'last_login', current_time('mysql'));
}

/**
 * Track User Last Login
 */
add_action('wp_login', 'modfolio_track_last_login', 10, 2);
function modfolio_track_last_login($user_login, $user) {
    update_user_meta($user->ID, 'last_login', current_time('mysql'));
}

/**
 * "How it Works" Section - Shop Page
 * Displays AFTER the load more/pagination button (priority 20, pagination is at 10)
 */
add_action('woocommerce_after_shop_loop', 'modfolio_how_it_works_section', 20);
function modfolio_how_it_works_section() {
    // Only show on main shop page, first page
    if ( ! is_shop() || is_paged() ) {
        return;
    }
    ?>
    <div class="modfolio-how-it-works-section">
        <?php echo do_shortcode( '[elementor-template id="24152"]' ); ?>
    </div>
    <?php
}

/**
 * AJAX Handler: Get Attachment URL from ID
 * Used to convert media attachment IDs to URLs for downloadable files
 */
add_action('wp_ajax_modfolio_get_attachment_url', 'modfolio_get_attachment_url_handler');
add_action('wp_ajax_nopriv_modfolio_get_attachment_url', 'modfolio_get_attachment_url_handler');
function modfolio_get_attachment_url_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'modfolio_get_attachment_url')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'wc-frontend-manager')));
    }

    $attachment_id = isset($_POST['attachment_id']) ? absint($_POST['attachment_id']) : 0;

    if (!$attachment_id) {
        wp_send_json_error(array('message' => __('Invalid attachment ID.', 'wc-frontend-manager')));
    }

    $url = wp_get_attachment_url($attachment_id);

    if (!$url) {
        wp_send_json_error(array('message' => __('Attachment not found.', 'wc-frontend-manager')));
    }

    wp_send_json_success(array('url' => $url));
}

/**
 * Fix downloadable files before saving - convert IDs to URLs
 * This hook runs before WCFM saves the product meta
 */
add_filter('wcfm_product_manage_downloadable_files_data', 'modfolio_fix_downloadable_files_data', 10, 2);
function modfolio_fix_downloadable_files_data($downloadable_files, $product_id) {
    if (empty($downloadable_files) || !is_array($downloadable_files)) {
        return $downloadable_files;
    }

    foreach ($downloadable_files as $key => $file) {
        if (isset($file['file'])) {
            $file_value = trim($file['file']);

            // Check if it's a numeric ID
            if (is_numeric($file_value)) {
                $attachment_id = absint($file_value);
                $url = wp_get_attachment_url($attachment_id);

                if ($url) {
                    $downloadable_files[$key]['file'] = $url;
                    error_log("[Modfolio] Converted attachment ID {$attachment_id} to URL: {$url}");
                }
            }
        }
    }

    return $downloadable_files;
}

/**
 * Also fix downloadable files during product meta save
 */
add_action('after_wcfm_products_manage_meta_save', 'modfolio_fix_product_downloadable_files', 5, 2);
function modfolio_fix_product_downloadable_files($product_id, $form_data) {
    if (!isset($form_data['downloadable_files']) || empty($form_data['downloadable_files'])) {
        return;
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->is_downloadable()) {
        return;
    }

    $downloads = $product->get_downloads();
    $updated = false;

    foreach ($downloads as $key => $download) {
        $file = $download->get_file();

        // Check if the file value is a numeric ID instead of URL
        if (is_numeric($file)) {
            $attachment_id = absint($file);
            $url = wp_get_attachment_url($attachment_id);

            if ($url) {
                $download->set_file($url);
                $downloads[$key] = $download;
                $updated = true;
                error_log("[Modfolio] Fixed download file: converted ID {$attachment_id} to URL {$url} for product {$product_id}");
            }
        }
    }

    if ($updated) {
        $product->set_downloads($downloads);
        $product->save();
    }
}