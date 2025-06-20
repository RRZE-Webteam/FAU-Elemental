/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	TextControl,
	TextareaControl,
	SelectControl,
	RangeControl,
	Button,
	ComboboxControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		headline,
		showHeadline,
		teaserText,
		showTeaserText,
		facultyColor,
		teaserSize,
		variant,
		numberOfButtons,
		selectedPages,
		showAllPages,
		showFaculties,
		facultyItems,
	} = attributes;

	const [ searchTerm, setSearchTerm ] = useState( '' );

	// Faculty color options for individual items
	const facultyColorOptions = [
		{ label: __( 'Default', 'fau-elemental' ), value: 'default' },
		{ label: __( 'Philosophy', 'fau-elemental' ), value: 'phil' },
		{ label: __( 'Law & Economics', 'fau-elemental' ), value: 'rw' },
		{ label: __( 'Medicine', 'fau-elemental' ), value: 'med' },
		{ label: __( 'Sciences', 'fau-elemental' ), value: 'nat' },
		{ label: __( 'Technology', 'fau-elemental' ), value: 'tf' },
	];

	// Get available pages from WordPress
	const { availablePages, pages, allPages } = useSelect(
		( select ) => {
			// Don't fetch pages when in faculty showcase mode
			if ( showFaculties ) {
				return {
					availablePages: [],
					pages: [],
					allPages: [],
				};
			}

			const { getEntityRecords } = select( 'core' );
			const query = {
				per_page: 100,
				status: 'publish',
				search: searchTerm,
			};

			const pagesData =
				getEntityRecords( 'postType', 'page', query ) || [];
			const selectedPagesData =
				selectedPages.length > 0
					? getEntityRecords( 'postType', 'page', {
							include: selectedPages.map( ( p ) => p.id ),
							per_page: selectedPages.length,
					  } ) || []
					: [];

			// Fetch all pages when showAllPages is enabled
			const allPagesData = showAllPages
				? getEntityRecords( 'postType', 'page', {
						per_page: -1, // Get all pages
						status: 'publish',
						orderby: 'title',
						order: 'asc',
				  } ) || []
				: [];

			return {
				availablePages: pagesData,
				pages: showAllPages ? allPagesData : selectedPagesData,
				allPages: allPagesData,
			};
		},
		[ searchTerm, selectedPages, showAllPages, showFaculties ]
	);

	const blockProps = useBlockProps( {
		className: `fau-big-button-teaser-group fau-big-button-teaser-group--${ teaserSize } fau-big-button-teaser-group--${ variant } ${
			! showFaculties && facultyColor !== 'default'
				? `fau-big-button-teaser-group--${ facultyColor }`
				: ''
		} ${
			showFaculties ? 'fau-big-button-teaser-group--faculty-showcase' : ''
		}`,
	} );

	// Handle page selection
	const handlePageSelection = ( pageId ) => {
		if ( ! pageId ) {
			return;
		}

		const page = availablePages.find( ( p ) => p.id === pageId );
		if ( ! page ) {
			return;
		}

		// Don't add if already selected
		if ( selectedPages.some( ( p ) => p.id === page.id ) ) {
			return;
		}

		// Don't add if we've reached the maximum number of buttons
		if ( selectedPages.length >= numberOfButtons ) {
			return;
		}

		const newSelectedPages = [
			...selectedPages,
			{
				id: page.id,
				title: page.title.rendered,
				excerpt: page.excerpt.rendered,
				link: page.link,
			},
		];

		setAttributes( { selectedPages: newSelectedPages } );
	};

	// Remove selected page
	const removeSelectedPage = ( pageId ) => {
		const newSelectedPages = selectedPages.filter(
			( p ) => p.id !== pageId
		);
		setAttributes( { selectedPages: newSelectedPages } );
	};

	// Update number of buttons and trim selected pages if needed
	const updateNumberOfButtons = ( newNumber ) => {
		const updatedSelectedPages = selectedPages.slice( 0, newNumber );
		const updatedFacultyItems = facultyItems.slice( 0, newNumber );
		setAttributes( {
			numberOfButtons: newNumber,
			selectedPages: updatedSelectedPages,
			facultyItems: updatedFacultyItems,
		} );
	};

	// Faculty Items Management
	const addFacultyItem = () => {
		if ( facultyItems.length >= numberOfButtons ) {
			return;
		}

		const newFacultyItem = {
			id: Date.now(), // Simple ID generation
			title: '',
			description: '',
			url: '',
			facultyColor: 'default',
		};

		setAttributes( {
			facultyItems: [ ...facultyItems, newFacultyItem ],
		} );
	};

	const updateFacultyItem = ( index, field, value ) => {
		const updatedFacultyItems = [ ...facultyItems ];
		updatedFacultyItems[ index ][ field ] = value;
		setAttributes( { facultyItems: updatedFacultyItems } );
	};

	const removeFacultyItem = ( index ) => {
		const updatedFacultyItems = facultyItems.filter(
			( _, i ) => i !== index
		);
		setAttributes( { facultyItems: updatedFacultyItems } );
	};

	// Content mode toggle handler
	const handleContentModeChange = ( mode ) => {
		if ( mode === 'pages' ) {
			setAttributes( { showFaculties: false, showAllPages: false } );
		} else if ( mode === 'allPages' ) {
			setAttributes( { showFaculties: false, showAllPages: true } );
		} else if ( mode === 'faculties' ) {
			setAttributes( { showFaculties: true, showAllPages: false } );
		}
	};

	// Get current content mode
	const getCurrentContentMode = () => {
		if ( showFaculties ) {
			return 'faculties';
		}
		if ( showAllPages ) {
			return 'allPages';
		}
		return 'pages';
	};

	// Get items for preview
	const getPreviewItems = () => {
		if ( showFaculties ) {
			return facultyItems.slice( 0, numberOfButtons );
		}
		return pages ? pages.slice( 0, numberOfButtons ) : [];
	};

	const previewItems = getPreviewItems();

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Content Settings', 'fau-elemental' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Show Headline', 'fau-elemental' ) }
						checked={ showHeadline }
						onChange={ ( value ) =>
							setAttributes( { showHeadline: value } )
						}
					/>
					{ showHeadline && (
						<TextControl
							label={ __( 'Headline', 'fau-elemental' ) }
							value={ headline }
							onChange={ ( value ) =>
								setAttributes( { headline: value } )
							}
						/>
					) }

					<ToggleControl
						label={ __( 'Show Teaser Text', 'fau-elemental' ) }
						checked={ showTeaserText }
						onChange={ ( value ) =>
							setAttributes( { showTeaserText: value } )
						}
					/>
					{ showTeaserText && (
						<TextareaControl
							label={ __( 'Teaser Text', 'fau-elemental' ) }
							value={ teaserText }
							onChange={ ( value ) =>
								setAttributes( { teaserText: value } )
							}
						/>
					) }
				</PanelBody>

				<PanelBody
					title={ __( 'Appearance Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
					{ ! showFaculties && (
						<SelectControl
							label={ __( 'Faculty Color', 'fau-elemental' ) }
							value={ facultyColor }
							options={ facultyColorOptions }
							onChange={ ( value ) =>
								setAttributes( { facultyColor: value } )
							}
							help={ __(
								'Global color applied to all buttons when not in faculty showcase mode.',
								'fau-elemental'
							) }
						/>
					) }

					<SelectControl
						label={ __( 'Teaser Size', 'fau-elemental' ) }
						value={ teaserSize }
						options={ [
							{
								label: __( 'Small', 'fau-elemental' ),
								value: 'small',
							},
							{
								label: __( 'Large', 'fau-elemental' ),
								value: 'large',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { teaserSize: value } )
						}
					/>

					<SelectControl
						label={ __( 'Variant', 'fau-elemental' ) }
						value={ variant }
						options={ [
							{
								label: __( 'Filled', 'fau-elemental' ),
								value: 'filled',
							},
							{
								label: __( 'Outline', 'fau-elemental' ),
								value: 'outline',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { variant: value } )
						}
					/>

					<RangeControl
						label={ __( 'Number of Buttons', 'fau-elemental' ) }
						value={ numberOfButtons }
						onChange={ updateNumberOfButtons }
						min={ 1 }
						max={ 10 }
						help={ __(
							'Select the number of buttons to display.',
							'fau-elemental'
						) }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Content Mode', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Content Source', 'fau-elemental' ) }
						value={ getCurrentContentMode() }
						options={ [
							{
								label: __( 'Selected Pages', 'fau-elemental' ),
								value: 'pages',
							},
							{
								label: __( 'All Pages', 'fau-elemental' ),
								value: 'allPages',
							},
							{
								label: __(
									'Faculty Showcase',
									'fau-elemental'
								),
								value: 'faculties',
							},
						] }
						onChange={ handleContentModeChange }
						help={ __(
							'Choose how to populate the button content.',
							'fau-elemental'
						) }
					/>

					{ showFaculties && (
						<div style={ { marginTop: '16px' } }>
							<p
								style={ {
									fontStyle: 'italic',
									fontSize: '13px',
								} }
							>
								{ __(
									'Faculty Showcase Mode: Create custom faculty buttons with individual colors, titles, and links.',
									'fau-elemental'
								) }
							</p>
						</div>
					) }
				</PanelBody>

				{ showFaculties && (
					<PanelBody
						title={ __( 'Faculty Items', 'fau-elemental' ) }
						initialOpen={ true }
					>
						<div style={ { marginBottom: '16px' } }>
							<Button
								isPrimary
								onClick={ addFacultyItem }
								disabled={
									facultyItems.length >= numberOfButtons
								}
							>
								{ __( 'Add Faculty Item', 'fau-elemental' ) }
							</Button>
							{ facultyItems.length >= numberOfButtons && (
								<p
									style={ {
										fontSize: '13px',
										color: '#d63638',
										marginTop: '8px',
									} }
								>
									{ __(
										'Maximum number of faculty items reached. Increase "Number of Buttons" to add more.',
										'fau-elemental'
									) }
								</p>
							) }
						</div>

						{ facultyItems.map( ( item, index ) => {
							const itemTitle =
								item.title ||
								__( 'Untitled Faculty Item', 'fau-elemental' );
							const panelTitle = `${ __(
								'Faculty Item',
								'fau-elemental'
							) } ${ index + 1 }${
								itemTitle &&
								itemTitle !==
									__(
										'Untitled Faculty Item',
										'fau-elemental'
									)
									? ` - ${ itemTitle }`
									: ''
							}`;

							return (
								<PanelBody
									key={ item.id || index }
									title={ panelTitle }
									initialOpen={ index === 0 } // Only first item open by default
								>
									<div
										style={ {
											marginBottom: '16px',
											textAlign: 'right',
										} }
									>
										<Button
											isSmall
											isDestructive
											onClick={ () =>
												removeFacultyItem( index )
											}
										>
											{ __(
												'Remove Faculty Item',
												'fau-elemental'
											) }
										</Button>
									</div>

									<TextControl
										label={ __( 'Title', 'fau-elemental' ) }
										value={ item.title }
										onChange={ ( value ) =>
											updateFacultyItem(
												index,
												'title',
												value
											)
										}
									/>
									<TextareaControl
										label={ __(
											'Description',
											'fau-elemental'
										) }
										value={ item.description }
										onChange={ ( value ) =>
											updateFacultyItem(
												index,
												'description',
												value
											)
										}
										rows={ 3 }
									/>
									<TextControl
										label={ __( 'URL', 'fau-elemental' ) }
										value={ item.url }
										onChange={ ( value ) =>
											updateFacultyItem(
												index,
												'url',
												value
											)
										}
										type="url"
									/>
									<SelectControl
										label={ __(
											'Faculty Color',
											'fau-elemental'
										) }
										value={ item.facultyColor }
										options={ facultyColorOptions }
										onChange={ ( value ) =>
											updateFacultyItem(
												index,
												'facultyColor',
												value
											)
										}
									/>
								</PanelBody>
							);
						} ) }

						{ facultyItems.length === 0 && (
							<p style={ { fontStyle: 'italic', color: '#666' } }>
								{ __(
									'No faculty items added yet. Click "Add Faculty Item" to get started.',
									'fau-elemental'
								) }
							</p>
						) }
					</PanelBody>
				) }

				{ ! showFaculties && (
					<PanelBody
						title={ __( 'Page Selection', 'fau-elemental' ) }
						initialOpen={ false }
					>
						{ ! showAllPages && (
							<>
								<ComboboxControl
									label={ __(
										'Search and select pages',
										'fau-elemental'
									) }
									value=""
									onChange={ handlePageSelection }
									options={
										availablePages
											? availablePages.map(
													( page ) => ( {
														value: page.id,
														label: page.title
															.rendered,
													} )
											  )
											: []
									}
									onFilterValueChange={ setSearchTerm }
									aria-label={ __(
										'Search and select pages',
										'fau-elemental'
									) }
									__next40pxDefaultSize={ true }
									__nextHasNoMarginBottom={ true }
									help={
										selectedPages.length >= numberOfButtons
											? __(
													'Maximum number of pages selected for current button count.',
													'fau-elemental'
											  )
											: __(
													'Type to search for pages to add as button teasers.',
													'fau-elemental'
											  )
									}
								/>

								<div className="selected-pages-list">
									{ selectedPages.map( ( page, index ) => (
										<div
											key={ page.id }
											className="selected-page-item"
											style={ {
												marginBottom: '10px',
												padding: '10px',
												border: '1px solid #ddd',
												borderRadius: '4px',
											} }
										>
											<div
												style={ {
													marginBottom: '8px',
												} }
											>
												<strong>
													{ __(
														'Button',
														'fau-elemental'
													) }{ ' ' }
													{ index + 1 }:{ ' ' }
												</strong>
												<span
													dangerouslySetInnerHTML={ {
														__html: String(
															page.title || ''
														),
													} }
												/>
											</div>
											<Button
												isSmall
												isDestructive
												onClick={ () =>
													removeSelectedPage(
														page.id
													)
												}
												aria-label={
													__(
														'Remove page',
														'fau-elemental'
													) +
													': ' +
													String( page.title || '' )
												}
											>
												{ __(
													'Remove',
													'fau-elemental'
												) }
											</Button>
										</div>
									) ) }
								</div>
							</>
						) }

						{ showAllPages && allPages && (
							<div style={ { marginTop: '16px' } }>
								<p>
									<strong>
										{ __(
											'Displaying all pages:',
											'fau-elemental'
										) }
									</strong>
								</p>
								<div
									style={ {
										maxHeight: '200px',
										overflowY: 'auto',
										border: '1px solid #ddd',
										borderRadius: '4px',
										padding: '8px',
									} }
								>
									{ allPages.map( ( page, index ) => (
										<div
											key={ page.id }
											style={ {
												marginBottom: '4px',
												fontSize: '13px',
												color: '#666',
											} }
										>
											{ index + 1 }.{ ' ' }
											<span
												dangerouslySetInnerHTML={ {
													__html: String(
														page.title?.rendered ||
															''
													),
												} }
											/>
										</div>
									) ) }
								</div>
								{ allPages.length > numberOfButtons && (
									<p
										style={ {
											fontSize: '13px',
											color: '#d63638',
											marginTop: '8px',
										} }
									>
										{ sprintf(
											// translators: %s: number of pages
											__(
												'Note: Only the first %s pages will be displayed based on your "Number of Buttons" setting.',
												'fau-elemental'
											),
											numberOfButtons
										) }
									</p>
								) }
							</div>
						) }
					</PanelBody>
				) }
			</InspectorControls>

			<div { ...blockProps }>
				{ showHeadline && headline && (
					<h2 className="fau-big-button-teaser-group__headline">
						{ headline }
					</h2>
				) }

				{ showTeaserText && teaserText && (
					<div className="fau-big-button-teaser-group__teaser-text">
						{ teaserText }
					</div>
				) }

				<div className="fau-big-button-teaser-group__buttons">
					{ previewItems.map( ( item, index ) => {
						// Determine button classes based on mode
						let buttonClasses =
							'fau-big-button-teaser-group__button';
						if (
							showFaculties &&
							item.facultyColor &&
							item.facultyColor !== 'default'
						) {
							buttonClasses += ` fau-big-button-teaser-group__button--${ item.facultyColor }`;
						}

						// Get title and excerpt based on item type
						const title = showFaculties
							? item.title
							: item.title?.rendered || item.title;
						const rawExcerpt = showFaculties
							? item.description
							: item.excerpt?.rendered || item.excerpt;
						// Convert excerpt to string and handle empty/object cases
						const excerpt =
							rawExcerpt &&
							typeof rawExcerpt === 'string' &&
							rawExcerpt.trim() !== ''
								? rawExcerpt
								: '';
						const url = showFaculties ? item.url : item.link;

						return (
							<div
								key={
									showFaculties ? item.id || index : item.id
								}
								className={ buttonClasses }
							>
								<a
									href={ url || '#preview' }
									className="fau-big-button-teaser-group__button-link"
								>
									<h3 className="fau-big-button-teaser-group__button-title">
										{ title ? (
											<span
												dangerouslySetInnerHTML={ {
													__html: String( title ),
												} }
											/>
										) : (
											<span style={ { color: '#999' } }>
												{ __(
													'Enter title…',
													'fau-elemental'
												) }
											</span>
										) }
									</h3>
									{ excerpt && (
										<p className="fau-big-button-teaser-group__button-text">
											<span
												dangerouslySetInnerHTML={ {
													__html:
														excerpt
															.replace(
																/<[^>]*>/g,
																''
															)
															.substring(
																0,
																120
															) +
														( excerpt.length > 120
															? '...'
															: '' ),
												} }
											/>
										</p>
									) }
									{ ! excerpt && (
										<p
											className="fau-big-button-teaser-group__button-text"
											style={ { color: '#999' } }
										>
											{ showFaculties
												? __(
														'Add description…',
														'fau-elemental'
												  )
												: __(
														'No excerpt available',
														'fau-elemental'
												  ) }
										</p>
									) }
									<span className="arrow-link"></span>
								</a>
							</div>
						);
					} ) }

					{ /* Show placeholder buttons for empty slots */ }
					{ Array.from( {
						length: numberOfButtons - previewItems.length,
					} ).map( ( _, index ) => (
						<div
							key={ `placeholder-${ index }` }
							className="fau-big-button-teaser-group__button fau-big-button-teaser-group__button--placeholder"
						>
							<a
								href="#preview"
								className="fau-big-button-teaser-group__button-link"
							>
								<h3 className="fau-big-button-teaser-group__button-title">
									{ showFaculties
										? __(
												'Add faculty item…',
												'fau-elemental'
										  )
										: __(
												'Select a page…',
												'fau-elemental'
										  ) }
								</h3>
								<p className="fau-big-button-teaser-group__button-text">
									{ showFaculties
										? __(
												'Use the Faculty Items panel to add content.',
												'fau-elemental'
										  )
										: __(
												'Use the Page Selection panel to choose pages for your button teasers.',
												'fau-elemental'
										  ) }
								</p>
								<span className="arrow-link"></span>
							</a>
						</div>
					) ) }
				</div>
			</div>
		</>
	);
}
