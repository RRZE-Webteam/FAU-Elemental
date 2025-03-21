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

    
}); 