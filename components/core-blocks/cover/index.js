import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Add custom attributes
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-cover-block-settings',
	function ( settings, name ) {
		if ( name !== 'core/cover' ) {
			return settings;
		}

		settings.attributes = {
			...settings.attributes,
			copyrightInfo: {
				type: 'string',
				default: '',
			},
		};

		return settings;
	}
);

// Add inspector controls for copyright info
addFilter(
	'editor.BlockEdit',
	'fau-elemental/add-copyright-info-inspector-controls-cover',
	function ( BlockEdit ) {
		return ( props ) => {
			const { name, attributes, setAttributes } = props;

			if ( name !== 'core/cover' ) {
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
							className="faue-additional-settings"
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
