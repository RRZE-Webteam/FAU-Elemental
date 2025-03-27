
document.addEventListener("DOMContentLoaded", () => {
    
    // Target all gallery blocks instead of just light style
    const galleries = document.querySelectorAll(".wp-block-gallery");
  
    galleries.forEach((gallery) => {
        const figures = Array.from(gallery.querySelectorAll(":scope > figure.wp-block-image"));
  
        if (figures.length === 0 || gallery.querySelector(".gallery-slider-wrapper")) {
            return;
        }
  
        // Create slider structure
        const sliderWrapper = document.createElement("div");
        sliderWrapper.className = "gallery-slider-wrapper";
        
        const slideContainer = document.createElement("div");
        slideContainer.className = "gallery-slider";
  
        let currentSlide = 0;
  
        figures.forEach((figure, index) => {
            const slide = document.createElement("div");
            slide.className = "gallery-slide";
            slide.style.display = index === 0 ? "block" : "none";
            
            // Clone the figure
            const clonedFigure = figure.cloneNode(true);
            
            // Add counter
            const counter = document.createElement("div");
            counter.className = "slide-counter";
            counter.textContent = `${index + 1}/${figures.length}`;
            
            // Update caption if it exists
            const caption = clonedFigure.querySelector('figcaption');
            if (caption) {
                caption.appendChild(counter);
            }
    
            slide.appendChild(clonedFigure);
            slideContainer.appendChild(slide);
        });
  
        // Create navigation buttons
        const prevBtn = document.createElement("button");
        const nextBtn = document.createElement("button");
        prevBtn.className = "gallery-nav-button prev";
        nextBtn.className = "gallery-nav-button next";
        prevBtn.innerHTML = "&#10094;"; // Left arrow
        nextBtn.innerHTML = "&#10095;"; // Right arrow
  
        // Navigation functions
        const showSlide = (n) => {
            const slides = slideContainer.querySelectorAll('.gallery-slide');
            currentSlide = (n + slides.length) % slides.length;
            
            slides.forEach(slide => slide.style.display = "none");
            slides[currentSlide].style.display = "block";
        };
  
        prevBtn.addEventListener("click", () => {
            showSlide(currentSlide - 1);
        });
  
        nextBtn.addEventListener("click", () => {
            showSlide(currentSlide + 1);
        });
  
        // Add keyboard navigation
        document.addEventListener("keydown", (e) => {
            if (e.key === "ArrowLeft") {
                showSlide(currentSlide - 1);
            } else if (e.key === "ArrowRight") {
                showSlide(currentSlide + 1);
            }
        });
  
        // Assemble the slider
        sliderWrapper.appendChild(slideContainer);
        sliderWrapper.appendChild(prevBtn);
        sliderWrapper.appendChild(nextBtn);
  
        // Replace gallery content with slider
        gallery.innerHTML = "";
        gallery.appendChild(sliderWrapper);
    });
});  
