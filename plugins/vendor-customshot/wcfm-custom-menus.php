<?php
/**
 * Plugin Name: WCFM - Vendor Custom Shots
 * Plugin URI: http://modfolios.com
 * Description: Custom Shots feature for WCFM - allows buyers to request custom photo shoots from vendors.
 * Author: Modfolios
 * Version: 1.0.0
 * Author URI: http://modfolios.com
 *
 * Text Domain: vendor-customshot
 * Domain Path: /lang/
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Define plugin constants
define('CUSTOMSHOTS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CUSTOMSHOTS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include email handler
require_once(CUSTOMSHOTS_PLUGIN_PATH . 'includes/class-customshot-emails.php');

// Include chat handler
require_once(CUSTOMSHOTS_PLUGIN_PATH . 'includes/class-customshot-chat.php');

// Include services & subscriptions controller
require_once(CUSTOMSHOTS_PLUGIN_PATH . 'controllers/wcfm-controller-services-subscriptions.php');

/**
 * Create chat table on plugin activation
 */
register_activation_hook(__FILE__, 'customshots_activate');

function customshots_activate() {
    Customshot_Chat::create_table();
}

// Also check/create table on init (for manual updates)
add_action('init', 'customshots_check_table', 5);

function customshots_check_table() {
    if (get_option('customshot_chat_table_version') !== '1.0') {
        Customshot_Chat::create_table();
    }
}

/**
 * Register Custom Shot Post Type
 */
add_action('init', 'customshots_register_post_type');

function customshots_register_post_type() {
    $labels = array(
        'name'                  => _x('Custom Shots', 'Post type general name', 'vendor-customshot'),
        'singular_name'         => _x('Custom Shot', 'Post type singular name', 'vendor-customshot'),
        'menu_name'             => _x('Custom Shots', 'Admin Menu text', 'vendor-customshot'),
        'name_admin_bar'        => _x('Custom Shot', 'Add New on Toolbar', 'vendor-customshot'),
        'add_new'               => __('Add New', 'vendor-customshot'),
        'add_new_item'          => __('Add New Custom Shot', 'vendor-customshot'),
        'new_item'              => __('New Custom Shot', 'vendor-customshot'),
        'edit_item'             => __('Edit Custom Shot', 'vendor-customshot'),
        'view_item'             => __('View Custom Shot', 'vendor-customshot'),
        'all_items'             => __('All Custom Shots', 'vendor-customshot'),
        'search_items'          => __('Search Custom Shots', 'vendor-customshot'),
        'parent_item_colon'     => __('Parent Custom Shots:', 'vendor-customshot'),
        'not_found'             => __('No custom shots found.', 'vendor-customshot'),
        'not_found_in_trash'    => __('No custom shots found in Trash.', 'vendor-customshot'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'customshot'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-camera',
        'supports'           => array('title', 'author'),
    );

    register_post_type('customshot', $args);
}

/**
 * Register Taxonomies for Custom Shot
 */
add_action('init', 'customshots_register_taxonomies');

function customshots_register_taxonomies() {
    // Shoot Type Taxonomy
    $shoot_type_labels = array(
        'name'              => _x('Shoot Types', 'taxonomy general name', 'vendor-customshot'),
        'singular_name'     => _x('Shoot Type', 'taxonomy singular name', 'vendor-customshot'),
        'search_items'      => __('Search Shoot Types', 'vendor-customshot'),
        'all_items'         => __('All Shoot Types', 'vendor-customshot'),
        'parent_item'       => __('Parent Shoot Type', 'vendor-customshot'),
        'parent_item_colon' => __('Parent Shoot Type:', 'vendor-customshot'),
        'edit_item'         => __('Edit Shoot Type', 'vendor-customshot'),
        'update_item'       => __('Update Shoot Type', 'vendor-customshot'),
        'add_new_item'      => __('Add New Shoot Type', 'vendor-customshot'),
        'new_item_name'     => __('New Shoot Type Name', 'vendor-customshot'),
        'menu_name'         => __('Shoot Types', 'vendor-customshot'),
    );

    $shoot_type_args = array(
        'hierarchical'      => true,
        'labels'            => $shoot_type_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'shoot-type'),
        'show_in_rest'      => true,
    );

    register_taxonomy('shoot_type', array('customshot'), $shoot_type_args);

    // Usage Type Taxonomy
    $usage_type_labels = array(
        'name'              => _x('Usage Types', 'taxonomy general name', 'vendor-customshot'),
        'singular_name'     => _x('Usage Type', 'taxonomy singular name', 'vendor-customshot'),
        'search_items'      => __('Search Usage Types', 'vendor-customshot'),
        'all_items'         => __('All Usage Types', 'vendor-customshot'),
        'parent_item'       => __('Parent Usage Type', 'vendor-customshot'),
        'parent_item_colon' => __('Parent Usage Type:', 'vendor-customshot'),
        'edit_item'         => __('Edit Usage Type', 'vendor-customshot'),
        'update_item'       => __('Update Usage Type', 'vendor-customshot'),
        'add_new_item'      => __('Add New Usage Type', 'vendor-customshot'),
        'new_item_name'     => __('New Usage Type Name', 'vendor-customshot'),
        'menu_name'         => __('Usage Types', 'vendor-customshot'),
    );

    $usage_type_args = array(
        'hierarchical'      => true,
        'labels'            => $usage_type_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'usage-type'),
        'show_in_rest'      => true,
    );

    register_taxonomy('usage_type', array('customshot'), $usage_type_args);
}

/**
 * Insert default taxonomy terms on plugin activation
 */
add_action('after_setup_theme', 'customshots_insert_default_terms');

function customshots_insert_default_terms() {
    // Check if we've already inserted the default terms
    if (get_option('customshots_default_terms_inserted')) {
        return;
    }

    // Default Shoot Types
    $shoot_types = array(
        'portrait'   => __('Portrait', 'vendor-customshot'),
        'product'    => __('Product', 'vendor-customshot'),
        'event'      => __('Event', 'vendor-customshot'),
        'fashion'    => __('Fashion', 'vendor-customshot'),
        'commercial' => __('Commercial', 'vendor-customshot'),
        'other'      => __('Other', 'vendor-customshot'),
    );

    // Default Usage Types
    $usage_types = array(
        'personal'     => __('Personal', 'vendor-customshot'),
        'commercial'   => __('Commercial', 'vendor-customshot'),
        'editorial'    => __('Editorial', 'vendor-customshot'),
        'social_media' => __('Social Media', 'vendor-customshot'),
    );

    // Insert Shoot Types
    foreach ($shoot_types as $slug => $name) {
        if (!term_exists($slug, 'shoot_type')) {
            wp_insert_term($name, 'shoot_type', array('slug' => $slug));
        }
    }

    // Insert Usage Types
    foreach ($usage_types as $slug => $name) {
        if (!term_exists($slug, 'usage_type')) {
            wp_insert_term($name, 'usage_type', array('slug' => $slug));
        }
    }

    // Mark as inserted so we don't run this again
    update_option('customshots_default_terms_inserted', true);
}

/**
 * Force insert default terms (run once) - call this manually if needed
 */
function customshots_force_insert_terms() {
    delete_option('customshots_default_terms_inserted');
    customshots_insert_default_terms();
}

/**
 * Get shoot type terms for dropdown
 */
function customshots_get_shoot_types() {
    $terms = get_terms(array(
        'taxonomy'   => 'shoot_type',
        'hide_empty' => false,
    ));

    if (is_wp_error($terms)) {
        return array();
    }

    return $terms;
}

/**
 * Get usage type terms for dropdown
 */
function customshots_get_usage_types() {
    $terms = get_terms(array(
        'taxonomy'   => 'usage_type',
        'hide_empty' => false,
    ));

    if (is_wp_error($terms)) {
        return array();
    }

    return $terms;
}

/**
 * Enqueue frontend scripts for AJAX
 */
add_action('wp_enqueue_scripts', 'customshots_enqueue_scripts');

function customshots_enqueue_scripts() {
    wp_enqueue_script('jquery');

    // Localize script with AJAX URL and nonce
    wp_localize_script('jquery', 'customshots_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('customshots_nonce'),
    ));
}

/**
 * Handle AJAX request to create custom shot (for logged in users)
 */
add_action('wp_ajax_customshots_submit_request', 'customshots_handle_ajax_submit');

function customshots_handle_ajax_submit() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customshots_nonce')) {
        wp_send_json_error(array(
            'message' => __('Security check failed. Please refresh the page and try again.', 'vendor-customshot'),
        ));
    }

    // Validate required fields
    $required_fields = array('title', 'shoot_type', 'brief', 'usage_type', 'vendor_id');
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(array(
                'message' => sprintf(__('Please fill in the %s field.', 'vendor-customshot'), str_replace('_', ' ', $field)),
            ));
        }
    }

    // Sanitize input data
    $title        = sanitize_text_field($_POST['title']);
    $shoot_type   = sanitize_text_field($_POST['shoot_type']);
    $brief        = sanitize_textarea_field($_POST['brief']);
    $usage_type   = sanitize_text_field($_POST['usage_type']);
    $deliverables = isset($_POST['deliverables']) ? sanitize_text_field($_POST['deliverables']) : '';
    $budget       = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
    $shoot_date   = isset($_POST['shoot_date']) ? sanitize_text_field($_POST['shoot_date']) : '';
    $vendor_id    = intval($_POST['vendor_id']);
    $buyer_id     = get_current_user_id();

    // Create the custom shot post
    $post_data = array(
        'post_title'  => $title,
        'post_type'   => 'customshot',
        'post_status' => 'pending',
        'post_author' => $buyer_id,
    );

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        wp_send_json_error(array(
            'message' => __('Failed to create custom shot request. Please try again.', 'vendor-customshot'),
        ));
    }

    // Set taxonomies
    wp_set_object_terms($post_id, $shoot_type, 'shoot_type');
    wp_set_object_terms($post_id, $usage_type, 'usage_type');

    // Save meta data
    update_post_meta($post_id, '_customshot_brief', $brief);
    update_post_meta($post_id, '_customshot_deliverables', $deliverables);
    update_post_meta($post_id, '_customshot_budget', $budget);
    update_post_meta($post_id, '_customshot_shoot_date', $shoot_date);
    update_post_meta($post_id, '_customshot_vendor_id', $vendor_id);
    update_post_meta($post_id, '_customshot_buyer_id', $buyer_id);
    update_post_meta($post_id, '_customshot_status', 'pending');

    // Send email notification to vendor
    Customshot_Emails::send_new_request_to_vendor($post_id);

    // Success response
    wp_send_json_success(array(
        'message' => __('Your custom shot request has been submitted successfully!', 'vendor-customshot'),
        'post_id' => $post_id,
    ));
}

/**
 * Handle AJAX request for non-logged in users
 */
add_action('wp_ajax_nopriv_customshots_submit_request', 'customshots_handle_ajax_submit_nopriv');

function customshots_handle_ajax_submit_nopriv() {
    wp_send_json_error(array(
        'message' => __('You must be logged in to submit a custom shot request.', 'vendor-customshot'),
    ));
}

/**
 * Add vendor tagline after store title
 * Hook: before_wcfmmp_store_header_info - fires right after the store title
 */
add_action('before_wcfmmp_store_header_info', 'customshots_add_vendor_tagline', 10, 1);

function customshots_add_vendor_tagline($store_id) {
    $vendor_id = $store_id;

    // Get vendor profile data
    $vendor_data = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);

    // Get tagline, default to "Editorial and Fashion" if empty
    $tagline = isset($vendor_data['tagline']) && !empty($vendor_data['tagline']) 
        ? $vendor_data['tagline'] 
        : __('Editorial and Fashion', 'vendor-customshot');

    ?>
    <span class="vendor-tagline"><?php echo esc_html($tagline); ?></span>

    <style>
    .vendor-tagline {
        display: block;
        font-size: 14px;
        font-weight: 400;
        color: #666;
        margin: 4px 0 10px 0;
        line-height: 1.4;
    }

    @media (max-width: 480px) {
        .vendor-tagline {
            font-size: 13px;
        }
    }
    </style>
    <?php
}


/**
 * Add vendor stats (location, reviews, rating, products) after store avatar
 * Hook: wcfmmp_store_after_avatar - fires right after the store avatar
 */
add_action('wcfmmp_store_after_avatar', 'customshots_add_vendor_stats', 10, 1);

function customshots_add_vendor_stats($store_id) {
    global $WCFMmp;
    
    $vendor_id = $store_id;

    // Get vendor profile data
    $vendor_data = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);

    // Get address/location from demographics top_locations
    $address = isset($vendor_data['demographics']['top_locations']) ? $vendor_data['demographics']['top_locations'] : '';

    // Get store user object for accurate review data
    $store_user = wcfmmp_get_store($vendor_id);
    
    // Get total reviews and average rating using store_user object (like the reviews template)
    $total_reviews = 0;
    $average_rating = 0;

    if ($store_user) {
        $average_rating = $store_user->get_avg_review_rating();
        $total_reviews = $store_user->get_review_count();
    }

    // Get total published products
    $total_products = count_user_posts($vendor_id, 'product', true);

    // Format rating like the reviews template (4.5/5)
    $formatted_rating = $average_rating > 0 ? wc_format_decimal($average_rating, 1) : '0.0';

    ?>
    <div class="vendor-stats-section">
        <?php if (!empty($address)) : ?>
        <h1 class="vendor-location-title"><?php echo esc_html($address); ?></h1>
        <?php endif; ?>

        <div class="vendor-stats-row">
            <div class="vendor-stat-item vendor-stat-rating">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span class="stat-value"><?php echo esc_html($formatted_rating); ?><sub>/5</sub></span>
                <span class="stat-count">(<?php echo esc_html($total_reviews); ?> <?php echo $total_reviews == 1 ? __('review', 'vendor-customshot') : __('reviews', 'vendor-customshot'); ?>)</span>
            </div>

            <span class="stat-divider">|</span>

            <div class="vendor-stat-item vendor-stat-products">

                <span class="stat-value"><?php echo esc_html($total_products); ?>+</span>
            </div>
        </div>
    </div>

    <style>
    .vendor-stats-section {
        margin: 10px 0 15px 0;
        clear: both;
    }

    .vendor-location-title {
        font-size: 18px;
        font-weight: 500;
        color: #333;
        margin: 0 0 8px 0;
        padding: 0;
        line-height: 1.3;
    }

    .vendor-stats-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .vendor-stat-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: #555;
    }

    .vendor-stat-item svg {
        flex-shrink: 0;
    }

    .vendor-stat-rating svg {
        color: #f5a623;
        fill: #f5a623;
    }

    .vendor-stat-products svg {
        color: #666;
    }

    .stat-value {
        font-weight: 600;
        color: #1a1a1a;
    }

    .stat-value sub {
        font-size: 11px;
        font-weight: 400;
        color: #999;
        vertical-align: baseline;
        position: relative;
        bottom: 0;
    }

    .stat-count,
    .stat-label {
        color: #777;
        font-weight: 400;
    }

    .stat-divider {
        color: #ddd;
        font-weight: 300;
    }
    
    .wcfmmp-store-rating {
  display: none;
}
    

    @media (max-width: 480px) {
        .vendor-stats-section {
            margin: 8px 0 12px 0;
        }

        .vendor-location-title {
            font-size: 16px;
        }

        .vendor-stats-row {
            gap: 8px;
        }

        .vendor-stat-item {
            font-size: 13px;
        }
    }
    </style>
    <?php
}





add_action('wcfmmp_store_after_header', 'customshots_add_about_section', 10, 1);

function customshots_add_about_section($store_id) {
    $vendor_id = $store_id;

    // Get vendor profile data
    $vendor_data = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);
    $about = isset($vendor_data['about']) ? $vendor_data['about'] : '';

    // Only show if about content exists
    if (empty($about)) {
        return;
    }
    ?>
    <div class="vendor-about-section body_area right_side">
        <div class="vendor-about-container">
            <h3 class="vendor-about-title"><?php esc_html_e('About Me', 'vendor-customshot'); ?></h3>
            <div class="vendor-about-content">
                <?php echo wp_kses_post(wpautop($about)); ?>
            </div>
        </div>
    </div>

    <style>
    .vendor-about-section {
        padding: 30px 0;
        background: #fff;
/*         border-bottom: 1px solid #e5e5e5; */
    }
    .vendor-about-container {
/*         max-width: 1200px; */
        margin: 0 auto;
/*         padding: 0 20px; */
		margin-bottom: 45px;
    }
    .vendor-about-title {
        font-size: 30px;
        font-weight: 600;
        color: var(--new-color);
        margin: 0 0 16px 0;
    }
    .vendor-about-content {
        font-size: 15px;
        line-height: 1.7;
        color: #555;
    }
    .vendor-about-content p {
        margin: 0 0 12px 0;
    }
    .vendor-about-content p:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .vendor-about-section {
            padding: 20px 0;
        }
        .vendor-about-container {
            padding: 0 15px;
        }
        .vendor-about-title {
            font-size: 18px;
        }
        .vendor-about-content {
            font-size: 14px;
        }
    }
    </style>
    <?php
}

/**
 * Add "Request Custom Shot" button before the enquiry section on vendor store page
 */


add_action('wcfmmp_store_after_bannar_image', 'customshots_add_request_button', 10, 1);

function customshots_add_request_button($store_id) {
    $vendor_id = $store_id;

    // Get vendor social media data
    $vendor_data = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);
    $facebook_url = isset($vendor_data['social']['fb']) ? esc_url($vendor_data['social']['fb']) : '';
    $linkedin_url = isset($vendor_data['social']['linkedin']) ? esc_url($vendor_data['social']['linkedin']) : '';
    $twitter_url = isset($vendor_data['social']['twitter']) ? esc_url($vendor_data['social']['twitter']) : '';
    $instagram_url = isset($vendor_data['social']['instagram']) ? esc_url($vendor_data['social']['instagram']) : '';
	
	// Correct escaping for colors
	$background_color = isset($vendor_data['theme']['background']) ? esc_attr($vendor_data['theme']['background']) : '';
	$accent_color     = isset($vendor_data['theme']['accent']) ? esc_attr($vendor_data['theme']['accent']) : '';
	$light_bg_color   = isset($vendor_data['theme']['light_bg']) ? esc_attr($vendor_data['theme']['light_bg']) : '#FFFEF4';
	$dark_bg_color    = isset($vendor_data['theme']['dark_bg']) ? esc_attr($vendor_data['theme']['dark_bg']) : '#1a1a1a';

	if (!empty($background_color) || !empty($accent_color)) {
	?>
	<style>
	:root {
		<?php if (!empty($background_color)) : ?>
			--color-background: <?php echo $background_color; ?>;
		<?php endif; ?>

		<?php if (!empty($accent_color)) : ?>
			--color-accent: <?php echo $accent_color; ?>;
		<?php endif; ?>

		--color-light-bg: <?php echo $light_bg_color; ?>;
		--color-dark-bg: <?php echo $dark_bg_color; ?>;
	}





    /* --- banner --- */
#wcfmmp-store .banner_img::after{
	background: linear-gradient(180deg, #fff0 60%, var(--color-accent) 100%) !important;
}
.button.customshots-request-btn, button:not(.mce-container button).customshots-request-btn{
	background: var(--color-accent) !important;
}
#wcfmmp-store .logo_area::after{
	border-color: var(--color-accent) !important;
}


/* ----- body ----- */

#wcfmmp-store .tab_area .tab_links li.active{
	border-color: var(--color-accent) !important;
}
#wcfmmp-store .tab_area .tab_links li.active a,
.dark__theme #wcfmmp-store .tab_area .tab_links li.active a{
	color: var(--color-accent) !important;
}
#wcfmmp-store .product .thumbnail a::before{
	background: linear-gradient(180deg, rgba(0, 0, 0, 0) 50%, var(--color-accent) 100%) !important;
}

#wcfmmp-store .premium-notice-box .notice-icon{
	background: var(--color-accent) !important;
}
#wcfmmp-store .add_review button{
	background: var(--color-accent) !important;
}

/* -- portfolios -- */
.premium-notice-box .notice-btn{
    background: var(--color-accent) !important;
}


/* -- subscription -- */
#wcfmmp-store .subscription-tier-name{
	color: var(--color-accent) !important;
}
#wcfmmp-store .subscription-price{
	background: var(--color-accent) !important;
	background: linear-gradient(90deg, var(--color-accent) 0%, var(--color-background) 100%) !important;
	background-clip: text !important;
	-webkit-background-clip: text !important;
    width: fit-content;
}
#wcfmmp-store .subscribe-btn,
#wcfmmp-store .subscribe-btn:hover {
	background: var(--color-accent) !important;
}
#wcfmmp-store .subscription-card{
	background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 50%, var(--color-accent) 140%) !important;
}
.dark__theme #wcfmmp-store .subscription-card {
    background: linear-gradient(180deg, #ffffff21 0%, #ffffff17 50%, var(--color-accent) 140%) !important;
}


/* --- media kit --- */
#wcfmmp-store .metric-value{
	background: var(--color-accent) !important;
	background: linear-gradient(90deg, var(--color-accent) 0%, var(--color-background) 100%) !important;
	background-clip: text !important;
    width: fit-content;
}
#wcfmmp-store .achievements-section .section-title,
#wcfmmp-store .media-kit-card .card-title {
	color: var(--color-accent) !important;
    -webkit-text-fill-color: var(--color-accent) !important;
}

#wcfmmp-store .gender-female,
#wcfmmp-store .location-bar,
#wcfmmp-store .age-bar{
	background: var(--color-accent) !important;
}

#wcfmmp-store .achievement-year{
	background: var(--color-accent) !important;
	background: linear-gradient(90deg, var(--color-accent) 0%, var(--color-background) 100%) !important;
	background-clip: text !important;
	-webkit-background-clip: text !important;
    width: fit-content;
}
#wcfmmp-store .info-label{
	color: var(--color-accent) !important;
}




/* ---- rate card ---- */

#wcfmmp-store .package-title{
	color: var(--color-accent) !important;
}

#wcfmmp-store .rate-card-package,
#wcfmmp-store .rate-card-empty{
	background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 50%, var(--color-accent) 140%) !important;
}
.dark__theme #wcfmmp-store .rate-card-package {
    background: linear-gradient(180deg, #ffffff21 0%, #ffffff17 50%, var(--color-accent) 140%) !important;
}









.wcfm-store-page .vendor-about-title{
	color: var(--color-accent) !important;
}










/* --- body --- */

#wcfmmp-store .tab_area .tab_links li.active{
	border-color: var(--color-accent) !important;
}





/* ----- background color -------- */

body.wcfm-store-page {
	background-color: var(--color-light-bg) !important;
}
.page-header,
#page-header.header-pinned .page-header-inner,
.header_left,
#wcfmmp-store .header_right,
#wcfmmp-store #wcfm_store_header,
#wcfmmp-store .header_left,
#wcfmmp-store .body_area,
#wcfmmp-store .tab_area .tab_links,
#wcfmmp-store .tab_area .tab_links li.active a{
    background: var(--color-light-bg) !important;
}


/* -- footer -- */
.elementor-6422 .elementor-element.elementor-element-341d4a6,
.elementor-6432 .elementor-element.elementor-element-738468e,
.elementor-6432 .elementor-element.elementor-element-a48d9b4{
    background: var(--color-light-bg) !important;
}


/* ==== dark theme ==== */

.dark__theme body.wcfm-store-page {
	background-color: var(--color-dark-bg) !important;
}
.dark__theme .page-header,
.dark__theme #page-header.header-pinned .page-header-inner,
.dark__theme #wcfmmp-store .header_left,
.dark__theme #wcfmmp-store .header_right,
.dark__theme #wcfmmp-store #wcfm_store_header,
.dark__theme #wcfmmp-store .header_left,
.dark__theme #wcfmmp-store .body_area,
.dark__theme #wcfmmp-store .tab_area .tab_links,
.dark__theme #wcfmmp-store .tab_area .tab_links li.active a{
    background: var(--color-dark-bg) !important;
}


/* -- footer -- */
.dark__theme .elementor-6422 .elementor-element.elementor-element-341d4a6,
.dark__theme .elementor-6432 .elementor-element.elementor-element-738468e,
.dark__theme .elementor-6432 .elementor-element.elementor-element-a48d9b4{
    background: var(--color-dark-bg) !important;
}


			
		</style>
	
	<?php
	}
	
    // Check if any social links exist
    $has_social_links = !empty($facebook_url) || !empty($linkedin_url) || !empty($twitter_url) || !empty($instagram_url);
    ?>
    <div class="customshots-store-actions">
        <?php if ($has_social_links) : ?>
        <div class="vendor-social-icons">
            <?php if (!empty($linkedin_url)) : ?>
            <a href="<?php echo $linkedin_url; ?>" target="_blank" rel="noopener noreferrer" class="social-icon social-linkedin" title="LinkedIn">
                <svg width="37" height="37" viewBox="0 0 37 37" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M35.75 9.87393C35.75 4.83812 31.6619 0.75 26.6261 0.75H9.87393C4.83812 0.75 0.75 4.83812 0.75 9.87393V26.6261C0.75 31.6619 4.83812 35.75 9.87393 35.75H26.6261C31.6619 35.75 35.75 31.6619 35.75 26.6261V9.87393ZM34.2543 9.87393V26.6261C34.2543 30.8362 30.8362 34.2543 26.6261 34.2543H9.87393C5.66376 34.2543 2.24573 30.8362 2.24573 26.6261V9.87393C2.24573 5.66376 5.66376 2.24573 9.87393 2.24573H26.6261C30.8362 2.24573 34.2543 5.66376 34.2543 9.87393Z" fill="white" stroke="white" stroke-width="1.5"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.7372 15.2006C13.7372 14.8769 13.4712 14.6142 13.1435 14.6142H9.34371C9.01598 14.6142 8.75 14.8769 8.75 15.2006V27.1636C8.75 27.4873 9.01598 27.75 9.34371 27.75H13.1435C13.4712 27.75 13.7372 27.4873 13.7372 27.1636V15.2006ZM11.2436 8.75C9.86713 8.75 8.75 9.85341 8.75 11.213C8.75 12.5725 9.86713 13.6759 11.2436 13.6759C12.6201 13.6759 13.7372 12.5725 13.7372 11.213C13.7372 9.85341 12.6201 8.75 11.2436 8.75ZM19.6743 15.5567V15.2006C19.6743 14.8769 19.4084 14.6142 19.0806 14.6142H15.2809C14.9531 14.6142 14.6871 14.8769 14.6871 15.2006V25.9082C14.6871 26.3967 14.8836 26.8651 15.2333 27.2105C15.583 27.556 16.0573 27.75 16.5519 27.75H19.0806C19.4084 27.75 19.6743 27.4873 19.6743 27.1636V20.1359C19.6762 20.1204 19.7076 19.8709 19.9569 19.6612C20.2058 19.4524 20.6257 19.3056 21.3068 19.3056C22.6168 19.3056 22.764 19.8441 22.764 19.8441C22.75 19.75 22.7617 20.2157 22.7617 19.892V27.1636C22.7617 27.4873 23.0276 27.75 23.3554 27.75H27.1551C27.4829 27.75 27.7489 27.4873 27.7489 27.1636V19.0058C27.7516 18.9603 27.7495 18.9147 27.7427 18.8697C27.6183 18.0078 27.269 17.1927 26.7291 16.5045C26.1892 15.8163 25.4774 15.2786 24.6634 14.9444C23.8495 14.6102 22.9616 14.4909 22.0868 14.5983C21.2121 14.7056 20.3807 15.0359 19.6743 15.5567Z" fill="white"/>
</svg>

            </a>
            <?php endif; ?>
            <?php if (!empty($instagram_url)) : ?>
            <a href="<?php echo $instagram_url; ?>" target="_blank" rel="noopener noreferrer" class="social-icon social-instagram" title="Instagram">
                <svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M34.9044 10.2901C34.8223 8.43036 34.5216 7.15192 34.0907 6.04401C33.6463 4.86807 32.9626 3.81527 32.0669 2.94003C31.1916 2.05118 30.1318 1.36048 28.9695 0.923032C27.8553 0.492284 26.5834 0.191444 24.7237 0.109533C22.8501 0.0205118 22.2553 0 17.5034 0C12.7515 0 12.1567 0.0205118 10.2901 0.102559C8.43043 0.184606 7.15192 0.485583 6.04435 0.916058C4.86807 1.36048 3.81527 2.04421 2.94003 2.94003C2.05118 3.8152 1.36082 4.87498 0.923032 6.03731C0.492284 7.15192 0.191512 8.42352 0.109533 10.2831C0.0205802 12.1567 0 12.7515 0 17.5034C0 22.2553 0.0205802 22.8502 0.102559 24.7167C0.184606 26.5765 0.485583 27.8549 0.9164 28.9628C1.36082 30.1388 2.05118 31.1916 2.94003 32.0668C3.81527 32.9557 4.87504 33.6464 6.03738 34.0838C7.15186 34.5146 8.42345 34.8154 10.2835 34.8973C12.1498 34.9796 12.7449 34.9999 17.4968 34.9999C22.2487 34.9999 22.8435 34.9796 24.7101 34.8973C26.5698 34.8153 27.8483 34.5146 28.9559 34.0838C30.1191 33.634 31.1756 32.9462 32.0575 32.0643C32.9394 31.1825 33.6273 30.1261 34.0772 28.9628C34.5076 27.8483 34.8087 26.5764 34.8907 24.7167C34.9727 22.8502 34.9932 22.2553 34.9932 17.5034C34.9932 12.7515 34.9863 12.1567 34.9044 10.2901ZM31.7525 24.58C31.6772 26.2893 31.3901 27.2123 31.1508 27.8277C30.5626 29.3524 29.3525 30.5626 27.8277 31.1508C27.2123 31.3901 26.2827 31.6772 24.58 31.7522C22.7339 31.8345 22.1803 31.8547 17.5104 31.8547C12.8405 31.8547 12.2799 31.8345 10.4404 31.7522C8.73113 31.6772 7.8081 31.3901 7.19274 31.1508C6.43401 30.8703 5.74338 30.4259 5.18272 29.8447C4.60155 29.2772 4.15713 28.5935 3.87667 27.8347C3.63736 27.2193 3.35026 26.2893 3.27526 24.587C3.19294 22.7409 3.1727 22.187 3.1727 17.517C3.1727 12.8471 3.19294 12.2865 3.27526 10.4474C3.35026 8.7381 3.63736 7.81507 3.87667 7.19972C4.15713 6.44064 4.60155 5.75015 5.1897 5.18935C5.75692 4.60819 6.44064 4.16376 7.19972 3.88364C7.81507 3.64433 8.74508 3.35717 10.4474 3.28189C12.2935 3.19984 12.8474 3.17933 17.517 3.17933C22.1939 3.17933 22.7475 3.19984 24.587 3.28189C26.2963 3.35724 27.2193 3.64427 27.8347 3.88357C28.5934 4.16376 29.2841 4.60819 29.8447 5.18935C30.4259 5.75692 30.8703 6.44064 31.1508 7.19972C31.3901 7.81507 31.6772 8.74473 31.7524 10.4474C31.8345 12.2935 31.855 12.8471 31.855 17.517C31.855 22.187 31.8345 22.7339 31.7525 24.58Z" fill="white"/>
<path d="M17.5027 8.51206C12.539 8.51206 8.51172 12.5391 8.51172 17.5031C8.51172 22.4671 12.539 26.4941 17.5027 26.4941C22.4666 26.4941 26.4937 22.4671 26.4937 17.5031C26.4937 12.5391 22.4666 8.51206 17.5027 8.51206ZM17.5027 23.3353C14.2825 23.3353 11.6704 20.7236 11.6704 17.5031C11.6704 14.2826 14.2825 11.6709 17.5027 11.6709C20.7232 11.6709 23.3349 14.2826 23.3349 17.5031C23.3349 20.7236 20.7231 23.3353 17.5027 23.3353ZM28.9485 8.15652C28.9485 9.31572 28.0085 10.2556 26.8492 10.2556C25.69 10.2556 24.7502 9.31572 24.7502 8.15652C24.7502 6.9972 25.69 6.05762 26.8492 6.05762C28.0085 6.05762 28.9485 6.99713 28.9485 8.15652Z" fill="white"/>
</svg>


            </a>
            <?php endif; ?>
            <?php if (!empty($facebook_url)) : ?>
            <a href="<?php echo $facebook_url; ?>" target="_blank" rel="noopener noreferrer" class="social-icon social-facebook" title="Facebook">
                <svg width="37" height="37" viewBox="0 0 37 37" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M23.6813 35.75H26.6261C31.6619 35.75 35.75 31.6619 35.75 26.6261V9.87393C35.75 4.83812 31.6619 0.75 26.6261 0.75H9.87393C4.83812 0.75 0.75 4.83812 0.75 9.87393V26.6261C0.75 31.6619 4.83812 35.75 9.87393 35.75H18.6987C18.8971 35.75 19.0873 35.6712 19.2275 35.531C19.3678 35.3907 19.4466 35.2005 19.4466 35.0021C19.4466 34.8038 19.3678 34.6136 19.2275 34.4733C19.0873 34.3331 18.8971 34.2543 18.6987 34.2543H9.87393C5.66376 34.2543 2.24573 30.8362 2.24573 26.6261V9.87393C2.24573 5.66376 5.66376 2.24573 9.87393 2.24573H26.6261C30.8362 2.24573 34.2543 5.66376 34.2543 9.87393V26.6261C34.2543 30.8362 30.8362 34.2543 26.6261 34.2543H23.6813C23.4829 34.2543 23.2927 34.3331 23.1525 34.4733C23.0122 34.6136 22.9334 34.8038 22.9334 35.0021C22.9334 35.2005 23.0122 35.3907 23.1525 35.531C23.2927 35.6712 23.4829 35.75 23.6813 35.75Z" fill="white" stroke="white" stroke-width="1.5"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M22.937 15.2176V18.0505C22.937 18.4633 23.272 18.7996 23.6848 18.7996H27.4451L26.9222 22.0794H23.6854C23.5872 22.0793 23.4899 22.0986 23.399 22.1362C23.3082 22.1737 23.2257 22.2288 23.1562 22.2982C23.0867 22.3677 23.0316 22.4502 22.9939 22.5409C22.9563 22.6317 22.937 22.729 22.937 22.8273V35.0085C22.937 35.4219 23.272 35.7575 23.6848 35.7575C24.0976 35.7575 24.4327 35.4219 24.4327 35.0091V23.5751H27.5617C27.9291 23.5751 28.2414 23.3071 28.3 22.9451L29.0599 18.1672C29.0767 18.0604 29.0702 17.9512 29.0409 17.8472C29.0115 17.7432 28.9599 17.6468 28.8897 17.5646C28.8194 17.4824 28.7322 17.4165 28.6341 17.3712C28.5359 17.326 28.4291 17.3026 28.321 17.3026H24.4327V15.0746L23.6848 15.8225L23.704 15.789L24.4183 15.0381C24.3806 14.2723 24.4901 13.6591 25.0663 13.6082H28.6686C29.0814 13.6082 29.4165 13.2726 29.4165 12.8597V8.42999C29.4164 8.26063 29.3588 8.09631 29.2531 7.96393C29.1475 7.83154 29 7.73894 28.8349 7.70128C25.9775 7.04794 22.7294 7.25375 20.5755 8.4964C19.0044 9.40222 17.9753 10.8315 17.9532 12.852V17.3026H14.4783C14.0655 17.3026 13.7305 17.6371 13.7305 18.0505V22.8267C13.7305 23.2395 14.0655 23.5745 14.4783 23.5745H17.9532V35.0079C17.9532 35.4213 18.2882 35.7569 18.7017 35.7569C19.1139 35.7569 19.4495 35.4213 19.4495 35.0085V22.8273C19.4495 22.4144 19.1145 22.0794 18.7017 22.0794H15.2268V18.7996H18.7017C19.1145 18.7996 19.4495 18.4639 19.4495 18.0505V12.8657C19.4705 11.0529 20.6653 9.93529 22.2878 9.35076C23.9289 8.75846 25.9942 8.69145 27.9201 9.04085V12.1125H25.0429C25.0274 12.1125 25.013 12.1125 24.9975 12.1137C23.6244 12.1974 22.8359 13.3228 22.9244 15.1129C22.9256 15.1482 22.931 15.1835 22.937 15.2176Z" fill="white" stroke="white" stroke-width="1.5"/>
</svg>

            </a>
            <?php endif; ?>
            <?php if (!empty($twitter_url)) : ?>
            <a href="<?php echo $twitter_url; ?>" target="_blank" rel="noopener noreferrer" class="social-icon social-twitter" title="X (Twitter)">
                <svg width="37" height="37" viewBox="0 0 37 37" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M28.1077 0.75H8.39234C4.17834 0.75 0.75 4.17834 0.75 8.39234V28.1077C0.75 32.3217 4.17834 35.75 8.39234 35.75H28.1077C32.3217 35.75 35.75 32.3217 35.75 28.1077V8.39234C35.75 4.17834 32.3217 0.75 28.1077 0.75ZM33.9459 28.1077C33.9459 31.3269 31.3269 33.9459 28.1077 33.9459H8.39234C5.17313 33.9459 2.55412 31.3269 2.55412 28.1077V8.39234C2.55412 5.17313 5.17313 2.55412 8.39234 2.55412H28.1077C31.3269 2.55412 33.9459 5.17313 33.9459 8.39234V28.1077ZM20.7344 16.595L27.5431 8.1088C27.8549 7.7202 27.7926 7.15247 27.4041 6.84065C27.0154 6.52897 26.4477 6.5911 26.1359 6.97978L19.5778 15.1535L13.0198 6.97978C12.9352 6.87443 12.8281 6.78941 12.7064 6.73098C12.5846 6.67255 12.4512 6.64221 12.3162 6.6422H7.00475C6.83473 6.6422 6.66817 6.69025 6.52428 6.78081C6.38038 6.87138 6.26501 7.00076 6.19147 7.15406C6.11792 7.30735 6.08919 7.4783 6.1086 7.64721C6.12801 7.81612 6.19475 7.97611 6.30114 8.10873L15.7656 19.905L8.95681 28.3912C8.64506 28.7798 8.70734 29.3475 9.09588 29.6593C9.25569 29.7879 9.45471 29.858 9.65984 29.8578C9.79503 29.858 9.92853 29.8277 10.0504 29.7693C10.1723 29.7109 10.2795 29.6257 10.364 29.5202L16.9222 21.3465L23.4802 29.5202C23.5647 29.6256 23.6718 29.7106 23.7936 29.769C23.9153 29.8275 24.0487 29.8578 24.1838 29.8578H29.4966C29.9948 29.8578 30.3987 29.454 30.3987 28.9557C30.3987 28.7258 30.3127 28.516 30.171 28.3566L20.7344 16.595ZM24.6166 28.0537L8.88508 8.44632H11.8834L27.6149 28.0537H24.6166Z" fill="white" stroke="white" stroke-width="1.5"/>
</svg>


            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="customshots-request-wrapper">
            <button type="button"
                    class="customshots-request-btn"
                    id="customshots-request-btn"
                    data-vendor-id="<?php echo esc_attr($vendor_id); ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#customshotModal">
                <?php esc_html_e('Request Custom Shoots', 'vendor-customshot'); ?>
            </button>
        </div>
    </div>

    <style>
    /* Store Actions Container */
    .customshots-store-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    /* Social Icons */
    .vendor-social-icons {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .vendor-social-icons .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #fff;
        color: #1a1a1a;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .vendor-social-icons .social-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .vendor-social-icons .social-icon svg {
        width: 20px;
        height: 20px;
    }

    /* Request Button */
    .customshots-request-wrapper {
        display: inline-block;
    }

    .customshots-request-btn {
        background: linear-gradient(135deg, #00c4aa 0%, #00bcd4 100%);
        color: #fff;
        border: none;
        padding: 14px 28px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        box-shadow: 0 4px 15px rgba(0, 196, 170, 0.3);
    }

    .customshots-request-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 196, 170, 0.4);
        background: linear-gradient(135deg, #00b09a 0%, #00a5bb 100%);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .customshots-store-actions {
            flex-direction: column;
            gap: 15px;
        }

        .vendor-social-icons {
            order: 2;
        }

        .customshots-request-wrapper {
            order: 1;
            width: 100%;
        }

        .customshots-request-btn {
            width: 100%;
        }
    }
    </style>

    <!-- Custom Shot Request Modal -->
    <div class="modal fade" id="customshotModal" tabindex="-1" aria-labelledby="customshotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content customshot-modal-content">
                <div class="modal-body customshot-modal-body">
                    <h4 class="customshot-modal-title"><?php esc_html_e('Request Custom Shoot', 'vendor-customshot'); ?></h4>

                    <form id="customshot-request-form">
                        <input type="hidden" name="vendor_id" value="<?php echo esc_attr($vendor_id); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="customshot-field">
                                    <label for="customshot-title"><?php esc_html_e('Title', 'vendor-customshot'); ?></label>
                                    <input type="text" id="customshot-title" name="title" placeholder="<?php esc_attr_e('Enter Title Here', 'vendor-customshot'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="customshot-field">
                                    <label for="customshot-type"><?php esc_html_e('Type of Shoot', 'vendor-customshot'); ?></label>
                                    <select id="customshot-type" name="shoot_type" required>
                                        <option value="" selected disabled><?php esc_html_e('Select Type', 'vendor-customshot'); ?></option>
                                        <?php
                                        $shoot_types = customshots_get_shoot_types();
                                        foreach ($shoot_types as $term) :
                                        ?>
                                            <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="customshot-field customshot-field-brief">
                            <label for="customshot-brief"><?php esc_html_e('Brief', 'vendor-customshot'); ?></label>
                            <textarea id="customshot-brief" name="brief" rows="3" placeholder="<?php esc_attr_e('Describe your vision or concept for the shoot', 'vendor-customshot'); ?>" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="customshot-field">
                                    <label for="customshot-usage"><?php esc_html_e('Usage Type', 'vendor-customshot'); ?></label>
                                    <select id="customshot-usage" name="usage_type" required>
                                        <option value="" selected disabled><?php esc_html_e('Select Usage Type', 'vendor-customshot'); ?></option>
                                        <?php
                                        $usage_types = customshots_get_usage_types();
                                        foreach ($usage_types as $term) :
                                        ?>
                                            <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="customshot-field">
                                    <label for="customshot-deliverables"><?php esc_html_e('Deliverables', 'vendor-customshot'); ?></label>
                                    <input type="text" id="customshot-deliverables" name="deliverables" placeholder="<?php esc_attr_e('e.g. 10 images, 1 video', 'vendor-customshot'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="customshot-field">
                                    <label for="customshot-budget"><?php esc_html_e('Budget (USD)', 'vendor-customshot'); ?></label>
                                    <div class="customshot-budget-input">
                                        <span class="customshot-currency">$</span>
                                        <input type="number" id="customshot-budget" name="budget" placeholder="" min="0" step="1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="customshot-field">
                                    <label for="customshot-date"><?php esc_html_e('Date', 'vendor-customshot'); ?></label>
                                    <input type="date" id="customshot-date" name="shoot_date">
                                </div>
                            </div>
                        </div>

                        <div class="customshot-modal-footer">
                            <button type="button" class="customshot-btn customshot-btn-cancel" data-bs-dismiss="modal"><?php esc_html_e('Cancel', 'vendor-customshot'); ?></button>
                            <button type="submit" class="customshot-btn customshot-btn-submit"><?php esc_html_e('Request Custom Shoot', 'vendor-customshot'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Shot Modal Styles */
        .customshot-modal-content {
            border-radius: 16px;
            border: none;
            overflow: hidden;
        }

        .customshot-modal-body {
            padding: 32px 40px;
        }

        .customshot-modal-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 24px;
        }

        .customshot-field {
            display: flex;
            align-items: center;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            background: #fff;
        }

        .customshot-field-brief {
            align-items: flex-start;
            border: 2px solid #00bcd4;
        }

        .customshot-field label {
            font-size: 14px;
            font-weight: 500;
            color: #1a1a1a;
            min-width: 100px;
            margin: 0;
            padding-right: 12px;
            border-right: 1px solid #e5e5e5;
        }

        .customshot-field-brief label {
            padding-top: 4px;
        }

        /* Override WCFM styles for customshot modal inputs */
        #customshotModal .customshot-field input[type="text"],
        #customshotModal .customshot-field input[type="email"],
        #customshotModal .customshot-field input[type="number"],
        #customshotModal .customshot-field input[type="date"],
        #customshotModal .customshot-field select,
        #customshotModal .customshot-field textarea {
            border: none !important;
            color: #666 !important;
            padding: 0 0 0 12px !important;
            margin: 0 !important;
            background-color: transparent !important;
            height: auto !important;
            outline: 0 !important;
            flex: 1;
            font-size: 14px;
            box-shadow: none !important;
        }

        .customshot-field input,
        .customshot-field select,
        .customshot-field textarea {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-size: 14px;
            color: #666;
            padding-left: 12px;
            margin: 0;
        }

        .customshot-field input::placeholder,
        .customshot-field textarea::placeholder {
            color: #999;
        }

        #customshotModal .customshot-field select {
            cursor: pointer;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2300bcd4' d='M6 8L1 3h10z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 0 center !important;
            padding-right: 20px !important;
        }

        #customshotModal .customshot-field textarea {
            resize: none;
            min-height: 60px;
        }

        .customshot-budget-input {
            display: flex;
            align-items: center;
            flex: 1;
            padding-left: 12px;
        }

        .customshot-currency {
            color: #999;
            font-size: 14px;
            margin-right: 4px;
        }

        .customshot-budget-input input {
            padding-left: 0;
        }

        .customshot-field input[type="date"] {
            cursor: pointer;
        }

        .customshot-modal-footer {
            display: flex;
            gap: 16px;
            margin-top: 24px;
            padding-top: 0;
            border: none;
        }

        .customshot-btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .customshot-btn-cancel {
            background: #1a1a1a;
            color: #fff;
        }

        .customshot-btn-cancel:hover {
            background: #333;
        }

        .customshot-btn-submit {
            background: #00bcd4;
            color: #fff;
        }

        .customshot-btn-submit:hover {
            background: #00a5bb;
        }

        /* Remove default Bootstrap modal header */
        #customshotModal .modal-header {
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .customshot-modal-body {
                padding: 24px 20px;
            }

            .customshot-field {
                flex-direction: column;
                align-items: flex-start;
            }

            .customshot-field label {
                border-right: none;
                border-bottom: 1px solid #e5e5e5;
                padding-bottom: 8px;
                margin-bottom: 8px;
                width: 100%;
                min-width: auto;
            }

            .customshot-field input,
            .customshot-field select,
            .customshot-field textarea {
                padding-left: 0;
                width: 100%;
            }

            .customshot-budget-input {
                padding-left: 0;
                width: 100%;
            }

            .customshot-modal-footer {
                flex-direction: column;
            }
        }

        /* Loading state */
        .customshot-btn-submit.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .customshot-btn-submit.loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-left: 8px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: customshot-spin 0.8s linear infinite;
            vertical-align: middle;
        }

        @keyframes customshot-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Success/Error messages */
        .customshot-message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .customshot-message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .customshot-message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $form = $('#customshot-request-form');
            var $submitBtn = $form.find('.customshot-btn-submit');
            var $modalBody = $('.customshot-modal-body');
            var originalBtnText = $submitBtn.text();
            var isSubmitting = false;

            // Use .off().on() to prevent duplicate handlers if script loads more than once
            $form.off('submit').on('submit', function(e) {
                e.preventDefault();

                // Prevent double submission
                if (isSubmitting) return;
                isSubmitting = true;

                // Remove any existing messages
                $('.customshot-message').remove();

                // Validate required fields
                var title = $('#customshot-title').val();
                var shootType = $('#customshot-type').val();
                var brief = $('#customshot-brief').val();
                var usageType = $('#customshot-usage').val();

                if (!title || !shootType || !brief || !usageType) {
                    showMessage('<?php echo esc_js(__('Please fill in all required fields.', 'vendor-customshot')); ?>', 'error');
                    isSubmitting = false;
                    return;
                }

                // Show loading state
                $submitBtn.addClass('loading').prop('disabled', true);

                // Prepare form data
                var formData = {
                    action: 'customshots_submit_request',
                    nonce: customshots_ajax.nonce,
                    title: title,
                    shoot_type: shootType,
                    brief: brief,
                    usage_type: usageType,
                    deliverables: $('#customshot-deliverables').val(),
                    budget: $('#customshot-budget').val(),
                    shoot_date: $('#customshot-date').val(),
                    vendor_id: $('input[name="vendor_id"]').val()
                };

                // Send AJAX request
                $.ajax({
                    url: customshots_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $submitBtn.removeClass('loading').prop('disabled', false);
                        isSubmitting = false;

                        if (response.success) {
                            showMessage(response.data.message, 'success');

                            // Reset form
                            $form[0].reset();

                            // Close modal after 2 seconds
                            setTimeout(function() {
                                $('#customshotModal').modal('hide');
                                $('.customshot-message').remove();
                            }, 2000);
                        } else {
                            showMessage(response.data.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        $submitBtn.removeClass('loading').prop('disabled', false);
                        isSubmitting = false;
                        showMessage('<?php echo esc_js(__('An error occurred. Please try again.', 'vendor-customshot')); ?>', 'error');
                    }
                });
            });

            // Helper function to show messages
            function showMessage(message, type) {
                var messageClass = type === 'success' ? 'customshot-message-success' : 'customshot-message-error';
                var $message = $('<div class="customshot-message ' + messageClass + '">' + message + '</div>');

                // Insert at the top of modal body, after the title
                $('.customshot-modal-title').after($message);

                // Scroll to top of modal
                $modalBody.scrollTop(0);
            }

            // Clear messages when modal is closed
            $('#customshotModal').on('hidden.bs.modal', function() {
                $('.customshot-message').remove();
                $form[0].reset();
            });
        });
    </script>
    <?php
}


/* ========================================
   WCFM Dashboard Integration
   ======================================== */

/**
 * Initialize WCFM integration after all plugins are loaded
 */
add_action('plugins_loaded', 'wcfm_customshots_plugin_init');

function wcfm_customshots_plugin_init() {
    // Exit if WCFM not installed
    if (!class_exists('WCFMmp') && !class_exists('WCFM')) {
        return;
    }

    // Add all the filters and actions for WCFM dashboard
    add_filter('wcfm_query_vars', 'wcfm_customshots_query_vars', 50);
    add_filter('wcfm_endpoint_title', 'wcfm_customshots_endpoint_title', 50, 2);
    add_action('init', 'wcfm_customshots_init', 50);
    add_filter('wcfm_endpoints_slug', 'wcfm_customshots_endpoints_slug');
    add_filter('wcfm_menus', 'wcfm_customshots_menus', 20);
    add_action('wcfm_load_views', 'wcfm_customshots_load_views', 50);
    add_action('before_wcfm_load_views', 'wcfm_customshots_load_views', 50);
    add_action('wcfm_load_scripts', 'wcfm_customshots_load_scripts');
    add_action('after_wcfm_load_scripts', 'wcfm_customshots_load_scripts');
    add_action('wcfm_load_styles', 'wcfm_customshots_load_styles');
    add_action('after_wcfm_load_styles', 'wcfm_customshots_load_styles');
    add_action('after_wcfm_ajax_controller', 'wcfm_customshots_ajax_controller');

    // Services & Subscriptions menu
    add_filter('wcfm_query_vars', 'wcfm_services_subscriptions_query_vars', 51);
    add_filter('wcfm_endpoint_title', 'wcfm_services_subscriptions_endpoint_title', 51, 2);
    add_filter('wcfm_endpoints_slug', 'wcfm_services_subscriptions_endpoints_slug', 51);
    add_filter('wcfm_menus', 'wcfm_services_subscriptions_menus', 21);
    add_action('wcfm_load_views', 'wcfm_services_subscriptions_load_views', 51);
    add_action('before_wcfm_load_views', 'wcfm_services_subscriptions_load_views', 51);
    add_action('wcfm_load_scripts', 'wcfm_services_subscriptions_load_scripts', 51);
    add_action('after_wcfm_load_scripts', 'wcfm_services_subscriptions_load_scripts', 51);
    add_action('wcfm_load_styles', 'wcfm_services_subscriptions_load_styles', 51);
    add_action('after_wcfm_load_styles', 'wcfm_services_subscriptions_load_styles', 51);
    add_action('after_wcfm_ajax_controller', 'wcfm_services_subscriptions_ajax_controller', 51);

    // KYC / ID Verification menu
    add_filter('wcfm_query_vars', 'wcfm_kyc_query_vars', 52);
    add_filter('wcfm_endpoint_title', 'wcfm_kyc_endpoint_title', 52, 2);
    add_filter('wcfm_endpoints_slug', 'wcfm_kyc_endpoints_slug', 52);
    add_filter('wcfm_menus', 'wcfm_kyc_menus', 22);
    add_action('wcfm_load_views', 'wcfm_kyc_load_views', 52);
    add_action('before_wcfm_load_views', 'wcfm_kyc_load_views', 52);
    add_action('wcfm_load_styles', 'wcfm_kyc_load_styles', 52);
    add_action('after_wcfm_load_styles', 'wcfm_kyc_load_styles', 52);

    // Payment Settings menu
    add_filter('wcfm_query_vars', 'wcfm_payment_settings_query_vars', 53);
    add_filter('wcfm_endpoint_title', 'wcfm_payment_settings_endpoint_title', 53, 2);
    add_filter('wcfm_endpoints_slug', 'wcfm_payment_settings_endpoints_slug', 53);
    add_filter('wcfm_menus', 'wcfm_payment_settings_menus', 24);
    add_action('wcfm_load_views', 'wcfm_payment_settings_load_views', 53);
    add_action('before_wcfm_load_views', 'wcfm_payment_settings_load_views', 53);

    // Account Management menu (links to profile page)
    add_filter('wcfm_menus', 'wcfm_account_management_menus', 10);

}

/**
 * WCFM - Custom Shots Query Var
 */
function wcfm_customshots_query_vars($query_vars) {
    $wcfm_modified_endpoints = (array) get_option('wcfm_endpoints');

    $query_custom_vars = array(
        'wcfm-customshots' => !empty($wcfm_modified_endpoints['wcfm-customshots']) ? $wcfm_modified_endpoints['wcfm-customshots'] : 'customshots',
        'wcfm-customshot-messages' => !empty($wcfm_modified_endpoints['wcfm-customshot-messages']) ? $wcfm_modified_endpoints['wcfm-customshot-messages'] : 'customshot-messages',
    );

    $query_vars = array_merge($query_vars, $query_custom_vars);

    return $query_vars;
}

/**
 * WCFM - Custom Shots End Point Title
 */
function wcfm_customshots_endpoint_title($title, $endpoint) {
    global $wp;
    switch ($endpoint) {
        case 'wcfm-customshots':
            $title = __('Custom Shots', 'vendor-customshot');
            break;
        case 'wcfm-customshot-messages':
            $title = __('Messages', 'vendor-customshot');
            break;
    }

    return $title;
}

/**
 * WCFM - Custom Shots Endpoint Initialize
 */
function wcfm_customshots_init() {
    global $WCFM_Query;

    // Initialize WCFM End points
    $WCFM_Query->init_query_vars();
    $WCFM_Query->add_endpoints();

    if (!get_option('wcfm_updated_end_point_customshots')) {
        // Flush rules after endpoint update
        flush_rewrite_rules();
        update_option('wcfm_updated_end_point_customshots', 1);
    }
}

/**
 * WCFM - Custom Shots Endpoint Slug
 */
function wcfm_customshots_endpoints_slug($endpoints) {
    $custom_endpoints = array(
        'wcfm-customshots' => 'customshots',
        'wcfm-customshot-messages' => 'customshot-messages',
    );

    $endpoints = array_merge($endpoints, $custom_endpoints);

    return $endpoints;
}

/**
 * Get Custom Shots URL
 */
if (!function_exists('get_wcfm_customshots_url')) {
    function get_wcfm_customshots_url() {
        global $WCFM;
        $wcfm_page = get_wcfm_page();
        $wcfm_customshots_url = wcfm_get_endpoint_url('wcfm-customshots', '', $wcfm_page);
        return $wcfm_customshots_url;
    }
}

/**
 * Get Custom Shot Messages URL
 */
if (!function_exists('get_wcfm_customshot_messages_url')) {
    function get_wcfm_customshot_messages_url($shot_id = '') {
        global $WCFM;
        $wcfm_page = get_wcfm_page();
        $url = wcfm_get_endpoint_url('wcfm-customshot-messages', '', $wcfm_page);
        if ($shot_id) {
            $url = add_query_arg('shot_id', $shot_id, $url);
        }
        return $url;
    }
}

/**
 * WCFM - Custom Shots Menu
 */
function wcfm_customshots_menus($menus) {
    global $WCFM;

    // Get unread message count for badge
    $vendor_id = get_current_user_id();
    $unread_count = 0;
    $conversations = Customshot_Chat::get_vendor_conversations($vendor_id);
    foreach ($conversations as $conv) {
        $unread_count += $conv['unread_count'];
    }

    $custom_menus = array(
        'wcfm-customshots' => array(
            'label'    => __('Custom Shots', 'vendor-customshot'),
            'url'      => get_wcfm_customshots_url(),
            'icon'     => 'camera',
            'priority' => 55
        ),
        'wcfm-customshot-messages' => array(
            'label'    => __('Messages', 'vendor-customshot'),
            'url'      => get_wcfm_customshot_messages_url(),
            'icon'     => 'comments',
            'priority' => 56,
            'count'    => $unread_count > 0 ? $unread_count : '',
        ),
    );

    $menus = array_merge($menus, $custom_menus);

    return $menus;
}

/**
 * WCFM - Custom Shots Views
 */
function wcfm_customshots_load_views($end_point) {
    global $WCFM, $WCFMu;
    $plugin_path = trailingslashit(dirname(__FILE__));

    switch ($end_point) {
        case 'wcfm-customshots':
            // Check if viewing single request detail
            if (isset($_GET['tab']) && $_GET['tab'] === 'view' && isset($_GET['request_id'])) {
                require_once($plugin_path . 'views/wcfm-view-customshot-detail.php');
            } else {
                require_once($plugin_path . 'views/wcfm-view-customshots.php');
            }
            break;

        case 'wcfm-customshot-messages':
            // Check if viewing specific chat
            if (isset($_GET['shot_id']) && intval($_GET['shot_id']) > 0) {
                require_once($plugin_path . 'views/wcfm-view-customshot-chat.php');
            } else {
                // Auto-redirect to most recent conversation if any exist
                $vendor_id = get_current_user_id();
                $conversations = Customshot_Chat::get_vendor_conversations($vendor_id);
                if (!empty($conversations)) {
                    // Redirect to the first (most recent) conversation
                    $first_conv = reset($conversations);
                    $chat_url = get_wcfm_customshot_messages_url($first_conv['shot_id']);
                    wp_redirect($chat_url);
                    exit;
                }
                // No conversations - show empty state
                require_once($plugin_path . 'views/wcfm-view-customshot-messages.php');
            }
            break;
    }
}

/**
 * WCFM - Custom Shots Scripts
 */
function wcfm_customshots_load_scripts($end_point) {
    global $WCFM;
    $plugin_url = trailingslashit(plugins_url('', __FILE__));

    switch ($end_point) {
        case 'wcfm-customshots':
            wp_enqueue_script('wcfm_customshots_js', $plugin_url . 'js/wcfm-script-customshots.js', array('jquery'), $WCFM->version, true);

            // Localize script
            wp_localize_script('wcfm_customshots_js', 'wcfm_customshots_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('wcfm_customshots_nonce'),
            ));
            break;

        case 'wcfm-customshot-messages':
            wp_enqueue_media();
            wp_enqueue_script('wcfm_customshot_chat_js', $plugin_url . 'js/wcfm-script-chat.js', array('jquery'), $WCFM->version, true);

            // Get shot_id if in chat view
            $shot_id = isset($_GET['shot_id']) ? intval($_GET['shot_id']) : 0;

            // Localize script
            wp_localize_script('wcfm_customshot_chat_js', 'wcfm_chat_ajax', array(
                'ajax_url'    => admin_url('admin-ajax.php'),
                'nonce'       => wp_create_nonce('wcfm_chat_nonce'),
                'shot_id'     => $shot_id,
                'user_id'     => get_current_user_id(),
                'poll_interval' => 5000, // Poll every 5 seconds
                'strings'     => array(
                    'sending'       => __('Sending...', 'vendor-customshot'),
                    'send_error'    => __('Failed to send message. Please try again.', 'vendor-customshot'),
                    'upload_error'  => __('Failed to upload file. Please try again.', 'vendor-customshot'),
                    'file_too_large'=> __('File is too large. Maximum size is 10MB.', 'vendor-customshot'),
                ),
            ));
            break;
    }
}

/**
 * WCFM - Custom Shots Styles
 */
function wcfm_customshots_load_styles($end_point) {
    global $WCFM, $WCFMu;
    $plugin_url  = trailingslashit(plugins_url('', __FILE__));
    $plugin_path = trailingslashit(plugin_dir_path(__FILE__));

    switch ($end_point) {

        case 'wcfm-customshots':
            wp_enqueue_style(
                'wcfm_customshots_css',
                $plugin_url . 'css/wcfm-style-customshots.css',
                array(),
                filemtime($plugin_path . 'css/wcfm-style-customshots.css')
            );
            break;

        case 'wcfm-customshot-messages':
            wp_enqueue_style(
                'wcfm_customshot_chat_css',
                $plugin_url . 'css/wcfm-style-chat.css',
                array(),
                filemtime($plugin_path . 'css/wcfm-style-chat.css')
            );
            break;
    }
}


/**
 * WCFM - Custom Shots Ajax Controllers
 */
function wcfm_customshots_ajax_controller() {
    global $WCFM, $WCFMu;

    $plugin_path = trailingslashit(dirname(__FILE__));

    $controller = '';
    if (isset($_POST['controller'])) {
        $controller = $_POST['controller'];

        switch ($controller) {
            case 'wcfm-customshots':
                require_once($plugin_path . 'controllers/wcfm-controller-customshots.php');
                new WCFM_Customshots_Controller();
                break;

            case 'wcfm-customshot-chat':
                require_once($plugin_path . 'controllers/wcfm-controller-chat.php');
                new WCFM_Customshot_Chat_Controller();
                break;
        }
    }
}

/* ========================================
   WooCommerce My Account - Buyer Dashboard
   ======================================== */

/**
 * Register custom endpoints for My Account
 */
add_action('init', 'customshots_buyer_add_endpoint');

function customshots_buyer_add_endpoint() {
    add_rewrite_endpoint('custom-shots', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('custom-shots-messages', EP_ROOT | EP_PAGES);
}

/**
 * Add custom shots to My Account menu
 */
add_filter('woocommerce_account_menu_items', 'customshots_buyer_menu_items');

function customshots_buyer_menu_items($items) {
    // Get unread message count for buyer
    $buyer_id = get_current_user_id();
    $unread_count = 0;
    if ($buyer_id) {
        $conversations = Customshot_Chat::get_buyer_conversations($buyer_id);
        foreach ($conversations as $conv) {
            $unread_count += $conv['unread_count'];
        }
    }

    // Insert after dashboard
    $new_items = array();
    foreach ($items as $key => $value) {
        $new_items[$key] = $value;
        if ($key === 'dashboard') {
            $new_items['custom-shots'] = __('Custom Shots', 'vendor-customshot');
            $messages_label = __('Messages / Inquiries', 'vendor-customshot');
            if ($unread_count > 0) {
                $messages_label .= ' (' . $unread_count . ')';
            }
            $new_items['custom-shots-messages'] = $messages_label;
        }
    }
    return $new_items;
}

/**
 * Custom shots endpoint content
 */
add_action('woocommerce_account_custom-shots_endpoint', 'customshots_buyer_endpoint_content');

function customshots_buyer_endpoint_content() {
    $plugin_path = CUSTOMSHOTS_PLUGIN_PATH;

    // Enqueue styles
    // wp_enqueue_style('customshots-buyer-css', CUSTOMSHOTS_PLUGIN_URL . 'css/buyer-customshots.css', array(), '1.0.0');
    wp_enqueue_style(
        'customshots-buyer-css',
        CUSTOMSHOTS_PLUGIN_URL . 'css/buyer-customshots.css',
        array(),
        filemtime(CUSTOMSHOTS_PLUGIN_PATH . 'css/buyer-customshots.css')
    );


    // Enqueue scripts
    wp_enqueue_script('customshots-buyer-js', CUSTOMSHOTS_PLUGIN_URL . 'js/buyer-customshots.js', array('jquery'), '1.0.0', true);
    wp_localize_script('customshots-buyer-js', 'customshots_buyer_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('customshots_buyer_nonce'),
    ));

    // Check if viewing specific request or chat
    if (isset($_GET['view']) && isset($_GET['id'])) {
        $shot_id = intval($_GET['id']);
        require_once($plugin_path . 'views/buyer/buyer-customshot-detail.php');
    } elseif (isset($_GET['chat']) && isset($_GET['id'])) {
        $shot_id = intval($_GET['id']);
        wp_enqueue_media();
        wp_enqueue_script('wcfm_customshot_chat_js', CUSTOMSHOTS_PLUGIN_URL . 'js/wcfm-script-chat.js', array('jquery'), '1.0.0', true);
        wp_localize_script('wcfm_customshot_chat_js', 'wcfm_chat_ajax', array(
            'ajax_url'      => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('wcfm_chat_nonce'),
            'shot_id'       => $shot_id,
            'user_id'       => get_current_user_id(),
            'poll_interval' => 5000,
            'strings'       => array(
                'sending'        => __('Sending...', 'vendor-customshot'),
                'send_error'     => __('Failed to send message.', 'vendor-customshot'),
                'upload_error'   => __('Failed to upload file.', 'vendor-customshot'),
                'file_too_large' => __('File is too large. Max 10MB.', 'vendor-customshot'),
            ),
        ));
        // wp_enqueue_style('wcfm_customshot_chat_css', CUSTOMSHOTS_PLUGIN_URL . 'css/wcfm-style-chat.css', array(), '1.0.0');
        wp_enqueue_style(
            'wcfm_customshot_chat_css',
            CUSTOMSHOTS_PLUGIN_URL . 'css/wcfm-style-chat.css',
            array(),
            filemtime(CUSTOMSHOTS_PLUGIN_PATH . 'css/wcfm-style-chat.css')
        );
        require_once($plugin_path . 'views/buyer/buyer-customshot-chat.php');
    } else {
        require_once($plugin_path . 'views/buyer/buyer-customshots-list.php');
    }
}

/**
 * Custom shots messages endpoint content
 */
add_action('woocommerce_account_custom-shots-messages_endpoint', 'customshots_buyer_messages_endpoint_content');

function customshots_buyer_messages_endpoint_content() {
    $plugin_path = CUSTOMSHOTS_PLUGIN_PATH;

    // Enqueue styles
    // wp_enqueue_style('customshots-buyer-css', CUSTOMSHOTS_PLUGIN_URL . 'css/buyer-customshots.css', array(), '1.0.0');
    wp_enqueue_style(
        'customshots-buyer-css',
        CUSTOMSHOTS_PLUGIN_URL . 'css/buyer-customshots.css',
        array(),
        filemtime(CUSTOMSHOTS_PLUGIN_PATH . 'css/buyer-customshots.css')
    );


    // Enqueue scripts
    wp_enqueue_script('customshots-buyer-js', CUSTOMSHOTS_PLUGIN_URL . 'js/buyer-customshots.js', array('jquery'), '1.0.0', true);
    wp_localize_script('customshots-buyer-js', 'customshots_buyer_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('customshots_buyer_nonce'),
    ));

    // Check if viewing specific chat
    if (isset($_GET['id']) && intval($_GET['id']) > 0) {
        $shot_id = intval($_GET['id']);
        wp_enqueue_media();
        wp_enqueue_script('wcfm_customshot_chat_js', CUSTOMSHOTS_PLUGIN_URL . 'js/wcfm-script-chat.js', array('jquery'), '1.0.0', true);
        wp_localize_script('wcfm_customshot_chat_js', 'wcfm_chat_ajax', array(
            'ajax_url'      => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('wcfm_chat_nonce'),
            'shot_id'       => $shot_id,
            'user_id'       => get_current_user_id(),
            'poll_interval' => 5000,
            'strings'       => array(
                'sending'        => __('Sending...', 'vendor-customshot'),
                'send_error'     => __('Failed to send message.', 'vendor-customshot'),
                'upload_error'   => __('Failed to upload file.', 'vendor-customshot'),
                'file_too_large' => __('File is too large. Max 10MB.', 'vendor-customshot'),
            ),
        ));
        // wp_enqueue_style('wcfm_customshot_chat_css', CUSTOMSHOTS_PLUGIN_URL . 'css/wcfm-style-chat.css', array(), '1.0.0');
        wp_enqueue_style(
            'wcfm_customshot_chat_css',
            CUSTOMSHOTS_PLUGIN_URL . 'css/wcfm-style-chat.css',
            array(),
            filemtime(CUSTOMSHOTS_PLUGIN_PATH . 'css/wcfm-style-chat.css')
        );

        require_once($plugin_path . 'views/buyer/buyer-customshot-chat.php');
    } else {
        // Auto-redirect to most recent conversation if any exist
        $buyer_id = get_current_user_id();
        $conversations = Customshot_Chat::get_buyer_conversations($buyer_id);
        if (!empty($conversations)) {
            // Redirect to the first (most recent) conversation
            $first_conv = reset($conversations);
            $chat_url = wc_get_account_endpoint_url('custom-shots-messages') . '?id=' . $first_conv['shot_id'];
            wp_redirect($chat_url);
            exit;
        }
        // No conversations - show empty state
        require_once($plugin_path . 'views/buyer/buyer-customshot-messages.php');
    }
}

/**
 * Flush rewrite rules on activation
 */
register_activation_hook(__FILE__, 'customshots_flush_rewrite_rules');

function customshots_flush_rewrite_rules() {
    customshots_buyer_add_endpoint();
    flush_rewrite_rules();
}

/**
 * AJAX: Buyer accept quote
 */
add_action('wp_ajax_customshots_buyer_accept_quote', 'customshots_buyer_accept_quote');

function customshots_buyer_accept_quote() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customshots_buyer_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;
    $buyer_id = get_current_user_id();

    // Verify this shot belongs to this buyer
    $shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
    if ($shot_buyer_id != $buyer_id) {
        wp_send_json_error(array('message' => __('You do not have permission.', 'vendor-customshot')));
    }

    // Check status is quoted
    $status = get_post_meta($shot_id, '_customshot_status', true);
    if ($status !== 'quoted') {
        wp_send_json_error(array('message' => __('This quote is no longer available.', 'vendor-customshot')));
    }

    // Get the quote amount
    $quote_amount = floatval(get_post_meta($shot_id, '_customshot_vendor_quote', true));
    if ($quote_amount <= 0) {
        wp_send_json_error(array('message' => __('Invalid quote amount.', 'vendor-customshot')));
    }

    // Check buyer's wallet balance
    $wallet_balance = floatval(get_user_meta($buyer_id, '_modfolios_user_wallet', true));

    if ($wallet_balance < $quote_amount) {
        // Insufficient funds
        $shortfall = $quote_amount - $wallet_balance;
        wp_send_json_error(array(
            'message' => sprintf(
                __('Insufficient wallet balance. You need $%.2f more to accept this quote. Please add funds to your wallet.', 'vendor-customshot'),
                $shortfall
            ),
            'insufficient_funds' => true,
            'wallet_balance' => $wallet_balance,
            'quote_amount' => $quote_amount,
            'shortfall' => $shortfall,
        ));
    }

    // Deduct amount from buyer's wallet
    $new_balance = $wallet_balance - $quote_amount;
    update_user_meta($buyer_id, '_modfolios_user_wallet', $new_balance);

    // Store escrow information for admin to process later
    update_post_meta($shot_id, '_customshot_escrow_amount', $quote_amount);
    update_post_meta($shot_id, '_customshot_escrow_date', current_time('mysql'));
    update_post_meta($shot_id, '_customshot_escrow_status', 'held'); // held, released, refunded

    // Log the transaction
    $transaction_log = get_user_meta($buyer_id, '_modfolios_wallet_transactions', true);
    if (!is_array($transaction_log)) {
        $transaction_log = array();
    }
    $transaction_log[] = array(
        'type' => 'escrow_deduction',
        'amount' => -$quote_amount,
        'shot_id' => $shot_id,
        'date' => current_time('mysql'),
        'note' => sprintf(__('Custom Shot #%d - Escrow held', 'vendor-customshot'), $shot_id),
    );
    update_user_meta($buyer_id, '_modfolios_wallet_transactions', $transaction_log);

    // Update status to accepted
    update_post_meta($shot_id, '_customshot_status', 'accepted');
    update_post_meta($shot_id, '_customshot_accepted_date', current_time('mysql'));

    // Initialize chat
    Customshot_Chat::initialize_chat($shot_id);

    // Send email to vendor
    Customshot_Emails::send_accepted_to_vendor($shot_id);

    wp_send_json_success(array(
        'message'  => sprintf(
            __('Quote accepted! $%.2f has been deducted from your wallet and held in escrow. You can now start chatting with the vendor.', 'vendor-customshot'),
            $quote_amount
        ),
        'chat_url' => wc_get_account_endpoint_url('custom-shots') . '?chat=1&id=' . $shot_id,
        'new_balance' => $new_balance,
    ));
}

/**
 * AJAX: Buyer reject quote
 */
add_action('wp_ajax_customshots_buyer_reject_quote', 'customshots_buyer_reject_quote');

function customshots_buyer_reject_quote() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customshots_buyer_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;
    $buyer_id = get_current_user_id();

    // Verify this shot belongs to this buyer
    $shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
    if ($shot_buyer_id != $buyer_id) {
        wp_send_json_error(array('message' => __('You do not have permission.', 'vendor-customshot')));
    }

    // Update status
    update_post_meta($shot_id, '_customshot_status', 'quote_rejected');

    // Send email to vendor
    Customshot_Emails::send_rejected_to_vendor($shot_id);

    wp_send_json_success(array('message' => __('Quote declined.', 'vendor-customshot')));
}

/**
 * AJAX: Buyer approve deliverables
 */
add_action('wp_ajax_customshots_buyer_approve_deliverables', 'customshots_buyer_approve_deliverables');

function customshots_buyer_approve_deliverables() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customshots_buyer_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;
    $buyer_id = get_current_user_id();

    // Verify this shot belongs to this buyer
    $shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
    if ($shot_buyer_id != $buyer_id) {
        wp_send_json_error(array('message' => __('You do not have permission.', 'vendor-customshot')));
    }

    // Update deliverable status
    update_post_meta($shot_id, '_customshot_deliverable_status', 'approved');
    update_post_meta($shot_id, '_customshot_status', 'completed');

    // Send system message in chat
    Customshot_Chat::send_message($shot_id, 0, 'system', __('Deliverables have been approved! Project completed.', 'vendor-customshot'));

    wp_send_json_success(array('message' => __('Deliverables approved! Project completed.', 'vendor-customshot')));
}

/**
 * AJAX: Buyer request revision
 */
add_action('wp_ajax_customshots_buyer_request_revision', 'customshots_buyer_request_revision');

function customshots_buyer_request_revision() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customshots_buyer_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;
    $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';
    $buyer_id = get_current_user_id();

    // Verify this shot belongs to this buyer
    $shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
    if ($shot_buyer_id != $buyer_id) {
        wp_send_json_error(array('message' => __('You do not have permission.', 'vendor-customshot')));
    }

    // Update deliverable status
    update_post_meta($shot_id, '_customshot_deliverable_status', 'revision_requested');
    update_post_meta($shot_id, '_customshot_status', 'in_progress');

    // Send message in chat with revision reason
    $message = __('Revision requested', 'vendor-customshot');
    if ($reason) {
        $message .= ': ' . $reason;
    }
    Customshot_Chat::send_message($shot_id, $buyer_id, 'text', $message);

    wp_send_json_success(array('message' => __('Revision request sent to vendor.', 'vendor-customshot')));
}

/**
 * AJAX: Buyer approve single deliverable item
 */
add_action('wp_ajax_customshots_buyer_approve_single_item', 'customshots_buyer_approve_single_item');

function customshots_buyer_approve_single_item() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customshots_buyer_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;
    $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
    $buyer_id = get_current_user_id();

    // Verify this shot belongs to this buyer
    $shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
    if ($shot_buyer_id != $buyer_id) {
        wp_send_json_error(array('message' => __('You do not have permission.', 'vendor-customshot')));
    }

    // Get current approved items
    $approved_items = get_post_meta($shot_id, '_customshot_approved_items', true);
    if (!is_array($approved_items)) {
        $approved_items = array();
    }

    // Add this item if not already approved
    if (!in_array($attachment_id, $approved_items)) {
        $approved_items[] = $attachment_id;
        update_post_meta($shot_id, '_customshot_approved_items', $approved_items);
    }

    // Get total count of deliverables
    $deliverable_images = get_post_meta($shot_id, '_customshot_deliverable_images', true);
    $deliverable_videos = get_post_meta($shot_id, '_customshot_deliverable_videos', true);
    if (!is_array($deliverable_images)) $deliverable_images = array();
    if (!is_array($deliverable_videos)) $deliverable_videos = array();
    $total_count = count($deliverable_images) + count($deliverable_videos);
    $approved_count = count($approved_items);

    // Check if all items are approved
    $all_approved = ($approved_count >= $total_count && $total_count > 0);

    if ($all_approved) {
        // Auto-complete the project
        update_post_meta($shot_id, '_customshot_deliverable_status', 'approved');
        update_post_meta($shot_id, '_customshot_status', 'completed');
        Customshot_Chat::send_message($shot_id, 0, 'system', __('All deliverables approved! Project completed.', 'vendor-customshot'));
    }

    wp_send_json_success(array(
        'message' => __('Item approved.', 'vendor-customshot'),
        'approved_count' => $approved_count,
        'total_count' => $total_count,
        'all_approved' => $all_approved,
    ));
}

/**
 * AJAX: Buyer approve all deliverables at once
 */
add_action('wp_ajax_customshots_buyer_approve_all_deliverables', 'customshots_buyer_approve_all_deliverables');

function customshots_buyer_approve_all_deliverables() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customshots_buyer_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;
    $feedback = isset($_POST['feedback']) ? sanitize_textarea_field($_POST['feedback']) : '';
    $buyer_id = get_current_user_id();

    // Verify this shot belongs to this buyer
    $shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
    if ($shot_buyer_id != $buyer_id) {
        wp_send_json_error(array('message' => __('You do not have permission.', 'vendor-customshot')));
    }

    // Get all deliverables
    $deliverable_images = get_post_meta($shot_id, '_customshot_deliverable_images', true);
    $deliverable_videos = get_post_meta($shot_id, '_customshot_deliverable_videos', true);
    if (!is_array($deliverable_images)) $deliverable_images = array();
    if (!is_array($deliverable_videos)) $deliverable_videos = array();

    // Mark all as approved
    $all_items = array_merge($deliverable_images, $deliverable_videos);
    update_post_meta($shot_id, '_customshot_approved_items', $all_items);

    // Complete the project
    update_post_meta($shot_id, '_customshot_deliverable_status', 'approved');
    update_post_meta($shot_id, '_customshot_status', 'completed');

    // Send feedback as message if provided
    if (!empty($feedback)) {
        Customshot_Chat::send_message($shot_id, $buyer_id, 'text', __('Feedback: ', 'vendor-customshot') . $feedback);
    }

    // Send system message
    Customshot_Chat::send_message($shot_id, 0, 'system', __('All deliverables approved! Project completed.', 'vendor-customshot'));

    wp_send_json_success(array('message' => __('All deliverables approved! Project completed.', 'vendor-customshot')));
}

/**
 * AJAX: Handle chat actions via wcfm_ajax_controller for buyers
 * This allows the chat to work on the buyer's My Account page
 */
add_action('wp_ajax_wcfm_ajax_controller', 'customshots_handle_wcfm_ajax_for_buyers', 5);

function customshots_handle_wcfm_ajax_for_buyers() {
    // Only intercept if controller is wcfm-customshot-chat
    if (!isset($_POST['controller']) || $_POST['controller'] !== 'wcfm-customshot-chat') {
        return; // Let WCFM handle other controllers
    }

    $plugin_path = CUSTOMSHOTS_PLUGIN_PATH;
    require_once($plugin_path . 'controllers/wcfm-controller-chat.php');
    new WCFM_Customshot_Chat_Controller();

    // The controller sends JSON response and exits
}

/**
 * AJAX: Buyer download all deliverables
 */
add_action('wp_ajax_customshots_buyer_download_deliverables', 'customshots_buyer_download_deliverables');

function customshots_buyer_download_deliverables() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'customshots_buyer_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    $shot_id = isset($_POST['shot_id']) ? intval($_POST['shot_id']) : 0;
    $buyer_id = get_current_user_id();

    // Verify this shot belongs to this buyer
    $shot_buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);
    if ($shot_buyer_id != $buyer_id) {
        wp_send_json_error(array('message' => __('You do not have permission.', 'vendor-customshot')));
    }

    // Get all deliverables
    $deliverable_images = get_post_meta($shot_id, '_customshot_deliverable_images', true);
    $deliverable_videos = get_post_meta($shot_id, '_customshot_deliverable_videos', true);
    if (!is_array($deliverable_images)) $deliverable_images = array();
    if (!is_array($deliverable_videos)) $deliverable_videos = array();

    $all_items = array_merge($deliverable_images, $deliverable_videos);

    if (empty($all_items)) {
        wp_send_json_error(array('message' => __('No deliverables to download.', 'vendor-customshot')));
    }

    // For simplicity, return the first file URL (in production, you'd create a zip)
    // Or provide individual download links
    $download_urls = array();
    foreach ($all_items as $attachment_id) {
        $url = wp_get_attachment_url($attachment_id);
        if ($url) {
            $download_urls[] = $url;
        }
    }

    if (!empty($download_urls)) {
        // Return the first URL for now - in production create a ZIP
        wp_send_json_success(array(
            'download_url' => $download_urls[0],
            'all_urls' => $download_urls,
            'message' => __('Download starting...', 'vendor-customshot'),
        ));
    } else {
        wp_send_json_error(array('message' => __('No files available for download.', 'vendor-customshot')));
    }
}


/* ========================================
   Services & Subscriptions - WCFM Integration
   ======================================== */

/**
 * Services & Subscriptions Query Vars
 */
function wcfm_services_subscriptions_query_vars($query_vars) {
    $wcfm_modified_endpoints = (array) get_option('wcfm_endpoints');

    $query_custom_vars = array(
        'wcfm-services-subscriptions' => !empty($wcfm_modified_endpoints['wcfm-services-subscriptions']) ? $wcfm_modified_endpoints['wcfm-services-subscriptions'] : 'services-subscriptions',
    );

    $query_vars = array_merge($query_vars, $query_custom_vars);

    return $query_vars;
}

/**
 * Services & Subscriptions End Point Title
 */
function wcfm_services_subscriptions_endpoint_title($title, $endpoint) {
    switch ($endpoint) {
        case 'wcfm-services-subscriptions':
            $title = __('Services & Subscriptions', 'vendor-customshot');
            break;
    }
    return $title;
}

/**
 * Services & Subscriptions Endpoint Slug
 */
function wcfm_services_subscriptions_endpoints_slug($endpoints) {
    $custom_endpoints = array(
        'wcfm-services-subscriptions' => 'services-subscriptions',
    );

    $endpoints = array_merge($endpoints, $custom_endpoints);

    return $endpoints;
}

/**
 * Get Services & Subscriptions URL
 */
if (!function_exists('get_wcfm_services_subscriptions_url')) {
    function get_wcfm_services_subscriptions_url() {
        global $WCFM;
        $wcfm_page = get_wcfm_page();
        $url = wcfm_get_endpoint_url('wcfm-services-subscriptions', '', $wcfm_page);
        return $url;
    }
}

/**
 * Services & Subscriptions Menu
 */
function wcfm_services_subscriptions_menus($menus) {
    global $WCFM;

    $custom_menus = array(
        'wcfm-services-subscriptions' => array(
            'label'    => __('Services & Subscriptions', 'vendor-customshot'),
            'url'      => get_wcfm_services_subscriptions_url(),
            'icon'     => 'credit-card',
            'priority' => 57
        ),
    );

    $menus = array_merge($menus, $custom_menus);

    return $menus;
}

/**
 * Services & Subscriptions Views
 */
function wcfm_services_subscriptions_load_views($end_point) {
    global $WCFM, $WCFMu;
    $plugin_path = trailingslashit(dirname(__FILE__));

    switch ($end_point) {
        case 'wcfm-services-subscriptions':
            require_once($plugin_path . 'views/wcfm-view-services-subscriptions.php');
            break;
    }
}

/**
 * Services & Subscriptions Scripts
 */
function wcfm_services_subscriptions_load_scripts($end_point) {
    global $WCFM;
    $plugin_url = trailingslashit(plugins_url('', __FILE__));

    switch ($end_point) {
        case 'wcfm-services-subscriptions':
            wp_enqueue_script('wcfm_services_subscriptions_js', $plugin_url . 'js/wcfm-script-services-subscriptions.js', array('jquery'), $WCFM->version, true);

            // Localize script
            wp_localize_script('wcfm_services_subscriptions_js', 'wcfm_services_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('wcfm_services_nonce'),
            ));
            break;
    }
}

/**
 * Services & Subscriptions Styles
 */
function wcfm_services_subscriptions_load_styles($end_point) {
    global $WCFM, $WCFMu;
    $plugin_url = trailingslashit(plugins_url('', __FILE__));

    switch ($end_point) {
        case 'wcfm-services-subscriptions':
            // wp_enqueue_style('wcfm_services_subscriptions_css', $plugin_url . 'css/wcfm-style-services-subscriptions.css', array(), $WCFM->version);
            wp_enqueue_style(
                'wcfm_services_subscriptions_css',
                CUSTOMSHOTS_PLUGIN_URL . 'css/wcfm-style-services-subscriptions.css',
                array(),
                filemtime(CUSTOMSHOTS_PLUGIN_PATH . 'css/wcfm-style-services-subscriptions.css')
            );
            break;
    }
}

/**
 * Services & Subscriptions Ajax Controllers
 */
function wcfm_services_subscriptions_ajax_controller() {
    global $WCFM, $WCFMu;

    $plugin_path = trailingslashit(dirname(__FILE__));

    $controller = '';
    if (isset($_POST['controller'])) {
        $controller = $_POST['controller'];

        switch ($controller) {
            case 'wcfm-services-subscriptions':
                require_once($plugin_path . 'controllers/wcfm-controller-services-subscriptions.php');
                new WCFM_Services_Subscriptions_Controller();
                break;
        }
    }
}


/* ========================================
   KYC / ID Verification - WCFM Integration
   ======================================== */

/**
 * AJAX Handler for KYC Document Upload
 */
add_action('wp_ajax_wcfm_kyc_upload', 'wcfm_handle_kyc_upload');

function wcfm_handle_kyc_upload() {
    // Verify nonce
    if (!isset($_POST['kyc_nonce']) || !wp_verify_nonce($_POST['kyc_nonce'], 'kyc_upload_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in.', 'vendor-customshot')));
    }

    $user_id = get_current_user_id();

    // Check if file was uploaded
    if (empty($_FILES['kyc_document']) || $_FILES['kyc_document']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(array('message' => __('No file was uploaded or upload error occurred.', 'vendor-customshot')));
    }

    // Validate file size (5MB max)
    if ($_FILES['kyc_document']['size'] > 5 * 1024 * 1024) {
        wp_send_json_error(array('message' => __('File is too large. Maximum size is 5MB.', 'vendor-customshot')));
    }

    // Validate file type
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
    $file_type = wp_check_filetype($_FILES['kyc_document']['name']);
    $mime_type = $_FILES['kyc_document']['type'];

    if (!in_array($mime_type, $allowed_types)) {
        wp_send_json_error(array('message' => __('Invalid file type. Please upload JPG, PNG, or PDF.', 'vendor-customshot')));
    }

    // Include required files
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    // Handle the upload
    $attachment_id = media_handle_upload('kyc_document', 0);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error(array('message' => $attachment_id->get_error_message()));
    }

    // Delete old document if exists
    $old_document = get_user_meta($user_id, '_vendor_id_document', true);
    if ($old_document) {
        wp_delete_attachment($old_document, true);
    }

    // Save new document and update status (new meta keys)
    update_user_meta($user_id, '_vendor_id_document', $attachment_id);
    update_user_meta($user_id, '_vendor_id_verification_status', 'pending');
    update_user_meta($user_id, '_vendor_id_upload_date', current_time('mysql'));

    // Also update old meta keys for backwards compatibility with withdrawal modal
    update_user_meta($user_id, 'user_registration_vendor_id_verification', wp_get_attachment_url($attachment_id));
    update_user_meta($user_id, 'vendor_id_verification_status', 'pending');

    wp_send_json_success(array(
        'message' => __('Document uploaded successfully! It will be reviewed shortly.', 'vendor-customshot'),
        'file_url' => wp_get_attachment_url($attachment_id),
        'file_name' => basename(get_attached_file($attachment_id)),
    ));
}

/**
 * AJAX Handler for KYC Approval (Admin)
 */
add_action('wp_ajax_modfolio_approve_kyc', 'modfolio_handle_approve_kyc');

function modfolio_handle_approve_kyc() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'modfolio_kyc_actions')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    // Check if user is admin
    if (!current_user_can('administrator') && !current_user_can('shop_manager')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'vendor-customshot')));
    }

    $vendor_id = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 0;

    if (!$vendor_id) {
        wp_send_json_error(array('message' => __('Invalid vendor ID.', 'vendor-customshot')));
    }

    // Update verification status (new meta keys)
    update_user_meta($vendor_id, '_vendor_id_verification_status', 'approved');
    update_user_meta($vendor_id, '_vendor_id_approved_date', current_time('mysql'));
    delete_user_meta($vendor_id, '_vendor_id_rejection_reason');

    // Also update old meta key for backwards compatibility with withdrawal modal
    update_user_meta($vendor_id, 'vendor_id_verification_status', 'approved');

    // Send notification email to vendor
    $vendor = get_user_by('id', $vendor_id);
    if ($vendor) {
        $subject = __('Your ID Verification has been Approved', 'vendor-customshot');
        $message = sprintf(
            __("Hello %s,\n\nGreat news! Your ID verification has been approved.\n\nYou can now access all vendor features on our platform.\n\nThank you,\nThe Modfolios Team", 'vendor-customshot'),
            $vendor->display_name
        );
        wp_mail($vendor->user_email, $subject, $message);
    }

    wp_send_json_success(array('message' => __('Vendor verification approved successfully.', 'vendor-customshot')));
}

/**
 * AJAX Handler for KYC Rejection (Admin)
 */
add_action('wp_ajax_modfolio_reject_kyc', 'modfolio_handle_reject_kyc');

function modfolio_handle_reject_kyc() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'modfolio_kyc_actions')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    // Check if user is admin
    if (!current_user_can('administrator') && !current_user_can('shop_manager')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'vendor-customshot')));
    }

    $vendor_id = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 0;
    $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';

    if (!$vendor_id) {
        wp_send_json_error(array('message' => __('Invalid vendor ID.', 'vendor-customshot')));
    }

    if (empty($reason)) {
        wp_send_json_error(array('message' => __('Please provide a rejection reason.', 'vendor-customshot')));
    }

    // Update verification status (new meta keys)
    update_user_meta($vendor_id, '_vendor_id_verification_status', 'rejected');
    update_user_meta($vendor_id, '_vendor_id_rejection_reason', $reason);
    update_user_meta($vendor_id, '_vendor_id_rejected_date', current_time('mysql'));

    // Also update old meta key for backwards compatibility with withdrawal modal
    update_user_meta($vendor_id, 'vendor_id_verification_status', 'rejected');

    // Send notification email to vendor
    $vendor = get_user_by('id', $vendor_id);
    if ($vendor) {
        $subject = __('Your ID Verification Requires Attention', 'vendor-customshot');
        $message = sprintf(
            __("Hello %s,\n\nUnfortunately, your ID verification was not approved.\n\nReason: %s\n\nPlease upload a new document that meets our verification requirements.\n\nThank you,\nThe Modfolios Team", 'vendor-customshot'),
            $vendor->display_name,
            $reason
        );
        wp_mail($vendor->user_email, $subject, $message);
    }

    wp_send_json_success(array('message' => __('Vendor verification rejected.', 'vendor-customshot')));
}

/**
 * Get KYC Details - AJAX endpoint for admin detail modal
 */
add_action('wp_ajax_modfolio_get_kyc_details', 'modfolio_handle_get_kyc_details');

function modfolio_handle_get_kyc_details() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'modfolio_kyc_actions')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'vendor-customshot')));
    }

    if (!current_user_can('administrator') && !current_user_can('shop_manager')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'vendor-customshot')));
    }

    $vendor_id = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 0;
    if (!$vendor_id) {
        wp_send_json_error(array('message' => __('Invalid vendor ID.', 'vendor-customshot')));
    }

    $vendor = get_user_by('id', $vendor_id);
    if (!$vendor) {
        wp_send_json_error(array('message' => __('Vendor not found.', 'vendor-customshot')));
    }

    // Personal info
    $first_name = get_user_meta($vendor_id, '_vendor_kyc_first_name', true);
    $last_name  = get_user_meta($vendor_id, '_vendor_kyc_last_name', true);
    $dob        = get_user_meta($vendor_id, '_vendor_kyc_dob', true);
    $country_code = get_user_meta($vendor_id, '_vendor_kyc_country', true);
    $doc_type   = get_user_meta($vendor_id, '_vendor_kyc_document_type', true);

    // Resolve country name
    $country_name = $country_code;
    if ($country_code && function_exists('WC') && WC()->countries) {
        $countries = WC()->countries->get_countries();
        if (isset($countries[$country_code])) {
            $country_name = $countries[$country_code];
        }
    }

    // Document type label
    $doc_type_labels = array(
        'passport'        => __('Passport', 'vendor-customshot'),
        'drivers_license' => __("Driver's License", 'vendor-customshot'),
        'national_id'     => __('National ID', 'vendor-customshot'),
    );
    $doc_type_label = isset($doc_type_labels[$doc_type]) ? $doc_type_labels[$doc_type] : $doc_type;

    // Document image
    $document_id  = get_user_meta($vendor_id, '_vendor_id_document', true);
    $document_url = $document_id ? wp_get_attachment_url($document_id) : '';
    $document_thumb = $document_id ? wp_get_attachment_image_url($document_id, 'medium') : '';

    // Selfie image
    $selfie_id    = get_user_meta($vendor_id, '_vendor_kyc_selfie', true);
    $selfie_url   = $selfie_id ? wp_get_attachment_url($selfie_id) : '';
    $selfie_thumb = $selfie_id ? wp_get_attachment_image_url($selfie_id, 'medium') : '';

    // Status
    $status = get_user_meta($vendor_id, '_vendor_id_verification_status', true);
    if (empty($status)) $status = 'pending';

    $rejection_reason = get_user_meta($vendor_id, '_vendor_id_rejection_reason', true);

    wp_send_json_success(array(
        'vendor_name'     => $vendor->display_name,
        'first_name'      => $first_name,
        'last_name'       => $last_name,
        'dob'             => $dob,
        'country'         => $country_name,
        'document_type'   => $doc_type_label,
        'document_url'    => $document_url,
        'document_thumb'  => $document_thumb,
        'selfie_url'      => $selfie_url,
        'selfie_thumb'    => $selfie_thumb,
        'status'          => $status,
        'rejection_reason' => $rejection_reason,
    ));
}

/**
 * KYC Query Vars
 */
function wcfm_kyc_query_vars($query_vars) {
    $wcfm_modified_endpoints = (array) get_option('wcfm_endpoints');

    $query_custom_vars = array(
        'wcfm-kyc' => !empty($wcfm_modified_endpoints['wcfm-kyc']) ? $wcfm_modified_endpoints['wcfm-kyc'] : 'kyc',
    );

    $query_vars = array_merge($query_vars, $query_custom_vars);

    return $query_vars;
}

/**
 * KYC End Point Title
 */
function wcfm_kyc_endpoint_title($title, $endpoint) {
    switch ($endpoint) {
        case 'wcfm-kyc':
            $title = __('KYC', 'vendor-customshot');
            break;
    }
    return $title;
}

/**
 * KYC Endpoint Slug
 */
function wcfm_kyc_endpoints_slug($endpoints) {
    $custom_endpoints = array(
        'wcfm-kyc' => 'kyc',
    );

    $endpoints = array_merge($endpoints, $custom_endpoints);

    return $endpoints;
}

/**
 * Get KYC URL
 */
if (!function_exists('get_wcfm_kyc_url')) {
    function get_wcfm_kyc_url() {
        global $WCFM;
        $wcfm_page = get_wcfm_page();
        $url = wcfm_get_endpoint_url('wcfm-kyc', '', $wcfm_page);
        return $url;
    }
}

/**
 * KYC Menu
 */
function wcfm_kyc_menus($menus) {
    global $WCFM;

    $custom_menus = array(
        'wcfm-kyc' => array(
            'label'    => __('KYC', 'vendor-customshot'),
            'url'      => get_wcfm_kyc_url(),
            'icon'     => 'id-card',
            'priority' => 29
        ),
    );

    $menus = array_merge($menus, $custom_menus);

    return $menus;
}

/**
 * KYC Views
 */
function wcfm_kyc_load_views($end_point) {
    global $WCFM, $WCFMu;
    $plugin_path = trailingslashit(dirname(__FILE__));

    switch ($end_point) {
        case 'wcfm-kyc':
            require_once($plugin_path . 'views/wcfm-view-kyc.php');
            break;
    }
}

/**
 * KYC Styles
 */
function wcfm_kyc_load_styles($end_point) {
    global $WCFM, $WCFMu;
    $plugin_url = trailingslashit(plugins_url('', __FILE__));

    switch ($end_point) {
        case 'wcfm-kyc':
            // wp_enqueue_style('wcfm_kyc_css', $plugin_url . 'css/wcfm-style-kyc.css', array(), $WCFM->version);
            wp_enqueue_style(
                'wcfm_kyc_css',
                 $plugin_url . 'css/wcfm-style-kyc.css',
                array(),
                filemtime(CUSTOMSHOTS_PLUGIN_PATH . 'css/wcfm-style-kyc.css')
            );

            break;
    }
}


/* ========================================
   Payment Settings - WCFM Integration
   ======================================== */

/**
 * Payment Settings Query Vars
 */
function wcfm_payment_settings_query_vars($query_vars) {
    $wcfm_modified_endpoints = (array) get_option('wcfm_endpoints');

    $query_custom_vars = array(
        'wcfm-payment-settings' => !empty($wcfm_modified_endpoints['wcfm-payment-settings']) ? $wcfm_modified_endpoints['wcfm-payment-settings'] : 'payment-settings',
    );

    $query_vars = array_merge($query_vars, $query_custom_vars);

    return $query_vars;
}

/**
 * Payment Settings End Point Title
 */
function wcfm_payment_settings_endpoint_title($title, $endpoint) {
    switch ($endpoint) {
        case 'wcfm-payment-settings':
            $title = __('Payment Settings', 'vendor-customshot');
            break;
    }
    return $title;
}

/**
 * Payment Settings Endpoint Slug
 */
function wcfm_payment_settings_endpoints_slug($endpoints) {
    $custom_endpoints = array(
        'wcfm-payment-settings' => 'payment-settings',
    );

    $endpoints = array_merge($endpoints, $custom_endpoints);

    return $endpoints;
}

/**
 * Get Payment Settings URL
 */
if (!function_exists('get_wcfm_payment_settings_url')) {
    function get_wcfm_payment_settings_url() {
        global $WCFM;
        $wcfm_page = get_wcfm_page();
        $url = wcfm_get_endpoint_url('wcfm-payment-settings', '', $wcfm_page);
        return $url;
    }
}

/**
 * Payment Settings Menu
 */
function wcfm_payment_settings_menus($menus) {
    global $WCFM;

    // Only show for vendors, not admins
    if (current_user_can('administrator') || current_user_can('shop_manager')) {
        return $menus;
    }

    $custom_menus = array(
        'wcfm-payment-settings' => array(
            'label'    => __('Payment Settings', 'vendor-customshot'),
            'url'      => get_wcfm_payment_settings_url(),
            'icon'     => 'money-bill-alt',
            'priority' => 90
        ),
    );

    $menus = array_merge($menus, $custom_menus);

    return $menus;
}

/**
 * Payment Settings Views
 */
function wcfm_payment_settings_load_views($end_point) {
    global $WCFM, $WCFMu;
    $plugin_path = trailingslashit(dirname(__FILE__));

    switch ($end_point) {
        case 'wcfm-payment-settings':
            require_once($plugin_path . 'views/wcfm-view-payment-settings.php');
            break;
    }
}


/* ========================================
   Account Management - WCFM Integration
   ======================================== */

/**
 * Account Management Menu
 * Replaces the existing profile menu with custom label
 */
function wcfm_account_management_menus($menus) {
    global $WCFM;

    // Get the profile page URL
    $wcfm_page = get_wcfm_page();
    $profile_url = wcfm_get_endpoint_url('wcfm-profile', '', $wcfm_page);

    // Remove the existing profile menu if it exists
    if (isset($menus['wcfm-profile'])) {
        unset($menus['wcfm-profile']);
    }

    // Add our custom Account Management menu using the same endpoint key
    $menus['wcfm-profile'] = array(
        'label'    => __('Account Management', 'vendor-customshot'),
        'url'      => $profile_url,
        'icon'     => 'user-cog',
        'priority' => 12
    );

    return $menus;
}




/* -------------------------------------------------------------
 *  AJAX: Load more products on vendor store page
 * ------------------------------------------------------------- */
add_action( 'wp_ajax_wcfm_store_load_more_products',        'wcfm_store_load_more_products_handler' );
add_action( 'wp_ajax_nopriv_wcfm_store_load_more_products', 'wcfm_store_load_more_products_handler' );

function wcfm_store_load_more_products_handler() {
    check_ajax_referer( 'wcfm_store_load_more', 'nonce' );

    $vendor_id = isset( $_POST['vendor_id'] ) ? absint( $_POST['vendor_id'] ) : 0;
    $page      = isset( $_POST['page'] )      ? absint( $_POST['page'] )      : 2;
    $per_page  = isset( $_POST['per_page'] )   ? absint( $_POST['per_page'] )  : 10;

    if ( ! $vendor_id ) {
        wp_send_json_error();
    }

    // Get exclusive product IDs to exclude
    $exclusive_product_ids = get_posts( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'author'         => $vendor_id,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_is_exclusive_product',
                'value'   => 'yes',
                'compare' => '=',
            ),
        ),
    ) );

    $query = new WP_Query( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'author'         => $vendor_id,
        'post__not_in'   => $exclusive_product_ids,
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_type',
                'field'    => 'slug',
                'terms'    => array( 'subscription', 'variable-subscription', 'subscription_variation' ),
                'operator' => 'NOT IN',
            ),
        ),
    ) );

    ob_start();
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            wc_get_template_part( 'content', 'product' );
        }
    }
    $html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success( array( 'html' => $html ) );
}