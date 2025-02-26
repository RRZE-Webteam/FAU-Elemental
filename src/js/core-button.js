const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { useEffect } = wp.element;
const { BlockControls } = wp.blockEditor;

wp.domReady(() => {
    wp.blocks.unregisterBlockStyle('core/button', ['fill', 'outline']);
    wp.blocks.unregisterBlockVariation('core/button', 'width');

    wp.blocks.registerBlockStyle('core/button', { name: 'primary', label: 'Primary', isDefault: true });
    wp.blocks.registerBlockStyle('core/button', { name: 'secondary', label: 'Secondary', isDefault: false });
    wp.blocks.registerBlockStyle('core/button', { name: 'tertiary', label: 'Tertiary', isDefault: false });
});

addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-button-selected-class',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { isSelected, name } = props;

            useEffect(() => {
                if (isSelected) {
                    const isButtonBlock = name === 'core/button';
                    document.body.classList.toggle('faue-is-button-block-selected', isButtonBlock);
                }
            }, [isSelected]);

            return <BlockEdit {...props} />;
        };
    }, 'withButtonSelectedClass')
);
