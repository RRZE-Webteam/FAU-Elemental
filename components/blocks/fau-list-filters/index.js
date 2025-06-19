import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';

/**
 * Register the FAU List Filters block
 */
registerBlockType( 'fau-elemental/fau-list-filters', {
	edit: Edit,
	save: () => null, // Server-side rendered block
} );
