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

	// Find associated teaser grid
	const associatedGrid = findAssociatedGrid( blockId );

	// Check if grid was found
	if ( ! associatedGrid ) {
		return;
	}

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

	// State
	let currentFilters = {};
	let currentSearch = '';
	let currentView = getCurrentView();
	let currentPage = 1;
	const resultsPerPage =
		parseInt( blockElement.getAttribute( 'data-results-per-page' ), 10 ) ||
		6;
	let filtersExpanded = false;
	let paginationEnabled = false;
	let associatedPagination = null;

	// Find associated pagination block
	const paginationBlockId = associatedGrid?.getAttribute(
		'data-pagination-block-id'
	);

	if ( paginationBlockId ) {
		associatedPagination = document.querySelector(
			`.wp-block-fau-elemental-fau-pagination[data-block-id="${ paginationBlockId }"]`
		);
		if ( associatedPagination ) {
			paginationEnabled = true;
		} else {
			// Try alternative selectors
			const altPagination = document.querySelector(
				`#${ paginationBlockId }`
			);
			if ( altPagination ) {
				associatedPagination = altPagination;
				paginationEnabled = true;
			}
		}
	}

	// Initialize - Load actual data instead of simulating
	loadInitialData();

	// Set initial view class on main article
	if ( currentView ) {
		updateGridView( currentView );
	}

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
		if ( ! associatedGrid ) {
			return;
		}

		// Get all existing teaser items in the grid
		const existingTeaserItems =
			associatedGrid.querySelectorAll( '.teaser-item' );

		// Extract categories, tags, authors, etc. from existing items
		const availableOptions = {
			categories: new Set(),
			tags: new Set(),
			authors: new Set(),
			years: new Set(),
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
					if ( year && ! isNaN( year ) ) {
						availableOptions.years.add( year.toString() );
					}
				}
			} );

			// Extract authors from author elements (if they exist)
			const authorElements = item.querySelectorAll(
				'.author, .post-author'
			);
			authorElements.forEach( ( authorEl ) => {
				const authorName = authorEl.textContent.trim();
				if ( authorName ) {
					availableOptions.authors.add( authorName );
				}
			} );
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
					options: Array.from( availableOptions.categories ).map(
						( cat ) => ( {
							value: cat.toLowerCase().replace( /\s+/g, '-' ),
							label: cat,
						} )
					),
				},
				years: {
					label: 'Year',
					options: Array.from( availableOptions.years )
						.sort( ( a, b ) => b - a )
						.map( ( year ) => ( {
							value: year,
							label: year,
						} ) ),
				},
			};

			// Only include categories with options
			const filteredData = {};
			Object.entries( dynamicFilterData ).forEach( ( [ key, data ] ) => {
				if ( data.options.length > 0 ) {
					filteredData[ key ] = data;
				}
			} );

			showMoreButton.setAttribute(
				'data-available-filters',
				JSON.stringify( filteredData )
			);
		}
	}

	// Helper function to find associated grid
	function findAssociatedGrid( filterId ) {
		// Method 1: Look for a grid with matching filter-block-id
		const grids = document.querySelectorAll(
			'.filterable-grid, .fau-teaser-grid'
		);

		for ( const grid of grids ) {
			const gridFilterId = grid.getAttribute( 'data-filter-block-id' );

			if ( gridFilterId === filterId ) {
				return grid;
			}
		}

		// Method 2: If no exact match, find the closest grid after this filter block
		let nextElement = blockElement.nextElementSibling;
		while ( nextElement ) {
			if (
				nextElement.classList.contains( 'fau-teaser-grid' ) ||
				nextElement.classList.contains( 'filterable-grid' ) ||
				nextElement.querySelector(
					'.fau-teaser-grid, .filterable-grid'
				)
			) {
				const foundGrid =
					nextElement.querySelector(
						'.fau-teaser-grid, .filterable-grid'
					) || nextElement;

				return foundGrid;
			}
			nextElement = nextElement.nextElementSibling;
		}

		// Method 3: If still no grid found, look for any grid on the page
		if ( grids.length > 0 ) {
			return grids[ 0 ];
		}

		return null;
	}

	// Search functions
	function handleSearch() {
		const searchTerm = searchInput.value.trim();
		if ( searchTerm !== currentSearch ) {
			currentSearch = searchTerm;
			performSearch( false, 1 );
			updateSearchClearButton();
		}
	}

	function clearSearch() {
		searchInput.value = '';
		currentSearch = '';
		performSearch( false, 1 );
		updateSearchClearButton();
	}

	function updateSearchClearButton() {
		if ( searchClear ) {
			if ( currentSearch ) {
				searchClear.style.display = 'block';
				searchInput.classList.add( 'has-value' );
			} else {
				searchClear.style.display = 'none';
				searchInput.classList.remove( 'has-value' );
			}
		}
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

		performSearch( false, 1 );
		updateFilterChips();
		updateFilterLabels();
	}

	function updateFilterChips() {
		if ( ! filterChipsContainer ) {
			return;
		}

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
			if ( showMoreText ) {
				showMoreText.style.display = 'none';
			}
			if ( showLessText ) {
				showLessText.style.display = 'inline';
			}
			showMoreButton.setAttribute( 'aria-expanded', 'true' );
		} else {
			if ( showMoreText ) {
				showMoreText.style.display = 'inline';
			}
			if ( showLessText ) {
				showLessText.style.display = 'none';
			}
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
		if ( ! dynamicContainer ) {
			return;
		}

		dynamicContainer.style.display = 'block';

		// Get available filters from the show more button
		const availableFiltersData = showMoreButton.getAttribute(
			'data-available-filters'
		);
		let availableFilters = {};

		try {
			availableFilters = JSON.parse( availableFiltersData );
		} catch ( e ) {
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
		if ( ! buttonsContainer ) {
			return;
		}

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
		const addedFiltersContainer =
			blockElement.querySelector( '.added-filters' );
		if ( ! addedFiltersContainer ) {
			return;
		}

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
					<option value="">All ${ escapeHtml( filterData.label ) }</option>
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

	function removeDynamicFilter( filterField ) {
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
		const availableFiltersContainer =
			blockElement.querySelector( '.available-filters' );
		const addedFiltersContainer =
			blockElement.querySelector( '.added-filters' );

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
				// Silently handle parsing errors
			}
		}
	}

	function updateFilterSelectsList() {
		// Update the filterSelects NodeList to include new dynamic filters
		// Note: We can't reassign the NodeList, but we can work with the updated one
		// This is mainly for reference - the event listeners are already attached individually
	}

	function clearAllFilters() {
		// Clear search
		if ( searchInput ) {
			searchInput.value = '';
			currentSearch = '';
			searchClear.style.display = 'none';
			searchInput.classList.remove( 'has-value' );
		}

		// Clear all filter selects
		const allFilterSelects =
			blockElement.querySelectorAll( '.filter-select' );
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
		if ( ! associatedGrid ) {
			return;
		}

		// Remove existing view classes from grid
		associatedGrid.classList.remove(
			'view-cards',
			'view-table',
			'view-list'
		);

		// Add new view class to grid
		associatedGrid.classList.add( `view-${ view }` );

		// Find the main article element and add/remove table view class
		const mainArticle =
			document.querySelector( 'main' ) ||
			document.querySelector( 'article' );
		if ( mainArticle ) {
			// Remove existing view classes from main article
			mainArticle.classList.remove(
				'table-view',
				'cards-view',
				'list-view'
			);

			// Add the new view class to main article
			mainArticle.classList.add( `${ view }-view` );
		}

		// Trigger custom event for other components to listen to
		const viewChangeEvent = new CustomEvent( 'fauListFiltersViewChange', {
			detail: { view, grid: associatedGrid },
		} );
		document.dispatchEvent( viewChangeEvent );
	}

	function handleSortChange() {
		performSearch( false, 1 );
	}

	function performSearch( isInitial = false, page = 1 ) {
		if ( ! associatedGrid ) {
			return;
		}

		// Update current page
		currentPage = page;

		// Update URL for pagination state
		const url = new URL( window.location );
		if ( currentPage > 1 ) {
			url.searchParams.set( 'paged', currentPage );
		} else {
			url.searchParams.delete( 'paged' );
		}
		if ( ! isInitial && window.location.href !== url.href ) {
			window.history.pushState( { path: url.href }, '', url.href );
		}

		// Check if grid uses JavaScript pagination
		const teaserGrid = associatedGrid.querySelector( '.fau-teaser-grid' );
		const isJsPagination =
			teaserGrid &&
			teaserGrid.getAttribute( 'data-js-pagination' ) === 'true';

		if ( isJsPagination ) {
			performClientSideFilter();
			return;
		}

		// Original server-side filtering code continues below...
		// Read grid attributes to respect its settings
		const gridVariant =
			associatedGrid.getAttribute( 'data-variant' ) || 'post';
		const gridCategory =
			associatedGrid.getAttribute( 'data-category' ) || '0';

		// Get list of post IDs currently in the grid to limit filtering scope
		const existingTeaserItems =
			associatedGrid.querySelectorAll( '.teaser-item' );
		const gridPostIds = [];

		existingTeaserItems.forEach( ( item ) => {
			let postId = item.getAttribute( 'data-post-id' );

			// If no data-post-id, try to extract from teaser-title-{ID} pattern
			if ( ! postId ) {
				const titleElement = item.querySelector(
					'[id^="teaser-title-"]'
				);
				if ( titleElement ) {
					postId = titleElement.id.replace( 'teaser-title-', '' );
				}
			}

			if ( postId ) {
				gridPostIds.push( postId );
			}
		} );

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
				const filterType = select.getAttribute( 'data-filter-type' );
				const taxonomy = select.getAttribute( 'data-taxonomy' );

				// Map filter types to the format expected by the AJAX handler
				let adjustedFilterType = filterType;
				if ( filterType === 'taxonomy' && taxonomy ) {
					// Map taxonomy types to the expected filter names
					if ( taxonomy === 'category' ) {
						adjustedFilterType = 'categories';
					} else if ( taxonomy === 'post_tag' ) {
						adjustedFilterType = 'tags';
					} else {
						adjustedFilterType = taxonomy; // Use taxonomy name directly
					}
				} else if ( filterType === 'author' ) {
					adjustedFilterType = 'authors';
				} else if ( filterType === 'date' ) {
					adjustedFilterType = 'years';
				}

				activeFilters[ filterName ] = {
					type: adjustedFilterType,
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

				// Dynamic filters already use the correct filter types from available options,
				// but ensure consistency with the AJAX handler expectations
				let adjustedFilterType = filterType;
				if ( filterType === 'category' ) {
					adjustedFilterType = 'categories';
				} else if ( filterType === 'post_tag' ) {
					adjustedFilterType = 'tags';
				} else if ( filterType === 'author' ) {
					adjustedFilterType = 'authors';
				} else if ( filterType === 'year' ) {
					adjustedFilterType = 'years';
				}

				activeFilters[ filterName ] = {
					type: adjustedFilterType,
					value: select.value,
				};
			}
		} );

		// Show loading state
		updateLoadingState( true );

		// Prepare AJAX data using grid attributes and limiting to grid post IDs
		const ajaxData = {
			action: 'fau_elemental_filter_posts',
			nonce: window.fauElemental?.nonce || '',
			search: searchValue,
			filters: JSON.stringify( activeFilters ), // Convert to JSON string
			sort: sortValue,
			page,
			posts_per_page: resultsPerPage, // Use resultsPerPage from filter block
			post_type: gridVariant, // Use grid's variant (post type)
			category: parseInt( gridCategory ), // Use grid's category setting
		};

		// Make AJAX request
		fetch( window.fauElemental?.ajaxUrl || '', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams( ajaxData ),
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				if ( data.success && data.data ) {
					const responseData = data.data;

					updateLoadingState( false );
					updateGrid( responseData.posts );
					updatePagination( currentPage, responseData.total_pages );
					updateResultsCount( responseData.total_posts );
				} else {
					updateLoadingState( false );
					showError();
				}
			} )
			.catch( () => {
				updateLoadingState( false );
				showError();
			} );
	}

	function updatePagination( currentPageNum, totalPagesNum ) {
		if ( ! paginationEnabled || ! associatedPagination ) {
			return;
		}

		// Hide pagination if there's only one page
		if ( totalPagesNum <= 1 ) {
			associatedPagination.style.display = 'none';
			return;
		}
		associatedPagination.style.display = '';

		const paginationHtml = generatePaginationHTML(
			currentPageNum,
			totalPagesNum
		);
		const paginationControls = associatedPagination.querySelector(
			'.pagination-controls'
		);

		if ( paginationControls ) {
			paginationControls.innerHTML = paginationHtml;
			// Add event listeners to the new pagination links
			paginationControls
				.querySelectorAll( 'a' )
				.forEach( ( link ) =>
					link.addEventListener( 'click', handlePaginationClick )
				);
		}
	}

	function generatePaginationHTML( currentPageNum, totalPagesNum ) {
		let html = '';
		const maxVisiblePages = 5;
		const halfVisible = Math.floor( maxVisiblePages / 2 );

		// Previous button
		const prevDisabled = currentPageNum === 1 ? ' disabled' : '';
		html += `<a href="#" class="page-nav prev${ prevDisabled }" data-page-number="${
			currentPageNum - 1
		}" ${ prevDisabled ? 'aria-disabled="true"' : '' }>
			<span>Previous</span>
		</a>`;

		// Page numbers
		html += '<div class="page-numbers">';

		let startPage = Math.max( 1, currentPageNum - halfVisible );
		let endPage = Math.min( totalPagesNum, currentPageNum + halfVisible );

		// Adjust if we're near the beginning or end
		if ( endPage - startPage < maxVisiblePages - 1 ) {
			if ( startPage === 1 ) {
				endPage = Math.min(
					totalPagesNum,
					startPage + maxVisiblePages - 1
				);
			} else {
				startPage = Math.max( 1, endPage - maxVisiblePages + 1 );
			}
		}

		// First page + ellipsis
		if ( startPage > 1 ) {
			html += `<a href="#" class="page-number" data-page-number="1">1</a>`;
			if ( startPage > 2 ) {
				html += '<span class="page-ellipsis">...</span>';
			}
		}

		// Page numbers
		for ( let i = startPage; i <= endPage; i++ ) {
			const currentClass = i === currentPageNum ? ' current' : '';
			html += `<a href="#" class="page-number${ currentClass }" data-page-number="${ i }">${ i }</a>`;
		}

		// Last page + ellipsis
		if ( endPage < totalPagesNum ) {
			if ( endPage < totalPagesNum - 1 ) {
				html += '<span class="page-ellipsis">...</span>';
			}
			html += `<a href="#" class="page-number" data-page-number="${ totalPagesNum }">${ totalPagesNum }</a>`;
		}

		html += '</div>';

		// Next button
		const nextDisabled =
			currentPageNum === totalPagesNum ? ' disabled' : '';
		html += `<a href="#" class="page-nav next${ nextDisabled }" data-page-number="${
			currentPageNum + 1
		}" ${ nextDisabled ? 'aria-disabled="true"' : '' }>
			<span>Next</span>
		</a>`;

		return html;
	}

	function handlePaginationClick( e ) {
		e.preventDefault();
		const link = e.target.closest( 'a' );

		if ( ! link ) {
			return;
		}

		const page = parseInt( link.getAttribute( 'data-page-number' ), 10 );

		if ( isNaN( page ) ) {
			return;
		}

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
		if ( ! associatedGrid ) {
			return;
		}

		if ( ! posts || posts.length === 0 ) {
			associatedGrid.innerHTML = `<p class="no-results">${
				window.fauElemental?.noResultsText || 'No results found.'
			}</p>`;
			return;
		}

		// Replace the grid content with the new HTML from the server.
		associatedGrid.innerHTML = posts
			.map( ( post ) => post.html_output )
			.join( '' );
	}

	function updateResultsCount( total ) {
		if ( ! resultsCountElement ) {
			return;
		}

		if ( total === 0 ) {
			resultsCountElement.textContent = 'No results found';
		} else {
			resultsCountElement.textContent = `Total results: ${ total }`;
		}
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
	function performClientSideFilter( filterBlock, filterData ) {
		const gridBlockId = filterBlock.getAttribute( 'data-grid-block-id' );
		if ( ! gridBlockId ) {
			return;
		}

		// Find the associated teaser grid
		const teaserGrid = document.querySelector(
			`[data-custom-block-id="${ gridBlockId }"] .fau-teaser-grid`
		);

		if ( ! teaserGrid ) {
			return;
		}

		// Get all teaser items
		const teaserItems = teaserGrid.querySelectorAll( '.teaser-item' );
		let visibleCount = 0;

		teaserItems.forEach( ( item ) => {
			let shouldShow = true;

			// Check each filter
			if ( filterData.searchTerm ) {
				const searchLower = filterData.searchTerm.toLowerCase();
				const title =
					item
						.querySelector( 'h2, h3, h4, h5, h6' )
						?.textContent?.toLowerCase() || '';
				const excerpt =
					item
						.querySelector( '.teaser-item__excerpt' )
						?.textContent?.toLowerCase() || '';
				const content = item.textContent?.toLowerCase() || '';

				if (
					! title.includes( searchLower ) &&
					! excerpt.includes( searchLower ) &&
					! content.includes( searchLower )
				) {
					shouldShow = false;
				}
			}

			if ( filterData.category && filterData.category !== '' ) {
				const itemCategories =
					item.getAttribute( 'data-categories' )?.split( ',' ) || [];
				if ( ! itemCategories.includes( filterData.category ) ) {
					shouldShow = false;
				}
			}

			if ( filterData.tags && filterData.tags.length > 0 ) {
				const itemTags =
					item.getAttribute( 'data-tags' )?.split( ',' ) || [];
				const hasMatchingTag = filterData.tags.some( ( tag ) =>
					itemTags.includes( tag )
				);
				if ( ! hasMatchingTag ) {
					shouldShow = false;
				}
			}

			if ( filterData.year && filterData.year !== '' ) {
				const itemYear = item.getAttribute( 'data-year' );
				if ( itemYear !== filterData.year ) {
					shouldShow = false;
				}
			}

			if ( filterData.author && filterData.author !== '' ) {
				const itemAuthor = item.getAttribute( 'data-author' );
				if ( itemAuthor !== filterData.author ) {
					shouldShow = false;
				}
			}

			// Apply visibility
			if ( shouldShow ) {
				item.classList.remove( 'filtered-out' );
				item.style.display = '';
				visibleCount++;
			} else {
				item.classList.add( 'filtered-out' );
				item.style.display = 'none';
			}
		} );

		// Emit event for teaser grid to update pagination and reset to page 1
		document.dispatchEvent(
			new CustomEvent( 'fau-filter-update', {
				detail: {
					gridId: gridBlockId,
					visibleCount,
					resetToPage1: true, // Add flag to indicate page reset
				},
			} )
		);
	}
}
