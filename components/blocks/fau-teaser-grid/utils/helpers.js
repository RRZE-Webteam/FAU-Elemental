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
