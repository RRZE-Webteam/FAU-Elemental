document.addEventListener('DOMContentLoaded', () => {
    const teaserGrids = document.querySelectorAll('.wp-block-fau-elemental-fau-list-item .fau-teaser-grid');
    teaserGrids.forEach(initializeTeaserGrid);
});

async function initializeTeaserGrid(grid) {
    const variant = grid.dataset.variant || 'post';
    const postsPerPage = parseInt(grid.dataset.postsPerPage) || 15;
    const currentPage = parseInt(grid.dataset.currentPage) || 1;
    const selectedCategory = parseInt(grid.dataset.category) || 0;
    
    try {
        let apiUrl = `/wp-json/wp/v2/${variant}s?_embed=true&per_page=${postsPerPage}&page=${currentPage}`;
        
        if (variant === 'post' && selectedCategory) {
            apiUrl += `&categories=${selectedCategory}`;
        }

        const response = await fetch(apiUrl);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const items = await response.json();
        
        if (!items || !Array.isArray(items)) {
            throw new Error('Invalid response format');
        }

        grid.innerHTML = items.map(item => 
            variant === 'post' ? renderPostTeaser(item) : renderPageTeaser(item)
        ).join('');
    } catch (error) {
        console.error('Error loading content:', error);
        grid.innerHTML = `<p>Error loading content: ${error.message}</p>`;
    }
}

function renderPostTeaser(post) {
    try {
        const date = new Date(post.date).toLocaleDateString();
        const category = post._embedded?.['wp:term']?.[0]?.[0]?.name;
        const image = post._embedded?.['wp:featuredmedia']?.[0]?.source_url;
        const title = post.title?.rendered || '';
        const excerpt = post.excerpt?.rendered || '';
        const link = post.link || '#';

        return `
            <div class="teaser-item">
                <div class="teaser-meta">
                    <time>${date}</time>
                    ${category ? `<span class="category">${category}</span>` : ''}
                </div>
                ${image ? `
                    <div class="teaser-image">
                        <img src="${image}" alt="${title}" />
                    </div>
                ` : ''}
                <h3>${title}</h3>
                <div class="excerpt">${excerpt}</div>
                <a href="${link}" class="teaser-link">Read more</a>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering post teaser:', error);
        return '<div class="teaser-item">Error rendering post</div>';
    }
}

function renderPageTeaser(page) {
    try {
        const image = page._embedded?.['wp:featuredmedia']?.[0]?.source_url;
        const title = page.title?.rendered || '';
        const excerpt = page.excerpt?.rendered || '';
        const link = page.link || '#';

        return `
            <div class="teaser-item">
                ${image ? `
                    <div class="teaser-image">
                        <img src="${image}" alt="${title}" />
                    </div>
                ` : ''}
                <h3>${title}</h3>
                <div class="excerpt">${excerpt}</div>
                <a href="${link}" class="teaser-link">View page</a>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering page teaser:', error);
        return '<div class="teaser-item">Error rendering page</div>';
    }
} 