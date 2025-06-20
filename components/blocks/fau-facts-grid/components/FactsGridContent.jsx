import { __, sprintf } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';

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
				{ facts.map( ( fact, index ) => (
					<div
						key={ index }
						className={ `fau-facts-grid-item ${
							selectedFactIndex === index ? 'is-active' : ''
						} ${ fact.link && fact.showLink ? 'has-link' : '' }` }
						onClick={ () => setSelectedFactIndex( index ) }
						onKeyDown={ ( event ) => {
							if ( event.key === 'Enter' || event.key === ' ' ) {
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
									src={ `${ window.location.origin }/wp-content/themes/FAU-Elemental/assets/images/fact-icon.png` }
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
								allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
								multiline={ false }
							/>
							{ fact.link && fact.showLink && (
								<div className="wp-block-buttons">
									<div className="wp-block-button is-style-tertiary">
										<a
											className="wp-block-button__link"
											href={ fact.link }
										>
											{ __( 'More', 'fau-elemental' ) }
										</a>
									</div>
								</div>
							) }
						</div>
					</div>
				) ) }
			</div>

			{ facts.length === 0 && (
				<div className="fau-facts-grid-empty-state">
					<p>
						{ __(
							'No facts added yet. Use the "+" button in the block toolbar to get started.',
							'fau-elemental'
						) }
					</p>
				</div>
			) }
		</div>
	);
}
