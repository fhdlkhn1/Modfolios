/**
 * Functions for archive pages.
 */
(function( $ ) {
	'use strict';

	var $body             = $( 'body' ),
	    $pageSidebars     = $( '.page-sidebar' ),
	    Helpers           = window.minimog.Helpers,
	    collapseDuration  = 250,
	    COLLAPSED_CLASS   = 'collapsed',
	    COLLAPSIBLE_CLASS = 'sidebar-widgets-collapsible';

	$( document ).ready( function() {
		initBlogMasonry();
		handlerScrollInfinite();

		$( document.body ).on( 'click', '.minimog-grid-pagination a.page-numbers', function( evt ) {
			evt.preventDefault();

			var $link = $( this ),
			    href  = $link.attr( 'href' ),
			    url   = href.includes( window.location.origin ) ? href : window.location.origin + href;

			filterPostsByUrl( url, { scrollTop: 1 } );
		} );

		$( document.body ).on( 'click', '.archive-load-more-button', function( evt ) {
			evt.preventDefault();

			var $button = $( this ),
			    href    = $button.attr( 'data-url' ),
			    url     = href.includes( window.location.origin ) ? href : window.location.origin + href;

			filterPostsByUrl( url, { loadMore: 1 } );
		} );
	} );

	function filterPostsByUrl( url, options = {} ) {
		var settings = $.extend( true, {}, {
			loadMore: 0,
			scrollTop: 0
		}, options );

		url = decodeURIComponent( url );

		if ( ! settings.loadMore ) {
			history.pushState( {}, null, url );
		}

		var $btnLoadMore = $( '.archive-load-more-button' );

		$.ajax( {
			url: url,
			type: 'GET',
			dataType: 'html',
			success: function( response ) {
				var $response = $( response );

				var $gridWrapper = $( '#minimog-main-post' ),
				    $grid        = $gridWrapper.children( '.minimog-grid' );

				if ( ! settings.loadMore ) {
					$grid.children( '.grid-item' ).remove();
				}

				var $newItems = $response.find( '#minimog-main-post .grid-item' );
				if ( $gridWrapper.data( 'minimogGridLayout' ) ) {
					$gridWrapper.MinimogGridLayout( 'update', $newItems );
				} else {
					$grid.append( $newItems );
				}

				var fragments = [
					'.minimog-grid-pagination',
				];

				var $filterWidgets = $( '.minimog-wp-widget-filter' );

				$filterWidgets.each( function() {
					fragments.push( '#' + $( this ).attr( 'id' ) );
				} );

				for ( let i = 0, totalFragments = fragments.length; i < totalFragments; i ++ ) {
					var key  = fragments[i],
					    $key = $( key );

					if ( $key.length > 0 ) {
						$key.empty();
						var $newElement = $response.find( key );
						if ( $newElement.length > 0 ) {
							$key.html( $newElement.html() );
						}
					}
				}

				$( document.body ).trigger( 'minimog_get_post_fragments_loaded', [ $response ] );

				if ( settings.scrollTop ) {
					var offsetTop = $gridWrapper.offset().top;
					offsetTop -= 198; // Header + topbar + filter bar.
					offsetTop     = Math.max( 0, offsetTop );

					$( 'html, body' ).animate( { scrollTop: offsetTop }, 300 );
				}

				// Disable collapse if it opens before.
				$pageSidebars.each( function() {
					var $thisSidebar = $( this );
					if ( $thisSidebar.hasClass( COLLAPSIBLE_CLASS ) ) {
						$thisSidebar.find( '.widget:not(.' + COLLAPSED_CLASS + ')' ).find( '.widget-content' ).stop().slideDown( collapseDuration );
					}
				} );

				// Update Widget Scrollable size.
				if ( $.fn.perfectScrollbar && ! Helpers.isHandheld() ) {
					$pageSidebars.find( '.widget-scrollable' ).each( function() {
						$( this ).find( '.widget-content-inner' ).perfectScrollbar( 'update' );
					} );
				}
			},
			beforeSend: function() {
				if ( settings.loadMore ) {
					Helpers.setElementHandling( $btnLoadMore );
				} else {
					Helpers.setBodyHandling();
				}
			},
			complete: function() {
				if ( settings.loadMore ) {
					Helpers.unsetElementHandling( $btnLoadMore );
				} else {
					Helpers.setBodyCompleted();
				}
			}
		} );
	}

	function handlerScrollInfinite() {
		var $el = $( '.minimog-grid-pagination' );

		if ( 'infinite' !== $el.data( 'type' ) ) {
			return;
		}

		var lastST  = 0;
		var $window = $( window );

		$window.on( 'scroll', function() {
			var currentST = $( this ).scrollTop();

			// Scroll down only.
			if ( currentST > lastST ) {
				var windowHeight = $window.height();
				// 90% window height.
				var halfWH       = 90 / 100 * windowHeight;
				halfWH           = parseInt( halfWH );

				var elOffsetTop = $el.offset().top;
				var elHeight    = $el.outerHeight( true );
				var offsetTop   = elOffsetTop + elHeight;
				var finalOffset = offsetTop - halfWH;

				if ( currentST >= finalOffset ) {
					var $button = $el.find( '.archive-load-more-button' );

					if ( ! $button.hasClass( 'updating-icon' ) ) {
						$button.trigger( 'click' );
					}
				}
			}

			lastST = currentST;
		} );
	}

	function initBlogMasonry() {
		if ( $.fn.MinimogGridLayout ) {
			var $gridWrapper = $( '#minimog-main-post' );

			if ( $gridWrapper.hasClass( 'minimog-grid-masonry' ) ) {
				$gridWrapper.MinimogGridLayout();
			}
		}
	}

}( jQuery ));
