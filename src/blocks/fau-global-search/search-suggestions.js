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
                              form.closest('.menu-meta-nav__search-content')?.querySelector('.search-suggestions-area') ||
                              form.closest('.menu-meta-nav__search-content')?.querySelector('.wp-block-fau-elemental-fau-global-search__suggestions-area');
        
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
        let cachedFAQs = null;

        // Handle focus event
        searchInput.addEventListener('focus', async () => {
            if (searchInput.value.length === 0) {
                await showFAQs();
            }
        });

        // Handle input event
        searchInput.addEventListener('input', async () => {
            if (searchInput.value.length === 0) {
                showDefaultSuggestions();
            } else if (searchInput.value.length >= 3) {
                await fetchLiveSuggestions(searchInput.value);
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

        async function showFAQs() {
            if (!cachedFAQs) {
                try {
                    const response = await fetch('/wp-json/wp/v2/faq?per_page=5');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    cachedFAQs = await response.json();
                } catch (error) {
                    console.error('Error fetching FAQs:', error);
                    return;
                }
            }

            if (cachedFAQs && cachedFAQs.length > 0) {
                const faqHtml = `
                    <div class="search-faqs">
                        <h3 class="search-faqs-title">${window.fauGlobalSearch?.strings?.faqsTitle || 'Frequently Asked Questions'}</h3>
                        <ul class="search-faqs-list">
                            ${cachedFAQs.map(faq => `
                                <li>
                                    <a href="${faq.link}">${faq.title.rendered}</a>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
                suggestionsArea.innerHTML = faqHtml;
            }
        }

        function showDefaultSuggestions() {
            suggestionsArea.innerHTML = defaultContent;
        }

        async function fetchLiveSuggestions(query) {
            try {
                const response = await fetch(`/wp-json/fau/v1/search-suggestions?search=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const suggestions = await response.json();

                if (suggestions && suggestions.length > 0) {
                    const suggestionsHtml = `
                        <div class="search-suggestions">
                            <h3 class="search-suggestions-title">${window.fauGlobalSearch?.strings?.suggestionsTitle || 'Search Suggestions'}</h3>
                            <ul class="search-suggestions-list">
                                ${suggestions.map(suggestion => `
                                    <li>
                                        <a href="${suggestion.link}">${suggestion.title}</a>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    `;
                    suggestionsArea.innerHTML = suggestionsHtml;
                } else {
                    suggestionsArea.innerHTML = `<div class="search-suggestions">
                        <p>${window.fauGlobalSearch?.strings?.noResults || 'No results found'}</p>
                    </div>`;
                }
            } catch (error) {
                console.error('Error fetching suggestions:', error);
                suggestionsArea.innerHTML = `<div class="search-suggestions">
                    <p>Error loading suggestions. Please try again.</p>
                </div>`;
            }
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
    $('.menu-meta-nav__open-btn[data-meta-modal="search"]').on('click', function() {
        // Wait for modal to be added to DOM and shown
        setTimeout(function() {
            initializeSearchSuggestions();
        }, 100);
    });

    // Listen for modal close events
    $('.menu-meta-nav__close-btn').on('click', function() {
        const modal = $(this).closest('.menu-meta-nav__modal');
        if (modal.attr('id') === 'search-modal') {
            // Reset the search form when modal is closed
            const form = modal.find('.wp-block-fau-elemental-fau-global-search__form');
            if (form.length) {
                form[0].reset();
                const suggestionsArea = form.closest('.menu-meta-nav__search-content').find('.wp-block-fau-elemental-fau-global-search__suggestions-area');
                if (suggestionsArea.length) {
                    suggestionsArea.html(suggestionsArea.data('default-content'));
                }
            }
        }
    });

    // Listen for modal visibility changes
    $('.menu-meta-nav__modal').on('transitionend', function() {
        if ($(this).hasClass('is-open')) {
            initializeSearchSuggestions();
        }
    });
}); 