<?php
/**
 * WCFM Products View - Custom Override for Modfolios
 * Custom table implementation matching Figma design exactly
 */

// Include helper functions
require_once get_template_directory() . '/wcfm/wcfm-helpers.php';

global $WCFM, $wp_query;

$wcfm_is_allow_manage_products = apply_filters( 'wcfm_is_allow_manage_products', true );
if( !$wcfm_is_allow_manage_products ) {
	wcfm_restriction_message_show( "Products" );
	return;
}

// Get current user info
$current_user = wp_get_current_user();
$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
$is_admin = current_user_can('administrator') || current_user_can('shop_manager');
$is_vendor = wcfm_is_vendor();
if( !$is_vendor && !$is_admin ) $current_user_id = 0;

// Get membership info
$membership_type = 'Basic Plan';

// Get last login
$last_login = get_user_meta($current_user_id, 'last_login', true);
$last_login_display = $last_login ? date_i18n('g:i a (F j, Y)', strtotime($last_login)) : date_i18n('g:i a (F j, Y)');

// Current tab/status
$product_status = ! empty( $_GET['product_status'] ) ? sanitize_text_field( $_GET['product_status'] ) : 'any';

// Map status to post_status
$status_map = array(
    'any'      => array('publish', 'draft', 'pending', 'private'),
    'publish'  => array('publish'),
    'archived' => array('draft', 'private'),
    'pending'  => array('pending')
);

$query_statuses = isset($status_map[$product_status]) ? $status_map[$product_status] : array('publish', 'draft', 'pending', 'private');

// Custom labels for display
$custom_menu_labels = array(
    'any'      => __( 'All', 'wc-frontend-manager'),
    'publish'  => __( 'Active', 'wc-frontend-manager'),
    'archived' => __( 'In-Active', 'wc-frontend-manager'),
    'pending'  => __( 'In-Review', 'wc-frontend-manager')
);

// Count products for tabs (exclude subscription products)
$count_products = array();
$count_args_base = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => array( 'subscription', 'variable-subscription' ),
            'operator' => 'NOT IN',
        ),
    ),
);

if ($is_vendor) {
    $count_args_base['author'] = $current_user_id;
}

foreach ($status_map as $status_key => $statuses) {
    $count_args = $count_args_base;
    $count_args['post_status'] = $statuses;
    $count_query = new WP_Query($count_args);
    $count_products[$status_key] = $count_query->found_posts;
}

// Pagination
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$posts_per_page = 10;

// Query products (exclude subscription products)
$args = array(
    'post_type' => 'product',
    'post_status' => $query_statuses,
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => array( 'subscription', 'variable-subscription' ),
            'operator' => 'NOT IN',
        ),
    ),
);

if ($is_vendor) {
    $args['author'] = $current_user_id;
}

$products_query = new WP_Query($args);
$products = $products_query->posts;
$total_products = $products_query->found_posts;
$total_pages = ceil($total_products / $posts_per_page);

// Get avatar
$avatar_url = get_avatar_url($current_user_id, array('size' => 60));

// Check if we're on In-Review tab (for admin approval column)
$show_approval_column = $is_admin && $product_status === 'pending';
?>

<div class="collapse wcfm-collapse modfolio-products-page" id="wcfm_products_listing">

	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>

		<?php
		// Use the reusable header function
		$breadcrumb_items = array(
			array( 'label' => __('Portfolio', 'wc-frontend-manager'), 'url' => '' ),
			array( 'label' => isset($custom_menu_labels[$product_status]) ? $custom_menu_labels[$product_status] : 'All', 'url' => '' )
		);
		modfolio_wcfm_render_header( '', $breadcrumb_items );
		?>

		<!-- Tabs and Actions Bar -->
		<div class="modfolio-tabs-bar">
			<div class="tabs-left">
				<ul class="modfolio-product-tabs">
					<?php
					$display_menus = array('any', 'publish', 'archived', 'pending');
					foreach( $display_menus as $menu_key ) :
						$menu_label = isset($custom_menu_labels[$menu_key]) ? $custom_menu_labels[$menu_key] : ucfirst($menu_key);
					?>
						<li>
							<a href="<?php echo esc_url(add_query_arg('product_status', $menu_key, get_wcfm_products_url())); ?>"
							   class="tab-btn <?php echo ( $menu_key == $product_status ) ? 'active' : ''; ?>">
								<?php echo esc_html( $menu_label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="tabs-right">
				<?php if( $has_new = apply_filters( 'wcfm_add_new_product_sub_menu', true ) ) : ?>
					<a id="add_new_product_dashboard" class="modfolio-create-btn" href="<?php echo esc_url(get_wcfm_edit_product_url()); ?>">
						<span class="btn-icon">+</span>
						<span class="btn-text"><?php _e( 'Create New', 'wc-frontend-manager'); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="wcfm-clearfix"></div>

		<!-- Custom Products Table -->
		<div class="modfolio-products-container">
			<?php if (empty($products)) : ?>
				<div class="modfolio-no-products">
					<p><?php _e('No products found.', 'wc-frontend-manager'); ?></p>
				</div>
			<?php else : ?>
				<div class="modfolio-products-list">
					<?php foreach ($products as $product_post) :
						$product_id = $product_post->ID;
						$product = wc_get_product($product_id);
						if (!$product) continue;

						// Get product data
						$product_name = $product->get_name();
						$product_image = $product->get_image('thumbnail', array('class' => 'product-thumb'));
						$product_price = $product->get_price();
						$product_status_raw = $product_post->post_status;

						// Get license package
						$license_package = get_post_meta($product_id, '_license_package', true);
						$license_display = '';
						if ($license_package === 'standard') {
							$license_display = __('Individual / Commercial', 'wc-frontend-manager');
						} elseif ($license_package === 'exclusive') {
							$license_display = __('Exclusive Rights', 'wc-frontend-manager');
						}

						// Get categories
						$categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
						$category_display = !empty($categories) ? implode(', ', $categories) : '';

						// Get sales count
						$sales_count = get_post_meta($product_id, 'total_sales', true);
						$sales_count = $sales_count ? intval($sales_count) : 0;

						// Get rejection reason (if rejected)
						$rejection_reason = get_post_meta($product_id, '_rejection_reason', true);

						// Determine status label and class
						$status_label = '';
						$status_class = '';
						switch ($product_status_raw) {
							case 'publish':
								$status_label = __('Active', 'wc-frontend-manager');
								$status_class = 'status-active';
								break;
							case 'draft':
							case 'private':
								$status_label = __('In-Active', 'wc-frontend-manager');
								$status_class = 'status-inactive';
								break;
							case 'pending':
								$status_label = __('In-Review', 'wc-frontend-manager');
								$status_class = 'status-pending';
								break;
							default:
								$status_label = ucfirst($product_status_raw);
								$status_class = 'status-default';
						}

						// URLs
						$view_url = get_permalink($product_id);
						$edit_url = get_wcfm_edit_product_url($product_id);
					?>
						<div class="product-row" data-product-id="<?php echo esc_attr($product_id); ?>">
							<!-- Product Image -->
							<div class="product-col product-col-image">
								<?php echo $product_image; ?>
							</div>

							<!-- Product Info -->
							<div class="product-col product-col-info">
								<a href="<?php echo esc_url($edit_url); ?>" class="product-title"><?php echo esc_html($product_name); ?></a>
								<?php if ($license_display) : ?>
									<span class="product-license"><?php echo esc_html($license_display); ?></span>
								<?php endif; ?>
								<span class="product-trending"><?php _e('Trending', 'wc-frontend-manager'); ?></span>
								<?php if ($rejection_reason && $product_status_raw === 'pending') : ?>
									<span class="product-rejection-reason"><?php echo esc_html($rejection_reason); ?></span>
								<?php endif; ?>
							</div>

							<!-- Price -->
							<div class="product-col product-col-price">
								<span class="price-value">$<?php echo esc_html(number_format((float)$product_price, 0)); ?></span>
							</div>

							<!-- Sales -->
							<div class="product-col product-col-sales">
								<span class="sales-count"><?php echo esc_html($sales_count); ?></span>
								<span class="sales-label"><?php _e('Sales', 'wc-frontend-manager'); ?></span>
							</div>

							<!-- Status -->
							<div class="product-col product-col-status">
								<span class="status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
							</div>

							<!-- Actions -->
							<div class="product-col product-col-actions">
								<a href="<?php echo esc_url($view_url); ?>" class="action-btn action-view" target="_blank">
									<span class="action-icon">&#128065;</span>
									<span class="action-text"><?php _e('View', 'wc-frontend-manager'); ?></span>
								</a>
								<a href="<?php echo esc_url($edit_url); ?>" class="action-btn action-edit">
									<span class="action-icon">&#9998;</span>
									<span class="action-text"><?php _e('Edit', 'wc-frontend-manager'); ?></span>
								</a>
								<?php if ($product_status_raw === 'publish') : ?>
								<a href="#" class="action-btn action-deactivate" data-product-id="<?php echo esc_attr($product_id); ?>">
									<span class="action-icon">&#8856;</span>
									<span class="action-text"><?php _e('Deactivate', 'wc-frontend-manager'); ?></span>
								</a>
							<?php elseif ($product_status_raw === 'draft' || $product_status_raw === 'private') : ?>
								<a href="#" class="action-btn action-activate" data-product-id="<?php echo esc_attr($product_id); ?>">
									<span class="action-icon">&#10003;</span>
									<span class="action-text"><?php _e('Activate', 'wc-frontend-manager'); ?></span>
								</a>
							<?php endif; ?>
								<?php if ($show_approval_column) : ?>
									<a href="#" class="action-btn action-approve-reject" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-name="<?php echo esc_attr($product_name); ?>">
										<span class="action-icon">&#8801;</span>
										<span class="action-text"><?php _e('Approve/Reject', 'wc-frontend-manager'); ?></span>
									</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Pagination -->
				<?php if ($total_pages > 1) : ?>
					<div class="modfolio-pagination">
						<?php
						$base_url = add_query_arg('product_status', $product_status, get_wcfm_products_url());
						for ($i = 1; $i <= $total_pages; $i++) :
							$page_url = add_query_arg('paged', $i, $base_url);
							$is_current = ($i == $paged);
						?>
							<a href="<?php echo esc_url($page_url); ?>" class="page-btn <?php echo $is_current ? 'active' : ''; ?>">
								<?php echo esc_html($i); ?>
							</a>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

	</div>
</div>

<!-- Approval/Rejection Modal -->
<?php if ($is_admin) : ?>
<div class="modfolio-modal-overlay" id="approval-modal" style="display: none;">
	<div class="modfolio-modal">
		<div class="modal-content approval-content" id="approval-step-1">
			<div class="modal-icon">
				<!-- <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Crect x='10' y='20' width='35' height='50' rx='5' fill='%23f5f5f5' stroke='%23333' stroke-width='2'/%3E%3Ccircle cx='27' cy='35' r='8' fill='%2300c4aa'/%3E%3Cpath d='M23 35 l3 3 l6 -6' stroke='white' stroke-width='2' fill='none'/%3E%3Crect x='55' y='20' width='35' height='50' rx='5' fill='%23f5f5f5' stroke='%23333' stroke-width='2'/%3E%3Cpath d='M68 32 l8 8 M76 32 l-8 8' stroke='%23e74c3c' stroke-width='2'/%3E%3Cpath d='M20 5 Q27 -5 34 5' stroke='%23333' stroke-width='2' fill='none'/%3E%3Ccircle cx='27' cy='8' r='4' fill='%23ffcc00'/%3E%3Cpath d='M66 5 Q73 -5 80 5' stroke='%23333' stroke-width='2' fill='none'/%3E%3Ccircle cx='73' cy='8' r='4' fill='%23ffcc00'/%3E%3C/svg%3E" alt="Approve/Reject" width="120"> -->
                <svg width="92" height="92" viewBox="0 0 92 92" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.0469 34.0996H29.9491V90.0015H22.0469V34.0996Z" fill="#D46B5C"/>
                    <path d="M22.0469 48.5726H29.9491V41.4181H25.6388C23.655 41.4181 22.0469 39.8099 22.0469 37.8262V48.5726Z" fill="#B55348"/>
                    <path d="M62.0508 34.0996H69.953V90.0015H62.0508V34.0996Z" fill="#D46B5C"/>
                    <path d="M69.953 48.5726H62.0508V41.4181H66.361C68.3448 41.4181 69.953 39.8099 69.953 37.8262V48.5726Z" fill="#B55348"/>
                    <path d="M35.0008 56.2363H32.9652C35.0813 56.2363 36.7968 54.5209 36.7968 52.4049C36.7968 50.2888 35.0813 48.5733 32.9652 48.5733H25.6407C23.6569 48.5733 22.0488 46.9651 22.0488 44.9814V43.9844H20.2503C18.0856 43.9844 15.9711 44.6363 14.1822 45.8553L4.62947 52.365H2V75.3899H4.62965L7.88586 77.1963C10.2849 78.5271 12.9832 79.2255 15.7267 79.2255H30.411C32.5271 79.2255 34.2426 77.51 34.2426 75.3938C34.2426 73.2777 32.5271 71.5624 30.411 71.5624H32.9654C35.0815 71.5624 36.797 69.8469 36.797 67.7308C36.797 65.6147 35.0815 63.8992 32.9654 63.8992H35.001C37.1171 63.8992 38.8326 62.1837 38.8326 60.0676C38.8322 57.9518 37.117 56.2363 35.0008 56.2363ZM87.3711 52.365L77.8184 45.8553C76.0295 44.6363 73.915 43.9844 71.7503 43.9844H69.952V44.9814C69.952 46.9651 68.3438 48.5733 66.36 48.5733H59.0356C56.9194 48.5733 55.204 50.2888 55.204 52.4049C55.204 54.521 56.9194 56.2363 59.0356 56.2363H56.9999C54.8838 56.2363 53.1683 57.9518 53.1683 60.0679C53.1683 62.1841 54.8838 63.8996 56.9999 63.8996H59.0356C56.9194 63.8996 55.204 65.615 55.204 67.7312C55.204 69.8473 56.9194 71.5628 59.0356 71.5628H61.5899C59.4738 71.5628 57.7583 73.2782 57.7583 75.3942C57.7583 77.5102 59.4738 79.2258 61.5899 79.2258H76.2742C79.0177 79.2258 81.716 78.5275 84.1151 77.1966L87.3713 75.3903H90.0008V52.3654H87.3711V52.365Z" fill="#FDE0BE"/>
                    <path d="M8.80253 49.5215L4.62947 52.3652H2V75.3901H4.62965L7.88586 77.1965C10.2849 78.5273 12.9832 79.2257 15.7267 79.2257H30.411C32.5271 79.2257 34.2426 77.5102 34.2426 75.3941C34.2426 74.4674 33.9135 73.6176 33.366 72.9549C22.0998 69.1947 13.0731 60.5439 8.80253 49.5215ZM87.3711 52.3652L83.198 49.5215C78.9277 60.5438 69.9008 69.1945 58.6349 72.9549C58.0671 73.6408 57.757 74.5036 57.7583 75.3941C57.7583 77.5102 59.4738 79.2257 61.5899 79.2257H76.2742C79.0177 79.2257 81.716 78.5273 84.1151 77.1965L87.3713 75.3901H90.0008V52.3652H87.3711Z" fill="#F7B073"/>
                    <path d="M36.6821 34.0988H15.3151C12.3515 34.0988 9.94922 31.6965 9.94922 28.7329V7.3659C9.94922 4.40231 12.3515 2 15.3151 2H36.6821C39.6457 2 42.048 4.40231 42.048 7.3659V28.7329C42.048 31.6963 39.6457 34.0988 36.6821 34.0988Z" fill="#0CCAAE"/>
                    <path d="M76.6821 34.0988H55.3151C52.3515 34.0988 49.9492 31.6965 49.9492 28.7329V7.3659C49.9492 4.40231 52.3515 2 55.3151 2H76.6821C79.6457 2 82.048 4.40231 82.048 7.3659V28.7329C82.048 31.6963 79.6455 34.0988 76.6821 34.0988Z" fill="#696969"/>
                    <path d="M29.951 34.0977V48.5722M22.0488 43.9833V34.0977M62.0498 34.0977V48.5722M69.952 43.9833V34.0977M14.1822 45.8542L4.62947 52.3639H2V75.3888H4.62965L7.88586 77.1951C10.2849 78.526 12.9832 79.2243 15.7267 79.2243H30.411C32.5271 79.2243 34.2426 77.5089 34.2426 75.3927C34.2426 73.2766 32.5271 71.5611 30.411 71.5611H27.6737H32.9654C35.0815 71.5611 36.797 69.8456 36.797 67.7295C36.797 65.6134 35.0815 63.8979 32.9654 63.8979H27.6737H35.001C37.1171 63.8979 38.8326 62.1824 38.8326 60.0663C38.8326 57.9502 37.1171 56.2347 35.001 56.2347H27.6737H32.9654C35.0815 56.2347 36.797 54.5192 36.797 52.4031C36.797 50.2869 35.0815 48.5715 32.9654 48.5715H25.6409C23.6571 48.5715 22.049 46.9633 22.049 44.9795V43.9825H20.2505C18.0857 43.9831 15.9712 44.6353 14.1822 45.8542ZM77.8185 45.8542L87.3713 52.3639H90.0008V75.3888H87.3711L84.1149 77.1951C81.7159 78.526 79.0175 79.2243 76.2741 79.2243H61.5897C59.4736 79.2243 57.7581 77.5089 57.7581 75.3927C57.7581 73.2766 59.4736 71.5611 61.5897 71.5611H64.3271H59.0354C56.9193 71.5611 55.2038 69.8456 55.2038 67.7295C55.2038 65.6134 56.9193 63.8979 59.0354 63.8979H64.3271H56.9998C54.8836 63.8979 53.1681 62.1824 53.1681 60.0663C53.1681 57.9502 54.8836 56.2347 56.9998 56.2347H64.3271H59.0354C56.9193 56.2347 55.2038 54.5192 55.2038 52.4031C55.2038 50.2869 56.9193 48.5715 59.0354 48.5715H66.3598C68.3436 48.5715 69.9518 46.9633 69.9518 44.9795V43.9825H71.7503C73.915 43.9831 76.0295 44.6353 77.8185 45.8542Z" stroke="#052A75" stroke-width="4" stroke-miterlimit="10" stroke-linejoin="round"/>
                    <path d="M22.0469 79.2246V90.0002H29.9491V79.2246M62.0479 79.2246V90.0002H69.95V79.2246" stroke="#052A75" stroke-width="4" stroke-miterlimit="10" stroke-linejoin="round"/>
                    <path d="M19.5938 16.9866L24.5731 21.966L32.4061 14.1328" stroke="white" stroke-width="4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M36.6821 34.0988H15.3151C12.3515 34.0988 9.94922 31.6965 9.94922 28.7329V7.3659C9.94922 4.40231 12.3515 2 15.3151 2H36.6821C39.6457 2 42.048 4.40231 42.048 7.3659V28.7329C42.048 31.6963 39.6457 34.0988 36.6821 34.0988Z" stroke="#052A75" stroke-width="4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M71.1977 12.8516L60.8008 23.2485M71.1977 23.2485L60.8008 12.8516" stroke="white" stroke-width="4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M76.6821 34.0988H55.3151C52.3515 34.0988 49.9492 31.6965 49.9492 28.7329V7.3659C49.9492 4.40231 52.3515 2 55.3151 2H76.6821C79.6457 2 82.048 4.40231 82.048 7.3659V28.7329C82.048 31.6963 79.6455 34.0988 76.6821 34.0988Z" stroke="#052A75" stroke-width="4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

            </div>
			<p class="modal-text"><?php _e('Select Below to', 'wc-frontend-manager'); ?><br><strong><?php _e('Approve or Reject', 'wc-frontend-manager'); ?></strong></p>
			<div class="modal-actions">
				<button type="button" class="modal-btn btn-reject" id="show-reject-form"><?php _e('Reject', 'wc-frontend-manager'); ?></button>
				<button type="button" class="modal-btn btn-approve" id="approve-product"><?php _e('Approve', 'wc-frontend-manager'); ?></button>
			</div>
		</div>

		<div class="modal-content rejection-content" id="approval-step-2" style="display: none;">
			<div class="modal-icon">
				<!-- <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Crect x='10' y='20' width='35' height='50' rx='5' fill='%23f5f5f5' stroke='%23333' stroke-width='2'/%3E%3Ccircle cx='27' cy='35' r='8' fill='%2300c4aa'/%3E%3Cpath d='M23 35 l3 3 l6 -6' stroke='white' stroke-width='2' fill='none'/%3E%3Crect x='55' y='20' width='35' height='50' rx='5' fill='%23f5f5f5' stroke='%23333' stroke-width='2'/%3E%3Cpath d='M68 32 l8 8 M76 32 l-8 8' stroke='%23e74c3c' stroke-width='2'/%3E%3Cpath d='M20 5 Q27 -5 34 5' stroke='%23333' stroke-width='2' fill='none'/%3E%3Ccircle cx='27' cy='8' r='4' fill='%23ffcc00'/%3E%3Cpath d='M66 5 Q73 -5 80 5' stroke='%23333' stroke-width='2' fill='none'/%3E%3Ccircle cx='73' cy='8' r='4' fill='%23ffcc00'/%3E%3C/svg%3E" alt="Approve/Reject" width="120"> -->
                <svg width="92" height="92" viewBox="0 0 92 92" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.0469 34.0996H29.9491V90.0015H22.0469V34.0996Z" fill="#D46B5C"/>
                    <path d="M22.0469 48.5726H29.9491V41.4181H25.6388C23.655 41.4181 22.0469 39.8099 22.0469 37.8262V48.5726Z" fill="#B55348"/>
                    <path d="M62.0508 34.0996H69.953V90.0015H62.0508V34.0996Z" fill="#D46B5C"/>
                    <path d="M69.953 48.5726H62.0508V41.4181H66.361C68.3448 41.4181 69.953 39.8099 69.953 37.8262V48.5726Z" fill="#B55348"/>
                    <path d="M35.0008 56.2363H32.9652C35.0813 56.2363 36.7968 54.5209 36.7968 52.4049C36.7968 50.2888 35.0813 48.5733 32.9652 48.5733H25.6407C23.6569 48.5733 22.0488 46.9651 22.0488 44.9814V43.9844H20.2503C18.0856 43.9844 15.9711 44.6363 14.1822 45.8553L4.62947 52.365H2V75.3899H4.62965L7.88586 77.1963C10.2849 78.5271 12.9832 79.2255 15.7267 79.2255H30.411C32.5271 79.2255 34.2426 77.51 34.2426 75.3938C34.2426 73.2777 32.5271 71.5624 30.411 71.5624H32.9654C35.0815 71.5624 36.797 69.8469 36.797 67.7308C36.797 65.6147 35.0815 63.8992 32.9654 63.8992H35.001C37.1171 63.8992 38.8326 62.1837 38.8326 60.0676C38.8322 57.9518 37.117 56.2363 35.0008 56.2363ZM87.3711 52.365L77.8184 45.8553C76.0295 44.6363 73.915 43.9844 71.7503 43.9844H69.952V44.9814C69.952 46.9651 68.3438 48.5733 66.36 48.5733H59.0356C56.9194 48.5733 55.204 50.2888 55.204 52.4049C55.204 54.521 56.9194 56.2363 59.0356 56.2363H56.9999C54.8838 56.2363 53.1683 57.9518 53.1683 60.0679C53.1683 62.1841 54.8838 63.8996 56.9999 63.8996H59.0356C56.9194 63.8996 55.204 65.615 55.204 67.7312C55.204 69.8473 56.9194 71.5628 59.0356 71.5628H61.5899C59.4738 71.5628 57.7583 73.2782 57.7583 75.3942C57.7583 77.5102 59.4738 79.2258 61.5899 79.2258H76.2742C79.0177 79.2258 81.716 78.5275 84.1151 77.1966L87.3713 75.3903H90.0008V52.3654H87.3711V52.365Z" fill="#FDE0BE"/>
                    <path d="M8.80253 49.5215L4.62947 52.3652H2V75.3901H4.62965L7.88586 77.1965C10.2849 78.5273 12.9832 79.2257 15.7267 79.2257H30.411C32.5271 79.2257 34.2426 77.5102 34.2426 75.3941C34.2426 74.4674 33.9135 73.6176 33.366 72.9549C22.0998 69.1947 13.0731 60.5439 8.80253 49.5215ZM87.3711 52.3652L83.198 49.5215C78.9277 60.5438 69.9008 69.1945 58.6349 72.9549C58.0671 73.6408 57.757 74.5036 57.7583 75.3941C57.7583 77.5102 59.4738 79.2257 61.5899 79.2257H76.2742C79.0177 79.2257 81.716 78.5273 84.1151 77.1965L87.3713 75.3901H90.0008V52.3652H87.3711Z" fill="#F7B073"/>
                    <path d="M36.6821 34.0988H15.3151C12.3515 34.0988 9.94922 31.6965 9.94922 28.7329V7.3659C9.94922 4.40231 12.3515 2 15.3151 2H36.6821C39.6457 2 42.048 4.40231 42.048 7.3659V28.7329C42.048 31.6963 39.6457 34.0988 36.6821 34.0988Z" fill="#0CCAAE"/>
                    <path d="M76.6821 34.0988H55.3151C52.3515 34.0988 49.9492 31.6965 49.9492 28.7329V7.3659C49.9492 4.40231 52.3515 2 55.3151 2H76.6821C79.6457 2 82.048 4.40231 82.048 7.3659V28.7329C82.048 31.6963 79.6455 34.0988 76.6821 34.0988Z" fill="#696969"/>
                    <path d="M29.951 34.0977V48.5722M22.0488 43.9833V34.0977M62.0498 34.0977V48.5722M69.952 43.9833V34.0977M14.1822 45.8542L4.62947 52.3639H2V75.3888H4.62965L7.88586 77.1951C10.2849 78.526 12.9832 79.2243 15.7267 79.2243H30.411C32.5271 79.2243 34.2426 77.5089 34.2426 75.3927C34.2426 73.2766 32.5271 71.5611 30.411 71.5611H27.6737H32.9654C35.0815 71.5611 36.797 69.8456 36.797 67.7295C36.797 65.6134 35.0815 63.8979 32.9654 63.8979H27.6737H35.001C37.1171 63.8979 38.8326 62.1824 38.8326 60.0663C38.8326 57.9502 37.1171 56.2347 35.001 56.2347H27.6737H32.9654C35.0815 56.2347 36.797 54.5192 36.797 52.4031C36.797 50.2869 35.0815 48.5715 32.9654 48.5715H25.6409C23.6571 48.5715 22.049 46.9633 22.049 44.9795V43.9825H20.2505C18.0857 43.9831 15.9712 44.6353 14.1822 45.8542ZM77.8185 45.8542L87.3713 52.3639H90.0008V75.3888H87.3711L84.1149 77.1951C81.7159 78.526 79.0175 79.2243 76.2741 79.2243H61.5897C59.4736 79.2243 57.7581 77.5089 57.7581 75.3927C57.7581 73.2766 59.4736 71.5611 61.5897 71.5611H64.3271H59.0354C56.9193 71.5611 55.2038 69.8456 55.2038 67.7295C55.2038 65.6134 56.9193 63.8979 59.0354 63.8979H64.3271H56.9998C54.8836 63.8979 53.1681 62.1824 53.1681 60.0663C53.1681 57.9502 54.8836 56.2347 56.9998 56.2347H64.3271H59.0354C56.9193 56.2347 55.2038 54.5192 55.2038 52.4031C55.2038 50.2869 56.9193 48.5715 59.0354 48.5715H66.3598C68.3436 48.5715 69.9518 46.9633 69.9518 44.9795V43.9825H71.7503C73.915 43.9831 76.0295 44.6353 77.8185 45.8542Z" stroke="#052A75" stroke-width="4" stroke-miterlimit="10" stroke-linejoin="round"/>
                    <path d="M22.0469 79.2246V90.0002H29.9491V79.2246M62.0479 79.2246V90.0002H69.95V79.2246" stroke="#052A75" stroke-width="4" stroke-miterlimit="10" stroke-linejoin="round"/>
                    <path d="M19.5938 16.9866L24.5731 21.966L32.4061 14.1328" stroke="white" stroke-width="4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M36.6821 34.0988H15.3151C12.3515 34.0988 9.94922 31.6965 9.94922 28.7329V7.3659C9.94922 4.40231 12.3515 2 15.3151 2H36.6821C39.6457 2 42.048 4.40231 42.048 7.3659V28.7329C42.048 31.6963 39.6457 34.0988 36.6821 34.0988Z" stroke="#052A75" stroke-width="4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M71.1977 12.8516L60.8008 23.2485M71.1977 23.2485L60.8008 12.8516" stroke="white" stroke-width="4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M76.6821 34.0988H55.3151C52.3515 34.0988 49.9492 31.6965 49.9492 28.7329V7.3659C49.9492 4.40231 52.3515 2 55.3151 2H76.6821C79.6457 2 82.048 4.40231 82.048 7.3659V28.7329C82.048 31.6963 79.6455 34.0988 76.6821 34.0988Z" stroke="#052A75" stroke-width="4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
			</div>
			<p class="modal-text"><?php _e('Select Below to', 'wc-frontend-manager'); ?><br><strong><?php _e('Approve or Reject', 'wc-frontend-manager'); ?></strong></p>
			<div class="rejection-form">
				<label for="rejection-reason"><?php _e('Rejection Reason', 'wc-frontend-manager'); ?> <span class="required">*</span></label>
				<input type="text" id="rejection-reason" placeholder="<?php esc_attr_e('Enter Reason Here', 'wc-frontend-manager'); ?>">
			</div>
			<div class="modal-actions">
				<button type="button" class="modal-btn btn-cancel" id="cancel-rejection"><?php _e('Cancel', 'wc-frontend-manager'); ?></button>
				<button type="button" class="modal-btn btn-submit" id="submit-rejection"><?php _e('Submit', 'wc-frontend-manager'); ?></button>
			</div>
		</div>

		<input type="hidden" id="modal-product-id" value="">
		<span type="button" class="modal-close" id="close-modal">&times;</button>
	</div>
</div>
<?php endif; ?>

<style>
<?php echo modfolio_wcfm_get_header_styles(); ?>

/* Modfolio Products Page Styles */
.modfolio-products-page {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Hide default WCFM elements */
.modfolio-products-page > .wcfm-page-headig {
    display: none !important;
}

/* Tabs Bar */
.modfolio-tabs-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.tabs-left {
    flex: 1;
}

.modfolio-product-tabs {
    display: flex;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.modfolio-product-tabs li {
    margin: 0;
}

.modfolio-product-tabs .tab-btn {
    display: inline-block;
    padding: 10px 20px;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 500;
    color: #666;
    text-decoration: none;
    transition: all 0.2s;
    min-width: 100px;
    text-align: center;
    font-weight: 600;
}

.modfolio-product-tabs .tab-btn:hover {
    border-color: #00c4aa;
    color: #00c4aa;
}

.modfolio-product-tabs .tab-btn.active {
    background: #1a1a1a;
    border-color: #1a1a1a;
    background: #00c4aa;
    border-color: #00c4aa;
    color: #fff;
}

.modfolio-create-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #00c4aa;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    transition: background 0.2s;
}

.modfolio-create-btn:hover {
    background: #00b09a;
    color: #fff;
}

.modfolio-create-btn .btn-icon {
    font-size: 16px;
    font-weight: 700;
}

/* Products Container */
.modfolio-products-container {
    background: #fff;
    border-radius: 20px;
}

.modfolio-no-products {
    padding: 60px 20px;
    text-align: center;
    color: #666;
}

/* Product List */
.modfolio-products-list {
    display: flex;
    flex-direction: column;
    border-radius: 20px;
    overflow: hidden;
}

/* Product Row */
.product-row {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.product-row:hover {
    background: #f8f9fa;
}

.product-row:last-child {
    border-bottom: none;
}

/* Product Columns */
.product-col {
    flex-shrink: 0;
}

.product-col-image {
    width: 70px;
    margin-right: 16px;
}

.product-col-image img,
.product-col-image .product-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.product-col-info {
    flex: 1;
    min-width: 200px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-title {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
    text-decoration: none;
    text-transform: capitalize;
}

.product-title:hover {
    color: #00c4aa;
}

.product-license {
    font-size: 12px;
    color: #666;
}

.product-trending {
    font-size: 12px;
    color: #00c4aa;
    font-weight: 500;
}

.product-rejection-reason {
    font-size: 12px;
    color: #e74c3c;
    font-style: italic;
}

.product-col-price {
    width: 80px;
    text-align: left;
}

.price-value {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
}

.product-col-sales {
    width: 80px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.sales-count {
    font-size: 14px;
    font-weight: 600;
    color: #00c4aa;
}

.sales-label {
    font-size: 12px;
    color: #666;
}

.product-col-status {
    width: 100px;
    text-align: center;
}

.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-active {
    background: #00c4aa;
    color: #fff;
}

.status-inactive {
    background: #ffd700;
    color: #1a1a1a;
}

.status-pending {
    background: #00c4aa;
    color: #fff;
}

.status-default {
    background: #e5e5e5;
    color: #666;
}

.product-col-actions {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-left: auto;
    padding-left: 20px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #666;
    text-decoration: none;
    transition: color 0.2s;
    white-space: nowrap;
}

.action-btn:hover {
    color: #00c4aa;
}

.action-icon {
    font-size: 14px;
}

.action-approve-reject {
    color: #1a1a1a;
    font-weight: 500;
}

.action-activate {
    color: #00c4aa;
}

.action-activate:hover {
    color: #00b09a;
}

/* Pagination */
.modfolio-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 20px;
    border-top: 1px solid #f0f0f0;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f5f5f5;
    color: #666;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}

.page-btn:hover {
    background: #e5e5e5;
    color: #1a1a1a;
}

.page-btn.active {
    background: #00c4aa;
    color: #fff;
}

/* Modal */
.modfolio-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
}

.modfolio-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 360px;
    padding: 30px;
    position: relative;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 30px;
    height: 30px;
    border: none;
    background: none;
    font-size: 24px;
    color: #999;
    cursor: pointer;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #1a1a1a;
}

.modal-content {
    text-align: center;
}

.modal-icon {
    margin-bottom: 20px;
}

.modal-icon img {
    max-width: 120px;
}

.modal-text {
    font-size: 14px;
    color: #666;
    margin-bottom: 24px;
    line-height: 1.5;
}

.modal-text strong {
    color: #1a1a1a;
    font-size: 16px;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.modal-btn {
    padding: 12px 28px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-reject,
.btn-cancel {
    background: #fff;
    border: 1px solid #e5e5e5;
    color: #1a1a1a;
}

.btn-reject:hover,
.btn-cancel:hover {
    background: #f5f5f5;
}

.btn-approve,
.btn-submit {
    background: #00c4aa;
    color: #fff;
}

.btn-approve:hover,
.btn-submit:hover {
    background: #00b09a;
}

/* Rejection Form */
.rejection-form {
    margin-bottom: 24px;
    text-align: left;
}

.rejection-form label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #00c4aa;
    margin-bottom: 8px;
}

.rejection-form label .required {
    color: #e74c3c;
}

.rejection-form input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
}

.rejection-form input:focus {
    outline: none;
    border-color: #00c4aa;
}

/* Responsive */
@media (max-width: 1024px) {
    .modfolio-tabs-bar {
        flex-direction: column;
        gap: 16px;
    }

    .modfolio-product-tabs {
        flex-wrap: wrap;
        justify-content: center;
    }

    .product-row {
        flex-wrap: wrap;
        gap: 12px;
    }

    .product-col-actions {
        width: 100%;
        justify-content: flex-start;
        padding-left: 86px;
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .modfolio-product-tabs .tab-btn {
        padding: 8px 14px;
        font-size: 12px;
    }

    .modfolio-create-btn {
        padding: 8px 16px;
        font-size: 12px;
    }

    .product-col-price,
    .product-col-sales,
    .product-col-status {
        width: auto;
    }
}





/* =========== dark theme =========== */

.dark__theme .modfolio-product-tabs .tab-btn{
    background: #202020;
    border-color: #202020;
}
.dark__theme .modfolio-product-tabs .tab-btn.active{
    background: #00c4aa;
    color: #fff !important;
}
.dark__theme .modfolio-product-tabs .tab-btn:hover{
    color: #00c4aa;
    border-color: #00c4aa;
}

.dark__theme .modfolio-products-container{
    background: #202020;
}

.dark__theme .product-row{
    background: #202020 !important;
    border-color: #484848;
}
.dark__theme .product-row:hover{
    background: #333 !important;
}

.dark__theme .product-row .product-title,
.dark__theme .product-row .price-value{
    color: #fff;
}
.dark__theme .product-row .product-title:hover{
    color: #00c4aa !important;
}
.dark__theme .product-license,
.dark__theme .sales-label,
.dark__theme .action-btn{
    color: #bababa;
}






</style>

<script>
jQuery(document).ready(function($) {
    var currentProductId = null;

    // Open approval modal
    $('.action-approve-reject').on('click', function(e) {
        e.preventDefault();
        currentProductId = $(this).data('product-id');
        $('#modal-product-id').val(currentProductId);
        $('#approval-step-1').show();
        $('#approval-step-2').hide();
        $('#approval-modal').fadeIn(200);
    });

    // Close modal
    $('#close-modal').on('click', function() {
        $('#approval-modal').fadeOut(200);
        currentProductId = null;
    });

    // Click outside to close
    $('#approval-modal').on('click', function(e) {
        if ($(e.target).hasClass('modfolio-modal-overlay')) {
            $(this).fadeOut(200);
            currentProductId = null;
        }
    });

    // Show rejection form
    $('#show-reject-form').on('click', function() {
        $('#approval-step-1').hide();
        $('#approval-step-2').show();
    });

    // Cancel rejection
    $('#cancel-rejection').on('click', function() {
        $('#approval-step-2').hide();
        $('#approval-step-1').show();
        $('#rejection-reason').val('');
    });

    // Approve product
    $('#approve-product').on('click', function() {
        if (!currentProductId) return;

        var $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e("Processing...", "wc-frontend-manager"); ?>');

        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: {
                action: 'modfolio_approve_product',
                product_id: currentProductId,
                nonce: '<?php echo wp_create_nonce("modfolio_product_actions"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e("Error approving product.", "wc-frontend-manager"); ?>');
                    $btn.prop('disabled', false).text('<?php _e("Approve", "wc-frontend-manager"); ?>');
                }
            },
            error: function() {
                alert('<?php _e("Error approving product.", "wc-frontend-manager"); ?>');
                $btn.prop('disabled', false).text('<?php _e("Approve", "wc-frontend-manager"); ?>');
            }
        });
    });

    // Submit rejection
    $('#submit-rejection').on('click', function() {
        if (!currentProductId) return;

        var reason = $('#rejection-reason').val().trim();
        if (!reason) {
            alert('<?php _e("Please enter a rejection reason.", "wc-frontend-manager"); ?>');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e("Processing...", "wc-frontend-manager"); ?>');

        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: {
                action: 'modfolio_reject_product',
                product_id: currentProductId,
                reason: reason,
                nonce: '<?php echo wp_create_nonce("modfolio_product_actions"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e("Error rejecting product.", "wc-frontend-manager"); ?>');
                    $btn.prop('disabled', false).text('<?php _e("Submit", "wc-frontend-manager"); ?>');
                }
            },
            error: function() {
                alert('<?php _e("Error rejecting product.", "wc-frontend-manager"); ?>');
                $btn.prop('disabled', false).text('<?php _e("Submit", "wc-frontend-manager"); ?>');
            }
        });
    });

    // Deactivate product
    $('.action-deactivate').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        if (!confirm('<?php _e("Are you sure you want to deactivate this product?", "wc-frontend-manager"); ?>')) {
            return;
        }

        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: {
                action: 'modfolio_deactivate_product',
                product_id: productId,
                nonce: '<?php echo wp_create_nonce("modfolio_product_actions"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e("Error deactivating product.", "wc-frontend-manager"); ?>');
                }
            }
        });
    });

    // Activate product
    $('.action-activate').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        if (!confirm('<?php _e("Are you sure you want to activate this product?", "wc-frontend-manager"); ?>')) {
            return;
        }

        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: {
                action: 'modfolio_activate_product',
                product_id: productId,
                nonce: '<?php echo wp_create_nonce("modfolio_product_actions"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e("Error activating product.", "wc-frontend-manager"); ?>');
                }
            }
        });
    });
});
</script>
