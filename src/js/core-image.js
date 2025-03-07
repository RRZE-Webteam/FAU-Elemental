const { unregisterBlockStyle, registerBlockStyle } = wp.blocks;

wp.domReady(() => {
    unregisterBlockStyle('core/image', ['default', 'rounded']);

    registerBlockStyle('core/image', { name: 'large', label: 'Large', isDefault: true });
    registerBlockStyle('core/image', { name: 'medium', label: 'Medium', isDefault: false });
    registerBlockStyle('core/image', { name: 'small', label: 'Small', isDefault: false });
});