/**
 * FAU Global Search Suggestions
 */

function initializeSearchSuggestions() {
    const searchForms = document.querySelectorAll('.wp-block-fau-elemental-fau-global-search__form');

    searchForms.forEach(form => {
        // Skip if already initialized
        if (form.dataset.searchInitialized === 'true') {
            return;
        }

        const searchInput = form.querySelector('input[type="search"]');
        if (!searchInput) {
            console.warn('Search input not found in form');
            return;
        }

        // Find the suggestions area - it could be in different places
        const suggestionsArea = form.closest('.wp-block-fau-elemental-fau-global-search')?.querySelector('.search-suggestions-area') ||
                              form.closest('.wp-block-fau-elemental-fau-global-search')?.querySelector('.wp-block-fau-elemental-fau-global-search__suggestions-area') ||
                              form.closest('.menu-modal__content')?.querySelector('.search-suggestions-area') ||
                              form.closest('.menu-modal__content')?.querySelector('.wp-block-fau-elemental-fau-global-search__suggestions-area');
        
        if (!suggestionsArea) {
            console.warn('Search suggestions area not found');
            return;
        }

        // Store the default content
        if (!suggestionsArea.dataset.defaultContent) {
            suggestionsArea.dataset.defaultContent = suggestionsArea.innerHTML;
        }

        const defaultContent = suggestionsArea.dataset.defaultContent;

        // Store FAQs for quick access
        let cachedFrequentQueries = null;

        // Handle focus event
        searchInput.addEventListener('focus', async () => {
            if (searchInput.value.length === 0) {
                await showFrequentQueries(suggestionsArea);
            }
        });

        // Handle input event
        searchInput.addEventListener('input', async () => {
            if (searchInput.value.length === 0) {
                showDefaultSuggestions();
            } else if (searchInput.value.length >= 3) {
                await fetchLiveSuggestions(searchInput.value, suggestionsArea);
            }
        });

        // Handle blur event
        searchInput.addEventListener('blur', () => {
            // Small delay to allow for clicking on suggestions
            setTimeout(() => {
                if (document.activeElement !== searchInput) {
                    showDefaultSuggestions();
                }
            }, 200);
        });

        async function showFrequentQueries(suggestionsArea) {
            const searchScope = document.querySelector('input[name="fau_search_scope"]')?.value || 'current';
            fetch(`${fauGlobalSearch.restUrl}fau/v1/frequent-queries?per_page=5`, {
                headers: {
                    'X-WP-Nonce': fauGlobalSearch.restNonce
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const html = `
                        <div class="search-suggestions">
                            <h3 class="search-suggestions__title">${fauGlobalSearch.strings.faqsTitle || 'Frequently Searched'}</h3>
                            <ul class="search-suggestions__list">
                                ${data.map(item => {
                                    const query = item.query || item.title || '';
                                    if (!query) return '';
                                    return `
                                        <li class="search-suggestions__item">
                                            <a href="${window.location.origin}/?s=${encodeURIComponent(query)}&fau_search_scope=${searchScope}" class="search-suggestions__link">
                                                ${query}
                                            </a>
                                        </li>
                                    `;
                                }).join('')}
                            </ul>
                        </div>
                    `;
                    suggestionsArea.innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error fetching frequent queries:', error);
                suggestionsArea.innerHTML = '';
            });
        }

        function showDefaultSuggestions() {
            suggestionsArea.innerHTML = defaultContent;
        }

        function fetchLiveSuggestions(query, suggestionsArea) {
            if (query.length < 3) {
                suggestionsArea.innerHTML = '';
                return;
            }

            fetch(`${fauGlobalSearch.restUrl}fau/v1/search-suggestions?search=${encodeURIComponent(query)}`, {
                headers: {
                    'X-WP-Nonce': fauGlobalSearch.restNonce
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const html = `
                        <div class="search-suggestions">
                            <h3 class="search-suggestions__title">${fauGlobalSearch.strings.suggestionsTitle || 'Suggestions'}</h3>
                            <ul class="search-suggestions__list">
                                ${data.map(item => {
                                    const title = item.title || item.query || '';
                                    if (!title) return '';
                                    return `
                                        <li class="search-suggestions__item">
                                            <a href="${item.link || `${window.location.origin}/?s=${encodeURIComponent(title)}`}" class="search-suggestions__link">
                                                ${title}
                                            </a>
                                        </li>
                                    `;
                                }).join('')}
                            </ul>
                        </div>
                    `;
                    suggestionsArea.innerHTML = html;
                } else {
                    suggestionsArea.innerHTML = `
                        <div class="search-suggestions">
                            <p class="search-suggestions__no-results">${fauGlobalSearch.strings.noResults || 'No results found'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching search suggestions:', error);
                suggestionsArea.innerHTML = '';
            });
        }

        // Mark as initialized
        form.dataset.searchInitialized = 'true';
    });
}

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSearchSuggestions();
});

// Initialize when modal opens
jQuery(document).ready(function($) {
    // Listen for modal open events
    $('.menu-modal__open-btn[data-modal-target="search"]').on('click', function() {
        // Wait for modal to be added to DOM and shown
        setTimeout(function() {
            initializeSearchSuggestions();
        }, 100);
    });

    // Listen for modal close events
    $('.menu-modal__close-btn').on('click', function() {
        const modal = $(this).closest('.menu-modal');
        if (modal.attr('id') === 'search-modal') {
            // Reset the search form when modal is closed
            const form = modal.find('.wp-block-fau-elemental-fau-global-search__form');
            if (form.length) {
                form[0].reset();
                const suggestionsArea = form.closest('.menu-modal__content').find('.wp-block-fau-elemental-fau-global-search__suggestions-area');
                if (suggestionsArea.length) {
                    suggestionsArea.html(suggestionsArea.data('default-content'));
                }
            }
        }
    });

    // Listen for modal visibility changes
    $('.menu-modal').on('transitionend', function() {
        if ($(this).hasClass('is-open')) {
            initializeSearchSuggestions();
        }
    });
}); 