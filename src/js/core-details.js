const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button } = wp.components;
const { useSelect } = wp.data;
const { getSaveElement } = wp.blocks;
const { Children } = wp.element;

// Add block attributes
addFilter(
    'blocks.registerBlockType',
    'core/details-extended',
    (settings, name) => {
        if (name !== 'core/details') {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                coverImage: {
                    type: 'object',
                    default: null
                }
            },
            save: (props) => {
                const { attributes } = props;
                
                // First get the original save output
                const originalSave = settings.save;
                const originalContent = originalSave(props);

                // If no cover image, return original content unchanged
                if (!attributes.coverImage) {
                    return originalContent;
                }

                // Create the cover image element
                const coverImageElement = wp.element.createElement(
                    'div',
                    { className: 'details-image' },
                    wp.element.createElement('img', {
                        src: attributes.coverImage.url,
                        alt: attributes.coverImage.alt || ''
                    })
                );

                // Get the original children
                const children = Children.toArray(originalContent.props.children);
                const summary = children.find(child => child.type === 'summary');
                const otherContent = children.filter(child => child.type !== 'summary');

                // Clone and modify the summary to add our custom class
                const modifiedSummary = wp.element.cloneElement(
                    summary,
                    { 
                        ...summary.props,
                        className: 'custom-summary'
                    }
                );

                // Create new content with image after summary
                return wp.element.cloneElement(
                    originalContent,
                    originalContent.props,
                    modifiedSummary,
                    coverImageElement,
                    ...otherContent
                );
            }
        };
    }
);

// Add inspector controls
const withInspectorControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (name !== 'core/details') {
            return <BlockEdit {...props} />;
        }

        // Image handling functions
        const onSelectImage = (media) => {
            setAttributes({
                coverImage: {
                    id: media.id,
                    url: media.url,
                    alt: media.alt || ''
                }
            });
        };

        const blockProps = wp.blockEditor.useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Block Settings">
                        <div className="editor-details-image">
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={onSelectImage}
                                    allowedTypes={['image']}
                                    value={attributes.coverImage?.id}
                                    render={({ open }) => (
                                        <div>
                                            {!attributes.coverImage && (
                                                <Button 
                                                    onClick={open}
                                                    variant="secondary"
                                                >
                                                    Add Cover Image
                                                </Button>
                                            )}
                                            {attributes.coverImage && (
                                                <div>
                                                    <img 
                                                        src={attributes.coverImage.url}
                                                        alt={attributes.coverImage.alt}
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
                                                            onClick={() => setAttributes({ coverImage: null })}
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
                        </div>
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    {attributes.coverImage && (
                        <div className="details-image">
                            <img 
                                src={attributes.coverImage.url} 
                                alt={attributes.coverImage.alt || ''} 
                            />
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