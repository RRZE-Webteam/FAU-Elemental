const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { useEffect, Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;

// Disable rich text formatting for table cells
const disableRichTextFormatting = ( settings, name ) => {
	if ( name === 'core/table' ) {
		return {
			...settings,
			attributes: {
				...settings.attributes,
				tableHeading: {
					type: 'string',
					default: '',
				},
			},
			supports: {
				...settings.supports,
				typography: false,
				color: false,
				align: false,
				spacing: false,
				anchor: false,
			},
		};
	}
	return settings;
};

wp.domReady( () => {
	// Unregister default block styles
	wp.blocks.unregisterBlockStyle( 'core/table', [ 'regular', 'stripes' ] );

	// Remove typography and color support
	wp.blocks.unregisterBlockVariation( 'core/table', 'typography' );
	wp.blocks.unregisterBlockVariation( 'core/table', 'color' );

	// Disable rich text formatting for table cells
	//wp.richText.unregisterFormatType( 'core/bold' );
	//wp.richText.unregisterFormatType( 'core/italic' );
	//wp.richText.unregisterFormatType( 'core/link' );
} );

// Remove formatting options from block registration
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/remove-table-supports',
	disableRichTextFormatting
);

// Remove cell formatting options
addFilter(
	'blocks.getSaveContent.extraProps',
	'fau-elemental/remove-table-cell-formats',
	( props, blockType, attributes ) => {
		if ( blockType.name === 'core/table' ) {
			if ( props.className ) {
				props.className = props.className.replace(
					/has-[\w-]+-(color|background|font-size|text-align)/,
					''
				);
			}
		}
		return props;
	}
);
addFilter(
	'blocks.getSaveElement',
	'fau-elemental/with-table-heading-save',
	( element, blockType, attributes ) => {
		if ( blockType.name !== 'core/table' || ! attributes.tableHeading ) {
			return element;
		}

		return (
			<div className="wp-block-table-wrapper">
				<h3 className="wp-block-table__heading">
					{ attributes.tableHeading }
				</h3>
				{ element }
			</div>
		);
	}
);

// Add table heading control and display
addFilter(
	'editor.BlockEdit',
	'fau-elemental/with-table-heading',
	createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const { name, attributes, setAttributes } = props;

			if ( name !== 'core/table' ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<Fragment>
					<InspectorControls>
						<PanelBody title="Table Settings">
							<TextControl
								label="Table Heading"
								value={ attributes.tableHeading || '' }
								onChange={ ( value ) =>
									setAttributes( { tableHeading: value } )
								}
							/>
						</PanelBody>
					</InspectorControls>
					<div className="wp-block-table-wrapper">
						{ attributes.tableHeading && (
							<h3 className="wp-block-table__heading">
								{ attributes.tableHeading }
							</h3>
						) }
						<BlockEdit { ...props } />
					</div>
				</Fragment>
			);
		};
	}, 'withTableHeading' )
);
// Additional cleanup for any remaining formatting buttons
addFilter(
	'editor.BlockEdit',
	'fau-elemental/with-table-formatting-removed',
	createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const { name } = props;

			useEffect( () => {
				if ( name === 'core/table' ) {
					const removeFormattingButtons = () => {
						// Target all possible toolbar locations
						const selectors = [
							'.block-editor-table-block__fixed-toolbar',
							'.block-editor-block-toolbar',
							'.block-editor-table-cell-toolbar',
							'.block-editor-rich-text__inline-format-toolbar-group',
							'.components-toolbar-group',
							'.block-editor-rich-text__inline-format-toolbar',
						];

						const toolbars = document.querySelectorAll(
							selectors.join( ', ' )
						);

						toolbars.forEach( ( toolbar ) => {
							if ( toolbar ) {
								// Target all formatting buttons and controls
								const formatButtons = toolbar.querySelectorAll(
									'[aria-label*="Bold"], ' +
										'[aria-label*="Italic"], ' +
										'[aria-label*="Link"], ' +
										'[aria-label*="caption"], ' +
										'button[aria-label*="More text settings"], ' +
										'.block-editor-format-toolbar, ' +
										'.format-library-text-color-button, ' +
										'.components-dropdown-menu__toggle'
								);

								formatButtons.forEach( ( button ) => {
									button.style.display = 'none';
								} );

								// Hide the entire toolbar if it's empty
								if (
									toolbar.children.length === 0 ||
									Array.from( toolbar.children ).every(
										( child ) =>
											child.style.display === 'none'
									)
								) {
									toolbar.style.display = 'none';
								}
							}
						} );
					};

					// Initial removal
					removeFormattingButtons();

					// Set up observer for dynamically added buttons
					const observer = new MutationObserver( ( mutations ) => {
						removeFormattingButtons();
					} );

					// Observe the entire editor area
					const editor = document.querySelector(
						'.block-editor-block-list__layout'
					);
					if ( editor ) {
						observer.observe( editor, {
							childList: true,
							subtree: true,
							attributes: true,
							attributeFilter: [ 'class' ],
						} );
					}

					return () => observer.disconnect();
				}
			}, [ name ] );

			return <BlockEdit { ...props } />;
		};
	}, 'withTableFormattingRemoved' )
);
