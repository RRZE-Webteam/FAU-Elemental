/**
 * Makes teaser cards clickable and handles load more functionality.
 *
 * Accessibility features:
 * - Keyboard navigation support
 * - Preserves actual link behavior when links are clicked
 * - Adds proper ARIA attributes for screen readers
 */
document.addEventListener( 'DOMContentLoaded', function () {
	// Find all teaser items with data-href attribute
	const teaserItems = document.querySelectorAll( '.teaser-item[data-href]' );

	teaserItems.forEach( function ( item ) {
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

	// Handle Load More functionality
	const loadMoreButtons = document.querySelectorAll( '.fau-teaser-grid__load-more-button' );

	loadMoreButtons.forEach( function ( button ) {
		button.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			// Get the grid container
			const gridContainer = button.closest( '[data-grid-id]' );
			if ( ! gridContainer ) {
				return;
			}

			// Get data attributes
			const gridId = gridContainer.getAttribute( 'data-grid-id' );
			const variant = gridContainer.getAttribute( 'data-variant' );
			const category = gridContainer.getAttribute( 'data-category' );
			const postsPerPage = gridContainer.getAttribute( 'data-posts-per-page' );
			const displayStyle = gridContainer.getAttribute( 'data-display-style' );
			const teaserLayout = gridContainer.getAttribute( 'data-teaser-layout' );
			const orderBy = gridContainer.getAttribute( 'data-order-by' );
			const order = gridContainer.getAttribute( 'data-order' );
			const headingLevel = gridContainer.getAttribute( 'data-heading-level' );
			const nonce = gridContainer.getAttribute( 'data-nonce' );

			// Get current page from button
			const currentPage = parseInt( button.getAttribute( 'data-page' ) ) || 1;
			const nextPage = currentPage + 1;
			const maxPages = parseInt( button.getAttribute( 'data-max-pages' ) ) || 1;

			// Show loading state
			const spinner = button.parentNode.querySelector( '.load-more-spinner' );
			button.style.display = 'none';
			if ( spinner ) {
				spinner.style.display = 'block';
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
			fetch( fauTeaserGrid.ajaxUrl, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( data ) {
				if ( data.success && data.data.html ) {
					// Find the teaser grid
					const teaserGrid = gridContainer.querySelector( '.fau-teaser-grid' );
					if ( teaserGrid ) {
						// Create a temporary container to parse the HTML
						const tempDiv = document.createElement( 'div' );
						tempDiv.innerHTML = data.data.html;

						// Append new items to the grid
						while ( tempDiv.firstChild ) {
							teaserGrid.appendChild( tempDiv.firstChild );
						}

						// Initialize clickable behavior for new items
						const newTeaserItems = teaserGrid.querySelectorAll( '.teaser-item[data-href]:not([data-initialized])' );
						newTeaserItems.forEach( function ( item ) {
							item.setAttribute( 'data-initialized', 'true' );
							
							const href = item.getAttribute( 'data-href' );
							item.style.cursor = 'pointer';

							item.addEventListener( 'focus', function () {
								this.classList.add( 'is-focused' );
							} );

							item.addEventListener( 'blur', function () {
								this.classList.remove( 'is-focused' );
							} );

							item.addEventListener( 'click', function ( e ) {
								if (
									e.target.tagName.toLowerCase() === 'a' ||
									e.target.closest( 'a' )
								) {
									return;
								}
								window.location.href = href;
							} );

							item.addEventListener( 'keydown', function ( e ) {
								if (
									e.key === 'Enter' ||
									e.key === ' ' ||
									e.keyCode === 13 ||
									e.keyCode === 32
								) {
									e.preventDefault();
									window.location.href = href;
								}
							} );
						} );
					}

					// Update button state
					if ( data.data.has_more ) {
						button.setAttribute( 'data-page', nextPage );
						button.style.display = 'block';
					} else {
						// No more posts, hide the button wrapper
						const buttonWrapper = button.closest( '.fau-teaser-grid__load-more-wrapper' );
						if ( buttonWrapper ) {
							buttonWrapper.style.display = 'none';
						}
					}
				} else {
					// Show error message
					console.error( 'Failed to load more posts:', data );
					button.style.display = 'block';
				}

				// Hide loading spinner
				if ( spinner ) {
					spinner.style.display = 'none';
				}
			} )
			.catch( function ( error ) {
				console.error( 'Error loading more posts:', error );
				button.style.display = 'block';
				
				// Hide loading spinner
				if ( spinner ) {
					spinner.style.display = 'none';
				}
			} );
		} );
	} );
} );
