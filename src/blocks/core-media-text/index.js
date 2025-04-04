import { addFilter } from '@wordpress/hooks';
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockVariation( 'core/media-text', {
	name: 'media-text-with-heading',
	title: __( 'Media & Text', 'fau-elemental' ),
	description: __(
		'Set media and words side-by-side for a richer layout.',
		'fau-elemental'
	),
	attributes: {
		align: 'full',
	},
	isDefault: true,
	scope: [ 'block', 'inserter' ],
	innerBlocks: [
		[
			'core/heading',
			{ placeholder: __( 'Heading', 'fau-elemental' ), level: 3 },
		],
		[ 'core/paragraph', { placeholder: __( 'Content', 'fau-elemental' ) } ],
	],
} );

// Add custom attribute
addFilter(
	'blocks.registerBlockType',
	'my-plugin/media-text-vertical-attribute',
	function ( settings, name ) {
		if ( name !== 'core/media-text' ) {
			return settings;
		}

		settings.supports = {
			...settings.supports,
			align: false, // Remove alignment support
		};

		settings.allowedBlocks = [
			'core/paragraph',
			'core/heading',
			'core/list',
		];

		return settings;
	}
);
