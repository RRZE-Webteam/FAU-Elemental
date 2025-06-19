import { __, sprintf } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
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
import { useState } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const { facts } = attributes;
	const [ selectedFactIndex, setSelectedFactIndex ] = useState( 0 );

	const blockProps = useBlockProps();

	const addFact = () => {
		const newFacts = [
			...facts,
			{
				text: '',
				iconUrl: '',
				iconId: null,
				link: '',
				showLink: false,
			},
		];
		setAttributes( { facts: newFacts } );
		setSelectedFactIndex( newFacts.length - 1 );
	};

	const removeFact = ( index ) => {
		const newFacts = facts.filter( ( _, i ) => i !== index );
		setAttributes( { facts: newFacts } );
		if ( selectedFactIndex >= newFacts.length ) {
			setSelectedFactIndex( Math.max( 0, newFacts.length - 1 ) );
		}
	};

	const updateFact = ( index, field, value ) => {
		const newFacts = [ ...facts ];
		newFacts[ index ] = { ...newFacts[ index ], [ field ]: value };
		setAttributes( { facts: newFacts } );
	};

	const selectedFact = facts[ selectedFactIndex ] || null;

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Facts Management', 'fau-elemental' ) }>
					<div className="fau-facts-grid-inspector-buttons">
						<Button
							variant="primary"
							onClick={ addFact }
						>
							{ __( 'Add New Fact', 'fau-elemental' ) }
						</Button>
						{ facts.length > 0 && (
							<Button
								variant="secondary"
								isDestructive
								onClick={ () =>
									removeFact( selectedFactIndex )
								}
							>
								{ __(
									'Remove Selected Fact',
									'fau-elemental'
								) }
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
														src={
															selectedFact.iconUrl
														}
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
								updateFact(
									selectedFactIndex,
									'showLink',
									value
								)
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

			<div className="fau-facts-grid">
				<div className="fau-facts-grid-items">
					{ facts.map( ( fact, index ) => (
						<div
							key={ index }
							className={ `fau-facts-grid-item ${
								selectedFactIndex === index ? 'is-active' : ''
							} ${
								fact.link && fact.showLink ? 'has-link' : ''
							}` }
							onClick={ () => setSelectedFactIndex( index ) }
							onKeyDown={ ( event ) => {
								if (
									event.key === 'Enter' ||
									event.key === ' '
								) {
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
									<img
										src={ fact.iconUrl }
										alt=""
									/>
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
								/>
								{ fact.link && fact.showLink && (
									<div className="wp-block-buttons">
										<div className="wp-block-button is-style-tertiary">
											<a
												className="wp-block-button__link"
												href={ fact.link }
											>
												{ __(
													'Mehr',
													'fau-elemental'
												) }
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
								'No facts added yet. Use the "Add New Fact" button in the sidebar to get started.',
								'fau-elemental'
							) }
						</p>
					</div>
				) }
			</div>
		</div>
	);
}
