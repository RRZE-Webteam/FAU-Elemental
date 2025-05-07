import { addFilter } from '@wordpress/hooks';
import { unregisterBlockStyle } from '@wordpress/blocks';

wp.domReady( () => {
	unregisterBlockStyle( 'core/separator', [ 'default', 'wide', 'dots' ] );
} );

addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-separator-block-settings',
	( settings, name ) => {
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
);
