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
    }

    /**
     * Render the FAU navigation
     */
    public function render() {
        // Include the unified menu modal configuration
        require_once get_template_directory() . '/src/components/navigation/menu-modal-config.php';
        
        // Check for Services and Structure menus using unified system
        $has_services = fau_elemental_has_services_menu();
        $has_structure = fau_elemental_has_structure_menu();
        ?>
        <nav class="fau-navigation" role="navigation" aria-label="<?php esc_attr_e('FAU Navigation', 'fau-elemental'); ?>">
            <div class="fau-navigation__container">
                <div class="fau-navigation__left">
                    <a href="https://www.fau.de" class="fau-navigation__fau-link">
                        <span class="fau-navigation__back-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="currentColor"/>
                            </svg>
                        </span>
                        FAU.de
                    </a>
                </div>

                <div class="fau-navigation__right">
                    <?php
                    if (has_nav_menu('fau_top_navigation')) {
                        wp_nav_menu(array(
                            'theme_location' => 'fau_top_navigation',
                            'menu_class' => 'fau-navigation__menu',
                            'container' => false,
                            'fallback_cb' => false,
                            'depth' => 2,
                            'walker' => new FAU_Navigation_Walker(),
                        ));
                    } else {
                        // Fallback menu items if no menu is set
                        ?>
                        <ul class="fau-navigation__menu">
                            <?php if ($has_services): ?>
                                <li class="menu-item">
                                    <button class="fau-navigation__button menu-modal__open-btn"
                                        data-modal-target="services-modal"
                                        aria-label="Services"
                                        aria-expanded="false">
                                        <span class="fau-navigation__icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z" fill="currentColor"/>
                                            </svg>
                                        </span>
                                        Services
                                    </button>
                                </li>
                            <?php endif; ?>
                            <?php if ($has_structure): ?>
                                <li class="menu-item">
                                    <button class="fau-navigation__button menu-modal__open-btn"
                                        data-modal-target="structure-modal"
                                        aria-label="Structure"
                                        aria-expanded="false">
                                        <span class="fau-navigation__icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 11H9V5H4V11ZM4 18H9V12H4V18ZM10 18H15V12H10V18ZM16 18H21V12H16V18ZM10 11H15V5H10V11ZM16 5V11H21V5H16Z" fill="currentColor"/>
                                            </svg>
                                        </span>
                                        Structure
                                    </button>
                                </li>
                            <?php endif; ?>
                            <li class="menu-item">
                                <button class="fau-navigation__button" aria-label="Search" aria-expanded="false">
                                    <span class="fau-navigation__icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.5 14H14.71L14.43 13.73C15.41 12.59 16 11.11 16 9.5C16 5.91 13.09 3 9.5 3C5.91 3 3 5.91 3 9.5C3 13.09 5.91 16 9.5 16C11.11 16 12.59 15.41 13.73 14.43L14 14.71V15.5L19 20.49L20.49 19L15.5 14ZM9.5 14C7.01 14 5 11.99 5 9.5C5 7.01 7.01 5 9.5 5C11.99 5 14 7.01 14 9.5C14 11.99 11.99 14 9.5 14Z" fill="currentColor"/>
                                        </svg>
                                    </span>
                                    Search
                                </button>
                            </li>
                            <li class="menu-item">
                                <button class="fau-navigation__button" aria-label="Language" aria-expanded="false">
                                    DE
                                    <span class="fau-navigation__icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M7 10L12 15L17 10H7Z" fill="currentColor"/>
                                        </svg>
                                    </span>
                                </button>
                            </li>
                        </ul>
                        <?php
                    }
                    ?>
                </div>
            </div>
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

// Initialize the component
$fau_navigation = new FAU_Navigation(); 