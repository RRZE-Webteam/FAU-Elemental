import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { DropdownMenu } from '@wordpress/components';
import {
	RichText,
	useBlockProps,
	BlockControls,
} from '@wordpress/block-editor';
import { v4 as uuidv4 } from 'uuid';

registerBlockType( 'fau-elemental/fau-meta-headline', {
	edit: function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const { headlineLevel = 'h2', headline } = attributes;

		if ( ! attributes.id || attributes.id.length === 0 ) {
			attributes.id = uuidv4();
		}

		return (
			<>
				<BlockControls group="block">
					<DropdownMenu
						icon="heading"
						label={ __( 'Change heading level', 'fau-elemental' ) }
						controls={ [
							{
								title: __( 'Heading 2', 'fau-elemental' ),
								onClick: () =>
									setAttributes( { headlineLevel: 'h2' } ),
								isActive: headlineLevel === 'h2',
							},
							{
								title: __( 'Heading 3', 'fau-elemental' ),
								onClick: () =>
									setAttributes( { headlineLevel: 'h3' } ),
								isActive: headlineLevel === 'h3',
							},
							{
								title: __( 'Heading 4', 'fau-elemental' ),
								onClick: () =>
									setAttributes( { headlineLevel: 'h4' } ),
								isActive: headlineLevel === 'h4',
							},
							{
								title: __( 'Heading 5', 'fau-elemental' ),
								onClick: () =>
									setAttributes( { headlineLevel: 'h5' } ),
								isActive: headlineLevel === 'h5',
							},
							{
								title: __( 'Heading 6', 'fau-elemental' ),
								onClick: () =>
									setAttributes( { headlineLevel: 'h6' } ),
								isActive: headlineLevel === 'h6',
							},
						] }
					/>
				</BlockControls>
				<RichText
					{ ...blockProps }
					tagName={ headlineLevel }
					value={ headline }
					onChange={ ( content ) =>
						setAttributes( { headline: content } )
					}
					placeholder={ __(
						'Enter meta headline…',
						'fau-elemental'
					) }
					allowedFormats={ [] }
				/>
			</>
		);
	},
	save: function Save( { attributes } ) {
		const blockProps = useBlockProps.save();
		const { headlineLevel = 'h2', headline } = attributes;
		return (
			<RichText.Content
				{ ...blockProps }
				tagName={ headlineLevel }
				id={ 'headline-' + attributes.id }
				value={ headline }
			/>
		);
	},
	deprecated: [
		{
			attributes: {
				headline: {
					type: 'string',
				},
				id: {
					type: 'string',
				},
			},
			migrate( attributes ) {
				return {
					...attributes,
					headlineLevel: attributes?.headlineLevel || 'h2',
				};
			},
			save( { attributes } ) {
				const blockProps = useBlockProps.save();
				return (
					<RichText.Content
						{ ...blockProps }
						tagName="div"
						id={ 'headline-' + ( attributes.id || '' ) }
						value={ attributes.headline }
					/>
				);
			},
		},
	],
} );
