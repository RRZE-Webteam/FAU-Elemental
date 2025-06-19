/**
 * Unified Menu Modal JavaScript
 * Handles both global menus (meta-nav) and local menus (website)
 */

( function ( $ ) {
	'use strict';

	class MenuModal {
		constructor() {
			this.modals = [];
			this.currentModal = null;
			this.init();
		}

		init() {
			this.bindEvents();
			this.setupAccessibility();
		}

		bindEvents() {
			// Open modal buttons
			$( document ).on( 'click', '.menu-modal__open-btn', ( e ) => {
				e.preventDefault();
				const modalTarget =
					$( e.currentTarget ).data( 'modal-target' ) ||
					$( e.currentTarget ).data( 'meta-modal' );
				const targetItem = $( e.currentTarget ).data( 'target-item' );
				const targetUrl = $( e.currentTarget ).data( 'target-url' );

				if ( modalTarget ) {
					this.openModal( modalTarget, targetItem, targetUrl );
				}
			} );

			// Close modal buttons - Updated to match actual generated HTML structure
			$( document ).on(
				'click',
				'.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn',
				( e ) => {
					e.preventDefault();
					this.closeCurrentModal();
				}
			);

			// Back buttons
			$( document ).on(
				'click',
				'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn',
				( e ) => {
					e.preventDefault();
					this.goBack();
				}
			);

			// Overlay click to close
			$( document ).on(
				'click',
				'.menu-modal__overlay, .menu-meta-nav__modal__overlay, .menu-website-modal__overlay',
				( e ) => {
					if ( e.target === e.currentTarget ) {
						this.closeCurrentModal();
					}
				}
			);

			// Submenu toggles (including new row-style toggles)
			$( document ).on(
				'click',
				'.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle, .menu-modal__submenu-row',
				( e ) => {
					e.preventDefault();
					this.toggleSubmenu( $( e.currentTarget ) );
				}
			);

			// Escape key to close
			$( document ).on( 'keydown', ( e ) => {
				if ( e.key === 'Escape' && this.currentModal ) {
					this.closeCurrentModal();
				}
			} );

			// Prevent body scroll when modal is open
			$( document ).on( 'touchmove', ( e ) => {
				if (
					this.currentModal &&
					! $( e.target ).closest(
						'.menu-modal__content, .menu-meta-nav__modal-content, .menu-website-modal__content'
					).length
				) {
					e.preventDefault();
				}
			} );
		}

		setupAccessibility() {
			// Ensure modals are properly hidden from screen readers initially
			$( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' ).each(
				function () {
					$( this ).attr( 'aria-hidden', 'true' );
				}
			);
		}

		openModal( modalId, targetItemId = null, targetUrl = null ) {
			// Close any currently open modal
			this.closeCurrentModal();

			// Find the modal (support different ID formats)
			let $modal = $( `#${ modalId }` );

			if ( ! $modal.length ) {
				$modal = $( `#${ modalId }-modal` );
			}

			if ( ! $modal.length ) {
				// Modal not found, return early
				return;
			}

			this.currentModal = $modal;

			// Reset modal to initial state
			this.resetModalState( $modal );

			// Remove inline display:none so CSS can take over
			$modal.removeAttr( 'style' );

			// Show modal using CSS classes only
			$modal.addClass( 'is-open' );
			$modal.attr( 'aria-hidden', 'false' );

			// If we have a specific target item, navigate to it
			if ( targetItemId && targetUrl ) {
				this.openToSpecificItem( $modal, targetItemId, targetUrl );
			} else {
				// For regular menu opening, find current page in menu and open its path
				this.openCurrentPagePath( $modal );
			}

			// Focus management
			const $firstFocusable = $modal
				.find(
					'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
				)
				.first();
			if ( $firstFocusable.length ) {
				$firstFocusable.focus();
			}

			// Store the previously focused element
			this.previouslyFocused = $modal[ 0 ].ownerDocument.activeElement;

			// Prevent body scroll
			$( 'body' ).addClass( 'modal-open' );

			// Trap focus within modal
			this.trapFocus( $modal );
		}

		// Helper to highlight the overview link for the current page if present in the given submenu
		highlightCurrentOverviewLink( $submenu ) {
			// Remove highlight from all overview links and menu items in the entire modal
			$submenu
				.closest(
					'.menu-modal, .menu-meta-nav__modal, .menu-website-modal'
				)
				.find( '.current-menu-item-focused, .active' )
				.removeClass( 'current-menu-item-focused active' );

			const currentUrl = window.location.href;
			const $overviewLink = $submenu
				.find( '.menu-item-overview a' )
				.filter( function () {
					try {
						if ( this.getAttribute( 'href' ) === '#' ) {
							return false;
						}
						// Compare full URLs instead of just paths
						const linkUrl = this.href;
						return linkUrl === currentUrl;
					} catch ( e ) {
						return false;
					}
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

		openToSpecificItem( $modal, targetItemId, targetUrl ) {
			// First try to find the target item by ID if provided
			let $targetItem = null;
			if ( targetItemId ) {
				$targetItem = $modal
					.find( `li[data-menu-item-id="${ targetItemId }"]` )
					.first();
			}

			// If not found by ID, try finding by URL
			if ( ! $targetItem ) {
				$targetItem = $modal
					.find( 'li[data-menu-url]' )
					.filter( function () {
						const itemUrl = $( this ).attr( 'data-menu-url' );
						// Remove trailing slashes for comparison
						const normalizedItemUrl = itemUrl
							? itemUrl.replace( /\/$/, '' )
							: '';
						const normalizedTargetUrl = targetUrl
							? targetUrl.replace( /\/$/, '' )
							: '';
						return normalizedItemUrl === normalizedTargetUrl;
					} )
					.first();
			}

			// If still not found, try finding by button/toggle with matching parent-url
			if ( ! $targetItem ) {
				$targetItem = $modal
					.find(
						'.menu-modal__submenu-toggle, .menu-modal__submenu-row'
					)
					.filter( function () {
						const parentUrl = $( this ).data( 'parent-url' );
						const normalizedParentUrl = parentUrl
							? parentUrl.replace( /\/$/, '' )
							: '';
						const normalizedTargetUrl = targetUrl
							? targetUrl.replace( /\/$/, '' )
							: '';
						return normalizedParentUrl === normalizedTargetUrl;
					} )
					.closest( '.menu-item' )
					.first();
			}

			// If still not found, try to find by link href
			if ( ! $targetItem ) {
				$targetItem = $modal
					.find( 'a' )
					.filter( function () {
						const href = $( this ).attr( 'href' );
						if ( ! href ) {
							return false;
						}
						try {
							const linkUrl = new URL(
								href,
								window.location.origin
							).pathname.replace( /\/$/, '' );
							const normalizedTargetUrl = targetUrl
								? targetUrl.replace( /\/$/, '' )
								: '';
							return linkUrl === normalizedTargetUrl;
						} catch ( e ) {
							return false;
						}
					} )
					.closest( '.menu-item' )
					.first();
			}

			if ( ! $targetItem ) {
				// Fallback to current page path if target not found
				this.openCurrentPagePath( $modal );
				return;
			}

			// Find the toggle for this item
			const $toggle = $targetItem.children(
				'.menu-modal__submenu-toggle, .menu-modal__submenu-row'
			);

			if ( $toggle.length ) {
				// This item has children, drill down into it to show its submenu
				this.drillDownToMenuItem( $modal, $targetItem, $toggle );
			} else {
				// This item doesn't have children, just highlight it
				$modal
					.find( '.current-menu-item-focused, .active' )
					.removeClass( 'current-menu-item-focused active' );
				if ( $targetItem.find( 'a' ).length ) {
					$targetItem
						.find( 'a' )
						.addClass( 'current-menu-item-focused active' );
					$targetItem.find( 'a' )[ 0 ].scrollIntoView( {
						behavior: 'smooth',
						block: 'center',
					} );
				} else {
					$targetItem.addClass( 'current-menu-item-focused active' );
					$targetItem[ 0 ].scrollIntoView( {
						behavior: 'smooth',
						block: 'center',
					} );
				}
			}
		}

		highlightCurrentPage( $modal ) {
			const currentPath = window.location.pathname.replace( /\/$/, '' ); // remove trailing slash
			const currentPathWithSlash =
				currentPath === '' ? '/' : currentPath + '/';

			// Try to find the current item with or without trailing slash
			let $currentItem = $();
			if ( currentPath === '' ) {
				// Only match homepage menu item (data-menu-url='/')
				$currentItem = $modal.find( '[data-menu-url="/"]' ).first();
			} else {
				$currentItem = $modal
					.find( `[data-menu-url="${ currentPath }"]` )
					.filter( function () {
						return $( this ).attr( 'data-menu-url' ) !== '';
					} )
					.first();
				if ( ! $currentItem.length ) {
					$currentItem = $modal
						.find( `[data-menu-url="${ currentPathWithSlash }"]` )
						.filter( function () {
							return $( this ).attr( 'data-menu-url' ) !== '';
						} )
						.first();
				}
			}

			if ( $currentItem.length ) {
				// Just highlight the current page item without drilling down
				$modal
					.find( '.current-menu-item-focused, .active' )
					.removeClass( 'current-menu-item-focused active' );
				if ( $currentItem.find( 'a' ).length ) {
					$currentItem
						.find( 'a' )
						.addClass( 'current-menu-item-focused active' );
				} else {
					$currentItem.addClass( 'current-menu-item-focused active' );
				}
			}
		}

		openCurrentPagePath( $modal ) {
			const currentPath = window.location.pathname.replace( /\/$/, '' ); // remove trailing slash
			const currentPathWithSlash =
				currentPath === '' ? '/' : currentPath + '/';

			// Try to find the current item with or without trailing slash
			let $currentItem = $();
			if ( currentPath === '' ) {
				// Only match homepage menu item (data-menu-url='/')
				$currentItem = $modal.find( '[data-menu-url="/"]' ).first();
			} else {
				$currentItem = $modal
					.find( `[data-menu-url="${ currentPath }"]` )
					.filter( function () {
						return $( this ).attr( 'data-menu-url' ) !== '';
					} )
					.first();
				if ( ! $currentItem.length ) {
					$currentItem = $modal
						.find( `[data-menu-url="${ currentPathWithSlash }"]` )
						.filter( function () {
							return $( this ).attr( 'data-menu-url' ) !== '';
						} )
						.first();
				}
			}

			// If current page is not found in the menu, just show root level without highlighting anything
			if ( ! $currentItem.length ) {
				// Reset any existing highlights
				$modal
					.find( '.current-menu-item-focused, .active' )
					.removeClass( 'current-menu-item-focused active' );
				// Hide all submenus
				$modal.find( '.sub-menu' ).hide().css( 'display', 'none' );
				// Show all top-level menu items
				$modal
					.find(
						'.menu-modal__menu > .menu-item, .menu-meta-nav__menu > .menu-item, .menu-website-modal__menu > .menu-item'
					)
					.show();
				// Hide all back buttons and level headings
				$modal
					.find(
						'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
					)
					.hide();
				$modal.find( '.menu-modal__level-heading' ).remove();
				// Reset navigation stack
				$modal.data( 'navigation-stack', [] );
				// Debug log
				return;
			}

			// Find all parent menu-items (from root to direct parent)
			const $parents = $currentItem
				.parents( '.menu-item' )
				.get()
				.reverse();
			$parents.forEach( ( parentItem ) => {
				const $parent = $( parentItem );
				const $toggle = $parent.children(
					'.menu-modal__submenu-toggle'
				);
				if ( $toggle.length ) {
					this.drillDownToMenuItem( $modal, $parent, $toggle );
				}
			} );

			// If the current item itself is a parent, drill down into it as well
			const $submenu = $currentItem.children( '.sub-menu' );
			const $toggle = $currentItem.children(
				'.menu-modal__submenu-toggle'
			);
			if ( $submenu.length && $toggle.length ) {
				this.drillDownToMenuItem( $modal, $currentItem, $toggle );
			}

			if ( $submenu.length ) {
				$submenu.css( 'display', 'block' );
				// Always highlight the overview link after showing the submenu
				this.highlightCurrentOverviewLink( $submenu );
			} else {
				// Remove highlight from all overview links and menu items in the modal
				$modal
					.find( '.current-menu-item-focused, .active' )
					.removeClass( 'current-menu-item-focused active' );
				// Highlight the current item as before (for leaf pages)
				// Try to highlight the <a> inside the <li>
				if ( $currentItem.find( 'a' ).length ) {
					$currentItem
						.find( 'a' )
						.addClass( 'current-menu-item-focused active' );
					$currentItem.find( 'a' )[ 0 ].scrollIntoView( {
						behavior: 'smooth',
						block: 'center',
					} );
				} else {
					$currentItem.addClass( 'current-menu-item-focused active' );
					$currentItem[ 0 ].scrollIntoView( {
						behavior: 'smooth',
						block: 'center',
					} );
				}
			}
		}

		drillDownToMenuItem( $modal, $parentLi, $toggle ) {
			const $submenu = $toggle.siblings( '.sub-menu' );

			if ( $submenu.length === 0 ) {
				return;
			}

			const $backButton = $modal.find(
				'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
			);

			// Hide all siblings of the current item
			$parentLi.siblings().hide();
			// Hide the submenu toggle and add heading for current level
			$toggle.hide();
			// Remove any existing level headings in the modal
			$modal.find( '.menu-modal__level-heading' ).remove();
			// Add new level heading for current level
			$toggle.after(
				'<h2 class="menu-modal__level-heading">' +
					$toggle.data( 'parent-title' ) +
					'</h2>'
			);
			// Remove any old overview links in this submenu
			$submenu.find( '.menu-item-overview' ).remove();
			// Add overview link at the beginning of submenu
			const parentUrl = $toggle.data( 'parent-url' );
			const parentTitle = $toggle.data( 'parent-title' );
			if ( parentUrl && parentTitle && parentUrl !== '#' ) {
				const overviewLink = `<li class="menu-item menu-item-overview"><a href="${ parentUrl }">Übersicht: ${ parentTitle }</a></li>`;
				$submenu.prepend( overviewLink );
			}
			// Show the submenu
			$submenu.css( 'display', 'block' );
			$toggle.attr( 'aria-expanded', 'true' );
			// Always highlight the overview link after showing the submenu
			this.highlightCurrentOverviewLink( $submenu );
			// Initialize or get navigation stack
			if ( ! $modal.data( 'navigation-stack' ) ) {
				$modal.data( 'navigation-stack', [] );
			}
			// Push current state to navigation stack
			const navigationStack = $modal.data( 'navigation-stack' );
			navigationStack.push( {
				parentUl: $parentLi.parent(),
				parentLi: $parentLi,
				parentUrl,
				parentTitle,
			} );
			// Show back button
			$backButton.show();
		}

		resetModalState( $modal ) {
			// Reset drill-down navigation state completely
			const $menu = $modal.find(
				'.menu-modal__menu, .menu-meta-nav__menu, .menu-website-modal__menu'
			);
			const $backButton = $modal.find(
				'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
			);

			// Show all top-level menu items
			$menu.children( '.menu-item' ).show();
			// Hide all submenus initially
			$menu.find( '.sub-menu' ).hide();
			// Remove all overview links
			$menu.find( '.menu-item-overview' ).remove();
			// Remove all level headings
			$menu.find( '.menu-modal__level-heading' ).remove();
			// Reset all toggle states
			$menu
				.find(
					'.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle'
				)
				.attr( 'aria-expanded', 'false' )
				.removeClass( 'expanded' )
				.show();
			// Hide back button
			$backButton.hide();
			// Clear navigation stack
			$modal.data( 'navigation-stack', [] );
		}

		closeCurrentModal() {
			if ( ! this.currentModal ) {
				return;
			}

			const $modal = this.currentModal;

			// Reset drill-down navigation state completely
			const $menu = $modal.find(
				'.menu-modal__menu, .menu-meta-nav__menu, .menu-website-modal__menu'
			);
			const $backButton = $modal.find(
				'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
			);

			// Show all menu items
			$menu.find( '.menu-item' ).show();

			// Hide all submenus
			$menu.find( '.sub-menu' ).hide();

			// Remove all overview links
			$menu.find( '.menu-item-overview' ).remove();

			// Remove all level headings
			$menu.find( '.menu-modal__level-heading' ).remove();

			// Reset all toggle states
			$menu
				.find( '.menu-modal__submenu-toggle' )
				.attr( 'aria-expanded', 'false' )
				.removeClass( 'expanded' )
				.show();

			// Hide back button
			$backButton.hide();

			// Clear navigation stack
			$modal.data( 'navigation-stack', [] );

			// Hide modal
			$modal.removeClass( 'is-open' );
			$modal.attr( 'aria-hidden', 'true' );

			// Wait for animation then hide
			setTimeout( () => {
				$modal.hide();
			}, 300 );

			// Restore focus
			if ( this.previouslyFocused ) {
				$( this.previouslyFocused ).focus();
			}

			// Allow body scroll
			$( 'body' ).removeClass( 'modal-open' );

			this.currentModal = null;
			this.previouslyFocused = null;
		}

		goBack() {
			if ( ! this.currentModal ) {
				return;
			}

			const $modal = this.currentModal;
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			if ( navigationStack.length === 0 ) {
				// If no navigation stack, just close the modal
				this.closeCurrentModal();
				return;
			}

			const $backButton = $modal.find(
				'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
			);

			// Pop the most recent navigation state
			const { parentUl, parentLi } = navigationStack.pop();

			// Hide current submenu
			const $submenu = parentLi.find( '.sub-menu' );
			$submenu.hide();

			// Remove overview link if it exists
			$submenu.find( '.menu-item-overview' ).remove();

			// Remove level heading if it exists
			parentLi.find( '.menu-modal__level-heading' ).remove();

			// Show all siblings in the parent menu
			parentUl.children( 'li' ).show();

			// Show the parent toggle button
			parentLi.children( '.menu-modal__submenu-toggle' ).show();

			// Set all toggles in the current menu to collapsed
			parentUl
				.find( '> li > .menu-modal__submenu-toggle' )
				.attr( 'aria-expanded', 'false' );

			// Update navigation stack
			$modal.data( 'navigation-stack', navigationStack );

			// Check if there's still a parent level to show heading for
			if ( navigationStack.length > 0 ) {
				// Get the parent level info from the remaining stack
				const currentLevel =
					navigationStack[ navigationStack.length - 1 ];
				if ( currentLevel.parentTitle ) {
					// Remove any existing headings first
					$modal.find( '.menu-modal__level-heading' ).remove();
					// Add heading for the level we're going back to
					parentUl.prepend(
						'<h2 class="menu-modal__level-heading">' +
							currentLevel.parentTitle +
							'</h2>'
					);
				}
			} else {
				// We're back to the root level, remove all headings
				$modal.find( '.menu-modal__level-heading' ).remove();
				$backButton.hide();
			}
			// Always highlight the overview link after showing the parent submenu
			this.highlightCurrentOverviewLink( parentUl.find( '> .sub-menu' ) );
			// Also highlight in any currently visible submenu (for robustness)
			const $visibleSubmenus = $modal.find( '.sub-menu:visible' );
			$visibleSubmenus.each( ( _, submenu ) => {
				this.highlightCurrentOverviewLink( $( submenu ) );
			} );
		}

		toggleSubmenu( $toggle ) {
			const $submenu = $toggle.siblings( '.sub-menu' );

			if ( $submenu.length === 0 ) {
				return;
			}

			const $parentLi = $toggle.closest( '.menu-item' );
			const $modal = $toggle.closest(
				'.menu-modal, .menu-meta-nav__modal, .menu-website-modal'
			);
			const $backButton = $modal.find(
				'.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn'
			);

			// Get parent information from data attributes
			const parentUrl = $toggle.data( 'parent-url' );
			const parentTitle = $toggle.data( 'parent-title' );

			// Use drill-down navigation for all menus (both global and local)
			// Hide all siblings of the current item
			$parentLi.siblings().hide();

			// Hide the submenu toggle and add heading for current level
			$toggle.hide();

			// Remove any existing level headings in the modal
			$modal.find( '.menu-modal__level-heading' ).remove();

			// Add new level heading for current level
			$toggle.after(
				'<h2 class="menu-modal__level-heading">' + parentTitle + '</h2>'
			);

			// Add overview link at the beginning of submenu
			if ( parentUrl && parentTitle ) {
				const overviewLink = `<li class="menu-item menu-item-overview"><a href="${ parentUrl }">Übersicht: ${ parentTitle }</a></li>`;
				$submenu.prepend( overviewLink );
			}

			// Show the submenu
			$submenu.css( 'display', 'block' );
			$toggle.attr( 'aria-expanded', 'true' );
			// Always highlight the overview link after showing the submenu
			this.highlightCurrentOverviewLink( $submenu );

			// Initialize or get navigation stack
			if ( ! $modal.data( 'navigation-stack' ) ) {
				$modal.data( 'navigation-stack', [] );
			}

			// Push current state to navigation stack
			const navigationStack = $modal.data( 'navigation-stack' );
			navigationStack.push( {
				parentUl: $parentLi.parent(),
				parentLi: $parentLi,
				parentUrl,
				parentTitle,
			} );

			// Show back button
			$backButton.show();
		}

		trapFocus( $modal ) {
			const $focusableElements = $modal.find(
				'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
			);
			const $firstFocusable = $focusableElements.first();
			const $lastFocusable = $focusableElements.last();

			$modal.on( 'keydown.menu-modal', ( e ) => {
				if ( e.key !== 'Tab' ) {
					return;
				}

				const activeElement = $modal[ 0 ].ownerDocument.activeElement;

				if ( e.shiftKey ) {
					// Shift + Tab
					if ( activeElement === $firstFocusable[ 0 ] ) {
						e.preventDefault();
						$lastFocusable.focus();
					}
				} else if ( activeElement === $lastFocusable[ 0 ] ) {
					// Tab
					e.preventDefault();
					$firstFocusable.focus();
				}
			} );
		}
	}

	// Initialize when DOM is ready
	$( document ).ready( function () {
		new MenuModal();
	} );


} )( jQuery );
