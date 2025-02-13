// custom-button.js
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl, SelectControl } = wp.components;

// Add custom attributes to the button block
addFilter('blocks.registerBlockType', 'my-plugin/button-custom-attributes', (settings, name) => {
    if (name !== 'core/button') {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            // Add custom attributes
            isAnimated: {
                type: 'boolean',
                default: false,
            },
            animationType: {
                type: 'string',
                default: 'fade',
            },
            customBorderRadius: {
                type: 'string',
                default: '',
            },
        },
    };
});

// Add custom controls to the button block
const withCustomControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (props.name !== 'core/button') {
            return <BlockEdit {...props} />;
        }

        const { attributes, setAttributes } = props;
        const { isAnimated, animationType, customBorderRadius } = attributes;

        return (
            <Fragment>
                <BlockEdit {...props} />
                <InspectorControls>
                    <PanelBody title="Custom Button Settings" initialOpen={true}>
                        <ToggleControl
                            label="Enable Animation"
                            checked={isAnimated}
                            onChange={(value) => setAttributes({ isAnimated: value })}
                        />
                        {isAnimated && (
                            <SelectControl
                                label="Animation Type"
                                value={animationType}
                                options={[
                                    { label: 'Fade', value: 'fade' },
                                    { label: 'Slide', value: 'slide' },
                                    { label: 'Bounce', value: 'bounce' },
                                ]}
                                onChange={(value) => setAttributes({ animationType: value })}
                            />
                        )}
                        <SelectControl
                            label="Border Radius"
                            value={customBorderRadius}
                            options={[
                                { label: 'Default', value: '' },
                                { label: 'Round', value: 'round' },
                                { label: 'Pill', value: 'pill' },
                                { label: 'Square', value: 'square' },
                            ]}
                            onChange={(value) => setAttributes({ customBorderRadius: value })}
                        />
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withCustomControls');

addFilter('editor.BlockEdit', 'my-plugin/button-custom-controls', withCustomControls);

// Add custom classes based on attributes
addFilter('blocks.getSaveContent.extraProps', 'my-plugin/button-custom-classes', (extraProps, blockType, attributes) => {
    if (blockType.name !== 'core/button') {
        return extraProps;
    }

    const { isAnimated, animationType, customBorderRadius } = attributes;

    if (isAnimated) {
        extraProps.className = `${extraProps.className || ''} animate-${animationType}`;
    }

    if (customBorderRadius) {
        extraProps.className = `${extraProps.className || ''} border-${customBorderRadius}`;
    }

    return extraProps;
});