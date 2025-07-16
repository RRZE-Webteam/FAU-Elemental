import { __ } from '@wordpress/i18n';
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
import { useSelect } from '@wordpress/data';

import PostTeaser from './components/PostTeaser';
import PageTeaser from './components/PageTeaser';
import { createPagination, updateGridClasses } from './utils/helpers';
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

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		displayStyle,
		variant,
		teaserLayout,
		postsPerPage,
		selectedCategory,
		currentPage,
		totalPosts,
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

	const calculatedTotalPosts = useMemo( () => {
		if ( selectionMode === 'manual' ) {
			return selectedPosts.length;
		}
		return totalItems;
	}, [ selectionMode, selectedPosts, totalItems ] );

	const blockProps = useBlockProps( {
		className: `fau-teaser-grid-editor ${ displayStyle }`,
	} );

	if ( isLoading ) {
		return (
			<div { ...blockProps }>
				<Placeholder label={ __( 'Loading...', 'fau-elemental' ) }>
					<Spinner />
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<DisplaySettings
					displayStyle={ displayStyle }
					teaserLayout={ teaserLayout }
					setAttributes={ setAttributes }
				/>

				<SelectionMode
					selectionMode={ selectionMode }
					setAttributes={ setAttributes }
				/>

				{ selectionMode === 'auto' && (
					<ContentSettings
						variant={ variant }
						postsPerPage={ postsPerPage }
						selectedCategory={ selectedCategory }
						orderBy={ orderBy }
						order={ order }
						setAttributes={ setAttributes }
						postTypeOptions={ postTypeOptions }
						categoryOptions={ categoryOptions }
						categories={ categories }
					/>
				) }

				{ selectionMode === 'auto' && (
					<PanelBody
						title={ __( 'Pagination Settings', 'fau-elemental' ) }
					>
						<ToggleControl
							label={ __(
								'Show Pagination',
								'fau-elemental'
							) }
							checked={ showPagination }
							onChange={ ( value ) =>
								setAttributes( { showPagination: value } )
							}
							help={ __(
								'Display pagination controls below the grid.',
								'fau-elemental'
							) }
						/>

						{ showPagination && (
							<SelectControl
								label={ __(
									'Pagination Type',
									'fau-elemental'
								) }
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
								help={ __(
									'Choose how pagination is displayed.',
									'fau-elemental'
								) }
							/>
						) }
					</PanelBody>
				) }

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
										( item ) => item.id === selectedPost.id
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
											size={ selectedPost.size }
										/>
									) : (
										<PageTeaser
											key={ postData.id }
											page={ postData }
											headingLevel={ headingLevel }
											size={ selectedPost.size }
										/>
									);
								} ),
								teaserLayout
							)
						) : (
							<p>{ __( 'No posts selected', 'fau-elemental' ) }</p>
						)
					) : (
						<>
							{ wrapTeaserItems(
								items.map( ( item ) => {
									return variant === 'post' ? (
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
									);
								} ),
								teaserLayout
							) }
							{ showPagination && (
								<div className="pagination-preview">
								
									<div className="pagination-preview-controls">
										{ paginationType === 'numbers' && (
											<>
												<span className="page-number prev disabled">
													{ __( 'Previous', 'fau-elemental' ) }
												</span>
												<span className="page-number current">1</span>
												<span className="page-number">2</span>
												<span className="page-number">3</span>
												<span className="page-ellipsis">...</span>
												<span className="page-number">8</span>
												<span className="page-number">9</span>
												<span className="page-number">10</span>
												<span className="page-number next">
													{ __( 'Next', 'fau-elemental' ) }
												</span>
											</>
										) }
										{ paginationType === 'simple' && (
											<>
												<span className="page-number prev disabled">
													{ __( 'Previous', 'fau-elemental' ) }
												</span>
												<span className="page-number next">
													{ __( 'Next', 'fau-elemental' ) }
												</span>
											</>
										) }
										{ paginationType === 'load-more' && (
											<div className="wp-block-button">
												<button className="wp-block-button__link load-more-button">
													{ __( 'Load More', 'fau-elemental' ) }
												</button>
											</div>
										) }
									</div>
								</div>
							) }
						</>
					)
				) : (
					<Placeholder label={ __( 'Loading...', 'fau-elemental' ) }>
						<Spinner />
					</Placeholder>
				) }
			</div>
		</div>
	);
}
