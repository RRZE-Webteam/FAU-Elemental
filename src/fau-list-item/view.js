console.log('FAU List Item View Script Loading...');

// Update the FALLBACK_IMAGE constant at the top of the file
import FALLBACK_IMAGE from '../../assets/images/logo.svg';

document.addEventListener('DOMContentLoaded', () => {
    initializeTeaserGrids();
});

async function initializeTeaserGrids() {
    const teaserGrids = document.querySelectorAll('.wp-block-fau-elemental-fau-list-item');
    teaserGrids.forEach(initializeTeaserGrid);
}

async function initializeTeaserGrid(container) {
    const grid = container.querySelector('.fau-teaser-grid');
    if (!grid) return;

    const variant = grid.dataset.variant || 'post';
    const postsPerPage = parseInt(grid.dataset.postsPerPage) || 15;
    const currentPage = parseInt(grid.dataset.currentPage) || 1;
    const selectedCategory = parseInt(grid.dataset.category) || 0;

    try {
        const apiUrl = `/wp-json/wp/v2/${variant}s?_embed&per_page=${postsPerPage}&page=${currentPage}${selectedCategory ? `&categories=${selectedCategory}` : ''}`;
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const items = await response.json();
        
        if (items.length === 0) {
            grid.innerHTML = '<p>No items found</p>';
            return;
        }
        
        grid.innerHTML = items.map(item => 
            variant === 'post' ? renderPostTeaser(item, grid) : renderPageTeaser(item, grid)
        ).join('');

    } catch (error) {
        grid.innerHTML = `<p>Error loading content: ${error.message}</p>`;
    }
}

function renderPostTeaser(post, grid) {
    const dateObj = post.date ? new Date(post.date) : null;
    const day = dateObj ? dateObj.toLocaleDateString('de-DE', { day: '2-digit' }) : '';
    const monthYear = dateObj ? dateObj.toLocaleDateString('de-DE', {
        month: 'short',
        year: 'numeric'
    }).replace('.', '').toUpperCase() : '';
    const category = post._embedded?.['wp:term']?.[0]?.[0]?.name || '';
    const image = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || FALLBACK_IMAGE;
    const title = post.title?.rendered || '';
    const excerpt = (post.excerpt?.rendered || '').replace('[&hellip;]', '..');
    const link = post.link || '#';

    return `
        <div class="teaser-item post-teaser ${grid.classList.contains('is-style-dark') ? 'dark-theme' : ''}">
            <div class="teaser-image-wrapper">
                <div class="teaser-image">
                    <img src="${image}" alt="${title}" />
                </div>
                <div class="teaser-meta">
                    <time>
                        <span class="date-day">${day}</span>
                        <span class="date-month-year">${monthYear}</span>
                    </time>
                </div>
            </div>
            <div class="teaser-content-wrapper ${grid.classList.contains('is-style-dark') ? 'dark-theme' : ''}">
                <div class="teaser-content">
                    <div class="content-column">
                        ${category ? `<span class="category">${category}</span>` : ''}
                        <h3 class="clamp-3">
                            <span class="visually-hidden">${title}</span>
                            <span aria-hidden="true">${title}</span>
                        </h3>
                        <div class="excerpt clamp-3">
                            <span class="visually-hidden">${excerpt}</span>
                            <span aria-hidden="true">${excerpt}</span>
                        </div>
                    </div>
                    <div class="button-column">
                        <div class="wp-block-button is-style-icon-only">
                            <a href="${link}" class="wp-block-button__link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderPageTeaser(page, grid) {
    const image = page._embedded?.['wp:featuredmedia']?.[0]?.source_url || FALLBACK_IMAGE;
    const title = page.title?.rendered || '';
    const excerpt = (page.excerpt?.rendered || '').replace('[&hellip;]', '..');
    const link = page.link || '#';

    return `
        <div class="teaser-item page-teaser ${grid.classList.contains('is-style-dark') ? 'dark-theme' : ''}">
            <div class="teaser-image">
                <img src="${image}" alt="${title}" />
            </div>
            <div class="teaser-content-wrapper ${grid.classList.contains('is-style-dark') ? 'dark-theme' : ''}">
                <div class="teaser-content">
                    <div class="content-column">
                        <h3 class="clamp-3">
                            <span class="visually-hidden">${title}</span>
                            <span aria-hidden="true">${title}</span>
                        </h3>
                        <div class="excerpt clamp-3">
                            <span class="visually-hidden">${excerpt}</span>
                            <span aria-hidden="true">${excerpt}</span>
                        </div>
                    </div>
                    <div class="button-column">
                        <div class="wp-block-button is-style-icon-only">
                            <a href="${link}" class="wp-block-button__link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}