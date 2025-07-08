import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import metadata from './block.json';

import './editor.scss';
import './style.scss';

/**
 * Register the FAU Global Search block
 */
registerBlockType( metadata.name, {
	...metadata,
	icon: 'search',
	edit: Edit,
} );
