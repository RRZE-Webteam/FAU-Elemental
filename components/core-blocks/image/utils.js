// Breakpoint for x-small (mobile)
const xSmallWidth = 393;

// Function to enforce 3:2 aspect ratio maximum
export const enforceImageAspectRatio = ( block ) => {
	// We do NOT want this aspect ratio enforcement inside the gallery because it will break in the editor.
	const isInGallery = block.matches(
		'.wp-block-gallery-container .wp-block-image-wrapper'
	);
	if ( isInGallery ) return;

	const img = block.querySelector( 'img' );
	if ( ! img ) return;

	const naturalWidth = img.naturalWidth;
	const naturalHeight = img.naturalHeight;
	const containerWidth = img.parentElement.offsetWidth;

	// Calculate maximum allowed height for 3:2 ratio based on container width
	const maxAllowedHeight = containerWidth / 1.5;

	// Calculate what the height would be at natural aspect ratio
	const naturalHeightAtWidth =
		( containerWidth / naturalWidth ) * naturalHeight;

	// Get the window width. Gutenberg uses an iFrame if all Blocks are API >= 3, so we need to
	// check the view of our image instead of directly using window.innerWidth.
	// We only use it as a fallback.
	const windowWidth =
		img.ownerDocument?.defaultView?.innerWidth || window.innerWidth;

	// If the natural height at container width would be taller than max allowed height and we are not on mobile
	if (
		naturalHeightAtWidth > maxAllowedHeight &&
		windowWidth > xSmallWidth
	) {
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
		img.style.objectFit = 'fill';
		img.style.objectPosition = 'initial';
	}

	// Calculate total block height including wrapper and figcaption
	const wrapper = block.querySelector( '.image-wrapper' );
	const figcaption = block.querySelector( 'figcaption' );
	const wrapperHeight = wrapper ? wrapper.offsetHeight : 0;
	const figcaptionHeight = figcaption ? figcaption.offsetHeight : 0;
	const figcaptionOffset = figcaption ? 47 : 0;
	const totalHeight = wrapperHeight + figcaptionHeight - figcaptionOffset;

	// Set the calculated height on the block
	block.style.height = `${ totalHeight }px`;
};
