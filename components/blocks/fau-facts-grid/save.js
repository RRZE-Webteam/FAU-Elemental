import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Save function - generates static HTML
 */
export default function Save( { attributes } ) {
	const { facts } = attributes;
	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			<div className="fau-facts-grid">
				{ facts && facts.length > 0 && (
					<div className="fau-facts-grid-items">
						{ facts.map( ( fact, index ) => {
							if ( ! fact || typeof fact !== 'object' ) {
								return null;
							}

							const {
								text = '',
								iconUrl = '',
								link = '',
							} = fact;

							// Don't render fact if no text is provided
							if ( ! text || text.trim() === '' ) {
								return null;
							}

							const hasLinkClass = link ? ' has-link' : '';

							return (
								<div
									key={ index }
									className={ `fau-facts-grid-item${ hasLinkClass }` }
								>
									<div className="fau-facts-grid-item-icon">
										{ iconUrl ? (
											<img src={ iconUrl } alt="" />
										) : (
											<img
												src={ `${window.fauElemental?.themeUrl}/assets/images/fact-icon.png` }
												alt=""
											/>
										) }
									</div>
									<div className="fau-facts-grid-item-content">
										{ text && (
											<div
												className="fau-facts-grid-item-text"
												dangerouslySetInnerHTML={ {
													__html: text,
												} }
											/>
										) }

										{ link && (
											<div className="wp-block-buttons">
												<div className="wp-block-button is-style-tertiary">
													<a
														className="wp-block-button__link"
														href={ link }
													>
														{ __(
															'More',
															'fau-elemental'
														) }
													</a>
												</div>
											</div>
										) }
									</div>
								</div>
							);
						} ) }
					</div>
				) }
			</div>
		</div>
	);
} 