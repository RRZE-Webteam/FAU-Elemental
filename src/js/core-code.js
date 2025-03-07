const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, ToggleControl } = wp.components;


// Add heading attribute
addFilter(
    'blocks.registerBlockType',
    'core/code-dark-mode',
    (settings, name) => {
        if (name !== 'core/code') {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                darkMode: {
                    type: 'boolean',
                    default: false
                }
            },
            save: (props) => {
                const { attributes } = props;
                const blockProps = wp.blockEditor.useBlockProps.save({
                    'data-dark-mode': attributes.darkMode,
                    className: attributes.darkMode ? 'dark' : ''
                });

                // Get the original saved content
                const originalSaveElement = settings.save(props);

                return wp.element.cloneElement(originalSaveElement, blockProps);
            },
        };
    }
);

// Add inspector controls
const withInspectorControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (name !== 'core/code') {
            return <BlockEdit {...props} />;
        }

        // Add dark class in editor
        const blockProps = wp.blockEditor.useBlockProps({
            className: attributes.darkMode ? 'dark' : ''
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Block Settings">
                        <ToggleControl
                            label="Dark Mode"
                            checked={attributes.darkMode}
                            onChange={(value) => setAttributes({ darkMode: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <BlockEdit {...props} />
                </div>
            </>
        );
    };
}, 'withInspectorControls');

addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-inspector-controls',
    withInspectorControls
);
