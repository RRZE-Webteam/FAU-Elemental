import { RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Save function - generates static HTML
 */
export default function Save( { attributes } ) {
	// Filter out items with empty content
	const validItems = attributes.items.filter(
		( item ) => item.content && item.content.trim() !== ''
	);

	const blockProps = useBlockProps.save( {
		className: 'testimonial-carousel',
	} );

	const renderItem = ( testimonial ) => (
		<div className="testimonial-content">
			{ testimonial.image && (
				<figure className="testimonial-image">
					<img
						src={ testimonial.image.url }
						alt={ testimonial.image.alt || '' }
					/>
				</figure>
			) }
			<div className="testimonial-text">
				<blockquote>
					<RichText.Content value={ testimonial.content } />
				</blockquote>
				{ testimonial.citation && (
					<cite>
						<RichText.Content value={ testimonial.citation } />
					</cite>
				) }
			</div>
		</div>
	);

	if ( validItems.length === 1 ) {
		return (
			<div className="fau-testimonial-item">
				{ renderItem( validItems[ 0 ] ) }
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<div className="carousel-container">
				{ validItems.map( ( item ) => (
					<div key={ item.id } className="testimonial-slide">
						<div className="fau-testimonial-item">
							{ renderItem( item ) }
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
