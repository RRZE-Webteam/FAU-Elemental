<?php
/**
 * Menu Meta Nav Modal Component
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

class Menu_Meta_Nav_Modal {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('menu-meta-nav', get_template_directory_uri() . '/src/components/navigation/menu-meta-nav.scss');
        wp_enqueue_script('menu-meta-nav', get_template_directory_uri() . '/src/components/navigation/menu-meta-nav.js', array('jquery'), '1.0.0', true);
    }

    /**
     * Get menu from main site
     *
     * @param string $theme_location The theme location to get the menu for
     * @return array|false The menu items or false if not found
     */
    public function get_main_site_menu($theme_location) {
        if (!is_multisite()) {
            return false;
        }

        $main_site_id = get_main_site_id();
        if (!$main_site_id) {
            return false;
        }

        // Switch to main site
        switch_to_blog($main_site_id);

        // Get the menu location
        $locations = get_nav_menu_locations();
        if (!isset($locations[$theme_location])) {
            restore_current_blog();
            return false;
        }

        // Get the menu
        $menu_id = $locations[$theme_location];
        $menu_items = wp_get_nav_menu_items($menu_id);

        // Switch back to current site
        restore_current_blog();

        return $menu_items;
    }

    /**
     * Build menu HTML from menu items
     *
     * @param array $menu_items The menu items to build the menu from
     * @param string $menu_class The class to add to the menu
     * @return string The menu HTML
     */
    private function build_menu_html($menu_items, $menu_class) {
        if (empty($menu_items)) {
            return '';
        }

        $walker = new Menu_Meta_Nav_Walker();
        $output = '';
        
        // Start the menu
        $output .= '<ul class="' . esc_attr($menu_class) . '">';
        
        // Build the menu structure
        $parent_items = array_filter($menu_items, function($item) {
            return $item->menu_item_parent == 0;
        });

        foreach ($parent_items as $parent) {
            // Start parent item
            $walker->start_el($output, $parent, 0, (object)['before' => '', 'after' => '']);
            
            // Get child items
            $child_items = array_filter($menu_items, function($item) use ($parent) {
                return $item->menu_item_parent == $parent->ID;
            });

            if (!empty($child_items)) {
                // Start submenu
                $walker->start_lvl($output, 0);
                
                foreach ($child_items as $child) {
                    $walker->start_el($output, $child, 1, (object)['before' => '', 'after' => '']);
                    $walker->end_el($output, $child, 1);
                }
                
                $walker->end_lvl($output, 0);
            }
            
            $walker->end_el($output, $parent, 0);
        }
        
        // End the menu
        $output .= '</ul>';
        
        return $output;
    }

    public function render() {
        ?>
        <div class="menu-meta-nav">
            <!-- Services Modal -->
            <div id="services-modal" class="menu-meta-nav__modal" style="display: none;" tabindex="-1" aria-modal="true" role="dialog" aria-hidden="true">
                <div class="menu-meta-nav__modal-content">
                    <button class="menu-meta-nav__close-btn" data-meta-modal-close="services" aria-label="Close Services Menu">&times;</button>
                    <?php
                    // Get menu items from main site
                    $services_menu_items = $this->get_main_site_menu('meta_navigation_services');
                    
                    if ($services_menu_items) {
                        echo $this->build_menu_html($services_menu_items, 'menu-meta-nav__menu menu-meta-nav__menu--services');
                    } else {
                        // Fallback to current site menu if main site menu not found
                        wp_nav_menu(array(
                            'theme_location' => 'meta_navigation_services',
                            'menu_class'     => 'menu-meta-nav__menu menu-meta-nav__menu--services',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'depth'          => 3,
                            'walker'         => new Menu_Meta_Nav_Walker(),
                        ));
                    }
                    ?>
                </div>
            </div>

            <!-- Structure Modal -->
            <div id="structure-modal" class="menu-meta-nav__modal" style="display: none;" tabindex="-1" aria-modal="true" role="dialog" aria-hidden="true">
                <div class="menu-meta-nav__modal-content">
                    <button class="menu-meta-nav__close-btn" data-meta-modal-close="structure" aria-label="Close Structure Menu">&times;</button>
                    <?php
                    // Get menu items from main site
                    $structure_menu_items = $this->get_main_site_menu('meta_navigation_structure');
                    
                    if ($structure_menu_items) {
                        echo $this->build_menu_html($structure_menu_items, 'menu-meta-nav__menu menu-meta-nav__menu--structure');
                    } else {
                        // Fallback to current site menu if main site menu not found
                        wp_nav_menu(array(
                            'theme_location' => 'meta_navigation_structure',
                            'menu_class'     => 'menu-meta-nav__menu menu-meta-nav__menu--structure',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'depth'          => 3,
                            'walker'         => new Menu_Meta_Nav_Walker(),
                        ));
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}

class Menu_Meta_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="sub-menu" style="display: none;">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . '>';
        
        // Add the link
        $output .= $args->before . '<a href="' . esc_attr($item->url) . '">' . apply_filters('the_title', $item->title, $item->ID) . '</a>' . $args->after;

        // Add toggle button for items with children
        if (in_array('menu-item-has-children', $classes)) {
            $output .= '<button class="menu-meta-nav__submenu-toggle" aria-expanded="false" aria-label="' . esc_attr($item->title) . ' submenu">';
            $output .= '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 5L12 10L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            $output .= '</button>';
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}

// Initialize the component
$menu_meta_nav_modal = new Menu_Meta_Nav_Modal();
