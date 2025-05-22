/**
 * FAU Global Search Suggestions
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchForms = document.querySelectorAll('.wp-block-fau-elemental-fau-global-search__form');

    searchForms.forEach(form => {
        const searchInput = form.querySelector('input[type="search"]');
        const suggestionsArea = form.closest('.wp-block-fau-elemental-fau-global-search').querySelector('.search-suggestions-area');
        const defaultContent = suggestionsArea.innerHTML;

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
                    cachedFAQs = await response.json();
                } catch (error) {
                    console.error('Error fetching FAQs:', error);
                    return;
                }
            }

            if (cachedFAQs && cachedFAQs.length > 0) {
                const faqHtml = `
                    <div class="search-faqs">
                        <h3 class="search-faqs-title">${window.fauGlobalSearch.strings.faqsTitle}</h3>
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
                const suggestions = await response.json();

                if (suggestions.length > 0) {
                    const suggestionsHtml = `
                        <div class="search-suggestions">
                            <h3 class="search-suggestions-title">${window.fauGlobalSearch.strings.suggestionsTitle}</h3>
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
                }
            } catch (error) {
                console.error('Error fetching suggestions:', error);
            }
        }
    });
}); 