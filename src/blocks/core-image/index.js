import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import {
	unregisterBlockStyle,
	registerBlockVariation,
	registerBlockStyle,
} from '@wordpress/blocks';

// Unregister the rounded style and register new styles for image blocks
wp.domReady( () => {
	unregisterBlockStyle( 'core/image', [ 'default', 'rounded' ] );

	registerBlockStyle( 'core/image', {
		name: 'large',
		label: __( 'Large', 'fau-elemental' ),
		isDefault: true,
	} );

	registerBlockStyle( 'core/image', {
		name: 'medium',
		label: __( 'Medium', 'fau-elemental' ),
		isDefault: false,
	} );

	registerBlockStyle( 'core/image', {
		name: 'small',
		label: __( 'Small', 'fau-elemental' ),
		isDefault: false,
	} );
} );

// Register a default block variation with preconfigured attributes
registerBlockVariation( 'core/image', {
	name: 'fau-default-image',
	isDefault: true,
	attributes: {
		align: 'full',
		className: 'is-style-large',
	},
} );

/**
 * Adds a custom 'copyrightInfo' attribute to all Image blocks and modifies block supports.
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
function editImageBlockAttributesAndSupports( settings, name ) {
	// Only modify Image blocks
	if ( name !== 'core/image' ) {
		return settings;
	}

	// Modify block supports
	settings.supports = {
		...settings.supports,
		// Disable specific features
		filter: false,
		shadow: false,
	};

	settings.attributes = {
		...settings.attributes,
		// Add copyright info attribute
		copyrightInfo: {
			type: 'string',
			default: '',
		},
	};

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'fau-elemental/add-copyright-info-attribute',
	editImageBlockAttributesAndSupports
);

/**
 * Adds a custom 'copyrightInfo' attribute text input field to all Image blocks in the editor.
 *
 * @param {*} BlockEdit
 * @returns
 */
function addCopyrightInfoInspectorControls( BlockEdit ) {
	return ( props ) => {
		const { name, attributes, setAttributes } = props;

		// Early return if the block is not the Image block.
		if ( name !== 'core/image' ) {
			return <BlockEdit { ...props } />;
		}

		// Retrieve selected attributes from the block.
		const { copyrightInfo } = attributes;

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Additional Settings', 'fau-elemental' ) }
					>
						<TextControl
							label={ __( 'Copyright Info', 'fau-elemental' ) }
							value={ copyrightInfo }
							onChange={ ( value ) =>
								setAttributes( { copyrightInfo: value } )
							}
						/>
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}

addFilter(
	'editor.BlockEdit',
	'fau-elemental/add-copyright-info-inspector-controls',
	addCopyrightInfoInspectorControls
);
