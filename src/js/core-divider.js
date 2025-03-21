wp.domReady(() => {
    // Remove default separator styles
    wp.blocks.unregisterBlockStyle('core/separator', 'default');
    wp.blocks.unregisterBlockStyle('core/separator', 'wide');
    wp.blocks.unregisterBlockStyle('core/separator', 'dots');

    // Register custom styles
    wp.blocks.registerBlockStyle('core/separator', {
        name: 'content',
        label: 'Content',
        isDefault: true,
    });

    wp.blocks.registerBlockStyle('core/separator', {
        name: 'full-grid',
        label: 'Full-Grid',
    });

    // Disable all unwanted features
    wp.hooks.addFilter(
        'blocks.registerBlockType',
        'custom/separator-settings',
        (settings, name) => {
            if (name !== 'core/separator') return settings;

            return {
                ...settings,
                supports: {
                    ...settings.supports,
                    color: false,
                    spacing: false,
                    padding: false,
                    margin: false,
                    dimensions: false,
                    __experimentalBorder: false,
                    customClassName: true,
                    align: true,
                },
            };
        }
    );
}); 