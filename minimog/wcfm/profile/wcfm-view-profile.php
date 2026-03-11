<?php
/**
 * WCFM Profile View - Modfolio Design
 * Custom template matching Figma design exactly
 */

require_once get_template_directory() . '/wcfm/wcfm-helpers.php';

global $WCFM, $wpdb, $blog_id, $wp;

if (!apply_filters('wcfm_is_pref_profile', true) || !apply_filters('wcfm_is_allow_profile', true)) {
    wcfm_restriction_message_show("Profile");
    return;
}

$user_id = get_current_user_id();
$user = get_user_by('id', $user_id);

// Avatar
$wp_user_avatar_id = get_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', true);
$avatar_url = $wp_user_avatar_id ? wp_get_attachment_url($wp_user_avatar_id) : get_avatar_url($user_id, array('size' => 100));

// User data
$display_name = $user->display_name;
$first_name = get_user_meta($user_id, 'first_name', true);
$last_name = get_user_meta($user_id, 'last_name', true);
$full_name = trim($first_name . ' ' . $last_name);
if (empty($full_name)) {
    $full_name = $display_name;
}
$email = $user->user_email;

// Date of Birth and Age
$dob = get_user_meta($user_id, 'user_registration_vendor_daob', true);
$age = '';
if (!empty($dob)) {
    $dob_date = new DateTime($dob);
    $today = new DateTime();
    $age = $dob_date->diff($today)->y;
}

// Last login
$last_login = get_user_meta($user_id, 'last_login', true);
if (empty($last_login)) {
    $last_login = $user->user_registered;
}
$last_login_formatted = date_i18n('g:i a (F j, Y)', strtotime($last_login));

// WCFM Membership data
$membership_id = get_user_meta($user_id, 'wcfm_membership', true);
$membership_plan = __('Basic', 'wc-frontend-manager');
$subscription_start = '';
$subscription_end = '';

if ($membership_id) {
    // Get membership name
    $membership_post = get_post($membership_id);
    if ($membership_post && $membership_post->post_title) {
        $membership_plan = $membership_post->post_title;
    }

    // Get subscription start date (billing period start - timestamp)
    $billing_period_start = get_user_meta($user_id, 'wcfm_membership_billing_period', true);
    if ($billing_period_start && is_numeric($billing_period_start)) {
        $subscription_start = date_i18n('d F Y', $billing_period_start);
    }

    // Get subscription end/renewal date (next schedule - timestamp)
    $next_schedule = get_user_meta($user_id, 'wcfm_membership_next_schedule', true);
    if ($next_schedule && is_numeric($next_schedule)) {
        $subscription_end = date_i18n('d F Y', $next_schedule);
    }
}

// Fallback subscription dates
if (empty($subscription_start)) {
    $subscription_start = date_i18n('d F Y', strtotime($user->user_registered));
}
if (empty($subscription_end)) {
    $subscription_end = __('Lifetime', 'wc-frontend-manager');
}

// Commission rate from WCFM Marketplace membership
$commission_rate = '10%';
if ($membership_id) {
    // Get commission from membership post meta
    $membership_commission = get_post_meta($membership_id, 'commission', true);
    if (is_array($membership_commission) && isset($membership_commission['commission_percent'])) {
        $commission_rate = $membership_commission['commission_percent'] . '%';
    }
}

// Vendor status
$vendor_status = __('Verified', 'wc-frontend-manager');
if (function_exists('wcfm_is_vendor') && wcfm_is_vendor()) {
    $vendor_data = get_user_meta($user_id, 'wcfmmp_profile_settings', true);
    if (is_array($vendor_data) && isset($vendor_data['vendor_status'])) {
        $vendor_status = ucfirst($vendor_data['vendor_status']);
    }
}

// Security settings
$two_factor_enabled = get_user_meta($user_id, '_two_factor_enabled', true) === 'yes';
$preferred_currency = get_user_meta($user_id, '_preferred_currency', true);
if (empty($preferred_currency)) {
    $preferred_currency = 'USD';
}

// Email notification
$notify_email = get_user_meta($user_id, '_notify_via_email', true);
if (empty($notify_email)) {
    $notify_email = 'yes';
}

// Breadcrumb
$breadcrumb_items = array(
    array('label' => __('Account Management', 'wc-frontend-manager'), 'url' => '')
);
?>

<div class="collapse wcfm-collapse modfolio-profile-page" id="wcfm_profile">
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>

        <?php modfolio_wcfm_render_header('', $breadcrumb_items); ?>
        <?php do_action('before_wcfm_wcvendors_profile'); ?>

        <form id="wcfm_profile_form" class="wcfm modfolio-profile-form">
            <?php do_action('begin_wcfm_wcvendors_profile_form'); ?>

            <!-- ========== ACCOUNT INFORMATION ========== -->
            <div class="modfolio-section">
                <h3 class="section-title"><?php _e('Account Information', 'wc-frontend-manager'); ?></h3>

                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('Display Name', 'wc-frontend-manager'); ?></label>
                        <input type="text" name="display_name" value="<?php echo esc_attr($display_name); ?>"
                            placeholder="<?php _e('Enter Display Name', 'wc-frontend-manager'); ?>">
                    </div>
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('Full Name', 'wc-frontend-manager'); ?></label>
                        <input type="text" name="full_name" value="<?php echo esc_attr($full_name); ?>"
                            placeholder="<?php _e('Enter Full Name', 'wc-frontend-manager'); ?>">
                    </div>
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('Email', 'wc-frontend-manager'); ?></label>
                        <input type="email" name="email" value="<?php echo esc_attr($email); ?>" readonly>
                    </div>
                </div>

                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline modfolio-field-dob">
                        <label><?php _e('Date of Birth', 'wc-frontend-manager'); ?></label>
                        <input type="date" name="vendor_dob" value="<?php echo esc_attr($dob); ?>"
                            max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="modfolio-field modfolio-field-inline modfolio-field-age">
                        <label><?php _e('Age', 'wc-frontend-manager'); ?></label>
                        <input type="text" name="vendor_age"
                            value="<?php echo $age ? esc_attr($age . ' ' . __('years', 'wc-frontend-manager')) : ''; ?>"
                            readonly placeholder="<?php _e('Auto-calculated', 'wc-frontend-manager'); ?>">
                    </div>
                    <!-- <div class="modfolio-field modfolio-field-inline modfolio-field-spacer"></div> -->
                </div>

                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('Current Password', 'wc-frontend-manager'); ?></label>
                        <input type="password" name="current_password"
                            placeholder="<?php _e('Enter Current Password', 'wc-frontend-manager'); ?>">
                    </div>
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('Password', 'wc-frontend-manager'); ?></label>
                        <input type="password" name="password"
                            placeholder="<?php _e('Enter New Password', 'wc-frontend-manager'); ?>">
                    </div>
                    <div class="modfolio-field modfolio-field-inline">
                        <label><?php _e('Confirm Password', 'wc-frontend-manager'); ?></label>
                        <input type="password" name="password_confirm"
                            placeholder="<?php _e('Re-enter New Password', 'wc-frontend-manager'); ?>">
                    </div>
                </div>

                <div class="modfolio-row modfolio-row-end">
                    <button type="button" class="modfolio-btn modfolio-btn-primary" id="update-password-btn">
                        <?php _e('Update Password', 'wc-frontend-manager'); ?>
                    </button>
                </div>
                <!-- </div> -->

                <!-- ========== SECURITY SETTINGS ========== -->
                <!-- <div class="modfolio-section"> -->
                <h3 class="section-title" style="margin-top: 20px;">
                    <?php _e('Security Settings', 'wc-frontend-manager'); ?></h3>

                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-toggle">
                        <label><?php _e('Enable Two-Factor Authentication During Login', 'wc-frontend-manager'); ?></label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="two_factor_enabled" value="yes" <?php checked($two_factor_enabled, true); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline modfolio-field-third">
                        <label><?php _e('Preferred Currency', 'wc-frontend-manager'); ?></label>
                        <select name="preferred_currency">
                            <option value="USD" <?php selected($preferred_currency, 'USD'); ?>>$ USD</option>
                            <option value="EUR" <?php selected($preferred_currency, 'EUR'); ?>>&#8364; EUR</option>
                            <option value="GBP" <?php selected($preferred_currency, 'GBP'); ?>>&#163; GBP</option>
                            <option value="PKR" <?php selected($preferred_currency, 'PKR'); ?>>Rs PKR</option>
                        </select>
                    </div>
                </div>
                <!-- </div> -->

                <!-- ========== MEMBERSHIP ========== -->
                <!-- <div class="modfolio-section"> -->
                <h3 class="section-title" style="margin-top: 20px;"><?php _e('Membership', 'wc-frontend-manager'); ?>
                </h3>

                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline modfolio-field-readonly">
                        <label><?php _e('Subscription', 'wc-frontend-manager'); ?></label>
                        <div class="field-value">
                            <span><?php echo esc_html(str_replace(' Plan', '', $membership_plan)); ?></span>
                            <a href="<?php echo home_url('/pricing/') ?>" type="button" class="edit-btn"
                                title="<?php _e('Edit', 'wc-frontend-manager'); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="modfolio-field modfolio-field-inline modfolio-field-readonly">
                        <label><?php _e('Commission', 'wc-frontend-manager'); ?></label>
                        <div class="field-value">
                            <span><?php echo esc_html($commission_rate); ?></span>
                        </div>
                    </div>
                    <div class="modfolio-field modfolio-field-inline modfolio-field-readonly">
                        <label><?php _e('Status', 'wc-frontend-manager'); ?></label>
                        <div class="field-value">
                            <span><?php echo esc_html($vendor_status); ?></span>
                        </div>
                    </div>
                </div>

                <div class="modfolio-row">
                    <div class="modfolio-field modfolio-field-inline modfolio-field-readonly">
                        <label><?php _e('Subscription Start Date', 'wc-frontend-manager'); ?></label>
                        <div class="field-value">
                            <span><?php echo esc_html($subscription_start); ?></span>
                        </div>
                    </div>
                    <div class="modfolio-field modfolio-field-inline modfolio-field-readonly">
                        <label><?php _e('Subscription End Date', 'wc-frontend-manager'); ?></label>
                        <div class="field-value">
                            <span><?php echo esc_html($subscription_end); ?></span>
                        </div>
                    </div>
                    <div class="modfolio-field modfolio-field-inline modfolio-field-readonly">
                        <label><?php _e('Notify via Email', 'wc-frontend-manager'); ?></label>
                        <div class="field-value">
                            <span><?php echo $notify_email === 'yes' ? __('Yes', 'wc-frontend-manager') : __('No', 'wc-frontend-manager'); ?></span>
                        </div>
                    </div>
                </div>

                <?php do_action('end_wcfm_user_profile', $user_id); ?>

                <!-- ========== SUBMIT ========== -->
                <div class="modfolio-submit">
                    <div class="wcfm-message" tabindex="-1"></div>
                    <div class="submit-buttons">
                        <input type="submit" name="save-data" value="<?php _e('Save', 'wc-frontend-manager'); ?>"
                            id="wcfmprofile_save_button" class="wcfm_submit_button modfolio-btn modfolio-btn-primary">
                    </div>
                </div>

                <!-- Hidden fields for WCFM compatibility -->
                <input type="hidden" name="first_name" id="first_name" value="<?php echo esc_attr($first_name); ?>">
                <input type="hidden" name="last_name" id="last_name" value="<?php echo esc_attr($last_name); ?>">
                <input type="hidden" name="wcfm_nonce" value="<?php echo wp_create_nonce('wcfm_profile'); ?>">
            </div>
        </form>

        <?php do_action('after_wcfm_wcvendors_profile'); ?>
    </div>
</div>

<style>
    <?php echo modfolio_wcfm_get_header_styles(); ?>

    /* ========== BASE ========== */
    .modfolio-profile-page {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background: #f5f5f5;
    }

    .modfolio-profile-page .wcfm-page-headig {
        display: none !important;
    }

    /* ========== SECTIONS ========== */
    .modfolio-section {
        background: #fff;
        border-radius: 20px;
        padding: 20px 20px 35px;
        margin-bottom: 24px;
        /* box-shadow: 0 2px 8px rgba(0,0,0,0.04); */
        /* border: 1px solid #e8e8e8; */
    }

    .section-title {
        font-size: 20px;
        font-weight: 800;
        color: #000;
        margin: 10px 0 10px 0;
        padding-bottom: 16px;
        /* border-bottom: 1px solid #e5e5e5; */

        /* font-size: 20px !important;
    font-weight: 800 !important;
    color: #000 !important;
    line-height: 1 !important;
    margin-bottom: 5px !important;
    font-style: normal !important;
    padding-top: 5px !important; */
    }

    /* ========== FORM ROWS ========== */
    .modfolio-row {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
    }

    .modfolio-row-end {
        justify-content: flex-end;
        margin-bottom: 0;
    }

    .modfolio-field {
        flex: 1;
        min-width: 0;
    }

    .modfolio-field.modfolio-field-third {
        flex: 0 0 calc(33.333% - 11px);
    }

    .modfolio-field.modfolio-field-dob,
    .modfolio-field.modfolio-field-age {
        flex: 0 0 calc(33.333% - 11px);
    }

    .modfolio-field.modfolio-field-spacer {
        flex: 0 0 calc(33.333% - 11px);
        background: transparent;
        border: none;
    }

    /* ========== INLINE FIELDS ========== */
    .modfolio-field.modfolio-field-inline {
        display: flex;
        align-items: center;
        background: #fff;
        /* border: 1px solid #e5e5e5; */
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 16px;
        min-height: 57px;
    }

    .modfolio-field.modfolio-field-inline>label {
        /* display: flex !important;
    align-items: center;
    font-size: 14px;
    font-weight: 500;
    color: #1a1a1a;
    margin: 0;
    padding: 0 20px;
    background: #fff;
    white-space: nowrap;
    min-width: fit-content;
    position: relative; */
        font-size: 13px;
        font-weight: 600;
        color: #1a1a1a;
        padding: 10px 14px;
        white-space: nowrap;
        min-width: fit-content;
        position: relative;
    }

    .modfolio-field.modfolio-field-inline>label::after {
        content: '';
        position: absolute;
        right: 0;
        top: 12px;
        bottom: 12px;
        height: 24px;
        width: 1px;
        background: #e5e5e5;
        background: #e0e0e0;
    }

    .modfolio-field.modfolio-field-inline input[type="text"],
    .modfolio-field.modfolio-field-inline input[type="email"],
    .modfolio-field.modfolio-field-inline input[type="password"],
    .modfolio-field.modfolio-field-inline input[type="date"],
    .modfolio-field.modfolio-field-inline select,
    #wcfm-main-contentainer .modfolio-field.modfolio-field-inline input,
    #wcfm-main-contentainer .modfolio-field.modfolio-field-inline select {
        flex: 1;
        border: none !important;
        border-radius: 0 !important;
        padding: 0px 16px;
        font-size: 14px;
        color: #666;
        background: #fff;
        outline: none;
        min-width: 0;
        box-shadow: none !important;
    }

    .modfolio-field.modfolio-field-inline input:focus,
    .modfolio-field.modfolio-field-inline select:focus {
        background: #fafafa;
    }

    .modfolio-field.modfolio-field-inline input::placeholder {
        color: #999;
    }

    .modfolio-field.modfolio-field-inline input[readonly] {
        background: #f9fafb;
        color: #666;
    }

    /* Readonly field with value display */
    .modfolio-field.modfolio-field-readonly .field-value {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        font-size: 14px;
        color: #1a1a1a;
    }

    .modfolio-field.modfolio-field-readonly .edit-btn {
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        color: #00c4aa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modfolio-field.modfolio-field-readonly .edit-btn:hover {
        color: #00b09a;
    }

    /* ========== TOGGLE SWITCH ========== */
    .modfolio-field.modfolio-field-toggle {
        display: flex;
        align-items: center;
        background: transparent;
        padding: 0;
        /* justify-content: space-between; */
        justify-content: start;
        gap: 40px;
    }

    .modfolio-field.modfolio-field-toggle>label {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
        margin: 0;
    }

    .toggle-switch {
        position: relative !important;
        width: 50px !important;
        height: 26px !important;
        display: inline-block !important;
    }

    .toggle-switch input {
        opacity: 0 !important;
        width: 0 !important;
        height: 0 !important;
        position: absolute !important;
    }

    .toggle-slider {
        position: relative !important;
        display: block !important;
        cursor: pointer !important;
        width: 50px !important;
        height: 26px !important;
        background-color: #e5e5e5 !important;
        transition: 0.3s !important;
        border-radius: 26px !important;
        display: none !important; /* Hidden Switch */
    }

    .toggle-slider::before {
        position: absolute !important;
        content: "" !important;
        height: 20px !important;
        width: 20px !important;
        left: 3px !important;
        bottom: 3px !important;
        background-color: white !important;
        transition: 0.3s !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    }

    .toggle-switch input:checked+.toggle-slider {
        background-color: #00c4aa !important;
    }

    .toggle-switch input:checked+.toggle-slider::before {
        transform: translateX(24px) !important;
    }

    /* ========== BUTTONS ========== */
    .modfolio-btn {
        border: none;
        border-radius: 30px;
        padding: 14px 32px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    #wcfm-main-contentainer input.wcfm_submit_button,
    .modfolio-btn-primary {
        background: #00c4aa !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(0, 196, 170, 0.25);
        padding-left: 20px;
        padding-right: 20px;
    }

    .modfolio-btn-primary:hover {
        background: #00b09a !important;
    }

    /* ========== SUBMIT ========== */
    .modfolio-submit {
        display: flex;
        justify-content: flex-end;
        padding: 0;
    }

    .modfolio-submit .wcfm-message {
        margin-bottom: 16px;
    }

    .submit-buttons {
        display: flex;
        gap: 16px;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1024px) {
        .modfolio-welcome-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
            padding: 24px;
        }

        .welcome-left {
            flex-direction: column;
        }

        .welcome-right {
            align-items: center;
        }

        .modfolio-row {
            flex-direction: column;
        }

        .modfolio-field.modfolio-field-third {
            flex: 1;
        }
    }

    @media (max-width: 768px) {
        .modfolio-section {
            /* padding: 20px; */
            padding: 15px 15px 22px;
            border-radius: 20px;
        }

        .section-title {
            font-size: 18px;
        }

        .welcome-name {
            font-size: 20px;
        }

        .modfolio-field.modfolio-field-inline {
            flex-direction: column;
            align-items: stretch;
        }

        .modfolio-field.modfolio-field-inline>label {
            width: 100%;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e5e5;
        }

        .modfolio-field.modfolio-field-inline>label::after {
            display: none;
        }

        .modfolio-field.modfolio-field-inline input,
        .modfolio-field.modfolio-field-inline select {
            width: 100%;
        }

        .modfolio-field.modfolio-field-readonly .field-value {
            padding: 12px 16px;
        }

        .modfolio-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<script>
    jQuery(document).ready(function ($) {
        // Calculate age from DOB
        function calculateAge(dob) {
            if (!dob) return '';
            var birthDate = new Date(dob);
            var today = new Date();
            var age = today.getFullYear() - birthDate.getFullYear();
            var monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age > 0 ? age + ' <?php _e("years", "wc-frontend-manager"); ?>' : '';
        }

        // Auto-calculate age when DOB changes
        $('input[name="vendor_dob"]').on('change', function () {
            var dob = $(this).val();
            var age = calculateAge(dob);
            $('input[name="vendor_age"]').val(age);
        });

        // Update password handler
        $('#update-password-btn').on('click', function () {
            var currentPassword = $('input[name="current_password"]').val();
            var newPassword = $('input[name="password"]').val();
            var confirmPassword = $('input[name="password_confirm"]').val();

            // Validation
            if (!currentPassword) {
                alert('<?php _e("Please enter your current password.", "wc-frontend-manager"); ?>');
                return;
            }
            if (!newPassword) {
                alert('<?php _e("Please enter a new password.", "wc-frontend-manager"); ?>');
                return;
            }
            if (newPassword !== confirmPassword) {
                alert('<?php _e("Passwords do not match.", "wc-frontend-manager"); ?>');
                return;
            }
            if (newPassword.length < 8) {
                alert('<?php _e("Password must be at least 8 characters long.", "wc-frontend-manager"); ?>');
                return;
            }

            // AJAX request to update password
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'modfolio_update_password',
                    current_password: currentPassword,
                    new_password: newPassword,
                    nonce: '<?php echo wp_create_nonce("modfolio_update_password"); ?>'
                },
                beforeSend: function () {
                    $('#update-password-btn').prop('disabled', true).text('<?php _e("Updating...", "wc-frontend-manager"); ?>');
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        $('input[name="current_password"]').val('');
                        $('input[name="password"]').val('');
                        $('input[name="password_confirm"]').val('');
                    } else {
                        alert(response.data.message || '<?php _e("Error updating password.", "wc-frontend-manager"); ?>');
                    }
                },
                error: function () {
                    alert('<?php _e("An error occurred. Please try again.", "wc-frontend-manager"); ?>');
                },
                complete: function () {
                    $('#update-password-btn').prop('disabled', false).text('<?php _e("Update Password", "wc-frontend-manager"); ?>');
                }
            });
        });

        // Sync full name to first/last name hidden fields
        $('input[name="full_name"]').on('change', function () {
            var fullName = $(this).val().trim();
            var parts = fullName.split(' ');
            var firstName = parts[0] || '';
            var lastName = parts.slice(1).join(' ') || '';
            $('#first_name').val(firstName);
            $('#last_name').val(lastName);
        });

        // Form submission
        $('#wcfm_profile_form').on('submit', function (e) {
            // Let WCFM handle the form submission
            // The form has the correct ID for WCFM processing
        });
    });
</script>