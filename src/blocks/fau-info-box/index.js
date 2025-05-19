// Make sure view.js is included in the build
import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import './editor.scss';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';

// Add theme URL to window object
window.fauElemental = {
	...window.fauElemental,
	themeUrl: window.fauElemental?.themeUrl || '',
};

registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: Save,
} );
