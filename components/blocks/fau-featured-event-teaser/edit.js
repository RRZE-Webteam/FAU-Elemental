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
	ToolbarGroup,
	ToolbarButton,
	DatePicker,
	Button,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { processEventDate } from './utils/date-helpers';

// Custom Date Picker Component using WordPress DatePicker
function DatePickerField( { label, value, onChange, help } ) {
	const [ showDatePicker, setShowDatePicker ] = useState( false );
	const [ displayValue, setDisplayValue ] = useState( '' );

	// Convert the current eventDate format to Date object for WordPress DatePicker
	const getDateFromString = ( dateString ) => {
		if ( ! dateString || dateString.trim() === '' ) {
			return null;
		}
		const { datetimeAttr } = processEventDate( dateString );
		return datetimeAttr ? new Date( datetimeAttr ) : null;
	};

	// Update display value when value changes
	useEffect( () => {
		setDisplayValue( value || '' );
	}, [ value ] );

	// Convert Date object back to the required format
	const handleDateChange = ( date ) => {
		// WordPress DatePicker returns an ISO string, not a Date object
		if ( ! date ) {
			onChange( '' );
			setDisplayValue( '' );
			setShowDatePicker( false );
			return;
		}

		// Convert ISO string to Date object
		const dateObj = new Date( date );

		if ( isNaN( dateObj.getTime() ) ) {
			onChange( '' );
			setDisplayValue( '' );
			setShowDatePicker( false );
			return;
		}

		const day = dateObj.getDate();
		const month = dateObj.getMonth() + 1; // getMonth() returns 0-11
		const year = dateObj.getFullYear();

		// Format as "DD MM YYYY" - date-helpers.js will handle the localization
		const formattedDate = `${ day } ${ month } ${ year }`;
		onChange( formattedDate );
		setDisplayValue( formattedDate );
		setShowDatePicker( false );
	};

	return (
		<BaseControl label={ label } help={ help } id="fau-event-date-picker">
			<div className="fau-date-picker-container">
				<Button
					variant="secondary"
					onClick={ () => setShowDatePicker( ! showDatePicker ) }
					className="fau-date-picker-button"
					aria-label={ __( 'Open date picker', 'fau-elemental' ) }
				>
					{ displayValue || __( 'Event Date', 'fau-elemental' ) }
				</Button>
				{ showDatePicker && (
					<div className="fau-date-picker-dropdown">
						<DatePicker
							currentDate={ getDateFromString( value ) }
							onChange={ handleDateChange }
							locale={ 'en' }
						/>
					</div>
				) }
			</div>
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
		imageId,
		imageAlt,
	} = attributes;

	// Process the date using shared utility
	const { day, monthYear, datetimeAttr } = processEventDate( eventDate );

	const onSelectImage = ( media ) => {
		setAttributes( {
			imageUrl: media.url,
			imageId: media.id,
			imageAlt: media.alt,
		} );
	};

	const removeImage = () => {
		setAttributes( {
			imageUrl: '',
			imageId: 0,
			imageAlt: '',
		} );
	};

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectImage }
							allowedTypes={ [ 'image' ] }
							value={ imageId }
							render={ ( { open } ) => (
								<ToolbarButton
									icon="format-image"
									label={
										imageUrl
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
					{ imageUrl && (
						<ToolbarButton
							icon="no"
							label={ __( 'Remove Image', 'fau-elemental' ) }
							onClick={ removeImage }
						/>
					) }
				</ToolbarGroup>
			</BlockControls>
			<InspectorControls>
				<PanelBody title={ __( 'Event Details', 'fau-elemental' ) }>
					<DatePickerField
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
				<PanelBody
					title={ __( 'Image Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ onSelectImage }
							allowedTypes={ [ 'image' ] }
							value={ imageId }
							render={ ( { open } ) => (
								<>
									<Button
										onClick={ open }
										variant="secondary"
										className="fau-featured-event-teaser__image-select-button"
									>
										{ imageUrl
											? __(
													'Replace Image',
													'fau-elemental'
											  )
											: __(
													'Select Image',
													'fau-elemental'
											  ) }
									</Button>
									{ imageUrl && (
										<div className="fau-featured-event-teaser__image-preview">
											<img
												src={ imageUrl }
												alt={ imageAlt }
												className="fau-featured-event-teaser__preview-image"
											/>
										</div>
									) }
								</>
							) }
						/>
					</MediaUploadCheck>

					{ imageUrl && (
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
