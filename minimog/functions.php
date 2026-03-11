<?php
/**
 * Define constants.
 */
$theme = wp_get_theme();

if ( ! empty( $theme['Template'] ) ) {
	$theme = wp_get_theme( $theme['Template'] );
}

define( 'MINIMOG_THEME_NAME', $theme['Name'] );
define( 'MINIMOG_THEME_VERSION', $theme['Version'] );
define( 'MINIMOG_THEME_DIR', get_template_directory() );
define( 'MINIMOG_THEME_URI', get_template_directory_uri() );
define( 'MINIMOG_THEME_ASSETS_DIR', get_template_directory() . '/assets' );
define( 'MINIMOG_THEME_ASSETS_URI', get_template_directory_uri() . '/assets' );
define( 'MINIMOG_THEME_IMAGE_URI', MINIMOG_THEME_ASSETS_URI . '/images' );
define( 'MINIMOG_THEME_SVG_DIR', MINIMOG_THEME_ASSETS_DIR . '/svg' );
define( 'MINIMOG_FRAMEWORK_DIR', get_template_directory() . DIRECTORY_SEPARATOR . 'framework' );
define( 'MINIMOG_WIDGETS_DIR', get_template_directory() . DIRECTORY_SEPARATOR . 'widgets' );
define( 'MINIMOG_PROTOCOL', is_ssl() ? 'https' : 'http' );

define( 'MINIMOG_ELEMENTOR_DIR', get_template_directory() . DIRECTORY_SEPARATOR . 'elementor' );
define( 'MINIMOG_ELEMENTOR_URI', get_template_directory_uri() . '/elementor' );
define( 'MINIMOG_ELEMENTOR_ASSETS', get_template_directory_uri() . '/elementor/assets' );

/**
 * Load required functions
 */

/**
 * @param string $file_path File path to valid.
 *
 * @return mixed
 */
function minimog_valid_file_path( $file_path ) {
	return str_replace( '/', DIRECTORY_SEPARATOR, $file_path );
}

/**
 * @param string $file_path File path to include.
 */
function minimog_require_file_once( $file_path ) {
	require_once $file_path;
}

/**
 * Define a constant if it is not already defined.
 *
 * @param string $name  Constant name.
 * @param mixed  $value Value.
 */
function minimog_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Load Frameworks.
 */
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-functions.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-helper.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-debug.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-ajax.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-font-awesome-manager.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-svg-manager.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-template-loader.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-site-layout.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-aqua-resizer.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-performance.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-static.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-init.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-global.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-actions-filters.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-search.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-kses.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-cookie-notice.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-promo-popup.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-admin.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-nav-menu-item.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-nav-menu.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-enqueue.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-attachment.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-image.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-logo.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-color.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-import.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-metabox.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-plugins.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-custom-css.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-templates.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-language-switcher.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-walker-nav-menu.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-walker-nav-menu-extra-items.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-widget.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-widgets.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-top-bar.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-header.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-title-bar.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-sidebar.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-footer.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-post-type-blog.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-woo.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/dokan/class-utils.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/tgm-plugin-activation.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/tgm-plugin-registration.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-tha.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-hcaptcha.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-login-register.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-two-factor-auth.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-wpforms.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/class-instagram.php' );
minimog_require_file_once( MINIMOG_FRAMEWORK_DIR . '/buyer-dashboard.php' );

minimog_require_file_once( MINIMOG_ELEMENTOR_DIR . '/class-entry.php' );

minimog_require_file_once( MINIMOG_THEME_DIR . '/theme-options/main.php' );


if ( file_exists( MINIMOG_THEME_DIR . '/wcfm/wcfm-helpers.php' ) ) {
    minimog_require_file_once( MINIMOG_THEME_DIR . '/wcfm/wcfm-helpers.php' );
}

// Google Drive Integration for downloadable products
if ( file_exists( MINIMOG_THEME_DIR . '/wcfm/class-google-drive-integration.php' ) ) {
    minimog_require_file_once( MINIMOG_THEME_DIR . '/wcfm/class-google-drive-integration.php' );
}

//minimog_require_file_once( MINIMOG_THEME_DIR . '/customshots.php' );

/**
 * Init the theme
 */
Minimog_Init::instance()->initialize();





// ============== disable plugin autoupdate to keep customizations ==============

add_filter( 'auto_update_plugin', '__return_false' );
// Hide all plugin update notifications
add_filter('site_transient_update_plugins', '__return_null');






// ================== All custom Ajax functions ==================

/* ---------------------------
 * AJAX Handlers for Product Approval/Rejection/Deactivation
 * (Used in custom WCFM Products page)
 * --------------------------- */

/**
 * AJAX: Approve a product (set status to publish)
 */
add_action( 'wp_ajax_modfolio_approve_product', 'modfolio_approve_product_handler' );
function modfolio_approve_product_handler() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'modfolio_product_actions' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wc-frontend-manager' ) ) );
    }

    // Check if user is admin
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to approve products.', 'wc-frontend-manager' ) ) );
    }

    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'wc-frontend-manager' ) ) );
    }

    // Check if product exists
    $product = get_post( $product_id );
    if ( ! $product || $product->post_type !== 'product' ) {
        wp_send_json_error( array( 'message' => __( 'Product not found.', 'wc-frontend-manager' ) ) );
    }

    // Update product status to publish
    $result = wp_update_post( array(
        'ID'          => $product_id,
        'post_status' => 'publish',
    ), true );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    // Clear any rejection reason if exists
    delete_post_meta( $product_id, '_rejection_reason' );

    // Trigger WCFM notification if available
    do_action( 'modfolio_product_approved', $product_id, $product->post_author );

    wp_send_json_success( array(
        'message'    => __( 'Product approved successfully.', 'wc-frontend-manager' ),
        'product_id' => $product_id,
        'new_status' => 'publish',
    ) );
}

/**
 * AJAX: Reject a product (keep as pending, save rejection reason)
 */
add_action( 'wp_ajax_modfolio_reject_product', 'modfolio_reject_product_handler' );
function modfolio_reject_product_handler() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'modfolio_product_actions' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wc-frontend-manager' ) ) );
    }

    // Check if user is admin
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to reject products.', 'wc-frontend-manager' ) ) );
    }

    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $reason     = isset( $_POST['reason'] ) ? sanitize_textarea_field( $_POST['reason'] ) : '';

    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'wc-frontend-manager' ) ) );
    }

    if ( empty( $reason ) ) {
        wp_send_json_error( array( 'message' => __( 'Please provide a rejection reason.', 'wc-frontend-manager' ) ) );
    }

    // Check if product exists
    $product = get_post( $product_id );
    if ( ! $product || $product->post_type !== 'product' ) {
        wp_send_json_error( array( 'message' => __( 'Product not found.', 'wc-frontend-manager' ) ) );
    }

    // Save rejection reason as meta
    update_post_meta( $product_id, '_rejection_reason', $reason );

    // Keep status as pending (or set to draft if you prefer)
    wp_update_post( array(
        'ID'          => $product_id,
        'post_status' => 'pending',
    ) );

    // Trigger notification for vendor
    do_action( 'modfolio_product_rejected', $product_id, $product->post_author, $reason );

    wp_send_json_success( array(
        'message'    => __( 'Product rejected successfully.', 'wc-frontend-manager' ),
        'product_id' => $product_id,
        'reason'     => $reason,
    ) );
}

/**
 * AJAX: Deactivate a product (set status to draft/archived)
 */
add_action( 'wp_ajax_modfolio_deactivate_product', 'modfolio_deactivate_product_handler' );
function modfolio_deactivate_product_handler() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'modfolio_product_actions' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wc-frontend-manager' ) ) );
    }

    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'wc-frontend-manager' ) ) );
    }

    // Check if product exists
    $product = get_post( $product_id );
    if ( ! $product || $product->post_type !== 'product' ) {
        wp_send_json_error( array( 'message' => __( 'Product not found.', 'wc-frontend-manager' ) ) );
    }

    // Check permission - user must be admin or the product author
    $current_user_id = get_current_user_id();
    $is_admin        = current_user_can( 'manage_options' );
    $is_author       = ( (int) $product->post_author === $current_user_id );

    if ( ! $is_admin && ! $is_author ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to deactivate this product.', 'wc-frontend-manager' ) ) );
    }

    // Update product status to draft (archived)
    $result = wp_update_post( array(
        'ID'          => $product_id,
        'post_status' => 'draft',
    ), true );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    wp_send_json_success( array(
        'message'    => __( 'Product deactivated successfully.', 'wc-frontend-manager' ),
        'product_id' => $product_id,
        'new_status' => 'draft',
    ) );
}

/**
 * AJAX: Activate a product (set status from draft back to publish)
 */
add_action( 'wp_ajax_modfolio_activate_product', 'modfolio_activate_product_handler' );
function modfolio_activate_product_handler() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'modfolio_product_actions' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wc-frontend-manager' ) ) );
    }

    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'wc-frontend-manager' ) ) );
    }

    // Check if product exists
    $product = get_post( $product_id );
    if ( ! $product || $product->post_type !== 'product' ) {
        wp_send_json_error( array( 'message' => __( 'Product not found.', 'wc-frontend-manager' ) ) );
    }

    // Check permission - user must be admin or the product author
    $current_user_id = get_current_user_id();
    $is_admin        = current_user_can( 'manage_options' );
    $is_author       = ( (int) $product->post_author === $current_user_id );

    if ( ! $is_admin && ! $is_author ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to activate this product.', 'wc-frontend-manager' ) ) );
    }

    // Update product status to publish
    $result = wp_update_post( array(
        'ID'          => $product_id,
        'post_status' => 'publish',
    ), true );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    wp_send_json_success( array(
        'message'    => __( 'Product activated successfully.', 'wc-frontend-manager' ),
        'product_id' => $product_id,
        'new_status' => 'publish',
    ) );
}

/**
 * Skip the Profile/Registration step in WCFM Membership flow for logged-in users.
 * Since we handle registration separately and email is already verified,
 * go directly from Plan Selection → WooCommerce Checkout.
 */

// Remove the 'registration' step from the progress bar for logged-in users
add_filter( 'wcfm_membership_registration_steps', function ( $steps ) {
    if ( is_user_logged_in() ) {
        unset( $steps['registration'] );
    }
    return $steps;
} );

// Fallback: if a logged-in user somehow lands on vmstep=registration, redirect to checkout
add_action( 'template_redirect', function () {
    if ( ! is_user_logged_in() ) return;
    if ( ! isset( $_REQUEST['vmstep'] ) || $_REQUEST['vmstep'] !== 'registration' ) return;
    if ( ! function_exists( 'is_wcfm_membership_page' ) || ! is_wcfm_membership_page() ) return;

    if ( ! WC()->session ) return;
    $wcfm_membership = absint( WC()->session->get( 'wcfm_membership' ) );
    if ( ! $wcfm_membership ) return;

    $member_id = get_current_user_id();
    update_user_meta( $member_id, 'temp_wcfm_membership', $wcfm_membership );

    $subscription          = (array) get_post_meta( $wcfm_membership, 'subscription', true );
    $subscription_pay_mode = isset( $subscription['subscription_pay_mode'] ) ? $subscription['subscription_pay_mode'] : 'by_wcfm';
    $subscription_product  = isset( $subscription['subscription_product'] ) ? $subscription['subscription_product'] : '';
    $is_free               = isset( $subscription['is_free'] ) ? 'yes' : 'no';

    if ( $is_free === 'yes' ) {
        WC()->session->set( 'wcfm_membership_free_registration', true );
        wp_safe_redirect( add_query_arg( 'vmstep', 'thankyou', get_wcfm_membership_url() ) );
        exit;
    }

    if ( $subscription_pay_mode === 'by_wc' && $subscription_product ) {
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart( $subscription_product );
        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }

    wp_safe_redirect( add_query_arg( 'vmstep', 'payment', get_wcfm_membership_url() ) );
    exit;
} );

/**
 * AJAX: Direct membership checkout for logged-in users.
 * Called from pricing page to skip the vendor-membership page entirely.
 * Sets WC session, adds subscription product to cart, returns checkout URL.
 */
add_action( 'wp_ajax_modfolio_direct_membership_checkout', 'modfolio_direct_membership_checkout_handler' );
function modfolio_direct_membership_checkout_handler() {
    check_ajax_referer( 'modfolio_membership_nonce', 'nonce' );

    $membership_id = isset( $_POST['membership_id'] ) ? absint( $_POST['membership_id'] ) : 0;
    if ( ! $membership_id ) {
        wp_send_json_error( array( 'message' => 'Invalid membership.' ) );
    }

    $member_id = get_current_user_id();

    // Set WC session (same as the WCFM AJAX handler does)
    if ( WC()->session ) {
        do_action( 'woocommerce_set_cart_cookies', true );
        WC()->session->set( 'wcfm_membership', $membership_id );

        if ( function_exists( 'wcfm_has_membership' ) && wcfm_has_membership() ) {
            WC()->session->set( 'wcfm_membership_mode', 'upgrade' );
        } else {
            WC()->session->set( 'wcfm_membership_mode', 'new' );
        }

        if ( WC()->session->get( 'wcfm_membership_free_registration' ) ) {
            WC()->session->__unset( 'wcfm_membership_free_registration' );
        }
    }

    do_action( 'wcfmvm_after_choosing_membership', $membership_id );

    // Store temp membership
    update_user_meta( $member_id, 'temp_wcfm_membership', $membership_id );

    // Get subscription details
    $subscription          = (array) get_post_meta( $membership_id, 'subscription', true );
    $subscription_pay_mode = isset( $subscription['subscription_pay_mode'] ) ? $subscription['subscription_pay_mode'] : 'by_wcfm';
    $subscription_product  = isset( $subscription['subscription_product'] ) ? $subscription['subscription_product'] : '';
    $is_free               = isset( $subscription['is_free'] ) ? 'yes' : 'no';

    // Free plan
    if ( $is_free === 'yes' ) {
        WC()->session->set( 'wcfm_membership_free_registration', true );
        wp_send_json_success( array(
            'redirect' => add_query_arg( 'vmstep', 'thankyou', get_wcfm_membership_url() ),
        ) );
    }

    // Paid plan via WooCommerce
    if ( $subscription_pay_mode === 'by_wc' && $subscription_product ) {
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart( $subscription_product );
        wp_send_json_success( array(
            'redirect' => wc_get_checkout_url(),
        ) );
    }

    // Fallback: non-WC payment
    wp_send_json_success( array(
        'redirect' => add_query_arg( 'vmstep', 'payment', get_wcfm_membership_url() ),
    ) );
}

/**
 * AJAX: Save vendor settings (Personal Info, Media Kit, Demographics, etc.)
 */
add_action( 'wp_ajax_modfolio_save_vendor_settings', 'modfolio_save_vendor_settings_handler' );
function modfolio_save_vendor_settings_handler() {
    // Verify nonce
    if ( ! isset( $_POST['modfolio_settings_nonce'] ) || ! wp_verify_nonce( $_POST['modfolio_settings_nonce'], 'modfolio_save_settings' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wc-frontend-manager' ) ) );
    }

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'wc-frontend-manager' ) ) );
    }

    // Get existing WCFM profile settings
    $vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
    if ( ! is_array( $vendor_data ) ) {
        $vendor_data = array();
    }

    // ===== Profile Images =====
    if ( isset( $_POST['gravatar'] ) && ! empty( $_POST['gravatar'] ) ) {
        $vendor_data['gravatar'] = absint( $_POST['gravatar'] );
    }
    if ( isset( $_POST['banner'] ) && ! empty( $_POST['banner'] ) ) {
        $vendor_data['banner'] = absint( $_POST['banner'] );
    }

    // ===== Tagline =====
    if ( isset( $_POST['tagline'] ) ) {
        $vendor_data['tagline'] = sanitize_text_field( $_POST['tagline'] );
    }

    // ===== Personal Information =====
    $vendor_data['about'] = isset( $_POST['about'] ) ? sanitize_textarea_field( $_POST['about'] ) : '';

    // Social links
    if ( ! isset( $vendor_data['social'] ) ) {
        $vendor_data['social'] = array();
    }
    if ( isset( $_POST['social'] ) && is_array( $_POST['social'] ) ) {
        foreach ( $_POST['social'] as $key => $value ) {
            $vendor_data['social'][ sanitize_key( $key ) ] = esc_url_raw( $value );
        }
    }

    // ===== Live Media Kit =====
    if ( ! isset( $vendor_data['media_kit'] ) ) {
        $vendor_data['media_kit'] = array();
    }
    if ( isset( $_POST['media_kit'] ) && is_array( $_POST['media_kit'] ) ) {
        foreach ( $_POST['media_kit'] as $platform => $stats ) {
            $platform = sanitize_key( $platform );
            if ( ! isset( $vendor_data['media_kit'][ $platform ] ) ) {
                $vendor_data['media_kit'][ $platform ] = array();
            }
            if ( is_array( $stats ) ) {
                foreach ( $stats as $stat_key => $stat_value ) {
                    $vendor_data['media_kit'][ $platform ][ sanitize_key( $stat_key ) ] = sanitize_text_field( $stat_value );
                }
            }
        }
    }

    // ===== Audience Demographics =====
    if ( ! isset( $vendor_data['demographics'] ) ) {
        $vendor_data['demographics'] = array();
    }
    if ( isset( $_POST['demographics'] ) && is_array( $_POST['demographics'] ) ) {
        if ( isset( $_POST['demographics']['gender_female'] ) ) {
            $gender_female = absint( $_POST['demographics']['gender_female'] );
            $vendor_data['demographics']['gender_female'] = min( 100, $gender_female );
        }
        if ( isset( $_POST['demographics']['dominant_age'] ) ) {
            $vendor_data['demographics']['dominant_age'] = sanitize_text_field( $_POST['demographics']['dominant_age'] );
        }
        if ( isset( $_POST['demographics']['top_locations'] ) ) {
            $vendor_data['demographics']['top_locations'] = sanitize_text_field( $_POST['demographics']['top_locations'] );
        }
    }

    // ===== Achievements =====
    $vendor_data['achievements'] = array();
    if ( isset( $_POST['achievements'] ) && is_array( $_POST['achievements'] ) ) {
        foreach ( $_POST['achievements'] as $achievement ) {
            if ( ! empty( $achievement['title'] ) ) {
                $vendor_data['achievements'][] = array(
                    'title' => sanitize_text_field( $achievement['title'] ),
                    'year'  => isset( $achievement['year'] ) ? sanitize_text_field( $achievement['year'] ) : '',
                );
            }
        }
    }

    // ===== Theme Settings =====
    if ( ! isset( $vendor_data['theme'] ) ) {
        $vendor_data['theme'] = array();
    }
    if ( isset( $_POST['theme'] ) && is_array( $_POST['theme'] ) ) {
        if ( isset( $_POST['theme']['background'] ) ) {
            $vendor_data['theme']['background'] = sanitize_hex_color( $_POST['theme']['background'] );
        }
        if ( isset( $_POST['theme']['accent'] ) ) {
            $vendor_data['theme']['accent'] = sanitize_hex_color( $_POST['theme']['accent'] );
        }
        if ( isset( $_POST['theme']['light_bg'] ) ) {
            $vendor_data['theme']['light_bg'] = sanitize_hex_color( $_POST['theme']['light_bg'] );
        }
        if ( isset( $_POST['theme']['dark_bg'] ) ) {
            $vendor_data['theme']['dark_bg'] = sanitize_hex_color( $_POST['theme']['dark_bg'] );
        }
    }

    // ===== Languages =====
    $vendor_data['languages'] = array();
    if ( isset( $_POST['languages'] ) && is_array( $_POST['languages'] ) ) {
        foreach ( $_POST['languages'] as $language ) {
            if ( ! empty( $language ) ) {
                $vendor_data['languages'][] = sanitize_text_field( $language );
            }
        }
    }

    // ===== Collab Preferences =====
    if ( ! isset( $vendor_data['collab'] ) ) {
        $vendor_data['collab'] = array();
    }
    $collab_options = array( 'ugc_video', 'photography', 'modeling', 'sponsored_posts', 'tfp' );
    foreach ( $collab_options as $option ) {
        $vendor_data['collab'][ $option ] = ( isset( $_POST['collab'][ $option ] ) && $_POST['collab'][ $option ] === 'yes' ) ? 'yes' : 'no';
    }

    // ===== Travel Status =====
    if ( ! isset( $vendor_data['travel'] ) ) {
        $vendor_data['travel'] = array();
    }
    if ( isset( $_POST['travel'] ) && is_array( $_POST['travel'] ) ) {
        if ( isset( $_POST['travel']['available'] ) ) {
            $vendor_data['travel']['available'] = sanitize_text_field( $_POST['travel']['available'] );
        }
        if ( isset( $_POST['travel']['base_city'] ) ) {
            $vendor_data['travel']['base_city'] = sanitize_text_field( $_POST['travel']['base_city'] );
        }
    }

    // Save settings
    update_user_meta( $user_id, 'wcfmmp_profile_settings', $vendor_data );

    // Calculate profile completion percentage
    $completion = modfolio_calculate_profile_completion( $user_id, $vendor_data );

    wp_send_json_success( array(
        'message'    => __( 'Settings saved successfully.', 'wc-frontend-manager' ),
        'completion' => $completion,
    ) );
}

/**
 * Calculate profile completion percentage
 */
// function modfolio_calculate_profile_completion( $user_id, $vendor_data = null ) {
//     if ( $vendor_data === null ) {
//         $vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
//         if ( ! is_array( $vendor_data ) ) {
//             $vendor_data = array();
//         }
//     }

//     $total_fields  = 19; // Updated to include tagline
//     $filled_fields = 0;

//     // Profile images
//     if ( ! empty( $vendor_data['gravatar'] ) ) $filled_fields++;
//     if ( ! empty( $vendor_data['banner'] ) ) $filled_fields++;

//     // Tagline
//     if ( ! empty( $vendor_data['tagline'] ) ) $filled_fields++;

//     // Personal Info
//     if ( ! empty( $vendor_data['about'] ) ) $filled_fields++;

//     // Social links
//     if ( ! empty( $vendor_data['social']['fb'] ) ) $filled_fields++;
//     if ( ! empty( $vendor_data['social']['linkedin'] ) ) $filled_fields++;
//     if ( ! empty( $vendor_data['social']['twitter'] ) ) $filled_fields++;
//     if ( ! empty( $vendor_data['social']['instagram'] ) ) $filled_fields++;

//     // Media Kit platforms
//     if ( ! empty( $vendor_data['media_kit']['instagram']['followers'] ) ) $filled_fields++;
//     if ( ! empty( $vendor_data['media_kit']['tiktok']['followers'] ) ) $filled_fields++;
//     if ( ! empty( $vendor_data['media_kit']['youtube']['subscribers'] ) ) $filled_fields++;
//     if ( ! empty( $vendor_data['media_kit']['snapchat']['daily_users'] ) ) $filled_fields++;

//     // Demographics
//     if ( ! empty( $vendor_data['demographics']['top_locations'] ) ) $filled_fields++;

//     // Achievements
//     if ( ! empty( $vendor_data['achievements'] ) ) $filled_fields++;

//     // Theme
//     if ( ! empty( $vendor_data['theme']['background'] ) || ! empty( $vendor_data['theme']['accent'] ) ) $filled_fields++;

//     // Languages
//     if ( ! empty( $vendor_data['languages'] ) ) $filled_fields++;

//     // Travel
//     if ( ! empty( $vendor_data['travel']['base_city'] ) ) $filled_fields++;

//     // Collab preferences (at least one selected)
//     $collab_count = 0;
//     if ( isset( $vendor_data['collab'] ) && is_array( $vendor_data['collab'] ) ) {
//         foreach ( $vendor_data['collab'] as $val ) {
//             if ( $val === 'yes' ) $collab_count++;
//         }
//     }
//     if ( $collab_count > 0 ) $filled_fields++;

//     return min( 100, round( ( $filled_fields / $total_fields ) * 100 ) );
// }



/**
 * Calculate profile completion percentage
 */
function modfolio_calculate_profile_completion( $user_id, $vendor_data = null ) {
    if ( $vendor_data === null ) {
        $vendor_data = get_user_meta( $user_id, 'wcfmmp_profile_settings', true );
        if ( ! is_array( $vendor_data ) ) {
            $vendor_data = array();
        }
    }

    $total_fields  = 19; // Updated to include tagline
    $filled_fields = 0;

    // Profile images
    if ( ! empty( $vendor_data['gravatar'] ) ) $filled_fields++;
    if ( ! empty( $vendor_data['banner'] ) ) $filled_fields++;

    // Tagline
    if ( ! empty( $vendor_data['tagline'] ) ) $filled_fields++;

    // Personal Info
    if ( ! empty( $vendor_data['about'] ) ) $filled_fields++;

    // Social links
    if ( ! empty( $vendor_data['social']['fb'] ) ) $filled_fields++;
    if ( ! empty( $vendor_data['social']['linkedin'] ) ) $filled_fields++;
    if ( ! empty( $vendor_data['social']['twitter'] ) ) $filled_fields++;
    if ( ! empty( $vendor_data['social']['instagram'] ) ) $filled_fields++;

    // Media Kit platforms
    if ( ! empty( $vendor_data['media_kit']['instagram']['followers'] ) ) $filled_fields++;
    if ( ! empty( $vendor_data['media_kit']['tiktok']['followers'] ) ) $filled_fields++;
    if ( ! empty( $vendor_data['media_kit']['youtube']['subscribers'] ) ) $filled_fields++;
    if ( ! empty( $vendor_data['media_kit']['snapchat']['daily_users'] ) ) $filled_fields++;

    // Demographics
    if ( ! empty( $vendor_data['demographics']['top_locations'] ) ) $filled_fields++;

    // Achievements
    if ( ! empty( $vendor_data['achievements'] ) ) $filled_fields++;

    // Theme
    if ( ! empty( $vendor_data['theme']['background'] ) || ! empty( $vendor_data['theme']['accent'] ) ) $filled_fields++;

    // Languages
    if ( ! empty( $vendor_data['languages'] ) ) $filled_fields++;

    // Travel
    if ( ! empty( $vendor_data['travel']['base_city'] ) ) $filled_fields++;

    // Collab preferences (at least one selected)
    $collab_count = 0;
    if ( isset( $vendor_data['collab'] ) && is_array( $vendor_data['collab'] ) ) {
        foreach ( $vendor_data['collab'] as $val ) {
            if ( $val === 'yes' ) $collab_count++;
        }
    }
    if ( $collab_count > 0 ) $filled_fields++;

    $percentage = min( 100, round( ( $filled_fields / $total_fields ) * 100 ) );
    if ( $percentage >= 95 ) $percentage = 100;
    return $percentage;
}