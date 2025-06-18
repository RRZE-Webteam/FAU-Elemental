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
	CheckboxControl,
	__experimentalText as Text,
	__experimentalSpacer as Spacer,
	Notice,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

const Edit = ( props ) => {
	const { attributes, setAttributes, clientId } = props;
	const {
		enableSearch,
		searchPlaceholder,
		enableFilters,
		filterFields,
		showMoreFiltersButton,
		enableViewSwitcher,
		availableViews,
		defaultView,
		enableSorting,
		sortOptions,
		defaultSort,
		showResultsCount,
		resultsPerPage,
		gridWidth,
	} = attributes;

	const [ newFilterName, setNewFilterName ] = useState( '' );
	const [ newFilterOptions, setNewFilterOptions ] = useState( '' );
	const [ availableFilterOptions, setAvailableFilterOptions ] = useState(
		{}
	);

	// Get WordPress data for dynamic filters
	const { categories, tags, postTypes, authors } = useSelect( ( select ) => {
		const { getEntityRecords, getPostTypes, getUsers } = select( 'core' );

		return {
			categories:
				getEntityRecords( 'taxonomy', 'category', { per_page: -1 } ) ||
				[],
			tags:
				getEntityRecords( 'taxonomy', 'post_tag', { per_page: -1 } ) ||
				[],
			postTypes: getPostTypes( { per_page: -1 } ) || [],
			authors: getUsers( { who: 'authors', per_page: -1 } ) || [],
		};
	}, [] );

	// Set up available filter options when WordPress data loads
	useEffect( () => {
		const options = {
			categories: {
				label: __( 'Categories', 'fau-elemental' ),
				options: categories.map( ( cat ) => ( {
					value: cat.slug,
					label: `${ cat.name } (${ cat.count })`,
				} ) ),
			},
			tags: {
				label: __( 'Tags', 'fau-elemental' ),
				options: tags.map( ( tag ) => ( {
					value: tag.slug,
					label: `${ tag.name } (${ tag.count })`,
				} ) ),
			},
			post_types: {
				label: __( 'Content Types', 'fau-elemental' ),
				options: Object.values( postTypes )
					.filter(
						( type ) => type.viewable && type.name !== 'attachment'
					)
					.map( ( type ) => ( {
						value: type.name,
						label: type.labels.name,
					} ) ),
			},
			authors: {
				label: __( 'Authors', 'fau-elemental' ),
				options: authors.map( ( author ) => ( {
					value: author.slug,
					label: author.name,
				} ) ),
			},
		};
		setAvailableFilterOptions( options );
	}, [ categories, tags, postTypes, authors ] );

	const blockProps = useBlockProps( {
		className: `fau-list-filters grid-width-${ gridWidth }`,
	} );

	const addFilterField = () => {
		if ( ! newFilterName.trim() ) return;

		const options = newFilterOptions
			.split( '\n' )
			.filter( ( option ) => option.trim() )
			.map( ( option ) => {
				const [ value, label ] = option.split( '|' );
				return {
					value: value?.trim() || option.trim(),
					label: label?.trim() || option.trim(),
				};
			} );

		const newField = {
			name: newFilterName.trim(),
			options: options,
			type: 'custom',
		};

		setAttributes( {
			filterFields: [ ...filterFields, newField ],
		} );

		setNewFilterName( '' );
		setNewFilterOptions( '' );
	};

	const addWordPressFilter = ( filterType ) => {
		const filterData = availableFilterOptions[ filterType ];
		if ( ! filterData || filterData.options.length === 0 ) return;

		const newField = {
			name: filterData.label,
			options: filterData.options,
			type: filterType,
		};

		setAttributes( {
			filterFields: [ ...filterFields, newField ],
		} );
	};

	const removeFilterField = ( index ) => {
		const updatedFields = filterFields.filter( ( _, i ) => i !== index );
		setAttributes( { filterFields: updatedFields } );
	};

	const addSortOption = () => {
		const newOption = { value: '', label: '' };
		setAttributes( {
			sortOptions: [ ...sortOptions, newOption ],
		} );
	};

	const updateSortOption = ( index, field, value ) => {
		const updatedOptions = [ ...sortOptions ];
		updatedOptions[ index ][ field ] = value;
		setAttributes( { sortOptions: updatedOptions } );
	};

	const removeSortOption = ( index ) => {
		const updatedOptions = sortOptions.filter( ( _, i ) => i !== index );
		setAttributes( { sortOptions: updatedOptions } );
	};

	const updateAvailableViews = ( view, isChecked ) => {
		let newViews;
		if ( isChecked ) {
			newViews = [ ...availableViews, view ];
		} else {
			newViews = availableViews.filter( ( v ) => v !== view );
		}

		setAttributes( { availableViews: newViews } );

		// Update default view if current default is no longer available
		if ( ! newViews.includes( defaultView ) && newViews.length > 0 ) {
			setAttributes( { defaultView: newViews[ 0 ] } );
		}
	};

	const renderPreview = () => {
		return (
			<div className="fau-list-filters-preview">
				{ enableSearch && (
					<div className="fau-list-filters__search-section">
						<div className="search-wrapper">
							<input
								type="search"
								className="search-input"
								placeholder={ searchPlaceholder }
								disabled
							/>
							<button
								type="button"
								className="search-clear"
								style={ { display: 'none' } }
							>
								×
							</button>
						</div>
					</div>
				) }

				{ enableFilters && (
					<div className="fau-list-filters__filter-section">
						<div className="filter-controls">
							{ filterFields
								.slice(
									0,
									showMoreFiltersButton
										? 3
										: filterFields.length
								)
								.map( ( field, index ) => (
									<div key={ index } className="filter-field">
										<label className="filter-label">
											{ field.name }
										</label>
										<select
											className="filter-select"
											disabled
										>
											<option>
												{ __( 'All', 'fau-elemental' ) }{ ' ' }
												{ field.name }
											</option>
											{ field.options
												.slice( 0, 5 )
												.map( ( option, optIndex ) => (
													<option
														key={ optIndex }
														value={ option.value }
													>
														{ option.label }
													</option>
												) ) }
											{ field.options.length > 5 && (
												<option disabled>
													...and{ ' ' }
													{ field.options.length - 5 }{ ' ' }
													more
												</option>
											) }
										</select>
									</div>
								) ) }

							{ /* Show available WordPress filters that aren't configured */ }
							{ Object.entries( availableFilterOptions ).map(
								( [ filterKey, filterData ] ) => {
									const isAlreadyAdded = filterFields.some(
										( field ) => field.type === filterKey
									);
									if (
										isAlreadyAdded ||
										filterData.options.length === 0
									)
										return null;

									const shouldShow =
										! showMoreFiltersButton ||
										filterFields.length < 3;
									if ( ! shouldShow ) return null;

									return (
										<div
											key={ filterKey }
											className="filter-field filter-field--available"
										>
											<label className="filter-label">
												{ filterData.label }
											</label>
											<select
												className="filter-select"
												disabled
											>
												<option>
													{ __(
														'All',
														'fau-elemental'
													) }{ ' ' }
													{ filterData.label }
												</option>
												{ filterData.options
													.slice( 0, 3 )
													.map(
														(
															option,
															optIndex
														) => (
															<option
																key={ optIndex }
																value={
																	option.value
																}
															>
																{ option.label }
															</option>
														)
													) }
												{ filterData.options.length >
													3 && (
													<option disabled>
														...and{ ' ' }
														{ filterData.options
															.length - 3 }{ ' ' }
														more
													</option>
												) }
											</select>
										</div>
									);
								}
							) }

							{ showMoreFiltersButton &&
								( filterFields.length > 3 ||
									Object.keys( availableFilterOptions )
										.length > 0 ) && (
									<button
										type="button"
										className="show-more-filters"
										disabled
									>
										{ __(
											'Show more filters',
											'fau-elemental'
										) }
									</button>
								) }
						</div>

						{ /* Active filters preview */ }
						<div
							className="active-filters"
							style={ { display: 'none' } }
						>
							<div className="active-filters__header">
								<span className="active-filters__label">
									{ __( 'Active filters:', 'fau-elemental' ) }
								</span>
							</div>
							<div className="filter-chips">
								<div className="filter-chip">
									<span className="chip-content">
										<span className="chip-name">
											{ __( 'Example', 'fau-elemental' ) }
											:
										</span>
										<span className="chip-value">
											{ __(
												'Filter Value',
												'fau-elemental'
											) }
										</span>
									</span>
									<button
										type="button"
										className="chip-remove"
										disabled
									>
										×
									</button>
								</div>
							</div>
							<button
								type="button"
								className="clear-all-filters"
								disabled
							>
								{ __( 'Clear all', 'fau-elemental' ) }
							</button>
						</div>
					</div>
				) }

				<div className="fau-list-filters__sort-section">
					{ showResultsCount && (
						<div className="results-count">
							<span className="results-text">
								1 to { resultsPerPage } from 100 records
							</span>
						</div>
					) }

					<div className="sort-controls">
						{ enableViewSwitcher && availableViews.length > 1 && (
							<div className="view-switcher">
								{ availableViews.map( ( view ) => (
									<button
										key={ view }
										type="button"
										className={ `view-button ${
											view === defaultView ? 'active' : ''
										}` }
										disabled
										title={
											view.charAt( 0 ).toUpperCase() +
											view.slice( 1 )
										}
									>
										<span
											className={ `view-icon view-icon-${ view }` }
										></span>
										<span className="view-label sr-only">
											{ view.charAt( 0 ).toUpperCase() +
												view.slice( 1 ) }
										</span>
									</button>
								) ) }
							</div>
						) }

						{ enableSorting && sortOptions.length > 0 && (
							<div className="sort-dropdown">
								<label className="sort-label">
									{ __( 'Sort by:', 'fau-elemental' ) }
								</label>
								<select className="sort-select" disabled>
									{ sortOptions.map( ( option, index ) => (
										<option
											key={ index }
											value={ option.value }
										>
											{ option.label }
										</option>
									) ) }
								</select>
							</div>
						) }
					</div>
				</div>
			</div>
		);
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Search Settings', 'fau-elemental' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Enable Search', 'fau-elemental' ) }
						checked={ enableSearch }
						onChange={ ( value ) =>
							setAttributes( { enableSearch: value } )
						}
					/>
					{ enableSearch && (
						<TextControl
							label={ __(
								'Search Placeholder',
								'fau-elemental'
							) }
							value={ searchPlaceholder }
							onChange={ ( value ) =>
								setAttributes( { searchPlaceholder: value } )
							}
						/>
					) }
				</PanelBody>

				<PanelBody
					title={ __( 'Filter Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Enable Filters', 'fau-elemental' ) }
						checked={ enableFilters }
						onChange={ ( value ) =>
							setAttributes( { enableFilters: value } )
						}
					/>

					{ enableFilters && (
						<>
							<ToggleControl
								label={ __(
									'Show More Filters Button',
									'fau-elemental'
								) }
								checked={ showMoreFiltersButton }
								onChange={ ( value ) =>
									setAttributes( {
										showMoreFiltersButton: value,
									} )
								}
								help={ __(
									'Hide filters after the first 3 and show a "Show more" button',
									'fau-elemental'
								) }
							/>

							<Spacer marginTop={ 4 } marginBottom={ 4 } />

							{ /* WordPress Content Filters */ }
							<div style={ { marginBottom: '20px' } }>
								<Text
									style={ {
										fontWeight: 'bold',
										marginBottom: '10px',
									} }
								>
									{ __(
										'Add WordPress Content Filters',
										'fau-elemental'
									) }
								</Text>
								<Text
									variant="muted"
									style={ { marginBottom: '15px' } }
								>
									{ __(
										'These filters are automatically populated from your WordPress content.',
										'fau-elemental'
									) }
								</Text>

								{ Object.entries( availableFilterOptions ).map(
									( [ filterKey, filterData ] ) => {
										const isAlreadyAdded =
											filterFields.some(
												( field ) =>
													field.type === filterKey
											);
										const hasOptions =
											filterData.options &&
											filterData.options.length > 0;

										return (
											<div
												key={ filterKey }
												style={ {
													display: 'flex',
													justifyContent:
														'space-between',
													alignItems: 'center',
													marginBottom: '10px',
													padding: '10px',
													border: '1px solid #ddd',
													borderRadius: '4px',
													backgroundColor:
														isAlreadyAdded
															? '#f0f0f0'
															: 'transparent',
												} }
											>
												<div>
													<strong>
														{ filterData.label }
													</strong>
													<div
														style={ {
															fontSize: '12px',
															color: '#666',
														} }
													>
														{ hasOptions
															? `${
																	filterData
																		.options
																		.length
															  } ${ __(
																	'options available',
																	'fau-elemental'
															  ) }`
															: __(
																	'No options available',
																	'fau-elemental'
															  ) }
													</div>
												</div>
												<Button
													isSecondary
													isSmall
													onClick={ () =>
														addWordPressFilter(
															filterKey
														)
													}
													disabled={
														isAlreadyAdded ||
														! hasOptions
													}
												>
													{ isAlreadyAdded
														? __(
																'Added',
																'fau-elemental'
														  )
														: __(
																'Add Filter',
																'fau-elemental'
														  ) }
												</Button>
											</div>
										);
									}
								) }
							</div>

							<Spacer marginTop={ 4 } marginBottom={ 4 } />

							{ /* Custom Filter Fields */ }
							<div style={ { marginTop: '20px' } }>
								<Text style={ { fontWeight: 'bold' } }>
									{ __(
										'Add Custom Filter Field',
										'fau-elemental'
									) }
								</Text>
								<Text
									variant="muted"
									style={ { marginBottom: '15px' } }
								>
									{ __(
										'Create custom filters with your own options.',
										'fau-elemental'
									) }
								</Text>
								<TextControl
									label={ __(
										'Filter Name',
										'fau-elemental'
									) }
									value={ newFilterName }
									onChange={ setNewFilterName }
								/>
								<TextareaControl
									label={ __(
										'Filter Options',
										'fau-elemental'
									) }
									value={ newFilterOptions }
									onChange={ setNewFilterOptions }
									help={ __(
										'Enter one option per line. Use format: value|label (e.g., "news|News Articles")',
										'fau-elemental'
									) }
									rows={ 4 }
								/>
								<Button
									isPrimary
									onClick={ addFilterField }
									disabled={ ! newFilterName.trim() }
								>
									{ __(
										'Add Custom Filter',
										'fau-elemental'
									) }
								</Button>
							</div>

							{ filterFields.length > 0 && (
								<div style={ { marginTop: '20px' } }>
									<Text style={ { fontWeight: 'bold' } }>
										{ __(
											'Current Filter Fields',
											'fau-elemental'
										) }
									</Text>
									{ filterFields.map( ( field, index ) => (
										<div
											key={ index }
											style={ {
												border: '1px solid #ddd',
												padding: '10px',
												marginBottom: '10px',
												borderRadius: '4px',
											} }
										>
											<div
												style={ {
													display: 'flex',
													justifyContent:
														'space-between',
													alignItems: 'center',
												} }
											>
												<div>
													<strong>
														{ field.name }
													</strong>
													<div
														style={ {
															fontSize: '12px',
															color: '#666',
														} }
													>
														{ field.type ===
														'custom'
															? __(
																	'Custom filter',
																	'fau-elemental'
															  )
															: __(
																	'WordPress filter',
																	'fau-elemental'
															  ) }{ ' ' }
														•
														{ field.options.length }{ ' ' }
														{ __(
															'options',
															'fau-elemental'
														) }
													</div>
												</div>
												<Button
													isDestructive
													isSmall
													onClick={ () =>
														removeFilterField(
															index
														)
													}
												>
													{ __(
														'Remove',
														'fau-elemental'
													) }
												</Button>
											</div>
										</div>
									) ) }
								</div>
							) }
						</>
					) }
				</PanelBody>

				<PanelBody
					title={ __( 'View & Sort Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Enable View Switcher', 'fau-elemental' ) }
						checked={ enableViewSwitcher }
						onChange={ ( value ) =>
							setAttributes( { enableViewSwitcher: value } )
						}
					/>

					{ enableViewSwitcher && (
						<>
							<Text
								style={ {
									fontWeight: 'bold',
									marginTop: '15px',
									marginBottom: '10px',
								} }
							>
								{ __( 'Available Views', 'fau-elemental' ) }
							</Text>
							{ [
								{
									value: 'cards',
									label: __( 'Cards', 'fau-elemental' ),
								},
								{
									value: 'table',
									label: __( 'Table', 'fau-elemental' ),
								},
								{
									value: 'list',
									label: __( 'List', 'fau-elemental' ),
								},
							].map( ( view ) => (
								<CheckboxControl
									key={ view.value }
									label={ view.label }
									checked={ availableViews.includes(
										view.value
									) }
									onChange={ ( isChecked ) =>
										updateAvailableViews(
											view.value,
											isChecked
										)
									}
								/>
							) ) }

							{ availableViews.length > 0 && (
								<SelectControl
									label={ __(
										'Default View',
										'fau-elemental'
									) }
									value={ defaultView }
									options={ availableViews.map(
										( view ) => ( {
											label:
												view.charAt( 0 ).toUpperCase() +
												view.slice( 1 ),
											value: view,
										} )
									) }
									onChange={ ( value ) =>
										setAttributes( { defaultView: value } )
									}
								/>
							) }
						</>
					) }

					<ToggleControl
						label={ __( 'Enable Sorting', 'fau-elemental' ) }
						checked={ enableSorting }
						onChange={ ( value ) =>
							setAttributes( { enableSorting: value } )
						}
					/>

					{ enableSorting && (
						<div style={ { marginTop: '20px' } }>
							<Text style={ { fontWeight: 'bold' } }>
								{ __( 'Sort Options', 'fau-elemental' ) }
							</Text>
							{ sortOptions.map( ( option, index ) => (
								<div
									key={ index }
									style={ {
										border: '1px solid #ddd',
										padding: '10px',
										marginBottom: '10px',
										borderRadius: '4px',
									} }
								>
									<TextControl
										label={ __( 'Value', 'fau-elemental' ) }
										value={ option.value }
										onChange={ ( value ) =>
											updateSortOption(
												index,
												'value',
												value
											)
										}
									/>
									<TextControl
										label={ __( 'Label', 'fau-elemental' ) }
										value={ option.label }
										onChange={ ( value ) =>
											updateSortOption(
												index,
												'label',
												value
											)
										}
									/>
									<Button
										isDestructive
										isSmall
										onClick={ () =>
											removeSortOption( index )
										}
									>
										{ __( 'Remove', 'fau-elemental' ) }
									</Button>
								</div>
							) ) }
							<Button isSecondary onClick={ addSortOption }>
								{ __( 'Add Sort Option', 'fau-elemental' ) }
							</Button>
						</div>
					) }

					{ sortOptions.length > 0 && (
						<SelectControl
							label={ __( 'Default Sort', 'fau-elemental' ) }
							value={ defaultSort }
							options={ sortOptions.map( ( option ) => ( {
								label: option.label,
								value: option.value,
							} ) ) }
							onChange={ ( value ) =>
								setAttributes( { defaultSort: value } )
							}
						/>
					) }
				</PanelBody>

				<PanelBody
					title={ __( 'Display Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show Results Count', 'fau-elemental' ) }
						checked={ showResultsCount }
						onChange={ ( value ) =>
							setAttributes( { showResultsCount: value } )
						}
					/>

					<RangeControl
						label={ __( 'Results Per Page', 'fau-elemental' ) }
						value={ resultsPerPage }
						onChange={ ( value ) =>
							setAttributes( { resultsPerPage: value } )
						}
						min={ 5 }
						max={ 50 }
						step={ 5 }
					/>

					<SelectControl
						label={ __( 'Grid Width', 'fau-elemental' ) }
						value={ gridWidth }
						options={ [
							{
								label: __( '8 Columns', 'fau-elemental' ),
								value: '8',
							},
							{
								label: __( '10 Columns', 'fau-elemental' ),
								value: '10',
							},
							{
								label: __( '12 Columns', 'fau-elemental' ),
								value: '12',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { gridWidth: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="fau-list-filters-editor">
					<h3>{ __( 'FAU List Filters', 'fau-elemental' ) }</h3>
					<p>
						{ __(
							'This block will render interactive filters for lists on the frontend. Configure the settings in the sidebar to customize the filtering options.',
							'fau-elemental'
						) }
					</p>

					{ renderPreview() }
				</div>
			</div>
		</>
	);
};

export default Edit;
