/**
 * Makes teaser cards clickable and handles load more functionality.
 *
 * Accessibility features:
 * - Keyboard navigation support
 * - Preserves actual link behavior when links are clicked
 * - Adds proper ARIA attributes for screen readers
 */

// Prevent multiple initializations
if ( typeof window.fauTeaserGridInitialized === 'undefined' ) {
	window.fauTeaserGridInitialized = true;

	document.addEventListener( 'DOMContentLoaded', function () {
		// Initialize teaser card clickability
		initializeTeaserCards();

		// Initialize load more functionality
		initializeLoadMoreButtons();
	} );

	function initializeTeaserCards() {
		// Find all teaser items with data-href attribute that haven't been initialized
		const teaserItems = document.querySelectorAll(
			'.teaser-item[data-href]:not([data-teaser-initialized])'
		);

		teaserItems.forEach( function ( item ) {
			item.setAttribute( 'data-teaser-initialized', 'true' );

			const href = item.getAttribute( 'data-href' );

			// Set cursor to pointer to indicate clickability
			item.style.cursor = 'pointer';

			// Add visual feedback on focus for accessibility
			item.addEventListener( 'focus', function () {
				this.classList.add( 'is-focused' );
			} );

			item.addEventListener( 'blur', function () {
				this.classList.remove( 'is-focused' );
			} );

			// Make the whole card clickable
			item.addEventListener( 'click', function ( e ) {
				// Prevent card click if user clicked on an actual link inside the card
				if (
					e.target.tagName.toLowerCase() === 'a' ||
					e.target.closest( 'a' )
				) {
					return;
				}

				// Navigate to the post/page
				window.location.href = href;
			} );

			// Handle keyboard navigation
			item.addEventListener( 'keydown', function ( e ) {
				// Navigate on Enter key or Space key (for button role)
				if (
					e.key === 'Enter' ||
					e.key === ' ' ||
					e.keyCode === 13 ||
					e.keyCode === 32
				) {
					e.preventDefault(); // Prevent page scroll on space
					window.location.href = href;
				}
			} );
		} );
	}

	function initializeLoadMoreButtons() {
		// Handle Load More functionality
		const loadMoreButtons = document.querySelectorAll(
			'.fau-teaser-grid__load-more-button:not([data-loadmore-initialized])'
		);

		loadMoreButtons.forEach( function ( button ) {
			button.setAttribute( 'data-loadmore-initialized', 'true' );

			button.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				// Prevent multiple clicks while loading
				if (
					button.disabled ||
					button.classList.contains( 'loading' )
				) {
					return;
				}

				// Get the grid container
				const gridContainer = button.closest( '[data-grid-id]' );
				if ( ! gridContainer ) {
					console.error( 'Grid container not found' );
					return;
				}

				// Check if fauTeaserGrid is available
				if ( typeof window.fauTeaserGrid === 'undefined' ) {
					console.error( 'fauTeaserGrid object not found' );
					return;
				}

				// Get data attributes
				const variant = gridContainer.getAttribute( 'data-variant' );
				const category = gridContainer.getAttribute( 'data-category' );
				const postsPerPage = gridContainer.getAttribute(
					'data-posts-per-page'
				);
				const displayStyle =
					gridContainer.getAttribute( 'data-display-style' );
				const teaserLayout =
					gridContainer.getAttribute( 'data-teaser-layout' );
				const orderBy = gridContainer.getAttribute( 'data-order-by' );
				const order = gridContainer.getAttribute( 'data-order' );
				const headingLevel =
					gridContainer.getAttribute( 'data-heading-level' );
				const nonce = gridContainer.getAttribute( 'data-nonce' );

				// Get current page from button
				const currentPage =
					parseInt( button.getAttribute( 'data-page' ) ) || 1;
				const nextPage = currentPage + 1;

				// Show loading state
				const spinner =
					button.parentNode.querySelector( '.load-more-spinner' );
				button.disabled = true;
				button.classList.add( 'loading' );
				button.style.display = 'none';
				if ( spinner ) {
					spinner.classList.add( 'loading' );
				}

				// Prepare AJAX data
				const formData = new FormData();
				formData.append( 'action', 'fau_load_more_posts' );
				formData.append( 'nonce', nonce );
				formData.append( 'variant', variant );
				formData.append( 'category', category );
				formData.append( 'posts_per_page', postsPerPage );
				formData.append( 'page', nextPage );
				formData.append( 'display_style', displayStyle );
				formData.append( 'teaser_layout', teaserLayout );
				formData.append( 'order_by', orderBy );
				formData.append( 'order', order );
				formData.append( 'heading_level', headingLevel );

				// Make AJAX request
				fetch( window.fauTeaserGrid.ajaxUrl, {
					method: 'POST',
					body: formData,
					credentials: 'same-origin',
				} )
					.then( function ( response ) {
						if ( ! response.ok ) {
							throw new Error(
								'Network response was not ok: ' +
									response.status
							);
						}
						return response.json();
					} )
					.then( function ( data ) {
						if ( data.success && data.data && data.data.html ) {
							// Find the teaser grid
							const teaserGrid =
								gridContainer.querySelector(
									'.fau-teaser-grid'
								);
							if ( teaserGrid ) {
								// Create a temporary container to parse the HTML
								const tempDiv = document.createElement( 'div' );
								tempDiv.innerHTML = data.data.html;

								// Append new items to the grid
								while ( tempDiv.firstChild ) {
									teaserGrid.appendChild(
										tempDiv.firstChild
									);
								}

								// Initialize clickable behavior for new items
								initializeTeaserCards();
							}

							// Update button state
							if ( data.data.has_more ) {
								button.setAttribute( 'data-page', nextPage );
								button.disabled = false;
								button.classList.remove( 'loading' );
								button.style.display = 'block';
							} else {
								// No more posts, hide the button wrapper
								const buttonWrapper = button.closest(
									'.fau-teaser-grid__load-more-wrapper'
								);
								if ( buttonWrapper ) {
									buttonWrapper.style.display = 'none';
								}
							}
						} else {
							button.disabled = false;
							button.classList.remove( 'loading' );
							button.style.display = 'block';
						}

						// Hide loading spinner
						if ( spinner ) {
							spinner.classList.remove( 'loading' );
						}
					} )
					.catch( function () {
						button.disabled = false;
						button.classList.remove( 'loading' );
						button.style.display = 'block';

						// Hide loading spinner
						if ( spinner ) {
							spinner.classList.remove( 'loading' );
						}
					} );
			} );
		} );
	}
}
