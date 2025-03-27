import { addFilter } from '@wordpress/hooks';

/**
 * Modifies block supports for the Heading block.
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
function editHeadingBlockSupports( settings, name ) {
	// Only modify Heading blocks
	if ( name !== 'core/heading' ) {
		return settings;
	}

	console.log( settings );

	// Modify block supports
	settings.supports = {
		...settings.supports,
		// Disable specific features
		align: false,
	};

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-heading-block-supports',
	editHeadingBlockSupports
);