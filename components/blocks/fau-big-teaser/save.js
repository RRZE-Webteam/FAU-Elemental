import { useBlockProps } from '@wordpress/block-editor';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
    const {
        headline = '',
        teaserText = '',
        linkText = '',
        linkUrl = '',
        image = null
    } = attributes;

    // Helper function to truncate text (similar to PHP version)
    const truncateText = (text, length) => {
        if (!text || text.length <= length) {
            return text;
        }
        
        const truncated = text.substring(0, length);
        const lastSpace = truncated.lastIndexOf(' ');
        
        if (lastSpace !== -1 && lastSpace > length * 0.8) {
            return truncated.substring(0, lastSpace) + '...';
        }
        
        return truncated + '...';
    };

    // Apply character limits (same as PHP version)
    const truncatedHeadline = truncateText(headline, 100);
    const truncatedTeaserText = truncateText(teaserText, 200);
    const truncatedLinkText = truncateText(linkText, 40);

    const blockProps = useBlockProps.save({
        className: 'fau-big-teaser'
    });

    return (
        <section {...blockProps}>
            {image && image.url && (
                <div className="fau-big-teaser__image">
                    <img 
                        src={image.url} 
                        alt={image.alt || truncatedHeadline || ''} 
                        loading="lazy" 
                    />
                </div>
            )}
            
            <div className="fau-big-teaser__content">
                {truncatedHeadline && (
                    <h3 className="fau-big-teaser__headline">
                        {truncatedHeadline}
                    </h3>
                )}
                
                {truncatedTeaserText && (
                    <p className="fau-big-teaser__teaser-text">
                        {truncatedTeaserText}
                    </p>
                )}
                
                {truncatedLinkText && linkUrl && (
                    <a 
                        href={linkUrl}
                        className="fau-big-teaser__link"
                    >
                        {truncatedLinkText}
                    </a>
                )}
            </div>
        </section>
    );
} 