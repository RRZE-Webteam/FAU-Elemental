import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

export default function QuoteCarousel( {
	children,
	selectedIndex = 0,
	onSlideChange,
} ) {
	const [ currentSlide, setCurrentSlide ] = useState( selectedIndex );
	const [ slides, setSlides ] = useState( [] );
	const carouselRef = useRef( null );

	useEffect( () => {
		if ( carouselRef.current ) {
			const slideElements = Array.from(
				carouselRef.current.querySelectorAll( '.quote-slide' )
			);
			setSlides( slideElements );
		}
	}, [ children ] );

	// Update currentSlide when selectedIndex changes from parent
	useEffect( () => {
		setCurrentSlide( selectedIndex );
	}, [ selectedIndex ] );

	const handlePrevClick = () => {
		const newIndex = ( currentSlide - 1 + slides.length ) % slides.length;
		setCurrentSlide( newIndex );
		if ( onSlideChange ) {
			onSlideChange( newIndex );
		}
	};

	const handleNextClick = () => {
		const newIndex = ( currentSlide + 1 ) % slides.length;
		setCurrentSlide( newIndex );
		if ( onSlideChange ) {
			onSlideChange( newIndex );
		}
	};

	const handleDotClick = ( index ) => {
		setCurrentSlide( index );
		if ( onSlideChange ) {
			onSlideChange( index );
		}
	};

	// Update slide visibility
	useEffect( () => {
		slides.forEach( ( slide, index ) => {
			if ( slide ) {
				const isVisible = index === currentSlide;
				slide.style.display = isVisible ? 'block' : 'none';
				slide.setAttribute( 'aria-hidden', ! isVisible );
			}
		} );
	}, [ currentSlide, slides ] );

	// Hide navigation if only one slide
	const showNavigation = slides.length > 1;

	return (
		<div className="quote-carousel" ref={ carouselRef }>
			{ children }
			{ showNavigation && (
				<div className="carousel-controls">
					<button
						className="carousel-prev"
						aria-label={ __( 'Previous slide', 'fau-elemental' ) }
						onClick={ handlePrevClick }
					>
						❮
					</button>
					<div className="carousel-dots">
						{ slides.map( ( _, index ) => (
							<button
								key={ index }
								className={
									index === currentSlide ? 'active' : ''
								}
								aria-label={ sprintf(
									// translators: %s: slide index
									__( `Go to slide %s`, 'fau-elemental' ),
									index + 1
								) }
								onClick={ () => handleDotClick( index ) }
							/>
						) ) }
					</div>
					<button
						className="carousel-next"
						aria-label={ __( 'Next slide', 'fau-elemental' ) }
						onClick={ handleNextClick }
					>
						❯
					</button>
				</div>
			) }
		</div>
	);
}
