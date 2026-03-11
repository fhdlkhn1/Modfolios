(
	function( $ ) {
		'use strict';

		var MinimogQueryHandler = function( $scope, $ ) {
			var $element = $scope.find( '.minimog-grid-wrapper' );
			$element.MinimogGridQuery();
			$element.on( 'MinimogQueryEnd', function( event, el, $items ) {
				el.find('.minimog-grid').append($items);
			} );
		};

		$( window ).on( 'elementor/frontend/init', function() {
			elementorFrontend.hooks.addAction( 'frontend/element_ready/tm-blog.default', MinimogQueryHandler );
			elementorFrontend.hooks.addAction( 'frontend/element_ready/tm-product.default', MinimogQueryHandler );
		} );
	}
)( jQuery );
