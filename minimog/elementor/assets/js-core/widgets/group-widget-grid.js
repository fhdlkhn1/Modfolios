(
	function( $ ) {
		'use strict';

		var MinimogGridHandler = function( $scope, $ ) {
			var $element = $scope.find( '.minimog-grid-wrapper' );

			$element.MinimogGridLayout();
		};

		$( window ).on( 'elementor/frontend/init', function() {
			elementorFrontend.hooks.addAction( 'frontend/element_ready/tm-image-gallery.default', MinimogGridHandler );
			elementorFrontend.hooks.addAction( 'frontend/element_ready/tm-product-categories-metro.default', MinimogGridHandler );
		} );
	}
)( jQuery );
