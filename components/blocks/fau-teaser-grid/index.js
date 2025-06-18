import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';

// Add theme URL to window object
window.fauElemental = {
	...window.fauElemental,
	themeUrl: window.fauElemental?.themeUrl || '',
};

registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: () => null,
} );
