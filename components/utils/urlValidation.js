import { __ } from '@wordpress/i18n';
import { isURL } from '@wordpress/url';

/**
 * URL validation helper
 *
 * @param {string}  url      - The URL to validate
 * @param {boolean} required - Whether the URL is required (default: false)
 * @return {Object} - Object with isValid boolean and message string
 */
export const validateUrl = ( url, required = false ) => {
	if ( ! url ) {
		if ( required ) {
			return {
				isValid: false,
				message: __( 'URL is required', 'fau-elemental' ),
			};
		}
		return {
			isValid: true, // Empty URL is valid (optional field)
			message: '',
		};
	}

	// Check for obvious non-URL text patterns first
	if ( /^[a-zA-Z\s]+$/.test( url ) ) {
		return {
			isValid: false,
			message: __(
				'Please enter a valid URL (not just text)',
				'fau-elemental'
			),
		};
	}

	const urlLower = url.toLowerCase();

	// Special cases: mailto:, .ics files, and feed URLs
	// Allow mailto: links
	if ( urlLower.startsWith( 'mailto:' ) ) {
		// Validate mailto: format (mailto:email@domain.com)
		if ( /^mailto:[^\s@]+@[^\s@]+\.[^\s@]+/.test( url ) ) {
			return { isValid: true, message: '' };
		}
		return {
			isValid: false,
			message: __( 'Please enter a valid email address', 'fau-elemental' ),
		};
	}

	// Allow feed URLs (contains /feed)
	if ( urlLower.includes( '/feed' ) ) {
		// If no scheme, add https:// for validation
		let urlToValidate = url;
		if ( ! /^https?:\/\//.test( url ) ) {
			urlToValidate = 'https://' + url;
		}
		// Basic validation - check if it looks like a URL
		try {
			new URL( urlToValidate );
			return { isValid: true, message: '' };
		} catch {
			return {
				isValid: false,
				message: __( 'Please enter a valid URL', 'fau-elemental' ),
			};
		}
	}

	// Allow .ics files (calendar files)
	if ( urlLower.endsWith( '.ics' ) ) {
		// If no scheme, add https:// for validation
		let urlToValidate = url;
		if ( ! /^https?:\/\//.test( url ) ) {
			urlToValidate = 'https://' + url;
		}
		// Basic validation - check if it looks like a URL
		try {
			new URL( urlToValidate );
			return { isValid: true, message: '' };
		} catch {
			return {
				isValid: false,
				message: __( 'Please enter a valid URL', 'fau-elemental' ),
			};
		}
	}

	// For regular URLs, use standard validation
	if ( ! isURL( url ) ) {
		return {
			isValid: false,
			message: __( 'Please enter a valid URL', 'fau-elemental' ),
		};
	}
	return { isValid: true, message: '' };
};

// Make the function available globally for customizer use
if ( typeof window !== 'undefined' ) {
	window.fauElementalValidateUrl = validateUrl;
}
