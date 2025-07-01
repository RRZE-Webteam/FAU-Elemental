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

			isHierarchyMenu( $modal ) {
		// Only the structure menu should show breadcrumbs
		// Check for the hierarchy-specific menu class
		return $modal.find( '.menu-meta-nav__menu--hierarchy' ).length > 0;
	}

		generateBreadcrumbs( navigationStack ) {
			if ( navigationStack.length === 0 ) return '';

			let breadcrumbsHtml = '<nav class="menu-modal__breadcrumbs" aria-label="Menu breadcrumbs"><ol>';
			
			navigationStack.forEach( ( item, index ) => {
				const isLast = index === navigationStack.length - 1;
				const levelClass = `breadcrumb-level-${ index }`;
				
				if ( isLast ) {
					breadcrumbsHtml += `<li class="breadcrumb-item ${ levelClass } current" aria-current="location">${ item.parentTitle }</li>`;
				} else {
					breadcrumbsHtml += `<li class="breadcrumb-item ${ levelClass }">`;
					breadcrumbsHtml += `<a class="breadcrumb-link" data-level="${ index }" aria-label="Go to ${ item.parentTitle }">${ item.parentTitle }</a>`;
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
				const targetItemId = $( e.currentTarget ).data( 'target-item-id' );
				const targetUrl = $( e.currentTarget ).data( 'target-url' );
				this.openModal( modalId, targetItemId, targetUrl );
			} );

			$( document ).on( 'click', '.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn', ( e ) => {
				e.preventDefault();
				this.closeCurrentModal();
			} );

			$( document ).on( 'click', '.menu-modal__overlay, .menu-meta-nav__modal__overlay, .menu-website-modal__overlay', () => {
				this.closeCurrentModal();
			} );

			$( document ).on( 'click', '.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle', ( e ) => {
				e.preventDefault();
				this.toggleSubmenu( $( e.currentTarget ) );
			} );

			$( document ).on( 'click', '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn', ( e ) => {
				e.preventDefault();
				this.navigateBack();
			} );

			$( document ).on( 'click', '.breadcrumb-link', ( e ) => {
				e.preventDefault();
				const targetLevel = parseInt( $( e.currentTarget ).data( 'level' ) );
				this.navigateToBreadcrumbLevel( targetLevel );
			} );

			$( document ).on( 'keydown', ( e ) => {
				if ( e.key === 'Escape' && this.currentModal ) {
					this.closeCurrentModal();
				}
			} );
		}

		setupAccessibility() {
			$( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' ).attr( 'aria-hidden', 'true' );
		}

		openModal( modalId, targetItemId = null, targetUrl = null ) {
			let $modal = $( `#${ modalId }` );
			if ( ! $modal.length ) {
				$modal = $( `#${ modalId }-modal` );
			}
			if ( ! $modal.length ) return;

			this.previouslyFocused = document.activeElement;

			if ( this.currentModal && this.currentModal.is( $modal ) ) {
				this.closeCurrentModal();
				return;
			}

			this.closeCurrentModal();

			if ( this.closeTimeout ) {
				clearTimeout( this.closeTimeout );
				this.closeTimeout = null;
			}

			this.currentModal = $modal;
			this.resetModalState( $modal );

			const $triggerButton = $( `[data-modal-target="${ modalId }"]` );
			if ( $triggerButton.length ) {
				$( '.menu-modal__open-btn' ).removeClass( 'is-active' ).attr( 'aria-expanded', 'false' );
				$triggerButton.addClass( 'is-active' ).attr( 'aria-expanded', 'true' );
			}

			$modal.removeAttr( 'style' ).addClass( 'is-open' ).attr( 'aria-hidden', 'false' );
			$( 'body' ).addClass( 'modal-open' );

			this.announce( 'Menu opened' );

			if ( targetItemId || targetUrl ) {
				this.navigateToItem( $modal, targetItemId, targetUrl );
			} else {
				this.navigateToCurrentPage( $modal );
			}

			this.trapFocus( $modal );
			
			setTimeout( () => {
				const $closeButton = $modal.find( '.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn' ).first();
				if ( $closeButton.length ) {
					$closeButton.focus();
				} else {
					$modal.attr( 'tabindex', '-1' ).focus();
				}
				
				this.updateFocusTrap( $modal );
			}, 150 );
		}

		navigateToItem( $modal, targetItemId, targetUrl ) {
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
			const $parents = $targetItem.parents( '.menu-item' ).get().reverse();
			$parents.forEach( ( parentItem ) => {
				const $parent = $( parentItem );
				const $toggle = $parent.children( '.menu-modal__submenu-toggle' );
				if ( $toggle.length ) {
					this.performDrillDown( $modal, $parent, $toggle );
				}
			} );

			const $toggle = $targetItem.children( '.menu-modal__submenu-toggle' );
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
			if ( $submenu.length === 0 ) return;

			const isHierarchyMenu = this.isHierarchyMenu( $modal );
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			$parentLi.siblings().hide();
			$toggle.hide();

			if ( ! isHierarchyMenu ) {
				$modal.find( '.menu-modal__level-heading' ).remove();
				$toggle.after( `<h2 class="menu-modal__level-heading">${ $toggle.data( 'parent-title' ) }</h2>` );
			}

			const parentUrl = $toggle.data( 'parent-url' );
			const parentTitle = $toggle.data( 'parent-title' );
			if ( parentUrl && parentTitle && parentUrl !== '#' ) {
				$submenu.find( '.menu-item-overview' ).remove();
				$submenu.prepend( `<li class="menu-item menu-item-overview"><a href="${ parentUrl }">Übersicht: ${ parentTitle }</a></li>` );
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

			$modal.find( '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn' ).show();

			setTimeout( () => {
				this.updateFocusTrap( $modal );
				this.announce( `Navigated to ${parentTitle} submenu` );
			}, 50 );
		}

		toggleSubmenu( $toggle ) {
			const $modal = $toggle.closest( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' );
			const $parentLi = $toggle.closest( '.menu-item' );
			
			if ( this.isHierarchyMenu( $modal ) || $toggle.hasClass( 'menu-modal__submenu-row' ) ) {
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
			const parentTitle = $toggle.data( 'parent-title' ) || $toggle.find( '.menu-modal__item-title' ).text();

			if ( isExpanded ) {
				$submenu.hide();
				$toggle.attr( 'aria-expanded', 'false' ).removeClass( 'expanded' );
				this.announce( `${parentTitle} submenu collapsed` );
			} else {
				$submenu.show();
				$toggle.attr( 'aria-expanded', 'true' ).addClass( 'expanded' );
				this.announce( `${parentTitle} submenu expanded` );
				
				setTimeout( () => {
					this.updateFocusTrap( $toggle.closest( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' ) );
				}, 50 );
			}
		}

		navigateBack() {
			if ( ! this.currentModal ) return;

			const $modal = this.currentModal;
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			if ( navigationStack.length === 0 ) return;

			const lastLevel = navigationStack.pop();
			$modal.data( 'navigation-stack', navigationStack );

			lastLevel.parentLi.siblings().show();
			lastLevel.parentLi.children( '.menu-modal__submenu-toggle' ).show();
			lastLevel.parentLi.children( '.sub-menu' ).hide();

			$modal.find( '.menu-modal__level-heading' ).remove();

			if ( navigationStack.length === 0 ) {
				$modal.find( '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn' ).hide();
			}

			if ( this.isHierarchyMenu( $modal ) ) {
				this.updateBreadcrumbs( $modal );
			}

			this.rehighlightCurrentPage( $modal );
			this.announce( 'Navigated back' );

			setTimeout( () => {
				this.updateFocusTrap( $modal );
			}, 50 );
		}

		navigateToBreadcrumbLevel( targetLevel ) {
			if ( ! this.currentModal ) return;

			const $modal = this.currentModal;
			const navigationStack = $modal.data( 'navigation-stack' ) || [];

			while ( navigationStack.length > targetLevel + 1 ) {
				this.navigateBack();
			}

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

			$submenu.closest( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' )
				.find( '.current-menu-item-focused, .active' )
				.removeClass( 'current-menu-item-focused active' );

			const currentUrl = window.location.href;
			const $overviewLink = $submenu.find( '.menu-item-overview a' ).filter( function () {
				return this.href === currentUrl;
			} ).first();

			if ( $overviewLink.length ) {
				$overviewLink.addClass( 'current-menu-item-focused active' );
				$overviewLink[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center' } );
			}
		}

		rehighlightCurrentPage( $modal ) {
			const currentPath = window.location.pathname.replace( /\/$/, '' );
			const searchPaths = currentPath === '' ? ['/'] : [currentPath, currentPath + '/'];
			
			const $visibleSubmenus = $modal.find( '.sub-menu:visible' );
			$visibleSubmenus.each( ( _, submenu ) => {
				this.highlightOverviewLink( $( submenu ) );
			} );

			const $highlighted = $modal.find( '.current-menu-item-focused, .active' );
			if ( ! $highlighted.length ) {
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
			$item.closest( '.menu-modal, .menu-meta-nav__modal, .menu-website-modal' )
				.find( '.current-menu-item-focused, .active' )
				.removeClass( 'current-menu-item-focused active' );

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
			
			this.announce( 'Menu closed' );
			
			if ( this.closeTimeout ) {
				clearTimeout( this.closeTimeout );
			}
			
			this.resetModalState( $modal );

			$( '.menu-modal__open-btn' ).removeClass( 'is-active' ).attr( 'aria-expanded', 'false' );

			$modal.removeClass( 'is-open' ).attr( 'aria-hidden', 'true' );
			this.closeTimeout = setTimeout( () => {
				$modal.hide();
				this.closeTimeout = null;
			}, 300 );

			if ( this.previouslyFocused ) {
				$( this.previouslyFocused ).focus();
			}
			$( 'body' ).removeClass( 'modal-open' );

			$modal.off( 'keydown.menu-modal' );

			this.currentModal = null;
			this.previouslyFocused = null;
		}

		trapFocus( $modal ) {
			this.updateFocusTrap( $modal );
		}

		updateFocusTrap( $modal ) {
			$modal.off( 'keydown.menu-modal' );

			const $focusableElements = this.getFocusableElements( $modal );
			
			if ( $focusableElements.length === 0 ) return;

			const $firstFocusable = $focusableElements.first();
			const $lastFocusable = $focusableElements.last();

			$modal.on( 'keydown.menu-modal', ( e ) => {
				if ( e.key !== 'Tab' ) return;

				const activeElement = document.activeElement;
				
				if ( ! $modal.find( activeElement ).length && activeElement !== $modal[0] ) {
					return;
				}

				if ( e.shiftKey ) {
					if ( activeElement === $firstFocusable[ 0 ] || ! $modal.find( activeElement ).length ) {
						e.preventDefault();
						$lastFocusable.focus();
						this.announce( 'Moved to last menu item' );
					}
				} else {
					if ( activeElement === $lastFocusable[ 0 ] ) {
						e.preventDefault();
						$firstFocusable.focus();
						this.announce( 'Moved to close button' );
					}
				}
			} );
		}

		getFocusableElements( $modal ) {
			const focusableSelectors = [
				'button:visible:not([disabled])',
				'a:visible[href]',
				'input:visible:not([disabled])',
				'select:visible:not([disabled])',
				'textarea:visible:not([disabled])',
				'[tabindex]:visible:not([tabindex="-1"]):not([disabled])'
			].join(', ');

			const $allFocusable = $modal.find( focusableSelectors );

			const $visibleFocusable = $allFocusable.filter( function() {
				const $el = $( this );
				return $el.is(':visible') && 
					   $el.css('visibility') !== 'hidden' && 
					   $el.css('opacity') !== '0' &&
					   !$el.closest('[style*="display: none"]').length;
			});

			const $closeButton = $modal.find( '.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn' ).filter(':visible');
			const $backButton = $modal.find( '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn' ).filter(':visible');
			const $otherElements = $visibleFocusable.not( $closeButton ).not( $backButton );

			let $orderedElements = $closeButton;
			if ( $backButton.length ) {
				$orderedElements = $orderedElements.add( $backButton );
			}
			$orderedElements = $orderedElements.add( $otherElements );

			return $orderedElements;
		}

		announce( message ) {
			if ( ! this.currentModal ) return;
			
			const $announcements = this.currentModal.find( '.menu-modal__announcements' );
			if ( $announcements.length ) {
				$announcements.text( message );
				setTimeout( () => {
					$announcements.empty();
				}, 1000 );
			}
		}
	}

	$( document ).ready( function () {
		new MenuModal();
	} );
} )( jQuery );
