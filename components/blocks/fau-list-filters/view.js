/**
 * Frontend JavaScript for FAU List Filters block
 */

document.addEventListener( 'DOMContentLoaded', function () {
	// Initialize all FAU List Filters blocks on the page
	const filterBlocks = document.querySelectorAll( '.fau-list-filters' );

	filterBlocks.forEach( initializeFilterBlock );
} );

function initializeFilterBlock( blockElement ) {
	const blockId = blockElement.getAttribute( 'data-block-id' );

	// Get elements
	const searchInput = blockElement.querySelector( '.search-input' );
	const searchClear = blockElement.querySelector( '.search-clear' );
	const filterSelects = blockElement.querySelectorAll( '.filter-select' );
	const showMoreButton = blockElement.querySelector( '.show-more-filters' );
	const activeFiltersContainer =
		blockElement.querySelector( '.active-filters' );
	const filterChipsContainer = blockElement.querySelector( '.filter-chips' );
	const clearAllButton = blockElement.querySelector( '.clear-all-filters' );
	const viewButtons = blockElement.querySelectorAll( '.view-button' );
	const sortSelect = blockElement.querySelector( '.sort-select' );
	const resultsCountElement = blockElement.querySelector( '.results-text' );
	const hiddenFilters = blockElement.querySelectorAll(
		'.filter-field.hidden'
	);

	// Find associated teaser grid
	const associatedGrid = findAssociatedGrid( blockId );
	
	// Debug: Check if grid was found
	if ( associatedGrid ) {
		console.log( 'DEBUG: Filter initialized with grid:', associatedGrid.className );
	} else {
		console.log( 'ERROR: Filter initialized but no grid found!' );
	}

	// State
	let currentFilters = {};
	let currentSearch = '';
	let currentSort = sortSelect ? sortSelect.value : '';
	let currentView = getCurrentView();
	let currentPage = 1;
	let totalResults = 0;
	let totalPages = 1;
	let resultsPerPage =
		parseInt( blockElement.getAttribute( 'data-results-per-page' ) ) || 15;
	let filtersExpanded = false;
	let isInitialized = false;
	let paginationEnabled = false;
	let associatedPagination = null;

	// Find associated pagination block
	const paginationBlockId = associatedGrid?.getAttribute('data-pagination-block-id');
	console.log('DEBUG: Looking for pagination with ID:', paginationBlockId);
	console.log('DEBUG: Associated grid:', associatedGrid);
	
	if (paginationBlockId) {
		associatedPagination = document.querySelector(`[data-block-id="${paginationBlockId}"]`);
		if (associatedPagination) {
			paginationEnabled = true;
			console.log('DEBUG: Found associated pagination block:', paginationBlockId, associatedPagination);
		} else {
			console.log('DEBUG: Pagination block ID found but element not found:', paginationBlockId);
			// Try alternative selectors
			const altPagination = document.querySelector(`#${paginationBlockId}`);
			if (altPagination) {
				associatedPagination = altPagination;
				paginationEnabled = true;
				console.log('DEBUG: Found pagination using ID selector:', altPagination);
			}
		}
	} else {
		console.log('DEBUG: No pagination block ID found on grid');
	}

	// Initialize - Load actual data instead of simulating
	loadInitialData();

	// Search functionality
	if ( searchInput ) {
		searchInput.addEventListener( 'input', debounce( handleSearch, 300 ) );
		searchInput.addEventListener( 'keypress', function ( e ) {
			if ( e.key === 'Enter' ) {
				e.preventDefault();
				handleSearch();
			}
		} );
	}

	if ( searchClear ) {
		searchClear.addEventListener( 'click', clearSearch );
	}

	// Filter functionality
	filterSelects.forEach( ( select ) => {
		select.addEventListener( 'change', handleFilterChange );
	} );

	// Show more filters
	if ( showMoreButton ) {
		showMoreButton.addEventListener( 'click', toggleMoreFilters );
	}

	// Clear all filters
	if ( clearAllButton ) {
		clearAllButton.addEventListener( 'click', clearAllFilters );
	}

	// View switcher
	viewButtons.forEach( ( button ) => {
		button.addEventListener( 'click', handleViewChange );
	} );

	// Sort functionality
	if ( sortSelect ) {
		sortSelect.addEventListener( 'change', handleSortChange );
	}

	// Load initial data from the server
	function loadInitialData() {
		if ( ! associatedGrid ) {
			// If no associated grid is found, show a default message
			if ( resultsCountElement ) {
				resultsCountElement.textContent = 'No content to filter';
			}
			return;
		}

		// First, populate filters based on grid content
		populateFiltersFromGrid();

		// Then load the filtered data
		performSearch( true );
	}

	// Populate filter options based on content in the associated grid
	function populateFiltersFromGrid() {
		console.log( 'DEBUG: Populating filters from grid content' );
		
		if ( ! associatedGrid ) {
			return;
		}

		// Get all existing teaser items in the grid
		const existingTeaserItems = associatedGrid.querySelectorAll( '.teaser-item' );
		console.log( 'DEBUG: Found', existingTeaserItems.length, 'items to analyze for filters' );

		// Extract categories, tags, authors, etc. from existing items
		const availableOptions = {
			categories: new Set(),
			tags: new Set(), 
			authors: new Set(),
			years: new Set()
		};

		existingTeaserItems.forEach( ( item ) => {
			// Extract categories from category spans
			const categoryElements = item.querySelectorAll( '.category' );
			categoryElements.forEach( ( catEl ) => {
				const categoryName = catEl.textContent.trim();
				if ( categoryName ) {
					availableOptions.categories.add( categoryName );
				}
			} );

			// Extract years from date elements
			const timeElements = item.querySelectorAll( 'time' );
			timeElements.forEach( ( timeEl ) => {
				const datetime = timeEl.getAttribute( 'datetime' );
				if ( datetime ) {
					const year = new Date( datetime ).getFullYear();
					if ( year && !isNaN( year ) ) {
						availableOptions.years.add( year.toString() );
					}
				}
			} );

			// Extract authors from author elements (if they exist)
			const authorElements = item.querySelectorAll( '.author, .post-author' );
			authorElements.forEach( ( authorEl ) => {
				const authorName = authorEl.textContent.trim();
				if ( authorName ) {
					availableOptions.authors.add( authorName );
				}
			} );
		} );

		console.log( 'DEBUG: Available filter options from grid:', {
			categories: Array.from( availableOptions.categories ),
			years: Array.from( availableOptions.years ),
			authors: Array.from( availableOptions.authors )
		} );

		// Update dynamic filter options
		updateDynamicFilterOptions( availableOptions );
	}

	// Update the dynamic filter options based on grid content
	function updateDynamicFilterOptions( availableOptions ) {
		// Update the show more filters button data
		if ( showMoreButton ) {
			const dynamicFilterData = {
				categories: {
					label: 'Categories',
					options: Array.from( availableOptions.categories ).map( cat => ({
						value: cat.toLowerCase().replace( /\s+/g, '-' ),
						label: cat
					}) )
				},
				years: {
					label: 'Year',
					options: Array.from( availableOptions.years ).sort( ( a, b ) => b - a ).map( year => ({
						value: year,
						label: year
					}) )
				}
			};

			// Only include categories with options
			const filteredData = {};
			Object.entries( dynamicFilterData ).forEach( ( [ key, data ] ) => {
				if ( data.options.length > 0 ) {
					filteredData[ key ] = data;
				}
			} );

			console.log( 'DEBUG: Setting dynamic filter data:', filteredData );
			showMoreButton.setAttribute( 'data-available-filters', JSON.stringify( filteredData ) );
		}
	}

	// Helper function to find associated grid
	function findAssociatedGrid( filterId ) {
		console.log( 'DEBUG: Looking for grid with filter ID:', filterId );
		
		// Method 1: Look for a grid with matching filter-block-id
		const grids = document.querySelectorAll(
			'.filterable-grid, .fau-teaser-grid'
		);
		
		console.log( 'DEBUG: Found', grids.length, 'grids on page' );
		
		for ( let grid of grids ) {
			const gridFilterId = grid.getAttribute( 'data-filter-block-id' );
			console.log( 'DEBUG: Grid has filter-block-id:', gridFilterId );
			
			if ( gridFilterId === filterId ) {
				console.log( 'DEBUG: Found matching grid with ID:', filterId );
				return grid;
			}
		}
		
		// Method 2: If no exact match, find the closest grid after this filter block
		console.log( 'DEBUG: No exact ID match, looking for closest grid...' );
		let nextElement = blockElement.nextElementSibling;
		while ( nextElement ) {
			if (
				nextElement.classList.contains( 'fau-teaser-grid' ) ||
				nextElement.classList.contains( 'filterable-grid' ) ||
				nextElement.querySelector(
					'.fau-teaser-grid, .filterable-grid'
				)
			) {
				const foundGrid = nextElement.querySelector(
					'.fau-teaser-grid, .filterable-grid'
				) || nextElement;
				
				console.log( 'DEBUG: Found closest grid via DOM traversal' );
				return foundGrid;
			}
			nextElement = nextElement.nextElementSibling;
		}
		
		// Method 3: If still no grid found, look for any grid on the page
		if ( grids.length > 0 ) {
			console.log( 'DEBUG: Using first available grid as fallback' );
			return grids[0];
		}
		
		console.log( 'ERROR: No grid found at all!' );
		return null;
	}

	// Search functions
	function handleSearch() {
		const searchValue = searchInput.value.trim();
		currentSearch = searchValue;

		if ( searchValue ) {
			searchClear.style.display = 'block';
			searchInput.classList.add( 'has-value' );
		} else {
			searchClear.style.display = 'none';
			searchInput.classList.remove( 'has-value' );
		}

		currentPage = 1;
		performSearch();
	}

	function clearSearch() {
		searchInput.value = '';
		currentSearch = '';
		searchClear.style.display = 'none';
		searchInput.classList.remove( 'has-value' );
		currentPage = 1;
		performSearch();
	}

	// Filter functions
	function handleFilterChange( e ) {
		const select = e.target;
		const filterName = select.getAttribute( 'data-filter-name' );
		const filterType = select.getAttribute( 'data-filter-type' );
		const filterValue = select.value;

		if ( filterValue ) {
			currentFilters[ filterName ] = {
				value: filterValue,
				label: select.options[ select.selectedIndex ].text,
				type: filterType,
			};
			select.classList.add( 'has-selection' );
		} else {
			delete currentFilters[ filterName ];
			select.classList.remove( 'has-selection' );
		}

		updateFilterChips();
		updateFilterLabels();
		currentPage = 1;
		performSearch();
	}

	function updateFilterChips() {
		if ( ! filterChipsContainer ) return;

		const hasFilters = Object.keys( currentFilters ).length > 0;
		const hasSearch = currentSearch.length > 0;

		if ( hasFilters || hasSearch ) {
			activeFiltersContainer.style.display = 'block';

			// Clear existing chips
			filterChipsContainer.innerHTML = '';

			// Add search chip
			if ( hasSearch ) {
				const searchChip = createFilterChip(
					'Search',
					currentSearch,
					'search'
				);
				filterChipsContainer.appendChild( searchChip );
			}

			// Add filter chips
			Object.entries( currentFilters ).forEach(
				( [ filterName, filterData ] ) => {
					const chip = createFilterChip(
						filterName,
						filterData.label,
						filterData.type,
						filterName
					);
					filterChipsContainer.appendChild( chip );
				}
			);

			// Show clear all button
			if ( clearAllButton ) {
				clearAllButton.style.display = 'block';
			}
		} else {
			activeFiltersContainer.style.display = 'none';
			if ( clearAllButton ) {
				clearAllButton.style.display = 'none';
			}
		}
	}

	function createFilterChip( name, value, type, filterKey = null ) {
		const chip = document.createElement( 'div' );
		chip.className = 'filter-chip';
		chip.setAttribute( 'data-type', type );
		if ( filterKey ) {
			chip.setAttribute( 'data-filter-key', filterKey );
		}

		chip.innerHTML = `
			<span class="chip-label">${ escapeHtml( name ) }: ${ escapeHtml(
			value
		) }</span>
			<button type="button" class="chip-remove" aria-label="Remove ${ escapeHtml(
				name
			) } filter">
				<span aria-hidden="true">×</span>
			</button>
		`;

		// Add remove functionality
		const removeButton = chip.querySelector( '.chip-remove' );
		removeButton.addEventListener( 'click', function () {
			if ( type === 'search' ) {
				clearSearch();
			} else if ( filterKey ) {
				removeFilter( filterKey );
			}
		} );

		return chip;
	}

	function removeFilter( filterKey ) {
		// Remove from current filters
		delete currentFilters[ filterKey ];

		// Reset corresponding select element
		const select = blockElement.querySelector(
			`[data-filter-name="${ filterKey }"]`
		);
		if ( select ) {
			select.value = '';
			select.classList.remove( 'has-selection' );
		}

		updateFilterChips();
		updateFilterLabels();
		currentPage = 1;
		performSearch();
	}

	function updateFilterLabels() {
		// Update filter labels to reflect current selections
		filterSelects.forEach( ( select ) => {
			const hasSelection = select.value !== '';
			select.classList.toggle( 'has-selection', hasSelection );
		} );
	}

	function toggleMoreFilters() {
		filtersExpanded = ! filtersExpanded;

		// Toggle hidden filters
		hiddenFilters.forEach( ( filter ) => {
			if ( filtersExpanded ) {
				filter.classList.remove( 'hidden' );
			} else {
				filter.classList.add( 'hidden' );
			}
		} );

		// Update button text and state
		const showMoreText = showMoreButton.querySelector( '.show-more-text' );
		const showLessText = showMoreButton.querySelector( '.show-less-text' );

		if ( filtersExpanded ) {
			if ( showMoreText ) showMoreText.style.display = 'none';
			if ( showLessText ) showLessText.style.display = 'inline';
			showMoreButton.setAttribute( 'aria-expanded', 'true' );
		} else {
			if ( showMoreText ) showMoreText.style.display = 'inline';
			if ( showLessText ) showLessText.style.display = 'none';
			showMoreButton.setAttribute( 'aria-expanded', 'false' );
		}

		// Handle dynamic filters
		if ( filtersExpanded ) {
			createDynamicFilterInterface();
		} else {
			// Hide dynamic filters container
			const dynamicContainer = blockElement.querySelector(
				'.dynamic-filters-container'
			);
			if ( dynamicContainer ) {
				dynamicContainer.style.display = 'none';
			}
		}
	}

	function createDynamicFilterInterface() {
		const dynamicContainer = blockElement.querySelector(
			'.dynamic-filters-container'
		);
		if ( ! dynamicContainer ) return;

		dynamicContainer.style.display = 'block';

		// Get available filters from the show more button
		const availableFiltersData = showMoreButton.getAttribute(
			'data-available-filters'
		);
		let availableFilters = {};

		try {
			availableFilters = JSON.parse( availableFiltersData );
		} catch ( e ) {
			console.error( 'Error parsing available filters:', e );
			return;
		}

		// Clear existing content
		dynamicContainer.innerHTML = '';

		// Create container for available filter buttons
		const availableFiltersContainer = document.createElement( 'div' );
		availableFiltersContainer.className = 'available-filters';
		availableFiltersContainer.innerHTML =
			'<h4>Add filters:</h4><div class="filter-buttons-container"></div>';

		// Create container for added filters
		const addedFiltersContainer = document.createElement( 'div' );
		addedFiltersContainer.className = 'added-filters';

		dynamicContainer.appendChild( availableFiltersContainer );
		dynamicContainer.appendChild( addedFiltersContainer );

		// Update available filter buttons
		updateAvailableFilterButtons(
			availableFilters,
			availableFiltersContainer,
			addedFiltersContainer
		);
	}

	function updateAvailableFilterButtons(
		availableFilters,
		availableFiltersContainer,
		addedFiltersContainer
	) {
		const buttonsContainer = availableFiltersContainer.querySelector(
			'.filter-buttons-container'
		);
		if ( ! buttonsContainer ) return;

		// Clear existing buttons
		buttonsContainer.innerHTML = '';

		// Get currently configured filter types
		const configuredFilterTypes = Array.from( filterSelects ).map(
			( select ) => select.getAttribute( 'data-filter-type' )
		);

		// Get currently added dynamic filter types
		const addedDynamicTypes = Array.from(
			addedFiltersContainer.querySelectorAll( '.filter-select' )
		).map( ( select ) => select.getAttribute( 'data-filter-type' ) );

		// Create buttons for available filters that aren't already used
		Object.entries( availableFilters ).forEach(
			( [ filterKey, filterData ] ) => {
				if (
					! configuredFilterTypes.includes( filterKey ) &&
					! addedDynamicTypes.includes( filterKey )
				) {
					const filterButton = document.createElement( 'button' );
					filterButton.type = 'button';
					filterButton.className = 'filter-add-button';
					filterButton.textContent = filterData.label;
					filterButton.setAttribute( 'data-filter-key', filterKey );

					filterButton.addEventListener( 'click', function () {
						addDynamicFilter( filterKey, filterData );
						updateAvailableFilterButtons(
							availableFilters,
							availableFiltersContainer,
							addedFiltersContainer
						);
					} );

					buttonsContainer.appendChild( filterButton );
				}
			}
		);

		// Hide the section if no filters are available
		if ( buttonsContainer.children.length === 0 ) {
			availableFiltersContainer.style.display = 'none';
		} else {
			availableFiltersContainer.style.display = 'block';
		}
	}

	function addDynamicFilter( filterKey, filterData ) {
		const addedFiltersContainer = blockElement.querySelector(
			'.added-filters'
		);
		if ( ! addedFiltersContainer ) return;

		const filterField = document.createElement( 'div' );
		filterField.className = 'filter-field filter-field--dynamic';
		filterField.setAttribute( 'data-filter-key', filterKey );

		const filterId = blockId + '-dynamic-filter-' + filterKey;

		let filterHTML = `
			<label for="${ filterId }" class="filter-label">${ escapeHtml(
			filterData.label
		) }</label>
			<div class="filter-control-wrapper">
				<select id="${ filterId }" class="filter-select" data-filter-name="${ escapeHtml(
			filterData.label
		) }" data-filter-type="${ filterKey }">
					<option value="">All ${ escapeHtml(
						filterData.label
					) }</option>
		`;

		filterData.options.forEach( ( option ) => {
			filterHTML += `<option value="${ escapeHtml(
				option.value
			) }">${ escapeHtml( option.label ) }</option>`;
		} );

		filterHTML += `
				</select>
				<button type="button" class="filter-remove-button" aria-label="Remove ${ escapeHtml(
					filterData.label
				) } filter">
					<span aria-hidden="true">×</span>
				</button>
			</div>
		`;

		filterField.innerHTML = filterHTML;

		// Add event listeners
		const select = filterField.querySelector( '.filter-select' );
		const removeButton = filterField.querySelector(
			'.filter-remove-button'
		);

		select.addEventListener( 'change', handleFilterChange );
		removeButton.addEventListener( 'click', function () {
			removeDynamicFilter( filterField, filterKey );
		} );

		addedFiltersContainer.appendChild( filterField );

		// Update the filter selects list
		updateFilterSelectsList();
	}

	function removeDynamicFilter( filterField, filterKey ) {
		// Remove from current filters if it was selected
		const filterName = filterField
			.querySelector( '.filter-select' )
			.getAttribute( 'data-filter-name' );
		if ( currentFilters[ filterName ] ) {
			delete currentFilters[ filterName ];
			updateFilterChips();
			updateFilterLabels();
			currentPage = 1;
			performSearch();
		}

		// Remove the filter field
		filterField.remove();

		// Update the filter selects list
		updateFilterSelectsList();

		// Update available filter buttons
		const availableFiltersContainer = blockElement.querySelector(
			'.available-filters'
		);
		const addedFiltersContainer = blockElement.querySelector(
			'.added-filters'
		);

		if ( availableFiltersContainer && addedFiltersContainer ) {
			const availableFiltersData = showMoreButton.getAttribute(
				'data-available-filters'
			);
			let availableFilters = {};

			try {
				availableFilters = JSON.parse( availableFiltersData );
				updateAvailableFilterButtons(
					availableFilters,
					availableFiltersContainer,
					addedFiltersContainer
				);
			} catch ( e ) {
				console.error( 'Error parsing available filters:', e );
			}
		}
	}

	function updateFilterSelectsList() {
		// Update the filterSelects NodeList to include new dynamic filters
		const updatedFilterSelects = blockElement.querySelectorAll(
			'.filter-select'
		);
		// Note: We can't reassign the NodeList, but we can work with the updated one
		// This is mainly for reference - the event listeners are already attached individually
	}

	function clearAllFilters() {
		console.log( 'DEBUG: Clearing all filters' );

		// Clear search
		if ( searchInput ) {
			searchInput.value = '';
			currentSearch = '';
			searchClear.style.display = 'none';
			searchInput.classList.remove( 'has-value' );
		}

		// Clear all filter selects
		const allFilterSelects = blockElement.querySelectorAll( '.filter-select' );
		allFilterSelects.forEach( ( select ) => {
			select.value = '';
			select.classList.remove( 'has-selection' );
		} );

		// Clear currentFilters object
		currentFilters = {};

		// Hide active filters
		if ( activeFiltersContainer ) {
			activeFiltersContainer.style.display = 'none';
		}

		// Clear filter chips
		if ( filterChipsContainer ) {
			filterChipsContainer.innerHTML = '';
		}

		// Hide clear all button
		if ( clearAllButton ) {
			clearAllButton.style.display = 'none';
		}

		// Reset current page to 1
		currentPage = 1;

		// Perform search with cleared filters and reset pagination
		performSearch( false, 1 );
	}

	function getCurrentView() {
		const activeButton = blockElement.querySelector(
			'.view-button.active'
		);
		return activeButton
			? activeButton.getAttribute( 'data-view' )
			: 'cards';
	}

	function handleViewChange( e ) {
		const button = e.currentTarget;
		const newView = button.getAttribute( 'data-view' );

		// Update button states
		viewButtons.forEach( ( btn ) => {
			btn.classList.remove( 'active' );
			btn.setAttribute( 'aria-pressed', 'false' );
		} );

		button.classList.add( 'active' );
		button.setAttribute( 'aria-pressed', 'true' );

		currentView = newView;

		// Apply view to associated grid
		if ( associatedGrid ) {
			updateGridView( newView );
		}
	}

	function updateGridView( view ) {
		if ( ! associatedGrid ) return;

		// Remove existing view classes
		associatedGrid.classList.remove(
			'view-cards',
			'view-table',
			'view-list'
		);

		// Add new view class
		associatedGrid.classList.add( `view-${ view }` );

		// Trigger custom event for other components to listen to
		const viewChangeEvent = new CustomEvent( 'fauListFiltersViewChange', {
			detail: { view: view, grid: associatedGrid },
		} );
		document.dispatchEvent( viewChangeEvent );
	}

	function handleSortChange( e ) {
		currentSort = e.target.value;
		currentPage = 1;
		performSearch();
	}

	function performSearch( isInitial = false, page = 1 ) {
		if ( !associatedGrid ) {
			console.log( 'DEBUG: No grid available for search' );
			return;
		}

		console.log( 'DEBUG: Starting search, isInitial:', isInitial, 'page:', page );

		// Update current page
		currentPage = page;

		// Check if grid uses JavaScript pagination
		const teaserGrid = associatedGrid.querySelector('.fau-teaser-grid');
		const isJsPagination = teaserGrid && teaserGrid.getAttribute('data-js-pagination') === 'true';
		
		if (isJsPagination) {
			console.log( 'DEBUG: Using client-side filtering for JS pagination grid' );
			performClientSideFilter();
			return;
		}

		// Original server-side filtering code continues below...
		// Read grid attributes to respect its settings
		const gridPostsPerPage = associatedGrid.getAttribute( 'data-posts-per-page' ) || '15';
		const gridVariant = associatedGrid.getAttribute( 'data-variant' ) || 'post';
		const gridCategory = associatedGrid.getAttribute( 'data-category' ) || '0';

		console.log( 'DEBUG: Grid settings - postsPerPage:', gridPostsPerPage, 'variant:', gridVariant, 'category:', gridCategory );

		// Get list of post IDs currently in the grid to limit filtering scope
		const existingTeaserItems = associatedGrid.querySelectorAll( '.teaser-item' );
		const gridPostIds = [];
		
		existingTeaserItems.forEach( ( item ) => {
			let postId = item.getAttribute( 'data-post-id' );
			
			// If no data-post-id, try to extract from teaser-title-{ID} pattern
			if ( !postId ) {
				const titleElement = item.querySelector( '[id^="teaser-title-"]' );
				if ( titleElement ) {
					postId = titleElement.id.replace( 'teaser-title-', '' );
				}
			}
			
			if ( postId ) {
				gridPostIds.push( postId );
			}
		} );

		console.log( 'DEBUG: Grid contains post IDs:', gridPostIds );

		// Collect current filter values
		const searchValue = searchInput ? searchInput.value.trim() : '';
		const sortValue = sortSelect ? sortSelect.value : 'date';
		const activeFilters = {};

		// Collect configured filter values
		const configuredFilters = blockElement.querySelectorAll(
			'.filter-field--configured .filter-select'
		);
		configuredFilters.forEach( ( select ) => {
			if ( select.value ) {
				const filterName = select.getAttribute( 'data-filter-name' );
				activeFilters[filterName] = {
					type: 'configured',
					value: select.value,
				};
			}
		} );

		// Collect dynamic filter values
		const dynamicFilters = blockElement.querySelectorAll(
			'.dynamic-filters-container .filter-select'
		);
		dynamicFilters.forEach( ( select ) => {
			if ( select.value ) {
				const filterType = select.getAttribute( 'data-filter-type' );
				const filterName = select.getAttribute( 'data-filter-name' );
				activeFilters[filterName] = {
					type: filterType,
					value: select.value,
				};
			}
		} );

		console.log( 'DEBUG: Making AJAX request for', gridVariant, gridVariant + 's' );
		console.log( 'DEBUG: Request params - search:', searchValue, 'filters:', activeFilters, 'sort:', sortValue, 'page:', page );

		// Show loading state
		updateLoadingState( true );

		// Prepare AJAX data using grid attributes and limiting to grid post IDs
		const ajaxData = {
			action: 'fau_filter_teaser_grid',
			nonce: fauListFilters.nonce,
			search: searchValue,
			filters: JSON.stringify(activeFilters), // Convert to JSON string
			sort: sortValue,
			page: page,
			per_page: parseInt( gridPostsPerPage ), // Use grid's posts per page setting
			post_type: gridVariant, // Use grid's variant (post type)
			category: parseInt( gridCategory ), // Use grid's category setting
			grid_post_ids: JSON.stringify(gridPostIds), // Convert to JSON string
			pagination_enabled: paginationEnabled,
		};

		console.log( 'DEBUG: AJAX data being sent:', ajaxData );

		// Make AJAX request
		fetch( fauListFilters.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams( ajaxData ),
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				console.log( 'DEBUG: Received', data.posts ? data.posts.length : 0, 'posts from server' );
				console.log( 'DEBUG: Pagination info - current page:', data.current_page, 'total pages:', data.pages );
				updateLoadingState( false );

				if ( data.success && data.posts ) {
					// Update pagination info
					totalPages = data.pages || 1;
					totalResults = data.total || data.posts.length;
					
					if (paginationEnabled) {
						updateGrid( data.posts );
						updatePagination( data.current_page, data.pages );
					} else {
						updateGrid( data.posts );
					}
					
					updateResultsCount( data.total || data.posts.length );
					isInitialized = true;
				} else {
					console.error( 'Filter error:', data );
					showError();
				}
			} )
			.catch( ( error ) => {
				console.error( 'Filter error:', error );
				updateLoadingState( false );
				showError();
			} );
	}

	function updatePagination( currentPage, totalPages ) {
		console.log( 'DEBUG: updatePagination called with:', { currentPage, totalPages, associatedPagination, paginationEnabled } );
		
		if ( !associatedPagination ) {
			console.log( 'DEBUG: No pagination block found to update' );
			return;
		}

		console.log( 'DEBUG: Updating pagination - current:', currentPage, 'total:', totalPages );

		// Update pagination block attributes
		associatedPagination.setAttribute( 'data-current-page', currentPage );
		associatedPagination.setAttribute( 'data-total-pages', totalPages );

		// Find pagination controls
		const paginationControls = associatedPagination.querySelector( '.pagination-controls' );
		console.log( 'DEBUG: Found pagination controls:', paginationControls );
		
		if ( !paginationControls ) {
			console.log( 'DEBUG: No pagination controls found' );
			return;
		}

		// Generate pagination HTML
		const paginationHTML = generatePaginationHTML( currentPage, totalPages );
		console.log( 'DEBUG: Generated pagination HTML:', paginationHTML );
		paginationControls.innerHTML = paginationHTML;

		// Add event listeners to pagination buttons
		const pageButtons = paginationControls.querySelectorAll( '.page-number, .page-nav' );
		console.log( 'DEBUG: Found page buttons:', pageButtons.length );
		pageButtons.forEach( button => {
			button.addEventListener( 'click', handlePaginationClick );
		} );
	}

	function generatePaginationHTML( currentPage, totalPages ) {
		if ( totalPages <= 1 ) {
			return '<div class="no-pagination">All results shown</div>';
		}

		let html = '';
		const maxVisiblePages = 5;
		const halfVisible = Math.floor( maxVisiblePages / 2 );

		// Previous button
		const prevDisabled = currentPage === 1 ? ' disabled' : '';
		html += `<button class="page-nav prev${prevDisabled}" data-page="${currentPage - 1}" ${prevDisabled ? 'disabled' : ''}>
			<span>Previous</span>
		</button>`;

		// Page numbers
		html += '<div class="page-numbers">';
		
		let startPage = Math.max( 1, currentPage - halfVisible );
		let endPage = Math.min( totalPages, currentPage + halfVisible );

		// Adjust if we're near the beginning or end
		if ( endPage - startPage < maxVisiblePages - 1 ) {
			if ( startPage === 1 ) {
				endPage = Math.min( totalPages, startPage + maxVisiblePages - 1 );
			} else {
				startPage = Math.max( 1, endPage - maxVisiblePages + 1 );
			}
		}

		// First page + ellipsis
		if ( startPage > 1 ) {
			html += `<button class="page-number" data-page="1">1</button>`;
			if ( startPage > 2 ) {
				html += '<span class="page-ellipsis">...</span>';
			}
		}

		// Page numbers
		for ( let i = startPage; i <= endPage; i++ ) {
			const currentClass = i === currentPage ? ' current' : '';
			html += `<button class="page-number${currentClass}" data-page="${i}">${i}</button>`;
		}

		// Last page + ellipsis
		if ( endPage < totalPages ) {
			if ( endPage < totalPages - 1 ) {
				html += '<span class="page-ellipsis">...</span>';
			}
			html += `<button class="page-number" data-page="${totalPages}">${totalPages}</button>`;
		}

		html += '</div>';

		// Next button
		const nextDisabled = currentPage === totalPages ? ' disabled' : '';
		html += `<button class="page-nav next${nextDisabled}" data-page="${currentPage + 1}" ${nextDisabled ? 'disabled' : ''}>
			<span>Next</span>
		</button>`;

		return html;
	}

	function handlePaginationClick( e ) {
		e.preventDefault();
		
		const button = e.currentTarget;
		const page = parseInt( button.getAttribute( 'data-page' ) );
		
		if ( !page || button.disabled || button.classList.contains( 'disabled' ) ) {
			return;
		}

		console.log( 'DEBUG: Pagination clicked - going to page:', page );
		
		// Scroll to top of grid
		if ( associatedGrid ) {
			associatedGrid.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		}

		// Perform search for the new page
		performSearch( false, page );
	}

	function updateLoadingState( isLoading ) {
		if ( associatedGrid ) {
			if ( isLoading ) {
				associatedGrid.classList.add( 'loading' );
			} else {
				associatedGrid.classList.remove( 'loading' );
			}
		}

		if ( resultsCountElement ) {
			if ( isLoading ) {
				resultsCountElement.textContent = 'Loading results...';
			} else {
				resultsCountElement.textContent = 'Results loaded';
			}
		}
	}

	function showError() {
		if ( resultsCountElement ) {
			resultsCountElement.textContent = 'An error occurred';
		}
	}

	function updateGrid( posts ) {
		console.log( 'DEBUG: Filtering existing grid items based on', posts.length, 'results' );

		if ( ! associatedGrid ) {
			console.log( 'ERROR: No associated grid found!' );
			return;
		}

		// Update total results for count display
		totalResults = posts.length;
		updateResultsCount( totalResults );

		// Get all existing teaser items in the grid
		const existingTeaserItems = associatedGrid.querySelectorAll( '.teaser-item' );
		console.log( 'DEBUG: Found', existingTeaserItems.length, 'existing teaser items' );

		if ( existingTeaserItems.length === 0 ) {
			console.log( 'ERROR: No existing teaser items found to filter!' );
			return;
		}

		// Create a set of post IDs that should be visible
		const visiblePostIds = new Set( posts.map( post => String( post.id ) ) );
		console.log( 'DEBUG: Post IDs that should be visible:', Array.from( visiblePostIds ) );

		let visibleCount = 0;
		let hiddenCount = 0;

		// Check if grid uses JavaScript pagination
		const teaserGrid = associatedGrid.querySelector('.fau-teaser-grid');
		const isJsPagination = teaserGrid && teaserGrid.getAttribute('data-js-pagination') === 'true';
		const customBlockId = associatedGrid.getAttribute('data-custom-block-id');

		// Show/hide existing teaser items based on filter results
		existingTeaserItems.forEach( ( item ) => {
			// Try to get post ID from various possible attributes and patterns
			let postId = item.getAttribute( 'data-post-id' );
			
			// If no data-post-id, try to extract from teaser-title-{ID} pattern
			if ( !postId ) {
				const titleElement = item.querySelector( '[id^="teaser-title-"]' );
				if ( titleElement ) {
					postId = titleElement.id.replace( 'teaser-title-', '' );
				}
			}
			
			// Also try other possible attributes as fallback
			if ( !postId ) {
				postId = item.getAttribute( 'data-id' ) || 
						 item.id?.replace( 'post-', '' );
			}

			console.log( 'DEBUG: Checking teaser item with post ID:', postId );

			if ( postId && visiblePostIds.has( String( postId ) ) ) {
				// Mark as not filtered out
				item.classList.remove( 'filtered-out' );
				visibleCount++;
				console.log( 'DEBUG: Showing post ID:', postId );
				
				// If NOT using JS pagination, directly show the item
				if (!isJsPagination) {
					item.style.display = '';
					item.removeAttribute( 'hidden' );
				}
			} else if ( postId ) {
				// Mark as filtered out
				item.classList.add( 'filtered-out' );
				hiddenCount++;
				console.log( 'DEBUG: Hiding post ID:', postId );
				
				// If NOT using JS pagination, directly hide the item
				if (!isJsPagination) {
					item.style.display = 'none';
					item.setAttribute( 'hidden', 'hidden' );
				}
			} else {
				// No post ID found - leave item as is and log warning
				console.log( 'DEBUG: No post ID found for teaser item:', item );
			}
		} );

		console.log( 'DEBUG: Filter complete - visible:', visibleCount, 'hidden:', hiddenCount );

		// If using JavaScript pagination, trigger update event
		if (isJsPagination && customBlockId) {
			console.log( 'DEBUG: Triggering filter update event for JS pagination' );
			
			// Reset to page 1 after filtering
			currentPage = 1;
			
			// Emit filter update event
			document.dispatchEvent(new CustomEvent('fau-filter-update', {
				detail: {
					gridId: customBlockId,
					visibleCount: visibleCount,
					totalCount: existingTeaserItems.length
				}
			}));
			
			// Also update pagination if we have it
			if (paginationEnabled && associatedPagination) {
				// Calculate new total pages based on visible items
				const gridPostsPerPage = parseInt(associatedGrid.getAttribute('data-posts-per-page')) || 6;
				const newTotalPages = Math.ceil(visibleCount / gridPostsPerPage);
				updatePagination(1, newTotalPages);
			}
		}

		// Show "no results" message if no items are visible
		let noResultsMessage = associatedGrid.querySelector( '.no-results-message' );
		
		if ( visibleCount === 0 ) {
			if ( ! noResultsMessage ) {
				noResultsMessage = document.createElement( 'p' );
				noResultsMessage.className = 'no-results-message';
				noResultsMessage.setAttribute( 'role', 'status' );
				noResultsMessage.textContent = 'No items found matching your filters.';
				
				// Insert after the grid
				const teaserContainer = associatedGrid.querySelector( '.fau-teaser-grid' ) || associatedGrid;
				teaserContainer.appendChild( noResultsMessage );
			}
			noResultsMessage.style.display = 'block';
		} else {
			if ( noResultsMessage ) {
				noResultsMessage.style.display = 'none';
			}
		}

		// Apply current view class (preserve any view settings)
		if ( currentView ) {
			updateGridView( currentView );
		}

		// Hide loading state after filtering is complete
		updateLoadingState( false );

		// Trigger custom event for other components
		const contentUpdateEvent = new CustomEvent(
			'fauListFiltersContentUpdated',
			{
				detail: {
					data: { total: totalResults, posts: posts },
					grid: associatedGrid,
					blockId: blockId,
					visibleCount: visibleCount,
					hiddenCount: hiddenCount
				},
			}
		);
		document.dispatchEvent( contentUpdateEvent );
	}

	function getCurrentTeaserLayout( gridContainer ) {
		// Extract layout from grid classes
		const classList = Array.from( gridContainer.classList );
		for ( const className of classList ) {
			if ( className.startsWith( 'layout-' ) ) {
				return className.replace( 'layout-', '' );
			}
		}
		return null;
	}

	function createTeaserItem( post ) {
		// Safely extract post data with defaults
		const postId = post.id || 0;
		const postTitle = post.title || 'Untitled';
		let postExcerpt = post.excerpt || '';
		const postPermalink = post.permalink || '#';
		const postFeaturedImage = post.featured_image || '';
		const postDate = post.date || new Date().toISOString();
		const postCategories = post.categories || [];

		// Handle empty excerpt with a fallback
		if (!postExcerpt || postExcerpt.trim() === '') {
			postExcerpt = `Read more about ${postTitle}...`;
		}

		const article = document.createElement( 'article' );
		article.className = 'teaser-item post-teaser';
		article.setAttribute( 'role', 'listitem' );
		article.setAttribute( 'data-variant', 'post' );
		article.setAttribute( 'data-href', postPermalink );
		article.setAttribute( 'tabindex', '0' );
		article.setAttribute( 'aria-labelledby', `teaser-title-${ postId }` );

		// Create the teaser HTML structure that matches the original teaser grid format
		let teaserHTML = `
			<div class="teaser-image-wrapper">
				<div class="teaser-image">
		`;

		// Add featured image if available
		if ( postFeaturedImage ) {
			teaserHTML += `<img src="${ escapeHtml(
				postFeaturedImage
			) }" alt="${ escapeHtml( postTitle ) }" loading="lazy" />`;
		}

		// Create date object safely
		const dateObj = new Date( postDate );
		const day = dateObj.getDate() || 1;
		const monthYear =
			dateObj
				.toLocaleDateString( 'en-US', {
					month: 'short',
					year: 'numeric',
				} )
				.toUpperCase() || 'JAN 2024';

		teaserHTML += `
				</div>
				<div class="teaser-meta">
					<time datetime="${ escapeHtml( postDate ) }">
						<span class="date-day">${ escapeHtml( day ) }</span>
						<span class="date-month-year">${ escapeHtml( monthYear ) }</span>
					</time>
				</div>
			</div>
			<div class="teaser-content-wrapper">
				<div class="teaser-content">
					<div class="content-column">
		`;

		// Add categories if available
		if ( postCategories.length > 0 ) {
			teaserHTML += `<span class="category">${ escapeHtml(
				postCategories[ 0 ]
			) }</span>`;
		}

		// Add title
		teaserHTML += `
			<h4 class="clamp-3" id="teaser-title-${ postId }">
				<a href="${ escapeHtml( postPermalink ) }">${ escapeHtml( postTitle ) }</a>
			</h4>
		`;

		// Always add excerpt section, even if empty (with fallback)
		teaserHTML += `
			<div class="excerpt clamp-3">
				<span class="visually-hidden">${ escapeHtml( postExcerpt ) }</span>
				<span aria-hidden="true">${ escapeHtml( postExcerpt ) }</span>
			</div>
		`;

		teaserHTML += `
					</div>
					<div class="button-teaser">
						<a href="${ escapeHtml( postPermalink ) }" class="wp-block-button__link">
							<span class="screen-reader-text">Read more about ${ escapeHtml(
								postTitle
							) }</span>
						</a>
					</div>
				</div>
			</div>
		`;

		article.innerHTML = teaserHTML;

		// Add click handler to make the whole teaser clickable
		article.addEventListener( 'click', function ( e ) {
			if ( e.target.tagName !== 'A' ) {
				window.location.href = postPermalink;
			}
		} );

		article.addEventListener( 'keypress', function ( e ) {
			if ( e.key === 'Enter' || e.key === ' ' ) {
				e.preventDefault();
				window.location.href = postPermalink;
			}
		} );

		return article;
	}

	function updateResultsCount( total ) {
		if ( ! resultsCountElement ) return;

		if ( total === 0 ) {
			resultsCountElement.textContent = 'No results found';
		} else {
			resultsCountElement.textContent = `Total results: ${ total }`;
		}
	}

	function simulateResults() {
		// Simulate search results based on current filters
		const hasActiveFilters =
			Object.keys( currentFilters ).length > 0 ||
			currentSearch.length > 0;
		const baseTotal = 150;
		const filteredTotal = hasActiveFilters
			? Math.floor( Math.random() * 50 ) + 10
			: baseTotal;

		return {
			total: filteredTotal,
			items: [], // Would contain actual result items
			page: currentPage,
			per_page: resultsPerPage,
		};
	}

	function updateResultsDisplay() {
		const simulatedResults = simulateResults();
		updateResultsCount( simulatedResults.total );
	}

	function debounce( func, wait ) {
		let timeout;
		return function executedFunction( ...args ) {
			const later = () => {
				clearTimeout( timeout );
				func( ...args );
			};
			clearTimeout( timeout );
			timeout = setTimeout( later, wait );
		};
	}

	function escapeHtml( text ) {
		// Handle non-string values
		if ( text === null || text === undefined ) {
			return '';
		}

		// Convert to string if it's not already
		const str = String( text );

		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;',
		};
		return str.replace( /[&<>"']/g, function ( m ) {
			return map[ m ];
		} );
	}

	// Function to perform client-side filtering
	function performClientSideFilter(filterBlock, filterData) {
		const gridBlockId = filterBlock.getAttribute('data-grid-block-id');
		if (!gridBlockId) {
			console.log('No grid block ID found for client-side filtering');
			return;
		}
		
		// Find the associated teaser grid
		let teaserGrid = document.querySelector(`[data-custom-block-id="${gridBlockId}"] .fau-teaser-grid`);
		
		if (!teaserGrid) {
			console.log('No teaser grid found with custom block ID:', gridBlockId);
			return;
		}
		
		console.log('Performing client-side filtering with data:', filterData);
		
		// Get all teaser items
		const teaserItems = teaserGrid.querySelectorAll('.teaser-item');
		let visibleCount = 0;
		
		teaserItems.forEach(item => {
			let shouldShow = true;
			
			// Check each filter
			if (filterData.searchTerm) {
				const searchLower = filterData.searchTerm.toLowerCase();
				const title = item.querySelector('h2, h3, h4, h5, h6')?.textContent?.toLowerCase() || '';
				const excerpt = item.querySelector('.teaser-item__excerpt')?.textContent?.toLowerCase() || '';
				const content = item.textContent?.toLowerCase() || '';
				
				if (!title.includes(searchLower) && 
					!excerpt.includes(searchLower) && 
					!content.includes(searchLower)) {
					shouldShow = false;
				}
			}
			
			if (filterData.category && filterData.category !== '') {
				const itemCategories = item.getAttribute('data-categories')?.split(',') || [];
				if (!itemCategories.includes(filterData.category)) {
					shouldShow = false;
				}
			}
			
			if (filterData.tags && filterData.tags.length > 0) {
				const itemTags = item.getAttribute('data-tags')?.split(',') || [];
				const hasMatchingTag = filterData.tags.some(tag => itemTags.includes(tag));
				if (!hasMatchingTag) {
					shouldShow = false;
				}
			}
			
			if (filterData.year && filterData.year !== '') {
				const itemYear = item.getAttribute('data-year');
				if (itemYear !== filterData.year) {
					shouldShow = false;
				}
			}
			
			if (filterData.author && filterData.author !== '') {
				const itemAuthor = item.getAttribute('data-author');
				if (itemAuthor !== filterData.author) {
					shouldShow = false;
				}
			}
			
			// Apply visibility
			if (shouldShow) {
				item.classList.remove('filtered-out');
				item.style.display = '';
				visibleCount++;
			} else {
				item.classList.add('filtered-out');
				item.style.display = 'none';
			}
		});
		
		console.log(`Client-side filtering complete. ${visibleCount} items visible out of ${teaserItems.length}`);
		
		// Emit event for teaser grid to update pagination and reset to page 1
		document.dispatchEvent(new CustomEvent('fau-filter-update', {
			detail: {
				gridId: gridBlockId,
				visibleCount: visibleCount,
				resetToPage1: true // Add flag to indicate page reset
			}
		}));
	}

	// Clear all filters
	function clearAllFilters(filterBlock) {
		const searchInput = filterBlock.querySelector('.fau-list-filters__search input');
		const categorySelect = filterBlock.querySelector('.fau-list-filters__category select');
		const tagCheckboxes = filterBlock.querySelectorAll('.fau-list-filters__tags input[type="checkbox"]');
		const yearSelect = filterBlock.querySelector('.fau-list-filters__year select');
		const authorSelect = filterBlock.querySelector('.fau-list-filters__author select');
		
		// Clear all filter values
		if (searchInput) searchInput.value = '';
		if (categorySelect) categorySelect.value = '';
		if (yearSelect) yearSelect.value = '';
		if (authorSelect) authorSelect.value = '';
		tagCheckboxes.forEach(cb => cb.checked = false);
		
		// Emit clear event with resetToPage1 flag
		const gridBlockId = filterBlock.getAttribute('data-grid-block-id');
		if (gridBlockId) {
			document.dispatchEvent(new CustomEvent('fau-filter-clear', {
				detail: {
					gridId: gridBlockId,
					resetToPage1: true
				}
			}));
		}
		
		// Perform the search with cleared filters
		performSearch(filterBlock);
	}
}
