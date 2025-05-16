import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

// Get the theme URL from WordPress data
const FALLBACK_IMAGE =
	'/wp-content/themes/fau-elemental/assets/images/logo.svg';

export default function PageTeaser({ page }) {
    if (!page) return null;

	const themeUrl = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecord( 'root', 'site' )?.url || '';
	}, [] );

    const image = page._embedded?.['wp:featuredmedia']?.[0]?.source_url || `${themeUrl}${FALLBACK_IMAGE}`;
    const title = page.title?.rendered || '';
    const excerpt = (page.excerpt?.rendered || '').replace('[&hellip;]', '..');
    
    // Define variant for consistency with PHP implementation
    const variant = 'page';

	return (
		<a
			className="teaser-item disabled"
			data-variant={ variant }
			aria-labelledby={ `teaser-title-${ page.id }` }
		>
			{ image && (
				<div className="teaser-image-wrapper">
					<div className="teaser-image">
						<img src={ image } alt={ title } loading="lazy" />
					</div>
				</div>
			) }
			<div className="teaser-content-wrapper">
				<div className="teaser-content">
					<div className="content-column">
						<h3
							className="clamp-3"
							id={ `teaser-title-${ page.id }` }
						>
							<span
								className="visually-hidden"
								dangerouslySetInnerHTML={ { __html: title } }
							/>
							<span
								aria-hidden="true"
								dangerouslySetInnerHTML={ { __html: title } }
							/>
						</h3>
						<div className="excerpt clamp-3">
							<span
								className="visually-hidden"
								dangerouslySetInnerHTML={ { __html: excerpt } }
							/>
							<span
								aria-hidden="true"
								dangerouslySetInnerHTML={ { __html: excerpt } }
							/>
						</div>
					</div>
					<div className="button-teaser">
						<span
							className="wp-block-button__link"
							aria-hidden="true"
						>
							<span className="screen-reader-text">
								{ __( 'Read more', 'fau-elemental' ) }
							</span>
						</span>
					</div>
				</div>
			</div>
		</a>
	);
}
