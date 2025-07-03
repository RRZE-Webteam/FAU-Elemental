import { useBlockProps } from '@wordpress/block-editor';
import { processEventDate } from './utils/date-helpers';

export default function Save( { attributes } ) {
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

	const blockProps = useBlockProps.save( {
		className: 'wp-block-fau-elemental-featured-event-teaser',
	} );

	return (
		<div { ...blockProps }>
			<div className="featured-event-content">
				<div className="content-left">
					<h2 className="event-title">{ eventTitle }</h2>
					<p className="event-description">{ eventDescription }</p>
					<div className="wp-block-button">
						<a className="wp-block-button__link" href={ buttonUrl }>
							{ buttonText }
						</a>
					</div>
				</div>
				<div className="content-right">
					<time className="event-date" dateTime={ datetimeAttr }>
						<span className="date-day">{ day }</span>
						<span className="date-month-year">{ monthYear }</span>
					</time>
					{ showImage && imageUrl && (
						<div className="featured-event-image">
							<img src={ imageUrl } alt={ imageAlt } />
						</div>
					) }
				</div>
			</div>
		</div>
	);
}
