/**
 * Portal Menu Block
 * 
 * Provides a block for inserting portal menus in the block editor.
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { 
    InspectorControls,
    useBlockProps
} = wp.blockEditor;
const { 
    PanelBody, 
    SelectControl,
    ToggleControl,
    Placeholder,
    Icon
} = wp.components;
const { Fragment } = wp.element;

/**
 * Register Portal Menu Block
 */
registerBlockType('fau/portal-menu', {
    title: __('FAU Portal Menu', 'fau-elemental'),
    description: __('Display a portal menu with optional thumbnails and layout options.', 'fau-elemental'),
    category: 'fau-blocks',
    icon: 'grid-view',
    supports: {
        html: false,
        multiple: true,
        reusable: true,
    },
    attributes: {
        menu: {
            type: 'string',
            default: '',
        },
        type: {
            type: 'string',
            default: 'grid',
        },
        ratio: {
            type: 'string',
            default: '33-33-33',
        },
        columns: {
            type: 'number',
            default: 3,
        },
        thumbnails: {
            type: 'boolean',
            default: true,
        },
        displaySubmenus: {
            type: 'boolean',
            default: true,
        },
        listview: {
            type: 'boolean',
            default: false,
        },
        hovertitle: {
            type: 'boolean',
            default: false,
        },
        megamenu: {
            type: 'boolean',
            default: false,
        }
    },

    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { 
            menu, 
            type, 
            ratio, 
            columns,
            thumbnails, 
            displaySubmenus, 
            listview, 
            hovertitle, 
            megamenu 
        } = attributes;
        
        const blockProps = useBlockProps({
            className: 'wp-block-fau-portal-menu',
        });

        // Prepare menu options from the localized variable
        const menuOptions = window.fauPortalMenuOptions ? 
            window.fauPortalMenuOptions.menus : 
            [{ value: '', label: __('No menus found', 'fau-elemental') }];

        // Prepare layout type options
        const typeOptions = [
            { value: 'grid', label: __('Grid', 'fau-elemental') },
            { value: 'list', label: __('List', 'fau-elemental') },
        ];

        // Prepare ratio options
        const ratioOptions = [
            { value: '33-33-33', label: __('Three columns (33-33-33)', 'fau-elemental') },
            { value: '50-50', label: __('Two columns (50-50)', 'fau-elemental') },
            { value: '25-25-25-25', label: __('Four columns (25-25-25-25)', 'fau-elemental') },
            { value: '25-50-25', label: __('Three columns (25-50-25)', 'fau-elemental') },
            { value: '60-40', label: __('Two columns (60-40)', 'fau-elemental') },
            { value: '40-60', label: __('Two columns (40-60)', 'fau-elemental') },
        ];
        
        // Prepare column options
        const columnOptions = [
            { value: 1, label: __('1 Column', 'fau-elemental') },
            { value: 2, label: __('2 Columns', 'fau-elemental') },
            { value: 3, label: __('3 Columns', 'fau-elemental') },
            { value: 4, label: __('4 Columns', 'fau-elemental') },
        ];

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title={__('Portal Menu Settings', 'fau-elemental')} initialOpen={true}>
                        <SelectControl
                            label={__('Select Menu', 'fau-elemental')}
                            value={menu}
                            options={menuOptions}
                            onChange={(value) => setAttributes({ menu: value })}
                        />
                        
                        <SelectControl
                            label={__('Layout Type', 'fau-elemental')}
                            value={type}
                            options={typeOptions}
                            onChange={(value) => setAttributes({ type: value })}
                        />
                        
                        {type === 'grid' && (
                            <>
                                <SelectControl
                                    label={__('Grid Ratio', 'fau-elemental')}
                                    value={ratio}
                                    options={ratioOptions}
                                    onChange={(value) => setAttributes({ ratio: value })}
                                />
                                
                                <SelectControl
                                    label={__('Number of Columns', 'fau-elemental')}
                                    value={columns}
                                    options={columnOptions}
                                    onChange={(value) => setAttributes({ columns: parseInt(value) })}
                                />
                            </>
                        )}
                        
                        <ToggleControl
                            label={__('Show Thumbnails', 'fau-elemental')}
                            checked={thumbnails}
                            onChange={(value) => setAttributes({ thumbnails: value })}
                        />
                        
                        <ToggleControl
                            label={__('Display Submenus', 'fau-elemental')}
                            checked={displaySubmenus}
                            onChange={(value) => setAttributes({ displaySubmenus: value })}
                        />
                        
                        <ToggleControl
                            label={__('List View', 'fau-elemental')}
                            checked={listview}
                            onChange={(value) => setAttributes({ listview: value })}
                        />
                        
                        <ToggleControl
                            label={__('Show Hover Title', 'fau-elemental')}
                            checked={hovertitle}
                            onChange={(value) => setAttributes({ hovertitle: value })}
                        />
                        
                        <ToggleControl
                            label={__('MegaNav Menu', 'fau-elemental')}
                            checked={megamenu}
                            onChange={(value) => setAttributes({ megamenu: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                
                <div {...blockProps}>
                    {!menu ? (
                        <Placeholder
                            icon={<Icon icon="grid-view" />}
                            label={__('FAU Portal Menu', 'fau-elemental')}
                            instructions={__('Select a menu to display as a portal menu.', 'fau-elemental')}
                        >
                            <SelectControl
                                value={menu}
                                options={menuOptions}
                                onChange={(value) => setAttributes({ menu: value })}
                            />
                        </Placeholder>
                    ) : (
                        <div className="portal-menu-preview">
                            <div className="portal-menu-preview-header">
                                {__('Portal Menu:', 'fau-elemental')} <strong>{menu}</strong>
                            </div>
                            <div className="portal-menu-preview-settings">
                                <ul>
                                    <li><strong>{__('Type:', 'fau-elemental')}</strong> {type}</li>
                                    {type === 'grid' && (
                                        <>
                                            <li><strong>{__('Ratio:', 'fau-elemental')}</strong> {ratio}</li>
                                            <li><strong>{__('Columns:', 'fau-elemental')}</strong> {columns}</li>
                                        </>
                                    )}
                                    <li><strong>{__('Thumbnails:', 'fau-elemental')}</strong> {thumbnails ? __('Yes', 'fau-elemental') : __('No', 'fau-elemental')}</li>
                                    <li><strong>{__('Submenus:', 'fau-elemental')}</strong> {displaySubmenus ? __('Yes', 'fau-elemental') : __('No', 'fau-elemental')}</li>
                                </ul>
                            </div>
                        </div>
                    )}
                </div>
            </Fragment>
        );
    },

    save: () => {
        // Rendering is done on the server side
        return null;
    },
}); 