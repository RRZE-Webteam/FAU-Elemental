<?php
/**
 * Mixed Navigation Walker
 * Handles navigation with both menu items and page hierarchies
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mixed Navigation Walker Class
 * Extends the menu modal walker to support page children
 */
class Mixed_Navigation_Walker extends Walker_Nav_Menu {
    
    /**
     * All menu items for reference
     * @var array
     */
    private $all_menu_items = array();
    
    /**
     * Stack to track parent items for each depth level
     * @var array
     */
    private $parent_stack = array();
    
    /**
     * Constructor - initialize menu items
     */
    public function __construct() {
        // Initialize menu items from current menu location
        $this->initialize_menu_items();
    }
    
    /**
     * Initialize menu items from current menu location
     */
    private function initialize_menu_items() {
        $locations = get_nav_menu_locations();
        
        // Try to find current menu from common locations
        foreach (array('header_primary_menu', 'header_menu_links', 'primary') as $location) {
            if (isset($locations[$location])) {
                $this->all_menu_items = wp_get_nav_menu_items($locations[$location]);
                if (!empty($this->all_menu_items)) {
                    break;
                }
            }
        }
        
        // If still empty, try to get from any available menu
        if (empty($this->all_menu_items)) {
            $menus = wp_get_nav_menus();
            if (!empty($menus)) {
                $this->all_menu_items = wp_get_nav_menu_items($menus[0]->term_id);
            }
        }
    }
    
    /**
     * Start the list before the elements are added.
     *
     * @param string $output Used to append additional content.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param stdClass $args An object of wp_nav_menu() arguments.
     */
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="sub-menu" data-depth="' . esc_attr($depth) . '">';
    }

    /**
     * Ends the list of after the elements are added.
     *
     * @param string $output Used to append additional content.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param stdClass $args An object of wp_nav_menu() arguments.
     */
    public function end_lvl(&$output, $depth = 0, $args = null) {
        // Add page children before closing the submenu (for mixed navigation)
        // This handles the case where we have both menu children and page children
        if (!empty($this->parent_stack) && isset($this->parent_stack[$depth])) {
            $parent_item = $this->parent_stack[$depth];
            if ($parent_item) {
                $menu_children = $this->get_menu_children($parent_item);
                $page_children = $this->get_page_children($parent_item);
                $has_menu_children = !empty($menu_children);
                $has_page_children = !empty($page_children);
                
                // Add page children, but filter out those that are already handled as menu children
                if ($has_page_children && !empty($page_children)) {
                    // Filter out page children that are already handled as menu children
                    $unique_page_children = $this->filter_duplicate_page_children($page_children, $menu_children);
                    
                    if (!empty($unique_page_children)) {
                        $this->add_page_children_to_output($output, $unique_page_children, $depth + 1);
                    }
                }
            }
        }
        
        $output .= '</ul>';
    }

    /**
     * Starts the element output.
     *
     * @param string $output Used to append additional content.
     * @param WP_Post $item Menu item data object.
     * @param int $depth Depth of menu item. Used for padding.
     * @param stdClass $args An object of wp_nav_menu() arguments.
     * @param int $id Current item ID.
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        // Store all menu items if this is the first call and we don't have them yet
        if (empty($this->all_menu_items) && isset($args->menu_items)) {
            $this->all_menu_items = $args->menu_items;
        }
        
        // Track parent items for mixed navigation - maintain proper hierarchy
        // Reset parent stack for deeper levels to avoid confusion
        if ($depth === 0) {
            $this->parent_stack = array();
        }
        $this->parent_stack[$depth] = $item;
        
        // Get all types of children for this item
        $menu_children = $this->get_menu_children($item);
        $page_children = $this->get_page_children($item);
        
        // Check if item has any children (menu or page)
        $has_menu_children = !empty($menu_children);
        $has_page_children = !empty($page_children);
        $has_children = $has_menu_children || $has_page_children;
        
        // Build classes
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item';
        
        if ($has_children) {
            $classes[] = 'menu-item-has-children';
            $classes[] = 'has-children';
        }
        
        if ($has_menu_children) {
            $classes[] = 'has-menu-children';
        }
        
        if ($has_page_children) {
            $classes[] = 'has-page-children';
        }
        
        // Add navigation type classes for different behaviors
        if ($has_menu_children && $has_page_children) {
            $classes[] = 'mixed-navigation';
        } else if ($has_menu_children) {
            $classes[] = 'menu-navigation';
        } else if ($has_page_children) {
            $classes[] = 'page-navigation';
        }

        // Add current page class and data attribute
        $current_url = rtrim($_SERVER['REQUEST_URI'], '/');
        $item_url = rtrim(parse_url($item->url, PHP_URL_PATH), '/');
        $is_current = ($current_url === $item_url);
        if ($is_current) {
            $classes[] = 'current-menu-item';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
        
        // Add data attributes for JavaScript
        $data_attributes = '';
        $data_attributes .= ' data-item-type="' . esc_attr($item->type) . '"';
        $data_attributes .= ' data-has-menu-children="' . ($has_menu_children ? 'true' : 'false') . '"';
        $data_attributes .= ' data-has-page-children="' . ($has_page_children ? 'true' : 'false') . '"';
        $data_attributes .= ' data-menu-url="' . esc_attr($item_url) . '"';
        $data_attributes .= ' data-menu-item-id="' . esc_attr($item->ID) . '"';
        
        if ($item->type === 'post_type' && $item->object === 'page') {
            $data_attributes .= ' data-page-id="' . esc_attr($item->object_id) . '"';
        }

        $output .= '<li' . $class_names . $data_attributes . '>';
        
        // For items with children, create a clickable row that opens submenu (following existing pattern)
        if ($has_children) {
            $button_classes = 'menu-modal__submenu-toggle menu-modal__submenu-row';
            $submenu_id = 'submenu-' . $item->ID;
            
            // Add specific classes based on child types
            if ($has_menu_children && $has_page_children) {
                $button_classes .= ' mixed-toggle';
            } else if ($has_page_children) {
                $button_classes .= ' page-toggle';
            } else {
                $button_classes .= ' menu-toggle';
            }
            
            $output .= '<button class="' . esc_attr($button_classes) . '" ';
            $output .= 'aria-expanded="false" ';
            $output .= 'aria-controls="' . esc_attr($submenu_id) . '" ';
            $output .= 'aria-haspopup="true" ';
            $output .= 'aria-label="' . esc_attr(sprintf(__('Open %s submenu', 'fau-elemental'), $item->title)) . '" ';
            $output .= 'data-parent-url="' . esc_attr($item->url) . '" ';
            $output .= 'data-parent-title="' . esc_attr($item->title) . '" ';
            $output .= 'data-menu-children="' . esc_attr(count($menu_children)) . '" ';
            $output .= 'data-page-children="' . esc_attr(count($page_children)) . '">';
            
            $output .= '<span class="menu-modal__item-title">' . apply_filters('the_title', $item->title, $item->ID) . '</span>';
            $output .= '<span class="menu-modal__submenu-arrow" aria-hidden="true"></span>';
            $output .= '</button>';
            
            // If this item has only page children (no menu children), create the submenu directly
            // because WordPress won't call start_lvl/end_lvl for page-only children
            if ($has_page_children && !$has_menu_children) {
                $submenu_id = 'submenu-' . $item->ID;
                $output .= '<ul class="sub-menu page-only-submenu" id="' . esc_attr($submenu_id) . '" data-depth="' . esc_attr($depth + 1) . '">';
                
                // Filter out page children that might be duplicated as menu children
                $unique_page_children = $this->filter_duplicate_page_children($page_children, $menu_children);
                
                if (!empty($unique_page_children)) {
                    $this->add_page_children_to_output($output, $unique_page_children, $depth + 1);
                }
                
                $output .= '</ul>';
            }
            
            // For items with menu children (or mixed), let the custom system handle it
            // The custom system will call start_lvl/end_lvl for menu children
            // Page children will be added in end_lvl if needed for mixed navigation
        } else {
            // For items without children, keep normal link (following existing pattern)
            $link_attributes = '';
            if ($is_current) {
                $link_attributes .= ' aria-current="page"';
            }
            $output .= '<a href="' . esc_attr($item->url) . '"' . $link_attributes . '>' . apply_filters('the_title', $item->title, $item->ID) . '</a>';
        }
    }

    /**
     * Ends the element output.
     *
     * @param string $output Used to append additional content.
     * @param WP_Post $item Menu item data object.
     * @param int $depth Depth of menu item.
     * @param stdClass $args An object of wp_nav_menu() arguments.
     */
    public function end_el(&$output, $item, $depth = 0, $args = null) {
        // Don't create submenus here - let the custom system handle the structure
        // Page children will be added in end_lvl if needed for mixed navigation
        $output .= '</li>';
    }
    
    /**
     * Get menu children for an item
     *
     * @param object $item Menu item
     * @return array Menu children
     */
    private function get_menu_children($item) {
        $children = array();
        if (!empty($this->all_menu_items)) {
            foreach ($this->all_menu_items as $menu_item) {
                if ($menu_item->menu_item_parent == $item->ID) {
                    $children[] = $menu_item;
                }
            }
        }
        
        // If we still don't have menu items, try to get them from the current menu
        if (empty($children) && empty($this->all_menu_items)) {
            $locations = get_nav_menu_locations();
            foreach (array('header_primary_menu', 'header_menu_links', 'primary') as $location) {
                if (isset($locations[$location])) {
                    $current_menu_items = wp_get_nav_menu_items($locations[$location]);
                    if ($current_menu_items) {
                        foreach ($current_menu_items as $menu_item) {
                            if ($menu_item->menu_item_parent == $item->ID) {
                                $children[] = $menu_item;
                            }
                        }
                        if (!empty($children)) {
                            break;
                        }
                    }
                }
            }
        }
        
        return $children;
    }
    
    /**
     * Get page children for an item (if it's a page)
     *
     * @param object $item Menu item
     * @return array Page children
     */
    private function get_page_children($item) {
        // Only get page children if this menu item links to a page
        if ($item->type !== 'post_type' || $item->object !== 'page') {
            return array();
        }
        
        // Get child pages and filter out hidden ones
        $args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_parent' => $item->object_id,
            'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
            'posts_per_page' => -1,
        );
        
        $page_children = get_posts($args);
        
        // Filter out pages marked as hidden from menu
        $page_children = array_filter($page_children, function($page) {
            $hide_from_menu = get_post_meta($page->ID, '_fau_hide_from_menu', true);
            return $hide_from_menu !== '1';
        });
        
        return $page_children;
    }
    
    /**
     * Filter out page children that are already handled as menu children
     *
     * @param array $page_children Array of page objects
     * @param array $menu_children Array of menu item objects
     * @return array Filtered page children
     */
    private function filter_duplicate_page_children($page_children, $menu_children) {
        if (empty($menu_children)) {
            return $page_children; // No menu children, so all page children are unique
        }
        
        $filtered_children = array();
        
        foreach ($page_children as $page) {
            $is_duplicate = false;
            
            // Check if this page is already handled as a menu child
            foreach ($menu_children as $menu_child) {
                // Method 1: Direct object_id comparison (most reliable for pages)
                if ($menu_child->type === 'post_type' && $menu_child->object === 'page' && $menu_child->object_id == $page->ID) {
                    $is_duplicate = true;
                    break;
                }
                
                // Method 2: URL path comparison (fallback for other types)
                $page_url = rtrim(parse_url(get_permalink($page->ID), PHP_URL_PATH), '/');
                $menu_url = rtrim(parse_url($menu_child->url, PHP_URL_PATH), '/');
                
                if ($page_url === $menu_url && !empty($page_url) && !empty($menu_url)) {
                    $is_duplicate = true;
                    break;
                }
                
                // Method 3: Title comparison (additional fallback)
                if (strtolower(trim($page->post_title)) === strtolower(trim($menu_child->title))) {
                    $is_duplicate = true;
                    break;
                }
            }
            
            if (!$is_duplicate) {
                $filtered_children[] = $page;
            }
        }
        
        return $filtered_children;
    }
    
    /**
     * Add page children to the output
     *
     * @param string &$output Output string (passed by reference)
     * @param array $page_children Array of page objects
     * @param int $depth Current depth
     */
    private function add_page_children_to_output(&$output, $page_children, $depth = 1) {
        if (empty($page_children)) {
            return;
        }
        
        foreach ($page_children as $page) {
            // Get child pages for this page - filter out hidden ones
            $args = array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_parent' => $page->ID,
                'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
                'posts_per_page' => -1,
            );
            
            $child_pages = get_posts($args);
            
            // Filter out hidden pages
            $child_pages = array_filter($child_pages, function($child_page) {
                $hide_from_menu = get_post_meta($child_page->ID, '_fau_hide_from_menu', true);
                return $hide_from_menu !== '1';
            });
            
            $has_children = !empty($child_pages);
            
            // Build classes
            $classes = array('menu-item', 'page-item');
            if ($has_children) {
                $classes[] = 'menu-item-has-children';
                $classes[] = 'has-children';
                $classes[] = 'has-page-children';
                $classes[] = 'page-navigation';
            }
            
            $class_names = join(' ', $classes);
            
            // Data attributes
            $data_attributes = '';
            $data_attributes .= ' data-item-type="page-child"';
            $data_attributes .= ' data-page-id="' . esc_attr($page->ID) . '"';
            $data_attributes .= ' data-has-page-children="' . ($has_children ? 'true' : 'false') . '"';
            $data_attributes .= ' data-menu-url="' . esc_attr(rtrim(parse_url(get_permalink($page->ID), PHP_URL_PATH), '/')) . '"';
            $data_attributes .= ' data-menu-item-id="page-' . esc_attr($page->ID) . '"';
            
            $output .= '<li class="' . esc_attr($class_names) . '"' . $data_attributes . '>';
            
            // Add the page link or button based on children
            if ($has_children) {
                $button_classes = 'menu-modal__submenu-toggle menu-modal__submenu-row page-toggle';
                $submenu_id = 'submenu-page-' . $page->ID;
                
                $output .= '<button class="' . esc_attr($button_classes) . '" ';
                $output .= 'aria-expanded="false" ';
                $output .= 'aria-controls="' . esc_attr($submenu_id) . '" ';
                $output .= 'aria-haspopup="true" ';
                $output .= 'aria-label="' . esc_attr(sprintf(__('Open %s submenu', 'fau-elemental'), $page->post_title)) . '" ';
                $output .= 'data-parent-url="' . esc_attr(get_permalink($page->ID)) . '" ';
                $output .= 'data-parent-title="' . esc_attr($page->post_title) . '" ';
                $output .= 'data-page-children="' . esc_attr(count($child_pages)) . '">';
                
                $output .= '<span class="menu-modal__item-title">' . esc_html($page->post_title) . '</span>';
                $output .= '<span class="menu-modal__submenu-arrow" aria-hidden="true"></span>';
                $output .= '</button>';
                
                // Add child pages submenu
                $output .= '<ul class="sub-menu page-submenu" id="' . esc_attr($submenu_id) . '" data-depth="' . esc_attr($depth) . '">';
                $this->add_page_children_to_output($output, $child_pages, $depth + 1);
                $output .= '</ul>';
            } else {
                // Regular page link
                $output .= '<a href="' . esc_attr(get_permalink($page->ID)) . '" class="menu-item-link page-link">';
                $output .= esc_html($page->post_title);
                $output .= '</a>';
            }
            
            $output .= '</li>';
        }
    }
}
