const { registerBlockVariation, unregisterBlockVariation } = wp.blocks;

wp.domReady(() => {
    // Unregister Quote as a transform option for Paragraph
    unregisterBlockVariation('core/paragraph', 'core/quote');

    // Register "Intro Text" variation for core/paragraph with an icon
    registerBlockVariation('core/paragraph', {
        name: 'intro-text',
        title: 'Intro Text',
        description: 'A paragraph styled as an introduction.',
        attributes: {
            className: 'intro-text',
        },
        icon: 'editor-textcolor', // Dashicon for text
        isDefault: false,
        scope: ['block', 'inserter', 'transform'],
    });

    // Register "Small Text" variation for core/paragraph with an icon
    registerBlockVariation('core/paragraph', {
        name: 'small-text',
        title: 'Small Text',
        description: 'A smaller paragraph for fine print or secondary content.',
        attributes: {
            className: 'small-text',
        },
        icon: 'editor-paragraph', // Dashicon for paragraph text
        isDefault: false,
        scope: ['block', 'inserter', 'transform'],
    });

    // Register "List with Icons" variation for core/list with an icon
    registerBlockVariation('core/list', {
        name: 'list-with-icons',
        title: 'List with Icons',
        description: 'An unordered list with icons.',
        attributes: {
            className: 'list-icons',
        },
        icon: 'editor-ul', // Dashicon for list
        isDefault: false,
        scope: ['block', 'inserter', 'transform'],
    });
});
