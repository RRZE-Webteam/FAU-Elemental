import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { variant, currentPage, customBlockId, gridBlockId } = attributes;
	const blockProps = useBlockProps();

	// Generate and set customBlockId if it doesn't exist
	useEffect( () => {
		if ( ! customBlockId ) {
			// Use clientId to create a unique block ID
			const newBlockId = `fau-pagination-${ clientId.substring( 0, 8 ) }`;
			setAttributes( { customBlockId: newBlockId } );
		}
	}, [ customBlockId, clientId, setAttributes ] );

	// Detect teaser grid block - look for the closest or most recent teaser grid
	const { nearbyGrid, hasFilters } = useSelect(
		( select ) => {
			const { getBlocks, getBlockHierarchyRootClientId } =
				select( 'core/block-editor' );
			const allBlocks = getBlocks();

			// Find all teaser grid blocks
			const gridBlocks = allBlocks.filter(
				( block ) => block.name === 'fau-elemental/fau-teaser-grid'
			);

			// Find filter blocks
			const filterBlocks = allBlocks.filter(
				( block ) => block.name === 'fau-elemental/fau-list-filters'
			);

			if ( gridBlocks.length === 0 ) {
				return {
					nearbyGrid: null,
					hasFilters: filterBlocks.length > 0,
				};
			}

			// If there's only one teaser grid, use it
			if ( gridBlocks.length === 1 ) {
				return {
					nearbyGrid: gridBlocks[ 0 ],
					hasFilters: filterBlocks.length > 0,
				};
			}

			// If there are multiple teaser grids, try to find the one in the same container
			// or the most recently created one
			const rootClientId = getBlockHierarchyRootClientId( clientId );
			const sameRootBlocks = gridBlocks.filter(
				( block ) =>
					getBlockHierarchyRootClientId( block.clientId ) ===
					rootClientId
			);

			if ( sameRootBlocks.length > 0 ) {
				// Return the last one in the same container
				return {
					nearbyGrid: sameRootBlocks[ sameRootBlocks.length - 1 ],
					hasFilters: filterBlocks.length > 0,
				};
			}

			// Fallback: return the last teaser grid block
			return {
				nearbyGrid: gridBlocks[ gridBlocks.length - 1 ],
				hasFilters: filterBlocks.length > 0,
			};
		},
		[ clientId ]
	);

	// Update grid block ID when detected
	useEffect( () => {
		if (
			nearbyGrid &&
			nearbyGrid.attributes.customBlockId &&
			gridBlockId !== nearbyGrid.attributes.customBlockId
		) {
			setAttributes( {
				gridBlockId: nearbyGrid.attributes.customBlockId,
			} );
		}
	}, [ nearbyGrid, gridBlockId, setAttributes ] );

	// Calculate pagination display values
	const gridPostsPerPage = nearbyGrid?.attributes?.postsPerPage || 6;
	const gridTotalItems = nearbyGrid?.attributes?.totalPosts || 10; // Mock data for preview
	const calculatedTotalPages = Math.ceil( gridTotalItems / gridPostsPerPage );

	// If no connected grid, show a placeholder
	if ( ! nearbyGrid ) {
		return (
			<>
				<InspectorControls>
					<PanelBody
						title={ __( 'Pagination Settings', 'fau-elemental' ) }
					>
						<SelectControl
							label={ __(
								'Pagination Variant',
								'fau-elemental'
							) }
							value={ variant }
							options={ [
								{
									label: __(
										'Basic Pagination',
										'fau-elemental'
									),
									value: 'basic',
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
								setAttributes( { variant: value } )
							}
							help={ __(
								'Choose the type of pagination to display.',
								'fau-elemental'
							) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<div className="pagination-placeholder">
						<p>{ __( 'FAU Pagination', 'fau-elemental' ) }</p>
						<p>
							{ __(
								'Add a FAU Teaser Grid block to see pagination options.',
								'fau-elemental'
							) }
						</p>
					</div>
				</div>
			</>
		);
	}

	// If pagination should be hidden (only 1 page), show a message
	if ( calculatedTotalPages <= 1 ) {
		return (
			<>
				<InspectorControls>
					<PanelBody
						title={ __( 'Pagination Settings', 'fau-elemental' ) }
					>
						<SelectControl
							label={ __(
								'Pagination Variant',
								'fau-elemental'
							) }
							value={ variant }
							options={ [
								{
									label: __(
										'Basic Pagination',
										'fau-elemental'
									),
									value: 'basic',
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
								setAttributes( { variant: value } )
							}
							help={ __(
								'Choose the type of pagination to display.',
								'fau-elemental'
							) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<div className="pagination-hidden">
						<p>
							{ __( 'FAU Pagination', 'fau-elemental' ) }
							{ hasFilters &&
								' + ' + __( 'Filters', 'fau-elemental' ) }
						</p>
						<p>
							{ __(
								'Pagination will appear when there are multiple pages of content.',
								'fau-elemental'
							) }
						</p>
					</div>
				</div>
			</>
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Pagination Settings', 'fau-elemental' ) }
				>
					<SelectControl
						label={ __( 'Pagination Variant', 'fau-elemental' ) }
						value={ variant }
						options={ [
							{
								label: __(
									'Basic Pagination',
									'fau-elemental'
								),
								value: 'basic',
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
							setAttributes( { variant: value } )
						}
						help={ __(
							'Choose the type of pagination to display.',
							'fau-elemental'
						) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<nav
					className={ `pagination ${ variant }` }
					role="navigation"
					aria-label={ __( 'Pagination', 'fau-elemental' ) }
				>
					{ variant === 'load-more' ? (
						<button
							className="load-more-button"
							data-current-page={ currentPage }
							data-total-pages={ calculatedTotalPages }
						>
							{ __( 'Load More', 'fau-elemental' ) }
						</button>
					) : (
						<>
							<button
								type="button"
								className={ `page-numbers prev${
									currentPage === 1 ? ' disabled' : ''
								}` }
								disabled={ currentPage === 1 }
							>
								{ __( 'Prev', 'fau-elemental' ) }
							</button>
							{ Array.from(
								{ length: Math.min( calculatedTotalPages, 5 ) },
								( _, i ) => i + 1
							).map( ( page ) => (
								<button
									key={ page }
									type="button"
									className={ `page-numbers${
										page === currentPage ? ' current' : ''
									}` }
									aria-current={
										page === currentPage
											? 'page'
											: undefined
									}
								>
									{ page }
								</button>
							) ) }
							{ calculatedTotalPages > 5 && (
								<span className="page-numbers dots">...</span>
							) }
							<button
								type="button"
								className={ `page-numbers next${
									currentPage === calculatedTotalPages
										? ' disabled'
										: ''
								}` }
								disabled={
									currentPage === calculatedTotalPages
								}
							>
								{ __( 'Next', 'fau-elemental' ) }
							</button>
						</>
					) }
				</nav>
			</div>
		</>
	);
}
