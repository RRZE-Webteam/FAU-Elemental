import { __ } from '@wordpress/i18n';
import { useSelect, createSelector } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

// Create a stable selector for the REST API base URL
const getRestBaseUrl = createSelector( () => window.location.origin );

export default function PageTeaser( { page, headingLevel = 'h4' } ) {
	if ( ! page ) {
		return null;
	}

	// Get the fallback image from theme customizer or use default fallback image
	const FALLBACK_IMAGE =
		( window.fauElemental && window.fauElemental.fallbackImageUrl ) ??
		'/wp-content/themes/fau-elemental/assets/images/Default_FAU_Schloss_blau.jpg';

	const baseUrl = useSelect( ( select ) => getRestBaseUrl( select ), [] );

	// Memoize derived values
	const memoizedData = useMemo( () => {
		// Return early with empty object if page isn't properly defined
		if ( ! page || ! page.title || ! page.excerpt ) {
			return {
				image: FALLBACK_IMAGE,
				title: '',
				excerpt: '',
			};
		}

		// Check if there's a valid featured image
		const hasFeaturedImage =
			page._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url;
		const imageUrl = hasFeaturedImage
			? page._embedded[ 'wp:featuredmedia' ][ 0 ].source_url
			: FALLBACK_IMAGE;

		return {
			image: imageUrl,
			title: page.title?.rendered || '',
			excerpt: ( page.excerpt?.rendered || '' ).replace(
				'[&hellip;]',
				'..'
			),
		};
	}, [
		page?.id,
		page?.title?.rendered,
		page?.excerpt?.rendered,
		page?._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url,
		baseUrl,
	] );

	// Define variant for consistency with PHP implementation
	const variant = 'page';

	// Dynamically create the heading element
	const HeadingTag = headingLevel;

	return (
		<a
			className="teaser-item disabled"
			data-variant={ variant }
			aria-labelledby={ `teaser-title-${ page.id }` }
			href="#preview"
		>
			{ memoizedData.image && (
				<div className="teaser-image-wrapper">
					<div className="teaser-image">
						<img
							src={ memoizedData.image }
							alt={ memoizedData.title }
							loading="lazy"
						/>
					</div>
				</div>
			) }
			<div className="teaser-content-wrapper">
				<div className="teaser-content">
					<div className="content-column">
						<HeadingTag
							className="clamp-3"
							id={ `teaser-title-${ page.id }` }
						>
							<span
								key="visually-hidden"
								className="visually-hidden"
								dangerouslySetInnerHTML={ {
									__html: memoizedData.title,
								} }
							/>
							<span
								key="aria-hidden"
								aria-hidden="true"
								dangerouslySetInnerHTML={ {
									__html: memoizedData.title,
								} }
							/>
						</HeadingTag>
						<div className="excerpt clamp-3">
							<span
								className="visually-hidden"
								dangerouslySetInnerHTML={ {
									__html: memoizedData.excerpt,
								} }
							/>
							<span
								aria-hidden="true"
								dangerouslySetInnerHTML={ {
									__html: memoizedData.excerpt,
								} }
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
