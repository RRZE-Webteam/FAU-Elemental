import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

/**
 * Register the block
 */
registerBlockType( 'fau-elemental/portalmenu', {
	edit: Edit,
} );
