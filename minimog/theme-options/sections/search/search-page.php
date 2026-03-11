<?php
Redux::set_section( Minimog_Redux::OPTION_NAME, array(
	'title'      => __( 'Search Page', 'minimog' ),
	'id'         => 'search_page',
	'subsection' => true,
	'fields'     => array(
		array(
			'id'          => 'search_page_filter',
			'type'        => 'select',
			'title'       => __( 'Search Results Filter', 'minimog' ),
			'description' => __( 'Controls the type of content that displays in search results.', 'minimog' ),
			'options'     => [
				'all'     => __( 'All Post Types and Pages', 'minimog' ),
				'page'    => __( 'Only Pages', 'minimog' ),
				'post'    => __( 'Only Blog Posts', 'minimog' ),
				'product' => __( 'Only Products', 'minimog' ),
			],
			'default'     => 'product',
		),
		array(
			'id'            => 'search_page_number_results',
			'title'         => __( 'Number of Search Results Per Page', 'minimog' ),
			'description'   => __( 'Controls the number of search results per page.', 'minimog' ),
			'type'          => 'slider',
			'default'       => 10,
			'min'           => 1,
			'max'           => 100,
			'step'          => 1,
			'display_value' => 'text',
		),
		array(
			'id'          => 'search_page_search_form_display',
			'title'       => __( 'Search Form Display', 'minimog' ),
			'description' => __( 'Controls the display of the search form on the search results page.', 'minimog' ),
			'type'        => 'select',
			'options'     => [
				'below'    => __( 'Below Result List', 'minimog' ),
				'above'    => __( 'Above Result List', 'minimog' ),
				'disabled' => __( 'Hide', 'minimog' ),
			],
			'default'     => 'disabled',
		),
		array(
			'id'          => 'search_page_no_results_text',
			'title'       => __( 'No Results Text', 'minimog' ),
			'description' => __( 'Enter the text that displays on search no results page.', 'minimog' ),
			'type'        => 'textarea',
			'default'     => __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'minimog' ),
		),
	),
) );
