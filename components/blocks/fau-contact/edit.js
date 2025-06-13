import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
	InnerBlocks,
	RichText,
} from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	TextControl,
	TextareaControl,
	SelectControl,
	Button,
	ToolbarGroup,
	ToolbarButton,
	__experimentalVStack as VStack,
	Card,
	CardHeader,
	CardBody,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { plus, trash, arrowUp, arrowDown } from '@wordpress/icons';

const CONTACT_TYPES = [
	{ label: __( 'Phone', 'fau-elemental' ), value: 'phone' },
	{ label: __( 'Email', 'fau-elemental' ), value: 'email' },
	{ label: __( 'Messenger', 'fau-elemental' ), value: 'messenger' },
	{ label: __( 'Website', 'fau-elemental' ), value: 'website' },
	{ label: __( 'Matrix', 'fau-elemental' ), value: 'matrix' },
];

const SOCIAL_PLATFORMS = [
	{ label: __( 'Instagram', 'fau-elemental' ), value: 'instagram' },
	{ label: __( 'Facebook', 'fau-elemental' ), value: 'facebook' },
	{ label: __( 'Xing', 'fau-elemental' ), value: 'xing' },
	{ label: __( 'LinkedIn', 'fau-elemental' ), value: 'linkedin' },
	{ label: __( 'Twitter/X', 'fau-elemental' ), value: 'twitter' },
	{ label: __( 'Mastodon', 'fau-elemental' ), value: 'mastodon' },
	{ label: __( 'Bluesky', 'fau-elemental' ), value: 'bluesky' },
	{ label: __( 'YouTube', 'fau-elemental' ), value: 'youtube' },
	{ label: __( 'TikTok', 'fau-elemental' ), value: 'tiktok' },
];

const ALLOWED_BLOCKS = [ 'core/image' ];
const TEMPLATE = [
	[ 'core/image' ],
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		showTopLine,
		topLine,
		headline,
		showAddress,
		address,
		showOpeningHours,
		openingHours,
		contactLinks,
		socialMedia,
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

	const updateSocialMedia = ( platform, url ) => {
		const newSocialMedia = { ...socialMedia };
		if ( url.trim() ) {
			newSocialMedia[ platform ] = url;
		} else {
			delete newSocialMedia[ platform ];
		}
		setAttributes( { socialMedia: newSocialMedia } );
	};

	return (
		<div { ...blockProps }>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={ arrowUp }
						label={ __( 'Move up', 'fau-elemental' ) }
						onClick={ () => {
							// Add functionality if needed for reordering
						} }
					/>
					<ToolbarButton
						icon={ arrowDown }
						label={ __( 'Move down', 'fau-elemental' ) }
						onClick={ () => {
							// Add functionality if needed for reordering
						} }
					/>
				</ToolbarGroup>
			</BlockControls>

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
							placeholder={ __( 'e.g. Forschung an der FAU', 'fau-elemental' ) }
						/>
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
							placeholder={ __( 'Street\nCity, Postal Code', 'fau-elemental' ) }
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
							placeholder={ __( 'Mo., Di. und Do.\n10:00-16:00 Uhr', 'fau-elemental' ) }
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
											placeholder={ __( 'Display text', 'fau-elemental' ) }
										/>
										<TextControl
											label={ __( 'Value', 'fau-elemental' ) }
											value={ link.value }
											onChange={ ( value ) =>
												updateContactLink( index, 'value', value )
											}
											placeholder={ __( 'Phone, email, or URL', 'fau-elemental' ) }
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
							'Add URLs for social media platforms you want to display.',
							'fau-elemental'
						) }
					</p>
					<VStack spacing={ 2 }>
						{ SOCIAL_PLATFORMS.map( ( platform ) => (
							<TextControl
								key={ platform.value }
								label={ platform.label }
								value={ socialMedia[ platform.value ] || '' }
								onChange={ ( value ) =>
									updateSocialMedia( platform.value, value )
								}
								placeholder={ __( 'https://...', 'fau-elemental' ) }
							/>
						) ) }
					</VStack>
				</PanelBody>
			</InspectorControls>

			<div className="fau-contact-block">
				{ showTopLine && topLine && (
					<div className="contact-topline">
						{ topLine }
					</div>
				) }

				<div className="contact-layout">
					<div className="contact-content">
						<RichText
							tagName="h2"
							className="contact-headline"
							placeholder={ __( 'Enter headline...', 'fau-elemental' ) }
							value={ headline }
							onChange={ ( value ) =>
								setAttributes( { headline: value } )
							}
						/>

						{ showAddress && address && (
							<div className="contact-section">
								<h3>{ __( 'Adresse', 'fau-elemental' ) }</h3>
								<div className="contact-text">{ address }</div>
							</div>
						) }

						{ showOpeningHours && openingHours && (
							<div className="contact-section">
								<h3>{ __( 'Sprechzeiten', 'fau-elemental' ) }</h3>
								<div className="contact-text">{ openingHours }</div>
							</div>
						) }

						{ contactLinks.length > 0 && (
							<div className="contact-section">
								<h3>{ __( 'Kontakt', 'fau-elemental' ) }</h3>
								{ contactLinks.map( ( link, index ) => (
									<div key={ index } className={ `contact-link contact-link-${ link.type }` }>
										<i className="fas fa-icon" aria-hidden="true"></i>
										<span>{ link.label || link.value }</span>
									</div>
								) ) }
							</div>
						) }

						{ Object.keys( socialMedia ).length > 0 && (
							<div className="contact-section">
								<div className="social-links">
									{ Object.entries( socialMedia ).map( ( [ platform, url ] ) => (
										url && (
											<a key={ platform } href={ url } className={ `social-link ${ platform }` } rel="noopener noreferrer">
												{ /* Social icon will be added via CSS */ }
											</a>
										)
									) ) }
								</div>
							</div>
						) }
					</div>

					<div className="contact-image-section">
						<InnerBlocks
							allowedBlocks={ ALLOWED_BLOCKS }
							template={ TEMPLATE }
						/>
					</div>
				</div>
			</div>
		</div>
	);
} 