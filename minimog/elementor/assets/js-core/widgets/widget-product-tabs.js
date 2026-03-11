(
	function ($) {
		'use strict'

		var MinimogProductTabs = function ($scope, $) {
			$scope.find('.minimog-tabs').each(function () {
				var $tabPanel   = $(this),
					tabSettings = {}

				$tabPanel.children('.minimog-tabs__content').children('.tab-content.active').each(function () {
					var $thisTab = $(this)

					$thisTab.children().children('.tm-swiper').each(function () {
						var $component = $(this)

						$component.MinimogSwiper()

						if ($component.hasClass('group-style-10')) {
							/**
							 * Need to re-calculate because slide class visible maybe wrong when slides has drop shadow (margin, padding)
							 */
							setTimeout(function () {
								var swiper = $component.find('.swiper')[ 0 ].swiper
								swiper.update()
							}, 200)
						}
					})
				})

				if ($tabPanel.hasClass('minimog-tabs--nav-type-dropdown')) {
					tabSettings.navType = 'dropdown'
				}

				$tabPanel.MinimogTabPanel(tabSettings)
			})

			$(document.body).on('MinimogTabChange', function (e, $tabPanel, $newTabContent) {
				if (!$newTabContent.hasClass('ajax-loaded')) {
					loadProductData($tabPanel, $newTabContent)

					$newTabContent.addClass('ajax-loaded')
				}
			})

			function loadProductData ($tabPanel, $currentTab) {
				var $tabContentPlaceholder = $tabPanel.find('.tab-content-placeholder'),
					$component             = $currentTab.find('.tm-tab-product-element'),
					layout                 = $currentTab.data('layout'),
					query                  = $currentTab.data('query')

				if ($tabContentPlaceholder) {
					if ('grid' === layout) {
						$component.html($tabContentPlaceholder.html())
					} else {
						$component.find('.swiper-wrapper').html($tabContentPlaceholder.html())
					}
				}

				query.action = 'get_product_tabs'

				$.ajax({
					url: $minimog.ajaxurl,
					type: 'GET',
					data: query,
					dataType: 'json',
					cache: true,
					success: function (response) {
						var result = response.data

						if (!result.found) {
							$component.remove()
							var message = document.createElement('div')
							message.classList.add('minimog-grid-response-messages')
							message.innerHTML = result.template
							$currentTab.children('.tab-content-wrapper').append(message)
						} else {
							if ('grid' === layout) {
								var $grid = $component.children('.minimog-grid')
								$grid.empty().html(result.template)
							} else {
								$component.find('.swiper-wrapper').html(result.template)
								$component.MinimogSwiper()
							}
						}
					}
				})
			}
		}

		$(window).on('elementor/frontend/init', function () {
			elementorFrontend.hooks.addAction('frontend/element_ready/tm-product-tabs.default', MinimogProductTabs)
			elementorFrontend.hooks.addAction('frontend/element_ready/tm-carousel-product-tabs.default', MinimogProductTabs)
		})
	}
)(jQuery)
