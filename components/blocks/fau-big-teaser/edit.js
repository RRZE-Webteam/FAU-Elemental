import { __, sprintf } from '@wordpress/i18n';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	SelectControl,
	Button,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import {
	InspectorControls,
	BlockControls,
	MediaUpload,
	MediaUploadCheck,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {
	const { headlineLevel, headline, teaserText, linkText, linkUrl, image } =
		attributes;

	const blockProps = useBlockProps();

	const onSelectImage = ( media ) => {
		setAttributes( {
			image: {
				id: media.id,
				url: media.url,
				alt: media.alt,
			},
		} );
	};

	const removeImage = () => {
		setAttributes( { image: null } );
	};

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectImage }
							allowedTypes={ [ 'image' ] }
							value={ image?.id }
							render={ ( { open } ) => (
								<ToolbarButton
									icon="format-image"
									label={
										image
											? __(
													'Replace Image',
													'fau-elemental'
											  )
											: __( 'Add Image', 'fau-elemental' )
									}
									onClick={ open }
								/>
							) }
						/>
					</MediaUploadCheck>
					{ image && (
						<ToolbarButton
							icon="no"
							label={ __( 'Remove Image', 'fau-elemental' ) }
							onClick={ removeImage }
						/>
					) }
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody
					title={ __( 'Content', 'fau-elemental' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Headline Level', 'fau-elemental' ) }
						value={ headlineLevel }
						options={ [
							{
								label: __( 'Heading 2', 'fau-elemental' ),
								value: 'h2',
							},
							{
								label: __( 'Heading 3', 'fau-elemental' ),
								value: 'h3',
							},
							{
								label: __( 'Heading 4', 'fau-elemental' ),
								value: 'h4',
							},
							{
								label: __( 'Heading 5', 'fau-elemental' ),
								value: 'h5',
							},
							{
								label: __( 'Heading 6', 'fau-elemental' ),
								value: 'h6',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { headlineLevel: value } )
						}
					/>

					<TextControl
						label={ __(
							'Headline (max 100 characters)',
							'fau-elemental'
						) }
						value={ headline }
						onChange={ ( value ) =>
							setAttributes( {
								headline: value.substring( 0, 100 ),
							} )
						}
						placeholder={ __( 'Enter headline…', 'fau-elemental' ) }
						help={ sprintf(
							/* translators: %d: current character count */
							__( '%d/100 characters', 'fau-elemental' ),
							headline ? headline.length : 0
						) }
					/>

					<TextareaControl
						label={ __(
							'Teaser Text (max 200 characters)',
							'fau-elemental'
						) }
						value={ teaserText }
						onChange={ ( value ) =>
							setAttributes( {
								teaserText: value.substring( 0, 200 ),
							} )
						}
						placeholder={ __(
							'Enter teaser text…',
							'fau-elemental'
						) }
						help={ sprintf(
							/* translators: %d: current character count */
							__( '%d/200 characters', 'fau-elemental' ),
							teaserText ? teaserText.length : 0
						) }
						rows={ 3 }
					/>

					<TextControl
						label={ __(
							'Link Text (max 40 characters)',
							'fau-elemental'
						) }
						value={ linkText }
						onChange={ ( value ) =>
							setAttributes( {
								linkText: value.substring( 0, 40 ),
							} )
						}
						placeholder={ __(
							'Enter link text…',
							'fau-elemental'
						) }
						help={ sprintf(
							/* translators: %d: current character count */
							__( '%d/40 characters', 'fau-elemental' ),
							linkText ? linkText.length : 0
						) }
					/>

					<TextControl
						label={ __( 'Link URL', 'fau-elemental' ) }
						value={ linkUrl }
						onChange={ ( value ) =>
							setAttributes( { linkUrl: value } )
						}
						placeholder={ __( 'Enter URL…', 'fau-elemental' ) }
						type="url"
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Image Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectImage }
							allowedTypes={ [ 'image' ] }
							value={ image?.id }
							render={ ( { open } ) => (
								<Button
									onClick={ open }
									variant="secondary"
									className="fau-big-teaser__image-select-button"
								>
									{ image
										? __( 'Replace Image', 'fau-elemental' )
										: __(
												'Select Image',
												'fau-elemental'
										  ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>

					{ image && (
						<Button
							onClick={ removeImage }
							variant="tertiary"
							isDestructive
						>
							{ __( 'Remove Image', 'fau-elemental' ) }
						</Button>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ /* Frontend-style preview */ }
				<div className="fau-big-teaser-editor-preview">
					<div className="fau-big-teaser__content">
						<RichText
							tagName={ headlineLevel }
							className="fau-big-teaser__headline"
							value={ headline }
							onChange={ ( value ) => {
								// Limit to 100 characters
								const truncated =
									value.length > 100
										? value.substring( 0, 100 )
										: value;
								setAttributes( { headline: truncated } );
							} }
							placeholder={ __(
								'Add your headline here…',
								'fau-elemental'
							) }
							allowedFormats={ [] }
							multiline={ false }
						/>

						<RichText
							tagName="p"
							className="fau-big-teaser__teaser-text"
							value={ teaserText }
							onChange={ ( value ) => {
								// Limit to 200 characters
								const truncated =
									value.length > 200
										? value.substring( 0, 200 )
										: value;
								setAttributes( { teaserText: truncated } );
							} }
							placeholder={ __(
								'Add your teaser text here…',
								'fau-elemental'
							) }
							allowedFormats={ [] }
							multiline={ false }
						/>

						{ linkText && linkUrl && (
							<div className="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
								<div className="wp-block-button is-style-tertiary">
									<a
										href={ linkUrl }
										className="wp-block-button__link wp-element-button"
										onClick={ ( e ) => e.preventDefault() }
									>
										{ linkText }
									</a>
								</div>
							</div>
						) }
					</div>

					{ image && (
						<div className="fau-big-teaser__image">
							<img
								src={ image.url }
								alt={ image.alt || headline }
							/>
						</div>
					) }
				</div>
			</div>
		</>
	);
}
