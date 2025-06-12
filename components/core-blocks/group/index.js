import { registerBlockStyle } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// Register dark style for core/group block
wp.domReady( () => {
	registerBlockStyle( 'core/group', {
		name: 'dark',
		label: __( 'Dark', 'fau-elemental' ),
		isDefault: false,
	} );
} );
