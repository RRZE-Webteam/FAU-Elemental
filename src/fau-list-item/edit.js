import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    RangeControl, 
    ToggleControl,
    SelectControl,
    Placeholder,
    Spinner,
    ButtonGroup,
    Button
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import './editor.scss';

const FALLBACK_IMAGE = './../../assets/images/logo.svg';

function PostTeaser({ post, grid }) {
    if (!post) return null;

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

    return (
        <div className="teaser-item">
            {image && (
                <div className="teaser-image-wrapper">
                    <div className="teaser-image">
                        <img src={image} alt={title} />
                    </div>
                    <div className="teaser-meta">
                        <time>
                            <span className="date-day">{day}</span>
                            <span className="date-month-year">{monthYear}</span>
                        </time>
                    </div>
                </div>
            )}
            <div className="teaser-content-wrapper">
                <div className="teaser-content">
                    <div className="content-column">
                        {category && <span className="category">{category}</span>}
                        <h3 className="clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: title }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: title }} />
                        </h3>
                        <div className="excerpt clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: excerpt }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: excerpt }} />
                        </div>
                    </div>
                    <div className="button-column">
                        <div className="wp-block-button is-style-icon-only">
                            <a href={link} className="wp-block-button__link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function PageTeaser({ page, grid }) {
    if (!page) return null;

    const image = page._embedded?.['wp:featuredmedia']?.[0]?.source_url || FALLBACK_IMAGE;
    const title = page.title?.rendered || '';
    const excerpt = (page.excerpt?.rendered || '').replace('[&hellip;]', '..');
    const link = page.link || '#';

    return (
        <div className="teaser-item">
            {image && (
                <div className="teaser-image">
                    <img src={image} alt={title} />
                </div>
            )}
            <div className="teaser-content-wrapper">
                <div className="teaser-content">
                    <div className="content-column">
                        <h3 className="clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: title }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: title }} />
                        </h3>
                        <div className="excerpt clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: excerpt }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: excerpt }} />
                        </div>
                    </div>
                    <div className="button-column">
                        <div className="wp-block-button is-style-icon-only">
                            <a href={link} className="wp-block-button__link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function createPagination(currentPage, totalPages, onPageChange) {
    const pages = [];
    
    // Add Previous button
    pages.push(
        <button 
            key="prev"
            className={`page-number prev ${currentPage === 1 ? 'disabled' : ''}`}
            onClick={() => onPageChange(currentPage - 1)}
            disabled={currentPage === 1}
        >
            Prev
        </button>
    );

    // Add page numbers
    for (let i = 1; i <= totalPages; i++) {
        // Show first page, last page, current page, and pages around current
        if (
            i === 1 || 
            i === totalPages || 
            (i >= currentPage - 1 && i <= currentPage + 1)
        ) {
            pages.push(
                <button 
                    key={i}
                    className={`page-number ${currentPage === i ? 'active' : ''}`}
                    onClick={() => onPageChange(i)}
                >
                    {i}
                </button>
            );
        } else if (
            i === currentPage - 2 ||
            i === currentPage + 2
        ) {
            // Add ellipsis
            pages.push(
                <span key={`ellipsis-${i}`} className="page-ellipsis">
                    ...
                </span>
            );
        }
    }

    // Add Next button
    pages.push(
        <button 
            key="next"
            className={`page-number next ${currentPage === totalPages ? 'disabled' : ''}`}
            onClick={() => onPageChange(currentPage + 1)}
            disabled={currentPage === totalPages}
        >
            Next
        </button>
    );
    
    return <div className="pagination">{pages}</div>;
}

function renderItems(items, variant, grid) {
    // Use the same rendering for both list and teaser grid styles
    return items.map(item => 
        variant === 'post' ? renderPostTeaser(item, grid) : renderPageTeaser(item, grid)
    ).join('');
}

// Define teaser layout options
const TEASER_LAYOUTS = [
    { label: __('1 Extra Large Teaser (1×XL)', 'fau-elemental'), value: '1xl' },
    { label: __('2 Large Teasers (2×L)', 'fau-elemental'), value: '2l' },
    { label: __('1 Large + 2 Small (L+2S)', 'fau-elemental'), value: 'l2s' },
    { label: __('2 Small + 1 Large (2S+L)', 'fau-elemental'), value: '2sl' },
    { label: __('3 Medium Teasers (3×M)', 'fau-elemental'), value: '3m' },
    { label: __('2 Small - Image Left (2S BLTR)', 'fau-elemental'), value: '2s-left' },
    { label: __('2 Small - Image Right (2S TLBR)', 'fau-elemental'), value: '2s-right' }
];

export default function Edit({ attributes, setAttributes }) {
    const { 
        displayStyle,
        variant,
        teaserLayout,
        postsPerPage,
        showFilters,
        selectedCategory,
        selectedTaxonomy,
        currentPage,
        showPagination,
        totalPosts,
        orderBy,
        order
    } = attributes;

    const { postTypes, taxonomies, terms, items, totalPages, isLoading } = useSelect((select) => {
        const coreSelect = select('core');
        
        // Default to 'post' if variant is empty
        const currentVariant = variant || 'post';

        // Keep the original working query
        let query = {
            _embed: true,
            per_page: postsPerPage,
            page: currentPage
        };

        // Only add category if it's actually selected
        if (selectedCategory && selectedCategory !== 0) {
            query.categories = selectedCategory;
        }

        // Get posts with the working query
        let posts = coreSelect.getEntityRecords('postType', currentVariant, query);

        // Sort posts after fetching if we have posts and ordering is specified
        if (posts && Array.isArray(posts)) {
            posts = [...posts].sort((a, b) => {
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

        // Debug logging
        console.log('Sorted Posts:', posts);

        // Get total posts count
        const totalItems = posts?._pagination?.total || 0;
        const effectiveTotalPosts = totalPosts > 0 ? Math.min(totalPosts, totalItems) : totalItems;
        const calculatedTotalPages = Math.ceil(effectiveTotalPosts / postsPerPage) || 1;

        const isLoadingPostTypes = coreSelect.isResolving('getPostTypes');
        const isLoadingTaxonomies = coreSelect.isResolving('getTaxonomies');
        const isLoadingTerms = selectedTaxonomy ? 
            coreSelect.isResolving('getEntityRecords', ['taxonomy', selectedTaxonomy, { per_page: -1 }]) : false;
        const isLoadingPosts = coreSelect.isResolving('getEntityRecords', ['postType', currentVariant, query]);

        // Log loading states
        console.log('Loading States:', {
            isLoadingPostTypes,
            isLoadingTaxonomies,
            isLoadingTerms,
            isLoadingPosts
        });

        return {
            postTypes: coreSelect.getPostTypes()?.filter(type => 
                type.viewable && type.slug !== 'attachment'
            ) || [],
            taxonomies: coreSelect.getTaxonomies()?.filter(tax => 
                tax.types.includes(currentVariant)
            ) || [],
            terms: selectedTaxonomy ? 
                coreSelect.getEntityRecords('taxonomy', selectedTaxonomy, { per_page: -1 }) : [],
            items: Array.isArray(posts) ? posts : [],
            totalPages: calculatedTotalPages,
            isLoading: isLoadingPostTypes || isLoadingTaxonomies || isLoadingTerms || isLoadingPosts
        };
    }, [variant, postsPerPage, selectedTaxonomy, selectedCategory, currentPage, totalPosts, orderBy, order]);

    // Create options for post types select with proper labels
    const postTypeOptions = postTypes.map(type => ({
        label: type.labels?.singular_name || type.name,
        value: type.slug
    }));

    // Create options for taxonomies select with proper labels
    const taxonomyOptions = taxonomies.map(tax => ({
        label: tax.labels?.singular_name || tax.name,
        value: tax.slug
    }));

    // Create options for terms select
    const termOptions = terms ? terms.map(term => ({
        label: term.name,
        value: term.id.toString() // Ensure ID is a string
    })) : [];

    // Sorting options
    const sortingOptions = [
        { label: __('Date', 'fau-elemental'), value: 'date' },
        { label: __('Title', 'fau-elemental'), value: 'title' }
    ];

    const orderOptions = [
        { label: __('Ascending', 'fau-elemental'), value: 'ASC' },
        { label: __('Descending', 'fau-elemental'), value: 'DESC' }
    ];

    const blockProps = useBlockProps({
        className: `style-${displayStyle}`
    });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Display Settings', 'fau-elemental')}>
                    <ButtonGroup>
                        <Button
                            isPrimary={displayStyle === 'teaser-grid'}
                            onClick={() => setAttributes({ displayStyle: 'teaser-grid' })}
                        >
                            {__('Teaser Grid', 'fau-elemental')}
                        </Button>
                        <Button
                            isPrimary={displayStyle === 'list-item'}
                            onClick={() => setAttributes({ displayStyle: 'list-item' })}
                        >
                            {__('List Item', 'fau-elemental')}
                        </Button>
                        <Button
                            isPrimary={displayStyle === 'mini-list'}
                            onClick={() => setAttributes({ displayStyle: 'mini-list' })}
                        >
                            {__('Mini List', 'fau-elemental')}
                        </Button>
                    </ButtonGroup>
                    
                    {/* Show teaser layout only when teaser-grid is selected */}
                    {displayStyle === 'teaser-grid' && (
                        <SelectControl
                            label={__('Teaser Layout', 'fau-elemental')}
                            value={teaserLayout}
                            options={TEASER_LAYOUTS}
                            onChange={(value) => setAttributes({ teaserLayout: value })}
                        />
                    )}
                </PanelBody>

                <PanelBody title={__('Content Settings', 'fau-elemental')}>
                    <SelectControl
                        label={__('Content Type', 'fau-elemental')}
                        value={variant}
                        options={[
                            { label: __('Posts', 'fau-elemental'), value: 'post' },
                            { label: __('Pages', 'fau-elemental'), value: 'page' }
                        ]}
                        onChange={(value) => setAttributes({ variant: value })}
                    />

                    <RangeControl
                        label={__('Posts per Page', 'fau-elemental')}
                        value={postsPerPage}
                        onChange={(value) => setAttributes({ postsPerPage: value })}
                        min={1}
                        max={20}
                    />

                    <RangeControl
                        label={__('Total Posts', 'fau-elemental')}
                        value={totalPosts}
                        onChange={(value) => setAttributes({ totalPosts: value })}
                        min={-1}
                        max={100}
                        help={__('-1 for all posts', 'fau-elemental')}
                    />

                    <SelectControl
                        label={__('Sort By', 'fau-elemental')}
                        value={orderBy}
                        options={sortingOptions}
                        onChange={(value) => setAttributes({ orderBy: value })}
                    />

                    <SelectControl
                        label={__('Sort Order', 'fau-elemental')}
                        value={order}
                        options={orderOptions}
                        onChange={(value) => setAttributes({ order: value })}
                    />

                 
                    <ToggleControl
                        label={__('Show Pagination', 'fau-elemental')}
                        checked={showPagination}
                        onChange={() => setAttributes({ showPagination: !showPagination })}
                    />
                </PanelBody>
            </InspectorControls>
            
            <div {...blockProps}>
                {showFilters && taxonomies.length > 0 && (
                    <div className="list-filter">
                        <SelectControl
                            label={__('Filter by', 'fau-elemental')}
                            value={selectedTaxonomy}
                            options={[
                                { label: __('Select a taxonomy', 'fau-elemental'), value: '' },
                                ...taxonomyOptions
                            ]}
                            onChange={(value) => {
                                setAttributes({ 
                                    selectedTaxonomy: value,
                                    selectedCategory: 0
                                });
                            }}
                        />
                    </div>
                )}
                
                <div className={`fau-teaser-grid ${displayStyle} layout-${teaserLayout}`}>
                    {!isLoading ? (
                        items && items.length > 0 ? (
                            items.map((item) => (
                                variant === 'post' 
                                    ? <PostTeaser key={item.id} post={item} grid={blockProps} />
                                    : <PageTeaser key={item.id} page={item} grid={blockProps} />
                            ))
                        ) : (
                            <div>
                                <p>{__('No items found', 'fau-elemental')}</p>
                                <pre style={{fontSize: '12px', background: '#f0f0f0', padding: '10px'}}>
                                    Current State:
                                    Content Type: {variant}
                                    Posts Per Page: {postsPerPage}
                                    Selected Category: {selectedCategory}
                                    Selected Taxonomy: {selectedTaxonomy}
                                    Current Page: {currentPage}
                                </pre>
                            </div>
                        )
                    ) : (
                        <Placeholder>
                            <Spinner />
                            <p>{__('Loading...', 'fau-elemental')}</p>
                        </Placeholder>
                    )}
                </div>

                {showPagination && totalPages > 1 && (
                    createPagination(currentPage, totalPages, (newPage) => 
                        setAttributes({ currentPage: newPage })
                    )
                )}
            </div>
        </>
    );
}
