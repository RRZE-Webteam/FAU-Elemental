<?php
/**
 * FAU Navigation Component (Top Navigation)
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FAU Navigation Component Class
 */
class FAU_Navigation {
    /**
     * Initialize the component
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueue_scripts() {
        // Note: CSS is now handled by the main theme enqueue function (inc/enqueue-assets.php)
    }

    /**
     * Render the FAU navigation
     */
    public function render() {
        // Check for Services and Structure menus using unified system
        $has_services = fau_elemental_has_services_menu();
        $has_structure = fau_elemental_has_structure_menu();
        $website_type = get_theme_mod('faue_website_type', 'fau');
        
        ?>
        <nav class="fau-navigation" role="navigation" aria-label="<?php esc_attr_e('FAU Navigation', 'fau-elemental'); ?>">
            <?php if ($website_type !== 'fau'): ?>
            <a href="https://www.fau.de" class="fau-navigation__fau-link">
                <span class="fau-navigation__back-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="currentColor"/>
                    </svg>
                </span>
                FAU.de
            </a>
            <?php endif; ?>
            <div class="fau-nav-modals">
                <?php
                {
                    // Fallback menu items if no menu is set
                ?>
                <?php if ($has_services): ?>
                    <button class="fau-navigation__button menu-modal__open-btn"
                        data-modal-target="services-modal"
                        aria-label="Services"
                        aria-expanded="false">
                        Services
                        <span class="fau-navigation__services-icon"></span>
                    </button>
                <?php endif; ?>
                <?php if ($has_structure): ?>
                    <button class="fau-navigation__button menu-modal__open-btn"
                        data-modal-target="structure-modal"
                        aria-label="Structure"
                        aria-expanded="false">
                        Structure
                        <span class="fau-navigation__structure-icon"></span>
                    </button>
                <?php endif; ?>
                    <button class="fau-navigation__button" aria-label="Search" aria-expanded="false">
                        Search
                        <span class="fau-navigation__search-icon"></span>
                    </button>
                <?php
                }
                ?>
            </div>
            <button class="language-switcher fau-navigation__button" aria-label="Language" aria-expanded="false">
                DE
                <span class="fau-navigation__language-switcher-icon"></span>
            </button>
        </nav>
        <?php
    }
}

/**
 * Custom Walker for FAU Navigation
 */
class FAU_Navigation_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = array()) {
        $output .= '<ul class="sub-menu">';
    }

    public function end_lvl(&$output, $depth = 0, $args = array()) {
        $output .= '</ul>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item';
        
        if (in_array('menu-item-has-children', $classes)) {
            $classes[] = 'has-children';
        }

        $output .= '<li class="' . esc_attr(implode(' ', $classes)) . '">';
        
        if ($depth > 0) {
            // For submenu items, use a link
            $output .= '<a href="' . esc_url($item->url) . '" class="fau-navigation__link">';
            $output .= esc_html($item->title);
            $output .= '</a>';
        } else {
            // For top-level items with children, use a button
            if (in_array('menu-item-has-children', $classes)) {
                $output .= '<button class="fau-navigation__button" aria-label="' . esc_attr($item->title) . '" aria-expanded="false">';
                $output .= esc_html($item->title);
                $output .= '</button>';
            } else {
                // For top-level items without children, use a link
                $output .= '<a href="' . esc_url($item->url) . '" class="fau-navigation__link">';
                $output .= esc_html($item->title);
                $output .= '</a>';
            }
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = array()) {
        $output .= '</li>';
    }
} 