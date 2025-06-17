import { __ } from '@wordpress/i18n';

/**
 * Performs a shallow comparison between two objects or arrays
 * Used for memoization purposes to prevent unnecessary re-renders
 *
 * @param {Object|Array} obj1 First object to compare
 * @param {Object|Array} obj2 Second object to compare
 * @return {boolean} Whether the objects are shallowly equal
 */
export function shallowEqual( obj1, obj2 ) {
	if ( obj1 === obj2 ) {
		return true;
	}

	if (
		typeof obj1 !== 'object' ||
		typeof obj2 !== 'object' ||
		obj1 === null ||
		obj2 === null
	) {
		return false;
	}

	const keys1 = Object.keys( obj1 );
	const keys2 = Object.keys( obj2 );

	if ( keys1.length !== keys2.length ) {
		return false;
	}

	for ( const key of keys1 ) {
		if ( ! obj2.hasOwnProperty( key ) || obj1[ key ] !== obj2[ key ] ) {
			return false;
		}
	}

	return true;
}

export function createPagination( currentPage, totalPages, onPageChange ) {
	if ( totalPages <= 1 ) {
		return null;
	}

	const pages = [];
	const maxVisiblePages = 5;
	const halfVisible = Math.floor( maxVisiblePages / 2 );

	// Previous button
	pages.push(
		<a
			key="prev"
			href="#"
			className={ `page-numbers prev ${
				currentPage === 1 ? 'disabled' : ''
			}` }
			onClick={ ( e ) => {
				e.preventDefault();
				if ( currentPage > 1 ) {
					onPageChange( currentPage - 1 );
				}
			} }
		>
			{ __( 'Prev', 'fau-elemental' ) }
		</a>
	);

	// Page numbers
	for ( let i = 1; i <= totalPages; i++ ) {
		if (
			i === 1 || // First page
			i === totalPages || // Last page
			( i >= currentPage - halfVisible && i <= currentPage + halfVisible ) // Pages around current
		) {
			pages.push(
				<a
					key={ i }
					href="#"
					className={ `page-numbers ${
						currentPage === i ? 'current' : ''
					}` }
					onClick={ ( e ) => {
						e.preventDefault();
						onPageChange( i );
					} }
				>
					{ i }
				</a>
			);
		} else if (
			i === currentPage - halfVisible - 1 ||
			i === currentPage + halfVisible + 1
		) {
			pages.push(
				<span key={ i } className="page-numbers dots">
					...
				</span>
			);
		}
	}

	// Next button
	pages.push(
		<a
			key="next"
			href="#"
			className={ `page-numbers next ${
				currentPage === totalPages ? 'disabled' : ''
			}` }
			onClick={ ( e ) => {
				e.preventDefault();
				if ( currentPage < totalPages ) {
					onPageChange( currentPage + 1 );
				}
			} }
		>
			{ __( 'Next', 'fau-elemental' ) }
		</a>
	);

	return <div className="pagination">{ pages }</div>;
}

export function updateGridClasses( grid, displayStyle, teaserLayout ) {
	if ( ! grid ) {
		return;
	}

	// First, remove all existing classes
	grid.className = '';

	// Add the base class
	grid.classList.add( 'fau-teaser-grid' );

	// Add the display style class
	if ( displayStyle ) {
		grid.classList.add( displayStyle );
	}

	// Only add layout classes if we're in teaser-grid mode
	if ( displayStyle === 'teaser-grid' && teaserLayout ) {
		// Handle special cases for 2s-left and 2s-right layouts
		if ( teaserLayout === '2s-left' || teaserLayout === '2s-right' ) {
			grid.classList.add( 'layout-2s' );
			grid.classList.add( `layout-${ teaserLayout }` );
		} else {
			grid.classList.add( `layout-${ teaserLayout }` );
		}
	}
}
