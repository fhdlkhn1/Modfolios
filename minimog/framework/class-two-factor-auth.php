<?php
/**
 * Two-Factor Authentication for WooCommerce Login
 *
 * Intercepts login when user has 2FA enabled (_two_factor_enabled = 'yes').
 * Sends a 6-digit code via email. User must verify before being logged in.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Modfolio_Two_Factor_Auth' ) ) {

	class Modfolio_Two_Factor_Auth {

		private static $instance = null;

		const CODE_EXPIRY    = 600; // 10 minutes
		const MAX_ATTEMPTS   = 5;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function initialize() {
			// Intercept standard WooCommerce form login (non-AJAX)
			add_filter( 'authenticate', [ $this, 'intercept_login_for_2fa' ], 100, 3 );

			// Output 2FA modal + inline JS/CSS in footer
			add_action( 'wp_footer', [ $this, 'output_2fa_modal' ] );

			// AJAX handlers (nopriv - user isn't logged in yet)
			add_action( 'wp_ajax_nopriv_modfolio_verify_2fa', [ $this, 'ajax_verify_2fa' ] );
			add_action( 'wp_ajax_nopriv_modfolio_cancel_2fa', [ $this, 'ajax_cancel_2fa' ] );
			add_action( 'wp_ajax_nopriv_modfolio_resend_2fa', [ $this, 'ajax_resend_2fa' ] );

			// Withdrawal 2FA (logged-in users)
			add_action( 'wp_ajax_modfolio_withdrawal_send_2fa', [ $this, 'ajax_withdrawal_send_2fa' ] );
			add_action( 'wp_ajax_modfolio_withdrawal_verify_2fa', [ $this, 'ajax_withdrawal_verify_2fa' ] );
		}

		/**
		 * Intercept the authenticate filter for standard WC form login.
		 * Skips AJAX modal login (handled separately in class-login-register.php).
		 */
		public function intercept_login_for_2fa( $user, $username, $password ) {
			if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) ) {
				return $user;
			}

			if ( get_user_meta( $user->ID, '_two_factor_enabled', true ) !== 'yes' ) {
				return $user;
			}

			// Skip if this is the theme's AJAX modal login - handled separately
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST['action'] ) && $_POST['action'] === 'minimog_user_login' ) {
				return $user;
			}

			$token = $this->generate_and_send_2fa( $user );

			// Set cookie so the modal knows 2FA is pending
			setcookie( 'modfolio_2fa_token', $token, time() + self::CODE_EXPIRY, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
			$_COOKIE['modfolio_2fa_token'] = $token;

			return new WP_Error(
				'2fa_required',
				__( 'A verification code has been sent to your email. Please enter it below.', 'minimog' )
			);
		}

		/**
		 * Check 2FA for the theme's AJAX modal login.
		 * Called from class-login-register.php user_login() method.
		 * If 2FA is needed, sends JSON response and dies.
		 *
		 * @param WP_User $user
		 * @return bool false if no 2FA needed
		 */
		public function handle_ajax_2fa_check( $user ) {
			if ( get_user_meta( $user->ID, '_two_factor_enabled', true ) !== 'yes' ) {
				return false;
			}

			$token = $this->generate_and_send_2fa( $user );

			wp_send_json_success( [
				'requires_2fa' => true,
				'2fa_token'    => $token,
				'messages'     => __( 'A verification code has been sent to your email.', 'minimog' ),
			] );
			// Dies here
		}

		/**
		 * Generate 6-digit code, store it, send email.
		 *
		 * @param WP_User $user
		 * @return string Token
		 */
		public function generate_and_send_2fa( $user ) {
			$code  = sprintf( '%06d', random_int( 0, 999999 ) );
			$token = wp_generate_password( 32, false );

			$remember = ! empty( $_POST['rememberme'] ) || ! empty( $_POST['remember'] );

			set_transient( 'modfolio_2fa_' . $token, [
				'user_id'  => $user->ID,
				'code'     => wp_hash( $code ),
				'remember' => $remember,
				'attempts' => 0,
			], self::CODE_EXPIRY );

			$this->send_2fa_email( $user, $code );

			return $token;
		}

		/**
		 * AJAX: Verify the 2FA code.
		 */
		public function ajax_verify_2fa() {
			$token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
			$code  = isset( $_POST['code'] ) ? sanitize_text_field( $_POST['code'] ) : '';

			if ( empty( $token ) || empty( $code ) ) {
				wp_send_json_error( [ 'message' => __( 'Please enter the verification code.', 'minimog' ) ] );
			}

			$data = get_transient( 'modfolio_2fa_' . $token );

			if ( ! $data ) {
				wp_send_json_error( [
					'message' => __( 'Verification code has expired. Please log in again.', 'minimog' ),
					'expired' => true,
				] );
			}

			// Rate limit
			if ( $data['attempts'] >= self::MAX_ATTEMPTS ) {
				delete_transient( 'modfolio_2fa_' . $token );
				wp_send_json_error( [
					'message' => __( 'Too many failed attempts. Please log in again.', 'minimog' ),
					'expired' => true,
				] );
			}

			if ( ! hash_equals( $data['code'], wp_hash( $code ) ) ) {
				$data['attempts']++;
				set_transient( 'modfolio_2fa_' . $token, $data, self::CODE_EXPIRY );
				$remaining = self::MAX_ATTEMPTS - $data['attempts'];
				wp_send_json_error( [
					'message' => sprintf(
						__( 'Invalid code. %d attempts remaining.', 'minimog' ),
						$remaining
					),
				] );
			}

			// Success - log the user in
			$user_id  = $data['user_id'];
			$remember = $data['remember'];

			delete_transient( 'modfolio_2fa_' . $token );

			// Clear the cookie
			setcookie( 'modfolio_2fa_token', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

			wp_set_auth_cookie( $user_id, $remember );
			do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) );

			// Determine redirect URL based on user role
			$redirect_url = $this->get_role_based_redirect( $user_id );

			wp_send_json_success( [
				'message'      => __( 'Verification successful! Redirecting...', 'minimog' ),
				'redirect_url' => $redirect_url,
			] );
		}

		/**
		 * AJAX: Cancel 2FA.
		 */
		public function ajax_cancel_2fa() {
			$token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';

			if ( $token ) {
				delete_transient( 'modfolio_2fa_' . $token );
			}

			setcookie( 'modfolio_2fa_token', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

			wp_send_json_success( [ 'message' => __( 'Verification cancelled.', 'minimog' ) ] );
		}

		/**
		 * AJAX: Resend 2FA code.
		 */
		public function ajax_resend_2fa() {
			$token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';

			if ( empty( $token ) ) {
				wp_send_json_error( [ 'message' => __( 'Invalid request.', 'minimog' ) ] );
			}

			$data = get_transient( 'modfolio_2fa_' . $token );

			if ( ! $data ) {
				wp_send_json_error( [
					'message' => __( 'Session expired. Please log in again.', 'minimog' ),
					'expired' => true,
				] );
			}

			$user = get_user_by( 'id', $data['user_id'] );
			if ( ! $user ) {
				wp_send_json_error( [ 'message' => __( 'User not found.', 'minimog' ) ] );
			}

			// Generate new code
			$code         = sprintf( '%06d', random_int( 0, 999999 ) );
			$data['code'] = wp_hash( $code );
			$data['attempts'] = 0;

			set_transient( 'modfolio_2fa_' . $token, $data, self::CODE_EXPIRY );

			$this->send_2fa_email( $user, $code );

			wp_send_json_success( [ 'message' => __( 'A new code has been sent to your email.', 'minimog' ) ] );
		}

		/**
		 * Get redirect URL based on user role.
		 */
		private function get_role_based_redirect( $user_id ) {
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return home_url();
			}

			$roles = (array) $user->roles;

			// Admin or WCFM vendor → store-manager
			if ( in_array( 'administrator', $roles, true ) || in_array( 'wcfm_vendor', $roles, true ) ) {
				if ( function_exists( 'get_wcfm_url' ) ) {
					return get_wcfm_url();
				}
				return home_url( '/store-manager/' );
			}

			// Buyer/customer/subscriber → my-account
			if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
				return wc_get_account_endpoint_url( 'dashboard' );
			}

			return home_url();
		}

		/**
		 * AJAX: Send 2FA code for withdrawal verification (logged-in user).
		 */
		public function ajax_withdrawal_send_2fa() {
			$user_id = get_current_user_id();
			if ( ! $user_id ) {
				wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'minimog' ) ] );
			}

			$user = get_userdata( $user_id );
			if ( ! $user ) {
				wp_send_json_error( [ 'message' => __( 'User not found.', 'minimog' ) ] );
			}

			// Check if 2FA is enabled for this user
			if ( get_user_meta( $user_id, '_two_factor_enabled', true ) !== 'yes' ) {
				wp_send_json_success( [ '2fa_not_required' => true ] );
			}

			$code  = sprintf( '%06d', random_int( 0, 999999 ) );
			$token = wp_generate_password( 32, false );

			set_transient( 'modfolio_wd_2fa_' . $token, [
				'user_id'  => $user_id,
				'code'     => wp_hash( $code ),
				'attempts' => 0,
			], self::CODE_EXPIRY );

			$this->send_2fa_email( $user, $code, 'withdrawal' );

			wp_send_json_success( [
				'2fa_required' => true,
				'2fa_token'    => $token,
				'message'      => __( 'A verification code has been sent to your email.', 'minimog' ),
			] );
		}

		/**
		 * AJAX: Verify 2FA code for withdrawal (logged-in user).
		 */
		public function ajax_withdrawal_verify_2fa() {
			$user_id = get_current_user_id();
			if ( ! $user_id ) {
				wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'minimog' ) ] );
			}

			$token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
			$code  = isset( $_POST['code'] ) ? sanitize_text_field( $_POST['code'] ) : '';

			if ( empty( $token ) || empty( $code ) ) {
				wp_send_json_error( [ 'message' => __( 'Please enter the verification code.', 'minimog' ) ] );
			}

			$data = get_transient( 'modfolio_wd_2fa_' . $token );

			if ( ! $data || (int) $data['user_id'] !== $user_id ) {
				wp_send_json_error( [
					'message' => __( 'Verification code has expired. Please try again.', 'minimog' ),
					'expired' => true,
				] );
			}

			if ( $data['attempts'] >= self::MAX_ATTEMPTS ) {
				delete_transient( 'modfolio_wd_2fa_' . $token );
				wp_send_json_error( [
					'message' => __( 'Too many failed attempts. Please try again.', 'minimog' ),
					'expired' => true,
				] );
			}

			if ( ! hash_equals( $data['code'], wp_hash( $code ) ) ) {
				$data['attempts']++;
				set_transient( 'modfolio_wd_2fa_' . $token, $data, self::CODE_EXPIRY );
				$remaining = self::MAX_ATTEMPTS - $data['attempts'];
				wp_send_json_error( [
					'message' => sprintf(
						__( 'Invalid code. %d attempts remaining.', 'minimog' ),
						$remaining
					),
				] );
			}

			// Valid! Clean up
			delete_transient( 'modfolio_wd_2fa_' . $token );

			wp_send_json_success( [
				'verified' => true,
				'message'  => __( 'Verification successful!', 'minimog' ),
			] );
		}

		/**
		 * Send the 2FA verification email (dark mode design).
		 */
		private function send_2fa_email( $user, $code, $context = 'login' ) {
			$display_name = $user->display_name ?: $user->user_login;
			$logo_url     = site_url( '/wp-content/uploads/2025/11/Logo-e1763359370675.png' );
			$subject = $context === 'withdrawal'
				? __( 'Your Withdrawal Verification Code - Modfolios', 'minimog' )
				: __( 'Your Login Verification Code - Modfolios', 'minimog' );
			$body         = $this->get_email_html( $display_name, $code, $logo_url );
			$headers      = [ 'Content-Type: text/html; charset=UTF-8' ];

			wp_mail( $user->user_email, $subject, $body, $headers );
		}

		/**
		 * Dark-mode email HTML template.
		 */
		private function get_email_html( $name, $code, $logo_url ) {
			ob_start();
			?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#0a0a0a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,sans-serif;">
	<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#0a0a0a;padding:40px 20px;">
		<tr>
			<td align="center">
				<table role="presentation" width="520" cellspacing="0" cellpadding="0" style="background-color:#1a1a1a;border-radius:12px;overflow:hidden;">
					<!-- Logo -->
					<tr>
						<td style="padding:32px 40px 20px;">
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="Modfolios" style="height:40px;width:auto;">
						</td>
					</tr>
					<!-- Greeting -->
					<tr>
						<td style="padding:0 40px 8px;">
							<p style="margin:0;font-size:14px;color:#4ECDC4;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">HI <?php echo esc_html( strtoupper( $name ) ); ?>,</p>
						</td>
					</tr>
					<!-- Heading -->
					<tr>
						<td style="padding:0 40px 12px;">
							<h1 style="margin:0;font-size:24px;color:#ffffff;font-weight:700;line-height:1.3;">Your Login Verification Code</h1>
						</td>
					</tr>
					<!-- Description -->
					<tr>
						<td style="padding:0 40px 24px;">
							<p style="margin:0;font-size:14px;color:#a0a0a0;line-height:1.6;">For security, please use the following verification code to complete your login. Do not share this code with anyone.</p>
						</td>
					</tr>
					<!-- Code Label -->
					<tr>
						<td style="padding:0 40px 8px;">
							<p style="margin:0;font-size:13px;color:#a0a0a0;font-weight:600;">Verification Code:</p>
						</td>
					</tr>
					<!-- Code Box -->
					<tr>
						<td style="padding:0 40px 24px;">
							<table role="presentation" cellspacing="0" cellpadding="0">
								<tr>
									<td style="background-color:#2a2a2a;border-radius:8px;padding:16px 32px;text-align:center;letter-spacing:8px;font-size:32px;font-weight:700;color:#4ECDC4;font-family:'Courier New',monospace;">
										<?php echo esc_html( $code ); ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<!-- Expiry Note -->
					<tr>
						<td style="padding:0 40px 32px;">
							<p style="margin:0;font-size:13px;color:#666666;line-height:1.5;">This code expires in <strong style="color:#a0a0a0;">10 minutes</strong>. If you did not attempt to log in, please secure your account immediately.</p>
						</td>
					</tr>
					<!-- Divider -->
					<tr>
						<td style="padding:0 40px;">
							<hr style="border:none;border-top:1px solid #2a2a2a;margin:0;">
						</td>
					</tr>
					<!-- Footer -->
					<tr>
						<td style="padding:24px 40px 32px;text-align:center;">
							<p style="margin:0;font-size:12px;color:#555555;line-height:1.5;">&copy; <?php echo gmdate( 'Y' ); ?> Modfolios. All rights reserved.</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
			<?php
			return ob_get_clean();
		}

		/**
		 * Output 2FA modal HTML, CSS, and JS in the footer.
		 */
		public function output_2fa_modal() {
			if ( is_user_logged_in() ) {
				return;
			}

			$token          = isset( $_COOKIE['modfolio_2fa_token'] ) ? sanitize_text_field( $_COOKIE['modfolio_2fa_token'] ) : '';
			$has_pending_2fa = ! empty( $token ) && get_transient( 'modfolio_2fa_' . $token );
			$ajax_url       = admin_url( 'admin-ajax.php' );
			?>
<!-- Modfolio 2FA Verification Modal -->
<style>
.mf2fa-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.5);
	z-index: 999999;
	display: flex;
	align-items: center;
	justify-content: center;
	opacity: 0;
	visibility: hidden;
	transition: opacity 0.3s ease, visibility 0.3s ease;
}
.mf2fa-overlay.active {
	opacity: 1;
	visibility: visible;
}
.mf2fa-modal {
	background: #ffffff;
	border-radius: 12px;
	width: 420px;
	max-width: 90vw;
	box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
	overflow: hidden;
	transform: translateY(20px);
	transition: transform 0.3s ease;
}
.mf2fa-overlay.active .mf2fa-modal {
	transform: translateY(0);
}
.mf2fa-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20px 24px;
	border-bottom: 1px solid #e8e8e8;
}
.mf2fa-header h3 {
	margin: 0;
	font-size: 18px;
	font-weight: 700;
	color: #1a1a1a;
}
.mf2fa-close {
	background: none;
	border: none;
	font-size: 22px;
	color: #999;
	cursor: pointer;
	padding: 0;
	line-height: 1;
	transition: color 0.2s;
}
.mf2fa-close:hover {
	color: #333;
}
.mf2fa-body {
	padding: 32px 24px;
	text-align: center;
}
.mf2fa-icon {
	width: 56px;
	height: 56px;
	margin: 0 auto 20px;
}
.mf2fa-icon svg {
	width: 100%;
	height: 100%;
}
.mf2fa-body p {
	margin: 0 0 24px;
	font-size: 14px;
	color: #555;
	line-height: 1.6;
}
.mf2fa-input {
	width: 100%;
	padding: 14px 16px;
	border: 2px solid #e0e0e0;
	border-radius: 8px;
	font-size: 18px;
	text-align: center;
	letter-spacing: 6px;
	font-weight: 600;
	color: #1a1a1a;
	outline: none;
	transition: border-color 0.2s;
	box-sizing: border-box;
}
.mf2fa-input:focus {
	border-color: #4ECDC4;
}
.mf2fa-input::placeholder {
	letter-spacing: 0;
	font-weight: 400;
	font-size: 14px;
	color: #aaa;
}
.mf2fa-btn {
	display: block;
	width: 100%;
	padding: 14px;
	margin-top: 16px;
	background: #4ECDC4;
	color: #fff;
	border: none;
	border-radius: 8px;
	font-size: 16px;
	font-weight: 700;
	cursor: pointer;
	transition: background 0.2s;
}
.mf2fa-btn:hover {
	background: #3dbdb5;
}
.mf2fa-btn:disabled {
	background: #b0e8e4;
	cursor: not-allowed;
}
.mf2fa-cancel {
	display: inline-block;
	margin-top: 16px;
	font-size: 14px;
	color: #888;
	cursor: pointer;
	border: none;
	background: none;
	text-decoration: none;
	transition: color 0.2s;
}
.mf2fa-cancel:hover {
	color: #333;
}
.mf2fa-message {
	margin-top: 12px;
	padding: 10px 14px;
	border-radius: 6px;
	font-size: 13px;
	display: none;
}
.mf2fa-message.error {
	display: block;
	background: #fff5f5;
	color: #e53e3e;
	border: 1px solid #fed7d7;
}
.mf2fa-message.success {
	display: block;
	background: #f0fff4;
	color: #38a169;
	border: 1px solid #c6f6d5;
}
.mf2fa-resend {
	margin-top: 12px;
	font-size: 13px;
	color: #4ECDC4;
	cursor: pointer;
	border: none;
	background: none;
	text-decoration: underline;
	transition: color 0.2s;
}
.mf2fa-resend:hover {
	color: #3dbdb5;
}
.mf2fa-resend:disabled {
	color: #ccc;
	cursor: not-allowed;
	text-decoration: none;
}
.mf2fa-spinner {
	display: inline-block;
	width: 16px;
	height: 16px;
	border: 2px solid #fff;
	border-top-color: transparent;
	border-radius: 50%;
	animation: mf2fa-spin 0.6s linear infinite;
	vertical-align: middle;
	margin-right: 6px;
}
@keyframes mf2fa-spin {
	to { transform: rotate(360deg); }
}
</style>

<div id="mf2fa-overlay" class="mf2fa-overlay">
	<div class="mf2fa-modal">
		<div class="mf2fa-header">
			<h3 style="color: #333 !important;"><?php esc_html_e( '2FA Confirmation', 'minimog' ); ?></h3>
			<span type="button" class="mf2fa-close" id="mf2fa-close">&times;</span>
		</div>
		<div class="mf2fa-body">
			<div class="mf2fa-icon">
				<svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="28" cy="28" r="28" fill="#e6faf8"/>
					<path d="M28 14l12 5.5v8.25c0 7.7-5.1 14.9-12 16.75-6.9-1.85-12-9.05-12-16.75V19.5L28 14z" stroke="#4ECDC4" stroke-width="2" fill="none"/>
					<path d="M22 28.5l4 4 8-8" stroke="#4ECDC4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
				</svg>
			</div>
			<p><?php esc_html_e( 'For security, please enter the 6-digit code sent to your email to complete your login.', 'minimog' ); ?></p>
			<input type="text" id="mf2fa-code" class="mf2fa-input" placeholder="<?php esc_attr_e( 'Enter 6-digit code', 'minimog' ); ?>" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
			<button type="button" id="mf2fa-verify" class="mf2fa-btn"><?php esc_html_e( 'Verify & Confirm', 'minimog' ); ?></button>
			<div id="mf2fa-message" class="mf2fa-message"></div>
			<span type="button" id="mf2fa-resend" class="mf2fa-resend"><?php esc_html_e( 'Resend code', 'minimog' ); ?></span>
			<br>
			<!-- <button type="button" id="mf2fa-cancel" class="mf2fa-cancel"><?php //esc_html_e( 'Cancel', 'minimog' ); ?></button> -->
		</div>
	</div>
</div>

<script>
(function() {
	'use strict';

	var overlay   = document.getElementById('mf2fa-overlay'),
	    codeInput = document.getElementById('mf2fa-code'),
	    verifyBtn = document.getElementById('mf2fa-verify'),
	    closeBtn  = document.getElementById('mf2fa-close'),
	    resendBtn = document.getElementById('mf2fa-resend'),
	    msgEl     = document.getElementById('mf2fa-message'),
	    ajaxUrl   = <?php echo wp_json_encode( $ajax_url ); ?>,
	    token     = <?php echo wp_json_encode( $has_pending_2fa ? $token : '' ); ?>;

	function getCookie(name) {
		var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
		return match ? match[2] : '';
	}

	function deleteCookie(name) {
		document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=<?php echo esc_js( COOKIEPATH ); ?>';
	}

	function openModal(t) {
		if (t) token = t;
		overlay.classList.add('active');
		codeInput.value = '';
		codeInput.focus();
		showMsg('', '');
	}

	function closeModal() {
		overlay.classList.remove('active');
	}

	function showMsg(text, type) {
		if (!text) {
			msgEl.style.display = 'none';
			msgEl.className = 'mf2fa-message';
			return;
		}
		msgEl.textContent = text;
		msgEl.className = 'mf2fa-message ' + type;
	}

	function setLoading(loading) {
		verifyBtn.disabled = loading;
		if (loading) {
			verifyBtn.innerHTML = '<span class="mf2fa-spinner"></span> Verifying...';
		} else {
			verifyBtn.textContent = '<?php echo esc_js( __( 'Verify & Confirm', 'minimog' ) ); ?>';
		}
	}

	function ajaxPost(action, data, callback) {
		var formData = new FormData();
		formData.append('action', action);
		formData.append('token', token);
		for (var key in data) {
			formData.append(key, data[key]);
		}
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxUrl);
		xhr.onload = function() {
			var resp;
			try { resp = JSON.parse(xhr.responseText); } catch(e) { resp = null; }
			callback(resp);
		};
		xhr.onerror = function() { callback(null); };
		xhr.send(formData);
	}

	// Verify
	verifyBtn.addEventListener('click', function() {
		var code = codeInput.value.trim();
		if (code.length !== 6 || !/^\d{6}$/.test(code)) {
			showMsg('<?php echo esc_js( __( 'Please enter a valid 6-digit code.', 'minimog' ) ); ?>', 'error');
			return;
		}
		setLoading(true);
		showMsg('', '');
		ajaxPost('modfolio_verify_2fa', { code: code }, function(resp) {
			setLoading(false);
			if (!resp) {
				showMsg('<?php echo esc_js( __( 'An error occurred. Please try again.', 'minimog' ) ); ?>', 'error');
				return;
			}
			if (resp.success) {
				showMsg(resp.data.message, 'success');
				deleteCookie('modfolio_2fa_token');
				setTimeout(function() {
					if (resp.data.redirect_url) {
						window.location.href = resp.data.redirect_url;
					} else {
						location.reload();
					}
				}, 800);
			} else {
				showMsg(resp.data.message, 'error');
				codeInput.value = '';
				codeInput.focus();
				if (resp.data.expired) {
					setTimeout(function() {
						closeModal();
						deleteCookie('modfolio_2fa_token');
					}, 2000);
				}
			}
		});
	});

	// Cancel
	function handleCancel() {
		ajaxPost('modfolio_cancel_2fa', {}, function() {});
		deleteCookie('modfolio_2fa_token');
		closeModal();
		token = '';
		// Clear login form
		var forms = document.querySelectorAll('.woocommerce-form-login, #minimog-login-form');
		forms.forEach(function(form) {
			var inputs = form.querySelectorAll('input[type="text"], input[type="password"], input[type="email"]');
			inputs.forEach(function(input) { input.value = ''; });
		});
	}
	closeBtn.addEventListener('click', handleCancel);

	// Close on overlay click
	overlay.addEventListener('click', function(e) {
		if (e.target === overlay) handleCancel();
	});

	// Close on Escape key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && overlay.classList.contains('active')) {
			handleCancel();
		}
	});

	// Enter key to verify
	codeInput.addEventListener('keydown', function(e) {
		if (e.key === 'Enter') {
			e.preventDefault();
			verifyBtn.click();
		}
	});

	// Only allow digits
	codeInput.addEventListener('input', function() {
		this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
	});

	// Resend
	resendBtn.addEventListener('click', function() {
		if (resendBtn.disabled) return;
		resendBtn.disabled = true;
		resendBtn.textContent = '<?php echo esc_js( __( 'Sending...', 'minimog' ) ); ?>';
		ajaxPost('modfolio_resend_2fa', {}, function(resp) {
			if (resp && resp.success) {
				showMsg(resp.data.message, 'success');
			} else if (resp) {
				showMsg(resp.data.message, 'error');
				if (resp.data.expired) {
					setTimeout(function() {
						closeModal();
						deleteCookie('modfolio_2fa_token');
					}, 2000);
				}
			}
			// Cooldown 30s
			var countdown = 30;
			resendBtn.textContent = '<?php echo esc_js( __( 'Resend code', 'minimog' ) ); ?> (' + countdown + 's)';
			var timer = setInterval(function() {
				countdown--;
				if (countdown <= 0) {
					clearInterval(timer);
					resendBtn.disabled = false;
					resendBtn.textContent = '<?php echo esc_js( __( 'Resend code', 'minimog' ) ); ?>';
				} else {
					resendBtn.textContent = '<?php echo esc_js( __( 'Resend code', 'minimog' ) ); ?> (' + countdown + 's)';
				}
			}, 1000);
		});
	});

	// Auto-open if 2FA is pending (standard WooCommerce form login)
	if (token) {
		openModal(token);
	}

	// Expose openModal globally for the AJAX modal login
	window.modfolio2faOpen = openModal;

})();
</script>
			<?php
		}
	}

	Modfolio_Two_Factor_Auth::instance()->initialize();
}
