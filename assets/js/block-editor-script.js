console.log('Hello World - Editor Script');
wp.domReady(() => {
    console.log('DOM Ready');

    // unregister the fill block style for the core/button block
    wp.blocks.unregisterBlockStyle('core/button', 'fill');

    // unregister the outline block style for the core/button block
    wp.blocks.unregisterBlockStyle('core/button', 'outline');

    // register the primary block style for the core/button block
    wp.blocks.registerBlockStyle('core/button', {
        name: 'primary',
        label: 'Primary',
        isDefault: true,
    });

    // register the secondary block style for the core/button block
    wp.blocks.registerBlockStyle('core/button', {
        name: 'secondary',
        label: 'Secondary',
        isDefault: false,
    });

    // register the tertiary block style for the core/button block
    wp.blocks.registerBlockStyle('core/button', {
        name: 'tertiary',
        label: 'Tertiary',
        isDefault: false,
    });
});