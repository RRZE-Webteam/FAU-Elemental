/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Editor preview component for FAU Portal Menu block
 * Matches the frontend WCAG 2.2 Level II compliant structure exactly
 * Shows actual menu items from the selected menu
 */
const EditorPreview = ( { attributes, selectedMenuName } ) => {
	// Configuration classes that match the backend config
	const CSS_CLASSES = {
		container: 'contentmenu',
		menu_list: 'subpages-menu',
		portal_item: 'portal-item',
		portal_thumbnail: 'portal-thumbnail',
		portal_content: 'portal-content',
		portal_title: 'portal-title',
		portal_main_link: 'portal-main-link',
		portal_button_arrow: 'portal-button-arrow',
		portal_submenu: 'portal-submenu',
		portal_subitem: 'portal-subitem',
		portal_sublink: 'portal-sublink',
		screen_reader_text: 'screen-reader-text',
		image_link: 'image-link',
		list_view: 'listview',
		no_thumb: 'no-thumb',
		hover_zoom: 'hover-zoom',
		hover_blur: 'hover-blur',
		dark_style: 'is-style-dark',
	};

	// Type configurations that match the backend
	const TYPES = {
		1: { css_class: 'size_2-1', aspect_ratio: '2/1' },
		2: { css_class: 'size_3-2', aspect_ratio: '3/2' },
		3: { css_class: 'size_3-4', aspect_ratio: '3/4' },
	};

	// Fetch actual menu items
	const menuItems = useSelect(
		( select ) => {
			if ( ! attributes.menuId ) {
				return [];
			}

			// Get menu items - all items, not just hierarchical
			const menuItemsData = select( 'core' ).getMenuItems( {
				menus: parseInt( attributes.menuId ),
				per_page: 100,
				orderby: 'menu_order',
				order: 'asc',
			} );

			if ( ! menuItemsData ) {
				return [];
			}

			// Process all items and fetch featured images
			const processedItems = menuItemsData.map( ( item ) => {
				// Try to get featured image from the linked object (post/page)
				let featuredImageUrl = null;
				if ( item.object_id && item.object === 'page' ) {
					// For pages, try to get the featured image
					const page = select( 'core' ).getEntityRecord(
						'postType',
						'page',
						item.object_id
					);
					if ( page && page.featured_media ) {
						const media = select( 'core' ).getMedia(
							page.featured_media
						);
						if (
							media &&
							media.media_details &&
							media.media_details.sizes
						) {
							const imageSize =
								media.media_details.sizes.medium ||
								media.media_details.sizes.full;
							if ( imageSize ) {
								featuredImageUrl = imageSize.source_url;
							} else {
								featuredImageUrl = media.source_url;
							}
						}
					}
				} else if ( item.object_id && item.object === 'post' ) {
					// For posts, try to get the featured image
					const post = select( 'core' ).getEntityRecord(
						'postType',
						'post',
						item.object_id
					);
					if ( post && post.featured_media ) {
						const media = select( 'core' ).getMedia(
							post.featured_media
						);
						if (
							media &&
							media.media_details &&
							media.media_details.sizes
						) {
							const imageSize =
								media.media_details.sizes.medium ||
								media.media_details.sizes.full;
							if ( imageSize ) {
								featuredImageUrl = imageSize.source_url;
							} else {
								featuredImageUrl = media.source_url;
							}
						}
					}
				}

				return {
					...item,
					featuredImageUrl,
				};
			} );

			// Return only top-level items (parent == 0) for display
			// but include children data for each
			const topLevelItems = processedItems.filter(
				( item ) => item.parent === 0
			);

			// If no top-level items found, return all items (some menus might not use hierarchy)
			if ( topLevelItems.length === 0 ) {
				return processedItems.map( ( item ) => ( {
					...item,
					children: [],
				} ) );
			}

			return topLevelItems.map( ( topItem ) => {
				// Find children for this top-level item
				const children = processedItems.filter(
					( item ) => item.parent === topItem.id
				);

				return {
					...topItem,
					children,
				};
			} );
		},
		[ attributes.menuId ]
	);

	// Build CSS classes based on attributes - matches backend logic exactly
	const getContentClasses = () => {
		let classes = CSS_CLASSES.container;

		// Add type-specific class
		const type = attributes.type || 1;
		const typeConfig = TYPES[ type ] || TYPES[ 1 ];
		classes += ' ' + typeConfig.css_class;

		// Add optional classes using configuration
		if ( attributes.listView ) {
			classes += ' ' + CSS_CLASSES.list_view;
		}
		if ( attributes.noThumbs ) {
			classes += ' ' + CSS_CLASSES.no_thumb;
		}
		if ( attributes.hoverZoom ) {
			classes += ' ' + CSS_CLASSES.hover_zoom;
		}
		if ( attributes.hoverBlur ) {
			classes += ' ' + CSS_CLASSES.hover_blur;
		}
		if ( attributes.isDark ) {
			classes += ' ' + CSS_CLASSES.dark_style;
		}

		return classes;
	};

	// Get column class based on default 3 columns
	const getColumnClass = () => {
		return 'portal-column-3'; // Default to 3 columns like backend
	};

	// Get featured image for menu item if available
	const getMenuItemImage = ( item ) => {
		// Use the pre-fetched featured image URL
		if ( item.featuredImageUrl ) {
			return item.featuredImageUrl;
		}

		// Fallback: check if the menu item has embedded featured media
		if (
			item._embedded &&
			item._embedded[ 'wp:featuredmedia' ] &&
			item._embedded[ 'wp:featuredmedia' ][ 0 ]
		) {
			const media = item._embedded[ 'wp:featuredmedia' ][ 0 ];
			if ( media.media_details && media.media_details.sizes ) {
				// Use medium size if available, fallback to full
				const imageSize =
					media.media_details.sizes.medium ||
					media.media_details.sizes.full;
				if ( imageSize ) {
					return imageSize.source_url;
				}
			}
			return media.source_url;
		}

		return null;
	};

	// Render individual menu item
	const renderMenuItem = ( item ) => {
		const hasChildren = item.children && item.children.length > 0;
		const showSubs = attributes.showSubs !== false;
		const itemImage = getMenuItemImage( item );
		const itemTitle =
			item.title?.rendered ||
			item.title ||
			__( 'Menu Item', 'fau-elemental' );

		return (
			<li
				key={ item.id }
				className={ `${
					CSS_CLASSES.portal_item
				} ${ getColumnClass() }` }
			>
				{ ! attributes.noThumbs && (
					<div className={ CSS_CLASSES.portal_thumbnail }>
						<button
							type="button"
							onClick={ ( e ) => e.preventDefault() }
							className={ CSS_CLASSES.image_link }
							/* translators: %s: Menu item title */
							aria-label={ sprintf(
								__( 'Go to %s', 'fau-elemental' ),
								itemTitle
							) }
							tabIndex="0"
						>
							{ itemImage ? (
								<img
									src={ itemImage }
									/* translators: %s: Menu item title */
									alt={ sprintf(
										__(
											'Featured image for %s',
											'fau-elemental'
										),
										itemTitle
									) }
									loading="lazy"
								/>
							) : (
								/* translators: %s: Menu item title */
								<div
									className="portal-placeholder-image"
									role="img"
									aria-label={ sprintf(
										__(
											'No image available for %s',
											'fau-elemental'
										),
										itemTitle
									) }
								>
									<span>
										{ __( 'No Image', 'fau-elemental' ) }
									</span>
								</div>
							) }
						</button>
					</div>
				) }

				<div className={ CSS_CLASSES.portal_content }>
					<h3 className={ CSS_CLASSES.portal_title }>
						<button
							type="button"
							onClick={ ( e ) => e.preventDefault() }
							className={ CSS_CLASSES.portal_main_link }
							/* translators: %s: Menu item title */
							aria-label={ sprintf(
								__( 'Go to main page: %s', 'fau-elemental' ),
								itemTitle
							) }
							tabIndex="0"
						>
							{ itemTitle }
						</button>
					</h3>

					{ showSubs && (
						<div
							className={ CSS_CLASSES.portal_submenu }
							role="list"
						>
							{ hasChildren &&
								item.children.map( ( child ) => {
									const childTitle =
										child.title?.rendered ||
										child.title ||
										__( 'Submenu Item', 'fau-elemental' );
									return (
										<div
											key={ child.id }
											className={
												CSS_CLASSES.portal_subitem
											}
											role="listitem"
										>
											<button
												type="button"
												onClick={ ( e ) =>
													e.preventDefault()
												}
												className={
													CSS_CLASSES.portal_sublink
												}
												/* translators: %s: Submenu item title */
												aria-label={ sprintf(
													__(
														'Go to %s',
														'fau-elemental'
													),
													childTitle
												) }
												tabIndex="0"
											>
												<span className="portal-link-text">
													{ childTitle }
												</span>
												<span
													className="portal-link-button"
													aria-hidden="true"
												></span>
											</button>
										</div>
									);
								} ) }
						</div>
					) }
				</div>
			</li>
		);
	};

	// Show loading state while menu items are being fetched
	if ( attributes.menuId && ! menuItems ) {
		return (
			<section
				className="wp-block-fau-elemental-portalmenu"
				aria-labelledby="portal-menu-heading"
			>
				<h2
					id="portal-menu-heading"
					className={ CSS_CLASSES.screen_reader_text }
				>
					{ /* translators: %s: Menu name */ }
					{ __( 'Portal Menu:', 'fau-elemental' ) }{ ' ' }
					{ selectedMenuName }
				</h2>
				<nav
					className={ getContentClasses() }
					role="navigation"
					aria-label={ __( 'Portal Menu', 'fau-elemental' ) }
				>
					<div
						className="portal-loading-state"
						role="status"
						aria-live="polite"
					>
						{ __( 'Loading menu items…', 'fau-elemental' ) }
					</div>
				</nav>
			</section>
		);
	}

	// Show message if no menu items found
	if ( attributes.menuId && menuItems && menuItems.length === 0 ) {
		return (
			<section
				className="wp-block-fau-elemental-portalmenu"
				aria-labelledby="portal-menu-heading"
			>
				<h2
					id="portal-menu-heading"
					className={ CSS_CLASSES.screen_reader_text }
				>
					{ /* translators: %s: Menu name */ }
					{ __( 'Portal Menu:', 'fau-elemental' ) }{ ' ' }
					{ selectedMenuName }
				</h2>
				<nav
					className={ getContentClasses() }
					role="navigation"
					aria-label={ __( 'Portal Menu', 'fau-elemental' ) }
				>
					<div
						className="portal-empty-state"
						role="status"
						aria-live="polite"
					>
						{ __(
							'This menu has no items. Please add items to the menu in Appearance → Menus.',
							'fau-elemental'
						) }
					</div>
				</nav>
			</section>
		);
	}

	return (
		<section
			className="wp-block-fau-elemental-portalmenu"
			aria-labelledby="portal-menu-heading"
		>
			{ /* Hidden heading for screen readers - matches frontend exactly */ }
			<h2
				id="portal-menu-heading"
				className={ CSS_CLASSES.screen_reader_text }
			>
				{ /* translators: %s: Menu name */ }
				{ __( 'Portal Menu:', 'fau-elemental' ) } { selectedMenuName }
			</h2>

			{ /* Navigation with semantic markup - matches frontend exactly */ }
			<nav
				className={ getContentClasses() }
				role="navigation"
				aria-label={ __( 'Portal Menu', 'fau-elemental' ) }
			>
				<ul className={ CSS_CLASSES.menu_list }>
					{ menuItems && menuItems.length > 0 ? (
						menuItems.map( ( item ) => renderMenuItem( item ) )
					) : (
						// Fallback to sample items if no menu items
						<li
							className={ `${
								CSS_CLASSES.portal_item
							} ${ getColumnClass() }` }
						>
							<div
								className="portal-no-menu-state"
								role="status"
								aria-live="polite"
							>
								{ __( 'No menu selected', 'fau-elemental' ) }
							</div>
						</li>
					) }
				</ul>
			</nav>
		</section>
	);
};

export default EditorPreview;
