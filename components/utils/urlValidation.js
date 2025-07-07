import { __ } from '@wordpress/i18n';
import { isURL } from '@wordpress/url';

/**
 * URL validation helper
 * 
 * @param {string} url - The URL to validate
 * @returns {Object} - Object with isValid boolean and message string
 */
export const validateUrl = ( url ) => {
	if ( ! url ) {
		return {
			isValid: true, // Empty URL is valid (optional field)
			message: '',
		};
	}
	if ( ! isURL( url ) ) {
		return {
			isValid: false,
			message: __( 'Please enter a valid URL', 'fau-elemental' ),
		};
	}
	return { isValid: true, message: '' };
}; 