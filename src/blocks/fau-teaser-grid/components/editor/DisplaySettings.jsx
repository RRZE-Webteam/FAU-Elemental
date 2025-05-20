import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	SelectControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';

// Define teaser layout options
const TEASER_LAYOUTS = [
	{
		label: __( '1 Extra Large Teaser (1×XL)', 'fau-elemental' ),
		value: '1xl',
	},
	{ label: __( '2 Large Teasers (2×L)', 'fau-elemental' ), value: '2l' },
	{ label: __( '1 Large + 2 Small (L+2S)', 'fau-elemental' ), value: 'l2s' },
	{ label: __( '2 Small + 1 Large (2S+L)', 'fau-elemental' ), value: '2sl' },
	{ label: __( '3 Medium Teasers (3×M)', 'fau-elemental' ), value: '3m' },
	{
		label: __( '2 Small - Image Left (2S BLTR)', 'fau-elemental' ),
		value: '2s-left',
	},
	{
		label: __( '2 Small - Image Right (2S TLBR)', 'fau-elemental' ),
		value: '2s-right',
	},
];

export const DisplaySettings = ( {
	displayStyle,
	teaserLayout,
	headingLevel,
	onDisplayStyleChange,
	onTeaserLayoutChange,
	setAttributes,
} ) => {
	return (
		<PanelBody title={ __( 'Display Settings', 'fau-elemental' ) }>
			<ToggleGroupControl
				label={ __( 'Display style options', 'fau-elemental' ) }
				value={ displayStyle }
				onChange={ onDisplayStyleChange }
				isBlock
				__next40pxDefaultSize={ true }
				__nextHasNoMarginBottom={ true }
			>
				<ToggleGroupControlOption
					value="teaser-grid"
					label={ __( 'Teaser Grid', 'fau-elemental' ) }
				/>
				<ToggleGroupControlOption
					value="mini-list"
					label={ __( 'Mini List', 'fau-elemental' ) }
				/>
			</ToggleGroupControl>

			{ displayStyle === 'teaser-grid' && (
				<SelectControl
					label={ __( 'Teaser Layout', 'fau-elemental' ) }
					value={ teaserLayout }
					options={ TEASER_LAYOUTS }
					onChange={ onTeaserLayoutChange }
					aria-describedby="teaser-layout-description"
					__nextHasNoMarginBottom={ true }
					__next40pxDefaultSize={ true }
				/>
			) }

			<SelectControl
				label={ __( 'Heading Level', 'fau-elemental' ) }
				value={ headingLevel || 'h4' }
				options={ [
					{ label: 'H2', value: 'h2' },
					{ label: 'H3', value: 'h3' },
					{ label: 'H4', value: 'h4' },
					{ label: 'H5', value: 'h5' },
					{ label: 'H6', value: 'h6' },
				] }
				onChange={ ( value ) =>
					setAttributes( { headingLevel: value } )
				}
				help={ __(
					'Choose the heading level for teasers (H1 is excluded for accessibility)',
					'fau-elemental'
				) }
				__nextHasNoMarginBottom={ true }
				__next40pxDefaultSize={ true }
			/>
		</PanelBody>
	);
};
