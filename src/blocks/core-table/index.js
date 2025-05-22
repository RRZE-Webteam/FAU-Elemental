import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { unregisterBlockStyle } from '@wordpress/blocks';

// Unregister default styles
wp.domReady( () => {
	unregisterBlockStyle( 'core/table', [ 'regular', 'stripes' ] );
} );

// Add heading attribute
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/table-heading',
	( settings, name ) => {
		if ( name !== 'core/table' ) {
			return settings;
		}

		// Ensure this is only done once.
		if ( settings.fauModded ) {
			return settings;
		}
		settings.fauModded = true;

		const originalGetSaveElement = settings.save;

		const coreTableDeprecation = {
			supports: { ...settings.supports }, // They stay the same (besides some defaults)
			attributes: { ...settings.attributes }, // They stay the same (besides some defaults)
			save: originalGetSaveElement,
			migrate( attributes ) {
				return {
					...attributes,
					hasFixedLayout: false,
				};
			},
		};

		return {
			...settings,
			deprecated: [
				coreTableDeprecation,
				...( settings.deprecated || [] ),
			],
			attributes: {
				...settings.attributes,
				hasFixedLayout: {
					type: 'boolean',
					default: false,
				},
			},
			save: ( props ) => {
				const { attributes } = props;
				const blockProps = wp.blockEditor.useBlockProps.save( {
					className: 'wp-block-table-wrapper',
				} );

				// Get the original saved content
				const originalSaveElement = originalGetSaveElement( props );

				// Add footer-active class if footer exists
				if ( attributes.foot && attributes.foot.length > 0 ) {
					// Need to clone the original element to modify it
					const modifiedElement = {
						...originalSaveElement,
						props: {
							...originalSaveElement.props,
							className: `${
								originalSaveElement.props?.className || ''
							} footer-active`.trim(),
						},
					};

					return <div { ...blockProps }>{ modifiedElement }</div>;
				}

				return <div { ...blockProps }>{ originalSaveElement }</div>;
			},
		};
	}
);

// Add inspector controls
const withInspectorControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, name } = props;

		if ( name !== 'core/table' ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<div
					className={ `wp-block-table-wrapper${
						attributes.foot && attributes.foot.length > 0
							? ' footer-active'
							: ''
					}` }
				>
					<BlockEdit { ...props } />
				</div>
			</>
		);
	};
}, 'withInspectorControls' );

addFilter(
	'editor.BlockEdit',
	'fau-elemental/with-inspector-controls',
	withInspectorControls
);
