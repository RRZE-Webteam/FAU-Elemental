import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import metadata from './block.json';

/**
 * Register the FAU Global Search block
 */
registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
} );
