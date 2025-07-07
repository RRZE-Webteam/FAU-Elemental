import { RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Save function - generates static HTML
 */
export default function Save( { attributes } ) {
	// Filter out quotes with empty content
	const validQuotes = attributes.quotes.filter(
		( quote ) => quote.content && quote.content.trim() !== ''
	);

	const blockProps = useBlockProps.save( {
		className: 'quote-carousel',
	} );

	const renderQuote = ( quote ) => (
		<div className="quote-content">
			{ quote.image && (
				<figure className="quote-image">
					<img
						src={ quote.image.url }
						alt={ quote.image.alt || '' }
					/>
				</figure>
			) }
			<div className="quote-text">
				<blockquote>
					<RichText.Content value={ quote.content } />
				</blockquote>
				{ quote.citation && (
					<cite>
						<RichText.Content value={ quote.citation } />
					</cite>
				) }
			</div>
		</div>
	);

	if ( validQuotes.length === 1 ) {
		return (
			<div className="wp-block-quote-item">
				{ renderQuote( validQuotes[ 0 ] ) }
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<div className="carousel-container">
				{ validQuotes.map( ( quote ) => (
					<div key={ quote.id } className="quote-slide">
						<div className="wp-block-quote-item">
							{ renderQuote( quote ) }
						</div>
					</div>
				) ) }
			</div>
			<div className="carousel-controls">
				<button
					className="carousel-prev"
					aria-label={ __( 'Previous slide', 'fau-elemental' ) }
				>
					❮
				</button>
				<div className="carousel-dots"></div>
				<button
					className="carousel-next"
					aria-label={ __( 'Next slide', 'fau-elemental' ) }
				>
					❯
				</button>
			</div>
		</div>
	);
}
