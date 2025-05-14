import { __ } from '@wordpress/i18n';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { headline, content } = attributes;

	const blockProps = useBlockProps();

	return (
		<div
			{ ...blockProps }
			role="region"
			aria-label={ __( 'Info Box', 'fau-elemental' ) }
		>
			<RichText
				tagName="h3"
				value={ headline }
				allowedFormats={ [] }
				onChange={ (headline) => setAttributes( { headline })}
				placeholder={ __( 'Optional Headline', 'fau-elemental' ) }
			/>

			<RichText
				tagName="p"
				value={ content }
				allowedFormats={ ['core/bold','core/code','core/italic','core/keyboard','core/link','core/strikethrough','core/subscript','core/superscript'] }
				onChange={ (content) => setAttributes( { content })}
				placeholder={ __( 'Info Box Text...', 'fau-elemental' ) }
			/>

			TODO Optional Image
			TODO Optional CTA
		</div>
	);
}
