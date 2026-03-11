<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Minimog_Template_Loader' ) ) {
	class Minimog_Template_Loader {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			add_action( 'minimog_ajax_template_lazyload', [ $this, 'ajax_load_template_part' ] );
		}

		public function ajax_load_template_part() {
			check_ajax_referer( 'minimog-security', 'security' );

			$template = ! empty( $_GET['template'] ) ? sanitize_file_name( $_GET['template'] ) : '';
			$context  = ! empty( $_GET['context'] ) ? sanitize_file_name( $_GET['context'] ) : 'theme';

			if ( empty( $template ) ) {
				wp_send_json_error();
			}

			ob_start();
			switch ( $context ) {
				case 'wc' :
					wc_get_template( $template . '.php' );
					break;
				case 'wc_cart' :
					wc_get_template( "cart/{$template}.php" );
					break;
				default:
					minimog_load_template( $template );
					break;
			}
			$html = ob_get_clean();

			$response = [
				'template' => $html,
			];

			wp_send_json_success( $response );
		}
	}

	Minimog_Template_Loader::instance()->initialize();
}
