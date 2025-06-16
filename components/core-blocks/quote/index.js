import { unregisterBlockStyle } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import {
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
	RichText,
	useBlockProps,
	BlockControls,
} from '@wordpress/block-editor';
import {
	Button,
	BaseControl,
	ToolbarGroup,
	ToolbarButton,
	ToolbarDropdownMenu,
	MenuGroup,
	MenuItem,
} from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { v4 as uuidv4 } from 'uuid';

domReady( () => {
	unregisterBlockStyle( 'core/quote', [ 'default', 'plain' ] );
} );

// Add custom attribute to quote block
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/edit-quote-block-settings',
	( settings, name ) => {
		if ( name !== 'core/quote' ) {
			return settings;
		}

		// Ensure this is only done once.
		if ( settings.fauModded ) {
			return settings;
		}
		settings.fauModded = true;

		// Deprecation for old Core-Markup
		const oldSaveFn = settings.save;
		const coreQuoteDeprecation = {
			supports: { ...settings.supports },
			attributes: { ...settings.attributes },
			save: oldSaveFn,
			migrate( attributes, innerBlocks ) {
				const firstParagraph = innerBlocks.find(
					( x ) => x.name === 'core/paragraph'
				);
				const firstImage = innerBlocks.find(
					( x ) => x.name === 'core/image'
				);
				let quoteImage = null;
				if ( firstImage ) {
					quoteImage = {
						id: firstImage.attributes.id,
						alt: firstImage.attributes.alt,
						url: firstImage.attributes.url,
					};
				}
				const migratedQuote = {
					id: uuidv4(),
					content: firstParagraph?.attributes.content?.text || '',
					citation: attributes.citation?.text || '',
					image: quoteImage,
				};
				return [
					{
						...attributes,
						citation: undefined,
						quotes: [ migratedQuote ],
					},
					[],
				];
			},
			isEligible( _attributes, innerBlocks, { block } ) {
				return (
					innerBlocks?.length ||
					block?.attributes?.citation?.text?.length
				);
			},
		};
		settings.deprecated = [
			coreQuoteDeprecation,
			...( settings.deprecated || [] ),
		];

		const initialUuid = uuidv4();

		return {
			...settings,
			attributes: {
				...settings.attributes,
				quotes: {
					type: 'array',
					default: [
						{
							id: initialUuid,
							content: '',
							citation: '',
							image: null,
						},
					],
				},
			},
		};
	}
);

// We create a HOC that overrides the core/quote block with a custom
// quote block that supports multiple quotes and images.
const withFauImageQuote = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		// We only want to override the core/quote blocks
		if ( props.name !== 'core/quote' ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;

		const [ selectedQuoteIndex, setSelectedQuoteIndex ] = useState( 0 );
		const blockProps = useBlockProps();

		const addNewQuote = () => {
			const quotes = [ ...( attributes.quotes || [] ) ];
			const newUuid = uuidv4();

			quotes.push( {
				id: newUuid,
				content: '',
				citation: '',
				image: null,
			} );

			// First update the attributes to ensure the new quote is added
			setAttributes( { quotes } );

			// Then update the selected quote index to point to the newly added quote
			// This ensures the InspectorControls panel will highlight the correct quote
			setSelectedQuoteIndex( quotes.length - 1 );
		};

		const updateQuote = ( index, field, value ) => {
			const quotes = [ ...attributes.quotes ];
			quotes[ index ] = { ...quotes[ index ], [ field ]: value };
			setAttributes( { quotes } );
		};

		const removeQuote = ( index ) => {
			const quotes = [ ...attributes.quotes ];
			quotes.splice( index, 1 );
			setSelectedQuoteIndex(
				Math.min( selectedQuoteIndex, Math.max( 0, quotes.length - 1 ) )
			);
			setAttributes( { quotes } );
		};

		const moveQuote = ( index, direction ) => {
			const quotes = [ ...attributes.quotes ];
			const newIndex = index + direction;
			if ( newIndex >= 0 && newIndex < quotes.length ) {
				[ quotes[ index ], quotes[ newIndex ] ] = [
					quotes[ newIndex ],
					quotes[ index ],
				];
				setSelectedQuoteIndex( newIndex );
				setAttributes( { quotes } );
			}
		};

		// Show a single quote inside the editor
		const renderQuoteContent = ( quote, index ) => (
			<div className="quote-wrapper">
				<div className="quote-content">
					{ quote.image && (
						<figure className="quote-image">
							<img
								src={ quote.image.url }
								alt={ quote.image.alt || '' }
							/>
						</figure>
					) }
					<div className="quote-text">
						<RichText
							tagName="blockquote"
							value={ quote.content }
							onChange={ ( content ) =>
								updateQuote( index, 'content', content )
							}
							placeholder={ __(
								'Enter quote text…',
								'fau-elemental'
							) }
							allowedFormats={ [] }
						/>
						<RichText
							tagName="cite"
							value={ quote.citation }
							onChange={ ( citation ) =>
								updateQuote( index, 'citation', citation )
							}
							placeholder={ __(
								'Enter citation…',
								'fau-elemental'
							) }
							allowedFormats={ [] }
						/>
					</div>
				</div>
			</div>
		);

		// Show all quotes inside the editor
		const renderQuotes = () => {
			if ( ! attributes.quotes?.length ) {
				return null;
			}

			if ( attributes.quotes.length === 1 ) {
				return (
					<div className="wp-block-quote-item">
						{ renderQuoteContent( attributes.quotes[ 0 ], 0 ) }
					</div>
				);
			}

			return (
				<QuoteCarousel
					selectedIndex={ selectedQuoteIndex }
					onSlideChange={ setSelectedQuoteIndex }
				>
					<div className="carousel-container">
						{ attributes.quotes.map( ( quote, index ) => (
							<div key={ quote.id } className="quote-slide">
								<div className="wp-block-quote-item">
									{ renderQuoteContent( quote, index ) }
								</div>
							</div>
						) ) }
					</div>
				</QuoteCarousel>
			);
		};

		// Having a MediaUpload component inside the ToolbarDropdownMenu caused some problems like
		// exceptions or the dropdown beeing in front of the MediaUpload Popover.
		// As a workaround we save a reference to the MediaUpload Button inside the InspectorControls
		// and virtually "click" this button inside the toolbar.
		const mediaUploaderButton = useRef( null );

		// Component to show the BlockControls Toolbar
		const renderQuoteBlockControls = () => {
			return (
				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton
							icon="plus"
							label={ __( 'Add New Quote', 'fau-elemental' ) }
							onClick={ addNewQuote }
						/>
						<ToolbarButton
							icon="arrow-left-alt"
							label={ __( 'Move quote up', 'fau-elemental' ) }
							onClick={ () =>
								moveQuote( selectedQuoteIndex, -1 )
							}
							disabled={ selectedQuoteIndex === 0 }
						/>
						<ToolbarButton
							icon="arrow-right-alt"
							label={ __( 'Move quote down', 'fau-elemental' ) }
							onClick={ () => moveQuote( selectedQuoteIndex, 1 ) }
							disabled={
								selectedQuoteIndex ===
								attributes.quotes.length - 1
							}
						/>
						<ToolbarDropdownMenu
							icon="arrow-down-alt2"
							label={ __( 'More', 'fau-elemental' ) }
						>
							{ ( { onClose } ) => (
								<>
									<MenuGroup>
										<MenuItem
											icon="format-image"
											iconPosition="left"
											disabled={
												mediaUploaderButton.current ==
												null
											}
											onClick={ () => {
												onClose();
												mediaUploaderButton.current?.click();
											} }
										>
											{ attributes.quotes[
												selectedQuoteIndex
											].image === null
												? __(
														'Add Image',
														'fau-elemental'
												  )
												: __(
														'Replace Image',
														'fau-elemental'
												  ) }
										</MenuItem>
										<MenuItem
											icon="editor-removeformatting"
											iconPosition="left"
											disabled={
												attributes.quotes[
													selectedQuoteIndex
												].image === null
											}
											onClick={ () =>
												updateQuote(
													selectedQuoteIndex,
													'image',
													null
												)
											}
										>
											{ __(
												'Remove image',
												'fau-elemental'
											) }
										</MenuItem>
									</MenuGroup>
									<MenuGroup>
										<MenuItem
											icon="trash"
											iconPosition="left"
											isDestructive
											disabled={
												attributes.quotes.length <= 1
											}
											onClick={ () =>
												removeQuote(
													selectedQuoteIndex
												)
											}
										>
											{ attributes.quotes.length <= 1
												? __(
														'Cannot remove the last quote',
														'fau-elemental'
												  )
												: __(
														'Remove this quote',
														'fau-elemental'
												  ) }
										</MenuItem>
									</MenuGroup>
								</>
							) }
						</ToolbarDropdownMenu>
					</ToolbarGroup>
				</BlockControls>
			);
		};

		// Renders the InspectorControls to manage
		// all quotes inside this block, including adding new ones
		const renderManageInspectorControls = () => {
			if ( ! attributes.quotes?.length ) {
				return null;
			}
			return (
				<>
					<div className="quote-list">
						{ attributes.quotes.map( ( quote, index ) => (
							<div
								key={ quote.id }
								className={ `quote-list-item ${
									index === selectedQuoteIndex
										? 'is-selected'
										: ''
								}` }
								onClick={ () => {
									setSelectedQuoteIndex( index );
								} }
							>
								<div className="quote-list-item__content">
									<span className="quote-list-item__text">
										{ quote.content
											? quote.content
													.replace( /<[^>]*>/g, '' )
													.substring( 0, 50 ) + '...'
											: __(
													'Empty quote',
													'fau-elemental'
											  ) }
									</span>
								</div>
								<div className="quote-list-item__actions">
									<Button
										icon="arrow-up-alt2"
										onClick={ ( e ) => {
											e.stopPropagation();
											moveQuote( index, -1 );
										} }
										disabled={ index === 0 }
										className="quote-list-item__move"
										title={ __(
											'Move quote up',
											'fau-elemental'
										) }
									/>
									<Button
										icon="arrow-down-alt2"
										onClick={ ( e ) => {
											e.stopPropagation();
											moveQuote( index, 1 );
										} }
										disabled={
											index ===
											attributes.quotes.length - 1
										}
										className="quote-list-item__move"
										title={ __(
											'Move quote down',
											'fau-elemental'
										) }
									/>
									<Button
										icon="trash"
										onClick={ ( e ) => {
											e.stopPropagation();
											removeQuote( index );
										} }
										isDestructive
										disabled={
											attributes.quotes.length <= 1
										}
										className="quote-list-item__remove"
										title={
											attributes.quotes.length <= 1
												? __(
														'Cannot remove the last quote',
														'fau-elemental'
												  )
												: __(
														'Remove this quote',
														'fau-elemental'
												  )
										}
									/>
								</div>
							</div>
						) ) }
						<button
							type="button"
							className="quote-list-item quote-list-item-add"
							onClick={ addNewQuote }
						>
							<div className="quote-list-item__content">
								<span className="quote-list-item__add-icon">
									<svg
										width="24"
										height="24"
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 24 24"
										aria-hidden="true"
										focusable="false"
									>
										<path d="M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z"></path>
									</svg>
								</span>
								<span className="quote-list-item__add-label">
									{ __( 'Add New Quote', 'fau-elemental' ) }
								</span>
							</div>
						</button>
					</div>
				</>
			);
		};

		// Renders the InspectorControls for a single quote
		const renderQuoteInspectorControls = () => {
			return (
				<BaseControl
					label={ __( 'Quote Image', 'fau-elemental' ) }
					help={ __(
						'Add an image to accompany this quote',
						'fau-elemental'
					) }
				>
					<div className="quote-image-controls">
						<MediaUploadCheck>
							<div className="editor-post-featured-image">
								<MediaUpload
									onSelect={ ( media ) =>
										updateQuote(
											selectedQuoteIndex,
											'image',
											media
										)
									}
									allowedTypes={ [ 'image' ] }
									value={
										attributes.quotes[ selectedQuoteIndex ]
											.image?.id
									}
									render={ ( { open } ) => (
										<div>
											{ ! attributes.quotes[
												selectedQuoteIndex
											].image && (
												<Button
													ref={ mediaUploaderButton }
													onClick={ open }
													variant="secondary"
													className="editor-post-featured-image__toggle"
												>
													{ __(
														'Add Image',
														'fau-elemental'
													) }
												</Button>
											) }
											{ attributes.quotes[
												selectedQuoteIndex
											].image && (
												<>
													<img
														src={
															attributes.quotes[
																selectedQuoteIndex
															].image.url
														}
														alt={
															attributes.quotes[
																selectedQuoteIndex
															].image.alt || ''
														}
														className="editor-post-featured-image__preview"
													/>
													<div className="editor-post-featured-image__actions">
														<Button
															ref={
																mediaUploaderButton
															}
															onClick={ open }
															variant="secondary"
															className="editor-post-featured-image__action"
														>
															{ __(
																'Replace',
																'fau-elemental'
															) }
														</Button>
														<Button
															onClick={ () =>
																updateQuote(
																	selectedQuoteIndex,
																	'image',
																	null
																)
															}
															isDestructive
															className="editor-post-featured-image__action"
														>
															{ __(
																'Remove',
																'fau-elemental'
															) }
														</Button>
													</div>
												</>
											) }
										</div>
									) }
								/>
							</div>
						</MediaUploadCheck>
					</div>
				</BaseControl>
			);
		};

		// Do not render anything if the selectedQuoteIndex is out of bounds, instead
		// reset it to 0.
		// This may happen if the user undos or redos changes.
		if (
			attributes.quotes &&
			selectedQuoteIndex >= attributes.quotes.length
		) {
			setSelectedQuoteIndex( 0 );
			return <></>;
		}

		// Build the full withFauImageQuote
		return (
			<>
				{ renderQuoteBlockControls() }
				<InspectorControls>
					{ attributes.quotes?.length > 0 && (
						<>
							{ renderManageInspectorControls() }
							{ renderQuoteInspectorControls() }
						</>
					) }
				</InspectorControls>
				<div { ...blockProps }>{ renderQuotes() }</div>
			</>
		);
	};
}, 'withFauImageQuote' );

// Install the fau quote override
addFilter(
	'editor.BlockEdit',
	'fau-elemental/quote-with-image',
	withFauImageQuote
);

// Modify the frontend save element
addFilter(
	'blocks.getSaveElement',
	'fau-elemental/quote-with-image',
	( element, block, attributes ) => {
		if ( block.name !== 'core/quote' || ! attributes.quotes?.length ) {
			return element;
		}

		// Filter out quotes with empty content
		const validQuotes = attributes.quotes.filter(
			( quote ) => quote.content && quote.content.trim() !== ''
		);

		// If no valid quotes remain, return default element
		if ( validQuotes.length === 0 ) {
			return element;
		}

		const renderQuote = ( quote ) => (
			<div className="quote-content">
				{ quote.image && (
					<figure className="quote-image">
						<img
							src={ quote.image.url }
							alt={ quote.image.alt || '' }
						/>
					</figure>
				) }
				<div className="quote-text">
					<blockquote>
						<RichText.Content value={ quote.content } />
					</blockquote>
					{ quote.citation && (
						<cite>
							<RichText.Content value={ quote.citation } />
						</cite>
					) }
				</div>
			</div>
		);

		if ( validQuotes.length === 1 ) {
			return (
				<div className="wp-block-quote-item">
					{ renderQuote( validQuotes[ 0 ] ) }
				</div>
			);
		}

		return (
			<div className="quote-carousel">
				<div className="carousel-container">
					{ validQuotes.map( ( quote ) => (
						<div key={ quote.id } className="quote-slide">
							<div className="wp-block-quote-item">
								{ renderQuote( quote ) }
							</div>
						</div>
					) ) }
				</div>
				<div className="carousel-controls">
					<button
						className="carousel-prev"
						aria-label={ __( 'Previous slide', 'fau-elemental' ) }
					>
						❮
					</button>
					<div className="carousel-dots"></div>
					<button
						className="carousel-next"
						aria-label={ __( 'Next slide', 'fau-elemental' ) }
					>
						❯
					</button>
				</div>
			</div>
		);
	}
);

// React component for the quote carousel (used in editor)
const QuoteCarousel = ( { children, selectedIndex = 0, onSlideChange } ) => {
	const [ currentSlide, setCurrentSlide ] = useState( selectedIndex );
	const [ slides, setSlides ] = useState( [] );
	const carouselRef = useRef( null );

	useEffect( () => {
		if ( carouselRef.current ) {
			const slideElements = Array.from(
				carouselRef.current.querySelectorAll( '.quote-slide' )
			);
			setSlides( slideElements );
		}
	}, [ children ] );

	// Update currentSlide when selectedIndex changes from parent
	useEffect( () => {
		setCurrentSlide( selectedIndex );
	}, [ selectedIndex ] );

	const handlePrevClick = () => {
		const newIndex = ( currentSlide - 1 + slides.length ) % slides.length;
		setCurrentSlide( newIndex );
		if ( onSlideChange ) {
			onSlideChange( newIndex );
		}
	};

	const handleNextClick = () => {
		const newIndex = ( currentSlide + 1 ) % slides.length;
		setCurrentSlide( newIndex );
		if ( onSlideChange ) {
			onSlideChange( newIndex );
		}
	};

	const handleDotClick = ( index ) => {
		setCurrentSlide( index );
		if ( onSlideChange ) {
			onSlideChange( index );
		}
	};

	// Update slide visibility
	useEffect( () => {
		slides.forEach( ( slide, index ) => {
			if ( slide ) {
				const isVisible = index === currentSlide;
				slide.style.display = isVisible ? 'block' : 'none';
				slide.setAttribute( 'aria-hidden', ! isVisible );
			}
		} );
	}, [ currentSlide, slides ] );

	// Hide navigation if only one slide
	const showNavigation = slides.length > 1;

	return (
		<div className="quote-carousel" ref={ carouselRef }>
			{ children }
			{ showNavigation && (
				<div className="carousel-controls">
					<button
						className="carousel-prev"
						aria-label={ __( 'Previous slide', 'fau-elemental' ) }
						onClick={ handlePrevClick }
					>
						❮
					</button>
					<div className="carousel-dots">
						{ slides.map( ( _, index ) => (
							<button
								key={ index }
								className={
									index === currentSlide ? 'active' : ''
								}
								aria-label={ __(
									`Go to slide ${ index + 1 }`,
									'fau-elemental'
								) }
								onClick={ () => handleDotClick( index ) }
							/>
						) ) }
					</div>
					<button
						className="carousel-next"
						aria-label={ __( 'Next slide', 'fau-elemental' ) }
						onClick={ handleNextClick }
					>
						❯
					</button>
				</div>
			) }
		</div>
	);
};
