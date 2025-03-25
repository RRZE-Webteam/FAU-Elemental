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
    Button,
    ComboboxControl
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import './editor.scss';
import { useState } from 'react';

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
        selectedCategory,
        currentPage,
        showPagination,
        totalPosts,
        orderBy,
        order,
        selectedPosts,
        selectionMode
    } = attributes;

    // Add this new state for search
    const [searchTerm, setSearchTerm] = useState('');
    const [searchResults, setSearchResults] = useState([]);

    // Get post types and categories
    const { postTypes, categories, items, totalPages, isLoading } = useSelect((select) => {
        const coreSelect = select('core');
        const allPostTypes = coreSelect.getPostTypes();
        
        // Get all categories
        const allCategories = coreSelect.getEntityRecords('taxonomy', 'category', { per_page: -1 }) || [];
        
        // Get all post types that are viewable (public)
        const availablePostTypes = allPostTypes?.filter(type => 
            type.viewable && 
            type.slug !== 'attachment' &&
            type.slug !== 'wp_block'
        ) || [];

        // Build query for current post type
        let query = {
            _embed: true,
            per_page: postsPerPage,
            page: currentPage
        };

        // Add category to query if selected
        if (selectedCategory) {
            query.categories = selectedCategory;
        }

        // Get posts
        const posts = coreSelect.getEntityRecords('postType', variant, query);
        
        // Calculate total pages
        const totalItems = coreSelect.getEntityRecords('postType', variant, { ...query, per_page: -1 })?.length || 0;
        const calculatedTotalPosts = totalPosts > 0 ? Math.min(totalPosts, totalItems) : totalItems;
        const calculatedTotalPages = Math.ceil(calculatedTotalPosts / postsPerPage);

        return {
            postTypes: availablePostTypes,
            categories: allCategories,
            items: Array.isArray(posts) ? posts : [],
            totalPages: calculatedTotalPages,
            isLoading: coreSelect.isResolving('getEntityRecords', ['postType', variant, query])
        };
    }, [variant, postsPerPage, currentPage, totalPosts, selectedCategory]);

    // Convert post types to options
    const postTypeOptions = postTypes.map(type => ({
        label: type.labels?.singular_name || type.name,
        value: type.slug
    }));

    // Convert categories to options
    const categoryOptions = [
        { label: __('All Categories', 'fau-elemental'), value: 0 },
        ...categories.map(category => ({
            label: category.name,
            value: category.id
        }))
    ];

    // Sorting options
    const sortingOptions = [
        { label: __('Date', 'fau-elemental'), value: 'date' },
        { label: __('Title', 'fau-elemental'), value: 'title' }
    ];

    const orderOptions = [
        { label: __('Ascending', 'fau-elemental'), value: 'ASC' },
        { label: __('Descending', 'fau-elemental'), value: 'DESC' }
    ];

    // Add this new select to get available posts
    const { availablePosts } = useSelect((select) => {
        if (searchTerm) {
            return {
                availablePosts: select('core').getEntityRecords('postType', variant, {
                    search: searchTerm,
                    per_page: 20,
                    _fields: ['id', 'title']
                })
            };
        }
        return { availablePosts: [] };
    }, [searchTerm, variant]);

    // Add this function to handle post selection
    const handlePostSelection = (postId) => {
        if (!postId) return;
        
        const post = availablePosts.find(p => p.id === postId);
        if (!post) return;

        const newSelectedPosts = [...selectedPosts];
        if (!newSelectedPosts.some(p => p.id === post.id)) {
            newSelectedPosts.push({
                id: post.id,
                title: post.title.rendered
            });
            setAttributes({ selectedPosts: newSelectedPosts });
        }
    };

    // Add this function to remove selected posts
    const removeSelectedPost = (postId) => {
        const newSelectedPosts = selectedPosts.filter(p => p.id !== postId);
        setAttributes({ selectedPosts: newSelectedPosts });
    };

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
                    
                    {displayStyle === 'teaser-grid' && (
                        <SelectControl
                            label={__('Teaser Layout', 'fau-elemental')}
                            value={teaserLayout}
                            options={TEASER_LAYOUTS}
                            onChange={(value) => setAttributes({ teaserLayout: value })}
                        />
                    )}
                </PanelBody>

                <PanelBody title={__('Selection Mode', 'fau-elemental')}>
                    <ButtonGroup>
                        <Button
                            isPrimary={selectionMode === 'auto'}
                            onClick={() => {
                                setAttributes({ 
                                    selectionMode: 'auto',
                                    selectedPosts: [] // Clear selected posts when switching to auto
                                });
                            }}
                        >
                            {__('Automatic', 'fau-elemental')}
                        </Button>
                        <Button
                            isPrimary={selectionMode === 'manual'}
                            onClick={() => setAttributes({ selectionMode: 'manual' })}
                        >
                            {__('Manual Selection', 'fau-elemental')}
                        </Button>
                    </ButtonGroup>

                    {selectionMode === 'manual' && (
                        <>
                            <ComboboxControl
                                label={__('Search and select posts', 'fau-elemental')}
                                value=""
                                onChange={handlePostSelection}
                                options={
                                    availablePosts
                                        ? availablePosts.map(post => ({
                                            value: post.id,
                                            label: post.title.rendered
                                        }))
                                        : []
                                }
                                onFilterValueChange={setSearchTerm}
                            />

                            <div className="selected-posts-list">
                                {selectedPosts.map(post => (
                                    <div key={post.id} className="selected-post-item">
                                        <span dangerouslySetInnerHTML={{ __html: post.title }} />
                                        <Button
                                            isSmall
                                            isDestructive
                                            onClick={() => removeSelectedPost(post.id)}
                                        >
                                            {__('Remove', 'fau-elemental')}
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        </>
                    )}
                </PanelBody>

                {selectionMode === 'auto' && (
                    <PanelBody title={__('Content Settings', 'fau-elemental')}>
                        <SelectControl
                            label={__('Content Type', 'fau-elemental')}
                            value={variant}
                            options={postTypeOptions}
                            onChange={(value) => setAttributes({ variant: value })}
                        />

                        {variant === 'post' && (
                            <SelectControl
                                label={__('Category', 'fau-elemental')}
                                value={selectedCategory}
                                options={categoryOptions}
                                onChange={(value) => setAttributes({ selectedCategory: parseInt(value) })}
                            />
                        )}

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
                )}
            </InspectorControls>
            
            <div {...blockProps}>
                <div className={`fau-teaser-grid ${displayStyle} layout-${teaserLayout}`}>
                    {!isLoading ? (
                        selectionMode === 'manual' ? (
                            // Display manually selected posts
                            selectedPosts.length > 0 ? (
                                selectedPosts.map((selectedPost) => {
                                    const post = items.find(item => item.id === selectedPost.id);
                                    return post ? (
                                        variant === 'post' 
                                            ? <PostTeaser key={post.id} post={post} grid={blockProps} />
                                            : <PageTeaser key={post.id} page={post} grid={blockProps} />
                                    ) : null;
                                })
                            ) : (
                                <p>{__('No posts selected', 'fau-elemental')}</p>
                            )
                        ) : (
                            // Display automatic posts
                            items && items.length > 0 ? (
                                items.map((item) => (
                                    variant === 'post' 
                                        ? <PostTeaser key={item.id} post={item} grid={blockProps} />
                                        : <PageTeaser key={item.id} page={item} grid={blockProps} />
                                ))
                            ) : (
                                <p>{__('No items found', 'fau-elemental')}</p>
                            )
                        )
                    ) : (
                        <Placeholder>
                            <Spinner />
                            <p>{__('Loading...', 'fau-elemental')}</p>
                        </Placeholder>
                    )}
                </div>

                {showPagination && totalPages > 1 && selectionMode === 'auto' && (
                    createPagination(currentPage, totalPages, (newPage) => 
                        setAttributes({ currentPage: newPage })
                    )
                )}
            </div>
        </>
    );
}
