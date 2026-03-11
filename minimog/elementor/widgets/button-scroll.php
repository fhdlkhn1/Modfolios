<?php

namespace Minimog_Elementor;

defined( 'ABSPATH' ) || exit;

class Widget_Button_Scroll extends Widget_Button {

	public function get_name() {
		return 'tm-button-scroll';
	}

	public function get_title() {
		return __( 'Button: Scroll', 'minimog' );
	}

	public function register_controls() {
		parent::register_controls();

		$this->update_control( 'link', [
			'description' => __( 'To make smooth scroll to a section, then input section\'s ID like this: #about-us-section.', 'minimog' ),
		] );
	}
}
