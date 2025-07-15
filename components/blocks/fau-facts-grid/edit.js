import { useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import FactsInspectorControls from './components/FactsInspectorControls';
import FactsGridContent from './components/FactsGridContent';

/**
 * Main Edit Component
 */
export default function Edit( { attributes, setAttributes } ) {
	const { facts } = attributes;
	const [ selectedFactIndex, setSelectedFactIndex ] = useState( 0 );

	const blockProps = useBlockProps();

	// Ensure exactly 4 facts on component mount and when facts change
	useEffect( () => {
		if ( facts.length !== 4 ) {
			const newFacts = [ ...facts ];

			// Add empty facts if we have less than 4
			while ( newFacts.length < 4 ) {
				newFacts.push( {
					text: '',
					iconUrl: '',
					iconId: null,
					link: '',
				} );
			}

			// Remove excess facts if we have more than 4
			if ( newFacts.length > 4 ) {
				newFacts.splice( 4 );
			}

			setAttributes( { facts: newFacts } );

			// Adjust selected index if needed
			if ( selectedFactIndex >= 4 ) {
				setSelectedFactIndex( 3 );
			}
		}
	}, [ facts.length, selectedFactIndex, setAttributes ] );

	const updateFact = ( index, field, value ) => {
		const newFacts = [ ...facts ];
		newFacts[ index ] = { ...newFacts[ index ], [ field ]: value };
		setAttributes( { facts: newFacts } );
	};

	return (
		<div { ...blockProps }>
			<FactsInspectorControls
				facts={ facts }
				selectedFactIndex={ selectedFactIndex }
				updateFact={ updateFact }
				setAttributes={ setAttributes }
			/>

			<FactsGridContent
				facts={ facts }
				selectedFactIndex={ selectedFactIndex }
				setSelectedFactIndex={ setSelectedFactIndex }
				updateFact={ updateFact }
			/>
		</div>
	);
}
