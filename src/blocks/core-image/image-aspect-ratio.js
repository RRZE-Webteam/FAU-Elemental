( function ( $ ) {

	// Function to enforce 3:2 aspect ratio maximum
	function enforceAspectRatio() {
		// Select images that are in wp-block-image but not in wp-block-gallery
		$( '.wp-block-image:not(.wp-block-gallery .wp-block-image) img' ).each(
			function () {
				const $img = $( this );
				const naturalWidth = this.naturalWidth;
				const naturalHeight = this.naturalHeight;
				const containerWidth = $img.parent().width();

				// Calculate the actual width the image will be displayed at
				const displayWidth = Math.min( naturalWidth, containerWidth );

				// Calculate what the height would be at natural aspect ratio
				const naturalHeightAtWidth =
					( displayWidth / naturalWidth ) * naturalHeight;

				// Calculate what the height would be at 3:2 ratio
				const targetHeight = displayWidth / 1.5;

				// If the natural height at this width would be taller than 3:2, use the target height
				if ( naturalHeightAtWidth > targetHeight ) {
					$img.css( {
						width: displayWidth + 'px',
						height: targetHeight + 'px',
						'object-fit': 'cover',
						'object-position': 'center',
					} );
				} else {
					// Reset to natural dimensions
					$img.css( {
						width: displayWidth + 'px',
						height: 'auto',
						'object-fit': 'none',
						'object-position': 'initial',
					} );
				}
			}
		);
	}

	// Run when images are loaded
	$( window ).on( 'load', enforceAspectRatio );

	// Run when window is resized
	let resizeTimer;
	$( window ).on( 'resize', function () {
		clearTimeout( resizeTimer );
		resizeTimer = setTimeout( function () {
			enforceAspectRatio();
		}, 250 ); // Debounce resize events
	} );
} )( jQuery );
