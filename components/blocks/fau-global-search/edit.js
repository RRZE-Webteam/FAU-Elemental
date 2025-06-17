import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';

// This is the main Edit component for the block editor interface.
export default function Edit({ attributes, setAttributes, clientId }) { // Added clientId for unique keys if needed
    const blockProps = useBlockProps({
        className: 'wp-block-fau-elemental-fau-global-search' 
    });
    const { title, searchScope } = attributes;

    const onTitleChange = (newTitle) => {
        setAttributes({ title: newTitle });
    };
    
    const stringToArray = (str) => {
        if (typeof str !== 'string') return [];
        return str.split(',').map(item => item.trim()).filter(item => item);
    };
    const arrayToString = (arr) => {
        if (!Array.isArray(arr)) return '';
        return arr.join(', ');
    };

    // Unique ID for editor elements like radio group name, using clientId from props
    const editorInstanceId = `edit-scope-${clientId}`;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Search Settings', 'fau-elemental')}>
                    <TextControl
                        label={__('Block Title', 'fau-elemental')}
                        value={title}
                        onChange={onTitleChange}
                        help={__('The title displayed above the search form.', 'fau-elemental')}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <RichText
                    tagName="h2"
                    className="wp-block-fau-elemental-fau-global-search__title"
                    value={title}
                    onChange={onTitleChange}
                    placeholder={__('Enter search title...', 'fau-elemental')}
                    allowedFormats={[]}
                />
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