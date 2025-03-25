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

    // Get the display style from data attribute
    const displayStyle = grid.dataset.style || 'teaser-grid';
    
    // Ensure the correct display style class is applied
    grid.className = `fau-teaser-grid ${displayStyle}`;

    const variant = grid.dataset.variant || 'post';
    const postsPerPage = parseInt(grid.dataset.postsPerPage) || 15;
    const currentPage = parseInt(grid.dataset.currentPage) || 1;
    const selectedCategory = parseInt(grid.dataset.category) || 0;
    const showPagination = grid.dataset.showPagination !== 'false';
    const totalPosts = parseInt(grid.dataset.totalPosts) || -1;
    const orderBy = grid.dataset.orderBy || 'date';
    const order = grid.dataset.order || 'DESC';

    try {
        // First, get total number of posts
        const countUrl = `/wp-json/wp/v2/${variant}s?per_page=1`;
        const countResponse = await fetch(countUrl);
        const totalItems = parseInt(countResponse.headers.get('X-WP-Total')) || 0;
        
        // Calculate total pages
        const effectiveTotalPosts = totalPosts > 0 ? Math.min(totalPosts, totalItems) : totalItems;
        const totalPages = Math.ceil(effectiveTotalPosts / postsPerPage);

        // Keep the original working query
        let apiUrl = `/wp-json/wp/v2/${variant}s?_embed&per_page=${postsPerPage}&page=${currentPage}`;
        
        if (selectedCategory) {
            apiUrl += `&categories=${selectedCategory}`;
        }

        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        let items = await response.json();
        
        // Sort items after fetching
        if (items && Array.isArray(items)) {
            items = [...items].sort((a, b) => {
                if (orderBy === 'title') {
                    const titleA = a.title?.rendered?.toLowerCase() || '';
                    const titleB = b.title?.rendered?.toLowerCase() || '';
                    return order === 'ASC' ? 
                        titleA.localeCompare(titleB) : 
                        titleB.localeCompare(titleA);
                } else {
                    // Default to date sorting
                    const dateA = new Date(a.date);
                    const dateB = new Date(b.date);
                    return order === 'ASC' ? 
                        dateA - dateB : 
                        dateB - dateA;
                }
            });
        }

        if (items.length === 0) {
            grid.innerHTML = '<p>No items found</p>';
            return;
        }
        
        // Use the same rendering for all display styles
        grid.innerHTML = items.map(item => 
            variant === 'post' ? renderPostTeaser(item, grid) : renderPageTeaser(item, grid)
        ).join('');

        // Add pagination if enabled and there are multiple pages
        if (showPagination && totalPages > 1) {
            // Remove existing pagination if any
            const existingPagination = container.querySelector('.pagination');
            if (existingPagination) {
                existingPagination.remove();
            }
            
            // Create pagination HTML and add it after the grid
            const paginationHtml = createPagination(currentPage, totalPages, container);
            grid.insertAdjacentHTML('afterend', paginationHtml);

            // Add event listeners to pagination
            const paginationElement = container.querySelector('.pagination');
            if (paginationElement) {
                paginationElement.addEventListener('click', async (e) => {
                    const button = e.target.closest('button');
                    if (button && !button.disabled) {
                        const newPage = parseInt(button.dataset.page);
                        grid.dataset.currentPage = newPage;
                        await initializeTeaserGrid(container);
                    }
                });
            }
        }

    } catch (error) {
        console.error('Error:', error);
        grid.innerHTML = `<p>Error loading content: ${error.message}</p>`;
    }
}

function createPagination(currentPage, totalPages, container) {
    const pages = [];
    
    // Add Previous button
    pages.push(`
        <button 
            class="page-number prev ${currentPage === 1 ? 'disabled' : ''}"
            data-page="${currentPage - 1}"
            ${currentPage === 1 ? 'disabled' : ''}
        >
            Prev
        </button>
    `);

    // Add page numbers
    for (let i = 1; i <= totalPages; i++) {
        // Show first page, last page, current page, and pages around current
        if (
            i === 1 || 
            i === totalPages || 
            (i >= currentPage - 1 && i <= currentPage + 1)
        ) {
            pages.push(`
                <button 
                    class="page-number ${currentPage === i ? 'active' : ''}"
                    data-page="${i}"
                >
                    ${i}
                </button>
            `);
        } else if (
            i === currentPage - 2 ||
            i === currentPage + 2
        ) {
            // Add ellipsis
            pages.push('<span class="page-ellipsis">...</span>');
        }
    }

    // Add Next button
    pages.push(`
        <button 
            class="page-number next ${currentPage === totalPages ? 'disabled' : ''}"
            data-page="${currentPage + 1}"
            ${currentPage === totalPages ? 'disabled' : ''}
        >
            Next
        </button>
    `);
    
    const paginationHtml = `<div class="pagination">${pages.join('')}</div>`;
    
    return paginationHtml;
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
        <div class="teaser-item post-teaser">
            <div class="teaser-image-wrapper">
                <div class="teaser-image">
                    <img src="${image}" alt="${title}" />
                </div>
                <div class="teaser-meta">
                    <time>
                        <span class="date-day">${day}</span>
                        <span class="date-month-year">${monthYear}</span>
                    </time>
                    ${category ? `<span class="category">${category}</span>` : ''}
                </div>
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

function renderPageTeaser(page, grid) {
    const image = page._embedded?.['wp:featuredmedia']?.[0]?.source_url || FALLBACK_IMAGE;
    const title = page.title?.rendered || '';
    const excerpt = (page.excerpt?.rendered || '').replace('[&hellip;]', '..');
    const link = page.link || '#';

    return `
        <div class="teaser-item page-teaser">
            <div class="teaser-image-wrapper">
                <div class="teaser-image">
                    <img src="${image}" alt="${title}" />
                </div>
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