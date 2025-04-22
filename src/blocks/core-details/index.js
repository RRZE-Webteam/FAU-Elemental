import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { useBlockProps } from '@wordpress/block-editor';

// Add block attributes and restrictions
addFilter(
	'blocks.registerBlockType',
	'core/details-extended',
	( settings, name ) => {
		if ( name !== 'core/details' ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
			},
			supports: {
				...settings.supports,
				align: false, // Remove alignment support
			},
			allowedBlocks: [ 'core/paragraph', 'core/image' ], // Only allow paragraphs and images
			save: ( props ) => {
				const { attributes } = props;

				// Get the original save output
				const originalSave = settings.save;
				return originalSave( props );
			},
		};
	}
);

// Add block restrictions
addFilter(
	'editor.BlockEdit',
	'fau-elemental/with-details-restrictions',
	createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const { name } = props;

			if ( name !== 'core/details' ) {
				return <BlockEdit { ...props } />;
			}

			const blockProps = useBlockProps( {
				onMouseDown: ( event ) => {
					// Get the summary element
					const summary = event.target.closest( 'summary' );
					if ( ! summary ) return;

					// Get the click position relative to the summary
					const rect = summary.getBoundingClientRect();
					const clickX = event.clientX - rect.left;
					const clickY = event.clientY - rect.top;

					// Check if click is in the chevron area (right side)
					if ( clickX > rect.width - 40 ) {
						event.preventDefault();
						event.stopPropagation();
						const details = summary.parentElement;
						if ( details.tagName.toLowerCase() === 'details' ) {
							details.open = ! details.open;
						}
					}
				},
			} );

			return (
				<div { ...blockProps }>
					<BlockEdit { ...props } />
				</div>
			);
		};
	}, 'withDetailsRestrictions' )
);
