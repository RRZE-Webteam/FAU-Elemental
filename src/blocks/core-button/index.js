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
