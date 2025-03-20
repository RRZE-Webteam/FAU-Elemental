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

// Add block attributes
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
                },
                quotes: {
                    type: 'array',
                    default: []
                },
                currentQuoteIndex: {
                    type: 'number',
                    default: 0
                }
            }
        };
    }
);

// Add save element modifications
addFilter(
    'blocks.getSaveElement',
    'fau-elemental/with-quote-save',
    (element, blockType, attributes) => {
        if (blockType.name !== 'core/quote') {
            return element;
        }

        const blockProps = wp.blockEditor.useBlockProps.save();

        if (!attributes.isCarousel) {
            return createElement(
                'div',
                blockProps,
                [
                    attributes.quoteImage && createElement('div', 
                        { className: 'quote-image' }, 
                        createElement('img', {
                            src: attributes.quoteImage.url,
                            alt: attributes.quoteImage.alt || ''
                        })
                    ),
                    element
                ]
            );
        }

        return createElement(
            'div',
            { ...blockProps, className: 'quote-carousel' },
            [
                createElement(
                    'div',
                    { className: 'quote-carousel-slides' },
                    attributes.quotes.map((quote, index) => 
                        createElement(
                            'div',
                            { 
                                key: index,
                                className: 'quote-slide',
                                style: { display: index === 0 ? 'block' : 'none' }
                            },
                            [
                                quote.image && createElement('div', 
                                    { className: 'quote-image' }, 
                                    createElement('img', {
                                        src: quote.image.url,
                                        alt: quote.image.alt || ''
                                    })
                                ),
                                createElement(
                                    'blockquote',
                                    { className: 'wp-block-quote' },
                                    [
                                        createElement('p', null, quote.content),
                                        quote.citation && createElement('cite', null, quote.citation)
                                    ]
                                )
                            ]
                        )
                    )
                ),
                attributes.quotes.length > 1 && createElement(
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

// Add inspector controls
addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-quote-inspector-controls',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, setAttributes, name } = props;

            if (name !== 'core/quote') {
                return <BlockEdit {...props} />;
            }

            const { quotes = [], currentQuoteIndex = 0, isCarousel = false } = attributes;

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title="Quote Settings">
                            <ToggleControl
                                label="Enable Multiple Quotes Carousel"
                                checked={isCarousel}
                                onChange={(value) => {
                                    setAttributes({ isCarousel: value });
                                    if (value && quotes.length === 0) {
                                        setAttributes({ quotes: [{ content: '', citation: '' }] });
                                    }
                                }}
                            />
                            
                            {!isCarousel ? (
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
                            ) : (
                                <Fragment>
                                    <Button 
                                        variant="secondary"
                                        onClick={() => {
                                            setAttributes({ 
                                                quotes: [...quotes, {}],
                                                currentQuoteIndex: quotes.length
                                            });
                                        }}
                                    >
                                        Add New Quote
                                    </Button>
                                    
                                    <div className="quote-selector">
                                        {quotes.map((_, index) => (
                                            <Button
                                                key={index}
                                                variant={currentQuoteIndex === index ? 'primary' : 'secondary'}
                                                onClick={() => setAttributes({ currentQuoteIndex: index })}
                                            >
                                                {index + 1}
                                            </Button>
                                        ))}
                                    </div>

                                    <TextareaControl
                                        label="Quote Content"
                                        value={quotes[currentQuoteIndex]?.content || ''}
                                        onChange={(content) => {
                                            const updatedQuotes = [...quotes];
                                            if (!updatedQuotes[currentQuoteIndex]) {
                                                updatedQuotes[currentQuoteIndex] = {};
                                            }
                                            updatedQuotes[currentQuoteIndex].content = content;
                                            setAttributes({ quotes: updatedQuotes });
                                        }}
                                    />
                                    <TextControl
                                        label="Citation"
                                        value={quotes[currentQuoteIndex]?.citation || ''}
                                        onChange={(citation) => {
                                            const updatedQuotes = [...quotes];
                                            if (!updatedQuotes[currentQuoteIndex]) {
                                                updatedQuotes[currentQuoteIndex] = {};
                                            }
                                            updatedQuotes[currentQuoteIndex].citation = citation;
                                            setAttributes({ quotes: updatedQuotes });
                                        }}
                                    />
                                    <MediaUploadCheck>
                                        <MediaUpload
                                            onSelect={(media) => {
                                                const updatedQuotes = [...quotes];
                                                if (!updatedQuotes[currentQuoteIndex]) {
                                                    updatedQuotes[currentQuoteIndex] = {};
                                                }
                                                updatedQuotes[currentQuoteIndex].image = {
                                                    id: media.id,
                                                    url: media.url,
                                                    alt: media.alt || ''
                                                };
                                                setAttributes({ quotes: updatedQuotes });
                                            }}
                                            allowedTypes={['image']}
                                            value={quotes[currentQuoteIndex]?.image?.id}
                                            render={({ open }) => (
                                                <div>
                                                    {!quotes[currentQuoteIndex]?.image ? (
                                                        <Button onClick={open} variant="secondary">
                                                            Add Quote Image
                                                        </Button>
                                                    ) : (
                                                        <div>
                                                            <img 
                                                                src={quotes[currentQuoteIndex].image.url}
                                                                alt={quotes[currentQuoteIndex].image.alt}
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
                                                                    onClick={() => {
                                                                        const updatedQuotes = [...quotes];
                                                                        updatedQuotes[currentQuoteIndex].image = null;
                                                                        setAttributes({ quotes: updatedQuotes });
                                                                    }}
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
                                </Fragment>
                            )}
                        </PanelBody>
                    </InspectorControls>
                    <BlockEdit {...props} />
                </Fragment>
            );
        };
    }, 'withQuoteInspectorControls')
);

// Add editor wrapper for carousel view
addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-quote-editor-wrapper',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, name } = props;

            if (name !== 'core/quote' || !attributes.isCarousel) {
                return <BlockEdit {...props} />;
            }

            const { quotes = [], currentQuoteIndex = 0 } = attributes;
            const currentQuote = quotes[currentQuoteIndex] || {};

            return (
                <div className="quote-carousel">
                    <div className="quote-carousel-slides">
                        <div className="quote-slide" style={{ display: 'block' }}>
                            {currentQuote.image && (
                                <div className="quote-image">
                                    <img 
                                        src={currentQuote.image.url}
                                        alt={currentQuote.image.alt || ''}
                                    />
                                </div>
                            )}
                            <BlockEdit {...props} />
                        </div>
                    </div>
                    {quotes.length > 1 && (
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