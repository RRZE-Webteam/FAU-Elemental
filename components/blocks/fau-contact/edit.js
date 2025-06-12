import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
} from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	TextControl,
	TextareaControl,
	SelectControl,
	Button,
	__experimentalVStack as VStack,
	Card,
	CardHeader,
	CardBody,
	Flex,
	FlexItem,
	Icon,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { plus, trash } from '@wordpress/icons';

const CONTACT_TYPES = [
	{ label: __( 'Phone', 'fau-elemental' ), value: 'phone' },
	{ label: __( 'Email', 'fau-elemental' ), value: 'email' },
	{ label: __( 'Messenger', 'fau-elemental' ), value: 'messenger' },
	{ label: __( 'Website', 'fau-elemental' ), value: 'website' },
	{ label: __( 'Matrix', 'fau-elemental' ), value: 'matrix' },
];

const SOCIAL_TYPES = [
	{ label: __( 'Facebook', 'fau-elemental' ), value: 'facebook' },
	{ label: __( 'Twitter', 'fau-elemental' ), value: 'twitter' },
	{ label: __( 'Instagram', 'fau-elemental' ), value: 'instagram' },
	{ label: __( 'LinkedIn', 'fau-elemental' ), value: 'linkedin' },
	{ label: __( 'XING', 'fau-elemental' ), value: 'xing' },
	{ label: __( 'YouTube', 'fau-elemental' ), value: 'youtube' },
	{ label: __( 'GitHub', 'fau-elemental' ), value: 'github' },
	{ label: __( 'ResearchGate', 'fau-elemental' ), value: 'researchgate' },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		showTopLine,
		topLine,
		imageId,
		imageUrl,
		imageAlt,
		headline,
		showAddress,
		address,
		showOpeningHours,
		openingHours,
		contactLinks,
		socialLinks,
	} = attributes;

	const blockProps = useBlockProps();

	const addContactLink = () => {
		const newContactLinks = [
			...contactLinks,
			{ type: 'phone', label: '', value: '' },
		];
		setAttributes( { contactLinks: newContactLinks } );
	};

	const updateContactLink = ( index, field, value ) => {
		const newContactLinks = [ ...contactLinks ];
		newContactLinks[ index ][ field ] = value;
		setAttributes( { contactLinks: newContactLinks } );
	};

	const removeContactLink = ( index ) => {
		const newContactLinks = contactLinks.filter( ( _, i ) => i !== index );
		setAttributes( { contactLinks: newContactLinks } );
	};

	const addSocialLink = () => {
		if ( socialLinks.length >= 8 ) return;
		const newSocialLinks = [
			...socialLinks,
			{ type: 'facebook', label: '', url: '' },
		];
		setAttributes( { socialLinks: newSocialLinks } );
	};

	const updateSocialLink = ( index, field, value ) => {
		const newSocialLinks = [ ...socialLinks ];
		newSocialLinks[ index ][ field ] = value;
		setAttributes( { socialLinks: newSocialLinks } );
	};

	const removeSocialLink = ( index ) => {
		const newSocialLinks = socialLinks.filter( ( _, i ) => i !== index );
		setAttributes( { socialLinks: newSocialLinks } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Contact Settings', 'fau-elemental' ) }>
					<ToggleControl
						label={ __( 'Show top line', 'fau-elemental' ) }
						checked={ showTopLine }
						onChange={ ( value ) =>
							setAttributes( { showTopLine: value } )
						}
					/>
					{ showTopLine && (
						<TextControl
							label={ __( 'Top line text', 'fau-elemental' ) }
							value={ topLine }
							onChange={ ( value ) =>
								setAttributes( { topLine: value } )
							}
						/>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Image', 'fau-elemental' ) }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => {
								setAttributes( {
									imageId: media.id,
									imageUrl: media.url,
									imageAlt: media.alt,
								} );
							} }
							allowedTypes={ [ 'image' ] }
							value={ imageId }
							render={ ( { open } ) => (
								<Button
									onClick={ open }
									variant="secondary"
									style={ { marginBottom: '12px' } }
								>
									{ imageId
										? __( 'Change Image', 'fau-elemental' )
										: __( 'Select Image', 'fau-elemental' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ imageId && (
						<Button
							onClick={ () => {
								setAttributes( {
									imageId: 0,
									imageUrl: '',
									imageAlt: '',
								} );
							} }
							variant="link"
							isDestructive
						>
							{ __( 'Remove Image', 'fau-elemental' ) }
						</Button>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Optional Fields', 'fau-elemental' ) }>
					<ToggleControl
						label={ __( 'Show address', 'fau-elemental' ) }
						checked={ showAddress }
						onChange={ ( value ) =>
							setAttributes( { showAddress: value } )
						}
					/>
					{ showAddress && (
						<TextareaControl
							label={ __( 'Address', 'fau-elemental' ) }
							value={ address }
							onChange={ ( value ) =>
								setAttributes( { address: value } )
							}
						/>
					) }

					<ToggleControl
						label={ __( 'Show opening hours', 'fau-elemental' ) }
						checked={ showOpeningHours }
						onChange={ ( value ) =>
							setAttributes( { showOpeningHours: value } )
						}
					/>
					{ showOpeningHours && (
						<TextareaControl
							label={ __( 'Opening hours', 'fau-elemental' ) }
							value={ openingHours }
							onChange={ ( value ) =>
								setAttributes( { openingHours: value } )
							}
						/>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Contact Links', 'fau-elemental' ) }>
					<VStack spacing={ 3 }>
						{ contactLinks.map( ( link, index ) => (
							<Card key={ index }>
								<CardHeader>
									<Flex>
										<FlexItem>
											<strong>
												{ __( 'Contact Link', 'fau-elemental' ) }{ ' ' }
												{ index + 1 }
											</strong>
										</FlexItem>
										<FlexItem>
											<Button
												icon={ trash }
												onClick={ () =>
													removeContactLink( index )
												}
												variant="tertiary"
												isDestructive
											/>
										</FlexItem>
									</Flex>
								</CardHeader>
								<CardBody>
									<VStack spacing={ 2 }>
										<SelectControl
											label={ __( 'Type', 'fau-elemental' ) }
											value={ link.type }
											options={ CONTACT_TYPES }
											onChange={ ( value ) =>
												updateContactLink( index, 'type', value )
											}
										/>
										<TextControl
											label={ __( 'Label', 'fau-elemental' ) }
											value={ link.label }
											onChange={ ( value ) =>
												updateContactLink( index, 'label', value )
											}
										/>
										<TextControl
											label={ __( 'Value', 'fau-elemental' ) }
											value={ link.value }
											onChange={ ( value ) =>
												updateContactLink( index, 'value', value )
											}
										/>
									</VStack>
								</CardBody>
							</Card>
						) ) }
						<Button
							icon={ plus }
							onClick={ addContactLink }
							variant="secondary"
						>
							{ __( 'Add Contact Link', 'fau-elemental' ) }
						</Button>
					</VStack>
				</PanelBody>

				<PanelBody title={ __( 'Social Media Links', 'fau-elemental' ) }>
					<p>
						{ __(
							'Maximum 8 social media links allowed.',
							'fau-elemental'
						) }
					</p>
					<VStack spacing={ 3 }>
						{ socialLinks.map( ( link, index ) => (
							<Card key={ index }>
								<CardHeader>
									<Flex>
										<FlexItem>
											<strong>
												{ __( 'Social Link', 'fau-elemental' ) }{ ' ' }
												{ index + 1 }
											</strong>
										</FlexItem>
										<FlexItem>
											<Button
												icon={ trash }
												onClick={ () =>
													removeSocialLink( index )
												}
												variant="tertiary"
												isDestructive
											/>
										</FlexItem>
									</Flex>
								</CardHeader>
								<CardBody>
									<VStack spacing={ 2 }>
										<SelectControl
											label={ __( 'Platform', 'fau-elemental' ) }
											value={ link.type }
											options={ SOCIAL_TYPES }
											onChange={ ( value ) =>
												updateSocialLink( index, 'type', value )
											}
										/>
										<TextControl
											label={ __( 'Label', 'fau-elemental' ) }
											value={ link.label }
											onChange={ ( value ) =>
												updateSocialLink( index, 'label', value )
											}
										/>
										<TextControl
											label={ __( 'URL', 'fau-elemental' ) }
											value={ link.url }
											onChange={ ( value ) =>
												updateSocialLink( index, 'url', value )
											}
										/>
									</VStack>
								</CardBody>
							</Card>
						) ) }
						{ socialLinks.length < 8 && (
							<Button
								icon={ plus }
								onClick={ addSocialLink }
								variant="secondary"
							>
								{ __( 'Add Social Link', 'fau-elemental' ) }
							</Button>
						) }
					</VStack>
				</PanelBody>
			</InspectorControls>

			<div className="fau-contact-block-editor">
				{ showTopLine && topLine && (
					<div className="contact-topline">
						<small>{ topLine }</small>
					</div>
				) }

				<div className="contact-main">
					{ imageUrl && (
						<div className="contact-image">
							<img src={ imageUrl } alt={ imageAlt } />
						</div>
					) }

					<div className="contact-content">
						<RichText
							tagName="h3"
							className="contact-headline"
							placeholder={ __( 'Enter headline...', 'fau-elemental' ) }
							value={ headline }
							onChange={ ( value ) =>
								setAttributes( { headline: value } )
							}
						/>

						{ showAddress && address && (
							<div className="contact-address">
								<strong>{ __( 'Address:', 'fau-elemental' ) }</strong>
								<div>{ address }</div>
							</div>
						) }

						{ showOpeningHours && openingHours && (
							<div className="contact-hours">
								<strong>{ __( 'Opening Hours:', 'fau-elemental' ) }</strong>
								<div>{ openingHours }</div>
							</div>
						) }

						{ contactLinks.length > 0 && (
							<div className="contact-links">
								<strong>{ __( 'Contact:', 'fau-elemental' ) }</strong>
								<ul>
									{ contactLinks.map( ( link, index ) => (
										<li key={ index }>
											{ link.type }: { link.label || link.value }
										</li>
									) ) }
								</ul>
							</div>
						) }

						{ socialLinks.length > 0 && (
							<div className="social-links">
								<strong>{ __( 'Social Media:', 'fau-elemental' ) }</strong>
								<ul>
									{ socialLinks.map( ( link, index ) => (
										<li key={ index }>
											{ link.type }: { link.label || link.url }
										</li>
									) ) }
								</ul>
							</div>
						) }
					</div>
				</div>
			</div>
		</div>
	);
} 