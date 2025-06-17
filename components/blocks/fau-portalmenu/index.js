/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Panel,
	PanelBody,
	SelectControl,
	ToggleControl,
	RadioControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import './style.scss';
import './editor.scss';
import Edit from './edit';

/**
 * Register the block
 */
registerBlockType( 'fau-elemental/portalmenu', {
	edit: Edit,
} );
