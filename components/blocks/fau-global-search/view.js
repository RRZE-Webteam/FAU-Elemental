/**
 * FAU Global Search Frontend JavaScript
 */

document.addEventListener( 'DOMContentLoaded', function () {
	// Find all search forms with advanced features enabled
	const searchForms = document.querySelectorAll(
		'.fau-global-search[data-advanced-features="true"]'
	);

	searchForms.forEach( ( form ) => {
		const input = form.querySelector( '.fau-global-search__input' );

		if ( ! form || ! input ) {
			return;
		}

		// Initialize advanced features
		if ( form.dataset.enableAutocomplete === 'true' ) {
			// Initialize in order: autocomplete first (creates structure), then features that depend on it
			initializeAutocomplete( input, form );
			initializeSearchOptionsMenu( form, input );
			initializeFrequentSearches( input, form );
		}
	} );
} );

/**
 * Get translatable message from hidden elements
 */
function getTranslatableMessage( form, messageType ) {
	const messagesContainer = form.querySelector( '.fau-global-search__hidden-messages' );
	if ( ! messagesContainer ) {
		// Fallback for forms without hidden messages
		const fallbacks = {
			'searching': 'Searching...',
			'no-suggestions': 'No suggestions found',
			'no-results': 'No results found for "%s"',
			'page': 'Page',
			'post': 'Post',
			'frequent-searches': 'Frequent Searches',
			'loading': 'Loading...',
			'no-search-data': 'No search data available yet',
			'loading-options': 'Loading search options...',
			'search-options': 'Search Options',
			'advanced-search': 'Advanced Search'
		};
		return fallbacks[ messageType ] || '';
	}
	
	const messageElement = messagesContainer.querySelector( `.fau-global-search__message-${ messageType }` );
	return messageElement ? messageElement.textContent : '';
}

/**
 * Initialize autocomplete functionality
 */
function initializeAutocomplete( input, form ) {
	// Prevent multiple initializations
	if ( form._autocompleteInitialized ) {
		return;
	}
	form._autocompleteInitialized = true;

	let autocompleteTimeout;
	let suggestionsContainer;

	// Create suggestions container
	function createSuggestionsContainer() {
		// If container already exists and is attached, return it
		if ( suggestionsContainer && suggestionsContainer.parentNode ) {
			return suggestionsContainer;
		}

		// Remove any existing suggestions containers globally to prevent duplicates
		document
			.querySelectorAll( '.fau-global-search__suggestions' )
			.forEach( ( container ) => {
				container.remove();
			} );

		suggestionsContainer = document.createElement( 'div' );
		suggestionsContainer.className = 'fau-global-search__suggestions';
		suggestionsContainer.style.display = 'none';

		// Insert after the radio buttons (scope container)
		const scopeContainer = form.querySelector(
			'.fau-global-search__scope'
		);
		if ( scopeContainer && scopeContainer.parentNode ) {
			scopeContainer.parentNode.insertBefore(
				suggestionsContainer,
				scopeContainer.nextSibling
			);
		} else {
			// Fallback: insert after the input wrapper
			const inputWrapper = form.querySelector(
				'.fau-global-search__input-wrapper'
			);
			if ( inputWrapper && inputWrapper.parentNode ) {
				inputWrapper.parentNode.insertBefore(
					suggestionsContainer,
					inputWrapper.nextSibling
				);
			} else {
				form.parentNode.insertBefore(
					suggestionsContainer,
					form.nextSibling
				);
			}
		}
		return suggestionsContainer;
	}

	// Handle input changes
	input.addEventListener( 'input', function () {
		clearTimeout( autocompleteTimeout );
		const query = this.value.trim();

		// Hide frequent searches when typing starts
		if ( input._hideFrequentSearches ) {
			input._hideFrequentSearches();
		}

		// Hide search options menu when typing
		if ( query.length > 0 && input._hideSearchOptionsMenu ) {
			input._hideSearchOptionsMenu();
		}

		if ( query.length < 3 ) {
			hideSuggestions();
			// Show search options menu again if input becomes empty
			if ( query.length === 0 && input._showSearchOptionsMenu ) {
				input._showSearchOptionsMenu();
			}
			return;
		}

		autocompleteTimeout = setTimeout( () => {
			fetchSuggestions( query );
		}, 300 );
	} );

	// Handle input focus
	input.addEventListener( 'focus', function () {
		const query = this.value.trim();

		// Show frequent searches only if input is empty
		if ( query.length === 0 && input._showFrequentSearches ) {
			input._showFrequentSearches();
		}
		// If there's text and it's 3+ chars, show live suggestions
		else if ( query.length >= 3 ) {
			fetchSuggestions( query );
		}
	} );

	// Fetch suggestions from custom title-only search API
	function fetchSuggestions( query ) {
		// Hide any existing containers first
		hideSuggestions();

		const container = createSuggestionsContainer();

		// Show loading state
		const searchingText = getTranslatableMessage( form, 'searching' );
		container.innerHTML = `
			<div class="fau-global-search__suggestion-item fau-global-search__suggestion-loading">
				<span>${ searchingText }</span>
			</div>
		`;
		container.style.display = 'block';

		// Fetch from custom title-only search endpoint (limited to 5 results)
		const searchUrl =
			'/wp-json/fau/v1/search-suggestions?search=' +
			encodeURIComponent( query );

		fetch( searchUrl )
			.then( ( response ) => response.json() )
			.then( ( results ) => {
				// Double check that this container is still the active one
				if ( container.parentNode ) {
					displaySuggestions( results, query, container );
				}
			} )
			.catch( () => {
				if ( container.parentNode ) {
					const noSuggestionsText = getTranslatableMessage( form, 'no-suggestions' );
					container.innerHTML = `
						<div class="fau-global-search__suggestion-item">
							<span>${ noSuggestionsText }</span>
						</div>
					`;
				}
			} );
	}

	// Display search suggestions
	function displaySuggestions( results, query, container ) {
		if ( ! results || results.length === 0 ) {
			const noResultsText = getTranslatableMessage( form, 'no-results' ).replace( '%s', query );
			container.innerHTML = `
				<div class="fau-global-search__suggestion-item">
					<span>${ noResultsText }</span>
				</div>
			`;
			return;
		}

		// Remove duplicates by title (client-side safeguard)
		const uniqueResults = [];
		const seenTitles = new Set();

		results.forEach( ( result ) => {
			const titleLower = result.title.toLowerCase();
			if ( ! seenTitles.has( titleLower ) ) {
				seenTitles.add( titleLower );
				uniqueResults.push( result );
			}
		} );

		// Limit to 5 results maximum
		const limitedResults = uniqueResults.slice( 0, 5 );

		html = '<div class="fau-global-search__suggestions-list">';

		limitedResults.forEach( ( result ) => {
			// Handle both old format (subtype) and new format (type)
			const pageText = getTranslatableMessage( form, 'page' );
			const postText = getTranslatableMessage( form, 'post' );
			const type =
				result.type || ( result.subtype === 'page' ? pageText : postText );

			// Show site name for network results
			const siteIndicator =
				result.site_name && ! result.is_current_site
					? `<span class="fau-global-search__suggestion-site">${ result.site_name }</span>`
					: '';

			const currentSiteClass = result.is_current_site
				? ' fau-global-search__suggestion-item--current-site'
				: '';

			html += `
				<div class="fau-global-search__suggestion-item${ currentSiteClass }" data-url="${
					result.link || result.url
				}">
					<div class="fau-global-search__suggestion-content">
						<span class="fau-global-search__suggestion-title">${ result.title }</span>
						<div class="fau-global-search__suggestion-meta">
							<span class="fau-global-search__suggestion-type">${ type }</span>
							${ siteIndicator }
						</div>
					</div>
				</div>
			`;
		} );

		html += '</div>';
		container.innerHTML = html;

		// Add click handlers
		container
			.querySelectorAll( '.fau-global-search__suggestion-item' )
			.forEach( ( item ) => {
				item.addEventListener( 'click', function () {
					if ( this.dataset.url ) {
						// Navigate to specific result
						window.location.href = this.dataset.url;
					} else if ( this.dataset.search ) {
						// Submit search form for all results with FAU-wide scope
						input.value = this.dataset.search;

						// Set FAU-wide scope if this is the "view all" option
						if ( this.dataset.fauWide ) {
							const scopeRadio = form.querySelector(
								'input[name="fau_search_scope"][value="fau-wide"]'
							);
							if ( scopeRadio ) {
								scopeRadio.checked = true;
							}
						}

						hideSuggestions();
						form.submit();
					}
				} );
			} );
	}

	// Hide suggestions
	function hideSuggestions() {
		if ( suggestionsContainer ) {
			suggestionsContainer.style.display = 'none';
		}

		// Show search options menu again if input is empty
		if ( input.value.trim().length === 0 && input._showSearchOptionsMenu ) {
			input._showSearchOptionsMenu();
		}
	}

	// Hide suggestions when clicking outside
	document.addEventListener( 'click', function ( event ) {
		const clickedInsideForm = form.contains( event.target );
		const clickedInsideSuggestions =
			suggestionsContainer &&
			suggestionsContainer.contains( event.target );
		const clickedInsideFrequent =
			input._getFrequentContainer &&
			input._getFrequentContainer() &&
			input._getFrequentContainer().contains( event.target );

		if (
			! clickedInsideForm &&
			! clickedInsideSuggestions &&
			! clickedInsideFrequent
		) {
			hideSuggestions();
			if ( input._hideFrequentSearches ) {
				input._hideFrequentSearches();
			}
		}
	} );

	// Hide suggestions on form submit
	form.addEventListener( 'submit', function () {
		hideSuggestions();
		if ( input._hideFrequentSearches ) {
			input._hideFrequentSearches();
		}
	} );
}

/**
 * Initialize frequent searches functionality
 */
function initializeFrequentSearches( input, form ) {
	// Prevent multiple initializations
	if ( form._frequentSearchesInitialized ) {
		return;
	}
	form._frequentSearchesInitialized = true;

	let frequentContainer;

	// Create frequent searches container
	function createFrequentContainer() {
		if ( frequentContainer ) {
			return frequentContainer;
		}

		frequentContainer = document.createElement( 'div' );
		frequentContainer.className = 'fau-global-search__frequent';
		frequentContainer.style.display = 'none';

		// Insert after the radio buttons (scope container)
		const scopeContainer = form.querySelector(
			'.fau-global-search__scope'
		);
		if ( scopeContainer && scopeContainer.parentNode ) {
			scopeContainer.parentNode.insertBefore(
				frequentContainer,
				scopeContainer.nextSibling
			);
		} else {
			// Fallback: insert after the input wrapper
			const inputWrapper = form.querySelector(
				'.fau-global-search__input-wrapper'
			);
			if ( inputWrapper && inputWrapper.parentNode ) {
				inputWrapper.parentNode.insertBefore(
					frequentContainer,
					inputWrapper.nextSibling
				);
			} else {
				form.parentNode.insertBefore(
					frequentContainer,
					form.nextSibling
				);
			}
		}
		return frequentContainer;
	}

	// Show frequent searches
	function showFrequentSearches() {
		// Hide search options menu when showing frequent searches
		hideSearchOptionsMenu();

		const container = createFrequentContainer();

		// Show loading state
		const frequentSearchesText = getTranslatableMessage( form, 'frequent-searches' );
		const loadingText = getTranslatableMessage( form, 'loading' );
		container.innerHTML = `
			<div class="fau-global-search__frequent-header">${ frequentSearchesText }</div>
			<div class="fau-global-search__frequent-list">
				<div class="fau-global-search__frequent-item">
					<span>${ loadingText }</span>
				</div>
			</div>
		`;
		container.style.display = 'block';

		// Fetch real frequent searches from WordPress
		fetchFrequentSearches( container );
	}

	// Fetch frequent searches from WordPress analytics
	function fetchFrequentSearches( container ) {
		const ajaxUrl = window.fauElemental?.ajaxUrl || '/wp-admin/admin-ajax.php';
		const formData = new FormData();
		formData.append( 'action', 'get_frequent_searches' );
		formData.append( 'nonce', window.fauElemental?.nonce || '' );

		fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				if (
					data.success &&
					data.data.searches &&
					data.data.searches.length > 0
				) {
					displayFrequentSearches( data.data.searches, container );
				} else {
					// No search data available yet - hide the container
					const frequentSearchesText = getTranslatableMessage( form, 'frequent-searches' );
					const noSearchDataText = getTranslatableMessage( form, 'no-search-data' );
					container.innerHTML = `
					<div class="fau-global-search__frequent-header">${ frequentSearchesText }</div>
					<div class="fau-global-search__frequent-list">
						<div>
							${ noSearchDataText }
						</div>
					</div>
				`;
				}
			} )
			.catch( () => {
				// Hide container on error
				container.style.display = 'none';
			} );
	}

	// Display frequent searches with click handlers
	function displayFrequentSearches( searches, container ) {
		const frequentSearchesText = getTranslatableMessage( form, 'frequent-searches' );
		let html =
			`<div class="fau-global-search__frequent-header">${ frequentSearchesText }</div>`;
		html += '<div class="fau-global-search__frequent-list">';

		searches.forEach( ( query ) => {
			html += `
				<div class="fau-global-search__frequent-item" data-query="${ query }">
					<span>${ query }</span>
				</div>
			`;
		} );

		html += '</div>';
		container.innerHTML = html;

		// Add click handlers
		container
			.querySelectorAll( '.fau-global-search__frequent-item' )
			.forEach( ( item ) => {
				item.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					const query = this.dataset.query;
					input.value = query;
					hideFrequentSearches();

					// Focus the input first
					input.focus();

					// Trigger live suggestions for the selected query
					if ( query.length >= 3 ) {
						// Find the autocomplete functionality and trigger it
						input.dispatchEvent(
							new Event( 'input', { bubbles: true } )
						);
					} else {
						// If query is short, just submit
						form.submit();
					}
				} );
			} );
	}

	// Hide frequent searches
	function hideFrequentSearches() {
		if ( frequentContainer ) {
			frequentContainer.style.display = 'none';
		}

		// Show search options menu again when hiding frequent searches (if input is empty)
		if ( input.value.trim().length === 0 ) {
			showSearchOptionsMenu();
		}
	}

	// Expose functions to the autocomplete module
	input._showFrequentSearches = showFrequentSearches;
	input._hideFrequentSearches = hideFrequentSearches;
	input._getFrequentContainer = () => frequentContainer;

	// Get search options menu functions from the form
	function hideSearchOptionsMenu() {
		if ( input._hideSearchOptionsMenu ) {
			input._hideSearchOptionsMenu();
		}
	}

	function showSearchOptionsMenu() {
		if ( input._showSearchOptionsMenu ) {
			input._showSearchOptionsMenu();
		}
	}
}

/**
 * Initialize search options menu (loads when block appears)
 * Fetches and displays the menu assigned to "search_options_menu" theme location
 */
function initializeSearchOptionsMenu( form, input ) {
	// Prevent multiple initializations
	if ( form._searchOptionsMenuInitialized ) {
		return;
	}
	form._searchOptionsMenuInitialized = true;

	// Check if menu container already exists to prevent duplication
	let menuContainer = form.querySelector(
		'.fau-global-search__options-menu'
	);
	if ( menuContainer ) {
		return;
	}

	menuContainer = document.createElement( 'div' );
	menuContainer.className = 'fau-global-search__options-menu';

	// Insert the menu container after the radio buttons (scope options)
	const scopeContainer = form.querySelector( '.fau-global-search__scope' );
	if ( scopeContainer && scopeContainer.parentNode ) {
		scopeContainer.parentNode.insertBefore(
			menuContainer,
			scopeContainer.nextSibling
		);
	} else {
		// Fallback: insert after the input wrapper
		const inputWrapper = form.querySelector(
			'.fau-global-search__input-wrapper'
		);
		if ( inputWrapper && inputWrapper.parentNode ) {
			inputWrapper.parentNode.insertBefore(
				menuContainer,
				inputWrapper.nextSibling
			);
		} else {
			form.appendChild( menuContainer );
		}
	}

	// Fetch the search options menu
	fetchSearchOptionsMenu( menuContainer, form );

	// Expose show/hide functions for the frequent searches module
	input._showSearchOptionsMenu = function () {
		if ( menuContainer ) {
			menuContainer.style.display = 'block';
		}
	};

	input._hideSearchOptionsMenu = function () {
		if ( menuContainer ) {
			menuContainer.style.display = 'none';
		}
	};
}

/**
 * Fetch search options menu from WordPress
 */
function fetchSearchOptionsMenu( container, form ) {
	// Show loading state
	const loadingOptionsText = getTranslatableMessage( form, 'loading-options' );
	container.innerHTML = `
		<div class="fau-global-search__menu-loading">
			<span>${ loadingOptionsText }</span>
		</div>
	`;

	// Create a simple REST API endpoint call to get menu items
	// Since WordPress doesn't have a direct menu REST endpoint, we'll use a custom approach
	const menuUrl =
		'/wp-json/wp/v2/menu-items?menus=search_options_menu&per_page=100';

	fetch( menuUrl )
		.then( ( response ) => {
			if ( ! response.ok ) {
				// If REST API doesn't work, try an alternative approach
				return fetchSearchOptionsMenuFallback( container, form );
			}
			return response.json();
		} )
		.then( ( menuItems ) => {
			if ( Array.isArray( menuItems ) && menuItems.length > 0 ) {
				displaySearchOptionsMenu( menuItems, container, form );
			} else {
				// Try fallback approach
				fetchSearchOptionsMenuFallback( container, form );
			}
		} )
		.catch( () => {
			// Fallback approach
			fetchSearchOptionsMenuFallback( container, form );
		} );
}

/**
 * Fallback method to fetch menu using a simpler approach
 */
function fetchSearchOptionsMenuFallback( container, form ) {
	// Try to get menu via admin-ajax
	const ajaxUrl = window.fauElemental?.ajaxUrl || '/wp-admin/admin-ajax.php';
	const formData = new FormData();
	formData.append( 'action', 'get_search_options_menu' );
	formData.append( 'nonce', window.fauElemental?.nonce || '' );

	fetch( ajaxUrl, {
		method: 'POST',
		body: formData,
	} )
		.then( ( response ) => response.json() )
		.then( ( data ) => {
			if ( data.success && data.data.menu_html ) {
				container.innerHTML = data.data.menu_html;
				addMenuClickHandlers( container );
			} else {
				// No menu found or error - hide the container
				container.style.display = 'none';
			}
		} )
		.catch( () => {
			// If all fails, show some default options or hide
			const searchOptionsText = getTranslatableMessage( form, 'search-options' );
			const advancedSearchText = getTranslatableMessage( form, 'advanced-search' );
			container.innerHTML = `
				<div class="fau-global-search__menu-header">${ searchOptionsText }</div>
				<div class="fau-global-search__menu-item">
					<a href="/search/">${ advancedSearchText }</a>
				</div>
			`;
			addMenuClickHandlers( container );
		} );
}

/**
 * Display search options menu items
 */
function displaySearchOptionsMenu( menuItems, container, form ) {
	if ( ! menuItems || menuItems.length === 0 ) {
		container.style.display = 'none';
		return;
	}

	const searchOptionsText = getTranslatableMessage( form, 'search-options' );
	let html =
		`<div class="fau-global-search__menu-header">${ searchOptionsText }</div>`;
	html += '<div class="fau-global-search__menu-list">';

	// Build menu structure (handle parent/child relationships)
	const topLevelItems = menuItems.filter(
		( item ) => ! item.menu_item_parent || item.menu_item_parent === '0'
	);

	topLevelItems.forEach( ( item ) => {
		const children = menuItems.filter(
			( child ) => child.menu_item_parent === item.ID
		);
		const hasChildren = children.length > 0;

		html += `
			<div class="fau-global-search__menu-item${
				hasChildren ? ' has-children' : ''
			}" data-url="${ item.url }">
				<span class="fau-global-search__menu-title">${ item.title }</span>
				${
					item.description
						? `<span class="fau-global-search__menu-description">${ item.description }</span>`
						: ''
				}
			</div>
		`;

		// Add children if any
		if ( hasChildren ) {
			html += '<div class="fau-global-search__menu-submenu">';
			children.forEach( ( child ) => {
				html += `
					<div class="fau-global-search__menu-item fau-global-search__menu-item--child" data-url="${
						child.url
					}">
						<span class="fau-global-search__menu-title">${ child.title }</span>
						${
							child.description
								? `<span class="fau-global-search__menu-description">${ child.description }</span>`
								: ''
						}
					</div>
				`;
			} );
			html += '</div>';
		}
	} );

	html += '</div>';
	container.innerHTML = html;

	addMenuClickHandlers( container );
}

/**
 * Add click handlers to menu items
 */
function addMenuClickHandlers( container ) {
	container
		.querySelectorAll( '.fau-global-search__menu-item[data-url]' )
		.forEach( ( item ) => {
			item.addEventListener( 'click', function () {
				const url = this.dataset.url;
				if ( url && url !== '#' ) {
					window.location.href = url;
				}
			} );
		} );
}
