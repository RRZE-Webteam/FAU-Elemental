import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

// This is the main Edit component for the block editor interface.
export default function Edit({ attributes, setAttributes, clientId }) { // Added clientId for unique keys if needed
    const blockProps = useBlockProps({
        className: 'wp-block-fau-elemental-fau-global-search' 
    });
    const { title, searchScope } = attributes;

    const onTitleChange = (newTitle) => {
        setAttributes({ title: newTitle });
    };

    // Unique ID for editor elements like radio group name, using clientId from props
    const editorInstanceId = `edit-scope-${clientId}`;

    return (
        <>
            <div {...blockProps}>
                <div className="wp-block-fau-elemental-fau-global-search__form search-form-placeholder">
                    <input 
                        type="search" 
                        className="wp-block-fau-elemental-fau-global-search__field" 
                        placeholder={__('Search …', 'fau-elemental')} 
                        disabled 
                    />
                    <input 
                        type="submit" 
                        className="wp-block-fau-elemental-fau-global-search__submit" 
                        value={__('Search', 'fau-elemental')} 
                        disabled 
                    />
                    <div className="wp-block-fau-elemental-fau-global-search__scope-toggle search-scope-toggle-placeholder">
                        <label>
                            <input type="radio" name={editorInstanceId} checked={searchScope === 'current'} disabled onChange={() => {}} /> {__('Only in this website', 'fau-elemental')}
                        </label>
                        <label>
                            <input type="radio" name={editorInstanceId} checked={searchScope === 'fau-wide'} disabled onChange={() => {}} /> {__('FAU-wide', 'fau-elemental')}
                        </label>
                    </div>
                </div>
            </div>
        </>
    );
} 