<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add extra fields to nav menu edit
 */
if ( ! class_exists( 'Minimog_Extra_Nav_Menu_Items' ) ) {
	class Minimog_Extra_Nav_Menu_Items {

		protected static $instance = null;

		function __construct() {

		}

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		function initialize() {
			// Allow SVG.
			add_filter( 'wp_check_filetype_and_ext', [ $this, 'check_svg_support' ], 10, 4 );
			add_filter( 'upload_mimes', [ $this, 'add_svg_support' ] );

			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'add_custom_fields' ], 10, 5 );
			add_action( 'wp_update_nav_menu_item', [ $this, 'update_nav_menu_item' ], 10, 3 );
			add_filter( 'wp_setup_nav_menu_item', [ $this, 'setup_nav_menu_item' ] );
		}

		public function enqueue_scripts() {
			$screen = get_current_screen();
			if ( 'nav-menus' === $screen->id ) {
				wp_enqueue_media();
				wp_enqueue_script( 'menu-icon', MINIMOG_THEME_ASSETS_URI . '/admin/js/menu-icon.js', array( 'jquery' ), null, true );
			}
		}

		function check_svg_support( $data, $file, $filename, $mimes ) {
			global $wp_version;
			if ( $wp_version !== '4.7.1' ) {
				return $data;
			}

			$filetype = wp_check_filetype( $filename, $mimes );

			return [
				'ext'             => $filetype['ext'],
				'type'            => $filetype['type'],
				'proper_filename' => $data['proper_filename'],
			];
		}

		function add_svg_support( $mimes ) {
			$mimes['svg'] = 'image/svg+xml';

			return $mimes;
		}

		public function add_custom_fields( $item_id, $menu_item, $depth, $args, $current_object_id ) {
			?>
			<p id="<?php echo 'field-item-icon-' . $item_id ?>" class="field-item-icon description description-wide">
				<label for="edit-menu-item-image-hover-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Icon', 'minimog' ); ?>
					<span class="minimog-menu-icon-view"
						<?php if ( ! $menu_item->icon ) : ?>
							style="display:none"
						<?php endif; ?>
					>
						<?php if ( $menu_item->icon ) : ?>
							<img src="<?php echo esc_url( $menu_item->icon_url ); ?>" alt="Icon">
						<?php endif; ?>
					</span>
					<a class="button-link minimog-menu-icon-select"
					   data-item-id="<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Select', 'minimog' ); ?></a>
					<a class="button-link button-link-delete minimog-menu-icon-remove"
						<?php if ( ! $menu_item->icon ) : ?>
							style="display:none"
						<?php endif; ?>
					><?php esc_html_e( 'Remove', 'minimog' ); ?></a>
				</label>
				<input type="hidden" class="minimog-menu-icon-input"
				       name="menu-item-icon[<?php echo esc_attr( $item_id ); ?>]"
				       value="<?php echo esc_attr( $menu_item->icon ); ?>"/>
			</p>
			<?php
		}

		public function update_nav_menu_item( $menu_id, $menu_item_db_id, $args ) {
			if ( isset( $_REQUEST['menu-item-icon'] ) && is_array( $_REQUEST['menu-item-icon'] ) ) {
				$custom_value = $_REQUEST['menu-item-icon'][ $menu_item_db_id ];
				update_post_meta( $menu_item_db_id, '_menu_item_icon', $custom_value );
			}
		}

		/**
		 * Setup icon for both backend + frontend
		 *
		 * @param $menu_item
		 *
		 * @return mixed
		 */
		public function setup_nav_menu_item( $menu_item ) {
			$menu_item->icon = get_post_meta( $menu_item->ID, '_menu_item_icon', true );

			if ( ! empty( $menu_item->icon ) ) {
				$attachment_info = Minimog_Image::get_attachment_info( $menu_item->icon );
				$attachment_ext  = wp_check_filetype( $attachment_info['src'] );

				$menu_item->icon_url  = $attachment_info['src'];
				$menu_item->icon_type = $attachment_ext['ext'];

				if ( 'svg' === $attachment_ext['ext'] ) {
					$svg_file_path       = get_attached_file( $menu_item->icon, true );
					$svg_file_content    = Minimog_Helper::get_file_contents( $svg_file_path );
					$menu_item->icon_svg = $svg_file_content;
				}
			}

			return $menu_item;
		}
	}

	Minimog_Extra_Nav_Menu_Items::instance()->initialize();
}
