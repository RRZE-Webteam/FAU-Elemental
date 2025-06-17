/**
 * Image fullscreen functionality
 */

( function ( $ ) {
	/**
	 * Image fullscreen functionality
	 * @param {string} imgSrc - The source URL of the image to display in fullscreen
	 */
	function openImageFullscreen( imgSrc ) {
		const fullscreenContainer = $(
			"<div class='image-fullscreen-container'></div>"
		);
		const img = $( "<img src='" + imgSrc + "'></img>" );
		const closeBtn = $(
			"<button class='image-fullscreen-close'>×</button>"
		);

		// Function to close the fullscreen container
		function closeFullscreen() {
			fullscreenContainer.remove();
			$( document ).off( 'keydown.fullscreen' );
		}

		closeBtn.click( closeFullscreen );

		// Handle ESC key press
		$( document ).on( 'keydown.fullscreen', function ( e ) {
			if ( e.key === 'Escape' ) {
				closeFullscreen();
			}
		} );

		fullscreenContainer.append( img ).append( closeBtn ).appendTo( 'body' );

		// Close when clicking outside the image
		fullscreenContainer.click( function ( e ) {
			if (
				e.target === this ||
				$( e.target ).hasClass( 'image-fullscreen-container' )
			) {
				closeFullscreen();
			}
		} );

		// Prevent clicks on the image from closing the container
		img.click( function ( e ) {
			e.stopPropagation();
		} );
	}

	// Make the function available globally
	window.openImageFullscreen = openImageFullscreen;
} )( jQuery );
