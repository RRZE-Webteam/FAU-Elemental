import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';

/**
 * Register the FAU List Filters block
 */
registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: () => null, // Server-side rendered block
} );
