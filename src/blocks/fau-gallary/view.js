/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

/* eslint-disable no-console */
console.log( 'Hello World! (from create-block-fau-gallary block)' );
/* eslint-enable no-console */

document.addEventListener( 'DOMContentLoaded', () => {
	const fullscreenButtons = document.querySelectorAll( '.fullscreen-button' );
	fullscreenButtons.forEach( ( button ) => {
		button.addEventListener( 'click', ( e ) => {
			const imageUrl = e.target
				.closest( '.gallery-item-wrapper' )
				.querySelector( 'img' ).src;
			const fullscreenWindow = window.open( '', '_blank' );
			fullscreenWindow.document.write(
				`<img src="${ imageUrl }" style="width: 100%; height: auto;" />`
			);
		} );
	} );
} );
