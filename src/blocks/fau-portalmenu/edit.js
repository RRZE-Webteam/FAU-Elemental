/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { 
    InspectorControls, 
    useBlockProps 
} from '@wordpress/block-editor';
import {
    Panel,
    PanelBody,
    SelectControl,
    ToggleControl,
    RadioControl,
    Placeholder
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.scss';
import EditorPreview from './EditorPreview';

/**
 * Edit component for the FAU Portal Menu block
 */
export default function Edit({ attributes, setAttributes, clientId }) {
    const blockProps = useBlockProps({
        className: 'wp-block-fau-elemental-portalmenu'
    });

    // Get available menus
    const menus = useSelect((select) => {
        const menuItems = select('core').getMenus();
        return menuItems ? menuItems.map((menu) => ({
            label: menu.name,
            value: menu.id.toString()
        })) : [];
    }, []);

    // Add an empty option
    const menuOptions = [
        { label: __('Select a menu', 'fau-elemental'), value: '' },
        ...menus
    ];

    // Add type options
    const typeOptions = [
        { label: __('Type 1 (2:1 Ratio)', 'fau-elemental'), value: '1' },
        { label: __('Type 2 (3:2 Ratio)', 'fau-elemental'), value: '2' },
        { label: __('Type 3 (3:4 Ratio)', 'fau-elemental'), value: '3' }
    ];

    // Get the currently selected menu name for display
    const selectedMenu = menus.find(menu => menu.value === attributes.menuId);
    const selectedMenuName = selectedMenu ? selectedMenu.label : attributes.menuId;

    // Effect to store menu name when menuId changes
    useEffect(() => {
        if (attributes.menuId && selectedMenu) {
            setAttributes({ menuName: selectedMenu.label });
        }
    }, [attributes.menuId, selectedMenu]);

    // Inspector controls for the settings sidebar
    const inspectorControls = (
        <InspectorControls>
            <Panel>
                <PanelBody title={__('Menu Settings', 'fau-elemental')} initialOpen={true}>
                    <SelectControl
                        label={__('Menu', 'fau-elemental')}
                        value={attributes.menuId}
                        options={menuOptions}
                        onChange={(menuId) => setAttributes({ menuId })}
                    />

                    <RadioControl
                        label={__('Display Type', 'fau-elemental')}
                        selected={attributes.type?.toString() || '1'}
                        options={typeOptions}
                        onChange={(type) => setAttributes({ type: parseInt(type) })}
                    />

                    <ToggleControl
                        label={__('Is Mega Navigation', 'fau-elemental')}
                        checked={attributes.isMegaNav}
                        onChange={(value) => setAttributes({ isMegaNav: value })}
                    />

                    <ToggleControl
                        label={__('Show Submenus', 'fau-elemental')}
                        checked={attributes.showSubs}
                        onChange={(value) => setAttributes({ showSubs: value })}
                    />

                    <ToggleControl
                        label={__('List View', 'fau-elemental')}
                        checked={!!attributes.listView}
                        onChange={(listView) => setAttributes({ listView })}
                    />

                    <ToggleControl
                        label={__('Hide Thumbnails', 'fau-elemental')}
                        checked={!!attributes.noThumbs}
                        onChange={(noThumbs) => setAttributes({ noThumbs })}
                    />

                    <ToggleControl
                        label={__('No Fallback Images', 'fau-elemental')}
                        checked={!!attributes.noFallback}
                        onChange={(noFallback) => setAttributes({ noFallback })}
                    />

                    <ToggleControl
                        label={__('Hover Zoom Effect', 'fau-elemental')}
                        checked={!!attributes.hoverZoom}
                        onChange={(hoverZoom) => setAttributes({ hoverZoom })}
                    />

                    <ToggleControl
                        label={__('Hover Blur Effect', 'fau-elemental')}
                        checked={!!attributes.hoverBlur}
                        onChange={(hoverBlur) => setAttributes({ hoverBlur })}
                    />
                </PanelBody>
            </Panel>
        </InspectorControls>
    );

    // Return the block content
    return (
        <>
            {inspectorControls}
            
            <div {...blockProps}>
                {attributes.menuId ? (
                    <EditorPreview 
                        attributes={attributes} 
                        selectedMenuName={selectedMenuName} 
                    />
                ) : (
                    <Placeholder
                        label={__('FAU Portal Menu', 'fau-elemental')}
                        instructions={__('Please select a menu in the block settings panel.', 'fau-elemental')}
                    />
                )}
            </div>
        </>
    );
} 