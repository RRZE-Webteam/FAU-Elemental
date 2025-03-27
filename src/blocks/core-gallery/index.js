import { addFilter } from '@wordpress/hooks';

/**
 * Modifies block supports for the Gallery block.
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
function editGalleryBlockSupports( settings, name ) {
	// Only modify Gallery blocks
	if ( name !== 'core/gallery' ) {
		return settings;
	}

	// Modify block supports
	settings.supports = {
		...settings.supports,
		// Disable alignment support
		align: false,
	};

	// Set default image size to full and disable crop
	if (settings.attributes) {
		settings.attributes = {
			...settings.attributes,
			sizeSlug: {
				type: 'string',
				default: 'full'
			},
			imageCrop: {
				type: 'boolean',
				default: false
			}
		};
	}

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'core/gallery-remove-align',
	editGalleryBlockSupports
);
