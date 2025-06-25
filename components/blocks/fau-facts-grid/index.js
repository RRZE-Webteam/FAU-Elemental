import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import Save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: Save,
	deprecated: [
		{
			// Previous version that used dynamic rendering
			attributes: metadata.attributes,
			save: () => null, // Was a dynamic block
			migrate( attributes ) {
				// No migration needed, just use the same attributes
				return attributes;
			},
			// eslint-disable-next-line no-unused-vars
			isEligible( attributes, innerBlocks ) {
				// This deprecated version applies to blocks that have no saved content
				// but have attributes (i.e., were previously dynamic blocks)
				return true;
			},
		},
	],
} );
