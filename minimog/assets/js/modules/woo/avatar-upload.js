(function( $ ) {
	'use strict';

	$( document ).ready( function() {
		uploadAvatar();
		removeAvatar();
		toggleButton();
	} );

	function toggleButton() {
		$( document.body ).on( 'click', '.btn-toggle-avatar-upload-menu', function( evt ) {
			evt.preventDefault();
			evt.stopPropagation();

			$( this ).closest( '.my-avatar' ).children( '.minimog-user-profile' ).toggleClass( 'active' );
		} );

		$( document.body ).on( 'click', function( evt ) {
			if ( ! $( evt.target ).is( '.btn-toggle-avatar-upload-menu' ) ) {
				$( '.minimog-user-profile' ).removeClass( 'active' );
			}
		} );
	}

	function uploadAvatar() {
		var $uploadWrap    = $( '.my-account-profile' ),
		    $form          = $( '.minimog-user-profile__form' ),
		    $avatar        = $( '.avatar', $uploadWrap ),
		    $toggleMenuBtn = $( '.btn-toggle-avatar-upload-menu', $uploadWrap ),
		    $fileField     = $( '.tm_user_avatar', $form ),
		    $uploadBtn     = $( '.upload_avatar', $form );

		$uploadBtn.on( 'click', function( e ) {
			e.preventDefault();
			$( this ).closest( '.minimog-user-profile__action' ).removeClass( 'active' );
			$( this ).closest( '.minimog-user-profile__form' ).find( '.tm_user_avatar' ).trigger( 'click' );
		} );

		$fileField.on( 'change', function( e ) {
			var file = e.target.files,
			    data = new FormData( $form.get( 0 ) );

			data.append( 'action', 'minimog_upload_avatar' );
			data.append( '_ajax_nonce', $minimogUpload.update_avatar_nonce );

			$.each( file, function( key, value ) {
				data.append( 'user_avatar', value );
			} );

			$.ajax( {
				url: $minimog.ajaxurl,
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function( response, textStatus, jqXHR ) {
					if ( response.data ) {
						var newAvatar = new Image();
						newAvatar.src = response.data;
						newAvatar.alt = 'test';

						newAvatar.onload = function() {
							$avatar.find( 'img' ).remove();
							$avatar.append( newAvatar );
						}
					}
				},
				beforeSend: function() {
					$toggleMenuBtn.addClass( 'updating-icon' );
				},
				complete: function() {
					$toggleMenuBtn.removeClass( 'updating-icon' );
				}
			} );
		} );

		$form.on( 'submit', function( e ) {
			e.preventDefault();
		} );
	}

	function removeAvatar() {
		var $uploadWrap    = $( '.my-account-profile' ),
		    $form          = $( '.minimog-user-profile__form' ),
		    $toggleMenuBtn = $( '.btn-toggle-avatar-upload-menu', $uploadWrap ),
		    $avatar        = $( '.avatar', $uploadWrap ),
		    $removeBtn     = $( '.remove_avatar', $form );

		$removeBtn.on( 'click', function( e ) {
			e.preventDefault();

			$( this ).closest( '.minimog-user-profile__action' ).removeClass( 'active' );

			var data = new FormData( $form.get( 0 ) );

			data.append( 'action', 'minimog_remove_avatar' );
			data.append( '_ajax_nonce', $minimogUpload.update_avatar_nonce );

			$.ajax( {
				url: $minimog.ajaxurl,
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function( response, textStatus, jqXHR ) {
					if ( response.data ) {
						var newAvatar = new Image();
						newAvatar.src = response.data;
						newAvatar.alt = 'test';

						newAvatar.onload = function() {
							$avatar.find( 'img' ).remove();
							$avatar.append( newAvatar );
						}
					}
				},
				beforeSend: function() {
					$toggleMenuBtn.addClass( 'updating-icon' );
				},
				complete: function() {
					$toggleMenuBtn.removeClass( 'updating-icon' );
				}
			} );
		} );
	}
}( jQuery ));
