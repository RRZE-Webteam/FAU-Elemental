/**
 * Frontend JavaScript for FAU List Filters block
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all FAU List Filters blocks on the page
    const filterBlocks = document.querySelectorAll('.fau-list-filters');
    
    filterBlocks.forEach(initializeFilterBlock);
});

function initializeFilterBlock(blockElement) {
    const blockId = blockElement.getAttribute('data-block-id');
    
    // Get elements
    const searchInput = blockElement.querySelector('.search-input');
    const searchClear = blockElement.querySelector('.search-clear');
    const filterSelects = blockElement.querySelectorAll('.filter-select');
    const showMoreButton = blockElement.querySelector('.show-more-filters');
    const activeFiltersContainer = blockElement.querySelector('.active-filters');
    const filterChipsContainer = blockElement.querySelector('.filter-chips');
    const clearAllButton = blockElement.querySelector('.clear-all-filters');
    const viewButtons = blockElement.querySelectorAll('.view-button');
    const sortSelect = blockElement.querySelector('.sort-select');
    const resultsCountElement = blockElement.querySelector('.results-text');
    
    // Find associated teaser grid
    const associatedGrid = findAssociatedGrid(blockId);
    
    // State
    let currentFilters = {};
    let currentSearch = '';
    let currentSort = sortSelect ? sortSelect.value : '';
    let currentView = getCurrentView();
    let currentPage = 1;
    let totalResults = 0;
    let resultsPerPage = parseInt(blockElement.getAttribute('data-results-per-page')) || 15;

    // Initialize
    updateResultsDisplay();

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearch();
            }
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', clearSearch);
    }

    // Filter functionality
    filterSelects.forEach(select => {
        select.addEventListener('change', handleFilterChange);
    });

    // Show more filters
    if (showMoreButton) {
        showMoreButton.addEventListener('click', toggleMoreFilters);
    }

    // Clear all filters
    if (clearAllButton) {
        clearAllButton.addEventListener('click', clearAllFilters);
    }

    // View switcher
    viewButtons.forEach(button => {
        button.addEventListener('click', handleViewChange);
    });

    // Sort functionality
    if (sortSelect) {
        sortSelect.addEventListener('change', handleSortChange);
    }

    // Helper function to find associated grid
    function findAssociatedGrid(filterId) {
        // Look for a grid with matching filter-block-id
        const grids = document.querySelectorAll('.filterable-grid');
        for (let grid of grids) {
            if (grid.getAttribute('data-filter-block-id') === filterId) {
                return grid;
            }
        }
        
        // Fallback: look for the closest grid after this filter block
        const nextGrid = blockElement.parentElement.querySelector('.fau-list-item, .filterable-grid');
        return nextGrid;
    }

    // Search functions
    function handleSearch() {
        const searchValue = searchInput.value.trim();
        currentSearch = searchValue;
        
        if (searchValue) {
            searchClear.style.display = 'block';
        } else {
            searchClear.style.display = 'none';
        }
        
        currentPage = 1;
        performSearch();
    }

    function clearSearch() {
        searchInput.value = '';
        currentSearch = '';
        searchClear.style.display = 'none';
        currentPage = 1;
        performSearch();
    }

    // Filter functions
    function handleFilterChange(e) {
        const select = e.target;
        const filterName = select.getAttribute('data-filter-name');
        const filterValue = select.value;
        
        if (filterValue) {
            currentFilters[filterName] = {
                value: filterValue,
                label: select.options[select.selectedIndex].text
            };
        } else {
            delete currentFilters[filterName];
        }
        
        updateFilterChips();
        updateFilterLabels();
        currentPage = 1;
        performSearch();
    }

    function updateFilterChips() {
        if (!filterChipsContainer) return;
        
        const hasFilters = Object.keys(currentFilters).length > 0;
        const hasSearch = currentSearch.length > 0;
        
        if (hasFilters || hasSearch) {
            activeFiltersContainer.style.display = 'block';
            
            // Clear existing chips
            filterChipsContainer.innerHTML = '';
            
            // Add search chip
            if (hasSearch) {
                const searchChip = createFilterChip('Search', currentSearch, 'search');
                filterChipsContainer.appendChild(searchChip);
            }
            
            // Add filter chips
            Object.entries(currentFilters).forEach(([filterName, filterData]) => {
                const chip = createFilterChip(filterName, filterData.label, 'filter', filterName);
                filterChipsContainer.appendChild(chip);
            });
            
            // Show/hide clear all button
            if (Object.keys(currentFilters).length > 1 || (hasSearch && hasFilters)) {
                clearAllButton.style.display = 'inline-block';
            } else {
                clearAllButton.style.display = 'none';
            }
        } else {
            activeFiltersContainer.style.display = 'none';
        }
    }

    function createFilterChip(name, value, type, filterKey = null) {
        const chip = document.createElement('div');
        chip.className = 'filter-chip';
        chip.innerHTML = `
            <span class="chip-label">${name}: ${value}</span>
            <button type="button" class="chip-remove" aria-label="Remove ${name} filter">
                <span class="chip-remove-icon">×</span>
            </button>
        `;
        
        const removeButton = chip.querySelector('.chip-remove');
        removeButton.addEventListener('click', () => {
            if (type === 'search') {
                clearSearch();
            } else if (type === 'filter' && filterKey) {
                removeFilter(filterKey);
            }
        });
        
        return chip;
    }

    function removeFilter(filterKey) {
        delete currentFilters[filterKey];
        
        // Reset the corresponding select
        const select = blockElement.querySelector(`[data-filter-name="${filterKey}"]`);
        if (select) {
            select.value = '';
        }
        
        updateFilterChips();
        updateFilterLabels();
        currentPage = 1;
        performSearch();
    }

    function updateFilterLabels() {
        filterSelects.forEach(select => {
            const filterName = select.getAttribute('data-filter-name');
            const label = blockElement.querySelector(`label[for="${select.id}"]`);
            
            if (label) {
                const selectedCount = Object.keys(currentFilters).filter(key => 
                    key === filterName && currentFilters[key].value
                ).length;
                
                if (selectedCount > 0) {
                    label.textContent = `${selectedCount} Filter ausgewählt`;
                } else {
                    label.textContent = `Alle ${filterName}`;
                }
            }
        });
    }

    function toggleMoreFilters() {
        const hiddenFilters = blockElement.querySelectorAll('.filter-field.hidden');
        const isExpanded = showMoreButton.getAttribute('aria-expanded') === 'true';
        
        hiddenFilters.forEach(filter => {
            filter.classList.toggle('hidden');
        });
        
        showMoreButton.setAttribute('aria-expanded', !isExpanded);
        showMoreButton.textContent = isExpanded ? 
            'Show more filters' : 'Show fewer filters';
    }

    function clearAllFilters() {
        // Clear all filter selects
        filterSelects.forEach(select => {
            select.value = '';
        });
        
        // Clear search
        if (searchInput) {
            searchInput.value = '';
            searchClear.style.display = 'none';
        }
        
        // Reset state
        currentFilters = {};
        currentSearch = '';
        currentPage = 1;
        
        updateFilterChips();
        updateFilterLabels();
        performSearch();
    }

    function getCurrentView() {
        const activeButton = blockElement.querySelector('.view-button.active');
        return activeButton ? activeButton.getAttribute('data-view') : 'cards';
    }

    function handleViewChange(e) {
        const button = e.currentTarget;
        const view = button.getAttribute('data-view');
        
        // Update button states
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.setAttribute('aria-pressed', 'false');
        });
        
        button.classList.add('active');
        button.setAttribute('aria-pressed', 'true');
        
        currentView = view;
        
        // Update grid view if connected
        if (associatedGrid) {
            updateGridView(view);
        }
    }

    function updateGridView(view) {
        const gridContainer = associatedGrid.querySelector('.fau-teaser-grid');
        if (gridContainer) {
            // Remove existing view classes
            gridContainer.classList.remove('view-cards', 'view-table', 'view-list');
            // Add new view class
            gridContainer.classList.add(`view-${view}`);
        }
    }

    function handleSortChange(e) {
        currentSort = e.target.value;
        currentPage = 1;
        performSearch();
    }

    function performSearch() {
        if (!associatedGrid) {
            console.warn('No associated grid found for filter block');
            simulateResults();
            return;
        }

        // Show loading state
        showLoadingState();

        // Prepare filter data
        const filterData = {};
        Object.entries(currentFilters).forEach(([key, data]) => {
            filterData[key] = data.value;
        });

        // Get grid configuration
        const gridConfig = {
            variant: associatedGrid.getAttribute('data-variant') || 'post',
            posts_per_page: parseInt(associatedGrid.getAttribute('data-posts-per-page')) || 15,
            display_style: associatedGrid.getAttribute('data-display-style') || 'teaser-grid',
            teaser_layout: associatedGrid.getAttribute('data-teaser-layout') || '3m',
            heading_level: associatedGrid.getAttribute('data-heading-level') || 'h4'
        };

        // Prepare AJAX data
        const ajaxData = {
            action: 'fau_teaser_grid_filter',
            nonce: associatedGrid.getAttribute('data-nonce'),
            ...gridConfig,
            page: currentPage,
            search: currentSearch,
            filters: filterData,
            sort: currentSort,
            sort_order: 'DESC' // Could be made configurable
        };

        // Perform AJAX request
        fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(ajaxData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateGridContent(data.data);
                updateResultsCount(data.data);
            } else {
                console.error('Filter request failed:', data);
                hideLoadingState();
            }
        })
        .catch(error => {
            console.error('Filter request error:', error);
            hideLoadingState();
        });
    }

    function showLoadingState() {
        if (associatedGrid) {
            const loadingElement = associatedGrid.querySelector('.grid-loading');
            const gridContent = associatedGrid.querySelector('.fau-teaser-grid');
            
            if (loadingElement) {
                loadingElement.style.display = 'block';
            }
            if (gridContent) {
                gridContent.style.opacity = '0.5';
            }
        }
    }

    function hideLoadingState() {
        if (associatedGrid) {
            const loadingElement = associatedGrid.querySelector('.grid-loading');
            const gridContent = associatedGrid.querySelector('.fau-teaser-grid');
            
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            if (gridContent) {
                gridContent.style.opacity = '1';
            }
        }
    }

    function updateGridContent(data) {
        if (!associatedGrid) return;
        
        const gridContainer = associatedGrid.querySelector('.fau-teaser-grid');
        if (gridContainer) {
            gridContainer.innerHTML = data.html;
        }
        
        hideLoadingState();
    }

    function updateResultsCount(data) {
        if (resultsCountElement) {
            resultsCountElement.textContent = data.results_text || `${data.found_posts} records found`;
        }
        
        totalResults = data.found_posts || 0;
    }

    function simulateResults() {
        // Fallback simulation for when no grid is connected
        const baseResults = 100;
        const searchModifier = currentSearch ? 0.6 : 1;
        const filterModifier = Object.keys(currentFilters).length > 0 ? 0.8 : 1;
        
        totalResults = Math.floor(baseResults * searchModifier * filterModifier);
        updateResultsDisplay();
    }

    function updateResultsDisplay() {
        if (resultsCountElement) {
            const startResult = ((currentPage - 1) * resultsPerPage) + 1;
            const endResult = Math.min(currentPage * resultsPerPage, totalResults);
            resultsCountElement.textContent = `${startResult} to ${endResult} from ${totalResults} records`;
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
} 