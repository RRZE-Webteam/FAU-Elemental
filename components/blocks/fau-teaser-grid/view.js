/**
 * Frontend JavaScript for FAU Teaser Grid block
 * Handles integrated pagination and load-more functionality
 */

/* global fauTeaserGrid */

document.addEventListener( 'DOMContentLoaded', function () {
	// Initialize all FAU Teaser Grid blocks on the page
	const teaserGrids = document.querySelectorAll(
		'.wp-block-fau-elemental-fau-teaser-grid'
	);

	teaserGrids.forEach( initializeTeaserGrid );
} );

function initializeTeaserGrid( gridContainer ) {
	const teaserGrid = gridContainer.querySelector( '.fau-teaser-grid' );

	if ( ! teaserGrid || teaserGrid.initialized ) {
		return;
	}

	// Read configuration from data attributes
	const showPagination =
		gridContainer.getAttribute( 'data-show-pagination' ) === 'true';
	const paginationType =
		gridContainer.getAttribute( 'data-pagination-type' ) || 'numbers';
	const postsPerPage =
		parseInt( gridContainer.getAttribute( 'data-posts-per-page' ) ) || 6;
	const currentPage =
		parseInt( gridContainer.getAttribute( 'data-current-page' ) ) || 1;

	// Skip initialization if no pagination
	if ( ! showPagination ) {
		return;
	}

	// Initialize load-more functionality if needed
	if ( paginationType === 'load-more' ) {
		initializeLoadMore( gridContainer );
	}

	// Store pagination data on the grid element
	teaserGrid.paginationData = {
		postsPerPage,
		currentPage,
		paginationType,

		showPagination,
	};
	teaserGrid.initialized = true;
}

function initializeLoadMore( gridContainer ) {
	const loadMoreBtn = gridContainer.querySelector( '.load-more-button' );

	if ( ! loadMoreBtn ) {
		return;
	}

	loadMoreBtn.addEventListener( 'click', function ( e ) {
		e.preventDefault();

		const gridId = loadMoreBtn.getAttribute( 'data-grid-id' );
		const currentPage = parseInt(
			loadMoreBtn.getAttribute( 'data-current-page' )
		);
		const nextPage = currentPage + 1;

		// Disable button during loading
		loadMoreBtn.disabled = true;
		loadMoreBtn.textContent =
			loadMoreBtn.getAttribute( 'data-loading-text' ) || 'Loading…';

		// Make AJAX request to load more posts
		loadMorePosts( gridId, nextPage, loadMoreBtn );
	} );
}

function loadMorePosts( gridId, page, loadMoreBtn ) {
	const gridContainer = document.getElementById( gridId );

	if ( ! gridContainer ) {
		console.error( 'Grid container not found for ID:', gridId );
		return;
	}

	const teaserGrid = gridContainer.querySelector( '.fau-teaser-grid' );
	const nonce = gridContainer.getAttribute( 'data-nonce' );
	const variant = gridContainer.getAttribute( 'data-variant' );
	const postsPerPage = gridContainer.getAttribute( 'data-posts-per-page' );
	const selectedCategory = gridContainer.getAttribute( 'data-category' );
	const selectedTags = gridContainer.getAttribute( 'data-tags' );
	const selectedAuthor = gridContainer.getAttribute( 'data-author' );
	const selectedYear = gridContainer.getAttribute( 'data-year' );
	const selectedMonth = gridContainer.getAttribute( 'data-month' );
	const selectedDay = gridContainer.getAttribute( 'data-day' );
	const orderBy = gridContainer.getAttribute( 'data-order-by' );
	const order = gridContainer.getAttribute( 'data-order' );
	const headingLevel = gridContainer.getAttribute( 'data-heading-level' );
	const displayStyle = gridContainer.getAttribute( 'data-display-style' );
	const teaserLayout = gridContainer.getAttribute( 'data-teaser-layout' );

	// Prepare form data
	const formData = new FormData();
	formData.append( 'action', 'fau_load_more_posts' );
	formData.append( 'nonce', nonce );
	formData.append( 'variant', variant );
	formData.append( 'page', page );
	formData.append( 'posts_per_page', postsPerPage );
	formData.append( 'selected_category', selectedCategory );
	formData.append( 'selected_tags', selectedTags );
	formData.append( 'selected_author', selectedAuthor );
	formData.append( 'selected_year', selectedYear );
	formData.append( 'selected_month', selectedMonth );
	formData.append( 'selected_day', selectedDay );
	formData.append( 'order_by', orderBy );
	formData.append( 'order', order );
	formData.append( 'heading_level', headingLevel );
	formData.append( 'display_style', displayStyle );
	formData.append( 'teaser_layout', teaserLayout );

	// Make AJAX request
	fetch( fauTeaserGrid.ajaxUrl, {
		method: 'POST',
		body: formData,
	} )
		.then( ( response ) => response.json() )
		.then( ( data ) => {
			if ( data.success ) {
				// Append new posts to the grid
				teaserGrid.insertAdjacentHTML( 'beforeend', data.data.html );

				// Update button state
				const currentPage = parseInt(
					loadMoreBtn.getAttribute( 'data-current-page' )
				);
				const totalPages = parseInt(
					loadMoreBtn.getAttribute( 'data-total-pages' )
				);
				const nextPage = currentPage + 1;

				loadMoreBtn.setAttribute( 'data-current-page', nextPage );
				loadMoreBtn.disabled = false;
				loadMoreBtn.textContent =
					loadMoreBtn.getAttribute( 'data-default-text' ) ||
					'Load More';

				// Hide button if no more pages
				if ( nextPage >= totalPages ) {
					loadMoreBtn.style.display = 'none';
				}

				// Trigger event for analytics or other integrations
				document.dispatchEvent(
					new CustomEvent( 'fau-load-more-complete', {
						detail: {
							gridId,
							currentPage: nextPage,
							totalPages,
							newItemsCount: data.data.count,
						},
					} )
				);
			} else {
				console.error( 'Error loading more posts:', data.data );
				loadMoreBtn.disabled = false;
				loadMoreBtn.textContent =
					fauTeaserGrid.strings?.errorLoadingPosts ||
					'Error loading posts';
			}
		} )
		.catch( ( error ) => {
			console.error( 'AJAX error:', error );
			loadMoreBtn.disabled = false;
			loadMoreBtn.textContent =
				fauTeaserGrid.strings?.errorLoadingPosts ||
				'Error loading posts';
		} );
}

// Handle pagination navigation (for numbers and simple pagination)
function handlePaginationNavigation() {
	const paginationLinks = document.querySelectorAll(
		'.fau-pagination .page-number:not(.disabled)'
	);

	paginationLinks.forEach( ( link ) => {
		link.addEventListener( 'click', function () {
			// Let the browser handle the navigation naturally
			// This function is here for future enhancements if needed
		} );
	} );
}

// Initialize pagination navigation
document.addEventListener( 'DOMContentLoaded', function () {
	handlePaginationNavigation();
} );

// Scroll to top of grid after pagination (for better UX)
function scrollToGrid( gridId ) {
	const gridContainer = document.getElementById( gridId );

	if ( gridContainer ) {
		gridContainer.scrollIntoView( {
			behavior: 'smooth',
			block: 'start',
		} );
	}
}

// Check if we need to scroll to grid on page load (after pagination)
document.addEventListener( 'DOMContentLoaded', function () {
	const urlParams = new URLSearchParams( window.location.search );
	const paged = urlParams.get( 'paged' );

	if ( paged && paged > 1 ) {
		// Find the first teaser grid and scroll to it
		const firstGrid = document.querySelector(
			'.wp-block-fau-elemental-fau-teaser-grid'
		);
		if ( firstGrid ) {
			setTimeout( () => {
				firstGrid.scrollIntoView( {
					behavior: 'smooth',
					block: 'start',
				} );
			}, 100 );
		}
	}
} );

// Export functions for use by other scripts
window.fauTeaserGrid = {
	initializeTeaserGrid,
	loadMorePosts,
	scrollToGrid,
	ajaxUrl:
		typeof fauTeaserGrid !== 'undefined'
			? fauTeaserGrid.ajaxUrl
			: '/wp-admin/admin-ajax.php',
};
