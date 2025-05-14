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
	'fau-elemental/edit-image-block-settings',
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

			// Function to enforce 3:2 aspect ratio maximum
			const enforceAspectRatio = () => {
				if ( ! blockRef.current ) return;

				const img = blockRef.current.querySelector( 'img' );
				if ( ! img ) return;

				const naturalWidth = img.naturalWidth;
				const naturalHeight = img.naturalHeight;
				const containerWidth = img.parentElement.offsetWidth;

				// Calculate the actual width the image will be displayed at
				const displayWidth = Math.min( naturalWidth, containerWidth );

				// Calculate what the height would be at natural aspect ratio
				const naturalHeightAtWidth =
					( displayWidth / naturalWidth ) * naturalHeight;

				// Calculate what the height would be at 3:2 ratio
				const targetHeight = displayWidth / 1.5;

				// Log the original aspect ratio
				console.log( 'Image Aspect Ratio Debug:', {
					naturalWidth,
					naturalHeight,
					containerWidth,
					displayWidth,
					naturalHeightAtWidth,
					targetHeight,
					originalRatio: naturalWidth / naturalHeight,
					displayRatio: displayWidth / naturalHeightAtWidth,
					targetRatio: displayWidth / targetHeight,
				} );

				// If the natural height at this width would be taller than 3:2, use the target height
				if ( naturalHeightAtWidth > targetHeight ) {
					img.style.width = `${ displayWidth }px`;
					img.style.height = `${ targetHeight }px`;
					img.style.objectFit = 'cover';
					img.style.objectPosition = 'center';

					// Log the adjusted aspect ratio
					console.log( 'Adjusted Image:', {
						finalWidth: displayWidth,
						finalHeight: targetHeight,
						finalRatio: displayWidth / targetHeight,
						adjustment: 'Applied 3:2 ratio',
					} );
				} else {
					// Reset to natural dimensions
					img.style.width = `${ displayWidth }px`;
					img.style.height = 'auto';
					img.style.objectFit = 'none';
					img.style.objectPosition = 'initial';

					// Log the natural aspect ratio
					console.log( 'Natural Image:', {
						finalWidth: displayWidth,
						finalHeight: naturalHeightAtWidth,
						finalRatio: displayWidth / naturalHeightAtWidth,
						adjustment: 'Kept natural ratio',
					} );
				}
			};

			// Add the button and enforce aspect ratio after the component mounts
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

				// Enforce aspect ratio when image loads
				if ( img ) {
					if ( img.complete ) {
						enforceAspectRatio();
					} else {
						img.addEventListener( 'load', enforceAspectRatio );
					}
				}

				// Add resize observer to handle container width changes
				const resizeObserver = new ResizeObserver( () => {
					enforceAspectRatio();
				} );

				if ( parentDiv ) {
					resizeObserver.observe( parentDiv );
				}

				return () => {
					if ( img ) {
						img.removeEventListener( 'load', enforceAspectRatio );
					}
					resizeObserver.disconnect();
				};
			}, [ url ] );

			return (
				<div className="wp-block-image-wrapper" ref={ blockRef }>
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
				</div>
			);
		};
	}
);
