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
                            'walker'         => new FAU_Menu_With_Children_Walker(),
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

class FAU_Menu_With_Children_Walker extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '<ul class="sub-menu menu-children" style="display: none;">';
    }

    function end_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '</ul>';
    }

    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        
        // Add menu-child-item class for items that are not at the top level
        if ($depth > 0) {
            $classes[] = 'menu-child-item';
        }
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . '>';
        $output .= $args->before . '<a href="' . esc_attr($item->url) . '">' . apply_filters('the_title', $item->title, $item->ID) . '</a>' . $args->after;

        // Handle both menu children and page children
        $has_menu_children = in_array('menu-item-has-children', $classes);
        $has_page_children = false;
        
        // Check if this page has children
        if ($item->object === 'page') {
            $page_children = get_pages(array(
                'child_of' => $item->object_id,
                'parent' => $item->object_id,
                'numberposts' => 1
            ));
            $has_page_children = !empty($page_children);
        }
        
        // If this item has any children (menu or page), add a single toggle
        if ($has_menu_children || $has_page_children) {
            $output .= '<button class="menu-website-modal__submenu-toggle" aria-expanded="false" aria-label="' . esc_attr($item->title) . ' submenu">';
            $output .= '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 5L12 10L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            $output .= '</button>';
        }
        
        // If this is a page with children, render page children (they will be mixed with menu children)
        if ($has_page_children) {
            $this->render_page_children($output, $item->object_id, $item->title, 'page-children', true);
        }
    }

    function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= '</li>';
    }

    // Recursive function to render children of a page
    private function render_page_children( &$output, $parent_id, $parent_title, $class = 'page-children', $mixed_with_menu = false ) {
        $children = get_pages(array(
            'child_of' => $parent_id,
            'parent' => $parent_id,
            'sort_column' => 'menu_order',
            'sort_order' => 'ASC'
        ));
        if (!empty($children)) {
            // Only add toggle button if not mixed with menu children
            if (!$mixed_with_menu) {
                $output .= '<button class="menu-website-modal__submenu-toggle page-children-toggle" aria-expanded="false" aria-label="' . esc_attr($parent_title) . ' page submenu">';
                $output .= '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 5L12 10L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                $output .= '</button>';
            }
            $output .= '<ul class="sub-menu ' . esc_attr($class) . '" style="display: none;">';
            foreach ($children as $child) {
                $child_classes = 'menu-item page-child-item';
                // Check if this child has children
                $has_children = get_pages(array(
                    'child_of' => $child->ID,
                    'parent' => $child->ID,
                    'numberposts' => 1
                ));
                if (!empty($has_children)) {
                    $child_classes .= ' menu-item-has-children';
                }
                $output .= '<li class="' . esc_attr($child_classes) . '">';
                $output .= '<a href="' . esc_url(get_permalink($child->ID)) . '">' . esc_html($child->post_title) . '</a>';
                // Recursively render children (these will have their own toggles)
                $this->render_page_children($output, $child->ID, $child->post_title);
                $output .= '</li>';
            }
            $output .= '</ul>';
        }
    }
}

// Initialize the component
$menu_website_modal = new Menu_Website_Modal(); 