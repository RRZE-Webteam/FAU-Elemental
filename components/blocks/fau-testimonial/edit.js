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
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { v4 as uuidv4 } from 'uuid';
import TestimonialCarousel from './components/TestimonialCarousel';

export default function Edit( { attributes, setAttributes } ) {
	const [ selectedItemIndex, setSelectedItemIndex ] = useState( 0 );
	const blockProps = useBlockProps();

	const addNewItem = () => {
		const items = [ ...( attributes.items || [] ) ];
		const newUuid = uuidv4();

		items.push( {
			id: newUuid,
			content: '',
			citation: '',
			image: null,
		} );

		// First update the attributes to ensure the new item is added
		setAttributes( { items } );

		// Then update the selected item index to point to the newly added item
		// This ensures the InspectorControls panel will highlight the correct item
		setSelectedItemIndex( items.length - 1 );
	};

	const updateItem = ( index, field, value ) => {
		const items = [ ...attributes.items ];
		items[ index ] = { ...items[ index ], [ field ]: value };
		setAttributes( { items } );
	};

	const removeItem = ( index ) => {
		const items = [ ...attributes.items ];
		items.splice( index, 1 );
		setSelectedItemIndex(
			Math.min( selectedItemIndex, Math.max( 0, items.length - 1 ) )
		);
		setAttributes( { items } );
	};

	const moveItem = ( index, direction ) => {
		const items = [ ...attributes.items ];
		const newIndex = index + direction;
		if ( newIndex >= 0 && newIndex < items.length ) {
			[ items[ index ], items[ newIndex ] ] = [
				items[ newIndex ],
				items[ index ],
			];
			setSelectedItemIndex( newIndex );
			setAttributes( { items } );
		}
	};

	// Show a single testimonial inside the editor
	const renderTestimonialContent = ( testimonial, index ) => (
		<div className="testimonial-wrapper">
			<div className="testimonial-content">
				{ testimonial.image && (
					<figure className="testimonial-image">
						<img
							src={ testimonial.image.url }
							alt={ testimonial.image.alt || '' }
						/>
					</figure>
				) }
				<div className="testimonial-text">
					<RichText
						tagName="blockquote"
						value={ testimonial.content }
						onChange={ ( content ) =>
							updateItem( index, 'content', content )
						}
						placeholder={ __(
							'Enter testimonial text…',
							'fau-elemental'
						) }
						allowedFormats={ [] }
					/>
					<RichText
						tagName="cite"
						value={ testimonial.citation }
						onChange={ ( citation ) =>
							updateItem( index, 'citation', citation )
						}
						placeholder={ __( 'Enter citation…', 'fau-elemental' ) }
						allowedFormats={ [] }
					/>
				</div>
			</div>
		</div>
	);

	// Show all testimonials inside the editor
	const renderTestimonials = () => {
		if ( ! attributes.items?.length ) {
			return null;
		}

		if ( attributes.items.length === 1 ) {
			return (
				<div className="fau-testimonial-item">
					{ renderTestimonialContent( attributes.items[ 0 ], 0 ) }
				</div>
			);
		}

		return (
			<TestimonialCarousel
				selectedIndex={ selectedItemIndex }
				onSlideChange={ setSelectedItemIndex }
			>
				<div className="carousel-container">
					{ attributes.items.map( ( item, index ) => (
						<div key={ item.id } className="testimonial-slide">
							<div className="fau-testimonial-item">
								{ renderTestimonialContent( item, index ) }
							</div>
						</div>
					) ) }
				</div>
			</TestimonialCarousel>
		);
	};

	// Having a MediaUpload component inside the ToolbarDropdownMenu caused some problems like
	// exceptions or the dropdown beeing in front of the MediaUpload Popover.
	// As a workaround we save a reference to the MediaUpload Button inside the InspectorControls
	// and virtually "click" this button inside the toolbar.
	const mediaUploaderButton = useRef( null );

	// Component to show the BlockControls Toolbar
	const renderTestimonialBlockControls = () => {
		return (
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon="plus"
						label={ __( 'Add New Testimonial', 'fau-elemental' ) }
						onClick={ addNewItem }
					/>
					<ToolbarButton
						icon="arrow-left-alt"
						label={ __( 'Move testimonial up', 'fau-elemental' ) }
						onClick={ () => moveItem( selectedItemIndex, -1 ) }
						disabled={ selectedItemIndex === 0 }
					/>
					<ToolbarButton
						icon="arrow-right-alt"
						label={ __( 'Move testimonial down', 'fau-elemental' ) }
						onClick={ () => moveItem( selectedItemIndex, 1 ) }
						disabled={
							selectedItemIndex === attributes.items.length - 1
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
											mediaUploaderButton.current === null
										}
										onClick={ () => {
											onClose();
											mediaUploaderButton.current?.click();
										} }
									>
										{ attributes.items[ selectedItemIndex ]
											.image === null
											? __( 'Add Image', 'fau-elemental' )
											: __(
													'Replace Image',
													'fau-elemental'
											  ) }
									</MenuItem>
									<MenuItem
										icon="editor-removeformatting"
										iconPosition="left"
										disabled={
											attributes.items[
												selectedItemIndex
											].image === null
										}
										onClick={ () =>
											updateItem(
												selectedItemIndex,
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
											attributes.items.length <= 1
										}
										onClick={ () =>
											removeItem( selectedItemIndex )
										}
									>
										{ attributes.items.length <= 1
											? __(
													'Cannot remove the last testimonial',
													'fau-elemental'
											  )
											: __(
													'Remove this testimonial',
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
	// all testimonials inside this block, including adding new ones
	const renderManageInspectorControls = () => {
		if ( ! attributes.items?.length ) {
			return null;
		}
		return (
			<>
				<div className="testimonial-list">
					{ attributes.items.map( ( item, index ) => (
						<button
							key={ item.id }
							className={ `testimonial-list-item ${
								index === selectedItemIndex ? 'is-selected' : ''
							}` }
							onClick={ () => {
								setSelectedItemIndex( index );
							} }
						>
							<div className="testimonial-list-item__content">
								<span className="testimonial-list-item__text">
									{ item.content
										? item.content
												.replace( /<[^>]*>/g, '' )
												.substring( 0, 50 ) + '...'
										: __(
												'Empty testimonial',
												'fau-elemental'
										  ) }
								</span>
							</div>
							<div className="testimonial-list-item__actions">
								<Button
									icon="arrow-up-alt2"
									onClick={ ( e ) => {
										e.stopPropagation();
										moveItem( index, -1 );
									} }
									disabled={ index === 0 }
									className="testimonial-list-item__move"
									title={ __(
										'Move testimonial up',
										'fau-elemental'
									) }
								/>
								<Button
									icon="arrow-down-alt2"
									onClick={ ( e ) => {
										e.stopPropagation();
										moveItem( index, 1 );
									} }
									disabled={
										index === attributes.items.length - 1
									}
									className="testimonial-list-item__move"
									title={ __(
										'Move testimonial down',
										'fau-elemental'
									) }
								/>
								<Button
									icon="trash"
									onClick={ ( e ) => {
										e.stopPropagation();
										removeItem( index );
									} }
									isDestructive
									disabled={ attributes.items.length <= 1 }
									className="testimonial-list-item__remove"
									title={
										attributes.items.length <= 1
											? __(
													'Cannot remove the last testimonial',
													'fau-elemental'
											  )
											: __(
													'Remove this testimonial',
													'fau-elemental'
											  )
									}
								/>
							</div>
						</button>
					) ) }
					<button
						type="button"
						className="testimonial-list-item testimonial-list-item-add"
						onClick={ addNewItem }
					>
						<div className="testimonial-list-item__content">
							<span className="testimonial-list-item__add-icon">
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
							<span className="testimonial-list-item__add-label">
								{ __( 'Add New Testimonial', 'fau-elemental' ) }
							</span>
						</div>
					</button>
				</div>
			</>
		);
	};

	// Renders the InspectorControls for a single testimonial
	const renderTestimonialInspectorControls = () => {
		return (
			<BaseControl
				label={ __( 'Testimonial Image', 'fau-elemental' ) }
				help={ __(
					'Add an image to accompany this testimonial',
					'fau-elemental'
				) }
				id="testimonial-image-upload"
			>
				<div className="testimonial-image-controls">
					<MediaUploadCheck>
						<div className="editor-post-featured-image">
							<MediaUpload
								onSelect={ ( media ) =>
									updateItem(
										selectedItemIndex,
										'image',
										media
									)
								}
								allowedTypes={ [ 'image' ] }
								value={
									attributes.items[ selectedItemIndex ].image
										?.id
								}
								render={ ( { open } ) => (
									<div>
										{ ! attributes.items[
											selectedItemIndex
										].image && (
											<Button
												id="testimonial-image-upload"
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
										{ attributes.items[ selectedItemIndex ]
											.image && (
											<>
												<img
													src={
														attributes.items[
															selectedItemIndex
														].image.url
													}
													alt={
														attributes.items[
															selectedItemIndex
														].image.alt || ''
													}
													className="editor-post-featured-image__preview"
												/>
												<div className="editor-post-featured-image__actions">
													<Button
														id="testimonial-image-upload"
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
															updateItem(
																selectedItemIndex,
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

	// Do not render anything if the selectedItemIndex is out of bounds, instead
	// reset it to 0.
	// This may happen if the user undos or redos changes.
	if ( attributes.items && selectedItemIndex >= attributes.items.length ) {
		setSelectedItemIndex( 0 );
		return <></>;
	}

	return (
		<>
			{ renderTestimonialBlockControls() }
			<InspectorControls>
				{ attributes.items?.length > 0 && (
					<>
						{ renderManageInspectorControls() }
						{ renderTestimonialInspectorControls() }
					</>
				) }
			</InspectorControls>
			<div { ...blockProps }>{ renderTestimonials() }</div>
		</>
	);
}
