import {
	registerBlockStyle,
	unregisterBlockVariation,
} from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';

wp.domReady( () => {
	registerBlockStyle( 'core/group', {
		name: 'dark',
		label: __( 'Dark', 'fau-elemental' ),
		isDefault: false,
	} );

	unregisterBlockVariation( 'core/group', 'group-row' );
	unregisterBlockVariation( 'core/group', 'group-stack' );
	unregisterBlockVariation( 'core/group', 'group-grid' );
} );

addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-group-block-settings',
	function ( settings, name ) {
		if ( name !== 'core/group' ) {
			return settings;
		}

		settings.supports = {
			...settings.supports,
			layout: false,
		};

		return settings;
	}
);
