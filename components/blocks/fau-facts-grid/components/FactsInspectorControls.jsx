import { __, sprintf } from '@wordpress/i18n';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	TextControl,
	Button,
	ButtonGroup,
} from '@wordpress/components';

/**
 * Inspector Controls Component
 */
export default function FactsInspectorControls( {
	facts,
	selectedFactIndex,
	addFact,
	removeFact,
	updateFact,
	setAttributes,
} ) {
	const selectedFact = facts[ selectedFactIndex ] || null;

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Facts Management', 'fau-elemental' ) }>
				<div className="fau-facts-grid-inspector-buttons">
					<Button variant="primary" onClick={ addFact }>
						{ __( 'Add New Fact', 'fau-elemental' ) }
					</Button>
					{ facts.length > 0 && (
						<Button
							variant="secondary"
							isDestructive
							onClick={ () => removeFact( selectedFactIndex ) }
						>
							{ __( 'Remove Selected Fact', 'fau-elemental' ) }
						</Button>
					) }
				</div>
				<p className="fau-facts-grid-inspector-help-text">
					{ __(
						'Click on a fact in the editor to select and edit it.',
						'fau-elemental'
					) }
				</p>
			</PanelBody>

			{ selectedFact && (
				<PanelBody
					title={ sprintf(
						/* translators: %d: fact number */
						__( 'Fact %d Settings', 'fau-elemental' ),
						selectedFactIndex + 1
					) }
				>
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
					/>

					<ToggleControl
						label={ __( 'Show Link Button', 'fau-elemental' ) }
						checked={ selectedFact.showLink }
						onChange={ ( value ) =>
							updateFact( selectedFactIndex, 'showLink', value )
						}
						help={
							selectedFact.link
								? __(
										'Toggle to show/hide the link button',
										'fau-elemental'
								  )
								: __(
										'Add a link URL above to enable this option',
										'fau-elemental'
								  )
						}
					/>
				</PanelBody>
			) }
		</InspectorControls>
	);
}
