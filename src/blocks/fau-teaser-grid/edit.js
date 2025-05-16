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
	ComboboxControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import './editor.scss';
import { useState, useEffect, useRef } from 'react';

import PostTeaser from './components/PostTeaser';
import PageTeaser from './components/PageTeaser';
import { createPagination, updateGridClasses } from './utils/helpers';

// Define teaser layout options
const TEASER_LAYOUTS = [
	{
		label: __( '1 Extra Large Teaser (1×XL)', 'fau-elemental' ),
		value: '1xl',
	},
	{ label: __( '2 Large Teasers (2×L)', 'fau-elemental' ), value: '2l' },
	{ label: __( '1 Large + 2 Small (L+2S)', 'fau-elemental' ), value: 'l2s' },
	{ label: __( '2 Small + 1 Large (2S+L)', 'fau-elemental' ), value: '2sl' },
	{ label: __( '3 Medium Teasers (3×M)', 'fau-elemental' ), value: '3m' },
	{
		label: __( '2 Small - Image Left (2S BLTR)', 'fau-elemental' ),
		value: '2s-left',
	},
	{
		label: __( '2 Small - Image Right (2S TLBR)', 'fau-elemental' ),
		value: '2s-right',
	},
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

	const gridRef = useRef( null );

	// Effect to update grid classes when display style or layout changes
	useEffect( () => {
		if ( gridRef.current ) {
			const grid = gridRef.current;
			// Clear all existing classes
			grid.className = '';
			// Add new classes
			updateGridClasses( grid, displayStyle, teaserLayout );
		}
	}, [ displayStyle, teaserLayout ] );

	// Add this new state for search
	const [ searchTerm, setSearchTerm ] = useState( '' );

	// Get post types and categories
	const { postTypes, categories, items, isLoading } = useSelect(
		( select ) => {
			const coreSelect = select( 'core' );
			const allPostTypes = coreSelect.getPostTypes();

			// Get all categories
			const allCategories =
				coreSelect.getEntityRecords( 'taxonomy', 'category', {
					per_page: -1,
				} ) || [];

			// Get all post types that are viewable (public)
			const availablePostTypes =
				allPostTypes?.filter(
					( type ) =>
						type.viewable &&
						type.slug !== 'attachment' &&
						type.slug !== 'wp_block'
				) || [];

			// Build query for current post type
			let query = {
				_embed: true,
				per_page: postsPerPage,
				page: currentPage,
				orderby: orderBy,
				order: order.toLowerCase(),
			};

			// Add category to query if selected
			if ( selectedCategory ) {
				query.categories = selectedCategory;
			}

			// Get posts
			const posts = coreSelect.getEntityRecords(
				'postType',
				variant,
				query
			);

			return {
				postTypes: availablePostTypes,
				categories: allCategories,
				items: Array.isArray( posts ) ? posts : [],
				isLoading: coreSelect.isResolving( 'getEntityRecords', [
					'postType',
					variant,
					query,
				] ),
			};
		},
		[ variant, postsPerPage, currentPage, selectedCategory, orderBy, order ]
	);

    // Use useEntityRecords to get total count efficiently
    const { totalItems } = useSelect((select) => {
        const { getEntityRecords } = select(coreStore);
        const countQuery = {
            per_page: 1,
            _fields: ['id'],
            ...(selectedCategory ? { categories: selectedCategory } : {})
        };
        const total = select('core').getEntityRecords('postType', variant, {
            ...countQuery,
            per_page: -1
        })?.length || 0;
        return {
            totalItems: total
        };
    }, [variant, selectedCategory]);

	// Calculate total pages based on totalItems
	const calculatedTotalPosts =
		totalPosts > 0 ? Math.min( totalPosts, totalItems ) : totalItems;
	const calculatedTotalPages = Math.ceil(
		calculatedTotalPosts / postsPerPage
	);

	// Convert post types to options
	const postTypeOptions = postTypes.map( ( type ) => ( {
		label: type.labels?.singular_name || type.name,
		value: type.slug,
	} ) );

	// Convert categories to options
	const categoryOptions = [
		{ label: __( 'All Categories', 'fau-elemental' ), value: 0 },
		...categories.map( ( category ) => ( {
			label: category.name,
			value: category.id,
		} ) ),
	];

	// Sorting options
	const sortingOptions = [
		{ label: __( 'Date', 'fau-elemental' ), value: 'date' },
		{ label: __( 'Title', 'fau-elemental' ), value: 'title' },
	];

	const orderOptions = [
		{ label: __( 'Ascending', 'fau-elemental' ), value: 'ASC' },
		{ label: __( 'Descending', 'fau-elemental' ), value: 'DESC' },
	];

	// Add this new select to get available posts
	const { availablePosts } = useSelect(
		( select ) => {
			if ( searchTerm ) {
				return {
					availablePosts: select( 'core' ).getEntityRecords(
						'postType',
						variant,
						{
							search: searchTerm,
							per_page: 20,
							_fields: [ 'id', 'title' ],
						}
					),
				};
			}
			return { availablePosts: [] };
		},
		[ searchTerm, variant ]
	);

	// Add this function to handle post selection
	const handlePostSelection = ( postId ) => {
		if ( ! postId ) return;

		const post = availablePosts.find( ( p ) => p.id === postId );
		if ( ! post ) return;

		const newSelectedPosts = [ ...selectedPosts ];
		if ( ! newSelectedPosts.some( ( p ) => p.id === post.id ) ) {
			newSelectedPosts.push( {
				id: post.id,
				title: post.title.rendered,
			} );
			setAttributes( { selectedPosts: newSelectedPosts } );
		}
	};

	// Add this function to remove selected posts
	const removeSelectedPost = ( postId ) => {
		const newSelectedPosts = selectedPosts.filter(
			( p ) => p.id !== postId
		);
		setAttributes( { selectedPosts: newSelectedPosts } );
	};

	const blockProps = useBlockProps( {
		className: `style-${ displayStyle }`,
	} );

	// Update display style
	const onDisplayStyleChange = ( newStyle ) => {
		setAttributes( { displayStyle: newStyle } );
	};

	// Update teaser layout
	const onTeaserLayoutChange = ( newLayout ) => {
		setAttributes( { teaserLayout: newLayout } );
	};

    return (
        <div {...blockProps} role="region" aria-label={__('Teaser Grid Block', 'fau-elemental')}>
            <InspectorControls>
                <PanelBody title={__('Display Settings', 'fau-elemental')}>
                    <ButtonGroup aria-label={__('Display style options', 'fau-elemental')}>
                        <Button
                            isPrimary={displayStyle === 'teaser-grid'}
                            onClick={() => onDisplayStyleChange('teaser-grid')}
                            aria-pressed={displayStyle === 'teaser-grid'}
                        >
                            {__('Teaser Grid', 'fau-elemental')}
                        </Button>
                        <Button
                            isPrimary={displayStyle === 'list-item'}
                            onClick={() => onDisplayStyleChange('list-item')}
                            aria-pressed={displayStyle === 'list-item'}
                        >
                            {__('List Item', 'fau-elemental')}
                        </Button>
                    </ButtonGroup>
                    
                    {displayStyle === 'teaser-grid' && (
                        <SelectControl
                            label={__('Teaser Layout', 'fau-elemental')}
                            value={teaserLayout}
                            options={TEASER_LAYOUTS}
                            onChange={(value) => onTeaserLayoutChange(value)}
                            aria-describedby="teaser-layout-description"
                            __nextHasNoMarginBottom={true}
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
                    />
                </PanelBody>

				<PanelBody title={ __( 'Selection Mode', 'fau-elemental' ) }>
					<ButtonGroup
						aria-label={ __(
							'Selection mode options',
							'fau-elemental'
						) }
					>
						<Button
							isPrimary={ selectionMode === 'auto' }
							onClick={ () => {
								setAttributes( {
									selectionMode: 'auto',
									selectedPosts: [],
								} );
							} }
							aria-pressed={ selectionMode === 'auto' }
						>
							{ __( 'Automatic', 'fau-elemental' ) }
						</Button>
						<Button
							isPrimary={ selectionMode === 'manual' }
							onClick={ () =>
								setAttributes( { selectionMode: 'manual' } )
							}
							aria-pressed={ selectionMode === 'manual' }
						>
							{ __( 'Manual Selection', 'fau-elemental' ) }
						</Button>
					</ButtonGroup>

					{ selectionMode === 'manual' && (
						<>
							<ComboboxControl
								label={ __(
									'Search and select posts',
									'fau-elemental'
								) }
								value=""
								onChange={ handlePostSelection }
								options={
									availablePosts
										? availablePosts.map( ( post ) => ( {
												value: post.id,
												label: post.title.rendered,
										  } ) )
										: []
								}
								onFilterValueChange={ setSearchTerm }
								aria-label={ __(
									'Search and select posts',
									'fau-elemental'
								) }
								aria-describedby="post-search-description"
							/>
							<p
								id="post-search-description"
								className="screen-reader-text"
							>
								{ __(
									'Type to search for posts. Use arrow keys to navigate and enter to select.',
									'fau-elemental'
								) }
							</p>

							<div
								className="selected-posts-list"
								role="list"
								aria-label={ __(
									'Selected posts',
									'fau-elemental'
								) }
							>
								{ selectedPosts.map( ( post ) => (
									<div
										key={ post.id }
										className="selected-post-item"
										role="listitem"
									>
										<span
											dangerouslySetInnerHTML={ {
												__html: post.title,
											} }
										/>
										<Button
											isSmall
											isDestructive
											onClick={ () =>
												removeSelectedPost( post.id )
											}
											aria-label={
												__(
													'Remove post',
													'fau-elemental'
												) +
												': ' +
												post.title
											}
										>
											{ __( 'Remove', 'fau-elemental' ) }
										</Button>
									</div>
								) ) }
							</div>
						</>
					) }
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
                        />

                        {variant === 'post' && categories.length > 0 && (
                            <SelectControl
                                label={__('Category', 'fau-elemental')}
                                value={selectedCategory}
                                options={categoryOptions}
                                onChange={(value) => setAttributes({ selectedCategory: parseInt(value), currentPage: 1 })}
                                help={__('Select a category to filter posts.', 'fau-elemental')}
                                __nextHasNoMarginBottom={true}
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
                        />

                        <SelectControl
                            label={__('Order', 'fau-elemental')}
                            value={order.toUpperCase()} 
                            options={orderOptions}
                            onChange={(value) => setAttributes({ order: value })}
                            __nextHasNoMarginBottom={true}
                        />
                    </PanelBody>
                )}

                {selectionMode === 'manual' && (
                    <PanelBody title={__('Manual Post Selection', 'fau-elemental')}>
                        <ComboboxControl
                            label={__('Search and Select Posts', 'fau-elemental')}
                            value={searchTerm}
                            onChange={setSearchTerm} // Update search term on input change
                            onFilterValueChange={setSearchTerm} // Also update on filter change for consistency
                            options={availablePosts?.map(post => ({ label: post.title.rendered, value: post.id })) || []}
                            onSelect={handlePostSelection} // Use onSelect to handle the selection
                            help={__('Type to search for posts and select them to add to the grid.', 'fau-elemental')}
                        />
                        {selectedPosts.length > 0 && (
                            <ul className="selected-posts-list">
                                {selectedPosts.map(post => (
                                    <li key={post.id}>
                                        {post.title}
                                        <Button 
                                            isSmall 
                                            isDestructive 
                                            onClick={() => removeSelectedPost(post.id)}
                                            style={{ marginLeft: '8px' }}
                                        >
                                            {__('Remove', 'fau-elemental')}
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        )}
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
                className={`fau-teaser-grid ${displayStyle} ${displayStyle === 'teaser-grid' ? `layout-${teaserLayout}` : ''}`}
                role="list"
                aria-label={__('Content grid', 'fau-elemental')}
            >
                {!isLoading ? (
                    selectionMode === 'manual' ? (
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
                            <p role="status">{__('No posts selected', 'fau-elemental')}</p>
                        )
                    ) : (
                        items && items.length > 0 ? (
                            items.map((item) => (
                                variant === 'post' 
                                    ? <PostTeaser key={item.id} post={item} grid={blockProps} />
                                    : <PageTeaser key={item.id} page={item} grid={blockProps} />
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

			{ showPagination &&
				calculatedTotalPages > 1 &&
				selectionMode === 'auto' && (
					<nav
						role="navigation"
						aria-label={ __( 'Pagination', 'fau-elemental' ) }
					>
						{ createPagination(
							currentPage,
							calculatedTotalPages,
							( newPage ) =>
								setAttributes( { currentPage: newPage } )
						) }
					</nav>
				) }
		</div>
	);
}
