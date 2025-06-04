import { __ } from '@wordpress/i18n';
import { 
    PanelBody, 
    TextControl, 
    TextareaControl, 
    ToggleControl,
    SelectControl,
    Button
} from '@wordpress/components';
import { 
    InspectorControls, 
    MediaUpload, 
    MediaUploadCheck,
    useBlockProps 
} from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
    const {
        roofLine,
        headline,
        teaserText,
        linkText,
        linkUrl,
        linkTarget,
        image,
        showRoofLine,
    } = attributes;

    const blockProps = useBlockProps();

    const onSelectImage = (media) => {
        setAttributes({
            image: {
                id: media.id,
                url: media.url,
                alt: media.alt
            }
        });
    };

    const removeImage = () => {
        setAttributes({ image: null });
    };

    return (
        <Fragment>
            <InspectorControls>
                <PanelBody title={__('Feature Teaser Settings', 'fau-elemental')} initialOpen={true}>
                    <ToggleControl
                        label={__('Show Roof Line', 'fau-elemental')}
                        checked={showRoofLine}
                        onChange={(value) => setAttributes({ showRoofLine: value })}
                        help={__('Display an optional roof line above the headline', 'fau-elemental')}
                    />

                    <SelectControl
                        label={__('Link Target', 'fau-elemental')}
                        value={linkTarget}
                        options={[
                            { label: __('Same Window', 'fau-elemental'), value: '_self' },
                            { label: __('New Window', 'fau-elemental'), value: '_blank' }
                        ]}
                        onChange={(value) => setAttributes({ linkTarget: value })}
                    />
                </PanelBody>

                <PanelBody title={__('Content', 'fau-elemental')} initialOpen={true}>
                    {showRoofLine && (
                        <TextControl
                            label={__('Roof Line (max 50 characters)', 'fau-elemental')}
                            value={roofLine}
                            onChange={(value) => setAttributes({ roofLine: value.substring(0, 50) })}
                            placeholder={__('Enter roof line text...', 'fau-elemental')}
                            help={`${roofLine.length}/50 characters`}
                        />
                    )}

                    <TextControl
                        label={__('Headline (max 100 characters)', 'fau-elemental')}
                        value={headline}
                        onChange={(value) => setAttributes({ headline: value.substring(0, 100) })}
                        placeholder={__('Enter headline...', 'fau-elemental')}
                        help={`${headline.length}/100 characters`}
                    />

                    <TextareaControl
                        label={__('Teaser Text (max 200 characters)', 'fau-elemental')}
                        value={teaserText}
                        onChange={(value) => setAttributes({ teaserText: value.substring(0, 200) })}
                        placeholder={__('Enter teaser text...', 'fau-elemental')}
                        help={`${teaserText.length}/200 characters`}
                        rows={3}
                    />

                    <TextControl
                        label={__('Link Text (max 40 characters)', 'fau-elemental')}
                        value={linkText}
                        onChange={(value) => setAttributes({ linkText: value.substring(0, 40) })}
                        placeholder={__('Enter link text...', 'fau-elemental')}
                        help={`${linkText.length}/40 characters`}
                    />

                    <TextControl
                        label={__('Link URL', 'fau-elemental')}
                        value={linkUrl}
                        onChange={(value) => setAttributes({ linkUrl: value })}
                        placeholder={__('Enter URL...', 'fau-elemental')}
                        type="url"
                    />
                </PanelBody>

                <PanelBody title={__('Image Settings', 'fau-elemental')} initialOpen={false}>
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={onSelectImage}
                            allowedTypes={['image']}
                            value={image?.id}
                            render={({ open }) => (
                                <Button
                                    onClick={open}
                                    variant="secondary"
                                    style={{ marginBottom: '10px', display: 'block' }}
                                >
                                    {image ? __('Replace Image', 'fau-elemental') : __('Select Image', 'fau-elemental')}
                                </Button>
                            )}
                        />
                    </MediaUploadCheck>
                    
                    {image && (
                        <Button
                            onClick={removeImage}
                            variant="tertiary"
                            isDestructive
                        >
                            {__('Remove Image', 'fau-elemental')}
                        </Button>
                    )}
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                {/* Frontend-style preview */}
                <div className="fau-big-teaser-editor-preview">
                    <div className="fau-big-teaser__content">
                        {showRoofLine && roofLine && (
                            <div className="fau-big-teaser__roof-line">
                                {roofLine}
                            </div>
                        )}

                        {headline && (
                            <h3 className="fau-big-teaser__headline">
                                {headline}
                            </h3>
                        )}

                        {teaserText && (
                            <p className="fau-big-teaser__teaser-text">
                                {teaserText}
                            </p>
                        )}

                        {linkText && linkUrl && (
                            <a 
                                href={linkUrl} 
                                target={linkTarget}
                                className="fau-big-teaser__link"
                                onClick={(e) => e.preventDefault()}
                            >
                                {linkText}
                            </a>
                        )}
                    </div>

                    {image && (
                        <div className="fau-big-teaser__image">
                            <img src={image.url} alt={image.alt || headline} />
                        </div>
                    )}

                    {/* Image placeholder when no image selected */}
                    {!image && (
                        <div className="fau-big-teaser__image-placeholder">
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={onSelectImage}
                                    allowedTypes={['image']}
                                    render={({ open }) => (
                                        <Button
                                            onClick={open}
                                            variant="secondary"
                                            className="fau-big-teaser__add-image-button"
                                        >
                                            <span className="dashicon dashicons-plus-alt2"></span>
                                            {__('Add Image', 'fau-elemental')}
                                        </Button>
                                    )}
                                />
                            </MediaUploadCheck>
                        </div>
                    )}
                </div>
            </div>
        </Fragment>
    );
} 