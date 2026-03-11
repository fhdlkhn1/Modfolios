<?php
/**
 * WCFMu plugin view
 *
 * Admin Dashboard Views
 * This template can be overridden by copying it to yourtheme/wcfm/dashboard/
 *
 * @author 		WC Lovers
 * @package 	wcfm/views
 * @version   1.0.0
 */
 
global $WCFM, $wpdb;

$order_count = 0;
$on_hold_count    = 0;
$processing_count = 0;

foreach ( wc_get_order_types( 'order-count' ) as $type ) {
	$counts           = (array) wp_count_posts( $type );
	$on_hold_count    += isset( $counts['wc-on-hold'] ) ? $counts['wc-on-hold'] : 0;
	$processing_count += isset( $counts['wc-processing'] ) ? $counts['wc-processing'] : 0;
	
	$order_count    += isset( $counts['wc-on-hold'] ) ? $counts['wc-on-hold'] : 0;
	$order_count    += isset( $counts['wc-processing'] ) ? $counts['wc-processing'] : 0;
	$order_count    += isset( $counts['wc-completed'] ) ? $counts['wc-completed'] : 0;
	$order_count    += isset( $counts['wc-pending'] ) ? $counts['wc-pending'] : 0;
}


// Get products using a query - this is too advanced for get_posts :(
$stock          = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 1 ) );
$nostock        = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );
$transient_name = 'wc_low_stock_count';

if ( false === ( $lowinstock_count = get_transient( $transient_name ) ) ) {
	$query_from = apply_filters( 'woocommerce_report_low_in_stock_query_from', $wpdb->prepare("FROM {$wpdb->posts} as posts
		INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
		INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
		WHERE 1=1
		AND posts.post_type IN ( 'product', 'product_variation' )
		AND posts.post_status = 'publish'
		AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
		AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '%s'
		AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '%s'
	", $stock, $nostock) );
	$lowinstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );
	set_transient( $transient_name, $lowinstock_count, DAY_IN_SECONDS * 30 );
}

$transient_name = 'wc_outofstock_count';

if ( false === ( $outofstock_count = get_transient( $transient_name ) ) ) {
	$query_from = apply_filters( 'woocommerce_report_out_of_stock_query_from', $wpdb->prepare("FROM {$wpdb->posts} as posts
		INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
		INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
		WHERE 1=1
		AND posts.post_type IN ( 'product', 'product_variation' )
		AND posts.post_status = 'publish'
		AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
		AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '%s'
	", $nostock) );
	$outofstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );
	set_transient( $transient_name, $outofstock_count, DAY_IN_SECONDS * 30 );
}

include_once( $WCFM->plugin_path . 'includes/reports/class-wcfm-report-sales-by-date.php' );

// For net sales block value
$wcfm_report_sales_by_date_block = new WCFM_Report_Sales_By_Date( '7day' );
$wcfm_report_sales_by_date_block->calculate_current_range( '7day' );
$report_data_block   = $wcfm_report_sales_by_date_block->get_report_data();

// For sales by date graph
$wcfm_report_sales_by_date = new WCFM_Report_Sales_By_Date( 'month' );
$wcfm_report_sales_by_date->calculate_current_range( 'month' );
$report_data   = $wcfm_report_sales_by_date->get_report_data();

// WCFM Analytics
include_once( $WCFM->plugin_path . 'includes/reports/class-wcfm-report-analytics.php' );
$wcfm_report_analytics = new WCFM_Report_Analytics();
$wcfm_report_analytics->chart_colors = apply_filters( 'wcfm_report_analytics_chart_colors', array(
			'view_count'       => '#C79810',
		) );
$wcfm_report_analytics->calculate_current_range( '7day' );

$user_id = get_current_user_id();

$is_marketplace = wcfm_is_marketplace();

$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );

?>

<div class="collapse wcfm-collapse" id="wcfm_dashboard">

  <div class="wcfm-page-headig">
		<span class="wcfmfa fa-chalkboard"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Dashboard', 'wc-frontend-manager' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		
		<?php do_action( 'begin_wcfm_dashboard' ); ?>
		
		<?php //$WCFM->template->get_template( 'dashboard/wcfm-view-dashboard-welcome-box.php' ); ?>
		<?php
		// Use the reusable header function
		$breadcrumb_items = array(
			array( 'label' => __('Portfolio', 'wc-frontend-manager'), 'url' => '' ),
			array( 'label' => isset($custom_menu_labels[$product_status]) ? $custom_menu_labels[$product_status] : 'All', 'url' => '' )
		);
		modfolio_wcfm_render_header( '', $breadcrumb_items );
		?>
		
		<?php if( apply_filters( 'wcfm_is_pref_stats_box', true ) ) { ?>
			<div class="wcfm_dashboard_stats new__dashboard_stats">
				<?php if ( apply_filters( 'wcfm_is_allow_reports', true ) && apply_filters( 'wcfm_sales_report_is_allow_gross_sales', true ) && apply_filters( 'wcfm_is_allow_stats_block_gross_sales', true ) && current_user_can( 'view_woocommerce_reports' ) && ( $report_data_block ) ) { ?>
					<div class="stats__sub_wrapper">
						<div class="wcfm_dashboard_stats_block">
							<a href="<?php echo get_wcfm_reports_url( 'month' ); ?>">
								<span class="wcfmfa fa-currency"><?php echo get_woocommerce_currency_symbol() ; ?></span>
								<div>
									<strong><?php echo wc_price( $report_data_block->total_sales ); ?></strong><br />
									<?php _e( 'gross sales in last 7 days', 'wc-frontend-manager' ); ?>
								</div>
							</a>
						</div>
						<?php } ?>


						<?php
							if( $is_marketplace ) {
								$commission = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor();
								//$total_sell = $WCFM->wcfm_vendor_support->wcfm_get_total_sell_by_vendor();
								
								if( $is_marketplace == 'wcmarketplace' ) {
									global $WCMp;
									if (isset($WCMp->vendor_caps->payment_cap['revenue_sharing_mode'])) {
										if ($WCMp->vendor_caps->payment_cap['revenue_sharing_mode'] == 'admin') {
											$admin_fee_mode = true;
											$grose_sell = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor();
											$commission = $grose_sell - $commission;
										}
									}
								} elseif( $is_marketplace == 'dokan' ) {
									$grose_sell = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor();
									$commission = $grose_sell - $commission;
								} elseif( $is_marketplace == 'wcfmmarketplace' ) {
									$grose_sell = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor();
									$commission = $grose_sell - $commission;
								}
							?>
								<?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) && apply_filters( 'wcfm_is_allow_stats_block_commission', true ) ) { ?>
									<div class="wcfm_dashboard_stats_block">
										<a href="<?php echo get_wcfm_reports_url( ); ?>">
											<span class="wcfmfa fa-money fa-money-bill-alt"></span>
											<div>
												<strong><?php echo wc_price( $commission ); ?></strong><br />
												<?php if( $admin_fee_mode ) { _e( 'admin fees in last 7 days', 'wc-frontend-manager' ); } else { _e( 'commission in last 7 days', 'wc-frontend-manager' ); } ?>
											</div>
										</a>
									</div>
								<?php } ?>

					</div>



					<!-- ===== custom shoot stat ========== -->

					<div class="wcfm_dashboard_stats_block custom__shoot_stats">
						<a href="#">
							<span class="light__mode_icon">
								<svg width="49" height="53" viewBox="0 0 49 53" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M28.6986 32.5614C31.0052 32.5614 32.8816 30.685 32.8816 28.3784C32.8816 26.0719 31.0052 24.1954 28.6986 24.1954C26.392 24.1954 24.5156 26.0719 24.5156 28.3784C24.5156 30.685 26.392 32.5614 28.6986 32.5614ZM28.6986 26.2305C29.883 26.2305 30.8464 27.1941 30.8464 28.3783C30.8464 29.5626 29.8829 30.5261 28.6986 30.5261C27.5143 30.5261 26.5508 29.5626 26.5508 28.3783C26.5508 27.1941 27.5143 26.2305 28.6986 26.2305ZM42.5665 4.22307C43.2118 4.22307 43.7367 4.74793 43.7367 5.39318C43.7367 5.95509 44.1924 6.41077 44.7543 6.41077C45.3162 6.41077 45.7719 5.95509 45.7719 5.39318C45.7719 4.75231 46.2898 4.23009 46.9292 4.22307H46.942L46.9422 4.22296C47.5041 4.22296 47.9597 3.76729 47.9597 3.20538C47.9597 2.64337 47.504 2.1878 46.9421 2.1878C46.2969 2.1878 45.7719 1.66283 45.7719 1.01758C45.7719 0.455673 45.3162 0 44.7543 0C44.1924 0 43.7367 0.455673 43.7367 1.01758C43.7367 1.65856 43.2188 2.18078 42.5794 2.1878H42.5666L42.5664 2.1879C42.0045 2.1879 41.5489 2.64358 41.5489 3.20548C41.5489 3.76749 42.0045 4.22307 42.5665 4.22307Z" fill="black"/>
									<path d="M43.3489 14.319H35.2671L33.7236 10.7117H35.229C36.5732 10.7117 37.6668 9.61809 37.6668 8.27376V3.72894C37.6668 2.38461 36.5732 1.29102 35.229 1.29102H22.1624C20.8182 1.29102 19.7246 2.38461 19.7246 3.72894V8.27376C19.7246 9.61809 20.8182 10.7117 22.1624 10.7117H23.6678L22.1243 14.319H18.4904C18.7015 13.864 18.8108 13.3684 18.8105 12.8668C18.8105 10.9558 17.2558 9.40104 15.3448 9.40104H11.7972C11.6628 9.40102 11.5296 9.42766 11.4055 9.47943C11.2814 9.5312 11.1687 9.60707 11.0741 9.70265L5.14805 15.6882C4.9594 15.8787 4.85357 16.136 4.85356 16.4041V20.2342L3.3859 21.012C1.73671 21.8744 0.712308 23.5662 0.712308 25.4272V28.7683C0.24656 29.5112 -0.000317898 30.3704 3.07216e-07 31.2472V37.6245C3.07216e-07 41.2998 1.3776 44.7632 3.88869 47.4252V51.0828C3.88869 51.6447 4.34436 52.1004 4.90627 52.1004C5.46818 52.1004 5.92385 51.6447 5.92385 51.0828V47.0125C5.92385 46.7426 5.8166 46.4837 5.6258 46.293C3.3103 43.9776 2.03516 40.8991 2.03516 37.6245V31.2472C2.0345 30.7679 2.16483 30.2974 2.41207 29.8868C2.65931 29.4761 3.01406 29.1408 3.438 28.9171C3.93479 28.6542 4.12416 28.0384 3.86121 27.5417C3.64294 27.1296 3.18188 26.9299 2.74747 27.0238V25.4272C2.74747 24.3263 3.35344 23.3256 4.33398 22.8129L11.3244 19.1085L19.6807 23.763C19.8448 23.8537 19.9891 23.9761 20.1055 24.123C20.2219 24.27 20.308 24.4385 20.3588 24.619C20.4106 24.7991 20.4261 24.9877 20.4045 25.1738C20.3829 25.36 20.3246 25.54 20.233 25.7035C19.8753 26.3457 19.0709 26.6122 18.4023 26.3103C18.3966 26.3078 11.8317 22.6463 11.8317 22.6463C11.685 22.5645 11.5202 22.5203 11.3522 22.5177C11.1842 22.5151 11.0182 22.5542 10.869 22.6314L7.3908 24.4308C6.89167 24.6891 6.6963 25.303 6.95456 25.8022C7.21272 26.3014 7.82704 26.4966 8.32596 26.2385L11.3188 24.6902L19.6328 29.3211C20.3291 29.709 20.6065 30.5765 20.2512 31.2549C20.1633 31.4242 20.0422 31.5741 19.895 31.6954C19.7478 31.8168 19.5776 31.9072 19.3947 31.9612C19.2123 32.0163 19.0207 32.034 18.8313 32.0132C18.6419 31.9925 18.4586 31.9338 18.2925 31.8405C18.2132 31.7964 12.2954 28.2205 12.2954 28.2205C12.1358 28.123 11.9523 28.0714 11.7652 28.0714H8.49681C7.9349 28.0714 7.47923 28.5271 7.47923 29.089C7.47923 29.6509 7.9349 30.1066 8.49681 30.1066H11.4791L16.772 33.3379C16.9322 33.4352 17.0716 33.5632 17.182 33.7147C17.2924 33.8661 17.3717 34.038 17.4152 34.2203C17.5047 34.5907 17.4447 34.9738 17.2462 35.2991C16.8362 35.9707 15.9564 36.1833 15.2851 35.7733L10.9213 33.1091C10.7616 33.0116 10.5781 32.96 10.391 32.96H8.59867C8.03676 32.96 7.58109 33.4157 7.58109 33.9776C7.58109 34.5395 8.03676 34.9952 8.59867 34.9952H9.36185V37.5163C9.36185 40.0973 11.4617 42.1972 14.0427 42.1972H20.1578C19.9373 45.2364 18.7311 48.1206 16.7225 50.412C16.3519 50.8345 16.3941 51.4775 16.8167 51.848C17.002 52.011 17.2404 52.1008 17.4872 52.1005C17.6322 52.1007 17.7755 52.0699 17.9075 52.0101C18.0395 51.9503 18.1572 51.863 18.2527 51.7539C20.5915 49.087 21.9732 45.723 22.1968 42.1971H43.3491C45.9301 42.1971 48.03 40.0972 48.03 37.5162V33.2751C48.03 32.7132 47.5743 32.2576 47.0124 32.2576C46.4505 32.2576 45.9948 32.7132 45.9948 33.2751V37.5164C45.9948 38.9753 44.808 40.1621 43.3491 40.1621H14.0427C12.5838 40.1621 11.397 38.9753 11.397 37.5164V35.7841L14.2246 37.5104C14.7658 37.8418 15.3882 38.0171 16.0228 38.0168C17.1904 38.0168 18.3315 37.4271 18.9833 36.3596C19.4267 35.6333 19.569 34.8067 19.4472 34.0253C19.6264 34.001 19.8034 33.963 19.9768 33.9116C20.6575 33.7085 21.2445 33.3122 21.6796 32.7712C23.146 35.1052 25.7421 36.6608 28.696 36.6608C33.2627 36.6608 36.9781 32.9454 36.9781 28.3787C36.9781 23.812 33.2627 20.0966 28.696 20.0966C25.9864 20.0966 23.5766 21.4047 22.0646 23.4225C21.7556 22.8201 21.2761 22.3219 20.6714 21.985L12.9637 17.6918L14.2584 16.3542H43.3493C44.8081 16.3542 45.995 17.541 45.995 18.9999V24.1169C45.995 24.6788 46.4507 25.1345 47.0126 25.1345C47.5745 25.1345 48.0302 24.6788 48.0302 24.1169V18.9999C48.0298 16.4189 45.93 14.319 43.3489 14.319ZM28.6957 22.1317C32.1403 22.1317 34.9426 24.934 34.9426 28.3786C34.9426 31.8232 32.1403 34.6256 28.6957 34.6256C25.2511 34.6256 22.4488 31.8232 22.4488 28.3786C22.4488 24.934 25.2512 22.1317 28.6957 22.1317ZM21.7598 8.27376V3.72894C21.7599 3.62217 21.8023 3.5198 21.8778 3.4443C21.9533 3.36879 22.0557 3.32631 22.1624 3.32618H35.229C35.3358 3.32631 35.4381 3.36879 35.5136 3.4443C35.5891 3.5198 35.6315 3.62217 35.6316 3.72894V8.27376C35.6315 8.38053 35.5891 8.4829 35.5136 8.5584C35.4381 8.63391 35.3358 8.67639 35.229 8.67652H22.1624C22.0557 8.67636 21.9533 8.63387 21.8778 8.55837C21.8024 8.48287 21.7599 8.38052 21.7598 8.27376ZM6.88872 16.8227L12.2217 11.4361H15.3448C16.1336 11.4361 16.7753 12.0779 16.7753 12.8667C16.7753 13.6555 16.1336 14.2973 15.3448 14.2973H13.8477C13.7112 14.2973 13.5762 14.3248 13.4506 14.378C13.325 14.4312 13.2113 14.5092 13.1164 14.6072L10.6362 17.1698L6.88852 19.1558V16.8227H6.88872ZM24.3379 14.319L25.8815 10.7117H31.51L33.0536 14.319H24.3379Z" fill="black"/>
									<path d="M47.0137 27.6797C46.7449 27.6797 46.4835 27.7885 46.2942 27.9778C46.105 28.1672 45.9961 28.4286 45.9961 28.6973C45.9961 28.9649 46.1049 29.2274 46.2942 29.4166C46.4854 29.6069 46.7439 29.7141 47.0137 29.7149C47.2813 29.7149 47.5438 29.606 47.733 29.4166C47.9232 29.2254 48.0303 28.967 48.0313 28.6973C48.0313 28.4285 47.9224 28.1671 47.733 27.9778C47.5418 27.7876 47.2834 27.6805 47.0137 27.6797Z" fill="black"/>
								</svg>
							</span>
							<span class="dark__mode_icon">
								<svg width="49" height="53" viewBox="0 0 49 53" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M28.6986 32.5614C31.0052 32.5614 32.8816 30.685 32.8816 28.3784C32.8816 26.0719 31.0052 24.1954 28.6986 24.1954C26.392 24.1954 24.5156 26.0719 24.5156 28.3784C24.5156 30.685 26.392 32.5614 28.6986 32.5614ZM28.6986 26.2305C29.883 26.2305 30.8464 27.1941 30.8464 28.3783C30.8464 29.5626 29.8829 30.5261 28.6986 30.5261C27.5143 30.5261 26.5508 29.5626 26.5508 28.3783C26.5508 27.1941 27.5143 26.2305 28.6986 26.2305ZM42.5665 4.22307C43.2118 4.22307 43.7367 4.74793 43.7367 5.39318C43.7367 5.95509 44.1924 6.41077 44.7543 6.41077C45.3162 6.41077 45.7719 5.95509 45.7719 5.39318C45.7719 4.75231 46.2898 4.23009 46.9292 4.22307H46.942L46.9422 4.22296C47.5041 4.22296 47.9597 3.76729 47.9597 3.20538C47.9597 2.64337 47.504 2.1878 46.9421 2.1878C46.2969 2.1878 45.7719 1.66283 45.7719 1.01758C45.7719 0.455673 45.3162 0 44.7543 0C44.1924 0 43.7367 0.455673 43.7367 1.01758C43.7367 1.65856 43.2188 2.18078 42.5794 2.1878H42.5666L42.5664 2.1879C42.0045 2.1879 41.5489 2.64358 41.5489 3.20548C41.5489 3.76749 42.0045 4.22307 42.5665 4.22307Z" fill="white"/>
									<path d="M43.3489 14.319H35.2671L33.7236 10.7117H35.229C36.5732 10.7117 37.6668 9.61809 37.6668 8.27376V3.72894C37.6668 2.38461 36.5732 1.29102 35.229 1.29102H22.1624C20.8182 1.29102 19.7246 2.38461 19.7246 3.72894V8.27376C19.7246 9.61809 20.8182 10.7117 22.1624 10.7117H23.6678L22.1243 14.319H18.4904C18.7015 13.864 18.8108 13.3684 18.8105 12.8668C18.8105 10.9558 17.2558 9.40104 15.3448 9.40104H11.7972C11.6628 9.40102 11.5296 9.42766 11.4055 9.47943C11.2814 9.5312 11.1687 9.60707 11.0741 9.70265L5.14805 15.6882C4.9594 15.8787 4.85357 16.136 4.85356 16.4041V20.2342L3.3859 21.012C1.73671 21.8744 0.712308 23.5662 0.712308 25.4272V28.7683C0.24656 29.5112 -0.000317898 30.3704 3.07216e-07 31.2472V37.6245C3.07216e-07 41.2998 1.3776 44.7632 3.88869 47.4252V51.0828C3.88869 51.6447 4.34436 52.1004 4.90627 52.1004C5.46818 52.1004 5.92385 51.6447 5.92385 51.0828V47.0125C5.92385 46.7426 5.8166 46.4837 5.6258 46.293C3.3103 43.9776 2.03516 40.8991 2.03516 37.6245V31.2472C2.0345 30.7679 2.16483 30.2974 2.41207 29.8868C2.65931 29.4761 3.01406 29.1408 3.438 28.9171C3.93479 28.6542 4.12416 28.0384 3.86121 27.5417C3.64294 27.1296 3.18188 26.9299 2.74747 27.0238V25.4272C2.74747 24.3263 3.35344 23.3256 4.33398 22.8129L11.3244 19.1085L19.6807 23.763C19.8448 23.8537 19.9891 23.9761 20.1055 24.123C20.2219 24.27 20.308 24.4385 20.3588 24.619C20.4106 24.7991 20.4261 24.9877 20.4045 25.1738C20.3829 25.36 20.3246 25.54 20.233 25.7035C19.8753 26.3457 19.0709 26.6122 18.4023 26.3103C18.3966 26.3078 11.8317 22.6463 11.8317 22.6463C11.685 22.5645 11.5202 22.5203 11.3522 22.5177C11.1842 22.5151 11.0182 22.5542 10.869 22.6314L7.3908 24.4308C6.89167 24.6891 6.6963 25.303 6.95456 25.8022C7.21272 26.3014 7.82704 26.4966 8.32596 26.2385L11.3188 24.6902L19.6328 29.3211C20.3291 29.709 20.6065 30.5765 20.2512 31.2549C20.1633 31.4242 20.0422 31.5741 19.895 31.6954C19.7478 31.8168 19.5776 31.9072 19.3947 31.9612C19.2123 32.0163 19.0207 32.034 18.8313 32.0132C18.6419 31.9925 18.4586 31.9338 18.2925 31.8405C18.2132 31.7964 12.2954 28.2205 12.2954 28.2205C12.1358 28.123 11.9523 28.0714 11.7652 28.0714H8.49681C7.9349 28.0714 7.47923 28.5271 7.47923 29.089C7.47923 29.6509 7.9349 30.1066 8.49681 30.1066H11.4791L16.772 33.3379C16.9322 33.4352 17.0716 33.5632 17.182 33.7147C17.2924 33.8661 17.3717 34.038 17.4152 34.2203C17.5047 34.5907 17.4447 34.9738 17.2462 35.2991C16.8362 35.9707 15.9564 36.1833 15.2851 35.7733L10.9213 33.1091C10.7616 33.0116 10.5781 32.96 10.391 32.96H8.59867C8.03676 32.96 7.58109 33.4157 7.58109 33.9776C7.58109 34.5395 8.03676 34.9952 8.59867 34.9952H9.36185V37.5163C9.36185 40.0973 11.4617 42.1972 14.0427 42.1972H20.1578C19.9373 45.2364 18.7311 48.1206 16.7225 50.412C16.3519 50.8345 16.3941 51.4775 16.8167 51.848C17.002 52.011 17.2404 52.1008 17.4872 52.1005C17.6322 52.1007 17.7755 52.0699 17.9075 52.0101C18.0395 51.9503 18.1572 51.863 18.2527 51.7539C20.5915 49.087 21.9732 45.723 22.1968 42.1971H43.3491C45.9301 42.1971 48.03 40.0972 48.03 37.5162V33.2751C48.03 32.7132 47.5743 32.2576 47.0124 32.2576C46.4505 32.2576 45.9948 32.7132 45.9948 33.2751V37.5164C45.9948 38.9753 44.808 40.1621 43.3491 40.1621H14.0427C12.5838 40.1621 11.397 38.9753 11.397 37.5164V35.7841L14.2246 37.5104C14.7658 37.8418 15.3882 38.0171 16.0228 38.0168C17.1904 38.0168 18.3315 37.4271 18.9833 36.3596C19.4267 35.6333 19.569 34.8067 19.4472 34.0253C19.6264 34.001 19.8034 33.963 19.9768 33.9116C20.6575 33.7085 21.2445 33.3122 21.6796 32.7712C23.146 35.1052 25.7421 36.6608 28.696 36.6608C33.2627 36.6608 36.9781 32.9454 36.9781 28.3787C36.9781 23.812 33.2627 20.0966 28.696 20.0966C25.9864 20.0966 23.5766 21.4047 22.0646 23.4225C21.7556 22.8201 21.2761 22.3219 20.6714 21.985L12.9637 17.6918L14.2584 16.3542H43.3493C44.8081 16.3542 45.995 17.541 45.995 18.9999V24.1169C45.995 24.6788 46.4507 25.1345 47.0126 25.1345C47.5745 25.1345 48.0302 24.6788 48.0302 24.1169V18.9999C48.0298 16.4189 45.93 14.319 43.3489 14.319ZM28.6957 22.1317C32.1403 22.1317 34.9426 24.934 34.9426 28.3786C34.9426 31.8232 32.1403 34.6256 28.6957 34.6256C25.2511 34.6256 22.4488 31.8232 22.4488 28.3786C22.4488 24.934 25.2512 22.1317 28.6957 22.1317ZM21.7598 8.27376V3.72894C21.7599 3.62217 21.8023 3.5198 21.8778 3.4443C21.9533 3.36879 22.0557 3.32631 22.1624 3.32618H35.229C35.3358 3.32631 35.4381 3.36879 35.5136 3.4443C35.5891 3.5198 35.6315 3.62217 35.6316 3.72894V8.27376C35.6315 8.38053 35.5891 8.4829 35.5136 8.5584C35.4381 8.63391 35.3358 8.67639 35.229 8.67652H22.1624C22.0557 8.67636 21.9533 8.63387 21.8778 8.55837C21.8024 8.48287 21.7599 8.38052 21.7598 8.27376ZM6.88872 16.8227L12.2217 11.4361H15.3448C16.1336 11.4361 16.7753 12.0779 16.7753 12.8667C16.7753 13.6555 16.1336 14.2973 15.3448 14.2973H13.8477C13.7112 14.2973 13.5762 14.3248 13.4506 14.378C13.325 14.4312 13.2113 14.5092 13.1164 14.6072L10.6362 17.1698L6.88852 19.1558V16.8227H6.88872ZM24.3379 14.319L25.8815 10.7117H31.51L33.0536 14.319H24.3379Z" fill="white"/>
									<path d="M47.0137 27.6797C46.7449 27.6797 46.4835 27.7885 46.2942 27.9778C46.105 28.1672 45.9961 28.4286 45.9961 28.6973C45.9961 28.9649 46.1049 29.2274 46.2942 29.4166C46.4854 29.6069 46.7439 29.7141 47.0137 29.7149C47.2813 29.7149 47.5438 29.606 47.733 29.4166C47.9232 29.2254 48.0303 28.967 48.0313 28.6973C48.0313 28.4285 47.9224 28.1671 47.733 27.9778C47.5418 27.7876 47.2834 27.6805 47.0137 27.6797Z" fill="white"/>
								</svg>
							</span>
							<div>
								<strong><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol"><span class="totla__requests">0</span></span> Requests</bdi></span></strong><br>
								Custom Shoots									</div>
						</a>
					</div>



					<div class="stats__sub_wrapper">
						<?php if( apply_filters( 'wcfm_is_allow_stats_block_sold_item', true ) ) { ?>
							<div class="wcfm_dashboard_stats_block">
								<a href="<?php echo apply_filters( 'sales_by_product_report_url', get_wcfm_reports_url( ), '' ); ?>">
									<span class="wcfmfa fa-cube"></span>
									<div>
										<?php printf( _n( "<strong>%s item</strong>", "<strong>%s items</strong>", $report_data_block->total_items, 'wc-frontend-manager' ), $report_data_block->total_items ); ?>
										<br /><?php _e( 'sold in last 7 days', 'wc-frontend-manager' ); ?>
									</div>
								</a>
							</div>
						<?php } ?>
						<?php
						}
						?>

						<?php if ( apply_filters( 'wcfm_is_allow_orders', true ) && apply_filters( 'wcfm_is_allow_stats_block_orders', true ) && current_user_can( 'edit_shop_orders' ) ) { ?>
							<div class="wcfm_dashboard_stats_block">
								<a href="<?php echo get_wcfm_orders_url( ); ?>">
									<span class="wcfmfa fa-cart-plus"></span>
									<div>
										<?php printf( _n( "<strong>%s order</strong>", "<strong>%s orders</strong>", $report_data_block->total_orders, 'wc-frontend-manager' ), $report_data_block->total_orders ); ?>
										<br /><?php _e( 'received in last 7 days', 'wc-frontend-manager' ); ?>
									</div>
								</a>
							</div>
						<?php } ?>
					</div>
			</div>
			<div class="wcfm-clearfix"></div>
		<?php } ?>
		<?php do_action( 'wcfm_after_dashboard_stats_box' ); ?>



		
		<!-- ============ vendor product slider in Dashboard ============ -->


		<?php
		$current_user_id = get_current_user_id();
		$is_admin        = current_user_can( 'administrator' );

		// Product query args
		$args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'tax_query'      => [
				[
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => ['subscription', 'variable-subscription'],
					'operator' => 'NOT IN',
				],
			],
		];

		// If NOT admin → show only vendor products
		if ( ! $is_admin ) {
			$args['author'] = $current_user_id;
		}

		$vendor_product_query = new WP_Query( $args );
		?>

		<div class="wcfm-container vendor__products_container">

			<section class="vendor-products-section group-style-01 minimog-product style-carousel-01 caption-style-01">

				<div class="heading__wrapper">
					<h3 class="wcfm__dashboard_heading"><?php echo $is_admin ? 'All Products' : 'Portfolio Management'; ?></h3>

					<?php if ( $vendor_product_query->have_posts() ) : ?>
						<a class="all__products_link" href="<?php echo esc_url( home_url('/store-manager/products/') ); ?>">
							View All
							<svg width="21" height="15" viewBox="0 0 21 15" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1 6.36426C0.447715 6.36426 0 6.81197 0 7.36426C0 7.91654 0.447715 8.36426 1 8.36426V7.36426V6.36426ZM20.7071 8.07136C21.0976 7.68084 21.0976 7.04768 20.7071 6.65715L14.3431 0.29319C13.9526 -0.0973344 13.3195 -0.0973344 12.9289 0.29319C12.5384 0.683714 12.5384 1.31688 12.9289 1.7074L18.5858 7.36426L12.9289 13.0211C12.5384 13.4116 12.5384 14.0448 12.9289 14.4353C13.3195 14.8259 13.9526 14.8259 14.3431 14.4353L20.7071 8.07136ZM1 7.36426V8.36426H20V7.36426V6.36426H1V7.36426Z" fill="black"/>
							</svg>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( $vendor_product_query->have_posts() ) : ?>

					<!-- Swiper -->
					<div class="swiper vendorProductsSwiper">
						<div class="swiper-wrapper">

							<?php
							while ( $vendor_product_query->have_posts() ) : 
								$vendor_product_query->the_post();
								
								$product_id = get_the_ID();
								$_product = wc_get_product( $product_id );
								
								if ( ! $_product ) continue;

								$view_link  = get_permalink( $product_id );
								$edit_link  = home_url('/store-manager/products-manage/'.$product_id);
								
							?>

							<div class="swiper-slide product__wrapper">
								
								<!-- Method 1: Use WordPress featured image directly -->
								<div class="product__image">
									<a href="<?php echo get_permalink(); ?>">
										<?php if ( has_post_thumbnail() ) : ?>
											<?php the_post_thumbnail( 'woocommerce_thumbnail' ); ?>
										<?php else : ?>
											<img src="<?php echo wc_placeholder_img_src(); ?>" alt="Product placeholder" />
										<?php endif; ?>
									</a>
								</div>
								
								<div class="button__group">
									<a href="<?php echo esc_url( $edit_link ); ?>" class="btn-edit">
										<svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M21.8019 5.39307C19.5786 7.61631 14.3604 12.8335 13.0877 14.102C12.8152 14.3741 12.4909 14.5889 12.134 14.7336C11.1134 15.1571 8.62349 16.1338 7.89147 16.183C7.67569 16.1975 7.4593 16.1657 7.25683 16.0897C7.05435 16.0136 6.87048 15.8952 6.71756 15.7423C6.56463 15.5894 6.44619 15.4055 6.37018 15.203C6.29417 15.0005 6.26235 14.7842 6.27685 14.5684C6.326 13.8364 7.30272 11.3485 7.71892 10.3321C7.86428 9.97131 8.08075 9.64399 8.35578 9.37001L17.0668 0.657977C17.4889 0.23664 18.0609 0 18.6573 0C19.2537 0 19.8258 0.23664 20.2479 0.657977L21.8019 2.21194C22.2232 2.63405 22.4598 3.2061 22.4598 3.80251C22.4598 4.39892 22.2232 4.97096 21.8019 5.39307ZM7.8486 14.6112C8.56388 14.5213 10.6543 13.6492 11.5327 13.2842C11.6997 13.2186 11.8513 13.1193 11.9782 12.9924L11.9793 12.9914C14.8856 10.0908 17.79 7.18815 20.6923 4.28355C20.7556 4.22041 20.8057 4.14543 20.84 4.06289C20.8742 3.98035 20.8918 3.89187 20.8918 3.80251C20.8918 3.71315 20.8742 3.62467 20.84 3.54213C20.8057 3.45958 20.7556 3.3846 20.6923 3.32147L19.1384 1.7675C19.0752 1.70427 19.0003 1.6541 18.9177 1.61987C18.8352 1.58564 18.7467 1.56802 18.6573 1.56802C18.568 1.56802 18.4795 1.58564 18.3969 1.61987C18.3144 1.6541 18.2394 1.70427 18.1763 1.7675L9.46426 10.4795C9.33882 10.6043 9.24024 10.7534 9.17459 10.9177L9.1725 10.9219C8.81172 11.8024 7.93853 13.8949 7.8486 14.6112Z" fill="white"/>
											<path fill-rule="evenodd" clip-rule="evenodd" d="M19.7279 6.35536C19.8007 6.42821 19.8585 6.5147 19.8979 6.60989C19.9374 6.70507 19.9577 6.80709 19.9577 6.91012C19.9577 7.01315 19.9374 7.11517 19.8979 7.21036C19.8585 7.30555 19.8007 7.39203 19.7279 7.46489C19.655 7.53774 19.5685 7.59553 19.4733 7.63496C19.3781 7.67438 19.2761 7.69468 19.1731 7.69468C19.0701 7.69468 18.968 7.67438 18.8729 7.63496C18.7777 7.59553 18.6912 7.53774 18.6183 7.46489L14.9928 3.83932C14.8456 3.69218 14.763 3.49263 14.763 3.28455C14.763 3.07648 14.8456 2.87692 14.9928 2.72979C15.1399 2.58266 15.3395 2.5 15.5475 2.5C15.7556 2.5 15.9552 2.58266 16.1023 2.72979L19.7279 6.35536ZM13.3269 12.7563C13.3997 12.8291 13.4574 12.9155 13.4968 13.0106C13.5362 13.1057 13.5565 13.2076 13.5565 13.3106C13.5565 13.4135 13.5362 13.5154 13.4968 13.6105C13.4574 13.7056 13.3997 13.792 13.3269 13.8648C13.2541 13.9376 13.1677 13.9953 13.0726 14.0347C12.9775 14.0741 12.8756 14.0944 12.7727 14.0944C12.6697 14.0944 12.5678 14.0741 12.4727 14.0347C12.3776 13.9953 12.2912 13.9376 12.2184 13.8648L8.59286 10.2392C8.52007 10.1664 8.46234 10.08 8.42295 9.98494C8.38356 9.88984 8.36328 9.78792 8.36328 9.68498C8.36328 9.58205 8.38356 9.48013 8.42295 9.38503C8.46234 9.28994 8.52007 9.20353 8.59286 9.13074C8.66564 9.05796 8.75205 9.00023 8.84714 8.96083C8.94224 8.92144 9.04416 8.90117 9.1471 8.90117C9.25003 8.90117 9.35195 8.92144 9.44705 8.96083C9.54215 9.00023 9.62855 9.05796 9.70134 9.13074L13.3269 12.7563Z" fill="white"/>
											<path fill-rule="evenodd" clip-rule="evenodd" d="M7.58159 2.06836C7.7896 2.06836 7.98909 2.15099 8.13618 2.29808C8.28326 2.44516 8.36589 2.64465 8.36589 2.85266C8.36589 3.06067 8.28326 3.26016 8.13618 3.40725C7.98909 3.55433 7.7896 3.63696 7.58159 3.63696H3.92151C3.29748 3.63696 2.69901 3.88486 2.25776 4.32612C1.8165 4.76737 1.56861 5.36584 1.5686 5.98987V18.5387C1.56861 19.1627 1.8165 19.7612 2.25776 20.2025C2.69901 20.6437 3.29748 20.8916 3.92151 20.8916H17.5161C18.1401 20.8916 18.7386 20.6437 19.1798 20.2025C19.6211 19.7612 19.869 19.1627 19.869 18.5387V14.8786C19.869 14.6706 19.9516 14.4711 20.0987 14.324C20.2458 14.177 20.4453 14.0943 20.6533 14.0943C20.8613 14.0943 21.0608 14.177 21.2079 14.324C21.355 14.4711 21.4376 14.6706 21.4376 14.8786V18.5387C21.437 19.5786 21.0237 20.5757 20.2884 21.311C19.5531 22.0463 18.556 22.4597 17.5161 22.4602H3.92151C2.88163 22.4597 1.8845 22.0463 1.1492 21.311C0.41389 20.5757 0.000554159 19.5786 0 18.5387V5.98987C0 4.94982 0.413158 3.95237 1.14858 3.21694C1.88401 2.48152 2.88146 2.06836 3.92151 2.06836H7.58159Z" fill="white"/>
										</svg>
									</a>
									<a href="<?php echo esc_url( $view_link ); ?>" class="btn-view" target="_blank">
										<svg width="28" height="19" viewBox="0 0 28 19" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M24.6316 0C22.9914 0 21.657 1.33434 21.657 2.9746C21.657 3.87702 22.0614 4.68624 22.6979 5.23221L18.2135 11.5134C17.8946 11.4 17.5586 11.3421 17.2202 11.3423C16.9358 11.3423 16.6611 11.3833 16.4005 11.458L11.7078 6.42533C11.8938 6.03056 11.99 5.59953 11.9897 5.16315C11.9897 3.52296 10.6553 2.18856 9.01509 2.18856C7.37489 2.18856 6.0405 3.5229 6.0405 5.16315C6.0405 6.00621 6.39364 6.7679 6.95902 7.30967L3.39624 12.5812C3.25655 12.5611 3.11561 12.5509 2.97448 12.5507C1.33446 12.5507 0 13.8852 0 15.5254C0 17.1656 1.33446 18.5 2.9746 18.5C4.61473 18.5 5.94919 17.1657 5.94919 15.5254C5.94919 14.7042 5.61462 13.9597 5.07478 13.4209L8.65563 8.11522C8.77359 8.12946 8.89341 8.13775 9.01509 8.13775C9.50286 8.13824 9.98316 8.01802 10.4132 7.78779L14.8398 12.5351C14.4669 13.0321 14.2456 13.6491 14.2456 14.3169C14.2456 15.9571 15.58 17.2916 17.2202 17.2916C18.8604 17.2916 20.1948 15.9572 20.1948 14.3169C20.1953 13.7346 20.0239 13.165 19.702 12.6797L24.4581 5.94385C24.5155 5.94715 24.5733 5.94932 24.6316 5.94932C26.2717 5.94932 27.6062 4.61497 27.6062 2.97472C27.6062 1.33446 26.2717 0 24.6316 0ZM2.9746 16.624C2.36878 16.624 1.87599 16.1312 1.87599 15.5254C1.87599 14.9196 2.36884 14.4268 2.9746 14.4268C3.58036 14.4268 4.07321 14.9196 4.07321 15.5254C4.07321 16.1312 3.58042 16.624 2.9746 16.624ZM9.01509 6.26177C8.40927 6.26177 7.91647 5.76897 7.91647 5.16315C7.91647 4.55733 8.40933 4.06454 9.01509 4.06454C9.62085 4.06454 10.1137 4.55733 10.1137 5.16315C10.1137 5.76897 9.62085 6.26177 9.01509 6.26177ZM17.2202 15.4155C16.6143 15.4155 16.1215 14.9227 16.1215 14.3169C16.1215 13.711 16.6144 13.2182 17.2202 13.2182C17.8259 13.2182 18.3188 13.711 18.3188 14.3169C18.3188 14.9227 17.8259 15.4155 17.2202 15.4155ZM24.6316 4.07327C24.0257 4.07327 23.5329 3.58048 23.5329 2.97466C23.5329 2.36884 24.0258 1.87605 24.6316 1.87605C25.2373 1.87605 25.7302 2.36878 25.7302 2.9746C25.7302 3.58042 25.2373 4.07327 24.6316 4.07327Z" fill="white"/>
										</svg>
									</a>
								</div>
							</div>

							<?php endwhile; ?>


						</div>

					</div>

					<?php wp_reset_postdata(); ?>

				<?php else : ?>

					<div class="no-products-found">
						<h3><?php esc_html_e( 'No Portfolio yet', 'pm-wcfm' ); ?></h3>
						<p><?php esc_html_e( 'No portfolio items found. Create one to get started.', 'pm-wcfm' ); ?></p>
						<a href="<?php echo home_url('/store-manager/products-manage/') ?>" class="button tm-button slider__add_portfolio"> Add Portfolio <i class="fas fa-plus"></i></a>
					</div>

				<?php endif; ?>

			</section>

		</div>


		<script>
			document.addEventListener('DOMContentLoaded', function () {

				if (typeof Swiper !== 'undefined') {

					new Swiper('.vendorProductsSwiper', {
						slidesPerView: 5,
						spaceBetween: 20,
						loop: false,
						grabCursor: true,
						watchOverflow: true,

						breakpoints: {
							320: {
								slidesPerView: 1,
							},
							576: {
								slidesPerView: 2,
							},
							768: {
								slidesPerView: 3,
							},
							1024: {
								slidesPerView: 4,
							},
							1400: {
								slidesPerView: 5,
							}
						}
					});

				}

			});
		</script>







		
		<?php if ( apply_filters( 'wcfm_is_allow_reports', true ) && current_user_can( 'view_woocommerce_reports' ) && apply_filters( 'wcfm_is_allow_dashboard_reports', true ) ) { ?>
			<div class="wcfm_dashboard_wc_reports_sales">
				<div class="wcfm-container">

				<div class="heading__wrapper">
					<h3 class="wcfm__dashboard_heading">Sales Chart</h3>
				</div>

					<div id="wcfm_dashboard_wc_reports_expander_sales" class="wcfm-content sales__chart_wrapper">
						<div id="poststuff" class="woocommerce-reports-wide">
							<div class="postbox">
								<div class="inside">
									<a class="chart_holder_anchor" href="<?php echo get_wcfm_reports_url( 'month' ); ?>">
										<?php $wcfm_report_sales_by_date->get_main_chart(0); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="wcfm-clearfix"></div>
		<?php } ?>



		
		
		<!-- <div class="wcfm_dashboard_wc_status" style="display: none !important;">
		
			<div class="wcfm_dashboard_wc_status_data">
			
			  <?php //if ( $is_wcfm_analytics_enable = is_wcfm_analytics() ) { ?>
					<?php //if ( apply_filters( 'wcfm_is_allow_analytics', true ) && apply_filters( 'wcfm_is_allow_dashboard_store_analytics', true ) ) { ?>
						<div class="wcfm_dashboard_wcfm_analytics">
							<div class="page_collapsible" id="wcfm_dashboard_wcfm_anaytics"><span class="wcfmfa fa-chart-line"></span><span class="dashboard_widget_head"><?php _e('Store Analytics', 'wc-frontend-manager'); ?></span></div>
							<div class="wcfm-container">
								<div id="wcfm_dashboard_wcfm_analytics_expander" class="wcfm-content">
									<div id="poststuff" class="woocommerce-reports-wide">
										<div class="postbox">
											<div class="inside">
												<?php// if( WCFM_Dependencies::wcfma_plugin_active_check() ) { ?>
													<a class="chart_holder_anchor" href="<?php //echo get_wcfm_analytics_url( 'month' ); ?>">
												<?php //} ?>
														<?php //$wcfm_report_analytics->get_main_chart(); ?>
												<?php //if( WCFM_Dependencies::wcfma_plugin_active_check() ) { ?>
													</a>
												<?php// } ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php //} ?>
				<?php //} ?>
				
				<?php //if ( ( !is_wcfm_analytics() || WCFM_Dependencies::wcfma_plugin_active_check() ) && apply_filters( 'wcfm_is_allow_dashboard_product_stats', true ) ) { ?>
					<div class="wcfm_dashboard_wcfm_product_stats">
						<div class="page_collapsible" id="wcfm_dashboard_wcfm_product_status"><span class="wcfmfa fa-cubes"></span><span class="dashboard_widget_head"><?php _e('Product Stats', 'wc-frontend-manager'); ?></span></div>
						<div class="wcfm-container">
							<div id="wcfm_dashboard_wcfm_product_stats_expander" class="wcfm-content">
								 <?php //if ( apply_filters( 'wcfm_is_allow_manage_products', true ) ) { ?>
								 <a class="chart_holder_anchor" href="<?php //echo get_wcfm_products_url( ); ?>">
								 <?php //} ?>
									 <div id="product_stats-report"><canvas id="product_stats_report-canvas"></canvas></div>	
								 <?php //if ( apply_filters( 'wcfm_is_allow_manage_products', true ) ) { ?>
								 </a>
								 <?php// } ?>
							</div>
						</div>
					</div>
				<?php //} ?>
				
				<?php //do_action( 'after_wcfm_dashboard_product_stats' ); ?>
				
				<?php //if( apply_filters( 'wcfm_is_dashboard_more_stats', true ) ) { ?>
					<?php //if( apply_filters( 'wcfm_is_allow_reports', true ) || apply_filters( 'wcfm_is_allow_orders', true ) ) { ?>
						<div class="wcfm_dashboard_more_stats">
							<div class="page_collapsible" id="wcfm_dashboard_wc_status">
								<span class="wcfmfa fa-list"></span>
								<span class="dashboard_widget_head"><?php// _e('Store Stats', 'wc-frontend-manager'); ?></span>
							</div>
							<div class="wcfm-container">
								<div id="wcfm_dashboard_wc_status_expander" class="wcfm-content">
									<ul class="wc_status_list">
										<?php
										//if ( current_user_can( 'view_woocommerce_reports' ) && ( $top_seller = $WCFM->library->get_top_seller() ) && $top_seller->qty ) {
											?>
											<li class="best-seller-this-month">
												<a href="<?php// echo apply_filters( 'sales_by_product_report_url',  get_wcfm_reports_url( ), $top_seller->product_id ); ?>">
													<span class="wcfmfa fa-cube"></span>
													<?php //printf( __( '%s top seller in last 7 days (sold %d)', 'wc-frontend-manager' ), '<strong>' . get_the_title( $top_seller->product_id ) . '</strong> - ', $top_seller->qty ); ?>
												</a>
											</li>
											<?php
										//}
										?>
										
										<?php //do_action( 'after_wcfm_dashboard_sales_reports' ); ?>
										
										<?php //if ( current_user_can( 'edit_shop_orders' ) ) { ?>
										<li class="processing-orders">
											<a href="<?php //echo get_wcfm_orders_url( 'processing' ); ?>">
												<span class="wcfmfa fa-life-ring"></span>
												<?php //printf( _n( "<strong>%s order</strong> - processing", "<strong>%s orders</strong> - processing", $processing_count, 'wc-frontend-manager' ), $processing_count ); ?>
											</a>
										</li>
										<li class="on-hold-orders">
											<a href="<?php //echo get_wcfm_orders_url( 'on-hold' ); ?>">
												<span class="wcfmfa fa-minus-circle"></span>
												<?php //printf( _n( "<strong>%s order</strong> - on-hold", "<strong>%s orders</strong> - on-hold", $on_hold_count, 'wc-frontend-manager' ), $on_hold_count ); ?>
											</a>
										</li>
										<?php //} ?>
										
										<?php //do_action( 'after_wcfm_dashboard_orders' ); ?>
										
										<?php //if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) { ?>
											<li class="low-in-stock">
												<a href="<?php //echo apply_filters( 'low_in_stock_report_url',  get_wcfm_reports_url( ) ); ?>">
													<span class="wcfmfa fa-sort-amount-down"></span>
													<?php //printf( _n( "<strong>%s product</strong> - low in stock", "<strong>%s products</strong> - low in stock", $lowinstock_count, 'wc-frontend-manager' ), $lowinstock_count ); ?>
												</a>
											</li>
											<li class="out-of-stock">
												<a href="<?php //echo get_wcfm_reports_url( '', 'wcfm-reports-out-of-stock' ); ?>">
													<span class="wcfmfa fa-times-circle"></span>
													<?php //printf( _n( "<strong>%s product</strong> - out of stock", "<strong>%s products</strong> - out of stock", $outofstock_count, 'wc-frontend-manager' ), $outofstock_count ); ?>
												</a>
											</li>
										<?php //} ?>
										
										<?php //do_action( 'after_wcfm_dashboard_stock_reports' ); ?>
										
									</ul>
								</div>
							</div>
						</div>
					<?php //} ?>
				<?php //} ?>
				
			</div>
			
			<?php //do_action( 'after_wcfm_dashboard_left_col' ); ?>
			
			<div class="wcfm_dashboard_wc_status_graph">
			
				<?php //if ( apply_filters( 'wcfm_is_allow_reports', true ) && current_user_can( 'view_woocommerce_reports' ) ) { ?>
					<div class="wcfm_dashboard_wc_reports_pie">
						<div class="page_collapsible" id="wcfm_dashboard_wc_reports_pie"><span class="wcfmfa fa-chart-pie"></span><span class="dashboard_widget_head"><?php //_e('Sales by Product', 'wc-frontend-manager'); ?></span></div>
						<div class="wcfm-container">
							<div id="wcfm_dashboard_wc_reports_expander_pie" class="wcfm-content">
								<a class="chart_holder_anchor" href="<?php //echo apply_filters( 'sales_by_product_report_url',  get_wcfm_reports_url( ), ( $top_seller ) ? $top_seller->product_id : '' ); ?>">
									<div id="sales-piechart"><canvas id="sales-piechart-canvas"></canvas></div>
								</a>
							</div>
						</div>
					</div>
					<?php //do_action('after_wcfm_dashboard_sales_report'); ?>
				<?php //} ?>
				
				<?php// if ( is_wcfm_analytics() && WCFM_Dependencies::wcfma_plugin_active_check() ) { ?>
					<?php //if ( $wcfm_is_allow_analytics = apply_filters( 'wcfm_is_allow_analytics', true ) ) { ?>
						<div class="wcfm_dashboard_wcfm_region_stats">
							<div class="page_collapsible" id="wcfm_dashboard_wcfm_region_status"><span class="wcfmfa fa-globe"></span><span class="dashboard_widget_head"><?php //_e('Top Regions', 'wc-frontend-manager'); ?></span></div>
							<div class="wcfm-container">
								<div id="wcfm_dashboard_wcfm_region_stats_expander" class="wcfm-content">
									 <a class="chart_holder_anchor" href="<?php// echo get_wcfm_analytics_url( 'month' ); ?>">
										 <div id="wcfm_world_map_analytics_view"></div>
										 <?php
										//  global $WCFMa;
										//  $WCFMa->library->world_map_analytics_data(); 
										 ?>
									 </a>
								</div>
							</div>
						</div>
					<?php //} ?>
				<?php //} ?>
				
				<?php// do_action('after_wcfm_dashboard_zone_analytics'); ?>
				
				<?php //if( apply_filters( 'wcfm_is_allow_notice', true ) && apply_filters( 'wcfm_is_allow_dashboard_latest_topics', true ) ) { ?>
					<div class="wcfm_dashboard_latest_topics">
						<div class="page_collapsible" id="wcfm_dashboard_latest_topics"><span class="wcfmfa fa-bullhorn"></span><span class="dashboard_widget_head"><?php //_e('Latest Topics', 'wc-frontend-manager'); ?></span></div>
						<div class="wcfm-container">
							<div id="wcfm_dashboard_latest_topics_expander" class="wcfm-content">
								<?php
								// $args = array(
								// 	'posts_per_page'   => 5,
								// 	'offset'           => 0,
								// 	'orderby'          => 'date',
								// 	'order'            => 'DESC',
								// 	'post_type'        => 'wcfm_notice',
								// 	'post_parent'      => 0,
								// 	'post_status'      => array('draft', 'pending', 'publish'),
								// 	'suppress_filters' => 0 
								// );
								// $args = apply_filters( 'wcfm_notice_args', $args );
								// $wcfm_notices_array = get_posts( $args );
								
								// $wcfm_dashboard_notice_content_length = (int) apply_filters( 'wcfm_is_allow_dashboard_notice_content_length', 80 );
								
								// if( !empty( $wcfm_notices_array ) ) {
								// 	foreach($wcfm_notices_array as $wcfm_notices_single) {
								// 		echo '<div class="wcfm_dashboard_latest_topic"><a href="' . get_wcfm_notice_view_url($wcfm_notices_single->ID) . '" class="wcfm_dashboard_item_title"><span class="wcfmfa fa-bullhorn"></span>' . substr( $wcfm_notices_single->post_title, 0, $wcfm_dashboard_notice_content_length ) . ' ...</a></div>';
								// 	}
								// } else {
								// 	_e( 'There is no topic yet!!', 'wc-frontend-manager' );
								// }
								?>
							</div>
						</div>
					</div>
				<?php //} ?>
			  
			</div>
			<?php //do_action( 'after_wcfm_dashboard_right_col' ); ?>
		</div> -->




	</div>
</div>