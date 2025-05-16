// Frontend jQuery carousel functionality
( function ( $ ) {
	console.log( 'quote-carousel' );
	$( document ).ready( function () {
		function initCarousel( $container ) {
			const $slides = $container.find( '.quote-slide' );
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
		$( '.quote-carousel' ).each( function () {
			initCarousel( $( this ) );
		} );

		// Handle non-carousel quotes with inner blocks
		$( '.wp-block-quote:not(.quote-carousel)' ).each( function () {
			const $innerQuotes = $( this ).find( '.wp-block-quote' );
			if ( $innerQuotes.length > 1 ) {
				// Hide all quotes except the first one
				$innerQuotes.each( function ( index ) {
					if ( index > 0 ) {
						$( this ).hide();
					}
				} );
			}
		} );
	} );
} )( jQuery );
