import { unregisterBlockStyle, registerBlockStyle } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';
import { addFilter } from '@wordpress/hooks';

// Register styles for tag cloud block
domReady(() => {
    // Unregister default styles
    unregisterBlockStyle('core/tag-cloud', ['default', 'outline']);
});

// Modify block attributes and supports
addFilter(
    'blocks.registerBlockType',
    'fau-elemental/tag-cloud-attributes',
    (settings, name) => {
        if (name !== 'core/tag-cloud') {
            return settings;
        }

        return {
            ...settings,
            supports: {
                ...settings.supports,
                align: false, // Remove alignment support
            },
        };
    }
);
