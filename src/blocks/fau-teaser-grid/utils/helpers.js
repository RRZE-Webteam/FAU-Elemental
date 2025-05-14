import { __ } from '@wordpress/i18n';

export function createPagination( currentPage, totalPages, onPageChange ) {
	if ( totalPages <= 1 ) return null;

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
	if ( ! grid ) return;

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
		grid.classList.add( `layout-${ teaserLayout }` );
	}
}
