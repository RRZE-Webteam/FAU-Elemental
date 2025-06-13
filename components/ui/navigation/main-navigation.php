<?php
/**
 * Main Navigation Component
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom Walker for Main Navigation Direct Links
 * Handles items with children by making them modal triggers
 */
class Main_Navigation_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = array()) {
        // We don't render submenus in the direct links area
    }

    public function end_lvl(&$output, $depth = 0, $args = array()) {
        // We don't render submenus in the direct links area
    }

    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        // Only process top-level items (depth = 0)
        if ($depth > 0) {
            return;
        }

        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'main-navigation__direct-item';
        
        $has_children = in_array('menu-item-has-children', $classes);
        
        if ($has_children) {
            $classes[] = 'has-children';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . '>';
        
        if ($has_children) {
            // For items with children, create a button that opens the modal to this specific item
            $output .= '<button class="main-navigation__direct-link main-navigation__modal-trigger menu-modal__open-btn" ';
            $output .= 'data-modal-target="menu-website-modal" ';
            $output .= 'data-target-item="' . esc_attr($item->ID) . '" ';
            $output .= 'data-target-url="' . esc_attr($item->url) . '" ';
            $output .= 'aria-expanded="false" ';
            $output .= 'aria-label="' . esc_attr(sprintf(__('Open %s menu', 'fau-elemental'), $item->title)) . '">';
            $output .= apply_filters('the_title', $item->title, $item->ID);
            $output .= '<svg class="main-navigation__dropdown-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">';
            $output .= '<path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
            $output .= '</svg>';
            $output .= '</button>';
        } else {
            // For items without children, use regular links
            $output .= '<a href="' . esc_attr($item->url) . '" class="main-navigation__direct-link">';
            $output .= apply_filters('the_title', $item->title, $item->ID);
            $output .= '</a>';
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = array()) {
        if ($depth === 0) {
            $output .= '</li>';
        }
    }
}

/**
 * Main Navigation Component Class
 */
class Main_Navigation {
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
        // JavaScript is handled by the unified menu-modal system
    }

    /**
     * Render the main navigation
     */
    public function render() {
        // Ensure the logo display function exists
        if (!function_exists('fau_elemental_display_logo_title')) {
            require_once get_template_directory() . '/inc/logo-display.php';
        }

        // Check if website menu exists using unified system
        $has_website_menu = fau_elemental_has_website_menu();
        ?>
        <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Main Navigation', 'fau-elemental'); ?>">
            <div class="main-navigation__container">
                <div class="main-navigation__brand">
                    <div class="main-navigation__logo">
                        <?php fau_elemental_display_logo_title(); ?>
                    </div>
                </div>

                <div class="main-navigation__direct-links">
                    <?php
                    // Check if primary menu exists
                    if (has_nav_menu('header_primary_menu')) {
                        wp_nav_menu(array(
                            'theme_location' => 'header_primary_menu',
                            'menu_id'        => 'main-direct-links',
                            'menu_class'     => 'main-navigation__direct-menu',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'depth'          => 1, // Only show top-level items in direct links
                            'walker'         => new Main_Navigation_Walker(),
                        ));
                    }
                    ?>
                </div>

                <div class="main-navigation__menu-container">
                    <?php if ($has_website_menu): ?>
                        <button class="main-navigation__toggle menu-modal__open-btn" aria-expanded="false" aria-controls="menu-website-modal" data-modal-target="menu-website-modal">
                            <span class="main-navigation__toggle-text">Menü</span>
                            <span class="main-navigation__toggle-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <?php
    }
} 