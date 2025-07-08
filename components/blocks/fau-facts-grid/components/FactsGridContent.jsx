import { __, sprintf } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
import { validateUrl } from '../../../utils/urlValidation';

/**
 * Facts Grid Content Component
 */
export default function FactsGridContent( {
	facts,
	selectedFactIndex,
	setSelectedFactIndex,
	updateFact,
} ) {
	return (
		<div className="fau-facts-grid">
			<div className="fau-facts-grid-items">
				{ facts.map( ( fact, index ) => {
					const urlValidation = validateUrl( fact.link );

					return (
						<div
							key={ index }
							className={ `fau-facts-grid-item ${
								selectedFactIndex === index ? 'is-active' : ''
							} ${ fact.link ? 'has-link' : '' } ${
								fact.link && ! urlValidation.isValid
									? 'has-url-error'
									: ''
							}` }
							onClick={ () => setSelectedFactIndex( index ) }
							onKeyDown={ ( event ) => {
								if ( event.key === 'Enter' ) {
									event.preventDefault();
									setSelectedFactIndex( index );
								}
							} }
							role="button"
							tabIndex={ 0 }
							aria-label={ sprintf(
								/* translators: %d: fact number */
								__( 'Edit fact %d', 'fau-elemental' ),
								index + 1
							) }
						>
							<div className="fau-facts-grid-item-icon">
								{ fact.iconUrl ? (
									<img src={ fact.iconUrl } alt="" />
								) : (
									<img
										src={ `${ window.fauElemental?.themeUrl }/assets/images/fact-icon.png` }
										alt=""
									/>
								) }
							</div>
							<div className="fau-facts-grid-item-content">
								<RichText
									tagName="div"
									className="fau-facts-grid-item-text"
									value={ fact.text }
									onChange={ ( value ) =>
										updateFact( index, 'text', value )
									}
									placeholder={ __(
										'Enter fact text…',
										'fau-elemental'
									) }
									preserveWhiteSpace={ true }
									allowedFormats={ [
										'core/bold',
										'core/italic',
										'core/link',
									] }
									multiline={ false }
								/>
								{ fact.link && (
									<div className="wp-block-buttons">
										<div className="wp-block-button is-style-tertiary">
											<span className="wp-block-button__link">
												{ __(
													'More',
													'fau-elemental'
												) }
											</span>
										</div>
									</div>
								) }
							</div>
						</div>
					);
				} ) }
			</div>
		</div>
	);
}
