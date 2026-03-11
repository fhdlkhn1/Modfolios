<?php
/**
 * Custom Shots Email Handler
 * Handles all email notifications for custom shot requests
 */

if (!defined('ABSPATH')) exit;

class Customshot_Emails {

    /**
     * Get site name for email
     */
    private static function get_site_name() {
        return get_bloginfo('name');
    }

    /**
     * Get site URL
     */
    private static function get_site_url() {
        return home_url();
    }

    /**
     * Get email header
     */
    private static function get_email_header() {
        $site_name = self::get_site_name();
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='margin: 0; padding: 0; background-color: #f5f5f5; font-family: Arial, sans-serif;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f5f5f5; padding: 40px 20px;'>
        <tr>
            <td align='center'>
                <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden;'>
                    <!-- Header -->
                    <tr>
                        <td style='background-color: #00bcd4; padding: 30px; text-align: center;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>{$site_name}</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style='padding: 40px 30px;'>
";
    }

    /**
     * Get email footer
     */
    private static function get_email_footer() {
        $site_name = self::get_site_name();
        $site_url = self::get_site_url();
        $year = date('Y');

        return "
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f9f9f9; padding: 30px; text-align: center; border-top: 1px solid #e5e5e5;'>
                            <p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>
                                Thank you for using {$site_name}
                            </p>
                            <p style='margin: 0; color: #999; font-size: 12px;'>
                                &copy; {$year} {$site_name}. All rights reserved.
                            </p>
                            <p style='margin: 10px 0 0 0;'>
                                <a href='{$site_url}' style='color: #00bcd4; text-decoration: none; font-size: 12px;'>Visit our website</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";
    }

    /**
     * Send email with HTML template
     */
    private static function send_email($to, $subject, $content) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::get_site_name() . ' <' . get_option('admin_email') . '>'
        );

        $html = self::get_email_header() . $content . self::get_email_footer();

        return wp_mail($to, $subject, $html, $headers);
    }

    /**
     * Send email to vendor when new request is received
     */
    public static function send_new_request_to_vendor($shot_id) {
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);

        $vendor = get_user_by('id', $vendor_id);
        $buyer = get_user_by('id', $buyer_id);

        if (!$vendor || !$buyer) return false;

        $shot = get_post($shot_id);
        $budget = get_post_meta($shot_id, '_customshot_budget', true);
        $shoot_date = get_post_meta($shot_id, '_customshot_shoot_date', true);
        $brief = get_post_meta($shot_id, '_customshot_brief', true);

        $shoot_type_terms = wp_get_post_terms($shot_id, 'shoot_type');
        $shoot_type = !empty($shoot_type_terms) ? $shoot_type_terms[0]->name : '-';

        $dashboard_url = get_wcfm_customshots_url();
        $detail_url = add_query_arg(array('tab' => 'view', 'request_id' => $shot_id), $dashboard_url);

        $subject = sprintf(__('New Custom Shot Request #%d', 'vendor-customshot'), $shot_id);

        $content = "
            <h2 style='color: #333; margin: 0 0 20px 0; font-size: 20px;'>New Custom Shot Request</h2>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Hello {$vendor->display_name},
            </p>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                You have received a new custom shot request from <strong>{$buyer->display_name}</strong>.
            </p>

            <!-- Request Details Box -->
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f9f9f9; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px;'>
                        <table width='100%' cellpadding='0' cellspacing='0'>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e5e5e5;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Request ID</span><br>
                                    <span style='color: #333; font-size: 14px; font-weight: 600;'>#{$shot_id}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e5e5e5;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Type of Shoot</span><br>
                                    <span style='color: #333; font-size: 14px;'>{$shoot_type}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e5e5e5;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Budget Offered</span><br>
                                    <span style='color: #333; font-size: 18px; font-weight: 700;'>$" . number_format((float)$budget, 0) . " USD</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Delivery Date</span><br>
                                    <span style='color: #333; font-size: 14px;'>" . ($shoot_date ? date_i18n('M d, Y', strtotime($shoot_date)) : '-') . "</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                <strong>Project Brief:</strong><br>
                " . nl2br(esc_html($brief)) . "
            </p>

            <p style='text-align: center; margin: 30px 0;'>
                <a href='{$detail_url}' style='display: inline-block; background-color: #00bcd4; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 25px; font-size: 14px; font-weight: 500;'>View Request Details</a>
            </p>

            <p style='color: #999; font-size: 12px; margin: 20px 0 0 0;'>
                Please log in to your dashboard to review and respond to this request.
            </p>
        ";

        return self::send_email($vendor->user_email, $subject, $content);
    }

    /**
     * Send email to buyer when quote is received
     */
    public static function send_quote_to_buyer($shot_id) {
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);

        $vendor = get_user_by('id', $vendor_id);
        $buyer = get_user_by('id', $buyer_id);

        if (!$vendor || !$buyer) return false;

        $vendor_quote = get_post_meta($shot_id, '_customshot_vendor_quote', true);
        $vendor_note = get_post_meta($shot_id, '_customshot_vendor_note', true);
        $delivery_date = get_post_meta($shot_id, '_customshot_delivery_date', true);

        $subject = sprintf(__('Quote Received for Your Custom Shot Request #%d', 'vendor-customshot'), $shot_id);

        $content = "
            <h2 style='color: #333; margin: 0 0 20px 0; font-size: 20px;'>Quote Received!</h2>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Hello {$buyer->display_name},
            </p>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Great news! <strong>{$vendor->display_name}</strong> has sent you a quote for your custom shot request.
            </p>

            <!-- Quote Details Box -->
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #e8f5e9; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 25px; text-align: center;'>
                        <p style='margin: 0 0 5px 0; color: #388e3c; font-size: 12px; text-transform: uppercase;'>Quote Amount</p>
                        <p style='margin: 0; color: #333; font-size: 32px; font-weight: 700;'>$" . number_format((float)$vendor_quote, 2) . " USD</p>
                    </td>
                </tr>
            </table>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f9f9f9; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px;'>
                        <table width='100%' cellpadding='0' cellspacing='0'>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e5e5e5;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Request ID</span><br>
                                    <span style='color: #333; font-size: 14px; font-weight: 600;'>#{$shot_id}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e5e5e5;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Vendor</span><br>
                                    <span style='color: #333; font-size: 14px;'>{$vendor->display_name}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Delivery Date</span><br>
                                    <span style='color: #333; font-size: 14px;'>" . ($delivery_date ? date_i18n('M d, Y', strtotime($delivery_date)) : '-') . "</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            " . ($vendor_note ? "
            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                <strong>Vendor's Note:</strong><br>
                " . nl2br(esc_html($vendor_note)) . "
            </p>
            " : "") . "

            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . home_url('/my-account/') . "' style='display: inline-block; background-color: #00bcd4; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 25px; font-size: 14px; font-weight: 500;'>Review Quote</a>
            </p>

            <p style='color: #999; font-size: 12px; margin: 20px 0 0 0;'>
                Please log in to your account to accept or decline this quote.
            </p>
        ";

        return self::send_email($buyer->user_email, $subject, $content);
    }

    /**
     * Send email to buyer when request is declined
     */
    public static function send_declined_to_buyer($shot_id) {
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);

        $vendor = get_user_by('id', $vendor_id);
        $buyer = get_user_by('id', $buyer_id);

        if (!$vendor || !$buyer) return false;

        $subject = sprintf(__('Update on Your Custom Shot Request #%d', 'vendor-customshot'), $shot_id);

        $content = "
            <h2 style='color: #333; margin: 0 0 20px 0; font-size: 20px;'>Request Update</h2>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Hello {$buyer->display_name},
            </p>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                We wanted to let you know that <strong>{$vendor->display_name}</strong> has declined your custom shot request #<strong>{$shot_id}</strong>.
            </p>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #ffebee; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px; text-align: center;'>
                        <p style='margin: 0; color: #d32f2f; font-size: 14px;'>
                            This request has been declined by the vendor.
                        </p>
                    </td>
                </tr>
            </table>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Don't worry! There are many other talented creators on our platform. Feel free to browse and send requests to other creators.
            </p>

            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . home_url('/creators/') . "' style='display: inline-block; background-color: #00bcd4; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 25px; font-size: 14px; font-weight: 500;'>Browse Creators</a>
            </p>
        ";

        return self::send_email($buyer->user_email, $subject, $content);
    }

    /**
     * Send email to buyer when vendor accepts request directly (no quote)
     */
    public static function send_accepted_to_buyer($shot_id) {
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);

        $vendor = get_user_by('id', $vendor_id);
        $buyer = get_user_by('id', $buyer_id);

        if (!$vendor || !$buyer) return false;

        $shot = get_post($shot_id);
        $messages_url = function_exists('get_wcfm_customshot_messages_url') ? get_wcfm_customshot_messages_url($shot_id) : home_url('/my-account/');

        $subject = sprintf(__('Your Custom Shot Request #%d Has Been Accepted!', 'vendor-customshot'), $shot_id);

        $content = "
            <h2 style='color: #333; margin: 0 0 20px 0; font-size: 20px;'>Request Accepted!</h2>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Hello {$buyer->display_name},
            </p>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Great news! <strong>{$vendor->display_name}</strong> has accepted your custom shot request.
            </p>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #e8f5e9; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 25px; text-align: center;'>
                        <p style='margin: 0; color: #388e3c; font-size: 18px; font-weight: 600;'>
                            Your request has been accepted!
                        </p>
                        <p style='margin: 10px 0 0 0; color: #666; font-size: 14px;'>
                            You can now start communicating with the vendor.
                        </p>
                    </td>
                </tr>
            </table>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f9f9f9; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px;'>
                        <table width='100%' cellpadding='0' cellspacing='0'>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e5e5e5;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Request ID</span><br>
                                    <span style='color: #333; font-size: 14px; font-weight: 600;'>#{$shot_id}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Vendor</span><br>
                                    <span style='color: #333; font-size: 14px;'>{$vendor->display_name}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                A chat has been started so you can discuss the project details with the vendor. Log in to your account to start the conversation!
            </p>

            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . home_url('/my-account/') . "' style='display: inline-block; background-color: #00bcd4; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 25px; font-size: 14px; font-weight: 500;'>Go to Messages</a>
            </p>
        ";

        return self::send_email($buyer->user_email, $subject, $content);
    }

    /**
     * Send email to vendor when buyer accepts quote
     */
    public static function send_accepted_to_vendor($shot_id) {
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);

        $vendor = get_user_by('id', $vendor_id);
        $buyer = get_user_by('id', $buyer_id);

        if (!$vendor || !$buyer) return false;

        $vendor_quote = get_post_meta($shot_id, '_customshot_vendor_quote', true);
        $delivery_date = get_post_meta($shot_id, '_customshot_delivery_date', true);

        $dashboard_url = get_wcfm_customshots_url();
        $detail_url = add_query_arg(array('tab' => 'view', 'request_id' => $shot_id), $dashboard_url);

        $subject = sprintf(__('Great News! Quote Accepted for Request #%d', 'vendor-customshot'), $shot_id);

        $content = "
            <h2 style='color: #333; margin: 0 0 20px 0; font-size: 20px;'>Quote Accepted!</h2>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Hello {$vendor->display_name},
            </p>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Congratulations! <strong>{$buyer->display_name}</strong> has accepted your quote for custom shot request #<strong>{$shot_id}</strong>.
            </p>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #e8f5e9; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 25px; text-align: center;'>
                        <p style='margin: 0 0 5px 0; color: #388e3c; font-size: 14px;'>Your quote of</p>
                        <p style='margin: 0; color: #333; font-size: 32px; font-weight: 700;'>$" . number_format((float)$vendor_quote, 2) . " USD</p>
                        <p style='margin: 10px 0 0 0; color: #388e3c; font-size: 14px;'>has been accepted!</p>
                    </td>
                </tr>
            </table>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f9f9f9; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px;'>
                        <table width='100%' cellpadding='0' cellspacing='0'>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e5e5e5;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Buyer</span><br>
                                    <span style='color: #333; font-size: 14px;'>{$buyer->display_name}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0;'>
                                    <span style='color: #00bcd4; font-size: 12px; text-transform: uppercase;'>Delivery Deadline</span><br>
                                    <span style='color: #333; font-size: 14px;'>" . ($delivery_date ? date_i18n('M d, Y', strtotime($delivery_date)) : '-') . "</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                You can now start working on this project. The funds have been held in escrow and will be released upon project completion.
            </p>

            <p style='text-align: center; margin: 30px 0;'>
                <a href='{$detail_url}' style='display: inline-block; background-color: #00bcd4; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 25px; font-size: 14px; font-weight: 500;'>View Project Details</a>
            </p>
        ";

        return self::send_email($vendor->user_email, $subject, $content);
    }

    /**
     * Send email to vendor when buyer rejects quote
     */
    public static function send_rejected_to_vendor($shot_id) {
        $vendor_id = get_post_meta($shot_id, '_customshot_vendor_id', true);
        $buyer_id = get_post_meta($shot_id, '_customshot_buyer_id', true);

        $vendor = get_user_by('id', $vendor_id);
        $buyer = get_user_by('id', $buyer_id);

        if (!$vendor || !$buyer) return false;

        $subject = sprintf(__('Update on Custom Shot Request #%d', 'vendor-customshot'), $shot_id);

        $content = "
            <h2 style='color: #333; margin: 0 0 20px 0; font-size: 20px;'>Quote Update</h2>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Hello {$vendor->display_name},
            </p>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                We wanted to let you know that <strong>{$buyer->display_name}</strong> has declined your quote for custom shot request #<strong>{$shot_id}</strong>.
            </p>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #fff8e1; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px; text-align: center;'>
                        <p style='margin: 0; color: #f57c00; font-size: 14px;'>
                            The buyer has decided not to proceed with this quote.
                        </p>
                    </td>
                </tr>
            </table>

            <p style='color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;'>
                Don't be discouraged! Keep your portfolio updated and continue responding to new requests.
            </p>
        ";

        return self::send_email($vendor->user_email, $subject, $content);
    }
}
