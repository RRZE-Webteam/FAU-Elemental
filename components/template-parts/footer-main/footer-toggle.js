/**
 * Footer toggle functionality
 * Handles collapsible FAU info section in footer
 */
document.addEventListener( 'DOMContentLoaded', function () {
	const toggleButton = document.querySelector( '.fau-info-toggle' );
	const fauInfoSection = document.querySelector( '.fau-info-section' );

	// Only initialize toggle functionality if elements exist (FAU info section is not hidden)
	if ( toggleButton && fauInfoSection ) {
		// Get localized strings from wp_localize_script
		const strings = window.fauFooterStrings || {
			showMore: 'Show More',
			showLess: 'Show Less',
		};

		toggleButton.addEventListener( 'click', function () {
			const isExpanded =
				toggleButton.getAttribute( 'aria-expanded' ) === 'true';
			toggleButton.setAttribute( 'aria-expanded', ! isExpanded );
			fauInfoSection.hidden = isExpanded;

			// Update button text based on state
			const currentText = isExpanded
				? strings.showMore
				: strings.showLess;

			// Update button text while preserving the icon
			toggleButton.innerHTML =
				currentText +
				'<span class="toggle-icon" aria-hidden="true"></span>';
		} );
	}
} ); 