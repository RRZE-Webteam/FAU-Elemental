/**
 * Customizer URL validation initialization
 *
 * @package
 */

jQuery( document ).ready( function ( $ ) {
	'use strict';

	function addUrlValidation( settingId, controlId ) {
		wp.customize( settingId, function ( setting ) {
			const control = wp.customize.control( controlId );

			if ( ! control.body ) {
				return;
			}

			const input = control.body.find( 'input[type="url"]' );
			if ( ! input.length ) {
				return;
			}

			const messageElement = $(
				'<div class="url-validation-message"></div>'
			);
			input.after( messageElement );

			function validateAndUpdate() {
				const url = input.val();
				const validation = window.fauElementalValidateUrl
					? window.fauElementalValidateUrl( url )
					: { isValid: true, message: '' };

				messageElement.text( validation.message );
				messageElement.removeClass( 'valid invalid' );

				if ( url && ! validation.isValid ) {
					messageElement.addClass( 'invalid' );
					setting.set( '' );
				} else if ( validation.isValid ) {
					messageElement.addClass( 'valid' );
					setting.set( url );
				} else {
					setting.set( '' );
				}
			}

			input.on( 'input blur', validateAndUpdate );
			validateAndUpdate();
		} );
	}

	wp.customize.bind( 'ready', function () {
		[
			'facebook',
			'twitter',
			'instagram',
			'linkedin',
			'youtube',
			'xing',
			'researchgate',
			'mastodon',
			'bluesky',
			'threads',
		].forEach( function ( platform ) {
			const id = 'social_' + platform;
			if ( wp.customize.control( id ) ) {
				addUrlValidation( id, id );
			}
		} );
	} );
} );
