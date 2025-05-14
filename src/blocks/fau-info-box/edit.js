import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
	InnerBlocks,
} from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { headline, content, image } = attributes;

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
			<InnerBlocks allowedBlocks={ [ 'core/buttons' ] } />
		</div>
	);
}

const InfoBoxInspector = ( { image, onSelectImage } ) => {
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
		</InspectorControls>
	);
};
