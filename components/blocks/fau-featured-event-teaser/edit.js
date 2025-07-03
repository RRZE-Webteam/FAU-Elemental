import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { processEventDate } from './utils/date-helpers';

export default function Edit( { attributes, setAttributes } ) {
	const {
		eventTitle,
		eventDescription,
		eventDate,
		buttonText,
		buttonUrl,
		showImage,
		imageUrl,
		imageAlt,
	} = attributes;

	// Process the date using shared utility
	const { day, monthYear, datetimeAttr } = processEventDate( eventDate );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Display Settings', 'fau-elemental' ) }>
					<ToggleControl
						label={ __( 'Show Image', 'fau-elemental' ) }
						checked={ showImage }
						onChange={ ( value ) =>
							setAttributes( { showImage: value } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Event Details', 'fau-elemental' ) }>
					<TextControl
						label={ __( 'Event Date', 'fau-elemental' ) }
						value={ eventDate }
						onChange={ ( value ) =>
							setAttributes( { eventDate: value } )
						}
						help={ __(
							'Enter date in format: DD MM YYYY or DD MMM YYYY (e.g. 01 12 2025 or 01 Dec 2025)',
							'fau-elemental'
						) }
					/>
					<TextControl
						label={ __( 'Button Text', 'fau-elemental' ) }
						value={ buttonText }
						onChange={ ( value ) =>
							setAttributes( { buttonText: value } )
						}
					/>
					<TextControl
						label={ __( 'Button URL', 'fau-elemental' ) }
						value={ buttonUrl }
						onChange={ ( value ) =>
							setAttributes( { buttonUrl: value } )
						}
					/>
				</PanelBody>
				{ showImage && (
					<PanelBody
						title={ __( 'Image Settings', 'fau-elemental' ) }
					>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) =>
									setAttributes( {
										imageUrl: media.url,
										imageAlt: media.alt,
									} )
								}
								allowedTypes={ [ 'image' ] }
								value={ imageUrl }
								render={ ( { open } ) => (
									<button onClick={ open }>
										{ imageUrl
											? __(
													'Replace Image',
													'fau-elemental'
											  )
											: __(
													'Select Image',
													'fau-elemental'
											  ) }
									</button>
								) }
							/>
						</MediaUploadCheck>
					</PanelBody>
				) }
			</InspectorControls>

			<div
				{ ...useBlockProps( {
					className: 'wp-block-fau-elemental-featured-event-teaser',
				} ) }
			>
				<div className="featured-event-content">
					<div className="content-left">
						<RichText
							tagName="h2"
							className="event-title"
							value={ eventTitle }
							onChange={ ( value ) =>
								setAttributes( { eventTitle: value } )
							}
							placeholder={ __(
								'Enter event title…',
								'fau-elemental'
							) }
						/>
						<RichText
							tagName="p"
							className="event-description"
							value={ eventDescription }
							onChange={ ( value ) =>
								setAttributes( { eventDescription: value } )
							}
							placeholder={ __(
								'Enter event description…',
								'fau-elemental'
							) }
						/>
						<div className="wp-block-button">
							<RichText
								tagName="a"
								className="wp-block-button__link"
								value={ buttonText }
								onChange={ ( value ) =>
									setAttributes( { buttonText: value } )
								}
								placeholder={ __(
									'Button text…',
									'fau-elemental'
								) }
								allowedFormats={ [] }
							/>
						</div>
					</div>
					<div className="content-right">
						<time className="event-date" dateTime={ datetimeAttr }>
							<span className="date-day">{ day }</span>
							<span className="date-month-year">
								{ monthYear }
							</span>
						</time>
						{ showImage && imageUrl && (
							<div className="featured-event-image">
								<img src={ imageUrl } alt={ imageAlt } />
							</div>
						) }
					</div>
				</div>
			</div>
		</>
	);
}
