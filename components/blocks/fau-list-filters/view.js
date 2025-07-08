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
	const dynamicFiltersContainer = blockElement.querySelector(
		'.dynamic-filters-container'
	);
	const availableFiltersContainer = blockElement.querySelector(
		'.available-filters'
	);
	const addedFiltersContainer = blockElement.querySelector( '.added-filters' );

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
			availableFilters = JSON.parse(
				showMoreButton.dataset.availableFilters || '{}'
			);
		} catch ( e ) {
			console.error( 'FAU LIST FILTERS: Invalid JSON in data-available-filters attribute.', e );
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

	// --- Main Functions ---
	function loadInitialData() {
		console.log( `FAU LIST FILTERS: #${ blockId } loadInitialData` );
		if ( isJsPagination ) {
			performClientSideFilter();
		} else {
			performSearch( true );
		}
	}

	function handleFilterChange() {
		console.log( `FAU LIST FILTERS: #${ blockId } handleFilterChange` );
		currentPage = 1;
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

		console.log( `FAU LIST FILTERS: #${ blockId } Client Filter State:`, {
			searchTerm,
			sortValue,
			activeFilters,
		} );

		const allItems = Array.from( teaserGrid.querySelectorAll( '.teaser-item' ) );
		let matchedItems = allItems.filter( ( item ) => {
			const textMatch =
				! searchTerm ||
				item.textContent.toLowerCase().includes( searchTerm );

			const filterMatch = activeFilters.every( ( filter ) => {
				if ( filter.type === 'taxonomy' ) {
					if ( filter.taxonomy === 'category' ) {
						const catEl = item.querySelector( '.category' );
						return (
							catEl &&
							catEl.textContent.toLowerCase() === filter.value
						);
					}
					// Add other taxonomies like tags here if needed
				}
				if ( filter.type === 'author' ) {
					// Add author check if needed
				}
				return true;
			} );

			return textMatch && filterMatch;
		} );

		// --- SORTING ---
		const getSortableValue = ( item, type ) => {
			if ( type === 'title' ) {
				return item.querySelector( 'h2' )?.textContent || '';
			}
			if ( type === 'date' || type === 'modified' ) {
				const timeEl = item.querySelector( 'time' );
				return timeEl ? new Date( timeEl.getAttribute( 'datetime' ) ) : 0;
			}
			return 0;
		};

		if ( sortValue === 'title' ) {
			matchedItems.sort( ( a, b ) => {
				const valA = getSortableValue( a, 'title' );
				const valB = getSortableValue( b, 'title' );
				return valA.localeCompare( valB );
			} );
		} else if ( sortValue === 'date' || sortValue === 'modified' ) {
			matchedItems.sort( ( a, b ) => {
				const dateA = getSortableValue( a, sortValue );
				const dateB = getSortableValue( b, sortValue );
				return dateB - dateA; // Newest first
			} );
		}

		console.log(
			`FAU LIST FILTERS: #${ blockId } Matched ${ matchedItems.length } of ${ allItems.length } items.`
		);

		// --- DOM UPDATE ---
		// Detach all items first to preserve event listeners
		allItems.forEach( ( item ) => item.remove() );

		// Append sorted and filtered items back to the grid
		matchedItems.forEach( ( item ) => {
			teaserGrid.appendChild( item );
		} );

		// Update grid visibility
		allItems.forEach( ( item ) => {
			const isVisible = matchedItems.includes( item );
			item.style.display = isVisible ? '' : 'none';
			item.classList.toggle( 'filtered-out', ! isVisible );
		} );

		updateResultsCount( matchedItems.length );
		dispatchFilterUpdateEvent( matchedItems.length );
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
		if (
			! availableFiltersContainer ||
			Object.keys( availableFilters ).length === 0
		) {
			dynamicFiltersContainer?.classList.add(
				'dynamic-filters-container--hidden'
			);
			showMoreButton?.classList.add( 'show-more-filters--hidden' );
			return;
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

	function toggleMoreFilters() {
		if ( ! dynamicFiltersContainer || ! showMoreButton ) return;

		const isExpanded = showMoreButton.getAttribute( 'aria-expanded' ) === 'true';
		showMoreButton.setAttribute( 'aria-expanded', ! isExpanded );
		dynamicFiltersContainer.style.display = isExpanded ? 'none' : 'block';

		const showText = showMoreButton.querySelector( '.show-more-text' );
		const hideText = showMoreButton.querySelector( '.show-less-text' );

		if ( showText && hideText ) {
			showText.style.display = isExpanded ? 'inline' : 'none';
			hideText.style.display = isExpanded ? 'none' : 'inline';
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
