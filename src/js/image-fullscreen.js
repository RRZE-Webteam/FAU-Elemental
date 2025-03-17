( function ( $ ) {
	/**
	 * Image fullscreen functionality
	 */
	function openImageFullscreen( imgSrc ) {
		const fullscreenContainer = $(
			"<div class='image-fullscreen-container'></div>"
		);
		const img = $( "<img src='" + imgSrc + "'></img>" );
		const closeBtn = $(
			"<button class='image-fullscreen-close'>×</button>"
		);

		closeBtn.click( function () {
			fullscreenContainer.remove();
		} );

		fullscreenContainer.append( img ).append( closeBtn ).appendTo( 'body' );

		fullscreenContainer.click( function ( e ) {
			if ( e.target === this ) {
				$( this ).remove();
			}
		} );
	}

	// Make the function available globally
	window.openImageFullscreen = openImageFullscreen;
} )( jQuery );
