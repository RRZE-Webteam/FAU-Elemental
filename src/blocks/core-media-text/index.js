import { addFilter } from '@wordpress/hooks';

// Add custom attribute
addFilter(
    'blocks.registerBlockType',
    'my-plugin/media-text-vertical-attribute',
    function(settings, name) {
        if (name !== 'core/media-text') {
            return settings;
        }

        settings.supports = {
            ...settings.supports,
            align: false, // Remove alignment support
        };

        settings.allowedBlocks = ['core/paragraph', 'core/heading', 'core/list'];

        return settings;
    }
);

