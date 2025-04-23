import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment, useRef, useEffect, useState } from '@wordpress/element';
import { createElement } from '@wordpress/element';

addFilter(
	'blocks.registerBlockType',
	'core/gallery-remove-align',
	(settings, name) => {
		// Only modify Gallery blocks
		if (name !== 'core/gallery') {
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
				columns: {
					type: 'number',
					default: 1
				}
			};
		}

		return settings;
	}
);

// React component for the carousel
const GalleryCarousel = ({ clientId, children }) => {
	const [currentSlide, setCurrentSlide] = useState(0);
	const [slides, setSlides] = useState([]);
	const carouselRef = useRef(null);
	const navContainerRef = useRef(null);

	// Function to select the current image block in the editor
	const selectCurrentImageBlock = () => {
		// select the image block based on
	};

	useEffect(() => {
		if (carouselRef.current) {
			const slideElements = Array.from(carouselRef.current.querySelectorAll('.wp-block-image'));

			if (slideElements.length !== slides.length) {
				setSlides(slideElements);
			}
		}
	}, [children]);

	const selectSlide = (offset) => {
		// get the currently selected image index by checking the isselected class
		let currentSlideIndex = slides.findIndex(image => image.classList.contains('is-selected'));
		if (currentSlideIndex === -1) {
			currentSlideIndex = 0;
		}
		console.log(currentSlideIndex);

		// figure out the next slide - handle both positive and negative offsets correctly
		let nextSlideIndex = ((currentSlideIndex + offset) % slides.length + slides.length) % slides.length;
		console.log(nextSlideIndex);

		// select the next slide
		const selectBlock = wp.data.dispatch('core/block-editor').selectBlock;
		selectBlock(slides[nextSlideIndex].getAttribute('data-block'));
	}

	// Event handlers using React's event system
	const handlePrevClick = () => {
		selectSlide(-1);
	};

	const handleNextClick = () => {
		selectSlide(1);
	};

	return (
		<div className="wp-block-gallery-container" ref={carouselRef}>
			{children}
			{slides.length > 1 && (
				<>
					<div className="gallery-nav-container" ref={navContainerRef}>
						<button
							className="gallery-nav-button prev"
							aria-label="Previous slide"
							onClick={handlePrevClick}
						/>
						<button
							className="gallery-nav-button next"
							aria-label="Next slide"
							onClick={handleNextClick}
						/>
					</div>
				</>
			)}
		</div>
	);
};

addFilter(
	'editor.BlockEdit',
	'core/gallery-carousel-view',
	createHigherOrderComponent(BlockEdit => props => {
		if (props.name !== 'core/gallery') {
			return <BlockEdit {...props} />;
		}

		const { clientId } = props;

		return (
			<Fragment>
				<GalleryCarousel clientId={clientId}>
					<BlockEdit {...props} />
				</GalleryCarousel>
			</Fragment>
		);
	}, 'withCarouselView')
);

addFilter(
	'blocks.getSaveElement',
	'core/gallery-add-navigation',
	(element, blockType, attributes) => {
		if (blockType.name !== 'core/gallery') {
			return element;
		}

		return createElement(
			'div',
			{ className: 'wp-block-gallery-container' },
			element
		);
	}
);