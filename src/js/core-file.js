import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { getSaveElement } from '@wordpress/blocks';
import { createElement, useEffect } from '@wordpress/element';

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
                align: false,
                displayPreview: false
            };
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

//Remove text placeholder
addFilter(
    'blocks.registerBlockType',
    'core/file-remove-placeholder',
    (settings, name) => {
        if (name !== 'core/file') {
            return settings;
        }

        const OriginalEdit = settings.edit;

        settings.edit = (props) => {
            const { setAttributes } = props;

            React.useEffect(() => {
                setAttributes({
                    downloadButtonText: ' ',  // space character
                    text: ' '  // space character
                });
            }, []);

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
                const blockProps = useBlockProps.save();
                
                // Get the original content with empty text
                const originalContent = getSaveElement(settings, {
                    ...attributes,
                    downloadButtonText: '',
                    text: ''
                });
                
                // Add file info elements for frontend display
                const fileInfoElements = attributes.fileDetails ? [
                    createElement('div', { className: 'file-info-wrapper' }, [
                        createElement('span', { className: 'file-info' }, 
                            attributes.fileDetails.filename
                        ),
                        createElement('span', { className: 'file-info' }, 
                            formatFileSize(attributes.fileDetails.filesize)
                        ),
                        createElement('span', { className: 'file-info' }, 
                            getFileType(attributes.fileDetails)
                        )
                    ])
                ] : [];

                return createElement(
                    'div',
                    blockProps,
                    createElement(
                        'div',
                        { className: 'wp-block-file__content-wrapper' },
                        [
                            attributes.coverImage && createElement(
                                'div',
                                { key: 'cover-image', className: 'file-cover-image' },
                                createElement('img', {
                                    src: attributes.coverImage.url,
                                    alt: attributes.coverImage.alt || ''
                                })
                            ),
                            createElement(
                                'div',
                                { className: 'wp-block-file' },
                                [
                                    createElement('div', { className: 'file-content' }, [
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

        const blockProps = useBlockProps();

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