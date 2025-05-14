import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, Button } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { useEffect } from '@wordpress/element';
import { addFilter } from '@wordpress/hooks';
import './style-index.scss';
import metadata from './block.json';

// Ensure media scripts are loaded
addFilter('blocks.registerBlockType', 'fau/logo-grid', (settings, name) => {
    if (name !== 'fau/logo-grid') {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes
        }
    };
});

registerBlockType(metadata.name, {
    ...metadata,
    title: __('FAU Logo Grid', 'fau-elemental'),
    description: __('Display a grid of logos with optional links and roof line', 'fau-elemental'),
    keywords: [__('logo', 'fau-elemental'), __('grid', 'fau-elemental'), __('image', 'fau-elemental')],
    edit: function Edit({ attributes, setAttributes }) {
        const blockProps = useBlockProps();
        const { roofLine, showRoofLine, logos = [], selectedLogoIndex = null } = attributes;

        useEffect(() => {
            // Ensure logos array is initialized
            if (!Array.isArray(logos)) {
                setAttributes({ logos: [] });
            }
        }, []);

        const onSelectImage = (index, media) => {
            const newLogos = [...logos];
            newLogos[index] = {
                ...newLogos[index],
                imageId: media.id,
                imageUrl: media.url
            };
            setAttributes({ logos: newLogos });
        };

        const updateLogoLink = (link) => {
            if (selectedLogoIndex === null || !logos[selectedLogoIndex]) return;
            const newLogos = [...logos];
            newLogos[selectedLogoIndex] = {
                ...newLogos[selectedLogoIndex],
                link
            };
            setAttributes({ logos: newLogos });
        };

        const addLogo = () => {
            setAttributes({
                logos: [...logos, { imageId: 0, imageUrl: '', link: '' }],
                selectedLogoIndex: logos.length // select the new logo
            });
        };

        const removeLogo = (index) => {
            const newLogos = [...logos];
            newLogos.splice(index, 1);
            let newSelected = selectedLogoIndex;
            if (selectedLogoIndex === index) newSelected = null;
            else if (selectedLogoIndex > index) newSelected = selectedLogoIndex - 1;
            setAttributes({ logos: newLogos, selectedLogoIndex: newSelected });
        };

        const selectLogo = (index) => {
            setAttributes({ selectedLogoIndex: index });
        };

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Logo Grid Settings', 'fau-elemental')} initialOpen={true}>
                        <ToggleControl
                            label={__('Show Roof Line', 'fau-elemental')}
                            checked={showRoofLine}
                            onChange={(value) => setAttributes({ showRoofLine: value })}
                        />
                        {showRoofLine && (
                            <TextControl
                                label={__('Roof Line Text', 'fau-elemental')}
                                value={roofLine}
                                onChange={(value) => setAttributes({ roofLine: value })}
                            />
                        )}
                    </PanelBody>
                    {selectedLogoIndex !== null && logos[selectedLogoIndex] && (
                        <PanelBody title={__('Selected Logo Settings', 'fau-elemental')} initialOpen={true}>
                            <TextControl
                                label={__('Logo Link', 'fau-elemental')}
                                value={logos[selectedLogoIndex].link || ''}
                                onChange={updateLogoLink}
                            />
                            <Button
                                isDestructive
                                onClick={() => removeLogo(selectedLogoIndex)}
                                style={{ marginTop: '1rem' }}
                            >
                                {__('Remove Logo', 'fau-elemental')}
                            </Button>
                        </PanelBody>
                    )}
                </InspectorControls>

                <div {...blockProps}>
                    {showRoofLine && roofLine && (
                        <div className="fau-logo-grid__roof-line">
                            {roofLine}
                        </div>
                    )}

                    <div className="fau-logo-grid__container">
                        {Array.isArray(logos) && logos.map((logo, index) => (
                            <div
                                key={index}
                                className={
                                    'fau-logo-grid__item' +
                                    (selectedLogoIndex === index ? ' fau-logo-grid__item--selected' : '')
                                }
                                onClick={() => selectLogo(index)}
                                style={{ cursor: 'pointer', border: selectedLogoIndex === index ? '2px solid #007cba' : undefined }}
                            >
                                <MediaUploadCheck>
                                    <MediaUpload
                                        onSelect={(media) => onSelectImage(index, media)}
                                        allowedTypes={['image']}
                                        value={logo.imageId}
                                        render={({ open }) => (
                                            <Button
                                                onClick={(e) => { e.stopPropagation(); open(); }}
                                                className={!logo.imageUrl ? 'button' : 'fau-logo-grid__image-button'}
                                                style={{ cursor: 'pointer' }}
                                            >
                                                {!logo.imageUrl ? (
                                                    __('Choose Logo', 'fau-elemental')
                                                ) : (
                                                    <img
                                                        src={logo.imageUrl}
                                                        alt=""
                                                        className="fau-logo-grid__image"
                                                    />
                                                )}
                                            </Button>
                                        )}
                                    />
                                </MediaUploadCheck>
                            </div>
                        ))}
                    </div>

                    <Button
                        isPrimary
                        onClick={addLogo}
                        style={{ marginTop: '1rem' }}
                    >
                        {__('Add Logo', 'fau-elemental')}
                    </Button>
                </div>
            </>
        );
    },
    save: function() {
        return null; // Use server-side rendering
    }
}); 