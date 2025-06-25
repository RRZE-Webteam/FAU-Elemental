import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { variant, currentPage, totalPages, customBlockId, gridBlockId } =
		attributes;
	const blockProps = useBlockProps();

	// Generate and set customBlockId if it doesn't exist
	useEffect( () => {
		if ( ! customBlockId ) {
			// Use clientId to create a unique block ID
			const newBlockId = `fau-pagination-${ clientId.substring( 0, 8 ) }`;
			setAttributes( { customBlockId: newBlockId } );
		}
	}, [ customBlockId, clientId, setAttributes ] );

	// Detect teaser grid block
	const { nearbyGrid } = useSelect( ( select ) => {
		const { getBlocks } = select( 'core/block-editor' );
		const allBlocks = getBlocks();

		// Find teaser grid block
		const gridBlock = allBlocks.find(
			( block ) => block.name === 'fau-elemental/fau-teaser-grid'
		);

		return {
			nearbyGrid: gridBlock,
		};
	}, [] );

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
							data-total-pages={ totalPages }
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
								{ length: Math.min( totalPages, 5 ) },
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
							{ totalPages > 5 && (
								<span className="page-numbers dots">...</span>
							) }
							<button
								type="button"
								className={ `page-numbers next${
									currentPage === totalPages
										? ' disabled'
										: ''
								}` }
								disabled={ currentPage === totalPages }
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
