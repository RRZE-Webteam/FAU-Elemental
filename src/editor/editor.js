import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { useEffect } from '@wordpress/element';

/**
 * Block Selection Class Manager
 * 
 * This filter manages CSS classes on the body element based on block selection state:
 * - Adds classes for selected core blocks (e.g. 'faue-is-paragraph-block-selected')
 * - Adds classes for block variations based on className attribute
 * - Cleans up classes when selection changes
 * - Enables contextual styling in the editor based on selected block type
 * 
 * The classes follow the pattern:
 * - Core blocks: faue-is-{blocktype}-block-selected
 * - Variations: faue-is-{variation}-selected
 */
addFilter(
	'editor.BlockEdit',
	'fau-elemental/with-block-selected-classes',
	createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const { isSelected, name, attributes } = props;

			useEffect( () => {
				document.body.classList.forEach( ( className ) => {
					if ( className.startsWith( 'faue-is-' ) ) {
						document.body.classList.remove( className );
					}
				} );

				if ( isSelected ) {

					if ( name.startsWith( 'core/' ) ) {
						const blockType = name.replace( 'core/', '' );
						document.body.classList.add( `faue-is-${ blockType }-block-selected` );

						if ( attributes?.className ) {
							const variations = attributes.className.split( ' ' );
							variations.forEach( ( variation ) => {
								if ( variation ) {
									document.body.classList.add( `faue-is-${ variation }-selected` );
								}
							} );
						}
					}
				}
			}, [ isSelected, name, attributes?.className ] );

			return <BlockEdit { ...props } />;
		};
	}, 'withBlockSelectedClasses' )
);
