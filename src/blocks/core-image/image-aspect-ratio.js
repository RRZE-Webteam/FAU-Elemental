( function ( $ ) {
    console.log('image-aspect-ratio.js loaded');

    // Function to enforce 3:2 aspect ratio maximum
    function enforceAspectRatio() {
        // Select images that are in wp-block-image but not in wp-block-gallery
        $('.wp-block-image:not(.wp-block-gallery .wp-block-image) img').each(function() {
            const $img = $(this);
            const naturalWidth = this.naturalWidth;
            const naturalHeight = this.naturalHeight;

            console.log('naturalWidth', naturalWidth);
            console.log('naturalHeight', naturalHeight);
            
            // Calculate current aspect ratio
            const currentRatio = naturalWidth / naturalHeight;

            console.log('currentRatio', currentRatio);
            
            // If aspect ratio is taller than 3:2 (1.5), adjust the height
            if (currentRatio < 1.5) {
                const newHeight = naturalWidth / 1.5;
                console.log('newHeight', newHeight);
                $img.css({
                    'height': newHeight + 'px',
                    'object-fit': 'cover',
                    'object-position': 'center'
                });
            } else {
                // Reset height if aspect ratio is already correct
                $img.css({
                    'height': 'auto',
                    'object-fit': 'none',
                    'object-position': 'initial'
                });
            }

            // log the height and width and aspect ratio after the css is applied
            console.log('aspect ratio', $img.width() / $img.height());
        });
    }

    // Run on page load
    enforceAspectRatio();

    // Run when images are loaded
    $(window).on('load', enforceAspectRatio);

    // Run when window is resized
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            enforceAspectRatio();
        }, 250); // Debounce resize events
    });

    // Run when images are dynamically added
    // const observer = new MutationObserver(function(mutations) {
    //     mutations.forEach(function(mutation) {
    //         if (mutation.addedNodes.length) {
    //             enforceAspectRatio();
    //         }
    //     });
    // });

    // Start observing the document body for changes
    // observer.observe(document.body, {
    //     childList: true,
    //     subtree: true
    // });
} )( jQuery );
