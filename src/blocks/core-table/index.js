import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

// Unregister default styles
wp.domReady( () => {
	wp.blocks.unregisterBlockStyle( 'core/table', [ 'regular', 'stripes' ] );
} );

// Add heading attribute
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/table-heading',
	( settings, name ) => {
		if ( name !== 'core/table' ) {
			return settings;
		}

		const originalGetSaveElement = settings.save;
		
		return {
			...settings,
			attributes: {
				...settings.attributes,
				tableHeading: {
					type: 'string',
					default: '',
				},
			},
			save: ( props ) => {
				const { attributes } = props;
				const blockProps = wp.blockEditor.useBlockProps.save( {
					className: 'wp-block-table-wrapper',
				});

				// Get the original saved content
				const originalSaveElement = originalGetSaveElement(props);

				// Add footer-active class if footer exists
				if (attributes.foot && attributes.foot.length > 0) {
					// Need to clone the original element to modify it
					const modifiedElement = {
						...originalSaveElement,
						props: {
							...originalSaveElement.props,
							className: `${originalSaveElement.props?.className || ''} footer-active`.trim()
						}
					};

					return (
						<div { ...blockProps }>
							{ attributes.tableHeading && (
								<h3>{ attributes.tableHeading }</h3>
							) }
							{ modifiedElement }
						</div>
					);
				}

				return (
					<div { ...blockProps }>
						{ attributes.tableHeading && (
							<h3>{ attributes.tableHeading }</h3>
						) }
						{ originalSaveElement }
					</div>
				);
			},
		};
	}
);

// Add inspector controls
const withInspectorControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, setAttributes, name } = props;

		if ( name !== 'core/table' ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<InspectorControls>
					<PanelBody title="Table Settings">
						<TextControl
							label="Table Heading"
							value={ attributes.tableHeading || '' }
							onChange={ ( value ) =>
								setAttributes( { tableHeading: value } )
							}
							help="Add a heading that will appear above the table"
						/>
					</PanelBody>
				</InspectorControls>
				<div className="wp-block-table-wrapper">
					{ attributes.tableHeading && (
						<div className="wp-block-table__heading">
							{ attributes.tableHeading }
						</div>
					) }
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
