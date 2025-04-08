// Simple carousel functionality
document.addEventListener( 'DOMContentLoaded', function () {
	console.log( 'quote-carousel' );
	function initCarousel( container ) {
		const slides = container.querySelectorAll( '.quote-slide' );
		const prevButton = container.querySelector( '.carousel-prev' );
		const nextButton = container.querySelector( '.carousel-next' );
		const dots = container.querySelector( '.carousel-dots' );

		if ( slides.length <= 1 ) {
			if ( prevButton ) prevButton.style.display = 'none';
			if ( nextButton ) nextButton.style.display = 'none';
			if ( dots ) dots.style.display = 'none';
			return;
		}

		let currentSlide = 0;

		function updateSlides() {
			slides.forEach( ( slide, index ) => {
				slide.style.display = index === currentSlide ? 'block' : 'none';
			} );

			const dotButtons = dots.querySelectorAll( 'button' );
			dotButtons.forEach( ( dot, index ) => {
				dot.classList.toggle( 'active', index === currentSlide );
			} );
		}

		// Clear existing dots
		dots.innerHTML = '';

		// Create dots
		slides.forEach( ( _, index ) => {
			const dot = document.createElement( 'button' );
			dot.setAttribute( 'aria-label', `Go to slide ${ index + 1 }` );
			dot.addEventListener( 'click', () => {
				currentSlide = index;
				updateSlides();
			} );
			dots.appendChild( dot );
		} );

		prevButton.addEventListener( 'click', () => {
			currentSlide = ( currentSlide - 1 + slides.length ) % slides.length;
			updateSlides();
		} );

		nextButton.addEventListener( 'click', () => {
			currentSlide = ( currentSlide + 1 ) % slides.length;
			updateSlides();
		} );

		updateSlides();
	}

	// Initialize all carousels on the page
	document.querySelectorAll( '.quote-carousel' ).forEach( ( carousel ) => {
		initCarousel( carousel );
	} );

	// Handle non-carousel quotes with inner blocks
	const regularQuotes = document.querySelectorAll(
		'.wp-block-quote:not(.quote-carousel)'
	);
	regularQuotes.forEach( ( quote ) => {
		const innerQuotes = quote.querySelectorAll( '.wp-block-quote' );
		if ( innerQuotes.length > 1 ) {
			// Hide all quotes except the first one
			innerQuotes.forEach( ( innerQuote, index ) => {
				if ( index > 0 ) {
					innerQuote.style.display = 'none';
				}
			} );
		}
	} );
} );
