import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

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
