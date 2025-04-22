import { addFilter } from '@wordpress/hooks';
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

registerBlockVariation( 'core/media-text', {
	name: 'media-text-with-heading',
	title: __( 'Media & Text', 'fau-elemental' ),
	description: __(
		'Set media and words side-by-side for a richer layout.',
		'fau-elemental'
	),
	attributes: {
		align: 'full',
	},
	isDefault: true,
	scope: [ 'block', 'inserter' ],
	innerBlocks: [
		[
			'core/heading',
			{ placeholder: __( 'Heading', 'fau-elemental' ), level: 2 },
		],
		[ 'core/paragraph', { placeholder: __( 'Content', 'fau-elemental' ) } ],
	],
} );

// Add custom attributes
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-media-text-settings',
	function ( settings, name ) {
		if ( name !== 'core/media-text' ) {
			return settings;
		}

		settings.supports = {
			...settings.supports,
			align: false,
		};

		settings.attributes = {
			...settings.attributes,
			copyrightInfo: {
				type: 'string',
				default: '',
			},
		};

		settings.allowedBlocks = [
			'core/paragraph',
			'core/heading',
			'core/list',
		];

		return settings;
	}
);

// Add inspector controls for copyright info
addFilter(
	'editor.BlockEdit',
	'fau-elemental/add-copyright-info-inspector-controls-media-text',
	function ( BlockEdit ) {
		return ( props ) => {
			const { name, attributes, setAttributes } = props;

			if ( name !== 'core/media-text' ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<>
					<BlockEdit { ...props } />
					<InspectorControls>
						<PanelBody
							title={ __(
								'Additional Settings',
								'fau-elemental'
							) }
						>
							<TextControl
								label={ __(
									'Copyright Info',
									'fau-elemental'
								) }
								value={ attributes.copyrightInfo || '' }
								onChange={ ( value ) =>
									setAttributes( { copyrightInfo: value } )
								}
								__nextHasNoMarginBottom
							/>
						</PanelBody>
					</InspectorControls>
				</>
			);
		};
	}
);
