import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import Edit from './edit';
import metadata from './block.json';

/**
 * Save function - generates static HTML
 */
function Save( { attributes } ) {
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
								showLink = false,
							} = fact;

							const hasLinkClass =
								link && showLink ? ' has-link' : '';

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
												src="/wp-content/themes/fau-elemental/assets/images/fact-icon.png"
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

										{ link && showLink && (
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

registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: Save,
	deprecated: [
		{
			// Previous version that used dynamic rendering
			attributes: metadata.attributes,
			save: () => null, // Was a dynamic block
			migrate( attributes ) {
				// No migration needed, just use the same attributes
				return attributes;
			},
			// eslint-disable-next-line no-unused-vars
			isEligible( attributes, innerBlocks ) {
				// This deprecated version applies to blocks that have no saved content
				// but have attributes (i.e., were previously dynamic blocks)
				return true;
			},
		},
	],
} );
