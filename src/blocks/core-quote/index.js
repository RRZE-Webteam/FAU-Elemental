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
import { Button, BaseControl, ToolbarGroup } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { v4 as uuidv4 } from 'uuid';
import { useDispatch, useSelect } from '@wordpress/data';

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

// Simple carousel initialization
const initCarousel = ( container, initialSlide = 0, onSlideChange = null ) => {
	if ( ! container ) return;

	const slides = container.querySelectorAll( '.quote-slide' );
	const prevButton = container.querySelector( '.carousel-prev' );
	const dots = container.querySelector( '.carousel-dots' );
	const nextButton = container.querySelector( '.carousel-next' );

	if ( ! slides.length || slides.length <= 1 ) {
		if ( prevButton ) prevButton.style.display = 'none';
		if ( nextButton ) nextButton.style.display = 'none';
		if ( dots ) dots.style.display = 'none';
		return;
	}

	let currentSlide = Math.min( initialSlide, slides.length - 1 );

	const updateSlides = () => {
		slides.forEach( ( slide, index ) => {
			if ( slide ) {
				slide.style.display = index === currentSlide ? 'block' : 'none';
			}
		} );

		if ( dots ) {
			const dotButtons = dots.querySelectorAll( 'button' );
			dotButtons.forEach( ( dot, index ) => {
				dot.classList.toggle( 'active', index === currentSlide );
			} );
		}

		if ( onSlideChange ) {
			onSlideChange( currentSlide );
		}
	};

	// Clear and create new dots
	if ( dots ) {
		dots.innerHTML = '';
		slides.forEach( ( _, index ) => {
			const dot = document.createElement( 'button' );
			dot.setAttribute(
				'aria-label',
				__( `Go to slide ${ index + 1 }`, 'fau-elemental' )
			);
			dot.addEventListener( 'click', () => {
				currentSlide = index;
				updateSlides();
			} );
			dots.appendChild( dot );
		} );
		dots.style.display = 'flex';
	}

	// Add click handlers directly without cloning
	if ( prevButton ) {
		prevButton.style.display = 'block';
		prevButton.onclick = () => {
			currentSlide = ( currentSlide - 1 + slides.length ) % slides.length;
			updateSlides();
		};
	}

	if ( nextButton ) {
		nextButton.style.display = 'block';
		nextButton.onclick = () => {
			currentSlide = ( currentSlide + 1 ) % slides.length;
			updateSlides();
		};
	}

	updateSlides();
};

const withImageControl = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( props.name !== 'core/quote' ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes, clientId } = props;
		const { selectBlock } = useDispatch( 'core/block-editor' );
		const { getSelectedBlockClientId } = useSelect(
			( select ) => ( {
				getSelectedBlockClientId:
					select( 'core/block-editor' ).getSelectedBlockClientId,
			} ),
			[]
		);

		const carouselRef = useRef( null );
		const currentSlideRef = useRef( 0 );
		const [ selectedQuoteIndex, setSelectedQuoteIndex ] = useState( 0 );
		const blockProps = useBlockProps();

		// Track if we're in the middle of an undo operation
		const isUndoRef = useRef( false );

		// Listen for changes in the selected block
		useEffect( () => {
			const selectedBlockId = getSelectedBlockClientId();

			// If we were previously selected and now we're not, and we're in an undo operation
			if ( selectedBlockId !== clientId && isUndoRef.current ) {
				// Re-select our block
				selectBlock( clientId );
			}

			// Reset the undo flag
			isUndoRef.current = false;
		}, [ getSelectedBlockClientId(), clientId, selectBlock ] );

		// Listen for undo/redo operations
		useEffect( () => {
			const handleKeyDown = ( event ) => {
				// Check for Ctrl+Z (undo) or Ctrl+Y (redo)
				if (
					( event.ctrlKey || event.metaKey ) &&
					( event.key === 'z' || event.key === 'y' )
				) {
					isUndoRef.current = true;
				}
			};

			document.addEventListener( 'keydown', handleKeyDown );
			return () => {
				document.removeEventListener( 'keydown', handleKeyDown );
			};
		}, [] );

		const handleSlideChange = ( newIndex ) => {
			setSelectedQuoteIndex( newIndex );
			currentSlideRef.current = newIndex;
		};

		useEffect( () => {
			if ( carouselRef.current ) {
				initCarousel(
					carouselRef.current,
					currentSlideRef.current,
					handleSlideChange
				);
			}
		}, [ attributes.quotes ] );

		useEffect( () => {
			if ( carouselRef.current ) {
				currentSlideRef.current = selectedQuoteIndex;
				initCarousel(
					carouselRef.current,
					selectedQuoteIndex,
					handleSlideChange
				);
			}
		}, [ selectedQuoteIndex ] );

		const addNewQuote = () => {
			const quotes = [ ...( attributes.quotes || [] ) ];
			const newUuid = uuidv4();

			quotes.push( {
				id: newUuid,
				content: '',
				citation: '',
				image: null,
			} );
			// Set the current slide to the new quote
			currentSlideRef.current = quotes.length - 1;
			setSelectedQuoteIndex( quotes.length - 1 );
			setAttributes( { quotes } );
		};

		const updateQuote = ( index, field, value ) => {
			const quotes = [ ...attributes.quotes ];
			quotes[ index ] = { ...quotes[ index ], [ field ]: value };
			setAttributes( { quotes } );
		};

		const removeQuote = ( index ) => {
			const quotes = [ ...attributes.quotes ];
			quotes.splice( index, 1 );
			currentSlideRef.current = Math.min(
				currentSlideRef.current,
				Math.max( 0, quotes.length - 1 )
			);
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

		const renderQuotes = () => {
			if ( ! attributes.quotes?.length ) return null;

			if ( attributes.quotes.length === 1 ) {
				return (
					<div className="wp-block-quote-item">
						{ renderQuoteContent( attributes.quotes[ 0 ], 0 ) }
					</div>
				);
			}

			return (
				<div className="quote-carousel" ref={ carouselRef }>
					<div className="carousel-container">
						{ attributes.quotes.map( ( quote, index ) => (
							<div key={ quote.id } className="quote-slide">
								<div className="wp-block-quote-item">
									{ renderQuoteContent( quote, index ) }
								</div>
							</div>
						) ) }
					</div>
					<div className="carousel-controls">
						<button
							className="carousel-prev"
							aria-label={ __(
								'Previous slide',
								'fau-elemental'
							) }
						>
							❮
						</button>
						<div className="carousel-dots"></div>
						<button
							className="carousel-next"
							Move
							quote
							down
							aria-label={ __( 'Next slide', 'fau-elemental' ) }
						>
							❯
						</button>
					</div>
				</div>
			);
		};

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
								'Enter quote text...',
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
								'Enter citation...',
								'fau-elemental'
							) }
							allowedFormats={ [] }
						/>
					</div>
				</div>
			</div>
		);

		const renderQuoteControls = () => {
			if ( ! attributes.quotes?.length ) return null;

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
									if ( carouselRef.current ) {
										currentSlideRef.current = index;
										initCarousel(
											carouselRef.current,
											index,
											handleSlideChange
										);
									}
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

		return (
			<>
				<BlockControls>
					<ToolbarGroup>
						<Button
							icon="plus"
							label={ __( 'Add New Quote', 'fau-elemental' ) }
							onClick={ addNewQuote }
						/>
					</ToolbarGroup>
				</BlockControls>
				<InspectorControls>
					{ attributes.quotes?.length > 0 && (
						<>
							{ renderQuoteControls() }
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
													attributes.quotes[
														selectedQuoteIndex
													].image?.id
												}
												render={ ( { open } ) => (
													<div>
														{ ! attributes.quotes[
															selectedQuoteIndex
														].image && (
															<Button
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
																		attributes
																			.quotes[
																			selectedQuoteIndex
																		].image
																			.url
																	}
																	alt={
																		attributes
																			.quotes[
																			selectedQuoteIndex
																		].image
																			.alt ||
																		''
																	}
																	className="editor-post-featured-image__preview"
																/>
																<div className="editor-post-featured-image__actions">
																	<Button
																		onClick={
																			open
																		}
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
						</>
					) }
				</InspectorControls>
				<div { ...blockProps }>{ renderQuotes() }</div>
			</>
		);
	};
}, 'withImageControl' );

addFilter(
	'editor.BlockEdit',
	'fau-elemental/quote-with-image',
	withImageControl
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

// Add support for grouping to the quote block
addFilter(
	'blocks.registerBlockType',
	'fau-elemental/quote-group-support',
	( settings, name ) => {
		if ( name !== 'core/quote' ) {
			return settings;
		}

		return {
			...settings,
			supports: {
				...settings.supports,
				__experimentalGroup: true,
			},
		};
	}
);

// Prevent quote block from being transformed into a paragraph when grouped
addFilter(
	'blocks.switchToBlockType.transformedBlock',
	'fau-elemental/prevent-quote-transformation',
	( transformedBlock, sourceBlock, sourceAttributes ) => {
		// Check if the source block is a quote block
		if ( sourceBlock.name === 'core/quote' ) {
			// If the transformed block is a paragraph, return the original quote block
			if ( transformedBlock.name === 'core/paragraph' ) {
				return sourceBlock;
			}
		}
		return transformedBlock;
	}
);
