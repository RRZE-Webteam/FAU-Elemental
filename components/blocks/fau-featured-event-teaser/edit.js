import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	BaseControl,
	ToolbarButton,
} from '@wordpress/components';
import { processEventDate } from './utils/date-helpers';
import { useState, useEffect } from '@wordpress/element';

// Custom Date Picker Component
function DatePicker( { label, value, onChange, help } ) {
	const [ dateValue, setDateValue ] = useState( '' );

	// Convert the current eventDate format to HTML5 date format (YYYY-MM-DD)
	useEffect( () => {
		if ( value ) {
			const { datetimeAttr } = processEventDate( value );
			setDateValue( datetimeAttr || '' );
		} else {
			setDateValue( '' );
		}
	}, [ value ] );

	// Convert HTML5 date format back to the required format
	const handleDateChange = ( newDateValue ) => {
		setDateValue( newDateValue );

		if ( newDateValue ) {
			const date = new Date( newDateValue );
			const day = date.getDate().toString();
			const month = date.toLocaleDateString( 'en-US', {
				month: 'short',
			} );
			const year = date.getFullYear().toString();

			// Convert English month to localized month
			const englishToLocalized = {
				Jan: __( 'Jan', 'fau-elemental' ),
				Feb: __( 'Feb', 'fau-elemental' ),
				Mar: __( 'Mar', 'fau-elemental' ),
				Apr: __( 'Apr', 'fau-elemental' ),
				May: __( 'May', 'fau-elemental' ),
				Jun: __( 'Jun', 'fau-elemental' ),
				Jul: __( 'Jul', 'fau-elemental' ),
				Aug: __( 'Aug', 'fau-elemental' ),
				Sep: __( 'Sep', 'fau-elemental' ),
				Oct: __( 'Oct', 'fau-elemental' ),
				Nov: __( 'Nov', 'fau-elemental' ),
				Dec: __( 'Dec', 'fau-elemental' ),
			};

			const localizedMonth = englishToLocalized[ month ] || month;
			const formattedDate = `${ day } ${ localizedMonth } ${ year }`;
			onChange( formattedDate );
		} else {
			onChange( '' );
		}
	};

	return (
		<BaseControl label={ label } help={ help } id="fau-event-date-picker">
			<input
				type="date"
				value={ dateValue }
				onChange={ ( e ) => handleDateChange( e.target.value ) }
				className="components-text-control__input fau-date-picker"
			/>
		</BaseControl>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		eventTitle,
		eventDescription,
		eventDate,
		buttonText,
		buttonUrl,
		imageUrl,
		imageAlt,
	} = attributes;

	// Process the date using shared utility
	const { day, monthYear, datetimeAttr } = processEventDate( eventDate );

	return (
		<>
			<BlockControls>
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
							<ToolbarButton
								icon={ imageUrl ? undefined : 'plus' }
								label={
									imageUrl
										? __( 'Replace Image', 'fau-elemental' )
										: __( 'Add Image', 'fau-elemental' )
								}
								onClick={ open }
							>
								{ imageUrl && __( 'Replace', 'fau-elemental' ) }
							</ToolbarButton>
						) }
					/>
				</MediaUploadCheck>
			</BlockControls>
			<InspectorControls>
				<PanelBody title={ __( 'Event Details', 'fau-elemental' ) }>
					<DatePicker
						label={ __( 'Event Date', 'fau-elemental' ) }
						value={ eventDate }
						onChange={ ( value ) =>
							setAttributes( { eventDate: value } )
						}
						help={ __(
							'Select the event date using the date picker.',
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
				<PanelBody title={ __( 'Image Settings', 'fau-elemental' ) }>
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
								<div className="fau-image-controls">
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
									{ imageUrl && (
										<button
											onClick={ () =>
												setAttributes( {
													imageUrl: '',
													imageAlt: '',
												} )
											}
											className="fau-remove-image-button"
										>
											{ __(
												'Remove Image',
												'fau-elemental'
											) }
										</button>
									) }
								</div>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>
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
							allowedFormats={ [] }
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
							allowedFormats={ [] }
						/>
						<div className="wp-block-buttons">
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
					</div>
					<div className="content-right">
						<time className="event-date" dateTime={ datetimeAttr }>
							<span className="date-day">{ day }</span>
							<span className="date-month-year">
								{ monthYear }
							</span>
						</time>
						{ imageUrl && (
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
