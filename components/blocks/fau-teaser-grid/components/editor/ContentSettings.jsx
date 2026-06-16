import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	FormTokenField,
} from '@wordpress/components';

export const ContentSettings = ( {
	variant,
	selectedCategory,
	selectedTags = [],
	postsPerPage,
	orderBy,
	order,
	setAttributes,
	categoryOptions,
	categories,
	tags = [],
} ) => {
	const sortingOptions = [
		{ label: __( 'Date', 'fau-elemental' ), value: 'date' },
		{ label: __( 'Title', 'fau-elemental' ), value: 'title' },
	];

	const orderOptions = [
		{ label: __( 'Ascending', 'fau-elemental' ), value: 'ASC' },
		{ label: __( 'Descending', 'fau-elemental' ), value: 'DESC' },
	];

	const tagValues = selectedTags
		.map( ( tagId ) => tags.find( ( tag ) => tag.id === tagId )?.name )
		.filter( Boolean );
	const tagSuggestions = tags.map( ( tag ) => tag.name );
	const handleTagChange = ( values ) => {
		const nextTags = values
			.map(
				( value ) =>
					tags.find( ( tag ) => tag.name === value )?.id || null
			)
			.filter( Boolean );

		setAttributes( {
			selectedTags: [ ...new Set( nextTags ) ],
			currentPage: 1,
		} );
	};

	return (
		<PanelBody title={ __( 'Content Settings', 'fau-elemental' ) }>
			{ variant === 'post' && categories.length > 0 && (
				<SelectControl
					label={ __( 'Category', 'fau-elemental' ) }
					value={ selectedCategory }
					options={ categoryOptions }
					onChange={ ( value ) =>
						setAttributes( {
							selectedCategory: parseInt( value ),
							currentPage: 1,
						} )
					}
					help={ __(
						'Select a category to filter posts.',
						'fau-elemental'
					) }
					__nextHasNoMarginBottom={ true }
					__next40pxDefaultSize={ true }
				/>
			) }

			{ variant === 'post' && tags.length > 0 && (
				<FormTokenField
					label={ __( 'Tags', 'fau-elemental' ) }
					value={ tagValues }
					suggestions={ tagSuggestions }
					onChange={ handleTagChange }
					help={ __(
						'Select one or more tags to filter content.',
						'fau-elemental'
					) }
					__nextHasNoMarginBottom={ true }
					__next40pxDefaultSize={ true }
				/>
			) }

			<RangeControl
				label={ __( 'Posts Per Page', 'fau-elemental' ) }
				value={ postsPerPage }
				onChange={ ( value ) =>
					setAttributes( {
						postsPerPage: value,
						currentPage: 1,
					} )
				}
				min={ 1 }
				max={ 12 }
				help={ __(
					'Set the maximum number of posts to display per page.',
					'fau-elemental'
				) }
				__nextHasNoMarginBottom={ true }
				__next40pxDefaultSize={ true }
			/>

			<SelectControl
				label={ __( 'Order By', 'fau-elemental' ) }
				value={ orderBy }
				options={ sortingOptions }
				onChange={ ( value ) => setAttributes( { orderBy: value } ) }
				__nextHasNoMarginBottom={ true }
				__next40pxDefaultSize={ true }
			/>

			<SelectControl
				label={ __( 'Order', 'fau-elemental' ) }
				value={ order.toUpperCase() }
				options={ orderOptions }
				onChange={ ( value ) => setAttributes( { order: value } ) }
				__nextHasNoMarginBottom={ true }
				__next40pxDefaultSize={ true }
			/>
		</PanelBody>
	);
};
