( function ( $ ) {
	// Target all gallery blocks instead of just light style
	const $galleries = $( '.wp-block-gallery-container' );

	$galleries.each( function () {
		const $gallery = $( this );
		const $galleryBlock = $gallery.find( '.wp-block-gallery' );
		const $imageBlocks = $galleryBlock.children( '.wp-block-image' );

		// If there's only one image, don't add the slider
		if ( $imageBlocks.length <= 1 ) {
			return;
		}

		// Create a container for the navigation buttons
		const $navContainer = $( '<div>' ).addClass( 'gallery-nav-container' );

		// Create navigation buttons
		const $prevBtn = $( '<button>' )
			.addClass( 'gallery-nav-button prev' )
			.html( '&#10094;' );
		const $nextBtn = $( '<button>' )
			.addClass( 'gallery-nav-button next' )
			.html( '&#10095;' );

		// Add buttons to the container
		$navContainer.append( $prevBtn );
		$navContainer.append( $nextBtn );

		// Append the container to the slider wrapper
		$gallery.append( $navContainer );

		$imageBlocks.hide().first().show();

		let currentSlide = 0;

		// Function to position the navigation container based on the current image height
		const positionNavContainer = function () {
			const windowWidth = $( window ).width();
			const mobileWidth = 768;
			const desktopWidth = 1440;

			const $imageBlock = $imageBlocks.eq( currentSlide );
			const $img = $imageBlock.find( 'img' );
			const $figcaption = $imageBlock.find( 'figcaption' );

			const imgHeight = $img.outerHeight();
			const imgWidth = $img.outerWidth();

			const figcaptionHeight = $figcaption.length
				? $figcaption.outerHeight()
				: 0;
			const figcaptionBottom = $figcaption.length ? 45 : 0;

			const navHeight = $navContainer.outerHeight();
			const navSpace = 20;

			// Check if window width is between sm (768px) and md (1440px) breakpoints
			if ( mobileWidth <= windowWidth && windowWidth < desktopWidth ) {
				const navSpace = 10;
				// Position the nav container below the image
				$navContainer.css( 'top', imgHeight + navSpace + 'px' );

				// Match the width of the navigation container to the imageBlock width
				$navContainer.css( 'width', $imageBlock.outerWidth() + 'px' );

				// Set the height of the gallery to the height of the nav container or the imageBlock height
				const heightWithNav = imgHeight + navSpace + navHeight;
				const heightWithFigcaption =
					imgHeight + figcaptionHeight - figcaptionBottom;
				$gallery.css(
					'height',
					Math.max( heightWithNav, heightWithFigcaption ) + 'px'
				);
			} else if ( windowWidth < mobileWidth ) {
				const navSpace = 10;
				// For small screens, position below the figcaption or below the image if no figcaption
				const navTop = $figcaption.length
					? imgHeight - figcaptionBottom + figcaptionHeight + navSpace
					: imgHeight + navSpace;

				// Position the nav container
				$navContainer.css( 'top', navTop + 'px' );

				// Match the width of the navigation container to the image width
				$navContainer.css( 'width', imgWidth + 'px' );

				// Set the height of the gallery to the height of the nav container or the imageBlock height
				const heightWithNav = imgHeight + navSpace + navHeight;
				const heightWithFigcaption =
					imgHeight +
					figcaptionHeight -
					figcaptionBottom +
					navSpace +
					navHeight;
				$gallery.css(
					'height',
					Math.max( heightWithNav, heightWithFigcaption ) + 'px'
				);
			} else {
				// Position the nav container at the vertical center of the image for large screens
				$navContainer.css(
					'top',
					imgHeight / 2 - navHeight / 2 + 'px'
				);

				// Reset width and positioning for other screen sizes
				$navContainer.css( 'width', '100%' );
				$gallery.css(
					'height',
					imgHeight + figcaptionHeight - figcaptionBottom + 'px'
				);
			}
		};

		// Call the positioning function initially
		positionNavContainer();

		// Navigation functions
		const showSlide = function ( n ) {
			currentSlide = ( n + $imageBlocks.length ) % $imageBlocks.length;
			$imageBlocks.hide();
			$imageBlocks.eq( currentSlide ).show();

			// Reposition the navigation container after changing slides
			positionNavContainer();
		};

		$prevBtn.on( 'click', function () {
			showSlide( currentSlide - 1 );
		} );

		$nextBtn.on( 'click', function () {
			showSlide( currentSlide + 1 );
		} );

		// Reposition on window resize to handle responsive image size changes
		$( window ).on( 'resize', function () {
			positionNavContainer();
		} );
	} );
} )( jQuery );
