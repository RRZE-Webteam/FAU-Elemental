import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
	URLInput,
	URLPopover,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { headline, content, image, ctaButtonUrl, ctaButtonText } =
		attributes;

	const blockProps = useBlockProps();

	return (
		<div
			{ ...blockProps }
			role="region"
			aria-label={ __( 'Info Box', 'fau-elemental' ) }
		>
			<InfoBoxInspector
				image={ image }
				onSelectImage={ ( image ) => setAttributes( { image } ) }
				ctaUrl={ ctaButtonUrl }
				onCtaUrlChange={ ( ctaButtonUrl ) =>
					setAttributes( { ctaButtonUrl } )
				}
			/>
			<RichText
				tagName="h3"
				value={ headline }
				allowedFormats={ [] }
				onChange={ ( headline ) => setAttributes( { headline } ) }
				placeholder={ __( 'Optional Headline', 'fau-elemental' ) }
				className="fau-info-box-headline"
			/>
			<RichText
				tagName="p"
				value={ content }
				allowedFormats={ [
					'core/bold',
					'core/code',
					'core/italic',
					'core/keyboard',
					'core/link',
					'core/strikethrough',
					'core/subscript',
					'core/superscript',
				] }
				onChange={ ( content ) => setAttributes( { content } ) }
				placeholder={ __( 'Info Box Text...', 'fau-elemental' ) }
				className="fau-info-box-content"
			/>
			{ image && (
				<figure className="fau-info-box-image">
					<img src={ image.url } alt={ image.alt || '' } />
				</figure>
			) }
			{ /* TODO: This Button is a problem: without using nested blocks, we can't safely use core/button because the styles may be missing if no other button is used on a page */ }
			{ ctaButtonUrl && (
				<div class="wp-block-buttons">
					<div class="wp-block-button is-style-primary">
						<RichText
							tagName="a"
							value={ ctaButtonText }
							allowedFormats={ [] }
							onChange={ ( ctaButtonText ) =>
								setAttributes( { ctaButtonText } )
							}
							placeholder={ __( 'CTA Text...', 'fau-elemental' ) }
							className="wp-block-button__link wp-element-button fau-info-box-cta"
						/>
					</div>
				</div>
			) }
		</div>
	);
}

const InfoBoxInspector = ( {
	image,
	onSelectImage,
	ctaUrl,
	onCtaUrlChange,
} ) => {
	return (
		<InspectorControls>
			<MediaUploadCheck>
				<PanelBody
					title={ __( 'Info Box Image', 'fau-elemental' ) }
					initialOpen={ true }
				>
					<div className="editor-post-featured-image">
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							onSelect={ onSelectImage }
							render={ ( { open } ) => (
								<Button
									className="editor-post-featured-image__toggle"
									onClick={ open }
								>
									{ image
										? __( 'Replace Image', 'fau-elemental' )
										: __(
												'Choose Image',
												'fau-elemental'
										  ) }
								</Button>
							) }
						/>
						<br />
						<Button
							className="editor-post-featured-image__toggle"
							isDestructive
							disabled={ ! image }
							onClick={ () => onSelectImage( null ) }
						>
							{ __( 'Remove Image', 'fau-elemental' ) }
						</Button>
					</div>
				</PanelBody>
			</MediaUploadCheck>
			<PanelBody
				title={ __( 'CTA Button', 'fau-elemental' ) }
				initialOpen={ true }
			>
				<TextControl
					label={ __( 'Button Link', 'fau-elemental' ) }
					value={ ctaUrl }
					onChange={ ( url ) => onCtaUrlChange( url ) }
				/>
			</PanelBody>
		</InspectorControls>
	);
};
