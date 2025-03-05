const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { useEffect } = wp.element;
const { getBlockType, registerBlockType, registerBlockVariation, unregisterBlockVariation } = wp.blocks;
const { BlockControls, InspectorControls } = wp.blockEditor;
const { ToolbarGroup, ToolbarButton, PanelBody, SelectControl } = wp.components;

// Get the original Heading block
const headingBlock = getBlockType('core/heading');

wp.domReady(() => {
    // Register "Intro Text" variation for core/paragraph with an icon
    registerBlockVariation('core/paragraph', {
        name: 'text',
        title: 'Text',
        description: 'A paragraph.',
        attributes: {
            className: 'text',
        },
        icon: 'editor-paragraph', // Dashicon for text
        isDefault: true,
        scope: ['block', 'inserter', 'transform'],
    });

    // Register "Intro Text" variation for core/paragraph with an icon
    registerBlockVariation('core/paragraph', {
        name: 'intro-text',
        title: 'Intro Text',
        description: 'A paragraph styled as an introduction.',
        attributes: {
            className: 'intro-text',
        },
        icon: 'editor-paragraph', // Dashicon for text
        isDefault: false,
        scope: ['block', 'inserter', 'transform'],
    });

    // Register "Small Text" variation for core/paragraph with an icon
    registerBlockVariation('core/paragraph', {
        name: 'small-text',
        title: 'Small Text',
        description: 'A smaller paragraph for fine print or secondary content.',
        attributes: {
            className: 'small-text',
        },
        icon: 'editor-paragraph', // Dashicon for paragraph text
        isDefault: false,
        scope: ['block', 'inserter', 'transform'],
    });
});

addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-selected-class',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { isSelected, name, attributes } = props;

            useEffect(() => {
                if (isSelected) {
                    const isHeadingBlock = name === 'core/heading';
                    const isParagraphBlock = name === 'core/paragraph';
                    const isIntroText = isParagraphBlock && attributes.className?.includes('intro-text');

                    // Toggle classes based on block type and variation
                    document.body.classList.toggle('faue-is-heading-block-selected', isHeadingBlock);
                    document.body.classList.toggle('faue-is-paragraph-block-selected', isParagraphBlock);
                    document.body.classList.toggle('faue-is-intro-text-selected', isIntroText);
                }
            }, [isSelected, attributes.className]);

            return <BlockEdit {...props} />;
        };
    }, 'withSelectedClass')
);

addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-list-style-controls',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, setAttributes, name } = props;

            // Only show for list blocks
            if (name !== 'core/list') {
                return <BlockEdit {...props} />;
            }

            // Only show for unordered lists
            const isUnordered = !attributes.ordered;
            
            return (
                <>
                    {isUnordered && (
                        <InspectorControls>
                            <PanelBody title="List Style Settings">
                                <SelectControl
                                    label="List Style"
                                    value={attributes.className?.includes('list-icons') ? 'list-icons' : 'dots'}
                                    options={[
                                        { label: 'Dots', value: 'dots' },
                                        { label: 'Icons', value: 'list-icons' }
                                    ]}
                                    onChange={(value) => {
                                        // Get current classes as an array
                                        const currentClasses = attributes.className ? 
                                            attributes.className.split(' ').filter(cls => cls !== 'list-icons') : 
                                            [];
                                        
                                        // Add the new class if it's not 'dots'
                                        if (value !== 'dots') {
                                            currentClasses.push(value);
                                        }
                                        
                                        // Set the new className
                                        setAttributes({
                                            className: currentClasses.length > 0 ? 
                                                currentClasses.join(' ') : 
                                                undefined
                                        });
                                    }}
                                />
                            </PanelBody>
                        </InspectorControls>
                    )}
                    <BlockEdit {...props} />
                </>
            );
        };
    }, 'withListStyleControls')
);
