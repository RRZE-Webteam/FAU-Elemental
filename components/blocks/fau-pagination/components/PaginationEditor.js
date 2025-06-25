import { __ } from '@wordpress/i18n';

/**
 * Shared FAU Pagination Component for Editor Use
 * Replicates the structure and styling of the fau-pagination block
 * Can be imported and used by other blocks that need pagination
 */
export const PaginationEditor = ( {
	currentPage,
	totalPages,
	onPageChange,
	variant = 'basic',
} ) => {
	if ( totalPages <= 1 ) {
		return null;
	}

	// Load More variant
	if ( variant === 'load-more' ) {
		return (
			<div className="wp-block-fau-elemental-fau-pagination pagination load-more">
				<button
					className="load-more-button"
					onClick={ ( e ) => {
						e.preventDefault();
						if ( currentPage < totalPages ) {
							onPageChange( currentPage + 1 );
						}
					} }
					disabled={ currentPage === totalPages }
				>
					{ __( 'Load More', 'fau-elemental' ) }
				</button>
			</div>
		);
	}

	// Basic Pagination variant
	const generatePageNumbers = () => {
		const pages = [];

		if ( totalPages <= 6 ) {
			// Show all pages if 6 or fewer
			for ( let i = 1; i <= totalPages; i++ ) {
				pages.push(
					<button
						key={ i }
						className={ `page-number ${
							currentPage === i ? 'current' : ''
						}` }
						onClick={ ( e ) => {
							e.preventDefault();
							onPageChange( i );
						} }
						aria-current={ currentPage === i ? 'page' : undefined }
						aria-label={
							currentPage === i
								? __( 'Current page', 'fau-elemental' )
								: __( 'Go to page', 'fau-elemental' ) + ' ' + i
						}
					>
						{ i }
					</button>
				);
			}
		} else {
			// Sliding window pagination logic
			let pagesToShow = [];

			if ( currentPage <= 2 ) {
				// Pages 1-2: Show 1,2,3 ... 8,9,10 (first 3, last 3)
				pagesToShow = [
					1,
					2,
					3,
					'...',
					totalPages - 2,
					totalPages - 1,
					totalPages,
				];
			} else if ( currentPage === 3 ) {
				// Page 3: Show ..., 2,3,4, ..., 8,9,10
				pagesToShow = [
					'...',
					2,
					3,
					4,
					'...',
					totalPages - 2,
					totalPages - 1,
					totalPages,
				];
			} else if ( currentPage >= totalPages - 2 ) {
				// Last 3 pages: Show 1,2,3, ..., 7,8,9 (first 3, last 3)
				pagesToShow = [
					1,
					2,
					3,
					'...',
					totalPages - 2,
					totalPages - 1,
					totalPages,
				];
			} else {
				// Middle pages: Pure sliding window
				// Show 1, ..., current-1, current, current+1, ..., last
				pagesToShow = [
					1,
					'...',
					currentPage - 1,
					currentPage,
					currentPage + 1,
					'...',
					totalPages,
				];
			}

			// Generate the page elements
			pagesToShow.forEach( ( page, index ) => {
				if ( page === '...' ) {
					pages.push(
						<span
							key={ `ellipsis-${ index }` }
							className="page-ellipsis"
							aria-hidden="true"
						>
							…
						</span>
					);
				} else {
					pages.push(
						<button
							key={ page }
							className={ `page-number ${
								currentPage === page ? 'current' : ''
							}` }
							onClick={ ( e ) => {
								e.preventDefault();
								onPageChange( page );
							} }
							aria-current={
								currentPage === page ? 'page' : undefined
							}
							aria-label={
								currentPage === page
									? __( 'Current page', 'fau-elemental' )
									: __( 'Go to page', 'fau-elemental' ) +
									  ' ' +
									  page
							}
						>
							{ page }
						</button>
					);
				}
			} );
		}

		return pages;
	};

	return (
		<div className="wp-block-fau-elemental-fau-pagination pagination basic">
			<div className="pagination-controls">
				{ /* Previous button */ }
				<button
					className={ `page-nav prev ${
						currentPage === 1 ? 'disabled' : ''
					}` }
					onClick={ ( e ) => {
						e.preventDefault();
						if ( currentPage > 1 ) {
							onPageChange( currentPage - 1 );
						}
					} }
					disabled={ currentPage === 1 }
					aria-label={ __( 'Previous page', 'fau-elemental' ) }
				>
					<span aria-hidden="true">‹</span>
				</button>

				{ /* Page numbers */ }
				<div className="page-numbers">{ generatePageNumbers() }</div>

				{ /* Next button */ }
				<button
					className={ `page-nav next ${
						currentPage === totalPages ? 'disabled' : ''
					}` }
					onClick={ ( e ) => {
						e.preventDefault();
						if ( currentPage < totalPages ) {
							onPageChange( currentPage + 1 );
						}
					} }
					disabled={ currentPage === totalPages }
					aria-label={ __( 'Next page', 'fau-elemental' ) }
				>
					<span aria-hidden="true">›</span>
				</button>
			</div>
		</div>
	);
};
