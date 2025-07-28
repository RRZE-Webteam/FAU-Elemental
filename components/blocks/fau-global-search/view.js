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

		// Check if form is inside menu modal
		const isInMenuModal = form.closest( '.menu-modal__content' ) !== null;

		// Initialize advanced features
		if ( form.dataset.enableAutocomplete === 'true' ) {
			// Initialize in order: autocomplete first (creates structure), then features that depend on it
			initializeAutocomplete( input, form, isInMenuModal );
			// Only initialize search options menu if NOT in dropdown context
			if ( isInMenuModal ) {
				initializeSearchOptionsMenu( form, input );
			}
		}
	} );
} );

/**
 * Position dropdown relative to input
 */
function positionDropdown( container, inputElement ) {
	const inputRect = inputElement.getBoundingClientRect();
	const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
	const scrollLeft =
		window.pageXOffset || document.documentElement.scrollLeft;

	container.style.position = 'absolute';
	container.style.top = inputRect.bottom + scrollTop + 'px';
	container.style.left = inputRect.left + scrollLeft + 'px';
	container.style.width = inputRect.width + 'px';
	container.style.zIndex = '9999';
}

/**
 * Get translatable message from hidden elements
 */
function getTranslatableMessage( form, messageType ) {
	const messagesContainer = form.querySelector(
		'.fau-global-search__hidden-messages'
	);
	if ( ! messagesContainer ) {
		// Fallback for forms without hidden messages
		const fallbacks = {
			searching: 'Searching...',
			'no-suggestions': 'No suggestions found',
			'no-results': 'No results found for "%s"',
			page: 'Page',
			post: 'Post',
			'frequent-searches': 'Frequent Searches',
			loading: 'Loading...',
			'no-search-data': 'No search data available yet',
			'loading-options': 'Loading search options...',
			'search-options': 'Search Options',
			'advanced-search': 'Advanced Search',
			'search-suggestions': 'Search suggestions',
			'rate-limit-exceeded':
				'Too many search requests. Please wait a moment and try again.',
			'invalid-search-term': 'Please enter a valid search term.',
		};
		return fallbacks[ messageType ] || '';
	}

	const messageElement = messagesContainer.querySelector(
		`.fau-global-search__message-${ messageType }`
	);
	return messageElement ? messageElement.textContent : '';
}

/**
 * Initialize autocomplete functionality
 */
function initializeAutocomplete( input, form, isInMenuModal ) {
	// Prevent multiple initializations
	if ( form._autocompleteInitialized ) {
		return;
	}
	form._autocompleteInitialized = true;

	let autocompleteTimeout;
	let suggestionsContainer;
	let selectedIndex = -1;

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

		if ( isInMenuModal ) {
			// Original behavior: insert after scope container
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
		} else {
			// Dropdown behavior: position as overlay
			suggestionsContainer.classList.add(
				'fau-global-search__suggestions--dropdown'
			);
			document.body.appendChild( suggestionsContainer );

			// Position the dropdown
			positionDropdown( suggestionsContainer, input );

			// Reposition on window resize/scroll
			const repositionHandler = () =>
				positionDropdown( suggestionsContainer, input );
			window.addEventListener( 'resize', repositionHandler );
			window.addEventListener( 'scroll', repositionHandler );

			// Store handlers for cleanup
			suggestionsContainer._repositionHandlers = repositionHandler;
		}

		return suggestionsContainer;
	}

	// Handle input changes with configurable debouncing
	input.addEventListener( 'input', function () {
		clearTimeout( autocompleteTimeout );
		const query = this.value.trim();

		// Hide search options menu when typing
		if ( query.length > 0 && input._hideSearchOptionsMenu ) {
			input._hideSearchOptionsMenu();
		}

		// Only hide suggestions if input is completely cleared
		if ( query.length === 0 ) {
			hideSuggestions();

			// Show search options menu again if input becomes empty
			if ( input._showSearchOptionsMenu ) {
				input._showSearchOptionsMenu();
			}
			return;
		}

		// For queries less than 3 characters, don't fetch but keep existing suggestions visible
		if ( query.length < 3 ) {
			return;
		}

		// Get debounce delay from config or use default
		const debounceDelay = window.fauElemental?.searchDebounceDelay || 300;

		autocompleteTimeout = setTimeout( () => {
			fetchSuggestions( query );
		}, debounceDelay );
	} );

	// Handle input focus - only show search options menu, don't trigger search
	input.addEventListener( 'focus', function () {
		const query = this.value.trim();

		// Only show search options menu if input is empty
		if ( query.length === 0 && input._showSearchOptionsMenu ) {
			input._showSearchOptionsMenu();
		}
		// Don't trigger search on focus - let debouncing handle it when user types
	} );

	// Fetch suggestions from custom title-only search API
	function fetchSuggestions( query ) {
		// Reset selection
		selectedIndex = -1;

		// Hide any existing containers first
		hideSuggestions();

		const container = createSuggestionsContainer();

		// Show loading state
		const searchingText = getTranslatableMessage( form, 'searching' );
		container.innerHTML = `
			<ul class="fau-global-search__suggestions-list" role="listbox" aria-label="${getTranslatableMessage(form, 'search-suggestions')}">
				<li class="fau-global-search__suggestion-item fau-global-search__suggestion-loading" role="option">
					${ searchingText }
				</li>
			</ul>
		`;
		container.style.display = 'block';

		// Fetch from custom title-only search endpoint (limited to 5 results)
		const searchUrl =
			'/wp-json/fau/v1/search-suggestions?search=' +
			encodeURIComponent( query );

		fetch( searchUrl )
			.then( ( response ) => {
				// Check if the response is ok (status 200-299)
				if ( ! response.ok ) {
					// Handle specific error status codes
					if ( response.status === 429 ) {
						// Rate limit exceeded
						throw new Error( 'rate_limit_exceeded' );
					} else if ( response.status === 400 ) {
						// Bad request (invalid search term)
						throw new Error( 'invalid_search_term' );
					} else {
						// Other server errors
						throw new Error( 'server_error' );
					}
				}
				return response.json();
			} )
			.then( ( results ) => {
				// Double check that this container is still the active one
				if ( container.parentNode ) {
					displaySuggestions( results, query, container );
				}
			} )
			.catch( ( error ) => {
				if ( container.parentNode ) {
					let errorMessage;

					if ( error.message === 'rate_limit_exceeded' ) {
						errorMessage = getTranslatableMessage( form, 'rate-limit-exceeded' );
					} else if ( error.message === 'invalid_search_term' ) {
						errorMessage = getTranslatableMessage( form, 'invalid-search-term' );
					} else {
						// Generic error or network issue
						errorMessage = getTranslatableMessage( form, 'no-suggestions' );
					}

					container.innerHTML = `
						<ul class="fau-global-search__suggestions-list" role="listbox" aria-label="${getTranslatableMessage(form, 'search-suggestions')}">
							<li class="fau-global-search__suggestion-error" role="option">
								${ errorMessage }
							</li>
						</ul>
					`;
				}
			} );
	}

	// Highlight matching text in title
	function highlightMatchingText( title, query ) {
		if ( ! query || query.length === 0 ) {
			return title;
		}

		// Escape special regex characters in the query
		const escapedQuery = query.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );

		// Create regex for case-insensitive matching
		const regex = new RegExp( `(${ escapedQuery })`, 'gi' );

		// Replace matches with highlighted version
		return title.replace( regex, '<b>$1</b>' );
	}

	// Display search suggestions
	function displaySuggestions( results, query, container ) {
		if ( ! results || results.length === 0 ) {
			const noResultsText = getTranslatableMessage(
				form,
				'no-results'
			).replace( '%s', query );
			container.innerHTML = `
				<ul class="fau-global-search__suggestions-list" role="listbox" aria-label="${getTranslatableMessage(form, 'search-suggestions')}">
					<li class="fau-global-search__suggestion-error" role="option">
						${ noResultsText }
					</li>
				</ul>
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

		let html = `<ul class="fau-global-search__suggestions-list" role="listbox" aria-label="${getTranslatableMessage( form, 'search-suggestions' )}">`;

		limitedResults.forEach( ( result, index ) => {
			const currentSiteClass = result.is_current_site
				? ' fau-global-search__suggestion-item--current-site'
				: '';

			// Highlight matching text in the title
			const highlightedTitle = highlightMatchingText(
				result.title,
				query
			);

			html += `
				<li class="fau-global-search__suggestion-item${ currentSiteClass }" role="option" data-url="${
					result.link || result.url
				}" data-index="${ index }" tabindex="-1">
					<a href="${
						result.link || result.url
					}" class="fau-global-search__suggestion-link">${ highlightedTitle }</a>
				</li>
			`;
		} );

		html += '</ul>';
		container.innerHTML = html;

		// Add click handlers and keyboard navigation
		const suggestionItems = container.querySelectorAll(
			'.fau-global-search__suggestion-item'
		);

		// Function to update selected item
		function updateSelectedItem( newIndex ) {
			// Remove previous selection
			if ( selectedIndex >= 0 && suggestionItems[ selectedIndex ] ) {
				suggestionItems[ selectedIndex ].classList.remove(
					'fau-global-search__suggestion-item--selected'
				);
				suggestionItems[ selectedIndex ].setAttribute(
					'aria-selected',
					'false'
				);
			}

			// Set new selection
			selectedIndex = newIndex;
			if ( selectedIndex >= 0 && suggestionItems[ selectedIndex ] ) {
				suggestionItems[ selectedIndex ].classList.add(
					'fau-global-search__suggestion-item--selected'
				);
				suggestionItems[ selectedIndex ].setAttribute(
					'aria-selected',
					'true'
				);
				suggestionItems[ selectedIndex ].focus();
			}
		}

		// Function to handle item selection
		function selectItem( item ) {
			if ( item.dataset.url ) {
				// Navigate to specific result
				window.location.href = item.dataset.url;
			} else if ( item.dataset.search ) {
				// Submit search form for all results with FAU-wide scope
				input.value = item.dataset.search;

				// Set FAU-wide scope if this is the "view all" option
				if ( item.dataset.fauWide ) {
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
		}
		// Add keyboard navigation to the input
		const inputKeydownHandler = function ( event ) {
			if (
				! suggestionsContainer ||
				suggestionsContainer.style.display === 'none'
			) {
				return;
			}

			switch ( event.key ) {
				case 'ArrowDown':
					event.preventDefault();
					if ( selectedIndex < suggestionItems.length - 1 ) {
						updateSelectedItem( selectedIndex + 1 );
					} else if ( selectedIndex === -1 ) {
						// First time pressing arrow down, select first item
						updateSelectedItem( 0 );
					}
					break;

				case 'ArrowUp':
					event.preventDefault();
					if ( selectedIndex > 0 ) {
						updateSelectedItem( selectedIndex - 1 );
					} else if ( selectedIndex === 0 ) {
						// Move focus back to input
						updateSelectedItem( -1 );
						input.focus();
					}
					break;

				case 'Enter':
					event.preventDefault();
					if (
						selectedIndex >= 0 &&
						suggestionItems[ selectedIndex ]
					) {
						selectItem( suggestionItems[ selectedIndex ] );
					} else {
						// No item selected, submit the form normally
						form.submit();
					}
					break;

				case 'Escape':
					event.preventDefault();
					hideSuggestions();
					input.focus();
					break;

				case 'Tab':
					// Allow normal tab behavior but hide suggestions
					hideSuggestions();
					break;
			}
		};

		// Remove any existing keyboard handler to prevent duplicates
		input.removeEventListener( 'keydown', inputKeydownHandler );
		input.addEventListener( 'keydown', inputKeydownHandler );

		// Add keyboard navigation to suggestion items
		suggestionItems.forEach( ( item, index ) => {
			item.addEventListener( 'keydown', function ( event ) {
				switch ( event.key ) {
					case 'Enter':
					case ' ':
						event.preventDefault();
						selectItem( this );
						break;

					case 'ArrowDown':
						event.preventDefault();
						if ( index < suggestionItems.length - 1 ) {
							updateSelectedItem( index + 1 );
						}
						break;

					case 'ArrowUp':
						event.preventDefault();
						if ( index > 0 ) {
							updateSelectedItem( index - 1 );
						} else {
							// Move focus back to input
							updateSelectedItem( -1 );
							input.focus();
						}
						break;

					case 'Escape':
						event.preventDefault();
						hideSuggestions();
						input.focus();
						break;
				}
			} );

			// Add focus/blur handlers for visual feedback
			item.addEventListener( 'focus', function () {
				updateSelectedItem( index );
			} );

			item.addEventListener( 'blur', function () {
				// Only remove selection if focus is not moving to another suggestion item
				setTimeout( () => {
					if (
						! container.contains(
							container.ownerDocument.activeElement
						)
					) {
						updateSelectedItem( -1 );
					}
				}, 10 );
			} );
		} );
	}

	// Hide suggestions
	function hideSuggestions() {
		// Reset selection
		selectedIndex = -1;

		if ( suggestionsContainer ) {
			suggestionsContainer.style.display = 'none';

			// Clean up event listeners for dropdown
			if ( suggestionsContainer._repositionHandlers ) {
				window.removeEventListener(
					'resize',
					suggestionsContainer._repositionHandlers
				);
				window.removeEventListener(
					'scroll',
					suggestionsContainer._repositionHandlers
				);
			}
		}

		// Show search options menu again if input is empty (only in modal context)
		if (
			isInMenuModal &&
			input.value.trim().length === 0 &&
			input._showSearchOptionsMenu
		) {
			input._showSearchOptionsMenu();
		}
	}

	// Only hide suggestions when clicking on a suggestion item (let the item's click handler handle it)
	document.addEventListener( 'click', function ( event ) {
		// If clicking on a suggestion item, let the item's click handler handle it
		if ( event.target.closest( '.fau-global-search__suggestion-item' ) ) {
			return;
		}
	} );

	// Hide suggestions on form submit
	form.addEventListener( 'submit', function () {
		hideSuggestions();
	} );
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

	// Expose show/hide functions for the search options menu
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
	// Hide container by default - only show if menu exists
	container.style.display = 'none';

	// Start the AJAX request immediately to check if menu exists
	fetchSearchOptionsMenuFallback( container, form );
}

/**
 * Fallback method to fetch menu using a simpler approach
 */
function fetchSearchOptionsMenuFallback( container ) {
	// Try to get menu via admin-ajax
	const ajaxUrl = window.fauElemental?.ajaxUrl || '/wp-admin/admin-ajax.php';

	// If no AJAX URL is available, container stays hidden
	if ( ! ajaxUrl ) {
		return;
	}

	const formData = new FormData();
	formData.append( 'action', 'get_search_options_menu' );
	formData.append( 'nonce', window.fauElemental?.nonce || '' );

	fetch( ajaxUrl, {
		method: 'POST',
		body: formData,
	} )
		.then( ( response ) => {
			// Check if response is ok first
			if ( ! response.ok ) {
				throw new Error(
					`HTTP ${ response.status }: ${ response.statusText }`
				);
			}
			return response.json();
		} )
		.then( ( data ) => {
			// Check for successful response with menu content
			if (
				data &&
				data.success &&
				data.data &&
				data.data.menu_html &&
				data.data.menu_html.trim()
			) {
				container.innerHTML = data.data.menu_html;
				container.style.display = 'block';
				addMenuClickHandlers( container );
			}
			// If no menu found, container stays hidden (no need to explicitly hide)
		} )
		.catch( () => {
			// Network error, action doesn't exist, or invalid JSON - container stays hidden
		} );
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
