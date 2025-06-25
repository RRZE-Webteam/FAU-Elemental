import { __, sprintf } from '@wordpress/i18n';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	Button,
	ButtonGroup,
} from '@wordpress/components';

/**
 * Inspector Controls Component
 */
export default function FactsInspectorControls( {
	facts,
	selectedFactIndex,
	updateFact,
	setAttributes,
} ) {
	const selectedFact = facts[ selectedFactIndex ] || null;

	return (
		<InspectorControls>
			{ selectedFact && (
				<PanelBody
					title={ sprintf(
						/* translators: %d: fact number */
						__( 'Fact %d Settings', 'fau-elemental' ),
						selectedFactIndex + 1
					) }
					initialOpen={ true }
				>
					<div className="fau-facts-grid-inspector-info">
						<p className="fau-facts-grid-inspector-help-text">
							{ __(
								'This block displays exactly 4 facts. Click on a fact in the editor to select and edit it.',
								'fau-elemental'
							) }
						</p>
					</div>

					<TextareaControl
						label={ __( 'Fact Text', 'fau-elemental' ) }
						value={ selectedFact.text }
						onChange={ ( value ) =>
							updateFact( selectedFactIndex, 'text', value )
						}
						placeholder={ __(
							'Enter fact text…',
							'fau-elemental'
						) }
						rows={ 3 }
						help={ __(
							'You can also edit this text by clicking on the fact in the editor.',
							'fau-elemental'
						) }
					/>

					<div className="fau-facts-grid-icon-field">
						<label
							htmlFor={ `fau-facts-grid-icon-${ selectedFactIndex }` }
							className="fau-facts-grid-icon-label"
						>
							{ __( 'Icon', 'fau-elemental' ) }
						</label>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => {
									const newFacts = [ ...facts ];
									newFacts[ selectedFactIndex ] = {
										...newFacts[ selectedFactIndex ],
										iconUrl: media.url,
										iconId: media.id,
									};
									setAttributes( { facts: newFacts } );
								} }
								allowedTypes={ [ 'image' ] }
								value={ selectedFact.iconId }
								render={ ( { open } ) => (
									<div
										id={ `fau-facts-grid-icon-${ selectedFactIndex }` }
									>
										{ selectedFact.iconUrl ? (
											<div className="fau-facts-grid-icon-preview">
												<img
													src={ selectedFact.iconUrl }
													alt={ __(
														'Selected icon',
														'fau-elemental'
													) }
												/>
											</div>
										) : null }
										<ButtonGroup>
											<Button
												variant="secondary"
												onClick={ open }
											>
												{ selectedFact.iconUrl
													? __(
															'Change Icon',
															'fau-elemental'
													  )
													: __(
															'Upload Icon',
															'fau-elemental'
													  ) }
											</Button>
											{ selectedFact.iconUrl && (
												<Button
													variant="secondary"
													isDestructive
													onClick={ () => {
														const newFacts = [
															...facts,
														];
														newFacts[
															selectedFactIndex
														] = {
															...newFacts[
																selectedFactIndex
															],
															iconUrl: '',
															iconId: null,
														};
														setAttributes( {
															facts: newFacts,
														} );
													} }
												>
													{ __(
														'Remove',
														'fau-elemental'
													) }
												</Button>
											) }
										</ButtonGroup>
									</div>
								) }
							/>
						</MediaUploadCheck>
					</div>

					<TextControl
						label={ __( 'Link URL', 'fau-elemental' ) }
						value={ selectedFact.link }
						onChange={ ( value ) =>
							updateFact( selectedFactIndex, 'link', value )
						}
						placeholder={ __(
							'https://example.com',
							'fau-elemental'
						) }
						help={ __(
							'A link button will automatically appear when you enter a URL.',
							'fau-elemental'
						) }
					/>
				</PanelBody>
			) }
		</InspectorControls>
	);
}
