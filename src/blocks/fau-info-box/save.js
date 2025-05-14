import { RichText, useBlockProps } from '@wordpress/block-editor';

export default function Save( { attributes } ) {
	const { headline, content, image, ctaButtonUrl, ctaButtonText } =
		attributes;

	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			<RichText.Content
				tagName="h3"
				value={ headline }
				className="fau-info-box-headline"
			/>
			<RichText.Content
				tagName="p"
				value={ content }
				className="fau-info-box-content"
			/>
			{ image && (
				<figure className="fau-info-box-image">
					<img src={ image.url } alt={ image.alt || '' } />
				</figure>
			) }
			{ ctaButtonUrl && (
				<div class="wp-block-buttons">
					<div class="wp-block-button is-style-primary">
						<a
							class="wp-block-button__link wp-element-button fau-info-box-cta"
							href={ ctaButtonUrl }
						>
							{ ctaButtonText }
						</a>
					</div>
				</div>
			) }
		</div>
	);
}
