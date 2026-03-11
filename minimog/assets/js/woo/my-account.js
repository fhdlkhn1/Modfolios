(function( $ ) {
	'use strict';

	$( document ).ready( function() {
		// Remove inline css.
		$( '.mo-openid-app-icons' ).each( function() {
			$( this ).find( '.mo_btn-social' ).prop( 'style', false );
			$( this ).find( '.mo_btn-social .mofa' ).prop( 'style', false );
			$( this ).find( '.mo_btn-social svg' ).prop( 'style', false );
		} );

		// Toggle Account Nav
		$( '#btn-toggle-account-nav' ).on( 'click', function( evt ) {
			evt.preventDefault();

			$( this ).closest( '.minimog-wc-account-nav' ).toggleClass( 'opened' );
		} );
	} );

}( jQuery ));
