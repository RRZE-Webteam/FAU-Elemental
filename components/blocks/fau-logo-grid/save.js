import { useBlockProps } from '@wordpress/block-editor';

/**
 * Save function for frontend rendering
 */
export default function Save( { attributes } ) {
	const blockProps = useBlockProps.save();
	const { logos = [] } = attributes;

	// Create class attribute
	let className = 'fau-logo-grid';
	if ( attributes.className ) {
		className += ' ' + attributes.className;
	}
	if ( attributes.align ) {
		className += ' align' + attributes.align;
	}

	return (
		<div { ...blockProps } className={ className }>
			{ logos.length > 0 && (
				<div className="fau-logo-grid__container">
					{ logos.map( ( logo, index ) => {
						// Skip invalid logo entries
						if ( ! logo || typeof logo !== 'object' ) {
							return null;
						}

						// Check if logo has an image
						const hasImage = logo.imageId || logo.imageUrl;
						if ( ! hasImage ) {
							return null;
						}

						return (
							<div key={ index } className="fau-logo-grid__item">
								{ logo.link ? (
									<a
										href={ logo.link }
										className="fau-logo-grid__link"
									>
										{ logo.imageUrl ? (
											<img
												src={ logo.imageUrl }
												alt=""
												className="fau-logo-grid__image"
												loading="lazy"
											/>
										) : null }
									</a>
								) : logo.imageUrl ? (
									<img
										src={ logo.imageUrl }
										alt=""
										className="fau-logo-grid__image"
										loading="lazy"
									/>
								) : null }
							</div>
						);
					} ) }
				</div>
			) }
		</div>
	);
}
