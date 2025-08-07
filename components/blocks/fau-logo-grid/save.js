import { useBlockProps } from '@wordpress/block-editor';

/**
 * Save function for frontend rendering
 */
export default function Save( { attributes } ) {
	const { logos = [] } = attributes;

	// Create class attribute
	let className = 'fau-logo-grid';
	if ( attributes.className ) {
		className += ' ' + attributes.className;
	}
	if ( attributes.align ) {
		className += ' align' + attributes.align;
	}

	const blockProps = useBlockProps.save( {
		className,
	} );

	return (
		<div { ...blockProps }>
			{ logos.length > 0 && (
				<div className="fau-logo-grid__container">
					{ logos.map( ( logo, index ) => {
						// Skip invalid logo entries
						if ( ! logo || typeof logo !== 'object' ) {
							return null;
						}

						// Check if logo has an image URL (imageId is only used in editor)
						if ( ! logo.imageUrl ) {
							return null;
						}

						// Create image element once to avoid duplication
						const imageElement = (
							<img
								src={ logo.imageUrl }
								alt=""
								className="fau-logo-grid__image"
								loading="lazy"
							/>
						);

						return (
							<div key={ index } className="fau-logo-grid__item">
								{ logo.link ? (
									<a
										href={ logo.link }
										className="fau-logo-grid__link"
									>
										{ imageElement }
									</a>
								) : (
									imageElement
								) }
							</div>
						);
					} ) }
				</div>
			) }
		</div>
	);
}
