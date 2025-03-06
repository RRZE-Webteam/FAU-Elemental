// Core image block customizations
wp.domReady(() => {
    wp.blocks.unregisterBlockStyle('core/image', ['default', 'rounded']);

    wp.blocks.registerBlockStyle('core/image', { name: 'large', label: 'Large', isDefault: true });
    wp.blocks.registerBlockStyle('core/image', { name: 'medium', label: 'Medium', isDefault: false });
    wp.blocks.registerBlockStyle('core/image', { name: 'small', label: 'Small', isDefault: false });
});
