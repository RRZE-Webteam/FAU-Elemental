/**
 * Frontend JavaScript for FAU Teaser Grid block
 * Handles client-side pagination when integrated with filter/pagination blocks
 */

document.addEventListener('DOMContentLoaded', function() {
	// Initialize all FAU Teaser Grid blocks on the page
	const teaserGrids = document.querySelectorAll('.wp-block-fau-elemental-fau-teaser-grid');
	
	teaserGrids.forEach(initializeTeaserGrid);
});

function initializeTeaserGrid(gridContainer) {
	const teaserGrid = gridContainer.querySelector('.fau-teaser-grid');
	
	if (!teaserGrid) {
		return;
	}
	
	const isJsPagination = teaserGrid.getAttribute('data-js-pagination') === 'true';
	// Read posts per page from the wrapper container (where it's stored)
	const postsPerPage = parseInt(gridContainer.getAttribute('data-posts-per-page')) || 6;
	const customBlockId = gridContainer.getAttribute('data-custom-block-id');
	
	console.log('DEBUG: Teaser Grid Initialization');
	console.log('DEBUG: - isJsPagination:', isJsPagination);
	console.log('DEBUG: - postsPerPage:', postsPerPage);
	console.log('DEBUG: - customBlockId:', customBlockId);
	console.log('DEBUG: - filterBlockId:', gridContainer.getAttribute('data-filter-block-id'));
	console.log('DEBUG: - paginationBlockId:', gridContainer.getAttribute('data-pagination-block-id'));
	
	if (!isJsPagination) {
		console.log('DEBUG: Grid uses server-side pagination, skipping JS pagination setup');
		return; // This grid uses server-side pagination
	}
	
	console.log('DEBUG: Initializing JS pagination for grid:', customBlockId);
	
	// Store pagination data on the grid element
	teaserGrid.jsPaginationData = {
		postsPerPage: postsPerPage,
		currentPage: 1,
		totalItems: 0
	};
	
	// Count initial items
	updatePaginationDisplay(teaserGrid);
	
	// Listen for pagination events
	if (customBlockId) {
		// Listen for pagination clicks from associated pagination blocks
		document.addEventListener('fau-pagination-change', function(e) {
			if (e.detail.gridId === customBlockId) {
				console.log('DEBUG: Received pagination change event for page:', e.detail.page);
				showPage(teaserGrid, e.detail.page);
			}
		});
		
		// Listen for filter changes that might affect pagination
		document.addEventListener('fau-filter-update', function(e) {
			if (e.detail.gridId === customBlockId) {
				console.log('DEBUG: Received filter update event, resetting to page 1');
				console.log('DEBUG: Visible items after filter:', e.detail.visibleCount);
				
				// Reset to page 1 when filters change
				teaserGrid.jsPaginationData.currentPage = 1;
				
				// Update pagination display to reflect filtered items
				updatePaginationDisplay(teaserGrid);
			}
		});
	}
}

function showPage(teaserGrid, pageNumber) {
	const paginationData = teaserGrid.jsPaginationData;
	if (!paginationData) {
		return;
	}
	
	const postsPerPage = paginationData.postsPerPage;
	const startIndex = (pageNumber - 1) * postsPerPage;
	const endIndex = startIndex + postsPerPage;
	
	// Get all visible teaser items (not filtered out)
	const allItems = Array.from(teaserGrid.querySelectorAll('.teaser-item:not(.filtered-out)'));
	
	console.log(`DEBUG: Showing page ${pageNumber}, items ${startIndex} to ${endIndex - 1} of ${allItems.length} visible items`);
	
	// First, hide ALL items (including filtered ones)
	const allTeaserItems = teaserGrid.querySelectorAll('.teaser-item');
	allTeaserItems.forEach(item => {
		item.classList.add('js-paginated-hidden');
		item.style.display = 'none';
	});
	
	// Then show only the items for the current page (from visible items only)
	allItems.forEach((item, index) => {
		if (index >= startIndex && index < endIndex) {
			// Show this item
			item.classList.remove('js-paginated-hidden');
			item.style.display = '';
		}
	});
	
	// Update current page
	paginationData.currentPage = pageNumber;
	
	// Emit event for pagination blocks to update
	const customBlockId = teaserGrid.closest('.wp-block-fau-elemental-fau-teaser-grid').getAttribute('data-custom-block-id');
	if (customBlockId) {
		document.dispatchEvent(new CustomEvent('fau-grid-page-change', {
			detail: {
				gridId: customBlockId,
				currentPage: pageNumber,
				totalPages: Math.ceil(allItems.length / postsPerPage)
			}
		}));
	}
}

function updatePaginationDisplay(teaserGrid) {
	const paginationData = teaserGrid.jsPaginationData;
	if (!paginationData) {
		return;
	}
	
	// Count visible items (not filtered out)
	const visibleItems = teaserGrid.querySelectorAll('.teaser-item:not(.filtered-out)');
	const totalPages = Math.ceil(visibleItems.length / paginationData.postsPerPage);
	
	paginationData.totalItems = visibleItems.length;
	
	console.log(`DEBUG: Grid has ${visibleItems.length} visible items, ${totalPages} total pages`);
	
	// Show the first page by default
	showPage(teaserGrid, paginationData.currentPage);
	
	// Emit event for pagination blocks to update
	const customBlockId = teaserGrid.closest('.wp-block-fau-elemental-fau-teaser-grid').getAttribute('data-custom-block-id');
	if (customBlockId) {
		document.dispatchEvent(new CustomEvent('fau-grid-pagination-ready', {
			detail: {
				gridId: customBlockId,
				totalPages: totalPages,
				currentPage: paginationData.currentPage,
				totalItems: visibleItems.length
			}
		}));
	}
}

// Export functions for use by other scripts
window.fauTeaserGrid = {
	initializeTeaserGrid,
	showPage,
	updatePaginationDisplay
};
