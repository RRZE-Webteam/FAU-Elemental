import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { getSaveElement } from '@wordpress/blocks';
import { useEffect, cloneElement } from 'react';
import { __ } from '@wordpress/i18n';

// Shared utility functions
const formatFileSize = ( bytes ) => {
	if ( ! bytes ) return '';
	const sizes = [ 'Bytes', 'KB', 'MB', 'GB', 'TB' ];
	if ( bytes === 0 ) return '0 Byte';
	const i = parseInt( Math.floor( Math.log( bytes ) / Math.log( 1024 ) ) );
	return Math.round( bytes / Math.pow( 1024, i ), 2 ) + ' ' + sizes[ i ];
};

const getFileType = ( fileDetails ) => {
	if ( ! fileDetails?.mime_type ) return '';
	const mimeType = fileDetails.mime_type;
	const mimeParts = mimeType.split( '/' );
	if ( mimeParts.length !== 2 ) return mimeType.toUpperCase();

	const mimeMap = {
		'application/pdf': 'PDF',
		'image/jpeg': 'JPEG',
		'image/png': 'PNG',
		'application/msword': 'DOC',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
			'DOCX',
		'application/vnd.ms-excel': 'XLS',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
			'XLSX',
	};
	return mimeMap[ mimeType ] || mimeParts[ 1 ].toUpperCase();
};
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-file-block-settings',
	( settings, name ) => {
		if ( name !== 'core/file' ) {
			return settings;
		}

		// Remove PDF preview support entirely
		if ( settings.supports ) {
			settings.supports = {
				...settings.supports,
				align: false,
				displayPreview: false,
			};
		}

		// Store the original edit component
		const OriginalEdit = settings.edit;

		// Wrap the edit component to ensure PDF settings stay disabled
		settings.edit = ( props ) => {
			const { attributes, setAttributes } = props;

			// Remove displayPreview if it somehow gets added
			useEffect( () => {
				if ( attributes.displayPreview !== undefined ) {
					setAttributes( { displayPreview: undefined } );
				}
			}, [ attributes.displayPreview ] );

			return <OriginalEdit { ...props } />;
		};

		return settings;
	}
);

//Remove text placeholder
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-file-block-settings',
	( settings, name ) => {
		if ( name !== 'core/file' ) {
			return settings;
		}

		const OriginalEdit = settings.edit;

		settings.edit = ( props ) => {
			const { setAttributes, attributes } = props;

			useEffect( () => {
				setAttributes( {
					downloadButtonText: ' ', // space character
					text: ' ', // space character
				} );
			}, [ attributes.downloadButtonText, attributes.text ] );

			return <OriginalEdit { ...props } />;
		};

		return settings;
	}
);

// Add block attributes
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-file-block-settings',
	( settings, name ) => {
		if ( name !== 'core/file' ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				coverImage: {
					type: 'object',
					default: null,
				},
				fileDetails: {
					type: 'object',
					default: null,
				},
			},
			save: ( props ) => {
				const { attributes } = props;
				const blockProps = useBlockProps.save();

				// Get the original content with empty text
				const originalContent = getSaveElement( settings, {
					...attributes,
					downloadButtonText: '',
					text: '',
				} );

				// Add file info elements for frontend display
				const fileInfoElements = attributes.fileDetails ? (
					<div className="file-info-wrapper">
						<dl className="file-info-list">
							<div className="file-info-item">
								<dt className="file-info-term">File Name</dt>
								<dd className="file-info-definition">
									{ attributes.fileDetails.filename }
								</dd>
							</div>
							<div className="file-info-item">
								<dt className="file-info-term">File Size</dt>
								<dd className="file-info-definition">
									{ formatFileSize(
										attributes.fileDetails.filesize
									) }
								</dd>
							</div>
							<div className="file-info-item">
								<dt className="file-info-term">File Type</dt>
								<dd className="file-info-definition">
									{ getFileType( attributes.fileDetails ) }
								</dd>
							</div>
						</dl>
					</div>
				) : null;

				// Add accessibility attributes to the download button and file name link
				let contentWithAccessibility = originalContent;
				if (
					originalContent &&
					originalContent.props &&
					originalContent.props.children
				) {
					const downloadButton = originalContent.props.children.find(
						( child ) =>
							child &&
							child.props &&
							child.props.className?.includes(
								'wp-block-file__button'
							)
					);
					const fileNameLink = originalContent.props.children.find(
						( child ) =>
							child &&
							child.props &&
							child.props.id?.startsWith(
								'wp-block-file--media-'
							)
					);
					if ( downloadButton || fileNameLink ) {
						contentWithAccessibility = cloneElement(
							originalContent,
							{
								children: originalContent.props.children.map(
									( child ) => {
										if ( child && child.props ) {
											if (
												child.props.className?.includes(
													'wp-block-file__button'
												)
											) {
												return cloneElement( child, {
													'aria-label': `${
														attributes.fileDetails
															?.filename || ''
													} ${ __(
														'Download',
														'fau-elemental'
													) }`,
													role: 'button',
													'aria-describedby':
														child.props[
															'aria-describedby'
														],
												} );
											}
											if (
												child.props.id?.startsWith(
													'wp-block-file--media-'
												)
											) {
												const fileName =
													typeof child.props
														.children === 'string'
														? child.props.children
														: attributes.fileDetails
																?.filename ||
														  '';
												return cloneElement( child, {
													'aria-label': `${ fileName } ${ __(
														'Download',
														'fau-elemental'
													) }`,
													'aria-describedby':
														child.props.id,
												} );
											}
										}
										return child;
									}
								),
							}
						);
					}
				}

				return (
					<article { ...blockProps }>
						<main className="wp-block-file__content-wrapper">
							<figure
								className="file-cover-image"
								key="cover-image"
								aria-label={ __(
									'Cover image for file',
									'fau-elemental'
								) }
							>
								{ attributes.coverImage && (
									<img
										src={ attributes.coverImage.url }
										alt={ attributes.coverImage.alt || '' }
									/>
								) }
							</figure>
							<section className="wp-block-file">
								<div className="file-content">
									{ contentWithAccessibility }
									{ fileInfoElements }
								</div>
							</section>
						</main>
					</article>
				);
			},
		};
	}
);

// Add inspector controls
const withInspectorControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, setAttributes, name } = props;

		if ( name !== 'core/file' ) {
			return <BlockEdit { ...props } />;
		}

		// Get file details using useSelect
		const fileDetails = useSelect(
			( select ) => {
				const { getMedia } = select( 'core' );
				return attributes.id ? getMedia( attributes.id ) : null;
			},
			[ attributes.id ]
		);

		// Save file details to attributes when they change
		useEffect( () => {
			if ( fileDetails ) {
				setAttributes( {
					fileDetails: {
						filename:
							fileDetails.title?.rendered ||
							fileDetails.filename ||
							fileDetails.source_url?.split( '/' ).pop(),
						filesize: fileDetails.media_details?.filesize,
						mime_type: fileDetails.mime_type,
					},
				} );
			}
		}, [ fileDetails ] );

		// Image handling functions
		const onSelectImage = ( media ) => {
			setAttributes( {
				coverImage: {
					id: media.id,
					url: media.url,
					alt: media.alt || '',
				},
			} );
		};

		const blockProps = useBlockProps();

		return (
			<div { ...blockProps }>
				<InspectorControls>
					<PanelBody title="Block Settings">
						<div className="editor-file-cover-image">
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ onSelectImage }
									allowedTypes={ [ 'image' ] }
									value={ attributes.coverImage?.id }
									render={ ( { open } ) => (
										<div key="media-upload-container">
											{ ! attributes.coverImage && (
												<Button
													key="add-cover-button"
													onClick={ open }
													variant="secondary"
												>
													Add Cover Image
												</Button>
											) }
											{ attributes.coverImage && (
												<div key="cover-image-preview">
													<img
														key="cover-image"
														src={
															attributes
																.coverImage.url
														}
														alt={
															attributes
																.coverImage
																.alt || ''
														}
														style={ {
															maxWidth: '100%',
															marginBottom: '8px',
														} }
													/>
													<div key="cover-image-buttons">
														<Button
															key="replace-button"
															onClick={ open }
															variant="secondary"
															style={ {
																marginRight:
																	'8px',
															} }
														>
															Replace
														</Button>
														<Button
															key="remove-button"
															onClick={ () =>
																setAttributes( {
																	coverImage:
																		null,
																} )
															}
															variant="secondary"
															isDestructive
														>
															Remove
														</Button>
													</div>
												</div>
											) }
										</div>
									) }
								/>
							</MediaUploadCheck>
						</div>
					</PanelBody>
				</InspectorControls>
				<main className="wp-block-file__content-wrapper">
					<figure
						className="file-cover-image"
						key="cover-image"
						aria-label={ __(
							'Cover image for file',
							'fau-elemental'
						) }
					>
						{ attributes.coverImage && (
							<img
								src={ attributes.coverImage.url }
								alt={ attributes.coverImage.alt || '' }
							/>
						) }
					</figure>
					<section className="wp-block-file">
						<BlockEdit { ...props } />
						{ fileDetails && (
							<div className="file-info-wrapper">
								<dl className="file-info-list">
									<div className="file-info-item">
										<dt className="file-info-term">
											File Name
										</dt>
										<dd className="file-info-definition">
											{ fileDetails.title?.rendered ||
												fileDetails.filename ||
												fileDetails.source_url
													?.split( '/' )
													.pop() }
										</dd>
									</div>
									<div className="file-info-item">
										<dt className="file-info-term">
											File Size
										</dt>
										<dd className="file-info-definition">
											{ formatFileSize(
												fileDetails.media_details
													?.filesize
											) }
										</dd>
									</div>
									<div className="file-info-item">
										<dt className="file-info-term">
											File Type
										</dt>
										<dd className="file-info-definition">
											{ getFileType( fileDetails ) }
										</dd>
									</div>
								</dl>
							</div>
						) }
					</section>
				</main>
			</div>
		);
	};
}, 'withInspectorControls' );

addFilter(
	'editor.BlockEdit',
	'fau-elemental/with-inspector-controls',
	withInspectorControls
);
