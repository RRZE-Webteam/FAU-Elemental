import {
	registerBlockStyle,
	unregisterBlockVariation,
} from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// Register block styles for core/paragraph
wp.domReady( () => {
	// Unregister previously registered block variations
	unregisterBlockVariation( 'core/paragraph', 'intro-text' );
	unregisterBlockVariation( 'core/paragraph', 'small-text' );

	registerBlockStyle( 'core/paragraph', {
		name: 'intro-text',
		label: __( 'Intro Text', 'fau-elemental' ),
		isDefault: false,
	} );

	registerBlockStyle( 'core/paragraph', {
		name: 'small-text',
		label: __( 'Small Text', 'fau-elemental' ),
		isDefault: false,
	} );
} );
