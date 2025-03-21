const { createElement, Fragment } = wp.element;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button, TextareaControl, TextControl, ToggleControl } = wp.components;
const { unregisterBlockStyle } = wp.blocks;

wp.domReady(() => {
    // Unregister default styles
    unregisterBlockStyle('core/quote', 'default');
    unregisterBlockStyle('core/quote', 'plain');
});

// Modify block attributes
addFilter(
    'blocks.registerBlockType',
    'fau-elemental/quote-attributes',
    (settings, name) => {
        if (name !== 'core/quote') {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                isCarousel: {
                    type: 'boolean',
                    default: false
                },
                quoteImage: {
                    type: 'object',
                    default: null
                }
            }
        };
    }
);

// Modify save element
addFilter(
    'blocks.getSaveElement',
    'fau-elemental/with-quote-save',
    (element, blockType, attributes) => {
        if (blockType.name !== 'core/quote') {
            return element;
        }

        const { InnerBlocks } = wp.blockEditor;
        const blockProps = wp.blockEditor.useBlockProps.save();

        // Always include the image if it exists
        const imageElement = attributes.quoteImage && createElement('div', 
            { className: 'quote-image' }, 
            createElement('img', {
                src: attributes.quoteImage.url,
                alt: attributes.quoteImage.alt || ''
            })
        );

        if (!attributes.isCarousel) {
            // For non-carousel mode, preserve the original blockquote structure
            const blockquote = createElement(
                'blockquote',
                { className: 'wp-block-quote' },
                [
                    imageElement,
                    ...element.props.children // Spread the original quote content
                ]
            );

            return blockquote;
        }

        // For carousel mode
        return createElement(
            'div',
            { ...blockProps, className: 'quote-carousel' },
            [
                createElement(
                    'div',
                    { className: 'quote-carousel-slides' },
                    createElement(InnerBlocks.Content)
                ),
                createElement(
                    'div',
                    { className: 'quote-carousel-nav' },
                    [
                        createElement('button', { className: 'prev', type: 'button' }, '←'),
                        createElement('button', { className: 'next', type: 'button' }, '→')
                    ]
                )
            ]
        );
    }
);

// Modify editor wrapper
addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-quote-editor-wrapper',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, name, clientId } = props;
            const { InnerBlocks, useBlockProps } = wp.blockEditor;
            const { select } = wp.data;

            if (name !== 'core/quote') {
                return <BlockEdit {...props} />;
            }

            // Check if this quote block is a child of another quote block
            const parentBlock = select('core/block-editor').getBlockParents(clientId);
            const isChildQuote = parentBlock.length > 0 && 
                select('core/block-editor').getBlockName(parentBlock[0]) === 'core/quote';

            // Get block props
            const blockProps = useBlockProps();

            // If it's a child quote or not a carousel, render normally
            if (isChildQuote || !attributes.isCarousel) {
                return <BlockEdit {...props} />;
            }

            // For carousel mode
            return (
                <div {...blockProps} className={`${blockProps.className} quote-carousel`}>
                    <div className="quote-carousel-slides">
                        <InnerBlocks
                            allowedBlocks={['core/quote']}
                            template={[['core/quote']]}
                            templateLock={false}
                            renderAppender={
                                () => <InnerBlocks.ButtonBlockAppender />
                            }
                        />
                    </div>
                    {select('core/block-editor').getBlocks(clientId).length > 1 && (
                        <div className="quote-carousel-nav">
                            <button className="prev" type="button">←</button>
                            <button className="next" type="button">→</button>
                        </div>
                    )}
                </div>
            );
        };
    }, 'withQuoteEditorWrapper')
);

// Update inspector controls to handle the transition
addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-quote-inspector-controls',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, setAttributes, name, clientId } = props;
            const { select, dispatch } = wp.data;

            if (name !== 'core/quote') {
                return <BlockEdit {...props} />;
            }

            // Check if this quote block is a child of another quote block
            const parentBlock = select('core/block-editor').getBlockParents(clientId);
            const isChildQuote = parentBlock.length > 0 && 
                select('core/block-editor').getBlockName(parentBlock[0]) === 'core/quote';

            // Check if this block has inner quote blocks
            const innerBlocks = select('core/block-editor').getBlocks(clientId);
            const hasInnerQuotes = innerBlocks.some(block => block.name === 'core/quote');

            const handleCarouselToggle = (value) => {
                if (value) {
                    // Switching to carousel mode
                    setAttributes({ 
                        isCarousel: value,
                        quoteImage: null 
                    });
                    
                    // Ensure there's at least one inner quote block
                    if (innerBlocks.length === 0) {
                        const currentContent = select('core/block-editor').getBlockAttributes(clientId);
                        dispatch('core/block-editor').insertBlock(
                            wp.blocks.createBlock('core/quote', {
                                content: currentContent.content,
                                citation: currentContent.citation
                            }),
                            0,
                            clientId
                        );
                    }
                } else {
                    // Switching from carousel mode
                    setAttributes({ isCarousel: value });
                }
            };

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title="Quote Settings">
                            {!isChildQuote && (
                                <ToggleControl
                                    label="Enable Quotes Carousel"
                                    checked={attributes.isCarousel}
                                    onChange={handleCarouselToggle}
                                />
                            )}
                            {!hasInnerQuotes && (
                                <MediaUploadCheck>
                                    <MediaUpload
                                        onSelect={(media) => {
                                            setAttributes({
                                                quoteImage: {
                                                    id: media.id,
                                                    url: media.url,
                                                    alt: media.alt || ''
                                                }
                                            });
                                        }}
                                        allowedTypes={['image']}
                                        value={attributes.quoteImage?.id}
                                        render={({ open }) => (
                                            <div>
                                                {!attributes.quoteImage ? (
                                                    <Button onClick={open} variant="secondary">
                                                        Add Quote Image
                                                    </Button>
                                                ) : (
                                                    <div>
                                                        <img 
                                                            src={attributes.quoteImage.url}
                                                            alt={attributes.quoteImage.alt}
                                                            style={{ maxWidth: '100%', marginBottom: '8px' }}
                                                        />
                                                        <div>
                                                            <Button 
                                                                onClick={open}
                                                                variant="secondary"
                                                                style={{ marginRight: '8px' }}
                                                            >
                                                                Replace
                                                            </Button>
                                                            <Button 
                                                                onClick={() => setAttributes({ quoteImage: null })}
                                                                variant="secondary"
                                                                isDestructive
                                                            >
                                                                Remove
                                                            </Button>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                    />
                                </MediaUploadCheck>
                            )}
                        </PanelBody>
                    </InspectorControls>
                    <BlockEdit {...props} />
                </Fragment>
            );
        };
    }, 'withQuoteInspectorControls')
);