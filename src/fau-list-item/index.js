import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import './editor.scss';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';

registerBlockType(metadata.name, {
    ...metadata,
    edit: Edit,
    save: Save,
    example: {
        attributes: {
            variant: 'post',
            postsPerPage: 3,
            columns: 3,
            showFilters: false,
            currentPage: 1
        }
    }
});
