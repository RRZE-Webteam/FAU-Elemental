import { addFilter } from '@wordpress/hooks';

wp.domReady( () => {
	wp.blocks.unregisterBlockStyle( 'core/separator', [
		'default',
		'wide',
		'dots',
	] );
} );

/**
 * Modifies block supports for the Separator block.
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
function editSeparatorBlockSupports( settings, name ) {
	// Only modify Separator blocks
	if ( name !== 'core/separator' ) {
		return settings;
	}

	// Modify block supports
	settings.supports = {
		...settings.supports,
		// Disable specific features
		align: [],
	};

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'fau-elemental/add-copyright-info-attribute',
	editSeparatorBlockSupports
);
