import { __ } from '@wordpress/i18n';
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
import { useEffect } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const { logos = [], selectedLogoIndex = null } = attributes;

	useEffect( () => {
		// First check if there are migrated image links to populate
		if ( ! Array.isArray( logos ) || logos.length === 0 ) {
			// Check if there are migrated image links available
			wp.apiFetch( { path: '/wp/v2/settings' } )
				.then( ( settings ) => {
					const migratedLinks =
						settings.fau_elemental_migrated_image_links;
					if (
						migratedLinks &&
						Array.isArray( migratedLinks ) &&
						migratedLinks.length > 0
					) {
						// Convert migrated links to logo format
						const convertedLogos = migratedLinks.map(
							( link ) => ( {
								imageId: link.imageId || 0,
								imageUrl: link.imageUrl || '',
								link: link.link || '',
								category: link.category || '',
								migrated: true, // Mark as migrated
							} )
						);
						setAttributes( { logos: convertedLogos } );
					} else {
						// No migrated links, use default empty logo
						setAttributes( {
							logos: [
								{
									imageId: 0,
									imageUrl: '',
									link: '',
									category: '',
									migrated: false, // Not migrated
								},
							],
						} );
					}
				} )
				.catch( () => {
					// Fallback if API call fails
					setAttributes( {
						logos: [
							{
								imageId: 0,
								imageUrl: '',
								link: '',
								category: '',
								migrated: false, // Not migrated
							},
						],
					} );
				} );
		}
	}, [] );

	const onSelectImage = ( index, media ) => {
		const newLogos = [ ...logos ];
		newLogos[ index ] = {
			...newLogos[ index ],
			imageId: media.id,
			imageUrl: media.url,
		};
		setAttributes( { logos: newLogos } );
	};

	const updateLogoLink = ( link ) => {
		if ( selectedLogoIndex === null || ! logos[ selectedLogoIndex ] ) {
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
				{ imageId: 0, imageUrl: '', link: '', category: '' },
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

	const handleKeyDown = ( event, index ) => {
		if ( event.key === 'Enter' || event.key === ' ' ) {
			event.preventDefault();
			selectLogo( index );
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
				{ selectedLogoIndex !== null && logos[ selectedLogoIndex ] && (
					<PanelBody
						title={ __(
							'Selected Logo Settings',
							'fau-elemental'
						) }
						initialOpen={ true }
					>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) =>
									onSelectImage( selectedLogoIndex, media )
								}
								allowedTypes={ [ 'image' ] }
								value={ logos[ selectedLogoIndex ].imageId }
								render={ ( { open } ) => (
									<>
										<Button
											onClick={ open }
											isSecondary
											style={ { marginBottom: '16px' } }
										>
											{ logos[ selectedLogoIndex ]
												.imageUrl
												? __(
														'Replace Image',
														'fau-elemental'
												  )
												: __(
														'Set Image',
														'fau-elemental'
												  ) }
										</Button>
										{ logos[ selectedLogoIndex ]
											.imageUrl && (
											<div
												style={ {
													marginBottom: '16px',
												} }
											>
												<img
													src={
														logos[
															selectedLogoIndex
														].imageUrl
													}
													alt=""
													style={ {
														maxWidth: '100%',
														height: 'auto',
														border: '1px solid #ccc',
													} }
												/>
											</div>
										) }
									</>
								) }
							/>
						</MediaUploadCheck>

						<TextControl
							label={ __( 'Logo Link', 'fau-elemental' ) }
							value={ logos[ selectedLogoIndex ].link || '' }
							onChange={ updateLogoLink }
						/>

						{ logos[ selectedLogoIndex ].migrated && (
							<div
								style={ {
									marginTop: '16px',
									padding: '12px',
									backgroundColor: '#f6f7f7',
									borderRadius: '4px',
								} }
							>
								{ logos[ selectedLogoIndex ].category && (
									<>
										<strong>
											{ __(
												'Original Category:',
												'fau-elemental'
											) }
										</strong>{ ' ' }
										{ logos[ selectedLogoIndex ].category }
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

						<Button
							isDestructive
							onClick={ () => removeLogo( selectedLogoIndex ) }
						>
							{ __( 'Remove Logo', 'fau-elemental' ) }
						</Button>
					</PanelBody>
				) }
			</InspectorControls>

			<div { ...blockProps }>
				<div className="fau-logo-grid__container">
					{ Array.isArray( logos ) &&
						logos.map( ( logo, index ) => (
							<div
								key={ index }
								className={
									'fau-logo-grid__item' +
									( selectedLogoIndex === index
										? ' fau-logo-grid__item--selected'
										: '' )
								}
								onClick={ () => selectLogo( index ) }
								onKeyDown={ ( event ) =>
									handleKeyDown( event, index )
								}
								role="button"
								tabIndex={ 0 }
								aria-label={ __(
									'Select logo for editing',
									'fau-elemental'
								) }
							>
								<MediaUploadCheck>
									<MediaUpload
										onSelect={ ( media ) =>
											onSelectImage( index, media )
										}
										allowedTypes={ [ 'image' ] }
										value={ logo.imageId }
										render={ ( { open } ) => (
											<Button
												onClick={ ( e ) => {
													e.stopPropagation();
													open();
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
														src={ logo.imageUrl }
														alt=""
														className="fau-logo-grid__image"
													/>
												) }
											</Button>
										) }
									/>
								</MediaUploadCheck>
							</div>
						) ) }
				</div>
			</div>
		</>
	);
}
