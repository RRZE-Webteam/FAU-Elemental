import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import {
	unregisterBlockStyle,
	registerBlockVariation,
	registerBlockStyle,
} from '@wordpress/blocks';
import { useRef, useEffect } from '@wordpress/element';

/**
 * Customizes the core/image block by:
 * 1. Removing default and rounded styles
 * 2. Adding custom 'large' and 'medium' styles
 * 3. Creating a default variation with full alignment and overlay
 *
 * This code runs when the DOM is ready and modifies the core image block
 * to match the FAU Elemental theme's design requirements.
 */
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

	registerBlockVariation( 'core/image', {
		name: 'fau-default-image',
		isDefault: true,
		attributes: {
			align: 'full',
			className: 'is-style-large has-overlay',
		},
	} );
} );

/**
 * This filter extends the core/image block by:
 * 1. Adding a 'copyrightInfo' string attribute to store image copyright information
 * 2. Adding a 'hasOverlay' boolean attribute to control overlay display
 * 3. Disabling certain block supports (filter, align) to customize the block's behavior
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/add-copyright-info-attribute',
	( settings, name ) => {
		// Only modify Image blocks
		if ( name !== 'core/image' ) {
			return settings;
		}

		settings.supports = {
			...settings.supports,
			filter: false,
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
				default: true,
			},
		};

		return settings;
	}
);

/**
 * Enhances the Image block with custom functionality in the editor.
 * This filter adds a fullscreen button to image blocks and provides additional
 * controls in the sidebar inspector for copyright information and overlay options.
 * It also adds an 'image-wrapper' class to the parent div of images for styling purposes.
 *
 * @param {*} BlockEdit
 * @returns {JSX} The enhanced Image block component.
 */
addFilter(
	'editor.BlockEdit',
	'fau-elemental/enhance-image-block',
	( BlockEdit ) => {
		return ( props ) => {
			const { name, attributes, setAttributes } = props;

			// Early return if the block is not the Image block.
			if ( name !== 'core/image' ) {
				return <BlockEdit { ...props } />;
			}

			// Retrieve selected attributes from the block.
			const { copyrightInfo, hasOverlay, className, url } = attributes;

			// Use a ref to access the DOM after render
			const blockRef = useRef( null );

			// Add the button after the component mounts
			useEffect( () => {
				if ( ! url ) return;

				const figure = blockRef.current?.querySelector( 'figure' );
				if ( ! figure ) return;

				// Add image-wrapper class to the parent div of the img
				const img = figure.querySelector( 'img' );
				const parentDiv = img?.parentNode;
				if ( parentDiv?.tagName === 'DIV' ) {
					parentDiv.classList.add( 'image-wrapper' );
				}

				// Add fullscreen button if it doesn't exist
				if ( ! figure.querySelector( '.image-fullscreen-btn' ) ) {
					const button = document.createElement( 'button' );
					button.className = 'image-fullscreen-btn';
					button.innerHTML = '⛶';
					figure.appendChild( button );
				}
			}, [ url ] );

			return (
				<>
					<div className="wp-block-image-wrapper" ref={ blockRef }>
						<BlockEdit { ...props } />
					</div>
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
										if (
											! classes.includes( 'has-overlay' )
										) {
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
);
