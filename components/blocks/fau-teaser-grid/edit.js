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
	const [ searchTerm ] = useState( '' );

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

	// Calculate visible items based on pagination
	const visibleItems = useMemo( () => {
		if ( selectionMode === 'manual' ) {
			return selectedPosts.slice( 0, postsPerPage );
		}
		return items;
	}, [ items, selectedPosts, selectionMode, postsPerPage ] );

	const blockProps = useBlockProps( {
		className: 'fau-teaser-grid-editor',
	} );

	if ( isLoading ) {
		return (
			<div { ...blockProps }>
				<Placeholder
					icon="grid-view"
					label={ __( 'FAU Teaser Grid', 'fau-elemental' ) }
				>
					<Spinner />
					<p>{ __( 'Loading content…', 'fau-elemental' ) }</p>
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<ContentSettings
					attributes={ attributes }
					setAttributes={ setAttributes }
					postTypeOptions={ postTypeOptions }
					categoryOptions={ categoryOptions }
				/>
				<DisplaySettings
					attributes={ attributes }
					setAttributes={ setAttributes }
				/>
				<SelectionMode
					attributes={ attributes }
					setAttributes={ setAttributes }
					searchTerm={ searchTerm }
					variant={ variant }
				/>

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
			</InspectorControls>

			<BlockControls>
				<DropdownMenu
					icon="admin-settings"
					label={ __( 'Settings', 'fau-elemental' ) }
				>
					{ ( { onClose } ) => (
						<div className="fau-teaser-grid-settings-dropdown">
							<p>{ __( 'Quick settings', 'fau-elemental' ) }</p>
							<button
								className="button"
								onClick={ () => {
									setAttributes( {
										displayStyle: 'teaser-grid',
										teaserLayout: '3m',
									} );
									onClose();
								} }
							>
								{ __( 'Reset to default', 'fau-elemental' ) }
							</button>
						</div>
					) }
				</DropdownMenu>
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
								visibleItems.length,
								totalItems
							) }
						</span>
					</div>
				</div>

				<div
					ref={ gridRef }
					className={ `fau-teaser-grid ${ displayStyle }` }
					data-layout={ teaserLayout }
				>
					{ visibleItems.length > 0 ? (
						<>
							{ wrapTeaserItems(
								visibleItems.map( ( item ) => {
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
													{ __(
														'Previous',
														'fau-elemental'
													) }
												</span>
												<span className="page-number current">
													1
												</span>
												<span className="page-number">
													2
												</span>
												<span className="page-number">
													3
												</span>
												<span className="page-ellipsis">
													…
												</span>
												<span className="page-number">
													8
												</span>
												<span className="page-number">
													9
												</span>
												<span className="page-number">
													10
												</span>
												<span className="page-number next">
													{ __(
														'Next',
														'fau-elemental'
													) }
												</span>
											</>
										) }
										{ paginationType === 'simple' && (
											<>
												<span className="page-number prev disabled">
													{ __(
														'Previous',
														'fau-elemental'
													) }
												</span>
												<span className="page-number next">
													{ __(
														'Next',
														'fau-elemental'
													) }
												</span>
											</>
										) }
										{ paginationType === 'load-more' && (
											<div className="wp-block-button">
												<button className="wp-block-button__link load-more-button">
													{ __(
														'Load More',
														'fau-elemental'
													) }
												</button>
											</div>
										) }
									</div>
								</div>
							) }
						</>
					) : (
						<Placeholder
							icon="grid-view"
							label={ __( 'No items found', 'fau-elemental' ) }
						>
							<p>
								{ __(
									'No items match your current selection. Try adjusting your filters or add some content.',
									'fau-elemental'
								) }
							</p>
						</Placeholder>
					) }
				</div>
			</div>
		</div>
	);
}
