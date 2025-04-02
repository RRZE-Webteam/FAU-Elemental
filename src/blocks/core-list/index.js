import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

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

			// If the list is ordered and has the list-icons class, remove it
			if ( ! isUnordered && attributes.className?.includes( 'list-icons' ) ) {
				const currentClasses = attributes.className
					.split( ' ' )
					.filter( ( cls ) => cls !== 'list-icons' );
				
				setAttributes( {
					className: currentClasses.length > 0
						? currentClasses.join( ' ' )
						: undefined,
				} );
			}

			return (
				<>
					{ isUnordered && (
						<InspectorControls>
							<PanelBody
								title={ __(
									'List Style Settings',
									'fau-elemental'
								) }
							>
								<SelectControl
									label={ __(
										'List Style',
										'fau-elemental'
									) }
									value={
										attributes.className?.includes(
											'list-icons'
										)
											? 'list-icons'
											: 'line'
									}
									options={ [
										{
											label: __(
												'Line',
												'fau-elemental'
											),
											value: 'line',
										},
										{
											label: __(
												'Icons',
												'fau-elemental'
											),
											value: 'list-icons',
										},
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

										// Add the new class if it's not 'line'
										if ( value !== 'line' ) {
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
