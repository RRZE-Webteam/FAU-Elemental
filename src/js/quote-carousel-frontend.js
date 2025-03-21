// Simple carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle carousels
    const carousels = document.querySelectorAll('.quote-carousel');
    carousels.forEach(carousel => {
        const slides = carousel.querySelectorAll('.quote-carousel-slides > .wp-block-quote');
        const prevBtn = carousel.querySelector('.prev');
        const nextBtn = carousel.querySelector('.next');
        
        if (!slides.length) return;
        
        let currentIndex = 0;
        
        // Set initial styles for all slides
        slides.forEach((slide, index) => {
            slide.style.display = index === 0 ? 'block' : 'none';
        });
        
        function showSlide(index) {
            slides.forEach(slide => slide.style.display = 'none');
            slides[index].style.display = 'block';
        }
        
        if (prevBtn && nextBtn && slides.length > 1) {
            // Show navigation only if there are multiple slides
            carousel.querySelector('.quote-carousel-nav').style.display = 'flex';
            
            nextBtn.onclick = () => {
                currentIndex = (currentIndex + 1) % slides.length;
                showSlide(currentIndex);
            };
            
            prevBtn.onclick = () => {
                currentIndex = (currentIndex - 1 + slides.length) % slides.length;
                showSlide(currentIndex);
            };
        } else {
            // Hide navigation if there's only one slide
            carousel.querySelector('.quote-carousel-nav').style.display = 'none';
        }
    });

    // Handle non-carousel quotes with inner blocks
    const regularQuotes = document.querySelectorAll('.wp-block-quote:not(.quote-carousel)');
    regularQuotes.forEach(quote => {
        const innerQuotes = quote.querySelectorAll('.wp-block-quote');
        if (innerQuotes.length > 1) {
            // Hide all quotes except the first one
            innerQuotes.forEach((innerQuote, index) => {
                if (index > 0) {
                    innerQuote.style.display = 'none';
                }
            });
        }
    });
}); 