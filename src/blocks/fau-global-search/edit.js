import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, TextareaControl } from '@wordpress/components';

// This is the main Edit component for the block editor interface.
export default function Edit({ attributes, setAttributes, clientId }) { // Added clientId for unique keys if needed
    const blockProps = useBlockProps({
        className: 'wp-block-fau-elemental-fau-global-search' 
    });
    const { title, searchScope, defaultSuggestions, faqSuggestions } = attributes;

    const onTitleChange = (newTitle) => {
        setAttributes({ title: newTitle });
    };

    const onScopeChange = (scope) => {
        setAttributes({ searchScope: scope ? 'fau-wide' : 'current' });
    };
    
    const stringToArray = (str) => {
        if (typeof str !== 'string') return [];
        return str.split(',').map(item => item.trim()).filter(item => item);
    };
    const arrayToString = (arr) => {
        if (!Array.isArray(arr)) return '';
        return arr.join(', ');
    };

    const currentDefaultSuggestions = defaultSuggestions || [];
    const currentFaqSuggestions = faqSuggestions || [];

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
                    <ToggleControl
                        label={__('Default to FAU-wide Search', 'fau-elemental')}
                        checked={searchScope === 'fau-wide'}
                        onChange={onScopeChange}
                        help={__('If checked, "FAU-wide" will be the default selected scope.', 'fau-elemental')}
                    />
                </PanelBody>
                <PanelBody title={__('Search Suggestions', 'fau-elemental')} initialOpen={false}>
                    <TextareaControl
                        label={__('Default Suggestions', 'fau-elemental')}
                        value={arrayToString(currentDefaultSuggestions)}
                        onChange={(value) => setAttributes({ defaultSuggestions: stringToArray(value) })}
                        help={__('Comma-separated list of default search suggestions.', 'fau-elemental')}
                        rows={3}
                    />
                    <TextareaControl
                        label={__('FAQ Suggestions (on focus)', 'fau-elemental')}
                        value={arrayToString(currentFaqSuggestions)}
                        onChange={(value) => setAttributes({ faqSuggestions: stringToArray(value) })}
                        help={__('Comma-separated list of suggestions to show when the search input gains focus (replaces default suggestions).', 'fau-elemental')}
                        rows={3}
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
                <div className="wp-block-fau-elemental-fau-global-search__suggestions-area search-suggestions-placeholder">
                    <p><em>{__('Default/FAQ suggestions will appear here on the front-end.', 'fau-elemental')}</em></p>
                    {currentDefaultSuggestions.length > 0 && (
                        <div>
                            <strong>{__('Default Suggestions Preview:', 'fau-elemental')}</strong>
                            <ul>{currentDefaultSuggestions.map((s, i) => <li key={`def-${i}`}>{s}</li>)}</ul>
                        </div>
                    )}
                     {currentFaqSuggestions.length > 0 && (
                        <div>
                            <strong>{__('FAQ Suggestions Preview (on focus):', 'fau-elemental')}</strong>
                            <ul>{currentFaqSuggestions.map((s, i) => <li key={`faq-${i}`}>{s}</li>)}</ul>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
} 