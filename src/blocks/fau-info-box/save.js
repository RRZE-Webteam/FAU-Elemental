import { RichText, useBlockProps } from '@wordpress/block-editor';

export default function Save( { attributes } ) {
	const { headline, content } = attributes;

	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			<RichText.Content
				tagName="h3"
				value={ headline }
				className='fau-info-box-headline'
			/>

			<RichText.Content
				tagName="p"
				value={ content }
				className='fau-info-box-content'
			/>
		</div>
	);
}
