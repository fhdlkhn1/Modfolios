<?php
/**
 * Template part for display register form on modal.
 *
 * @link      https://codex.wordpress.org/Template_Hierarchy
 *
 * @package   Minimog
 * @since     1.0.0
 * @version   2.9.9
 */

$has_name_fields    = 'yes' === get_option( 'woocommerce_registration_input_name_enable', 'yes' );
$has_password_field = 'no' === get_option( 'woocommerce_registration_generate_password' );
$has_username_field = 'no' === get_option( 'woocommerce_registration_generate_username' );

defined( 'ABSPATH' ) || exit;
?>
<div class="minimog-modal modal-user-register" id="modal-user-register"
     data-template="template-parts/modal/modal-content-register" aria-hidden="true" role="dialog" hidden>
	<div class="modal-overlay"></div>
	<div class="modal-content">
		<div class="button-close-modal" role="button" aria-label="<?php esc_attr_e( 'Close', 'minimog' ); ?>"></div>
		<div class="modal-content-wrap">
			<div class="modal-content-inner">
				<div class="modal-content-header">
					<h3 class="modal-title"><?php esc_html_e( 'Sign Up', 'minimog' ); ?></h3>
					<p class="modal-description">
						<?php printf( esc_html__( 'Already have an account? %s Log in %s', 'minimog' ), '<a href="#" class="open-modal-login link-transition-01">', '</a>' ); ?>
					</p>
				</div>

				<div class="modal-content-body">

					<?php
					/**
					 * @since 2.9.9
					 */
					do_action( 'minimog/modal_user_register/before_form' );
					?>

					<form id="minimog-register-form" class="minimog-register-form" method="post">

						<?php do_action( 'minimog/modal_user_register/before_form_fields' ); ?>

						<?php if ( $has_name_fields ) : ?>
							<div class="row">
								<div class="form-group col-sm-6">
									<label for="ip_reg_first_name" class="form-label">
										<?php esc_html_e( 'First name', 'minimog' ); ?>
									</label>
									<input type="text" id="ip_reg_first_name" class="form-control form-input"
									       name="fname" placeholder="<?php esc_attr_e( 'First name', 'minimog' ); ?>"
									       required/>
								</div>
								<div class="form-group col-sm-6">
									<label for="ip_reg_last_name" class="form-label">
										<?php esc_html_e( 'Last name', 'minimog' ); ?>
									</label>
									<input type="text" id="ip_reg_last_name" class="form-control form-input"
									       name="lname" placeholder="<?php esc_attr_e( 'Last name', 'minimog' ); ?>"
									       required/>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $has_username_field ) : ?>
							<div class="form-group">
								<label for="ip_reg_username"
								       class="form-label"><?php esc_html_e( 'Username', 'minimog' ); ?></label>
								<input type="text" id="ip_reg_username" class="form-control form-input"
								       name="username" placeholder="<?php esc_attr_e( 'Username', 'minimog' ); ?>"
								       required/>
							</div>
						<?php endif; ?>

						<div class="form-group">
							<label for="ip_reg_email"
							       class="form-label"><?php esc_html_e( 'Email', 'minimog' ); ?></label>
							<input type="email" id="ip_reg_email" class="form-control form-input"
							       name="email" placeholder="<?php esc_attr_e( 'Your Email', 'minimog' ); ?>" required/>
							<?php if ( ! $has_password_field ) : ?>
								<p class="form-input-help"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'minimog' ); ?></p>
							<?php endif; ?>
						</div>

						<?php if ( $has_password_field ) : ?>
							<div class="form-group">
								<label for="ip_reg_password"
								       class="form-label"><?php esc_html_e( 'Password', 'minimog' ); ?></label>
								<div class="form-input-group form-input-password">
									<input type="password" id="ip_reg_password" class="form-control form-input"
									       name="password" placeholder="<?php esc_attr_e( 'Password', 'minimog' ); ?>"
									       required autocomplete="off"/>
									<button type="button" class="btn-pw-toggle" data-toggle="0"
									        aria-label="<?php esc_attr_e( 'Show password', 'minimog' ); ?>">
									</button>
								</div>
							</div>
						<?php endif; ?>

						<?php do_action( 'minimog/modal_user_register/after_form_fields' ); ?>

						<?php
						$show_accept = apply_filters( 'minimog/modal_user_register/show_accept_field', true );
						?>
						<?php if ( $show_accept ) : ?>
							<?php
							$privacy_page_id   = get_option( 'wp_page_for_privacy_policy', 0 );
							$privacy_page_url  = ! empty( $privacy_page_id ) ? get_permalink( $privacy_page_id ) : '';
							$privacy_link_html = esc_html__( 'Privacy Policy', 'minimog' );
							if ( ! empty( $privacy_page_url ) ) {
								$privacy_link_html = sprintf( '<a href="%1$s" class="minimog-privacy-policy-link" target="_blank">%2$s</a>', esc_url( $privacy_page_url ), $privacy_link_html );
							}

							$terms_conditions_page_id   = Minimog::setting( 'page_for_terms_and_conditions', 0 );
							$terms_conditions_page_url  = ! empty( $terms_conditions_page_id ) ? get_permalink( $terms_conditions_page_id ) : '';
							$terms_conditions_link_html = esc_html__( 'Terms of Use', 'minimog' );
							if ( ! empty( $terms_conditions_page_url ) ) {
								$terms_conditions_link_html = sprintf( '<a href="%1$s" class="minimog-terms-conditions-link" target="_blank">%2$s</a>', esc_url( $terms_conditions_page_url ), $terms_conditions_link_html );
							}

							$acceptance_text = pll__( Minimog::setting( 'register_form_acceptance_text' ) );
							$acceptance_text = str_replace( '{privacy}', $privacy_link_html, $acceptance_text );
							$acceptance_text = str_replace( '{terms}', $terms_conditions_link_html, $acceptance_text );
							?>
							<div class="form-group accept-account">
								<label class="form-label form-label-checkbox" for="ip_accept_account">
									<input type="checkbox" id="ip_accept_account" class="form-control"
									       name="accept_account" value="1"/><?php echo '' . $acceptance_text; ?>
								</label>
							</div>
						<?php endif; ?>

						<div class="form-response-messages"></div>

						<?php do_action( 'minimog/modal_user_register/before_form_submit' ); ?>

						<div class="form-group form-submit-wrap">
							<?php
							wp_nonce_field( 'user_register', 'user_register_nonce' );

							/**
							 * We need wc nonce to make it working properly Dokan plugin.
							 *
							 * @since 2.9.4
							 */
							wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' );
							?>
							<?php ?>
							<input type="hidden" name="action" value="minimog_user_register"/>
							<button type="submit"
							        class="button form-submit"><span><?php esc_html_e( 'Sign Up', 'minimog' ); ?></span>
							</button>
						</div>

						<?php do_action( 'minimog/modal_user_register/after_form_submit' ); ?>
					</form>

					<?php
					/**
					 * @since 2.9.9
					 */
					do_action( 'minimog/modal_user_register/after_form' );
					?>

				</div>
			</div>
		</div>
	</div>
</div>
