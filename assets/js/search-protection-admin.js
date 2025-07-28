/**
 * Search Protection Admin JavaScript
 * Handles cache clearing and FULLTEXT index management
 *
 * @global
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
			nonce: '',
			fulltextNonce: '',
		};
	}
	// Cache clearing functionality
	function clearCache( clearAll ) {
		const status = $( '#clear-cache-status' );

		if ( ! clearAll ) {
			// Check if at least one option is selected
			if (
				! $( '#clear-search-results' ).is( ':checked' ) &&
				! $( '#clear-recent-searches' ).is( ':checked' ) &&
				! $( '#clear-rate-limits' ).is( ':checked' ) &&
				! $( '#clear-detailed-logs' ).is( ':checked' )
			) {
				status.html(
					'<span class="fau-status-error">✗ Please select at least one option to clear.</span>'
				);
				return;
			}
		}

		const button = $( '#clear-search-cache' );
		button.prop( 'disabled', true ).text( 'Clearing...' );
		$( '#clear-all-cache' ).prop( 'disabled', true );
		status.html( '<span class="fau-status-info">Clearing cache...</span>' );

		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'clear_search_cache',
				nonce: fauSearchProtection.nonce,
				clear_all: clearAll ? 1 : 0,
				clear_search_results: clearAll
					? 1
					: $( '#clear-search-results' ).is( ':checked' )
					? 1
					: 0,
				clear_recent_searches: clearAll
					? 1
					: $( '#clear-recent-searches' ).is( ':checked' )
					? 1
					: 0,
				clear_rate_limits: clearAll
					? 1
					: $( '#clear-rate-limits' ).is( ':checked' )
					? 1
					: 0,
				clear_detailed_logs: clearAll
					? 1
					: $( '#clear-detailed-logs' ).is( ':checked' )
					? 1
					: 0,
			},
			success( response ) {
				if ( response.success ) {
					status.html(
						'<span class="fau-status-success">✓ ' +
							response.data.message +
							'</span>'
					);
					button.text( 'Cache Cleared' ).addClass( 'button-primary' );
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
					$( '#clear-all-cache' ).prop( 'disabled', false );
				}
			},
			error() {
				status.html(
					'<span class="fau-status-error">✗ AJAX request failed. Please try again.</span>'
				);
				button.prop( 'disabled', false ).text( 'Retry' );
				$( '#clear-all-cache' ).prop( 'disabled', false );
			},
		} );
	}

	// Clear selected cache button
	$( '#clear-search-cache' ).on( 'click', function () {
		clearCache( false );
	} );

	// Clear all cache button
	$( '#clear-all-cache' ).on( 'click', function () {
		// Clear all cache regardless of checkbox states
		const button = $( this );
		const status = $( '#clear-cache-status' );

		button.prop( 'disabled', true ).text( 'Clearing All...' );
		$( '#clear-search-cache' ).prop( 'disabled', true );
		status.html(
			'<span class="fau-status-info">Clearing all cache...</span>'
		);

		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'clear_search_cache',
				nonce: fauSearchProtection.nonce,
				clear_all: 1,
				clear_search_results: 1,
				clear_recent_searches: 1,
				clear_rate_limits: 1,
				clear_detailed_logs: 1,
			},
			success( response ) {
				if ( response.success ) {
					status.html(
						'<span class="fau-status-success">✓ ' +
							response.data.message +
							'</span>'
					);
					button
						.text( 'All Cache Cleared' )
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
					button.prop( 'disabled', false ).text( 'Clear All Cache' );
					$( '#clear-search-cache' ).prop( 'disabled', false );
				}
			},
			error() {
				status.html(
					'<span class="fau-status-error">✗ AJAX request failed. Please try again.</span>'
				);
				button.prop( 'disabled', false ).text( 'Clear All Cache' );
				$( '#clear-search-cache' ).prop( 'disabled', false );
			},
		} );
	} );

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
