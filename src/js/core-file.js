const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, ToggleControl, Button } = wp.components;
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

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString();
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

// Add block attributes
addFilter(
    'blocks.registerBlockType',
    'core/file-dark-mode',
    (settings, name) => {
        if (name !== 'core/file') {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                darkMode: {
                    type: 'boolean',
                    default: false
                },
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
                const blockProps = wp.blockEditor.useBlockProps.save({
                    'data-dark-mode': attributes.darkMode || null,
                    className: attributes.darkMode ? 'dark' : ''
                });

                const originalContent = getSaveElement(settings, attributes);
                
                // Add file info elements for frontend display
                const fileInfoElements = attributes.fileDetails ? [
                    wp.element.createElement('div', { className: 'file-info-wrapper' }, [
                        wp.element.createElement('span', { className: 'file-info' }, 
                            formatDate(attributes.fileDetails.date)
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

        // Save file details to attributes when they change
        React.useEffect(() => {
            if (fileDetails) {
                setAttributes({
                    fileDetails: {
                        date: fileDetails.date,
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
                                        {formatDate(fileDetails.date)}
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