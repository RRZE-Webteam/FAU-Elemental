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

		const headlineOptions = [
			{
				value: 'h2',
				label: __( 'Heading 2', 'fau-elemental' ),
				icon: 'H2',
			},
			{
				value: 'h3',
				label: __( 'Heading 3', 'fau-elemental' ),
				icon: 'H3',
			},
			{
				value: 'h4',
				label: __( 'Heading 4', 'fau-elemental' ),
				icon: 'H4',
			},
			{
				value: 'h5',
				label: __( 'Heading 5', 'fau-elemental' ),
				icon: 'H5',
			},
			{
				value: 'h6',
				label: __( 'Heading 6', 'fau-elemental' ),
				icon: 'H6',
			},
			{
				value: 'header',
				label: __( 'Header element', 'fau-elemental' ),
				icon: __( 'Header', 'fau-elemental' ),
			},
		];

		const currentHeadlineOption =
			headlineOptions.find(
				( option ) => option.value === headlineLevel
			) || headlineOptions[ 0 ];

		if ( ! attributes.id || attributes.id.length === 0 ) {
			attributes.id = uuidv4();
		}

		return (
			<>
				<BlockControls group="block">
					<DropdownMenu
						icon={
							<span className="fau-meta-headline-level-icon">
								{ currentHeadlineOption.icon }
							</span>
						}
						label={ __( 'Change heading level', 'fau-elemental' ) }
						controls={ headlineOptions.map(
							( { value, label, icon } ) => ( {
								title: label,
								icon: (
									<span className="fau-meta-headline-level-icon">
										{ icon }
									</span>
								),
								onClick: () =>
									setAttributes( { headlineLevel: value } ),
								isActive: headlineLevel === value,
							} )
						) }
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
