/**
 * Frontend JavaScript for FAU Teaser Grid block
 * Handles client-side pagination when integrated with filter/pagination
 * blocks
 */

document.addEventListener( 'DOMContentLoaded', function () {
	// Initialize all FAU Teaser Grid blocks on the page
	const teaserGrids = document.querySelectorAll(
		'.wp-block-fau-elemental-fau-teaser-grid'
	);

	teaserGrids.forEach( initializeTeaserGrid );
} );

function initializeTeaserGrid( gridContainer ) {
	const filterBlockId = gridContainer.getAttribute( 'data-filter-block-id' );

	const teaserGrid = gridContainer.querySelector( '.fau-teaser-grid' );

	if ( ! teaserGrid ) {
		return;
	}

	const isJsPagination =
		teaserGrid.getAttribute( 'data-js-pagination' ) === 'true';

	// If the grid is controlled by a filter block AND this is server-side pagination,
	// do not initialize client-side JS - the filter block's view.js will handle all interactions.
	// But if this is JavaScript pagination, we need to initialize to emit pagination events.
	if ( filterBlockId && ! isJsPagination ) {
		return;
	}

	if ( ! isJsPagination ) {
		return; // This grid uses server-side pagination
	}
	
	// Read posts per page from the wrapper container (where it's stored)
	const postsPerPage =
		parseInt( gridContainer.getAttribute( 'data-posts-per-page' ) ) || 6;
	const customBlockId = gridContainer.getAttribute( 'data-custom-block-id' );

	// Store pagination data on the grid element
	teaserGrid.jsPaginationData = {
		postsPerPage,
		currentPage: 1,
		totalItems: 0,
	};

	// Count initial items
	updatePaginationDisplay( teaserGrid );

	// Listen for pagination events
	if ( customBlockId ) {
		// Listen for pagination clicks from associated pagination blocks
		document.addEventListener( 'fau-pagination-change', function ( e ) {
			if ( e.detail.gridId === customBlockId ) {
				showPage( teaserGrid, e.detail.page );
			}
		} );

		// Listen for filter changes that might affect pagination
		document.addEventListener( 'fau-filter-update', function ( e ) {
			if ( e.detail.gridId === customBlockId ) {
				// Reset to page 1 when filters change (if requested)
				if ( e.detail.resetToPage1 ) {
					teaserGrid.jsPaginationData.currentPage = 1;
				}

				// Update pagination display to reflect filtered items
				updatePaginationDisplay( teaserGrid );
			}
		} );

		// Listen for filter clear event
		document.addEventListener( 'fau-filter-clear', function ( e ) {
			if ( e.detail.gridId === customBlockId ) {
				// Reset to page 1
				teaserGrid.jsPaginationData.currentPage = 1;

				// Remove filtered-out class from all items
				const allItems = teaserGrid.querySelectorAll( '.teaser-item' );
				allItems.forEach( ( item ) => {
					item.classList.remove( 'filtered-out' );
					item.style.display = '';
				} );

				// Update pagination display
				updatePaginationDisplay( teaserGrid );
			}
		} );
	}
}

function showPage( teaserGrid, pageNumber ) {
	const paginationData = teaserGrid.jsPaginationData;
	if ( ! paginationData ) {
		return;
	}

	const postsPerPage = paginationData.postsPerPage;
	const startIndex = ( pageNumber - 1 ) * postsPerPage;
	const endIndex = startIndex + postsPerPage;

	// Get all teaser items (including filtered ones)
	const allTeaserItems = teaserGrid.querySelectorAll( '.teaser-item' );
	
	// Get all visible teaser items (not filtered out)
	const visibleItems = Array.from(
		teaserGrid.querySelectorAll( '.teaser-item:not(.filtered-out)' )
	);

	// First, hide ALL items (including filtered ones)
	allTeaserItems.forEach( ( item ) => {
		item.classList.add( 'js-paginated-hidden' );
		item.style.display = 'none';
	} );

	// Then show only the items for the current page (from visible items only)
	visibleItems.forEach( ( item, index ) => {
		if ( index >= startIndex && index < endIndex ) {
			// Show this item
			item.classList.remove( 'js-paginated-hidden' );
			item.style.display = '';
		}
	} );

	// Update current page
	paginationData.currentPage = pageNumber;

	// Emit event for pagination blocks to update
	const customBlockId = teaserGrid
		.closest( '.wp-block-fau-elemental-fau-teaser-grid' )
		.getAttribute( 'data-custom-block-id' );
	if ( customBlockId ) {
		document.dispatchEvent(
			new CustomEvent( 'fau-grid-page-change', {
				detail: {
					gridId: customBlockId,
					currentPage: pageNumber,
					totalPages: Math.ceil( visibleItems.length / postsPerPage ),
				},
			} )
		);
	}
}

function updatePaginationDisplay( teaserGrid ) {
	const paginationData = teaserGrid.jsPaginationData;
	if ( ! paginationData ) {
		return;
	}

	// Count visible items (not filtered out)
	const visibleItems = teaserGrid.querySelectorAll(
		'.teaser-item:not(.filtered-out)'
	);
	const totalPages = Math.ceil(
		visibleItems.length / paginationData.postsPerPage
	);

	paginationData.totalItems = visibleItems.length;

	// Ensure current page is valid for the number of visible items
	if ( paginationData.currentPage > totalPages && totalPages > 0 ) {
		paginationData.currentPage = totalPages;
	} else if ( paginationData.currentPage < 1 ) {
		paginationData.currentPage = 1;
	}

	// Show the appropriate page
	showPage( teaserGrid, paginationData.currentPage );

	// Emit event for pagination blocks to update
	const customBlockId = teaserGrid
		.closest( '.wp-block-fau-elemental-fau-teaser-grid' )
		.getAttribute( 'data-custom-block-id' );
	if ( customBlockId ) {
		document.dispatchEvent(
			new CustomEvent( 'fau-grid-pagination-ready', {
				detail: {
					gridId: customBlockId,
					totalPages,
					currentPage: paginationData.currentPage,
					totalItems: visibleItems.length,
				},
			} )
		);
	}
}

// Export functions for use by other scripts
window.fauTeaserGrid = {
	initializeTeaserGrid,
	showPage,
	updatePaginationDisplay,
};
