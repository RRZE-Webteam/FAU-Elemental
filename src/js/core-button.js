// Core button block customizations
wp.domReady( () => {
	wp.blocks.unregisterBlockStyle( 'core/button', [ 'fill', 'outline' ] );

	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'primary',
		label: 'Primary',
		isDefault: true,
	} );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'secondary',
		label: 'Secondary',
		isDefault: false,
	} );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'tertiary',
		label: 'Tertiary',
		isDefault: false,
	} );
} );
