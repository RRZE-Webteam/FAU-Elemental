<?php
/**
 * Mixed Navigation Walker
 * 
 * This class extends WordPress's Walker_Nav_Menu to provide mixed navigation
 * that combines menu items with page hierarchy.
 * 
 * TESTING APPROACH:
 * This class has been refactored to support unit testing through:
 * 1. Lazy loading - no database calls during instantiation
 * 2. Dependency injection - use set_menu_items() and set_pages() for testing
 * 3. Manual initialization - call mark_initialized() to skip lazy loading

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
     * All menu items from current menu location
     * @var array
     */
    private $all_menu_items = array();
    
    /**
     * All pages for current site
     * @var array
     */
    private $all_pages = array();
    
    /**
     * Menu items indexed by parent ID for O(1) lookups
     * @var array
     */
    private $menu_items_by_parent = array();
    
    /**
     * Menu items indexed by object_id for duplicate checking
     * @var array
     */
    private $menu_items_by_object_id = array();
    
    /**
     * Menu items indexed by URL for duplicate checking
     * @var array
     */
    private $menu_items_by_url = array();
    
    /**
     * Menu items indexed by title for duplicate checking
     * @var array
     */
    private $menu_items_by_title = array();
    
    /**
     * Pages indexed by parent ID for O(1) lookups
     * @var array
     */
    private $pages_by_parent = array();
    
    /**
     * Pages indexed by ID for O(1) lookups
     * @var array
     */
    private $pages_by_id = array();
    
    /**
     * Stack to track parent items for each depth level
     * @var array
     */
    private $parent_stack = array();
    
    /**
     * Whether the walker has been initialized
     * @var bool
     */
    private $initialized = false;
    
    /**
     * Flag to prevent multiple additions of current page parent info
     * @var bool
     */
    private $parent_info_added = false;
    
    /**
     * Constructor - no database calls during instantiation
     */
    public function __construct() {
        // Lazy initialization - no database calls here
    }
    
    /**
     * Initialize the walker (lazy loading)
     */
    private function initialize() {
        if ($this->initialized) {
            return;
        }
        
        // Initialize menu items from current menu location
        $this->initialize_menu_items();
        
        // Build performance indexes
        $this->build_menu_indexes();
        $this->build_page_indexes();
        
        $this->initialized = true;
    }
    
    /**
     * Set menu items for testing (dependency injection)
     * 
     * @param array $menu_items Array of menu item objects
     */
    public function set_menu_items($menu_items) {
        $this->all_menu_items = $menu_items;
        $this->build_menu_indexes();
    }
    
    /**
     * Set pages for testing (dependency injection)
     * 
     * @param array $pages Array of page objects
     */
    public function set_pages($pages) {
        $this->all_pages = $pages;
        $this->build_page_indexes();
    }
    
    /**
     * Mark as initialized for testing
     */
    public function mark_initialized() {
        $this->initialized = true;
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
     * Build menu item indexes for O(1) lookups
     */
    private function build_menu_indexes() {
        if (empty($this->all_menu_items)) {
            return;
        }
        
        foreach ($this->all_menu_items as $item) {
            // Index by parent ID
            $parent_id = $item->menu_item_parent;
            if (!isset($this->menu_items_by_parent[$parent_id])) {
                $this->menu_items_by_parent[$parent_id] = array();
            }
            $this->menu_items_by_parent[$parent_id][] = $item;
            
            // Index by object_id for duplicate checking
            if ($item->type === 'post_type' && $item->object === 'page') {
                $this->menu_items_by_object_id[$item->object_id] = $item;
            }
            
            // Index by URL for duplicate checking
            $parsed_url = parse_url($item->url, PHP_URL_PATH);
            $url = $parsed_url ? rtrim($parsed_url, '/') : '';
            if (!empty($url)) {
                $this->menu_items_by_url[$url] = $item;
            }
            
            // Index by title for duplicate checking
            $title = strtolower(trim($item->title));
            if (!empty($title)) {
                $this->menu_items_by_title[$title] = $item;
            }
        }
    }
    
    /**
     * Build page indexes for O(1) lookups
     */
    private function build_page_indexes() {
        // Get all pages at once with hierarchical=0 for flat structure
        // Include menu_order to respect page ordering set in admin
        $this->all_pages = get_pages(array(
            'hierarchical' => 0,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'sort_column' => 'menu_order,post_title', // Order by menu_order first, then title
            'sort_order' => 'ASC',
        ));
        
        if (empty($this->all_pages)) {
            return;
        }
        
        // Filter out hidden pages and build indexes
        foreach ($this->all_pages as $page) {
            $hide_from_menu = get_post_meta($page->ID, '_fau_hide_from_menu', true);
            if ($hide_from_menu === '1') {
                continue;
            }
            
            // Index by parent ID
            $parent_id = $page->post_parent;
            if (!isset($this->pages_by_parent[$parent_id])) {
                $this->pages_by_parent[$parent_id] = array();
            }
            $this->pages_by_parent[$parent_id][] = $page;
            
            // Index by ID
            $this->pages_by_id[$page->ID] = $page;
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
     * Override the walk method to add parent info at the beginning
     *
     * @param array $elements Elements to walk
     * @param int $max_depth Maximum depth
     * @param ...$args Additional arguments
     * @return string
     */
    public function walk($elements, $max_depth, ...$args) {
        $output = '';
        
        // Add current page parent info at the very beginning if needed
        $this->add_current_page_parent_info($output);
        
        // Call the parent walk method
        $output .= parent::walk($elements, $max_depth, ...$args);
        
        return $output;
    }



    /**
     * Add information about the current page's parent when the current page is hidden
     * This helps JavaScript navigate to the correct level
     * Traverses up the hierarchy until it finds a visible page
     *
     * @param string &$output Output string (passed by reference)
     */
    private function add_current_page_parent_info(&$output) {
        // Only add this info if we're on a single page
        if (!is_page()) {
            return;
        }
        
        $current_page_id = get_queried_object_id();
        if (!$current_page_id) {
            return;
        }
        
        // Check if current page is hidden from menu
        $hide_from_menu = get_post_meta($current_page_id, '_fau_hide_from_menu', true);
        if ($hide_from_menu !== '1') {
            return;
        }
        
        // Traverse up the hierarchy until we find a visible page
        $current_page = get_post($current_page_id);
        $visible_parent = null;
        $current_parent_id = $current_page->post_parent;
        
        while ($current_parent_id !== 0) {
            $parent_page = get_post($current_parent_id);
            if (!$parent_page) {
                break;
            }
            
            // Check if this parent is visible in the menu
            $parent_hide_from_menu = get_post_meta($parent_page->ID, '_fau_hide_from_menu', true);
            if ($parent_hide_from_menu !== '1') {
                // Found a visible parent - use this one
                $visible_parent = $parent_page;
                break;
            }
            
            // This parent is also hidden, continue up the hierarchy
            $current_parent_id = $parent_page->post_parent;
        }
        
        // If we found a visible parent, add the info
        if ($visible_parent) {
            $output .= '<div class="current-page-parent-info" ';
            $output .= 'data-parent-page-id="' . esc_attr($visible_parent->ID) . '" ';
            $parent_parsed_url = parse_url(get_permalink($visible_parent->ID), PHP_URL_PATH);
            $parent_url = $parent_parsed_url ? rtrim($parent_parsed_url, '/') : '';
            $output .= 'data-parent-page-url="' . esc_attr($parent_url) . '" ';
            $output .= 'data-parent-page-title="' . esc_attr($visible_parent->post_title) . '" ';
            $output .= 'data-current-page-hidden="true"></div>';
        }
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
        // Lazy initialize if not already done
        if (!$this->initialized) {
            $this->initialize();
        }
        
        // Add current page parent info when processing the first item (depth 0) and only once
        if ($depth === 0 && !$this->parent_info_added) {
            $this->add_current_page_parent_info($output);
            $this->parent_info_added = true;
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
        
        // Check if we should disable submenu functionality for this location
        $disable_submenus = $this->should_disable_submenus($args);
        
        // Build classes
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item';
        
        if ($has_children && !$disable_submenus) {
            $classes[] = 'menu-item-has-children';
            $classes[] = 'has-children';
        }
        
        if ($has_menu_children && !$disable_submenus) {
            $classes[] = 'has-menu-children';
        }
        
        if ($has_page_children && !$disable_submenus) {
            $classes[] = 'has-page-children';
        }
        
        // Add navigation type classes for different behaviors
        if (!$disable_submenus) {
            if ($has_menu_children && $has_page_children) {
                $classes[] = __('mixed-navigation', 'fau-elemental');
            } else if ($has_menu_children) {
                $classes[] = __('menu-navigation', 'fau-elemental');
            } else if ($has_page_children) {
                $classes[] = __('page-navigation', 'fau-elemental');
            }
        }

        // Add current page class and data attribute
        $current_parsed_url = wp_parse_url(home_url(add_query_arg([])), PHP_URL_PATH);
        $current_url = $current_parsed_url ? rtrim($current_parsed_url, '/') : '';
        $item_parsed_url = parse_url($item->url, PHP_URL_PATH);
        $item_url = $item_parsed_url ? rtrim($item_parsed_url, '/') : '';
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
        // But only if submenus are not disabled
        if ($has_children && !$disable_submenus) {
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
            
            $title = esc_html(wp_strip_all_tags(apply_filters('the_title', $item->title, $item->ID)));
            $aria = esc_attr(sprintf(__('Open %s submenu', 'fau-elemental'), $title));
            
            $output .= '<button type="button" class="' . esc_attr($button_classes) . '" ';
            $output .= 'aria-expanded="false" ';
            $output .= 'aria-controls="' . esc_attr($submenu_id) . '" ';
            $output .= 'aria-haspopup="true" ';
            $output .= 'aria-label="' . $aria . '" ';
            $output .= 'data-parent-url="' . esc_attr($item->url) . '" ';
            $output .= 'data-parent-title="' . esc_attr($item->title) . '" ';
            $output .= 'data-menu-children="' . esc_attr(count($menu_children)) . '" ';
            $output .= 'data-page-children="' . esc_attr(count($page_children)) . '">';
            
            $output .= '<span class="menu-modal__item-title">' . $title . '</span>';
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
            // For items without children OR when submenus are disabled, keep normal link
            $link_attributes = '';
            if ($is_current) {
                $link_attributes .= ' aria-current="page"';
            }
            $title = esc_html(wp_strip_all_tags(apply_filters('the_title', $item->title, $item->ID)));
            $output .= '<a href="' . esc_attr($item->url) . '"' . $link_attributes . '>' . $title . '</a>';
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
     * Get menu children for an item - O(1) lookup using pre-built index
     *
     * @param object $item Menu item
     * @return array Menu children
     */
    private function get_menu_children($item) {
        return isset($this->menu_items_by_parent[$item->ID]) ? $this->menu_items_by_parent[$item->ID] : array();
    }
    
    /**
     * Check if submenu functionality should be disabled for this location
     *
     * @param object $args Menu arguments
     * @return bool True if submenus should be disabled
     */
    private function should_disable_submenus($args) {
        // Check if we're in the header_menu_links location
        if ($args && isset($args->theme_location) && $args->theme_location === 'header_menu_links') {
            return true;
        }
        
        // Also check if we're in a context where depth is limited to 1
        if ($args && isset($args->depth) && $args->depth === 1) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get page children for an item (if it's a page) - O(1) lookup using pre-built index
     *
     * @param object $item Menu item
     * @return array Page children
     */
    private function get_page_children($item) {
        // Only get page children if this menu item links to a page
        if ($item->type !== 'post_type' || $item->object !== 'page') {
            return array();
        }
        
        return isset($this->pages_by_parent[$item->object_id]) ? $this->pages_by_parent[$item->object_id] : array();
    }
    
    /**
     * Filter out page children that are already handled as menu children - O(n) using hash maps
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
            
            // O(1) lookup by object_id (most reliable for pages)
            if (isset($this->menu_items_by_object_id[$page->ID])) {
                $is_duplicate = true;
            }
            
            // O(1) lookup by URL (fallback for other types)
            if (!$is_duplicate) {
                $page_parsed_url = parse_url(get_permalink($page->ID), PHP_URL_PATH);
                $page_url = $page_parsed_url ? rtrim($page_parsed_url, '/') : '';
                if (!empty($page_url) && isset($this->menu_items_by_url[$page_url])) {
                    $is_duplicate = true;
                }
            }
            
            // O(1) lookup by title (additional fallback)
            if (!$is_duplicate) {
                $title = strtolower(trim($page->post_title));
                if (!empty($title) && isset($this->menu_items_by_title[$title])) {
                    $is_duplicate = true;
                }
            }
            
            if (!$is_duplicate) {
                $filtered_children[] = $page;
            }
        }
        
        return $filtered_children;
    }
    
    /**
     * Add page children to the output - O(1) lookup using pre-built index
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
            // O(1) lookup for child pages using pre-built index
            $child_pages = isset($this->pages_by_parent[$page->ID]) ? $this->pages_by_parent[$page->ID] : array();
            $has_children = !empty($child_pages);
            
            // Build classes
            $classes = array('menu-item', 'page-item');
            if ($has_children) {
                $classes[] = 'menu-item-has-children';
                $classes[] = 'has-children';
                $classes[] = 'has-page-children';
                $classes[] = __('page-navigation', 'fau-elemental');
            }
            
            $class_names = join(' ', $classes);
            
            // Data attributes
            $data_attributes = '';
            $data_attributes .= ' data-item-type="page-child"';
            $data_attributes .= ' data-page-id="' . esc_attr($page->ID) . '"';
            $data_attributes .= ' data-has-page-children="' . ($has_children ? 'true' : 'false') . '"';
            $page_parsed_url = parse_url(get_permalink($page->ID), PHP_URL_PATH);
            $page_url = $page_parsed_url ? rtrim($page_parsed_url, '/') : '';
            $data_attributes .= ' data-menu-url="' . esc_attr($page_url) . '"';
            $data_attributes .= ' data-menu-item-id="page-' . esc_attr($page->ID) . '"';
            
            $output .= '<li class="' . esc_attr($class_names) . '"' . $data_attributes . '>';
            
            // Add the page link or button based on children
            if ($has_children) {
                $button_classes = 'menu-modal__submenu-toggle menu-modal__submenu-row page-toggle';
                $submenu_id = 'submenu-page-' . $page->ID;
                $page_title = esc_html(wp_strip_all_tags($page->post_title));
                
                $output .= '<button type="button" class="' . esc_attr($button_classes) . '" ';
                $output .= 'aria-expanded="false" ';
                $output .= 'aria-controls="' . esc_attr($submenu_id) . '" ';
                $output .= 'aria-haspopup="true" ';
                $aria = esc_attr(sprintf(__('Open %s submenu', 'fau-elemental'), $page_title));
                $output .= 'aria-label="' . $aria . '" ';
                $output .= 'data-parent-url="' . esc_attr(get_permalink($page->ID)) . '" ';
                $output .= 'data-parent-title="' . esc_attr($page->post_title) . '" ';
                $output .= 'data-page-children="' . esc_attr(count($child_pages)) . '">';
                
                $output .= '<span class="menu-modal__item-title">' . $page_title . '</span>';
                $output .= '<span class="menu-modal__submenu-arrow" aria-hidden="true"></span>';
                $output .= '</button>';
                
                // Add child pages submenu
                $output .= '<ul class="sub-menu page-submenu" id="' . esc_attr($submenu_id) . '" data-depth="' . esc_attr($depth) . '">';
                $this->add_page_children_to_output($output, $child_pages, $depth + 1);
                $output .= '</ul>';
            } else {
                // Regular page link
                $page_title = esc_html(wp_strip_all_tags($page->post_title));
                $output .= '<a href="' . esc_attr(get_permalink($page->ID)) . '" class="menu-item-link page-link">';
                $output .= $page_title;
                $output .= '</a>';
            }
            
            $output .= '</li>';
        }
    }
}
