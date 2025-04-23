import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import {
	unregisterBlockStyle,
	registerBlockVariation,
	registerBlockStyle,
} from '@wordpress/blocks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

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

	settings.supports = {
		...settings.supports,
		filter: false,
		shadow: false,
		align: false,
	};

	settings.attributes = {
		...settings.attributes,
		copyrightInfo: {
			type: 'string',
			default: '',
		},
		hasOverlay: {
			type: 'boolean',
			default: false,
		},
	};

	return settings;
}

// Comment out each filter temporarily to test
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
		const { copyrightInfo, hasOverlay, className } = attributes;

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
							__nextHasNoMarginBottom
						/>
						<ToggleControl
							label={ __( 'Add Overlay', 'fau-elemental' ) }
							checked={ hasOverlay }
							onChange={ ( value ) => {
								const classes = className
									? className.split( ' ' )
									: [];
								if ( value ) {
									if ( ! classes.includes( 'has-overlay' ) ) {
										classes.push( 'has-overlay' );
									}
								} else {
									const index =
										classes.indexOf( 'has-overlay' );
									if ( index > -1 ) {
										classes.splice( index, 1 );
									}
								}
								setAttributes( {
									hasOverlay: value,
									className: classes.join( ' ' ),
								} );
							} }
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

/**
 * Adds the fullscreen button to the image block in the editor.
 *
 * @param {*} BlockEdit
 * @returns
 */
function addFullscreenButtonToEditor( BlockEdit ) {
	return ( props ) => {
		const { name, attributes } = props;

		// Early return if the block is not the Image block.
		if ( name !== 'core/image' ) {
			return <BlockEdit { ...props } />;
		}

		// Get the image URL from the block attributes
		const { url } = attributes;

		// If there's no URL, return the original block edit component
		if ( ! url ) {
			return <BlockEdit { ...props } />;
		}

		// Create a wrapper for the block edit component
		const blockProps = useBlockProps();

		// Use a ref to access the DOM after render
		const blockRef = React.useRef( null );

		// Add the button after the component mounts
		React.useEffect( () => {
			if ( blockRef.current ) {
				// Find the figure element
				const figure = blockRef.current.querySelector( 'figure' );
				if ( figure ) {
					// Check if button already exists
					if ( ! figure.querySelector( '.image-fullscreen-btn' ) ) {
						// Create the button
						const button = document.createElement( 'button' );
						button.className = 'image-fullscreen-btn';
						button.innerHTML = '⛶';

						// Add the button to the figure
						figure.appendChild( button );
					}
				}
			}
		}, [ url ] );

		return (
			<div { ...blockProps } ref={ blockRef }>
				<BlockEdit { ...props } />
			</div>
		);
	};
}

addFilter(
	'editor.BlockEdit',
	'fau-elemental/add-fullscreen-button-to-editor',
	addFullscreenButtonToEditor
);
