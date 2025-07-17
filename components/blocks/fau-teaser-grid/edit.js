import { __, sprintf } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	Placeholder,
	Spinner,
	DropdownMenu,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';
import { useState, useEffect, useRef, useMemo } from '@wordpress/element';

import PostTeaser from './components/PostTeaser';
import PageTeaser from './components/PageTeaser';
import { updateGridClasses } from './utils/helpers';
import { DisplaySettings } from './components/editor/DisplaySettings';
import { SelectionMode } from './components/editor/SelectionMode';
import { ContentSettings } from './components/editor/ContentSettings';
import {
	usePostTypes,
	useCategories,
	usePosts,
	useAvailablePosts,
	useTotalItems,
} from './components/editor/useTeaserData';

// Add this helper function at the top level
const wrapTeaserItems = ( items, layout ) => {
	// Only wrap for l2s and 2sl layouts
	if ( ! [ 'l2s', '2sl' ].includes( layout ) ) {
		return items;
	}

	const wrappedItems = [];
	for ( let i = 0; i < items.length; i += 3 ) {
		const groupItems = items.slice( i, i + 3 );
		if ( groupItems.length > 0 ) {
			wrappedItems.push(
				<div key={ `teaser-group-${ i }` } className="teaser-group">
					{ groupItems }
				</div>
			);
		}
	}
	return wrappedItems;
};

// Generate pagination preview similar to render.php logic
const generatePaginationPreview = (
	currentPage,
	totalPages,
	paginationType
) => {
	if ( totalPages <= 1 ) {
		return null;
	}

	const pages = [];

	// Previous button
	if ( currentPage > 1 ) {
		pages.push(
			<span key="prev" className="page-number prev">
				<span className="pagination-icon pagination-icon-prev"></span>
			</span>
		);
	} else {
		pages.push(
			<span key="prev" className="page-number prev disabled">
				<span className="pagination-icon pagination-icon-prev"></span>
			</span>
		);
	}

	if ( paginationType === 'numbers' ) {
		if ( totalPages <= 7 ) {
			// Show all pages if 7 or fewer
			for ( let i = 1; i <= totalPages; i++ ) {
				if ( i === currentPage ) {
					pages.push(
						<span
							key={ i }
							className="page-number current"
							aria-current="page"
						>
							{ i }
						</span>
					);
				} else {
					pages.push(
						<span key={ i } className="page-number">
							{ i }
						</span>
					);
				}
			}
		} else {
			// Show first 3 ... last 3 pattern

			// First 3 pages
			for ( let i = 1; i <= 3; i++ ) {
				if ( i === currentPage ) {
					pages.push(
						<span
							key={ i }
							className="page-number current"
							aria-current="page"
						>
							{ i }
						</span>
					);
				} else {
					pages.push(
						<span key={ i } className="page-number">
							{ i }
						</span>
					);
				}
			}

			// Ellipsis
			if ( totalPages > 6 ) {
				pages.push(
					<span
						key="ellipsis"
						className="page-ellipsis"
						aria-hidden="true"
					>
						...
					</span>
				);
			}

			// Last 3 pages
			for ( let i = totalPages - 2; i <= totalPages; i++ ) {
				if ( i === currentPage ) {
					pages.push(
						<span
							key={ i }
							className="page-number current"
							aria-current="page"
						>
							{ i }
						</span>
					);
				} else {
					pages.push(
						<span key={ i } className="page-number">
							{ i }
						</span>
					);
				}
			}
		}
	}

	// Next button
	if ( currentPage < totalPages ) {
		pages.push(
			<span key="next" className="page-number next">
				<span className="pagination-icon pagination-icon-next"></span>
			</span>
		);
	} else {
		pages.push(
			<span key="next" className="page-number next disabled">
				<span className="pagination-icon pagination-icon-next"></span>
			</span>
		);
	}

	return pages;
};

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		displayStyle,
		variant,
		teaserLayout,
		postsPerPage,
		selectedCategory,
		currentPage,
		orderBy,
		order,
		selectedPosts,
		selectionMode,
		headingLevel,
		showPagination,
		paginationType,
		customBlockId,
	} = attributes;

	// Generate and set customBlockId if it doesn't exist
	useEffect( () => {
		if ( ! customBlockId ) {
			// Use clientId to create a unique block ID
			const newBlockId = `fau-teaser-grid-${ clientId.substring(
				0,
				8
			) }`;
			setAttributes( { customBlockId: newBlockId } );
		}
	}, [ customBlockId, clientId, setAttributes ] );

	const gridRef = useRef( null );
	const [ searchTerm, setSearchTerm ] = useState( '' );

	// Effect to update grid classes when display style or layout changes
	useEffect( () => {
		if ( gridRef.current ) {
			const grid = gridRef.current;
			grid.className = '';
			updateGridClasses( grid, displayStyle, teaserLayout );
		}
	}, [ displayStyle, teaserLayout ] );

	// Data hooks
	const postTypes = usePostTypes();
	const categories = useCategories();

	// Memoize query parameters
	const queryParams = useMemo(
		() => ( {
			_embed: true,
			per_page: postsPerPage,
			page: currentPage,
			orderby: orderBy,
			order: order.toLowerCase(),
			...( selectedCategory ? { categories: selectedCategory } : {} ),
		} ),
		[ postsPerPage, currentPage, orderBy, order, selectedCategory ]
	);

	const { items, isLoading } = usePosts( variant, queryParams );
	const availablePosts = useAvailablePosts( searchTerm, variant );
	const { totalItems } = useTotalItems( variant, selectedCategory );

	// Memoize options
	const postTypeOptions = useMemo(
		() =>
			postTypes.map( ( type ) => ( {
				label: type.labels?.singular_name || type.name,
				value: type.slug,
			} ) ),
		[ postTypes ]
	);

	const categoryOptions = useMemo(
		() => [
			{ label: __( 'All Categories', 'fau-elemental' ), value: 0 },
			...categories.map( ( category ) => ( {
				label: category.name,
				value: category.id,
			} ) ),
		],
		[ categories ]
	);

	// Calculate total pages for pagination preview
	const calculatedTotalPosts =
		selectionMode === 'manual' ? selectedPosts.length : totalItems; // Use actual total items for pagination calculation
	const calculatedTotalPages = Math.max(
		1,
		Math.ceil( calculatedTotalPosts / postsPerPage )
	);

	// Post selection handlers
	const handlePostSelection = ( postId ) => {
		if ( ! postId ) {
			return;
		}

		const post = availablePosts.find( ( p ) => p.id === postId );
		if ( ! post ) {
			return;
		}

		const newSelectedPosts = [ ...selectedPosts ];
		if ( ! newSelectedPosts.some( ( p ) => p.id === post.id ) ) {
			newSelectedPosts.push( {
				id: post.id,
				title: post.title.rendered,
			} );
			setAttributes( { selectedPosts: newSelectedPosts } );
		}
	};

	const removeSelectedPost = ( postId ) => {
		const newSelectedPosts = selectedPosts.filter(
			( p ) => p.id !== postId
		);
		setAttributes( { selectedPosts: newSelectedPosts } );
	};

	const blockProps = useBlockProps( {
		className: `style-${ displayStyle }`,
	} );

	return (
		<div
			{ ...blockProps }
			role="region"
			aria-label={ __( 'Teaser Grid Block', 'fau-elemental' ) }
		>
			<InspectorControls>
				<DisplaySettings
					displayStyle={ displayStyle }
					teaserLayout={ teaserLayout }
					headingLevel={ headingLevel }
					onDisplayStyleChange={ ( newStyle ) =>
						setAttributes( { displayStyle: newStyle } )
					}
					onTeaserLayoutChange={ ( newLayout ) =>
						setAttributes( { teaserLayout: newLayout } )
					}
					setAttributes={ setAttributes }
				/>

				<SelectionMode
					selectionMode={ selectionMode }
					setAttributes={ setAttributes }
					selectedPosts={ selectedPosts }
					availablePosts={ availablePosts }
					searchTerm={ searchTerm }
					setSearchTerm={ setSearchTerm }
					handlePostSelection={ handlePostSelection }
					removeSelectedPost={ removeSelectedPost }
				/>

				{ selectionMode === 'auto' && (
					<ContentSettings
						variant={ variant }
						selectedCategory={ selectedCategory }
						postsPerPage={ postsPerPage }
						orderBy={ orderBy }
						order={ order }
						setAttributes={ setAttributes }
						postTypeOptions={ postTypeOptions }
						categoryOptions={ categoryOptions }
						categories={ categories }
					/>
				) }

				<PanelBody
					title={ __( 'Pagination Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show Pagination', 'fau-elemental' ) }
						checked={ showPagination }
						onChange={ ( value ) =>
							setAttributes( { showPagination: value } )
						}
					/>

					{ showPagination && (
						<SelectControl
							label={ __( 'Pagination Type', 'fau-elemental' ) }
							value={ paginationType }
							options={ [
								{
									label: __(
										'Page Numbers',
										'fau-elemental'
									),
									value: 'numbers',
								},
								{
									label: __(
										'Load More Button',
										'fau-elemental'
									),
									value: 'load-more',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( { paginationType: value } )
							}
						/>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Accessibility', 'fau-elemental' ) }>
					<p>
						{ __(
							'To ensure good accessibility and SEO, please make sure that there is only one H1 heading on the page. If you already have an H1 heading, use a different heading level for the teasers in this block.',
							'fau-elemental'
						) }
					</p>
				</PanelBody>
			</InspectorControls>

			<BlockControls group="block">
				<DropdownMenu
					icon="heading"
					label={ __( 'Change heading level', 'fau-elemental' ) }
					controls={ [
						{
							title: __( 'Heading 2', 'fau-elemental' ),
							onClick: () =>
								setAttributes( { headingLevel: 'h2' } ),
							isActive: headingLevel === 'h2',
						},
						{
							title: __( 'Heading 3', 'fau-elemental' ),
							onClick: () =>
								setAttributes( { headingLevel: 'h3' } ),
							isActive: headingLevel === 'h3',
						},
						{
							title: __( 'Heading 4', 'fau-elemental' ),
							onClick: () =>
								setAttributes( { headingLevel: 'h4' } ),
							isActive: headingLevel === 'h4',
						},
						{
							title: __( 'Heading 5', 'fau-elemental' ),
							onClick: () =>
								setAttributes( { headingLevel: 'h5' } ),
							isActive: headingLevel === 'h5',
						},
						{
							title: __( 'Heading 6', 'fau-elemental' ),
							onClick: () =>
								setAttributes( { headingLevel: 'h6' } ),
							isActive: headingLevel === 'h6',
						},
					] }
				/>
			</BlockControls>

			<div className="fau-teaser-grid-preview">
				<div className="teaser-grid-header">
					<h3 className="grid-title">
						{ __( 'FAU Teaser Grid', 'fau-elemental' ) }
					</h3>
					<div className="grid-info">
						<span className="item-count">
							{ sprintf(
								/* translators: %1$d: number of visible items, %2$d: total number of items */
								__(
									'Showing %1$d of %2$d items',
									'fau-elemental'
								),
								selectionMode === 'manual'
									? selectedPosts.length
									: items.length,
								totalItems
							) }
						</span>
					</div>
				</div>

				<div
					ref={ gridRef }
					className={ `fau-teaser-grid ${ displayStyle } ${
						displayStyle === 'teaser-grid'
							? `layout-${ teaserLayout }`
							: displayStyle === 'mini-list'
							? 'style-mini-list'
							: ''
					}` }
					role="list"
					aria-label={ __( 'Content grid', 'fau-elemental' ) }
				>
					{ ! isLoading ? (
						selectionMode === 'manual' ? (
							selectedPosts.length > 0 ? (
								wrapTeaserItems(
									selectedPosts.map( ( selectedPost ) => {
										const fullPost = items.find(
											( item ) =>
												item.id === selectedPost.id
										);

										const postData = fullPost || {
											id: selectedPost.id,
											title: {
												rendered: selectedPost.title,
											},
											excerpt: {
												rendered: '',
											},
											_embedded: {
												'wp:featuredmedia': [],
												'wp:term': [],
											},
										};

										return variant === 'post' ? (
											<PostTeaser
												key={ postData.id }
												post={ postData }
												headingLevel={ headingLevel }
											/>
										) : (
											<PageTeaser
												key={ postData.id }
												page={ postData }
												headingLevel={ headingLevel }
											/>
										);
									} ),
									teaserLayout
								)
							) : (
								<p role="status">
									{ __(
										'No posts selected',
										'fau-elemental'
									) }
								</p>
							)
						) : items && items.length > 0 ? (
							wrapTeaserItems(
								items.map( ( item ) =>
									variant === 'post' ? (
										<PostTeaser
											key={ item.id }
											post={ item }
											headingLevel={ headingLevel }
										/>
									) : (
										<PageTeaser
											key={ item.id }
											page={ item }
											headingLevel={ headingLevel }
										/>
									)
								),
								teaserLayout
							)
						) : (
							<p role="status">
								{ __( 'No items found', 'fau-elemental' ) }
							</p>
						)
					) : (
						<Placeholder>
							<Spinner />
							<p role="status">
								{ __( 'Loading…', 'fau-elemental' ) }
							</p>
						</Placeholder>
					) }

					{ calculatedTotalPages > 1 && (
						<div
							className={ `pagination-preview ${
								! showPagination ? 'pagination-disabled' : ''
							}` }
						>
							{ ! showPagination && (
								<div className="pagination-status-notice">
									<small>
										{ __(
											'Pagination disabled - enable in block settings',
											'fau-elemental'
										) }
									</small>
								</div>
							) }
							<nav
								className="fau-pagination"
								role="navigation"
								aria-label={ __(
									'Posts pagination',
									'fau-elemental'
								) }
							>
								<div className="pagination-wrapper">
									{ paginationType === 'numbers' && (
										<>
											{ generatePaginationPreview(
												currentPage,
												calculatedTotalPages,
												paginationType
											) }
										</>
									) }
									{ paginationType === 'load-more' && (
										<div className="wp-block-button is-style-secondary">
											<button className="wp-block-button__link load-more-button">
												{ __(
													'Load More',
													'fau-elemental'
												) }
											</button>
										</div>
									) }
								</div>
							</nav>
						</div>
					) }
				</div>
			</div>
		</div>
	);
}
