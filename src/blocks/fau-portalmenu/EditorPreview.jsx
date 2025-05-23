/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Button } from '@wordpress/components';

/**
 * Editor preview component for FAU Portal Menu block
 * Designed to closely match the frontend appearance
 */
const EditorPreview = ({ attributes, selectedMenuName }) => {
    // Fetch actual menu items
    const { menuItems, isLoading } = useSelect((select) => {
        if (!attributes.menuId) {
            return { menuItems: [], isLoading: false };
        }

        const items = select('core').getMenuItems();
        
        if (!items) {
            return { menuItems: [], isLoading: true };
        }

        // Filter items for the selected menu and organize them hierarchically
        const menuId = parseInt(attributes.menuId);
        
        const menuItems = items.filter(item => {
            // Handle different possible data structures for menu association
            if (Array.isArray(item.menus)) {
                return item.menus.includes(menuId);
            } else if (typeof item.menus === 'number') {
                return item.menus === menuId;
            } else if (typeof item.menus === 'string') {
                return parseInt(item.menus) === menuId;
            } else if (item.menu && typeof item.menu === 'number') {
                return item.menu === menuId;
            } else if (item.menu_id && typeof item.menu_id === 'number') {
                return item.menu_id === menuId;
            }
            // Fallback: check if item has meta or other properties
            return false;
        }).sort((a, b) => (a.menu_order || 0) - (b.menu_order || 0));

        // Organize into parent/child structure
        const parentItems = menuItems.filter(item => (item.parent || 0) === 0);
        const childItems = menuItems.filter(item => (item.parent || 0) !== 0);

        const structuredItems = parentItems.map(parent => ({
            ...parent,
            children: childItems
                .filter(child => (child.parent || 0) === parent.id)
                .sort((a, b) => (a.menu_order || 0) - (b.menu_order || 0))
        }));

        return { 
            menuItems: structuredItems, 
            isLoading: false 
        };
    }, [attributes.menuId]);

    // Build CSS classes based on attributes
    const getContentClasses = () => {
        let classes = 'contentmenu';
        
        // Add size class based on type
        if (attributes.type === 1) {
            classes += ' size_2-1';
        } else if (attributes.type === 2) {
            classes += ' size_3-2';
        } else if (attributes.type === 3) {
            classes += ' size_3-4';
        }
        
        // Add optional classes
        if (attributes.listView) {
            classes += ' listview';
        }
        if (attributes.noThumbs) {
            classes += ' no-thumb';
        }
        if (attributes.hoverZoom) {
            classes += ' hover-zoom';
        }
        if (attributes.hoverBlur) {
            classes += ' hover-blur';
        }
        
        return classes;
    };

    // Inline styles for editor-only elements
    const menuNoticeStyle = {
        marginTop: '15px',
        padding: '10px',
        background: '#f9f9f9',
        borderLeft: '4px solid #0073aa',
        fontSize: '0.9em'
    };

    const placeholderStyle = {
        width: '100%',
        height: '200px',
        background: 'linear-gradient(135deg, #f1f1f1 25%, #e5e5e5 25%, #e5e5e5 50%, #f1f1f1 50%, #f1f1f1 75%, #e5e5e5 75%)',
        backgroundSize: '20px 20px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: '#666',
        fontSize: '14px'
    };

    // Get featured image URL for menu item
    const getFeaturedImageUrl = (item) => {
        if (item.featured_media && item.featured_media > 0) {
            // In a real implementation, you'd fetch the media details
            // For now, we'll use a placeholder
            return null;
        }
        return null;
    };

    // Get title from menu item (handle different possible formats)
    const getMenuItemTitle = (item) => {
        if (typeof item.title === 'object' && item.title.rendered) {
            return item.title.rendered;
        } else if (typeof item.title === 'string') {
            return item.title;
        } else if (item.post_title) {
            return item.post_title;
        } else {
            return __('Untitled', 'fau-elemental');
        }
    };

    // Render individual menu item
    const renderMenuItem = (item, index) => (
        <li key={item.id || index} className="portal-item portal-column-3">
            {!attributes.noThumbs && (
                <div className="portal-thumbnail">
                    <div className="image-link">
                        {getFeaturedImageUrl(item) ? (
                            <img 
                                src={getFeaturedImageUrl(item)} 
                                alt={getMenuItemTitle(item)}
                                style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                            />
                        ) : (
                            <div style={placeholderStyle}>
                                {__('No Image', 'fau-elemental')}
                            </div>
                        )}
                    </div>
                </div>
            )}
            <div className="portal-content">
                <div className="portal-title">
                    <h2 className="portal-main-title">{getMenuItemTitle(item)}</h2>
                    <Button
                        variant="primary"
                        className="portal-main-button"
                        onClick={(e) => e.preventDefault()}
                        icon="arrow-right-alt"
                        label={__('Go to page', 'fau-elemental')}
                        showTooltip={true}
                    />
                </div>
                {attributes.showSubs !== false && item.children && item.children.length > 0 && (
                    <ul className="portal-submenu">
                        {item.children.map((child, childIndex) => (
                            <li key={child.id || childIndex} className="portal-subitem">
                                <Button
                                    variant="link"
                                    className="portal-sublink"
                                    onClick={(e) => e.preventDefault()}
                                    icon="arrow-right-alt"
                                    iconPosition="right"
                                >
                                    {getMenuItemTitle(child)}
                                </Button>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </li>
    );

    if (isLoading) {
        return (
            <div className="wp-block-fau-elemental-portalmenu">
                <div className={getContentClasses()}>
                    <div style={{ padding: '20px', textAlign: 'center', color: '#666' }}>
                        {__('Loading menu items...', 'fau-elemental')}
                    </div>
                </div>
            </div>
        );
    }

    if (!menuItems || menuItems.length === 0) {
        return (
            <div className="wp-block-fau-elemental-portalmenu">
                <div className={getContentClasses()}>
                    <div style={{ padding: '20px', textAlign: 'center', color: '#666' }}>
                        {__('No menu items found. Please check that the selected menu has items.', 'fau-elemental')}
                    </div>
                    <div className="menu-notice" style={menuNoticeStyle}>
                        <p style={{margin: 0}}>
                            {__('Selected menu:', 'fau-elemental')} <strong style={{color: '#0073aa'}}>{selectedMenuName}</strong>
                        </p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="wp-block-fau-elemental-portalmenu">
            <div className={getContentClasses()}>
                <ul className="subpages-menu">
                    {menuItems.map((item, index) => renderMenuItem(item, index))}
                </ul>
                <div className="menu-notice" style={menuNoticeStyle}>
                    <p style={{margin: 0}}>
                        {__('Showing', 'fau-elemental')} <strong style={{color: '#0073aa'}}>{menuItems.length}</strong> {__('items from menu:', 'fau-elemental')} <strong style={{color: '#0073aa'}}>{selectedMenuName}</strong>
                    </p>
                </div>
            </div>
        </div>
    );
};

export default EditorPreview; 