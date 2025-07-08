/**
 * Frontend JavaScript for FAU List Filters block
 */

document.addEventListener( 'DOMContentLoaded', function () {
	console.log( 'FAU LIST FILTERS: DOM Content Loaded - Initializing...' );

	const filterBlocks = document.querySelectorAll(
		'.wp-block-fau-elemental-fau-list-filters'
	);
	console.log( `FAU LIST FILTERS: Found ${ filterBlocks.length } filter blocks.` );

	filterBlocks.forEach( initializeFilterBlock );
} );

function initializeFilterBlock( blockElement ) {
	const blockId = blockElement.getAttribute( 'data-block-id' );
	console.log( `FAU LIST FILTERS: Initializing block #${ blockId }` );

	const associatedGrid = findAssociatedGrid( blockId );
	if ( ! associatedGrid ) {
		console.error(
			`FAU LIST FILTERS: No associated grid found for filter block #${ blockId }. Aborting.`
		);
		return;
	}

	// --- Element Selectors ---
	const searchInput = blockElement.querySelector( '.search-input' );
	const searchClear = blockElement.querySelector( '.search-clear' );
	const configuredFilterSelects = blockElement.querySelectorAll(
		'.filter-field--configured .filter-select'
	);
	const sortSelect = blockElement.querySelector( '.sort-select' );
	const resultsCountElement = blockElement.querySelector( '.results-text' );
	const showMoreButton = blockElement.querySelector( '.show-more-filters' );
	const activeFiltersContainer =
		blockElement.querySelector( '.active-filters' );
	const filterChipsContainer = blockElement.querySelector( '.filter-chips' );
	const clearAllButton = blockElement.querySelector( '.clear-all-filters' );

	// --- JS-managed container creation (robustness fix) ---
	let dynamicFiltersContainer = blockElement.querySelector(
		'.dynamic-filters-container'
	);
	if ( ! dynamicFiltersContainer ) {
		dynamicFiltersContainer = document.createElement( 'div' );
		dynamicFiltersContainer.className = 'dynamic-filters-container';
		const filterControls = blockElement.querySelector( '.filter-controls' );
		if ( filterControls ) {
			filterControls.prepend( dynamicFiltersContainer );
		}
	}

	let availableFiltersContainer = blockElement.querySelector(
		'.available-filters'
	);
	if ( ! availableFiltersContainer ) {
		availableFiltersContainer = document.createElement( 'div' );
		availableFiltersContainer.className = 'available-filters';
		availableFiltersContainer.innerHTML =
			'<h4>Add filters:</h4><div class="filter-buttons-container"></div>';
		dynamicFiltersContainer.appendChild( availableFiltersContainer );
	}

	let addedFiltersContainer = blockElement.querySelector( '.added-filters' );
	if ( ! addedFiltersContainer ) {
		addedFiltersContainer = document.createElement( 'div' );
		addedFiltersContainer.className = 'added-filters';
		dynamicFiltersContainer.appendChild( addedFiltersContainer );
	}
	// --- End of robustness fix ---

	// --- State Variables ---
	let currentPage = 1;
	let availableFilters = {};
	const teaserGrid = associatedGrid.querySelector( '.fau-teaser-grid' );
	const isJsPagination = teaserGrid?.dataset.jsPagination === 'true';
	const postsPerPage =
		parseInt( associatedGrid.dataset.postsPerPage, 10 ) || 6;

	console.log( `FAU LIST FILTERS: Block #${ blockId } Settings:`, {
		isJsPagination,
		postsPerPage,
	} );

	// --- Initialization ---
	if ( showMoreButton ) {
		try {
			availableFilters =
				JSON.parse( showMoreButton.dataset.availableFilters ) || {};
		} catch ( e ) {
			console.error(
				'FAU LIST FILTERS: Invalid JSON in data-available-filters attribute.',
				e
			);
		}
	}

	loadInitialData();
	createDynamicFilterInterface();

	// --- Event Listeners ---
	if ( searchInput ) {
		searchInput.addEventListener( 'input', debounce( handleSearch, 300 ) );
		searchInput.addEventListener( 'keypress', ( e ) => {
			if ( e.key === 'Enter' ) e.preventDefault();
		} );
	}
	if ( searchClear ) {
		searchClear.addEventListener( 'click', clearSearch );
	}
	configuredFilterSelects.forEach( ( select ) => {
		select.addEventListener( 'change', handleFilterChange );
	} );
	if ( sortSelect ) {
		sortSelect.addEventListener( 'change', handleSortChange );
	}
	if ( showMoreButton ) {
		showMoreButton.addEventListener( 'click', toggleMoreFilters );
	}
	if ( clearAllButton ) {
		clearAllButton.addEventListener( 'click', clearAllFilters );
	}

	// --- Main Functions ---
	function loadInitialData() {
		console.log( `FAU LIST FILTERS: #${ blockId } loadInitialData` );
		updateFilterChips();
		if ( isJsPagination ) {
			performClientSideFilter();
		} else {
			performSearch( true );
		}
	}

	function handleFilterChange() {
		console.log( `FAU LIST FILTERS: #${ blockId } handleFilterChange` );
		currentPage = 1;
		updateFilterChips();
		if ( isJsPagination ) {
			performClientSideFilter();
		} else {
			performSearch( false, 1 );
		}
	}

	function handleSearch() {
		console.log( `FAU LIST FILTERS: #${ blockId } handleSearch` );
		currentPage = 1;
		updateSearchClearButton();
		updateFilterChips();
		if ( isJsPagination ) {
			performClientSideFilter();
		} else {
			performSearch( false, 1 );
		}
	}

	function clearSearch() {
		console.log( `FAU LIST FILTERS: #${ blockId } clearSearch` );
		if ( searchInput ) {
			searchInput.value = '';
		}
		handleSearch();
	}

	function clearAllFilters() {
		console.log( `FAU LIST FILTERS: #${ blockId } clearAllFilters` );
		if ( searchInput ) {
			searchInput.value = '';
		}
		blockElement
			.querySelectorAll( '.filter-select' )
			.forEach( ( select ) => {
				select.value = '';
			} );

		// Remove all dynamic filters
		addedFiltersContainer
			.querySelectorAll( '.filter-field--dynamic' )
			.forEach( ( field ) => field.remove() );

		updateAvailableFilterButtons();
		handleFilterChange();
	}

	function handleSortChange() {
		console.log( `FAU LIST FILTERS: #${ blockId } handleSortChange` );
		if ( isJsPagination ) {
			performClientSideFilter();
		} else {
			performSearch( false, currentPage );
		}
	}

	function updateSearchClearButton() {
		if ( searchInput && searchClear ) {
			searchClear.classList.toggle(
				'search-clear--hidden',
				! searchInput.value
			);
		}
	}

	function findAssociatedGrid( filterId ) {
		const grid = document.querySelector(
			`.wp-block-fau-elemental-fau-teaser-grid[data-filter-block-id="${ filterId }"]`
		);
		if ( ! grid ) {
			console.warn( `FAU LIST FILTERS: Could not find grid for filter ID: ${ filterId }` );
		}
		return grid;
	}

	function performClientSideFilter() {
		console.log( `FAU LIST FILTERS: #${ blockId } performClientSideFilter` );

		const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
		const sortValue = sortSelect ? sortSelect.value : 'date';
		const allFilterSelects = blockElement.querySelectorAll( '.filter-select' );

		const activeFilters = Array.from( allFilterSelects )
			.filter( ( select ) => select.value !== '' )
			.map( ( select ) => ( {
				value: select.value.toLowerCase(),
				type: select.dataset.filterType,
				taxonomy: select.dataset.taxonomy,
			} ) );

		console.log(
			`FAU LIST FILTERS: #${ blockId } Client Filter State:`,
			{ searchTerm, sortValue, activeFilters }
		);

		const allItems = Array.from(
			associatedGrid.querySelectorAll( '.teaser-item' )
		);
		let visibleItems = [];

		allItems.forEach( ( item ) => {
			const title =
				item.querySelector( 'h4' )?.textContent.toLowerCase() || '';
			const excerpt =
				item.querySelector( '.excerpt' )?.textContent.toLowerCase() ||
				'';
			const categories = Array.from(
				item.querySelectorAll( '.category' )
			).map( ( el ) => el.textContent.trim().toLowerCase() );
			const tags = Array.from( item.querySelectorAll( '.tag' ) ).map(
				( el ) => el.textContent.trim().toLowerCase()
			);
			const author = item.dataset.author?.toLowerCase() || '';
			const yearString =
				item.querySelector( 'time' )?.getAttribute( 'datetime' ) || '';
			const year = yearString
				? new Date( yearString ).getFullYear().toString()
				: '';

			const searchMatch =
				! searchTerm ||
				title.includes( searchTerm ) ||
				excerpt.includes( searchTerm );

			const filterMatch = activeFilters.every( ( filter ) => {
				switch ( filter.type ) {
					case 'categories':
						return categories.includes( filter.value );
					case 'tags':
						return tags.includes( filter.value );
					case 'authors':
						return author === filter.value;
					case 'years':
						return year === filter.value;
					case 'taxonomy':
						// Generic taxonomy handler, might need refinement
						return (
							categories.includes( filter.value ) ||
							tags.includes( filter.value )
						);
					default:
						return true;
				}
			} );

			if ( searchMatch && filterMatch ) {
				visibleItems.push( item );
			}
		} );

		console.log(
			`FAU LIST FILTERS: #${ blockId } Matched ${ visibleItems.length } of ${ allItems.length } items.`
		);

		// --- Sorting ---
		const getSortableValue = ( item, type ) => {
			switch ( type ) {
				case 'title':
					return (
						item.querySelector( 'h4' )?.textContent.trim() || ''
					);
				case 'modified':
				case 'date':
				default:
					return (
						item.querySelector( 'time' )?.getAttribute( 'datetime' ) ||
						'0'
					);
			}
		};

		visibleItems.sort( ( a, b ) => {
			const valA = getSortableValue( a, sortValue );
			const valB = getSortableValue( b, sortValue );

			if ( sortValue === 'title' ) {
				return valA.localeCompare( valB );
			}
			// For date and modified, descending order is newest first
			return new Date( valB ) - new Date( valA );
		} );

		// --- Re-append to DOM and handle pagination ---
		// Reorder DOM based on sort order first
		visibleItems.forEach(item => teaserGrid.appendChild(item));
		allItems.filter(item => !visibleItems.includes(item)).forEach(item => teaserGrid.appendChild(item));

		// Now apply visibility based on filtering and pagination
		allItems.forEach((item, index) => {
			const isVisible = visibleItems.includes(item);
			
			if (!isVisible) {
				item.style.display = 'none';
				item.classList.add('filtered-out');
				item.classList.add('js-paginated-hidden');
			} else {
				item.classList.remove('filtered-out');
				const visibleIndex = visibleItems.indexOf(item);
				const isOnCurrentPage = visibleIndex >= (currentPage - 1) * postsPerPage && visibleIndex < currentPage * postsPerPage;

				if (isOnCurrentPage) {
					item.style.display = '';
					item.classList.remove('js-paginated-hidden');
				} else {
					item.style.display = 'none';
					item.classList.add('js-paginated-hidden');
				}
			}
		});

		updateResultsCount(visibleItems.length);
		dispatchFilterUpdateEvent(visibleItems.length);
	}

	function performSearch( isInitial = false, page = 1 ) {
		console.log( `FAU LIST FILTERS: #${ blockId } performSearch (Server-side)`, {
			isInitial,
			page,
		} );

		updateLoadingState( true );
		currentPage = page;
		const allFilterSelects = blockElement.querySelectorAll( '.filter-select' );

		const ajaxData = {
			action: 'fau_filter_teaser_grid',
			nonce: window.fauElemental?.nonce || '',
			search: searchInput ? searchInput.value : '',
			filters: JSON.stringify(
				Array.from( allFilterSelects )
					.filter( ( s ) => s.value )
					.map( ( s ) => ( {
						name: s.dataset.filterName,
						type: s.dataset.filterType,
						taxonomy: s.dataset.taxonomy,
						value: s.value,
					} ) )
			),
			sort: sortSelect ? sortSelect.value : 'date',
			page,
			posts_per_page: postsPerPage,
			post_type: associatedGrid.dataset.variant || 'post',
			category: parseInt( associatedGrid.dataset.category, 10 ) || 0,
		};

		fetch( window.fauElemental?.ajaxUrl || '', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams( ajaxData ),
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				updateLoadingState( false );
				if ( data.success && data.data ) {
					teaserGrid.innerHTML = data.data.posts;
					updateResultsCount( data.data.total_posts );
					dispatchFilterUpdateEvent( data.data.total_posts );
				} else {
					showError();
				}
			} )
			.catch( () => {
				updateLoadingState( false );
				showError();
			} );
	}

	// --- Dynamic Filter Functions ---
	function createDynamicFilterInterface() {
		if ( ! dynamicFiltersContainer ) return;
		if (
			! availableFiltersContainer ||
			Object.keys( availableFilters ).length === 0
		) {
			dynamicFiltersContainer.classList.add(
				'dynamic-filters-container--hidden'
			);
			if ( showMoreButton ) {
				showMoreButton.style.display = 'none';
			}
			return;
		}

		// Initially hide the dynamic filters container
		dynamicFiltersContainer.style.display = 'none';
		
		// Set initial button state
		if ( showMoreButton ) {
			showMoreButton.setAttribute( 'aria-expanded', 'false' );
			const showText = showMoreButton.querySelector( '.show-more-text' );
			const hideText = showMoreButton.querySelector( '.show-less-text' );
			if ( showText && hideText ) {
				showText.style.display = 'inline';
				hideText.style.display = 'none';
			}
		}

		updateAvailableFilterButtons();
	}

	function updateAvailableFilterButtons() {
		if ( ! availableFiltersContainer ) return;
		const buttonsContainer = availableFiltersContainer.querySelector(
			'.filter-buttons-container'
		);
		if ( ! buttonsContainer ) return;

		buttonsContainer.innerHTML = ''; // Clear existing buttons
		const addedFilterKeys = [
			...addedFiltersContainer.querySelectorAll( '.filter-field' ),
		].map( ( el ) => el.dataset.filterKey );

		for ( const [ key, data ] of Object.entries( availableFilters ) ) {
			if ( ! addedFilterKeys.includes( key ) ) {
				const button = document.createElement( 'button' );
				button.type = 'button';
				button.className = 'filter-add-button';
				button.dataset.filterKey = key;
				button.textContent = data.label;
				button.addEventListener( 'click', () => addDynamicFilter( key, data ) );
				buttonsContainer.appendChild( button );
			}
		}

		availableFiltersContainer.style.display =
			buttonsContainer.children.length > 0 ? 'block' : 'none';
	}

	function addDynamicFilter( key, data ) {
		const filterField = document.createElement( 'div' );
		filterField.className = 'filter-field filter-field--dynamic';
		filterField.dataset.filterKey = key;

		const label = document.createElement( 'label' );
		const selectId = `${ blockId }-dynamic-filter-${ key }`;
		label.htmlFor = selectId;
		label.className = 'filter-label';
		label.textContent = data.label;

		const wrapper = document.createElement( 'div' );
		wrapper.className = 'filter-control-wrapper';

		const select = document.createElement( 'select' );
		select.id = selectId;
		select.className = 'filter-select';
		select.dataset.filterName = data.label;
		select.dataset.filterType = key; // e.g., 'years'

		const defaultOption = document.createElement( 'option' );
		defaultOption.value = '';
		defaultOption.textContent = `All ${ data.label }`;
		select.appendChild( defaultOption );

		data.options.forEach( ( opt ) => {
			const option = document.createElement( 'option' );
			option.value = opt.value;
			option.textContent = opt.label;
			select.appendChild( option );
		} );

		select.addEventListener( 'change', handleFilterChange );

		const removeBtn = document.createElement( 'button' );
		removeBtn.type = 'button';
		removeBtn.className = 'filter-remove-button';
		removeBtn.setAttribute( 'aria-label', `Remove ${ data.label } filter` );
		removeBtn.innerHTML = '<span aria-hidden="true">×</span>';
		removeBtn.addEventListener( 'click', () =>
			removeDynamicFilter( filterField )
		);

		wrapper.appendChild( select );
		wrapper.appendChild( removeBtn );
		filterField.appendChild( label );
		filterField.appendChild( wrapper );
		addedFiltersContainer.appendChild( filterField );

		updateAvailableFilterButtons();
	}

	function removeDynamicFilter( filterField ) {
		filterField.remove();
		updateAvailableFilterButtons();
		handleFilterChange(); // Re-run filter after removing one
	}

	// --- UI Update Functions ---
	function updateFilterChips() {
		if ( ! filterChipsContainer || ! activeFiltersContainer ) return;

		filterChipsContainer.innerHTML = '';
		let hasActiveFilter = false;

		if ( searchInput && searchInput.value ) {
			createFilterChip( 'Search', searchInput.value, 'search' );
			hasActiveFilter = true;
		}

		blockElement
			.querySelectorAll( '.filter-select' )
			.forEach( ( select ) => {
				if ( select.value ) {
					const label =
						select.options[ select.selectedIndex ].textContent;
					const type = select.dataset.filterType;
					createFilterChip(
						select.dataset.filterName,
						label,
						type,
						select
					);
					hasActiveFilter = true;
				}
			} );

		activeFiltersContainer.classList.toggle(
			'active-filters--hidden',
			! hasActiveFilter
		);
		clearAllButton.classList.toggle(
			'clear-all-filters--hidden',
			! hasActiveFilter
		);
	}

	function createFilterChip( name, value, type, selectElement = null ) {
		const chip = document.createElement( 'div' );
		chip.className = 'filter-chip';
		chip.dataset.type = type;

		const label = document.createElement( 'span' );
		label.className = 'chip-label';
		label.textContent = `${ name }: ${ value }`;
		chip.appendChild( label );

		const removeBtn = document.createElement( 'button' );
		removeBtn.type = 'button';
		removeBtn.className = 'chip-remove';
		removeBtn.setAttribute( 'aria-label', `Remove ${ name } filter` );
		removeBtn.innerHTML = '<span aria-hidden="true">×</span>';
		removeBtn.addEventListener( 'click', () => {
			if ( type === 'search' ) {
				clearSearch();
			} else if ( selectElement ) {
				if ( selectElement.closest( '.filter-field--dynamic' ) ) {
					removeDynamicFilter(
						selectElement.closest( '.filter-field--dynamic' )
					);
				} else {
					selectElement.value = '';
					handleFilterChange();
				}
			}
		} );

		chip.appendChild( removeBtn );
		filterChipsContainer.appendChild( chip );
	}

	function toggleMoreFilters() {
		if ( ! dynamicFiltersContainer || ! showMoreButton ) return;

		const isExpanded = showMoreButton.getAttribute( 'aria-expanded' ) === 'true';
		const newExpandedState = ! isExpanded;
		
		// Update button state
		showMoreButton.setAttribute( 'aria-expanded', newExpandedState );
		
		// Update container visibility
		dynamicFiltersContainer.style.display = newExpandedState ? 'block' : 'none';

		// Update button text
		const showText = showMoreButton.querySelector( '.show-more-text' );
		const hideText = showMoreButton.querySelector( '.show-less-text' );

		if ( showText && hideText ) {
			showText.style.display = newExpandedState ? 'none' : 'inline';
			hideText.style.display = newExpandedState ? 'inline' : 'none';
		}
	}

	function dispatchFilterUpdateEvent( visibleCount ) {
		const gridId = associatedGrid.dataset.customBlockId;
		if ( ! gridId ) return;

		console.log( `FAU LIST FILTERS: #${ blockId } dispatching 'fau-filter-update' for grid #${ gridId }`, { visibleCount } );
		document.dispatchEvent(
			new CustomEvent( 'fau-filter-update', {
				detail: {
					gridId,
					visibleCount,
					resetToPage1: true,
				},
			} )
		);
	}

	function updateResultsCount( total ) {
		if ( resultsCountElement ) {
			resultsCountElement.textContent = `${ total } results`;
		}
	}

	function updateLoadingState( isLoading ) {
		resultsCountElement.textContent = isLoading ? 'Loading...' : '';
	}

	function showError() {
		resultsCountElement.textContent = 'An error occurred';
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
}
