import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const {
        subtitle,
        showSubtitle,
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
    const dateParts = eventDate ? eventDate.split(' ') : ['01', 'Okt 2024'];
    const day = dateParts[0];
    const monthYear = dateParts.slice(1).join(' ');

    return (
        <div {...useBlockProps.save({
            className: 'wp-block-fau-elemental-featured-event-teaser'
        })}>
            <div className="featured-event-content">
                <div className="content-left">
                    {showSubtitle && (
                        <RichText.Content
                            tagName="p"
                            className="event-subtitle"
                            value={subtitle}
                        />
                    )}
                    <RichText.Content
                        tagName="h2"
                        className="event-title"
                        value={eventTitle}
                    />
                    <RichText.Content
                        tagName="p"
                        className="event-description"
                        value={eventDescription}
                    />
                    <div className="wp-block-button">
                        <a className="wp-block-button__link" href={buttonUrl}>
                            {buttonText}
                            <span className="button-arrow">→</span>
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