/**
 * Frontend JavaScript for FAU Pagination block
 * Handles pagination clicks and communicates with associated teaser grid blocks
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all FAU Pagination blocks on the page
    const paginationBlocks = document.querySelectorAll('.wp-block-fau-elemental-fau-pagination');
    
    paginationBlocks.forEach(initializePaginationBlock);
});

function initializePaginationBlock(paginationElement) {
    const blockId = paginationElement.getAttribute('data-block-id');
    const gridBlockId = paginationElement.getAttribute('data-grid-block-id');
    const filterBlockId = paginationElement.getAttribute('data-filter-block-id');
    
    // Find associated teaser grid
    const associatedGrid = findAssociatedGrid(gridBlockId);
    
    if (!associatedGrid) {
        console.log('WARNING: Pagination block found but no associated grid found for:', gridBlockId);
        return;
    }
    
    console.log('DEBUG: Pagination initialized with grid:', gridBlockId);
    
    // Function to attach click handlers to pagination controls
    function attachClickHandlers() {
        // Remove any existing handlers first
        const existingHandlers = paginationElement.querySelectorAll('[data-pagination-handler]');
        existingHandlers.forEach(el => {
            el.removeAttribute('data-pagination-handler');
        });
        
        // Add click handlers to ALL pagination elements (including spans)
        const paginationControls = paginationElement.querySelectorAll('.page-nav, .page-numbers a, .page-numbers span.page-number, .page-numbers button');
        
        paginationControls.forEach(control => {
            // Skip if it's a disabled element or ellipsis
            if (control.classList.contains('disabled') || control.classList.contains('page-ellipsis')) {
                return;
            }
            
            // Mark as having handler
            control.setAttribute('data-pagination-handler', 'true');
            
            // Make spans clickable by adding pointer cursor
            if (control.tagName === 'SPAN' && control.classList.contains('page-number')) {
                control.style.cursor = 'pointer';
            }
            
            control.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handlePaginationClick(e, associatedGrid, paginationElement);
            });
        });
    }
    
    // Initial attachment
    attachClickHandlers();
    
    // Re-attach handlers when pagination is updated
    paginationElement.addEventListener('pagination-updated', attachClickHandlers);
    
    // Handle pagination navigation via keyboard
    paginationElement.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' && !e.ctrlKey && !e.metaKey) {
            const prevButton = paginationElement.querySelector('.page-nav.prev:not(.disabled)');
            if (prevButton) {
                e.preventDefault();
                prevButton.click();
            }
        } else if (e.key === 'ArrowRight' && !e.ctrlKey && !e.metaKey) {
            const nextButton = paginationElement.querySelector('.page-nav.next:not(.disabled)');
            if (nextButton) {
                e.preventDefault();
                nextButton.click();
            }
        }
    });
}

function findAssociatedGrid(gridBlockId) {
    if (!gridBlockId) {
        return null;
    }
    
    // Try multiple selectors to find the grid
    let grid = document.querySelector(`[data-grid-id="${gridBlockId}"]`);
    
    if (!grid) {
        grid = document.querySelector(`[data-block-id="${gridBlockId}"]`);
    }
    
    if (!grid) {
        grid = document.querySelector(`#${gridBlockId}`);
    }
    
    if (!grid) {
        // Look for a teaser grid with a custom block ID attribute
        grid = document.querySelector(`[data-custom-block-id="${gridBlockId}"]`);
    }
    
    return grid;
}

function handlePaginationClick(e, gridContainer, paginationContainer) {
    const clickedElement = e.target.closest('a, button, span.page-number, .page-nav');
    
    if (!clickedElement) {
        return;
    }
    
    // Skip if disabled or ellipsis
    if (clickedElement.classList.contains('disabled') || clickedElement.classList.contains('page-ellipsis')) {
        return;
    }
    
    // Get page number from the clicked element
    let targetPage = 1;
    const currentPage = parseInt(paginationContainer.getAttribute('data-current-page')) || 1;
    const totalPages = parseInt(paginationContainer.getAttribute('data-total-pages')) || 1;
    
    if (clickedElement.classList.contains('prev')) {
        targetPage = Math.max(1, currentPage - 1);
    } else if (clickedElement.classList.contains('next')) {
        targetPage = Math.min(totalPages, currentPage + 1);
    } else {
        // Direct page number click
        const pageText = clickedElement.textContent.trim();
        const pageNum = parseInt(pageText);
        if (!isNaN(pageNum)) {
            targetPage = pageNum;
        }
    }
    
    // Don't do anything if we're already on this page
    if (targetPage === currentPage) {
        console.log('DEBUG: Already on page', targetPage);
        return;
    }
    
    console.log('DEBUG: Navigating from page', currentPage, 'to page', targetPage);
    
    // Check if this is a JavaScript pagination grid
    const isJsPagination = gridContainer.querySelector('[data-js-pagination="true"]');
    
    if (isJsPagination) {
        // Use JavaScript pagination
        const customBlockId = gridContainer.getAttribute('data-custom-block-id');
        if (customBlockId) {
            // Emit event for teaser grid to handle
            document.dispatchEvent(new CustomEvent('fau-pagination-change', {
                detail: {
                    gridId: customBlockId,
                    page: targetPage
                }
            }));
            
            // Update pagination state immediately
            updatePaginationState(paginationContainer, targetPage);
        }
    } else {
        // Use traditional AJAX pagination
        updateGridForPage(gridContainer, targetPage, paginationContainer);
    }
}

function updateGridForPage(gridContainer, targetPage, paginationContainer) {
    // Show loading state
    showLoadingState(gridContainer, true);
    
    // Get grid attributes needed for the AJAX call
    const variant = gridContainer.getAttribute('data-variant') || 'post';
    const category = gridContainer.getAttribute('data-category') || '0';
    const postsPerPage = gridContainer.getAttribute('data-posts-per-page') || '6';
    const displayStyle = gridContainer.getAttribute('data-display-style') || 'teaser-grid';
    const teaserLayout = gridContainer.getAttribute('data-teaser-layout') || '3m';
    const orderBy = gridContainer.getAttribute('data-order-by') || 'date';
    const order = gridContainer.getAttribute('data-order') || 'DESC';
    const headingLevel = gridContainer.getAttribute('data-heading-level') || 'h4';
    const nonce = gridContainer.getAttribute('data-nonce');
    
    if (!nonce) {
        console.error('No nonce found for grid update');
        showLoadingState(gridContainer, false);
        return;
    }
    
    // Prepare AJAX data
    const formData = new FormData();
    formData.append('action', 'fau_load_more_content');
    formData.append('nonce', nonce);
    formData.append('blockType', 'fau-teaser-grid');
    formData.append('page', targetPage);
    formData.append('attributes', JSON.stringify({
        variant: variant,
        category: parseInt(category),
        postsPerPage: parseInt(postsPerPage),
        displayStyle: displayStyle,
        teaserLayout: teaserLayout,
        orderBy: orderBy,
        order: order,
        headingLevel: headingLevel
    }));
    
    // Make AJAX request
    fetch(window.location.origin + '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(html => {
        // Find the teaser grid within the container
        const teaserGrid = gridContainer.querySelector('.fau-teaser-grid');
        
        if (teaserGrid && html.trim()) {
            // Replace grid content
            teaserGrid.innerHTML = html;
            
            // Update pagination state
            updatePaginationState(paginationContainer, targetPage);
            
            // Scroll to grid (with some offset for better UX)
            const gridTop = gridContainer.getBoundingClientRect().top + window.pageYOffset - 20;
            window.scrollTo({
                top: gridTop,
                behavior: 'smooth'
            });
            
            // Initialize teaser cards if the function exists
            if (typeof initializeTeaserCards === 'function') {
                initializeTeaserCards();
            }
        }
        
        showLoadingState(gridContainer, false);
    })
    .catch(error => {
        console.error('Error updating grid:', error);
        showLoadingState(gridContainer, false);
    });
}

function updatePaginationState(paginationContainer, currentPage) {
    // Update current page data attribute
    paginationContainer.setAttribute('data-current-page', currentPage);
    
    // Get total pages
    const totalPages = parseInt(paginationContainer.getAttribute('data-total-pages')) || 1;
    
    // Find the page numbers container or placeholder
    const pageNumbersContainer = paginationContainer.querySelector('.page-numbers');
    const placeholderContainer = paginationContainer.querySelector('.pagination-placeholder');
    const controlsContainer = paginationContainer.querySelector('.pagination-controls');
    
    // If we have a placeholder and need to create the pagination structure
    if (placeholderContainer && totalPages > 1) {
        // Create the full pagination structure
        const paginationHTML = generateFullPaginationHTML(currentPage, totalPages);
        controlsContainer.innerHTML = paginationHTML;
    } else if (pageNumbersContainer) {
        // Update existing pagination
        const paginationHTML = generatePaginationHTML(currentPage, totalPages);
        pageNumbersContainer.innerHTML = paginationHTML;
    }
    
    // Update prev/next button states
    const prevButton = paginationContainer.querySelector('.page-nav.prev');
    const nextButton = paginationContainer.querySelector('.page-nav.next');
    
    if (prevButton) {
        if (currentPage <= 1) {
            prevButton.classList.add('disabled');
            prevButton.setAttribute('aria-disabled', 'true');
        } else {
            prevButton.classList.remove('disabled');
            prevButton.removeAttribute('aria-disabled');
        }
    }
    
    if (nextButton) {
        if (currentPage >= totalPages) {
            nextButton.classList.add('disabled');
            nextButton.setAttribute('aria-disabled', 'true');
        } else {
            nextButton.classList.remove('disabled');
            nextButton.removeAttribute('aria-disabled');
        }
    }
    
    // Dispatch event to re-attach click handlers
    paginationContainer.dispatchEvent(new Event('pagination-updated'));
}

// Helper function to generate full pagination structure (including prev/next)
function generateFullPaginationHTML(currentPage, totalPages) {
    let html = '';
    
    // Previous button
    if (currentPage > 1) {
        html += '<span class="page-nav prev" aria-label="Previous page"><span aria-hidden="true">‹</span></span>';
    } else {
        html += '<span class="page-nav prev disabled" aria-disabled="true"><span aria-hidden="true">‹</span></span>';
    }
    
    // Page numbers container
    html += '<div class="page-numbers">';
    html += generatePaginationHTML(currentPage, totalPages);
    html += '</div>';
    
    // Next button
    if (currentPage < totalPages) {
        html += '<span class="page-nav next" aria-label="Next page"><span aria-hidden="true">›</span></span>';
    } else {
        html += '<span class="page-nav next disabled" aria-disabled="true"><span aria-hidden="true">›</span></span>';
    }
    
    return html;
}

// Helper function to generate pagination HTML
function generatePaginationHTML(currentPage, totalPages) {
    let html = '';
    
    if (totalPages <= 6) {
        // Show all pages if 6 or fewer
        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                html += `<span class="page-number current" aria-current="page">${i}</span>`;
            } else {
                html += `<span class="page-number">${i}</span>`;
            }
        }
    } else {
        // Sliding window pagination logic
        let pages = [];
        
        if (currentPage <= 2) {
            // Pages 1-2: Show 1,2,3 ... last-2,last-1,last
            pages = [1, 2, 3, '...', totalPages - 2, totalPages - 1, totalPages];
        } else if (currentPage === 3) {
            // Page 3: Show ..., 2,3,4, ..., last-2,last-1,last
            pages = ['...', 2, 3, 4, '...', totalPages - 2, totalPages - 1, totalPages];
        } else if (currentPage >= totalPages - 2) {
            // Last 3 pages: Show 1,2,3, ..., last-2,last-1,last
            pages = [1, 2, 3, '...', totalPages - 2, totalPages - 1, totalPages];
        } else {
            // Middle pages: Show 1, ..., current-1, current, current+1, ..., last
            pages = [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages];
        }
        
        // Generate HTML for pages
        pages.forEach(page => {
            if (page === '...') {
                html += '<span class="page-ellipsis" aria-hidden="true">…</span>';
            } else if (page === currentPage) {
                html += `<span class="page-number current" aria-current="page">${page}</span>`;
            } else {
                html += `<span class="page-number">${page}</span>`;
            }
        });
    }
    
    return html;
}

function showLoadingState(container, isLoading) {
    if (isLoading) {
        container.classList.add('is-loading');
        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';
    } else {
        container.classList.remove('is-loading');
        container.style.opacity = '';
        container.style.pointerEvents = '';
    }
}

// Listen for grid events to update pagination display
document.addEventListener('fau-grid-pagination-ready', function(e) {
    const gridId = e.detail.gridId;
    const totalPages = e.detail.totalPages;
    const currentPage = e.detail.currentPage;
    
    console.log('DEBUG: Pagination received grid ready event:', e.detail);
    
    // Find associated pagination block
    const paginationBlocks = document.querySelectorAll('[data-grid-block-id="' + gridId + '"]');
    paginationBlocks.forEach(paginationBlock => {
        paginationBlock.setAttribute('data-total-pages', totalPages);
        paginationBlock.setAttribute('data-current-page', currentPage);
        updatePaginationState(paginationBlock, currentPage);
        
        // Re-initialize to attach event handlers to new elements
        initializePaginationBlock(paginationBlock);
    });
});

document.addEventListener('fau-grid-page-change', function(e) {
    const gridId = e.detail.gridId;
    const currentPage = e.detail.currentPage;
    const totalPages = e.detail.totalPages;
    
    // Find associated pagination block
    const paginationBlocks = document.querySelectorAll('[data-grid-block-id="' + gridId + '"]');
    paginationBlocks.forEach(paginationBlock => {
        paginationBlock.setAttribute('data-current-page', currentPage);
        paginationBlock.setAttribute('data-total-pages', totalPages);
        updatePaginationState(paginationBlock, currentPage);
    });
});

// Export functions for use by other scripts
window.fauPagination = {
    initializePaginationBlock,
    updateGridForPage,
    updatePaginationState
}; 