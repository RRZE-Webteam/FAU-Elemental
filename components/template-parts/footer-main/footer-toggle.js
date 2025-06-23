/**
 * Footer toggle functionality
 * Handles collapsible FAU info section in footer
 * Implements proper accessibility with progressive enhancement
 */
document.addEventListener( 'DOMContentLoaded', function () {
	document.documentElement.classList.add( 'js' );

	const toggleButton = document.querySelector( '.fau-info-toggle' );
	const fauInfoSection = document.querySelector( '.fau-info-section' );

	if ( toggleButton && fauInfoSection ) {
		const strings = window.fauFooterStrings || {
			showMore: 'Mehr anzeigen',
			showLess: 'Weniger anzeigen',
		};

		fauInfoSection.hidden = true;
		toggleButton.setAttribute( 'aria-expanded', 'false' );

		const initialText = toggleButton.textContent.trim();
		toggleButton.innerHTML =
			'<span class="toggle-text">' +
			initialText +
			'</span>';

		toggleButton.addEventListener( 'click', function () {
			fauInfoSection.hidden = ! fauInfoSection.hidden;

			const isExpanded = ! fauInfoSection.hidden;
			toggleButton.setAttribute( 'aria-expanded', isExpanded.toString() );

			const toggleTextElement =
				toggleButton.querySelector( '.toggle-text' );

			if ( toggleTextElement ) {
				toggleTextElement.textContent = isExpanded
					? strings.showLess
					: strings.showMore;
			}
		} );
	}
} );
