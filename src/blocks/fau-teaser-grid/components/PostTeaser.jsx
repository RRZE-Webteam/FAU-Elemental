import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

// Get the theme URL from WordPress data
const FALLBACK_IMAGE = '/wp-content/themes/fau-elemental//assets/images/logo.svg';

export default function PostTeaser({ post, grid }) {
    if (!post) return null;

    const themeUrl = useSelect((select) => {
        return select('core').getEntityRecord('root', 'site')?.url || '';
    }, []);

    const dateObj = post.date ? new Date(post.date) : null;
    const day = dateObj ? dateObj.toLocaleDateString('de-DE', { day: '2-digit' }) : '';
    const monthYear = dateObj ? dateObj.toLocaleDateString('de-DE', {
        month: 'short',
        year: 'numeric'
    }).replace('.', '').toUpperCase() : '';
    const category = post._embedded?.['wp:term']?.[0]?.[0] || null;
    const image = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || `${themeUrl}${FALLBACK_IMAGE}`;
    const title = post.title?.rendered || '';
    const excerpt = (post.excerpt?.rendered || '').replace('[&hellip;]', '..');
    const link = post.link || '#';

    return (
        <div className="teaser-item">
            {image && (
                <div className="teaser-image-wrapper">
                    <a 
                        href={link} 
                        className="teaser-image-link"
                        aria-label={__('Read more about', 'fau-elemental') + ' ' + title}
                    >
                        <div className="teaser-image">
                            <img src={image} alt={title} loading="lazy" />
                        </div>
                    </a>
                    <div className="teaser-meta">
                        <time>
                            <span className="date-day">{day}</span>
                            <span className="date-month-year">{monthYear}</span>
                        </time>
                    </div>
                </div>
            )}
            <div className="teaser-content-wrapper">
                <div className="teaser-content">
                    <div className="content-column">
                        {category && (
                            <a 
                                href={category.link} 
                                className="category"
                                aria-label={__('Category:', 'fau-elemental')}
                            >
                                {category.name}
                            </a>
                        )}
                        <h3 className="clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: title }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: title }} />
                        </h3>
                        <div className="excerpt clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: excerpt }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: excerpt }} />
                        </div>
                    </div>
                    <div className="button-teaser">
                        <a href={link} className="wp-block-button__link"></a>
                    </div>
                </div>
            </div>
        </div>
    );
} 