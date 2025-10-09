import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { v4 as uuidv4 } from 'uuid';

registerBlockType( 'fau-elemental/fau-meta-headline', {
	edit: function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();

		if ( ! attributes.id || attributes.id.length === 0 ) {
			attributes.id = uuidv4();
		}

		return (
			<RichText
				{ ...blockProps }
				tagName="div"
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
				tagName="div"
				id={ 'headline-' + attributes.id }
				value={ attributes.headline }
			/>
		);
	},
} );
