import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment, useRef, useEffect, useState } from '@wordpress/element';
import { createElement } from '@wordpress/element';

addFilter(
	'blocks.registerBlockType',
	'core/gallery-remove-align',
	( settings, name ) => {
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
		if (clientId) {
			const { selectBlock } = wp.data.dispatch('core/block-editor');
			const { getBlock } = wp.data.select('core/block-editor');
			
			// Get the gallery block
			const galleryBlock = getBlock(clientId);
			if (galleryBlock && galleryBlock.innerBlocks && galleryBlock.innerBlocks[currentSlide]) {
				// Select the current image block
				selectBlock(galleryBlock.innerBlocks[currentSlide].clientId);
			}
		}
	};
	
	useEffect(() => {
		if (carouselRef.current) {
			const slideElements = Array.from(carouselRef.current.querySelectorAll('.wp-block-image'));

			if (slideElements.length !== slides.length) {
				setCurrentSlide(slideElements.length - 1);
			}

			setSlides(slideElements);
		}
	}, [children]);
	
	useEffect(() => {
		if (!clientId || !wp.data || !wp.data.subscribe) return;
		
		// Subscribe to changes in the selected block
		const unsubscribe = wp.data.subscribe(() => {
			const { getSelectedBlock } = wp.data.select('core/block-editor');
			const selectedBlock = getSelectedBlock();
			
			// Check if the selected block is an image block within this gallery
			if (selectedBlock && selectedBlock.name === 'core/image') {
				const { getBlock } = wp.data.select('core/block-editor');
				const galleryBlock = getBlock(clientId);
				
				if (galleryBlock && galleryBlock.innerBlocks) {
					// Find the index of the selected image block in the gallery
					const imageIndex = galleryBlock.innerBlocks.findIndex(
						block => block.clientId === selectedBlock.clientId
					);
					
					// If the selected image is in this gallery, update the current slide
					if (imageIndex !== -1 && imageIndex !== currentSlide) {
						setCurrentSlide(imageIndex);
					}
				}
			}
		});
		
		// Clean up subscription when component unmounts
		return () => unsubscribe();
	}, [clientId, currentSlide]);
	

	useEffect(() => {		

		// Only select the current image block if the gallery block is not currently selected
		const { getSelectedBlock, getBlockParents } = wp.data.select('core/block-editor');
		const selectedBlock = getSelectedBlock();
		
		// Check if the gallery block itself is selected
		if (selectedBlock && selectedBlock.clientId === clientId) {
			// Don't change selection if the gallery block is selected
			return;
		}
		
		// Check if any block is selected
		if (!selectedBlock) {
			// If no block is selected (clicked elsewhere), don't change the selection
			return;
		}
		
		// Check if the selected block is a child of this gallery
		const parentBlocks = getBlockParents(selectedBlock.clientId);
		const isChildOfThisGallery = parentBlocks.includes(clientId);
		
		// Only select the current image block if the selected block is not a child of this gallery
		if (!isChildOfThisGallery) {
			// Don't change selection if clicking outside the gallery hierarchy
			return;
		}
		
		// Otherwise, select the current image block
		selectCurrentImageBlock();
	}, [currentSlide, slides, clientId]);
	
	// This effect runs only once when the component mounts
	// It adds a copy event listener to prevent copying the slide-counter element
	// The event listener is removed when the component unmounts
	useEffect(() => {
		const handleCopy = (e) => {
			const selection = window.getSelection();
			if (selection.rangeCount > 0) {
				const range = selection.getRangeAt(0);
				const container = range.commonAncestorContainer;
				
				// Check if the selection contains a slide-counter
				if (container.parentNode && 
					(container.parentNode.classList.contains('slide-counter') || 
					 container.parentNode.hasAttribute('data-no-copy'))) {
					e.preventDefault();
					return false;
				}
			}
		};
		
		// Add the event listener to the document
		document.addEventListener('copy', handleCopy);
		
		// Clean up the event listener when the component unmounts
		return () => {
			document.removeEventListener('copy', handleCopy);
		};
	}, []);

	// Event handlers using React's event system
	const handlePrevClick = () => {
		setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length);
	};
	
	const handleNextClick = () => {
		setCurrentSlide((prev) => (prev + 1) % slides.length);
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