import { useBlockProps } from '@wordpress/block-editor';

/**
 * Save component for the FAU Big Teaser block.
 *
 * Renders a promotional teaser section with optional image, headline, description text,
 * and action button. Automatically truncates text content to maintain consistent layout:
 * - Headlines limited to 100 characters
 * - Teaser text limited to 200 characters
 * - Link text limited to 40 characters
 *
 * @return {Element} Section element with fau-big-teaser styling and structured content.
 */
export default function Save( { attributes } ) {
	const {
		headline = '',
		teaserText = '',
		linkText = '',
		linkUrl = '',
		image = null,
	} = attributes;

	// Helper function to truncate text (similar to PHP version)
	const truncateText = ( text, length ) => {
		if ( ! text || text.length <= length ) {
			return text;
		}

		const truncated = text.substring( 0, length );
		const lastSpace = truncated.lastIndexOf( ' ' );

		if ( lastSpace !== -1 && lastSpace > length * 0.8 ) {
			return truncated.substring( 0, lastSpace ) + '…';
		}

		return truncated + '…';
	};

	// Apply character limits (same as PHP version)
	const truncatedHeadline = truncateText( headline, 100 );
	const truncatedTeaserText = truncateText( teaserText, 200 );
	const truncatedLinkText = truncateText( linkText, 40 );

	const blockProps = useBlockProps.save( {
		className: 'fau-big-teaser',
	} );

	return (
		<section { ...blockProps }>
			{ image && image.url && (
				<div className="fau-big-teaser__image">
					<img
						src={ image.url }
						alt={ image.alt || truncatedHeadline || '' }
						loading="lazy"
					/>
				</div>
			) }

			<div className="fau-big-teaser__content">
				{ truncatedHeadline && (
					<h3 className="fau-big-teaser__headline">
						{ truncatedHeadline }
					</h3>
				) }

				{ truncatedTeaserText && (
					<p className="fau-big-teaser__teaser-text">
						{ truncatedTeaserText }
					</p>
				) }

				{ truncatedLinkText && linkUrl && (
					<div className="wp-block-button is-style-tertiary">
						<a
							href={ linkUrl }
							className="wp-block-button__link wp-element-button"
						>
							{ truncatedLinkText }
						</a>
					</div>
				) }
			</div>
		</section>
	);
}
