/**
 * Search Protection Admin JavaScript
 * Handles FULLTEXT index management
 *
 * @global
 */

/* eslint-disable no-undef */

jQuery( document ).ready( function ( $ ) {
	// Ensure ajaxurl is available (WordPress global)
	if ( typeof ajaxurl === 'undefined' ) {
		// eslint-disable-next-line no-global-assign
		ajaxurl = '/wp-admin/admin-ajax.php';
	}

	// Ensure fauSearchProtection object exists
	if ( typeof fauSearchProtection === 'undefined' ) {
		// eslint-disable-next-line no-global-assign
		fauSearchProtection = {
			fulltextNonce: '',
		};
	}

	// FULLTEXT index creation
	$( '#create-fulltext-index' ).on( 'click', function () {
		const button = $( this );
		const status = $( '#create-index-status' );

		button.prop( 'disabled', true ).text( 'Creating...' );
		status.html(
			'<span class="fau-status-info">Creating FULLTEXT index...</span>'
		);

		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'create_fulltext_index',
				nonce: fauSearchProtection.fulltextNonce,
			},
			success( response ) {
				if ( response.success ) {
					status.html(
						'<span class="fau-status-success">✓ ' +
							response.data.message +
							'</span>'
					);
					button
						.text( 'Index Created' )
						.addClass( 'button-secondary' );
					setTimeout( function () {
						location.reload();
					}, 2000 );
				} else {
					status.html(
						'<span class="fau-status-error">✗ ' +
							response.data.message +
							'</span>'
					);
					button.prop( 'disabled', false ).text( 'Retry' );
				}
			},
			error() {
				status.html(
					'<span class="fau-status-error">✗ AJAX request failed. Please try again.</span>'
				);
				button.prop( 'disabled', false ).text( 'Retry' );
			},
		} );
	} );
} );
