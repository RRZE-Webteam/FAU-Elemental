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
		dark_style: 'is-style-dark'
	};

	// Type configurations that match the backend
	const TYPES = {
		1: { css_class: 'size_2-1', aspect_ratio: '2/1' },
		2: { css_class: 'size_3-2', aspect_ratio: '3/2' },
		3: { css_class: 'size_3-4', aspect_ratio: '3/4' }
	};

	// Fetch actual menu items
	const menuItems = useSelect( ( select ) => {
		if ( ! attributes.menuId ) {
			return [];
		}

		// Get menu items
		const menuItemsData = select( 'core' ).getMenuItems( { 
			menus: parseInt( attributes.menuId ),
			per_page: 100,
			_embed: true 
		} );

		if ( ! menuItemsData ) {
			return [];
		}

		// Build hierarchical menu structure
		const buildMenuTree = ( items, parentId = 0 ) => {
			const children = items.filter( item => item.parent === parentId );
			
			return children.map( item => {
				const childItems = buildMenuTree( items, item.id );
				return {
					...item,
					children: childItems
				};
			} );
		};

		return buildMenuTree( menuItemsData );
	}, [ attributes.menuId ] );

	// Build CSS classes based on attributes - matches backend logic exactly
	const getContentClasses = () => {
		let classes = CSS_CLASSES.container;

		// Add type-specific class
		const type = attributes.type || 1;
		const typeConfig = TYPES[type] || TYPES[1];
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
		// Check if the menu item has a featured image
		if ( item._embedded && item._embedded['wp:featuredmedia'] && item._embedded['wp:featuredmedia'][0] ) {
			const media = item._embedded['wp:featuredmedia'][0];
			if ( media.media_details && media.media_details.sizes ) {
				// Use medium size if available, fallback to full
				const imageSize = media.media_details.sizes.medium || media.media_details.sizes.full;
				if ( imageSize ) {
					return imageSize.source_url;
				}
			}
			return media.source_url;
		}
		return null;
	};

	// Render individual menu item
	const renderMenuItem = ( item, index ) => {
		const hasChildren = item.children && item.children.length > 0;
		const showSubs = attributes.showSubs !== false && hasChildren;
		const itemImage = getMenuItemImage( item );

		return (
			<li key={ item.id } className={ `${ CSS_CLASSES.portal_item } ${ getColumnClass() }` }>
				{ ! attributes.noThumbs && (
					<div className={ CSS_CLASSES.portal_thumbnail }>
						<div className={ CSS_CLASSES.image_link }>
							{ itemImage ? (
								<img 
									src={ itemImage } 
									alt={ item.title?.rendered || '' }
									style={ {
										width: '100%',
										height: '100%',
										objectFit: 'cover'
									} }
								/>
							) : (
								<div
									className="editor-preview-thumbnail"
									style={ {
										width: '100%',
										height: '100%',
										backgroundColor: 'var(--FAU-Col-FAU-Grau-12_5, #f2f2f2)',
										display: 'flex',
										alignItems: 'center',
										justifyContent: 'center',
										color: 'var(--FAU-Col-FAU-Blau-100, #04316a)',
										fontSize: '0.875rem',
										fontWeight: '600'
									} }
								>
									{ __( 'No Image', 'fau-elemental' ) }
								</div>
							) }
						</div>
					</div>
				) }
				<div className={ CSS_CLASSES.portal_content }>
					<div className={ CSS_CLASSES.portal_title }>
						<a
							href="#"
							onClick={ ( e ) => e.preventDefault() }
							className={ CSS_CLASSES.portal_main_link }
						>
							{ item.title?.rendered || item.title || __( 'Menu Item', 'fau-elemental' ) }
							<span className={ CSS_CLASSES.portal_button_arrow }>
								<span className={ CSS_CLASSES.screen_reader_text }>
									{ __( 'Visit page', 'fau-elemental' ) }
								</span>
							</span>
						</a>
					</div>
					{ showSubs && (
						<div className={ CSS_CLASSES.portal_submenu }>
							{ item.children.slice( 0, 3 ).map( ( child, childIndex ) => (
								<div key={ child.id } className={ CSS_CLASSES.portal_subitem }>
									<a
										href="#"
										onClick={ ( e ) => e.preventDefault() }
										className={ CSS_CLASSES.portal_sublink }
									>
										{ child.title?.rendered || child.title || __( 'Submenu Item', 'fau-elemental' ) }
									</a>
								</div>
							) ) }
							{ item.children.length > 3 && (
								<div className={ CSS_CLASSES.portal_subitem }>
									<span className={ CSS_CLASSES.portal_sublink } style={ { opacity: 0.7 } }>
										{ sprintf( 
											__( '... and %d more', 'fau-elemental' ), 
											item.children.length - 3 
										) }
									</span>
								</div>
							) }
						</div>
					) }
				</div>
			</li>
		);
	};

	// Show loading state while menu items are being fetched
	if ( attributes.menuId && ! menuItems ) {
		return (
			<section className="wp-block-fau-elemental-portalmenu" aria-labelledby="portal-menu-heading">
				<h2 id="portal-menu-heading" className={ CSS_CLASSES.screen_reader_text }>
					{ __( 'Portal Menu:', 'fau-elemental' ) } { selectedMenuName }
				</h2>
				<nav className={ getContentClasses() } role="navigation" aria-label={ __( 'Portal Menu', 'fau-elemental' ) }>
					<div style={ {
						padding: 'var(--Spacing-8x, 2rem)',
						textAlign: 'center',
						color: 'var(--FAU-Col-FAU-Blau-100, #04316a)'
					} }>
						{ __( 'Loading menu items...', 'fau-elemental' ) }
					</div>
				</nav>
			</section>
		);
	}

	// Show message if no menu items found
	if ( attributes.menuId && menuItems && menuItems.length === 0 ) {
		return (
			<section className="wp-block-fau-elemental-portalmenu" aria-labelledby="portal-menu-heading">
				<h2 id="portal-menu-heading" className={ CSS_CLASSES.screen_reader_text }>
					{ __( 'Portal Menu:', 'fau-elemental' ) } { selectedMenuName }
				</h2>
				<nav className={ getContentClasses() } role="navigation" aria-label={ __( 'Portal Menu', 'fau-elemental' ) }>
					<div style={ {
						padding: 'var(--Spacing-8x, 2rem)',
						textAlign: 'center',
						color: 'var(--FAU-Col-FAU-Blau-100, #04316a)',
						fontStyle: 'italic'
					} }>
						{ __( 'This menu has no items. Please add items to the menu in Appearance → Menus.', 'fau-elemental' ) }
					</div>
				</nav>
			</section>
		);
	}

	return (
		<section className="wp-block-fau-elemental-portalmenu" aria-labelledby="portal-menu-heading">
			{/* Hidden heading for screen readers - matches frontend exactly */}
			<h2 id="portal-menu-heading" className={ CSS_CLASSES.screen_reader_text }>
				{ __( 'Portal Menu:', 'fau-elemental' ) } { selectedMenuName }
			</h2>

			{/* Navigation with semantic markup - matches frontend exactly */}
			<nav className={ getContentClasses() } role="navigation" aria-label={ __( 'Portal Menu', 'fau-elemental' ) }>
				<ul className={ CSS_CLASSES.menu_list }>
					{ menuItems && menuItems.length > 0 ? (
						menuItems.map( ( item, index ) => renderMenuItem( item, index ) )
					) : (
						// Fallback to sample items if no menu items
						<li className={ `${ CSS_CLASSES.portal_item } ${ getColumnClass() }` }>
							<div style={ {
								padding: 'var(--Spacing-6x, 1.5rem)',
								textAlign: 'center',
								color: 'var(--FAU-Col-FAU-Schwarz-75, #666)',
								fontStyle: 'italic'
							} }>
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
