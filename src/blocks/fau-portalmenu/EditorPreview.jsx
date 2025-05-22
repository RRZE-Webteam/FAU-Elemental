/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Editor preview component for FAU Portal Menu block
 * Designed to closely match the frontend appearance
 */
const EditorPreview = ({ attributes, selectedMenuName }) => {
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

    return (
        <div className="wp-block-fau-elemental-portalmenu">
            <div className={getContentClasses()}>
                <ul className="subpages-menu">
                    {/* Sample portal items that match frontend appearance */}
                    <li className="portal-item portal-column-2">
                        {!attributes.noThumbs && (
                            <div className="portal-thumbnail">
                                <div className="image-link">
                                    <div className="thumbnail placeholder" style={{ width: '100%', height: '100%', backgroundColor: '#f2f2f2' }}></div>
                                </div>
                            </div>
                        )}
                        <div className="portal-content">
                            <h2 className="portal-title">
                                <a href="#" onClick={(e) => e.preventDefault()} className="portal-main-link">
                                    {__('Sample Menu Item 1', 'fau-elemental')}
                                    <span className="portal-button-arrow">
                                        <span className="screen-reader-text">{__('Go to page', 'fau-elemental')}</span>
                                    </span>
                                </a>
                            </h2>
                            {attributes.showSubs !== false && (
                                <ul className="portal-submenu">
                                    <li className="portal-subitem">
                                        <a href="#" onClick={(e) => e.preventDefault()} className="portal-sublink">
                                            {__('Submenu Item 1', 'fau-elemental')}
                                        </a>
                                    </li>
                                    <li className="portal-subitem">
                                        <a href="#" onClick={(e) => e.preventDefault()} className="portal-sublink">
                                            {__('Submenu Item 2', 'fau-elemental')}
                                        </a>
                                    </li>
                                </ul>
                            )}
                        </div>
                    </li>
                    <li className="portal-item portal-column-2">
                        {!attributes.noThumbs && (
                            <div className="portal-thumbnail">
                                <div className="image-link">
                                    <div className="thumbnail placeholder" style={{ width: '100%', height: '100%', backgroundColor: '#f2f2f2' }}></div>
                                </div>
                            </div>
                        )}
                        <div className="portal-content">
                            <h2 className="portal-title">
                                <a href="#" onClick={(e) => e.preventDefault()} className="portal-main-link">
                                    {__('Sample Menu Item 2', 'fau-elemental')}
                                    <span className="portal-button-arrow">
                                        <span className="screen-reader-text">{__('Go to page', 'fau-elemental')}</span>
                                    </span>
                                </a>
                            </h2>
                            {attributes.showSubs !== false && (
                                <ul className="portal-submenu">
                                    <li className="portal-subitem">
                                        <a href="#" onClick={(e) => e.preventDefault()} className="portal-sublink">
                                            {__('Submenu Item 1', 'fau-elemental')}
                                        </a>
                                    </li>
                                </ul>
                            )}
                        </div>
                    </li>
                </ul>
                <div className="menu-notice">
                    <p>{__('This is a preview of', 'fau-elemental')} <strong>{selectedMenuName}</strong>. {__('The actual menu items will display on the frontend.', 'fau-elemental')}</p>
                </div>
            </div>
        </div>
    );
};

export default EditorPreview; 