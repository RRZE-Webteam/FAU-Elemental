/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	SelectControl,
	Button,
} from '@wordpress/components';
import { useEffect, useCallback, useMemo } from '@wordpress/element';
import { v4 as uuidv4 } from 'uuid';

/**
 * Helper function to trim text by characters while respecting word boundaries
 *
 * @param {string} text     The text to trim
 * @param {number} maxChars Maximum number of characters
 * @param {string} more     Trailing text
 * @return {string} Trimmed text
 */
function trimTextSmart( text, maxChars = 80, more = '...' ) {
	if ( ! text || typeof text !== 'string' ) {
		return '';
	}

	// Remove HTML tags and trim
	const cleanText = text.replace( /<[^>]*>/g, '' ).trim();

	// If text is already short enough, return as-is
	if ( cleanText.length <= maxChars ) {
		return cleanText;
	}

	// Find the last space before the character limit
	const trimmed = cleanText.substring( 0, maxChars );
	const lastSpaceIndex = trimmed.lastIndexOf( ' ' );

	// If there's a space, cut at word boundary; otherwise cut at character limit
	const result =
		lastSpaceIndex > 0 ? trimmed.substring( 0, lastSpaceIndex ) : trimmed;

	return result + more;
}



/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { teaserSize, variant, items } = attributes;

	// Faculty color options for individual items
	const facultyColorOptions = [
		{ label: __( 'Default', 'fau-elemental' ), value: 'default' },
		{ label: __( 'Philosophy', 'fau-elemental' ), value: 'phil' },
		{ label: __( 'Law & Economics', 'fau-elemental' ), value: 'rw' },
		{ label: __( 'Medicine', 'fau-elemental' ), value: 'med' },
		{ label: __( 'Sciences', 'fau-elemental' ), value: 'nat' },
		{ label: __( 'Technology', 'fau-elemental' ), value: 'tf' },
	];

	const isFauDe =
		typeof window !== 'undefined' &&
		window.fauElemental &&
		window.fauElemental.websiteType === 'fau';
	const isFacultyWebsite =
		typeof window !== 'undefined' &&
		window.fauElemental &&
		window.fauElemental.websiteType === 'faculty';
	const facultyType =
		typeof window !== 'undefined' &&
		window.fauElemental &&
		window.fauElemental.facultyType;

	// Create a unique identifier for the block to force re-renders
	const blockId = useMemo( () => uuidv4(), [] );

	const blockProps = useBlockProps( {
		className: `fau-big-button-teaser-group fau-big-button-teaser-group--${ teaserSize } fau-big-button-teaser-group--${ variant } fau-big-button-teaser-group--faculty-showcase`,
		'data-block-id': blockId,
	} );

	// Items Management
	const addItem = useCallback( () => {
		const newItem = {
			id: uuidv4(),
			title: '',
			description: '',
			url: '',
			facultyColor: 'default',
		};

		setAttributes( {
			items: [ ...items, newItem ],
		} );
	}, [ items, setAttributes ] );

	const updateItem = useCallback(
		( index, field, value ) => {
			const updatedItems = items.map( ( item, i ) => {
				if ( i === index ) {
					return {
						...item,
						[ field ]: value,
					};
				}
				return item;
			} );
			setAttributes( { items: updatedItems } );
		},
		[ items, setAttributes ]
	);

	const removeItem = useCallback(
		( index ) => {
			// Don't allow removing the last item
			if ( items.length <= 1 ) {
				return;
			}

			const updatedItems = items.filter( ( _, i ) => i !== index );
			setAttributes( { items: updatedItems } );
		},
		[ items, setAttributes ]
	);

	// Ensure all items have unique IDs and at least one item exists
	const ensureValidItems = useCallback( () => {
		let needsUpdate = false;
		let updatedItems = [ ...items ];

		// Ensure we have at least one item
		if ( updatedItems.length === 0 ) {
			updatedItems = [
				{
					id: uuidv4(),
					title: '',
					description: '',
					url: '',
					facultyColor: 'default',
				},
			];
			needsUpdate = true;
		}

		// Ensure all items have unique IDs
		updatedItems = updatedItems.map( ( item ) => {
			if ( ! item.id ) {
				needsUpdate = true;
				return {
					...item,
					id: uuidv4(),
				};
			}
			return item;
		} );

		if ( needsUpdate ) {
			setAttributes( { items: updatedItems } );
		}
	}, [ items, setAttributes ] );

	// Run once on component mount to ensure valid items
	useEffect( () => {
		ensureValidItems();
	}, [ ensureValidItems ] );

	// Get items for preview
	const previewItems = items;

	// Helper function to get the effective faculty color for an item
	const getEffectiveFacultyColor = ( item ) => {
		// For fau.de websites, use the individual item color
		if ( isFauDe ) {
			return item.facultyColor && item.facultyColor !== 'default'
				? item.facultyColor
				: null;
		}
		// For faculty websites, use the website's faculty type
		if ( isFacultyWebsite && facultyType ) {
			return facultyType;
		}
		// For other website types (chair, other, cooperation), no faculty color
		return null;
	};

	// Generate faculty color text for faculty websites
	const facultyColorText = isFacultyWebsite
		? sprintf(
				/* translators: %s: faculty type */
				__( 'Using faculty color: %s', 'fau-elemental' ),
				facultyType || __( 'Unknown', 'fau-elemental' )
		  )
		: '';

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Appearance Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
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
				</PanelBody>

				<PanelBody
					title={ __( 'Items', 'fau-elemental' ) }
					initialOpen={ true }
				>
					<div style={ { marginBottom: '16px' } }>
						<Button isPrimary onClick={ addItem }>
							{ __( 'Add Item', 'fau-elemental' ) }
						</Button>
					</div>

					{ items.map( ( item, index ) => {
						const itemTitle =
							item.title ||
							__( 'Untitled Item', 'fau-elemental' );
						const panelTitle = `${ __(
							'Item',
							'fau-elemental'
						) } ${ index + 1 }${
							itemTitle &&
							itemTitle !== __( 'Untitled Item', 'fau-elemental' )
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
										onClick={ () => removeItem( index ) }
										disabled={ items.length <= 1 }
									>
										{ __( 'Remove Item', 'fau-elemental' ) }
									</Button>
								</div>

								<TextControl
									label={ __( 'Title', 'fau-elemental' ) }
									value={ item.title || '' }
									onChange={ ( value ) =>
										updateItem( index, 'title', value )
									}
								/>
								<TextareaControl
									label={ __(
										'Description',
										'fau-elemental'
									) }
									value={ item.description || '' }
									onChange={ ( value ) =>
										updateItem(
											index,
											'description',
											value
										)
									}
									rows={ 3 }
								/>
								<TextControl
									label={ __( 'URL', 'fau-elemental' ) }
									value={ item.url || '' }
									onChange={ ( value ) =>
										updateItem( index, 'url', value )
									}
									type="url"
								/>
								{ isFauDe && (
									<SelectControl
										label={ __( 'Color', 'fau-elemental' ) }
										value={ item.facultyColor || 'default' }
										options={ facultyColorOptions }
										onChange={ ( value ) =>
											updateItem(
												index,
												'facultyColor',
												value
											)
										}
									/>
								) }
								{ isFacultyWebsite && (
									<p className="fau-big-button-faculty-info">
										{ facultyColorText }
									</p>
								) }
							</PanelBody>
						);
					} ) }

					{ items.length === 0 && (
						<p className="fau-big-button-empty-info">
							{ __(
								'No items added yet. Click "Add Item" to get started.',
								'fau-elemental'
							) }
						</p>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="fau-big-button-teaser-group__buttons">
					{ previewItems.map( ( item, index ) => {
						// Determine button classes
						let buttonClasses =
							'fau-big-button-teaser-group__button';

						// Get the effective faculty color for this item
						const effectiveFacultyColor =
							getEffectiveFacultyColor( item );
						if ( effectiveFacultyColor ) {
							buttonClasses += ` fau-big-button-teaser-group__button--${ effectiveFacultyColor }`;
						}

						// Get title and excerpt
						const title = item.title;
						const rawExcerpt = item.description;
						// Convert excerpt to string and handle empty/object cases
						const excerpt =
							rawExcerpt &&
							typeof rawExcerpt === 'string' &&
							rawExcerpt.trim() !== ''
								? rawExcerpt
								: '';
						const url = item.url;

						return (
							<div
								key={ item.id || index }
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
											<span className="fau-big-button-title-placeholder">
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
													__html: trimTextSmart(
														excerpt,
														80,
														'...'
													),
												} }
											/>
										</p>
									) }
									{ ! excerpt && (
										<p className="fau-big-button-text-placeholder">
											{ __(
												'Add description…',
												'fau-elemental'
											) }
										</p>
									) }
									<span className="arrow-link"></span>
								</a>
							</div>
						);
					} ) }
				</div>
			</div>
		</>
	);
}
