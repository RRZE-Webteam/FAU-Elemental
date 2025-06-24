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
    
    // Add click handlers to all pagination links
    const paginationLinks = paginationElement.querySelectorAll('.page-nav, .page-numbers a, .page-numbers button');
    
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            handlePaginationClick(e, associatedGrid, paginationElement);
        });
    });
    
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
    const clickedElement = e.target.closest('a, button');
    
    if (!clickedElement) {
        return;
    }
    
    // Get page number from the clicked element
    let targetPage = 1;
    
    if (clickedElement.classList.contains('prev')) {
        const currentPage = parseInt(paginationContainer.getAttribute('data-current-page')) || 1;
        targetPage = Math.max(1, currentPage - 1);
    } else if (clickedElement.classList.contains('next')) {
        const currentPage = parseInt(paginationContainer.getAttribute('data-current-page')) || 1;
        const totalPages = parseInt(paginationContainer.getAttribute('data-total-pages')) || 1;
        targetPage = Math.min(totalPages, currentPage + 1);
    } else {
        // Direct page number click
        const pageText = clickedElement.textContent.trim();
        const pageNum = parseInt(pageText);
        if (!isNaN(pageNum)) {
            targetPage = pageNum;
        }
    }
    
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
    
    // Update active state of pagination links
    const pageNumbers = paginationContainer.querySelectorAll('.page-numbers a, .page-numbers button');
    pageNumbers.forEach(link => {
        link.classList.remove('current', 'active');
        const linkPage = parseInt(link.textContent.trim());
        if (linkPage === currentPage) {
            link.classList.add('current', 'active');
        }
    });
    
    // Update prev/next button states
    const totalPages = parseInt(paginationContainer.getAttribute('data-total-pages')) || 1;
    const prevButton = paginationContainer.querySelector('.page-nav.prev');
    const nextButton = paginationContainer.querySelector('.page-nav.next');
    
    if (prevButton) {
        if (currentPage <= 1) {
            prevButton.classList.add('disabled');
        } else {
            prevButton.classList.remove('disabled');
        }
    }
    
    if (nextButton) {
        if (currentPage >= totalPages) {
            nextButton.classList.add('disabled');
        } else {
            nextButton.classList.remove('disabled');
        }
    }
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
    
    // Find associated pagination block
    const paginationBlocks = document.querySelectorAll('[data-grid-block-id="' + gridId + '"]');
    paginationBlocks.forEach(paginationBlock => {
        paginationBlock.setAttribute('data-total-pages', totalPages);
        paginationBlock.setAttribute('data-current-page', currentPage);
        updatePaginationState(paginationBlock, currentPage);
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