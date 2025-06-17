import { __ } from '@wordpress/i18n';
import { useSelect, createSelector } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

// Get the theme URL from WordPress data
const FALLBACK_IMAGE =
	'/wp-content/themes/fau-elemental/assets/images/logo.svg';

// Create a stable selector for the REST API base URL
const getRestBaseUrl = createSelector( ( select ) => window.location.origin );

export default function PostTeaser( { post, headingLevel = 'h4' } ) {
	if ( ! post ) {
		return null;
	}

	const baseUrl = useSelect( ( select ) => getRestBaseUrl( select ), [] );

	// Memoize derived values
	const memoizedData = useMemo( () => {
		// Return early with empty object if post isn't properly defined
		if ( ! post || ! post.title || ! post.excerpt ) {
			return {
				day: '',
				monthYear: '',
				category: null,
				image: `${ baseUrl }${ FALLBACK_IMAGE }`,
				title: '',
				excerpt: '',
			};
		}

		const dateObj = post.date ? new Date( post.date ) : null;

		// Check if there's a valid featured image
		const hasFeaturedImage =
			post._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url;
		const imageUrl = hasFeaturedImage
			? post._embedded[ 'wp:featuredmedia' ][ 0 ].source_url
			: `${ baseUrl }${ FALLBACK_IMAGE }`;

		return {
			day: dateObj
				? dateObj.toLocaleDateString( 'de-DE', { day: '2-digit' } )
				: '',
			monthYear: dateObj
				? dateObj
						.toLocaleDateString( 'de-DE', {
							month: 'short',
							year: 'numeric',
						} )
						.replace( '.', '' )
						.toUpperCase()
				: '',
			category: post._embedded?.[ 'wp:term' ]?.[ 0 ]?.[ 0 ] || null,
			image: imageUrl,
			title: post.title?.rendered || '',
			excerpt: ( post.excerpt?.rendered || '' ).replace(
				'[&hellip;]',
				'..'
			),
		};
	}, [
		post?.id,
		post?.date,
		post?.title?.rendered,
		post?.excerpt?.rendered,
		post?._embedded?.[ 'wp:term' ]?.[ 0 ]?.[ 0 ]?.id,
		post?._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url,
		baseUrl,
	] );

	// Define variant for consistency with PHP implementation
	const variant = 'post';

	// Dynamically create the heading element
	const HeadingTag = headingLevel;

	return (
		<a
			className="teaser-item disabled"
			data-variant={ variant }
			aria-labelledby={ `teaser-title-${ post.id }` }
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
					<div className="teaser-meta">
						<time>
							<span className="date-day">
								{ memoizedData.day }
							</span>
							<span className="date-month-year">
								{ memoizedData.monthYear }
							</span>
						</time>
					</div>
				</div>
			) }
			<div className="teaser-content-wrapper">
				<div className="teaser-content">
					<div className="content-column">
						{ memoizedData.category && (
							<span className="category">
								{ memoizedData.category.name }
							</span>
						) }
						<HeadingTag
							className="clamp-3"
							id={ `teaser-title-${ post.id }` }
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
