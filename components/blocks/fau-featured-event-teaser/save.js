import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const {
        eventTitle,
        eventDescription,
        eventDate,
        buttonText,
        buttonUrl,
        showImage,
        imageUrl,
        imageAlt
    } = attributes;

    // Split the date into day and month/year
    const dateParts = eventDate ? eventDate.split(' ') : ['01', 'Okt', '2024'];
    const day = dateParts[0];
    const monthYear = dateParts.slice(1).join(' ');

    const blockProps = useBlockProps.save({
        className: 'wp-block-fau-elemental-featured-event-teaser'
    });

    return (
        <div {...blockProps}>
            <div className="featured-event-content">
                <div className="content-left">
                    <h2 className="event-title">{eventTitle}</h2>
                    <p className="event-description">{eventDescription}</p>
                    <div className="wp-block-button">
                        <a className="wp-block-button__link" href={buttonUrl}>
                            {buttonText}
                        </a>
                    </div>
                </div>
                <div className="content-right">
                    <div className="event-date">
                        <div className="date-day">{day}</div>
                        <div className="date-month-year">{monthYear}</div>
                    </div>
                    {showImage && imageUrl && (
                        <div className="featured-event-image">
                            <img src={imageUrl} alt={imageAlt} />
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
} 