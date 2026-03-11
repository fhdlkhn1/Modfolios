<?php
/**
 * Icon list on header
 *
 * @package Minimog
 * @since   1.0.0
 * @version 2.9.3
 */

defined( 'ABSPATH' ) || exit;

$info_list = $args['info_list'];

$styling = Minimog::setting( 'header_info_list_secondary_style' );

$wrapper_classes = 'header-info-list header-info-list-secondary';
if ( ! empty( $styling ) ) {
	$wrapper_classes .= ' header-info-list-secondary__style-' . $styling;
}
?>
<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
	<ul class="info-list">
		<?php
		foreach ( $info_list as $item ) {
			$url        = $item['url'] ?? '';
			$icon_class = $item['icon_class'] ?? '';
			$text       = $item['text'] ?? '';
			$sub_title  = $item['sub_title'] ?? '';
			$item_class = $item['item_class'] ?? '';
			$icon       = $item['icon'] ?? false;

			$link_attrs = [
				'class' => 'info-link',
			];
			$link_tag   = 'div';

			if ( ! empty( $url ) ) {
				$link_tag           = 'a';
				$link_attrs['href'] = $url;
			}

			$link_text = '';
			$link_icon = '';

			if ( ! empty( $sub_title ) ) {
				$link_text .= '<span class="info-sub-title">' . $sub_title . '</span>';
			}

			if ( ! empty( $text ) ) {
				$link_text .= '<span class="info-text">' . $text . '</span>';
			}

			$link_text = '<div class="item-info">' . $link_text . '</div>';

			if ( ! empty( $icon_class ) || ( ! empty( $icon['url'] ) || ! empty( $icon['id'] ) ) ) {
				$icon_classes = 'info-icon';

				if ( ! empty( $icon_class ) ) {
					$icon_classes .= ' ' . $icon_class;
				}

				$icon_html = '';
				if ( ! empty( $icon['id'] ) ) {
					$icon_html = Minimog_Image::get_attachment_by_id( [
						'id' => $icon['id'],
					] );
				} elseif ( ! empty( $icon['url'] ) ) {
					$icon_html = '<img src="' . esc_url( $icon['url'] ) . '" />';
				}

				$link_icon = '<span aria-hidden="true" class="' . esc_attr( $icon_classes ) . '">' . $icon_html . '</span>';
			}

			$item_classes = 'info-item';
			$item_classes .= ! empty ( $item_class ) ? ' ' . $item_class : '';
			?>
			<li class="<?php echo esc_attr( $item_classes ); ?>">
				<?php printf( '<%1$s %2$s>%3$s</%1$s>', $link_tag, Minimog_Helper::convert_array_html_attributes_to_string( $link_attrs ), $link_icon . $link_text ); ?>
			</li>
		<?php } ?>
	</ul>
</div>
