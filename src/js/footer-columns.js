/**
 * Footer columns layout
 * Handles dynamic column layout for footer menu
 */
document.addEventListener( 'DOMContentLoaded', function () {
	const menu = document.querySelector( '.footer-lists-menu' );
	if ( menu ) {
		const topLevelItems = menu.querySelectorAll(
			'li.menu-item-has-children, li.page_item_has_children'
		);

		// Add appropriate classes based on number of items
		if ( topLevelItems.length > 0 ) {
			// Add class based on number of top-level items (up to 4 columns)
			const columnCount = Math.min( topLevelItems.length, 4 );
			menu.classList.add( `columns-${ columnCount }` );

			// If more than 4 items, add a class to handle wrapping
			if ( topLevelItems.length > 4 ) {
				menu.classList.add( 'multi-row' );
			}

			// Add column class to each top level item
			topLevelItems.forEach( ( item ) => {
				item.classList.add( 'column-item' );
			} );
		}
	}
} );
