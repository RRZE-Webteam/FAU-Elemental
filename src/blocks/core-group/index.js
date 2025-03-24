import { registerBlockStyle } from '@wordpress/blocks';

// Register dark style for core/group block
wp.domReady( () => {
	registerBlockStyle( 'core/group', {
		name: 'dark',
		label: 'Dark',
		isDefault: false,
	} );
} );
