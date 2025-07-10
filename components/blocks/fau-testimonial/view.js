// Frontend jQuery carousel functionality
( function ( $ ) {
	$( document ).ready( function () {
		function initCarousel( $container ) {
			const $slides = $container.find( '.testimonial-slide' );
			const $prevButton = $container.find( '.carousel-prev' );
			const $nextButton = $container.find( '.carousel-next' );
			const $dots = $container.find( '.carousel-dots' );

			if ( $slides.length <= 1 ) {
				$prevButton.hide();
				$nextButton.hide();
				$dots.hide();
				return;
			}

			let currentSlide = 0;

			function updateSlides() {
				$slides.each( function ( index ) {
					const isVisible = index === currentSlide;
					$( this ).toggle( isVisible );
					$( this ).attr( 'aria-hidden', ! isVisible );
				} );

				$dots.find( 'button' ).each( function ( index ) {
					$( this ).toggleClass( 'active', index === currentSlide );
				} );
			}

			// Clear existing dots
			$dots.empty();

			// Create dots
			$slides.each( function ( index ) {
				const $dot = $( '<button>' ).attr(
					'aria-label',
					`Go to slide ${ index + 1 }`
				);
				$dot.on( 'click', function () {
					currentSlide = index;
					updateSlides();
				} );
				$dots.append( $dot );
			} );

			$prevButton.on( 'click', function () {
				currentSlide =
					( currentSlide - 1 + $slides.length ) % $slides.length;
				updateSlides();
			} );

			$nextButton.on( 'click', function () {
				currentSlide = ( currentSlide + 1 ) % $slides.length;
				updateSlides();
			} );

			updateSlides();
		}

		// Initialize all carousels on the page
		$( '.testimonial-carousel' ).each( function () {
			initCarousel( $( this ) );
		} );

		// Handle non-carousel testimonial with inner blocks
		$(
			'.wp-block-fau-elemental-fau-testimonial:not(.testimonial-carousel)'
		).each( function () {
			const $innerItems = $( this ).find(
				'.wp-block-fau-elemental-fau-testimonial'
			);
			if ( $innerItems.length > 1 ) {
				// Hide all items except the first one
				$innerItems.each( function ( index ) {
					if ( index > 0 ) {
						$( this ).hide();
					}
				} );
			}
		} );
	} );
} )( jQuery );
