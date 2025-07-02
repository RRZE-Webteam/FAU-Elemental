import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Define spacing values from the theme's spacing variables
const SPACING_VALUES = [
	{ value: 0.125, label: '0.125rem (2px)' },
	{ value: 0.25, label: '0.25rem (4px)' },
	{ value: 0.5, label: '0.5rem (8px)' },
	{ value: 0.75, label: '0.75rem (12px)' },
	{ value: 1, label: '1rem (16px)' },
	{ value: 1.25, label: '1.25rem (20px)' },
	{ value: 1.5, label: '1.5rem (24px)' },
	{ value: 2, label: '2rem (32px)' },
	{ value: 2.5, label: '2.5rem (40px)' },
	{ value: 3, label: '3rem (48px)' },
	{ value: 3.5, label: '3.5rem (56px)' },
	{ value: 4, label: '4rem (64px)' },
	{ value: 5, label: '5rem (80px)' },
	{ value: 6, label: '6rem (96px)' },
	{ value: 7.5, label: '7.5rem (120px)' },
];

// Convert rem to pixels for the height attribute
const remToPx = ( rem ) => Math.round( rem * 16 );

// Convert pixels to rem for the slider
const pxToRem = ( px ) => {
	// Handle string values like "100px" or "100"
	if ( typeof px === 'string' ) {
		px = parseInt( px.replace( 'px', '' ), 10 );
	}
	return px / 16;
};

// Set initial height attribute for new spacer blocks
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/spacer-initial-height',
	( settings, name ) => {
		if ( name !== 'core/spacer' ) {
			return settings;
		}

		// Set default height to 1rem (16px) - index 4 in our spacing values
		const defaultSpacing = SPACING_VALUES[ 6 ]; // 1rem
		const defaultHeight = remToPx( defaultSpacing.value );

		return {
			...settings,
			attributes: {
				...settings.attributes,
				height: {
					...settings.attributes.height,
					default: `${ defaultHeight }px`,
				},
			},
		};
	}
);

// Add custom settings panel to spacer block
addFilter(
	'editor.BlockEdit',
	'fau-elemental/spacer-custom-settings',
	createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const { name, attributes, setAttributes, isSelected } = props;

			// Only apply to spacer block
			if ( name !== 'core/spacer' ) {
				return <BlockEdit { ...props } />;
			}

			const { height } = attributes;
			const currentRem = pxToRem( height || '100px' );
			const currentSpacingIndex = SPACING_VALUES.findIndex(
				( spacing ) => Math.abs( spacing.value - currentRem ) < 0.01
			);
			const sliderValue =
				currentSpacingIndex >= 0 ? currentSpacingIndex : 4; // Default to 1rem

			const handleSpacingChange = ( newIndex ) => {
				const selectedSpacing = SPACING_VALUES[ newIndex ];
				const newHeight = remToPx( selectedSpacing.value );
				setAttributes( { height: `${ newHeight }px` } );
			};

			return (
				<>
					<BlockEdit { ...props } />
					{ isSelected && (
						<InspectorControls>
							<PanelBody
								title={ __( 'FAU Spacing', 'fau-elemental' ) }
								initialOpen={ true }
							>
								<RangeControl
									label={ __(
										'Spacing Height',
										'fau-elemental'
									) }
									value={ sliderValue }
									onChange={ handleSpacingChange }
									min={ 0 }
									max={ SPACING_VALUES.length - 1 }
									step={ 1 }
									help={ __(
										'Select from predefined spacing values',
										'fau-elemental'
									) }
									__next40pxDefaultSize={ true }
									__nextHasNoMarginBottom={ true }
								/>
								<div
									style={ {
										marginTop: '8px',
										fontSize: '13px',
										color: '#757575',
									} }
								>
									{ __( 'Current:', 'fau-elemental' ) }{ ' ' }
									{ SPACING_VALUES[ sliderValue ].label }
								</div>
							</PanelBody>
						</InspectorControls>
					) }
				</>
			);
		};
	}, 'withSpacerCustomSettings' )
);
