document.addEventListener( 'DOMContentLoaded', function () {
	const filterBlocks = document.querySelectorAll(
		'.wp-block-fau-elemental-fau-list-filters'
	);
	filterBlocks.forEach( initializeFilterBlock );
} );

function initializeFilterBlock( blockElement ) {
	const blockId = blockElement.getAttribute( 'data-block-id' );

	const associatedGrid = findAssociatedGrid( blockId );
	if ( ! associatedGrid ) {
		return;
	}

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

	let availableFiltersContainer =
		blockElement.querySelector( '.available-filters' );
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

	let currentPage = 1;
	let availableFilters = {};
	const teaserGrid = associatedGrid.querySelector( '.fau-teaser-grid' );
	const isJsPagination = teaserGrid?.dataset.jsPagination === 'true';
	const postsPerPage =
		parseInt( associatedGrid.dataset.postsPerPage, 10 ) || 6;

	if ( showMoreButton ) {
		try {
			availableFilters =
				JSON.parse( showMoreButton.dataset.availableFilters ) || {};
		} catch ( e ) {
			// Do nothing, just prevent crash
		}
	}

	loadInitialData();
	createDynamicFilterInterface();

	if ( searchInput ) {
		searchInput.addEventListener( 'input', debounce( handleSearch, 300 ) );
		searchInput.addEventListener( 'keypress', ( e ) => {
			if ( e.key === 'Enter' ) {
				e.preventDefault();
			}
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

	function loadInitialData() {
		updateFilterChips();
		if ( isJsPagination ) {
			performClientSideFilter();
		} else {
			performSearch( true );
		}
	}

	function handleFilterChange() {
		currentPage = 1;
		updateFilterChips();
		if ( isJsPagination ) {
			performClientSideFilter();
		} else {
			performSearch( false, 1 );
		}
	}

	function handleSearch() {
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
		if ( searchInput ) {
			searchInput.value = '';
		}
		handleSearch();
	}

	function clearAllFilters() {
		if ( searchInput ) {
			searchInput.value = '';
		}
		blockElement
			.querySelectorAll( '.filter-select' )
			.forEach( ( select ) => {
				select.value = '';
			} );

		addedFiltersContainer
			.querySelectorAll( '.filter-field--dynamic' )
			.forEach( ( field ) => {
				field.remove();
			} );

		updateAvailableFilterButtons();
		handleFilterChange();
	}

	function handleSortChange() {
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
			// Do nothing if grid not found
		}
		return grid;
	}

	function performClientSideFilter() {
		const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
		const sortValue = sortSelect ? sortSelect.value : 'date';
		const allFilterSelects =
			blockElement.querySelectorAll( '.filter-select' );

		const activeFilters = Array.from( allFilterSelects )
			.filter( ( select ) => select.value !== '' )
			.map( ( select ) => ( {
				value: select.value.toLowerCase(),
				type: select.dataset.filterType,
				taxonomy: select.dataset.taxonomy,
			} ) );

		const allItems = Array.from(
			associatedGrid.querySelectorAll( '.teaser-item' )
		);
		const visibleItems = [];

		allItems.forEach( ( item ) => {
			const title =
				item
					.querySelector(
						'.teaser-content h2, .teaser-content h3, .teaser-content h4'
					)
					?.textContent.toLowerCase() || '';
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

		const getSortableValue = ( item, type ) => {
			switch ( type ) {
				case 'title':
					return (
						item
							.querySelector(
								'.teaser-content h2, .teaser-content h3, .teaser-content h4'
							)
							?.textContent.trim() || ''
					);
				case 'modified':
				case 'date':
				default:
					return (
						item
							.querySelector( 'time' )
							?.getAttribute( 'datetime' ) || '0'
					);
			}
		};

		visibleItems.sort( ( a, b ) => {
			const valA = getSortableValue( a, sortValue );
			const valB = getSortableValue( b, sortValue );

			if ( sortValue === 'title' ) {
				return valA.localeCompare( valB );
			}
			return new Date( valB ) - new Date( valA );
		} );

		visibleItems.forEach( ( item ) => {
			teaserGrid.appendChild( item );
		} );
		allItems
			.filter( ( item ) => ! visibleItems.includes( item ) )
			.forEach( ( item ) => {
				teaserGrid.appendChild( item );
			} );

		allItems.forEach( ( item ) => {
			const isVisible = visibleItems.includes( item );

			if ( ! isVisible ) {
				item.style.display = 'none';
				item.classList.add( 'filtered-out' );
				item.classList.add( 'js-paginated-hidden' );
			} else {
				item.classList.remove( 'filtered-out' );
				const visibleIndex = visibleItems.indexOf( item );
				const isOnCurrentPage =
					visibleIndex >= ( currentPage - 1 ) * postsPerPage &&
					visibleIndex < currentPage * postsPerPage;

				if ( isOnCurrentPage ) {
					item.style.display = '';
					item.classList.remove( 'js-paginated-hidden' );
				} else {
					item.style.display = 'none';
					item.classList.add( 'js-paginated-hidden' );
				}
			}
		} );

		updateResultsCount( visibleItems.length );
		dispatchFilterUpdateEvent( visibleItems.length );
	}

	function performSearch( page = 1 ) {
		updateLoadingState( true );
		currentPage = page;
		const allFilterSelects =
			blockElement.querySelectorAll( '.filter-select' );

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
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
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

	function createDynamicFilterInterface() {
		if ( ! dynamicFiltersContainer ) {
			return;
		}
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

		dynamicFiltersContainer.style.display = 'none';

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
		if ( ! availableFiltersContainer ) {
			return;
		}
		const buttonsContainer = availableFiltersContainer.querySelector(
			'.filter-buttons-container'
		);
		if ( ! buttonsContainer ) {
			return;
		}

		buttonsContainer.innerHTML = '';
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
				button.addEventListener( 'click', () => {
					addDynamicFilter( key, data );
				} );
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
		select.dataset.filterType = key;

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
		removeBtn.addEventListener( 'click', () => {
			removeDynamicFilter( filterField );
		} );

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
		handleFilterChange();
	}

	function updateFilterChips() {
		if ( ! filterChipsContainer || ! activeFiltersContainer ) {
			return;
		}

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
		if ( ! dynamicFiltersContainer || ! showMoreButton ) {
			return;
		}

		const isExpanded =
			showMoreButton.getAttribute( 'aria-expanded' ) === 'true';
		const newExpandedState = ! isExpanded;

		showMoreButton.setAttribute( 'aria-expanded', newExpandedState );

		dynamicFiltersContainer.style.display = newExpandedState
			? 'block'
			: 'none';

		const showText = showMoreButton.querySelector( '.show-more-text' );
		const hideText = showMoreButton.querySelector( '.show-less-text' );

		if ( showText && hideText ) {
			showText.style.display = newExpandedState ? 'none' : 'inline';
			hideText.style.display = newExpandedState ? 'inline' : 'none';
		}
	}

	function dispatchFilterUpdateEvent( visibleCount ) {
		const gridId = associatedGrid.dataset.customBlockId;
		if ( ! gridId ) {
			return;
		}

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
		if ( resultsCountElement ) {
			resultsCountElement.textContent = isLoading ? 'Loading...' : '';
		}
	}

	function showError() {
		if ( resultsCountElement ) {
			resultsCountElement.textContent = 'An error occurred';
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
}
