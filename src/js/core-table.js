import { unregisterBlockStyle } from '@wordpress/blocks';
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;

// Unregister default styles
wp.domReady(() => {
    wp.blocks.unregisterBlockStyle('core/table', ['regular', 'stripes']);
});

// Add heading attribute
addFilter(
    'blocks.registerBlockType',
    'fau-elemental/table-heading',
    (settings, name) => {
        if (name !== 'core/table') {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                tableHeading: {
                    type: 'string',
                    default: '',
                },
            },
            // Add save component to handle frontend rendering
            save: (props) => {
                const { attributes } = props;
                const blockProps = wp.blockEditor.useBlockProps.save({
                    className: 'wp-block-table-wrapper'
                });

                // Get the original saved content
                const originalSaveElement = settings.save(props);

                return (
                    <div {...blockProps}>
                        {attributes.tableHeading && (
                            <div className="wp-block-table__heading">
                                {attributes.tableHeading}
                            </div>
                        )}
                        {originalSaveElement}
                    </div>
                );
            },
        };
    }
);

// Add inspector controls
const withInspectorControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (name !== 'core/table') {
            return <BlockEdit {...props} />;
        }

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Table Settings">
                        <TextControl
                            label="Table Heading"
                            value={attributes.tableHeading || ''}
                            onChange={(value) => setAttributes({ tableHeading: value })}
                            help="Add a heading that will appear above the table"
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="wp-block-table-wrapper">
                    {attributes.tableHeading && (
                        <div className="wp-block-table__heading">
                            {attributes.tableHeading}
                        </div>
                    )}
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
