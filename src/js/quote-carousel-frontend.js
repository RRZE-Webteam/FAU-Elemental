// Simple carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    const carousels = document.querySelectorAll('.quote-carousel');
    
    carousels.forEach(carousel => {
        const slides = carousel.querySelectorAll('.quote-slide');
        const prevBtn = carousel.querySelector('.prev');
        const nextBtn = carousel.querySelector('.next');
        
        let currentIndex = 0;
        
        function showSlide(index) {
            slides.forEach(slide => slide.style.display = 'none');
            slides[index].style.display = 'block';
        }
        
        showSlide(currentIndex);
        
        nextBtn.onclick = () => {
            currentIndex = (currentIndex + 1) % slides.length;
            showSlide(currentIndex);
        };
        
        prevBtn.onclick = () => {
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            showSlide(currentIndex);
        };
    });
}); 