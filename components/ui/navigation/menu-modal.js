/**
 * Unified Menu Modal JavaScript
 * Handles both global menus (meta-nav) and local menus (website)
 */

( function ( $ ) {
	'use strict';

	class MenuModal {
		constructor() {
			this.currentModal = null;
			this.previouslyFocused = null;
			this.closeTimeout = null;
			this.init();
		}

		init() {
			this.bindEvents();
			this.setupAccessibility();
		}

		// Check if a modal is using hierarchy navigation (structure menu)
		isHierarchyMenu( $modal ) {
			return $modal.find( '.menu-meta-nav__menu--hierarchy' ).length > 0;
		}

		// Generate breadcrumbs for hierarchy navigation
		generateBreadcrumbs( navigationStack ) {
			if ( navigationStack.length === 0 ) {
				return '';
			}

			let breadcrumbs = '<nav class="menu-modal__breadcrumbs" aria-label="Breadcrumb">';
			breadcrumbs += '<ol class="menu-modal__breadcrumb-list">';
			
			navigationStack.forEach( ( item, index ) => {
				const isLast = index === navigationStack.length - 1;
				breadcrumbs += '<li class="menu-modal__breadcrumb-item">';
				
				if ( isLast ) {
					breadcrumbs += `<span class="menu-modal__breadcrumb-current" aria-current="page">${ item.parentTitle }</span>`;
				} else {
					breadcrumbs += `<button class="menu-modal__breadcrumb-link" data-breadcrumb-level="${ index }">${ item.parentTitle }</button>`;
				}
				
				if ( ! isLast ) {
					breadcrumbs += '<span class="menu-modal__breadcrumb-separator" aria-hidden="true">›</span>';
				}
				
				breadcrumbs += '</li>';
			} );
			
			breadcrumbs += '</ol>';
			breadcrumbs += '</nav>';
			
			return breadcrumbs;
		}

		bindEvents() {
			// Open modal buttons
			$( document ).on( 'click', '.menu-modal__open-btn', ( e ) => {
				e.preventDefault();
				const modalTarget = $( e.currentTarget ).data( 'modal-target' ) || $( e.currentTarget ).data( 'meta-modal' );
				const targetItem = $( e.currentTarget ).data( 'target-item' );
				const targetUrl = $( e.currentTarget ).data( 'target-url' );

				if ( modalTarget ) {
					this.openModal( modalTarget, targetItem, targetUrl );
				}
			} );

			// Close modal buttons
			$( document ).on( 'click', '.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn', ( e ) => {
				e.preventDefault();
				this.closeCurrentModal();
			} );

			// Back buttons
			$( document ).on( 'click', '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn', ( e ) => {
				e.preventDefault();
				this.navigateBack();
			} );

			// Overlay click to close
			$( document ).on( 'click', '.menu-modal__overlay, .menu-meta-nav__modal__overlay, .menu-website-modal__overlay', ( e ) => {
				if ( e.target === e.currentTarget ) {
					this.closeCurrentModal();
				}
			} );

			// Submenu toggles
			$( document ).on( 'click', '.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle, .menu-modal__submenu-row', ( e ) => {
				e.preventDefault();
				this.toggleSubmenu( $( e.currentTarget ) );
			} );

			// Breadcrumb navigation
			$( document ).on( 'click', '.menu-modal__breadcrumb-link', ( e ) => {
				e.preventDefault();
				const level = parseInt( $( e.currentTarget ).data( 'breadcrumb-level' ) );
				this.navigateToBreadcrumbLevel( level );
			} );

			// Escape key to close
			$( document ).on( 'keydown', ( e ) => {
				if ( e.key === 'Escape' && this.currentModal ) {
					this.closeCurrentModal();
				}
			} );

			// Prevent body scroll when modal is open
			$( document ).on( 'touchmove', ( e ) => {
				if ( this.currentModal && ! $( e.target ).closest( '.menu-modal__content, .menu-meta-nav__modal__content, .menu-website-modal__content' ).length ) {
					e.preventDefault();
				}
			} );
		}

		setupAccessibility() {
			// Ensure modals are properly hidden from screen readers initially
			$( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' ).attr( 'aria-hidden', 'true' );
		}

		openModal( modalId, targetItemId = null, targetUrl = null ) {
			// Find the modal
			let $modal = $( `#${ modalId }` );
			if ( ! $modal.length ) {
				$modal = $( `#${ modalId }-modal` );
			}
			if ( ! $modal.length ) return;

			// If the same modal is already open, toggle it closed
			if ( this.currentModal && this.currentModal.is( $modal ) ) {
				this.closeCurrentModal();
				return;
			}

			// Close any other currently open modal
			this.closeCurrentModal();

			// Clear any pending close timeout
			if ( this.closeTimeout ) {
				clearTimeout( this.closeTimeout );
				this.closeTimeout = null;
			}

			this.currentModal = $modal;
			this.resetModalState( $modal );

			// Show modal
			$modal.removeAttr( 'style' ).addClass( 'is-open' ).attr( 'aria-hidden', 'false' );

			// Navigate to specific item or current page
			if ( targetItemId && targetUrl ) {
				this.navigateToItem( $modal, targetItemId, targetUrl );
			} else {
				this.navigateToCurrentPage( $modal );
			}

			// Focus management
			const $firstFocusable = $modal.find( 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])' ).first();
			if ( $firstFocusable.length ) {
				$firstFocusable.focus();
			}

			this.previouslyFocused = document.activeElement;
			$( 'body' ).addClass( 'modal-open' );
			this.trapFocus( $modal );
		}

		navigateToItem( $modal, targetItemId, targetUrl ) {
			// Find target item by ID or URL
			let $targetItem = $modal.find( `li[data-menu-item-id="${ targetItemId }"]` ).first();
			
			if ( ! $targetItem.length ) {
				$targetItem = $modal.find( 'li[data-menu-url]' ).filter( function () {
					const itemUrl = $( this ).attr( 'data-menu-url' );
					return itemUrl && itemUrl.replace( /\/$/, '' ) === targetUrl.replace( /\/$/, '' );
				} ).first();
			}

			if ( $targetItem.length ) {
				this.drillDownToItem( $modal, $targetItem );
			} else {
				this.navigateToCurrentPage( $modal );
			}
		}

		navigateToCurrentPage( $modal ) {
			const currentPath = window.location.pathname.replace( /\/$/, '' );
			const searchPaths = currentPath === '' ? ['/'] : [currentPath, currentPath + '/'];
			
			let $currentItem = $();
			for ( const path of searchPaths ) {
				$currentItem = $modal.find( `[data-menu-url="${ path }"]` ).filter( function () {
					return $( this ).attr( 'data-menu-url' ) !== '';
				} ).first();
				if ( $currentItem.length ) break;
			}

			if ( $currentItem.length ) {
				this.drillDownToItem( $modal, $currentItem );
			}
		}

		drillDownToItem( $modal, $targetItem ) {
			// Navigate through all parent items
			const $parents = $targetItem.parents( '.menu-item' ).get().reverse();
			$parents.forEach( ( parentItem ) => {
				const $parent = $( parentItem );
				const $toggle = $parent.children( '.menu-modal__submenu-toggle' );
				if ( $toggle.length ) {
					this.performDrillDown( $modal, $parent, $toggle );
				}
			} );

			// If target item itself has children, drill into it
			const $toggle = $targetItem.children( '.menu-modal__submenu-toggle' );
			const $submenu = $targetItem.children( '.sub-menu' );
			if ( $submenu.length && $toggle.length ) {
				this.performDrillDown( $modal, $targetItem, $toggle );
				this.highlightOverviewLink( $submenu );
			} else {
				this.highlightMenuItem( $targetItem );
			}

			// Update breadcrumbs for hierarchy menus
			if ( this.isHierarchyMenu( $modal ) ) {
				this.updateBreadcrumbs( $modal );
			}
		}

		performDrillDown( $modal, $parentLi, $toggle ) {
			const $submenu = $toggle.siblings( '.sub-menu' );
			if ( $submenu.length === 0 ) return;

			const isHierarchyMenu = this.isHierarchyMenu( $modal );
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			// Hide siblings and toggle
			$parentLi.siblings().hide();
			$toggle.hide();

			// Add heading for regular menus only
			if ( ! isHierarchyMenu ) {
				$modal.find( '.menu-modal__level-heading' ).remove();
				$toggle.after( `<h2 class="menu-modal__level-heading">${ $toggle.data( 'parent-title' ) }</h2>` );
			}

			// Add overview link
			const parentUrl = $toggle.data( 'parent-url' );
			const parentTitle = $toggle.data( 'parent-title' );
			if ( parentUrl && parentTitle && parentUrl !== '#' ) {
				$submenu.find( '.menu-item-overview' ).remove();
				$submenu.prepend( `<li class="menu-item menu-item-overview"><a href="${ parentUrl }">Übersicht: ${ parentTitle }</a></li>` );
			}

			// Show submenu
			$submenu.css( 'display', 'block' );
			$toggle.attr( 'aria-expanded', 'true' );

			// Update navigation stack
			navigationStack.push( {
				parentUl: $parentLi.parent(),
				parentLi: $parentLi,
				parentUrl,
				parentTitle,
			} );
			$modal.data( 'navigation-stack', navigationStack );

			// Show back button for all menus
			$modal.find( '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn' ).show();
		}

		toggleSubmenu( $toggle ) {
			const $modal = $toggle.closest( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' );
			const $parentLi = $toggle.closest( '.menu-item' );
			
			this.performDrillDown( $modal, $parentLi, $toggle );

			// Update breadcrumbs for hierarchy menus
			if ( this.isHierarchyMenu( $modal ) ) {
				this.updateBreadcrumbs( $modal );
			}

			// Re-highlight current page after navigation
			this.rehighlightCurrentPage( $modal );
		}

		navigateBack() {
			if ( ! this.currentModal ) return;

			const $modal = this.currentModal;
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			if ( navigationStack.length === 0 ) {
				this.closeCurrentModal();
				return;
			}

			const { parentUl, parentLi } = navigationStack.pop();

			// Hide current submenu and show parent level
			parentLi.find( '.sub-menu' ).hide();
			parentLi.find( '.menu-item-overview' ).remove();
			parentLi.find( '.menu-modal__level-heading' ).remove();
			parentUl.children( 'li' ).show();
			parentLi.children( '.menu-modal__submenu-toggle' ).show().attr( 'aria-expanded', 'false' );

			$modal.data( 'navigation-stack', navigationStack );

			// Handle remaining navigation levels
			if ( navigationStack.length > 0 ) {
				const currentLevel = navigationStack[ navigationStack.length - 1 ];
				if ( currentLevel.parentTitle && ! this.isHierarchyMenu( $modal ) ) {
					$modal.find( '.menu-modal__level-heading' ).remove();
					parentUl.prepend( `<h2 class="menu-modal__level-heading">${ currentLevel.parentTitle }</h2>` );
				}
			} else {
				$modal.find( '.menu-modal__level-heading' ).remove();
				$modal.find( '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn' ).hide();
			}

			// Update breadcrumbs for hierarchy menus
			if ( this.isHierarchyMenu( $modal ) ) {
				this.updateBreadcrumbs( $modal );
			}

			// Re-highlight current page after navigation
			this.rehighlightCurrentPage( $modal );
		}

		navigateToBreadcrumbLevel( targetLevel ) {
			if ( ! this.currentModal ) return;

			const $modal = this.currentModal;
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			// Navigate back to target level
			while ( navigationStack.length > targetLevel + 1 ) {
				this.navigateBack();
			}

			// Ensure current page is highlighted after breadcrumb navigation
			this.rehighlightCurrentPage( $modal );
		}

		updateBreadcrumbs( $modal ) {
			if ( ! this.isHierarchyMenu( $modal ) ) return;

			const navigationStack = $modal.data( 'navigation-stack' ) || [];
			const $breadcrumbContainer = $modal.find( '.menu-modal__breadcrumbs' );
			
			if ( navigationStack.length === 0 ) {
				$breadcrumbContainer.remove();
				return;
			}

			const breadcrumbHtml = this.generateBreadcrumbs( navigationStack );
			
			if ( $breadcrumbContainer.length ) {
				$breadcrumbContainer.replaceWith( breadcrumbHtml );
			} else {
				$modal.find( '.menu-meta-nav__modal__content' ).prepend( breadcrumbHtml );
			}
		}

		highlightOverviewLink( $submenu ) {
			if ( ! $submenu.length ) return;

			// Remove existing highlights
			$submenu.closest( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' )
				.find( '.current-menu-item-focused, .active' )
				.removeClass( 'current-menu-item-focused active' );

			// Highlight overview link if it matches current URL
			const currentUrl = window.location.href;
			const $overviewLink = $submenu.find( '.menu-item-overview a' ).filter( function () {
				return this.href === currentUrl;
			} ).first();

			if ( $overviewLink.length ) {
				$overviewLink.addClass( 'current-menu-item-focused active' );
				$overviewLink[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center' } );
			}
		}

		// Re-highlight current page in the currently visible menu level
		rehighlightCurrentPage( $modal ) {
			const currentPath = window.location.pathname.replace( /\/$/, '' );
			const searchPaths = currentPath === '' ? ['/'] : [currentPath, currentPath + '/'];
			
			// First try to highlight overview link in visible submenus
			const $visibleSubmenus = $modal.find( '.sub-menu:visible' );
			$visibleSubmenus.each( ( _, submenu ) => {
				this.highlightOverviewLink( $( submenu ) );
			} );

			// If no overview link was highlighted, look for current page in visible items
			const $highlighted = $modal.find( '.current-menu-item-focused, .active' );
			if ( ! $highlighted.length ) {
				// Find current page item in currently visible menu items
				let $currentItem = $();
				for ( const path of searchPaths ) {
					$currentItem = $modal.find( '.menu-item:visible' ).filter( function () {
						const itemUrl = $( this ).attr( 'data-menu-url' );
						return itemUrl && itemUrl.replace( /\/$/, '' ) === path;
					} ).first();
					if ( $currentItem.length ) break;
				}

				if ( $currentItem.length ) {
					this.highlightMenuItem( $currentItem );
				}
			}
		}

		highlightMenuItem( $item ) {
			// Remove existing highlights
			$item.closest( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' )
				.find( '.current-menu-item-focused, .active' )
				.removeClass( 'current-menu-item-focused active' );

			// Highlight the item
			const $link = $item.find( 'a' ).first();
			if ( $link.length ) {
				$link.addClass( 'current-menu-item-focused active' );
				$link[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center' } );
			} else {
				$item.addClass( 'current-menu-item-focused active' );
				$item[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center' } );
			}
		}

		resetModalState( $modal ) {
			const $menu = $modal.find( '.menu-modal__menu, .menu-meta-nav__menu, .menu-website-modal__menu' );

			// Reset to initial state
			$menu.children( '.menu-item' ).show();
			$menu.find( '.sub-menu' ).hide();
			$menu.find( '.menu-item-overview' ).remove();
			$menu.find( '.menu-modal__level-heading' ).remove();
			$modal.find( '.menu-modal__breadcrumbs' ).remove();
			$menu.find( '.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle' )
				.attr( 'aria-expanded', 'false' )
				.removeClass( 'expanded' )
				.show();
			$modal.find( '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn' ).hide();
			$modal.data( 'navigation-stack', [] );
		}

		closeCurrentModal() {
			if ( ! this.currentModal ) return;

			const $modal = this.currentModal;
			
			// Clear any existing close timeout
			if ( this.closeTimeout ) {
				clearTimeout( this.closeTimeout );
			}
			
			this.resetModalState( $modal );

			// Hide modal
			$modal.removeClass( 'is-open' ).attr( 'aria-hidden', 'true' );
			this.closeTimeout = setTimeout( () => {
				$modal.hide();
				this.closeTimeout = null;
			}, 300 );

			// Restore focus and body scroll
			if ( this.previouslyFocused ) {
				$( this.previouslyFocused ).focus();
			}
			$( 'body' ).removeClass( 'modal-open' );

			this.currentModal = null;
			this.previouslyFocused = null;
		}

		trapFocus( $modal ) {
			const $focusableElements = $modal.find( 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])' );
			const $firstFocusable = $focusableElements.first();
			const $lastFocusable = $focusableElements.last();

			$modal.on( 'keydown.menu-modal', ( e ) => {
				if ( e.key !== 'Tab' ) return;

				const activeElement = document.activeElement;
				if ( e.shiftKey ) {
					if ( activeElement === $firstFocusable[ 0 ] ) {
						e.preventDefault();
						$lastFocusable.focus();
					}
				} else if ( activeElement === $lastFocusable[ 0 ] ) {
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
