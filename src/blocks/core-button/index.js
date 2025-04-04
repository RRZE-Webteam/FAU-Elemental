import { unregisterBlockStyle, registerBlockStyle } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

wp.domReady( () => {
	unregisterBlockStyle( 'core/button', [ 'fill', 'outline' ] );

	registerBlockStyle( 'core/button', {
		name: 'primary',
		label: __( 'Primary', 'fau-elemental' ),
		isDefault: true,
	} );
	registerBlockStyle( 'core/button', {
		name: 'secondary',
		label: __( 'Secondary', 'fau-elemental' ),
		isDefault: false,
	} );
	registerBlockStyle( 'core/button', {
		name: 'tertiary',
		label: __( 'Tertiary', 'fau-elemental' ),
		isDefault: false,
	} );
} );

/**
 * Modifies the block supports for the Button block.
 * Removes all alignment options from the block.
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-button-block-supports',
	( settings, name ) => {
		if ( name !== 'core/button' ) {
			return settings;
		}

		settings.supports = {
			...settings.supports,
		};

		return settings;
	}
);
