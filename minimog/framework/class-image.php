<?php
defined( 'ABSPATH' ) || exit;

class Minimog_Image {

	private static $site_url = null;

	/**
	 * Cache site url to improvement performance.
	 *
	 * @return null|string
	 */
	public static function get_site_url() {
		if ( null === self::$site_url ) {
			self::$site_url = get_site_url();
		}

		return self::$site_url;
	}

	public static function is_external_image( $image_url ) {
		$image_url_info = wp_parse_url( $image_url );
		$site_url_info  = wp_parse_url( self::get_site_url() );

		return ! empty( $image_url_info['host'] ) && $image_url_info['host'] !== $site_url_info['host'];
	}

	public static function get_attachment_info( $attachment_id ) {
		if ( empty( $attachment_id ) ) {
			return false;
		}

		$attachment = get_post( $attachment_id );

		$attachment_types = [
			'attachment',
			'vdl_remote_image', // Compatible with Woosa - vidaXL dropshipping for WooCommerce.
		];

		if ( ! $attachment instanceof WP_Post || ! in_array( $attachment->post_type, $attachment_types ) ) {
			return false;
		}

		$image_src = wp_get_attachment_image_src( $attachment_id, 'full', false );

		if ( empty( $image_src[0] ) ) {
			return false;
		}

		list ( $attachment_url, $width, $height ) = $image_src;

		$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

		if ( '' === $alt ) {
			$alt = $attachment->post_title;
		}

		return array(
			'alt'         => $alt,
			'caption'     => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'href'        => get_permalink( $attachment->ID ),
			'src'         => $attachment_url,
			'title'       => $attachment->post_title,
		);
	}

	/**
	 * Get post thumbnail in loop.
	 *
	 * @param array $args
	 *
	 * @return string HTML img tag.
	 */
	public static function get_the_post_thumbnail( $args = array() ) {
		if ( ! empty( $args['post_id'] ) ) {
			$args['id'] = get_post_thumbnail_id( $args['post_id'] );
		} else {
			$args['id'] = get_post_thumbnail_id( get_the_ID() );
		}

		$attachment = self::get_attachment_by_id( $args );

		return $attachment;
	}

	/**
	 * Print post thumbnail in loop.
	 *
	 * @param array $args
	 */
	public static function the_post_thumbnail( $args = array() ) {
		$image = self::get_the_post_thumbnail( $args );

		echo "{$image}";
	}

	/**
	 * Get post thumbnail url in loop.
	 *
	 * @param array $args
	 *
	 * @return string $attachment_url post thumbnail url
	 */
	public static function get_the_post_thumbnail_url( $args = array() ) {
		if ( isset( $args['post_id'] ) ) {
			$args['id'] = get_post_thumbnail_id( $args['post_id'] );
		} else {
			$args['id'] = get_post_thumbnail_id( get_the_ID() );
		}

		$attachment_url = self::get_attachment_url_by_id( $args );

		return $attachment_url;
	}

	/**
	 * Print post thumbnail url in loop.
	 *
	 * @param array $args
	 */
	public static function the_post_thumbnail_url( $args = array() ) {
		$url = self::get_the_post_thumbnail_url( $args );

		echo esc_url( $url );
	}

	/**
	 * @param $file
	 *
	 * @return mixed|string
	 * @see wp_get_attachment_url()
	 *
	 */
	public static function get_attachment_url_by_file( $file ) {
		global $pagenow;

		$file_url = wp_parse_url( $file );
		$site_url = wp_parse_url( self::get_site_url() );
		// If external image.
		if ( ! empty( $file_url['host'] ) && $file_url['host'] !== $site_url['host'] ) {
			return $file;
		}

		$url     = '';
		$uploads = wp_get_upload_dir();
		if ( $uploads && false === $uploads['error'] ) {
			// Check that the upload base exists in the file location.
			if ( 0 === strpos( $file, $uploads['basedir'] ) ) {
				// Replace file location with url location.
				$url = str_replace( $uploads['basedir'], $uploads['baseurl'], $file );
			} elseif ( false !== strpos( $file, 'wp-content/uploads' ) ) {
				// Get the directory name relative to the basedir (back compat for pre-2.7 uploads).
				$url = trailingslashit( $uploads['baseurl'] . '/' . _wp_get_attachment_relative_path( $file ) ) . wp_basename( $file );
			} else {
				// It's a newly-uploaded file, therefore $file is relative to the basedir.
				$url = $uploads['baseurl'] . "/$file";
			}
		}

		// On SSL front end, URLs should be HTTPS.
		if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $pagenow ) {
			$url = set_url_scheme( $url );
		}

		return $url;
	}

	public static function parse_attachment_size( $args ) {
		if ( 'custom' === $args['size'] ) {
			$width  = $args['width'];
			$height = $args['height'];

			if ( $width === '' ) {
				$width = 9999;
			}

			if ( $height === '' ) {
				$height = 9999;
			}

			return "{$width}x{$height}";
		}

		return $args['size'];
	}

	public static function get_attachment_by_id( $args = array() ) {
		$defaults = array(
			'id'         => '',
			'size'       => 'full',
			'width'      => '',
			'height'     => '',
			'alt'        => '',
			'crop'       => true,
			'class'      => '',
			'retina'     => true,
			'lazy_load'  => true,
			'lazy_class' => '', // Custom css class for m-image tag.
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['id'] ) ) {
			return false;
		}

		$attachment_id = intval( $args['id'] );
		$image_size    = self::parse_attachment_size( $args );

		$image_attributes = array(
			'src'    => '',
			'width'  => '',
			'height' => '',
		);

		// If size is wp_image_size like full, thumbnail then we don't need a fly image.
		if ( ! preg_match( '/(\d+)x(\d+)/', $image_size ) ) {
			$image_src = wp_get_attachment_image_src( $attachment_id, $image_size );
			if ( ! empty( $image_src ) ) {
				list ( $image_attributes['src'], $image_attributes['width'], $image_attributes['height'] ) = $image_src;
			}
		} else {
			$image_src = self::fly_get_attachment_image_src( $args, $attachment_id, $image_size );
			if ( ! empty( $image_src ) ) {
				list ( $image_attributes['src'], $image_attributes['width'], $image_attributes['height'] ) = $image_src;
			}
		}

		if ( empty( $image_attributes['src'] ) ) {
			return;
		}

		$image_attributes['alt'] = ! empty( $args['alt'] ) ? $args['alt'] : get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( empty( $image_attributes['alt'] ) ) {
			$image_attributes['alt'] = get_the_title( $attachment_id );
		}

		if ( ! empty( $args['lazy_load'] ) ) {
			$image_attributes['loading'] = 'lazy';
		}

		$img_srcset            = [];
		$retina_display_enable = ! empty( $args['retina'] ) && Minimog::setting( 'retina_display_enable' );
		if ( $retina_display_enable ) {
			$cropped_image_info = pathinfo( $image_attributes['src'] );
			// Check retina version exist.
			if ( self::check_retina_image_exists( $cropped_image_info ) ) {
				$retina_img_src = $cropped_image_info['dirname'] . '/' . $cropped_image_info['filename'] . '@2x.' . $cropped_image_info['extension'];
				$img_srcset[]   = "$retina_img_src @2x";
			}
		}

		$image_attributes['class'] = "attachment-$image_size size-$image_size";
		if ( ! empty( $args['class'] ) ) {
			$image_attributes['class'] .= ' ' . $args['class'];
		}

		if ( ! empty( $img_srcset ) ) {
			$image_attributes['srcset'] = implode( ', ', $img_srcset );
		}

		$image_html = self::build_img_tag( $image_attributes );

		return self::build_lazy_img_tag( $image_html, $image_attributes['width'], $image_attributes['height'], $args['lazy_class'] );
	}

	public static function fly_get_attachment_image_src( $args, $attachment_id, $image_size ) {
		// Get all data of attachment from single query.
		$attachment_cropped_info = Minimog_Attachment::instance()->get_cropped_info( $attachment_id );
		$attachment_cropped_info = is_array( $attachment_cropped_info ) && ! empty( $attachment_cropped_info ) ? $attachment_cropped_info : array();

		if ( empty( $attachment_cropped_info['file'] ) ) {
			$attachment = get_post( $attachment_id );

			if ( ! $attachment instanceof WP_Post ) {
				return false;
			}

			if ( 'attachment' === $attachment->post_type ) {
				/**
				 * Compatible with 3rd plugin
				 *
				 * #1 Support Leopard - WordPress offload media plugin.
				 * #2 Media Cloud
				 * #3 WP Offload Media Lite
				 *
				 * @package Media Cloud
				 * @package_uri https://wordpress.org/plugins/ilab-media-tools/
				 * @author  interfacelab
				 */
				if ( ( function_exists( 'leopard_offload_media_is_attachment_served_by_provider' ) && leopard_offload_media_is_attachment_served_by_provider( $attachment_id ) ) || defined( 'MEDIA_CLOUD_VERSION' ) || class_exists( 'Amazon_S3_And_CloudFront' ) ) {
					$image_src = wp_get_attachment_image_src( $attachment_id, 'full', false );
					$file      = $image_src[0];
				} else {
					$file = get_post_meta( $attachment->ID, '_wp_attached_file', true );
				}
			} else {
				// Support external images plugin.
				$image_src = wp_get_attachment_image_src( $attachment_id, 'full', false );
				$file      = ! empty( $image_src[0] ) ? $image_src[0] : false;
			}

			if ( empty( $file ) ) {
				return false;
			}

			$attachment_cropped_info['file'] = $file;
		}

		if ( isset( $attachment_cropped_info['sizes'][ $image_size ] ) ) {
			$current_cropped = $attachment_cropped_info['sizes'][ $image_size ];

			$image_src = [
				self::get_attachment_url_by_file( $current_cropped['src'] ),
				$current_cropped['width'],
				$current_cropped['height'],
			];
		} else {
			$url           = self::get_attachment_url_by_file( $attachment_cropped_info['file'] );
			$cropped_image = self::get_image_cropped_url( $url, $args );

			if ( $cropped_image[0] === '' ) {
				return false;
			}

			$cropped_image_w = isset( $cropped_image[1] ) ? $cropped_image[1] : '';
			$cropped_image_h = isset( $cropped_image[2] ) ? $cropped_image[2] : '';

			if ( '' === $cropped_image_w || '' === $cropped_image_h ) {
				$cropped_image_size = self::get_image_size( $cropped_image['0'] );

				if ( ! empty( $cropped_image_size ) ) {
					$cropped_image_w = $cropped_image_size['width'];
					$cropped_image_h = $cropped_image_size['height'];
				}
			}

			$file_url = $attachment_cropped_info['file'];

			if ( ! self::is_external_image( $attachment_cropped_info['file'] ) ) {
				$file_path_info = pathinfo( $attachment_cropped_info['file'] );
				$file_crop_url  = "{$file_path_info['dirname']}/{$file_path_info['filename']}-{$cropped_image_w}x{$cropped_image_h}.{$file_path_info['extension']}";
				$uploads        = wp_get_upload_dir();

				if ( $uploads && false === $uploads['error'] ) {
					if ( file_exists( "{$uploads['basedir']}/{$file_crop_url}" ) ) {
						// Callback to original file if specify dimensions file not exist.
						$file_url = $file_crop_url;
					}
				}
			}

			$cropped_data = [
				'src'    => $file_url,
				'width'  => $cropped_image_w,
				'height' => $cropped_image_h,
			];

			$attachment_cropped_info['sizes'][ $image_size ] = $cropped_data;

			Minimog_Attachment::instance()->update_cropped_info( $args['id'], $attachment_cropped_info );

			$image_src = [
				$cropped_image[0],
				$cropped_image_w,
				$cropped_image_h,
			];
		}

		/**
		 * When image cropped failed then it kept height is 9999.
		 * Then it make huge white gap on swiper in Safari.
		 * Force it same width to fix that.
		 */
		if ( 9999 === $image_src[2] ) {
			$image_src[2] = $image_src[1];
		}

		return $image_src;
	}

	public static function the_attachment_by_id( $args = array() ) {
		$attachment = self::get_attachment_by_id( $args );

		echo "{$attachment}";
	}

	public static function get_attachment_url_by_id( $args = array() ) {
		$id = $size = $width = $height = $crop = '';

		$defaults = array(
			'id'      => '',
			'size'    => 'full',
			'width'   => '',
			'height'  => '',
			'crop'    => true,
			'details' => false,
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( $id === '' ) {
			return '';
		}

		if ( $details === false ) {
			$url           = wp_get_attachment_image_url( $id, 'full' );
			$image_cropped = self::get_image_cropped_url( $url, $args );

			return $image_cropped[0];
		} else {
			$image_full = self::get_attachment_info( $id );
			$url        = $image_full['src'];

			$image_cropped = self::get_image_cropped_url( $url, $args );

			$full_details                  = $image_full;
			$full_details['cropped_image'] = $image_cropped[0];

			return $full_details;
		}
	}

	public static function the_attachment_url_by_id( $args = array() ) {
		$url = self::get_attachment_url_by_id( $args );

		echo esc_url( $url );
	}

	/**
	 * @param string $url  Original image url.
	 * @param array  $args Array attributes.
	 *
	 * @return array|bool|string
	 */
	public static function get_image_cropped_url( $url, $args = array() ) {
		extract( $args );
		if ( $url === false ) {
			return array( 0 => '' );
		}

		$path_info = pathinfo( $url );
		if ( $size === 'full' || ( isset( $path_info['extension'] ) && in_array( $path_info['extension'], [
					'svg',
					'gif',
				], true ) ) ) {
			return array( 0 => $url );
		}

		if ( $size !== 'custom' && ! preg_match( '/(\d+)x(\d+)/', $size ) ) {
			$attachment_url = wp_get_attachment_image_url( $args['id'], $size );

			if ( ! $attachment_url ) {
				return array( 0 => $url );
			} else {
				return array( 0 => $attachment_url );
			}
		}

		if ( $size !== 'custom' ) {
			$_sizes = explode( 'x', $size );
			$width  = $_sizes[0];
			$height = $_sizes[1];
		} else {
			if ( $width === '' ) {
				$width = 9999;
			}

			if ( $height === '' ) {
				$height = 9999;
			}
		}

		$width  = (int) $width;
		$height = (int) $height;

		if ( $width === 9999 || $height === 9999 ) {
			$crop = false;
		}

		if ( $width !== '' && $height !== '' && function_exists( 'aq_resize' ) ) {
			$crop_image = aq_resize( $url, $width, $height, $crop, false );

			if ( ! empty( $crop_image ) && is_array( $crop_image ) && ! empty( $crop_image[0] ) ) {
				return $crop_image;
			}
		}

		return array( 0 => $url );
	}

	public static function elementor_parse_image_size( $settings = null, $default = 'full', $image_size_key = 'thumbnail' ) {
		if ( empty( $settings ) ) {
			return $default;
		}

		if ( isset( $settings['thumbnail_default_size'] ) && '1' === $settings['thumbnail_default_size'] ) {
			return $default;
		}

		if ( isset( $settings["{$image_size_key}_size"] ) ) {
			if ( $settings["{$image_size_key}_size"] === 'custom' ) {
				$width  = $settings["{$image_size_key}_custom_dimension"]['width'];
				$height = $settings["{$image_size_key}_custom_dimension"]['height'];

				if ( $width === '' ) {
					$width = 9999;
				}

				if ( $height === '' ) {
					$height = 9999;
				}

				return "{$width}x{$height}";
			} else {
				return $settings["{$image_size_key}_size"];
			}
		}

		return $default;
	}

	/**
	 * @param array $args
	 *
	 * @return bool|string HTML img tag || false if errors.
	 * @var string  $image_key      Name if image control.
	 * @var array   $size_settings  Elementor settings or custom array or null to use $settings.
	 * @var string  $image_size_key Name if image size control. Default same name with image key.
	 * @var array   $attributes     An array attributes that add to img tag.
	 *
	 * @var array   $settings       Elementor settings or repeater item settings.
	 */
	public static function get_elementor_attachment( array $args ) {
		$defaults = array(
			'settings'       => [],
			'image_key'      => 'image',
			'size_settings'  => [],
			'image_size_key' => '',
			'attributes'     => [],
			'lazy_class'     => '',
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( empty( $settings ) ) {
			return '';
		}

		if ( empty( $settings["{$image_key}"] ) ) {
			return '';
		}

		$image = $settings["{$image_key}"];

		// Default same name with $image_key
		if ( empty( $image_size_key ) ) {
			$image_size_key = $image_key;
		}

		// If image has no both id & url.
		if ( empty( $image['url'] ) && empty( $image['id'] ) ) {
			return '';
		}

		// If image has id.
		if ( ! empty( $image['id'] ) ) {
			$attachment_args = array(
				'id'         => $image['id'],
				'lazy_class' => $lazy_class,
			);

			// If not override. then use from $settings.
			if ( empty( $size_settings ) ) {
				$size_settings = $settings;
			}

			// Check if image has custom size.
			// Usage: `{name}_size` and `{name}_custom_dimension`, default `image_size` and `image_custom_dimension`.
			if ( isset( $size_settings["{$image_size_key}_size"] ) ) {
				$image_size = $size_settings["{$image_size_key}_size"];

				// Get get image size.
				if ( 'custom' === $image_size ) {
					$width  = $size_settings["{$image_size_key}_custom_dimension"]['width'];
					$height = $size_settings["{$image_size_key}_custom_dimension"]['height'];

					if ( empty( $width ) ) {
						$width = 9999;

						$attachment_args['crop'] = false;
					}

					if ( empty( $height ) ) {
						$height = 9999;

						$attachment_args['crop'] = false;
					}

					$attachment_args['size'] = "{$width}x{$height}";

				} else {
					// WP Image Size like: full, thumbnail, large...
					$attachment_args['size'] = $image_size;
				}
			}

			$attachment = self::get_attachment_by_id( $attachment_args );
		} else {
			$attributes['src'] = $image['url'];

			$attachment = self::build_img_tag( $attributes );
		}

		return $attachment;
	}

	/**
	 * @param array $attributes
	 *
	 * @return string HTML img tag.
	 */
	public static function build_img_tag( $attributes = array() ) {
		if ( empty( $attributes['src'] ) ) {
			return '';
		}

		$attributes_str = '';

		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute => $value ) {
				if ( '' === $value ) {
					continue;
				}

				$attributes_str .= ' ' . $attribute . '="' . esc_attr( $value ) . '"';
			}
		}

		$image = '<img ' . $attributes_str . ' />';

		return $image;
	}

	public static function build_lazy_img_tag( $img_html, $width, $height, $extra_classes = '' ) {
		$lazy_height = '100';
		$lazy_width  = '100%';
		$classes     = 'minimog-lazy-image';

		if ( ! empty( $extra_classes ) ) {
			$classes .= ' ' . $extra_classes;
		}

		if ( isset( $width ) && '' !== $width ) {
			$lazy_width = $width . 'px';

			if ( isset( $height ) && '' !== $height ) {
				$lazy_height = Minimog_Helper::calculate_percentage( $height, $width );
			}
		}

		$image_style = "--lazy-image-width: {$lazy_width};";
		$image_style .= "--lazy-image-height: {$lazy_height}%;";

		return '<m-image class="' . $classes . '" style="' . $image_style . '" data-image-loading>' . $img_html . '</m-image>';
	}

	/**
	 * @param array $cropped_image_info Info of image
	 *
	 * @return bool Whether the image exists.
	 */
	public static function check_retina_image_exists( $cropped_image_info ) {
		$image_dir         = explode( 'wp-content/uploads/', $cropped_image_info['dirname'] );
		$sub_dir           = isset( $image_dir[1] ) ? $image_dir[1] . DIRECTORY_SEPARATOR : ''; // For eg: 2020/03
		$retina_image_name = $cropped_image_info['filename'] . '@2x.' . $cropped_image_info['extension'];

		$wp_upload = wp_upload_dir();
		$base_url  = $wp_upload['basedir'];

		return file_exists( $base_url . DIRECTORY_SEPARATOR . $sub_dir . $retina_image_name );
	}

	public static function get_elementor_image_ratio_height_percent( $settings = null, $image_size_key = 'thumbnail' ) {
		$height = 100;

		if ( empty( $settings ) ) {
			return "{$height}%";
		}

		if ( isset( $settings["{$image_size_key}_size"] ) ) {
			if ( $settings["{$image_size_key}_size"] === 'custom' ) {
				$ratio_w = absint( $settings["{$image_size_key}_custom_dimension"]['width'] );
				$ratio_h = absint( $settings["{$image_size_key}_custom_dimension"]['height'] );

				$height = $ratio_w > 0 ? ( $ratio_h / $ratio_w ) * 100 : 100;

				return "{$height}%";
			} else {
				return self::get_image_ratio_height_percent( $settings["{$image_size_key}_size"] );
			}
		}

		return "{$height}%";
	}

	public static function get_image_ratio_height_percent( $size = null ) {
		$all_image_sizes = self::get_all_image_sizes();

		$image_keys = array_keys( $all_image_sizes );

		$height = 100;

		if ( empty( $size ) ) {
			return "{$height}%";
		}

		if ( ! in_array( $size, $image_keys ) ) {
			return "{$height}%";
		}

		$ratio_w = $all_image_sizes[ $size ]['width'];
		$ratio_h = $all_image_sizes[ $size ]['height'];

		if ( $ratio_w > 0 && $ratio_h > 0 ) {
			$height = ( $ratio_h / $ratio_w ) * 100;
		}

		return "{$height}%";
	}

	public static function get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = [ 'thumbnail', 'medium', 'medium_large', 'large' ];

		$image_sizes = [];

		foreach ( $default_image_sizes as $size ) {
			$image_sizes[ $size ] = [
				'width'  => (int) get_option( $size . '_size_w' ),
				'height' => (int) get_option( $size . '_size_h' ),
				'crop'   => (bool) get_option( $size . '_crop' ),
			];
		}

		if ( $_wp_additional_image_sizes ) {
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
		}

		/** This filter is documented in wp-admin/includes/media.php */
		return apply_filters( 'image_size_names_choose', $image_sizes );
	}

	/**
	 * Get image size from image url
	 * This function instead of getimagesize().
	 * The original function raising php warning on SSL certificate.
	 *
	 * @param $image_src
	 *
	 * @return array|bool
	 */
	public static function get_image_size( $image_src ) {
		$image_size = false;
		$path_info  = pathinfo( $image_src );

		if ( $path_info['extension'] === 'svg' ) {
			$image_size = self::get_svg_dimension( $image_src );
		} else {
			$get_image_size = @getimagesize( $image_src );

			// Back compatible with SSL verify issue
			if ( empty( $get_image_size ) ) {
				$response = wp_remote_get( $image_src, [
					'sslverify' => false,
				] );
				$data     = wp_remote_retrieve_body( $response );

				$get_image_size = getimagesizefromstring( $data );
			}

			if ( ! empty( $get_image_size[0] ) && ! empty( $get_image_size[1] ) ) {
				$image_size = [
					'width'  => $get_image_size[0],
					'height' => $get_image_size[1],
				];
			}
		}

		return $image_size;
	}

	public static function get_svg_dimension( $svg_url ) {
		$svg_content = @file_get_contents( $svg_url );

		try {
			if ( $svg_content === false ) {
				throw new \Exception( 'Unable to load SVG file from URL.' );
			}

			$svg = simplexml_load_string( $svg_content );

			if ( $svg === false ) {
				throw new Exception( 'Unable to parse SVG content.' );
			}

			// Extract width and height attributes if they exist.
			$width  = (string) $svg['width'];
			$height = (string) $svg['height'];

			// Handle cases where width and height might be specified as percentages.
			if ( strpos( $width, '%' ) !== false || strpos( $height, '%' ) !== false ) {
				throw new Exception( 'Width and height are specified as percentages, which are not supported.' );
			}

			// If width and height are not set, use the viewBox attribute
			if ( empty( $width ) || empty( $height ) ) {
				$viewBox = (string) $svg['viewBox'];
				if ( ! empty( $viewBox ) ) {
					$viewBoxValues = explode( ' ', $viewBox );

					if ( count( $viewBoxValues ) === 4 ) {
						$width  = $viewBoxValues[2];
						$height = $viewBoxValues[3];
					} else {
						throw new Exception( 'Invalid viewBox attribute format.' );
					}
				} else {
					throw new Exception( 'SVG does not have width, height, or viewBox attributes.' );
				}
			}

			// Handle cases where width and height might be specified in different units.
			$width  = preg_replace( '/[^0-9.]/', '', $width );
			$height = preg_replace( '/[^0-9.]/', '', $height );

			return [
				'width'  => $width,
				'height' => $height,
			];
		} catch ( \Exception $e ) {
			return false;
		}
	}

	public static function get_lazy_image_src( $width = 1, $height = 1 ) {
		return 'data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%22http://www.w3.org/2000/svg%22%20viewBox%3D%220%200%20' . $width . '%20' . $height . '%22%3E%3C%2Fsvg%3E';
	}
}
