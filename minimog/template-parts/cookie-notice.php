<?php
/**
 *
 * @package Minimog
 * @since   1.0.0
 * @version 2.9.3
 */
defined( 'ABSPATH' ) || exit;
?>
<div id="cookie-notice-popup" class="cookie-notice-popup close">
	<div class="cookie-messages">
		<?php echo pll__( Minimog::setting( 'notice_cookie_messages' ) ); ?>
	</div>
	<a id="btn-accept-cookie"
	   class="tm-button tm-button-xs style-flat"><?php echo pll__( Minimog::setting( 'notice_cookie_button_text' ) ); ?></a>
</div>
