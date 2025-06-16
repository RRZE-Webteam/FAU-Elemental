/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */

import Edit from './edit';

/**
 * Register the block
 */
registerBlockType( 'fau-elemental/portalmenu', {
	edit: Edit,
} );
