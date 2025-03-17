import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	RichText,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	TextControl,
	TextareaControl,
} from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { galleryItems } = attributes;

	const addGalleryItem = () => {
		const newItems = [
			...( galleryItems || [] ),
			{ imageUrl: '', caption: '', copyright: '' },
		];
		setAttributes( { galleryItems: newItems } );
	};

	const updateGalleryItem = ( index, key, value ) => {
		const newItems = [ ...galleryItems ];
		newItems[ index ][ key ] = value;
		setAttributes( { galleryItems: newItems } );
	};

	const removeGalleryItem = ( index ) => {
		const newItems = galleryItems.filter( ( _, i ) => i !== index );
		setAttributes( { galleryItems: newItems } );
	};

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Gallery Items', 'fau-gallery' ) }
					initialOpen={ true }
				>
					{ ( galleryItems || [] ).map( ( item, index ) => (
						<div key={ index } className="gallery-item-settings">
							<h4>
								{ __( 'Gallery Item', 'fau-gallery' ) }{ ' ' }
								{ index + 1 }
							</h4>
							<MediaUpload
								onSelect={ ( media ) =>
									updateGalleryItem(
										index,
										'imageUrl',
										media.url
									)
								}
								allowedTypes={ [ 'image' ] }
								render={ ( { open } ) => (
									<Button onClick={ open } isSecondary>
										{ item.imageUrl ? (
											<img
												src={ item.imageUrl }
												alt={ __(
													'Gallery Item Image',
													'fau-gallery'
												) }
												style={ {
													width: '100%',
													height: 'auto',
												} }
											/>
										) : (
											__( 'Select Image', 'fau-gallery' )
										) }
									</Button>
								) }
							/>
							<TextareaControl
								label={ __( 'Caption', 'fau-gallery' ) }
								value={ item.caption }
								onChange={ ( value ) =>
									updateGalleryItem( index, 'caption', value )
								}
							/>
							<TextControl
								label={ __(
									'Copyright Notice',
									'fau-gallery'
								) }
								value={ item.copyright }
								onChange={ ( value ) =>
									updateGalleryItem(
										index,
										'copyright',
										value
									)
								}
							/>
							<Button
								isDestructive
								onClick={ () => removeGalleryItem( index ) }
								className="remove-gallery-item"
							>
								{ __( 'Remove Item', 'fau-gallery' ) }
							</Button>
							<hr />
						</div>
					) ) }
					<Button variant="primary" onClick={ addGalleryItem }>
						{ __( 'Add Gallery Item', 'fau-gallery' ) }
					</Button>
				</PanelBody>
			</InspectorControls>
			<div className="gallery-items-preview">
				{ ( galleryItems || [] ).map( ( item, index ) => (
					<div
						key={ index }
						className="gallery-item"
						style={ { position: 'relative' } }
					>
						{ item.imageUrl && (
							<div style={ { position: 'relative' } }>
								<img
									src={ item.imageUrl }
									alt={ __( 'Gallery Item', 'fau-gallery' ) }
									style={ { width: '100%', height: 'auto' } }
								/>
								<button
									className="fullscreen-button"
									onClick={ () => {
										const img = new Image();
										img.src = item.imageUrl;
										const fullscreenWindow = window.open(
											'',
											'_blank'
										);
										fullscreenWindow.document.write(
											`<img src="${ img.src }" style="width: 100%; height: auto;" />`
										);
										fullscreenWindow.document.title = __(
											'Fullscreen Image',
											'fau-gallery'
										);
									} }
									style={ {
										position: 'absolute',
										top: '8px',
										right: '8px',
										background: 'rgba(0, 0, 0, 0.7)',
										color: '#fff',
										border: 'none',
										borderRadius: '50%',
										width: '32px',
										height: '32px',
										display: 'flex',
										alignItems: 'center',
										justifyContent: 'center',
										cursor: 'pointer',
									} }
								>
									🔍
								</button>
							</div>
						) }
						<RichText.Content tagName="p" value={ item.caption } />
					</div>
				) ) }
			</div>
		</div>
	);
}
