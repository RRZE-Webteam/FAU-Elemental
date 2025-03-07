addFilter(
    'editor.BlockEdit',
    'fau-elemental/with-selected-class',
    createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { isSelected, name, attributes } = props;

            useEffect(() => {
                if (isSelected) {
                    const blockTypes = {
                        'core/heading': 'faue-is-heading-block-selected',
                        'core/paragraph': 'faue-is-paragraph-block-selected',
                        'core/button': 'faue-is-button-block-selected',
                    };
                    const isIntroText = name === 'core/paragraph' && attributes.className?.includes('intro-text');

                    Object.keys(blockTypes).forEach(blockName => {
                        document.body.classList.toggle(blockTypes[blockName], name === blockName);
                    });
                    document.body.classList.toggle('faue-is-intro-text-selected', isIntroText);
                }
            }, [isSelected, attributes.className]);

            return <BlockEdit {...props} />;
        };
    }, 'withSelectedClass')
);