document.addEventListener( 'DOMContentLoaded', function () {
	const expandButton = document.querySelector(
		'.footer-expand-button .wp-block-button__link'
	);
	const fauSection = document.querySelector( '.footer-fau-section' );

	if ( expandButton && fauSection ) {
		expandButton.addEventListener( 'click', function () {
			const isExpanded =
				expandButton.getAttribute( 'aria-expanded' ) === 'true';
			expandButton.setAttribute( 'aria-expanded', ! isExpanded );
			fauSection.hidden = isExpanded;
		} );
	}
} );
