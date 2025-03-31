import {
	registerBlockStyle,
	unregisterBlockVariation,
} from '@wordpress/blocks';

// Register block styles for core/paragraph
wp.domReady( () => {
	// Unregister previously registered block variations
	unregisterBlockVariation( 'core/paragraph', 'intro-text' );
	unregisterBlockVariation( 'core/paragraph', 'small-text' );

	registerBlockStyle( 'core/paragraph', {
		name: 'intro-text',
		label: 'Intro Text',
		isDefault: false,
	} );

	registerBlockStyle( 'core/paragraph', {
		name: 'small-text',
		label: 'Small Text',
		isDefault: false,
	} );
} );
