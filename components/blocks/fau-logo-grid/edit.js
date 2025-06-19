import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const { logos = [], selectedLogoIndex = null } = attributes;

	useEffect( () => {
		// Ensure logos array is initialized
		if ( ! Array.isArray( logos ) ) {
			setAttributes( { logos: [] } );
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
			logos: [ ...logos, { imageId: 0, imageUrl: '', link: '' } ],
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
			<InspectorControls>
				{ selectedLogoIndex !== null && logos[ selectedLogoIndex ] && (
					<PanelBody
						title={ __(
							'Selected Logo Settings',
							'fau-elemental'
						) }
						initialOpen={ true }
					>
						<TextControl
							label={ __( 'Logo Link', 'fau-elemental' ) }
							value={ logos[ selectedLogoIndex ].link || '' }
							onChange={ updateLogoLink }
						/>
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

				<Button isPrimary onClick={ addLogo }>
					{ __( 'Add Logo', 'fau-elemental' ) }
				</Button>
			</div>
		</>
	);
}
