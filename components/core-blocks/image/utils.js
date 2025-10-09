// Tolerance value for aspect ratio comparison (prevents floating-point precision issues)
const ASPECT_RATIO_TOLERANCE = 0.01;
const TARGET_ASPECT_RATIO = 1.5; // 3:2 aspect ratio
const MIN_ASPECT_RATIO = TARGET_ASPECT_RATIO - ASPECT_RATIO_TOLERANCE; // 1.49

// Function to add tall-image class for images taller than 3:2 aspect ratio
export const addTallImageClass = ( block ) => {
	// We do NOT want this aspect ratio enforcement inside the gallery because it will break in the editor.
	const isInGallery = block.matches(
		'.wp-block-gallery-container .wp-block-image-wrapper'
	);
	if ( isInGallery ) {
		return;
	}

	const img = block.querySelector( 'img' );
	if ( ! img ) {
		return;
	}

	// Check if image has natural dimensions
	if ( img.naturalWidth && img.naturalHeight ) {
		const naturalWidth = img.naturalWidth;
		const naturalHeight = img.naturalHeight;

		// Calculate aspect ratio
		const naturalAspectRatio = naturalWidth / naturalHeight;

		// Determine if class should be added
		// Use tolerance to prevent floating-point precision issues
		// Images with aspect ratio >= MIN_ASPECT_RATIO will not get the class
		const shouldAddClass = naturalAspectRatio < MIN_ASPECT_RATIO;

		// Add or remove the tall-image class
		if ( shouldAddClass ) {
			img.classList.add( 'tall-image' );
		} else {
			img.classList.remove( 'tall-image' );
		}
	}
};
