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
                if (currentFilters[filterName]) {
                    label.textContent = `1 filter selected`;
                } else {
                    label.textContent = `All ${filterName}`;
                }
            }
        });
    }

    function toggleMoreFilters() {
        const hiddenFilters = blockElement.querySelectorAll('.filter-field.hidden');
        const isExpanded = showMoreButton.getAttribute('aria-expanded') === 'true';
        
        if (isExpanded) {
            hiddenFilters.forEach(filter => filter.classList.add('hidden'));
            showMoreButton.textContent = 'Show more filters';
            showMoreButton.setAttribute('aria-expanded', 'false');
        } else {
            hiddenFilters.forEach(filter => filter.classList.remove('hidden'));
            showMoreButton.textContent = 'Show fewer filters';
            showMoreButton.setAttribute('aria-expanded', 'true');
        }
    }

    function clearAllFilters() {
        // Clear search
        if (searchInput) {
            searchInput.value = '';
            currentSearch = '';
            searchClear.style.display = 'none';
        }
        
        // Clear all filters
        currentFilters = {};
        filterSelects.forEach(select => {
            select.value = '';
        });
        
        updateFilterChips();
        updateFilterLabels();
        currentPage = 1;
        performSearch();
    }

    // View functions
    function getCurrentView() {
        const activeButton = blockElement.querySelector('.view-button.active');
        return activeButton ? activeButton.getAttribute('data-view') : 'cards';
    }

    function handleViewChange(e) {
        const button = e.currentTarget;
        const newView = button.getAttribute('data-view');
        
        // Update button states
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.setAttribute('aria-pressed', 'false');
        });
        
        button.classList.add('active');
        button.setAttribute('aria-pressed', 'true');
        
        currentView = newView;
        
        // Trigger view change event
        const event = new CustomEvent('fauListFiltersViewChanged', {
            detail: {
                blockId: blockId,
                view: newView,
                filters: currentFilters,
                search: currentSearch,
                sort: currentSort
            }
        });
        document.dispatchEvent(event);
    }

    // Sort functions
    function handleSortChange(e) {
        currentSort = e.target.value;
        currentPage = 1;
        performSearch();
    }

    // Search execution
    function performSearch() {
        // Trigger search event for external handling
        const event = new CustomEvent('fauListFiltersChanged', {
            detail: {
                blockId: blockId,
                search: currentSearch,
                filters: currentFilters,
                sort: currentSort,
                view: currentView,
                page: currentPage,
                resultsPerPage: resultsPerPage
            }
        });
        document.dispatchEvent(event);
        
        // For demo purposes, simulate results
        simulateResults();
    }

    // Simulate results for demonstration
    function simulateResults() {
        const hasFilters = Object.keys(currentFilters).length > 0 || currentSearch.length > 0;
        totalResults = hasFilters ? Math.floor(Math.random() * 50) + 10 : 100;
        updateResultsDisplay();
    }

    function updateResultsDisplay() {
        if (resultsCountElement) {
            const startResult = ((currentPage - 1) * resultsPerPage) + 1;
            const endResult = Math.min(currentPage * resultsPerPage, totalResults);
            
            resultsCountElement.textContent = `${startResult} to ${endResult} from ${totalResults} records`;
        }
    }

    // Utility functions
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

    // Public API for external integration
    blockElement.fauListFilters = {
        getCurrentState: () => ({
            search: currentSearch,
            filters: currentFilters,
            sort: currentSort,
            view: currentView,
            page: currentPage,
            resultsPerPage: resultsPerPage,
            totalResults: totalResults
        }),
        
        setSearch: (searchTerm) => {
            searchInput.value = searchTerm;
            handleSearch();
        },
        
        setFilter: (filterName, value) => {
            const select = blockElement.querySelector(`[data-filter-name="${filterName}"]`);
            if (select) {
                select.value = value;
                handleFilterChange({ target: select });
            }
        },
        
        clearAll: clearAllFilters,
        
        updateResults: (newTotalResults) => {
            totalResults = newTotalResults;
            updateResultsDisplay();
        }
    };
} 