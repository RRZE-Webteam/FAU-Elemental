import { __ } from '@wordpress/i18n';
import { useRef } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Button,
	ToolbarGroup,
} from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const { logos = [], selectedLogoIndex = 0 } = attributes;
	const mediaUploadRef = useRef();

	const onSelectImage = ( index, media ) => {
		const newLogos = [ ...logos ];
		newLogos[ index ] = {
			...newLogos[ index ],
			imageId: media.id,
			imageUrl: media.url,
			// Use media alt text if available, otherwise use the filename
			alt: media.alt || media.title || media.filename || '',
			// Store image dimensions for CLS optimization
			width: media.width || null,
			height: media.height || null,
		};
		setAttributes( { logos: newLogos } );
	};

	const updateLogoLink = ( link ) => {
		if (
			selectedLogoIndex === null ||
			selectedLogoIndex < 0 ||
			! logos[ selectedLogoIndex ]
		) {
			return;
		}
		const newLogos = [ ...logos ];
		newLogos[ selectedLogoIndex ] = {
			...newLogos[ selectedLogoIndex ],
			link,
		};
		setAttributes( { logos: newLogos } );
	};

	const addLogo = () => {
		setAttributes( {
			logos: [
				...logos,
				{
					imageId: 0,
					imageUrl: '',
					link: '',
					category: '',
					alt: '',
					width: null,
					height: null,
				},
			],
			selectedLogoIndex: logos.length, // select the new logo
		} );
	};

	const removeLogo = ( index ) => {
		const newLogos = [ ...logos ];
		newLogos.splice( index, 1 );
		let newSelected = selectedLogoIndex;
		if ( selectedLogoIndex === index ) {
			newSelected = null;
		} else if ( selectedLogoIndex > index ) {
			newSelected = selectedLogoIndex - 1;
		}
		setAttributes( {
			logos: newLogos,
			selectedLogoIndex: newSelected,
		} );
	};

	const selectLogo = ( index ) => {
		setAttributes( { selectedLogoIndex: index } );
	};

	const openMediaUpload = ( index ) => {
		if ( mediaUploadRef.current ) {
			// Set the target index for the media upload
			mediaUploadRef.current.targetIndex = index;
			mediaUploadRef.current.open();
		}
	};

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<Button
						icon="plus"
						label={ __( 'Add Logo', 'fau-elemental' ) }
						onClick={ addLogo }
					/>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				{ selectedLogoIndex !== null &&
					selectedLogoIndex >= 0 &&
					logos[ selectedLogoIndex ] && (
						<PanelBody
							title={ __(
								'Selected Logo Settings',
								'fau-elemental'
							) }
							initialOpen={ true }
						>
							<Button
								onClick={ () =>
									openMediaUpload( selectedLogoIndex )
								}
								variant="secondary"
								className="fau-logo-grid__image-select-button"
							>
								{ logos[ selectedLogoIndex ].imageUrl
									? __( 'Replace Image', 'fau-elemental' )
									: __( 'Set Image', 'fau-elemental' ) }
							</Button>
							<Button
								variant="secondary"
								isDestructive
								onClick={ () =>
									removeLogo( selectedLogoIndex )
								}
								className="fau-logo-grid__remove-button"
							>
								{ __( 'Remove Logo', 'fau-elemental' ) }
							</Button>
							{ logos[ selectedLogoIndex ].imageUrl && (
								<div className="fau-logo-grid__image-preview">
									<img
										src={
											logos[ selectedLogoIndex ].imageUrl
										}
										alt={
											logos[ selectedLogoIndex ].alt ||
											__(
												'Logo preview',
												'fau-elemental'
											)
										}
										className="fau-logo-grid__preview-image"
										width={
											logos[ selectedLogoIndex ].width ||
											undefined
										}
										height={
											logos[ selectedLogoIndex ].height ||
											undefined
										}
									/>
								</div>
							) }

							<TextControl
								label={ __( 'Logo Link', 'fau-elemental' ) }
								value={ logos[ selectedLogoIndex ].link || '' }
								onChange={ updateLogoLink }
							/>

							{ logos[ selectedLogoIndex ].migrated && (
								<div className="fau-logo-grid__migration-notice">
									{ logos[ selectedLogoIndex ].category && (
										<>
											<strong>
												{ __(
													'Original Category:',
													'fau-elemental'
												) }
											</strong>{ ' ' }
											{
												logos[ selectedLogoIndex ]
													.category
											}
											<br />
										</>
									) }
									<small>
										{ __(
											'This logo was migrated from the previous theme.',
											'fau-elemental'
										) }
									</small>
								</div>
							) }
						</PanelBody>
					) }
			</InspectorControls>

			<div { ...blockProps }>
				<MediaUploadCheck>
					<MediaUpload
						onSelect={ ( media ) => {
							const targetIndex =
								mediaUploadRef.current?.targetIndex ??
								selectedLogoIndex;
							if (
								targetIndex !== null &&
								targetIndex !== undefined
							) {
								onSelectImage( targetIndex, media );
							}
						} }
						allowedTypes={ [ 'image' ] }
						value={
							selectedLogoIndex !== null && selectedLogoIndex >= 0
								? logos[ selectedLogoIndex ]?.imageId
								: 0
						}
						render={ ( { open } ) => {
							// Store the open function in the ref
							mediaUploadRef.current = {
								open,
								targetIndex: null,
							};
							return (
								<div className="fau-logo-grid__container">
									{ Array.isArray( logos ) &&
										logos.map( ( logo, index ) => (
											<div
												key={ index }
												className={
													'fau-logo-grid__item' +
													( selectedLogoIndex ===
													index
														? ' fau-logo-grid__item--selected'
														: '' )
												}
												onClick={ () =>
													selectLogo( index )
												}
												role="button"
												tabIndex={ 0 }
												onKeyDown={ ( e ) => {
													if (
														e.key === 'Enter' ||
														e.key === ' '
													) {
														e.preventDefault();
														selectLogo( index );
													}
												} }
												aria-label={ __(
													'Select logo for editing',
													'fau-elemental'
												) }
											>
												<Button
													onClick={ ( e ) => {
														e.stopPropagation();
														openMediaUpload(
															index
														);
													} }
													className={
														! logo.imageUrl
															? 'button'
															: 'fau-logo-grid__image-button'
													}
												>
													{ ! logo.imageUrl ? (
														__(
															'Choose Logo',
															'fau-elemental'
														)
													) : (
														<img
															src={
																logo.imageUrl
															}
															alt={
																logo.alt ||
																__(
																	'Logo',
																	'fau-elemental'
																)
															}
															className="fau-logo-grid__image"
															width={
																logo.width ||
																undefined
															}
															height={
																logo.height ||
																undefined
															}
														/>
													) }
												</Button>
											</div>
										) ) }
								</div>
							);
						} }
					/>
				</MediaUploadCheck>
			</div>
		</>
	);
}
