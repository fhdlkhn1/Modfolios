(function( $ ) {
	'use strict'

	$( document ).ready( function() {
		$( document.body ).on( 'should_send_ajax_request.adding_to_cart', function( evt, $thisButton ) {
			if( typeof checkAddonsRequirements === 'function') {
				var checkResult = checkAddonsRequirements();
				if ( ! checkResult ) { // if it's not true, do not allow to add to cart.
					return false;
				}
			}

			return true;
		} );
	} );
}( jQuery ));
