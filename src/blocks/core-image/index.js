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
			galleryIndexText: {
				type: 'string',
				default: '',
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
			const {
				copyrightInfo,
				hasOverlay,
				className,
				url,
				galleryIndexText,
				caption
			} = attributes;

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

				// Calculate maximum allowed height for 3:2 ratio based on container width
				const maxAllowedHeight = containerWidth / 1.5;

				// Calculate what the height would be at natural aspect ratio
				const naturalHeightAtWidth =
					( containerWidth / naturalWidth ) * naturalHeight;

				// If the natural height at container width would be taller than max allowed height
				if ( naturalHeightAtWidth > maxAllowedHeight ) {
					// Calculate the scale factor needed to fit within max allowed height
					const scaleFactor = maxAllowedHeight / naturalHeightAtWidth;
					const scaledWidth = containerWidth * scaleFactor;
					
					img.style.width = `${ scaledWidth }px`;
					img.style.height = 'auto';
					img.style.objectFit = 'contain';
					img.style.objectPosition = 'center';
				} else {
					// Reset to natural dimensions
					img.style.width = `${ containerWidth }px`;
					img.style.height = 'auto';
					img.style.objectFit = 'none';
					img.style.objectPosition = 'initial';
				}

				// Calculate total block height including wrapper and figcaption
				const wrapper = blockRef.current.querySelector('.image-wrapper');
				const figcaption = blockRef.current.querySelector('figcaption');
				const wrapperHeight = wrapper ? wrapper.offsetHeight : 0;
				const figcaptionHeight = figcaption ? figcaption.offsetHeight : 0;
				const figcaptionOffset = figcaption ? 47 : 0;
				const totalHeight = wrapperHeight + figcaptionHeight - figcaptionOffset;
				
				// Set the calculated height on the block
				blockRef.current.style.height = `${totalHeight}px`;
			};

			// Add the button and enforce aspect ratio after the component mounts
			useEffect( () => {
				if ( ! url ) return;

				const figure = blockRef.current?.querySelector( 'figure' );
				const img = blockRef.current?.querySelector( 'img' );
				if ( ! img ) return;

				// Add image-wrapper class to the parent div of the img
				const parentDiv = img?.parentNode;
				if ( parentDiv?.tagName === 'DIV' ) {
					parentDiv.classList.add( 'image-wrapper' );
				}

				// Add fullscreen button if it doesn't exist
				if (
					figure &&
					! figure.querySelector( '.image-fullscreen-btn' )
				) {
					const button = document.createElement( 'button' );
					button.className = 'image-fullscreen-btn';
					button.innerHTML = '⛶';
					figure.appendChild( button );
				}

				// Enforce aspect ratio when image loads
				if ( img.complete ) {
					enforceAspectRatio();
				} else {
					img.addEventListener( 'load', enforceAspectRatio );
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
			}, [ url, galleryIndexText ] );

			// Recalculate height when caption changes
			useEffect(() => {
				if (blockRef.current) {
					enforceAspectRatio();
				}
			}, [caption]);

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
