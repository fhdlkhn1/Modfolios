<?php

namespace Minimog\Woo;

defined( 'ABSPATH' ) || exit;

class Product_Thumb_Media {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		add_filter( 'attachment_fields_to_edit', [ $this, 'attachment_fields_to_edit' ], 10, 2 );
		add_filter( 'attachment_fields_to_save', [ $this, 'attachment_fields_to_save' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

		// Disable downsize on upload. Product 360 sprite is big image.
		add_filter( 'big_image_size_threshold', '__return_false' );
	}

	public function admin_scripts() {
		global $post, $pagenow;

		$get_page = sanitize_text_field( filter_input( INPUT_GET, 'page' ) );

		if ( ( $post && ( 'product' === get_post_type( $post->ID ) && ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) ) || ( 'admin.php' === $pagenow && $get_page && $get_page === 'minimog-woothumbs-bulk-edit' ) || ( $get_page && ( 'minimog-woothumbs-settings-account' === $get_page || 'minimog-woothumbs-settings' === $get_page ) ) || 'upload.php' === $pagenow || ( 'post.php' === $pagenow && 'attachment' === get_post_type( $post->ID ) ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'minimog-product-media-attach', MINIMOG_THEME_ASSETS_URI . '/admin/js/video-attachments.js', [ 'jquery' ], MINIMOG_THEME_VERSION, true );
		}
	}

	/**
	 * Add fields to the $form_fields array.
	 *
	 * @param array  $form_fields
	 * @param object $post
	 *
	 * @return array
	 */
	public function attachment_fields_to_edit( $form_fields, $post ) {
		if ( strpos( $post->post_mime_type, 'image/' ) !== 0 ) {
			return $form_fields;
		}

		$screen = get_current_screen();

		$form_fields['minimog_media_attach_title'] = array(
			'tr' => '<tr><td colspan="2">' . sprintf( '<strong style="font-size: 1.1em; line-height: 30px;">%s</strong>', esc_html__( 'Minimog Media Attach', 'minimog' ) ) . '</td></tr>',
		);

		$form_fields['minimog_media_attach_help'] = array(
			'tr' => '<tr><td colspan="2">' . esc_html__( 'Any changes are saved automatically.', 'minimog' ) . '</td></tr>',
		);

		if ( $screen instanceof \WP_Screen && 'post' === $screen->base && 'attachment' === $screen->post_type ) {
			unset( $form_fields['minimog_media_attach_help'] );
		}

		$attach_type      = get_post_meta( $post->ID, 'minimog_product_attachment_type', true );
		$attachment_types = [
			''      => esc_html__( 'None', 'minimog' ),
			'video' => esc_html__( 'Product Video', 'minimog' ),
			'360'   => esc_html__( 'Product 360', 'minimog' ),
		];

		ob_start(); ?>
		<select id="<?php echo 'attachments-' . $post->ID . '-minimog_product_attachment_type' ?>"
		        name="<?php echo 'attachments[' . $post->ID . '][minimog_product_attachment_type]'; ?>">
			<?php foreach ( $attachment_types as $attachment_type => $label ) : ?>
				<option
					value="<?php echo esc_attr( $attachment_type ) ?>" <?php selected( $attach_type, $attachment_type ); ?>><?php echo '' . $label; ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		$attachment_type_html = ob_get_clean();

		$form_fields['minimog_product_attachment_type'] = array(
			'label' => __( 'Media Attach', 'minimog' ),
			'input' => 'html',
			'html'  => $attachment_type_html,
			'value' => $attach_type,
		);

		$video_url = get_post_meta( $post->ID, 'minimog_product_video', true );
		ob_start();
		?>
		<input type="text" class="text"
		       id="<?php echo 'attachments-' . $post->ID . '-minimog_product_video' ?>"
		       name="<?php echo 'attachments[' . $post->ID . '][minimog_product_video]'; ?>"
		       value="<?php echo esc_attr( $video_url ); ?>"/>
		<a href="#" class="minimog-video-upload button-secondary"
		   data-image-id="<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Attach MP4', 'minimog' ); ?></a>
		<p class="description" style="width: 100%; padding-top: 4px;">
			<?php echo sprintf( __( 'Enter a <a href="%s" target="_blank">valid media URL</a>, or click "Attach MP4" to upload your own MP4 video into the WordPress media library.', 'minimog' ), esc_url( 'https://wordpress.org/support/article/embeds/#okay-so-what-sites-can-i-embed-from' ) ); ?>
		</p>
		<?php
		$video_html = ob_get_clean();

		$product_video_fields                          = [];
		$product_video_fields['minimog_product_video'] = array(
			'label' => __( 'Video URL', 'minimog' ),
			'input' => 'html',
			'html'  => $video_html,
			'value' => $video_url,
			//'show_in_edit'  => 'video' === $attach_type,
			//'show_in_modal' => 'video' === $attach_type,
		);
		$this->form_field_tr( $form_fields, $post, $product_video_fields, $attach_type, 'video' );

		// Product 360 settings.
		$source_sprite    = get_post_meta( $post->ID, 'minimog_360_source_sprite', true );
		$sprite_image_url = \Minimog_Image::get_attachment_url_by_id( [
			'id'   => $source_sprite,
			'size' => '150x150',
		] );

		ob_start();

		$media_wrap_class = 'minimog-360-sprite-image';
		$sprite_style     = '';

		if ( ! empty( $sprite_image_url ) ) {
			$sprite_style     = ' style="background-image: url(' . $sprite_image_url . ')"';
			$media_wrap_class .= ' minimog-360-sprite-has-image';
		}

		echo '<input type="hidden" class="text" id="attachments-' . $post->ID . '-minimog_360_source_sprite" name="attachments[' . $post->ID . '][minimog_360_source_sprite]" value="' . esc_attr( $source_sprite ) . '" />';
		echo '<div class="' . $media_wrap_class . '" ' . $sprite_style . '></div>
			<a href="#" class="minimog-product-360-sprite-upload button-secondary" data-image-id="' . esc_attr( $post->ID ) . '">' . esc_html__( 'Upload Image', 'minimog' ) . '</a>';

		if ( ! empty( $sprite_image_url ) ) {
			echo '<a href="#" class="minimog-product-360-sprite-clear button-secondary button-link-delete" data-image-id="' . esc_attr( $post->ID ) . '">' . esc_html__( 'Delete', 'minimog' ) . '</a>';
		}

		$sprite_image_field_html = ob_get_clean();

		$product_360_fields = [];

		$product_360_fields['minimog_360_source_sprite'] = array(
			'label' => __( 'Sprite Image', 'minimog' ),
			'input' => 'html',
			'html'  => $sprite_image_field_html,
			'value' => $source_sprite,
			//'show_in_edit'  => '360' === $attach_type,
			//'show_in_modal' => '360' === $attach_type,
		);

		$product_360_fields['minimog_360_total_frames'] = array(
			'label' => __( 'Total Frames', 'minimog' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, 'minimog_360_total_frames', true ),
			'helps' => 'Set the total number of frames to show. The 6x6 sprite might contain 36 images, but it only has 34 frames, hence we set it to 34 here.',
			//'show_in_edit'  => '360' === $attach_type,
			//'show_in_modal' => '360' === $attach_type,
		);

		$product_360_fields['minimog_360_total_frames_per_row'] = array(
			'label' => __( 'Frames per row', 'minimog' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, 'minimog_360_total_frames_per_row', true ),
			'helps' => 'The 6x6 sprite sheet contains 6 frames in one row.',
			//'show_in_edit'  => '360' === $attach_type,
			//'show_in_modal' => '360' === $attach_type,
		);

		$this->form_field_tr( $form_fields, $post, $product_360_fields, $attach_type, '360' );

		return $form_fields;
	}

	/**
	 * @param $form_fields
	 * @param $post
	 * @param $fields
	 * @param $attached_type
	 *
	 * @return void
	 *
	 * @see get_compat_media_markup()
	 */
	public function form_field_tr( &$form_fields, $post, $fields, $selected_attached_type, $attached_type ) {
		$attachment_id = $post->ID;
		$user_can_edit = current_user_can( 'edit_post', $attachment_id );

		foreach ( $fields as $id => $field ) {
			$tr_css_class = 'compat-field-' . $id;
			$tr_css_class .= ' compat-field__product-attached-media compat-field__product-attached-' . $attached_type;

			$name    = "attachments[$attachment_id][$id]";
			$id_attr = "attachments-$attachment_id-$id";

			$readonly      = ! $user_can_edit && ! empty( $field['taxonomy'] ) ? " readonly='readonly' " : '';
			$required      = ! empty( $field['required'] ) ? ' ' . wp_required_field_indicator() : '';
			$required_attr = ! empty( $field['required'] ) ? ' required' : '';

			$inline_style = '';
			if ( $selected_attached_type !== $attached_type ) {
				$inline_style = 'style="display: none"';
			}

			ob_start();
			?>
			<tr class="<?php echo esc_attr( $tr_css_class ); ?>"<?php echo $inline_style ?>>
				<th scope="row" class="label">
					<label for="<?php echo $id_attr; ?>"><span class="alignleft"><?php echo esc_html( $field['label'] ); ?></span><?php echo $required; ?>
						<br class="clear"></label>
				</th>
				<td class="field">
					<?php
					switch ( $field['input'] ) :
						case 'html':
							echo $field['html'];
							break;
						case 'text':
							?>
							<input type="text" class="text" id="<?php echo $id_attr; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr( $field['value'] ); ?>"<?php echo $readonly . $required_attr ?>/>
							<?php
							break;
					endswitch;
					?>
					<?php if ( ! empty( $field['helps'] ) ) : ?>
						<p class="description help"><?php echo implode( '</p>\n<p class="help">', array_unique( (array) $field['helps'] ) ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<?php
			$tr_html = ob_get_clean();

			$form_fields[ $id ] = array(
				'tr' => $tr_html,
			);
		}
	}

	/**
	 * Save attachment fields.
	 *
	 * @param array $post
	 * @param array $form_data
	 *
	 * @return array
	 */
	public function attachment_fields_to_save( $post, $form_data ) {
		$this->save_field_meta_data( $post['ID'], $form_data, 'minimog_product_attachment_type' );

		// Product video.
		$this->save_field_meta_data( $post['ID'], $form_data, 'minimog_product_video' );

		// Product 360.
		$this->save_field_meta_data( $post['ID'], $form_data, 'minimog_360_source_sprite' );
		$this->save_field_meta_data( $post['ID'], $form_data, 'minimog_360_total_frames' );
		$this->save_field_meta_data( $post['ID'], $form_data, 'minimog_360_total_frames_per_row' );

		return $post;
	}

	public function save_field_meta_data( $attachment_id, $data, $key ) {
		if ( isset( $data[ $key ] ) && '' !== $data[ $key ] ) {
			update_post_meta( $attachment_id, $key, sanitize_text_field( $data[ $key ] ) );
		} else {
			delete_post_meta( $attachment_id, $key );
		}
	}
}

Product_Thumb_Media::instance()->initialize();
