import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Register the block
 */
registerBlockType( 'fau-elemental/portalmenu', {
	edit: Edit,
} );
