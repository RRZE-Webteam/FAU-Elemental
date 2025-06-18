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

	// State
	let currentFilters = {};
	let currentSearch = '';
	let currentSort = sortSelect ? sortSelect.value : '';
	let currentView = getCurrentView();
	let currentPage = 1;
	let totalResults = 0;
	let resultsPerPage =
		parseInt( blockElement.getAttribute( 'data-results-per-page' ) ) || 15;
	let filtersExpanded = false;

	// Initialize
	updateResultsDisplay();

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

	// Helper function to find associated grid
	function findAssociatedGrid( filterId ) {
		// Look for a grid with matching filter-block-id
		const grids = document.querySelectorAll(
			'.filterable-grid, .fau-teaser-grid'
		);
		for ( let grid of grids ) {
			if ( grid.getAttribute( 'data-filter-block-id' ) === filterId ) {
				return grid;
			}
		}

		// Fallback: look for the closest grid after this filter block
		let nextElement = blockElement.nextElementSibling;
		while ( nextElement ) {
			if (
				nextElement.classList.contains( 'fau-teaser-grid' ) ||
				nextElement.classList.contains( 'filterable-grid' ) ||
				nextElement.querySelector(
					'.fau-teaser-grid, .filterable-grid'
				)
			) {
				return (
					nextElement.querySelector(
						'.fau-teaser-grid, .filterable-grid'
					) || nextElement
				);
			}
			nextElement = nextElement.nextElementSibling;
		}

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
						'filter',
						filterName
					);
					filterChipsContainer.appendChild( chip );
				}
			);

			// Show/hide clear all button
			const totalActiveFilters =
				Object.keys( currentFilters ).length + ( hasSearch ? 1 : 0 );
			if ( totalActiveFilters > 1 ) {
				clearAllButton.style.display = 'inline-block';
			} else {
				clearAllButton.style.display = 'none';
			}
		} else {
			activeFiltersContainer.style.display = 'none';
		}
	}

	function createFilterChip( name, value, type, filterKey = null ) {
		const chip = document.createElement( 'div' );
		chip.className = 'filter-chip';

		// Clean up the display value (remove count numbers in parentheses)
		const displayValue = value.replace( /\s*\(\d+\)$/, '' );

		chip.innerHTML = `
            <span class="chip-content">
                <span class="chip-name">${ escapeHtml( name ) }:</span>
                <span class="chip-value">${ escapeHtml( displayValue ) }</span>
            </span>
            <button type="button" class="chip-remove" aria-label="Remove ${ escapeHtml(
				name
			) } filter" title="Remove ${ escapeHtml( name ) } filter">
                <span class="chip-remove-icon" aria-hidden="true">×</span>
            </button>
        `;

		const removeButton = chip.querySelector( '.chip-remove' );
		removeButton.addEventListener( 'click', () => {
			if ( type === 'search' ) {
				clearSearch();
			} else if ( type === 'filter' && filterKey ) {
				removeFilter( filterKey );
			}
		} );

		return chip;
	}

	function removeFilter( filterKey ) {
		delete currentFilters[ filterKey ];

		// Reset the corresponding select element
		const correspondingSelect = Array.from( filterSelects ).find(
			( select ) =>
				select.getAttribute( 'data-filter-name' ) === filterKey
		);
		if ( correspondingSelect ) {
			correspondingSelect.value = '';
			correspondingSelect.classList.remove( 'has-selection' );
		}

		updateFilterChips();
		updateFilterLabels();
		currentPage = 1;
		performSearch();
	}

	function updateFilterLabels() {
		filterSelects.forEach( ( select ) => {
			const filterName = select.getAttribute( 'data-filter-name' );
			const hasSelection = currentFilters[ filterName ];
			const label = select.previousElementSibling;

			if ( label && label.classList.contains( 'filter-label' ) ) {
				if ( hasSelection ) {
					const selectedCount = 1; // Could be enhanced to show multiple selections
					label.textContent = `${ selectedCount } Filter selected`;
					label.classList.add( 'has-selection' );
				} else {
					// Reset to original text
					const originalText = filterName;
					label.textContent = originalText;
					label.classList.remove( 'has-selection' );
				}
			}
		} );
	}

	function toggleMoreFilters() {
		const hiddenFilterFields = blockElement.querySelectorAll(
			'.filter-field.hidden'
		);
		const dynamicContainer = blockElement.querySelector(
			'.dynamic-filters-container'
		);
		const showMoreText = showMoreButton.querySelector( '.show-more-text' );
		const showLessText = showMoreButton.querySelector( '.show-less-text' );

		filtersExpanded = ! filtersExpanded;

		if ( filtersExpanded ) {
			// Show hidden configured filters
			hiddenFilterFields.forEach( ( field ) => {
				field.classList.remove( 'hidden' );
				field.classList.add( 'revealed' );
			} );

			// Show dynamic filter addition interface
			if ( dynamicContainer ) {
				dynamicContainer.style.display = 'block';
				createDynamicFilterInterface();
			}

			// Update button text
			showMoreText.style.display = 'none';
			showLessText.style.display = 'inline';
		} else {
			// Hide filters
			hiddenFilterFields.forEach( ( field ) => {
				field.classList.add( 'hidden' );
				field.classList.remove( 'revealed' );
			} );

			// Hide dynamic filters
			if ( dynamicContainer ) {
				dynamicContainer.style.display = 'none';
			}

			// Update button text
			showMoreText.style.display = 'inline';
			showLessText.style.display = 'none';
		}

		// Update button aria-expanded
		showMoreButton.setAttribute(
			'aria-expanded',
			filtersExpanded.toString()
		);
	}

	function createDynamicFilterInterface() {
		const dynamicContainer = blockElement.querySelector(
			'.dynamic-filters-container'
		);
		const availableFiltersData = showMoreButton
			? showMoreButton.getAttribute( 'data-available-filters' )
			: null;

		if ( ! dynamicContainer || ! availableFiltersData ) return;

		// Clear existing content
		dynamicContainer.innerHTML = '';

		try {
			const availableFilters = JSON.parse( availableFiltersData );

			// Create header for dynamic filters section
			const dynamicHeader = document.createElement( 'div' );
			dynamicHeader.className = 'dynamic-filters-header';
			dynamicHeader.innerHTML = '<h4>Zusätzliche Filter</h4>';
			dynamicContainer.appendChild( dynamicHeader );

			// Create container for available filter buttons
			const availableFiltersContainer = document.createElement( 'div' );
			availableFiltersContainer.className = 'available-filters-container';
			dynamicContainer.appendChild( availableFiltersContainer );

			// Container for added dynamic filters
			const addedFiltersContainer = document.createElement( 'div' );
			addedFiltersContainer.className = 'added-dynamic-filters';
			dynamicContainer.appendChild( addedFiltersContainer );

			// Create buttons for each available filter type
			updateAvailableFilterButtons(
				availableFilters,
				availableFiltersContainer,
				addedFiltersContainer
			);
		} catch ( error ) {
			console.error( 'Error parsing available filters:', error );
		}
	}

	function updateAvailableFilterButtons(
		availableFilters,
		availableFiltersContainer,
		addedFiltersContainer
	) {
		// Clear existing buttons
		availableFiltersContainer.innerHTML = '';

		// Get currently added filter types
		const addedFilterTypes = Array.from(
			addedFiltersContainer.querySelectorAll( '.filter-field--dynamic' )
		).map( ( field ) => field.getAttribute( 'data-filter-key' ) );

		// Get configured filter types
		const configuredFilterTypes = Array.from( filterSelects )
			.map( ( select ) => select.getAttribute( 'data-filter-type' ) )
			.filter( ( type ) => type !== 'configured' );

		// Create buttons for available filters
		Object.entries( availableFilters ).forEach(
			( [ filterKey, filterData ] ) => {
				// Skip if filter is already configured or already added
				if (
					configuredFilterTypes.includes( filterKey ) ||
					addedFilterTypes.includes( filterKey )
				) {
					return;
				}

				// Skip if no options available
				if ( ! filterData.options || filterData.options.length === 0 ) {
					return;
				}

				// Create add filter button
				const addButton = document.createElement( 'button' );
				addButton.type = 'button';
				addButton.className = 'add-filter-button';
				addButton.innerHTML = `
				<span class="add-filter-icon">+</span>
				<span class="add-filter-text">${ filterData.label }</span>
				<span class="add-filter-count">(${ filterData.options.length })</span>
			`;
				addButton.title = `${ filterData.label } Filter hinzufügen`;

				// Add click handler
				addButton.addEventListener( 'click', function () {
					addDynamicFilter( filterKey, filterData );
					// Update available buttons after adding a filter
					updateAvailableFilterButtons(
						availableFilters,
						availableFiltersContainer,
						addedFiltersContainer
					);
				} );

				availableFiltersContainer.appendChild( addButton );
			}
		);

		// Show message if no more filters available
		if ( availableFiltersContainer.children.length === 0 ) {
			const noFiltersMessage = document.createElement( 'p' );
			noFiltersMessage.className = 'no-more-filters-message';
			noFiltersMessage.textContent =
				'Alle verfügbaren Filter wurden bereits hinzugefügt.';
			availableFiltersContainer.appendChild( noFiltersMessage );
		}
	}

	function addDynamicFilter( filterKey, filterData ) {
		const addedFiltersContainer = blockElement.querySelector(
			'.added-dynamic-filters'
		);
		if ( ! addedFiltersContainer ) return;

		const filterId = blockId + '-dynamic-filter-' + filterKey;

		// Check if filter already exists
		if ( blockElement.querySelector( `#${ filterId }` ) ) {
			return; // Filter already added
		}

		// Create filter field
		const filterField = document.createElement( 'div' );
		filterField.className = 'filter-field filter-field--dynamic';
		filterField.setAttribute( 'data-filter-key', filterKey );

		// Create label
		const label = document.createElement( 'label' );
		label.setAttribute( 'for', filterId );
		label.className = 'filter-label';
		label.textContent = filterData.label;

		// Create select element
		const select = document.createElement( 'select' );
		select.id = filterId;
		select.className = 'filter-select';
		select.setAttribute( 'data-filter-name', filterData.label );
		select.setAttribute( 'data-filter-type', filterKey );

		// Add default option
		const defaultOption = document.createElement( 'option' );
		defaultOption.value = '';
		defaultOption.textContent = `Alle ${ filterData.label }`;
		select.appendChild( defaultOption );

		// Add filter options
		filterData.options.forEach( ( option ) => {
			const optionElement = document.createElement( 'option' );
			optionElement.value = option.value;
			const countDisplay = option.count ? ` (${ option.count })` : '';
			optionElement.textContent = option.label + countDisplay;
			select.appendChild( optionElement );
		} );

		// Create remove button
		const removeButton = document.createElement( 'button' );
		removeButton.type = 'button';
		removeButton.className = 'remove-dynamic-filter';
		removeButton.innerHTML = '×';
		removeButton.title = `${ filterData.label } entfernen`;
		removeButton.setAttribute(
			'aria-label',
			`Remove ${ filterData.label } filter`
		);

		// Add event listeners
		select.addEventListener( 'change', handleFilterChange );
		removeButton.addEventListener( 'click', function () {
			removeDynamicFilter( filterField, filterKey );
		} );

		// Assemble filter field
		filterField.appendChild( label );
		filterField.appendChild( select );
		filterField.appendChild( removeButton );
		addedFiltersContainer.appendChild( filterField );

		// Update filterSelects NodeList
		updateFilterSelectsList();
	}

	function removeDynamicFilter( filterField, filterKey ) {
		const filterName =
			filterField.querySelector( '.filter-label' ).textContent;

		// Remove from current filters if it was selected
		if ( currentFilters[ filterName ] ) {
			delete currentFilters[ filterName ];
			updateFilterChips();
			performSearch();
		}

		// Remove the filter field
		filterField.remove();

		// Update filterSelects NodeList
		updateFilterSelectsList();

		// Refresh available filter buttons to show the removed filter as available again
		const dynamicContainer = blockElement.querySelector(
			'.dynamic-filters-container'
		);
		if ( dynamicContainer ) {
			const availableFiltersContainer = dynamicContainer.querySelector(
				'.available-filters-container'
			);
			const addedFiltersContainer = dynamicContainer.querySelector(
				'.added-dynamic-filters'
			);
			const availableFiltersData = showMoreButton
				? showMoreButton.getAttribute( 'data-available-filters' )
				: null;

			if (
				availableFiltersContainer &&
				addedFiltersContainer &&
				availableFiltersData
			) {
				try {
					const availableFilters = JSON.parse( availableFiltersData );
					updateAvailableFilterButtons(
						availableFilters,
						availableFiltersContainer,
						addedFiltersContainer
					);
				} catch ( error ) {
					console.error(
						'Error refreshing available filters:',
						error
					);
				}
			}
		}
	}

	function updateFilterSelectsList() {
		// Update the filterSelects NodeList to include newly added dynamic filters
		const newFilterSelects =
			blockElement.querySelectorAll( '.filter-select' );

		// Remove old event listeners and add new ones
		newFilterSelects.forEach( ( select ) => {
			// Check if listener already exists by checking for a custom property
			if ( ! select.hasFilterListener ) {
				select.addEventListener( 'change', handleFilterChange );
				select.hasFilterListener = true;
			}
		} );
	}

	function clearAllFilters() {
		// Clear all filter selections
		currentFilters = {};
		currentSearch = '';

		// Reset all select elements
		filterSelects.forEach( ( select ) => {
			select.value = '';
			select.classList.remove( 'has-selection' );
		} );

		// Reset search input
		if ( searchInput ) {
			searchInput.value = '';
			searchInput.classList.remove( 'has-value' );
			if ( searchClear ) {
				searchClear.style.display = 'none';
			}
		}

		updateFilterChips();
		updateFilterLabels();
		currentPage = 1;
		performSearch();
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

	function performSearch() {
		if ( ! associatedGrid ) {
			// No associated grid found, just update results display
			updateResultsDisplay();
			return;
		}

		showLoadingState();

		// Get teaser grid configuration
		const gridVariant = associatedGrid.getAttribute( 'data-variant' ) || 'post';
		const gridCategory = associatedGrid.getAttribute( 'data-category' ) || '0';

		// Prepare search parameters
		const searchParams = {
			action: 'fau_filter_teaser_grid',
			nonce: fauListFilters?.nonce || '',
			search: currentSearch,
			sort: currentSort,
			page: currentPage,
			per_page: resultsPerPage,
			post_type: gridVariant,
			category: parseInt( gridCategory ),
		};

		// Add filters - need to serialize them properly for PHP
		Object.entries( currentFilters ).forEach( ( [ filterName, filterData ], index ) => {
			searchParams[`filters[${filterName}][value]`] = filterData.value;
			searchParams[`filters[${filterName}][label]`] = filterData.label;
			searchParams[`filters[${filterName}][type]`] = filterData.type;
		} );

		// Debug logging - show what we're sending
		console.log( 'AJAX Request Parameters:' );
		console.log( '- Search:', currentSearch );
		console.log( '- Filters:', currentFilters );
		console.log( '- Filters JSON:', JSON.stringify( currentFilters ) );
		console.log( '- Sort:', currentSort );
		console.log( '- Page:', currentPage );
		console.log( '- Per Page:', resultsPerPage );
		console.log( '- Post Type:', gridVariant );
		console.log( '- Category:', parseInt( gridCategory ) );
		console.log( 'Full searchParams object:', searchParams );
		
		// Also log the serialized body to see exactly what's being sent
		const serializedBody = new URLSearchParams( searchParams ).toString();
		console.log( 'Serialized request body:', serializedBody );

		// Make AJAX request
		fetch( fauListFilters?.ajaxUrl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams( searchParams ).toString(),
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				console.log( 'Filter response:', data ); // Debug log
				console.log( 'Posts array:', data.posts ); // Debug posts specifically
				console.log( 'Posts length:', data.posts ? data.posts.length : 'undefined' ); // Debug posts length
				console.log( 'Debug args from server:', data.debug_args ); // Show server query args
				if ( data.success ) {
					updateGridContent( data );
				} else {
					console.error( 'Filter request failed:', data );
					// Fallback to simulation if AJAX fails
					const simulatedResults = simulateResults();
					updateGridContent( simulatedResults );
				}
				hideLoadingState();
			} )
			.catch( ( error ) => {
				console.error( 'Filter request error:', error );
				// Fallback to simulation on error
				const simulatedResults = simulateResults();
				updateGridContent( simulatedResults );
				hideLoadingState();
			} );
	}

	function showLoadingState() {
		if ( associatedGrid ) {
			associatedGrid.classList.add( 'loading' );

			// Add loading overlay if it doesn't exist
			let loadingOverlay =
				associatedGrid.querySelector( '.loading-overlay' );
			if ( ! loadingOverlay ) {
				loadingOverlay = document.createElement( 'div' );
				loadingOverlay.className = 'loading-overlay';
				loadingOverlay.innerHTML =
					'<div class="loading-spinner"></div>';
				associatedGrid.appendChild( loadingOverlay );
			}
			loadingOverlay.style.display = 'flex';
		}

		if ( resultsCountElement ) {
			resultsCountElement.textContent = 'Loading results...';
		}
	}

	function hideLoadingState() {
		if ( associatedGrid ) {
			associatedGrid.classList.remove( 'loading' );
			const loadingOverlay =
				associatedGrid.querySelector( '.loading-overlay' );
			if ( loadingOverlay ) {
				loadingOverlay.style.display = 'none';
			}
		}
	}

	function updateGridContent( data ) {
		console.log( 'updateGridContent called with:', data );
		
		if ( ! associatedGrid ) {
			console.log( 'No associated grid found!' );
			return;
		}

		// Update total results for count display
		totalResults = data.total || 0;
		updateResultsCount( data );

		// Get the grid container (look for the actual content container)
		const gridContainer = associatedGrid.querySelector( '.fau-teaser-grid' ) || associatedGrid;
		
		if ( ! gridContainer ) {
			console.log( 'No grid container found!' );
			return;
		}

		console.log( 'Grid container found:', gridContainer );

		// Get the current layout from the grid classes
		const currentLayout = getCurrentTeaserLayout( gridContainer );
		console.log( 'Current layout:', currentLayout );

		// Clear existing content
		gridContainer.innerHTML = '';

		// Check if we have posts to display
		if ( data.posts && data.posts.length > 0 ) {
			console.log( 'Creating teaser items for', data.posts.length, 'posts' );
			
			// Create teaser items
			const teaserItems = data.posts.map( ( post, index ) => {
				console.log( 'Creating teaser item for post', index, ':', post );
				return createTeaserItem( post );
			} );
			
			console.log( 'Created', teaserItems.length, 'teaser items' );
			
			// Apply layout wrapping if needed
			if ( currentLayout && [ 'l2s', '2sl' ].includes( currentLayout ) ) {
				console.log( 'Applying layout wrapping for', currentLayout );
				// Wrap items in groups of 3 for these layouts
				for ( let i = 0; i < teaserItems.length; i += 3 ) {
					const groupItems = teaserItems.slice( i, i + 3 );
					if ( groupItems.length > 0 ) {
						const groupDiv = document.createElement( 'div' );
						groupDiv.className = 'teaser-group';
						groupItems.forEach( item => groupDiv.appendChild( item ) );
						gridContainer.appendChild( groupDiv );
					}
				}
			} else {
				console.log( 'Applying direct layout for', currentLayout );
				// For other layouts, just append items directly
				teaserItems.forEach( ( item, index ) => {
					console.log( 'Appending item', index, 'to grid' );
					gridContainer.appendChild( item );
				} );
			}
			
			console.log( 'Grid container after adding items:', gridContainer.innerHTML.length, 'characters' );
		} else {
			console.log( 'No posts to display, showing no results message' );
			// Show no results message
			const noResultsMessage = document.createElement( 'p' );
			noResultsMessage.className = 'no-results-message';
			noResultsMessage.setAttribute( 'role', 'status' );
			noResultsMessage.textContent = 'No items found matching your filters.';
			gridContainer.appendChild( noResultsMessage );
		}

		// Apply current view class
		if ( currentView ) {
			updateGridView( currentView );
		}

		// Trigger custom event for other components
		const contentUpdateEvent = new CustomEvent( 'fauListFiltersContentUpdated', {
			detail: {
				data: data,
				grid: associatedGrid,
				blockId: blockId,
			},
		} );
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
		const postExcerpt = post.excerpt || '';
		const postPermalink = post.permalink || '#';
		const postFeaturedImage = post.featured_image || '';
		const postDate = post.date || new Date().toISOString();
		const postCategories = post.categories || [];

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
			teaserHTML += `<img src="${ escapeHtml( postFeaturedImage ) }" alt="${ escapeHtml( postTitle ) }" loading="lazy" />`;
		}

		// Create date object safely
		const dateObj = new Date( postDate );
		const day = dateObj.getDate() || 1;
		const monthYear = dateObj.toLocaleDateString( 'en-US', { month: 'short', year: 'numeric' } ).toUpperCase() || 'JAN 2024';

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
			teaserHTML += `<span class="category">${ escapeHtml( postCategories[0] ) }</span>`;
		}

		// Add title
		teaserHTML += `
			<h4 class="clamp-3" id="teaser-title-${ postId }">
				<a href="${ escapeHtml( postPermalink ) }">${ escapeHtml( postTitle ) }</a>
			</h4>
		`;

		// Add excerpt
		if ( postExcerpt ) {
			teaserHTML += `
				<div class="excerpt clamp-3">
					<span class="visually-hidden">${ escapeHtml( postExcerpt ) }</span>
					<span aria-hidden="true">${ escapeHtml( postExcerpt ) }</span>
				</div>
			`;
		}

		teaserHTML += `
					</div>
					<div class="button-teaser">
						<a href="${ escapeHtml( postPermalink ) }" class="wp-block-button__link">
							<span class="screen-reader-text">Read more about ${ escapeHtml( postTitle ) }</span>
						</a>
					</div>
				</div>
			</div>
		`;

		article.innerHTML = teaserHTML;
		
		// Add click handler to make the whole teaser clickable
		article.addEventListener( 'click', function( e ) {
			if ( e.target.tagName !== 'A' ) {
				window.location.href = postPermalink;
			}
		} );

		article.addEventListener( 'keypress', function( e ) {
			if ( e.key === 'Enter' || e.key === ' ' ) {
				e.preventDefault();
				window.location.href = postPermalink;
			}
		} );

		return article;
	}

	function updateResultsCount( data ) {
		if ( ! resultsCountElement ) return;

		const total = data.total || 0;
		const start = total > 0 ? ( currentPage - 1 ) * resultsPerPage + 1 : 0;
		const end = Math.min( currentPage * resultsPerPage, total );

		if ( total === 0 ) {
			resultsCountElement.textContent = 'No results found';
		} else {
			resultsCountElement.textContent = `${ start } to ${ end } from ${ total } records`;
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
		updateResultsCount( simulatedResults );
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
}
