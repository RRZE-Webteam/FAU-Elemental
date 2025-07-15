import { __ } from '@wordpress/i18n';

/**
 * Processes event date string and returns formatted display and machine-readable data
 *
 * @param {string} eventDate - Date string in format "DD MM YYYY"
 * @return {Object} Object containing day, monthYear, and datetimeAttr
 */
export function processEventDate( eventDate ) {
	// Split the date into day and month/year
	const dateParts = eventDate
		? eventDate.split( ' ' ).filter( ( part ) => part.trim() !== '' )
		: [ '01', '1', '2024' ];

	const day = dateParts[ 0 ] || '';
	const month = dateParts[ 1 ] || '';
	const year = dateParts[ 2 ] || '';

	// Convert numeric months to localized abbreviations for display
	const numberToMonth = {
		'01': __( 'Jan', 'fau-elemental' ),
		'02': __( 'Feb', 'fau-elemental' ),
		'03': __( 'Mar', 'fau-elemental' ),
		'04': __( 'Apr', 'fau-elemental' ),
		'05': __( 'May', 'fau-elemental' ),
		'06': __( 'Jun', 'fau-elemental' ),
		'07': __( 'Jul', 'fau-elemental' ),
		'08': __( 'Aug', 'fau-elemental' ),
		'09': __( 'Sep', 'fau-elemental' ),
		10: __( 'Oct', 'fau-elemental' ),
		11: __( 'Nov', 'fau-elemental' ),
		12: __( 'Dec', 'fau-elemental' ),
		1: __( 'Jan', 'fau-elemental' ),
		2: __( 'Feb', 'fau-elemental' ),
		3: __( 'Mar', 'fau-elemental' ),
		4: __( 'Apr', 'fau-elemental' ),
		5: __( 'May', 'fau-elemental' ),
		6: __( 'Jun', 'fau-elemental' ),
		7: __( 'Jul', 'fau-elemental' ),
		8: __( 'Aug', 'fau-elemental' ),
		9: __( 'Sep', 'fau-elemental' ),
	};

	// Convert numeric month to localized abbreviation for display
	const localizedMonth = numberToMonth[ month ] || month;

	// Build monthYear string, handling incomplete dates gracefully
	let monthYear = '';
	if ( localizedMonth && year ) {
		monthYear = `${ localizedMonth } ${ year }`;
	} else if ( localizedMonth ) {
		monthYear = localizedMonth;
	} else if ( year ) {
		monthYear = year;
	}

	// Create machine-readable datetime attribute (YYYY-MM-DD format)
	let datetimeAttr = '';
	if ( eventDate && day && month && year ) {
		const yearValue = year;
		const monthNumber = month.padStart( 2, '0' );
		const dayPadded = day.padStart( 2, '0' );
		datetimeAttr = `${ yearValue }-${ monthNumber }-${ dayPadded }`;
	}

	return {
		day,
		monthYear,
		datetimeAttr,
	};
}
