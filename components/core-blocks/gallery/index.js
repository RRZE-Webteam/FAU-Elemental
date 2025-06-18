import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { useRef, useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { useBlockProps } from '@wordpress/block-editor';

addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-gallery-block-settings',
	( settings, name ) => {
		// Only modify Gallery blocks
		if ( name !== 'core/gallery' ) {
			return settings;
		}

		// Ensure this is only done once.
		if ( settings.fauModded ) {
			return settings;
		}
		settings.fauModded = true;

		// Deprecation for old Core-Markup
		const oldSaveFn = settings.save;
		const coreGalleryDeprecation = {
			supports: { ...settings.supports },
			attributes: { ...settings.attributes },
			save: oldSaveFn,
			migrate( attributes ) {
				return {
					...attributes,
					sizeSlug: 'full',
					columns: 1,
				};
			},
		};
		settings.deprecated = [
			coreGalleryDeprecation,
			...( settings.deprecated || [] ),
		];

		// Modify block supports
		settings.supports = {
			...settings.supports,
			// Disable alignment support
			align: false,
		};

		// Set default image size to full, disable crop, and set columns to 1
		settings.attributes = {
			...( settings.attributes || {} ),
			sizeSlug: {
				type: 'string',
				default: 'full',
			},
			columns: {
				type: 'number',
				default: 1,
			},
		};

		// Overwrite save method to add wrapper
		settings.save = ( attributes ) => {
			return (
				<div className="wp-block-gallery-container">
					{ oldSaveFn( attributes ) }
				</div>
			);
		};

		return settings;
	},
	20 // Prio
);

// Add filter to update image indices when gallery content changes
addFilter(
	'editor.BlockEdit',
	'fau-elemental/update-gallery-image-indices',
	createHigherOrderComponent(
		( BlockEdit ) => ( props ) => {
			const { name, clientId } = props;

			// Only modify Gallery blocks
			if ( name !== 'core/gallery' ) {
				return <BlockEdit { ...props } />;
			}

			const { updateBlockAttributes } =
				useDispatch( 'core/block-editor' );
			const innerBlocks = useSelect(
				( select ) => {
					const block =
						select( 'core/block-editor' ).getBlock( clientId );
					return block?.innerBlocks || [];
				},
				[ clientId ]
			);

			// Update image indices whenever inner blocks change
			useEffect( () => {
				// Count total number of image blocks
				const totalImages = innerBlocks.filter(
					( block ) => block.name === 'core/image'
				).length;

				innerBlocks.forEach( ( block, index ) => {
					if ( block.name === 'core/image' ) {
						updateBlockAttributes( block.clientId, {
							galleryIndexText: `${ index + 1 }/${ totalImages }`,
						} );
					}
				} );
			}, [ innerBlocks ] );

			return <BlockEdit { ...props } />;
		},
		'withGalleryImageIndices'
	)
);

// React component for the carousel
const GalleryCarousel = ( props ) => {
	const [ slides, setSlides ] = useState( [] );
	const carouselRef = useRef( null );

	// Get the current block's inner blocks and content
	const { innerBlocks, content } = useSelect(
		( select ) => {
			const block = select( 'core/block-editor' ).getBlock(
				props.clientId
			);
			return {
				innerBlocks: block?.innerBlocks || [],
				content: block?.attributes?.content || '',
			};
		},
		[ props.clientId ]
	);

	// Function to update slides
	const updateSlides = () => {
		if ( carouselRef.current ) {
			const slideElements = Array.from(
				carouselRef.current.querySelectorAll( '.wp-block-image' )
			);

			// Display the slide number in the backend
			slideElements.forEach( ( slide, index ) => {
				const galleryIndexStr = `${ index + 1 }/${
					slideElements.length
				}`;
				const figcaption = slide.querySelector( 'figcaption' );
				let galleryIndex = slide.querySelector(
					'.gallery-index-display'
				);

				// If the caption is toggled on, we must remove the old index display.
				if (
					figcaption &&
					galleryIndex &&
					! figcaption.contains( galleryIndex )
				) {
					galleryIndex.remove();
					galleryIndex = null;
				}

				if ( galleryIndex ) {
					// If the index already exists we can simply update it.
					galleryIndex.textContent = galleryIndexStr;
				} else {
					// If it does not exists we create it and append it to the figcaption or the image-wrapper
					galleryIndex = document.createElement( 'span' );
					galleryIndex.textContent = galleryIndexStr;
					galleryIndex.classList.add( 'gallery-index-display' );

					if ( figcaption ) {
						figcaption.appendChild( galleryIndex );
					} else {
						slide
							.querySelector( '.image-wrapper' )
							?.appendChild( galleryIndex );
					}
				}
			} );

			if ( slideElements.length !== slides.length ) {
				setSlides( slideElements );
			}
		}
	};

	// Watch for changes in inner blocks and content
	useEffect( () => {
		updateSlides();
	}, [ innerBlocks, content, props.clientId ] );

	// Also watch for block editor events
	useEffect( () => {
		const unsubscribe = wp.data.subscribe( () => {
			// Check if the block's content has changed
			const currentBlock = wp.data
				.select( 'core/block-editor' )
				.getBlock( props.clientId );
			if ( currentBlock ) {
				updateSlides();
			}
		} );

		return () => unsubscribe();
	}, [ props.clientId ] );

	const selectSlide = ( offset ) => {
		// get the currently selected image index by checking the isselected class
		let currentSlideIndex = slides.findIndex( ( image ) =>
			image.classList.contains( 'is-selected' )
		);
		if ( currentSlideIndex === -1 ) {
			currentSlideIndex = 0;
		}

		// figure out the next slide - handle both positive and negative offsets correctly
		const nextSlideIndex =
			( ( ( currentSlideIndex + offset ) % slides.length ) +
				slides.length ) %
			slides.length;

		// select the next slide
		const selectBlock = wp.data.dispatch( 'core/block-editor' ).selectBlock;
		selectBlock( slides[ nextSlideIndex ].getAttribute( 'data-block' ) );
	};

	// Event handlers using React's event system
	const handlePrevClick = () => {
		selectSlide( -1 );
	};

	const handleNextClick = () => {
		selectSlide( 1 );
	};

	const blockProps = useBlockProps( {
		className: 'wp-block-gallery-container',
	} );

	return (
		<div { ...blockProps } ref={ carouselRef }>
			{ slides.length > 1 && (
				<div className="gallery-nav-container">
					<button
						className="gallery-nav-button prev"
						aria-label="Previous slide"
						onClick={ handlePrevClick }
					/>
					<button
						className="gallery-nav-button next"
						aria-label="Next slide"
						onClick={ handleNextClick }
					/>
				</div>
			) }
			{ props.children }
		</div>
	);
};

addFilter(
	'editor.BlockEdit',
	'fau-elemental/edit-gallery-block-view',
	createHigherOrderComponent(
		( BlockEdit ) => ( props ) => {
			if ( props.name !== 'core/gallery' ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<GalleryCarousel { ...props }>
					<BlockEdit { ...props } />
				</GalleryCarousel>
			);
		},
		'withCarouselView'
	)
);
