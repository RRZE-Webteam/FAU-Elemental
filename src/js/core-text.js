const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const {
	getBlockType,
	registerBlockType,
	registerBlockVariation,
	unregisterBlockVariation,
} = wp.blocks;
const { BlockControls, InspectorControls } = wp.blockEditor;
const { ToolbarGroup, ToolbarButton, PanelBody, SelectControl } = wp.components;

// Get the original Heading block
const headingBlock = getBlockType( 'core/heading' );

wp.domReady( () => {
	// Register "Intro Text" variation for core/paragraph with an icon
	registerBlockVariation( 'core/paragraph', {
		name: 'text',
		title: 'Text',
		description: 'A paragraph.',
		attributes: {
			className: 'text',
		},
		icon: 'editor-paragraph', // Dashicon for text
		isDefault: true,
		scope: [ 'block', 'inserter', 'transform' ],
	} );

	// Register "Intro Text" variation for core/paragraph with an icon
	registerBlockVariation( 'core/paragraph', {
		name: 'intro-text',
		title: 'Intro Text',
		description: 'A paragraph styled as an introduction.',
		attributes: {
			className: 'intro-text',
		},
		icon: 'editor-paragraph', // Dashicon for text
		isDefault: false,
		scope: [ 'block', 'inserter', 'transform' ],
	} );

	// Register "Small Text" variation for core/paragraph with an icon
	registerBlockVariation( 'core/paragraph', {
		name: 'small-text',
		title: 'Small Text',
		description: 'A smaller paragraph for fine print or secondary content.',
		attributes: {
			className: 'small-text',
		},
		icon: 'editor-paragraph', // Dashicon for paragraph text
		isDefault: false,
		scope: [ 'block', 'inserter', 'transform' ],
	} );
} );

// Add list style controls
addFilter(
	'editor.BlockEdit',
	'fau-elemental/with-list-style-controls',
	createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const { attributes, setAttributes, name } = props;

			// Only show for list blocks
			if ( name !== 'core/list' ) {
				return <BlockEdit { ...props } />;
			}

			// Only show for unordered lists
			const isUnordered = ! attributes.ordered;

			return (
				<>
					{ isUnordered && (
						<InspectorControls>
							<PanelBody title="List Style Settings">
								<SelectControl
									label="List Style"
									value={
										attributes.className?.includes(
											'list-icons'
										)
											? 'list-icons'
											: 'dots'
									}
									options={ [
										{ label: 'Dots', value: 'dots' },
										{ label: 'Icons', value: 'list-icons' },
									] }
									onChange={ ( value ) => {
										// Get current classes as an array
										const currentClasses =
											attributes.className
												? attributes.className
														.split( ' ' )
														.filter(
															( cls ) =>
																cls !==
																'list-icons'
														)
												: [];

										// Add the new class if it's not 'dots'
										if ( value !== 'dots' ) {
											currentClasses.push( value );
										}

										// Set the new className
										setAttributes( {
											className:
												currentClasses.length > 0
													? currentClasses.join( ' ' )
													: undefined,
										} );
									} }
								/>
							</PanelBody>
						</InspectorControls>
					) }
					<BlockEdit { ...props } />
				</>
			);
		};
	}, 'withListStyleControls' )
);
