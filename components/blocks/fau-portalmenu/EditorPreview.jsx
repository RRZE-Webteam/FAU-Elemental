/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

// TODO Get the theme URL from WordPress data
const FALLBACK_IMAGE =
	'/wp-content/themes/fau-elemental/assets/images/logo.svg';

/**
 * Editor preview component for FAU Portal Menu block
 * Matches the frontend WCAG 2.2 Level II compliant structure exactly
 * Shows actual menu items from the selected menu
 */
const EditorPreview = ( { attributes } ) => {
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
			<div key={ item.id } className="fau-portal-item">
				{ ! attributes.noThumbs && (
					<div className="fau-portal-thumbnail">
						{ itemImage ? (
							<img
								src={ itemImage }
								alt={ sprintf(
									// translators: %s: Menu item title
									__(
										'Featured image for %s',
										'fau-elemental'
									),
									itemTitle
								) }
								loading="lazy"
							/>
						) : (
							<img
								src={ FALLBACK_IMAGE }
								alt={ sprintf(
									// translators: %s: Menu item title
									__(
										'No image available for %s',
										'fau-elemental'
									),
									itemTitle
								) }
								loading="lazy"
							/>
						) }
					</div>
				) }

				<div className="fau-portal-wrapper">
					<div className="fau-portal-content">
						{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid */ }
						<a
							role="link"
							aria-label={ sprintf(
								// translators: %s: Menu item title
								__( 'Go to %s', 'fau-elemental' ),
								itemTitle
							) }
							tabIndex="0"
							onClick={ ( e ) => e.preventDefault() }
							onKeyDown={ ( e ) => {
								if ( e.key === 'Enter' || e.key === ' ' ) {
									e.preventDefault();
								}
							} }
						>
							<h3>{ itemTitle }</h3>
							<span></span>
						</a>

						{ showSubs && hasChildren && (
							<ul>
								{ item.children.map( ( child ) => {
									const childTitle =
										child.title?.rendered ||
										child.title ||
										__( 'Submenu Item', 'fau-elemental' );
									return (
										<li key={ child.id }>
											{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid */ }
											<a
												role="link"
												aria-label={ sprintf(
													// translators: %s: Submenu item title
													__(
														'Go to %s',
														'fau-elemental'
													),
													childTitle
												) }
												tabIndex="0"
												onClick={ ( e ) =>
													e.preventDefault()
												}
												onKeyDown={ ( e ) => {
													if (
														e.key === 'Enter' ||
														e.key === ' '
													) {
														e.preventDefault();
													}
												} }
											>
												{ childTitle }
											</a>
										</li>
									);
								} ) }
							</ul>
						) }
					</div>
				</div>
			</div>
		);
	};

	return (
		<div
			className={ `wp-block-group${
				attributes.isDark ? ' is-style-dark' : ''
			}` }
		>
			<div
				className="fau-portal-menu"
				aria-label={ __( 'Portal Menu', 'fau-elemental' ) }
			>
				{ menuItems && menuItems.length > 0 ? (
					menuItems.map( ( item ) => renderMenuItem( item ) )
				) : menuItems && menuItems.length === 0 ? (
					<div
						className="fau-portal-empty-state"
						role="status"
						aria-live="polite"
					>
						{ __(
							'This menu has no items. Please add items to the menu in Appearance → Menus.',
							'fau-elemental'
						) }
					</div>
				) : attributes.menuId && ! menuItems ? (
					<div
						className="fau-portal-loading-state"
						role="status"
						aria-live="polite"
					>
						{ __( 'Loading menu items…', 'fau-elemental' ) }
					</div>
				) : (
					<div
						className="fau-portal-no-menu-state"
						role="status"
						aria-live="polite"
					>
						{ __( 'No menu selected', 'fau-elemental' ) }
					</div>
				) }
			</div>
		</div>
	);
};

export default EditorPreview;
