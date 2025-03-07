const { unregisterBlockStyle, registerBlockStyle } = wp.blocks;

wp.domReady(() => {
    unregisterBlockStyle('core/button', ['fill', 'outline']);

    registerBlockStyle('core/button', { name: 'primary', label: 'Primary', isDefault: true });
    registerBlockStyle('core/button', { name: 'secondary', label: 'Secondary', isDefault: false });
    registerBlockStyle('core/button', { name: 'tertiary', label: 'Tertiary', isDefault: false });
});
