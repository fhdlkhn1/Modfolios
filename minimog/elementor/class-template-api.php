<?php

namespace Minimog_Elementor;

defined( 'ABSPATH' ) || exit;

class Template_API {

	public function __construct() {
		add_filter( 'tm_addons/elementor/templates_info_api', [ $this, 'get_templates_info_api' ] );
		add_filter( 'tm_addons/elementor/template_data_api', [ $this, 'get_template_data_api' ] );
		add_filter( 'tm_addons/elementor/template_tags', [ $this, 'get_template_tags_api' ] );
	}

	public function get_configs() {
		/**
		 * Templates API: https://minimog-templates.thememove.com/wp-json/tm/v2/templates/
		 * Template API: https://minimog-templates.thememove.com/wp-json/tm/v2/templates/%d/
		 */
		return [
			'base'      => 'https://minimog-templates.thememove.com/',
			'path'      => 'wp-json/tm/v2',
			'endpoints' => array(
				'templates'      => '/templates/',
				'template'       => '/templates/%d/',
				'template_types' => '/template_types/',
				'template_tags'  => '/template_tags/',
			),
		];
	}

	public function get_api_url( $flag ) {
		$config = $this->get_configs();

		if ( empty( $config['endpoints'][ $flag ] ) ) {
			return false;
		}

		return $config['base'] . $config['path'] . $config['endpoints'][ $flag ];
	}

	public function get_templates_info_api() {
		return $this->get_api_url( 'templates' );
	}

	public function get_template_data_api() {
		return $this->get_api_url( 'template' );
	}

	public function get_template_tags_api() {
		return $this->get_api_url( 'template_tags' );
	}
}

new Template_API();
