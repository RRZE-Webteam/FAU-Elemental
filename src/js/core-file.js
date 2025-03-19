const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button } = wp.components;
const { useSelect } = wp.data;
const { getSaveElement } = wp.blocks;

// Shared utility functions
const formatFileSize = (bytes) => {
    if (!bytes) return '';
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes === 0) return '0 Byte';
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};

const getFileType = (fileDetails) => {
    if (!fileDetails?.mime_type) return '';
    const mimeType = fileDetails.mime_type;
    const mimeMap = {
        'application/pdf': 'PDF',
        'image/jpeg': 'JPEG',
        'image/png': 'PNG',
        'application/msword': 'DOC',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'DOCX',
        'application/vnd.ms-excel': 'XLS',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'XLSX',
    };
    return mimeMap[mimeType] || mimeType.split('/')[1].toUpperCase();
};

// Add this filter near the top with your other filters
addFilter(
    'editor.BlockListBlock',
    'core/file/add-pdf-class',
    (BlockListBlock) => (props) => {
        if (props.name !== 'core/file') {
            return <BlockListBlock {...props} />;
        }

        const { attributes } = props;
        const isPDF = attributes?.fileDetails?.mime_type === 'application/pdf';
        
        return (
            <BlockListBlock
                {...props}
                className={isPDF ? 'is-pdf-file' : ''}
            />
        );
    }
);

// Replace your existing PDF-related filters with this one
addFilter(
    'blocks.registerBlockType',
    'core/file-remove-pdf-settings',
    (settings, name) => {
        if (name !== 'core/file') {
            return settings;
        }

        // Remove PDF preview support entirely
        if (settings.supports) {
            settings.supports = {
                ...settings.supports,
                displayPreview: false
            };
        }

        // Remove PDF-related attributes
        if (settings.attributes) {
            const { displayPreview, ...otherAttributes } = settings.attributes;
            settings.attributes = otherAttributes;
        }

        // Store the original edit component
        const OriginalEdit = settings.edit;

        // Wrap the edit component to ensure PDF settings stay disabled
        settings.edit = (props) => {
            const { attributes, setAttributes } = props;

            // Remove displayPreview if it somehow gets added
            React.useEffect(() => {
                if (attributes.displayPreview !== undefined) {
                    setAttributes({ displayPreview: undefined });
                }
            }, [attributes.displayPreview]);

            return <OriginalEdit {...props} />;
        };

        return settings;
    }
);

// Add block attributes
addFilter(
    'blocks.registerBlockType',
    'core/file-extended',
    (settings, name) => {
        if (name !== 'core/file') {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                coverImage: {
                    type: 'object',
                    default: null
                },
                fileDetails: {
                    type: 'object',
                    default: null
                }
            },
            save: (props) => {
                const { attributes } = props;
                const blockProps = wp.blockEditor.useBlockProps.save();

                const originalContent = getSaveElement(settings, attributes);
                
                // Add file info elements for frontend display
                const fileInfoElements = attributes.fileDetails ? [
                    wp.element.createElement('div', { className: 'file-info-wrapper' }, [
                        wp.element.createElement('span', { className: 'file-info' }, 
                            attributes.fileDetails.filename
                        ),
                        wp.element.createElement('span', { className: 'file-info' }, 
                            formatFileSize(attributes.fileDetails.filesize)
                        ),
                        wp.element.createElement('span', { className: 'file-info' }, 
                            getFileType(attributes.fileDetails)
                        )
                    ])
                ] : [];

                return wp.element.createElement(
                    'div',
                    blockProps,
                    wp.element.createElement(
                        'div',
                        { className: 'wp-block-file__content-wrapper' },
                        [
                            attributes.coverImage && wp.element.createElement(
                                'div',
                                { key: 'cover-image', className: 'file-cover-image' },
                                wp.element.createElement('img', {
                                    src: attributes.coverImage.url,
                                    alt: attributes.coverImage.alt || ''
                                })
                            ),
                            wp.element.createElement(
                                'div',
                                { className: 'wp-block-file' },
                                [
                                    wp.element.createElement('div', { className: 'file-content' }, [
                                        originalContent,
                                        ...fileInfoElements
                                    ])
                                ]
                            )
                        ].filter(Boolean)
                    )
                );
            }
        };
    }
);

// Add inspector controls
const withInspectorControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (name !== 'core/file') {
            return <BlockEdit {...props} />;
        }

        // Get file details using useSelect
        const fileDetails = useSelect((select) => {
            const { getMedia } = select('core');
            return attributes.id ? getMedia(attributes.id) : null;
        }, [attributes.id]);

        // Add PDF class and save file details when fileDetails changes
        React.useEffect(() => {
            if (fileDetails) {
                const isPDF = fileDetails.mime_type === 'application/pdf';
                document.body.classList.toggle('is-pdf-file', isPDF);
                
                // const blockWrapper = document.querySelector('.faue-is-file-block-selected');
                // if (blockWrapper) {
                //     if (fileDetails.mime_type === 'application/pdf') {
                //         blockWrapper.classList.add('is-pdf-file');
                //     } else {
                //         blockWrapper.classList.remove('is-pdf-file');
                //     }
                // }

                setAttributes({
                    fileDetails: {
                        filename: fileDetails.title?.rendered || fileDetails.filename || fileDetails.source_url?.split('/').pop(),
                        filesize: fileDetails.media_details?.filesize,
                        mime_type: fileDetails.mime_type
                    }
                });
            }
        }, [fileDetails]);

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
                        <div className="editor-file-cover-image">
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
                    <div className="wp-block-file__content-wrapper">
                        {attributes.coverImage && (
                            <div className="file-cover-image">
                                <img 
                                    src={attributes.coverImage.url} 
                                    alt={attributes.coverImage.alt || ''} 
                                />
                            </div>
                        )}
                        <div className="wp-block-file">
                            <BlockEdit {...props} />
                            {fileDetails && (
                                <>
                                    <span className="file-info">
                                        {fileDetails.title?.rendered || fileDetails.filename || fileDetails.source_url?.split('/').pop()}
                                    </span>
                                    <span className="file-info">
                                        {formatFileSize(fileDetails.media_details?.filesize)}
                                    </span>
                                    <span className="file-info">
                                        {getFileType(fileDetails)}
                                    </span>
                                </>
                            )}
                        </div>
                    </div>
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