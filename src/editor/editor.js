import { select, subscribe, dispatch } from '@wordpress/data';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { unregisterFormatType, registerFormatType } from '@wordpress/rich-text';

/**
 * Block Selection Class Manager
 *
 * Adds class to the body element based on currently selected block
 * Class format: 'faue-is-{blocktype}-block-selected'
 */

// Subscribe to block selection changes
subscribe( () => {
	// Clear all block selection classes first
	document.body.classList.forEach( ( className ) => {
		if (
			className.startsWith( 'faue-is-' ) &&
			className.endsWith( '-block-selected' )
		) {
			document.body.classList.remove( className );
		}
	} );

	// Get the currently selected block (first in selection array)
	const selectedBlockId =
		select( 'core/block-editor' ).getSelectedBlockClientId();

	// Add class for the currently selected block
	if ( selectedBlockId ) {
		const block = select( 'core/block-editor' ).getBlock( selectedBlockId );
		if ( block && block.name.startsWith( 'core/' ) ) {
			const blockType = block.name.replace( 'core/', '' );
			document.body.classList.add(
				`faue-is-${ blockType }-block-selected`
			);
		}
	}
} );

// Remove the text-color format type
unregisterFormatType( 'core/text-color' );

/**
 * Filter Rich Text Format Types
 *
 * This filter allows disabling specific format types (like bold, italic)
 * for specific blocks
 */
// addFilter(
// 	'editor.BlockEdit',
// 	'fau-elemental/filter-format-types',
// 	createHigherOrderComponent((BlockEdit) => {
// 		return (props) => {
// 			const { name, isSelected } = props;

// 			// When the block is selected, filter available format types
// 			if (isSelected) {
// 				// Check for specific block types to customize
// 				if (name === 'core/paragraph') {
// 					// Example: Disable bold formatting for paragraphs
// 					const formatTypes = select('core/rich-text').getFormatTypes();
// 					const disabledFormats = ['core/bold']; // Add format names to disable

// 					formatTypes.forEach(format => {
// 						if (disabledFormats.includes(format.name)) {
// 							// Unregister or modify the format type
// 							dispatch('core/rich-text').removeFormatTypes(format.name);
// 						}
// 					});
// 				}
// 			}

// 			return <BlockEdit {...props} />;
// 		};
// 	}, 'withFilteredFormatTypes')
// );
