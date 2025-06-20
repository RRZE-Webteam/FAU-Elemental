import { useBlockProps, BlockControls } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { plus } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import FactsInspectorControls from './components/FactsInspectorControls';
import FactsGridContent from './components/FactsGridContent';

/**
 * Main Edit Component
 */
export default function Edit( { attributes, setAttributes } ) {
	const { facts } = attributes;
	const [ selectedFactIndex, setSelectedFactIndex ] = useState( 0 );

	const blockProps = useBlockProps();

	const addFact = () => {
		const newFacts = [
			...facts,
			{
				text: '',
				iconUrl: '',
				iconId: null,
				link: '',
				showLink: false,
			},
		];
		setAttributes( { facts: newFacts } );
		setSelectedFactIndex( newFacts.length - 1 );
	};

	const removeFact = ( index ) => {
		const newFacts = facts.filter( ( _, i ) => i !== index );
		setAttributes( { facts: newFacts } );
		if ( selectedFactIndex >= newFacts.length ) {
			setSelectedFactIndex( Math.max( 0, newFacts.length - 1 ) );
		}
	};

	const updateFact = ( index, field, value ) => {
		const newFacts = [ ...facts ];
		newFacts[ index ] = { ...newFacts[ index ], [ field ]: value };
		setAttributes( { facts: newFacts } );
	};

	return (
		<div { ...blockProps }>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={ plus }
						label={ __( 'Add New Fact', 'fau-elemental' ) }
						onClick={ addFact }
					/>
				</ToolbarGroup>
			</BlockControls>

			<FactsInspectorControls
				facts={ facts }
				selectedFactIndex={ selectedFactIndex }
				addFact={ addFact }
				removeFact={ removeFact }
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
