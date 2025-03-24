import { registerBlockVariation } from '@wordpress/blocks';

// Register "Intro Text" variation for core/paragraph with an icon
registerBlockVariation( 'core/paragraph', {
	name: 'text',
	title: 'Text',
	description: 'A paragraph.',
	attributes: {
		className: 'text',
	},
	icon: 'editor-paragraph',
	isDefault: true,
	scope: [ 'block', 'inserter', 'transform' ],
} );

// Register "Intro Text" variation for core/paragraph with an icon
registerBlockVariation( 'core/paragraph', {
	name: 'intro-text',
	title: 'Intro Text',
	description: 'A paragraph styled as an introduction.',
	attributes: {
		className: 'intro-text',
	},
	icon: 'editor-paragraph',
	isDefault: false,
	scope: [ 'block', 'inserter', 'transform' ],
} );

// Register "Small Text" variation for core/paragraph with an icon
registerBlockVariation( 'core/paragraph', {
	name: 'small-text',
	title: 'Small Text',
	description: 'A smaller paragraph for fine print or secondary content.',
	attributes: {
		className: 'small-text',
	},
	icon: 'editor-paragraph',
	isDefault: false,
	scope: [ 'block', 'inserter', 'transform' ],
} );
