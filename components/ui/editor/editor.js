import { select, subscribe, dispatch } from '@wordpress/data';
// import { addFilter } from '@wordpress/hooks';
// import { createHigherOrderComponent } from '@wordpress/compose';
import { unregisterFormatType } from '@wordpress/rich-text';

// Import all core-blocks
import '../../core-blocks/button/index.js';
import '../../core-blocks/details/index.js';
import '../../core-blocks/file/index.js';
import '../../core-blocks/gallery/index.js';
import '../../core-blocks/group/index.js';
import '../../core-blocks/image/index.js';
import '../../core-blocks/list/index.js';
import '../../core-blocks/media-text/index.js';
import '../../core-blocks/paragraph/index.js';
import '../../core-blocks/quote/index.js';
import '../../core-blocks/separator/index.js';
import '../../core-blocks/spacer/index.js';
import '../../core-blocks/table/index.js';
import '../../core-blocks/tag-cloud/index.js';

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
		// Also clear pattern-related classes
		if (
			className.startsWith( 'faue-is-' ) &&
			className.endsWith( '-pattern-selected' )
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
		const parentBlockIds =
			select( 'core/block-editor' ).getBlockParents( selectedBlockId );
		const parentBlockId = parentBlockIds[ parentBlockIds.length - 1 ]; // Get the immediate parent
		const parentBlock = parentBlockId
			? select( 'core/block-editor' ).getBlock( parentBlockId )
			: null;

		if ( block && block.name.startsWith( 'core/' ) ) {
			const blockType = block.name.replace( 'core/', '' );
			document.body.classList.add(
				`faue-is-${ blockType }-block-selected`
			);
		}

		if ( parentBlock && parentBlock.name.startsWith( 'core/' ) ) {
			const blockType = parentBlock.name.replace( 'core/', '' );
			document.body.classList.add(
				`faue-is-${ blockType }-parent-block-selected`
			);
		}

		// Check if the selected block is inside a pattern
		const patternClass = detectPatternClass( selectedBlockId );
		if ( patternClass ) {
			document.body.classList.add(
				`faue-is-${ patternClass }-pattern-selected`
			);
		}
	}
} );

/**
 * Detect if a block is inside a pattern and return the pattern class name
 * @param {string} blockId - The client ID of the block to check
 * @return {string|null} - The pattern class name or null if not in a pattern
 */
function detectPatternClass( blockId ) {
	const block = select( 'core/block-editor' ).getBlock( blockId );
	if ( ! block ) {
		return null;
	}

	// Check if this block itself is a pattern root
	const patternClass = getPatternClassFromBlock( block );
	if ( patternClass ) {
		return patternClass;
	}

	// Check parent blocks for pattern classes
	const parentBlockIds =
		select( 'core/block-editor' ).getBlockParents( blockId );

	for ( let i = parentBlockIds.length - 1; i >= 0; i-- ) {
		const parentBlock = select( 'core/block-editor' ).getBlock(
			parentBlockIds[ i ]
		);
		if ( parentBlock ) {
			const parentPatternClass = getPatternClassFromBlock( parentBlock );
			if ( parentPatternClass ) {
				return parentPatternClass;
			}
		}
	}

	return null;
}

/**
 * Extract pattern class name from a block's className attribute
 * @param {Object} block - The block object
 * @return {string|null} - The pattern class name or null if not a pattern
 */
function getPatternClassFromBlock( block ) {
	if ( ! block.attributes?.className ) {
		return null;
	}

	const className = block.attributes.className;

	// Check for hero pattern classes
	const heroPatterns = [
		'hero-fau',
		'hero-portal',
		'hero-faculty',
		'hero-chair',
		'hero-cooperation',
		'hero-other',
	];

	const foundPattern = heroPatterns.find( ( pattern ) =>
		className.includes( pattern )
	);
	return foundPattern || null;
}

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

const { createNotice } = dispatch( 'core/notices' );

let isProcessing = false;

// Function to remove the most recently added FAU Hero pattern
function removeLastFAUHeroPattern() {
	const blocks = select( 'core/block-editor' ).getBlocks();

	// Find all top-level blocks that contain the FAU Hero pattern
	const heroBlocks = [];

	blocks.forEach( ( block, index ) => {
		if ( containsFAUHeroPattern( block ) ) {
			heroBlocks.push( {
				clientId: block.clientId,
				index,
				block,
			} );
		}
	} );

	// If we have more than one hero block, remove the last one
	if ( heroBlocks.length > 1 ) {
		const lastHeroBlock = heroBlocks[ heroBlocks.length - 1 ];
		dispatch( 'core/block-editor' ).removeBlock( lastHeroBlock.clientId );
	}
}

// Helper function to check if a block (or its children) contains the FAU Hero pattern
function containsFAUHeroPattern( block ) {
	// Check if this is the main hero group block
	if (
		block.name === 'core/group' &&
		block.attributes?.className?.includes( 'hero-fau' )
	) {
		return true;
	}

	// Check if any inner blocks contain the hero pattern
	if ( block.innerBlocks && block.innerBlocks.length > 0 ) {
		return block.innerBlocks.some( ( innerBlock ) =>
			containsFAUHeroPatternRecursive( innerBlock )
		);
	}

	return false;
}

// Recursive helper to check inner blocks
function containsFAUHeroPatternRecursive( block ) {
	// Check for hero-specific classes
	if (
		block.attributes?.className?.includes( 'hero-front-page-title' ) ||
		block.attributes?.className?.includes( 'hero-content' ) ||
		block.attributes?.className?.includes( 'hero-fau' )
	) {
		return true;
	}

	// Check inner blocks recursively
	if ( block.innerBlocks && block.innerBlocks.length > 0 ) {
		return block.innerBlocks.some( ( innerBlock ) =>
			containsFAUHeroPatternRecursive( innerBlock )
		);
	}

	return false;
}

// Subscribe to block editor changes
let previousBlockCount = 0;

subscribe( () => {
	if ( isProcessing ) {
		return;
	}

	const blocks = select( 'core/block-editor' ).getBlocks();
	const currentBlockCount = select( 'core/block-editor' ).getBlockCount();

	// Only check when blocks are added (not removed or modified)
	if ( currentBlockCount > previousBlockCount ) {
		const patternCount = countFAUHeroOccurrences( blocks );

		if ( patternCount > 1 ) {
			isProcessing = true;

			// Remove the duplicate
			removeLastFAUHeroPattern();

			// Show notice to user
			createNotice(
				'warning',
				'Only one FAU Hero pattern is allowed per page. The duplicate has been removed.',
				{
					isDismissible: true,
					type: 'snackbar',
				}
			);

			setTimeout( () => {
				isProcessing = false;
			}, 100 );
		}
	}

	previousBlockCount = currentBlockCount;
} );

// Count how many times the FAU Hero pattern appears (count top-level occurrences only)
function countFAUHeroOccurrences( blocks ) {
	let count = 0;

	// Only count top-level blocks that contain the hero pattern
	blocks.forEach( ( block ) => {
		if ( containsFAUHeroPattern( block ) ) {
			count++;
		}
	} );

	return count;
}
