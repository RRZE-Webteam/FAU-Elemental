<?php
/**
 * Menu Website Modal Component
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Menu Website Modal Component Class
 */
class Menu_Website_Modal {
    /**
     * Initialize the component
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render'));
    }

    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('menu-website-modal', get_template_directory_uri() . '/src/components/navigation/menu-website.scss');
        wp_enqueue_script('menu-website-modal', get_template_directory_uri() . '/src/components/navigation/menu-website.js', array('jquery'), '1.0.0', true);
    }

    /**
     * Render the menu website modal
     */
    public function render() {
        ?>
        <div class="menu-website-modal" id="menu-website-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Website Menu', 'fau-elemental'); ?>" hidden>
            <div class="menu-website-modal__overlay"></div>
            <div class="menu-website-modal__container">
                <div class="menu-website-modal__header">
                    <button class="menu-website-modal__back" aria-label="<?php esc_attr_e('Back to main menu', 'fau-elemental'); ?>" style="display: none;">
                        <svg class="menu-website-modal__back-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="currentColor"/>
                        </svg>
                        <span class="menu-website-modal__back-text"><?php esc_html_e('Back', 'fau-elemental'); ?></span>
                    </button>
                    <button class="menu-website-modal__close" aria-label="<?php esc_attr_e('Close menu', 'fau-elemental'); ?>">
                        <svg class="menu-website-modal__close-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                <div class="menu-website-modal__content">
                    <nav class="menu-website-modal__main-nav" role="navigation" aria-label="<?php esc_attr_e('Main Navigation', 'fau-elemental'); ?>">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'primary',
                            'menu_class'     => 'menu-website-modal__menu',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'depth'          => 0,
                            'walker'         => new FAU_Menu_First_Only_Walker(),
                        ));
                        ?>
                    </nav>

                    <nav class="menu-website-modal__secondary-nav" role="navigation" aria-label="<?php esc_attr_e('Secondary Links', 'fau-elemental'); ?>">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'secondary_links',
                            'menu_class'     => 'menu-website-modal__secondary-menu',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'depth'          => 1,
                        ));
                        ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the component
$menu_website_modal = new Menu_Website_Modal();

class FAU_Menu_First_Only_Walker extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '<ul class="sub-menu" style="display: none;">';
    }

    function end_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '</ul>';
    }

    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . '>';
        
        // Add the link
        $output .= $args->before . '<a href="' . esc_attr($item->url) . '">' . apply_filters('the_title', $item->title, $item->ID) . '</a>' . $args->after;

        // Add toggle button for items with children
        if (in_array('menu-item-has-children', $classes)) {
            $output .= '<button class="menu-website-modal__submenu-toggle" aria-expanded="false" aria-label="' . esc_attr($item->title) . ' submenu">';
            $output .= '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 5L12 10L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            $output .= '</button>';
        }
    }

    function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= '</li>';
    }
} 