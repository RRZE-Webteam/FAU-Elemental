( function ( $ ) {
	// Function to enforce 3:2 aspect ratio maximum
	function enforceAspectRatio() {
		// Select images that are in wp-block-image but not in wp-block-gallery
		$( '.wp-block-image:not(.wp-block-gallery .wp-block-image)' ).each(
			function () {
				const xSmallWidth = 393;
				const $block = $( this );
				const $img = $block.find( 'img' );
				const naturalWidth = $img[ 0 ].naturalWidth;
				const naturalHeight = $img[ 0 ].naturalHeight;
				const containerWidth = $img.parent().width();

				// Calculate maximum allowed height for 3:2 ratio based on container width
				const maxAllowedHeight = containerWidth / 1.5;

				// Calculate what the height would be at natural aspect ratio
				const naturalHeightAtWidth =
					( containerWidth / naturalWidth ) * naturalHeight;

				// If the natural height at container width would be taller than max allowed height
				if (
					naturalHeightAtWidth > maxAllowedHeight &&
					window.innerWidth > xSmallWidth
				) {
					// Calculate the scale factor needed to fit within max allowed height
					const scaleFactor = maxAllowedHeight / naturalHeightAtWidth;
					const scaledWidth = containerWidth * scaleFactor;

					$img.css( {
						width: scaledWidth + 'px',
						height: 'auto',
						'object-fit': 'contain',
						'object-position': 'center',
					} );
				} else {
					// Reset to natural dimensions
					$img.css( {
						width: containerWidth + 'px',
						height: 'auto',
						'object-fit': 'fill',
						'object-position': 'initial',
					} );
				}

				// Calculate total block height including wrapper and figcaption
				const wrapperHeight =
					$block.find( '.image-wrapper' ).outerHeight() || 0;
				const figcaptionHeight =
					$block.find( 'figcaption' ).outerHeight() || 0;
				const figcaptionOffset = $block.find( 'figcaption' ).length
					? 47
					: 0;
				const totalHeight =
					wrapperHeight + figcaptionHeight - figcaptionOffset;

				// Set the calculated height on the block
				$block.css( 'height', totalHeight + 'px' );
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
