import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment, useRef, useEffect } from '@wordpress/element';

/**
 * Modifies block supports for the Gallery block.
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
function editGalleryBlockSupports( settings, name ) {
	// Only modify Gallery blocks
	if ( name !== 'core/gallery' ) {
		return settings;
	}

	// Modify block supports
	settings.supports = {
		...settings.supports,
		// Disable alignment support
		align: false,
	};

	// Set default image size to full, disable crop, and set columns to 1
	if (settings.attributes) {
		settings.attributes = {
			...settings.attributes,
			sizeSlug: {
				type: 'string',
				default: 'full'
			},
			imageCrop: {
				type: 'boolean',
				default: false
			},
			columns: {
				type: 'number',
				default: 1
			}
		};
	}

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'core/gallery-remove-align',
	editGalleryBlockSupports
);

const initCarousel = (container, initialSlide = 0) => {
	if (!container) return;

	const slides = Array.from(container.querySelectorAll('.wp-block-image'));
	if (!slides.length || slides.length <= 1) return;

	let currentSlide = Math.min(initialSlide, slides.length - 1);

	const updateSlides = () => {
		slides.forEach((slide, index) => {
			slide.style.display = index === currentSlide ? 'block' : 'none';
		});

		const counter = container.querySelector('.carousel-counter');
		if (counter) {
			counter.textContent = `${currentSlide + 1}/${slides.length}`;
		}
	};

	updateSlides();

	const prevButton = container.querySelector('.carousel-prev');
	if (prevButton) {
		prevButton.onclick = () => {
			currentSlide = (currentSlide - 1 + slides.length) % slides.length;
			updateSlides();
		};
	}

	const nextButton = container.querySelector('.carousel-next');
	if (nextButton) {
		nextButton.onclick = () => {
			currentSlide = (currentSlide + 1) % slides.length;
			updateSlides();
		};
	}
};

const withCarouselView = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		if (props.name !== 'core/gallery') {
			return <BlockEdit {...props} />;
		}

		const carouselRef = useRef(null);

		useEffect(() => {
			// Initialize carousel when block is selected or images change
			if (carouselRef.current) {
				initCarousel(carouselRef.current);
			}
		}, [props.isSelected, props.attributes.images]);

		return (
			<Fragment>
				<div className="gallery-carousel-wrapper" ref={carouselRef}>
					<BlockEdit {...props} />
					<div className="carousel-controls">
						<button 
							className="carousel-prev" 
							aria-label="Previous slide"
							dangerouslySetInnerHTML={{ __html: "&#10094;" }}
						/>
						<button 
							className="carousel-next" 
							aria-label="Next slide"
							dangerouslySetInnerHTML={{ __html: "&#10095;" }}
						/>
					</div>
					<div className="carousel-counter"></div>
				</div>
			</Fragment>
		);
	};
}, 'withCarouselView');

addFilter(
	'editor.BlockEdit',
	'core/gallery-carousel-view',
	withCarouselView
);

