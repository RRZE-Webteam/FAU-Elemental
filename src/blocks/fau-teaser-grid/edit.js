import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    RangeControl, 
    ToggleControl,
    SelectControl,
    Placeholder,
    Spinner,
    Button,
    ComboboxControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption
} from '@wordpress/components';
import { useSelect, createSelector } from '@wordpress/data';
import './editor.scss';
import { useState, useEffect, useRef, useMemo } from 'react';

import PostTeaser from './components/PostTeaser';
import PageTeaser from './components/PageTeaser';
import { createPagination, updateGridClasses } from './utils/helpers';

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
        selectionMode,
        headingLevel
    } = attributes;

    const gridRef = useRef(null);

    // Effect to update grid classes when display style or layout changes
    useEffect(() => {
        if (gridRef.current) {
            const grid = gridRef.current;
            // Clear all existing classes
            grid.className = '';
            // Add new classes
            updateGridClasses(grid, displayStyle, teaserLayout);
        }
    }, [displayStyle, teaserLayout]);

    // Add this new state for search
    const [searchTerm, setSearchTerm] = useState('');

    // Get post types with proper memoization
    const postTypes = useSelect((select) => {
        const coreSelect = select('core');
        const allPostTypes = coreSelect.getPostTypes();
        return allPostTypes?.filter(type => 
            type.viewable && 
            type.slug !== 'attachment' &&
            type.slug !== 'wp_block'
        ) || [];
    }, []);

    // Get categories with proper memoization
    const categories = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'category', { per_page: -1 }) || [];
    }, []);

    // Memoize query parameters for fetching posts to ensure stable reference
    const queryParams = useMemo(() => ({
        _embed: true,
        per_page: postsPerPage,
        page: currentPage,
        orderby: orderBy,
        order: order.toLowerCase(),
        ...(selectedCategory ? { categories: selectedCategory } : {})
    }), [postsPerPage, currentPage, orderBy, order, selectedCategory]);

    // Create a stable selector for posts
    const getPosts = createSelector(
        // Computation function:
        // Fetches raw posts and maps them to a consistent structure.
        // This function is only re-run if the dependencies (from the second function) change.
        (select, variant, query) => {
            const rawPosts = select('core').getEntityRecords('postType', variant, query);
            if (!Array.isArray(rawPosts)) return [];
            
            return rawPosts.map(post => ({
                id: post.id,
                title: post.title, 
                excerpt: post.excerpt, 
                date: post.date,
                _embedded: post._embedded ? {
                    'wp:featuredmedia': post._embedded['wp:featuredmedia'] || [],
                    'wp:term': post._embedded['wp:term'] || []
                } : {
                    'wp:featuredmedia': [],
                    'wp:term': []
                }
            }));
        },
        // Dependencies function:
        // Derives stable dependencies (like a string of IDs) from the raw data.
        // If these stable dependencies don't change, the computation function above isn't re-run.
        (select, variant, query) => {
            const rawPosts = select('core').getEntityRecords('postType', variant, query);
            // Use a string of IDs as a more stable dependency than the rawPosts array reference
            const postIdsString = Array.isArray(rawPosts) ? rawPosts.map(p => p.id).join(',') : '';
            const isResolving = select('core').isResolving('getEntityRecords', ['postType', variant, query]);
            // query is the stable queryParams object from useMemo
            return [postIdsString, isResolving, variant, query]; 
        }
    );

    // Get posts with proper memoization
    const { items, isLoading } = useSelect((select) => {
        // Use the memoized queryParams
        const posts = getPosts(select, variant, queryParams);
        // Pass the same stable queryParams to isResolving
        const isResolving = select('core').isResolving('getEntityRecords', ['postType', variant, queryParams]);

        return {
            items: posts,
            isLoading: isResolving
        };
    }, [variant, queryParams, getPosts]); // Dependencies for useSelect

    // Get available posts for search with proper memoization
    const availablePosts = useSelect((select) => {
        if (!searchTerm || !variant) return [];
        const posts = select('core').getEntityRecords('postType', variant, {
            search: searchTerm,
            per_page: 20,
            _fields: ['id', 'title'],
            _embed: true
        }) || [];
        
        // Create stable references for each post
        return posts.map(post => ({
            id: post.id,
            title: {
                rendered: post.title.rendered
            }
        }));
    }, [searchTerm, variant]);

    // Memoize the total items count with stable reference
    const { totalItems } = useSelect((select) => {
        if (!variant) return { totalItems: 0 };
        
        const countQuery = {
            per_page: 1,
            _fields: ['id'],
            ...(selectedCategory ? { categories: selectedCategory } : {})
        };
        
        const posts = select('core').getEntityRecords('postType', variant, {
            ...countQuery,
            per_page: -1
        });
        
        // Create a stable reference for the total count
        return { 
            totalItems: Array.isArray(posts) ? posts.length : 0 
        };
    }, [variant, selectedCategory]);

    // Memoize the post type options
    const postTypeOptions = useMemo(() => 
        postTypes.map(type => ({
            label: type.labels?.singular_name || type.name,
            value: type.slug
        })), [postTypes]);

    // Memoize the category options
    const categoryOptions = useMemo(() => [
        { label: __('All Categories', 'fau-elemental'), value: 0 },
        ...categories.map(category => ({
            label: category.name,
            value: category.id
        }))
    ], [categories]);

    // Sorting options
    const sortingOptions = [
        { label: __('Date', 'fau-elemental'), value: 'date' },
        { label: __('Title', 'fau-elemental'), value: 'title' }
    ];

    const orderOptions = [
        { label: __('Ascending', 'fau-elemental'), value: 'ASC' },
        { label: __('Descending', 'fau-elemental'), value: 'DESC' }
    ];

    // Calculate total pages based on totalItems
    const calculatedTotalPosts = totalPosts > 0 ? Math.min(totalPosts, totalItems) : totalItems;
    const calculatedTotalPages = Math.ceil(calculatedTotalPosts / postsPerPage);

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

    // Update display style
    const onDisplayStyleChange = (newStyle) => {
        setAttributes({ displayStyle: newStyle });
    };

    // Update teaser layout
    const onTeaserLayoutChange = (newLayout) => {
        setAttributes({ teaserLayout: newLayout });
    };

    return (
        <div {...blockProps} role="region" aria-label={__('Teaser Grid Block', 'fau-elemental')}>
            <InspectorControls>
                <PanelBody title={__('Display Settings', 'fau-elemental')}>
                    <ToggleGroupControl
                        label={__('Display style options', 'fau-elemental')}
                        value={displayStyle}
                        onChange={onDisplayStyleChange}
                        isBlock
                        __next40pxDefaultSize={true}
                        __nextHasNoMarginBottom={true}
                    >
                        <ToggleGroupControlOption 
                            value="teaser-grid" 
                            label={__('Teaser Grid', 'fau-elemental')} 
                        />
                        <ToggleGroupControlOption 
                            value="mini-list" 
                            label={__('Mini List', 'fau-elemental')} 
                        />
                    </ToggleGroupControl>
                    
                    {displayStyle === 'teaser-grid' && (
                        <SelectControl
                            label={__('Teaser Layout', 'fau-elemental')}
                            value={teaserLayout}
                            options={TEASER_LAYOUTS}
                            onChange={(value) => onTeaserLayoutChange(value)}
                            aria-describedby="teaser-layout-description"
                            __nextHasNoMarginBottom={true}
                            __next40pxDefaultSize={true}
                        />
                    )}

                    <SelectControl
                        label={__('Heading Level', 'fau-elemental')}
                        value={headingLevel || 'h4'}
                        options={[
                            { label: 'H1', value: 'h1' },
                            { label: 'H2', value: 'h2' },
                            { label: 'H3', value: 'h3' },
                            { label: 'H4', value: 'h4' },
                            { label: 'H5', value: 'h5' },
                            { label: 'H6', value: 'h6' },
                        ]}
                        onChange={(headingLevel) => setAttributes({ headingLevel })}
                        __nextHasNoMarginBottom={true}
                        __next40pxDefaultSize={true}
                    />
                </PanelBody>

                <PanelBody title={__('Selection Mode', 'fau-elemental')}>
                    <ToggleGroupControl
                        label={__('Selection mode options', 'fau-elemental')}
                        value={selectionMode}
                        onChange={(value) => {
                            setAttributes({ 
                                selectionMode: value,
                                ...(value === 'auto' ? { selectedPosts: [] } : {})
                            });
                        }}
                        isBlock
                        __next40pxDefaultSize={true}
                        __nextHasNoMarginBottom={true}
                    >
                        <ToggleGroupControlOption
                            value="auto"
                            label={__('Automatic', 'fau-elemental')}
                        />
                        <ToggleGroupControlOption
                            value="manual"
                            label={__('Manual Selection', 'fau-elemental')}
                        />
                    </ToggleGroupControl>

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
                                aria-label={__('Search and select posts', 'fau-elemental')}
                                aria-describedby="post-search-description"
                                __next40pxDefaultSize={true}
                                __nextHasNoMarginBottom={true}
                            />
                            <p id="post-search-description" className="screen-reader-text">
                                {__('Type to search for posts. Use arrow keys to navigate and enter to select.', 'fau-elemental')}
                            </p>

                            <div 
                                className="selected-posts-list" 
                                role="list" 
                                aria-label={__('Selected posts', 'fau-elemental')}
                            >
                                {selectedPosts.map(post => (
                                    <div 
                                        key={post.id} 
                                        className="selected-post-item" 
                                        role="listitem"
                                    >
                                        <span dangerouslySetInnerHTML={{ __html: post.title }} />
                                        <Button
                                            isSmall
                                            isDestructive
                                            onClick={() => removeSelectedPost(post.id)}
                                            aria-label={__('Remove post', 'fau-elemental') + ': ' + post.title}
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
                            onChange={(value) => setAttributes({ variant: value, selectedCategory: 0, currentPage: 1 })}
                            help={__('Select the type of content to display.', 'fau-elemental')}
                            __nextHasNoMarginBottom={true}
                            __next40pxDefaultSize={true}
                        />

                        {variant === 'post' && categories.length > 0 && (
                            <SelectControl
                                label={__('Category', 'fau-elemental')}
                                value={selectedCategory}
                                options={categoryOptions}
                                onChange={(value) => setAttributes({ selectedCategory: parseInt(value), currentPage: 1 })}
                                help={__('Select a category to filter posts.', 'fau-elemental')}
                                __nextHasNoMarginBottom={true}
                                __next40pxDefaultSize={true}
                            />
                        )}

                        <RangeControl
                            label={__('Posts Per Page', 'fau-elemental')}
                            value={postsPerPage}
                            onChange={(value) => setAttributes({ postsPerPage: value, currentPage: 1 })}
                            min={1}
                            max={12}
                            help={__('Set the maximum number of posts to display per page.', 'fau-elemental')}
                            __nextHasNoMarginBottom={true}
                            __next40pxDefaultSize={true}
                        />

                        <ToggleControl
                            label={__('Show Pagination', 'fau-elemental')}
                            checked={showPagination}
                            onChange={(value) => setAttributes({ showPagination: value })}
                            help={__('Toggle to show or hide pagination.', 'fau-elemental')}
                            __nextHasNoMarginBottom={true}
                        />

                        <SelectControl
                            label={__('Order By', 'fau-elemental')}
                            value={orderBy}
                            options={sortingOptions}
                            onChange={(value) => setAttributes({ orderBy: value })}
                            __nextHasNoMarginBottom={true}
                            __next40pxDefaultSize={true}
                        />

                        <SelectControl
                            label={__('Order', 'fau-elemental')}
                            value={order.toUpperCase()} 
                            options={orderOptions}
                            onChange={(value) => setAttributes({ order: value })}
                            __nextHasNoMarginBottom={true}
                            __next40pxDefaultSize={true}
                        />
                    </PanelBody>
                )}

                <PanelBody title={__('Accessibility', 'fau-elemental')}>
                    <p>
                        {__(
                            'To ensure good accessibility and SEO, please make sure that there is only one H1 heading on the page. If you already have an H1 heading, use a different heading level for the teasers in this block.',
                            'fau-elemental'
                        )}
                    </p>
                </PanelBody>

            </InspectorControls>
            
            <div 
                ref={gridRef} 
                className={`fau-teaser-grid ${displayStyle} ${displayStyle === 'teaser-grid' ? `layout-${teaserLayout}` : displayStyle === 'mini-list' ? 'style-mini-list' : ''}`}
                role="list"
                aria-label={__('Content grid', 'fau-elemental')}
            >
                {!isLoading ? (
                    selectionMode === 'manual' ? (
                        selectedPosts.length > 0 ? (
                            selectedPosts.map((selectedPost) => {
                                // Find the full post data if available
                                const fullPost = items.find(item => item.id === selectedPost.id);
                                
                                // If we have full data, use it, otherwise create a minimal version
                                const postData = fullPost || {
                                    id: selectedPost.id,
                                    title: {
                                        rendered: selectedPost.title
                                    },
                                    excerpt: {
                                        rendered: ''
                                    },
                                    _embedded: {
                                        'wp:featuredmedia': [],
                                        'wp:term': []
                                    }
                                };
                                
                                return variant === 'post' 
                                    ? <PostTeaser key={postData.id} post={postData} headingLevel={headingLevel} />
                                    : <PageTeaser key={postData.id} page={postData} headingLevel={headingLevel} />
                            })
                        ) : (
                            <p role="status">{__('No posts selected', 'fau-elemental')}</p>
                        )
                    ) : (
                        items && items.length > 0 ? (
                            items.map((item) => (
                                variant === 'post' 
                                    ? <PostTeaser key={item.id} post={item} headingLevel={headingLevel} />
                                    : <PageTeaser key={item.id} page={item} headingLevel={headingLevel} />
                            ))
                        ) : (
                            <p role="status">{__('No items found', 'fau-elemental')}</p>
                        )
                    )
                ) : (
                    <Placeholder>
                        <Spinner />
                        <p role="status">{__('Loading...', 'fau-elemental')}</p>
                    </Placeholder>
                )}
            </div>

            {showPagination && calculatedTotalPages > 1 && selectionMode === 'auto' && (
                <nav 
                    role="navigation" 
                    aria-label={__('Pagination', 'fau-elemental')}
                >
                    {createPagination(currentPage, calculatedTotalPages, (newPage) => 
                        setAttributes({ currentPage: newPage })
                    )}
                </nav>
            )}
        </div>
    );
}
