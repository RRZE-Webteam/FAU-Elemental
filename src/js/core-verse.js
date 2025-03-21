const { registerBlockVariation } = wp.blocks;

wp.domReady(() => {
    // Register light "Verse" variation (default)
    registerBlockVariation('core/verse', {
        name: 'light-verse',
        title: 'Light Verse',
        description: 'A block for poetry or other formatted text with light styling.',
        attributes: {
            className: 'light-verse',
        },
        icon: 'editor-paragraph',
        isDefault: true,
        scope: ['block', 'inserter', 'transform'],
    });

    // Register dark "Verse" variation
    registerBlockVariation('core/verse', {
        name: 'dark-verse',
        title: 'Dark Verse',
        description: 'A verse block with dark styling.',
        attributes: {
            className: 'dark-verse',
        },
        icon: 'editor-paragraph',
        isDefault: false,
        scope: ['block', 'inserter', 'transform'],
    });
}); 