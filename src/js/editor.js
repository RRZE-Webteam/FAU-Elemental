const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { useEffect } = wp.element;

/**
 * Add selected block classes to body
 * This filter adds specific classes to the body tag when certain blocks are selected
 * to allow for contextual styling in the editor.
 */
addFilter(
	'editor.BlockEdit',
	'fau-elemental/with-block-selected-classes',
	createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const { isSelected, name, attributes } = props;

			useEffect( () => {
				if ( isSelected ) {
					// Define block types and their corresponding classes
					const blockClasses = {
						'core/button': 'faue-is-button-block-selected',
						'core/heading': 'faue-is-heading-block-selected',
						'core/paragraph': 'faue-is-paragraph-block-selected',
						'core/image': 'faue-is-image-block-selected',
						'core/table': 'faue-is-table-block-selected',
					};

					// Add/remove the basic block type class
					Object.entries( blockClasses ).forEach(
						( [ blockName, className ] ) => {
							document.body.classList.toggle(
								className,
								name === blockName
							);
						}
					);

					// Handle special variations (like intro-text)
					const isParagraph = name === 'core/paragraph';
					const isIntroText =
						isParagraph &&
						attributes.className?.includes( 'intro-text' );
					document.body.classList.toggle(
						'faue-is-intro-text-selected',
						isIntroText
					);
				}
			}, [ isSelected, name, attributes?.className ] );

			return <BlockEdit { ...props } />;
		};
	}, 'withBlockSelectedClasses' )
);
