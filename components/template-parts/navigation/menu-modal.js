/**
 * Unified Menu Modal JavaScript
 * Handles both global menus (meta-nav) and local menus (website)
 */

// Localized strings are available via fauElementalMenuModal.strings

( function ( $ ) {
	'use strict';

	// Safety check for localized strings
	const strings =
		typeof window.fauElementalMenuModal !== 'undefined' &&
		window.fauElementalMenuModal.strings
			? window.fauElementalMenuModal.strings
			: {
					overview: '',
					menuOpened: '',
					menuClosed: '',
					navigatedTo: '',
					submenu: '',
					submenuCollapsed: '',
					submenuExpanded: '',
					navigatedBack: '',
					movedToLastMenuItem: '',
					movedToCloseButton: '',
					menuBreadcrumbs: '',
					goTo: '',
			  };

	class MenuModal {
		constructor() {
			this.currentModal = null;
			this.previouslyFocused = null;
			this.closeTimeout = null;
			this.domCache = new Map(); // Cache for DOM query results
			this.cacheVersion = 0; // Increment when DOM structure changes
			this.pendingTimeouts = new Set(); // Track all pending timeouts for cleanup
			this.scrollbarWidth = 0; // Cache scrollbar width
			this.init();
		}

		init() {
			this.bindEvents();
			this.setupAccessibility();
			this.bindResizeEvents();
		}

		/**
		 * Calculate the scrollbar width dynamically
		 * @return {number} The scrollbar width in pixels
		 */
		calculateScrollbarWidth() {
			// Create a temporary div to measure scrollbar width
			const outer = document.createElement( 'div' );
			outer.style.visibility = 'hidden';
			outer.style.overflow = 'scroll';
			outer.style.msOverflowStyle = 'scrollbar'; // Needed for IE
			document.body.appendChild( outer );

			const inner = document.createElement( 'div' );
			outer.appendChild( inner );

			const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
			document.body.removeChild( outer );

			return scrollbarWidth;
		}

		/**
		 * Apply scrollbar width compensation to prevent layout shift
		 */
		applyScrollbarCompensation() {
			if ( this.scrollbarWidth === 0 ) {
				this.scrollbarWidth = this.calculateScrollbarWidth();
			}

			const body = document.body;
			const scrollbarWidth = this.scrollbarWidth;

			// Apply padding-right to body
			body.style.paddingRight = `${ scrollbarWidth }px`;

			// Apply compensation to specific elements based on screen size
			const siteHeaderTopWrapper = document.querySelector(
				'.site-header .site-header-top__wrapper'
			);
			const fauNavigation = document.querySelector(
				'.site-header__top .fau-navigation'
			);
			const siteHeaderMain = document.querySelector(
				'.home:not(.blog).has-hero-block .site-header__main'
			);

			// Apply max-width compensation for large screens
			if ( window.innerWidth >= 1816 && siteHeaderTopWrapper ) {
				siteHeaderTopWrapper.style.maxWidth = `calc(1800px - ${ scrollbarWidth }px)`;
			}

			// Apply margin-right compensation for medium screens
			if (
				window.innerWidth <= 1815 &&
				window.innerWidth >= 600 &&
				fauNavigation
			) {
				fauNavigation.style.marginRight = `${ scrollbarWidth }px`;
			}

			// Apply transform compensation for hero blocks on large screens
			if ( window.innerWidth >= 1920 && siteHeaderMain ) {
				siteHeaderMain.style.transform = `translateX(calc(-50% - ${
					scrollbarWidth / 2
				}px))`;
			}
		}

		/**
		 * Remove scrollbar width compensation
		 */
		removeScrollbarCompensation() {
			const body = document.body;

			// Remove padding-right from body
			body.style.paddingRight = '';

			// Reset specific elements
			const siteHeaderTopWrapper = document.querySelector(
				'.site-header .site-header-top__wrapper'
			);
			const fauNavigation = document.querySelector(
				'.site-header__top .fau-navigation'
			);
			const siteHeaderMain = document.querySelector(
				'.home:not(.blog).has-hero-block .site-header__main'
			);

			if ( siteHeaderTopWrapper ) {
				siteHeaderTopWrapper.style.maxWidth = '';
			}

			if ( fauNavigation ) {
				fauNavigation.style.marginRight = '';
			}

			if ( siteHeaderMain ) {
				siteHeaderMain.style.transform = '';
			}
		}

		/**
		 * Bind resize events to recalculate scrollbar width
		 */
		bindResizeEvents() {
			let resizeTimeout;

			$( window ).on( 'resize.menu-modal', () => {
				// Debounce resize events
				clearTimeout( resizeTimeout );
				resizeTimeout = setTimeout( () => {
					// Reset scrollbar width cache to force recalculation
					this.scrollbarWidth = 0;

					// If modal is open, reapply compensation with new width
					if ( this.currentModal ) {
						this.applyScrollbarCompensation();
					}
				}, 100 );
			} );
		}

		/**
		 * Cleanup resize events
		 */
		cleanupResizeEvents() {
			$( window ).off( 'resize.menu-modal' );
		}

		// Cache key generator for DOM queries
		getCacheKey( modalId, queryType, additionalParams = '' ) {
			return `${ modalId }-${ queryType }-${ this.cacheVersion }-${ additionalParams }`;
		}

		// Invalidate cache when DOM structure changes
		invalidateCache() {
			this.cacheVersion++;
			this.domCache.clear();
		}

		// Cached DOM query with automatic invalidation
		cachedQuery( $modal, selector, cacheKey ) {
			if ( this.domCache.has( cacheKey ) ) {
				return this.domCache.get( cacheKey );
			}

			const result = $modal.find( selector );
			this.domCache.set( cacheKey, result );
			return result;
		}

		// Safe timeout creation with cleanup tracking
		safeSetTimeout( callback, delay ) {
			const timeoutId = setTimeout( () => {
				this.pendingTimeouts.delete( timeoutId );
				callback();
			}, delay );
			this.pendingTimeouts.add( timeoutId );
			return timeoutId;
		}

		// Clear all pending timeouts
		clearAllTimeouts() {
			this.pendingTimeouts.forEach( ( timeoutId ) => {
				clearTimeout( timeoutId );
			} );
			this.pendingTimeouts.clear();
		}

		// HTML escaping function to prevent XSS
		escapeHtml( text ) {
			if ( ! text ) {
				return '';
			}
			const div = document.createElement( 'div' );
			div.textContent = text;
			return div.innerHTML;
		}

		isHierarchyMenu( $modal ) {
			// Only the structure menu should show breadcrumbs
			// Check for the hierarchy-specific menu class
			return $modal.find( '.menu-meta-nav__menu--hierarchy' ).length > 0;
		}

		generateBreadcrumbs( navigationStack ) {
			if ( navigationStack.length === 0 ) {
				return '';
			}

			let breadcrumbsHtml = `<nav class="menu-modal__breadcrumbs" aria-label="${ strings.menuBreadcrumbs }"><ol>`;

			navigationStack.forEach( ( item, index ) => {
				const isLast = index === navigationStack.length - 1;
				const levelClass = `breadcrumb-level-${ index }`;
				const safeParentTitle = this.escapeHtml( item.parentTitle );

				if ( isLast ) {
					breadcrumbsHtml += `<li class="breadcrumb-item ${ levelClass } current" aria-current="location">${ safeParentTitle }</li>`;
				} else {
					breadcrumbsHtml += `<li class="breadcrumb-item ${ levelClass }">`;
					breadcrumbsHtml += `<a class="breadcrumb-link" data-level="${ index }" aria-label="${ strings.goTo } ${ safeParentTitle }">${ safeParentTitle }</a>`;
					breadcrumbsHtml += '</li>';
				}
			} );

			breadcrumbsHtml += '</ol></nav>';
			return breadcrumbsHtml;
		}

		bindEvents() {
			$( document ).on( 'click', '[data-modal-target]', ( e ) => {
				e.preventDefault();
				const modalId = $( e.currentTarget ).data( 'modal-target' );
				const targetItemId = $( e.currentTarget ).data(
					'target-item-id'
				);
				const targetUrl = $( e.currentTarget ).data( 'target-url' );
				this.openModal( modalId, targetItemId, targetUrl );
			} );

			// Handle search form submission in search modal
			$( document ).on(
				'submit',
				'#search-modal .fau-global-search',
				( e ) => {
					this.handleSearchFormSubmission( e );
				}
			);

			$( document ).on(
				'click',
				'.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn',
				( e ) => {
					e.preventDefault();
					this.closeCurrentModal();
				}
			);

			$( document ).on(
				'click',
				'.menu-modal__overlay, .menu-meta-nav__modal__overlay, .menu-website-modal__overlay',
				() => {
					this.closeCurrentModal();
				}
			);

			$( document ).on(
				'click',
				'.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle',
				( e ) => {
					e.preventDefault();
					this.toggleSubmenu( $( e.currentTarget ) );
				}
			);

			$( document ).on(
				'click',
				'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn',
				( e ) => {
					e.preventDefault();
					this.navigateBack();
				}
			);

			$( document ).on( 'click', '.breadcrumb-link', ( e ) => {
				e.preventDefault();
				const targetLevel = parseInt(
					$( e.currentTarget ).data( 'level' )
				);
				this.navigateToBreadcrumbLevel( targetLevel );
			} );

			$( document ).on( 'keydown', ( e ) => {
				if ( e.key === 'Escape' && this.currentModal ) {
					this.closeCurrentModal();
				}
			} );
		}

		setupAccessibility() {
			$( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' ).attr(
				'aria-hidden',
				'true'
			);
		}

		openModal( modalId, targetItemId = null, targetUrl = null ) {
			let $modal = $( `#${ modalId }` );
			if ( ! $modal.length ) {
				$modal = $( `#${ modalId }-modal` );
			}
			if ( ! $modal.length ) {
				return;
			}

			this.previouslyFocused = $modal[ 0 ].ownerDocument.activeElement;

			if ( this.currentModal && this.currentModal.is( $modal ) ) {
				this.closeCurrentModal();
				return;
			}

			this.closeCurrentModal();

			// Clear all pending timeouts when opening a new modal
			this.clearAllTimeouts();

			this.currentModal = $modal;
			this.resetModalState( $modal );

			const $triggerButton = $( `[data-modal-target="${ modalId }"]` );
			if ( $triggerButton.length ) {
				$( '.menu-modal__open-btn' )
					.removeClass( 'is-active' )
					.attr( 'aria-expanded', 'false' );
				$triggerButton
					.addClass( 'is-active' )
					.attr( 'aria-expanded', 'true' );
			}

			$modal
				.removeClass( 'u-hidden' )
				.addClass( 'is-open' )
				.attr( 'aria-hidden', 'false' );
			$( 'body' ).addClass( 'modal-open' );

			// Apply scrollbar width compensation to prevent layout shift
			this.applyScrollbarCompensation();

			this.announce( strings.menuOpened );

			if ( targetItemId || targetUrl ) {
				this.navigateToItem( $modal, targetItemId, targetUrl );
			} else {
				this.navigateToCurrentPage( $modal );
			}

			this.trapFocus( $modal );

			this.safeSetTimeout( () => {
				const $closeButton = $modal
					.find(
						'.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn'
					)
					.first();
				if ( $closeButton.length ) {
					$closeButton.focus();
				} else {
					$modal.attr( 'tabindex', '-1' ).focus();
				}

				this.updateFocusTrap( $modal );
			}, 150 );
		}

		navigateToItem( $modal, targetItemId, targetUrl ) {
			let $targetItem = $modal
				.find( `li[data-menu-item-id="${ targetItemId }"]` )
				.first();

			if ( ! $targetItem.length ) {
				$targetItem = $modal
					.find( 'li[data-menu-url]' )
					.filter( function () {
						const itemUrl = $( this ).attr( 'data-menu-url' );
						return (
							itemUrl &&
							itemUrl.replace( /\/$/, '' ) ===
								targetUrl.replace( /\/$/, '' )
						);
					} )
					.first();
			}

			if ( $targetItem.length ) {
				this.drillDownToItem( $modal, $targetItem );
			} else {
				this.navigateToCurrentPage( $modal );
			}
		}

		navigateToCurrentPage( $modal ) {
			const currentPath = window.location.pathname.replace( /\/$/, '' );
			const searchPaths =
				currentPath === ''
					? [ '/' ]
					: [ currentPath, currentPath + '/' ];

			let $currentItem = $();
			for ( const path of searchPaths ) {
				$currentItem = $modal
					.find( `[data-menu-url="${ path }"]` )
					.filter( function () {
						return $( this ).attr( 'data-menu-url' ) !== '';
					} )
					.first();
				if ( $currentItem.length ) {
					break;
				}
			}

			if ( $currentItem.length ) {
				this.drillDownToItem( $modal, $currentItem );
			} else {
				// Current page not found in menu - it might be hidden
				// Try to find the parent page and navigate to that level
				this.navigateToParentOfHiddenPage( $modal );
			}
		}

		/**
		 * Navigate to the parent page when the current page is hidden from menu
		 *
		 * @param {jQuery} $modal The modal element
		 */
		navigateToParentOfHiddenPage( $modal ) {
			// Look for the hidden element with parent information that was added by PHP
			const $parentInfo = $modal.find( '.current-page-parent-info' );

			if ( ! $parentInfo.length ) {
				return; // No parent info available
			}

			// Use the first parent info element (in case there are multiple)
			const $firstParentInfo = $parentInfo.first();
			const parentPageUrl = $firstParentInfo.data( 'parent-page-url' );
			const parentPageTitle =
				$firstParentInfo.data( 'parent-page-title' );

			if ( ! parentPageUrl || ! parentPageTitle ) {
				return; // Missing required data
			}

			// Try to find the parent page in the menu
			let $parentItem = $modal
				.find( `[data-menu-url="${ parentPageUrl }"]` )
				.filter( function () {
					return $( this ).attr( 'data-menu-url' ) !== '';
				} )
				.first();

			// If we found the parent page in the menu, navigate to it without highlighting
			if ( $parentItem.length ) {
				// Navigate to the parent level but don't highlight it
				// since the user is on a hidden page, not the parent page
				this.navigateToParentLevel( $modal, $parentItem );
				return;
			}

			// If parent page not found as a direct menu item, try to find it as a page child
			// This handles cases where the parent is a page child rather than a menu item
			$parentItem = $modal
				.find( `[data-menu-url="${ parentPageUrl }"]` )
				.filter( function () {
					return $( this ).attr( 'data-menu-url' ) !== '';
				} )
				.first();

			if ( $parentItem.length ) {
				// Find the parent of this page child to navigate to that level
				const $parentContainer = $parentItem
					.closest( '.sub-menu' )
					.prev( '.menu-modal__submenu-toggle' )
					.closest( '.menu-item' );
				if ( $parentContainer.length ) {
					const $toggle = $parentContainer.children(
						'.menu-modal__submenu-toggle'
					);
					if ( $toggle.length ) {
						this.performDrillDown(
							$modal,
							$parentContainer,
							$toggle
						);
						// Don't highlight the parent - user is on a hidden page
					}
				}
			}

			// If no parent found, just stay at the root level
			// This is the fallback behavior when we can't determine the hierarchy
		}

		/**
		 * Navigate to a parent level without highlighting the parent item
		 * Used when navigating to parent context for hidden pages
		 *
		 * @param {jQuery} $modal      The modal element
		 * @param {jQuery} $parentItem The parent item to navigate to
		 */
		navigateToParentLevel( $modal, $parentItem ) {
			// Get all parent levels up to this item
			const $parents = $parentItem
				.parents( '.menu-item' )
				.get()
				.reverse();

			// Navigate through each parent level without highlighting
			$parents.forEach( ( parentItem ) => {
				const $parent = $( parentItem );
				const $toggle = $parent.children(
					'.menu-modal__submenu-toggle'
				);
				if ( $toggle.length ) {
					this.performDrillDown( $modal, $parent, $toggle );
				}
			} );

			// Don't highlight the parent item - just show its submenu
			const $toggle = $parentItem.children(
				'.menu-modal__submenu-toggle'
			);
			const $submenu = $parentItem.children( '.sub-menu' );
			if ( $submenu.length && $toggle.length ) {
				this.performDrillDown( $modal, $parentItem, $toggle );
				// Don't highlight overview link either
			}
		}

		drillDownToItem( $modal, $targetItem ) {
			const $parents = $targetItem
				.parents( '.menu-item' )
				.get()
				.reverse();
			$parents.forEach( ( parentItem ) => {
				const $parent = $( parentItem );
				const $toggle = $parent.children(
					'.menu-modal__submenu-toggle'
				);
				if ( $toggle.length ) {
					this.performDrillDown( $modal, $parent, $toggle );
				}
			} );

			const $toggle = $targetItem.children(
				'.menu-modal__submenu-toggle'
			);
			const $submenu = $targetItem.children( '.sub-menu' );
			if ( $submenu.length && $toggle.length ) {
				this.performDrillDown( $modal, $targetItem, $toggle );
				this.highlightOverviewLink( $submenu );
			} else {
				this.highlightMenuItem( $targetItem );
			}

			if ( this.isHierarchyMenu( $modal ) ) {
				this.updateBreadcrumbs( $modal );
			}
		}

		performDrillDown( $modal, $parentLi, $toggle ) {
			const $submenu = $toggle.siblings( '.sub-menu' );
			if ( $submenu.length === 0 ) {
				return;
			}

			const isHierarchyMenu = this.isHierarchyMenu( $modal );
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			$parentLi.siblings().hide();
			$toggle.hide();

			if ( ! isHierarchyMenu ) {
				$modal.find( '.menu-modal__level-heading' ).remove();
				$toggle.after(
					`<h2 class="menu-modal__level-heading">${ $toggle.data(
						'parent-title'
					) }</h2>`
				);
			}

			const parentUrl = $toggle.data( 'parent-url' );
			const parentTitle = $toggle.data( 'parent-title' );
			if ( parentUrl && parentTitle && parentUrl !== '#' ) {
				$submenu.find( '.menu-item-overview' ).remove();
				const safeParentTitle = this.escapeHtml( parentTitle );
				$submenu.prepend(
					`<li class="menu-item menu-item-overview"><a href="${ parentUrl }">${ strings.overview } ${ safeParentTitle }</a></li>`
				);
			}

			$submenu.css( 'display', 'block' );
			$toggle.attr( 'aria-expanded', 'true' );

			navigationStack.push( {
				parentUl: $parentLi.parent(),
				parentLi: $parentLi,
				parentUrl,
				parentTitle,
			} );
			$modal.data( 'navigation-stack', navigationStack );

			$modal
				.find(
					'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
				)
				.removeClass( 'u-hidden' );

			// Invalidate cache due to DOM structure change
			this.invalidateCache();

			this.safeSetTimeout( () => {
				this.updateFocusTrap( $modal );
				this.announce(
					strings.navigatedTo + ` ${ parentTitle } ` + strings.submenu
				);
			}, 50 );
		}

		toggleSubmenu( $toggle ) {
			const $modal = $toggle.closest(
				'.menu-modal, .menu-meta-nav__modal, .menu-website-modal'
			);
			const $parentLi = $toggle.closest( '.menu-item' );

			if (
				this.isHierarchyMenu( $modal ) ||
				$toggle.hasClass( 'menu-modal__submenu-row' )
			) {
				this.performDrillDown( $modal, $parentLi, $toggle );

				if ( this.isHierarchyMenu( $modal ) ) {
					this.updateBreadcrumbs( $modal );
				}

				this.rehighlightCurrentPage( $modal );
			} else {
				this.toggleSubmenuSimple( $toggle );
			}
		}

		toggleSubmenuSimple( $toggle ) {
			const $submenu = $toggle.siblings( '.sub-menu' );
			const isExpanded = $toggle.attr( 'aria-expanded' ) === 'true';
			const parentTitle =
				$toggle.data( 'parent-title' ) ||
				$toggle.find( '.menu-modal__item-title' ).text();

			if ( isExpanded ) {
				$submenu.hide();
				$toggle
					.attr( 'aria-expanded', 'false' )
					.removeClass( 'expanded' );
				this.announce( `${ parentTitle } ` + strings.submenuCollapsed );
			} else {
				$submenu.show();
				$toggle.attr( 'aria-expanded', 'true' ).addClass( 'expanded' );
				this.announce( `${ parentTitle } ` + strings.submenuExpanded );

				this.safeSetTimeout( () => {
					this.updateFocusTrap(
						$toggle.closest(
							'.menu-modal, .menu-meta-nav__modal, .menu-website-modal'
						)
					);
				}, 50 );
			}
		}

		navigateBack() {
			if ( ! this.currentModal ) {
				return;
			}

			const $modal = this.currentModal;
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			if ( navigationStack.length === 0 ) {
				return;
			}

			const lastLevel = navigationStack.pop();
			$modal.data( 'navigation-stack', navigationStack );

			lastLevel.parentLi.siblings().show();
			lastLevel.parentLi.children( '.menu-modal__submenu-toggle' ).show();
			lastLevel.parentLi.children( '.sub-menu' ).hide();

			$modal.find( '.menu-modal__level-heading' ).remove();

			$modal.find( '.menu-item-heading' ).remove();

			if ( navigationStack.length === 0 ) {
				$modal
					.find(
						'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
					)
					.addClass( 'u-hidden' );
			}

			if ( this.isHierarchyMenu( $modal ) ) {
				this.updateBreadcrumbs( $modal );
			} else if ( navigationStack.length > 0 ) {
				// For non-hierarchy menus (like website menu with Mixed Navigation Walker),
				// restore the level heading for the current level
				const currentLevel =
					navigationStack[ navigationStack.length - 1 ];
				const $currentParentLi = currentLevel.parentLi;
				const $currentToggle = $currentParentLi.children(
					'.menu-modal__submenu-toggle'
				);

				if ( $currentToggle.length && currentLevel.parentTitle ) {
					const safeParentTitle = this.escapeHtml(
						currentLevel.parentTitle
					);
					$currentToggle.after(
						`<h2 class="menu-modal__level-heading">${ safeParentTitle }</h2>`
					);
				}
			}

			// Invalidate cache due to DOM structure change
			this.invalidateCache();

			this.rehighlightCurrentPage( $modal );
			this.announce( strings.navigatedBack );

			this.safeSetTimeout( () => {
				this.updateFocusTrap( $modal );
			}, 50 );
		}

		navigateToBreadcrumbLevel( targetLevel ) {
			if ( ! this.currentModal ) {
				return;
			}

			const $modal = this.currentModal;
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			while ( navigationStack.length > targetLevel + 1 ) {
				this.navigateBack();
			}

			this.rehighlightCurrentPage( $modal );
		}

		updateBreadcrumbs( $modal ) {
			if ( ! this.isHierarchyMenu( $modal ) ) {
				return;
			}

			const navigationStack = $modal.data( 'navigation-stack' ) || [];
			const $breadcrumbContainer = $modal.find(
				'.menu-modal__breadcrumbs'
			);
			const $levelHeading = $modal.find( '.menu-modal__level-heading' );

			if ( navigationStack.length === 0 ) {
				$breadcrumbContainer.remove();
				$levelHeading.remove();
				return;
			}

			const breadcrumbHtml = this.generateBreadcrumbs( navigationStack );
			const currentLevel = navigationStack[ navigationStack.length - 1 ];
			const safeParentTitle = this.escapeHtml( currentLevel.parentTitle );
			const levelHeadingHtml = `<h2 class="menu-modal__level-heading">${ safeParentTitle }</h2>`;

			// Remove existing breadcrumbs and level heading
			$breadcrumbContainer.remove();
			$levelHeading.remove();

			// Add breadcrumbs first
			$modal
				.find( '.menu-meta-nav__modal__content' )
				.prepend( breadcrumbHtml );

			// Add level heading inside the menu ul (as first item)
			$modal
				.find( '.menu-meta-nav__menu' )
				.prepend(
					`<li class="menu-item menu-item-heading">${ levelHeadingHtml }</li>`
				);
		}

		highlightOverviewLink( $submenu ) {
			if ( ! $submenu.length ) {
				return;
			}

			// Use more efficient traversal - only search within the submenu
			$submenu
				.find( '.current-menu-item-focused, .active' )
				.removeClass( 'current-menu-item-focused active' );

			const currentUrl = window.location.href;
			const $overviewLink = $submenu
				.find( '.menu-item-overview a' )
				.filter( function () {
					return this.href === currentUrl;
				} )
				.first();

			if ( $overviewLink.length ) {
				$overviewLink.addClass( 'current-menu-item-focused active' );
				$overviewLink[ 0 ].scrollIntoView( {
					behavior: 'smooth',
					block: 'center',
				} );
			}
		}

		rehighlightCurrentPage( $modal ) {
			// Check if we're dealing with a hidden page
			const $parentInfo = $modal.find( '.current-page-parent-info' );
			if ( $parentInfo.length ) {
				// We're on a hidden page - don't highlight anything
				// Just highlight overview links in visible submenus
				const cacheKey = this.getCacheKey(
					$modal.attr( 'id' ),
					'visible-submenus'
				);
				const $visibleSubmenus = this.cachedQuery(
					$modal,
					'.sub-menu:visible',
					cacheKey
				);

				$visibleSubmenus.each( ( _, submenu ) => {
					this.highlightOverviewLink( $( submenu ) );
				} );
				return; // Don't proceed with normal highlighting
			}

			const currentPath = window.location.pathname.replace( /\/$/, '' );
			const searchPaths =
				currentPath === ''
					? [ '/' ]
					: [ currentPath, currentPath + '/' ];

			// Use cached query for visible submenus
			const cacheKey = this.getCacheKey(
				$modal.attr( 'id' ),
				'visible-submenus'
			);
			const $visibleSubmenus = this.cachedQuery(
				$modal,
				'.sub-menu:visible',
				cacheKey
			);

			$visibleSubmenus.each( ( _, submenu ) => {
				this.highlightOverviewLink( $( submenu ) );
			} );

			// Use cached query for highlighted items
			const highlightedCacheKey = this.getCacheKey(
				$modal.attr( 'id' ),
				'highlighted-items'
			);
			const $highlighted = this.cachedQuery(
				$modal,
				'.current-menu-item-focused, .active',
				highlightedCacheKey
			);

			if ( ! $highlighted.length ) {
				let $currentItem = $();
				for ( const path of searchPaths ) {
					// Use cached query for visible menu items
					const menuItemsCacheKey = this.getCacheKey(
						$modal.attr( 'id' ),
						'visible-menu-items',
						path
					);
					$currentItem = this.cachedQuery(
						$modal,
						'.menu-item:visible',
						menuItemsCacheKey
					)
						.filter( function () {
							const itemUrl = $( this ).attr( 'data-menu-url' );
							return (
								itemUrl && itemUrl.replace( /\/$/, '' ) === path
							);
						} )
						.first();
					if ( $currentItem.length ) {
						break;
					}
				}

				if ( $currentItem.length ) {
					this.highlightMenuItem( $currentItem );
				} else {
					// Current page not found - it might be hidden
					// Try to highlight the parent page instead
					this.highlightParentOfHiddenPage( $modal );
				}
			}
		}

		/**
		 * Highlight the parent page when the current page is hidden from menu
		 *
		 * @param {jQuery} $modal The modal element
		 */
		highlightParentOfHiddenPage( $modal ) {
			// Look for the hidden element with parent information that was added by PHP
			const $parentInfo = $modal.find( '.current-page-parent-info' );
			if ( ! $parentInfo.length ) {
				return; // No parent info available
			}

			const parentPageUrl = $parentInfo.data( 'parent-page-url' );
			if ( ! parentPageUrl ) {
				return; // Missing required data
			}

			// Try to find the parent page in the menu
			$modal
				.find( `[data-menu-url="${ parentPageUrl }"]` )
				.filter( function () {
					return $( this ).attr( 'data-menu-url' ) !== '';
				} )
				.first();

			// Don't highlight the parent page - just navigate to its level
			// The user is on a hidden page, not the parent page
		}

		highlightMenuItem( $item ) {
			// Use more efficient traversal - find the modal through the item's parents
			const $modal = $item.closest(
				'.menu-modal, .menu-meta-nav__modal, .menu-website-modal'
			);

			// Only search within the modal, not the entire document
			$modal
				.find( '.current-menu-item-focused, .active' )
				.removeClass( 'current-menu-item-focused active' );

			const $link = $item.find( 'a' ).first();
			if ( $link.length ) {
				$link.addClass( 'current-menu-item-focused active' );
				$link[ 0 ].scrollIntoView( {
					behavior: 'smooth',
					block: 'center',
				} );
			} else {
				$item.addClass( 'current-menu-item-focused active' );
				$item[ 0 ].scrollIntoView( {
					behavior: 'smooth',
					block: 'center',
				} );
			}
		}

		resetModalState( $modal ) {
			// Cache the menu element to avoid repeated queries
			const menuCacheKey = this.getCacheKey(
				$modal.attr( 'id' ),
				'menu-element'
			);
			const $menu = this.cachedQuery(
				$modal,
				'.menu-modal__menu, .menu-meta-nav__menu, .menu-website-modal__menu',
				menuCacheKey
			);

			$menu.children( '.menu-item' ).show();
			$menu.find( '.sub-menu' ).hide();
			$menu.find( '.menu-item-overview' ).remove();
			$menu.find( '.menu-modal__level-heading' ).remove();
			$modal.find( '.menu-modal__breadcrumbs' ).remove();
			$menu
				.find(
					'.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle'
				)
				.attr( 'aria-expanded', 'false' )
				.removeClass( 'expanded' )
				.show();
			$modal
				.find(
					'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
				)
				.addClass( 'u-hidden' );
			$modal.data( 'navigation-stack', [] );

			// Invalidate cache due to DOM structure change
			this.invalidateCache();
		}

		closeCurrentModal() {
			if ( ! this.currentModal ) {
				return;
			}

			const $modal = this.currentModal;

			this.announce( strings.menuClosed );

			// Clear all pending timeouts when closing modal
			this.clearAllTimeouts();

			this.resetModalState( $modal );

			$( '.menu-modal__open-btn' )
				.removeClass( 'is-active' )
				.attr( 'aria-expanded', 'false' );

			$modal.removeClass( 'is-open' ).attr( 'aria-hidden', 'true' );
			this.closeTimeout = this.safeSetTimeout( () => {
				$modal.addClass( 'u-hidden' );
				this.closeTimeout = null;
			}, 300 );

			if ( this.previouslyFocused ) {
				$( this.previouslyFocused ).focus();
			}
			$( 'body' ).removeClass( 'modal-open' );

			// Remove scrollbar width compensation
			this.removeScrollbarCompensation();

			$modal.off( 'keydown.menu-modal' );

			this.currentModal = null;
			this.previouslyFocused = null;

			// Clear cache when modal is closed
			this.domCache.clear();
		}

		trapFocus( $modal ) {
			this.updateFocusTrap( $modal );
		}

		updateFocusTrap( $modal ) {
			$modal.off( 'keydown.menu-modal' );

			const $focusableElements = this.getFocusableElements( $modal );

			if ( $focusableElements.length === 0 ) {
				return;
			}

			const $firstFocusable = $focusableElements.first();
			const $lastFocusable = $focusableElements.last();

			$modal.on( 'keydown.menu-modal', ( e ) => {
				if ( e.key !== 'Tab' ) {
					return;
				}

				const activeElement = $modal[ 0 ].ownerDocument.activeElement;

				if (
					! $modal.find( activeElement ).length &&
					activeElement !== $modal[ 0 ]
				) {
					return;
				}

				if ( e.shiftKey ) {
					if (
						activeElement === $firstFocusable[ 0 ] ||
						! $modal.find( activeElement ).length
					) {
						e.preventDefault();
						$lastFocusable.focus();
						this.announce( strings.movedToLastMenuItem );
					}
				} else if ( activeElement === $lastFocusable[ 0 ] ) {
					e.preventDefault();
					$firstFocusable.focus();
					this.announce( strings.movedToCloseButton );
				}
			} );
		}

		getFocusableElements( $modal ) {
			// Use cached query for focusable elements
			const cacheKey = this.getCacheKey(
				$modal.attr( 'id' ),
				'focusable-elements'
			);
			const $allFocusable = this.cachedQuery(
				$modal,
				[
					'button:visible:not([disabled])',
					'a:visible[href]',
					'input:visible:not([disabled])',
					'select:visible:not([disabled])',
					'textarea:visible:not([disabled])',
					'[tabindex]:visible:not([tabindex="-1"]):not([disabled])',
				].join( ', ' ),
				cacheKey
			);

			const $visibleFocusable = $allFocusable.filter( function () {
				const $el = $( this );
				return (
					$el.is( ':visible' ) &&
					$el.css( 'visibility' ) !== 'hidden' &&
					$el.css( 'opacity' ) !== '0' &&
					! $el.closest( '[style*="display: none"]' ).length
				);
			} );

			// Use cached queries for buttons
			const closeButtonCacheKey = this.getCacheKey(
				$modal.attr( 'id' ),
				'close-buttons'
			);
			const $closeButton = this.cachedQuery(
				$modal,
				'.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn',
				closeButtonCacheKey
			).filter( ':visible' );

			const backButtonCacheKey = this.getCacheKey(
				$modal.attr( 'id' ),
				'back-buttons'
			);
			const $backButton = this.cachedQuery(
				$modal,
				'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn',
				backButtonCacheKey
			).filter( ':visible' );

			const $otherElements = $visibleFocusable
				.not( $closeButton )
				.not( $backButton );

			let $orderedElements = $closeButton;
			if ( $backButton.length ) {
				$orderedElements = $orderedElements.add( $backButton );
			}
			$orderedElements = $orderedElements.add( $otherElements );

			return $orderedElements;
		}

		announce( message ) {
			if ( ! this.currentModal ) {
				return;
			}

			const $announcements = this.currentModal.find(
				'.menu-modal__announcements'
			);
			if ( $announcements.length ) {
				$announcements.text( message );
				this.safeSetTimeout( () => {
					$announcements.empty();
				}, 3000 );
			}
		}

		handleSearchFormSubmission( e ) {
			e.preventDefault(); // Prevent default form submission

			const $form = $( e.currentTarget );

			// Get the search query
			const searchQuery = $form.find( 'input[name="s"]' ).val();
			const searchScope =
				$form.find( 'input[name="fau_search_scope"]:checked' ).val() ||
				'current-site';

			// Close the modal
			this.closeCurrentModal();

			// Build search URL
			const homeUrl = window.location.origin + '/';
			const searchUrl =
				homeUrl +
				'?s=' +
				encodeURIComponent( searchQuery ) +
				'&fau_search_scope=' +
				encodeURIComponent( searchScope );

			// Navigate to search results page
			window.location.href = searchUrl;
		}

		// Destructor method to clean up all resources
		disconnect() {
			// Clear all pending timeouts
			this.clearAllTimeouts();

			// Clear cache
			this.domCache.clear();

			// Remove event listeners
			if ( this.currentModal ) {
				this.currentModal.off( 'keydown.menu-modal' );
			}

			// Cleanup resize events
			this.cleanupResizeEvents();

			// Reset state
			this.currentModal = null;
			this.previouslyFocused = null;
			this.closeTimeout = null;
			this.cacheVersion = 0;
		}
	}

	$( document ).ready( function () {
		new MenuModal();
	} );
} )( jQuery );
