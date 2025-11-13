/**
 * Social Media Custom Icons Handler
 *
 * Sets background images for custom social media icons using data attributes
 *
 * @package
 */

document.addEventListener( 'DOMContentLoaded', function () {
	const customIcons = document.querySelectorAll(
		'.social-links a[data-custom-icon]'
	);

	customIcons.forEach( function ( icon ) {
		const iconUrl = icon.getAttribute( 'data-custom-icon' );
		if ( iconUrl ) {
			icon.style.backgroundImage = 'url("' + iconUrl + '")';
		}
	} );
} );
