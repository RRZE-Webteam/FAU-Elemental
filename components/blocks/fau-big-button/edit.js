/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	SelectControl,
	Button,
	Tooltip,
	ButtonGroup,
} from '@wordpress/components';
import { useEffect, useMemo } from '@wordpress/element';
import { arrowUp, arrowDown, trash, plus } from '@wordpress/icons';
import { v4 as uuidv4 } from 'uuid';
import { validateUrl } from '../../utils/urlValidation';

/**
 * Helper function to trim text by characters while respecting word boundaries
 *
 * @param {string} text     The text to trim
 * @param {number} maxChars Maximum number of characters
 * @param {string} more     Trailing text
 * @return {string} Trimmed text
 */
function trimTextSmart( text, maxChars = 80, more = '…' ) {
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

// Character limits for the big button block (matching frontend behavior)
const DESCRIPTION_MAX_LENGTH = 80; // Only descriptions are trimmed in frontend

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
		{ label: __( 'FAU', 'fau-elemental' ), value: 'fau' },
		{ label: __( 'Philosophy', 'fau-elemental' ), value: 'phil' },
		{ label: __( 'Law & Economics', 'fau-elemental' ), value: 'rw' },
		{ label: __( 'Medicine', 'fau-elemental' ), value: 'med' },
		{ label: __( 'Sciences', 'fau-elemental' ), value: 'nat' },
		{ label: __( 'Technology', 'fau-elemental' ), value: 'tf' },
	];

	// Safe window access with proper guards
	const siteContext = useMemo( () => {
		if ( typeof window === 'undefined' ) {
			return {};
		}
		return window?.fauElemental ?? {};
	}, [] );

	const isFauDe = siteContext.websiteType === 'fau';
	const isFacultyWebsite = siteContext.websiteType === 'faculty';
	const facultyType = siteContext.facultyType;

	const blockProps = useBlockProps( {
		className: `fau-big-button-teaser-group fau-big-button-teaser-group--${ teaserSize } fau-big-button-teaser-group--${ variant } fau-big-button-teaser-group--faculty-showcase`,
	} );

	// Simple functions for managing items
	const addItem = () => {
		const newItem = {
			id: uuidv4(),
			title: '',
			description: '',
			url: '',
			facultyColor: 'default',
		};
		setAttributes( { items: [ ...items, newItem ] } );
	};

	const updateItem = ( index, field, value ) => {
		const updatedItems = items.map( ( item, i ) => {
			if ( i === index ) {
				return { ...item, [ field ]: value };
			}
			return item;
		} );
		setAttributes( { items: updatedItems } );
	};

	const removeItem = ( index ) => {
		if ( items.length <= 1 ) {
			return;
		}
		const updatedItems = items.filter( ( _, i ) => i !== index );
		setAttributes( { items: updatedItems } );
	};

	const moveItemUp = ( index ) => {
		if ( index === 0 ) {
			return;
		}
		const updatedItems = [ ...items ];
		const temp = updatedItems[ index - 1 ];
		updatedItems[ index - 1 ] = updatedItems[ index ];
		updatedItems[ index ] = temp;
		setAttributes( { items: updatedItems } );
	};

	const moveItemDown = ( index ) => {
		if ( index === items.length - 1 ) {
			return;
		}
		const updatedItems = [ ...items ];
		const temp = updatedItems[ index + 1 ];
		updatedItems[ index + 1 ] = updatedItems[ index ];
		updatedItems[ index ] = temp;
		setAttributes( { items: updatedItems } );
	};

	// Ensure we have at least one item on mount
	useEffect( () => {
		if ( items.length === 0 ) {
			addItem();
		}
	}, [] );

	// Helper function to get the effective faculty color for an item
	const getEffectiveFacultyColor = ( item ) => {
		if ( isFauDe ) {
			return item.facultyColor && item.facultyColor !== 'default'
				? item.facultyColor
				: null;
		}
		if ( isFacultyWebsite && facultyType ) {
			return facultyType;
		}
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

	// URL validation helper

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Appearance Settings', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Buttons Per Row', 'fau-elemental' ) }
						value={ teaserSize }
						options={ [
							{
								label: __( '4 buttons', 'fau-elemental' ),
								value: 'small',
							},
							{
								label: __( '3 buttons', 'fau-elemental' ),
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
					<div className="fau-big-button-sidebar-add-button">
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
								? ` - ${ trimTextSmart( itemTitle, 20 ) }`
								: ''
						}`;

						const urlValidation = validateUrl( item.url, true );

						return (
							<PanelBody
								key={ item.id || index }
								title={ panelTitle }
								initialOpen={ index === 0 }
							>
								<div className="fau-big-button-sidebar-controls">
									<ButtonGroup>
										<Tooltip
											text={ __(
												'Move up',
												'fau-elemental'
											) }
										>
											<Button
												icon="arrow-up-alt2"
												onClick={ () =>
													moveItemUp( index )
												}
												disabled={ index === 0 }
												label={ __(
													'Move up',
													'fau-elemental'
												) }
											/>
										</Tooltip>
										<Tooltip
											text={ __(
												'Move down',
												'fau-elemental'
											) }
										>
											<Button
												icon="arrow-down-alt2"
												onClick={ () =>
													moveItemDown( index )
												}
												disabled={
													index === items.length - 1
												}
												label={ __(
													'Move down',
													'fau-elemental'
												) }
											/>
										</Tooltip>
									</ButtonGroup>
									<Button
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
									help={ __(
										'You can also click on the title in the preview to edit it directly.',
										'fau-elemental'
									) }
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
									help={ sprintf(
										/* translators: %d: maximum character count */
										__(
											'Maximum %d characters recommended. You can also click on the description in the preview to edit it directly.',
											'fau-elemental'
										),
										DESCRIPTION_MAX_LENGTH
									) }
								/>
								{ item.description && (
									<p
										className={ `fau-big-button-character-count ${
											item.description.length >=
											DESCRIPTION_MAX_LENGTH
												? 'fau-big-button-character-count--warning'
												: ''
										}` }
									>
										{ sprintf(
											/* translators: %1$d: current character count, %2$d: maximum character count */
											__(
												'%1$d / %2$d characters',
												'fau-elemental'
											),
											item.description.length,
											DESCRIPTION_MAX_LENGTH
										) }
									</p>
								) }
								<TextControl
									label={ __( 'URL', 'fau-elemental' ) }
									value={ item.url || '' }
									onChange={ ( value ) =>
										updateItem( index, 'url', value )
									}
									type="url"
									help={ urlValidation.message }
									className={
										! urlValidation.isValid && item.url
											? 'has-error'
											: ''
									}
								/>

								{ /* Validation messages */ }
								{ ( ! item.title ||
									! item.url ||
									! urlValidation.isValid ) && (
									<div className="fau-big-button-validation-notice">
										<p>
											<strong>
												{ __(
													'Required for display:',
													'fau-elemental'
												) }
											</strong>
										</p>
										<ul>
											{ ! item.title && (
												<li>
													{ __(
														'Title is required',
														'fau-elemental'
													) }
												</li>
											) }
											{ ! item.url && (
												<li>
													{ __(
														'URL is required',
														'fau-elemental'
													) }
												</li>
											) }
											{ item.url &&
												! urlValidation.isValid && (
													<li>{ `• ${ urlValidation.message }` }</li>
												) }
										</ul>
										<p>
											<em>
												{ __(
													'This item will not be displayed on the frontend until both title and URL are provided.',
													'fau-elemental'
												) }
											</em>
										</p>
									</div>
								) }

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
					{ items.map( ( item, index ) => {
						// Determine button classes
						let buttonClasses =
							'fau-big-button-teaser-group__button';

						// Get the effective faculty color for this item
						const effectiveFacultyColor =
							getEffectiveFacultyColor( item );
						if ( effectiveFacultyColor ) {
							buttonClasses += ` fau-big-button-teaser-group__button--${ effectiveFacultyColor }`;
						}

						// Check if item is complete and valid
						const urlValidation = validateUrl( item.url, true );
						const isComplete =
							item.title && item.url && urlValidation.isValid;
						const previewClasses = isComplete
							? buttonClasses
							: `${ buttonClasses } fau-big-button-teaser-group__button--incomplete`;

						return (
							// eslint-disable-next-line jsx-a11y/anchor-is-valid -- <a> without href used as non-interactive editor preview container matching frontend a-tag styles
							<a
								key={ item.id || index }
								className={ `${ previewClasses } disabled` }
							>
								{ /* Inline controls */ }
								<div className="fau-big-button-teaser-group__inline-controls">
									{ items.length > 1 && index > 0 && (
										<Tooltip
											text={ __(
												'Move up',
												'fau-elemental'
											) }
										>
											<Button
												icon={ arrowUp }
												isSmall
												className="fau-big-button-teaser-group__move-up"
												onClick={ ( e ) => {
													e.stopPropagation();
													moveItemUp( index );
												} }
											/>
										</Tooltip>
									) }
									{ items.length > 1 &&
										index < items.length - 1 && (
											<Tooltip
												text={ __(
													'Move down',
													'fau-elemental'
												) }
											>
												<Button
													icon={ arrowDown }
													isSmall
													className="fau-big-button-teaser-group__move-down"
													onClick={ ( e ) => {
														e.stopPropagation();
														moveItemDown( index );
													} }
												/>
											</Tooltip>
										) }
									{ items.length > 1 && (
										<Tooltip
											text={ __(
												'Remove item',
												'fau-elemental'
											) }
										>
											<Button
												icon={ trash }
												isSmall
												isDestructive
												className="fau-big-button-teaser-group__button-remove"
												onClick={ ( e ) => {
													e.stopPropagation();
													removeItem( index );
												} }
											/>
										</Tooltip>
									) }
								</div>

								<RichText
									tagName="p"
									className="rich-text big-button-title"
									value={ item.title || '' }
									onChange={ ( value ) =>
										updateItem( index, 'title', value )
									}
									placeholder={ __(
										'Enter title…',
										'fau-elemental'
									) }
									allowedFormats={ [] }
									disableLineBreaks={ true }
									onClick={ ( e ) => e.stopPropagation() }
								/>

								<RichText
									tagName="p"
									className="rich-text"
									value={
										item.description &&
										item.description.length >
											DESCRIPTION_MAX_LENGTH
											? trimTextSmart(
													item.description,
													DESCRIPTION_MAX_LENGTH,
													'…'
											  )
											: item.description || ''
									}
									onChange={ ( value ) =>
										updateItem(
											index,
											'description',
											value
										)
									}
									placeholder={ __(
										'Add description…',
										'fau-elemental'
									) }
									allowedFormats={ [] }
									disableLineBreaks={ false }
									onClick={ ( e ) => e.stopPropagation() }
								/>

								<span
									className="arrow-link"
									aria-hidden="true"
								></span>
							</a>
						);
					} ) }

					{ /* Add new item button */ }
					<div
						className={ `fau-big-button-teaser-group__add-button fau-big-button-teaser-group__add-button--${ teaserSize }` }
					>
						<Tooltip text={ __( 'Add new item', 'fau-elemental' ) }>
							<Button
								icon={ plus }
								isPrimary
								onClick={ addItem }
								label={ __( 'Add Item', 'fau-elemental' ) }
							/>
						</Tooltip>
					</div>
				</div>
			</div>
		</>
	);
}
