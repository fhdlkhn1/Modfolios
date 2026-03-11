<?php
$sidebar_positions   = Minimog_Helper::get_list_sidebar_positions();
$registered_sidebars = Minimog_Redux::instance()->get_registered_widgets_options();

Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Single Post', 'minimog' ),
	'id'         => 'single_blog',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'     => 'section_start_single_blog_header',
			'type'   => 'tm_heading',
			'title'  => __( 'Header Settings', 'minimog' ),
			'indent' => true,
		),
		array(
			'id'          => 'blog_single_header_type',
			'type'        => 'select',
			'title'       => __( 'Header Style', 'minimog' ),
			'placeholder' => __( 'Use Global Setting', 'minimog' ),
			'options'     => Minimog_Header::instance()->get_list( true ),
		),
		array(
			'id'          => 'blog_single_header_overlay',
			'type'        => 'select',
			'title'       => __( 'Header Overlay', 'minimog' ),
			'placeholder' => __( 'Use Global Setting', 'minimog' ),
			'options'     => Minimog_Header::instance()->get_overlay_list(),
		),
		array(
			'id'          => 'blog_single_header_skin',
			'type'        => 'select',
			'title'       => __( 'Header Skin', 'minimog' ),
			'placeholder' => __( 'Use Global Setting', 'minimog' ),
			'options'     => Minimog_Header::instance()->get_skin_list(),
		),
		array(
			'id'     => 'section_start_blog_single_title_bar',
			'type'   => 'tm_heading',
			'title'  => __( 'Title Bar Settings', 'minimog' ),
			'indent' => true,
		),
		array(
			'id'          => 'blog_single_title_bar_layout',
			'type'        => 'select',
			'title'       => __( 'Title Bar Style', 'minimog' ),
			'placeholder' => __( 'Use Global Setting', 'minimog' ),
			'options'     => Minimog_Title_Bar::instance()->get_list( true ),
			'default'     => Minimog_Redux::get_default_setting( 'blog_single_title_bar_layout' ),
		),
		array(
			'id'     => 'section_start_blog_single_sidebar',
			'type'   => 'tm_heading',
			'title'  => __( 'Sidebar Settings', 'minimog' ),
			'indent' => true,
		),
		array(
			'id'      => 'post_page_sidebar_1',
			'type'    => 'select',
			'title'   => __( 'Sidebar 1', 'minimog' ),
			'options' => $registered_sidebars,
			'default' => Minimog_Redux::get_default_setting( 'post_page_sidebar_1' ),
		),
		array(
			'id'      => 'post_page_sidebar_2',
			'type'    => 'select',
			'title'   => __( 'Sidebar 2', 'minimog' ),
			'options' => $registered_sidebars,
			'default' => Minimog_Redux::get_default_setting( 'post_page_sidebar_2' ),
		),
		array(
			'id'      => 'post_page_sidebar_position',
			'type'    => 'button_set',
			'title'   => __( 'Sidebar Position', 'minimog' ),
			'options' => $sidebar_positions,
			'default' => Minimog_Redux::get_default_setting( 'post_page_sidebar_position' ),
		),
		array(
			'id'      => 'post_page_sidebar_style',
			'type'    => 'select',
			'title'   => __( 'Sidebar Style', 'minimog' ),
			'options' => Minimog_Sidebar::instance()->get_supported_style_options(),
			'default' => Minimog_Redux::get_default_setting( 'post_page_sidebar_style' ),
		),
		array(
			'id'     => 'section_start_blog_single_layout',
			'type'   => 'tm_heading',
			'title'  => __( 'Other Settings', 'minimog' ),
			'indent' => true,
		),
		array(
			'id'      => 'single_post_related_enable',
			'type'    => 'button_set',
			'title'   => __( 'Related Post', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_related_enable' ),
		),
		array(
			'id'            => 'single_post_related_number',
			'title'         => __( 'Number of related posts item', 'minimog' ),
			'type'          => 'slider',
			'default'       => Minimog_Redux::get_default_setting( 'single_post_related_number' ),
			'min'           => 1,
			'max'           => 30,
			'step'          => 1,
			'display_value' => 'text',
			'required'      => array(
				[ 'single_post_related_enable', '=', '1' ],
			),
		),
		array(
			'id'      => 'single_post_feature_enable',
			'type'    => 'button_set',
			'title'   => __( 'Featured Image', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_feature_enable' ),
		),
		array(
			'id'      => 'single_post_title_enable',
			'type'    => 'button_set',
			'title'   => __( 'Post Title', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_title_enable' ),
		),
		array(
			'id'      => 'single_post_categories_enable',
			'type'    => 'button_set',
			'title'   => __( 'Categories', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_categories_enable' ),
		),
		array(
			'id'      => 'single_post_tags_enable',
			'type'    => 'button_set',
			'title'   => __( 'Tags', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_tags_enable' ),
		),
		array(
			'id'      => 'single_post_date_enable',
			'type'    => 'button_set',
			'title'   => __( 'Post Meta Date', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_date_enable' ),
		),
		array(
			'id'      => 'single_post_author_enable',
			'type'    => 'button_set',
			'title'   => __( 'Post Meta Author', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_author_enable' ),
		),
		array(
			'id'      => 'single_post_share_enable',
			'type'    => 'button_set',
			'title'   => __( 'Sharing', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_share_enable' ),
		),
		array(
			'id'      => 'single_post_author_box_enable',
			'type'    => 'button_set',
			'title'   => __( 'Author Info Box', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_author_box_enable' ),
		),
		array(
			'id'      => 'single_post_pagination_enable',
			'type'    => 'button_set',
			'title'   => __( 'Previous/Next Pagination', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_pagination_enable' ),
		),
		array(
			'id'      => 'single_post_comment_enable',
			'type'    => 'button_set',
			'title'   => __( 'Comments List/Form', 'minimog' ),
			'options' => array(
				'0' => __( 'Hide', 'minimog' ),
				'1' => __( 'Show', 'minimog' ),
			),
			'default' => Minimog_Redux::get_default_setting( 'single_post_comment_enable' ),
		),
	),
) );
