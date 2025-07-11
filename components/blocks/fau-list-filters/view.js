import { __, sprintf } from '@wordpress/i18n';

document.addEventListener( 'DOMContentLoaded', function () {
	const filterBlocks = document.querySelectorAll(
		'.wp-block-fau-elemental-fau-list-filters'
	);
	filterBlocks.forEach( ( block ) => {
		initializeFilterBlock( block );
	} );
} );

function initializeFilterBlock( blockElement ) {
	const blockId = blockElement.getAttribute( 'data-block-id' );
	const associatedGrid = findAssociatedGrid( blockId );

	if ( ! associatedGrid ) {
		const resultsContainer = blockElement.querySelector(
			'.fau-list-filters__sort-section'
		);
		if ( resultsContainer ) {
			resultsContainer.innerHTML = `<p>${ __(
				'No content to filter',
				'fau-elemental'
			) }</p>`;
		}
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
		availableFiltersContainer.innerHTML = `<h4>${ __(
			'Add filters:',
			'fau-elemental'
		) }</h4><div class="filter-buttons-container"></div>`;
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
	const postsPerPage =
		parseInt( associatedGrid.dataset.postsPerPage, 10 ) || 6;

	if ( showMoreButton ) {
		try {
			availableFilters =
				JSON.parse( showMoreButton.dataset.availableFilters ) || {};
		} catch ( e ) {
			// Prevent crash on malformed JSON
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

	// Add view switching functionality
	const viewButtons = blockElement.querySelectorAll( '.view-button' );
	viewButtons.forEach( ( button ) => {
		button.addEventListener( 'click', handleViewChange );
	} );

	// Listen for pagination changes from associated pagination blocks
	const gridId = associatedGrid.dataset.customBlockId;
	if ( gridId ) {
		document.addEventListener( 'fau-pagination-change', function ( e ) {
			if ( e.detail.gridId === gridId ) {
				performSearch( e.detail.page );
			}
		} );

		// Attach click handlers to existing pagination immediately
		const paginationBlocks = document.querySelectorAll(
			`.wp-block-fau-elemental-fau-pagination[data-grid-block-id="${ gridId }"]`
		);

		paginationBlocks.forEach( ( paginationBlock ) => {
			let paginationContainer =
				paginationBlock.querySelector( '.pagination' );
			if (
				! paginationContainer &&
				paginationBlock.classList.contains( 'pagination' )
			) {
				paginationContainer = paginationBlock;
			}

			if ( paginationContainer ) {
				attachPaginationClickHandlers( paginationContainer );
			}
		} );
	}

	function handleViewChange( event ) {
		event.preventDefault();
		event.stopPropagation();

		const clickedButton = event.target.closest( '.view-button' );

		if ( ! clickedButton ) {
			return;
		}

		const newView = clickedButton.dataset.view;

		if ( ! newView || ! associatedGrid ) {
			return;
		}

		// Update button states
		viewButtons.forEach( ( button ) => {
			const isActive = button.dataset.view === newView;
			button.classList.toggle( 'active', isActive );
			button.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
		} );

		// Update grid view classes
		const gridContainer =
			associatedGrid.querySelector( '.fau-teaser-grid' );
		if ( gridContainer ) {
			gridContainer.classList.remove(
				'view-cards',
				'view-table',
				'view-list'
			);
			gridContainer.classList.add( `view-${ newView }` );

			// Add is-table-view class to all article elements when table view is selected
			const articles = gridContainer.querySelectorAll( 'article' );
			articles.forEach( ( article ) => {
				if ( newView === 'table' ) {
					article.classList.add( 'is-table-view' );
				} else {
					article.classList.remove( 'is-table-view' );
				}
			} );
		}
	}

	function loadInitialData() {
		updateFilterChips();

		// Read current page from pagination if available
		let initialPage = 1;
		const currentGridId = associatedGrid.dataset.customBlockId;
		if ( currentGridId ) {
			const paginationBlocks = document.querySelectorAll(
				`.wp-block-fau-elemental-fau-pagination[data-grid-block-id="${ currentGridId }"]`
			);

			if ( paginationBlocks.length > 0 ) {
				const paginationBlock = paginationBlocks[ 0 ];
				let paginationContainer =
					paginationBlock.querySelector( '.pagination' );
				if (
					! paginationContainer &&
					paginationBlock.classList.contains( 'pagination' )
				) {
					paginationContainer = paginationBlock;
				}

				if ( paginationContainer ) {
					const currentPageAttr =
						paginationContainer.getAttribute( 'data-current-page' );
					if ( currentPageAttr ) {
						initialPage = parseInt( currentPageAttr ) || 1;
					}
				}
			}
		}

		currentPage = initialPage;
		performSearch( initialPage );
	}

	function handleFilterChange() {
		currentPage = 1;
		updateFilterChips();
		performSearch( 1 );
	}

	function handleSearch() {
		currentPage = 1;
		updateSearchClearButton();
		updateFilterChips();
		performSearch( 1 );
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
		performSearch( currentPage );
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
		const selector = `.wp-block-fau-elemental-fau-teaser-grid[data-filter-block-id="${ filterId }"]`;
		return document.querySelector( selector );
	}

	function updatePaginationFromResponse( responseData ) {
		const responseGridId = associatedGrid.dataset.customBlockId;

		if ( ! responseGridId ) {
			return;
		}

		const paginationBlocks = document.querySelectorAll(
			`.wp-block-fau-elemental-fau-pagination[data-grid-block-id="${ responseGridId }"]`
		);

		paginationBlocks.forEach( ( paginationBlock ) => {
			let paginationContainer =
				paginationBlock.querySelector( '.pagination' );
			if (
				! paginationContainer &&
				paginationBlock.classList.contains( 'pagination' )
			) {
				paginationContainer = paginationBlock;
			}

			if ( ! paginationContainer ) {
				return;
			}

			// Update pagination attributes
			paginationContainer.setAttribute(
				'data-current-page',
				responseData.current_page || 1
			);
			paginationContainer.setAttribute(
				'data-total-pages',
				responseData.max_num_pages || 1
			);

			// If the pagination system has an update function, call it
			if (
				typeof window.fauPagination?.updatePaginationState ===
				'function'
			) {
				window.fauPagination.updatePaginationState(
					paginationContainer,
					responseData.current_page
				);
				attachPaginationClickHandlers( paginationContainer );
			} else {
				updatePaginationDisplay(
					paginationContainer,
					responseData.current_page,
					responseData.max_num_pages
				);
			}
		} );
	}

	function updatePaginationDisplay(
		paginationContainer,
		displayCurrentPage,
		totalPages
	) {
		paginationContainer.setAttribute(
			'data-current-page',
			displayCurrentPage
		);
		paginationContainer.setAttribute( 'data-total-pages', totalPages );

		const pageNumbersContainer =
			paginationContainer.querySelector( '.page-numbers' );
		if ( pageNumbersContainer ) {
			pageNumbersContainer.innerHTML = generatePaginationHTML(
				displayCurrentPage,
				totalPages
			);
		}

		// Update prev/next button states
		const prevButton =
			paginationContainer.querySelector( '.page-nav.prev' );
		const nextButton =
			paginationContainer.querySelector( '.page-nav.next' );

		if ( prevButton ) {
			if ( displayCurrentPage <= 1 ) {
				prevButton.classList.add( 'disabled' );
				prevButton.setAttribute( 'aria-disabled', 'true' );
			} else {
				prevButton.classList.remove( 'disabled' );
				prevButton.removeAttribute( 'aria-disabled' );
			}
		}

		if ( nextButton ) {
			if ( displayCurrentPage >= totalPages ) {
				nextButton.classList.add( 'disabled' );
				nextButton.setAttribute( 'aria-disabled', 'true' );
			} else {
				nextButton.classList.remove( 'disabled' );
				nextButton.removeAttribute( 'aria-disabled' );
			}
		}

		paginationContainer.dispatchEvent( new Event( 'pagination-updated' ) );
		attachPaginationClickHandlers( paginationContainer );
	}

	function attachPaginationClickHandlers( paginationContainer ) {
		// Remove existing handlers first
		const existingHandlers = paginationContainer.querySelectorAll(
			'[data-filter-pagination-handler]'
		);
		existingHandlers.forEach( ( el ) => {
			el.removeAttribute( 'data-filter-pagination-handler' );
		} );

		const paginationControls = paginationContainer.querySelectorAll(
			'.page-nav, .page-numbers a, .page-numbers span.page-number, .page-numbers button, a.page-number'
		);

		paginationControls.forEach( ( control ) => {
			if (
				control.classList.contains( 'disabled' ) ||
				control.classList.contains( 'page-ellipsis' )
			) {
				return;
			}

			control.setAttribute( 'data-filter-pagination-handler', 'true' );

			control.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				e.stopPropagation();

				let targetPage = 1;
				const clickCurrentPage =
					parseInt(
						paginationContainer.getAttribute( 'data-current-page' )
					) || 1;
				const clickTotalPages =
					parseInt(
						paginationContainer.getAttribute( 'data-total-pages' )
					) || 1;

				if ( control.classList.contains( 'prev' ) ) {
					targetPage = Math.max( 1, clickCurrentPage - 1 );
				} else if ( control.classList.contains( 'next' ) ) {
					targetPage = Math.min(
						clickTotalPages,
						clickCurrentPage + 1
					);
				} else {
					const dataPage = control.getAttribute( 'data-page' );
					if ( dataPage ) {
						targetPage = parseInt( dataPage );
					} else {
						const pageText = control.textContent.trim();
						const pageNum = parseInt( pageText );
						if ( ! isNaN( pageNum ) ) {
							targetPage = pageNum;
						}
					}
				}

				if ( targetPage === clickCurrentPage ) {
					return;
				}

				performSearch( targetPage );
			} );
		} );
	}

	function generatePaginationHTML( displayCurrentPage, totalPages ) {
		let html = '';

		if ( totalPages <= 1 ) {
			return html;
		}

		if ( totalPages <= 6 ) {
			for ( let i = 1; i <= totalPages; i++ ) {
				if ( i === displayCurrentPage ) {
					html += `<span class="page-number current" aria-current="page">${ i }</span>`;
				} else {
					const goToPageLabel = sprintf(
						/* translators: %s: page number */
						__( 'Go to page %s', 'fau-elemental' ),
						i
					);
					html += `<a href="#" class="page-number" aria-label="${ goToPageLabel }" data-page="${ i }">${ i }</a>`;
				}
			}
		} else {
			for ( let i = 1; i <= totalPages; i++ ) {
				if ( i === displayCurrentPage ) {
					html += `<span class="page-number current" aria-current="page">${ i }</span>`;
				} else {
					const goToPageLabel = sprintf(
						/* translators: %s: page number */
						__( 'Go to page %s', 'fau-elemental' ),
						i
					);
					html += `<a href="#" class="page-number" aria-label="${ goToPageLabel }" data-page="${ i }">${ i }</a>`;
				}
			}
		}

		return html;
	}

	function performSearch( page = 1 ) {
		updateLoadingState( true );
		currentPage = page;
		const allFilterSelects =
			blockElement.querySelectorAll( '.filter-select' );

		const filters = Array.from( allFilterSelects )
			.filter( ( s ) => s.value )
			.map( ( s ) => ( {
				name: s.dataset.filterName,
				type: s.dataset.filterType,
				taxonomy: s.dataset.taxonomy,
				value: s.value,
			} ) );

		const filtersJson = JSON.stringify( filters );

		const ajaxData = {
			action: 'fau_teaser_grid_filter',
			nonce: window.fauListFilters?.nonce || '',
			search: searchInput ? searchInput.value : '',
			filters: filtersJson,
			sort: sortSelect ? sortSelect.value : 'date',
			sort_order: 'DESC',
			page,
			posts_per_page: postsPerPage,
			variant: associatedGrid.dataset.variant || 'post',
			category: parseInt( associatedGrid.dataset.category, 10 ) || 0,
			display_style: associatedGrid.dataset.displayStyle || 'teaser-grid',
			teaser_layout: associatedGrid.dataset.teaserLayout || '3m',
			heading_level: associatedGrid.dataset.headingLevel || 'h4',
		};

		const urlParams = new URLSearchParams();
		for ( const key in ajaxData ) {
			urlParams.append( key, ajaxData[ key ] );
		}

		fetch( window.fauListFilters?.ajaxUrl || '', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: urlParams,
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				updateLoadingState( false );
				if ( data.success && data.data ) {
					if ( teaserGrid ) {
						teaserGrid.innerHTML = data.data.posts;
					}
					updateResultsCount( data.data.total_posts );
					updatePaginationFromResponse( data.data );
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
		if ( ! availableFiltersContainer.querySelector( 'h4' ) ) {
			const heading = document.createElement( 'h4' );
			heading.textContent = __( 'Add filters:', 'fau-elemental' );
			buttonsContainer.before( heading );
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
		defaultOption.textContent = sprintf(
			/* translators: %s: filter label (e.g., "Categories", "Tags") */
			__( 'All %s', 'fau-elemental' ),
			data.label
		);
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
		removeBtn.setAttribute(
			'aria-label',
			sprintf(
				/* translators: %s: filter label (e.g., "Categories", "Tags") */
				__( 'Remove %s filter', 'fau-elemental' ),
				data.label
			)
		);
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
			createFilterChip(
				__( 'Search', 'fau-elemental' ),
				searchInput.value,
				'search'
			);
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
		removeBtn.setAttribute(
			'aria-label',
			sprintf(
				/* translators: %s: filter name (e.g., "Search", "Categories") */
				__( 'Remove %s filter', 'fau-elemental' ),
				name
			)
		);
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
		const eventGridId = associatedGrid.dataset.customBlockId;
		if ( ! eventGridId ) {
			return;
		}

		document.dispatchEvent(
			new CustomEvent( 'fau-filter-update', {
				detail: {
					gridId: eventGridId,
					visibleCount,
					resetToPage1: true,
				},
			} )
		);
	}

	function updateResultsCount( total ) {
		if ( resultsCountElement ) {
			if ( total === 0 ) {
				resultsCountElement.textContent = __(
					'No results found',
					'fau-elemental'
				);
			} else {
				resultsCountElement.textContent = sprintf(
					/* translators: %s: number of results found */
					__( 'Total results: %s', 'fau-elemental' ),
					total
				);
			}
		}
	}

	function updateLoadingState( isLoading ) {
		if ( resultsCountElement ) {
			resultsCountElement.textContent = isLoading
				? __( 'Loading results…', 'fau-elemental' )
				: '';
		}
	}

	function showError() {
		if ( resultsCountElement ) {
			resultsCountElement.textContent = __(
				'An error occurred',
				'fau-elemental'
			);
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
