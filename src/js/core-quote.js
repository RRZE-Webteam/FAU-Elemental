import { unregisterBlockStyle } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { MediaUpload, MediaUploadCheck, InspectorControls, RichText } from '@wordpress/block-editor';
import { Button, PanelBody } from '@wordpress/components';
import { useEffect, useRef } from '@wordpress/element';

// Add custom attribute to quote block
addFilter(
    'blocks.registerBlockType',
    'my-plugin/quote-image-attribute',
    (settings, name) => {
        if (name !== 'core/quote') {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                quotes: {
                    type: 'array',
                    default: [{
                        id: Date.now(),
                        content: '',
                        citation: '',
                        image: null
                    }]
                }
            }
        };
    }
);

// Simple carousel initialization
const initCarousel = (container, initialSlide = 0) => {
    if (!container) return;

    const slides = container.querySelectorAll('.quote-slide');
    const prevButton = container.querySelector('.carousel-prev');
    const nextButton = container.querySelector('.carousel-next');
    const dots = container.querySelector('.carousel-dots');
    
    if (!slides.length || slides.length <= 1) {
        if (prevButton) prevButton.style.display = 'none';
        if (nextButton) nextButton.style.display = 'none';
        if (dots) dots.style.display = 'none';
        return;
    }

    let currentSlide = Math.min(initialSlide, slides.length - 1);

    const updateSlides = () => {
        slides.forEach((slide, index) => {
            if (slide) {
                slide.style.display = index === currentSlide ? 'block' : 'none';
            }
        });

        if (dots) {
            const dotButtons = dots.querySelectorAll('button');
            dotButtons.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }
    };

    // Clear and create new dots
    if (dots) {
        dots.innerHTML = '';
        slides.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlides();
            });
            dots.appendChild(dot);
        });
        dots.style.display = 'flex';
    }

    // Add click handlers directly without cloning
    if (prevButton) {
        prevButton.style.display = 'block';
        prevButton.onclick = () => {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            updateSlides();
        };
    }

    if (nextButton) {
        nextButton.style.display = 'block';
        nextButton.onclick = () => {
            currentSlide = (currentSlide + 1) % slides.length;
            updateSlides();
        };
    }

    updateSlides();
};

const withImageControl = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (props.name !== 'core/quote') {
            return <BlockEdit {...props} />;
        }

        const { attributes, setAttributes } = props;
        const carouselRef = useRef(null);
        const currentSlideRef = useRef(0);

        useEffect(() => {
            if (carouselRef.current) {
                initCarousel(carouselRef.current, currentSlideRef.current);
            }
        }, [attributes.quotes]);

        const addNewQuote = () => {
            const quotes = [...(attributes.quotes || [])];
            quotes.push({
                id: Date.now(),
                content: '',
                citation: '',
                image: null
            });
            // Set the current slide to the new quote
            currentSlideRef.current = quotes.length - 1;
            setAttributes({ quotes });
        };

        const updateQuote = (index, field, value) => {
            const quotes = [...attributes.quotes];
            quotes[index] = { ...quotes[index], [field]: value };
            setAttributes({ quotes });
        };

        const removeQuote = (index) => {
            const quotes = [...attributes.quotes];
            quotes.splice(index, 1);
            currentSlideRef.current = Math.min(currentSlideRef.current, Math.max(0, quotes.length - 1));
            setAttributes({ quotes });
        };

        const moveQuote = (index, direction) => {
            const quotes = [...attributes.quotes];
            const newIndex = index + direction;
            if (newIndex >= 0 && newIndex < quotes.length) {
                [quotes[index], quotes[newIndex]] = [quotes[newIndex], quotes[index]];
                setAttributes({ quotes });
            }
        };

        const renderQuotes = () => {
            if (!attributes.quotes?.length) return null;

            if (attributes.quotes.length === 1) {
                return (
                    <div className="wp-block-quote-item">
                        {renderQuoteContent(attributes.quotes[0], 0)}
                    </div>
                );
            }

            return (
                <div className="quote-carousel" ref={carouselRef}>
                    <div className="carousel-container">
                        {attributes.quotes.map((quote, index) => (
                            <div key={quote.id} className="quote-slide">
                                <div className="wp-block-quote-item">
                                    {renderQuoteContent(quote, index)}
                                </div>
                            </div>
                        ))}
                    </div>
                    <button className="carousel-prev" aria-label="Previous slide">❮</button>
                    <button className="carousel-next" aria-label="Next slide">❯</button>
                    <div className="carousel-dots"></div>
                </div>
            );
        };

        const renderQuoteContent = (quote, index) => (
            <div className="quote-wrapper">
                <div className="quote-controls-wrapper">
                    <Button 
                        icon="arrow-up-alt2" 
                        onClick={() => moveQuote(index, -1)}
                        disabled={index === 0}
                        className="quote-control-button"
                    >
                        Move Up
                    </Button>
                    <Button 
                        icon="arrow-down-alt2" 
                        onClick={() => moveQuote(index, 1)}
                        disabled={index === attributes.quotes.length - 1}
                        className="quote-control-button"
                    >
                        Move Down
                    </Button>
                    <Button 
                        icon="trash" 
                        onClick={() => removeQuote(index)}
                        isDestructive
                        className="quote-control-button"
                    >
                        Remove
                    </Button>
                </div>
                <div className="quote-content">
                    {quote.image && (
                        <figure className="quote-image">
                            <img 
                                src={quote.image.url} 
                                alt={quote.image.alt || ''} 
                            />
                        </figure>
                    )}
                    <div className="quote-text">
                        <RichText
                            tagName="blockquote"
                            value={quote.content}
                            onChange={(content) => updateQuote(index, 'content', content)}
                            placeholder="Enter quote text..."
                        />
                        <RichText
                            tagName="cite"
                            value={quote.citation}
                            onChange={(citation) => updateQuote(index, 'citation', citation)}
                            placeholder="Enter citation..."
                        />
                    </div>
                </div>
                <div className="quote-image-controls">
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={(media) => updateQuote(index, 'image', media)}
                            allowedTypes={['image']}
                            value={quote.image?.id}
                            render={({ open }) => (
                                <Button 
                                    onClick={open}
                                    variant="secondary"
                                >
                                    {!quote.image ? 'Add Image' : 'Change Image'}
                                </Button>
                            )}
                        />
                    </MediaUploadCheck>
                    {quote.image && (
                        <Button 
                            onClick={() => updateQuote(index, 'image', null)}
                            variant="link"
                            isDestructive
                        >
                            Remove Image
                        </Button>
                    )}
                </div>
            </div>
        );

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Quote Management" initialOpen={true}>
                        <Button variant="secondary" onClick={addNewQuote}>
                            Add New Quote
                        </Button>
                    </PanelBody>
                </InspectorControls>
                <div className="wp-block-quotes-container">
                    {renderQuotes()}
                </div>
            </>
        );
    };
}, 'withImageControl');

// Modify the frontend save element
addFilter(
    'blocks.getSaveElement',
    'my-plugin/quote-with-image',
    (element, block, attributes) => {
        if (block.name !== 'core/quote' || !attributes.quotes?.length) {
            return element;
        }

        const renderQuote = (quote) => (
            <div className="quote-content">
                {quote.image && (
                    <figure className="quote-image">
                        <img src={quote.image.url} alt={quote.image.alt || ''} />
                    </figure>
                )}
                <div className="quote-text">
                    <blockquote>
                        <RichText.Content value={quote.content} />
                    </blockquote>
                    {quote.citation && (
                        <cite>
                            <RichText.Content value={quote.citation} />
                        </cite>
                    )}
                </div>
            </div>
        );

        if (attributes.quotes.length === 1) {
            return (
                <div className="wp-block-quote-item">
                    {renderQuote(attributes.quotes[0])}
                </div>
            );
        }

        return (
            <div className="quote-carousel">
                <div className="carousel-container">
                    {attributes.quotes.map((quote) => (
                        <div key={quote.id} className="quote-slide">
                            <div className="wp-block-quote-item">
                                {renderQuote(quote)}
                            </div>
                        </div>
                    ))}
                </div>
                <button className="carousel-prev" aria-label="Previous slide">❮</button>
                <button className="carousel-next" aria-label="Next slide">❯</button>
                <div className="carousel-dots"></div>
            </div>
        );
    }
);

addFilter(
    'editor.BlockEdit',
    'my-plugin/quote-with-image',
    withImageControl
);

domReady(() => {
    // Unregister default styles
    unregisterBlockStyle('core/quote', ['default', 'plain']);
});