( function ( $ ) {
    // Target all gallery blocks instead of just light style
    const $galleries = $(".wp-block-gallery-container");

    $galleries.each(function() {
        const $gallery = $(this);
        const $galleryBlock = $gallery.find(".wp-block-gallery");
        const $imageblocks = $galleryBlock.children(".wp-block-image");

        // If there's only one image, don't add the slider
        if ($imageblocks.length <= 1) {
            return;
        }  
  
        // Create a container for the navigation buttons
        const $navContainer = $("<div>").addClass("gallery-nav-container");

        // Create navigation buttons
        const $prevBtn = $("<button>").addClass("gallery-nav-button prev").html("&#10094;");
        const $nextBtn = $("<button>").addClass("gallery-nav-button next").html("&#10095;");

        // Add buttons to the container
        $navContainer.append($prevBtn);
        $navContainer.append($nextBtn);
        
        // Append the container to the slider wrapper
        $gallery.append($navContainer);

        $imageblocks.hide().first().show();
  
        let currentSlide = 0;

        // Function to position the navigation container based on the current image height
        const positionNavContainer = function() {
            const $currentImage = $imageblocks.eq(currentSlide);
            const $img = $currentImage.find('img');
            const $figcaption = $currentImage.find('figcaption');
            const imageHeight = $img.outerHeight();
            const imageWidth = $img.outerWidth();
            const navHeight = $navContainer.outerHeight();
            const windowWidth = $(window).width();
            
            // Check if window width is between sm (768px) and md (1440px) breakpoints
            if (windowWidth >= 768 && windowWidth < 1440) {
                // Position the nav container below the image
                $navContainer.css('top', imageHeight + 20 + 'px'); // 20px gap between image and nav
                
                // Match the width of the navigation container to the image width
                $navContainer.css('width', imageWidth + 'px');
            } else if (windowWidth < 768) {
                // For small screens, position below the figcaption or below the image if no figcaption
                const figcaptionHeight = $figcaption.length ? $figcaption.outerHeight() : 0;
                const figcaptionBottom = 45; // This is the bottom position of the figcaption in CSS

                const navTop = $figcaption.length ? 
                    imageHeight - figcaptionBottom + figcaptionHeight + 10 :
                    imageHeight + 20;

                // Position the nav container
                $navContainer.css('top', navTop + 'px');
                
                // Match the width of the navigation container to the image width
                $navContainer.css('width', imageWidth + 'px');
            } else {
                // Position the nav container at the vertical center of the image for large screens
                $navContainer.css('top', (imageHeight / 2) - (navHeight / 2) + 'px');
                
                // Reset width and positioning for other screen sizes
                $navContainer.css('width', '100%');
            }
        };

        // Call the positioning function initially
        positionNavContainer();

        // Navigation functions
        const showSlide = function(n) {
            currentSlide = (n + $imageblocks.length) % $imageblocks.length;            
            $imageblocks.hide();
            $imageblocks.eq(currentSlide).show();
            
            // Reposition the navigation container after changing slides
            positionNavContainer();
        };
  
        $prevBtn.on("click", function() {
            showSlide(currentSlide - 1);
        });
  
        $nextBtn.on("click", function() {
            showSlide(currentSlide + 1);
        });

        // Reposition on window resize to handle responsive image size changes
        $(window).on('resize', function() {
            positionNavContainer();
        });
    });
} )( jQuery );
