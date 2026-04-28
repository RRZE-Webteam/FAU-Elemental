/**
 * Editor Iframe Body Class Injection
 *
 * Mirrors the PHP faue_get_org_classes() logic to inject faculty/website-type
 * body classes into the iframed editor canvas. WordPress's transformStyles
 * scopes editor CSS so that `body.faculty-phil .block` becomes
 * `.editor-styles-wrapper.faculty-phil .block` (same-element compound selector),
 * which only works if the iframe body actually carries these classes.
 *
 * Uses a persistent MutationObserver pattern (inspired by rrze-settings) that
 * survives iframe rebuilds by React. Unlike a one-shot observer, this keeps
 * watching the parent DOM for iframe creation/recreation and monitors the
 * iframe's contentDocument for mutations that might strip classes.
 *
 * @see https://github.com/RRZE-Webteam/rrze-settings/blob/main/src/advanced/block-editor-iframe-body-class-injection.ts
 */

const IFRAME_SELECTOR = 'iframe[name="editor-canvas"]';

/**
 * Build the list of org classes from theme customizer settings.
 */
function getOrgClasses() {
	const { websiteType, facultyType } = window.fauElemental || {};
	const classes = [];

	switch ( websiteType ) {
		case 'fau':
			classes.push( 'fauorg-home' );
			break;
		case 'faculty':
			classes.push( 'fauorg-fakultaet' );
			if ( facultyType ) {
				classes.push( `faculty-${ facultyType }` );
			}
			break;
		case 'chair':
			classes.push( 'fauorg-unterorg' );
			if ( facultyType ) {
				classes.push( `faculty-${ facultyType }` );
			}
			break;
		case 'cooperation':
			classes.push( 'fauorg-kooperation' );
			break;
		case 'cooperation-external':
			classes.push( 'fauorg-kooperation-extern' );
			break;
		case 'other':
			classes.push( 'fauorg-sonstige' );
			break;
	}

	return classes;
}

/**
 * Apply classes to a body element only if not already present.
 */
function ensureClasses( body, classes ) {
	classes.forEach( ( cls ) => {
		if ( ! body.classList.contains( cls ) ) {
			body.classList.add( cls );
		}
	} );
}

/**
 * Start observing. Keeps a persistent watch on the parent document for
 * iframe creation/recreation and monitors the iframe's contentDocument
 * for mutations that might strip classes.
 */
function startObserving( classes ) {
	let activeIframe = null;
	let innerObserver = null;

	function cleanupInnerObserver() {
		if ( innerObserver ) {
			innerObserver.disconnect();
			innerObserver = null;
		}
	}

	function observeIframeContent( iframe ) {
		cleanupInnerObserver();
		activeIframe = iframe;

		try {
			const iframeDoc = iframe.contentDocument;
			if ( ! iframeDoc?.body ) {
				return;
			}

			ensureClasses( iframeDoc.body, classes );

			innerObserver = new MutationObserver( () => {
				if ( iframeDoc?.body ) {
					ensureClasses( iframeDoc.body, classes );
				}
			} );

			innerObserver.observe( iframeDoc.body, {
				attributes: true,
				attributeFilter: [ 'class' ],
			} );
		} catch ( e ) {
			// Cross-origin iframe access may fail in some edge cases.
		}
	}

	function checkIframe() {
		const iframe = document.querySelector( IFRAME_SELECTOR );

		if ( ! iframe ) {
			// Iframe removed — clean up inner observer.
			if ( activeIframe ) {
				cleanupInnerObserver();
				activeIframe = null;
			}
			return;
		}

		if ( iframe !== activeIframe ) {
			// New or rebuilt iframe detected.
			observeIframeContent( iframe );

			// Also re-apply on iframe load (e.g. navigation within the editor).
			iframe.addEventListener( 'load', () => {
				observeIframeContent( iframe );
			} );
		}
	}

	// Initial check.
	checkIframe();

	// Persistent observer on the parent document — never disconnects.
	const parentObserver = new MutationObserver( () => {
		checkIframe();
	} );

	parentObserver.observe( document.body, {
		childList: true,
		subtree: true,
	} );
}

// Initialize.
const orgClasses = getOrgClasses();
if ( orgClasses.length ) {
	startObserving( orgClasses );
}
