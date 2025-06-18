import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';

registerBlockType( 'fau-elemental/fau-meta-headline', {
	edit: function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();

		return (
			<RichText
				{ ...blockProps }
				tagName="header"
				value={ attributes.headline }
				onChange={ ( content ) =>
					setAttributes( { headline: content } )
				}
				placeholder={ __( 'Enter meta headline…', 'fau-elemental' ) }
				allowedFormats={ [] }
			/>
		);
	},
	save: function Save( { attributes } ) {
		const blockProps = useBlockProps.save();
		return (
			<RichText.Content
				{ ...blockProps }
				tagName="header"
				value={ attributes.headline }
			/>
		);
	},
} );
