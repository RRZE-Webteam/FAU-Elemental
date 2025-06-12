import { __ } from '@wordpress/i18n';
import { 
    InspectorControls, 
    useBlockProps 
} from '@wordpress/block-editor';
import {
    PanelBody,
    ToggleControl,
    TextControl,
    TextareaControl,
    SelectControl,
    RangeControl,
    Button,
    __experimentalText as Text
} from '@wordpress/components';
import { useState } from '@wordpress/element';

const Edit = (props) => {
    const { attributes, setAttributes } = props;
    const {
        enableSearch,
        searchPlaceholder,
        enableFilters,
        filterFields,
        showMoreFiltersButton,
        enableViewSwitcher,
        availableViews,
        defaultView,
        enableSorting,
        sortOptions,
        defaultSort,
        showResultsCount,
        resultsPerPage,
        gridWidth
    } = attributes;

    const [newFilterName, setNewFilterName] = useState('');
    const [newFilterOptions, setNewFilterOptions] = useState('');

    const blockProps = useBlockProps({
        className: `fau-list-filters grid-width-${gridWidth}`
    });

    const addFilterField = () => {
        if (!newFilterName.trim()) return;
        
        const options = newFilterOptions
            .split('\n')
            .filter(option => option.trim())
            .map(option => {
                const [value, label] = option.split('|');
                return {
                    value: value?.trim() || option.trim(),
                    label: label?.trim() || option.trim()
                };
            });

        const newField = {
            name: newFilterName.trim(),
            options: options
        };

        setAttributes({
            filterFields: [...filterFields, newField]
        });

        setNewFilterName('');
        setNewFilterOptions('');
    };

    const removeFilterField = (index) => {
        const updatedFields = filterFields.filter((_, i) => i !== index);
        setAttributes({ filterFields: updatedFields });
    };

    const addSortOption = () => {
        const newOption = { value: '', label: '' };
        setAttributes({
            sortOptions: [...sortOptions, newOption]
        });
    };

    const updateSortOption = (index, field, value) => {
        const updatedOptions = [...sortOptions];
        updatedOptions[index][field] = value;
        setAttributes({ sortOptions: updatedOptions });
    };

    const removeSortOption = (index) => {
        const updatedOptions = sortOptions.filter((_, i) => i !== index);
        setAttributes({ sortOptions: updatedOptions });
    };

    const renderPreview = () => {
        return (
            <div className="fau-list-filters-preview">
                {enableSearch && (
                    <div className="fau-list-filters__search-section">
                        <div className="search-wrapper">
                            <input 
                                type="search" 
                                className="search-input" 
                                placeholder={searchPlaceholder}
                                disabled
                            />
                        </div>
                    </div>
                )}

                {enableFilters && filterFields.length > 0 && (
                    <div className="fau-list-filters__filter-section">
                        <div className="filter-controls">
                            {filterFields.slice(0, showMoreFiltersButton ? 3 : filterFields.length).map((field, index) => (
                                <div key={index} className="filter-field">
                                    <label className="filter-label">{field.name}</label>
                                    <select className="filter-select" disabled>
                                        <option>{__('All', 'fau-elemental')} {field.name}</option>
                                        {field.options.map((option, optIndex) => (
                                            <option key={optIndex} value={option.value}>
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            ))}
                            {showMoreFiltersButton && filterFields.length > 3 && (
                                <button type="button" className="show-more-filters" disabled>
                                    {__('Show more filters', 'fau-elemental')}
                                </button>
                            )}
                        </div>
                    </div>
                )}

                <div className="fau-list-filters__sort-section">
                    {showResultsCount && (
                        <div className="results-count">
                            <span className="results-text">1 to {resultsPerPage} from 100 records</span>
                        </div>
                    )}
                    
                    <div className="sort-controls">
                        {enableViewSwitcher && availableViews.length > 1 && (
                            <div className="view-switcher">
                                {availableViews.map((view) => (
                                    <button 
                                        key={view}
                                        type="button" 
                                        className={`view-button ${view === defaultView ? 'active' : ''}`}
                                        disabled
                                    >
                                        <span className={`view-icon view-icon-${view}`}></span>
                                        <span className="view-label">{view.charAt(0).toUpperCase() + view.slice(1)}</span>
                                    </button>
                                ))}
                            </div>
                        )}
                        
                        {enableSorting && sortOptions.length > 0 && (
                            <div className="sort-dropdown">
                                <label className="sort-label">{__('Sort by:', 'fau-elemental')}</label>
                                <select className="sort-select" disabled>
                                    {sortOptions.map((option, index) => (
                                        <option key={index} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        );
    };

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Search Settings', 'fau-elemental')} initialOpen={true}>
                    <ToggleControl
                        label={__('Enable Search', 'fau-elemental')}
                        checked={enableSearch}
                        onChange={(value) => setAttributes({ enableSearch: value })}
                    />
                    {enableSearch && (
                        <TextControl
                            label={__('Search Placeholder', 'fau-elemental')}
                            value={searchPlaceholder}
                            onChange={(value) => setAttributes({ searchPlaceholder: value })}
                        />
                    )}
                </PanelBody>

                <PanelBody title={__('Filter Settings', 'fau-elemental')} initialOpen={false}>
                    <ToggleControl
                        label={__('Enable Filters', 'fau-elemental')}
                        checked={enableFilters}
                        onChange={(value) => setAttributes({ enableFilters: value })}
                    />
                    
                    {enableFilters && (
                        <>
                            <ToggleControl
                                label={__('Show More Filters Button', 'fau-elemental')}
                                checked={showMoreFiltersButton}
                                onChange={(value) => setAttributes({ showMoreFiltersButton: value })}
                                help={__('Hide filters after the first 3 and show a "Show more" button', 'fau-elemental')}
                            />

                            <div style={{ marginTop: '20px' }}>
                                <Text style={{ fontWeight: 'bold' }}>
                                    {__('Add New Filter Field', 'fau-elemental')}
                                </Text>
                                <TextControl
                                    label={__('Filter Name', 'fau-elemental')}
                                    value={newFilterName}
                                    onChange={setNewFilterName}
                                />
                                <TextareaControl
                                    label={__('Filter Options', 'fau-elemental')}
                                    value={newFilterOptions}
                                    onChange={setNewFilterOptions}
                                    help={__('Enter one option per line. Use format: value|label (e.g., "news|News Articles")', 'fau-elemental')}
                                    rows={4}
                                />
                                <Button 
                                    isPrimary 
                                    onClick={addFilterField}
                                    disabled={!newFilterName.trim()}
                                >
                                    {__('Add Filter Field', 'fau-elemental')}
                                </Button>
                            </div>

                            {filterFields.length > 0 && (
                                <div style={{ marginTop: '20px' }}>
                                    <Text style={{ fontWeight: 'bold' }}>
                                        {__('Current Filter Fields', 'fau-elemental')}
                                    </Text>
                                    {filterFields.map((field, index) => (
                                        <div key={index} style={{ 
                                            border: '1px solid #ddd', 
                                            padding: '10px', 
                                            marginBottom: '10px',
                                            borderRadius: '4px'
                                        }}>
                                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                                <strong>{field.name}</strong>
                                                <Button 
                                                    isDestructive
                                                    isSmall
                                                    onClick={() => removeFilterField(index)}
                                                >
                                                    {__('Remove', 'fau-elemental')}
                                                </Button>
                                            </div>
                                            <div style={{ fontSize: '12px', marginTop: '5px' }}>
                                                {field.options.length} {__('options', 'fau-elemental')}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </>
                    )}
                </PanelBody>

                <PanelBody title={__('View & Sort Settings', 'fau-elemental')} initialOpen={false}>
                    <ToggleControl
                        label={__('Enable View Switcher', 'fau-elemental')}
                        checked={enableViewSwitcher}
                        onChange={(value) => setAttributes({ enableViewSwitcher: value })}
                    />
                    
                    {enableViewSwitcher && (
                        <>
                            <SelectControl
                                label={__('Available Views', 'fau-elemental')}
                                multiple
                                value={availableViews}
                                options={[
                                    { label: __('Cards', 'fau-elemental'), value: 'cards' },
                                    { label: __('Table', 'fau-elemental'), value: 'table' },
                                    { label: __('List', 'fau-elemental'), value: 'list' }
                                ]}
                                onChange={(value) => setAttributes({ availableViews: value })}
                            />
                            
                            <SelectControl
                                label={__('Default View', 'fau-elemental')}
                                value={defaultView}
                                options={availableViews.map(view => ({
                                    label: view.charAt(0).toUpperCase() + view.slice(1),
                                    value: view
                                }))}
                                onChange={(value) => setAttributes({ defaultView: value })}
                            />
                        </>
                    )}

                    <ToggleControl
                        label={__('Enable Sorting', 'fau-elemental')}
                        checked={enableSorting}
                        onChange={(value) => setAttributes({ enableSorting: value })}
                    />

                    {enableSorting && (
                        <div style={{ marginTop: '20px' }}>
                            <Text style={{ fontWeight: 'bold' }}>
                                {__('Sort Options', 'fau-elemental')}
                            </Text>
                            {sortOptions.map((option, index) => (
                                <div key={index} style={{ 
                                    border: '1px solid #ddd', 
                                    padding: '10px', 
                                    marginBottom: '10px',
                                    borderRadius: '4px'
                                }}>
                                    <TextControl
                                        label={__('Value', 'fau-elemental')}
                                        value={option.value}
                                        onChange={(value) => updateSortOption(index, 'value', value)}
                                    />
                                    <TextControl
                                        label={__('Label', 'fau-elemental')}
                                        value={option.label}
                                        onChange={(value) => updateSortOption(index, 'label', value)}
                                    />
                                    <Button 
                                        isDestructive
                                        isSmall
                                        onClick={() => removeSortOption(index)}
                                    >
                                        {__('Remove', 'fau-elemental')}
                                    </Button>
                                </div>
                            ))}
                            <Button 
                                isSecondary 
                                onClick={addSortOption}
                            >
                                {__('Add Sort Option', 'fau-elemental')}
                            </Button>
                        </div>
                    )}

                    <SelectControl
                        label={__('Default Sort', 'fau-elemental')}
                        value={defaultSort}
                        options={sortOptions.map(option => ({
                            label: option.label,
                            value: option.value
                        }))}
                        onChange={(value) => setAttributes({ defaultSort: value })}
                    />
                </PanelBody>

                <PanelBody title={__('Display Settings', 'fau-elemental')} initialOpen={false}>
                    <ToggleControl
                        label={__('Show Results Count', 'fau-elemental')}
                        checked={showResultsCount}
                        onChange={(value) => setAttributes({ showResultsCount: value })}
                    />
                    
                    <RangeControl
                        label={__('Results Per Page', 'fau-elemental')}
                        value={resultsPerPage}
                        onChange={(value) => setAttributes({ resultsPerPage: value })}
                        min={5}
                        max={50}
                        step={5}
                    />
                    
                    <SelectControl
                        label={__('Grid Width', 'fau-elemental')}
                        value={gridWidth}
                        options={[
                            { label: __('8 Columns', 'fau-elemental'), value: '8' },
                            { label: __('10 Columns', 'fau-elemental'), value: '10' },
                            { label: __('12 Columns', 'fau-elemental'), value: '12' }
                        ]}
                        onChange={(value) => setAttributes({ gridWidth: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <div className="fau-list-filters-editor">
                    <h3>{__('FAU List Filters', 'fau-elemental')}</h3>
                    <p>{__('This block will render interactive filters for lists on the frontend.', 'fau-elemental')}</p>
                    
                    {renderPreview()}
                </div>
            </div>
        </>
    );
};

export default Edit; 