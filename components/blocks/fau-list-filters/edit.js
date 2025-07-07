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
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
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
		customBlockId,
	} = attributes;

	const [ filterFieldsState, setFilterFieldsState ] = useState(
		filterFields || []
	);

	const blockProps = useBlockProps( {
		className: `fau-list-filters grid-width-${ gridWidth }`,
	} );

	const categories = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', 'category', {
			per_page: -1,
		} );
	}, [] );

	const tags = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', 'post_tag', {
			per_page: -1,
		} );
	}, [] );

	const authors = useSelect( ( select ) => {
		return select( 'core' ).getUsers( { who: 'authors' } );
	}, [] );

	useEffect( () => {
		if ( ! customBlockId ) {
			const blockId = `fau-list-filters-${ Date.now() }`;
			setAttributes( { customBlockId: blockId } );
		}
	}, [ customBlockId, setAttributes ] );

	const addFilterField = ( filterType = 'custom' ) => {
		let newField;
		
		if ( filterType === 'custom' ) {
			newField = {
				name: `filter_${ filterFieldsState.length + 1 }`,
				label: __( 'New Filter', 'fau-elemental' ),
				type: 'custom',
				options: [
					{ label: __( 'Option 1', 'fau-elemental' ), value: 'option1' },
					{ label: __( 'Option 2', 'fau-elemental' ), value: 'option2' },
				],
			};
		} else {
			// WordPress filter types
			const filterData = filterType === 'categories' ? categories : 
							  filterType === 'tags' ? tags : 
							  filterType === 'authors' ? authors : [];
			
			newField = {
				name: filterType,
				label: filterType.charAt( 0 ).toUpperCase() + filterType.slice( 1 ),
				type: filterType,
				options: filterData ? filterData.map( item => ({
					label: item.name || item.title?.rendered || item.display_name,
					value: item.id || item.slug
				})) : [],
			};
		}
		
		const updatedFields = [ ...filterFieldsState, newField ];
		setFilterFieldsState( updatedFields );
		setAttributes( { filterFields: updatedFields } );
	};

	const updateFilterField = ( index, field, value ) => {
		const updatedFields = [ ...filterFieldsState ];
		updatedFields[ index ][ field ] = value;
		setFilterFieldsState( updatedFields );
		setAttributes( { filterFields: updatedFields } );
	};

	const removeFilterField = ( index ) => {
		const updatedFields = filterFieldsState.filter(
			( _, i ) => i !== index
		);
		setFilterFieldsState( updatedFields );
		setAttributes( { filterFields: updatedFields } );
	};

	const addSortOption = () => {
		const newOption = {
			value: 'date',
			label: __( 'Date', 'fau-elemental' ),
		};
		const updatedOptions = [ ...sortOptions, newOption ];
		setAttributes( { sortOptions: updatedOptions } );
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

	const updateAvailableView = ( index, value ) => {
		const updatedViews = [ ...availableViews ];
		updatedViews[ index ] = value;
		setAttributes( { availableViews: updatedViews } );
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
							{ filterFieldsState
								.slice(
									0,
									showMoreFiltersButton
										? 3
										: filterFieldsState.length
								)
								.map( ( field, index ) => (
									<div key={ index } className="filter-field">
										<label
											className="filter-label"
											htmlFor={ `filter-field-${ index }` }
										>
											{ field.name }
										</label>
										<select
											id={ `filter-field-${ index }` }
											className="filter-select"
											disabled
										>
											<option>
												{ __( 'All', 'fau-elemental' ) }{ ' ' }
												{ field.name }
											</option>
											{ ( field.options || [] )
												.slice( 0, 5 )
												.map( ( option, optIndex ) => (
													<option
														key={ optIndex }
														value={ option.value }
													>
														{ option.label }
													</option>
												) ) }
											{ ( field.options && field.options.length > 5 ) && (
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
							{ Object.entries( {
								categories,
								tags,
								authors,
							} ).map( ( [ filterKey, filterData ] ) => {
								const isAlreadyAdded = filterFieldsState.some(
									( field ) => field.type === filterKey
								);
								if (
									isAlreadyAdded ||
									!filterData ||
									filterData.length === 0
								) {
									return null;
								}

								const shouldShow =
									! showMoreFiltersButton ||
									filterFieldsState.length < 3;
								if ( ! shouldShow ) {
									return null;
								}

								return (
									<div
										key={ filterKey }
										className="filter-field filter-field--available"
									>
										<label
											className="filter-label"
											htmlFor={ `filter-available-${ filterKey }` }
										>
											{ filterKey
												.charAt( 0 )
												.toUpperCase() +
												filterKey.slice( 1 ) }
										</label>
										<select
											id={ `filter-available-${ filterKey }` }
											className="filter-select"
											disabled
										>
											<option>
												{ __( 'All', 'fau-elemental' ) }{ ' ' }
												{ filterKey
													.charAt( 0 )
													.toUpperCase() +
													filterKey.slice( 1 ) }
											</option>
											{ ( filterData || [] )
												.slice( 0, 3 )
												.map( ( option, optIndex ) => (
													<option
														key={ optIndex }
														value={ option.value }
													>
														{ option.label }
													</option>
												) ) }
											{ ( filterData && filterData.length > 3 ) && (
												<option disabled>
													...and{ ' ' }
													{ filterData.length - 3 }{ ' ' }
													more
												</option>
											) }
										</select>
									</div>
								);
							} ) }

							{ showMoreFiltersButton &&
								( filterFieldsState.length > 3 ||
									Object.keys( { categories, tags, authors } )
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
								{ availableViews.map( ( view, index ) => (
									<button
										key={ index }
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
								<label
									className="sort-label"
									htmlFor="sort-preview-select"
								>
									{ __( 'Sort by:', 'fau-elemental' ) }
								</label>
								<select
									id="sort-preview-select"
									className="sort-select"
									disabled
								>
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

								{ Object.entries( {
									categories,
									tags,
									authors,
								} ).map( ( [ filterKey, filterData ] ) => {
									const isAlreadyAdded =
										filterFieldsState.some(
											( field ) =>
												field.type === filterKey
										);
									const hasOptions =
										filterData && filterData.length > 0;

									return (
										<div
											key={ filterKey }
											style={ {
												display: 'flex',
												justifyContent: 'space-between',
												alignItems: 'center',
												marginBottom: '10px',
												padding: '10px',
												border: '1px solid #ddd',
												borderRadius: '4px',
												backgroundColor: isAlreadyAdded
													? '#f0f0f0'
													: 'transparent',
											} }
										>
											<div>
												<strong>
													{ filterKey
														.charAt( 0 )
														.toUpperCase() +
														filterKey.slice( 1 ) }
												</strong>
												<div
													style={ {
														fontSize: '12px',
														color: '#666',
													} }
												>
													{ hasOptions
														? `${
																filterData.length
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
													addFilterField( filterKey )
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
								} ) }
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
								<Button
									isPrimary
									onClick={ addFilterField }
								>
									{ __(
										'Add Custom Filter',
										'fau-elemental'
									) }
								</Button>
							</div>

							{ filterFieldsState.length > 0 && (
								<div style={ { marginTop: '20px' } }>
									<Text style={ { fontWeight: 'bold' } }>
										{ __(
											'Current Filter Fields',
											'fau-elemental'
										) }
									</Text>
									{ filterFieldsState.map(
										( field, index ) => (
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
																fontSize:
																	'12px',
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
															{
																( field.options && field.options.length ) || 0
															}{ ' ' }
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
										)
									) }
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
							].map( ( view, index ) => (
								<CheckboxControl
									key={ view.value }
									label={ view.label }
									checked={ availableViews.includes(
										view.value
									) }
									onChange={ () =>
										updateAvailableView( index, view.value )
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
