(function( $ ) {
	'use strict';

	var $window      = $( window ),
	    $body        = $( 'body' ),
	    $pageTopBar  = $( '#page-top-bar' ),
	    $pageHeader  = $( '#page-header' ),
	    $headerInner = $pageHeader.find( '#page-header-inner' ),
	    wWidth       = window.innerWidth,
	    wHeight      = window.innerHeight,
	    Helpers      = window.minimog.Helpers;

	window.minimog.LightGallery = {
		selector: '.zoom',
		mode: 'lg-fade',
		thumbnail: $minimog.light_gallery_thumbnail === '1',
		download: $minimog.light_gallery_download === '1',
		autoplay: $minimog.light_gallery_auto_play === '1',
		zoom: $minimog.light_gallery_zoom === '1',
		share: $minimog.light_gallery_share === '1',
		fullScreen: $minimog.light_gallery_full_screen === '1',
		hash: false,
		animateThumb: false,
		showThumbByDefault: false,
		getCaptionFromTitleOrAlt: false
	};

	// Call this asap for better rendering.
	calMobileMenuBreakpoint();

	$window.on( 'resize', function() {
		if ( wWidth !== window.innerWidth ) {
			$window.trigger( 'hresize' );
		}

		if ( wHeight !== window.innerHeight ) {
			$window.trigger( 'vresize' );
		}

		wWidth  = window.innerWidth;
		wHeight = window.innerHeight;
	} );

	$window.on( 'hresize', function() {
		calMobileMenuBreakpoint();
	} );

	$window.on( 'load', function() {
		initPreLoader();
		initStickyHeader();
		handlerLanguageSwitchers();
		handlerCurrencySwitchers();
		handlerTopBarSubMenus();
	} );

	$( document ).ready( function() {
		initSliders();

		initSearchPopup();
		initTopBarCollapsible();
		initSmartmenu();
		initSplitNavHeader();
		initMobileMenu();
		initCookieNotice();
		initLightGalleryPopups();
		initVideoPopups();

		initAccordion();
		initNiceSelect();
		initModal();
		handlerVerticalCategoryMenu();
	} );

	function initPreLoader() {
		$body.addClass( 'loaded' );

		setTimeout( function() {
			var $loader = $( '#page-preloader' );

			if ( $loader.length > 0 ) {
				$loader.remove();
			}
		}, 2000 );
	}

	function initSliders() {
		$( '.tm-slider' ).each( function() {
			if ( $( this ).hasClass( 'minimog-swiper-linked-yes' ) ) {
				var mainSlider   = $( this ).children( '.minimog-main-swiper' ).MinimogSwiper();
				var thumbsSlider = $( this ).children( '.minimog-thumbs-swiper' ).MinimogSwiper();

				mainSlider.controller.control   = thumbsSlider;
				thumbsSlider.controller.control = mainSlider;
			} else {
				$( this ).MinimogSwiper();
			}
		} );
	}

	function initLightGalleryPopups() {
		if ( $.fn.lightGallery ) {
			$( '.minimog-light-gallery' ).each( function() {
				$( this ).lightGallery( window.minimog.LightGallery );
			} );
		}
	}

	function initVideoPopups() {
		if ( $.fn.lightGallery ) {
			var options = {
				selector: 'a',
				fullScreen: false,
				zoom: false,
				getCaptionFromTitleOrAlt: false,
				counter: false
			};

			$( '.tm-popup-video' ).each( function() {
				$( this ).lightGallery( options );
			} );
		}
	}

	function initNiceSelect() {
		if ( $.fn.MinimogNiceSelect ) {
			$( '.minimog-nice-select' ).MinimogNiceSelect();
		}
	}

	function initAccordion() {
		if ( $.fn.MinimogAccordion ) {
			$( '.minimog-accordion' ).MinimogAccordion();
		}
	}

	function initModal() {
		if ( $.fn.MinimogModal ) {
			$body.on( 'click', '[data-minimog-toggle="modal"]', function( evt ) {
				var $target = $( $( this ).data( 'minimog-target' ) );

				if ( $target.length > 0 ) {
					evt.preventDefault();

					if ( $( this ).attr( 'data-minimog-dismiss' ) === '1' ) {
						$target.MinimogModal( 'close' );
					} else {
						$target.MinimogModal( 'open' );
					}
				}
			} );
		}
	}

	function initSmartmenu() {
		if ( typeof $.fn.smartmenus !== 'function' ) {
			return;
		}

		var $primaryMenu = $pageHeader.find( '.sm.sm-simple' );

		$primaryMenu.smartmenus( {
			showTimeout: 0,
			hideTimeout: 150,
		} );

		// Add animation for sub menu.
		$primaryMenu.on( {
			'show.smapi': function( e, menu ) {
				var $thisMenu = $( menu );
				$thisMenu.removeClass( 'hide-animation' ).addClass( 'show-animation' );

				if ( ! $thisMenu.hasClass( 'menu-loaded' ) ) {
					// Update Swiper Size.
					$thisMenu.find( '.tm-swiper' ).each( function() {
						var swiper = $( this )
							.children( '.swiper-inner' )
							.children( '.swiper' )[0].swiper;
						swiper.update();
					} );

					// Update Grid Layout.
					if ( $.fn.MinimogGridLayout ) {
						$thisMenu.find( '.minimog-grid-wrapper' ).MinimogGridLayout( 'updateLayout' );
					}

					$thisMenu.addClass( 'menu-loaded' );
				}
			},
			'hide.smapi': function( e, menu ) {
				$( menu ).removeClass( 'show-animation' ).addClass( 'hide-animation' );
			}
		} ).on( 'animationend webkitAnimationEnd oanimationend MSAnimationEnd', 'ul', function( e ) {
			$( this ).removeClass( 'show-animation hide-animation' );
			e.stopPropagation();
		} );
	}

	function openMobileMenu( $mobileMenu ) {
		$body.addClass( 'page-mobile-menu-opened' );

		Helpers.setBodyOverflow();

		$( document ).trigger( 'mobileMenuOpen' );

		$mobileMenu.attr( 'aria-hidden', 'false' ).prop( 'hidden', false );
	}

	function closeMobileMenu( $mobileMenu ) {
		$body.removeClass( 'page-mobile-menu-opened' );

		Helpers.unsetBodyOverflow();

		$( document ).trigger( 'mobileMenuClose' );

		$mobileMenu.attr( 'aria-hidden', 'true' ).prop( 'hidden', true );
	}

	function calMobileMenuBreakpoint() {
		var menuBreakpoint = parseInt( $minimog.mobile_menu_breakpoint );

		if ( wWidth <= menuBreakpoint ) {
			$body.removeClass( 'primary-nav-rendering' ).removeClass( 'desktop-menu' ).addClass( 'mobile-menu' );
		} else {
			$body.removeClass( 'primary-nav-rendering' ).addClass( 'desktop-menu' ).removeClass( 'mobile-menu' );
		}
	}

	function initMobileMenu() {
		var $btnOpenMobileMenu = $( '#page-open-mobile-menu' );
		var duration           = 300;

		if ( $btnOpenMobileMenu.length > 0 ) {
			var settings = $btnOpenMobileMenu.data( 'menu-settings' );

			var animation = settings.animation ? settings.animation : 'slide';
			var direction = settings.direction ? settings.direction : 'left';

			$body.addClass( 'mobile-menu-' + animation + '-to-' + direction );
		}

		var $mobileMenu     = $( '#page-mobile-main-menu' ),
		    $tabContentWrap = $( '.mobile-menu-nav-menus' ),
		    $menu           = $mobileMenu.find( '.menu__container' );

		if ( $.fn.perfectScrollbar && ! Helpers.isHandheld() ) {
			$mobileMenu.find( '.page-mobile-menu-content' ).perfectScrollbar();
		}

		$btnOpenMobileMenu.on( 'click', function( e ) {
			e.preventDefault();
			e.stopPropagation();

			openMobileMenu( $mobileMenu );
		} );

		$mobileMenu.on( 'click', '.tm-button', function() {
			closeMobileMenu( $mobileMenu );
		} );

		$mobileMenu.on( 'click', '#page-close-mobile-menu', function( e ) {
			e.preventDefault();
			e.stopPropagation();

			closeMobileMenu( $mobileMenu );
		} );

		$mobileMenu.on( 'click', function( e ) {
			if ( e.target !== this ) {
				return;
			}

			closeMobileMenu( $mobileMenu );
		} );

		$mobileMenu.on( 'click', '.mobile-nav-tabs li', function( evt ) {
			var $thisTab = $( this );

			if ( $thisTab.hasClass( 'active' ) ) {
				return;
			}

			$thisTab.siblings().removeClass( 'active' ).attr( 'aria-selected', 'false' ).attr( 'tabindex', '-1' );
			$thisTab.addClass( 'active' ).attr( 'aria-selected', 'true' ).attr( 'tabindex', '0' );

			var $thisTabContent = $tabContentWrap.children( '#' + $thisTab.attr( 'aria-controls' ) );

			$thisTabContent.siblings().attr( 'aria-expanded', false ).prop( 'hidden', true );
			$thisTabContent.attr( 'aria-expanded', true ).prop( 'hidden', false );
		} );

		$menu.on( 'click', '.toggle-sub-menu', function( evt ) {
			var $li = $( this ).parents( 'li' ).first();

			evt.preventDefault();
			evt.stopPropagation();

			var _friends = $li.siblings( '.opened' );
			_friends.removeClass( 'opened' );
			_friends.find( '.opened' ).removeClass( 'opened' );
			_friends.find( '.sub-menu' ).stop().slideUp( duration );

			if ( $li.hasClass( 'opened' ) ) {
				$li.removeClass( 'opened' );
				$li.find( '.opened' ).removeClass( 'opened' );
				$li.find( '.sub-menu' ).stop().slideUp( duration );
			} else {
				$li.addClass( 'opened' );
				var $subMenu = $li.children( '.sub-menu' );
				$subMenu.stop().slideDown( duration, function() {
					// Need wait for animation end to make ll image working properly.
					if ( ! $subMenu.hasClass( 'menu-loaded' ) ) {
						// Update Swiper Size.
						$subMenu.find( '.tm-swiper' ).each( function() {
							var swiper = $( this )
								.children( '.swiper-inner' )
								.children( '.swiper' )[0].swiper;
							swiper.update();
						} );

						// Update Grid Layout.
						if ( $.fn.MinimogGridLayout ) {
							$subMenu.find( '.minimog-grid-wrapper' ).MinimogGridLayout( 'updateLayout' );
						}

						$subMenu.addClass( 'menu-loaded' );
					}
				} );
			}
		} );
	}

	function initSplitNavHeader() {
		if ( 0 >= $headerInner.length || 1 !== $headerInner.data( 'centered-logo' ) ) {
			return;
		}

		var $navigation = $headerInner.find( '#page-navigation' ),
		    $navItems   = $navigation.find( '#menu-primary > li' ),
		    $logo       = $headerInner.find( '.branding__logo img.logo' ),
		    itemsNumber = $navItems.length,
		    isRTL       = $body.hasClass( 'rtl' ),
		    midIndex    = parseInt( itemsNumber / 2 + .5 * isRTL - .5 ),
		    $midItem    = $navItems.eq( midIndex ),
		    rule        = isRTL ? 'marginLeft' : 'marginRight';

		var recalc = function() {
			var logoWidth  = $logo.outerWidth(),
			    logoHeight = $logo.closest( '.branding__logo' ).height(),
			    leftWidth  = 0,
			    rightWidth = 0;

			$logo.closest( '.header-content-inner' ).css( 'min-height', logoHeight + 'px' );

			for ( var i = itemsNumber - 1; i >= 0; i -- ) {
				var itemWidth = $navItems.eq( i ).outerWidth();

				if ( i > midIndex ) {
					rightWidth += itemWidth;
				} else {
					leftWidth += itemWidth;
				}
			}

			var diff = leftWidth - rightWidth;

			if ( isRTL ) {
				if ( leftWidth > rightWidth ) {
					$navigation.find( '#menu-primary > li:first-child' ).css( 'marginRight', - diff );
				} else {
					$navigation.find( '#menu-primary > li:last-child' ).css( 'marginLeft', diff );
				}
			} else {
				if ( leftWidth > rightWidth ) {
					$navigation.find( '#menu-primary > li:last-child' ).css( 'marginRight', diff );
				} else {
					$navigation.find( '#menu-primary > li:first-child' ).css( 'marginLeft', - diff );
				}
			}

			$midItem.css( rule, logoWidth + 66 );
		};

		recalc();

		$logo.on( 'loaded', function() {
			setTimeout( function() {
				recalc();
				$navigation.addClass( 'menu-calculated' );
			}, 100 ); // Delay 100 wait for logo rendered.
		} );

		$window.on( 'hresize', recalc );
	}

	function initStickyHeader() {
		if ( $minimog.header_sticky_enable != 1 || 0 >= $pageHeader.length || $headerInner.data( 'sticky' ) != '1' ) {
			return;
		}

		var $headerHolder = $pageHeader.children( '.page-header-place-holder' ),
		    offset        = $headerInner.offset().top,
		    _hHeight      = $headerInner.outerHeight(),
		    ACTIVE_CLASS  = 'header-pinned',
		    lastST        = 0,
		    isPinned      = false,
		    stickyTimer   = null;

		// Fix offset top return negative value on some devices.
		if ( offset < 0 ) {
			offset = 0;
		}

		offset += _hHeight;

		if ( ! $pageHeader.hasClass( 'header-layout-fixed' ) ) {
			$headerHolder.height( _hHeight );
			$headerInner.addClass( 'held' );

			$window.on( 'hresize', function() {
				var _hHeight = $headerInner.outerHeight();

				$headerHolder.height( _hHeight );
			} );
		}

		$window.on( 'scroll', function() {
			var currentST = $( this ).scrollTop();

			clearTimeout( stickyTimer );

			if ( currentST <= offset ) { // When on top remove sticky without delay.
				isPinned = false;
				$pageHeader.removeClass( ACTIVE_CLASS );
				setStickyHeight( 0 );
			} else {
				switch ( $minimog.header_sticky_type ) {
					case 'up':
						if ( currentST < lastST ) {
							if ( !isPinned ) {
								toggleSticky();
							}
						} else { // Scroll down
							if ( isPinned ) {
								toggleSticky();
							}
						}
						break;
					case 'both':
						if ( !isPinned ) {
							toggleSticky();
						}
						break;
				}
			}

			lastST = currentST;
		} );

		function toggleSticky() {
			stickyTimer = setTimeout( function() {
				if ( ! isPinned ) {
					isPinned = true;
					$pageHeader.addClass( ACTIVE_CLASS );
					$pageHeader.css( '--logo-sticky-height', $pageHeader.find( '.branding__logo' )
					                                                    .height() + 'px' );
					setStickyHeight( $headerInner.outerHeight() );
				} else {
					isPinned = false;
					$pageHeader.removeClass( ACTIVE_CLASS );
					setStickyHeight( 0 );
				}
			}, 200 );
		}

		function setStickyHeight( stickyHeight ) {
			document.body.style.setProperty( '--header-sticky-height', stickyHeight + 'px' );
		}
	}

	function initSearchPopup() {
		var settings = $minimog.search,
		    delay    = parseInt( settings.delay ),
			isAjaxSearch = ! settings.hasOwnProperty( 'ajax_disable' ) || settings.ajax_disable != '1';

		delay = ! isNaN( delay ) ? delay : 1000;

		var $popupSearch     = $( '#popup-search' ),
		    $popupSearchForm = $popupSearch.find( '.search-form' ),
			$viewAllResultsBtn = $('#btn-view-all-search-results'),
		    $dropdownCat     = $popupSearchForm.find( '.search-select' );

		// Use Select2 enhancement if possible.
		if ( $.fn.selectWoo ) {
			$dropdownCat.selectWoo( {
				dropdownAutoWidth: true,
				dropdownCssClass: 'select2-dropdown-search-cat'
			} );
		}

		$viewAllResultsBtn.on('click', function () {
			$popupSearchForm.trigger('submit');
		});

		$( document.body ).on( 'click', '.page-open-popup-search', function( evt ) {
			evt.preventDefault();

			openSearch();
		} );

		if( isAjaxSearch ) {
			$pageHeader.on( 'mousedown', '#placeholder_cat_dropdown', function( evt ) {
				evt.preventDefault();

				openSearch( 'cat' );
			} );

			$pageHeader.on( 'focus', '.search-field', function( evt ) {
				evt.preventDefault();

				openSearch();
			} );

			$pageHeader.on( 'click', '.search-submit', function( evt ) {
				evt.preventDefault();

				openSearch();
			} );
		}

		$popupSearchForm.on( 'change', '.search-select', function( evt ) {
			evt.preventDefault();

			var selectedOptionText = $( this ).children( 'option' ).filter( ':selected' ).text();

			$pageHeader.find( '.search-select' ).children( 'option' ).html( selectedOptionText );

			openSearch();
		} );

		$( '#search-popup-close' ).on( 'click', function( evt ) {
			evt.preventDefault();

			closeSearch();
		} );

		$popupSearch.on( 'click', function( evt ) {
			if ( evt.target !== this ) {
				return;
			}

			closeSearch();
		} );

		var searching = null;

		$popupSearchForm.on( 'focus', '.search-field', function() {
			$popupSearchForm.addClass( 'search-field-focused' );
		} );

		$popupSearchForm.on( 'blur', '.search-field', function() {
			$popupSearchForm.removeClass( 'search-field-focused' );
		} );

		if ( isAjaxSearch ) {
			$popupSearchForm.on( 'keyup', '.search-field', function() {
				var $field = $( this );

				clearTimeout( searching );
				searching = setTimeout( function() {
					if ( $field.val().length > 2 ) {
						doSearch();
					} else {
						clearSearch();
					}
				}, delay );
			} );
		}

		function doSearch() {
			var formData   = $popupSearchForm.serializeArray(),
			    searchTerm = '';

			formData.forEach( function( item, index, array ) {
				if ( 's' === item.name ) {
					searchTerm = item.value;

					return false;
				}
			} );

			if ( '' === searchTerm ) {
				$popupSearch.find( '.popup-search-results' ).hide();
				$popupSearch.find( '.row-popular-search-keywords' ).show();
				return;
			}

			$.ajax( {
				url: Helpers.getAjaxUrl( 'minimog_search_products' ),
				type: 'GET',
				data: $.param( formData ),
				dataType: 'json',
				cache: true,
				success: function( response ) {
					$popupSearch.find( '.row-popular-search-keywords' ).hide();
					$popupSearch.find( '.popup-search-results' ).show();
					$popupSearch.find( '.popup-search-current' ).text( searchTerm );

					var $grid = $popupSearch.find( '.minimog-grid' );
					$grid.html( response.data.template );

					response.data.template !== '' ? $viewAllResultsBtn.show() : $viewAllResultsBtn.hide();
				},
				beforeSend: function() {
					$popupSearch.removeClass( 'loaded' ).addClass( 'loading' );
				},
				complete: function() {
					$popupSearch.removeClass( 'loading' ).addClass( 'loaded' );
				},
			} );
		}

		function clearSearch() {
			$popupSearch.find( '.popup-search-results' ).hide();
			$popupSearch.find( '.row-popular-search-keywords' ).show();
		}

		function openSearch( focus = '' ) {
			Helpers.setBodyOverflow();
			$popupSearch.addClass( 'open' ).attr( 'aria-hidden', 'false' ).prop( 'hidden', false );

			setTimeout( function() {
				if ( 'cat' === focus ) {
					$popupSearch.find( '.search-select' ).select2( 'open' );
				} else {
					$popupSearch.find( '.search-field' ).trigger( 'focus' );
				}
			}, 300 );

			if ( $.fn.perfectScrollbar && ! Helpers.isHandheld() ) {
				$popupSearch.children( '.inner' ).perfectScrollbar();
			}
		}

		function closeSearch() {
			Helpers.unsetBodyOverflow();
			$popupSearch.removeClass( 'open' ).attr( 'aria-hidden', 'true' ).prop( 'hidden', true );
		}
	}

	function initTopBarCollapsible() {
		if ( 0 >= $pageTopBar.length ) {
			return;
		}

		var ACTIVE_CLASS   = 'expanded';
		var $topbarContent = $pageTopBar.find( '.top-bar-section' );
		var topbarLineHeight = 31;

		if ( $topbarContent.outerHeight() > topbarLineHeight ) {
			$pageTopBar.addClass( 'top-bar-has-collapsible-toggle' );
		}

		$pageTopBar.on( 'click', '#top-bar-collapsible-toggle', function() {
			if ( $pageTopBar.hasClass( ACTIVE_CLASS ) ) {
				$pageTopBar.removeClass( ACTIVE_CLASS );
				$pageTopBar.find( '.top-bar-wrap' ).css( { height: '26px' } );
			} else {
				$pageTopBar.addClass( ACTIVE_CLASS );
				$pageTopBar.find( '.top-bar-wrap' ).css( { height: $topbarContent.outerHeight() + 'px' } );
			}
		} );

		$window.on( 'hresize', function() {
			if ( $pageTopBar.hasClass( ACTIVE_CLASS ) ) {
				$pageTopBar.find( '.top-bar-wrap' ).css( { height: $topbarContent.outerHeight() + 'px' } );
			}
		} );
	}

	function initCookieNotice() {
		// Fix Nginx Redis Cache skip cookie.
		if ( typeof Storage !== 'undefined' && localStorage.getItem( 'minimog_cookie_accepted' ) === 'yes' ) {
			return;
		}

		var $cookiePopup = $( '#cookie-notice-popup' );

		if ( 0 >= $cookiePopup.length ) {
			return;
		}

		$cookiePopup.removeClass( 'close' ).addClass( 'show' );

		$cookiePopup.on( 'click', '#btn-accept-cookie', function() {
			$cookiePopup.removeClass( 'show' ).addClass( 'close' );

			var data = $.param( {
				action: 'minimog_cookie_accepted'
			} );

			$.ajax( {
				url: $minimog.ajaxurl,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function( response ) {
					if ( typeof Storage !== 'undefined' ) {
						localStorage.setItem( 'minimog_cookie_accepted', 'yes' );
					}
				}
			} );
		} );
	}

	function handlerLanguageSwitchers() {
		var $switcherInTopBar = $pageTopBar.find( '.switcher-language-wrapper' );
		if ( 0 < $switcherInTopBar.length ) {
			handlerLanguageSwitcher( $switcherInTopBar, 'topbar' );
		}

		var $switcherInHeader = $pageHeader.find( '.switcher-language-wrapper' );
		if ( 0 < $switcherInHeader.length ) {
			handlerLanguageSwitcher( $switcherInHeader, 'header' );
		}
	}

	function handlerLanguageSwitcher( $switcher, context = '' ) {
		var options = {}
		if ( $switcher.hasClass( 'polylang' ) ) {
			options = {
				toggle: '.current-lang'
			};
		} else if ( $switcher.hasClass( 'translate-press' ) ) {
			options = {
				toggle: '.trp-language-switcher',
				children: '.trp-ls-shortcode-language'
			};
		} else if ( $switcher.hasClass( 'wpml' ) ) {
			options = {
				toggle: '.wpml-ls-current-language'
			}
		}

		if ( ! options.hasOwnProperty( 'toggle' ) ) { // Maybe unknown switcher.
			return;
		}

		switch ( context ) {
		case 'header':
			handlerHeaderSubMenuAlignment( $switcher, options );
			break;
		case 'topbar':
			handlerTopBarSubMenuAlignment( $switcher, options );
			break;
		}

		// Disable click on active item on mobile to avoid refresh page without open sub menu.
		if ( Helpers.isHandheld() ) {
			$switcher.on( 'click touch', options.toggle + ' > a', function( evt ) {
				evt.preventDefault();
			} );
		}
	}

	function handlerCurrencySwitchers() {
		var $switcherInTopBar = $pageTopBar.find( '.currency-switcher-menu-wrap' );
		if ( 0 < $switcherInTopBar.length ) {
			handlerCurrencySwitcher( $switcherInTopBar, 'topbar' );
		}

		var $switcherInHeader = $pageHeader.find( '.currency-switcher-menu-wrap' );
		if ( 0 < $switcherInHeader.length ) {
			handlerCurrencySwitcher( $switcherInHeader, 'header' );
		}
	}

	function handlerCurrencySwitcher( $switcher, context = '' ) {
		var options = {}

		if ( $switcher.hasClass( 'curcy' ) || $switcher.hasClass( 'wc-pay' ) ) {
			options = {
				toggle: '.menu-item-has-children'
			}
		} else if ( $switcher.hasClass( 'wcml' ) ) {
			options = {
				toggle: '.wcml-cs-active-currency'
			}
		} else if ( $switcher.hasClass( 'aelia' ) ) {
			options = {
				toggle: '.dropdown_selector',
				children: '.dropdown'
			}
		}

		if ( ! options.hasOwnProperty( 'toggle' ) ) { // Maybe unknown switcher.
			return;
		}

		switch ( context ) {
		case 'header':
			handlerHeaderSubMenuAlignment( $switcher, options );
			break;
		case 'topbar':
			handlerTopBarSubMenuAlignment( $switcher, options );
			break;
		}

		// Disable click on active item on mobile to avoid refresh page without open sub menu.
		if ( Helpers.isHandheld() ) {
			$switcher.on( 'click touch', options.toggle + ' > a', function( evt ) {
				evt.preventDefault();
			} );
		}
	}

	function handlerTopBarSubMenus() {
		var $topBarMenus = $pageTopBar.find( '.menu' );

		if ( 0 < $topBarMenus.length ) {
			$topBarMenus.on( 'mouseenter', '.menu-item-has-children', function() {
				handlerHeaderSubMenuAlignment( $( this ) );
			} );
		}
	}

	function handlerHeaderSubMenuAlignment( $menuContainer, args = {} ) {
		let options = $.extend( true, {}, {
			children: 'ul',
			offset: 10, // Gap from right side
			appendClass: 'hover-back'
		}, args );

		$menuContainer.on( 'mouseenter', options.toggle, function() {
			var $listItem = $( this ),
			    docWidth  = $( document ).width();

			$listItem.children( options.children ).each( function() {
				var $subMenu = $( this );
				$subMenu.removeClass( options.appendClass );
				if ( ($subMenu.offset().left + $subMenu.outerWidth() + options.offset) >= docWidth ) {
					$subMenu.addClass( options.appendClass );
				}
			} );
		} );
	}

	/**
	 * Sub menu in top bar set fixed then need to calculate top left for them.
	 * @param $menuContainer
	 * @param args
	 */
	function handlerTopBarSubMenuAlignment( $menuContainer, args = {} ) {
		let docWidth = $( document ).width(),
		    options  = $.extend( true, {}, {
			    children: 'ul',
		    }, args );


		var $listItem = $menuContainer.find( options.toggle );
		if ( 0 >= $listItem.length ) {
			return;
		}

		calculatePosition();

		$menuContainer.on( 'click touch mouseenter', options.toggle, function() {
			calculatePosition();
		} );

		function calculatePosition() {
			var $subMenu   = $listItem.children( options.children ),
			    itemOffset = $listItem.offset(),
			    subMenuY   = itemOffset.top + $listItem.outerHeight();

			$subMenu.css( {
				top: subMenuY,
			} );

			if ( itemOffset.left + $subMenu.outerWidth() >= docWidth ) {
				$subMenu.css( {
					left: 'auto',
					right: docWidth - (itemOffset.left + $listItem.outerWidth()),
				} );
			} else {
				$subMenu.css( {
					left: itemOffset.left,
					right: 'auto'
				} );
			}
		}
	}

	function handlerVerticalCategoryMenu() {
		var $wrapper = $( '#header-categories-nav' );

		if ( 0 >= $wrapper.length ) {
			return;
		}

		var FIXED_NAV_CLASS = 'categories-nav-fixed';

		$wrapper.on( 'mouseenter', function() {
			$wrapper.removeClass( 'hide-animation' ).addClass( 'show-animation' );
		} ).on( 'mouseleave', function() {
			if ( $wrapper.hasClass( FIXED_NAV_CLASS ) && ! $pageHeader.hasClass( 'header-pinned' ) ) {
				$wrapper.removeClass( 'show-animation hide-animation' );
			} else {
				$wrapper.removeClass( 'show-animation' ).addClass( 'hide-animation' );
			}
		} ).on( 'animationend webkitAnimationEnd oanimationend MSAnimationEnd', 'nav', function( evt ) {
			evt.stopPropagation();
			$wrapper.removeClass( 'hide-animation' );
		} );
	}
}( jQuery ));
