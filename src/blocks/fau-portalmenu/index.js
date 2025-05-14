/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { 
    InspectorControls, 
    useBlockProps 
} from '@wordpress/block-editor';
import {
    Panel,
    PanelBody,
    SelectControl,
    ToggleControl,
    RadioControl
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import './editor.scss';

/**
 * Register the block
 */
registerBlockType('fau-elemental/portalmenu', {
    edit: function Edit({ attributes, setAttributes }) {
        const blockProps = useBlockProps();

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

        return (
            <>
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
                                selected={attributes.type.toString()}
                                options={typeOptions}
                                onChange={(type) => setAttributes({ type: parseInt(type) })}
                            />

                            <ToggleControl
                                label={__('Show Submenus', 'fau-elemental')}
                                checked={attributes.showSubs}
                                onChange={(showSubs) => setAttributes({ showSubs })}
                            />

                            <ToggleControl
                                label={__('Mega Navigation', 'fau-elemental')}
                                checked={attributes.isMegaNav}
                                onChange={(isMegaNav) => setAttributes({ isMegaNav })}
                            />

                            <ToggleControl
                                label={__('List View', 'fau-elemental')}
                                checked={attributes.listView}
                                onChange={(listView) => setAttributes({ listView })}
                            />

                            <ToggleControl
                                label={__('Hide Thumbnails', 'fau-elemental')}
                                checked={attributes.noThumbs}
                                onChange={(noThumbs) => setAttributes({ noThumbs })}
                            />

                            <ToggleControl
                                label={__('No Fallback Images', 'fau-elemental')}
                                checked={attributes.noFallback}
                                onChange={(noFallback) => setAttributes({ noFallback })}
                            />

                            <ToggleControl
                                label={__('Hover Zoom Effect', 'fau-elemental')}
                                checked={attributes.hoverZoom}
                                onChange={(hoverZoom) => setAttributes({ hoverZoom })}
                            />

                            <ToggleControl
                                label={__('Hover Blur Effect', 'fau-elemental')}
                                checked={attributes.hoverBlur}
                                onChange={(hoverBlur) => setAttributes({ hoverBlur })}
                            />
                        </PanelBody>
                    </Panel>
                </InspectorControls>

                <div {...blockProps}>
                    {attributes.menuId ? (
                        <ServerSideRender
                            block="fau-elemental/portalmenu"
                            attributes={attributes}
                        />
                    ) : (
                        <p className="wp-block-fau-elemental-portalmenu-placeholder">
                            {__('Please select a menu in the block settings panel.', 'fau-elemental')}
                        </p>
                    )}
                </div>
            </>
        );
    }
}); 