<?php
defined( 'ABSPATH' ) || exit;

class Minimog_Logo {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		/**
		 * @see Minimog_Redux::OPTION_NAME
		 */
		add_action( 'update_option_minimog_options', [ $this, 'regenerate_logo_dimensions' ], 20, 3 );
	}

	public function render( $args = array() ) {
		$defaults = [
			// Used when get specify skin. Eg: light, dark
			'skin' => '',
		];
		$args     = wp_parse_args( $args, $defaults );

		$sticky_logo_skin = Minimog::setting( 'header_sticky_logo' );
		$header_logo_skin = Minimog_Global::instance()->get_header_skin();
		$display_logos    = [];

		if ( in_array( $args['skin'], [ 'dark', 'light' ] ) ) {
			$display_logos["{$args['skin']}"] = [];
		} else {
			if ( $sticky_logo_skin !== $header_logo_skin || is_page_template( 'templates/one-page-scroll.php' ) ) {
				$display_logos = array(
					'dark'  => [],
					'light' => [],
				);
			} else {
				$display_logos = 'dark' === $header_logo_skin ? array( 'dark' => [] ) : array( 'light' => [] );
			}
		}

		$logo_width   = intval( Minimog::setting( 'logo_width' ) );
		$retina_width = $logo_width * 2;
		$alt          = get_bloginfo( 'name', 'display' );
		$logos_html   = '';

		foreach ( $display_logos as $skin => $display_logo ) {
			$logo_setting = Minimog::setting( 'logo_' . $skin );

			if ( ! empty( $logo_setting['id'] ) ) {
				$logo_url = Minimog_Image::get_attachment_url_by_id( array(
					'id'   => $logo_setting['id'],
					'size' => "{$retina_width}x9999",
					'crop' => false,
				) );
			} elseif ( ! empty( $logo_setting['url'] ) ) {
				$logo_url = $logo_setting['url'];
			}

			if ( empty( $logo_url ) ) {
				continue;
			}

			$display_logo = [
				'src'           => $logo_url,
				'class'         =>  "logo {$skin}-logo",
				'alt'           => $alt,
				'width'         => $logo_width,
				'fetchpriority' => 'high',
			];

			$transient_key   = "minimog_logo_dimensions_{$display_logo['src']}";
			$logo_dimensions = get_transient( $transient_key );

			if ( false !== $logo_dimensions ) {
				$attachment_width  = $logo_dimensions['width'];
				$attachment_height = $logo_dimensions['height'];
			} else {
				$logo_dimensions = $this->save_dimensions( $display_logo['src'] );

				if ( ! empty( $logo_dimensions ) ) {
					$attachment_width  = $logo_dimensions['width'];
					$attachment_height = $logo_dimensions['height'];
				} else {
					$attachment_width  = $display_logo['width'];
					$attachment_height = 42;
				}
			}

			$display_logo['height'] = $logo_width * ( $attachment_height / $attachment_width );

			$logo_html  = Minimog_Image::build_img_tag( $display_logo );
			$logos_html .= Minimog_Image::build_lazy_img_tag( $logo_html, $attachment_width, $attachment_height );
		}
		?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo $logos_html; ?></a>
		<?php
	}

	public function save_dimensions( $logo_url ) {
		if ( empty( $logo_url ) ) {
			return false;
		}

		$transient_key = "minimog_logo_dimensions_{$logo_url}";
		$image_size    = Minimog_Image::get_image_size( $logo_url );

		if ( ! empty( $image_size ) ) {
			$dimensions = [
				'width'  => $image_size['width'],
				'height' => $image_size['height'],
			];

			set_transient( $transient_key, $dimensions, WEEK_IN_SECONDS );

			return $dimensions;
		}

		return false;
	}

	public function regenerate_logo_dimensions( $old_value, $value, $option ) {
		$settings   = [
			'logo_dark',
			'logo_light',
		];
		$logo_width = intval( Minimog::setting( 'logo_width' ) );

		foreach ( $settings as $setting ) {
			$logo     = Minimog::setting( $setting );
			$logo_url = '';

			if ( ! empty( $logo['id'] ) ) {
				$logo_url = Minimog_Image::get_attachment_url_by_id( array(
					'id'   => $logo['id'],
					'size' => "{$logo_width}x9999",
					'crop' => false,
				) );
			} elseif ( ! empty( $logo['url'] ) ) {
				$logo_url = $logo['url'];
			}

			$this->save_dimensions( $logo_url );
		}
	}

	/**
	 * Adds classes to the site branding.
	 *
	 * @param string $class
	 */
	public function output_wrap_class( $class = '' ) {
		$classes = array( 'branding' );

		if ( ! empty( $class ) ) {
			if ( ! is_array( $class ) ) {
				$class = preg_split( '#\s+#', $class );
			}
			$classes = array_merge( $classes, $class );
		} else {
			// Ensure that we always coerce class to being an array.
			$class = array();
		}

		$classes = apply_filters( 'minimog/branding/class', $classes, $class );

		echo 'class="' . esc_attr( join( ' ', $classes ) ) . '"';
	}
}

Minimog_Logo::instance()->initialize();
