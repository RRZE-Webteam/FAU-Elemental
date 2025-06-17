/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
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
		roofLine,
		showRoofLine,
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
	} = attributes;

	const [ searchTerm, setSearchTerm ] = useState( '' );

	// Get available pages from WordPress
	const { availablePages, pages, allPages } = useSelect(
		( select ) => {
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
		[ searchTerm, selectedPages, showAllPages ]
	);

	const blockProps = useBlockProps( {
		className: `fau-big-button-teaser-group fau-big-button-teaser-group--${ teaserSize } fau-big-button-teaser-group--${ variant } ${
			facultyColor !== 'default'
				? `fau-big-button-teaser-group--${ facultyColor }`
				: ''
		}`,
	} );

	// Handle page selection
	const handlePageSelection = ( pageId ) => {
		if ( ! pageId ) return;

		const page = availablePages.find( ( p ) => p.id === pageId );
		if ( ! page ) return;

		// Don't add if already selected
		if ( selectedPages.some( ( p ) => p.id === page.id ) ) return;

		// Don't add if we've reached the maximum number of buttons
		if ( selectedPages.length >= numberOfButtons ) return;

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
		setAttributes( {
			numberOfButtons: newNumber,
			selectedPages: updatedSelectedPages,
		} );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Content Settings', 'fau-elemental' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Show Roof Line', 'fau-elemental' ) }
						checked={ showRoofLine }
						onChange={ ( value ) =>
							setAttributes( { showRoofLine: value } )
						}
					/>
					{ showRoofLine && (
						<TextControl
							label={ __( 'Roof Line', 'fau-elemental' ) }
							value={ roofLine }
							onChange={ ( value ) =>
								setAttributes( { roofLine: value } )
							}
						/>
					) }

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
					<SelectControl
						label={ __( 'Faculty Color', 'fau-elemental' ) }
						value={ facultyColor }
						options={ [
							{
								label: __( 'Default', 'fau-elemental' ),
								value: 'default',
							},
							{
								label: __( 'Philosophy', 'fau-elemental' ),
								value: 'phil',
							},
							{
								label: __( 'Law & Economics', 'fau-elemental' ),
								value: 'rw',
							},
							{
								label: __( 'Medicine', 'fau-elemental' ),
								value: 'med',
							},
							{
								label: __( 'Sciences', 'fau-elemental' ),
								value: 'nat',
							},
							{
								label: __( 'Technology', 'fau-elemental' ),
								value: 'tf',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { facultyColor: value } )
						}
					/>

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
						help={
							selectedPages.length >= numberOfButtons
								? __(
										'Maximum number of pages selected for current button count.',
										'fau-elemental'
								  )
								: __(
										'Select the number of buttons to display.',
										'fau-elemental'
								  )
						}
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Page Selection', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show All Pages', 'fau-elemental' ) }
						checked={ showAllPages }
						onChange={ ( value ) =>
							setAttributes( { showAllPages: value } )
						}
						help={
							showAllPages
								? __(
										'All published pages will be displayed as buttons.',
										'fau-elemental'
								  )
								: __(
										'Manually select specific pages to display as buttons.',
										'fau-elemental'
								  )
						}
					/>

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
										? availablePages.map( ( page ) => ( {
												value: page.id,
												label: page.title.rendered,
										  } ) )
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
										<div style={ { marginBottom: '8px' } }>
											<strong>
												{ __(
													'Button',
													'fau-elemental'
												) }{ ' ' }
												{ index + 1 }:{ ' ' }
											</strong>
											<span
												dangerouslySetInnerHTML={ {
													__html: page.title,
												} }
											/>
										</div>
										<Button
											isSmall
											isDestructive
											onClick={ () =>
												removeSelectedPage( page.id )
											}
											aria-label={
												__(
													'Remove page',
													'fau-elemental'
												) +
												': ' +
												page.title
											}
										>
											{ __( 'Remove', 'fau-elemental' ) }
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
												__html: page.title.rendered,
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
									{ __(
										'Note: Only the first ',
										'fau-elemental'
									) +
										numberOfButtons +
										__(
											' pages will be displayed based on your "Number of Buttons" setting.',
											'fau-elemental'
										) }
								</p>
							) }
						</div>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ showRoofLine && roofLine && (
					<div className="fau-big-button-teaser-group__roof-line">
						{ roofLine }
					</div>
				) }

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
					{ pages &&
						pages
							.slice( 0, numberOfButtons )
							.map( ( page, index ) => (
								<div
									key={ page.id }
									className="fau-big-button-teaser-group__button"
								>
									<a
										href={ page.link }
										className="fau-big-button-teaser-group__button-link"
									>
										<h3 className="fau-big-button-teaser-group__button-title">
											<span
												dangerouslySetInnerHTML={ {
													__html: page.title.rendered,
												} }
											/>
										</h3>
										{ page.excerpt &&
											page.excerpt.rendered && (
												<p className="fau-big-button-teaser-group__button-text">
													<span
														dangerouslySetInnerHTML={ {
															__html:
																page.excerpt.rendered
																	.replace(
																		/<[^>]*>/g,
																		''
																	)
																	.substring(
																		0,
																		120
																	) + '...',
														} }
													/>
												</p>
											) }
										<span className="arrow-link"></span>
									</a>
								</div>
							) ) }

					{ /* Show placeholder buttons for empty slots */ }
					{ Array.from( {
						length: numberOfButtons - ( pages ? pages.length : 0 ),
					} ).map( ( _, index ) => (
						<div
							key={ `placeholder-${ index }` }
							className="fau-big-button-teaser-group__button fau-big-button-teaser-group__button--placeholder"
						>
							<a
								href="#"
								className="fau-big-button-teaser-group__button-link"
							>
								<h3 className="fau-big-button-teaser-group__button-title">
									{ __(
										'Select a page...',
										'fau-elemental'
									) }
								</h3>
								<p className="fau-big-button-teaser-group__button-text">
									{ __(
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
